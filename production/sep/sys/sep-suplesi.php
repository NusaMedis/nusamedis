<?php 
	require_once "api.php";
	$noKartu = $_GET["noKartu"];
	$tglLayanan = $_GET["tglLayanan"];
	
	$bpjs = new Bpjs();
	echo $bpjs->refFaskes($noKartu, $tglLayanan);
?>