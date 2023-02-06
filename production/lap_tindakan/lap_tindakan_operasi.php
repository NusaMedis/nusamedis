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
$thisPage = "lap_tindakan.php";

$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$lokasi = $ROOT . "/gambar/img_cfg";
$depLowest = $auth->GetDepLowest();
$depNama = $auth->GetDepNama();

//if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
if (!$_POST["klinik"]) $_POST["klinik"] = $depId;
else $_POST["klinik"] = $_POST["klinik"];

if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}


$sql = "select * from  klinik.klinik_split where id_tahun_tarif = " . QuoteValue(DPE_CHAR, $tahunTarif) . " order by split_urut asc ";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
$dataSplit = $dtaccess->FetchAll($rs);
// echo $sql;

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

$perusahaan = $_POST["ush_id"];
$kasir = $_POST["usr_id"];

//$sql_where[] = "reg_tanggal is not null and a.fol_lunas = ".QuoteValue(DPE_CHAR,"y"); 
if ($_POST["klinik"] && $_POST["klinik"] != "--") $sql_where[] = "a.id_dep = " . QuoteValue(DPE_CHAR, $_POST["klinik"]);
if ($_POST['status'] == 'y') {
 if($_POST["tgl_awal"]) $sql_where[] = "x.pembayaran_det_tgl >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
 if($_POST["tgl_akhir"]) $sql_where[] = "x.pembayaran_det_tgl <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
}else{
 if($_POST["tgl_awal"]) $sql_where[] = "d.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
 if($_POST["tgl_akhir"]) $sql_where[] = "d.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
}
if ($_POST["js_biaya"]) $sql_where[] = "i.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_POST["js_biaya"]);
//if($_POST["jbayar"]) $sql_where[] = "m.id_jbayar = ".QuoteValue(DPE_CHAR,$_POST["jbayar"]);
if ($_POST["cust_usr_kode"]) $sql_where[] = "c.cust_usr_kode like " . QuoteValue(DPE_CHAR, "%" . $_POST["cust_usr_kode"] . "%");
//$sql_where[] = " (pembayaran_flag='y' or pembayaran_flag='k') ";

if ($_POST["id_dokter"]) $sql_where[] = "d.id_dokter = " . QuoteValue(DPE_CHAR, $_POST["id_dokter"]);

if ($_POST["id_pelaksana"]) $sql_where[] = "a.fol_id in (SELECT id_fol from klinik.klinik_folio_pelaksana x where  x.id_usr = ". QuoteValue(DPE_CHAR, $_POST["id_pelaksana"])." and x.id_fol = a.fol_id) ";



if ($_POST["id_poli"]) { 
  if ($_POST["id_poli"] == 'ed6ab21fcc06cf3bda1186c44b59f453') {
    $sql_where[] = " a.is_operasi = 'y' ";
  }
  else{
    $sql_where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
  }
  
}


if ($_POST["reg_shift"]) {
  $sql_where[] = " d.reg_shift = " . QuoteValue(DPE_DATE, $_POST["reg_shift"]);
}

if ($_POST["reg_tipe_layanan"]) {
  $sql_where[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_POST["reg_tipe_layanan"]);
}

if ($_POST["cust_usr_jenis"] || $_POST["cust_usr_jenis"] != "0") {
  $sql_where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_jenis"]);
}

if ($_POST["ush_id"]) {
  $sql_where[] = "d.id_perusahaan = " . QuoteValue(DPE_CHAR, $_POST["ush_id"]);
}

if ($_POST["cito"]) {
  $sql_where[] = "n.is_cito = " . QuoteValue(DPE_CHAR, $_POST["cito"]);
}

//if($userId == 'b9ead727d46bc226f23a7c1666c2d9fb' || $userId=='fed7a2bfc3479110ea037d1940b44c7c'){
if ($_POST["usr_id"] <> '--') {
  $sql_where[] = "i.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_POST["usr_id"]);
}
//}

if ($_POST["layanan"] <> "--") {
  if ($_POST["layanan"] == "A") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='J'";
  } elseif ($_POST["layanan"] == "I") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='I'";
  } elseif ($_POST["layanan"] == "G") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='G'";
  } else {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500')";
  }
}

