<!-- Fichier Serveur-Dedie/include/modif/host/ModHost.php -->
<div id="ModHost">
    <h2>Modification de la configuration de l'hote</h2>
    <?php $HostNames = $_SESSION['DroitsAclHosts'] ?? []; ?>
    <form id="FormHost">
        <?php include __DIR__ . '/../../ConstruitHTML/host-select-mod.php'; ?>
        <?php include __DIR__ . '/../../ConstruitHTML/host-config-table.php'; ?>
        <div id="divsubmit">
            <button type="submit" id="btnSubmit"
                class="button dark_grey">Appliquer les changements</button>
        </div>
        <BR>
        <?php include __DIR__ . '/../../popup/popup-template-non-ajoute.php'; ?>
        <?php include __DIR__ . '/../../popup/popup-host-validation.php'; ?>
    </form>
</div>
