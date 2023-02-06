<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
	 require_once($LIB."tampilan.php");		
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	 $auth = new CAuth();
	 $depNama = $auth->GetDepNama();
	 $userName = $auth->GetUserName();
	 $depId = $auth->GetDepId();
	 $findPage = "akun_prk.php?";
	 $findPageBeban = "akun_prk_beban.php?";
 
     /*
     if(!$auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_rawat_jalan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     //Keterangan CITO
    $labelCito["C"] = "CITO";
    $labelCito["E"] = "Non CITO";
    
    if($_POST["biaya_tarif_id"]) { 
       $biayaTarifId = $_POST["biaya_tarif_id"];
       $_POST["biaya_tarif_id"] = $_POST["biaya_tarif_id"];
       }
     
     if($_GET["biaya_tarif_id"]) { 
       $biayaTarifId = $_GET["biaya_tarif_id"];
       $_POST["biaya_tarif_id"] = $_GET["biaya_tarif_id"];
     }  
     
     if($_POST["split_id"]) { 
       $splitId = $_POST["split_id"];
       $_POST["split_id"] = $_POST["split_id"];
       }
     
     if($_GET["split_id"]) { 
       $splitId = $_GET["split_id"];
       $_POST["split_id"] = $_GET["split_id"];
     }       
     
      $sql = "select h.klinik_kategori_tindakan_header_instalasi_id,g.kategori_tindakan_header_id,
              c.kategori_tindakan_id
              from  klinik.klinik_biaya_tarif a           
              left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id     
              left join klinik.klinik_kategori_tindakan c on c.kategori_tindakan_id = b.biaya_kategori
              left join klinik.klinik_kategori_tindakan_header g on c.id_kategori_tindakan_header = g.kategori_tindakan_header_id 
              left join klinik.klinik_kategori_tindakan_header_instalasi h on g.id_kategori_tindakan_header_instalasi = h.klinik_kategori_tindakan_header_instalasi_id 
              where a.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId);        
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      $dataAwal = $dtaccess->Fetch($rs);      
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataAwal = $dtaccess->Fetch($rs);
      $_POST["id_kategori_tindakan_header_instalasi"] =  $dataAwal["klinik_kategori_tindakan_header_instalasi_id"];
      $_POST["id_kategori_tindakan_header"] =  $dataAwal["kategori_tindakan_header_id"];
      $_POST["biaya_kategori"] =  $dataAwal["kategori_tindakan_id"];
     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
    if($_GET["id_kategori_tindakan_header_instalasi"])  $_POST["id_kategori_tindakan_header_instalasi"] = & $_GET["id_kategori_tindakan_header_instalasi"];
    if($_GET["id_kategori_tindakan_header"])  $_POST["id_kategori_tindakan_header"] = & $_GET["id_kategori_tindakan_header"];
    if($_GET["biaya_kategori"])  $_POST["biaya_kategori"] = & $_GET["biaya_kategori"];
    if($_GET["biaya_id"])  $_POST["biaya_id"] = & $_GET["biaya_id"];
    if(!$_POST["is_cito"]) $_POST["is_cito"] = "E"; //dibuat default elektif
  

    $backPage = "tindakan_split_detail_remun_view.php?split_id=".$splitId."&biaya_tarif_id=".$biayaTarifId."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
 

  
     if($_GET["id"] || $_GET["id_dep"]) 
     {
     	
		 $biayaRemunerasiId = $enc->Decode($_GET["id"]);
        
		  if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
          }

          $sql = "select a.*
              from klinik.klinik_biaya_remunerasi a 
              where a.biaya_remunerasi_id = ".QuoteValue(DPE_CHAR,$biayaRemunerasiId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
          $dtaccess->Clear($rs_edit); 
          
      }         

	if($_x_mode=="New") $privMode = PRIV_CREATE;
	elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	else $privMode = PRIV_DELETE;    

     if ($_POST["btnSave"] || $_POST["btnUpdate"])
     { 
     
               $dbTable = " klinik.klinik_biaya_remunerasi";
               
               $dbField[0] = "biaya_remunerasi_id";   // PK
               $dbField[1] = "biaya_remunerasi_nominal";  
               $dbField[2] = "id_folio_posisi";
               $dbField[3] = "id_biaya_tarif";
               $dbField[4] = "id_split";
               
               if($_POST["biaya_remunerasi_id"]) $biayaRemunerasiId = $_POST["biaya_remunerasi_id"];
               else $biayaRemunerasiId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaRemunerasiId);
               $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["biaya_remunerasi_nominal"]));   
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_folio_posisi"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$biayaTarifId);
               $dbValue[4] = QuoteValue(DPE_CHAR,$splitId);
  
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
               
               if($_POST["biaya_remunerasi_id"])
                    $dtmodel->Update() or die("Update  error");	
               else
                    $dtmodel->Insert() or die("insert  error");	
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);


      echo "<script>document.location.href='".$backPage."';</script>";
      exit(); 
             

     }

     $sql = "select * from klinik.klinik_split a join klinik.klinik_kategori_split_header b
             on a.split_id = b.id_split where 
             b.id_kategori_header = ".QuoteValue(DPE_CHAR,$_GET["id_kategori_tindakan_header"])."
             and  a.split_flag = ".QuoteValue(DPE_CHAR,SPLIT_PERAWATAN)."
             order by b.klinik_kategori_split_header_urut asc";
     //echo $sql;
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);  
  
  //Fungsi untuk Menghapus Tindakan  
  if ($_GET["del"]) 
  {
          $biayaRemunerasiId = $_GET["id_del"];
          
          $sql = "delete from klinik.klinik_biaya_remunerasi where biaya_remunerasi_id=".QuoteValue(DPE_CHAR,$biayaRemunerasiId);
          $dtaccess->Execute($sql);
         
          header("location:".$backPage);
          exit();
     } //AKHIR HAPUS TINDAKAN

     //-- bikin keterangan untuk Master Kelas --//
     
     
       $sql = "select * from klinik.klinik_folio_posisi  order by fol_posisi_nama asc";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA);  
       $dataPosisi = $dtaccess->FetchAll($rs);
     
   $sql = "select d.bea_split_nominal,a.biaya_total,a.is_cito,b.biaya_nama,c.kelas_nama , e.split_nama
              from  klinik.klinik_biaya_split d 
              left join klinik.klinik_biaya_tarif a on d.id_biaya_tarif = a.biaya_tarif_id           
              left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id   
              left join klinik.klinik_kelas c on c.kelas_id = a.id_kelas   
              left join klinik.klinik_split e on e.split_id = d.id_split
              where a.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId)." and id_split = ".QuoteValue(DPE_CHAR,$_GET['split_id']);
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataHeader = $dtaccess->Fetch($rs);
      
          $sql = "select sum(biaya_remunerasi_nominal) as jumlah 
              from  klinik.klinik_biaya_remunerasi a            
              where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$biayaTarifId)." and 
              a.id_split = ".QuoteValue(DPE_CHAR,$splitId);
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataRemunNominal = $dtaccess->Fetch($rs);
     
     $tableHeader = "Manajemen - Tambah Split Tarif Tindakan per Posisi";

     
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
            <!-- row filter -->
			      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h3>Nama Tindakan : <?php echo $dataHeader["biaya_nama"];?></h3>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_title">
                    <h2>Nama Split : <?php echo $dataHeader["split_nama"];?></h2>
                    <div class="clearfix"></div>
                  </div>                                                                                        
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >          
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">Total Nominal Jasmed yg sudah ada : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo currency_format($dataRemunNominal["jumlah"]);?></label>
          			  </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">Kelas : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo $dataHeader["kelas_nama"];?></label>
          			  </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">CITO : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo $labelCito[$dataHeader["is_cito"]];?></label>
				      </div>
     				   <div class="col-md-4 col-sm-4 col-xs-4">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4">&nbsp;</label>                     
  						<?php echo "$tombolAdd"; ?>
  				      </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">&nbsp;</label>
						<?php echo "$tombolKembali"; ?>
				    </div>				    
					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->
            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">         
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12">Posisi</label>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <select name="id_folio_posisi" id="id_folio_posisi" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
				    		<option class="inputField" value="--" >- Pilih Posisi -</option>
				     		<?php for($i=0,$n=count($dataPosisi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataPosisi[$i]["fol_posisi_id"];?>"<?php if ($_POST["id_folio_posisi"]==$dataPosisi[$i]["fol_posisi_id"]) echo"selected"?>><?php echo $dataPosisi[$i]["fol_posisi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select>
             </div>
            </div>

		    <div class="form-group">
             <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nominal Jasa Medik<span class="required">*</span>
             </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                  <?php echo $view->RenderTextBox("biaya_remunerasi_nominal","biaya_remunerasi_nominal","85","100",currency_format($_POST["biaya_remunerasi_nominal"]),"curedit", "",true);?>
      		</div>
      	  </div>
            
            <div class="ln_solid"></div>
            <div class="form-group">
              <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <button id="btnSave" name="btnSave" type="submit" value="Simpan" class="btn btn-success">Simpan</button>
                <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
              </div>
            </div>                                 
            <input type="hidden" name="biaya_remunerasi_id" id="biaya_remunerasi_id" value="<?php echo $biayaRemunerasiId;?>" />           
            <input type="hidden" name="biaya_tarif_id" id="biaya_tarif_id" value="<?php echo $biayaTarifId;?>" /> 
            <input type="hidden" name="split_id" id="split_id" value="<?php echo $splitId;?>" />    
                      <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
						
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
