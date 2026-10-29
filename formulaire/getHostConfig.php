<?php
// Fichier Serveur-Dedie/formulaire/getHostConfig.php
require_once '../config.php';
require_once '../include/global/DbFunctions.php';
require_once '../include/DBFunctions/DBFunctionsHost.php';

header('Content-Type: application/json');

$hostName = trim($_GET['host_name'] ?? '');

if ($hostName === '') {
    echo json_encode([]);
    exit;
}

$rows = RechercheHostConfiguration($hostName);

echo json_encode($rows);