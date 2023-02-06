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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $err_code = 0;
     $depId = $auth->GetDepId();
     $tahunTarif = $auth->GetTahunTarif();
     
    /* if(!$auth->IsAllowed("man_tarif_biaya_pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_biaya_pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */

     
	   $plx = new expAJAX("CheckDataIcd");
     $findPage = "cari_tarif.php?";
     
      if($_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"] = $_GET["id_tahun_tarif"];
      if($_GET["id_poli"]) $_POST["id_poli"] = $_GET["id_poli"];
     
     
     	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
    	else $_x_mode = "New";
   
	    if(!$_POST["biaya_pemeriksaan_id"])  $_POST["biaya_pemeriksaan_id"] = $_GET["biaya_pemeriksaan_id"];
      //if(!$_GET["id_tahun_tarif"]) $_POST["id_tahun_tarif"] = $tahunTarif;
      if(!$_POST["id_dep"]) $_POST["id_dep"]=$depId; 
      
      $editPage = "biaya_edit.php";
      $backPage = "biaya_view.php?id_tahun_tarif=".$_POST["id_tahun_tarif"]."&id_poli=".$_POST["id_poli"];
      
      if ($_GET["id"])
         $tableHeader = "TAMBAH SETUP BIAYA PEMERIKSAAN "; 
      else
         $tableHeader = "EDIT SETUP BIAYA PEMERIKSAAN";
       

     
    /* if(!$auth->IsAllowed("setup_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	function CheckDataIcd($icdNomor,$biayaPemeriksaanId=null)
	{
          global $dtaccess;                  
	        $sql = "SELECT * FROM klinik.klinik_biaya_pemeriksaan
              WHERE biaya_pemeriksaan_id =".$_GET["id"];
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
            
		return $dataAdaIcd["biaya_pemeriksaan_id"];
     }  
      
     
	   //View
     if ($_GET["id"]) {         
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaPemeriksaanId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "SELECT a.*,b.biaya_nama,c.biaya_tarif_id,c.biaya_total FROM klinik.klinik_biaya_pemeriksaan a left join 
                  klinik.klinik_biaya_tarif c on a.id_biaya_tarif=c.biaya_tarif_id left join
                  klinik.klinik_biaya b on a.id_biaya=b.biaya_id
              WHERE a.biaya_pemeriksaan_id= ".QuoteValue(DPE_CHAR,$biayaPemeriksaanId); 
          
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);   
          
          $_POST["biaya_pemeriksaan_id"]= $row_edit["biaya_pemeriksaan_id"]; 
          $_POST["id_tipe_biaya"]= $row_edit["id_tipe_biaya"];
          $_POST["id_shift"]= $row_edit["id_shift"];
          $_POST["id_dokter"]= $row_edit["id_dokter"];
          $_POST["id_biaya"]= $row_edit["id_biaya"];
          $_POST["id_biaya_tarif"]= $row_edit["id_biaya_tarif"];
          $_POST["biaya_total"]= $row_edit["biaya_total"];
          $_POST["id_dep"]= $row_edit["id_dep"];
          $_POST["id_tahun_tarif"]= $row_edit["id_tahun_tarif"];
          $_POST["id_poli"]= $row_edit["id_poli"];
          $_POST["biaya_nama"]= $row_edit["biaya_nama"];        
           
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
               
               $biayaPemeriksaanId = & $_POST["biaya_pemeriksaan_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya_pemeriksaan";
               
               $dbField[0] = "biaya_pemeriksaan_id";   // PK
               $dbField[1] = "id_tipe_biaya";   
               $dbField[2] = "id_shift";
               $dbField[3] = "id_biaya";
               $dbField[4] = "id_dep";              
               $dbField[5] = "id_poli";
               $dbField[6] = "id_biaya_tarif";
			
               if(!$biayaPemeriksaanId) $biayaPemeriksaanId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaPemeriksaanId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_tipe_biaya"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_shift"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_biaya_tarif"]);
			        // print_r($dbValue); die();
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
               
               $sql = "select * from global.global_auth_poli where poli_tipe='G'";
               $rs = $dtaccess->Execute($sql);
               $poliIgd = $dtaccess->Fetch($rs);
               
               if($_POST["id_poli"]==$poliIgd["poli_id"]){
               $sql = "update klinik.klinik_biaya set biaya_jenis_sem='KG' where biaya_id=".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dtaccess->Execute($sql);
               } else {
               $sql = "update klinik.klinik_biaya set biaya_jenis_sem='KA' where biaya_id=".QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               $dtaccess->Execute($sql);
               }
               
               header("location:".$backPage);
               exit();        
          }
     }
     
     //delete
      if ($_GET["del"]) 
      {
          $biayaPemeriksaanId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_biaya_pemeriksaan where biaya_pemeriksaan_id = ".QuoteValue(DPE_CHAR,$biayaPemeriksaanId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }



      // Combo Box
    /* $sql = "select * from klinik.klinik_biaya_pemeriksaan";    
     $rs = $dtaccess->Execute($sql);
     $data1 = $dtaccess->FetchAll($rs); 
     */  
     $sql = "select * from global.global_auth_user where id_rol='2' or id_rol='5' 
              order by usr_name ASC";    
     $rs = $dtaccess->Execute($sql);
     $data2 = $dtaccess->FetchAll($rs); 
     
     $sql= "select * from global.global_shift 
              order by shift_id ASC";    
     $rs = $dtaccess->Execute($sql);
     $data3 = $dtaccess->FetchAll($rs);
     
    /* $sql= "select * from klinik.klinik_biaya 
               order by biaya_nama ASC";  
     $rs = $dtaccess->Execute($sql);
     $data4 = $dtaccess->FetchAll($rs); */
     
     $sql= "select * from global.global_departemen
             order by dep_id ASC";   
     $rs = $dtaccess->Execute($sql);
     $data5 = $dtaccess->FetchAll($rs); 
    
     $sql= "select * from klinik.klinik_tahun_tarif
             order by tahun_tarif_id ASC";   
     $rs = $dtaccess->Execute($sql);
     $data6 = $dtaccess->FetchAll($rs);
     
     $sql= "select * from global.global_auth_poli where (poli_tipe='J' or poli_tipe='M' or poli_tipe='G')
             order by poli_nama ASC";   
     $rs = $dtaccess->Execute($sql);
     $data7 = $dtaccess->FetchAll($rs);
     
     $sql= "select * from global.global_tipe_biaya
             order by tipe_biaya_id ASC";   
     $rs = $dtaccess->Execute($sql);
     $data9 = $dtaccess->FetchAll($rs);
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
                    <h2>Master Biaya Pemeriksaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Klinik </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                           <select name="id_poli" id="id_poli" class="form-control">
       						<option value="" >[ Pilih Nama Klinik ]</option>
								<?php 
       							for($i=0,$n=count($data7);$i<$n;$i++){
								?>
							<option class="inputField" value="<?php echo $data7[$i]["poli_id"];?>"
        					<?php if($data7[$i]["poli_id"]==$_POST['id_poli']) {echo"selected";}?>>
        					<?php echo $data7[$i]["poli_nama"] ;?>&nbsp;
          					</option>
							<?php } ?>
							</select>
                        </div>
                      </div>
                 

	 				<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Tarif</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          	<input  type="text" name="biaya_nama" id="biaya_nama" size="30" maxlength="50" readonly value="<?php echo $_POST["biaya_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
                          	<input  type="text" name="biaya_total" id="biaya_total" size="30" maxlength="50" readonly value="<?php echo $_POST["biaya_total"];?>" onKeyDown="return tabOnEnter(this, event);"/>

       						<input type="hidden" name="id_biaya" id="id_biaya" value="<?php echo $_POST["id_biaya"];?>"/>   
                   <input type="hidden" name="id_biaya_tarif" id="id_biaya_tarif" value="<?php echo $_POST["id_biaya_tarif"];?>"/>     
       						<a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Tarif">
       						<img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Pasien" alt="Cari Pasien" class="tombol" align="middle"/></a>
       
                      </div>
                      </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("biaya_pemeriksaan_id","biaya_pemeriksaan_id",$biayaPemeriksaanId);?>
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
