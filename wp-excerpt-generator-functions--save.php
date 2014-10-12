<?php
// Fonction de fermeture automatique des balises non fermées...
// Author : Milian <mail@mili.de>
// Fonction très remaniée à cause des bugs initiaux liés aux <br/>, etc.
function closetags($html) {
	// Ajoute toutes les balises ouvertes dans un tableau
	preg_match_all('#<([a-zA-Z]+)(?: .*)?>#iU', $html, $result);
	$openedtags = $result[1];

	// Ajoute toutes les balises fermées dans un tableau
	preg_match_all('#</([a-z]+)>#iU', $html, $result);
	$closedtags = $result[1];

	// Nettoie les <br> et <hr> inutiles...
	$excludedtags = array('br', 'hr');
	foreach($openedtags as $k => $val) {
		if(in_array($val,$excludedtags)) {
			$val = '';
		}
		if(empty($val)) {
			unset($openedtags[$k]);
		}
	}

	// On compte le nombre de balises ouvertes et fermées
	$len_opened = count($openedtags);
	$len_closed = count($closedtags);
	
	// Si toutes les balises sont bien fermées
	if($len_closed == $len_opened) {
		return $html;
	} else {
		$openedtags = array_reverse($openedtags);
	
		// On ferme les balises proprement
		for($i=0; $i < $len_opened; $i++) {
			if(!in_array($openedtags[$i], $closedtags)){
				$html .= '</'.$openedtags[$i].'>';
			} else {
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}
		return $html;
	}
}

// Fonction de comptage des mots
function Limit_Words($Text, $NbWords, $htmlOK = false, $htmlBR = true, $Cleaner = true, $CharsMore = array(true, ' [...]')) {
	// On découpe la chaîne pour repérer le dernier mot --> Plusieurs méthodes variables !
	preg_match_all('#[[:blank:]]?([^[:blank:]]+)[[:blank:]]#iU', strip_tags($Text), $ChaineTab);
	print_r($ChaineTab[0]);
		// Alternatives (plus ou moins bonnes...)
		//$ChaineTab[0] = explode(' ', strip_tags($Text));
		//$ChaineTab[0] = str_word_count(strip_tags($Text), 1);
		//$ChaineTab[0] = mb_split("[[:blank:]]+", strip_tags(trim($Text)));
	$TempText = "";
	
	// On reconstitue la chaîne exacte pour récupérer la position du dernier mot
	foreach($ChaineTab[0] as $key => $chaine) {
//echo $key.' : '.$chaine;
		// On reconstitue la chaîne
		if($key < $NbWords) {
			$TempText .= $chaine." ";
		}
	}
echo $TempText;

	// Extraction du dernier mot (pour toujours couper la phrase au bon endroit)
	$lastWord = $ChaineTab[0][$NbWords-1];

	// On récupère la position exacte du dernier mot (au cas où il serait répété !)
	$lastWordPosition = strripos($TempText, $lastWord);

	// Ajoute une chaine reconnaissable après le dernier mot (jusqu'où il faut découper)
	//$Text = preg_replace('#(.*){'.$lastWordPosition.',}('.$lastWord.')(.*)#iU', "$1[*****]$3", $Text);
	//$Text = substr_replace($Text,"[*****]",0);
	
	echo $lastWord." - ";
	echo $lastWordPosition."<br/>";		
 	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;
	} else if($htmlOK == 'partial') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
		}
		$Text = strip_tags($Text,"<br><br/><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
			$Text = strip_tags($Text,"<br><br/>");
		} else {
			$Text = strip_tags($Text);
		}
	}

	// On trouve des solutions pour couper avant le dernier mot
	$lenghtLastWord = strlen($lastWord);
	if(preg_match('#(.*)'.$lastWord.'#iU', $Text)) {
		if($lastWordPosition != stripos($Text,$lastWord)) {
			$NewText = substr($Text,0,stripos($Text,$lastWord,$lastWordPosition)+$lenghtLastWord);
		} else {
			$NewText = substr($Text,0,stripos($Text,$lastWord)+$lenghtLastWord);
		}
	}
	
	// On découpe proprement la fin si l'option est activée sur "true"
	if($Cleaner == true) {
		if(strripos($NewText,". ")) {
			$NewText = substr($NewText,0,strripos($NewText,". ")+1);
		} else if(strripos($NewText,"! ")) {
			$NewText = substr($NewText,0,strripos($NewText,"! ")+1);
		} else if(strripos($NewText,"? ")) {
			$NewText = substr($NewText,0,strripos($NewText,"? ")+1);
		} else if(strripos($NewText,"... ")) {
			$NewText = substr($NewText,0,strripos($NewText,"... ")+1);
		} else if(strripos($NewText,"; ")) {
			$NewText = substr($NewText,0,strripos($NewText,"; ")+1);
		} else if(strripos($NewText,"¿ ")) {
			$NewText = substr($NewText,0,strripos($NewText,"¿ ")+1);
		} else if(strripos($NewText,"! ")) {
			$NewText = substr($NewText,0,strripos($NewText,"! ")+1);
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

// Fonction de comptage des lettres
function Limit_Letters($Text, $NbLetters, $htmlOK = false, $htmlBR = true, $Cleaner = true, $CharsMore = array(true, ' [...]')) {
	// Nombre d'espaces (approximatif...) sur l'extrait découpé
	$NbSpace = count(explode(" ", substr(strip_tags($Text), 0, $NbLetters)));

	// Nombre de caractères occupés par les balises HTML (à décompter !)
	preg_match_all("#<([\/]?[a-zA-Z]+(.*)?[\/]?)>#iU",substr($Text, 0, $NbLetters+$NbSpace),$Result);
	$listNbTag = implode("", $Result[1]);
	
	// (1 * count($Result[1])) correspond au nombre de "<" et ">" manquants pour chaque balise détectée
	$LenghtTags = strlen($listNbTag)+(2 * count($Result[1]));
	
	// On coupe les mots après tant de lettres (hors balises HTML !)
	if(strlen(strip_tags($Text)) >= ($NbLetters+1)) {
		$Text = substr($Text, 0, $NbLetters+$LenghtTags);
	}

	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$NewText = $Text; // Le texte reste entier...
	}
	if($htmlOK == 'partial') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
		}
		$NewText = strip_tags($Text,"<br><br/><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	}
	if($htmlOK == 'none') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
			$NewText = strip_tags($Text,"<br><br/>");
		} else {
			$NewText = strip_tags($Text);
		}
	}

	if($Cleaner == true) {
		if(strripos($NewText,". ")) {
			$NewText = substr($NewText,0,strripos($NewText,". ")+1);
		} else if(strripos($NewText,"! ")) {
			$NewText = substr($NewText,0,strripos($NewText,"! ")+1);
		} else if(strripos($NewText,"? ")) {
			$NewText = substr($NewText,0,strripos($NewText,"? ")+1);
		} else if(strripos($NewText,"... ")) {
			$NewText = substr($NewText,0,strripos($NewText,"... ")+1);
		} else if(strripos($NewText,"; ")) {
			$NewText = substr($NewText,0,strripos($NewText,"; ")+1);
		} else if(strripos($NewText,"¿ ")) {
			$NewText = substr($NewText,0,strripos($NewText,"¿ ")+1);
		} else if(strripos($NewText,"! ")) {
			$NewText = substr($NewText,0,strripos($NewText,"! ")+1);
		}
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	} else {
		$NewText = $Text;
		
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}	
	}
	$NewText = closetags($NewText);
	return $NewText;
}

