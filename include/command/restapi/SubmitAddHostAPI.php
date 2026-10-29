<?php 
// Fichier SubmitAddHostAPI.php
require_once 'GestionAPI.php';
require_once '../../../include/ConstruitHTML/url-centreon.php';
function ImportHostAPI(array $Clapi, bool $force): array
{
    $commandes = [];
    
    if ($force) {
        $commandes[] = [
            'action' => 'setparam',
            'object' => 'host',
            'values' => "{$Clapi['HostName']};alias;{$Clapi['HostAlias']}",
            ];
        
        $commandes[] = [
            'action' => 'setparam',
            'object' => 'host',
            'values' => "{$Clapi['HostName']};address;{$Clapi['HostAddress']}",
            ];
    } else {
        $commandes[] = [
            'action' => 'add',
            'object' => 'host',
            'values' => "{$Clapi['HostName']};{$Clapi['HostAlias']};{$Clapi['HostAddress']};{$Clapi['HostTemplate']};{$Clapi['PollerName']};",
            ];
    }
    
    $commandes[] = [
        'action' => 'settemplate',
        'object' => 'host',
        'values' => "{$Clapi['HostName']};{$Clapi['HostTemplate']}",
        ];
    
    $commandes[] = [
        'action' => 'applytpl',
        'object' => 'host',
        'values' => $Clapi['HostName'],
    ];
    
    return $commandes;
}

function nettoyerPourCsv(string $valeur): string {
    $valeur = trim($valeur);
    $valeur = str_replace([';', "\r", "\n"], '', $valeur);
    if (preg_match('/^[=+\-@]/', $valeur)) {
        $valeur = "'" . $valeur;
    }
    return $valeur;
}

$champs = [
    'HostName'     => nettoyerPourCsv($_GET['name'] ?? ''),
    'HostTemplate' => nettoyerPourCsv($_GET['TH_Name'] ?? ''),
    'HostAlias'    => nettoyerPourCsv($_GET['alias'] ?? ''),
    'HostAddress'  => nettoyerPourCsv($_GET['address'] ?? ''),
    'PollerName'   => nettoyerPourCsv($_GET['poller_name'] ?? ''),
];
if ($champs['HostName'] === '' || $champs['HostAddress'] === '') {
    die('Champs requis manquants.');
}
$force = ($_GET['force'] ?? '0') === '1';
require_once '../../global/DbFunctions.php';
$token = AuthentifierApi();
$commandes = ImportHostAPI($champs, $force);
$resultats = EnvoyerToutesLesCommandes($token, $commandes);
$dernier = end($resultats);
if ($dernier !== false && $dernier['codeRetour'] === 0) {
    $resultat = UrlCentreonHost($champs['HostName']);
    echo json_encode([
        'codeRetour' => 0,
        'popup' => [
            'type' => 'success',
            'message' => $resultat['message'],
            'url' => $resultat['url'],
        ],
    ]);
} else {
    echo json_encode([
        'codeRetour' => 1,
        'popup' => [
            'type' => 'error',
            'message' => "Échec de l'import pour {$champs['HostName']}",
            'url' => null,
        ],
    ]);
}