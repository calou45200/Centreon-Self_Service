<?php
//Fichier Serveur-Dedie/include/global/Traces.php
const REP_SQL = __DIR__ . '/../../logs/Sql/';
const REP_TEMP_PATH = __DIR__ . '/../../logs/Clapi';
$GLOBALS['REP_TEMP'] = REP_TEMP_PATH;
if (!is_dir(REP_TEMP_PATH)) {
    mkdir(REP_TEMP_PATH, 0755, true);
}

function EnregistrerRequete(string $nomFichier, string $sql): void {
    if (!is_dir(REP_SQL)) {
        mkdir(REP_SQL, 0755, true);
    }
    file_put_contents(REP_SQL . $nomFichier, $sql);
}

?>