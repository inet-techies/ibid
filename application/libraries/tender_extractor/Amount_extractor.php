<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intelligent Indian Currency & Financial Amount Extractor
 *
 * Normalizes Indian and international currency formats (Rupees, Lakhs, Crores, words, commas)
 * into standard numeric integers/floats while retaining the exact original formatted text.
 * Disambiguates EMD, Tender Fee, PBG, and Turnover requirements.
 */
class Amount_extractor
{
    /**
     * Map of number words for parsing amounts in words
     */
    protected static $wordsMap = [
        'zero' => 0, 'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4,
        'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9,
        'ten' => 10, 'eleven' => 11, 'twelve' => 12, 'thirteen' => 13,
        'fourteen' => 14, 'fifteen' => 15, 'sixteen' => 16, 'seventeen' => 17,
        'eighteen' => 18, 'nineteen' => 19, 'twenty' => 20, 'thirty' => 30,
        'forty' => 40, 'fifty' => 50, 'sixty' => 60, 'seventy' => 70,
        'eighty' => 80, 'ninety' => 90, 'hundred' => 100,
        'thousand' => 1000, 'lakh' => 100000, 'lakhs' => 100000,
        'lac' => 100000, 'lacs' => 100000, 'crore' => 10000000,
        'crores' => 10000000, 'cr' => 10000000
    ];

    /**
     * Parse raw string into numeric value, formatted representation, original amount, and amount in words
     *
     * @param string $raw
     * @return array ['numeric' => float|int|null, 'formatted' => string, 'raw' => string, 'original_amount' => string, 'amount_in_words' => string, 'is_exempt' => bool]
     */
    public static function parse($raw)
    {
        $result = [
            'numeric'         => null,
            'formatted'       => '',
            'raw'             => trim((string)$raw),
            'original_amount' => '',
            'amount_in_words' => '',
            'is_exempt'       => false
        ];

        if (empty($result['raw'])) return $result;
        $str = $result['raw'];

        // 1. Check for Nil / Exempted / Free / N/A
        if (preg_match('/\b(?:Nil|Exempted|Exempt|Free\s+of\s+Cost|Waived|N\/?A|Zero)\b/i', $str)) {
            $result['numeric']         = 0;
            $result['formatted']       = 'Exempted / Nil';
            $result['original_amount'] = 'Nil / Exempted';
            $result['is_exempt']       = true;
            return $result;
        }

        // 2. Extract words in parentheses e.g. "(Rupees Five Lakh only)" or "(Sixteen Lacs Only)"
        if (preg_match('/\(\s*(?:Rupees|INR|Rs\.?)?\s*([A-Za-z\s]+?)\s*(?:only)?\s*\)/i', $str, $wm)) {
            $wordPart = trim($wm[1]);
            $wVal = self::parseWords($wordPart);
            if ($wVal !== null && $wVal > 0) {
                $result['amount_in_words'] = 'Rupees ' . ucwords(strtolower($wordPart)) . ' only';
            }
        }

        // 3. Currency-prefixed Lakhs / Crores (e.g. "Rs. 39 Lakhs", "₹ 10.5 Lakh", "INR 2.25 Crores", "Rs. 5 Lakhs")
        if (preg_match('/(?:Rs\.?|INR|₹|Rupees)\s*([0-9]+(?:\.[0-9]+)?)\s*(Lakhs?|Lacs?|Lac|Crores?|Cr\.?|Thousand|K)\b/iu', $str, $m)) {
            $val = (float)$m[1];
            $unit = strtolower(trim($m[2]));
            if (in_array($unit, ['crore', 'crores', 'cr', 'cr.'])) {
                $val *= 10000000;
            } elseif (in_array($unit, ['lakh', 'lakhs', 'lac', 'lacs'])) {
                $val *= 100000;
            } elseif (in_array($unit, ['thousand', 'k'])) {
                $val *= 1000;
            }
            $result['numeric']         = (int)round($val);
            $result['original_amount'] = trim($m[0]);
            $result['formatted']       = self::formatIndianCurrency($result['numeric']);
            return $result;
        }

        // 4. Currency-prefixed comma-separated or plain numbers (e.g. "Rs. 5,00,000/-" or "₹5,00,000" or "INR 500000")
        if (preg_match('/(?:Rs\.?|INR|₹|Rupees)\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?|[0-9]{4,10})\s*(?:\/\s*-|\/-)?/iu', $str, $m)) {
            $numOnly = str_replace(',', '', $m[1]);
            if (is_numeric($numOnly)) {
                $val = (float)$numOnly;
                $result['numeric']         = (int)round($val);
                $result['original_amount'] = trim($m[0]);
                $result['formatted']       = self::formatIndianCurrency($result['numeric']);
                return $result;
            }
        }

        // 5. Bare Lakhs / Crores representation without currency symbol (e.g. "39 Lakhs", "10 Lacs")
        if (preg_match('/\b([0-9]+(?:\.[0-9]+)?)\s*(Lakhs?|Lacs?|Lac|Crores?|Cr\.?)\b/i', $str, $m)) {
            $val = (float)$m[1];
            $unit = strtolower(trim($m[2]));
            if (in_array($unit, ['crore', 'crores', 'cr', 'cr.'])) {
                $val *= 10000000;
            } elseif (in_array($unit, ['lakh', 'lakhs', 'lac', 'lacs'])) {
                $val *= 100000;
            }
            $result['numeric']         = (int)round($val);
            $result['original_amount'] = trim($m[0]);
            $result['formatted']       = self::formatIndianCurrency($result['numeric']);
            return $result;
        }

        // 6. Indian comma-separated format without currency prefix (e.g. "5,00,000/-" or "16,00,000.00")
        if (preg_match('/\b([0-9]{1,3}(?:,[0-9]{2,3})+(?:\.[0-9]{1,2})?)\s*(?:\/\s*-|\/-)?/u', $str, $m)) {
            $numOnly = str_replace(',', '', $m[1]);
            if (is_numeric($numOnly)) {
                $val = (float)$numOnly;
                $result['numeric']         = (int)round($val);
                $result['original_amount'] = trim($m[0]);
                $result['formatted']       = self::formatIndianCurrency($result['numeric']);
                return $result;
            }
        }

        // 7. Verbal amounts in words (e.g. "Rupees Five Lakh Only", "Five Lakhs")
        $wordVal = self::parseWords($str);
        if ($wordVal !== null && $wordVal > 0) {
            $result['numeric']         = $wordVal;
            $result['original_amount'] = trim($str);
            $result['formatted']       = self::formatIndianCurrency($wordVal);
            if (empty($result['amount_in_words'])) {
                $result['amount_in_words'] = trim($str);
            }
            return $result;
        }

        return $result;
    }

