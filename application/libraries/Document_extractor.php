<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Document_extractor Library
 * 
 * High-performance, multi-format Document Intelligence and Text Extraction Library for CodeIgniter.
 * Supports: PDF (Digital, Scanned, Vector Outlines), DOCX, DOC, RTF, and TXT.
 * Features:
 *   - 4-Tier PDF extraction (Smalot -> Poppler -> Windows Native OCR -> Pure PHP stream parser)
 *   - Word DOCX full XML extraction (tables, headers, footnotes)
 *   - Word DOC (97-2003) OLE2 binary parser
 *   - RTF parser
 *   - Text normalization & intra-word kerning repair
 *   - Structural module analyzer (Notices, Schedules, TOC, Sections)
 *   - AI Executive Document Summarizer & Intelligence Engine
 * 
 * Reusable across any controller or module:
 *   $this->load->library('document_extractor');
 *   $result = $this->document_extractor->extract($tmpFilePath, $originalFileName);
 */
class Document_extractor
{
    protected $CI;

    public function __construct()
    {
        if (function_exists('get_instance')) {
            $ci = get_instance();
            $this->CI =& $ci;
        }
    }

    /**
     * Primary extraction pipeline
     *
     * @param string $filePath Absolute path to the document file
     * @param string $origName Original uploaded filename (optional)
     * @param string $ext Explicit extension (optional)
     * @return array Standardized result payload
     */
    public function extract($filePath, $origName = '', $ext = '')
    {
        if (empty($filePath) || !file_exists($filePath)) {
            return [
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'The specified document file does not exist or cannot be accessed.'
            ];
        }

        if (empty($origName)) {
            $origName = basename($filePath);
        }

        if (empty($ext)) {
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (empty($ext)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            }
        } else {
            $ext = strtolower($ext);
        }

        $allowedExts = ['pdf', 'docx', 'doc', 'txt', 'rtf'];
        if (!in_array($ext, $allowedExts)) {
            return [
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'Unsupported file format (.' . htmlspecialchars($ext) . '). Supported: PDF, DOCX, DOC, RTF, TXT.'
            ];
        }

        $fileSize = @filesize($filePath);
        $formattedSize = $fileSize < 1024 
            ? $fileSize . ' B' 
            : ($fileSize < 1048576 ? round($fileSize / 1024, 1) . ' KB' : round($fileSize / 1048576, 2) . ' MB');

        $rawResult = $this->extractRawText($filePath, $ext);
        $extractedText = $rawResult['text'];
        $methodUsed    = $rawResult['method'];

        // Clean text, remove form feeds, and handle character encoding
        $extractedText = str_replace(["\x0c", "\f"], "\n", $extractedText);
        if (!mb_check_encoding($extractedText, 'UTF-8')) {
            $extractedText = mb_convert_encoding($extractedText, 'UTF-8', 'auto');
        }
        $extractedText = str_replace(["\r\n", "\r"], "\n", $extractedText);
        $extractedText = preg_replace("/\n{3,}/", "\n\n", $extractedText);
        $trimmedText = trim($extractedText);

        if (empty($trimmedText)) {
            return [
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'No readable text could be extracted from this file. The document may be empty, an unsupported scan, or password-protected.'
            ];
        }

        // Smart Entities: Emails
        $emails = [];
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $trimmedText, $emMatches)) {
            $emails = array_values(array_unique(array_map('strtolower', $emMatches[0])));
        }

        // Smart Entities: Phone numbers
        $phones = [];
        if (preg_match_all('/(?:\+?91[\s.-]?)?[6-9]\d{4}[\s.-]?\d{5}|(?:\+?\d{1,3}[\s.-]?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}/', $trimmedText, $phMatches)) {
            foreach ($phMatches[0] as $ph) {
                $phones[] = trim($ph);
            }
            $phones = array_values(array_unique($phones));
        }

        $charCount = mb_strlen($trimmedText, 'UTF-8');
        $wordCount = str_word_count($trimmedText);
        $lineCount = substr_count($trimmedText, "\n") + 1;
        $readingTimeMin = max(1, (int)ceil($wordCount / 200));

        $formattedText = $this->cleanText($trimmedText);
        $modules       = $this->structureIntoModules($formattedText);
        $aiSummary     = $this->generateAiSummary($formattedText, $origName, $ext);

        $extractedTitle = !empty($aiSummary['title']) ? $aiSummary['title'] : (!empty($aiSummary['document_title']) ? $aiSummary['document_title'] : $origName);
        $classification = isset($aiSummary['classification']) ? $aiSummary['classification'] : 'unknown';
        $classificationStatus = isset($aiSummary['classification_status']) ? $aiSummary['classification_status'] : 'unclassified';

        return [
            'status'                => 'success',
            'success'               => true,
            'title'                 => $extractedTitle,
            'document_title'        => $extractedTitle,
            'classification'        => $classification,
            'classification_status' => $classificationStatus,
            'classification_reason' => isset($aiSummary['classification_reason']) ? $aiSummary['classification_reason'] : '',
            'txt_file'              => null,
            'filename'              => htmlspecialchars($origName),
            'filetype'              => strtoupper($ext),
            'filesize'              => $formattedSize,
            'method'                => $methodUsed,
            'char_count'            => number_format($charCount),
            'word_count'            => number_format($wordCount),
            'line_count'            => number_format($lineCount),
            'reading_time'          => $readingTimeMin . ' min read',
            'emails'                => $emails,
            'phones'                => $phones,
            'extracted_text'        => $trimmedText,
            'formatted_text'        => $formattedText,
            'modules'               => $modules,
            'ai_summary'            => $aiSummary
        ];
    }

    /**
     * Extracts raw text string from the target document based on file type
     *
     * @param string $filePath
     * @param string $ext
     * @return array ['text' => string, 'method' => string]
     */
    public function extractRawText($filePath, $ext = '')
    {
        if (empty($ext)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        } else {
            $ext = strtolower($ext);
        }

        $extractedText = '';
        $methodUsed = 'Processed';

        if ($ext === 'pdf') {
            // â”€â”€ Strategy 1: Smalot PdfParser (Composer) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if (file_exists(FCPATH . 'vendor/autoload.php')) {
                require_once FCPATH . 'vendor/autoload.php';
            }
            if (class_exists('\Smalot\PdfParser\Parser')) {
                try {
                    $config = new \Smalot\PdfParser\Config();
                    $config->setRetainImageContent(false);
                    $parser = new \Smalot\PdfParser\Parser([], $config);
                    $pdf = $parser->parseFile($filePath);
                    $pages = $pdf->getPages();
                    $pageTexts = [];
                    foreach ($pages as $page) {
                        $pt = $page->getText();
                        if ($this->isValidDocumentText($pt)) $pageTexts[] = $pt;
                    }
                    if (!empty($pageTexts)) {
                        $extractedText = implode("\n\n", $pageTexts);
                        $methodUsed = 'Processed';
                    }
                } catch (\Throwable $e) {}
            }

            $isWindows = (DIRECTORY_SEPARATOR === '\\' || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

            // â”€â”€ Strategy 2: Poppler pdftotext CLI (Official PDF text engine) â”€â”€
            if (!$this->isValidDocumentText($extractedText)) {
                $popplerPaths = [];
                if ($isWindows) {
                    $popplerPaths = [
                        __DIR__ . '/../third_party/poppler-23.11.0-0/Library/bin/pdftotext.exe',
                        FCPATH . 'application/modules/document_parser/third_party/poppler-23.11.0-0/Library/bin/pdftotext.exe',
                        FCPATH . 'application/third_party/poppler-23.11.0-0/Library/bin/pdftotext.exe',
                        FCPATH . 'application/third_party/poppler/bin/pdftotext.exe',
                        'C:/Program Files/poppler/bin/pdftotext.exe',
                        'C:/poppler/bin/pdftotext.exe',
                    ];
                } else {
                    // Linux / Unix / macOS paths
                    $popplerPaths = [
                        '/usr/bin/pdftotext',
                        '/usr/local/bin/pdftotext',
                        '/opt/local/bin/pdftotext',
                        '/opt/homebrew/bin/pdftotext',
                    ];
                    $whichPdf = @trim(@shell_exec('which pdftotext 2>/dev/null'));
                    if (!empty($whichPdf) && file_exists($whichPdf)) {
                        array_unshift($popplerPaths, $whichPdf);
                    }
                }

                foreach ($popplerPaths as $popplerBin) {
                    if (file_exists($popplerBin) || (!$isWindows && is_executable($popplerBin))) {
                        $outFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdfout_' . uniqid() . '.txt';
                        $cmd = escapeshellarg($popplerBin) . ' -enc UTF-8 -layout ' . escapeshellarg($filePath) . ' ' . escapeshellarg($outFile);
                        @shell_exec($cmd);
                        if (file_exists($outFile)) {
                            $output = @file_get_contents($outFile);
                            @unlink($outFile);
                            if ($this->isValidDocumentText($output)) {
                                $extractedText = $output;
                                $methodUsed = 'Processed';
                                break;
                            }
                        }
                        // stdout attempt
                        $cmd2 = escapeshellarg($popplerBin) . ' -enc UTF-8 -layout ' . escapeshellarg($filePath) . ' -';
                        $out2 = @shell_exec($cmd2);
                        if ($this->isValidDocumentText($out2)) {
                            $extractedText = $out2;
                            $methodUsed = 'Processed';
                            break;
                        }
                    }
                }
            
            }

            // â”€â”€ Strategy 3: Native OCR (Windows PowerShell OCR / Linux Tesseract) â”€â”€
            if (!$this->isValidDocumentText($extractedText)) {
                if ($isWindows) {
                    $ocrCandidates = [
                        __DIR__ . '/../third_party/win_ocr_runner.ps1',
                        FCPATH . 'application/modules/document_parser/third_party/win_ocr_runner.ps1',
                        FCPATH . 'application/third_party/win_ocr_runner.ps1'
                    ];
                    $ocrScript = '';
                    foreach ($ocrCandidates as $cand) {
                        if (file_exists($cand)) {
                            $ocrScript = $cand;
                            break;
                        }
                    }
                    if (!empty($ocrScript) && file_exists($ocrScript)) {
                        $cmd = 'powershell -ExecutionPolicy Bypass -File ' . escapeshellarg($ocrScript) . ' -FilePath ' . escapeshellarg($filePath) . ' -FileType ' . escapeshellarg($ext);
                        $ocrOut = @shell_exec($cmd);
                        if ($this->isValidDocumentText($ocrOut)) {
                            $extractedText = $ocrOut;
                            $methodUsed = 'Processed';
                        }
                    }
                } else {
                    // Linux OCR: Tesseract CLI if installed
                    $whichTess = @trim(@shell_exec('which tesseract 2>/dev/null'));
                    if (!empty($whichTess)) {
                        $tessOut = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tess_' . uniqid();
                        $cmd = escapeshellarg($whichTess) . ' ' . escapeshellarg($filePath) . ' ' . escapeshellarg($tessOut) . ' -l eng --oem 1 txt 2>/dev/null';
                        @shell_exec($cmd);
                        if (file_exists($tessOut . '.txt')) {
                            $ocrOut = @file_get_contents($tessOut . '.txt');
                            @unlink($tessOut . '.txt');
                            if ($this->isValidDocumentText($ocrOut)) {
                                $extractedText = $ocrOut;
                                $methodUsed = 'Processed';
                            }
                        }
                    }
                }
            }

            // â”€â”€ Strategy 4: Pure-PHP PDF stream parser â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if (!$this->isValidDocumentText($extractedText)) {
                $streamText = $this->parsePdfPurePhp($filePath);
                if ($this->isValidDocumentText($streamText)) {
                    $extractedText = $streamText;
                    $methodUsed = 'Processed';
                }
            }

        } elseif ($ext === 'docx') {
            $extractedText = $this->extractDocx($filePath);
            if (!empty(trim($extractedText))) {
                $methodUsed = 'Processed';
            }

        } elseif ($ext === 'doc') {
            $extractedText = $this->extractDoc($filePath);
            if (!empty(trim($extractedText))) {
                $methodUsed = 'Processed';
            }

        } elseif ($ext === 'rtf') {
            $extractedText = $this->extractRtf($filePath);
            if (!empty(trim($extractedText))) {
                $methodUsed = 'Processed';
            }

        } elseif ($ext === 'txt') {
            $extractedText = @file_get_contents($filePath);
            $methodUsed = 'Processed';
        }

        return [
            'text'   => $extractedText,
            'method' => $methodUsed
        ];
    }

    /**
     * Validates whether an extracted text block contains real readable words
     * and is not binary stream noise, form-feed artifacts, or random gibberish.
     *
     * @param string $text
     * @return bool
     */
    public function isValidDocumentText($text)
    {
        if (empty($text)) return false;
        $clean = trim($text);
        if (strlen($clean) < 25) return false;

        // Check ratio of alphanumeric characters vs symbols
        preg_match_all('/[a-zA-Z0-9\s.,\-\/]/', $clean, $matches);
        $alphaCount = count($matches[0]);
        $totalLen = strlen($clean);
        if ($totalLen > 0 && ($alphaCount / $totalLen) < 0.65) {
            return false;
        }

        // Must contain at least 2 common English dictionary words
        $commonWords = [
            'the','of','and','to','in','for','is','on','that','by','this','with','or','be',
            'are','from','at','as','date','no','tender','rfp','school','cctv','name','page',
            'project','management','system','office','supply','service','contract','order'
        ];
        $matchCount = 0;
        foreach ($commonWords as $w) {
            if (preg_match('/\b' . $w . '\b/i', $clean)) {
                $matchCount++;
                if ($matchCount >= 2) return true;
            }
        }
        return false;
    }

    /**
     * Cleans OCR/PDF artifacts, kerning spaces, repeated watermarks, and broken dates/codes
     *
     * @param string $raw
     * @return string
     */
    public function cleanText($raw)
    {
        // Convert unicode dashes to standard hyphen
        $text = str_replace(["\xe2\x80\x93", "\xe2\x80\x94", 'â€“', 'â€”', '&ndash;', '&mdash;', '–', '—'], '-', $raw);

        // Remove isolated page numbers
        $text = preg_replace('/^[ \t]*\d+[ \t]*$/m', '', $text);
        
        // Fix 2-column OCR schedule tables (e.g. in PM Shree School and similar tenders)
        $tableKeyPattern = '/(Availability\s+of\s+RFP\s+Document)\s+(Last\s+date\s+for\s+receiving\s+queries)\s+(Pre-Bid\s+Conference)\s+(Issue\s+of\s+Corrigendum(?:\s*\([^\)]*\))?)\s+(Last\s+Date\s+for\s+Submission\s+of\s+RFP)\s+(Place\s+of\s+Submission\s+of\s+RFP\s+Document)\s*[:.]*\s*(Dt\.\s*\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+to\s+\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+at\s+[\d:]+\s*(?:AM|PM))\s*(Dt\.\s*\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+by\s+[\d:]+\s*(?:AM|PM))\s*(Dt\.\s*\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+by\s+[\d:]+\s*(?:AM|PM))\s*[.]*\s*(Dt\.\s*\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+by\s+[\d:]+\s*(?:AM|PM))\s*[.]*\s*(Dt\.\s*\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\s+at\s+[\d:]+\s*(?:AM|PM))\s*[:.]*\s*(https?:\/\/[^\s]+(?:\s*\([^\)]*\))?)/iu';
        if (preg_match($tableKeyPattern, $text, $tkm)) {
            $repTable = "\n1. " . trim($tkm[1]) . ": " . trim($tkm[7]) .
                        "\n2. " . trim($tkm[2]) . ": " . trim($tkm[8]) .
                        "\n3. " . trim($tkm[3]) . ": " . trim($tkm[9]) .
                        "\n4. " . trim($tkm[4]) . ": " . trim($tkm[10]) .
                        "\n5. " . trim($tkm[5]) . ": " . trim($tkm[11]) .
                        "\n6. " . trim($tkm[6]) . ": " . trim($tkm[12]) . "\n";
            $text = str_replace($tkm[0], $repTable, $text);
        }

        // Intra-word kerning & PDF spacing repair
        $wordFixes = [
            '/\bNOTI\s+CE\b/iu' => 'NOTICE',
            '/\bINV\s+ITI\s+NG\b/iu' => 'INVITING',
            '/\be\s*-\s*TEND\s*ER\b/iu' => 'e-TENDER',
            '/\bSEC\s+TIO\s+N\b/iu' => 'SECTION',
            '/\bCONT\s+ENTS\b/iu' => 'CONTENTS',
            '/\bTEND\s+ER\b/iu' => 'TENDER',
            '/\bDOC\s+UMENT\b/iu' => 'DOCUMENT',
            '/\bTe\s*nd\s*er\b/iu' => 'Tender',
            '/\bdocu\s*m\s*ent\b/iu' => 'document',
            '/\bco\s*mp\s*ris\s*es\b/iu' => 'comprises',
            '/\bfollo\s*w\s*ing\b/iu' => 'following',
            '/\bBACKGROUD\b/iu' => 'BACKGROUND',
            '/\bcompl\s+etion\b/iu' => 'completion',
            '/\bstip\s+ulated\b/iu' => 'stipulated',
            '/\bDow\s+nloading\b/iu' => 'Downloading',
            '/\bPre\s*-\s*Bid\b/iu' => 'Pre-Bid',
            '/\bon\s*-\s*site\b/iu' => 'on-site',
            '/\breal\s*-\s*time\b/iu' => 'real-time',
            '/\binfiltration\s*-\s*prone\b/iu' => 'infiltration-prone',
            '/\bcross\s*-\s*border\b/iu' => 'cross-border',
            '/\bstate\s*-\s*of\s*-\s*the\s*-\s*art\b/iu' => 'state-of-the-art',
            '/\bhigh\s*-\s*resolution\b/iu' => 'high-resolution',
            '/\bhigh\s*-\s*risk\b/iu' => 'high-risk',
            '/\bbroadcast\s*-\s*wide\b/iu' => 'broadcast-wide',
            '/\bIP\s*-\s*Based\b/iu' => 'IP-Based',
            '/\be\s*-\s*tender\b/iu' => 'e-tender',
            '/\be\s*-\s*fil+ing\b/iu' => 'e-filing',
            '/\be\s*-\s*Governance\b/iu' => 'e-Governance',
            '/\be\s*-\s*Recruitment\b/iu' => 'e-Recruitment',
            '/\bState\s*-\s*of\s*-\s*the\s*-\s*art\b/iu' => 'State-of-the-art',
            '/\bpost\s*-\s*incident\b/iu' => 'post-incident',
            '/\bInter\s*-\s*District\b/iu' => 'Inter-District',
            '/\blaw\s*-\s*and\s*-\s*order\b/iu' => 'law-and-order',
            '/\bnight\s*-\s*and\s*-\s*day\b/iu' => 'night-and-day',
            '/\bauto\s+matically\b/iu' => 'automatically',
            '/\binstalla\s+tion\b/iu' => 'installation',
            '/\bcommis\s+sioning\b/iu' => 'commissioning',
            '/\btechnicall\b/iu' => 'technical',
            '/\bun\s+lawful\b/iu' => 'unlawful',
            '/\badm\s+inistrative\b/iu' => 'administrative',
            '/\bautho\s+rity\b/iu' => 'authority',
            '/\bexperi\s+ence\b/iu' => 'experience',
            '/\bconcur\s+rently\b/iu' => 'concurrently',
            '/\bsubmis\s+sion\b/iu' => 'submission',
            '/\bqualifi\s+ed\b/iu' => 'qualified',
            '/\bdeci\s+sion\b/iu' => 'decision',
            '/\bcorri\s+genda\b/iu' => 'corrigenda',
            '/\bpar\s+tner\b/iu' => 'partner',
            '/\beligi\s+bility\b/iu' => 'eligibility',
            '/\bmanu\s+facturing\b/iu' => 'manufacturing',
            '/\bmanu\s+facturers\b/iu' => 'manufacturers',
            '/\brequire\s+ment\b/iu' => 'requirement',
            '/\bblack\s+listed\b/iu' => 'blacklisted',
            '/\bauthori\s+zation\b/iu' => 'authorization',
            '/\bsignifi\s+cantly\b/iu' => 'significantly',
            '/\bopera\s+tionalize\b/iu' => 'operationalize',
            '/\bjuris\s+diction\b/iu' => 'jurisdiction',
            '/\btrans\s+ferred\b/iu' => 'transferred',
            '/\binter\s+national\b/iu' => 'international',
            '/\bDist\s*rict\b/iu' => 'District',
            '/\bCommi\s*ssionerate\b/iu' => 'Commissionerate',
            '/\bSurveil\s*lance\b/iu' => 'Surveillance',
            '/\bInspec\s*tor\b/iu' => 'Inspector',
            '/\bAutono\s*mous\b/iu' => 'Autonomous',
            '/\bexecu\s*tion\b/iu' => 'execution',
            '/\bade\s*quate\b/iu' => 'adequate',
            '/\bDepar\s*tment\b/iu' => 'Department',
            '/\bEngin\s*eer\b/iu' => 'Engineer',
            '/\bBinocu\s*lar\b/iu' => 'Binocular',
            '/\bimple\s*mentation\b/iu' => 'implementation',
            '/\bcreden\s*tials\b/iu' => 'credentials',
            '/\bGovern\s*ment\b/iu' => 'Government',
            '/\binteg\s*rators\b/iu' => 'integrators',
            '/\bappli\s*cation\b/iu' => 'application',
            '/\bspecifi\s*cation\b/iu' => 'specification',
            '/\bcandi\s*date\b/iu' => 'candidate',
            '/\brequire\s*ments\b/iu' => 'requirements',
            '/\bevalu\s*ation\b/iu' => 'evaluation',
            '/\bperfor\s*mance\b/iu' => 'performance',
            '/\bService\s+Engineer\s+fo\b/iu' => 'Service Engineer for',
            '/\bfo(?=\s+(?:the|a|an|each|all|any|our|their|his|her|this|that|further|successful))\b/iu' => 'for',
        ];
        $text = preg_replace(array_keys($wordFixes), array_values($wordFixes), $text);

        // Fix broken dates (01 -09 -2026 -> 01-09-2026)
        $text = preg_replace('/(\d{1,2})\s*-\s*(\d{1,2})\s*-\s*(\d{2,4})/', '$1-$2-$3', $text);
        $text = preg_replace('/(\d{1,2})\s*-\s*([A-Za-z]{3})\s*-\s*(\d{2,4})/', '$1-$2-$3', $text);

        // Fix split code formats (WTL/WBPNB/CCTV /26 -27/ 046 -> WTL/WBPNB/CCTV/26-27/046)
        $text = preg_replace('/([A-Z0-9]+)\s*\/\s*([A-Z0-9]+)\s*\/\s*([A-Z0-9]+)\s*\/\s*(\d+)\s*-\s*(\d+)\s*\/\s*(\d+)/', '$1/$2/$3/$4-$5/$6', $text);

        // Fix currency formatting
        $text = preg_replace('/Rs\s*\.\s*(\d+)/i', 'Rs. $1', $text);

        // Clean bullets and indentation
        $text = preg_replace('/[ \t]*â€¢[ \t]*/u', "\nâ€¢ ", $text);
        $text = preg_replace('/[ \t]*o[ \t]+(?=[A-Z])/', "\nâ€¢ ", $text);

        // Clean whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Structures clean text into logical, understandable modules
     *
     * @param string $cleanText
     * @return array
     */
    public function structureIntoModules($cleanText)
    {
        $modules = [];

        // Module 1: Tender Notice / Executive Summary
        if (preg_match('/NOTICE\s+INVITING\s+e-TENDER[\s\S]*?(?=(?:1\.\s*Tender\s+No|SECTION\s*-\s*[A-Z0-9]+|CONTENTS\s+OF|$))/iu', $cleanText, $nm)) {
            $content = trim($nm[0]);
            $summary = '';
            if (preg_match('/(?:e-Tender\s+are\s+invited|Bids\s+are\s+invited|The\s+objective)[^\.\n]+\./iu', $content, $sm)) {
                $summary = trim($sm[0]);
            }
            $modules[] = [
                'id'          => 'mod_notice',
                'title'       => 'Tender Notice & Project Overview',
                'badge'       => 'Notice Overview',
                'badge_color' => 'badge-primary',
                'icon'        => 'fa-bullhorn',
                'type'        => 'notice',
                'summary'     => $summary,
                'content'     => $content
            ];
        }

        // Module 2: Important Schedule & Critical Dates (e.g. Points 1 to 13)
        $tableItems = [];
        if (preg_match_all('/(?:^|\n)(\d{1,2})\.\s*([A-Za-z0-9\s&.,\/â€“-]+?)(?::|\t|\s{2,})([^\n]+(?:\n(?!\d{1,2}\.)[^\n]+)*)/u', $cleanText, $rows, PREG_SET_ORDER)) {
            foreach ($rows as $r) {
                $key = trim($r[2]);
                $val = trim(preg_replace('/\s+/', ' ', $r[3]));
                if (strlen($key) > 2 && strlen($key) < 60 && strlen($val) > 0) {
                    $type = 'info';
                    if (stripos($key, 'fee') !== false || stripos($key, 'money') !== false || stripos($key, 'emd') !== false) {
                        $type = 'finance';
                    } elseif (stripos($key, 'date') !== false || stripos($key, 'meeting') !== false || stripos($key, 'opening') !== false || stripos($key, 'submission') !== false) {
                        $type = 'schedule';
                    } elseif (stripos($key, 'address') !== false || stripos($key, 'contact') !== false) {
                        $type = 'contact';
                    }

                    $tableItems[] = [
                        'num'   => $r[1],
                        'key'   => $key,
                        'value' => $val,
                        'type'  => $type
                    ];
                }
            }
        }
        if (count($tableItems) >= 3) {
            $modules[] = [
                'id'          => 'mod_schedule',
                'title'       => 'Key Project Schedule, Fees & Critical Milestones',
                'badge'       => count($tableItems) . ' Key Milestones',
                'badge_color' => 'badge-warning text-dark',
                'icon'        => 'fa-calendar-check',
                'type'        => 'table',
                'items'       => $tableItems
            ];
        }

        // Module 3: Table of Contents / Index
        if (preg_match('/CONTENTS\s+OF\s+THE\s+TENDER\s+DOCUMENT[\s\S]*?(?=(?:SECTION\s*-\s*A\b\s*[\n\r]|PROJECT OBJECTIVE|$))/iu', $cleanText, $cm)) {
            $tocLines = [];
            if (preg_match_all('/SECTION\s*-\s*([A-Z0-9]+)\s+([^\n]+)/iu', $cm[0], $tm, PREG_SET_ORDER)) {
                foreach ($tm as $t) {
                    $tocLines[] = [
                        'section' => 'Section ' . trim($t[1]),
                        'title'   => trim($t[2])
                    ];
                }
            }
            $modules[] = [
                'id'          => 'mod_toc',
                'title'       => 'Document Sections Index (Table of Contents)',
                'badge'       => count($tocLines) . ' Sections',
                'badge_color' => 'badge-secondary',
                'icon'        => 'fa-list-ol',
                'type'        => 'toc',
                'items'       => $tocLines
            ];
        }

        // Module 4+: Individual Sections (SECTION - A, SECTION - B, etc.)
        if (preg_match_all('/(?:^|\n)(SECTION\s*-\s*([A-Z0-9]+)[^\n]*)([\s\S]*?)(?=(?:\nSECTION\s*-\s*[A-Z0-9]+|$))/iu', $cleanText, $secMatches, PREG_SET_ORDER)) {
            foreach ($secMatches as $idx => $sec) {
                $fullHeader = trim($sec[1]);
                $secLetter  = strtoupper(trim($sec[2]));
                $secBody    = trim($sec[3]);

                if (stripos($secBody, 'SECTION - B') !== false && strlen($secBody) < 200) {
                    continue; // Skip TOC mention
                }

                $title = $fullHeader;
                $lines = explode("\n", trim($secBody));
                if (!empty($lines[0])) {
                    $candidate = trim($lines[0]);
                    if (strlen($candidate) > 2 && strlen($candidate) < 70 && !preg_match('/^\d+\./', $candidate)) {
                        $title .= ' â€” ' . $candidate;
                    }
                }

                // Sub-blocks
                $blocks = [];
                if (preg_match_all('/(?:^|\n)(\d+(?:\.\d+)?)\.?\s+([^\n]+)([\s\S]*?)(?=(?:\n\d+(?:\.\d+)?\.?\s+[^\n]+|$))/iu', $secBody, $bm, PREG_SET_ORDER)) {
                    foreach ($bm as $b) {
                        $bNum = trim($b[1]);
                        $bTitle = trim($b[2]);
                        $bContent = trim($b[3]);

                        $bullets = [];
                        if (preg_match_all('/â€¢\s*([^\nâ€¢]+)/u', $bContent, $bullMatches)) {
                            foreach ($bullMatches[1] as $bul) {
                                $bulClean = trim($bul);
                                if (strlen($bulClean) > 2) $bullets[] = $bulClean;
                            }
                        }

                        $blocks[] = [
                            'num'     => $bNum,
                            'title'   => $bTitle,
                            'content' => $bContent,
                            'bullets' => $bullets
                        ];
                    }
                }

                $icon = 'fa-layer-group';
                if ($secLetter === 'A') $icon = 'fa-crosshairs';
                elseif ($secLetter === 'B') $icon = 'fa-cogs';
                elseif ($secLetter === 'C') $icon = 'fa-clock';
                elseif ($secLetter === 'D') $icon = 'fa-user-check';

                $modules[] = [
                    'id'          => 'mod_section_' . strtolower($secLetter) . '_' . $idx,
                    'title'       => $title,
                    'badge'       => 'Section ' . $secLetter,
                    'badge_color' => 'badge-info',
                    'icon'        => $icon,
                    'type'        => 'section',
                    'content'     => $secBody,
                    'blocks'      => $blocks
                ];
            }
        }

        // Module: RFP Title & Objective Overview (for RFPs without "NOTICE INVITING e-TENDER")
        if (empty($modules) || count($modules) === 0) {
            if (preg_match('/(?:Implementation\s+of|RFP\s+for)[^\n]+?(?:Govt\.\s+of\s+[A-Za-z]+|Department[^\n]*|\.)/iu', $cleanText, $pmScope)) {
                $scopeContent = trim($pmScope[0]);
                $modules[] = [
                    'id'          => 'mod_rfp_scope',
                    'title'       => 'RFP Purpose & Scope of Procurement',
                    'badge'       => 'Scope Overview',
                    'badge_color' => 'badge-primary',
                    'icon'        => 'fa-video',
                    'type'        => 'notice',
                    'summary'     => $scopeContent,
                    'content'     => $scopeContent
                ];
            }
        }

        // Module: Authority & Submission Instructions (e.g. OCAC / WEBEL / Department)
        if (preg_match('/(?:RFP\s+to\s+be\s+Submitted\s+to|Submitted\s+to|Contact\s+Details|ODISHA\s+COMPUTER\s+APPLICATION)[\s\S]*?(?=(?:Place:|Signature|$))/iu', $cleanText, $amAuth)) {
            $modules[] = [
                'id'          => 'mod_authority_info',
                'title'       => 'Authority, Submission Center & Official Contact',
                'badge'       => 'Submission Center',
                'badge_color' => 'badge-dark',
                'icon'        => 'fa-building',
                'type'        => 'general',
                'content'     => trim($amAuth[0]),
                'blocks'      => []
            ];
        }

        // Fallback if no sections were parsed
        if (empty($modules)) {
            $paragraphs = preg_split('/\n\s*\n/', $cleanText);
            $chunkSize = max(1, (int)ceil(count($paragraphs) / 4));
            $chunks = array_chunk($paragraphs, $chunkSize);
            foreach ($chunks as $cIdx => $chunk) {
                $partNum = $cIdx + 1;
                $chunkContent = implode("\n\n", $chunk);
                $firstLine = strtok($chunkContent, "\n");
                $title = (strlen($firstLine) < 60 && strlen($firstLine) > 5) ? $firstLine : 'Module ' . $partNum;
                $modules[] = [
                    'id'          => 'mod_chunk_' . $partNum,
                    'title'       => $title,
                    'badge'       => 'Part ' . $partNum,
                    'badge_color' => 'badge-primary',
                    'icon'        => 'fa-file-alt',
                    'type'        => 'general',
                    'content'     => $chunkContent,
                    'blocks'      => []
                ];
            }
        }

        return $modules;
    }

    /**
     * AI Executive Document Summarizer & Intelligence Engine
     *
     * Extraction strategy for Indian government tenders (NIT/RFP):
     *  - Title  : joins multi-line NIT headings; looks for "for", "of", "regarding" triggers
     *  - Auth   : labeled-first (Inviting Authority / Issued by), then LIMITED/LTD entities in header lines
     *  - Dates  : handles Dt. prefix with optional spaces, dd-mm-yyyy, dd/mm/yyyy, dd.mm.yyyy, named month
     *  - Values : EMD and Estimated separately; strips /- suffix; handles lakh/crore shorthand
     *  - Keywords: concise comma-separated, NOT the full objective sentence
     *
     * @param string $cleanText
     * @param string $origName
     * @param string $ext
     * @return array
     */
    public function generateAiSummary($cleanText, $origName = '', $ext = '')
    {
        $docType        = 'General Business Document';
        $title          = '';
        $refNo          = '';
        $authority      = '';
        $portal         = '';
        $signatory      = '';
        $submissionDate     = '';
        $submissionTime     = '';
        $submissionDeadline = '';
        $preBidDate         = '';
        $openingDate        = '';
        $queryDate          = '';
        $publishedDate      = '';
        $estimatedValue     = '';
        $emdValue           = '';
        $objective          = '';
        $keywords           = '';
        $keyHighlights      = [];

        // ── Helpers ───────────────────────────────────────────────────────────

        // Normalize a date string to dd-mm-yyyy
        $normalizeDate = function($raw) {
            $raw = trim(preg_replace('/\s+/', ' ', $raw));
            // dd-mm-yyyy / dd/mm/yyyy / dd.mm.yyyy
            if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/', $raw, $m)) {
                return sprintf('%02d-%02d-%04d', (int)$m[1], (int)$m[2], (int)$m[3]);
            }
            // dd Mon yyyy  or  dd-Mon-yyyy
            $months = ['jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06',
                       'jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12'];
            if (preg_match('/^(\d{1,2})\s*[-\/\s]\s*([A-Za-z]{3,9})\s*[-\/\s]\s*(\d{4})$/', $raw, $m)) {
                $mon = strtolower(substr(trim($m[2]), 0, 3));
                if (isset($months[$mon])) {
                    return sprintf('%02d-%s-%04d', (int)$m[1], $months[$mon], (int)$m[3]);
                }
            }
            return $raw;
        };

        // Normalize time representation (e.g. "2 PM" -> "02:00 PM", "14:00 hrs" -> "02:00 PM")
        $normalizeTime = function($raw) {
            $raw = trim(preg_replace('/\s+/', ' ', $raw));
            if (empty($raw)) return '';
            if (preg_match('/^(\d{1,2})\s*(am|pm)$/i', $raw, $m)) {
                return sprintf('%02d:00 %s', (int)$m[1], strtoupper($m[2]));
            }
            if (preg_match('/^(\d{1,2}):(\d{2})\s*(am|pm)$/i', $raw, $m)) {
                return sprintf('%02d:%02d %s', (int)$m[1], (int)$m[2], strtoupper($m[3]));
            }
            if (preg_match('/^(\d{1,2}):(\d{2})\s*(?:hrs?|hours?)?$/i', $raw, $m)) {
                $h = (int)$m[1];
                $min = (int)$m[2];
                $ampm = $h >= 12 ? 'PM' : 'AM';
                $h12 = $h % 12;
                if ($h12 === 0) $h12 = 12;
                return sprintf('%02d:%02d %s', $h12, $min, $ampm);
            }
            if (preg_match('/^(\d{1,2})\s*(?:hrs?|hours?)$/i', $raw, $m)) {
                $h = (int)$m[1];
                $ampm = $h >= 12 ? 'PM' : 'AM';
                $h12 = $h % 12;
                if ($h12 === 0) $h12 = 12;
                return sprintf('%02d:00 %s', $h12, $ampm);
            }
            return $raw;
        };

        // Date and Time capture regexes with named groups
        $dateCapRx = '(?P<date>\d{1,2}\s*[-\/\.]\s*\d{1,2}\s*[-\/\.]\s*\d{2,4}|\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4})';
        $timeRx    = '(?:\s*(?:at\s*|,\s*|@\s*|[-–]\s*)?(?P<time>\d{1,2}(?::\d{2})?\s*(?:hrs?|hours?|am|pm|a\.m\.|p\.m\.)|\d{1,2}:\d{2}))?';

        $extractDateTime = function($patterns) use ($cleanText, $normalizeDate, $normalizeTime) {
            if (!is_array($patterns)) $patterns = [$patterns];
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $cleanText, $m)) {
                    $d = isset($m['date']) ? $normalizeDate($m['date']) : '';
                    $t = isset($m['time']) ? $normalizeTime($m['time']) : '';
                    if (!empty($d)) {
                        $full = !empty($t) ? ($d . ' at ' . $t) : $d;
                        return ['date' => $d, 'time' => $t, 'full' => $full];
                    }
                }
            }
            return ['date' => '', 'time' => '', 'full' => ''];
        };

        $extractDate = function($patterns) use ($extractDateTime) {
            $res = $extractDateTime($patterns);
            return $res['date'];
        };

        // Parse a money string like "16,00,000" or "5.50" into an integer (rupees)
        $parseMoney = function($numStr, $unit) {
            $num  = (float) str_replace(',', '', trim($numStr));
            $unit = strtolower(trim($unit));
            if (in_array($unit, ['crore', 'crores', 'cr', 'cr.'])) $num *= 10000000;
            elseif (in_array($unit, ['lakh', 'lakhs', 'lacs', 'lac', 'l', 'l.'])) $num *= 100000;
            return (int) round($num);
        };

        // ── 1. Document Classification ────────────────────────────────────────
        if (preg_match('/\b(?:RFP|NIT|TENDER|e-TENDER|NOTICE\s+INVITING|PROCUREMENT|BIDDER|QUOTATION|EOI|EXPRESSION\s+OF\s+INTEREST|RATE\s+CONTRACT|INVITATION\s+TO\s+BID)\b/i', $cleanText)) {
            $docType = 'Tender / Request for Proposal (RFP)';
        } elseif (preg_match('/\b(?:CURRICULUM\s+VITAE|RESUME|EDUCATIONAL\s+QUALIFICATION|WORK\s+EXPERIENCE|TECHNICAL\s+SKILLS)\b/i', $cleanText)) {
            $docType = 'Candidate Profile & Resume';
        } elseif (preg_match('/\b(?:MEMORANDUM\s+OF\s+UNDERSTANDING|AGREEMENT|BETWEEN\s+THE\s+PARTIES|NON-DISCLOSURE|CONTRACT)\b/i', $cleanText)) {
            $docType = 'Legal Contract & Agreement';
        } elseif (preg_match('/\b(?:FINANCIAL\s+PROPOSAL|BALANCE\s+SHEET|ANNUAL\s+REPORT|AUDIT)\b/i', $cleanText)) {
            $docType = 'Financial & Commercial Document';
        }

        // ── Bid Type Extraction (OEM vs Service) ──────────────────────────────
        $bidType = '';
        // 1. Check explicit label in text
        if (preg_match('/(?:Bid\s+Type|Tender\s+Type|Tender\s+Category|Category\s+of\s+(?:Tender|Bid|Work)|Type\s+of\s+Bid|Nature\s+of\s+Contract)\s*[:\-]\s*([^\n\r,;]{3,60})/iu', $cleanText, $btm)) {
            $btVal = trim($btm[1]);
            if (preg_match('/\b(?:OEM|Original\s+Equipment\s+Manufacturer|Goods|Supply|Product|Equipment|Hardware)\b/i', $btVal)) {
                $bidType = 'OEM';
            } elseif (preg_match('/\b(?:Services?|Consultancy|Maintenance|AMC|Support|Manpower|Facility|Operations?)\b/i', $btVal)) {
                $bidType = 'Service';
            }
        }
        // 2. Check document requirements & scope analysis (equipment/supply -> OEM, pure service/AMC -> Service)
        if (empty($bidType)) {
            if (preg_match('/\b(?:Manufacturer(?:[\'’]s)?\s+Authorization\s+(?:Form|Certificate|Letter)|MAF\b|OEM\s+Authorization|Bidder\s+(?:must|shall)\s+be\s+an?\s+OEM|Only\s+OEMs?\b|Original\s+Equipment\s+Manufacturer)\b/i', $cleanText)
                || preg_match('/\b(?:Supply,\s*Installation|Supply\s+of\s+(?:ANPR|CCTV|Camera|Servers?|Hardware|Equipment|Laptops?|Desktops?|Sensors?|Machinery|Vehicles?))\b/i', $cleanText)) {
                $bidType = 'OEM';
            } elseif (preg_match('/\b(?:Tender\s+for\s+(?:the\s+)?(?:Annual\s+Maintenance|AMC|Consultancy\s+Services?|Facility\s+Management\s+Services?|Managed\s+Services?)|Annual\s+Maintenance\s+Contract|CAMC\b|AMC\b|Facility\s+Management\s+Services?|Managed\s+Services?|Consultancy\s+Services?|Housekeeping|Security\s+Services?|Manpower\s+Services?|Hiring\s+of\s+Services?|Service\s+Contract)\b/i', $cleanText)) {
                $bidType = 'Service';
            }
        }


        // Strategy A: explicit label (Brief description of material/work, Name of Work, Subject, Scope of Work)
        $labelScopePatterns = [
            '/(?:Brief\s+description\s+of\s+(?:the\s+)?(?:material|work|goods|services?|supplies)|Name\s+of\s+(?:the\s+)?(?:Work|Project|Tender|Assignment)|Work\s+(?:Name|Description)|Subject(?:\s+of\s+Work)?|Scope\s+of\s+(?:Work|Services?))\s*[:\-.]?\s*([^\n\r]+(?:\r?\n(?!\s*(?:Sl\.|S\.No|No\.|Tender|NIT|Ref|EMD|Estimated|Date|Last|Opening|Pre-Bid|Portal|http|\d+\.\s+[A-Z]))[^\n\r]+)*)/iu',
            '/(?:Name\s+of\s+(?:the\s+)?(?:Work|Project|Tender)|Work\s+(?:Name|Description)|Subject(?:\s+of\s+Work)?)\s*[:\-]\s*(.{10,600}?)(?=\n\n|\n(?:Sl\.|S\.No|No\.|Tender|NIT|Ref|EMD|Estimated|Date|Last|Opening|Pre-Bid|Portal|http|\d+\.?\s+[A-Z]))/isu'
        ];
        foreach ($labelScopePatterns as $lsp) {
            if (preg_match($lsp, $cleanText, $tm)) {
                $candidate = preg_replace('/\s+/', ' ', trim($tm[1]));
                if (strlen($candidate) > 10 && strlen($candidate) < 600) {
                    $title = $candidate;
                    break;
                }
            }
        }

        // Strategy B: multi-line NIT scope sentence
        // Matches "Supply, Installation... under <authority>" spanning multiple lines without spilling into schedule tables
        if (empty($title)) {
            $actionVerbs = '(?:Supply|Installation|Construction|Procurement|Providing|Provision|Operation|Development|Implementation|Commissioning|Maintenance|Setting\s+up|Establishment|Design\s+and|Testing)';
            if (preg_match('/\b(' . $actionVerbs . '\b[^\n\r]+?(?:\r?\n(?!\s*(?:Sl\.|S\.No|Table|Event|Schedule|NIT\b|Tender\b|Notice\b|\d+\.))[^\n\r]+?)*?)(?:\.\s*(?:\r?\n|$)|(?=\r?\n\s*(?:Sl\.|S\.No|Table|Event|Schedule|NIT\b|Tender\b|Notice\b|\d+\.))|(?=\r?\n\s*\r?\n)|$)/iu', $cleanText, $tm)) {
                $candidate = preg_replace('/\s+/', ' ', trim($tm[1]));
                $candidate = rtrim($candidate, " .,;\t\n\r");
                if (strlen($candidate) > 15 && strlen($candidate) < 600) {
                    $title = $candidate;
                }
            }
        }

        // Strategy C: simple verb-phrase openers
        if (empty($title)) {
            $vPatterns = [
                '/(?:e-Tenders?\s+are\s+invited\s+for\s+)(.{15,300})/iu',
                '/(?:RFP|NIT)\s+(?:No\.?\s*[^\s,]+\s+)?(?:for|regarding)\s+(.{15,300})/iu',
                '/(?:Project|Tender|Assignment)\s+(?:Title|Name)\s*[:\-]\s*(.{5,300})/iu',
                '/Notice\s+Inviting\s+(?:e-?)?Tender\s+(?:for\s+)?(.{15,300})/iu',
            ];
            foreach ($vPatterns as $vp) {
                if (preg_match($vp, $cleanText, $tm)) {
                    $candidate = preg_replace('/\s+/', ' ', trim(strtok($tm[1], "\n")));
                    $candidate = rtrim($candidate, ' .,');
                    if (strlen($candidate) > 10 && strlen($candidate) < 500) {
                        $title = $candidate;
                        break;
                    }
                }
            }
        }

        // Strategy D: first meaningful non-metadata line
        if (empty($title)) {
            $skipPat = '/^(?:Page|Date|Time|Place|Sl\.|S\.No|No\.|Sr\.|Signature|Ref|www\.|http|\d+\.?\s*$|Government\s+of|Ministry\s+of|Department\s+of|Directorate\s+of|Office\s+of)/i';
            foreach (explode("\n", $cleanText) as $l) {
                $l = trim($l);
                if (strlen($l) > 18 && strlen($l) < 200 && !preg_match($skipPat, $l) && preg_match('/[a-zA-Z]{3,}/', $l)) {
                    $title = $l;
                    break;
                }
            }
            if (empty($title)) $title = pathinfo($origName, PATHINFO_FILENAME);
        }

        // Clean up title: collapse whitespace and safeguard against trailing table artifacts
        $title = preg_replace('/\s+/', ' ', trim($title));
        if (!empty($title)) {
            if (preg_match('/^(.*?)(?:\s+(?:Sl\.|S\.No|Table|Event|Schedule|Uploading\/Publishing|NIT\s+No\.?|Date\s*&)\b|\.\s+[A-Z0-9])/iu', $title, $mTrunc)) {
                $truncated = rtrim(trim($mTrunc[1]), ' .,;');
                if (strlen($truncated) > 20) {
                    $title = $truncated;
                }
            }
        }

        // ── 3. Tender Reference / NIT Number ─────────────────────────────────
        $refPatterns = [
            // Explicit label: Tender No., NIT No., Ref. No., etc.
            '/(?:Tender\s+(?:Enquiry\s+)?No\.?|NIT\s+No\.?|Reference\s+No\.?|Ref\.?\s*No\.?|Bid\s+No\.?|e-NIT\s+No\.?|Notice\s+No\.?|Enquiry\s+No\.?)\s*[:\-]?\s*([A-Za-z0-9][A-Za-z0-9\/\-_.]{3,60})/iu',
            // WTL-style: ALPHA/ALPHA/ALPHA/YY-YY/NNN
            '/\b([A-Z]{2,10}\/[A-Z0-9]{2,20}\/[A-Z0-9\-]{2,20}\/[0-9\-]{2,10}\/[0-9]{2,6})\b/',
            // Generic slash-separated code (must have at least 3 segments)
            '/\b([A-Z]{2,10}(?:\/[A-Z0-9\-]{2,20}){2,})\b/',
        ];
        foreach ($refPatterns as $rp) {
            if (preg_match($rp, $cleanText, $rm)) {
                $refNo = trim($rm[1]);
                break;
            }
        }

        // ── 4. Issuing Authority / Department ────────────────────────────────
        // Priority 1: Explicit labeled field (most reliable)
        $authLabelPats = [
            '/(?:Inviting\s+Authority|Tendering\s+Authority|Procuring\s+Entity|Client\s+(?:Name|Organization)|Issued\s+by|On\s+Behalf\s+of)\s*[:\-]\s*([^\n]{5,150})/iu',
            '/(?:Purchaser|Organization\s+Name|Employer)\s*[:\-]\s*([^\n]{5,120})/iu',
        ];
        foreach ($authLabelPats as $alp) {
            if (preg_match($alp, $cleanText, $am)) {
                $cand = trim($am[1]);
                $cand = trim(strtok($cand, "\n"));
                if (strlen($cand) > 3 && strlen($cand) < 160) {
                    $authority = $cand;
                    break;
                }
            }
        }

        // Priority 2: Known company/agency entity names in the first 60 lines
        // Targets lines containing LIMITED/LTD/CORPORATION/BOARD – commonly the issuer header
        if (empty($authority)) {
            $lines = explode("\n", $cleanText);
            $headerLines = array_slice($lines, 0, 60);
            foreach ($headerLines as $hl) {
                $hl = trim($hl);
                if (strlen($hl) < 5 || strlen($hl) > 150) continue;
                // Lines that look like an organization name (has org-type keyword, avoids plain text paragraphs)
                if (preg_match('/\b(?:Limited|Ltd\.?|Corporation|Board|Authority|Council|Institute|University|Commission|Centre|Agency)\b/i', $hl)
                    && !preg_match('/\b(?:shall|will|must|may|are|is|was|the|for|and|of|in|a|an)\b.{20,}/i', $hl)) {
                    $authority = $hl;
                    break;
                }
            }
        }

        // Priority 3: Any line mentioning Government of / Ministry of / Dept of
        if (empty($authority)) {
            $lines = explode("\n", $cleanText);
            foreach (array_slice($lines, 0, 80) as $l) {
                $l = trim($l);
                if (strlen($l) > 6 && strlen($l) < 150
                    && preg_match('/\b(?:Government|Ministry|Board|Corporation|Authority|Directorate|Department|Office)\b/i', $l)
                    && !preg_match('/^(?:Page|Date|Sl\.|No\.|\d)/i', $l)
                    && !preg_match('/\b(?:the\s+(?:tender|document|work|project|bid)|is\s+invited|are\s+invited|shall\s+be|will\s+be|surveillance|camera|system|installation|supply)\b/i', $l)) {
                    $authority = $l;
                    break;
                }
            }
        }

        // ── 5. Dates (multi-format, supports Dt. prefix with spaces) ─────────
        // Published / NIT date (excludes upload date, bid submission start date, pre-bid date, or bid opening date)
        $pubPatterns = [
            '/(?:Tender\s+No\.?\s*&?\s*Date|[A-Za-z0-9\/\-_]{5,40})\s+Dated\s+' . $dateCapRx . '/iu',
            '/(?:(?:Publishing|Published|Issue|Publication|Notification|Advertisement|e-?Publish(?:ed|ing)?)\s+Date(?:\s+of\s+(?:NIT|Tender|Notice|e-?Tender))?)\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
            '/(?:Date\s+of\s+(?:Publication|Issue|Advertisement|Notification|NIT))\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
            '/(?:NIT|Tender|Notice)\s+(?:Published|Issue)?\s*Date\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
            '/(?:^|\n)\s*(?:Tender\s+)?Dated?\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/ium',
            '/\bDated\s+' . $dateCapRx . '/iu',
        ];
        $pubRes = $extractDateTime($pubPatterns);
        $publishedDate = $pubRes['date'];

        // Submission / Last Date (NOT start date or EMD payment deadline)
        $subPatterns = [
            '/(?:Last\s+Date(?:\s*(?:and|&)\s*Time)?\s+(?:for|of)\s+(?:online\s+)?(?:Bid\s+Submission|Submission\s+of\s+(?:Bids?|Tenders?|RFPs?)|Receipt\s+of\s+Bids?))\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . $timeRx . '/iu',
            '/(?:Bid|Tender)\s+Submission\s+(?:End|Closing|Last|Due)\s+Date(?:\s*(?:and|&)\s*Time)?\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . $timeRx . '/iu',
            '/(?:Submission\s+(?:Deadline|Due\s+Date|Closing\s+Date|End\s+Date)|Closing\s+Date\s+(?:for|of)\s+(?:Bid|Tender|Submission))\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . $timeRx . '/iu',
            '/(?:Last\s+Date(?:\s*(?:and|&)\s*Time)?\s+(?:for|of)\s+(?:Submission|Receipt))\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . $timeRx . '/iu',
        ];
        $subRes = $extractDateTime($subPatterns);
        $submissionDate     = $subRes['date'];
        $submissionTime     = $subRes['time'];
        $submissionDeadline = $subRes['full'];

        // Pre-bid date
        $preBidPatterns = [
            '/(?:Pre[-\s]?Bid\s+(?:Conference|Meeting|Discussion|Date))(?:\s*[:\-.]|\s+Date\s*[:\-.]|\s+on)?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
            '/(?:Date\s+(?:and|&)\s+Time\s+of\s+Pre[-\s]?Bid\s+(?:Meeting|Conference))\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
        ];
        $preBidDate = $extractDate($preBidPatterns);

        // Opening date: Technical Bid Opening preferred over Financial
        $openPatterns = [
            '/(?:Opening\s+(?:of\s+)?(?:(?:General\s*(?:and|&)\s*)?Technical\s+Bids?|Technical\s+Proposal|Cover\s+1)|Technical\s+Bid\s+Opening(?:\s+Date)?)\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
            '/(?:Opening\s+(?:of\s+)?(?:Bids?|Tenders?)|Bid\s+Opening(?:\s+Date)?|Date\s+of\s+Opening\s+of\s+Bids?)\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
        ];
        $openingDate = $extractDate($openPatterns);

        // Query deadline
        $queryPatterns = [
            '/(?:Last\s+[Dd]ate\s+for\s+(?:Receiving\s+|Seeking\s+)?(?:[Qq]ueries|[Cc]larifications)|[Qq]uery\s+(?:Deadline|End\s+Date)|Clarification\s+End\s+Date)\s*[:\-.]?\s*(?:Dt\.?\s*)?' . $dateCapRx . '/iu',
        ];
        $queryDate = $extractDate($queryPatterns);

        // ── 6. Portal URL ─────────────────────────────────────────────────────
        // Prefer wbtenders / eprocure / gem / cppp / etenders style domains
        if (preg_match('/https?:\/\/(?:www\.)?(?:wbtenders|eprocure|etenders|gem|cppp|nicgep|mstc|ireps|nprocure|tenderwizard|govtprocure)[a-zA-Z0-9.\/_\-?=&%#]*/i', $cleanText, $pm)) {
            $portal = rtrim($pm[0], '.,;)');
        } elseif (preg_match('/https?:\/\/[a-zA-Z0-9.\/_\-?=&%#]+/i', $cleanText, $pm)) {
            $portal = rtrim($pm[0], '.,;)');
        }

        // ── 7. Signatory ──────────────────────────────────────────────────────
        if (preg_match('/Digitally\s+signed\s+by\s+([A-Za-z\s.]+?)(?=\s+Date\s*:|\n|$)/iu', $cleanText, $sm)) {
            $signatory = trim($sm[1]);
        }

        // ── 8. Estimated Tender Value ─────────────────────────────────────────
        // IMPORTANT: Must NOT match EMD / Tender Fee / Security Deposit
        // Look for the primary "Estimated Cost/Value/Amount" label
        $estPatterns = [
            '/(?:Estimated\s+(?:Tender\s+)?(?:Cost|Value|Amount|Project\s+Cost)|Total\s+(?:Estimated|Tender)\s+(?:Value|Cost)|Approximate\s+(?:Tender\s+)?(?:Cost|Value)|Tender\s+Value|Contract\s+(?:Value|Amount))\s*[:\-.]?\s*(?:Rs\.?|INR|₹)?\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)\s*(?:\/\s*-|\/-)?\s*(Lakh|Lakhs|Lacs|Lac|Crore|Crores|Cr\.?|L\.?)?\b/iu',
        ];
        foreach ($estPatterns as $ep) {
            if (preg_match($ep, $cleanText, $evm)) {
                $v = $this->_parseMoney_($evm[1], isset($evm[2]) ? $evm[2] : '');
                if ($v > 0) { $estimatedValue = (string)$v; break; }
            }
        }

        // ── 9. EMD Value ──────────────────────────────────────────────────────
        $emdPatterns = [
            '/(?:EMD(?:\s+(?:Amount|Value|Rs\.?))?|Earnest\s+Money\s+(?:Deposit(?:\s+Amount)?)?|Bid\s+Security(?:\s+Amount)?)\s*[:\-.]?\s*(?:Rs\.?|INR|₹)?\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)\s*(?:\/\s*-|\/-)?\s*(Lakh|Lakhs|Lacs|Lac|Crore|Crores|Cr\.?|L\.?)?\b/iu',
        ];
        foreach ($emdPatterns as $ep) {
            if (preg_match($ep, $cleanText, $emdm)) {
                $v = $this->_parseMoney_($emdm[1], isset($emdm[2]) ? $emdm[2] : '');
                if ($v > 0) { $emdValue = (string)$v; break; }
            }
        }

        // ── 10. Keywords (concise, comma-separated) ───────────────────────────
        // Extract meaningful noun phrases from the title + document context
        $kwParts = [];
        if (!empty($title)) {
            // Split title on conjunctions and commas into keyword segments
            $parts = preg_split('/(?:\s+(?:and|or|under|for|of|in|at|by|the)\s+|\s*,\s*|\s*&\s*)/iu', $title);
            foreach ($parts as $pt) {
                $pt = trim($pt, " \t\n\r,.-;:()");
                if (strlen($pt) >= 4 && strlen($pt) <= 50
                    && !preg_match('/^(?:Date|Time|Sl\.|Event|NIT|Schedule|Page|Uploading|Submission|Opening)\b/i', $pt)
                    && preg_match('/[a-zA-Z]{3,}/', $pt)) {
                    $kwParts[] = $pt;
                }
            }
        }
        // Add domain keywords found in the text
        $domainKw = [
            '/\bANPR\b/i'                                       => 'ANPR',
            '/\bCCTV\b/i'                                       => 'CCTV Surveillance',
            '/\bBody\s+Worn\s+Camera/i'                         => 'Body Worn Camera',
            '/\bNight\s+Vision\s+Binocular/i'                   => 'Night Vision Binocular',
            '/\bSurveillance\b/i'                               => 'Surveillance',
            '/\bPolice\s+(?:District|Station|Commissionerate)/i' => 'Police',
            '/\bBorder\s+(?:Security|Area)/i'                   => 'Border Security',
            '/\bIP\s+(?:Based|Camera|CCTV)\b/i'                 => 'IP-Based Camera',
            '/\bOCR\b/i'                                        => 'OCR',
            '/\bGIS\b/i'                                        => 'GIS',
            '/\bVMS\b/i'                                        => 'VMS',
            '/\bNVR\b/i'                                        => 'NVR',
            '/\bPTZ\b/i'                                        => 'PTZ Camera',
            '/\bAI\s+(?:Analytics|Camera)\b/i'                  => 'AI Analytics',
            '/\b(?:Smart\s+City|Smart\s+Policing)\b/i'          => 'Smart City',
            '/\bRFID\b/i'                                       => 'RFID',
            '/\bFace\s+Recognition\b/i'                         => 'Face Recognition',
            '/\bLicense\s+Plate\b/i'                            => 'License Plate Recognition',
            '/\bData\s+Center\b/i'                              => 'Data Center',
            '/\bCloud\b/i'                                      => 'Cloud',
            '/\bNetwork\s+(?:Switch|Infrastructure)\b/i'        => 'Network Infrastructure',
            '/\bFiber\s+Optic\b/i'                              => 'Fiber Optic',
            '/\bWireless\b/i'                                   => 'Wireless',
            '/\b(?:Supply\s+and\s+)?Installation\b/i'           => 'Installation',
            '/\bCommissioning\b/i'                              => 'Commissioning',
            '/\bMaintenance\b/i'                                => 'Maintenance',
            '/\bAMC\b/i'                                        => 'AMC',
        ];
        $addedKw = [];
        foreach ($domainKw as $pat => $kw) {
            if (preg_match($pat, $cleanText) && !in_array($kw, $addedKw)) {
                $addedKw[] = $kw;
            }
        }
        // Merge: start with domain keywords, then title parts to complete context
        $finalKw = array_unique(array_merge($addedKw, $kwParts));
        $finalKw = array_slice($finalKw, 0, 10); // cap at 10 concise terms
        $keywords = implode(', ', $finalKw);

        // ── 11. Objective (for display / remarks; not dumped into keywords) ───
        if ($docType === 'Tender / Request for Proposal (RFP)') {
            $authStr = !empty($authority)     ? $authority : 'the designated procurement authority';
            $refStr  = !empty($refNo)          ? ' [Ref: ' . $refNo . ']' : '';
            $subStr  = !empty($submissionDate) ? ' Submission deadline: ' . $submissionDate . '.' : '';
            $portStr = !empty($portal)         ? ' Portal: ' . $portal . '.' : '';
            $objective = 'Official tender issued by ' . $authStr . $refStr . ' for: ' . $title . '.' . $subStr . $portStr;
        } elseif ($docType === 'Candidate Profile & Resume') {
            $objective = 'Professional candidate profile detailing technical competencies, educational qualifications, project credentials, and work history for recruitment evaluation.';
        } else {
            $objective = 'Structured document outlining operational specifications, contractual terms, deliverable milestones, and administrative instructions.';
        }

        // ── 12. Short Title Generation ───────────────────────────────────────
        $fullScope = $title;
        $shortTitle = $this->_generateShortTitle_($authority, $fullScope);

        // ── 12b. Automatic OEM vs Service Document Classification ────────────
        $classificationResult = $this->classifyDocument($cleanText, !empty($shortTitle) ? $shortTitle : $fullScope);
        if ($classificationResult['classification'] === 'oem') {
            $bidType = 'OEM';
        } elseif ($classificationResult['classification'] === 'service') {
            $bidType = 'Service';
        }

        // ── 13. Key Highlights ────────────────────────────────────────────────
        if (!empty($shortTitle))     $keyHighlights[] = 'Scope: '                 . $shortTitle;
        if (!empty($authority))      $keyHighlights[] = 'Issued by: '             . $authority;
        if (!empty($refNo))          $keyHighlights[] = 'Tender Ref: '            . $refNo;
        if (!empty($estimatedValue)) $keyHighlights[] = 'Estimated Value: Rs.'    . number_format((float)$estimatedValue);
        if (!empty($emdValue))       $keyHighlights[] = 'EMD: Rs.'                . number_format((float)$emdValue);
        if (!empty($publishedDate))  $keyHighlights[] = 'Published: '             . $publishedDate;
        if (!empty($submissionDeadline)) {
            $keyHighlights[] = 'Submission Deadline: ' . $submissionDeadline;
        } elseif (!empty($submissionDate)) {
            $keyHighlights[] = 'Submission Deadline: ' . $submissionDate;
        }
        if (!empty($openingDate))    $keyHighlights[] = 'Bid Opening: '           . $openingDate;
        if (!empty($preBidDate))     $keyHighlights[] = 'Pre-Bid: '               . $preBidDate;
        if (!empty($portal))         $keyHighlights[] = 'Portal: '                . $portal;
        if (!empty($signatory))      $keyHighlights[] = 'Signatory: '             . $signatory;

        return [
            'doc_type'              => $docType,
            'document_type'         => $docType,
            'bid_type'              => $bidType,
            'classification'        => $classificationResult['classification'],
            'classification_status' => $classificationResult['classification_status'],
            'classification_reason' => $classificationResult['reason'],
            'confidence'            => $classificationResult['confidence'],
            'score_oem'             => $classificationResult['score_oem'],
            'score_service'         => $classificationResult['score_service'],
            'title'                 => $shortTitle ? $shortTitle : $fullScope,
            'short_title'           => $shortTitle ? $shortTitle : $fullScope,
            'document_title'        => $shortTitle ? $shortTitle : $fullScope,
            'full_title'            => $fullScope,
            'scope_of_work'         => $fullScope,
            'ref_no'                => $refNo,
            'ref_code'              => $refNo,
            'tender_id'             => $refNo,
            'authority'             => $authority,
            'issuing_authority'     => $authority,
            'department'            => $authority,
            'portal'                => $portal,
            'portal_or_mode'        => $portal,
            'signatory'             => $signatory,
            'published_date'        => $publishedDate,
            'estimated_value'       => $estimatedValue,
            'emd_value'             => $emdValue,
            'submission_date'       => $submissionDate,
            'submission_time'       => $submissionTime,
            'submission_deadline'   => $submissionDeadline ? $submissionDeadline : $submissionDate,
            'pre_bid_date'          => $preBidDate,
            'pre_bid_meeting'       => $preBidDate,
            'opening_date'          => $openingDate,
            'query_date'            => $queryDate,
            'keywords'              => $keywords,
            'objective'             => $objective,
            'executive_objective'   => $objective,
            'highlights'            => $keyHighlights,
            'key_highlights'        => $keyHighlights,
            'is_tender_or_rfp'      => ($docType === 'Tender / Request for Proposal (RFP)' || !empty($refNo) || !empty($submissionDate))
        ];
    }

    /**
     * Generates a concise, informative tender title combining:
     * Issuing company/authority + primary purpose or scope of work (approx 8-15 words).
     *
     * @param string $authority
     * @param string $fullScope
     * @return string
     */
    protected function _generateShortTitle_($authority, $fullScope)
    {
        $scope = trim($fullScope);
        if (empty($scope)) {
            return !empty($authority) ? $authority : '';
        }

        // 1. Remove introductory/tendering boilerplate prefixes
        $boilerplatePrefixes = [
            '/^(?:e-?Tenders?\s+(?:are\s+)?invited\s+(?:for\s+)?(?:the\s+)?(?:work\s+of\s+)?(?:implementation\s+of\s+)?)/iu',
            '/^(?:Notice\s+Inviting\s+(?:e-?)?Tender\s+(?:for\s+)?(?:the\s+work\s+of\s+)?)/iu',
            '/^(?:Request\s+for\s+Proposal\s+(?:\(RFP\)\s+)?for\s+(?:the\s+)?)/iu',
            '/^(?:RFP\s+for\s+(?:the\s+)?)/iu',
            '/^(?:Selection\s+of\s+(?:an?\s+)?(?:agency|bidder|vendor|system\s+integrator)\s+for\s+(?:the\s+)?)/iu',
            '/^(?:Tender\s+for\s+(?:the\s+)?(?:work\s+of\s+)?)/iu',
            '/^(?:Engagement\s+of\s+(?:an?\s+)?agency\s+for\s+)/iu',
            '/^(?:Bid\s+Document\s+for\s+)/iu',
        ];
        foreach ($boilerplatePrefixes as $bp) {
            $scope = preg_replace($bp, '', $scope);
        }

        // 2. Remove jurisdiction/regional ending clauses (e.g. "under Inspector General of Police...", "under Government of...")
        if (preg_match('/^(.*?)\s+(?:under|in\s+the\s+state\s+of|in\s+respect\s+of)\s+[A-Za-z\s,()]+/iu', $scope, $um)) {
            if (strlen(trim($um[1])) >= 20) {
                $scope = trim($um[1]);
            }
        }

        // 3. Remove verbose administrative details (e.g. "of 09 Police District & 01 Commissionerate", "across XX locations")
        $scope = preg_replace('/\s+of\s+\d+\s+(?:Police\s+Districts?|Districts?|Locations?|Offices?|Sites?)\s*(?:&|and)?\s*\d*\s*(?:Commissionerate|Sub-districts?)?/iu', '', $scope);
        $scope = preg_replace('/\s+(?:across|in|at)\s+\d+\s+(?:districts?|locations?|sites?|zones?)\b/iu', '', $scope);
        $scope = preg_replace('/\s+system\b/iu', '', $scope);
        $scope = preg_replace('/\s+and\s+Supply\s+of\b/iu', ' and', $scope);

        // 4. Strip verbose SITC prefixes to keep core nouns
        $scope = preg_replace('/\b(?:Supply,\s*Installation,\s*Testing,\s*Commissioning|Supply,\s*Installation\s*(?:and|&)\s*Commissioning|SITC)\s+(?:of\s+)?/iu', '', $scope);

        // 5. Clean up verbose hardware labels for concise title
        $scope = preg_replace('/\bIP\s+Based\s+CCTV\b/iu', 'CCTV', $scope);
        $scope = preg_replace('/\bBody\s+Worn\s+Camera\b/iu', 'Body-Worn Camera', $scope);

        // 6. If multiple chained items exist (e.g. "... and Body-Worn Camera & Night Vision Binocular"),
        // condense tertiary item so title stays within the ideal 8-15 words
        $scope = preg_replace('/(\band\s+[A-Za-z\s-]+?)\s*&\s*[A-Za-z\s-]+$/iu', '$1', $scope);

        // Normalize whitespace and punctuation
        $scope = preg_replace('/\s+/', ' ', trim($scope));
        $scope = rtrim($scope, " .,;\t\n\r-");

        // 7. Ensure core scope retains a clear purpose (e.g. Supply, Implementation, Services)
        if (!preg_match('/\b(?:Supply|Implementation|Procurement|Installation|Services?|Maintenance|Support|Development|Works?)\b/i', $scope)) {
            $scope .= ' Supply';
        }

        // 8. Combine Authority + Scope
        $combined = '';
        if (!empty($authority)) {
            $combined = $authority . ' – ' . $scope;
        } else {
            $combined = $scope;
        }

        // 9. Word count optimization: target ~8 to 15 words
        $words = preg_split('/\s+/', trim($combined));
        if (count($words) > 16) {
            $trimmed = implode(' ', array_slice($words, 0, 15));
            $trimmed = rtrim($trimmed, " .,;\t\n\r-&");
            return $trimmed;
        }

        return $combined;
    }

    /**
     * Parse a money string into integer rupees (handles lakh/crore suffix).
     */
    protected function _parseMoney_($numStr, $unit = '')
    {
        $num  = (float) str_replace(',', '', trim($numStr));
        $unit = strtolower(trim($unit));
        if (in_array($unit, ['crore', 'crores', 'cr', 'cr.'])) $num *= 10000000;
        elseif (in_array($unit, ['lakh', 'lakhs', 'lacs', 'lac', 'l', 'l.'])) $num *= 100000;
        return (int) round($num);
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ROBUST PURE-PHP PDF PARSER
    // Handles: FlateDecode (zlib), raw streams, BT/ET text blocks,
    //          Tj / TJ text operators, Td/Tm line positioning, hex strings
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public function parsePdfPurePhp($filePath)
    {
        $raw = @file_get_contents($filePath);
        if (!$raw || substr($raw, 0, 4) !== '%PDF') return '';
        $allText = '';
        $objectPattern = '/\d+\s+\d+\s+obj\s*(?:<<([\s\S]*?)>>)?\s*stream([\s\S]*?)endstream/';
        if (preg_match_all($objectPattern, $raw, $objMatches, PREG_SET_ORDER)) {
            foreach ($objMatches as $m) {
                $header = isset($m[1]) ? $m[1] : '';
                $streamData = $m[2];
                if (preg_match('/\/Subtype\s*\/(Image|OTF|CIDFontType)/i', $header)) continue;
                $filter = '';
                if (preg_match('/\/Filter\s*\[?\s*\/?(\w+)/i', $header, $fm)) $filter = $fm[1];
                $decoded = $this->_decodePdfStream(ltrim($streamData, "\r\n"), $filter);
                $t = $this->_extractTextFromPdfContent($decoded);
                if ($t) $allText .= $t . "\n";
            }
        }
        if (strlen(trim($allText)) < 50) {
            $allText = $this->_extractPdfUncompressed($raw);
        }
        return $allText;
    }

    protected function _decodePdfStream($data, $filter)
    {
        $filter = strtolower(trim($filter));
        if (in_array($filter, array('flatedecode','fl'))) {
            $r = @gzuncompress($data);
            if ($r !== false) return $r;
            $r = @gzinflate($data);
            if ($r !== false) return $r;
            if (strlen($data) > 2) {
                $r = @gzinflate(substr($data, 2));
                if ($r !== false) return $r;
            }
        } elseif (in_array($filter, array('asciihexdecode','ahx'))) {
            $hex = preg_replace('/[^0-9A-Fa-f]/', '', $data);
            return pack('H*', $hex);
        }
        $r = @gzuncompress($data);
        if ($r !== false) return $r;
        $r = @gzinflate($data);
        if ($r !== false) return $r;
        return $data;
    }

    protected function _extractTextFromPdfContent($content)
    {
        if (empty($content)) return '';
        $text = ''; $inBT = false; $lineBuffer = ''; $lastY = null;
        $lines = preg_split('/\r\n|\r|\n/', $content);
        foreach ($lines as $line) {
            $line = rtrim($line);
            if (preg_match('/^\s*BT\s*$/', $line)) { $inBT = true; continue; }
            if (preg_match('/^\s*ET\s*$/', $line)) {
                if ($lineBuffer !== '') { $text .= $lineBuffer . "\n"; $lineBuffer = ''; }
                $inBT = false; continue;
            }
            if (preg_match('/BT\s+([\s\S]+?)\s+ET/', $line, $btm)) {
                $t = $this->_parsePdfTextOperators($btm[1]);
                if ($t) $text .= $t . "\n";
                continue;
            }
            if (!$inBT) continue;
            if (preg_match('/^\s*T\*\s*$/', $line)) {
                if ($lineBuffer !== '') { $text .= $lineBuffer . "\n"; $lineBuffer = ''; }
                continue;
            }
            if (preg_match('/^\s*([-\d.]+)\s+([-\d.]+)\s+T[dD]\s*$/', $line, $m)) {
                if (floatval($m[2]) < -1) {
                    if ($lineBuffer !== '') { $text .= $lineBuffer . "\n"; $lineBuffer = ''; }
                } elseif (floatval($m[2]) == 0 && $lineBuffer !== '') { $lineBuffer .= ' '; }
                continue;
            }
            if (preg_match('/^\s*[-\d.]+\s+[-\d.]+\s+[-\d.]+\s+[-\d.]+\s+([-\d.]+)\s+([-\d.]+)\s+Tm\s*$/i', $line, $m)) {
                $y = floatval($m[2]);
                if ($lastY !== null && abs($y - $lastY) > 2) {
                    if ($lineBuffer !== '') { $text .= $lineBuffer . "\n"; $lineBuffer = ''; }
                }
                $lastY = $y; continue;
            }
            if (preg_match('/^\s*\/\w+\s+[\d.]+\s+Tf\s*$/i', $line)) continue;
            if (preg_match('/^\s*\(([\s\S]*)\)\s*Tj\s*$/i', $line, $m)) {
                $lineBuffer .= $this->_decodePdfString($m[1]); continue;
            }
            if (preg_match("/^\\s*\\(([\\s\\S]*)\\)\\s*'\\s*\$/", $line, $m)) {
                if ($lineBuffer !== '') { $text .= $lineBuffer . "\n"; $lineBuffer = ''; }
                $lineBuffer = $this->_decodePdfString($m[1]); continue;
            }
            if (preg_match('/^\s*\[[\s\S]*\]\s*TJ\s*$/i', $line)) {
                preg_match_all('/\((.*?)(?<!\\\\)\)|([-\d.]+)/', $line, $tjParts, PREG_SET_ORDER);
                foreach ($tjParts as $tp) {
                    if ($tp[0][0] === '(') { $lineBuffer .= $this->_decodePdfString($tp[1]); }
                    elseif (!empty($tp[2]) && floatval($tp[2]) < -200) { $lineBuffer .= ' '; }
                }
                continue;
            }
            if (preg_match('/^\s*<([0-9A-Fa-f\s]+)>\s*Tj\s*$/i', $line, $m)) {
                $hexStr = preg_replace('/\s+/', '', $m[1]);
                $decoded = '';
                for ($i = 0; $i < strlen($hexStr); $i += 2) {
                    $b = hexdec(substr($hexStr, $i, 2));
                    if ($b >= 32 && $b < 128) $decoded .= chr($b);
                }
                $lineBuffer .= $decoded; continue;
            }
        }
        if ($lineBuffer !== '') $text .= $lineBuffer . "\n";
        return $text;
    }

    protected function _parsePdfTextOperators($content)
    {
        $text = '';
        if (preg_match_all('/\((.*?)(?<!\\\\)\)\s*Tj/i', $content, $m)) {
            foreach ($m[1] as $s) $text .= $this->_decodePdfString($s) . ' ';
        }
        if (preg_match_all('/\[([\s\S]*?)\]\s*TJ/i', $content, $m)) {
            foreach ($m[1] as $arr) {
                if (preg_match_all('/\((.*?)(?<!\\\\)\)/', $arr, $sub)) {
                    foreach ($sub[1] as $s) $text .= $this->_decodePdfString($s);
                }
            }
        }
        return trim($text);
    }

    protected function _decodePdfString($s)
    {
        $s = preg_replace_callback('/\\\\(\d{1,3})/', function($m) {
            return chr(octdec($m[1]));
        }, $s);
        return str_replace(
            array('\\n','\\r','\\t','\\(','\\)','\\\\','\\/'),
            array("\n","\r","\t",'(',')','\\'  ,'/'  ),
            $s
        );
    }

    protected function _extractPdfUncompressed($raw)
    {
        $text = '';
        if (preg_match_all('/BT([\s\S]*?)ET/i', $raw, $btMatches)) {
            foreach ($btMatches[1] as $block) {
                $t = $this->_parsePdfTextOperators($block);
                if ($t) $text .= $t . "\n";
            }
        }
        if (strlen(trim($text)) < 50) {
            if (preg_match_all('/\(([^\(\)]{2,200})\)\s*(?:Tj|\')/i', $raw, $sm)) {
                $chunks = array();
                foreach ($sm[1] as $s) {
                    $decoded = $this->_decodePdfString($s);
                    $p = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $decoded);
                    if (strlen(trim($p)) > 1) $chunks[] = $p;
                }
                if (!empty($chunks)) $text .= implode(' ', $chunks);
            }
        }
        return $text;
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ROBUST DOCX EXTRACTOR â€” tables, lists, footnotes, endnotes, headers
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public function extractDocx($filePath)
    {
        if (!class_exists('ZipArchive')) return '';
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) return '';
        $parts = array('word/document.xml');
        for ($i = 1; $i <= 10; $i++) {
            $parts[] = "word/header{$i}.xml";
            $parts[] = "word/footer{$i}.xml";
        }
        $parts[] = 'word/footnotes.xml';
        $parts[] = 'word/endnotes.xml';
        $result = array();
        foreach ($parts as $partName) {
            $xml = $zip->getFromName($partName);
            if (!$xml) continue;
            $t = $this->_docxXmlToText($xml);
            if (!empty(trim($t))) $result[] = trim($t);
        }
        $zip->close();
        return implode("\n\n", $result);
    }

    protected function _docxXmlToText($xml)
    {
        $xml = preg_replace('/\s+xmlns[^=]*="[^"]*"/', '', $xml);
        $xml = preg_replace('/<w:(?:bookmarkStart|bookmarkEnd|proofErr|rPrChange|pPrChange|sectPr|pgSz|pgMar|cols|docGrid|headerReference|footerReference|instrText|fldChar|smartTag)[^>]*\/?>/i', '', $xml);
        $xml = preg_replace('/<\/w:tc>/i', " | ", $xml);
        $xml = preg_replace('/<\/w:tr>/i', "\n", $xml);
        $xml = preg_replace('/<\/w:p>/i', "\n", $xml);
        $xml = preg_replace('/<w:br[^>]*\/?>/i', "\n", $xml);
        $xml = preg_replace('/<w:softHyphen\s*\/>/i', '', $xml);
        $xml = preg_replace('/<w:noBreakHyphen\s*\/>/i', '-', $xml);
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n[ \t]+/', "\n", $text);
        $text = preg_replace('/[ \t]+\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ROBUST DOC (Word 97-2003) EXTRACTOR â€” OLE2 + UTF-16LE + ASCII
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public function extractDoc($filePath)
    {
        $data = @file_get_contents($filePath);
        if (!$data) return '';
        $text = '';
        if (substr($data, 0, 4) === "\xD0\xCF\x11\xE0") {
            $text = $this->_extractDocOle2($data);
        }
        if (strlen(trim($text)) < 50) {
            $utf16 = @iconv('UTF-16LE', 'UTF-8//IGNORE', $data);
            if ($utf16) {
                $cleaned = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/u', ' ', $utf16);
                $words = preg_split('/\s+/', $cleaned);
                $goodWords = array_filter($words, function($w) {
                    return strlen($w) >= 2 && preg_match('/[a-zA-Z]/', $w);
                });
                if (count($goodWords) > 30) {
                    $text = implode(' ', $goodWords);
                }
            }
        }
        if (strlen(trim($text)) < 50) {
            $runs = array();
            if (preg_match_all('/[\x20-\x7E]{4,}/', $data, $m)) {
                foreach ($m[0] as $run) {
                    $run = trim($run);
                    if (strlen($run) < 4) continue;
                    if (preg_match('/\b(bjbj|PAPX|CHPX|CompObj|SummaryInformation|MSWordDoc|Normal\.dotm|Calibri|Times New Roman|Arial|msonormal|mstheme|ListParagraph|DocumentSummary|Word\.Document)\b/i', $run)) continue;
                    $alphaCount = preg_match_all('/[a-zA-Z]/', $run);
                    if ($alphaCount < strlen($run) * 0.25) continue;
                    $runs[] = $run;
                }
            }
            if (!empty($runs)) $text = implode("\n", $runs);
        }
        return $text;
    }

    protected function _extractDocOle2($data)
    {
        $chunks = array();
        $len = strlen($data);
        $i = 0; $current = '';
        while ($i < $len - 1) {
            $lo = ord($data[$i]);
            $hi = ord($data[$i + 1]);
            if ($hi === 0x00 && $lo >= 0x20 && $lo <= 0x7E) {
                $current .= chr($lo);
            } elseif ($hi === 0x00 && in_array($lo, array(0x0D, 0x0A, 0x09))) {
                if ($current !== '') $current .= chr($lo);
            } else {
                if (strlen($current) >= 5 && preg_match('/[a-zA-Z]{2,}/', $current)) {
                    if (!preg_match('/\b(bjbj|PAPX|CHPX|Normal|Calibri|Arial|Times|mstheme|msonormal)\b/i', $current)) {
                        $chunks[] = rtrim($current);
                    }
                }
                $current = '';
            }
            $i += 2;
        }
        if (strlen($current) >= 5) $chunks[] = $current;
        return count($chunks) > 5 ? implode("\n", $chunks) : '';
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // RTF EXTRACTOR â€” strips all RTF control words, returns plain text
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    public function extractRtf($filePath)
    {
        $rtf = @file_get_contents($filePath);
        if (!$rtf) return '';
        if (strpos($rtf, '{\\rtf') !== 0 && strpos($rtf, '{\\rt') !== 0) return $rtf;
        $rtf = preg_replace('/\{\\\\(?:fonttbl|colortbl|stylesheet|info|pict|object|rsidtbl)[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/i', '', $rtf);
        $rtf = preg_replace_callback('/\\\\u(-?\d+)\??/', function($m) {
            $code = intval($m[1]);
            if ($code < 0) $code += 65536;
            return ($code >= 32 && $code < 127) ? chr($code) : '';
        }, $rtf);
        $rtf = preg_replace('/\\\\par\b\s*/', "\n", $rtf);
        $rtf = preg_replace('/\\\\line\b\s*/', "\n", $rtf);
        $rtf = preg_replace('/\\\\tab\b\s*/', "\t", $rtf);
        $rtf = str_replace(array('\\~','\\-','\\_'), array(' ','-','_'), $rtf);
        $rtf = preg_replace('/\\\\[a-z]+[-\d]* ?/i', '', $rtf);
        $rtf = preg_replace('/\\\\[^a-zA-Z]/', '', $rtf);
        $rtf = preg_replace('/[{}]/', '', $rtf);
        $rtf = preg_replace('/[ \t]+/', ' ', $rtf);
        $rtf = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $rtf);
        return trim($rtf);
    }

    /**
     * Automatic Document Classification (OEM vs Service vs Unknown)
     * Inspects actual text content using multi-signal weighted semantic analysis:
     * 1. Minimum text volume check
     * 2. Explicit metadata labels
     * 3. Scope of work & primary title intent
     * 4. Multi-tier weighted domain keywords
     * 5. Confidence scoring & ambiguous fallback to 'unknown'
     *
     * @param string $cleanText Document extracted text
     * @param string $title Document title or scope (optional)
     * @return array Classification result payload
     */
    public function classifyDocument($cleanText, $title = '')
    {
        $text = trim($cleanText);
        $charLen = mb_strlen($text, 'UTF-8');

        // Rule 1: Text sufficiency check
        if ($charLen < 50) {
            return [
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'confidence'            => 0.0,
                'score_oem'             => 0,
                'score_service'         => 0,
                'reason'                => 'Insufficient readable text content to perform reliable classification.'
            ];
        }

        $oemScore       = 0;
        $serviceScore   = 0;
        $oemMatches     = [];
        $serviceMatches = [];

        // Rule 2: Explicit metadata
        if (preg_match('/(?:Bid\s+Type|Tender\s+Type|Tender\s+Category|Category\s+of\s+(?:Tender|Bid|Work)|Type\s+of\s+Bid|Nature\s+of\s+Contract)\s*[:\-]\s*([^\n\r,;]{3,60})/iu', $text, $btm)) {
            $btVal = trim($btm[1]);
            if (preg_match('/\b(?:OEM|Original\s+Equipment\s+Manufacturer|Goods|Supply|Product|Equipment|Hardware)\b/i', $btVal)) {
                $oemScore += 8;
                $oemMatches[] = "Explicit category: {$btVal}";
            } elseif (preg_match('/\b(?:Services?|Consultancy|Maintenance|AMC|Support|Manpower|Facility|Operations?)\b/i', $btVal)) {
                $serviceScore += 8;
                $serviceMatches[] = "Explicit category: {$btVal}";
            }
        }

        // Rule 3: Title & primary subject
        $titleToCheck = trim($title);
        if (!empty($titleToCheck)) {
            if (preg_match('/\b(?:Annual\s+Maintenance\s+Contract|AMC\b|Comprehensive\s+Maintenance|CMC\b|CAMC\b|Facility\s+Management\s+Services?|FMS\b|Manpower\s+(?:Supply|Services?)|Staffing\s+Services?|Housekeeping|Cleaning\s+Services?|Security\s+(?:Guarding|Services?)|Operation\s+and\s+Maintenance|O\s*&\s*M\b|Consultancy\s+Services?|Hiring\s+of\s+(?:Services?|Vehicles?|Staff))\b/i', $titleToCheck)) {
                $serviceScore += 7;
                $serviceMatches[] = 'Title primary purpose: Service/AMC';
            }
            if (preg_match('/\b(?:Supply\s+(?:and\s+delivery\s+of|of)|Procurement\s+of|SITC\s+of|Supply,\s*Installation|Purchase\s+of\s+(?:Equipment|Hardware|Cameras?|Servers?|Goods))\b/i', $titleToCheck)) {
                $oemScore += 6;
                $oemMatches[] = 'Title primary purpose: Equipment Supply/OEM';
            }
        }

        // Rule 4: Weighted keywords
        $oemPatterns = [
            5 => [
                '/\b(?:Manufacturer(?:[\'’]s)?\s+Authorization\s+(?:Form|Certificate|Letter)|MAF)\b/i' => 'Manufacturer Authorization Form (MAF)',
                '/\b(?:OEM\s+Authorization(?:\s+Certificate|\s+Letter)?|OEM\s+Certificate)\b/i' => 'OEM Authorization Certificate',
                '/\b(?:Bidder\s+(?:must|shall)\s+be\s+an?\s+OEM|Only\s+OEMs?\s+(?:are\s+eligible|can\s+bid)|OEM\s+Direct\s+Bid)\b/i' => 'OEM Direct / OEM Only Requirement',
                '/\b(?:Original\s+Equipment\s+Manufacturer)\b/i' => 'Original Equipment Manufacturer reference',
                '/\b(?:Authorized\s+(?:Manufacturer|Dealer|Distributor|Partner\s+of\s+OEM))\b/i' => 'Authorized Manufacturer/Dealer',
                '/\b(?:Brand\s+Authorization|Manufacturer\s+Credentials?)\b/i' => 'Brand/Manufacturer Authorization',
                '/\b(?:Proprietary\s+(?:Equipment|Hardware|Articles?)\s+Certificate|PAC)\b/i' => 'Proprietary Equipment Certificate',
            ],
            3 => [
                '/\b(?:Bill\s+of\s+Material|BoM|Bill\s+of\s+Quantities|BoQ)\s+(?:for\s+Supply|for\s+Equipment|for\s+Hardware)\b/i' => 'BoM/BoQ for Equipment',
                '/\b(?:Equipment\s+Specifications?|Technical\s+Datasheet|Datasheet\s+of\s+OEM|Hardware\s+Specifications?)\b/i' => 'Equipment/Hardware Specifications',
                '/\b(?:Supply\s+(?:of|and\s+delivery\s+of)\s+(?:ANPR|CCTV|Cameras?|Servers?|Hardware|Equipment|Desktops?|Laptops?|Sensors?|Machinery|Vehicles?|Monitors?|Switches|Routers?|NVR|VMS))\b/i' => 'Specific Hardware/Equipment Supply',
                '/\b(?:Supply,\s*Installation,\s*Testing,\s*Commissioning|SITC)\b/i' => 'SITC of Equipment',
                '/\b(?:Hardware\s+Components?|OEM\s+Hardware|Equipment\s+Delivery)\b/i' => 'Hardware components',
            ],
            1 => [
                '/\b(?:Supply\s+of\s+Goods|Procurement\s+of\s+Goods|Supply\s+and\s+Delivery)\b/i' => 'Supply of Goods',
                '/\b(?:Make\s+and\s+Model|Make\s*[:\-]|Model\s*[:\-]|Product\s+Brochure)\b/i' => 'Make and Model',
                '/\b(?:Factory\s+Acceptance\s+Test|FAT\b|Site\s+Acceptance\s+Test|SAT\b)\b/i' => 'Factory/Site Acceptance Test',
            ]
        ];

        foreach ($oemPatterns as $weight => $patterns) {
            foreach ($patterns as $pat => $label) {
                if (preg_match_all($pat, $text, $matches)) {
                    $hitCount = min(count($matches[0]), 3);
                    $oemScore += $weight * $hitCount;
                    $oemMatches[] = "{$label} (x{$hitCount})";
                }
            }
        }

        $servicePatterns = [
            5 => [
                '/\b(?:Annual\s+Maintenance\s+Contract|Comprehensive\s+Maintenance\s+Contract|CAMC\b|CMC\b)\b/i' => 'Annual/Comprehensive Maintenance Contract (AMC/CMC)',
                '/\b(?:Facility\s+Management\s+Services?|FMS)\b/i' => 'Facility Management Services',
                '/\b(?:Manpower\s+(?:Supply|Services?|Outsourcing)|Staffing\s+Services?)\b/i' => 'Manpower Supply / Staffing',
                '/\b(?:Housekeeping\s+(?:Services?|Contract)|Cleaning\s+Services?)\b/i' => 'Housekeeping / Cleaning Services',
                '/\b(?:Security\s+(?:Guarding|Services?|Personnel))\b/i' => 'Security Guarding / Services',
                '/\b(?:Operation\s+and\s+Maintenance|Operations?\s*&\s*Maintenance|O\s*&\s*M)\b/i' => 'Operation & Maintenance (O&M)',
                '/\b(?:Repair\s+and\s+Maintenance|Servicing\s+Contract)\b/i' => 'Repair & Maintenance Contract',
                '/\b(?:Technical\s+Support\s+Services?|Managed\s+Services?\s+Provider|MSP)\b/i' => 'Technical Support / Managed Services',
                '/\b(?:Consultancy\s+Services?|Consulting\s+Assignment)\b/i' => 'Consultancy Services',
                '/\b(?:Hiring\s+of\s+(?:Vehicles?|Services?|Manpower|Cabs?|Equipment))\b/i' => 'Hiring of Services/Vehicles',
            ],
            3 => [
                '/\b(?:Service\s+Level\s+Agreement|SLA\b|Uptime\s+Guarantee|Penalty\s+for\s+Downtime)\b/i' => 'SLA / Uptime Guarantee',
                '/\b(?:Preventive\s+Maintenance|Routine\s+Maintenance|Breakdown\s+Maintenance)\b/i' => 'Maintenance schedules',
                '/\b(?:Scope\s+of\s+Services?|Terms\s+of\s+Reference|ToR\b)\b/i' => 'Scope of Services / ToR',
                '/\b(?:Helpdesk\s+Support|Ticketing\s+System|On-site\s+Engineers?)\b/i' => 'Helpdesk / Support Engineers',
                '/\b(?:Deployment\s+of\s+Personnel|Working\s+Hours\s+of\s+Staff)\b/i' => 'Deployment of Personnel',
            ],
            1 => [
                '/\b(?:AMC\b|Servicing|Quarterly\s+Maintenance|Monthly\s+Maintenance)\b/i' => 'Periodic maintenance',
                '/\b(?:Maintenance\s+Charges|Service\s+Charges)\b/i' => 'Service/Maintenance charges',
            ]
        ];

        foreach ($servicePatterns as $weight => $patterns) {
            foreach ($patterns as $pat => $label) {
                if (preg_match_all($pat, $text, $matches)) {
                    $hitCount = min(count($matches[0]), 3);
                    $serviceScore += $weight * $hitCount;
                    $serviceMatches[] = "{$label} (x{$hitCount})";
                }
            }
        }

        $diff  = abs($oemScore - $serviceScore);
        $total = $oemScore + $serviceScore;

        if ($total === 0) {
            return [
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'confidence'            => 0.0,
                'score_oem'             => 0,
                'score_service'         => 0,
                'reason'                => 'No explicit or contextual indicators found for OEM or Service.'
            ];
        }

        $oemRatio     = $oemScore / max(1, $serviceScore);
        $serviceRatio = $serviceScore / max(1, $oemScore);

        if ($oemScore >= 4 && ($oemRatio >= 1.3 || ($oemScore - $serviceScore >= 4))) {
            $confidence = round(min(0.99, 0.5 + ($oemScore / max(10, $total))), 2);
            return [
                'classification'        => 'oem',
                'classification_status' => 'classified',
                'confidence'            => $confidence,
                'score_oem'             => $oemScore,
                'score_service'         => $serviceScore,
                'reason'                => 'Classified as OEM based on equipment supply/manufacturer authorization indicators: ' . implode(', ', array_slice($oemMatches, 0, 3))
            ];
        }

        if ($serviceScore >= 4 && ($serviceRatio >= 1.3 || ($serviceScore - $oemScore >= 4))) {
            $confidence = round(min(0.99, 0.5 + ($serviceScore / max(10, $total))), 2);
            return [
                'classification'        => 'service',
                'classification_status' => 'classified',
                'confidence'            => $confidence,
                'score_oem'             => $oemScore,
                'score_service'         => $serviceScore,
                'reason'                => 'Classified as Service based on maintenance/service contract indicators: ' . implode(', ', array_slice($serviceMatches, 0, 3))
            ];
        }

        return [
            'classification'        => 'unknown',
            'classification_status' => 'unclassified',
            'confidence'            => round($diff / max(1, $total), 2),
            'score_oem'             => $oemScore,
            'score_service'         => $serviceScore,
            'reason'                => 'Ambiguous evidence with balanced OEM (' . $oemScore . ') and Service (' . $serviceScore . ') signals without a definitive primary purpose.'
        ];
    }

    /**
     * Saves the extracted text to a unique UTF-8 .txt file in the appropriate category folder
     * (uploads/oem/ or uploads/service/).
     *
     * @param string $extractedText The clean extracted text
     * @param string $classification 'oem', 'service', or 'unknown'
     * @param string $origName Original filename for readable slug
     * @return string|null Relative path to the saved .txt file (e.g. 'uploads/oem/...txt'), or null if unclassified/failed
     */
    public function saveExtractedText($extractedText, $classification, $origName = '')
    {
        $classification = strtolower(trim($classification));
        if ($classification !== 'oem' && $classification !== 'service') {
            return null; // Do not save .txt file for unknown or unclassified documents
        }

        if (empty(trim($extractedText))) {
            return null;
        }

        // Target directory: absolute path using FCPATH
        $baseDir = defined('FCPATH') ? FCPATH : dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
        $targetDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $classification . DIRECTORY_SEPARATOR;

        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                log_message('error', "Failed to create directory: {$targetDir}");
                return null;
            }
        }

        // Clean base name for filesystem safety and prevent path traversal
        $rawBase = pathinfo($origName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $rawBase);
        $safeBase = trim($safeBase, '_');
        if (empty($safeBase)) {
            $safeBase = 'document';
        }
        if (strlen($safeBase) > 50) {
            $safeBase = substr($safeBase, 0, 50);
        }

        // Generate unique filename with timestamp and random token
        $timestamp = time();
        $randomToken = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        $filename = "{$timestamp}_{$randomToken}_{$safeBase}.txt";
        $fullPath = $targetDir . $filename;

        // Collision avoidance check
        $counter = 1;
        while (file_exists($fullPath)) {
            $filename = "{$timestamp}_{$randomToken}_{$safeBase}_{$counter}.txt";
            $fullPath = $targetDir . $filename;
            $counter++;
        }

        // Ensure text is UTF-8 encoded
        $content = $extractedText;
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        // Write the file
        if (@file_put_contents($fullPath, $content) === false) {
            log_message('error', "Failed to write extracted text to: {$fullPath}");
            return null;
        }

        // Return relative path with forward slashes for API consistency
        return 'uploads/' . $classification . '/' . $filename;
    }

    /**
     * Extracts structured Tender Fact Sheet data across all 12 sections.
     * Works accurately for both pre-formatted Fact Sheets (like Bank of Baroda) and standard NIT/RFP tenders.
     *
     * @param string $cleanText Extracted readable text
     * @param string $origName Document filename
     * @param array $aiSummary Existing AI summary attributes (optional)
     * @return array 12-section structured attribute payload for TenderFactSheet table
     */
    public function extractFactSheet($cleanText, $origName = '', $aiSummary = [])
    {
        // 1. Repair common PDF extraction ligatures (e.g. "Installa on" -> "Installation")
        $repairedText = $this->_repairFactSheetLigatures($cleanText);

        $data = [];

        $findField = function($patterns, $default = '') use ($repairedText) {
            foreach ((array)$patterns as $pat) {
                if (preg_match($pat, $repairedText, $m)) {
                    return trim($m[1]);
                }
            }
            return $default;
        };

        // ── SECTION 1: Tender Overview ──
        $data['Department'] = $findField([
            '/(?:Department|Issuing\s+Authority|Tendering\s+Authority|Organization|Client|Employer)\s*[:\t\- ]+([^\n\r]{3,120})/iu',
            '/Department\s+([^\n\r]{3,120})/iu'
        ], isset($aiSummary['department']) ? $aiSummary['department'] : (isset($aiSummary['authority']) ? $aiSummary['authority'] : ''));

        $data['ZoneRegion'] = $findField([
            '/(?:Zone|Region|Circle|Division|Location)\s*[:\t\- ]+([^\n\r]{3,100})/iu',
            '/Zone\s+([^\n\r]{3,100})/iu'
        ], '');

        $titleCand = '';
        if (preg_match('/(?:Engagement\s+of\s+System\s+Integrator[^\n\r]+(?:\r?\n[^\n\r]+)*?)(?=\r?\n\s*(?:Tender\s+Type|Tender\s+Ref|Project\s+Value))/iu', $repairedText, $tm)) {
            $titleCand = preg_replace('/\s+Tender\s+Title\s+/i', ' ', $tm[0]);
            $titleCand = preg_replace('/\s+/', ' ', trim($titleCand));
        }
        if (empty($titleCand)) {
            $titleCand = $findField([
                '/(?:Tender\s+Title|Name\s+of\s+(?:the\s+)?Work|Scope\s+of\s+Work|Subject)\s*[:\t\- ]*([^\n\r]+(?:\r?\n[ \t]+[^\n\r]+)*)/iu',
                '/Tender\s+Title\s+([^\n\r]+(?:\r?\n[ \t]+[^\n\r]+)*)/iu'
            ], isset($aiSummary['title']) ? $aiSummary['title'] : (!empty($aiSummary['document_title']) ? $aiSummary['document_title'] : ''));
            $titleCand = preg_replace('/\s+/', ' ', trim($titleCand));
        }
        $data['TenderTitle'] = $titleCand;

        $data['TenderType'] = $findField([
            '/(?:Tender\s+Type|Bid\s+Type|Type\s+of\s+Bid|Category\s+of\s+Tender)\s*[:\t\- ]+([^\n\r]{3,100})/iu',
            '/Tender\s+Type\s+([^\n\r]{3,100})/iu'
        ], isset($aiSummary['bid_type']) ? $aiSummary['bid_type'] : '');

        $refCand = '';
        if (preg_match('/\b(ZO\s*:\s*[A-Z0-9_\-:]+(?:[^\n\r]*))/i', $repairedText, $rm)) {
            $refCand = trim($rm[1]);
        } elseif (preg_match('/Reference\s+No\.?\s*[\r\n]+([A-Z0-9_\-:]+)/i', $repairedText, $rm)) {
            $refCand = trim($rm[1]);
        }
        if (empty($refCand)) {
            $refCand = $findField([
                '/(?:Tender\s+Reference\s+No\.?|Tender\s+Ref\s*No\.?|NIT\s+No\.?|Ref\s+No\.?)\s*[:\t\- ]*([A-Za-z0-9\/\-_:. ]{4,100})/iu'
            ], isset($aiSummary['ref_no']) ? $aiSummary['ref_no'] : '');
        }
        $data['TenderRefNo'] = $refCand;

        $data['TenderDate'] = $findField([
            '/(?:Tender\s+Date|NIT\s+Date|Published\s+Date|Dated)\s*[:\t\- ]+([0-9]{1,2}[.\/-][0-9]{1,2}[.\/-][0-9]{2,4})/iu',
            '/Tender\s+Date\s+([0-9]{1,2}[.\/-][0-9]{1,2}[.\/-][0-9]{2,4})/iu'
        ], isset($aiSummary['published_date']) ? $aiSummary['published_date'] : '');

        $data['ProjectValue'] = $findField([
            '/(?:Project\s+Value|Estimated\s+(?:Cost|Value|Amount)|Contract\s+Value)\s*[:\t\- ]*([^\n\r]{3,60})/iu',
            '/Project\s+Value\s+([^\n\r]{3,60})/iu'
        ], isset($aiSummary['estimated_value']) ? '₹' . number_format((float)$aiSummary['estimated_value']) : '');

        // Clean currency glyphs if corrupted by encodings
        $data['ProjectValue'] = str_replace(['Γé╣', 'Ã¢â€šÂ¹', '?'], '₹', $data['ProjectValue']);
        $data['ProjectValueAmount'] = isset($aiSummary['estimated_value']) ? (float)$aiSummary['estimated_value'] : null;

        $data['ContractType'] = $findField([
            '/(?:Contract\s+Type|Type\s+of\s+Contract)\s*[:\t\- ]+([^\n\r]{3,100})/iu',
            '/Contract\s+Type\s+([^\n\r]{3,100})/iu'
        ], '');

        $data['WarrantyAmc'] = $findField([
            '/(?:Warranty\s*\+\s*AMC|Warranty\s*[\/&]\s*AMC|Warranty\s+and\s+AMC)\s*[:\t\- ]+([^\n\r]{3,120})/iu',
            '/Warranty\s*\+\s*AMC\s+([^\n\r]{3,120})/iu'
        ], '');

        $data['BidSystem'] = $findField([
            '/(?:Bid\s+System|System\s+of\s+Bidding|Cover\s+System)\s*[:\t\- ]+([^\n\r]{3,100})/iu',
            '/Bid\s+System\s+([^\n\r]{3,100})/iu'
        ], '');

        // ── SECTION 2: Important Dates ──
        $dates = [];
        $datePatterns = [
            'Tender Published'          => '/(?:Tender\s+Published|NIT\s+Published)\s*[:\t\- ]*([^\n\r]{6,50})/iu',
            'Tender Download'           => '/(?:Tender\s+Download(?:\s+Period)?|Document\s+Download)\s*[:\t\- ]*([^\n\r]{6,60})/iu',
            'Pre-Bid Query Submission'  => '/(?:Pre[-\s]?Bid\s+Query\s+Submission|Clarification\s+Submission)\s*[:\t\- ]*([^\n\r]{6,50})/iu',
            'Pre-Bid Meeting'           => '/(?:Pre[-\s]?Bid\s+Meeting|Pre[-\s]?Bid\s+Conference)\s*[:\t\- ]*([^\n\r]{6,60})/iu',
            'Bid Submission'            => '/(?:Bid\s+Submission(?:\s+Deadline)?|Last\s+Date\s+(?:and\s+Time\s+)?for\s+Submission)\s*[:\t\- ]*([^\n\r]{6,60})/iu',
            'Technical Bid Opening'     => '/(?:Technical\s+Bid\s+Opening|Opening\s+of\s+Technical\s+Bid)\s*[:\t\- ]*([^\n\r]{6,60})/iu',
            'Financial Bid Opening'     => '/(?:Financial\s+Bid\s+Opening|Opening\s+of\s+Financial\s+Bid)\s*[:\t\- ]*([^\n\r]{6,60})/iu',
        ];

        foreach ($datePatterns as $act => $pat) {
            if (preg_match($pat, $repairedText, $m)) {
                $val = trim($m[1]);
                $val = str_replace(['ΓÇô', 'â€“', 'â€”'], '–', $val);
                $time = '';
                if (preg_match('/\(([^)]+)\)/', $val, $tm)) {
                    $time = trim($tm[1]);
                }
                $dates[] = [
                    'activity' => $act,
                    'date_text'=> $val,
                    'time'     => $time
                ];
            }
        }

        // Fallbacks from aiSummary
        if (empty($dates)) {
            if (!empty($aiSummary['published_date']))   $dates[] = ['activity' => 'Tender Published', 'date_text' => $aiSummary['published_date'], 'time' => ''];
            if (!empty($aiSummary['pre_bid_date']))      $dates[] = ['activity' => 'Pre-Bid Meeting', 'date_text' => $aiSummary['pre_bid_date'], 'time' => ''];
            if (!empty($aiSummary['submission_date']))   $dates[] = ['activity' => 'Bid Submission', 'date_text' => $aiSummary['submission_date'], 'time' => isset($aiSummary['submission_time']) ? $aiSummary['submission_time'] : ''];
            if (!empty($aiSummary['opening_date']))      $dates[] = ['activity' => 'Technical Bid Opening', 'date_text' => $aiSummary['opening_date'], 'time' => ''];
            if (!empty($aiSummary['query_date']))        $dates[] = ['activity' => 'Pre-Bid Query Submission', 'date_text' => $aiSummary['query_date'], 'time' => ''];
        }

        $data['TenderPublishedDate']  = isset($dates[0]) ? $dates[0]['date_text'] : (!empty($aiSummary['published_date']) ? $aiSummary['published_date'] : '');
        $data['TenderDownloadPeriod'] = isset($dates[1]) ? $dates[1]['date_text'] : '';
        $data['PreBidQueryDeadline']  = isset($dates[2]) ? $dates[2]['date_text'] : (!empty($aiSummary['query_date']) ? $aiSummary['query_date'] : '');
        $data['PreBidMeeting']         = isset($dates[3]) ? $dates[3]['date_text'] : (!empty($aiSummary['pre_bid_date']) ? $aiSummary['pre_bid_date'] : '');
        $data['BidSubmissionDeadline'] = isset($dates[4]) ? $dates[4]['date_text'] : (!empty($aiSummary['submission_deadline']) ? $aiSummary['submission_deadline'] : '');
        $data['TechnicalBidOpening']   = isset($dates[5]) ? $dates[5]['date_text'] : (!empty($aiSummary['opening_date']) ? $aiSummary['opening_date'] : '');
        $data['FinancialBidOpening']   = isset($dates[6]) ? $dates[6]['date_text'] : '';
        $data['ImportantDatesJson']    = json_encode($dates, JSON_UNESCAPED_UNICODE);

        // ── SECTION 3: Tender Value ──
        $data['EstimatedValue'] = $findField([
            '/(?:Estimated\s+Value|Estimated\s+Cost)\s*[:\t\- ]*([^\n\r]{3,60})/iu',
            '/Estimated\s+Value\s+([^\n\r]{3,60})/iu'
        ], $data['ProjectValue']);
        $data['EstimatedValue'] = str_replace(['Γé╣', 'Ã¢â€šÂ¹', '?'], '₹', $data['EstimatedValue']);
        $data['EstimatedValueAmount'] = isset($aiSummary['estimated_value']) ? (float)$aiSummary['estimated_value'] : null;

        $data['EmdAmount'] = $findField([
            '/(?:EMD(?:\s+Amount)?|Earnest\s+Money\s+Deposit)\s*[:\t\- ]*([^\n\r]{3,60})/iu',
            '/EMD\s+([^\n\r]{3,60})/iu'
        ], isset($aiSummary['emd_value']) ? '₹' . number_format((float)$aiSummary['emd_value']) : '');
        $data['EmdAmount'] = str_replace(['Γé╣', 'Ã¢â€šÂ¹', '?'], '₹', $data['EmdAmount']);
        $data['EmdAmountValue'] = isset($aiSummary['emd_value']) ? (float)$aiSummary['emd_value'] : null;

        $data['PbgDetails'] = $findField([
            '/(?:Performance\s+Bank\s+Guarantee|PBG|Security\s+Deposit)\s*[:\t\- ]*([^\n\r]{3,80})/iu',
            '/Performance\s+Bank\s+Guarantee\s+([^\n\r]{3,80})/iu'
        ], '');

        $data['PbgPercentage'] = null;
        if (!empty($data['PbgDetails']) && preg_match('/([0-9]+(?:\.[0-9]+)?)\s*%/i', $data['PbgDetails'], $pm)) {
            $data['PbgPercentage'] = (float)$pm[1];
        }

        $data['BidValidityDays'] = $findField([
            '/(?:Bid\s+Validity|Validity\s+of\s+Bid)\s*[:\t\- ]*([^\n\r]{3,50})/iu',
            '/\b(180\s+Days|90\s+Days|120\s+Days)\b/i'
        ], '');

        // ── SECTION 4: Scope of Work ──
        $scopes = [];
        if (preg_match('/(?:4\.\s*Scope\s+of\s+Work|Scope\s+of\s+(?:Work|Services?)|Schedule\s+of\s+Requirements?)([\s\S]*?)(?=(?:\n\s*(?:5\.|0?5\b|Areas\s+Covered|Contract\s+Period|Tender\s+Value|6\.|\d+\.)))/iu', $repairedText, $sm)) {
            $lines = preg_split('/\r?\n/', $sm[1]);
            foreach ($lines as $line) {
                $line = trim($line, " \t\n\r*•-·∩é╖");
                if (strlen($line) > 3 && strlen($line) < 140 && !preg_match('/^(?:Selected\s+SI|Particular|Details|Scope)/i', $line)) {
                    $scopes[] = $line;
                }
            }
        }
        if (empty($scopes) && !empty($aiSummary['highlights'])) {
            foreach ($aiSummary['highlights'] as $hl) {
                $hl = trim($hl);
                if (!empty($hl)) $scopes[] = $hl;
            }
        }
        if (empty($scopes) && !empty($aiSummary['scope_of_work'])) {
            $scopes = array_filter(array_map('trim', explode("\n", $aiSummary['scope_of_work'])));
        }
        $data['ScopeOfWork'] = implode("\n", $scopes);
        $data['ScopeItemsJson'] = json_encode($scopes, JSON_UNESCAPED_UNICODE);

        // ── SECTION 5: Areas Covered ──
        $areas = [];
        $lines = explode("\n", $repairedText);
        $inArea = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/5\.\s*Areas\s+Covered/i', $line)) { $inArea = true; continue; }
            if ($inArea && preg_match('/6\.\s*Contract\s+Period/i', $line)) { $inArea = false; break; }
            if ($inArea) {
                if (preg_match('/^(Delhi\s+(?:East|West|North|South))\s+(Delhi\s+(?:East|West|North|South))$/iu', $line, $am)) {
                    $areas[] = ['region' => trim($am[1]), 'coverage' => trim($am[2])];
                } elseif (preg_match('/^([A-Za-z\s]+?)\s{2,}(.+)$/iu', $line, $am)) {
                    if (strtolower(trim($am[1])) !== 'region') {
                        $areas[] = ['region' => trim($am[1]), 'coverage' => trim($am[2])];
                    }
                } elseif (preg_match('/^([A-Za-z]+(?:\s+[A-Za-z]+)?)\s+\1$/iu', $line, $am)) {
                    if (strtolower(trim($am[1])) !== 'region') {
                        $areas[] = ['region' => trim($am[1]), 'coverage' => trim($am[1])];
                    }
                }
            }
        }
        $areaTextLines = [];
        foreach ($areas as $ar) $areaTextLines[] = "{$ar['region']}: {$ar['coverage']}";
        $data['AreasCovered'] = implode("\n", $areaTextLines);
        $data['AreasCoveredJson'] = json_encode($areas, JSON_UNESCAPED_UNICODE);

        // ── SECTION 6: Contract Period ──
        $data['ContractInitialDuration'] = $findField([
            '/Initial\s+Contract\s*:?[\s\r\n]*[o•\-\*]?\s*([0-9]+\s*[A-Za-z]+)/iu',
            '/Initial\s+Contract\s*[:\- ]*([^\n\r]+)/iu',
            '/(?:Contract\s+Period|Duration\s+of\s+Contract)\s*[:\- ]*([0-9]+\s*[A-Za-z]+)/iu'
        ], '');
        $data['ContractExtendable'] = $findField([
            '/Extendable\s*:?[\s\r\n]*[o•\-\*]?\s*([0-9]+\s+[A-Za-z\s]+?)(?=\r?\n\s*[\S]+|\Z)/iu',
            '/Extendable\s*[:\- ]+([0-9]+\s+[A-Za-z\s]+)/iu'
        ], '');
        $data['ContractMaxPeriod'] = $findField([
            '/Maximum\s+Period\s*:?[\s\r\n]*[o•\-\*]?\s*([0-9]+\s*[A-Za-z\s]+?)(?=\r?\n\s*[\S]+|\Z)/iu',
            '/Maximum\s+Period\s*[:\- ]*([^\n\r]+)/iu'
        ], '');
        $data['ContractExtensionConditions'] = $findField([
            '/(Extension\s+depends\s+upon[^\n\r]+)/iu',
            '/Extension\s+conditions?\s*[:\- ]*([^\n\r]+)/iu'
        ], '');

        // ── SECTION 7: Award Criteria ──
        $data['AwardEvaluationMethod'] = $findField([
            '/(\bL1\s+based\s+on[^\n\r]+)/iu',
            '/Evaluation\s+Method\s*[:\- ]*([^\n\r]+)/iu',
            '/\b(L1\s+Bidder|Lowest\s+Cost|QCBS|Lowest\s+Evaluated\s+Bidder)\b/iu'
        ], '');
        $data['AwardSplitWork'] = $findField([
            '/(Bank\s+may\s+split\s+work[^\n\r]*)/iu',
            '/(?:Split\s+of\s+Work|Split\s+Work|Splitting\s+of\s+Order)\s*[:\- ]*([^\n\r]+)/iu'
        ], '');
        if (preg_match('/(60\s*:\s*40[\s\S]*?60\s*:\s*20\s*:\s*20)/is', $repairedText, $amatch)) {
            $cleanAlloc = preg_replace('/\s+/', ' ', trim($amatch[1]));
            $cleanAlloc = preg_replace('/[^0-9:\sA-Za-z]/', ' ', $cleanAlloc);
            $data['AwardAllocation'] = preg_replace('/\s+/', ' ', trim($cleanAlloc));
        } else {
            $data['AwardAllocation'] = $findField([
                '/(60\s*:\s*40\s+or\s+60\s*:\s*20\s*:\s*20)/iu',
                '/Possible\s+allocation\s*[:\- ]*([0-9\s:]+)/iu'
            ], '');
        }
        $data['AwardL2L3Matching'] = $findField([
            '/(L2[\/&]L3\s+should\s+match[^\n\r]+)/iu',
            '/Matching\s+of\s+Rates\s*[:\- ]*([^\n\r]+)/iu'
        ], '');
        $data['AwardConditions'] = trim($data['AwardEvaluationMethod'] . ' ' . $data['AwardSplitWork'] . ' ' . $data['AwardAllocation'] . ' ' . $data['AwardL2L3Matching']);

        // ── SECTION 8: Performance Guarantee ──
        $data['PbgAmount'] = $findField([
            '/(?:Requirement\s+Details\s+Amount|Performance\s+Guarantee\s+Amount)\s*[:\t\- ]*([0-9]+(?:\.[0-9]+)?\s*%)/iu',
            '/\b([0-9]+%\s*(?:of\s+Contract\s+Value)?)/i'
        ], '');
        $data['PbgValidity'] = $findField([
            '/(?:Validity)\s*[:\t\- ]*(Contract\s+Period\s*\+\s*[^\n\r]+)/iu',
            '/(Contract\s+Period\s*\+\s*[0-9]+\s*Months)/iu'
        ], '');
        $data['PbgSubmissionDeadline'] = $findField([
            '/(?:Submission)\s*[:\t\- ]*(Within\s+[0-9]+\s*Days?)/iu',
            '/(Within\s+[0-9]+\s*Days)/iu'
        ], '');

        // ── SECTION 9: Bid Validity ──
        $data['BidValidityDuration'] = !empty($data['BidValidityDays']) ? $data['BidValidityDays'] : '';

        // ── SECTION 10: Warranty ──
        $data['WarrantyDuration'] = $data['WarrantyAmc'];
        $data['WarrantyReplacement'] = '';
        $data['WarrantyRepair'] = '';
        $data['WarrantySupport'] = '';
        $data['AmcTerms'] = '';
        $data['WarrantyTerms'] = $findField([
            '/(?:Warranty\s+Terms|Warranty\s+Clause)\s*[:\- ]*([^\n\r]+)/iu'
        ], '');

        // ── SECTION 11: Risk Points ──
        $risks = [];
        if (preg_match('/(?:11\.\s*Risk\s+Points|Risk\s+Points)([\s\S]*?)(?=(?:\n\s*(?:12\.|\d+\.|\Z)))/iu', $repairedText, $rm)) {
            $rawRisks = preg_split('/(?:\r?\n\s*∩é╖|\r?\n\s*[•\x{F0B7}\x{00B7}]|\r?\n\s*\d+\.)/u', $rm[1]);
            foreach ($rawRisks as $rk) {
                $clean = trim(preg_replace('/\s+/', ' ', $rk));
                $clean = trim($clean, " \t\n\r*•-·∩é╖.:");
                if (strlen($clean) > 8) {
                    $risks[] = $clean;
                }
            }
        }
        $data['RiskPoints'] = implode("\n", $risks);
        $data['RiskPointsJson'] = json_encode($risks, JSON_UNESCAPED_UNICODE);

        // ── SECTION 12: Opportunity Assessment ──
        $data['PrebidQueryNotes'] = $findField([
            '/(Minimum\s+few\s+Changes\s+is\s+required[\s\S]*?Queries\.?)/iu',
            '/(Minimum\s+few\s+Changes\s+is\s+required[^\n\r]+)/iu',
            '/(?:Pre-?bid\s+Queries|Query\s+Assessment)\s*[:\- ]*([^\n\r]+)/iu'
        ], '');
        $data['PrebidQueryNotes'] = preg_replace('/\s+/', ' ', $data['PrebidQueryNotes']);

        $assessmentItems = [];
        if (preg_match('/(?:Key\s+Attention\s+Areas|Opportunity\s+Assessment)([\s\S]*?)$/iu', $repairedText, $am)) {
            $rawOA = preg_split('/(?:\r?\n\s*∩é╖|\r?\n\s*[•\x{F0B7}\x{00B7}]|\r?\n\s*\d+\.)/u', $am[1]);
            foreach ($rawOA as $item) {
                $clean = trim(preg_replace('/\s+/', ' ', $item));
                $clean = trim($clean, " \t\n\r*•-·∩é╖.:");
                if (strlen($clean) > 10 && !preg_match('/^(?:for\s+I-Net|Minimum\s+few|Key\s+Attention|Opportunity)/i', $clean)) {
                    $assessmentItems[] = $clean;
                }
            }
        }
        $data['OpportunityAssessment'] = implode("\n", $assessmentItems);
        $data['OpportunityAssessmentJson'] = json_encode($assessmentItems, JSON_UNESCAPED_UNICODE);

        // ── BUILD 12 MAIN SECTION COLUMNS (One Column Per Section) ──
        $serKV = function(array $items) {
            $parts = [];
            foreach ($items as $k => $v) {
                $vStr = trim((string)$v);
                if ($vStr === '') continue;
                $vEsc = str_replace('|', '\|', $vStr);
                $kClean = str_replace([':', '|'], ['-', '/'], trim($k));
                $parts[] = "{$kClean}: {$vEsc}";
            }
            return implode(' | ', $parts);
        };

        $serList = function(array $items, $numbered = true) {
            $parts = [];
            $i = 1;
            foreach ($items as $item) {
                $itemStr = trim((string)$item);
                if ($itemStr === '') continue;
                $clean = preg_replace('/^[\s*•\-·∩é╖\x{F0B7}\x{00B7}\.o\d\)]+/u', '', $itemStr);
                $clean = trim($clean);
                if (empty($clean)) continue;
                $itemEsc = str_replace('|', '\|', $clean);
                if ($numbered) {
                    $parts[] = "{$i}. {$itemEsc}";
                    $i++;
                } else {
                    $parts[] = $itemEsc;
                }
            }
            return implode(' | ', $parts);
        };

        // 1. TenderOverview
        $data['TenderOverview'] = $serKV([
            'Department'            => $data['Department'],
            'Zone'                  => $data['ZoneRegion'],
            'Tender Title'          => $data['TenderTitle'],
            'Tender Type'           => $data['TenderType'],
            'Tender Reference No'   => $data['TenderRefNo'],
            'Tender Date'           => $data['TenderDate'],
            'Project Value'         => !empty($data['ProjectValue']) ? $data['ProjectValue'] : $data['EstimatedValue'],
            'Contract Type'         => $data['ContractType'],
            'Warranty + AMC'        => $data['WarrantyAmc'],
            'Bid System'            => $data['BidSystem']
        ]);

        // 2. ImportantDates
        $datesKV = [];
        foreach ($dates as $d) {
            if (!empty($d['activity']) && !empty($d['date_text'])) {
                $datesKV[$d['activity']] = $d['date_text'];
            }
        }
        $data['ImportantDates'] = $serKV($datesKV);

        // 3. TenderValue
        $data['TenderValue'] = $serKV([
            'Estimated Value'               => !empty($data['EstimatedValue']) ? $data['EstimatedValue'] : $data['ProjectValue'],
            'EMD'                           => $data['EmdAmount'],
            'Performance Bank Guarantee'    => !empty($data['PbgDetails']) ? $data['PbgDetails'] : (!empty($data['PbgAmount']) ? $data['PbgAmount'] : ''),
            'Bid Validity'                  => !empty($data['BidValidityDays']) ? $data['BidValidityDays'] : $data['BidValidityDuration']
        ]);

        // 4. ScopeOfWork
        $data['ScopeOfWork'] = $serList($scopes, true);

        // 5. AreasCovered
        $areasKV = [];
        foreach ($areas as $a) {
            if (!empty($a['region']) && !empty($a['coverage']) && strtolower(trim($a['region'])) !== 'region') {
                $areasKV[$a['region']] = $a['coverage'];
            }
        }
        $data['AreasCovered'] = $serKV($areasKV);

        // 6. ContractPeriod
        $data['ContractPeriod'] = $serKV([
            'Initial Contract'      => $data['ContractInitialDuration'],
            'Extendable'            => $data['ContractExtendable'],
            'Maximum Period'        => $data['ContractMaxPeriod'],
            'Extension Conditions'  => $data['ContractExtensionConditions']
        ]);

        // 7. AwardCriteria
        $data['AwardCriteria'] = $serKV([
            'Evaluation Method'     => $data['AwardEvaluationMethod'],
            'Split Work'            => $data['AwardSplitWork'],
            'Possible Allocation'   => $data['AwardAllocation'],
            'Matching Requirements' => $data['AwardL2L3Matching']
        ]);

        // 8. PerformanceGuarantee
        $data['PerformanceGuarantee'] = $serKV([
            'Amount'        => !empty($data['PbgAmount']) ? $data['PbgAmount'] : (!empty($data['PbgPercentage']) ? $data['PbgPercentage'] . '%' : ''),
            'Validity'      => $data['PbgValidity'],
            'Submission'    => $data['PbgSubmissionDeadline']
        ]);

        // 9. BidValidity
        $data['BidValidity'] = $serKV([
            'Duration' => !empty($data['BidValidityDuration']) ? $data['BidValidityDuration'] : $data['BidValidityDays']
        ]);

        // 10. Warranty
        $data['Warranty'] = $serKV([
            'Scope'         => $data['WarrantyAmc'],
            'Terms'         => $data['WarrantyTerms'],
            'Replacement'   => $data['WarrantyReplacement'],
            'Repair'        => $data['WarrantyRepair'],
            'Support'       => $data['WarrantySupport'],
            'AMC Terms'     => $data['AmcTerms']
        ]);

        // 11. RiskPoints
        $data['RiskPoints'] = $serList($risks, true);

        // 12. OpportunityAssessment
        $oaKV = [];
        if (!empty($data['PrebidQueryNotes'])) {
            $oaKV['Pre-Bid Queries'] = $data['PrebidQueryNotes'];
        }
        $idx = 1;
        foreach ($assessmentItems as $item) {
            $oaKV["Key Attention Area {$idx}"] = $item;
            $idx++;
        }
        $data['OpportunityAssessment'] = $serKV($oaKV);

        return $data;
    }

    /**
     * Repairs common PDF kerning and ligature extraction glitches
     */
    protected function _repairFactSheetLigatures($text)
    {
        $replacements = [
            '/\bPar\s*c\s*ular\b/i'     => 'Particular',
            '/\bAc\s*v\s*ity\b/i'       => 'Activity',
            '/\bEs\s*mated\b/i'         => 'Estimated',
            '/\bInstalla\s*on\b/i'      => 'Installation',
            '/\bTes\s*ng\b/i'           => 'Testing',
            '/\bPreven\s*ve\b/i'        => 'Preventive',
            '/\bCorrec\s*ve\b/i'        => 'Corrective',
            '/\bIni\s*al\b/i'           => 'Initial',
            '/\bAddi\s*on\s*al\b/i'     => 'Additional',
            '/\bAddi\s*onal\b/i'        => 'Additional',
            '/\bin\s*mated\b/i'         => 'intimated',
            '/\bsa\s*sfactory\b/i'      => 'satisfactory',
            '/\balloca\s*on\b/i'        => 'allocation',
            '/\brejec\s*on\b/i'         => 'rejection',
            '/\bauthoriza\s*on\b/i'     => 'authorization',
            '/\bspeci[^\s]*ca\s*ons\b/i'=> 'specifications',
            '/\bMee\s*ng\b/i'           => 'Meeting',
            '/\ble\s*ers\b/i'           => 'letters',
            '/\ble\s*er\b/i'            => 'letter',
            '/\bA\s*en\s*on\b/i'        => 'Attention',
            '/\bop\s*ons\b/i'           => 'options',
            '/\bmul\s*ple\b/i'          => 'multiple',
            '/\bExis\s*ng\b/i'          => 'Existing',
            '/\bCondi\s*onal\b/i'       => 'Conditional',
        ];
        foreach ($replacements as $pat => $rep) {
            $text = preg_replace($pat, $rep, $text);
        }
        return $text;
    }
}

