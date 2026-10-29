<?php
// Fichier Serveur-Dedie/include/command/clapi/SubmitAddService.php
require_once __DIR__ . '/../CommandGeneric.php';

//---------------------------------------------------
// Function pour générer un tableau  du fichier clapi
//---------------------------------------------------
function nettoyerDonnees(array $donnees): array
{

    return [
        'ServiceName'     => nettoyerPourCsv($donnees['name'] ?? ''),
        'ServiceTemplate' => nettoyerPourCsv($donnees['TS_Name'] ?? ''),
        'HostAlias'    => nettoyerPourCsv($donnees['alias'] ?? ''),
        'HostAddress'  => nettoyerPourCsv($donnees['address'] ?? ''),
        'PollerName'   => nettoyerPourCsv($donnees['poller_name'] ?? ''),
    ];
}

// function CreeNomFichierHostCSV($Command,$Host,$Address){
//     $dossierCsv = __DIR__ . '/exports/'; // dossier dédié, plus propre qu'à la racine
    
//     // On s'assure que le dossier existe
//     if (!is_dir($dossierCsv)) {
//         mkdir($dossierCsv, 0755, true);
//     }
    
//     // Nettoyage des valeurs utilisées dans le nom de fichier
//     // (on retire tout ce qui n'est pas alphanumérique, tiret ou underscore)
    
//     $datestamp = date('Ymd'); // ex: 20260806
//     $timestamp = date('His'); // ex: 141503 pour 14H15m03s
    
//     $dossierClapi = $dossierCsv . "{$datestamp}/"; // dossier dédié, plus propre qu'à la racine
    
//     // On s'assure que le dossier existe
//     if (!is_dir($dossierClapi)) {
//         mkdir($dossierClapi, 0755, true);
//     }
    
    
//     $nomFichier = "{$Command}_{$Host}_{$Address}.csv";
//     $cheminComplet = $dossierClapi . $nomFichier;
//     return $cheminComplet;
// }

function CreeNomFichierServiceCSV($Command,$Host,$Service){
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
    
    
    $nomFichier = "{$Command}_{$Host}_{$Service}.csv";
    $cheminComplet = $dossierClapi . $nomFichier;
    return $cheminComplet;
}

//----------------------------------------
// Function pour appeler l'api de Centreon
//----------------------------------------

// function creeFichierAddHost(array $Clapi, bool $force)
// {
//     $FichierClapi = CreeNomFichierHostCSV("ADD", $Clapi['HostName'], $Clapi['HostAddress']);
    
//     $fp = fopen($FichierClapi, 'w');
//     if ($fp === false) {
//         die('Impossible de créer le fichier CSV.');
//     }
    
//     if (flock($fp, LOCK_EX)) {
//         if ($force) {
//             $ligne1 = "HOST;setparam;{$Clapi['HostName']};alias;{$Clapi['HostAlias']}" . PHP_EOL;
//             $ligne2 = "HOST;setparam;{$Clapi['HostName']};address;{$Clapi['HostAddress']}" . PHP_EOL;
//             $ligne3 = "HOST;settemplate;{$Clapi['HostName']};{$Clapi['HostTemplate']}" . PHP_EOL;
//             $ligne4 = "HOST;applytpl;{$Clapi['HostName']}" . PHP_EOL;
            
//             fwrite($fp, $ligne1);
//             fwrite($fp, $ligne2);
//             fwrite($fp, $ligne3);
//             fwrite($fp, $ligne4);
//         } else {
//             $ligne1 = "HOST;ADD;{$Clapi['HostName']};{$Clapi['HostAlias']};{$Clapi['HostAddress']};{$Clapi['HostTemplate']};{$Clapi['PollerName']};" . PHP_EOL;
//             $ligne2 = "HOST;settemplate;{$Clapi['HostName']};{$Clapi['HostTemplate']}" . PHP_EOL;
//             $ligne3 = "HOST;applytpl;{$Clapi['HostName']}" . PHP_EOL;
            
//             fwrite($fp, $ligne1);
//             fwrite($fp, $ligne2);
//             fwrite($fp, $ligne3);
//         }
        
//         flock($fp, LOCK_UN);
//     }
    
