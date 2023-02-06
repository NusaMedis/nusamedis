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

$sql = "select * from apotik.apotik_jam_aturan_pakai where jam_aturan_pakai_id = " . QuoteValue(DPE_CHAR, $_POST['jam_aturan_pakai_id']);
$JamAturanPakai = $dtaccess->Fetch($sql);

$sql = "select * from apotik.apotik_aturan_pakai where aturan_pakai_id = " . QuoteValue(DPE_CHAR, $_POST['aturan_pakai_id']);
$AturanPakai = $dtaccess->Fetch($sql);

$sql = "select * from apotik.apotik_aturan_minum where aturan_minum_id = " . QuoteValue(DPE_CHAR, $_POST['aturan_minum_id']);
$AturanMinum = $dtaccess->Fetch($sql);

$sql = "select * from apotik.apotik_obat_petunjuk where petunjuk_id = " . QuoteValue(DPE_CHAR, $_POST['petunjuk_id']);
$Dosis = $dtaccess->Fetch($sql);

$sql = "select * from logistik.logistik_item_satuan where satuan_tipe = 'J' and satuan_id = " . QuoteValue(DPE_CHAR, $_POST['satuan_id']);
$Satuan = $dtaccess->Fetch($sql);

$sql = "select * from apotik.apotik_jenis_racikan where jenis_racikan_id = " . QuoteValue(DPE_CHAR, $_POST['jenis_racikan_id']);
$JenisRacikan = $dtaccess->Fetch($sql);

$sql = "select id_gudang from global.global_auth_poli where poli_id=" . QuoteValue(DPE_CHAR, '33');
$gudang = $dtaccess->Fetch($sql);

$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif 

