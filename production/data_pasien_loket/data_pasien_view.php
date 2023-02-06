<?php

/** LIBRARY */
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

/** INISIALISASI LIBRARY */
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$depLowest = $auth->GetDepLowest();
$table = new InoTable("table1", "100%", "left", null, 1, 2, 1, null);
$editPage = "pasien_edit.php";
$thisPage = "pasien_view.php";
$regPage = "kedatangan_pasien.php";
$PageJenisBiaya = "page_jenis_biaya.php";
//$depNama = $auth->GetDepNama(); 
$userName = $auth->GetUserName();

if (strlen(str_replace('_', '', $_GET["find_tgl_lahir"])) == 10) {
  $tgl = date_format(date_create($_GET['find_tgl_lahir']), 'Y-m-d');
} else {
  $tgl = date('Y-m-d');
}
/** AUTHENTIKASI  */
if (!$auth->IsAllowed("man_ganti_password", PRIV_READ)) {
  die("access_denied");
  exit(1);
} elseif ($auth->IsAllowed("man_ganti_password", PRIV_READ) === 1) {
  echo "<script>window.parent.document.location.href='" . $MASTER_APP . "login/login.php?msg=Session Expired'</script>";
  exit(1);
}

/** AUTHENTIKASI CRUD */
$isAllowedDel = $auth->IsAllowed("man_ganti_password", PRIV_DELETE);
// $isAllowedUpdate = $auth->IsAllowed("man_ganti_password", PRIV_UPDATE);
// $isAllowedCreate = $auth->IsAllowed("man_ganti_password", PRIV_CREATE);

/** DEKLARASI LINK*/
$editPage         = "data_pasien_edit.php?";
$kartuPage         = "cetak_kartu.php?";
$thisPage         = "data_pasien_view.php";

