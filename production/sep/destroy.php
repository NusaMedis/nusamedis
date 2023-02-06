<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");

  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $userLogin = $auth->GetUserData();                 
  require_once 'sys/sep-delete.php';

  // update no jaminan
  $sql = "DELETE from klinik.klinik_sep";
  $sql .= " WHERE no_sep = ".QuoteValue(DPE_CHAR, $_GET["no_sep"]);
  $dtaccess->execute($sql);

  exit();        

?>