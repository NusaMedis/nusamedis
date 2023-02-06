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
     
     //$depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
	 
	 
	 //cari data registrasi lama
	  $sql = "select * from klinik.klinik_registrasi where reg_id = '$_POST[regId]'";
      $rs = $dtaccess->Execute($sql);
	  $dataPasien = $dtaccess->FetchAll($rs);
    
    //konfigurasi rumah sakit
   $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
   $rs = $dtaccess->Execute($sql);
   $konfigurasi = $dtaccess->Fetch($rs);
   
	 //cari poli tipe
		$sql = "select b.poli_tipe
		from global.global_auth_poli b
		where b.poli_id = '$_POST[poli_id]'";   
		$rs = $dtaccess->Execute($sql);
		$dataPoli = $dtaccess->FetchAll($rs);
    
    if ($dataPoli[0]["poli_tipe"]=='I')
    {
     $regStatus="I2";
     //$regTipeRawat = "I";
    }
    else if ($dataPoli[0]["poli_tipe"]=='G')
	  {
     $regStatus="G0";
     //$regTipeRawat = "G";
    }
    else if ($dataPoli[0]["poli_tipe"]!='G' || $dataPoli[0]["poli_tipe"]!='I')
	  {
     if ($dataPoli[0]["poli_tipe"]=='R')
     {
       $regStatus="R0";
       //$regTipeRawat = "G";
     }
     else if ($dataPoli[0]["poli_tipe"]=='L')
     {
       $regStatus="E0";
       //$regTipeRawat = "G";
     }
     else
     {
      $regStatus="E0";
      //$regTipeRawat = "J";
     }
      
    }
    
    
    
    
	  require_once('reg_kode_trans.php');
	 // FUNGSI ADD      
		 
            // ---- insert ke registrasi ----
          $dbTable = "klinik.klinik_registrasi";
     
          $dbField[0] = "reg_id";   // PK
          $dbField[1] = "reg_utama";   // PK
          $dbField[2] = "reg_status";
          $dbField[3] = "id_cust_usr";
          $dbField[4] = "reg_dokter_rujuk";
          $dbField[5] = "id_poli_asal";
          $dbField[6] = "id_poli";
          $dbField[7] = "reg_tipe_rawat";
          $dbField[8] = "reg_tanggal";
          $dbField[9] = "reg_waktu";
          $dbField[10] = "id_pembayaran";
		  $dbField[11] = "reg_who_update";
          $dbField[12] = "reg_when_update";
          $dbField[13] = "reg_jenis_pasien";
          $dbField[14] = "reg_status_pasien";
		  $dbField[15] = "reg_rujukan_id"; 
		  $dbField[16] = "reg_shift";
          $dbField[17] = "reg_tipe_layanan";
          $dbField[18] = "reg_sebab_sakit";
		  $dbField[19] = "id_dep";
		  $dbField[20] = "id_dokter";
		  $dbField[21] = "reg_diagnosa_awal";
		  $dbField[22] = "reg_tingkat_kegawatan";
      $dbField[23] = "reg_kode_trans";
		  
                
			$regId = $dtaccess->GetTransID();  
			$regUtama = $_POST['regId'];
			$regTipeRawat = $dataPasien[0]['reg_tipe_rawat'];
			
		  $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
		  $dbValue[1] = QuoteValue(DPE_CHAR,$regUtama);
          $dbValue[2] = QuoteValue(DPE_CHAR,$regStatus);
          $dbValue[3] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_cust_usr']);
          $dbValue[4] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_dokter']);
          $dbValue[5] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_poli']);
          $dbValue[6] = QuoteValue(DPE_CHAR,$_POST['poli_id']);
          $dbValue[7] = QuoteValue(DPE_CHAR,$regTipeRawat);
          $dbValue[8] = QuoteValue(DPE_DATE,date("Y-m-d"));
          $dbValue[9] = QuoteValue(DPE_DATE,date("H:i:s"));
          $dbValue[10] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_pembayaran']);
		  $dbValue[11] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[12] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[13] = QuoteValue(DPE_NUMERICKEY,$dataPasien[0]['reg_jenis_pasien']);
          $dbValue[14] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_status_pasien']);
          $dbValue[15] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_rujukan_id']);
          $dbValue[16] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_shift']);
          $dbValue[17] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_tipe_layanan']);
          $dbValue[18] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_sebab_sakit']);
		  $dbValue[19] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[20] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_dokter']);
          $dbValue[21] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_diagnosa_awal']);
          $dbValue[22] = QuoteValue(DPE_CHAR,$dataPasien[0]['reg_tingkat_kegawatan']);
          $dbValue[23] = QuoteValue(DPE_CHAR,$kodeTrans);

         
				//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
         $dtmodel->Insert() or die("insert  error");	
		 
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
		 
		 # insert klinik perawatan ----------------
		 
		 $sql_rawat = "select * from klinik.klinik_perawatan 
                     where id_reg = ".QuoteValue(DPE_CHAR,$regId)." 
                     and id_dep =".QuoteValue(DPE_CHAR,$depId);
            $dataPerawat= $dtaccess->Fetch($sql_rawat);
            
            if(!$dataPerawat){
 
              $dbTable = " klinik.klinik_perawatan";
              $dbField[0] = "rawat_id";   // PK
              $dbField[1] = "id_reg";
              $dbField[2] = "id_cust_usr";
              $dbField[3] = "rawat_waktu_kontrol";
              $dbField[4] = "rawat_tanggal";
              $dbField[5] = "rawat_flag"; 
              $dbField[6] = "rawat_flag_komen"; 
              $dbField[7] = "id_poli"; 
              $dbField[8] = "id_dep";
              $dbField[9] = "rawat_who_update";
              $dbField[10] = "rawat_waktu";         
              
              $_POST["rawat_id"] = $dtaccess->GetTransID();          
              $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
              $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
              $dbValue[2] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_cust_usr']);
              $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
              $dbValue[5] = QuoteValue(DPE_CHAR,$dataPoli[0]['poli_tipe']); 
              $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["poli_id"]); 
              $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
              $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
              $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
              $dtmodel->Insert() or die("insert  error");	
          
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          
            }

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
			
			    $waktuTungguId = $dtaccess->GetTransID(); 
			 
			    $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
		      $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPasien[0]['id_cust_usr']);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$regStatus);
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pasien di Registrasi");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST['poli_id']);
          
				//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         $dtmodel->Insert() or die("insert  error");	

         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
		 
         //header("Location: ".$_SERVER['HTTP_REFERER']."?autocetak=true&regId=".$regId."&cust_usr_id=".$_POST['cust_usr_id']);

         exit();        
?>