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

$sql = "select * from logistik.logistik_item where item_id = " . QuoteValue(DPE_CHAR, $_POST['item_id']);
$Item = $dtaccess->Fetch($sql);

$sql = "SELECT * from logistik.logistik_stok_dep where id_gudang = '2' and id_item = ".QuoteValue(DPE_CHAR, $_POST["item_id"]);
          $stok = $dtaccess->Fetch($sql);

$sql = "SELECT b.reg_jenis_pasien,a.cust_usr_nama,b.id_dokter,a.cust_usr_id,c.pembayaran_id,b.id_poli, pembayaran_flag from global.global_customer_user a left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id  left join klinik.klinik_pembayaran c on c.id_reg = b.reg_id where reg_id = " . QuoteValue(DPE_CHAR, $_POST['id_reg']);
$dtPasien = $dtaccess->Fetch($sql);

        
if($stok['stok_dep_saldo'] >= $_POST["terapi_jumlah_item"]){
    
}

switch ($_GET['func']) {
     case 'store':
          

          $dbTable = "klinik.klinik_perawatan_terapi";
          $dbField[0] = "rawat_item_id";   // PK
          $dbField[1] = "id_rawat";
          $dbField[2] = "id_item";
          $dbField[3] = "terapi_jumlah_item";
          $dbField[4] = "terapi_dosis";
          $dbField[5] = "id_reg";
          $dbField[6] = "id_aturan_minum";
          $dbField[7] = "id_aturan_pakai";
          $dbField[8] = "id_jam_aturan_pakai";
          $dbField[9] = "item_nama";
          $dbField[10] = "petunjuk_nama";
          $dbField[11] = "aturan_minum_nama";
          $dbField[12] = "aturan_pakai_nama";
          $dbField[13] = "jam_aturan_pakai_nama";

          $rawatItemId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR, $rawatItemId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["id_rawat"]);
          $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["item_id"]);
          $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
          $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
          $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
          $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["aturan_pakai_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
          $dbValue[9] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
          $dbValue[10] = QuoteValue(DPE_CHAR, $Dosis["petunjuk_nama"]);
          $dbValue[11] = QuoteValue(DPE_CHAR, $AturanMinum["aturan_minum_nama"]);
          $dbValue[12] = QuoteValue(DPE_CHAR, $AturanPakai["aturan_pakai_nama"]);
          $dbValue[13] = QuoteValue(DPE_CHAR, $JamAturanPakai["jam_aturan_pakai_nama"]);
          //print_r($dbValue);die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);

          if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
               $a = "Terbayar";
          }
          else{
               $a = $dtmodel->insert() or die("insert  error");
          }
          

          $rs = [];
          if ($a) {
               $rs['rawat_item_id'] = $rawatItemId;
               $rs['item_id'] = $_POST['item_id'];
               $rs['item_nama'] = $Item['item_nama'];
               $rs['petunjuk_nama'] = $Dosis['petunjuk_nama'];
               $rs['aturan_minum_nama'] = $AturanMinum['aturan_minum_nama'];
               $rs['aturan_pakai_nama'] = $AturanPakai['aturan_pakai_nama'];
               $rs['jam_aturan_pakai_nama'] = $JamAturanPakai['jam_aturan_pakai_nama'];
               $rs['terapi_jumlah_item'] = $_POST["terapi_jumlah_item"];
          }

          echo json_encode($rs);

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
        
          
          break;
     case 'update':
          $dbTable = "klinik.klinik_perawatan_terapi";
          $dbField[0] = "rawat_item_id";   // PK
          $dbField[1] = "id_item";
          $dbField[2] = "terapi_jumlah_item";
          $dbField[3] = "terapi_dosis";
          $dbField[4] = "id_reg";
          $dbField[5] = "id_aturan_minum";
          $dbField[6] = "id_aturan_pakai";
          $dbField[7] = "id_jam_aturan_pakai";
          $dbField[8] = "item_nama";
          $dbField[9] = "petunjuk_nama";
          $dbField[10] = "aturan_minum_nama";
          $dbField[11] = "aturan_pakai_nama";
          $dbField[12] = "jam_aturan_pakai_nama";

          $dbValue[0] = QuoteValue(DPE_CHAR, $_POST["rawat_item_id"]);
          $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["item_id"]);
          $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
          $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
          $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
          $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["aturan_pakai_id"]);
          $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
          $dbValue[9] = QuoteValue(DPE_CHAR, $Dosis["petunjuk_nama"]);
          $dbValue[10] = QuoteValue(DPE_CHAR, $AturanMinum["aturan_minum_nama"]);
          $dbValue[11] = QuoteValue(DPE_CHAR, $AturanPakai["aturan_pakai_nama"]);
          $dbValue[12] = QuoteValue(DPE_CHAR, $JamAturanPakai["jam_aturan_pakai_nama"]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
          if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          
          }
          else{
               $a = $dtmodel->update() or die("insert  error");
          }
          $rs = [];
          if ($a) {
               $rs['rawat_item_id'] = $_POST['rawat_item_id'];
               $rs['item_id'] = $_POST['item_id'];
               $rs['item_nama'] = $Item['item_nama'];
               $rs['petunjuk_nama'] = $Dosis['petunjuk_nama'];
               $rs['aturan_minum_nama'] = $AturanMinum['aturan_minum_nama'];
               $rs['aturan_pakai_nama'] = $AturanPakai['aturan_pakai_nama'];
               $rs['jam_aturan_pakai_nama'] = $JamAturanPakai['jam_aturan_pakai_nama'];
          }

          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          break;

     case 'destroy':
          $sql = 'DELETE from klinik.klinik_perawatan_terapi';
          $sql .= ' WHERE rawat_item_id = ' . QuoteValue(DPE_CHAR, $_POST['id']);
          if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          
          }
          else{
               $dtaccess->execute($sql);
          }
          echo json_encode(['success' => true]);
          break;

     default:
          $sql = "SELECT rawat_item_id, id_item, terapi_jumlah_item, terapi_dosis, id_aturan_minum, id_aturan_pakai, a.id_jam_aturan_pakai, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
          $sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
          $sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
          $sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
          $sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
          $sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
          $sql .= " WHERE id_rawat = " . QuoteValue(DPE_CHAR, $_POST['rawat_id']);
          $sql .= " AND id_rawat_terapi_racikan is null ";
          $q = $dtaccess->fetchAll($sql);


          echo json_encode($q);
          //echo $sql;
          break;
}
$penjualanId = $dtaccess->GetTransId();
$penjualanDetailId = $dtaccess->GetTransId();
$regId = $dtaccess->GetTransId();
$folId = $dtaccess->GetTransId();


