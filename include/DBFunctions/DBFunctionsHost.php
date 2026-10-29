<?php
// Fichier Serveur-Dedie/include/DBFunctions/DBFunctionsHost.php


function RechercheHosts(): array {
    try {
        $db = connecterBDD();
        return $db->query(
            "SELECT host_name
            FROM centreon.host
            WHERE host_register = '1'
            AND host_activate = '1'
            ORDER BY host_name"
        )->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Recherche des ID d'un host à partir de son nom
function RechercheHostId(string $HostName){
    
    $db = connecterBDD();
    $HostId = $db->prepare(
        "SELECT HST.host_id 
         FROM centreon.host AS HST
         WHERE HST.host_name = :name")
     ;
    
    $HostId->execute(['name' => $HostName]);
    
    return $HostId->fetchAll(PDO::FETCH_ASSOC);
    
}
// Recherche les Host qui sont liés aux template TH
function ListeTemplate(array $exclusion = []): array {
    $db = connecterBDD();
    
    $placeholders = [];
    $params = [];
    foreach ($exclusion as $i => $nom) {
        $cle = ':excl' . $i;
        $placeholders[] = $cle;
        $params[$cle] = $nom;
    }
    
    $sql = "SELECT host_name
            FROM centreon.host
            WHERE host_register = '0'
            AND host_activate = '1'
            AND host_name like 'TH_%'";
    
    if (!empty($placeholders)) {
        $sql .= " AND host_name NOT IN (" . implode(',', $placeholders) . ")";
    }
    
    $sql .= " ORDER BY host_name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function RechercheTemplates(): array {
    try {
        $db = connecterBDD();
        return $db->query(
            "SELECT host_id, host_name
             FROM centreon.host
             WHERE host_register = '0'
               AND host_activate = '1'
               AND host_name like 'TH%'
             ORDER BY host_name"
        )->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
// Recherche les templates d'un hote
function RechercheHostConfiguration(string $HostName){
    
    $db = connecterBDD();
    $HostTemplate = $db->prepare(
        "SELECT HST.host_name AS HOST,
     HST.host_alias AS Alias,
     HST.host_address AS address,
     HTPL.host_name AS HostTemplate
     FROM centreon.host_template_relation AS HTR
     LEFT JOIN centreon.host AS HTPL ON HTR.host_tpl_id=HTPL.host_id
     LEFT JOIN centreon.host AS HST ON HTR.host_host_id=HST.host_id
     WHERE HTPL.host_register ='0'
     AND HTPL.host_activate = '1'
     AND HST.host_register = '1'
     AND HST.host_name = :name
     ORDER BY HST.host_name,HTPL.host_name"        )
     ;
    
    $HostTemplate->execute(['name' => $HostName]);
    
    return $HostTemplate->fetchAll(PDO::FETCH_ASSOC);
    
} 
// Recherche si l'hote existe dejà dans la base de données
function RechercheHostExistant($HostName)
{
    try {
        $pdo = connecterBDD();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM centreon.host WHERE host_name = :name');
        $stmt->execute(['name' => $HostName]);
        $resultat = $stmt->fetchColumn();
    }
    catch (PDOException $e) {
        $resultat = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
    }
    return $resultat;
}
?>