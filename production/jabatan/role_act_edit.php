<?php



     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
	   require_once($LIB."tampilan.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
	   $userName = $auth->GetUserName();
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
	   
     $priv["1"] = "checked";
     $priv["0"] = "";
     
   /*  if(!$auth->IsAllowed("man_user_jabatan",PRIV_READ) && !$auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("man_user_jabatan",PRIV_READ)===1 || $auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
     if($_GET["id_rol"]) {
          $_POST["id_rol"] = $_GET["id_rol"];
          $idRolEnc = $_GET["id_rol"];
     }
     
     if($_GET["app_id"]){
         $appEnc = $_GET["app_id"];
         $appId = $_GET["app_id"];
     }
     
     if($_POST["app_id"]) $appEnc = $_POST["app_id"]; 
     if ($_POST["id_rol_enc"]) $idRolEnc = $_POST["id_rol_enc"]; 
     
     $backPage = "role_act_view.php?id=".$idRolEnc."&app_id=".$appEnc;
     
     if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
     else $_x_mode = "New";
     
     if($_x_mode=="Edit") $privMode = PRIV_UPDATE;
     else $privMode = PRIV_DELETE;    
     
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $_POST["id_priv"] = $_GET["id"];
          }
          
          $sql = "select a.*,b.rol_name, c.priv_name from global.global_auth_role_priv a
                    join global.global_auth_role b on a.id_rol = b.rol_id
                    join global.global_auth_privilege c on a.id_priv = c.priv_id
                    where a.id_priv = ".$_POST["id_priv"]." and a.id_rol = ".$_POST["id_rol"];
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["rol_priv_access"] = $row_edit["rol_priv_access"];
          $jabNama = $row_edit["rol_name"];
          $privNama = $row_edit["priv_name"];
          $privCreate = $_POST["rol_priv_access"]{PRIV_CREATE};
          $privRead = $_POST["rol_priv_access"]{PRIV_READ};
          $privUpdate = $_POST["rol_priv_access"]{PRIV_UPDATE};
          $privDelete = $_POST["rol_priv_access"]{PRIV_DELETE};
     }
     
     if ($_POST["btnNew"]) {
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     if ($_POST["btnUpdate"]) {
          if($_POST["btnUpdate"]){
               $jabprivId = & $_POST["id"];
               $_x_mode = "Edit";
          }             
     
          if ($err_code == 0) {
               
               if($_POST["rol_create"]) $privAkses = "1";
               else $privAkses = "0";
               
               if($_POST["rol_read"]) $privAkses .= "1";
               else $privAkses .= "0";
               
               if($_POST["rol_update"]) $privAkses .= "1";
               else $privAkses .= "0";
               
               if($_POST["rol_delete"]) $privAkses .= "1";
               else $privAkses .= "0";
         
               $dbTable = "global.global_auth_role_priv";
               
               $dbField[0] = "id_rol";   // PK
               $dbField[1] = "id_priv";
               $dbField[2] = "rol_priv_access";
               $dbField[3] = "id_dep";
         
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["id_rol"]);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST["id_priv"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$privAkses);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dbKey[1] = 1; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
         
               $dtmodel->Update() or die("update  error");	
         
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);
               
               header("location:role_act_view.php?id=".$_POST["id_rol"]."&app_id=".$appEnc);
               exit();        
          }
     }
     
     if ($_POST["btnDelete"]) {
          $privId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($privId);$i<$n;$i++){
               $sql = "delete from global.global_auth_role_priv where id_priv = ".$privId[$i]." and id_rol = ".$_POST["id_rol"];
               $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          }
          
          header("location:role_act_view.php?id=".$_POST["id_rol"]."&app_id=".$appEnc);
          exit();    
     }
     
     $PageHeader = $jabNama." - ".$privNama;

?>


<!DOCTYPE html>
<html lang="en">
	<header>
		<head>
			<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/ew.js"></script>
		</head>
	</header>
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
                    <h2><?php echo $PageHeader;?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">CREATE</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_create" id="rol_create"  class="inputField" value="1" <?php echo $priv[$privCreate];?>><label for="rol_create"></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">READ</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_read" id="rol_read" class="inputField" value="1" <?php echo $priv[$privRead];?>><label for="rol_read"></label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">UPDATE<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_update" id="rol_update" class="inputField" value="1" <?php echo $priv[$privUpdate];?>><label for="rol_update"></label>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">DELETE<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input onKeyDown="return tabOnEnter_select_with_button(this, event);" type="checkbox" name="rol_delete" id="rol_delete" class="inputField" value="1" <?php echo $priv[$privDelete];?>><label for="rol_delete"></label>
                        </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <input type="submit" class="btn btn-success" name="btnUpdate" value="Simpan" class="inputField"/>
						  <input type="button" name="btnBack" value="Kembali" class="btn btn-Primary" onClick="document.location.href='<?php echo $backPage;?>'"/>
                        </div>
                      </div>
                      <script>document.frmEdit.rol_create.focus();</script>
						<input type="hidden" name="id_priv" value="<?php echo $_POST["id_priv"];?>" />
						<input type="hidden" name="id_rol" value="<?php echo $_POST["id_rol"];?>" />
						<input type="hidden" name="id_rol_enc" value="<?php echo $idRolEnc;?>" />
						<input type="hidden" name="app_id" value="<?php echo $appEnc;?>" />
						<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
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