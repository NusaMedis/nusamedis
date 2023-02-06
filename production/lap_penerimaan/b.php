<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$userId = $auth->GetUserId();
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$thisPage = "report_setoran_cicilan.php";
$printPage = "report_setoran_cicilan_cetak.php?";

//  if (!$_GET["klinik"]) $_GET["klinik"]=$depId;
//$_GET["klinik"] = $_GET["klinik"]; 

if ($_GET["klinik"]) {
  $_GET["klinik"] = $_GET["klinik"];
} else if (!$_GET["klinik"]) {
  $_GET["klinik"] = $depId;
}


// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_GET["klinik"]);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_GET["dep_id"] = $konfigurasi["dep_id"];
$_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];




$skr = date("d-m-Y");
$time = date("H:i:s");

if (!$_GET['tgl_awal']) {
  $_GET['tgl_awal']  = $skr;
}
if (!$_GET['tgl_akhir']) {
  $_GET['tgl_akhir']  = $skr;
}

// if (!$_GET["pembayaran_det_flag"]) $_GET["pembayaran_det_flag"] = 'T';

//cari shift
$sql = "select * from global.global_shift order by shift_id";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataShift = $dtaccess->FetchAll($rs);



$sql_where[] = "1 = 1";
  
  $sql = "select who_when_update from klinik.klinik_pembayaran_det where pembayaran_det_tgl >=  ".QuoteValue(DPE_CHAR,date_db($_GET['tgl_awal']))." and pembayaran_det_tgl <= ".QuoteValue(DPE_CHAR,date_db($_GET['tgl_akhir']));
  if ($_GET['usr_id'] != '--') $sql .= " and who_when_update = ".QuoteValue(DPE_CHAR,$_GET['usr_id']);
  $sql .= " and who_when_update in(select usr_name from global.global_auth_user where (id_rol='4' or id_rol='1'  or id_rol = '35'))";
  $sql .= " group by who_when_update order by who_when_update asc";
  $Kasir = $dtaccess->FetchAll($sql);


//  function buat get nama pasien luar yang radiologi
function get_nama_pasien_luar($id_reg)
{
  $dtaccess = new DataAccess();
  $sql = "select * from klinik.klinik_registrasi where reg_id='" . $id_reg . "'";
  $data = $dtaccess->FetchAll($sql);
  
  echo str_replace("*", "'", strtoupper($data[0]['reg_keterangan']));
}


//  function buat get nama pasien luar yang Penjualan Apotik
function get_nama_pasien_luar_apotik($id_reg)
{
  $dtaccess = new DataAccess();
  $sql = "select * from apotik.apotik_penjualan where id_reg='" . $id_reg . "'";
  $data = $dtaccess->FetchAll($sql);
  echo str_replace("*", "'", $data[0]['cust_usr_nama']);
}

if ($_GET["tgl_awal"]) $sql_where2[] = "pembayaran_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal']);
if ($_GET["tgl_akhir"]) $sql_where2[] = "pembayaran_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir']);
if ($_GET["usr_id"]  <> "--") $sql_where2[] = "p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
// echo $_POST["usr_id"];
// exit();
$sql_where2 = implode(" and ", $sql_where2);
$sql = "select penjualan_nomor, pembayaran_create, cust_usr_nama, pembayaran_id, pembayaran_who_create
    from kasir.kasir_penjualan a 
    left join global.global_auth_user e on e.usr_id = a.who_update
    left join global.global_departemen f on f.dep_id = a.id_dep
    left join kasir.kasir_data_pembeli h on a.id_reg = h.reg_id
    left join kasir.kasir_pembayaran p on p.id_reg=h.reg_id
    left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id";
$sql .= " where p.pembayaran_yg_dibayar!='0' and " . $sql_where2;
$sql .= " group by penjualan_nomor, pembayaran_create, cust_usr_nama, pembayaran_id, pembayaran_who_create";
$sql .= " order by pembayaran_create desc";
   // echo $sql;
$rs = $dtaccess->Execute($sql);
$dataPenjualan = $dtaccess->FetchAll($rs);

$sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
        left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
        left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
        left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
        where q.id_jbayar = '01' and ". $sql_where2;
$dataPenjualanTunai = $dtaccess->Fetch($sql);

$sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
        left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
        left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
        left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
        where q.id_jbayar != '01' and q.id_jbayar <> 'Disc' and ". $sql_where2;
$dataPenjualanNonTunai = $dtaccess->Fetch($sql);

$sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
        left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
        left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
        left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
        where q.id_jbayar = 'Disc' and ". $sql_where2;
$dataPenjualanDisc = $dtaccess->Fetch($sql);

//Piutang
$sql = "select * from ar_ap.ar_payment a
        left join ar_ap.ar_trans b on b.ar_trans_id = a.id_ar_trans
        left join global.global_customer_user c on c.cust_usr_id = b.id_cust_usr
        where id_jbayar = '01' and ar_payment_when_update >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal']);
$sql .= " and ar_payment_when_update <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir']);
if ($_GET['usr_id'] <> '--') $sql .= " and ar_payment_who_update = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
$sql .= " order by ar_payment_when_update asc";
$dataPelunasanPiutang = $dtaccess->FetchAll($sql);
$tableHeader = "Report Pembayaran";

