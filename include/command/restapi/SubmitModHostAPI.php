<?php
// Fichier SubmitModHostAPI.php
require_once 'GestionAPI.php';
require_once '../../../include/ConstruitHTML/url-centreon.php';
function ModifHostAPI(string $hostname, array $listeModif): array
{
    $commandes = [];
    $templatesModifies = false;
    $changementNom = null;
    
    foreach ($listeModif as $modif) {
        if ($modif['Type'] === 'name') {
            $changementNom = $modif['Champ'];
            continue;
        }
        
        if ($modif['Type'] === 'alias' || $modif['Type'] === 'address') {
            $commandes[] = [
                'action' => 'setparam',
                'object' => 'host',
                'values' => "{$hostname};{$modif['Type']};{$modif['Champ']}",
                ];
            continue;
        }
        
        if ($modif['Type'] === 'ADD') {
            $commandes[] = [
                'action' => 'addtemplate',
                'object' => 'host',
                'values' => "{$hostname};{$modif['Champ']}",
                ];
            $templatesModifies = true;
            continue;
        }
        
        if ($modif['Type'] === 'DEL') {
            $commandes[] = [
                'action' => 'deltemplate',
                'object' => 'host',
                'values' => "{$hostname};{$modif['Champ']}",
                ];
            $templatesModifies = true;
            continue;
        }
    }
    
    if ($templatesModifies) {
        $commandes[] = [
            'action' => 'applytpl',
            'object' => 'host',
            'values' => $hostname,
        ];
    }
    
    if ($changementNom) {
        $commandes[] = [
            'action' => 'setparam',
            'object' => 'host',
            'values' => "{$hostname};name;{$changementNom}",
            ];
    }
    
    return $commandes;
}
$hostname = $_GET['hostname'] ?? '';
$listeModif = isset($_GET['listeModif']) ? json_decode($_GET['listeModif'], true) : [];

if ($hostname === '' || !is_array($listeModif) || empty($listeModif)) {
    die('Paramètres invalides.');
}

$token = AuthentifierApi();
$commandes = ModifHostAPI($hostname, $listeModif);
$resultats = EnvoyerToutesLesCommandes($token, $commandes);

header('Content-Type: application/json');
$dernier = end($resultats);
if ($dernier !== false && $dernier['codeRetour'] === 0) {
    $resultatUrl = UrlCentreonHost($hostname);
    echo json_encode([
        'codeRetour' => $dernier['codeRetour'],
        'popup' => [
            'message' => $resultatUrl['message'],
            'type' => $resultatUrl['success'] ? 'success' : 'error',
            'url' => $resultatUrl['url']
        ]
    ]);
} else {
    echo json_encode([
        'codeRetour' => $dernier['codeRetour'] ?? 1,
        'popup' => [
            'message' => "Échec de la modification pour {$hostname}",
            'type' => 'error',
            'url' => null
        ]
    ]);
}