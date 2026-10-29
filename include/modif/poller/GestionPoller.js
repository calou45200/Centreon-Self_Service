// Fichier Serveur-Dedie/include/modif/poller/GestionPoller.js — tout ce qui concerne le changement de poller
// Construit les commandes CLAPI de changement de poller
function ModifPoller(hosts, pollerName) {
  return hosts.map(h => `HOST;setinstance;${h};${pollerName}`).join('\n');
}

document.addEventListener('DOMContentLoaded', function () {
	if (!document.getElementById('FormPoller')) return;
	document.getElementById('FormPoller').addEventListener('submit', function (e) {
		e.preventDefault();

		const pollerName = document.getElementById('poller_name').value;
		const hostsSelectionnes = Array.from(document.querySelectorAll('#host_name input[type="checkbox"]:checked')).map(c => c.value);

		if (!pollerName || hostsSelectionnes.length === 0) return;

		window.parent.postMessage({ type: 'popupPoller', pollerName, hosts: hostsSelectionnes }, '*');
	});
	window.addEventListener('message', function (e) {
		if (e.data && e.data.type === 'validerPoller') {
			const pollerName = document.getElementById('poller_name').value;
			const hostsSelectionnes = Array.from(document.querySelectorAll('#host_name input[type="checkbox"]:checked')).map(c => c.value);			const modeEnvoi = e.data.mode;
			const csv = ModifPoller(hostsSelectionnes, pollerName);
			envoyerCommande(
				modeEnvoi,
				'../include/command/restapi/SubmitModPollerAPI.php',
				{ hosts: JSON.stringify(hostsSelectionnes), poller: pollerName },
				'../include/command/clapi/SubmitModPoller.php',
				{ hosts: hostsSelectionnes, csv: csv.split('\n') }
			);
		}
	});
});