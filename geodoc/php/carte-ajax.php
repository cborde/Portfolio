<?php

//récupération de la spécialité
$data = $_POST['specialite'];

require_once './general_lib.php';
$bd = bd_connect();

$sql = 'SELECT medID, medNom, medPrenom, medAccepteCB, medAccepteTiersPayant, locNumero, locRue, locCP, locVille FROM medecin, specialite, localite WHERE medSpecialiteID = speID AND medLocID = localiteID AND speNom= \''.$data.'\';';

$res = mysqli_query($bd, $sql);

$coord = array();

$count = 0;

while ($t = mysqli_fetch_assoc($res)) {

	$coord[$count] = array (
							"nom" => strip_tags($t['medNom']),
							"prenom" => strip_tags($t['medPrenom']),
							"cb" => strip_tags($t['medAccepteCB']),
							"tiersPayant" => strip_tags($t['medAccepteTiersPayant']),
							"num" => strip_tags($t['locNumero']),
							"rue" => strip_tags($t['locRue']),
							"cp" => strip_tags($t['locCP']),
							"ville" => strip_tags($t['locVille']),
							"id" => strip_tags($t['medID'])
							);
	$count++;
}

//renvoi du tableau
echo json_encode($coord);

mysqli_close($bd);
exit(0);
?>
