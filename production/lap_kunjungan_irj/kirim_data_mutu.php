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
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
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

$type = $_GET['type'];




$tgl_awal = $_GET['tgl_awal'];
$tgl_akhir  = $_GET['tgl_akhir'];
$reg_jenis_pasien = $_GET['reg_jenis_pasien'];
$id_poli = $_GET['id_poli'];
$id_dokter  = $_GET['id_dokter'];
$reg_status_pasien = $_GET['reg_status_pasien'];
$cust_usr_jkn  = $_GET['cust_usr_jkn'];
$kondisi_akhir = $_GET['kondisi_akhir'];


if (!$_GET["klinik"]) $_GET["klinik"]=$depId;
else  $_GET["klinik"] = $_GET["klinik"];

     // KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_GET["klinik"]);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

$skr = date("d-m-Y");
$time = date("H:i:s");

if(!$_GET['tgl_awal']){
 $_GET['tgl_awal']  = $skr;
}
if(!$_GET['tgl_akhir']){
 $_GET['tgl_akhir']  = $skr;
}

     //cari shift
$sql = "select * from global.global_shift order by shift_id";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataShift = $dtaccess->FetchAll($rs);

if($_GET["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["id_dokter"]);

     //untuk mencari tanggal
     //if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]);
if($_GET["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
if($_GET["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));

if($_GET["reg_shift"]){
  $sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_GET["reg_shift"]);
}

  /*if($userId<>'b9ead727d46bc226f23a7c1666c2d9fb' or $userId<>'92df81c2bebf2f93f75d9ad1014fe930'){
    $sql_where[] = " a.reg_who_update =".QuoteValue(DPE_CHAR,$userName);
  }*/

  if($_GET["cust_usr_nama"]){
    $sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_GET["cust_usr_nama"])."%'";
  }

  if($_GET["cust_usr_kode"]){
    $sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_kode"]);
  }

  if($_GET["cust_usr_alamat"]){
    $sql_where[] = " b.cust_usr_alamat = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_alamat"]);
  }

  if($_GET["reg_jenis_pasien"]){
    $sql_where[] = " a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["reg_jenis_pasien"]);
  }

  if($_GET["reg_tipe_layanan"]){
    $sql_where[] = " a.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_GET["reg_tipe_layanan"]);
  }

  if($_GET["id_perusahaan"]){
    $sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_GET["id_perusahaan"]);
  }

  if($_GET["cust_usr_jkn"]){
    $sql_where[] = " b.reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_jkn"]);
  }

  if($_GET["id_jamkesda_kota"]){
    $sql_where[] = " a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_GET["id_jamkesda_kota"]);
  }

  if($_GET["reg_status_pasien"]){
    $sql_where[] = " a.reg_status_pasien = ".QuoteValue(DPE_CHAR,$_GET["reg_status_pasien"]);
  }

  if($_GET["kondisi_akhir"]){
    $sql_where[] = " a.reg_status_kondisi = ".QuoteValue(DPE_CHAR,$_GET["kondisi_akhir"]);
  }

  if($_GET["id_lokasi_kota"]){
   $sql = "select * from global.global_lokasi where lokasi_id = ".QuoteValue(DPE_NUMERIC,$_GET["id_lokasi_kota"]);
   $rs = $dtaccess->Execute($sql);
   $datakotacari = $dtaccess->Fetch($rs);

   $sql_where[] = " ( b.id_prop = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_propinsi"])."
   and b.id_kota = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_kabupatenkota"]).") ";
 }
 if($_GET["reg_tipe_rawat"]){
  $sql_where[] = " a.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_GET["reg_tipe_rawat"]);
}

    //Pilih Poli
if($_GET["id_poli"]) 
{
 $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
}

$host="192.168.100.2";
$user="its";
$pass="itsthok";
$db="kmrs";
$port="5432";
$con=pg_connect("host=".$host."  port=".$port." dbname=".$db." user=".$user." password=".$pass) or die("Koneksi gagal");


$sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
a.reg_batal,d.usr_name as dokter,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran,
a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd,l.kondisi_akhir_pasien_nama, a.reg_id
from klinik.klinik_registrasi a 
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
left join global.global_auth_poli c on c.poli_id = a.id_poli
left join global.global_auth_user d on a.id_dokter = d.usr_id
left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
left join global.global_departemen f on a.id_dep = f.dep_id
left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
left join global.global_kondisi_akhir_pasien l on l.kondisi_akhir_pasien_id=a.reg_status_kondisi";
$sql.= " where a.reg_tipe_rawat='J' and ".implode(" and ",$sql_where);
$sql.= " and  cust_usr_kode<>'500' and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') and id_pembayaran is not null ";
$sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
$rs = $dtaccess->Execute($sql,DB_SCHEMA);
$dataTable = $dtaccess->FetchAll($rs);
     // echo $sql;

