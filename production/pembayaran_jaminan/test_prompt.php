<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
   	 $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
     
     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  

     ?>

     <!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <!-- /////////////////// -->
  <body class="nav-sm">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
               <div class="page-title">
              <div class="title_left">
                <h3><?php echo $tableHeader; ?></h3>
              </div>
            </div>
            <?php //echo "$addpasien"; ?>
            <div class="clearfix"></div>
     <form id="form_irj" action="test_prompt.php" method="post">
     <div class="col-md-4 col-sm-6 col-xs-12">
          <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>                            
          <input type="button" name="btnLanjut" value="Lanjut" onclick="inputKwitansi('4da562b1d25532b23ab14b10e38cfefc');" class="pull-right btn btn-primary">
          </div>
     </form>
     <div class="clearfix"></div>
</div>
</div>
 <?php require_once($LAY."footer.php") ?>
</div>
</div>
<?php require_once($LAY."js.php") ?>
<script type="text/javascript">
          function inputKwitansi($bayardet){
              //var id_det = $bayardet;
                    $.messager.prompt('Silahkan input', 'Nomor Kwitansi:', function(r){
                         if (r){
                              //alert(r);
                              $.post('update_slip.php',{id_det:$bayardet,pembayaran_det_slip:r});
                              window.location.href='kasir_pemeriksaan_view.php';
                         }
                    });
               
          }
</script>
</body>
</html>