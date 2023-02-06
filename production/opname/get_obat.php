<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."currency.php");
  require_once($LIB."expAJAX.php");
  require_once($LIB."tampilan.php");

  $dtaccess = new DataAccess();
  $enc = new textEncrypt();     
  $auth = new CAuth();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId(); 
  $userName = $auth->GetUserName();
  $data = array();

	if($_GET['item_nama']) $sql_where[] = " UPPER(b.item_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_GET['item_nama']."%"));
  
  if($sql_where) $sql_where = implode(" and ",$sql_where);       
  
  
  $sql = "select * from logistik.logistik_item a
  right join logistik.logistik_stok_dep b on b.id_item = a.item_id
  where item_racikan = 'n' and item_flag = 'M' and UPPER(item_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_GET['item_nama']."%"))." and b.id_gudang = ".QuoteValue(DPE_CHAR, $_GET['id_gudang'])." order by item_nama asc limit 10";
  $dataItem = $dtaccess->FetchAll($sql);
  for ($i = 0; $i < count($dataItem); $i++) {
    $sql = "select * from logistik.logistik_item b right join logistik.logistik_stok_item a on b.item_id = a.id_item where a.id_gudang = ".QuoteValue(DPE_CHAR,$_GET['id_gudang'])."
            and stok_item_create < ".QuoteValue(DPE_CHAR,date_db($_GET['tanggal']).' '.$_GET['waktu']);
    $sql .= " and item_id = ".QuoteValue(DPE_CHAR,$dataItem[$i]['item_id']);
    $sql .= " order by stok_item_create desc";
    $dataTable = $dtaccess->Fetch($sql);
    if($dataTable){
      $isi = [
      'item_id' => $dataTable['item_id'],
      'item_kode' => $dataTable['item_kode'],
      'item_nama' => $dataTable['item_nama'],
      'stok_dep_saldo' => number_format($dataTable['stok_item_saldo'],2,",","."),
      'stok_tercatat' => $dataTable['stok_item_saldo']
    ];
    }
    
    $data[] = $isi;
  }
 
  echo json_encode($data);
?>