<?php

    $error = array();

/**
 * FUNCTION profil
 *
 * Affichage du formulaire contanant les information sur la personne connectée
 * Possibilité de modifier ces informations
 *
 * @param: boolean $patient: variable qui permet de savoir si la personne connectée est un patient ou un médecin
 * Les médecins ont des informations en plus que les patients
 *
 * @param: $bd: base de données associée
 *
 * @param: $id: l'id de la personne actuellement connectée
 *
 * @param: $error array : tableau contenant les erreurs que le client aurait pu commettre en remplissant le formulaire
 *
 */

function profil($patient, $bd, $id, $error){

    if ($patient){
        $sql = 'SELECT cliNom AS Nom, cliPrenom AS Prenom, cliMail AS Mail, cliTel AS Tel, locNumero, locRue, locComplement, locCP, locVille, locPays FROM client, localite WHERE cliID ='.$id.' AND localiteID = cliLocID;';
    } else {
        $sql = 'SELECT medNom AS Nom, medPrenom AS Prenom, medMail AS Mail, medTel AS Tel, medDureeConsultation, medAccepteCB, medAccepteTiersPayant, locNumero, locRue, locComplement, locCP, locVille, locPays FROM medecin, localite WHERE medID ='.$id.' AND localiteID = medLocID;';
    }

    $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

    $t = mysqli_fetch_assoc($res);

    echo
    '<form method="post" action="profil.php">',
        '<h3>Mon profil</h3>',
        '<table id="profil_table"><tr>',
            '<td><div id="profil_mdp">',
                '<h4>Changer votre mot de passe</h4>',
                '<label for="mdp1">Nouveau mot de passe : </label>',
                    '<input type="password" name="mdp1"><br>',
                '<label for="mdp2">Répétez le mot de passe : </label>',
                    '<input type="password" name="mdp2"><br>',
            '</div>',

            '<div id="profil_suppr">',
                '<form method="post" action="profil.php">',
                    '<h4>Suppression du compte</h4>',
                    '<label for="pass_supp">Mot de passe actuel pour supprimer votre compte : </label>',
                        '<input type="password" name="pass_supp"><br>',
                        '<input type="submit" name="btnSupprimer" value="Supprimer">',
                '</form>',
            '</div></td>',

            '<td><div id="profil_patient">',
                '<h4>Mes informations personnelles</h4>',
                '<label for="nom">Nom : </label>',
                    '<input type="text" name="nom" value="'.$t['Nom'].'"><br>',
                '<label for="prenom">Prénom : </label>',
                    '<input type="text" name="prenom" value="'.$t['Prenom'].'"><br>',
                '<label for="mail">E-mail : </label>',
                    '<input type="text" name="mail" value="'.$t['Mail'].'"><br>',
                '<label for="tel">Téléphone : </label>',
                    '<input type="text" name="tel" value="0'.$t['Tel'].'"><br>',

                '<h4>Adresse</h4>',
                '<label for="numRue">Numéro : </label>',
                    '<input type="text" name="numRue" value="'.$t['locNumero'].'"><br>',
                '<label for="addr">Adresse : </label>',
                    '<input type="text" name="addr" value="'.$t['locRue'].'"><br>',
                '<label for="cplt">Complément (bis, A, ...) : </label>',
                    '<input type="text" name="cplt" value="'.$t['locComplement'].'"><br>',
                '<label for="cp">Code Postal : </label>',
                    '<input type="text" name="cp" value="'.$t['locCP'].'"><br>',
                '<label for="ville">Ville : </label>',
                    '<input type="text" name="ville" value="'.$t['locVille'].'"><br>',
                '<label for="pays">Pays : </label>',
                    '<input type="text" name="pays" value="'.$t['locPays'].'"><br>',
            '</div></td>';


    if(!$patient){

    $nb_heure = substr($t['medDureeConsultation'], 0, 2);
    if ($nb_heure == '00'){
        $dureeConsF = substr($t['medDureeConsultation'], 3, 2);
    } else {
        $dureeConsF = intval($nb_heure)*60 + intval(substr($t['medDureeConsultation'], 3, 2));
    }

    echo
        '<td><div id="profil_medecin">',

            '<h4>Profil médical</h4>',
            '<label for="durCons">Durée des consultations : </label>',
            '<input type="text" name="durCons" id="profil_durCons" value="'.$dureeConsF.'"> minutes<br>',
            '<div id="profil_medecin_cb">';

            if ($t['medAccepteCB']){
                echo
                '<input type="radio" name="accCB" value="true" checked>Accepte la Carte Bancaire <br>',
                '<input type="radio" name="accCB" value="false">N\'accepte pas la Carte Bancaire <br>';
            } else {
                echo
                '<input type="radio" name="accCB" value="true">Accepte la Carte Bancaire <br>',
                '<input type="radio" name="accCB" value="false" checked>N\'accepte pas la Carte Bancaire <br>';
            }

            echo
            '</div>',

            '<div id="profil_medecin_tp">';

            if ($t['medAccepteTiersPayant']){
                echo
                '<input type="radio" name="accTP" value="true" checked>Accepte le Tiers Payant <br>',
                '<input type="radio" name="accTP" value="false">N\'accepte pas le Tiers Payant <br>';
            } else {
                echo
                '<input type="radio" name="accTP" value="true">Accepte le Tiers Payant <br>',
                '<input type="radio" name="accTP" value="false" checked>N\'accepte pas le Tiers Payant <br>';
            }

            echo
            '</div>',
        '</div></td>';
    }
    echo
        '</tr></table>',
        '<div id="profil_confirmation">',
            '<label for="password"><h4>Pour confirmer vos changements, tapez votre mot de passe actuel :</h4></label>',
                '<input type="password" name="password"><br>',
        '</div>',
        '<div id="profil_save">',
            '<input type="submit" name="btnValider" value="Enregistrer">',
        '</div>',
     '</form>';
     mysqli_free_result($res);
}


