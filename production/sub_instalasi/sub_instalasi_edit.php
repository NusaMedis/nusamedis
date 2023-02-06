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
     $auth = new CAuth();
     $enc = new textEncrypt();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $depId = $auth->GetDepId(); 
     $err_code = 0;
     
	 $sql = "select sub_instalasi_id,sub_instalasi_nama 
             from global.global_auth_sub_instalasi
             ";     
      //         echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataIns = $dtaccess->FetchAll($rs);
     $pilihins[0] = $view->RenderOption("","Pilih Sub Instalasi",$show);
     for($i=0,$n=count($dataIns);$i<$n;$i++) {
          unset($show);
          if($_POST["instalasi_id"]==$dataIns[$i]["sub_instalasi_id"]) $show = "selected";
          $pilihins[$i+1] = $view->RenderOption($dataIns[$i]["instalasi_id"],$dataIns[$i]["sub_instalasi_nama"],$show);
     }

	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
  if($_GET["id_instalasi"]) $_POST["id_instalasi"]=$_GET["id_instalasi"]; 
	if($_POST["sub_instalasi_id"])  $subInstalasiId = & $_POST["sub_instalasi_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $subInstalasiId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from global.global_auth_sub_instalasi a 
				where sub_instalasi_id = ".QuoteValue(DPE_CHAR,$subInstalasiId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["sub_instalasi_nama"] = $row_edit["sub_instalasi_nama"];
          $_POST["sub_instalasi_urut"] = $row_edit["sub_instalasi_urut"];
          $_POST["sub_instalasi_kode"] = $row_edit["sub_instalasi_kode"];
          $_POST["id_instalasi"] = $row_edit["id_instalasi"];
          
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
               $subInstalasiId = & $_POST["sub_instalasi_id"];
               $_x_mode = "Edit";
          } 
         
          
               $dbTable = "global.global_auth_sub_instalasi";
               
               $dbField[0] = "sub_instalasi_id";   // PK
               $dbField[1] = "sub_instalasi_nama";
               $dbField[2] = "sub_instalasi_kode";
               $dbField[3] = "id_instalasi";
               $dbField[4] = "id_dep";
               if($_POST["btnSave"]){
               $dbField[5] = "sub_instalasi_urut";
               }
               
               $sql = "select sub_instalasi_urut as max from global.global_auth_sub_instalasi order by sub_instalasi_urut desc";
               $rs = $dtaccess->Execute($sql);
               $maks = $dtaccess->Fetch($rs);
               $maxUrut = $maks["max"]+1;
			
               if(!$subInstalasiId) $subInstalasiId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$subInstalasiId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["sub_instalasi_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["sub_instalasi_kode"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_instalasi"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               if($_POST["btnSave"]){
               $dbValue[5] = QuoteValue(DPE_NUMERIC,$maxUrut);
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
                  
               header("location:sub_instalasi_view.php?konf=".$_POST["konf"]."&id_instalasi=".$_POST["id_instalasi"]);
               exit();        
          
     }
 
     
     if ($_GET["del"]) {
          $instalasiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_auth_sub_instalasi where sub_instalasi_id = ".QuoteValue(DPE_CHAR,$instalasiId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:sub_instalasi_view.php?konf=".$_POST["konf"]."&id_instalasi=".$_POST["id_instalasi"]);
          exit();    
     }
     
     $sql = "select * from global.global_auth_instalasi where id_dep = '$depId' order by instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataIns = $dtaccess->FetchAll($rs);
     
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
                    <h2>Setup Sub Instalasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="frmEdit" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Instalasi</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                        	<select id="id_instalasi" name="id_instalasi" class="form-control" onKeyDown="return tabOnEnter(this,event)">
                    			<option value="">Pilih Instalasi</option>
                    				<?php for($i=0,$n=count($dataIns);$i<$n;$i++){ ?>
                    			<option value="<?php echo $dataIns[$i]["instalasi_id"];?>" <?php if($_POST["id_instalasi"]==$dataIns[$i]["instalasi_id"]) echo "selected"; ?>><?php echo $dataIns[$i]["instalasi_nama"]; ?></option>
                    				<?php } ?>
                  			</select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("sub_instalasi_nama","sub_instalasi_nama","50","100",$_POST["sub_instalasi_nama"],"inputField", null,false);?>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kode<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("sub_instalasi_kode","sub_instalasi_kode","50","100",$_POST["sub_instalasi_kode"],"inputField", null,false);?>
                        </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                         </div>
                      </div>
                      <?php echo $view->RenderHidden("sub_instalasi_id","sub_instalasi_id",$subInstalasiId);?>
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