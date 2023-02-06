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

	
   //konfigurasi rumah sakit
   $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
   $rs = $dtaccess->Execute($sql);
   $konfigurasi = $dtaccess->Fetch($rs);
   # reg kode trans
   require_once('reg_kode_trans.php');
   //echo $kodeTrans;
   //die();
    
	 //DICEK APA SUDAH MELAKUKAN PEMERIKSAAN HARI INI
	 $sql = "select reg_id, id_pembayaran from klinik.klinik_registrasi where id_cust_usr=".QuoteValue(DPE_CHAR, $custUsrId)." and (reg_utama is null or reg_utama='') and reg_tanggal=".QuoteValue(DPE_DATE,date("Y-m-d"))." and reg_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["reg_jenis_pasien"]);
	 $rs = $dtaccess->Execute($sql);
   $AdaDataReg = $dtaccess->Fetch($rs);
   //die($sql);
		
	#menghitung umur 
	$sql = "select cust_usr_tanggal_lahir from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
	$rs = $dtaccess->Execute($sql);
	$pasien = $dtaccess->Fetch($rs);
	
	# Tanggal Lahir
	$birthday = $pasien['cust_usr_tanggal_lahir'];	
	$biday = new DateTime( $birthday );  // Convert Ke Date Time
	$today = new DateTime();
	$diff = $today->diff($biday);	
		//echo "Tanggal Lahir: ". date('d M Y', strtotime($birthday)) .'<br />';
		//echo "Umur: ". $diff->y ." Tahun ".$diff->m ." Bulan ". $diff->d ." hari";
		//die();
     
	 if ($AdaDataReg) //Jika Sudah pernah mendaftar hari ini maka ambil id_pembayaran dari reg yang lama
	 {
		$byrId=$AdaDataReg["id_pembayaran"];
		$regUtama=$AdaDataReg["reg_id"];
	 }
     else //Jika Belum pernah mendaftar hari ini maka ciptakan klinik_pembayaran
	 {
		$lunas = "n"; //karena belum dibayar
		  // Insert Biaya Pembayaran //                                  
		$dbTable = "klinik.klinik_pembayaran";
		$dbField[0] = "pembayaran_id";   // PK
		$dbField[1] = "pembayaran_create";
		$dbField[2] = "pembayaran_who_create";
		$dbField[3] = "pembayaran_tanggal";
		$dbField[4] = "id_cust_usr";
		$dbField[5] = "pembayaran_total";
		$dbField[6] = "id_dep";
		$dbField[7] = "pembayaran_flag";
		$dbField[8] = "pembayaran_yg_dibayar";
		
		$byrId = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHARKEY,$byrId);
		$dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
		$dbValue[2] = QuoteValue(DPE_CHAR,$userName);
		$dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
		$dbValue[4] = QuoteValue(DPE_CHAR,$custUsrId);
		$dbValue[5] = QuoteValue(DPE_NUMERIC,0);
		$dbValue[6] = QuoteValue(DPE_CHAR,$depId);
		$dbValue[7] = QuoteValue(DPE_CHAR,$lunas);
		$dbValue[8] = QuoteValue(DPE_NUMERIC,'0.00');

		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		 
		$dtmodel->Insert() or die("insert  error");
		 
		unset($dbField);
		unset($dtmodel);
		unset($dbValue);
		unset($dbKey);
		 
	 } // AKHIR JIKA BELUM MENDAFTAR HARI INI   
    
                    
      // ---- insert ke registrasi ----
      $dbTable = "klinik.klinik_registrasi";
 
      $dbField[0] = "reg_id";   // PK
      $dbField[1] = "reg_tanggal";
      $dbField[2] = "reg_waktu";
      $dbField[3] = "id_cust_usr";
      $dbField[4] = "reg_status";
      $dbField[5] = "reg_who_update";
      $dbField[6] = "reg_when_update";
      $dbField[7] = "reg_jenis_pasien";
      $dbField[8] = "reg_status_pasien";
      $dbField[9] = "reg_rujukan_id";         
      $dbField[10] = "reg_tipe_rawat";
      $dbField[11] = "id_poli";
      $dbField[12] = "id_dep";
      $dbField[13] = "reg_shift";
      $dbField[14] = "reg_tipe_layanan";
      $dbField[15] = "reg_sebab_sakit";
      $dbField[16] = "id_dokter";
      $dbField[17] = "reg_diagnosa_awal";
      $dbField[18] = "id_pembayaran";
      $dbField[19] = "reg_tingkat_kegawatan";
      $dbField[20] = "id_poli_asal";
      $dbField[21] = "reg_umur";
      $dbField[22] = "reg_umur_bulan";
      $dbField[23] = "reg_umur_hari";
      $dbField[24] = "reg_kelas";
		  $dbField[25] = "reg_prosedur_masuk";
		  $dbField[26] = "reg_tracer_registrasi";
		  $dbField[27] = "reg_tracer_barcode";
		  $dbField[28] = "reg_tracer_barcode_besar";
		  $dbField[29] = "reg_tracer_riwayat";
		  $dbField[30] = "reg_tracer";
		  $dbField[31] = "reg_kode_trans";
		  $dbField[32] = "reg_rujukan_det";
      $dbField[33] = "reg_no_antrian";
      $dbField[34] = "reg_tipe_paket";
  	  $dbField[35] = "reg_dokter_sender";
  	  $dbField[36] = "reg_tgl_sep";
  	  $dbField[37] = "hak_kelas_inap";
      $dbField[38] = "reg_tanggal_pulang";
      $dbField[39] = "reg_waktu_pulang";
      
	  
		if ($regUtama){
			if ($_POST["tipe_jkn"]) $dbField[40] = "reg_tipe_jkn";
			$dbField[41] = "reg_utama";
			if(!empty($_POST["reg_no_sep"])) $dbField[42] = "reg_no_sep";
		}else{
			if ($_POST["tipe_jkn"]) $dbField[40] = "reg_tipe_jkn";
			if(!empty($_POST["reg_no_sep"])) $dbField[41] = "reg_no_sep";
		}
			if(!$_POST['regId']){ $regId = $dtaccess->GetTransID(); } 
			 else{ $regId = $_POST['regId']; }
			 // tracer; n = cetak, y = tidak cetak
			if (!$_POST["cetak_reg"]) $_POST["cetak_reg"] = "y";
			if (!$_POST["cetak_barcode_k"]) $_POST["cetak_barcode_k"] = "y";
			if (!$_POST["cetak_barcode_b"]) $_POST["cetak_barcode_b"] = "y";
			if (!$_POST["cetak_ringkasan"]) $_POST["cetak_ringkasan"] = "y";
			
			if ($_POST["instalasi"]=="I") 
      {
        $status = "I2";
        #mencari poli
      	$sql = "select id_poli from klinik.klinik_kamar where kamar_id = ".QuoteValue(DPE_CHAR, $_POST["id_kamar"]);
      	$rs = $dtaccess->Execute($sql);
      	$poliIrna = $dtaccess->Fetch($rs);
        
        $_POST["klinik"] = $poliIrna["id_poli"];
      }
			elseif ($_POST["instalasi"]=="G") $status = "G0";
			//die($status);
			elseif ($_POST["instalasi"]!="G" || $_POST["instalasi"]!="I") {
        if ($poliTipe['poli_tipe']=='R') {
          $status = "R0";
        } else {
          $status = "E0";
        }
      };
			
      $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
      $dbValue[1] = QuoteValue(DPE_DATE,$_POST["reg_tanggal"]);
      $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
      $dbValue[3] = QuoteValue(DPE_CHAR,$custUsrId);
      $dbValue[4] = QuoteValue(DPE_CHAR,$status);
      $dbValue[5] = QuoteValue(DPE_CHAR,$userLogin["name"]);
      $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
      $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_id"]);
      $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["instalasi"]);
      $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
      $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["layanan"]);
      $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["reg_sebab_sakit"]);
	  $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["dokter"]);
      $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["reg_diagnosa_awal"]);
      $dbValue[18] = QuoteValue(DPE_CHAR,$byrId);
      $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["reg_tingkat_kegawatan"]);
      $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[21] = QuoteValue(DPE_NUMERIC,$diff->y);
      $dbValue[22] = QuoteValue(DPE_NUMERIC,$diff->m);
      $dbValue[23] = QuoteValue(DPE_NUMERIC,$diff->d);
      $dbValue[24] = QuoteValue(DPE_CHAR,NULL/* $konfigurasi["dep_konf_kelas_tarif_irj"] */); # ikut conf rs
		  $dbValue[25] = QuoteValue(DPE_CHAR,$_POST["reg_prosedur_masuk"]);
		  $dbValue[26] = QuoteValue(DPE_CHAR,$_POST["cetak_reg"]);
		  $dbValue[27] = QuoteValue(DPE_CHAR,$_POST["cetak_barcode_k"]);
		  $dbValue[28] = QuoteValue(DPE_CHAR,$_POST["cetak_barcode_b"]);
		  $dbValue[29] = QuoteValue(DPE_CHAR,$_POST["cetak_ringkasan"]);
		  $dbValue[30] = QuoteValue(DPE_CHAR,$_POST["cetak_tracer"]);
		  $dbValue[31] = QuoteValue(DPE_CHAR,$kodeTrans);
		  $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_det"]);
      $dbValue[33] = QuoteValue(DPE_CHAR,$noantri); 
      $dbValue[34] = QuoteValue(DPE_CHAR,$_POST["paket"]);
  	  $dbValue[35] = QuoteValue(DPE_CHAR,$_POST["reg_dokter_sender"]);   
  	  $dbValue[36] = QuoteValue(DPE_DATE,$_POST["reg_tgl_sep"]);
  	  $dbValue[37] = QuoteValue(DPE_CHAR,$_POST["hak_kelas_inap"]);
      $dbValue[38] = QuoteValue(DPE_DATE,$_POST["reg_tanggal"]);
      $dbValue[39] = QuoteValue(DPE_DATE,date("H:i:s"));
	    if ($regUtama){
			if ($_POST["tipe_jkn"]) $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]);
			$dbValue[41] = QuoteValue(DPE_CHAR,$regUtama);
			if(!empty($_POST["reg_no_sep"])) $dbValue[42] = QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]);
		}else{
			if ($_POST["tipe_jkn"]) $dbValue[40] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]);
			if(!empty($_POST["reg_no_sep"])) $dbValue[41] = QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]);
		}
		//print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         if ($_POST["btnUpdate"]) {
              $dtmodel->Insert() or die("insert  error");		
         } 
		 # sekalian update umur di global.global_customer_user
		 $umur =  $diff->y ."~".$diff->m ."~". $diff->d ;
		 $sql = "update global.global_customer_user";
		 $sql .= " set cust_usr_umur = ".QuoteValue(DPE_CHAR, $umur);
		 $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
		 $rs = $dtaccess->Execute($sql);
		 //echo "sukses update umur di global_customer_user =>";
		 
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
		 
            $sql_rawat = "select * from klinik.klinik_perawatan 
                     where id_reg = ".QuoteValue(DPE_CHAR,$regId);
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
              $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
              $dbValue[3] = QuoteValue(DPE_CHAR,date("H:i:s"));
              $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
              $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
              $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]); 
              $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
              $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
              $dbValue[10] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
              $dtmodel->Insert() or die("insert  error"); 
          
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);
          
            }
		 
		
          if (!$AdaDataReg){
          $sql = "update klinik.klinik_pembayaran set id_reg = ".QuoteValue(DPE_CHAR,$regId)."
                  where pembayaran_id = ".QuoteValue(DPE_CHAR,$byrId);
          $rs = $dtaccess->Execute($sql);
           if($_POST["paket"]){
                require_once("insert_biaya_paket.php");                   
           }else{
           if($konfigurasi["dep_konf_reg"]=='y'){
           	  if ($_POST["instalasi"] != "I")  {                 
				require_once("insert_biaya_registrasi.php");
			  }
           }
           if($konfigurasi["dep_konf_kons"]=='y'){
		  	  if ($_POST["instalasi"]!="I")  {
				require_once("insert_biaya_pemeriksaan.php");
			  }
           }

           }
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
          $dbField[8] = "id_waktu_tunggu_status";
			
			    $waktuTungguId = $dtaccess->GetTransID(); 
			 
			    $dbValue[0] = QuoteValue(DPE_CHAR,$waktuTungguId);
		      $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$custUsrId);
          $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
          $dbValue[4] = QuoteValue(DPE_CHAR,$userLogin["name"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$status);
          $dbValue[6] = QuoteValue(DPE_CHAR,"Pasien di Registrasi");
          $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$status);
          
				
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

         if ($_POST["btnUpdate"]) {
             $dtmodel->Insert() or die("insert  error");	
         } 
         //print_r($dbValue); die();
         unset($dtmodel);
         unset($dbField);
         unset($dbValue);
         unset($dbKey);
        
?>