if ($_POST["fol_nama"]) $sql_where[] = "upper(a.fol_nama) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_POST["fol_nama"]) . "%");

  if($_POST['icdcode']) $sql_where[] = " p.rawat_icd_status='Primer' and p.id_icd = ".QuoteValue(DPE_CHAR,$_POST["icdcode"]);


/*if(!$userId == 'b9ead727d46bc226f23a7c1666c2d9fb'){
    $sql_where[] = " i.pembayaran_who_create = '".$userName."'";
   }*/

$sql_where = implode(" and ", $sql_where);


if ($_POST["btnLanjut"] || $_POST["btnExcel"] || $_POST["btnCetak"]) {
  $sql = "select a.*,  c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_umur, c.cust_usr_tanggal_lahir, c.cust_usr_alamat, c.cust_usr_penanggung_jawab, b.biaya_nama,              
             f.jenis_nama, g.usr_name as dokter, i.*, d.reg_jenis_pasien, d.reg_tanggal, d.reg_waktu, e.dep_nama,
             j.poli_nama, k.tipe_biaya_nama, l.shift_nama, m.usr_name as ptg_entri, n.is_cito ,d.reg_who_update ,o.inacbg_id,q.icd_nama,q.icd_nomor,q.icd_deskripsi     
             from  klinik.klinik_folio a  
              left join klinik.klinik_registrasi d on a.id_reg = d.reg_id
              left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran 
             left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
             left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
             left join global.global_departemen e on e.dep_id = a.id_dep 
             left join global.global_jenis_pasien f on f.jenis_id = d.reg_jenis_pasien
             left join global.global_auth_user g on d.id_dokter = g.usr_id
             left join global.global_auth_poli j on j.poli_id = d.id_poli
             left join global.global_tipe_biaya k on k.tipe_biaya_id = d.reg_tipe_layanan
             left join global.global_shift l on l.shift_id = d.reg_shift
             left join global.global_auth_user m on a.who_when_update = m.usr_id
         
             left join klinik.klinik_biaya_tarif n on a.id_biaya_tarif = n.biaya_tarif_id
             left join klinik.klinik_inacbg o on d.reg_id = o.id_reg
             left join klinik.klinik_perawatan_icd p on p.id_inacbg = o.inacbg_id
             left join klinik.klinik_icd q on q.icd_id=p.id_icd";
  //$sql .= " where d.reg_tipe_rawat='J' and 1=1 and ".$sql_where; 
  $sql .= " where 1=1 and a.is_operasi = 'y' and " . $sql_where;
  $sql .= " order by i.pembayaran_create,a.id_pembayaran,a.fol_waktu";
  // echo $sql;
  $dataTable = $dtaccess->FetchAll($sql);

  $sqljml="select count(*) from klinik.klinik_preop a
left join klinik.klinik_registrasi b on b.reg_id=a.id_reg ";
  $sqljml .=  " where b.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]))." and b.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
    // echo $sqljml;
  $jmlpx = $dtaccess->Fetch($sqljml);
  // echo $jmlpx['count'];

}



$tableHeader = "Operasi-Laporan Tindakan";
if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=laporan_tindakan.xls');
}

