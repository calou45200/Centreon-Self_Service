<?php
// SubmitModPoller.php
require_once 'GestionClapi.php';

function creeFichierModPoller($Hosts, array $Tabcsv)
{
    $FichierClapi = CreeNomFichierModCSV("MODPOLLER", implode('-', $Hosts));
    return ecrireFichierCsv($FichierClapi, $Tabcsv);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json');
    $donnees = json_decode(file_get_contents('php://input'), true);
    $hosts = $donnees['hosts'] ?? [];
    $tabCsv = $donnees['csv'] ?? [];
    if (! is_array($hosts) || empty($hosts) || ! is_array($tabCsv) || empty($tabCsv)) {
        echo json_encode([
            'error' => 'Paramètres invalides.'
        ]);
        exit();
    }
    $fichier = creeFichierModPoller($hosts, $tabCsv);
    $resultatClapi = LanceClapi($fichier, 'i');
    echo json_encode([
        'codeRetour' => $resultatClapi['codeRetour'],
        'sortie' => $resultatClapi['sortie'],
        'fichier' => $fichier
    ]);
    exit();
}