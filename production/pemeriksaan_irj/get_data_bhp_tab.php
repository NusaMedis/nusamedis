<?php
  // LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");

  //INISIALISASI LIBRARY
  $dtaccess = new DataAccess();
		  	
  if( isset($_POST['id_reg']) && $_POST['id_reg'] !='' ){
    $sql = "select id_cust_usr from klinik.klinik_registrasi   where reg_id='$_POST[id_reg]' ";
    $datacust = $dtaccess->Fetch($sql);
    
    $sql =" select id_reg ,fol_id  from klinik.klinik_registrasi a
             left join klinik.klinik_folio b on b.id_reg = a.reg_id  where  
             reg_status = 'A7' and a.id_cust_usr='$datacust[id_cust_usr]'";
    $datareg = $dtaccess->Fetch($sql);
    if($datareg['fol_id']){
    for ($i=0; $i < count($datareg) ; $i++) { 
      $sql="select c.item_nama as nama_obat, a.* ,satuan_nama
      from apotik.apotik_penjualan_detail a
      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
      left join logistik.logistik_item c on a.id_item = c.item_id
      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
      where b.id_fol=".QuoteValue(DPE_CHAR,$datareg['fol_id']);
      $dataTable = $dtaccess->FetchAll($sql);
    }
  }

    $data = [];
	
    for($i=0; $i < count($dataTable); $i++){    
      array_push($data, [
        'id_reg'   => $datareg['id_reg'],
        'item_nama'   => $dataTable[$i]['item_nama'],
        'item_id'   => $dataTable[$i]['id_item'],
        'fol_jumlah'   => $dataTable[$i]['penjualan_detail_jumlah'],
        'satuan_nama'   => $dataTable[$i]['satuan_nama'],
        'biaya'   => number_format($dataTable[$i]['penjualan_detail_total'],0,',','.'),
      ]);
    }
 
    echo json_encode($data);
	} 
?>