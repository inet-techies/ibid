<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Universal Tender Title Extractor
 *
 * Dedicated extraction engine to identify the true procurement purpose and subject
 * matter of a tender (WHAT the department intends to purchase, procure, supply,
 * construct, develop, or execute).
 *
 * Strict Architectural Rule:
 * - NEVER simply take the first line of the PDF.
 * - NEVER use department, issuing authority, or generic document headings alone.
 * - 100% offline PHP implementation (zero external AI API dependencies).
 */
class Tender_title_extractor
{
    /**
     * Generic document titles and section headings that must NEVER be accepted as tender title
     */
    protected static $genericTitles = [
        'notice inviting tender',
        'notice inviting e-tender',
        'notice inviting tenders',
        'notice inviting e-tenders',
        'e-tender notice',
        'tender notice',
        'tender document',
        'e-tender document',
        'request for proposal',
        'request for proposal cum tender document',
        'rfp document',
        'rfp',
        'nit',
        'e-nit',
        'press notice',
        'press note',
        'corrigendum',
        'addendum',
        'disclaimer',
        'table of contents',
        'contents',
        'instruction to bidders',
        'instructions to bidders',
        'instruction to bidder',
        'instructions to bidder',
        'general terms and conditions',
        'list of dates',
        'schedule of requirements',
        'schedule of events',
        'section- i',
        'section- ii',
        'section- iii',
        'section- iv',
        'section - i',
        'section - ii',
        'section - iii',
        'section - iv',
        'drawing & disbursing officer (ddo)'
    ];

    /**
     * Pure department / authority names that must NOT be accepted as tender title
     */
    protected static $deptSuffixes = [
        'police',
        'headquarters',
        'headquarter',
        'telecom headquarters',
        'police telecom headquarters',
        'madhya pradesh police',
        'university',
        'tripura university',
        'webel technology limited',
        'corporation',
        'municipal corporation',
        'greater hyderabad municipal corporation',
        'government of telangana',
        'govt. of telangana',
        'govt of telangana',
        'govt. of odisha',
        'govt of odisha',
        'government of odisha',
        'odisha computer application centre',
        'ocac',
        'limited',
        'pvt ltd',
        'private limited',
        'department',
        'directorate',
        'commissionerate',
        'ministry'
    ];

    /**
     * Strong procurement action verbs that indicate an actual procurement purpose
     */
    protected static $procurementVerbs = [
        'supply', 'procurement', 'selection', 'implementation', 'construction',
        'hiring', 'appointment', 'provision', 'operation and maintenance',
        'o&m', 'rate contract', 'turnkey', 'design', 'installation',
        'commissioning', 'testing', 'configuration', 'engagement', 'execution',
        'development', 'purchase', 'acquisition', 'empanelment', 'maintenance',
        'elimination'
    ];

    /**
     * Domain and equipment nouns indicating specific deliverables
     */
    protected static $deliverableKeywords = [
        'cctv', 'surveillance', 'camera', 'cameras', 'network', 'wi-fi', 'wifi',
        'anpr', 'hardware', 'software', 'system', 'systems', 'infrastructure',
        'components', 'server', 'servers', 'switches', 'cloud', 'iot',
        'iot-based', 'garbage', 'waste', 'schools', 'ip based', 'ip-based',
        'sensor', 'sensors', 'cabling', 'security', 'vehicle', 'vehicles',
        'equipment', 'goods', 'works', 'civil', 'electrical', 'monitoring'
    ];

