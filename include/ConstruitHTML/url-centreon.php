<?php   
    // fichier url-centreon.php
    // Pour gestion des urls vers centreon

require_once __DIR__ . '/../global/DbFunctions.php';
require_once __DIR__ . '/../control/gestion-url.php';
require_once __DIR__ . '/../DBFunctions/DBFunctionsHost.php';
function AfficherPopupResultat(string $message, string $type = 'success', ?string $url = null): void
{
    $couleur = ($type === 'success') ? 'green' : 'red';
    $contenu = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $boutonLien = $url
        ? "<button type='button' onclick=\"window.open('" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "','_blank');\" class='button dark_grey'>Ouvrir le lien</button>"
        : '';

    echo "
    <div id='popupResultat' class='popup-overlay' style='display:flex;'>
        <div class='popup-contenu'>
            <div class='cadre-popup'><STRONG style='color: {$couleur};'>{$contenu}</STRONG></div>
            <div class='cadre-boutons'>{$boutonLien}
                <button type='button' onclick=\"document.getElementById('popupResultat').style.display='none';\" class='button dark_grey'>
                    Fermer
                </button>
            </div>
        </div>
    </div>";
}

function UrlCentreonHost($HostName) {
    // Génère l'URL vers la page de détails d'un hôte dans Centreon
    $hostInfo = RechercheHostId($HostName);
    if (!empty($hostInfo[0]['host_id'])) {
        $hostId = $hostInfo[0]['host_id'];
        $scheme = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
            ? $_SERVER['HTTP_X_FORWARDED_PROTO']
            : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');            
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/centreon';
        $lien = $baseUrl . "/main.php?p=60101&o=c&host_id=" . $hostId;
        return [
            'success' => true,
            'message' => "Import réussi pour {$HostName}",
            'url' => $lien,
            'lien' => retrouveURL() . '/main.php?p=20202&o=host&host_id=' . urlencode($hostId),
        ];
    }
    return [
        'success' => false,
        'message' => "Échec de l'import pour {$HostName}",
        'url' => null,
        'lien' => '',
    ];}
?>