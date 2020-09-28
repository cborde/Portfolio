<?php
//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

date_default_timezone_set('UTC');

$bd = bd_connect();

//réalisation des actions des formulaires
isset($_SESSION['docID']) || exit_session();
$form = false;
$err = array();

//changement de la semaine en cours
$debut_semaine = date('Y-m-d H:i:s', (strtotime(date('Y-m-d')) - (((intval(date('w')) - 1) % 7) * S_DAY)));
$fin_semaine = date('Y-m-d H:i:s', (strtotime($debut_semaine) + (7 * S_DAY - 1)));
if (isset($_POST['chgWeek'])) {
	$debut_semaine = date('Y-m-d H:i:s', strtotime($_POST['anPlanning'].'-01-01') + (S_DAY * 7 * ($_POST['weekPlanning'] - 1)) - ((intval(date('w', strtotime($_POST['anPlanning'].'-01-01'))) - 1) * S_DAY));
	$fin_semaine = date('Y-m-d H:i:s', strtotime($_POST['anPlanning'].'-01-01') + (S_DAY * 7 * $_POST['weekPlanning'] - 1));

	$form = true;
}

//modification des infos des rdv passé
if (isset($_POST['rdvModif'])) {
	$prix = (isset($_POST['prix']) && $_POST['prix'] != NULL) ? $_POST['prix'] : "null";
	$sql = "UPDATE `rdv` SET `rdvIntitule` =  '".bd_protect($bd, $_POST['intitule'])."', `rdvResume` = '".bd_protect($bd, $_POST['resume'])."', `rdvHonore` = ".$_POST['rdvHonore'].", `rdvPrix` = ".$prix."
				WHERE `rdvID` = ".$_POST['id'].";";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

	$form = true;
}

//suppression d'un créneau
if (isset($_POST['creSuppr'])) {
	if (is_creneau_empty($bd)) {
		$sql = "DELETE FROM `creneau`
					WHERE `creMedID` = ".$_SESSION['docID']."
					AND `creJour` = ".$_POST['creJour']."
					AND `creDebut` = '".$_POST['creDebut']."';";
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	} else {
		$err['creneauNonVide'] = 'Le créneau que vous souhaitez supprimer n\'est pas vide pour toutes les semaines. Veuillez supprimer les rendez-vous prévus avant de supprimer ce créneau.';
	}
	$from = true;
}

//ajout d'un créneau
if (isset($_POST['creAdd'])) {
	if (strtotime('1970-01-01 '.$_POST['creDebut']) >= strtotime('1970-01-01 '.$_POST['creFin'])) {
		$err['creneauInvalide'] = 'Le créneau a un horaire de fin plus tôt que l\'horaire de début.';
	} else {
		//vérification: le créneau n'existe ni n'est entre les bornes d'un autre créneau
		$sql = "SELECT creJour, creDebut, creFin
					FROM creneau
					WHERE creMedID = ".$_SESSION['docID'].";";
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

		while ($t = mysqli_fetch_assoc($res)) {
			if ($t['creJour'] == $_POST['creJour'] && ((strtotime('1970-01-01 '.$_POST['creDebut']) >= strtotime('1970-01-01 '.$t['creDebut']) && strtotime('1970-01-01 '.$_POST['creDebut']) < strtotime('1970-01-01 '.$t['creFin'])) || (strtotime('1970-01-01 '.$_POST['creFin']) > strtotime('1970-01-01 '.$t['creDebut']) && strtotime('1970-01-01 '.$_POST['creFin']) <= strtotime('1970-01-01 '.$t['creFin'])))) {
				$err['creneauExiste'] = 'Ce créneau existe déjà, vous ne pouvez pas créer deux fois le même créneau.';
				break;
			}
		}
		mysqli_free_result($res);

		if (!isset($err['creneauExiste'])) {
			$sql = "INSERT INTO creneau (creMedID, creJour, creDebut, creFin)
						VALUES (".$_SESSION['docID'].", ".$_POST['creJour'].", '".$_POST['creDebut']."', '".$_POST['creFin']."');";
			$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
		}

	}
	$form = true;
}

//suppression d'un rendez-vous
if (isset($_POST['rdvSuppr'])) {
	$sql = "DELETE FROM rdv
				WHERE rdvID = ".$_POST['rdvID'].";";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

	$form = true;
}

