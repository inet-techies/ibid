<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Universal Tender Text Normalizer
 *
 * Implements a dual-layer normalization pipeline:
 *  - Preserves the pristine original extracted text unaltered.
 *  - Generates a normalized text layer with OCR error repair, ligature healing,
 *    Indian number formatting, and noise suppression.
 */
class Tender_normalizer
{
    /**
     * Common OCR and PDF text ligature breaks
     */
    protected static $ligatureRepairs = [
        '/\bInstalla\s+tion\b/i'   => 'Installation',
        '/\bSubmis\s+sion\b/i'     => 'Submission',
        '/\bSpeci\s+fication\b/i'  => 'Specification',
        '/\bTechni\s+cal\b/i'      => 'Technical',
        '/\bCommer\s+cial\b/i'     => 'Commercial',
        '/\bDocu\s+ment\b/i'       => 'Document',
        '/\bProcure\s+ment\b/i'    => 'Procurement',
        '/\bGovern\s+ment\b/i'     => 'Government',
        '/\bAutho\s+rity\b/i'      => 'Authority',
        '/\bSecu\s+rity\b/i'       => 'Security',
        '/\bDepo\s+sit\b/i'        => 'Deposit',
        '/\bQualifi\s+cation\b/i'  => 'Qualification',
        '/\bPerfor\s+mance\b/i'    => 'Performance',
        '/\bGuaran\s+tee\b/i'      => 'Guarantee',
        '/\bCorri\s+gendum\b/i'    => 'Corrigendum',
        '/\bAmend\s+ment\b/i'      => 'Amendment',
        '/\bInfor\s+mation\b/i'    => 'Information',
        '/\bClarifi\s+cation\b/i'  => 'Clarification',
        '/\bOrgani\s+zation\b/i'   => 'Organization',
        '/\bDepart\s+ment\b/i'     => 'Department',
        '/\bSched\s+ule\b/i'       => 'Schedule',
        '/\bFinan\s+cial\b/i'      => 'Financial',
        '/\bConfe\s+rence\b/i'     => 'Conference',
        '/\bDown\s+load\b/i'       => 'Download',
        '/\bUp\s+load\b/i'         => 'Upload',
        '/\be\s*[\-\.]\s*Tender\b/i' => 'e-Tender',
        '/\be\s*[\-\.]\s*Procure/i' => 'e-Procure'
    ];

    /**
     * Normalize extracted text while maintaining the original text
     *
     * @param string $rawText The original extracted text
     * @return array ['raw' => string, 'normalized' => string, 'lines' => array]
     */
    public static function process($rawText)
    {
        $normalized = (string)$rawText;

        // 1. Convert non-breaking spaces and irregular unicode whitespaces to standard space
        $normalized = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $normalized);

        // 2. Normalize Windows/Mac line breaks to Unix LF
        $normalized = str_replace(["\r\n", "\r"], "\n", $normalized);

        // 3. Repair common PDF/OCR intra-word breaks and ligatures
        foreach (self::$ligatureRepairs as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized);
        }

        // 4. Heal intra-word hyphenation at line breaks (e.g. "submis-\nsion" -> "submission")
        $normalized = preg_replace('/([A-Za-z]{3,})\-\s*\n\s*([A-Za-z]{2,})/u', '$1$2', $normalized);

        // 5. Clean common OCR misreads in numeric blocks
        // e.g. "Rs. l5,000" -> "Rs. 15,000" or "O5-08-2026" -> "05-08-2026"
        $normalized = preg_replace_callback('/(?:Rs\.?|INR|₹)\s*([0-9lIO,.\s]+)/iu', function($m) {
            $cleaned = str_replace(['l', 'I'], '1', $m[1]);
            $cleaned = str_replace(['O', 'o'], '0', $cleaned);
            return 'Rs. ' . $cleaned;
        }, $normalized);

        // 6. Suppress isolated page numbers / running headers: "Page X of Y"
        $normalized = preg_replace('/^[ \t]*(?:Page\s*\d+\s*(?:of|\/)\s*\d+|\d+\s*(?:of|\/)\s*\d+)[ \t]*$/im', '', $normalized);

        // 7. Collapse excessive horizontal spaces while keeping line breaks intact
        $normalized = preg_replace('/[ \t]{2,}/', ' ', $normalized);

        // 8. Collapse 3+ consecutive line breaks into 2
        $normalized = preg_replace('/\n{3,}/', "\n\n", $normalized);

        // Build array of clean non-empty lines for line-based extraction
        $rawLines = explode("\n", $normalized);
        $lines = [];
        foreach ($rawLines as $l) {
            $trimmed = trim($l);
            if ($trimmed !== '') {
                $lines[] = $trimmed;
            }
        }

        return [
            'raw'        => (string)$rawText,
            'normalized' => trim($normalized),
            'lines'      => $lines
        ];
    }

    /**
     * Strip repeated running headers and footers across page text chunks
     *
     * @param array $pages Array of page texts indexed by page number (1-based)
     * @return array Cleaned pages
     */
    public static function stripRunningHeadersFooters(array $pages)
    {
        if (count($pages) < 3) {
            return $pages;
        }

        // Count frequency of top 2 and bottom 2 lines of each page
        $topLines = [];
        $bottomLines = [];
        $totalPages = count($pages);

        foreach ($pages as $pNum => $text) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
            if (!empty($lines)) {
                $first = substr($lines[0], 0, 80);
                $topLines[$first] = ($topLines[$first] ?? 0) + 1;
            }
            if (count($lines) > 1) {
                $last = substr($lines[count($lines) - 1], 0, 80);
                $bottomLines[$last] = ($bottomLines[$last] ?? 0) + 1;
            }
        }

        // Identify recurring patterns appearing on more than 60% of pages
        $threshold = max(2, (int)($totalPages * 0.6));
        $repeatedHeaders = array_keys(array_filter($topLines, function($c) use ($threshold) { return $c >= $threshold; }));
        $repeatedFooters = array_keys(array_filter($bottomLines, function($c) use ($threshold) { return $c >= $threshold; }));

        $cleanedPages = [];
        foreach ($pages as $pNum => $text) {
            $lines = explode("\n", $text);
            $cleanLines = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                $isHeader = false;
                foreach ($repeatedHeaders as $rh) {
                    if (strpos($trimmed, $rh) === 0) {
                        $isHeader = true;
                        break;
                    }
                }
                $isFooter = false;
                foreach ($repeatedFooters as $rf) {
                    if (strpos($trimmed, $rf) === 0) {
                        $isFooter = true;
                        break;
                    }
                }
                if (!$isHeader && !$isFooter) {
                    $cleanLines[] = $line;
                }
            }
            $cleanedPages[$pNum] = implode("\n", $cleanLines);
        }

        return $cleanedPages;
    }
}

