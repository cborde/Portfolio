var socket;
document.addEventListener("DOMContentLoaded",function(_e){
	
    function connect(){
		
		//ENVOIE DU NOM DE L'UTILISATEUR QUI VIENS DE CE CONNECTER
        pseudo = document.getElementById("pseudo").value;
        salon = document.getElementById("salon").value;
		socket.emit("login", {id: pseudo, server: salon, avatar: avatar});
		
		//BIENVENUE
		socket.on("bienvenue",function(id){
            pseudo = id;
            var radio2 = document.getElementById("radio2");
            radio2.checked = true;
            document.querySelector("#chat main").innerHTML = "<p class=\"system\">Bienvenue "+pseudo+" !</p>";
		
			//MAJ DU TITRE
			var titre = document.getElementById("login");
			titre.innerHTML = pseudo;
		
            //RECUPERATION DE LA LISTE DES UTILISATEURS CONNECTES ET AFFICHAGE
            
            socket.on("gameInfo", receiveGameInfo);
            jouer();
        });
		
		socket.on("infoServer", function(infos){
			var nomSalon = document.getElementById("nomSalon");
			var idSalon = document.getElementById("idSalon");
			nomSalon.innerHTML = infos.name;
			idSalon.innerHTML = "ID : " + infos.id;
		});
        
        socket.on("serverError", function(serv){
            var errorLog = document.getElementById("errorLog");
            errorLog.innerHTML = "Le serveur " + serv + " n'existe pas.";
        });
    }
	
	function getRandomInt(max) {
	  return Math.floor(Math.random() * Math.floor(max));
	}
	
	function createAvatarObject(){
		avatarStorage = localStorage.getItem("avatar");
		if (avatarStorage && avatarStorage != []){
			return JSON.parse(avatarStorage);
		}else{
			var head = getRandomInt(4)+1;
			var nose = getRandomInt(4)+1;
			var mouth = getRandomInt(4)+1;
			var eyes = getRandomInt(4)+1;
			var hair = getRandomInt(4)+1;
			return {head: head, nose: nose, mouth: mouth, eyes: eyes, hair: hair};
		}
	}
    
    function tryConnect(){
        pseudo = document.getElementById("pseudo").value;
        salon = document.getElementById("salon").value;
        if(pseudo != "" && !isNaN(salon)){
            if(connect()){
                var errorLog = document.getElementById("errorLog");
                errorLog.innerHTML = "";
            }
        }else{
            var errorLog = document.getElementById("errorLog");
            errorLog.innerHTML = "Veuillez remplir CORRECTEMENT les champs";
        }
    }
    
    function tryCreate(){
	pseudo = document.getElementById("pseudoCreer").value;
        nomSalon = document.getElementById("nomSalonCreer").value;
        if(pseudo != "" && nomSalon != ""){
            creerSalon();
            var errorLog = document.getElementById("errorLog");
            errorLog.innerHTML = "";
        }else{
            var errorLog = document.getElementById("errorLog");
            errorLog.innerHTML = "Veuillez remplir CORRECTEMENT les champs";
        }
    }
	
	function creerSalon(){
		pseudo = document.getElementById("pseudoCreer").value;
		nomSalon = document.getElementById("nomSalonCreer").value;
		var nbmanche = document.getElementById("nombreMancheCreer").value;
		var dureemanche = document.getElementById("dureeCreer").value;
        var glypheOptions = options.querySelectorAll('[name="radGlyphe"]');
        var checkedGlyphe;
        for(var i in glypheOptions){
            if(glypheOptions[i].checked){
                checkedGlyphe = glypheOptions[i];
                break;
            }
        }
        var glyphe = checkedGlyphe.getAttribute('value');
        regexes = [];
        var suprefixesOptions = options.querySelectorAll('#options input[type="checkbox"]');
        for(var i in suprefixesOptions){
            if(suprefixesOptions[i].checked){
                regexes.push(suprefixesOptions[i].value);
            }
        }
		socket.emit("create", {name: nomSalon, id: pseudo, manches: nbmanche, duree: dureemanche, regexp: regexes, alphabet: glyphe, avatar: avatar});
		socket.on("serverInfo", function(info){
			salon = info.id;
			var radio2 = document.getElementById("radio2");
			radio2.checked = true;
            document.getElementById("idSalon").innerHTML = "ID : " + salon;
            document.getElementById("nomSalon").innerHTML = info.name;
			var html = dureemanche + "s";
			document.getElementById("chrono").innerHTML = html;
			socket.on("gameInfo", receiveGameInfo);
		});
		jouer();
	}
	
	function enregistrerPreferences(){
		var nomSalon = document.getElementById("nomSalonCreer").value;
		var pseudo = document.getElementById("pseudoCreer").value;
		var mancheDuree = document.getElementById("dureeCreer").value;
		var nbManche = document.getElementById("nombreMancheCreer").value;
		
		var liste = [];
		
		var object_preferences = {nomSalon: nomSalon, pseudo: pseudo, mancheDuree: mancheDuree, nbManche: nbManche};
		liste.push(object_preferences);
		localStorage.setItem("preferences", JSON.stringify(liste));
		alert("Vos préférences ont bien été enregistrées");
	}
	
	function preferences(){
		var liste = localStorage.getItem("preferences");
		if (liste && liste != []){
			liste = JSON.parse(liste);
			document.getElementById("nomSalonCreer").value = liste[0].nomSalon;
			document.getElementById("pseudoCreer").value = liste[0].pseudo;
			document.getElementById("dureeCreer").value = liste[0].mancheDuree;
			document.getElementById("nombreMancheCreer").value = liste[0].nbManche;
		} else {
			alert("Vous n'avez pas encore défini de préférences de jeu");
		}
	}
	
	function jouer(){
		//QUITTER
// 		var quit = document.getElementById("btnQuitter");
// 		quit.addEventListener("click", function(e){
// 			socket.emit("logout");
// 			window.location.reload();
// 		});
		
		//RECUPERATION DES MSG
		socket.on("message", function(msg){
			
			var html = "";
			if (msg.from == null && msg.to == null){ //MSG QUAND UN UTILISATEUR CE CONNECTE
				var date = new Date(parseInt(msg.date));
				html += "<p class=\"system\">" + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds() + " - [admin] " + msg.text + "</p>";
				//lecture du message
				var enonciation = new SpeechSynthesisUtterance(msg.text);
				window.speechSynthesis.speak(enonciation);
			} else if (msg.to == null){ //MSG D'UN UTILISATEUR VERS TOUT LES AUTRES
				var date = new Date(parseInt(msg.date));
				if (msg.from == pseudo){
					html += "<p class=\"moi\">";
				} else {
					html += "<p>";
				}
				
				if (msg.text.match(/\[img:.*/)){
					var img = msg.text.match(/\[img:.*/).input;
					var n = img.substr(5);
					var lien = n.substr(0, n.length-1);
					msg.text = "<img src=\""+lien+"\">";
				}
				
				html += date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds() + " - " + msg.from + " : " + msg.text + "</p>";
				
			} else { //MP
				var date = new Date(parseInt(msg.date));
				if (msg.from == pseudo){
					html += "<p class=\"moi\">";
				} else {
					html += "<p class=\"mp\">";
				}
				html += date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds() + " - " + msg.from + " (à " + msg.to + ") : " + msg.text + "</p>";
			}
			
			var mess = document.querySelector("#chat main");
			mess.innerHTML += html;
		});
		
		//ENVOI D'UN MESSAGE
		var envoyer_msg = document.getElementById("btnEnvoyer");
        document.getElementById("monMessage").addEventListener("keypress", function(e){
            key = e.which || e.keyCode;
            if(key === 13){
                sendMessage();
            }
        });
        
		envoyer_msg.addEventListener("click", sendMessage());
        
        
        function sendMessage(){
			var mess = document.querySelector("input[id=monMessage]");
			var message = mess.value;
			if (message.length != 0){
				
				var reg = new RegExp(':lol:','g');
				message = message.replace(reg, '<span class="emoji rire"></span>');
				
				var reg2 = new RegExp(':zzz:','g');
				message = message.replace(reg2, '<span class="emoji zzz"></span>');
				
				var reg3 = new RegExp(':love:','g');
				message = message.replace(reg3, '<span class="emoji love"></span>');
				
				var reg4 = new RegExp(':holala:','g');
				message = message.replace(reg4, '<span class="emoji holala"></span>');
				
				var reg5 = new RegExp(':grrr:','g');
				message = message.replace(reg5, '<span class="emoji grrr"></span>');
				
				var reg6 = new RegExp(':triste:','g');
				message = message.replace(reg6, '<span class="emoji triste"></span>');
				
				var reg7 = new RegExp(':sourire:','g');
				message = message.replace(reg7, '<span class="emoji sourire"></span>');
				
				var reg8 = new RegExp(':banane:','g');
				message = message.replace(reg8, '<span class="emoji banane"></span>');
				
				var reg9 = new RegExp(':malade:','g');
				message = message.replace(reg9, '<span class="emoji malade"></span>');
				
				if (message.charAt(0) == '@'){
					var name = "";
					for (var i = 1; message.charAt(i) !== ' '; ++i){
						name += message.charAt(i);
					}
					socket.emit("message", {from: pseudo, to: name, text: message, date: Date.now()});
				} else {
					socket.emit("message", {from: pseudo, to: null, text: message, date: Date.now()});
				}
			}
			mess.value = "";
		}

		//IMAGES
		var imgs = document.getElementById("btnImage");
		imgs.addEventListener("click", function(e){
			
			var fen_img = document.getElementById("bcImage");
			fen_img.style.display = "block";
			
			var rech = document.getElementById("btnRechercher");
			rech.addEventListener("click", function(e){
				var rech_img = document.getElementById("recherche").value;
				
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200){
						var data = JSON.parse(this.responseText);
						var results = data.data;
						var size_r = results.length;
						var html = "";
						
						results.forEach(function(elem){
							html += "<img id=\""+elem.images.downsized.url+"\" src=\"" + elem.images.downsized.url + "\">";
						});
						
						var result = document.getElementById("bcResults");
						result.innerHTML = html;
						
						results.forEach(function(elem){
							var listen = document.getElementById(elem.images.downsized.url);
							listen.addEventListener("click", function(e){
								socket.emit("message", {from: pseudo, to: null, text: "[img:"+elem.images.downsized.url+"]", date: Date.now()});
								fen_img.style.display = "none";
							});
						});
					}
				}
				xhttp.open("get", "http://api.giphy.com/v1/gifs/search?q="+ rech_img + "&api_key=0X5obvHJHTxBVi92jfblPqrFbwtf1xig&limit=15", true);
				xhttp.send();
			});
			
			var fermer_fen = document.getElementById("btnFermer");
			fermer_fen.addEventListener("click", function(e){
				fen_img.style.display = "none";
			});
			
		});
		
		socket.on("reponseAide", function(kanas_ok){
			document.getElementById("kana").innerHTML = "&#" + kanas_ok;
		});
		
		socket.on("success", function(msg){
			if (msg != pseudo){
				var enonciation = new SpeechSynthesisUtterance(msg + "a trouvé la réponse");
				window.speechSynthesis.speak(enonciation);
			}
		});
		
		socket.on("nbEssaiMoins", function(nbEssaiRestant){
			if (nbEssaiRestant == 2){
				var enonciation = new SpeechSynthesisUtterance("C'est n'est pas la bonne réponse !");
			} else if (nbEssaiRestant == 1){
				var enonciation = new SpeechSynthesisUtterance("C'est pas la bonne réponse ! Tu es vraiment con");
			} else {
				var enonciation = new SpeechSynthesisUtterance("Ta perdu espèce de petit joueur");
			}
			window.speechSynthesis.speak(enonciation);
		});
		
		socket.on("depecheToi", function(){
			var enonciation = new SpeechSynthesisUtterance("Bouge ton cul espèce de limace !");
			window.speechSynthesis.speak(enonciation);
		});
		
		socket.on("bravo", function(){
			var enonciation = new SpeechSynthesisUtterance("Bravo mon champion !");
			window.speechSynthesis.speak(enonciation);
		});
	}
	
	
                
                
                /** gameInfo
                 *  { list: { "joueur1" : 10, "joueur2" : 15 }
                 *    drawer: "joueur1"
                 *    time: 59
                 *  }
                 */                
	function receiveGameInfo(gameInfo){
		var html = "";
		for(var elem in gameInfo.list){
			html += "<div class=\"joueur\"><p>" + elem + "</p>";
			if(gameInfo.drawer === elem){
				html+= "<div id=\"drawer\"></div>";
			}else if(gameInfo.successes[elem]){
				html+= "<div class=\"success\"></div>";
			}else{
				for(var i = 0; i < gameInfo.essais[elem]; ++i){
					html+= "<div class=\"essai\">·</div>";
				}
			}
			html+= "<canvas class=\"avatar\" width=\"100\" height=\"100\"></canvas>";
			html+="<div class=\"score\">"+Math.floor(gameInfo.list[elem])+"</div></div>";
		}
		document.getElementById("joueurs").innerHTML = html;
		var joueurs = document.querySelectorAll(".joueur");
		var order = 0;
		joueurs.forEach(function(elem){
			var ctxAvatar = elem.querySelector("canvas").getContext("2d");
			ctxAvatar.clearRect(0, 0, ctxAvatar.width, ctxAvatar.height);
			var name = elem.querySelector("p").innerText;
			ctxAvatar.width = 100;
			ctxAvatar.height = 100;
			var img = new Image();
			img.onload = function() {
				ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
				img = new Image();
				img.onload = function() {
					ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
					img = new Image();
					img.onload = function() {
						ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
						img = new Image();
						img.onload = function() {
							ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
							img = new Image();
							img.onload = function() {
								ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
							}
							img.src = "images/avatar/mouth"+ gameInfo.avatars[name].mouth + ".png";
						}
						img.src = "images/avatar/nose"+ gameInfo.avatars[name].nose + ".png";
					}
					img.src = "images/avatar/hair"+ gameInfo.avatars[name].hair + ".png";
				}
				img.src = "images/avatar/eyes"+ gameInfo.avatars[name].eyes + ".png";
			}
			img.src = "images/avatar/head"+ gameInfo.avatars[name].head + ".png";
		});
		document.getElementById("chrono").innerHTML = gameInfo.time + "s";
		document.getElementById("nb_manches").innerHTML = gameInfo.duration;
	}
	
	function drawThisAvatar(ctxAvatar, size, avatarObject){
		
		localStorage.setItem("avatar", JSON.stringify(avatarObject));
		
		ctxAvatar.width = size;
		ctxAvatar.height = size;
		ctxAvatar.clearRect(0, 0, ctxAvatar.width, ctxAvatar.height);
		var img = new Image();
		img.onload = function() {
			ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
			img = new Image();
			img.onload = function() {
				ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
				img = new Image();
				img.onload = function() {
					ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
					img = new Image();
					img.onload = function() {
						ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
						img = new Image();
						img.onload = function() {
							ctxAvatar.drawImage(this, 0, 0, ctxAvatar.width, ctxAvatar.height);
						}
						img.src = "images/avatar/mouth"+ avatarObject.mouth + ".png";
					}
					img.src = "images/avatar/nose"+ avatarObject.nose + ".png";
				}
				img.src = "images/avatar/hair"+ avatarObject.hair + ".png";
			}
			img.src = "images/avatar/eyes"+ avatarObject.eyes + ".png";
		}
		img.src = "images/avatar/head"+ avatarObject.head + ".png";
	}
		
	
	function montrerKana(){
		document.getElementById("kanaRadio").checked = true;
		socket.emit("aideDemandee", true);
	}
        
	function validerSyllabe(){
		var choix = "";
		var allSyllables = document.querySelectorAll("#syllabe label");
		for(var i = 0; i < 3; ++i){
			if(allSyllables[i].previousSibling.checked){
				choix = allSyllables[i].innerText;
			}
		}
		document.getElementById("syllabe").style.display = "none";
		var syllabeValidee = document.getElementById("syllabeValidee")
		syllabeValidee.innerHTML = choix;
		syllabeValidee.style.display = "block";
		
		socket.emit("validerSyllabe", choix);
	}
	
	function lireMessages(){
		
	}

    var pseudo;
    var salon;
	var nomSalon;
    var regexes = [];
    var avatar = createAvatarObject();
    		
	//CONNECT
	socket = io.connect('http://localhost:8080');
    document.getElementById("btnConnecter").addEventListener("click", tryConnect);
    document.getElementById("salon").addEventListener("keypress", function(e){
        key = e.which || e.keyCode;
        if(key === 13){
            tryConnect();
        }
    });
	document.getElementById("btnCreer").addEventListener("click", tryCreate);
    document.getElementById("pseudoCreer").addEventListener("keypress", function(e){
        key = e.which || e.keyCode;
        if(key === 13){
            tryCreate();
        }
    });
	
	document.getElementById("btnEnregistrer").addEventListener("click", enregistrerPreferences);
	document.getElementById("btnPreferences").addEventListener("click", preferences);
    
    document.getElementById("btnMontrerKana").addEventListener("click", montrerKana);
    document.getElementById("btnValiderSyllabe").addEventListener("click", validerSyllabe);
	
	document.getElementById("btnLeftHair").addEventListener("click", function(){
		avatar.hair--;
		if(avatar.hair == 0){
			avatar.hair = 4;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnRightHair").addEventListener("click", function(){
		avatar.hair++;
		if(avatar.hair == 5){
			avatar.hair = 1;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnLeftFace").addEventListener("click", function(){
		avatar.head--;
		if(avatar.head == 0){
			avatar.head = 4;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnRightFace").addEventListener("click", function(){
		avatar.head++;
		if(avatar.head == 5){
			avatar.head = 1;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnLeftEyes").addEventListener("click", function(){
		avatar.eyes--;
		if(avatar.eyes == 0){
			avatar.eyes = 4;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnRightEyes").addEventListener("click", function(){
		avatar.eyes++;
		if(avatar.eyes == 5){
			avatar.eyes = 1;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnLeftMouth").addEventListener("click", function(){
		avatar.mouth--;
		if(avatar.mouth == 0){
			avatar.mouth = 4;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnRightMouth").addEventListener("click", function(){
		avatar.mouth++;
		if(avatar.mouth == 5){
			avatar.mouth = 1;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnLeftNose").addEventListener("click", function(){
		avatar.nose--;
		if(avatar.nose == 0){
			avatar.nose = 4;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	document.getElementById("btnRightNose").addEventListener("click", function(){
		avatar.nose++;
		if(avatar.nose == 5){
			avatar.nose = 1;
		}
		drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
	});
	drawThisAvatar(document.getElementById("avatarSelectionCanvas").getContext("2d"), 300, avatar);
});
