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
if ($_POST["tgl_awal"]) $sql_where[] = "date(a.pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
if ($_POST["tgl_akhir"]) $sql_where[] = "date(a.pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));

if ($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"] != "0") {
  $sql_where[] = "c.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_jenis"]);
}

if ($_POST["id_poli"] != '--') {
  $sql_where[] = "e.poli_id = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
}

if ($_POST["usr_id"] <> "--") {
  $sql_where[] = "a.who_when_update = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
}

// if ($_POST["pembayaran_det_flag"] <> "--") {
//   $sql_where[] = "a.pembayaran_det_flag = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_det_flag"]);
// }

if ($_POST["layanan"] <> "--") {
  if ($_POST["layanan"] == "A") {
    $sql_where[] = "c.reg_tipe_rawat = 'J'";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "c.reg_tipe_rawat = 'I'";
  } elseif ($_POST["layanan"] == "Lain") {
    $sql_where[] = "c.reg_tipe_rawat = 'Lain'";
  } else {
    $sql_where[] = "c.reg_tipe_rawat = 'G'";
  }
}

if ($_POST['cust_usr_nama']) $sql_where[] = "d.cust_usr_nama like '%".strtoupper($_POST['cust_usr_nama'])."%'";
if ($_POST['cust_usr_kode']) $sql_where[] = "d.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_kode']);


