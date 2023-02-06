<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $thisPage = "report_setoran_cicilan.php";
     $printPage = "report_setoran_cicilan_cetak.php?";
    
   //  if (!$_GET["klinik"]) $_GET["klinik"]=$depId;
       //$_GET["klinik"] = $_GET["klinik"]; 
       
     if($_GET["klinik"]) { $_GET["klinik"]=$_GET["klinik"]; }
      else if(!$_GET["klinik"]) { $_GET["klinik"]=$depId; }
      
    

 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_GET["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_GET["dep_id"] = $konfigurasi["dep_id"];
     $_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
          
   $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

$skr = date("d-m-Y");
$time = date("H:i:s");

if (!$_GET['tgl_awal']) {
  $_GET['tgl_awal']  = $skr;
}
if (!$_GET['tgl_akhir']) {
  $_GET['tgl_akhir']  = $skr;
}

//cari shift
$sql = "select * from global.global_shift order by shift_id";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataShift = $dtaccess->FetchAll($rs);

if (!$_GET["cust_usr_jenis"])  $_GET["cust_usr_jenis"] = "0";
if (!$_GET["pembayaran_det_flag"]) $_GET["pembayaran_det_flag"] = 'T';

$perusahaan = $_GET["perusahaan"];
$kasir = $_GET["usr_id"];

//$sql_where[] = "reg_tanggal is not null"; 
if ($_GET["tgl_awal"]) $sql_where[] = "date(a.pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
if ($_GET["tgl_akhir"]) $sql_where[] = "date(a.pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));

if ($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"] != "0") {
  $sql_where[] = "c.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_jenis"]);
}

if ($_GET["id_poli"] != '--') {
  $sql_where[] = "e.poli_id = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
}

if ($_GET["usr_id"] <> "--") {
  $sql_where[] = "a.who_when_update = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
}

// if ($_GET["pembayaran_det_flag"] <> "--") {
//   $sql_where[] = "a.pembayaran_det_flag = " . QuoteValue(DPE_CHAR, $_GET["pembayaran_det_flag"]);
// }

if ($_GET['layanan'] <> "--") {
  if ($_GET['layanan'] == "A") {
    $sql_where[] = "c.reg_tipe_rawat = 'J'";
  } elseif ($_GET['layanan'] == "I") {
    $sql_where[] = "c.reg_tipe_rawat = 'I'";
  } elseif ($_GET['layanan'] == "Lain") {
    $sql_where[] = "c.reg_tipe_rawat = 'Lain'";
  } else {
    $sql_where[] = "c.reg_tipe_rawat = 'G'";
  }
}

