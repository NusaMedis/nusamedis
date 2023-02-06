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

  // echo "<pre>";
  // print_r ($_POST);
  // echo "</pre>";


  if ($_POST["btnSave"] ) {          
    // echo "<pre>";
    // print_r ($_POST);
    // echo "</pre>";
    // die();
    $dbTable = "global.global_departemen";

    $dbField[0] = "dep_id";   // PK
    $dbField[1] = "dep_nama"; 
    $dbField[2] = "dep_aktif"; 
    $dbField[3] = "dep_kop_surat_1"; 
    $dbField[4] = "dep_kop_surat_2"; 
    $dbField[5] = "dep_website";
    $dbField[6] = "dep_height";
    $dbField[7] = "dep_width";              
    $dbField[8] = "dep_logo";
    $dbField[9] = "dep_footer_kwitansi"; 
    $dbField[10] = "dep_kode_prop";            
    $dbField[11] = "dep_prop";
    $dbField[12] = "dep_kota";
    $dbField[13] = "dep_pjb_kasir"; 
    $dbField[14] = "dep_tipe_rs";
    $dbField[15] = "dep_kode_rs";
    $dbField[16] = "dep_alamat_ip_sep";
    $dbField[17] = "dep_alamat_ip_inacbg";
    $dbField[18] = "dep_id_bpjs";
    $dbField[19] = "dep_secret_key_bpjs";
    $dbField[20] = "dep_alamat_ip_inacbg_appv";
    $dbField[21] = "dep_alamat_ip_peserta";
    $dbField[22] = "dep_alamat_ip_rujukan";
    $dbField[23] = "dep_alamat_ip_sep_tgl_pulang";
    $dbField[24] = "dep_dir";
    $dbField[25] = "dep_kode_ppk";
    $dbField[26] = "dep_title";

    $dbValue[0] = QuoteValue(DPE_CHAR,$depId);        
    $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dep_nama"]);  
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["dep_aktif"]); 
    $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["dep_kop_surat_1"]); 
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["dep_kop_surat_2"]); 
    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["dep_website"]);
    $dbValue[6] = QuoteValue(DPE_NUMERIC,$_POST["dep_height"]);
    $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["dep_width"]);
    if($_POST["dep_logo"]) 
    { 
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dep_logo"]); 
    } 
    elseif(!$_POST["dep_logo"]) 
    {
      $dbValue[8] = QuoteValue(DPE_CHAR,'default.jpg'); 
    }     
    $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["dep_footer_kwitansi"]);
    $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["dep_kode_prop"]);      
    $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["dep_prop"]);
    $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["dep_kota"]);               
    $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["dep_pjb_kasir"]);
    $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["dep_tipe_rs"]);
    $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["dep_kode_rs"]);
    $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_sep"]);
    $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_inacbg"]);
    $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["dep_id_bpjs"]);
    $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["dep_secret_key_bpjs"]);
    $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_inacbg_appv"]);
    $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_peserta"]);
    $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_rujukan"]);
    $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["dep_alamat_ip_sep_tgl_pulang"]);
    $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["dep_dir"]);
    $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["dep_kode_ppk"]);
    $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["dep_title"]);

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    $dtmodel->Update() or die("update  error");	 
    $simpan=1;

    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);
  }



  $lokasi = $ROOT."gambar/img_cfg";

  //UNTUK MELOAD DATA 
  $sql = "select * from global.global_departemen a where a.dep_id = ".QuoteValue(DPE_CHAR,$depId);
  $rs_edit = $dtaccess->Execute($sql);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);
  $view->CreatePost($row_edit);

  if($_POST["dep_logo"]!="n") {
    $fotoName = $lokasi."/".$_POST["dep_logo"];
  } elseif($_POST["dep_logo"]=="n") { 
    $fotoName = $lokasi."/default.jpg"; 
  } else { 
    $fotoName = $lokasi."/default.jpg"; 
  } 

  if (!$_POST["dep_logo"])
  {
    $_POST["dep_logo"] = "default.jpg";
    $fotoName = $lokasi."/".$_POST["dep_logo"];
  }

  $tableHeader = "Manajemen - Konfigurasi Klinik";

?>

<script type="text/javascript">

	function ajaxFileUpload()
	{
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
		});
		$.ajaxFileUpload
		(
			{
				url:'konfigurasi_pic.php',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						}else
						{
							alert(data.msg);
						
             document.getElementById('dep_logo').value= data.file;
             document.img_cfg.src='<?php echo $lokasi."/";?>'+data.file;
					}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;
	}

