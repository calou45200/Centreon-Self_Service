<?php
// Fichier SubmitDepHost.php

require_once '/etc/centreon/centreon.conf.php';
require_once '../../../include/global/DbFunctions.php';
require_once 'GestionClapi.php';
require_once 'GestionAPI.php';
require_once '../../../formulaire/ServiceDependencies.php';

if (($_POST['host_name_dep'] ?? '') === '') {
    die('Champs requis manquants.');
}

$Host      = trim($_POST['host_name_dep']);
$TypeEnvoi = trim($_POST['type_envoi_dep'] ?? 'clapi');

HostDependencies($Host);