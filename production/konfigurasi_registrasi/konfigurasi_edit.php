<?php
    // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
	   require_once($LIB."tampilan.php");	
     
     // INISIALISASY LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();   
	   $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
     $viewPage = "konfigurasi_edit.php";
     //$bantuan = $ROOT."module/bantuan/admin/ganti_password.php";       
     
   /*if(!$auth->IsAllowed("man_pengaturan_konf_medik",PRIV_READ)){
          die("access_denied");
         exit(1);
          
    } elseif($auth->IsAllowed("man_pengaturan_konf_medik",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
    }*/ 
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 

          //btn simpan edit shift layanan
     if($_POST["btnSimpanShift"])
     {
        $shiftId = & $_POST["shift_id"];
        $_x_mode = "Edit";
        
        $dbTable = "global.global_shift";
        
        $dbField[0] = "shift_id";
        $dbField[1] = "shift_nama";
        $dbField[2] = "shift_jam_awal";
        $dbField[3] = "shift_jam_akhir";
        $dbField[4] = "shift_aktif";
        $dbField[5] = "id_dep";
        
        if(!$shiftId) $shiftId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$shiftId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["shift_nama"]);
        $dbValue[2] = QuoteValue(DPE_DATE,$_POST["shift_jam_awal"]);
        $dbValue[3] = QuoteValue(DPE_DATE,$_POST["shift_jam_akhir"]);
        $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["shift_aktif"]);
        $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
        
        $dbKey[0] = 0;
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
        
        $dtmodel->Update() or die("update error");
        
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
     
        header("location:".$viewPage);
        exit();
     }
     
     

     if ($_POST["btnSave"] ) 
     {
       //Simpan untuk Konfigurasi Registrasi
       $konfId = & $_POST["konf_reg_id"]; 
          
 		   $dbTable = "global.global_konf_reg";              

       $dbField[0] = "konf_reg_id";   // PK
       $dbField[1] = "konf_reg_sebab_sakit"; 
     	 $dbField[2] = "id_dep";
       $dbField[3] = "konf_reg_sebab_sakit_aktif";
       $dbField[4] = "konf_reg_istri";
       $dbField[5] = "konf_reg_ayah";
       $dbField[6] = "konf_reg_ibu";
       $dbField[7] = "konf_reg_layanan";
       $dbField[8] = "konf_reg_shift";
       $dbField[9] = "konf_reg_cara_kunjungan";
       $dbField[10] = "konf_reg_cara_kunjungan_aktif";
       
       $dbValue[0] = QuoteValue(DPE_CHAR,$konfId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["konf_reg_sebab_sakit"]);  
       $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["konf_reg_sebab_sakit_aktif"]);
       $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["konf_reg_istri"]);
       $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["konf_reg_ayah"]);
       $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["konf_reg_ibu"]);
       $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["konf_reg_layanan"]);
       $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["konf_reg_shift"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["konf_reg_cara_kunjungan"]);
       $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["konf_reg_cara_kunjungan_aktif"]); 
       //print_r($dbValue);
       //die();
  		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  		$dtmodel->Update() or die("update  error");	 
  		$simpan=1;
  		
  		unset($dbTable);
      unset($dtmodel);
  		unset($dbField);
  		unset($dbValue);
  		unset($dbKey);
      
      //Simpan untuk Departemen    
		  $dbTable = "global.global_departemen";
               
      $dbField[0] = "dep_id";   // PK
      $dbField[1] = "dep_konf_alamat"; 
      $dbField[2] = "dep_layanan_paten";      
      $dbField[3] = "dep_panjang_kode_pasien";
      $dbField[4] = "dep_jml_nol_depan";
      $dbField[5] = "dep_tipe_no_rm";
      $dbField[6] = "dep_konf_loket_antrian_poli";
      $dbField[7] = "dep_antrian_poli";
      $dbField[8] = "dep_view_kelas_asal_rawat_inap";
      $dbField[9] = "dep_konf_jam_aturan_pakai";
      $dbField[10] = "dep_konf_tindakan_rujukan";
              
      $dbValue[0] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dep_konf_alamat"]);  
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dep_layanan_paten"]); 
      $dbValue[3] = QuoteValue(DPE_NUMERIC,$_POST["dep_panjang_kode_pasien"]); 
      $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["dep_jml_nol_depan"]); 
      $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["dep_tipe_no_rm"]);  
      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["dep_konf_loket_antrian_poli"]);  
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["dep_antrian_poli"]);  
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dep_view_kelas_asal_rawat_inap"]);
      $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["dep_konf_jam_aturan_pakai"]); 
      $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["dep_konf_tindakan_rujukan"]); 

  		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  		$dtmodel->Update() or die("update  error");	 
  		$simpan=1;
  		
  		unset($dbTable);
      unset($dtmodel);
  		unset($dbField);
  		unset($dbValue);
  		unset($dbKey);
  
     }  // AKHIR DARI SIMPAN KONFIGURASI REGISTRASI


   // jika shift pelayanan di update // 
     if ($_GET["edit"]) {
               
//           $_x_mode = "Edit";
           $shiftId = $_GET["shift_id"];
               
          // buat nampilin data yg sudah di simpan di database
          $sql = "select * from global.global_shift where shift_id = ".QuoteValue(DPE_CHAR,$shiftId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
              
          $_POST["shift_nama"] = $row_edit["shift_nama"];
          $_POST["shift_jam_awal"] = $row_edit["shift_jam_awal"];
          $_POST["shift_jam_akhir"] = $row_edit["shift_jam_akhir"];
     }
           
	//Ambil Data Konfigurasi Registrasi
	$sql = "select * from global.global_konf_reg a where a.konf_reg_id = ".QuoteValue(DPE_CHAR,"1");
	$rs_edit = $dtaccess->Execute($sql);
	$row_edit = $dtaccess->Fetch($rs_edit);
	
  //JIKA tidak ada Data Konfigurasi Registrasi
  if(!$row_edit) {
  
	  $dbTable = "global.global_konf_reg";
         
         $dbField[0] = "konf_reg_id";   // PK
         $dbField[1] = "konf_reg_sebab_sakit"; 
       	 $dbField[2] = "id_dep";
         $dbField[3] = "konf_reg_sebab_sakit_aktif";
         $dbField[4] = "konf_reg_istri";
         $dbField[5] = "konf_reg_ayah";
         $dbField[6] = "konf_reg_ibu";
         $dbField[7] = "konf_reg_layanan";
         $dbField[8] = "konf_reg_shift";
         $dbField[9] = "konf_reg_cara_kunjungan";
         $dbField[10] = "konf_reg_cara_kunjungan_aktif";
         
         $dbValue[0] = QuoteValue(DPE_CHAR,'1');
         $dbValue[1] = QuoteValue(DPE_CHAR,'9');  
         $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
         $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["konf_reg_sebab_sakit_aktif"]);
         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["konf_reg_istri"]);
         $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["konf_reg_ayah"]);
         $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["konf_reg_ibu"]);
         $dbValue[7] = QuoteValue(DPE_CHAR,'1');
         $dbValue[8] = QuoteValue(DPE_CHAR,'1');
         $dbValue[9] = QuoteValue(DPE_CHAR,'1');
         $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["konf_reg_cara_kunjungan_aktif"]); 
    		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    		$dtmodel->Insert() or die("insert  error");	 
    		
    		unset($dtmodel);
    		unset($dbField);
    		unset($dbValue);
    		unset($dbKey);

  }
 
      //Masukkan Data Konfigurasi Registrasi ke dalam POST
      $dtaccess->Clear($rs_edit);
    	$view->CreatePost($row_edit);
      

     //Ambil Data Departemen
  	 $sql_dep = "select * from global.global_departemen a where a.dep_id = ".QuoteValue(DPE_CHAR,$depId);
  	 $rs_dep = $dtaccess->Execute($sql_dep);
   	 $row_dep = $dtaccess->Fetch($rs_dep);
     $dtaccess->Clear($rs_dep);
     $view->CreatePost($row_dep); 
    
     
     $sql = "select * from global.global_sebab_sakit order by sebab_sakit_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataSebabSakit = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_rujukan order by rujukan_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataKunjungan = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_tipe_biaya order by tipe_biaya_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataLayanan = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_shift order by shift_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataShift = $dtaccess->FetchAll($rs);   
     
     $sql = "select * from global.global_tipe_no_rm order by tipe_no_rm_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataTipeNoRm = $dtaccess->FetchAll($rs);               

     $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql);
     $dataShiftLayanan = $dtaccess->FetchAll($rs);

     $tableHeader = 'Konfigurasi Pelayanan';

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
                <h3>Konfigurasi registrasi utama</h3>
              </div>
            </div>

            <div class="clearfix"></div>
			<?php if($simpan) { ?>
				<font color="red"><strong>Konfigurasi telah disimpan, klik tombol KELUAR pada MENU UTAMA agar perubahan Konfigurasi terjadi.</strong></font>
			<?php } ?> 
            <div class="row"> <!-- ==== BARIS ===== -->
			<!-- ==== kolom kiri ===== -->
			<!-- ==== mulai form ===== -->
			<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-6 col-sm-6 col-xs-12">

			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Alamat Pasien dan No Hp</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Status
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">                          
						        	   <select name="dep_konf_alamat" class="form-control" id="dep_konf_alamat" onKeyDown="return tabOnEnter(this, event);">								
                    			<option value="y" <?php if($_POST["dep_konf_alamat"]=='y') echo "selected"; ?>>Aktif</option>
                    			<option value="n" <?php if($_POST["dep_konf_alamat"]=='n') echo "selected"; ?>>Tidak Aktif</option>
                				</select>	
            						</div>
                      </div>
					 
                  </div>
                </div>
			<!-- ==== //panel putih ===== -->
			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Pemeriksaan</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<iframe src="label_pemeriksaan.php" FRAMEBORDER=0 align=top width="100%" height="130" SCROLLING=AUTO></iframe> 		  
                  </div>
                </div>
			<!-- ==== // panel putih ===== -->
			  
			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Satu Layanan(Pagi Saja / Malam Saja)</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Status
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                    	  <select class="form-control" name="dep_layanan_paten" id="dep_layanan_paten" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_layanan_paten"]=='y') echo "selected"; ?>>Aktif</option>
                          <option value="n" <?php if($_POST["dep_layanan_paten"]=='n') echo "selected"; ?>>Tidak Aktif</option>
                        </select>	
					             	</div>
                      </div>					  
                  </div>
                </div>
                
               
                 <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi No RM</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Panjang Digit No RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
			  		       <?php echo $view->RenderTextBox("dep_panjang_kode_pasien","dep_panjang_kode_pasien","25","45",$_POST["dep_panjang_kode_pasien"],"inputfield", "",false);?>
						</div>
                      </div>					  
                  </div>
                  
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jumlah Digit 0 Depan No RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">			  		     
			  		       <?php echo $view->RenderTextBox("dep_jml_nol_depan","dep_jml_nol_depan","20","20",$_POST["dep_jml_nol_depan"], null,true);?>
			          
			         </div>
                      </div>					                 
                  </div>
                  
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jumlah Digit 0 Depan No RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">			  		     
			  		      <select class="form-control" name="dep_tipe_no_rm" id="dep_tipe_no_rm" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
  				    		<option class="inputField" value="" >Pilih Tipe No RM</option>
              				<?php for($i=0,$n=count($dataTipeNoRm);$i<$n;$i++){ ?>
  			     			 <option class="inputField" value="<?php echo $dataTipeNoRm[$i]["tipe_no_rm_id"];?>" <?php if ($_POST["dep_tipe_no_rm"]==$dataTipeNoRm[$i]["tipe_no_rm_id"]) echo"selected"?>><?php echo $dataTipeNoRm[$i]["tipe_no_rm_nama"]; ?>&nbsp;</option>
              			<?php } ?>
  				  </select>
			         </div>
                      </div>					  
                  </div>
                </div>



