<?php

// Fichier Serveur-Dedie/formulaire/FicheServiceAdd.php

// ********************************
// CODE PHP
// *********************************

require_once '../config.php';
require_once '../include/global/DbFunctions.php';

$message = '';

// Session nécessaire pour lire les droits ACL (formulaire chargé dans une iframe)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$templates = RechercheTemplates();
$HostNames = $_SESSION['DroitsAclHosts'] ?? [];
echo "<BR>List des hosts depuis FicheServiceAdd.php</BR>";
var_dump($HostNames);

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
<title><?php echo $Entreprise . " : ADDSERVICE ";?></title>
<link rel="stylesheet" href="../include/theme/style.css">
<link rel="stylesheet" href="../include/theme/search.css">
<link rel="stylesheet" href="../include/theme/button.css">

</head>
<body>
    <h2>Ajouter un service dans centreon</h2>
    <p class="highlight">Attention ce formulaire écrase la configuration
        d'un équipement existant</p>

    <?php if ($message): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

<!--  *********************************
//             FORMULAIRE   
//    *********************************-->

    <form id="ServiceAddForm" method="POST" action="../include/command/clapi/SubmitAddService.php">
        <label><b>Selectionner un hôte : </b></label>
            <table class="Table_Centre">
            <tr class="border">
                <th><label for="host_filter">Filtrer hote </label><BR></th>
                <th><label for="host_name">Sélection </label><BR></th>
            </tr>
            <tr class="border">
                <td class="border">
                    <div class="search-wrapper">
                        <div class="search-box">
                            <button type="button" class="search-btn"
                                aria-label="Ouvrir la recherche">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="M20 20l-3.5-3.5"></path></svg>
                            </button>
                            <input type="text" class="search-input"
                                id="host_filter"
                                placeholder="Recherche...">
                        </div>
                    </div> <BR>
                </td>
                <td class="border">
                    <select id="host_name" name="host_name">
                        <option value="">— Sélectionnez un host —</option>
                        <?= renderHostOptionServices($HostNames) ?>
                    </select>
                </td>
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
                <th id="vecteur-ts" hidden="hidden">
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
                    <select id="domaine" name="domaine" disabled required>
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
            <input type="hidden" id="TSName" name="TS_Name">
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
    const templatesData = <?=json_encode($templates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)?>;
</script>
<script src="../include/command/EnvoiCommande.js"></script>
<script src="../include/ConstruitHTML/UtilsUI.js"></script>
<script src="../include/modif/host/GestionTemplateSelect.js"></script>
<script src="../include/ajout/host/GestionAddHost.js"></script>
<script src="../include/global/script.js"></script>

<script>
    (function() {
        const btn = document.getElementById('btnSubmit');
		const form = document.getElementById('ServiceAddForm');
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
    initGestionAddHost();
</script>
</body>
</html>
