<?php

$errorCo = 0;
$errorInscription = 0;

/*
 * FONCTION co
 * @param : $err (0 -> pas d'erreur)
 *
 * Affiche le formulaire de connexion
 */
function co($err){

    echo
    '<table class="connexion_table">',
      '<tr>',
        '<th id="connexion_co_title"><div class=connexion_encadre><h2>Connexion</h2></div></th>';

    if ($err != 0){
        echo '<p class="error">Echec de l\'authentification</p>';
    }
    echo
        '<th id="connexion_inscription_title"><div class=connexion_encadre><h2>Inscription</h2></div></th>',
          '</tr>',
          '<tr>',
            '<td id="connexion_co_text">',
            '<div class=connexion_encadre><form method="post" action="connexion.php">',
            '<label for="login">Login : </label>',
                '<input type="text" name="login"><br>',
            '<label for="pass">Mot de passe : </label>',
                '<input type="password" name="pass"><br>',
                '<input type="submit" name="btnConnecter" value="Se connecter">',
            '</form></div></td>',
            '<td id="connexion_inscription_text">';
    inscription();
    echo
            '</td>',
          '</tr>',
        '</table>';

}

/*
 * FONCTION test_co()
 * Vérification des données entrées par l'utilisateur et vérification dans la BD
 *
 */
function test_co(){

    $bd = bd_connect();
    $login = mysqli_real_escape_string($bd, trim($_POST['login']));
    $pass = mysqli_real_escape_string($bd, trim(md5($_POST['pass'])));

    //ON REGARDE SI C'EST UN CLIENT
    $sql = 'SELECT cliID FROM client WHERE cliLogin = \''.$login.'\' AND cliMDP = \''.$pass.'\';';
    $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

    //Test si il existe bien un client
    if (mysqli_num_rows($res) != 1){
        mysqli_free_result($res);
    } else { //Si c'est bien un client, alors on peut s'arreter et ne pas faire la requete pour medecin
        $t = mysqli_fetch_assoc($res);
        $id = $t['cliID'];
        $_SESSION['patID'] = $id;
        mysqli_free_result($res);
        redirect('../index.php');
    }

    //ON REGARDE SI C'EST UN MEDECIN
    $sql = 'SELECT medID FROM medecin WHERE medLogin = \''.$login.'\' AND medMDP = \''.$pass.'\';';
    $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

    if (mysqli_num_rows($res) != 1){
        mysqli_free_result($res);
        mysqli_close($bd);
        return -1;
    } else {
        $t = mysqli_fetch_assoc($res);
        $id = $t['medID'];
        $_SESSION['docID'] = $id;
        mysqli_free_result($res);
        redirect('../index.php');
    }
}

/*
 * FONCTION ancienne_valeur_input
 * @param : $index : index dans le tableau
 * Cette fonction retourne l'ancienne valeur de l'input qui a pour nom index si elle exite et vide sinon
 */
function ancienne_valeur_input($index){
    return (isset($_POST[$index]))? $_POST[$index] : "";
}

/*
 * FONCTION inscription
 * Affiche le formulaire d'inscription pour les patients (pour les médecins, c'est dans inscription_medecin.php)
 *
 *
 */
