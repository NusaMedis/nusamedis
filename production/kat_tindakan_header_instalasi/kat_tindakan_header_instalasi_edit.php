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
     $depId = $auth->GetDepId();
     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
          
     $viewPage = "kat_tindakan_header_instalasi_view.php";
     $editPage = "kat_tindakan_header_instalasi_edit.php";

    /*   if(!$auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
	
     if(!$_POST["id_tahun_tarif"] || $_POST["id_tahun_tarif"]=='') $_POST["id_tahun_tarif"]= $tahunTarif;
     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["kat_header_ins_id"])  $splitId = & $_POST["kat_header_ins_id"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $splitId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select * from klinik.klinik_kategori_tindakan_header_instalasi
                 where klinik_kategori_tindakan_header_instalasi_id = ".QuoteValue(DPE_CHAR,$splitId);
          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
         //  echo $sql;
           
          $_POST["klinik_kategori_tindakan_header_instalasi_nama"] = $row_edit["klinik_kategori_tindakan_header_instalasi_nama"];
          $_POST["id_tahun_tarif"] = $row_edit["id_tahun_tarif"];
          $_POST["id_dep"] = $row_edit["id_dep"];
          $_POST["klinik_kategori_tindakan_header_instalasi_urut"] = $row_edit["klinik_kategori_tindakan_header_instalasi_urut"];
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
               $splitId = & $_POST["kat_header_ins_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_kategori_tindakan_header_instalasi";
               
               $dbField[0] = "klinik_kategori_tindakan_header_instalasi_id";   // PK
               $dbField[1] = "klinik_kategori_tindakan_header_instalasi_nama"; 
               $dbField[2] = "id_instalasi";
               $dbField[3] = "id_tahun_tarif";
               $dbField[4] = "id_dep";
               $dbField[5] = "klinik_kategori_tindakan_header_instalasi_urut";
			
               if(!$splitId) $splitId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$splitId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["klinik_kategori_tindakan_header_instalasi_nama"]); 
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_instalasi"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["klinik_kategori_tindakan_header_instalasi_urut"]);
			
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
                  
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
                  unset($dtmodel);
                  unset($dbField);
                  unset($dbValue);
                  unset($dbKey);
               
                  header("location:".$viewPage."?klinik=".$depId."&dep_lowest=".$_POST["dep_lowest"]."&id_tahun_tarif=".$_POST["id_tahun_tarif"]);
                  exit();
          }
     }
     
     if ($_GET["del"]) {
          $splitId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_kategori_tindakan_header_instalasi where klinik_kategori_tindakan_header_instalasi_id = ".QuoteValue(DPE_CHAR,$splitId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:kat_tindakan_header_instalasi_view.php?klinik=".$depId."&dep_lowest=".$_POST["dep_lowest"]."&id_tahun_tarif=".$_POST["id_tahun_tarif"]);
          exit(); 
          }
 
     /*if ($_POST["btnDelete"]) {
          $splitId = & $_POST["cbDelete"];
          
          for($i=0,$n=count($splitId);$i<$n;$i++){
               $sql = "delete from klinik.klinik_split
                         where split_id = ".QuoteValue(DPE_CHAR,$splitId[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          }
          
          header("location:".$viewPage);
          exit();    
     } */
     
     if(!$splitId){
      $sql = "select max(klinik_kategori_tindakan_header_instalasi_urut) as total 
              from klinik.klinik_kategori_tindakan_header_instalasi where id_dep =".QuoteValue(DPE_CHAR,$depId)."
              and id_tahun_tarif=".QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);
      $rs = $dtaccess->Execute($sql);
      $Maxs = $dtaccess->Fetch($rs);
      $MaksUrut = ($Maxs["total"]+1);
      
      $_POST["klinik_kategori_tindakan_header_instalasi_urut"] = $MaksUrut;
    }
     
     $sql = "select * from klinik.klinik_tahun_tarif";
     $rs = $dtaccess->Execute($sql);
     $dataSearch = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_auth_instalasi order by instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
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
                    <h2>Setup Header Instalasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Kategori Header Instalasi<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						  <?php echo $view->RenderTextBox("klinik_kategori_tindakan_header_instalasi_nama","klinik_kategori_tindakan_header_instalasi_nama","50","100",$_POST["klinik_kategori_tindakan_header_instalasi_nama"],"inputField", null,false);?>
						</div>
                      </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Urutan<span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
						  <?php echo $view->RenderTextBox("klinik_kategori_tindakan_header_instalasi_urut","klinik_kategori_tindakan_header_instalasi_urut","50","100",$_POST["klinik_kategori_tindakan_header_instalasi_urut"],"inputField", null,false);?>
						</div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Instalasi</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name="id_instalasi" id="id_instalasi" class="select2_single form-control" onKeyDown="return tabOnEnter(this.event)">
                			<?php for($i=0,$n=count($dataInstalasi);$i<$n;$i++){ ?>
                			<option value="<?php echo $dataInstalasi[$i]["instalasi_id"];?>" <?php if($_POST["id_instalasi"]==$dataInstalasi[$i]["instalasi_id"]) echo "selected";?>><?php echo $dataInstalasi[$i]["instalasi_nama"];?></option>
                			<?php } ?>
            			  </select>
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-danger" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("kat_header_ins_id","kat_header_ins_id",$splitId);?>
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
