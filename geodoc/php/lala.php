<?php

date_default_timezone_set('UTC');
$creneaux = getCreneau($id_medecin);

foreach ($creneaux as $cre) {
	$rdvs = getRdvsOfCreneau($id_medecin, $cre);
	$nb_rdvs = ($timestamp_debut_creneau - $timestamp_fin_creneau) / $timestamp_duree_consultation;
	
	for ($i = 0; $i < $nb_rdvs; ++$i) {
		$debut_rdv = $timestamp_debut_creneau + ($timestamp_duree_consultation * $i);
		$libre = true;
		
		foreach ($rdvs as $rdv) {
			if ($debut_rdv == $rdv['debut']) {
				display_rdv_occupe();
				$libre = false;
			}
		}
		
		if ($libre) {
			display_rdv_libre();
		}
	}
}

>