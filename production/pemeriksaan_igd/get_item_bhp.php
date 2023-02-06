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

  $sql = "select item_id, item_nama, b.satuan_nama from logistik.logistik_item a";
  $sql .= " left join logistik.logistik_item_satuan b on a.id_satuan_jual = b.satuan_id ";
  if (isset($_POST)) {
    $sql .= " WHERE item_racikan = 'n' and item_flag = 'B' and upper(item_nama) like ".QuoteValue(DPE_CHAR, strtoupper( '%%'.$_POST['q'].'%%') );
  }
  
  $sql .= "order by item_nama asc LIMIT 100";
   //echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'item_id' => $value['item_id'],
      'item_nama' => $value['item_nama'],
      'satuan_nama' => $value['satuan_nama'],
    ]);
    
  }

  echo json_encode($rs);

?>