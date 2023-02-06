<?php 
	require_once "api.php";
	$param = $_GET["q"];
	$bpjs = new Bpjs();
	echo $bpjs->refDiagnosa($param);
?>