<?php
	require("library/lib.php");
	$db = new database();
	session_start();
	$idUser = $_SESSION['idUserConnected'];
	$user = $db->getCurrentUser($idUser);
	$profil = $user;
	$object = $_POST["object"];
	$message = $object["message"];
	$date = timestampToDate($object["date"]);
	include("normal_qwitt.php");
?>