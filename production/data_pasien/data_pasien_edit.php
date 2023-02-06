<?php    
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."bit.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."currency.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."expAJAX.php");
  require_once($LIB."tampilan.php");	

  //INISIALISASI LIBRARY
  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $depNama = $auth->GetDepNama(); 
  $userName = $auth->GetUserName();
  $enc = new textEncrypt();     
  $depId = $auth->GetDepId();
  $plx = new expAJAX("CheckDataKamar");
  $lokasi = $ROOT."gambar/foto_pasien";
     
  //AUTHENTIKASI
  if (!$auth->IsAllowed("man_ganti_password",PRIV_READ)) {
    die("access_denied");
    exit(1);
      
  } 

  if ($auth->IsAllowed("man_ganti_password",PRIV_READ)===1) {
    echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
    exit(1);
  }

  /* SQL KONFIGURASI */
  $sql = "select dep_konf_reg_no_rm_depan, dep_konf_reg_banyak, dep_konf_reg_ulang, dep_kode_prop from global.global_departemen";
  $konf = $dtaccess->Fetch($sql);
  /* SQL KONFIGURASI */
	  
  //INISIALISASI AWAL
  $err_code = 0;
  if($_GET["id_kelas"]) $_POST["id_kelas"] = $_GET["id_kelas"];		    
  if($_POST["cust_usr_id"])  $custUsrId = & $_POST["cust_usr_id"];    
  if(!$_POST["cust_usr_asal_negara"])$_POST["cust_usr_asal_negara"] ='1';
    
	if ($_POST['ref'] == 'irj') { $backPage = "../registrasi_irj/registrasi_irj_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'irj_bpjs') { $backPage = "../registrasi_irj_bpjs/registrasi_bpjs_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'igd') { $backPage = "../registrasi_igd/registrasi_igd_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'igd_bpjs') { $backPage = "../registrasi_igd_bpjs/registrasi_bpjs_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'irna') { $backPage = "../registrasi_irna/registrasi_irna_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'radiologi') { $backPage = "../registrasi_radiologi/registrasi_radiologi_view.php?usr_id=";}
	elseif ($_POST['ref'] == 'radiologi_luar') { $backPage = "../radiologi_luar/registrasi_radiologi_view.php?usr_id=";}
	else { $backPage = "data_pasien_view.php?usr_id="; }
     
  //JIKA ADA GET id untuk View Data
  if($_GET["id"]) {
    $_x_mode = "Edit";
    $custUsrId = $enc->Decode($_GET["id"]);
  }
     
  //PENGATURAN MODE ADD atau UPDATE atau DELETE
  if($_POST["x_mode"]) { //JIKA ADA POST MODE 
    $_x_mode = & $_POST["x_mode"];
    if ($_POST["btnDelete"]) $_x_mode = "Delete";
    else $_x_mode = "New"; 
  }
     
  if($_POST["btnUpdate"]) {
    $custUsrId = & $_POST["cust_usr_id"];             
    $_x_mode = "Edit";
  }
     
  if($_x_mode=="New") $privMode = PRIV_CREATE;
  elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
  else $privMode = PRIV_DELETE;    
     
  //INISIALISASI /
  function CheckDataKamar($kamarKode,$custUsrId=null) {
    global $dtaccess;

    /* SQL KAMAR */
    $sql = "SELECT a.cust_usr_id FROM klinik.klinik_kamar a WHERE upper(a.cust_usr_nama) = ".QuoteValue(DPE_CHAR,strtoupper($kamarKode));
    if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_NUMERIC,$custUsrId);
    $dataAdaKamar = $dtaccess->Fetch($rs);
    /* SQL KAMAR */
    
    return $dataAdaKamar["cust_usr_id"];
  }


  if ($_GET["del"]) {
    $custUsrId = $enc->Decode($_GET["id"]);

    /* DELETE DATA PASIEN */
    $sql = "delete from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR,$custUsrId);
    $dtaccess->Execute($sql);
    /* DELETE DATA PASIEN */
     
    header("location:".$backPage);
    exit();    
  }

  //DATA VIEW UNTUK EDIT
  if ($_GET["id"]) {
    /* SQL DATA PASEIN */
    $sql = "select a.* from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$custUsrId);
    $row_edit = $dtaccess->Fetch($sql);
    /* SQL DATA PASEIN */

    $view->CreatePost($row_edit);
		  
    $umurtahun = (strtotime(date("Y-m-d")) - strtotime(date_db($_POST["cust_usr_tanggal_lahir"])))/86400/365;
    $umurbulan = ($umurtahun - floor($umurtahun)) * 12;
    $umurhari = ($umurbulan - floor($umurbulan)) * 31; 

    $_POST["tahun"]=floor($umurtahun);
  	$_POST["bulan"]=floor($umurbulan);
  	$_POST["hari"]=floor($umurhari);
	}

  $custUsrId = ($_GET["id"]) ? $enc->Decode($_GET["id"]) : $dtaccess->GetTransID();

  if(!$_GET['id']) {require_once("data_pasien_kode.php");}
  if (!$_POST["cust_usr_kode"]) $_POST["cust_usr_kode"] = $_POST["kode_pasien"];
  
  $arr = str_split($_POST["cust_usr_kode"],"2");
  $usr_kode_tampilan = implode(".",$arr);
  $_POST["cust_usr_kode_tampilan"] = $usr_kode_tampilan;
  $custUsrId = ($_GET["id"]) ? $enc->Decode($_GET["id"]) : $dtaccess->GetTransID();
  if ($_POST["btnSave"] || $_POST["btnUpdate"]) {   
    if ($_POST['btnSave']) echo 'baba';
    if ($_POST['btnUpdate']) echo 'babaz';
    /* SQL LOKASI */                            
	  $sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["kel"]."'";
    $lokasidaerah = $dtaccess->Fetch($sql);
    /* SQL LOKASI */                            
         
    /* INSERT DATA PASIEN */
    $dbTable = "global.global_customer_user";  

    $dbField[0] = "cust_usr_id";   // PK         
    $dbField[1] = "cust_usr_nama";
    $dbField[2] = "cust_usr_tempat_lahir";
    $dbField[3] = "cust_usr_tanggal_lahir";
    $dbField[4] = "cust_usr_umur";
    $dbField[5] = "cust_usr_alamat";
    $dbField[6] = "cust_usr_dusun";
    $dbField[7] = "cust_usr_no_hp";
    $dbField[8] = "id_dep";
    $dbField[9] = "cust_usr_jenis_kelamin"; 
    $dbField[10] = "cust_usr_agama"; 
    $dbField[11] = "cust_usr_no_identitas"; 
    $dbField[12] = "id_card"; 
    $dbField[13] = "id_pendidikan"; 
    $dbField[14] = "id_pekerjaan"; 
    $dbField[15] = "cust_usr_asal_negara"; 
    $dbField[16] = "id_status_perkawinan"; 
    $dbField[17] = "id_kecamatan";
    $dbField[18] = "id_kelurahan";
    $dbField[19] = "id_prop";
    $dbField[20] = "id_kota";
    $dbField[21] = "id_lokasi";
    $dbField[22] = "cust_usr_foto";
    $dbField[23] = "cust_usr_kode";
    $dbField[24] = "cust_usr_kode_tampilan";
    $dbField[25] = "cust_usr_penanggung_jawab";
    $dbField[26] = "cust_usr_penanggung_jawab_status";
    $dbField[27] = "cust_usr_gol_darah";
    $dbField[28] = "cust_usr_gol_darah_resus";
    $dbField[29] = "cust_usr_alergi";
		 
    // if(!$custUsrId) $custUsrId = $dtaccess->GetTransID();

    $dbValue[0] = QuoteValue(DPE_CHAR,$_POST['cust_usr_id2']);         
    $dbValue[1] = QuoteValue(DPE_CHAR,STRTOUPPER($_POST["cust_usr_nama"]));
    $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
    $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["tahun"]."~".$_POST["bulan"]."~".$_POST["hari"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["cust_usr_dusun"]);
    $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_hp"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis_kelamin"]);
    $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["cust_usr_agama"]);
    $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["cust_usr_no_identitas"]);
    $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["id_card"]);
    $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_pendidikan"]);
    $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_pekerjaan"]);
    $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["cust_usr_asal_negara"]);
    $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["id_status_perkawinan"]);
    $dbValue[17] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kecamatan"]);
    $dbValue[18] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kelurahan"]);
    $dbValue[19] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_propinsi"]);
    $dbValue[20] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_kabupatenkota"]);          
    $dbValue[21] = QuoteValue(DPE_CHAR,$lokasidaerah["lokasi_id"]);
    $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
    $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
    $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode_tampilan"]);
    $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab"]);
    $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_status"]);
    $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["cust_usr_gol_darah"]);
    $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["cust_usr_gol_darah_resus"]);
    $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cust_usr_alergi"]);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		if ($_POST["btnSave"]) $dtmodel->Insert() or die("insert  error");	
    else if ($_POST["btnUpdate"]) $dtmodel->Update() or die("update  error");
         
    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);
    /* INSERT DATA PASIEN */
    
    header("location:".$backPage.$_POST["cust_usr_kode"]);
    exit();        
  }

  /* SQL AGAMA */ 
  $sql = "select * from global.global_agama order by agm_id";
  $dataAgama = $dtaccess->FetchAll($sql); 
  /* SQL AGAMA */ 
	 
  /* SQL PENDIDIKAN */
	$sql = "select * from global.global_pendidikan order by pendidikan_urut";
  $dataPendidikan = $dtaccess->FetchAll($sql);
  /* SQL PENDIDIKAN */
  
  /* SQL PEKERJAAN */       
  $sql = "select * from global.global_pekerjaan order by pekerjaan_nama";
  $dataPekerjaan = $dtaccess->FetchAll($sql);
  /* SQL PEKERJAAN */       
	
  /* SQL LOKASI */
	$sql = "select * from  global.global_lokasi where lokasi_kabupatenkota='00' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_id";
  $dataProvinsi = $dtaccess->FetchAll($sql); 
  /* SQL LOKASI */

  /* SQL NEGARA */
  $sql = "select * from global.global_negara order by negara_nama asc";
  $dataNegara = $dtaccess->FetchAll($sql);    
  /* SQL NEGARA */
	
  /* SQL STATUS PERKAWINAN */ 
	$sql = "select * from global.global_status_perkawinan order by status_perkawinan_nama";
  $dataStatus = $dtaccess->FetchAll($sql);
  /* SQL STATUS PERKAWINAN */ 
	
  /* SQL PENANGGUNG JAWAB */ 
	$sql = "select * from global.global_status_pj order by status_pj_nama";
  $dataStatusPJ = $dtaccess->FetchAll($sql);
  /* SQL PENANGGUNG JAWAB */ 
	
  /* SQL INSTALASI */ 
	$sql = "select instalasi_id, instalasi_nama from global.global_auth_instalasi";
  $dataInstalasi = $dtaccess->FetchAll($sql);
  /* SQL INSTALASI */ 
	
  /* SQL SEBAB SAKIT */
	$sql = "select * from global.global_sebab_sakit";
  $dataSebabSakit = $dtaccess->FetchAll($sql);
  /* SQL SEBAB SAKIT */

  /* SQL RUJUKAN */
	$sql = "select * from global.global_rujukan";
  $dataCaraKunjungan = $dtaccess->FetchAll($sql);
  /* SQL RUJUKAN */
	
  /* SQL JENIS PASIEN */ 
	$sql = "select * from  global.global_jenis_pasien a where jenis_id<>".PASIEN_BAYAR_BPJS." and jenis_flag='y'";
  $dataJPasien = $dtaccess->FetchAll($sql);
  /* SQL JENIS PASIEN */ 
	 
  /* SQL DOKTER */
	$sql = "select * from global.global_auth_user a left join global.global_auth_role b on a.id_rol = b.rol_id where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='A') and a.id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
  $dataDokter = $dtaccess->FetchAll($sql);
  $dataPelaksana = $dtaccess->FetchAll($sql);
  /* SQL DOKTER */
	 
  /* SQL PROSEDUR MASUK */
	$sql = "select * from global.global_prosedur_masuk";    
  $dataProsedurMasuk = $dtaccess->FetchAll($sql);
  /* SQL PROSEDUR MASUK */
	 
  $lokasi = $ROOT."gambar/foto_pasien";
  $lokTakeFoto = $ROOT."gambar/foto_pasien"; 

  $tableHeader = "Data Pasien";
