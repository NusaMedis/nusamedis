<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");


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
$enc = new textEncrypt();

//if (!$_POST["klinik"]) $_POST["klinik"]=$depId;


if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}


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
//  if(!$_POST["pembayaran_det_flag"]) $_POST["pembayaran_det_flag"]='T';

$perusahaan = $_POST["ush_id"];
$kasir = $_POST["usr_id"];

//$sql_where[] = "reg_tanggal is not null"; 
$sql_where[] = "j.id_dep = " . QuoteValue(DPE_CHAR, $depId);
if ($_POST["tgl_awal"]) $sql_where[] = "j.pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal']);
if ($_POST["tgl_akhir"]) $sql_where[] = "j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir']);
if ($_POST["js_biaya"]) $sql_where[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_POST["js_biaya"]);
// if ($_POST["jbayar"] != '-') $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
if ($_POST['jbayar'] == '2') {
  $sql_where[] = "j.id_jbayar in(select jbayar_id from global.global_jenis_bayar)";
}elseif ($_POST['jbayar'] == '5') {
  $sql_where[] = "j.id_jbayar = 'BPJS'";
}elseif ($_POSt['jbayar'] == '7') {
  $sql_where[] = "j.id_jbayar = 'Karyawan'";
}elseif ($_POST['jbayar'] == '20') {
  $sql_where[] = "j.id_jbayar in(select perusahaan_id from global.global_perusahaan)";  
}
if ($_POST["id_dokter"]) $sql_where[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_POST["id_dokter"]);

if ($_POST["reg_shift"]) {

  $sql = "select * from global.global_shift where shift_id=" . QuoteValue(DPE_CHAR, $_POST["reg_shift"]);
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $dataShiftPost = $dtaccess->Fetch($rs);

  $sql_where[] = " j.pembayaran_det_create>= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]) . " " . $dataShiftPost["shift_jam_awal"]) . " and j.pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]) . " " . $dataShiftPost["shift_jam_akhir"]);
}

if ($_POST["reg_tipe_layanan"]) {
  $sql_where[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_POST["reg_tipe_layanan"]);
}

if ($_POST["cust_usr_kode"]) {
  $sql_where[] = "c.cust_usr_kode =" . QuoteValue(DPE_CHAR, $_POST["cust_usr_kode"]);
}

if ($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"] != "0") {
  $sql_where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_jenis"]);
}

if ($_POST["id_perusahaan"]) {
  $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["id_perusahaan"]);
}

if ($_POST["id_poli"] != '--') {
  $sql_where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
}

if ($_POST["usr_id"] <> "--") {
  $sql_where[] = "j.who_when_update = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
}

//  if($_POST["pembayaran_det_flag"]<>"--"){
//     $sql_where[] = "j.pembayaran_det_flag = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_det_flag"]);
//     }      

if ($_POST["layanan"] <> "--") {
  if ($_POST["layanan"] == "A") {
    $sql_where[] = "reg_tipe_rawat = 'J'";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "reg_tipe_rawat = 'I'";
  } else {
    $sql_where[] = "reg_tipe_rawat = 'G'";
  }
}


/*if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
       $sql_where[] = "a.pembayaran_who_create = '".$userName."'";
     }*/

//  get data dari tiap kasir 
     function getDataTable($sql_where, $usr_name)
     {
      $dtaccess = new DataAccess();
      $sql_where = implode(" and ", $sql_where);
      $sql = "select j.id_reg, d.reg_kode_trans, d.reg_tanggal_pulang, a.pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama, pembayaran_det_create, l.poli_nama as poli_asal 
      from klinik.klinik_pembayaran_det j 
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
      $sql .= " where  j.who_when_update='" . $usr_name . "' and  " . $sql_where;
      $sql .= " group by pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama, d.reg_kode_trans, j.id_reg, d.reg_tanggal_pulang, pembayaran_det_create, l.poli_nama";
      $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
  // echo $sql;
      return $dataTable = $dtaccess->FetchAll($sql);
    }

    //  get data dari tiap kasir Diskon 100%
    function getDataTableDiskon($sql_where, $usr_name)
    {
      $dtaccess = new DataAccess();
      $sql_where = implode(" and ", $sql_where);
      $sql = "select j.id_reg, d.reg_kode_trans, d.reg_tanggal_pulang, a.pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama, pembayaran_det_create, l.poli_nama as poli_asal 
      from klinik.klinik_pembayaran_det j 
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
      $sql .= " where  j.who_when_update='" . $usr_name . "' and j.id_jbayar = 'Diskon' and  " . $sql_where;
  //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
      $sql .= " group by pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama, d.reg_kode_trans, j.id_reg, d.reg_tanggal_pulang, pembayaran_det_create, l.poli_nama";
      $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
  // echo $sql;
      return $dataTable = $dtaccess->FetchAll($sql);
    }

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



    if ($_POST["tgl_awal"]) $sql_where2[] = "pembayaran_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal']);
    if ($_POST["tgl_akhir"]) $sql_where2[] = "pembayaran_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir']);
    if ($_POST["usr_id"]  <> "--") $sql_where2[] = "p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