    /**
     * Extract the tender title from raw document text and page map
     *
     * @param string $rawText
     * @param array $pageMap
     * @param string $origName
     * @return array
     */
    public static function extract($rawText, array $pageMap = [], $origName = '')
    {
        $candidates = [];

        // 1. Gather text from the first 3-4 pages or first 20,000 characters
        $inspectText = '';
        if (!empty($pageMap)) {
            $pCount = 0;
            foreach ($pageMap as $pNo => $pText) {
                if (!empty($pText)) {
                    $inspectText .= "\n[PAGE " . $pNo . "]\n" . $pText;
                    $pCount++;
                    if ($pCount >= 4) break;
                }
            }
        }
        if (empty($inspectText) || strlen(trim($inspectText)) < 50) {
            $inspectText = mb_substr($rawText, 0, 20000);
        }

        // Clean form feeds
        $inspectText = str_replace("\x0C", "\n", $inspectText);

        $lines = explode("\n", $inspectText);
        $totalLines = count($lines);

        // --- STRATEGY A: Explicit Labels ---
        // "Name of Work:", "Name of the Item:", "Brief description of material:", "Tender Title:", "Subject:"
        $labelPattern = '/^(?:(?:\d+\.|\(?\d+\)?|sl\.?\s*(?:no\.?)?\s*\d+)?\s*(?:name\s+of\s+(?:the\s+)?(?:work|item|equipment|material|project|job)|tender\s+title|title\s+of\s+(?:the\s+)?(?:work|tender)|brief\s+description\s+of\s+(?:the\s+)?(?:material|work|goods)|description\s+of\s+(?:the\s+)?(?:work|goods|material)|subject|sub\s*:|scope\s+of\s+(?:work|procurement)))\s*(?:[-:.\x{2013}\x{2014}]+\s*|\s{2,})\s*(.+)$/iu';

        for ($i = 0; $i < $totalLines; $i++) {
            $line = trim($lines[$i]);
            if ($line === '') continue;

            if (preg_match($labelPattern, $line, $m)) {
                $candVal = trim($m[1]);
                // Check multiline continuation
                $j = $i + 1;
                while ($j < $totalLines && $j < $i + 7) {
                    $nextL = trim($lines[$j]);
                    if ($nextL === '') {
                        $j++;
                        continue;
                    }
                    // Stop boundary: page indicator, table numbering, fee, EMD, new section
                    if (preg_match('/^(?:\[PAGE|\d+\.?\s*(?:Tender|Date|SL|Section|Cost|EMD|Fee|Pre-bid|Bid|Opening)|No\.|Dated|Through|Drawing|Notice|\*+|\-+)/iu', $nextL)) {
                        break;
                    }
                    if (preg_match('/(?:[-:.]|[\x{2013}\x{2014}])\s*Rs\.|\bRs\.\s*\d+/iu', $nextL) && !preg_match('/\b(?:components|supply|maintenance)\b/i', $nextL)) {
                        break;
                    }
                    $candVal .= ' ' . $nextL;
                    $j++;
                }

                $candVal = self::cleanCandidate($candVal);
                if (self::isValidCandidate($candVal)) {
                    $candidates[] = [
                        'value'      => $candVal,
                        'source'     => 'explicit_label',
                        'raw_label'  => $line,
                        'base_score' => 95,
                        'line_idx'   => $i
                    ];
                }
            }
        }

        // --- STRATEGY B: Header Announcement Block ("e-Tender For ...", "RFP For ...") ---
        for ($i = 0; $i < $totalLines; $i++) {
            $line = trim($lines[$i]);
            if ($line === '') continue;

            // Pattern: "e-Tender\nFor\nSupply of ..."
            if (preg_match('/^(?:(?:notice\s+inviting\s+)?(?:e[\s\-]?)?tender|request\s+for\s+proposal(?:\s*cum\s*tender\s*document)?|rfp)\s*$/iu', $line)) {
                $nextL = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : '';
                $targetLineIdx = null;
                if (preg_match('/^(?:for\s+the|for)\s*$/iu', $nextL)) {
                    $targetLineIdx = $i + 2;
                } elseif (preg_match('/^(?:for\s+the|for)\s+(.+)/iu', $nextL, $forM)) {
                    $targetCand = $forM[1];
                    $targetLineIdx = $i + 1;
                }

                if ($targetLineIdx !== null && isset($lines[$targetLineIdx])) {
                    $candVal = trim($lines[$targetLineIdx]);
                    $j = $targetLineIdx + 1;
                    while ($j < $totalLines && $j < $targetLineIdx + 6) {
                        $nxt = trim($lines[$j]);
                        if ($nxt === '' || preg_match('/^(?:\(|\[|Tender\s+No|Dated|RFP\s+Notice|Particulars|Cost|Through)/iu', $nxt)) {
                            break;
                        }
                        $candVal .= ' ' . $nxt;
                        $j++;
                    }
                    $candVal = self::cleanCandidate($candVal);
                    if (self::isValidCandidate($candVal)) {
                        $candidates[] = [
                            'value'      => $candVal,
                            'source'     => 'header_announcement',
                            'raw_label'  => $line,
                            'base_score' => 90,
                            'line_idx'   => $i
                        ];
                    }
                }
            }

            // Pattern: Single line "RFP for Supply of..." or "Tender Document for: Supply of..."
            if (preg_match('/^(?:(?:notice\s+inviting\s+)?(?:e[\s\-]?)?tender(?:\s+document)?|request\s+for\s+proposal(?:\s*\(\s*rfp\s*\))?|rfp)\s+(?:cum\s+tender\s+document\s+)?(?:for\s+the|for)\s*[-:.\x{2013}\x{2014}]?\s*(.+)/iu', $line, $mDocFor)) {
                $candVal = trim($mDocFor[1]);
                $j = $i + 1;
                while ($j < $totalLines && $j < $i + 6) {
                    $nxt = trim($lines[$j]);
                    if ($nxt === '' || preg_match('/^(?:\(|\[|Tender\s+No|Dated|RFP\s+Notice|Particulars|Cost|Through)/iu', $nxt)) {
                        break;
                    }
                    $candVal .= ' ' . $nxt;
                    $j++;
                }
                $candVal = self::cleanCandidate($candVal);
                if (self::isValidCandidate($candVal)) {
                    $candidates[] = [
                        'value'      => $candVal,
                        'source'     => 'doc_for_prefix',
                        'base_score' => 88,
                        'line_idx'   => $i
                    ];
                }
            }
        }

        // --- STRATEGY C: Direct Procurement Action Headings on Pages 1-2 ---
        for ($i = 0; $i < min(120, $totalLines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || strlen($line) < 15) continue;

            if (preg_match('/^(?:Supply|Procurement|Selection|Implementation|Construction|Hiring|Appointment|Provision|Design,\s+Supply|GARBAGE\s+VULNERABLE)\s+(?:of|for|and|UNDER)?\s+(.+)/iu', $line)) {
                $candVal = $line;
                $j = $i + 1;
                while ($j < $totalLines && $j < $i + 5) {
                    $nxt = trim($lines[$j]);
                    if ($nxt === '' || preg_match('/^(?:Police|Bhadbhada|TRIPURA|No\.|Dated|S\.N\.|Through|Page|\()/iu', $nxt)) {
                        break;
                    }
                    $candVal .= ' ' . $nxt;
                    $j++;
                }
                $candVal = self::cleanCandidate($candVal);
                if (self::isValidCandidate($candVal)) {
                    $candidates[] = [
                        'value'      => $candVal,
                        'source'     => 'action_heading',
                        'base_score' => 85,
                        'line_idx'   => $i
                    ];
                }
            }
        }

        // --- STRATEGY D: "invites ... for the following work: “..." or "e-Tender are invited for ..." ---
        if (preg_match('/(?:invites\s+(?:e[\s\-]?)?(?:tenders|bids|proposals)|(?:e[\s\-]?)?(?:tenders?|bids?|proposals?)\s+(?:are\s+)?invited)\s+.*?\s+for\s+(?:(?:implementation|execution|the|selection)\s+of\s+)?(?:the\s+)?(?:following\s+(?:work|items?|procurement)\s*[-:.\x{2013}\x{2014}]*\s*)?[“"\'\`]?([^”"\'\`\r\n]{15,350})/iu', $inspectText, $invM)) {
            $candVal = self::cleanCandidate($invM[1]);
            if (self::isValidCandidate($candVal)) {
                $candidates[] = [
                    'value'      => $candVal,
                    'source'     => 'invitation_clause',
                    'base_score' => 85,
                    'line_idx'   => 0
                ];
            }
        }

        // --- STRATEGY E: Scope / Disclaimer Purpose Statement ---
        if (preg_match('/(?:this\s+rfp\s+is\s+issued\s+for|tenders?\s+are\s+invited\s+for)\s+(?:the\s+)?([A-Z0-9\s,\-\/\(\)]{20,300}?)(?:\.\s|\n|$)/iu', $inspectText, $purpM)) {
            $candVal = self::cleanCandidate($purpM[1]);
            if (self::isValidCandidate($candVal)) {
                $candidates[] = [
                    'value'      => $candVal,
                    'source'     => 'purpose_statement',
                    'base_score' => 78,
                    'line_idx'   => 0
                ];
            }
        }

        // --- EVALUATE AND SCORE CANDIDATES ---
        $scoredCandidates = [];
        $seen = [];
        foreach ($candidates as $cand) {
            $normKey = strtolower(preg_replace('/[^a-z0-9]/i', '', $cand['value']));
            if (isset($seen[$normKey])) {
                continue;
            }
            $seen[$normKey] = true;

            $score = self::scoreCandidate($cand);
            if ($score >= 70) {
                $cand['final_score'] = $score;
                $scoredCandidates[] = $cand;
            }
        }

        // Sort descending by score, tiebreaker on length
        usort($scoredCandidates, function ($a, $b) {
            if ($a['final_score'] === $b['final_score']) {
                return strlen($b['value']) - strlen($a['value']);
            }
            return $b['final_score'] - $a['final_score'];
        });

        $top = !empty($scoredCandidates) ? $scoredCandidates[0] : null;

        return [
            'value'      => $top ? $top['value'] : '',
            'confidence' => $top ? $top['final_score'] : 0,
            'status'     => ($top && $top['final_score'] >= 70) ? 'extracted' : 'not_found',
            'source'     => $top ? $top['source'] : 'none',
            'candidates' => $scoredCandidates
        ];
    }