/**
 * FONCTION modification()
 * Modifie les données du client (à condition que toutes les données soient conformes et que le client est tapé le bon mod de passe)
 *
 * @param: boolean $patient: variable qui permet de savoir si la personne connectée est un patient ou un médecin
 * Les médecins ont des informations en plus que les patients
 *
 * @param: $bd: base de données associée
 *
 * @param: $id: l'id de la personne actuellement connectée
 *
 */
function modification($patient, $bd, $id){
    $error = array();

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $mail = trim($_POST['mail']);
    $tel = trim($_POST['tel']);
    $numRue = trim($_POST['numRue']);
    $addr = trim($_POST['addr']);
    $cplt = trim($_POST['cplt']);
    $cp = trim($_POST['cp']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);

    $pass1 = trim($_POST['mdp1']);
    $pass2 = trim($_POST['mdp2']);

    $mdpActuel = trim($_POST['password']);

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
            echo '<p> Email pas bonn format </p>';
        }
    }

    //VERIFICATION Tel
    if (!preg_match('#^[0-9]{9,10}$#',$tel)){
        $error['tel'] = 'Le numéro de téléphone doit être composé de chiffres';
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
    }

    //VERIFICATION numRue
    if (!preg_match('#^[0-9]{1,10}$#', $numRue)){
        $error['numRue'] = 'Le numéro de rue doit être un nombre';
    }

    //VERIFICATION addr
    if (mb_regex_encoding ('UTF-8') && strlen($addr)>100) {
        $error['addr'] = 'L\'adresse n\'est pas valide';
    }

    //VERIFICATION cplt
    if (mb_regex_encoding ('UTF-8') && strlen($cplt)>100) {
        $error['addr'] = 'L\'adresse n\'est pas valide';
    }

    //VERIFICATION CP
    if (!preg_match('#^[0-9]{5}$#', $cp)){
        $error['cp'] = 'Le code postal doit être composé de 5 chiffres';
    }

    //VERIFICATION ville
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $ville)) {
        $error['ville'] = 'La ville n\'est pas valide.';
    }

	//VERIFICATION pays
	if (mb_regex_encoding ('UTF-8') && ! mb_ereg_match("^[[:alpha:]][[:alpha:]\- ']{1,49}$", $pays)) {
        $error['pays'] = 'Le pays n\'est pas valide.';
    }

    if (!$patient){
        //VERIFICATION des infos du médecin (duree cons, accepte)
        $dureeCons = trim($_POST['durCons']);

        if (!preg_match('#^[0-9]{1,3}$#',$dureeCons)) {
            $error['dureeCons'] = 'La durée de consultation doit être un nombre';
        }

        //conversion (40 -> 00:40:00 // 80 -> 01:20:00)
        if ($dureeCons < 60){
            $dureeConsulationF = '00:'.$dureeCons.':00';
        } else {
            $nb_heure = intval($dureeCons / 60);
            if ($nb_heure < 10){
                $nb_heureF = '0'.$nb_heure;
            }
            $dureeConsulationF = $nb_heureF.':'.intval($dureeCons-$nb_heure*60).':00';
        }

        //VERIFICATION des infos des boutons radio pour accCB
        $ok = array('true', 'false');
        if (empty($_POST['accCB'])){
            $error['accCB'] = 'Vous devez sélectionner un bouton pour le champs "Accepte la Carte Bancaire"';
        } else if (!in_array($_POST['accCB'], $ok)){
            $error['accCB'] = 'Vous n\'avez pas sélectionné un de nos bouton (AccepteCB)';
        } else {
            $accCB = $_POST['accCB'];
        }

        if (empty($_POST['accTP'])){
            $error['accTP'] = 'Vous devez sélectionner un bouton pour le champs "Accepte le Tiers Payant"';
        } else if (!in_array($_POST['accTP'], $ok)){
            $error['accTP'] = 'Vous n\'avez pas sélectionné un de nos bouton (Accepte Tiers Payant)';
        } else {
            $accTP = $_POST['accTP'];
        }
    }


    //VERIFICATION mdp actuel
    if ($patient){
        $sql = "SELECT cliMDP AS mdp FROM client WHERE cliID = $id";
    } else {
        $sql = "SELECT medMDP AS mdp FROM medecin WHERE medID = $id";
    }

    $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    if (md5($mdpActuel) != $t['mdp']){
        $error['mdpActuel'] = 'Votre mot de passe actuel est incorrect, recommencez';
    }


    if (count($error)==0){//Si il n'y a aucune erreur
        if ($patient){
            $sql = 'SELECT cliMail AS mail FROM client WHERE cliID = '.$id.';';
        } else {
            $sql = 'SELECT medMail AS mail FROM medecin WHERE medID = '.$id.';';
        }

        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        $t = mysqli_fetch_assoc($res);
        mysqli_free_result($res);

        if (strcmp($t['mail'], $mail) != 0){//Il y a eu un changement de mail
            if ($patient){
                $sql = 'SELECT cliID AS id FROM client WHERE cliMail = \''.$mail.'\';';
            } else {
                $sql = 'SELECT medID AS id FROM medecin WHERE medMail = \''.$mail.'\';';
            }

            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
            if (mysqli_num_rows($res) != 0) {
                $error['email'] = 'L\'adresse email est déjà utilisée';
            }
            mysqli_free_result($res);
        }

        //Si il y a des erreurs, on s'arrête
        if (count($error) > 0){
            return;
        }
        //Pas d'erreur

        $nom = mysqli_real_escape_string($bd, trim($nom));
        $prenom = mysqli_real_escape_string($bd, trim($prenom));
        $mail = mysqli_real_escape_string($bd, trim($mail));
        $tel = mysqli_real_escape_string($bd, trim($tel));
        $numRue = mysqli_real_escape_string($bd, trim($numRue));
        $addr = mysqli_real_escape_string($bd, trim($addr));
        $cplt = mysqli_real_escape_string($bd, trim($cplt));
        $cp = mysqli_real_escape_string($bd, trim($cp));
        $ville = mysqli_real_escape_string($bd, trim($ville));
        $pays = mysqli_real_escape_string($bd, trim($pays));

        $pass = mysqli_real_escape_string($bd, trim(md5($pass1)));

        //VÉRIFICATION QUE LA LOCALITE EXISTE DEJA
        $sql = 'SELECT localiteID AS locID FROM localite WHERE locNumero = '.$numRue.' AND locRue = \''.$addr.'\' AND locComplement = \''.$cplt.'\' AND locCP = '.$cp.' AND locVille = \''.$ville.' \' AND locPays = \''.$pays.'\';';
        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

        if (mysqli_num_rows($res) != 0){//la localité existe
            $t = mysqli_fetch_assoc($res);
            $id_localite = $t['locID'];
        } else {//la localité n'existe pas : on va l'ajouter dans la bd, puis récupérer son id
            $sql = 'INSERT INTO localite (locNumero, locRue, locComplement, locCP, locVille, locPays) VALUES ('.$numRue.', \''.$addr.'\', \''.$cplt.'\', '.$cp.', \''.$ville.'\', \''.$pays.'\');';
            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

            $sql = 'SELECT MAX(localiteID) AS locID FROM localite';
            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
            $t = mysqli_fetch_assoc($res);
            $id_localite = $t['locID'];

            mysqli_free_result($res);
        }

        $add_sql = "";
        if (!empty($pass1) || !empty($pass2)){
            if ($patient){
                $add_sql .= ", cliMDP='$pass'";
            } else {
                $add_sql .= ", medMDP='$pass'";
            }
        }

        //On peut mettre à jour les infos du client ou du médecin
        if ($patient){
            $sql = "UPDATE client SET cliNom = '$nom', cliPrenom = '$prenom', cliMail = '$mail', cliTel = $tel, cliLocID = $id_localite $add_sql WHERE cliID = $id;";
        } else {
            //ajouter les modifs des infos spécifiques au médecin
            $sql = "UPDATE medecin SET medNom = '$nom', medPrenom = '$prenom', medMail = '$mail', medTel = $tel, medLocID = $id_localite , medAccepteCB = $accCB, medAccepteTiersPayant = $accTP, medDureeConsultation = '$dureeConsulationF' $add_sql WHERE medID = $id;";
        }

        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        mysqli_free_result($res);

        header("Location: ./profil.php");
        return;

    } else { //Affichage erreurs
        display_errors($error);
    }
}

