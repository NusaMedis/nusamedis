<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."expAJAX.php");
	   require_once($LIB."tampilan.php");	
	   require_once($LIB."currency.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     $poli = $auth->GetPoli();
     $logoKlinik = $auth->GetLogoKlinik();
     $logoAplKiri = $auth->GetLogoAplikasiKiri();
     $warnaCSS = $auth->GetWarnaCSS();
     
     $viewPage = "satuan_view.php";
     $editPage = "satuan_edit.php";
	
	   $plx = new expAJAX("CheckDataCustomerTipe");
	
    /* if(!$auth->IsAllowed("apo_setup_sat_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_setup_sat_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
            if ($_GET["tambah"]) {
        $_POST["klinik"] = $_GET["tambah"];
        $kembali = "satuan_view.php?kembali=".$_POST["klinik"]; 
        //echo $_POST["klinik"];
     }
	         if($_POST["klinik"]) $_POST["klinik"] = $_POST["klinik"]; 
           
	function CheckDataCustomerTipe($custTipeNama,$satuan,$klinik)
	{
          global $dtaccess;
          
          $sql = "SELECT a.satuan_id FROM logistik.logistik_item_satuan a 
                    WHERE upper(a.satuan_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama))."
                    and satuan_tipe = ".QuoteValue(DPE_CHAR,$satuan)." and id_dep =".QuoteValue(DPE_CHAR,$klinik);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $datasatuan = $dtaccess->Fetch($rs);
          
		return $datasatuan["satuan_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["satuan_id"])  $satuanId = & $_POST["satuan_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $satuanId = $enc->Decode($_GET["id"]);
          }
          $_POST["klinik"] = $_GET["klinik"];
          $sql = "select a.* from logistik.logistik_item_satuan a where satuan_id = ".QuoteValue(DPE_CHAR,$satuanId)." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["satuan_nama"] = $row_edit["satuan_nama"];
          $_POST["satuan_tipe"] = $row_edit["satuan_tipe"];
          $_POST["satuan_jumlah"] = $row_edit["satuan_jumlah"];
          //$_POST["klinik"] = $row_edit["id_dep_klinik"];
          $kembali = "satuan_view.php?kembali=".$_POST["klinik"];
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
               $satuanId = & $_POST["satuan_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "logistik.logistik_item_satuan";
               
               $dbField[0] = "satuan_id";   // PK
               $dbField[1] = "satuan_nama";
               $dbField[2] = "id_dep";
               $dbField[3] = "satuan_tipe";
               $dbField[4] = "satuan_jumlah";
               
               if(!$satuanId) $satuanId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$satuanId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["satuan_nama"]); 
			         $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
			         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["satuan_tipe"]);
			         $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["satuan_jumlah"]));
			         
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   //print_r($dbValue);die();
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                  
                $kembali = "satuan_view.php?kembali=".$_POST["klinik"];
               
               header("location:".$kembali);
               exit();        
          }
     }
     
      // Data Departemen
     $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
     
       if ($_GET["del"]) {
          $satuanId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from logistik.logistik_item_satuan 
                         where satuan_id = ".QuoteValue(DPE_CHAR,$satuanId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           
       $kembali = "satuan_view.php?kembali=".$_POST["klinik"];
    
          header("location:".$kembali);
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
                <h3>Apotik</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Edit Satuan Obat</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form  name="frmEdit" id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="satuan_tipe" id="satuan_tipe" >
							<option value="">- Semua Tipe -</option>
							 <option value="B"  <?php if($_POST["satuan_tipe"]=="B") echo "selected";?>>Beli</option>    
							 <option value="J" <?php if($_POST["satuan_tipe"]=="J") echo "selected";?>>Jual</option>
							</select>
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("satuan_nama","satuan_nama","50","100",$_POST["satuan_nama"],"inputField", null,false);?>
						</div>
                      </div>
                                     
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jumlah</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <?php echo $view->RenderTextBox("satuan_jumlah","satuan_jumlah","50","100",currency_format($_POST["satuan_jumlah"]),"inputField", null,true);?>
                        </div>
                      </div>
                                           
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
						</div>
                      </div>
                      <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.satuan_nama.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("satuan_id","satuan_id",$satuanId);?>
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


























