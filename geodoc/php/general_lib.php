<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                              Bibliothèque de fonctions génériques                                                                    //
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Paramètres d'accès à la base de données
define('SERVER', 'localhost');
define('DB', 'geodoc');
define('USER', 'geodoc');
define('PASS', 'geodoc');

//Patterns
$numsecu = '/^'.
'([1-378])'.
'([0-9]{2})'.
'(0[0-9]|[235-9][0-9]|[14][0-2])'.
'((0[1-9]|[1-8][0-9]|9[0-69]|2[abAB])(00[1-9]|0[1-9][0-9]|[1-8][0-9]{2}|9[0-8][0-9]|990)|(9[78][0-9])(0[1-9]|[1-8][0-9]|90))'.
'(00[1-9]|0[1-9][0-9]|[1-9][0-9]{2})'.
'(0[1-9]|[1-8][0-9]|9[0-7])'.
'$/';
define('NUMSECU_REGEX', $numsecu);

//Divers
define('S_DAY', 86400);


/////////////////  Affichage HTML   /////////////////////////////:

/**
 *	Affiche le début du code HTML d'une page.
 *
 *  @param 	String	$titre	Titre de la page
 *	 @param 	String	$css		Chemin relatif vers la feuille de style CSS
 * @param		string		$id			identifiant de la page
 */
function begin_html($titre, $css, $prefix = '../', $id = '') {
	$css = ($css == '') ? '' : "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\">";
	echo
		'<!doctype html>',
		'<html lang="fr">',
			'<head>',
				'<title>', $titre, '</title>',
				'<meta charset="UTF-8">',
                '<link rel="icon" type="image/png" href="', $prefix, 'images/geodoc_icon.png" sizes="128x128">',
			   	$css,
				'<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>',
				'<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>',
			'</head>',
			'<body id="', $id, '">';
}

/**
 *	Affiche la fin du code HTML d'une page.
 */
function end_html() {
	echo '</body></html>';
}

/**
 *	Fonction affichant le pied de page de l'application BookShop.
 */
function display_footer($prefix = './') {
	echo
		'</section>', 
		'<footer>',
			'Géo\'doc &copy; ', date('Y'), ' - ',
			'<a href="', $prefix, 'apropos.php">A propos</a>',
		'</footer>';
}


/////////////   Fonctions pour la BD    /////////////////////

/**
 *	Ouverture de la connexion à la base de données
 *
 *	@return objet 	connecteur à la base de données
 */
function bd_connect() {
    $conn = mysqli_connect(SERVER, USER, PASS, DB);
    if ($conn !== FALSE) {
        mysqli_set_charset($conn, 'utf8')
        or bd_errorExit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
        return $conn;
    }
    //erreur de connexion, collecte des informations facilitant le debugage
	//TO DO : virer ça à la fin
    $msg = '<h4>Erreur de connexion base MySQL</h4>'
            .'<div style="margin: 20px auto; width: 350px;">'
            .'SERVER : '. SERVER
            .'<br>USER : '. USER
            .'<br>PASS : '. PASS
            .'<br>DB : '. DB
            .'<p>Erreur MySQL numéro : '.mysqli_connect_errno()
            .'<br>'.htmlentities(mysqli_connect_error(), ENT_QUOTES, 'ISO-8859-1')
            //appel de htmlentities() pour que les éventuels accents s'affichent correctement
            .'</div>';
    bd_errorExit($msg);
}

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
function bd_errorExit($msg) {
    ob_end_clean();	// Supression de tout ce qui a pu être déja généré
    ob_start('ob_gzhandler');
    echo    '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>',
            'Erreur base de données</title>',
            '<style>table{border-collapse: collapse;}td{border: 1px solid black;padding: 4px 10px;}</style>',
            '</head><body>',
            $msg,
            '</body></html>';
    exit(1);
}

/**
 * Gestion d'une erreur de requête à la base de données.
 *
 * A appeler impérativement quand un appel de mysqli_query() échoue
 * Appelle la fonction fd_bd_erreurExit() qui affiche un message d'erreur puis termine le script
 *
 * @param objet		$bd		Connecteur sur la bd ouverte
 * @param string	$sql	requête SQL provoquant l'erreur
 */
function bd_error($bd, $sql) {
    $errNum = mysqli_errno($bd);
    $errTxt = mysqli_error($bd);

    // Collecte des informations facilitant le debugage
	//TO DO : virer ça à la fin
    $msg = '<h4>Erreur de requête</h4>'
            ."<pre><b>Erreur mysql :</b>$errNum"
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

    bd_errorExit($msg);
}


/////////////////////////////   Fonctions de protection contre d'odieux spams   ///////////////////////////////

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
function bd_protect($bd, $str) {
	$str = trim($str);
	return mysqli_real_escape_string($bd, $str);
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
 *	@param	string 	$str	la chaine à protéger
 * 	@return string 	la chaîne protégée
 */
function out_protect($str) {
	$str = trim($str);
	return htmlentities($str, ENT_QUOTES, 'UTF-8');
}


////////////////////////////////   Fonctions usuelles ////////////////////////////////

/**
 * Redirige l'utilisateur sur une page
 *
 * @param string	$page		Page où l'utilisateur est redirigé
 */
function redirect($page) {
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
  * @param string	$prefix		Préfixe du chemin relatif de la page vers laquelle on redirige à la fin
 */
function exit_session($prefix = '..') {
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

	header("Location: {$prefix}/index.php");
	exit();
}

/**
 * Teste si une valeur est une valeur entière
 *
 * @param mixed     $x  valeur à tester
 * @return boolean  TRUE si entier, FALSE sinon
*/
function is_entier($x) {
    return is_numeric($x) && ($x == (int) $x);
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
* Affiche les clés et valeurs du tableau global POST
*/
function display_post() {
	echo 'post  :  ';
	foreach($_POST as $key => $val) {
		echo $key, ' => ', $val, ' | ';
	}
}

/**
* Affiche les clés et valeurs du tableau global GET
*/
function display_get() {
	echo 'get  :  ';
	foreach($_GET as $key => $val) {
		echo $key, ' => ', $val, ' | ';
	}
}


?>
