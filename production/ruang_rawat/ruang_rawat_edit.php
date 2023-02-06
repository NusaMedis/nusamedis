<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     //INISIALISASI LIBRARY
     $enc = new textEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");
     
     $depId = $auth->GetDepId(); 
     $userName = $auth->GetUserName();
     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	 
	 //INISIALISASI AWAL
     $err_code = 0;	    
	   if($_POST["gedung_rawat_id"])  $ruangRawatId = & $_POST["gedung_rawat_id"];    
     
     $backPage = "ruang_rawat_view.php"; 
     
     //JIKA ADA GET id untuk View Data
     if($_GET["id"]) 
     {
       $_x_mode = "Edit";
       $ruangRawatId = $enc->Decode($_GET["id"]);
     }
     
     //PENGATURAN MODE ADD atau UPDATE atau DELETE
     if($_POST["x_mode"]) //JIKA ADA POST MODE 
     {
      $_x_mode = & $_POST["x_mode"];
      if ($_POST["btnDelete"])       
         $_x_mode = "Delete";
	    else 
          $_x_mode = "New"; 
     }
	 
	 if($_POST["btnUpdate"])
     {
           $ruangRawatId = & $_POST["gedung_rawat_id"];             
           $_x_mode = "Edit";
      }
     
  	if($_x_mode=="New") $privMode = PRIV_CREATE;
 	  elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	  else $privMode = PRIV_DELETE;    
     
     //INISIALISASI AJAX//
  	function CheckDataKamar($kamarKode,$ruangRawatId=null)
  	{
            global $dtaccess;
            
            $sql = "SELECT a.gedung_rawat_id FROM global.global_gedung_rawat a 
                      WHERE upper(a.kamar_nama) = ".QuoteValue(DPE_CHAR,strtoupper($kamarKode));
                      
            if($ruangRawatId) $sql .= " and a.gedung_rawat_id <> ".QuoteValue(DPE_NUMERIC,$ruangRawatId);
            
            $rs = $dtaccess->Execute($sql);
            $dataAdaKamar = $dtaccess->Fetch($rs);
            
  		return $dataAdaKamar["gedung_rawat_id"];
     }
	 
	 //DATA VIEW UNTUK EDIT
     if ($_GET["id"]) {
         
          $sql = "select a.* from global.global_gedung_rawat a where a.gedung_rawat_id = ".QuoteValue(DPE_CHAR,$ruangRawatId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);

     }
   
    // FUNGSI ADD dan DELETE
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) 
    {                               
         $dbTable = "global.global_gedung_rawat";         
         $dbField[0] = "gedung_rawat_id";   // PK
         $dbField[1] = "gedung_rawat_kode";
         $dbField[2] = "gedung_rawat_nama";
         $dbField[3] = "gedung_lantai_ke";
         $dbField[4] = "id_dep"; 

         if(!$ruangRawatId) $ruangRawatId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHAR,$ruangRawatId);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["gedung_rawat_kode"]);
         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["gedung_rawat_nama"]);
         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["gedung_lantai_ke"]);
         $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
         
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
        
         header("location:".$backPage);
         exit();        
     }
 if ($_GET["del"]) {
          $ruangRawatId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_gedung_rawat where gedung_rawat_id = ".QuoteValue(DPE_CHAR,$ruangRawatId);
           $dtaccess->Execute($sql);
     
          header("location:".$backPage);
          exit();    
     }
     
     $sql = "select kelas_id,kelas_nama 
             from klinik.klinik_kelas
             ";     
      //         echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs);

       $kategoriKelas[0] = $view->RenderOption("","Pilih Kelas",$show);
     for($i=0,$n=count($dataKelas);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
          $kategoriKelas[$i+1] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
     } 
     
          $tableHeader = "Manajemen | Master Gedung / Ruang Rawat";
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
                    <h2>Master Gedung / Ruang Rawat</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Gedung /Ruang Rawat<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="gedung_rawat_kode" name="gedung_rawat_kode" value="<?php echo $_POST["gedung_rawat_kode"];?>" required="required" class="form-control col-md-7 col-xs-12">
							
						</div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Gedung / Ruang <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="gedung_rawat_nama" name="gedung_rawat_nama" value="<?php echo $_POST["gedung_rawat_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
               <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Lantai Ke <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="gedung_lantai_ke" name="gedung_lantai_ke" value="<?php echo $_POST["gedung_lantai_ke"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                                            
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("gedung_rawat_id","gedung_rawat_id",$ruangRawatId);?>
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