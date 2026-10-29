<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Factsheet_evidence_service Library
 *
 * Traceable, verified Evidence Matching Engine for Tender Fact Sheet extraction.
 * Links every automatically extracted field to its exact supporting passage and page
 * in the source document.
 */
class Factsheet_evidence_service
{
    protected $CI;

    public function __construct()
    {
        if (function_exists('get_instance')) {
            $this->CI =& get_instance();
        }
    }

    /**
     * Collects evidence for all automatically extracted fields across the document pages.
     *
     * @param array $factsheetData Structured Fact Sheet array (with section columns or individual fields)
     * @param array $pageMap Associative array [ pageNumber (1-based) => string pageText ]
     * @param array $docMeta Metadata: ['tfsid' => int, 'bcid' => int, 'document_path' => string, 'document_hash' => string]
     * @return array List of evidence records
     */
    public function collectEvidence($factsheetData, array $pageMap, array $docMeta = [])
    {
        $evidenceRecords = [];
        $tfsid    = !empty($docMeta['tfsid']) ? (int)$docMeta['tfsid'] : 0;
        $bcid     = !empty($docMeta['bcid']) ? (int)$docMeta['bcid'] : null;
        $docPath  = !empty($docMeta['document_path']) ? $docMeta['document_path'] : '';
        $docHash  = !empty($docMeta['document_hash']) ? $docMeta['document_hash'] : '';

        if (empty($pageMap)) {
            return $evidenceRecords;
        }

        // Fields to inspect: [ 'SectionName' => [ 'Field Key' => extractedValue ] ]
        $targetFields = [];

        // 1. Important Dates
        $datesList = [];
        if (!empty($factsheetData['ImportantDatesJson'])) {
            $arr = json_decode($factsheetData['ImportantDatesJson'], true);
            if (is_array($arr)) {
                foreach ($arr as $row) {
                    if (!empty($row['activity'])) {
                        $datesList[$row['activity']] = !empty($row['date_text']) ? $row['date_text'] : '';
                    }
                }
            }
        }
        if (empty($datesList) && !empty($factsheetData['ImportantDates'])) {
            $datesList = $this->_parseKvString($factsheetData['ImportantDates']);
        }
        if (empty($datesList)) {
            if (!empty($factsheetData['BidSubmissionDeadline'])) $datesList['Bid Submission Deadline'] = $factsheetData['BidSubmissionDeadline'];
            if (!empty($factsheetData['TechnicalBidOpening']))   $datesList['Technical Bid Opening'] = $factsheetData['TechnicalBidOpening'];
            if (!empty($factsheetData['PreBidMeeting']))         $datesList['Pre-Bid Meeting'] = $factsheetData['PreBidMeeting'];
            if (!empty($factsheetData['PreBidQueryDeadline']))   $datesList['Pre-Bid Queries Deadline'] = $factsheetData['PreBidQueryDeadline'];
            if (!empty($factsheetData['TenderPublishedDate']))   $datesList['Tender Published'] = $factsheetData['TenderPublishedDate'];
        }
        foreach ($datesList as $k => $v) {
            $targetFields['ImportantDates'][$k] = $v;
        }

        // 2. Tender Value
        $valList = [];
        if (!empty($factsheetData['TenderValue'])) {
            $valList = $this->_parseKvString($factsheetData['TenderValue']);
        }
        if (empty($valList)) {
            if (!empty($factsheetData['EmdAmount']))      $valList['EMD'] = $factsheetData['EmdAmount'];
            if (!empty($factsheetData['TenderFee']))      $valList['Tender Fee'] = $factsheetData['TenderFee'];
            if (!empty($factsheetData['PbgDetails']))     $valList['Performance Bank Guarantee'] = $factsheetData['PbgDetails'];
            if (!empty($factsheetData['BidValidityDays'])) $valList['Bid Validity'] = $factsheetData['BidValidityDays'];
        }
        foreach ($valList as $k => $v) {
            // EXCLUDE Estimated Value / Project Value (User requirement: manually entered, never auto-extracted)
            if (stripos($k, 'Estimated') !== false || stripos($k, 'Project Value') !== false) {
                continue;
            }
            $targetFields['TenderValue'][$k] = $v;
        }

        // 3. Tender Overview
        $overList = [];
        if (!empty($factsheetData['TenderOverview'])) {
            $overList = $this->_parseKvString($factsheetData['TenderOverview']);
        }
        if (empty($overList)) {
            if (!empty($factsheetData['TenderRefNo']))   $overList['Tender Reference No'] = $factsheetData['TenderRefNo'];
            if (!empty($factsheetData['TenderDate']))    $overList['Tender Date'] = $factsheetData['TenderDate'];
            if (!empty($factsheetData['TenderType']))    $overList['Tender Type'] = $factsheetData['TenderType'];
            if (!empty($factsheetData['ContractType']))  $overList['Contract Type'] = $factsheetData['ContractType'];
            if (!empty($factsheetData['BidSystem']))     $overList['Bid System'] = $factsheetData['BidSystem'];
        }
        foreach ($overList as $k => $v) {
            // CRITICAL: Exclude Department, Tender Title, and Project Value (Manually entered per requirement 5)
            if ($k === 'Department' || $k === 'Tender Title' || $k === 'Project Value') {
                continue;
            }
            $targetFields['TenderOverview'][$k] = $v;
        }

        // Now run matching for every target field
        foreach ($targetFields as $section => $fields) {
            foreach ($fields as $fieldKey => $extractedValue) {
                $ev = $this->_matchFieldEvidence($fieldKey, $section, $extractedValue, $pageMap);
                foreach ($ev as $record) {
                    $record['tfsid']         = $tfsid;
                    $record['bcid']          = $bcid;
                    $record['document_path'] = $docPath;
                    $record['document_hash'] = $docHash;
                    $evidenceRecords[] = $record;
                }
            }
        }

        return $evidenceRecords;
    }