/*if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
       $sql_where[] = "a.pembayaran_who_create = '".$userName."'";
     }*/

     $sql_where = implode(" and ", $sql_where);
     $sql = "select a.pembayaran_det_id, a.pembayaran_det_create, a.pembayaran_det_kwitansi, b.pembayaran_id, c.reg_id, d.cust_usr_id, d.cust_usr_kode, d.cust_usr_nama, poli_nama, reg_tipe_rawat, reg_keterangan
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and (pembayaran_det_mp_ke = '1' or (a.id_jbayar = 'Diskon' and 
                (select count(pembayaran_det_id) from klinik.klinik_pembayaran_det x where x.pembayaran_det_kwitansi = a.pembayaran_det_kwitansi) = 1)) and " . $sql_where;
     $sql .= " order by a.pembayaran_det_kwitansi, a.pembayaran_det_create, b.pembayaran_id asc";
     // echo $sql;
     $dataTable = $dtaccess->FetchAll($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_tunai
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar = '01' and " . $sql_where;
     $dataPenerimaanTunai = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_bank
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01') and " . $sql_where;
     $dataPenerimaanBank = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_asuransi
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar in(select perusahaan_id from global.global_perusahaan) and " . $sql_where;
     $dataPenerimaanAsuransi = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_bpjs
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar = 'BPJS' and " . $sql_where;
     $dataPenerimaanBPJS = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_karyawan
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar = 'Karyawan' and " . $sql_where;
     $dataPenerimaanKaryawan = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_karyawan
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and a.id_jbayar = 'x' and " . $sql_where;
     $TotalPiutangUmum = $dtaccess->Fetch($sql);

     $sql = "select sum(pembayaran_det_dibayar) as total_karyawan
     FROM klinik.klinik_pembayaran_det a 
     LEFT JOIN klinik.klinik_pembayaran b ON a.id_pembayaran = b.pembayaran_id 
     LEFT JOIN klinik.klinik_registrasi c ON b.id_reg = c.reg_id 
     LEFT JOIN global.global_customer_user d ON c.id_cust_usr = d.cust_usr_id
     LEFT JOIN global.global_auth_poli e on e.poli_id = c.id_poli";
     $sql .= " where 1=1 and pembayaran_det_flag = 'P' and pembayaran_det_tipe_piutang = 'P' and " . $sql_where;
     $TotalKurangBayar = $dtaccess->Fetch($sql);




     $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag from klinik.klinik_deposit_history a 
     left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
     where date(deposit_history_when_create) >= '" . date_format(date_create($_POST['tgl_awal']), 'Y-m-d') . "' 
     and date(deposit_history_when_create) <= '" . date_format(date_create($_POST['tgl_akhir']), 'Y-m-d') . "'
     and deposit_history_nominal <> '0' and id_jbayar = '01'";
     if ($_POST["usr_id"] <> "--") {
      $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
    }
    if ($_POST["cust_usr_kode"]<>'') {
      $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
    }
    if ($_POST['layanan'] == 'I') {
      $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
    }elseif ($_POST['layanan'] == 'DP') {
      $sql .= " and (deposit_history_flag = 'M')";
    }
               // echo $sql;
    $dataDepositTunai = $dtaccess->FetchAll($sql);


                for ($i = 0; $i < count($dataDepositTunai); $i++) {
                  if ($dataDepositTunai[$i]['deposit_history_flag'] == 'R') {
                    $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                    $ReturDeposit += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                  }elseif($dataDepositTunai[$i]['deposit_history_flag'] == 'P' || $dataDepositTunai[$i]['deposit_history_flag'] == 'E' ){
                    $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                    $PemakaianDeposit += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                  }elseif($dataDepositTunai[$i]['deposit_history_flag'] == 'M'){
                    $totalDepositTunai += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                    $DepositMasuk += str_replace('-', '', $dataDepositTunai[$i]['deposit_history_nominal']);
                  }
                }


    $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag from klinik.klinik_deposit_history a 
    left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
    where date(deposit_history_when_create) >= '" . date_format(date_create($_POST['tgl_awal']), 'Y-m-d') . "' 
     and date(deposit_history_when_create) <= '" . date_format(date_create($_POST['tgl_akhir']), 'Y-m-d') . "'
    and deposit_history_nominal <> '0' and id_jbayar <> '01'";
    if ($_POST["usr_id"] <> "--") {
      $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
    }
    if ($_POST["cust_usr_kode"]<>'') {
      $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
    }
    if ($_POST['layanan'] == 'I') {
      $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
    }elseif ($_POST['layanan'] == 'DP') {
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


    $data = array();

    foreach ($dataTable as $key => $value) {
      $sql = "SELECT SUM(pembayaran_det_dibayar) AS total_pembayaran FROM klinik.klinik_pembayaran_det a LEFT JOIN global.global_jenis_bayar b ON a.id_jbayar = b.jbayar_id WHERE pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." AND a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id']);
      $TotalPembayaran = $dtaccess->Fetch($sql);

      $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$value['pembayaran_id'])." and id_jbayar = 'Disc'";
      $PembayaranDisc = $dtaccess->Fetch($sql);

      $sql = "SELECT sum(pembayaran_det_dibayar) as diskon from klinik.klinik_pembayaran_det a 
      left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar 
      where id_pembayaran = ".QuoteValue(DPE_CHAR,$value['pembayaran_id'])." and 
      date(pembayaran_det_create) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"])) . " and 
      date(pembayaran_det_create) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"])) . " and 
      pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$value['pembayaran_det_kwitansi'])." and id_jbayar = 'Diskon'";
      $TotalDiskon = $dtaccess->Fetch($sql);

      $sql = "SELECT a.pembayaran_det_id, a.pembayaran_det_dibayar, b.jbayar_nama, c.perusahaan_nama, a.id_jbayar, a.who_when_update 
      FROM klinik.klinik_pembayaran_det a 
      LEFT JOIN global.global_jenis_bayar b ON a.id_jbayar = b.jbayar_id 
      LEFT JOIN global.global_perusahaan c ON c.perusahaan_id = a.id_jbayar
      WHERE pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." AND a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id'])." ORDER BY a.pembayaran_det_mp_ke ASC";
      
      $dataPembayaran = $dtaccess->FetchAll($sql);

      $SubDiscPenjualan += $PembayaranDisc['total'];

      $pembayaran = array();

      foreach ($dataPembayaran as $val) {
        // echo $val['id_jbayar'];
       if ($val['id_jbayar'] == 'BPJS') {
        $isi_pembayaran['jenis_bayar'] = 'BPJS';
        $isi_pembayaran['total_tunai'] += $val['pembayaran_det_dibayar'];
      }
      if ($val['id_jbayar'] == '01' || $val['id_jbayar'] != "Diskon"  ) {
        $isi_pembayaran['jenis_bayar'] = $val['jbayar_nama'];
        $isi_pembayaran['total_tunai'] += $val['pembayaran_det_dibayar'];
      }
      
      elseif ($val['id_jbayar'] == "Diskon" ) {
            // code...
       $isi_pembayaran['jenis_bayar'] = $val['id_jbayar'];
       $isi_pembayaran['total_tunai'] += $val['pembayaran_det_dibayar'];
     }
     elseif ($val['perusahaan_nama'] != '') {
      $isi_pembayaran['jenis_bayar'] = $val['perusahaan_nama'];
    }
    $isi_pembayaran['pembayaran_nominal'] = $val['pembayaran_det_dibayar'];
    $isi_pembayaran['kasir'] = $val['who_when_update'];

    array_push($pembayaran, $isi_pembayaran);
  }

  $sql = "SELECT SUM(fol_nominal) as total_tindakan FROM klinik.klinik_folio a 
  LEFT JOIN klinik.klinik_biaya b ON a.id_biaya = b.biaya_id 
  WHERE a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id'])." 
  AND a.fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." AND a.fol_lunas = 'y'";
  $TotalTindakan = $dtaccess->Fetch($sql);

  $sql = "SELECT (SUM(fol_nominal) * 0.1) as total_jasars FROM klinik.klinik_folio a 
  LEFT JOIN klinik.klinik_biaya b ON a.id_biaya = b.biaya_id 
  WHERE a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id'])." 
  AND a.fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." 
  and a.id_biaya != '9999999' AND a.fol_lunas = 'y'";
    //if($value['pembayaran_det_id'] == '0953e0fd2bb8c5262b7aa859853190ca') echo $sql;
  $jasaRS = $dtaccess->Fetch($sql);

  $sql = "SELECT a.fol_id, a.fol_nominal, b.biaya_nama FROM klinik.klinik_folio a 
  LEFT JOIN klinik.klinik_biaya b ON a.id_biaya = b.biaya_id 
  WHERE a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id'])." 
  AND a.fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." 
  AND a.fol_lunas = 'y' AND fol_nama <> '' ORDER BY a.fol_waktu ASC";
  $dataTindakan = $dtaccess->FetchAll($sql);

  $tindakan = array();

  foreach ($dataTindakan as $val) {
    $isi_tindakan['nama_tindakan'] = $val['biaya_nama'];
    $isi_tindakan['harga_tindakan'] = $val['fol_nominal'];

    array_push($tindakan, $isi_tindakan);
  }

  $isi['nomor'] = $key+1;
  $isi['tanggal'] = $value['pembayaran_det_create'];
  $isi['kwitansi'] = $value['pembayaran_det_kwitansi'];
  $isi['pembayaran_det_id'] = $value['pembayaran_det_id'];
  if ($value['cust_usr_kode'] == '100' || $value['cust_usr_kode'] == '500') {
    $isi['nama'] = $value['reg_keterangan'];
  } else {
    $isi['nama'] = $value['cust_usr_nama'];
  }
  $isi['no_rm'] = $value['cust_usr_kode'];
  $isi['pembayaran'] = $pembayaran;
  $isi['tindakan'] = $tindakan;
  $isi['poli'] = $value['poli_nama'];
  $isi['total_pembayaran'] = $TotalPembayaran['total_pembayaran'];
  $isi['total_tindakan'] = $TotalTindakan['total_tindakan'];
  if ($value['reg_tipe_rawat'] == 'I') {
    $isi['jasa_rs'] = $jasaRS['total_jasars'];
  } else {
    $isi['jasa_rs'] = 0;
  }
  $isi['total_diskon'] = $TotalDiskon['diskon'];

  array_push($data, $isi);
}

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

