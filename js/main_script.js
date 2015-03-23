function checkFormRegister(){
	var testMail = testSurname = testName = testDate = testPseudo = false;

	if(regMail.test($('#form_mail').val())){
		testMail = true;
	} else {
		testMail = false;
		alert('Mail Invalid');
	}
	if(regText.test($('#form_surname').val())){
		testSurname = true;
	} else {
		testSurname = false;
		alert('Surname Invalid');
	}
	if(regText.test($('#form_name').val())){
		testName = true;
	} else {
		testName = false;
		alert('Name Invalid');
	}
	if(regDate.test($('#form_date').val())){
		testDate = true;
	} else {
		testDate = false;
		alert('Date Invalid');
	}
	if(regPseudo.test($('#form_pseudo').val())){
		testPseudo = true;
	} else {
		testPseudo = false;
		alert('Pseudo Invalid');
	}

	if(testMail && testSurname && testName && testDate && testPseudo){
		if(parseInt($('#form_number').val()) == (parseInt($('#alpha1').html()) + parseInt($('#alpha2').html()))){
			return true;
		} else {
			alert('Vous devez résoudre l\'addtion pour prouver que vous n\'êtes pas un robot !');
			return false;
		}
	} else {
		return false;
	}
}

var regMail = new RegExp("[a-zA-Z\.0-9]+[@][a-zA-Z]+[\.][a-zA-Z]+","i");
var regText = new RegExp("[a-zA-Z]+","i");
var regPseudo = new RegExp("[a-zA-Z0-9\.\-]+","i");
var regDate = new RegExp("[0-9]{4}[-][0-9]{2}[-][0-9]{2}","i");

$(document).ready(function(){
	parametersout = false;
	popupparamout = false;

	if($('#alpha1')){
		$('#alpha1').html(Math.floor((Math.random() * 10) + 1));
		$('#alpha2').html(Math.floor((Math.random() * 10) + 1));
	}


	$("#post-qwitt textarea").keydown(function(e){
		if(e.keyCode == 13 && e.shiftKey == false){
			send_post($(this).val());
			$(this).val('');
			return false;
		}
	});
	$("#post-qwitt p").click(function(){
		send_post($("#post-qwitt textarea").val());
	});

	$("#user-finder").keyup(function(e){
		if($(this).val() !=""){

			$.getJSON(
				"php/userFinder.php",
			{
				research : $(this).val()
			},function(resp){
				$('#list-user-finder').html('');
				$.each(resp,function(i,item){
					$('#list-user-finder').html($('#list-user-finder').html()+"<div class='item-user-finder' rel="+item["id"]+"><div class='item-user-pic'><img src=datas/profil-pic/"+item["url_pic"]+"></div><div class='item-user-info'><p class='item-user-name'>"+ item["surname"]+" "+item["name"]+"</p><p class='item-user-pseudo'>@"+item["pseudo"]+"</p></div></div>");
				});

				$('.item-user-finder').click(function(){
					window.location = "profil.php?idUserToSee="+$(this).attr('rel');
				})
			});
		}

		// REMPLACER PAR DES PROMESSES !!
	});

	$('#nav-pic').click(function(){
		window.location = "profil.php?idUserToSee="+$(this).attr('rel');
	});

	$('#gear').click(function(){
		if(!parametersout){
			$('#parameters').fadeIn();
			parametersout = true;
		} 	else {
			$('#parameters').fadeOut();
			parametersout = false;
		}
	});

	$('#setParam').click(function(){
		if(!popupparamout){
			$('.ctn-popup').fadeIn();
			popupparamout = true;
		}
	});
	$('#annuler').click(function(){
		if(popupparamout){
			$('.ctn-popup').fadeOut();
			popupparamout = false;
		}
	});

	$('#annuler_rq').click(function(){
		$('.ctn-reqwitt').fadeOut();
	})

	$('#setDeco').click(function(){
		window.location = 'php/disconnect.php';
	});

	$('.icon-fav').click(function(){
		var idMsg = $(this).parent().attr('rel');
		send_fav(idMsg);
	});

	$('.icon-reqwitt').click(function(){
		var idMsg = $(this).parent().attr('rel');
		$('.ctn-reqwitt').fadeIn();
		$('.qwitt-to-reqwitt').html($(this).parent().parent().parent().html());
	});

	$('.ctn-profil-pic').mouseenter(function(){
		$('.hover-profil-pic').fadeIn();
	});
	$('.ctn-profil-pic').mouseleave(function(){
		$('.hover-profil-pic').fadeOut();
	});
	
	$('.ctn-profil-pic').click(function(){
		$('#input_file').click();
	})

	$('#reqwitt').click(function(){
		var message = $('#reqwitt-text').val();
		var idMsg = $('.qwitt-to-reqwitt .q-header-icon').attr("rel");
		send_reqwitt(message,idMsg);
		$('.ctn-reqwitt').fadeOut();
	});

	active_socialebutton();
});

function send_fav(id){
	$.getJSON(
		"php/addFavoris.php"
	,{
		idMsg : id
	});
}

function send_follow(idAbo){
	$.getJSON(
		"php/addFollow.php"
	,{
		idAbo : idAbo
	},function(resp){
		if(resp["result"] == "success"){
			$('#ctn-social-button').html('<button id="social-button" rel="abo"><img class="valid" src="datas/img/abo.png"><p>Abonné</p></button>');
			active_socialebutton();
			$('#nbFollow').html(resp['nbAbo']+' Abonnés');
		}
	});
}

function remove_follow(idAbo){
	$.getJSON(
		"php/removeFollow.php"
	,{
		idAbo : idAbo
	},function(resp){
		if(resp["result"] == "success"){
			$('#ctn-social-button').html('<button id="social-button" rel="noabo"><p>S\'abonner</p></button>');
			active_socialebutton();
			$('#nbFollow').html(resp['nbAbo']+' Abonnés');
		}
	});
}

function send_reqwitt(msg,idmsg){
	$.getJSON(
		"php/addReqwitt.php"
	,{
		idMsg : idmsg,
		message : msg
	});
}

function send_post(u_message){
	$.getJSON(
		"php/addMessage.php",
		{
			message : u_message
		},
		function(resp){
			$.post("php/launch_message.php",{
				object : resp
			},function(data,status){
				if(status == "success"){
					$('#qwitt-launcher').html($(data));
					$('#qwitt-launcher').css('height','auto');
					var new_height = $('#qwitt-launcher').height();
					$('#qwitt-launcher').css('height','0');
					$('#qwitt-launcher').animate({
						height:new_height
					},500,function(){
						$('#qwitt-launcher').html('');
						$('#qwitt-launcher').css('height','0');
						$(data).insertAfter($('#qwitt-launcher'));
					});
					$('#qwitt-count').html(resp["nbQwitt"]+' Qwitts');
				}
			});
		}
	);
}

function active_socialebutton(){
	$('#social-button').mouseenter(function(){
		if($(this).attr('rel') == 'abo'){
			$('#social-button p').html('Se désabonner');
			$('#social-button img').attr('src','datas/img/desabo.png');
		}
	});

	$('#social-button').mouseleave(function(){
		if($(this).attr('rel') == 'abo'){
			$('#social-button p').html('Abonné');
			$('#social-button img').attr('src','datas/img/abo.png');
		}
	});

	$('#social-button').click(function(){
		if($(this).attr('rel') == 'noabo'){
			send_follow($(this).parent().attr('rel'));
		}
		if($(this).attr('rel') == 'abo'){
			remove_follow($(this).parent().attr('rel'));
		}
	});
}