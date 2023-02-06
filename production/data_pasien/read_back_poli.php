<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php"); 
     
     // Inisialisasi Lib
	   $dtaccess = new DataAccess();
       $auth = new CAuth();
	   $depId = $auth->GetDepId(); 

		$sql = "SELECT * FROM klinik.klinik_kamar where kamar_id = ".QuoteValue(DPE_CHAR,$_GET['kamar_id']);
		$d = $dtaccess->Fetch($sql);
		
		$hasil = array('id_poli' => $d['id_poli'] );
		echo json_encode($hasil);

?>
