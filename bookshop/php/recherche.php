<?php
//Démarage de la buffurisation
ob_start('ob_gzhandler');

//Lancement de la session
session_start();

//Apporte les fonctions
require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

//Capture des erreurs
error_reporting(E_ALL);

//Valeurs prédéfinis
$valueType = 'auteur';
$valueQuoi = '';
	
($_GET && $_POST) && bg_exit_session();

if ($_GET){
	$valueQuoi = bgl_control_get ();
}
else if ($_POST){
	$valueQuoi = bgl_control_post ($valueType);
}

//Début de la page HTML
bg_html_debut('BookShop | Recherche', '../styles/bookshop.css');

//Entete de la page HTML et affichage du menu selon l'état de connexion du client
bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

//Affichage du contenue
bgl_contenu($valueType, $valueQuoi);

//Affichage du footer
bg_bookshop_pied();

//Fin de la page HTML
bg_html_fin();

ob_end_flush();


// ----------  Fonctions locales au script ----------- //

/**
 *	Affichage de tous les livre.
 *
 *	@param	array		$livre 		tableau associatif des infos sur des livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, edWeb, edNom)
 *	@param 	string 		$debut		Numero du premier livre a etre affichée
 *  @param 	String		$pagination		Nombre de livre par pagination
 */
function bgl_afficher_livres($livre, $debut, $pagination) {
	$fin = $debut + $pagination;
	for($i=$debut; $i<$fin; $i++){
		if(isset($livre[$i])){
			bg_afficher_livre($livre[$i], 'bcResultat', '../');	
		}
	}
}


/**
 *	Contenu de la page : formulaire de recherche + résultats éventuels 
 *
 * @param   string    $valueType type de recherche (auteur ou titre)
 * @param   string    $valueQuoi partie du nom de l'auteur ou du titre à rechercher
 * @global  array     $_POST
 * @global  array     $_GET
 */
