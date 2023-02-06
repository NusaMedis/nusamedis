<?php 
	require_once "api.php";
	
	$bpjs = new Bpjs();
	echo $bpjs->refKabupaten($_GET["kdPropinsi"]);
?>