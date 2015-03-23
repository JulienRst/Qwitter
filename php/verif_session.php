<?php
	session_start();
	// Vérifie que l'utilisateur est connecté
	if(!$_SESSION || $_SESSION['connected'] == false){
		header('location:connection.php');
		exit();
	}
?>