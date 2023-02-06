<?php


     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  
		$sql = "select kondisi_akhir_pasien_id,kondisi_akhir_pasien_nama
				from global.global_kondisi_akhir_pasien 
				order by kondisi_akhir_pasien_id asc";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs);
		$json1 = json_encode($dataTable);
		echo $json1;
		

?>