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
<!-- Popup vers le lien vers centreon -->
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
    // Début gestionnaire d'évènements pour les messages provenant des l'ajout d'un hote dans l'iframe
window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'popupTemplateNonAjoute') {
        iframeSource = e.source;
        document.getElementById('templateNonAjouteNomParent').textContent = e.data.templateName;
        document.getElementById('popupTemplateNonAjouteParent').style.display = 'flex';
        document.querySelector('#popupTemplateNonAjouteParent .popup-contenu').focus();
    }
    // Gestionnaire d'évènements pour le popup de validation d'ajout d'hôte
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
    // Gestion d'évènement pour le popup de conflit lors de l'ajout d'hôte
    if (e.data && e.data.type === 'popupConflitAddHost') {
        iframeSource = e.source;
        document.getElementById('conflitMessageAddParent').textContent = e.data.message;
        document.getElementById('popupConflitAddHostParent').style.display = 'flex';
        document.querySelector('#popupConflitAddHostParent .popup-contenu').focus();
    }
});
    // Fin du gestionnaire d'évènements pour les messages provenant des l'ajout d'un hote dans l'iframe

    // gestionnaire du bouton d'annulation du popup de validation d'ajout d'hôte
document.getElementById('btnCorrigerAddParent').addEventListener('click', () => {
    document.getElementById('popupAddHostValidationParent').style.display = 'none';
});

    // gestionnaire du bouton de validation du popup de validation d'ajout d'hôte
document.getElementById('btnValiderAddParent').addEventListener('click', () => {
    document.getElementById('popupAddHostValidationParent').style.display = 'none';
    const mode = document.getElementById('radioRestApiAddParent').checked ? 'RestApi' : 'Clapi';
    if (iframeSource) iframeSource.postMessage({ type: 'validerAddHost', mode }, '*');
});

    // gestionnaire du bouton d'annulation du popup de validation en cas de conflit d'ajout d'hôte
document.getElementById('btnAnnulerAddParent').addEventListener('click', () => {
    document.getElementById('popupConflitAddHostParent').style.display = 'none';
});

    // gestionnaire du bouton pour forcer la mise à jour du popup de validation en cas de conflit d'ajout d'hôte
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

    // gestionnaire du bouton pour quitter le conflit d'ajout d'hôte
document.getElementById('btnQuitterSansTemplateParent').addEventListener('click', () => {
    document.getElementById('popupTemplateNonAjouteParent').style.display = 'none';
});
document.getElementById('btnFermerResultatParent').addEventListener('click', () => {
    document.getElementById('popupResultatParent').style.display = 'none';
    window.location.reload();
});
</script>