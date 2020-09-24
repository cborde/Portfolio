<?php
//Démarage de la buffurisation
ob_start('ob_gzhandler');

//Lancement de la session
session_start();

//Apporte les fonctions
require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

//Capture des erreurs
error_reporting(E_ALL);

// si utilisateur n'est pas authentifié, on le redirige sur la page appelante, ou à défaut sur l'index
if (!isset($_SESSION['cliID'])){
    $page = '../index.php';
    if (isset($_SERVER['HTTP_REFERER'])){
        $page = $_SERVER['HTTP_REFERER'];
        $nom_page = url_get_nom_fichier($page);
        // suppression des éventuelles boucles de redirection
        if (($nom_page == 'login.php') || ($nom_page == 'inscription.php')){
            $page = '../index.php'; 
        } // si la page appelante n'appartient pas à notre site
        else if (! in_array($nom_page, get_pages_bookshop())){
            $page = '../index.php';
        }  
    }
    bg_redirige($page);
}

$err = isset($_POST['btnModifier']) ? bgl_modification() : array(); 

bg_html_debut('BookShop | Mon comtpe', '../styles/bookshop.css');

bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

bgl_contenu($err);

//Affichage du footer
bg_bookshop_pied();

//Fin de la page HTML
bg_html_fin();

ob_end_flush();


// ----------  Fonctions locales au script ----------- //

/**
 *	Affichage du contenu de la page
 *	@param 	array	$err	tableau d'erreurs à afficher
 */
function bgl_contenu($err) {
	$bd = bg_bd_connect();
	$sql = 	"SELECT * 
			FROM clients
			WHERE cliID = '$_SESSION[cliID]'";
	$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
	$t = mysqli_fetch_assoc($res);
	$info = array(	'id' => $t['cliID'], 
		'email' => $t['cliEmail'],
		'pass' => $t['cliPassword'],
		'nomprenom' => $t['cliNomPrenom'],
		'date' => $t['cliDateNaissance'],
		'adresse' => $t['cliAdresse'],
		'ville' => $t['cliVille'],
		'cp' => $t['cliCP'],
		'pays' => $t['cliPays']
	);		
		
	
	$email = $info['email'];
	$nomprenom = isset($info['nomprenom']) ? $info['nomprenom'] : '';
	$adresse = isset($info['adresse']) ? $info['adresse'] : '';
	$ville = isset($info['ville']) ? $info['ville'] : '';
	$cp = isset($info['cp']) && $info['cp']!=0 ? $info['cp'] : '';
	$pays = isset($info['pays']) ? $info['pays'] : '';
	$naiss_a = isset($info['date']) ? substr($info['date'], 0, -4) : '';
	$naiss_m = isset($info['date']) ? substr($info['date'], 4, -2) : '';
	$naiss_j = isset($info['date']) ? substr($info['date'], 6, 7) : '';	
	// libération des ressources
	mysqli_free_result($res);
	mysqli_close($bd);
	
	
	echo 
		'<H1>Mes informations personnelles</H1>';
		
	if (count($err) > 0) {
		echo '<p class="erreur">La modification n\'a pas pu être réalisée à cause des erreurs suivantes : ';
		foreach ($err as $v) {
			echo '<br> - ', $v;
		}
		echo '</p>';	
	}
	
    if (isset($_POST['source'])){
        $source = $_POST['source'];
    }
    else if (isset($_SERVER['HTTP_REFERER'])){
        $source = $_SERVER['HTTP_REFERER'];
        $nom_source = url_get_nom_fichier($source);
        // si la page appelante n'appartient pas à notre site
        if (! in_array($nom_source, get_pages_bookshop())){
            $source = '../index.php';
        }
    }
    else{
        $source = '../index.php';
    }
	
	echo 	
		'<a href="./recapitulatif.php"> <p> Accédez au récapitulatif des commandes !</p></a>',
		'<form method="post" action="compte.php">',
			bg_form_input(bg_Z_HIDDEN, 'source', $source),
			'<h2>Informations du compte</h2>',
			'<table>',
				bg_form_ligne('Votre adresse email :', bg_form_input(bg_Z_TEXT, 'email', $email, 30)),
				bg_form_ligne('Nom et prénom :', bg_form_input(bg_Z_TEXT, 'nomprenom', $nomprenom, 30)),
				bg_form_ligne('Date de naissance :', bg_form_date_disabled('naiss', NB_ANNEES_DATE_NAISSANCE, $naiss_j, $naiss_m, $naiss_a)),
				bg_form_ligne('Choisissez un mot de passe :', bg_form_input(bg_Z_PASSWORD, 'pass1', '', 30)),
				bg_form_ligne('Répétez le mot de passe :', bg_form_input(bg_Z_PASSWORD, 'pass2', '', 30)),
			'</table>',
			'<h2>Informations de livraison</h2>',
			'<table>',
			bg_form_ligne('Adresse :', bg_form_input(bg_Z_TEXT, 'adresse', $adresse, 30)),
			bg_form_ligne('Code postal :', bg_form_input(bg_Z_TEXT, 'cp', $cp, 30)),
			bg_form_ligne('Ville :', bg_form_input(bg_Z_TEXT, 'ville', $ville, 30)),
			//On aurait pu faire une liste select de pays qui est bien plus efficace pour la vérification...
			bg_form_ligne('Pays :', bg_form_input(bg_Z_TEXT, 'pays', $pays, 30)),
			
			'<tr><td colspan="2" style="padding-top: 10px;" class="centered">', bg_form_input(bg_Z_SUBMIT,'btnModifier','Modifier !'), '</td></tr>',
			'</table>',
		'</form>';
}	