//déplacement d'un rendez-vous
if (isset($_POST['rdvDepl'])) {
	$heure = date('H:i:s', strtotime($_POST['deplHoraire']));
	if (valid_deplacement($bd, $_POST['deplDate'], $heure)) {
		$sql = "DELETE FROM rdv
					WHERE rdvID = ".$_POST['rdvID'].";";
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

		$sql = 'INSERT INTO `rdv` (`rdvCliID`, `rdvMedID`, `rdvHoraire`, `rdvDate`, `rdvJour`) VALUES('.
					$_POST['rdvCliID'].', '.$_SESSION['docID'].', "'.$heure.'", "'.$_POST['deplDate'].'", '.date('w', strtotime($_POST['deplDate'])).');';
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	}

	$form = true;
}

//affectation d'un rendez-vous
if (isset($_POST['rdvAffect'])) {
	$sql = "SELECT cliID
				FROM client
				WHERE cliNumSecu = ".$_POST['affectSecu'].";";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	$cli_id = mysqli_fetch_assoc($res);
	if  (NULL == $cli_id) {
		$err['NumSecuInexistant'] = 'Le numéro de sécurité sociale n\'est pas affecté à un utilisateur du site.';
	} else if ($_POST['rdvDate'] < strtotime(date('Y-m-d H:i:s'))) {
		$err['PastRDV'] = 'Le rendez-vous que vous souhaitez planifier est déjà passé.';
	} else {
		mysqli_free_result($res);
		$sql = 'INSERT INTO `rdv` (`rdvCliID`, `rdvMedID`, `rdvHoraire`, `rdvDate`, `rdvJour`, `rdvUrgent`, `rdvDescription`) VALUES('.
				$cli_id['cliID'].', '.$_SESSION['docID'].', "'.date('H:i:s', $_POST['rdvDate']).'", "'.date('Y-m-d', $_POST['rdvDate']).'", '.date('w', $_POST['rdvDate']).', 0, NULL);';
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	}

	$form = true;
}

begin_html('Géo\'doc | Mon planning', '../css/geodoc.css', '../', 'planning');
display_header();
//affichage des erreurs repérées lors de la phase de traitement des formulaires
display_errors($err);
$extra_info_id = 0;

echo
'<h3>Planning</h3>',
'<table id="planning_table"><tr><td colspan="2">',
		display_emergency($bd);
echo
	'</td><tr>',
	'<tr><td><div class="planning"><table>',
		'<caption>Planning</caption>';
		display_planning($bd, $debut_semaine, $fin_semaine);
echo
	'</table></div></td>',
	'<td><div id="planning_creneaux"><table>',
		'<caption>Mes créneaux</caption>';
		display_creneaux($bd);
echo
	'</table></div></td></tr>',
'</table>',

'<article class="rdv_article">',
	'<table class="rdv_fais">',
		'<th>Rendez-vous passés</th>';
		display_past_rdv($bd, true, './planning.php');
echo
	'</table>',
'</article>';

mysqli_close($bd);

display_footer();
//script permettant le déroulement des infos des rdvs passés par clic sur le bouton "+"
echo
'<script>
	function extra_info(id)  {
		if (document.getElementById(id).style.display == "none") {
			document.getElementById(id).style.display = "block";
		} else {
			document.getElementById(id).style.display = "none";
		}
	}
</script>';
end_html();

ob_end_flush();

//Fonctions locales

/**
* Affiche les lignes du tableau des urgences
*
*@param	 $bd	int 	identifiant de connexion à la base de données
*/
function display_emergency($bd) {
	$today = date('Y-m-d H:i:s');
	//récupération des rendez-vous à venir urgents
	$sql = "SELECT cliNom, cliPrenom, cliNumSecu, rdvDescription
				FROM client INNER JOIN rdv ON cliID = rdvCliID
				WHERE rdvUrgent = true
				AND DATEDIFF('$today', CONCAT_WS(' ', rdvDate, rdvHoraire)) < 0
				ORDER BY rdvDate, rdvHoraire;";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	$empty = true;
	if ($res->num_rows != NULL) {
		$empty = false;
		echo
			'<div id="planning_urgences"><table>',
			'<caption>Demandes urgentes</caption>',
			'<tr><td>Numéro de sécurité sociale </td><td> Nom </td><td> Descriptif de l\'urgence</td></td></tr>';
	}

	while ($t = mysqli_fetch_assoc($res)) {
		$nom = out_protect($t['cliNom']);
		$prenom = out_protect($t['cliPrenom']);
		$num = out_protect($t['cliNumSecu']);
		$descriptif = out_protect($t['rdvDescription']);
		echo "<scan class='planning_urgences_patient'><tr><td>$num </td><td> $nom $prenom </td><td> $descriptif</td></tr></scan>";
	}

	if (!$empty) {
		echo '</table></div>';
	}
	mysqli_free_result($res);
}

