<?php

ob_start('ob_gzhandler');
session_start();

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

$id = bgl_control_get();

bgl_delete_session($id);

header('Location: ./panier.php');
exit();


/**
 * Fonction qui controle la méthode get passée dans l'url
 *
 *  @global array $_GET
 *
 * @return $id : id du livre supprimer de la liste
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
 * Fonction qui se charge de supprimer le livre qui correspond à $id dans la variable de session, et si il n'y a plus aucun livre alors suppression totale
 *
 * @param $id : id du livre à supprimer du panier
 * @global array $_SESSION
 *
 */
 
function bgl_delete_session($id){
    $count = count($_SESSION['Panier']);
    for ($i = 0; $i<$count; $i++){
        if ($_SESSION['Panier'][$i] == $id){
        
            if ($count == 1){
                unset($_SESSION['Panier'][$i]);
                unset($_SESSION['Panier']);
            }
            
            if ($count > 1){
                for ($j = $i+1; $j < $count; $j++){
            
                $_SESSION['Panier'][$i] = $_SESSION['Panier'][$j];
                }
                unset($_SESSION['Panier'][$j-1]);
            }
            
            break;
        }
    }

}

?>