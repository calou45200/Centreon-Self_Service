<?php
// Fichier gestion-url.php
function retrouveURL(){
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https";
        else
            $url = "https";
            
            // Ajoutez // à l'URL.
            $url .= "://";
            
            // Ajoutez l'hôte (nom de domaine, ip) à l'URL.
            $url .= $_SERVER['HTTP_HOST'];
            
            // Ajouter l'emplacement de la ressource demandée à l'URL
            //$url .= $_SERVER['REQUEST_URI'];
            // répertoire courrant
		   $chemin_relatif = dirname($_SERVER['SCRIPT_NAME']);
           return $url . $chemin_relatif ;            
}