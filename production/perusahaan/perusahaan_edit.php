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
    
     //if ($_GET["tambah"]) {
     //$_POST["klinik"] = $_GET["tambah"]; 
     //   //echo $_POST["klinik"];
     //}
	   $backPage = "perusahaan_view.php";
	   $thisPage = "perusahaan_edit.php";
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
  
     	$lokasi = $ROOT."/gambar/img_cfg";
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $perusahaanid = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from global.global_perusahaan  
		  where perusahaan_id = ".QuoteValue(DPE_CHAR,$perusahaanid);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          //	echo $sql;
            $_POST["perusahaan_id"] = $row_edit["perusahaan_id"];
            $_POST["perusahaan_nama"] = $row_edit["perusahaan_nama"]; 
            $_POST["perusahaan_kode"] = $row_edit["perusahaan_kode"]; 
            $_POST["perusahaan_alamat"] = $row_edit["perusahaan_alamat"]; 
            $_POST["perusahaan_diskon"] = $row_edit["perusahaan_diskon"]; 
            $_POST["perusahaan_plafon"] = $row_edit["perusahaan_plafon"]; 
            
            $kembali = "perusahaan_edit.php?kembali=".$_POST["departemen_id"];
          
          $view->CreatePost($row_edit);



     }
     

     
	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $perusahaanid = & $_POST["perusahaan_id"];
               $_x_mode = "Edit";
          }
		
          if ($err_code == 0) {
               $dbTable = "global.global_perusahaan";
               
               $dbField[0] = "perusahaan_id";   // PK
               $dbField[1] = "perusahaan_nama"; 
               $dbField[2] = "perusahaan_kode";   
               $dbField[3] = "perusahaan_alamat"; 
               $dbField[4] = "perusahaan_diskon";             
               $dbField[5] = "perusahaan_plafon";             
			       
		 if(!$perusahaanid) $perusahaanid = $dtaccess->GetTransId();
               $dbValue[0] = QuoteValue(DPE_CHAR,$perusahaanid);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["perusahaan_nama"]);        
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["perusahaan_kode"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["perusahaan_alamat"]);
               $dbValue[4] = QuoteValue(DPE_NUMERIC,Stripcurrency($_POST["perusahaan_diskon"]));
               $dbValue[5] = QuoteValue(DPE_NUMERIC,Stripcurrency($_POST["perusahaan_plafon"]));
              
              //print_r($dbValue); die(); 
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          		
               if ($_POST["btnSave"]) {
                 $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                 $dtmodel->Update() or die("update  error");	
               }
          		
                 $simpan=1;
          		
          	  unset($dtmodel);
          	  unset($dbField);
          	  unset($dbValue);
          	  unset($dbKey);
               

          header("location:".$backPage);
          exit();
                  
          }
     }
 
    
     if ($_GET["del"]) {
         
          $perusahaanid = $enc->Decode($_GET["id"]);
          $sql = "delete from global.global_perusahaan
		  where perusahaan_id = '".$perusahaanid."'"; 
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    	//	echo $perusahaanid;
       // die();	
    			
          header("location:".$backPage);
          exit();  
          
  }
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <header>
  	<head>
  		<title>Setup</title>
  	</head>
  </header>
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

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Perusahaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Perusahaan<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" name="perusahaan_nama" id="perusahaan_nama" maxlength="255" class="form-control col-md-7 col-xs-12" value="<?php echo $_POST["perusahaan_nama"];?>">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kode Perusahaan<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" name="perusahaan_kode" id="perusahaan_kode" maxlength="255" class="form-control col-md-7 col-xs-12" value="<?php echo $_POST["perusahaan_kode"];?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Alamat Perusahaan<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" name="perusahaan_alamat" id="perusahaan_alamat" maxlength="255" class="form-control col-md-7 col-xs-12" value="<?php echo $_POST["perusahaan_alamat"];?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Diskon (%)<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("perusahaan_diskon","perusahaan_diskon","50","50",$_POST["perusahaan_diskon"],"inputField", null,true);?>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Plafon<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("perusahaan_plafon","perusahaan_plafon","50","50",currency_format($_POST["perusahaan_plafon"]),"inputField", null,true);?>
                        </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>      
        				  <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='perusahaan_view.php';\"");?>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("perusahaan_id","perusahaan_id",$perusahaanid);?>
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