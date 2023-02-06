<?php 
	require_once "api.php";

	$bpjs = new Bpjs();
	echo $bpjs->deleteKontrol($_GET["noKontrol"]);
?>