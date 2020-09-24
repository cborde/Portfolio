<?php

/*********************************************************
 *        Bibliothèque de fonctions génériques           *
 *********************************************************/

 // Paramètres pour accéder à la base de données
define('BS_SERVER', 'localhost');
define('BS_DB', 'bookshop_db');
define('BS_USER', 'borde');
define('BS_PASS', 'bordeMDP');

//---------------------------------------------------------------
// Définition des types de zones de saisies
//---------------------------------------------------------------
define('bg_Z_TEXT', 'text');
define('bg_Z_PASSWORD', 'password');
define('bg_Z_SUBMIT', 'submit');
define('bg_Z_HIDDEN', 'hidden');


/**
 *	Fonction affichant le début du code HTML d'une page.
 *
 *  @param 	String	$titre	Titre de la page
 *	@param 	String	$css	Chemin relatif vers la feuille de style CSS.
 */
	function bg_html_debut($title) {
	echo '<!doctype html>',
			'<html lang="fr">',
				'<head>',
					'<meta name="viewport" content="width=device-width" />',
					'<meta charset="UTF-8">',
					'<title>',$title,'</title>';	
					$nbArg = func_num_args();
  					for ($i = 1; $i < $nbArg; $i++) {
						echo '<link type="text/css" media="screen" rel="stylesheet" href="', func_get_arg($i),'">';
					}
				echo '</head>',
				'<body>';
	} 


/**
 *	Fonction affichant la fin du code HTML d'une page.
 */
function bg_html_fin() {
	echo '</body></html>';
}



//____________________________________________________________________________
/** 
 *	Ouverture de la connexion à la base de données
 *
 *	@return objet 	connecteur à la base de données
 */
function bg_bd_connect() {
    $conn = mysqli_connect(BS_SERVER, BS_USER, BS_PASS, BS_DB);
    if ($conn !== FALSE) {
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8') 
        or bg_bd_erreurExit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
        return $conn;     // ===> Sortie connexion OK
    }
    // Erreur de connexion
    // Collecte des informations facilitant le debugage
    $msg = '<h4>Erreur de connexion base MySQL</h4>'
            .'<div style="margin: 20px auto; width: 350px;">'
            .'BD_SERVER : '. BS_SERVER
            .'<br>BS_USER : '. BS_USER
            .'<br>BS_PASS : '. BS_PASS
            .'<br>BS_DB : '. BS_DB
            .'<p>Erreur MySQL numéro : '.mysqli_connect_errno()
            .'<br>'.htmlentities(mysqli_connect_error(), ENT_QUOTES, 'ISO-8859-1')  
            //appel de htmlentities() pour que les éventuels accents s'affiche correctement
            .'</div>';
    bg_bd_erreurExit($msg);
}

//____________________________________________________________________________
/**
 * Arrêt du script si erreur base de données 
 *
 * Affichage d'un message d'erreur, puis arrêt du script
 * Fonction appelée quand une erreur 'base de données' se produit :
 * 		- lors de la phase de connexion au serveur MySQL
 *		- ou indirectement lorsque l'envoi d'une requête échoue
 *
 * @param string	$msg	Message d'erreur à afficher
 */
function bg_bd_erreurExit($msg) {
    ob_end_clean();	// Supression de tout ce qui a pu être déja généré
    ob_start('ob_gzhandler'); // nécessaire sur saturnin quand compression avec ob_gzhandler
    echo    '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>',
            'Erreur base de données</title>',
            '<style>table{border-collapse: collapse;}td{border: 1px solid black;padding: 4px 10px;}</style>',
            '</head><body>',
            $msg,
            '</body></html>';
    exit(1);
}


//____________________________________________________________________________
/**
 * Gestion d'une erreur de requête à la base de données.
 *
 * A appeler impérativement quand un appel de mysqli_query() échoue 
 * Appelle la fonction bg_bd_erreurExit() qui affiche un message d'erreur puis termine le script
 *
 * @param objet		$bd		Connecteur sur la bd ouverte
 * @param string	$sql	requête SQL provoquant l'erreur
 */
function bg_bd_erreur($bd, $sql) {
    $errNum = mysqli_errno($bd);
    $errTxt = mysqli_error($bd);

    // Collecte des informations facilitant le debugage
    $msg =  '<h4>Erreur de requête</h4>'
            ."<pre><b>Erreur mysql :</b> $errNum"
            ."<br> $errTxt"
            ."<br><br><b>Requête :</b><br> $sql"
            .'<br><br><b>Pile des appels de fonction</b></pre>';

    // Récupération de la pile des appels de fonction
    $msg .= '<table>'
            .'<tr><td>Fonction</td><td>Appelée ligne</td>'
            .'<td>Fichier</td></tr>';

    $appels = debug_backtrace();
    for ($i = 0, $iMax = count($appels); $i < $iMax; $i++) {
        $msg .= '<tr style="text-align: center;"><td>'
                .$appels[$i]['function'].'</td><td>'
                .$appels[$i]['line'].'</td><td>'
                .$appels[$i]['file'].'</td></tr>';
    }

    $msg .= '</table>';

    bg_bd_erreurExit($msg);	// => ARRET DU SCRIPT
}


