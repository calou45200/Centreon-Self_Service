<?php
// Fichier GestionAPI.php — fonctions communes pour l'envoi de commandes CLAPI via l'API REST

// Chargé au niveau global pour que $clapiuser et $clapiPWD restent disponibles à chaque appel
require_once __DIR__ . '/../../../config.php';

function AuthentifierApi(): string
{
    global $clapiuser, $clapiPWD;
 //   error_reporting(E_ALL);
 //   ini_set('display_errors', 1);
    //Les variables sont définies dans le fichier config.php
    //$clapiuser = "cs_clapi";
    //$clapiPWD = "Nemours*77140";

    $scheme = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
        ? $_SERVER['HTTP_X_FORWARDED_PROTO']
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $racineSite = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/centreon';
    $url = $racineSite . '/api/index.php?action=authenticate';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $clapiuser,
        'password' => $clapiPWD,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $reponse = curl_exec($ch);
    $erreur = curl_error($ch);
    curl_close($ch);

    if ($reponse === false) {
        die('Erreur cURL authentification : ' . $erreur);
    }

    $donnees = json_decode($reponse, true);

    if (empty($donnees['authToken'])) {
        die('Authentification échouée : ' . $reponse);
    }
    //var_dump($reponse);
    return $donnees['authToken'];
}

function EnvoyerCommandeApi(string $token, array $commande): array
{
    $dossierLog = __DIR__ . '/../../logs/ResApi/' . date('Ymd');
    if (!is_dir($dossierLog)) {
        mkdir($dossierLog, 0755, true);
    }
    // trace des appels à l'API dans un fichier JSON pour historisation et débogage
    $timestamp = date('His');
    $identifiant = ($commande['object'] ?? '') . '-' . ($commande['action'] ?? '') . '-' . ($commande['values'] ?? '');
    $identifiantPropre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifiant);
    $fichierLog = $dossierLog . '/' . $timestamp . '_' . $identifiantPropre . '.json';
    file_put_contents($fichierLog, json_encode($commande));
    // connexion à l'APIRest centreon pour envoyer la commande
    $scheme = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
        ? $_SERVER['HTTP_X_FORWARDED_PROTO']
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $racineSite = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/centreon';
    $url = $racineSite . '/api/index.php?action=action&object=centreon_clapi';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commande));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'centreon-auth-token: ' . $token,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $verbose = fopen($dossierLog . '/' . $timestamp . '_verbose.log', 'w');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $reponse = curl_exec($ch);
    fclose($verbose);
    $erreur = curl_error($ch);
    curl_close($ch);

    if ($reponse === false) {
        return ['codeRetour' => 1, 'erreur' => $erreur, 'commande' => $commande];
    }

    $donnees = json_decode($reponse, true);
    file_put_contents($fichierLog . '.response.json', $reponse);
    $messagesAIgnorer = ['Object already exists'];
    $estIgnorable = false;
    if (is_string($donnees)) {
        foreach ($messagesAIgnorer as $motif) {
            if (str_contains($donnees, $motif)) {
                $estIgnorable = true;
                break;
            }
        }
    }
    $succes = ($donnees === null) || $estIgnorable || empty($donnees['result']);
    return ['codeRetour' => $succes ? 0 : 1, 'reponse' => $donnees, 'commande' => $commande];
}

function EnvoyerToutesLesCommandes(string $token, array $commandes): array
{
    $resultats = [];

    foreach ($commandes as $commande) {
        $resultat = EnvoyerCommandeApi($token, $commande);
        $resultats[] = $resultat;

        if ($resultat['codeRetour'] != 0) {
            break;
        }
    }

    return $resultats;
}
