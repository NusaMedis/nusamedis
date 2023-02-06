<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
	   require_once($LIB."tampilan.php");		
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   $findPage = "akun_prk.php?";
	   $findPageBeban = "akun_prk_beban.php?";
 
     

     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
    $sql = "select * from klinik.klinik_biaya where biaya_id = ".QuoteValue(DPE_CHAR,$_GET['id']);
    $Tindakan = $dtaccess->Fetch($sql);

    if ($_POST['btnSave']) {
      $sql = "update klinik.klinik_biaya set tindakan_lab_urut = ".QuoteValue(DPE_CHAR,$_POST['tindakan_lab_urut'])." where biaya_id = ".QuoteValue(DPE_CHAR,$_POST['biaya_id']);
      $result = $dtaccess->Fetch($sql);

      header('Location:hasil_lab_view.php');
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
                    <h2>Edit Tindakan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <div class="col-md-3 col-sm-12 col-xs-12">
                          <label class="col-md-12 col-sm12 col-xs-12">Nama Tindakan</label>
                        </div>
                        <div class="col-md-9 col-sm-12 col-xs-12">
                          <input type="text" name="biaya_nama" class="form-control" value="<?php echo $Tindakan['biaya_nama'] ?>" readonly>
                          <input type="hidden" name="biaya_id" class="form-control" value="<?php echo $Tindakan['biaya_id'] ?>" readonly>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-md-3 col-sm-12 col-xs-12">
                          <label class="col-md-12 col-sm12 col-xs-12">Tindakan Lab Urut</label>
                        </div>
                        <div class="col-md-9 col-sm-12 col-xs-12">
                          <input type="text" name="tindakan_lab_urut" class="form-control" value="<?php echo $Tindakan['tindakan_lab_urut'] ?>">
                        </div>
                      </div>  
                      <div class="form-group">
                        <div class="col-md-3 col-sm-12 col-xs-12">
                          <input type="submit" name="btnSave" class="btn btn-primary" value="Simpan">
                        </div>
                      </div>
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
