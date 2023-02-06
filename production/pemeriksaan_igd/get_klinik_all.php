<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
	 require_once($LIB."login.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $auth = new CAuth();
	 $userId = $auth->GetUserId();
	 
		// Data poli / klinik combo rujuk 
		 $sql = "select b.poli_nama,b.poli_id,b.poli_tipe
		 from global.global_auth_poli b
		 where (b.poli_tipe='J' or b.poli_tipe='R' or b.poli_tipe='L' or b.poli_tipe='P' or b.poli_tipe='N' or b.poli_tipe='M' or b.poli_tipe='E' or b.poli_tipe='G') order by poli_urut ASC";    
	
		 $rs = $dtaccess->Execute($sql);
		$dataPoli = $dtaccess->FetchAll($rs);
		//echo $sql;
		echo json_encode($dataPoli);
	
?>