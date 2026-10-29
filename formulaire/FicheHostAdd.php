<?php

// Fichier Serveur-Dedie/formulaire/FicheHostAdd.php
//session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ********************************
// CODE PHP
// *********************************
const HOST_TEMPLATE_ID = 1;

// ID du modèle d’hôte
const HOST_GROUP_ID = 1; // Facultatif : ID du groupe d’hôtes

// declare(strict_types=1);
/**
 */
session_start();

require_once '../config.php';
require_once '../include/global/DbFunctions.php';
require_once '../include/DBFunctions/DBFunctionsHost.php';
require_once '../include/DBFunctions/DbFunctionsACL.php';
global $DroitsAclTemplate;
$message = '';
$pollers = $_SESSION['DroitsAclPollers'] ?? [];
$HostNames = $_SESSION['DroitsAclHosts'] ?? [];
$templates = RechercheTemplates();
$DroitsAclTemplate = $_SESSION['DroitsAclTemplate'] ?? [];
    // echo "<BR></BR>";
    // echo "<BR>Fichier FicheHostAdd DroitsAclTemplate : </BR>";
    // var_dump($DroitsAclTemplate);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $pollerName = trim($_POST['poller_name'] ?? '');

    if ($pollerName === '') {
        $pollerName = $defaultPoller ?: '';
    }

    if ($pollerName === '') {
        $message = 'Aucun poller par défaut n’est configuré.';
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title><?php echo $Entreprise . " : ADDHOST ";?></title>
<link rel="stylesheet" href="../include/theme/style.css">
<link rel="stylesheet" href="../include/theme/search.css">
<link rel="stylesheet" href="../include/theme/button.css">

</head>
<body>
    <h2>Ajouter un hôte dans centreon</h2>
    <p class="highlight">Attention ce formulaire écrase la configuration
        d'un équipement existant</p>

    <?php if ($message): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

<!--  *********************************
//             FORMULAIRE   
//    *********************************-->

    <form id="monFormulaire" method="POST" action="../include/command/clapi/SubmitAddHost.php" novalidate>
        <label><b>Info poller : </b></label>
        <table>
            <tr>
                <th><label for="poller_filter">Recherche </label><BR></th>
                <th><label for="poller_name">Sélection </label><BR></th>
            </tr>
            <tr>
				<td>
					<div class="search-wrapper" id="pollerSearchWrapper">
						<div class="search-box">
							<button type="button" class="search-btn" id="pollerSearchBtn">
								<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
							</button>
							<input type="text" id="poller_filter" class="search-input"
								placeholder="Rechercher un poller...">
						</div>
					</div>
					<BR>
				</td>
				<td>
				<select id="poller_name" name="poller_name" required>
                        <option value="">— Sélectionnez un poller —</option>
    					<?php foreach ($pollers as $poller): ?>
                  	<option
                            value="<?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>">
                    	<?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>
                  	</option>
                		<?php endforeach; ?>
          		</select></td>
            </tr>
        </table>
        <br> <label><b>Sélection Template : </b></label> <br>
        <table>
            <tr>
                <th>
                    <label for="brique">Brique</label>
                </th>
                <th>
                    <label for="famille">Famille</label>
                </th>
                <th>
                    <label for="type">Type</label>
                </th>
                <th>
                    <label for="domaine">Domaine</label>
                </th>
                <th id="vecteur-th" hidden="hidden">
                    <label for="vecteur">Vecteur</label>
                </th>
            </tr>
            <tr>
                <td>
                    <select id="brique" required>
                        <option value="">— Sélectionnez une brique —</option>
                    </select>
                </td>
                <td>
                    <select id="famille" disabled required>
                        <option value="">— Sélectionnez une famille —</option>
                    </select>
                </td>
                <td>
                    <select id="type" disabled required>
                        <option value="">— Sélectionnez un type —</option>
                    </select>
                </td>
                <td>
                    <select id="domaine" name="domaine" disabled >
                        <option value="">— Sélectionnez un domaine —</option>
                    </select>
                </td>
                <td id="vecteur-bloc" hidden="hidden">
                    <select id="vecteur" disabled>
                    </select>
                </td>
            </tr>
        </table>


        <!-- ID du template final envoyé lors de la validation -->
        <input type="hidden" id="template_id" name="template_id">

        <p id="template-selection"></p>
        <p>
            <strong><i>Modèle d’hôte sélectionné :</i></strong>
            <!-- pour l'affichage -->
            <output id="template_name" name="template_name">sélection
                vide</output>
            <!-- pour l'envoie -->
            <input type="hidden" id="TH_Name" name="TH_Name">
        </p>
        <br> <br> <br> <br> 
        <label><b>Info serveur : </b></label> 
        <br> <br>
        <label>Nom Centreon 
            <input id="name" name="name" required pattern="[A-Za-z0-9_.\-]+">
        </label> 
        <label>Alias 
            <input id="alias" name="alias">
        </label> 
        <label>Adresse IP <input id="address" name="address" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"placeholder="192.168.1.1" required>
        </label>
        <br> <br>
        <button type="submit" id="btnSubmit" class="button">Contrôle</button>
    </form>
    <!-- Fenêtre Pop-up (Modale) -->
<script>
    const templatesData = <?=json_encode($DroitsAclTemplate, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)?>;
</script>
<script src="../include/command/EnvoiCommande.js"></script>
<script src="../include/ConstruitHTML/UtilsUI.js"></script>
<script src="../include/modif/host/GestionTemplateSelect.js"></script>
<script src="../include/ajout/host/GestionAddHost.js"></script>
<script src="../include/global/script.js"></script>

<script>
    (function() {
        const btn = document.getElementById('btnSubmit');
		const form = document.getElementById('monFormulaire');
        function checkForm() {
            const requis = form.querySelectorAll('[required]');
            let ok = true;
            requis.forEach(champ => {
                if (champ.offsetParent === null) return;
                if (!champ.value.trim()) ok = false;
            });
            btn.disabled = !ok;
        }
    
        form.addEventListener('input', checkForm);
        form.addEventListener('change', checkForm);
        checkForm();
    })();
</script>
<script>
    initSearchButton(document.getElementById('pollerSearchWrapper'));
    initGestionTemplateSelect();
    initGestionAddHost();
</script>
</body>
</html>
