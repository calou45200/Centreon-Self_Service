<?php
	// Fichier logo.php 
	// a supprimer en prod dans /usr/share/centreon/www/souscription/include/global/
	session_start();
	$UserName = $_SESSION['UserName'] ?? '';
	if (isset($_GET['logout'])) {
		$_SESSION = [];
		session_destroy();
		header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
		exit;
	}
	// $AclGroup = $_SESSION['AclGroup'] ?? [];
	global $AclGroup;
	require_once __DIR__ . '/../../config.php';
	$cheminImage = BASE_URL . '/Image/' . $LogoName;
	
?>

<div class="conteneurimage">
	<img  src="<?php echo $cheminImage; ?>" alt="Mon logo">
	<!-- <?php var_dump($_SESSION['AclGroup']); ?> -->
	<div class="Div-3D">
		<div class="infoUtilisateur">Utilisateur : <?= htmlspecialchars($UserName, ENT_QUOTES, 'UTF-8') ?></div>
		<div class="infoUtilisateur">Droits : <?= htmlspecialchars($AclGroup[0]["acl_group_name"] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
		<a href="?logout=1" class="btn-logout" title="Se déconnecter">Déconnection</a>
	</div>
</div>
