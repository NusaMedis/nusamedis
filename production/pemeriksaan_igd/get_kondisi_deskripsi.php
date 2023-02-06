<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		 
	  $sql = "select * from global.global_kondisi_akhir_deskripsi";
    $sql .=" where id_kondisi_akhir_pasien =".QuoteValue(DPE_CHAR,$_GET['id']);
    $sql .=" order by kondisi_akhir_deskripsi_nama asc";
      //echo $sql;
	  
	  //die($sql);

	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	
	$json = json_encode($dataTable);
	echo $json;
?>