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
	$dataDalam = $dtaccess->Fetch($sql);

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

	  if($dataDalam['rawat_terapi']){

	  	$terapi = explode("+", $dataDalam['rawat_terapi']);

	  }
	  



	 if($dataDalam['rawat_pemeriksaan_fisik']){
	 	$pemeriksaan = $dataDalam['rawat_pemeriksaan_fisik'];

	 	$d = explode(' ; ', $pemeriksaan);
		$keadaan = array();
		for($i = 0; $i < count($d); $i++){
			$temp = explode(' : ', $d[$i]);
		    $keadaan[$temp[0]] = $temp[1];
		}
	 }


	
	$asmedDalam = unserialize($dataDalam['rawat_poli']);

	if($asmedDalam['diagnose_skr']){
		$diag_skr.= "  ".$asmedDalam['diagnose_skr'];
	}
	if($asmedDalam['keluhanUtama']){
		$dataDalam['rawat_anamnesa'] = $asmedDalam['keluhanUtama'];
	}
	$rspns['keluhamutama'] = $dataDalam['rawat_anamnesa'];
	$rspns['keadaan_umum_pasien'] = ($keadaan['Keadaan Umum pasien']) ? $keadaan['Keadaan Umum pasien'] : $asmedDalam['keadaan_umum_pasien'];
	// $rspns['kesadaran'] =  $asmedDalam['kesadaran'];
	$rspns['tekanan_darah_sistole'] = ($keadaan['Tekanan Darah Sistole']) ? $keadaan['Tekanan Darah Sistole'] : $asmedDalam['tekanan_darah_sistole'];
	$rspns['tekanan_darah_diastole'] = ($keadaan['Tekanan Darah Diastole']) ? $keadaan['Tekanan Darah Diastole'] : $asmedDalam['tekanan_darah_diastole'];
	$rspns['nadi'] = ($keadaan['Nadi']) ? $keadaan['Nadi'] : $asmedDalam['nadi'];
	$rspns['pernafasan'] = ($keadaan['Pernafasan']) ? $keadaan['Pernafasan'] : $asmedDalam['pernafasan'];
	$rspns['suhu_badan'] = ($keadaan['Suhu']) ? $keadaan['Suhu'] : $asmedDalam['suhu_badan'];
	$rspns['berat_badan'] = ($keadaan['Berat Badan']) ? $keadaan['Berat Badan'] : $asmedDalam['berat_badan'];
	$rspns['tinggi_badan'] = ($keadaan['Tinggi Badan']) ? $keadaan['Tinggi Badan'] : $asmedDalam['tinggi_badan'];
	$rspns['saturasi'] = ($keadaan['Saturasi']) ? $keadaan['Saturasi'] : $asmedDalam['saturasi'];
	$rspns['mata'] = $asmedDalam['mata'];
	$rspns['leher'] = $asmedDalam['leher'];
	$rspns['pemeriksaanPenunjang'] = $asmedDalam['pemeriksaanPenunjang'];
	$rspns['status_lokalis'] = $dataDalam['rawat_status_lokalis'];
	$rspns['ket_diagnosa_empat'] = $asmedDalam['ket_diagnosa_empat'];
	$rspns['planning_penatalaksanaan'] = $asmedDalam['planning_penatalaksanaan'];
	$rspns['lap_tindakan'] = $asmedDalam['lap_tindakan'];
	$rspns['diagnose_skr'] = $diag_skr;
	$rspns['terapi'] = $terapi;
	$rspns['terapiRaw'] = $dataDalam['rawat_terapi'];
	$rspns['asd'] = $_GET["rawat_id"];

	$rspns['memahamiMateri'] = $asmedDalam['memahamiMateri'];
	$rspns['butuhLeaflet'] = $asmedDalam['butuhLeaflet'];
	$rspns['membatasiMateri'] = $asmedDalam['membatasiMateri'];
	$rspns['pengulanganMateri'] = $asmedDalam['pengulanganMateri'];
	$rspns['bisaMengulang'] = $asmedDalam['bisaMengulang'];
	$rspns['lain_lainEdukasi'] = $asmedDalam['lain_lainEdukasi'];
	$rspns['lainEd_det'] = $asmedDalam['lainEd_det'];

	$rspns['diagnosa'] = $asmedDalam['diagnosa'];
	$rspns['penjelasan_penyakit'] = $asmedDalam['penjelasan_penyakit'];
	$rspns['pemeriksaan_penunjang'] = $asmedDalam['pemeriksaan_penunjang'];
	$rspns['terapi_edukasi'] = $asmedDalam['terapi_edukasi'];
	$rspns['tindakan_medis'] = $asmedDalam['tindakan_medis'];
	$rspns['prognosa'] = $$asmedDalam['prognosa'];
	$rspns['perkiraan_hari_rawat'] = $asmedDalam['perkiraan_hari_rawat'];
	$rspns['penjelasan_komplikasi'] = $asmedDalam['penjelasan_komplikasi'];
	$rspns['informed_concent'] = $asmedDalam['informed_concent'];
	$rspns['kondisi'] = $asmedDalam['kondisi'];
	$rspns['konsul'] = $asmedDalam['konsul'];
	$rspns['konsul_det'] = $asmedDalam['konsul_det'];
	$rspns['edukasi_pulang'] = $asmedDalam['edukasi_pulang'];
	$rspns['edukasi_lain'] = $asmedDalam['edukasi_lain'];
	$rspns['lain_det'] = $asmedDalam['lain_det'];

	for ($i=0; $i < count($dataTerapi); $i++) { 
		$rspns['terapi'][$i] = $terapi[$i];
	}
	echo json_encode($rspns);
	// echo '<pre>';
	// 	print_r($Obgyn);
	// echo '</pre>'
?>
