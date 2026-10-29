<?php
// fichier host-mod.php  
?>
<!-- Popup de validation des modifications d'hôte -->
<div id="popupHostValidationParent" class="popup-overlay" style="display: none;">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <h2>Confirmer les modifications</h2>
            <div id="popupHostNamechangeParent" style="display: none;">
                <p><strong>Nom</strong> : <span id="recapHostNameOldParent"></span> → <span id="recapHostNameNewParent"></span></p>
            </div>
            <div id="popupHostAliasParent" style="display: none;">
                <p><strong>Alias</strong> : <span id="recapHostAliasOldParent"></span> → <span id="recapHostAliasNewParent"></span></p>
            </div>
            <div id="popupHostAddressParent" style="display: none;">
                <p><strong>Adresse</strong> : <span id="recapHostAddressOldParent"></span> → <span id="recapHostAddressNewParent"></span></p>
            </div>
            <div id="popupTemplatesAjoutesParent" style="display: none;">
                <p><strong>Templates ajoutés</strong></p>
                <ul id="recapTemplatesAjoutesParent"></ul>
            </div>
            <div id="popupTemplatesSupprimesParent" style="display: none;">
                <p><strong>Templates supprimés</strong></p>
                <ul id="recapTemplatesSupprimesParent"></ul>
            </div>
        </div>
        <div class="fieldChangeConf">
          <p class="aligneTexteGras">changez le mode d'envoie si nécessaire</p>
          <input type="radio" id="radioClapiHostParent" name="ModeEnvoiHostParent" value="Clapi" checked />
          <label for="radioClapiHostParent">Ligne de commandes (Clapi)</label>
          <input type="radio" id="radioRestApiHostParent" name="ModeEnvoiHostParent" value="RestApi" />
          <label for="radioRestApiHostParent">API REST</label>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnValiderHostParent" class="button dark_grey">Valider</button>
            <button type="button" id="btnAnnulerHostParent" class="button dark_grey">Annuler</button>
        </div>
    </div>
</div>

<div id="popupResultatParent" class="popup-overlay" style="display: none;">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <STRONG id="popupResultatMessageParent"></STRONG>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnOuvrirLienParent" class="button dark_grey" style="display:none;">Ouvrir le lien</button>
            <button type="button" id="btnFermerResultatParent" class="button dark_grey">Fermer</button>
        </div>
    </div>
</div>

<script>
window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'popupResultat') {
        const texte = document.getElementById('popupResultatMessageParent');
        const btnLien = document.getElementById('btnOuvrirLienParent');

        texte.textContent = e.data.message;
        texte.style.color = (e.data.popupType === 'success') ? 'green' : 'red';

        if (e.data.url) {
            btnLien.style.display = 'inline-block';
            btnLien.onclick = () => {
                window.open(e.data.url, '_blank');
                document.getElementById('popupResultatParent').style.display = 'none';
                window.location.reload();
            };
        } else {
            btnLien.style.display = 'none';
        }

        document.getElementById('popupResultatParent').style.display = 'flex';
    }

    if (e.data && e.data.type === 'popupHost') {
        iframeSource = e.source;

        document.getElementById('recapHostNameOldParent').textContent = e.data.nomOld;
        document.getElementById('recapHostNameNewParent').textContent = e.data.nomNew;
        document.getElementById('popupHostNamechangeParent').style.display = e.data.nomOld !== e.data.nomNew ? 'block' : 'none';

        document.getElementById('recapHostAliasOldParent').textContent = e.data.aliasOld;
        document.getElementById('recapHostAliasNewParent').textContent = e.data.aliasNew;
        document.getElementById('popupHostAliasParent').style.display = e.data.aliasOld !== e.data.aliasNew ? 'block' : 'none';

        document.getElementById('recapHostAddressOldParent').textContent = e.data.addressOld;
        document.getElementById('recapHostAddressNewParent').textContent = e.data.addressNew;
        document.getElementById('popupHostAddressParent').style.display = e.data.addressOld !== e.data.addressNew ? 'block' : 'none';

        const ajoutesUl = document.getElementById('recapTemplatesAjoutesParent');
        ajoutesUl.innerHTML = '';
        e.data.templatesAjoutes.forEach(t => {
            const li = document.createElement('li');
            li.textContent = t;
            ajoutesUl.appendChild(li);
        });
        document.getElementById('popupTemplatesAjoutesParent').style.display = e.data.templatesAjoutes.length ? 'block' : 'none';

        const supprimesUl = document.getElementById('recapTemplatesSupprimesParent');
        supprimesUl.innerHTML = '';
        e.data.templatesSupprimes.forEach(t => {
            const li = document.createElement('li');
            li.textContent = t;
            supprimesUl.appendChild(li);
        });
        document.getElementById('popupTemplatesSupprimesParent').style.display = e.data.templatesSupprimes.length ? 'block' : 'none';

        document.getElementById('popupHostValidationParent').style.display = 'flex';
        document.querySelector('#popupHostValidationParent .popup-contenu').focus();
    }
});

document.getElementById('btnAnnulerHostParent').addEventListener('click', () => {
    document.getElementById('popupHostValidationParent').style.display = 'none';
});

document.getElementById('btnValiderHostParent').addEventListener('click', () => {
    document.getElementById('popupHostValidationParent').style.display = 'none';
    const mode = document.getElementById('radioRestApiHostParent').checked ? 'RestApi' : 'Clapi';
    if (iframeSource) iframeSource.postMessage({ type: 'validerHost', mode }, '*');
});

document.getElementById('btnFermerResultatParent').addEventListener('click', () => {
    document.getElementById('popupResultatParent').style.display = 'none';
    window.location.reload();
});
</script>