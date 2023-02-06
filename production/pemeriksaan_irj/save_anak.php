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

$terapiPse = $_POST['terapi'];
$forma = explode("&", $_POST['forma']);

for($i = 0; $i < count($forma); $i++){
     $value = explode("=", $forma[$i]);

     $removePlus = str_replace("+", " ", $value[1]);
     $remooveOdd = str_replace("%2C", ",", $removePlus);
     $remooveOdd = str_replace("%3A", ":", $remooveOdd);
     $remooveOdd = str_replace("%2F", "/", $remooveOdd);
     $remooveOdd = str_replace("%3F", "?", $remooveOdd);
     $remooveOdd = str_replace("%2B", " ", $remooveOdd);
     $remooveOdd = str_replace("%0D%0A", " \n ", $remooveOdd);

     $form[$value[0]] = $remooveOdd;
}

$sql = "SELECT * from klinik.klinik_perawatan_diagnosa where id_rawat = '".$form['asd']."'";


$dataDiagnosa = $dtaccess->FetchAll($sql);

// if(count($dataDiagnosa) > 0){

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
          $remooveOdd = str_replace("%2B", " ", $remooveOdd);
          $remooveOdd = str_replace("%0D%0A", " \n ", $remooveOdd);

          $form[$value[0]] = $remooveOdd;
     }

	//print_r($form);

     $objArray = [];

     $objArray['Keadaan Umum pasien'] = $form['keadaan_umum_pasien'];
     $objArray['Tekanan Darah Sistole'] = $form['tekanan_darah_sistole'];
     $objArray['Tekanan Darah Diastole'] = $form['tekanan_darah_diastole'];
     $objArray['Pernafasan'] = $form['pernafasan'];
     $objArray['Nadi'] = $form['nadi'];
     $objArray['Suhu'] = $form['suhu_badan'];
     $objArray['Berat Badan'] = $form['berat_badan'];
     $objArray['Tinggi Badan'] = $form['tinggi_badan'];
     $objArray['Lingkar Kepala'] = $form['lingkar_kepala'];

     $keys = array_keys($objArray);

     $reArrange = [];
     for($i = 0; $i < count($keys); $i++){
        $reArrange[] = $keys[$i]." : ".$objArray[$keys[$i]];
   }

   $objective = implode(" ; ", $reArrange);
   $subjective = str_replace("'", "''", $form['keluhanUtama']);
   $formSerial = serialize($form);
   $formSerial = str_replace("'", "''", $formSerial);


   $sql = "UPDATE klinik.klinik_perawatan SET rawat_anak = '$formSerial'";
   $sql .= ", rawat_anamnesa = '$subjective'";
   $sql .= ",  rawat_diagnosa_utama = ".QuoteValue(DPE_CHAR, $form['ket_diagnosa_empat']);
   $sql .= ", rawat_ket = ".QuoteValue(DPE_CHAR,$form['planning_penatalaksanaan']);
   $sql .= ", rawat_pemeriksaan_fisik = '$objective' ";
   $sql .= ", rawat_terapi = ".QuoteValue(DPE_CHAR, $terapi);
   $sql .= ", waktu_asmed = ".QuoteValue(DPE_CHAR, date("Y-m-d H:i:s"));
   $sql .= ", rawat_status_lokalis = ".QuoteValue(DPE_CHAR,$form['status_lokalis'])." WHERE rawat_id = ".QuoteValue(DPE_CHAR, $form['asd']);

   $a = $dtaccess->execute($sql);

   $sql = "SELECT a.*, c.* from klinik.klinik_registrasi a
   left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
   left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
   where rawat_id = ".QuoteValue(DPE_CHAR, $form['asd']);
   $dtPasien = $dtaccess->Fetch($sql);

   $sql = "SELECT penjualan_id from apotik.apotik_penjualan where id_pembayaran = " . QuoteValue(DPE_CHAR, $dtPasien['id_pembayaran']);
   $dtFarmasii = $dtaccess->Fetch($sql);

   $sql = "SELECT max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and penjualan_flag = 'D'";
   $lastKode = $dtaccess->Fetch($sql);
   $skr = date("Y-m-d");
   $tgl = explode("-", $skr);
   $_POST["hidUrut"] = $lastKode["urut"] + 1;
   $_POST["penjualan_no"] = "APRJ" . str_pad($lastKode["urut"] + 1, 5, "0", STR_PAD_LEFT) . "/" . $tgl[2] . "/" . $tgl[1] . "/" . $tgl[0];

   if($terapi){
          //echo $dtFarmasii['penjualan_id'];
       if ($dtFarmasii['penjualan_id'] == '') {

          $dbTable = "klinik.klinik_registrasi";
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

               $regId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR, $regId);
               $dbValue[1] = QuoteValue(DPE_DATE, date('Y-m-d'));
               $dbValue[2] = QuoteValue(DPE_DATE, date('H:i:s'));
               $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
               $dbValue[4] = QuoteValue(DPE_NUMBER, $dtPasien["reg_jenis_pasien"]);
               $dbValue[5] = QuoteValue(DPE_CHAR, '33');
               $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
               $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
               $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["id_poli"]);
               $dbValue[9] = QuoteValue(DPE_CHAR, 'A0');
               $dbValue[10] = QuoteValue(DPE_CHAR, $dtPasien["id_pembayaran"]);
               $dbValue[11] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
               $dbValue[12] = QuoteValue(DPE_CHAR, $dtPasien['reg_id']);
               // print_r($dbValue);die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
               $c = $dtmodel->insert() or die("insert  error");

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

               $dbTable = "apotik.apotik_penjualan";
               $dbField[0] = "penjualan_id";   // PK
               $dbField[1] = "cust_usr_nama";
               $dbField[2] = "penjualan_create";
               $dbField[3] = "id_jenis_pasien";
               $dbField[4] = "id_dokter";
               $dbField[5] = "id_reg";
               $dbField[6] = "id_dep";
               $dbField[7] = "id_cust_usr";
               $dbField[8] = "id_pembayaran";
               $dbField[9] = "id_gudang";
               $dbField[10] = "penjualan_flag";
               $dbField[11] = "penjualan_nomor";
               $dbField[12] = "penjualan_urut";
               $dbField[13] = "id_resep";

               $penjualanId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR, $penjualanId);
               $dbValue[1] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_nama"]);
               $dbValue[2] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
               $dbValue[3] = QuoteValue(DPE_NUMBER, $dtPasien["reg_jenis_pasien"]);
               $dbValue[4] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
               $dbValue[5] = QuoteValue(DPE_CHAR, $regId);
               $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
               $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
               $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["id_pembayaran"]);
               $dbValue[9] = QuoteValue(DPE_CHAR, '2');
               $dbValue[10] = QuoteValue(DPE_CHAR, 'D');
               $dbValue[11] = QuoteValue(DPE_CHAR, $_POST['penjualan_no']);
               $dbValue[12] = QuoteValue(DPE_CHAR, $lastKode['urut'] + 1);
               $dbValue[13] = QuoteValue(DPE_CHAR, $form['asd']);
               //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
               $b = $dtmodel->insert() or die("insert  error");

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

               $dbTable = "klinik.klinik_folio";
               $dbField[0] = "fol_id";   // PK
               $dbField[1] = "id_reg";
               $dbField[2] = "fol_nama";
               $dbField[3] = "fol_jenis";
               $dbField[4] = "fol_waktu";
               $dbField[5] = "fol_jenis_pasien";
               $dbField[6] = "id_dep";
               $dbField[7] = "id_cust_usr";
               $dbField[8] = "id_pembayaran";
               $dbField[9] = "id_poli";
               $dbField[10] = "tipe_rawat";

               $folId = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR, $folId);
               $dbValue[1] = QuoteValue(DPE_CHAR, $regId);
               $dbValue[2] = QuoteValue(DPE_CHAR, 'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_CHAR, 'OA');
               $dbValue[4] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
               $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien['reg_jenis_pasien']);
               $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
               $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
               $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["id_pembayaran"]);
               $dbValue[9] = QuoteValue(DPE_CHAR, '33');
               $dbValue[10] = QuoteValue(DPE_CHAR, 'J');
               //print_r($dbValue); die();
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
               $x = $dtmodel->insert() or die("insert  error");

               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
          }
     }

     if ($a) {
       echo "Berhasil Disimpan";

  }

// }else{
//      echo "Diagnosa Belum Diisi";
// }

?>
