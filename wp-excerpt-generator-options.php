<?php
// Mise à jour des données par défaut
function WP_Excerpt_Generator_update() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Réglages de base
	$wp_excerpt_generator_save		= $_POST['wp_excerpt_generator_save'];
	$wp_excerpt_generator_type		= $_POST['wp_excerpt_generator_type'];
	$wp_excerpt_generator_status	= $_POST['wp_excerpt_generator_status'];
	$wp_excerpt_generator_method	= $_POST['wp_excerpt_generator_method'];
	$wp_excerpt_generator_owntag	= $_POST['wp_excerpt_generator_owntag'];
	$wp_excerpt_generator_nbletters	= $_POST['wp_excerpt_generator_nbletters'];
	$wp_excerpt_generator_nbwords	= $_POST['wp_excerpt_generator_nbwords'];
	$wp_excerpt_generator_cleaner	= $_POST['wp_excerpt_generator_cleaner'];
	$wp_excerpt_generator_breakOK	= $_POST['wp_excerpt_generator_breakOK'];
	$wp_excerpt_generator_break		= $_POST['wp_excerpt_generator_break'];
	$wp_excerpt_generator_htmlOK	= $_POST['wp_excerpt_generator_htmlOK'];
	$wp_excerpt_generator_htmlBR	= $_POST['wp_excerpt_generator_htmlBR'];

	update_option("wp_excerpt_generator_save", $wp_excerpt_generator_save);
	update_option("wp_excerpt_generator_type", $wp_excerpt_generator_type);
	update_option("wp_excerpt_generator_status", $wp_excerpt_generator_status);
	update_option("wp_excerpt_generator_method", $wp_excerpt_generator_method);
	update_option("wp_excerpt_generator_owntag", $wp_excerpt_generator_owntag);
	update_option("wp_excerpt_generator_nbletters", $wp_excerpt_generator_nbletters);
	update_option("wp_excerpt_generator_nbwords", $wp_excerpt_generator_nbwords);
	update_option("wp_excerpt_generator_cleaner", $wp_excerpt_generator_cleaner);
	update_option("wp_excerpt_generator_breakOK", $wp_excerpt_generator_breakOK);
	update_option("wp_excerpt_generator_break", $wp_excerpt_generator_break);
	update_option("wp_excerpt_generator_htmlOK", $wp_excerpt_generator_htmlOK);
	update_option("wp_excerpt_generator_htmlBR", $wp_excerpt_generator_htmlBR);
}

// Fonction de génération manuelle des extraits
function WP_Excerpt_Generator_generate() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales
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

	// Si le code HTML est conservé
	if(get_option("wp_excerpt_generator_htmlBR") == true) {
		$htmlBR = true;
	} else {
		$htmlBR = false;
	}

	// Si la chaîne doit être terminée par quelques caractères
	if(get_option("wp_excerpt_generator_breakOK") == true) {
		$break = array(true, get_option("wp_excerpt_generator_break"));
	} else {
		$break = array(false, '');
	}

	// Vérifie que l'option "Fin de chaîne"
	if($wp_excerpt_generator_method == "owntag" && is_string($wp_excerpt_generator_owntag) && !empty($wp_excerpt_generator_owntag)) {
		$owntag = get_option("wp_excerpt_generator_owntag");
	} else {
		$owntag = '';
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
		// On récupère les ID dans un tableau pour la mise à jour et les contenus à traiter
		$ID[] = $content->ID;
		$content = $content->post_content;
			
		// On adapte la fonction de formatage en fonction de la méthode utilisée
		if(get_option("wp_excerpt_generator_method") == 'paragraph') {
			$formatText[] = Limit_Paragraph($content, $htmlOK, $htmlBR, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'words') {
			$formatText[] = Limit_Words($content, $nbwords, $htmlOK, $htmlBR, $cleaner, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'letters') {
			$formatText[] = Limit_Letters($content, $nbletters, $htmlOK, $htmlBR, $cleaner, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'moretag') {
			$formatText[] = Limit_More($content, $htmlOK, $htmlBR, $break);
		} else if(get_option("wp_excerpt_generator_method") == 'owntag') {
			$formatText[] = Limit_OwnTag($content, $owntag, $htmlOK, $htmlBR, $break);
		}
	}
	// On combine les ID avec leur valeur et on boucle pour faire l'update
	$arrayContent = array_combine($ID, $formatText);
	if(get_option("wp_excerpt_generator_save") == true) {
		foreach($arrayContent as $key => $value) {
			$wp_excerpt_generator_update = $wpdb->query("UPDATE $table_WP_Excerpt_Generator SET post_excerpt = '".mysql_real_escape_string($value)."' WHERE ID = '".mysql_real_escape_string(htmlspecialchars($key))."' AND (post_excerpt IS NULL OR post_excerpt = '')");
		}
	} else {
		foreach($arrayContent as $key => $value) {
			$wp_excerpt_generator_update = $wpdb->update($table_WP_Excerpt_Generator, array('post_excerpt' => $value), array('ID' => $key));
		}
	}
}

// Fonction de lancement du générateur automatique d'extraits...
function WP_Excerpt_Generator_update_maj_auto() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales
	$wp_excerpt_generator_maj = $_POST['wp_excerpt_generator_maj'];
	update_option("wp_excerpt_generator_maj", $wp_excerpt_generator_maj);
}

// Suppression complète des données
function WP_Excerpt_Generator_delete() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Réglages de base
	$wp_excerpt_generator_deleteExcerpt = $_POST['wp_excerpt_generator_deleteExcerpt'];
	update_option("wp_excerpt_generator_deleteExcerpt", $wp_excerpt_generator_deleteExcerpt);
	
	if($wp_excerpt_generator_deleteExcerpt == true) {
		$deleteContent = $wpdb->get_results("UPDATE $table_WP_Excerpt_Generator SET post_excerpt = ''");
	}
}

