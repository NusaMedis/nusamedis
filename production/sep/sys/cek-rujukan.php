<?php 
	require_once "api.php";
	
	$faskes = $_GET["rujukan_asalRujukan_"];
	$tipe_param = $_GET["tipe_param"];
	$key = $_GET["rujukan_noRujukan_"]; 

	$bpjs = new Bpjs();
	echo $bpjs->cekRujukan($key, $faskes, $tipe_param);
?>