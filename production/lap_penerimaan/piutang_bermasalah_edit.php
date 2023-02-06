<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."expAJAX.php");  
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depLowest = $auth->GetDepLowest();
     $err_code = 0;
     $tableHeader = "&nbsp;SETUP JAM JADWAL";
     
     $viewPage = "jam_jadwal_view.php";
     $editPage = "jam_jadwal_edit.php";
	
	
	    
  $sql = "select * from gl.gl_buffer_transaksi where id_tra = ".QuoteValue(DPE_CHAR,$_GET['id']);
  $dataPiutang = $dtaccess->Fetch($sql);

  $sql = "select * from gl.gl_buffer_transaksidetil where tra_id = ".QuoteValue(DPE_CHAR,$_GET['id'])." and prk_id = '01010101010106'";
  $Nominal = $dtaccess->Fetch($sql);

  if ($_POST['btnSave']) {
    $sql = "update gl.gl_buffer_transaksi set id_pembayaran_det = ".QuoteValue(DPE_CHAR,$_POST['id_pembayaran_det'])." where id_tra = ".QuoteValue(DPE_CHAR,$_POST['id_tra']);
    // echo $sql; die();
    $result = $dtaccess->Execute($sql);
    header('Location:piutang_bermasalah.php');
  }

?>





<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        
        <?php require_once($LAY."sidebar.php"); ?>
        <!-- top navigation -->
        <?php require_once($LAY."topnav.php"); ?>
        <!-- /top navigation -->
        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Edit Jurnal Penerimaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Jurnal <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="ref_tra" name="ref_tra" value="<?php echo $dataPiutang["ref_tra"];?>" class="form-control col-md-7 col-xs-12">
                        <input type="hidden" id="id_tra" name="id_tra" value="<?php echo $dataPiutang["id_tra"];?>" class="form-control col-md-7 col-xs-12">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tanggal Tra <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="tanggal_tra" name="tanggal_tra" value="<?php echo $dataPiutang["tanggal_tra"];?>" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Keterangan <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="ket_tra" name="ket_tra" value="<?php echo $dataPiutang["ket_tra"];?>" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nominal <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="ket_tra" name="ket_tra" value="<?php echo $Nominal["jumlah_trad"];?>" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Id Pembayaran Det <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="id_pembayaran_det" name="id_pembayaran_det" value="<?php echo $dataPiutang["id_pembayaran_det"];?>" required="required" class="form-control col-md-7 col-xs-12">
                  </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <input type="submit" name="btnSave" value="Lanjut" class="pull-right btn btn-primary">
                  </div>
                </div>
                <?php echo $view->RenderHidden("jam_jadwal_id","jam_jadwal_id",$jamJadwalId);?>
                <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /page content -->
  <!-- footer content -->
  <?php require_once($LAY."footer.php") ?>
  <!-- /footer content -->
</div>
</div>
<?php require_once($LAY."js.php") ?>
</body>
</html>