<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
	   require_once($LIB."tampilan.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   $usrId = $auth->GetUserId();
	   
     $viewPage = "pekerjaan_view.php";
     $editPage = "pekerjaan_edit.php";

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     $back = "pekerjaan_view.php?kembali=".$_POST["klinik"];
     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["pekerjaan_id"])  $pekerjaanId = & $_POST["pekerjaan_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $pekerjaanId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from global.global_pekerjaan a 
				          where pekerjaan_id = ".QuoteValue(DPE_CHAR,$pekerjaanId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
     
          $_POST["pekerjaan_nama"] = $row_edit["pekerjaan_nama"];
          $_POST["klinik"] = $depId;
          $back = "pekerjaan_view.php?kembali=".$_POST["klinik"];
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
               $pekerjaanId = & $_POST["pekerjaan_id"];
               $_x_mode = "Edit";
          }
               
               $dbTable = "  global.global_pekerjaan";
               
               $dbField[0] = "pekerjaan_id";   // PK
               $dbField[1] = "pekerjaan_nama";
               $dbField[2] = "id_dep";
			
               if(!$pekerjaanId) $pekerjaanId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$pekerjaanId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pekerjaan_nama"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
              
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
               
                  $back = "pekerjaan_view.php";
                  header("location:".$back);
                  exit();
     }
 
    if ($_GET["del"]) {
          $pekerjaanId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_pekerjaan where pekerjaan_id = ".QuoteValue(DPE_CHAR,$pekerjaanId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
          $back = "pekerjaan_view.php";
          header("location:".$back);
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
                <h3>Pekerjaan</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Master Pekerjaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Pekerjaan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <?php echo $view->RenderTextBox("pekerjaan_nama","pekerjaan_nama","50","100",$_POST["pekerjaan_nama"],"inputField", null,false);?>
                        </div>
                      </div>                    
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                    <script>document.frmEdit.pekerjaan_nama.focus();</script>
					<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
					<?php echo $view->RenderHidden("pekerjaan_id","pekerjaan_id",$pekerjaanId);?>
					<?php echo $view->RenderHidden("id_dep","id_dep",$depId);?>
					<? } ?>
					<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
					<?php echo $view->RenderHidden("id_dep","id_dep",$depId);?>
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



<br />
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<script language="javascript" type="text/javascript">
function CheckDataSave(frm)
{ 
     if(!frm.pekerjaan_nama.value){
		alert('Nama Jenis pegawai Harus Diisi');
		frm.pekerjaan_nama.focus();
          return false;
	}
	
     	return true;    
}
</script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>

<body>
<div id="body">
<div id="scroller">
<br />
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <table width="100%" border="0" cellpadding="1" cellspacing="1">
          <tr>
               <td align="right" class="tablesmallheader" width="30%"><strong>Nama Dokter</strong>&nbsp;</td>
               <td width="70%">
                    <?php echo $view->RenderTextBox("pekerjaan_nama","pekerjaan_nama","50","100",$_POST["pekerjaan_nama"],"inputField", null,false);?>
               </td>
          </tr> 
      
          <tr>
               <td colspan="2" align="center">
                    <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                    <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$back."';\"");?>                    
               </td>
          </tr>
     </table>
     </td>
</tr>
</table>

<script>document.frmEdit.pekerjaan_nama.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("pekerjaan_id","pekerjaan_id",$pekerjaanId);?>
<?php echo $view->RenderHidden("id_dep","id_dep",$depId);?>
<? } ?>
<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
<?php echo $view->RenderHidden("id_dep","id_dep",$depId);?>
</form>
</div>
</div>

<div id="footer">
  		<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <!--<td align="left" width="15%" valign="middle"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>-->
      <td align="right" width="75%" valign="middle"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>
</div>

