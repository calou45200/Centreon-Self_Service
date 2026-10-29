<?php
// Fichier Serveur-Dedie/include/control/verifier_login.php
include __DIR__ . '/../DBFunctions/DBFunctionsUsers.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../global/DbFunctions.php';

$login = trim($_POST['login_user'] ?? '');
$motDePasse = $_POST['login_pass'] ?? '';
error_log('login: ' . $login . ' / pass: ' . $motDePasse);
if ($login === '' || $motDePasse === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Identifiants manquants.']);
    exit;
}

$resultat = RechercheUser($login);

if (empty($resultat) || empty($resultat[0]['PWD']) || !password_verify($motDePasse, $resultat[0]['PWD'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Identifiants invalides.']);
    exit;
}

session_start();
$_SESSION['souscription_login'] = $login;
$_SESSION['souscription_derniere_activite'] = time();

echo json_encode(['success' => true]);