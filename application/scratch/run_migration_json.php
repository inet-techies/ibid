<?php
define('BASEPATH', 'd:/xampp/htdocs/ibid/system/');
require 'd:/xampp/htdocs/ibid/application/config/database.php';
$dbCfg = $db['default'];
$pdo = new PDO('mysql:host=' . $dbCfg['hostname'] . ';dbname=' . $dbCfg['database'], $dbCfg['username'], $dbCfg['password']);

if (!class_exists('CI_Model')) {
    class CI_Model {}
}
require_once 'd:/xampp/htdocs/ibid/application/modules/document_parser/models/Factsheet_model.php';

// Fetch all rows
$stmt = $pdo->query('SELECT * FROM tenderfactsheet');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total rows in tenderfactsheet: " . count($rows) . "\n";

$kvCols = ['TenderOverview', 'ImportantDates', 'TenderValue', 'AreasCovered', 'ContractPeriod', 'AwardCriteria', 'PerformanceGuarantee', 'BidValidity', 'Warranty', 'OpportunityAssessment'];
$listCols = ['ScopeOfWork', 'RiskPoints'];

$updatedCount = 0;

foreach ($rows as $row) {
    $updates = [];

    foreach ($kvCols as $col) {
        if (!empty($row[$col])) {
            $val = trim($row[$col]);
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                $parsed = Factsheet_model::deserializeKeyValue($val);
                if (!empty($parsed)) {
                    $updates[$col] = Factsheet_model::serializeKeyValue($parsed);
                }
            }
        }
    }

    foreach ($listCols as $col) {
        if (!empty($row[$col])) {
            $val = trim($row[$col]);
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                $parsed = Factsheet_model::deserializeList($val);
                if (!empty($parsed)) {
                    $updates[$col] = Factsheet_model::serializeList($parsed);
                }
            }
        }
    }

    if (!empty($updates)) {
        $setClauses = [];
        $params = [];
        foreach ($updates as $col => $jsonVal) {
            $setClauses[] = "`{$col}` = ?";
            $params[] = $jsonVal;
        }
        $params[] = $row['Tfsid'];
        $sql = "UPDATE tenderfactsheet SET " . implode(', ', $setClauses) . " WHERE Tfsid = ?";
        $upStmt = $pdo->prepare($sql);
        $upStmt->execute($params);
        $updatedCount++;
    }
}

echo "Migrated {$updatedCount} rows to JSON format.\n\n";

// Sample row to demonstrate
$sampleStmt = $pdo->query('SELECT Tfsid, DocumentName, TenderOverview, ImportantDates, ScopeOfWork FROM tenderfactsheet WHERE Tfsid = 5');
$sample = $sampleStmt->fetch(PDO::FETCH_ASSOC);
if ($sample) {
    echo "Sample Row Tfsid: {$sample['Tfsid']} ({$sample['DocumentName']})\n";
    echo "--- TenderOverview (JSON) ---\n" . $sample['TenderOverview'] . "\n\n";
    echo "--- ImportantDates (JSON) ---\n" . $sample['ImportantDates'] . "\n\n";
    echo "--- ScopeOfWork (JSON) ---\n" . $sample['ScopeOfWork'] . "\n\n";
}
