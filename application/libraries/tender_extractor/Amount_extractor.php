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
     * Parse raw string into numeric value and formatted representation
     *
     * @param string $raw
     * @return array ['numeric' => float|int|null, 'formatted' => string, 'raw' => string, 'is_exempt' => bool]
     */
    public static function parse($raw)
    {
        $result = [
            'numeric'   => null,
            'formatted' => '',
            'raw'       => trim((string)$raw),
            'is_exempt' => false
        ];

        if (empty($result['raw'])) return $result;

        $str = $result['raw'];

        // 1. Check for Nil / Exempted / Free / N/A
        if (preg_match('/\b(?:Nil|Exempted|Exempt|Free\s+of\s+Cost|Waived|N\/?A|Zero)\b/i', $str)) {
            $result['numeric']   = 0;
            $result['formatted'] = 'Exempted / Nil';
            $result['is_exempt'] = true;
            return $result;
        }

        // 2. Lakhs / Crores representation (e.g. "Rs. 10.5 Lakh", "2.25 Crores", "15 Cr")
        if (preg_match('/(?:Rs\.?|INR|₹)?\s*([0-9]+(?:\.[0-9]+)?)\s*(Lakhs?|Lacs?|Lac|Crores?|Cr\.?|Thousand|K)\b/i', $str, $m)) {
            $val = (float)$m[1];
            $unit = strtolower(trim($m[2]));
            if (in_array($unit, ['crore', 'crores', 'cr', 'cr.'])) {
                $val *= 10000000;
            } elseif (in_array($unit, ['lakh', 'lakhs', 'lac', 'lacs'])) {
                $val *= 100000;
            } elseif (in_array($unit, ['thousand', 'k'])) {
                $val *= 1000;
            }
            $result['numeric']   = (int)round($val);
            $result['formatted'] = self::formatIndianCurrency($result['numeric']);
            return $result;
        }

        // 3. Standard comma-separated or plain Indian numeric digits (e.g. "₹ 31,00,000/-" or "1000000.00")
        if (preg_match('/(?:Rs\.?|INR|₹)?\s*([0-9]{1,3}(?:,[0-9]{2,3})*(?:\.[0-9]{1,2})?)/iu', $str, $m)) {
            $numOnly = str_replace(',', '', $m[1]);
            if (is_numeric($numOnly)) {
                $val = (float)$numOnly;
                $result['numeric']   = (int)round($val);
                $result['formatted'] = self::formatIndianCurrency($result['numeric']);
                return $result;
            }
        }

        // 4. Try parsing amounts written in words (e.g. "Rupees Thirty One Lakhs Only")
        $wordVal = self::parseWords($str);
        if ($wordVal !== null && $wordVal > 0) {
            $result['numeric']   = $wordVal;
            $result['formatted'] = self::formatIndianCurrency($wordVal);
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
        $text = strtolower(preg_replace('/[^a-zA-Z\s]/', ' ', $text));
        $tokens = preg_split('/\s+/', $text);

        $hasMoneyContext = false;
        $total = 0;
        $current = 0;

        foreach ($tokens as $token) {
            if ($token === 'rupees' || $token === 'rupee' || $token === 'inr') {
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
            // Tender fees in India range typically from ₹100 to ₹1,00,000 (usually <= 50,000)
            return $amount >= 50 && $amount <= 500000;
        }

        if ($fieldType === 'emd_value') {
            // EMD is generally >= ₹1,000 and <= ₹10,00,00,000 (10 Cr)
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

