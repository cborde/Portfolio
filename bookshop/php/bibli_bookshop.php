<?php

/*********************************************************
 *        Bibliothèque de fonctions spécifiques          *
 *               à l'application BookShop                *
 *********************************************************/

 
 // constantes utilisées pour initialiser certains champs de la table client lors de l'inscription d'un utilisateur
 define('bg_INVALID_STRING', ''); //utilisé pour les champs adresse, ville et pays
 define('bg_INVALID_CODE_POSTAL', 0); //utilisé pour le code postal
 
 // Nombre d'années affichées pour la date de naissance du formulaire d'inscription 
 define('NB_ANNEES_DATE_NAISSANCE', 121);

/**
 *	Fonction affichant le canevas général de l'application BookShop 
 *
 *	Affiche bloc page, entête et menu de navigation, enseigne, ouverture du bloc de contenu.
 *
 *  @param 	boolean		$connecte	Indique si l'utilisateur est connecté ou non.
 *	@param 	string		$prefix		Prefixe des chemins vers les fichiers du menu (usuellement "./" ou "../").
 */
function bg_bookshop_enseigne_entete($connecte,$prefix) {
	echo 
		'<div id="bcPage">',
	
		'<aside>',
			'<a href="http://www.facebook.com" target="_blank"></a>',
			'<a href="http://www.twitter.com" target="_blank"></a>',
			'<a href="http://plus.google.com" target="_blank"></a>',
			'<a href="http://www.pinterest.com" target="_blank"></a>',
		'</aside>',
		
		'<header>';
	
	bg_bookshop_menu($connecte,$prefix);
	echo 	'<img src="', $prefix,'images/soustitre.png" alt="sous titre">',
		'</header>',
		'<section>';
}


/**
 *	Fonction affichant le menu de navigation de l'application BookShop 
 *
 *  @param 	boolean		$connecte	Indique si l'utilisateur est connecté ou non.
 *	@param 	string		$prefix		Prefixe des chemins vers les fichiers du menu (usuellement "./" ou "../").
 */
function bg_bookshop_menu($connecte, $prefix) {		
	echo 
		'<nav>',	
			'<a href="', $prefix, 'index.php"></a>';
	
	$liens = array( 'recherche' => array( 'pos' => 1, 'title' => 'Effectuer une recherche'),
					'panier' => array( 'pos' => 2, 'title' => 'Voir votre panier'),
					'liste' => array( 'pos' => 3, 'title' => 'Voir une liste de cadeaux'),
					'compte' => array( 'pos' => 4, 'title' => 'Consulter votre compte'),
					'deconnexion' => array( 'pos' => 5, 'title' => 'Se déconnecter'));
					
	if (!$connecte){
		unset($liens['compte']);
		unset($liens['deconnexion']);
		$liens['recherche']['pos']++;
		$liens['panier']['pos']++;
		$liens['liste']['pos']++;
		/*TODO : 	- peut-on implémenter les 3 incrémentations ci-dessus avec un foreach ? */
		$liens['login'] = array( 'pos' => 5, 'title' => 'Se connecter');
		/* Debug :
		echo '<pre>', print_r($liens, true), '</pre>';
		exit;*/
	}
	
	foreach ($liens as $cle => $elt) {
		echo
			'<a class="lienMenu position', $elt['pos'], '" href="', $prefix, 'php/', $cle, '.php" title="', $elt['title'], '"></a>';
	}
	if(!$connecte){
		echo
			'<a class="lienMenu position1" title="empty"></a>';
	}
	echo '</nav>';
}


/**
 *	Fonction affichant le pied de page de l'application BookShop.
 */
function bg_bookshop_pied() {
	echo 
		'</section>', // fin de la section
		'<footer>', 
			'BookShop &amp; Partners &copy; ', date('Y'), ' - ',
			'<a href="apropos.html">A propos</a> - ',
			'<a href="confident.html">Emplois @ BookShop</a> - ',
			'<a href="conditions.html">Conditions d\'utilisation</a>',
		'</footer>',
	'</div>'; // fin bcPage
}


/**
 *	Affichage d'un livre.
 *
 *	@param	array		$livre 		tableau associatif des infos sur un livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, edWeb, edNom)
 *	@param 	string 		$class		classe de l'élement div  : bcResultat ou bcArticle
 *  @param 	String		$prefix		Prefixe des chemins vers le répertoire images (usuellement "./" ou "../").
 */
