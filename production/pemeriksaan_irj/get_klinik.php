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

		// Data poli / klinik untuk filter
		 $sql = "select b.poli_nama 
		 from global.global_auth_user_poli a 
		 left join global.global_auth_poli b on a.id_poli = b.poli_id
		 where a.id_usr = '$userId' and (b.poli_tipe='J' or b.poli_tipe='R' or b.poli_tipe='L') order by poli_urut ASC";    
		 $rs = $dtaccess->Execute($sql);
		$dataPoli = $dtaccess->FetchAll($rs);
		//echo $sql;
		echo json_encode($dataPoli);
?>