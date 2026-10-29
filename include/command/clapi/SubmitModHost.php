<?php
// Serveur-Dedie/include/command/clapi/SubmitModHost.php
require_once 'GestionClapi.php';
require_once '../../../include/ConstruitHTML/url-centreon.php';

function creeFichierModHost($HostName, array $Tabcsv)
{
    $FichierClapi = CreeNomFichierModCSV("MOD", $HostName);
    return ecrireFichierCsv($FichierClapi, $Tabcsv);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json');
    $donnees = json_decode(file_get_contents('php://input'), true);
    $hostname = $donnees['hostname'] ?? '';
    $tabCsv = $donnees['csv'] ?? [];
    if ($hostname === '' || ! is_array($tabCsv) || empty($tabCsv)) {
        echo json_encode([
            'error' => 'Paramètres invalides.'
        ]);
        exit();
    }
    $fichier = creeFichierModHost($hostname, $tabCsv);
    $resultatClapi = LanceClapi($fichier, 'i');
$resultatUrl = ['success' => false, 'message' => '', 'url' => null, 'lien' => ''];
    if ($resultatClapi['codeRetour'] === 0) {
        $resultatUrl = UrlCentreonHost($hostname);
    }
    echo json_encode([
        'codeRetour' => $resultatClapi['codeRetour'],
        'sortie' => $resultatClapi['sortie'],
        'fichier' => $fichier,
        'lien' => $resultatUrl['lien'],
        'popup' => [
            'message' => $resultatUrl['message'],
            'type' => $resultatUrl['success'] ? 'success' : 'error',
            'url' => $resultatUrl['url']
        ]
    ]);
    exit();
}