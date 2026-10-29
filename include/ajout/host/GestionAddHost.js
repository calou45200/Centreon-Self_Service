// Fichier GestionAddHost.js — soumission du formulaire d'ajout d'hôte et gestion des popups

function envoyerFormulaire(force, mode) {
  const form = document.getElementById('monFormulaire');
  const modeEnvoi = mode;
  const donnees = new FormData(form);

  if (modeEnvoi === 'RestApi') {
    envoyerCommande(
      modeEnvoi,
      '../include/command/restapi/SubmitAddHostAPI.php',
      {
        name: donnees.get('name') || '',
        alias: donnees.get('alias') || '',
        address: donnees.get('address') || '',
        TH_Name: donnees.get('TH_Name') || '',
        poller_name: donnees.get('poller_name') || '',
        force: force ? '1' : '0'
      },
      '',
      {}
    );
    return;
  }
  envoyerCommande(
    modeEnvoi,
    '',
    {},
    '../include/command/clapi/SubmitAddHostClapi.php',
    {
      name: donnees.get('name') || '',
      alias: donnees.get('alias') || '',
      address: donnees.get('address') || '',
      TH_Name: donnees.get('TH_Name') || '',
      poller_name: donnees.get('poller_name') || '',
      force: force ? '1' : '0'
    }
  );
}

async function verifierEtEnvoyer(mode) {
  const nomHote = document.getElementById('name').value;

  try {
    const reponse = await fetch('../include/control/verifier_hote.php', {
      method: 'POST',
      body: new URLSearchParams({ name: nomHote })
    });

    const resultat = await reponse.json();

    if (resultat.error) {
      alert('Erreur : ' + resultat.error);
      return;
    }

    if (resultat.exists) {
      window._modeEnvoiAddMemo = mode;
      window.parent.postMessage({
        type: 'popupConflitAddHost',
        message: `L'hôte "${nomHote}" existe déjà. Voulez-vous l'écraser ?`
      }, '*');
    } else {
      envoyerFormulaire(false, mode);
    }
  } catch (err) {
    alert('Erreur réseau lors de la vérification.');
    console.error(err);
  }
}

function initGestionAddHost() {
  const form = document.getElementById('monFormulaire');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const donnees = new FormData(form);
    window.parent.postMessage({
      type: 'popupAddHost',
      poller: donnees.get('poller_name') || '',
      template: donnees.get('TH_Name') || '',
      name: donnees.get('name') || '',
      alias: donnees.get('alias') || '',
      address: donnees.get('address') || ''
    }, '*');
  });

  window.addEventListener('message', function (e) {
    if (e.data && e.data.type === 'validerAddHost') {
      verifierEtEnvoyer(e.data.mode);
    }
    if (e.data === 'ecraserAddHost') {
      envoyerFormulaire(true, window._modeEnvoiAddMemo);
    }
  });
}