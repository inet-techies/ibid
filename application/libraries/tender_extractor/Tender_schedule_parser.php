<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/Date_extractor.php';

/**
 * Universal Tender Schedule & Table Parser
 *
 * Implements atomic milestone record extraction:
 * - Activity description, Date, and Time are ALWAYS extracted and preserved together as one logical record.
 * - Extracts values from pipe-delimited tables, multi-column aligned text, tabbed schedules, and key-value lines.
 * - Accurately classifies all 12 standard tender milestones without guessing or cross-milestone bleeding.
 */
class Tender_schedule_parser
{
    // The 12 standard tender milestones
    const M_PUBLICATION          = 'tender_publication';
    const M_DOC_SALE_START       = 'document_sale_start';
    const M_DOC_SALE_END         = 'document_sale_end';
    const M_PREBID_QUERY_START   = 'prebid_query_start';
    const M_PREBID_QUERY_END     = 'prebid_query_end';
    const M_PREBID_MEETING       = 'prebid_meeting';
    const M_PREBID_RESPONSE      = 'prebid_response';
    const M_SUBMISSION_START     = 'bid_submission_start';
    const M_SUBMISSION_END       = 'bid_submission_end';
    const M_PQ_OPENING           = 'pq_bid_opening';
    const M_TECHNICAL_OPENING    = 'technical_bid_opening';
    const M_FINANCIAL_OPENING    = 'financial_bid_opening';

    /**
     * Map milestone key to standard dictionary field name and time field name
     */
    public static $fieldMapping = [
        self::M_PUBLICATION => [
            'date_field' => 'published_date',
            'time_field' => 'tender_publication_time'
        ],
        self::M_DOC_SALE_START => [
            'date_field' => 'document_download_start_date',
            'time_field' => 'document_sale_start_time'
        ],
        self::M_DOC_SALE_END => [
            'date_field' => 'document_download_end_date',
            'time_field' => 'document_sale_end_time'
        ],
        self::M_PREBID_QUERY_START => [
            'date_field' => 'prebid_query_start_date',
            'time_field' => 'prebid_query_start_time'
        ],
        self::M_PREBID_QUERY_END => [
            'date_field' => 'query_deadline',
            'time_field' => 'prebid_query_end_time'
        ],
        self::M_PREBID_MEETING => [
            'date_field' => 'pre_bid_date',
            'time_field' => 'prebid_meeting_time'
        ],
        self::M_PREBID_RESPONSE => [
            'date_field' => 'prebid_response_date',
            'time_field' => 'prebid_response_time'
        ],
        self::M_SUBMISSION_START => [
            'date_field' => 'bid_submission_start_date',
            'time_field' => 'bid_submission_start_time'
        ],
        self::M_SUBMISSION_END => [
            'date_field' => 'submission_date',
            'time_field' => 'submission_time'
        ],
        self::M_PQ_OPENING => [
            'date_field' => 'pq_bid_opening_date',
            'time_field' => 'pq_bid_opening_time'
        ],
        self::M_TECHNICAL_OPENING => [
            'date_field' => 'technical_opening_date',
            'time_field' => 'technical_opening_time'
        ],
        self::M_FINANCIAL_OPENING => [
            'date_field' => 'financial_opening_date',
            'time_field' => 'financial_opening_time'
        ],
    ];

