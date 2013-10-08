<?php
// Fonction de comptage des mots
function Limit_Words($Text, $NbWords, $htmlOK = false, $Cleaner = true, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == false) {
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
	return $NewText;
}

// Fonction de comptage des lettres
function Limit_Letters($Text, $NbLetters, $htmlOK = false, $Cleaner = true, $CharsMore = array(true, ' [...]')) { 
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == false) {
		$Text = strip_tags($Text);
	}

	if(strlen($Text) >= $NbLetters+1){
		$NewText = substr($Text, 0, $NbLetters);
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

	return $NewText; 
}

// Fonction de comptage des lettres
function Limit_Tags($Text, $htmlOK = false, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == false) {
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
	return $NewText; 
}

// Fonction de comptage des lettres
function Limit_More($Text, $htmlOK = false, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == true) {
		$Text = strip_tags($Text);
	}

	// On trouve des solutions pour couper avant la balise <!--more-->
	if(stripos($Text,"&lt;!--more--&gt;")) {
		$NewText = substr($Text,0,stripos($Text,"&lt;!--more--&gt;"));
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	}
	return $NewText; 
}
?>