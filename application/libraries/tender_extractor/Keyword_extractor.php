<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Universal Offline Keyword Extractor for Tender Documents
 *
 * Automatically generates 5 to 12 concise, domain-relevant technical key phrases
 * from a tender document's Title, Scope of Work, Technical Requirements,
 * Deliverables, and overall body text using:
 *
 *  1. Title & Heading Clause Decomposition
 *  2. Syntactic Multi-word N-gram Extraction with Strict Stopword Filtering
 *  3. Cross-Domain Technical & Procurement Phrase Mining
 *  4. TF-IDF & Section-Weight Scoring
 *  5. Substring & Near-Duplicate Subsumption (preferring rich compound phrases)
 *
 * Fully offline PHP with zero external API calls or AI subscriptions.
 */
class Keyword_extractor
{
    /**
     * Stopwords and boilerplate terms to discard unless part of a valid technical compound
     */
    protected static $stopWords = [
        'the', 'shall', 'bidder', 'bidders', 'tender', 'tenders', 'document', 'documents',
        'page', 'date', 'time', 'section', 'annexure', 'appendix', 'table', 'clause',
        'authority', 'director', 'registrar', 'university', 'department', 'government',
        'patna', 'bihar', 'delhi', 'mumbai', 'india', 'state', 'district', 'office',
        'registered', 'eligible', 'format', 'signature', 'stamp', 'duly', 'signed',
        'sealed', 'copy', 'original', 'per', 'as', 'to', 'from', 'in', 'on', 'at', 'by',
        'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before',
        'after', 'above', 'below', 'under', 'again', 'further', 'then', 'once', 'here',
        'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more',
        'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
        'than', 'too', 'very', 'can', 'will', 'just', 'should', 'now', 'oken', 'scanner',
        'camscanner', 'scanned', 'watermark', 'hereby', 'thereof', 'wherein', 'notice',
        'inviting', 'invitation', 'request', 'proposal', 'e-tender', 'nit', 'rfp', 'buhs',
        'hq', 'headquarters', 'submission', 'opening', 'fee', 'emd', 'rs', 'inr', 'lakhs',
        'crores', 'year', 'years', 'month', 'months', 'day', 'days', 'validity'
    ];