    /**
     * Classify an activity label into one of the 12 standard milestones
     *
     * @param string $label
     * @return string|null Milestone key or null if not a recognized milestone
     */
    public static function classifyActivity($label)
    {
        $lbl = trim(preg_replace('/\s+/', ' ', $label));
        // Normalize common OCR misrecognitions
        $lbl = preg_replace('/\b0pening\b/i', 'opening', $lbl);
        $lbl = preg_replace('/\bd0cuments?\b/i', 'document', $lbl);

        if (strlen($lbl) < 3) return null;

        // Discard table headers / non-milestone rows
        if (preg_match('/^(?:sl\.?\s*no\.?|s\.no|item\s+no|description|event|activity|particulars|milestones?|schedule|critical\s+dates?|key\s+dates?|stage|information)$/i', $lbl)) {
            return null;
        }

        // 12. Financial Bid Opening
        if (preg_match('/\b(?:financial|price|commercial|cover\s*2)\b/i', $lbl)) {
            if (preg_match('/\b(?:opening|open|opened|evaluation)\b/i', $lbl)) {
                return self::M_FINANCIAL_OPENING;
            }
        }

        // 11. Technical Bid Opening
        if (preg_match('/\b(?:technical|cover\s*1)\b/i', $lbl)) {
            if (preg_match('/\b(?:opening|open|opened|evaluation)\b/i', $lbl)) {
                return self::M_TECHNICAL_OPENING;
            }
        }

        // 10. PQ / Pre-Qualification Bid Opening
        if (preg_match('/\b(?:pq|pre[\s\-]qualification|eligibility|cover\s*0)\b/i', $lbl)) {
            if (preg_match('/\b(?:opening|open|opened)\b/i', $lbl)) {
                return self::M_PQ_OPENING;
            }
        }

        // Generic Bid Opening (when technical/financial not specified, default is Technical Opening)
        if (preg_match('/\b(?:tender|bid|bids)\s+opening\b/i', $lbl) || preg_match('/\bopening\s+of\s+(?:the\s+)?(?:bids?|tender|rfp)\b/i', $lbl)) {
            if (!preg_match('/\b(?:financial|price|commercial)\b/i', $lbl)) {
                return self::M_TECHNICAL_OPENING;
            }
        }

        // 7. Pre-bid Response / Reply to queries
        if (preg_match('/\b(?:reply|response|replies)\b/i', $lbl)) {
            if (preg_match('/\b(?:pre[\s\-]bid|prebid|queries|query|clarifications?|vendor)\b/i', $lbl) || preg_match('/\bresponse\s+submission\b/i', $lbl)) {
                return self::M_PREBID_RESPONSE;
            }
        }

        // 4 & 5. Pre-bid Query Start / End
        if (preg_match('/\b(?:queries|query|clarification|clarifications)\b/i', $lbl)) {
            if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
                return self::M_PREBID_QUERY_START;
            }
            if (preg_match('/\b(?:last|end|clos|deadline|cutoff|receipt|receiving|due|reaching)\b/i', $lbl)) {
                return self::M_PREBID_QUERY_END;
            }
            if (preg_match('/\bseeking\s+clarification\b/i', $lbl)) {
                return self::M_PREBID_QUERY_END;
            }
        }

        // 6. Pre-bid Meeting
        if (preg_match('/\b(?:pre[\s\-]bid|prebid)\b/i', $lbl)) {
            if (preg_match('/\b(?:meeting|conference|discussion|briefing)\b/i', $lbl)) {
                return self::M_PREBID_MEETING;
            }
        }

