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

  $sql = 'SELECT * from klinik.klinik_procedure';
  if (isset($_GET)) {
    $sql .= ' WHERE upper(procedure_nomor) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
    $sql .= ' OR upper(procedure_nama) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
    $sql .= ' OR upper(procedure_short_desc) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
  }
  $sql .= ' order by procedure_nomor_tanpa_titik LIMIT 100';
  // echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'procedure_id' => $value['procedure_id'],
      'procedure_nomor' => $value['procedure_nomor'],
      'procedure_nama' => $value['procedure_nama'],
      'procedure_short_desc' => $value['procedure_short_desc'],
      'procedure_nomor_tanpa_titik' => $value['procedure_nomor_tanpa_titik'],
    ]);
    
  }

  echo json_encode($rs);

?>