    /**
     * Format an integer into Indian currency notation: ₹ 10,00,000
     *
     * @param int|float $amount
     * @return string
     */
    public static function formatIndianCurrency($amount)
    {
        $amount = (int)round($amount);
        if ($amount === 0) return '₹ 0';

        $isNeg = $amount < 0;
        $numStr = (string)abs($amount);
        $len = strlen($numStr);

        if ($len <= 3) {
            $formatted = $numStr;
        } else {
            $last3 = substr($numStr, -3);
            $rest = substr($numStr, 0, -3);
            $parts = [];
            while (strlen($rest) > 2) {
                $parts[] = substr($rest, -2);
                $rest = substr($rest, 0, -2);
            }
            if (strlen($rest) > 0) {
                $parts[] = $rest;
            }
            $parts = array_reverse($parts);
            $formatted = implode(',', $parts) . ',' . $last3;
        }

        return ($isNeg ? '-' : '') . '₹ ' . $formatted;
    }

    /**
     * Parse verbal amounts like "Rupees Two Crore Fifty Lakhs"
     *
     * @param string $text
     * @return int|null
     */
    public static function parseWords($text)
    {
        $clean = strtolower(preg_replace('/[^a-zA-Z\s]/', ' ', $text));
        $tokens = preg_split('/\s+/', $clean);
        $hasMoneyContext = false;
        $total = 0;
        $current = 0;

        foreach ($tokens as $token) {
            if ($token === 'rupees' || $token === 'rupee' || $token === 'inr' || $token === 'only') {
                $hasMoneyContext = true;
                continue;
            }
            if (!isset(self::$wordsMap[$token])) {
                continue;
            }

            $hasMoneyContext = true;
            $val = self::$wordsMap[$token];

            if ($val === 10000000) { // crore
                $current = ($current === 0 ? 1 : $current) * $val;
                $total += $current;
                $current = 0;
            } elseif ($val === 100000) { // lakh
                $current = ($current === 0 ? 1 : $current) * $val;
                $total += $current;
                $current = 0;
            } elseif ($val === 1000) { // thousand
                $current = ($current === 0 ? 1 : $current) * $val;
                $total += $current;
                $current = 0;
            } elseif ($val === 100) { // hundred
                $current = ($current === 0 ? 1 : $current) * $val;
            } else {
                $current += $val;
            }
        }

        $total += $current;

        return ($hasMoneyContext && $total > 0) ? $total : null;
    }

