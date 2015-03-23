<?php

	require('library/lib.php');
	$db = new database();
	session_start();
	$idUser = $_SESSION["idUserConnected"];

	if(isset($_FILES['url_pic']) && $_FILES['url_pic']["error"] == 0){ 
	    $dossier = '../datas/profil-pic/';
	    $fichier = basename($idUser.$_FILES['url_pic']['name']);
		$fichier = strtr($fichier,'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ','AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy'); 
		$fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
		$extensions = array('.png', '.gif', '.jpg', '.jpeg');
		$extension = strrchr($_FILES['url_pic']['name'], '.');
		if(!in_array($extension, $extensions)){
			exit();
		} else {
			if(move_uploaded_file($_FILES['url_pic']['tmp_name'], $dossier.$fichier)){
				echo 'Upload effectué avec succès !';
			} else {
				exit();
			}
		}
	} else {
		$fichier = '';
	}



	$new_profil = array("surname" => $_POST["surname"],"name" => $_POST["name"],"pseudo" => $_POST["pseudo"],"mail" => $_POST["mail"],"password" => $_POST["password"],"url_pic" => $fichier);

	$error = $db->changeProfil($new_profil,$idUser);
	
	//gestion de l'erreur à faire

	header('location:../profil.php');
	exit();
?>