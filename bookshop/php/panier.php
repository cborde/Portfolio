<?php
ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

bg_html_debut('BookShop | Panier', '../styles/bookshop.css');

bg_bookshop_enseigne_entete(isset($_SESSION['cliID']),'../');

bg_contenu();

bg_bookshop_pied();

bg_html_fin();

ob_end_flush();

/**
 * Fonction contenu de la page
 *
 * @global array $_SESSION
 */

function bg_contenu(){

    echo '<h1> Panier </h1>';

    if (isset($_SESSION['cliID'])){
    
        $nom = 'id';
        
        
        
        if (!isset($_SESSION['Panier'])){
            $count = 0;
            echo '<p> Votre panier est vide ! </p>';
                return;
        }else{
            $count = count($_SESSION['Panier']);
        }
        
    } else {
        
        echo '<p> Connectez-vous pour profiter de cette fonctionnalité </p>',
        '<a id="lien_connect" href="./login.php" title="Se Connecter">',bg_form_input(bg_Z_SUBMIT,'btnLogin', 'Se connecter'),'</a>';
        exit();
    }
    
    $bd = bg_bd_connect();
    
    $prix = 0;
    
    $fin_sql = '';
    $nblivre = count($_SESSION['Panier']);
    foreach($_SESSION['Panier'] as $cle => $i){
        $fin_sql .= intval($i). ",";
        if($cle == $nblivre-1){
            $fin_sql .= intval($i);
        }
    
    }
    
    $sql = 'SELECT liID, liTitre, liPrix
            FROM livres
            WHERE liID in ('.$fin_sql.')';
            
    $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
    $id=0;
    while ($t = mysqli_fetch_assoc($res)) {
			$id++;
            $livre[$id] = array( 'id' => strip_tags($t['liID']), 
                            'titre' => strip_tags($t['liTitre']),
                            'quantite' => bgl_nb_occ($_SESSION['Panier'], $t['liID']), //on compte le nombre de fois qu'apparait l'id du livre dans la variable de session
                            'prix' => strip_tags($t['liPrix']));
            $prix += $t['liPrix'];
    }

    
    if (isset($_POST['btnCommande'])){
    
        $sql = 'SELECT cliAdresse, cliCP, cliVille, cliPays FROM clients WHERE cliID = '. $_SESSION['cliID'];
        
        $res = mysqli_query($bd, $sql);
        
        $infos = mysqli_fetch_assoc($res);
        
        if ($infos['cliAdresse'] == "" || $infos['cliCP'] == 0  || $infos['cliVille'] == "" || $infos['cliPays'] == ""){
            echo '<p> Vous devez enregistrer vos informations de livraison ! </p>';
            
        }else{
    
    
    
    
    
    
            $sql = 'SELECT coID FROM commandes ORDER BY coID DESC LIMIT 0,1';
            $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql); 
            
            date_default_timezone_set('Europe/Paris');
            
            $coid = mysqli_fetch_assoc($res);
            $coid = $coid['coID'] + 1;
            $date = gmdate('Ymd');
            $heure = gmdate('Hi');
            
            $sql1 = 'INSERT INTO commandes (coID, coIDClient, coDate, coHeure)
            VALUES ('. $coid. ',' . $_SESSION['cliID']. ',' . $date . ',' . $heure.')';
            
            $res = mysqli_query($bd, $sql1) or bg_bd_erreur($bd,$sql1);

            $sql2 = 'INSERT INTO compo_commande (ccIDCommande, ccIDLivre, ccQuantite) VALUES';
            
            $nb_livre = count($livre);
            $compteur = 0;
            foreach ($livre as $cle => $l){
            
                if($compteur == $nb_livre-1){
                    $fin_sql = '('. $coid. ',' . $l['id']. ','. $l['quantite'].')';
                } else {
                    $fin_sql = '('. $coid. ',' . $l['id']. ','. $l['quantite'].'),';
                }
                $sql2.=$fin_sql;
                $compteur++;
            }
            
            $res = mysqli_query($bd, $sql2) or bg_bd_erreur($bd,$sql2);
            
            unset($_SESSION['Panier']);
            echo '<p> Votre commande a bien été validée ! Merci pour vos achats ! </p>';
        }
        
        
        
    }else{
            bg_afficher_panier($livre);
            echo '<center><form method="post" action="./panier.php">', bg_form_input(bg_Z_SUBMIT,'btnCommande', 'Commander !'),'</form></center>';
        }
    
    mysqli_close($bd);
            
        



}

/**
 * Fonction qui compte le nombre d'occurence dans un tableau
 *
 * @param $a : tableau
 * @param $id : valeur recherchée dans le tableau et celle qu'on veut compter
 *
 * @return $count : nombre d'occurence de la valeur
 */

function bgl_nb_occ($a, $id){

    $count = 0;
    foreach($a as $t){
        if ($t == $id){
            $count++;
        }
    }
    return $count;

}


?>