<!-- Popup de validation du changement de poller -->
<div id="popupPollerValidationParent" class="popup-overlay" style="display: none;">
  <div class="popup-contenu" tabindex="-1">
    <div class="cadre-popup">
      <h2>Confirmer le changement de poller</h2>
      <p><strong>Nouveau poller</strong> : <span id="recapPollerNameParent"></span></p>
      <p><strong>Hôtes concernés</strong></p>
      <ul id="recapPollerHostsParent"></ul>
    </div>
    <div class="fieldChangeConf">
      <p class="aligneTexteGras">changez le mode d'envoie si nécessaire</p>
      <input type="radio" id="radioClapiPollerParent" name="ModeEnvoiPollerParent" value="Clapi" checked />
      <label for="radioClapiPollerParent">Ligne de commandes (Clapi)</label>
      <input type="radio" id="radioRestApiPollerParent" name="ModeEnvoiPollerParent" value="RestApi" />
      <label for="radioRestApiPollerParent">API REST</label>
    </div>
    <div class="cadre-boutons">
      <button type="button" id="btnValiderPollerParent" class="button dark_grey">Valider</button>
      <button type="button" id="btnAnnulerPollerParent" class="button dark_grey">Annuler</button>
    </div>
  </div>
</div>

<script>
window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'popupPoller') {
        iframeSource = e.source;
        document.getElementById('recapPollerNameParent').textContent = e.data.pollerName;
        const ul = document.getElementById('recapPollerHostsParent');
        ul.innerHTML = '';
        e.data.hosts.forEach(h => {
            const li = document.createElement('li');
            li.textContent = h;
            ul.appendChild(li);
        });
        document.getElementById('popupPollerValidationParent').style.display = 'flex';
        document.querySelector('#popupPollerValidationParent .popup-contenu').focus();
    }
});

document.getElementById('btnAnnulerPollerParent').addEventListener('click', () => {
    document.getElementById('popupPollerValidationParent').style.display = 'none';
});

document.getElementById('btnValiderPollerParent').addEventListener('click', () => {
    document.getElementById('popupPollerValidationParent').style.display = 'none';
    const mode = document.getElementById('radioRestApiPollerParent').checked ? 'RestApi' : 'Clapi';
    if (iframeSource) iframeSource.postMessage({ type: 'validerPoller', mode }, '*');
});
</script>