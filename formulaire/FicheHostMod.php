<?php
session_start();
// fichier Serveur-Dedie/formulaire/FicheHostMod.php
const HOST_TEMPLATE_ID = 1;

// ID du modèle d’hôte
const HOST_GROUP_ID = 1; // Facultatif : ID du groupe d’hôtes

// declare(strict_types=1);
global $DroitsAclHosts, $DroitsAclResources, $AdminCentreon,$FiltreHC,$hostFilter;
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include/global/DbFunctions.php';
require_once __DIR__ . '/../include/DBFunctions/DbFunctionsACL.php';

// require_once '/etc/centreon/centreon.conf.php';
$message = '';
$pollers = $_SESSION['DroitsAclPollers'] ?? [];
$HostNames = $_SESSION['DroitsAclHosts'] ?? [];
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

<script src="../include/command/EnvoiCommande.js"></script>
<script src="../include/ConstruitHTML/UtilsUI.js"></script>
<script src="../include/modif/poller/GestionPoller.js"></script>
<script src="../include/modif/host/GestionHost.js"></script>
<script src="../include/modif/host/ModHost.js"></script>


<script type="text/javascript">
    // Gère l'ouverture/fermeture des barres de recherche animées
    function initSearchButtons() {
    	document.querySelectorAll('.search-wrapper').forEach(wrapper => {
            const btn = wrapper.querySelector('.search-btn');
            const input = wrapper.querySelector('.search-input');
            btn.addEventListener('click', () => {
        	   if (!wrapper.classList.contains('active')) {
                    wrapper.classList.add('active');
                    setTimeout(() => input.focus(), 220);
              } 
              else if (input.value.trim() === "") {
                    wrapper.classList.remove('active');
              }
            });
            document.addEventListener('click', (e) => {
            	if (!wrapper.contains(e.target) && input.value.trim() === "") {
                	wrapper.classList.remove('active');
                }
            });
            input.addEventListener('keydown', (e) => {
        	   if (e.key === "Escape" && input.value.trim() === "") {
                    wrapper.classList.remove('active');
                }
        });
      });
    }
// Met à jour le texte du bouton selon le nombre de hosts cochés
function majLibelleHosts() {
    const n = document.querySelectorAll('#host_name input[type="checkbox"]:checked').length;
    document.getElementById('host_dropdown_btn').textContent = n ? n + ' host(s) sélectionné(s)' : '— Sélectionnez des hosts —';
}

