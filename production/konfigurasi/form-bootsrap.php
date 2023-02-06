<?php  require_once("../penghubung.inc.php"); require_once($ROOT."lib/tampilan.php"); $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']); ?>

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
            <div class="page-title">
              <div class="title_left">
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="row"> <!-- ==== BARIS ===== -->
			<!-- ==== kolom kiri ===== -->
			<!-- ==== mulai form ===== -->
			<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Sikita</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="kelas_nama" name="kelas_nama" value="<?php echo $_POST["kelas_nama"]?>" required="required" class="form-control col-md-7 col-xs-12">		
						</div>
                      </div>
					  
                  </div>
                </div>
              </div>
			  <!-- ==== // kolom kiri ===== -->
			  
			  <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Kelas Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="kelas_nama" name="kelas_nama" value="<?php echo $_POST["kelas_nama"]?>" required="required" class="form-control col-md-7 col-xs-12">
							
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tingkat Kelas<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <select id="kelas_tingkat" name="kelas_tingkat" class="form-control">
							<option value="">Pilih Tingkat</option>
							<option value="1" <?php if($_POST["kelas_tingkat"]=="1")echo "selected";?>>1</option>
							<option value="2" <?php if($_POST["kelas_tingkat"]=="2")echo "selected";?>>2</option>
							<option value="3" <?php if($_POST["kelas_tingkat"]=="3")echo "selected";?>>3</option>
							<option value="4" <?php if($_POST["kelas_tingkat"]=="4")echo "selected";?>>4</option>
							<option value="5" <?php if($_POST["kelas_tingkat"]=="5")echo "selected";?>>5</option>
						</select>  
  					    </div>
                      </div>
                  </div>
                </div>
			  <!-- ==== KHUSUS BUTTON ===== -->
                  <div class="x_content">
					<div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                  </div>
			  <!-- ==== // KHUSUS BUTTON ===== -->
              </div>
			</form>	<!-- ==== Akhir form ===== -->
			<!-- ==== // kolom kanan ===== -->
            </div> <!-- ==== // BARIS ===== -->
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