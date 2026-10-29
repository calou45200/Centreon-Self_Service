<?php
// Fichier Serveur-Dedie/include/global/DBFunctions/DBFunctionPollers.php
    // A activer pour débugger
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
require_once __DIR__ . '/../global/DbFunctions.php';

function RecherchePollers(): array {
    try {
        $db = connecterBDD();
        return $db->query('SELECT name FROM centreon.nagios_server ORDER BY name')->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function RechercheDefaultPoller() {
    try {
        $db = connecterBDD();
        return $db->query(
            'SELECT name
                FROM centreon.nagios_server
                WHERE is_default = 1
                LIMIT 1'
        )->fetchColumn();
    } catch (PDOException $e) {
        return '';
    }
}
?>