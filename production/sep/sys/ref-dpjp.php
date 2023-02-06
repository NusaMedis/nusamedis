<?php 
	require_once "api.php";
	
	$bpjs = new Bpjs();
	echo $bpjs->refDPJP($_GET["jnsLayanan"], date_db($_GET["tglLayanan"]), $_GET["poli_bpjs"]);
?>