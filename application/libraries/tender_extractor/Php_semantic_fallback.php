 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 100% PHP-Based Offline Semantic Extraction Fallback
 *
 * Replaces all remote AI / API-key requirements with deterministic PHP-based
 * offline heuristic analysis, contextual window extraction, and dictionary mapping.
 * Strictly operates offline with zero external network requests.
 */
class Php_semantic_fallback
{
    /**
     * Run offline semantic extraction for missing or low-confidence fields
     *
     * @param array $unresolvedFields Array of field keys requiring fallback
     * @param string $fullText Normalized document text
     * @param array $pageMap Optional page-by-page text map [pageNo => text]
     * @return array Array of candidates by field: fieldKey => candidate array
     */
    public static function extractTargetedFields(array $unresolvedFields, $fullText, array $pageMap = [])
    {
        $results = [];
        if (empty($unresolvedFields) || empty($fullText)) {
            return $results;
        }

        require_once __DIR__ . '/Date_extractor.php';
        require_once __DIR__ . '/Amount_extractor.php';

        foreach ($unresolvedFields as $field) {
            $windowInfo = self::findTargetedWindow($field, $fullText, $pageMap);
            if (empty($windowInfo['text'])) {
                continue;
            }

            $cand = self::analyzeTargetedWindow($field, $windowInfo);
            if ($cand && !empty($cand['value'])) {
                $results[$field] = $cand;
            }
        }

        return $results;
    }

    /**
     * Locate the most relevant page/text window for a given field
     *
     * @param string $field
     * @param string $fullText
     * @param array $pageMap
     * @return array ['text' => string, 'page' => int|null]
     */
    protected static function findTargetedWindow($field, $fullText, array $pageMap = [])
    {
        $keywords = [
            'tender_reference'             => ['tender no', 'tender id', 'nit no', 'rfp no', 'bid reference', 'gem bid', 'bid/ra'],
            'published_date'               => ['published date', 'publication date', 'nit date', 'date of issue', 'tender notice date'],
            'nit_issue_date'               => ['nit date', 'notice date', 'issue date', 'date of nit'],
            'document_download_start_date' => ['download start', 'sale start', 'document sale start', 'tender download'],
            'document_download_end_date'   => ['download closing', 'sale close', 'document sale end'],
            'submission_date'              => ['submission end', 'closing date', 'due date', 'last date of submission', 'last date and time of bid submission', 'bid due date'],
            'submission_time'              => ['submission time', 'due time', 'closing time', 'upto', 'hrs'],
            'technical_opening_date'       => ['technical bid opening', 'opening of technical', 'cover 1 opening', 'technical proposals'],
            'financial_opening_date'       => ['financial bid opening', 'price bid opening', 'commercial bid opening', 'cover 2 opening'],
            'pre_bid_date'                 => ['pre-bid meeting', 'pre bid conference', 'clarification meeting', 'pre-bid discussion'],
            'query_deadline'               => ['query submission', 'clarification deadline', 'last date for queries', 'receiving queries'],
            'bid_submission_start_date'    => ['submission start', 'bid submission start', 'start date of submission'],
            'emd_value'                    => ['earnest money', 'emd amount', 'bid security', 'value of emd'],
            'tender_fee'                   => ['tender fee', 'cost of tender', 'document fee', 'processing fee'],
            'tender_portal'                => ['eprocure', 'portal', 'website', 'online submission', 'etenders', 'gem.gov.in']
        ];

        $fieldKeywords = $keywords[$field] ?? [str_replace('_', ' ', $field)];

        // Search through page map first for accurate page attribution
        if (!empty($pageMap)) {
            foreach ($pageMap as $pageNo => $pageText) {
                foreach ($fieldKeywords as $kw) {
                    $pos = stripos($pageText, $kw);
                    if ($pos !== false) {
                        $start = max(0, $pos - 150);
                        $len   = min(1500, strlen($pageText) - $start);
                        return [
                            'text' => substr($pageText, $start, $len),
                            'page' => (int)$pageNo
                        ];
                    }
                }
            }
        }

        // Fallback to fullText scanning
        foreach ($fieldKeywords as $kw) {
            $pos = stripos($fullText, $kw);
            if ($pos !== false) {
                $start = max(0, $pos - 150);
                $len   = min(1500, strlen($fullText) - $start);
                return [
                    'text' => substr($fullText, $start, $len),
                    'page' => null
                ];
            }
        }

        return ['text' => '', 'page' => null];
    }

    /**
     * Offline semantic analyzer for targeted text snippet
     *
     * @param string $field
     * @param array $windowInfo
     * @return array|null
     */
    protected static function analyzeTargetedWindow($field, array $windowInfo)
    {
        $text = $windowInfo['text'];
        $page = $windowInfo['page'];

        // Dates & Times: Never guess or borrow dates/times by proximity.
        // Milestones not explicitly scheduled or labeled in the tender document must remain blank/not_found.
        if (strpos($field, 'date') !== false || strpos($field, 'time') !== false || $field === 'query_deadline') {
            return null;
        }

        // Amounts
        if ($field === 'emd_value' || $field === 'tender_fee') {
            $amtRes = Amount_extractor::parse($text);
            if ($amtRes['numeric'] !== null) {
                if (Amount_extractor::isPlausibleForField($amtRes['numeric'], $field)) {
                    return [
                        'value'      => (string)$amtRes['numeric'],
                        'raw_value'  => $amtRes['formatted'],
                        'confidence' => 76,
                        'method'     => 'context_based',
                        'page'       => $page,
                        'evidence'   => 'Offline semantic window: ' . substr(trim(preg_replace('/\s+/', ' ', $text)), 0, 140)
                    ];
                }
            }
        }

        return null;
    }
}
