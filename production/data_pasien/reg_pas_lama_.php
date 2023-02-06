<?php
  // LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."tampilan.php");

  //INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();

/* SQL KONFIGURASI */
$sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
$konfigurasi = $dtaccess->Fetch($sql);
/* SQL KONFIGURASI */

  // require_once('reg_kode_trans.php'); // Menentukan reg_kode_trans
$tgl = date('ymd');
$sql = "select count(reg_id) as nomorurut from klinik.klinik_registrasi where reg_tanggal = ".QuoteValue(DPE_DATE,date('Y-m-d'));
$noUrut = $dtaccess->Fetch($sql);
$noantri =  $noUrut["nomorurut"]+1;
$kodeUrutReg =  $noUrut["nomorurut"]+1;
$kodeUrutReg = str_pad($kodeUrutReg,4,"0",STR_PAD_LEFT);

$kodeTrans = 'R'.$tgl.$kodeUrutReg;
   //require_once('reg_kode_trans.php');
/* Pengecekan Sudah Melakukan Pemeriksaan Sebelumnya */  
$sql = "select reg_id, id_pembayaran from klinik.klinik_registrasi where id_cust_usr=".QuoteValue(DPE_CHAR, $custUsrId)." and reg_tanggal=".QuoteValue(DPE_DATE,date_db($_POST["reg_tanggal"]))." and reg_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);

if($_POST["instalasi"] == 'G' || $_POST["instalasi"] == 'I'){
  $sql .= " and reg_tipe_rawat = ".QuoteValue(DPE_CHAR, $_POST["instalasi"]);

}
else{
  $sql .=   " and reg_tipe_rawat = 'J'";
}
 // echo $sql; 
$AdaDataReg = $dtaccess->Fetch($sql);
/* Pengecekan Sudah Melakukan Pemeriksaan Sebelumnya */  

/* Menentukan Umur */
$sql = "select cust_usr_tanggal_lahir from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
$pasien = $dtaccess->Fetch($sql);

$sql = "select count(fol_id) as row from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$AdaDataReg['id_pembayaran'])." and fol_lunas = 'n'";
$Folio = $dtaccess->Fetch($sql);
// echo $sql;
$birthday = $pasien['cust_usr_tanggal_lahir'];  
$biday = new DateTime( $birthday );
$today = new DateTime();
$diff = $today->diff($biday); 

  if ($AdaDataReg && $Folio['row'] > 0) { //Jika Ada Pemeriksaan Sebelumnya  
    // echo 'ada';die();
    $byrId=$AdaDataReg["id_pembayaran"];
    $regUtama=$AdaDataReg["reg_id"];
  } else {
    // echo $byrId;
    // echo 'ada1';die();
    /* INSERT PEMBAYARAN */
    $dbTable = "klinik.klinik_pembayaran";

    $dbField[0] = "pembayaran_id";   // PK
    $dbField[1] = "pembayaran_create";
    $dbField[2] = "pembayaran_who_create";
    $dbField[3] = "pembayaran_tanggal";
    $dbField[4] = "id_cust_usr";
    $dbField[5] = "pembayaran_total";
    $dbField[6] = "id_dep";
    $dbField[7] = "pembayaran_flag";
    $dbField[8] = "pembayaran_yg_dibayar";
    
    $byrId = $dtaccess->GetTransID();
    $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrId);
    $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
    $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['reg_tanggal']));
    $dbValue[4] = QuoteValue(DPE_CHAR,$custUsrId);
    $dbValue[5] = QuoteValue(DPE_NUMERIC,0);
    $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[7] = QuoteValue(DPE_CHAR,'n');
    $dbValue[8] = QuoteValue(DPE_NUMERIC,'0.00');

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

    $dtmodel->Insert() or die("insert  error");

    unset($dbField);
    unset($dtmodel);
    unset($dbValue);
    unset($dbKey);
    /* INSERT PEMBAYARAN */
  }

  $id_poli=$_POST["klinik"];
   //   $sql = "SELECT * FROM global.global_auth_poli WHERE poli_id ='$id_poli'";
   // $row = pg_fetch_array(pg_query($con,$sql));
  $id_dokter=$_POST['dokter'];
  $reg_buffer_tanggal=date_db($_POST["reg_tanggal"]);


 // membaca kode barang terbesar


