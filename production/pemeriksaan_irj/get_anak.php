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


	for ($i=0; $i < count($dataTerapi); $i++) { 
		$a = $dataTerapi[$i]['item_nama'];
		//$obat = explode('()', $obat);
		$terapi[$i] = $dataTerapi[$i]['item_nama']."( ".$dataTerapi[$i]['terapi_jumlah_item']." )"."( ".$dataTerapi[$i]['petunjuk_nama']." ) ".$dataTerapi[$i]['aturan_minum_nama']." ".$dataTerapi[$i]['aturan_pakai_nama']." ".$dataTerapi[$i]['jam_aturan_pakai_nama'];
	}

	
	
	$sql = "SELECT * from klinik.klinik_perawatan where rawat_id = ".QuoteValue(DPE_CHAR, $_GET['rawat_id']);
	$dataAnak = $dtaccess->Fetch($sql);

	$sql = "SELECT b.diagnosa_nomor, b.diagnosa_short_desc from klinik.klinik_perawatan_diagnosa a 
	  left join klinik.klinik_diagnosa b on a.id_diagnosa = b.diagnosa_id
	  where a.id_rawat = ". QuoteValue(DPE_CHAR, $_GET['rawat_id']);
	  $diagnose_skr = $dtaccess->FetchAll($sql);



	  if(count($diagnose_skr) > 0){
	    for($i = 0; $i < count($diagnose_skr); $i++){
	    $str[] = implode("-", $diagnose_skr[$i]);
	  }
	  $diag_skr = implode(" n/ ", $str);
	  }

	  $diag_skr = "";

	  if($dataAnak['rawat_terapi']){

	  	$terapi = explode("+", $dataAnak['rawat_terapi']);

	  }
	  



	 if($dataAnak['rawat_pemeriksaan_fisik']){
	 	$pemeriksaan = $dataAnak['rawat_pemeriksaan_fisik'];

	 	$d = explode(' ; ', $pemeriksaan);
		$keadaan = array();
		for($i = 0; $i < count($d); $i++){
			$temp = explode(' : ', $d[$i]);
		    $keadaan[$temp[0]] = $temp[1];
		}
	 }

	// foreach ($qAnamnesa as $rs){
	// 	$a = unserialize($rs['anamnesa_tb_isi']);
	// 	foreach ($a as $k => $aa) {
	// 		$row[ $k ] = $aa['value'];
	// 	}
	// 	$dataAnamnesa[] = $row;
	// }

	
	$asmedAnak = unserialize($dataAnak['rawat_anak']);

	if($asmedAnak['diagnose_skr']){
		$diag_skr.= "  ".$asmedAnak['diagnose_skr'];
	}
	if($asmedAnak['keluhanUtama']){
		$dataAnak['rawat_anamnesa'] = $asmedAnak['keluhanUtama'];
	}
	$rspns['keluhamutama'] = $dataAnak['rawat_anamnesa'];
	$rspns['keadaan_umum_pasien'] = ($keadaan['Keadaan Umum pasien']) ? $keadaan['Keadaan Umum pasien'] : $asmedAnak['keadaan_umum_pasien'];
	// $rspns['kesadaran'] =  $asmedAnak['kesadaran'];
	$rspns['tekanan_darah_sistole'] = ($keadaan['Tekanan Darah Sistole']) ? $keadaan['Tekanan Darah Sistole'] : $asmedAnak['tekanan_darah_sistole'];
	$rspns['tekanan_darah_diastole'] = ($keadaan['Tekanan Darah Diastole']) ? $keadaan['Tekanan Darah Diastole'] : $asmedAnak['tekanan_darah_diastole'];
	$rspns['nadi'] = ($keadaan['Nadi']) ? $keadaan['Nadi'] : $asmedAnak['nadi'];
	$rspns['pernafasan'] = ($keadaan['Pernafasan']) ? $keadaan['Pernafasan'] : $asmedAnak['pernafasan'];
	$rspns['suhu_badan'] = ($keadaan['Suhu']) ? $keadaan['Suhu'] : $asmedAnak['suhu_badan'];
	$rspns['berat_badan'] = ($keadaan['Berat Badan']) ? $keadaan['Berat Badan'] : $asmedAnak['berat_badan'];
	$rspns['tinggi_badan'] = ($keadaan['Tinggi Badan']) ? $keadaan['Tinggi Badan'] : $asmedAnak['tinggi_badan'];
	$rspns['lingkar_kepala'] = ($keadaan['Lingkar Kepala']) ? $keadaan['Lingkar Kepala'] : $asmedAnak['lingkar_kepala'];
	$rspns['mata'] = $asmedAnak['mata'];
	$rspns['leher'] = $asmedAnak['leher'];
	$rspns['pemeriksaanPenunjang'] = $asmedAnak['pemeriksaanPenunjang'];
	$rspns['status_lokalis'] = $dataAnak['rawat_status_lokalis'];
	$rspns['ket_diagnosa_empat'] = $asmedAnak['ket_diagnosa_empat'];
	$rspns['planning_penatalaksanaan'] = $asmedAnak['planning_penatalaksanaan'];
	$rspns['lap_tindakan'] = $asmedAnak['lap_tindakan'];
	$rspns['diagnose_skr'] = $diag_skr;
	$rspns['terapi'] = $terapi;
	$rspns['terapiRaw'] = $dataAnak['rawat_terapi'];
	$rspns['asd'] = $_GET["rawat_id"];

	$rspns['memahamiMateri'] = $asmedAnak['memahamiMateri'];
	$rspns['butuhLeaflet'] = $asmedAnak['butuhLeaflet'];
	$rspns['membatasiMateri'] = $asmedAnak['membatasiMateri'];
	$rspns['pengulanganMateri'] = $asmedAnak['pengulanganMateri'];
	$rspns['bisaMengulang'] = $asmedAnak['bisaMengulang'];
	$rspns['lain_lainEdukasi'] = $asmedAnak['lain_lainEdukasi'];
	$rspns['lainEd_det'] = $asmedAnak['lainEd_det'];

	$rspns['diagnosa'] = $asmedAnak['diagnosa'];
	$rspns['penjelasan_penyakit'] = $asmedAnak['penjelasan_penyakit'];
	$rspns['pemeriksaan_penunjang'] = $asmedAnak['pemeriksaan_penunjang'];
	$rspns['terapi_edukasi'] = $asmedAnak['terapi_edukasi'];
	$rspns['tindakan_medis'] = $asmedAnak['tindakan_medis'];
	$rspns['prognosa'] = $asmedAnak['prognosa'];
	$rspns['perkiraan_hari_rawat'] = $asmedAnak['perkiraan_hari_rawat'];
	$rspns['penjelasan_komplikasi'] = $asmedAnak['penjelasan_komplikasi'];
	$rspns['informed_concent'] = $asmedAnak['informed_concent'];
	$rspns['kondisi'] = $asmedAnak['kondisi'];
	$rspns['konsul'] = $asmedAnak['konsul'];
	$rspns['konsul_det'] = $asmedAnak['konsul_det'];
	$rspns['edukasi_pulang'] = $asmedAnak['edukasi_pulang'];
	$rspns['edukasi_lain'] = $asmedAnak['edukasi_lain'];
	$rspns['lain_det'] = $asmedAnak['lain_det'];

	for ($i=0; $i < count($dataTerapi); $i++) { 
		$rspns['terapi'][$i] = $terapi[$i];
	}
	echo json_encode($rspns);
	// echo '<pre>';
	// 	print_r($Obgyn);
	// echo '</pre>'
?>
