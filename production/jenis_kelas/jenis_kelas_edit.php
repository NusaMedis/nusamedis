<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
	 require_once($LIB."tampilan.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama(); 
	$userName = $auth->GetUserName();
     
    /* if(!$auth->IsAllowed("man_medis_det_kat_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_det_kat_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["jenis_kelas_id"])  $jeniskelasId = & $_POST["jenis_kelas_id"];
     
     $backPage = "jenis_kelas_view.php";

     $tableHeader = "&nbsp;Jenis Kelas";

	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $jeniskelasId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from klinik.klinik_jenis_kelas  where jenis_kelas_id= ".QuoteValue(DPE_CHAR,$jeniskelasId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
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
               $jeniskelasId = & $_POST["jenis_kelas_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_jenis_kelas";
               
               $dbField[0] = "jenis_kelas_id";   // PK
               $dbField[1] = "jenis_kelas_nama";
        	   $dbField[2] = "jenis_kelas_kode";


			
               if(!$jeniskelasId) $jeniskelasId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$jeniskelasId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jenis_kelas_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["jenis_kelas_kode"]);
             


			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:".$backPage);
               exit();        
          }
     }
        if ($_GET["del"]) {
          $jeniskelasId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_jenis_kelas where jenis_kelas_id = ".QuoteValue(DPE_CHAR,$jeniskelasId);
           $dtaccess->Execute($sql);
    
    
          header("location:".$backPage);
          exit();    
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
            <div class="page-title">
              <div class="title_left">
                <h3>Managemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Master Jenis Kelas</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Jenis Kelas</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <?php echo $view->RenderTextBox("jenis_kelas_nama","jenis_kelas_nama","50","100",$_POST["jenis_kelas_nama"],"inputField", null,false);?> 
                        </div>
                      </div>
                      
                      <div class="ln_solid"></div>
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kode Jenis Kelas</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <?php echo $view->RenderTextBox("jenis_kelas_kode","jenis_kelas_kode","50","100",$_POST["jenis_kelas_kode"],"inputField", null,false);?> 
                        </div>
                      </div>
                      
                                     
                      
                      <div class="ln_solid"></div>                      
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                        <script>document.frmEdit.kelompok_nomor.focus();</script>
						<?php echo $view->RenderHidden("jenis_kelas_id","jenis_kelas_id",$jeniskelasId);?>
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
