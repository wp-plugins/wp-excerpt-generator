<?php
// Fonction de fermeture automatique des balises non fermées...
// Author : Milian <mail@mili.de>
function closetags($html) {
#put all opened tags into an array
	preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	$openedtags = $result[1]; #put all closed tags into an array
	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closedtags = $result[1];

	$len_opened = count($openedtags);
	
	# all tags are closed
	if(count($closedtags) == $len_opened) {
		return $html;
	}

	$openedtags = array_reverse($openedtags);
	
	# close tags
	for($i=0; $i < $len_opened; $i++) {
		if(!in_array($openedtags[$i], $closedtags)){
			$html .= '</'.$openedtags[$i].'>';
		} else {
			unset($closedtags[array_search($openedtags[$i], $closedtags)]);
		}
	}
	return $html;
}

// Fonction de comptage des mots
function Limit_Words($Text, $NbWords, $htmlOK = false, $Cleaner = true, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;		
	} else if($htmlOK == 'partial') {
		$Text = strip_tags($Text,"<br><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		$Text = strip_tags($Text);
	}

	// On coupe à chaque espace pour repérer les mots différents
	//$ChaineTab = explode(" ", $Text);
	
	if(preg_match_all('#([^[:space:]])+#i', $Text, $ChaineTab)) {
		foreach($ChaineTab[0] as $key => $chaine) {
			// Nettoyage des chaînes de caractères
			$specialchars = array('€', '$', '#', '+', '*', "'", '"', '²', '&', '~', '"', '{', '(', '[', '|', '`', '^', ')', '}', '=', '}', '^', '$', '£', '¤', '%', '*', 'µ', ',', '?', ';', ':', '/', '!', '§', '>', '<', '//');
			// On supprime les chaines vides du tableau (donc les caractères exclus)
			if(in_array($chaine, $specialchars)) {
				$count += count(in_array($chaine, $specialchars),1);
			}
			
			// On reconstitue la chaîne
			if($key < $NbWords + $count) {
				$NewText .=	$chaine." ";
			}
		}
	}

	if($Cleaner == true) {
		if(strripos($NewText,".")) {
			$NewText = substr($NewText,0,strripos($NewText,".")+1);
		} else if(strripos($NewText,"!")) {
			$NewText = substr($NewText,0,strripos($NewText,"!")+1);
		} else if(strripos($NewText,"?")) {
			$NewText = substr($NewText,0,strripos($NewText,"?")+1);
		} else if(strripos($NewText,"...")) {
			$NewText = substr($NewText,0,strripos($NewText,"...")+1);
		} else if(strripos($NewText,";")) {
			$NewText = substr($NewText,0,strripos($NewText,";")+1);
		} else if(strripos($NewText,"¿")) {
			$NewText = substr($NewText,0,strripos($NewText,"¿")+1);
		} else if(strripos($NewText,"!")) {
			$NewText = substr($NewText,0,strripos($NewText,"!")+1);
		}
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
		/*
		// On termine la phrase proprement (mot complet ou ponctuation)
		if(preg_match_all('#(.*)[.:;!?¡¿»")\]}]\s#i', $NewText, $args)) {
			foreach($args[0] as $arg) {
				$NewText = $arg;
			}
		} else if(preg_match_all('#(.*)[\s]#i', $NewText, $args)) {
			foreach($args[0] as $arg) {
				$NewText = $arg;
			}
		}
		*/
	} else {
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}	
	}
	$NewText = closetags($NewText);
	return $NewText;
}

// Fonction de comptage des lettres
function Limit_Letters($Text, $NbLetters, $htmlOK = false, $Cleaner = true, $CharsMore = array(true, ' [...]')) {
	// On coupe les mots après tant de lettres (hors balises HTML !)
	if(strlen(strip_tags($Text)) >= $NbLetters+1) {
		$Text = substr($Text, 0, $NbLetters);
	}
	
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$NewText = $Text;		
	} else if($htmlOK == 'partial') {
		$NewText = strip_tags($Text,"<br><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		$NewText = strip_tags($Text);
	}
	
	if($Cleaner == true) {
		if(strripos($NewText,".")) {
			$NewText = substr($NewText,0,strripos($NewText,".")+1);
		} else if(strripos($NewText,"!")) {
			$NewText = substr($NewText,0,strripos($NewText,"!")+1);
		} else if(strripos($NewText,"?")) {
			$NewText = substr($NewText,0,strripos($NewText,"?")+1);
		} else if(strripos($NewText,"...")) {
			$NewText = substr($NewText,0,strripos($NewText,"...")+1);
		} else if(strripos($NewText,";")) {
			$NewText = substr($NewText,0,strripos($NewText,";")+1);
		} else if(strripos($NewText,"¿")) {
			$NewText = substr($NewText,0,strripos($NewText,"¿")+1);
		} else if(strripos($NewText,"!")) {
			$NewText = substr($NewText,0,strripos($NewText,"!")+1);
		}
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	} else {
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}	
	}
	$NewText = closetags($NewText);
	return $NewText; 
}

// Fonction de récupération du premier paragraphe
function Limit_Paragraph($Text, $htmlOK = false, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;		
	} else if($htmlOK == 'partial') {
		$Text = strip_tags($Text,"<br><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		$Text = strip_tags($Text);
	}

	// On trouve des solutions pour couper après le premier paragraphe
	if(preg_match_all('#(.*)[^[:space:]]+#i', $Text, $ChaineTab)) {
		foreach($ChaineTab[0] as $key => $chaine) {
			if($key == 0) {
				$NewText = $chaine;
				// Ajoute des caractères de fin pour faire plus propre...
				if($CharsMore[0] == true) {
					$NewText .= $CharsMore[1]."\n";
				}
			}				
		}
	}
	/*
	if(stripos($Text,"\n")) {
		$NewText = substr($Text,0,stripos($Text,"\n"));
	} else if(stripos($Text,"</p>")) {
		$NewText = substr($Text,0,stripos($Text,"</p>"));
	} else if(stripos($Text,"</span>")) {
		$NewText = substr($Text,0,stripos($Text,"</span>"));
	} else if(stripos($Text,"</div>")) {
		$NewText = substr($Text,0,stripos($Text,"</div>"));
	}
	if(preg_match_all('#(.*)((\n|\r)*|(<\/(span|p|div)>)|(<br>)|(<br\>))+#i', $Text, $args)) {
		foreach($args[0] as $key => $arg) {
			if($key == 0) {
				$NewText = $arg;
			}
		}
	}
	*/
	$NewText = closetags($NewText);
	return $NewText; 
}

// Fonction de comptage des lettres
function Limit_More($Text, $htmlOK = 'none', $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;
	} else if($htmlOK == 'partial') {
		$saveTags = array('<!--more-->');
		$replaceTags = array('!!!MORE!!!');
		$Text = str_ireplace($saveTags, $replaceTags, $Text);
		$Text = strip_tags($Text,"<br><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
		$Text = str_ireplace($replaceTags, $saveTags, $Text);
	} else if($htmlOK == 'none') {
		$saveTags = array('<!--more-->');
		$replaceTags = array('!!!MORE!!!');
		$Text = str_ireplace($saveTags, $replaceTags, $Text);
		$Text = strip_tags($Text);
		$Text = str_ireplace($replaceTags, $saveTags, $Text);
	}

	// On trouve des solutions pour couper avant la balise <!--more-->
	if(stripos($Text,"<!--more-->")) {
		$NewText = substr($Text,0,stripos($Text,"<!--more-->"));
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	}
	$NewText = closetags($NewText);
	return $NewText; 
}
?>