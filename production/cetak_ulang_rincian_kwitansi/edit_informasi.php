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
$depId = $auth->GetDepId();
$userId = $auth->GetUserId();
$userName = $auth->GetUserName();
$depNama = $auth->GetDepNama();


  $sql = "select reg_keterangan,perusahaan_nama,a.reg_jenis_pasien, a.reg_tipe_rawat, a.reg_tipe_jkn, a.id_poli, a.id_dokter, a.id_cust_usr, a.id_perusahaan,
          a.id_jamkesda_kota, a.reg_tipe_layanan, a.id_poli, a.reg_tipe_paket, 
          a.reg_tipe_layanan, a.reg_shift, b.pembayaran_dijamin, c.cust_usr_id, 
          c.cust_usr_alamat, c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_jenis_kelamin, 
          c.cust_usr_foto,  ((current_date - c.cust_usr_tanggal_lahir)/365) as umur, c.cust_usr_jkn,   
          d.fol_keterangan, e.perusahaan_diskon, e.perusahaan_plafon, f.* from  
          klinik.klinik_registrasi a 
          left join klinik.klinik_pembayaran b on b.pembayaran_id = a.id_pembayaran 
          join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
          left join klinik.klinik_folio d on d.id_reg=a.reg_id
          left join global.global_perusahaan e on e.perusahaan_id=a.id_perusahaan
          left join global.global_jamkesda_kota f on f.jamkesda_kota_id=a.id_jamkesda_kota
          where a.reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]) . " and a.id_dep =" . QuoteValue(DPE_CHAR, $depId);
  $rs_pasien = $dtaccess->Execute($sql);
  $dataPasien = $dtaccess->Fetch($sql);
  // echo $sql;

  if ($_POST['btnOk']) {
  	$sql = "update klinik.klinik_folio set fol_keterangan = ".QuoteValue(DPE_CHAR,$_POST['fol_keterangan'])." where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['id_pembayaran']);
  	// echo $sql;die();
  	$result = $dtaccess->Execute($sql);

    $sql = "update klinik.klinik_registrasi set reg_keterangan = ".QuoteValue(DPE_CHAR,$_POST['fol_keterangan'])." where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['id_pembayaran']);
    $result = $dtaccess->Execute($sql);
  	header("Location:cetak_view_pemeriksaan.php");
  }

  $tableHeader = 'Ubah Data Kwitansi';
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php"); ?>
<!-- <body  onLoad="GantiPembulatan('<?php echo $_POST["txtBiayaPembulatan"]; ?>','<?php echo $grandTotalHarga; ?>')"; >-->

<body class="nav-md" onload="GantiDiskon()">
  <div class="container body">
    <div class="main_container">

      <?php require_once($LAY . "sidebar.php"); ?>

      <!-- top navigation -->
      <?php require_once($LAY . "topnav.php"); ?>
      <!-- /top navigation -->

      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">

          <div class="clearfix"></div>
          <div class="row">
            <!-- ==== BARIS ===== -->
            <!-- ==== kolom kiri ===== -->
            <!-- ==== mulai form ===== -->
            <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
              <div class="col-md-6 col-sm-6 col-xs-12">

                <!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No. RM
                      </label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_kode"]; ?>">
                      </div>
                    </div>
                    <?php if ($dataPasien["id_cust_usr"] == '100' || $dataPasien["id_cust_usr"] == '500') { ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input readonly type="text" class="form-control" value="<?php echo $dataPasien["fol_keterangan"]; ?>">
                        </div>
                      </div>
                    <?php } else { ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_nama"]; ?>">
                        </div>
                      </div>
                    <?php } ?>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat
                      </label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_alamat"]; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Sudah Terima Dari
                      </label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <!-- <input type="text" class="form-control" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $dataPasien["reg_keterangan"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" /> -->
                        <textarea name="fol_keterangan" class="form-control"><?php echo $dataPasien['reg_keterangan']; ?></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Perusahaan
                      </label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" class="form-control" name="perusahaan_nama" id="perusahaan_nama" readonly size="45" maxlength="45" value="<?php echo $dataPasien["perusahaan_nama"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">&nbsp;</label>
                    	<input type="submit" name="btnOk" value="Simpan" class="btn btn-primary">
                    	<input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $_GET['id_pembayaran']; ?>">
                    </div>
                  </div>
                </div>
                

              </div>
              <!-- ==== // kolom kiri ===== -->

              <!-- ==== // KHUSUS BUTTON ===== -->
          </div>
        </div>
        </form> <!-- ==== Akhir form ===== -->
        <!-- ==== // kolom kanan ===== -->
      </div> <!-- ==== // BARIS ===== -->
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