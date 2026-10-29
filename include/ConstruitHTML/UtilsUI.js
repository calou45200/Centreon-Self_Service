// Fichier Serveur-Dedie/include/ConstruitHTML/UtilsUI.js — utilitaires génériques réutilisables
function initFiltreSelect(elementFiltre, idSelect) {
    const champFiltre = typeof elementFiltre === 'string' ? document.getElementById(elementFiltre) : elementFiltre;
    // Le champ filtre n'existe pas sur toutes les pages
    if (!champFiltre) return;
    champFiltre.addEventListener('input', function() {
        const terme = this.value.toLowerCase();
        const options = document.getElementById(idSelect).options;
        let premierTrouve = null;
        for (let i = 0;i < options.length;i++) {
            const option = options[i];
            if (option.value === '') continue;
            const visible = option.text.toLowerCase().includes(terme);
            option.hidden = !visible;
            if (visible && !premierTrouve) {
                premierTrouve = option;
            }
        }
        if (premierTrouve) {
            premierTrouve.selected = true;
            document.getElementById(idSelect).dispatchEvent(new Event('change'));
        }
    });
}

//permet de sélectionner/désélectionner une option au clic simple, sans Ctrl
let selectedHosts = [];

function updateSelectedHosts(idSelect) {
    const options = document.getElementById(idSelect).options;
    selectedHosts = Array.from(options)
        .filter(option => option.selected)
        .map(option => option.value);
}

function initMultiSelectClic(idSelect) {
    const select = document.getElementById(idSelect);
    select.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'OPTION') {
            e.preventDefault();
            e.target.selected = !e.target.selected;
            updateSelectedHosts(idSelect);
            select.dispatchEvent(new Event('change'));
        }
    });
}


function ajusterLargeur(select) {
    // Trouver l'option ayant le texte le plus long
    let maxText = "";
    for (let option of select.options) {
        if (option.text.length > maxText.length) {
            maxText = option.text;
        }
    }
    
    // Créer un span temporaire pour mesurer le texte en pixels
    let span = document.createElement("span");
    span.style.visibility = "hidden";
    span.style.position = "absolute";
    span.style.whiteSpace = "nowrap";
    span.style.font = window.getComputedStyle(select).font;
    span.innerText = maxText;
   // console.log(JSON.stringify(span.innerText), JSON.stringify(span.textContent));
    document.body.appendChild(span);
    // Appliquer la largeur mesurée + un espace pour la flèche du select
    select.style.width = (span.getBoundingClientRect().width + 30) + "px";
   // console.log(span.offsetWidth, window.getComputedStyle(select).font);
    document.body.removeChild(span);
}

// Attache le comportement d'ouverture/fermeture à un seul bouton de recherche
function initSearchButton(wrapper) {
    const btn = wrapper.querySelector('.search-btn');
    const input = wrapper.querySelector('.search-input');

    btn.addEventListener('click', () => {
        if (!wrapper.classList.contains('active')) {
            wrapper.classList.add('active');
            setTimeout(() => input.focus(), 220);
        } else if (input.value.trim() === "") {
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
}

// Ferme un popup de validation
function fermerPopup(idPopup) {
    document.getElementById(idPopup).style.display = 'none';
}