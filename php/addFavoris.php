<?php
	require('library/database.php');
	$db = new database();
	session_start();
	$idUser = $_SESSION['idUserConnected'];
	$idMessage = $_GET['idMsg'];

	$date = new DateTime();
	$timestamp = $date->getTimestamp();

	$error = $db->addFavoris($idUser,$idMessage,$timestamp);
	
	$resp = array('idMsg' => $idMessage,'date' => $timestamp,'error' => $error);

	header('Content-type: text/javascript');
	echo json_encode($resp);
?>