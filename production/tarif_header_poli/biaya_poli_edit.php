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
     $findPage = "akun_find.php?";
	
    /* if(!$auth->IsAllowed("man_tarif_tarif_header_poli",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_header_poli",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
	   $plx = new expAJAX("GetHeader");
	  
     function GetHeader()
	    {
        global $dtaccess,$view;
     $sql = "select * from klinik.klinik_kategori_tindakan_header order by kategori_tindakan_header_urut";
     $rs_edit = $dtaccess->Execute($sql);
     $dataKatHeader = $dtaccess->FetchAll($rs_edit);
			unset($header);
			$header[0] = $view->RenderOption("","[Pilih Kategori Header]",$show);
			$i = 1;
			
     for($i=0,$n=count($dataKatHeader);$i<$n;$i++){   
         if($_POST["id_kategori_tindakan_header"]==$dataKatHeader[$i]["kategori_tindakan_header_id"]) $show = "selected";
         $header[$i+1] = $view->RenderOption($dataKatHeader[$i]["kategori_tindakan_header_id"],$dataKatHeader[$i]["kategori_tindakan_header_nama"],$show);
         unset($show);
     }
			$str = $view->RenderComboBox("id_kategori_tindakan_header","id_kategori_tindakan_header",$header,null,null,null);
	 return $str;
  }
       
  if($_GET["poli"]) $_POST["poli"] = $_GET["poli"];
      
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["biaya_poli_id"])  $biayaPoliId = & $_POST["biaya_poli_id"];
     
     $backPage = "biaya_poli_view.php?poli=".$_POST["poli"];
    // $tableHeader = "&nbsp;Biaya Poli";
     
     if ($_GET["id"]) {         
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $biayaPoliId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.*,b.biaya_nama,b.biaya_id from klinik.klinik_biaya_poli a
          left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya WHERE a.biaya_poli_id= ".QuoteValue(DPE_CHAR,$biayaPoliId); 
          
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);  
          //
         $_POST["poli"] = $row_edit["id_poli"]; 
         $_POST["id_kategori_tindakan_header"]= $row_edit["id_kategori_tindakan_header"];
         $_POST["prk_nama"]= $row_edit["biaya_nama"];
         
          $sqlInstalasi = "select b.klinik_kategori_tindakan_header_instalasi_id
           from klinik.klinik_kategori_tindakan_header a left join klinik.klinik_kategori_tindakan_header_instalasi b 
           on b.klinik_kategori_tindakan_header_instalasi_id = a.id_kategori_tindakan_header_instalasi 
           WHERE a.kategori_tindakan_header_id= ".QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan_header"]); 
          $rs_instalasi = $dtaccess->Execute($sqlInstalasi);
          $row_instalasi = $dtaccess->Fetch($rs_instalasi); 
          $_POST["id_kategori_tindakan_header_instalasi"]= $row_instalasi["klinik_kategori_tindakan_header_instalasi_id"];
         
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
   
     if ($_POST["btnSave"] || $_POST["btnUpdate"]) {          
          if($_POST["btnUpdate"]){
          
               $biayaPoliId = & $_POST["biaya_poli_id"];
               $_x_mode = "Edit";
          }
         
          if ($err_code == 0) {
               $dbTable = "klinik.klinik_biaya_poli";
               
               $dbField[0] = "biaya_poli_id";   // PK
               $dbField[1] = "id_poli";   
               $dbField[2] = "id_kategori_tindakan_header";
               $dbField[3] = "id_biaya";
               //$dbField[3] = "biaya_poli_default";
			
               if(!$biayaPoliId) $biayaPoliId = $dtaccess->GetTransID();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$biayaPoliId); 
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["poli"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan_header"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["prk_no"]);
               //$dbValue[3] = QuoteValue(DPE_CHAR,$_POST["biaya_poli_default"]);

			      //   print_r($dbValue); die();
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
        if ($_GET["del"]) {
          $biayaPoliId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from klinik.klinik_biaya_poli where biaya_poli_id = ".QuoteValue(DPE_CHAR,$biayaPoliId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$backPage);
          exit();    
     }
     
          $sql = "select * from global.global_auth_poli order by poli_nama ASC";    
          $rs = $dtaccess->Execute($sql);
          $dataPoli = $dtaccess->FetchAll($rs);
          
          /*$sql = "select * from klinik.klinik_kategori_tindakan_header order by kategori_tindakan_header_urut";
          $rs = $dtaccess->Execute($sql);
          $dataKatHeader = $dtaccess->FetchAll($rs); 
          
          $katHeader[0] = $view->RenderOption("","[Pilih Kategori Header]",$show);
          for($i=0,$n=count($dataKatHeader);$i<$n;$i++){
                 unset($show);
                 if($_POST["id_kategori_tindakan_header"]==$dataKatHeader[$i]["kategori_tindakan_header_id"]) $show = "selected";
                 $katHeader[$i+1] = $view->RenderOption($dataKatHeader[$i]["kategori_tindakan_header_id"],$dataKatHeader[$i]["kategori_tindakan_header_nama"],$show);               
            }*/
			
			 // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

      // Data Kategori Tindakan Header //
     if($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_kategori_tindakan_header_instalasi']);
     $sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
     if ($sql_where_header) $sql_header .= " and ".implode(" and ",$sql_where_header);
     $sql_header .= " order by kategori_tindakan_header_urut asc";
     $rs_header = $dtaccess->Execute($sql_header);
     $dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

     //Konfigurasi
     $sql = "select dep_konf_header_klinik from global.global_departemen";
     $rs = $dtaccess->Execute($sql);
     $Konfigurasi = $dtaccess->Fetch($rs);
          
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
                    <h2>Master - Tarif Header Klinik</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
            <?php if ($Konfigurasi["dep_konf_header_klinik"]=='y') { ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Poli <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="poli" class="inputField">
              <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
              <option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["poli"]) {echo"selected";}?>><?php echo $dataPoli[$i]["poli_nama"] ;?>&nbsp;</option>
              <?php } ?>
              </select>
            </div>
                      </div>
            <?php } ?>
                     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori Tindakan Header Instalasi <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        	<select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control"  onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Kategori Tindakan Header Instalasi-</option>
				     		<?php for($i=0,$n=count($dataKategoriTindakanHeaderInstalasi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"];?>"<?php if ($_POST["id_kategori_tindakan_header_instalasi"]==$dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo"selected"?>><?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select>
						</div>
                      </div> 
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori Tindakan Header <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="select2_single form-control" name="id_kategori_tindakan_header" id="id_kategori_tindakan_header"  onKeyDown="return tabOnEnter(this, event);" >
              						<option value="--" >[Pilih Kategori Tindakan]</option>
              						<?php for($jj=0,$kk=count($dataKategoriTindakanHeader);$jj<$kk;$jj++){ ?>               
              						<option value="<?php echo $dataKategoriTindakanHeader[$jj]["kategori_tindakan_header_id"] ;?>" <?php if($_POST["id_kategori_tindakan_header"]==$dataKategoriTindakanHeader[$jj]["kategori_tindakan_header_id"])echo "selected" ;?> ><?php echo $dataKategoriTindakanHeader[$jj]["kategori_tindakan_header_nama"] ;?></option>
              						<?php }?>          
              					</select>
              					</div>
                      </div>
                      <?php if ($Konfigurasi["dep_konf_header_klinik"]=='n') { ?>
      <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Tarif</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input  type="text" name="prk_nama" id="prk_nama" size="100" maxlength="50" readonly value="<?php echo $_POST["prk_nama"];?>" onKeyDown="return tabOnEnter(this, event);"/>
                      <input type="hidden" name="prk_id" id="prk_id" value="<?php echo $_POST["prk_id"];?>"/>     
                      <input type="hidden" name="prk_no" id="prk_no" value="<?php echo $_POST["prk_no"];?>"/> 
                                   <a href="<?php echo $findPage;?>&TB_iframe=true&height=400&width=600&modal=true" class="thickbox" title="Cari Tarif">
                      <img src="<?php echo($ROOT);?>gambar/finder.png" border="0" style="cursor:pointer; margin-bottom:15px; " title="Cari Pasien" alt="Cari Pasien" class="tombol" align="middle"/></a> 
                </div>
            </div>
          <?php } ?>
                      
                      
                      
                      <div class="ln_solid"></div>
                     <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
					             <input type="hidden" name="klinik" id="klinik" value="<?php echo $_POST["klinik"];?>" />						
                      <?php echo $view->RenderHidden("biaya_poli_id","biaya_poli_id",$biayaPoliId);?>
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