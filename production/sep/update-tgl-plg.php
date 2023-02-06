<?php
  require_once 'sys/sep-update-tgl-plg.php';
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  // include_once($LIB."dateLib.php");
  // require_once($LIB."tampilan.php");
 
  //INISIALISAI AWAL LIBRARY
  // $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $userLogin = $auth->GetUserData();                 

  #update no jaminan
  $sql = "UPDATE klinik.klinik_registrasi SET reg_waktu_pulang =".QuoteValue(DPE_CHAR, $_POST["reg_waktu_pulang"]);
  $sql .= " , reg_tanggal_pulang =".QuoteValue(DPE_CHAR, date_db($_POST["reg_tanggal_pulang"]));
  $sql .= " WHERE reg_id = ".QuoteValue(DPE_CHAR, $_POST["id_reg"]);
  $dtaccess->execute($sql);

  $sql = "UPDATE klinik.klinik_sep SET reg_waktu_pulang =".QuoteValue(DPE_CHAR, $_POST["reg_waktu_pulang"]);
  $sql .= " , reg_tanggal_pulang =".QuoteValue(DPE_CHAR, date_db($_POST["reg_tanggal_pulang"]));
  $sql .= " WHERE sep_reg_id = ".QuoteValue(DPE_CHAR, $_POST["id_reg"]);
  $dtaccess->execute($sql);


  exit();        

?>