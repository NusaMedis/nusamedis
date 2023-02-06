<?php 
	require_once "api.php";
	$param = $_GET["param"];
	$jenis = $_GET["jenis"];
	
	$bpjs = new Bpjs();
	echo $bpjs->refFaskes($param, $jenis);
?>