/**
 *	Changement de données
 *		Etape 1. vérification de la validité des données
 *					-> return des erreurs si on en trouve
 *		Etape 2. Modification des données
 * @global  array     $_POST
 * @return array 	tableau assosiatif contenant les erreurs
 */
function bgl_modification() {
    
	$err = array();
	$email = trim($_POST['email']);
	$pass1 = trim($_POST['pass1']);
	$pass2 = trim($_POST['pass2']);
	$nomprenom = trim($_POST['nomprenom']);
	$adresse = trim($_POST['adresse']);
	$cp = trim($_POST['cp']);
	$ville = trim($_POST['ville']);
	$pays = trim($_POST['pays']);
	
	// vérification email
    $noTags = strip_tags($email);
    if ($noTags != $email){
        $err['email'] = 'L\'email ne peut pas contenir de code HTML.';
    }
    else {
        $i = mb_strpos($email, '@', 0, 'UTF-8');
        $j = mb_strpos($email, '.', 0, 'UTF-8');
        if ($i === FALSE || $j === FALSE){
            $err['email'] = 'L\'adresse email ne respecte pas le bon format.';	
        }
        else if (! filter_var($email, FILTER_VALIDATE_EMAIL)){
            $err['email'] = 'L\'adresse email ne respecte pas le bon format.';
        }
    }
    
	// vérification des mots de passe
	if (!empty($pass1) || !empty($pass2)){
		if ($pass1 != $pass2) {
			$err['pass1'] = 'Les mots de passe doivent être identiques.';	
		}
		else if (empty($pass1)) {
			$err['pass1'] = 'Le mot de passe doit être renseigné dans le premier champs.';	
		}
		else if (empty($pass2)) {
			$err['pass1'] = 'Vous n\'avez pas confirmé votre mot de passe.';	
		}
		else {
			$nb = mb_strlen($pass1, 'UTF-8');
			$noTags = strip_tags($pass1);
			if (mb_strlen($noTags, 'UTF-8') != $nb) {
				$err['pass1'] = 'La zone Mot de passe ne peut pas contenir de code HTML.';
			}
			else if ($nb < 4 || $nb > 20){
				$err['pass1'] = 'Le mot de passe doit être constitué de 4 à 20 caractères.';
			}
				
		}
	}
	
	// vérification des noms et prenoms
	$noTags = strip_tags($nomprenom);
    if ($noTags != $nomprenom){
        $err['nomprenom'] = 'Le nom et le prénom ne peuvent pas contenir de code HTML.';
    }
    else if (empty($nomprenom)) {
		$err['nomprenom'] = 'Le nom et le prénom doivent être renseignés.';	
    }
    else if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,99}$", $nomprenom)) {
        $err['nomprenom'] = 'Le nom et le prénom ne sont pas valides.';
    }
	
	//verification du code postal
	if (!preg_match('#^[0-9]{5}$#',$cp)){
		$err['cp'] = 'Le code postal doit être composé de 5 numéros.';
	}

	//verification de l'adresse
	if (mb_regex_encoding ('UTF-8') && strlen($adresse)>100) {
        $err['adresse'] = 'L\'adresse n\'est pas valide.';
    }
	
	//verification de la ville
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $ville)) {
        $err['ville'] = 'La ville n\'est pas valide.';
    }
	
	//verification du pays
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $pays)) {
        $err['pays'] = 'Le pays n\'est pas valide.';
    }
	
	if (count($err) == 0) {
		$bd = bg_bd_connect();
		$sql = 	"SELECT cliEmail 
				FROM clients
				WHERE cliID = '$_SESSION[cliID]'";
		$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
		$t = mysqli_fetch_assoc($res);
		$info = array(
			'email' => $t['cliEmail'],
		);		
		$emailverif = $info['email'];
		mysqli_free_result($res);
		mysqli_close($bd);
		
		
		$bd = bg_bd_connect();
		if(strcmp($email, $emailverif) !=0){
			$email = bg_bd_protect($bd, $email);
			$sql = "SELECT cliID FROM clients WHERE cliEmail = '$email'"; 
			$res = mysqli_query($bd,$sql) or bg_bd_erreur($bd,$sql);
			
			if (mysqli_num_rows($res) != 0) {
				$err['email'] = 'L\'adresse email spécifiée existe déjà.';
				mysqli_free_result($res);
				mysqli_close($bd);
			}
			else{
				mysqli_free_result($res);
			}
		}
		
	}
	
	// s'il y a des erreurs ==> on retourne le tableau d'erreurs	
	if (count($err) > 0) { 	
		return $err;	
	}
	
	// pas d'erreurs ==> modification des données de l'utilisateur
	$nomprenom = bg_bd_protect($bd, $nomprenom);
	$adresse = bg_bd_protect($bd, $adresse);
	$ville = bg_bd_protect($bd, $ville);
	$cp = bg_bd_protect($bd, $cp);
	$pays = bg_bd_protect($bd, $pays);
	$pass = bg_bd_protect($bd, md5($pass1));
	$add = "";
	if (!empty($pass1) || !empty($pass2)){
		$add .= ", cliPassword='$pass'";
	}
	if(strcmp($email, $emailverif) !=0){
		$add .= ", cliEmail='$email'";
	}
				
	$sql = "UPDATE clients
			SET cliNomPrenom='$nomprenom', 
				cliAdresse='$adresse', 
				cliCP=$cp, 
				cliVille='$ville',
				cliPays='$pays'
				$add
			WHERE cliID = '$_SESSION[cliID]'";
            
	mysqli_query($bd, $sql) or bg_bd_erreur($bd, $sql);

	$id = mysqli_insert_id($bd);

	// libération des ressources
	mysqli_close($bd);
	
    // redirection vers la page d'origine
	bg_redirige($_POST['source']);
}
	


?>