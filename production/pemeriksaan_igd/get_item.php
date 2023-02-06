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

  $sql = "select item_id,item_harga_jual, item_nama, b.satuan_nama, c.stok_dep_saldo from logistik.logistik_stok_dep c 
          left join logistik.logistik_item a on c.id_item = a.item_id 
          left join logistik.logistik_item_satuan b on a.id_satuan_jual = b.satuan_id ";
  if (isset($_POST)) {
    $sql .= " WHERE c.id_gudang = '2' and c.stok_dep_saldo > 0 and item_racikan = 'n' and upper(item_nama) like ".QuoteValue(DPE_CHAR, strtoupper( '%'.$_GET['q'].'%') );
  }
  
  $sql .= "order by item_nama asc LIMIT 100";
   // echo $sql;
  $q = $dtaccess->fetchAll($sql);

  $rs = [];
  foreach ($q as $key => $value) {
    array_push($rs, [
      'item_id' => $value['item_id'],
      'item_nama' => $value['item_nama'],
      'satuan_nama' => $value['satuan_nama'],
      'stok_apotik' => $value['stok_dep_saldo'] * 1,
      'item_harga_jual' => $value['item_harga_jual'],
    ]);
    
  }

  echo json_encode($rs);

?>