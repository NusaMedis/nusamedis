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

     $viewPage = "aturan_pakai_view.php";
     $editPage = "aturan_pakai_edit.php";
	
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
        $kembali = "aturan_pakai_view.php?kembali=".$_POST["klinik"]; 
        //echo $_POST["klinik"];
     }
	         if($_POST["klinik"]) $_POST["klinik"] = $_POST["klinik"]; 
           
	function CheckDataCustomerTipe($custTipeNama,$aturan_pakai,$klinik)
	{
          global $dtaccess;
          
          $sql = "SELECT a.aturan_pakai_id FROM apotik.apotik_aturan_pakai a 
                    WHERE upper(a.aturan_pakai_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataaturan_pakai = $dtaccess->Fetch($rs);
          
		return $dataaturan_pakai["aturan_pakai_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["aturan_pakai_id"])  $aturan_pakaiId = & $_POST["aturan_pakai_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $aturan_pakaiId = $enc->Decode($_GET["id"]);
          }
          $_POST["klinik"] = $_GET["klinik"];
          $sql = "select a.* from apotik.apotik_aturan_pakai a 
                  where aturan_pakai_id = ".QuoteValue(DPE_CHAR,$aturan_pakaiId);//." and id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["aturan_pakai_nama"] = $row_edit["aturan_pakai_nama"];
          $_POST["aturan_pakai_jam"] = $row_edit["aturan_pakai_jam"];

          $kembali = "aturan_pakai_view.php?kembali=".$_POST["klinik"];
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
               $aturan_pakaiId = & $_POST["aturan_pakai_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
          
               $dbTable = "apotik.apotik_aturan_pakai";
               
               $dbField[0] = "aturan_pakai_id";   // PK
               $dbField[1] = "aturan_pakai_nama";
               
               
               
               if(!$aturan_pakaiId) $aturan_pakaiId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$aturan_pakaiId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["aturan_pakai_nama"]); 
               
              
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
                  
                $kembali = "aturan_pakai_view.php?kembali=".$_POST["klinik"];
               
               header("location:".$kembali);
               exit();        
          }
     }
     // Data Jam Aturan Pakai
     $sql = "select * from apotik.apotik_jam_aturan_pakai";
     $rs = $dtaccess->Execute($sql);
     $dataJam = $dtaccess->Fetch($rs);
      // Data Departemen
     $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
     
       if ($_GET["del"]) {
          $aturan_pakaiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from apotik.apotik_aturan_pakai 
                         where aturan_pakai_id = ".QuoteValue(DPE_CHAR,$aturan_pakaiId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           
       $kembali = "aturan_pakai_view.php?kembali=".$_POST["klinik"];
    
          header("location:".$kembali);
          exit();      
     }
?>

<!DOCTYPE html>
<html lang="en">
<script language="javascript" type="text/javascript">
<? $plx->Run(); ?>

function CheckDataSave(frm)
{ 
     
     if(!frm.aturan_pakai_nama.value){
		alert('Nama aturan_pakai Obat Harus Diisi');
		frm.aturan_pakai_nama.focus();
          return false;
	}

	
	if(frm.x_mode.value=="New") {
		if(CheckDataCustomerTipe(frm.aturan_pakai_nama.value,frm.aturan_pakai_tipe.value,document.getElementById('klinik').value,'type=r')){
			//var test = CheckDataCustomerTipe(frm.aturan_pakai_nama.value,frm.aturan_pakai_tipe.value,document.getElementById('klinik').value,'type=r');
			//alert(test);
      alert('Nama aturan_pakai Obat Sudah Ada');
			frm.aturan_pakai_nama.focus();
			frm.aturan_pakai_nama.select();
			return false;
		}
	} 
     document.frmEdit.submit();     
}
</script>
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
                    <h2>Edit Aturan Pakai</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form  name="frmEdit" id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderTextBox("aturan_pakai_nama","aturan_pakai_nama","50","100",$_POST["aturan_pakai_nama"],"inputField", null,false);?>
                        </div>
                      </div>

                      
                                           
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                           <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
							<?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$kembali."';\"");?>                    
							</div>
                      </div>
<input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />
<script>document.frmEdit.aturan_pakai_nama.focus();</script>
<? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
<?php echo $view->RenderHidden("aturan_pakai_id","aturan_pakai_id",$aturan_pakaiId);?>
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