/**
* Dit si l'horaire proposé pour un rendez-vous est valide
*
* @param	$bd			int			identifiant de connexion à la base de données
* @param	$rdv_date	string		date du rendez-vous au format Y-m-d
* @param	$rdv_heure	string		heure du rendez-vous au format H:i:s
* @return	$ret			boolean	faux si le déplacement n'est pas valide, vrai sinon
*/
function valid_deplacement($bd, $rdv_date, $rdv_heure) {
	global $err;
	$ret = true;
	$today = date('Y-m-d H:i:s');

	//test : le rendez-vous n'existe pas
	$sql = "SELECT rdvID
			FROM rdv
			WHERE rdvMedID = ".$_SESSION['docID']."
			AND rdvHoraire = '".$rdv_heure."'
			AND rdvDate = '".$rdv_date."';";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	$existing = mysqli_fetch_assoc($res);
	if ($existing != NULL) {
		$err['Exists'] = 'Vous avez déjà un rendez-vous prévu à cette date.';
		$ret = false;
	}
	mysqli_free_result($res);

	$sql = "SELECT creJour, creDebut, creFin, medDureeConsultation
				FROM creneau INNER JOIN medecin ON creMedID = medID
				WHERE creMedID = ".$_SESSION['docID']."
				AND creJour = ".date('w', strtotime($rdv_date)).";";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

	//le rdv s'intègre dans le créneau
	while ($cre = mysqli_fetch_assoc($res)) {
		$nb_rdvs = floor((strtotime('1970-01-01 '.$cre['creFin']) - strtotime('1970-01-01 '.$cre['creDebut'])) / strtotime('1970-01-01 '.$cre['medDureeConsultation']));
		for ($i = 0; $i < $nb_rdvs; ++$i) {
			$debut_rdv = date('H:i:s', (strtotime('1970-01-01 '.$cre['creDebut']) + (strtotime('1970-01-01 '.$cre['medDureeConsultation']) * $i)));
			if ($rdv_heure == $debut_rdv) {
					return $ret;
			}
		}
	}
	$err['InvalidHour'] = 'L\'heure ne correspond à aucune heure de début de créneau de rendez-vous.';
	return false;
}

