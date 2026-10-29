<?php
//Fichier Serveur-Dedie/include/global/DbFunctions.php

require_once '/etc/centreon/centreon.conf.php';
require_once __DIR__ . "/../DBFunctions/DBFunctionPollers.php";
// include './Traces.php';

/** @var array<string, string> $conf_centreon */

function connecterBDD() {
    global $conf_centreon;
    /** @var array<string, string> $conf_centreon */
    try {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $conf_centreon['hostCentreon'],
                $conf_centreon['port'] ?? '3306',
                $conf_centreon['db']
                ),
            $conf_centreon['user'],
            $conf_centreon['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
            );
        return $pdo;
    } catch (PDOException $e) {
        die('Erreur de connexion : ' . $e->getMessage());
    }
}

function renderHostOptions(array $hosts): string {
    echo "<BR>renderHostOptions : ListeDroitsUtilisateurs DroitsAclHosts après filtrage des HC: </BR>";
    var_dump($hosts);
    $html = '';
    foreach ($hosts as $host) {
        $value = htmlspecialchars($host['host_name'], ENT_QUOTES, 'UTF-8');
        $pollerId = htmlspecialchars($host['Poller_id'] ?? '', ENT_QUOTES, 'UTF-8');
        $html .= "<option value=\"$value\" data-poller-id=\"$pollerId\">$value</option>";
    }
    return $html;
}
function renderHostOptionServices(array $hosts): string {
    echo "<BR>renderHostOptionServices : ListeDroitsUtilisateurs DroitsAclHosts après filtrage des HC: </BR>";
    var_dump($hosts);
    $html = '';
    foreach ($hosts as $key => $host) {
        // Gère les deux formats : tableau associatif ou simple nom d'hôte
        $name = is_array($host) ? ($host['host_name'] ?? $host['name'] ?? '') : (is_string($host) ? $host : (string)$key);
        $value = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $pollerId = htmlspecialchars(is_array($host) ? ($host['Poller_id'] ?? '') : '', ENT_QUOTES, 'UTF-8');
        $html .= "<option value=\"$value\" data-poller-id=\"$pollerId\">$value</option>";
    }
    return $html;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $defaultPoller = RechercheDefaultPoller();
    $pollerName = trim($_POST['poller_name'] ?? '');
    
    if ($pollerName === '') {
        $pollerName = $defaultPoller ?: '';
    }
    
    if ($pollerName === '') {
        $message = 'Aucun poller par défaut n’est configuré.';
    }
}

$pollers = RecherchePollers();
$defaultPoller = RechercheDefaultPoller();
//$HostNames = RechercheHosts();