        // 8 & 9. Bid Submission Start / End (Strict: exclude queries / clarification submission)
        if (!preg_match('/\b(?:queries|query|clarification|clarifications)\b/i', $lbl)) {
            if (preg_match('/\b(?:submission|submitting|submit)\b/i', $lbl) || preg_match('/\bclosing\s+of\s+submission\b/i', $lbl) || preg_match('/\bbid\s+(?:end|due|closing)\b/i', $lbl)) {
                if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
                    return self::M_SUBMISSION_START;
                }
                if (preg_match('/\b(?:end|clos|closing|last|deadline|due|cutoff|upto|till)\b/i', $lbl) || preg_match('/\bclosing\s+of\s+submission\b/i', $lbl)) {
                    return self::M_SUBMISSION_END;
                }
                if (preg_match('/\b(?:date|last\s+date)\s+(?:and\s+time\s+)?(?:of|for)\s+submission\b/i', $lbl)) {
                    return self::M_SUBMISSION_END;
                }
            }
        }

        // 2 & 3. Document Sale / Download Start / End
        if (preg_match('/\b(?:download|downloading|sale|availability|purchase)\b/i', $lbl)) {
            if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
                return self::M_DOC_SALE_START;
            }
            if (preg_match('/\b(?:end|clos|closing|last|deadline|to)\b/i', $lbl)) {
                return self::M_DOC_SALE_END;
            }
            if (preg_match('/\bavailability\s+of\b/i', $lbl)) {
                return self::M_DOC_SALE_START;
            }
        }

        // 1. Tender Publication
        if (preg_match('/\b(?:publication|publishing|published)\b/i', $lbl) || preg_match('/\bdate\s+of\s+uploading\b/i', $lbl) || preg_match('/\btender\s+date\b/i', $lbl) || preg_match('/\bnit\s+date\b/i', $lbl)) {
            if (!preg_match('/\b(?:submission|opening|pre[\s\-]bid|download|sale)\b/i', $lbl)) {
                return self::M_PUBLICATION;
            }
        }

        return null;
    }

    /**
     * Parse atomic milestone records across the document
     *
     * @param array $pageMap [pageNo => pageText]
     * @return array List of atomic milestone records
     */
    public static function extractMilestones(array $pageMap)
    {
        $records = [];

        foreach ($pageMap as $pNo => $pageText) {
            $lines = preg_split('/[\r\n]+/', $pageText);
            $total = count($lines);

            for ($i = 0; $i < $total; $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                // 1. Pipe-separated tables: "| Sl No | Activity | Date | Time |" or "| Activity | Date & Time |"
                if (strpos($line, '|') !== false) {
                    $cells = array_values(array_filter(array_map('trim', explode('|', $line)), function($c) { return $c !== ''; }));
                    if (count($cells) >= 2) {
                        $label = '';
                        $val = '';
                        if (preg_match('/^(?:sl\.?\s*no\.?|\d+[\.\)]?|[a-z][\.\)])$/i', $cells[0]) && count($cells) >= 3) {
                            $label = $cells[1];
                            $val = $cells[2] . (isset($cells[3]) ? ' ' . $cells[3] : '');
                        } else {
                            $label = $cells[0];
                            $val = $cells[1] . (isset($cells[2]) ? ' ' . $cells[2] : '');
                        }

                        $mKey = self::classifyActivity($label);
                        if ($mKey) {
                            $dt = Date_extractor::extractFromPassage($val);
                            if ($dt['iso_date']) {
                                $records[] = [
                                    'milestone' => $mKey,
                                    'label'     => $label,
                                    'date'      => $dt['iso_date'],
                                    'time'      => $dt['time'],
                                    'page'      => $pNo,
                                    'evidence'  => $line,
                                    'confidence'=> 96
                                ];
                            }
                        }
                    }
                    continue;
                }

                // 2. Columnar schedule lines with multi-space, tab, colon, or dash delimiter
                // e.g. "Date and time for opening of technical bid document.        18-11-2026 at 11.00 AM"
                // e.g. "Pre-Bid Conference : Dt. 03-09-2026 by 12:30 PM"
                if (preg_match('/^(.*?)(?:\s{2,}|\t+|[:\–—]\s*)(.*?\b\d{1,2}\s*[\.\/\-]\s*\d{1,2}\s*[\.\/\-]\s*\d{2,4}.*)$/i', $line, $lm)) {
                    $candLabel = trim(rtrim(trim($lm[1]), ':.-–'));
                    $candVal   = trim($lm[2]);

                    // Remove serial number prefix if present
                    if (preg_match('/^(?:\d{1,2}[\.\)]|[a-z][\.\)])\s*(.*)$/i', $candLabel, $snum)) {
                        $candLabel = trim($snum[1]);
                    }

                    $mKey = self::classifyActivity($candLabel);
                    if ($mKey) {
                        // Check for date range e.g. "25-08-2026 to 19-09-2026 at 3:00 PM"
                        if (preg_match('/(\d{1,2}\s*[\.\/\-]\s*\d{1,2}\s*[\.\/\-]\s*\d{2,4}[^to]*?)\s+(?:to|till|-)\s+(\d{1,2}\s*[\.\/\-]\s*\d{1,2}\s*[\.\/\-]\s*\d{2,4}.*)/i', $candVal, $rm)) {
                            $startDt = Date_extractor::extractFromPassage($rm[1]);
                            $endDt   = Date_extractor::extractFromPassage($rm[2]);

                            if ($mKey === self::M_DOC_SALE_START || $mKey === self::M_DOC_SALE_END) {
                                if ($startDt['iso_date']) {
                                    $records[] = [
                                        'milestone' => self::M_DOC_SALE_START,
                                        'label'     => $candLabel . ' (Start)',
                                        'date'      => $startDt['iso_date'],
                                        'time'      => $startDt['time'],
                                        'page'      => $pNo,
                                        'evidence'  => $line,
                                        'confidence'=> 95
                                    ];
                                }
                                if ($endDt['iso_date']) {
                                    $records[] = [
                                        'milestone' => self::M_DOC_SALE_END,
                                        'label'     => $candLabel . ' (End)',
                                        'date'      => $endDt['iso_date'],
                                        'time'      => $endDt['time'],
                                        'page'      => $pNo,
                                        'evidence'  => $line,
                                        'confidence'=> 95
                                    ];
                                }
                                continue;
                            }
                        }

                        $dt = Date_extractor::extractFromPassage($candVal);
                        if ($dt['iso_date']) {
                            $records[] = [
                                'milestone' => $mKey,
                                'label'     => $candLabel,
                                'date'      => $dt['iso_date'],
                                'time'      => $dt['time'],
                                'page'      => $pNo,
                                'evidence'  => $line,
                                'confidence'=> 95
                            ];
                        }
                    }
                }
            }
        }

        return $records;
    }
}

