// Chargement des modules 
var express = require('express');
const fetch = require("node-fetch");
var app = express();
var server = app.listen(8080, function() {
    console.log("C'est parti ! En attente de connexion sur le port 8080...");
});

// Ecoute sur les websockets
var io = require('socket.io').listen(server);

// Configuration d'express pour utiliser le répertoire "public"
app.use(express.static('public'));
// set up to 
app.get('/', function(req, res) {
    res.sendFile(__dirname + '/public/index.html');
});

/*** Gestion des clients et des connexions ***/
var servers = {};
var alphabet;
kanas();

/**
 * Etat du jeu
 */

var State = {
	WAIT: 0,
    WAITCHOICE: 1,
    INGAME: 2,
    OVER: 3,
}

var game = State.WAIT;

//tirage des kanas
function random(max){
	return Math.floor((Math.random() * max - 1) + 1);
}

async function kanas(){
	var response = await fetch("http://localhost:8080/js/alphabet.json");
	
	if (response.ok) {
        alphabet = await response.json();
	}
}

function getRandomInt(max) {
  return Math.floor(Math.random() * Math.floor(max));
}

function getRandomKanas(thisAlphabet, regexes){
    var fullTableChar = Object.keys(alphabet[thisAlphabet]);
	var tableChar = [];
	for(var i in fullTableChar){
		for(var j in regexes){
			if(fullTableChar[i].match(regexes[j]) !== null){
				tableChar.push(fullTableChar[i]);
				break;
			}
		}
	}
    var res = [];
    for(var i = 0; i < 3; ++i){
        var randomValue = getRandomInt(tableChar.length);
        res.push(tableChar[randomValue]);
        tableChar.splice(randomValue,1);
    }
    return res;
}

var dureeManche = 0;

