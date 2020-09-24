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

bg_html_debut('BookShop | Récapitulatif des commandes', '../styles/bookshop.css');

bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

bgl_contenu();

//Affichage du footer
bg_bookshop_pied();

//Fin de la page HTML
bg_html_fin();

ob_end_flush();


// ----------  Fonctions locales au script ----------- //

/**
 *	Affichage du contenu de la page
 *	
 */
function bgl_contenu() {
	echo '<h2>Récapitulatif des commandes</h2>';
	$cmd = bgl_liste_commande();
	if($cmd != 0){
		bgl_afficher_commandes($cmd);
	}
	else{
		echo '<p>Aucune commande n\'a été passée.</p>';
	}
}	


/**
 *	Affichage de toutes les commandes.
 *
 *	@param	array		$cmd 		tableau associatif des infos sur les commandes
 */
function bgl_afficher_commandes($cmd) {
	foreach($cmd AS $cle => $val){
		if(isset($cmd[$cle])){
			bg_afficher_commande($cmd[$cle]);	
		}
	}
}


/** 
 *	Cherche les commandes de l'utilisateur
 *
 *	@return 	array 	$commande	tableau contenant les données des commandes de l'utilisateur
 */
function bgl_liste_commande() { 
			// Connexion à la base de données	
			$bd = bg_bd_connect();
			// Requête SQL
			$id = $_SESSION['cliID'];
			$sql = 	"SELECT coID, coDate, coHeure, liTitre, liPrix, ccQuantite, liPrix * ccQuantite AS sum
					FROM commandes, compo_commande, livres
					WHERE coID = ccIDCommande
					AND ccIDLivre = liID
					AND coIDClient = $id
					ORDER BY coDate DESC, coHeure DESC";
			
			$res = mysqli_query($bd, $sql) or bg_bd_erreur($bd, $sql);
			//Stockage de chaque commande dans un tableau
			
			$nb = mysqli_num_rows($res);
			if($nb ==0){
				mysqli_free_result($res);
				mysqli_close($bd);
				return 0;
			}
			
			$lastID = -1;
			$numCom = 0;
			
			while ($t = mysqli_fetch_assoc($res)) {
				if ($t['coID'] != $lastID) {
					if ($lastID != -1) {
						$numCom++;	
					}
					$lastID = $t['coID'];
					$commande[$numCom] = array(	'id' => $t['coID'], 
									'date' => $t['coDate'],
									'heure' => $t['coHeure'],
									'article' => array(array(
									'titre' => $t['liTitre'],
									'prix' => $t['liPrix'],
									'quantite' => $t['ccQuantite'],
									'total' => $t['sum'])
									));
				}
				else {
					//Si il y a plusieurs livres dans une même commande...
					$commande[$numCom]['article'][] = array(
									'titre' => $t['liTitre'],
									'prix' => $t['liPrix'],
									'quantite' => $t['ccQuantite'],
									'total' => $t['sum']);
				}
			}
			//Fin de la requête SQL
			mysqli_free_result($res);
			mysqli_close($bd);
	return $commande;
}


?>