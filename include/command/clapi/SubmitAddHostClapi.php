<?php
// Fichier SubmitAddHostClapi.php 
require_once '/etc/centreon/centreon.conf.php';
require_once '../../../include/global/DbFunctions.php';
require_once 'GestionClapi.php';
require_once '../../../include/ConstruitHTML/url-centreon.php';
// Rejet si champs requis vides ce qui ne devrait pas arriver depuis le formulaire
if ($_POST['name'] === '' || $_POST['address'] === '') {
    die('Champs requis manquants.');
}


//---------------------------------------------------
// Function pour Préparer les champs du fichier clapi
//---------------------------------------------------
function nettoyerPourCsv(string $valeur): string
{
    $valeur = trim($valeur);
    $valeur = str_replace([';', "\r", "\n"], '', $valeur);
    
    if (preg_match('/^[=+\-@]/', $valeur)) {
        $valeur = "'" . $valeur;
    }
    
    return $valeur;
}


function nettoyerPourNomFichier(string $valeur): string
{
    $valeur = trim($valeur);
    return preg_replace('/[^A-Za-z0-9_-]/', '_', $valeur);
}



//---------------------------------------------------
// Function pour générer un tableau  du fichier clapi
//---------------------------------------------------
function nettoyerDonnees(array $donnees): array
{

    return [
        'HostName'     => nettoyerPourCsv($donnees['name'] ?? ''),
        'HostTemplate' => nettoyerPourCsv($donnees['TH_Name'] ?? ''),
        'HostAlias'    => nettoyerPourCsv($donnees['alias'] ?? ''),
        'HostAddress'  => nettoyerPourCsv($donnees['address'] ?? ''),
        'PollerName'   => nettoyerPourCsv($donnees['poller_name'] ?? ''),
    ];
}

function CreeNomFichierHostCSV($Command,$Host,$Address){
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
    
    
    $nomFichier = "{$Command}_{$Host}_{$Address}.csv";
    $cheminComplet = $dossierClapi . $nomFichier;
    return $cheminComplet;
}

//----------------------------------------
// Function pour appeler l'api de Centreon
//----------------------------------------

function creeFichierAddHost(array $Clapi, bool $force)
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
    
    return $FichierClapi;
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
    $RetourClapi = LanceClapi($nomFichier, 'i');
    
    // 4- on informe l'utilisateur en ajoutant un lien vers centreon pour le serveur créé
    if ($RetourClapi['codeRetour'] === 0) {
        $resultat = UrlCentreonHost($Clapi['HostName']);
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
                'message' => "Échec de l'import pour {$Clapi['HostName']}",
                'url' => null,
            ],
        ]);
    }
}
$donneesRecues = json_decode(file_get_contents('php://input'), true) ?? [];
$champs = nettoyerDonnees($donneesRecues);
$force  = ($donneesRecues['force'] ?? '0') === '1';
ImportHost($champs, $force);

?>
