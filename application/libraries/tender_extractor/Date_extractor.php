<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intelligent Tender Date & Time Extractor
 *
 * Handles parsing, disambiguation, time separation, Gregorian validation, and ISO standard normalization (YYYY-MM-DD)
 * across Indian government (Central/State), GeM, PSU, municipal, and private-sector tender documents.
 * Tolerates irregular spacing around delimiters, OCR errors, 2-digit years, and multiline table layouts.
 */
class Date_extractor
{
    /**
     * Named month map
     */
    protected static $monthMap = [
        'jan' => 1, 'january' => 1,
        'feb' => 2, 'february' => 2,
        'mar' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'may' => 5,
        'jun' => 6, 'june' => 6,
        'jul' => 7, 'july' => 7,
        'aug' => 8, 'august' => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12
    ];

    /**
     * Regex for Indian & International date patterns with tolerance for spaces around separators:
     * - DD/MM/YYYY, DD-MM-YYYY, DD.MM.YYYY (e.g. "16/09 / 2026", "07. 09. 2026", "25 - 08 - 2026")
     * - YYYY-MM-DD, YYYY/MM/DD, YYYY.MM.DD
     * - DD Month YYYY (e.g. "22nd September 2026", "01-Sep-2026")
     * - Month DD, YYYY (e.g. "September 22, 2026")
     */
    protected static $dateRegex = '/(?:(?P<d1>\d{1,2})\s*[\.\/\-]\s*(?P<m1>\d{1,2})\s*[\.\/\-]\s*(?P<y1>\d{2,4})|(?P<y2>\d{4})\s*[\.\/\-]\s*(?P<m2>\d{1,2})\s*[\.\/\-]\s*(?P<d2>\d{1,2})|(?P<d3>\d{1,2})(?:st|nd|rd|th)?\s*[\-\/\s\.]\s*(?P<mon>[A-Za-z]{3,9})\s*[\-\/\s,\.]*\s*(?P<y3>\d{2,4})|(?P<mon2>[A-Za-z]{3,9})\s+(?P<d4>\d{1,2})(?:st|nd|rd|th)?[\s,]+(?P<y4>\d{2,4}))/i';

    /**
     * Regex for time patterns:
     * - "04 :05 PM", "4:00 PM", "2 PM", "14:00", "15:00 hrs", "12:30 PM", "11.30 AM"
     */
    protected static $timeRegex = '/(?:\b(?:at|till|upto|up\s*to|by|@|[-–])\s*)?(?P<hour>\d{1,2})(?:\s*[:\.]\s*(?P<min>\d{2}))?(?:\s*[:\.]\s*(?P<sec>\d{2}))?\s*(?P<ampm>am|pm|a\.m\.|p\.m\.|hrs|hours)?/i';

    /**
     * Parse any raw date string into an ISO YYYY-MM-DD format
     *
     * @param string $raw
     * @return string|null ISO YYYY-MM-DD or null if invalid
     */
    public static function toIsoDate($raw)
    {
        if (empty($raw)) return null;

        $raw = trim($raw);

        // Remove Dt:, Dt., Date: prefixes if any
        $cleaned = preg_replace('/^(?:Dt\.?|Date[:\s]*|Dated[:\s]*)/i', '', $raw);
        $cleaned = trim($cleaned);

        // Already ISO YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $cleaned, $m)) {
            $y = (int)$m[1]; $mon = (int)$m[2]; $d = (int)$m[3];
            if (checkdate($mon, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $mon, $d);
            }
        }

