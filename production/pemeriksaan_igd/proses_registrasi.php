<?php
# LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

# INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();

//konfigurasi rumah sakit
$sql = "select * from global.global_departemen where dep_id = " . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

#cari waktu di buat terakhir 
$sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_reg = " . QuoteValue(DPE_CHAR, $_POST["regId"]);
$sql .= " order by klinik_waktu_tunggu_when_create desc ";
$rs = $dtaccess->Fetch($sql);
$durasi = durasi($rs["prev"], date("Y-m-d H:i:s"));
$durasiDetik = durasiDetik($rs["prev"], date("Y-m-d H:i:s"));

#data Reg
$sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST['regId']);
$dataRegistrasi = $dtaccess->Fetch($sql);

# ---- insert ke registrasi ----
$dbTable = "klinik.klinik_registrasi";

$dbField[0] = "reg_id";   // PK
$dbField[1] = "reg_status_kondisi";
$dbField[2] = "id_dokter";
$dbField[3] = "reg_tingkat_kegawatan";
$dbField[4] = "reg_status";
$dbField[5] = "reg_status_kondisi_deskripsi";
$dbField[6] = "reg_tanggal_pulang";
$dbField[7] = "reg_waktu_pulang";
$dbField[8] = "reg_diagnosa_igd";
$dbField[9] = "id_jenis_kb";
$dbField[10] = "id_dokter_2";

$dbValue[0] = QuoteValue(DPE_CHAR, $_POST["regId"]);
$dbValue[1] = QuoteValue(DPE_CHAR, $_POST["reg_status_kondisi"]);
$dbValue[2] = QuoteValue(DPE_CHAR, $_POST["dokter"]);
$dbValue[3] = QuoteValue(DPE_CHAR, $_POST["tingkat_kegawatan"]);
$dbValue[4] = QuoteValue(DPE_CHAR, "G2");
$dbValue[5] = QuoteValue(DPE_CHAR, $_POST["reg_status_kondisi_deskripsi"]);
$dbValue[6] = QuoteValue(DPE_DATE, date('Y-m-d'));
$dbValue[7] = QuoteValue(DPE_DATE, date('H:i:s'));
$dbValue[8] = QuoteValue(DPE_CHAR, $_POST["diagnosa"]);
$dbValue[9] = QuoteValue(DPE_CHAR, $_POST["jenis_kb_id"]);
$dbValue[10] = QuoteValue(DPE_CHAR, $_POST["dokter_2"]);

//print_r($dbValue); die();
$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
$dtmodel->Update() or die("update  error");
//print_r($dbValue); die();
unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);


$sql = "update klinik.klinik_registrasi set reg_tanggal_pulang = ".QuoteValue(DPE_DATE, date('Y-m-d')).", reg_waktu_pulang = ".QuoteValue(DPE_DATE, date('H:i:s'))." where reg_utama = ".QuoteValue(DPE_CHAR, $_POST['regId']);
$result = $dtaccess->Execute($sql);

$sql = "update klinik.klinik_folio set tipe_rawat = 'G' where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataRegistrasi['id_pembayaran']);
$result = $dtaccess->Execute($sql);

// ---- insert ke klinik waktu tunggu ----
$dbTable = "klinik.klinik_waktu_tunggu";

$dbField[0] = "klinik_waktu_tunggu_id";   // PK
$dbField[1] = "id_reg";
$dbField[2] = "id_cust_usr";
$dbField[3] = "klinik_waktu_tunggu_when_create";
$dbField[4] = "klinik_waktu_tunggu_who_create";
$dbField[5] = "klinik_waktu_tunggu_status";
$dbField[6] = "klinik_waktu_tunggu_status_keterangan";
$dbField[7] = "id_poli";
$dbField[8] = "id_waktu_tunggu_status";
$dbField[9] = "klinik_waktu_tunggu_durasi";
$dbField[10] = "klinik_waktu_tunggu_durasi_detik";

$waktuTungguId = $dtaccess->GetTransID();
$dbValue[0] = QuoteValue(DPE_CHAR, $waktuTungguId);
$dbValue[1] = QuoteValue(DPE_CHAR, $_POST['regId']);
$dbValue[2] = QuoteValue(DPE_CHAR, $_POST["cust_usr_id"]);
$dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
$dbValue[4] = QuoteValue(DPE_CHAR, $userLogin["name"]);
$dbValue[5] = QuoteValue(DPE_CHAR, "G2");
$dbValue[6] = QuoteValue(DPE_CHAR, "Pasien Selesai Dilayani");
$dbValue[7] = QuoteValue(DPE_CHAR, $_POST["id_poli"]);
$dbValue[8] = QuoteValue(DPE_CHAR, "G2");
$dbValue[9] = QuoteValue(DPE_CHAR, $durasi);
$dbValue[10] = QuoteValue(DPE_NUMERIC, $durasiDetik);
//print_r($dbValue); die();
$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
$dtmodel->Insert() or die("insert  error");
unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey); 