function inscription(){

    $bd = bd_connect();
    echo
    '<a title="Créer un profil médecin" href="./inscription_medecin.php"><div id=connexion_encadre_medecin><p> Pour un médecin l\'inscription se fait ici.</p></div></a>';

    //Si le patient s'est trompé dans un champ, on récupère les autres données entrées pour les réaficher
    $loginE = ancienne_valeur_input('login');
    $nomE = ancienne_valeur_input('nom');
    $prenomE = ancienne_valeur_input('prenom');
    $emailE = ancienne_valeur_input('mail');
    $telE = ancienne_valeur_input('tel');
    $numRueE = ancienne_valeur_input('numRue');
    $addrE = ancienne_valeur_input('addr');
    $cpltE = ancienne_valeur_input('cplt');
    $cpE = ancienne_valeur_input('cp');
    $villeE = ancienne_valeur_input('ville');
    $paysE = ancienne_valeur_input('pays');
    $numsecuE = ancienne_valeur_input('numSecu');

    echo
        '<div class=connexion_encadre>',
        '<form action="connexion.php" method="post">',
            '<label for="login">Login : </label>',
                '<input type="text" name="login" value="',$loginE,'"><br>',
            '<label for="pass1">Mot de passe : </label>',
                '<input type="password" name="pass1"><br>',
            '<label for="pass2">Confirmer le mot de passe : </label>',
                '<input type="password" name="pass2"><br>',
            '<div class="geodoc_trait"></div>',
            '<label for="nom">Nom : </label>',
                '<input type="text" name="nom" value="',$nomE,'"><br>',
            '<label for="prenom">Prénom : </label>',
                '<input type="text" name="prenom" value="',$prenomE,'"><br>',
            '<label for="mail">Email : </label>',
                '<input type="text" name="mail" value="',$emailE,'"><br>',
            '<label for="tel">Téléphone : </label>',
                '<input type="text" name="tel" value="',$telE,'"><br>',
            '<div class="geodoc_trait"></div>',
            '<h4>Adresse</h4>',
            '<label for="numRue">Numéro de rue: </label>',
                '<input type="text" name="numRue" value="',$numRueE,'"><br>',
            '<label for="addr">Rue : </label>',
                '<input type="text" name="addr" value="',$addrE,'"><br>',
            '<label for="cplt">Complément (bis, A, ...) : </label>',
                '<input type="text" name="cplt" value="',$cpltE,'"><br>',
            '<label for="cp">Code postal : </label>',
                '<input type="text" name="cp" value="',$cpE,'"><br>',
            '<label for="ville">Ville : </label>',
                '<input type="text" name="ville" value="',$villeE,'"><br>',
            '<label for="pays">Pays : </label>',
                '<input type="text" name="pays" value="',$paysE,'"><br>',
            '<div class="geodoc_trait"></div>',
            '<label for="numSecu">Numéro de sécurité sociale : </label>',
                '<input type="text" name="numSecu" value="',$numsecuE,'"><br>',

            '<input type="submit" name="btnInscription" value="Inscription">',
        '</form>',
        '</div></div>';
}

/*
 * FONCTION verif_inscription();
 * Vérifie les données saisies par l'utilisateur puis l'ajoute dans la BD
 *
 */
