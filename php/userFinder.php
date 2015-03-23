<?php

	require('library/database.php');
	$db = new database();

	if($_GET['research'] != ""){
		$tab_user = $db->getUserByName(strtolower($_GET['research']));
		echo json_encode($tab_user);
		exit();
	} else {
		//CODE ERREUR ICI
		exit();
	}
?>