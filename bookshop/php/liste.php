<?php
ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

bg_html_debut('BookShop | Liste', '../styles/bookshop.css');

bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

bgl_contenu();

bg_bookshop_pied();

bg_html_fin();

ob_end_flush();

/**
 * Fonction contenu de la page
 *
 * @global array $_SESSION
 */

function bgl_contenu(){

    $bd = bg_bd_connect();

    if (! isset($_SESSION['cliID'])){ //Si non connecté, on demande à l'utilisateur de se connecter

        echo '<h1> Liste de voeux </h1>',
        '<h2> Votre liste de voeux </h2>',
        '<div><p> Connectez-vous pour profiter de cette fonctionnalité </p>',
        '<a id="lien_connect" href="./login.php" title="Se Connecter">',bg_form_input(bg_Z_SUBMIT,'btnLogin', 'Se connecter'),'</a>';

    } else {

        echo '<h1> Liste de voeux </h1>',
        '<h2> Votre liste de voeux </h2>';
        
        $sql = 'SELECT liID, edNom, liTitre, liPrix, liPages, liISBN13, edWeb, auNom, auPrenom, liResume
            FROM livres, editeurs, aut_livre, auteurs, listes
            WHERE  liIDEditeur = edID
            AND liID = al_IDLivre
            AND al_IDAuteur = auID
            AND listIDLivre = liID
            AND listIDClient = "'.$_SESSION['cliID'].'"';
            
       bgl_boucle_aff($bd, $sql);


    }
    
    echo '<h2 id=rech_autre> Rechercher liste d\'une autre personne </h2>';
    
    $email="";
    
    echo '<form action="#rech_autre" method="post">',
                '<p class="centered">Rechercher (email) <input type="text" name="email" value="', bg_protect_sortie($email), '">', 
                '<input type="submit" value="Rechercher" name="btnRechercher"></p>',
        '</form>';
        
    if (! $_GET && ! $_POST){
        return;
    }
    
    $mail = bg_bd_protect($bd, $_POST['email']);
    
    //test non html 
    $clean = strip_tags($mail);

    if ($clean != $mail){
        header('Location: ../index.php');
        exit();
    }   

    $mail = mysqli_real_escape_string($bd, $mail);
    //test email
    
    $long = strlen($mail);
    
    $ar = false;
    $pt = false;
    for ($i = 0; $i < $long; $i++){
        if ($mail[$i] == '@'){
            $ar = true;
        }
        if ($mail[$i] == '.'){
            $pt = true;
        }
    }

    if (!$ar || !$pt){
        echo '<p class="erreur">L\adresse email ne respecte pas le bon format</p>';
        return false;
    }
    
    $sql = 'SELECT liID, edNom, liTitre, liPrix, liPages, liISBN13, edWeb, auNom, auPrenom, liResume
            FROM livres, editeurs, aut_livre, auteurs, listes, clients
            WHERE  liIDEditeur = edID
            AND liID = al_IDLivre
            AND al_IDAuteur = auID
            AND listIDLivre = liID
            AND listIDClient = cliID
            AND cliEmail = "'.$mail.'"' ;
            
    
            
    bgl_boucle_aff($bd, $sql, $mail);

    mysqli_close($bd);
}

/**
 * Fonction affichage des livres
 * 
 * @param $bd : base de donnée associée
 * @param $sql : requete sql à envoyer
 * @param $mail : mail recherché par l'utilisateur (si vide, c'est que l'utilisateur ne veut pas faire la recherche)
 *
 */





function bgl_boucle_aff($bd, $sql, $mail=''){

    if ($mail != ''){
        echo '<p><strong> Voici la liste de voeux de ', $mail, ' : </strong></p>';
    }

    $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);

    $lastID = -1;
    while ($t = mysqli_fetch_assoc($res)) {            
                    
        if ($t['liID'] != $lastID) {
            if ($lastID != -1) {
                if ($mail != ''){
                    bg_afficher_livre($livre, 'bcResultat', '../');
                }else{
                    bg_afficher_livre($livre, 'bcliste', '../');	
                }
            }
            $lastID = $t['liID'];
            $livre = array( 'id' => strip_tags($t['liID']), 
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
        if ($mail != ''){
                    bg_afficher_livre($livre, 'bcResultat', '../');
                }else{
                    bg_afficher_livre($livre, 'bcliste', '../');	
                }	
    }
    else{
        if ($mail==''){
            echo '<p> Votre liste de voeux est vide ! </p>';
        } 
        else {
            echo '<p>La liste de ', $mail, ' est vide ! </p>';
        }
    }
    mysqli_free_result($res);
}






?>
