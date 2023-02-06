<?php 
	
	
	require_once("../penghubung.inc.php");
	require_once($LIB."login.php");
	require_once($LIB."encrypt.php");
	require_once($LIB."datamodel.php");
	require_once($LIB."dateLib.php");
	require_once($LIB."tampilan.php");
	require_once "sys/api.php";

	//INISIALISAI AWAL LIBRARY
	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
	$enc = new textEncrypt();
	$auth = new CAuth();
	$depId = $auth->GetDepId();
	$userName = $auth->GetUserName();
	$userId = $auth->GetUserId();
	$userLogin = $auth->GetUserData(); 

	$sep_id = $_POST["sep_id"];
	$nama = $_POST['nama'];
	$noka = $_POST["noka"];
	$jnspelayanan = $_POST['jnspelayanan'];
	$keterangan = $_POST['keterangan'];
	$tglSep = $_POST['tglSep'];
	$bpjs = new Bpjs();
	$rs= $bpjs->AprovalSEP($sep_id, $nama,$noka,$jnspelayanan,$keterangan,$tglSep);


		$dc = json_decode($rs);

	if ($dc->metaData->code == 200) {
		 $dbTable = "klinik.klinik_sep_pengajuan";

		  $dbField[0] = "sep_pengajuan_id";   // PK
		  $dbField[1] = "noka";
		  $dbField[2] = "tglsep";
		  $dbField[3] = "jnspelayanan";
		  $dbField[4] = "keterangan";
		  $dbField[5] = "username";
		  $dbField[6] = "isaproval";
		 
		  $dbField[7] = "created";
		  $dbField[8] = "namapeserta";
		
		  $dbField[9] = "ispengajuan";
		  

		 

		  
		  $dbValue[0] = QuoteValue(DPE_CHAR, $sep_id);
		  $dbValue[1] = QuoteValue(DPE_CHAR, $noka);
		  $dbValue[2] = QuoteValue(DPE_CHAR, $tglSep);
		  $dbValue[3] = QuoteValue(DPE_CHAR, $jnspelayanan);
		  $dbValue[4] = QuoteValue(DPE_CHAR,$keterangan );
		  $dbValue[5] = QuoteValue(DPE_CHAR, $userName);
		  $dbValue[6] = QuoteValue(DPE_CHAR, 'Y');
		  $dbValue[7] = QuoteValue(DPE_DATE, date('Y-m-d H:i:s'));
		  $dbValue[8] = QuoteValue(DPE_CHAR, $nama);
		
		  $dbValue[9] = QuoteValue(DPE_CHAR, ' ');
		 
		  $dbKey[0] = 0; 
		  
		  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		  $dtmodel->Update(); 
		  unset($dtmodel);
		  unset($dbField);
		  unset($dbValue);
		  unset($dbKey);



		   $dbTable = "klinik.klinik_sep_history";

		  $dbField[0] = "sep_history_id";   // PK
		  $dbField[1] = "sep_noka_history";
		  $dbField[2] = "sep_tglsep_history";
		  $dbField[3] = "created_history";
		  $dbField[4] = "sep_user_history";
		  $dbField[5] = "sep_history_ket";
		  $dbField[6] = "sep_history_status";
	
		  

		 

		  $sep_id = $dtaccess->GetTransID(); 
		  $dbValue[0] = QuoteValue(DPE_CHAR, $sep_id);
		  $dbValue[1] = QuoteValue(DPE_CHAR, $noka);
		  $dbValue[2] = QuoteValue(DPE_CHAR, $tglSep);
		  $dbValue[3] = QuoteValue(DPE_CHAR,date('Y-m-d H:i:s'));
		  $dbValue[4] = QuoteValue(DPE_CHAR,$userName );
		  $dbValue[5] = QuoteValue(DPE_CHAR, $keterangan);
		  $dbValue[6] = QuoteValue(DPE_CHAR, 'A');
		 
		 
		  $dbKey[0] = 0; 
		  
		  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		  $dtmodel->Insert(); 
		  unset($dtmodel);
		  unset($dbField);
		  unset($dbValue);
		  unset($dbKey);

	}
		echo $rs;
?>