// echo $_POST["usr_id"];
// exit();

    function getDataTable2($sql_where2, $usr_name)
    {
      $dtaccess = new DataAccess();
      $sql_where2 = implode(" and ", $sql_where2);
      $sql = "select penjualan_nomor, pembayaran_create, cust_usr_nama, pembayaran_id, pembayaran_who_create
      from kasir.kasir_penjualan a 
      left join global.global_auth_user e on e.usr_id = a.who_update
      left join global.global_departemen f on f.dep_id = a.id_dep
      left join kasir.kasir_data_pembeli h on a.id_reg = h.reg_id
      left join kasir.kasir_pembayaran p on p.id_reg=h.reg_id
      left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id";
      $sql .= " where  p.pembayaran_yg_dibayar!='0' and " . $sql_where2;
      $sql .= " and pembayaran_who_create = '". $usr_name ."'";
      $sql .= " group by penjualan_nomor, pembayaran_create, cust_usr_nama, pembayaran_id, pembayaran_who_create";
      $sql .= " order by pembayaran_create desc";
// $rs = $dtaccess->Execute($sql);
// $dataPenjualan = $dtaccess->FetchAll($rs);
      return $dataPenjualan = $dtaccess->FetchAll($sql);

      $sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
      left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
      left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
      left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
      where q.id_jbayar = '01' and ". $sql_where2;
// $dataPenjualanTunai = $dtaccess->Fetch($sql);
      $dataPenjualanTunai = $dtaccess->FetchAll($sql);

      $sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
      left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
      left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
      left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
      where q.id_jbayar != '01' and q.id_jbayar <> 'Disc' and ". $sql_where2;
      $sql .= " and pembayaran_who_create = '". $usr_name ."'";
// $dataPenjualanNonTunai = $dtaccess->Fetch($sql);
      return $dataPenjualanNonTunai = $dtaccess->FetchAll($sql);

      $sql = "select sum(pembayaran_det_total) as total_pembayaran from kasir.kasir_penjualan a
      left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
      left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
      left join kasir.kasir_pembayaran_det q on q.id_pembayaran = p.pembayaran_id
      where q.id_jbayar = 'Disc' and ". $sql_where2;
      $sql .= " and pembayaran_who_create = '". $usr_name ."'";
// $dataPenjualanDisc = $dtaccess->Fetch($sql);
      return $dataPenjualanDisc = $dtaccess->FetchAll($sql);
    }


