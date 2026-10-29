<?php
function ListSession(){
session_start();

// Lister toutes les clés et valeurs de la session
foreach ($_SESSION as $cle => $valeur) {
    echo "Clé : " . htmlspecialchars($cle) . " - Valeur : " . htmlspecialchars($valeur) . "<br>";
}

// Afficher l'ID de la session courante
echo "ID de session : " . session_id() . "<br>";
var_dump($_SESSION);
}
ListSession();