<?php
// ---------- initialize database

global $aConfig, $oDB;

require_once __DIR__ . '/vendor/php-abstract-dbo/src/pdo-db.class.php';
$aConfig = require __DIR__ . '/config.php';
$oDB = new axelhahn\pdo_db((array)$aConfig['pdo']??[]);
