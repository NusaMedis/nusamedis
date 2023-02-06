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

  $sql = 'SELECT * from klinik.klinik_icd9';
  if (isset($_GET)) {
    $sql .= ' WHERE upper(icd9_nomor) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
    $sql .= ' OR upper(icd9_nama) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
    $sql .= ' OR upper(icd9_short_desc) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
  }
  $sql .= 'LIMIT 100';
  // echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'icd9_id' => $value['icd9_id'],
      'icd9_nomor' => $value['icd9_nomor'],
      'icd9_nama' => $value['icd9_nama'],
      'icd9_short_desc' => $value['icd9_short_desc'],
    ]);
    
  }

  echo json_encode($rs);

?>