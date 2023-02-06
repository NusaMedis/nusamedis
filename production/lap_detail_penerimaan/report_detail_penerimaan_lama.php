<?php
// LIBRARY
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
$depId = $auth->GetDepId();
$thisPage = "report_setoran_cicilan.php";
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$userId = $auth->GetUserId();
$lokasi = $ROOT . "/gambar/img_cfg";

//if (!$_POST["klinik"]) $_POST["klinik"]=$depId;


if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}

if (!$_POST["klinik"]) $_POST["klinik"] = $depId;
else $_POST["klinik"] = $_POST["klinik"];

// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

$skr = date("d-m-Y");
$time = date("H:i:s");

if (!$_POST['tgl_awal']) {
  $_POST['tgl_awal']  = $skr;
}
if (!$_POST['tgl_akhir']) {
  $_POST['tgl_akhir']  = $skr;
}

//cari shift
$sql = "select * from global.global_shift order by shift_id";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataShift = $dtaccess->FetchAll($rs);

if (!$_POST["cust_usr_jenis"])  $_POST["cust_usr_jenis"] = "0";
if (!$_POST["pembayaran_det_flag"]) $_POST["pembayaran_det_flag"] = 'T';

$perusahaan = $_POST["ush_id"];
$kasir = $_POST["usr_id"];

//$sql_where[] = "reg_tanggal is not null"; 
if ($_POST["klinik"] && $_POST["klinik"] != "--") $sql_where[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $_POST["klinik"]);
if ($_POST["tgl_awal"]) $sql_where[] = "date(j.pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
if ($_POST["tgl_akhir"]) $sql_where[] = "date(j.pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));
if ($_POST["js_biaya"]) $sql_where[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_POST["js_biaya"]);
if ($_POST["jbayar"]) $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
if ($_POST["id_dokter"]) $sql_where[] = "m.id_dokter = " . QuoteValue(DPE_CHAR, $_POST["id_dokter"]);
if ($_POST["fol_nama"]) $sql_where[] = "upper(m.fol_nama) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_POST["fol_nama"]) . "%");

if ($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"] != "0") {
  $sql_where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_jenis"]);
}

if ($_POST["ush_id"]) {
  $sql_where[] = "d.id_perusahaan = " . QuoteValue(DPE_CHAR, $_POST["ush_id"]);
}

if ($_POST["id_poli"] != '--') {
  $sql_where[] = "m.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
}

if ($_POST["usr_id"] <> "--") {
  $sql_where[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
}

if ($_POST["pembayaran_det_flag"] <> "--") {
  $sql_where[] = "j.pembayaran_det_flag = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_det_flag"]);
}

if ($_POST["layanan"] <> "--") {
  if ($_POST["layanan"] == "A") {
    $sql_where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status='F0' or d.reg_status='A0')";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "d.reg_status like 'I%'";
  } else {
    $sql_where[] = "d.reg_status like 'G%'";
  }
}

if ($_POST['cust_usr_nama']) $sql_where[] = "c.cust_usr_nama like '%".strtoupper($_POST['cust_usr_nama'])."%'";
if ($_POST['cust_usr_kode']) $sql_where[] = "c.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_kode']);


/*if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
       $sql_where[] = "a.pembayaran_who_create = '".$userName."'";
     }*/

$sql_where = implode(" and ", $sql_where);
$sql = "select a.*, j.*, cust_usr_kode, cust_usr_nama, tipe_biaya_nama, 
            usr_name, poli_nama, shift_nama, jenis_nama, jbayar_nama, 
            m.fol_nama,m.fol_nominal,m.fol_jumlah,m.fol_nominal_satuan,m.fol_total_harga,m.id_dokter as dokter,
            m.who_when_update as entri_data,m.id_pembayaran_det,d.reg_keterangan  
            from klinik.klinik_pembayaran_det j
            left join klinik.klinik_folio m on m.id_pembayaran_det = j.pembayaran_det_id
            left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
            left join klinik.klinik_registrasi d on d.reg_id = j.id_reg and j.id_pembayaran = d.id_pembayaran
            left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
            left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
            left join global.global_auth_poli f on f.poli_id = m.id_poli
            left join global.global_shift g on g.shift_id = d.reg_shift
            left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
            left join global.global_auth_user i on i.usr_id = d.id_dokter
            left join global.global_jenis_bayar k on k.jbayar_id=j.id_jbayar";