        // Run regex match
        if (preg_match(self::$dateRegex, $cleaned, $m)) {
            $y = 0; $mon = 0; $d = 0;

            if (!empty($m['d1']) && !empty($m['m1']) && !empty($m['y1'])) {
                $d   = (int)$m['d1'];
                $mon = (int)$m['m1'];
                $y   = (int)$m['y1'];
            } elseif (!empty($m['y2']) && !empty($m['m2']) && !empty($m['d2'])) {
                $y   = (int)$m['y2'];
                $mon = (int)$m['m2'];
                $d   = (int)$m['d2'];
            } elseif (!empty($m['d3']) && !empty($m['mon']) && !empty($m['y3'])) {
                $d   = (int)$m['d3'];
                $monName = strtolower(substr(trim($m['mon']), 0, 3));
                $mon = self::$monthMap[$monName] ?? 0;
                $y   = (int)$m['y3'];
            } elseif (!empty($m['mon2']) && !empty($m['d4']) && !empty($m['y4'])) {
                $d   = (int)$m['d4'];
                $monName = strtolower(substr(trim($m['mon2']), 0, 3));
                $mon = self::$monthMap[$monName] ?? 0;
                $y   = (int)$m['y4'];
            }

            // Two-digit year expansion: e.g. 26 -> 2026
            if ($y > 0 && $y < 100) {
                $y += ($y < 50 ? 2000 : 1900);
            }

            // Plausible tender date validation: Gregorian validity and sensible year range
            if ($y >= 2015 && $y <= 2040 && $mon >= 1 && $mon <= 12 && $d >= 1 && $d <= 31) {
                if (checkdate($mon, $d, $y)) {
                    return sprintf('%04d-%02d-%02d', $y, $mon, $d);
                }
            }
        }