//Piutang
    $sql = "select * from ar_ap.ar_payment a
    left join ar_ap.ar_trans b on b.ar_trans_id = a.id_ar_trans
    left join global.global_customer_user c on c.cust_usr_id = b.id_cust_usr
    where id_jbayar = '01' and ar_payment_when_update >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal']);
    $sql .= " and ar_payment_when_update <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir']);
    if ($_POST['usr_id'] <> '--') $sql .= " and ar_payment_who_update = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
    if ($_POST['cust_usr_kode'] != '') $sql .= " and cust_usr_kode = ".QuoteValue(DPE_CHAR, $_POST['cust_usr_kode']);
    $sql .= " order by ar_payment_when_update asc";
    $dataPelunasanPiutang = $dtaccess->FetchAll($sql);


    $tableHeader = "Lap. Penerimaan";
    if ($_POST["btnExcel"]) {
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment; filename=lap_penerimaan_irj.xls');
      echo "<h2>Laporan Penerimaan</h2><br>";
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

// cari nama perusahaan --
    $sql = "select * from global.global_perusahaan";
    $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
    $NamaPerusahaan = $dtaccess->FetchAll($rs);

//ambil nama dokter e
    $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like " . QuoteValue(DPE_CHAR, "%" . $_POST["klinik"]) . " order by usr_name asc ";
    $rs = $dtaccess->Execute($sql);
    $dataDokter = $dtaccess->FetchAll($rs);

    $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);


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
    $sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1' or id_rol='35')";
    if ($_POST['usr_id'] != '--') $sql .= "and usr_name = ".QuoteValue(DPE_CHAR,$_POST['usr_id']);
    $sql .= " order by usr_name asc";
    $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
    $dataKasir = $dtaccess->FetchAll($rs);

    $sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1' or id_rol = '35')";
    $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
    $dataKasir2 = $dtaccess->FetchAll($rs);


    ?>

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
                      <div class="x_title">
                        <h2>Laporan Penerimaan</h2>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">


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
                                <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Waktu (HH:mm:ss)</label>
                                <div class='input-group date' id='timepicker'>
                                  <input name="waktu_awal" type='text' class="form-control" value="<?php if ($_POST['waktu_awal']) {
                                    echo $_POST['waktu_awal'];
                                    } else {
                                      echo '00:00:00';
                                    } ?>" />
                                    <span class="input-group-addon">
                                      <span class="fa fa-calendar">
                                      </span>
                                    </span>
                                  </div>

                                  <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Waktu (HH:mm:ss)</label>
                                  <div class='input-group date' id='timepicker2'>
                                    <input name="waktu_akhir" type='text' class="form-control" value="<?php if ($_POST['waktu_akhir']) {
                                      echo $_POST['waktu_akhir'];
                                      } else {
                                        echo '23:59:59';
                                      } ?>" />
                                      <span class="input-group-addon">
                                        <span class="fa fa-calendar">
                                        </span>
                                      </span>
                                    </div>
                                  </div>

                                  <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12">No RM</label>
                                    <input name="cust_usr_kode" id="cust_usr_kode" type='text' class="form-control" value="<? echo $_POST['cust_usr_kode']; ?>" />
                                  </div>

                                  <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>

                                    <select class="select2_single form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                                      <option value="">[ Pilih Dokter ]</option>
                                      <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                                        <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"]; ?></option>
                                      <?php } ?>
                                    </select>

                                  </div>

                                  <div class="col-md-4 col-sm-6 col-xs-12">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Bayar</label>
                                    <?php if ($userData["rol"] != '2') { ?>
                                      <td width="20%" class="tablecontent">
                                      <?php } else { ?>
                                        <td width="20%" class="tablecontent">
                                        <?php } ?>
                                        <select name="jbayar" class="select2_single form-control" id="jbayar" onKeyDown="return tabOnEnter(this, event);">
                                          <option class="inputField" value="-">[ Pilih Jenis Bayar ]</option>
                                          <?php for ($i = 0, $n = count($jenisPasien); $i < $n; $i++) { ?>
                                            <option value="<?php echo $jenisPasien[$i]["jenis_id"]; ?>" <?php if ($jenisPasien[$i]["jenis_id"] == $_POST["jbayar"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"]; ?>');"><?php echo ($i + 1) . ". " . $jenisPasien[$i]["jenis_nama"]; ?></option>
                                          <?php } ?>
                                        </select>

                                      </div>


                                      <div class="col-md-4 col-sm-6 col-xs-12">
                                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
                                        <?php if ($userData["rol"] != '2') { ?>
                                          <td width="20%" class="tablecontent">
                                          <?php } else { ?>
                                            <td width="20%" class="tablecontent">
                                            <?php } ?>
                                            <select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                                              <!--onChange="this.form.submit();" -->
                                              <option value="0">[ Pilih Jenis Pasien ]</option>
                                              <?php for ($i = 0, $n = count($jenisPasien); $i < $n; $i++) { ?>
                                                <option value="<?php echo $jenisPasien[$i]["jenis_id"]; ?>" <?php if ($jenisPasien[$i]["jenis_id"] == $_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"]; ?>');"><?php echo ($i + 1) . ". " . $jenisPasien[$i]["jenis_nama"]; ?></option>
                                              <?php } ?>
                                            </select>

                                          </div>

                                          <div class="col-md-4 col-sm-6 col-xs-12">
                                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Perusahaan</label>
                                            <?php if ($userData["rol"] != '2') { ?>
                                              <td width="20%" class="tablecontent">
                                              <?php } else { ?>
                                                <td width="20%" class="tablecontent">
                                                <?php } ?>
                                                <select class="select2_single form-control" name="id_perusahaan" id="id_perusahaan" onKeyDown="return tabOnEnter(this, event);">
                                                  <option value="">[ Pilih Nama Perusahaan ]</option>
                                                  <?php for ($i = 0, $n = count($NamaPerusahaan); $i < $n; $i++) { ?>
                                                    <option value="<?php echo $NamaPerusahaan[$i]["perusahaan_id"]; ?>" <?php if ($NamaPerusahaan[$i]["perusahaan_id"] == $_POST["id_perusahaan"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $NamaPerusahaan[$i]["perusahaan_nama"]; ?></option>
                                                  <?php } ?>
                                                </select>

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
                                                  <option value="PP" <?php if ($_POST["layanan"] == 'PP') echo "selected"; ?>>Pelunasan Piutang</option>
                                                </select>

                                              </div>


                                              <div class="col-md-4 col-sm-6 col-xs-12">
                                                <label class="control-label col-md-12 col-sm-12 col-xs-12">Klinik</label>
                                                <select name="id_poli" class="select2_single form-control" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                                                  <option value="--">[ Pilih Klinik ]</option>
                                                  <?php for ($i = 0, $n = count($dataPoli); $i < $n; $i++) { ?>
                                                    <option value="<?php echo $dataPoli[$i]["poli_id"]; ?>" <?php if ($dataPoli[$i]["poli_id"] == $_POST["id_poli"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataPoli[$i]["poli_nama"]; ?></option>
                                                  <?php } ?>
                                                </select>
                                              </div>

                                              <div class="col-md-4 col-sm-6 col-xs-12">
                                                <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kasir</label>
                                                <select class="select2_single form-control" name="usr_id" onKeyDown="return tabOnEnter(this, event);">
                                                  <option value="--">[ Pilih Nama Kasir ]</option>
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

                                              <div class="col-md-4 col-sm-6 col-xs-12" hidden>
                                                <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Penerima</label>

                                                <select class="select2_single form-control" name="pembayaran_det_flag" id="pembayaran_det_flag" onKeyDown="return tabOnEnter(this, event);">
                                                  <!--<option value="--" >[ Semua Tipe Penerimaan ]</option> -->
                                                  <!-- <option value="T" <?php if ($_POST["pembayaran_det_flag"] == 'T') echo "selected"; ?>>Tunai</option> -->
                          <!--<option value="P" <?php if ($_POST["pembayaran_det_flag"] == 'P') echo "selected"; ?>>Piutang Perorangan</option>
                <option value="S" <?php if ($_POST["pembayaran_det_flag"] == 'S') echo "selected"; ?>>Subsidi</option>
                <option value="J" <?php if ($_POST["pembayaran_det_flag"] == 'J') echo "selected"; ?>>Jaminan/Asuransi</option>  -->
              </select>

            </div>



            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
              <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
              <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
              <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
            </div>
            <div class="clearfix"></div>
            <? if ($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]) { ?>
            <? } ?>
            <? if ($_x_mode == "Edit") { ?>
              <?php echo $view->RenderHidden("kategori_tindakan_id", "kategori_tindakan_id", $biayaId); ?>
            <? } ?>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- //row filter -->
<? } ?>

<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="x_panel">
    <div class="x_title">
      <div class="clearfix"></div>
    </div>
    <div class="x_content">


      <?php if ($_POST["btnLanjut"] || $_POST['btnExcel']) { ?>

        <?php
        $totalNettoGlobal = 0;
        $totalTunaiGlobal = 0;
        $totalBankGlobal = 0;
        $totalPerusahaanGlobal = 0;
        $totalKaryawanGlobal = 0;
        $totalJknGlobal = 0;
        $totalPiutangGlobal = 0;
        $totalPiutangUmumGlobal = 0;
                

        foreach ($dataKasir as $DK) {
          $sub_tunai = 0;
          $sub_bank = 0;
          $sub_bpjs = 0;
          $sub_asuransi = 0;
          $sub_karyawan = 0;
          $sub_kurang = 0;
          $sub_diskon_ = 0;
          $sub_netto = 0;
          $sub_piutang_umum = 0;
          if (count(getDataTableDiskon($sql_where, $DK['usr_name'])) != 0) {
            foreach (getDataTableDiskon($sql_where, $DK['usr_name']) as $dt) {
              $sql = "select sum(pembayaran_det_dibayar) as diskon from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi'])." and id_jbayar = 'Diskon'";
              
            }
          }
          if (count(getDataTable($sql_where, $DK['usr_name'])) != 0) {
            ?>
            <?php if ($_POST['layanan'] == 'I' || $_POST['layanan'] == 'G' || $_POST['layanan'] == 'A' || $_POST['layanan'] == '--' ) { ?>
              <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">

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
                    <th rowspan="2" width="5%" style="text-align: center;">Piutang Umum</th>
                    <th rowspan="2" width="5%" style="text-align: center;">Kurang Bayar</th>
                    <th rowspan="2" width="5%" style="text-align: center;">Diskon</th>
                    <th rowspan="2" width="5%" style="text-align: center;">Jumlah Netto</th>
                  </tr>
                  <tr>
                    <th class="column-title" style="text-align: center;">Tunai</th>
                    <th class="column-title" style="text-align: center;">Bank</th>
                            
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $no = 0;
                            foreach (getDataTable($sql_where, $DK['usr_name']) as $dt) {
                              $no++;

                              $id_pem[$no] = $dt['pembayaran_id'];
                              $pembayaran_det_kwitansi[$no] = $dt['pembayaran_det_kwitansi'];
                              $sql = "select sum(pembayaran_det_dibayar) as tunai from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where pembayaran_det_dibayar notnull and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar = '01'"." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                              
                              $TotalTunai = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as bank, jbayar_nama from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and jbayar_id <> '01' and jbayar_id <> 'x'"." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi'])." group by jbayar_nama";
                              $TotalBank = $dtaccess->FetchAll($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as bpjs from klinik.klinik_pembayaran_det where id_jbayar = 'BPJS' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                              $TotalBPJS = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as asuransi from klinik.klinik_pembayaran_det where id_jbayar in(select perusahaan_id from global.global_perusahaan) and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                              $TotalAsuransi = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as karyawan from klinik.klinik_pembayaran_det where id_jbayar = 'Karyawan' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                              $TotalKaryawan = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as piutang_umum from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar = 'x'"." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                              $TotalPiutangUmum = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as kurang from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi'])." and pembayaran_det_flag = 'P' and pembayaran_det_tipe_piutang = 'P'";
                              $TotalKurangBayar = $dtaccess->Fetch($sql);

                              $sql = "select sum(pembayaran_det_dibayar) as diskon from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and pembayaran_det_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal'])." and pembayaran_det_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir'])." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi'])." and id_jbayar = 'Diskon'";
                              $TotalDiskon = $dtaccess->Fetch($sql);



                              $sql = "select * from klinik.klinik_deposit_history where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id']);
                              $Deposit = $dtaccess->Fetch($sql);

                              $bankTotal = 0;
                              for($a = 0; $a < count($TotalBank); $a++){
                               $bankTotal += $TotalBank[$a]['bank'];
                             }
                             ?>
                             <?php if ($pembayaran_det_kwitansi[$no+1] != $pembayaran_det_kwitansi[$no]): ?>

                              <tr>
                                <td width="1%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= $no ?></td>
                                <?php
                                $daytime = explode(".", $dt["pembayaran_det_create"]);
                                $time = explode(" ", $daytime[0]);
                                $Netto = $TotalTunai['tunai'] + $bankTotal + $TotalBPJS['bpjs'] + $TotalAsuransi['asuransi'] + $TotalKaryawan['karyawan'] + $TotalPiutangUmum['piutang_umum'] + $TotalKurangBayar['kurang'] + $TotalDiskon['diskon'];
                                ?>
                                <td width="7%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= date_db($dt['reg_tanggal_pulang']) ?></td>
                                <td width="7%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= format_date($time[0]) . "&nbsp;" . $time[1] ?></td>
                                <td width="7%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= $dt['pembayaran_det_kwitansi'] ?></td>
                                <td width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= $dt['cust_usr_kode'] ?></td>
                                <td width="10%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?php if ($dt['cust_usr_kode'] == '500' || $dt["cust_usr_kode"] == '100') {
                                  get_nama_pasien_luar($dt['id_reg']);
                                    // get_nama_pasien_luar_apotik($dt['id_reg']);
                                } else {
                                  echo str_replace("*", "'", $dt['cust_usr_nama']);
                                }
                              ?></td>
                              <td width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= $dt["poli_nama"] ?></td>
                              <td width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>>
                                <?php if ($dt["poli_asal"] == '') {
                                  echo $dt["poli_nama"];
                                } else {
                                  echo $dt["poli_asal"];
                                } ?>
                              </td>
                              <?php
                              $sub_tunai += $TotalTunai['tunai'];

                              $sub_bank += $bankTotal;
                              $sub_bpjs += $TotalBPJS['bpjs'];
                              $sub_asuransi += $TotalAsuransi['asuransi'];
                              $sub_karyawan += $TotalKaryawan['karyawan'];
                              $sub_piutang_umum += $TotalPiutangUmum['piutang_umum'];
                              $sub_kurang += $TotalKurangBayar['kurang'];
                              $sub_diskon_ += $TotalDiskon['diskon'];
                              $sub_netto += $Netto;
                              ?>
                              <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalTunai['tunai'])) ?></td>
                              <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>>
                               <?php
                               for($a = 0; $a < count($TotalBank); $a++){
                                 echo str_replace(',', '.', $TotalBank[$a]['jbayar_nama']." ".currency_format($TotalBank[$a]['bank'])).", ";
                               }

                               ?>

                             </td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalBPJS['bpjs'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalAsuransi['asuransi'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalKaryawan['karyawan'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalPiutangUmum['piutang_umum'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalKurangBayar['kurang'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($TotalDiskon['diskon'])) ?></td>
                             <td align="right" width="5%" <?php if ($Deposit['deposit_history_id'] != '') echo "style='color:red;'" ?>><?= str_replace(',', '.', currency_format($Netto)) ?></td>
                             <?php $row++; ?>
                           </tr>
                         <?php endif ?>
                       </tbody>
                          <?php // }
                        } 
                        ?>
                        <tfoot>
                          <tr>
                            <td colspan="8"><?= "Sub Total : " . $DK['usr_name']; ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_tunai)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_bank)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_bpjs)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_asuransi)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_karyawan)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_piutang_umum)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_kurang)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_diskon_)) ?></td>
                            <td align="right"><?= str_replace(',', '.', currency_format($sub_netto)) ?></td>
                          </tr>
                        </tfoot>
                        <?php
                        $totalTunaiGlobal += $sub_tunai;
                        $totalBankGlobal += $sub_bank;
                        $totalJknGlobal += $sub_bpjs;
                        $totalPerusahaanGlobal += $sub_asuransi;
                        $totalKaryawanGlobal += $sub_karyawan;
                        $totalPiutangUmumGlobal += $sub_piutang_umum;
                        $totalKurangBayarGlobal += $sub_kurang;
                        $totalDiskonGlobal_ += $sub_diskon_;
                        $totalNettoGlobal += $sub_netto;
                        ?>
                      </table>
                    <?php } ?>

                  <?php }
                }
                $totalPasien = $row;
                  // enfor data kasir

                  // History Deposit

                $sql = "select * from klinik.klinik_deposit_history a 
                left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                left join global.global_jenis_bayar c on c.jbayar_id = a.id_jbayar
                where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']).' '.$_POST['waktu_awal']) . " 
                and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']).' '.$_POST['waktu_akhir']) . "
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

                $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag from klinik.klinik_deposit_history a 
                left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']).' '.$_POST['waktu_awal']) . " 
                and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']).' '.$_POST['waktu_akhir']) . "
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


                $sql = "select deposit_history_id, deposit_history_nominal, deposit_history_flag, id_jbayar from klinik.klinik_deposit_history a 
                left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
                where deposit_history_when_create >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal']).' '.$_POST['waktu_awal']) . " 
                and deposit_history_when_create <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']).' '.$_POST['waktu_akhir']) . "
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
                // echo $sql;
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

                    $DepositMasukNonB[$dataDepositNonTunai[$i]['id_jbayar']] += str_replace('-', '', $dataDepositNonTunai[$i]['deposit_history_nominal']);
                  }
                }

                ?>
                <?php if (count($dataDeposit) > 0) : ?>
                  <?php if ($_POST['layanan'] == 'DP' || $_POST['layanan'] == '--' || $_POST['layanan'] == 'I') { ?>
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

                          $sql = "select sum(deposit_history_nominal) as dp from klinik.klinik_deposit_history where deposit_history_flag = 'M' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDeposit[$i]['id_pembayaran']);
                          $datadepositMasuk = $dtaccess->Fetch($sql);

                          $sql = "select sum(deposit_history_nominal) as dp from klinik.klinik_deposit_history where deposit_history_flag = 'R'  and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataDeposit[$i]['id_pembayaran']);
                          $datadepositRetur = $dtaccess->Fetch($sql);

                          $PembDeposit = $datadepositMasuk['dp'] - str_replace('-', '', $datadepositRetur['dp']);

                          $sql = "select jenis_nama from klinik.klinik_registrasi a left join global.global_jenis_pasien b on b.jenis_id = a.reg_jenis_pasien where reg_utama is null and id_pembayaran = ".QuoteValue(DPE_CHAR, $dataDeposit[$i]['id_pembayaran']);
                          $JenisPasienDeposit = $dtaccess->Fetch($sql);

                          if ($dataDeposit[$i]['deposit_history_flag'] == 'P') {
                            $X = explode(' ', $TglDeposit['deposit_history_when_create']);
                            $Tanggal = date_db($X[0]);
                            $Waktu = $X[1];
                            $tgl = $Tanggal.' '.$Waktu;
                          }else{
                            $X = explode(' ', $dataDeposit[$i]['deposit_history_when_create']);
                            $Tanggal = date_db($X[0]);
                            $Waktu = $X[1];
                            $tgl = $Tanggal.' '.$Waktu;
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



                              echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataDeposit[$i]['deposit_history_nominal'])) : currency_format($dataDeposit[$i]['deposit_history_nominal']);
                              $SumDepositDebet += $dataDeposit[$i]['deposit_history_nominal'];
                            }
                            elseif ($dataDeposit[$i]['deposit_history_flag'] == 'E') {
                                # code...
                              echo currency_format($PembDeposit);
                            }
                            else {
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
                              echo "Retur ".$JenisPasienDeposit['jenis_nama'];
                            } elseif ($dataDeposit[$i]['deposit_history_flag'] == 'E') {
                              echo "Excess";
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
                        echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalHistoryDeposit)) : currency_format($totalHistoryDeposit) ?></b></td>
                      </tr>
                    </tfoot>
                  </table>
                <?php } ?>
              <?php endif; ?>

              <?php 
              foreach ($dataKasir as $DK) {
                if (count(getDataTable2($sql_where2, $DK['usr_name'])) != 0) { 
                  ?>
                  <?php if (($_POST['layanan'] == 'Lain' || $_POST['layanan'] == '--') && $_POST['cust_usr_jenis'] != '7' && $_POST['cust_usr_kode'] == '' && $_POST['id_poli'] == '--') { ?>
                    <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">
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
                        foreach (getDataTable2($sql_where2, $DK['usr_name']) as $dt) {
                          $urut++;
                          $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar = '01'";
                          $PembayaranTunai = $dtaccess->Fetch($sql);

                          $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01' and jbayar_id <> 'x')";
                          $PembayaranBank = $dtaccess->Fetch($sql);

                          $sql = "select sum(pembayaran_det_total) as total from kasir.kasir_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar = 'Disc'";
                          $PembayaranDisc = $dtaccess->Fetch($sql);

                          $Netto = $PembayaranTunai['total'] + $PembayaranBank['total'] + $PembayaranDisc['total'];
                          ?>
                          <tr>
                            <td width="5%"><?php echo $urut; ?></td>
                            <td width="10%"><?php echo $dt['pembayaran_create'] ?></td>
                            <td width="10%"><?php echo $dt['penjualan_nomor'] ?></td>
                            <td width="25%"><?php echo $dt['cust_usr_nama'] ?></td>
                            <td width="10%"><?php echo $dt['pembayaran_who_create'] ?></td>
                            <td width="10%" align="right"><?php echo str_replace(',', '.', currency_format($PembayaranTunai['total'])) ?></td>
                            <td width="10%" align="right"><?php echo str_replace(',', '.', currency_format($PembayaranBank['total'])) ?></td>
                            <td width="10%" align="right"><?php echo str_replace(',', '.', currency_format($PembayaranDisc['total'])) ?></td>
                            <td width="10%" align="right"><?php echo str_replace(',', '.', currency_format($Netto)) ?></td>
                          </tr>
                          <?php 
                          $SubTunai += $PembayaranTunai['total'];
                          $SubBank += $PembayaranBank['total'];
                          $SubDisc += $PembayaranDisc['total'];
                          $SubNetto += $Netto;
                          $TotalJumlahPenjualan++;
                          $SubTunaiPenjualan += $PembayaranTunai['total'];
                          $SubBankPenjualan += $PembayaranBank['total'];
                          $SubDiscPenjualan += $PembayaranDisc['total'];
                          $SubNettoPenjualan += $Netto;
                        } 
                        unset($urut);
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="5">TOTAL <?php echo $DK['usr_name']; ?></td>
                          <td align="right"><?php echo str_replace(',', '.', currency_format($SubTunai)) ?></td>
                          <td align="right"><?php echo str_replace(',', '.', currency_format($SubBank)) ?></td>
                          <td align="right"><?php echo str_replace(',', '.', currency_format($SubDisc)) ?></td>
                          <td align="right"><?php echo str_replace(',', '.', currency_format($SubNetto)) ?></td>
                        </tr>
                      </tfoot>
                    </table>
                    <?php 
                    unset($SubTunai);
                    unset($SubBank);
                    unset($SubDisc);
                    unset($SubNetto);
                  } 
                  ?>
                  <?php 
                } 
              }
              ?>
              <?php
                    // data diskon
              $layanan = ($_POST['layanan'] == 'A') ? 'J' : $_POST['layanan'] ;

              $sql = "select * from klinik.klinik_pembayaran_det a
              left join ar_ap.diskon d on d.pembayaran_id = a.id_pembayaran 
              left join klinik.klinik_registrasi c on c.reg_id=d.id_reg 
              left join global.global_customer_user e on e.cust_usr_id = c.id_cust_usr 
              where pembayaran_det_tgl >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal'])) . " 
              and pembayaran_det_tgl <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir']))  . " and id_jbayar ='Diskon'  
              and diskon_nominal <> '0' ";
              $sql .= ($_POST['layanan'] != '--') ? "and reg_tipe_rawat = '".$layanan."'" : "";
              $sql .= ($_POST['cust_usr_kode']) ? "and e.cust_usr_kode = '%".$_POST['cust_usr_kode']."%'" : "";
              if ($_POST['usr_id']<>'--') {
                    // code...
                $sql.="and who_when_update=". QuoteValue(DPE_CHAR, $_POST['usr_id']);
              }
              $sql.=" order by reg_tanggal desc";
               // echo $sql;
              $dataDiskon = $dtaccess->FetchAll($sql);
              
              ?>
              <?php if (count($dataDiskon) > 0) : ?>

                <?php $totalDiskon = 0;
                for ($i = 0; $i < count($dataDiskon); $i++) {
                  $totalDiskon = $totalDiskon + $dataDiskon[$i]['diskon_nominal']; ?>

                <?php } ?>
              <?php endif; ?>
              <?php 
              
              
                    // echo $_POST['layanan'];
              $FixDepositMasuk = $DepositMasuk;
              $FixTotalDepositPosting = $PemakaianDeposit + $PemakaianDepositNon + $ReturDeposit;

              // echo "$PemakaianDeposit + $PemakaianDepositNon + $ReturDeposit";

              if ($_POST['layanan'] == '--') {  
                $FixTotalTunaiGlobal = $totalTunaiGlobal + $SubTunaiPenjualan + $FixDepositMasuk;
                        // echo $totalTunaiGlobal.' + '.$dataPenjualanTunai['total_pembayaran'].' + '.$FixTotalDepositPosting.' + '.$FixDepositMasuk;
                $FixTotalBankGlobal = $totalBankGlobal + $SubBankPenjualan + $totalDepositNonTunai;
                $JumlahBaris = $totalPasien + $TotalJumlahPenjualan;
                $FixReturDeposit = $ReturDeposit;
              } elseif ($_POST['layanan']== 'I' ){ 
                $SubTunaiPenjualan = 0;
                $SubBankPenjualan = 0;
                $FixTotalTunaiGlobal = $totalTunaiGlobal;
                $FixTotalBankGlobal = $totalBankGlobal;
                $FixReturDeposit = $ReturDeposit;
                $JumlahBaris = $totalPasien;
              } elseif ($_POST['layanan']== 'A' || $_POST['layanan'] == 'G') { 
                $SubTunaiPenjualan = 0;
                $SubBankPenjualan = 0;
                $FixTotalDepositPosting = 0;
                $FixDepositMasuk = 0;
                $FixReturDeposit = 0;
                $FixTotalTunaiGlobal = $totalTunaiGlobal;
                $FixTotalBankGlobal = $totalBankGlobal;
                $JumlahBaris = $totalPasien;
              }elseif ($_POST['layanan']== 'Disc') {
                $FixTotalTunaiGlobal = $totalDiskon;
              } elseif ($_POST['layanan']== 'DP') {
                $FixTotalTunaiGlobal = $dataDepositTunai['total_deposit'];
                $FixTotalBankGlobal = $dataDepositNonTunai['total_deposit'];
              } elseif ($_POST['layanan']== 'Lain') {
                $FixTotalTunaiGlobal = $SubTunaiPenjualan;
                $FixTotalBankGlobal = $SubBankPenjualan;
                $JumlahBaris = $TotalJumlahPenjualan;
              } 
                      // echo $FixTotalDepositPosting;
              $FixTotalBPJSGlobal = $totalJknGlobal;
              $FixTotalAsuransiGlobal = $totalPerusahaanGlobal;
              $FixTotalKaryawanGlobal = $totalKaryawanGlobal;
              $FixTotalPiutangUmumGlobal = $totalPiutangUmumGlobal;
              $FixTotalKurangGlobal = $totalKurangBayarGlobal;
              $FixTotalDiskon = $sub_diskon + $SubDiscPenjualan+$totalDiskon;

              $FixTotalNetto = $FixTotalTunaiGlobal + $FixTotalBankGlobal + $FixTotalBPJSGlobal + $FixTotalAsuransiGlobal +$FixTotalKaryawanGlobal + $FixTotalPiutangUmumGlobal - $FixTotalKurangGlobal + $FixTotalDepositPosting+ $FixTotalDiskon;
              
                      
              ?>

              <?php if($_POST['layanan'] != 'PP') { ?>
                <p>TOTAL GLOBAL</p>
                <table cellspacing="0" width="100%" class="table table-bordered" border="1" style="margin-top:15px;">
                  <thead align="center">
                    <tr>
                      <td rowspan="2"></td>
                      <td align="center" colspan="2">Tunai/Bank</td>
                      <td align="center" rowspan="2">Retur Deposit</td>
                      <td align="center" rowspan="2">BPJS</td>
                      <td align="center" rowspan="2">Asuransi</td>
                      <td align="center" rowspan="2">Karyawan</td>
                      <td align="center" rowspan="2">Piutang Umum</td>
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
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalTunaiGlobal)) : currency_format($FixTotalTunaiGlobal); ?></td>
                      <!-- <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBankGlobal)) : currency_format($FixTotalBankGlobal); ?></td> -->
                      <?php 
                      if ($_POST['layanan'] != 'Disc' && $_POST['layanan'] != 'DP') { 
                        $sql_where = implode(" and ", $sql_where);
                        $sql = "select sum(pembayaran_det_dibayar) as total, jbayar_nama, jbayar_id
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
                        $sql .= " where 1=1 and j.id_jbayar in(select jbayar_id from global.global_jenis_bayar where jbayar_id <> '01' and jbayar_id <> 'x') and  " . $sql_where;
                        $sql .= " group by jbayar_nama, jbayar_id";
                              // $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
                        // echo $sql;
                        $dataBank = $dtaccess->FetchAll($sql);

                        if(!$_POST['cust_usr_kode']){
                          $sql = "select sum(pembayaran_det_total) as total_pembayaran, jbayar_nama from kasir.kasir_penjualan a
                          left join kasir.kasir_data_pembeli h on h.reg_id = a.id_reg
                          left join kasir.kasir_pembayaran p on p.id_reg = h.reg_id 
                          left join kasir.kasir_pembayaran_det b on b.id_pembayaran = p.pembayaran_id
                          left join global.global_jenis_bayar c on c.jbayar_id = b.id_jbayar
                          where b.id_jbayar != '01'and jbayar_id <> 'x' and b.id_jbayar <> 'Disc' ";
                          if ($_POST['tgl_awal']) $sql .= " and a.penjualan_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]).' '.$_POST['waktu_awal']);
                          if ($_POST['tgl_akhir']) $sql .= " and a.penjualan_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]).' '.$_POST['waktu_akhir']);
                          if ($_POST["usr_id"]  <> "--") $sql .= " and p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                          // if ($_POST["cust_usr_kode"]) $sql .= " and p.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                          $sql .= " group by jbayar_nama";

                          $dataPenjualanBank = $dtaccess->FetchAll($sql);
                        }
                      }
                      ?>
                      <td>
                        <table>
                          <?php if ($_POST['layanan'] == 'A' || $_POST['layanan'] == 'I' || $_POST['layanan'] == 'G' || $_POST['layanan'] == '--') { ?>
                            <?php for ($i = 0; $i < count($dataBank); $i++) { ?>
                              <tr>
                                <td><?=$dataBank[$i]['jbayar_nama'] ?></td>
                                <td> : </td>
                                <td align="right">
                                  <?php 
                                  $totalPerBank = $dataBank[$i]['total'] + $DepositMasukNonB[$dataBank[$i]['jbayar_id']];
                                  ?>
                                  <?=str_replace(',', '.', currency_format($totalPerBank)) ?> 
                                </td>
                              </tr>
                            <?php } ?>
                          <?php } ?>
                          <?php if ($_POST['layanan'] == 'Lain' || $_POST['layanan'] == '--') { ?>
                            <?php for ($i = 0; $i < count($dataPenjualanBank); $i++) { ?>
                              <tr>
                                <td><?php echo $dataPenjualanBank[$i]['jbayar_nama']." Lain-lain" ?></td>
                                <td> : </td>
                                <td align="right"><?php echo str_replace(',', '.', currency_format($dataPenjualanBank[$i]['total_pembayaran'])) ?> </td>
                              </tr>
                            <?php } ?>
                          <?php } ?>
                          
                        </table>
                      </td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixReturDeposit)) : currency_format($FixReturDeposit); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalBPJSGlobal)) : currency_format($FixTotalBPJSGlobal); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalAsuransiGlobal)) : currency_format($FixTotalAsuransiGlobal); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalKaryawanGlobal)) : currency_format($FixTotalKaryawanGlobal); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalPiutangUmumGlobal)) : currency_format($FixTotalPiutangUmumGlobal); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalKurangGlobal)) : currency_format($FixTotalKurangGlobal); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalDiskon)) : currency_format($FixTotalDiskon); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalDepositPosting)) : currency_format($FixTotalDepositPosting); ?></td>
                      <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($FixTotalNetto)) : currency_format($FixTotalNetto); ?></td>
                    </tr>
                  </tbody>
                </table>
              <?php } ?>

              <?php if($_POST['layanan'] == 'PP') { ?>
                <?php if (count($dataPelunasanPiutang) > 0) { ?>
                  <p>Pelunasan Piutang</p>
                  <table cellspacing="0" width="100%" class="table table-bordered" border="1" style="margin-top:15px;">
                    <thead align="center">
                      <tr>
                        <td>No</td>
                        <td>Waktu</td>
                        <td>Kwitansi</td>
                        <td>Medrec</td>
                        <td>Nama Pasien</td>
                        <td>Pembayaran</td>
                        <td>Lebih Bayar</td>
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
                          <td width="5%"><?php echo $i+1 ?></td>
                          <td width="10%"><?php echo $dataPelunasanPiutang[$i]['ar_payment_when_update'] ?></td>
                          <td width="10%"><?php echo $dataPelunasanPiutang[$i]['ar_payment_kode'] ?></td>
                          <td width="25%"><?php echo $dataPelunasanPiutang[$i]['cust_usr_kode'] ?></td>
                          <td width="25%"><?php echo $dataPelunasanPiutang[$i]['cust_usr_nama'] ?></td>
                          <td align="right" width="15%"><?php echo currency_format($dataPelunasanPiutang[$i]['ar_payment_jumlah']) ?></td>
                          <td align="right" width="15%"><?php echo currency_format($dataPelunasanPiutang[$i]['ar_trans_lebih_bayar']) ?></td>
                          <td width="10%"><?php echo $dataPelunasanPiutang[$i]['ar_payment_who_update'] ?></td>
                          <td width="10%"><?php echo $Flag ?></td>
                        </tr>
                        <?php 
                        $totalPembayaran += $dataPelunasanPiutang[$i]['ar_payment_jumlah'];
                        $totalLebihBayar += $dataPelunasanPiutang[$i]['ar_trans_lebih_bayar'];
                      } 
                      $FixTotalPembayaran = $totalPembayaran + $totalLebihBayar;
                      ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="5">TOTAL</td>
                        <td align="right"><?php echo currency_format($FixTotalPembayaran) ?></td>
                        <td>&nbsp;</td>
                      </tr>
                    </tfoot>
                  </table>
                <?php } ?>
              <?php } ?>

            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (!$_POST["btnExcel"]) { ?>

      </div>
    </div>
  </div>
  <!-- /page content -->

  <!-- footer content -->
  <?php require_once($LAY . "footer.php") ?>
  <!-- /footer content -->
</div>
</div>

<?php require_once($LAY . "js.php") ?>

</body>

</html>
<? } ?>