/**
* Affiche le planning
*
* @param	int 	$bd					identifiant de connexion à la base de données
* @param	string	$debut_semaine	début de la semaine à afficher au format Y-m-d H:i:s
* @param	string	$fin_semaine		fin de la semaine à afficher au format Y-m-d H:i:s
*/
function display_planning($bd, $debut_semaine, $fin_semaine) {
	if(isset($_SESSION['docID'])){
		//formulaire de choix de la semaine à afficher
		$an = intval(date('Y', strtotime($debut_semaine)));
		$max_an = $an + 10;
		$week = intval(date('W', strtotime($debut_semaine)));
		echo '<form method="post">',
					'<td><label for="anPlanning"> Année </label>',
					'<select name="anPlanning" autofocus required>',
						'<option value="'.$an.'">'.$an.'</option>';
		for ($an; $an < $max_an; ++$an) {
			echo 	'<option value="'.$an.'">'.$an.'</option>';
		}
		echo
					'</select>',
					'<label for="weekPlanning"> Semaine </label>',
					'<select name="weekPlanning" required>';
		for ($i = 1; $i <= 53; ++$i) {
			if ($i == $week) {
				echo '<option value="'.$i.'" selected>'.$i.'</option>';
			} else {
				echo '<option value="'.$i.'">'.$i.'</option>';
			}
		}
		echo
					'</select><br>',
					'<input class="btnVoirPlanning" type="submit" name="chgWeek" value="Voir"></td>',
				'</form>';

		//planning
		//on récupère les créneaux pour le médecin connecté
		$sql = "SELECT creJour, creDebut, creFin, medDureeConsultation
					FROM creneau INNER JOIN medecin ON medID = creMedID
					WHERE creMedID = ".$_SESSION['docID']."
					ORDER BY creJour;";
		$creneaux = mysqli_query($bd, $sql) or bd_error($bd, $sql);

		//puis les rendez-vous associés à la semaine et au jour en question
		$today = date('Y-m-d');
		global $extra_info_id;
		$empty = true;
		while ($cre = mysqli_fetch_assoc($creneaux)) {
			$date_creneau = strtotime($debut_semaine) + (S_DAY * ($cre['creJour'] - 1));
			if ($date_creneau < strtotime($today)) {
				continue;
			}
			$empty = false;
			$sql = "SELECT cliID, cliNom, cliPrenom, cliNumSecu, rdvID, rdvDate, rdvHoraire, rdvDescription
							FROM client INNER JOIN rdv ON cliID = rdvCliID
							WHERE rdvMedID = ".$_SESSION['docID']."
							AND DATEDIFF('$debut_semaine', CONCAT_WS(' ', rdvDate, rdvHoraire)) <= 0
							AND DATEDIFF(CONCAT_WS(' ', rdvDate, rdvHoraire), '$fin_semaine') <= 0
							AND rdvJour = ".$cre['creJour']."
							ORDER BY rdvDate, rdvHoraire;";
			$temp_rdvs = mysqli_query($bd, $sql) or bd_error($bd, $sql);
			//on stocke les rendez-vous pour pouvoir les passer en revue plusieurs fois
			$rdvs = array();
			while($rdv = mysqli_fetch_assoc($temp_rdvs)) {
				$rdvs[] = $rdv;
			}
			mysqli_free_result($temp_rdvs);

			//affichage du créneau
			$nb_rdvs = floor((strtotime('1970-01-01 '.$cre['creFin']) - strtotime('1970-01-01 '.$cre['creDebut'])) / strtotime('1970-01-01 '.$cre['medDureeConsultation']));
			echo '<tr><td><div class="planning_planning_creneau"><p class="planning_horaires_jour">', num_to_day($cre['creJour'])," ", writeDate(date('Y-m-d', $date_creneau))," de ", writeHour($cre['creDebut']), ' à ', writeHour($cre['creFin']), '</p>';

			for ($i = 0; $i < $nb_rdvs; ++$i) {
				//y a-il un rendez-vous à cet horaire ?
				$debut_rdv = date('H:i:s', strtotime('1970-01-01 '.$cre['creDebut']) + (strtotime('1970-01-01 '.$cre['medDureeConsultation']) * $i));
				$rdv_libre = true;

				foreach($rdvs as $rdv) {
					if ($rdv['rdvHoraire'] == $debut_rdv) {
						//afficher le rendez vous occupé avec le formulaire contenant les options de suppression et de déplacement
						echo
						'<div class="rdv_occupe">',
							'<p><scan class="planning_horaires">', writeHour($rdv['rdvHoraire']), '</scan><br>', $rdv['cliPrenom'], ' ', $rdv['cliNom'], '<br>Numéro de sécurité sociale : ', $rdv['cliNumSecu'], '</p>',
							'<table>', 
								'<tr><td>',
									'<input type="button" onclick="extra_info(\'extra-info-', $extra_info_id, '\')" value="Déplacer le rdv du client">',
									'<form method="post" class="extra-info" id="extra-info-', $extra_info_id, '" style="display : none;">',
										'<table>',
											'<tr><td>',
												'<input type="hidden" name="rdvID" value="', $rdv['rdvID'], '">',
												'<input type="hidden" name="rdvCliID" value="', $rdv['cliID'], '">',
												'Date : <input type="date" name="deplDate" value="', $rdv['rdvDate'], '" min="', $today, '" required>',
											'</td></tr>',
											'<tr><td>Horaire : <input type="time" name="deplHoraire" value="', $rdv['rdvHoraire'], '" required></td></tr>',
											'<tr><td><input class="btnDeplacer_rdv" type="submit" name="rdvDepl" value="Deplacer"></td></tr>',
										'</table>',
									'</form>',
									'<form method="post"><input type="hidden" name="rdvID" value="', $rdv['rdvID'], '"><input class="btnSuppr_rdv" type="submit" name="rdvSuppr" value="Supprimer"></form>',
								'</td></tr>',
							'</table>',
						'</div>';
						$rdv_libre = false;
						++$extra_info_id;
						break;
					}
				}

				//affichage du rendez-vous libre avec le formulaire permettant l'option d'affectation
				if ($rdv_libre) {
					echo
					'<div class="rdv_libre">',
						'<form method="post">',
							'<p>', writeHour($debut_rdv), ' : <input type="button" onclick="extra_info(\'extra-info-', $extra_info_id, '\')" value="Affecter un client"></p>',
							'<table class="extra-info" id="extra-info-', $extra_info_id, '" style="display : none;">',
								'<tr><td><input type="text" name="affectSecu" placeholder="n° sécu sociale patient" autocomplete required>',
								'<input type="hidden" name="rdvDate" value="', ($date_creneau + strtotime('1970-01-01 '.$debut_rdv)), '">',
								'<input class="btnAffecter_rdv" type="submit" name="rdvAffect" value="Affecter"></td></tr>',
							'</table>',
						'</form>',
					'</div>';
					++$extra_info_id;
				}
			}
			echo '</div></td></tr>';
		}
		if ($empty) {
			echo '<tr><td><p class="planning_noDispo">Il n\'y a pas de disponibilité pour cette semaine.</td></tr>';
		}
	}
}

