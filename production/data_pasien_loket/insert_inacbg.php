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

      // ---- insert ke registrasi ----
      $dbTable = "klinik.klinik_inacbg";
      $dbField[0] = "inacbg_id";   // PK
      $dbField[1] = "inacbg_pasien_nama";
      $dbField[2] = "id_cust_usr";
      $dbField[3] = "id_pembayaran";
      $dbField[4] = "id_reg";
      $dbField[5] = "inacbg_check";
      $dbField[6] = "inacbg_when_update";
      $dbField[7] = "inacbg_tanggal_masuk";
      $dbField[8] = "inacbg_waktu_masuk";
      
		  $inacbg_id = $dtaccess->GetTransID();     
      $dbValue[0] = QuoteValue(DPE_CHAR,$inacbg_id);
      $dbValue[1] = QuoteValue(DPE_CHAR,strtoupper($CustUsrNama));
      $dbValue[2] = QuoteValue(DPE_CHAR,$custUsrId);
      $dbValue[3] = QuoteValue(DPE_CHAR,$byrId);
      $dbValue[4] = QuoteValue(DPE_CHAR,$regId);
      $dbValue[5] = QuoteValue(DPE_CHAR,'k');
      $dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[7] = QuoteValue(DPE_DATE,date_db($_POST["reg_tanggal"]));
      $dbValue[8] = QuoteValue(DPE_DATE,date('H:i:s'));
          
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      $dtmodel->Insert() or die("insert  error");
 
      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);

?>