switch ($_GET['func']) {
     case 'store':
          $dbTable = "klinik.klinik_perawatan_terapi_racikan";
          $dbField[0] = "rawat_terapi_racikan_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_jenis_racikan";
          $dbField[3] = "rawat_terapi_racikan_jumlah";
          $dbField[4] = "id_satuan";
          $dbField[5] = "id_petunjuk";
          $dbField[6] = "id_aturan_minum";
          $dbField[7] = "id_aturan_pakai";
          $dbField[8] = "id_jam_aturan_pakai";
          $dbField[9] = "jenis_racikan_nama";
          $dbField[10] = "satuan_nama";
          $dbField[11] = "petunjuk_nama";
          $dbField[12] = "aturan_minum_nama";
          $dbField[13] = "aturan_pakai_nama";
          $dbField[14] = "jam_aturan_pakai_nama";
          //$dbField[9] = "rawat_terapi_racikan_urut";

          $rawatTerapiRacikanId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR, $rawatTerapiRacikanId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["id_rawat"]);
          $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["jenis_racikan_id"]);
          $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_racikan_jumlah"]);
          $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["satuan_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
          $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
          $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["aturan_pakai_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
          $dbValue[9] = QuoteValue(DPE_CHAR, $JenisRacikan["jenis_racikan_nama"]);
          $dbValue[10] = QuoteValue(DPE_CHAR, $Satuan["satuan_nama"]);
          $dbValue[11] = QuoteValue(DPE_CHAR, $Dosis["petunjuk_nama"]);
          $dbValue[12] = QuoteValue(DPE_CHAR, $AturanMinum["aturan_minum_nama"]);
          $dbValue[13] = QuoteValue(DPE_CHAR, $AturanPakai["aturan_pakai_nama"]);
          $dbValue[14] = QuoteValue(DPE_CHAR, $JamAturanPakai["jam_aturan_pakai_nama"]);
          //$dbValue[9] = QuoteValue(DPE_CHAR,$Item["item_nama"]);           

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $a = $dtmodel->insert() or die("insert  error");
          $rs = [];
          if ($a) {
               $rs['rawat_terapi_racikan_id'] = $rawatTerapiRacikanId;
               $rs['petunjuk_nama'] = $Dosis['petunjuk_nama'];
               $rs['jenis_racikan_nama'] = $JenisRacikan['jenis_racikan_nama'];
               $rs['satuan_nama'] = $Satuan['satuan_nama'];
               $rs['aturan_minum_nama'] = $AturanMinum['aturan_minum_nama'];
               $rs['aturan_pakai_nama'] = $AturanPakai['aturan_pakai_nama'];
               $rs['jam_aturan_pakai_nama'] = $JamAturanPakai['jam_aturan_pakai_nama'];
               $rs['rawat_terapi_racikan_jumlah'] = $_POST["rawat_terapi_racikan_jumlah"];
          }

          echo json_encode($rs);
          // exit();
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          // get Nama cutomer 
          $sql = "select cust_usr_nama from klinik.klinik_registrasi a
                 left join global.global_customer_user b on b.cust_usr_id=a.id_cust_usr where a.reg_id='" . $_POST["id_reg"] . "'";
          $q = $dtaccess->fetchAll($sql);

          //   insert ke apotik_nama_racikan

          $dbTable = "apotik.apotik_nama_racikan";
          $dbField[0] = "nama_racikan_id";   // PK
          $dbField[1] = "nama_racikan_nama";
          $dbField[2] = "nama_racikan_jenis";

          $dbValue[0] =  QuoteValue(DPE_CHAR, $rawatTerapiRacikanId);
          $dbValue[1] = QuoteValue(DPE_CHAR, "Racikan " . $q[0]["cust_usr_nama"] . '_' . date('d/m/Y'));
          $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["jenis_racikan_id"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $a = $dtmodel->insert() or die("insert  error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $ItemId = $rawatTerapiRacikanId;
          // insert ke table logistik item 
          $dbTable = "logistik.logistik_item";
          $dbField[0] = "item_id";   // PK
          $dbField[1] = "item_nama";
          $dbField[2] = "item_racikan";

          $dbValue[0] =  QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[1] = QuoteValue(DPE_CHAR, "Racikan " . $q[0]["cust_usr_nama"] . '_' . date('d/m/Y'));
          $dbValue[2] = QuoteValue(DPE_CHAR, "y");

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $a = $dtmodel->insert() or die("insert  error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $dbTable = "logistik.logistik_item_batch";

          $dbField[0] = "batch_id";   // PK
          $dbField[1] = "id_item";
          $dbField[2] = "batch_no";
          $dbField[3] = "batch_create";
          $dbField[4] = "id_dep";
          $dbField[5] = "batch_flag";

          $batchId = $dtaccess->GetTransId();
          $dbValue[0] = QuoteValue(DPE_CHAR, $batchId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[2] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[3] = QuoteValue(DPE_DATE, date('Y-m-d H:i:s'));
          $dbValue[4] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[5] = QuoteValue(DPE_CHAR, 'A');

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_LOGISTIK);
          $dtmodel->Insert() or die("insert  error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $dbTable = "logistik.logistik_stok_item";

          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep";

          $date = date("Y-m-d H:i:s");

          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR, $stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC, 0);
          $dbValue[2] = QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[3] = QuoteValue(DPE_CHAR, $theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR, 'A');
          $dbValue[5] = QuoteValue(DPE_DATE, $date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC, '0');
          $dbValue[7] = QuoteValue(DPE_CHAR, $depId);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
          $dtmodel->Insert() or die("insert  error");

          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $dbTable = "logistik.logistik_stok_dep";

          $dbField[0]  = "stok_dep_id";   // PK
          $dbField[1]  = "stok_dep_saldo";
          $dbField[2]  = "id_item";
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_dep_tgl";
          $dbField[5]  = "stok_dep_create";
          $dbField[6]  = "id_dep";

          $date = date("Y-m-d H:i:s");

          $stokdepid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR, $stokdepid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC, '0');
          $dbValue[2] = QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[3] = QuoteValue(DPE_CHAR, $theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_DATE, date('Y-m-d'));
          $dbValue[5] = QuoteValue(DPE_DATE, $date);
          $dbValue[6] = QuoteValue(DPE_CHAR, $depId);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
          $dtmodel->Insert() or die("insert  error");

          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $dbTable = "logistik.logistik_stok_item_batch";

          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "id_batch";

          $date = date("Y-m-d H:i:s");

          $stokbatchid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR, $stokbatchid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC, 0);
          $dbValue[2] = QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[3] = QuoteValue(DPE_CHAR, $theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR, 'A');
          $dbValue[5] = QuoteValue(DPE_DATE, $date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC, '0');
          $dbValue[7] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[8] = QuoteValue(DPE_CHAR, $batchId);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
          $dtmodel->Insert() or die("insert  error");

          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

          $dbTable = "logistik.logistik_stok_batch_dep";

          $dbField[0]  = "stok_batch_dep_id";   // PK
          $dbField[1]  = "stok_batch_dep_saldo";
          $dbField[2]  = "id_item";
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_batch_dep_tgl";
          $dbField[5]  = "stok_batch_dep_create";
          $dbField[6]  = "id_dep";
          $dbField[7]  = "id_batch";

          $date = date("Y-m-d H:i:s");

          $stokbatchdepid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR, $stokbatchdepid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC, '0');
          $dbValue[2] = QuoteValue(DPE_CHAR, $ItemId);
          $dbValue[3] = QuoteValue(DPE_CHAR, $theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_DATE, date('Y-m-d'));
          $dbValue[5] = QuoteValue(DPE_DATE, $date);
          $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[7] = QuoteValue(DPE_CHAR, $batchId);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
          $dtmodel->Insert() or die("insert  error");

          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          break;

     case 'update':
          $dbTable = "klinik.klinik_perawatan_terapi_racikan";
          $dbField[0] = "rawat_terapi_racikan_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_jenis_racikan";
          $dbField[3] = "rawat_terapi_racikan_jumlah";
          $dbField[4] = "id_satuan";
          $dbField[5] = "id_petunjuk";
          $dbField[6] = "id_aturan_minum";
          $dbField[7] = "id_aturan_pakai";
          $dbField[8] = "id_jam_aturan_pakai";
          $dbField[9] = "jenis_racikan_nama";
          $dbField[10] = "satuan_nama";
          $dbField[11] = "petunjuk_nama";
          $dbField[12] = "aturan_minum_nama";
          $dbField[13] = "aturan_pakai_nama";
          $dbField[14] = "jam_aturan_pakai_nama";
          //$dbField[9] = "rawat_terapi_racikan_urut";

          $dbValue[0] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_racikan_id"]);
          $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["id_rawat"]);
          $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["jenis_racikan_id"]);
          $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_racikan_jumlah"]);
          $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["satuan_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
          $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
          $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["aturan_pakai_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
          $dbValue[9] = QuoteValue(DPE_CHAR, $JenisRacikan["jenis_racikan_nama"]);
          $dbValue[10] = QuoteValue(DPE_CHAR, $Satuan["satuan_nama"]);
          $dbValue[11] = QuoteValue(DPE_CHAR, $Dosis["petunjuk_nama"]);
          $dbValue[12] = QuoteValue(DPE_CHAR, $AturanMinum["aturan_minum_nama"]);
          $dbValue[13] = QuoteValue(DPE_CHAR, $AturanPakai["aturan_pakai_nama"]);
          $dbValue[14] = QuoteValue(DPE_CHAR, $JamAturanPakai["jam_aturan_pakai_nama"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $a = $dtmodel->update() or die("insert  error");
          $rs = [];
          if ($a) {
               $rs['rawat_terapi_racikan_id'] = $_POST['rawat_terapi_racikan_id'];
               $rs['id_rawat'] = $_POST['id_rawat'];
               $rs['petunjuk_nama'] = $Dosis['petunjuk_nama'];
               $rs['jenis_racikan_nama'] = $JenisRacikan['jenis_racikan_nama'];
               $rs['satuan_nama'] = $Satuan['satuan_nama'];
               $rs['aturan_minum_nama'] = $AturanMinum['aturan_minum_nama'];
               $rs['aturan_pakai_nama'] = $AturanPakai['aturan_pakai_nama'];
               $rs['jam_aturan_pakai_nama'] = $JamAturanPakai['jam_aturan_pakai_nama'];
               $rs['rawat_terapi_racikan_jumlah'] = $_POST["rawat_terapi_racikan_jumlah"];
          }

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          break;

     case 'destroy':
          // hapus terapi racikan
          $sql = 'DELETE from klinik.klinik_perawatan_terapi_racikan';
          $sql .= ' WHERE rawat_terapi_racikan_id = ' . QuoteValue(DPE_CHAR, $_POST['id']);
          $dtaccess->execute($sql);

          // hapus data racikan Nama
          $sql = 'DELETE from apotik.apotik_nama_racikan';
          $sql .= ' WHERE nama_racikan_id = ' . QuoteValue(DPE_CHAR, $_POST['id']);
          $dtaccess->execute($sql);

          // hapus data logistik
          // $sql = 'DELETE from logistik.logistik_item';
          // $sql .= ' WHERE item_id = ' . QuoteValue(DPE_CHAR, $_POST['id']);
          // $dtaccess->execute($sql);


          // hapus data Penjualan Detail
          $sql = 'DELETE from apotik.apotik_penjualan_detail';
          $sql .= ' WHERE penjualan_detail_id = ' . QuoteValue(DPE_CHAR, $_POST['id']);
          $dtaccess->execute($sql);

          echo json_encode(['success' => true]);
          break;

     default:
          $sql = 'SELECT a.rawat_terapi_racikan_id,a.id_rawat,a.jenis_racikan_nama,a.satuan_nama,rawat_terapi_racikan_id, rawat_terapi_racikan_jumlah, id_jenis_racikan,id_satuan, id_aturan_minum, id_aturan_pakai, a.id_jam_aturan_pakai, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi_racikan a';
          $sql .= ' LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ';
          $sql .= ' LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ';
          $sql .= ' LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ';
          $sql .= ' LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.id_petunjuk ';
          $sql .= ' LEFT JOIN logistik.logistik_item_satuan h on h.satuan_id = a.id_satuan ';
          $sql .= ' LEFT JOIN apotik.apotik_jenis_racikan i on i.jenis_racikan_id = a.id_jenis_racikan ';
          $sql .= ' WHERE id_rawat = ' . QuoteValue(DPE_CHAR, $_POST['rawat_id']);
          $q = $dtaccess->fetchAll($sql);
          echo json_encode($q);
          break;
}

$penjualanDetailId =  $dtaccess->GetTransId();
$regId = $dtaccess->GetTransId();
$folId = $dtaccess->GetTransId();

$sql = "select b.reg_jenis_pasien,a.cust_usr_nama,b.id_dokter,a.cust_usr_id,c.pembayaran_id,b.id_poli
        from global.global_customer_user a left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id 
        left join klinik.klinik_pembayaran c on c.id_reg = b.reg_id where reg_id = " . QuoteValue(DPE_CHAR, $_POST['id_reg']);
$dtPasien = $dtaccess->Fetch($sql);
//echo $sql;
$sql = "select penjualan_id from apotik.apotik_penjualan where id_pembayaran = " . QuoteValue(DPE_CHAR, $dtPasien['pembayaran_id']);
$dtFarmasii = $dtaccess->Fetch($sql);
//echo $sql;
$sql = "select * from apotik.apotik_conf where id_dep = " . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konf = $dtaccess->Fetch($rs);
$_POST["txtResep"] = $konf["conf_biaya_resep"];  //Konfigurasi Resep Pasien
if ($konf["conf_biaya_tuslag_persen"] != "y") {
     $_POST["txtTuslag"] = $konf["conf_biaya_tuslag"];  //Konfigurasi Tuslag Pasien
}

$hargaSatuan = $Item['item_harga_jual'];
$konfig = ($konf["conf_biaya_tuslag"] / 100);
$hargaJumlah = $Item['item_harga_jual'] * $_POST['terapi_jumlah_item'];

$ppn = $hargaSatuan * $_POST['terapi_jumlah_item'] * 0.1;
$tuslag = $konfig * ($ppn + $hargaJumlah);
$total = $tuslag + $ppn + $hargaJumlah;
// $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and penjualan_flag = 'D'";
$sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where penjualan_flag = 'D'";
$lastKode = $dtaccess->Fetch($sql);
$skr = date("Y-m-d");
$tgl = explode("-", $skr);
$_POST["hidUrut"] = $lastKode["urut"] + 1;
$_POST["penjualan_no"] = "APRJ" . str_pad($lastKode["urut"] + 1, 5, "0", STR_PAD_LEFT) . "/" . $tgl[2] . "/" . $tgl[1] . "/" . $tgl[0];

if ($_GET['func'] == 'store') {
     //echo $sql;
     if ($dtFarmasii['penjualan_id'] == '') {

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


          $penjualanId   = $rawatTerapiRacikanId;
          $dbValue[0] = QuoteValue(DPE_CHAR, $penjualanId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_nama"]);
          $dbValue[2] = QuoteValue(DPE_DATE, $now);
          $dbValue[3] = QuoteValue(DPE_NUMBER, $dtPasien["reg_jenis_pasien"]);
          $dbValue[4] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
          $dbValue[5] = QuoteValue(DPE_CHAR, $regId);
          $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["pembayaran_id"]);
          $dbValue[9] = QuoteValue(DPE_CHAR, '2');
          $dbValue[10] = QuoteValue(DPE_CHAR, 'D');
          $dbValue[11] = QuoteValue(DPE_CHAR, $_POST['penjualan_no']);
          $dbValue[12] = QuoteValue(DPE_CHAR, $lastKode['urut'] + 1);
          //print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $b = $dtmodel->insert() or die("insert  error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);

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
          $dbValue[10] = QuoteValue(DPE_CHAR, $dtPasien["pembayaran_id"]);
          $dbValue[11] = QuoteValue(DPE_DATE, $now);
          $dbValue[12] = QuoteValue(DPE_CHAR, $_POST['id_reg']);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $c = $dtmodel->insert() or die("insert  error");

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


          $dbValue[0] = QuoteValue(DPE_CHAR, $folId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $regId);
          $dbValue[2] = QuoteValue(DPE_CHAR, 'Penjualan Obat');
          $dbValue[3] = QuoteValue(DPE_CHAR, 'OA');
          $dbValue[4] = QuoteValue(DPE_DATE, $now);
          $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien['reg_jenis_pasien']);
          $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["pembayaran_id"]);
          //print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          $x = $dtmodel->insert() or die("insert  error");

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
     }
     $dbTable = "apotik.apotik_penjualan_detail";
     $dbField[0] = "penjualan_detail_id";   // PK
     $dbField[1] = "id_penjualan";
     $dbField[2] = "id_item";
     $dbField[3] = "id_petunjuk";
     $dbField[4] = "id_dep";
     $dbField[5] = "id_aturan_minum";
     $dbField[6] = "id_aturan_pakai";
     $dbField[7] = "id_jam_aturan_pakai";
     $dbField[8] = "item_nama";
     $dbField[9] = "penjualan_detail_create";
     $dbField[10] = "penjualan_detail_jumlah";
     $dbField[11] = "penjualan_detail_harga_jual";
     $dbField[12] = "penjualan_detail_total";
     $dbField[13] = "penjualan_detail_tuslag";
     $dbField[14] = "penjualan_detail_ppn";
     $dbField[15] = "id_rawat_item";

     $sql = "select penjualan_id from apotik.apotik_penjualan where id_pembayaran = " . QuoteValue(DPE_CHAR, $dtPasien['pembayaran_id']);
     $dtFarmasi = $dtaccess->Fetch($sql);
     //echo $sql;

     $dbValue[0] = QuoteValue(DPE_CHAR, $penjualanDetailId);
     $dbValue[1] = QuoteValue(DPE_CHAR, $dtFarmasi['penjualan_id']);
     $dbValue[2] = QuoteValue(DPE_CHAR, $ItemId);
     $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, $depId);
     $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST['aturan_pakai_id']);
     $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
     $dbValue[8] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[9] = QuoteValue(DPE_DATE, $now);
     $dbValue[10] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_racikan_jumlah"]);
     $dbValue[11] = QuoteValue(DPE_CHAR, 0);
     $dbValue[12] = QuoteValue(DPE_CHAR, $total);
     $dbValue[13] = QuoteValue(DPE_CHAR, $tuslag);
     $dbValue[14] = QuoteValue(DPE_CHAR, $ppn);
     $dbValue[15] = QuoteValue(DPE_CHAR, $_POST['rawat_id']);

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     $b = $dtmodel->insert() or die("insert  error");

     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);
}

