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
	$rspns = [];


	$sql = "SELECT * from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR, $_GET['rawat_id']);
	$dataRawat = $dtaccess->Fetch($sql);


	$rspns['asd'] = $_GET["rawat_id"];
	$rspns['alasan_kontrol'] = $dataRawat["rawat_alasan"];
	$rspns['alasan_lain'] = $dataRawat["kontrol_alasan_lain"];
	$rspns['tgl_kembali'] = $dataRawat["rawat_rujuk_tanggal_kembali"];
	$rspns['tindak_lanjut'] = $dataRawat["rawat_tindak_lanjut"];

	
	echo json_encode($rspns);
	
?>
