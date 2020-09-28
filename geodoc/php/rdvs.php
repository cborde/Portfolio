<?php
//débute la bufferisation
ob_start('ob_gzhandler'); 
session_start();  

require_once '../php/general_lib.php';
require_once '../php/geodoc_lib.php';

$bd = bd_connect();
$extra_info_id = 0;
$form = false;
isset($_SESSION['patID']) || exit_session();

//modification des infos des rdv passé
if (isset($_POST['rdvModif'])) {	
	$prix = (isset($_POST['prix']) && $_POST['prix'] != NULL) ? $_POST['prix'] : "NULL";
	$rdv = isset($_POST['rdvHonore']) ? "', `rdvHonore` = ".$_POST['rdvHonore'] : "'"; 
	$sql = "UPDATE `rdv` SET `rdvIntitule` =  '".bd_protect($bd, $_POST['intitule'])."', `rdvResume` = '".bd_protect($bd, $_POST['resume']).$rdv.", `rdvPrix` = ".$prix." 
				WHERE `rdvID` = ".$_POST['id'].";";
	
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	$form = true;
}

//suppression d'un rendez-vous
if (isset($_POST['rdvSuppr'])) {
	$sql = "DELETE FROM rdv
				WHERE rdvID = ".$_POST['rdvID'].";";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);
	
	$form = true;
}

begin_html('Géo\'doc | Mes rendez-vous', '../css/geodoc.css', '../', 'rdvs');
display_header();

echo 
'<h3>Mes rendez-vous</h3>',
'<article class="rdv_article">',
	'<table id="rdv_pris">',
		'<th>Rendez-vous à venir</th>',
		'<tbody>';
display_incomming_rdv($bd);
echo
		'</tbody>',
	'</table>',
'</article>',
'<article class="rdv_article">',
	'<table class="rdv_fais">',
		'<th>Rendez-vous passés</th>';
display_past_rdv($bd, false, './rdvs.php');
echo
	'</table>',
'</article>';
		
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

ob_end_flush();

/**
* Affiche les rendez-vous à venir pour le client
*
* @param	int 	$bd					identifiant de connexion à la base de données
*/
function display_incomming_rdv($bd) {
	$today = date('Y-m-d H:i:s');
	$sql = "SELECT medID, medNom, medPrenom, rdvHoraire, rdvDate, rdvID, locNumero, locRue
				FROM medecin INNER JOIN rdv ON medID = rdvMedID 
				INNER JOIN localite ON medLocID = localiteID
				WHERE DATEDIFF('$today', CONCAT_WS(' ', rdvDate, rdvHoraire)) < 0
				AND rdvCliID = ".$_SESSION['patID']."
				ORDER BY rdvDate, rdvHoraire;";
	$res = mysqli_query($bd, $sql) or bd_error($bd, $sql);	

	$empty = true;
	while ($t = mysqli_fetch_assoc($res)) {
		$empty = false;
		$nom = out_protect($t['medNom']);
		$prenom = out_protect($t['medPrenom']);
		$adresse = $t['locNumero'].' '.out_protect($t['locRue']);
		echo '<tr><td><scan class="rdv_date">', writeDate($t['rdvDate']), ' à ', writeHour($t['rdvHoraire']),"</scan><scan class='rdv_nom'><a href='./page_medecin.php?medID=", $t['medID'], "'>$prenom $nom</a> ( $adresse )</scan><scan>",
				'<form method="post">', 
					'<input type="hidden" name="rdvID" value="', $t['rdvID'], '">', 
					'<input class="btnSuppr_Incomingrdv" type="submit" name="rdvSuppr" value="Supprimer">',
				'</scan></form></td></tr>';
	}
	mysqli_free_result($res);
	if ($empty) {
		echo "<tr><td>Aucun rendez-vous n'a été pris.</td></tr>";
	}
}
?>