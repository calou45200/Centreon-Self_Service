<?php
// Fichier Serveur-Dedie/include/global/DBFunctions/DbFunctionsACL.php
    // A activer pour débugger
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
// Fonctions pour gérer les accès et les autorisations dans la base de données
require_once __DIR__ . '/../global/DbFunctions.php';
// Sera envoyé par un post pour récupérer les ressources ACL d'un utilisateur
// Serveur-Dedie-ikoula\include\global\LDAP\AuthCentreon.php
require_once __DIR__ . '/../LDAP/AuthCentreon.php';

function IsAdminUser($UserName)
{
    global $AdminCentreon;
    if (isset($AdminCentreon)) {
        return $AdminCentreon;
    }
    $pdo = connecterBDD();
    $req = $pdo->prepare('SELECT contact_admin FROM centreon.contact WHERE contact_alias = :name');
    $req->execute(['name' => $UserName]);
    $resultat = $req->fetch(PDO::FETCH_ASSOC);
    $AdminCentreon = !empty($resultat) && isset($resultat['contact_admin']) && $resultat['contact_admin'] == 1;
    return $AdminCentreon;
}

function get_auth_ressource_info($pdo) {
    // echo "<BR>get_auth_ressource_info pdo</BR>";
    // var_dump($pdo);
    try {
        $req = $pdo->prepare("SELECT ari_name, ari_value FROM auth_ressource_info WHERE ari_name IN ('bind_dn','bind_pass','group_base_search')");
        // $req->debugDumpParams();
        $req->execute();
        $rows = $req->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        echo "<BR>Erreur PDO : " . $e->getMessage() . "</BR>";
        return [];
    }
    // echo "<BR>Resultat get_auth_ressource_info : </BR>";
    // var_dump($rows);
    return $rows;
}

// recherche ID d'un groupe ACL en fonction de son nom 

function RechercheIDGroupACL($GroupeCentreon) {
    global $ldap_host, $users_ou;
    // echo "<BR>RechercheIDGroupACL : GroupeCentreon = $GroupeCentreon</BR>";
    require_once __DIR__ . '/../../config.php';
    $pdo=connecterBDD();
    try {
        $req = $pdo->prepare('
        SELECT 
            AGN.acl_group_id,
            AGN.acl_group_name
        FROM centreon.acl_groups AS AGN 
        WHERE AGN.acl_group_name =:GroupeCentreon;'
        );
        $req->execute(['GroupeCentreon' => $GroupeCentreon]);
        // $req->debugDumpParams();
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        $AclGroups = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
        echo "<BR> " . $message . "</BR>";
    }        



    return $rows;
}