//echo $sql;
$sql = "select penjualan_id from apotik.apotik_penjualan where id_pembayaran = " . QuoteValue(DPE_CHAR, $dtPasien['pembayaran_id']);
$dtFarmasii = $dtaccess->Fetch($sql);
// echo $sql;


$sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and penjualan_flag = 'D'";
$lastKode = $dtaccess->Fetch($sql);
$skr = date("Y-m-d");
$tgl = explode("-", $skr);
$_POST["hidUrut"] = $lastKode["urut"] + 1;
$_POST["penjualan_no"] = "APRJ" . str_pad($lastKode["urut"] + 1, 5, "0", STR_PAD_LEFT) . "/" . $tgl[2] . "/" . $tgl[1] . "/" . $tgl[0];
// echo $_POST["penjualan_no"];
// exit();
if ($_GET['func'] == 'store') {
     //echo $sql;
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


          $dbValue[0] = QuoteValue(DPE_CHAR, $folId);
          $dbValue[1] = QuoteValue(DPE_CHAR, $regId);
          $dbValue[2] = QuoteValue(DPE_CHAR, 'Penjualan Obat');
          $dbValue[3] = QuoteValue(DPE_CHAR, 'OA');
          $dbValue[4] = QuoteValue(DPE_DATE, $now);
          $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien['reg_jenis_pasien']);
          $dbValue[6] = QuoteValue(DPE_CHAR, $depId);
          $dbValue[7] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
          $dbValue[8] = QuoteValue(DPE_CHAR, $dtPasien["pembayaran_id"]);
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

     $HargaJumlah = StripCurrency($hargajual) * StripCurrency($_POST['terapi_jumlah_item']);
     $PajakJumlah = StripCurrency(intval(($hargamargin * $_POST['terapi_jumlah_item']) * 0.1));
     $HargaBeliJumlah = StripCurrency($Item['item_hpp']) * StripCurrency($_POST['terapi_jumlah_item']);
     $TuslagJumlah = StripCurrency(intval($konfig * $HargaJumlah));
     $hargapokok = StripCurrency(intval($_POST['terapi_jumlah_item'] * $hargamargin));
     $totalAll = ($HargaJumlah + $TuslagJumlah);
     
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
     $dbField[16] = "penjualan_detail_harga_pokok";
     $dbField[17] = "penjualan_detail_harga_beli";

     $sql = "select penjualan_id from apotik.apotik_penjualan where id_pembayaran = " . QuoteValue(DPE_CHAR, $dtPasien['pembayaran_id']);
     $dtFarmasi = $dtaccess->Fetch($sql);
     //echo $sql;

     $dbValue[0] = QuoteValue(DPE_CHAR, $penjualanDetailId);
     $dbValue[1] = QuoteValue(DPE_CHAR, $dtFarmasi['penjualan_id']);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['item_id']);
     $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, $depId);
     $dbValue[5] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST['aturan_pakai_id']);
     $dbValue[7] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
     $dbValue[8] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[9] = QuoteValue(DPE_DATE, $now);
     $dbValue[10] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
     $dbValue[11] = QuoteValue(DPE_CHAR, StripCurrency($hargamargin));
     $dbValue[12] = QuoteValue(DPE_NUMERIC, $hargapokok + $PajakJumlah + $TuslagJumlah );
     $dbValue[13] = QuoteValue(DPE_CHAR, StripCurrency($TuslagJumlah));
     $dbValue[14] = QuoteValue(DPE_CHAR, StripCurrency($PajakJumlah));
     $dbValue[15] = QuoteValue(DPE_CHAR, $rawatItemId);
     $dbValue[16] = QuoteValue(DPE_NUMERIC, StripCurrency($hargapokok));
     $dbValue[17] = QuoteValue(DPE_NUMERIC, StripCurrency($HargaBeliJumlah));
