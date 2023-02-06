<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."login.php");
     require_once($LIB."currency.php");
   
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     
     $depId = $auth->GetDepId();
     
     
      $data = [];

  $sql = "select * from apotik.apotik_aturan_pakai";
  if (isset($_GET)) {
    $sql .= " WHERE upper(aturan_pakai_nama) like ".QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
  }
  $sql .= " order by no_urut";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  //$data = [];
  //echo $sql;


    echo json_encode($dataTable);
?>