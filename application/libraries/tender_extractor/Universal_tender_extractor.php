<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/Tender_dictionary.php';
require_once __DIR__ . '/Tender_normalizer.php';
require_once __DIR__ . '/Date_extractor.php';
require_once __DIR__ . '/Amount_extractor.php';
require_once __DIR__ . '/Candidate_ranker.php';
require_once __DIR__ . '/Php_semantic_fallback.php';
require_once __DIR__ . '/Tender_schedule_parser.php';

/**
 * Universal Tender Extraction Engine Orchestrator
 *
 * Implements a hybrid multi-strategy extraction pipeline handling Indian Central, State,
 * GeM, PSU, Municipal, and Private sector tenders across various document formats.
 * 100% PHP offline execution with zero remote API dependencies.
 */
class Universal_tender_extractor
{
    /**
     * Run universal extraction on extracted text and document structure
     *
     * @param string $rawText Raw extracted document text
     * @param string $origName Original filename
     * @param string $ext File extension
     * @param array $pageMap Optional page map [pageNo => pageText]
     * @return array Standardized payload containing 'fields', 'diagnostics', and 'ai_summary'
     */
    public static function extract($rawText, $origName = '', $ext = '', array $pageMap = [])
    {
        $startTime = microtime(true);

        // 1. Dual-Layer Text Normalization across full document
        $proc = Tender_normalizer::process($rawText);
        $normText = $proc['normalized'];
        $lines    = $proc['lines'];

        // If pageMap is provided, normalize each page
        $normPageMap = [];
        if (!empty($pageMap)) {
            foreach ($pageMap as $pNo => $pText) {
                $pProc = Tender_normalizer::process($pText);
                $normPageMap[$pNo] = $pProc['normalized'];
            }
            $normPageMap = Tender_normalizer::stripRunningHeadersFooters($normPageMap);
        } else {
            $normPageMap = [1 => $normText];
        }

        // 2. Candidate Collection Pool
        $allCandidates = [];
        $supportedFields = Tender_dictionary::getSupportedFields();
        $timeFields = [
            'tender_publication_time',
            'document_sale_start_time',
            'document_sale_end_time',
            'prebid_query_start_time',
            'prebid_query_end_time',
            'prebid_meeting_time',
            'prebid_response_time',
            'bid_submission_start_time',
            'bid_submission_end_time',
            'submission_time',
            'pq_bid_opening_time',
            'technical_opening_time',
            'financial_opening_time'
        ];
        $allFieldKeys = array_unique(array_merge($supportedFields, $timeFields));
        foreach ($allFieldKeys as $f) {
            $allCandidates[$f] = [];
        }

        // Strategy 1: Atomic Schedule Table & Schedule Block Parsing (Preserves Date & Time 1:1)
        // Prefer un-collapsed $pageMap to preserve multi-space columnar boundaries
        $schedulePageMap = !empty($pageMap) ? $pageMap : $normPageMap;
        self::extractAtomicSchedules($schedulePageMap, $allCandidates);

        // Strategy 2: Table-based Extraction (supports 2-col, 3-col, 4-col, and multiline table rows)
        self::extractTableBased($normPageMap, $allCandidates);

        // Strategy 3: Explicit Schedule Section & Schedule Line Parsing
        self::extractScheduleLines($normPageMap, $allCandidates);

        // Strategy 4: Label-based Extraction (using universal dictionary patterns)
        self::extractLabelBased($normPageMap, $lines, $allCandidates);

        // Strategy 5: Layout-aware Header Extraction (Tender Reference, Issuing Authority, Portal)
        self::extractLayoutAware($normPageMap, $normText, $allCandidates);

        // Strategy 6: Context-based Extraction
        self::extractContextBased($normPageMap, $allCandidates);

        // 3. Candidate Ranking & Strict Segregation
        $resolvedFields = [];
        $unresolvedFields = [];
        $dateAuditTrail = [];

        foreach ($allFieldKeys as $field) {
            $res = Candidate_ranker::resolveField($field, $allCandidates[$field], $dateAuditTrail);
            $resolvedFields[$field] = $res;

            if ($res['status'] === 'not_found' || $res['confidence'] < 70) {
                $unresolvedFields[] = $field;
            }
        }

        // Strict Date & Time Pairing Preservation
        // Guarantees that time is tied 1:1 to the selected date from the exact same record.
        // If a milestone date is not found in the document, its time is set to not_found.
        foreach (Tender_schedule_parser::$fieldMapping as $mKey => $mapping) {
            $dField = $mapping['date_field'];
            $tField = $mapping['time_field'];

            if (!empty($resolvedFields[$dField]['value'])) {
                $selDateVal = $resolvedFields[$dField]['value'];
                $matchedRecord = null;
                foreach ($allCandidates[$dField] as $cand) {
                    if ($cand['value'] === $selDateVal && !empty($cand['time'])) {
                        $matchedRecord = $cand;
                        break;
                    }
                }
                if ($matchedRecord && !empty($matchedRecord['time'])) {
                    $resolvedFields[$tField] = [
                        'value'      => $matchedRecord['time'],
                        'raw_value'  => $matchedRecord['time'],
                        'confidence' => $resolvedFields[$dField]['confidence'],
                        'status'     => 'extracted',
                        'method'     => $matchedRecord['method'] ?? 'schedule_table',
                        'page'       => $matchedRecord['page'] ?? null,
                        'evidence'   => $matchedRecord['evidence'] ?? null,
                        'warning'    => null
                    ];
                    if ($dField === 'submission_date') {
                        $resolvedFields['bid_submission_end_time'] = $resolvedFields[$tField];
                        $resolvedFields['submission_time']         = $resolvedFields[$tField];
                    }
                }
            } else {
                // If milestone date was not found, time must strictly be not_found / null
                $resolvedFields[$tField] = [
                    'value'      => null,
                    'raw_value'  => null,
                    'confidence' => 0,
                    'status'     => 'not_found',
                    'method'     => null,
                    'page'       => null,
                    'evidence'   => null,
                    'warning'    => null
                ];
                if ($dField === 'submission_date') {
                    $resolvedFields['bid_submission_end_time'] = $resolvedFields[$tField];
                    $resolvedFields['submission_time']         = $resolvedFields[$tField];
                }
            }
        }

        // 4. Offline PHP Semantic Fallback (Zero network calls, zero API keys)
        if (!empty($unresolvedFields)) {
            $fallbackCandidates = Php_semantic_fallback::extractTargetedFields($unresolvedFields, $normText, $normPageMap);
            foreach ($fallbackCandidates as $fKey => $cand) {
                if (!empty($cand['value'])) {
                    $allCandidates[$fKey][] = $cand;
                    // Re-resolve field with semantic candidate included
                    $resolvedFields[$fKey] = Candidate_ranker::resolveField($fKey, $allCandidates[$fKey], $dateAuditTrail);
                }
            }
        }

        // 5. Chronological Date Sequence Cross-Validation
        $resolvedFields = Date_extractor::validateSequence($resolvedFields);

        // 6. Universal Tender Title Extraction
        require_once __DIR__ . '/Tender_title_extractor.php';
        $titleRes = Tender_title_extractor::extract($normText, !empty($pageMap) ? $pageMap : $normPageMap, $origName);
        $resolvedFields['tender_title'] = [
            'value'      => $titleRes['value'],
            'raw_value'  => $titleRes['value'],
            'confidence' => $titleRes['confidence'],
            'status'     => $titleRes['status'],
            'method'     => 'tender_title_extractor',
            'page'       => null,
            'evidence'   => $titleRes['source'],
            'warning'    => null
        ];

        // 7. Diagnostics & Date Audit Report
        $endTime = microtime(true);
        $durationMs = (int)(($endTime - $startTime) * 1000);

        $foundCount = 0;
        $reviewCount = 0;
        $notFoundCount = 0;

        foreach ($resolvedFields as $fKey => $fData) {
            if ($fData['status'] === 'extracted') $foundCount++;
            elseif ($fData['status'] === 'review_needed') $reviewCount++;
            else $notFoundCount++;
        }

        $diagnostics = [
            'file_name'               => $origName,
            'file_type'               => strtolower($ext),
            'page_count'              => count($normPageMap),
            'text_length'             => strlen($normText),
            'extraction_duration_ms'  => $durationMs,
            'fields_found'            => $foundCount,
            'fields_requiring_review' => $reviewCount,
            'fields_not_found'        => $notFoundCount,
            'date_candidates'         => $dateAuditTrail
        ];

        // 7. Backward-Compatible ai_summary Structure
        $aiSummary = self::buildLegacyAiSummary($resolvedFields, $origName, $normText);

        return [
            'fields'      => $resolvedFields,
            'diagnostics' => $diagnostics,
            'ai_summary'  => $aiSummary
        ];
    }

