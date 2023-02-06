<?php
	// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
   	 $dtaccess = new DataAccess();
    

	 //delete di klinik folio
	 $sql = "select a.fol_id, a.fol_nama from klinik.klinik_folio a left join klinik.klinik_biaya b on 
   a.id_biaya = b.biaya_id where fol_id not in (select id_fol from klinik.klinik_folio_pelaksana) and 
   a.id_reg = '$_POST[regId]' and b.biaya_jenis_sem <> 'KA'";
   
	 $result = $dtaccess->FetchAll($sql);
	  //print_r($result);
	  if (count($result)==0){
      require_once('proses_registrasi.php');
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('errorMsg'=> $result[0]['fol_nama'].' Pelaksana yang belum diisi.'));
		} 
	 
	 exit();      

?>