if ($_GET["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=report_pembayaran.xls');
}

if ($_GET["btnCetak"]) {

  $_x_mode = "cetak";
}

//ambil jenis pasien
$sql = "select * from global.global_jenis_pasien where jenis_id=" . QuoteValue(DPE_NUMERIC, $_GET["reg_jenis_pasien"]);
$rs = $dtaccess->Execute($sql);
$jenisPasien = $dtaccess->Fetch($rs);

//Data Klinik
$sql = "select * from global.global_departemen where dep_id like '" . $_GET["klinik"] . "%' order by dep_id";
$rs = $dtaccess->Execute($sql);
$dataKlinik = $dtaccess->FetchAll($rs);

//echo $sql;
$sql = "select dep_nama from global.global_departemen where dep_id = '" . $_GET["klinik"] . "'";
$rs = $dtaccess->Execute($sql);
$namaKlinik = $dtaccess->Fetch($rs);
$klinikHeader = "Klinik : " . $namaKlinik["dep_nama"];

// cari tipe layanan
$sql = "select * from global.global_tipe_biaya where tipe_biaya_id = '" . $_GET["layanan"] . "'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$tipeBiayaId = $dtaccess->Fetch($rs);

//cari shift by id
$sql = "select * from global.global_shift where shift_id = '" . $_GET["shift"] . "'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataShiftId = $dtaccess->Fetch($rs);

//cari shift by id
$sql = "select poli_nama from global.global_auth_poli where poli_id = '" . $_GET["id_poli"] . "'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataPoli = $dtaccess->Fetch($rs);

//cari nama petugas by id
$sql = "select * from global.global_auth_user where usr_id = '" . $_GET["kasir"] . "'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasirId = $dtaccess->Fetch($rs);

//cari nama petugas 
$sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1'  or id_rol = '35')";
if ($_GET['kasir'] != '--') $sql .= "and usr_name = ".QuoteValue(DPE_CHAR,$_GET['kasir']);
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir = $dtaccess->FetchAll($rs);

// cari nama kasir --



$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_GET["klinik"]);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$lokasi = $ROOT . "/gambar/img_cfg";
if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

if ($konfigurasi["dep_logo"] != "n") {
  $fotoName = $lokasi . "/" . $konfigurasi["dep_logo"];
} elseif ($konfigurasi["dep_logo"] == "n") {
  $fotoName = $lokasi . "/default.jpg";
} else {
  $fotoName = $lokasi . "/default.jpg";
}

if ($_GET['layanan'] == 'A') {
  $header = "RAWAT JALAN";
}elseif ($_GET['layanan'] == 'G') {
  $header = "IGD";
}elseif ($_GET['layanan'] == 'I') {
  $header = "RAWAT INAP";
}
?>
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">
<style>
  @media print {
    break {
      page-break-after: always;
    }
  }
</style>
<script language="javascript" type="text/javascript">
  window.print();
</script>


<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName; ?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul">
      <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"] ?></strong><br></span>
      <span class="judul3">
        <?php echo $konfigurasi["dep_kop_surat_1"] ?></span><br>
      <span class="judul4">
        <?php echo $konfigurasi["dep_kop_surat_2"] ?></span>
    </td>
  </tr>
</table>
<br>
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">
  <tr>
    <td width="100%" style="text-align:center;font-size:16px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PENERIMAAN <?php echo $header ?></td>
  </tr>
  <?php if ($_GET["id_poli"] <> '--') { ?>
    <tr>
      <td width="100%" style="text-align:center;font-size:10px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik/Penunjang : <?php echo $dataPoli["poli_nama"]; ?> </td>
    </tr>
  <? } ?>
  <tr>
    <td width="100%" style="text-align:center;font-size:10px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode Penerimaan : <?php echo $_GET["tgl_awal"]; ?> s/d <?php echo $_GET["tgl_akhir"]; ?></td>
  </tr>

</table>
<br>
<br>
<?php 
  for ($i = 0; $i < count($Kasir); $i++) {
        $sub_netto = 0;
        $sub_tunai = 0;
        $sub_bank = 0;
        $sub_bpjs = 0;
        $sub_asuransi = 0;
        $sub_karyawan = 0;
        $sub_diskon = 0;
        $sub_kurang = 0;
  $where = array();
if ($_GET["klinik"] && $_GET["klinik"] != "--") $where[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $_GET["klinik"]);
if ($_GET["tgl_awal"]) $where[] = "j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal']);
if ($_GET["tgl_akhir"]) $where[] = "j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir']);
if ($_GET["js_biaya"]) $where[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_GET["js_biaya"]);
if ($_GET['jbayar'] == '2') {
  $sql_where[] = "j.id_jbayar in(select jbayar_id from global.global_jenis_bayar)";
}elseif ($_GET['jbayar'] == '5') {
  $sql_where[] = "j.id_jbayar = 'BPJS'";
}elseif ($_GET['jbayar'] == '7') {
  $sql_where[] = "j.id_jbayar = 'Karyawan'";
}elseif ($_GET['jbayar'] == '20') {
  $sql_where[] = "j.id_jbayar in(select perusahaan_id from global.global_perusahaan)";  
}
if ($_GET["dokter"]) $where[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_GET["dokter"]);
if ($_GET["id_poli"] <> '--') $where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);

if ($_GET["shift"]) {


  $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_GET["shift"]);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $dataShiftPost = $dtaccess->Fetch($rs);

  $where[] = " j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]) . " " . $dataShiftPost["shift_jam_awal"]) . " and j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]) . " " . $dataShiftPost["shift_jam_akhir"]);
}


