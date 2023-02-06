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
     $tree = new CTree("global.global_sender_umum","sender_umum_id", TREE_LENGTH_CHILD);
     
    /* if(!$auth->IsAllowed("man_medis_kecamatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	

   
	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
 
      $thisPage = "tambah_prop.php?klinik=".$_POST["klinik"];
      $editPage = "propinsi_edit.php";
      $viewpage = "propinsi_view.php";
     //cari lokasi_propinsi terakhir

     $sql = "select cast(lokasi_propinsi as integer) as kodeprop  from global.global_lokasi where lokasi_propinsi <>'00' and lokasi_kabupatenkota='00'
             and lokasi_kecamatan ='00' and lokasi_kelurahan ='0000' order by lokasi_propinsi desc";
     $rs = $dtaccess->Execute($sql);
     $lokasiPropinTerakhir = $dtaccess->Fetch($rs);       
   
     if ($_POST["btnSave"]) { 

     $sql = "select cast(lokasi_propinsi as integer) as kodeprop  from global.global_lokasi where lokasi_propinsi <>'00' and lokasi_kabupatenkota='00'
             and lokasi_kecamatan ='00' and lokasi_kelurahan ='0000' order by lokasi_propinsi desc";
     $rs = $dtaccess->Execute($sql);
     $lokasiPropinTerakhir = $dtaccess->Fetch($rs); 

      $sql = "select * from global.global_lokasi order by lokasi_id desc";
      $rs = $dtaccess->Execute($sql);
      $lokasiId = $dtaccess->Fetch($rs);
                    
               $lokasiIdNew = $lokasiId["lokasi_id"]+1;
                $propId = str_pad($lokasiPropinTerakhir["kodeprop"]+1,2,"0",STR_PAD_LEFT);                 
               $kode = $propId.".00.00.0000";
               
               $dbTable = "global.global_lokasi";
               
               $dbField[0] = "lokasi_id";   // PK
               $dbField[1] = "lokasi_kode"; 
               $dbField[2] = "lokasi_nama";
               $dbField[3] = "lokasi_propinsi";              
               $dbField[4] = "lokasi_kabupatenkota";              
               $dbField[5] = "lokasi_kecamatan";              
               $dbField[6] = "lokasi_kelurahan";              
                                                 
               $dbValue[0] = QuoteValue(DPE_NUMERIC,$lokasiIdNew);
               $dbValue[1] = QuoteValue(DPE_CHAR,$kode); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["lokasi_nama"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$propId);
               $dbValue[4] = QuoteValue(DPE_CHAR,'00');
               $dbValue[5] = QuoteValue(DPE_CHAR,'00');
               $dbValue[6] = QuoteValue(DPE_CHAR,'0000');

               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
              // print_r($dbValue); die();

                    $dtmodel->Insert() or die("insert  error");	
               
                unset($dtmodel);
                unset($dbField);
                unset($dbValue);
                unset($dbKey); 
                  
                $back = "propinsi_view.php?klinik=".$_POST["klinik"]; 
                header("location:".$back);
                exit();
                
     }
                                         
     
		// Nama Puskesmas
    $sql = "select dep_nama from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataPuskesmas = $dtaccess->Fetch($rs);      
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
                    <h2>MASTER PROPINSI</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Propinsi <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="lokasi_nama" name="lokasi_nama" value="<?php echo $_POST["lokasi_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
						</div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <!--<input type="submit" name="btnSave" id="btnSave" value="Simpan" class="btn btn-success" onClick="javascript:return CheckDataSave(document.frmEdit);" />-->
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("lokasi_id","lokasi_id",$_POST["lokasi_id"]);?>
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









<div id="body">
<div id="scroller">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table width="70%" border="0" cellpadding="1" cellspacing="1">
<tr>
     <td>
     <fieldset>
     <legend><strong>MASTER PROPINSI</strong></legend>
	<table width="100%" border="0" cellpadding="1" cellspacing="1">
  
<tr>
		<td width= "18%" align="left" class="tablecontent">Nama Propinsi</td>
		<td width= "45%" align="left" class="tablecontent-odd">
			<input  type="text" name="lokasi_nama" id="lokasi_nama" size="30" maxlength="50" value="<?php echo $_POST["lokasi_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
		</td>
	</tr>
  
     <tr>
		<td colspan="3" align="center" class="tablecontent-odd">&nbsp;</td>
	</tr>	
	<tr>
          <td colspan="3" align="center" class="tableheader">
               <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="submit" onClick="javascript:return CheckDataSave(document.frmEdit);" />
               <input type="button" name="btnBack" id="btnBack" value="Batal" class="submit" onClick="return document.location.href='<?php echo $viewpage; ?>'" />
          </td>
    </tr>
</table>
</table>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.lokasi_nama.focus();</script>
</form>
</div>
</div>

