<?php
// Library
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
$depLowest = $auth->GetDepLowest();

$sql = "select * from logistik.logistik_item where item_id = " . QuoteValue(DPE_CHAR, $_POST['komposisi']);
$Item = $dtaccess->Fetch($sql);

$sql_racikanhistory = "select * from klinik.klinik_history_terapi where history_terapi_id = " . QuoteValue(DPE_CHAR, $_GET['rawat_terapi_racikan_id']);
$racikanhistory = $dtaccess->Fetch($sql_racikanhistory);
/* --- Klinik_folio --- */
$dbTable = "klinik.klinik_perawatan_terapi";

$dbField[0] = "rawat_item_id";   // PK
$dbField[1] = "id_rawat";
$dbField[2] = "id_item";
$dbField[3] = "item_nama";
$dbField[4] = "terapi_jumlah_item";
$dbField[5] = "id_rawat_terapi_racikan";

$rawatItemId = $dtaccess->GetTransID();

$dbValue[0] = QuoteValue(DPE_CHAR, $rawatItemId);
$dbValue[1] = QuoteValue(DPE_CHAR, $_GET["id_rawat"]); // id_reg ambil dari rujukan
$dbValue[2] = QuoteValue(DPE_CHAR, $_POST['komposisi']); // tidak ada karena belum meilih dokter di rujukan
$dbValue[3] = QuoteValue(DPE_CHAR, $Item['item_nama']);
$dbValue[4] = QuoteValue(DPE_CHAR, $_POST["dosis"]);
$dbValue[5] = QuoteValue(DPE_CHAR, $_GET['rawat_terapi_racikan_id']);

$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

$dtmodel->Insert() or die("insert  error");

$dataFarmasi = ['item_nama', 'id_item', 'rawat_item_id', 'terapi_jumlah_item'];
foreach ($dataFarmasi as $key) {
  if (array_key_exists($key, $_POST)) {
    $data[] = ['field' => $key, 'value' => $_POST[$key]];
  }
}

if ($dtmodel) {
  foreach ($dataFarmasi as $key) {
    $rs['item_nama'] = $Item['item_nama'];
    $rs['terapi_jumlah_item'] = $_POST['dosis'];
    $rs['id_item'] = $_POST['komposisi'];
    $rs['rawat_item_id'] = $rawatItemId;
  }
  echo json_encode($rs);
}

unset($dbTable);
unset($dbField);
unset($dbValue);
unset($dbKey);
unset($dtmodel);

/* --- apotik detail Racikan --- */
$dbTable = "apotik.apotik_detail_racikan";

$dbField[0] = "detail_racikan_id";   // PK
$dbField[1] = "id_nama_racikan";
$dbField[2] = "id_item";
$dbField[3] = "item_nama";
$dbField[4] = "detail_racikan_jumlah";
$dbField[5] = "when_create";
$dbField[6] = "who_create";
$dbField[7] = "id_reg_asal";
$dbField[8] = "item_harga_jual";
$dbField[9] = "detail_racikan_total";
$dbField[10] = "detail_racikan_ppn";
$dbField[11] = "detail_racikan_hpp";
$dbField[12] = "detail_racikan_harga_pokok";
$dbField[13] = "detail_racikan_tuslag";


$sql = "select * from apotik.apotik_conf where id_dep = " . QuoteValue(DPE_CHAR, $depId);
$konf = $dtaccess->Fetch($sql);

$hargabeli = $Item['item_hpp'];

$konfig = ($konf["conf_biaya_tuslag"] / 100);
$_POST["txtResep"] = $konf["conf_biaya_resep"];  //Konfigurasi Resep Pasien
if ($konf["conf_apotik_harga_otomatis_margin"] == 'y')  //jika harga dari margin maka dari perhitungan jika tidak ambil dari db
  {
    $sql = "select margin_nilai from apotik.apotik_margin
                    where id_grup_item = ".QuoteValue(DPE_CHAR, $Item['id_kategori'])."
                    and is_aktif ='Y' and " . $hargabeli . " >= harga_min and " . $hargabeli .
               " <= harga_max ";
    $margin = $dtaccess->Fetch($sql);
    $hargamargin =  intval(((100 + $margin["margin_nilai"]) / 100) * $Item["item_hpp"]);
    $hargajual = intval(1.1 * $hargamargin);
} else { 
    $hargajual = $Item["item_harga_jual"];
    $ppn = intval(0.1 * $Item["item_harga_jual"]);
}   

