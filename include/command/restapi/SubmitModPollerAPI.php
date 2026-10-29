<?php
// Fichier SubmitModPollerAPI.php
require_once 'GestionAPI.php';

function ModifPollerAPI(array $hosts, string $pollerName): array
{
    $commandes = [];
    
    foreach ($hosts as $host) {
        $commandes[] = [
            'action' => 'setinstance',
            'object' => 'host',
            'values' => "{$host};{$pollerName}",
            ];
    }
    
    return $commandes;
}

$hosts = isset($_GET['hosts']) ? json_decode($_GET['hosts'], true) : [];
$pollerName = $_GET['poller'] ?? '';

if (!is_array($hosts) || empty($hosts) || $pollerName === '') {
    die('Paramètres invalides.');
}

$token = AuthentifierApi();
$commandes = ModifPollerAPI($hosts, $pollerName);
$resultats = EnvoyerToutesLesCommandes($token, $commandes);