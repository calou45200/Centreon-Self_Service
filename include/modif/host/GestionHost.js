// Fichier Serveur-Dedie/include/modif/host/GestionHost.js — tout ce qui concerne la modification d'un hôte et ses templates
let cellulesNotemplate = [];

function recalculerRowspan() {
    const tbody = document.getElementById('HostConfigTableBody');
    if (!tbody) return;
    const nbLignes = tbody.children.length;
    cellulesNotemplate.forEach(td => td.rowSpan = nbLignes);
}
function creerCelluleTemplate(nomTemplate, statut) {
    const td = document.createElement('td');

    const input = document.createElement('input');
    input.name = 'ChangeTemplate[]';
    input.readOnly = true;
    input.style.color = '#999';
    input.value = nomTemplate;
    input.dataset.original = statut;
    if (statut === 'ajoute') {
        input.classList.add('template-ajoute');
    }

    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'ChangeTemplateStatus[]';
    statusInput.value = statut;

    const btnToggle = document.createElement('button');
    btnToggle.type = 'button';
    btnToggle.textContent = statut === 'supprime' ? '+' : '-';

    btnToggle.addEventListener('click', function() {
        if (statusInput.value !== 'supprime') {
            input.classList.add('template-supprime');
            input.style.color = '';
            statusInput.value = 'supprime';
            btnToggle.textContent = '+';
        } else {
            input.classList.remove('template-supprime');
            input.style.color = '#999'
            statusInput.value = statut;
            btnToggle.textContent = '-';
        }
    });

    td.appendChild(input);
    td.appendChild(statusInput);
    td.appendChild(btnToggle);

    return td;
}

function ajouterLigneSelect() {
    const exclusion = Array.from(document.querySelectorAll('#HostConfigTableBody input[name="ChangeTemplate[]"]'))
        .filter(input => input.nextElementSibling?.value !== 'supprime')
        .map(input => input.value)
        .filter(Boolean);

    const params = new URLSearchParams();
    exclusion.forEach(nom => params.append('exclusion[]', nom));

    fetch('getTemplatesDispo.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            const idUnique = 'template_select_' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.className = 'search-wrapper';
            wrapper.innerHTML = `
        <div class="search-box">
          <button type="button" class="search-btn" aria-label="Ouvrir la recherche">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
          </button>
          <input type="text" class="search-input" placeholder="Recherche...">
        </div>`;
            const filtre = wrapper.querySelector('.search-input');

            const select = document.createElement('select');
            select.id = idUnique;
            select.innerHTML = '<option value="">— Sélectionnez un template —</option>'; data.forEach(t => {
                const option = document.createElement('option');
                option.value = t.host_name;
                option.textContent = t.host_name;
                select.appendChild(option);
            });

            const btnAjouter = document.createElement('button');
            btnAjouter.type = 'button';
            btnAjouter.textContent = '+';

            btnAjouter.addEventListener('click', function() {
                if (!select.value) return;

                const trConfirmee = document.createElement('tr');
                trConfirmee.appendChild(creerCelluleTemplate(select.value, 'ajoute'));
                tr.replaceWith(trConfirmee);

                ajouterLigneSelect();
            });

            td.appendChild(wrapper);
            td.appendChild(select);
            td.appendChild(btnAjouter);
            tr.appendChild(td);

            document.getElementById('HostConfigTableBody').appendChild(tr);
            recalculerRowspan();
            initFiltreSelect(filtre, idUnique);
            initSearchButton(wrapper);
        });
}