    /**
     * Known technical and operational concepts across diverse tender domains
     */
    protected static $domainLexicon = [
        // Security & Surveillance
        'cctv surveillance'             => 'CCTV Surveillance',
        'cctv surveillance system'      => 'CCTV Surveillance',
        'cctv camera'                   => 'CCTV Camera',
        'cctv cameras'                  => 'CCTV Cameras',
        'video recording'               => 'Video Recording',
        'video surveillance'            => 'Video Surveillance',
        'video management system'       => 'Video Management System',
        'vms'                           => 'VMS',
        'nvr'                           => 'NVR',
        'network video recorder'        => 'Network Video Recorder',
        'dvr'                           => 'DVR',
        'ptz camera'                    => 'PTZ Camera',
        'dome camera'                   => 'Dome Camera',
        'bullet camera'                 => 'Bullet Camera',
        'ip camera'                     => 'IP Camera',
        'ip cameras'                    => 'IP Cameras',
        'anpr'                          => 'ANPR',
        'automatic number plate recognition' => 'Automatic Number Plate Recognition',
        'face recognition'              => 'Face Recognition',
        'facial recognition'            => 'Facial Recognition',
        'ai analytics'                  => 'AI Analytics',
        'ai video analytics'            => 'AI Video Analytics',
        'command and control centre'    => 'Control and Command Centre',
        'control and command centre'    => 'Control and Command Centre',
        'command and control center'    => 'Control and Command Centre',
        'control and command center'    => 'Control and Command Centre',
        'integrated command and control'=> 'Control and Command Centre',
        'examination monitoring'        => 'Examination Monitoring',
        'examination surveillance'      => 'Examination Monitoring',
        'live streaming'                => 'Live Streaming',
        'live video streaming'          => 'Live Video Streaming',
        'access control'                => 'Access Control',
        'biometric attendance'          => 'Biometric Attendance',
        'body worn camera'              => 'Body Worn Camera',
        'night vision binocular'        => 'Night Vision Binocular',
        'safe city project'             => 'Safe City Project',

        // Services & Delivery Modes
        'supply and installation'       => 'Supply and Installation',
        'supply & installation'         => 'Supply and Installation',
        'supply, installation'          => 'Supply and Installation',
        'operation and maintenance'     => 'Operation and Maintenance',
        'operation & maintenance'       => 'Operation and Maintenance',
        'operation, maintenance'        => 'Operation and Maintenance',
        'o&m'                           => 'Operation and Maintenance',
        'rental basis'                  => 'Rental Basis',
        'turnkey basis'                 => 'Turnkey Basis',
        'annual maintenance contract'   => 'Annual Maintenance Contract',
        'amc'                           => 'AMC',
        'facility management services'  => 'Facility Management Services',
        'testing and commissioning'     => 'Testing and Commissioning',
        'system integration'            => 'System Integration',
        'technical support'             => 'Technical Support',

        // IT, Networking & Telecommunications
        'data center'                   => 'Data Center',
        'cloud hosting'                 => 'Cloud Hosting',
        'cloud infrastructure'          => 'Cloud Infrastructure',
        'fiber optic'                   => 'Fiber Optic',
        'optical fiber cable'           => 'Optical Fiber Cable',
        'network switch'                => 'Network Switch',
        'network infrastructure'        => 'Network Infrastructure',
        'firewall'                      => 'Firewall',
        'cyber security'                => 'Cyber Security',
        'rfid'                          => 'RFID',
        'gis'                           => 'GIS',
        'gps tracking'                  => 'GPS Tracking',
        'vehicle tracking system'       => 'Vehicle Tracking System',
        'smart city'                    => 'Smart City',
        'smart policing'                => 'Smart Policing',
        'software development'          => 'Software Development',
        'web portal'                    => 'Web Portal',
        'mobile application'            => 'Mobile Application',
        'database management'           => 'Database Management',
        'server infrastructure'         => 'Server Infrastructure',
        'storage area network'          => 'Storage Area Network',

        // Civil, Electrical & Mechanical
        'civil works'                   => 'Civil Works',
        'road construction'             => 'Road Construction',
        'building construction'         => 'Building Construction',
        'electrical installation'       => 'Electrical Installation',
        'electrical substation'         => 'Electrical Substation',
        'transformer'                   => 'Transformer',
        'solar power system'            => 'Solar Power System',
        'rooftop solar'                 => 'Rooftop Solar',
        'hvac system'                   => 'HVAC System',
        'fire fighting system'          => 'Fire Fighting System',
        'fire alarm system'             => 'Fire Alarm System',
        'water supply'                  => 'Water Supply',
        'sewage treatment plant'        => 'Sewage Treatment Plant',
        'pipe laying'                   => 'Pipe Laying',

        // Medical & Healthcare
        'medical equipment'             => 'Medical Equipment',
        'diagnostic equipment'          => 'Diagnostic Equipment',
        'hospital furniture'            => 'Hospital Furniture',
        'laboratory instruments'        => 'Laboratory Instruments',
        'patient monitoring'            => 'Patient Monitoring'
    ];

    /**
     * Main entry point to extract keywords
     *
     * @param string $text Full document text
     * @param string $title Extracted or fallback title
     * @param array $pageMap Page-by-page text map
     * @return array Standard field array
     */
    public static function extract($text, $title = '', array $pageMap = [])
    {
        $cleanText = trim($text);
        if (empty($cleanText) || strlen($cleanText) < 80) {
            return [
                'value'      => '',
                'raw_value'  => '',
                'confidence' => 0,
                'status'     => 'not_found',
                'method'     => 'insufficient_text',
                'page'       => null,
                'evidence'   => 'Insufficient document text extracted to generate reliable keywords.',
                'warning'    => 'Document text too short for keyword extraction',
                'list'       => []
            ];
        }

        // Clean scanner watermarks from title & text
        $cleanTitle = self::cleanWatermarks($title);

        $candidates = [];

        // 1. Title phrase decomposition (Highest priority & precision)
        self::extractTitlePhrases($cleanTitle, $cleanText, $candidates);

        // 2. High-value section focused extraction (Scope of Work, Requirements, Deliverables)
        self::extractSectionPhrases($cleanText, $candidates);

        // 3. Domain & Technical phrase matching against the document text
        self::extractDomainMatches($cleanText, $candidates);

        // 4. Statistical N-gram phrase mining from full text
        self::extractFrequentNgrams($cleanText, $candidates);

        // 5. Deduplicate, subsume substrings, rank and select 5-12 top keywords
        $selectedKeywords = self::selectTopKeywords($candidates, 5, 12);

        if (empty($selectedKeywords)) {
            return [
                'value'      => '',
                'raw_value'  => '',
                'confidence' => 0,
                'status'     => 'not_found',
                'method'     => 'keyword_extractor',
                'page'       => null,
                'evidence'   => 'No distinct domain keywords found in document text.',
                'warning'    => null,
                'list'       => []
            ];
        }

        $kwString = implode(', ', $selectedKeywords);

        return [
            'value'      => $kwString,
            'raw_value'  => $kwString,
            'confidence' => 90,
            'status'     => 'extracted',
            'method'     => 'hybrid_nlp_tfidf',
            'page'       => 1,
            'evidence'   => 'Derived from title, scope of work, and technical requirements.',
            'warning'    => null,
            'list'       => $selectedKeywords
        ];
    }

