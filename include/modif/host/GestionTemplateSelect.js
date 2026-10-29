// Fichier Serveur-Dedie/include/modif/host/GestionTemplateSelect.js — logique de sélection en cascade du template d'hôte
let templates = [];
let brique, famille, domaine, templateId, selection, templateName, THName, type, vecteurTH, vecteurBloc, vecteur;

function afficherNomConstruit() {
    const champs = [
        brique.value,
        famille.value,
        type.value,
        domaine.value,
        vecteur.value
    ].filter(Boolean);

    templateName.textContent = champs.length
        ? 'TH_' + champs.join('-')
        : 'sélection vide';
	THName.value = champs.length
        ? 'TH_' + champs.join('-')
        : 'sélection vide';
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
      .map(template => {
          const champs = template.host_name.replace(/^TH_/, '').split('-');

          if (champs.length < 3) {
              return null;
          }

          return {
              id: Number(template.host_id),
              nom: template.host_name,
              brique: champs[0],
              famille: champs[1],
              type: champs[2],
              domaine: champs[3] ?? '',
              vecteur: champs.length > 4 ? champs.at(-1) : ''
          };
      })
      .filter(Boolean);

  brique = document.getElementById('brique');
  famille = document.getElementById('famille');
  domaine = document.getElementById('domaine');
  templateId = document.getElementById('template_id');
  selection = document.getElementById('template-selection');
  templateName = document.getElementById('template_name');
  THName = document.getElementById('TH_Name');
  type = document.getElementById('type');
  vecteurTH = document.getElementById('vecteur-th');
  vecteurBloc = document.getElementById('vecteur-bloc');
  vecteur = document.getElementById('vecteur');

  remplir(brique, uniques(templates.map(t => t.brique)), 'Sélectionnez une brique');
  vider(famille, 'Sélectionnez une famille');
  vider(type, 'Sélectionnez un type');
  vider(domaine, 'Sélectionnez un domaine');

  brique.addEventListener('change', () => {
      vecteurBloc.hidden = true;
      vecteurTH.hidden = true;
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
      vecteurTH.hidden = true;
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
      vecteurTH.hidden = true;
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
          uniques(resultats.map(t => t.domaine).filter(Boolean)),
          'Sélectionnez un domaine'
      );
      // Template sans domaine : sélection directe au niveau du type
      if (resultats.length === 1 && !resultats[0].domaine) {
          templateId.value = resultats[0].id;
          templateName.textContent = resultats[0].nom;
          THName.value = resultats[0].nom;
          selection.textContent = 'Template trouvé.';
          return;
      }
      afficherNomConstruit();
  });

  domaine.addEventListener('change', () => {
      vecteur.value = '';
      vider(vecteur, 'Sélectionnez un vecteur');
      vecteurBloc.hidden = true;
      vecteurTH.hidden = true;
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
          THName.value = resultats[0].nom;
          selection.textContent = 'Template trouvé.';
          return;
      }
      if (resultats.length > 1) {
          remplir(
              vecteur,
              uniques(resultats.map(t => t.vecteur)),
              'Sélectionnez un vecteur'
          );

          vecteurBloc.hidden = false;
          vecteurTH.hidden = false;
          templateName.textContent = 'TH_' + [brique.value, famille.value, type.value, domaine.value].join('-');
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
          templateName.textContent = resultat.nom;
          THName.value = resultat.nom;
          selection.textContent = 'Template trouvé.';
      }
  });
}
