<?php
// Fichier Serveur-Dedie/formulaire/FicheDepHost.php

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once '../include/global/DbFunctions.php';
$HostNames = $_SESSION['DroitsAclHosts'] ?? [];
// echo
//var_dump($HostNames);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title><?php echo $Entreprise . " : DEPHOST ";?></title>
<link rel="stylesheet" href="../include/theme/style.css">
<link rel="stylesheet" href="../include/theme/table.css">
<link rel="stylesheet" href="../include/theme/general.css">
<link rel="stylesheet" href="../include/theme/search.css">
<link rel="stylesheet" href="../include/theme/button.css">
<link rel="stylesheet" href="../include/theme/host-select.css">
</head>
<body>
    <h2>Gestion des dépendances de services</h2>

    <form id="monFormulaire" method="POST" action="../include/command/clapi/SubmitDepHost.php" novalidate>
        <?php require '../include/ConstruitHTML/host-dep-Tables.php'; ?>
        <BR>
        <div id="divsubmitDep">
            <button type="submit" id="btnSubmitDep"
                class="button dark_grey">Contrôler Dep</button>
        </div>
        <BR>
    </form>

<script src="../include/command/EnvoiCommande.js"></script>
<script src="../include/ConstruitHTML/UtilsUI.js"></script>
<script src="../include/global/script.js"></script>

<script>
    (function() {
        const btn = document.getElementById('btnSubmitDep');
        const form = document.getElementById('monFormulaire');
        const hostSelect = document.getElementById('host_name_dep');
        const typeInput = document.getElementById('type_envoi_dep');

        function checkForm() {
            const n = hostSelect.querySelectorAll('input[type="checkbox"]:checked').length;
            btn.disabled = n === 0;
            document.getElementById('host_dropdown_btn_dep').textContent = n ? n + ' host(s) sélectionné(s)' : '— Sélectionnez des hosts —';            
            // btn.disabled = hostSelect.querySelectorAll('input[type="checkbox"]:checked').length === 0;
            // majLibelleHosts();
        }
        // Met à jour le texte du bouton selon le nombre de hosts cochés
        function majLibelleHosts() {
            const n = hostSelect.querySelectorAll('input[type="checkbox"]:checked').length;
            document.getElementById('host_dropdown_btn_dep').textContent = n ? n + ' host(s) sélectionné(s)' : '— Sélectionnez des hosts —';
        }
        form.addEventListener('change', checkForm);
        checkForm();
        btn.addEventListener('click', (e) => {
            e.preventDefault(); // empêche l'envoi natif du formulaire, qui remplace l'iframe par la réponse de SubmitDepHost.php
            window.parent.postMessage({
                type: 'popupDep',
                hosts: Array.from(hostSelect.querySelectorAll('input[type="checkbox"]:checked')).map(c => c.value)
            }, '*');
        });
        // btn.addEventListener('click', () => {
        //     window.parent.postMessage({
        //         type: 'popupDep',
        //         hosts: Array.from(hostSelect.querySelectorAll('input[type="checkbox"]:checked')).map(c => c.value)
        //     }, '*');
        // });

        window.addEventListener('message', (e) => {
            if (e.data && e.data.type === 'validerDep') {
                typeInput.value = e.data.mode;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                }).finally(() => {
                    // Prévient la page parente puis recharge la liste une fois l'import terminé
                    window.parent.postMessage({ type: 'depTermine' }, '*');
                    window.location.reload();
                });
                // });
                // hostSelect.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
                // checkForm();

            }
        });
    })();
</script>
<script>
    initSearchButton(document.getElementById('hostDepSearchWrapper'));

    // Applique le filtre texte sur tous les hosts
    function appliquerFiltres() {
        const terme = document.getElementById('host_filter_dep').value.toLowerCase();
        document.querySelectorAll('#host_name_dep .host-item').forEach(item => {
            const ok = item.textContent.toLowerCase().includes(terme);
            item.style.display = ok ? 'block' : 'none';
        });
    }
    document.getElementById('host_filter_dep').addEventListener('input', appliquerFiltres);

    // Ouverture/fermeture de la liste à cases à cocher
    const btnHostsDep = document.getElementById('host_dropdown_btn_dep');
    const panneauHostsDep = document.getElementById('host_name_dep');
    btnHostsDep.addEventListener('click', () => {
        panneauHostsDep.style.display = getComputedStyle(panneauHostsDep).display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', (e) => {
        if (!btnHostsDep.parentElement.contains(e.target)) panneauHostsDep.style.display = 'none';
    });
    appliquerFiltres();
</script>
</body>
</html>