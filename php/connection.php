<?php
	include("library/lib.php");

	$db = new database;

	$u_mail = strtolower($_GET["mail"]);
	$u_mdp = $_GET["password"];

	session_start(); //Permet d'accéder au tableau de la session

	$connection = $db->testConnection($u_mail,$u_mdp);
	if($connection["connected"] == true){
		$_SESSION['connected'] = true;
		$_SESSION['idUserConnected'] = $connection["id"];

		header('location:../index.php');
		exit();
	} else {
		$_SESSION['error'] = $connection["error"];
		header('location:../connection.php');
	}

?>