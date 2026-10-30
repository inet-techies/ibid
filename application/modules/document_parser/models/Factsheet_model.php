<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Factsheet_model
 *
 * Dedicated model for managing Tender Fact Sheet records in the single `TenderFactSheet` table.
 * Stores Fact Sheet data according to the 12 main headings in the reference PDF,
 * with one dedicated database column per section using safely encoded JSON.
 *
 * 12 Main Sections:
 *   1. TenderOverview         (JSON object of general tender metadata)
 *   2. ImportantDates         (JSON object of activity => date pairs)
 *   3. TenderValue            (JSON object of value, emd, pbg, validity)
 *   4. ScopeOfWork            (JSON array of work deliverables)
 *   5. AreasCovered           (JSON object of region => coverage description)
 *   6. ContractPeriod         (JSON object of duration, extension parameters)
 *   7. AwardCriteria          (JSON object of evaluation & splitting criteria)
 *   8. PerformanceGuarantee  (JSON object of guarantee details)
 *   9. BidValidity            (JSON object of validity duration)
 *   10. Warranty              (JSON object of warranty and AMC terms)
 *   11. RiskPoints            (JSON array of operational risks / SLA items)
 *   12. OpportunityAssessment (JSON object of assessment & query notes)
 */
class Factsheet_model extends CI_Model
{
    protected $table = 'tenderfactsheet';

    public function __construct()
    {
        parent::__construct();
        if (isset($this->load) && !isset($this->db)) {
            $this->load->database();
        }
    }


