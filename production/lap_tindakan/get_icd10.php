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

  $sql = 'SELECT icd_nomor, icd_nama, icd_id,icd_deskripsi from klinik.klinik_icd';
  if (isset($_GET['q'])) {
    $sql .= ' WHERE upper(icd_nomor) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
    $sql .= ' OR upper(icd_nama) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
    $sql .= ' OR upper(icd_deskripsi) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
  }
  $sql .= ' LIMIT 100';
  // echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'icd_id' => $value['icd_id'],
      'icd_nomor' => $value['icd_nomor'],
      'icd_nama' => $value['icd_nama'],
       'icd_deskripsi' => $value['icd_deskripsi'],
    ]);
    
  }

  echo json_encode($rs);

?>