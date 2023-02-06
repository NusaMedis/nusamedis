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
	   $auth = new CAuth();
     $err_code = 0;
	
	$plx = new expAJAX("CheckDataIcd");
	
   /*  if(!$auth->IsAllowed("man_medis_procedure",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_procedure",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	function CheckDataIcd($icdNomor,$icdId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT procedure_id FROM klinik.klinik_procedure
                    WHERE upper(procedure_nomor) = ".QuoteValue(DPE_CHAR,strtoupper($icdNomor));
                    
          if($icdId) $sql .= " and procedure_id <> ".QuoteValue(DPE_CHAR,$icdId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
          
		return $dataAdaIcd["procedure_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["procedure_id"])  $icdId = & $_POST["procedure_id"];
  
     if($_GET["nama"]) $_POST["nama"] = $_GET["nama"];
     if($_GET["kode"]) $_POST["kode"] = $_GET["kode"];
     
     $backPage = "procedure_view.php?kode=".$_POST["kode"]."&nama=".$_POST["nama"];

     $tableHeader = "&nbsp;Master procedure";

	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $icdId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*, b.poli_id from klinik.klinik_procedure a 
          left join global.global_auth_poli b on a.id_poli = b.poli_id
           where procedure_id= ".QuoteValue(DPE_CHAR,$icdId);
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
               $icdId = & $_POST["procedure_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_procedure";
               
               $dbField[0] = "procedure_id";   // PK
               $dbField[1] = "procedure_nomor";
               $dbField[2] = "procedure_nama";
               $dbField[3] = "procedure_short_desc";
               $dbField[4] = "procedure_nomor_tanpa_titik";
               $dbField[5] = "id_poli";

			
               if(!$icdId) $icdId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$icdId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["procedure_nomor"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["procedure_nama"]);
               $dbValue[3]=QuoteValue(DPE_CHAR,$_POST["procedure_short_desc"]);
               $dbValue[4]=QuoteValue(DPE_CHAR,$_POST["procedure_nomor_tanpa_titik"]);
               $dbValue[5]=QuoteValue(DPE_CHAR,$_POST["poli_id"]);

			
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
          $icdId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_procedure where procedure_id = ".QuoteValue(DPE_CHAR,$icdId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }
 
  /*   if ($_POST["btnDelete"]) {
          $icdId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($icdId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_icd  
                         where icd_id = ".QuoteValue(DPE_CHAR,$icdId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	*/


     $sql = "SELECT * from global.global_auth_poli";
     $poli = $dtaccess->FetchAll($sql);

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
                    <h2>Setup procedure</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Code <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="procedure_nomor" name="procedure_nomor" value="<?php echo $_POST["procedure_nomor"];?>" required="required" class="form-control col-md-7 col-xs-12">
						          </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nomor urut <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="procedure_nomor_tanpa_titik" name="procedure_nomor_tanpa_titik" value="<?php echo $_POST["procedure_nomor_tanpa_titik"];?>" required="required" class="form-control col-md-7 col-xs-12">
                      </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Poli <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="poli_id">
                            <option value="">-</option>
                            <?php for($i = 0; $i < count($poli); $i++){ ?>
                              <option value="<?=$poli[$i]['poli_id']?>" <?=($_POST['poli_id']==$poli[$i]['poli_id']) ? "selected" : ""?>><?=$poli[$i]['poli_nama']?></option>
                            <?php } ?>
                          </select>
                      </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">procedure <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="procedure_nama" name="procedure_nama" value="<?php echo $_POST["procedure_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
						            </div>
                      </div>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Deskripsi <span class="required">*</span>
                          </label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="procedure_short_desc" name="procedure_short_desc" value="<?php echo $_POST["procedure_short_desc"];?>" required="required" class="form-control col-md-7 col-xs-12">
                          </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("procedure_id","procedure_id",$icdId);?>
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