</script>

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
            <?php if($simpan) { ?>
            <font color="red"><strong>Konfigurasi telah disimpan, klik tombol KELUAR pada MENU UTAMA agar perubahan Konfigurasi terjadi.</strong></font>
            <?php } ?>
            <div class="row"> <!-- ==== BARIS ===== -->
            <!-- ==== kolom kiri ===== -->
            <!-- ==== mulai form ===== -->
            <form method="POST" class="form-horizontal form-label-left" action="">
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Rumah Sakit V.<?php echo $_POST["dep_version"];?></h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Sikita</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input readonly type="text" name="dep_id_sikita" id="dep_id_sikita" class="form-control" value="<?php echo $_POST["dep_id"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Direktori</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" class="form-control" name="dep_dir" id="dep_dir" value="<?php echo $_POST["dep_dir"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Title Sistem</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" class="form-control" name="dep_title" id="dep_title" value="<?php echo $_POST["dep_title"];?>">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- baris kiri -->
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Logo</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                      <span class="control-label col-md-3 col-sm-3 col-xs-12">
                        <img hspace="2" <?php if($_POST["dep_height"]!='0') { ?> height="<?php echo $_POST["dep_height"];?>" <?php } else {?>height="80" <?php } ?> name="img_cfg" id="img_cfg" src="<?php echo $fotoName;?>" valign="middle" border="1">
                      </span>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="hidden" name="dep_logo" id="dep_logo" value="<?php echo $_POST["dep_logo"];?>">
                        <input id="fileToUpload" type="file" size="25" name="fileToUpload" class="inputField">
                        Lebar<input type="text" name="dep_width" id="dep_width" maxlength="10" style="top :178px; left :70px; width :50px; height :25px;" value="<?php echo $_POST["dep_width"];?>">
                        Tinggi<input type="text" name="dep_height" id="dep_height" maxlength="10" style="top :178px; left :70px; width :50px; height :25px;" value="<?php echo $_POST["dep_height"];?>">height max 50px
                        <button class="submit" id="buttonUpload" onClick="return ajaxFileUpload('konfigurasi_pic.php','dep_logo','img_cfg');">Upload Logo</button>
                        <span id="loading" style="display:none;"><img width="26" height="16"  id="imgloading" src="<?php echo $ROOT;?>gambar/loading.gif"></span>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- //baris kiri -->
              </div>
              <!-- ==== // kolom kiri ===== -->
              
              <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Konfigurasi Utama</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Header1 <small>( Nama )</small></label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_nama" id="dep_nama" class="form-control" value="<?php echo $_POST["dep_nama"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Header2 <small>( Alamat )</small></label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kop_surat_1" id="dep_kop_surat_1" class="form-control" value="<?php echo $_POST["dep_kop_surat_1"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Header3 <small>( Telp. )</small></label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kop_surat_2" id="dep_kop_surat_2" class="form-control" value="<?php echo $_POST["dep_kop_surat_2"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Website</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_website" id="dep_website" class="form-control" value="<?php echo $_POST["dep_website"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Footer Kwitansi</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_footer_kwitansi" id="dep_footer_kwitansi" class="form-control" value="<?php echo $_POST["dep_footer_kwitansi"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Propinsi</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kode_prop" id="dep_kode_prop" maxlength="255" class="form-control" value="<?php echo $_POST["dep_kode_prop"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Propinsi</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_prop" id="dep_prop" maxlength="255" class="form-control" value="<?php echo $_POST["dep_prop"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Kota</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kota" id="dep_kota" maxlength="255" class="form-control" value="<?php echo $_POST["dep_kota"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Pjb. Penandatangan Kwitansi</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_pjb_kasir" id="dep_pjb_kasir" maxlength="255" class="form-control" value="<?php echo $_POST["dep_pjb_kasir"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tipe Rumah Sakit</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_tipe_rs" id="dep_tipe_rs" maxlength="255" class="form-control" value="<?php echo $_POST["dep_tipe_rs"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode Rumah Sakit</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kode_rs" id="dep_kode_rs" maxlength="255" class="form-control"  value="<?php echo $_POST["dep_kode_rs"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat SEP</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_sep" id="dep_alamat_ip_sep" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_sep"];?>">
                        <small>contoh : http://192.168.0.0:2222/SepLokalRest/sep</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat SEP Update Tgl Pulang</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_sep_tgl_pulang" id="dep_alamat_ip_sep_tgl_pulang" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_sep_tgl_pulang"];?>">
                        <small>contoh : http://192.168.0.0:2222/SepLokalRest/sep/updtglplg</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat Kepesertaan BPJS</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_peserta" id="dep_alamat_ip_peserta" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_peserta"];?>">
                        <small>contoh : http://192.168.0.0:2222/SepLokalRest/peserta</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat Rujukan Peserta</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_rujukan" id="dep_alamat_ip_rujukan" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_rujukan"];?>">
                        <small>contoh : http://192.168.0.0:2222/SepLokalRest/rujukan/peserta</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat INACBGs</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_inacbg" id="dep_alamat_ip_inacbg" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_inacbg"];?>">
                        <small>contoh : http://192.168.0.0:2222</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat INACBGs Approval</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_alamat_ip_inacbg_appv" id="dep_alamat_ip_inacbg_appv" maxlength="255" class="form-control" value="<?php echo $_POST["dep_alamat_ip_inacbg_appv"];?>">
                        <small>contoh : http://192.168.0.0:2222</small>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">ID BPJS</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_id_bpjs" id="dep_id_bpjs" maxlength="255" class="form-control" value="<?php echo $_POST["dep_id_bpjs"];?>">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Secret Key BPJS</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_secret_key_bpjs" id="dep_secret_key_bpjs" maxlength="255" class="form-control" value="<?php echo $_POST["dep_secret_key_bpjs"];?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kode PPK</label>
                      <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="dep_kode_ppk" id="dep_kode_ppk" maxlength="255" class="form-control" value="<?php echo $_POST["dep_kode_ppk"];?>">
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- ==== KHUSUS BUTTON ===== -->
                <div class="x_content">
                  <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                      <input type="submit" name="btnSave" id="btnSave" value="Simpan" class="btn btn-primary" onclick="FunX()">
                    </div>
                  </div>
                </div>
                <!-- ==== // KHUSUS BUTTON ===== -->
              </div>
            </form> <!-- ==== Akhir form ===== -->
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