/**
 * FONCTION supprimer_compte()
 * Suppression du compte de l'utilisateur (si son mdp est correct)
 *
 * @param: boolean $patient: variable qui permet de savoir si la personne connectée est un patient ou un médecin
 * Les médecins ont des informations en plus que les patients
 *
 * @param: $bd: base de données associée
 *
 * @param: $id: l'id de la personne actuellement connectée
 *
 */

function suppression_compte($patient, $bd, $id){
    $pass = trim($_POST['pass_supp']);

    if ($patient){
        $sql = "SELECT cliMDP AS mdp FROM client WHERE cliID = $id";
    } else {
        $sql = "SELECT medMDP AS mdp FROM medecin WHERE medID = $id";
    }

    $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
    $t = mysqli_fetch_assoc($res);
    if (md5($pass) != $t['mdp']){
        $error['mdpActuel'] = 'Votre mot de passe est incorrect';
    }

    if (count($error) == 0){
        if ($patient){

            $sql = "SELECT rdvID FROM rdv WHERE rdvCliID = $id";

            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

            $id_rdv = array();
            $count_rdv = 0;

            while ($t = mysqli_fetch_assoc($res)){
                $id_rdv[$count_rdv] = $t['rdvID'];
                ++$count_rdv;
            }

            foreach ($id_rdv as $value) {
                $sql = "DELETE FROM rdv WHERE rdvID = $value";
                $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
            }

            $sql = "DELETE FROM client WHERE cliID = $id";
        } else {

            $sql = "SELECT rdvID FROM rdv WHERE rdvMedID = $id";

            $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

            $id_rdv = array();
            $count_rdv = 0;

            while ($t = mysqli_fetch_assoc($res)){
                $id_rdv[$count_rdv] = $t['rdvID'];
                ++$count_rdv;
            }

            foreach ($id_rdv as $value) {
                $sql = "DELETE FROM rdv WHERE rdvID = $value";
                $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
            }

            $sql = "DELETE FROM medecin WHERE medID = $id";
        }

        $res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
        exit_session();
    } else {
        echo '<p class="error"> La suppression n\'a pas pu être réalisée à cause des erreurs suivantes :';
        foreach ($error as $value) {
            echo '<br> - ', $value;
        }
        echo '</p>';
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

$bd = bd_connect();

begin_html('Géo\'doc | Mon profil', '../css/geodoc.css');
display_header();

if (doc_co()){
    $id = $_SESSION['docID'];
    $patient = false;
} else if (patient_co()){
    $id = $_SESSION['patID'];
    $patient = true;
}

if (isset($_POST['btnValider'])){
    modification($patient, $bd, $id);
}

if (isset($_POST['btnSupprimer'])){
    suppression_compte($patient, $bd, $id);
}

if (doc_co()){ //connecté en tant que médecin
    profil(false, $bd, $id, $error);
} else if (patient_co()){ //connecté en tant que patient
    profil(true, $bd, $id, $error);
} else { //pas connecté
    echo '<h3>Connectez vous pour profiter de cette fonctionnalité.</h3>';
}

mysqli_close($bd);

display_footer();
end_html();

ob_end_flush();

/**
 * MDP pour DR HAREL : coucou1234
 * MDP pour les autres : azerty1234
 * MDP pour coco : azerty1234
 * MDP pour marie : smo
 * MDP pour guigui : hufflen123
 */

?>