        return null;
    }

    /**
     * Normalize time string into consistent 24-hour HH:MM format
     *
     * @param string $raw
     * @return string|null e.g. "17:00", "15:00", "10:30", "12:00"
     */
    public static function normalizeTime($raw)
    {
        if (empty($raw)) return null;

        $raw = trim($raw);

        // 1. "12.00 Noon" or "12:00 Noon" or "12 Noon" or "Noon"
        if (preg_match('/(?:(\d{1,2})(?:\s*[:\.]\s*(\d{2}))?\s*)?\bnoon\b/i', $raw, $m)) {
            $min = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
            return sprintf('12:%02d', $min);
        }

        // 2. "12.00 Midnight" or "12 Midnight" or "Midnight"
        if (preg_match('/(?:(\d{1,2})(?:\s*[:\.]\s*(\d{2}))?\s*)?\bmidnight\b/i', $raw, $m)) {
            return '00:00';
        }

        // 3. 4-digit military time without colon: "1700 Hrs", "1700 hrs", "1700 hours", "1700"
        if (preg_match('/^(\d{2})(\d{2})\s*(?:hrs|hours)?$/i', $raw, $m)) {
            $h = (int)$m[1]; $min = (int)$m[2];
            if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
                return sprintf('%02d:%02d', $h, $min);
            }
        }

        // 4. "15:00 Hrs" or "15.00 hrs" or "15 : 00 hours"
        if (preg_match('/(\d{1,2})\s*[:\.]\s*(\d{2})\s*(?:hrs|hours)/i', $raw, $m)) {
            $h = (int)$m[1]; $min = (int)$m[2];
            if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
                return sprintf('%02d:%02d', $h, $min);
            }
        }

        // 5. "04 :05 PM" or "3:00 PM" or "2 PM" or "10.30 AM" or "10:30 am"
        if (preg_match('/(\d{1,2})(?:\s*[:\.]\s*(\d{2}))?\s*(am|pm|a\.m\.|p\.m\.)/i', $raw, $m)) {
            $h = (int)$m[1];
            $min = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
            $ampm = strtoupper(str_replace('.', '', $m[3]));
            if ($ampm === 'PM' && $h < 12) {
                $h += 12;
            } elseif ($ampm === 'AM' && $h === 12) {
                $h = 0;
            }
            if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
                return sprintf('%02d:%02d', $h, $min);
            }
        }

        // 6. "15:00" or "17:00" 24-hour format
        if (preg_match('/^(\d{1,2})\s*[:\.]\s*(\d{2})$/', $raw, $m)) {
            $h = (int)$m[1]; $min = (int)$m[2];
            if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
                return sprintf('%02d:%02d', $h, $min);
            }
        }

        return null;
    }

    /**
     * Extract date and optional time from a text passage
     *
     * @param string $passage
     * @return array ['iso_date' => string|null, 'time' => string|null, 'raw_matched' => string]
     */
    public static function extractFromPassage($passage)
    {
        $result = [
            'iso_date'    => null,
            'time'        => null,
            'raw_matched' => ''
        ];

        if (empty($passage)) return $result;

        // Run regex match for first date
        if (preg_match(self::$dateRegex, $passage, $dm, PREG_OFFSET_CAPTURE)) {
            $rawDate = $dm[0][0];
            $offset  = $dm[0][1];
            $result['iso_date']    = self::toIsoDate($rawDate);
            $result['raw_matched'] = trim($rawDate);

            // Search for time strictly AFTER the date to avoid matching DD.MM as HH:MM
            $afterDate = substr($passage, $offset + strlen($rawDate), 80);

            if (preg_match('/(?:\b(?:at|till|upto|up\s*to|by|@|[-–])\s*)?(\d{1,2}(?:\s*[:\.]\s*\d{2})?\s*(?:am|pm|a\.m\.|p\.m\.|noon|midnight|hrs|hours)|\d{4}\s*(?:hrs|hours)|\d{1,2}\s*[:\.]\s*\d{2})/i', $afterDate, $tm)) {
                $result['time'] = self::normalizeTime($tm[1]);
                if (!empty($result['time'])) {
                    $result['raw_matched'] .= ' at ' . $result['time'];
                }
            }
        }

        return $result;
    }

    /**
     * Find all date candidates in a given text block
     *
     * @param string $text
     * @param int|null $pageNo
     * @param string $section
     * @return array List of candidate date objects
     */
    public static function findAllDateCandidates($text, $pageNo = null, $section = '')
    {
        $candidates = [];
        if (empty($text)) return $candidates;

        if (preg_match_all(self::$dateRegex, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $rawDate = trim($match[0]);
                $offset  = $match[1];
                $iso     = self::toIsoDate($rawDate);
                if (!$iso) continue;

                // Grab surrounding window (up to 80 chars before and 100 chars after)
                $start = max(0, $offset - 80);
                $len   = min(strlen($text) - $start, strlen($rawDate) + 180);
                $context = trim(substr($text, $start, $len));

                // Find time in vicinity
                $time = null;
                $afterDate = substr($text, $offset + strlen($rawDate), 60);
                if (preg_match('/(?:\b(?:at|till|upto|up\s*to|by|@|[-–])\s*)?(\d{1,2}(?:\s*[:\.]\s*\d{2})?\s*(?:am|pm|a\.m\.|p\.m\.|noon|midnight|hrs|hours)|\d{4}\s*(?:hrs|hours)|\d{1,2}\s*[:\.]\s*\d{2})/i', $afterDate, $tm)) {
                    $time = self::normalizeTime($tm[1]);
                }

                $candidates[] = [
                    'raw_date'             => $rawDate,
                    'iso_date'             => $iso,
                    'time'                 => $time,
                    'page'                 => $pageNo,
                    'section'              => $section,
                    'surrounding_sentence' => preg_replace('/\s+/', ' ', $context),
                    'offset'               => $offset
                ];
            }
        }

        return $candidates;
    }

    /**
     * Disambiguate multiple extracted dates based on document context and logical sequence
     *
     * @param array $extractedDates Associative array: field_name => ['iso_date', 'confidence', 'evidence', 'page']
     * @return array Validated dates with resolved conflicts
     */
    public static function validateSequence(array $extractedDates)
    {
        $sequence = [
            'published_date',
            'nit_issue_date',
            'document_download_start_date',
            'document_download_end_date',
            'prebid_query_start_date',
            'query_deadline',
            'pre_bid_date',
            'prebid_response_date',
            'bid_submission_start_date',
            'submission_date',
            'pq_bid_opening_date',
            'technical_opening_date',
            'financial_opening_date'
        ];

        // Ensure chronological plausibility
        $lastTimestamp = null;
        $validated = $extractedDates;

        foreach ($sequence as $field) {
            if (!empty($validated[$field]['value'])) {
                $iso = $validated[$field]['value'];
                $ts  = strtotime($iso);
                if ($ts !== false) {
                    if ($lastTimestamp !== null && $ts < ($lastTimestamp - 86400 * 2)) {
                        // Chronological anomaly: e.g. opening date is earlier than submission date
                        if (isset($validated[$field]['confidence'])) {
                            $validated[$field]['confidence'] = max(30, $validated[$field]['confidence'] - 30);
                            $validated[$field]['status'] = 'review_needed';
                            $validated[$field]['warning'] = 'Chronological order anomaly with preceding schedule dates';
                        }
                    }
                    $lastTimestamp = $ts;
                }
            }
        }

        return $validated;
    }
}