// Liste les ID des groupes ACL d'un utilisateur en fonction de son nom ou Le nom du groupe AD
function RechercheAclGroup($pdo,$ContactName)
{
    try {
        $req = $pdo->prepare('
        SELECT 
            AGN.acl_group_id,
            AGN.acl_group_name
        FROM centreon.contact AS CT 
        LEFT JOIN centreon.contactgroup_contact_relation AS CGR ON CGR.contact_contact_id = CT.contact_id 
        LEFT JOIN centreon.contactgroup AS CG ON CG.cg_id=CGR.contactgroup_cg_id 
        LEFT JOIN centreon.acl_group_contactgroups_relations AS AGCGR ON AGCGR.cg_cg_id=CG.cg_id 
        LEFT JOIN centreon.acl_groups AS AGN ON AGN.acl_group_id=AGCGR.acl_group_id 
        WHERE CT.contact_name =:ContactName;'
        );
        $req->execute(['ContactName' => $ContactName]);
        // $req->debugDumpParams();
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_map(fn($row) => array_map(fn($v) => $v === null ? "" : $v, $row), $rows);
        $AclGroups = array_combine(array_map(fn($i) => "AG_ID$i", array_keys($rows)), $rows);
    }
    catch (PDOException $e) {
        $AclGroups = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
        echo "<BR> " . $message . "</BR>";
    }
    // si le groupe ACL est vide, on recherche via la connexion LDAP les groupes Centreon de l'utilisateur
    // exemple re groupe LDAP : cn=AG_DBA,ou=centreon,ou=Application,dc=communaute-omr,dc=fr
    global $ldap_host, $users_ou,$base_dn,$userLDAP;
    if (empty($AclGroups) || empty(array_filter(array_column($AclGroups, 'acl_group_id')))) {
        // echo "<BR>Le groupe ACL est vide, on recherche via la connexion LDAP les groupes Centreon de l'utilisateur</BR>";
        // Connexion LDAP pour récupérer les groupes de l'utilisateur
        $LDAPGroups = RechercheGroupeLDAP($ContactName);
        $AclGroups=RechercheIDGroupACL($LDAPGroups[0]["acl_group_name"] ?? $LDAPGroups[0] ?? '');
    }   
    // echo "<BR>Resultat RechercheLDAPGroup :" . $LDAPGroups[0]['acl_group_name'] ." </BR>";
    return $AclGroups;
}
// fonction pour lister les filtres sur les hostcategories d'un utilisateur en fonction de ses groupes ACL
function rechercheFiltreHC()
{
    global $DroitsAclResources,$AclGroup,$AclResName,$aclResId,$FiltreHC;
    $DroitAclGroupName=$AclGroup[0]["acl_group_name"];
    $DroitAclGroupID=$AclGroup[0]["acl_group_id"];
    $FilterHostCategory = [];
    // echo "<BR></BR>";
    // echo "<BR>Debut RechercheFilteHC : AclGroup = " . $DroitAclGroupName . " -> AclGroupID = " . $DroitAclGroupID . "</BR>";
    // var_dump($DroitsAclResources);
    // $row = $DroitsAclResources[0];
    // $allHostCategories = (int) $row['all_hostcategories'];
    $db = connecterBDD();
    $FiltreHC = $db->prepare(
   "
    SELECT HC.hc_id , HC.hc_name
    FROM centreon.acl_resources_hc_relations AS ARHR
    LEFT JOIN centreon.hostcategories AS HC ON HC.hc_id=ARHR.hc_id
    WHERE ARHR.acl_res_id=:aclResId;
    ");
    $FiltreHC->execute(['aclResId' => $aclResId]);

    return $FiltreHC->fetchAll(PDO::FETCH_ASSOC);
}

function FiltreTemplateFromHC()
{
    global $DroitsAclHosts, $DroitsAclResources, $AdminCentreon,$FiltreHC,$hostFilter,$DroitsAclTemplate;
    // var_dump($DroitsAclHosts);
    // echo "<BR></BR>";
    // echo "<BR>FiltreHC depuis FiltreTemplateFromHC :</BR>";
    // var_dump($FiltreHC);
        $hcIds = array_column($FiltreHC, 'hc_id');
    // echo "<BR>hcIds depuis FiltreHostFromHC :</BR>";
    // var_dump($hcIds);
  
    $hostNames = array_column($DroitsAclHosts, 'host_name');
    // echo "<BR>hostNames depuis FiltreHostFromHC :</BR>";
    // var_dump($hostNames);
    $hcIdsStr = implode(',', array_map('intval', $hcIds));
    // echo "<BR>hcIdsStr depuis FiltreHostFromHC : ' . $hcIdsStr .'</BR>";
    $hostNamesStr = implode(',', array_map(function($n) { return '"'.addslashes($n).'"'; }, $hostNames));
    // echo "<BR>hostNamesStr depuis FiltreHostFromHC : ' . $hostNamesStr .'</BR>";
 
$whereClause = !empty($hcIds) ? 'WHERE hc_id IN  (' . implode(',', array_fill(0, count($hcIds), '?')) . ')' : 'WHERE 1=1';
    $db = connecterBDD();
    $TemplateFilter = $db->prepare(
        "
        SELECT DISTINCT
        HTPL.host_id AS host_id,
        HTPL.host_name AS host_name
        FROM centreon.hostcategories AS HC
        LEFT JOIN centreon.hostcategories_relation AS HCRH ON HCRH.hostcategories_hc_id=HC.hc_id
        LEFT JOIN centreon.host AS HTPL ON HTPL.host_id=HCRH.host_host_id
        LEFT JOIN centreon.host_template_relation AS HTR ON HTR.host_tpl_id=HTPL.host_id
        LEFT JOIN centreon.host AS HST ON  HTPL.host_register='0'
        $whereClause
        AND HTPL.host_name LIKE 'TH_%'
        ORDER BY HTPL.host_name;
        "
    );
    // echo "<BR>FiltreTemplateFromHC : Requete SQL pour filtrer les templates par catégories d'hôtes :</BR>";
    // $TemplateFilter->debugDumpParams();
    $TemplateFilter->execute();
    $DroitsAclTemplate=$TemplateFilter->fetchAll(PDO::FETCH_ASSOC);
    // echo "<BR></BR>";
    // echo "<BR>FiltreTemplateFromHC : L'utilisateur a accès uniquement aux Templates filtrés par les catégories d'hôtes</BR>";
    // var_dump($DroitsAclTemplate);
    // Filtrer les hôtes en fonction des catégories d'hôtes autorisées
    // Récupérer les catégories d'hôtes autorisées pour l'utilisateur
    // Implémenter la logique de filtrage ici
  
    return $DroitsAclTemplate;

}
function FiltreHostFromHC()
{
    global $DroitsAclHosts, $DroitsAclResources, $AdminCentreon,$FiltreHC,$hostFilter;
    // var_dump($DroitsAclHosts);
    // echo "<BR>FiltreHC depuis FiltreHostFromHC :</BR>";
    // var_dump($FiltreHC);
    $hcIds = array_column($FiltreHC, 'hc_id');
    // echo "<BR>hcIds depuis FiltreHostFromHC :</BR>";
    // var_dump($hcIds);
  
    $hostNames = array_column($DroitsAclHosts, 'host_name');
    // echo "<BR>hostNames depuis FiltreHostFromHC :</BR>";
    // var_dump($hostNames);
    $hcIdsStr = implode(',', array_map('intval', $hcIds));
    // echo "<BR>hcIdsStr depuis FiltreHostFromHC : ' . $hcIdsStr .'</BR>";
    $hostNamesStr = implode(',', array_map(function($n) { return '"'.addslashes($n).'"'; }, $hostNames));
    // echo "<BR>hostNamesStr depuis FiltreHostFromHC : ' . $hostNamesStr .'</BR>";
  
    // Aucun filtre catégorie d'hôtes : on garde la liste des hôtes telle quelle
    if (empty($hcIds)) {
        return $DroitsAclHosts;
    }
    $db = connecterBDD();
    $hostFilter = $db->prepare(
        "
        SELECT DISTINCT
        HST.host_name AS host_name,
        NHR.nagios_server_id AS poller_id
        FROM centreon.hostcategories AS HC
        LEFT JOIN centreon.hostcategories_relation AS HCRH ON HCRH.hostcategories_hc_id=HC.hc_id
        LEFT JOIN centreon.host AS H ON H.host_id=HCRH.host_host_id
        LEFT JOIN centreon.host_template_relation AS HTR ON HTR.host_tpl_id=H.host_id
        LEFT JOIN centreon.host AS HST ON (
            (H.host_register='1' AND HST.host_id=H.host_id)
            OR (H.host_register='0' AND HST.host_id=HTR.host_host_id)
        )
        LEFT JOIN centreon.ns_host_relation AS NHR ON NHR.host_host_id=HST.host_id
        WHERE hc_id IN ($hcIdsStr)
        AND HST.host_name IN ($hostNamesStr)
        AND (HST.host_register='1' OR HST.host_register IS NULL)
        AND (HST.host_activate='1' OR HST.host_activate IS NULL);
        "
    );
    $hostFilter->execute();
    $DroitsAclHosts=$hostFilter->fetchAll(PDO::FETCH_ASSOC);
    // echo "<BR></BR>";
    // echo "<BR>FiltreHostFomHC : L'utilisateur a accès uniquement aux hôtes filtrés par les catégories d'hôtes</BR>";
    // var_dump($DroitsAclHosts);
    // Filtrer les hôtes en fonction des catégories d'hôtes autorisées
    // Récupérer les catégories d'hôtes autorisées pour l'utilisateur
    // Implémenter la logique de filtrage ici
  
    return $DroitsAclHosts;
}




function ListeDroitsUtilisateurs($UserName)
{
    global $DroitsAclPollers, $DroitsAclHosts, $AclGroup,$FiltreHC,$aclResId,$hostFilter,$DroitsAclTemplate;
// global $DroitsAclPollers, $DroitsAclHosts, $AclGroup, $DroitsAclResources, $AdminCentreon, $aclResId, $allHosts, $allHostgroups, $allServicegroups;
    $pdo = connecterBDD();
    $AdminCentreon = IsAdminUser($UserName);
    //echo "<BR>AdminCentreon : " . ($AdminCentreon ? 'true' : 'false') . "</BR>";
    if ($AdminCentreon) {
        $AclGroup = ['0' => ['acl_group_id' => null, 'acl_group_name' => 'Admin Centreon']];
    } else {
        // Recherche des groupes ACL de l'utilisateur Contrôlé OK
        $AclGroup = RechercheAclGroup($pdo, $UserName);
    }
    // Recherche des DroitsAclResources de l'utilisateur Contrôlé OK
    $DroitsAclResources = RechercheAclGroupResourcesGlobal($pdo, $AclGroup);
    $row = $DroitsAclResources[0];
    //Contrôle si les différentes case all dans les resources ACL sont à 1 ou 0
    $aclResId = (int) $row['acl_res_id'];
    // echo "<BR>ListeDroitsUtilisateurs : aclResId = " . $aclResId . "</BR>";
    // si allhosts = 1, l'utilisateur a accès à tous les hôtes pour cimplifier l'administration des ACL, 
    // Dans tous les cas on contrôle si il y a des filtres Poller et le HC
    $allHosts = (int) $row['all_hosts'];
    // echo "<BR>ListeDroitsUtilisateurs : allHosts = " . $allHosts . "</BR>";
    //fonction ListAclPoller($pdo, $GroupId) est appelée pour récupérer les pollers auxquels l'utilisateur a accès 
    // en fonction de ses groupes ACL.
    $DroitsAclPollers = ListAclPoller($pdo, $AclGroup);
    // Recherche des hosts en fonction des pollers autorisés avant filtrage des hostCategories
    $DroitsAclHosts = ListHostFromPoller($pdo, $DroitsAclPollers);
    // echo "<BR>ListeDroitsUtilisateurs DroitsAclHosts avant filtrage des HC: </BR>";
    // var_dump($DroitsAclHosts);
    // Recherche des filtres sur les catégories d'hôtes pour l'utilisateur
    $FiltreHC=RechercheFiltreHC();
    // echo "<BR></BR>";
    // echo "<BR>ListeDroitsUtilisateurs FiltreHC: </BR>";
    // var_dump($FiltreHC);
    $DroitsAclTemplate=FiltreTemplateFromHC();
    $_SESSION['DroitsAclTemplate'] = $DroitsAclTemplate;
    // echo "<BR></BR>";
    // echo "<BR>ListeDroitsUtilisateurs FiltreTemplateFromHC: </BR>";
    // var_dump($DroitsAclTemplate);

    FiltreHostFromHC();
    // echo "<BR></BR>";
    // echo "<BR>ListeDroitsUtilisateurs DroitsAclHosts après filtrage des HC: </BR>";
    // var_dump($hostFilter);
    $_SESSION['DroitsAclHosts'] = $hostFilter;
    echo "<BR></BR>";
    echo "----------- Cas restant à traiter : -------------</BR>";
    // si allhostgroups = 1, l'utilisateur a accès à tous les groupes d'hôtes
    // Dans tous les cas on contrôle si il y a des filtres Poller et le HC
    $allHostgroups = (int) $row['all_hostgroups'];
    echo "<BR>ListeDroitsUtilisateurs : allHostgroups = " . $allHostgroups . "</BR>";
    // si allservicegroups = 1, l'utilisateur a accès à tous les groupes de services, sinon il est limité aux groupes de services de ses groupes ACL
    $allServicegroups = (int) $row['all_servicegroups'];
    echo "<BR>ListeDroitsUtilisateurs : allServicegroups = " . $allServicegroups . "</BR>";

    // Variables à exporter :

}




function ListeHostFromACL($pdo,$AclGroup)
{
    global $DroitsAclPollers, $DroitsAclHosts, $DroitsAclResources, $AdminCentreon, $HostACL;
    echo "<BR>Debut ListeHostFromACL </BR>";
    echo "<BR>DroitsAclPollers </BR>";
    var_dump($DroitsAclPollers);
    ListHostFromPoller($pdo, $DroitsAclPollers);
    $IdsAclGroup = array_values(array_filter(array_column((array) $AclGroup, 'acl_group_id')));    
$whereClause = !empty($IdsAclGroup) ? 'WHERE ACLRGR.acl_group_id IN (' . implode(',', array_fill(0, count($IdsAclGroup), '?')) . ')' : '';    try {
        $req = $pdo->prepare('
            SELECT 	DISTINCT HST.host_name,HST.host_id
            FROM centreon.acl_groups AS ACLG
            LEFT JOIN centreon.acl_res_group_relations AS ACLRGR ON ACLRGR.acl_group_id=ACLG.acl_group_id 
            LEFT JOIN centreon.acl_resources AS ACLR ON ACLR.acl_res_id=ACLRGR.acl_res_id
            LEFT JOIN centreon.acl_resources_hg_relations AS ACLHGR ON ACLHGR.acl_res_id=ACLRGR.acl_res_id
            LEFT JOIN centreon.hostgroup AS HG ON HG.hg_id=ACLHGR.hg_hg_id
            LEFT JOIN centreon.hostgroup_relation AS HGR ON HGR.hostgroup_hg_id=HG.hg_id
            LEFT JOIN centreon.host AS HST ON HST.host_id=HGR.host_host_id
            '
             . $whereClause. ';'
        );
         echo "<BR>ListeHostFromACL : Requete ACL Hosts : </BR>";
        $req->debugDumpParams();
        $req->execute($IdsAclGroup);

        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        $HostACL = array_combine(array_map(fn($i) => "AG_ID$i", array_keys($rows)), $rows); 
    }
    catch (PDOException $e) {
        $HostACL = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
    }
    return $HostACL;
}


// recherche les ressources globales sans filtres ACL d'un groupe ACL en fonction de son ID
// 0= aucun droit
// 1 = tous les droits
// Retourne un tableau nom du droit --> Valeur du droit
function RechercheAclGroupResourcesGlobal($pdo, $GroupId){
   
    try {
        $IdsAclGroup = array_filter(array_column((array) $GroupId, 'acl_group_id'));
        $whereClause = !empty($IdsAclGroup) ? 'WHERE ARGR.acl_group_id IN (' . implode(',', array_fill(0, count($IdsAclGroup), '?')) . ')' : '';
        $req = $pdo->prepare('
            SELECT AR.acl_res_id
                ,AR.all_hosts AS all_hosts
                ,AR.all_hostgroups AS all_hostgroups
                ,AR.all_servicegroups AS all_servicegroups
            FROM centreon.acl_res_group_relations ARGR
            LEFT JOIN centreon.acl_resources AS AR ON AR.acl_res_id=ARGR.acl_res_id
            ' . $whereClause . ';'
        );
        $req->execute($IdsAclGroup);
        // echo "<BR> Requete ACL GROUP : <BR>";
        // $req->debugDumpParams();
        $resultat = $req->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        $resultat = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
    }
    return $resultat;
}
// Fonction pour lister les pollers en fonction des ressources ACL d'un utilisateur
function ListAclPoller($pdo, $GroupId){
    global $AdminCentreon;
    // var_dump($GroupId);
    // echo "<BR>GroupId : " . var_dump($GroupId) . "</BR>";

    // echo "<BR>Resultat liste poller filter ";
    try {
        if ($AdminCentreon) {
            $Pollers = $pdo->prepare(
                'SELECT id AS "poller_id"
                , name
                FROM centreon.nagios_server AS NS;'
            );    
            $Pollers->execute();
            $resultat = $Pollers->fetchAll(PDO::FETCH_ASSOC);
            return $resultat;
        }
        $IdAclGroup = array_values(array_filter(array_column((array) $GroupId, 'acl_group_id')));
        //if (empty($IdAclGroup)) { return []; }
        $whereClause = !empty($IdAclGroup) ? 'WHERE ARGR.acl_group_id IN (' . implode(',', array_fill(0, count($IdAclGroup), '?')) . ')' : '';
        $Pollers = $pdo->prepare(
            'SELECT ARPR.poller_id
            ,NS.name
            FROM centreon.acl_resources_poller_relations ARPR
            LEFT JOIN centreon.acl_res_group_relations ARGR ON ARGR.acl_res_id = ARPR.acl_res_id
            LEFT JOIN centreon.nagios_server AS NS ON NS.id=ARPR.poller_id
            ' . $whereClause . ';'
            );
        $Pollers->execute($IdAclGroup);
        //$Pollers->debugDumpParams();
        $resultat = $Pollers->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultat)) {
            // echo "Si resultat vide : </BR>";
            $Pollers = $pdo->prepare(
                'SELECT id AS "poller_id"
                , name
                FROM centreon.nagios_server AS NS;'
            );    
            $Pollers->execute();
            $resultat = $Pollers->fetchAll(PDO::FETCH_ASSOC);
            // echo "Depuis ListAclPoller Résultat vide (Tous les pollers sont affichés) : </BR>"; 
            // var_dump($resultat);
            return $resultat;
        }
        // echo "Depuis ListAclPoller (Un filtre sur les pollers est appliqué) : </BR>";         
        // var_dump($resultat);
        return $resultat;
    }
    catch (PDOException $e) {
        $Pollers = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
        return $Pollers;
    }

}
function ListHostFromPoller($pdo, $PollerId){
    try {
        $IdsPoller = array_values(array_filter(array_column((array) $PollerId, 'poller_id')));
        $whereClause = !empty($IdsPoller) ? 'WHERE NHR.nagios_server_id IN (' . implode(',', array_fill(0, count($IdsPoller), '?')) . ')' : '';        
        $hosts = $pdo->prepare(
            'SELECT NHR.nagios_server_id AS Poller_id
 	        ,HST.host_name
 	        ,HST.host_id  
            FROM centreon.host AS HST
            LEFT JOIN centreon.ns_host_relation AS NHR ON NHR.host_host_id=HST.host_id
            ' . $whereClause . ' 
            ORDER BY HST.host_name;'
        );
        $hosts->execute($IdsPoller);
        return $hosts->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        $hosts = [];
        $message = 'Impossible de se connecter au serveur : ' . $e->getMessage();
        return $hosts;
    }
}

