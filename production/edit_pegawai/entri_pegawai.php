<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
	    require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
 
     $enc = new TextEncrypt();
     $auth = new CAuth();

     $thnskr = date("Y");
     $depId = $auth->GetDepId();
     $anggarNama = $auth->GetAnggarName();
     $anggarId = $auth->GetAnggarId();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName(); 

    /* if(!$auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ) && !$auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ)===1 || $auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
      
	
    $backPage = "data_pegawai_view.php?kembali=yes";
        
    $lokasi = $ROOT."gambar/foto_pegawai";
     if($_POST["pgw_foto"]) $fotoName = $lokasi."/".$_POST["pgw_foto"];
     else $fotoName = $lokasi."/default.jpg";
	
	// ----- update data ----- //
	if($_POST["btnSave"]) {
		
		
		// ---- Checking Data ---- //	
              
               $dbTable = "hris.hris_pegawai";
               $dbField[0] = "pgw_id";   // PK
               $dbField[1] = "pgw_nip";
               $dbField[2] = "pgw_nama";
               $dbField[3] = "pgw_alamat";
     		   $dbField[4] = "id_struk";
    		   $dbField[5] = "id_dep";
               $dbField[6] = "pgw_telp_hp";      
        
        	   if(!$_POST["id_struk"] || $_POST["id_struk"]=="--") $_POST["id_struk"] = 'null';
        	   if(!$_POST["id_dep"] || $_POST["id_dep"]=="--") $_POST["id_dep"] = 'null';
			
               if(!$pgwId) $pgwId = $dtaccess->GetTransId("hris.hris_pegawai","pgw_id",DB_SCHEMA_GLOBAL);
			
               $dbValue[0] = QuoteValue(DPE_CHAR,$pgwId);
        	 	$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pgw_nip"]);
        		$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pgw_nama"]);
        		$dbValue[3] = QuoteValue(DPE_CHAR,$_POST["pgw_alamat"]);
        		$dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_struk"]);
        		$dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
                $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["pgw_telp_hp"]);    

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
               
               $dtmodel->Insert() or die("insert  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

	//		$statusSave=1;
                
      $kembali = "data_pegawai_view.php?kembali=yes";
      header("location:".$kembali);
      exit();
	}
     
	// --- cari agama ---
	// --- cari unit_kerja ---
		$sql = "select * from hris.hris_struktural order by struk_tree";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		$dataDep = $dtaccess->FetchAll($rs);
		$dtaccess->Clear($rs);  



?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

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
              
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?php echo $tableHeader;?></h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li class="dropdown">
        
                    </ul>
                    <div class="clearfix"></div>
                  </div>

                  <div class="x_content">
					
            <div class="row"> <!-- ==== BARIS ===== -->
			<!-- ==== kolom kiri ===== -->
			<!-- ==== mulai form ===== -->
			<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Registrasi Kepegawaian</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nomor Induk Pegawai
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" name="pgw_nip" id="pgw_nip" class="form-control" value="<?php echo $_POST["pgw_nip"];?>" required="required">
						            </div>
                      </div>                  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Pegawai
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" name="pgw_nama" id="pgw_nama" class="form-control" value="<?php echo $_POST["pgw_nama"];?>" required="required">
						            </div>
                      </div>                  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat Pegawai
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" name="pgw_alamat" id="pgw_alamat" class="form-control" value="<?php echo $_POST["pgw_alamat"];?>" required="required">
						            </div>
                      </div>    
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No. HP
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" name="pgw_telp_hp" id="pgw_telp_hp" class="form-control" value="<?php echo $_POST["pgw_telp_hp"];?>" required="required">
						            </div>
                      </div>               
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Unit Kerja
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                              <select class="form-control" name="id_struk" id="id_struk" onKeyDown="return tabOnEnter(this, event);">								
                      				<option class="inputField" value="" >- Pilih Departemen -</option>
                      				<?php $counter = -1;
                      					for($i=0,$n=count($dataDep);$i<$n;$i++){
                      					unset($spacer); 
                      					$length = (strlen($dataDep[$i]["struk_tree"])/TREE_LENGTH)-1; 
                      					for($j=0;$j<$length;$j++) $spacer .= "....";
                      				?>
                      					<option class="inputField" value="<?php echo $dataDep[$i]["struk_id"];?>"<?php if ($_POST["id_struk"]==$dataDep[$i]["struk_id"]) echo"selected"?>><?php echo $spacer." ".$dataDep[$i]["struk_nama"];?>&nbsp;</option>
                      				<?php } ?>
                          	  </select>
						            </div>
                      </div>                  

      <?php echo $view->RenderHidden("dep_id","dep_id",$depId);?>
      <?php echo $view->RenderHidden("pgw_id","pgw_id",$pgwId);?>
      <?php echo $view->RenderHidden("nama","nama",$_POST["nama"]);?>
                  </div>                  
                  </div>

			  <!-- ==== KHUSUS BUTTON ===== -->
                  <div class="x_content">
					<div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <input name="btnSave" class="btn btn-default pull-right" type="submit" value="Simpan">
                            <input name="btnKembali" onClick="document.location.href='<?php echo $backPage;?>'" class="btn btn-default pull-right" type="button" value="Kembali"> 
                        </div>
                      </div>
                  </div>
                  
              </div>
			</form>	<!-- ==== Akhir form ===== -->
			<!-- ==== // kolom kanan ===== -->
            </div> <!-- ==== // BARIS ===== -->





                    </div>
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