function verif_inscription(){

    $errorInscription = array();

    $bd = bd_connect();

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $numRue = trim($_POST['numRue']);
    $addr = addslashes(trim($_POST['addr']));
    $cplt = trim($_POST['cplt']);
    $cp = trim($_POST['cp']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);
    $login = trim($_POST['login']);
    $pass1 = trim($_POST['pass1']);
    $pass2 = trim($_POST['pass2']);
    $numsecu = trim($_POST['numSecu']);

    //VERIFICATION Nom
    $noTags = strip_tags($nom);
    if ($noTags != $nom){
        $errorInscription['nom'] = 'Le nom ne peut pas contenir de code HTML';
    } else if (empty($nom)){
        $errorInscription['nom'] = 'Le nom doit être renseigné';
    } else if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,99}$", $nom)){
        $errorInscription['nom'] = 'Le nom n\'est pas valide';
    }

    //VERIFICATION Prenom
    $noTags = strip_tags($prenom);
    if ($noTags != $prenom){
        $errorInscription['prenom'] = 'Le prénom ne peut pas contenir de code HTML';
    } else if (empty($prenom)){
        $errorInscription['prenom'] = 'Le prénom doit être renseigné';
    } else if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,99}$", $prenom)){
        $errorInscription['prenom'] = 'Le prénom n\'est pas valide';
    }

    //VERIFICATION EMAIL
    $noTags = strip_tags($mail);
    if ($noTags != $mail){
        $errorInscription['email'] = 'L\'email ne peut pas contenir de code HTML';
    } else {
        $a = mb_strpos($mail, '@', 0, 'UTF-8');
        $point = mb_strpos($mail, '.', 0, 'UTF-8');
        if ($a === false || $point === false){
            $errorInscription['email'] = 'L\'email ne correspond pas au bon format';
        }
    }

    //VERIFICATION Tel
    if (!preg_match('#^[0-9]{9,10}$#',$tel)){
        $errorInscription['tel'] = 'Le numéro de téléphone doit être composé de chiffres';
    } else if (empty($tel)) {
        $errorInscription['tel'] = 'Le numéro de téléphone doit être renseigné';
    } else if (!preg_match('#^[0]#',$tel)) {
        $error['tel'] = 'Le numéro de téléphone doit commencer par 0';
    }

    //VERIFICATION login
    $noTags = strip_tags($login);
    $sql = 'SELECT cliID FROM client WHERE cliLogin = \''.$login.'\';';
    $res = mysqli_query($bd, $sql) ;//or bd_error($bd, $sql);

    if (empty($login)){
        $errorInscription['login'] = 'Le login doit être renseigné';
    } else if ($noTags !== $login){
        $errorInscription['login'] = 'Le login ne peut contenir de code HTML';
    } else if (mysqli_num_rows($res) != 0){
        $errorInscription['login'] = 'Le login est déjà utilisé, veuillez en choisir un autre';
    }

    //VERIFICATION mdp
    if (!empty($pass1) || !empty($pass2)){
        if ($pass1 != $pass2){
            $errorInscription['pass'] = 'Les mots de passe doivent être identiques';
        } else if (empty($pass1)){
            $errorInscription['pass'] = 'Le mot de passe doit être renseigné dans le premier champs';
        } else if (empty($pass2)){
            $errorInscription['pass'] = 'Vous n\'avez pas confirmé votre mot de passe';
        } else {
            $size = mb_strlen($pass1, 'UTF-8');
            $noTags = strip_tags($pass1);
            if (mb_strlen($noTags, 'UTF-8') != $size){
                $errorInscription['pass'] = 'Le mot de passe ne peut pas contenir de code HTML';
            } else if ($size < 4 || $size > 20){
                $errorInscription['pass'] = 'Le mot de passe doit être constitué de 4 à 20 caractères';
            }
        }
    } else {
        $errorInscription['pass'] = 'Les mots de passe doivent être rensignés';
    }

    $pass1 = md5($pass1);

    //VERIFICATION numRue
    if (!preg_match('#^[0-9]{1,10}$#', $numRue)){
        $errorInscription['numRue'] = 'Le numéro de rue doit être un nombre';
    } else if (empty($numRue)){
        $errorInscription['numRue'] = 'Le numéro de rue doit être renseigné';
    }

    //VERIFICATION addr
    if (mb_regex_encoding ('UTF-8') && strlen($addr)>100) {
        $errorInscription['addr'] = 'L\'adresse n\'est pas valide';
    } else if (empty($addr)){
        $errorInscription['addr'] = 'L\'adresse doit être renseignée';
    }

    //VERIFICATION cplt
    if (mb_regex_encoding ('UTF-8') && strlen($cplt)>100) {
        $errorInscription['addr'] = 'L\'adresse n\'est pas valide';
    }

    //VERIFICATION CP
    if (!preg_match('#^[0-9]{5}$#', $cp)){
        $errorInscription['cp'] = 'Le code postal doit être composé de 5 chiffres';
    } else if (empty($cp)){
        $errorInscription['cp'] = 'Le code postal doit être renseigné';
    }

    //VERIFICATION ville
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $ville)) {
        $errorInscription['ville'] = 'La ville n\'est pas valide.';
    } else if (empty($ville)){
        $errorInscription['ville'] = 'La ville doit être renseignée';
    }

	//VERIFICATION pays
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $pays)) {
        $errorInscription['pays'] = 'Le pays n\'est pas valide.';
    } else if (empty($pays)){
        $errorInscription['pays'] = 'Le pays doit être renseigné';
    }


    //VERIFICATION numSecu
    if (!preg_match('#^[0-9]{15}$#', $numsecu)){
        $errorInscription['numSecu'] = 'Le numéro de sécurité sociale doit être composé de 15 chiffres';
    } else if (empty($numsecu)){
        $errorInscription['numSecu'] = 'Le numéro de sécurité sociale doit être renseigné';
    }

    //PAS D'ERREUR
    if (count($errorInscription) <= 0){

        //VÉRIFICATION QUE LA LOCALITE EXISTE DEJA
        $sql = 'SELECT localiteID AS locID FROM localite WHERE locNumero = '.$numRue.' AND locRue = \''.$addr.'\' AND locComplement = \''.$cplt.'\' AND locCP = '.$cp.' AND locVille = \''.$ville.' \' AND locPays = \''.$pays.'\';';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

        if (mysqli_num_rows($res) != 0){//la localité existe -> récupération de son id
            $t = mysqli_fetch_assoc($res);
            $last_id = $t['locID'];
        } else {//la localité n'existe pas : on va l'ajouter dans la bd, puis récupérer son id

            $sql = 'INSERT INTO localite(locNumero, locRue, locComplement, locCP, locVille, locPays) VALUES ('.$numRue.', \''.$addr.'\', \''.$cplt.'\', '.$cp.', \''.$ville.'\', \''.$pays.'\');';
            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

            $sql = 'SELECT MAX(localiteID) AS locID FROM localite;';
            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
            $t = mysqli_fetch_assoc($res);
            $last_id = $t['locID'];
        }

        //Ajout du client
        $sql = 'INSERT INTO client(cliLogin, cliMDP, cliMail, cliNom, cliPrenom, cliTel, cliNumSecu, cliLocID) VALUES (\''.$login.'\', \''.$pass1.'\', \''.$mail.'\', \''.$nom.'\', \''.$prenom.'\', '.$tel.', '.$numsecu.', '.$last_id.');';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

        //Recuperation de l'id du client que l'on viens de créer pour le mettre dans la variable de session
        $sql = 'SELECT MAX(cliID) AS cliID FROM client;';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        $t = mysqli_fetch_assoc($res);
        $last_id_client = $t['cliID'];

        $_SESSION['patID'] = $last_id_client;
        redirect('../index.php');

    } else { //Afichage des erreurs
        display_errors($errorInscription);
    }
}

