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

  $sql = "select * from apotik.apotik_jenis_racikan";
  if (isset($_GET)) {
    $sql .= " WHERE upper(jenis_racikan_nama) like ".QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
  }
  $sql .= " order by jenis_racikan_nama asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  //$data = [];
  //echo $sql;


    echo json_encode($dataTable);
?>