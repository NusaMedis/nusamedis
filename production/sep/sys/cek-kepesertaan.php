<?php 
	require_once "api.php";
	
	$param = $_GET["param"];
	$tglSep = $_GET['tglSep'];
	$bpjs = new Bpjs();
	echo $bpjs->cekKepesertaan($param, $tglSep);
?>