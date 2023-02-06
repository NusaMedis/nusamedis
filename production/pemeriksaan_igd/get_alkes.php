<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."login.php");

  //INISIALISASI LIBRARY
  $dtaccess = new DataAccess();
  $tglSekarang = date("Y-m-d");
  $auth = new CAuth();
  $userId = $auth->GetUserId();

  $id_poli = $_POST['id_poli'];
  $sql = "select * from logistik.logistik_alkes a WHERE 1=1  ";
  $sql .= "and id_poli = '$id_poli'";
  if (isset($_POST)) {
    $sql .= " and upper(alkes_nama) like ".QuoteValue(DPE_CHAR, strtoupper( '%%'.$_POST['q'].'%%') );
  }
  
  $sql .= "order by alkes_nama asc LIMIT 100";
   //echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'alkes_id' => $value['alkes_id'],
      'alkes_nama' => $value['alkes_nama'],
      'alkes_kode' => $value['alkes_kode'],
      'alkes_merk' => $value['alkes_merk'],
    ]);
    
  }

  echo json_encode($rs);

?>