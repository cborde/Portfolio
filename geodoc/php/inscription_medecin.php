<?php

/*
 * FONCTION ancienne_valeur_input
 * @param : $index : index dans le tableau
 * Cette fonction retourne l'ancienne valeur de l'input qui a pour nom index si elle exite et vide sinon
 */
function ancienne_valeur_input($index){
    return (isset($_POST[$index]))? $_POST[$index] : "";
}

/*
 * FONCTION inscription_medecin
 * Affiche le formulaire d'inscription pour les medecins
 *
 *
 */
function inscription_medecin($err){

    $bd = bd_connect();
    echo
    '<h3> Inscription Médecin</h3>';

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
    $numRPPSE = ancienne_valeur_input('numRPPS');
    $dureeConsE = ancienne_valeur_input('durCons');

    echo
        '<div id="inscription_medecin_details">',
        '<form action="inscription_medecin.php" method="post">',
            '<div id="inscription_medecin_base">',
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
            '<div class="geodoc_trait"></div>',
            '<h4>Adresse du cabinet</h4>',
            '<label for="tel">Téléphone : </label>',
                '<input type="text" name="tel" value="',$telE,'"><br>',
            '<label for="numRue">Numéro : </label>',
                '<input type="text" name="numRue" value="',$numRueE,'"><br>',
            '<label for="addr">Rue : </label>',
                '<input type="text" name="addr" value="',$addrE,'"><br>',
            '<label for="cplt">Complément : </label>',
                '<input type="text" name="cplt" value="',$cpltE,'"><br>',
            '<label for="cp">Code postal : </label>',
                '<input type="text" name="cp" value="',$cpE,'"><br>',
            '<label for="ville">Ville : </label>',
                '<input type="text" name="ville" value="',$villeE,'"><br>',
            '<label for="pays">Pays : </label>',
                '<input type="text" name="pays" value="',$paysE,'"><br>',
            '<div class="geodoc_trait"></div>',
            '</div>',

            '<div id="inscription_medecin_med">',
            '<label for="numRPPS">Numéro RPPS : </label>',
                '<input type="text" name="numRPPS" value="',$numRPPSE,'"><br>',

            '<label for="specialite">Spécialité : </label>',
				'<select id="specialite" name="specialite" size=1>';

                    //Affichage des spécilitées
					$sql = 'SELECT speNom FROM specialite;';
					$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

					while ($t = mysqli_fetch_assoc($res)) {
						echo '<option value="', mysqli_real_escape_string($bd, $t['speNom']),'">', mysqli_real_escape_string($bd, $t['speNom']), '</option>';
					}

            echo
                '</select><br>',

            '<label for="durCons">Durée consultation (en minutes) : </label>',
                '<input type="text" name="durCons" value="',$dureeConsE,'"><br>',
                '<div class="geodoc_trait"></div>',
                '<input type="radio" name="accCB" value="true" checked>Accepte la Carte Bancaire <br>',
                '<input type="radio" name="accCB" value="false">N\'accepte pas la Carte Bancaire <br><br>',
                '<input type="radio" name="accTP" value="true" checked>Accepte le Tiers Payant <br>',
                '<input type="radio" name="accTP" value="false">N\'accepte pas le Tiers Payant <br>',
            '</div>',
            '<input type="submit" name="btnInscription" value="Inscription">',
        '</form>',
        '</div>';
}

/*
 * FONCTION verif_inscription_medecin();
 * Vérifie les données saisies par l'utilisateur puis l'ajoute dans la BD
 *
 */
