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

	$sql = "SELECT rawat_ket,rawat_anamnesa, rawat_pemeriksaan_fisik, rawat_obgyn, rawat_status_lokalis FROM klinik.klinik_perawatan WHERE rawat_id = ".QuoteValue(DPE_CHAR, $_GET['rawat_id']);
	$dataObgyn = $dtaccess->Fetch($sql);

	$sql = "select anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_rawat = ".QuoteValue(DPE_CHAR,$_GET['rawat_id'])." and id_anamnesa = '765ebce5c9263aaff30e2e1ba3cb7a46'";
	$dataKeluhanGinek = $dtaccess->Fetch($sql);

	$sql = "select anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_rawat = ".QuoteValue(DPE_CHAR,$_GET['rawat_id'])." and id_anamnesa = '737d2ef46a15be00ddfa680b84d60556'";
	$dataKeluhanObstet = $dtaccess->Fetch($sql);

	$sql = "select * from klinik.klinik_anamnesa_tb where id_rawat=".QuoteValue(DPE_CHAR,$_GET['rawat_id']);
	$qAnamnesa = $dtaccess->FetchAll($sql);

	$sql_ttv = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_header_anamnesa = 'f003'";
	$dataTtv = $dtaccess->FetchAll($sql_ttv);

	$sql_cek = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'TIPE LAYANAN'";
	$dataCek = $dtaccess->Fetch($sql_cek);

	$sql_obs = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '737d2ef46a15be00ddfa680b84d60556'";
	$dataObs = $dtaccess->Fetch($sql_obs);

	$sql_pendarahan = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'c8c6ef91d4b0268ab846d8efad5e2654'";
	$dataPendarahan = $dtaccess->Fetch($sql_pendarahan);

	$sql_pendarahan_gin = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '7e308a206b467a3241ba713bee412412'";
	$dataPendarahanGin = $dtaccess->Fetch($sql_pendarahan_gin);

	$sql_letak_anak = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'cb155cdaee721f191fbdbfbd18b7d060'";
	$dataLetakAnak  = $dtaccess->Fetch($sql_letak_anak);

	$sql_flour_albus = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '72dfe88627051211cfb04e41d9dafb51'";
	$dataFlourAlbus  = $dtaccess->Fetch($sql_flour_albus);

	$sql_perut_sakit = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '301a5e61a9d3c4032d1f51b70e862d8a'";
	$dataPerutSakit  = $dtaccess->Fetch($sql_perut_sakit);

	$sql_kesadaran = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '41ec7ec353ff8a0607a4eade4d893023'";
	$dataKesadaran  = $dtaccess->Fetch($sql_kesadaran);

	$sql_keadaan = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'F33'";
	$dataKeadaanUmumPasien  = $dtaccess->Fetch($sql_keadaan);

	$sql_hpht = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '737d2ef46a15be00ddfa680b84d60556' AND id_anamnesa_detail = '973a5546f1c490e800f3395350855d51' and anamnesa_isi_detail_nilai not like '%Hari%' and anamnesa_isi_detail_nilai not like '% - %'";
	$dataHpht = $dtaccess->Fetch($sql_hpht);

	$sql_FU = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'cb155cdaee721f191fbdbfbd18b7d060' AND id_anamnesa_detail = 'a9e2977ec5f1ed34dac3c39d9283e410'";
	$dataFU = $dtaccess->Fetch($sql_FU);

	$sql_Amenore_bln = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = '82dc5e9d3c952e5666862fd8c8b0cb76' and id_anamnesa_pilihan_detail = 'a802573d09bbb08f65d5459bd575380c'";
	$dataAmenoreBln = $dtaccess->Fetch($sql_Amenore_bln);

	$sql_Amenore_hari = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = '82dc5e9d3c952e5666862fd8c8b0cb76' and id_anamnesa_pilihan_detail='3143f9c99c3d8f729da73a050296741a'";
	$dataAmenoreHari = $dtaccess->Fetch($sql_Amenore_hari);

	$sql_Haid_Lama = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = 'b4ef83174831b55d2607743cd25c1957'";
	$dataHaidLama = $dtaccess->Fetch($sql_Haid_Lama);

	$sql_Haid_Lama_Banyak = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = 'ca86df942e54c98c1a331be1d59395d4'";
	$dataHaidLamaBanyak = $dtaccess->Fetch($sql_Haid_Lama_Banyak);

	$sql_Hadi_Bulan = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = 'b4dcc501c7eab4cf963c03e11c3cfc62'";
	$dataHaidBulan = $dtaccess->Fetch($sql_Hadi_Bulan);

	$sql_Terus_menerus = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '8a1850c891157d120d9eb5488aec40ab' AND id_anamnesa_detail = '1d91f47d0eeaaeb32686d60f8bd31dcc'";
	$dataTerusmenerus = $dtaccess->Fetch($sql_Terus_menerus);

	$sql_Terus_menerus_hari = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '7e308a206b467a3241ba713bee412412' AND id_anamnesa_detail = '4f6a9237cbad41f5d334544bc46aa4b9'";
	$dataTerusmenerushari = $dtaccess->Fetch($sql_Terus_menerus_hari);

	$sql_Berapa_lama = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = '72dfe88627051211cfb04e41d9dafb51' AND id_anamnesa_detail = 'ba52e4e9a62a0a06db323219bb1099cf'";
	$dataBerapaLama = $dtaccess->Fetch($sql_Berapa_lama);

	$sql_tipe_layanan = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi where id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." AND id_anamnesa = 'TIPE LAYANAN'";
	$dataTipeLayanan = $dtaccess->Fetch($sql_tipe_layanan);

	$sql_G = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '8c58848aae6b61fc5c7f70e4659ebbe5'";
	$dataG = $dtaccess->Fetch($sql_G);

	$sql_P = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '3275c22d4d0c8e008b5deb78d68ba116'";
	$dataP = $dtaccess->Fetch($sql_P);

	$sql_A = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." and id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee' and id_anamnesa_detail = '86b6648a00e26029a0949b88a6bebf2d'";
	$dataA = $dtaccess->Fetch($sql_A);

	$sql_Warna = "select anamnesa_isi_detail_nilai from klinik.klinik_anamnesa_isi_detail where id_rawat = ".QuoteValue(DPE_CHAR, $_GET['rawat_id'])." and id_anamnesa = '72dfe88627051211cfb04e41d9dafb51' and id_anamnesa_detail = '6c04612aeac5422c8b2cf34a4034c90e'";
	$dataWarna = $dtaccess->Fetch($sql_Warna);

	$sql_Terapi	= "select item_nama, petunjuk_nama, terapi_jumlah_item,aturan_minum_nama,aturan_pakai_nama,jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi where id_rawat = ".QuoteValue(DPE_CHAR,$_GET['rawat_id'])." and id_rawat_terapi_racikan is null";
	$dataTerapi = $dtaccess->FetchAll($sql_Terapi);

	if ($dataHpht['anamnesa_isi_detail_nilai'] <> null) {
		$HPL = date('Y-m-d', strtotime('+280 days', strtotime(date_db($dataHpht['anamnesa_isi_detail_nilai']))));
	}

	$obs = explode(';', $dataObs['anamnesa_isi_nilai']);
	$mual = $obs[6];
	$muntah = $obs[7];
	$pusing = $obs[11];
	$perut_sakit = $obs[14];

	for ($i=0; $i < count($dataTerapi); $i++) { 
		$a = $dataTerapi[$i]['item_nama'];
		//$obat = explode('()', $obat);
		$terapi[$i] = "; ".$dataTerapi[$i]['item_nama']."( ".$dataTerapi[$i]['terapi_jumlah_item']." )"."( ".$dataTerapi[$i]['petunjuk_nama']." ) ".$dataTerapi[$i]['aturan_minum_nama']." ".$dataTerapi[$i]['aturan_pakai_nama']." ".$dataTerapi[$i]['jam_aturan_pakai_nama'];
	}

	$pendarah = explode(';', $dataPendarahan['anamnesa_isi_nilai']);
	$sedikit = $pendarah[0];
	$banyak = $pendarah[1];

	$pendarahGin = explode(';', $dataPendarahanGin['anamnesa_isi_nilai']);
	$sedikitGin = $pendarahGin[0];
	$banyakGin = $pendarahGin[1];

	$letak_anak = explode(';', $dataLetakAnak['anamnesa_isi_nilai']);
	$kepala = $letak_anak[0];
	$sungsang = $letak_anak[1];
	$ablique = $letak_anak[2];
	$lintang = $letak_anak[3];

	$FlourAlbus = explode(';', $dataFlourAlbus['anamnesa_isi_nilai']);
	$gatal = $FlourAlbus[0];
	$tidak_gatal = $FlourAlbus[1];
	$bau = $FlourAlbus[2];
	$tidak_bau = $FlourAlbus[3];
	$Lainnya = $FlourAlbus[5];
	$PerutSakit = explode(';', $dataPerutSakit['anamnesa_isi_nilai']);
	$perut_sakitt = $PerutSakit[0];
	$tumor = $PerutSakit[1];
	$myom_uteri = $PerutSakit[2];
	$kista_ovari = $PerutSakit[3];
	$ca_cx = $PerutSakit[4];
	$lainnya = $PerutSakit[5];
	$warna = $dataWarna['anamnesa_isi_detail_nilai'];

	$dataAnamnesa = array();
	$TTV = array();

	foreach ($qAnamnesa as $rs){
		$a = unserialize($rs['anamnesa_tb_isi']);
		foreach ($a as $k => $aa) {
			$row[ $k ] = $aa['value'];
		}
		$dataAnamnesa[] = $row;
	}

	// if ($dataTipeLayanan["anamnesa_isi_nilai"] == '0bstetri') {
	// 	$id[] = "keadaan_umum_pasien";
	// 	$id[] = "kesadaran";
	// 	$id[] = "tekanan_darah_sistole";
	// 	$id[] = "tekanan_darah_diastole";
	// 	$id[] = "nadi";
	// 	$id[] = "pernafasan";
	// 	$id[] = "suhu_badan";
	// 	$id[] = "berat_badan";
	// 	$id[] = "tinggi_badan";
	// 	$id[] = "mata";
	// 	$id[] = "leher_tvj";
	// 	$id[] = "leher";
	// 	$id[] = "payudara";	
	// }else{
	// 	$id[] = "keadaan_umum_pasien_ginek";
	// 	$id[] = "kesadaran_ginek";
	// 	$id[] = "tekanan_darah_sistole_ginek";
	// 	$id[] = "tekanan_darah_diastole_ginek";
	// 	$id[] = "nadi_ginek";
	// 	$id[] = "pernafasan_ginek";
	// 	$id[] = "suhu_badan_ginek";
	// 	$id[] = "berat_badan_ginek";
	// 	$id[] = "tinggi_badan_ginek";
	// 	$id[] = "mata_ginek";
	// 	$id[] = "leher_tvj_ginek";
	// 	$id[] = "leher_ginek";
	// 	$id[] = "payudara_ginek";
	// }
	

	// foreach ($dataTtv as $key => $val) {
	// 	$objective['id'] = $id[$key];
	// 	$objective['anamnesa_isi_nilai'] = $val['anamnesa_isi_nilai'];
	// 	array_push($TTV, $objective);
	// }

	$rspns['keluhamutama'] = $dataObgyn['rawat_anamnesa'];
	$rspns['status_lokalis'] = $dataObgyn['rawat_status_lokalis'];
	$rspns['cek'] = $dataCek['anamnesa_isi_nilai'];
	$rspns['keluhanGinek'] = $dataKeluhanGinek['anamnesa_isi_nilai'];
	$rspns['keluhanObstet'] = $dataKeluhanObstet['anamnesa_isi_nilai'];
	$rspns['layanan'] = $dataTipeLayanan['anamnesa_isi_nilai'];
	$rspns['hpht'] = $dataHpht['anamnesa_isi_detail_nilai'];
	$rspns['FU'] = $dataFU['anamnesa_isi_detail_nilai'];
	$rspns['AmenoreBln'] = $dataAmenoreBln['anamnesa_isi_detail_nilai'];
	$rspns['AmenoreHari'] = $dataAmenoreHari['anamnesa_isi_detail_nilai'];
	$rspns['HailLama'] = $dataHaidLama['anamnesa_isi_detail_nilai'];
	$rspns['HaidLamaBanyak'] = $dataHaidLamaBanyak['anamnesa_isi_detail_nilai'];
	$rspns['HaidBulan'] = $dataHaidBulan['anamnesa_isi_detail_nilai'];
	$rspns['TerusMenerus'] = $dataTerusmenerus['anamnesa_isi_detail_nilai'];
	$rspns['TerusMenerusHari'] = $dataTerusmenerushari['anamnesa_isi_detail_nilai'];
	$rspns['BerapaLama'] = $dataBerapaLama['anamnesa_isi_detail_nilai'];
	$rspns['hpl'] = date_db($HPL);
	$rspns['tb'] = $dataAnamnesa;
	$rspns['ttv'] = $TTV;
	$rspns['mual'] = $mual;
	$rspns['muntah'] = $muntah;
	$rspns['pusing'] = $pusing;
	$rspns['sedikit'] = $sedikit;
	$rspns['banyak'] = $banyak;
	$rspns['sedikitGin'] = $sedikitGin;
	$rspns['banyakGin'] = $banyakGin;
	$rspns['kepala'] = $kepala;
	$rspns['sungsang'] = $sungsang;
	$rspns['ablique'] = $ablique;
	$rspns['lintang'] = $lintang;
	$rspns['keadaan_umum_pasien'] = $dataKeadaanUmumPasien["anamnesa_isi_nilai"];
	$rspns['kesadaran'] = $dataKesadaran["anamnesa_isi_nilai"];
	$rspns['asd'] = $_GET["rawat_id"];

	if ($dataTipeLayanan['anamnesa_isi_nilai']!='') {
		if ($dataG['anamnesa_isi_detail_nilai']=='') {
			$rspns['abc'] = '0';
		}else{
			$rspns['abc'] = $dataG['anamnesa_isi_detail_nilai'];
		}
		if ($dataP['anamnesa_isi_detail_nilai']=='') {
			$rspns['abcd'] = '0';
		}else{
			$rspns['abcd'] = $dataP['anamnesa_isi_detail_nilai'];
		}
		if ($dataA['anamnesa_isi_detail_nilai']=='') {
			$rspns['abcde'] = '0';
		}else{
			$rspns['abcde'] = $dataA['anamnesa_isi_detail_nilai'];
		}
	}
	$rspns['gatal'] = $gatal;
	$rspns['tidak_gatal'] = $tidak_gatal;
	$rspns['bau'] = $bau;
	$rspns['tidak_bau'] = $tidak_bau;
	$rspns['warna'] = $warna;
	$rspns['Lainnya'] = $Lainnya;
	$rspns['perut_sakitt'] = $perut_sakitt;
	$rspns['tumor'] = $tumor;
	$rspns['myom_uteri'] = $myom_uteri;
	$rspns['kista_ovari'] = $kista_ovari;
	$rspns['ca_cx'] = $ca_cx;
	$rspns['lainnya'] = $lainnya;
	$rspns['perut_sakit'] = $perut_sakit;
	$rspns['rawat'] = $_GET['rawat_id'];
	if ($dataObgyn['rawat_ket']!='') {
	$rspns['planning'] = $dataObgyn['rawat_ket'];
	}else{
	$rspns['planning'] = ' ';
	}
	$rspns['dataObgyn'] = unserialize($dataObgyn['rawat_obgyn']);

	for ($i=0; $i < count($dataTerapi); $i++) { 
		$rspns['terapi'][$i] = $terapi[$i];
	}

	// echo '<pre>';
	// print_r($rspns);
	// echo '</pre>';

	echo json_encode($rspns);
?>
