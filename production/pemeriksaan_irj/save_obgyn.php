<?php
	// Library
	require_once("../penghubung.inc.php");
	require_once($LIB."login.php");
	require_once($LIB."datamodel.php");
	require_once($LIB."dateLib.php");
	require_once($LIB."currency.php");
	require_once($LIB."encrypt.php");
	require_once($LIB."tampilan.php");

	// Inisialisasi Lib
	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
	$auth = new CAuth();
	$enc = new textEncrypt();
	$userData = $auth->GetUserData();
	$userName = $auth->GetUserName();
	$userId = $auth->GetUserId();
	$depId = $auth->GetDepId();
	$poliId = $auth->IdPoli();
	$tglSekarang = date("d-m-Y");
	$depLowest = $auth->GetDepLowest();

	$sql = "SELECT * from klinik.klinik_perawatan_diagnosa where id_rawat = '".$_POST['asd']."'";

     $dataDiagnosa = $dtaccess->FetchAll($sql);

     // if(count($dataDiagnosa) > 0){

	$sql = "UPDATE klinik.klinik_perawatan SET rawat_obgyn = ".QuoteValue(DPE_CHAR, serialize($_POST));
  if ($_POST['keluhanUtama'] == '') {
	$sql .= ", rawat_anamnesa = ".QuoteValue(DPE_CHAR, $_POST['keluhan_utama']);
	$sql .= ",  rawat_diagnosa_utama = ".QuoteValue(DPE_CHAR, $_POST['analisa_diagnosaa']); //Ginekologi
	$sql .= ", rawat_ket = ".QuoteValue(DPE_CHAR,$_POST['planning_penatalaksanaan_ginek']);
  }else{
  	$sql .= ", rawat_anamnesa = ".QuoteValue(DPE_CHAR, $_POST['keluhanUtama']);
  	$sql .= ",  rawat_diagnosa_utama = ".QuoteValue(DPE_CHAR, $_POST['ket_diagnosa_empat']); // Obstetri
	$sql .= ", rawat_ket = ".QuoteValue(DPE_CHAR,$_POST['planning_penatalaksanaan']);
  }
  	$sql .= ", waktu_asmed = ".QuoteValue(DPE_CHAR, date("Y-m-d H:i:s"));
	$sql .= ", rawat_status_lokalis = ".QuoteValue(DPE_CHAR,$_POST['status_lokalis'])." WHERE rawat_id = ".QuoteValue(DPE_CHAR, $_POST['asd']);
	$a = $dtaccess->execute($sql);
	if ($a) {
		echo "Berhasil Disimpan";
		
	}
	
	// }else{
 //          echo "Diagnosa Belum Diisi";
 //     }
	
?>
