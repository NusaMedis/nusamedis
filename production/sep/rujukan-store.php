<?php
	// LIBRARY
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

	
	$param = $_GET["param"];
	$tglSep = $_GET['tglSep'];
	$bpjs = new Bpjs();

	$rs = $bpjs->rujukanStore($_POST);
	$dc = json_decode($rs);

	if ($dc->metaData->code == 200) {
		$dbTable = "klinik.klinik_sep_rujukan";

		$dbField[0] = "sep_rujukan_id";   // PK
		$dbField[1] = "sep_rujukan_reg_id";
		$dbField[2] = "sep_rujukan_cust_usr_id";
		$dbField[3] = "tgl_rujukan";
		$dbField[4] = "faskes_rujukan";
		$dbField[5] = "ppk_dirujuk_txt";
		$dbField[6] = "ppk_dirujuk";
		$dbField[7] = "jns_pelayanan";
		$dbField[8] = "catatan";
		$dbField[9] = "diag_rujukan_txt";
		$dbField[10] = "diag_rujukan";
		$dbField[11] = "tipe_rujukan";
		$dbField[12] = "poli_rujukan_txt";
		$dbField[13] = "sep_rujukan_no_sep";
		$dbField[14] = "created_at";
		$dbField[15] = "no_rujukan";
		$dbField[16]="tgl_di_rujukan";
		
		$id = $dtaccess->GetTransID(); 
		$dbValue[0] = QuoteValue(DPE_CHAR, $id);
		$dbValue[1] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
		$dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_cust_usr"]);
		$dbValue[3] = QuoteValue(DPE_CHAR, date_db($_POST["tgl_rujukan"]));
		$dbValue[4] = QuoteValue(DPE_CHAR, $_POST["rujukan_asalRujukan"]);
		$dbValue[5] = QuoteValue(DPE_CHAR, $_POST["ppk_dirujuk_txt"]);
		$dbValue[6] = QuoteValue(DPE_CHAR, $_POST["ppk_dirujuk"]);
		$dbValue[7] = QuoteValue(DPE_CHAR, $_POST["jns_pelayanan"]);
		$dbValue[8] = QuoteValue(DPE_CHAR, $_POST["catatan"]);
		$dbValue[9] = QuoteValue(DPE_CHAR, $_POST["diag_rujukan_txt"]);
		$dbValue[10] = QuoteValue(DPE_CHAR, $_POST["diag_rujukan"]);
		$dbValue[11] = QuoteValue(DPE_CHAR, $_POST["tipe_rujukan"]);
		$dbValue[12] = QuoteValue(DPE_CHAR, $_POST["poli_rujukan_txt"]);
		$dbValue[13] = QuoteValue(DPE_CHAR, $_POST["no_sep"]);
		$dbValue[14] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
		$dbValue[15] = QuoteValue(DPE_CHAR, $dc->response->rujukan->noRujukan);
		$dbValue[16]= QuoteValue(DPE_CHAR, date_db($_POST["tgl_di_rujukan"]));
		
		$dbKey[0] = 0; 

		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		$dtmodel->Insert(); 
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);
	}

	echo $rs;