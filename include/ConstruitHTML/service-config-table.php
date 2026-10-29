<?php
    // Serveur-Dedie/include/global/service-config-table.php
?>
<style>
    .conteneurcentre {
        display: flex;
        justify-content: center;
    }
    .hosts-selectionnes-cadre {
        border: 1px solid rgb(190,190,190);
        padding: 10px 15px;
        box-sizing: border-box;
    }
    .hosts-selectionnes-cadre #clearHostsBtn {
        display: block;
        margin: 0 auto;
    }
    .hosts-selectionnes-cadre ul {
        margin: 8px 0;
        padding-left: 20px;
    }
</style>
<script src="../include/ConstruitHTML/UtilsUI.js"></script>
<script src="../include/command/EnvoiCommande.js"></script>
<script src="../include/modif/poller/GestionPoller.js"></script>
<script src="../include/modif/host/GestionHost.js"></script>
<script src="../include/modif/host/ModHost.js"></script>

<script type="text/javascript">
    // Ajoute un host dans la liste des hosts sélectionnés (évite les doublons)
    function ajouterHostSelectionne(nom) {
        const liste = document.getElementById('hostsListe');
        if ([...liste.children].some(li => li.dataset.host === nom)) return;

        const li = document.createElement('li');
        li.dataset.host = nom;
        li.textContent = nom;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.addEventListener('click', () => li.remove());

        // Envoie chaque host sélectionné avec le formulaire
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'hosts[]';
        input.value = nom;
        li.appendChild(input);
        li.appendChild(btn);
        liste.appendChild(li);
    }

    // Gère l'ouverture/fermeture des barres de recherche animées
    function initSearchButtons() {
           	document.querySelectorAll('.search-wrapper').forEach(wrapper => {
            const btn = wrapper.querySelector('.search-btn');
            const input = wrapper.querySelector('.search-input');
            btn.addEventListener('click', () => {
        	   if (!wrapper.classList.contains('active')) {
                    wrapper.classList.add('active');
                    setTimeout(() => input.focus(), 220);
              } 
              else if (input.value.trim() === "") {
                    wrapper.classList.remove('active');
              }
            });
            document.addEventListener('click', (e) => {
            	if (!wrapper.contains(e.target) && input.value.trim() === "") {
                	wrapper.classList.remove('active');
                }
            });
            input.addEventListener('keydown', (e) => {
        	   if (e.key === "Escape" && input.value.trim() === "") {
                    wrapper.classList.remove('active');
                }
        });
      });
    }
    // Appeler la fonction au chargement pour initialiser la taille du select
    // Exécution directe car le DOM est déjà chargé au moment de l'injection
     setTimeout(() => {
        initSearchButtons();

        if (document.getElementById('clearHostsBtn')) {
            document.getElementById('clearHostsBtn').addEventListener('click', () => {
                document.getElementById('hostsListe').innerHTML = '';
            });
        }
        if (document.getElementById('host_name')) {
            initFiltreSelect('host_filter', 'host_name');
            document.getElementById('host_name').addEventListener('change', function () {
                console.log('change host_name:', this.value);
                if (this.value) ajouterHostSelectionne(this.value);
            });
        }
        if (document.getElementById('host_name_mod')) {
            initFiltreSelect('host_filter_mod', 'host_name_mod');
            initMultiSelectClic('host_name_mod');
            document.getElementById('host_name_mod').addEventListener('change', function () {
                chargerConfigHost(this.value);
            });
            document.getElementById('HostConfigTableBody').addEventListener('input', function (e) {
                if (['Changehost_name', 'Changehost_alias', 'Changehost_address'].includes(e.target.id)) {
                    const original = e.target.id === 'Changehost_name'
                        ? document.getElementById('Changehost_name_old').value
                        : e.target.dataset.original;
                    e.target.classList.toggle('champ-modifie', e.target.value !== original);
                }
                if (e.target.id === 'Changehost_name') {
                    console.log('Nouveau Nom : ' + e.target.value);
                }
            });
        }

        if (document.getElementById('host_name')) ajusterLargeur(document.getElementById('host_name'));
        if (document.getElementById('host_name_mod')) ajusterLargeur(document.getElementById('host_name_mod'));

        function ajusterLargeurHostsListe() {
            const table = document.querySelector('.Table_Centre');
            const cadre = document.querySelector('.hosts-selectionnes-cadre');
            if (table && cadre && table.offsetWidth > 0) cadre.style.width = table.offsetWidth + 'px';
        }
        const tableCentre = document.querySelector('.Table_Centre');
        if (tableCentre && window.ResizeObserver) {
            new ResizeObserver(ajusterLargeurHostsListe).observe(tableCentre);
        }
        ajusterLargeurHostsListe();

        window.addEventListener('message', (e) => {
            if (e.data === 'recalcWidth') {
                if (document.getElementById('host_name')) ajusterLargeur(document.getElementById('host_name'));
                if (document.getElementById('host_name_mod')) ajusterLargeur(document.getElementById('host_name_mod'));
            }
        });
        window.addEventListener('message', (e) => {
            if (e.data === 'recalcWidth') {
                if (document.getElementById('host_name')) ajusterLargeur(document.getElementById('host_name'));
                if (document.getElementById('host_name_mod')) ajusterLargeur(document.getElementById('host_name_mod'));
            }
        });
    }, 0);
