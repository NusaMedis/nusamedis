<?php 
	require_once "api.php";
	
	$bpjs = new Bpjs();
	echo $bpjs->updateTanggalPulang($_POST['noSep'], date_db($_POST['reg_tanggal_pulang']).' '.$_POST['reg_waktu_pulang']);
?>