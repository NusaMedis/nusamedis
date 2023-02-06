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
          $dbTable = "klinik.klinik_waktu_tunggu";
     
          $dbField[0] = "klinik_waktu_tunggu_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "id_cust_usr";
          $dbField[3] = "klinik_waktu_tunggu_when_create";
          $dbField[4] = "klinik_waktu_tunggu_who_create";
          $dbField[5] = "klinik_waktu_tunggu_status";
          $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
          $dbField[7] = "id_poli";
          $dbField[8] = "id_waktu_tunggu_status";
			
			    $waktuTungguId = $dtaccess->GetTransID(); 
				$regId = $_POST['id_reg'];
			 
			    $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
		      $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,"G1");
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pasien Sampai di Poli");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,"G1");
          
				//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");	
		 
		 //update status di klinik registrasi
		 $sql = "update klinik.klinik_registrasi set reg_status = 'G1' where reg_id = ".
         QuoteValue(DPE_CHAR,$regId);
         $rs = $dtaccess->Execute($sql);
			//echo $sql;
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
         
         //header("location:".$_SERVER['HTTP_REFERER']);
         exit();        
	 
	
?>