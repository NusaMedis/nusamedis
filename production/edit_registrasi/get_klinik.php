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
	
		// Data poli / klinik
		 $sql = "select b.poli_nama 
		 from global.global_auth_user_poli a 
		 left join global.global_auth_poli b on a.id_poli = b.poli_id
		 where a.id_usr = '$userId' order by poli_urut ASC";    
		 $rs = $dtaccess->Execute($sql);
		 $dataPoli = $dtaccess->FetchAll($rs);
		
		//echo $sql;
		echo json_encode($dataPoli);
?>