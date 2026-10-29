<?php
// Fichier commandgeneric.php

// Rejet si champs requis vides ce qui ne devrait pas arriver depuis le formulaire
if ($_POST['name'] === '' || $_POST['address'] === '') {
    die('Champs requis manquants.');
}


//---------------------------------------------------
// Function pour Préparer les champs du fichier clapi
//---------------------------------------------------
function nettoyerPourCsv(string $valeur): string
{
    $valeur = trim($valeur);
    $valeur = str_replace([';', "\r", "\n"], '', $valeur);
    
    if (preg_match('/^[=+\-@]/', $valeur)) {
        $valeur = "'" . $valeur;
    }
    
    return $valeur;
}