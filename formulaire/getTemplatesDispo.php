<?php
// Fichier getTemplatesDispo.php
require_once '../config.php';
require_once '../include/global/DbFunctions.php';
require_once '../include/DBFunctions/DBFunctionsHost.php';
header('Content-Type: application/json');

$exclusion = $_GET['exclusion'] ?? [];
if (!is_array($exclusion)) {
    $exclusion = [];
}

echo json_encode(ListeTemplate($exclusion));