<?php 
	require_once "api.php";
	
	$bpjs = new Bpjs();
	echo $bpjs->poliKontrol($_GET["jnsLayanan"],  $_GET["nomor"],date_db($_GET["tglLayanan"]));
?>