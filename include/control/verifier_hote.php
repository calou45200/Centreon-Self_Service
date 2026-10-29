<?php
// Fichier Serveur-Dedie/include/control/verifier_hote.php
header('Content-Type: application/json; charset=utf-8');

require_once '/etc/centreon/centreon.conf.php';

$hostName = trim($_POST['name'] ?? '');

if ($hostName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Nom d’hôte manquant.']);
    exit;
}

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $conf_centreon['hostCentreon'],
            $conf_centreon['port'] ?? '3306',
            $conf_centreon['db']
            ),
        $conf_centreon['user'],
        $conf_centreon['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
        );
    
    // Requête préparée : plus d'injection SQL possible
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM centreon.host WHERE host_name = :name');
    $stmt->execute(['name' => $hostName]);
    
    $existe = ((int) $stmt->fetchColumn()) > 0;
    
    echo json_encode(['exists' => $existe]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