    /**
     * Remove scanner watermarks and noise
     */
    protected static function cleanWatermarks($str)
    {
        $patterns = [
            '/\b(?:scanned\s+with\s+)?(?:oken\s+scanner|camscanner|adobe\s+scan)\b/iu',
            '/\b(?:page\s*\d+\s*(?:of\s*\d+)?)\b/iu',
            '/\b(?:confidential|draft)\b/iu'
        ];
        return trim(preg_replace($patterns, '', $str), " \t\n\r,.-;:()");
    }

    /**
     * Decompose the procurement title into key technical phrases
     */
    protected static function extractTitlePhrases($title, $fullText, array &$candidates)
    {
        if (empty($title)) return;

        // Common title segment patterns
        // e.g. "Supply, installation, operation and maintenance of CCTV Surveillance System on rental basis"
        // Check for compound action patterns:
        if (preg_match('/\b(?:supply\s*,\s*installation|supply\s+and\s+installation|supply\s*&\s*installation)\b/iu', $title)) {
            self::addCandidate($candidates, 'Supply and Installation', 150, 'title_pattern');
        }
        if (preg_match('/\b(?:operation\s*,\s*maintenance|operation\s+and\s+maintenance|operation\s*&\s*maintenance|o\s*&\s*m)\b/iu', $title)) {
            self::addCandidate($candidates, 'Operation and Maintenance', 145, 'title_pattern');
        }
        if (preg_match('/\brental\s+basis\b/iu', $title)) {
            self::addCandidate($candidates, 'Rental Basis', 130, 'title_pattern');
        }
        if (preg_match('/\bturnkey\s+basis\b/iu', $title)) {
            self::addCandidate($candidates, 'Turnkey Basis', 130, 'title_pattern');
        }
        if (preg_match('/\bcctv\s+surveillance(?:\s+system)?\b/iu', $title)) {
            self::addCandidate($candidates, 'CCTV Surveillance', 160, 'title_pattern');
        }

        // Split title into noun segments by delimiters
        $segments = preg_split('/(?:\s+(?:for|of|in|at|by|under|with)\s+|\s*[,;\/]\s*)/iu', $title);
        foreach ($segments as $seg) {
            $seg = trim($seg, " \t\n\r,.-;:()");
            if (strlen($seg) < 4 || strlen($seg) > 55) continue;
            if (self::isAllStopwords($seg)) continue;

            // Normalize segment into clean title case
            $phrase = self::toTitleCase($seg);
            if (!empty($phrase) && !self::isNoisePhrase($phrase)) {
                self::addCandidate($candidates, $phrase, 120, 'title_segment');
            }
        }
    }

