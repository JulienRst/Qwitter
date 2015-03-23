<?php
	//On commence avec une page où les informations ne sont pas présentes 
	//Ce script récupère les informations
	require('library/lib.php');

	$db = new database();

	$idUser = $_SESSION['idUserConnected'];

	// $idProfil est l'id de l'utilisateur que l'on regarde
	if(!isset($_GET["idUserToSee"]) || $_GET["idUserToSee"] == null){
		$idProfil = $idUser;
	} else {
		$idProfil = $_GET["idUserToSee"];
	}	

	if($idProfil != $idUser){
		$displayAbo = $db->isAboTo($idUser,$idProfil);
	}

	// Pour J : ne pas faire deux fois : faire une condition si les deux sont égaux
	$profil = $db->getCurrentUser($idProfil);
	$user = $db->getCurrentUser($idUser);

	//On récupère les qwitts, les favoris et les reqwitts
	$tab_message = $db->getMessageFromUser($idProfil);
	$tab_message = array_merge($tab_message,$db->getFavorisFromUser($idProfil));
	$tab_message = array_merge($tab_message,$db->getReqwittFromUser($idProfil));


	function compare($a,$b){
		// Les dates apparaissent dans l'ordre decroissant
		return (-strcmp($a->getDate(),$b->getDate()));
	}
	//tri
	usort($tab_message,'compare');
?>