<?php
// fichier dep-host.php
?>
<!-- Popup de validation des dépendances de service-->
<div id="popupDepValidationParent" class="popup-overlay" style="display: none;">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <h2>Confirmer la mise à jour des dépendances</h2>
            <p><strong>Hôte</strong></p>
            <ul id="recapDepHostParent"></ul>
        </div>
        <div class="fieldChangeConf">
          <p class="aligneTexteGras">changez le mode d'envoie si nécessaire</p>
          <input type="radio" id="radioClapiDepParent" name="ModeEnvoiDepParent" value="clapi" checked />
          <label for="radioClapiDepParent">Ligne de commandes (Clapi)</label>
          <input type="radio" id="radioRestApiDepParent" name="ModeEnvoiDepParent" value="restapi" />
          <label for="radioRestApiDepParent">API REST</label>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnValiderDepParent" class="button dark_grey">Valider</button>
            <button type="button" id="btnAnnulerDepParent" class="button dark_grey">Annuler</button>
        </div>
    </div>
</div>

<script>
window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'popupDep') {
        iframeSource = e.source;
        document.getElementById('recapDepHostParent').innerHTML = e.data.hosts.map(h => `<li>${h}</li>`).join('');
        document.getElementById('popupDepValidationParent').style.display = 'flex';
        document.querySelector('#popupDepValidationParent .popup-contenu').focus();
    }
});

document.getElementById('btnAnnulerDepParent').addEventListener('click', () => {
    document.getElementById('popupDepValidationParent').style.display = 'none';
});

document.getElementById('btnValiderDepParent').addEventListener('click', () => {
    document.getElementById('popupDepValidationParent').style.display = 'none';
    const mode = document.getElementById('radioRestApiDepParent').checked ? 'restapi' : 'clapi';
    if (iframeSource) iframeSource.postMessage({ type: 'validerDep', mode }, '*');
});
</script>