for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
  $tanggal=$dataTable[$i]["reg_tanggal"];
  $explode=explode("-",$tanggal); 
  $th=$explode[0];
  $bln=$explode[1];
  $hr=$explode[2];

  if ($dataTable[$i]["id_poli"]=="c2b63ccfdc414dcd2c2d9a1c5f69db9a") {
    $anak=$dataTable[$i]["dokter"];
  }
  elseif ($dataTable[$i]["id_poli"]=="c96a0c5914b37954352542aae75e4709") {
    // code...
    $obgyn=$dataTable[$i]["dokter"];
  }
  elseif ($dataTable[$i]["id_poli"]=="a8460eb351235d2423d6404323285f11") {
    // code...
    $dalam=$dataTable[$i]["dokter"];
  }

  $nama= $dataTable[$i]["cust_usr_nama"];
  $rm=$dataTable[$i]["cust_usr_kode"];
  $tl=$dataTable[$i]["cust_usr_tanggal_lahir"];
  $poli=$dataTable[$i]["poli_nama"];




  $sql = "INSERT INTO komite_mutu.dbpx_poli(id,tanggal,bulan,tahun,no_rm,nama,tanggal_lahir,poli,dokter_spesialis_anak,dokter_spesialis_obgyn,dokter_spesialis_penyakit_dalam,waktu_tunggu,who_kirim)
  VALUES ('$i','$hr','$bln','$th','$rm','$nama','$tl','$poli','$anak','$obgyn','$dalam','','$username')";
  $res = pg_query($con,$sql);
  if ($res) {
    // code...

    echo "<script>alert('Kirim Data Sukses');</script>";
  }
  echo "<script>window.close()</script>";  

}

 //membuat koneksi dengan database
 // $con = mysqli_connect(HOST,USER,PASS,DB) or die('Unable to Connect');







 // $con = pg_connect("host=192.168.1.2 port=5432 dbname=muslimat");

 // $con=pg_connect("host=192.168.1.2 port=5432 dbname=muslimat user=db_reg_online");

  // $dbTable = "klinik.klinik_durante_operasi";

  // if ($type == "add") {
  //       $dbField[0] = "id";   // PK
  //       $dbField[1] = "data";
  //       $dbField[2] = "tab";
  //       $dbField[3] = "dibuat";
  //       $dbField[4] = "dibuat_oleh";
  //       $dbField[5] = "diperbarui";
  //       $dbField[6] = "diperbarui_oleh";
  //       $dbField[7] = "id_reg";

  //       $dbValue[0] = QuoteValue(DPE_CHAR, $id);
  //       $dbValue[1] = QuoteValue(DPE_CHAR, $form);
  //       $dbValue[2] = QuoteValue(DPE_CHAR, $tab);
  //       $dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
  //       $dbValue[4] = QuoteValue(DPE_DATE, $userId);
  //       $dbValue[5] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
  //       $dbValue[6] = QuoteValue(DPE_DATE, $userId);
  //       $dbValue[7] = QuoteValue(DPE_DATE, $id_reg);
  //     } else {
  //       $dbField[0] = "id";   // PK
  //       $dbField[1] = "data";
  //       $dbField[2] = "diperbarui";
  //       $dbField[3] = "diperbarui_oleh";

  //       $dbValue[0] = QuoteValue(DPE_CHAR, $id);
  //       $dbValue[1] = QuoteValue(DPE_CHAR, $form);
  //       $dbValue[2] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
  //       $dbValue[3] = QuoteValue(DPE_DATE, $userId);
  //     }

  //   //print_r($dbValue); die();
  //   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  //   $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

  //   if ($type == "add") {
  //     $res = $dtmodel->Insert() or die("insert  error");
  //   } else {
  //     $res = $dtmodel->Update() or die("Update  error");
  //   }

  //   if ($res) {
  //     echo 'ok';
  //   }

  //   unset($dtmodel);
  //   unset($dbField);
  //   unset($dbValue);
  //   unset($dbKey);


