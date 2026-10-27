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
    protected $table = 'TenderFactSheet';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /**
     * Ensures the single TenderFactSheet table exists in bid_core_db with all 12 section columns
     * and performs safe migration of existing rows to JSON.
     */
    public function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `TenderFactSheet` (
          `Tfsid` int(11) NOT NULL AUTO_INCREMENT,
          `Bcid` int(11) DEFAULT NULL,
          `DocumentPath` varchar(255) NOT NULL,
          `DocumentName` varchar(255) NOT NULL,
          `DocumentHash` varchar(64) DEFAULT NULL,
          `TxtFilePath` varchar(255) DEFAULT NULL,
          `Classification` varchar(50) DEFAULT 'unknown',
          `ClassificationStatus` varchar(50) DEFAULT 'unclassified',
          
          -- 12 Main Heading Section Columns (One Column Per Section, safely encoded JSON)
          `TenderOverview` text DEFAULT NULL,
          `ImportantDates` text DEFAULT NULL,
          `TenderValue` text DEFAULT NULL,
          `ScopeOfWork` text DEFAULT NULL,
          `AreasCovered` text DEFAULT NULL,
          `ContractPeriod` text DEFAULT NULL,
          `AwardCriteria` text DEFAULT NULL,
          `PerformanceGuarantee` text DEFAULT NULL,
          `BidValidity` text DEFAULT NULL,
          `Warranty` text DEFAULT NULL,
          `RiskPoints` text DEFAULT NULL,
          `OpportunityAssessment` text DEFAULT NULL,

          -- Core Legacy Individual Columns (Preserved for compatibility and fast lookup)
          `Department` varchar(255) DEFAULT NULL,
          `ZoneRegion` varchar(255) DEFAULT NULL,
          `TenderTitle` varchar(500) DEFAULT NULL,
          `TenderType` varchar(100) DEFAULT NULL,
          `TenderRefNo` varchar(150) DEFAULT NULL,
          `TenderDate` varchar(100) DEFAULT NULL,
          `ProjectValue` varchar(100) DEFAULT NULL,
          `ProjectValueAmount` decimal(15,2) DEFAULT NULL,
          `ContractType` varchar(100) DEFAULT NULL,
          `WarrantyAmc` varchar(255) DEFAULT NULL,
          `BidSystem` varchar(100) DEFAULT NULL,

          -- Audit & Metadata
          `CreatedBy` int(11) DEFAULT NULL,
          `CreationTs` datetime DEFAULT CURRENT_TIMESTAMP,
          `LastChangeTs` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`Tfsid`),
          KEY `idx_bcid` (`Bcid`),
          KEY `idx_docpath` (`DocumentPath`),
          KEY `idx_dochash` (`DocumentHash`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($sql);

        // Ensure 12 section columns exist in existing table
        $sectionColumns = [
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
            'OpportunityAssessment'
        ];

        foreach ($sectionColumns as $col) {
            if (!$this->db->field_exists($col, $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `{$col}` TEXT NULL");
            }
        }

        // Migrate any legacy pipe-delimited data to clean JSON
        $this->migrateLegacyRowsToJson();
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
     * Safely migrates existing rows in TenderFactSheet from legacy pipe-delimited format to JSON.
     */
    public function migrateLegacyRowsToJson()
    {
        $rows = $this->db->get($this->table)->result_array();
        if (empty($rows)) return;

        foreach ($rows as $row) {
            $updates = [];

            // KV sections
            $kvCols = ['TenderOverview', 'ImportantDates', 'TenderValue', 'AreasCovered', 'ContractPeriod', 'AwardCriteria', 'PerformanceGuarantee', 'BidValidity', 'Warranty', 'OpportunityAssessment'];
            foreach ($kvCols as $col) {
                if (!empty($row[$col])) {
                    $val = trim($row[$col]);
                    // Check if already valid JSON object
                    $decoded = json_decode($val, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        $parsed = self::deserializeKeyValue($val);
                        if (!empty($parsed)) {
                            $updates[$col] = self::serializeKeyValue($parsed);
                        }
                    }
                }
            }

            // List sections
            $listCols = ['ScopeOfWork', 'RiskPoints'];
            foreach ($listCols as $col) {
                if (!empty($row[$col])) {
                    $val = trim($row[$col]);
                    $decoded = json_decode($val, true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                        $parsed = self::deserializeList($val);
                        if (!empty($parsed)) {
                            $updates[$col] = self::serializeList($parsed);
                        }
                    }
                }
            }

            if (!empty($updates)) {
                $this->db->where('Tfsid', $row['Tfsid'])->update($this->table, $updates);
            }
        }
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
        if (empty($data['TenderOverview']) || (!is_string($data['TenderOverview']) || (strpos($data['TenderOverview'], '{') !== 0 && strpos($data['TenderOverview'], ' | ') !== false))) {
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

        // 2. ImportantDates (JSON object)
        if (empty($data['ImportantDates']) || (!is_string($data['ImportantDates']) || (strpos($data['ImportantDates'], '{') !== 0 && strpos($data['ImportantDates'], ' | ') !== false))) {
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

        // 3. TenderValue (JSON object)
        if (empty($data['TenderValue']) || (!is_string($data['TenderValue']) || (strpos($data['TenderValue'], '{') !== 0 && strpos($data['TenderValue'], ' | ') !== false))) {
            $valKV = [
                'Estimated Value'               => isset($data['EstimatedValue']) ? $data['EstimatedValue'] : (isset($data['ProjectValue']) ? $data['ProjectValue'] : ''),
                'EMD'                           => isset($data['EmdAmount']) ? $data['EmdAmount'] : '',
                'Performance Bank Guarantee'    => isset($data['PbgDetails']) ? $data['PbgDetails'] : (isset($data['PbgAmount']) ? $data['PbgAmount'] : ''),
                'Bid Validity'                  => isset($data['BidValidityDays']) ? $data['BidValidityDays'] : (isset($data['BidValidityDuration']) ? $data['BidValidityDuration'] : '')
            ];
            $data['TenderValue'] = self::serializeKeyValue($valKV);
        }

        // 4. ScopeOfWork (JSON array)
        if (empty($data['ScopeOfWork']) || (!is_string($data['ScopeOfWork']) || (strpos($data['ScopeOfWork'], '[') !== 0 && strpos($data['ScopeOfWork'], ' | ') !== false))) {
            $scopesArr = [];
            if (!empty($data['ScopeItemsJson'])) {
                $scopesArr = json_decode($data['ScopeItemsJson'], true);
            }
            if (empty($scopesArr) && !empty($data['ScopeOfWork'])) {
                $scopesArr = preg_split('/[\r\n]+|[•·*∩é╖]|\x{F0B7}|\x{00B7}/u', $data['ScopeOfWork']);
            }
            if (!empty($scopesArr) && is_array($scopesArr)) {
                $data['ScopeOfWork'] = self::serializeList($scopesArr);
            } else {
                $data['ScopeOfWork'] = json_encode([], JSON_UNESCAPED_UNICODE);
            }
        }

        // 5. AreasCovered (JSON object)
        if (empty($data['AreasCovered']) || (!is_string($data['AreasCovered']) || (strpos($data['AreasCovered'], '{') !== 0 && strpos($data['AreasCovered'], ' | ') !== false))) {
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

        // 6. ContractPeriod (JSON object)
        if (empty($data['ContractPeriod']) || (!is_string($data['ContractPeriod']) || (strpos($data['ContractPeriod'], '{') !== 0 && strpos($data['ContractPeriod'], ' | ') !== false))) {
            $cpKV = [
                'Initial Contract'      => isset($data['ContractInitialDuration']) ? $data['ContractInitialDuration'] : '',
                'Extendable'            => isset($data['ContractExtendable']) ? $data['ContractExtendable'] : '',
                'Maximum Period'        => isset($data['ContractMaxPeriod']) ? $data['ContractMaxPeriod'] : '',
                'Extension Conditions'  => isset($data['ContractExtensionConditions']) ? $data['ContractExtensionConditions'] : ''
            ];
            $data['ContractPeriod'] = self::serializeKeyValue($cpKV);
        }

        // 7. AwardCriteria (JSON object)
        if (empty($data['AwardCriteria']) || (!is_string($data['AwardCriteria']) || (strpos($data['AwardCriteria'], '{') !== 0 && strpos($data['AwardCriteria'], ' | ') !== false))) {
            $acKV = [
                'Evaluation Method'     => isset($data['AwardEvaluationMethod']) ? $data['AwardEvaluationMethod'] : '',
                'Split Work'            => isset($data['AwardSplitWork']) ? $data['AwardSplitWork'] : '',
                'Possible Allocation'   => isset($data['AwardAllocation']) ? $data['AwardAllocation'] : '',
                'Matching Requirements' => isset($data['AwardL2L3Matching']) ? $data['AwardL2L3Matching'] : ''
            ];
            $data['AwardCriteria'] = self::serializeKeyValue($acKV);
        }

        // 8. PerformanceGuarantee (JSON object)
        if (empty($data['PerformanceGuarantee']) || (!is_string($data['PerformanceGuarantee']) || (strpos($data['PerformanceGuarantee'], '{') !== 0 && strpos($data['PerformanceGuarantee'], ' | ') !== false))) {
            $pgKV = [
                'Amount'        => isset($data['PbgAmount']) ? $data['PbgAmount'] : (isset($data['PbgPercentage']) ? $data['PbgPercentage'] . '%' : ''),
                'Validity'      => isset($data['PbgValidity']) ? $data['PbgValidity'] : '',
                'Submission'    => isset($data['PbgSubmissionDeadline']) ? $data['PbgSubmissionDeadline'] : ''
            ];
            $data['PerformanceGuarantee'] = self::serializeKeyValue($pgKV);
        }

        // 9. BidValidity (JSON object)
        if (empty($data['BidValidity']) || (!is_string($data['BidValidity']) || (strpos($data['BidValidity'], '{') !== 0 && strpos($data['BidValidity'], ' | ') !== false))) {
            $bvKV = [
                'Duration' => isset($data['BidValidityDuration']) ? $data['BidValidityDuration'] : (isset($data['BidValidityDays']) ? $data['BidValidityDays'] : '')
            ];
            $data['BidValidity'] = self::serializeKeyValue($bvKV);
        }

        // 10. Warranty (JSON object)
        if (empty($data['Warranty']) || (!is_string($data['Warranty']) || (strpos($data['Warranty'], '{') !== 0 && strpos($data['Warranty'], ' | ') !== false))) {
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

        // 11. RiskPoints (JSON array)
        if (empty($data['RiskPoints']) || (!is_string($data['RiskPoints']) || (strpos($data['RiskPoints'], '[') !== 0 && strpos($data['RiskPoints'], ' | ') !== false))) {
            $risksArr = [];
            if (!empty($data['RiskPointsJson'])) {
                $risksArr = json_decode($data['RiskPointsJson'], true);
            }
            if (empty($risksArr) && !empty($data['RiskPoints'])) {
                $risksArr = preg_split('/[\r\n]+|[•·*∩é╖]|\x{F0B7}|\x{00B7}/u', $data['RiskPoints']);
            }
            if (!empty($risksArr) && is_array($risksArr)) {
                $data['RiskPoints'] = self::serializeList($risksArr);
            } else {
                $data['RiskPoints'] = json_encode([], JSON_UNESCAPED_UNICODE);
            }
        }

        // 12. OpportunityAssessment (JSON object)
        if (empty($data['OpportunityAssessment']) || (!is_string($data['OpportunityAssessment']) || (strpos($data['OpportunityAssessment'], '{') !== 0 && strpos($data['OpportunityAssessment'], ' | ') !== false))) {
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
     * Retrieves a Fact Sheet associated with a Bid creation ID (Bcid)
     */
    public function getFactSheetByBcid($bcid)
    {
        $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['Bcid' => (int)$bcid])->row_array();
        if (!$fs && (int)$bcid > 0) {
            $bid = $this->db->get_where('BidCreationDetails', ['Bcid' => (int)$bcid])->row_array();
            if ($bid) {
                if (!empty($bid['NASPath'])) {
                    $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['DocumentPath' => $bid['NASPath']])->row_array();
                }
                if (!$fs && !empty($bid['TenderId'])) {
                    $fs = $this->db->order_by('Tfsid', 'DESC')->get_where($this->table, ['TenderRefNo' => $bid['TenderId']])->row_array();
                }
                if (!$fs && !empty($bid['BidTitle'])) {
                    $fs = $this->db->order_by('Tfsid', 'DESC')->like('TenderTitle', substr($bid['BidTitle'], 0, 30))->get($this->table)->row_array();
                }
                if ($fs) {
                    $this->linkFactSheetToBid($fs['Tfsid'], $bcid);
                }
            }
        }
        if (!$fs) {
            $fs = $this->db->order_by('Tfsid', 'DESC')->get($this->table, 1)->row_array();
        }
        return $fs;
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
}
