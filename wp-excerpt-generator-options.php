<?php
// Mise à jour des données par défaut
function WP_Excerpt_Generator_update() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Réglages de base
	$wp_excerpt_generator_save		= $_POST['wp_excerpt_generator_save'];
	$wp_excerpt_generator_type		= $_POST['wp_excerpt_generator_type'];
	$wp_excerpt_generator_status	= $_POST['wp_excerpt_generator_status'];
	$wp_excerpt_generator_method	= $_POST['wp_excerpt_generator_method'];
	$wp_excerpt_generator_nbletters	= $_POST['wp_excerpt_generator_nbletters'];
	$wp_excerpt_generator_nbwords	= $_POST['wp_excerpt_generator_nbwords'];
	$wp_excerpt_generator_cleaner	= $_POST['wp_excerpt_generator_cleaner'];
	$wp_excerpt_generator_breakOK	= $_POST['wp_excerpt_generator_breakOK'];
	$wp_excerpt_generator_break		= $_POST['wp_excerpt_generator_break'];
	$wp_excerpt_generator_htmlOK	= $_POST['wp_excerpt_generator_htmlOK'];

	update_option("wp_excerpt_generator_save", $wp_excerpt_generator_save);
	update_option("wp_excerpt_generator_type", $wp_excerpt_generator_type);
	update_option("wp_excerpt_generator_status", $wp_excerpt_generator_status);
	update_option("wp_excerpt_generator_method", $wp_excerpt_generator_method);
	update_option("wp_excerpt_generator_nbletters", $wp_excerpt_generator_nbletters);
	update_option("wp_excerpt_generator_nbwords", $wp_excerpt_generator_nbwords);
	update_option("wp_excerpt_generator_cleaner", $wp_excerpt_generator_cleaner);
	update_option("wp_excerpt_generator_breakOK", $wp_excerpt_generator_breakOK);
	update_option("wp_excerpt_generator_break", $wp_excerpt_generator_break);
	update_option("wp_excerpt_generator_htmlOK", $wp_excerpt_generator_htmlOK);
	
	// Si la chaîne doit se terminer par une ponctuation logique
	if(get_option("wp_excerpt_generator_cleaner") == true) {
		$cleaner = true;
	} else {
		$cleaner = false;
	}

	// Si le code HTML est conservé
	if(get_option("wp_excerpt_generator_htmlOK") == 'none') {
		$htmlOK = 'none';
	} else if(get_option("wp_excerpt_generator_htmlOK") == 'partial') {
		$htmlOK = "partial";
	} else if(get_option("wp_excerpt_generator_htmlOK") == 'total') {
		$htmlOK = 'total';
	}

	// Si la chaîne doit être terminée par quelques caractères
	if(get_option("wp_excerpt_generator_breakOK") == true) {
		$break = array(true, get_option("wp_excerpt_generator_break"));
	} else {
		$break = array(false, '');
	}

	// Vérifie que l'option "lettres" est activée et qu'un nombre de lettres a été donné...
	if($wp_excerpt_generator_method == "letters" && is_numeric($wp_excerpt_generator_nbletters) && !empty($wp_excerpt_generator_nbletters)) {
		$nbletters = get_option("wp_excerpt_generator_nbletters");
	} else {
		$nbletters = 600;
	}
	
	// Vérifie que l'option "mots" est activée et qu'un nombre de mots a été donné...
	if($wp_excerpt_generator_method == "words" && is_numeric($wp_excerpt_generator_nbwords) && !empty($wp_excerpt_generator_nbwords)) {
		$nbwords = get_option("wp_excerpt_generator_nbwords");
	} else {
		$nbwords = 100;
	}

	// Récupère le statut des données dans la base de données
	if(get_option("wp_excerpt_generator_status") == 'publish') {
		$selectContent = "post_status = 'publish'";
	} else if(get_option("wp_excerpt_generator_status") == 'future') {		
		$selectContent = "post_status = 'future'";
	} else if(get_option("wp_excerpt_generator_status") == 'publishfuture') {	
		$selectContent = "(post_status = 'publish' OR post_status = 'future')";
	} else {
		$selectContent = "post_status = 'publish'";
	}

	// Récupère le type de contenus pour créer l'extrait et sélectionne des données dans la base de données
	if(get_option("wp_excerpt_generator_type") == 'page') {
		$selectContent = $wpdb->get_results("SELECT ID, post_content FROM $table_WP_Excerpt_Generator WHERE ".$selectContent." AND post_type = 'page'");
	} else if(get_option("wp_excerpt_generator_type") == 'post') {		
		$selectContent = $wpdb->get_results("SELECT ID, post_content FROM $table_WP_Excerpt_Generator WHERE ".$selectContent." AND post_type = 'post'");
	} else if(get_option("wp_excerpt_generator_type") == 'pagepost') {	
		$selectContent = $wpdb->get_results("SELECT ID, post_content FROM $table_WP_Excerpt_Generator WHERE ".$selectContent." AND (post_type = 'page' OR post_type = 'post')");
	}

	// Boucle de mise à jour des contenus
	foreach($selectContent as $key => $content) {		
		$ID[] = $content->ID;
		$content = $content->post_content;
			
		// On adapte la fonction de formatage en fonction de la méthode utilisée
		if(get_option("wp_excerpt_generator_method") == 'paragraph') {
			$formatText[] = Limit_Paragraph($content, $htmlOK, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'words') {
			$formatText[] = Limit_Words($content, $nbwords, $htmlOK, $cleaner, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'letters') {
			$formatText[] = Limit_Letters($content, $nbletters, $htmlOK, $cleaner, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'moretag') {
			$formatText[] = Limit_More($content, $htmlOK, $break);
		}
	}
	// On combine les ID avec leur valeur et on boucle pour faire l'update
	$arrayContent = array_combine($ID, $formatText);
	if(get_option("wp_excerpt_generator_save") == true) {
		foreach($arrayContent as $key => $value) {
			$wp_excerpt_generator_update = mysql_query("UPDATE $table_WP_Excerpt_Generator SET post_excerpt = '".addslashes($value)."' WHERE ID = '".$key."' AND (post_excerpt IS NULL OR post_excerpt = '')");
		}
	} else {
		foreach($arrayContent as $key => $value) {
			$wp_excerpt_generator_update = mysql_query("UPDATE $table_WP_Excerpt_Generator SET post_excerpt = '".addslashes($value)."' WHERE ID = '".$key."'");
		}
	}
}

// Mise à jour des données par défaut
function WP_Excerpt_Generator_delete() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Réglages de base
	$wp_excerpt_generator_deleteExcerpt = $_POST['wp_excerpt_generator_deleteExcerpt'];
	update_option("wp_excerpt_generator_deleteExcerpt", $wp_excerpt_generator_deleteExcerpt);
	
	if($wp_excerpt_generator_deleteExcerpt == true) {
		$deleteContent = $wpdb->get_results("UPDATE $table_WP_Excerpt_Generator SET post_excerpt = ''");
	}
}

// Fonction d'affichage de la page de réglages de l'extension
function WP_Excerpt_Generator_Callback() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Déclencher la fonction de mise à jour (upload)
	if(isset($_POST['wp_excerpt_generator_action']) && $_POST['wp_excerpt_generator_action'] == __('Enregistrer' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_update();
	}
	
	// Déclencher la fonction de suppression des extraits
	if(isset($_POST['wp_excerpt_generator_delete']) && $_POST['wp_excerpt_generator_delete'] == __('Supprimer' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_delete();
	}

	/* --------------------------------------------------------------------- */
	/* ------------------------ Affichage de la page ----------------------- */
	/* --------------------------------------------------------------------- */
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>'; _e('Réglages de WP Excerpt Generator.','WP-Excerpt-Generator'); echo '</h2><br/>';
	_e('<strong>WP Excerpt Generator</strong> est un générateur automatisé d\'extraits pour WordPress.', 'WP-Excerpt-Generator');
	_e('Plusieurs méthodes sont exploitables pour générer des extraits comme bon nous semble :', 'WP-Excerpt-Generator');	echo '<br/>';
	echo '<ol>';
	echo '<li>'; _e('Conserver ou non les extraits déjà existants','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Choisir le type de contenus ciblés (pages, articles ou les deux)','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Choisir la méthode de création (premier paragraphe, nombre de mots, nombre de lettres...)','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Affiner l\'affichage final','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Conserver ou non le code HTML dans l\'extrait (déconseillé)','WP-Excerpt-Generator'); echo '</li>';
	echo '</ol>';
	_e('<em>N.B. : cette extension n\'est pas parfaite mais elle aide à remplir les extraits manquants sans difficulté. N\'hésitez pas à contacter <a href="http://blog.internet-formation.fr" target="_blank">Mathieu Chartier</a>, le créateur du plugin, pour de plus amples informations.</em>' , 'WP-Excerpt-Generator'); echo '<br/><br/>';
?>       
<script language=javascript>
function montrer(object) {
   if (document.getElementById) document.getElementById(object).style.display = 'block';
}

function cacher(object) {
   if (document.getElementById) document.getElementById(object).style.display = 'none';
}
</script>

<div style="float:left; width:50%">
	<!-- Formulaire de mise à jour des données -->
    <form method="post" action="">
        <h3><?php _e('Paramétrage général','WP-Excerpt-Generator'); ?></h3>
        <p>
            <label for="wp_excerpt_generator_save"><strong><?php _e('Conserver les extraits existants ou les remplacer ?','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_save" id="wp_excerpt_generator_save" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="1" <?php if(get_option("wp_excerpt_generator_save") == true) { echo 'selected="selected"'; } ?>><?php _e('Conserver','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php if(get_option("wp_excerpt_generator_save") == false) { echo 'selected="selected"'; } ?>><?php _e('Remplacer','WP-Excerpt-Generator'); ?></option>
            </select>
            <br/><em><?php _e('L\'option permet de créer les extraits manquants sans effacer les existants, ou de tout remplacer...','WP-Excerpt-Generator'); ?></em>
        </p>
        <p>
            <label for="wp_excerpt_generator_type"><strong><?php _e('Générer les extraits pour quels contenus ?','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_type" id="wp_excerpt_generator_type" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="page" <?php if(get_option("wp_excerpt_generator_type") == 'page') { echo 'selected="selected"'; } ?>><?php _e('Pages','WP-Excerpt-Generator'); ?></option>
                <option value="post" <?php if(get_option("wp_excerpt_generator_type") == 'post') { echo 'selected="selected"'; } ?>><?php _e('Articles','WP-Excerpt-Generator'); ?></option>
                <option value="pagepost" <?php if(get_option("wp_excerpt_generator_type") == 'pagepost') { echo 'selected="selected"'; } ?>><?php _e('Articles + Pages','WP-Excerpt-Generator'); ?></option>
            </select>
        </p>
        <p>
            <label for="wp_excerpt_generator_status"><strong><?php _e('Générer les extraits pour les contenus publiés ou planifiés ?','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_status" id="wp_excerpt_generator_status" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="publish" <?php if(get_option("wp_excerpt_generator_status") == 'publish') { echo 'selected="selected"'; } ?>><?php _e('Contenus publiés','WP-Excerpt-Generator'); ?></option>
                <option value="future" <?php if(get_option("wp_excerpt_generator_status") == 'future') { echo 'selected="selected"'; } ?>><?php _e('Contenus planifiés','WP-Excerpt-Generator'); ?></option>
                <option value="publishfuture" <?php if(get_option("wp_excerpt_generator_status") == 'publishfuture') { echo 'selected="selected"'; } ?>><?php _e('Les deux','WP-Excerpt-Generator'); ?></option>
            </select>
        </p>
        <p>
            <label for="wp_excerpt_generator_method"><strong><?php _e('Méthode de création des extraits','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_method" id="wp_excerpt_generator_method" style="margin-top:3px;width:40%;border:1px solid #ccc;">
                <option value="paragraph" onclick="cacher('blockWords'); cacher('blockLetters'); cacher('blockClean');" <?php if(get_option("wp_excerpt_generator_method") == 'paragraph') { echo 'selected="selected"'; } ?>><?php _e('Premier paragraphe','WP-Excerpt-Generator'); ?></option>
                <option value="words" onclick="montrer('blockWords'); montrer('blockClean'); cacher('blockLetters');" <?php if(get_option("wp_excerpt_generator_method") == 'words') { echo 'selected="selected"'; } ?>><?php _e('Nombre de mots (à définir)','WP-Excerpt-Generator'); ?></option>
                <option value="letters" onclick="montrer('blockLetters'); montrer('blockClean'); cacher('blockWords');" <?php if(get_option("wp_excerpt_generator_method") == 'letters') { echo 'selected="selected"'; } ?>><?php _e('Nombre de lettres (à définir)','WP-Excerpt-Generator'); ?></option>
                <option value="moretag" onclick="cacher('blockWords'); cacher('blockLetters');" <?php if(get_option("wp_excerpt_generator_method") == 'moretag') { echo 'selected="selected"'; } ?>><?php _e('Avant la balise MORE de WordPress','WP-Excerpt-Generator'); ?></option>
            </select>
        </p>
        <p id="blockWords" <?php if(get_option("wp_excerpt_generator_method") == 'words') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <label for="wp_excerpt_generator_nbwords"><strong><?php _e('Nombre de mots à conserver (maximum)','WP-Excerpt-Generator'); ?></strong></label><br />
            <input value="<?php echo get_option("wp_excerpt_generator_nbwords"); ?>" name="wp_excerpt_generator_nbwords" id="wp_excerpt_generator_nbwords" type="text" style="margin-top:3px;width:20%;border:1px solid #ccc;" />
        </p>
        <p id="blockLetters" <?php if(get_option("wp_excerpt_generator_method") == 'letters') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <label for="wp_excerpt_generator_nbletters"><strong><?php _e('Nombre de lettres à conserver (maximum)','WP-Excerpt-Generator'); ?></strong></label><br />
            <input value="<?php echo get_option("wp_excerpt_generator_nbletters"); ?>" name="wp_excerpt_generator_nbletters" id="wp_excerpt_generator_nbletters" type="text" style="margin-top:3px;width:20%;border:1px solid #ccc;" />
        </p>

        <p id="blockClean" <?php if(get_option("wp_excerpt_generator_method") == 'letters' || get_option("wp_excerpt_generator_method") == 'words') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <label for="wp_excerpt_generator_cleaner"><strong><?php _e('Terminer l\'extrait par une ponctuation propre ? (conseillé)','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_cleaner" id="wp_excerpt_generator_cleaner" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="1" <?php if(get_option("wp_excerpt_generator_cleaner") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php if(get_option("wp_excerpt_generator_cleaner") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <br/><em><?php _e('L\'option permet de finir les phrases proprement par une ponctuation logique.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p>
            <label for="wp_excerpt_generator_htmlOK"><strong><?php _e('Conserver le code HTML ? (déconseillé)','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_htmlOK" id="wp_excerpt_generator_htmlOK" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="total" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'total') { echo 'selected="selected"'; } ?>><?php _e('Totalement','WP-Excerpt-Generator'); ?></option>
                <option value="partial" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'partial') { echo 'selected="selected"'; } ?>><?php _e('Partiellement (gras, italique...)','WP-Excerpt-Generator'); ?></option>
                <option value="none" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'none') { echo 'selected="selected"'; } ?>><?php _e('Pas du tout','WP-Excerpt-Generator'); ?></option>
            </select>
            <br/><em><?php _e('Attention ! Si vous coupez par groupes de mots ou lettres, vous risquez de casser la logique du code HTML...','WP-Excerpt-Generator'); ?></em>
        </p>
        <p>
            <label for="wp_excerpt_generator_breakOK"><strong><?php _e('Ajouter une chaîne de fin à l\'extrait ?','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_breakOK" id="wp_excerpt_generator_breakOK" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="1" onclick="montrer('blockBreak');" <?php if(get_option("wp_excerpt_generator_breakOK") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" onclick="cacher('blockBreak');" <?php if(get_option("wp_excerpt_generator_breakOK") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <br/><em><?php _e('L\'option permet d\'ajouter quelques caractères pour faire comprendre que le texte continu.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p id="blockBreak" <?php if(get_option("wp_excerpt_generator_breakOK") == false) { echo 'style="display:none;"'; } ?>>
            <label for="wp_excerpt_generator_break"><strong><?php _e('Chaîne de caractère affichée après l\'extrait','WP-Excerpt-Generator'); ?></strong></label><br />
            <input value="<?php echo get_option("wp_excerpt_generator_break"); ?>" name="wp_excerpt_generator_break" id="wp_excerpt_generator_break" type="text" style="margin-top:3px;width:20%;border:1px solid #ccc;" />
            <br/><em><?php _e('Exemples : " (...)", " [...]", " ..."','WP-Excerpt-Generator'); ?></em>
        </p>

    <p class="submit"><input type="submit" name="wp_excerpt_generator_action" class="button-primary" value="<?php _e('Enregistrer' , 'WP-Excerpt-Generator'); ?>" /></p>
    </form>
</div>

<div style="float:left; width:50%;">
    <form method="post" action="">
        <h3><?php _e('Nettoyage des extraits...','WP-Excerpt-Generator'); ?></h3>
        <p>
            <label for="wp_excerpt_generator_deleteExcerpt"><strong><?php _e('Supprimer tous les extraits de la base ?','WP-Excerpt-Generator'); ?></strong></label><br />
            <select name="wp_excerpt_generator_deleteExcerpt" id="wp_excerpt_generator_deleteExcerpt" style="margin-top:3px;width:20%;border:1px solid #ccc;">
                <option value="1" onclick="javascript:return(confirm('<?php _e('Etes-vous sûrs de vouloir supprimer les extraits existants ?\nN.B. : aucun extrait ne sera conservé !','WP-Advanced-Search'); ?>'));"><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php echo 'selected="selected"'; ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
        </p>   
    <p class="submit"><input type="submit" name="wp_excerpt_generator_delete" onclick="javascript:return(confirm('<?php _e('Dernière chance avant la suppression complète des extraits...\nVous êtes toujours sûrs de vous ?','WP-Advanced-Search'); ?>'));" class="button-primary" value="<?php _e('Supprimer' , 'WP-Excerpt-Generator'); ?>" /></p>
    </form>
</div>
<?php
echo '</div>'; // Fin de la page d'admin
} // Fin de la fonction Callback
?>