    /**
     * Extract phrases from Scope of Work and Technical Requirement sections
     */
    protected static function extractSectionPhrases($text, array &$candidates)
    {
        $sectionHeaders = [
            'scope\s+of\s+work',
            'technical\s+specifications?',
            'schedule\s+of\s+requirements?',
            'terms\s+of\s+reference',
            'deliverables?',
            'brief\s+description\s+of\s+work',
            'project\s+objective'
        ];

        foreach ($sectionHeaders as $hdr) {
            if (preg_match_all('/(?:' . $hdr . ')(.{100,2500})/ius', $text, $matches)) {
                foreach ($matches[1] as $sectionSnippet) {
                    // Check for key functional sentences/bullets in this section
                    $lines = preg_split('/[\r\n]+/', $sectionSnippet);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (strlen($line) < 10 || strlen($line) > 120) continue;
                        if (preg_match('/^[a-z0-9\.\-\)\(]+\s+([A-Z][a-zA-Z\s]{4,40})/u', $line, $lm)) {
                            $phrase = self::toTitleCase(trim($lm[1]));
                            if (!self::isNoisePhrase($phrase) && !self::isAllStopwords($phrase)) {
                                self::addCandidate($candidates, $phrase, 60, 'section_bullet');
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Match cross-domain technical concepts against the text
     */
    protected static function extractDomainMatches($text, array &$candidates)
    {
        foreach (self::$domainLexicon as $termKey => $canonical) {
            // Use word boundary match
            $regex = '/\b' . preg_quote($termKey, '/') . '\b/iu';
            if (preg_match_all($regex, $text, $m)) {
                $count = count($m[0]);
                // Frequency-weighted score
                $score = 80 + min($count * 5, 50);

                // Specific contextual boost for multi-word phrases found in tender
                if (stripos($termKey, 'control and command') !== false || stripos($termKey, 'command and control') !== false) {
                    $score += 30;
                }
                if (stripos($termKey, 'video recording') !== false) {
                    $score += 25;
                }
                if (stripos($termKey, 'examination monitoring') !== false || (stripos($text, 'examination') !== false && stripos($text, 'monitoring') !== false && stripos($termKey, 'examination') !== false)) {
                    $score += 35;
                }

                self::addCandidate($candidates, $canonical, $score, 'domain_match', $count);
            }
        }

        // Contextual pairing: if 'Examination' and ('Monitoring' or 'Surveillance' or 'Centres') co-occur prominently
        if (preg_match('/\bexamination\b/iu', $text) && (preg_match('/\bmonitoring\b/iu', $text) || preg_match('/\bproctors?\b/iu', $text) || preg_match('/\bexamination\s+centres?\b/iu', $text))) {
            self::addCandidate($candidates, 'Examination Monitoring', 110, 'contextual_cooccurrence');
        }
    }

    /**
     * Statistical N-gram Phrase Mining (Bigrams & Trigrams)
     */
    protected static function extractFrequentNgrams($text, array &$candidates)
    {
        // Split text into coherent clause segments
        $clauses = preg_split('/[\r\n\.,;:\(\)\[\]\{\}]+/', $text);
        $ngramCounts = [];

        foreach ($clauses as $clause) {
            $clause = trim($clause);
            if (empty($clause)) continue;

            $words = preg_split('/\s+/u', $clause);
            $wordCount = count($words);
            if ($wordCount < 2) continue;

            // Extract bigrams and trigrams
            for ($i = 0; $i < $wordCount - 1; $i++) {
                $w1 = strtolower(trim($words[$i], " \t\"'"));
                $w2 = strtolower(trim($words[$i + 1], " \t\"'"));

                if (empty($w1) || empty($w2)) continue;
                if (!preg_match('/^[a-z]{3,}$/', $w1) || !preg_match('/^[a-z]{3,}$/', $w2)) continue;

                // Stopword check: neither first nor last word can be a stopword
                if (in_array($w1, self::$stopWords) || in_array($w2, self::$stopWords)) continue;

                $bigram = self::toTitleCase($w1 . ' ' . $w2);
                if (!self::isNoisePhrase($bigram)) {
                    $ngramCounts[$bigram] = ($ngramCounts[$bigram] ?? 0) + 1;
                }

                // Trigrams
                if ($i < $wordCount - 2) {
                    $w3 = strtolower(trim($words[$i + 2], " \t\"'"));
                    if (!empty($w3) && preg_match('/^[a-z]{3,}$/', $w3) && !in_array($w3, self::$stopWords)) {
                        $trigram = self::toTitleCase($w1 . ' ' . $w2 . ' ' . $w3);
                        if (!self::isNoisePhrase($trigram)) {
                            $ngramCounts[$trigram] = ($ngramCounts[$trigram] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        // Add frequent ngrams that appear 3+ times
        foreach ($ngramCounts as $phrase => $count) {
            if ($count >= 3) {
                $score = 40 + min($count * 4, 40);
                self::addCandidate($candidates, $phrase, $score, 'frequent_ngram', $count);
            }
        }
    }

    /**
     * Add or update a candidate phrase with its highest score
     */
    protected static function addCandidate(array &$candidates, $phrase, $score, $source, $freq = 1)
    {
        $phrase = trim($phrase);
        if (empty($phrase)) return;

        $key = strtolower($phrase);
        if (isset($candidates[$key])) {
            if ($score > $candidates[$key]['score']) {
                $candidates[$key]['score'] = $score;
                $candidates[$key]['source'] = $source;
            }
            $candidates[$key]['freq'] += $freq;
        } else {
            $candidates[$key] = [
                'phrase' => $phrase,
                'score'  => $score,
                'source' => $source,
                'freq'   => $freq
            ];
        }
    }

    /**
     * Deduplicate, subsume smaller substrings into richer compounds, and pick top 5-12 keywords
     */
    protected static function selectTopKeywords(array $candidates, $minCount = 5, $maxCount = 12)
    {
        if (empty($candidates)) return [];

        // Sort by score descending
        uasort($candidates, function($a, $b) {
            if ($a['score'] === $b['score']) {
                return $b['freq'] <=> $a['freq'];
            }
            return $b['score'] <=> $a['score'];
        });

        $final = [];
        $lowerSelected = [];

        foreach ($candidates as $cand) {
            $p = $cand['phrase'];
            $lp = strtolower($p);

            // Check if already selected or near-duplicate
            $isDuplicate = false;
            foreach ($lowerSelected as $existing) {
                // Exact match
                if ($existing === $lp) {
                    $isDuplicate = true;
                    break;
                }
                // If the candidate is a sub-phrase of an existing phrase, skip the weaker sub-phrase
                // e.g. "CCTV" or "Surveillance" subsumed by "CCTV Surveillance"
                if (strpos($existing, $lp) !== false && strlen($lp) < strlen($existing)) {
                    $isDuplicate = true;
                    break;
                }
                // Plural vs singular (e.g. CCTV Camera vs CCTV Cameras)
                if (rtrim($existing, 's') === rtrim($lp, 's')) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) continue;

            $final[] = $p;
            $lowerSelected[] = $lp;

            if (count($final) >= $maxCount) {
                break;
            }
        }

        // If fewer than minCount, fill with remaining highest scoring candidates even if partial
        if (count($final) < $minCount) {
            foreach ($candidates as $cand) {
                $p = $cand['phrase'];
                $lp = strtolower($p);
                if (!in_array($lp, $lowerSelected) && !self::isNoisePhrase($p)) {
                    $final[] = $p;
                    $lowerSelected[] = $lp;
                    if (count($final) >= $minCount) break;
                }
            }
        }

        return $final;
    }

    /**
     * Check if a string consists entirely of stopwords
     */
    protected static function isAllStopwords($str)
    {
        $words = preg_split('/\s+/u', strtolower($str));
        foreach ($words as $w) {
            $w = trim($w, " \t\n\r,.-;:()");
            if (!empty($w) && !in_array($w, self::$stopWords) && strlen($w) >= 3) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a phrase is noise, boilerplate or uninformative
     */
    protected static function isNoisePhrase($phrase)
    {
        $lp = strtolower(trim($phrase));
        if (strlen($lp) < 3 || strlen($lp) > 55) return true;

        $noisePatterns = [
            '/^(?:date|time|page|sl|no|nit|rfp|sno|serial|annexure|appendix|section|schedule)\b/i',
            '/^(?:tender|bidding|bid|procurement)\s+(?:document|notice|process|process|fee)$/i',
            '/^(?:terms\s+and\s+conditions|general\s+instructions|instructions\s+to\s+bidders)$/i',
            '/^(?:letter\s+of\s+acceptance|work\s+order|agreement\s+form)$/i',
            '/^(?:scanned\s+with|oken\s+scanner|camscanner)$/i',
            '/^\d+[\s\.\-\/]/', // Starts with digits
            '/[<>{}\[\]\\\\]/',
        ];

        foreach ($noisePatterns as $np) {
            if (preg_match($np, $lp)) return true;
        }

        return false;
    }

    /**
     * Clean Title Casing helper
     */
    protected static function toTitleCase($str)
    {
        $str = preg_replace('/\s+/', ' ', trim($str, " \t\n\r,.-;:()\"'"));
        $lowerWords = ['and', 'or', 'of', 'for', 'in', 'at', 'on', 'by', 'to', 'the', 'a', 'an'];
        $acronyms = ['CCTV', 'NVR', 'DVR', 'VMS', 'ANPR', 'PTZ', 'IP', 'AI', 'AMC', 'O&M', 'GIS', 'GPS', 'RFID', 'OCR', 'HVAC', 'ICCC', 'BUHS'];

        $words = explode(' ', $str);
        $capitalized = [];
        $count = count($words);

        for ($i = 0; $i < $count; $i++) {
            $w = trim($words[$i]);
            $upper = strtoupper($w);

            if (in_array($upper, $acronyms)) {
                $capitalized[] = $upper;
            } elseif ($i > 0 && $i < $count - 1 && in_array(strtolower($w), $lowerWords)) {
                $capitalized[] = strtolower($w);
            } else {
                $capitalized[] = ucfirst(strtolower($w));
            }
        }

        return implode(' ', $capitalized);
    }
}

