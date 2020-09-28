<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                              Bibliothèque de fonctions spécifiques                                                                    //
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 *	Fonction affichant le canevas général de l'application
 *
 *	Affiche bloc page, entête et menu de navigation, enseigne, ouverture du bloc de contenu.
 *
 *	@param 	string		$prefix		préfixe du nom de fichier
 */
function display_header($prefix = '../') {
	$co = isset($_SESSION['cliID']) ? true : false;

	echo
		'<header>',
			'<nav>',
				'<a href="', $prefix, 'index.php"><img src="', $prefix, 'images/geodoc.png"></a>';

	$liens = array();

	if (doc_co()){
		$liens['deconnexion'] = array( 'pos' => 1);
		$liens['planning'] = array( 'pos' => 2);
		$liens['profil'] = array( 'pos' => 3);
	} else if (patient_co()) {
		$liens['deconnexion'] = array( 'pos' => 1);
		$liens['rdvs'] = array( 'pos' => 2);
		$liens['profil'] = array( 'pos' => 3);
	} else {
		$liens['connexion'] = array( 'pos' => 1);
	}

	foreach ($liens as $cle => $elt) {
		echo
			'<a class="item_menu ', $elt['pos'], '" href="', $prefix, 'php/', $cle, '.php"></a>';
	}

	echo
			'</nav>',
		'</header>',
	'<section id="general_contenu">';
}

/**
* Affiche les messages d'erreurs du formulaire envoyé
*
* @param	array	$err	tableau contenant les messages d'erreur à afficher
*/
function display_errors($err) {
	if (count($err) > 0) {
		echo '<p class="error"> Erreur(s) :';
		foreach ($err as $v) {
			echo '<br> - ', $v;
		}
		echo '</p>';
	}
}

/**
* Indique si l'usager est connecté en tant que docteur
*
* @return boolean 	Vrai si un docteur est connecté, faux sinon
*/
function doc_co() {
	if (isset($_SESSION['docID'])) {
		return true;
	}
	return false;
}

/**
* Indique si l'usager est connecté en tant que patient
*
* @return boolean 	Vrai si un patient est connecté, faux sinon
*/
function patient_co() {
	if (isset($_SESSION['patID'])) {
		return true;
	}
	return false;
}

/**
* Indique si l'usager n'est pas connecté
*
* @return boolean 	Vrai si un usager n'est pas connecté, faux sinon
*/
function not_co() {
	return !(doc_co() || patient_co());
}

/**
* Renvoie le nom du jour correspondant au numéro entré
*
* @param	int	$num_day	numéro du jour à transformer
* @return	string	nom du jour correspondant
*/
function num_to_day($num_day) {
	$days = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
	return $days[$num_day];
}

