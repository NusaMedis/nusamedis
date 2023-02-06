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
if ($_POST["tgl_awal"]) $sql_where[] = "date(j.pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
if ($_POST["tgl_akhir"]) $sql_where[] = "date(j.pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));
if ($_POST["js_biaya"]) $sql_where[] = "a.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_POST["js_biaya"]);
if ($_POST["jbayar"] != '-') $sql_where[] = "j.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
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

if ($_POST["ush_id"]) {
  $sql_where[] = "d.id_perusahaan = " . QuoteValue(DPE_CHAR, $_POST["ush_id"]);
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
    $sql_where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status='F0' or d.reg_status='A0')";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "d.reg_status like 'I%'";
  } else {
    $sql_where[] = "d.reg_status like 'G%'";
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
  $sql = "select d.reg_kode_trans, a.pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama 
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
  $sql .= " where j.who_when_update='" . $usr_name . "' and  " . $sql_where;
  //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
  $sql .= " group by pembayaran_id, j.pembayaran_det_kwitansi, a.pembayaran_create, c.cust_usr_nama, c.cust_usr_kode, f.poli_nama, d.reg_kode_trans";
  $sql .= " order by pembayaran_create asc, j.pembayaran_det_kwitansi";
  // echo $sql;
  return $dataTable = $dtaccess->FetchAll($sql);
}

//  function buat get nama pasien luar yang radiologi
function get_nama_pasien_luar($id_reg)
{
  $dtaccess = new DataAccess();
  $sql = "select * from klinik.klinik_folio where id_reg='" . $id_reg . "'";
  $data = $dtaccess->FetchAll($sql);
  echo str_replace("*", "'", $data[0]['fol_keterangan']);
}


//  function buat get nama pasien luar yang Penjualan Apotik
function get_nama_pasien_luar_apotik($id_reg)
{
  $dtaccess = new DataAccess();
  $sql = "select * from apotik.apotik_penjualan where id_reg='" . $id_reg . "'";
  $data = $dtaccess->FetchAll($sql);
  echo str_replace("*", "'", $data[0]['cust_usr_nama']);
}



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
$sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1')";
if ($_POST['usr_id'] != '--') $sql .= "and usr_name = ".QuoteValue(DPE_CHAR,$_POST['usr_id']);
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_auth_user where (id_rol='4' or id_rol='1')";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir2 = $dtaccess->FetchAll($rs);


?>

