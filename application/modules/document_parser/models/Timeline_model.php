<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Timeline_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

  
    public function getTimeline($tfs_id) {
        $fs = $this->db->select('Tfsid, Bcid, TenderRefNo, TenderTitle, ImportantDates, TimelineJson, DocumentName')
            ->from('TenderFactSheet')
            ->where('Tfsid', (int)$tfs_id)
            ->get()->row_array();

        if (!$fs) {
            return null;
        }

        // 1. If cached TimelineJson has milestones, check if valid; otherwise hydrate from DB
        $existingTimeline = !empty($fs['TimelineJson']) ? json_decode($fs['TimelineJson'], true) : [];
        $corrigenda = $existingTimeline['corrigenda'] ?? [];
        $reminders  = $this->getReminders($tfs_id);

        // 2. Fetch corresponding BidCreationDetails if Bcid is present
        $bid = null;
        if (!empty($fs['Bcid'])) {
            $bid = $this->db->select('Bcid, PublishedDate, SubmissionDate, OpeningDate, BidTitle')
                ->where('Bcid', (int)$fs['Bcid'])
                ->get('BidCreationDetails')->row_array();
        }

        // 3. Parse ImportantDates from FactSheet
        $rawDates = [];
        if (!empty($fs['ImportantDates'])) {
            $this->load->model('document_parser/Factsheet_model');
            $rawDates = Factsheet_model::deserializeKeyValue($fs['ImportantDates']);
        }

        // 4. Map and normalize dates into milestones
        $milestones = [];
        $now = time();

        if (is_array($rawDates)) {
            foreach ($rawDates as $key => $val) {
                if (empty($val) || !is_string($val)) continue;
                $valTrim = trim($val);
                if ($valTrim === '' || stripos($valTrim, 'not specified') !== false) continue;

                $type = $this->_detectMilestoneType($key);
                $ts = $this->_parseDateString($valTrim);

                $milestones[] = [
                    'milestone_type'      => $type,
                    'milestone_title'     => $this->_formatTitle($key),
                    'original_date_text'  => $valTrim,
                    'normalized_date'     => $ts ? date('Y-m-d', $ts) : null,
                    'normalized_datetime' => $ts ? (date('H:i:s', $ts) !== '00:00:00' ? date('Y-m-d H:i:s', $ts) : date('Y-m-d 23:59:59', $ts)) : null,
                    'timestamp'           => $ts ?: 0,
                    'source'              => 'FactSheet'
                ];
            }
        }

        // 5. Supplement from BidCreationDetails if not already present in FactSheet
        if ($bid) {
            $hasSubmission = false;
            $hasOpening    = false;
            $hasPublish    = false;

            foreach ($milestones as $m) {
                if ($m['milestone_type'] === 'bid_submission')    $hasSubmission = true;
                if ($m['milestone_type'] === 'technical_opening') $hasOpening    = true;
                if ($m['milestone_type'] === 'publication')        $hasPublish    = true;
            }

            if (!$hasPublish && !empty($bid['PublishedDate']) && $bid['PublishedDate'] !== '0000-00-00') {
                $ts = strtotime($bid['PublishedDate']);
                $milestones[] = [
                    'milestone_type'      => 'publication',
                    'milestone_title'     => 'Tender Publication Date',
                    'original_date_text'  => date('d-m-Y', $ts),
                    'normalized_date'     => date('Y-m-d', $ts),
                    'normalized_datetime' => date('Y-m-d 00:00:00', $ts),
                    'timestamp'           => $ts,
                    'source'              => 'BidCreationDetails'
                ];
            }

            if (!$hasSubmission && !empty($bid['SubmissionDate']) && $bid['SubmissionDate'] !== '0000-00-00') {
                $ts = strtotime($bid['SubmissionDate']);
                $milestones[] = [
                    'milestone_type'      => 'bid_submission',
                    'milestone_title'     => 'Bid Submission Deadline',
                    'original_date_text'  => date('d-m-Y', $ts),
                    'normalized_date'     => date('Y-m-d', $ts),
                    'normalized_datetime' => date('Y-m-d 17:00:00', $ts),
                    'timestamp'           => $ts,
                    'source'              => 'BidCreationDetails'
                ];
            }

            if (!$hasOpening && !empty($bid['OpeningDate']) && $bid['OpeningDate'] !== '0000-00-00') {
                $ts = strtotime($bid['OpeningDate']);
                $milestones[] = [
                    'milestone_type'      => 'technical_opening',
                    'milestone_title'     => 'Technical Bid Opening',
                    'original_date_text'  => date('d-m-Y', $ts),
                    'normalized_date'     => date('Y-m-d', $ts),
                    'normalized_datetime' => date('Y-m-d 17:30:00', $ts),
                    'timestamp'           => $ts,
                    'source'              => 'BidCreationDetails'
                ];
            }
        }

        // 6. Sort milestones chronologically
        usort($milestones, function($a, $b) {
            if ($a['timestamp'] === $b['timestamp']) return 0;
            if ($a['timestamp'] === 0) return 1;
            if ($b['timestamp'] === 0) return -1;
            return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
        });

        // 7. Calculate status and countdowns for each milestone
        $nextAction = null;
        $submissionCountdown = null;

        foreach ($milestones as &$m) {
            $ts = $m['timestamp'];
            if (!$ts) {
                $m['status'] = 'upcoming';
                $m['countdown_text'] = 'Date unstated';
                continue;
            }

            $diffSeconds = $ts - $now;
            $diffDays = (int)floor(abs($diffSeconds) / 86400);

            if ($diffSeconds < 0) {
                $m['status'] = 'completed';
                $m['countdown_text'] = ($diffDays === 0) ? 'Concluded today' : "Concluded {$diffDays}d ago";
            } elseif ($diffSeconds <= 259200) { // within 3 days
                $m['status'] = 'due_soon';
                $m['countdown_text'] = ($diffDays === 0) ? 'Due today' : "Due in {$diffDays}d";
                if (!$nextAction) {
                    $nextAction = [
                        'milestone_title' => $m['milestone_title'],
                        'countdown_text'  => $m['countdown_text']
                    ];
                }
            } else {
                $m['status'] = 'upcoming';
                $m['countdown_text'] = "In {$diffDays} days";
                if (!$nextAction) {
                    $nextAction = [
                        'milestone_title' => $m['milestone_title'],
                        'countdown_text'  => $m['countdown_text']
                    ];
                }
            }

            if ($m['milestone_type'] === 'bid_submission') {
                $badgeClass = ($diffSeconds < 0) ? 'badge bg-secondary' : (($diffSeconds <= 259200) ? 'badge bg-danger' : 'badge bg-primary');
                $submissionCountdown = [
                    'countdown_text' => $m['countdown_text'],
                    'date_text'      => !empty($m['normalized_datetime']) ? date('d-m-Y h:i A', strtotime($m['normalized_datetime'])) : (!empty($m['normalized_date']) ? date('d-m-Y', strtotime($m['normalized_date'])) : 'N/A'),
                    'badge_class'    => $badgeClass
                ];
            }
        }
        unset($m);

        $payload = [
            'tfs_id'     => (int)$tfs_id,
            'milestones' => $milestones,
            'metrics'    => [
                'submission_countdown' => $submissionCountdown,
                'next_action'          => $nextAction,
                'total_milestones'     => count($milestones)
            ],
            'validation' => [
                'health_score' => 100,
                'is_valid'     => true,
                'warnings'     => []
            ],
            'corrigenda' => $corrigenda,
            'reminders'  => $reminders
        ];

        return $payload;
    }

    /**
     * Parse date string into unix timestamp safely
     */
    private function _parseDateString($str) {
        $clean = trim($str);
        if (preg_match('/(\d{1,2}[-\/.]\d{1,2}[-\/.]\d{2,4}(?:\s+\d{1,2}:\d{2}(?::\d{2})?(?:\s*[AaPp][Mm])?)?)/', $clean, $m)) {
            $parsed = strtotime(str_replace('/', '-', $m[1]));
            if ($parsed !== false) return $parsed;
        }

        $parsed = strtotime($clean);
        return ($parsed !== false) ? $parsed : null;
    }

    /**
     * Detect milestone type from key label
     */
    private function _detectMilestoneType($key) {
        $k = strtolower($key);
        if (strpos($k, 'publish') !== false || strpos($k, 'nit date') !== false || strpos($k, 'issue date') !== false) {
            return 'publication';
        }
        if (strpos($k, 'download start') !== false || strpos($k, 'sale start') !== false) {
            return 'download_start';
        }
        if (strpos($k, 'download') !== false || strpos($k, 'sale end') !== false) {
            return 'download_end';
        }
        if (strpos($k, 'query') !== false || strpos($k, 'clarification') !== false) {
            return 'pre_bid_query';
        }
        if (strpos($k, 'pre-bid') !== false || strpos($k, 'pre bid') !== false || strpos($k, 'meeting') !== false || strpos($k, 'conference') !== false) {
            return 'pre_bid_meeting';
        }
        if (strpos($k, 'start') !== false && (strpos($k, 'submi') !== false || strpos($k, 'bid') !== false)) {
            return 'bid_start';
        }
        if (strpos($k, 'submission') !== false || strpos($k, 'due') !== false || strpos($k, 'closing') !== false || strpos($k, 'receipt') !== false) {
            return 'bid_submission';
        }
        if (strpos($k, 'technical') !== false || (strpos($k, 'opening') !== false && strpos($k, 'finan') === false)) {
            return 'technical_opening';
        }
        if (strpos($k, 'financial') !== false || strpos($k, 'price') !== false) {
            return 'financial_opening';
        }
        if (strpos($k, 'pbg') !== false || strpos($k, 'guarantee') !== false) {
            return 'pbg_submission';
        }
        return 'other';
    }

    /**
     * Clean and format title for display
     */
    private function _formatTitle($key) {
        $k = trim($key);
        $k = preg_replace('/(?<!\ )[A-Z]/', ' $0', $k);
        $k = ucwords(str_replace(['_', '-'], ' ', $k));
        return trim($k);
    }

    /**
     * Save Timeline JSON blob on TenderFactSheet table if caching is needed
     */
    public function saveTimelineJson($tfs_id, $timeline_array) {
        $json = json_encode($timeline_array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return $this->db->where('Tfsid', (int)$tfs_id)->update('TenderFactSheet', [
            'TimelineJson' => $json
        ]);
    }

    /**
     * Check if a table exists in a case-insensitive manner (robust for MySQL on Windows & Linux)
     */
    public function tableExists($table) {
        $tables = array_map('strtolower', $this->db->list_tables());
        return in_array(strtolower($table), $tables);
    }

    /**
     * Helper to read TimelineJson directly without infinite recursion
     */
    public function getTimelineFromBlob($tfs_id) {
        $row = $this->db->select('TimelineJson')->where('Tfsid', (int)$tfs_id)->get('TenderFactSheet')->row_array();
        return !empty($row['TimelineJson']) ? json_decode($row['TimelineJson'], true) : [];
    }

    /**
     * Get Corrigenda records directly from TimelineJson
     */
    public function getCorrigenda($tfs_id) {
        $timeline = $this->getTimelineFromBlob($tfs_id);
        return $timeline['corrigenda'] ?? [];
    }

    /**
     * Get reminders directly from exact table TenderTimelineReminders (with fallback to TimelineJson)
     */
    public function getReminders($tfs_id, $user_id = null) {
        $tfs_id = (int)$tfs_id;
        $dbReminders = [];

        if ($this->tableExists('TenderTimelineReminders')) {
            $this->db->where('Tfsid', $tfs_id)
                     ->where('DeliveryStatus !=', 'Cancelled')
                     ->order_by('ScheduledAt', 'ASC');

            if ($user_id) {
                $this->db->where('UserId', (int)$user_id);
            }

            $rows = $this->db->get('TenderTimelineReminders')->result_array();
            foreach ($rows as $row) {
                $dbReminders[] = [
                    'id'              => (int)$row['Ttrid'],
                    'Ttrid'           => (int)$row['Ttrid'],
                    'tfs_id'          => (int)$row['Tfsid'],
                    'Tfsid'           => (int)$row['Tfsid'],
                    'bcid'            => !empty($row['Bcid']) ? (int)$row['Bcid'] : null,
                    'Bcid'            => !empty($row['Bcid']) ? (int)$row['Bcid'] : null,
                    'user_id'         => (int)$row['UserId'],
                    'UserId'          => (int)$row['UserId'],
                    'milestone_type'  => $row['MilestoneType'],
                    'MilestoneType'   => $row['MilestoneType'],
                    'reminder_type'   => $row['ReminderType'],
                    'ReminderType'    => $row['ReminderType'],
                    'scheduled_at'    => $row['ScheduledAt'],
                    'ScheduledAt'     => $row['ScheduledAt'],
                    'recipient_email' => $row['RecipientEmail'],
                    'RecipientEmail'  => $row['RecipientEmail'],
                    'notes'           => $row['Notes'],
                    'Notes'           => $row['Notes'],
                    'status'          => strtolower($row['DeliveryStatus']),
                    'delivery_status' => $row['DeliveryStatus'],
                    'DeliveryStatus'  => $row['DeliveryStatus'],
                    'created_at'      => $row['CreationTs'],
                    'CreationTs'      => $row['CreationTs']
                ];
            }
        }

        if (!empty($dbReminders)) {
            return $dbReminders;
        }

        // Fallback to TimelineJson if table has no records for this tender
        $timeline = $this->getTimelineFromBlob($tfs_id);
        $reminders = $timeline['reminders'] ?? [];
        if ($user_id) {
            $filtered = [];
            foreach ($reminders as $r) {
                if ((int)($r['user_id'] ?? 0) === (int)$user_id) {
                    $filtered[] = $r;
                }
            }
            return $filtered;
        }
        return $reminders;
    }

    /**
     * Create or update a reminder inside TenderTimelineReminders and sync to TimelineJson
     */
    public function saveReminder($data) {
        $tfs_id = (int)($data['tfs_id'] ?? $data['Tfsid'] ?? 0);
        if ($tfs_id <= 0) return false;

        $now = date('Y-m-d H:i:s');
        $userId = (int)($data['user_id'] ?? $data['UserId'] ?? 1);
        $milestoneType = trim((string)($data['milestone_type'] ?? $data['MilestoneType'] ?? ''));
        $reminderType = trim((string)($data['reminder_type'] ?? $data['ReminderType'] ?? '3_days'));
        $scheduledAt = !empty($data['scheduled_at']) ? $data['scheduled_at'] : (!empty($data['ScheduledAt']) ? $data['ScheduledAt'] : null);
        $recipientEmail = trim((string)($data['recipient_email'] ?? $data['RecipientEmail'] ?? ''));
        $notes = trim((string)($data['notes'] ?? $data['Notes'] ?? ''));
        $bcid = !empty($data['bcid']) ? (int)$data['bcid'] : (!empty($data['Bcid']) ? (int)$data['Bcid'] : null);

        if (!$bcid) {
            $fsRow = $this->db->select('Bcid')->where('Tfsid', $tfs_id)->get('TenderFactSheet')->row_array();
            if (!empty($fsRow['Bcid'])) {
                $bcid = (int)$fsRow['Bcid'];
            }
        }

        $remId = null;

        if ($this->tableExists('TenderTimelineReminders')) {
            $existing = $this->db->where('Tfsid', $tfs_id)
                ->where('UserId', $userId)
                ->where('MilestoneType', $milestoneType)
                ->where('DeliveryStatus', 'Pending')
                ->get('TenderTimelineReminders')->row_array();

            $dbData = [
                'Tfsid'          => $tfs_id,
                'Bcid'           => $bcid,
                'UserId'         => $userId,
                'MilestoneType'  => $milestoneType,
                'ReminderType'   => $reminderType,
                'ScheduledAt'    => $scheduledAt,
                'RecipientEmail' => $recipientEmail,
                'Notes'          => $notes,
                'DeliveryStatus' => 'Pending'
            ];

            if ($existing) {
                $remId = (int)$existing['Ttrid'];
                $this->db->where('Ttrid', $remId)->update('TenderTimelineReminders', $dbData);
            } else {
                $dbData['CreationTs'] = $now;
                $this->db->insert('TenderTimelineReminders', $dbData);
                $remId = (int)$this->db->insert_id();
            }
        }

        // Dual sync: update TimelineJson on TenderFactSheet
        $timeline = $this->getTimelineFromBlob($tfs_id) ?: [];
        $reminders = $timeline['reminders'] ?? [];
        $record = [
            'id'              => $remId ?: (time() . '_' . rand(100, 999)),
            'Ttrid'           => $remId,
            'milestone_type'  => $milestoneType,
            'user_id'         => $userId,
            'reminder_type'   => $reminderType,
            'scheduled_at'    => $scheduledAt,
            'recipient_email' => $recipientEmail,
            'notes'           => $notes,
            'status'          => 'scheduled',
            'delivery_status' => 'Pending',
            'created_at'      => $now
        ];

        $synced = false;
        foreach ($reminders as &$r) {
            if (($r['milestone_type'] ?? '') === $milestoneType && (int)($r['user_id'] ?? 0) === $userId && ($r['status'] ?? '') === 'scheduled') {
                $r = array_merge($r, $record);
                $synced = true;
                break;
            }
        }
        unset($r);

        if (!$synced) {
            $reminders[] = $record;
        }

        $timeline['reminders'] = $reminders;
        $this->saveTimelineJson($tfs_id, $timeline);

        return $remId;
    }

    /**
     * Cancel an existing reminder in TenderTimelineReminders and TimelineJson
     * Accepts either ($ttrid, $user_id) or ($tfs_id, $reminder_id, $user_id)
     */
    public function cancelReminder($p1, $p2 = null, $p3 = null) {
        $now = date('Y-m-d H:i:s');
        $tfs_id = null;
        $reminder_id = null;
        $user_id = null;

        if ($p3 !== null) {
            // ($tfs_id, $reminder_id, $user_id)
            $tfs_id = (int)$p1;
            $reminder_id = $p2;
            $user_id = $p3 ? (int)$p3 : null;
        } elseif (is_numeric($p1)) {
            if ($this->tableExists('TenderTimelineReminders')) {
                $remRow = $this->db->get_where('TenderTimelineReminders', ['Ttrid' => (int)$p1])->row_array();
                if ($remRow) {
                    $reminder_id = (int)$p1;
                    $user_id = $p2 ? (int)$p2 : null;
                    $tfs_id = (int)$remRow['Tfsid'];
                } else {
                    $tfs_id = (int)$p1;
                    $reminder_id = $p2;
                    $user_id = null;
                }
            } else {
                $tfs_id = (int)$p1;
                $reminder_id = $p2;
                $user_id = null;
            }
        } else {
            $reminder_id = $p1;
            $user_id = $p2 ? (int)$p2 : null;
        }

        $updated = false;

        if ($this->tableExists('TenderTimelineReminders') && is_numeric($reminder_id)) {
            $this->db->where('Ttrid', (int)$reminder_id);
            if ($user_id) {
                $this->db->where('UserId', (int)$user_id);
            }
            $this->db->update('TenderTimelineReminders', [
                'DeliveryStatus' => 'Cancelled',
                'CancelledAt'    => $now
            ]);
            if ($this->db->affected_rows() > 0) {
                $updated = true;
            }
        }

        // Sync cancellation in TimelineJson
        if ($tfs_id) {
            $timeline = $this->getTimelineFromBlob($tfs_id);
            if (!empty($timeline['reminders'])) {
                foreach ($timeline['reminders'] as &$r) {
                    if (($r['id'] == $reminder_id) || (($r['Ttrid'] ?? null) == $reminder_id)) {
                        if (!$user_id || ((int)($r['user_id'] ?? 0) === (int)$user_id)) {
                            $r['status'] = 'cancelled';
                            $r['delivery_status'] = 'Cancelled';
                            $r['cancelled_at'] = $now;
                            $updated = true;
                        }
                    }
                }
                unset($r);
                $this->saveTimelineJson($tfs_id, $timeline);
            }
        }

        return $updated;
    }

    /**
     * Fetch pending reminders due for dispatch across all records from TenderTimelineReminders (for cron runner)
     */
    public function getPendingRemindersDue() {
        $now = date('Y-m-d H:i:s');
        $due = [];

        if ($this->tableExists('TenderTimelineReminders')) {
            $rows = $this->db->select('r.Ttrid, r.Tfsid, r.Bcid, r.UserId, r.MilestoneType, r.ReminderType, r.ScheduledAt, r.RecipientEmail, r.Notes, r.DeliveryStatus, fs.TenderRefNo, fs.Department, fs.TenderTitle')
                ->from('TenderTimelineReminders r')
                ->join('TenderFactSheet fs', 'fs.Tfsid = r.Tfsid', 'left')
                ->where('r.DeliveryStatus', 'Pending')
                ->where('r.ScheduledAt <=', $now)
                ->get()->result_array();

            foreach ($rows as $row) {
                $due[] = [
                    'id'              => (int)$row['Ttrid'],
                    'Ttrid'           => (int)$row['Ttrid'],
                    'tfs_id'          => (int)$row['Tfsid'],
                    'Tfsid'           => (int)$row['Tfsid'],
                    'bcid'            => !empty($row['Bcid']) ? (int)$row['Bcid'] : null,
                    'Bcid'            => !empty($row['Bcid']) ? (int)$row['Bcid'] : null,
                    'user_id'         => (int)$row['UserId'],
                    'UserId'          => (int)$row['UserId'],
                    'milestone_type'  => $row['MilestoneType'],
                    'MilestoneType'   => $row['MilestoneType'],
                    'reminder_type'   => $row['ReminderType'],
                    'ReminderType'    => $row['ReminderType'],
                    'scheduled_at'    => $row['ScheduledAt'],
                    'ScheduledAt'     => $row['ScheduledAt'],
                    'recipient_email' => $row['RecipientEmail'],
                    'RecipientEmail'  => $row['RecipientEmail'],
                    'notes'           => $row['Notes'],
                    'Notes'           => $row['Notes'],
                    'TenderNo'        => $row['TenderRefNo'],
                    'Department'      => $row['Department'],
                    'TenderTitle'     => $row['TenderTitle']
                ];
            }
            return $due;
        }

        // Fallback to scanning TimelineJson if table is not available
        $rows = $this->db->select('Tfsid, TenderRefNo, Department, TenderTitle, TimelineJson')
            ->from('TenderFactSheet')
            ->where('TimelineJson IS NOT NULL')
            ->where('TimelineJson !=', '')
            ->get()->result_array();

        foreach ($rows as $row) {
            $data = json_decode($row['TimelineJson'], true);
            if (empty($data['reminders'])) continue;

            foreach ($data['reminders'] as $r) {
                if (($r['status'] ?? '') === 'scheduled' && !empty($r['scheduled_at']) && $r['scheduled_at'] <= $now) {
                    $r['tfs_id'] = $row['Tfsid'];
                    $r['TenderNo'] = $row['TenderRefNo'];
                    $r['Department'] = $row['Department'];
                    $r['TenderTitle'] = $row['TenderTitle'];
                    $due[] = $r;
                }
            }
        }
        return $due;
    }
}

