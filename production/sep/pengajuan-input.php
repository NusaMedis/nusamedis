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
  $userLogin = $auth->GetUserData();                 

  #update no jaminan + telp
  // $sql = "UPDATE global.global_customer_user SET cust_usr_no_jaminan = ".QuoteValue(DPE_CHAR, $_POST["noKartu"]);
  // $sql .= ", cust_usr_no_hp =".QuoteValue(DPE_CHAR, $_POST["cust_usr_no_hp"]);
  // $sql .= " WHERE cust_usr_id = ".QuoteValue(DPE_CHAR, $_POST["id_cust_usr"]);
  // $dtaccess->execute($sql);

  #insert klinik sep      
  $dbTable = "klinik.klinik_sep_pengajuan";

  $dbField[0] = "sep_pengajuan_id";   // PK
  $dbField[1] = "noka";
  $dbField[2] = "tgl_sep";
  $dbField[3] = "jns_pelayanan";
  $dbField[4] = "keterangan";
  $dbField[5] = "user";
  $dbField[6] = "ispengajuan";
 
  $dbField[7] = "created";
  $dbField[8] = "namapeserta";
  $dbField[9] = "pilihan";
  

  if(count($_POST["laka_penjamin"]) > 1) { 
   $penjamin = implode(',', $_POST["laka_penjamin"]); 
  } else {
    $penjamin = $_POST["laka_penjamin"];
  }

  $sep_id = $dtaccess->GetTransID(); 
  $dbValue[0] = QuoteValue(DPE_CHAR, $sep_id);
  $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["noKartu"]);
  $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["nama_peserta"]);
  $dbValue[3] = QuoteValue(DPE_CHAR, $_POST["tglSep"]);
  $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["jnsPelayanan"]);
  $dbValue[5] = QuoteValue(DPE_CHAR, $userName);
  $dbValue[6] = QuoteValue(DPE_CHAR, "Y");
  $dbValue[7] = QuoteValue(DPE_CHAR, date('Y-m-d');
  $dbValue[8] = QuoteValue(DPE_CHAR, $_POST["nama_peserta"]);
  $dbValue[9] = QuoteValue(DPE_CHAR, $_POST["pilih"]);
 
  $dbKey[0] = 0; 
  
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  $dtmodel->Insert(); 
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

  $response['success'] = $_POST['id_reg'];

  echo json_encode($response);

  exit();        

?>