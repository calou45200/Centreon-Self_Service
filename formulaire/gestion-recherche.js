
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