    /**
     * Serializes an associative array of key-value pairs into safely encoded JSON.
     *
     * @param array $items
     * @return string JSON representation
     */
    public static function serializeKeyValue(array $items)
    {
        $filtered = [];
        foreach ($items as $k => $v) {
            $vStr = trim((string)$v);
            if ($vStr === '') continue;
            $filtered[trim((string)$k)] = $vStr;
        }
        return json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Deserializes a stored section column back into an associative array.
     * Decodes JSON safely; falls back to legacy pipe delimiter if an old row is encountered.
     *
     * @param mixed $raw
     * @return array
     */
    public static function deserializeKeyValue($raw)
    {
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;

        $rawStr = trim((string)$raw);
        if ($rawStr === '') return [];

        // 1. Try standard JSON decode
        $decoded = json_decode($rawStr, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // 2. Fallback: Legacy "Label: Value | Label: Value" pipe-delimited parser
        $out = [];
        $segments = preg_split('/(?<!\\\\)\s*\|\s*/', $rawStr);
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (empty($seg)) continue;
            $colonPos = strpos($seg, ': ');
            if ($colonPos !== false) {
                $label = trim(substr($seg, 0, $colonPos));
                $val   = trim(substr($seg, $colonPos + 2));
                $val   = str_replace('\|', '|', $val);
                $out[$label] = $val;
            } else {
                $out[] = str_replace('\|', '|', $seg);
            }
        }
        return $out;
    }

    /**
     * Serializes a list of items into safely encoded JSON array.
     *
     * @param array $items
     * @return string JSON array representation
     */
    public static function serializeList(array $items)
    {
        $cleanItems = [];
        foreach ($items as $item) {
            $itemStr = trim((string)$item);
            if ($itemStr === '') continue;
            // Clean leading numbering, bullets, wingdings
            $clean = preg_replace('/^[\s*•\-·∩é╖\x{F0B7}\x{00B7}\.o\d\)]+/u', '', $itemStr);
            $clean = trim($clean);
            if (!empty($clean)) {
                $cleanItems[] = $clean;
            }
        }
        return json_encode(array_values($cleanItems), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Deserializes a stored list column back into an indexed array of items.
     * Decodes JSON array safely; falls back to legacy pipe-delimited parser if an old row is encountered.
     *
     * @param mixed $raw
     * @return array
     */
    public static function deserializeList($raw)
    {
        if (empty($raw)) return [];
        if (is_array($raw)) return array_values($raw);

        $rawStr = trim((string)$raw);
        if ($rawStr === '') return [];

        // 1. Try standard JSON decode
        $decoded = json_decode($rawStr, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values($decoded);
        }

        // 2. Fallback: Legacy "1. Item | 2. Item" pipe-delimited parser
        $out = [];
        $segments = preg_split('/(?<!\\\\)\s*\|\s*/', $rawStr);
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if (empty($seg)) continue;
            $clean = preg_replace('/^\d+[\.\)]\s*/', '', $seg);
            $clean = preg_replace('/^[\s*•\-·∩é╖\x{F0B7}\x{00B7}\.o\d\)]+/u', '', $clean);
            $clean = trim(str_replace('\|', '|', $clean));
            if (!empty($clean)) {
                $out[] = $clean;
            }
        }
        return $out;
    }


    /**
     * Checks if a string is a valid JSON array or object.
     *
     * @param mixed $str
     * @return bool
     */
    public static function isJsonString($str)
    {
        if (!is_string($str)) return false;
        $trim = trim($str);
        if ($trim === '' || ($trim[0] !== '{' && $trim[0] !== '[')) return false;
        json_decode($trim);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Converts a structured or attribute-based payload into the 12 main section column format.
     * Every section is safely encoded as JSON.
     *
     * @param array $data Fact Sheet attributes
     * @return array Array with 12 section columns encoded as JSON
     */
    public function buildSectionPayload(array $data)
    {
        // 1. TenderOverview (JSON object)
        if (empty($data['TenderOverview']) || !self::isJsonString($data['TenderOverview'])) {
            if (!empty($data['TenderOverview']) && is_string($data['TenderOverview']) && strpos($data['TenderOverview'], ': ') !== false) {
                $data['TenderOverview'] = self::serializeKeyValue(self::deserializeKeyValue($data['TenderOverview']));
            } else {
                $overviewKV = [
                    'Department'            => isset($data['Department']) ? $data['Department'] : '',
                    'Zone'                  => isset($data['ZoneRegion']) ? $data['ZoneRegion'] : '',
                    'Tender Title'          => isset($data['TenderTitle']) ? $data['TenderTitle'] : '',
                    'Tender Type'           => isset($data['TenderType']) ? $data['TenderType'] : '',
                    'Tender Reference No'   => isset($data['TenderRefNo']) ? $data['TenderRefNo'] : '',
                    'Tender Date'           => isset($data['TenderDate']) ? $data['TenderDate'] : '',
                    'Project Value'         => isset($data['ProjectValue']) ? $data['ProjectValue'] : (isset($data['EstimatedValue']) ? $data['EstimatedValue'] : ''),
                    'Contract Type'         => isset($data['ContractType']) ? $data['ContractType'] : '',
                    'Warranty + AMC'        => isset($data['WarrantyAmc']) ? $data['WarrantyAmc'] : '',
                    'Bid System'            => isset($data['BidSystem']) ? $data['BidSystem'] : ''
                ];
                $data['TenderOverview'] = self::serializeKeyValue($overviewKV);
            }
        }

        // 2. ImportantDates (JSON object)
        if (empty($data['ImportantDates']) || !self::isJsonString($data['ImportantDates'])) {
            if (!empty($data['ImportantDates']) && is_string($data['ImportantDates']) && strpos($data['ImportantDates'], ': ') !== false) {
                $data['ImportantDates'] = self::serializeKeyValue(self::deserializeKeyValue($data['ImportantDates']));
            } else {
                $datesKV = [];
                if (!empty($data['ImportantDatesJson'])) {
                    $arr = json_decode($data['ImportantDatesJson'], true);
                    if (is_array($arr)) {
                        foreach ($arr as $d) {
                            if (!empty($d['activity']) && !empty($d['date_text'])) {
                                $datesKV[$d['activity']] = $d['date_text'];
                            }
                        }
                    }
                }
                if (empty($datesKV)) {
                    if (!empty($data['TenderPublishedDate']))   $datesKV['Tender Published'] = $data['TenderPublishedDate'];
                    if (!empty($data['TenderDownloadPeriod']))  $datesKV['Tender Download'] = $data['TenderDownloadPeriod'];
                    if (!empty($data['PreBidQueryDeadline']))   $datesKV['Pre-Bid Query Submission'] = $data['PreBidQueryDeadline'];
                    if (!empty($data['PreBidMeeting']))          $datesKV['Pre-Bid Meeting'] = $data['PreBidMeeting'];
                    if (!empty($data['BidSubmissionDeadline']))  $datesKV['Bid Submission'] = $data['BidSubmissionDeadline'];
                    if (!empty($data['TechnicalBidOpening']))    $datesKV['Technical Bid Opening'] = $data['TechnicalBidOpening'];
                    if (!empty($data['FinancialBidOpening']))    $datesKV['Financial Bid Opening'] = $data['FinancialBidOpening'];
                }
                $data['ImportantDates'] = self::serializeKeyValue($datesKV);
            }
        }

        // 3. TenderValue (JSON object)
        if (empty($data['TenderValue']) || !self::isJsonString($data['TenderValue'])) {
            if (!empty($data['TenderValue']) && is_string($data['TenderValue']) && strpos($data['TenderValue'], ': ') !== false) {
                $data['TenderValue'] = self::serializeKeyValue(self::deserializeKeyValue($data['TenderValue']));
            } else {
                $valKV = [
                    'Tender Fee'                    => isset($data['TenderFee']) ? $data['TenderFee'] : '',
                    'EMD'                           => isset($data['EmdAmount']) ? $data['EmdAmount'] : '',
                    'Performance Bank Guarantee'    => isset($data['PbgDetails']) ? $data['PbgDetails'] : (isset($data['PbgAmount']) ? $data['PbgAmount'] : ''),
                    'Bid Validity'                  => isset($data['BidValidityDays']) ? $data['BidValidityDays'] : (isset($data['BidValidityDuration']) ? $data['BidValidityDuration'] : '')
                ];
                if (!empty($data['EstimatedValue']) && $data['EstimatedValue'] !== 'Not specified in document') {
                    $valKV['Estimated Value'] = $data['EstimatedValue'];
                }
                $data['TenderValue'] = self::serializeKeyValue($valKV);
            }
        }

        // 4. ScopeOfWork (JSON array)
        if (empty($data['ScopeOfWork']) || !self::isJsonString($data['ScopeOfWork'])) {
            $scopesArr = [];
            if (!empty($data['ScopeItemsJson'])) {
                $scopesArr = json_decode($data['ScopeItemsJson'], true);
            }
            if (empty($scopesArr) && !empty($data['ScopeOfWork'])) {
                if (is_array($data['ScopeOfWork'])) {
                    $scopesArr = $data['ScopeOfWork'];
                } elseif (strpos($data['ScopeOfWork'], ' | ') !== false) {
                    $scopesArr = self::deserializeList($data['ScopeOfWork']);
                } else {
                    $scopesArr = preg_split('/[\r\n]+|[•·*∩é╖]|\x{F0B7}|\x{00B7}/u', $data['ScopeOfWork']);
                }
            }
            if (!empty($scopesArr) && is_array($scopesArr)) {
                $data['ScopeOfWork'] = self::serializeList($scopesArr);
            } else {
                $data['ScopeOfWork'] = json_encode([], JSON_UNESCAPED_UNICODE);
            }
        }

        // 5. AreasCovered (JSON object)
        if (empty($data['AreasCovered']) || !self::isJsonString($data['AreasCovered'])) {
            if (!empty($data['AreasCovered']) && is_string($data['AreasCovered']) && strpos($data['AreasCovered'], ' | ') !== false && strpos($data['AreasCovered'], ': ') !== false) {
                $data['AreasCovered'] = self::serializeKeyValue(self::deserializeKeyValue($data['AreasCovered']));
            } else {
                $areasKV = [];
                if (!empty($data['AreasCoveredJson'])) {
                    $arr = json_decode($data['AreasCoveredJson'], true);
                    if (is_array($arr)) {
                        foreach ($arr as $a) {
                            if (!empty($a['region']) && !empty($a['coverage'])) {
                                if (strtolower(trim($a['region'])) !== 'region') {
                                    $areasKV[$a['region']] = $a['coverage'];
                                }
                            }
                        }
                    }
                }
                if (empty($areasKV) && !empty($data['AreasCovered'])) {
                    $lines = preg_split('/[\r\n]+/', $data['AreasCovered']);
                    foreach ($lines as $ln) {
                        $p = explode(':', $ln, 2);
                        if (count($p) == 2 && strtolower(trim($p[0])) !== 'region') {
                            $areasKV[trim($p[0])] = trim($p[1]);
                        }
                    }
                }
                $data['AreasCovered'] = self::serializeKeyValue($areasKV);
            }
        }

        // 6. ContractPeriod (JSON object)
        if (empty($data['ContractPeriod']) || !self::isJsonString($data['ContractPeriod'])) {
            if (!empty($data['ContractPeriod']) && is_string($data['ContractPeriod']) && strpos($data['ContractPeriod'], ': ') !== false) {
                $data['ContractPeriod'] = self::serializeKeyValue(self::deserializeKeyValue($data['ContractPeriod']));
            } else {
                $cpKV = [
                    'Initial Contract'      => isset($data['ContractInitialDuration']) ? $data['ContractInitialDuration'] : '',
                    'Extendable'            => isset($data['ContractExtendable']) ? $data['ContractExtendable'] : '',
                    'Maximum Period'        => isset($data['ContractMaxPeriod']) ? $data['ContractMaxPeriod'] : '',
                    'Extension Conditions'  => isset($data['ContractExtensionConditions']) ? $data['ContractExtensionConditions'] : ''
                ];
                $data['ContractPeriod'] = self::serializeKeyValue($cpKV);
            }
        }

        // 7. AwardCriteria (JSON object)
        if (empty($data['AwardCriteria']) || !self::isJsonString($data['AwardCriteria'])) {
            if (!empty($data['AwardCriteria']) && is_string($data['AwardCriteria']) && strpos($data['AwardCriteria'], ': ') !== false) {
                $data['AwardCriteria'] = self::serializeKeyValue(self::deserializeKeyValue($data['AwardCriteria']));
            } else {
                $acKV = [
                    'Evaluation Method'     => isset($data['AwardEvaluationMethod']) ? $data['AwardEvaluationMethod'] : '',
                    'Split Work'            => isset($data['AwardSplitWork']) ? $data['AwardSplitWork'] : '',
                    'Possible Allocation'   => isset($data['AwardAllocation']) ? $data['AwardAllocation'] : '',
                    'Matching Requirements' => isset($data['AwardL2L3Matching']) ? $data['AwardL2L3Matching'] : ''
                ];
                $data['AwardCriteria'] = self::serializeKeyValue($acKV);
            }
        }

        // 8. PerformanceGuarantee (JSON object)
        if (empty($data['PerformanceGuarantee']) || !self::isJsonString($data['PerformanceGuarantee'])) {
            if (!empty($data['PerformanceGuarantee']) && is_string($data['PerformanceGuarantee']) && strpos($data['PerformanceGuarantee'], ': ') !== false) {
                $data['PerformanceGuarantee'] = self::serializeKeyValue(self::deserializeKeyValue($data['PerformanceGuarantee']));
            } else {
                $pgKV = [
                    'Amount'        => isset($data['PbgAmount']) ? $data['PbgAmount'] : (isset($data['PbgPercentage']) ? $data['PbgPercentage'] . '%' : ''),
                    'Validity'      => isset($data['PbgValidity']) ? $data['PbgValidity'] : '',
                    'Submission'    => isset($data['PbgSubmissionDeadline']) ? $data['PbgSubmissionDeadline'] : ''
                ];
                $data['PerformanceGuarantee'] = self::serializeKeyValue($pgKV);
            }
        }

        // 9. BidValidity (JSON object)
        if (empty($data['BidValidity']) || !self::isJsonString($data['BidValidity'])) {
            if (!empty($data['BidValidity']) && is_string($data['BidValidity']) && strpos($data['BidValidity'], ': ') !== false) {
                $data['BidValidity'] = self::serializeKeyValue(self::deserializeKeyValue($data['BidValidity']));
            } else {
                $bvKV = [
                    'Duration' => isset($data['BidValidityDuration']) ? $data['BidValidityDuration'] : (isset($data['BidValidityDays']) ? $data['BidValidityDays'] : '')
                ];
                $data['BidValidity'] = self::serializeKeyValue($bvKV);
            }
        }

        // 10. Warranty (JSON object)
        if (empty($data['Warranty']) || !self::isJsonString($data['Warranty'])) {
            if (!empty($data['Warranty']) && is_string($data['Warranty']) && strpos($data['Warranty'], ': ') !== false) {
                $data['Warranty'] = self::serializeKeyValue(self::deserializeKeyValue($data['Warranty']));
            } else {
                $wKV = [
                    'Scope'         => isset($data['WarrantyAmc']) ? $data['WarrantyAmc'] : '',
                    'Terms'         => isset($data['WarrantyTerms']) ? $data['WarrantyTerms'] : '',
                    'Replacement'   => isset($data['WarrantyReplacement']) ? $data['WarrantyReplacement'] : '',
                    'Repair'        => isset($data['WarrantyRepair']) ? $data['WarrantyRepair'] : '',
                    'Support'       => isset($data['WarrantySupport']) ? $data['WarrantySupport'] : '',
                    'AMC Terms'     => isset($data['AmcTerms']) ? $data['AmcTerms'] : ''
                ];
                $data['Warranty'] = self::serializeKeyValue($wKV);
            }
        }

        // 11. RiskPoints (JSON array)
        if (empty($data['RiskPoints']) || !self::isJsonString($data['RiskPoints'])) {
            $risksArr = [];
            if (!empty($data['RiskPointsJson'])) {
                $risksArr = json_decode($data['RiskPointsJson'], true);
            }
            if (empty($risksArr) && !empty($data['RiskPoints'])) {
                if (is_array($data['RiskPoints'])) {
                    $risksArr = $data['RiskPoints'];
                } elseif (strpos($data['RiskPoints'], ' | ') !== false) {
                    $risksArr = self::deserializeList($data['RiskPoints']);
                } else {
                    $risksArr = preg_split('/[\r\n]+|[•·*∩é╖]|\x{F0B7}|\x{00B7}/u', $data['RiskPoints']);
                }
            }
            if (!empty($risksArr) && is_array($risksArr)) {
                $data['RiskPoints'] = self::serializeList($risksArr);
            } else {
                $data['RiskPoints'] = json_encode([], JSON_UNESCAPED_UNICODE);
            }
        }

        // 12. OpportunityAssessment (JSON object)
        if (empty($data['OpportunityAssessment']) || !self::isJsonString($data['OpportunityAssessment'])) {
            if (!empty($data['OpportunityAssessment']) && is_string($data['OpportunityAssessment']) && strpos($data['OpportunityAssessment'], ': ') !== false) {
                $data['OpportunityAssessment'] = self::serializeKeyValue(self::deserializeKeyValue($data['OpportunityAssessment']));
            } else {
                $oaKV = [];
                if (!empty($data['PrebidQueryNotes'])) {
                    $oaKV['Pre-Bid Queries'] = $data['PrebidQueryNotes'];
                }
                if (!empty($data['OpportunityAssessmentJson'])) {
                    $arr = json_decode($data['OpportunityAssessmentJson'], true);
                    if (is_array($arr)) {
                        $idx = 1;
                        foreach ($arr as $item) {
                            $oaKV["Key Attention Area {$idx}"] = $item;
                            $idx++;
                        }
                    }
                }
                if (empty($oaKV) && !empty($data['OpportunityAssessment'])) {
                    $oaKV['Assessment'] = $data['OpportunityAssessment'];
                }
                $data['OpportunityAssessment'] = self::serializeKeyValue($oaKV);
            }
        }

        return $data;
    }

    /**
     * Inserts a new Fact Sheet record or updates an existing one when re-processed.
     * Persists section-based JSON data in the 12 main section columns, avoiding
     * individual column proliferation while maintaining core metadata and legacy indexing fields.
     *
     * @param array $data Fact Sheet attributes
     * @return int|false The Fact Sheet ID (Tfsid)
     */
    public function saveFactSheet(array $data)
    {
        if (empty($data['DocumentPath']) && empty($data['DocumentName'])) {
            return false;
        }

        // Build the 12 main section column values as JSON
        $data = $this->buildSectionPayload($data);

        if (!empty($data['evidence_records']) && is_array($data['evidence_records'])) {
            $data['EvidenceJson'] = json_encode($data['evidence_records'], JSON_UNESCAPED_UNICODE);
        }

        // Allowed database columns: metadata + 12 sections + required legacy indexing fields
        $allowedFields = [
            // Metadata & Keys
            'Tfsid', 'Bcid', 'DocumentPath', 'DocumentName', 'DocumentHash',
            'TxtFilePath', 'Classification', 'ClassificationStatus', 'CreatedBy',

            // 12 Section Columns (Grouped JSON storage)
            'TenderOverview',
            'ImportantDates',
            'TenderValue',
            'ScopeOfWork',
            'AreasCovered',
            'ContractPeriod',
            'AwardCriteria',
            'PerformanceGuarantee',
            'BidValidity',
            'Warranty',
            'RiskPoints',
            'OpportunityAssessment',
            'EvidenceJson',
            'TimelineJson',

            // Legacy Indexing Columns (Preserved for compatibility and fast lookup)
            'Department', 'ZoneRegion', 'TenderTitle', 'TenderType', 'TenderRefNo',
            'TenderDate', 'ProjectValue', 'ProjectValueAmount', 'ContractType',
            'WarrantyAmc', 'BidSystem'
        ];

        $savePayload = [];
        foreach ($allowedFields as $f) {
            if (array_key_exists($f, $data)) {
                $savePayload[$f] = $data[$f];
            }
        }

        $existing = null;
        if (!empty($savePayload['DocumentHash'])) {
            $existing = $this->db->get_where($this->table, ['DocumentHash' => $savePayload['DocumentHash']])->row_array();
        }
        if (!$existing && !empty($savePayload['DocumentPath'])) {
            $existing = $this->db->get_where($this->table, ['DocumentPath' => $savePayload['DocumentPath']])->row_array();
        }

        $this->db->trans_start();

        if ($existing) {
            $tfsid = (int)$existing['Tfsid'];
            if (empty($savePayload['Bcid']) && !empty($existing['Bcid'])) {
                $savePayload['Bcid'] = $existing['Bcid'];
            }
            $this->db->where('Tfsid', $tfsid);
            $this->db->update($this->table, $savePayload);
        } else {
            $this->db->insert($this->table, $savePayload);
            $tfsid = (int)$this->db->insert_id();
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Factsheet_model::saveFactSheet database transaction failed.');
            return false;
        }

        if ($tfsid > 0 && !empty($data['evidence_records']) && is_array($data['evidence_records'])) {
            $this->saveEvidenceRecords($tfsid, $data['evidence_records'], $savePayload['DocumentPath'] ?? null, $savePayload['DocumentHash'] ?? null);
        }

        return $tfsid;
    }

    /**
     * Retrieves a Fact Sheet by its primary key Tfsid
     */
    public function getFactSheetById($id)
    {
        return $this->db->get_where($this->table, ['Tfsid' => (int)$id])->row_array();
    }

    /**
     * Generates or synchronizes a Fact Sheet record directly from a saved Bid (BidCreationDetails).
     * Ensures that the Fact Sheet resembles and perfectly matches all details of the saved bid.
     *
     * @param int $bcid Bid creation ID
     * @param array $bidData Optional bid data array (if already loaded)
     * @return array|false The synchronized Fact Sheet record
     */
    public function generateOrSyncFactsheetForBid($bcid, array $bidData = [])
    {
        $bcid = (int)$bcid;
        if ($bcid <= 0) {
            return false;
        }

        if (empty($bidData)) {
            $bid = $this->db->get_where('BidCreationDetails', ['Bcid' => $bcid])->row_array();
        } else {
            $bid = $bidData;
            $bid['Bcid'] = $bcid;
        }

        if (empty($bid)) {
            return false;
        }

        // 1. Locate existing Fact Sheet record for this bid
        $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['Bcid' => $bcid])->row_array();
        if (!$fs && !empty($bid['NASPath'])) {
            $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['DocumentPath' => $bid['NASPath']])->row_array();
        }
        if (!$fs && !empty($bid['TenderId'])) {
            $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['TenderRefNo' => $bid['TenderId']])->row_array();
        }

        // If still no Fact Sheet, but a valid document file exists at NASPath, extract it first
        if (!$fs && !empty($bid['NASPath'])) {
            $fullDocPath = FCPATH . ltrim($bid['NASPath'], '/\\');
            if (file_exists($fullDocPath)) {
                if (file_exists(APPPATH . 'libraries/Document_extractor.php')) {
                    require_once APPPATH . 'libraries/Document_extractor.php';
                    $extractor = new Document_extractor();
                    $extractedFs = $extractor->extractFactSheet($fullDocPath);
                    if (!empty($extractedFs) && is_array($extractedFs)) {
                        $extractedFs['DocumentPath'] = $bid['NASPath'];
                        $extractedFs['DocumentName'] = basename($bid['NASPath']);
                        $extractedFs['DocumentHash'] = @md5_file($fullDocPath);
                        $extractedFs['Bcid']         = $bcid;
                        $tfsid = $this->saveFactSheet($extractedFs);
                        if ($tfsid > 0) {
                            $fs = $this->getFactSheetById($tfsid);
                        }
                    }
                }
            }
        }

        // Standardize Bid fields
        $bidTitle = isset($bid['BidTitle']) ? trim((string)$bid['BidTitle']) : '';
        $dept     = isset($bid['BidDepartment']) ? trim((string)$bid['BidDepartment']) : '';
        $bidType  = isset($bid['BidType']) ? trim((string)$bid['BidType']) : '';
        $refNo    = isset($bid['TenderId']) ? trim((string)$bid['TenderId']) : '';
        $estVal   = isset($bid['EstimatedValue']) ? trim((string)$bid['EstimatedValue']) : '';
        $emdVal   = isset($bid['EmdValue']) ? trim((string)$bid['EmdValue']) : '';
        $pubDate  = isset($bid['PublishedDate']) ? trim((string)$bid['PublishedDate']) : '';
        $subDate  = isset($bid['SubmissionDate']) ? trim((string)$bid['SubmissionDate']) : '';
        $opDate   = isset($bid['OpeningDate']) ? trim((string)$bid['OpeningDate']) : '';
        $keywords = isset($bid['SummaryKeywords']) ? trim((string)$bid['SummaryKeywords']) : '';
        $portal   = isset($bid['TenderPortalUrl']) ? trim((string)$bid['TenderPortalUrl']) : '';
        $remarks  = isset($bid['Remarks']) ? trim((string)$bid['Remarks']) : '';
        $nasPath  = isset($bid['NASPath']) ? trim((string)$bid['NASPath']) : '';

        // 2. Build or overlay the 12 main section columns to match Bid details exactly
        
        // ── Section 1: TenderOverview ──
        $overview = !empty($fs['TenderOverview']) ? self::deserializeKeyValue($fs['TenderOverview']) : [];
        if ($bidTitle !== '') $overview['Tender Title'] = $bidTitle;
        if ($dept !== '')     $overview['Department'] = $dept;
        if ($refNo !== '')    $overview['Tender Reference No'] = $refNo;
        if ($bidType !== '' && $bidType !== '0') $overview['Tender Type'] = $bidType;
        if ($pubDate !== '')  $overview['Tender Date'] = $pubDate;
        if ($estVal !== '')   $overview['Project Value'] = $estVal;
        if (empty($overview['Contract Type'])) $overview['Contract Type'] = 'Rate Contract / Supply';
        if (empty($overview['Bid System']))    $overview['Bid System'] = 'Two Bid System';

        // ── Section 2: ImportantDates ──
        $dates = !empty($fs['ImportantDates']) ? self::deserializeKeyValue($fs['ImportantDates']) : [];
        if ($pubDate !== '' && empty($dates['Published Date'])) $dates['Published Date'] = $pubDate;
        if ($subDate !== '') {
            if (empty($dates['Bid Submission Deadline']) || (strpos($dates['Bid Submission Deadline'], ':') === false && strpos($subDate, ':') !== false)) {
                $dates['Bid Submission Deadline'] = $subDate;
            }
        }
        if ($opDate !== '') {
            if (empty($dates['Technical Bid Opening']) || (strpos($dates['Technical Bid Opening'], ':') === false && strpos($opDate, ':') !== false)) {
                $dates['Technical Bid Opening'] = $opDate;
            }
        }

        // ── Section 3: TenderValue ──
        $valSec = !empty($fs['TenderValue']) ? self::deserializeKeyValue($fs['TenderValue']) : [];
        if ($estVal !== '') $valSec['Estimated Value'] = $estVal;
        if ($emdVal !== '') $valSec['EMD'] = $emdVal;

        // ── Section 4: ScopeOfWork ──
        $scopes = !empty($fs['ScopeOfWork']) ? self::deserializeList($fs['ScopeOfWork']) : [];
        if (empty($scopes) && $keywords !== '') {
            $kwItems = preg_split('/[,;\r\n]+/', $keywords);
            foreach ($kwItems as $k) {
                $k = trim($k);
                if (!empty($k)) $scopes[] = $k;
            }
        }
        if (empty($scopes) && $bidTitle !== '') {
            $scopes[] = $bidTitle;
        }

        // ── Section 5: AreasCovered ──
        $areas = !empty($fs['AreasCovered']) ? self::deserializeKeyValue($fs['AreasCovered']) : [];
        if (empty($areas) && $dept !== '') {
            $areas['Jurisdiction'] = $dept;
        }

        // ── Section 6: ContractPeriod ──
        $period = !empty($fs['ContractPeriod']) ? self::deserializeKeyValue($fs['ContractPeriod']) : [];

        // ── Section 7: AwardCriteria ──
        $award = !empty($fs['AwardCriteria']) ? self::deserializeKeyValue($fs['AwardCriteria']) : [];

        // ── Section 8: PerformanceGuarantee ──
        $pbg = !empty($fs['PerformanceGuarantee']) ? self::deserializeKeyValue($fs['PerformanceGuarantee']) : [];

        // ── Section 9: BidValidity ──
        $validity = !empty($fs['BidValidity']) ? self::deserializeKeyValue($fs['BidValidity']) : [];

        // ── Section 10: Warranty ──
        $warranty = !empty($fs['Warranty']) ? self::deserializeKeyValue($fs['Warranty']) : [];

        // ── Section 11: RiskPoints ──
        $risks = !empty($fs['RiskPoints']) ? self::deserializeList($fs['RiskPoints']) : [];

        // ── Section 12: OpportunityAssessment ──
        $opp = !empty($fs['OpportunityAssessment']) ? self::deserializeKeyValue($fs['OpportunityAssessment']) : [];
        if ($remarks !== '') $opp['Remarks'] = $remarks;
        if ($portal !== '')  $opp['Tender Portal'] = $portal;

        // 3. Assemble payload for database
        $numVal = null;
        if ($estVal !== '') {
            $cleanEst = str_replace(',', '', $estVal);
            if (is_numeric($cleanEst)) {
                $numVal = (float)$cleanEst;
            }
        }

        $payload = [
            'Bcid'                  => $bcid,
            'TenderTitle'           => $bidTitle ?: (isset($fs['TenderTitle']) ? $fs['TenderTitle'] : null),
            'Department'            => $dept ?: (isset($fs['Department']) ? $fs['Department'] : null),
            'TenderType'            => ($bidType !== '' && $bidType !== '0') ? $bidType : (isset($fs['TenderType']) ? $fs['TenderType'] : null),
            'TenderRefNo'           => $refNo ?: (isset($fs['TenderRefNo']) ? $fs['TenderRefNo'] : null),
            'TenderDate'            => $pubDate ?: (isset($fs['TenderDate']) ? $fs['TenderDate'] : null),
            'ProjectValue'          => $estVal ?: (isset($fs['ProjectValue']) ? $fs['ProjectValue'] : null),
            'ProjectValueAmount'    => $numVal !== null ? $numVal : (isset($fs['ProjectValueAmount']) ? $fs['ProjectValueAmount'] : null),
            'TenderOverview'        => self::serializeKeyValue($overview),
            'ImportantDates'        => self::serializeKeyValue($dates),
            'TenderValue'           => self::serializeKeyValue($valSec),
            'ScopeOfWork'           => self::serializeList($scopes),
            'AreasCovered'          => self::serializeKeyValue($areas),
            'ContractPeriod'        => self::serializeKeyValue($period),
            'AwardCriteria'         => self::serializeKeyValue($award),
            'PerformanceGuarantee'  => self::serializeKeyValue($pbg),
            'BidValidity'           => self::serializeKeyValue($validity),
            'Warranty'              => self::serializeKeyValue($warranty),
            'RiskPoints'            => self::serializeList($risks),
            'OpportunityAssessment' => self::serializeKeyValue($opp)
        ];

        if ($fs) {
            // Update existing record
            $this->db->where('Tfsid', (int)$fs['Tfsid'])->update($this->table, $payload);
            return $this->getFactSheetById($fs['Tfsid']);
        } else {
            // Create new record
            $payload['DocumentPath']         = $nasPath ?: 'bid_' . $bcid;
            $payload['DocumentName']         = $nasPath ? basename($nasPath) : ($bidTitle ?: 'Bid_' . $bcid);
            $payload['Classification']       = ($bidType !== '' && $bidType !== '0') ? strtolower($bidType) : 'unknown';
            $payload['ClassificationStatus'] = 'classified';
            $this->db->insert($this->table, $payload);
            $newTfsid = (int)$this->db->insert_id();
            return $this->getFactSheetById($newTfsid);
        }
    }