// print_r($dbValue);die();
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          $b = "Terbayar";
     }
     else{
          
          $b = $dtmodel->insert() or die("insert  error");
     }
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);

    //   $poli = "33"; //POLI APOTIK IRJ 
    //   $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
    //   $rs = $dtaccess->Execute($sql);
    //   $gudang = $dtaccess->Fetch($rs); 
    //   $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  

    // /* Cari jml Stok di gudang */
    //     $sql = "select stok_dep_saldo from logistik.logistik_stok_dep 
    //     where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
    //     $stok= $dtaccess->Fetch($sql); 

    //     $sisa_stok = $stok['stok_dep_saldo'] - $_POST["txtJumlah"] ;

    //     $sql = "select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$_POST["item_id"]);
    //     $rs = $dtaccess->Execute($sql);
    //     $dataHargabeli = $dtaccess->Fetch($rs);

    // /* insert data logistik */
    //    $dbTable = "logistik.logistik_stok_item";
    //    $dbField[0]  = "stok_item_id";   // PK
    //    $dbField[1]  = "stok_item_jumlah";
    //    $dbField[2]  = "id_item";    
    //    $dbField[3]  = "id_gudang";
    //    $dbField[4]  = "stok_item_flag";
    //    $dbField[5]  = "stok_item_create";       
    //    $dbField[6]  = "stok_item_saldo";
    //    $dbField[7]  = "id_dep";
    //    $dbField[8]  = "stok_item_keterangan";
    //    $dbField[9]  = "id_penjualan";
    //    $dbField[10]  = "stok_item_hpp";
    //    $dbField[11]  = "stok_item_hna";
    //    $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
       
    //    $date = date("Y-m-d H:i:s");
    //    $stokid = $dtaccess->GetTransID();
    //    $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
    //    $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);  
    //    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["item_id"]);
    //    $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
    //    $dbValue[4] = QuoteValue(DPE_CHAR,'P');
    //    $dbValue[5] = QuoteValue(DPE_DATE,$date);
    //    $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stok)); 
    //    $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
    //    $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
    //    $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
    //    $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
    //    $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
    //    $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
       
          
    //    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    //    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    //    if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
    //       echo "Terbayar";
    //    }
    //    else{
          
    //       $dtmodel->Insert() or die("insert  error");
    //    }
    //    unset($dbTable);
    //    unset($dbField);
    //    unset($dbValue);
    //    unset($dbKey); 
       

    // /* update stok dep */
    //    $sql = "update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stok)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))."
    //    where id_item = ".QuoteValue(DPE_CHAR,$_POST["item_id"])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
    //    $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

} elseif ($_GET['func'] == 'destroy') {
     $sql = "delete from apotik.apotik_penjualan_detail where id_rawat_item = " . QuoteValue(DPE_CHAR, $_POST['id']);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          
     }
     else{
          $dtaccess->Execute($sql);
     }
     //echo $sql;
} elseif ($_GET['func'] == 'update') {

     $dbTable = "apotik.apotik_penjualan_detail";
     $dbField[0] = "id_rawat_item";
     $dbField[1] = "id_item";
     $dbField[2] = "id_petunjuk";
     $dbField[3] = "id_dep";
     $dbField[4] = "id_aturan_minum";
     $dbField[5] = "id_aturan_pakai";
     $dbField[6] = "id_jam_aturan_pakai";
     $dbField[7] = "item_nama";
     $dbField[8] = "penjualan_detail_create";
     $dbField[9] = "penjualan_detail_jumlah";
     $dbField[10] = "penjualan_detail_harga_jual";
     $dbField[11] = "penjualan_detail_total";
     $dbField[12] = "penjualan_detail_tuslag";
     $dbField[13] = "penjualan_detail_ppn";

     $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['rawat_item_id']);
     $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['item_id']);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["petunjuk_id"]);
     $dbValue[3] = QuoteValue(DPE_CHAR, $depId);
     $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["aturan_minum_id"]);
     $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['aturan_pakai_id']);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["jam_aturan_pakai_id"]);
     $dbValue[7] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[8] = QuoteValue(DPE_DATE, $now);
     $dbValue[9] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
     $dbValue[10] = QuoteValue(DPE_CHAR, $Item["item_harga_jual"]);
     $dbValue[11] = QuoteValue(DPE_CHAR, $total);
     $dbValue[12] = QuoteValue(DPE_CHAR, $tuslag);
     $dbValue[13] = QuoteValue(DPE_CHAR, $ppn);

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          $r = "Terbayar";
     }
     else{
          $r = $dtmodel->update() or die("insert  error");
     }
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

     $historyTerapiId =  $rawatItemId;
     $dbValue[0] = QuoteValue(DPE_CHAR, $historyTerapiId);
     $dbValue[1] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
     $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, '33');
     $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
     $dbValue[7] = QuoteValue(DPE_CHAR, 'n');

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          $c = "Terbayar";
     }
     else{
          $c = $dtmodel->insert() or die("insert  error");
     }
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);

} elseif ($_GET['func'] == 'destroy') {
     $sql = "delete from klinik.klinik_history_terapi where history_terapi_id = " . QuoteValue(DPE_CHAR, $_POST['id']);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          
     } 
     else{
          $dtaccess->Execute($sql);
     }
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


     $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['rawat_item_id']);
     $dbValue[1] = QuoteValue(DPE_CHAR, $Item["item_nama"]);
     $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
     $dbValue[3] = QuoteValue(DPE_CHAR, $dtPasien["cust_usr_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR, '33');
     $dbValue[5] = QuoteValue(DPE_CHAR, $dtPasien["id_dokter"]);
     $dbValue[6] = QuoteValue(DPE_CHAR, $_POST["terapi_jumlah_item"]);
     $dbValue[7] = QuoteValue(DPE_CHAR, 'n');

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);
     if($dtPasien['pembayaran_flag'] == 'y' || $dtPasien['pembayaran_flag'] == 'p'){
          
     }
     else{
          $c = $dtmodel->update() or die("update  error");
     }
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);
}
