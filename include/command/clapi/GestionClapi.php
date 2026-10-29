<?php
// Fichier GestionClapi.php — fonctions communes pour l'exécution de commandes CLAPI en ligne de commande

function CreeNomFichierModCSV(string $Command, string $Identifiant): string
{
    $dossierCsv = __DIR__ . '/exports/'; // dossier dédié, plus propre qu'à la racine
    // On s'assure que le dossier existe
    if (!is_dir($dossierCsv)) {
        mkdir($dossierCsv, 0755, true);
    }
    // Nettoyage des valeurs utilisées dans le nom de fichier
    // (on retire tout ce qui n'est pas alphanumérique, tiret ou underscore)
    $datestamp = date('Ymd'); // ex: 20260806
    $timestamp = date('His'); // ex: 141503 pour 14H15m03s
    $dossierClapi = $dossierCsv . "{$datestamp}/"; // dossier dédié, plus propre qu'à la racine
    // On s'assure que le dossier existe
    if (!is_dir($dossierClapi)) {
        mkdir($dossierClapi, 0755, true);
    }
    $identifiantPropre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $Identifiant);
    $nomFichier = "{$Command}_{$identifiantPropre}_{$timestamp}.csv";
    $cheminComplet = $dossierClapi . $nomFichier;
    return $cheminComplet;
}

function LanceClapi(string $File, $Type): array
{
    global $clapiuser, $clapiPWD;
    require_once __DIR__ . '/../../../config.php';
    $nombreLignes = count(file($File));
    //Les variables sont définies dans le fichier config.php
    //$clapiuser = "cs_clapi";
    //$clapiPWD = "Nemours*77140";
    $CommandClapi = "/usr/share/centreon/bin/centreon -u " . $clapiuser . " -p " . $clapiPWD . " -" . $Type . " " . $File;
    // Affichage du nom du fichier CSV pour vérification
    // echo '<pre> Variables depuis GestionClapi.php -> LanceClapi</pre>';
    // echo '<pre> Fichier Clapi : ' . $File . '</pre>';
    // echo '<pre> Nombre de ligne : ' . $nombreLignes . '</pre>';
    exec($CommandClapi, $sortie, $codeRetour);
    // echo '<pre> Commande clapi : ' . $CommandClapi . '</pre>';
    $sortieTexte = implode("\n", $sortie);
    if (preg_match('/Missing parameters|Object not found/i', $sortieTexte)) {
        $codeRetour = 1;
    }
    if (preg_match('/Missing parameters|Object not found/i', $sortieTexte)) {
        $codeRetour = 1;
    }
    return ['codeRetour' => $codeRetour, 'sortie' => $sortie];
}

function ecrireFichierCsv(string $cheminFichier, array $Tabcsv): string
{
    $fp = fopen($cheminFichier, 'w');
    if ($fp === false) {
        die('Impossible de créer le fichier CSV.');
    }
    if (flock($fp, LOCK_EX)) {
        foreach ($Tabcsv as $LigneCsv) {
            $ligne = $LigneCsv . PHP_EOL;
            fwrite($fp, $ligne);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
    return $cheminFichier;
}
