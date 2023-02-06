<?php 
	require_once "api.php";
	
	$key = $_GET["noka__"]; 
	$param = $_GET["faskes_rujukan"]; 

	$bpjs = new Bpjs();
	echo $bpjs->listRujukan($key,$param);
?>