//     fclose($fp);
//     echo "Fichier clapi généré : " . $FichierClapi;
//     return $FichierClapi;
// }
function creeFichierAddService(array $Clapi, bool $force)
{
    $FichierClapi = CreeNomFichierHostCSV("ADD", $Clapi['HostName'], $Clapi['HostAddress']);
    
    $fp = fopen($FichierClapi, 'w');
    if ($fp === false) {
        die('Impossible de créer le fichier CSV.');
    }
    
    if (flock($fp, LOCK_EX)) {
        if ($force) {
            $ligne1 = "HOST;setparam;{$Clapi['HostName']};alias;{$Clapi['HostAlias']}" . PHP_EOL;
            $ligne2 = "HOST;setparam;{$Clapi['HostName']};address;{$Clapi['HostAddress']}" . PHP_EOL;
            $ligne3 = "HOST;settemplate;{$Clapi['HostName']};{$Clapi['HostTemplate']}" . PHP_EOL;
            $ligne4 = "HOST;applytpl;{$Clapi['HostName']}" . PHP_EOL;
            
            fwrite($fp, $ligne1);
            fwrite($fp, $ligne2);
            fwrite($fp, $ligne3);
            fwrite($fp, $ligne4);
        } else {
            $ligne1 = "HOST;ADD;{$Clapi['HostName']};{$Clapi['HostAlias']};{$Clapi['HostAddress']};{$Clapi['HostTemplate']};{$Clapi['PollerName']};" . PHP_EOL;
            $ligne2 = "HOST;settemplate;{$Clapi['HostName']};{$Clapi['HostTemplate']}" . PHP_EOL;
            $ligne3 = "HOST;applytpl;{$Clapi['HostName']}" . PHP_EOL;
            
            fwrite($fp, $ligne1);
            fwrite($fp, $ligne2);
            fwrite($fp, $ligne3);
        }
        
        flock($fp, LOCK_UN);
    }
    
    fclose($fp);
    echo "Fichier clapi généré : " . $FichierClapi;
    return $FichierClapi;
}
function LanceClapi(string $File,$Type)
{
    require_once '../../../config.php';
    //Les variables sont définies dans le fichier config.php
    //$clapiuser = "cs_clapi";
    //$clapiPWD = "Nemours*77140";
    $CommandClapi = "/usr/share/centreon/bin/centreon -u " . $clapiuser . " -p " . $clapiPWD . " -" . $Type . " " . $File;
//    echo '<pre>';
//    print_r($CommandClapi);
//   echo '</pre>';
    exec( $CommandClapi, $sortie, $codeRetour);
    $sortieTexte = implode("\n", $sortie);
    if (preg_match('/Missing parameters|Object not found/i', $sortieTexte)) {
        $codeRetour = 1;
    }
    
    //echo "Code de retour : $codeRetour <BR>";
    if ($codeRetour != 0){
        echo implode("\n", $sortie);
        echo "<BR>";
    }
    return $codeRetour;
}


function RechercheHostExistant($HostName)
{
    // on recherche en base si l'hôte existe déjà pour afficher un message d'avertissement par défaut OUI
    $FgExist=TRUE;
    // requete de contrôle
    require_once '/etc/centreon/centreon.conf.php';
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
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM centreon.host WHERE host_name = :name');
        $stmt->execute(['name' => $HostName]);
//        $resultat = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $resultat = $stmt->fetchColumn();
    }
    catch (PDOException $e) {
        $resultat = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
    }
}

function ImportHost(array $Clapi, bool $force)
{
    $existe = RechercheHostExistant($Clapi['HostName']) > 0;
    
    if ($existe && !$force) {
        // Ne devrait normalement pas arriver (le JS a déjà demandé confirmation),
        // mais on protège quand même le script d'un appel direct/contourné.
        echo "<STRONG style='color: red;'>L'hôte {$Clapi['HostName']} existe déjà. Import annulé.</STRONG>";
        return;
    }
    
    // 2- on crée le fichier
    $nomFichier = creeFichierAddHost($Clapi, $force);
   // echo "Fichier créé : " . htmlspecialchars($nomFichier, ENT_QUOTES, 'UTF-8') . "<BR>";
    
    // 3- on importe le fichier dans Centreon
    $codeRetour = LanceClapi($nomFichier, 'i');
    
    // 4- on informe l'utilisateur
    if ($codeRetour === 0) {
        echo "<STRONG style='color: green;'>Import réussi pour {$Clapi['HostName']}</STRONG><BR>";
    } else {
        echo "<STRONG style='color: red;'>Échec de l'import pour {$Clapi['HostName']}</STRONG><BR>";
    }
}
$champs = nettoyerDonnees($_POST);
$force  = ($_POST['force'] ?? '0') === '1';
ImportHost($champs, $force);

?>
