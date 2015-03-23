<?php
	include("library/lib.php");
	session_start();

	$db = new database;
	$u_mail = strtolower($_GET["mail"]);// strtolower : mettre en minuscule
	$u_mdp = $_GET["password"];
	$u_surname = strtolower($_GET["surname"]);
	$u_name = strtolower($_GET["name"]);
	$u_birthday = $_GET["birthday"];
	$u_pseudo = $_GET["pseudo"];

	if($db->isThisMailInDb($u_mail)){
		 $_SESSION["error"] = "Ce mail est déjà utilisé !";
		 header('location:../registration.php');
		 exit();
	} else {
		$key = uniqid();
		echo($key.'<br>');
		$db->insertNewUser($u_name,$u_surname,$u_pseudo,$u_birthday,$u_mail,$u_mdp,$key);
		sendConfirmMail($u_mail,$key);
		header('location:../connection.php');
		exit();
	}
?>