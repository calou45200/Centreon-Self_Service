<?php
	// Fichier Serveur-Dedie/index.php
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
	// a supprimer en prod dans /usr/share/centreon/www/souscription 


	require_once __DIR__ . '/config.php';
    $cheminImage = BASE_URL . '/Image/' . $LogoName;
?>
<!doctype html>
<html lang="fr">
<head>
    <?php
        echo '<link rel="icon" href=" ' . $cheminImage . '"><BR>'
    ?>
  <meta charset="utf-8">
  <title><?php echo " Fiche de souscriptions " . $Entreprise;?></title>
    <link rel="stylesheet" href="./include/theme/style.css">
    <link rel="stylesheet" href="./include/theme/table.css">
    <link rel="stylesheet" href="./include/theme/fieldAction.css">
    <link rel="stylesheet" href="./include/theme/fieldChangeConf.css">
    <link rel="stylesheet" href="./include/theme/general.css">
    <link rel="stylesheet" href="./include/theme/champs.css">
    <link rel="stylesheet" href="./include/theme/host-select.css">
    <link rel="stylesheet" href="./include/theme/popup.css">
    <link rel="stylesheet" href="./include/theme/button.css">
    <link rel="stylesheet" href="./include/theme/search.css">
  <style>
    .fieldAction,
    .fieldDomaine {
        display: inline-block;
        vertical-align: top;
        margin: 0 10px 20px 10px;
        padding: 15px 25px;
        border-radius: 12px;
        border: 1px solid #c2c2c2;
        background: linear-gradient(#f5f5f5, #dcdcdc);
        box-shadow:
            inset 0 1px 0 #ffffff,
            inset 0 -1px 0 #b0b0b0,
            0 3px 6px rgba(0,0,0,0.15);
    }    html {
        min-height: 100%;
        background: linear-gradient(#f5f5f5, #dcdcdc);
    }
    body {
        background: transparent;
    }  
    table {
        border-collapse: collapse;
        border: 2px solid;
        margin: 0 auto;
        table-layout: auto;
    }
    td, th {
        border: none;
        padding: 10px 20px;
    }
    .btn * {
        box-sizing: content-box;
    }
</style>
</head>
<body>
<?php
// Pour débugger
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
	require_once __DIR__ . '/include/control/gestion-url.php';
	require_once __DIR__ . '/include/LDAP/AuthCentreon.php';

	$access = verifierAccesUtilisateur();
    $UserName = $access['nom'];
	if (!$access['connecte']) {
	    require_once __DIR__ . '/include/popup/login.php';
        exit;
	}
    require_once './include/DBFunctions/DbFunctionsACL.php';
    global $DroitsAclPollers, $DroitsAclHosts,$AclGroup;
    ListeDroitsUtilisateurs($UserName);
    $_SESSION['DroitsAclPollers'] = $DroitsAclPollers;
    $_SESSION['DroitsAclHosts'] = $DroitsAclHosts;
    $_SESSION['AclGroup'] = $AclGroup;
    $_SESSION['UserName'] = $UserName;

    require_once __DIR__ . '/include/ConstruitHTML/logo.php';
    ?>

<h1> Fomulaires de souscription Centreon <?php echo '<BR>Entite : <span style="color: ' . $CouleurEntreprise . ';">' . $Entreprise . '</span>'; ?> </h1>

<div style="text-align:center;">
    <div style="display:inline-block; vertical-align:top;">
        <fieldset class="fieldDomaine">        <legend><strong>&nbsp;Domaine&nbsp;: </strong></legend>
            <table class="btn-table" id="domaine-table">
                <tr></tr>
                <tr></tr>
            </table>
        </fieldset>
    </div>
    <div style="display:inline-block; vertical-align:top;">
        <fieldset class="fieldAction">
            <legend><strong>&nbsp;Actions&nbsp;: </strong></legend>
            <script src="<?php echo retrouveURL() . '/formulaire/gestion-boutons.js'; ?>"></script>
            <table class="btn-table" id="action-table">
                <tr></tr>
                <tr></tr>
            </table>
        </fieldset>
    </div>
</div>
<div id="Action"></div>
<script>
  const Chemin = "<?php echo retrouveURL(); ?>";
</script>
<script>
  // Config centralisée : ajouter une entrée ici suffit pour ajouter bouton + colonne + div
    const btnConfig = [
        { id: 'btnModHost', value: 'host', divId: 'ModHost', label: 'Modifier un hote', src: '/formulaire/FicheHostMod.php?action=host', domain: 'Hosts' },
        { id: 'btnAddHost', value: 'add', divId: 'LienAdd', label: 'Créer un nouvel hote', src: '/formulaire/FicheHostAdd.php', domain: 'Hosts' },
        { id: 'btnPoller', value: 'poller', divId: 'ChangePoller', label: 'Changer un hote de poller', src: '/formulaire/FicheHostMod.php?action=poller', domain: 'Hosts' },
        { id: 'btnAddService', value: 'Addservice', divId: 'AddService', label: 'Ajouter un service', src: '/formulaire/GestionService.php?action=service', domain: 'Services' },
        { id: 'btnModService', value: 'Modservice', divId: 'ModService', label: 'Modifier un service', src: '/formulaire/FicheServiceMod.php?action=Modservice', domain: 'Services' },
        { id: 'btnModDep', value: 'ModDep', divId: 'ModDep', label: 'Gestion des dépendances', src: '/formulaire/FicheDepHost.php?action=ModDep', domain: 'Dependencies' },
        { id: 'btnAddUSer', value: 'AddUser', divId: 'AddUser', label: 'Ajouter un utilisateur', src: '/formulaire/FicheUserAdd.php?action=AddUser', domain: 'Users' },
        { id: 'btnModUser', value: 'ModUser', divId: 'ModUser', label: 'Modifier un utilisateur', src: '/formulaire/FicheUserMod.php?action=ModUser', domain: 'Users' },
    ];
// chargement initial
    document.addEventListener('DOMContentLoaded', () => {
        genererDivsAction(btnConfig, Chemin);
        genererColonnesBtnTable(btnConfig);
        initBtnGestion(btnConfig);
        genererColonnesDomaineTable(btnConfig);
        initDomaineFiltre(btnConfig);
    });
</script>
<!-- Popup de validation du changement de poller -->
<?php require_once __DIR__ . '/include/popup/poller.php'; ?>
<!-- Popup de modification des hotes -->
<?php require_once __DIR__ . '/include/popup/host-mod.php'; ?>
<!-- Popup d'ajout de nouveaux hotes -->
<?php require_once __DIR__ . '/include/popup/host-add.php'; ?>
<!-- Popup des services -->
<?php require_once __DIR__ . '/include/popup/service.php'; ?>
<!-- Popup des dépendances -->
<?php require_once __DIR__ . '/include/popup/dep-host.php'; ?>
<script>
<?php 
echo time();
?>

  // Gestion des formulaires dans le Iframes

let iframeSource = null;

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.popup-overlay').forEach(p => {
            if (p.style.display !== 'none') p.style.display = 'none';
        });
    }
});
</script>
</body>
</html>