<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	
	$sql = "select tingkat_kegawatan_id,tingkat_kegawatan_nama from global.global_tingkat_kegawatan order by urut asc";    		
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	echo $json;

	
?>
	