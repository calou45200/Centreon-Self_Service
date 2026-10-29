<?php
$clapiuser = "cs_clapi";
$clapiPWD = "Nemours*77140";
$LogoName = "Logo_OMR_Initial.png";
$Entreprise = "DSI Entreprise";
$CouleurEntreprise = '#629451';
define('BASE_PATH', '/usr/share/centreon/www/souscription');
define('BASE_URL', '/centreon/souscription');
$ldap_host = "ldap://192.168.122.150";
$base_dn   = "dc=communaute-omr,dc=fr";
$users_ou  = "ou=Utilisateurs," . $base_dn;
$group_ou = "ou=centreon,ou=Application,dc=communaute-omr,dc=fr";
$users_ou  = "ou=Utilisateurs," . $base_dn;
//$userLDAP = "uid=cs_Centreon-Central," . $users_ou;
//$pwdLDAP = "Nemours*77140";