function verif_inscription_medecin(){

    $error = array();

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

    $specialite = trim($_POST['specialite']);
    $dureeCons = trim($_POST['durCons']);
    $numRPPS = trim($_POST['numRPPS']);


    //VERIFICATION Nom
    $noTags = strip_tags($nom);
    if ($noTags != $nom){
        $error['nom'] = 'Le nom ne peut pas contenir de code HTML';
    } else if (empty($nom)){
        $error['nom'] = 'Le nom doit être renseigné';
    } else if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,99}$", $nom)){
        $error['nom'] = 'Le nom n\'est pas valide';
    }

    //VERIFICATION Prenom
    $noTags = strip_tags($prenom);
    if ($noTags != $prenom){
        $error['prenom'] = 'Le prénom ne peut pas contenir de code HTML';
    } else if (empty($prenom)){
        $error['prenom'] = 'Le prénom doit être renseigné';
    } else if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,99}$", $prenom)){
        $error['prenom'] = 'Le prénom n\'est pas valide';
    }

    //VERIFICATION EMAIL
    $noTags = strip_tags($mail);
    if ($noTags != $mail){
        $error['email'] = 'L\'email ne peut pas contenir de code HTML';
    } else {
        $a = mb_strpos($mail, '@', 0, 'UTF-8');
        $point = mb_strpos($mail, '.', 0, 'UTF-8');
        if ($a === false || $point === false){
            $error['email'] = 'L\'email ne correspond pas au bon format';
        }
    }

    //VERIFICATION Tel
    if (!preg_match('#^[0-9]{9,10}$#',$tel)){
        $error['tel'] = 'Le numéro de téléphone doit être composé de chiffres';
    } else if (empty($tel)) {
        $error['tel'] = 'Le numéro de téléphone doit être renseigné';
    } else if (!preg_match('#^[0]#',$tel)) {
        $error['tel'] = 'Le numéro de téléphone doit commencer par 0';
    }

    //VERIFICATION login
    $noTags = strip_tags($login);
    $sql = 'SELECT cliID FROM client WHERE cliLogin = \''.$login.'\';';
    $res = mysqli_query($bd, $sql) ;//or bd_error($bd, $sql);

    if (empty($login)){
        $error['login'] = 'Le login doit être renseigné';
    } else if ($noTags !== $login){
        $error['login'] = 'Le login ne peut contenir de code HTML';
    } else if (mysqli_num_rows($res) != 0){
        $error['login'] = 'Le login est déjà utilisé, veuillez en choisir un autre';
    }

    //VERIFICATION mdp
    if (!empty($pass1) || !empty($pass2)){
        if ($pass1 != $pass2){
            $error['pass'] = 'Les mots de passe doivent être identiques';
        } else if (empty($pass1)){
            $error['pass'] = 'Le mot de passe doit être renseigné dans le premier champs';
        } else if (empty($pass2)){
            $error['pass'] = 'Vous n\'avez pas confirmé votre mot de passe';
        } else {
            $size = mb_strlen($pass1, 'UTF-8');
            $noTags = strip_tags($pass1);
            if (mb_strlen($noTags, 'UTF-8') != $size){
                $error['pass'] = 'Le mot de passe ne peut pas contenir de code HTML';
            } else if ($size < 4 || $size > 20){
                $error['pass'] = 'Le mot de passe doit être constitué de 4 à 20 caractères';
            }
        }
    } else {
        $error['pass'] = 'Les mots de passe doivent être rensignés';
    }

    $pass1 = md5($pass1);

    //VERIFICATION numRue
    if (!preg_match('#^[0-9]{1,10}$#', $numRue)){
        $error['numRue'] = 'Le numéro de rue doit être un nombre';
    } else if (empty($numRue)){
        $error['numRue'] = 'Le numéro de rue doit être renseigné';
    }

    //VERIFICATION addr
    if (mb_regex_encoding ('UTF-8') && strlen($addr)>100) {
        $error['addr'] = 'L\'adresse n\'est pas valide';
    } else if (empty($addr)){
        $error['addr'] = 'L\'adresse doit être renseignée';
    }

    //VERIFICATION cplt
    if (mb_regex_encoding ('UTF-8') && strlen($cplt)>100) {
        $error['addr'] = 'L\'adresse n\'est pas valide';
    }

    //VERIFICATION CP
    if (!preg_match('#^[0-9]{5}$#', $cp)){
        $error['cp'] = 'Le code postal doit être composé de 5 chiffres';
    } else if (empty($cp)){
        $error['cp'] = 'Le code postal doit être renseigné';
    }

    //VERIFICATION ville
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $ville)) {
        $error['ville'] = 'La ville n\'est pas valide.';
    } else if (empty($ville)){
        $error['ville'] = 'La ville doit être renseignée';
    }

	//VERIFICATION pays
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $pays)) {
        $error['pays'] = 'Le pays n\'est pas valide.';
    } else if (empty($pays)){
        $error['pays'] = 'Le pays doit être renseigné';
    }

    //VERIFICATION numRPPS
    if (!preg_match('#^[0-9]{9}$#', $numRPPS)){
        $error['numRPPS'] = 'Le numéro RPPS doit être composé de 9 chiffres';
    } else if (empty($numRPPS)){
        $error['numRPPS'] = 'Le numéro RPPS doit être renseigné';
    }

    //VERIFICATION duree consultation
    if (!preg_match('#^[0-9]{1,2}$#', $dureeCons)){
        $error['durCons'] = 'La durée de consultation doit être un nombre';
    } else {
        //Dans la BD, la durée de consultation doit être stockée sous forme hh:mm:ss
        //Or dans le formulaire, le médecin doit choir la durée de consultation en minutes
        //Donc on fait une conversion
        if ($dureeCons < 60){ // 40 -> 00:40:00
            $duree_consultation = '00:'.$dureeCons.':00';
        } else { // 80 -> 01:20:00
            $nb_heure = intval($dureeCons / 60);
            if ($nb_heure < 10){
                $nb_heureF = '0'.$nb_heure;
            }
            $duree_consultation = $nb_heureF.':'.intval($dureeCons-$nb_heure*60).':00';
        }
    }

    //VERIFICATION specialite selectionnee
    if (empty($specialite)){
        $error['spe'] = 'La spécialité doit être renseignée';
    }

    //VERIFICATION carteBancaire selectionnee
    if (empty($_POST['accCB'])){
        $error['cb'] = 'Vous devez indiquer si vous acceptez la carte bancaire';
    } else {
        $cb = $_POST['accCB'];
    }

    //VERIFICATION tiersPayant selectionne
    if (empty($_POST['accTP'])){
        $error['tp'] = 'Vous devez indiquer si vous acceptez le tiers payant';
    } else {
        $tp = $_POST['accTP'];
    }

    if (count($error) <= 0){ //Pas d'erreur

        //VÉRIFICATION QUE LA LOCALITE EXISTE DEJA
        $sql = 'SELECT localiteID AS locID FROM localite WHERE locNumero = '.$numRue.' AND locRue = \''.$addr.'\' AND locComplement = \''.$cplt.'\' AND locCP = '.$cp.' AND locVille = \''.$ville.' \' AND locPays = \''.$pays.'\';';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

        if (mysqli_num_rows($res) != 0){//la localité existe
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

        //RECUPERATION DE L ID DE LA specialite
        $sql = 'SELECT speID FROM specialite WHERE speNom = \''.$specialite.'\';';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        $t = mysqli_fetch_assoc($res);
        $id_spe = $t['speID'];

        //Ajout du medecin

        $sql = 'INSERT INTO medecin(medNom, medPrenom, medMail, medTel, medLogin, medMDP, medNumRPPS, medSpecialiteID, medAccepteCB, medAccepteTiersPayant, medDureeConsultation, medLocID) VALUES (\''.$nom.'\', \''.$prenom.'\', \''.$mail.'\', '.$tel.', \''.$login.'\', \''.$pass1.'\', '.$numRPPS.', '.$id_spe.', '.$cb.', '.$tp.', \''.$duree_consultation.'\', '.$last_id.');';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

        //Recuperation de l'id du medecin que l'on viens de créer pour le mettre dans la variable de session
        $sql = 'SELECT MAX(medID) AS medID FROM medecin;';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        $t = mysqli_fetch_assoc($res);
        $last_id_medecin = $t['medID'];

        $_SESSION['docID'] = $last_id_medecin;
        redirect('./planning.php');


    } else { //Affichage des erreurs
        display_errors($error);
    }
}

/*
 * MAIN
 */

//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

begin_html('Géo\'doc | Inscription', '../css/geodoc.css');
display_header();

$err_ins = (isset($_POST['btnInscription'])) ? verif_inscription_medecin() : array();
inscription_medecin($err_ins);

display_footer();
end_html();

ob_end_flush();

?>