    /**
     * Retrieves a Fact Sheet associated with a Bid creation ID (Bcid)
     * Automatically synchronizes with BidCreationDetails so Fact Sheet matches the bid.
     */
    public function getFactSheetByBcid($bcid)
    {
        return $this->generateOrSyncFactsheetForBid($bcid);
    }

    /**
     * Retrieves a Fact Sheet by relative document path
     */
    public function getFactSheetByDocPath($path)
    {
        return $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['DocumentPath' => $path])->row_array();
    }

    /**
     * Links a Fact Sheet to a created Bid (Bcid)
     */
    public function linkFactSheetToBid($tfsid, $bcid)
    {
        return $this->db->where('Tfsid', (int)$tfsid)->update($this->table, ['Bcid' => (int)$bcid]);
    }
    /**
     * Persists evidence records for a Fact Sheet.
     * Prevents duplicate evidence records on re-extraction.
     */
    public function saveEvidenceRecords($tfsid, array $evidenceRecords, $docPath = null, $docHash = null)
    {
        $tfsid = (int)$tfsid;
        if ($tfsid <= 0) return false;

        $json = !empty($evidenceRecords) ? json_encode($evidenceRecords, JSON_UNESCAPED_UNICODE) : null;
        return $this->db->where('Tfsid', $tfsid)->update($this->table, ['EvidenceJson' => $json]);
    }

    /**
     * Retrieves all evidence records for a Fact Sheet, indexed and grouped by field_key.
     * Uses the EvidenceJson column stored directly in tenderfactsheet (No separate table needed).
     * If no evidence records exist yet, generates and caches them in EvidenceJson on-the-fly.
     *
     * @param int $tfsid
     * @param array|null $fs Optional already-loaded Fact Sheet record
     * @return array [ 'by_field' => [ fieldKey => primaryEvidence ], 'all' => [ fieldKey => [ ...records ] ] ]
     */
    public function getEvidenceByTfsid($tfsid, array $fs = null)
    {
        $tfsid = (int)$tfsid;
        if ($tfsid <= 0) return ['by_field' => [], 'all' => []];

        if ($fs === null || !array_key_exists('EvidenceJson', $fs)) {
            $fs = $this->getFactSheetById($tfsid);
        }

        $rows = [];
        if (!empty($fs['EvidenceJson'])) {
            $decoded = json_decode($fs['EvidenceJson'], true);
            if (is_array($decoded)) {
                $rows = $decoded;
            }
        }

                // If no evidence records stored, attempt on-the-fly extraction and cache directly into EvidenceJson
        if (empty($rows) && !empty($fs['DocumentPath'])) {
            $fullPath = FCPATH . ltrim($fs['DocumentPath'], '/\\');
            if (file_exists($fullPath) && is_file($fullPath)) {
                if (file_exists(APPPATH . 'libraries/Document_extractor.php')) {
                    require_once APPPATH . 'libraries/Document_extractor.php';
                    $extractor = new \Document_extractor();
                    $pageMap = $extractor->extractPageMap($fullPath);

                    if (!empty($pageMap)) {
                        require_once APPPATH . 'libraries/Factsheet_evidence_service.php';
                        $evService = new \Factsheet_evidence_service();
                        $rows = $evService->collectEvidence($fs, $pageMap, [
                            'tfsid'         => $tfsid,
                            'bcid'          => $fs['Bcid'] ?? null,
                            'document_path' => $fs['DocumentPath'],
                            'document_hash' => $fs['DocumentHash'] ?? (file_exists($fullPath) ? md5_file($fullPath) : null)
                        ]);

                        if (!empty($rows)) {
                            $this->saveEvidenceRecords($tfsid, $rows);
                        }
                    }
                }
            }
        }

        // Resilient fallback: If evidence is still empty, look up any existing FactSheet with the same TenderTitle or RefNo
        if (empty($rows) && (!empty($fs['TenderTitle']) || !empty($fs['TenderRefNo']))) {
            $alt = null;
            if (!empty($fs['TenderTitle'])) {
                $alt = $this->db->where('TenderTitle', $fs['TenderTitle'])->where('EvidenceJson IS NOT NULL')->where('EvidenceJson !=', '')->order_by('Tfsid', 'DESC')->get($this->table)->row_array();
            }
            if (!$alt && !empty($fs['TenderRefNo'])) {
                $alt = $this->db->where('TenderRefNo', $fs['TenderRefNo'])->where('EvidenceJson IS NOT NULL')->where('EvidenceJson !=', '')->order_by('Tfsid', 'DESC')->get($this->table)->row_array();
            }
            if ($alt && !empty($alt['EvidenceJson'])) {
                $decoded = json_decode($alt['EvidenceJson'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $rows = $decoded;
                    $this->saveEvidenceRecords($tfsid, $rows);
                    if (!empty($alt['DocumentPath'])) {
                        $this->db->where('Tfsid', $tfsid)->update($this->table, [
                            'DocumentPath' => $alt['DocumentPath'],
                            'DocumentName' => $alt['DocumentName'] ?? basename($alt['DocumentPath']),
                            'DocumentHash' => $alt['DocumentHash'] ?? null
                        ]);
                    }
                }
            }
        }

        $byField = [];
        $all = [];
        foreach ($rows as $r) {
            $k = trim($r['field_key'] ?? '');
            if (empty($k)) continue;
            if (!isset($all[$k])) {
                $all[$k] = [];
            }
            $all[$k][] = $r;

            // Pick highest confidence / verified record as primary
            if (!isset($byField[$k]) || (($r['verification_status'] ?? '') === 'verified' && ($byField[$k]['verification_status'] ?? '') !== 'verified')) {
                $byField[$k] = $r;
            }
        }

        return ['by_field' => $byField, 'all' => $all];
    }

    /**
     * Retrieves evidence records for a specific field in a Fact Sheet.
     */
    public function getEvidenceForField($tfsid, $fieldKey)
    {
        $evData = $this->getEvidenceByTfsid($tfsid);
        $cleanKey = trim($fieldKey);
        return $evData['all'][$cleanKey] ?? (isset($evData['by_field'][$cleanKey]) ? [$evData['by_field'][$cleanKey]] : []);
    }
}
