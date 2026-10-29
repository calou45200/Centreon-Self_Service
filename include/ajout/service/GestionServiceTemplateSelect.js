// Fichier Serveur-Dedie/include/ajout/service/GestionServiceTemplateSelect.js — logique de sélection en cascade du template d'hôte
let templates = [];
let brique, famille, domaine, templateId, selection, templateName, TSName, type, vecteurTS, vecteurBloc, vecteur;

function afficherNomConstruit() {
    const champs = [
        brique.value,
        famille.value,
        type.value,
        domaine.value,
        vecteur.value
    ].filter(Boolean);
    // Service affiché = Brique-Famille-Type-Domaine (sans TS_ ni vecteur)
    const champsService = [brique.value, famille.value, type.value, domaine.value].filter(Boolean);
    templateName.textContent = champsService.length ? champsService.join('-') : 'sélection vide';
    // Template envoyé = TS_ + Brique-Famille-Type-Domaine-Vecteur
    TSName.value = champs.length ? 'TS_' + champs.join('-') : '';
    // templateName.textContent = champs.length
    //     ? champs.join('-')
    //     : 'sélection vide';
	// TSName.value = champs.length
    //     ? champs.join('-')
    //     : 'sélection vide';
}
// TS_App-Monitoring-...-Domaine-Vecteur  ->  App-Monitoring-...-Domaine
function nomService(nomTemplate) {
    return nomTemplate.replace(/^TS_/, '').split('-').slice(0, -1).join('-');
}

function uniques(valeurs) {
    return [...new Set(valeurs)].sort();
}

function remplir(select, valeurs, texte) {
    select.innerHTML = `<option value="">— ${texte} —</option>`;
    valeurs.forEach(valeur => {
        const option = document.createElement('option');
        option.value = valeur;
        option.textContent = valeur;
        select.appendChild(option);
    });

    select.disabled = valeurs.length === 0;
}

function vider(select, texte) {
    remplir(select, [], texte);
}

function initGestionTemplateSelect() {
  if (typeof templatesData === 'undefined') return;

  templates = templatesData
      .map(templates => {
          const champs = templates.template_name.replace(/^TS_/, '').split('-');

          if (champs.length < 5) {
              return null;
          }

          return {
              nom: templates.template_name,
              brique: champs[0],
              famille: champs[1],
              type: champs[2],
              domaine: champs.slice(3, -1).join('-'),
              vecteur: champs.at(-1)
          };
      })
      .filter(Boolean);
  // Debug temporaire : voir pourquoi la liste est vide
  console.log('brut :', templatesData.length, 'valides :', templates.length);
  console.log('noms bruts :', templatesData.map(t => t.template_name));
  brique = document.getElementById('brique');
  famille = document.getElementById('famille');
  domaine = document.getElementById('domaine');
  templateId = document.getElementById('template_id');
  selection = document.getElementById('template-selection');
  templateName = document.getElementById('template_name');
  TSName = document.getElementById('TS_Name');
  type = document.getElementById('type');
  vecteurTS = document.getElementById('vecteur-ts');
  vecteurBloc = document.getElementById('vecteur-bloc');
  vecteur = document.getElementById('vecteur');

  remplir(brique, uniques(templates.map(t => t.brique)), 'Sélectionnez une brique');
  vider(famille, 'Sélectionnez une famille');
  vider(type, 'Sélectionnez un type');
  vider(domaine, 'Sélectionnez un domaine');

  brique.addEventListener('change', () => {
      vecteurBloc.hidden = true;
      vecteurTS.hidden = true;
      vider(vecteur, 'Sélectionnez un vecteur');
      templateId.value = '';
      selection.textContent = '';
      vider(type, 'Sélectionnez un type');
      vider(domaine, 'Sélectionnez un domaine');
      const resultats = templates.filter(t => t.brique === brique.value);
      remplir(
          famille,
          uniques(resultats.map(t => t.famille)),
          'Sélectionnez une famille'
      );
      afficherNomConstruit();
  });

  famille.addEventListener('change', () => {
      vecteurBloc.hidden = true;
      vecteurTS.hidden = true;
      vider(vecteur, 'Sélectionnez un vecteur');
      afficherNomConstruit();
      templateId.value = '';
      selection.textContent = '';
      vider(domaine, 'Sélectionnez un domaine');
      const resultats = templates.filter(t =>
          t.brique === brique.value &&
          t.famille === famille.value
      );
      remplir(
          type,
          uniques(resultats.map(t => t.type)),
          'Sélectionnez un type'
      );
      afficherNomConstruit();
  });

  type.addEventListener('change', () => {
      vecteurBloc.hidden = true;
      vecteurTS.hidden = true;
      vider(vecteur, 'Sélectionnez un vecteur');
      templateId.value = '';
      selection.textContent = '';
      const resultats = templates.filter(t =>
          t.brique === brique.value &&
          t.famille === famille.value &&
          t.type === type.value
      );
      remplir(
          domaine,
          uniques(resultats.map(t => t.domaine)),
          'Sélectionnez un domaine'
      );
      afficherNomConstruit();
  });

  domaine.addEventListener('change', () => {
      vecteur.value = '';
      vider(vecteur, 'Sélectionnez un vecteur');
      vecteurBloc.hidden = true;
      vecteurTS.hidden = true;
      templateId.value = '';
      selection.textContent = '';

      if (!domaine.value) {
          templateName.textContent = '— Faites vos sélections —';
          return;
      }

      const resultats = templates.filter(t =>
          t.brique === brique.value &&
          t.famille === famille.value &&
          t.type === type.value &&
          t.domaine === domaine.value
      );
      if (resultats.length === 1) {
          templateId.value = resultats[0].id;
          templateName.textContent = resultats[0].nom;
          templateName.textContent = nomService(resultats[0].nom);
        //   selection.textContent = 'Service trouvé.';
          return;
      }
      if (resultats.length > 1) {
          remplir(
              vecteur,
              uniques(resultats.map(t => t.vecteur)),
              'Sélectionnez un vecteur'
          );

          vecteurBloc.hidden = false;
          vecteurTS.hidden = false;
          templateName.textContent = [brique.value, famille.value, type.value, domaine.value].join('-');
          return;
      }
      templateName.textContent = 'Aucun template trouvé.';
  });

  vecteur.addEventListener('change', () => {
      const resultat = templates.find(t =>
          t.brique === brique.value &&
          t.famille === famille.value &&
          t.type === type.value &&
          t.domaine === domaine.value &&
          t.vecteur === vecteur.value
      );

      afficherNomConstruit();
      if (resultat) {
          templateId.value = resultat.id;
          templateName.textContent = nomService(resultat.nom);
          TSName.value = resultat.nom;
        //   selection.textContent = 'Service trouvé.';
      }
  });
}
