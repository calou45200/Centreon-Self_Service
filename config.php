<?php
$clapiuser = "<User API>";
$clapiPWD = "<MDP>";
$LogoName = "Logo_OMR_Initial.png";
$Entreprise = "DSI Entreprise";
$CouleurEntreprise = '#629451';
define('BASE_PATH', '/usr/share/centreon/www/souscription');
define('BASE_URL', '/centreon/souscription');
$ldap_host = "ldap://<Serveur LDAP>";
$base_dn   = "dc=communaute-omr,dc=fr";
$users_ou  = "ou=Utilisateurs," . $base_dn;
$group_ou = "ou=centreon,ou=Application,dc=communaute-omr,dc=fr";
$users_ou  = "ou=Utilisateurs," . $base_dn;
$userLDAP = "uid=<Compte de service LDAP>," . $users_ou;
$pwdLDAP = "MDP";
