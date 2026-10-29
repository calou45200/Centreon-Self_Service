<!-- Fichier popup-host_validatation.php -->
<div id="popupHostValidation" class="popup-overlay" style="display: none;">
	<div class="popup-contenu">
	   <div class="cadre-popup">
	       <h2>Confirmer les modifications</h2>
	       <div id="popupHostNamechange" style="display: none;">
	           <p>
	               <strong>host_name</strong> : 
	               <span id="recapHostNameOld" font-weignt="normal"></span> → 
	               <span id="recapHostNameNew"></span>
	           </p>
	           <script type="text/javascript">	  
	               document.getElementById('recapHostNameNew').classList.add('champ-nouveau');
	           </script>
	       </div>
	       <div id="popupHostAlias" style="display: none;">
	           <p>
	               <strong>host_alias</strong> : 
	               <span id="recapHostAliasOld"></span> → 
	               <span id="recapHostAliasNew"></span>
	           </p>
	           <script type="text/javascript">	  
	               document.getElementById('recapHostAliasNew').classList.add('champ-nouveau');
                </script>
	       </div>
           <div id="popupHostAddress" style="display: none;">
	           <p>
	               <strong>host_address</strong> : 
	               <span id="recapHostAddressOld"></span> → 
                   <span id="recapHostAddressNew"></span>
	           </p>
	           <script type="text/javascript">	  
	           	   document.getElementById('recapHostAddressNew').classList.add('champ-nouveau');
	           </script>
	       </div>
	       <div id="popupTemplatesAjoutes" style="display: none;">
	           <p>
	               <strong>Templates ajoutés</strong>
               </p>
               <ul id="recapTemplatesAjoutes"></ul>
           </div>
           <div id="popupTemplatesSupprimes" style="display: none;">
	           <p>
	               <strong>Templates supprimés</strong>
	           </p>
               <ul id="recapTemplatesSupprimes"></ul>
           </div>
	   </div>
	   <div class="cadre-popup fieldChangeConf">
	       <p class="aligneTexteGras">changez le mode d'envoie si nécessaire</p>
           <input type="radio" id="radioClapi" name="ModeEnvoi" value="Clapi" checked /> 
           <label for="radioClapi">Ligne de commandes (Clapi)</label>
           <input type="radio" id="radioRestApi" name="ModeEnvoi" value="RestApi" /> 
           <label for="radioRestApi">API REST</label>
       </div>
       <BR>
       <div class="cadre-boutons">
	       <button type="button" id="btnValiderHost" class="button dark_grey">Valider</button>
           <button type="button" id="btnAnnulerHost" class="button dark_grey">Annuler</button>
       </div>
    </div>
</div>
