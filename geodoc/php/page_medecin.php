<?php
//débute la bufferisation
ob_start('ob_gzhandler');
session_start();

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

date_default_timezone_set('UTC');

$bd = bd_connect();
$form = false;
$extra_info_id = 0;
$err = array();

((isset($_POST['medID']) && is_entier($_POST['medID'])) || isset($_GET['medID']) && is_entier($_GET['medID'])) || exit_session();
$_POST['medID'] = isset($_GET['medID']) ? $_GET['medID'] : $_POST['medID'];


//changement de la semaine en cours
$debut_semaine = date('Y-m-d H:i:s', (strtotime(date('Y-m-d')) - (((intval(date('w')) - 1) % 7) * S_DAY)));
$fin_semaine = date('Y-m-d H:i:s', (strtotime($debut_semaine) + (7 * S_DAY - 1)));
if (isset($_POST['chgWeek'])) {
	$debut_semaine = date('Y-m-d H:i:s', strtotime($_POST['anPlanning'].'-01-01') + (S_DAY * 7 * ($_POST['weekPlanning'] - 1)) - ((intval(date('w', strtotime($_POST['anPlanning'].'-01-01'))) - 1) * S_DAY));
	$fin_semaine = date('Y-m-d H:i:s', strtotime($_POST['anPlanning'].'-01-01') + (S_DAY * 7 * $_POST['weekPlanning'] - 1));

	$form = true;
}

//demande de connexion pour la prise de rendez-vous
if (isset($_POST['rdvLibre'])) {
	$err['NonCo'] = 'Vous devez <a href="./connexion.php">vous connecter ou créer un compte</a> pour prendre rendez-vous.';
	$form = true;
}

//affectation d'un rendez-vous
if (isset($_POST['rdvPrendre'])) {
	if ($_POST['rdvDate'] < strtotime(date('Y-m-d H:i:s'))) {
		$err['PastRDV'] = 'Le rendez-vous que vous souhaitez planifier est déjà passé.';
	} else if ($_POST['rdvUrgent'] == 'true' && (!isset($_POST['rdvDescription']) || $_POST['rdvDescription'] == '')) {
		$err['NoDescription'] = 'Si votre rendez-vous est urgent vous devez indiquer pourquoi à l\'aide d\'une description.';
	} else {
		$desc = (isset($_POST['rdvDescription']) && $_POST['rdvDescription'] != '') ? '"'.$_POST['rdvDescription'].'"' : 'NULL';
		$sql = 'INSERT INTO `rdv` (`rdvCliID`, `rdvMedID`, `rdvHoraire`, `rdvDate`, `rdvJour`, `rdvUrgent`, `rdvDescription`) VALUES('.
				$_SESSION['patID'].', '.$_POST['medID'].', "'.date('H:i:s', $_POST['rdvDate']).'", "'.date('Y-m-d', $_POST['rdvDate']).'", '.date('w', $_POST['rdvDate']).', '.$_POST['rdvUrgent'].', '.$desc.');';
		$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	}
	$form = true;
}

//récupération des infos du médecin
$sql = "SELECT medNom, medPrenom, medMail, medTel, medNumRPPS, medAccepteCB, medAccepteTiersPayant, medDureeConsultation, speNom, locNumero, locRue, locCP, locVille, locComplement
		FROM medecin INNER JOIN specialite ON medSpecialiteID = speID
		INNER JOIN localite ON medLocID = localiteID
		WHERE medID = ".$_POST['medID'].";";
$res_med = mysqli_query($bd, $sql) or bd_error($bd, $sql);
$info_med = mysqli_fetch_assoc($res_med);

//protection des sorties
$nom = out_protect($info_med['medNom']);
$prenom = out_protect($info_med['medPrenom']);
$mail = out_protect($info_med['medMail']);
$spe = out_protect($info_med['speNom']);
$adresse = out_protect($info_med['locNumero']).' '.out_protect($info_med['locRue']).' '.out_protect($info_med['locComplement']);
$ville = out_protect($info_med['locCP']).' '.out_protect($info_med['locVille']);

$titre = 'Géo\'doc | '.substr($prenom, 0, 1).". $nom - $spe";
begin_html($titre, '../css/geodoc.css', '../', 'page_medecin');
display_header();
display_errors($err);

//affichage des informations du médecin
echo
"<h3>Dr. $nom $prenom</h3>",
'<div id="page_med_info">',
"<h4>Informations</h4>",
'<p>Spécialité :<br><div class="page_med_result">', $spe, '</div>',
'Adresse :<br><div class="page_med_result">', $adresse.'<br>'.$ville, '</div>',
'Mail :<br><div class="page_med_result">', $mail, '</div>Téléphone :<br><div class="page_med_result">0', $info_med['medTel'],'</div><br>Details : ',
'<div id="page_med_pro">',
'Numéro RPPS : ', $info_med['medNumRPPS'],'</p>',
	'<p>Carte bancaire : ';
if ($info_med['medAccepteCB']) {echo '✔';} else {echo '✖';}
echo '<p>Tiers payant : ';
if ($info_med['medAccepteTiersPayant']) {echo '✔';} else {echo '✖';}
echo
	'</p>',
'</div></div>',
	'<div class="planning"><table>',
		'<caption>Planning</caption>';
display_planning($bd, $debut_semaine, $fin_semaine);
echo
	'</table></div>';
mysqli_close($bd);
display_footer();
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


