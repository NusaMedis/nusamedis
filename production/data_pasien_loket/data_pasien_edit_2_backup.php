<?php    
     //echo "masuk".$_POST["btnUpdate"];
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
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	  
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
     if($_GET["id"]) 
     {
       $_x_mode = "Edit";
       $custUsrId = $enc->Decode($_GET["id"]);
     }
     
     //PENGATURAN MODE ADD atau UPDATE atau DELETE
     if($_POST["x_mode"]) //JIKA ADA POST MODE 
     {
      $_x_mode = & $_POST["x_mode"];
      if ($_POST["btnDelete"])       
         $_x_mode = "Delete";
	    else 
          $_x_mode = "New"; 
     }
     
     if($_POST["btnUpdate"])
     {
           $custUsrId = & $_POST["cust_usr_id"];             
           $_x_mode = "Edit";
      }
     
  	if($_x_mode=="New") $privMode = PRIV_CREATE;
 	  elseif($_x_mode=="Edit") $privMode = PRIV_UPDATE;
	  else $privMode = PRIV_DELETE;    
     
     //INISIALISASI /
  	function CheckDataKamar($kamarKode,$custUsrId=null)
  	{
            global $dtaccess;
            
            $sql = "SELECT a.cust_usr_id FROM klinik.klinik_kamar a 
                      WHERE upper(a.cust_usr_nama) = ".QuoteValue(DPE_CHAR,strtoupper($kamarKode));
                      
            if($custUsrId) $sql .= " and a.cust_usr_id <> ".QuoteValue(DPE_NUMERIC,$custUsrId);
            
            $rs = $dtaccess->Execute($sql);
            $dataAdaKamar = $dtaccess->Fetch($rs);
            
  		return $dataAdaKamar["cust_usr_id"];
     }

     //DATA VIEW UNTUK EDIT
     if ($_GET["id"]) {
          $sql = "select a.* from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$custUsrId);
          $rs_edit = $dtaccess->Execute($sql);
          $row_edit = $dtaccess->Fetch($rs_edit);
          $view->CreatePost($row_edit);
		  
	$umurtahun = (strtotime(date("Y-m-d")) - strtotime(date_db($_POST["cust_usr_tanggal_lahir"])))/86400/365;
    $umurbulan = ($umurtahun - floor($umurtahun)) * 12;
    $umurhari = ($umurbulan - floor($umurbulan)) * 31; 
    //echo $hitungUmur."-".floor($umurtahun)."-".floor($umurbulan)."-".floor($umurhari);
    $_POST["tahun"]=floor($umurtahun);
  	$_POST["bulan"]=floor($umurbulan);
  	$_POST["hari"]=floor($umurhari);
	
	
//echo "masuk ".$_POST["id_kec"];
   }
  
//jika edit jangan gunakan data paien kode .php
  if(!$_GET['id']) {require_once("data_pasien_kode.php");}