if ($_GET['cust_usr_nama']) $sql_where[] = "d.cust_usr_nama like '%".strtoupper($_GET['cust_usr_nama'])."%'";
if ($_GET['cust_usr_kode']) $sql_where[] = "d.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_GET['cust_usr_kode']);


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
     where date(deposit_history_when_create) >= '" . date_format(date_create($_GET['tgl_awal']), 'Y-m-d') . "' 
     and date(deposit_history_when_create) <= '" . date_format(date_create($_GET['tgl_akhir']), 'Y-m-d') . "'
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
    where date(deposit_history_when_create) >= '" . date_format(date_create($_GET['tgl_awal']), 'Y-m-d') . "' 
     and date(deposit_history_when_create) <= '" . date_format(date_create($_GET['tgl_akhir']), 'Y-m-d') . "'
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


    $data = array();

    foreach ($dataTable as $key => $value) {
      $sql = "SELECT SUM(pembayaran_det_dibayar) AS total_pembayaran FROM klinik.klinik_pembayaran_det a LEFT JOIN global.global_jenis_bayar b ON a.id_jbayar = b.jbayar_id WHERE pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR, $value['pembayaran_det_kwitansi'])." AND a.id_pembayaran = ".QuoteValue(DPE_CHAR, $value['pembayaran_id']);
      $TotalPembayaran = $dtaccess->Fetch($sql);

      $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$value['pembayaran_id'])." and id_jbayar = 'Disc'";
      $PembayaranDisc = $dtaccess->Fetch($sql);

      $sql = "SELECT sum(pembayaran_det_dibayar) as diskon from klinik.klinik_pembayaran_det a 
      left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar 
      where id_pembayaran = ".QuoteValue(DPE_CHAR,$value['pembayaran_id'])." and 
      date(pembayaran_det_create) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"])) . " and 
      date(pembayaran_det_create) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"])) . " and 
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
if ($_GET["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=lap_detail_penerimaan_irj.xls');
}

if ($_GET["btnCetak"]) {
  //echo $_GET["ush_id"];
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
} else if ($_GET["klinik"]) {
  //Data Klinik
  $sql = "select * from global.global_departemen where dep_id = '" . $_GET["klinik"] . "' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
} else {
  $sql = "select * from global.global_departemen where dep_id = '" . $depId . "' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

if ($_GET["dep_logo"]) $fotoName = $lokasi . "/" . $row_edit["dep_logo"];
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
   <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <div class="clearfix"></div>
              </div>
              <?php if ($_GET['layanan'] == 'A' || $_GET['layanan']  == 'I' || $_GET['layanan']  == 'G' || $_GET['layanan']  == '--') { ?>
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
                where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal']).' '.$_GET['waktu_awal']) . " 
                and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir']).' '.$_GET['waktu_akhir']) . "
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

                  $sql = "select sum(deposit_history_nominal) as total_deposit from klinik.klinik_deposit_history a 
                  left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                  where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal'])) . " 
                  and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir'])) . "
                  and deposit_history_nominal <> '0' and (deposit_history_flag = 'M' or deposit_history_flag = 'R') and id_jbayar = '01'";
                  if ($_GET["usr_id"] <> "--") {
                    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
                  }
                  if ($_GET["cust_usr_kode"]<>'') {
                    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
                  }
                  $dataDepositTunai = $dtaccess->Fetch($sql);

                  $sql = "select sum(deposit_history_nominal) as total_deposit from klinik.klinik_deposit_history a 
                  left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                  where date(deposit_history_when_create) >= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_awal'])) . " 
                  and date(deposit_history_when_create) <= " . QuoteValue(DPE_DATE, date_db($_GET['tgl_akhir'])) . "
                  and deposit_history_nominal <> '0' and (deposit_history_flag = 'M' or deposit_history_flag = 'R') and id_jbayar != '01'";
                  if ($_GET["usr_id"] <> "--") {
                    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
                  }
                  if ($_GET["cust_usr_kode"]<>'') {
                    $sql .= "and cust_usr_kode = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
                  }
                  $dataDepositNonTunai = $dtaccess->Fetch($sql);
                  ?>
                  <?php if (count($dataDeposit) > 0) : ?>
                    <?php if ($_GET['layanan'] == 'DP' || $_GET['layanan'] == '--') { ?>
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

                                  echo ($_GET['btnExcel']) ? str_replace("-", "", currency_format($dataDeposit[$i]['deposit_history_nominal'])) : str_replace("-", "", currency_format($dataDeposit[$i]['deposit_history_nominal']));
                                  $SumDepositDebet += $dataDeposit[$i]['deposit_history_nominal'];
                                } else {
                                  echo "(" .  str_replace("-", "", currency_format($dataDeposit[$i]['deposit_history_nominal'])) . ")";
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
                            echo ($_GET['btnExcel']) ? currency_format($totalHistoryDeposit) : str_replace("-", "", currency_format($totalHistoryDeposit)) ?></b></td>
                          </tr>
                        </tfoot>
                      </table>
                    <?php } ?>
                  <?php endif; ?>
                 
                <?php } ?>
                <?php 
                if ($_GET["tgl_awal"]) $sql_where2[] = "date(a.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
                if ($_GET["tgl_akhir"]) $sql_where2[] = "date(a.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));
                if ($_GET["usr_id"]  <> "--") $sql_where2[] = "p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
                    // echo $_GET["usr_id"];
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
                  <?php if (($_GET['layanan'] == 'Lain' || $_GET['layanan'] == '--') && $_GET['cust_usr_jenis'] != '7' && $_GET['cust_usr_kode'] == '' && $_GET['id_poli'] == '--') { 
                    
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
                              <td><?php echo ($_GET['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_total"]) : currency_format($dataPenjualan[$i]["penjualan_total"]); ?></td>
                              <td><?php echo  $dataPenjualan[$i]["usr_name"]; ?></td>
                              <td><?php echo ($_GET['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_diskon"]) : currency_format($dataPenjualan[$i]["penjualan_diskon"]) ?></td>
                              <td align="right"><?php $total_penjualan_kasir = $total_penjualan_kasir + $dataPenjualan[$i]["penjualan_total"];
                              echo ($_GET['btnExcel']) ?  currency_format($dataPenjualan[$i]["penjualan_total"]) : currency_format($dataPenjualan[$i]["penjualan_total"]) ?></td>
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
                            <td><?php echo ($_GET['btnExcel']) ?currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"]) : currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"]); ?></td>
                            <td><?php echo ($_GET['btnExcel']) ? currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"]) : currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"]); ?></td>
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
                          <td align="right"><?= ($_GET['btnExcel']) ? currency_format($total_penjualan_kasir) : currency_format($total_penjualan_kasir) ?></td>
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
                  if ($_GET['layanan'] == '--') {
                    $FixTotalTunaiGlobal =$dataPenerimaanTunai['total_tunai'] + $dataPenjualanTunai['total_pembayaran'];
                       // echo $dataPenerimaanTunai['total_tunai'].' + '.$dataPenjualanTunai['total_pembayaran'].' + '.$FixTotalDepositPosting;
                    $FixTotalBankGlobal = $dataPenerimaanBank['total_bank'] + $dataPenjualanNonTunai['total_pembayaran'] + $dataDepositNonTunai['total_deposit'];
                    $JumlahBaris = count($data) + $TotalJumlahPenjualan;
                  }elseif ($_GET['layanan']  == 'A' ||$_GET['layanan']  == 'I' || $_GET['layanan']  == 'G') {
                    $FixTotalTunaiGlobal = $dataPenerimaanTunai['total_tunai'];
                    $FixTotalBankGlobal = $dataPenerimaanBank['total_bank'];
                    $JumlahBaris = count($data);
                  }elseif ($_GET['layanan']  == 'Lain') {
                    $JumlahBaris = $TotalJumlahPenjualan;
                    $FixTotalTunaiGlobal = $dataPenjualanTunai['total_pembayaran'];
                  }
                  $FixTotalBPJSGlobal = $dataPenerimaanBPJS['total_bpjs'];
                  $FixTotalAsuransiGlobal = $dataPenerimaanAsuransi['total_asuransi'];
                  $FixTotalKaryawanGlobal = $dataPenerimaanKaryawan['total_karyawan'];
                  $FixTotalDiskon = $totalDiskonGlobal + $SumDiskon+$totalDiskon;
                  // $FixTotalNetto = $FixTotalTunaiGlobal + $FixTotalBankGlobal + $FixTotalBPJSGlobal + $FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal+ $FixTotalDiskon+$FixTotalDepositPosting;
                  $FixTotalNetto = $FixTotalTunaiGlobal + $FixTotalBankGlobal + $FixTotalBPJSGlobal + $FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal + $FixTotalPiutangUmumGlobal + $FixTotalKurangGlobal + $FixTotalDiskon+$FixTotalDepositPosting;
               // echo   $FixTotalTunaiGlobal .", ". $FixTotalBankGlobal .", ". $FixTotalBPJSGlobal .", ". $FixTotalAsuransiGlobal .", ".$FixTotalKaryawanGlobal .", ". $FixTotalPiutangUmumGlobal .", ". $FixTotalKurangGlobal .", ". $FixTotalDiskon .", ". $FixTotalDepositPosting


                  if ($_GET['layanan'] != 'Disc' && $_GET['layanan'] != 'DP') {


                    if (!$_GET["cust_usr_jenis"])  $_GET["cust_usr_jenis"] = "0";
//  if(!$_GET["pembayaran_det_flag"]) $_GET["pembayaran_det_flag"]='T';

                    $perusahaan = $_GET["perusahaan"];
                    $kasir = $_GET["usr_id"];

//$sql_where[] = "reg_tanggal is not null"; 
                    $sql_wherebank[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $depId);
                    if ($_GET["tgl_awal"]) $sql_wherebank[] = "j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"])." 00:00:00");
                    if ($_GET["tgl_akhir"]) $sql_wherebank[] = "j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"])." 23:59:59)");
                    if ($_GET["js_biaya"]) $sql_wherebank[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_GET["js_biaya"]);
