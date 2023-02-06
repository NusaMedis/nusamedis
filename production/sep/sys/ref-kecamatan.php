<?php 
	require_once "api.php";
	
	$bpjs = new Bpjs();
	echo $bpjs->refKecamatan($_GET["kdKabupaten"]);
?>