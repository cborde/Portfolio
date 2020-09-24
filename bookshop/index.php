<?php
//Démarage de la buffurisation
ob_start('ob_gzhandler');

//Lancement de la session
session_start();

//Apporte les fonctions
require_once './php/bibli_generale.php';
require_once './php/bibli_bookshop.php';

//Capture des erreurs
error_reporting(E_ALL);

//Début de la page HTML
bg_html_debut('BookShop | Bienvenue', './styles/bookshop.css');

//Entete de la page HTML et affichage du menu selon l'état de connexion du client
bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'./');

//Affichage du contenue
bgl_contenu();

//Affichage du footer
bg_bookshop_pied();

//Fin de la page HTML
bg_html_fin();

ob_end_flush();


// ----------  Fonctions locales au script ----------- //

/** 
 *	Affichage du contenu de la page
 */
function bgl_contenu() {
	
	echo 
		'<h1>Bienvenue sur BookShop !</h1>',
		
		'<p>Passez la souris sur le logo et laissez-vous guider pour découvrir les dernières exclusivités de notre site. </p>',
		
		'<p>Nouveau venu sur BookShop ? Consultez notre <a href="./html/presentation.html">page de présentation</a> !',
	
		'<h2>Dernières nouveautés </h2>',
	
		'<p>Voici les 4 derniers articles ajoutés dans notre boutique en ligne :</p>';
	
	//Affichage des nouveaux livres
	$nouveauLivre = bgl_nouveau_livre();
	bgl_afficher_blocs_livres($nouveauLivre);
	
	echo 
		'<h2>Top des ventes</h2>', 
		'<p>Voici les 4 articles les plus vendus :</p>';
	
	//Affichage des livres en top ventes
	$topVentes = bgl_top_livre();
	bgl_afficher_blocs_livres($topVentes);
}


/** 
 *	Affichage d'une liste de livres sous la forme de blocs
 *
 *	@param 	array 	$tLivres	tableau contenant un élément (tableau associatif) pour chaque livre (id, auteurs(nom, prenom), titre)
 */
function bgl_afficher_blocs_livres($tLivres) {
	//Affichage du livre pour chaque livre de la liste
	foreach ($tLivres as $livre) {
		bg_afficher_livre($livre, 'bcArticle', './');
	}
}

/** 
 *	Affichage d'une liste de livres sous la forme de blocs
 *
 *	@return 	array 	$livre	tableau contenant les données de plusieurs livres
 */
function bgl_nouveau_livre() { 
			// Connexion à la base de données	
			$bd = bg_bd_connect();
			// Requête SQL
			$sql = 	"SELECT livres.liID, liTitre, auNom, auPrenom 
					FROM livres INNER JOIN (SELECT liID FROM livres ORDER BY liID DESC LIMIT 0,4) copy ON copy.liID= livres.liID 
					INNER JOIN aut_livre ON al_IDLivre = livres.liID 
					INNER JOIN auteurs ON al_IDAuteur = auID";
			
			$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd, $sql);
			
			//Stockage de chaque livre dans un tableau
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
									'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']))
					);
				}
				else {
					//Si il y a plusieurs auteurs, ajout de l'auteur pour le livre concerné
					$livre[$count]['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
				}	
			}
			//Fin de la requête SQL
			mysqli_free_result($res);
			mysqli_close($bd);
	return $livre;
}

/** 
 *	Affichage d'une liste de livres sous la forme de blocs
 *
 *	@return 	array 	$livre	tableau contenant les données de plusieurs livres
 */
function bgl_top_livre() { 
			// Connexion à la base de données	
			$bd = bg_bd_connect();
			// Requête SQL
			$sql = 	"SELECT livres.liID, liTitre, auNom, auPrenom 
					FROM livres INNER JOIN (SELECT ccIDLivre FROM compo_commande GROUP BY ccIDLivre ORDER BY SUM(ccQuantite) DESC LIMIT 0,4) copy ON copy.ccIDLivre= livres.liID 
					INNER JOIN aut_livre ON al_IDLivre = livres.liID 
					INNER JOIN auteurs ON al_IDAuteur = auID";
			
			$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd, $sql);
			
			//Stockage de chaque livre dans un tableau
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
									'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']))
					);
				}
				else {
					//Si il y a plusieurs auteurs, ajout de l'auteur pour le livre concerné
					$livre[$count]['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
				}	
			}
			//Fin de la requête SQL
			mysqli_free_result($res);
			mysqli_close($bd);
	return $livre;
}
?>
