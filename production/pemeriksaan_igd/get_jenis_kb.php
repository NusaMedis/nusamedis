<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	
	$sql = "select jenis_kb_id,jenis_kb_nama from klinik.klinik_jenis_kb";    		
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	echo $json;

	
?>
	