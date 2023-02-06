<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once "sys/api.php";

  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $bpjs = new Bpjs();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $userLogin = $auth->GetUserData();                 


  $sql = "select no_rujukan FROM klinik.klinik_sep_rujukan";
  $sql .= " WHERE sep_rujukan_reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
  $rs = $dtaccess->Execute($sql);
  $exist = $dtaccess->Fetch($rs);
  if ( !empty($exist['no_rujukan']) ) {
    $bpjs->rujukanDestroy($exist['no_rujukan']);
  }
  
  #update local
  $sql = "DELETE from klinik.klinik_sep_rujukan";
  $sql .= " WHERE sep_rujukan_reg_id = ".QuoteValue(DPE_CHAR, $_GET["reg_id"]);
  $dtaccess->execute($sql);

  $rspn = array(
    "metaData" => [
      "code" => 200,
      "message" => "OK"
    ],
    "response" => $_GET["reg_id"]
  );

  echo json_encode($rspn);

  exit();        

?>