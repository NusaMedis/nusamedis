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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();     
	 $auth = new CAuth();
     $err_code = 0;
     $depId = $auth->GetDepId();
     $tahunTarif = $auth->GetTahunTarif();
      
	 $plx = new expAJAX("CheckDataIcd");
     $findPage = "cari_tarif.php?";
     
    /*   if(!$auth->IsAllowed("man_tarif_biaya_reg",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_biaya_reg",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     
     	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
    	else $_x_mode = "New";
   
	    if(!$_POST["id_biaya"])  $_POST["id_biaya"] = $_GET["id_biaya"];
      
      if($_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"] = $_GET["id_tahun_tarif"];
      //if(!$_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"] = $tahunTarif;
      if(!$_POST["id_dep"]) $_POST["id_dep"]=$depId; 
      
      $editPage = "biaya_edit.php";
      $backPage = "biaya_view.php?id_tahun_tarif=".$_POST["id_tahun_tarif"];
      
      if ($_GET["id"])
         $tableHeader = "TAMBAH SETUP BIAYA REGISTRASI "; 
      else
         $tableHeader = "EDIT SETUP BIAYA REGISTRASI";
       

     
    /* if(!$auth->IsAllowed("setup_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	function CheckDataIcd($icdNomor,$biayaRegistrasiId=null)
	{
          global $dtaccess;                  
	        $sql = "SELECT * FROM klinik.klinik_biaya_registrasi
              WHERE biaya_registrasi_id =".$_GET["id"];
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
            
		return $dataAdaIcd["biaya_registrasi_id"];
     }  
      
        
	   //View
     if ($_GET["id"]) {         
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaRegistrasiId = $enc->Decode($_GET["id"]);
          }
           
          $sql = "SELECT a.*,b.biaya_nama,c.biaya_total FROM klinik.klinik_biaya_registrasi a left join 
                 klinik.klinik_biaya b on a.id_biaya=b.biaya_id left join
                 klinik.klinik_biaya_tarif c on a.id_biaya_tarif = c.biaya_tarif_id
              WHERE a.biaya_registrasi_id= ".QuoteValue(DPE_CHAR,$biayaRegistrasiId); 
          
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);   
          
          $_POST["biaya_registrasi_tipe"] = $row_edit["biaya_registrasi_tipe"]; 
          /*$_POST["id_tipe_biaya"]= $row_edit["id_tipe_biaya"];
          $_POST["id_shift"]= $row_edit["id_shift"];
          $_POST["id_biaya"]= $row_edit["id_biaya"];   */
          $_POST["id_dep"]= $row_edit["id_dep"];
          $_POST["biaya_nama"]= $row_edit["biaya_nama"]; 
          $_POST["biaya_total"]= $row_edit["biaya_total"]; 
          $_POST["id_poli"] = $row_edit["id_poli"];      
           
          $dtaccess->Clear($rs_edit);
          $view->CreatePost($row_edit);
     }
      
	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnNew"]) {     
          header("location: ".$_SERVER["PHP_SELF"]);
          exit();
     }
     
     //Tambah atau Edit Ketika klik tombol Simpan
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
               
               $biayaRegistrasiId = & $_POST["biaya_registrasi_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya_registrasi";
               
               $dbField[0] = "biaya_registrasi_id";   // PK
               $dbField[1] = "id_biaya";
               $dbField[2] = "id_dep";    
               $dbField[3] = "id_biaya_tarif";  
               $dbField[4] = "biaya_registrasi_tipe";          
               if($_POST["dep_konf_reg_poli"]=="y"){
               $dbField[5] = "id_poli";
               }
               
               //$dbField[3] = "id_tahun_tarif";
               //$dbField[1] = "biaya_registrasi_tipe";   
               //$dbField[2] = "id_tipe_biaya";
               //$dbField[3] = "id_shift";
               
			
               if(!$biayaRegistrasiId) $biayaRegistrasiId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaRegistrasiId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_biaya_tarif"]);  
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["biaya_registrasi_tipe"]);              
               if($_POST["dep_konf_reg_poli"]=="y"){
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               }

               //$dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_tahun_tarif"]);
               //$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["biaya_registrasi_tipe"]);
               //$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_tipe_biaya"]);
               //$dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_shift"]);
			   
               //print_r($dbValue); die();
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
               
               /* DITUTUP BIAR SEDERHANA
               if($_POST["biaya_registrasi_tipe"]=="G"){
               $sql = "update klinik.klinik_biaya set biaya_jenis_sem='RG' where biaya_id=".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dtaccess->Execute($sql);
               } else {
               $sql = "update klinik.klinik_biaya set biaya_jenis_sem='RA' where biaya_id=".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dtaccess->Execute($sql);
               } */
               
               //Update jadi KA
               $sql = "update klinik.klinik_biaya set biaya_jenis_sem='KA' where biaya_id=".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dtaccess->Execute($sql);
               
               header("location:".$backPage);
               exit();        
          }
     }
     
     //delete
      if ($_GET["del"]) 
      {
          $biayaRegistrasiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_biaya_registrasi where biaya_registrasi_id = ".QuoteValue(DPE_CHAR,$biayaRegistrasiId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }

          

      // Combo Box
    
     $sql = "select * from global.global_tipe_biaya 
              order by tipe_biaya_id ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataTipeBiaya = $dtaccess->FetchAll($rs); 
     
     $sql= "select * from global.global_shift 
              order by shift_id ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataShift = $dtaccess->FetchAll($rs);
     
     $sql= "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId)."
             order by dep_id ASC";   
     $rs = $dtaccess->Execute($sql);
     $dataDep = $dtaccess->Fetch($rs);  
     $_POST["dep_konf_reg_poli"] = $dataDep["dep_konf_reg_poli"];  
    
     $sql= "select * from klinik.klinik_tahun_tarif
             order by tahun_tarif_nama ASC";   
     $rs = $dtaccess->Execute($sql);
     $dataTahunTarif = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_auth_poli where poli_tipe='J'  or poli_tipe='G' or poli_tipe='M' or poli_tipe='L' or poli_tipe='R' order by poli_tipe, poli_id";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);    
?>


<!DOCTYPE html>
<html lang="en">

  <?php require_once($LAY."header.php"); ?>
  <body class="nav-sm">
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
                    <h2>Master Biaya Registrasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">

 					<? if($_POST["dep_konf_reg_poli"]=='y'){ ?> 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Klinik 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select name="id_poli" class="form-control" required="required">
       					<option value="" >[ Pilih Klinik ]</option>
						<?php 
       						for($i=0,$n=count($dataPoli);$i<$n;$i++){
							?>
							<option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>"
        				<?php if($dataPoli[$i]["poli_id"]==$_POST['id_poli']) {echo"selected";}?>>
        				<?php echo $dataPoli[$i]["poli_nama"] ;?>&nbsp;
          				</option>
							<?php } ?>
						</select>
						</div>
                      </div>
                      <? } ?>  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6" for="first-name">Tipe Registrasi</label>
                        <div class="col-md-3 col-sm-3 col-xs-6">
                        <select name="biaya_registrasi_tipe" class="form-control" required="required">
       					    <option value="" >[ Pilih Tipe Registrasi ]</option>
							<option class="inputField" value="L" <?php if('L'==$_POST['biaya_registrasi_tipe']) {echo"selected";}?>>Registrasi Pasien Lama</option>
							<option class="inputField" value="B" <?php if('B'==$_POST['biaya_registrasi_tipe']) {echo"selected";}?>>Registrasi Pasien Baru</option>							
						</select>
						</div>
                      </div>        
	 		<div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Tarif</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    	<input  type="text" name="biaya_nama" id="biaya_nama" size="30" maxlength="50" readonly value="<?php echo $_POST["biaya_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
           						<input type="hidden" name="id_biaya" id="id_biaya" value="<?php echo $_POST["id_biaya"];?>"/>     
           						<input type="hidden" name="id_biaya_tarif" id="id_biaya_tarif" value="<?php echo $_POST["id_biaya_tarif"];?>"/> 
                                   <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Tarif">
           						<img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Pasien" alt="Cari Pasien" class="tombol" align="middle"/></a> 
                </div>
            </div>
            <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Biaya</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    	<input  type="text" readonly name="biaya_total" id="biaya_total" size="30" maxlength="50" readonly value="<?php echo currency_format($_POST["biaya_total"]);?>" onKeyDown="return tabOnEnter(this, event);"/>
                </div>
            </div>                         
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                      </div>
                      </div>
                      	<?php echo $view->RenderHidden("biaya_registrasi_id","biaya_registrasi_id",$biayaRegistrasiId);?>
						<?php echo $view->RenderHidden("dep_konf_reg_poli","dep_konf_reg_poli",$dataDep["dep_konf_reg_poli"]);?>
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