// if ($_GET["jbayar"] != '-') $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_GET["jbayar"]);
                    if ($_GET['jbayar'] == '2') {
                      $sql_wherebank[] = "j.id_jbayar in(select jbayar_id from global.global_jenis_bayar)";
                    }elseif ($_GET['jbayar'] == '5') {
                      $sql_wherebank[] = "j.id_jbayar = 'BPJS'";
                    }elseif ($_GET['jbayar'] == '7') {
                      $sql_where[] = "j.id_jbayar = 'Karyawan'";
                    }elseif ($_GET['jbayar'] == '20') {
                      $sql_wherebank[] = "j.id_jbayar in(select perusahaan_id from global.global_perusahaan)";  
                    }
                    if ($_GET["id_dokter"]) $sql_wherebank[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_GET["id_dokter"]);

                    if ($_GET["reg_shift"]) {

                      $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_GET["reg_shift"]);
                      $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
                      $dataShiftPost = $dtaccess->Fetch($rs);

                      $sql_wherebank[] = " j.pembayaran_det_create>= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]) . " " . $dataShiftPost["shift_jam_awal"]) . " and j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]) . " " . $dataShiftPost["shift_jam_akhir"]);
                    }

                    if ($_GET['layanan'] != "--") {
                      $sql_wherebank[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_GET['layanan']);
                    }

                    if ($_GET["cust_usr_kode"]) {
                      $sql_wherebank[] = "c.cust_usr_kode =" . QuoteValue(DPE_CHAR, $_GET["cust_usr_kode"]);
                    }

                    if ($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"] != "0") {
                      $sql_wherebank[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_jenis"]);
                    }

                    if ($_GET["id_perusahaan"]) {
                      $sql_wherebank[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_GET["id_perusahaan"]);
                    }

                    if ($_GET["id_poli"] != '--') {
                      $sql_wherebank[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
                    }

                    if ($_GET["usr_id"] <> "--") {
                      $sql_wherebank[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
                    }

//  if($_GET["pembayaran_det_flag"]<>"--"){
//     $sql_where[] = "j.pembayaran_det_flag = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_flag"]);
//     }      

                    if ($_GET['layanan'] <> "--") {
                      if ($_GET['layanan'] == "A") {
                        $sql_wherebank[] = "reg_tipe_rawat = 'J'";
                      } elseif ($_GET['layanan'] == "I") {
                        $sql_wherebank[] = "reg_tipe_rawat = 'I'";
                      } else {
                        $sql_wherebank[] = "reg_tipe_rawat = 'G'";
                      }
                    } 

                    $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_GET["reg_shift"]);
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
                              
                    $sql .= " group by jbayar_nama";
                              
                    // echo $sql;
                    $dataBank = $dtaccess->FetchAll($sql);


                    $sql = "select sum(pembayaran_det_total) as total_pembayaran, jbayar_nama from kasir.kasir_penjualan a
                    left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                    left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                    left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                    left join global.global_jenis_bayar c on c.jbayar_id = b.id_jbayar
                    where b.id_jbayar != '01'and jbayar_id <> 'x' and b.id_jbayar <> 'Disc' ";
                    if ($_GET['tgl_awal']) $sql .= " and date(a.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]).' '.$_GET['waktu_awal']);
                    if ($_GET['tgl_akhir']) $sql .= " and date(a.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]).' '.$_GET['waktu_akhir']);
                    if ($_GET["usr_id"]  <> "--") $sql .= " and p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
                    $sql .= " group by jbayar_nama";
                    //echo $sql;
                    $dataPenjualanBank = $dtaccess->FetchAll($sql);
                  }
                              // echo $sql;
                  
                  
                  ?>
                  <tbody>
                    <tr>
                      <td align="center" width="">TOTAL ( <?php echo $JumlahBaris; ?> )</td>
                      <td align="right"><?= ($_GET['btnExcel']) ? currency_format($FixTotalTunaiGlobal) : currency_format($FixTotalTunaiGlobal); ?></td>
                      <td align="right"> 
                       <table>
                        <?php if ($_GET['layanan'] == 'A' || $_GET['layanan'] == 'I' || $_GET['layanan'] == 'G' || $_GET['layanan'] == '--') { ?>
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

                        <?php if ($_GET['layanan'] == 'Lain' || $_GET['layanan'] == '--') { ?>
                          <?php for ($i = 0; $i < count($dataPenjualanBank); $i++) { ?>
                            <tr>
                              <td><?php echo $dataPenjualanBank[$i]['jbayar_nama']." Lain-lain" ?></td>
                              <td> : </td>
                              <td align="right"><?php echo currency_format($dataPenjualanBank[$i]['total_pembayaran']) ?></td>
                            </tr>
                          <?php } ?>
                        <?php } ?>

                        <?php if ($_GET['layanan'] == 'DP' || $_GET['layanan'] == '--'  && $DepositMasukNon > 0) { ?>
                          <tr>
                            <td align="right"><?php echo currency_format($DepositMasukNon); ?></td>
                          </tr>
                        <?php } ?>
                      </table>
                      <!-- <?= ($_GET['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBankGlobal)) : currency_format($FixTotalBankGlobal); ?> -->
                    </td>
                    <td align="right"><?= ($_GET['btnExcel']) ?  currency_format($FixTotalBPJSGlobal) : currency_format($FixTotalBPJSGlobal); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ?currency_format($FixTotalAsuransiGlobal) : currency_format($FixTotalAsuransiGlobal); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ?currency_format($FixTotalKaryawanGlobal) : currency_format($FixTotalKaryawanGlobal); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ?  currency_format($TotalPiutangUmum['piutang_umum']) : currency_format($TotalPiutangUmum['piutang_umum']); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ?  currency_format($TotalKurangBayar['kurang']) : currency_format($TotalKurangBayar['kurang']); ?></td>

                    <td align="right"><?= ($_GET['btnExcel']) ?  currency_format($FixTotalDiskon) : currency_format($FixTotalDiskon); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ?  currency_format($FixTotalDepositPosting): currency_format($FixTotalDepositPosting); ?></td>
                    <td align="right"><?= ($_GET['btnExcel']) ? currency_format($FixTotalNetto) : currency_format($FixTotalNetto); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          
        </div>
      </div>