$sql = "SELECT penjualan_detail_jumlah, nama_racikan_jenis from apotik.apotik_penjualan_detail a 
left join apotik.apotik_nama_racikan b on a.id_item = b.nama_racikan_id
where id_item = '".$_GET['rawat_terapi_racikan_id']."'";
$datRacikan = $dtaccess->Fetch($sql);

$jumlah = ($datRacikan['nama_racikan_jenis'] == '1') ? $datRacikan['penjualan_detail_jumlah'] * $_POST["dosis"] : $_POST["dosis"];

$HargaJumlah = $hargajual * $jumlah;
$PajakJumlah = StripCurrency(intval(($hargamargin * $jumlah) * 0.1));
$HargaBeliJumlah = StripCurrency($Item['item_hpp']) * StripCurrency($jumlah);
$TuslagJumlah = StripCurrency(intval($konfig * $HargaJumlah));
$hargapokok = StripCurrency(intval($jumlah * $hargamargin));
$totalAll = intval($hargapokok + $PajakJumlah + $TuslagJumlah);

$dbValue[0] = QuoteValue(DPE_CHAR, $rawatItemId);
$dbValue[1] = QuoteValue(DPE_CHAR, $_GET['rawat_terapi_racikan_id']);
$dbValue[2] = QuoteValue(DPE_CHAR, $_POST['komposisi']); // tidak ada karena belum meilih dokter di rujukan
$dbValue[3] = QuoteValue(DPE_CHAR, $Item['item_nama']);
$dbValue[4] = QuoteValue(DPE_CHAR, $jumlah);
$dbValue[5] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
$dbValue[6] = QuoteValue(DPE_CHAR, $userName);
$dbValue[7] = QuoteValue(DPE_CHAR, $_POST["reg_id"]);
$dbValue[8] = QuoteValue(DPE_CHAR, $hargamargin);
$dbValue[9] = QuoteValue(DPE_CHAR, $totalAll);
$dbValue[10] = QuoteValue(DPE_CHAR, $PajakJumlah);
$dbValue[11] = QuoteValue(DPE_CHAR, $HargaBeliJumlah);
$dbValue[12] = QuoteValue(DPE_CHAR, $hargapokok);
$dbValue[13] = QuoteValue(DPE_CHAR, $TuslagJumlah);

$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

$dtmodel->Insert() or die("insert  error");

unset($dbTable);
unset($dbField);
unset($dbValue);
unset($dbKey);
unset($dtmodel);



$dbTable = "klinik.klinik_history_terapi";
$dbField[0] = "history_terapi_id";   // PK
$dbField[1] = "nama_item";
$dbField[2] = "id_reg";
$dbField[3] = "id_cust_usr";
$dbField[4] = "id_poli";
$dbField[5] = "id_dokter";
$dbField[6] = "jumlah_item";
$dbField[7] = "is_racikan";
$dbField[8] = "racikan_id_utama";

$historyTerapiId =  $rawatItemId;
$dbValue[0] = QuoteValue(DPE_CHAR, $historyTerapiId);
$dbValue[1] =  QuoteValue(DPE_CHAR, $Item['item_nama']);;
$dbValue[2] = QuoteValue(DPE_CHAR, $racikanhistory["id_reg"]);
$dbValue[3] = QuoteValue(DPE_CHAR, $racikanhistory["id_cust_usr"]);
$dbValue[4] = QuoteValue(DPE_CHAR, '33');
$dbValue[5] = QuoteValue(DPE_CHAR, $racikanhistory["id_dokter"]);
$dbValue[6] = QuoteValue(DPE_CHAR, $_POST["dosis"]);
$dbValue[7] = QuoteValue(DPE_CHAR, 'y');
$dbValue[8] = QuoteValue(DPE_CHAR, $_GET['rawat_terapi_racikan_id']);

$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
$c = $dtmodel->insert() or die("insert  error");

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);
