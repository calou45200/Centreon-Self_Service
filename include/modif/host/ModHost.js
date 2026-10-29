// Fichier Serveur-Dedie/include/modif/host/ModHost.js — point d'entrée, initialise toggle des divs
function initToggleDivs() {
    const radios = document.querySelectorAll('input[name="ActionHost"]');
    const divs = {
        ChangePoller: document.getElementById("ChangePoller"),
        Changehost: document.getElementById("ModHost")
    };

    function updateDisplay() {
        const selected = document.querySelector('input[name="ActionHost"]:checked').value;
        for (const [value, div] of Object.entries(divs)) {
            div.style.display = (value === selected) ? "block" : "none";
        }
    }

    radios.forEach(radio => radio.addEventListener('change', updateDisplay));

    updateDisplay();
}