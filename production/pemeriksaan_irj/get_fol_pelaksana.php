<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
		 $sql = "select a.*,b.usr_name, c.fol_posisi_nama
			from klinik.klinik_folio_pelaksana a
			left join global.global_auth_user b on a.id_usr = b.usr_id   
			left join klinik.klinik_folio_posisi c on a.fol_pelaksana_tipe = c.fol_posisi_id
			where a.id_fol= '$_POST[fol_id]'"; 
	
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	echo $json;
	//echo $sql;
	
?>
	