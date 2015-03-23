var xhr = null; 
var desti = null;

function getXhr(){
	if(window.XMLHttpRequest) 
		xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){
		try {
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			xhr = new ActiveXObject("Microsoft.XMLHTTP");
		}
	} else { // XMLHttpRequest non supporte par le navigateur 
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		xhr = false; 
	} 
	return xhr;
}

function go(file,dest){
	desti = dest;
	var xhr = getXhr();
	// On definit ce qu'on va faire quand on aura la reponse
	xhr.onreadystatechange = function(){
		test(desti);
	}
	xhr.open("GET",file,true);
	xhr.send();
}

function test(desti){
	// On ne fait quelque chose que si on a tout recu et que le serveur est ok
	if(xhr.readyState == 4 && xhr.status == 200){
		leselect = xhr.responseText;
		// On se sert de innerHTML pour modifier le contenu
		if(desti != null){
			document.getElementById(desti).innerHTML = leselect;
			document_ready();
		}
	} else {
		var a = setTimeout('test(desti);',100);
	}
}