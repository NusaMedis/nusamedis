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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName(); 
     $depId = $auth->GetDepId();    
   	$auth = new CAuth();
     $err_code = 0;

     echo QuoteValue(DPE_CHAR,$depId);
     
     
    /*  if(!$auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/

	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["instalasi_id"])  $instalasiId = & $_POST["instalasi_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $instalasiId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from global.global_auth_instalasi a 
				where instalasi_id = ".QuoteValue(DPE_CHAR,$instalasiId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["instalasi_nama"] = $row_edit["instalasi_nama"];
          $_POST["instalasi_urut"] = $row_edit["instalasi_urut"];
          $_POST["instalasi_kode"] = $row_edit["instalasi_kode"];
          
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
               $instalasiId = & $_POST["instalasi_id"];
               $_x_mode = "Edit";
          } 
         
          
               $dbTable = "global.global_auth_instalasi";
               
               $dbField[0] = "instalasi_id";   // PK
               $dbField[1] = "instalasi_nama";
               $dbField[2] = "instalasi_kode";
               $dbField[3] = "id_dep";
               if($_POST["btnSave"]){
               $dbField[4] = "instalasi_urut";
               }
               
               $sql = "select instalasi_urut as max from global.global_auth_instalasi order by instalasi_urut desc";
               $rs = $dtaccess->Execute($sql);
               $maks = $dtaccess->Fetch($rs);
               $maxUrut = $maks["max"]+1;
			
               if(!$instalasiId) $instalasiId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$instalasiId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["instalasi_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["instalasi_kode"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
               if($_POST["btnSave"]){
               $dbValue[4] = QuoteValue(DPE_NUMERIC,$maxUrut);
               }

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
                  
               header("location:instalasi_view.php?konf=".$_POST["konf"]);
               exit();        
          
     }
 
     
     if ($_GET["del"]) {
          $instalasiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_auth_instalasi where instalasi_id = ".QuoteValue(DPE_CHAR,$instalasiId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:instalasi_view.php?konf=".$_POST["konf"]);
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
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Instalasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="instalasi_nama" name="instalasi_nama" value="<?php echo $_POST["instalasi_nama"]?>" required="required" class="form-control col-md-7 col-xs-12">
							
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kode<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="instalasi_kode" name="instalasi_kode" value="<?php echo $_POST["instalasi_kode"]?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("instalasi_id","instalasi_id",$instalasiId);?>
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