function bgl_contenu($valueType, $valueQuoi) {
	$pagination = 5;
	$totalLivres = -1;
	$position = -1;
	$nbLivre = 0;

	//-- Calcul des limites ------------------------------
	// Au 1er passage il n'y a pas de soumission de
	// formulaire et le tableau $_POST est donc vide.

	if (isset($_POST['t']) && estEntier($_POST['t'])) {
		$totalLivres = (int) $_POST['t'];
	}

	if (isset($_POST['btn']) && estEntier($_POST['btn'])) {
		$position = (int) $_POST['btn'];
		$position = ($position - 1) * $pagination;
	}

	// Soit 1er passage, soit paramètres POST "modifiés"
	if ($totalLivres < 0 || $position < 0) {
		$totalLivres = $position = 0;
	}
	
	// Vérification paramètres POST valides	
	if (! estEntre($position, 0 ,$totalLivres)) {
		$totalLivres = $position = 0;
	}
	
	echo '<h3>Recherche par une partie du nom d\'un auteur ou du titre</h3>'; 
	
	/** 3ème version : version "formulaire de recherche" */
	echo '<form action="recherche.php" method="post">',
			'<p class="centered">Rechercher <input type="text" name="quoi" value="', bg_protect_sortie($valueQuoi), '">', 
			' dans ', 
				'<select name="type">', 
					'<option value="auteur" ', $valueType == 'auteur' ? 'selected' : '', '>auteurs</option>', 
					'<option value="titre" ', $valueType == 'titre' ? 'selected' : '','>titre</option>', 
				'</select>', 
			' <input type="submit" value="Rechercher" name="btnRechercher"></p></form>'; 

	if (! $_GET && ! $_POST){
        return; // ===> Fin de la fonction (ni soumission du formulaire, ni query string)
    }
	if ( mb_strlen($valueQuoi, 'UTF-8') < 2){
        echo '<p><strong>Le mot recherché doit avoir une longueur supérieure ou égale à 2</strong></p>';
		return; // ===> Fin de la fonction
	}
	
	
	// 1er passage : récup du nombre total de livres
	if ($totalLivres == 0) {
		$bd = bg_bd_connect();
		$q = bg_bd_protect($bd, $valueQuoi); 
		if ($valueType == 'auteur') {
			$critere = " WHERE liID in (SELECT al_IDLivre FROM aut_livre INNER JOIN auteurs ON al_IDAuteur = auID WHERE auNom LIKE '%$q%')";
		} 
		else {
			$critere = " WHERE liTitre LIKE '%$q%'";	
		}
		$sql = 	"SELECT DISTINCT liID
				FROM livres INNER JOIN editeurs ON liIDEditeur = edID 
							INNER JOIN aut_livre ON al_IDLivre = liID 
							INNER JOIN auteurs ON al_IDAuteur = auID 
				$critere";
		$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
		$totalLivres = mysqli_num_rows($res);
		mysqli_free_result($res);
		mysqli_close($bd);
	}
	
	// affichage des résultats
	// ouverture de la connexion, requête
	$bd = bg_bd_connect();
	
	$q = bg_bd_protect($bd, $valueQuoi); 
	
	if ($valueType == 'auteur') {
        $critere = " WHERE liID in (SELECT al_IDLivre FROM aut_livre INNER JOIN auteurs ON al_IDAuteur = auID WHERE auNom LIKE '%$q%')";
	} 
	else {
		$critere = " WHERE liTitre LIKE '%$q%'";	
	}
	$sql = 	"SELECT liID, liTitre, liPrix, liPages, liISBN13, edNom, edWeb, auNom, auPrenom 
			FROM livres INNER JOIN editeurs ON liIDEditeur = edID 
						INNER JOIN aut_livre ON al_IDLivre = liID 
						INNER JOIN auteurs ON al_IDAuteur = auID 
			$critere";
	$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);

	// 1er passage : récup du nombre total de livres
	if ($totalLivres == 0) {
		$totalLivres = mysqli_num_rows($res);
	}

	$lastID = -1;
	$count = 0;
	while ($t = mysqli_fetch_assoc($res)) {
		if ($t['liID'] != $lastID) {
			if ($lastID != -1) {
				$count++;	
			}
			$lastID = $t['liID'];
			$livre[$count] = array(	'id' => $t['liID'], 
							'titre' => $t['liTitre'],
							'edNom' => $t['edNom'],
							'edWeb' => $t['edWeb'],
							'pages' => $t['liPages'],
							'ISBN13' => $t['liISBN13'],
							'prix' => $t['liPrix'],
							'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']))
			);
		}
		else {
			$livre[$count]['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
		}	
	}
	
    // libération des ressources
	mysqli_free_result($res);
	mysqli_close($bd);	
	if($totalLivres>0){
		echo "<p>", $totalLivres, " livre(s) trouvé(s) !</p>";
		bgl_afficher_livres($livre, $position, $pagination);
	}
	else{
		echo "<p>Aucuns livres trouvés !</p>";
	}
	
	echo '<form method="POST" action="recherche.php">';
		if(isset($_POST['quoi']) && isset($_POST['type'])){
			echo '<input type="hidden" name="quoi" value="', trim($_POST['quoi']), '">';
			if($_POST['type'] == 'auteur'){
				echo '<input type="hidden" name="type" value="auteur">';
			}
			else{
				echo '<input type="hidden" name="type" value="titre">';
			}
		}else{
			echo '<input type="hidden" name="quoi" value="', trim($_GET['quoi']), '">';
			if($_GET['type'] == 'auteur'){
				echo '<input type="hidden" name="type" value="auteur">';
			}
			else{
				echo '<input type="hidden" name="type" value="titre">';
			}
		}
		
		
	echo '<input type="hidden" name="t"  value="', $totalLivres, '">';
	
	
	if ($totalLivres > 0) {
		echo '<p>Page(s) : ';
	}
	for ($i = 0, $nbLivre = 0; $i < $totalLivres; $i += $pagination) {
		$nbLivre ++;
		if ($i == $position) {  // page en cours, pas de lien
			echo " $nbLivre ";
		} else {
			echo '<input type="submit" name="btn" value="', $nbLivre, '">';
		}
	}
	
	echo '</p></form>';
}

/**
 *	Contrôle de la validité des informations reçues via la query string 
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il redirigé vers la page index.php
 * @global  array     $_GET
 * @return            partie du nom de l'auteur à rechercher            
 */
function bgl_control_get (){
	(count($_GET) != 2) && bg_exit_session();
	(! isset($_GET['type']) || $_GET['type'] != 'auteur') && bg_exit_session();
	(! isset($_GET['quoi'])) && bg_exit_session();
    $valueQ = trim($_GET['quoi']);
    $notags = strip_tags($valueQ);
    (mb_strlen($notags, 'UTF-8') != mb_strlen($valueQ, 'UTF-8')) && bg_exit_session();
	return $valueQ;
}

/**
 *	Contrôle de la validité des informations lors de la soumission du formulaire  
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il redirigé vers la page index.php
 * @param   string    $valueT   type de recherche (auteur ou titre)
 * @global  array     $_POST
 * @return            partie du nom de l'auteur ou du titre à rechercher            
 */
function bgl_control_post (&$valueT){
	(! isset($_POST['type'])) && bg_exit_session();
	($_POST['type'] != 'auteur' && $_POST['type'] != 'titre') && bg_exit_session();
	(! isset($_POST['quoi'])) && bg_exit_session();
	$valueT = $_POST['type'] == 'auteur' ? 'auteur' : 'titre';
    $valueQ = trim($_POST['quoi']);
    $notags = strip_tags($valueQ);
    (mb_strlen($notags, 'UTF-8') != mb_strlen($valueQ, 'UTF-8')) && bg_exit_session(); 
    return $valueQ;
}
?>
