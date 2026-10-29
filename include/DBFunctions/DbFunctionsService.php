<?php
// Fichier Serveur-Dedie/include/DBFunctions/DbFunctionsService.php
// Serveur-Dedie-ikoula\include\DBFunctions\DbFunctionsService.php
require_once '/etc/centreon/centreon.conf.php';
/** @var array<string, string> $conf_centreon */

function RechercheHostService($hostname): array {
    try {
        $db = connecterBDD();
        $stmt = $db->prepare(
            "SELECT
                HST.host_id
                ,HST.host_name
                ,SVC.service_description
            FROM centreon.host AS HST
            LEFT JOIN centreon.host_service_relation AS HSR ON HSR.host_host_id=HST.host_id
            LEFT JOIN centreon.service AS SVC ON SVC.service_id=HSR.service_service_id
            WHERE HST.host_name=:hostname
            ORDER BY SVC.service_description"
        );
        $stmt->bindValue(':hostname', $hostname, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

Function HostServicesTemplates($hostname){
   try {
        $db = connecterBDD();
        return $db->query(
            "SELECT 
                STPL.service_description AS Sertemplate_namevice_name
               ,STPL.service_alias AS Service_Centreon
            FROM centreon.service AS STPL
            WHERE SVC.service_description like 'TS_%'
            ORDER BY SVC.service_description"
            )->fetchAll();
    } catch (PDOException $e) {
        return [];
    }

}

Function ListServicesTemplates(){
   try {
        $db = connecterBDD();
        return $db->query(
            "SELECT 
                STPL.service_description AS template_name
               ,STPL.service_alias AS Service_Centreon
            FROM centreon.service AS STPL
            WHERE STPL.service_description like 'TS_%'
            ORDER BY STPL.service_description"
            )->fetchAll();
    } catch (PDOException $e) {
        return [];
    }

}



// Gestion des macros de services
function ListMacroServices(string $hostname): array{
    try {
        $db = connecterBDD();
        $stmt = $db->prepare(
        "select DISTINCT
            HST.host_name AS HOST
            ,SVC.service_description AS SERVICE
                ,COALESCE(
                NULLIF(REPLACE(REPLACE(ODSM.svc_macro_name,'SERVICE',''),'$',''),''),
                NULLIF(REPLACE(REPLACE(ODTM.svc_macro_name,'SERVICE',''),'$',''),''),
                NULLIF(REPLACE(REPLACE(ODTMN2.svc_macro_name,'SERVICE',''),'$',''),''),
                NULLIF(REPLACE(REPLACE(ODTMN3.svc_macro_name,'SERVICE',''),'$',''),'')
            ) AS MACRO
            ,COALESCE(
                NULLIF(ODSM.svc_macro_value,''),
                NULLIF(ODTM.svc_macro_value,''),
                NULLIF(ODTMN2.svc_macro_value,''),
                NULLIF(ODTMN3.svc_macro_value,'')
            ) AS VALUE
        FROM centreon.host as HST
        LEFT JOIN centreon.host_service_relation AS HSR ON HSR.host_host_id=HST.host_id
        LEFT JOIN centreon.service AS SVC ON SVC.service_id=HSR.service_service_id
        LEFT JOIN centreon.service AS STPL ON STPL.service_id=SVC.service_template_model_stm_id
        LEFT JOIN centreon.service AS STPLN2 ON STPLN2.service_id=STPL.service_template_model_stm_id
        LEFT JOIN centreon.service AS STPLN3 ON STPLN3.service_id=STPLN2.service_template_model_stm_id
        LEFT JOIN centreon.on_demand_macro_service AS ODSM ON ODSM.svc_svc_id=SVC.service_id
        LEFT JOIN centreon.on_demand_macro_service AS ODTM ON ODTM.svc_svc_id=STPL.service_id
        LEFT JOIN centreon.on_demand_macro_service AS ODTMN2 ON ODTMN2.svc_svc_id=STPLN2.service_id
        LEFT JOIN centreon.on_demand_macro_service AS ODTMN3 ON ODTMN3.svc_svc_id=STPLN3.service_id
        WHERE HST.host_register='1'
        AND HST.host_activate='1'
        AND HST.host_name=:hostname
        AND (
            NULLIF(ODSM.svc_macro_value,'') IS NOT NULL
            OR NULLIF(ODTM.svc_macro_value,'') IS NOT NULL
            OR NULLIF(ODTMN2.svc_macro_value,'') IS NOT NULL
            OR NULLIF(ODTMN3.svc_macro_value,'') IS NOT NULL
        )
        ORDER BY SVC.service_description;
        "
        );
        $stmt->bindValue(':hostname', $hostname, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } 
    catch (PDOException $e) {
        return [];
    }
}




?>