function filtrerHostSelonPoller() {
    const idSource = document.getElementById('poller_source').selectedOptions[0]?.dataset.pollerId;
    const pollerCible = document.getElementById('poller_name');
    // Affiche uniquement les hosts du poller source
    document.getElementById('host_filter').value = '';
    document.querySelectorAll('#host_name .host-item').forEach(item => {
        item.querySelector('input').checked = false;
        item.style.display = (idSource && item.dataset.pollerId === idSource) ? 'block' : 'none';
    });
    majLibelleHosts();
    // Le poller cible ne peut pas être le poller source
    pollerCible.value = '';
    Array.from(pollerCible.options).forEach(option => {
        if (option.value === '') return;
        option.hidden = (option.dataset.pollerId === idSource);
    });
}

    // Appeler la fonction au chargement pour initialiser la taille du select
    document.addEventListener("DOMContentLoaded", () => {
        //initToggleDivs();
        initSearchButtons();
        
        if (document.getElementById('poller_name')) {
            initFiltreSelect('poller_filter_source', 'poller_source');
            initFiltreSelect('poller_filter', 'poller_name');
            // Recherche dans la liste de cases à cocher (uniquement hosts du poller source)
            document.getElementById('host_filter').addEventListener('input', function () {
                const terme = this.value.toLowerCase();
                const idSource = document.getElementById('poller_source').selectedOptions[0]?.dataset.pollerId;
                document.querySelectorAll('#host_name .host-item').forEach(item => {
                    const ok = idSource
                        && item.dataset.pollerId === idSource
                        && item.textContent.toLowerCase().includes(terme);
                    item.style.display = ok ? 'block' : 'none';
                });
            });
            document.getElementById('poller_source').addEventListener('change', filtrerHostSelonPoller);

            // Ouverture/fermeture de la liste déroulante à cases à cocher
            const btnHosts = document.getElementById('host_dropdown_btn');
            const panneauHosts = document.getElementById('host_name');
            btnHosts.addEventListener('click', () => {
                panneauHosts.style.display = panneauHosts.style.display === 'none' ? 'block' : 'none';
            });
            document.addEventListener('click', (e) => {
                if (!btnHosts.parentElement.contains(e.target)) panneauHosts.style.display = 'none';
            });
            panneauHosts.addEventListener('change', majLibelleHosts);
        }

        if (document.getElementById('host_name_mod')) {
            initFiltreSelect('host_filter_mod', 'host_name_mod');
//            initMultiSelectClic('host_name_mod');
            document.getElementById('host_name_mod').addEventListener('change', function () {
                chargerConfigHost(this.value);
            });
            document.getElementById('HostConfigTableBody').addEventListener('input', function (e) {
                if (['Changehost_name', 'Changehost_alias', 'Changehost_address'].includes(e.target.id)) {
                    const original = e.target.id === 'Changehost_name'
                        ? document.getElementById('Changehost_name_old').value
                        : e.target.dataset.original;
                    e.target.classList.toggle('champ-modifie', e.target.value !== original);
                }
                if (e.target.id === 'Changehost_name') {
                    console.log('Nouveau Nom : ' + e.target.value);
                }
            });
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
       // if (document.getElementById('host_name')) ajusterLargeur(document.getElementById('host_name'));
        if (document.getElementById('host_name_mod')) ajusterLargeur(document.getElementById('host_name_mod'));
    });
    window.addEventListener('message', (e) => {
        if (e.data === 'recalcWidth') {
         //   if (document.getElementById('host_name')) ajusterLargeur(document.getElementById('host_name'));
            if (document.getElementById('host_name_mod')) ajusterLargeur(document.getElementById('host_name_mod'));
        }
    });
</script>
    <!--     *********************************
                         FORMULAIRES   
                *********************************-->
    <?php $action = $_GET['action'] ?? 'poller'; ?>

    <?php if ($action === 'poller'): ?>
        <h2>Changement de poller</h2>
        <form id="FormPoller">
            <!-- Poller source -->
            <table class="Table_Centre">
                <tr class="border">
                    <th><label for="poller_filter_source">Filtrer Poller source </label><BR></th>
                    <th><label for="poller_source">Poller source </label><BR></th>
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
                                <input type="text" class="search-input" id="poller_filter_source" placeholder="Recherche...">
                            </div>
                        </div> <BR>
                    </td>
                    <td class="border">
                        <select id="poller_source" name="poller_source">
                            <option value="">— Sélectionnez un poller source —</option>
                            <?php foreach ($pollers as $poller): ?>
                            <option value="<?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>" data-poller-id="<?= htmlspecialchars($poller['poller_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <!-- Poller cible -->
            <table class="Table_Centre">
                <tr class="border">
                    <th><label for="poller_filter">Filtrer Poller cible </label><BR></th>
                    <th><label for="poller_name">Poller cible </label><BR></th>
                </tr>
                <tr class="border">
                    <td class="border">
                        <div class="search-wrapper">
                            <div class="search-box">
                                <button type="button" class="search-btn" aria-label="Ouvrir la recherche">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7"></circle>
                                        <path d="M20 20l-3.5-3.5"></path></svg>
                                </button>
                                <input type="text" class="search-input" id="poller_filter" placeholder="Recherche...">
                            </div>
                        </div> <BR>
                    </td>
                    <td class="border">
                        <select id="poller_name" name="poller_name">
                            <option value="">— Sélectionnez un poller cible —</option>
                            <?php foreach ($pollers as $poller): ?>
                            <option value="<?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>" data-poller-id="<?= htmlspecialchars($poller['poller_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($poller['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <!-- Hosts -->
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
                        <div style="position:relative;display:inline-block;">
                            <button type="button" id="host_dropdown_btn" style="min-width:250px;text-align:left;">— Sélectionnez des hosts —</button>
                            <div id="host_name" style="display:none;position:absolute;z-index:10;background:#fff;border:1px solid #999;max-height:250px;overflow-y:auto;min-width:100%;text-align:left;">
                                <?php foreach ($HostNames as $h): ?>
                                <label class="host-item" data-poller-id="<?= htmlspecialchars($h['poller_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="display:none;white-space:nowrap;">
                                    <input type="checkbox" name="host_name[]" value="<?= htmlspecialchars($h['host_name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($h['host_name'], ENT_QUOTES, 'UTF-8') ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <BR>
            <div id="divsubmitPoller">
                <button type="submit" id="btnSubmitPoller"
                    class="button dark_grey">Appliquer les changements</button>
            </div>
            <BR>
         </form>
    <?php else: ?>
        <?php include '../include/modif/host/ModHost.php'; ?>
    <?php endif; ?>
</body>
</html>