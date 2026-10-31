<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Timeline_model
 *
 * Lightweight DB-driven model for Tender Timeline Intelligence.
 * Builds the timeline directly from existing database records:
 * - `tenderfactsheet.ImportantDates`
 * - `BidCreationDetails` (`PublishedDate`, `SubmissionDate`, `OpeningDate`)
 * Zero heavy external libraries or PDF re-parsing needed.
 */
class Timeline_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get structured timeline directly from database records
     *
     * @param int $tfs_id FactSheet ID
     * @return array|null Standardized timeline intelligence payload
     */
    public function getTimeline($tfs_id) {
        $fs = $this->db->select('Tfsid, Bcid, TenderRefNo, TenderTitle, ImportantDates, TimelineJson, DocumentName')
            ->from('tenderfactsheet')
            ->where('Tfsid', (int)$tfs_id)
            ->get()->row_array();

        if (!$fs) {
            return null;
        }

        // 1. If cached TimelineJson has milestones, check if valid; otherwise hydrate from DB
        $existingTimeline = !empty($fs['TimelineJson']) ? json_decode($fs['TimelineJson'], true) : [];
        $corrigenda = $existingTimeline['corrigenda'] ?? [];
        $reminders  = $existingTimeline['reminders'] ?? [];

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
     * Save Timeline JSON blob on tenderfactsheet table if caching is needed
     */
    public function saveTimelineJson($tfs_id, $timeline_array) {
        $json = json_encode($timeline_array, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return $this->db->where('Tfsid', (int)$tfs_id)->update('tenderfactsheet', [
            'TimelineJson' => $json
        ]);
    }

    /**
     * Get Corrigenda records directly from TimelineJson
     */
    public function getCorrigenda($tfs_id) {
        $timeline = $this->getTimeline($tfs_id);
        return $timeline['corrigenda'] ?? [];
    }

    /**
     * Get reminders directly from TimelineJson
     */
    public function getReminders($tfs_id, $user_id = null) {
        $timeline = $this->getTimeline($tfs_id);
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
     * Create or update a reminder inside TimelineJson
     */
    public function saveReminder($data) {
        $tfs_id = (int)$data['tfs_id'];
        $timeline = $this->getTimeline($tfs_id);
        if (!$timeline) return false;

        $reminders = $timeline['reminders'] ?? [];
        $now = date('Y-m-d H:i:s');

        $remId = time() . '_' . rand(100, 999);
        $record = [
            'id'              => $remId,
            'milestone_type'  => $data['milestone_type'] ?? '',
            'user_id'         => (int)($data['user_id'] ?? 1),
            'reminder_type'   => $data['reminder_type'] ?? '3_days',
            'scheduled_at'    => $data['scheduled_at'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? '',
            'notes'           => $data['notes'] ?? '',
            'status'          => 'scheduled',
            'created_at'      => $now
        ];

        // Replace if identical reminder exists for this user and milestone
        $updated = false;
        foreach ($reminders as &$r) {
            if ($r['milestone_type'] === $record['milestone_type'] && $r['user_id'] === $record['user_id'] && $r['status'] === 'scheduled') {
                $r = array_merge($r, $record);
                $updated = true;
                $remId = $r['id'];
                break;
            }
        }
        unset($r);

        if (!$updated) {
            $reminders[] = $record;
        }

        $timeline['reminders'] = $reminders;
        $this->saveTimelineJson($tfs_id, $timeline);

        return $remId;
    }

    /**
     * Cancel a reminder inside TimelineJson
     */
    public function cancelReminder($tfs_id, $reminder_id, $user_id = null) {
        $timeline = $this->getTimeline($tfs_id);
        if (!$timeline || empty($timeline['reminders'])) return false;

        foreach ($timeline['reminders'] as &$r) {
            if ($r['id'] == $reminder_id) {
                if (!$user_id || $r['user_id'] == $user_id) {
                    $r['status'] = 'cancelled';
                    $r['cancelled_at'] = date('Y-m-d H:i:s');
                }
            }
        }
        unset($r);

        return $this->saveTimelineJson($tfs_id, $timeline);
    }

    /**
     * Fetch pending reminders due for dispatch across all tenderfactsheet records (for cron runner)
     */
    public function getPendingRemindersDue() {
        $now = date('Y-m-d H:i:s');
        $rows = $this->db->select('Tfsid, TenderRefNo, Department, TenderTitle, TimelineJson')
            ->from('tenderfactsheet')
            ->where('TimelineJson IS NOT NULL')
            ->where('TimelineJson !=', '')
            ->get()->result_array();

        $due = [];
        foreach ($rows as $row) {
            $data = json_decode($row['TimelineJson'], true);
            if (empty($data['reminders'])) continue;

            foreach ($data['reminders'] as $r) {
                if (($r['status'] ?? '') === 'scheduled' && !empty($r['scheduled_at']) && $r['scheduled_at'] <= $now) {
                    $r['tfs_id'] = $row['Tfsid'];
                    $r['TenderRefNo'] = $row['TenderRefNo'];
                    $r['Department'] = $row['Department'];
                    $r['TenderTitle'] = $row['TenderTitle'];
                    $due[] = $r;
                }
            }
        }
        return $due;
    }
}