function afficherSiDifferent(idDiv, ancienneValeur, nouvelleValeur) {
    const div = document.getElementById(idDiv);
    if (ancienneValeur !== nouvelleValeur) {
        div.style.display = 'flex';
    }
}
function ModifHost(hostname, listeModif) {
    const lignes = [];
    let templatesModifies = false;
    let changementNom = null;
    listeModif.forEach(modif => {
        if (modif.Type === 'name') {
            changementNom = modif.Champ;
            return;
        }

        if (modif.Type === 'alias' || modif.Type === 'address') {
            lignes.push(`HOST;setparam;${hostname};${modif.Type};${modif.Champ}`);
            return;
        }

        if (modif.Type === 'ADD') {
            lignes.push(`HOST;addtemplate;${hostname};${modif.Champ}`);
            templatesModifies = true;
            return;
        }

        if (modif.Type === 'DEL') {
            lignes.push(`HOST;deltemplate;${hostname};${modif.Champ}`);
            templatesModifies = true;
            return;
        }
    });

    if (templatesModifies) {
        lignes.push(`HOST;applytpl;${hostname}`);
    }

    if (changementNom) {
        lignes.push(`HOST;setparam;${hostname};name;${changementNom}`);
    }

    return lignes.join('\n');
}
function chargerConfigHost(hostName) {
    console.log(hostName);

    const table = document.getElementById('HostConfigTable');

    if (!hostName) {
        table.style.display = 'none';
        return;
    }

    fetch('getHostConfig.php?host_name=' + encodeURIComponent(hostName))
        .then(response => response.json())
        .then(data => {
            if (!data.length) {
                table.style.display = 'none';
                return;
            }

            table.style.display = 'table';

            const first = data[0];
            const tbody = document.getElementById('HostConfigTableBody');
            tbody.innerHTML = '';

            const inputsTemplate = [];

            data.forEach((row, i) => {
                const tr = document.createElement('tr');

                if (i === 0) {
                    const nbLignes = data.length;

                    const tdName = document.createElement('td');
                    tdName.rowSpan = nbLignes;
                    tdName.className = 'Notemplate';
                    tdName.innerHTML = '<input id="Changehost_name" name="Changehost_name" value="' + (first.HOST || '') + '"><input type="hidden" id="Changehost_name_old" name="Changehost_name_old" value="' + (first.HOST || '') + '">';
                    tr.appendChild(tdName);

                    const tdAlias = document.createElement('td');
                    tdAlias.rowSpan = nbLignes;
                    tdAlias.className = 'Notemplate';
                    tdAlias.innerHTML = '<input id="Changehost_alias" name="Changehost_alias" value="' + (first.Alias || '') + '" data-original="' + (first.Alias || '') + '">';
                    tr.appendChild(tdAlias);

                    const tdAddress = document.createElement('td');
                    tdAddress.rowSpan = nbLignes;
                    tdAddress.className = 'Notemplate';
                    tdAddress.innerHTML = '<input id="Changehost_address" name="Changehost_address" value="' + (first.address || '') + '" data-original="' + (first.address || '') + '">';
                    tr.appendChild(tdAddress);
                    cellulesNotemplate = [tdName, tdAlias, tdAddress];
                }

                const tdTemplate = creerCelluleTemplate(row.HostTemplate || '', 'inchange');
                tr.appendChild(tdTemplate);

                inputsTemplate.push(tdTemplate.querySelector('input[name="ChangeTemplate[]"]'));
                tbody.appendChild(tr);
            });
            const largeurMax = Math.max(...inputsTemplate.map(input => input.value.length)) + 2;
            inputsTemplate.forEach(input => {
                input.style.width = largeurMax + 'ch';
            });
            ajouterLigneSelect();
        });

}

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('FormHost')) return;
    function envoyerPopupHostValidation() {
        const nomOld = document.getElementById('Changehost_name_old').value;
        const nomNew = document.getElementById('Changehost_name').value;
        const aliasOld = document.getElementById('Changehost_alias').dataset.original;
        const aliasNew = document.getElementById('Changehost_alias').value;
        const addressOld = document.getElementById('Changehost_address').dataset.original;
        const addressNew = document.getElementById('Changehost_address').value;

        const templatesAjoutes = [];
        const templatesSupprimes = [];

        document.querySelectorAll('#HostConfigTableBody input[name="ChangeTemplate[]"]').forEach(input => {
            const statusInput = input.nextElementSibling;
            if (!statusInput || !input.value) return;

            if (input.dataset.original === 'ajoute' && statusInput.value !== 'supprime') {
                templatesAjoutes.push(input.value);
            }

            if (input.dataset.original === 'inchange' && statusInput.value === 'supprime') {
                templatesSupprimes.push(input.value);
            }
        });
        window.parent.postMessage({
            type: 'popupHost',
            nomOld, nomNew, aliasOld, aliasNew, addressOld, addressNew,
            templatesAjoutes, templatesSupprimes
        }, '*');
    }  

    document.getElementById('FormHost').addEventListener('submit', function(e) {
        e.preventDefault();

        const selectEnAttente = document.querySelector('#HostConfigTableBody select[id^="template_select_"]');
        if (selectEnAttente && selectEnAttente.value) {
            window.parent.postMessage({ type: 'popupTemplateNonAjoute', templateName: selectEnAttente.value }, '*');
            return;
        }

        envoyerPopupHostValidation();
    });

    window.addEventListener('message', function (e) {
        if (e.data === 'appliquerSansTemplate') {
            envoyerPopupHostValidation();
        }

        if (e.data === 'forcerAjoutTemplate') {
            const selectEnAttente = document.querySelector('#HostConfigTableBody select[id^="template_select_"]');
            if (selectEnAttente) {
                selectEnAttente.nextElementSibling.click();
            }
            envoyerPopupHostValidation();
        }
    });

    window.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'validerHost') {
            const hostname = document.getElementById('Changehost_name_old').value;
            const listeModif = [];

            const nomOld = document.getElementById('Changehost_name_old').value;
            const nomNew = document.getElementById('Changehost_name').value;
            if (nomOld !== nomNew) {
                listeModif.push({ Type: 'name', Champ: nomNew });
            }

            const aliasEl = document.getElementById('Changehost_alias');
            if (aliasEl.value !== aliasEl.dataset.original) {
                listeModif.push({ Type: 'alias', Champ: aliasEl.value });
            }

            const addressEl = document.getElementById('Changehost_address');
            if (addressEl.value !== addressEl.dataset.original) {
                listeModif.push({ Type: 'address', Champ: addressEl.value });
            }

            document.querySelectorAll('#HostConfigTableBody input[name="ChangeTemplate[]"]').forEach(input => {
                const statusInput = input.nextElementSibling;
                if (!statusInput || !input.value) return;

                if (input.dataset.original === 'ajoute' && statusInput.value !== 'supprime') {
                    listeModif.push({ Type: 'ADD', Champ: input.value });
                }

                if (input.dataset.original === 'inchange' && statusInput.value === 'supprime') {
                    listeModif.push({ Type: 'DEL', Champ: input.value });
                }
            });

            const modeEnvoi = e.data.mode;
            const csv = ModifHost(hostname, listeModif);

            envoyerCommande(
                modeEnvoi,
                '../include/command/restapi/SubmitModHostAPI.php',
                { hostname: hostname, listeModif: JSON.stringify(listeModif) },
                '../include/command/clapi/SubmitModHostClapi.php',
                { hostname: hostname, csv: csv.split('\n') }
            );
        }
    });
});