//jika no rm manual jangan gunakan data pasien kode
  if (!$_POST["cust_usr_kode"]) $_POST["cust_usr_kode"] = $_POST["kode_pasien"];
  if (!$_POST["cust_usr_kode_tampilan"]) $_POST["cust_usr_kode_tampilan"] = $_POST["kode_pasien_tampilan"];
	//die ( $_POST["cust_usr_kode"] );
   //die();
    // FUNGSI ADD dan DELETE
    if ($_POST["btnSave"] || $_POST["btnUpdate"]) 
    {                               
	if($_POST['reg_status_pasien'] == "B") {
		$sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["kel"]."'";
          //  echo "post".$sql; die();
         $lokasidaerah = $dtaccess->Fetch($sql);
         //echo "masuk";              
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
         //$dbField[22] = "cust_berat_lahir";
         $dbField[22] = "cust_usr_foto";
		   
		// if($_POST["btnSave"]){
		 $dbField[23] = "cust_usr_kode";
     $dbField[24] = "cust_usr_kode_tampilan";
     $dbField[25] = "cust_usr_penanggung_jawab";
     $dbField[26] = "cust_usr_penanggung_jawab_status";
		 //}
		 
         if(!$custUsrId) $custUsrId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHAR,$custUsrId);         
         $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["cust_usr_nama"]);
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
         //$dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_berat_lahir"]);
         $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
		          
        // if($_POST["btnSave"]){
         $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
         $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode_tampilan"]);
         $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab"]);
         $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_status"]);
		 //}
		 
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		// print_r($dbValue);
		 //  die();
		   
         if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
         
         } else if ($_POST["btnUpdate"]) {
              $dtmodel->Update() or die("update  error");	
         } 
         
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
         //die();
	}	 
		 include("reg_pas_lama.php");
		 //die();
		 
         header("location:".$backPage.$_POST["cust_usr_kode"]);
        // echo "tes link kembali ".$backPage.$custUsrId;
         exit();        
     }
	 
 
    if ($_GET["del"]) {
          $custUsrId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR,$custUsrId);
           $dtaccess->Execute($sql);
     
          header("location:".$backPage);
          exit();    
     }
   
	 if($isAllowedCreate)
     {
          $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     }
	 
   $tombolback = '<button class="btn btn-Primary"  type="button" onClick="window.history.back()">Kembali</button>';
	
	//cari data agama
     $sql = "select * from global.global_agama order by agm_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataAgama = $dtaccess->FetchAll($rs); 
	 
	 //cari data pendidikan
     $sql = "select * from global.global_pendidikan order by pendidikan_urut";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPendidikan = $dtaccess->FetchAll($rs);
         
//cari data pekerjaan
     $sql = "select * from global.global_pekerjaan order by pekerjaan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPekerjaan = $dtaccess->FetchAll($rs);

 //combo negara kebangsaan
     $sql = "select * from global.global_negara order by negara_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataNegara = $dtaccess->FetchAll($rs);    
	 
	  //cari status perkawinan
     $sql = "select * from global.global_status_perkawinan order by status_perkawinan_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatus = $dtaccess->FetchAll($rs);
	 
	 // data mbuh
     $sql = "select * from global.global_status_pj order by status_pj_nama";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataStatusPJ = $dtaccess->FetchAll($rs);
	 
	 // data instalasi
     $sql = "select instalasi_id, instalasi_nama from global.global_auth_instalasi";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataInstalasi = $dtaccess->FetchAll($rs);
	 
	 //cari data Sebab Sakit
     $sql = "select * from global.global_sebab_sakit";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataSebabSakit = $dtaccess->FetchAll($rs);
	 
	 // Data Layanan / tipe biaya //
     $sql = "select * from  global.global_tipe_biaya where tipe_biaya_aktif ='y' ";
     $rs = $dtaccess->Execute($sql);
     $dataLayanan = $dtaccess->FetchAll($rs);
	 
	 // Data Shift //
     $sql = "select * from  global.global_shift a where a.shift_aktif='y' order by shift_id limit 1";
     $rs = $dtaccess->Execute($sql);
     $dataShift = $dtaccess->FetchAll($rs);
	 
	 //cari data cara kunjungan
     $sql = "select * from global.global_rujukan";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataCaraKunjungan = $dtaccess->FetchAll($rs);
	 
	 // Data jenis pasien yang ditampilkan umum saja//
     $sql = "select * from  global.global_jenis_pasien a where jenis_id<>".PASIEN_BAYAR_BPJS." and jenis_flag='y'";
     $rs = $dtaccess->Execute($sql);
     $dataJPasien = $dtaccess->FetchAll($rs);
	 
	 // Data dokter dan pelaksana
	 $sql = "select * from global.global_auth_user a
             left join global.global_auth_role b on a.id_rol = b.rol_id
             where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='A') and a.id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataDokter = $dtaccess->FetchAll($rs);
     $dataPelaksana = $dtaccess->FetchAll($rs);
	 
	 // Data prosedur masuk
	 $sql = "select * from global.global_prosedur_masuk";    
     $rs = $dtaccess->Execute($sql);
     $dataProsedurMasuk = $dtaccess->FetchAll($rs);
	 

	 
	 $lokasi = $ROOT."gambar/foto_pasien";
    $lokTakeFoto = $ROOT."gambar/foto_pasien"; 
	
	$tableHeader = "Registrasi Pasien Baru"