?>

<!DOCTYPE html>
<html lang="en">  
	<?php require_once($LAY."header.php") ?>
	<link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
	<script src="assets/fancybox/jquery.easing-1.3.pack.js"></script>
	<script src="assets/webcam/webcam.js"></script>	
  <script type="text/javascript">
    $(document).ready(function(){
      ajaxkota(<?=$konf['dep_kode_prop']?>);
      
      $('#instalasi').on('change',function(){
        var instalasi_id = $(this).val();
        if(instalasi_id) {
          $.ajax({
            type:'POST',
            url:'RS_Data.php',
            data:'instalasi_id='+instalasi_id,
            success:function(html){
              $('#sub_instalasi').html(html);
              $('#klinik').html('<option value="">Pilih Instalasi Dahulu</option>'); 
            }
          }); 
        } else {
          $('#sub_instalasi').html('<option value="">Pilih Instalasi Dahulu</option>');
          $('#klinik').html('<option value="">Pilih Sub Instalasi Dahulu</option>'); 
        }
      });
    
      $('#sub_instalasi').on('change',function(){
        var sub_instalasi_id = $(this).val();
        if (sub_instalasi_id) {
          $.ajax({
            type:'POST',
            url:'RS_Data.php',
            data:'sub_instalasi_id='+sub_instalasi_id,
            success:function(html){
              $('#klinik').html(html);
            }
          }); 
        } else {
          $('#klinik').html('<option value="">Pilih Sub Instalasi Dahulu</option>'); 
        }
      });
	
    	//fix load cmb propinsi, kota dst
    	var a = $('#cust_usr_id').val();
    	setTimeout(function() {
		    $.post( "get_pasien.php", { usr_id: a },
				function( data ) {
					$('#cust_usr_gol_darah').val(data.cust_usr_gol_darah);
					$('#cust_usr_gol_darah_resus').val(data.cust_usr_gol_darah_resus);
					if (data.id_prop != null) { ajaxkota(data.id_prop); };
					var delay = 1000;
					var delay2 = 1500;
					var delay3 = 2000;
					setTimeout(function() {
						$('#kota').val(data.id_kota+"&prop="+data.id_prop);
						ajaxkec(data.id_kota+"&prop="+data.id_prop);
					}, delay);
					setTimeout(function() {
						$('#kec').val(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
						ajaxkel(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
					}, delay2);
					setTimeout(function() {
						$('#kel').val(data.id_prop+"."+data.id_kota+"."+data.id_kecamatan+"."+data.id_kelurahan);
					}, delay3);
				},"json");            
	    }, 300);
    });
  </script>
  
  <script type="text/javascript">
    $(document).ready(function(){
    	var camera = $('#camera'), photos = $('#photos'), screen =  $('#screen');
	    var template = '<a href="<?php echo $ROOT;?>gambar/foto_pasien/{src}" rel="cam" '+'style="background-image:url(<?php echo $ROOT;?>gambar/thumbs/{src})"></a>';

    	/*----------------------------------
    		Setting up the web camera
    	----------------------------------*/
      webcam.set_swf_url('assets/webcam/webcam.swf');
    	webcam.set_api_url('upload_pasien.php');	// The upload script
    	webcam.set_quality(80);				// JPEG Photo Quality
    	webcam.set_shutter_sound(true, 'assets/webcam/shutter.mp3');

    	// Generating the embed code and adding it to the page:	
    	screen.html(
    	webcam.get_html(screen.width(), screen.height())
    	);

    	/*----------------------------------
    		Binding event listeners
    	----------------------------------*/
    	var shootEnabled = false;		
    	$('#shootButton').click(function(){
    		
    		if(!shootEnabled){
    			return false;
    		}
    		webcam.freeze();
    		togglePane();
    		return false;
    	});
    	
    	$('#cancelButton').click(function(){
    		webcam.reset();
    		togglePane();
    		return false;
    	});
       
    	$('#uploadButton').click(function(){
     
    		webcam.upload();
    		webcam.reset();
    		togglePane();  
    		return false;
    	});

    	camera.find('.settings').click(function(){
    		if(!shootEnabled){
    			return false;
    		}
    		
    		webcam.configure('camera');
    	});

	    $('.camTop').click(function(){
        camera.animate({
  			  bottom:-350
  			});
  	  });

  	  var showns = false;
	    $('.camTops').click(function(){
		    if(showns){
			    camera.animate({
				    bottom:-350
			    });
		    } else {
			    camera.animate({
  				  bottom:20
  			  },{easing:'easeOutExpo',duration:'slow'});
  		  }
  		
  		  showns = !showns;
  	  });

    	/*---------------------- 
    		     Callbacks
	    ----------------------*/

	    webcam.set_hook('onLoad',function(){
		    shootEnabled = true;
	    });
	
    	webcam.set_hook('onComplete', function(msg){
    		msg1 = $.parseJSON(msg);
    
    		if(msg.error){
          alert(msg1.message);
		    } else {  
          document.getElementById('cust_usr_foto').value=msg1.filename; 
          document.original.src='<?php echo $lokTakeFoto."/";?>'+msg1.filename;  
          alert('Foto Pasien telah tersimpan');
    			photos.prepend(templateReplace(template,{src:msg1.filename}));
    			initFancyBox();
		    }
	    });
	
  	  webcam.set_hook('onError',function(e){
    		screen.html(e);
    	});
  	
    
    	// This function toggles the two
    	// .buttonPane divs into visibility:
    	function togglePane(){
    		var visible = $('#camera .buttonPane:visible:first');
    		var hidden = $('#camera .buttonPane:hidden:first');
    		
    		visible.fadeOut('fast',function(){
    			hidden.show();
    		});
    	}
  	
    	// Helper function for replacing "{KEYWORD}" with
    	// the respectful values of an object:
    	function templateReplace(template,data){
    		return template.replace(/{([^}]+)}/g,function(match,group){
    			return data[group.toLowerCase()];
    		});
    	}
    });
  </script> 
  
  <script>
    function Umur(umur) {
      tgllahir = document.getElementById("cust_usr_tanggal_lahir").value;
      tanggal = tgllahir.split("-");
      t = tanggal[0];
      bln = (tanggal[1] - 1);
      thn = tanggal[2];
   
      var d = new Date();
      d.setDate(t);
      d.setMonth(bln);
      d.setFullYear(thn);
      x1 = d.getTime();
      var d2 = new Date();
      x2 = d2.getTime();
      beda = x2-x1;
      var umurtahun = beda/(1000*60*60*24*365);
      var umurbulan = (umurtahun - Math.floor(umurtahun)) * 12;
      var umurhari = (umurbulan - Math.floor(umurbulan)) * 31;
      
      document.getElementById("tahun").value = Math.floor(umurtahun);
      document.getElementById("bulan").value = Math.floor(umurbulan);
      document.getElementById("hari").value = Math.floor(umurhari);
    }

    function TanggalLahir(tanggal) {
      umur = document.getElementById("tahun").value;

      var e = new Date();
      
      skr = e.getFullYear();

      thn = skr-umur;
      var tahunlahir = thn;
      
      document.getElementById("cust_usr_tanggal_lahir").value = "01-01" + Math.floor(tahunlahir);
      document.getElementById("bulan").value = "0";
      document.getElementById("hari").value = "0";
                  
    }

    var ajaxku;
    function ajaxkota(id){
      ajaxku = buatajax();
      var url="select_kota.php";
      url=url+"?q="+id;
      url=url+"&sid="+Math.random();
      ajaxku.onreadystatechange=stateChanged;
      ajaxku.open("GET",url,true);
      ajaxku.send(null);
    }

    function ajaxkec(id){
      ajaxku = buatajax();
      var url="select_kota.php";
      url=url+"?kec="+id;
      url=url+"&sid="+Math.random();
      ajaxku.onreadystatechange=stateChangedKec;
      ajaxku.open("GET",url,true);
      ajaxku.send(null);
    }

    function ajaxkel(id){
      ajaxku = buatajax();
      var url="select_kota.php";
      url=url+"?kel="+id;
      url=url+"&sid="+Math.random();
      ajaxku.onreadystatechange=stateChangedKel;
      ajaxku.open("GET",url,true);
      ajaxku.send(null);
    }

    function buatajax(){
      if (window.XMLHttpRequest){
        return new XMLHttpRequest();
      }
      if (window.ActiveXObject){
        return new ActiveXObject("Microsoft.XMLHTTP");
      }
      return null;
    }

    function stateChanged(){
      var data;
      
      if (ajaxku.readyState==4){
        data=ajaxku.responseText;
        
        if (data.length>=0) {
          document.getElementById("kota").innerHTML = data
        } else {
          document.getElementById("kota").value = "<option selected>Pilih Kota/Kab</option>";
        }
      }
    }

    function stateChangedKec(){
      var data;
      if (ajaxku.readyState==4){
        data=ajaxku.responseText;
        
        if (data.length>=0) {
          document.getElementById("kec").innerHTML = data
        } else {
          document.getElementById("kec").value = "<option selected>Pilih Kecamatan</option>";
        }
      }
    }

    function stateChangedKel(){
      var data;
      if (ajaxku.readyState==4){
        data=ajaxku.responseText;

        if (data.length>=0) {
          document.getElementById("kel").innerHTML = data
        } else {
          document.getElementById("kel").value = "<option selected>Pilih Kelurahan/Desa</option>";
        }
      }
    }
  </script> 

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
		    <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
        <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
      		<input type="hidden" value="<?php echo $_GET['ref']; ?>" name="ref"> 
          <div class="right_col" role="main">
            <div class="">
              <div class="page-title">
                <div class="title_left">
                  <h3>&nbsp;</h3>
                </div>
                <div class="title_right"></div>
              </div>
              <div class="clearfix"></div>
              <!-- Row 1 Input Data Pasien -->
              <div class="row">
                <!-- Kolom 1 Input Data Pasien -->
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Data Pasien</h2>
                      <ul class="nav navbar-right panel_toolbox"></ul>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <br />
			                <div class="item form-group">
                        <!-- Inputan NO RM -->
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">No RM <span class="required">*</span></label>
                        <div class="col-md-3 col-sm-3 col-xs-3">
                          <input id="cust_usr_kode" name="cust_usr_kode" readonly="readonly" value="<?php echo $_POST["cust_usr_kode"];?>" class="form-control col-md-5 col-xs-5" required="required" type="text">
					              </div>
                        <!-- Inputan NO RM -->
                        <!-- Inputan RM Manual -->
					              <div class="col-md-6 col-sm-6 col-xs-6">
					                <a href="pasien_find.php?TB_iframe=true&height=550&width=800&modal=true" class="thickbox " title="Cari Pasien">
						                <i id="pasien_find" class="fa fa-search" style="display:none;"  > Cari No RM</i>  
				                  </a>
					                <span id="RMMt"><input id="RMM" type="checkbox"> Input RM Manual</span>
					                <script type="text/javascript">
							              $(function() {
							                $('#reg_status_pasien').change(function() {
              									if ($(this).val() == 'B') {
              										$("#pasien_find").css('display','none');
              										$("#RMMt").css('display','block');
              										$("#rm").css('display','none');
              									} else { 
              										$("#pasien_find").css('display','none');
              										$("#RMMt").css('display','none');
              										$("#rm").css('display','block');
              									}
            								  });
							  
							                $('#RMM').change(function() {
              									if ($(this).is(':checked')) {
              									   $("#cust_usr_kode").removeAttr("readonly")
              									} else { 
                                  $("#cust_usr_kode").attr("readonly","readonly")
                                }
            								  });
            								});
							  
                            $('#cust_usr_kode').blur(function() {
								              $.post('cek_noRM.php',{cust_usr_kode:$(this).val()},function(result){
									              if (result.success){
            											alert('No RM "'+result.success+'" sudah ada di database!');
            											$("#cust_usr_kode").focus();
            										}
            									},'json');
          								  });
          							  </script>
						  
						  	          <script type="text/javascript">
                            $('#find_rm').select2({
                              placeholder: 'Cari pasien berdasar no rm',
            									ajax: {
            									  url: 'get_rm.php',
            									  dataType: 'json',
            									  processResults: function (data) {
            										return {
            										  results: data
            										};
          									  },
          									  cache: true
          									},
								            allowClear: true
							            }).on('select2:select', function (e) {
								            $.post( "get_pasien.php", { usr_kode: e.params.data.id },
										          function( data ) {
        												var tgl_temp = data.cust_usr_tanggal_lahir;
        												var tgl = tgl_temp.split('-');
          												$('#cust_usr_kode').val(data.cust_usr_kode);
          												$('#cust_usr_nama').val(data.cust_usr_nama);
          												$('#cust_usr_tempat_lahir').val(data.cust_usr_tempat_lahir);
          												$('#cust_usr_tanggal_lahir').val(tgl[2]+'-'+tgl[1]+'-'+tgl[0]);
          												$('#tahun').val(data.cust_usr_umur_tahun);
          												$('#id_prop').val(data.id_prop);
          												$('#bulan').val(data.cust_usr_umur_bulan);
          												$('#hari').val(data.cust_usr_umur_hari);
          												$('#cust_usr_jenis_kelamin').val(data.cust_usr_jenis_kelamin);
          												$('#cust_usr_agama').val(data.cust_usr_agama);
          												$('#cust_usr_alamat').val(data.cust_usr_alamat);
          												$('#cust_usr_dusun').val(data.cust_usr_dusun);
          												$('#id_card').val(data.id_card);
          												$('#cust_usr_no_identitas').val(data.cust_usr_no_identitas);
          												$('#id_pendidikan').val(data.id_pendidikan);
          												$('#id_pekerjaan').val(data.id_pekerjaan);
          												$('#cust_usr_asal_negara').val(data.cust_usr_asal_negara);
          												$('#id_status_perkawinan').val(data.id_status_perkawinan);
          												$('#cust_usr_penanggung_jawab').val(data.cust_usr_penanggung_jawab);
          												$('#cust_usr_penanggung_jawab_status').val(data.cust_usr_penanggung_jawab_status);
          												$('#cust_usr_no_hp').val(data.cust_usr_no_hp);
          												$('#cust_usr_id').val(data.cust_usr_id);
											
											            if (data.id_prop != null) { ajaxkota(data.id_prop); };
          												var delay = 500;
          												var delay2 = 700;
          												var delay3 = 1000;
          												setTimeout(function() {
          													$('#kota').val(data.id_kota+"&prop="+data.id_prop);
          													ajaxkec(data.id_kota+"&prop="+data.id_prop);
          												}, delay);
											            
                                  setTimeout(function() {
          													$('#kec').val(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
          													ajaxkel(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
          												}, delay2);
											
          												setTimeout(function() {
          													$('#kel').val(data.id_prop+"."+data.id_kota+"."+data.id_kecamatan+"."+data.id_kelurahan);
          												}, delay3);
        											  },"json");
          										});
							              </script>
                          </div>
                        </div>
                        <!-- Inputan RM Manual -->
                        <!-- Inputan Nama -->
            					  <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nama <span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input id="cust_usr_nama" name="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"];?>" class="form-control col-md-7 col-xs-12" data-validate-length-range="6" data-validate-words="2" name="name" placeholder="dua kata contoh:Moch Mansyur" required="required" type="text">
                            <input type="hidden" name="cust_usr_id2" value="<?php echo $custUsrId ?>">
                          </div>
                        </div>
                        <!-- Inputan Nama -->
                        <!-- Inputan Tempat Lahir -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tempat lahir">Tempat Lahir<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" id="cust_usr_tempat_lahir" name="cust_usr_tempat_lahir" value="<?php echo $_POST["cust_usr_tempat_lahir"];?>" required="required" data-validate-length-range="5,20" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <!-- Inputan Tempat Lahir -->
                        <!-- Inputan Tanggal Lahir -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir<span class="required">*</span></label>
                          <div class="col-md-4 col-sm-4  col-xs-12">
                            <input type="text" class="form-control" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" value="<?php echo format_date($_POST["cust_usr_tanggal_lahir"]);?>" data-inputmask="'mask': '99-99-9999'" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required" />
                          </div>
                        </div>
                        <!-- Inputan Tanggal Lahir -->
                        <!-- Inputan Umur -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="umur">Umur<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" name="tahun" id="tahun" size="3" maxlength="3" value="<?php echo $_POST["tahun"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> tahun
      				              <input type="text" name="bulan" id="bulan" size="3" maxlength="3" value="<?php echo $_POST["bulan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> bulan  
	    			                <input type="text" name="hari" id="hari" size="3" maxlength="3" value="<?php echo $_POST["hari"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> hari
                          </div>
                        </div>
                        <!-- Inputan Umur -->
                        <!-- Inputan Jenis Kelamin -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Jenis Kelamin<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select id="cust_usr_jenis_kelamin" class="form-control" name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
                      				<option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
                      				<option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
                      			</select>
                          </div>
                        </div>
                        <!-- Inputan Jenis Kelamin -->
                        <!-- Inputan Agama -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Agama<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">	
                      	      <option value="" >[ Pilih Agama ]</option>	
                      	      <?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
                			        <?php } ?>
                            </select>
                          </div>
                        </div>
                        <!-- Inputan Agama -->
                        <!-- Inputan Golongan Darah -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gol. Darah</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <div class="col-md-5 col-sm-5 col-xs-5">
                              <select class="form-control" name="cust_usr_gol_darah" id="cust_usr_gol_darah">   
                                <option <?php if ($_POST['cust_usr_gol_darah'] = 'A') { echo "selected"; } ?> value="A">A</option>  
                                <option <?php if ($_POST['cust_usr_gol_darah'] = 'AB') { echo "selected"; } ?> value="AB">AB</option>  
                                <option <?php if ($_POST['cust_usr_gol_darah'] = 'B') { echo "selected"; } ?> value="B">B</option>  
                                <option <?php if ($_POST['cust_usr_gol_darah'] = 'O') { echo "selected"; } ?> value="O">O</option>  
                              </select>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                              <span class="control-label col-md-3 col-sm-3 col-xs-3" >Rhesus</span>
                              <div class="col-md-8 col-sm-8 col-xs-8">
                                <select class="form-control" name="cust_usr_gol_darah_resus" id="cust_usr_gol_darah_resus">   
                                  <option <?php if ($_POST['cust_usr_gol_darah_resus'] = 'Positif') { echo "selected"; } ?> value="Positif">Positif</option>  
                                  <option <?php if ($_POST['cust_usr_gol_darah_resus'] = 'Negatif') { echo "selected"; } ?> value="Negatif">Negatif</option>  
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                        <!-- Inputan Golongan Darah -->
                        <!-- Inputan Alergi -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Alergi</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <textarea class="form-control" id="cust_usr_alergi" name="cust_usr_alergi"><?php echo htmlspecialchars($_POST["cust_usr_alergi"]);?></textarea>
                          </div>
                        </div>
                        <!-- Inputan Alergi -->
                        <!-- Inputan Alamat -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="alamat">Alamat<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <textarea class="form-control" id="cust_usr_alamat" name="cust_usr_alamat"><?php echo htmlspecialchars($_POST["cust_usr_alamat"]);?></textarea> 
                          </div>
                        </div>
                        <!-- Inputan Alamat -->
                        <!-- Input Dusun -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Nama Dusun/RT/RW <span class="required">&nbsp;</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" id="cust_usr_dusun" name="cust_usr_dusun" value="<?php echo $_POST["cust_usr_dusun"];?>" data-validate-length-range="5,20" class="optional form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <!-- Input Dusun -->
                        <!-- Input Propinsi -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Propinsi <span class="required">&nbsp;</span>
                          </label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="id_prop" id="id_prop" onchange="ajaxkota(this.value)">
                              <!-- <option value="">Pilih Provinsi</option> -->
                              <?php                                                      
                              for($i=0,$n=count($dataProvinsi);$i<$n;$i++) { ?>  
                                <option value="<?php echo $dataProvinsi[$i]['lokasi_propinsi'];?>" 
                                <?php if($dataProvinsi[$i]["lokasi_propinsi"]==$_POST["id_prop"]) { echo "selected"; } elseif($dataProvinsi[$i]["lokasi_propinsi"]=='31') echo "selected";?>

                                  ><?php echo $dataProvinsi[$i]['lokasi_nama'];?></option>';
                              <? } ?>                                                                   
                              <option value="0">Tidak Tahu</option>
                            </select>
                          </div>
                        </div>
                        <!-- Input Propinsi -->
                        <!-- Input Kota -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kota</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="kota" id="kota" onchange="ajaxkec(this.value)">
                              <option value="">Pilih Kota</option>
                              <?php for($i=0,$n=count($dataKota);$i<$n;$i++) { ?>  
                                <option value="<?php echo $dataKota[$i]['lokasi_kabupatenkota'].'&prop=31';?>" <?php if($dataKota[$i]["lokasi_kabupatenkota"]==$_POST["id_prop"]) echo "selected";?>><?php echo $dataKota[$i]['lokasi_nama'];?></option>';
                              <? } ?>                                                                   
                              <option value="0">Tidak Tahu</option>
                            </select> 
                          </div>
                        </div>
                        <!-- Input Kota -->
                        <!-- Input Kecamatan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kecamatan</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="kec" id="kec" onchange="ajaxkel(this.value)">
                              <option value="">Pilih Kecamatan</option>
                            </select> 
                          </div>
                        </div>
                        <!-- Input Kecamatan -->
                        <!-- Input Kelurahan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Kelurahan</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="kel" id="kel">
                              <option value="">Pilih Kelurahan/Desa</option>
                            </select> 
                            <input type="hidden" id="id_kel" name="id_kel" value="<?php echo $_POST["id_prop"].".".$_POST["id_kota"].".".$_POST["id_kecamatan"].".".$_POST["id_kelurahan"];?>"/>
                          </div>
                        </div>  
                        <!-- Input Kelurahan -->
                        <div class="ln_solid"></div>
                        <div class="form-group">
                          <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3"></div>
                        </div>
                      </div>
                    </div>
                    <!-- Tombol -->
            				<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
            				  <button type="button" class="btn btn-primary" onclick="window.location.replace('<?=$backPage?>')">Kembali</button>
            				  <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn col-md-5 btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                    </div>
                    <!-- Tombol -->
			            </div>
                  <div class="col-md-6 col-sm-6 col-xs-6">
                    <!-- Foto Pasien -->
			              <div class="x_panel">
                      <div class="x_title">
                        <h2>Foto Pasien</h2>
                        <span class="pull-right"></span>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <div class="form-group">
                          <img hspace="2" height="100" name="original" id="original" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_foto"]) echo $lokTakeFoto."/".$_POST["cust_usr_foto"]; else echo $lokTakeFoto."/default.jpg";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_pic.php?orifoto='+ document.frmFind.cust_usr_foto.value + '&nama=<?php echo $_POST["vcust_usr_kode"];?>','UploadFoto')">
              						<input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>"><br/>
              						<div class="camTops"  alt="foto pasien" title="foto pasien">
              							<input type="button" id="Ambil Foto" size="35" name="Ambil Foto" value="Ambil Foto" class="btn btn-default">
              						</div>  
             		        </div>					  
                      </div>
                    </div>
                    <!-- Foto Pasienn -->
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>&nbsp;</h2>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <br />
                        <!-- Inputan NO HP -->
            					  <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="telephone">No. HP <span class="required">*</span></label>
                          <div class="col-md-4 col-sm-4 col-xs-12">
                            <input type="text" id="cust_usr_no_hp" name="cust_usr_no_hp" value="<?php echo $_POST["cust_usr_no_hp"];?>" maxlength="13" required="required" data-validate-length-range="10,13" class="form-control col-md-7 col-xs-12">
                          </div>
                        </div>
                        <!-- Inputan NO HP -->
                        <!-- Inputan NO KTP -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nik">No. KTP / Identitas <span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" class="form-control" name="cust_usr_no_identitas" id="cust_usr_no_identitas" size="30" maxlength="65" value="<?php echo $_POST["cust_usr_no_identitas"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>&nbsp;Jenis :
                            <select name="id_card" class="form-control" onKeyDown="return tabOnEnter(this, event);">
                      				<option value="KTP" <?php if($_POST["id_card"]=="KTP")echo "selected";?>>KTP</option>
                      				<option value="SIM" <?php if($_POST["id_card"]=="SIM")echo "selected";?>>SIM</option>
                      				<option value="PASPOR" <?php if($_POST["id_card"]=="PASPOR")echo "selected";?>>PASPOR</option>
                      			</select>
                          </div>
                        </div>
                        <!-- Inputan NO KTP -->
                        <!-- Inputan Pendidiikan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Pendidikan <span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="id_pendidikan" id="id_pendidikan" onKeyDown="return tabOnEnter(this, event);">	
                            	<option value="--" >[ Pilih sekolah ]</option>	
                            	<?php for($i=0,$n=count($dataPendidikan);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataPendidikan[$i]["pendidikan_id"];?>" <?php if($dataPendidikan[$i]["pendidikan_id"]==$_POST["id_pendidikan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPendidikan[$i]["pendidikan_nama"];?></option>
                      			  <?php } ?>
                            </select>
                          </div>
                        </div>
                        <!-- Inputan Pendidiikan -->
                        <!-- Input Pekerjaan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pekerjaan<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="id_pekerjaan" id="id_pekerjaan" onKeyDown="return tabOnEnter(this, event);">	
                            	<option value="" >Pilih Pekerjaan</option>
                            	<?php for($i=0,$n=count($dataPekerjaan);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"];?>" <?php if($dataPekerjaan[$i]["pekerjaan_id"]==$_POST["id_pekerjaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPekerjaan[$i]["pekerjaan_nama"];?></option>
                      			  <?php } ?>	
                            </select>
                          </div>
                        </div>
                        <!-- Input Pekerjaan -->
                        <!-- Input Kebangsaan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Kebangsaan<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="cust_usr_asal_negara" id="cust_usr_asal_negara" onKeyDown="return tabOnEnter(this, event);">	
                            	<option value="" >Pilih Kebangsaan</option>
                            	<?php for($i=0,$n=count($dataNegara);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataNegara[$i]["negara_id"];?>" <?php if($dataNegara[$i]["negara_id"]==$_POST["cust_usr_asal_negara"]) echo "selected"; ?>><?php echo $dataNegara[$i]["negara_nama"]." ( ".$dataNegara[$i]["negara_kode"]." ) ";?></option>
	             		            <?php } ?>	
                            </select>
                          </div>
                        </div>
                        <!-- Input Kebangsaan -->
                        <!-- Status Pernikahan -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Status Pernikahan<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <select class="form-control" name="id_status_perkawinan" id="id_status_perkawinan" onKeyDown="return tabOnEnter(this, event);">	
                        	    <option value="" >Pilih Status Perkawinan</option>
                        	    <?php for($i=0,$n=count($dataStatus);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataStatus[$i]["status_perkawinan_id"];?>" <?php if($dataStatus[$i]["status_perkawinan_id"]==$_POST["id_status_perkawinan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatus[$i]["status_perkawinan_nama"];?></option>
        			                <?php } ?>	
                            </select>
                          </div>
                        </div>
                        <!-- Status Pernikahan -->
                        <!-- Input Penanggung Jawab -->
                        <div class="item form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" >Nama Penanggung Jawab<span class="required">*</span></label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input type="text" class="form-control" name="cust_usr_penanggung_jawab" id="cust_usr_penanggung_jawab" size="30" maxlength="65" value="<?php echo $_POST["cust_usr_penanggung_jawab"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>&nbsp;Status :
                            <select class="form-control" name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">	
                            	<option value="" >- Pilih Hubungan -</option>
                            	<?php for($i=0,$n=count($dataStatusPJ);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataStatusPJ[$i]["status_pj_id"];?>" <?php if($dataStatusPJ[$i]["status_pj_id"]==$_POST["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatusPJ[$i]["status_pj_nama"];?></option>
              			          <?php } ?>	
                            </select>
                          </div>
                        </div>
                        <!-- Input Penanggung Jawab -->
				                <input type="hidden" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>"  value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>"/>
				                <input type="hidden" name="cust_usr_id" id="cust_usr_id" value="<?php echo $custUsrId; ?>">
                        <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="camera">
				          <span class="camTop"></span>
        				  <div id="screen"></div>
        					<div id="buttons" style="margin-top:90px;">
        						<div class="buttonPane">
        							<a id="shootButton" href="" class="blueButton">Shoot!</a>
        						</div>
        						<div class="buttonPane" style="display:none;">
        							<a id="cancelButton" href="" class="blueButton">Cancel</a> <a id="uploadButton" href="" class="greenButton">Upload!</a>
        						</div>
        					</div>
					        <span class="settings"></span>
			          </div> 
              </div>
          </div>
        </form>
        <?php require_once($LAY."footer.php"); ?>
      </div>
    </div>
    <script src="<?php echo $ROOT; ?>assets/vendors/validator/validator.js"></script>
    <?php require_once($LAY."js.php"); ?>
  </body>
</html>