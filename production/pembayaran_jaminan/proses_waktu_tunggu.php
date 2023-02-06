<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

//INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();

if ($_GET['id_reg'] and !$_POST['btnBayar']) {

   $regId = $_GET['id_reg'];
   #cek apakah sudah diinsert atau belum
   $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " . QuoteValue(DPE_CHAR, $regId);
   $rs = $dtaccess->Fetch($sql);

   if (!$rs["prev"]) {
      #cari waktu di buat terakhir
      $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " . QuoteValue(DPE_CHAR, $regId);
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
      $dbValue[1] = QuoteValue(DPE_CHAR, $regId);
      $dbValue[2] = QuoteValue(DPE_CHAR, $_GET["id_cust_usr"]);
      $dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
      $dbValue[4] = QuoteValue(DPE_CHAR, $userLogin["name"]);
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

      //header("location:".$_SERVER['HTTP_REFERER']);

      #return form data
      //   $return = array(
      //       'reg_status' => 'E1', 
      //   );
      //   echo json_encode( $return );

      // exit();        
   }
}

if ($_POST['btnBayar']) {
   $regId = $_POST['id_reg'];

   #cek apakah sudah diinsert atau belum
   $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K1' AND id_reg = " . QuoteValue(DPE_CHAR, $regId);
   $rs = $dtaccess->Fetch($sql);

   if (!$rs["prev"]) {
      #cari waktu di buat terakhir
      $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " . QuoteValue(DPE_CHAR, $regId);
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
      $dbValue[1] = QuoteValue(DPE_CHAR, $regId);
      $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["cust_usr_id"]);
      $dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
      $dbValue[4] = QuoteValue(DPE_CHAR, $userLogin["name"]);
      $dbValue[5] = QuoteValue(DPE_CHAR, "K1");
      $dbValue[6] = QuoteValue(DPE_CHAR, "Pasien sudah bayar di kasir");
      $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["id_poli"]);
      $dbValue[8] = QuoteValue(DPE_CHAR, $durasi);
      $dbValue[9] = QuoteValue(DPE_NUMERIC, $durasiDetik);
      $dbValue[10] = QuoteValue(DPE_CHAR, "K1");


      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

      $dtmodel->Insert() or die("insert  error");

      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);
      
      $sql = "update klinik.klinik_waktu_tunggu set keterangan = " . QuoteValue(DPE_CHAR, $_POST['et_alasan']) . " 
      where id_reg = " . QuoteValue(DPE_CHAR, $regId)." and klinik_waktu_tunggu_status='K1'";
      $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);

       $sql = "update klinik.klinik_waktu_tunggu set keterangan = " . QuoteValue(DPE_CHAR, $_POST['et_alasan_']) . " 
      where id_reg = " . QuoteValue(DPE_CHAR, $regId)." and klinik_waktu_tunggu_status='K0'";
      $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
   }
}
