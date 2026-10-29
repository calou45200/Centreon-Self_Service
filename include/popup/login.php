<?php
// fichier Serveur-Dedie/include/popup/login.php
?>
<div id="popupLoginOverlay" class="popup-overlay">
    <div class="popup-contenu" tabindex="-1">
        <div class="cadre-popup">
            <h2>Connexion</h2>
            <form id="FormLogin">
                <table class="Table_Centre">
                    <tr class="border">
                        <th><label for="login_user">Identifiant</label></th>
                        <td class="border">
                            <input type="text" id="login_user" name="login_user" required>
                        </td>
                    </tr>
                    <tr class="border">
                        <th><label for="login_pass">Mot de passe</label></th>
                        <td class="border">
                            <input type="password" id="login_pass" name="login_pass" required>
                        </td>
                    </tr>
                </table>
                <p id="loginErreur" style="color: red; display: none;"></p>
                <div class="cadre-boutons">
                    <button type="submit" id="btnLogin" class="button dark_grey">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
console.log('1 - DOMContentLoaded');
document.getElementById('FormLogin').addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('2 - submit intercepté');
    const erreurEl = document.getElementById('loginErreur');
    erreurEl.style.display = 'none';

    fetch('./include/control/verifier_login.php', {
        method: 'POST',
        body: new URLSearchParams(new FormData(this))
    })
        .then(response => {
            console.log('3 - réponse reçue, status:', response.status);
            return response.json();
        })
        .then(resultat => {
            console.log('4 - résultat JSON:', resultat);
            if (resultat.error) {
                erreurEl.textContent = resultat.error;
                erreurEl.style.display = 'block';
                return;
            }
            console.log('5 - succès, fermeture popup');
            document.getElementById('popupLoginOverlay').style.display = 'none';
            location.reload();
        })
        .catch(err => console.error('6 - erreur fetch/JSON:', err));
});
});
</script>