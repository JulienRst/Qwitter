<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><!-- Trouver le moyen de le faire en htaccess -->
		<title>Qwitter | Inscription</title>
		<link rel="stylesheet" type="text/css" href="css/material.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/ripples.min.css">
		<link rel="stylesheet" type="text/css" href="css/main_style.css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div class="ctn-v-center">
			<section class="h-v-center">
				<article class="content">
					<div class="ctn_form">
						<h2>Inscription à Qwitter</h2>
						<p>Twitter, en moins bien</p>
						<form class="form-horizontal" method="get" action="php/registration.php" onsubmit="return checkFormRegister();">
							<div class="form-group">
								<input id="form_mail" type="mail" name="mail" class="form-control" value="" placeholder="adresse e-mail">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" value="" placeholder="mot de passe">
							</div>
							<div class="form-group">
								<input id="form_surname" type="text" name="surname" class="form-control" value="" placeholder="prénom">
							</div>
							<div class="form-group">
								<input id="form_name" type="text" name="name" class="form-control" value="" placeholder="nom">
							</div>
							<div class="form-group">
								<input id="form_date" type="date" name="birthday" class="form-control" value="" placeholder="date de naissance">
							</div>
							<div class="form-group">
								<input id="form_pseudo" type="text" name="pseudo" class="form-control" value="" placeholder="pseudonyme">
							</div>
							<div id="ctn_addition" class="form-group">
								<p><span id="alpha1"></span>+<span id="alpha2"></span> = </p>
								<input id="form_number" type="number" class="form-control" value="">
							</div>
							<div class="form-group">
								<span class="input-group-btn">
									 <button type="submit" class="btn btn-primary">
									 	Inscription
									 </button>
								</span>
							</div>
						</form>
						<?php session_start();
							if($_SESSION["error"] != NULL){
								echo('
									<div id="error_message" class="alert alert-danger alert-dismissible" role="alert">
						  				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						  				<strong>Attention ! </strong>'.$_SESSION["error"].'
									</div>
								');
								$_SESSION["error"] = NULL;
							}
						?>
					</div>
					<div id="ctn_ci_logo">
						<img src="datas/img/logo.png" title="Logo Qweeter"></div>
					</div>
				</article>
			</section>
		</div>
		<script type="text/javascript" src="js/library/jquery.js" title="jquery"></script>
		<script type="text/javascript" src="js/material.min.js" title="material"></script>
		<script type="text/javascript" src="js/library/bootstrap.js" title="bootstrap"></script
		<script type="text/javascript" src="js/ripples.min.js" title="ripples"></script>
		<script type="text/javascript" src="js/main_script.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){$.material.init();});
		</script>
	</body>
</html>