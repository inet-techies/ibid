<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_parser extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form', 'file', 'cookie', 'string']);
        $this->load->library('session');
        $this->load->database();
        $this->load->model('bid/bid_model');
        date_default_timezone_set("Asia/Kolkata");

        // Load document extractor library from application/libraries/
        if (file_exists(APPPATH . 'libraries/Document_extractor.php')) {
            require_once APPPATH . 'libraries/Document_extractor.php';
            $this->document_extractor = new Document_extractor();
        } else {
            $this->load->library('document_extractor');
        }

        // Initialize role-based menus following the bid module structure
        $check_session = $this->session->userdata('logged_in');
        if (!empty($check_session) && isset($check_session['RoleId'])) {
            $menus = $this->bid_model->getMenusByRole($check_session['RoleId']);
            $menuTree = [];
            foreach ($menus as $menu) {
                if ($menu['ParentId'] === NULL) {
                    $menuTree[$menu['Bmid']] = $menu;
                    $menuTree[$menu['Bmid']]['children'] = [];
                }
            }
            foreach ($menus as $menu) {
                if ($menu['ParentId'] !== NULL && isset($menuTree[$menu['ParentId']])) {
                    $menuTree[$menu['ParentId']]['children'][] = $menu;
                }
            }
            $this->load->vars('menuTree', $menuTree);
        }
    }

    /**
     * Follows bid module session validation and base_url redirection
     * Route: GET /document_parser
     */
    public function index()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {
            redirect($this->config->item('base_url') . "bid/CreateBids");
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Legacy widget alias - redirects to main bid module with session check
     */
    public function widget($config = [])
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {
            redirect($this->config->item('base_url') . "bid/CreateBids");
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Styled Upload File Button
     */
    public function upload_button($config = [])
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {
            if (empty($config)) {
                redirect($this->config->item('base_url') . "bid/CreateBids");
                return;
            }

            $data = [
                'button_text'  => isset($config['button_text']) ? $config['button_text'] : 'Upload File',
                'button_class' => isset($config['button_class']) ? $config['button_class'] : '',
            ];

            return $this->load->view('document_parser/upload_button', $data, true);
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Alias for upload_button
     */
    public function button()
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {
            redirect($this->config->item('base_url') . "bid/CreateBids");
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Legacy workbench alias - redirects to main bid module
     */
    public function workbench($config = [])
    {
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {
            redirect($this->config->item('base_url') . "bid/CreateBids");
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Main AJAX file upload & extraction endpoint
     * Route: POST /document_parser/extract
     */
    public function extract()
    {
        @ini_set('display_errors', '0');
        error_reporting(0);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();

        // Validate user session following the bid module authentication structure
        $Bid_Session = $this->session->userdata('logged_in');
        if (isset($Bid_Session) && !empty($Bid_Session)) {

        // Validate upload existence & errors
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'Please select a valid PDF, Word (.docx, .doc), RTF, or text document to upload.';
            if (isset($_FILES['file']['error'])) {
                switch ($_FILES['file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg = 'The uploaded file exceeds the server maximum file size limit.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMsg = 'No file was selected for upload.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMsg = 'The file was only partially uploaded. Please try again.';
                        break;
                    default:
                        $errorMsg = 'Upload failed with error code: ' . $_FILES['file']['error'];
                }
            }

            $this->_jsonResponse([
                'status'                => 'error',
                'success'               => false,
                'title'                 => '',
                'document_title'        => '',
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => $errorMsg
            ]);
        }

        $uploadedFile = $_FILES['file'];
        $origName     = $uploadedFile['name'];
        $tmpPath      = $uploadedFile['tmp_name'];
        $fileSize     = (int)$uploadedFile['size'];

        // Safety limit: 200MB ceiling
        if ($fileSize > 200 * 1024 * 1024) {
            $this->_jsonResponse([
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'File too large (max 200 MB). Please upload a smaller document.'
            ]);
        }

        // Validate extension
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'docx', 'doc', 'txt', 'rtf'];
        if (!in_array($ext, $allowedExts)) {
            $this->_jsonResponse([
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'Unsupported file format (.' . htmlspecialchars($ext) . '). Supported: PDF, DOCX, DOC, RTF, TXT.'
            ]);
        }

        // Ensure target directories exist (single uploads/tender/ directory)
        $tenderDir = FCPATH . 'uploads/tender/';
        $oemDir    = FCPATH . 'uploads/oem/';
        $servDir   = FCPATH . 'uploads/service/';

        foreach ([$tenderDir, $oemDir, $servDir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        // Generate filesystem-safe, path-traversal-free filename for uploads/tender/
        $rawBase  = pathinfo($origName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $rawBase);
        $safeBase = trim($safeBase, '_');
        if (empty($safeBase)) $safeBase = 'document';
        if (strlen($safeBase) > 50) $safeBase = substr($safeBase, 0, 50);

        $safeName   = time() . '_' . $safeBase . '.' . $ext;
        $targetPath = $tenderDir . $safeName;
        $counter    = 1;
        while (file_exists($targetPath)) {
            $safeName   = time() . '_' . $safeBase . '_' . $counter . '.' . $ext;
            $targetPath = $tenderDir . $safeName;
            $counter++;
        }

        // Store original uploaded document into uploads/tender/
        if (!@copy($tmpPath, $targetPath)) {
            $this->_jsonResponse([
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'Failed to save uploaded file to tender repository.'
            ]);
        }

        $relPath = 'uploads/tender/' . $safeName;

        // Run multi-tier extraction and intelligence pipeline
        try {
            $result = $this->document_extractor->extract($targetPath, $origName, $ext);

            $result['file_path']      = $relPath;
            $result['file_name']      = $origName;
            $result['file_full_path'] = $targetPath;

            if (isset($result['status']) && $result['status'] === 'success') {
                $result['success'] = true;
                $classification = isset($result['classification']) ? strtolower($result['classification']) : 'unknown';

                $title = !empty($result['title']) ? $result['title'] : (!empty($result['ai_summary']['title']) ? $result['ai_summary']['title'] : $origName);
                $result['title']          = $title;
                $result['document_title'] = $title;

                // Document's first non-empty line for Bid Title
                if (empty($result['first_line'])) {
                    $rawText = !empty($result['formatted_text']) ? $result['formatted_text'] : (!empty($result['extracted_text']) ? $result['extracted_text'] : '');
                    foreach (preg_split('/[\r\n]+/', $rawText) as $l) {
                        $l = trim($l);
                        if ($l !== '' && !preg_match('/^(?:page\s*\d+|\d+)$/i', $l)) {
                            $result['first_line'] = preg_replace('/\s+/', ' ', $l);
                            break;
                        }
                    }
                }
                if (empty($result['first_line'])) {
                    $result['first_line'] = $title;
                }
                $result['bid_title'] = $result['first_line'];
                if (isset($result['ai_summary'])) {
                    $result['ai_summary']['first_line'] = $result['first_line'];
                    $result['ai_summary']['bid_title']  = $result['first_line'];
                }

                // Handle classification and save extracted .txt file
                if ($classification === 'oem' || $classification === 'service') {
                    $txtRelPath = $this->document_extractor->saveExtractedText(
                        isset($result['extracted_text']) ? $result['extracted_text'] : '',
                        $classification,
                        $origName
                    );

                    $result['classification']        = $classification;
                    $result['classification_status'] = 'classified';
                    $result['txt_file']              = $txtRelPath;
                } else {
                    // Unclassified / unknown: original stays in uploads/tender/, do NOT save to oem/ or service/
                    $result['classification']        = 'unknown';
                    $result['classification_status'] = 'unclassified';
                    $result['txt_file']              = null;
                }

                // Extract and persist structured 12-section Fact Sheet into single table TenderFactSheet
                try {
                    $textForFs = !empty($result['formatted_text']) ? $result['formatted_text'] : (!empty($result['extracted_text']) ? $result['extracted_text'] : '');
                    $factSheetData = $this->document_extractor->extractFactSheet(
                        $textForFs,
                        $origName,
                        isset($result['ai_summary']) ? $result['ai_summary'] : []
                    );
                    $factSheetData['DocumentPath']         = $relPath;
                    $factSheetData['DocumentName']         = $origName;
                    $factSheetData['DocumentHash']         = @md5_file($targetPath);
                    $factSheetData['TxtFilePath']          = isset($result['txt_file']) ? $result['txt_file'] : null;
                    $factSheetData['Classification']       = $result['classification'];
                    $factSheetData['ClassificationStatus'] = $result['classification_status'];

                    // Evidence Extraction & Linking
                    try {
                        $pageMap = $this->document_extractor->extractPageMap($targetPath);
                        if (!empty($pageMap)) {
                            $this->load->library('factsheet_evidence_service');
                            $evidenceRecords = $this->factsheet_evidence_service->collectEvidence($factSheetData, $pageMap, [
                                'document_path' => $relPath,
                                'document_hash' => $factSheetData['DocumentHash']
                            ]);
                            $factSheetData['evidence_records'] = $evidenceRecords;
                        }
                    } catch (\Throwable $eve) {
                        log_message('error', 'Evidence extraction error: ' . $eve->getMessage());
                    }

                    $this->load->model('document_parser/Factsheet_model');
                    $tfsid = $this->Factsheet_model->saveFactSheet($factSheetData);

                    if ($tfsid) {
                        $result['tfsid']         = $tfsid;
                        $result['fsid']          = $tfsid;
                        $result['factsheet_id']  = $tfsid;
                        $result['factsheet_url'] = site_url('bid/factsheet?id=' . $tfsid);
                    }
                } catch (\Throwable $fse) {
                    log_message('error', 'Fact sheet generation error: ' . $fse->getMessage());
                }
            } else {
                $result['success']               = false;
                $result['title']                 = htmlspecialchars($origName);
                $result['document_title']        = htmlspecialchars($origName);
                $result['classification']        = 'unknown';
                $result['classification_status'] = 'unclassified';
                $result['txt_file']              = null;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Document extraction exception: ' . $e->getMessage());
            $result = [
                'status'                => 'error',
                'success'               => false,
                'title'                 => htmlspecialchars($origName),
                'document_title'        => htmlspecialchars($origName),
                'classification'        => 'unknown',
                'classification_status' => 'unclassified',
                'txt_file'              => null,
                'message'               => 'Document extraction exception: ' . $e->getMessage(),
                'file_path'             => $relPath,
                'file_name'             => $origName,
                'file_full_path'        => $targetPath
            ];
        }

        $this->_jsonResponse($result);
        } else {
            $this->session->set_flashdata('err', 'Invalid Session.Please Login Again..!!');
            redirect($this->config->item('base_url') . "bid/index");
        }
    }

    /**
     * Clean JSON response helper
     */
    private function _jsonResponse($payload)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}
