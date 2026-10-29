// Fichier Serveur-Dedie/include/global/script.js
// document.addEventListener("DOMContentLoaded", () => {
//   initFiltreSelect('poller_filter', 'poller_name');
//   initGestionTemplateSelect();
//   initGestionAddHost();
// });
document.addEventListener("DOMContentLoaded", () => {
  initFiltreSelect('poller_filter', 'poller_name');
  // Ces fonctions n'existent pas dans toutes les pages qui chargent script.js
  if (typeof initGestionTemplateSelect === 'function') initGestionTemplateSelect();
  if (typeof initGestionAddHost === 'function') initGestionAddHost();
});