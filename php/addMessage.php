<?php
	require('library/lib.php');
	$db = new database();

	session_start();

	$idUser = $_SESSION['idUserConnected'];

	$message = $_GET['message'];
	$date = new DateTime(); // recupère l'heure
	$timestamp = $date->getTimestamp(); //transforme l'heure en timestamp

	$result = $db->addMessage($idUser,$timestamp,$message);

	$nbQwitt = $db->getNumberOf("qwitt",$idUser);//recalcul le nombre de qwitt posté

	$resp = array('message' => $message,'date' => $timestamp,'nbQwitt' => $nbQwitt);
	header('Content-type: text/javascript');
	echo json_encode($resp);
?>