<?php if (!$_POST["btnExcel"]) { ?>

  <br /><br /><br /><br />

<?php } ?>
<script language="JavaScript">
  function CheckSimpan(frm) {

    if (!frm.tgl_awal.value) {
      alert("Tanggal Awal Harus Diisi");
      return false;
    }
  }

  window.onload = function() {
    TampilCombo();
    TampilKasir();
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

  function TampilKasir(id) {

    //alert(id);
    if (id == "b9ead727d46bc226f23a7c1666c2d9fb") {
      usr_id.disabled = false;
      //elm_combo.checked = true; 

    } else {
      usr_id.disabled = true;
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
    window.open('report_setoran_cicilan_cetak.php?perusahaan=<?php echo $_POST['ush_id']; ?>&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&shift=<?php echo $_POST["reg_shift"]; ?>&dokter=<?php echo $_POST["id_dokter"]; ?>&kasir=<?php echo $kasir; ?>&reg_tipe_layanan=<?php echo $_POST["reg_tipe_layanan"]; ?>&kode=<?php echo $_POST["cust_usr_kode"]; ?>&jbayar=<?php echo $_POST["jbayar"]; ?>&layanan=<?php echo $_POST["layanan"]; ?>&id_poli=<?php echo $_POST["id_poli"]; ?>&waktu_awal=<?php echo $_POST['waktu_awal'] ?>&waktu_akhir=<?php echo $_POST['waktu_akhir'] ?>&usr_id=<?php echo $_POST['usr_id'] ?>', '_blank');
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
      ' transitionOut': 'none',
      'type': 'iframe'
    });
  });

  var _wnd_new;

  function BukaWindow(url, judul) {
    if (!_wnd_new) {
      _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=950,height=600,left=100,top=100');
    } else {
      if (_wnd_new.closed) {
        _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=950,height=600,left=100,top=100');
      } else {
        _wnd_new.focus();
      }
    }
    return false;
  }

  function ProsesEditing(id) {

    var all_id = id.split('-');
    var link = 'input_report_setoran_loket.php?bahan_edit=' + all_id[0] + '&klinik=' + all_id[1];
    BukaWindow(link);
    //document.location.href='<?php echo $thisPage; ?>';
  }
</script>


<?php if ($_POST["btnExcel"]) { ?>
<?php } ?>
<?php if (!$_POST["btnExcel"]) { ?>

</div>

<?php } ?>