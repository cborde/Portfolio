<?php
ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

error_reporting(E_ALL);

if ($_GET){
	$livre = bgl_control_get ();
}

bg_html_debut('BookShop | Recherche', '../styles/bookshop.css');

bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

bgl_contenu($livre);

bg_bookshop_pied();

bg_html_fin();

ob_end_flush();


/**
 *	Contrôle de la validité des informations reçues via la query string 
 *
 * En cas d'informations invalides, la session de l'utilisateur est arrêtée et il redirigé vers la page index.php
 *
 * @global  array     $_GET
 *
 * @return            partie du nom de l'auteur à rechercher            
 */
function bgl_control_get (){
	(count($_GET) != 1) && bg_exit_session();
	(! isset($_GET['article'])) && bg_exit_session();

        $livre = trim($_GET['article']);
        $notags = strip_tags($livre);
        (mb_strlen($notags, 'UTF-8') != mb_strlen($livre, 'UTF-8')) && bg_exit_session();
    
	return $livre;
}

/**
 *      Contenu de la page
 *
 *  @param $livre int id du livre recherché
 *
 *
 */



function bgl_contenu ($livre){
        
    $bd = bg_bd_connect();
    $li = bg_bd_protect($bd, $livre); 
    
    $sql = "SELECT liID, edNom, liTitre, liPrix, liPages, liISBN13, edWeb, auNom, auPrenom, liResume
        FROM livres, editeurs, aut_livre, auteurs
        WHERE  liIDEditeur = edID
        AND liID = al_IDLivre
        AND al_IDAuteur = auID
        AND liID = '$li'";
        
    $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);

    $lastID = -1;
    while ($t = mysqli_fetch_assoc($res)) {            
            
            if ($t['liID'] != $lastID) {
                if ($lastID != -1) {
                    bgl_affichage($livre);	
                }
                $lastID = $t['liID'];
                $livre = array(	'id' => strip_tags($t['liID']), 
                                'titre' => strip_tags($t['liTitre']),
                                'edNom' => strip_tags($t['edNom']),
                                'edWeb' => strip_tags($t['edWeb']),
                                'resume' => strip_tags($t['liResume']),
                                'pages' => strip_tags($t['liPages']),
                                'ISBN13' => strip_tags($t['liISBN13']),
                                'prix' => strip_tags($t['liPrix']),
                                'auteurs' => array(array('prenom' => strip_tags($t['auPrenom']), 'nom' => strip_tags($t['auNom'])))
                                );
            }
            else {
                $livre['auteurs'][] = array('prenom' => strip_tags($t['auPrenom']), 'nom' => strip_tags($t['auNom']));
            }		
    }
    if ($lastID != -1) {
        bgl_affichage($livre);	
    }
    else{
        echo '<h1>Détails du livre</h1>',
        '<p>Aucun livre trouvé</p>';
    }

    mysqli_free_result($res);
    mysqli_close($bd);


}

/**
 * Fonction affichage des livres
 *
 * @param $livre : tableau contenant les informations sur un livre
 */


function bgl_affichage($livre){
    echo '<h1> Détails du livre </h1>',
    '<h2>', $livre['titre'], '</h2>',    
    
      '<div class="details">',
        
        '<img class="imgD" src="../images/livres/',$livre['id'],'.jpg" alt="', $livre['titre'],'" width="182" height="300">',
        
        '</div>',
        
        '<div class="details">',
            'Ecrit par : ';
            
            $i = 0;
            
            foreach ($livre['auteurs'] as $auteur) {
 		$supportLien = "{$auteur['prenom']} {$auteur['nom']}";
		if ($i > 0) {
			echo ', ';
		}
		$i++;
		echo '<a href="../php/recherche.php?type=auteur&quoi=', urlencode($auteur['nom']), '">',bg_protect_sortie($supportLien), '</a>';
            }    
            echo '<br>',
            'Editeur : <a class="lienExterne" href="http://', $livre['edWeb'],'" target="_blank">', $livre['edNom'],'</a><br>',
            'Prix : ', $livre['prix'], ' € <br>',
            'Pages : ', $livre['pages'], '<br>',
            'ISBN13 : ', $livre['ISBN13'], '<br>',
            '<p>Résumé : <em>', $livre['resume'], '</em></p>',
            
            '<a class=bcDetails" href="./ajouter_panier.php?id=',$livre['id'],'" title="Panier">',bg_form_input(bg_Z_SUBMIT,'btnAjout', 'Panier'),'</a>',

            ' <a class=bcDetails" href="./ajouter_liste.php?id=',$livre['id'],'" title="Wishlist">',bg_form_input(bg_Z_SUBMIT,'btnWishList', 'Wishlist'),'</a>';
        '</div>';
    
//     '</div>';
    
}











?>