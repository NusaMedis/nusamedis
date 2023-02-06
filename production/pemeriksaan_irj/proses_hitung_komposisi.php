<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "tampilan.php");

// Inisialisasi Lib
$dtaccess = new DataAccess();
$auth = new CAuth();
$enc = new textEncrypt();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();
$tglSekarang = date("d-m-Y");
$now = date("Y-m-d H:i:s");

// -----------------get data komposisi--------------------------
$sql = "select * from apotik.apotik_detail_racikan where id_nama_racikan = " . QuoteValue(DPE_CHAR, $_POST['id_item']);
$dataKomposisi = $dtaccess->FetchAll($sql);

$sql = "SELECT * from apotik.apotik_nama_racikan where nama_racikan_id = ". QuoteValue(DPE_CHAR, $_POST['id_item']);
$namaracikan = $dtaccess->Fetch($sql);

$harga_jual = 0;
foreach ($dataKomposisi as $key => $value) {
    $harga_jual = $harga_jual + $value['detail_racikan_total'];
    $hargapokok = $hargapokok + $value['detail_racikan_harga_pokok'];
    $ppn = $ppn + $value['detail_racikan_ppn'];
    $hpp = $hpp + $value['detail_racikan_hpp'];
    $tuslag = $tuslag + $value['detail_racikan_tuslag'];
}

$dbTable = "logistik.logistik_item";
$dbField[0] = "item_id";   // PK
$dbField[1] = "item_harga_jual";


$dbValue[0] = QuoteValue(DPE_CHAR, $_POST['id_item']);
$dbValue[1] = QuoteValue(DPE_CHAR, $harga_jual);
//print_r($dbValue); die();
$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
$b = $dtmodel->update() or die("insert  error");

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);

// -------------------------------update ke apotik penjualan detail------------------------
$dbTable = "apotik.apotik_penjualan_detail"; // PK

$dbField[0] = "id_item";
$dbField[1] = "item_nama";
$dbField[2] = "penjualan_detail_harga_jual";
$dbField[3] = "penjualan_detail_total";
$dbField[4] = "penjualan_detail_tuslag";
$dbField[5] = "penjualan_detail_ppn";
$dbField[6] = "penjualan_detail_harga_pokok";
$dbField[7] = "penjualan_detail_harga_beli";

$sql = "select * from apotik.apotik_penjualan_detail where id_item = " . QuoteValue(DPE_CHAR, $_POST['id_item']);
$dtpenjualan = $dtaccess->Fetch($sql);
//echo $sql;

// $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['rawat_terapi_racikan_id']);
$dbValue[0] = QuoteValue(DPE_CHAR, $_POST['id_item']);
$dbValue[1] = QuoteValue(DPE_CHAR, $namaracikan['nama_racikan_nama']);
$dbValue[2] = QuoteValue(DPE_NUMERIC, intval($harga_jual / $dtpenjualan['penjualan_detail_jumlah']));
$dbValue[3] = QuoteValue(DPE_NUMERIC, $harga_jual);
$dbValue[4] = QuoteValue(DPE_NUMERIC, $tuslag);
$dbValue[5] = QuoteValue(DPE_NUMERIC, $ppn);
$dbValue[6] = QuoteValue(DPE_NUMERIC, $hargapokok);
$dbValue[7] = QuoteValue(DPE_NUMERIC, $hpp);

$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
// $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
$b = $dtmodel->update() or die("Update  error");

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);
