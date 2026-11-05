<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Corrigendum & Tender Amendment Detector
 *
 * Detects whether an uploaded tender document or section is an amendment, corrigendum,
 * or date extension notice. Disambiguates original vs revised values, flags amendments,
 * and tracks revision evidence.
 */
class Corrigendum_detector
{
    /**
     * Detect if document or text is a corrigendum/addendum
     *
     * @param string $text
     * @param string $filename
     * @return array ['is_corrigendum' => bool, 'type' => string, 'confidence' => int, 'original_ref' => string|null, 'revisions' => array]
     */
    public static function analyze($text, $filename = '')
    {
        $result = [
            'is_corrigendum' => false,
            'type'           => 'standard_tender',
            'confidence'     => 0,
            'original_ref'   => null,
            'evidence'       => '',
            'revisions'      => []
        ];

        $headerScope = substr($text, 0, 8000);

        // 1. Keyword detection in filename and header
        $corrigendumMatches = [];
        $patterns = [
            'corrigendum' => '/\b(?:Corrigendum|Corrigenda)(?:\s*(?:No\.?|Number|Notice|\-\s*\d+|\s*\d+))?\b/iu',
            'addendum'    => '/\b(?:Addendum|Addenda)(?:\s*(?:No\.?|Number|Notice|\-\s*\d+|\s*\d+))?\b/iu',
            'extension'   => '/\b(?:Extension\s+Notice|Notice\s+for\s+Extension\s+of\s+(?:Date|Time|Bid\s+Submission)|Date\s+Extension)\b/iu',
            'amendment'   => '/\b(?:Amendment(?:\s*(?:No\.?|Number|\-\s*\d+|\s*\d+))?|Revised\s+(?:Schedule|Notice|NIT))\b/iu'
        ];

        foreach ($patterns as $type => $pat) {
            if (preg_match($pat, $filename, $m)) {
                $result['is_corrigendum'] = true;
                $result['type']           = $type;
                $result['confidence']     = 95;
                $result['evidence']       = 'Filename contains ' . $m[0];
                break;
            }
            if (preg_match($pat, $headerScope, $m)) {
                $result['is_corrigendum'] = true;
                $result['type']           = $type;
                $result['confidence']     = 90;
                $result['evidence']       = 'Header contains ' . $m[0];
                break;
            }
        }

        if (!$result['is_corrigendum']) {
            return $result;
        }

        // 2. Extract original tender reference cited in the corrigendum
        if (preg_match('/(?:Reference(?:\s+to)?\s+NIT\s+No\.?|Ref(?:\s*No\.?)?\s*[:\-]|with\s+reference\s+to\s+tender\s+no\.?|in\s+continuation\s+to\s+(?:tender|NIT)\s+no\.?)\s*[:\t\- ]*([A-Za-z0-9][A-Za-z0-9\/_\-:\.]{3,70})/iu', $headerScope, $rm)) {
            $result['original_ref'] = trim($rm[1]);
        }

        // 3. Scan for "Existing Date / As Per Tender" vs "Read As / Revised / Extended Date"
        // Common Indian tender table format:
        // Sl No | Activity | Existing Date | Extended / Revised Date
        $revisionPatterns = [
            'submission_date' => [
                '/(?:Last\s+Date\s+of\s+Submission|Bid\s+Submission\s+End\s+Date)[^\n\r]*?\b(?:Existing|Old|Original)\s*[:\- ]*([^\n\r,;]+)[^\n\r]*?\b(?:Revised|Extended|Amended|Read\s+As)\s*[:\- ]*([^\n\r,;]+)/iu',
                '/(?:Revised|Extended)\s+(?:Last\s+Date\s+of\s+Submission|Bid\s+Submission\s+End\s+Date)\s*[:\t\- ]*([^\n\r,;]{6,60})/iu'
            ],
            'technical_opening_date' => [
                '/(?:Technical\s+Bid\s+Opening)[^\n\r]*?\b(?:Existing|Old|Original)\s*[:\- ]*([^\n\r,;]+)[^\n\r]*?\b(?:Revised|Extended|Amended|Read\s+As)\s*[:\- ]*([^\n\r,;]+)/iu',
                '/(?:Revised|Extended)\s+(?:Date\s+of\s+Technical\s+Bid\s+Opening|Technical\s+Opening)\s*[:\t\- ]*([^\n\r,;]{6,60})/iu'
            ]
        ];

        foreach ($revisionPatterns as $field => $pats) {
            foreach ($pats as $pat) {
                if (preg_match($pat, $text, $rm)) {
                    $revisedRaw = isset($rm[2]) ? trim($rm[2]) : trim($rm[1]);
                    $result['revisions'][$field] = [
                        'raw_revised' => $revisedRaw,
                        'original'    => isset($rm[2]) ? trim($rm[1]) : null,
                        'evidence'    => trim($rm[0])
                    ];
                    break;
                }
            }
        }

        return $result;
    }
}