$sql .= " where 1=1 and " . $sql_where;
//$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
$sql .= " order by j.pembayaran_det_kwitansi, j.pembayaran_det_create, a.pembayaran_id asc";
// echo $sql;
$dataTable = $dtaccess->FetchAll($sql);

for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
  if ($dataTable[$i]["pembayaran_det_id"] == $dataTable[$i - 1]["pembayaran_det_id"]) {
    $hitung[$dataTable[$i]["pembayaran_det_id"]] += 1;
  }
}


$counter = 0;
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Med Rec";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Biaya";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Total";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;
$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "18%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas Entri";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Total Biaya";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "18%";
$counterHeader++;
$tbHeader[0][$counterHeader][TABLE_ISI] = "Diskon";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "18%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Total Pembayaran";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
$counterHeader++;
/*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Subsidi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Iur Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hrs Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
     $counterHeader++;*/
$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Penerimaan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
$counterHeader++;

for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {
  //$totcicilan += $dataTable[$i]["cicilan_multipayment_total"];
  $sql = "select fol_keterangan from klinik.klinik_folio k
        left join klinik.klinik_pembayaran a on a.pembayaran_id = k.id_pembayaran and a.id_reg = k.id_reg
        where id_pembayaran = " . QuoteValue(DPE_CHAR, $dataTable[$i]["pembayaran_id"]);
  $rs = $dtaccess->Execute($sql);
  $dataFolket = $dtaccess->Fetch($rs);

  if ($dataTable[$i]["pembayaran_det_id"] != $dataTable[$i - 1]["pembayaran_det_id"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["pembayaran_det_id"]] + 1;

    $tbContent[$i][$counter][TABLE_ISI] = $m + 1;
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $m++;

    $daytime = explode(".", $dataTable[$i]["pembayaran_det_create"]);
    $time = explode(" ", $daytime[0]);
    $tbContent[$i][$counter][TABLE_ISI] = format_date($time[0]) . "&nbsp;" . $time[1];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $nama = explode("-", $dataFolket["fol_keterangan"]);
    if ($dataTable[$i]["cust_usr_kode"] == '100' || $dataTable[$i]["cust_usr_kode"] == '500') {
      if ($dataFolket["fol_keterangan"] == '' || $dataFolket["fol_keterangan"] == null) {
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_keterangan"];
      } else {
        $tbContent[$i][$counter][TABLE_ISI] = $nama[0];
      }
    } else {
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
    }
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
  }
  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["fol_nominal_satuan"])) : currency_format($dataTable[$i]["fol_nominal_satuan"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["fol_jumlah"])) : currency_format($dataTable[$i]["fol_jumlah"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["fol_nominal"])) : currency_format($dataTable[$i]["fol_nominal"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;

  $sql = "select usr_name from global.global_auth_user where usr_id =" . QuoteValue(DPE_CHAR, $dataTable[$i]["dokter"]);
  $rs = $dtaccess->Execute($sql);
  $dokterFolio = $dtaccess->Fetch($rs);

  $sql = "select usr_name from global.global_auth_user where usr_id =" . QuoteValue(DPE_CHAR, $dataTable[$i]["entri_data"]);
  $rs = $dtaccess->Execute($sql);
  $entri = $dtaccess->Fetch($rs);

  $tbContent[$i][$counter][TABLE_ISI] = $dokterFolio["usr_name"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $entri["usr_name"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  if ($dataTable[$i]["pembayaran_det_id"] != $dataTable[$i - 1]["pembayaran_det_id"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["pembayaran_det_id"]] + 1;

    $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($harusbayar = $dataTable[$i]["pembayaran_det_hrs_bayar"])) : currency_format($harusbayar = $dataTable[$i]["pembayaran_det_hrs_bayar"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($diskonku = $dataTable[$i]["pembayaran_det_diskon"])) : currency_format($diskonku = $dataTable[$i]["pembayaran_det_diskon"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($bayar = $dataTable[$i]["pembayaran_det_dibayar"])) : currency_format($bayar = $dataTable[$i]["pembayaran_det_dibayar"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $totalHarusBayr += $harusbayar;
    $totalDiskon += $diskonku;
    $totalPembayaran += $bayar;
    if ($dataTable[$i]["pembayaran_det_flag"] == 'T') {
      $tbContent[$i][$counter][TABLE_ISI] = 'Tunai';
    }
    if ($dataTable[$i]["pembayaran_det_flag"] == 'P') {
      $tbContent[$i][$counter][TABLE_ISI] = 'Piutang Perorangan';
    }
    if ($dataTable[$i]["pembayaran_det_flag"] == 'S') {
      $tbContent[$i][$counter][TABLE_ISI] = 'Subsidi';
    }
    if ($dataTable[$i]["pembayaran_det_flag"] == 'J') {
      $tbContent[$i][$counter][TABLE_ISI] = 'Jaminan / Asuransi';
    }
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["who_when_update"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
  }
}
//$total += $dataTable[$i]["fol_dibayar"];

//echo $dijamin."-".$subsidi."-".$iur."-".$hrsBayar; die();
$counter = 0;

$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 14;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = "&nbsp;"; //"Rp.".currency_format($totalHarusBayr);
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($totalDiskon)) : "Rp." . currency_format($totalDiskon);
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;

$tbBottom[0][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($totalPembayaran)) : "Rp." . currency_format($totalPembayaran);
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;
/*    
  $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalDijamin);
  $tbBottom[0][$counter][TABLE_ALIGN] = "right";
  $counter++;
  
  $tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalSubsidi);
  $tbBottom[0][$counter][TABLE_ALIGN] = "right";
  $counter++;
*/
$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 5;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;

$tableHeader = "Laporan Detail Penerimaan";
if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=lap_detail_penerimaan_irj.xls');
}

if ($_POST["btnCetak"]) {
  //echo $_POST["ush_id"];
  //die();
  $_x_mode = "cetak";
}


// cari jenis pasien e
$sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$jenisPasien = $dtaccess->FetchAll($rs);


// cek nama perusahaan --
$sql = "select * from global.global_jenis_pasien where jenis_id = '7'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$corporate = $dtaccess->Fetch($rs);

// cari nama perusahaan --
$sql = "select * from global.global_perusahaan where id_dep =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$NamaPerusahaan = $dtaccess->FetchAll($rs);

//ambil nama dokter e
$sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like " . QuoteValue(DPE_CHAR, "%" . $_POST["klinik"]) . " order by usr_name asc ";
$rs = $dtaccess->Execute($sql);
$dataDokter = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if ($konfigurasi["dep_lowest"] == 'n') {
  $sql = "select * from global.global_departemen order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
} else if ($_POST["klinik"]) {
  //Data Klinik
  $sql = "select * from global.global_departemen where dep_id = '" . $_POST["klinik"] . "' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
} else {
  $sql = "select * from global.global_departemen where dep_id = '" . $depId . "' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

if ($_POST["dep_logo"]) $fotoName = $lokasi . "/" . $row_edit["dep_logo"];
else $fotoName = $lokasi . "/default.jpg";
//$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   

// cari jenis bayar ee //
$sql = "select * from global.global_jenis_bayar where jbayar_status='y' and id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by jbayar_id";
$jsBayar = $dtaccess->FetchAll($sql);

// Data Poli //
$sql = "select * from global.global_auth_poli where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by poli_nama";
$dataPoli = $dtaccess->FetchAll($sql);

// cari tipe layanan
$sql = "select * from global.global_tipe_biaya order by tipe_biaya_nama desc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$tipeBiaya = $dtaccess->FetchAll($rs);

// cari nama kasir --
$sql = "select * from global.global_auth_user_app a left join global.global_auth_user b on a.id_usr = b.usr_id where id_app = 5";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir = $dtaccess->FetchAll($rs);


?>

<script language="JavaScript">
  function CheckSimpan(frm) {

    if (!frm.tgl_awal.value) {
      alert("Tanggal Awal Harus Diisi");
      return false;
    }
  }

  window.onload = function() {
    TampilCombo();
  }

  function TampilCombo(id) {

    //alert(id);
    if (id == "7") {
      ush_id.disabled = false;
      //elm_combo.checked = true; 

    } else {
      ush_id.disabled = true;
    }
  }

  var _wnd_new;

  function BukaWindow(url, judul) {
    if (!_wnd_new) {
      _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
    } else {
      if (_wnd_new.closed) {
        _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
      } else {
        _wnd_new.focus();
      }
    }
    return false;
  }

  <?php if ($_x_mode == "cetak") { ?>

    window.open('detail_penerimaan_cetak.php?perusahaan=<?php echo $perusahaan; ?>&id_poli=<?php echo $_POST["id_poli"]; ?>&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&shift=<?php echo $_POST["reg_shift"]; ?>&dokter=<?php echo $_POST["id_dokter"]; ?>&js_biaya=<?php echo $_POST["js_biaya"]; ?>&jbayar=<?php echo $_POST["jbayar"]; ?>&kasir=<?php echo $kasir; ?>&reg_tipe_layanan=<?php echo $_POST["reg_tipe_layanan"] ?>&layanan=<?php echo $_POST["layanan"] ?>&pembayaran_det_flag=<?php echo $_POST["pembayaran_det_flag"] ?>&nama=<?php echo $_POST["fol_nama"] ?>', '_blank');
  <?php } ?>
</script>

<?php if (!$_POST["btnExcel"]) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<?php } ?>
<script src="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $("a[rel=sepur]").fancybox({
      'width': '50%',
      'height': '100%',
      'autoScale': false,
      'transitionIn': 'none',
      'transitionOut': 'none',
      'type': 'iframe'
    });
  });
</script>
<?php if (!$_POST["btnExcel"]) { ?>

<?php } ?>
<?php if ($_POST["btnExcel"]) { ?>

  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr class="tableheader">
      <td align="center" colspan="21">
        <strong>LAPORAN DETAIL PENERIMAAN IRJ<br />
          <?php echo $konfigurasi["dep_nama"] ?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"] ?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"] ?>
          <br />
        </strong>
      </td>
    </tr>
    <tr class="tableheader">
      <td align="left" colspan="21">
        <?php echo $poliNama; ?><br />
        <?php if ($_POST["tgl_awal"] == $_POST["tgl_akhir"]) {
          echo "Tanggal : " . $_POST["tgl_awal"];
        } elseif ($_POST["tgl_awal"] != $_POST["tgl_akhir"]) {
          echo "Periode : " . $_POST["tgl_awal"] . " s/d " . $_POST["tgl_akhir"];
        }  ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php echo "Shift : " . $_POST["shift"]; ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if ($_POST["cust_usr_jenis"]) {
          echo "Jenis Pasien : " . $bayarPasien[$_POST["cust_usr_jenis"]];
        } ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php //echo "Nama Poli : ".$dataPoli[$i]["poli_nama"]; 
        ?>

      </td>
    </tr>
  </table>
<?php } ?>
</div>
</div>



