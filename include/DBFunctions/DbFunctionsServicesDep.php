<?php
// Fichier Serveur-Dedie/include/DBFunctions/DbFunctionsServicesDep.php
// include/global/DbFunctions.php

require_once __DIR__ . '/../global/DbFunctions.php';
require_once __DIR__ . '/../control/Traces.php';

// Gestion des dépendances de services
function RechercheDependanceVecteur(string $Host, string $Vecteur, string $Maitre): array {
    $sql = "SELECT concat('DEP;ADDCHILD;DEP_',HST.host_name ,'_{$Vecteur};',HST.host_name,',',SVC.service_description)
        FROM centreon.host AS HST
        LEFT JOIN centreon.host_service_relation AS HSR on HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service as SVC ON SVC.service_id=HSR.service_service_id
        LEFT JOIN centreon.service AS TPL_DEP ON TPL_DEP.service_id=SVC.service_template_model_stm_id
        where HST.host_activate='1'
        AND HST.host_Register='1'
        AND SVC.service_activate='1'
        AND SVC.service_register='1'
        AND TPL_DEP.service_description like 'TS%{$Vecteur}%'
        AND SVC.service_description not like '{$Maitre}'
        AND HST.host_name='{$Host}'";
    EnregistrerRequete("{$Host}_{$Vecteur}.sql", $sql);

    $db = connecterBDD();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function RechercheDependancePing(string $Host): array {
    $sql = "SELECT concat('DEP;ADDCHILD;DEP_',HST.host_name,'_PING;',HST.host_name,',',SVC.service_description)
        FROM centreon.host AS HST
        LEFT JOIN centreon.host_service_relation AS HSR on HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service as SVC ON SVC.service_id=HSR.service_service_id
        where HST.host_activate='1'
        AND HST.host_Register='1'
        AND SVC.service_activate='1'
        AND SVC.service_register='1'
        AND (SVC.service_description like '%snmp%' 
          OR SVC.service_description like '%nrpe%' 
          OR SVC.service_description like '%nsclient%' 
          OR SVC.service_description like '%HTTP%' 
          OR SVC.service_description like '%Apache%')
        AND SVC.service_description not like '%mib%'
        AND HST.host_name='{$Host}'";
    EnregistrerRequete("{$Host}_PING.sql", $sql);

    $db = connecterBDD();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function RechercheServiceVecteur(string $Host, string $Vecteur, string $Maitre): array {
    $sql = "SELECT concat('DEP;ADD;DEP_',HST.host_name ,'_{$Vecteur};DEP_',HST.host_name,'_{$Vecteur};SERVICE;',HST.host_name,',',SVC.service_description)
        FROM centreon.host AS HST
        LEFT JOIN centreon.host_service_relation AS HSR on HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service as SVC ON SVC.service_id=HSR.service_service_id
        where HST.host_activate='1'
        AND HST.host_Register='1'
        AND SVC.service_activate='1'
        AND SVC.service_register='1'
        AND SVC.service_description like '%{$Maitre}'
        AND SVC.service_description not like '%mib%'
        AND HST.host_name='{$Host}'";
    EnregistrerRequete("{$Host}_{$Vecteur}Add.sql", $sql);

    $db = connecterBDD();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function RechercheTemplateReseau(string $Host): array {
    $sql = "SELECT TPL.host_name
        FROM centreon.host as HST
        LEFT JOIN centreon.host_template_relation HTR ON HTR.host_host_id=HST.host_id
        LEFT JOIN centreon.host as TPL ON TPL.host_id=HTR.host_tpl_id
        WHERE HST.host_name='{$Host}'
        AND TPL.host_name like 'TH_Net%'";
    EnregistrerRequete("{$Host}_tpl.sql", $sql);

    $db = connecterBDD();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>