/** 
 *	Protection des sorties (code HTML généré à destination du client).
 *
 *  Fonction à appeler pour toutes les chaines provenant de :
 *		- de saisies de l'utilisateur (formulaires)
 *		- de la bdD
 *	Permet de se protéger contre les attaques XSS (Cross site scripting)
 * 	Convertit tous les caractères éligibles en entités HTML, notamment :
 *		- les caractères ayant une signification spéciales en HTML (<, >, ...)
 *		- les caractères accentués
 *
 *	@param	string 	$text	la chaine à protéger	
 * 	@return string 	la chaîne protégée
 */
function bg_protect_sortie($str) {
	$str = trim($str);
	return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

/*
 * Protection des chaînes avant insertion dans une requête SQL
 *
 * Avant insertion dans une requête SQL, toutes les chaines contenant certains caractères spéciaux (", ', ...) 
 * doivent être protégées. En particulier, toutes les chaînes provenant de saisies de l'utilisateur doivent l'être. 
 * Echappe les caractères spéciaux d'une chaîne (en particulier les guillemets) 
 * Permet de se protéger contre les attaques de type injections SQL
 *
 * @param 	objet 		$bd 	La connexion à la base de données
 * @param 	string 		$str 	La chaîne à protéger
 * @return 	string 				La chaîne protégée
 */
function bg_bd_protect($bd, $str) {
	$str = trim($str);
	return mysqli_real_escape_string($bd, $str);
}


/**
 * Redirige l'utilisateur sur une page
 *
 * @param string	$page		Page où l'utilisateur est redirigé
 */
function bg_redirige($page) {
	header("Location: $page");
	exit();
}


/**
 * Arrête une session et effectue une redirection vers la page index.php
 *
 * Elle utilise :
 *   -   la fonction session_destroy() qui détruit la session existante
 *   -   la fonction session_unset() qui efface toutes les variables de session
 * Puis, le cookie de session est supprimé
 * 
 */
function bg_exit_session() {
	session_destroy();
	session_unset();
	$cookieParams = session_get_cookie_params();
	setcookie(session_name(), 
			'', 
			time() - 86400,
         	$cookieParams['path'], 
         	$cookieParams['domain'],
         	$cookieParams['secure'],
         	$cookieParams['httponly']
    	);
	
	header('Location: ../index.php');
	exit();
}

/**
 * Teste si une valeur est une valeur entière
 *
 * @param mixed     $x  valeur à tester
 * @return boolean  TRUE si entier, FALSE sinon
*/
function est_entier($x) {
    return is_numeric($x) && ($x == (int) $x);
}

//_______________________________________________________________
//
//		FONCTIONS UTILISEES DANS LES FORMULAIRES
//_______________________________________________________________

/**
* Génére le code d'une ligne de formulaire :
*
* @param string		$gauche		Contenu de la colonne de gauche
* @param string 	$droite		Contenu de la colonne de droite
*
* @return string 	Code HTML représentant une ligne de tableau
*/
function bg_form_ligne($gauche, $droite) {
    $gauche =  bg_protect_sortie($gauche);
    return "<tr><td>{$gauche}</td><td>{$droite}</td></tr>";
}

//_______________________________________________________________
/**
* Génére le code d'une zone input de formulaire (type input) :
*
* @param String		$type	Type de l'input ('text', 'hidden', ...).
* @param string		$name	Nom de la zone (attribut name).
* @param String		$value	Valeur par défaut (attribut value).
* @param integer	$size	Taille du champ (attribut size).
*
* @return string Code HTML de la zone de formulaire
*/
function bg_form_input($type, $name, $value, $size=0) {
   $value =  bg_protect_sortie($value);
   $size = ($size == 0) ? '' : "size='{$size}'";
   return "<input type='{$type}' name='{$name}' {$size} value='{$value}'>";
}

/**
 * Renvoie le nom d'un mois.
 *
 * @param integer	$numero		Numéro du mois (entre 1 et 12)
 *
 * @return string 	Nom du mois correspondant
 */
function bg_get_mois($numero) {
	$numero = (int) $numero;
	($numero < 1 || $numero > 12) && $numero = 0;

	$mois = array('Erreur', 'Janvier', 'F&eacute;vrier', 'Mars',
				'Avril', 'Mai', 'Juin', 'Juillet', 'Ao&ucirc;t',
				'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre');

	return $mois[$numero];
}

/**
* Génére le code pour un ensemble de trois zones de sélection représentant une date : jours, mois et années
*
* @param string		$nom	    Préfixe pour les noms des zones
* @param integer    $nb_annees  Nombre d'années à afficher
* @param integer	$jour 	    Le jour sélectionné par défaut
* @param integer	$mois 	    Le mois sélectionné par défaut
* @param integer	$annee	    L'année sélectionnée par défaut
*
* @return string 	Le code HTML des 3 zones de liste
*/
function bg_form_date($name, $nb_annees, $jsel=0, $msel=0, $asel=0){
	$jsel=(int)$jsel;
	$msel=(int)$msel;
	$asel=(int)$asel;
	$d = date('Y-m-d');
	list($aa, $mm, $jj) = explode('-', $d);
	($jsel==0) && $jsel = $jj;
	($msel==0) && $msel = $mm;
	($asel==0) && $asel = $aa;
	
	$res = "<select name='{$name}_j'>";
	for ($i=1; $i <= 31 ; $i++){
        $selected = ($i == $jsel) ? 'selected' : '';
		$res .= "<option value='$i' $selected>$i</option>";
	}
	$res .= "</select> <select name='{$name}_m'>"; 
	for ($i=1; $i <= 12 ; $i++){
		$selected = ($i == $msel)? 'selected' : '';
		$res .= "<option value='$i' $selected>".bg_get_mois($i).'</option>';
	}
	$res .= "</select> <select name='{$name}_a'>";
	for ($i=$aa; $i > $aa - $nb_annees ; $i--){
		$selected = ($i == $asel) ? 'selected' : '';
		$res .= "<option value='$i' $selected>$i</option>";
	}
	$res .= '</select>';
	return $res;		
}

/**
* Génére le code pour un ensemble de trois zones de sélection représentant une date : jours, mois et années désavtivé
*
* @param string		$nom	    Préfixe pour les noms des zones
* @param integer    $nb_annees  Nombre d'années à afficher
* @param integer	$jour 	    Le jour sélectionné par défaut
* @param integer	$mois 	    Le mois sélectionné par défaut
* @param integer	$annee	    L'année sélectionnée par défaut
*
* @return string 	Le code HTML des 3 zones de liste désactivée
*/
function bg_form_date_disabled($name, $nb_annees, $jsel=0, $msel=0, $asel=0){
	$jsel=(int)$jsel;
	$msel=(int)$msel;
	$asel=(int)$asel;
	$d = date('Y-m-d');
	list($aa, $mm, $jj) = explode('-', $d);
	($jsel==0) && $jsel = $jj;
	($msel==0) && $msel = $mm;
	($asel==0) && $asel = $aa;
	
	$res = "<select name='{$name}_j' disabled='disabled'>";
	for ($i=1; $i <= 31 ; $i++){
        $selected = ($i == $jsel) ? 'selected' : '';
		$res .= "<option value='$i' $selected>$i</option>";
	}
	$res .= "</select> <select name='{$name}_m' disabled='disabled'>"; 
	for ($i=1; $i <= 12 ; $i++){
		$selected = ($i == $msel)? 'selected' : '';
		$res .= "<option value='$i' $selected>".bg_get_mois($i).'</option>';
	}
	$res .= "</select> <select name='{$name}_a' disabled='disabled'>";
	for ($i=$aa; $i > $aa - $nb_annees ; $i--){
		$selected = ($i == $asel) ? 'selected' : '';
		$res .= "<option value='$i' $selected>$i</option>";
	}
	$res .= '</select>';
	return $res;		
}


/**
* Extrait et renvoie le nom du fichier cible contenu dans une URL
*
* Exemple : si la fonction reçoit l'URL
*    http://localhost/bookshop/php/page1.php?nom=valeur&name=value
* elle renvoie 'page1.php'
*  
* @param string		$url        URL à traiter

*
* @return string 	Le nom du fichier cible
*/
function url_get_nom_fichier($url){
    $nom = basename($url);
    $pos = mb_strpos($nom, '?', 0, 'UTF-8');
    if ($pos !== false){
        $nom = mb_substr($nom, 0, $pos, 'UTF-8');
    }
    return $nom;
}

/**
 * Teste si une valeur est une valeur entière
 *
 * @param mixed     $x  valeur à tester
 * @return boolean  TRUE si entier, FALSE sinon
*/
function estEntier($x) {
    return is_numeric($x) && ($x == (int) $x);
}

/**
 * Teste si un nombre est compris entre 2 autres
 *
 * @param integer   $x  nombre à tester
 * @return boolean  TRUE si ok, FALSE sinon
*/
function estEntre($x, $min, $max) {
    return ($x >= $min) && ($x <= $max);
}
?>
