<?php
define('BASEPATH', 'd:/xampp/htdocs/ibid/system/');
require 'd:/xampp/htdocs/ibid/application/config/database.php';
$dbCfg = $db['default'];
$pdo = new PDO('mysql:host=' . $dbCfg['hostname'] . ';dbname=' . $dbCfg['database'], $dbCfg['username'], $dbCfg['password']);
$stmt = $pdo->query('SHOW COLUMNS FROM tenderfactsheet');
$cols = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols[] = $row['Field'] . ' (' . $row['Type'] . ')';
    echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}
echo "\nTotal columns: " . count($cols) . "\n";