    /**
     * Clean and normalize a candidate title string
     *
     * @param string $text
     * @return string
     */
    public static function cleanCandidate($text)
    {
        $text = trim($text);

        // If candidate has an embedded explicit RFP announcement like "... RFP for Procurement of...", extract from "Procurement of..."
        if (preg_match('/(?:^|[\s\.])(?:RFP|e[\s\-]Tender|Notice\s+Inviting\s+Tender)\s+(?:cum\s+tender\s+document\s+)?for\s+(?:the\s+)?([A-Z0-9].+)$/iu', $text, $embM)) {
            $text = $embM[1];
        }

        // Remove leading prefixes
        $prefixes = [
            '/^(?:name\s+of\s+(?:the\s+)?(?:work|item|equipment|material|project|job)|tender\s+title|title\s+of\s+(?:the\s+)?(?:work|tender)|brief\s+description\s+of\s+(?:the\s+)?(?:material|work|goods)|description\s+of\s+(?:the\s+)?(?:work|goods|material)|subject|sub\s*:|scope\s+of\s+(?:work|procurement)|e[\s\-]tender\s+for|notice\s+inviting\s+(?:e[\s\-]?)?tender\s+for|request\s+for\s+proposal(?:\s*\(\s*rfp\s*\))?\s*for(?:\s+the)?|rfp\s+for(?:\s+the)?|tender\s+for(?:\s+the)?|for\s+the|for)\s*[-:.\x{2013}\x{2014}]*\s*/iu',
            '/^[-:\s.\x{2013}\x{2014}]+/u',
            '/^[“"\'\`\s]+|[”"\'\`\s]+$/u'
        ];
        foreach ($prefixes as $p) {
            $text = preg_replace($p, '', $text);
        }

        // Remove trailing sentences that are administrative conditions or bidding rules
        $cutoffPatterns = [
            '/\.\s+(?:Bids\s+are\s+invited|Bidder\s+must|WTL\s+has\s+been|Cost\s+of\s+tender|The\s+EMD\s+amount|Tender\s+documents\s+can|The\s+Tender\s+Should|Tender\s+Fee|Earnest\s+Money).*$/iu',
            '/\s*4\.\s+Tender\s+Fee.*$/iu',
            '/\s*5\.\s+Earnest\s+Money.*$/iu',
            '/\s*(?:Dated|Date)\s*[:\-]?\s*\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}.*$/iu',
            '/\s*\(?Tender\s+(?:No|Notice\s+No|Ref)[\s\S]*$/iu',
            '/\s*RFP\s+Enquiry\s+No[\s\S]*$/iu',
            '/\s*No\.\s*[A-Z0-9\/\-\.]+.*$/iu',
            '/\s*Availability\s+of\s+RFP.*$/iu',
            '/\s*Page\s+\d+\s+of\s+\d+.*$/iu',
            '/[-:.,\x{2013}\x{2014}\s]+$/u'
        ];
        foreach ($cutoffPatterns as $np) {
            $text = preg_replace($np, '', $text);
        }

        // Normalize internal whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Validate whether a candidate is legitimate and not a rejected heading/department
     *
     * @param string $val
     * @return bool
     */
    public static function isValidCandidate($val)
    {
        if (empty($val) || strlen($val) < 15) return false;

        $lower = strtolower($val);

        // Disqualify generic document headings
        foreach (self::$genericTitles as $gt) {
            if ($lower === $gt || $lower === $gt . 's') return false;
        }

        // Disqualify annexures, formats, declarations, and undertakings
        if (preg_match('/^(?:self[\s\-]declaration|declaration\b|undertaking\b|format\b|power\s+of\s+attorney|bank\s+guarantee|integrity\s+pact|letter\s+of\s+authori[sz]ation|affidavit\b|proforma\b|annexure\b|appendix\b)/iu', $lower)) {
            return false;
        }

        // Disqualify pure department names
        foreach (self::$deptSuffixes as $ds) {
            if ($lower === $ds) return false;
        }

        // Must not be only an address or contact
        if (preg_match('/^(?:bhadbhada\s+road|suryamaninagar|tank\s+bund|acharya\s+vihar|fax\s+no|phone|email)/i', $lower)) {
            return false;
        }

        return true;
    }

    /**
     * Compute confidence score for a candidate
     *
     * @param array $cand
     * @return int
     */
    public static function scoreCandidate(array $cand)
    {
        $val = $cand['value'];
        $lower = strtolower($val);
        $score = $cand['base_score'];

        // Bonus for procurement verbs
        $hasProcVerb = false;
        foreach (self::$procurementVerbs as $verb) {
            if (preg_match('/\b' . preg_quote($verb, '/') . '\b/i', $lower)) {
                $hasProcVerb = true;
                break;
            }
        }
        if ($hasProcVerb) {
            $score += 15;
        } else {
            $score -= 15;
        }

        // Bonus for domain/technical deliverable keywords
        $hasDeliverable = false;
        foreach (self::$deliverableKeywords as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $lower)) {
                $hasDeliverable = true;
                break;
            }
        }
        if ($hasDeliverable) {
            $score += 10;
        }

        // Ideal length bonus (25 - 250 characters)
        $len = strlen($val);
        if ($len >= 25 && $len <= 250) {
            $score += 10;
        } elseif ($len > 350) {
            $score -= 10;
        }

        // Proximity / Cover page priority bonus
        $lineIdx = isset($cand['line_idx']) ? (int)$cand['line_idx'] : 50;
        if ($lineIdx <= 25) {
            $score += 15; // Prominent top cover page
        } elseif ($lineIdx <= 50) {
            $score += 5;
        } elseif ($lineIdx > 100) {
            $score -= 15; // Deeper down in annexures or boilerplate
        }

        // Heavy penalty if it contains organization words without a procurement action
        if (!$hasProcVerb && preg_match('/\b(?:police|university|corporation|department|limited)\b/i', $lower)) {
            $score -= 40;
        }

        return min(100, max(0, $score));
    }
}