if($_POST["reg_status_kondisi"]!="2"){

	 $sql="update klinik.klinik_registrasi_buffer set reg_buffer_batal='n',is_daftar='n' where id_cust_usr = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_id"])." and reg_buffer_tanggal = " . QuoteValue(DPE_DATE, date_db($_POST["reg_tanggal"]));
	 // echo $sql;
  $rs = $dtaccess->Execute($sql);

	#cek apakah sudah diinsert atau belum
$sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$_POST["regId"]);
$rs = $dtaccess->Fetch($sql);



	if (!$rs["prev"]) {
		#cari waktu di buat terakhir
		$sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$_POST["regId"]);
		$sql .= " order by klinik_waktu_tunggu_when_create desc ";
		$rs = $dtaccess->Fetch($sql);
		$durasi = durasi($rs["prev"], date("Y-m-d H:i:s"));
		$durasiDetik = durasiDetik($rs["prev"], date("Y-m-d H:i:s"));
		// echo $rs["prev"]; 
		//echo $durasi;
		//die();
  
  
  
  
		// ---- insert ke klinik waktu tunggu ----
		$dbTable = "klinik.klinik_waktu_tunggu";
  
		$dbField[0] = "klinik_waktu_tunggu_id";   // PK
		$dbField[1] = "id_reg";
		$dbField[2] = "id_cust_usr";
		$dbField[3] = "klinik_waktu_tunggu_when_create";
		$dbField[4] = "klinik_waktu_tunggu_who_create";
		$dbField[5] = "klinik_waktu_tunggu_status";
		$dbField[6] = "klinik_waktu_tunggu_status_keterangan";
		$dbField[7] = "id_poli";
		$dbField[8] = "klinik_waktu_tunggu_durasi";
		$dbField[9] = "klinik_waktu_tunggu_durasi_detik";
		$dbField[10] = "id_waktu_tunggu_status";
  
		$waktuTungguId = $dtaccess->GetTransID();
  
  
		$dbValue[0] = QuoteValue(DPE_CHAR, $waktuTungguId);
		$dbValue[1] = QuoteValue(DPE_CHAR, $_POST["regId"]);
		$dbValue[2] = QuoteValue(DPE_CHAR, $_POST["cust_usr_id"]);
		$dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
		$dbValue[4] = QuoteValue(DPE_CHAR, $userName);
		$dbValue[5] = QuoteValue(DPE_CHAR, "K0");
		$dbValue[6] = QuoteValue(DPE_CHAR, "Pasien di kasir");
		$dbValue[7] = QuoteValue(DPE_CHAR, $_GET["id_poli"]);
		$dbValue[8] = QuoteValue(DPE_CHAR, $durasi);
		$dbValue[9] = QuoteValue(DPE_NUMERIC, $durasiDetik);
		$dbValue[10] = QuoteValue(DPE_CHAR, "K0");
  
		//print_r($dbValue); die();
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
  
		$dtmodel->Insert() or die("insert  error");
  
		//update status di klinik registrasi
		// $sql = "update klinik.klinik_registrasi set reg_status = 'E1' where reg_id = ".
		// QuoteValue(DPE_CHAR,$regId);
		// $rs = $dtaccess->Execute($sql);
		//echo $sql;
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);
			   }


 }
 


 

   // }



		 /*
###############################  Begin kondisi akhir dirawat ##############################################
		 if($_POST["reg_status_kondisi"] == '1') { #jika kondisi akhir dirawat
			$reg_id_baru = $dtaccess->GetTransID(); 
			$reg_id_lama = $_POST["regId"];
			$bayar_id_baru = $dtaccess->GetTransID();
			
			# cari data registrasi lama
			$sql = " select * from klinik.klinik_registrasi where reg_id = '$reg_id_lama' ";
			$dataReg = $dtaccess->Fetch($sql);
			
			# cari data folio lama
			$sql = " select * from klinik.klinik_folio where fol_lunas = 'n' and id_reg = '$reg_id_lama' ";
			$dataFol = $dtaccess->FetchAll($sql);
			
			  ######   insert ke registrasi #######
			  $dbTable = "klinik.klinik_registrasi";
		 
			  $dbField[0] = "reg_id";   // PK
			  $dbField[1] = "reg_tanggal";
			  $dbField[2] = "reg_waktu";
			  $dbField[3] = "id_cust_usr";
			  $dbField[4] = "reg_status";
			  $dbField[5] = "reg_who_update";
			  $dbField[6] = "reg_when_update";
			  $dbField[7] = "reg_jenis_pasien";
			  $dbField[8] = "reg_status_pasien";
			  $dbField[9] = "reg_rujukan_id";         
			  $dbField[10] = "reg_tipe_rawat";
			  $dbField[11] = "id_dep";
			  $dbField[12] = "reg_shift";
			  $dbField[13] = "reg_tipe_layanan";
			  $dbField[14] = "reg_sebab_sakit";
			  $dbField[15] = "reg_utama";
			  $dbField[16] = "id_pembayaran";
			  $dbField[17] = "id_dokter";
			  //$dbField[11] = "id_poli";
			 // $dbField[17] = "reg_diagnosa_awal";
				
			  $dbValue[0] = QuoteValue(DPE_CHAR,$reg_id_baru);
			  $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
			  $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
			  $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
			  $dbValue[4] = QuoteValue(DPE_CHAR,"I0");
			  $dbValue[5] = QuoteValue(DPE_CHAR,$userLogin["name"]);
			  $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
			  $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$dataReg["reg_jenis_pasien"]);
			  $dbValue[8] = QuoteValue(DPE_CHAR,"L");#otomatis lama
			  $dbValue[9] = QuoteValue(DPE_CHAR,$dataReg["reg_rujukan_id"]);
			  $dbValue[10] = QuoteValue(DPE_CHAR,"I"); # otomatis rawat inap
			  $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
			  $dbValue[12] = QuoteValue(DPE_CHAR,$dataReg["reg_shift"]);
			  $dbValue[13] = QuoteValue(DPE_CHAR,$dataReg["reg_tipe_layanan"]);
			  $dbValue[14] = QuoteValue(DPE_CHAR,$dataReg["reg_sebab_sakit"]);
			  $dbValue[15] = QuoteValue(DPE_CHAR,$reg_id_lama);
			  $dbValue[16] = QuoteValue(DPE_CHAR,$bayar_id_baru);
			  $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
			  //$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
			 // $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["reg_diagnosa_awal"]);
					//print_r($dbValue); die();
			 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
			 $dtmodel->Insert() or die("insert  error");	
		
			 unset($dtmodel);
			 unset($dbField);
			 unset($dbValue);
			 unset($dbKey);
			 
			 
			###### Insert Biaya Pembayaran #########
			$dbTable = "klinik.klinik_pembayaran";
			$dbField[0] = "pembayaran_id";   // PK
			$dbField[1] = "pembayaran_create";
			$dbField[2] = "pembayaran_who_create";
			$dbField[3] = "pembayaran_tanggal";
			$dbField[4] = "id_reg";
			$dbField[5] = "id_cust_usr";
			$dbField[6] = "pembayaran_total";
			$dbField[7] = "id_dep";
			$dbField[8] = "pembayaran_flag";
			$dbField[9] = "pembayaran_yg_dibayar";
			
			 $dbValue[0] = QuoteValue(DPE_CHARKEY,$bayar_id_baru);
			 $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
			 $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
			 $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
			 $dbValue[4] = QuoteValue(DPE_CHAR,$reg_id_baru);
			 $dbValue[5] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
			 $dbValue[6] = QuoteValue(DPE_NUMERIC,0);
			 $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
			 $dbValue[8] = QuoteValue(DPE_CHAR,$lunas);
			 $dbValue[9] = QuoteValue(DPE_NUMERIC,'0.00');

			 $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
			 
			 $dtmodel->Insert() or die("insert  error");
			 
			 unset($dbField);
			 unset($dtmodel);
			 unset($dbValue);
			 unset($dbKey);
			
			 
			if($konfigurasi["dep_konf_kons"]=='y'){
				require_once("insert_biaya_pemeriksaan.php");
			}
			 
			
			  // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
			
			    $waktuTungguId = $dtaccess->GetTransID(); 
			    $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
		      $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id_lama);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,"I2");
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pasien Dirawat");
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataReg["id_poli"]);
          
				//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");	
		 
			 
			###### Insert folio yang belum dibayar #########
			$sql = " update klinik.klinik_folio 
					 set id_reg = '$reg_id_baru', id_pembayaran ='$bayar_id_baru'
					 where fol_lunas = 'n' and id_reg = '$reg_id_lama' ";
			$dtaccess->Execute($sql);
			
		 } # end kondisi akhir dirawat
###############################  end kondisi akhir dirawat ##############################################*/
         
        // header("location:".$_SERVER['HTTP_REFERER']);
        // exit();        