if ($_GET["reg_tipe_layanan"]) {
  $where[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_GET["layanan"]);
}

if ($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"] != "0") {
  $where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_jenis"]);
}

if ($_GET["perusahaan"]) {
  $where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_GET["perusahaan"]);
}

if ($_GET["id_poli"] <> '--') {
  $where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
}

if ($_GET["kasir"] <> "--") {
  $where[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_GET["kasir"]);
}

// if ($_GET["pembayaran_det_flag"] <> "--") {
//   $where[] = "j.pembayaran_det_flag = " . QuoteValue(DPE_CHAR, $_GET["pembayaran_det_flag"]);
// }

if ($_GET["layanan"] <> "--") {
  if ($_GET["layanan"] == "A") {
    $where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status='F0' or d.reg_status='A0')";
  } elseif ($_GET["layanan"] == "I") {
    $where[] = "d.reg_status like 'I%'";
  } else {
    $where[] = "d.reg_status like 'G%'";
  }
}
  $dtaccess = new DataAccess();
  $where = implode(" and ", $where);
  $sql = "select a.*, j.*, cust_usr_kode, cust_usr_nama, tipe_biaya_nama, 
          usr_name, f.poli_nama, shift_nama, jenis_nama, jbayar_nama,d.reg_tanggal,d.reg_waktu, reg_tanggal_pulang,l.poli_nama as poli_asal from klinik.klinik_pembayaran_det j 
          left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
          left join klinik.klinik_registrasi d on d.reg_id = j.id_reg
          left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
          left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
          left join global.global_auth_poli f on f.poli_id = d.id_poli
          left join global.global_shift g on g.shift_id = d.reg_shift
          left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
          left join global.global_auth_user i on i.usr_id = d.id_dokter
          left join global.global_jenis_bayar k on k.jbayar_id=j.id_jbayar
          left join global.global_auth_poli l on l.poli_id = d.id_poli_asal";
  $sql .= " where j.who_when_update='" . $Kasir[$i]['who_when_update'] . "' and  " . $where;
  //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
  $sql .= "order by d.id_poli,j.pembayaran_det_kwitansi, j.pembayaran_det_create, a.pembayaran_id asc";
  // echo $sql;s
  $dataTable = $dtaccess->FetchAll($sql);