if ($_POST["btnCetak"]) {
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
$sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep =" . QuoteValue(DPE_CHAR, $_POST["klinik"]) . " order by usr_id asc ";
$rs = $dtaccess->Execute($sql);
$dataDokter = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

if ($_POST["dep_logo"]) $fotoName = $lokasi . "/" . $row_edit["dep_logo"];
else $fotoName = $lokasi . "/default.jpg";

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

// Data Poli //
$sql = "select * from global.global_auth_poli where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by poli_nama";
$dataPoli = $dtaccess->FetchAll($sql);

// cari tipe layanan
$sql = "select * from global.global_tipe_biaya where tipe_biaya_aktif='y' order by tipe_biaya_nama desc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$tipeBiaya = $dtaccess->FetchAll($rs);

// cari nama kasir --
$sql = "select * from global.global_auth_user_app a left join global.global_auth_user b on a.id_usr = b.usr_id where id_app = 5";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKasir = $dtaccess->FetchAll($rs);

//cari shift by id
$sql = "select * from global.global_shift where shift_id = '" . $_POST["reg_shift"] . "'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataShiftId = $dtaccess->Fetch($rs);

$sql = "select * from global.global_jenis_bayar where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and jbayar_status='y' order by jbayar_id asc";
$dataJenisBayar2 = $dtaccess->FetchAll($sql);



?>



<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>
<script type="text/javascript">
<?php if ($_x_mode == "cetak") { ?>
    window.open(
      'lap_tindakan_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&id_poli=<?php echo $_POST['id_poli'] ?>&id_dokter=<?php echo $_POST['id_dokter'] ?>&cust_usr_kode=<?php echo $_POST['cust_usr_kode'] ?>&fol_nama=<?php echo $_POST['fol_nama'] ?>&reg_jenis_pasien=<?php echo $_POST['reg_jenis_pasien'] ?>&cust_usr_jenis=<?php echo $_POST['cust_usr_jenis'] ?>&usr_id=<?php echo $_POST['usr_id'] ?>&status=<?php echo $_POST['status'] ?>',
      '_blank'); 
  <?php } ?>
</script>


  <script language="JavaScript"> 
        function CariICD(){
          $('#geticd10').autocomplete({
            serviceUrl : 'get_icd10.php',
            paramName : 'q',
            transformResult : function(response){
              var data = jQuery.parseJSON(response);
              return {
                suggestions : $.map(data, function(item){
                  return {
                    value : item.icd_nomor+'-'+item.icd_deskripsi, 
                    data : {
                      icd_id : item.icd_id,
                      icd_nomor : item.icd_nomor,
                      icd_nama : item.icd_nama,
                      icd_deskripsi : item.icd_deskripsi,
                    }
                  };
                })
              };
            },
            
          onSelect: function (suggestion) {
            $('#geticd10').val(suggestion.data.icd_nomor+'-'+suggestion.data.icd_nama);
            $('#icdcode').val(suggestion.data.icd_id);
          }
          });
        }

        $( document ).ready(function() {
          
          $('#geticd10').keyup(function(){
            var isi = $(this).val();
            if(isi == ''){
              $('#icdcode').val('')
            }
          });

          CariICD();

          
        });
</script>



<?php if(!$_POST["btnExcel"]){ ?>
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
                  <h2>Laporan Tindakan</h2>
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

                    <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik / Ruangan</label>
                      <?php if ($userData["rol"] != '2') { ?>
                        <td width="20%" class="tablecontent">
                        <?php } else { ?>
                        <td width="20%" class="tablecontent">
                        <?php } ?>
                        <select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[ Pilih Klinik ]</option>
                          <?php for ($i = 0, $n = count($dataPoli); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataPoli[$i]["poli_id"]; ?>" <?php if ($dataPoli[$i]["poli_id"] == $_POST["id_poli"]) echo "selected"; ?>>
                              <?php echo $dataPoli[$i]["poli_nama"]; ?></option>
                          <?php } ?>
                        </select>

                    </div> -->

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
                      <select class="select2_single form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                        <option value="">[ Pilih Dokter ]</option>
                        <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                          <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_dokter"]) echo "selected"; ?>>
                            <?php echo $dataDokter[$i]["usr_name"]; ?></option>
                        <?php } ?>
                      </select>
                    </div>

                     <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pelaksana</label>
                      <select class="select2_single form-control" name="id_pelaksana" id="id_pelaksana" onKeyDown="return tabOnEnter(this, event);">
                        <option value="">[ Pilih Dokter ]</option>
                        <?php for ($i = 0, $n = count($dataDokter); $i < $n; $i++) { ?>
                          <option value="<?php echo $dataDokter[$i]["usr_id"]; ?>" <?php if ($dataDokter[$i]["usr_id"] == $_POST["id_pelaksana"]) echo "selected"; ?>>
                            <?php echo $dataDokter[$i]["usr_name"]; ?></option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>

                      <input class="form-control col-md-7 col-xs-12" type="text" id="cust_usr_kode" name="cust_usr_kode" size="15" maxlength="10" value="<?php echo $_POST["cust_usr_kode"]; ?>" />

                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Tindakan</label>

                      <input class="form-control col-md-7 col-xs-12" type="text" id="fol_nama" name="fol_nama" size="100" maxlength="255" value="<?php echo $_POST["fol_nama"]; ?>" />
                    </div>


                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
                      <?php if ($userData["rol"] != '2') { ?>
                        <td width="20%" class="tablecontent">
                        <?php } else { ?>
                        <td width="20%" class="tablecontent">
                        <?php } ?>
                        <select class="select2_single form-control" name="reg_status_pasien" id="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);">
                          <!--onChange="this.form.submit();" -->
                          <option value="">[ Pilih Jenis Pasien ]</option>
                          <option value="B" <?php if ('B' == $_POST["reg_status_pasien"]) echo "selected"; ?>>Baru</option>
                          <option value="L" <?php if ('L' == $_POST["reg_status_pasien"]) echo "selected"; ?>>Lama</option>
                        </select>

                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
                      <?php if ($userData["rol"] != '2') { ?>
                        <td width="20%" class="tablecontent">
                        <?php } else { ?>
                        <td width="20%" class="tablecontent">
                        <?php } ?>
                        <select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);">
                          <!--onChange="this.form.submit();" -->
                          <option value="0">[ Pilih Cara Bayar ]</option>
                          <?php for ($i = 0, $n = count($jenisPasien); $i < $n; $i++) { ?>
                            <option value="<?php echo $jenisPasien[$i]["jenis_id"]; ?>" <?php if ($jenisPasien[$i]["jenis_id"] == $_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"]; ?>');">
                              <?php echo ($i + 1) . ". " . $jenisPasien[$i]["jenis_nama"]; ?></option>
                          <?php } ?>
                        </select>

                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Petugas</label>

                      <select class="select2_single form-control" name="usr_id" onKeyDown="return tabOnEnter(this, event);">
                        <option value="--">[ Pilih Nama Petugas ]</option>
                        <?php for ($i = 0, $n = count($dataKasir); $i < $n; $i++) { ?>
                          <option value="<?php echo $dataKasir[$i]["usr_name"]; ?>" <?php if ($_POST["usr_id"] == $dataKasir[$i]["usr_name"]) echo "selected"; ?>>
                            <?php echo $dataKasir[$i]["usr_name"]; ?></option>
                        <?php } ?>
                      </select>

                    </div>

              <div class="col-md-4 col-sm-4 col-xs-4">
                <label class="control-label col-md-12">ICD 10</label>
                <input type="text" name="geticd10" class="form-control" value="<?php echo $_POST['geticd10'];?>" id="geticd10">
                <input type="hidden" name="icdcode" id="icdcode" value="<?php echo $_POST['icdcode'];?>">
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

        <?php } ?>
        
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <?php

                for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
                    if ($dataTable[$i]["id_pembayaran"] == $dataTable[$i - 1]["id_pembayaran"]) {
                      $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
                    }
                  }

                for ($i = 0, $n = count($dataTable); $i < $n; $i++) { 
                  $jml += $dataTable[$i]["fol_jumlah"];

                

                }

                  

                ?>

                 <h4 align="center">Jumlah Total Pasien <?php echo $jmlpx['count']; ?></h4>

                <h4 align="center">Jumlah Total Tindakan <?php echo $jml;; ?></h4>

                <div class="clearfix"></div>
              </div>
              <div class="x_content">

              <!-- <?php echo $table->RenderView($tbContent, $tbBottom); ?> -->

                <table id="table" width="100%" class="table table-striped table-bordered" border="1" align="left" cellpadding="1" cellspacing="1">
                  <thead>
                      <tr>
                        <td align="center" width="1%">No</td>
                        <td align="center" width="5%">Tanggal Registrasi</td>
                        <td align="center" width="5%">Waktu</td>
                        <td align="center" width="5%">No. RM</td>
                        <td align="center" width="10%">Nama</td>
                        <td align="center" width="10%">Tanggal lahir</td>
                        <td align="center" width="15%">Umur Pasien</td>
                        <td align="center" width="13%">Alamat</td>
                        <td align="center" width="10%">Penanggung Jawab</td>
                        <td>Diagnosa</td>
                        <td align="center" width="2%">Jenis Pasien</td>
                        
                        <td align="center" width="10%">Tindakan</td>
                        <td align="center" width="10%">Waktu Tindakan</td>
                        <td align="center" width="5%">Jumlah</td>
                        <td align="center" width="10%">Perawat</td>
                        <td align="center" width="10%">Pelaksana</td>
                        <td align="center" width="10%">Dokter Instruksi</td>
                        <td align="center" width="10%">Dokter Penanggung Jawab</td>
                        <td align="center" width="10%">Klinik</td>
                        <td align="center" width="10%">Nama Petugas</td>
                      </tr>
                  </thead>
                  <tbody>
                      <?php for ($i = 0, $n = count($dataTable); $i < $n; $i++) { 
                        $sql = "select usr_name, fol_pelaksana_tipe from klinik.klinik_folio_pelaksana b 
                        left join global.global_auth_user g on b.id_usr=g.usr_id 
                        left join klinik.klinik_folio a on a.fol_id=b.id_fol 
                        where b.id_fol=" . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_id"]) . " order by fol_pelaksana_tipe asc";
                        $pelaksana = $dtaccess->FetchAll($sql);

                        $indPerawat = '';
                        $indDokter = '';
                        $indInstruk = '';

                        for($y = 0; $y < count($pelaksana); $y++){
                          if($pelaksana[$y]['fol_pelaksana_tipe'] == '7'){
                            $indInstruk = $y;
                          }
                          else if($pelaksana[$y]['fol_pelaksana_tipe'] == '2'){
                            $indPerawat = $y;
                          }
                          else if($pelaksana[$y]['fol_pelaksana_tipe'] == '10'){
                            $indDokter = $y;
                          }
                        }

                        ?>
                      <tr>
                        <?php if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
                              $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]] + 1;

                              $umur = explode('~', $dataTable[$i]["cust_usr_umur"]); ?>
                              <td rowspan="<?=$dataSpan["jml_span"]?>" text-align="right"><?=$m + 1?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>" text-align="left"><?=format_date($dataTable[$i]["pembayaran_tanggal"])?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["reg_waktu"]?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["cust_usr_kode"]?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=($dataTable[$i]["cust_usr_kode"] == '500' || $dataTable[$i]["cust_usr_kode"] == '100') ? $dataTable[$i]["fol_keterangan"] : $dataTable[$i]["cust_usr_nama"]?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["cust_usr_tanggal_lahir"]?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$umur[0]. " Tahun ".$umur[1]. " Bulan ".$umur[2]." Hari"?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["cust_usr_alamat"]?></td>
                              <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["cust_usr_penanggung_jawab"]?></td>
                              <td  rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["icd_nomor"]." - ".$dataTable[$i]["icd_deskripsi"]?>
                          
                                </td>
                                 <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["jenis_nama"]?></td>

                        <?php 
                        $m++;
                        } 
                        if ($dataTable[$i]['is_cito'] == 'C') {
                          $cito = " ( CITO )";
                        } else {
                          $cito = "";
                        }
                        ?>
                        <td><?=$dataTable[$i]["fol_nama"]?></td>
                        <td><?=$dataTable[$i]["fol_waktu"]?></td>
                        <td text-align="right"><?=currency_format($dataTable[$i]["fol_jumlah"])?></td>

                        <td><?=$pelaksana[$indPerawat]["usr_name"]?></td>
                        <td><?=$pelaksana[$indDokter]["usr_name"]?></td>
                        <td><?=$pelaksana[$indInstruk]["usr_name"]?></td>
                        <td><?=$dataTable[$i]["dokter"]?></td>
                        <td><?=$dataTable[$i]["poli_nama"]?></td>
                        <?php if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) { ?>
                        <td rowspan="<?=$dataSpan["jml_span"]?>"><?=$dataTable[$i]["reg_who_update"]?></td>
                        <?php } ?>
                      </tr>
                      <?php
                      $jml+=$dataTable[$i]["fol_jumlah"]; } ?>
                      
                  </tbody>
                </table>
                
                <?php if(!$_POST["btnExcel"]){ ?>
              </div>
            </div>
          </div>

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
<?php } ?>
</html>