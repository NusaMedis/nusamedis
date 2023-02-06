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
     $depNama = $auth->GetDepNama(); 

     $viewPage = "konfigurasi_edit.php";
    
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 

	
    /* if(!$auth->IsAllowed("man_pengaturan_konf_tarif",PRIV_READ)){
         die("access_denied");
          exit(1);
          
    } elseif($auth->IsAllowed("man_pengaturan_konf_tarif",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

     // Untuk update flag jenis pasien
      if ($_GET["ganti_aktif"]) $ganti_aktif = $_GET["ganti_aktif"];
      if ($_GET["ganti_noaktif"]) $ganti_no_naktif = $_GET["ganti_noaktif"];
      if ($_GET["id"]) $id = $_GET["id"];
      if ($ganti_aktif && $id) {
    
           $sql = "update global.global_jenis_pasien set jenis_flag = 'y' where jenis_id = ".QuoteValue(DPE_CHAR,$id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);

           header("location:".$viewPage);
           exit();    
      }
      
       
      if ($ganti_no_naktif && $id) {
    
           $sql = "update global.global_jenis_pasien set jenis_flag = 'n' where jenis_id = ".QuoteValue(DPE_CHAR,$id);
           $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);

           header("location:".$viewPage);
           exit();    
     }
          
     if ($_GET["id"]) {
          if ($_POST["btnDelete"]) { 
               $_x_mode = "Delete";
          } else { 
               $_x_mode = "Edit";
               $JnsPasienId = $_GET["id"];
          }
          // buat nampilin data yg sudah di simpan di database
          $sql = "select * from global.global_jenis_pasien where jenis_id = ".QuoteValue(DPE_CHAR,$JnsPasienId);
          $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $dtaccess->Clear($rs_edit);
          
          $_POST["jenis_nama"] = $row_edit["jenis_nama"];
          $_POST["jenis_id"] = $row_edit["jenis_id"];
     }
     

     
     
    // buat insert atau update data
     if ($_POST["btnSimpanJs"]) 
     {
         $JnsPasienId = & $_POST["jenis_id"];
         $_x_mode = "Edit";     

         $dbTable = "global.global_jenis_pasien";
         
         $dbField[0] = "jenis_id";   // PK
         $dbField[1] = "jenis_nama"; 

         if(!$JnsPasienId) $JnsPasienId = $dtaccess->GetNewID("global.global_jenis_pasien","jenis_id");  
         $dbValue[0] = QuoteValue(DPE_CHAR,$JnsPasienId);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["jenis_nama"]); 

         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
   
         $dtmodel->Update() or die("update  error");	
               
          unset($dtmodel);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
               
          header("location:".$viewPage);
          exit();
     }
     
     

     if ($_POST["btnSave"] ) {
     $depId = & $_POST["dep_id"]; 
          
		  $dbTable = "global.global_departemen";
               
               $dbField[0] = "dep_id";   // PK
               $dbField[1] = "dep_konf_reg";
               $dbField[2] = "dep_konf_kons";  
               $dbField[3] = "dep_kasir_tindakan";               
               $dbField[4] = "dep_konf_cetak_tanda_tangan";
               $dbField[5] = "dep_konf_biaya_akomodasi";
               $dbField[6] = "dep_posting_poli";
               $dbField[7] = "dep_kasir_reg_bayar";
               $dbField[8] = "dep_posting_split";
               $dbField[9] = "dep_konf_bulat_ratusan";
               $dbField[10] = "dep_konf_bulat_ribuan";
               $dbField[11] = "dep_konf_reg_poli";
               $dbField[12] = "dep_posting_beban";
               $dbField[13] = "dep_cetak_rincian";
               $dbField[14] = "dep_konf_reg_kelas_irj";
               $dbField[15] = "dep_konf_reg_kelas_igd";
               $dbField[16] = "dep_konf_reg_kelas_lab";
               $dbField[17] = "dep_konf_reg_kelas_rad";
               $dbField[18] = "dep_konf_reg_banyak";
               $dbField[19] = "dep_konf_reg_ulang";
               $dbField[20] = "dep_konf_tarif_jenis_pasien";
               $dbField[21] = "dep_konf_header_klinik";
			       
               $dbValue[0] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dep_konf_kons"]);
               $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["dep_kasir_tindakan"]);
               $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["dep_konf_cetak_tanda_tangan"]);          
               $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["dep_konf_biaya_akomodasi"]);
               $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["dep_posting_poli"]);
               $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["dep_kasir_reg_bayar"]);
               $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dep_posting_split"]);                                                                   
               $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["dep_konf_bulat_ratusan"]);
               $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["dep_konf_bulat_ribuan"]);
               $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_poli"]);
               $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["dep_posting_beban"]);
               $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["dep_cetak_rincian"]); 
               $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_kelas_irj"]); 
               $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_kelas_igd"]); 
               $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_kelas_lab"]); 
               $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_kelas_rad"]);              
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_banyak"]);  
               $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["dep_konf_reg_ulang"]);               
               $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["dep_konf_tarif_jenis_pasien"]);               
               $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["dep_konf_header_klinik"]);               
              //print_r($dbValue);
              //die();
          		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          		$dtmodel->Update() or die("update  error");	 
          		$simpan=1;
          		
          		unset($dtmodel);
          		unset($dbField);
          		unset($dbValue);
          		unset($dbKey);
            		
   
            		
            		$dbTable = "global.global_sender";
               
               $dbField[0] = "sender_id";   // PK  
               $dbField[1] = "sender_bagian"; 
               $dbField[2] = "id_dep";   
               
               $dbValue[0] = QuoteValue(DPE_CHAR,'2'); 
               $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["sender_umum_bagian"]));
               $dbValue[2] = QuoteValue(DPE_CHAR,$depId);  
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            	 $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
            	 
               $dtmodel->Update() or die("update  error");	 
                          
                unset($dtmodel);
            		unset($dbField);
            		unset($dbValue);
            		unset($dbKey);  

      
     }

           
  //OPEN DATA KONFIGURASI   
	$sql = "select * from global.global_departemen a where a.dep_id = ".QuoteValue(DPE_CHAR,$depId);
	$rs_edit = $dtaccess->Execute($sql);
	$row_edit = $dtaccess->Fetch($rs_edit);
	$dtaccess->Clear($rs_edit);
	$view->CreatePost($row_edit);

  // Combo Jenis Pasien
  $sql = "select a.*,b.konf_fasilitas_pagu, b.konf_fasilitas_pagu_igd, b.konf_fasilitas_pagu_irna from global.global_jenis_pasien a 
          left join global.global_konfigurasi_fasilitas b on a.jenis_id = b.id_jenis 
          order by a.jenis_id asc ";
  $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $dataJnsPasien = $dtaccess->FetchAll($rs);
     
  $sql = "select * from klinik.klinik_tahun_tarif order by tahun_tarif_nama asc";
  $rs = $dtaccess->Execute($sql);
  $dataTahunTarif = $dtaccess->FetchAll($rs);
  
  // Data Kategori Tindakan Header Instalasi//
     $sql = "select * from  klinik.klinik_kelas";
     $sql .= " order by kelas_tingkat asc";
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs);
          
  $tableHeader = "Manajemen - Konfigurasi Tarif";  
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
                <h3>Konfigurasi Tarif</h3>
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
                    <h2>Tarif Klinik Utama</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tarif Registrasi Pasien
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="dep_konf_reg" onKeyDown="return tabOnEnter(this, event);">
							   <option value="y" <?php if($_POST["dep_konf_reg"]=="y")echo "selected";?>>Aktif</option>
							   <option value="n" <?php if($_POST["dep_konf_reg"]=="n")echo "selected";?>>Non Aktif</option>
							</select>	
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Tarif Konsultasi 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
							<select class="form-control" name="dep_konf_kons" onKeyDown="return tabOnEnter(this, event);">
								<option value="y" <?php if($_POST["dep_konf_kons"]=="y")echo "selected";?>>Aktif</option>
								<option value="n" <?php if($_POST["dep_konf_kons"]=="n")echo "selected";?>>Non Aktif</option>
							</select>	
						</div>
                      </div>

            <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Tarif Berdasarkan Jenis Pasien 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
              <select class="form-control" name="dep_konf_tarif_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                <option value="y" <?php if($_POST["dep_konf_tarif_jenis_pasien"]=="y")echo "selected";?>>Aktif</option>
                <option value="n" <?php if($_POST["dep_konf_tarif_jenis_pasien"]=="n")echo "selected";?>>Non Aktif</option>
              </select> 
            </div>
                      </div>
					  
            <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name"> Tarif Header Klinik 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
              <select class="form-control" name="dep_konf_header_klinik" onKeyDown="return tabOnEnter(this, event);">
                <option value="y" <?php if($_POST["dep_konf_header_klinik"]=="y")echo "selected";?>>Aktif</option>
                <option value="n" <?php if($_POST["dep_konf_header_klinik"]=="n")echo "selected";?>>Non Aktif</option>
              </select> 
            </div>
                      </div>
                     
					  
                  </div>
                </div>
			<!-- ==== //panel putih ===== -->
			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Jenis Pasien / Jenis Bayar</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<table width="100%" border="0">
						<tr class="tablesmallheader">
						  <td width="5%">Edit</td>
						  <td width="3%" align="center">No</td>
						  <td width="20%">Nama</td>
						  <td width="10%">Status</td>
						  <td width="15%" align="center">Rubah Status</td>
						</tr>  
						  
						<?php if($_POST["jenis_nama"]) {  
							if($_POST["jenis_id"]){?>
							<tr>
							  <td width="20%" colspan="1"><?php echo $view->RenderHidden("jenis_id","jenis_id","20","60",$_POST["jenis_id"],"inputField", null,false);?></td>
							 
							  <td width="20%" colspan="2"><?php echo $view->RenderTextBox("jenis_nama","jenis_nama","20","60",$_POST["jenis_nama"],"inputField", null,false);?></td>
							  <td width="10%"><?php echo $view->RenderButton(BTN_SUBMIT,($_x_mode == "Edit")?"btnSimpanJs":"btnSimpanJs","btnSimpanJs","Simpan","submit",false,"onClick=\"javascript:return CheckPasien(document.frmEdit);\"");?></td>
							  <td width="20%" align="center"><?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='".$viewPage."';\"");?></td>
							</tr>
							  <?php } 
							  }?>
						  
						  <?php for($i=0,$n=count($dataJnsPasien);$i<$n;$i++){ ?>
						  <tr>
						  <td width="5%"><?php echo '<a href="'.$viewPage.'?id='.$dataJnsPasien[$i]["jenis_id"].'"> <img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" ></a>';?></td>
						  <td width="3%" align="center"><?php echo $i+1;?></td>
						  <td width="20%"><?php echo $dataJnsPasien[$i]["jenis_nama"];?></td>
						  <?php if($dataJnsPasien[$i]["jenis_flag"]=="y") { ?>
						  <td width="15%">Aktif</td>
						  <?php } else { ?>
						  <td width="15%">Non Aktif</td>
						  <?php } ?>
						  
						  <?php if($dataJnsPasien[$i]["jenis_flag"]=="n") { ?>
						  <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?ganti_aktif=1&id='.$dataJnsPasien[$i]["jenis_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/non_aktif.png" alt="Non Aktif" title="Non Aktif" border="0"></a>';?></td>
						  <?php } else { ?>
						  <td width="20%" align="center"><?php echo '<a href="'.$viewPage.'?ganti_noaktif=1&id='.$dataJnsPasien[$i]["jenis_id"].'"><img hspace="2" width="18" height="18" src="'.$ROOT.'gambar/aktif.png" alt="Aktif" title="Aktif" border="0"></a>';?></td>
						  <?php } ?>
						  </tr>
						  <?php } ?>
					</table>
					  
                  </div>
                </div>
			<!-- ==== // panel putih ===== -->
			  
			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Pembulatan Otomatis</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Pembulatan Ribuan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                    	  <select class="form-control" name="dep_konf_bulat_ribuan" id="dep_konf_bulat_ribuan" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_konf_bulat_ribuan"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_konf_bulat_ribuan"]=='n') echo "selected"; ?>>Non AKtif</option>
						  </select>		
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Pembulatan Ratusan
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="dep_konf_bulat_ratusan" id="dep_konf_bulat_ratusan" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_konf_bulat_ratusan"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_konf_bulat_ratusan"]=='n') echo "selected"; ?>>Non AKtif</option>
                          </select>		
						</div>
                      </div> 
                  </div>
                </div>
			<!-- ==== // panel putih ===== -->
			
			<!-- ==== panel putih ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Keterangan:</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="col-md-12 col-sm-12 col-xs-12">			
						1. Konfigurasi Posting Poli digunakan utk memposting jurnal pendapatan per poli.<br>
						2. Konfigurasi Posting Beban digunakan utk memposting beban pendapatan secara otomatis.<br>
						3. Konfigurasi Biaya Registrasi per Poli diaktifkan jika tarif biaya registrasi utk masing-masing poli berbeda-beda.<br>
						4. Konfigurasi Posting Split digunakan utk memposting split tindakan ke jurnal secara otomatis<br>
						5. Konfigurasi Kasir Memasukkan Tindakan diaktifkan jika kasir diperbolehkan menambah tindakan pasien yang kurang.<br>
                        6. Konfigurasi Kasir Penandatangan Kwitansi berguna untuk tampilan nama yang muncul pada Kwitansi Pembayaran.<br>
						7. Konfigurasi Kasir Cetak Kwitansi Rincian diaktifkan jika kwitansi yang dicetak rincian bukan global.<br>
                        8. Konfigurasi Pembayaran Kasir Di Depan diaktifkan jika rumah sakit mengharuskan pasien membayar terlebih dulu sebelum ditindak.<br>
					</div>  
                  </div>
                </div>
			<!-- ==== // panel putih ===== -->
			  </div>
			  <!-- ==== // kolom kiri ===== -->
			  
              	
              
			  <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">
              
              <!-- ==== Setting Tarif ===== -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setting Tarif</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  
                      <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-12" for="first-name">Tarif Rawat Jalan
                        </label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
                          <select name="dep_konf_reg_kelas_irj" class="select2_single form-control"  onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    <option class="inputField" value="" >- Pilih Kelas -</option>
				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["dep_konf_reg_kelas_irj"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
				   			<?php } ?>
				  		   </select>	
						</div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-12" for="first-name">Tarif IGD 
                        </label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<select name="dep_konf_reg_kelas_igd" class="select2_single form-control"  onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    						    <option class="inputField" value="" >- Pilih Kelas -</option>
    				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
    				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["dep_konf_reg_kelas_igd"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
    				   			<?php } ?>
				  		   </select>	
						</div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-12" for="first-name">Tarif Laboratorium 
                        </label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<select name="dep_konf_reg_kelas_lab" class="select2_single form-control"  onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    						    <option class="inputField" value="" >- Pilih Kelas -</option>
    				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
    				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["dep_konf_reg_kelas_lab"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
    				   			<?php } ?>
				  		   </select>	
						</div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-12" for="first-name">Tarif Radiologi 
                        </label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<select name="dep_konf_reg_kelas_rad" class="select2_single form-control"  onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    						    <option class="inputField" value="" >- Pilih Kelas -</option>
    				     		<?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
    				    		<option class="inputField" value="<?php echo $dataKelas[$i]["kelas_id"];?>"<?php if ($_POST["dep_konf_reg_kelas_lab"]==$dataKelas[$i]["kelas_id"]) echo"selected"?>><?php echo $dataKelas[$i]["kelas_nama"];?>&nbsp;</option>
    				   			<?php } ?>
				  		   </select>	
						</div>
                      </div>
					  
                     
					  
                  </div>
                </div>
			<!-- ==== //setting tarif ===== -->
            
              
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi (Status)</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Posting Poli
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="dep_posting_poli" id="dep_posting_poli" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_posting_poli"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_posting_poli"]=='n') echo "selected"; ?>>Tidak Aktif</option>
						  </select>
						</div>
                      </div>
                  </div>
				  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Posting Beban
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="dep_posting_beban" id="dep_posting_beban" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_posting_beban"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_posting_beban"]=='n') echo "selected"; ?>>Tidak Aktif</option>
						  </select>
						</div>
                      </div>
                  </div>
				  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Biaya Registrasi Per Poli
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="dep_konf_reg_poli" id="dep_konf_reg_poli" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_konf_reg_poli"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_konf_reg_poli"]=='n') echo "selected"; ?>>Tidak Aktif</option>
						  </select>
						</div>
                      </div>
               
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Posting Split
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="dep_posting_split" id="dep_posting_split" onKeyDown="return tabOnEnter(this, event);">								
							<option value="y" <?php if($_POST["dep_posting_split"]=='y') echo "selected"; ?>>Aktif</option>
							<option value="n" <?php if($_POST["dep_posting_split"]=='n') echo "selected"; ?>>Tidak Aktif</option>
                          </select>
						</div>
                      </div>
                  </div>
				  <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Kasir Memasukkan Tindakan
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="dep_kasir_tindakan" id="dep_kasir_tindakan" onKeyDown="return tabOnEnter(this, event);">								
                            <option value="y" <?php if($_POST["dep_kasir_tindakan"]=='y') echo "selected"; ?>>Aktif</option>
                            <option value="n" <?php if($_POST["dep_kasir_tindakan"]=='n') echo "selected"; ?>>Tidak Aktif</option>
                          </select>
						</div>
                      </div>
           
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Kasir Penandatangan Kwitansi 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select class="form-control" name="dep_konf_cetak_tanda_tangan" id="dep_konf_cetak_tanda_tangan" onKeyDown="return tabOnEnter(this, event);">								
							  <option value="y" <?php if($_POST["dep_konf_cetak_tanda_tangan"]=='y') echo "selected"; ?>>Nama Kasir</option>
							  <option value="n" <?php if($_POST["dep_konf_cetak_tanda_tangan"]=='n') echo "selected"; ?>>Nama Pejabat</option>
							</select>
						</div>
                      </div>
         
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Bentuk Kwitansi Kasir 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select class="form-control" name="dep_cetak_rincian" id="dep_cetak_rincian" onKeyDown="return tabOnEnter(this, event);">								
							  <option value="y" <?php if($_POST["dep_cetak_rincian"]=='y') echo "selected"; ?>>Kwitansi Rinician 1/2 A5</option>
                              <option value="a" <?php if($_POST["dep_cetak_rincian"]=='a') echo "selected"; ?>>Kwitansi Rinician A5</option>
							  <option value="n" <?php if($_POST["dep_cetak_rincian"]=='n') echo "selected"; ?>>Kwitansi Global tanpa Label</option>
                              <option value="l" <?php if($_POST["dep_cetak_rincian"]=='l') echo "selected"; ?>>Kwitansi Global dengan Label</option>
							</select>
						</div>
                      </div>
                 
                      <div class="form-group">
                        <label class="control-label col-md-5 col-sm-5 col-xs-12" for="first-name">Konfigurasi Pembayaran Kasir didepan  
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
							<select class="form-control" name="dep_kasir_reg_bayar" id="dep_kasir_reg_bayar" onKeyDown="return tabOnEnter(this, event);">								
							  <option value="y" <?php if($_POST["dep_kasir_reg_bayar"]=='y') echo "selected"; ?>>Ya</option>
							  <option value="n" <?php if($_POST["dep_kasir_reg_bayar"]=='n') echo "selected"; ?>>Tidak</option>
							</select>
						</div>
                      </div>
                  </div>
                </div>
<!-- ==== panel putih ===== -->
<div class="x_panel">
  <div class="x_title">
    <h2>Registrasi</h2>
    <span class="pull-right"></span>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">
    <div class="form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Banyak Registrasi Sehari</label>
      <div class="col-md-8 col-sm-8 col-xs-12">
        <select class="form-control" name="dep_konf_reg_banyak" onKeyDown="return tabOnEnter(this, event);">
          <option value="y" <?php if($_POST["dep_konf_reg_banyak"]=="y")echo "selected";?>>Aktif</option>
          <option value="n" <?php if($_POST["dep_konf_reg_banyak"]=="n")echo "selected";?>>Non Aktif</option>
        </select> 
      </div>
    </div>
    <div class="form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Ulang Registrasi Sehari</label>
      <div class="col-md-8 col-sm-8 col-xs-12">
        <select class="form-control" name="dep_konf_reg_ulang" onKeyDown="return tabOnEnter(this, event);">
          <option value="y" <?php if($_POST["dep_konf_reg_ulang"]=="y")echo "selected";?>>Aktif</option>
          <option value="n" <?php if($_POST["dep_konf_reg_ulang"]=="n")echo "selected";?>>Non Aktif</option>
        </select> 
      </div>
    </div>
  </div>
</div>
<!-- ==== //panel putih ===== -->
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
				<?php echo $view->RenderHidden("dep_id","dep_id",$depId);?> 
				<?php echo $view->RenderHidden("dep_aktif","dep_aktif",$_POST["dep_aktif"]);?>
				<?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
				<?php echo $view->RenderHidden("jenis_id","jenis_id",$_POST["jenis_id"]);?>
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