?>
  <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px; font-size: 8px;">

    <thead align="center">
      <tr>
        <th rowspan="2" width="1%">No.</th>
        <th rowspan="2" width="7%">Tanggal Pulang</th>
        <th rowspan="2" width="7%">Tanggal Posting</th>
        <th rowspan="2" width="7%">No. Bukti</th>
        <th rowspan="2" width="5%">Medrec</th>
        <th rowspan="2" width="10%">Nama Pasien</th>
        <th rowspan="2" width="5%">Tag</th>
        <th rowspan="2" width="5%">Poli Asal</th>
        <th colspan="2" width="10%" style="text-align: center;">Tunai/Bank</th>
        <th rowspan="2" width="5%" style="text-align: center;">BPJS</th>
        <th rowspan="2" width="5%" style="text-align: center;">Asuransi</th>
        <th rowspan="2" width="5%" style="text-align: center;">Karyawan</th>
        <th rowspan="2" width="5%" style="text-align: center;">Kurang Bayar</th>
        <th rowspan="2" width="5%" style="text-align: center;">Diskon</th>
        <th rowspan="2" width="5%" style="text-align: center;">Jumlah Netto</th>
      </tr>
      <tr>
        <th class="column-title" style="text-align: center;">Tunai</th>
        <th class="column-title" style="text-align: center;">Bank</th>
        <!-- <th class="column-title">Jumlah</th>
        <th class="column-title">Charge</th> -->
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 0;
      $nxx = 0;
      for ($x = 0; $x < count($dataTable); $x++){
        // $sub_tunai = 0;
        // $sub_bank = 0;
        // $sub_bpjs = 0;
        // $sub_asuransi = 0;
        // $sub_karyawan = 0;
        // $sub_diskon = 0;
        // $sub_kurang = 0;
        // $sub_netto = 0;
        $no++;
        $id_pem[$x] = $dataTable[$x]['pembayaran_id'];
        $pembayaran_det_kwitansi[$x] = $dataTable[$x]['pembayaran_det_kwitansi'];
        $sql = "select sum(pembayaran_det_dibayar) as tunai from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and id_jbayar = '01'"." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi']);
        $TotalTunai = $dtaccess->Fetch($sql);
        // echo $sql;

        $sql = "select sum(pembayaran_det_dibayar) as bank, jbayar_nama from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and jbayar_id <> '01'"." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi'])." group by jbayar_nama";
        $TotalBank = $dtaccess->Fetch($sql);

        $sql = "select sum(pembayaran_det_dibayar) as bpjs from klinik.klinik_pembayaran_det where id_jbayar = 'BPJS' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi']);
        $TotalBPJS = $dtaccess->Fetch($sql);

        $sql = "select sum(pembayaran_det_dibayar) as asuransi from klinik.klinik_pembayaran_det where id_jbayar in(select perusahaan_id from global.global_perusahaan) and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi']);
        $TotalAsuransi = $dtaccess->Fetch($sql);

        $sql = "select sum(pembayaran_det_dibayar) as karyawan from klinik.klinik_pembayaran_det where id_jbayar = 'Karyawan' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi']);
        $TotalKaryawan = $dtaccess->Fetch($sql);

        $sql = "select sum(pembayaran_det_dibayar) as kurang from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi'])." and pembayaran_det_flag = 'P' and pembayaran_det_tipe_piutang = 'P'";
        $TotalKurangBayar = $dtaccess->Fetch($sql);

        $sql = "select sum(pembayaran_det_dibayar) as diskon from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dataTable[$x]['pembayaran_det_kwitansi'])." and id_jbayar = 'Diskon'";
        $TotalDiskon = $dtaccess->Fetch($sql);
      ?>
      <?php 
        if ($pembayaran_det_kwitansi[$x-1] != $pembayaran_det_kwitansi[$x]): 
          $nxx++;
      ?>
        
        <tr>
          <td width="1%"><?= $nxx; ?></td>
          <?php
          $daytime = explode(".", $dataTable[$x]["pembayaran_det_create"]);
          $time = explode(" ", $daytime[0]);
          $Netto = $TotalTunai['tunai'] + $TotalBank['bank'] + $TotalBPJS['bpjs'] + $TotalAsuransi['asuransi'] + $TotalKaryawan['karyawan'] + $TotalKurangBayar['kurang'] + $TotalDiskon['diskon'];
          ?>
          <td width="7%"><?= date_db($dataTable[$x]['reg_tanggal_pulang']) ?></td>
          <td width="7%"><?= format_date($time[0]) . "&nbsp;" . $time[1] ?></td>
          <td width="7%"><?= $dataTable[$x]['pembayaran_det_kwitansi'] ?></td>
          <td width="5%"><?= $dataTable[$x]['cust_usr_kode'] ?></td>
          <td width="10%"><?php if ($dataTable[$x]['cust_usr_kode'] == '500' || $dataTable[$x]["cust_usr_kode"] == '100') {
                get_nama_pasien_luar($dataTable[$x]['id_reg']);
                // get_nama_pasien_luar_apotik($dataTable[$x]['id_reg']);
              } else {
                echo str_replace("*", "'", $dataTable[$x]['cust_usr_nama']);
              }
              ?></td>
          <td width="5%"><?= $dataTable[$x]["poli_nama"] ?></td>
          <td width="5%">
            <?php  
              if ($dataTable[$x]['poli_asal'] == '') {
                echo $dataTable[$x]['poli_nama'];
              }else{
                echo $dataTable[$x]['poli_asal'];
              }
            ?>
          </td>
          <?php
            $sub_tunai += $TotalTunai['tunai'];
            $sub_bank += $TotalBank['bank'];
            $sub_bpjs += $TotalBPJS['bpjs'];
            $sub_asuransi += $TotalAsuransi['asuransi'];
            $sub_karyawan += $TotalKaryawan['karyawan'];
            $sub_kurang += $TotalKurangBayar['kurang'];
            $sub_diskon += $TotalDiskon['diskon'];
            $sub_netto += $Netto;
          ?>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalTunai['tunai'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', $TotalBank['jbayar_nama']." ".currency_format($TotalBank['bank'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalBPJS['bpjs'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalAsuransi['asuransi'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalKaryawan['karyawan'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalKurangBayar['kurang'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalDiskon['diskon'])) ?></td>
          <td align="right" width="5%"><?= str_replace(',', '.', currency_format($Netto)) ?></td>
          <?php $row++; ?>
        </tr>
      <?php endif ?>
      </tbody>
      <?php // }
      } 
      ?>
      <tfoot>
        <tr>
          <td colspan="8"><?= "Sub Total : " . $Kasir[$i]['who_when_update']; ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_tunai)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_bank)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_bpjs)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_asuransi)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_karyawan)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_kurang)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_diskon)) ?></td>
          <td align="right"><?= str_replace(',', '.', currency_format($sub_netto)) ?></td>
        </tr>
      </tfoot>
      <?php
        $totalTunaiGlobal += $sub_tunai;
        $totalBankGlobal += $sub_bank;
        $totalJknGlobal += $sub_bpjs;
        $totalPerusahaanGlobal += $sub_asuransi;
        $totalKaryawanGlobal += $sub_karyawan;
        $totalKurangBayarGlobal += $sub_kurang;
        $totalDiskonGlobal += $sub_diskon;
        $totalNettoGlobal += $sub_netto;
      ?>
  </table>
  <?php  }
  $totalPasien = $row;
// enfor data kasir