<?php if (!$_POST["btnExcel"]) { ?>


  <!DOCTYPE html>
  <html lang="en">
  <?php require_once($LAY . "header.php") ?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY . "sidebar.php") ?>

        <!-- top navigation -->
        <?php require_once($LAY . "topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                <?php } ?>
                <div class="x_title">
                  <h2>Laporan Detail Penerimaan</h2>
                  <div class="clearfix"></div>
                </div>

                <?php if (!$_POST["btnExcel"]) { ?>
                  <div class="x_content">

                    <form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="tgl_awal" type='text' class="form-control" value="<?php if ($_POST['tgl_awal']) {
                                                                                            echo $_POST['tgl_awal'];
                                                                                          } else {
                                                                                            echo date('d-m-Y');
                                                                                          } ?>" />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>

                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker2'>
                          <input name="tgl_akhir" type='text' class="form-control" value="<?php if ($_POST['tgl_akhir']) {
                                                                                            echo $_POST['tgl_akhir'];
                                                                                          } else {
                                                                                            echo date('d-m-Y');
                                                                                          } ?>" />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                        <input type="text" name="cust_usr_nama" id="cust_usr_nama" value="<?php echo $_POST['cust_usr_nama'] ?>" class="form-control">
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nomor MedRec</label>
                        <input type="text" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $_POST['cust_usr_kode'] ?>" class="form-control">
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Perawatan</label>
                        <select class="form-control" name="layanan" id="layanan" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Semua Tipe Perawatan ]</option>
                          <option value="A" <?php if ($_POST["layanan"] == 'A') echo "selected"; ?>>Rawat Jalan</option>
                          <option value="I" <?php if ($_POST["layanan"] == 'I') echo "selected"; ?>>Rawat Inap</option>
                          <option value="G" <?php if ($_POST["layanan"] == 'G') echo "selected"; ?>>I G D</option>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
                        <select class="form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                          <!--onChange="this.form.submit();" -->
                          <option value="0">[ Pilih Jenis Bayar ]</option>
                          <?php for ($i = 0, $n = count($jenisPasien); $i < $n; $i++) { ?>
                            <option value="<?php echo $jenisPasien[$i]["jenis_id"]; ?>" <?php if ($jenisPasien[$i]["jenis_id"] == $_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"]; ?>');"><?php echo ($i + 1) . ". " . $jenisPasien[$i]["jenis_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
                        <select class="form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[ Pilih Dokter ]</option>
                          <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Klinik</label>
                        <select name="id_poli" class="form-control" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Pilih Klinik ]</option>

                          <?php for ($i = 0, $n = count($dataPoli); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataPoli[$i]["poli_id"]; ?>" <?php if ($dataPoli[$i]["poli_id"] == $_POST["id_poli"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataPoli[$i]["poli_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Petugas</label>
                        <select name="usr_id" class="form-control" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Pilih Nama Petugas ]</option>
                          <?php for ($i = 0, $n = count($dataKasir); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataKasir[$i]["usr_name"]; ?>" <?php if ($_POST["usr_id"] == $dataKasir[$i]["usr_name"]) echo "selected"; ?>><?php echo $dataKasir[$i]["usr_name"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Perusahaan</label>
                        <select class="form-control" name="ush_id" id="ush_id" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[ Pilih Nama Perusahaan ]</option>
                          <?php for ($i = 0, $n = count($NamaPerusahaan); $i < $n; $i++) { ?>
                            <option value="<?php echo $NamaPerusahaan[$i]["perusahaan_id"]; ?>" <?php if ($NamaPerusahaan[$i]["perusahaan_id"] == $_POST["ush_id"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $NamaPerusahaan[$i]["perusahaan_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Bayar</label>
                        <select class="form-control" name="jbayar" id="jbayar" onKeyDown="return tabOnEnter(this, event);">
                          <option class="inputField" value="">[ Pilih Jenis Bayar ]</option>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($jsBayar); $i < $n; $i++) {
                            unset($spacer);
                            $length = (strlen($jsBayar[$i]["jbayar_id"]) / TREE_LENGTH_CHILD) - 1;
                            for ($j = 0; $j < $length; $j++) $spacer .= "..";
                          ?>
                            <option value="<?php echo $jsBayar[$i]["jbayar_id"]; ?>" <?php if ($_POST["jbayar"] == $jsBayar[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer . " " . $jsBayar[$i]["jbayar_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Penerimaan</label>
                        <select class="form-control" name="pembayaran_det_flag" id="pembayaran_det_flag" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Semua Tipe Penerimaan ]</option>
                          <option value="T" <?php if ($_POST["pembayaran_det_flag"] == 'T') echo "selected"; ?>>Tunai</option>
                          <option value="P" <?php if ($_POST["pembayaran_det_flag"] == 'P') echo "selected"; ?>>Piutang Perorangan</option>
                          <option value="S" <?php if ($_POST["pembayaran_det_flag"] == 'S') echo "selected"; ?>>Subsidi</option>
                          <option value="J" <?php if ($_POST["pembayaran_det_flag"] == 'J') echo "selected"; ?>>Jaminan/Asuransi</option>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Tindakan</label>
                        <input type="text" class="form-control" id="fol_nama" name="fol_nama" size="100" maxlength="255" value="<?php echo $_POST["fol_nama"]; ?>" />

                      </div>


                      <div class="col-md-4 pull-right col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
                        <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                        <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
                        <!--input type="submit" name="btnExcelNew" value="Export Data" class="pull-right btn btn-primary"-->
                      </div>
                      <div class="clearfix"></div>
                      <? if ($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]) { ?>
                      <? } ?>
                      <? if ($_x_mode == "Edit") { ?>
                        <?php echo $view->RenderHidden("kategori_tindakan_id", "kategori_tindakan_id", $biayaId); ?>
                      <? } ?>


                    </form>
                  </div>
                <?php } ?>
                </div>
              </div>
            </div>
            <!-- //row filter -->


            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <!--table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI]; ?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for ($i = 0, $n = count($dataTable); $i < $n; $i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI] ?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table -->
                  <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /page content -->

      <?php if (!$_POST["btnExcel"]) { ?>
        <!-- footer content -->
        <?php require_once($LAY . "footer.php") ?>
        <!-- /footer content -->
    </div>
    </div>

    <?php require_once($LAY . "js.php") ?>

  </body>

  </html>
<?php } ?>