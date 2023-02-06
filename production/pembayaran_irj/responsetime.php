<?php 

require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();


$idreg=$_POST['id_reg'];
$id_cust_usr=$_POST['id_cust_usr'];
$id_poli=$_POST['id_poli'];

  #cek apakah sudah diinsert atau belum
   $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$idreg);
   $rs = $dtaccess->Fetch($sql);

   if (!$rs["prev"]) {
      #cari waktu di buat terakhir
      $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$idreg);
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
      $dbValue[1] = QuoteValue(DPE_CHAR, $idreg);
      $dbValue[2] = QuoteValue(DPE_CHAR, $id_cust_usr);
      $dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
      $dbValue[4] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[5] = QuoteValue(DPE_CHAR, "K0");
      $dbValue[6] = QuoteValue(DPE_CHAR, "Pasien di kasir");
      $dbValue[7] = QuoteValue(DPE_CHAR, $id_poli);
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
   else{
      $sql = "update klinik.klinik_waktu_tunggu set klinik_waktu_tunggu_when_create = " . QuoteValue(DPE_DATE, date("Y-m-d H:i:s")) . ",klinik_waktu_tunggu_who_create= '$userName'
      where id_reg = " . QuoteValue(DPE_CHAR, $idreg)." and klinik_waktu_tunggu_status='K0'";
      $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
   }

   echo "y";

?>