// History Deposit

  $sql = "select * from klinik.klinik_deposit_history a 
              left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
              left join global.global_jenis_bayar c on c.jbayar_id = a.id_jbayar
              where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal']).' '.$_GET['waktu_awal']) . " 
              and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir']).' '.$_GET['waktu_akhir']) . "
              and deposit_history_nominal <> '0'";
  if ($_GET["usr_id"] <> "--") {
    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
  }
  if ($_GET["cust_usr_kode"]<>'') {
    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
  }
  if ($_GET['layanan'] == 'I') {
    $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
  }elseif ($_GET['layanan'] == 'DP') {
    $sql .= " and (deposit_history_flag = 'M')";
  }
  $sql .= "order by deposit_history_when_create asc";
  // echo $sql;
  $dataDeposit = $dtaccess->FetchAll($sql);

  $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag from klinik.klinik_deposit_history a 
              left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
              where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal']).' '.$_GET['waktu_awal']) . " 
              and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir']).' '.$_GET['waktu_akhir']) . "
              and deposit_history_nominal <> '0' and id_jbayar = '01'";
  if ($_GET["usr_id"] <> "--") {
    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
  }
  if ($_GET["cust_usr_kode"]<>'') {
    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
  }
  if ($_GET['layanan'] == 'I') {
    $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
  }elseif ($_GET['layanan'] == 'DP') {
    $sql .= " and (deposit_history_flag = 'M')";
  }
  $dataDepositTunai = $dtaccess->FetchAll($sql);

  for ($i = 0; $i < count($dataDepositTunai); $i++) {
    if ($dataDepositTunai[$i]['deposit_history_flag'] == 'R') {
      $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
      $ReturDeposit += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
    }elseif($dataDepositTunai[$i]['deposit_history_flag'] == 'P'){
      $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
      $PemakaianDeposit += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
    }elseif($dataDepositTunai[$i]['deposit_history_flag'] == 'M'){
      $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
      $DepositMasuk += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
    }
  }


  $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag from klinik.klinik_deposit_history a 
              left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
              where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal']).' '.$_GET['waktu_awal']) . " 
              and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir']).' '.$_GET['waktu_akhir']) . "
              and deposit_history_nominal <> '0' and id_jbayar <> '01'";
  if ($_GET["usr_id"] <> "--") {
    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
  }
  if ($_GET["cust_usr_kode"]<>'') {
    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
  }
  if ($_GET['layanan'] == 'I') {
    $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
  }elseif ($_GET['layanan'] == 'DP') {
    $sql .= " and (deposit_history_flag = 'M')";
  }
  $dataDepositNonTunai = $dtaccess->FetchAll($sql);
  
  for ($i = 0; $i < count($dataDepositNonTunai); $i++) {
    if ($dataDepositNonTunai[$i]['deposit_history_flag'] == 'R') {
      $totalDepositNonTunai += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
      $ReturDepositNon += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
    }elseif($dataDepositNonTunai[$i]['deposit_history_flag'] == 'P'){
      $totalDepositNonTunai += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
      $PemakaianDepositNon += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
    }elseif($dataDepositNonTunai[$i]['deposit_history_flag'] == 'M'){
      $totalDepositNonTunai += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
      $DepositMasukNon += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
    }
  }
  // echo $totalDepositNonTunai;

  ?>
    <?php if (count($dataDeposit) > 0) : ?>
      <?php if ($_GET['layanan'] == 'DP' || $_GET['layanan'] == '--' || $_GET['layanan'] == 'I') { ?>
        <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px; font-size: 8px;">
          <thead>
            <tr>
              <th colspan="7"><b>Daftar Deposit</b></th>
            </tr>
            <tr>
              <th style="text-align: center;">No</th>
              <th style="text-align: center;">Tanggal Transaksi</th>
              <th style="text-align: center;">No Bukti</th>
              <th style="text-align: center;">No Medrec</th>
              <th style="text-align: center;">Nama Pasien</th>
              <th style="text-align: center;">Nominal</th>
              <th style="text-align: center;">Jenis Bayar</th>
              <th style="text-align: center;">Petugas Entry</th>
              <th style="text-align: center;">Tipe</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              for ($i = 0; $i < count($dataDeposit); $i++) {
                $sql = "select * from klinik.klinik_deposit_history where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDeposit[$i]['id_pembayaran'])." and deposit_history_flag = 'M'";
                $TglDeposit = $dtaccess->Fetch($sql);

                if ($dataDeposit[$i]['deposit_history_flag'] == 'P') {
                  $tgl = date_db($TglDeposit['deposit_history_tgl']);
                }else{
                  $tgl = date_db($dataDeposit[$i]['deposit_history_tgl']);
                }

                if ($dataDeposit[$i]['deposit_history_flag'] == 'P') {
                   $dataDeposit[$i]['deposit_history_nominal'] = str_replace('-', '', $dataDeposit[$i]['deposit_history_nominal']);
                 } 
            ?>
              <tr>
                <td align="center"><?php echo $i + 1 ?></td>
                <td align="center"><?php echo $tgl; ?></td>
                <td align="center"><?php echo $dataDeposit[$i]['deposit_history_no_bukti'] ?></td>
                <td align="center"><?php echo $dataDeposit[$i]['cust_usr_kode'] ?></td>
                <td><?php echo $dataDeposit[$i]['cust_usr_nama'] ?></td>
                <td align="right">
                  <?php if ($dataDeposit[$i]['deposit_history_nominal'] > 0) {

                    echo ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($dataDeposit[$i]['deposit_history_nominal'])) : currency_format($dataDeposit[$i]['deposit_history_nominal']);
                    $SumDepositDebet += $dataDeposit[$i]['deposit_history_nominal'];
                  } else {
                    echo "(" . str_replace("-", "", currency_format($dataDeposit[$i]['deposit_history_nominal'])) . ")";
                    $SumDepositKredit += $dataDeposit[$i]['deposit_history_nominal'];
                  } ?>

                </td>
                <td align="center"><?php echo $dataDeposit[$i]['jbayar_nama'] ?></td>
                <td align="center"><?php echo $dataDeposit[$i]['deposit_history_who_create'] ?></td>
                <td align="center">
                  <?php 
                    if ($dataDeposit[$i]['deposit_history_flag'] == 'M') { 
                      echo "Masuk" ;
                    } elseif ($dataDeposit[$i]['deposit_history_flag'] == 'P') {
                      echo "Pelunasan";
                    } elseif ($dataDeposit[$i]['deposit_history_flag'] == 'R') {
                      echo "Retur";
                    }
                  ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" align="left"><b>TOTAL</b></td>
              <td align="right"><b><?php $totalHistoryDeposit = $SumDepositDebet - str_replace("-", "", $SumDepositKredit);
                                    echo ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($totalHistoryDeposit)) : currency_format($totalHistoryDeposit) ?></b></td>
            </tr>
          </tfoot>
        </table>
      <?php } ?>
    <?php endif; ?>
  <break></break>
  <?php if (count($dataPenjualan) > 0) : ?>
    <?php if (($_GET['layanan'] == 'Lain' || $_GET['layanan'] == '--') && $_GET['cust_usr_jenis'] != '7' && $_GET['cust_usr_kode'] == '' && $_GET['id_poli'] == '--') { ?>
      <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px; font-size: 8px;">
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No Nota</th>
            <th>Nama Pasien</th>
            <th>Nama Kasir</th>
            <th>Tunai</th>
            <th>Bank</th>
            <th>Diskon</th>
            <th>Total Netto</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            for ($i = 0; $i < count($dataPenjualan); $i++) { 

              $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPenjualan[$i]['pembayaran_id'])." and id_jbayar = '01'";
              $PembayaranTunai = $dtaccess->Fetch($sql);

              $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPenjualan[$i]['pembayaran_id'])." and id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01')";
              $PembayaranBank = $dtaccess->Fetch($sql);

              $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPenjualan[$i]['pembayaran_id'])." and id_jbayar = 'Disc'";
              $PembayaranDisc = $dtaccess->Fetch($sql);

              $Netto = $PembayaranTunai['total'] + $PembayaranBank['total'] + $PembayaranDisc['total'];
          ?>
            <tr>
              <td><?php echo $i+1 ?></td>
              <td><?php echo $dataPenjualan[$i]['pembayaran_create'] ?></td>
              <td><?php echo $dataPenjualan[$i]['penjualan_nomor'] ?></td>
              <td><?php echo $dataPenjualan[$i]['cust_usr_nama'] ?></td>
              <td><?php echo $dataPenjualan[$i]['pembayaran_who_create'] ?></td>
              <td align="right"><?php echo currency_format($PembayaranTunai['total']) ?></td>
              <td align="right"><?php echo currency_format($PembayaranBank['total']) ?></td>
              <td align="right"><?php echo currency_format($PembayaranDisc['total']) ?></td>
              <td align="right"><?php echo currency_format($Netto) ?></td>
            </tr>
          <?php 
              $SubTunai += $PembayaranTunai['total'];
              $SubBank += $PembayaranBank['total'];
              $SubDisc += $PembayaranDisc['total'];
              $SubNetto += $Netto;
              $TotalJumlahPenjualan++;
            } 
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5">TOTAL</td>
            <td align="right"><?php echo currency_format($SubTunai) ?></td>
            <td align="right"><?php echo currency_format($SubBank) ?></td>
            <td align="right"><?php echo currency_format($SubDisc) ?></td>
            <td align="right"><?php echo currency_format($SubNetto) ?></td>
          </tr>
        </tfoot>
      </table>
    <?php } ?>
  <?php endif; ?>
