<?php
	# LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
	# INISIALISAI AWAL LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
   	 $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
	 $depId = $auth->GetDepId();
	 $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();

		
	if (!empty($_POST["usr_id"]) && !empty($_POST["reg_id"])) {                               
          $dbTable = "klinik.klinik_registrasi";
     
          $dbField[0] = "reg_id";   // PK
		  $dbField[1] = "id_dokter";
			
		  $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["reg_id"]);
		  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["usr_id"]);
				//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		 $dtmodel->Update() or die("update  error");	
         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);


        # cari folio berdasar registrasi
         $sql = "SELECT fol_id from klinik.klinik_folio where id_reg =".QuoteValue(DPE_CHAR, $_POST["reg_id"]);
         $fol = $dtaccess->FetchAll($sql);
        // print_r($fol);

        # update pelaksana
         for ($i = 0; $i < count($fol); $i++){
          //   $sql = "UPDATE klinik.klinik_folio_pelaksana SET id_usr = ".QuoteValue(DPE_CHAR, $_POST["usr_id"]);
          //   $sql .= " WHERE id_fol = ".QuoteValue(DPE_CHAR,$fol[$i]["fol_id"]);
           // $sql .= " AND fol_pelaksana_tipe = '1'";
            //$dtaccess->Execute($sql);
            //echo $sql;
         }

         echo json_encode(array('success'=>$_POST["reg_id"]));
     }