/** FILTER */
if ($_GET["cust_usr_kode"])  $sql_where[] = "cust_usr_kode like" . QuoteValue(DPE_CHAR, "%" . $_GET["cust_usr_kode"] . "%");
if ($_GET["cust_usr_nama"])  $sql_where[] = "upper(cust_usr_nama) like" . QuoteValue(DPE_CHAR, strtoupper(str_replace("'", "*", $_GET["cust_usr_nama"])) . "%");
if ($_GET["find_alamat"])  $sql_where[] = "UPPER(cust_usr_alamat) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_GET["find_alamat"]) . "%");
if ($_GET["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =" . QuoteValue(DPE_CHAR, $tgl);
// $sql_where[] = "cust_usr_nama is not null";
$sql_where[] = "cust_usr_kode <> '500'";
if ($sql_where[0])  $sql_where = implode(" and ", $sql_where);

/** PAGINATION */
$recordPerPage = 20;
if ($_GET["currentPage"]) $currPage = $_GET["currentPage"];
else $currPage = 1;
$startPage = ($currPage - 1) * $recordPerPage;
$endPage = $startPage + $recordPerPage;

/** SQL DATA PASIEN*/
$sql = "select a.cust_usr_id,a.cust_usr_kode,a.cust_usr_nama,a.cust_usr_alamat,a.cust_usr_tanggal_lahir ,a.id_dep from global.global_customer_user a";
$sql .= " where 1=1 and a.id_dep ='$depId'";
$sql .= " and " . $sql_where;
$sql .= " order by a.cust_usr_kode desc";
$rs = $dtaccess->Query($sql, $recordPerPage, $startPage);
$dataTable = $dtaccess->FetchAll($rs);

/**SQL BANYAK-NYA PASIEN */
$sql = "select count(cust_usr_id) as total from global.global_customer_user a where a.id_dep= '$depId'";
if ($sql_where) $sql .= " and 1=1 and " . $sql_where;
$rsNum = $dtaccess->Execute($sql);
$numRows = $dtaccess->Fetch($rsNum);

// echo $sql;

/**JUDUL TABEL */
$tableHeader = "Rekam Medik - Data Pasien";

/** BUTTON CREATE */
//if ($isAllowedCreate) {
 $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\'' . $thisPage."?reg_status_pasien=B" . '\'"></button>';
//}

/** SQL LOAD DATA KELAS */
$sql = "select * from klinik.klinik_kelas where id_dep= '$depId' order by kelas_id";
$rs = $dtaccess->Execute($sql);
$dataKelas = $dtaccess->FetchAll($rs);

$kelas[0] = $view->RenderOption("", "Pilih Semua Kelas", $show);
for ($i = 0, $n = count($dataKelas); $i < $n; $i++) {
  unset($show);
  if ($_GET["id_kelas"] == $dataKelas[$i]["kelas_id"]) $show = "selected";
  $kelas[$i + 1] = $view->RenderOption($dataKelas[$i]["kelas_id"], $dataKelas[$i]["kelas_nama"], $show);
}
      if($_GET["btnExcel"]){
        
        $_x_mode = "excel";
      }  

      $sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs from global.global_departemen";
$konf = $dtaccess->Fetch($sql);
$norm_depan = $konf['dep_konf_reg_no_rm_depan'];

if ($_GET['reg_status_pasien'] == "B") {
  require_once("data_pasien_kode.php");
  $usr_kode = $_POST["kode_pasien"];


  $rm =substr( $_POST["kode_pasien"],0,2);
  // $arr = str_split($usr_kode, "2");
  $usr_kode_tampilan = $rm.".".substr( $_POST["kode_pasien"],2);

  $dbTable = "global.global_customer_user";
  $dbField[0] = "cust_usr_id";   // PK  
  if ($norm_depan == 'y') {
    $dbField[1] = "cust_usr_kode";
    $dbField[2] = "cust_usr_kode_tampilan";
  }

  $custUsrId = $dtaccess->GetTransID();
  $dbValue[0] = QuoteValue(DPE_CHAR, $custUsrId);
  if ($norm_depan == 'y') {
    $dbValue[1] = QuoteValue(DPE_CHAR, $usr_kode);
    $dbValue[2] = QuoteValue(DPE_CHAR, $usr_kode_tampilan);
  }
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
  $dtmodel->Insert() or die("insert  error");

  // die();
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

  if ($_GET['bpjs'] == "0") {
    $statusJKN = "bpjs=true&noKartu=" . $_GET['noKartu'];
  } else {
    $statusJKN = "bpjs=false";
  };
    $x=substr($_POST["cust_usr_kode"], 2);
  $y=substr($_POST["cust_usr_kode"], 2,5);
  $thn=substr(date('Y'), -2);
  $z=str_replace("20", $thn, $x);
                    // echo $thn; 
  $_POST["cust_usr_kode"]=str_replace(substr($_POST["cust_usr_kode"],0, 2), $thn, $_POST["cust_usr_kode"]);
  $_POST["cust_usr_kode"] = $_POST["kode_pasien"];
  // echo $_POST["cust_usr_kode"]." ".$usr_kode.$custUsrId;

  header("location:" .  $editPage . 'id=' . $enc->Encode($custUsrId)) ;
  exit();
}



?>
<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <!-- SIDEBAR -->
      <?php require_once($LAY . "sidebar.php") ?>
      <!-- END SIDEBAR -->
      <!-- TOP NAVIGATION -->
      <?php require_once($LAY . "topnav.php") ?>
      <!-- END TOP NAVIGATION -->
      <!-- CONTENT -->
      <div class="right_col" role="main">
        <div class="">
          <div class="page-title">
            <div class="title_left">
              <h3>&nbsp;</h3>
            </div>
          </div>
          <div class="clearfix"></div>
          <!-- FILTER -->
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Filter</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form name="frmFind" method="GET" action="<?php echo $_SERVER["PHP_SELF"] ?>">

                    <div class="col-md-3 col-sm-3 col-xs-3">
                      <!-- Filter Kode Pasien -->
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
                      <div class='input-group col-md-12 col-sm-12 col-xs-12'>
                        <input type="text" name="cust_usr_kode" id="cust_usr_kode" class="form-control" value="<?= $_GET['cust_usr_kode'] ?>" placeholder="">
                      </div>
                      <!-- Filter Kode Pasien -->
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-3">
                      <!-- Filter Nama Pasien -->
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                      <div class='input-group col-md-12 col-sm-12 col-xs-12'>
                        <input type="text" name="cust_usr_nama" id="cust_usr_nama" class="form-control" value="<?= str_replace("*", "'", $_GET['cust_usr_nama']) ?>" placeholder="">
                      </div>
                      <!-- Filter Nama Pasien -->
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-3">
                      <!-- Filter Alamat Pasien -->
                      <div class='input-group col-md-12 col-sm-12 col-xs-12'>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat Pasien</label>
                        <input type="text" name="cust_usr_alamat" id="cust_usr_alamat" class="form-control" value="<?= $_GET['cust_usr_alamat'] ?>" placeholder="">
                      </div>
                      <!-- Filter Alamat Pasien -->
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-3">
                      <!-- Filter Tanggal Lahir -->
                      <div class='input-group col-md-12 col-sm-12 col-xs-12'>
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tanggal Lahir</label>
                        <input type="text" class="form-control" id="find_tgl_lahir" name="find_tgl_lahir" data-inputmask="'mask': '99-99-9999'" value="<?= $_GET['find_tgl_lahir']; ?>" />
                      </div>
                      <!-- Filter Tanggal Lahir -->
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-3 pull-right">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <input type="submit" name="btnLanjut" value="Cari" class="btn btn-primary form-control">
                       
                    </div>
                    <?php   if ($auth->IsAllowed("man_user_edit_pegawai", PRIV_READ)) {  ?>
                    <div class="col-md-3 col-sm-3 col-xs-3 pull-right">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                     
                         
                      <input type="submit" name="btnExcel" id="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                   
                    </div>
                   <?php }?>

                    <div class="clearfix"></div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- END FILTER -->
       
          <!-- Data View Pasien Row 1-->
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Data Pasien</h2>
                  <?php //if ($userName == 'administrator') { 
                  ?>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <?php //} 
                  ?>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
               
                  <!-- TABLE VIEW -->
                  <table width="100%" id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" border="1">
                    <thead>
                      <tr>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>Alamat</th>
                        <th>Tanggal Lahir</th>
                         <th>Cetak Kartu Pasien</th>
                        <th>Edit</th>
                        <th>Hapus</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($dataTable as $key => $value) : ?>
                        <tr>
                          <td><?= $value['cust_usr_kode'] ?></td>
                          <td><?= str_replace("*", "'", $value['cust_usr_nama']) ?></td>
                          <td><?= $value['cust_usr_alamat'] ?></td>
                          <td><?= format_date($value["cust_usr_tanggal_lahir"]) ?></td>
                          <td><?php //if ($isAllowedUpdate)
                                echo '<a href="' . $kartuPage . 'id=' .$value["cust_usr_id"] . '" target="_blank"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/cetak.png" alt="Edit" title="Edit" border="0" ></a>'; ?>
                          </td>
                          <td>
                            <?php
                           //   if (is_null($value['cust_usr_nama']) or is_null($value['cust_usr_nama'])) {
                              
                           // }
                           // else{
                            
                           // }
                             echo '<a href="' . $editPage . 'id=' . $enc->Encode($value["cust_usr_id"]) . '"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';



                            ?>

                          </td>
                         <td><?php
                        //   if ($auth->IsAllowed("man_pengaturan_konfigurasi", PRIV_READ)) {
                        //     $sql = "select reg_id,b.id_poli,reg_tanggal,jenis_nama, reg_kode_trans, cust_usr_nama,cust_usr_kode, poli_nama, reg_tipe_rawat, usr_name, b.reg_waktu
                        //     from global.global_customer_user a 
                        //     left join klinik.klinik_registrasi b on a.cust_usr_id=b.id_cust_usr 
                        //     left join global.global_auth_poli c on c.poli_id=b.id_poli
                        //     left join global.global_jenis_pasien d on d.jenis_id=b.reg_jenis_pasien
                        //     left join global.global_auth_user e on e.usr_id = b.id_dokter";
                        // $sql .= " WHERE b.id_poli!='33' and reg_utama is null and b.id_poli!='b1b99707e536adf5e57daede3576bb0f' and cust_usr_id=" . QuoteValue(DPE_CHAR,$value['cust_usr_id']);
                        // $sql .= " order by reg_tanggal desc limit 5";

                        if ($auth->IsAllowed("man_pengaturan_konfigurasi", PRIV_READ)) {
                            $sql = "select reg_id,b.id_poli,reg_tanggal,jenis_nama, reg_kode_trans, cust_usr_nama,cust_usr_kode, poli_nama, reg_tipe_rawat, usr_name, b.reg_waktu
                            from global.global_customer_user a 
                            left join klinik.klinik_registrasi b on a.cust_usr_id=b.id_cust_usr 
                            left join global.global_auth_poli c on c.poli_id=b.id_poli
                            left join global.global_jenis_pasien d on d.jenis_id=b.reg_jenis_pasien
                            left join global.global_auth_user e on e.usr_id = b.id_dokter";
                        $sql .= " WHERE cust_usr_id=" . QuoteValue(DPE_CHAR,$value['cust_usr_id']);
                        $sql .= " order by reg_tanggal desc limit 5";
                        //echo $sql;
                        $rs = $dtaccess->Execute($sql);
                           $row = $dtaccess->FetchAll($rs);
                           // echo $sql;
                           $jml=count($row);
                          //  echo $jml;
                           if($jml==0){
                            echo '<a href="' . $editPage . '&id=' . $enc->Encode($value["cust_usr_id"]) . '&del=1"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';

                           }

                          //  echo $sql;
                          //  echo $jml;
                         }
                                
                              ?>
                          </td> 

                        </tr>
                      <?php endforeach ?>
                    </tbody>
                    <!-- <tfoot>
                        <tr>
                            <td colspan="2"><strong>Total Pasien : <?php echo $numRows["total"]; ?></strong></td>
                            <td colspan="4"><strong><?php echo $view->RenderPaging($numRows["total"], $recordPerPage, $currPage) ?></strong></td>
                          </tr>
                      </tfoot> -->
                  </table>
               

                </div>
              </div>
            </div>
          </div>
          <!-- END TABEL VIEW -->
            
        </div>
      </div>
      <!-- /page content -->

      <!-- footer content -->
      <?php require_once($LAY . "footer.php") ?>
      <!-- /footer content -->
    </div>
  </div>
  <!-- jQuery -->

</body>

</html>




<script>

<?php if($_x_mode=="cetak"){ ?> 
  window.open('lap_obat_diare_view_cetak.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&cust_usr_kode=<?php echo $_POST["cust_usr_kode"];?>&cust_usr_nama=<?php echo $_POST["cust_usr_nama"];?>&cetak=y', '_blank');
<?php } ?>

<?php if($_x_mode=="excel"){ ?> 
  window.open('data_pasien_excel.php?tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&cust_usr_kode=<?php echo $_POST["cust_usr_kode"];?>&cust_usr_nama=<?php echo $_POST["cust_usr_nama"];?>&excel=y', '_blank');
<?php } ?>

</script>