    /**
     * Strategy 1: Table-based Extraction
     * Handles 2-column, 3-column (Index | Label | Value), 4-column (Index | Label | Date | Time),
     * and multiline table rows (where cell label is on line N and value is on line N+1).
     */
    /**
     * Strategy 1: Atomic Schedule Table & Schedule Block Parsing
     * Preserves Activity description, Date, and Time as one logical record.
     */
    protected static function extractAtomicSchedules(array $pageMap, array &$allCandidates)
    {
        $records = Tender_schedule_parser::extractMilestones($pageMap);
        foreach ($records as $rec) {
            $mKey = $rec['milestone'];
            if (!isset(Tender_schedule_parser::$fieldMapping[$mKey])) continue;

            $mapping = Tender_schedule_parser::$fieldMapping[$mKey];
            $dField = $mapping['date_field'];
            $tField = $mapping['time_field'];

            // Add date candidate
            $allCandidates[$dField][] = [
                'value'      => $rec['date'],
                'raw_value'  => $rec['date'],
                'time'       => $rec['time'],
                'confidence' => $rec['confidence'],
                'method'     => 'schedule_table',
                'page'       => $rec['page'],
                'label'      => $rec['label'],
                'evidence'   => $rec['evidence']
            ];

            // If time is present on the same row, add paired time candidate strictly from this record
            if (!empty($rec['time'])) {
                $allCandidates[$tField][] = [
                    'value'       => $rec['time'],
                    'raw_value'   => $rec['time'],
                    'paired_date' => $rec['date'],
                    'confidence'  => $rec['confidence'],
                    'method'      => 'schedule_table',
                    'page'        => $rec['page'],
                    'label'       => $rec['label'],
                    'evidence'    => $rec['evidence']
                ];
                if ($dField === 'submission_date') {
                    $allCandidates['bid_submission_end_time'][] = [
                        'value'       => $rec['time'],
                        'raw_value'   => $rec['time'],
                        'paired_date' => $rec['date'],
                        'confidence'  => $rec['confidence'],
                        'method'      => 'schedule_table',
                        'page'        => $rec['page'],
                        'label'       => $rec['label'],
                        'evidence'    => $rec['evidence']
                    ];
                }
            }
        }
    }

