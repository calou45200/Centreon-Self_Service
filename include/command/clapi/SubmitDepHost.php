<?php
// Fichier Serveur-Dedie/include/command/clapi/SubmitDepHost.php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
require_once '/etc/centreon/centreon.conf.php';
require_once __DIR__ . '/../../global/DbFunctions.php';
require_once 'GestionClapi.php';
require_once __DIR__ . '/../restapi/GestionAPI.php';
require_once __DIR__ . '/../../ajout/Dependencies/ServiceDependencies.php';

if (empty($_POST['host_name_dep']) || !is_array($_POST['host_name_dep'])) {
    die('Champs requis manquants.');
}
//var_dump($_POST['host_name_dep']);
$Hosts     = array_map('trim', $_POST['host_name_dep']);
$TypeEnvoi = trim($_POST['type_envoi_dep'] ?? 'clapi');

foreach ($Hosts as $Host) {
    HostDependencies($Host);
}
