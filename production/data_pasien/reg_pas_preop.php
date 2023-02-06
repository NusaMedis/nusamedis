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

  /* INSERT PREOP */
  $dbTable = "klinik.klinik_preop";

  $dbField[0] = "preop_id";
  $dbField[1] = "id_reg";
  $dbField[2] = "preop_waktu";
  $dbField[3] = "preop_tanggal_jadwal";

  $preopId = $dtaccess->GetTransID();

  $dbValue[0] = QuoteValue(DPE_CHAR,$preopId);
  $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
  $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
  $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  $dtmodel->Insert() or die("insert  error");

  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dtmodel);
  /* INSERT PREOP */
?>