if ($_GET['func'] == 'store') {


     $dbTable = "klinik.klinik_history_terapi";
     $dbField[0] = "history_terapi_id";   // PK
     $dbField[1] = "nama_item";
     $dbField[2] = "id_reg";
     $dbField[3] = "id_cust_usr";
     $dbField[4] = "id_poli";
     $dbField[5] = "id_dokter";
     $dbField[6] = "jumlah_item";
     $dbField[7] = "is_racikan";

     $historyTerapiId =  $rawatTerapiRacikanId;
     $dbValue[0] = QuoteValue(DPE_CHAR, $historyTerapiId);
     $dbValue[1] =  QuoteValue(DPE_CHAR, "Racikan " . $q[0]["cust_usr_nama"] . '_' . date('d/m/Y'));;
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
     $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, '33');
     $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_jumlah_item"]);
     $dbValue[7] = QuoteValue(DPE_CHAR, 'y');

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     $c = $dtmodel->insert() or die("insert  error");

     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);
} elseif ($_GET['func'] == 'destroy') {
     $sql = "delete from klinik.klinik_history_terapi where history_terapi_id = " . QuoteValue(DPE_CHAR, $_POST['id']);
     $dtaccess->Execute($sql);
     //echo $sql;
} elseif ($_GET['func'] == 'update') {


     $dbTable = "klinik.klinik_history_terapi";
     $dbField[0] = "history_terapi_id";   // PK
     $dbField[1] = "nama_item";
     $dbField[2] = "id_reg";
     $dbField[3] = "id_cust_usr";
     $dbField[4] = "id_poli";
     $dbField[5] = "id_dokter";
     $dbField[6] = "jumlah_item";
     $dbField[7] = "is_racikan";

     $dbValue[0] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_racikan_id"]);
     $dbValue[1] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
     $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, '33');
     $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["rawat_terapi_jumlah_item"]);
     $dbValue[7] = QuoteValue(DPE_CHAR, 'y');

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     $c = $dtmodel->update() or die("update  error");

     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);
}
// echo $_POST["penjualan_no"];
