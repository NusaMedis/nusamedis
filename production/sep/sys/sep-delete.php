<?php 
	require_once "api.php";

	$bpjs = new Bpjs();
	echo $bpjs->deleteSep($_GET["no_sep"]);
?>