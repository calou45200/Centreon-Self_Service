<?php
$clapiuser = "<User API>";
$clapiPWD = "<MDP>";
$LogoName = "/PATH/TO/Logo.png";
$Entreprise = "DSI Entreprise";
$CouleurEntreprise = '#629451';
define('BASE_PATH', '/usr/share/centreon/www/souscription');
define('BASE_URL', '/centreon/souscription');
$ldap_host = "ldap://<Serveur LDAP>";
$base_dn   = "<basedn Entreprise>";
$users_ou  = "ou=Utilisateurs," . $base_dn;
$group_ou = "<OU Centreon>,ou=Application," . $base_dn;
$users_ou  = "ou=Utilisateurs," . $base_dn;
$userLDAP = "uid=<Compte de service LDAP>," . $users_ou;
$pwdLDAP = "MDP";