/**
* Affiche les lignes du tableau des rendez-vous passés
*
* @param	$bd		int			identifiant de connexion à la base de données
* @param	$med		boolean	vrai si on affiche les rendez-vous d'un medecin, faux si c'est ceux d'un client
* @param	$page	string		donne la page vers laquelle le formulaire doit être renvoyé
*/
function display_past_rdv($bd, $med, $page) {
	date_default_timezone_set('UTC');
	$req = $med ? 'cliNom, cliPrenom, cliNumSecu, rdvMedID,' : 'medNom, medPrenom, rdvCliID,';
	$cond = $med? 'rdvMedID = '.$_SESSION['docID'] : 'rdvCliID = '.$_SESSION['patID'];
	$today = date('Y-m-d H:i:s');
	
	//récupération des informations de rendez-vous passés en fonction des conditions définies plus haut prenant en compte l'appel pour un médecin ou pour un patient
	$sql = "SELECT $req rdvID, rdvDate, rdvHoraire, rdvPrix, rdvResume, rdvIntitule, rdvHonore
				FROM client INNER JOIN rdv ON cliID = rdvCliID
				INNER JOIN medecin ON rdvMedID = medID
				WHERE DATEDIFF('$today', CONCAT_WS(' ', rdvDate, rdvHoraire)) >= 0
				AND $cond
				ORDER BY rdvDate DESC, rdvHoraire DESC;";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);

	global $extra_info_id;
	$empty = true;
	//pour tous les rendez-vous récupérés
	while ($t = mysqli_fetch_assoc($res)) {
		$empty = false;
		
		//protection des sorties
		$nom = $med ? out_protect($t['cliNom']) : out_protect($t['medNom']);
		$prenom = $med ? out_protect($t['cliPrenom']) : out_protect($t['medPrenom']);
		$intitule = out_protect($t['rdvIntitule']);
		$resume = out_protect($t['rdvResume']);
		$numsecu = $med ? $t['cliNumSecu'] : '';
		$readonly = $med? '' : ' readonly ';
		
		//affichage d'un bloc normal et d'un bloc caché contenant les informations du rendez-vous qui pourra être déroulé
		echo '<tr><td class="extra-resume"><scan class="rdv_date">', writeDate($t['rdvDate']), " à ", writeHour($t['rdvHoraire']), "</scan> <scan class='rdv_nom'>$nom $prenom ", '</scan> <input class="rdv_details" type="button" onclick="extra_info(\'extra-info-', $extra_info_id, '\')" value="+" title="Détails"></td></tr>',
				'<tr><td class="extra-info" id="extra-info-', $extra_info_id, '" style="display : none;">',
					'<div class="geodoc_trait"></div>',
					'<form action="', $page, '" method="post">';
					if ($numsecu != '') {
						echo '<label for="numsecu"><h4>Numéro de sécurité sociale : ', $numsecu,'</h4></label>';
					}
						echo
							'<label for="intitule"><h4>Intitulé</h4></label><input type="text" name="intitule" value="', $intitule, '">',
							'<label for="resume"><h4>Résumé</h4></label><textarea rows="8" cols="50" name="resume">', $resume,'</textarea>',
							'<label for="prix"><h4>Prix (€)</h4></label><input type="text" name="prix" value="', $t['rdvPrix'], '"', $readonly, '>',
							'<p>Le rendez-vous a-il été honoré ? ';
		//la présence au rendez-vous est modifiable uniquement par le médecin
		if ($med) {
			echo '<input type="radio" name="rdvHonore" value="true" id="honore" ', $readonly;
			if ($t['rdvHonore']) { echo 'checked'; }
			echo  '><label for="honore">oui</label>',
					'<input type="radio" name="rdvHonore" value="false" id="nonhonore" ', $readonly;
			if (!$t['rdvHonore']) { echo 'checked'; }
			echo '><label for="nonhonore">non</label>';
		} else {
			if ($t['rdvHonore']) { 
				echo 'Oui'; 
			} else {
				echo 'Non';
			}
		}
		echo '</p>',
							'<input type="hidden" name="id" value="', $t['rdvID'], '">', 
							'<input class="btnModif_rdv" type="submit" name="rdvModif" value="Modifier">',
					'</form>',
				'<div class="geodoc_trait"></div>',
				'</td></tr>';
		++$extra_info_id;
	}
	mysqli_free_result($res);
	if ($empty) {
		echo '<tr><td>Aucun rendez-vous n\'a été passé</td></tr>';
	}
}

