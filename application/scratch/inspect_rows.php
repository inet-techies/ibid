<?php
define('BASEPATH', 'd:/xampp/htdocs/ibid/system/');
require 'd:/xampp/htdocs/ibid/application/config/database.php';
$dbCfg = $db['default'];
$pdo = new PDO('mysql:host=' . $dbCfg['hostname'] . ';dbname=' . $dbCfg['database'], $dbCfg['username'], $dbCfg['password']);
$stmt = $pdo->query('SELECT Tfsid, Bcid, DocumentName, TenderOverview, ImportantDates FROM tenderfactsheet LIMIT 5');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Tfsid: {$row['Tfsid']} | Bcid: {$row['Bcid']} | File: {$row['DocumentName']}\n";
    echo "  TenderOverview: " . substr((string)$row['TenderOverview'], 0, 100) . "...\n";
    echo "  ImportantDates: " . substr((string)$row['ImportantDates'], 0, 100) . "...\n";
}
