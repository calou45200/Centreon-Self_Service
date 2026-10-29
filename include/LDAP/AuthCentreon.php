<?php
// Fichier Serveur-Dedie/include/LDAP/AuthCentreon.php
// vérifie qu'une session Centreon est ouverte, renvoie ID + nom utilisateur

require_once '/usr/share/centreon/www/class/centreon.class.php';
require_once '/usr/share/centreon/www/class/centreonSession.class.php';
require_once __DIR__ . '/../../config.php';

session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
function ldap_check_user($uid, $password) {
    global $ldap_host, $users_ou;

    $conn = ldap_connect($ldap_host);
    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

    $user_dn = "uid=" . ldap_escape($uid, "", LDAP_ESCAPE_DN) . "," . $users_ou;

    // Bind avec les identifiants de l'utilisateur = vérifie le mot de passe
    $bind = @ldap_bind($conn, $user_dn, $password);
     if ($bind) {
        ldap_unbind($conn);
        return true;
    }

    ldap_unbind($conn);
    return false;
}

function verifierAccesUtilisateur(): array
{
    if (!empty($_SESSION['centreon']) && $_SESSION['centreon'] instanceof Centreon) {
        $centreon = $_SESSION['centreon'];
        return [
            'connecte' => true,
            'user_id'  => $centreon->user->user_id,
            'nom'      => $centreon->user->alias,
        ];
    }
    if (!empty($_SESSION['souscription_login'])) {
        if (!empty($_SESSION['souscription_derniere_activite']) && (time() - $_SESSION['souscription_derniere_activite']) > 3600) {
            unset($_SESSION['souscription_login'], $_SESSION['souscription_derniere_activite']);
            return ['connecte' => false];
        }
        $_SESSION['souscription_derniere_activite'] = time();
        return [
            'connecte' => true,
            'nom'      => $_SESSION['souscription_login'],
        ];
    }

    return ['connecte' => false];
}

function ldap_get_group_names($conn, $group_base_search, $user_guid) {
    $search = ldap_search($conn, $group_base_search, "(member=$user_guid)", ["cn"]);
    $entries = ldap_get_entries($conn, $search);

    $groups = [];
    for ($i = 0; $i < $entries["count"]; $i++) {
        $groups[] = $entries[$i]["cn"][0];
    }
    return $groups;
}

// Recherche groupe LDAP si le groupe ACL remonte vide

function RechercheGroupeLDAP($uid) {
    global $ldap_host, $users_ou;
    require_once __DIR__ . '/../../config.php';
    $pdo=connecterBDD();
    $AuthLDAP = get_auth_ressource_info($pdo);
    $bind_dn           = $AuthLDAP['bind_dn'];
    $bind_pass         = $AuthLDAP['bind_pass'];
    $group_base_search = $AuthLDAP['group_base_search'];
    // Nouvelle connexion (celle utilisée par ldap_check_user a été fermée) + bind admin
    $conn = ldap_connect($ldap_host);
    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_bind($conn, $bind_dn, $bind_pass);

    $user_dn = "uid=" . ldap_escape($uid, "", LDAP_ESCAPE_DN) . "," . $users_ou;
    $GroupeCentreon=ldap_get_group_names($conn, $group_base_search, $user_dn);
    return $GroupeCentreon;


    // $user_dn = "uid=" . ldap_escape($base_dn, "", LDAP_ESCAPE_DN) . "," . $users_ou;

    // // Bind avec les identifiants de l'utilisateur = vérifie le mot de passe
    // $bind = @ldap_bind($conn, $user_dn, $userLDAP);
    // if ($bind) {
    //     ldap_unbind($conn);
    //     return $GroupeCentreon;
    // }
    // $GroupeCentreon = ldap_check_user($uid, $password);
    // return $GroupeCentreon;
}    
// Exemple d'appel
// if (ldap_check_user("cs_Centreon-Central", "motdepasse")) {
//     echo "Auth OK";
// } else {
//     echo "Auth KO";
// }