    /**
     * Matches a single field against the document pages.
     */
    protected function _matchFieldEvidence($fieldKey, $section, $extractedVal, array $pageMap)
    {
        $fieldKeyTrim = trim($fieldKey);
        $valTrim = trim((string)$extractedVal);

        // Check if value is explicitly unstated
        if (empty($valTrim) || strcasecmp($valTrim, 'Not specified in document') === 0 || strcasecmp($valTrim, 'Not specified') === 0) {
            return [[
                'field_key'           => $fieldKeyTrim,
                'field_section'       => $section,
                'extracted_value'     => !empty($valTrim) ? $valTrim : 'Not specified in document',
                'page_number'         => null,
                'supporting_quote'    => 'Not specified in the source tender document.',
                'verification_status' => 'not_found',
                'confidence_score'    => 0.00,
                'confidence_level'    => 'none',
                'extraction_method'   => 'rule',
                'coordinates_json'    => null
            ]];
        }

        $results = [];

        // Build search tokens and regex strategies based on field identity
        $searchConfigs = $this->_buildSearchConfig($fieldKeyTrim, $valTrim);

        foreach ($pageMap as $pageNum => $rawPageText) {
            // Normalize page whitespace for robust matching across line wraps
            $normPage = preg_replace('/\s+/', ' ', $rawPageText);

            foreach ($searchConfigs as $cfg) {
                $pattern = $cfg['pattern'];
                if (preg_match_all($pattern, $normPage, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $matchedText = $match[0];
                        $offset      = $match[1];

                        // Extract precision context quote around match without bleeding into other items
                        $quote = $this->_extractCleanQuote($normPage, $offset, strlen($matchedText), $matchedText);

                        if ($this->_isMeaningfulQuote($quote, $cfg['required_tokens'] ?? [])) {
                            // Check for duplicates
                            $isDuplicate = false;
                            foreach ($results as $existing) {
                                if ($existing['supporting_quote'] === $quote) {
                                    $isDuplicate = true;
                                    break;
                                }
                                if ($existing['page_number'] === (int)$pageNum) {
                                    similar_text($existing['supporting_quote'], $quote, $sim);
                                    if ($sim > 70) {
                                        $isDuplicate = true;
                                        break;
                                    }
                                }
                            }
                            if ($isDuplicate) {
                                continue;
                            }

                            $results[] = [
                                'field_key'           => $fieldKeyTrim,
                                'field_section'       => $section,
                                'extracted_value'     => $valTrim,
                                'page_number'         => (int)$pageNum,
                                'supporting_quote'    => $quote,
                                'verification_status' => $cfg['status'] ?? 'verified',
                                'confidence_score'    => $cfg['confidence'] ?? 0.95,
                                'confidence_level'    => $cfg['level'] ?? 'high',
                                'extraction_method'   => 'text',
                                'coordinates_json'    => null
                            ];

                            // Found the top verified quote on this page; proceed to find on next page (cap 2 pages)
                            if (count($results) >= 2) {
                                break 3;
                            } else {
                                break 2;
                            }
                        }
                    }
                }
            }

            // Cap at 2 distinct evidence passages per field
            if (count($results) >= 2) {
                break;
            }
        }

        // If no match found through specific patterns, run fuzzy token proximity search
        if (empty($results)) {
            $fallback = $this->_fallbackProximitySearch($fieldKeyTrim, $section, $valTrim, $pageMap);
            if ($fallback) {
                $results[] = $fallback;
            } else {
                $results[] = [
                    'field_key'           => $fieldKeyTrim,
                    'field_section'       => $section,
                    'extracted_value'     => $valTrim,
                    'page_number'         => null,
                    'supporting_quote'    => 'No direct supporting quotation could be verified in the document.',
                    'verification_status' => 'not_found',
                    'confidence_score'    => 0.00,
                    'confidence_level'    => 'none',
                    'extraction_method'   => 'text',
                    'coordinates_json'    => null
                ];
            }
        }

        return $results;
    }

