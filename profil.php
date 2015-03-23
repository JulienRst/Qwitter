<?php include_once('php/verif_session.php') ?>
<?php include_once('php/makeProfile.php') ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Qwitter | Profil</title>
		<link rel="stylesheet" type="text/css" href="css/main_style.css">
		<link rel="stylesheet" type="text/css" href="css/material.css">
		<link rel="stylesheet" type="text/css" href="css/ripples.min.css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div id="wrap">
			<nav>
				<div class="nav-ctn-pic">
					<div rel="<?php echo($user["id"]); ?>" style="background-image:url(datas/profil-pic/<?php echo($user["url_pic"]);?>)" id="nav-pic"></div>
					<!--<img src="datas/img/logo_small.png" id="nav-logo">-->
				</div>
				<!--<div id="ntf">
					<p>Notifications</p>
					<div id="ntf-ctn">0</div>
				</div>-->
				<input id="user-finder" value=""/>
				<div id="list-user-finder"></div>
				<div id="ctn-gear" class="nav-ctn-pic">
					<img id="gear" src="datas/img/gear.png">
					<div id="parameters">
						<div id="setParam" class="param">
							<img src="datas/img/gear.png">
							<p>Paramètres</p>
						</div>
						<div id="setDeco" class="param">
							<img src="datas/img/off.png">
							<p>Déconnexion<p>
						</div>
					</div>
				</div>
			</nav>
			<header>
				<div id="ctn-profil-pic">
					<div class="profil-pic" style="background-image:url(datas/profil-pic/<?php echo($profil["url_pic"]);?>);"></div>
				</div>
				<div id="view-info">
					<div id="sub-view-info">
						<div id="sub-view-name" class="sub-view-info"><p><?php echo($profil["surname"]." ".$profil["name"]);?></p></div>
						<div id="sub-view-pseudo" class="sub-view-info"><p>(@<?php echo($profil["pseudo"])?>)</p></div>						
						<div id="sub-view-age" class="sub-view-info"><p><?php echo($profil["birthday"]);?> ans</p></div>
						<div id="ctn-relative">
							<div><p><span id="nbFollow"><?php echo($profil["nbFollow"]);?> Abonnés </span> | <span id="nbAbo"><?php echo($profil["nbAbo"]);?> Abonnements</span></p></div>
						</div>
						<div id="ctn-social-button" rel="<?php echo($profil['id']);?>">
							<?php 
								if($idProfil != $idUser){
									if($displayAbo){
										echo('<button id="social-button" rel="abo"><img class="valid" src="datas/img/abo.png"><p>Abonné</p></button>');
									} else {
										echo('<button id="social-button" rel="noabo"><p>S\'abonner</p></button>');
									}
								}
							?>
						</div>
						<div id="ctn-number">
							<img id="envelope" src="datas/img/envelope.png"> 
							<p id="qwitt-count"><?php echo($profil["nbQwitts"]);?> Qwitts</p>
							<img id="favoris" src="datas/img/favoris.png"> 
							<p><?php echo($profil["nbFavoris"]);?> Favoris</p>
							<img id="retweet" src="datas/img/retweet.png"> 
							<p><?php echo($profil["nbReqwitts"]);?> Reqwitts</p>
						</div>
					</div>
				</div>
			</header>

			<!--||| /////////////// SECTION \\\\\\\\\\\\\\\ ||| -->

			<section id="ctn-qwitts">
				<?php if($idUser == $idProfil){echo('
					<div id="post-qwitt">
						<textarea placeholder="Exprimez vous !"></textarea>
						<p>Envoyer >></p>
					</div>
					<div id="qwitt-launcher"></div>
					');}
				?>
				<?php
					foreach($tab_message as $qwitt){
						if($qwitt->getType() == 'normal'){
							$date = $qwitt->getDate();
							$message = $qwitt->getMessage();
							$nbFav = $qwitt->getNbFav();
							$nbReq = $qwitt->getNbReQwitt();
							$id = $qwitt->getIdMsg();
							//On prépare chaque variable qui va être utilisé dans l'affichage
							include("php/normal_qwitt.php");
						} else if($qwitt->getType() == 'favoris'){
							$date = $qwitt->getDate();
							$fUser = $qwitt->getUser();
							$favoris_pic = $fUser['url_pic'];
							$favoris_name = $fUser['surname'].' '.$fUser['name'];
							$favoris = $qwitt->getMessage();
							$favoris_date = $favoris->getDate();
							$favoris_message = $favoris->getMessage();
							$id = $favoris->getIdMsg();
							$nbFav = $favoris->getNbFav(); 
							$nbReq = $favoris->getNbReQwitt();
							include("php/favoris_qwitt.php");
						} else if($qwitt->getType() == 'reqwitt'){
							$date = $qwitt->getDate();
							$rUser = $qwitt->getUser();
							$reqwitt_pic = $rUser['url_pic'];
							$reqwitt_name = $rUser['surname'].' '.$rUser['name'];
							$reqwitt = $qwitt->getMessage();
							$reqwitt_date = $reqwitt->getDate();
							$reqwitt_message = $reqwitt->getMessage();
							$id = $reqwitt->getIdMsg();
							$nbFav = $reqwitt->getNbFav(); 
							$nbReq = $reqwitt->getNbReQwitt();
							$reqwitt_ofmessage = $qwitt->getReqwittMessage();
							include("php/reqwitt_qwitt.php");
						}
					}
				?>
			</section>
		</div>
		<div class="ctn-popup">
			<div class="ctn-v-center parameter-popup">
				<section class="h-v-center">
					<article class="content">
						<div class="ctn-profil-pic">
							<div class="profil-pic" style="background-image:url(datas/profil-pic/<?php echo($user["url_pic"]); ?>)"/></div>
							<div class="hover-profil-pic">
								<img class="hover-profil-photo" src="datas/img/profil-photo.png">
							</div>
						</div>
						<div class="modify">
							<div class="title">Modifier les informations</div>
							<form method=post action="php/changeProfil.php" enctype="multipart/form-data">
								<div class="form-group">
									<input id="form_mail" type="mail" name="mail" class="form-control" value="<?php echo($user["mail"]);?>" placeholder="adresse e-mail">
								</div>
								<div class="form-group">
									<input type="password" name="password" class="form-control" value=""placeholder="mot de passe">
								</div>
								<div class="form-group">
									<input id="form_surname" type="text" name="surname" class="form-control" value="<?php echo($user["surname"]);?>" placeholder="prénom">
								</div>
								<div class="form-group">
									<input id="form_name" type="text" name="name" class="form-control" value="<?php echo($user["name"]);?>" placeholder="nom">
								</div>
								<div class="form-group">
									<input id="form_pseudo" type="text" name="pseudo" class="form-control" value="<?php echo($user["pseudo"]);?>" placeholder="pseudonyme">
								</div>
								<input id="input_file" type="file" name="url_pic">
								<div class="form-group">
									<span class="input-group-btn">
										<button id="annuler" type="submit" class="btn btn-primary">
										 	Annuler
										</button>
										<button type="submit" class="btn btn-primary">
										 	Enregistrer
										</button>
									</span>
								</div>
							</form>
							<div id="error">

							</div>
						</div>
					</article>
				</section>
			</div>
		</div>
		<div class="ctn-reqwitt">
			<div class="ctn-v-center parameter-popup">
			<section class="h-v-center">
				<article class="content">
					<div class="reqwitt-title">Reqwittez ce qwitt !</div>
					<div class="post-qwitt">
						<textarea id="reqwitt-text" placeholder="Exprimez vous !"></textarea>
					</div>
					<div class="qwitt-to-reqwitt">
					</div>
					<div class="rq-ctn-btn">
						<button id="annuler_rq" type="submit" class="btn btn-primary">
						 	Annuler
						</button>
						<button id="reqwitt"type="submit" class="btn btn-primary">
						 	Reqwitter >>
						</button>
				</article>
			</section>
		</div>
		<script type="text/javascript" src="js/library/jquery.js" title="jquery"></script>
		<script type="text/javascript" src="js/main_script.js"></script>
	</body>
</html>