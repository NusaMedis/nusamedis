<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
     //INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
   	 $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
			
			
		 // ---- insert ke klinik waktu tunggu ----
          $dbTable = "klinik.klinik_registrasi";
     
          $dbField[0] = "reg_id";   // PK
          $dbField[1] = "reg_diagnosa_awal";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "reg_dokter_sender";
          $dbField[4] = "reg_tgl_sep";
          $dbField[5] = "hak_kelas_inap";
          $dbField[6] = "reg_tgl_rujukan";
          $dbField[7] = "reg_no_rujukan";
          $dbField[8] = "reg_ppk_rujukan";
          $dbField[9] = "catatan_bpjs";
          $dbField[10] = "reg_jenis_layanan";
          $dbField[11] = "reg_no_sep";
          $dbField[12] = "reg_tipe_jkn";
		  
			 
		  $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
		  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['reg_diagnosa_awal']);
		  $dbValue[2] = QuoteValue(DPE_CHAR,$custUsrId);
          $dbValue[3] = QuoteValue(DPE_CHAR,$_POST['reg_dokter_sender']);
          $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[5] = QuoteValue(DPE_CHAR,$_POST['hak_kelas_inap']);
          $dbValue[6] = QuoteValue(DPE_DATE,date_db($_POST['reg_tgl_rujukan']));
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["reg_no_rujukan"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST['reg_ppk_rujukan']);
          $dbValue[9] = QuoteValue(DPE_CHAR,$_POST['catatan_bpjs']);
          $dbValue[10] = QuoteValue(DPE_CHAR,$_POST['reg_jenis_layanan']);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST['reg_no_sep']);
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]);
		
          
				
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		 $dtmodel->Update() or die("update error");    
         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
		 
		 // ---- insert ke klinik waktu tunggu ----
          $dbTable = "global.global_customer_user";
     
          $dbField[0] = "cust_usr_id";   // PK
          $dbField[1] = "cust_usr_jkn_asal";
          $dbField[2] = "cust_usr_no_jaminan";
		  
		  $dbValue[0] = QuoteValue(DPE_CHAR,$_POST['cust_usr_id']);
		  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['cust_usr_jkn_asal']);
		  $dbValue[2] = QuoteValue(DPE_CHAR,$_POST['cust_usr_no_jaminan']);
				
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		// print_r($dtmodel->Update());
		 $dtmodel->Update() or die("update error");    
         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
        
?>
