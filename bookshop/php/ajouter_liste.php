<?php

ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

//vérification de méthode get de l'url
$id = bgl_control_get();

if (isset($_SESSION['cliID'])){
    //execute la requete seulement si le client est connecté
    exec_requete($id);
}

header('Location: ./liste.php');
exit();

/**
 * Fonction qui controle la méthode get passée dans l'url
 *
 *  @global array $_GET
 *
 * @return $id : id du livre à ajouter dans la liste
 */

function bgl_control_get (){
	(count($_GET) != 1) && bg_exit_session();
	(! isset($_GET['id'])) && bg_exit_session();

        $id = trim($_GET['id']);
        $notags = strip_tags($id);
        (mb_strlen($notags, 'UTF-8') != mb_strlen($id, 'UTF-8')) && bg_exit_session();
    
	return $id;
}

/**
 * Fonction qui exécute la requete pour ajouter l'article dans la liste du client
 *
 * @param $id : id du livre à ajouter dans la liste
 *
 */
 

function exec_requete($id){

    $bd = bg_bd_connect();
    
    $sql = 'SELECT liID
            FROM livres, listes
            WHERE listIDLivre = liID
            AND listIDClient = "'.$_SESSION['cliID'].'"';
            
    $estPresent = false;
            
    $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd, $sql);
    while ($t = mysqli_fetch_assoc($res)){
        if ($t['liID'] == $id){
            $estPresent = true;
            break;
        }
    }
    
    if (!$estPresent){
        $sql = 'INSERT INTO listes (listIDClient, listIDLivre)
                VALUES ('. $_SESSION['cliID'].', '.$id.')';
                
        $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
    }
    
    mysqli_close($bd);

}

?>