// Suppression des extraits sélectionnés
function WP_Excerpt_Generator_deleteSelectedExcerpt() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Réglages de base
	$wp_excerpt_generator_deleteSelectedExcerpt = $_POST['wp_excerpt_generator_deleteSelectedExcerpt'];
	
	if(!in_array('aucun',$wp_excerpt_generator_deleteSelectedExcerpt)) {
		$deleteContent = "UPDATE $table_WP_Excerpt_Generator SET post_excerpt = '' WHERE ";
		$countExcerpt = count($wp_excerpt_generator_deleteSelectedExcerpt);
		$nb = 0;
		foreach($wp_excerpt_generator_deleteSelectedExcerpt as $IDExcerpt) {
			$deleteContent .= "ID = ".$IDExcerpt."";
			if($nb < $countExcerpt-1) {
				$deleteContent .= " OR ";
			}
			$nb++;
		}
		$deleteSelectedContent = $wpdb->get_results($deleteContent);
	}
}

// Fonction d'affichage de la page de réglages de l'extension
function WP_Excerpt_Generator_Callback() {
	global $wpdb, $table_WP_Excerpt_Generator; // insérer les variables globales

	// Déclencher la fonction de mise à jour (upload)
	if(isset($_POST['wp_excerpt_generator_action']) && $_POST['wp_excerpt_generator_action'] == __('Enregistrer' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_update();
	}
	
	// Déclencher la fonction de mise à jour (upload)
	if(isset($_POST['wp_excerpt_generator_generate']) && $_POST['wp_excerpt_generator_generate'] == __('Générer les extraits' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_generate();
	}
	
	// Déclencher la fonction de mise à jour automatique des extraits (upload)
	if(isset($_POST['wp_excerpt_generator_action_maj_auto']) && $_POST['wp_excerpt_generator_action_maj_auto'] == __('Enregistrer' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_update_maj_auto();
	}
	
	// Déclencher la fonction de suppression des extraits
	if(isset($_POST['wp_excerpt_generator_delete']) && $_POST['wp_excerpt_generator_delete'] == __('Supprimer' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_delete();
	}
	
	// Déclencher la fonction de suppression des extraits sélectionnés uniquement
	if(isset($_POST['wp_excerpt_generator_deleteSelectedExcerpt_choice']) && $_POST['wp_excerpt_generator_deleteSelectedExcerpt_choice'] == __('Supprimer ces extraits' , 'WP-Excerpt-Generator')) {
		WP_Excerpt_Generator_deleteSelectedExcerpt();
	}

	/* --------------------------------------------------------------------- */
	/* ------------------------ Affichage de la page ----------------------- */
	/* --------------------------------------------------------------------- */
	echo '<div class="wrap excerpt-generator-admin">';
	echo '<div class="block-info">';
	echo '<div class="icon">';
	echo '<h2>'; _e('Réglages de WP Excerpt Generator','WP-Excerpt-Generator'); echo '</h2><br/>';
	echo '</div>';
	echo '<div class="text">';
	_e('<strong>WP Excerpt Generator</strong> est un générateur automatisé d\'extraits pour WordPress.', 'WP-Excerpt-Generator');
	_e('Plusieurs méthodes sont exploitables pour générer des extraits comme bon nous semble :', 'WP-Excerpt-Generator');	echo '<br/>';
	echo '<ol>';
	echo '<li>'; _e('Conserver ou non les extraits déjà existants','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Choisir le type de contenus ciblés (pages, articles ou les deux)','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Choisir la méthode de création (premier paragraphe, nombre de mots, nombre de lettres...)','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Affiner l\'affichage final','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Conserver ou non le code HTML dans l\'extrait','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Générer automatiquement des extraits selon les paramètres définis','WP-Excerpt-Generator'); echo '</li>';
	echo '<li>'; _e('Nettoyer et supprimer les extraits existants (générés ou non)','WP-Excerpt-Generator'); echo '</li>';
	echo '</ol>';
	_e('<em>N.B. : cette extension n\'est pas parfaite mais elle aide à remplir les extraits manquants sans difficulté. N\'hésitez pas à contacter <a href="http://blog.internet-formation.fr" target="_blank">Mathieu Chartier</a>, le créateur du plugin, pour de plus amples informations.</em>' , 'WP-Excerpt-Generator'); 
	echo '<br/>';
	echo '</div>';
	echo '</div>';
?>       
<script type="text/javascript">
function montrer(object) {
   if (document.getElementById) document.getElementById(object).style.display = 'block';
}

function cacher(object) {
   if (document.getElementById) document.getElementById(object).style.display = 'none';
}
</script>

<div class="block">
    <div class="col first-col">
    <!-- Formulaire de mise à jour des données -->
    <form method="post" action="">
        <h4><?php _e('Paramétrage général','WP-Excerpt-Generator'); ?></h4>
        <p class="tr">
            <select name="wp_excerpt_generator_save" id="wp_excerpt_generator_save">
                <option value="1" <?php if(get_option("wp_excerpt_generator_save") == true) { echo 'selected="selected"'; } ?>><?php _e('Conserver','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php if(get_option("wp_excerpt_generator_save") == false) { echo 'selected="selected"'; } ?>><?php _e('Remplacer','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_save"><strong><?php _e('Conserver les extraits existants ou les remplacer ?','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet de créer les extraits manquants sans effacer les existants, ou de tout remplacer...','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr">
            <select name="wp_excerpt_generator_type" id="wp_excerpt_generator_type">
                <option value="page" <?php if(get_option("wp_excerpt_generator_type") == 'page') { echo 'selected="selected"'; } ?>><?php _e('Pages','WP-Excerpt-Generator'); ?></option>
                <option value="post" <?php if(get_option("wp_excerpt_generator_type") == 'post') { echo 'selected="selected"'; } ?>><?php _e('Articles','WP-Excerpt-Generator'); ?></option>
                <option value="pagepost" <?php if(get_option("wp_excerpt_generator_type") == 'pagepost') { echo 'selected="selected"'; } ?>><?php _e('Articles + Pages','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_type"><strong><?php _e('Générer les extraits pour quels contenus ?','WP-Excerpt-Generator'); ?></strong></label>
        </p>
        <p class="tr">
            <select name="wp_excerpt_generator_status" id="wp_excerpt_generator_status">
                <option value="publish" <?php if(get_option("wp_excerpt_generator_status") == 'publish') { echo 'selected="selected"'; } ?>><?php _e('Contenus publiés','WP-Excerpt-Generator'); ?></option>
                <option value="future" <?php if(get_option("wp_excerpt_generator_status") == 'future') { echo 'selected="selected"'; } ?>><?php _e('Contenus planifiés','WP-Excerpt-Generator'); ?></option>
                <option value="publishfuture" <?php if(get_option("wp_excerpt_generator_status") == 'publishfuture') { echo 'selected="selected"'; } ?>><?php _e('Les deux','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_status"><strong><?php _e('Générer les extraits pour les contenus publiés ou planifiés ?','WP-Excerpt-Generator'); ?></strong></label>
        </p>
        <p class="tr">
            <select name="wp_excerpt_generator_method" id="wp_excerpt_generator_method">
                <option value="paragraph" onclick="cacher('blockWords'); cacher('blockLetters'); cacher('blockClean'); cacher('blockOwn');" <?php if(get_option("wp_excerpt_generator_method") == 'paragraph') { echo 'selected="selected"'; } ?>><?php _e('Premier paragraphe','WP-Excerpt-Generator'); ?></option>
                <option value="words" onclick="montrer('blockWords'); montrer('blockClean'); cacher('blockLetters'); cacher('blockOwn');" <?php if(get_option("wp_excerpt_generator_method") == 'words') { echo 'selected="selected"'; } ?>><?php _e('Nombre de mots (à définir)','WP-Excerpt-Generator'); ?></option>
                <option value="letters" onclick="montrer('blockLetters'); montrer('blockClean'); cacher('blockWords'); cacher('blockOwn');" <?php if(get_option("wp_excerpt_generator_method") == 'letters') { echo 'selected="selected"'; } ?>><?php _e('Nombre de lettres (à définir)','WP-Excerpt-Generator'); ?></option>
                <option value="moretag" onclick="cacher('blockWords'); cacher('blockLetters'); cacher('blockClean'); cacher('blockOwn');" <?php if(get_option("wp_excerpt_generator_method") == 'moretag') { echo 'selected="selected"'; } ?>><?php _e('Avant la balise MORE de WordPress','WP-Excerpt-Generator'); ?></option>
                <option value="owntag" onclick="montrer('blockOwn'); cacher('blockWords'); cacher('blockLetters'); montrer('blockClean');" <?php if(get_option("wp_excerpt_generator_method") == 'owntag') { echo 'selected="selected"'; } ?>><?php _e('Avant un délimiteur personnalisé ?','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_method"><strong><?php _e('Méthode de création des extraits','WP-Excerpt-Generator'); ?></strong></label>
        </p>
        <p class="tr" id="blockOwn" <?php if(get_option("wp_excerpt_generator_method") == 'owntag') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <input value="<?php echo get_option("wp_excerpt_generator_owntag"); ?>" name="wp_excerpt_generator_owntag" id="wp_excerpt_generator_owntag" type="text" />
            <label for="wp_excerpt_generator_owntag"><strong><?php _e('Choisir le délimiteur (chaîne de caractère)','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet de couper le texte avant la chaîne choisi.<br/>Exemples : un mot, un tag inventé, etc.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr" id="blockWords" <?php if(get_option("wp_excerpt_generator_method") == 'words') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <input value="<?php echo get_option("wp_excerpt_generator_nbwords"); ?>" name="wp_excerpt_generator_nbwords" id="wp_excerpt_generator_nbwords" type="text" />
            <label for="wp_excerpt_generator_nbwords"><strong><?php _e('Nombre de mots à conserver (maximum)','WP-Excerpt-Generator'); ?></strong></label>
        </p>
        <p class="tr" id="blockLetters" <?php if(get_option("wp_excerpt_generator_method") == 'letters') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <input value="<?php echo get_option("wp_excerpt_generator_nbletters"); ?>" name="wp_excerpt_generator_nbletters" id="wp_excerpt_generator_nbletters" type="text" />
            <label for="wp_excerpt_generator_nbletters"><strong><?php _e('Nombre de lettres à conserver (maximum)','WP-Excerpt-Generator'); ?></strong></label>
        </p>

        <p class="tr" id="blockClean" <?php if(get_option("wp_excerpt_generator_method") == 'letters' || get_option("wp_excerpt_generator_method") == 'words') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <select name="wp_excerpt_generator_cleaner" id="wp_excerpt_generator_cleaner">
                <option value="1" <?php if(get_option("wp_excerpt_generator_cleaner") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php if(get_option("wp_excerpt_generator_cleaner") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_cleaner"><strong><?php _e('Terminer l\'extrait par une ponctuation propre ? (conseillé)','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet de finir les phrases proprement par une ponctuation logique.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr">
            <select name="wp_excerpt_generator_htmlOK" id="wp_excerpt_generator_htmlOK">
                <option value="total" onclick="cacher('blockHtmlBR');" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'total') { echo 'selected="selected"'; } ?>><?php _e('Totalement','WP-Excerpt-Generator'); ?></option>
                <option value="partial" onclick="montrer('blockHtmlBR');" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'partial') { echo 'selected="selected"'; } ?>><?php _e('Partiellement (gras, italique...)','WP-Excerpt-Generator'); ?></option>
                <option value="none" onclick="montrer('blockHtmlBR');" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'none') { echo 'selected="selected"'; } ?>><?php _e('Pas du tout','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_htmlOK"><strong><?php _e('Conserver le code HTML ? (déconseillé)','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('Attention ! Si vous coupez par groupes de mots ou lettres, vous risquez de casser la logique du code HTML...','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr" id="blockHtmlBR" <?php if(get_option("wp_excerpt_generator_htmlOK") == 'partial' || get_option("wp_excerpt_generator_htmlOK") == 'none') { echo 'style="display:block;"'; } else { echo 'style="display:none;"'; } ?>>
            <select name="wp_excerpt_generator_htmlBR" id="wp_excerpt_generator_htmlBR">
                <option value="1" onclick="montrer('blockBreak');" <?php if(get_option("wp_excerpt_generator_htmlBR") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" onclick="cacher('blockBreak');" <?php if(get_option("wp_excerpt_generator_htmlBR") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_htmlBR"><strong><?php _e('Conserver les sauts de lignes ?','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet d\'ajouter quelques caractères pour faire comprendre que le texte continu.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr">
            <select name="wp_excerpt_generator_breakOK" id="wp_excerpt_generator_breakOK">
                <option value="1" onclick="montrer('blockBreak');" <?php if(get_option("wp_excerpt_generator_breakOK") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" onclick="cacher('blockBreak');" <?php if(get_option("wp_excerpt_generator_breakOK") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_breakOK"><strong><?php _e('Ajouter une chaîne de fin à l\'extrait ?','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet d\'ajouter quelques caractères pour faire comprendre que le texte continu.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="tr" id="blockBreak" <?php if(get_option("wp_excerpt_generator_breakOK") == false) { echo 'style="display:none;"'; } ?>>
            <input value="<?php echo get_option("wp_excerpt_generator_break"); ?>" name="wp_excerpt_generator_break" id="wp_excerpt_generator_break" type="text" />
            <label for="wp_excerpt_generator_break"><strong><?php _e('Chaîne de caractère affichée après l\'extrait','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('Exemples : " (...)", " [...]", " ..."','WP-Excerpt-Generator'); ?></em>
        </p>
        
    	<p class="submit">
        	<input type="submit" name="wp_excerpt_generator_action" class="button-primary" value="<?php _e('Enregistrer' , 'WP-Excerpt-Generator'); ?>" />
            <input type="submit" name="wp_excerpt_generator_generate" class="button-primary" value="<?php _e('Générer les extraits' , 'WP-Excerpt-Generator'); ?>" />
        </p>
    </form>
	</div>

	<div class="col">
	<form method="post" action="">
    	<h4><?php _e('Mise à jour automatique des extraits ?','WP-Excerpt-Generator'); ?></h4>
        <p class="tr">
            <select name="wp_excerpt_generator_maj" id="wp_excerpt_generator_maj">
                <option value="1" <?php if(get_option("wp_excerpt_generator_maj") == true) { echo 'selected="selected"'; } ?>><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php if(get_option("wp_excerpt_generator_maj") == false) { echo 'selected="selected"'; } ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_maj"><strong><?php _e('Générer automatiquement les nouveaux extraits ?','WP-Excerpt-Generator'); ?></strong></label>
            <br/><em><?php _e('L\'option permet de générer automatiquement les extraits des contenus après leur publication ou leur modification.','WP-Excerpt-Generator'); ?></em>
        </p>
        <p class="submit"><input type="submit" name="wp_excerpt_generator_action_maj_auto" class="button-primary" value="<?php _e('Enregistrer' , 'WP-Excerpt-Generator'); ?>" /></p>
    </form>
    <form method="post" action="">
        <h4><?php _e('Nettoyage des extraits...','WP-Excerpt-Generator'); ?></h4>
        <p class="tr">
            <select name="wp_excerpt_generator_deleteExcerpt" id="wp_excerpt_generator_deleteExcerpt">
                <option value="1" onclick="javascript:return(confirm('<?php _e('Etes-vous sûrs de vouloir supprimer les extraits existants ?\nN.B. : aucun extrait ne sera conservé !','WP-Advanced-Search'); ?>'));"><?php _e('Oui','WP-Excerpt-Generator'); ?></option>
                <option value="0" <?php echo 'selected="selected"'; ?>><?php _e('Non','WP-Excerpt-Generator'); ?></option>
            </select>
            <label for="wp_excerpt_generator_deleteExcerpt"><strong><?php _e('Supprimer tous les extraits de la base ?','WP-Excerpt-Generator'); ?></strong></label>
        </p>   
    	<p class="submit"><input type="submit" name="wp_excerpt_generator_delete" onclick="javascript:return(confirm('<?php _e('Dernière chance avant la suppression complète des extraits...\nVous êtes toujours sûrs de vous ?','WP-Advanced-Search'); ?>'));" class="button-primary" value="<?php _e('Supprimer' , 'WP-Excerpt-Generator'); ?>" /></p>
    </form>
    <form method="post" action="">
		<p class="trNew">
			<?php
                $existingTitleExcerpt = $wpdb->get_results("SELECT post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_title !='' AND post_excerpt != '' ORDER BY post_date DESC"); // Lister les extraits existants
				$existingIdExcerpt = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_title !='' AND post_excerpt != '' ORDER BY post_date DESC"); // Lister les extraits existants
                foreach($existingTitleExcerpt as $excerpt) {
					foreach($excerpt as $TitleExcerpt) {
                        $tabTitleExcerpt[] = $TitleExcerpt;	
                    }
                }
				foreach($existingIdExcerpt as $excerptId) {
					foreach($excerptId as $IdExcerpt) {
                        $tabIdExcerpt[] = $IdExcerpt;	
                    }
                }
				if(!empty($tabTitleExcerpt) && !empty($tabIdExcerpt)) {
	                $tabExcerpt = array_combine($tabIdExcerpt, $tabTitleExcerpt);
				}
            ?>
            <label for="wp_excerpt_generator_deleteSelectedExcerpt"><strong><?php _e('Supprimer les extraits sélectionnés dans la base de données ?','WP-Excerpt-Generator'); ?></strong></label>
            <select name="wp_excerpt_generator_deleteSelectedExcerpt[]" id="wp_excerpt_generator_deleteSelectedExcerpt" multiple="multiple" size="12" class="selectedExcerpt">
                <option value="aucun"><?php _e('Aucun','WP-Advanced-Search'); ?></option>
                <?php foreach($tabExcerpt as $ExcerptKey => $ExcerptTitle) { ?>
                	<option value="<?php echo $ExcerptKey; ?>"><?php _e($ExcerptTitle,'WP-Advanced-Search'); ?></option>
                <?php } ?>
            </select>
            <br/><em><?php _e('Seuls les contenus ayant des extraits remplis sont affichés dans la liste !','WP-Excerpt-Generator'); ?></em>
        </p>  
    	<p class="submit"><input type="submit" name="wp_excerpt_generator_deleteSelectedExcerpt_choice" onclick="javascript:return(confirm('<?php _e('Dernière chance avant la suppression des extraits sélectionnés...\n&Ecirc;tes-vous toujours sûrs de vous ?','WP-Advanced-Search'); ?>'));" class="button-primary" value="<?php _e('Supprimer ces extraits' , 'WP-Excerpt-Generator'); ?>" /></p>
    </form>
    </div>
    <div class="clear"></div>
</div>
<?php
echo '</div>'; // Fin de la page d'admin
} // Fin de la fonction Callback
?>