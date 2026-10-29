<?php
// fichier Serveur-Dedie/include/ConstruitHTML/host-dep-Tables.php
?>

<table class="Table_Centre">
	<tr class="border">
        <th><label for="host_filter_dep">Recherche </label><BR></th>
        <th><label for="host_name_dep">Sélection </label><BR></th>
    </tr>
    <tr class="border">
        <td class="border">
            <div class="search-wrapper" id="hostDepSearchWrapper">
                <div class="search-box"> 
                   <button type="button" class="search-btn" aria-label="Ouvrir la recherche">
    	               <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                           <circle cx="11" cy="11" r="7"></circle>
                           <path d="M20 20l-3.5-3.5"></path>
                       </svg>
                   </button>
                   <input type="text" class="search-input" id="host_filter_dep" placeholder="Recherche...">
                </div>
            </div> 
            <BR>
        </td>
        <td class="border">
            <div style="position:relative;display:inline-block;">
                <button type="button" id="host_dropdown_btn_dep" style="min-width:250px;text-align:left;">— Sélectionnez des hosts —</button>
                <div id="host_name_dep" style="display:none;position:absolute;z-index:10;background:#fff;border:1px solid #999;max-height:250px;overflow-y:auto;min-width:100%;text-align:left;">
                    <?php foreach ($HostNames as $h): ?>
                    <label class="host-item" data-poller-id="<?= htmlspecialchars($h['poller_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="display:block;white-space:nowrap;">
                        <input type="checkbox" name="host_name_dep[]" value="<?= htmlspecialchars($h['host_name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($h['host_name'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </td>
    </tr>
</table>
<BR>
<input type="hidden" id="type_envoi_dep" name="type_envoi_dep" value="clapi">
