// Fichier EnvoiCommande.js — envoi réseau commun (Clapi / API REST)
const debug = 0;

function afficherPopupResultat(message, type, url) {
    window.parent.postMessage({ type: 'popupResultat', message, popupType: type, url }, '*');
}
// Envoie une commande soit en RestApi (GET) soit en Clapi (POST), selon le mode choisi
function envoyerCommande(modeEnvoi, urlApiRest, paramsRest, urlClapi, bodyClapi) {
    if (modeEnvoi === 'RestApi') {
        const params = new URLSearchParams(paramsRest);
        const url = urlApiRest + '?' + params.toString();
        fetch(url)
            .then(response => response.json())
            .then(resultat => {
                console.log('code retour : ' + resultat.codeRetour);
                if (resultat.popup) {
                    afficherPopupResultat(resultat.popup.message, resultat.popup.type, resultat.popup.url);
                }
            });
        return;
    }
    fetch(urlClapi, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(bodyClapi)
    })
        .then(response => response.json())
        .then(resultat => {
            console.log('code retour : ' + resultat.codeRetour);
            if (resultat.popup) {
                afficherPopupResultat(resultat.popup.message, resultat.popup.type, resultat.popup.url);
            }
        });
}