// Fonction de récupération du premier paragraphe
function Limit_Paragraph($Text, $limitParagraph = 1, $htmlOK = false, $htmlBR = true, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = nl2br($Text);		
	} else if($htmlOK == 'partial') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
		}
		$Text = strip_tags($Text,"<br><br/><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
			$Text = strip_tags($Text,"<br><br/>");
		} else {
			$Text = strip_tags($Text);
		}
	}

	// On trouve des solutions pour couper après le premier paragraphe
	if(preg_match_all('#(.*)[^[:space:]]+#i', $Text, $ChaineTab)) {
	//if(preg_match_all('#(.*)(</p>|\n)+#i', $Text, $ChaineTab)) {
		$NewText = "";
		foreach($ChaineTab[0] as $key => $chaine) {
			if($key < $limitParagraph) {
				$NewText.= $chaine;
			}
		}
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	}
	$NewText = closetags($NewText);
	return $NewText; 
}

// Fonction de césure avant la balise <!--more-->
function Limit_More($Text, $htmlOK = 'none', $htmlBR = true, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;
	} else if($htmlOK == 'partial') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
		}
		$saveTags = array('<!--more-->');
		$replaceTags = array('!!!MORE!!!');
		$Text = str_ireplace($saveTags, $replaceTags, $Text);
		$Text = strip_tags($Text,"<br><br/><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
		$Text = str_ireplace($replaceTags, $saveTags, $Text);
	} else if($htmlOK == 'none') {
		// On doit remplacer les commentaires par autre chose pour que la balise ne soit pas supprimée...
		$saveTags = array('<!--more-->');
		$replaceTags = array('!!!MORE!!!');
		$Text = str_ireplace($saveTags, $replaceTags, $Text);
		if($htmlBR == true) {
			$Text = nl2br($Text);
			$Text = strip_tags($Text,"<br><br/>");
		} else {
			$Text = strip_tags($Text);
		}
		// On replace la balise MORE classique une fois le nettoyage HTML effectué
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

// Fonction de césure après une chaine libre
function Limit_OwnTag($Text, $owntag = '', $htmlOK = 'none', $htmlBR = true, $CharsMore = array(true, ' [...]')) {
	// On retire les balises HTML gênantes (optionnel)...
	if($htmlOK == 'total') {
		$Text = $Text;
	} else if($htmlOK == 'partial') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
		}
		$Text = strip_tags($Text,"<br><br/><a><em><strong><cite><q><span><sup><sub><small><big><u><i><b><s><strike><ins><del>");
	} else if($htmlOK == 'none') {
		if($htmlBR == true) {
			$Text = nl2br($Text);
			$Text = strip_tags($Text,"<br><br/>");
		} else {
			$Text = strip_tags($Text);
		}
	}

	// On trouve des solutions pour couper avant le mot choisi
	$lenghtOwnTag = strlen($owntag);
	if(preg_match('#(.*)'.$owntag.'#i', $Text)) {
		$NewText = substr($Text,0,stripos($Text,$owntag)+$lenghtOwnTag);
		// Ajoute des caractères de fin pour faire plus propre...
		if($CharsMore[0] == true) {
			$NewText .= $CharsMore[1]."\n";
		}
	}
	$NewText = closetags($NewText);
	return $NewText; 
}
?>