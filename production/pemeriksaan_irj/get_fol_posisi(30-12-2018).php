<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
		 $sql = "select * from klinik.klinik_folio_posisi where fol_posisi_id in (select id_folio_posisi from klinik.klinik_biaya_remunerasi) order by fol_posisi_nama asc";
	
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	echo $json;
	//echo $sql;
	
?>
	