</script>
    
<div class="conteneurcentre"><label><b>Selectionner un hôte : </b></label></div><br>
        <table class="Table_Centre">
        <tr class="border">
            <th><label for="host_filter">Filtrer hote </label><BR></th>
            <th><label for="host_name">Sélection </label><BR></th>
        </tr>
        <tr class="border">
            <td class="border">
                <div class="search-wrapper">
                    <div class="search-box">
                        <button type="button" class="search-btn"
                            aria-label="Ouvrir la recherche">
                            <svg viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="M20 20l-3.5-3.5"></path></svg>
                        </button>
                        <input type="text" class="search-input"
                            id="host_filter"
                            placeholder="Recherche...">
                    </div>
                </div> <BR>
            </td>
            <td class="border">
                <select id="host_name" name="host_name">
                    <option value="">— Sélectionnez un host —</option>
                    <?= renderHostOptionServices($_SESSION['DroitsAclHosts'] ?? []) ?>
                </select>
            </td>
        </tr>
    </table>

<div class="conteneurcentre">
    <div class="hosts-selectionnes-cadre">
        <label><b>Hosts sélectionnés :</b></label>
        <ul id="hostsListe"></ul>
        <button type="button" id="clearHostsBtn">Vider tout</button>
    </div>
</div>

<label><b>Sélection Template : </b></label> <br>
<table>
    <tr>
        <th><label for="brique">Brique</label></th>
        <th><label for="famille">Famille</label></th>
        <th><label for="type">Type</label></th>
        <th><label for="domaine">Domaine</label></th>
        <th id="vecteur-ts" hidden="hidden"><label for="vecteur">Vecteur</label></th>
    </tr>
    <tr>
        <td>
            <select id="brique" required>
                <option value="">— Sélectionnez une brique —</option>
            </select>
        </td>
        <td>
            <select id="famille" disabled required>
                <option value="">— Sélectionnez une famille —</option>
            </select>
        </td>
        <td>
            <select id="type" disabled required>
                <option value="">— Sélectionnez un type —</option>
            </select>
        </td>
        <td>
            <select id="domaine" name="domaine" disabled required>
                <option value="">— Sélectionnez un domaine —</option>
            </select>
        </td>
        <td id="vecteur-bloc" hidden="hidden">
            <select id="vecteur" disabled></select>
        </td>
    </tr>
</table>

<input type="hidden" id="template_id" name="template_id">

<p id="template-selection"></p>
<p>
    <strong><i>Service ajouté dans centreon :</i></strong>
    <output id="template_name" name="template_name">sélection vide</output>
    <input type="hidden" id="TS_Name" name="TS_Name">
</p>
<BR>

<table id="ServiceConfigTable" style="display: none;">
	<tr>
        <th>Template</th>
        <th>Service</th>
    </tr>
    <tbody id="ServiceConfigTableBody"></tbody>
</table>
<BR>
