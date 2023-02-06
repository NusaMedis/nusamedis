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


// 	$b =  serialize($uy);

// 	foreach (unserialize($b) as $key => $value) {
// 		echo $key.'<br>';
// 	}

	

	$terapiPse = $_POST['terapi'];

	for($i = 0; $i < count($terapiPse); $i++){
		$terapis[] = str_replace("<br>", "", $terapiPse[$i]);
	}
	if($terapis){
		$terapi = implode("+", $terapis);
	}

	$forma = explode("&", $_POST['forma']);

	for($i = 0; $i < count($forma); $i++){
		$value = explode("=", $forma[$i]);

		$removePlus = str_replace("+", " ", $value[1]);
		$remooveOdd = str_replace("%2C", ",", $removePlus);
		$remooveOdd = str_replace("%3A", ":", $remooveOdd);
		$remooveOdd = str_replace("%2F", "/", $remooveOdd);
		$remooveOdd = str_replace("%3F", "?", $remooveOdd);
		$remooveOdd = str_replace("%0D%0A", " \n ", $remooveOdd);

		$form[$value[0]] = $remooveOdd;
	}

	//print_r($form);

	//$terapi = ($_POST['temp']) ? $_POST['terapiRaw'].$_POST['temp'] : $_POST['terapiRaw'];

	// if($form['alasan'] == '00') $form['alasan'] = $form['lain_lain'];
	if($form['alasan']) $lanjutan = "kontrol";
	else if($terapiPse) $lanjutan = "kembali ke FKTP";

	$tanggal_kontrol = date_format(date_create($form['tgl_kontrol']), 'Y-m-d');

	$sql = "UPDATE klinik.klinik_perawatan SET ";
  	$sql .= " rawat_tindak_lanjut = ".QuoteValue(DPE_CHAR, $lanjutan);
  	$sql .= ",  rawat_rujuk_tanggal_kembali = ".QuoteValue(DPE_CHAR, $tanggal_kontrol);
	$sql .= ", rawat_alasan = ".QuoteValue(DPE_CHAR,$form['alasan']);
	if($form['alasan'] == '00') $sql .= ", kontrol_alasan_lain = ".QuoteValue(DPE_CHAR,$form['lain_lain']);
	$sql .= " WHERE rawat_id = ".QuoteValue(DPE_CHAR, $form['asd']);

	$a = $dtaccess->execute($sql);

	$sql = "SELECT a.*, c.* from klinik.klinik_registrasi a
	left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
	left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
	where rawat_id = ".QuoteValue(DPE_CHAR, $form['asd']);
	//echo $sql;
	$dtPasien = $dtaccess->Fetch($sql);

	$tglAwalBulan = date('Y-m-01', strtotime($tanggal_kontrol));
	$tglAkhirBulan = date('Y-m-t', strtotime($tanggal_kontrol));

	$sql = "SELECT max(reg_kontrol_urut) as akhir from klinik.klinik_registrasi_kontrol where reg_tanggal >= '$tglAwalBulan' and reg_tanggal <= '$tglAkhirBulan'";
	$dataKontrolAkhir = $dtaccess->Fetch($sql);

	$urut = $dataKontrolAkhir['akhir'] + 1;
	$j = 4 - strlen($urut);
	$loop = str_repeat('0', $j);
	$nomorSurat = "RSIAM/RJ/".date('d')."/".date('m').$loop.$urut;

	$sql = "SELECT reg_id from klinik.klinik_registrasi_kontrol where reg_utama = ".QuoteValue(DPE_CHAR, $dtPasien['reg_id']);
	$dataKontrol = $dtaccess->Fetch($sql);

			

               $dbTable = "klinik.klinik_registrasi_kontrol";
               $dbField[0] = "reg_id";   // PK
               $dbField[1] = "reg_tanggal";
               $dbField[2] = "reg_waktu";
               $dbField[3] = "id_cust_usr";
               $dbField[4] = "reg_jenis_pasien";
               $dbField[5] = "id_poli";
               $dbField[6] = "id_dep";
               $dbField[7] = "id_dokter";
               $dbField[8] = "id_poli_asal";
               $dbField[9] = "reg_status";
               $dbField[10] = "id_pembayaran";
               $dbField[11] = "reg_when_update";
               $dbField[12] = "reg_utama";
               $dbField[13] = "reg_nomor_kontrol";
               $dbField[14] = "reg_kontrol_urut";

               if($dataKontrol['reg_id'] == ''){
               		$regId = $dtaccess->GetTransId();
           	   }
           	   else{
           	   	$regId = $dataKontrol['reg_id'];
           	   }

               $dbValue[0] = QuoteValue(DPE_CHAR, $regId);
               $dbValue[1] = QuoteValue(DPE_DATE,  $tanggal_kontrol);
               $dbValue[2] = QuoteValue(DPE_DATE, date('H:i:s'));
               $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC, $dtPasien["reg_jenis_pasien"]);
               $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien["id_poli"]);
               $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
               $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
               $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["id_poli"]);
               $dbValue[9] = QuoteValue(DPE_CHAR, 'E0');
               $dbValue[10] = QuoteValue(DPE_CHAR, "");
               $dbValue[11] = QuoteValue(DPE_DATE, $tanggal_kontrol);
               $dbValue[12] = QuoteValue(DPE_CHAR, $dtPasien['reg_id']);
               $dbValue[13] = QuoteValue(DPE_CHAR, $nomorSurat);
               $dbValue[14] = QuoteValue(DPE_NUMERIC, $urut);
              
               $dbKey[0] = 0; 
               $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
                if($dataKontrol['reg_id'] == ''){
               		$c = $dtmodel->insert() or die("insert  error");
           	   }
           	   else{
           	   	$c = $dtmodel->update() or die("update  error");
           	   }
               

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

           

	if ($a) {
		echo "Berhasil Disimpan";
		
	}
	//echo $sql;
?>