// SELECT max(reg_no_antrian) as maxkode FROM klinik.klinik_registrasi where reg_status!='' and reg_tanggal='2020-09-28'AND id_poli ='c96a0c5914b37954352542aae75e4709' and id_dokter ='95d2e62db7461708e6e44c0ee958485e'
  $query = "SELECT max(reg_no_antrian) as maxkode FROM klinik.klinik_registrasi WHERE reg_tanggal='$reg_buffer_tanggal'AND id_poli=".QuoteValue(DPE_CHAR,$_POST["klinik"])." AND id_dokter=".QuoteValue(DPE_CHAR,$_POST["dokter"]);
  $hasil = pg_query($query);
  $data  = pg_fetch_array($hasil);
  $kodeAntrian = $data['maxkode'];

// mengambil angka atau bilangan dalam kode anggota terbesar,
// dengan cara mengambil substring mulai dari karakter ke-1 diambil 6 karakter
// misal 'BRG001', akan diambil '001'
// setelah substring bilangan diambil lantas dicasting menjadi integer
  $noUrut = (int) substr($kodeAntrian, 3, 3);

// bilangan yang diambil ini ditambah 1 untuk menentukan nomor urut berikutnya
  $noUrut++;



// membentuk kode anggota baru
// perintah sprintf("%03s", $noUrut); digunakan untuk memformat string sebanyak 3 karakter
// misal sprintf("%03s", 12); maka akan dihasilkan '012'
// atau misal sprintf("%03s", 1); maka akan dihasilkan string '001'

  if ($_POST["klinik"]=="c96a0c5914b37954352542aae75e4709") {
  # code...
    $char="A";


  }
  elseif ($_POST["klinik"]=="c2b63ccfdc414dcd2c2d9a1c5f69db9a") {
  # code...
    $char="B";

  }
  elseif ($_POST["klinik"]=="92704e222196ff9e9342db6755e1a6f4") {
  # code...
    $char="D";

  }
  elseif ($_POST["klinik"]=="a8460eb351235d2423d6404323285f11") {
  # code...
    $char="D";

  }

  $kd=substr($id_dokter,0,2);

  $noAntrian = $char .$kd. sprintf("%03s", $noUrut);
  /* INSERT REGISTRASI */
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
  $dbField[11] = "id_poli";
  $dbField[12] = "id_dep";
  $dbField[13] = "reg_shift";
  $dbField[14] = "reg_tipe_layanan";
  $dbField[15] = "reg_sebab_sakit";
  $dbField[16] = "id_dokter";
  $dbField[17] = "reg_diagnosa_awal";
  $dbField[18] = "id_pembayaran";
  $dbField[19] = "reg_tingkat_kegawatan";
  $dbField[20] = "id_poli_asal";
  $dbField[21] = "reg_umur";
  $dbField[22] = "reg_umur_bulan";
  $dbField[23] = "reg_umur_hari";
  $dbField[24] = "reg_kelas";
  $dbField[25] = "reg_prosedur_masuk";
  $dbField[26] = "reg_tracer_registrasi";
  $dbField[27] = "reg_tracer_barcode";
  $dbField[28] = "reg_tracer_barcode_besar";
  $dbField[29] = "reg_tracer_riwayat";
  $dbField[30] = "reg_tracer";
  $dbField[31] = "reg_kode_trans";
  $dbField[32] = "reg_rujukan_det";
  $dbField[33] = "reg_no_antrian";
  $dbField[34] = "reg_tipe_paket";
  $dbField[35] = "reg_dokter_sender";
  $dbField[36] = "reg_tgl_sep";
  $dbField[37] = "hak_kelas_inap";
  $dbField[38] = "reg_tanggal_pulang";
  $dbField[39] = "reg_waktu_pulang";
  $dbField[40] = "isi_lainnya";
  if ($regUtama){
    $dbField[41] = "reg_tipe_jkn";
    $dbField[42] = "reg_utama";
    $dbField[43] = "id_perusahaan";
    if(!empty($_POST["reg_no_sep"])) $dbField[43] = "reg_no_sep";
  }else{
    $dbField[41] = "id_perusahaan";
    $dbField[42] = "reg_tipe_jkn";
    if(!empty($_POST["reg_no_sep"])) $dbField[42] = "reg_no_sep";
  }

  $regId = (!$_POST['regId']) ? $dtaccess->GetTransID() : $_POST['regId'];

  if (!$_POST["cetak_reg"]) $_POST["cetak_reg"] = "y";
  if (!$_POST["cetak_barcode_k"]) $_POST["cetak_barcode_k"] = "y";
  if (!$_POST["cetak_barcode_b"]) $_POST["cetak_barcode_b"] = "y";
  if (!$_POST["cetak_ringkasan"]) $_POST["cetak_ringkasan"] = "y";

  if ($_POST["instalasi"]=="I") {
    $status = "I2";
    $tipe_rawat = 'I';
    $poli_asal = $_POST['klinik'];
  } elseif ($_POST["instalasi"]=="G"){
    $status = "G0"; 
    $tipe_rawat = 'G'; 
    $poli_asal = $_POST['klinik'] ;
  } elseif ($_POST["instalasi"]!="G" || $_POST["instalasi"]!="I") {
    if ($poliTipe['poli_tipe']=='R') {
      $status = "R0";
    } else {
      $status = "E0";
    }
    $tipe_rawat = 'J';
    $poli_asal = $_POST['klinik']; 
  }

  $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
  $dbValue[1] = QuoteValue(DPE_DATE,date_db($_POST["reg_tanggal"]));
  $dbValue[2] = QuoteValue(DPE_DATE,$_POST["reg_waktu"]);
  $dbValue[3] = QuoteValue(DPE_CHAR,$custUsrId);
  $dbValue[4] = QuoteValue(DPE_CHAR,$status);
  $dbValue[5] = QuoteValue(DPE_CHAR,$userLogin["name"]);
  $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
  $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
  $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
  $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_id"]);
  $dbValue[10] = QuoteValue(DPE_CHAR,$tipe_rawat);
  $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
  $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
  $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
  $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["layanan"]);
  $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["reg_sebab_sakit"]);
  $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
  $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["reg_diagnosa_awal"]);
  $dbValue[18] = QuoteValue(DPE_CHAR,$byrId);
  $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["reg_tingkat_kegawatan"]);
  $dbValue[20] = QuoteValue(DPE_CHAR,$poli_asal);
  $dbValue[21] = QuoteValue(DPE_NUMERIC,$diff->y);
  $dbValue[22] = QuoteValue(DPE_NUMERIC,$diff->m);
  $dbValue[23] = QuoteValue(DPE_NUMERIC,$diff->d);
  $dbValue[24] = QuoteValue(DPE_CHAR, $konfigurasi["dep_konf_kelas_tarif_irj"] ); # ikut conf rs
  $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["reg_prosedur_masuk"]);
  $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cetak_reg"]);
  $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["cetak_barcode_k"]);
  $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["cetak_barcode_b"]);
  $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cetak_ringkasan"]);
  $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["cetak_tracer"]);
  $dbValue[31] = QuoteValue(DPE_CHAR,$kodeTrans);
  $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_det"]);
  $dbValue[33] = QuoteValue(DPE_CHAR,$noAntrian); 
  $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["paket"]);
  $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["reg_dokter_sender"]);   
  $dbValue[36] = QuoteValue(DPE_DATE,$_POST["reg_tgl_sep"]);
  $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["hak_kelas_inap"]);
  $dbValue[38] = QuoteValue(DPE_DATE,null);
  $dbValue[39] = QuoteValue(DPE_DATE,null);  
  $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["isi_lainnya_text"]);

  if ($regUtama){
    $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]);
    $dbValue[42] = QuoteValue(DPE_CHAR,$regUtama);
    $dbValue[43] = QuoteValue(DPE_CHAR,$_POST["perusahaan"]);
    if(!empty($_POST["reg_no_sep"])) $dbValue[43] = QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]);
  }else{
    $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["perusahaan"]);
    $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]);
    if(!empty($_POST["reg_no_sep"])) $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]);
  }
  
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  if ($_POST["btnUpdate"]) $dtmodel->Insert() or die("insert  error");
  
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

  /* UPDATE UMUR */
  $umur =  $diff->y ."~".$diff->m ."~". $diff->d ;
  $sql = "update global.global_customer_user";
  $sql .= " set cust_usr_umur = ".QuoteValue(DPE_CHAR, $umur);
  $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
  $rs = $dtaccess->Execute($sql);
  /* UPDATE UMUR */


  if ($custUsrId=="abcabcabc") {
    $sql = "select reg_id,id_pembayaran,reg_jenis_pasien from klinik.klinik_registrasi where id_cust_usr = '$custUsrId' and reg_tipe_rawat = '$_POST[asal_pasien]' and  reg_utama is null and id_pembayaran is not null order by reg_tanggal desc,reg_waktu desc ";
    $reg_id_lama = $dtaccess->Fetch($sql);

    if ($tipe_rawat=="I") {
  // code...
      $sql_rawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$reg_id_lama["reg_id"]);
      $dataPerawat= $dtaccess->Fetch($sql_rawat);

    } else {
  // code...
      $sql_rawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$regId);
      $dataPerawat= $dtaccess->Fetch($sql_rawat);

    }


    /* Pengecekan Sudah Ada Perawatan */

  // $sql_rawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$reg_id_lama["reg_id"]);
  // $dataPerawatLama= $dtaccess->Fetch($sql_rawat);
    /* Pengecekan Sudah Ada Perawatan */



    if (!$dataPerawat) {
      /* INSERT PERAWATAN */
      $dbTable = " klinik.klinik_perawatan";

    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "id_reg";
    $dbField[2] = "id_cust_usr";
    $dbField[3] = "rawat_waktu_kontrol";
    $dbField[4] = "rawat_tanggal";
    $dbField[5] = "rawat_flag"; 
    $dbField[6] = "rawat_flag_komen"; 
    $dbField[7] = "id_poli"; 
    $dbField[8] = "id_dep";
    $dbField[9] = "rawat_perawat_who_update";
    $dbField[10] = "rawat_waktu";         

    $_POST["rawat_id"] = $dtaccess->GetTransID();

    $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
    $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
    $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST['reg_tanggal']));
    $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
    $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    $dtmodel->Insert() or die("insert  error"); 

    unset($dtmodel);
    unset($dbValue);
    unset($dbField);
    unset($dbKey);
    /* INSERT PERAWATAN */
  }

  else{



   $dbTable = " klinik.klinik_perawatan";

    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "id_reg";
    $dbField[2] = "id_cust_usr";
    $dbField[3] = "rawat_waktu_kontrol";
    $dbField[4] = "rawat_tanggal";
    $dbField[5] = "rawat_flag"; 
    $dbField[6] = "rawat_flag_komen"; 
    $dbField[7] = "id_poli"; 
    $dbField[8] = "id_dep";
    $dbField[9] = "rawat_perawat_who_update";
    $dbField[10] = "rawat_waktu";
    $dbField[11] = "rawat_keluhan";
    $dbField[12] = "rawat_catatan"; 
    $dbField[13] = "rawat_anamnesa";
    $dbField[14] = "rawat_jenis_penyakit"; 
    $dbField[15] = "rawat_ket";    
    $dbField[16] = "rawat_terapi"; 
    $dbField[17] = "rawat_pemeriksaan_fisik";
    $dbField[18] = "rawat_penunjang";
    $dbField[19] = "waktu_mulai_assesment";
    $dbField[20] = "ttd_bidan";
    $dbField[21] = "ttd_nyeri"; 
    $dbField[22] = "ttd_farmasi";
    $dbField[23] = "ttd_gizi";   
    $dbField[24] = "ttd_rehab";
    $dbField[25] = "perawatan_triage";
    $dbField[26] = "ttd_penerima_transfer";
    $dbField[27] = "ttd_transport";    
    $dbField[28] = "rawat_bidan_who_update";
    $dbField[29] = "ttd_dpjp_cppt";
    $dbField[30] = "ttd_bidan_pas";
    $dbField[31] = "ttd_nyeri_pas";
    $dbField[32] = "ttd_farmasi_pas";
    $dbField[33] = "ttd_gizi_pas";
    $dbField[34] = "ttd_rehab_pas";  
    $dbField[35] = "edukator_bidan"; 
    $dbField[36] = "edukator_nyeri";  
    $dbField[37] = "edukator_farmasi";
    $dbField[38] = "edukator_gizi";  
    $dbField[39] = "edukator_rehab";
    $dbField[40] = "penerima_edukasi_bidan"; 
    $dbField[41] = "penerima_edukasi_nyeri";
    $dbField[42] = "penerima_edukasi_farmasi";
    $dbField[43] = "penerima_edukasi_rehab";   


    $_POST["rawat_id"] = $dtaccess->GetTransID();

    $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
    $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
    $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST['reg_tanggal']));
    $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
    $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
    $dbValue[11] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_keluhan"]);
    $dbValue[12] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_catatan"]);
    $dbValue[13] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_anamnesa"]);
    $dbValue[14] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_jenis_penyakit"]);
    $dbValue[15] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_ket"]);
    $dbValue[16] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_terapi"]);
    $dbValue[17] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_pemeriksaan_fisik"]);
    $dbValue[18] = QuoteValue(DPE_CHAR,$dataPerawat["rawat_penunjang"]);
    $dbValue[19] = QuoteValue(DPE_CHAR,$dataPerawat["waktu_mulai_assesment"]);
    $dbValue[20] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_bidan"]);
    $dbValue[21] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_nyeri"]);
    $dbValue[22] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_farmasi"]);
    $dbValue[23] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_gizi"]);
    $dbValue[24] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_rehab"]);
    $dbValue[25] = QuoteValue(DPE_CHAR,$dataPerawat["perawatan_triage"]);
    $dbValue[26] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_penerima_transfer"]);
    $dbValue[27] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_transport"]);
    $dbValue[28] = QuoteValue(DPE_CHAR,$dataPerawat["rawa_bidan_who_update"]);
    $dbValue[29] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_dpjp_cppt"]);
    $dbValue[30] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_bidan_pas"]);
    $dbValue[31] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_nyeri_pas"]);
    $dbValue[32] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_farmasi_pas"]);
    $dbValue[33] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_gizi_pas"]);
    $dbValue[34] = QuoteValue(DPE_CHAR,$dataPerawat["ttd_rehab_pas"]);
    $dbValue[35] = QuoteValue(DPE_CHAR,$dataPerawat["edukator_bidan"]);
    $dbValue[36] = QuoteValue(DPE_CHAR,$dataPerawat["edukator_nyeri"]);
    $dbValue[37] = QuoteValue(DPE_CHAR,$dataPerawat["edukator_farmasi"]);
    $dbValue[38] = QuoteValue(DPE_CHAR,$dataPerawat["edukator_gizi"]);
    $dbValue[39] = QuoteValue(DPE_CHAR,$dataPerawat["edukator_rehab"]);
    $dbValue[40] = QuoteValue(DPE_CHAR,$dataPerawat["penerima_edukasi_bidan"]);
    $dbValue[41] = QuoteValue(DPE_CHAR,$dataPerawat["penerima_edukasi_nyeri"]);
    $dbValue[42] = QuoteValue(DPE_CHAR,$dataPerawat["penerima_edukasi_farmasi"]);
    $dbValue[43] = QuoteValue(DPE_CHAR,$dataPerawat["penerima_edukasi_rehab"]);



    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    $dtmodel->Insert() or die("insert  error"); 

    unset($dtmodel);
    unset($dbValue);
    unset($dbField);
    unset($dbKey);

    $sql="update klinik.klinik_perawatan_edukasi set id_reg='$regId' where id_reg='$reg_id_lama[reg_id]'";
    $rs = $dtaccess->Execute($sql);

    $sql="update klinik.klinik_perawatan_edukasi_lanjutan set id_reg='$regId' where id_reg='$reg_id_lama[reg_id]'";
    $rs = $dtaccess->Execute($sql);


    $sql="update klinik.klinik_igd_serialize  set id_reg='$regId' where id_reg='$reg_id_lama[reg_id]' and  tab = 'cpo_1_'";
    $rs = $dtaccess->Execute($sql);

    $sql="update klinik.klinik_igd_serialize  set id_reg='$regId' where id_reg='$reg_id_lama[reg_id]' and  tab = 'cpo_2_'";
    $rs = $dtaccess->Execute($sql);

  }


}
else{

  $sql_rawat = "select * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$regId);
  $dataPerawat= $dtaccess->Fetch($sql_rawat);


  if (!$dataPerawat) {
    /* INSERT PERAWATAN */
    $dbTable = " klinik.klinik_perawatan";

    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "id_reg";
    $dbField[2] = "id_cust_usr";
    $dbField[3] = "rawat_waktu_kontrol";
    $dbField[4] = "rawat_tanggal";
    $dbField[5] = "rawat_flag"; 
    $dbField[6] = "rawat_flag_komen"; 
    $dbField[7] = "id_poli"; 
    $dbField[8] = "id_dep";
    $dbField[9] = "rawat_perawat_who_update";
    $dbField[10] = "rawat_waktu";         

    $_POST["rawat_id"] = $dtaccess->GetTransID();

    $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
    $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
    $dbValue[4] = QuoteValue(DPE_DATE,date_db($_POST['reg_tanggal']));
    $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
    $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
    $dtmodel->Insert() or die("insert  error"); 

    unset($dtmodel);
    unset($dbValue);
    unset($dbField);
    unset($dbKey);
    /* INSERT PERAWATAN */
  }

}


  if ($_POST["reg_status_pasien"] == "B") include("insert_biaya_kartu.php"); // INSERT TINDAKAN KARTU

  $sql = "update klinik.klinik_pembayaran set id_reg = ".QuoteValue(DPE_CHAR,$regId)." where pembayaran_id = ".QuoteValue(DPE_CHAR,$byrId);
  $rs = $dtaccess->Execute($sql);

  if($_POST["paket"]){ 
    require_once("insert_biaya_paket.php"); // INSERT TINDAKAN PAKET
  } else {
    if($_POST["reg_rujukan_id"] == '9') {
      if($konfigurasi["dep_konf_kons"]=='y'){
        if ($_POST["instalasi"]!="I")  {
          require_once("insert_biaya_pemeriksaan.php"); // INSERT TINDAKAN PEMERIKSAAN
        }
      }
    } else {
      if($konfigurasi["dep_konf_reg"]=='y'){
        if ($_POST["instalasi"] != "I")  {  
          if ($_POST["reg_status_pasien"] == "L") {
            require_once("insert_biaya_registrasi.php"); // INSERT BIAYA REGISTRASI
          }               
        }
      }
      if($konfigurasi["dep_konf_kons"]=='y'){
        if ($_POST["instalasi"]!="I")  {
          require_once("insert_biaya_pemeriksaan.php"); // INSERT TINDAKAN PEMERIKSAAN
        }
      }
    }
  }

  $durasi = durasi($_POST["waktu_awal"], date("Y-m-d H:i:s"));
  $durasiDetik = durasiDetik($_POST["waktu_awal"], date("Y-m-d H:i:s"));

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
  $dbField[9] = "klinik_waktu_tunggu_when_update";
  $dbField[10] = "klinik_waktu_tunggu_create";
  $dbField[11] = "klinik_waktu_tunggu_durasi";
  $dbField[12] = "klinik_waktu_tunggu_durasi_detik";

  $dbField[13] = "who_update";


  $waktuTungguId = $dtaccess->GetTransID(); 

  $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
  $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
  $dbValue[2] = QuoteValue(DPE_CHAR,$custUsrId);
  $dbValue[3] = QuoteValue(DPE_DATE,$_POST['waktu_awal']);
  $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
  $dbValue[5] = QuoteValue(DPE_CHAR,$status);
  $dbValue[6] = QuoteValue(DPE_CHAR,"Pasien di Registrasi");
  $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
  $dbValue[8] = QuoteValue(DPE_CHAR,$status);
  $dbValue[9] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
  $dbValue[10] = QuoteValue(DPE_DATE,$_POST['waktu_awal']);
  $dbValue[11] = QuoteValue(DPE_CHAR, $durasi);
  $dbValue[12] = QuoteValue(DPE_NUMERIC, $durasiDetik);

  $dbValue[13] = QuoteValue(DPE_CHAR, $userName);


  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  if ($_POST["btnUpdate"]) $dtmodel->Insert() or die("insert  error");  

  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  ?>
