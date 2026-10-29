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

<!-- Popup de validation de l'ajout d'hôte -->
<div id="popupAddHostValidationParent" class="popup-overlay" style="display: none;">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <h2>Confirmation des données</h2>
            <p><strong>Poller</strong> : <span id="recapPollerAddParent"></span></p>
            <p><strong>Template</strong> : <span id="recapTemplateAddParent"></span></p>
            <p><strong>HostName</strong> : <span id="recapHostNameAddParent"></span></p>
            <p><strong>HostAlias</strong> : <span id="recapHostAliasAddParent"></span></p>
            <p><strong>HostAddress</strong> : <span id="recapHostAddressAddParent"></span></p>
        </div>
        <div class="fieldChangeConf">
            <p class="aligneTexteGras">changez le mode d'envoie si nécessaire</p>
            <input type="radio" id="radioClapiAddParent" name="ModeEnvoiAddParent" value="Clapi" checked />
            <label for="radioClapiAddParent">Ligne de commandes (Clapi)</label>
            <input type="radio" id="radioRestApiAddParent" name="ModeEnvoiAddParent" value="RestApi" />
            <label for="radioRestApiAddParent">API REST</label>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnValiderAddParent" class="button dark_grey">Valider</button>
            <button type="button" id="btnCorrigerAddParent" class="button dark_grey">Corriger</button>
        </div>
    </div>
</div>

<!-- Popup de conflit lors de l'ajout d'hôte -->
<div id="popupConflitAddHostParent" class="popup-overlay" style="display: none;">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <h2 style="color:red;">Attention</h2>
            <p id="conflitMessageAddParent"></p>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnEcraserAddParent" class="button dark_grey">Écraser</button>
            <button type="button" id="btnAnnulerAddParent" class="button dark_grey">Annuler</button>
        </div>
    </div>
</div>

<!-- Popup pour les templates non ajoutés -->
<div id="popupTemplateNonAjouteParent" class="popup-overlay" style="display: none;">
     <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <p>Le template <strong id="templateNonAjouteNomParent"></strong> est sélectionné mais n'a pas été ajouté. </p>
            <p>Comment voulez-vous continuer ?</p>
        </div>
        <div class="cadre-boutons">
            <button type="button" id="btnForcerAjoutTemplateParent" class="button dark_grey">Ajouter le template</button>
            <button type="button" id="btnAppliquerSansTemplateParent" class="button dark_grey">Continuer sans</button>
            <button type="button" id="btnQuitterSansTemplateParent" class="button dark_grey">Fermer</button>
        </div>
    </div>
</div>

<script>
window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'popupTemplateNonAjoute') {
        iframeSource = e.source;
        document.getElementById('templateNonAjouteNomParent').textContent = e.data.templateName;
        document.getElementById('popupTemplateNonAjouteParent').style.display = 'flex';
        document.querySelector('#popupTemplateNonAjouteParent .popup-contenu').focus();
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

    if (e.data && e.data.type === 'popupAddHost') {
        iframeSource = e.source;
        document.getElementById('recapPollerAddParent').textContent = e.data.poller;
        document.getElementById('recapTemplateAddParent').textContent = e.data.template;
        document.getElementById('recapHostNameAddParent').textContent = e.data.name;
        document.getElementById('recapHostAliasAddParent').textContent = e.data.alias;
        document.getElementById('recapHostAddressAddParent').textContent = e.data.address;
        document.getElementById('popupAddHostValidationParent').style.display = 'flex';
        document.querySelector('#popupAddHostValidationParent .popup-contenu').focus();
    }

    if (e.data && e.data.type === 'popupConflitAddHost') {
        iframeSource = e.source;
        document.getElementById('conflitMessageAddParent').textContent = e.data.message;
        document.getElementById('popupConflitAddHostParent').style.display = 'flex';
        document.querySelector('#popupConflitAddHostParent .popup-contenu').focus();
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

document.getElementById('btnCorrigerAddParent').addEventListener('click', () => {
    document.getElementById('popupAddHostValidationParent').style.display = 'none';
});

document.getElementById('btnValiderAddParent').addEventListener('click', () => {
    document.getElementById('popupAddHostValidationParent').style.display = 'none';
    const mode = document.getElementById('radioRestApiAddParent').checked ? 'RestApi' : 'Clapi';
    if (iframeSource) iframeSource.postMessage({ type: 'validerAddHost', mode }, '*');
});

document.getElementById('btnAnnulerAddParent').addEventListener('click', () => {
    document.getElementById('popupConflitAddHostParent').style.display = 'none';
});

document.getElementById('btnEcraserAddParent').addEventListener('click', () => {
    document.getElementById('popupConflitAddHostParent').style.display = 'none';
    if (iframeSource) iframeSource.postMessage('ecraserAddHost', '*');
});

document.getElementById('btnAppliquerSansTemplateParent').addEventListener('click', () => {
    document.getElementById('popupTemplateNonAjouteParent').style.display = 'none';
    if (iframeSource) iframeSource.postMessage('appliquerSansTemplate', '*');
});

document.getElementById('btnForcerAjoutTemplateParent').addEventListener('click', () => {
    document.getElementById('popupTemplateNonAjouteParent').style.display = 'none';
    if (iframeSource) iframeSource.postMessage('forcerAjoutTemplate', '*');
});

document.getElementById('btnQuitterSansTemplateParent').addEventListener('click', () => {
    document.getElementById('popupTemplateNonAjouteParent').style.display = 'none';
});
</script>