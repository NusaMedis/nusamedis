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
     $tahunTarif = $auth->GetTahunTarif();
     $depId = $auth->GetDepId();
      
	   $plx = new expAJAX("CheckDataIcd,SetCmbKamar");
     $findPage = "cari_tarif.php?";
     
     
     	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
    	else $_x_mode = "New";
   
      
      $editPage = "biaya_akomodasi_edit.php";
      $backPage = "biaya_akomodasi_view.php";
      
      if ($_GET["id"])
         $tableHeader = "EDIT SETUP BIAYA AKOMODASI "; 
      else
         $tableHeader = "TAMBAH SETUP BIAYA AKOMODASI";
	
	function CheckDataIcd($icdNomor,$biayaAkomodasiId=null)
	{
          global $dtaccess;                  
	        $sql = "SELECT * FROM global.global_biaya_akomodasi
              WHERE biaya_akomodasi_id =".$_GET["id"];
          $rs = $dtaccess->Execute($sql);
          $dataAdaIcd = $dtaccess->Fetch($rs);
            
		return $dataAdaIcd["biaya_akomodasi_id"];
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
        
	   //View
     if ($_GET["id"]) {         
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaAkomodasiId = $enc->Decode($_GET["id"]);
          }
           
          $sql = "SELECT a.*,b.biaya_nama FROM global.global_biaya_akomodasi a left join klinik.klinik_biaya b on a.id_biaya=b.biaya_id
              WHERE a.biaya_akomodasi_id= ".QuoteValue(DPE_CHAR,$biayaAkomodasiId); 
          
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);   
          
          $_POST["id_kelas"]= $row_edit["id_kelas"];
          $_POST["id_kamar"]= $row_edit["id_kamar"];
          $_POST["id_biaya"]= $row_edit["id_biaya"];
          $_POST["id_dep"]= $row_edit["id_dep"];
          $_POST["biaya_nama"]= $row_edit["biaya_nama"]; 
          $_POST["id_tahun_tarif"] = $row_edit["id_tahun_tarif"];      
           
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
               
               $biayaAkomodasiId = & $_POST["biaya_akomodasi_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "global.global_biaya_akomodasi";
               
               $dbField[0] = "biaya_akomodasi_id";   // PK
               $dbField[1] = "id_biaya";
 			
               if(!$biayaAkomodasiId) $biayaAkomodasiId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaAkomodasiId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
 
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
               
               header("location:".$backPage);
               exit();        
          }
     }
     
     //delete
      if ($_GET["del"]) 
      {
          $biayaAkomodasiId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_biaya_akomodasi where biaya_akomodasi_id = ".QuoteValue(DPE_CHAR,$biayaAkomodasiId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }

          

      // Combo Box
    
     $sql = "select * from klinik.klinik_kelas order by kelas_id ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs); 
     
     $opt_kategori[0] = $view->RenderOption("","[Pilih Kelas]",$show);
     for($i=0,$n=count($dataKelas);$i<$n;$i++){   
         if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
         $opt_kategori[$i+1] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
         unset($show);
     }   
    
     $sql= "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama ASC";   
     $rs = $dtaccess->Execute($sql);
     $dataTahunTarif = $dtaccess->FetchAll($rs);    
	 
	  $sql= "select * from klinik.klinik_kamar order by kamar_nama ASC";   
     $rs = $dtaccess->Execute($sql);
     $dataKamar = $dtaccess->FetchAll($rs); 
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
                    <h2>Master Setting Tindakan Akomodasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
            					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Tindakan</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                         <input  type="text" name="biaya_nama" id="biaya_nama" size="70" maxlength="70" readonly value="<?php echo $_POST["biaya_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
            							<input type="hidden" name="id_biaya" id="id_biaya" value="<?php echo $_POST["id_biaya"];?>"/>     
            							<a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Tindakan">
            						<img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Tarif" alt="Cari Tarif" class="tombol" align="middle"/></a>
                        </div>
                      </div>                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
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
<script>document.frmEdit.icd_nomor.focus();</script>
  </body>
</html>