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
 
     

     
	if($_POST["x_mode"]) $_x_mode = & $_POST["x_mode"];
	else $_x_mode = "New";
  
  if($_GET["id_instalasi"])  $_POST["id_instalasi"] = & $_GET["id_instalasi"];
  if($_GET["id_sub_instalasi"])  $_POST["id_sub_instalasi"] = & $_GET["id_sub_instalasi"];
  if($_GET["id_poli"])  $_POST["id_poli"] = & $_GET["id_poli"];
  

  $backPage = "jadwal_dokter_view.php?id_instalasi=".$_POST["id_instalasi"]."&id_sub_instalasi=".$_POST["id_sub_instalasi"]."&id_poli=".$_POST["id_poli"];
 

  
     if($_GET["id"] || $_GET["id_dep"]) 
     {
     	
			$jadwalDokterId = $enc->Decode($_GET["id"]);
        
		  if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
          }

          $sql = "select a.*
              from klinik.klinik_jadwal_dokter a
              join global.global_auth_poli b on b.poli_id = a.id_poli
              left join global.global_auth_sub_instalasi c on c.sub_instalasi_id = a.id_sub_instalasi
              left join global.global_auth_instalasi d on a.id_instalasi = d.instalasi_id
              where jadwal_dokter_id = ".QuoteValue(DPE_CHAR,$jadwalDokterId);
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
     
             
          if($_POST["btnUpdate"])
          {
               $jadwalDokterId = & $_POST["jadwal_dokter_id"];
               $_x_mode = "Edit";
          } 
               $dbTable = " klinik.klinik_jadwal_dokter";
               
               $dbField[0] = "jadwal_dokter_id";   // PK
               $dbField[1] = "jadwal_dokter_status";  
             


               if(!$jadwalDokterId) $jadwalDokterId = $dtaccess->GetTransId();   
               $dbValue[0] = QuoteValue(DPE_CHAR,$jadwalDokterId);
               $dbValue[1] = QuoteValue(DPE_CHAR,"LIBUR");   
             
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
   
               if ($_POST["btnSave"]) {
                    $dtmodel->Insert() or die("insert  error");	
               
               } else if ($_POST["btnUpdate"]) {
                    $dtmodel->Update() or die("update  error");	
               }
               
               unset($dtmodel);
               unset($dbField);
               unset($dbValue);
               unset($dbKey);

               

      echo "<script>document.location.href='".$backPage."';</script>";
      exit(); 
             

     }

  
  //Fungsi untuk Menghapus Tindakan  
  if ($_GET["id_del"]) 
  {
      $jadwalDokterId = $_GET["id_del"];
      $sql = "delete from klinik.klinik_jadwal_dokter where jadwal_dokter_id = ".QuoteValue(DPE_CHAR,$jadwalDokterId);
      $dtaccess->Execute($sql);
      header("location:".$backPage);
      exit();
  } //AKHIR HAPUS TINDAKAN
     
    
     
      // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  global.global_auth_instalasi a";
     $sql .= " order by instalasi_urut asc";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);

      // Data Kategori Tindakan Header //
     if($_POST['id_instalasi']) $sql_where_instalasi[] = "id_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_instalasi']);
     $sql_instalasi = "select * from  global.global_auth_sub_instalasi a where 1=1";
     if ($sql_where_instalasi) $sql_instalasi .= " and ".implode(" and ",$sql_where_instalasi);
     $sql_instalasi .= " order by sub_instalasi_urut asc";
     $rs_instalasi = $dtaccess->Execute($sql_instalasi);
     $dataSubInstalasi = $dtaccess->FetchAll($rs_instalasi);

     // Data Kategori Tindakan Header //
     
     if($_POST['id_sub_instalasi']) $sql_where_poli[] = "id_sub_instalasi = ".QuoteValue(DPE_CHAR,$_POST['id_sub_instalasi']);
     $sql_poli = "select * from  global.global_auth_poli where 1=1";
     if ($sql_where_poli) $sql_poli .= " and ".implode(" and ",$sql_where_poli);
     $sql_poli .= " order by poli_urut asc";
     $rs_poli = $dtaccess->Execute($sql_poli);
     $dataPoli = $dtaccess->FetchAll($rs_poli);
     
     if($_POST['id_poli']) $sql_where_usr_poli[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST['id_poli']);
     $sql_user_poli = "select a.*,b.usr_name from  global.global_auth_user_poli a left join 
     global.global_auth_user b on a.id_usr = b.usr_id left join 
     global.global_auth_role c on b.id_rol = c.rol_id where c.rol_jabatan='D'";
     if ($sql_where_usr_poli) $sql_user_poli .= " and ".implode(" and ",$sql_where_usr_poli);
     $sql_user_poli .= " order by b.usr_name asc";
     $rs_user_poli = $dtaccess->Execute($sql_user_poli);
     $dataUserPoli = $dtaccess->FetchAll($rs_user_poli);

     
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
                    <h2>Jadwal Praktek Dokter</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
            <!--          
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Rawat <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                           <select name="biaya_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    		<option class="inputField" value="" >- Pilih Tipe Rawat -</option>
				    				<option class="inputField" value="TA" <?php if ($_POST["biaya_jenis"]=="TA") echo"selected"?>>Rawat Jalan&nbsp;</option> 
				   					 <option class="inputField" value="TI" <?php if ($_POST["biaya_jenis"]=="TI") echo"selected"?>>Rawat Inap&nbsp;</option>
           							 <option class="inputField" value="TG" <?php if ($_POST["biaya_jenis"]=="TG") echo"selected"?>>IGD&nbsp;</option>
								</select> 
						</div>
					  </div> -->
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
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Klinik  <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">      
    						  <select name="id_poli" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    						    <option class="inputField" value="" >- Pilih Klinik -</option>
    				     		<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
    				    		<option class="inputField" value="<?php echo $dataPoli[$i]["poli_id"];?>"<?php if ($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo"selected"?>><?php echo $dataPoli[$i]["poli_nama"];?>&nbsp;</option>
    				   			<?php } ?>
    				  		</select> 
                </div>
						</div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Dokter  <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">      
    						  <select name="id_dokter" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    						    <option class="inputField" value="" >- Pilih Dokter -</option>
    				     		<?php for($i=0,$n=count($dataUserPoli);$i<$n;$i++){ ?>
    				    		<option class="inputField" value="<?php echo $dataUserPoli[$i]["id_usr"];?>"<?php if ($_POST["id_dokter"]==$dataUserPoli[$i]["id_usr"]) echo"selected"?>><?php echo $dataUserPoli[$i]["usr_name"];?>&nbsp;</option>
    				   			<?php } ?>
    				  		</select> 
                </div>
						</div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Hari Praktek  <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">      
                  <select name="jadwal_dokter_hari" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
                    <option class="inputField" value="" >- Pilih Hari -</option>
                    <option class="inputField" value="1" <?php if ($_POST["jadwal_dokter_hari"]=='1') echo"selected"?>>Senin</option>
                    <option class="inputField" value="2" <?php if ($_POST["jadwal_dokter_hari"]=='2') echo"selected"?>>Selasa</option>
                    <option class="inputField" value="3" <?php if ($_POST["jadwal_dokter_hari"]=='3') echo"selected"?>>Rabu</option>
                    <option class="inputField" value="4" <?php if ($_POST["jadwal_dokter_hari"]=='4') echo"selected"?>>Kamis</option>
                    <option class="inputField" value="5" <?php if ($_POST["jadwal_dokter_hari"]=='5') echo"selected"?>>Jumat</option>
                    <option class="inputField" value="6" <?php if ($_POST["jadwal_dokter_hari"]=='6') echo"selected"?>>Sabtu</option>
                    <option class="inputField" value="7" <?php if ($_POST["jadwal_dokter_hari"]=='7') echo"selected"?>>Minggu</option>
                  </select>
                </div>
						</div>
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jam Mulai Praktek<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo $view->RenderTextBox("jadwal_dokter_jam_mulai","jadwal_dokter_jam_mulai","85","100",$_POST["jadwal_dokter_jam_mulai"],"inputField","disable", null,false);?>
						</div>
					  </div> 
            <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jam Selesai Praktek<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo $view->RenderTextBox("jadwal_dokter_jam_selesai","jadwal_dokter_jam_selesai","85","100",$_POST["jadwal_dokter_jam_selesai"],"inputField", null,false);?>
						</div>
					  </div>

             <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kuota<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <?php echo $view->RenderTextBox("jadwal_dokter_kuota","jadwal_dokter_kuota","85","100",$_POST["jadwal_dokter_kuota"],"inputField","disable", null,false);?>
            </div>
            </div> 

           
					  
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Kirim Pesan"; else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Kirim Pesan"; else echo "Simpan"; ?></button>
                        </div>
                      </div>
					  
						 
                      <? if (($_x_mode == "Edit") || ($_x_mode == "Delete")) { ?>
            						<?php echo $view->RenderHidden("jadwal_dokter_id","jadwal_dokter_id",$jadwalDokterId);?>
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