?>

<!DOCTYPE html>
<html lang="en">
  
	<?php require_once($LAY."header.php") ?>
	<link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
	<script src="assets/fancybox/jquery.easing-1.3.pack.js"></script>
	<script src="assets/webcam/webcam.js"></script>
	
<script type="text/javascript">
$(document).ready(function(){
    $('#instalasi').on('change',function(){
        var instalasi_id = $(this).val();
        if(instalasi_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'instalasi_id='+instalasi_id,
                success:function(html){
                    $('#sub_instalasi').html(html);
                    $('#klinik').html('<option value="">Pilih Instalasi Dahulu</option>'); 
                }
            }); 
        }else{
            $('#sub_instalasi').html('<option value="">Pilih Instalasi Dahulu</option>');
            $('#klinik').html('<option value="">Pilih Sub Instalasi Dahulu</option>'); 
        }
    });
    
    $('#sub_instalasi').on('change',function(){
        var sub_instalasi_id = $(this).val();
        if(sub_instalasi_id){
            $.ajax({
                type:'POST',
                url:'RS_Data.php',
                data:'sub_instalasi_id='+sub_instalasi_id,
                success:function(html){
                    $('#klinik').html(html);
                }
            }); 
        }else{
            $('#klinik').html('<option value="">Pilih Sub Instalasi Dahulu</option>'); 
        }
    });
});
</script>