<!-- KONFIGURASI RAWAT INAP -->
<div class="x_panel">
  <div class="x_title">
    <h2>Konfigurasi Rawat Inap</h2>
    <span class="pull-right"></span>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">
    <div class="form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">History Rawat Inap</label>
      <div class="col-md-8 col-sm-8 col-xs-12">                
        <select class="form-control" name="dep_view_kelas_asal_rawat_inap" id="dep_view_kelas_asal_rawat_inap" class="inputField">
          <option value="y" <?php if($_POST["dep_view_kelas_asal_rawat_inap"]=='y') echo "selected"; ?>>Aktif</option>
          <option value="n" <?php if($_POST["dep_view_kelas_asal_rawat_inap"]=='n') echo "selected"; ?>>Tidak Aktif</option>
        </select>
      </div>
    </div>            
  </div>
</div>
<!-- END KONFIGURASI RAWAT INAP -->
                
                
			  </div>
			  <!-- ==== // kolom kiri ===== -->
			  
			  <!-- ==== kolom kanan ===== -->
        <div class="col-md-6 col-sm-6 col-xs-12">

                          
          <!-- Awal Shift Pelayanan -->
          <div class="x_panel">
            <div class="x_title">
              <h2>Shift Pelayanan</h2>
              <span class="pull-right"><?php echo $tombolAdd; ?></span>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
             <table width="100%" border="0">
            <!--<tr>
            <?php if(!$_POST["btnAdd"]) { ?>
            <td align="right" colspan="5"><input type="submit" name="btnAdd" id="btnAdd" value="Tambah" class="submit" /></td>
            <?php } ?>
            </tr>-->
            
            <tr class="tablesmallheader">
            <td width="3%" align="center">No</td>
            <td width="10%">Nama Shift</td>
            <td width="5%">Jam Awal</td>
            <td width="5%">Jam Akhir</td>
            <td width="20%">Status</td>                                                                        
            <td width="3%">Edit</td>
            </tr>
                        
            <?php if($_GET["edit"] && $_GET["shift_id"]) { ?>
            <tr>
            <td width="20%" colspan="2"><?php echo $view->RenderTextBox("shift_nama","shift_nama","30","50",$_POST["shift_nama"],"inputField", null,false);?></td>
            <td width="10%"><?php echo $view->RenderTextBox("shift_jam_awal","shift_jam_awal","15","20",$_POST["shift_jam_awal"],"inputField", null,false);?> (H:i:s)</td>
            <td width="10%"><?php echo $view->RenderTextBox("shift_jam_akhir","shift_jam_akhir","15","20",$_POST["shift_jam_akhir"],"inputField", null,false);?> (H:i:s)</td>
            <td width="10%">
              <select name="shift_aktif" id="shift_aktif" onKeyDown="return tabOnEnter(this,event);">
                <option value="y" <?php if($_POST["shift_aktif"]=="y") echo "selected"; ?>>Aktif</option>
                <option value="n" <?php if($_POST["shift_aktif"]=="n") echo "selected"; ?>>Tidak Aktif</option>
              </select>
            </td>
            <td width="3%"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnSimpanShift":"btnSimpanShift","btnSimpanShift","Simpan","submit",false,null);?></td>
            <td width="3%" align="center"><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$viewPage."';\"");?></td>
            </tr>
            <?php echo $view->RenderHidden("shift_id","shift_id",$_GET["shift_id"]);?> 
            <?php } ?>
            
            <?php for($i=0,$n=count($dataShiftLayanan);$i<$n;$i++){ ?>
            <tr>
            <td width="3%" align="center"><?php echo $i+1;?></td>
            <td width="20%"><?php echo $dataShiftLayanan[$i]["shift_nama"];?></td>
            <td width="10%"><?php echo $dataShiftLayanan[$i]["shift_jam_awal"];?></td>
            <td width="10%"><?php echo $dataShiftLayanan[$i]["shift_jam_akhir"];?></td>
            <td width="3%"><?php if($dataShiftLayanan[$i]["shift_aktif"]=="y") { echo "Aktif"; } else echo "Tidak Aktif"; ?></td>
            <td width="3%"><?php echo '<a href="'.$viewPage.'?edit=1&shift_id='.$dataShiftLayanan[$i]["shift_id"].'"> <img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" ></a>';?></td>
            </tr>
            	<?php } ?> 
       			</table>
       			</fieldset>
   			 </td>
  		  </tr> 
 		  </table>                               
        </div>				 						 
     </div>
     <!-- Akhir Shift Pelayanan -->                   
                
                
              <div class="x_panel">
                  <div class="x_title">
                    <h2>Tipe Pelayanan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <iframe src="tipe_layanan.php" FRAMEBORDER=0 align=top width="100%" height="130" SCROLLING=AUTO></iframe>            
						</div>
                      </div>
                  </div>				 						 
                </div>
                
                 <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi No Registrasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No Urut Per Poli
                        </label>                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                       		 <select class="form-control" class="form-control"  name="dep_konf_loket_antrian_poli" id="dep_konf_loket_antrian_poli" onKeyDown="return tabOnEnter(this, event);">								
                          		<option value="y" <?php if($_POST["dep_konf_loket_antrian_poli"]=='y') echo "selected"; ?>>Ya</option>
                          		<option value="n" <?php if($_POST["dep_konf_loket_antrian_poli"]=='n') echo "selected"; ?>>Tidak</option>
                              </select>
                          </div>
                      </div>                                                     
                      
                      
                      <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Antrian Poli
                        </label>                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="form-control" name="dep_antrian_poli" id="dep_antrian_poli" onKeyDown="return tabOnEnter(this, event);">								
              				<option value="y" <?php if($_POST["dep_antrian_poli"]=='y') echo "selected"; ?>>Aktif</option>
             					 <option value="n" <?php if($_POST["dep_antrian_poli"]=='n') echo "selected"; ?>>Tidak Aktif</option>
           				 </select>
                          </div>
                      </div>
                  </div>				 						 
                </div>
                
                    <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi No Registrasi</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    	<table border="0">
                  <tr>
                    <td align="left" class="tablesmallheader" width="45%"><strong>&nbsp;Kode Instalasi</strong>&nbsp;</td>
                    <td align="left" class="tablesmallheader" width="1%">:</td>
                    <td >
                    	<select  name="dep_konf_kode_instalasi" id="dep_konf_kode_instalasi" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_konf_kode_instalasi"]=='y') echo "selected"; ?>>Ya</option>
                          <option value="n" <?php if($_POST["dep_konf_kode_instalasi"]=='n') echo "selected"; ?>>Tidak</option>
                      </select>
                      &nbsp;<input type="button" name="btnIns" value="Master Instalasi" id="btnIns" class='submit' onClick="document.location.href='<?php echo $ROOT;?>instalasi/instalasi_view.php?konf=1'">
                    </td>
                  </tr>
                  <tr>
                    <td align="left" class="tablesmallheader" width="35%"><strong>&nbsp;Kode Sub Instalasi</strong>&nbsp;</td>
                    <td align="left" class="tablesmallheader" width="1%">:</td>
                    <td >
                    	<select name="dep_konf_kode_sub_instalasi" id="dep_konf_kode_sub_instalasi" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_konf_kode_sub_instalasi"]=='y') echo "selected"; ?>>Ya</option>
                          <option value="n" <?php if($_POST["dep_konf_kode_sub_instalasi"]=='n') echo "selected"; ?>>Tidak</option>
                      </select>
                      &nbsp;<input type="button" name="btnIns" value="Master Sub Instalasi" id="btnIns" class='submit' onClick="document.location.href='<?php echo $ROOT;?>sub_instalasi/sub_instalasi_view.php?konf=1'">
                    </td>
                  </tr>
                  <tr>
                    <td align="left" class="tablesmallheader" width="35%"><strong>&nbsp;Kode Poli/Klinik</strong>&nbsp;</td>
                    <td align="left" class="tablesmallheader" width="1%">:</td>
                    <td >
                    	<select name="dep_konf_kode_poli" id="dep_konf_kode_poli" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_konf_kode_poli"]=='y') echo "selected"; ?>>Ya</option>
                          <option value="n" <?php if($_POST["dep_konf_kode_poli"]=='n') echo "selected"; ?>>Tidak</option>
                      </select>
                      &nbsp;<input type="button" name="btnPoli" value="Master Poli/Klinik" id="btnPoli" class='submit' onClick="document.location.href='<?php echo $ROOT;?>setup_poli/jenis_poli_view.php?konf=1'">
                    </td>
                  </tr>
                  <tr>
                    <td align="left" class="tablesmallheader" width="35%"><strong>&nbsp;No Urut Registrasi</strong>&nbsp;</td>
                    <td align="left" class="tablesmallheader" width="1%">:</td>
                    <td >
                    	<select name="dep_konf_urut_registrasi" id="dep_konf_urut_registrasi" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_konf_urut_registrasi"]=='y') echo "selected"; ?>>Ya</option>
                          <option value="n" <?php if($_POST["dep_konf_urut_registrasi"]=='n') echo "selected"; ?>>Tidak</option>
                      </select> 
                    </td>
                  </tr>
                  <tr>
                    <td align="left" class="tablesmallheader" width="35%"><strong>&nbsp;No Urut Pasien</strong>&nbsp;</td>
                    <td align="left" class="tablesmallheader" width="1%">:</td>
                    <td >
                    	<select name="dep_konf_urut_pasien" id="dep_konf_urut_pasien" onKeyDown="return tabOnEnter(this, event);">								
                          <option value="y" <?php if($_POST["dep_konf_urut_pasien"]=='y') echo "selected"; ?>>Ya</option>
                          <option value="n" <?php if($_POST["dep_konf_urut_pasien"]=='n') echo "selected"; ?>>Tidak</option>
                      </select>
                    </td>
                  </tr>
                </table>                      
                  </div>				 						 
                </div>

                <!-- ==== Konf Jam Aturan Pakai ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Jam Aturan Pakai Obat</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Jam Aturan Pakai Obat
                        </label>                        
                        <div class="col-md-6 col-sm-6 col-xs-12">
                           <select class="form-control" class="form-control"  name="dep_konf_jam_aturan_pakai" id="dep_konf_jam_aturan_pakai" onKeyDown="return tabOnEnter(this, event);">                
                              <option value="y" <?php if($_POST["dep_konf_jam_aturan_pakai"]=='y') echo "selected"; ?>>Ya</option>
                              <option value="n" <?php if($_POST["dep_konf_jam_aturan_pakai"]=='n') echo "selected"; ?>>Tidak</option>
                              </select>
                          </div>
                      </div>                                                     
                  </div>                     
                </div>
                <!-- ==== //Konf Jam Aturan Pakai ===== -->

                <!--Konfig Rujukan input tindakan dari instalasi asal-->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Tindakan Rujukan</h2><br>
                    <h5>Input tindakan dari instalasi asal</h5>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tindakan Rujukan</label>                        
                      <div class="col-md-6 col-sm-6 col-xs-12">
                         <select class="form-control" class="form-control"  name="dep_konf_tindakan_rujukan" id="dep_konf_tindakan_rujukan" onKeyDown="return tabOnEnter(this, event);">                
                            <option value="y" <?php if($_POST["dep_konf_tindakan_rujukan"]=='y') echo "selected"; ?>>Ya</option>
                            <option value="n" <?php if($_POST["dep_konf_tindakan_rujukan"]=='n') echo "selected"; ?>>Tidak</option>
                            </select>
                        </div>
                      </div>                                                     
                  </div>                     
                </div>
                <!--Konfig Rujukan input tindakan dari instalasi asal-->
                
      
			  <!-- ==== KHUSUS BUTTON ===== -->
                  <div class="x_content">
					<div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
							<span class="pull-right">
							  <?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnUpdate":"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(this.form);\"");?>      
               				</span>
						</div>
                      </div>
                  </div>
			  <!-- ==== // KHUSUS BUTTON ===== -->
              </div>				
				<?php echo $view->RenderHidden("konf_reg_id","konf_reg_id",$_POST["konf_reg_id"]);?> 
			</form>	<!-- ==== Akhir form ===== -->
			<!-- ==== // kolom kanan ===== -->
            </div> <!-- ==== // BARIS ===== -->
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