/**
* Affiche le planning
*
* @param	int 	$bd					identifiant de connexion à la base de données
* @param	string	$debut_semaine	début de la semaine à afficher au format Y-m-d H:i:s
* @param	string	$fin_semaine		fin de la semaine à afficher au format Y-m-d H:i:s
*/
function display_planning($bd, $debut_semaine, $fin_semaine) {
	//formulaire de choix de la semaine à afficher
	$an = intval(date('Y', strtotime($debut_semaine)));
	$max_an = $an + 10;
	$week = intval(date('W', strtotime($debut_semaine)));
	echo '<form method="post">',
				'<td><input type="hidden" name="medID" value="', $_POST['medID'], '">',
				'<label for="anPlanning"> Année </label>',
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
				'<input class="btnVoirPlanning" type="submit" name="chgWeek" value="Voir">',
			'</td></form>';

	//planning

	//on récupère les créneaux pour le médecin
	$sql = "SELECT creJour, creDebut, creFin, medDureeConsultation
				FROM creneau INNER JOIN medecin ON medID = creMedID
				WHERE creMedID = ".$_POST['medID']."
				ORDER BY creJour;";
	$creneaux	= mysqli_query($bd, $sql) or bd_error($bd, $sql);

	$today = date('Y-m-d');
	global $extra_info_id;
	$empty = true;;
	//puis les rendez-vous associés à la semaine et au jour en question
	while ($cre = mysqli_fetch_assoc($creneaux)) {
		$date_creneau = strtotime($debut_semaine) + (S_DAY * ($cre['creJour'] - 1));
		if ($date_creneau < strtotime($today)) {
			continue;
		}
		$empty = false;
		$sql = "SELECT cliID, cliNom, cliPrenom, cliNumSecu, rdvID, rdvDate, rdvHoraire, rdvDescription
						FROM client INNER JOIN rdv ON cliID = rdvCliID
						WHERE rdvMedID = ".$_POST['medID']."
						AND DATEDIFF('$debut_semaine', CONCAT_WS(' ', rdvDate, rdvHoraire)) <= 0
						AND DATEDIFF(CONCAT_WS(' ', rdvDate, rdvHoraire), '$fin_semaine') <= 0
						AND rdvJour = ".$cre['creJour']."
						ORDER BY rdvDate, rdvHoraire;";
					
		$temp_rdvs = mysqli_query($bd, $sql) or bd_error($bd, $sql);
		//storing rdv
		$rdvs = array();
		while($rdv = mysqli_fetch_assoc($temp_rdvs)) {
			$rdvs[] = $rdv;
		}
		mysqli_free_result($temp_rdvs);

		//affichage du créneau
		$nb_rdvs = floor((strtotime('1970-01-01 '.$cre['creFin']) - strtotime('1970-01-01 '.$cre['creDebut'])) / strtotime('1970-01-01 '.$cre['medDureeConsultation']));

		echo '<tr><td><div class="planning_planning_creneau"><p class="planning_horaires_jour">', num_to_day($cre['creJour']),' ', date('d/m/Y', $date_creneau), '<br>', writeHour($cre['creDebut']), ' - ', writeHour($cre['creFin']), '</p>';
		for ($i = 0; $i < $nb_rdvs; ++$i) {
			//Y a-il un rendez-vous à cet horaire ?
			$debut_rdv = date('H:i:s', strtotime('1970-01-01 '.$cre['creDebut']) + (strtotime('1970-01-01 '.$cre['medDureeConsultation']) * $i));
			$rdv_libre = true;

			foreach($rdvs as $rdv) {
				if ($rdv['rdvHoraire'] == $debut_rdv) {
					//afficher le rendez-vous, avec le nom s'il s'agit d'un rendez-vous du patient connecté
					$info_pat = (isset($_SESSION['patID']) && $rdv['cliID'] == $_SESSION['patID']) ? ' '.$rdv['cliPrenom'].' '.$rdv['cliNom'] : ' Occupé';
					echo
					'<div class="rdv_occupe"><p class="page_medecin_info_occupe">', writeHour($rdv['rdvHoraire']), ' - ', $info_pat, '</p></div>';
					$rdv_libre = false;
					break;
				}
			}
			
			//affichage du rendez-vous libre avec le formulaire permettant l'option de prise de rendez-vous si l'on est connecté en tant que patient
			if ($rdv_libre) {
				echo
				'<div class="rdv_libre">',
					'<p>', writeHour($debut_rdv), '</p>';
				if (patient_co()) {
					echo
					'<input type="button" onclick="extra_info(\'extra-info-', $extra_info_id, '\')" value="Prendre rendez-vous">',
					'<form method="post" class="extra-info" id="extra-info-', $extra_info_id, '" style="display : none;">',
						'<input type="hidden", name="medID" value="', $_POST['medID'], '">',
						'<input type="hidden" name="rdvDate" value="', ($date_creneau + strtotime('1970-01-01 '.$debut_rdv)), '">',
						'<table>',
							'<tr><td>Votre problème est-il urgent ?',
								'<input type="radio" name="rdvUrgent" value="true" id="urgent"><label for="urgent">oui</label>',
								'<input type="radio" name="rdvUrgent" value="false" id="non_urgent" checked><label for="non_urgent">non</label>',
							'</td></tr>',
							'<tr><td>Description : <input type="textarea" name="rdvDescription"></td></tr>',
							'<tr><td><input class="btnAffecter_rdv" type="submit" name="rdvPrendre" value="Prendre"></td></tr>',
						'</table>',
					'</form>';
					++$extra_info_id;
				} else {
					echo '<form method="post"><input type="hidden", name="medID" value="', $_POST['medID'], '"><input class="btnLibre_rdv" type="submit" name="rdvLibre" value="Libre"></form>';
				}
				echo
				'</div>';
			}
		}
		echo '</div></td></tr>';
	}
	if ($empty) {
		echo "<tr><td><p class='planning_noDispo'>Il n'y a pas de disponibilités pour cette semaine</p></td></tr>";
	}
}

ob_end_flush();

?>
