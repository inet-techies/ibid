<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/Tender_dictionary.php';
require_once __DIR__ . '/Date_extractor.php';

/**
 * Universal Candidate Ranker & Confidence Engine
 *
 * Implements multi-strategy candidate ranking, cross-validation, conflict resolution,
 * detailed date candidate audit tracking, and 0-100 confidence scoring.
 */
class Candidate_ranker
{
    const THRESHOLD_AUTOFILL = 90;
    const THRESHOLD_REVIEW   = 70;

    /**
     * Rank and resolve candidates for a specific field, logging selection and rejection reasons
     *
     * @param string $field Field name
     * @param array $candidates List of candidate arrays
     * @param array &$dateAuditTrail Reference to global date candidate audit log
     * @return array Standardized field result
     */
    public static function resolveField($field, array $candidates, array &$dateAuditTrail = [])
    {
        $isDateField = (strpos($field, 'date') !== false || strpos($field, 'time') !== false || $field === 'query_deadline');

        // Default empty structure if no candidate was found
        if (empty($candidates)) {
            return [
                'value'      => null,
                'raw_value'  => null,
                'confidence' => 0,
                'status'     => 'not_found',
                'method'     => null,
                'page'       => null,
                'evidence'   => null,
                'warning'    => null
            ];
        }

        // Method priority weighting: atomic schedule_table records hold highest authority
        $methodWeights = [
            'schedule_table' => 22,
            'table_based'    => 16,
            'schedule_scan'  => 15,
            'label_based'    => 12,
            'layout_aware'   => 8,
            'context_based'  => 4,
            'php_semantic'   => 3
        ];

        // Evaluate and score each candidate
        $scoredCandidates = [];
        $excludePatterns = Tender_dictionary::getExcludePatterns($field);

        foreach ($candidates as $cand) {
            $val = $cand['value'];
            if ($val === null || $val === '') continue;

            $score = isset($cand['confidence']) ? (int)$cand['confidence'] : 50;
            $method = isset($cand['method']) ? $cand['method'] : 'context_based';
            $evidence = $cand['evidence'] ?? '';
            $rejectionReason = null;

            // Check field-level mutual exclusion patterns
            // Bug 1 Fix: schedule_table candidates are already pre-classified by Tender_schedule_parser on their own row.
            // Never reject schedule_table candidates using broad text exclusion.
            // For other methods, evaluate exclusion patterns ONLY on the candidate's own label, never bleeding across rows.
            if (!empty($excludePatterns) && $method !== 'schedule_table') {
                $targetForExclusion = '';
                if (!empty($cand['label'])) {
                    $targetForExclusion = $cand['label'];
                } elseif (in_array($method, ['table_based', 'schedule_scan', 'label_based'])) {
                    $firstLine = strtok($evidence, "\r\n");
                    $targetForExclusion = preg_match('/^([^:|\t\–—]{3,80})/u', $firstLine, $lblM) ? $lblM[1] : $firstLine;
                }

                if (!empty($targetForExclusion)) {
                    foreach ($excludePatterns as $exPat) {
                        if (preg_match($exPat, $targetForExclusion)) {
                            $rejectionReason = "Activity label matched exclusion pattern ({$exPat}) for field {$field}";
                            break;
                        }
                    }
                }
            }

            if ($rejectionReason !== null) {
                if ($isDateField) {
                    $dateAuditTrail[] = [
                        'candidate_text'   => $cand['raw_value'] ?? (string)$val,
                        'page_number'      => $cand['page'] ?? 1,
                        'associated_field' => $field,
                        'evidence'         => $evidence,
                        'confidence'       => 0,
                        'status'           => 'rejected',
                        'reason'           => $rejectionReason
                    ];
                }
                continue;
            }

            // Add method weight
            $score += $methodWeights[$method] ?? 0;

            // Early pages or official schedule section bonus
            if (isset($cand['page']) && $cand['page'] >= 1 && $cand['page'] <= 3) {
                $score += 5;
            }

            // Field-specific validation adjustments
            $validation = self::validateCandidate($field, $val, $cand);
            if (!$validation['valid']) {
                if ($isDateField) {
                    $dateAuditTrail[] = [
                        'candidate_text'   => $cand['raw_value'] ?? (string)$val,
                        'page_number'      => $cand['page'] ?? 1,
                        'associated_field' => $field,
                        'evidence'         => $evidence,
                        'confidence'       => 0,
                        'status'           => 'rejected',
                        'reason'           => 'Failed data type or calendar date validation'
                    ];
                }
                continue;
            }
            $score += $validation['bonus'];

            // Cap confidence between 10 and 99
            $finalScore = min(99, max(10, $score));

            $scoredCandidates[] = [
                'value'           => $validation['cleaned_value'],
                'raw_value'       => $cand['raw_value'] ?? (string)$val,
                'original_amount' => $cand['original_amount'] ?? ($cand['raw_value'] ?? (string)$val),
                'amount_in_words' => $cand['amount_in_words'] ?? '',
                'source'          => $cand['source'] ?? '',
                'confidence'      => $finalScore,
                'method'          => $method,
                'page'            => $cand['page'] ?? null,
                'label'           => $cand['label'] ?? '',
                'evidence'        => $evidence,
                'is_corrigendum'  => false
            ];
        }

        if (empty($scoredCandidates)) {
            return [
                'value'      => null,
                'raw_value'  => null,
                'confidence' => 0,
                'status'     => 'not_found',
                'method'     => null,
                'page'       => null,
                'evidence'   => null,
                'warning'    => 'Candidates failed validation or mutual exclusion checks'
            ];
        }

        // Bug 2 Fix: Sort candidates using composite score:
        // Priority: schedule_table (100) > table_based (80) > schedule_scan (60) > label_based (40) > context_based (10)
        // Combined with exact label affinity and confidence so exact activity labels always outrank generic matches.
        usort($scoredCandidates, function($a, $b) use ($field) {
            $methodWeights = [
                'section_heading'   => 120,
                'table_column_grid' => 110,
                'schedule_table'    => 100,
                'table_based'       => 80,
                'table_row'         => 80,
                'schedule_scan'     => 60,
                'label_based'       => 40,
                'layout_aware'      => 20,
                'context_sentence'  => 15,
                'context_based'     => 10,
                'php_semantic'      => 5
            ];

            $wA = $methodWeights[$a['method'] ?? ''] ?? 0;
            $wB = $methodWeights[$b['method'] ?? ''] ?? 0;

            $affA = self::computeLabelAffinity($field, $a['label'] ?? $a['evidence'] ?? '', $a['method'] ?? '');
            $affB = self::computeLabelAffinity($field, $b['label'] ?? $b['evidence'] ?? '', $b['method'] ?? '');

            $totalA = ($wA * 100) + ($affA * 50) + ($a['confidence'] ?? 0);
            $totalB = ($wB * 100) + ($affB * 50) + ($b['confidence'] ?? 0);

            if ($totalA !== $totalB) {
                return $totalB - $totalA;
            }

            // Earliest page wins
            $pA = $a['page'] ?? 999;
            $pB = $b['page'] ?? 999;
            return $pA - $pB;
        });

        $best = $scoredCandidates[0];

        // Check for conflicts: if second candidate has similar high score but different value
        $warning = null;
        if (count($scoredCandidates) > 1) {
            $second = $scoredCandidates[1];
            if ($second['value'] !== $best['value'] && $second['confidence'] >= 75) {
                if (($best['confidence'] - $second['confidence']) < 10) {
                    $best['confidence'] = max(70, $best['confidence'] - 15);
                    $warning = 'Multiple conflicting values identified (' . $best['raw_value'] . ' vs ' . $second['raw_value'] . '); verify manually.';
                }
            }
        }

        // Determine extraction status based on threshold
        $status = 'not_found';
        if ($best['confidence'] >= self::THRESHOLD_AUTOFILL) {
            $status = 'extracted';
        } elseif ($best['confidence'] >= self::THRESHOLD_REVIEW) {
            $status = 'review_needed';
        } else {
            $status = 'not_found';
            $best['value'] = null; // Blank for values below threshold
        }

        // Log to date audit trail
        if ($isDateField) {
            foreach ($scoredCandidates as $idx => $sc) {
                $isSelected = ($idx === 0 && $status !== 'not_found');
                $dateAuditTrail[] = [
                    'candidate_text'   => $sc['raw_value'],
                    'page_number'      => $sc['page'] ?? 1,
                    'associated_field' => $field,
                    'evidence'         => $sc['evidence'],
                    'confidence'       => $sc['confidence'],
                    'status'           => $isSelected ? 'selected' : 'rejected',
                    'reason'           => $isSelected 
                        ? ("Selected as highest-confidence candidate ({$sc['confidence']}%) via {$sc['method']}") 
                        : ($status === 'not_found' ? 'Confidence below acceptance threshold' : 'Superseded by higher-confidence candidate')
                ];
            }
        }

        return [
            'value'           => $best['value'],
            'raw_value'       => $best['raw_value'],
            'original_amount' => $best['original_amount'] ?? $best['raw_value'],
            'amount_in_words' => $best['amount_in_words'] ?? '',
            'source'          => $best['source'] ?? '',
            'confidence'      => $best['confidence'],
            'status'          => $status,
            'method'          => $best['method'],
            'page'            => $best['page'],
            'evidence'        => $best['evidence'],
            'warning'         => $warning
        ];
    }

