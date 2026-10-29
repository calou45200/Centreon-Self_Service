<?php
// fichier Serveur-Dedie/formulaire/GestionService.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include/global/DbFunctions.php';
require_once __DIR__ . '/../include/DBFunctions/DbFunctionsService.php';
// Fichier Serveur-Dedie/include/DBFunctions/DbFunctionsService.php
require_once __DIR__ . '/../include/DBFunctions/DBFunctionsHost.php';
require_once '/etc/centreon/centreon.conf.php';
require_once __DIR__ . '/../include/LDAP/AuthCentreon.php';
// Démarre la session comme index.php, avant toute sortie HTML
$access = verifierAccesUtilisateur();
if (!$access['connecte']) {
    exit;
}
$_SESSION['UserName'] = $access['nom'];
$templates = ListServicesTemplates();
$message = '';

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo $Entreprise . " : MODHOST ";?></title>
    <link rel="stylesheet" href="../include/theme/style.css">
    <link rel="stylesheet" href="../include/theme/table.css">
    <link rel="stylesheet" href="../include/theme/fieldAction.css">
    <link rel="stylesheet" href="../include/theme/fieldChangeConf.css">
    <link rel="stylesheet" href="../include/theme/general.css">
    <link rel="stylesheet" href="../include/theme/champs.css">
    <link rel="stylesheet" href="../include/theme/host-select.css">
    <link rel="stylesheet" href="../include/theme/popup.css">
    <link rel="stylesheet" href="../include/theme/button.css">
    <link rel="stylesheet" href="../include/theme/search.css">
</head>
<body>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<script src="../include/ajout/service/GestionServiceTemplateSelect.js"></script>
<script>
    const templatesData = <?=json_encode($templates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)?>;
</script>


<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", () => {
        //initToggleDivs();
        // Appel unique ici : GestionService.php ne charge pas script.js
        initGestionTemplateSelect();
    });
</script>
    <!--     *********************************
                         FORMULAIRES   
                *********************************-->
 <div id="AddService">
    <h2>Ajouter un service à un équipement</h2>
    <form id="FormService" method="POST" action="../include/command/clapi/SubmitAddService.php">
        <?php 

            echo "<BR>Liste droits hosts depuis GestionService.php avant If SESSION : </BR>";
            var_dump($_SESSION['DroitsAclHosts']);
            // Si la session ne contient pas les droits, on les recalcule pour l'utilisateur connecté
            if (empty($_SESSION['DroitsAclHosts']) && !empty($_SESSION['UserName'])) {
                require_once __DIR__ . '/../include/DBFunctions/DbFunctionsACL.php';
                global $DroitsAclPollers, $DroitsAclHosts, $AclGroup;
                ListeDroitsUtilisateurs($_SESSION['UserName']);
                $_SESSION['DroitsAclHosts'] = $DroitsAclHosts;
                echo "<BR>Liste droits hosts depuis GestionService.php Aprés le IF : </BR>";
                var_dump($_SESSION['DroitsAclHosts']);
           }                
            include '../include/ConstruitHTML/service-config-table.php'; 
        ?>
        <div id="divsubmit">
            <button type="submit" id="btnSubmit"
                class="button dark_grey">Appliquer les Services</button>
        </div>
    </form>
</div>
</body>
</html>
