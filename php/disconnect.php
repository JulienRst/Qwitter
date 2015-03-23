<?php
	session_start(); // permet d'acceder à la session
	session_unset(); // permet de de détruire le contenu de la session
	session_destroy();// detruit la session
	header('location:../connection.php');
	exit();
?>