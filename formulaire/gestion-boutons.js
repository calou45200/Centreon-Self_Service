// Fichier : formulaire/gestion-boutons.js
function genererColonnesBtnTable(config) {
    let tdBoutons = '';
    let tdLegends = '';

    config.forEach((c, i) => {
        tdBoutons += `
        <td>
            <button id="${c.id}" class="btn" type="button" data-value="${c.value}" aria-pressed="${i === 0 ? 'true' : 'false'}">
                <svg class="btn__icon" viewBox="0 0 24 24" width="24px" height="24px" aria-hidden="true">
                    <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <polyline points="12,1 12,10" />
                        <circle fill="none" cx="12" cy="13" r="9" stroke-dasharray="49.48 7.07" stroke-dashoffset="10.6" />
                    </g>
                </svg>
                <span class="btn__sr">${c.label}</span>
            </button>
        </td>`;

        tdLegends += `
        <td><p class="btn-legend${i === 0 ? ' btn-legend--active' : ''}">${c.label}</p></td>`;
    });

    document.querySelector('#action-table tr:nth-child(1)').innerHTML = tdBoutons;
    document.querySelector('#action-table tr:nth-child(2)').innerHTML = tdLegends;
}

function genererDivsAction(config, chemin) {
    let divs = '';

    config.forEach((c, i) => {
        divs += `
        <div id="${c.divId}" style="display: none;">
            <iframe src="${chemin}${c.src}" style="width:100%; height:800px; border:none;"></iframe>
        </div>`;
    });

    document.getElementById('Action').innerHTML = divs;
}

function initBtnGestion(config) {
    const btnIds = config.map(c => '#' + c.id);
    document.querySelectorAll(btnIds.join(', ')).forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll(btnIds.join(', ')).forEach(b => b.setAttribute('aria-pressed', 'false'));
            this.setAttribute('aria-pressed', 'true');
            document.querySelectorAll('#action-table .btn-legend').forEach(l => l.classList.remove('btn-legend--active'));
            const index = Array.from(this.closest('tr').children).indexOf(this.closest('td'));
            const legendRow = this.closest('table').rows[1];
            legendRow.children[index].querySelector('.btn-legend').classList.add('btn-legend--active');
            const value = this.dataset.value;
            config.forEach(c => {
                const div = document.getElementById(c.divId);
                if (div) div.style.display = c.value === value ? 'block' : 'none';
            });
            const active = config.find(c => c.value === value);
            if (active) {
                const iframe = document.querySelector(`#${active.divId} iframe`);
                if (iframe) iframe.contentWindow.postMessage('recalcWidth', '*');
            }
        });
    });
}

// Génère les boutons de filtre par domaine
function genererColonnesDomaineTable(config) {
    const domaines = [...new Set(config.map(c => c.domain))];
    let tdBoutons = '';
    let tdLegends = '';

    domaines.forEach((d, i) => {
        tdBoutons += `
        <td>
            <button id="btnDomaine-${d}" class="btn" type="button" data-domain="${d}" aria-pressed="${i === 0 ? 'true' : 'false'}">
                <svg class="btn__icon" viewBox="0 0 24 24" width="24px" height="24px" aria-hidden="true">
                    <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <polyline points="12,1 12,10" />
                        <circle fill="none" cx="12" cy="13" r="9" stroke-dasharray="49.48 7.07" stroke-dashoffset="10.6" />
                    </g>
                </svg>
                <span class="btn__sr">${d}</span>
            </button>
        </td>`;

        tdLegends += `
        <td><p class="btn-legend${i === 0 ? ' btn-legend--active' : ''}">${d}</p></td>`;
    });

    document.querySelector('#domaine-table tr:nth-child(1)').innerHTML = tdBoutons;
    document.querySelector('#domaine-table tr:nth-child(2)').innerHTML = tdLegends;
}

// Gère le clic sur les boutons de domaine et filtre les colonnes Actions
function initDomaineFiltre(config) {
    const domaines = [...new Set(config.map(c => c.domain))];
    const btnIds = domaines.map(d => '#btnDomaine-' + d);

    document.querySelectorAll(btnIds.join(', ')).forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll(btnIds.join(', ')).forEach(b => b.setAttribute('aria-pressed', 'false'));
            this.setAttribute('aria-pressed', 'true');

            document.querySelectorAll('#domaine-table .btn-legend').forEach(l => l.classList.remove('btn-legend--active'));
            const index = Array.from(this.closest('tr').children).indexOf(this.closest('td'));
            document.querySelector('#domaine-table').rows[1].children[index].querySelector('.btn-legend').classList.add('btn-legend--active');

            filtrerActionsParDomaine(config, this.dataset.domain);
        });
    });

    filtrerActionsParDomaine(config, domaines[0]);
}

// Masque/affiche les colonnes de la table Actions selon le domaine sélectionné
function filtrerActionsParDomaine(config, domaine) {
    config.forEach(c => {
        const td = document.getElementById(c.id)?.closest('td');
        const index = td ? Array.from(td.parentElement.children).indexOf(td) : -1;
        const legendTd = document.querySelector('#action-table').rows[1]?.children[index];

        const visible = c.domain === domaine;
        if (td) td.style.display = visible ? '' : 'none';
        if (legendTd) legendTd.style.display = visible ? '' : 'none';

        document.getElementById(c.id)?.setAttribute('aria-pressed', 'false');
        document.getElementById(c.divId).style.display = 'none';
    });
}