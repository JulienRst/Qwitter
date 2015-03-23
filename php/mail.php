<?php
	$to      = $mail;
	$subject = 'Nouveau message laissé sur le site !';
	$message = 'Message du site : Projet Web /r/n';
	$message.= 'De : '.$_GET['email'].' /r/n';
	$message.= 'Demande de prestation : '.$_GET['type_prestation'].'/r/n';
	$message.= 'Message : '.$_GET['message'].'/r/n';
	$headers = 'MIME-Version: 1.0';

    mail($to, $subject, $message, $headers);
?>