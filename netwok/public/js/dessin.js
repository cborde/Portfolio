document.addEventListener("DOMContentLoaded",function(_e){
    
    function mouseMove(e){
		if (dessinateur){
			var rect = e.target.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;
			printCursor(x,y);
			executeTool(x,y);
		}
    }
    
    function clearCanvas(e){
		if(dessinateur){
			ctxDessin.clearRect(0,0,overlay.width,overlay.height);
		}
    }
    
    function executeTool(x,y){
        if(mouseBtn === ClickState.DOWN){
            switch(currentTool){
                case Tool.BRUSH:{
                    ctxDessin.beginPath();
                    ctxDessin.moveTo(originX,originY);
                    ctxDessin.lineCap = 'round';
                    ctxDessin.lineWidth = brushSize*2;
                    ctxDessin.lineJoin = 'round';
                    ctxDessin.strokeStyle = 'white';
                    ctxDessin.lineTo(x,y);
                    ctxDessin.stroke();
                    originX = x;
                    originY = y;
                    break;
                }
                case Tool.ERASER:{
                    ctxDessin.clearRect(x-brushSize/2,y-brushSize/2,brushSize,brushSize);
                    break;
                }
                case Tool.LINE:{
                    break;
                }
            }
        }
    }
    
    function mouseOut(e){
		if (dessinateur){
			ctxOverlay.clearRect(0,0,overlay.width,overlay.height);
			mouseBtn = ClickState.UP;
		}
    }
    
    function mouseUp(e){
		if (dessinateur){
			var rect = e.target.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;
			mouseBtn = ClickState.UP;
			if(currentTool === Tool.LINE){
				ctxOverlay.clearRect(0,0,overlay.width,overlay.height);
				ctxDessin.beginPath();
				ctxDessin.moveTo(originX,originY);
				ctxDessin.lineCap = 'round';
				ctxDessin.lineWidth = brushSize*2;
				ctxDessin.lineJoin = 'round';
				ctxDessin.strokeStyle = 'white';
				ctxDessin.lineTo(x,y);
				ctxDessin.stroke();
			}else if(currentTool === Tool.RECTANGLE){
				ctxOverlay.clearRect(0,0,overlay.width,overlay.height);
				ctxDessin.beginPath();
				ctxDessin.moveTo(originX,originY);
				ctxDessin.lineCap = 'square';
				ctxDessin.lineWidth = brushSize;
				ctxDessin.lineJoin = 'square';
				ctxDessin.fillStyle = 'white';
				ctxDessin.fillRect(originX,originY,x-originX,y-originY);
			}
			
			var img = dessin.toDataURL().replace(/^data:image\/(png|jpg);base64,/, "");
			socket.emit("image", img);
		}
    }
    
    function mouseDown(e){
		if (dessinateur){
			var rect = e.target.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;
			mouseBtn = ClickState.DOWN;
			ctxDessin.beginPath();
			originX = x;
			originY = y;
			executeTool(x,y);
		}
    }
    
    function printCursor(x,y){
        ctxOverlay.clearRect(0,0,overlay.width,overlay.height);
        switch(currentTool){
            case Tool.BRUSH:{
                ctxOverlay.beginPath();
                ctxOverlay.arc(x,y,brushSize,0,2 * Math.PI, false);
                ctxOverlay.fillStyle = 'white';
                ctxOverlay.fill();
                break;
            }
            case Tool.ERASER:{
                ctxOverlay.strokeStyle = 'black';
                ctxOverlay.lineWidth = 2;
                ctxOverlay.lineCap = 'square';
                ctxOverlay.lineJoin = 'square';
                ctxOverlay.strokeRect(x-brushSize/2,y-brushSize/2,brushSize,brushSize);
                break;
            }
            case Tool.LINE:{
                if(mouseBtn !== ClickState.DOWN){
                    ctxOverlay.beginPath();
                    ctxOverlay.arc(x,y,brushSize,0,2 * Math.PI, false);
                    ctxOverlay.fillStyle = 'white';
                    ctxOverlay.fill();
                }else{
                    ctxOverlay.beginPath();
                    ctxOverlay.moveTo(originX,originY);
                    ctxOverlay.lineCap = 'round';
                    ctxOverlay.lineWidth = brushSize*2;
                    ctxOverlay.lineJoin = 'round';
                    ctxOverlay.strokeStyle = 'white';
                    ctxOverlay.lineTo(x,y);
                    ctxOverlay.stroke();   
                }
                break;
            }
            case Tool.RECTANGLE:{
                if(mouseBtn !== ClickState.DOWN){
                    ctxOverlay.fillStyle = 'white';
                    ctxOverlay.lineCap = 'square';
                    ctxOverlay.lineJoin = 'square';
                    ctxOverlay.fillRect(x-brushSize/2,y-brushSize/2,brushSize,brushSize);
                }else{
                    ctxOverlay.beginPath();
                    ctxOverlay.moveTo(originX,originY);
                    ctxOverlay.lineCap = 'square';
                    ctxOverlay.lineWidth = brushSize;
                    ctxOverlay.lineJoin = 'square';
                    ctxOverlay.fillStyle = 'white';
                    ctxOverlay.fillRect(originX,originY,x-originX,y-originY);
                }
                break;
            }
        }
    }
    
    function updateTools(e){
		if (dessinateur){
			mouseBtn = ClickState.UP;
			var tools = document.querySelectorAll("[name=\"radCommande\"]");
			for(var i in tools){
				if(tools[i].checked){
					switch(tools[i].id){
						case "tracer":{
							currentTool = Tool.BRUSH;
							break;
						}
						case "gommer":{
							currentTool = Tool.ERASER;
							break;
						}
						case "ligne":{
							currentTool = Tool.LINE;
							break;
						}
						case "rectangle":{
							currentTool = Tool.RECTANGLE;
							break;
						}
					}
				}
			}
			brushSize = document.getElementById("size").value;
		}
    }
    
    var Tool = {
        BRUSH: 1,
        ERASER: 2,
        LINE: 3,
        RECTANGLE: 4,
    }
    var ClickState = {
        UP: 1,
        DOWN: 2,
    }
    var currentTool = Tool.BRUSH;
    var mouseBtn = ClickState.UP;
    var brushSize = 10;
    var ctxOverlay = overlay.getContext("2d");
    var ctxDessin = dessin.getContext("2d");
    ctxDessin.width = 500;
    ctxDessin.height = 500;
    var originX = 0;
    var originY = 0;
	var dessinateur = false;

    overlay.addEventListener("mouseenter", updateTools);
    overlay.addEventListener("mousemove", mouseMove);
    overlay.addEventListener("mouseout", mouseOut);
    overlay.addEventListener("mouseup", mouseUp);
    overlay.addEventListener("mousedown", mouseDown);
    document.getElementById("new").addEventListener("click", clearCanvas);
	var toolBox = document.getElementById("toolbox");
	var canvasDessin = document.getElementById("overlay");
	toolBox.style.display = "none";
    canvasDessin.style.cursor = "default";
	
	//RECEPTION D'UNE IMAGE ET AFFICHAGE DANS LE CANVAS
	socket.on("receivedImg", function(imgrcv){
		var img = new Image();
        img.onload = function() {
			ctxDessin.clearRect(0, 0, ctxDessin.width, ctxDessin.height);
			ctxDessin.drawImage(img, 0, 0, ctxDessin.width, ctxDessin.height);
        };
		img.src = "data:image/png;base64," + imgrcv;
	});
	
	socket.on("gameOver", function(){
		dessinateur = false;
		alert("Fin de la partie");
		window.location.reload();
	});
	
	socket.on("choixKanas", function(kanas){

 		var choix = document.getElementById("syllabe");
		choix.style.display = "block";
		
		var enonciation = new SpeechSynthesisUtterance("Choisi une syllabe !");
		window.speechSynthesis.speak(enonciation);

		for (var i = 0; i < 3; ++i){
			var id_choix = "ch"+i;
			var choix = document.getElementById(id_choix);
			choix.innerHTML = kanas[i];
		}
	});
	
	socket.on("wait", function(){
		var wait = document.getElementById("wait");
		wait.style.display = "block";
		var enonciation = new SpeechSynthesisUtterance("Tiens toi prêt !");
		window.speechSynthesis.speak(enonciation);
	});
	
	socket.on("endWait", function(){
		document.getElementById("wait").style.display = "none";
		document.getElementById("waitDebut").style.display = "none";
		var enonciation = new SpeechSynthesisUtterance("C'est parti !");
		window.speechSynthesis.speak(enonciation);
	});
	
	socket.on("waitDebut", function(){
		document.getElementById("waitDebut").style.display = "block";
	});
	
	socket.on("removeIMG", function(){
		ctxDessin.clearRect(0,0,overlay.width,overlay.height);
		document.getElementById("kana").innerHTML = "";
	});
	
	socket.on("dessinateur", function(dess){
		dessinateur = dess;
        if (dessinateur){
            toolBox.style.display = "flex";
            canvasDessin.style.cursor = "none";
        } else {
            toolBox.style.display = "none";
            canvasDessin.style.cursor = "default";
        }
	});
	
	socket.on("dessineVite", function(){
		var enonciation = new SpeechSynthesisUtterance("Tu dessine vraiment comme une merde !");
		window.speechSynthesis.speak(enonciation);
	});
});
