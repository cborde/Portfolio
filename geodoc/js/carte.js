document.addEventListener("DOMContentLoaded", function(_e) {

	/**
	 * FONCTIONS (sql() et connectURL(url) QUI APPELLENT LE FICHIER carte-ajax.php POUR FAIRE UNE REQUETE SQL DANS LA BASE DE DONNEE
	 * AJAX
	 */

	function sql(){
		var retour = new Array();
		var retour = connectURL("./php/carte-ajax.php");

		if (retour != ''){
			var r = JSON.parse(retour);
			return r;
		} else {
			alert("Un problème est survenu");
			return '';
		}
	}

	function connectURL(url){
		var xhr_object = null;

		if (window.XMLHttpRequest){
			xhr_object = new XMLHttpRequest();
		} else {
			alert("Problème avec requête XMLHttp");
		}

		xhr_object.open("POST", url, false);
		xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		var data = "specialite="+specialite;

		xhr_object.send(data);

		if (xhr_object.readyState == 4){
			return xhr_object.responseText;
		} else {
			return '';
		}
	}

	/**
	 * FONCTIONS (sql_client() et connectURL_client()) QUI PERMETTENT DE RECUPERER L'ADRESSE DU CLIENT
	 * AJAX
	 */

	 function sql_client(){
	 	var retour = new Array();
 		var retour = connectURL_client("./php/addr_client.php");

 		if (retour != ''){
 			var r = JSON.parse(retour);
 			return r;
 		} else {
 			alert("Un problème est survenu");
 			return '';
 		}
	 }

	 function connectURL_client(url){
 		var xhr_object = null;

 		if (window.XMLHttpRequest){
 			xhr_object = new XMLHttpRequest();
 		} else {
 			alert("Problème avec requête XMLHttp");
 		}

 		xhr_object.open("POST", url, false);
 		xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		var data = "pers="+personne_connectee;
 		xhr_object.send(data);

 		if (xhr_object.readyState == 4){
 			return xhr_object.responseText;
 		} else {
 			return '';
 		}
 	}

	/**
	 * FONCTIONS (sql_dispo() et connectURL_dispo()) QUI PERMETTENT DE RECUPERER LA PREMIERE DISPONIBILITE D'UN MEDECIN
	 * AJAX
	 */

	function sql_dispo(id_m){
		var retour = new Array();
		var retour = connectURL_dispo("./php/first_dispo-ajax.php", id_m);

		if (retour != ''){
			var r = JSON.parse(retour);
			return r;
		} else {
			alert("Un problème est survenu");
			return '';
		}
	}

	function connectURL_dispo(url, id_m){
		var xhr_object = null;

		if (window.XMLHttpRequest){
			xhr_object = new XMLHttpRequest();
		} else {
			alert("Problème avec requête XMLHttp");
		}

		xhr_object.open("POST", url, false);
		xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		var data = "id="+id_m;
		xhr_object.send(data);

		if (xhr_object.readyState == 4){
			return xhr_object.responseText;
		} else {
			return '';
		}
	}




	/**
	 * FONCTIONS (deg2rad(x) et get_distance_km() QUI PERMETTENT DE CALCULER UNE DISTANCE ENTRE DEUX POINTS AVEC COORDONNEES GPS
	 */

	/*
	 * FONCTION QUI CONVERTI DES DEGRES EN RADIAN
	 */
	function deg2rad(x){
		return Math.PI*x/180;
	}

	/*
	 * FONCTION QUI CALCULE LA DISTANCE EN KM ENTRE DEUX POINTS DE COORDONNEES GPS
	 *
	 *
	 */

	function get_distance_km($lat1, $lng1, $lat2, $lng2) {
		  $earth_radius = 6378.137;   // Terre = sphère de 6378.137km de rayon
		  //Conversions en radian
		  $rlo1 = deg2rad($lng1);
		  $rla1 = deg2rad($lat1);
		  $rlo2 = deg2rad($lng2);
		  $rla2 = deg2rad($lat2);
		  $dlo = ($rlo2 - $rlo1) / 2;
		  $dla = ($rla2 - $rla1) / 2;
		  $a = (Math.sin($dla) * Math.sin($dla)) + Math.cos($rla1) * Math.cos($rla2) * (Math.sin($dlo) * Math.sin($dlo));
		  $d = 2 * Math.atan2(Math.sqrt($a), Math.sqrt(1 - $a));
		  return (Math.trunc(($earth_radius * $d)*100)/100);
	}

	/**
	 * FONCTIONS (placerAddr(), addr_search() et myFunction()) QUI PERMETTENT D'OBTENIR LES COORDONNEES GPS EN FONCTION D'UNE ADRESSE
	 * API ADRESS2COORDONNEES
	 * https://stackoverflow.com/questions/15919227/get-latitude-longitude-as-per-address-given-for-leaflet
	 */

	 function placerAddr(lat1, lng1, infos){

		if (localisation_depuis == 'domicile'){
			var distance = get_distance_km(lat1, lng1, coordActuelles['lat'], coordActuelles['long']);
		} else {
			var distance = get_distance_km(lat1, lng1, coordActuelles['lat'], coordActuelles['long']);
		}

		if (dist != "+80" && distance > dist){
			return;
		}

		var myMarker = L.marker([lat1, lng1]);

		var iconMedecin = L.icon({
		    iconUrl: './images/medecin.png',
		    iconSize: [50, 50],
		});

		var iconPersonne = L.icon({
			iconUrl: './images/personne.png',
			iconSize: [50, 50],
		});

		if (infos == 'Votre position'){
			if (localisation_depuis == 'domicile'){
				myMarker.bindPopup("<p> Votre domicile </p>");
			} else {
				myMarker.bindPopup("<p> Votre position </p>");
			}
			myMarker.setIcon(iconPersonne);
		} else {

			var first_dispo = sql_dispo(infos['id']);
			//var first_dispo = 1;

			myMarker.bindPopup("<p>" + infos['nom'] + ' ' + infos['prenom'] + "<p>"+ infos['rue'] + ' ' + infos['ville'] + "</p>"+ "</p><p> Accepte CB : " + (infos['cb']==1? "Oui" : "Non") + "</p><p> Accepte tiers payant : " + (infos['tiersPayant']==1? "Oui":"Non") + "<p>"+ distance +" km</p><p>Première disponibilité : "+ first_dispo +"</p><p><a href='php/page_medecin.php?medID="+infos['id']+"'>Prendre RDV</a></p>").openPopup();
			myMarker.setIcon(iconMedecin);
		}
		myMarker.addTo(map);
	 }

	 function myFunction(arr, infos){
		if (arr != null){
			placerAddr(arr[0].lat, arr[0].lon, infos, coordActuelles);
		} else {
			alert('Problème pour l\'adresse');
		}
	}

	function addr_search(adresse, infos){
		var inp = document.getElementById("addr");
		var xmlhttp = new XMLHttpRequest();
		var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + adresse;
		xmlhttp.onreadystatechange = function(){
			if (this.readyState == 4 && this.status == 200){
				var myArr = JSON.parse(this.responseText);
				myFunction(myArr, infos, coordActuelles);
			}
		};
		xmlhttp.open("GET", url, true);
		xmlhttp.send();
	 }

	 /*
	  * FONCTION addr_search_set_coord
	  * Fonction converti l'addresse du client en coordonnees et qui les stockent dans la variable coordActuelles
	  *
	  *
	  */

	 function addr_search_set_coord(adresse){

 		var inp = document.getElementById("addr");
 		var xmlhttp = new XMLHttpRequest();
 		var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + adresse;
 		xmlhttp.onreadystatechange = function(){
			//Quand on recoit le resultat, on peut set la variable coordActuelles et appeler les fonctions qui affichent la position du domicile et les médecins
 			if (this.readyState == 4 && this.status == 200){
				var myArr = JSON.parse(this.responseText);
 				coordActuelles = {"lat" : myArr[0].lat, "long" : myArr[0].lon};
				addr_search(adresse, 'Votre position');
				affichage_resultats();
 			}
 		};
 		xmlhttp.open("GET", url, true);
 		xmlhttp.send();
 	 }

	/**
	 * FONCTION AFFICHAGE DE LA CARTE ET LA POSITION DE L'UTILISATEUR SUR LA CARTE
	 */
	function maPosition(position) {

		//Création de la carte
		var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
		var osmAttrib='Map data © OpenStreetMap contributors';
		var osm = new L.TileLayer(osmUrl, {attribution: osmAttrib});
		//si loca depuis domicile selectionnee, par défaut, la carte s'affiche sur besançon
		if (localisation_depuis == 'domicile'){
			map.setView([47.23, 6.02], 12)
		} else {
			map.setView([position.coords.latitude, position.coords.longitude], 12);
		}

		map.addLayer(osm);

		//Marker position du client
		if (localisation_depuis == 'domicile'){
			infos = 'Votre position';

			var coordonnees = sql_client();
			var adresse = coordonnees[0]['num'] + ' ' + coordonnees[0]['rue'] + ' ' + coordonnees[0]['cp'] + ' ' + coordonnees[0]['ville'];

			addr_search_set_coord(adresse);
			//coordActuelles = {"lat" : position.coords.latitude, "long" : position.coords.longitude};
			//coordActuelles = addr_search_set_coord(adresse).onloaddata = addr_search(adresse, infos);

		} else {
			coordActuelles = {"lat" : position.coords.latitude, "long" : position.coords.longitude};
			var marker = L.marker([position.coords.latitude, position.coords.longitude]);
			var iconPersonne = L.icon({
			    iconUrl: './images/personne.png',
			    iconSize: [50, 50],
			});

			//var coordActuelles = {"lat" : position.coords.latitude, "long" : position.coords.longitude};
			marker.setIcon(iconPersonne);
			marker.bindPopup("<p>Votre position</p>").openPopup();
			marker.addTo(map);

			affichage_resultats();
		}


	}

	/*
     * FONCTION affichage_resultats()
	 * Affiche les médecins sur la carte
	 */

	function affichage_resultats(){
		//Affichage des résultats (médecins)
		if (specialite != 0){
			var coordonnees = sql();
			if (coordonnees != ''){
				var count = 0;
				while (coordonnees[count] != null){
					var adresse = coordonnees[count]['num'] + ' ' + coordonnees[count]['rue'] + ' ' + coordonnees[count]['cp'] + ' ' + coordonnees[count]['ville'];
					addr_search(adresse, coordonnees[count]);
					count++;
				}
				count_res.innerHTML = count;
			}
		}
	}

	/**
	 * FONCTION D'ERREUR SI LA GEOLOCATION NE FONCTIONNE PAS
	 */

	function erreurPosition(error) {
		var info = "Erreur lors de la géolocalisation : ";
		switch(error.code) {
			case error.TIMEOUT:
				info += "Timeout !";
			break;
				case error.PERMISSION_DENIED:
				info += "Vous n’avez pas donné la permission";
			break;
			case error.POSITION_UNAVAILABLE:
				info += "La position n’a pu être déterminée";
			break;
			case error.UNKNOWN_ERROR:
				info += "Erreur inconnue";
			break;
		}
		//document.getElementById("infoposition").innerHTML = info;
		alert(info);
	}

	/**
	* MAIN
	*/

	//Ce bout de code permet de trier les cookies pour les stocker dans un tableau associatif.
	var cookies = document.cookie.split(';').map(function(el){ return el.split('='); }).reduce(function(prev,cur){ prev[cur[0]] = cur[1];return prev },{});
	//On récupère grâce au cookie la spécialité définie dans index.php

	var specialite = cookies['specialite'];

	//récupération de la distance sélectionnée
	var dist = document.querySelector("div[name='distance']");
	if (dist != null){
		dist = dist.id;
	}

	//récupération de l'id de la personne connectée qui sont dans les divs cachés
	var personne_connectee = document.querySelector("div[name='pers_co']");
	if (personne_connectee != null){
		personne_connectee = personne_connectee.id;
	}

	var localisation_depuis = document.querySelector("input[name=localisation]:checked");
	if (localisation_depuis != null){
		localisation_depuis = localisation_depuis.value;
	}

	//vérif que navigateur fonctionne avec la géoloc et que le client à autoriser
	if(navigator.geolocation){

		//déclaration de la map ici pour que la variable soit globale
		var map = L.map('mapid');

		var coordActuelles = {"lat" : 0, "long" : 0};

		navigator.geolocation.getCurrentPosition(maPosition, erreurPosition);
	} else {
	 	alert("Le navigateur ne prend pas en charge la localisation");
	}


});
