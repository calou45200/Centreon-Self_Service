<?php
// Fichier Serveur-Dedie/include/ajout/Dependencies/ServiceDependencies.php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
require_once '/etc/centreon/centreon.conf.php';
             // /include/global/DbFunctions.php
require_once __DIR__ . '/../../global/DbFunctions.php';
// /include/DBFunctions/DbFunctionsServicesDep.php
require_once __DIR__ . '/../../DBFunctions/DbFunctionsServicesDep.php';
/** @var array<string, string> $conf_centreon */
// Pour débugger
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
$CENTREON_DB = $conf_centreon['db'];

$POLLER   = '';
$OK       = 0;
$WARNING  = 1;
$CRITICAL = 2;
$UNKNOWN  = 3;


function ImportDependancies($Fichier_Clapi) {
    global $TypeEnvoi;
    $lignes = file($Fichier_Clapi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    if ($TypeEnvoi === 'restapi') {
        // découpage des lignes clapi (object;action;values) en commandes structurées pour l'API REST
        $token = AuthentifierApi();
        $commandes = [];
        foreach ($lignes as $ligne) {
            $parties = explode(';', $ligne);
            $commandes[] = [
                'object' => $parties[0] ?? '',
                'action' => $parties[1] ?? '',
                'values' => implode(';', array_slice($parties, 2)),
            ];
        }
        EnvoyerToutesLesCommandes($token, $commandes);
    } else {
        $FichierCsv = ecrireFichierCsv($Fichier_Clapi, $lignes);
        // Affichage du contenu du fichier clapi pour vérification
        // echo '<pre>' . htmlspecialchars(file_get_contents($Fichier_Clapi), ENT_QUOTES, 'UTF-8') . '</pre>';
        // Affichage du nom du fichier clapi pour vérification
        // echo '<pre>' . htmlspecialchars($Fichier_Clapi, ENT_QUOTES, 'UTF-8') . '</pre>';
        // Affichage du nom du fichier CSV pour vérification
        // echo '<pre> Import Clapi : ' . $FichierCsv . '</pre>';
        // echo '<pre> Nombre de ligne : ' . count($lignes) . '</pre>';
        $Resultat = LanceClapi($FichierCsv, 'i');
        if ($Resultat['codeRetour'] !== 0) {
            error_log('CLAPI import échoué (' . $FichierCsv . ') : ' . implode(' | ', $Resultat['sortie']));
        }
    }
}

function cleanName($str) {
    return str_replace([':', '/'], '', $str);
}

function ListeDependance($Arg1, $Arg2) {
    global $REP_TEMP;

    $Vecteur = strtoupper($Arg1);
    $Host    = strtoupper($Arg2);

    $requete_sql = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}.sql");
    $Dep_Clapi   = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.clapi");
    $Temp_Clapi  = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.tmp");
    $Temp_Ping   = $REP_TEMP . '/' . cleanName("{$Host}_PING_Dependancies.tmp");

    $Maitre = '';
    if ($Vecteur === 'NRPE') {
        $lines = @file($Temp_Ping, FILE_IGNORE_NEW_LINES) ?: [];
        $matches = array_values(array_filter($lines, fn($l) => preg_match('/NRPE|NSClient/i', $l)));
        if (!empty($matches)) {
            $last = end($matches);
            $parts = explode(',', $last);
            $Maitre = $parts[1] ?? '';
        }
    } else {
        $lines = @file($Temp_Ping, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $l) {
            if (stripos($l, $Vecteur) !== false) {
                $parts = explode(',', $l);
                $Maitre = $parts[1] ?? '';
                break;
            }
        }
    }

    if (empty($Maitre)) {
        $snmpFile = $REP_TEMP . '/' . cleanName("{$Host}_SNMP_Dependancies.tmp");
        $lines = @file($snmpFile, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $l) {
            if (stripos($l, $Vecteur) !== false) {
                $parts = explode(',', $l);
                $Maitre = $parts[1] ?? '';
                break;
            }
        }
    }

    if (empty($Maitre)) {
        return;
    }

    $lines_out = RechercheDependanceVecteur($Host, $Vecteur, $Maitre);
    file_put_contents($Temp_Clapi, implode("\n", $lines_out) . "\n");
    file_put_contents($Dep_Clapi, implode("\n", $lines_out) . "\n", FILE_APPEND);
    $NbLigne = count(file($Dep_Clapi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    if ($NbLigne > 5) {
        ImportDependancies($Dep_Clapi);
    }
}

function ListeDependancePing($Arg1, $Arg2) {
    global $REP_TEMP;

    $Vecteur = strtoupper($Arg1);
    $Host    = strtoupper($Arg2);

    $requete_sql = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}.sql");
    $Dep_Clapi   = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.clapi");
    $Temp_Clapi  = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.tmp");

    $lines_out = RechercheDependancePing($Host);
    file_put_contents($Temp_Clapi, implode("\n", $lines_out) . "\n");
    file_put_contents($Dep_Clapi, implode("\n", $lines_out) . "\n", FILE_APPEND);

    ImportDependancies($Dep_Clapi);
}

function ListService_Vecteur($Arg1, $Arg2) {
    global $REP_TEMP;

    $Vecteur = strtoupper($Arg1);
    $Host    = strtoupper($Arg2);

    $requete_sql = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}Add.sql");
    $Dep_Clapi   = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.clapi");
    $Temp_Clapi  = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_AddDependancies.tmp");
    $Temp_Ping   = $REP_TEMP . '/' . cleanName("{$Host}_PING_Dependancies.tmp");

    $Maitre = '';
    if ($Vecteur === 'NRPE') {
        $lines = @file($Temp_Ping, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $l) {
            if (preg_match('/NRPE|NSClient/i', $l)) {
                $parts = explode(',', $l);
                $Maitre = $parts[1] ?? '';
            }
        }
    } else {
        $lines = @file($Temp_Ping, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $l) {
            if (stripos($l, $Vecteur) !== false) {
                $parts = explode(',', $l);
                $Maitre = $parts[1] ?? '';
            }
        }
    }

    if ($Vecteur === 'PING') {
        $Maitre = 'ping';
    }

    if (empty($Maitre)) {
        $snmpFile = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.tmp");
        $lines = @file($snmpFile, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $l) {
            if (stripos($l, $Vecteur) !== false) {
                $parts = explode(',', $l);
                $Maitre = $parts[1] ?? '';
            }
        }
    }

    if (empty($Maitre)) {
        return;
    }

    $lines_out = RechercheServiceVecteur($Host, $Vecteur, $Maitre);

    file_put_contents($Dep_Clapi, implode("\n", $lines_out) . "\n");
}