<script type="text/javascript">
$(document).ready(function(){
	
	var camera = $('#camera'),
		photos = $('#photos'),
		screen =  $('#screen');

	var template = '<a href="<?php echo $ROOT;?>gambar/foto_pasien/{src}" rel="cam" '
		+'style="background-image:url(<?php echo $ROOT;?>gambar/thumbs/{src})"></a>';

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

	// Showing and hiding the camera panel:	
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
		}
		else {
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
		// When the flash loads, enable
		// the Shoot and settings buttons:
		shootEnabled = true;
	});
	
	webcam.set_hook('onComplete', function(msg){
		
		// This response is returned by upload.php
		// and it holds the name of the image in a
		// JSON object format:
		msg1 = $.parseJSON(msg);
    
		if(msg.error){
   // alert('masuk foto');
			alert(msg1.message);
		}
		else {  
     //     alert(msg1.filename);
			 //Adding it to the page;    
      document.getElementById('cust_usr_foto').value=msg1.filename; 
      
      document.original.src='<?php echo $lokTakeFoto."/";?>'+msg1.filename;  
      //alert(kepet);
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


    <!--function UMUR -->
   <script>
   
   //Perhitungan Umur//
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
// end perhitungan Umur //
// ajax untuk lokasi

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
    //alert(id);
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
    //alert(data);
    if(data.length>=0){
    document.getElementById("kota").innerHTML = data
    }else{
    document.getElementById("kota").value = "<option selected>Pilih Kota/Kab</option>";
    }
    }
}

function stateChangedKec(){
    var data;
    if (ajaxku.readyState==4){
    data=ajaxku.responseText;
    //alert(data);
    if(data.length>=0){
    document.getElementById("kec").innerHTML = data
    }else{
    document.getElementById("kec").value = "<option selected>Pilih Kecamatan</option>";
    }
    }
}

function stateChangedKel(){
    var data;
    if (ajaxku.readyState==4){
    data=ajaxku.responseText;
    //alert(data);
    if(data.length>=0){
    document.getElementById("kel").innerHTML = data
    }else{
    document.getElementById("kel").value = "<option selected>Pilih Kelurahan/Desa</option>";
    }
    }
}
// end ajax lokasi
</script> 

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
		
        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
        <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
		<input type="hidden" value="<?php echo $_GET['ref']; ?>" name="ref"> 
       <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>&nbsp;</h3>
              </div>

              <div class="title_right">
                
              </div>
            </div>
            <div class="clearfix"></div>
            
            <!-- Row 1 Input Data Pasien -->
            <div class="row">
            
            <!-- Kolom 1 Input Data Pasien -->
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      </li>
                      
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <br />
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" ><span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="reg_status_pasien" class="form-control" name="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);">
            				<option value="L">Pasien Lama</option>
            				<option value="B">Pasien Baru</option>
            			</select>
                        </div>
                      </div>
					  <div id ="rm" class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="find_rm" class="form-control find_rm" name="find_rm">
            				<option value=""></option>
            				
            			</select>
                        </div>
                      </div>

					  
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">No RM <span class="required">*</span>
                        </label>
                        <div class="col-md-3 col-sm-3 col-xs-3">
                          <input id="cust_usr_kode" name="cust_usr_kode" readonly="readonly" value="<?php echo $_POST["cust_usr_kode"];?>" class="form-control col-md-5 col-xs-5" required="required" type="text">
						  </div>
						  <div class="col-md-6 col-sm-6 col-xs-6">
						 <a href="pasien_find.php?TB_iframe=true&height=550&width=800&modal=true" class="thickbox " title="Cari Pasien">
							<i id="pasien_find" class="fa fa-search" style="display:none;"  > Cari No RM</i>  
						 </a>
						 
						   <span id="RMMt" style="display:none;" ><input id="RMM" type="checkbox"> Input RM Manual</span>
						      <script type="text/javascript">
								$(function()
								{
								  $('#reg_status_pasien').change(function()
								  {
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
								  
								  $('#RMM').change(function()
								  {
									if ($(this).is(':checked')) {
									   $("#cust_usr_kode").removeAttr("readonly")
									} else { $("#cust_usr_kode").attr("readonly","readonly")}
								  });
								});
								 //cek ketersediaan no rm
								  $('#cust_usr_kode').blur(function()
								  {
									//alert($(this).val());
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
									  //alert(e.params.data.id);
											$.post( "get_pasien.php", { usr_kode: e.params.data.id },
											function( data ) {
												var tgl_temp = data.cust_usr_tanggal_lahir;
												var tgl = tgl_temp.split('-');
												//alert(tgl[2]);
												//alert( data.cust_usr_tanggal_lahir);
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
												
												ajaxkota(data.id_prop);
												var delay = 500;
												var delay2 = 1000;
												var delay3 = 1500;
												setTimeout(function() {
													$('#kota').val(data.id_kota+"&prop="+data.id_prop);
													ajaxkec(data.id_kota+"&prop="+data.id_prop);
													//$('#kec').val(data.id_kecamatan);
													//console.log("anjay");
												}, delay);
												setTimeout(function() {
													$('#kec').val(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
													ajaxkel(data.id_kecamatan+"&kec="+data.id_kota+"&prop="+data.id_prop);
													//console.log("anjay");
												}, delay2);
												setTimeout(function() {
													$('#kel').val(data.id_prop+"."+data.id_kota+"."+data.id_kecamatan+"."+data.id_kelurahan);
												}, delay3);
												
											  },"json");
											
										});
									 

								</script>
                        </div>
                      </div>
					  					  
					  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nama <span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input id="cust_usr_nama" name="cust_usr_nama" value="<?php echo $_POST["cust_usr_nama"];?>" class="form-control col-md-7 col-xs-12" data-validate-length-range="6" data-validate-words="2" name="name" placeholder="dua kata contoh:Moch Mansyur" required="required" type="text">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tempat lahir">Tempat Lahir<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="cust_usr_tempat_lahir" name="cust_usr_tempat_lahir" value="<?php echo $_POST["cust_usr_tempat_lahir"];?>" required="required" data-validate-length-range="5,20" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Lahir<span class="required">*</span></label>
                        <div class="col-md-4 col-sm-4  col-xs-12">
                          <input type="text" class="form-control" id="cust_usr_tanggal_lahir" name="cust_usr_tanggal_lahir" value="<?php echo format_date($_POST["cust_usr_tanggal_lahir"]);?>" data-inputmask="'mask': '99-99-9999'" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required" />
                         <!-- <input type="text" id="tgl" name="tgl" size="2" maxlength="2" value="<?php echo $_POST["tgl"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/> -
                		  <input type="text" id="bln" name="bln" size="2" maxlength="2" value="<?php echo $_POST["bln"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/> -
                		  <input type="text" id="thn" name="thn" size="4" maxlength="4" value="<?php echo $_POST["thn"];?>" onKeyDown="return tabOnEnter(this, event);" onChange="Umur(this.value);" required="required"/><font color="red">*</font>-->
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="umur">Umur<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                        <input type="text" name="tahun" id="tahun" size="3" maxlength="3" value="<?php echo $_POST["tahun"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> tahun
        				<input type="text" name="bulan" id="bulan" size="3" maxlength="3" value="<?php echo $_POST["bulan"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> bulan  
		    			<input type="text" name="hari" id="hari" size="3" maxlength="3" value="<?php echo $_POST["hari"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="TanggalLahir(this.value);"/> hari
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Jenis Kelamin<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="cust_usr_jenis_kelamin" class="form-control" name="cust_usr_jenis_kelamin" onKeyDown="return tabOnEnter(this, event);">
            				<option value="L" <?php if($_POST["cust_usr_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
            				<option value="P" <?php if($_POST["cust_usr_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
            			</select>
                        </div>
                      </div>
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Agama<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">	
                    	<option value="" >[ Pilih Agama ]</option>	
                    	<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>
                       <option value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["cust_usr_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
              			  <?php } ?>
                    </select>
                    </div>
                      </div>
                    
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="alamat">Alamat<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="cust_usr_alamat" name="cust_usr_alamat" value="<?php echo $_POST["cust_usr_alamat"];?>" required="required" data-validate-length-range="5,40" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					<div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="prop">Propinsi / Kota<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_prop" id="id_prop" onchange="ajaxkota(this.value)">
      					<option value="">Pilih Provinsi</option>
      					<?php          
                  $sql = "select * from  global.global_lokasi where lokasi_kabupatenkota='00' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_id	";
                  $dataProvinsi = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataProvinsi);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataProvinsi[$i]['lokasi_propinsi'];?>" <?php if($dataProvinsi[$i]["lokasi_propinsi"]==$_POST["id_prop"]) echo "selected";?>><?php echo $dataProvinsi[$i]['lokasi_nama'];?></option>';
                  <? } ?>                                                                   
      				</select>
            &nbsp;&nbsp;
          <?if (!$_POST["id_kota"]) { ?>
            <select class="form-control" name="kota" id="kota" onchange="ajaxkec(this.value)">
    					<option value="">Pilih Kota</option>
    				</select> 
          <? } else { ?>
            <select class="form-control" name="kota" id="kota" onchange="ajaxkec(this.value)">
    					<option value="">Pilih Kota</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_nama";
                  $dataKabKota = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataKabKota);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataKabKota[$i]['lokasi_kabupatenkota'];?>" <?php if($dataKabKota[$i]["lokasi_kabupatenkota"]==$_POST["id_kota"]) echo "selected";?>><?php echo $dataKabKota[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select> 
                   <? } ?>

                        </div>
                      </div>
				<div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="alamat">Kecamatan / Kelurahan<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
            <?if (!$_POST["id_kecamatan"]) { ?>
        		<select class="form-control" name="kec" id="kec" onchange="ajaxkel(this.value)">
    					<option value="">Pilih Kecamatan</option>
    				</select>	
          <? } else { ?>
            <select class="form-control" name="kec" id="kec" onchange="ajaxkel(this.value)">
    					<option value="">Pilih Kecamatan</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kabupatenkota='".$_POST["id_kota"]."' and lokasi_kelurahan='0000' order by lokasi_nama";
                  $dataKec = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataKec);$i<$n;$i++) { ?>  
        						<option value="<?php echo $dataKec[$i]['lokasi_kecamatan'];?>" <?php if($dataKec[$i]["lokasi_kecamatan"]==$_POST["id_kecamatan"]) echo "selected";?>><?php echo $dataKec[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select> 
                     
          <? } ?>

         &nbsp;&nbsp;
        <?if (!$_POST["id_kelurahan"]) { ?>
            <select class="form-control" name="kel" id="kel">
    					<option value="">Pilih Kelurahan/Desa</option>
    				</select> 
          <? } else { ?>
            <select class="form-control" name="kel" id="kel">
    					<option value="">Pilih Kelurahan/Desa</option>
              <?php          
                  $sql = "select * from  global.global_lokasi where lokasi_propinsi='".$_POST["id_prop"]."' and lokasi_kabupatenkota='".$_POST["id_kota"]."' and lokasi_kecamatan='".$_POST["id_kecamatan"]."' order by lokasi_nama";
                  $dataKel = $dtaccess->FetchAll($sql);
        			                                                       
        		    	for($i=0,$n=count($dataKel);$i<$n;$i++) { ?>  
        				<option value="<?php echo $dataKel[$i]['lokasi_kelurahan'];?>" <?php if($dataKel[$i]["lokasi_kelurahan"]==$_POST["id_kelurahan"]) echo "selected";?>><?php echo $dataKel[$i]['lokasi_nama'];?></option>';
              <? } ?>
    				</select>                  
                                
          <? } ?>
<input type="hidden" id="id_kel" name="id_kel" value="<?php echo $_POST["id_prop"].".".$_POST["id_kota"].".".$_POST["id_kecamatan"].".".$_POST["id_kelurahan"];?>"/>
                        </div>
                      </div>                                            
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="dusun">Nama Dusun/RT/RW <span class="required">&nbsp;</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" id="cust_usr_dusun" name="cust_usr_dusun" value="<?php echo $_POST["cust_usr_dusun"];?>" data-validate-length-range="5,20" class="optional form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
                        </div>
                      </div>
                  </div>
                </div>
				
				<!-- begin kolom kanan row 2 // panel instalasi -->
				<div class="x_panel">
                  <div class="x_title">
                    <h2>Registrasi </h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content" >
				  
					<div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Instalasi</label>
                        <select id="instalasi" class="select2_single form-control" name="instalasi" >
							<option class="form_control" value="">- Pilih instalasi -</option>
							<?php 
       						for($i=0,$n=count($dataInstalasi);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataInstalasi[$i]["instalasi_id"];?>">
        						<?php echo $dataInstalasi[$i]["instalasi_nama"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Sub Instalasi</label>
                        <select id="sub_instalasi" class="select2_single form-control" name="sub_instalasi" >
							<option class="form_control" value="">- Pilih Sub instalasi -</option>
						</select>
						
                    </div>
					
					<div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Klinik</label>
                        <select id="klinik" class="select2_single form-control" name="klinik" required="required">
							<option class="form_control" value="">- Pilih Klinik -</option>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Nama Dokter</label>
                        <select id="dokter" class="select2_single form-control" name="dokter" >
						<option class="form_control" value="">- Pilih Dokter -</option>
							<?php 
       						for($i=0,$n=count($dataDokter);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataDokter[$i]["usr_id"];?>">
        						<?php echo $dataDokter[$i]["usr_name"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12" >
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Sebab Sakit</label>
                        <select id="reg_sebab_sakit" class="select2_single form-control" name="reg_sebab_sakit" >
						<option class="form_control" value="">- Pilih Sebab Sakit -</option>
							<?php for($i=0,$n=count($dataSebabSakit);$i<$n;$i++){ ?>
        					<option class="form_control" value="<?php echo $dataSebabSakit[$i]["sebab_sakit_id"];?>">
        						<?php echo $dataSebabSakit[$i]["sebab_sakit_nama"];?> 
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tipe Pelayanan</label>
                        <select class="select2_single form-control" name="layanan" >
						<option class="form_control" value="">- Pilih Tipe Layanan -</option>
							<?php 
       						for($i=0,$n=count($dataLayanan);$i<$n;$i++){
								?>
        					<option class="form_control" selected value="<?php echo $dataLayanan[$i]["tipe_biaya_id"];?>">
        						<?php echo $dataLayanan[$i]["tipe_biaya_nama"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Shift Pelayanan</label>
                        <select id="reg_shift" class="select2_single form-control" name="reg_shift" >
							<?php 
       						for($i=0,$n=count($dataShift);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataShift[$i]["shift_id"];?>">
        						<?php echo $dataShift[$i]["shift_nama"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Prosedur Masuk</label>
                        <select id="reg_prosedur_masuk" class="select2_single form-control" name="reg_prosedur_masuk" required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')">
						<option class="form_control" value="">- Pilih Prosedur Masuk -</option>
							<?php 
       						for($i=0,$n=count($dataProsedurMasuk);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_id"];?>">
        						<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_nama"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Kunjungan</label>
                        <select id="reg_rujukan_id" class="select2_single form-control" name="reg_rujukan_id" >
						<option class="form_control" value="">- Pilih Cara Kunjungan -</option>
							<?php 
       						for($i=0,$n=count($dataCaraKunjungan);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataCaraKunjungan[$i]["rujukan_id"];?>">
        						<?php echo $dataCaraKunjungan[$i]["rujukan_nama"];?>   
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Bayar</label>
                        <select id="reg_jenis_pasien" class="select2_single form-control" name="reg_jenis_pasien">
						<!--<option class="form_control" value="">- Pilih Cara Bayar -</option>-->
							<?php 
       						for($i=0,$n=count($dataJPasien);$i<$n;$i++){
								?>
        					<option class="form_control" value="<?php echo $dataJPasien[$i]["jenis_id"];?>">
        						<?php echo $dataJPasien[$i]["jenis_nama"];?>
          					</option>
          				<?php } ?>
						</select>
                    </div>
					
					<div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Diagnosa Awal</label>
                        <textarea id="reg_diagnosa_awal" name="reg_diagnosa_awal" class="form-control"></textarea>
                    </div>
					
                  </div>
				  
				</div>
				<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
                          <?php echo $tombolback ?>
                          <button id="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>" class="btn col-md-5 btn-success"><? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?></button>
                        </div>
				<!-- end kolom kanan row 2 -->
				
              </div>
              <!-- END KOLOM 1 DATA PASIEN -->

            <!-- Kolom 2 Input Data Pasien -->
              <div class="col-md-6 col-sm-6 col-xs-6">
				<div class="x_panel">
                  <div class="x_title">
                    <h2>Foto Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                        <!--td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if($_POST["cust_usr_foto"]) echo $lokasi."/".$_POST["cust_usr_foto"]; else echo $lokasi."/default.jpg";?>" height="100px" width="100px" align="center"/></td-->
						<img hspace="2" height="100" name="original" id="original" style="cursor:pointer; margin-bottom:15px; " src="<?php if($_POST["cust_usr_foto"]) echo $lokTakeFoto."/".$_POST["cust_usr_foto"]; else echo $lokTakeFoto."/default.jpg";?>" valign="middle" border="1" onDblClick="BukaWindowBaru('reg_pic.php?orifoto='+ document.frmFind.cust_usr_foto.value + '&nama=<?php echo $_POST["vcust_usr_kode"];?>','UploadFoto')">
						<input type="hidden" name="cust_usr_foto" id="cust_usr_foto" value="<?php echo $_POST["cust_usr_foto"];?>">
						<br/>
						<div class="camTops"  alt="foto pasien" title="foto pasien">
							<input type="button" id="Ambil Foto" size="35" name="Ambil Foto" value="Ambil Foto" class="btn btn-default">
						</div>  
               		</div>					  
                  </div>
                </div>
				
                <div class="x_panel">
                  <div class="x_title">
                    <h2>&nbsp;</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <br />
              					<div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="telephone">No. HP <span class="required">*</span>
                        </label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <input type="text" id="cust_usr_no_hp" name="cust_usr_no_hp" value="<?php echo $_POST["cust_usr_no_hp"];?>" maxlength="13" required="required" data-validate-length-range="10,13" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nik">No. KTP / Identitas <span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                         <input type="text" class="form-control" name="cust_usr_no_identitas" id="cust_usr_no_identitas" size="30" maxlength="65" value="<?php echo $_POST["cust_usr_no_identitas"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                         &nbsp;Jenis :
                     <select name="id_card" class="form-control" onKeyDown="return tabOnEnter(this, event);">
            				<option value="KTP" <?php if($_POST["id_card"]=="KTP")echo "selected";?>>KTP</option>
            				<option value="SIM" <?php if($_POST["id_card"]=="SIM")echo "selected";?>>SIM</option>
            				<option value="PASPOR" <?php if($_POST["id_card"]=="PASPOR")echo "selected";?>>PASPOR</option>
            			</select>
 
                        </div>
                      </div>
                       
                     <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Pendidikan <span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_pendidikan" id="id_pendidikan" onKeyDown="return tabOnEnter(this, event);">	
                	<option value="--" >[ Pilih sekolah ]</option>	
                	<?php for($i=0,$n=count($dataPendidikan);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataPendidikan[$i]["pendidikan_id"];?>" <?php if($dataPendidikan[$i]["pendidikan_id"]==$_POST["id_pendidikan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPendidikan[$i]["pendidikan_nama"];?></option>
          			  <?php } ?>
                </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pekerjaan<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_pekerjaan" id="id_pekerjaan" onKeyDown="return tabOnEnter(this, event);">	
                  	 <option value="" >Pilih Pekerjaan</option>
                  	 <?php for($i=0,$n=count($dataPekerjaan);$i<$n;$i++){ ?>
                     <option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"];?>" <?php if($dataPekerjaan[$i]["pekerjaan_id"]==$_POST["id_pekerjaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPekerjaan[$i]["pekerjaan_nama"];?></option>
            			   <?php } ?>	
                 </select>
                        </div>
                      </div>
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
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Status Pernikahan<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select class="form-control" name="id_status_perkawinan" id="id_status_perkawinan" onKeyDown="return tabOnEnter(this, event);">	
                	 <option value="" >Pilih Status Perkawinan</option>
                	 <?php for($i=0,$n=count($dataStatus);$i<$n;$i++){ ?>
                   <option value="<?php echo $dataStatus[$i]["status_perkawinan_id"];?>" <?php if($dataStatus[$i]["status_perkawinan_id"]==$_POST["id_status_perkawinan"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatus[$i]["status_perkawinan_nama"];?></option>
          			   <?php } ?>	
                 </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Nama Penanggung Jawab<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" class="form-control" name="cust_usr_penanggung_jawab" id="cust_usr_penanggung_jawab" size="30" maxlength="65" value="<?php echo $_POST["cust_usr_penanggung_jawab"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></font>
                     &nbsp;Status :
                     <select class="form-control" name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">	
                      	<option value="" >- Pilih Hubungan -</option>
                      	<?php for($i=0,$n=count($dataStatusPJ);$i<$n;$i++){ ?>
                        <option value="<?php echo $dataStatusPJ[$i]["status_pj_id"];?>" <?php if($dataStatusPJ[$i]["status_pj_id"]==$_POST["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataStatusPJ[$i]["status_pj_nama"];?></option>
                			  <?php } ?>	
                     </select>
                        </div>
                      </div>
                      <div hidden class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="telephone">Berat Lahir <span class="required">*</span>
                        </label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                          <input type="text" id="cust_berat_lahir" name="cust_berat_lahir" value="<?php echo $_POST["cust_berat_lahir"];?>" maxlength="13" data-validate-length-range="1,13" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
					  <input type="hidden" name="<? if ($_x_mode == "Edit") echo "btnUpdate"; else echo "btnSave"; ?>"  value="<? if ($_x_mode == "Edit") echo "Update"; else echo "Simpan"; ?>"/>
                      <?php //echo $view->RenderTextbox("cust_usr_id","cust_usr_id",$custUsrId);?>
					  <input type="text" name="cust_usr_id" id="cust_usr_id" value="<?php echo $custUsrId; ?>">
                      <?php echo $view->RenderHidden("x_mode","x_mode",$_x_mode);?>
                    
                  </div>
                </div>
              </div>
              <!-- END KOLOM 2 DATA PASIEN -->
                       
            </div>
            <!-- END ROW INPUT DATA 1 -->

            <!-- BEGIN CAM -->
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

				          
            <!-- END CAM -->
            
          </div>
        </div>
        <!-- /page content -->
        </form>
        <!-- footer content -->
		<?php require_once($LAY."footer.php"); ?>
        
        <!-- /footer content -->
      </div>
    </div>
<!-- validator -->
<script src="<?php echo $ROOT; ?>assets/vendors/validator/validator.js"></script>
<?php require_once($LAY."js.php"); ?>
  </body>
</html>
