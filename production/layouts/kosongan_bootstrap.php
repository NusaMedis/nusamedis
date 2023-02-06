<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
	 
	//INISIALISAI AWAL LIBRARY
     $auth = new CAuth();
	 $userName = $auth->GetUserName();

     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	 //tabel header
     $tableHeader = "";
	 
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  	<script type="text/javascript">
	</script>
	
  <body class="nav-md">
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
            <div class="clearfix"></div>
					
			<!-- insert ke folio sebaai data awal -->
			<form method="POST" action="proses_registrasi.php">
			 <!-- BARIS 1 -->
            <div class="row">             
			<!-- KOLOM KIRI -->
              <div class="col-md-4 col-sm-4 col-xs-12"> 
				<div class="x_panel">
                  <div class="x_content">
                  </div>
                </div>
              </div>
			  <!-- KOLOM KANAN -->
              <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="x_panel"> 
                  <div class="x_content">
                  </div>
                </div>
              </div>
            </div>
			</form>

			 <!-- BARIS 2 -->
            <div class="row">             
              <div class="col-md-12 col-sm-12 col-xs-12"> 
				<div class="x_panel">
                  <div class="x_content">
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
<!-- jQuery -->
<?php require_once($LAY."js.php") ?>
	<script type="text/javascript">
	</script>

  </body>
</html>           