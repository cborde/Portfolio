<?php

/*
 * FONCTION formulaire()
 * @param $spe : spécialité déjà sélectionnée (si pas encore cliqué sur bouton c'est 0, sinon on récupère celle sélectionnée)
 * @param $bd
 *
 * Affiche le formulaire de recherche : permet de sélectionner une spécialité de médecin et une distance maximum
 * Si le client est connecté, il est possible de pouvoir faire la géolocalisation depuis le domicile du client
 *
 */
function formulaire($spe, $bd){

	echo
        '<div class="index_rech_form">',
				'<form method="POST" action="./index.php">',

					'<h4>Rechercher des médecins</h4><br>',


					'<div id="div_spe"><label for="specialite">Spécialité : </label>',
					'<select id="specialite" name="specialite" size=1>';

					//Affichage de toutes les spécialitées
					$sql = 'SELECT speNom FROM specialite;';
					$res = mysqli_query($bd, $sql);

					while ($t = mysqli_fetch_assoc($res)) {
						echo '<option value="', mysqli_real_escape_string($bd, $t['speNom']),'"', $spe===$t['speNom']?', selected':'', '>', mysqli_real_escape_string($bd, $t['speNom']), '</option>';
					}

	echo
					'</select></div>',

					'<br>';

					$d = null;
					if (isset($_POST['distance'])){
						$d = $_POST['distance'];
					}
echo
					'<div id="div_dist"><label for="distance"> Distance : </label>',
					'<select id="distance" name="distance" size=1>',
						'<option value="5"',($d==5)?'selected':'','>5 km</option>',
						'<option value="10"',($d==10)?'selected':'','>10 km</option>',
						'<option value="15"',($d==15)?'selected':'','>15 km</option>',
						'<option value="20"',($d==20)?'selected':'','>20 km</option>',
						'<option value="40"',($d==40)?'selected':'','>40 km</option>',
						'<option value="80"',($d==80)?'selected':'','>80 km</option>',
						'<option value="+80"',($d=='+80')?'selected':'','>Tout afficher</option>',
					'</select></div>',

					'<br>';

					if (isset($_POST['distance'])){
						echo '<div hidden name="distance" id="'.$_POST['distance'].'"></div>';
					}

					//Si client connecté, localisation possible depuis domicile
					if (doc_co() || patient_co()){

						//Div cachés pour pouvoir récupérer facilement la valeur de l'id de la personne dans le script au moyen d'un querySelector
						if (doc_co()){
							echo '<div hidden name="pers_co" id="d_'.$_SESSION['docID'].'"></div>';
						} else if (patient_co()){
							echo '<div hidden name="pers_co" id="p_'.$_SESSION['patID'].'"></div>';
						}

						if(patient_co()){

							echo
							'<div id="div_loc"><label for="localisation">Géolocalosation depuis : </label>',
							'<div id="div_loc_answer">';
							if (isset($_POST['localisation'])){
								if ($_POST['localisation'] == 'pos_act'){
									echo
									'<input id="localisation" type="radio" name="localisation" value="pos_act" checked>Position actuelle',
									'<input id="localisation" type="radio" name="localisation" value="domicile">Domicile';
								} else if ($_POST['localisation'] == 'domicile'){
									echo
									'<input id="localisation" type="radio" name="localisation" value="pos_act">Position actuelle',
									'<input id="localisation" type="radio" name="localisation" value="domicile" checked>Domicile';
								}
							} else {
								echo
								'<input id="localisation" type="radio" name="localisation" value="pos_act" checked>Position actuelle',
								'<input id="localisation" type="radio" name="localisation" value="domicile">Domicile';
							}
							echo '</div></div>';
						}

					}
					echo
					'<br>',

					'<input type="submit" name="btnRech" value="Rechercher">',
				'</form>',
			'</div>';
}

/*
 * FONCTION carte()
 *	Affiches des balises <div>
 * La balise <div id="mapid"> est la balise qui permet d'afficher la carte dedans depuis le script js
 *
 */
function carte(){
	echo
	'<div id="mapid">',
	'<div id="infoposition"></div></div>';
}

/*
 * FONCTION informations()
 * Affiche des données sur la spécialité sélectionnée
 *
 */
/*function informations(){
	echo
    '<div id="index_infos">',
	'<h3> Informations </h3>';

	if (isset($_POST['specialite'])){
    echo
    	'<p>Il y a <span id="count_res"></span> ', $_POST['specialite'] ,'(s) dans un rayon de ', $_POST['distance'] ,' km de votre ';
    	if (isset($_POST['localisation'])){
	    	if ($_POST['localisation'] == 'domicile') {
	    		echo 'domicile';
	    	}
	    	else {
	    		echo 'position';
	    	}
	    }else {
	    	echo 'position';
	    }
    	echo'.</p>',
		'<p> Temps d\'attente moyen pour la spécialité "', $_POST['specialite'],'" : x jours</p>',
		'<p> Temps d\'attente moyen pour la spécialité "', $_POST['specialite'],'" dans votre région : x jours </p>',
		'<p> Nombre de rendez-vous non honorés pour la spécialité "', $_POST['specialite'],'" dans votre région : x/mois </p>';
	}
	else
	{
		echo '<p>Vous n\'avez pas encore effectué de recherche.</p>';
	}

	echo '</div>';
}*/

/*
 *
 * 	MAIN
 *
 */

//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once './php/general_lib.php';
require_once './php/geodoc_lib.php';

//Création d'un cookie pour stocker la spécialité et l'envoyer au script carte.js
setcookie('specialite', 0);

$bd = bd_connect();

//Récupération de la spécialité que le client a sélectionné dans index.html, si c'est le premier chargement de la page, il n'y a pas de spécialité sélectionnée (valeur par défaut : 0)
if (isset($_POST['specialite'])){
	$specialite = mysqli_real_escape_string($bd, $_POST['specialite']);
	setcookie('specialite', $specialite, time()+10, './');
} else {
	$specialite = 0;
}

begin_html('Géo\'doc | Bienvenue', './css/geodoc.css', './');
display_header('./');

carte();
echo '<script src="./js/carte.js" charset="UTF-8"></script>';

formulaire($specialite, $bd);

display_footer("./php/");

end_html();

mysqli_close($bd);
ob_end_flush();



?>
