<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><!-- Trouver le moyen de le faire en htaccess -->
		<title>Qwitter | Connexion</title>
		<link rel="stylesheet" type="text/css" href="css/material.css">
		<link rel="stylesheet" type="text/css" href="css/ripples.min.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/main_style.css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div class="ctn-v-center">
			<section class="h-v-center">
				<article class="content">
					<div class="ctn_form">
						<h2>Connexion à Qwitter</h2>
						<p>Twitter, en moins bien</p>
						<form class="form-horizontal" method="get" action="php/connection.php">
							<div class="form-group">
								<input type="mail" name="mail" class="form-control" placeholder="adresse e-mail">
							</div>
							<div class="form-group">
								<input type="password" name="password" class="form-control" placeholder="mot de passe">
							</div>
							<div class="form-group">
								<span class="input-group-btn">
									 <button type="submit" class="btn btn-primary">
									 	Connexion
									 </button>
								</span>
							</div>
						</form>
						<p id="register">Pas encore inscrit ? <a href="registration.php">S'incrire à Qwitter >></a></p>
					</div>
					<div id="ctn_ci_logo">
						<img src="datas/img/logo.png" title="Logo Qweeter"></div>
					</div>
				</article>
			</section>
		</div>
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
		<script type="text/javascript" src="js/library/jquery.js" title="jquery"></script>
		<script type="text/javascript" src="js/library/bootstrap.js" title="jquery"></script>
		<script type="text/javascript" src="js/material.min.js" title="material"></script>
		<script type="text/javascript" src="js/ripples.min.js" title="ripples"></script>
		<script type="text/javascript" src="js/main_script.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){$.material.init();});
		</script>
	</body>
</html>