function ListeAllDependance() {
    global $REP_TEMP;

    $List_Host = $REP_TEMP . '/hosts.lst';

    $hosts = array_column(RechercheHosts(), 'host_name');
    file_put_contents($List_Host, implode("\n", $hosts) . "\n", FILE_APPEND);

    foreach ($hosts as $Host) {
        foreach (['PING', 'SNMP', 'NRPE'] as $Vecteur) {
            CreeDependance($Vecteur, $Host);
        }
    }
}

function CreeDependance($Arg1, $Arg2) {
    global $REP_TEMP;

    $Vecteur = strtoupper($Arg1);
    $Host    = strtoupper($Arg2);

    $Dep_Clapi = $REP_TEMP . '/' . cleanName("{$Host}_{$Vecteur}_Dependancies.clapi");
    file_put_contents($Dep_Clapi, '');

    ListService_Vecteur($Vecteur, $Host);

    $content = "
DEP;SETPARAM;DEP_{$Host}_{$Vecteur};inherits_parent;1
DEP;SETPARAM;DEP_{$Host}_{$Vecteur};execution_failure_criteria;w,u,c,p
DEP;SETPARAM;DEP_{$Host}_{$Vecteur};notification_failure_criteria;w,u,c,p
DEP;SETPARAM;DEP_{$Host}_{$Vecteur};dep_comment;Dependance sur le service {$Vecteur}.";
    file_put_contents($Dep_Clapi, $content . "\n", FILE_APPEND);

    if ($Vecteur === 'PING') {
        ListeDependancePing($Vecteur, $Host);
    } else {
        ListeDependance($Vecteur, $Host);
    }
}

function InputDependencies($InputFile) {
    global $REP_TEMP, $OK, $RC;

    $hosts = file($InputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($hosts as $Host) {
        $requete_sql = $REP_TEMP . '/' . cleanName("{$Host}Add.sql");
        $rows = RechercheTemplateReseau($Host);
        $FgReseau = count(array_filter($rows, fn($v) => stripos($v, 'th_net') !== false));

        CreeDependance('ping', $Host);
        if ($FgReseau < 1) {
            CreeDependance('snmp', $Host);
            CreeDependance('nrpe', $Host);
            CreeDependance('ssh', $Host);
        }
    }
    $RC = $OK;
}

function HostDependencies($Host) {
    global $REP_TEMP, $OK, $RC;

    $requete_sql = $REP_TEMP . '/' . cleanName("{$Host}_tpl.sql");
    CreeDependance('ping', $Host);

    $rows = RechercheTemplateReseau($Host);
    $FgReseau = count(array_filter($rows, fn($v) => stripos($v, 'th_net') !== false));

    if ($FgReseau < 1) {
        CreeDependance('snmp', $Host);
        CreeDependance('nrpe', $Host);
        CreeDependance('ssh', $Host);
    }

    $RC = $OK;
}

function AllDependencies() {
    global $REP_TEMP, $OK, $RC, $Host;

    $rows = RechercheTemplateReseau($Host);
    $FgReseau = count(array_filter($rows, fn($v) => stripos($v, 'th_net') !== false));

    CreeDependance('ping', $Host);
    if ($FgReseau < 1) {
        CreeDependance('snmp', $Host);
        CreeDependance('nrpe', $Host);
    }

    $RC = $OK;
}