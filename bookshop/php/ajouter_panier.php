<?php

ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

$id = bgl_control_get();

if (isset($_SESSION['cliID'])){
    bgl_exec_session($id);
}

header('Location: ./panier.php');
exit();

/**
 * Fonction qui controle la méthode get passée dans l'url
 *
 *  @global array $_GET
 *
 * @return $id : id du livre à ajouter dans le panier
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
 * Fonction qui ajoute l'article dans le panier du client (dans la variable de session)
 *
 * @global array $_SESSION
 *
 */
 
function bgl_exec_session($id){
    if (!isset($_SESSION['Panier'])){
        $count = 0;
    } else {
        $count = count($_SESSION['Panier']);
    }
    
    if ($count != 0){
        for ($i = 0; $i < $count; $i++){
            if ($_SESSION['Panier'][$i] == $id){
                
            }
        }
    }
    
    $bd = bg_bd_connect();
    
    $sql = 'DELETE FROM listes
            WHERE listIDLivre ='.$id.'
            AND listIDClient = '.$_SESSION['cliID'];
            
    $res = mysqli_query($bd, $sql) or bg_bd_erreur($bd,$sql);
    
    mysqli_close($bd);

    $_SESSION['Panier'][$count] = $id;
}



?>