<?php if (!$_POST["btnExcel"]) { ?>
  <!DOCTYPE html>
  <html lang="en">
  <?php require_once($LAY . "header.php") ?>

  <body class="nav-sm">
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
                        <?php if ($userData["rol"] != '2') { ?>
                          <td width="20%" class="tablecontent">
                          <?php } else { ?>
                          <td width="20%" class="tablecontent">
                          <?php } ?>
                          <select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                            <!--onChange="this.form.submit();" -->
                            <option value="0">[ Pilih Jenis Bayar ]</option>
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
                  // for data kasir 
                  foreach ($dataKasir as $DK) {
                    $sub_tunai = 0;
                    $sub_bank = 0;
                    $sub_bpjs = 0;
                    $sub_asuransi = 0;
                    $sub_karyawan = 0;
                    $sub_netto = 0;
                    if (count(getDataTable($sql_where, $DK['usr_name'])) != 0) {
                  ?>
                  <?php if ($_POST['layanan'] == 'I' || $_POST['layanan'] == 'G' || $_POST['layanan'] == 'A' || $_POST['layanan'] == '--' ) { ?>
                      <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">

                        <thead align="center">
                          <tr>
                            <th rowspan="2" width="3%">No.</th>
                            <th rowspan="2" width="7%">Tanggal</th>
                            <th rowspan="2" width="7%">No. Bukti</th>
                            <th rowspan="2" width="5%">Medrec</th>
                            <th rowspan="2" width="10%">Nama Pasien</th>
                            <th rowspan="2" width="5%">Tag</th>
                            <th colspan="2" width="10%" style="text-align: center;">Tunai/Bank</th>
                            <th rowspan="2" width="5%" style="text-align: center;">BPJS</th>
                            <th rowspan="2" width="5%" style="text-align: center;">Asuransi</th>
                            <th rowspan="2" width="5%" style="text-align: center;">Karyawan</th>
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
                          foreach (getDataTable($sql_where, $DK['usr_name']) as $dt) {
                            $no++;
// echo "<pre>";
// print_r ("'".$dt['id_reg']."'"."<br>");
// print_r ("'".$dt['pembayaran_id']."'");
// echo "</pre>";
                            $id_pem[$no] = $dt['pembayaran_id'];
                            $pembayaran_det_kwitansi[$no] = $dt['pembayaran_det_kwitansi'];
                            $sql = "select sum(pembayaran_det_dibayar) as tunai from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and id_jbayar = '01'"." and date(pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]))." and date(pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]))." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                            $TotalTunai = $dtaccess->Fetch($sql);

                            $sql = "select sum(pembayaran_det_dibayar) as bank, jbayar_nama from klinik.klinik_pembayaran_det a left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar where id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and jbayar_id <> '01'"." and date(pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]))." and date(pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]))." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi'])." group by jbayar_nama";
                            $TotalBank = $dtaccess->Fetch($sql);

                            $sql = "select sum(pembayaran_det_dibayar) as bpjs from klinik.klinik_pembayaran_det where id_jbayar = 'BPJS' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and date(pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]))." and date(pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]))." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                            $TotalBPJS = $dtaccess->Fetch($sql);

                            $sql = "select sum(pembayaran_det_dibayar) as asuransi from klinik.klinik_pembayaran_det where id_jbayar in(select perusahaan_id from global.global_perusahaan) and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and date(pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]))." and date(pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]))." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                            $TotalAsuransi = $dtaccess->Fetch($sql);

                            $sql = "select sum(pembayaran_det_dibayar) as karyawan from klinik.klinik_pembayaran_det where id_jbayar = 'Karyawan' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dt['pembayaran_id'])." and date(pembayaran_det_tgl) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]))." and date(pembayaran_det_tgl) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]))." and pembayaran_det_kwitansi = ".QuoteValue(DPE_CHAR,$dt['pembayaran_det_kwitansi']);
                            $TotalKaryawan = $dtaccess->Fetch($sql);
                          ?>
                          <?php if ($id_pem[$no+1] != $id_pem[$no] && $pembayaran_det_kwitansi[$no+1] != $pembayaran_det_kwitansi[$no]): ?>
                            
                            <tr>
                              <td width="3%"><?= $no ?></td>
                              <?php
                              $daytime = explode(".", $dt["pembayaran_create"]);
                              $time = explode(" ", $daytime[0]);
                              $Netto = $TotalTunai['tunai'] + $TotalBank['bank'] + $TotalBPJS['bpjs'] + $TotalAsuransi['asuransi'] + $TotalKaryawan['karyawan'];
                              ?>
                              <td width="7%"><?= format_date($time[0]) . "&nbsp;" . $time[1] ?></td>
                              <td width="7%"><?= $dt['pembayaran_det_kwitansi'] ?></td>
                              <td width="5%"><?= $dt['cust_usr_kode'] ?></td>
                              <td width="10%"><?php if ($dt['cust_usr_kode'] == '500' || $dt["cust_usr_kode"] == '100') {
                                    get_nama_pasien_luar($dt['id_reg']);
                                    get_nama_pasien_luar_apotik($dt['id_reg']);
                                  } else {
                                    echo str_replace("*", "'", $dt['cust_usr_nama']);
                                  }
                                  ?></td>
                              <td width="5%"><?= $dt["poli_nama"] ?></td>
                              <?php
                                $sub_tunai += $TotalTunai['tunai'];
                                $sub_bank += $TotalBank['bank'];
                                $sub_bpjs += $TotalBPJS['bpjs'];
                                $sub_asuransi += $TotalAsuransi['asuransi'];
                                $sub_karyawan += $TotalKaryawan['karyawan'];
                                $sub_netto += $Netto;
                              ?>
                              <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalTunai['tunai'])) ?></td>
                              <td align="right" width="5%"><?= str_replace(',', '.', $TotalBank['jbayar_nama']." ".currency_format($TotalBank['bank'])) ?></td>
                              <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalBPJS['bpjs'])) ?></td>
                              <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalAsuransi['asuransi'])) ?></td>
                              <td align="right" width="5%"><?= str_replace(',', '.', currency_format($TotalKaryawan['karyawan'])) ?></td>
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
                              <td colspan="6"><?= "Sub Total : " . $DK['usr_name']; ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_tunai)) ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_bank)) ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_bpjs)) ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_asuransi)) ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_karyawan)) ?></td>
                              <td align="right"><?= str_replace(',', '.', currency_format($sub_netto)) ?></td>
                            </tr>
                          </tfoot>
                          <?php
                            $totalTunaiGlobal += $sub_tunai;
                            $totalBankGlobal += $sub_bank;
                            $totalJknGlobal += $sub_bpjs;
                            $totalPerusahaanGlobal += $sub_asuransi;
                            $totalKaryawanGlobal += $sub_karyawan;
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
                              where deposit_history_tgl >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal'])) . " 
                              and deposit_history_tgl <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir'])) . "
                              and deposit_history_nominal <> '0' and (deposit_history_flag = 'M' or deposit_history_flag = 'R')";
                  if ($_POST["usr_id"] <> "--") {
                    $sql .= "and deposit_history_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
                  }
                  $sql .= "order by deposit_history_when_create asc";
                  $dataDeposit = $dtaccess->FetchAll($sql);
                  // echo $sql;

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

                                    echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataDeposit[$i]['deposit_history_nominal'])) : currency_format($dataDeposit[$i]['deposit_history_nominal']);
                                    $SumDepositDebet += $dataDeposit[$i]['deposit_history_nominal'];
                                  } else {
                                    echo "(" . str_replace("-", "", currency_format($dataDeposit[$i]['deposit_history_nominal'])) . ")";
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
                                                    echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalHistoryDeposit)) : currency_format($totalHistoryDeposit) ?></b></td>
                            </tr>
                          </tfoot>
                        </table>
                      <?php } ?>
                    <?php endif; ?>
                    <?php
                    // data diskon

                    $sql = "select * from ar_ap.diskon a
                    left join klinik.klinik_pembayaran b on b.pembayaran_id=a.pembayaran_id 
                    left join klinik.klinik_registrasi c on c.reg_id=a.id_reg 
                    left join global.global_customer_user d on d.cust_usr_id = c.id_cust_usr 
                    where reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_awal'])) . " 
                    and reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_POST['tgl_akhir'])) . "
                    and diskon_nominal <> '0' order by reg_tanggal desc";
                    $dataDiskon = $dtaccess->FetchAll($sql);

                    ?>

                    <?php if (count($dataDiskon) > 0) : ?>
                      <?php if ($_POST['layanan'] == 'Disc' || $_POST['layanan'] == '--') { ?>
                        <table class="table table-bordered" cellspacing="0" width="100%" border="1" style="margin-top:15px;">
                          <thead>
                            <tr>
                              <th colspan="7"><b>Daftar Diskon</b></th>
                            </tr>
                            <tr>
                              <th style="text-align: center;">No</th>
                              <th style="text-align: center;">Tanggal Transaksi</th>
                              <th style="text-align: center;">Nama Diskon</th>
                              <th style="text-align: center;">No Medrec</th>
                              <th style="text-align: center;">Nama Pasien</th>
                              <th style="text-align: center;">Nominal</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php $totalDiskon = 0;
                            for ($i = 0; $i < count($dataDiskon); $i++) {
                              $totalDiskon = $totalDiskon + $dataDiskon[$i]['diskon_nominal']; ?>
                              <tr>
                                <td align="center"><?php echo $i + 1 ?></td>
                                <td align="center"><?php echo date_db($dataDiskon[$i]['pembayaran_tanggal']) ?></td>
                                <td align="center"><?php echo $dataDiskon[$i]['diskon_nama'] ?></td>
                                <td align="center"><?php echo $dataDiskon[$i]['cust_usr_kode'] ?></td>
                                <td><?php if ($dataDiskon[$i]['cust_usr_kode'] == '500' || $dataDiskon[$i]["cust_usr_kode"] == '100') {
                                      get_nama_pasien_luar($dataDiskon[$i]['id_reg']);
                                      get_nama_pasien_luar_apotik($dataDiskon[$i]['id_reg']);
                                    } else {
                                      echo str_replace("*", "'", $dataDiskon[$i]['cust_usr_nama']);
                                    }
                                    ?></td>
                                <td align="right"><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format(($dataDiskon[$i]['diskon_nominal']) ? $dataDiskon[$i]['diskon_nominal'] : '0')) : currency_format(($dataDiskon[$i]['diskon_nominal']) ? $dataDiskon[$i]['diskon_nominal'] : '0') ?></td>
                              </tr>
                            <?php } ?>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="5" align="left"><b>TOTAL</b></td>
                              <td align="right"><b><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalDiskon)) : currency_format($totalDiskon) ?></b></td>
                            </tr>
                          </tfoot>
                        </table>
                      <?php } ?>
                    <?php endif; ?>
                    <?php if (count($dataPenjualan) > 0) : ?>
                      <?php if ($_POST['layanan'] == 'Lain' || $_POST['layanan'] == '--') { ?>
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
                                  <td><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataPenjualan[$i]["penjualan_total"])) : currency_format($dataPenjualan[$i]["penjualan_total"]); ?></td>
                                  <td><?php echo  $dataPenjualan[$i]["usr_name"]; ?></td>
                                  <td><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataPenjualan[$i]["penjualan_diskon"])) : currency_format($dataPenjualan[$i]["penjualan_diskon"]) ?></td>
                                  <td align="right"><?php $total_penjualan_kasir = $total_penjualan_kasir + $dataPenjualan[$i]["penjualan_total"];
                                                    echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataPenjualan[$i]["penjualan_total"])) : currency_format($dataPenjualan[$i]["penjualan_total"]) ?></td>
                                </tr>
                              <?php $j = 1;
                              } ?>
                              <tr>
                                <td></td>
                                <td><?php echo $j++; ?></td>
                                <td><?php echo $dataPenjualan[$i]['item_nama']; ?></td>
                                <td><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"])) : currency_format($dataPenjualan[$i]["penjualan_detail_jumlah"]); ?></td>
                                <td><?php echo ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"])) : currency_format($dataPenjualan[$i]["penjualan_detail_harga_jual"]); ?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <tr>
                              <td colspan="7" align="center">
                                TOTAL
                              </td>
                              <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($total_penjualan_kasir)) : currency_format($total_penjualan_kasir) ?></td>
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
                          <td align="center" rowspan="2">Jumlah Netto</td>
                        </tr>
                        <tr>
                          <td class="column-title">Tunai</td>
                          <td class="column-title">Bank</td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td align="center" width="">TOTAL ( <?php echo $totalPasien; ?> )</td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalTunaiGlobal)) : currency_format($totalTunaiGlobal); ?></td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalBankGlobal)) : currency_format($totalBankGlobal); ?></td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalJknGlobal)) : currency_format($totalJknGlobal); ?></td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalPerusahaanGlobal)) : currency_format($totalPerusahaanGlobal); ?></td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalKaryawanGlobal)) : currency_format($totalKaryawanGlobal); ?></td>
                          <td align="right"><?= ($_POST['btnExcel']) ? str_replace(',', '.', currency_format($totalNettoGlobal + $totalHistoryDeposit)) : currency_format($totalNettoGlobal + $totalHistoryDeposit); ?></td>
                        </tr>
                      </tbody>
                    </table>

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
    //BukaWindow('report_setoran_loket_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"]; ?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"]; ?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"]; ?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"]; ?>','Pemakaian Kasir');
    //onclick="window.open(this.href); return false";
    window.open('report_setoran_cicilan_cetak.php?perusahaan=<?php echo $perusahaan; ?>&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&shift=<?php echo $_POST["reg_shift"]; ?>&dokter=<?php echo $_POST["id_dokter"]; ?>&kasir=<?php echo $kasir; ?>&reg_tipe_layanan=<?php echo $_POST["reg_tipe_layanan"]; ?>&kode=<?php echo $_POST["cust_usr_kode"]; ?>&jbayar=<?php echo $_POST["jbayar"]; ?>&layanan=<?php echo $_POST["layanan"]; ?>&id_poli=<?php echo $_POST["id_poli"]; ?>', '_blank');
    //document.location.href='report_setoran_loket_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"]; ?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"]; ?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"]; ?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"]; ?>';
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