/**
* Affiche les créneaux définis par l'utilisateur
*
* $bd	int	identifiant de connexion à la base de données
*/
function display_creneaux($bd) {
	$sql = "SELECT creJour, creDebut, creFin
				FROM creneau
				WHERE creMedID = ".$_SESSION['docID']."
				ORDER BY creJour, creDebut;";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	
	$empty = true;
	while ($t = mysqli_fetch_assoc($res)) {
		$empty = false;
		echo '<tr><td><div class="planning_creneaux_creneau"><p>', num_to_day($t['creJour']), ' de ', writeHour($t['creDebut']), ' à ', writeHour($t['creFin']), '</p><span>',
			'<form method="post">',
				'<input type="hidden" name="creJour" value="', $t['creJour'], '">',
				'<input type="hidden" name="creDebut" value="', $t['creDebut'], '">',
				'<input type="hidden" name="creFin" value="', $t['creFin'], '">',
				'<input type="submit" name="creSuppr" value="Supprimer"></form>',
		'</span></div></td></tr>';
	}
	if ($empty) {
		echo "<tr><td><p class='planning_creneau_noDispo'>Vous n'avez pas défini de créneaux.</p></td></tr>";
	}
	
	//formulaire permettant d'ajouter un créneau
	echo
	'<tr><td colspan="4">',
	'<h4>Ajouter un créneau</h4>',
	'<form method="post">',
		'<table id="planning_add_creneau"><tr>',
		'<td>Jour</td>',
		'<td>Début</td>',
		'<td>Fin</td>',
		'</tr>',
		'<tr>',
		'<td><select name="creJour" autofocus required>',
				'<option value="1">Lundi</option>',
				'<option value="2">Mardi</option>',
				'<option value="3">Mercredi</option>',
				'<option value="4">Jeudi</option>',
				'<option value="5">Vendredi</option>',
				'<option value="6">Samedi</option>',
				'<option value="0">Dimanche</option>',
		'</select></td>',
		'<td><input type="time" name="creDebut" required></td>',
		'<td><input type="time" name="creFin" required></td>',
		'</tr>',
		'<tr><td></td><td><input type="submit" name="creAdd" value="Ajouter"></td>',
		'</tr></table>',
	'</form></td></tr>';
}

/**
* Vérifie si un créneau est vide de rendez-vous pour toutes les semaines
*
* @param	int			$bd					identifiant de connexion à la base de données
* @return	boolean	$crenea_empty	vrai si le créneau peut être supprimé, faux sinon
*/
function is_creneau_empty($bd) {
	$today = date('Y-m-d H:i:s');
	$sql = "SELECT rdvID, rdvDate, rdvHoraire, rdvJour
				FROM rdv
				WHERE rdvMedID = ".$_SESSION['docID']."
				AND DATEDIFF('$today', CONCAT_WS(' ', rdvDate, rdvHoraire)) <= 0;";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

	$creneau_empty = true;
	while ($t = mysqli_fetch_assoc($res)) {
		$day = $t['rdvJour'];
		$hour_rdv = strtotime('1970-01-01 '.$t['rdvHoraire']);
		$hour_begin_creneau = strtotime('1970-01-01 '.$_POST['creDebut']);
		$hour_end_creneau = strtotime('1970-01-01 '.$_POST['creFin']);
		if ($day == $_POST['creJour'] && ($hour_rdv >= $hour_begin_creneau && $hour_rdv < $hour_end_creneau)) {
			$creneau_empty = false;
			break;
		}
	}
	mysqli_free_result($res);
	return $creneau_empty;
}

?>
