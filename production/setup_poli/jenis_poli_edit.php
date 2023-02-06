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
     require_once($LIB."tree.php");	
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0; 
     $depId = $auth->GetDepId();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $tree = new CTree("global.global_auth_poli","poli_tree", TREE_LENGTH_CHILD);
     
    /* if(!$auth->IsAllowed("man_medis_setup_poli",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_setup_poli",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
     $jenisPoli["J"]="Rawat Jalan";
     $jenisPoli["I"]="Rawat Inap";
     $jenisPoli["L"]="Laboratorium";
     $jenisPoli["R"]="Radiologi";
     $jenisPoli["M"]="Rehab Medik";
     $jenisPoli["G"]="IGD";
     
     
     $viewPage = "jenis_poli_view.php?konf=".$_GET["konf"]."&tipe=".$_GET["tipe"]."&lanjut=1";
     $editPage = "jenis_poli_edit.php?konf=".$_GET["konf"];
     	$dokterPage = "rawat_dokter_find.php?";
	$susterPage = "rawat_suster_find.php?";
	$bidanPage = "rawat_bidan_find.php?";
	
	$plx = new expAJAX("CheckDataCustomerTipe,GetSubIns");
	
	function CheckDataCustomerTipe($custTipeNama)
	{
          global $dtaccess;
          
          $sql = "SELECT a.rujukan_id from global.global_auth_poli a 
                    WHERE upper(a.poli_nama) = ".QuoteValue(DPE_CHAR,strtoupper($custTipeNama));
          $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $datasplit = $dtaccess->Fetch($rs);
          
		return $datasplit["poli_id"];
     }
     
  function GetSubIns($insId)
	{
  global $dtaccess,$view;
     $sql = "select * from global.global_auth_sub_instalasi where id_instalasi=".QuoteValue(DPE_CHAR,$insId)." order by sub_instalasi_nama";
     $rs_edit = $dtaccess->Execute($sql);
     $dataSubIns = $dtaccess->FetchAll($rs_edit);  
			unset($sub);
			$sub[0] = $view->RenderOption("","[Pilih Sub Instalasi]",$show);
			$i = 1;
			
     for($i=0,$n=count($dataSubIns);$i<$n;$i++){   
         if($_POST["id_sub_instalasi"]==$dataSubIns[$i]["sub_instalasi_id"]) $show = "selected";
         $sub[$i+1] = $view->RenderOption($dataSubIns[$i]["sub_instalasi_id"],$dataSubIns[$i]["sub_instalasi_nama"],$show);
         unset($show);
     }
			$str = $view->RenderComboBox("id_sub_instalasi","id_sub_instalasi",$sub,null,null,null);
	 return $str;
  }
	
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
   
	if($_POST["poli_id"])  $poliId = & $_POST["poli_id"];
  if($_POST["parent"]) $parent = & $_POST["parent"];
 
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $poliId = $enc->Decode($_GET["id"]);
          }
         
          $sql = "select a.* , b.biaya_id from global.global_auth_poli a left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
				where poli_id = ".QuoteValue(DPE_CHAR,$poliId);       


          $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          //echo $sql;
          $_POST["poli_nama"] = $row_edit["poli_nama"];
          $_POST["id_biaya"] = $row_edit["id_biaya"];
          $_POST["jenis_tindakan"] = $row_edit["jenis_tindakan"];  
          $_POST["id_instalasi"] = $row_edit["id_instalasi"];
          $_POST["poli_tipe"] = $row_edit["poli_tipe"];
          $_POST["poli_kode"] = $row_edit["poli_kode"];
          $_POST["poli_tree"] = $row_edit["poli_tree"];
          $_POST["gudang_id"] = $row_edit["id_gudang"];
          $_POST["id_sub_instalasi"] = $row_edit["id_sub_instalasi"];
          $_POST["poli_bpjs"] = $row_edit["poli_bpjs"];
          $_POST["id_gudang_nonmedis"] = $row_edit["id_gudang_nonmedis"]; 
          $_POST["poli_antrian"] = $row_edit["poli_antrian"];
          $_POST["poli_antrian_urut"] = $row_edit["poli_antrian_urut"];         
     }
     
     if($_GET["parent"]){
        $sql = "select * from global.global_auth_poli where poli_tree=".QuoteValue(DPE_CHAR,$_GET["parent"]);
        $rs = $dtaccess->Execute($sql);
        $dataParent = $dtaccess->Fetch($rs);
        //echo $sql;
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
               $poliId = & $_POST["poli_id"];
               $_x_mode = "Edit";
          }
 
         
          if ($err_code == 0) {
               $sql = "select max(poli_urut) as poliurut from global.global_auth_poli order by poliurut desc";
               $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
               $dataUrut = $dtaccess->Fetch($rs_edit);
               
               $_POST['urut'] = $dataUrut['poliurut']+1;
               
               $dbTable = "global.global_auth_poli";
               
               $dbField[0] = "poli_id";   // PK
               $dbField[1] = "poli_nama";
               //$dbField[2] = "id_biaya";
               //$dbField[3] = "jenis_tindakan";
               $dbField[2] = "id_dep";
               $dbField[3] = "id_instalasi";
               $dbField[4] = "poli_tipe";
               $dbField[5] = "poli_kode";
               $dbField[6] = "poli_tree";
               $dbField[7] = "id_gudang";
               $dbField[8] = "id_sub_instalasi";
               $dbField[9] = "poli_bpjs";
               $dbField[10] = "id_gudang_nonmedis"; 
               $dbField[11] = "poli_antrian";
               $dbField[12] = "poli_antrian_urut";              
               if($_POST['btnSave']){
               $dbField[13] = "poli_sub";
               $dbField[14] = "poli_urut"; 
               }
               
               
              if(!$poliId) $poliId = $dtaccess->GetTransID(); 
              if(!$_POST["poli_tree"]) $_POST["poli_tree"] = $tree->AddChild($parent);
               $dbValue[0] = QuoteValue(DPE_CHAR,$poliId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["poli_nama"]);
               //$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_biaya"]);
               //$dbValue[3] = QuoteValue(DPE_CHAR,$_POST["tindakan_jenis"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_instalasi"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["poli_tipe"]);
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["poli_kode"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["poli_tree"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["gudang_id"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_sub_instalasi"]);
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["poli_bpjs"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_gudang_nonmedis"]);
               if($_POST["poli_antrian"]=="y"){
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["poli_antrian"]); }else{
               $dbValue[11] = QuoteValue(DPE_CHAR,"n"); 
               }
               if($_POST["poli_antrian_urut"]!=""){
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["poli_antrian_urut"]);
               }else{
               $dbValue[12] = QuoteValue(DPE_CHAR,0);
               }
               if($_POST['btnSave']){
               if($parent){
               $dbValue[13] = QuoteValue(DPE_CHAR,'y');
               } else {
               $dbValue[13] = QuoteValue(DPE_CHAR,'n');
               }     
               $dbValue[14] = QuoteValue(DPE_NUMERIC,$_POST['urut']);}
               //print_r($dbValue); die();
               
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
               
                  header("location:".$viewPage);
                  exit();
                  
            $sql = "select a.poli_id from global.global_auth_poli a left join global.global_auth_instalasi b on b.instalasi_id=a.id_instalasi
                    order by b.instalasi_urut, a.poli_nama";
            $rs = $dtaccess->Execute($sql);
            $dataPoli = $dtaccess->FetchAll($rs);
            
            for($i=0,$n=count($dataPoli);$i<$n;$i++){
              $sql = "update global.global_auth_poli set poli_urut=".QuoteValue(DPE_NUMERIC,($i+1))."
                      where poli_id=".QuoteValue(DPE_CHAR,$dataPoli[$i]["poli_nama"]);
              $dtaccess->Execute($sql);
            }
          }
     }
 
    if ($_GET["del"]) {
          $poliId = $enc->Decode($_GET["id"]);
          
          // $sql = "delete from global.global_auth_poli where poli_tree like ".QuoteValue(DPE_CHAR,$_GET["parent"]."%");
          // $dtaccess->Execute($sql);
           
           $sql = "delete from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$poliId);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    
    
          header("location:".$viewPage);
          exit();    
     }
 
    
      // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  global.global_auth_instalasi a where a.id_dep = '$depId'";
     $sql .= " order by instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);

      // Data Kategori Tindakan Header //
     if($_POST['id_instalasi']) $sql_where_instalasi[] = "id_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_instalasi']);
     $sql_instalasi = "select * from  global.global_auth_sub_instalasi a where 1=1 and id_dep = '$depId'";
     if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by sub_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataSubInstalasi = $dtaccess->FetchAll($rs_instalasi);
     
     $sql = "select * from global.global_auth_poli_tipe 
                order by poli_tipe_nama ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataTipe = $dtaccess->FetchAll($rs);
     
     $sql = "select * from logistik.logistik_gudang 
             where id_dep = ".QuoteValue(DPE_CHAR,$depId)."
                order by gudang_nama ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataGudang = $dtaccess->FetchAll($rs);

     $sql = "select * from umum.umum_gudang 
             where id_dep = ".QuoteValue(DPE_CHAR,$depId)."
             order by gudang_nama ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataGudangNonMedis = $dtaccess->FetchAll($rs);
     //echo $sql;
     $sql = "select * from global.global_departemen
                order by dep_nama ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataIdDep = $dtaccess->FetchAll($rs);
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
                    <h2>Setup Jenis Poli</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>"">
                      
                      <?php if($_GET["parent"]){ ?>
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Parent Klinik/Poli<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							  <?php echo $view->RenderTextBox("poli_parent","poli_parent","50","100",$dataParent["poli_nama"],"inputField", "readonly",false);?>
						</div>
                      </div>
					  <?php } ?>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Klinik/Poli<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<?php echo $view->RenderTextBox("poli_nama","poli_nama","50","100",$_POST["poli_nama"],"inputField", null,false);?>
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Klinik/Poli<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<?php echo $view->RenderTextBox("poli_kode","poli_kode","50","100",$_POST["poli_kode"],"inputField", null,false);?>
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Klinik/Poli BPJS<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<?php echo $view->RenderTextBox("poli_bpjs","poli_bpjs","50","100",$_POST["poli_bpjs"],"inputField", null,false);?>
						</div>
                      </div>
                     
					  <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Instalasi</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
						<select name="id_instalasi" class="select2_single form-control"  onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Instalasi-</option>
				     		<?php for($i=0,$n=count($dataInstalasi);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataInstalasi[$i]["instalasi_id"];?>"<?php if ($_POST["id_instalasi"]==$dataInstalasi[$i]["instalasi_id"]) echo"selected"?>><?php echo $dataInstalasi[$i]["instalasi_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		</select> 
              </div> 
				    </div>
					  <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Sub Instalasi <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
    						<select name="id_sub_instalasi" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
  						    <option class="inputField" value="" >- Pilih Sub Instalasi -</option>
  				    		 <?php for($i=0,$n=count($dataSubInstalasi);$i<$n;$i++){ ?>
  				   			 <option class="inputField" value="<?php echo $dataSubInstalasi[$i]["sub_instalasi_id"];?>"<?php if ($_POST["id_sub_instalasi"]==$dataSubInstalasi[$i]["sub_instalasi_id"]) echo"selected"?>><?php echo $dataSubInstalasi[$i]["sub_instalasi_nama"];?>&nbsp;</option>
  				  			 <?php } ?>
  				  		</select> 
              </div>
						</div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Poli<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select name="poli_tipe" id="poli_tipe" class="form-control">
							<option value="">[ Pilih Tipe Poli ]</option>
								<?php for($i=0,$n=count($dataTipe);$i<$n;$i++){ ?>
									<option class="inputField" value="<?php echo $dataTipe[$i]["poli_tipe_id"];?>"
								<?php if($dataTipe[$i]["poli_tipe_id"]==$_POST["poli_tipe"]) {echo"selected";}?>>
								<?php echo $dataTipe[$i]["poli_tipe_nama"] ;?>
							</option>
								<?php } ?>
						</select>
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Gudang<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">	  
						   <select name="gudang_id" class="form-control">
						   <option value="">[ Pilih Gudang ]</option>
									<?php for($i=0,$n=count($dataGudang);$i<$n;$i++){ ?>
										<option class="inputField" value="<?php echo $dataGudang[$i]["gudang_id"];?>"
										<?php if($dataGudang[$i]["gudang_id"]==$_POST["gudang_id"]) {echo"selected";}?>>
										<?php echo $dataGudang[$i]["gudang_nama"] ;?>&nbsp;
										</option>
									<?php } ?>
							</select>
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Gudang Non Medis<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">	  
						   <select name="id_gudang_nonmedis" class="form-control">
						   <option value="">[ Pilih Gudang Non Medis ]</option>
									<?php for($i=0,$n=count($dataGudangNonMedis);$i<$n;$i++){	?>
										<option class="inputField" value="<?php echo $dataGudangNonMedis[$i]["gudang_id"];?>"
										<?php if($dataGudangNonMedis[$i]["gudang_id"]==$_POST["id_gudang_nonmedis"]) {echo"selected";}?>>
										<?php echo $dataGudangNonMedis[$i]["gudang_nama"] ;?>&nbsp;
						   </option>
									<?php } ?>
							</select>
						</div>
                      </div>
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Poli Antrian Aktif<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">	    
							<input type="checkbox" name="poli_antrian" id="poli_antrian" value="y" <?php if($_POST["poli_antrian"]=="y"){echo "checked";}?>>
						</div>
                       </div>

					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Urutan Poli Antrian<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">	    
							<input type="text" class="form-control" name="poli_antrian_urut" id="poli_antrian_urut" value="<?php echo $_POST["poli_antrian_urut"];?>">
						</div>
                       </div>
                                     
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","btn btn-success",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
                          <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","btn btn-warning",false,"onClick=\"document.location.href='".$viewPage."';\"");?>     
                        </div>
                      </div>
                      <?php echo $view->RenderHidden("instalasi_id","instalasi_id",$instalasiId);?>
                      <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
          					  <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
          						<?php echo $view->RenderHidden("poli_id","poli_id",$poliId);?>
          						<? } ?>
          						<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
          						<?php echo $view->RenderHidden("konf","konf",$_GET["konf"]);?>
          						<?php echo $view->RenderHidden("parent","parent",$_GET["parent"]);?>
          						<?php echo $view->RenderHidden("poli_tree","poli_tree",$_POST["poli_tree"]);?>
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
