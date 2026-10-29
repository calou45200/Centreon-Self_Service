<?php
//fichier Serveur-Dedie/include/DBFunctions/DBFunctionsUsers.php
require_once __DIR__ . "/../global/DbFunctions.php";
function RechercheUser(string $UserAlias){
    
    $db = connecterBDD();
    $UserCredentials = $db->prepare(
        "Select CP.password AS PWD 
        from centreon.contact AS CT 
    LEFT JOIN centreon.contact_password AS CP ON CP.contact_id=CT.contact_id
    Where CT.contact_alias=:alias;"        )
     ;
    
    $UserCredentials->execute(['alias' => $UserAlias]);
    
    return $UserCredentials->fetchAll(PDO::FETCH_ASSOC);
    
} 
?>