// cari nama kasir --
$sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1' or id_rol = '35')";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir2 = $dtaccess->FetchAll($rs);


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

    window.open('detail_penerimaan_cetak.php?perusahaan=<?php echo $perusahaan; ?>&id_poli=<?php echo $_POST["id_poli"]; ?>&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&waktu_awal=<?php echo $_POST["waktu_awal"]; ?>&waktu_akhir=<?php echo $_POST["waktu_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&reg_shift=<?php echo $_POST["reg_shift"]; ?>&id_dokter=<?php echo $_POST["id_dokter"]; ?>&js_biaya=<?php echo $_POST["js_biaya"]; ?>&jbayar=<?php echo $_POST["jbayar"]; ?>&usr_id=<?php echo $kasir; ?>&reg_tipe_layanan=<?php echo $_POST["reg_tipe_layanan"] ?>&layanan=<?php echo $_POST["layanan"] ?>&pembayaran_det_flag=<?php echo $_POST["pembayaran_det_flag"] ?>&nama=<?php echo $_POST["fol_nama"] ?>&cust_usr_nama=<?php echo $_POST["cust_usr_nama"] ?>&cust_usr_kode=<?php echo $_POST["cust_usr_kode"] ?>', '_blank');
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
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nomor MedRec</label>
                            <input type="text" name="cust_usr_kode" id="cust_usr_kode" value="<?php echo $_POST['cust_usr_kode'] ?>" class="form-control">
                          </div>

                          <div class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Penerimaan</label>
                            <select name="layanan" class="select2_single form-control" id="layanan" onKeyDown="return tabOnEnter(this, event);">
                              <option value="--">[ Semua Tipe Penerimaan ]</option>
                              <option value="A" <?php if ($_POST["layanan"] == 'A') echo "selected"; ?>>Rawat Jalan</option>
                              <option value="I" <?php if ($_POST["layanan"] == 'I') echo "selected"; ?>>Rawat Inap</option>
                              <option value="G" <?php if ($_POST["layanan"] == 'G') echo "selected"; ?>>I G D</option>
                              <option value="Disc" <?php if ($_POST["layanan"] == 'Disc') echo "selected"; ?>>Diskon</option>
                              <option value="DP" <?php if ($_POST["layanan"] == 'DP') echo "selected"; ?>>Deposit</option>
                              <option value="Lain" <?php if ($_POST["layanan"] == 'Lain') echo "selected"; ?>>Lain-lain</option>
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
                      <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
                        <select class="form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[ Pilih Dokter ]</option>
                          <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"]; ?></option>
                          <?php } ?>
                        </select>
                      </div> -->
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Ptg. Kasir</label>
                        <select name="usr_id" class="form-control" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Pilih Nama Petugas ]</option>
                          <?php for ($i = 0, $n = count($dataKasir2); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataKasir2[$i]["usr_name"]; ?>" <?php if ($_POST["usr_id"] == $dataKasir2[$i]["usr_name"]) echo "selected"; ?>><?php echo $dataKasir2[$i]["usr_name"]; ?></option>
                          <?php } ?>
                        </select>
                      </div> 

                      <div class="col-md-4 col-sm-6 col-xs-12" hidden>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Shift</label>
                        <select class="select2_single form-control" name="reg_shift" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[ Pilih Shift ]</option>
                          <?php for ($i = 0, $n = count($dataShift); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataShift[$i]["shift_id"]; ?>" <?php if ($_POST["reg_shift"] == $dataShift[$i]["shift_id"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"] . " (" . $dataShift[$i]["shift_jam_awal"] . "-" . $dataShift[$i]["shift_jam_akhir"] . ")"; ?></option>
                          <?php } ?>
                        </select>

                      </div>
                      <!-- <div class="col-md-4 col-sm-6 col-xs-12">
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
                      </div> -->
                      <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Penerimaan</label>
                        <select class="form-control" name="pembayaran_det_flag" id="pembayaran_det_flag" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Semua Tipe Penerimaan ]</option>
                          <option value="T" <?php if ($_POST["pembayaran_det_flag"] == 'T') echo "selected"; ?>>Tunai</option>
                          <option value="P" <?php if ($_POST["pembayaran_det_flag"] == 'P') echo "selected"; ?>>Piutang Perorangan</option>
                          <option value="S" <?php if ($_POST["pembayaran_det_flag"] == 'S') echo "selected"; ?>>Subsidi</option>
                          <option value="J" <?php if ($_POST["pembayaran_det_flag"] == 'J') echo "selected"; ?>>Jaminan/Asuransi</option>
                        </select>
                      </div> -->

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
              <?php if ($_POST['layanan'] == 'A' || $_POST['layanan'] == 'I' || $_POST['layanan'] == 'G' || $_POST['layanan'] == '--') { ?>
                <div class="x_content">
                  <!-- <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?> -->
                  <table border="1" class="table table-bordered" width="100%">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>No Kwitansi</th>
                        <th>No RM</th>
                        <th>Nama Pasien</th>
                        <th style="text-align: center;">Pembayaran</th>
                        <th>Total Pembayaran</th>
                        <th style="text-align: center;">Tindakan</th>
                        <th>Jasa RS</th>
                        <th>Diskon</th>
                        <th>Total Tindakan</th>
                        <th>Klinik</th>
                        <!-- <th></th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($data) { ?>
                        <?php 
                        foreach ($data as $value) { 
                          $SumPembayaran += $value['total_pembayaran'];
                          $SumTindakan += $value['total_tindakan'];
                          $SumDiskon += $value['total_diskon'];
                          $SumJasaRS += $value['jasa_rs'];
                          $PenerimaanTunai = $val['total_tunai'];
                          $PenerimaanBank = $val['total_bank'];
                          ?>
                          <tr>
                            <td><?php echo $value['nomor'] ?></td>
                            <td><?php echo $value['tanggal'] ?></td>
                            <td><?php echo $value['kwitansi'] ?></td>
                            <td><?php echo $value['no_rm'] ?></td>
                            <td><?php echo $value['nama'] ?></td>
                            <td>
                              <table border="1" class="table table-bordered" width="100%">
                                <thead>
                                  <tr>
                                    <th>Jenis Bayar</th>
                                    <th>Total</th>
                                    <th>Ptg Kasir</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php if ($value['pembayaran']): ?>
                                    <?php foreach ($value['pembayaran'] as $val): ?>
                                      <tr>
                                        <td><?= $val['jenis_bayar'] ?></td>
                                        <td><?= currency_format($val['pembayaran_nominal']) ?></td>
                                        <td><?= $val['kasir'] ?></td>
                                      </tr>
                                    <?php endforeach ?>
                                  <?php endif 
                                  ?>

                                </tbody>
                              </table>
                            </td>
                            <td align="right"><?php echo  currency_format($value['total_pembayaran']) ?></td>
                            <td>
                              <table border="1" class="table table-bordered" width="100%">
                                <thead>
                                  <tr>
                                    <th>Nama Tindakan</th>
                                    <th>Biaya Tindakan</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <?php if ($value['tindakan']): ?>
                                    <?php foreach ($value['tindakan'] as $val): ?>
                                      <tr>
                                        <td><?= $val['nama_tindakan'] ?></td>
                                        <td align="right"><?= currency_format($val['harga_tindakan']) ?></td>
                                      </tr>
                                    <?php endforeach ?>
                                  <?php endif ?>
                                </tbody>
                              </table>
                            </td>
                            <td align="right"><?php echo currency_format($value['jasa_rs']) ?></td>
                            <td align="right"><?php echo currency_format($value['total_diskon']) ?></td>
                            <td align="right"><?php echo currency_format($value['total_tindakan'] + $value['jasa_rs']) ?></td>
                            <td><?php echo $value['poli'] ?></td>
                            <!-- <td><?=$value['pembayaran_det_id'] ?></td> -->
                          </tr>
                        <?php } ?>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="6" align="right"><b>TOTAL</b></td>
                        <td align="right"><?php echo  currency_format($SumPembayaran); ?></td>
                        <td align="right">&nbsp;</td>
                        <td align="right"><?php echo currency_format($SumJasaRS); ?></td>
                        <td align="right"><?php echo  currency_format($SumDiskon); ?></td>
                        <td align="right"><?php echo  currency_format($SumTindakan); ?></td>
                      </tr>
                    </tfoot>
                  </table>
                  <?php 
                 
               $sql = "select * from klinik.klinik_deposit_history a 
                left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                left join global.global_jenis_bayar c on c.jbayar_id = a.id_jbayar
                where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']).' '.$_POST['waktu_awal']) . " 
                and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']).' '.$_POST['waktu_akhir']) . "
                and deposit_history_nominal <> '0'";
                if ($_POST["usr_id"] <> "--") {
                  $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                }
                if ($_POST["cust_usr_kode"]<>'') {
                  $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
                }
                if ($_POST['layanan'] == 'I') {
                  $sql .= " and (deposit_history_flag = 'P' or deposit_history_flag = 'R')";
                }elseif ($_POST['layanan'] == 'DP') {
                  $sql .= " and (deposit_history_flag = 'M')";
                }
                $sql .= "order by deposit_history_when_create asc";
                  // echo $sql;
                $dataDeposit = $dtaccess->FetchAll($sql);

                  $sql = "select sum(deposit_history_nominal) as total_deposit from klinik.klinik_deposit_history a 
                  left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                  where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal'])) . " 
                  and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir'])) . "
                  and deposit_history_nominal <> '0' and (deposit_history_flag = 'M' or deposit_history_flag = 'R') and id_jbayar = '01'";
                  if ($_POST["usr_id"] <> "--") {
                    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                  }
                  if ($_POST["cust_usr_kode"]<>'') {
                    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
                  }
                  $dataDepositTunai = $dtaccess->Fetch($sql);

                  $sql = "select sum(deposit_history_nominal) as total_deposit from klinik.klinik_deposit_history a 
                  left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                  where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal'])) . " 
                  and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir'])) . "
                  and deposit_history_nominal <> '0' and (deposit_history_flag = 'M' or deposit_history_flag = 'R') and id_jbayar != '01'";
                  if ($_POST["usr_id"] <> "--") {
                    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                  }
                  if ($_POST["cust_usr_kode"]<>'') {
                    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
                  }
                  $dataDepositNonTunai = $dtaccess->Fetch($sql);
                  ?>
                  <?php if (count($dataDeposit) > 0) : ?>
                    <?php if ($_POST['layanan'] == 'DP' || $_POST['layanan'] == '--') { ?>
                      <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">
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
                            <th style="text-align: center;">Petugas Entry</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php for ($i = 0; $i < count($dataDeposit); $i++) { ?>
                            <tr>
                              <td align="center"><?php echo $i + 1 ?></td>
                              <td align="center"><?php echo date_db($dataDeposit[$i]['deposit_history_tgl']) ?></td>
                              <td align="center"><?php echo $dataDeposit[$i]['deposit_history_no_bukti'] ?></td>
                              <td align="center"><?php echo $dataDeposit[$i]['cust_usr_kode'] ?></td>
                              <td><?php echo $dataDeposit[$i]['cust_usr_nama'] ?></td>
                              <td align="right">
                                <?php if ($dataDeposit[$i]['deposit_history_nominal'] > 0) {

                                  echo ($_POST['btnExcel']) ? currency_format(str_replace("-","",$dataDeposit[$i]['deposit_history_nominal'])) : currency_format(str_replace("-","",$dataDeposit[$i]['deposit_history_nominal']));
                                  $SumDepositDebet += $dataDeposit[$i]['deposit_history_nominal'];
                                } else {
                                  echo "(" .  str_replace("-","",currency_format($dataDeposit[$i]['deposit_history_nominal'])) . ")";
                                  $SumDepositKredit += $dataDeposit[$i]['deposit_history_nominal'];
                                } ?>

                              </td>
                              <td align="center"><?php echo $dataDeposit[$i]['deposit_history_who_create'] ?></td>
                            </tr>
                          <?php } ?>
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="5" align="left"><b>TOTAL</b></td>
                            <td align="right"><b><?php $totalHistoryDeposit = $SumDepositDebet - str_replace("-", "", $SumDepositKredit);
                            echo ($_POST['btnExcel']) ? str_replace("-","",currency_format($totalHistoryDeposit)) : str_replace("-","",currency_format($totalHistoryDeposit)) ?></b></td>
                          </tr>
                        </tfoot>
                      </table>
                    <?php } ?>
                  <?php endif; ?>
                 
                <?php } ?>
                <?php 
                if ($_POST["tgl_awal"]) $sql_where2[] = "date(a.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
                if ($_POST["tgl_akhir"]) $sql_where2[] = "date(a.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));
                if ($_POST["usr_id"]  <> "--") $sql_where2[] = "p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                    // echo $_POST["usr_id"];
                    // exit();
                $sql_where2 = implode(" and ", $sql_where2);
                $sql = "select g.batch_no, e.usr_name,c.item_tipe_jenis,d.cust_usr_nama,a.cust_usr_nama as nama, d.cust_usr_id,
                c.item_nama,b.penjualan_detail_jumlah,a.penjualan_nomor,
                b.penjualan_detail_harga_jual,b.penjualan_detail_total,b.penjualan_detail_tuslag,b.penjualan_detail_ppn,
                a.penjualan_create, a.penjualan_grandtotal, a.no_resep,a.penjualan_total,a.dokter_nama,a.penjualan_id,b.id_penjualan,
                a.penjualan_biaya_resep, a.penjualan_biaya_racikan, a.penjualan_biaya_bhps, a.penjualan_diskon,
                a.penjualan_biaya_pembulatan, f.dep_nama,d.cust_usr_nama,d.cust_usr_kode, a.penjualan_tuslag
                from kasir.kasir_penjualan a 
                left join kasir.kasir_penjualan_detail b on b.id_penjualan = a.penjualan_id
                left join kasir.kasir_item c on b.id_item = c.item_id
                left join global.global_customer_user d on d.cust_usr_id = a.id_cust_usr
                left join global.global_auth_user e on e.usr_id = a.who_update
                left join global.global_departemen f on f.dep_id = a.id_dep
                left join logistik.logistik_item_batch g on g.batch_id = b.id_batch
                left join kasir.kasir_data_pembeli h on a.id_reg = h.reg_id
                left join kasir.kasir_pembayaran p on p.id_reg=h.reg_id";
                $sql .= " where p.pembayaran_yg_dibayar!='0' and " . $sql_where2;
                $sql .= " order by a.penjualan_create desc,item_nama asc";
                       // echo $sql;

                $rs = $dtaccess->Execute($sql);
                $dataPenjualan = $dtaccess->FetchAll($rs);

                $sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
                left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                where b.id_jbayar = '01' and ". $sql_where2;
                $dataPenjualanTunai = $dtaccess->Fetch($sql);

                $sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
                left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                where b.id_jbayar != '01' and ". $sql_where2;
                $dataPenjualanNonTunai = $dtaccess->Fetch($sql);
                ?>
                <?php if (count($dataPenjualan) > 0) : ?>
                  <?php if (($_POST['layanan'] == 'Lain' || $_POST['layanan'] == '--') && $_POST['cust_usr_jenis'] != '7' && $_POST['cust_usr_kode'] == '' && $_POST['id_poli'] == '--') { 
                    
                    ?>

                    <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">
                      <thead>
                        <tr>
                          <th colspan="8"><b>Daftar Penjualan Kasir</b></th>
                        </tr>
                        <tr>
                          <th style="text-align: center;" rowspan="2">No</th>
                          <th style="text-align: center;">Tanggal</th>
                          <th style="text-align: center;">No Nota</th>
                          <th style="text-align: center;">Pasien</th>
                          <th style="text-align: center;">Total</th>
                          <th style="text-align: center;" rowspan="2">Kasir</th>
                          <th style="text-align: center;" rowspan="2">Diskon</th>
                          <th style="text-align: center;" rowspan="2">GrandTotal</th>
                        </tr>
                        <tr>
                          <th style="text-align: center;">No</th>
                          <th style="text-align: center;">Detail</th>
                          <th style="text-align: center;">Jumlah</th>
                          <th style="text-align: center;">Harga Satuan</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $no = 1;
                        $totalPenjualan = 0;
                        for ($i = 0; $i < count($dataPenjualan); $i++) {
                          $totalPenjualan = $totalPenjualan + $dataPenjualan[$i]['penjualan_detail_total']; ?>

                          <?php if ($dataPenjualan[$i]["penjualan_id"] != $dataPenjualan[$i - 1]["penjualan_id"]) { ?>
                            <tr>
                              <td><?php echo $no++; ?></td>
                              <td><?php $tanggal = explode(" ", $dataPenjualan[$i]["penjualan_create"]);
                              echo format_date($tanggal[0]) . " " . $tanggal[1]; ?></td>
                              <td><?php echo $dataPenjualan[$i]['penjualan_nomor']; ?></td>
                              <td><?php echo ($dataPenjualan[$i]["cust_usr_nama"] != 'Pasien lain-lain') ? $dataPenjualan[$i]["cust_usr_nama"] : $dataPenjualan[$i]["nama"]; ?></td>
                              <td><?php echo ($_POST['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_total"]) : currency_format($dataPenjualan[$i]["penjualan_total"]); ?></td>
                              <td><?php echo  $dataPenjualan[$i]["usr_name"]; ?></td>
                              <td><?php echo ($_POST['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_diskon"]) : currency_format($dataPenjualan[$i]["penjualan_diskon"]) ?></td>
                              <td align="right"><?php $total_penjualan_kasir = $total_penjualan_kasir + $dataPenjualan[$i]["penjualan_total"];
                              echo ($_POST['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_total"]) : currency_format($dataPenjualan[$i]["penjualan_total"]) ?></td>
                            </tr>
                            <?php 
                            $JumlahPenjualan++;
                            $j = 1;
                          } 
                          ?>
                          <tr>
                            <td></td>
                            <td><?php echo $j++; ?></td>
                            <td><?php echo $dataPenjualan[$i]['item_nama']; ?></td>
                            <td><?php echo ($_POST['btnExcel']) ?currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"]) : currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"]); ?></td>
                            <td><?php echo ($_POST['btnExcel']) ? currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"]) : currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"]); ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                          </tr>
                          <?php
                          $TotalJumlahPenjualan = $JumlahPenjualan;
                        } 
                        ?>
                        <tr>
                          <td colspan="7" align="center">
                            TOTAL
                          </td>
                          <td align="right"><?= ($_POST['btnExcel']) ? currency_format($total_penjualan_kasir) : currency_format($total_penjualan_kasir) ?></td>
                        </tr>
                      </tbody>
                    </table>
                  <?php } ?>
                <?php endif; ?>
                <p>TOTAL GLOBAL</p>
                <table cellspacing="0" width="100%" class="table table-bordered" border="1" style="margin-top:15px;">
                  <thead align="center">
                    <tr>
                      <td rowspan="2"></td>
                      <td align="center" colspan="2">Tunai/Bank</td>
                      <td align="center" rowspan="2">BPJS</td>
                      <td align="center" rowspan="2">Asuransi</td>
                      <td align="center" rowspan="2">Karyawan</td>
                      <td align="center" rowspan="2">Piutang Umum</td>
                      <td align="center" rowspan="2">Kurang Bayar</td>
                      <td align="center" rowspan="2">Diskon</td>
                      <td align="center" rowspan="2">Deposit Posting</td>
                      <td align="center" rowspan="2">Jumlah Netto</td>
                    </tr>
                    <tr>
                      <td class="column-title">Tunai</td>
                      <td class="column-title">Bank</td>
                    </tr>
                  </thead>
                  <?php 
                  $FixTotalDepositPosting = $PemakaianDeposit + $PemakaianDepositNon + $ReturDeposit;
                 // echo $PemakaianDeposit .", ". $PemakaianDepositNon .", ". $ReturDeposit.", ".$totalHistoryDeposit;
                  if ($_POST['layanan'] == '--') {
                    $FixTotalTunaiGlobal = $dataPenerimaanTunai['total_tunai'] + $dataPenjualanTunai['total_pembayaran'] ;
                    $FixTotalBankGlobal = $dataPenerimaanBank['total_bank'] + $dataPenjualanNonTunai['total_pembayaran'] + $dataDepositNonTunai['total_deposit'];
                    $JumlahBaris = count($data) + $TotalJumlahPenjualan;
                  }elseif ($_POST['layanan'] == 'A' || $_POST['layanan'] == 'I' || $_POST['layanan'] == 'G') {
                    $FixTotalTunaiGlobal = $dataPenerimaanTunai['total_tunai'];
                    $FixTotalBankGlobal = $dataPenerimaanBank['total_bank'];
                    $JumlahBaris = count($data);
                  }elseif ($_POST['layanan'] == 'Lain') {
                    $JumlahBaris = $TotalJumlahPenjualan;
                    $FixTotalTunaiGlobal = $dataPenjualanTunai['total_pembayaran'];
                  }
                  $FixTotalBPJSGlobal = $dataPenerimaanBPJS['total_bpjs'];
                  $FixTotalAsuransiGlobal = $dataPenerimaanAsuransi['total_asuransi'];
                  $FixTotalKaryawanGlobal = $dataPenerimaanKaryawan['total_karyawan'];
                  $FixTotalDiskon = $totalDiskonGlobal + $SumDiskon+$totalDiskon;
                $FixTotalNetto = $FixTotalTunaiGlobal + $FixTotalBankGlobal + $FixTotalBPJSGlobal + $FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal + $FixTotalPiutangUmumGlobal + $FixTotalKurangGlobal + $FixTotalDiskon + $FixTotalDepositPosting;
                // echo $FixTotalNetto;
                // echo  $FixTotalTunaiGlobal ."+". $FixTotalBankGlobal."+".  $FixTotalBPJSGlobal."+". $FixTotalAsuransiGlobal ."+". $FixTotalKaryawanGlobal ."+".  $FixTotalPiutangUmumGlobal ."+".  $FixTotalKurangGlobal ."+".  $FixTotalDiskon ."+".  $FixTotalDepositPosting."+". $totalHistoryDeposit;



                  if ($_POST['layanan'] != 'Disc' && $_POST['layanan'] != 'DP') {


                    if (!$_POST["cust_usr_jenis"])  $_POST["cust_usr_jenis"] = "0";
//  if(!$_POST["pembayaran_det_flag"]) $_POST["pembayaran_det_flag"]='T';

                    $perusahaan = $_POST["ush_id"];
                    $kasir = $_POST["usr_id"];

//$sql_where[] = "reg_tanggal is not null"; 
                    $sql_wherebank[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $depId);
                    if ($_POST["tgl_awal"]) $sql_wherebank[] = "j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"])." 00:00:00");
                    if ($_POST["tgl_akhir"]) $sql_wherebank[] = "j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"])." 23:59:59)");
                    if ($_POST["js_biaya"]) $sql_wherebank[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_POST["js_biaya"]);