<?php 
// echo $_GET['layanan'];
  $FixDepositMasuk = $DepositMasuk + $DepositMasukNon;
  $FixTotalDepositPosting = $PemakaianDeposit + $PemakaianDepositNon + $ReturDeposit;
  if ($_GET['layanan'] == '--') {  
    $FixTotalTunaiGlobal = $totalTunaiGlobal + $dataPenjualanTunai['total_pembayaran'] + $FixDepositMasuk;
    // echo $totalTunaiGlobal.' + '.$dataPenjualanTunai['total_pembayaran'].' + '.$FixTotalDepositPosting.' + '.$FixDepositMasuk;
    $FixTotalBankGlobal = $totalBankGlobal + $dataPenjualanNonTunai['total_pembayaran'] + $totalDepositNonTunai;
    $JumlahBaris = $totalPasien + $TotalJumlahPenjualan;
    $FixReturDeposit = $ReturDeposit;
  } elseif ($_GET['layanan']== 'I' ){ 
    $dataPenjualanTunai['total_pembayaran'] = 0;
    $dataPenjualanNonTunai['total_pembayaran'] = 0;
    $FixTotalTunaiGlobal = $totalTunaiGlobal;
    $FixTotalBankGlobal = $totalBankGlobal;
    $FixReturDeposit = $ReturDeposit;
    $JumlahBaris = $totalPasien;
  } elseif ($_GET['layanan']== 'A' || $_GET['layanan'] == 'G') { 
    $dataPenjualanTunai['total_pembayaran'] = 0;
    $dataPenjualanNonTunai['total_pembayaran'] = 0;
    $FixTotalDepositPosting = 0;
    $FixDepositMasuk = 0;
    $FixReturDeposit = 0;
    $FixTotalTunaiGlobal = $totalTunaiGlobal;
    $FixTotalBankGlobal = $totalBankGlobal;
    $JumlahBaris = $totalPasien;
  }elseif ($_GET['layanan']== 'Disc') {
    $FixTotalTunaiGlobal = $totalDiskon;
  } elseif ($_GET['layanan']== 'DP') {
    $FixTotalTunaiGlobal = $dataDepositTunai['total_deposit'];
    $FixTotalBankGlobal = $dataDepositNonTunai['total_deposit'];
  } elseif ($_GET['layanan']== 'Lain') {
    $FixTotalTunaiGlobal = $dataPenjualanTunai['total_pembayaran'];
    $FixTotalBankGlobal = $dataPenjualanNonTunai['total_pembayaran'];
    $JumlahBaris = $TotalJumlahPenjualan;
  } 
  // echo $FixTotalDepositPosting;
  $FixTotalBPJSGlobal = $totalJknGlobal;
  $FixTotalAsuransiGlobal = $totalPerusahaanGlobal;
  $FixTotalKaryawanGlobal = $totalKaryawanGlobal;
  $FixTotalKurangGlobal = $totalKurangBayarGlobal;
  $FixTotalDiskon = $totalDiskonGlobal + $dataPenjualanDisc['total_pembayaran'];
  $FixTotalNetto = $FixTotalTunaiGlobal + $FixTotalBankGlobal + $FixTotalBPJSGlobal + $FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal + $FixTotalKurangGlobal + $FixTotalDiskon + $FixTotalDepositPosting;
  // echo $FixTotalNetto .' = '. $FixTotalTunaiGlobal.' +xx '.$FixTotalBankGlobal.' + '.$FixTotalBPJSGlobal.' + '.$FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal.' + '.$FixTotalKurangGlobal.' + '.$FixTotalDiskon.' + '.$FixTotalDepositPosting;
  // echo $FixTotalTunaiGlobal.' + '.$FixTotalBankGlobal.' + '.$FixTotalBPJSGlobal.' + '.$FixTotalAsuransiGlobal.' + '.$FixTotalKaryawanGlobal.' + '.$FixTotalKurangGlobal.' + '.$FixTotalDiskon.' + '.$FixTotalDepositPosting;
