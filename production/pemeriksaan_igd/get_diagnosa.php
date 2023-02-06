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

  $sql = "SELECT poli_id from global.global_auth_poli where form_asmed = 'obgyn'";
  $poli = $dtaccess->Fetch($sql);

  $sql = 'SELECT * from klinik.klinik_diagnosa';
  if (isset($_GET)) {
    $sql .= ' WHERE (upper(diagnosa_nomor) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%') );
    $sql .= ' OR upper(diagnosa_nama) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' ));
     $sql .= ' OR upper(diagnosa_short_desc) like '.QuoteValue(DPE_CHAR, strtoupper( '%%'.$_GET['q'].'%%' )).') ';
     $sql .= ' and id_poli = '.QuoteValue(DPE_CHAR, $poli['poli_id'] );
  }
  $sql .= ' order by diagnosa_nomor_tanpa_titik LIMIT 100';
  // echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'diagnosa_id' => $value['diagnosa_id'],
      'diagnosa_nomor' => $value['diagnosa_nomor'],
      'diagnosa_nama' => $value['diagnosa_nama'],
      'diagnosa_deskripsi' => $value['diagnosa_short_desc'],
    ]);
    
  }

  echo json_encode($rs);

?>