    /**
     * Validate candidate according to field data type rules
     *
     * @param string $field
     * @param mixed $val
     * @param array $cand
     * @return array ['valid' => bool, 'cleaned_value' => mixed, 'bonus' => int]
     */
    protected static function validateCandidate($field, $val, array $cand)
    {
        $res = ['valid' => true, 'cleaned_value' => $val, 'bonus' => 0];

        // Dates validation: Must be valid ISO YYYY-MM-DD
        if (strpos($field, 'date') !== false || $field === 'query_deadline') {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $val, $m)) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $y = (int)$m[1]; $mon = (int)$m[2]; $d = (int)$m[3];
            if (!checkdate($mon, $d, $y) || $y < 2015 || $y > 2040) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $res['bonus'] += 6;
            return $res;
        }

        // Time validation
        if (strpos($field, 'time') !== false) {
            $normTime = Date_extractor::normalizeTime($val);
            if ($normTime === null) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $res['cleaned_value'] = $normTime;
            $res['bonus'] += 5;
            return $res;
        }

        // Amounts validation: EMD, Tender Fee
        if ($field === 'emd_value' || $field === 'tender_fee') {
            if (!is_numeric($val) && $val !== '0') {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $num = (float)$val;
            if ($num < 0) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            if ($field === 'tender_fee' && $num > 500000) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            if ($field === 'emd_value' && $num > 0 && $num < 500) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $bonus = 5;
            if (isset($cand['method'])) {
                if ($cand['method'] === 'section_heading') $bonus += 15;
                elseif ($cand['method'] === 'table_column_grid') $bonus += 12;
                elseif ($cand['method'] === 'table_row') $bonus += 10;
            }
            if (!empty($cand['amount_in_words'])) {
                $bonus += 10;
            }
            $res['cleaned_value'] = (string)(int)round($num);
            $res['bonus'] += $bonus;
            return $res;
        }

        // Tender Reference No validation
        if ($field === 'tender_reference') {
            $val = trim((string)$val);
            if (strlen($val) < 4 || strlen($val) > 70) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            if (preg_match('/^\d{6,10}$/', $val)) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            if (!preg_match('/[0-9\/_\-]/', $val)) {
                return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
            }
            $res['cleaned_value'] = $val;
            $res['bonus'] += 5;
            return $res;
        }

        // Tender Portal URL validation
        if ($field === 'tender_portal') {
            $val = trim((string)$val);
            if (filter_var($val, FILTER_VALIDATE_URL) || preg_match('/\b[a-zA-Z0-9.\-_]+\.(?:gov\.in|nic\.in|in|com|org)\b/i', $val)) {
                $res['bonus'] += 8;
                return $res;
            }
            return ['valid' => false, 'cleaned_value' => null, 'bonus' => 0];
        }

        return $res;
    }

    /**
     * Compute activity label affinity score for specific tender fields
     * Rewards exact activity matches and heavily penalizes mismatched activity contexts.
     *
     * @param string $field Target field name
     * @param string $label Candidate activity label or evidence
     * @param string $method Extraction method
     * @return int Affinity score
     */
    protected static function computeLabelAffinity($field, $label, $method)
    {
        if ($method === 'schedule_table') {
            return 50; // Guaranteed top priority from official schedule parser
        }
        $lbl = strtolower($label);
        if (empty($lbl)) return 0;

        switch ($field) {
            case 'submission_date':
            case 'bid_submission_end_time':
            case 'submission_time':
                if (strpos($lbl, 'bid submission') !== false || strpos($lbl, 'closing of submission') !== false || strpos($lbl, 'bid due') !== false) {
                    if (strpos($lbl, 'quer') === false && strpos($lbl, 'clarif') === false) {
                        return 40;
                    }
                }
                if (strpos($lbl, 'quer') !== false || strpos($lbl, 'clarif') !== false || strpos($lbl, 'response') !== false) {
                    return -50; // Heavy penalty for pre-bid query / response submission
                }
                break;

            case 'pre_bid_date':
            case 'prebid_meeting_time':
                if (strpos($lbl, 'meeting') !== false || strpos($lbl, 'conference') !== false) {
                    return 40;
                }
                if (strpos($lbl, 'quer') !== false && strpos($lbl, 'meeting') === false) {
                    return -30;
                }
                break;

            case 'published_date':
            case 'tender_publication_time':
                if (strpos($lbl, 'publication') !== false || strpos($lbl, 'published on') !== false || strpos($lbl, 'date of uploading') !== false) {
                    return 40;
                }
                if (strpos($lbl, 'dated') !== false && strpos($lbl, 'publication') === false) {
                    return -20; // Tender notice issue date is not tender publication date
                }
                break;

            case 'document_download_start_date':
            case 'document_sale_start_time':
                if (strpos($lbl, 'sale start') !== false || strpos($lbl, 'download start') !== false || strpos($lbl, 'publish/download/sale start') !== false) {
                    return 40;
                }
                break;
        }

        return 0;
    }
}