// Quand un client se connecte, on le note dans la console
io.on('connection', function (socket) {

    // message de debug
    console.log("Un client s'est connecté");
    var currentID = null;
    var currentServ = null;
    var nb_tour;
    
    /**
     *  Doit être la première action après la connexion.
     *  @param  log Object {id: "pseudo", server: "idDuServeur", avatar: "avatar"}
     */
    socket.on("login", function(log) {
        console.log("Début de la connexion...");
        var numberIfAlreadyUsed = 0;
        var serv = log.server;
		var id = log.id;
        var previousId = id;
        if(!(serv in servers)){
            console.log("Le serveur "+serv+" n'existe pas");
            socket.emit("serverError",serv);
        }else{
            while (servers[serv].clients[id]) {
                ++numberIfAlreadyUsed;
                id = previousId + "("+numberIfAlreadyUsed+")";
            }
            currentID = id;
            currentServ = serv;
            servers[serv].clients[currentID] = socket;
            servers[serv].essais[currentID] = 3;
            servers[serv].scores[currentID] = 0;
            servers[serv].successes[currentID] = false;
			servers[serv].avatars[currentID] = log.avatar;
            
            console.log("Nouvel utilisateur : " + currentID);
            // envoi d'un message de bienvenue à ce client
            socket.emit("bienvenue", id);
			if (game == State.WAITCHOICE){
				servers[serv].clients[currentID].emit("waitDebut");
			}
			socket.emit("infoServer", {name: servers[serv].name, id: servers[serv].id});
            // envoi aux autres clients 
            for(var i in servers[serv].clients){
                if(servers[serv].clients[i] != servers[serv].clients[currentID]){
                    servers[serv].clients[i].emit("message", { from: null, to: null, text: currentID + " a rejoint la partie", date: Date.now() } );
                }
            }
        }
        
        for (var i in servers[serv].clients){
			servers[serv].clients[i].emit("gameInfo", gameInfo());
		}
        
    });
    /**
     * 
     * @param create Object : {name: "nom", id: "pseudo", manches: "nbdemanche", duree: "dureedunemanche", regexp: regexes, alphabet: "nomAlphabet", avatar: string_avatar}
     */
    socket.on("create", function(create) {
		console.log(create.regexp);

		var serv = 1;
        while(serv in servers){
            ++serv;
        }
        console.log("creating room #"+serv);
        currentID = create.id;
        servers[serv] = {
            clients: {}, //id -> socket
            id: serv,
            name: ""+create.name,
            duration: isNaN(create.manches)? 60 : create.manches,
            chrono: isNaN(create.duree)? 60 : create.duree,
            regexes: [],
            syllabe: null,
            alphabet: create.alphabet,
            aide: false,
            drawer: currentID,
            scores: {}, //id -> score
            essais: {}, //id -> nombre d'essai restant
            successes: {}, //id -> la manche actuelle est réussie ou non
			avatars: {}
		}
		for(var i in create.regexp){
            servers[serv].regexes[i] = new RegExp("^"+create.regexp[i]+"$");
        }
		console.log(servers[serv].regexes);
        dureeManche = servers[serv].chrono;
		servers[serv].avatars[currentID] = create.avatar;
        servers[serv].clients[create.id] = socket;
        servers[serv].essais[currentID] = 3;
        servers[serv].successes[currentID] = false;
        servers[serv].scores[currentID] = 0;
        currentServ = serv;
        console.log(servers[serv]);
        socket.emit("serverInfo",{id: serv, name: create.name, manches: create.nbdemanche, duree: create.dureedunemanche});
        socket.emit("liste",Object.keys(servers[serv].clients), servers[serv].drawer);
		
		for (var i in servers[serv].clients){
			servers[serv].clients[i].emit("gameInfo", gameInfo());
		}
		
        jeu();
    });
	
	/**
	 * Fonction qui vérifie si tout les joueurs ont trouvé la réponse ou ont usé tous leurs essais
	 * 
	 */
	
	function verif_each_players_find(){
		for (var i in servers[currentServ].successes){
			if (!servers[currentServ].successes[i] && i != servers[currentServ].drawer && servers[currentServ].essais[i] > 0){
				return false;
			}
		}
		
		//Si il n'y a pas d'autres joueurs que le dessinateur
		if (Object.keys(servers[currentServ].clients).length == 1){
			return false;
		}
		
		return true;
	}

    function refreshInfo(){
		
		//messages pour la synthese vocale
		//Si il ne reste que 20 sec et au moins un essai restant, on lui dit de se dépecher
		if (servers[currentServ].chrono == 20){
			for (var i in servers[currentServ].clients){
				if (servers[currentServ].essais[i] >= 1 && i != servers[currentServ].drawer){
					servers[currentServ].clients[i].emit("depecheToi");
				}
			}
		}
		
		//Si il reste 25s et que personne n'a trouvé la réponse, on dit au dessinateur qu'il ne sait vraiment pas dessiner
		if (servers[currentServ].chrono == 25){
			servers[currentServ].clients[servers[currentServ].drawer].emit("dessineVite");
		} 
		
		//Si tout les joueurs ont trouvé le kana ou usé tous leurs essais, on peut changer de tour
		if (verif_each_players_find()){
			servers[currentServ].chrono = 0;
		}

		if (game == State.INGAME){
		
			//Quand le chrono est a 0, on peut changer de tour
			if(servers[currentServ].chrono <= 0){
				//changement de dessinateur
				var prec_drawer = false;
				var chang_drawer = false;
				for (var i in servers[currentServ].clients){
					
					if (prec_drawer){
						console.log("changement de drawer");
						servers[currentServ].drawer = i;
						for (var i in servers[currentServ].successes){
							servers[currentServ].successes[i] = false;
						}
						chang_drawer = true;
						game = State.WAIT;
						for (var i in servers[currentServ].clients){
							console.log("Remove img à : " + i);
							servers[currentServ].clients[i].emit("removeIMG");
						}
						break;
					}
					
					if (i == servers[currentServ].drawer){
						prec_drawer = true;
					}
				}
				
				//Si tout les joueurs ont dessinés, alors on peut changer de manche
				if (!chang_drawer){
					console.log("pas de changement de drawer, chang de manche");
					servers[currentServ].duration--;
					for (var i in servers[currentServ].successes){
						servers[currentServ].successes[i] = false;
					}
					game = State.WAIT;
					for (var i in servers[currentServ].clients){
						console.log("Remove img à : " + i);
						servers[currentServ].clients[i].emit("removeIMG");
					}
					console.log("nb manche restante : " + servers[currentServ].duration);
					//Si toute les manches sont passées, alors on peux arreter le jeu
					if (servers[currentServ].duration <= 0){
						console.log("if");
						for (var i in servers[currentServ].clients){
							//console.log("emit à : " + i);
							servers[currentServ].clients[i].emit("gameOver");
							game = State.OVER;
							return;
						}
					}
					for (var i in servers[currentServ].clients){
						servers[currentServ].drawer = i;
						break;
					}
				}
				
				//On réinitialise le chrono
				servers[currentServ].chrono = dureeManche;
				//On remet trois essais à chacun
				for (var i in servers[currentServ].successes){
					servers[currentServ].essais[i] = 3;
				}
				
				for (var i in servers[currentServ].clients){
					//console.log("Emit à : " + i + "drawer? " + i === servers[currentServ].drawer);
					servers[currentServ].clients[i].emit("gameInfo", gameInfo());
					servers[currentServ].clients[i].emit("dessinateur", i === servers[currentServ].drawer);
					return;
				}
				
				
			} else if(true || servers[currentServ].syllabe != null){
				servers[currentServ].chrono--;
				//console.log(servers[currentServ].chrono);
				for(var i in servers[currentServ].clients){
					servers[currentServ].clients[i].emit("gameInfo",gameInfo());
					/*console.log(i);
					console.log(i === servers[currentServ].drawer);*/
					servers[currentServ].clients[i].emit("dessinateur",i === servers[currentServ].drawer);
				}
			}
		} else if (game === State.WAIT){
			console.log("envoi des kanas");
			envoi_kanas_a_choisir();
		} else if (game === State.OVER){
			console.log("fin du jeu");
		}
    }
    
    /** exemple :
    * { list: { "joueur1" : 10, "joueur2" : 15 }
    *   essais: { "joueur1" : 2, "joueur2" : 3 }
    *    drawer: "joueur1"
    *    time: 59
    *  }
    */
    function gameInfo(){
		//console.log(servers[currentServ].scores);
        return {
            list: servers[currentServ].scores,
            essais: servers[currentServ].essais,
            drawer: servers[currentServ].drawer,
            successes: servers[currentServ].successes,
            time: servers[currentServ].chrono,
            duration: servers[currentServ].duration,
			avatars: servers[currentServ].avatars
        };
    }
        
    socket.on("image", function(img){
        if(!currentServ){
            return;
        }
        for(var i in servers[currentServ].clients){
            if( i != currentID){
                servers[currentServ].clients[i].emit("receivedImg", img);
            }
        }
    });
    
    /**
     *  Réception d'un message et transmission à tous.
     *  @param  msg     Object  le message à transférer à tous  
     */
    socket.on("message", function(msg) {
        console.log("Reçu message");
        // si jamais la date n'existe pas, on la rajoute
        msg.date = Date.now();
        // si message privé, envoi seulement au destinataire
		
		
        if (msg.to != null && servers[currentServ].clients[msg.to] !== undefined) {
            console.log(" --> message privé");
            servers[currentServ].clients[msg.to].emit("message", msg);
            if (msg.from != msg.to) {
                socket.emit("message", msg);
            }
        }
        else {
            console.log(servers[currentServ].syllabe);
            if(!servers[currentServ].successes[currentID] && servers[currentServ].syllabe != null && servers[currentServ].essais[currentID] > 0){
                if(msg.text.trim().toLowerCase() === servers[currentServ].syllabe.toLowerCase()){
                    incr_score();
                    console.log("Réussite de "+currentID);
                    for(var i in servers[currentServ].clients){
                        servers[currentServ].clients[i].emit("success", currentID);
                    }
                    servers[currentServ].clients[currentID].emit("bravo");
                    var changeTurn = true;
                    for(var i in servers[currentServ].clients){
                        if(!servers[currentServ].successes[i] && servers[currentServ].essais[i] === 0){
                            changeTurn = false;
                        }
                    }
                   /* if(changeTurn){
                        jeu();
                    }*/
                }else{
                    servers[currentServ].essais[currentID]--;
					//pour la synthèse vocale
					servers[currentServ].clients[currentID].emit("nbEssaiMoins", servers[currentServ].essais[currentID]);
                    console.log(" --> broadcast");
                    //console.log(servers[currentServ]);
                    for(var i in servers[currentServ].clients){
                        //console.log(servers[currentServ].clients[i]);
                        servers[currentServ].clients[i].emit("message", msg);
                    }
                }
                
				servers[currentServ].clients[i].emit("gameInfo", gameInfo());
            }else{
                console.log(" --> broadcast");
                //console.log(servers[currentServ]);
                for(var i in servers[currentServ].clients){
                    //console.log(servers[currentServ].clients[i]);
                    servers[currentServ].clients[i].emit("message", msg);
                }
            }
        }
    });
    
    socket.on("aideDemandee", function(val) {
       servers[currentServ].aide = true;
	   var kanas_ok = alphabet["hiragana"][servers[currentServ].syllabe];
	   socket.emit("reponseAide", kanas_ok);
    });
    
    function incr_score(){
        var position = 1;
        var size = 0;
        var score = 0;
        for(var i in servers[currentServ].successes){
            if(servers[currentServ].successes[i]){
                position++;
            }
            size++;
        }
        servers[currentServ].successes[currentID] = true;
        score += (2000 /(1 + position)); //1er : 1000, 2e : 666, 3e: 500
        score += servers[currentServ].chrono * 12;
        score *= (3 + servers[currentServ].essais[currentID])/4;//1 essai restant: *1, 2: *1.25, 3: *1.5
        servers[currentServ].scores[currentID] += score;
        var scoreDrawer = Math.floor((1.3 * score) / size);
        scoreDrawer += position * 127;
        if(servers[currentServ].aide){
            scoreDrawer = Math.floor(0.5*scoreDrawer);
        }
        servers[currentServ].scores[servers[currentServ].drawer] += scoreDrawer;
    }
    

    /** 
     *  Gestion des déconnexions
     */
    
    // fermeture
    socket.on("logout", function() {
        if (currentID && currentServ) {
            console.log("Sortie de l'utilisateur " + currentID);
            // envoi de l'information de déconnexion
            for(var i in servers[currentServ].clients){
                if(servers[currentServ].clients[i] != servers[currentServ].clients[currentID]){
                    servers[currentServ].clients[i].emit("message", { from: null, to: null, text: currentID + " a quitté le jeu", date: Date.now() } );
                }
            }
            // suppression de l'entrée
            delete servers[currentServ].clients[currentID];
            delete servers[currentServ].scores[currentID];
            delete servers[currentServ].essais[currentID];
            delete servers[currentServ].successes[currentID];
            if(servers[currentServ].clients.length == 0){
                delete servers[currentServ];
            }
			//TODO: changer de drawer si le drawer se déco
        }
    });
    
    // déconnexion de la socket
    socket.on("disconnect", function(reason) { 
        // si client était identifié
        if (currentID) {
            for(var i in servers[currentServ].clients){
                if(servers[currentServ].clients[i] != servers[currentServ].clients[currentID]){
                    servers[currentServ].clients[i].emit("message", { from: null, to: null, text: currentID + " vient de se déconnecter de l'application", date: Date.now() } );
                }
            }
            // suppression de l'entrée
            delete servers[currentServ].clients[currentID];
            delete servers[currentServ].scores[currentID];
            delete servers[currentServ].essais[currentID];
            delete servers[currentServ].successes[currentID];
            if(servers[currentServ].clients.length == 0){
                delete servers[currentServ];
            }
        }
        console.log("Client déconnecté");
    });

	
	/**
	 * Fonction qui choisi les kanas et qui les envoies au dessinateur
	 * 
	 */
	
	function envoi_kanas_a_choisir(){
		game = State.WAITCHOICE;
        var anAlphabet = servers[currentServ].alphabet;
        if(alphabet === "les2"){
            if(getRandomInt(2) == 0){
				anAlphabet = "katakana";
			}else{
				anAlphabet = "hiragana";
			}
        }
        console.log(servers[currentServ].regexes[0]);
		var kanas = getRandomKanas(anAlphabet, servers[currentServ].regexes);
		console.log("kanas  : " + kanas);

		servers[currentServ].clients[servers[currentServ].drawer].emit("choixKanas", kanas);
		console.log("emit choixKanas à : " + servers[currentServ].drawer + ", " + currentServ);
		
		for (var i in servers[currentServ].clients){
			if (i != servers[currentServ].drawer){
				servers[currentServ].clients[i].emit("wait");
			}
		}

	}
	
	socket.on("validerSyllabe", function(syllabe) {
        servers[currentServ].syllabe = syllabe;
		for (var i in servers[currentServ].clients){
			servers[currentServ].clients[i].emit("endWait");
		}
		game = State.INGAME;
		console.log("engame");
		
		
    });

    //JEU
    function jeu(){
		game = State.WAIT;
		setInterval(refreshInfo, 1000);

	}
	
    //JEU
    function choisir_dessinateur(){
        var nextIsTheDrawer = false;
        for (var i in servers[currentServ].clients){
           if(i === servers[currentServ].drawer){
                nextIsTheDrawer = true;
            }else if(nextIsTheDrawer){
                return i;
            }
        }
        //Sinon revient au début
        for (var i in servers[currentServ].clients){
            nb_tour--;
            if(nb_tour < 0){
                //TODO: FIN DU JEU
                return -1;
            }
            return i;
        }
    }
});