/**
* Perrmet d'obtenir la première disponibilité d'un médecin donné
*
* @param	$docid	int			identifiant du docteur dans la base
* @param	$bd		object	identifiant de connexiion à la base de données
* @return				string		Date et heure de la première disponibilité ou message indiquant qu'il n'y a pas de disponibilité
*/
function get_first_dispo($docid, $bd) {
	date_default_timezone_set('UTC');
	$today = date('Y-m-d');
	//définition des dates de début et fin de semaine. On commence à la semaine courante pour itérer ensuite
	$debut_semaine = date('Y-m-d H:i:s', (strtotime($today) - (((intval(date('w')) - 1) % 7) * S_DAY)));
	$fin_semaine = date('Y-m-d H:i:s', (strtotime($debut_semaine) + (7 * S_DAY - 1)));
	$num_semaine = 0;

	//on récupère les créneaux pour le médecin connecté
	$sql = "SELECT creJour, creDebut, creFin, medDureeConsultation
				FROM creneau INNER JOIN medecin ON medID = creMedID
				WHERE creMedID = ".$docid."
				ORDER BY creJour;";
	$temp_creneaux	= mysqli_query($bd, $sql) or bd_error($bd, $sql);
	
	//on stocke les créneaux dans un tableau pour pouvoir les passer en revue plusieurs fois
	$creneaux = array();
	while($cre = mysqli_fetch_assoc($temp_creneaux)) {
		$creneaux[] = $cre;
	}
	mysqli_free_result($temp_creneaux);

	if (empty($creneaux)) {
		return 'Pas de disponibilité';
	}

	//puis les rendez-vous associés à la semaine et au jour en question. On donne la disponibilité pour l'année en cours
	while(intval(date('Y', strtotime($debut_semaine))) == intval(date('Y', strtotime($today)))) {
		//redéfinition des semaines en fonction du nombre d'itérations
		$debut_semaine = date('Y-m-d H:i:s', (strtotime($debut_semaine) + (7 * S_DAY * $num_semaine)));
		$fin_semaine = date('Y-m-d H:i:s', (strtotime($debut_semaine) + (7 * S_DAY - 1)));

		foreach ($creneaux as $cre) {
			$sql = "SELECT rdvHoraire
							FROM rdv
							WHERE rdvMedID = ".$docid."
							AND DATEDIFF('$debut_semaine', CONCAT_WS(' ', rdvDate, rdvHoraire)) <= 0
							AND DATEDIFF(CONCAT_WS(' ', rdvDate, rdvHoraire), '$fin_semaine') <= 0
							AND rdvJour = ".$cre['creJour']."
							ORDER BY rdvDate, rdvHoraire;";
			$temp_rdvs = mysqli_query($bd, $sql) or bd_error($bd, $sql);
			//on stocke les rendez-vous pour la même raison que les créneaux
			$rdvs = array();
			while($rdv = mysqli_fetch_assoc($temp_rdvs)) {
				$rdvs[] = $rdv;
			}
			mysqli_free_result($temp_rdvs);

			//on teste si le créneau respecte la condition d'année courante
			$date_creneau = strtotime($debut_semaine) + (S_DAY * ($cre['creJour'] - 1));
			if (intval(date('Y', $date_creneau)) != intval(date('Y', strtotime($today)))) {
				return 'Pas de disponibilité';
			}
			$nb_rdvs = floor((strtotime('1970-01-01 '.$cre['creFin']) - strtotime('1970-01-01 '.$cre['creDebut'])) / strtotime('1970-01-01 '.$cre['medDureeConsultation']));

			//pour tous les créneaux de rendez-vous calculés en fonction des horaires de créneau, de la durée de consultation du médecin, et du nombre d'itérations sur les rendez-vous, on teste s'il est libre
			for ($i = 0; $i < $nb_rdvs; ++$i) {
				$debut_rdv = date('H:i:s', strtotime('1970-01-01 '.$cre['creDebut']) + (strtotime('1970-01-01 '.$cre['medDureeConsultation']) * $i));
				$rdv_libre = true;

				foreach($rdvs as $rdv) {
					if ($rdv['rdvHoraire'] == $debut_rdv) {
						$rdv_libre = false;
						break;
					}
				}
				if ($rdv_libre && $date_creneau > strtotime($today)) {
					return date('d/m/Y', $date_creneau).' à '.$debut_rdv;
				}
			}
		}
		++$num_semaine;
	}
	return 'Pas de disponibilité';
}

// Avec comme séparateur '-'
function writeDate ($reverseDate) {
	$tab = explode('-', $reverseDate);
	echo $tab[2], '/', $tab[1], '/', $tab[0];
}

function writeHour ($hour) {
	$tab = explode(':', $hour);
	echo $tab[0], 'h', $tab[1];
}
?>

