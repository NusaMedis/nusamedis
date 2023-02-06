<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
	   require_once($LIB."tampilan.php");	
     require_once($LIB."tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt(); 
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();    
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   //echo $depId;
     //$tree = new CTree("global.global_pekerjaan","pekerjaan_id", TREE_LENGTH_CHILD);
     
    /* if(!$auth->IsAllowed("man_medis_kategori_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kategori_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kat_icd_id"])  $KatIcdId = & $_POST["kat_icd_id"];
	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
      $thisPage = "kat_icd_view.php?klinik=".$_POST["klinik"];
      $editPage = "kat_icd_edit.php";
 
     if ($_GET["id"]) {
     	
     	
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $KatIcdId = $enc->Decode($_GET["id"]);
          }
          
          
          $sql = "select * from klinik.klinik_kat_icd where kat_icd_id = ".QuoteValue(DPE_CHAR,$KatIcdId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          //echo $sql;
          $_POST["kat_icd_nama"] = $row_edit["kat_icd_nama"];
          $_POST["kat_icd_id"] = $row_edit["kat_icd_id"];
          $_POST["klinik"] = $_GET["klinik"];
          
          $back = "kat_icd_view.php?klinik=".$_POST["klinik"]; 

     }

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     	 if ($_GET["tambah"]) {
        
        $back = "kat_icd_view.php?";
     } //else $back = "lucky_friend_view.php";
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               $KatIcdId = & $_POST["kat_icd_id"];
               $_x_mode = "Edit";
               }
                  
               $dbTable = "klinik.klinik_kat_icd";
               
               $dbField[0] = "kat_icd_id";   // PK
               $dbField[1] = "kat_icd_nama";
               $dbField[2] = "id_dep";

					if(!$KatIcdId) $KatIcdId = $dtaccess->GetTransId();               
                                  
               $dbValue[0] = QuoteValue(DPE_CHAR,$KatIcdId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kat_icd_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
               
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
                  
                  $back = "kat_icd_view.php?klinik=".$_POST["klinik"]; 
                  header("location:".$back);
                  exit();
     }
                                         
     if ($_GET["del"]) {
          $pekerjaanId = $enc->Decode($_GET["id"]);

               $sql = "delete from klinik.klinik_kat_icd where kat_icd_id = ".QuoteValue(DPE_CHAR,$KatIcdId);//[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA);

          $back = "kat_icd_view.php?klinik=".$_GET["klinik"]; 
          header("location:".$back);
          exit();    
     }
     

		// Nama Puskesmas
    $sql = "select dep_nama from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataPuskesmas = $dtaccess->Fetch($rs);
    //echo $sql;
      
   
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
                    <h2>MASTER KATEGORI ICD</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                     
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Kategori<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="kat_icd_nama" name="kat_icd_nama" value="<?php echo $_POST["kat_icd_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
						</div>
					  </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("kat_icd_id","kat_icd_id",$_POST["kat_icd_id"]);?>
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