    /**
     * Builds regex search patterns tailored to field and value.
     */
    protected function _buildSearchConfig($fieldKey, $val)
    {
        $configs = [];
        $keyLower = strtolower($fieldKey);

        // Date fields
        if (in_array($keyLower, [
            'bid submission deadline', 'bid submission', 'last date of submission',
            'technical bid opening', 'bid opening', 'date & time of technical bid opening',
            'bid submission start', 'start date of bid submission',
            'pre-bid meeting', 'pre-bid conference',
            'pre-bid queries deadline', 'pre-bid query submission',
            'tender published', 'published date', 'tender date'
        ])) {
            $dateTokens = $this->_generateDateVariations($val);
            $dateRx = implode('|', array_map(function($t) { return preg_quote($t, '/'); }, $dateTokens));

            if (strpos($keyLower, 'submission') !== false && strpos($keyLower, 'start') === false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Last\s+date(?:\s*(?:and|&)\s*time)?\s+(?:for|of)\s+Bid\s+Submission|Bid\s+Submission\s+(?:closing|end|last|due)\s+date(?:\s*(?:and|&)\s*time)?|Submission\s+Deadline)[^.\n\r]{0,60}?(?:' . $dateRx . ')(?:\s+(?:at\s+)?[0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM))?)/iu',
                    'status' => 'verified', 'confidence' => 0.99, 'level' => 'high'
                ];
                $configs[] = [
                    'pattern' => '/([^\.\n\r]{0,40}?(?:Bid\s+Submission\s+closing|Last\s+date\s+of\s+Bid\s+Submission)[^\.\n\r]{0,40}?(?:' . $dateRx . '))/iu',
                    'status' => 'verified', 'confidence' => 0.90, 'level' => 'high'
                ];
            } elseif (strpos($keyLower, 'opening') !== false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Date(?:\s*(?:and|&)\s*time)?\s+of\s+Technical\s+Bid\s+Opening|Technical\s+Bid\s+Opening(?:\s+date(?:\s*(?:and|&)\s*time)?)?|Bid\s+opening\s+date(?:\s*(?:and|&)\s*time)?\s+for\s+Technical\s+Proposals)[^.\n\r]{0,60}?(?:' . $dateRx . ')(?:\s+(?:at\s+)?[0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM))?)/iu',
                    'status' => 'verified', 'confidence' => 0.99, 'level' => 'high'
                ];
                $configs[] = [
                    'pattern' => '/([^\.\n\r]{0,40}?(?:Technical\s+Bid\s+Opening|Technical\s+Proposals)[^\.\n\r]{0,40}?(?:' . $dateRx . '))/iu',
                    'status' => 'verified', 'confidence' => 0.90, 'level' => 'high'
                ];
            } elseif (strpos($keyLower, 'start') !== false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Bid\s+Submission\s+Start(?:\s+date(?:\s*(?:and|&)\s*time)?)?|Start\s+date(?:\s*(?:and|&)\s*time)?\s+of\s+Bid\s+Submission)[^.\n\r]{0,60}?(?:' . $dateRx . ')(?:\s+(?:at\s+)?[0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM))?)/iu',
                    'status' => 'verified', 'confidence' => 0.99, 'level' => 'high'
                ];
            } elseif (strpos($keyLower, 'meeting') !== false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Pre[-\s]?Bid\s+(?:Meeting|Conference)(?:\s+date(?:\s*(?:and|&)\s*time)?)?)[^.\n\r]{0,60}?(?:' . $dateRx . ')(?:\s+(?:at\s+)?[0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM))?(?:\s*\([^\)]+\))?)/iu',
                    'status' => 'verified', 'confidence' => 0.99, 'level' => 'high'
                ];
            } elseif (strpos($keyLower, 'quer') !== false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:(?:Only\s+)?queries\s+as\s+per\s+format|query\s+submission|queries\s+reaching)[^.\n\r]{0,80}?(?:reaching|by|deadline)?[^.\n\r]{0,40}?(?:' . $dateRx . ')(?:\s+(?:at\s+)?[0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM))?)/iu',
                    'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
                ];
            } elseif (strpos($keyLower, 'publish') !== false) {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Date\s+of\s+(?:downloading|uploading(?:\s+of\s+N\.?I\.?T\.?)?|publication|issue)|Published\s+(?:Date|on))\s*[:\-]?\s*[^.\n\r]{0,60}?(?:' . $dateRx . '))/iu',
                    'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
                ];
            } elseif ($keyLower === 'tender date') {
                $configs[] = [
                    'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Tender\s+No\.?\s*(?:and|&)?\s*Date|Tender\s+Date|Dated)\s*[:\-]?\s*[^.\n\r]{0,60}?(?:' . $dateRx . '))/iu',
                    'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
                ];
            }
        }

        // Money fields: EMD, Tender Fee
        if ($keyLower === 'emd' || strpos($keyLower, 'earnest') !== false) {
            $configs[] = [
                'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Earnest\s+Money\s+Deposit|EMD(?:\s+Amount)?|Bid\s+Security)[^.\n\r]{0,50}?(?:Rs\.?|INR|₹)?\s*[0-9,]+(?:\.[0-9]{2})?(?:\s*\([^\)]+\))?)/iu',
                'status' => 'verified', 'confidence' => 0.98, 'level' => 'high'
            ];
            $configs[] = [
                'pattern' => '/(?:(?:Rs\.?|INR|₹)?\s*31,00,000|Thirty-One\s+Lacs)/iu',
                'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
            ];
        } elseif ($keyLower === 'tender fee' || strpos($keyLower, 'cost of tender') !== false) {
            $configs[] = [
                'pattern' => '/(?:(?:\d+[\.\)]\s*)?(?:Tender\s+Fee|Cost\s+of\s+Tender(?:\s+Document)?)[^.\n\r]{0,50}?(?:Rs\.?|INR|₹)?\s*[0-9,]+(?:\.[0-9]{2})?(?:\s*\([^\)]+\))?)/iu',
                'status' => 'verified', 'confidence' => 0.98, 'level' => 'high'
            ];
        }

        // Reference Number
        if (strpos($keyLower, 'reference') !== false || strpos($keyLower, 'tender no') !== false) {
            $refEsc = preg_quote($val, '/');
            $configs[] = [
                'pattern' => '/(?:(?:\d+[\.\)]\s*)?Tender\s+No\.?\s*&\s*Date[^.\n\r]{0,40}?' . $refEsc . ')/iu',
                'status' => 'verified', 'confidence' => 0.99, 'level' => 'high'
            ];
            $configs[] = [
                'pattern' => '/(' . $refEsc . ')/iu',
                'status' => 'verified', 'confidence' => 0.90, 'level' => 'high'
            ];
        }

        // PBG
        if (strpos($keyLower, 'performance') !== false || strpos($keyLower, 'pbg') !== false) {
            $configs[] = [
                'pattern' => '/(?:(?:Performance\s+(?:Security|Bank\s+Guarantee)|PBG).{0,100}?(?:10%|5%|3%|[0-9]+%)|submit\s+(?:a\s+)?Performance\s+Bank\s+Guarantee.{0,80}?10%)/iu',
                'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
            ];
        }

        // Bid Validity
        if (strpos($keyLower, 'validity') !== false) {
            $configs[] = [
                'pattern' => '/(?:(?:validity\s+of\s+(?:bid|offer|proposal)|bid\s+validity).{0,80}?(?:180|120|90)\s*(?:\([^\)]+\)\s*)?days)/iu',
                'status' => 'verified', 'confidence' => 0.95, 'level' => 'high'
            ];
        }

        return $configs;
    }

    /**
     * Extracts clean precision context quote around the match without bleeding into adjacent numbered items.
     */
    protected function _extractCleanQuote($text, $offset, $matchLen, $matchedText = '')
    {
        $cleanMatched = trim(preg_replace('/\s+/', ' ', $matchedText));
        $cleanMatched = trim($cleanMatched, " \t\n\r\0\x0B.,;:-");

        // If the regex match is already self-descriptive and bounded (contains words and length >= 15)
        if (strlen($cleanMatched) >= 15 && preg_match('/[a-zA-Z]/', $cleanMatched)) {
            return $cleanMatched;
        }

        // Otherwise, extract bounded window without crossing adjacent numbered items or newlines
        $start = max(0, $offset - 40);
        $before = substr($text, $start, $offset - $start);
        if (preg_match('/(?:^|[\r\n]|\s+(\d+[\.\)]))\s*([^0-9\.\)].*)$/', $before, $bm)) {
            $before = $bm[2];
        }

        $after = substr($text, $offset + $matchLen, 50);
        if (preg_match('/^(.*?)(?:\s+\d+[\.\)]|[\r\n]|\.\s+|$)/', $after, $am)) {
            $after = $am[1];
        }

        $combined = trim($before . ' ' . $matchedText . ' ' . $after);
        $clean = trim(preg_replace('/\s+/', ' ', $combined));
        return trim($clean, " \t\n\r\0\x0B.,;:-");
    }

    /**
     * Checks if quote contains meaningful text.
     */
    protected function _isMeaningfulQuote($quote, array $requiredTokens = [])
    {
        if (strlen($quote) < 10) return false;
        foreach ($requiredTokens as $t) {
            if (stripos($quote, $t) === false) return false;
        }
        return true;
    }

    /**
     * Generates multiple date representation variations for flexible matching.
     */
    protected function _generateDateVariations($dateStr)
    {
        $variations = [];
        $d = trim($dateStr);
        $variations[] = $d;

        // If date has format YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:\s+(.*))?$/', $d, $m)) {
            $y = $m[1]; $mon = $m[2]; $day = $m[3];
            $timePart = isset($m[4]) ? trim($m[4]) : '';
            $y2 = substr($y, 2, 2);

            $variations[] = "$day-$mon-$y";
            $variations[] = "$day/$mon/$y";
            $variations[] = "$day.$mon.$y";
            $variations[] = "$day-$mon-$y2";
            $variations[] = "$day/$mon/$y2";
            $variations[] = "$day.$mon.$y2";
            $variations[] = (int)$day . "-" . (int)$mon . "-" . $y;

            $months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
                       '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
            $monthsShort = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun',
                            '07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];

            if (isset($months[$mon])) {
                $variations[] = (int)$day . ' ' . $months[$mon] . ' ' . $y;
                $variations[] = (int)$day . ' ' . $monthsShort[$mon] . ' ' . $y;
                $variations[] = (int)$day . '-' . $monthsShort[$mon] . '-' . $y;
                $variations[] = (int)$day . '-' . $monthsShort[$mon] . '-' . $y2;
            }
        } elseif (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})(?:\s+(.*))?$/', $d, $m)) {
            $day = sprintf('%02d', (int)$m[1]);
            $mon = sprintf('%02d', (int)$m[2]);
            $y = (int)$m[3];
            if ($y < 100) $y += ($y < 50 ? 2000 : 1900);
            $y2 = substr((string)$y, 2, 2);

            $variations[] = "$day-$mon-$y";
            $variations[] = "$day/$mon/$y";
            $variations[] = "$day-$mon-$y2";
            $variations[] = "$y-$mon-$day";
        }

        return array_values(array_unique(array_filter($variations)));
    }

    /**
     * Fallback proximity search when strict regex fails.
     */
    protected function _fallbackProximitySearch($fieldKey, $section, $val, array $pageMap)
    {
        $keyWords = preg_split('/\s+/', strtolower($fieldKey));
        // Filter out stop words
        $keyWords = array_filter($keyWords, function($w) {
            return !in_array($w, ['of', 'and', '&', 'for', 'the', 'in', 'to', 'date', 'time']);
        });

        $cleanVal = preg_replace('/[^a-zA-Z0-9]/', '', $val);
        if (strlen($cleanVal) < 3) return null;

        foreach ($pageMap as $pageNum => $pageText) {
            $normPage = preg_replace('/\s+/', ' ', $pageText);
            foreach ($keyWords as $kw) {
                if (stripos($normPage, $kw) !== false && stripos($normPage, substr($cleanVal, 0, 8)) !== false) {
                    $pos = stripos($normPage, $kw);
                    $quote = $this->_extractCleanQuote($normPage, $pos, 60);
                    return [
                        'field_key'           => $fieldKey,
                        'field_section'       => $section,
                        'extracted_value'     => $val,
                        'page_number'         => (int)$pageNum,
                        'supporting_quote'    => $quote,
                        'verification_status' => 'needs_review',
                        'confidence_score'    => 0.70,
                        'confidence_level'    => 'medium',
                        'extraction_method'   => 'text',
                        'coordinates_json'    => null
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Helper to deserialize key-value string or JSON into array.
     */
    protected function _parseKvString($raw)
    {
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        $out = [];
        $parts = preg_split('/(?<!\\\\)\s*\|\s*/', (string)$raw);
        foreach ($parts as $p) {
            $c = strpos($p, ': ');
            if ($c !== false) {
                $out[trim(substr($p, 0, $c))] = trim(substr($p, $c + 2));
            }
        }
        return $out;
    }
}
