<?php
	session_start();
	require('library/database.php');

	$key = $_GET['key'];
	$mail = $_GET['mail'];

	$db = new database();

	$result = $db->verifyUser($mail,$key);

	if($result["success"] == true){
		$_SESSION["error"] = "Validation accepté !";
	} else {
		$_SESSION["error"] = $result["error"];
	}
	header('location:../connection.php');
?>