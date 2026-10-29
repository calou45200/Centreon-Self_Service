<?php 
// fichier Serveur-Dedie/include/ConstruitHTML/host-select-mod.php
require_once __DIR__ . "/../global/DbFunctions.php";
global $AclGroup;
// var_dump($_SESSION);
$HostNames = $_SESSION['DroitsAclHosts'] ?? [];
// echo "<BR> HostNames depuis host-select-mod.php </BR>";
// var_dump($HostNames);

$ContactName = $_SESSION['UserName'] ?? '';
// echo "<BR> ContactName depuis host-select-mod.php </BR>";
// var_dump($ContactName);
?>

<table class="Table_Centre">
	<tr class="border">
        <th><label for="host_filter_mod">Recherche </label><BR></th>
        <th><label for="host_name_mod">Sélection </label><BR></th>
    </tr>
    <tr class="border">
        <td class="border">
            <div class="search-wrapper">
                <div class="search-box"> 
                   <button type="button" class="search-btn" aria-label="Ouvrir la recherche">
    	               <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                           <circle cx="11" cy="11" r="7"></circle>
                           <path d="M20 20l-3.5-3.5"></path>
                       </svg>
                   </button>
                   <input type="text" class="search-input" id="host_filter_mod" placeholder="Recherche...">
                </div>
            </div> 
            <BR>
        </td>
        <!-- <?php echo "<BR>Appel de la fonction renderHostOptions(" . var_dump($HostNames) . ")</BR>" ?> -->
        <td class="border">
            <select id="host_name_mod" name="host_name_mod">
	           <option value="">— Sélectionnez un host —</option>
                <?= renderHostOptions($HostNames) ?>
            </select>
        </td>
    </tr>
</table>
<BR>