function bg_afficher_livre($livre, $class, $prefix) {
	echo 
		'<div class="', $class, '">';
			echo '<a class="addToCart" href="',$prefix,'php/ajouter_panier.php?id=',$livre['id'],'" title="Ajouter au panier"></a>';
			if ($class == 'bcliste'){
                            echo '<a class="deleteToWishlist" href="',$prefix,'php/supprimer_liste.php?id=', $livre['id'],'" title="Supprimer de la liste de cadeaux"></a>';
			}else{
                            echo '<a class="addToWishlist" href="',$prefix,'php/ajouter_liste.php?id=', $livre['id'],'" title="Ajouter à la liste de cadeaux"></a>';
			}
			echo '<a href="', $prefix, 'php/details.php?article=', $livre['id'], '" title="Voir détails"><img src="', $prefix, 'images/livres/', $livre['id'], '_mini.jpg" alt="', 
			bg_protect_sortie($livre['titre']),'"></a>';
	if ($class == 'bcResultat' || $class == 'bcliste'){
		echo	'<strong>', bg_protect_sortie($livre['titre']), '</strong> <br>',
			'Ecrit par : ';
	}
	elseif($class == 'bcArticle'){
		echo '<br>';
	}
	$i = 0;
	foreach ($livre['auteurs'] as $auteur) {
		$supportLien = $class == 'bcResultat' || $class == 'bcliste' ? "{$auteur['prenom']} {$auteur['nom']}" : "{$auteur['prenom']{0}}. {$auteur['nom']}";
		if ($i > 0) {
			echo ', ';
		}
		$i++;
		echo '<a href="', $prefix, 'php/recherche.php?type=auteur&quoi=', urlencode($auteur['nom']), '">',bg_protect_sortie($supportLien), '</a>';
	}
	if ($class == 'bcResultat' || $class == 'bcliste'){		
		echo	'<br>Editeur : <a class="lienExterne" href="http://', bg_protect_sortie($livre['edWeb']), '" target="_blank">', bg_protect_sortie($livre['edNom']), '</a><br>',
				'Prix : ', $livre['prix'], ' &euro;<br>',
				'Pages : ', $livre['pages'], '<br>',
				'ISBN13 : ', bg_protect_sortie($livre['ISBN13']), '</div>';
	}
	elseif($class == 'bcArticle'){
		echo 
			'<br>', 
			'<strong>', bg_protect_sortie($livre['titre']), '</strong>',
		  '</div>';
	}
}



/**
 *	Affichage d'une commande.
 *
 *	@param	array		$commande 		tableau associatif des infos sur une commande
 */
function bg_afficher_commande($commande) {
	$an = substr($commande['date'], 0, -4);
	$mo = substr($commande['date'], 4, -2);
	$jo = substr($commande['date'], 6, 7);
	$nb = strlen($commande['heure']);
	if($nb==3){
		$he = substr($commande['heure'], 0, 1);
		$mi = substr($commande['heure'], 1, 2);
	}else{
		$he = substr($commande['heure'], 0, -2);
		$mi = substr($commande['heure'], 2, 3);
	}
	$somme = 0;
	echo '<table class="tabCo">',
			'<tbody>',
				'<tr>',
					'<td class="tabCoDate" colspan="4">Commande du ', bg_protect_sortie($jo),'/', bg_protect_sortie($mo),'/', 
						bg_protect_sortie($an),' à ', bg_protect_sortie($he),'h',bg_protect_sortie($mi), '</td>',
				'</tr>',
				'<tr class="tabCoLegende">',
					'<td class="tabCoTitre">Titre</td>',
					'<td class="tabCoPrix" >Prix</td>',
					'<td class="tabCoQuantite">Qté</td>',
					'<td class="tabCoTotal">Total</td>',
				'</tr>';
	foreach ($commande['article'] as $article) {
		echo '<tr>',
					'<td class="tabCoTitre">', bg_protect_sortie($article['titre']),'</td>',
					'<td class="tabCoPrix">', bg_protect_sortie($article['prix']),' &euro;</td>',
					'<td class="tabCoQuantite" >', bg_protect_sortie($article['quantite']),'</td>',
					'<td class="tabCoTotal">', bg_protect_sortie($article['total']), ' &euro;</td>',
			'</tr>';
			$somme += $article['total'];
	}
	echo 	'<tr class="tabCoSomme">',
				'<td colspan="2"></td>',
				'<td class="tabCoTotal">Total :</td>',
				'<td>',$somme,' &euro;</td>',
			'</tr>',
		'</tbody>',
	'</table> <br><br>';
}


/**
 *	Affichage d'un panier.
 *
 *	@param	array		$panier 		tableau associatif des différents livres du panier
 */
function bg_afficher_panier($panier) {
	$somme = 0;
	echo '<table class="tabCo">',
			'<tbody>',
				'<tr>',
					'<td class="tabCoDate" colspan="5">Panier en cours</td>',
				'</tr>',
				'<tr class="tabCoLegende">',
					'<td class="tabCoTitre">Titre</td>',
					'<td class="tabCoPrix" >Prix</td>',
					'<td class="tabCoQuantite">Qté</td>',
					'<td class="tabCoTotal">Total</td>',
					'<td class="tabCoAction">Actions</td>',
				'</tr>';
	foreach ($panier as $article) {
		$total = $article['quantite'] * $article['prix'];
		echo '<tr>',
					'<td class="tabCoTitre">', bg_protect_sortie($article['titre']),'</td>',
					'<td class="tabCoPrix">', bg_protect_sortie($article['prix']),' &euro;</td>',
					'<td class="tabCoQuantite" >', bg_protect_sortie($article['quantite']),'</td>',
					'<td class="tabCoTotal">', $total, ' &euro;</td>',
					'<td class="tabCoAction"><a class="enlever" href="./supprimer_panier.php?id=',$article['id'],'" title="Enlever du panier"></a>  <a class="ajouter" href="./ajouter_panier.php?id=',$article['id'],'" title="Ajouter au panier"></a></td>',
			'</tr>';
			$somme += $total;
	}
	echo 	'<tr class="tabCoSomme">',
				'<td colspan="3"></td>',
				'<td class="tabCoTotal">Total :</td>',
				'<td>',$somme,' &euro;</td>',
			'</tr>',
		'</tbody>',
	'</table> <br><br>';
}


/** 
 *	Renvoie un tableau contenant les pages du site bookshop
 *
 * 	@return array pages du site
 */
function get_pages_bookshop() {
	return array('index.php', 'login.php', 'inscription.php', 'deconnexion.php', 'recherche.php', 'presentation.html');
}



?>