?>
  <p>TOTAL GLOBAL</p>
  <table cellspacing="0" width="100%" class="table table-bordered" border="1" style="margin-top:15px; font-size: 8px;">
    <thead align="center">
      <tr>
        <td rowspan="2"></td>
        <td align="center" colspan="2">Tunai/Bank</td>
        <td align="center" rowspan="2">Retur Deposit</td>
        <td align="center" rowspan="2">BPJS</td>
        <td align="center" rowspan="2">Asuransi</td>
        <td align="center" rowspan="2">Karyawan</td>
        <td align="center" rowspan="2">Kurang Bayar</td>
        <td align="center" rowspan="2">Diskon</td>
        <td align="center" rowspan="2">Deposit Posting</td>
        <td align="center" rowspan="2">Jumlah Netto Tanpa Retur</td>
      </tr>
      <tr>
        <td class="column-title">Tunai</td>
        <td class="column-title">Bank</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td align="center" width="">TOTAL ( <?php echo $JumlahBaris; ?> )</td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalTunaiGlobal)) : currency_format($FixTotalTunaiGlobal); ?></td>
        <!-- <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBankGlobal)) : currency_format($FixTotalBankGlobal); ?></td> -->
        <?php 
          $where = array();
          if ($_GET["klinik"] && $_GET["klinik"] != "--") $where[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $_GET["klinik"]);
          if ($_GET["tgl_awal"]) $where[] = "date(j.pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
          if ($_GET["tgl_akhir"]) $where[] = "date(j.pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));
          if ($_GET["js_biaya"]) $where[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_GET["js_biaya"]);
          if ($_GET['jbayar'] == '2') {
            $sql_where[] = "j.id_jbayar in(select jbayar_id from global.global_jenis_bayar)";
          }elseif ($_GET['jbayar'] == '5') {
            $sql_where[] = "j.id_jbayar = 'BPJS'";
          }elseif ($_GET['jbayar'] == '7') {
            $sql_where[] = "j.id_jbayar = 'Karyawan'";
          }elseif ($_GET['jbayar'] == '20') {
            $sql_where[] = "j.id_jbayar in(select perusahaan_id from global.global_perusahaan)";  
          }
          if ($_GET["dokter"]) $where[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_GET["dokter"]);
          if ($_GET["id_poli"] <> '--') $where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);

          if ($_GET["shift"]) {


            $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_GET["shift"]);
            $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
            $dataShiftPost = $dtaccess->Fetch($rs);

            $where[] = " j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]) . " " . $dataShiftPost["shift_jam_awal"]) . " and j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]) . " " . $dataShiftPost["shift_jam_akhir"]);
          }


          if ($_GET["reg_tipe_layanan"]) {
            $where[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_GET["layanan"]);
          }

          if ($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"] != "0") {
            $where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_jenis"]);
          }

          if ($_GET["perusahaan"]) {
            $where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_GET["perusahaan"]);
          }

          if ($_GET["id_poli"] <> '--') {
            $where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
          }

          if ($_GET["kasir"] <> "--") {
            $where[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_GET["kasir"]);
          }

          // if ($_GET["pembayaran_det_flag"] <> "--") {
          //   $where[] = "j.pembayaran_det_flag = " . QuoteValue(DPE_CHAR, $_GET["pembayaran_det_flag"]);
          // }

          if ($_GET["layanan"] <> "--") {
            if ($_GET["layanan"] == "A") {
              $where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status='F0' or d.reg_status='A0')";
            } elseif ($_GET["layanan"] == "I") {
              $where[] = "d.reg_status like 'I%'";
            } else {
              $where[] = "d.reg_status like 'G%'";
            }
          }
          $where = implode(" and ", $where);
          $sql = "select sum(pembayaran_det_dibayar) as total, jbayar_nama
                  from klinik.klinik_pembayaran_det j 
                  left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
                  left join klinik.klinik_registrasi d on d.reg_id = j.id_reg
                  left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
                  left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
                  left join global.global_auth_poli f on f.poli_id = d.id_poli
                  left join global.global_shift g on g.shift_id = d.reg_shift
                  left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
                  left join global.global_auth_user i on i.usr_id = d.id_dokter
                  left join global.global_jenis_bayar k on k.jbayar_id=j.id_jbayar";
          $sql .= " where 1=1 and j.id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01') and  " . $where;
          //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
          $sql .= " group by jbayar_nama";
          // $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
          $dataBank = $dtaccess->FetchAll($sql);

          $sql = "select sum(pembayaran_det_total) as total_pembayaran, jbayar_nama from kasir.kasir_penjualan a
                  left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                  left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                  left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                  left join global.global_jenis_bayar c on c.jbayar_id = b.id_jbayar
                  where b.id_jbayar != '01' ";
          if ($_GET['tgl_awal']) $sql .= " and a.penjualan_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal']);
          if ($_GET['tgl_akhir']) $sql .= " and a.penjualan_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir']);
          if ($_GET["usr_id"]  <> "--") $sql .= " and p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
          $sql .= " group by jbayar_nama";
          $dataPenjualanBank = $dtaccess->FetchAll($sql);
          // echo $sql;
        ?>
        <td>
          <table border="0" style="font-size: 8px;">
            <?php for ($i = 0; $i < count($dataBank); $i++) { ?>
              <tr>
                <td><?php echo $dataBank[$i]['jbayar_nama'] ?></td>
                <td> : </td>
                <td align="right">
                  <?php echo str_replace(',', '.', currency_format($dataBank[$i]['total'])) ?>
                </td>
              </tr>
            <?php } ?>
            <?php for ($i = 0; $i < count($dataPenjualanBank); $i++) { ?>
              <tr>
                <td><?php echo $dataPenjualanBank[$i]['jbayar_nama'] ?></td>
                <td> : </td>
                <td align="right"><?php echo str_replace(',', '.', currency_format($dataPenjualanBank[$i]['total_pembayaran'])) ?></td>
              </tr>
            <?php } ?>
          </table>
        </td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixReturDeposit)) : currency_format($FixReturDeposit); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBPJSGlobal)) : currency_format($FixTotalBPJSGlobal); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalAsuransiGlobal)) : currency_format($FixTotalAsuransiGlobal); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalKaryawanGlobal)) : currency_format($FixTotalKaryawanGlobal); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalKurangGlobal)) : currency_format($FixTotalKurangGlobal); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalDiskon)) : currency_format($FixTotalDiskon); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalDepositPosting)) : currency_format($FixTotalDepositPosting); ?></td>
        <td align="right"><?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalNetto)) : currency_format($FixTotalNetto); ?></td>
      </tr>
    </tbody>
  </table>

  <?php if (count($dataPelunasanPiutang) > 0) { ?>
  <p>Pelunasan Piutang</p>
  <table cellspacing="0" width="100%" class="table table-bordered" border="1" style="margin-top:15px;">
    <thead align="center">
      <tr>
        <td>No</td>
        <td>Waktu</td>
        <td>Kwitansi</td>
        <td>Nama Pasien</td>
        <td>Pembayaran</td>
        <td>Petugas</td>
        <td>Tipe</td>
      </tr>
    </thead>
    <tbody>
      <?php 
        for ($i = 0; $i < count($dataPelunasanPiutang); $i++) { 
          if ($dataPelunasanPiutang[$i]['flag_piutang'] == 'P') $Flag = 'Perorangan';
          if ($dataPelunasanPiutang[$i]['flag_piutang'] == 'K') $Flag = 'Karyawan';
          if ($dataPelunasanPiutang[$i]['flag_piutang'] == 'A') $Flag = 'Asuransi';
          if ($dataPelunasanPiutang[$i]['flag_piutang'] == 'B') $Flag = 'BPJS';
      ?>
        <tr>
          <td><?php echo $i+1 ?></td>
          <td><?php echo $dataPelunasanPiutang[$i]['ar_payment_when_update'] ?></td>
          <td><?php echo $dataPelunasanPiutang[$i]['ar_payment_kode'] ?></td>
          <td><?php echo $dataPelunasanPiutang[$i]['cust_usr_nama'] ?></td>
          <td align="right"><?php echo currency_format($dataPelunasanPiutang[$i]['ar_payment_jumlah']) ?></td>
          <td><?php echo $dataPelunasanPiutang[$i]['ar_payment_who_update'] ?></td>
          <td><?php echo $Flag ?></td>
        </tr>
      <?php 
        $totalPembayaran += $dataPelunasanPiutang[$i]['ar_payment_jumlah'];
        } 
      ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4">TOTAL</td>
        <td align="right"><?php echo currency_format($totalPembayaran) ?></td>
        <td>&nbsp;</td>
      </tr>
    </tfoot>
  </table>
  <?php } ?>
