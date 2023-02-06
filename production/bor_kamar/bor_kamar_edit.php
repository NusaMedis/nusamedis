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
	$auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
	
	$plx = new expAJAX("CheckDataMorfologi,SetCmbKamar");
	
    /* if(!$auth->IsAllowed("man_medis_master_bor_kamar",PRIV_READ) && !$auth->IsAllowed("sirs_tampilan_bor_bor",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_master_bor_kamar",PRIV_READ)===1 || $auth->IsAllowed("sirs_tampilan_bor_bor",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
	 //if($_GET["kelas"]) $_POST["id_kelas"] = $_GET["kelas"];
   
	function CheckDataMorfologi($morfologiNomor,$morfologiId=null)
	{
          global $dtaccess;
          
          $sql = "SELECT morfologi_id FROM klinik.klinik_morfologi 
                    WHERE upper(morfologi_nomor) = ".QuoteValue(DPE_CHAR,strtoupper($morfologiNomor));
                    
          if($morfologiId) $sql .= " and morfologi_id <> ".QuoteValue(DPE_CHAR,$morfologiId);
          
          $rs = $dtaccess->Execute($sql);
          $dataAdaMorfologi = $dtaccess->Fetch($rs);
          
		return $dataAdaMorfologi["morfologi_id"];
     }
     
     function SetCmbKamar($id_kategori){
          global $dtaccess, $view;
          
          $sql = "select a.* from klinik.klinik_kamar a 
         where a.id_kelas = ".QuoteValue(DPE_CHAR,$id_kategori);
         //return $sql."-".$_POST["id_kamar"];
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataKamar = $dtaccess->FetchAll($rs);
           
          unset($opt_kamar);
          $opt_kamar[0] = $view->RenderOption("","[pilih kamar]",$show);
          $i=1;
          
          for($i=0,$n=count($dataKamar);$i<$n;$i++){
            if($_POST["id_kamar"]==$dataKamar[$i]["kamar_id"]) $show="selected";
            $opt_kamar[$i+1] = $view->RenderOption($dataKamar[$i]["kamar_id"],$dataKamar[$i]["kamar_nama"],$show);
            unset($show);
          }
          $str = $view->RenderComboBox("id_kamar","id_kamar",$opt_kamar,null,null,null);

          
          return $str;
     }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["rawat_inap_bor_kamar_id"])  $borId = & $_POST["rawat_inap_bor_kamar_id"];
     
     $backPage = "bor_kamar_view.php";

     $tableHeader = "&nbsp;BOR KAMAR";
	
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $borId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*, b.id_kelas from klinik.klinik_rawat_inap_bor_kamar a left join klinik.klinik_kamar b on b.kamar_id=a.id_kamar 
                  where rawat_inap_bor_kamar_id= ".QuoteValue(DPE_CHAR,$borId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $view->CreatePost($row_edit);
          $_POST["id_kamar"] = $row_edit["id_kamar"];
          $_POST["id_kelas"] = $row_edit["id_kelas"];
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
               $borId = & $_POST["rawat_inap_bor_kamar_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
              $sql = "select * from klinik.klinik_rawat_inap_bor_kamar where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
              $rs = $dtaccess->Execute($sql);
              $dataBorKamar = $dtaccess->Fetch($rs);
              
               $dbTable = "klinik.klinik_rawat_inap_bor_kamar";
               
               $dbField[0] = "rawat_inap_bor_kamar_id";   // PK
               $dbField[1] = "id_kamar";
               $dbField[2] = "bed_terpakai";
               $dbField[3] = "bed_tersisa";
               $dbField[4] = "jml_op";                           
               $dbField[5] = "jml_bed";
               //$dbField[6] = "id_kelas";
               
               $sql = "select count(bed_kode) as total from klinik.klinik_kamar_bed where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"])." and bed_keterangan='n'";
               $rs = $dtaccess->Execute($sql);
               $dataBed = $dtaccess->Fetch($rs);
               
               $sisaBed = $dataBed["total"]-$_POST["bed_terpakai"];
			
               if(!$borId && !$dataBorKamar) $borId = $dtaccess->GetTransID();
               elseif(!$borId && $dataBorKamar) $borId = $dataBorKamar["rawat_inap_bor_kamar_id"];   
               $dbValue[0] = QuoteValue(DPE_CHAR,$borId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
               $dbValue[2] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["bed_terpakai"]));
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($sisaBed));
               $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jml_op"]));
               $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($dataBed["total"]));
               //$dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
               //print_r($dbValue);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
   
               if ($_POST["btnSave"] && !$dataBorKamar) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"] || $dataBorKamar) {
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
          $borId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_rawat_inap_bor_kamar where rawat_inap_bor_kamar_id = ".QuoteValue(DPE_CHAR,$borId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }
 
  /*   if ($_POST["btnDelete"]) {
          $morfologiId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($morfologiId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_morfologi  
                         where morfologi_id = ".QuoteValue(DPE_CHAR,$morfologiId[$i]);
               $dtaccess->Execute($sql);
          }
          
          header("location:".$backPage);
          exit();    
     }	*/
     
     //$opt_kamar[0] = $view->RenderOption("","[pilih kamar]",$show);
     
     $sql = "select * from klinik.klinik_kelas order by kelas_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs);
     
     $opt_kategori[0] = $view->RenderOption("","[Pilih Kelas]",$show);
     for($i=0,$n=count($dataKelas);$i<$n;$i++){   
         if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
         $opt_kategori[$i+1] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
         unset($show);
     }
?>






<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <script language="javascript" type="text/javascript">


<? $plx->Run(); ?>

function CariKamar(id_kat)
{
  document.getElementById('div_kamar').innerHTML = SetCmbKamar(id_kat,'type=r');
  //document.getElementById('id_kamar').focus();
}
</script>
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
                    <h2>Master Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelas</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name= "id_kelas" id="id_kelas" class="form-control" required="required" onchange="javascript:return CariKamar(document.getElementById('id_kelas').value);">
                            <?php echo $view->RenderComboBoxNew("id_kelas","id_kelas",$opt_kategori,null,null);?>
                          </select>
                        </div>
                      </div>
					  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kamar</label>
                        <div class="col-md-6 col-sm-6 col-xs-12" id="div_kamar">
                          
                          	<?php echo SetCmbKamar($_POST["id_kelas"]);?>
                         
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Bed Terpakai<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="bed_terpakai" name="bed_terpakai" value="<?php echo $_POST["bed_terpakai"];?>" required="required" class="form-control col-md-7 col-xs-12">
							
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Jumlah Operasi<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="jml_op" name="jml_op" value="<?php echo $_POST["jml_op"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("kamar_id","kamar_id",$kamarId);?>
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

