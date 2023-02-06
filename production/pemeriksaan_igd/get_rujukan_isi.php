<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
     
  //INISIALISASI LIBRARY
  $dtaccess = new DataAccess();
  $tglSekarang = date("Y-m-d");
  $data=array();

  $sqlReg = "SELECT id_cust_usr, reg_id FROM klinik.klinik_registrasi WHERE id_poli = ".QuoteValue(DPE_CHAR, $_GET['id_poli'])." AND reg_tanggal = ".QuoteValue(DPE_DATE, date_db($_GET['reg_tanggal']))." AND reg_waktu = ".QuoteValue(DPE_CHAR, $_GET['reg_waktu']);
  $dataReg = $dtaccess->Fetch($sqlReg);

	$sql = "SELECT a.rujukan_tindakan_id, a.id_fol, b.biaya_nama FROM klinik.klinik_rujukan_tindakan a LEFT JOIN klinik.klinik_biaya b ON a.rujukan_tindakan_nama = b.biaya_id WHERE a.id_poli = ".QuoteValue(DPE_CHAR, $_GET['id_poli'])." AND id_reg = ".QuoteValue(DPE_CHAR, $dataReg['reg_id']);
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs); 
    
	for($i=0; $i < count($dataTable); $i++){    
  	$row = array(
      'tindakan_rujukan'   => $dataTable[$i]['biaya_nama'],
      'id_tindakan'   => $dataTable[$i]['rujukan_tindakan_id'],
      'folio_id'   => $dataTable[$i]['id_fol'],
    );
    $data[]=$row;
  }

  echo json_encode($data);
?>