    protected static function extractTableBased(array $pageMap, array &$allCandidates)
    {
        foreach ($pageMap as $pNo => $pageText) {
            $pageLines = preg_split('/[\r\n]+/', $pageText);
            $totalLines = count($pageLines);

            for ($i = 0; $i < $totalLines; $i++) {
                $line = trim($pageLines[$i]);
                if (empty($line)) continue;

                // Pipe-delimited row check
                if (strpos($line, '|') !== false) {
                    $rawCells = explode('|', $line);
                    $cells = [];
                    foreach ($rawCells as $rc) {
                        $trimmed = trim($rc);
                        if ($trimmed !== '') {
                            $cells[] = $trimmed;
                        }
                    }

                    if (count($cells) >= 2) {
                        $label = '';
                        $value = '';

                        if (count($cells) === 2) {
                            $label = $cells[0];
                            $value = $cells[1];
                        } elseif (count($cells) >= 3) {
                            // If first cell is just index/serial number (e.g. "1.", "2", "Sl.No", "a)")
                            if (preg_match('/^(?:sl\.?\s*no\.?|\d+[\.\)]?|[a-z][\.\)])$/i', $cells[0])) {
                                $label = $cells[1];
                                $value = $cells[2];
                                if (isset($cells[3]) && preg_match('/(?:am|pm|hrs|hours|\d{1,2}:\d{2})/i', $cells[3])) {
                                    $value .= ' ' . $cells[3];
                                }
                            } else {
                                $label = $cells[0];
                                $value = $cells[1];
                            }
                        }

                        if (!empty($label) && !empty($value)) {
                            self::mapLabelValueToCandidate($label, $value, 'table_based', 94, $pNo, $line, $allCandidates);
                        }
                    } elseif (count($cells) === 1 && ($i + 1) < $totalLines) {
                        // Check multiline table row: line i has "| Label", line i+1 has "| Value"
                        $nextLine = trim($pageLines[$i + 1]);
                        if (strpos($nextLine, '|') !== false) {
                            $nextCells = array_values(array_filter(array_map('trim', explode('|', $nextLine))));
                            if (count($nextCells) === 1) {
                                $potLabel = $cells[0];
                                $potVal   = $nextCells[0];
                                self::mapLabelValueToCandidate($potLabel, $potVal, 'table_based', 95, $pNo, $line . ' ' . $nextLine, $allCandidates);
                                $i++; // skip next line
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Strategy 2: Explicit Schedule Section & Schedule Line Parsing
     * Recognizes lines in schedule sections like:
     * "Availability of RFP Document : Dt. 25-08-2026 to 19-09-2026 at 3:00 PM"
     * "Last date for receiving queries Dt. 31-08-2026 by 03:00 PM"
     * "Pre-Bid Conference Dt. 03-09-2026 by 12:30 PM"
     * "Last Date for Submission of RFP . Dt. 19-09-2026 at 03:00 PM"
     * "Date and Time of Opening of General & Technical Bid: Dt. 19-09-2026 at 04:00 PM"
     */
    protected static function extractScheduleLines(array $pageMap, array &$allCandidates)
    {
        // Line-based regex: Label followed by delimiter (colon, dot, hyphen, spaces) followed by date string
        $scheduleLinePattern = '/^(?:\d{1,2}[\.\)]\s*)?([A-Za-z0-9\s&()\/,\-]{4,60}?)\s*(?:[:\.\-–]\s*|\s{2,}|\t+)\s*(?:Dt\.?|Date[:\s]*)?\s*(\d{1,2}\s*[\.\/\-]\s*\d{1,2}\s*[\.\/\-]\s*\d{2,4}.*)$/i';

        foreach ($pageMap as $pNo => $pageText) {
            $lines = preg_split('/[\r\n]+/', $pageText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match($scheduleLinePattern, $line, $m)) {
                    $lbl = trim($m[1]);
                    $val = trim($m[2]);
                    self::mapLabelValueToCandidate($lbl, $val, 'schedule_scan', 95, $pNo, $line, $allCandidates);
                }
            }
        }
    }

    /**
     * Strategy 3: Label-based Extraction (Universal Dictionary Patterns)
     */
    protected static function extractLabelBased(array $pageMap, array $lines, array &$allCandidates)
    {
        $supportedFields = Tender_dictionary::getSupportedFields();

        foreach ($supportedFields as $field) {
            $patterns = Tender_dictionary::getLabelPatterns($field);
            if (empty($patterns)) continue;

            foreach ($pageMap as $pNo => $pageText) {
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $pageText, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $m) {
                            $candVal = isset($m[1]) ? trim($m[1]) : '';
                            if ($candVal === '') continue;

                            $offset = strpos($pageText, $m[0]);
                            $start = max(0, $offset - 20);
                            $evidence = substr($pageText, $start, strlen($m[0]) + 60);

                            self::addProcessedCandidate($field, $candVal, 'label_based', 90, $pNo, $evidence, $allCandidates);
                        }
                    }
                }
            }
        }
    }

    /**
     * Strategy 4: Layout-aware Header Extraction
     * Focuses on early pages for Reference Number, Issuing Authority, and Tender Portal
     */
    protected static function extractLayoutAware(array $pageMap, $normText, array &$allCandidates)
    {
        $earlyPagesText = '';
        for ($i = 1; $i <= min(3, count($pageMap)); $i++) {
            if (isset($pageMap[$i])) {
                $earlyPagesText .= "\n" . $pageMap[$i];
            }
        }
        if (empty($earlyPagesText)) {
            $earlyPagesText = substr($normText, 0, 15000);
        }

        // Layout extraction for Tender Reference No
        if (preg_match('/\b(NIT\s*No\.?|RFP\s*(?:Enquiry\s*)?No\.?|Tender\s*(?:Notice\s*)?No\.?|Bid\s*No\.?)\s*[:\t\- ]*([A-Za-z0-9][A-Za-z0-9\/_\-:\.]{3,70})/i', $earlyPagesText, $m)) {
            $allCandidates['tender_reference'][] = [
                'value'      => trim($m[2]),
                'raw_value'  => trim($m[2]),
                'confidence' => 94,
                'method'     => 'layout_aware',
                'page'       => 1,
                'evidence'   => trim($m[0])
            ];
        }

        // Layout extraction for Portal URL
        if (preg_match('/\b(https?:\/\/[a-zA-Z0-9.\-_]+\.(?:gov\.in|nic\.in|gem\.gov\.in)[^\s,;\"\'\)]*)/i', $earlyPagesText, $m)) {
            $cleanUrl = rtrim(trim($m[1]), '.');
            $allCandidates['tender_portal'][] = [
                'value'      => $cleanUrl,
                'raw_value'  => $cleanUrl,
                'confidence' => 96,
                'method'     => 'layout_aware',
                'page'       => 1,
                'evidence'   => 'Identified official government portal in tender header: ' . $cleanUrl
            ];
        } elseif (preg_match('/\b(enivida\.odisha\.gov\.in|wbtenders\.gov\.in|eprocure\.gov\.in|gem\.gov\.in|etenders\.gov\.in|mahatenders\.gov\.in|kppp\.karnataka\.gov\.in)\b/i', $earlyPagesText, $m)) {
            $cleanUrl = 'https://' . strtolower(trim($m[1]));
            $allCandidates['tender_portal'][] = [
                'value'      => $cleanUrl,
                'raw_value'  => $cleanUrl,
                'confidence' => 95,
                'method'     => 'layout_aware',
                'page'       => 1,
                'evidence'   => 'Identified government procurement portal domain: ' . $cleanUrl
            ];
        }

        // Layout extraction for Issuing Authority
        if (preg_match('/\b(ODISHA\s+COMPUTER\s+APPLICATION\s+CENTRE|WEBEL\s+TECHNOLOGY\s+LIMITED|KOLKATA\s+POLICE|MUNICIPAL\s+CORPORATION|DIRECTORATE\s+OF\s+[A-Z\s]{3,40}|GOVERNMENT\s+OF\s+[A-Z\s]{3,30}|COMMISSIONERATE\s+OF\s+POLICE)\b/iu', $earlyPagesText, $am)) {
            $cleanAuth = ucwords(strtolower(trim($am[0])));
            $allCandidates['issuing_authority'][] = [
                'value'      => $cleanAuth,
                'raw_value'  => $cleanAuth,
                'confidence' => 94,
                'method'     => 'layout_aware',
                'page'       => 1,
                'evidence'   => 'Header prominent organization: ' . trim($am[0])
            ];
        }
    }

    /**
     * Strategy 5: Context-based Extraction
     */
    protected static function extractContextBased(array $pageMap, array &$allCandidates)
    {
        foreach ($pageMap as $pNo => $pageText) {
            // EMD context match: "bidders must submit EMD of Rs. X"
            if (preg_match('/(?:EMD|Earnest\s+Money(?:\s+Deposit)?)\s+(?:of\s+)?(?:Rs\.?|INR|₹)?\s*([0-9,]+(?:\.\d{1,2})?(?:\s*(?:Lakhs?|Lacs?|Crores?|Cr))?)/i', $pageText, $em)) {
                $amtRes = Amount_extractor::parse($em[1]);
                if ($amtRes['numeric'] !== null) {
                    $allCandidates['emd_value'][] = [
                        'value'      => (string)$amtRes['numeric'],
                        'raw_value'  => $amtRes['formatted'],
                        'confidence' => 84,
                        'method'     => 'context_based',
                        'page'       => $pNo,
                        'evidence'   => trim($em[0])
                    ];
                }
            }

            // Submission date context match: "submission of bids on or before 22-09-2026"
            if (preg_match('/(?:submission\s+(?:of\s+bids?)?\s+(?:on\s+or\s+before|up\s+to|till)|bids?\s+must\s+reach\s+(?:on\s+or\s+before|by))\s+([0-9]{1,2}\s*[\.\/\-]\s*[0-9]{1,2}\s*[\.\/\-]\s*[0-9]{2,4}|[0-9]{1,2}\s+[A-Za-z]{3,9}\s+[0-9]{2,4})/i', $pageText, $sm)) {
                $iso = Date_extractor::toIsoDate($sm[1]);
                if ($iso) {
                    $allCandidates['submission_date'][] = [
                        'value'      => $iso,
                        'raw_value'  => $sm[1],
                        'confidence' => 82,
                        'method'     => 'context_based',
                        'page'       => $pNo,
                        'evidence'   => trim($sm[0])
                    ];
                }
            }
        }
    }

    /**
     * Helper to map a table label-value pair to field candidate using semantic dictionary synonyms
     */
    protected static function mapLabelValueToCandidate($label, $val, $method, $baseConfidence, $pageNo, $evidence, array &$allCandidates)
    {
        $supportedFields = Tender_dictionary::getSupportedFields();
        $cleanLabel = trim($label);

        foreach ($supportedFields as $field) {
            $synonyms = Tender_dictionary::getSynonyms($field);
            $excludePatterns = Tender_dictionary::getExcludePatterns($field);

            // Check exclusion patterns on label first
            if (!empty($excludePatterns)) {
                $excluded = false;
                foreach ($excludePatterns as $exPat) {
                    if (preg_match($exPat, $cleanLabel)) {
                        $excluded = true;
                        break;
                    }
                }
                if ($excluded) continue;
            }

            foreach ($synonyms as $syn) {
                if (stripos($cleanLabel, $syn) !== false) {
                    self::addProcessedCandidate($field, $val, $method, $baseConfidence, $pageNo, $evidence, $allCandidates, $cleanLabel);
                    return;
                }
            }
        }
    }

    /**
     * Add candidate with field-specific normalization and time extraction
     */
    protected static function addProcessedCandidate($field, $rawVal, $method, $confidence, $pageNo, $evidence, array &$allCandidates, $label = '')
    {
        $rawVal = trim($rawVal);
        if ($rawVal === '') return;

        // Date fields
        if (strpos($field, 'date') !== false || $field === 'query_deadline') {
            $dateRes = Date_extractor::extractFromPassage($rawVal);
            if (!empty($dateRes['iso_date'])) {
                $allCandidates[$field][] = [
                    'value'      => $dateRes['iso_date'],
                    'raw_value'  => $dateRes['raw_matched'],
                    'time'       => $dateRes['time'] ?? null,
                    'confidence' => $confidence,
                    'method'     => $method,
                    'page'       => $pageNo,
                    'label'      => $label ?: $evidence,
                    'evidence'   => $evidence
                ];

                // Associate extracted time with corresponding activity time candidate
                $dateTimeMap = [
                    'published_date'               => 'tender_publication_time',
                    'nit_issue_date'               => 'tender_publication_time',
                    'document_download_start_date' => 'document_sale_start_time',
                    'document_download_end_date'   => 'document_sale_end_time',
                    'prebid_query_start_date'      => 'prebid_query_start_time',
                    'query_deadline'               => 'prebid_query_end_time',
                    'pre_bid_date'                 => 'prebid_meeting_time',
                    'prebid_response_date'         => 'prebid_response_time',
                    'bid_submission_start_date'    => 'bid_submission_start_time',
                    'submission_date'              => 'submission_time',
                    'pq_bid_opening_date'          => 'pq_bid_opening_time',
                    'technical_opening_date'       => 'technical_opening_time',
                    'financial_opening_date'       => 'financial_opening_time',
                ];

                if (!empty($dateRes['time']) && isset($dateTimeMap[$field])) {
                    $tField = $dateTimeMap[$field];
                    $allCandidates[$tField][] = [
                        'value'      => $dateRes['time'],
                        'raw_value'  => $dateRes['time'],
                        'confidence' => $confidence,
                        'method'     => $method,
                        'page'       => $pageNo,
                        'evidence'   => $evidence
                    ];
                    if ($field === 'submission_date') {
                        $allCandidates['bid_submission_end_time'][] = [
                            'value'      => $dateRes['time'],
                            'raw_value'  => $dateRes['time'],
                            'confidence' => $confidence,
                            'method'     => $method,
                            'page'       => $pageNo,
                            'evidence'   => $evidence
                        ];
                    }
                }
            }
            return;
        }

        // Time fields
        if (strpos($field, 'time') !== false) {
            $normTime = Date_extractor::normalizeTime($rawVal);
            if ($normTime !== null) {
                $allCandidates[$field][] = [
                    'value'      => $normTime,
                    'raw_value'  => $rawVal,
                    'confidence' => $confidence,
                    'method'     => $method,
                    'page'       => $pageNo,
                    'evidence'   => $evidence
                ];
            }
            return;
        }

        // Amount fields
        if ($field === 'emd_value' || $field === 'tender_fee') {
            $amtRes = Amount_extractor::parse($rawVal);
            if ($amtRes['numeric'] !== null) {
                if (Amount_extractor::isPlausibleForField($amtRes['numeric'], $field)) {
                    $allCandidates[$field][] = [
                        'value'      => (string)$amtRes['numeric'],
                        'raw_value'  => $amtRes['formatted'],
                        'confidence' => $confidence,
                        'method'     => $method,
                        'page'       => $pageNo,
                        'evidence'   => $evidence
                    ];
                }
            }
            return;
        }

        // Standard string fields
        $allCandidates[$field][] = [
            'value'      => $rawVal,
            'raw_value'  => $rawVal,
            'confidence' => $confidence,
            'method'     => $method,
            'page'       => $pageNo,
            'evidence'   => $evidence
        ];
    }

    /**
     * Build backward-compatible legacy ai_summary payload
     * Note: Department, Tender Title, and Project Value are strictly manual and never auto-populated.
     */
    protected static function buildLegacyAiSummary(array $resolvedFields, $origName, $normText)
    {
        return [
            'ref_no'                        => $resolvedFields['tender_reference']['value'] ?? '',
            'ref_code'                      => $resolvedFields['tender_reference']['value'] ?? '',
            'tender_id'                     => $resolvedFields['tender_reference']['value'] ?? '',
            'authority'                     => $resolvedFields['issuing_authority']['value'] ?? '',

            // 1. Tender Publication
            'published_date'                => $resolvedFields['published_date']['value'] ?? '',
            'tender_publication_time'       => $resolvedFields['tender_publication_time']['value'] ?? '',
            'tenderPublicationDate'         => $resolvedFields['published_date']['value'] ?? '',
            'tenderPublicationTime'         => $resolvedFields['tender_publication_time']['value'] ?? '',
            'tender_publication_date'       => $resolvedFields['published_date']['value'] ?? '',
            'nit_issue_date'                => $resolvedFields['nit_issue_date']['value'] ?? '',

            // 2. Document Download / Sale Start
            'document_download_start_date'  => $resolvedFields['document_download_start_date']['value'] ?? '',
            'document_sale_start_date'      => $resolvedFields['document_download_start_date']['value'] ?? '',
            'document_sale_start_time'      => $resolvedFields['document_sale_start_time']['value'] ?? '',
            'documentSaleStartDate'         => $resolvedFields['document_download_start_date']['value'] ?? '',
            'documentSaleStartTime'         => $resolvedFields['document_sale_start_time']['value'] ?? '',

            // 3. Document Download / Sale End
            'document_download_end_date'    => $resolvedFields['document_download_end_date']['value'] ?? '',
            'document_sale_end_date'        => $resolvedFields['document_download_end_date']['value'] ?? '',
            'document_sale_end_time'        => $resolvedFields['document_sale_end_time']['value'] ?? '',
            'documentSaleEndDate'           => $resolvedFields['document_download_end_date']['value'] ?? '',
            'documentSaleEndTime'           => $resolvedFields['document_sale_end_time']['value'] ?? '',

            // 4. Pre-bid Query Start
            'prebid_query_start_date'       => $resolvedFields['prebid_query_start_date']['value'] ?? '',
            'prebid_query_start_time'       => $resolvedFields['prebid_query_start_time']['value'] ?? '',
            'prebidQueryStartDate'          => $resolvedFields['prebid_query_start_date']['value'] ?? '',
            'prebidQueryStartTime'          => $resolvedFields['prebid_query_start_time']['value'] ?? '',

            // 5. Pre-bid Query End
            'query_date'                    => $resolvedFields['query_deadline']['value'] ?? '',
            'query_deadline'                => $resolvedFields['query_deadline']['value'] ?? '',
            'prebid_query_end_date'         => $resolvedFields['query_deadline']['value'] ?? '',
            'prebid_query_end_time'         => $resolvedFields['prebid_query_end_time']['value'] ?? '',
            'prebidQueryEndDate'            => $resolvedFields['query_deadline']['value'] ?? '',
            'prebidQueryEndTime'            => $resolvedFields['prebid_query_end_time']['value'] ?? '',

            // 6. Pre-bid Meeting
            'pre_bid_date'                  => $resolvedFields['pre_bid_date']['value'] ?? '',
            'prebid_meeting_date'           => $resolvedFields['pre_bid_date']['value'] ?? '',
            'prebid_meeting_time'           => $resolvedFields['prebid_meeting_time']['value'] ?? '',
            'prebidMeetingDate'             => $resolvedFields['pre_bid_date']['value'] ?? '',
            'prebidMeetingTime'             => $resolvedFields['prebid_meeting_time']['value'] ?? '',

            // 7. Pre-bid Response Deadline
            'prebid_response_date'          => $resolvedFields['prebid_response_date']['value'] ?? '',
            'prebid_response_time'          => $resolvedFields['prebid_response_time']['value'] ?? '',
            'prebidResponseDate'            => $resolvedFields['prebid_response_date']['value'] ?? '',
            'prebidResponseTime'            => $resolvedFields['prebid_response_time']['value'] ?? '',

            // 8. Bid Submission Start
            'bid_submission_start_date'     => $resolvedFields['bid_submission_start_date']['value'] ?? '',
            'bid_submission_start_time'     => $resolvedFields['bid_submission_start_time']['value'] ?? '',
            'bidSubmissionStartDate'        => $resolvedFields['bid_submission_start_date']['value'] ?? '',
            'bidSubmissionStartTime'        => $resolvedFields['bid_submission_start_time']['value'] ?? '',

            // 9. Bid Submission End
            'submission_date'               => $resolvedFields['submission_date']['value'] ?? '',
            'submission_deadline'           => $resolvedFields['submission_date']['value'] ?? '',
            'submission_time'               => $resolvedFields['submission_time']['value'] ?? ($resolvedFields['bid_submission_end_time']['value'] ?? ''),
            'bid_submission_end_date'       => $resolvedFields['submission_date']['value'] ?? '',
            'bid_submission_end_time'       => $resolvedFields['submission_time']['value'] ?? ($resolvedFields['bid_submission_end_time']['value'] ?? ''),
            'bidSubmissionEndDate'          => $resolvedFields['submission_date']['value'] ?? '',
            'bidSubmissionEndTime'          => $resolvedFields['submission_time']['value'] ?? ($resolvedFields['bid_submission_end_time']['value'] ?? ''),

            // 10. PQ Bid Opening
            'pq_bid_opening_date'           => $resolvedFields['pq_bid_opening_date']['value'] ?? '',
            'pq_bid_opening_time'           => $resolvedFields['pq_bid_opening_time']['value'] ?? '',
            'pqBidOpeningDate'              => $resolvedFields['pq_bid_opening_date']['value'] ?? '',
            'pqBidOpeningTime'              => $resolvedFields['pq_bid_opening_time']['value'] ?? '',

            // 11. Technical Bid Opening
            'opening_date'                  => $resolvedFields['technical_opening_date']['value'] ?? '',
            'technical_opening_date'        => $resolvedFields['technical_opening_date']['value'] ?? '',
            'technical_opening_time'        => $resolvedFields['technical_opening_time']['value'] ?? '',
            'technicalBidOpeningDate'       => $resolvedFields['technical_opening_date']['value'] ?? '',
            'technicalBidOpeningTime'       => $resolvedFields['technical_opening_time']['value'] ?? '',

            // 12. Financial Bid Opening
            'financial_opening_date'        => $resolvedFields['financial_opening_date']['value'] ?? '',
            'financial_opening_time'        => $resolvedFields['financial_opening_time']['value'] ?? '',
            'financialBidOpeningDate'       => $resolvedFields['financial_opening_date']['value'] ?? '',
            'financialBidOpeningTime'       => $resolvedFields['financial_opening_time']['value'] ?? '',

            // Commercials & General
            'emd_value'                     => $resolvedFields['emd_value']['value'] ?? '',
            'tender_fee'                    => $resolvedFields['tender_fee']['value'] ?? '',
            'portal'                        => $resolvedFields['tender_portal']['value'] ?? '',
            'scope'                         => $resolvedFields['scope_of_work']['value'] ?? '',
            'location'                      => $resolvedFields['work_location']['value'] ?? '',
            'contract_period'               => $resolvedFields['contract_period']['value'] ?? '',
            'keywords'                      => $resolvedFields['keywords_summary']['value'] ?? '',
            'title'                         => !empty($resolvedFields['tender_title']['value']) ? $resolvedFields['tender_title']['value'] : $origName,
            'first_line'                    => '',
            'bid_title'                     => $resolvedFields['tender_title']['value'] ?? ''
        ];
    }
}
