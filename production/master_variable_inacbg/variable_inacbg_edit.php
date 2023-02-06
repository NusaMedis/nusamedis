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
     
     $viewPage = "variable_inacbg_view.php";
     $editPage = "variable_inacbg_edit.php";
     
    /* if(!$auth->IsAllowed("man_medis_rujukan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_rujukan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	$plx = new expAJAX("CheckDataCustomerTipe");
	
    
	function CheckDataCustomerTipe($jenisKode)
	{
          global $dtaccess;
          
          $sql = "SELECT a.jenis_inacbg_id from klinik.klinik_jenis_inacbg a 
                    WHERE upper(a.jenis_inacbg_kode) = ".QuoteValue(DPE_CHAR,strtoupper($jenisKode));
          $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $datamerk = $dtaccess->Fetch($rs);
          
		return $datamerk["jenis_inacbg_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["jenis_inacbg_id"])  $variableInacbgId = & $_POST["jenis_inacbg_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $variableInacbgId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_jenis_inacbg a 
				where jenis_inacbg_id = ".QuoteValue(DPE_CHAR,$variableInacbgId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
          $dtaccess->Clear($rs_edit);
     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
               $variableInacbgId = & $_POST["jenis_inacbg_id"];
               $dbTable = "klinik.klinik_jenis_inacbg";
               
               $dbField[0] = "jenis_inacbg_id";   // PK
               $dbField[1] = "jenis_inacbg_nama";
               $dbField[2] = "jenis_inacbg_urut";

               $dbValue[0] = QuoteValue(DPE_CHAR,$variableInacbgId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jenis_inacbg_nama"]); 
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["jenis_inacbg_urut"]);

               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
                $dtmodel->Update() or die("update  error");	
               
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage);
                  exit();
          
     }
     
      if ($_GET["del"]) {
          $variableInacbgId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_jenis_inacbg a 
				where jenis_inacbg_id = ".QuoteValue(DPE_CHAR,$variableInacbgId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$viewPage);
          exit();    
     }
 
 $tableHeader = "&nbsp;Master Variable INACBG";   
?>

<?php //$plx->Run();?>

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
                    <h2><?php echo $tableHeader;?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="jenis_inacbg_kode" readonly name="jenis_inacbg_kode" value="<?php echo $_POST["jenis_inacbg_kode"];?>" required="required" class="form-control col-md-7 col-xs-12" onClick="javascript:return CheckDataCustomerTipe(this.value);">
						</div>
                      </div>                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="jenis_inacbg_label" readonly name="jenis_inacbg_label" value="<?php echo $_POST["jenis_inacbg_label"];?>" required="required" class="form-control col-md-7 col-xs-12" onClick="javascript:return CheckDataCustomerTipe(this.value);">
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Urut <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="jenis_inacbg_urut" name="jenis_inacbg_urut" value="<?php echo $_POST["jenis_inacbg_urut"];?>" required="required" class="form-control col-md-7 col-xs-12" onClick="javascript:return CheckDataCustomerTipe(this.value);">
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Label <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="jenis_inacbg_nama" name="jenis_inacbg_nama" value="<?php echo $_POST["jenis_inacbg_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
						</div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success" onClick="javascript:return CheckDataCustomerTipe(this.value);"> <? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <script>document.frmEdit.jenis_inacbg_nama.focus();</script>
                        <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
						<?php echo $view->RenderHidden("jenis_inacbg_id","jenis_inacbg_id",$variableInacbgId);?>
						<? } ?>
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