    /**
     * Dedicated intelligent EMD Candidate Extractor
     * Scans sections, table grids, key-values, and sentences across all pages.
     *
     * @param string $fullText
     * @param array $pageMap [ pageNum => text ]
     * @return array List of candidate arrays
     */
    public static function extractEmdCandidates($fullText, array $pageMap = [])
    {
        $candidates = [];
        $pages = !empty($pageMap) ? $pageMap : [1 => $fullText];

        $emdKeywords = 'Earnest\s+Money(?:\s+Deposit)?|EMD|Bid\s+Security(?:\s+Deposit)?|Refundable\s+EMD';

        foreach ($pages as $pNo => $pText) {
            // ── Strategy A: Section Header + Paragraph Search ──
            // e.g. "M. EARNEST MONEY DEPOSIT (EMD) Refundable"
            // "An EMD/Bid Security of Rs. 5,00,000/- (Rupees Five Lakh only) shall be paid through..."
            $secRegex = '/(?:(?:Section\s+[A-Z0-9.\-]+\s*[:–—\-]?\s*|[A-Z]\.\s+)(?:' . $emdKeywords . ')[^\r\n]*)/i';
            if (preg_match_all($secRegex, $pText, $secMatches, PREG_OFFSET_CAPTURE)) {
                foreach ($secMatches[0] as $sm) {
                    $secTitle = trim($sm[0]);
                    $secOffset = $sm[1];
                    $secBlock = substr($pText, $secOffset, 700);

                    // Skip if primarily forfeiture or exemption clause without amount definition
                    if (preg_match('/^(?:[A-Z]\.\s+)?(?:FORFEITURE|EXEMPTION)/i', $secTitle)) {
                        continue;
                    }

                    // Look for amount inside the section block
                    $amtRegex = '/(?:(?:An?\s+)?(?:EMD|Bid\s+Security|Earnest\s+Money)?(?:[\/\s]+(?:Bid\s+Security|EMD))?\s*(?:Amount|of)?\s*[:\- ]*)?(?:Rs\.?|INR|₹|Rupees)\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?|\d+(?:\.\d+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))\s*(?:\/-)?(?:\s*\((?:Rupees|INR)?\s*([a-zA-Z\s]+?)\s*(?:only)?\))?/iu';
                    if (preg_match($amtRegex, $secBlock, $am)) {
                        $parsed = self::parse($am[0]);
                        if ($parsed['numeric'] !== null && $parsed['numeric'] >= 500) {
                            $words = !empty($parsed['amount_in_words']) ? $parsed['amount_in_words'] : (isset($am[2]) ? 'Rupees ' . ucwords(strtolower(trim($am[2]))) . ' only' : '');
                            $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : trim($am[0]);
                            $candidates[] = [
                                'value'           => (string)$parsed['numeric'],
                                'raw_value'       => $orig,
                                'original_amount' => $orig,
                                'amount_in_words' => $words,
                                'source'          => "Page {$pNo}, {$secTitle}",
                                'evidence'        => "{$secTitle} &mdash; " . trim(preg_replace('/\s+/', ' ', $am[0])),
                                'confidence'      => 99,
                                'page'            => $pNo,
                                'method'          => 'section_heading'
                            ];
                        }
                    }
                }
            }

            // ── Strategy B: Table Row / Key-Value Matching ──
            // e.g. "5. Earnest Money Deposit Rs. 16,00,000.00 (Sixteen Lacs Only)"
            // or "Required EMD: Rs. 5,00,000/-"
            $tblRegex = '/(?:^|[\r\n])\s*(?:(?:[0-9]+[.\)]\s*)?([^\r\n|:]*?(?:' . $emdKeywords . ')[^\r\n|:]*?))\s*(?:[:\t|]+|\s{3,})\s*([^\r\n|]+)/iu';
            if (preg_match_all($tblRegex, $pText, $tblMatches, PREG_SET_ORDER)) {
                foreach ($tblMatches as $tm) {
                    $rowLabel = trim($tm[1]);
                    $rowVal = trim($tm[2]);

                    // Exclude if label indicates deadline, fee, account, etc.
                    if (preg_match('/(?:Date|Time|Fee|Cost|Schedule|Account|Exemption|Forfeiture)/i', $rowLabel)) {
                        continue;
                    }

                    $parsed = self::parse($rowVal);
                    if ($parsed['numeric'] !== null && $parsed['numeric'] >= 500) {
                        $candidates[] = [
                            'value'           => (string)$parsed['numeric'],
                            'raw_value'       => $parsed['original_amount'] ?: $parsed['formatted'],
                            'original_amount' => $parsed['original_amount'] ?: $parsed['formatted'],
                            'amount_in_words' => $parsed['amount_in_words'],
                            'source'          => "Page {$pNo}, {$rowLabel}",
                            'evidence'        => "{$rowLabel}: {$rowVal}",
                            'confidence'      => 96,
                            'page'            => $pNo,
                            'method'          => 'table_row'
                        ];
                    }
                }
            }

            // ── Strategy C: Multi-Column Table Grid (Column Alignment) ──
            // e.g. Page 2 of RFPCOMPONENT:
            // "Name of item      Required EMD Rs.      Tender Fee Rs."
            // "Safe City Project  Rs. 39 Lakhs          Rs. 20,000/-"
            $lines = explode("\n", $pText);
            foreach ($lines as $lineIdx => $line) {
                if (preg_match('/(?:Required\s+)?EMD/i', $line, $emdm, PREG_OFFSET_CAPTURE)) {
                    $emdColPos = $emdm[0][1];
                    $feeColPos = null;
                    if (preg_match('/Tender\s+Fee/i', $line, $fm, PREG_OFFSET_CAPTURE)) {
                        $feeColPos = $fm[0][1];
                    }

                    for ($k = $lineIdx + 1; $k <= min($lineIdx + 6, count($lines) - 1); $k++) {
                        $rowLine = $lines[$k];
                        if (preg_match('/^\s*\d+\.\s+[A-Z]/', $rowLine)) break;

                        preg_match_all('/(?:Rs\.?|INR|₹|Rupees)?\s*([0-9]+(?:\.[0-9]+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|Thousand|K)|[0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?)\s*(?:\/-)?/iu', $rowLine, $amMatches, PREG_OFFSET_CAPTURE);
                        if (!empty($amMatches[0])) {
                            foreach ($amMatches[0] as $amtMatch) {
                                $amtStr = trim($amtMatch[0]);
                                $amtPos = $amtMatch[1];
                                if (abs($amtPos - $emdColPos) <= 22) {
                                    $parsed = self::parse($amtStr);
                                    if ($parsed['numeric'] !== null && $parsed['numeric'] >= 500) {
                                        $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : $amtStr;
                                        $candidates[] = [
                                            'value'           => (string)$parsed['numeric'],
                                            'raw_value'       => $orig,
                                            'original_amount' => $orig,
                                            'amount_in_words' => $parsed['amount_in_words'],
                                            'source'          => "Page {$pNo}, Multi-column table (Required EMD column)",
                                            'evidence'        => "Table column match: {$amtStr}",
                                            'confidence'      => 97,
                                            'page'            => $pNo,
                                            'method'          => 'table_column_grid'
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // ── Strategy D: Context Sentence in Paragraph ──
            // e.g. "An EMD/Bid Security of Rs. 5,00,000/- (Rupees Five Lakh only) shall be paid"
            $ctxRegex = '/\b(?:An?\s+)?(?:' . $emdKeywords . ')(?:[\/\s]+(?:Bid\s+Security|EMD))?\s+(?:of\s+|is\s+|amounting\s+to\s+)(?:Rs\.?|INR|₹|Rupees)\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?|\d+(?:\.\d+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))\s*(?:\/-)?(?:\s*\((?:Rupees|INR)?\s*([a-zA-Z\s]+?)\s*(?:only)?\))?/iu';
            if (preg_match_all($ctxRegex, $pText, $ctxMatches, PREG_SET_ORDER)) {
                foreach ($ctxMatches as $cm) {
                    $parsed = self::parse($cm[0]);
                    if ($parsed['numeric'] !== null && $parsed['numeric'] >= 500) {
                        $words = !empty($parsed['amount_in_words']) ? $parsed['amount_in_words'] : (isset($cm[2]) ? 'Rupees ' . ucwords(strtolower(trim($cm[2]))) . ' only' : '');
                        $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : trim($cm[0]);
                        $candidates[] = [
                            'value'           => (string)$parsed['numeric'],
                            'raw_value'       => $orig,
                            'original_amount' => $orig,
                            'amount_in_words' => $words,
                            'source'          => "Page {$pNo}, In-text EMD specification",
                            'evidence'        => trim(preg_replace('/\s+/', ' ', $cm[0])),
                            'confidence'      => 95,
                            'page'            => $pNo,
                            'method'          => 'context_sentence'
                        ];
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Dedicated intelligent Tender Fee Candidate Extractor
     * Ensures Tender Fee (e.g. ₹2,000 in Section L) is recognized accurately and NEVER confused with EMD.
     *
     * @param string $fullText
     * @param array $pageMap [ pageNum => text ]
     * @return array List of candidate arrays
     */
    public static function extractTenderFeeCandidates($fullText, array $pageMap = [])
    {
        $candidates = [];
        $pages = !empty($pageMap) ? $pageMap : [1 => $fullText];

        $feeKeywords = 'Tender\s+Fee|Cost\s+of\s+Tender(?:\s+Document)?|Tender\s+Document\s+Fee|Document\s+Fee|Tender\s+Cost|Tender\s+Processing\s+Fee|Processing\s+Fee';

        foreach ($pages as $pNo => $pText) {
            // ── Strategy A: Section Header + Paragraph Search (e.g. "L. TENDER FEE") ──
            $secRegex = '/(?:(?:Section\s+[A-Z0-9.\-]+\s*[:–—\-]?\s*|[A-Z]\.\s+)(?:' . $feeKeywords . ')[^\r\n]*)/i';
            if (preg_match_all($secRegex, $pText, $secMatches, PREG_OFFSET_CAPTURE)) {
                foreach ($secMatches[0] as $sm) {
                    $secTitle = trim($sm[0]);
                    $secOffset = $sm[1];
                    $secBlock = substr($pText, $secOffset, 500);

                    $amtRegex = '/(?:(?:Rs\.?|INR|₹|Rupees)\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?|\d+(?:\.\d+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))\s*(?:\/-)?(?:\s*\((?:Rupees|INR)?\s*([a-zA-Z\s]+?)\s*(?:only)?\))?|\bNil\b|\bExempted\b|\bFree\b)/iu';
                    if (preg_match($amtRegex, $secBlock, $am)) {
                        $parsed = self::parse($am[0]);
                        if ($parsed['numeric'] !== null && self::isPlausibleForField($parsed['numeric'], 'tender_fee')) {
                            $words = !empty($parsed['amount_in_words']) ? $parsed['amount_in_words'] : (isset($am[2]) ? 'Rupees ' . ucwords(strtolower(trim($am[2]))) . ' only' : '');
                            $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : trim($am[0]);
                            $candidates[] = [
                                'value'           => (string)$parsed['numeric'],
                                'raw_value'       => $orig,
                                'original_amount' => $orig,
                                'amount_in_words' => $words,
                                'source'          => "Page {$pNo}, {$secTitle}",
                                'evidence'        => "{$secTitle} &mdash; " . trim(preg_replace('/\s+/', ' ', $am[0])),
                                'confidence'      => 98,
                                'page'            => $pNo,
                                'method'          => 'section_heading'
                            ];
                        }
                    }
                }
            }

            // ── Strategy B: Multi-Column Table Grid (e.g. Tender Fee column) ──
            $lines = explode("\n", $pText);
            foreach ($lines as $lineIdx => $line) {
                if (preg_match('/Tender\s+Fee/i', $line, $fm, PREG_OFFSET_CAPTURE)) {
                    $feeColPos = $fm[0][1];
                    for ($k = $lineIdx + 1; $k <= min($lineIdx + 6, count($lines) - 1); $k++) {
                        $rowLine = $lines[$k];
                        if (preg_match('/^\s*\d+\.\s+[A-Z]/', $rowLine)) break;

                        preg_match_all('/(?:Rs\.?|INR|₹|Rupees)?\s*([0-9]+(?:\.[0-9]+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|Thousand|K)|[0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?)\s*(?:\/-)?/iu', $rowLine, $amMatches, PREG_OFFSET_CAPTURE);
                        if (!empty($amMatches[0])) {
                            foreach ($amMatches[0] as $amtMatch) {
                                $amtStr = trim($amtMatch[0]);
                                $amtPos = $amtMatch[1];
                                if (abs($amtPos - $feeColPos) <= 20) {
                                    $parsed = self::parse($amtStr);
                                    if ($parsed['numeric'] !== null && self::isPlausibleForField($parsed['numeric'], 'tender_fee')) {
                                        $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : $amtStr;
                                        $candidates[] = [
                                            'value'           => (string)$parsed['numeric'],
                                            'raw_value'       => $orig,
                                            'original_amount' => $orig,
                                            'amount_in_words' => $parsed['amount_in_words'],
                                            'source'          => "Page {$pNo}, Multi-column table (Tender Fee column)",
                                            'evidence'        => "Table column match: {$amtStr}",
                                            'confidence'      => 97,
                                            'page'            => $pNo,
                                            'method'          => 'table_column_grid'
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // ── Strategy C: In-text Context Sentence ──
            // e.g. "Cost of tender documents is Rs. 20,000 /- (Tender fee)"
            $ctxFeeRegex = '/(?:Cost\s+of\s+tender(?:\s+documents?)?|Tender\s+Fee|Document\s+Fee)\s*(?:is|of|amounting\s+to)?\s*[:\- ]*(?:Rs\.?|INR|₹|Rupees)?\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?|\d+(?:\.\d+)?\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))\s*(?:\/-)?(?:\s*\((?:Rupees|INR)?\s*([a-zA-Z\s]+?)\s*(?:only)?\))?/iu';
            if (preg_match_all($ctxFeeRegex, $pText, $cmMatches, PREG_SET_ORDER)) {
                foreach ($cmMatches as $cm) {
                    $parsed = self::parse($cm[0]);
                    if ($parsed['numeric'] !== null && self::isPlausibleForField($parsed['numeric'], 'tender_fee')) {
                        $words = !empty($parsed['amount_in_words']) ? $parsed['amount_in_words'] : (isset($cm[2]) ? 'Rupees ' . ucwords(strtolower(trim($cm[2]))) . ' only' : '');
                        $orig = !empty($parsed['original_amount']) ? $parsed['original_amount'] : trim($cm[0]);
                        $candidates[] = [
                            'value'           => (string)$parsed['numeric'],
                            'raw_value'       => $orig,
                            'original_amount' => $orig,
                            'amount_in_words' => $words,
                            'source'          => "Page {$pNo}, Tender Fee clause",
                            'evidence'        => trim(preg_replace('/\s+/', ' ', $cm[0])),
                            'confidence'      => 95,
                            'page'            => $pNo,
                            'method'          => 'context_sentence'
                        ];
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Validate and disambiguate EMD amount vs Tender Fee vs PBG
     *
     * @param float|int $amount
     * @param string $fieldType 'emd_value' | 'tender_fee'
     * @param float|int|null $contextTenderValue Optional tender estimated value for ratio checks
     * @return bool
     */
    public static function isPlausibleForField($amount, $fieldType, $contextTenderValue = null)
    {
        if ($amount === 0) return true; // Exempted

        if ($fieldType === 'tender_fee') {
            // Tender fees in India range typically from ₹50 to ₹5,00,000 (usually <= 50,000)
            return $amount >= 50 && $amount <= 500000;
        }

        if ($fieldType === 'emd_value') {
            // EMD is generally >= ₹500 and <= ₹10,00,00,000 (10 Cr)
            if ($amount < 500) return false;

            // If context tender value is known, EMD is normally between 0.5% and 5% of tender value
            if ($contextTenderValue !== null && $contextTenderValue > 0) {
                $ratio = $amount / $contextTenderValue;
                // EMD should not exceed 20% of estimated tender value
                if ($ratio > 0.20) return false;
            }
            return true;
        }

        return true;
    }
}