// if ($_POST["jbayar"] != '-') $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
                    if ($_POST['jbayar'] == '2') {
                      $sql_wherebank[] = "j.id_jbayar in(select jbayar_id from global.global_jenis_bayar)";
                    }elseif ($_POST['jbayar'] == '5') {
                      $sql_wherebank[] = "j.id_jbayar = 'BPJS'";
                    }elseif ($_POSt['jbayar'] == '7') {
                      $sql_where[] = "j.id_jbayar = 'Karyawan'";
                    }elseif ($_POST['jbayar'] == '20') {
                      $sql_wherebank[] = "j.id_jbayar in(select perusahaan_id from global.global_perusahaan)";  
                    }
                    if ($_POST["id_dokter"]) $sql_wherebank[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_POST["id_dokter"]);

                    if ($_POST["reg_shift"]) {

                      $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_POST["reg_shift"]);
                      $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
                      $dataShiftPost = $dtaccess->Fetch($rs);

                      $sql_wherebank[] = " j.pembayaran_det_create>= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]) . " " . $dataShiftPost["shift_jam_awal"]) . " and j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]) . " " . $dataShiftPost["shift_jam_akhir"]);
                    }

                    if ($_POST["reg_tipe_layanan"]) {
                      $sql_wherebank[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_POST["reg_tipe_layanan"]);
                    }

                    if ($_POST["cust_usr_kode"]) {
                      $sql_wherebank[] = "c.cust_usr_kode =" . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
                    }

                    if ($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"] != "0") {
                      $sql_wherebank[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_jenis"]);
                    }

                    if ($_POST["id_perusahaan"]) {
                      $sql_wherebank[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["id_perusahaan"]);
                    }

                    if ($_POST["id_poli"] != '--') {
                      $sql_wherebank[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
                    }

                    if ($_POST["usr_id"] <> "--") {
                      $sql_wherebank[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                    }

//  if($_POST["pembayaran_det_flag"]<>"--"){
//     $sql_where[] = "j.pembayaran_det_flag = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_det_flag"]);
//     }      

                    if ($_POST["layanan"] <> "--") {
                      if ($_POST["layanan"] == "A") {
                        $sql_wherebank[] = "reg_tipe_rawat = 'J'";
                      } elseif ($_POST["layanan"] == "I") {
                        $sql_wherebank[] = "reg_tipe_rawat = 'I'";
                      } else {
                        $sql_wherebank[] = "reg_tipe_rawat = 'G'";
                      }
                    } 

                    $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_POST["reg_shift"]);
                    $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
                    $dataShiftPost = $dtaccess->Fetch($rs);
                    $sql_wherebank = implode(" and ", $sql_wherebank);
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
                    $sql .= " where 1=1 and j.id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01' and jbayar_id <> 'x') and " . $sql_wherebank;
                              //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
                    $sql .= " group by jbayar_nama";
                              // $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
                    // echo $sql;
                    $dataBank = $dtaccess->FetchAll($sql);


                    $sql = "select sum(pembayaran_det_total) as total_pembayaran, jbayar_nama from kasir.kasir_penjualan a
                    left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                    left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                    left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                    left join global.global_jenis_bayar c on c.jbayar_id = b.id_jbayar
                    where b.id_jbayar != '01'and jbayar_id <> 'x' and b.id_jbayar <> 'Disc' ";
                    if ($_POST['tgl_awal']) $sql .= " and date(a.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal']);
                    if ($_POST['tgl_akhir']) $sql .= " and date(a.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir']);
                    if ($_POST["usr_id"]  <> "--") $sql .= " and p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                    $sql .= " group by jbayar_nama";
                    //echo $sql;
                    $dataPenjualanBank = $dtaccess->FetchAll($sql);
                  }
                              // echo $sql;
                  
                  
                  ?>
                  <tbody>
                    <tr>
                      <td align="center" width="">TOTAL ( <?php echo $JumlahBaris; ?> )</td>
                      <td align="right"><?= ($_POST['btnExcel']) ? currency_format($FixTotalTunaiGlobal) : currency_format($FixTotalTunaiGlobal); ?></td>
                      <td align="right"> 
                       <table>
                        <?php if ($_POST['layanan'] == 'A' || $_POST['layanan'] == 'I' || $_POST['layanan'] == 'G' || $_POST['layanan'] == '--') { ?>
                          <?php for ($i = 0; $i < count($dataBank); $i++) { ?>
                            <tr>
                              <td><?php echo $dataBank[$i]['jbayar_nama'] ?></td>
                              <td> : </td>
                              <td align="right">
                                <?php echo currency_format($dataBank[$i]['total']) ?>
                              </td>
                            </tr>
                          <?php } ?>
                        <?php } ?>

                        <?php if ($_POST['layanan'] == 'Lain' || $_POST['layanan'] == '--') { ?>
                          <?php for ($i = 0; $i < count($dataPenjualanBank); $i++) { ?>
                            <tr>
                              <td><?php echo $dataPenjualanBank[$i]['jbayar_nama']." Lain-lain" ?></td>
                              <td> : </td>
                              <td align="right"><?php echo currency_format($dataPenjualanBank[$i]['total_pembayaran']) ?></td>
                            </tr>
                          <?php } ?>
                        <?php } ?>

                        <?php if ($_POST['layanan'] == 'DP' || $_POST['layanan'] == '--'  && $DepositMasukNon > 0) { ?>
                          <tr>
                            <td align="right"><?php echo currency_format($DepositMasukNon); ?></td>
                          </tr>
                        <?php } ?>
                      </table>
                      <!-- <?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBankGlobal)) : currency_format($FixTotalBankGlobal); ?> -->
                    </td>
                    <td align="right"><?= ($_POST['btnExcel']) ?  currency_format($FixTotalBPJSGlobal) : currency_format($FixTotalBPJSGlobal); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ?currency_format($FixTotalAsuransiGlobal) : currency_format($FixTotalAsuransiGlobal); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ?currency_format($FixTotalKaryawanGlobal) : currency_format($FixTotalKaryawanGlobal); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ?  currency_format($TotalPiutangUmum['piutang_umum']) : currency_format($TotalPiutangUmum['piutang_umum']); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ?  currency_format($TotalKurangBayar['kurang']) : currency_format($TotalKurangBayar['kurang']); ?></td>

                    <td align="right"><?= ($_POST['btnExcel']) ?  currency_format($FixTotalDiskon) : currency_format($FixTotalDiskon); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ?  currency_format($FixTotalDepositPosting): currency_format($FixTotalDepositPosting); ?></td>
                    <td align="right"><?= ($_POST['btnExcel']) ? currency_format($FixTotalNetto) : currency_format($FixTotalNetto); ?></td>
                  </tr>
                </tbody>
              </table>
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