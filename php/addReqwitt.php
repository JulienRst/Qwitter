<?php
	require('library/lib.php');
	$db = new database();
	session_start();
	$idUser = $_SESSION['idUserConnected'];
	$idMessage = $_GET['idMsg'];
	$message = $_GET['message'];

	$date = new DateTime();
	$timestamp = $date->getTimestamp();

	$error = $db->addReqwitt($idUser,$idMessage,$timestamp,$message);
	
	$resp = array('idMsg' => $idMessage,'date' => $timestamp,'error' => $error);

	header('Content-type: text/javascript');
	echo json_encode($resp);
?>