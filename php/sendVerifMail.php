<?php

	session_start();
	require('library/lib.php');

	$db = new database();

	$idUser = $_GET['id'];

	$user = $db->getCurrentUser($idUser);

	sendConfirmMail($user['mail'],$user['verifKey']);

	header('location:../connection.php');
	exit();

?>