function control_piratage(){
    (!isset($_POST['btnConnecter']) || $_POST['btnConnecter'] != 'Se connecter' || !isset($_POST['login']) || !isset($_POST['pass'])/* || strip_tags($_POST['source']) != $_POST['source']*/) && exit_session();
}

/*
 *
 * MAIN
 *
 */

//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

//($_POST) && control_piratage(); //verif
//tableau contenant les erreurs de l'utilisateur
$err = array();

//Si utilisateur déjà authentifié, on le redirige sur la page appelante, ou index par défaut
if (isset($_SESSION['docID']) && isset($_SESSION['patID'])){
    $page = '../index.php';
    if (isset($_SERVER['HTTP_REFERER'])){
        $page = $_SERVER['HTTP_REFERER'];
        $n_page = url_get_nom_fichier($page);
        if ($n_page == 'connexion.php'){
            $page = '../index.php';
        }
    }
    redirect($page);
}

begin_html('Géo\'doc | Connexion', '../css/geodoc.css');
display_header();

//fonctions de vérification des formulaires
$err_co = (isset($_POST['btnConnecter'])) ? test_co() : 0;
$err_ins = (isset($_POST['btnInscription'])) ? verif_inscription() : array();

//affichage du formulaire de connexion
co($err_co);

display_footer();
end_html();

ob_end_flush();

?>
