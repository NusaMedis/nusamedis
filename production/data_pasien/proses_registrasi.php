<?php    
     ##################	NOTE #####################
	 ## insert cust usr kode sebagai primary	##
	 ## update untuk tambah berdasar primary	##
	 #############################################
	
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
	 $backPage = "registrasi_pasien.php?usr_id="; 		  
  
//jika edit jangan gunakan data paien kode .php
  if(!$_GET['id']) {require_once("data_pasien_kode.php");}
//jika no rm manual jangan gunakan data pasien kode
  if (!$_POST["cust_usr_kode"]) $_POST["cust_usr_kode"] = $_POST["kode_pasien"];
  if (!$_POST["cust_usr_kode_tampilan"]) $_POST["cust_usr_kode_tampilan"] = $_POST["kode_pasien_tampilan"];
	
   //die();
    // FUNGSI ADD dan DELETE
    if ($_POST["btn"] == "btnLanjut") 
    {                               
	if($_POST['reg_status_pasien'] == "B") {
		//$sql = "select * from global.global_lokasi where lokasi_kode like '".$_POST["kel"]."'";
         //$lokasidaerah = $dtaccess->Fetch($sql);
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
         $dbField[22] = "cust_usr_foto";
		 $dbField[23] = "cust_usr_kode";
		 $dbField[24] = "cust_usr_kode_tampilan";
		 $dbField[25] = "cust_usr_penanggung_jawab";
		 $dbField[26] = "cust_usr_penanggung_jawab_status";
		 
         if(!$custUsrId) $custUsrId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHAR,$custUsrId);         
         $dbValue[1] = QuoteValue(DPE_CHAR,str_replace("'", "*", $_POST["cust_usr_nama"]));
         $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_tempat_lahir"]);
         $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"]));
         $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["tahun"]."~".$_POST["bulan"]."~".$_POST["hari"]);
         $dbValue[5] = QuoteValue(DPE_CHAR,str_replace("'", "*", $_POST["cust_usr_alamat"]));
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
         $dbValue[17] = QuoteValue(DPE_CHAR,"");//$lokasidaerah["lokasi_kecamatan"]);
         $dbValue[18] = QuoteValue(DPE_CHAR,"");//$lokasidaerah["lokasi_kelurahan"]);
		 $dbValue[19] = QuoteValue(DPE_CHAR,"");//$lokasidaerah["lokasi_propinsi"]);
	  	 $dbValue[20] = QuoteValue(DPE_CHAR,"");//$lokasidaerah["lokasi_kabupatenkota"]);          
         $dbValue[21] = QuoteValue(DPE_CHAR,"");//$lokasidaerah["lokasi_id"]);
         $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["cust_usr_foto"]);
         $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
         $dbValue[24] = QuoteValue(DPE_CHAR,$_POST["cust_usr_kode_tampilan"]);
         $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab"]);
         $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cust_usr_penanggung_jawab_status"]);
		 
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		   
         //if ($_POST["btnSave"]) {
              $dtmodel->Insert() or die("insert  error");	
         
         //} else if ($_POST["btnUpdate"]) {
           //   $dtmodel->Update() or die("update  error");	
         //} 
         
		 // die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
	}	 
		 include("reg_pas_lama.php");
		 //die();
		 
         header("location:".$backPage.$_POST["cust_usr_kode"]);
        // echo "tes link kembali ".$backPage.$custUsrId;
         exit();        
     }
?>