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
     if($_GET["id_kelas"]) $_POST["id_kelas"] = $_GET["id_kelas"];		    
	   if($_POST["kamar_id"])  $kamarId = & $_POST["kamar_id"];    
     
     $backPage = "kamar_view.php"; 
     
     //JIKA ADA GET id untuk View Data
     if($_GET["id"]) 
     {
       $_x_mode = "Edit";
       $kamarId = $enc->Decode($_GET["id"]);
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
           $kamarId = & $_POST["kamar_id"];             
           $_x_mode = "Edit";
      }
     
  	if($_x_mode=="New") $privMode = PRIV_CREATE;
 	  elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	  else $privMode = PRIV_DELETE;    
     
     //INISIALISASI AJAX//
  	function CheckDataKamar($kamarKode,$kamarId=null)
  	{
            global $dtaccess;
            
            $sql = "SELECT a.kamar_id FROM klinik.klinik_kamar a 
                      WHERE upper(a.kamar_nama) = ".QuoteValue(DPE_CHAR,strtoupper($kamarKode));
                      
            if($kamarId) $sql .= " and a.kamar_id <> ".QuoteValue(DPE_NUMERIC,$kamarId);
            
            $rs = $dtaccess->Execute($sql);
            $dataAdaKamar = $dtaccess->Fetch($rs);
            
  		return $dataAdaKamar["kamar_id"];
     }
	 
	 //DATA VIEW UNTUK EDIT
     if ($_GET["id"]) {
         
          $sql = "select a.* from klinik.klinik_kamar a where a.kamar_id = ".QuoteValue(DPE_CHAR,$kamarId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);

     }
   
    // FUNGSI ADD dan DELETE
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) 
    {                               
         $dbTable = "klinik.klinik_kamar";         
         $dbField[0] = "kamar_id";   // PK
         $dbField[1] = "kamar_kode";
         $dbField[2] = "kamar_nama";
         $dbField[3] = "id_kelas";
         $dbField[4] = "id_poli";
         $dbField[5] = "id_dep"; 
         $dbField[6] = "id_gedung_rawat";
         $dbField[7] = "id_jenis_kelas";
         $dbField[8] = "kamar_fasilitas";
         
         if(!$kamarId) $kamarId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHAR,$kamarId);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kamar_kode"]);
         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["kamar_nama"]);
         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
         $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_gedung_rawat"]);
         $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_jenis_kelas"]);
         $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["kamar_fasilitas"]);
          //print_r($dbValue);// die();        
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
         
         $sql = "update global.global_auth_poli set id_kamar=".QuoteValue(DPE_CHAR,$kamarId)."
                where poli_id=".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
         $dtaccess->Execute($sql);
         
         header("location:".$backPage);
         exit();        
     }
 if ($_GET["del"]) {
          $kamarId = $enc->Decode($_GET["id"]);
    
           $sql = "update global.global_auth_poli set id_kamar=null where poli_id=".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
           $dtaccess-> Execute($sql);
           
           $sql = "delete from klinik.klinik_kamar where kamar_id = ".QuoteValue(DPE_CHAR,$kamarId);
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


     $sql = "select *
             from klinik.klinik_jenis_kelas
             order by jenis_kelas_kode asc
             ";     
      //         echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataJKelas = $dtaccess->FetchAll($rs);

    $jenisKelas[0] = $view->RenderOption("","Pilih Jenis Kelas",$show);
     for($i=0,$n=count($dataJKelas);$i<$n;$i++) {
          unset($show);
          if($_POST["id_jenis_kelas"]==$dataJKelas[$i]["jenis_kelas_id"]) $show = "selected";
          $jenisKelas[$i+1] = $view->RenderOption($dataJKelas[$i]["jenis_kelas_id"],$dataJKelas[$i]["jenis_kelas_kode"]." - ".$dataJKelas[$i]["jenis_kelas_nama"],$show);
     } 
     
      $sql = "select poli_id,poli_nama 
             from global.global_auth_poli where poli_tipe='I' or poli_tipe='C'"; 
   
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
  
     $kategoriPoli[0] = $view->RenderOption("","Pilih Poli",$show);
     for($i=0,$n=count($dataPoli);$i<$n;$i++) {
          unset($show);
          if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) $show = "selected";
          $kategoriPoli[$i+1] = $view->RenderOption($dataPoli[$i]["poli_id"],$dataPoli[$i]["poli_nama"],$show);
     }

 $sql = "select gedung_rawat_id,gedung_rawat_nama 
             from global.global_gedung_rawat 
             order by gedung_rawat_nama, gedung_lantai_ke asc
             ";     
      //         echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataGedungRawat = $dtaccess->FetchAll($rs);

       $GedungRawat[0] = $view->RenderOption("","Pilih Gedung / Ruang",$show);
     for($i=0,$n=count($dataGedungRawat);$i<$n;$i++) {
          unset($show);
          if($_POST["id_gedung_rawat"]==$dataGedungRawat[$i]["gedung_rawat_id"]) $show = "selected";
          $GedungRawat[$i+1] = $view->RenderOption($dataGedungRawat[$i]["gedung_rawat_id"],$dataGedungRawat[$i]["gedung_rawat_nama"],$show);
     } 

     $tableHeader = "Manajemen Kamar";
     	 	 
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
                    <h2>Master Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Kamar <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="kamar_kode" name="kamar_kode" value="<?php echo $_POST["kamar_kode"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kamar <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="kamar_nama" name="kamar_nama" value="<?php echo $_POST["kamar_nama"];?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelas Kamar</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <select name = "id_kelas" class="form-control" required="required">
                            <?php echo $view->RenderComboBoxNew("id_kelas","id_kelas",$kategoriKelas,null,null);?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kamar</label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <select name = "id_jenis_kelas" class="form-control" required="required">
                            <?php echo $view->RenderComboBoxNew("id_jenis_kelas","id_jenis_kelas",$jenisKelas,null,null);?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Poli</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select name = "id_poli" class="form-control" required="required">
                            <?php echo $view->RenderComboBoxNew("id_poli","id_poli",$kategoriPoli,null,null);?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Gedung / Ruang Rawat</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <select name = "id_gedung_rawat" class="form-control" required="required">
                            <?php echo $view->RenderComboBoxNew("id_gedung_rawat","id_gedung_rawat",$GedungRawat,null,null);?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Keterangan Fasilitas</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <textarea class="form-control" name="kamar_fasilitas" id="kamar_fasilitas"><?php echo $_POST['kamar_fasilitas'] ?></textarea>
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