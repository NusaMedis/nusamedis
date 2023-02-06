<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/bit.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/expAJAX.php");
	require_once($ROOT."lib/tampilan.php");		
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $depNama = $auth->GetDepNama(); 
    $userName = $auth->GetUserName();
     $enc = new textEncrypt();     
	$auth = new CAuth();
     $err_code = 0;
     $depId = $auth->GetDepId();
     
    
	
	$plx = new expAJAX("CheckDataCustomerTipe");
	
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.biaya_id FROM klinik.klinik_biaya a 
                    WHERE upper(a.biaya_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->Fetch($rs);
          
		return $dataPaket["biaya_id"];
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kelas_id"])  $opPaketId = & $_POST["kelas_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $opPaketId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* from klinik.klinik_kelas a 
				where kelas_id = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["kelas_nama"] = $row_edit["kelas_nama"];
          $_POST["kelas_tingkat"] = $row_edit["kelas_tingkat"];
          
          $sql = "select a.* from klinik.klinik_biaya_split a 
				where id_biaya = ".QuoteValue(DPE_CHAR,$opPaketId);
          $rs = $dtaccess->Execute($sql);
          $dataPaket = $dtaccess->FetchAll($rs);
		
		for($i=0,$n=count($dataPaket);$i<$n;$i++) {
			$_POST["txtNom"][$dataPaket[$i]["id_split"]] = $dataPaket[$i]["bea_split_nominal"];
		}
		
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
               $opPaketId = & $_POST["kelas_id"];
               $_x_mode = "Edit";
          } 
         
          
               $dbTable = "klinik.klinik_kelas";
               
               $dbField[0] = "kelas_id";   // PK
               $dbField[1] = "kelas_nama";
               $dbField[2] = "kelas_tingkat";
               $dbField[3] = "id_dep";
 			
               if(!$opPaketId) $opPaketId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$opPaketId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kelas_nama"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,$_POST["kelas_tingkat"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
               

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
                  
			$sql = "delete from klinik.klinik_biaya_split
					where id_biaya = ".QuoteValue(DPE_CHAR,$opPaketId);
			$dtaccess->Execute($sql);
			
			$dbTable = "klinik.klinik_biaya_split";
			
			$dbField[0] = "bea_split_id";   // PK
			$dbField[1] = "id_biaya";
			$dbField[2] = "bea_split_nominal";
			$dbField[3] = "id_split";

			
			
			foreach($_POST["txtNom"] as $split => $value) {
			
				$dbValue[0] = QuoteValue(DPE_CHAR,$dtaccess->GetTransID());
				$dbValue[1] = QuoteValue(DPE_CHAR,$opPaketId);
				$dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($value));
				$dbValue[3] = QuoteValue(DPE_CHAR,$split);
				
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		
				$dtmodel->Insert() or die("insert  error");	
			
				unset($dtmodel);
				unset($dbValue);
				unset($dbKey);
				$beaNominal += StripCurrency($value);
			} 
		//	$sql = "update klinik.klinik_biaya set biaya_total = ".QuoteValue(DPE_NUMERIC,$beaNominal)." where biaya_id = ".QuoteValue(DPE_CHAR,$opPaketId);
		//	$dtaccess->Execute($sql);
			
               header("location:tindakan_view.php");
               exit();        
          
     }
 


	$sql = "select * from klinik.klinik_split  where split_flag = ".QuoteValue(DPE_CHAR,SPLIT_INAP)." order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);  
     
     if ($_GET["del"]) {
          $opPaketId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_kelas 
                         where kelas_id = ".QuoteValue(DPE_CHAR,$opPaketId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:tindakan_view.php");
          exit();    
     }
     
     $sql = "select kelas_id,kelas_nama from klinik.klinik_kelas";     
      //         echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataBiaya = $dtaccess->FetchAll($rs);
       /*databiaya = data kelas
       
 /*    for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
          unset($show);
          if($_POST["kamar_kategori"]==$dataBiaya[$i]["biaya_id"]) $show = "selected";
          $kategori[$i] = $view->RenderOption($dataBiaya[$i]["biaya_id"],$dataBiaya[$i]["biaya_nama"],$show);
     }
  */        
       $kategori[0] = $view->RenderOption("","All",$show);
     for($i=0,$n=count($dataBiaya);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kategori"]==$dataBiaya[$i]["kelas_id"]) $show = "selected";
          $kategori[$i+1] = $view->RenderOption($dataBiaya[$i]["kelas_id"],$dataBiaya[$i]["kelas_nama"],$show);
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
                    <h2>Setup Kelas Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="kelas_nama" name="kelas_nama" value="<?php echo $_POST["kelas_nama"]?>" required="required" class="form-control col-md-7 col-xs-12">
							
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tingkat Kelas<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select id="kelas_tingkat" name="kelas_tingkat" class="form-control">
							<option value="">Pilih Tingkat</option>
							<option value="1" <?php if($_POST["kelas_tingkat"]=="1")echo "selected";?>>1</option>
							<option value="2" <?php if($_POST["kelas_tingkat"]=="2")echo "selected";?>>2</option>
							<option value="3" <?php if($_POST["kelas_tingkat"]=="3")echo "selected";?>>3</option>
							<option value="4" <?php if($_POST["kelas_tingkat"]=="4")echo "selected";?>>4</option>
							<option value="5" <?php if($_POST["kelas_tingkat"]=="5")echo "selected";?>>5</option>
			   
						</select>  
  					    </div>
                      </div>
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<font color="red">* Tingkat Kelas Standar:</font><br>
							<font color="red">Kelas VIP = 1</font><br>
							<font color="red">Kelas I = 2</font><br>
							<font color="red">Kelas II = 3</font><br>
							<font color="red">Kelas III = 4</font><br>
							<font color="red">Kelas ICU/HCU = 5</font><br>
  					    </div>
                      </div>
                      <div class="ln_solid"></div>
					  <br>

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