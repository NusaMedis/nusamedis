<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
		 $sql = "select * from klinik.klinik_pemakaian_alkes a
     left join logistik.logistik_alkes b on a.id_alkes = b.alkes_id 
     where a.id_pembayaran =  '$_POST[id_pembayaran]'"; 
          
	
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	$data = [];
	
	for($i=0; $i < count($dataTable); $i++){    
        array_push($data, [
          'pemakaian_alkes_id'   => $dataTable[$i]['pemakaian_alkes_id'],
          'id_alkes'   => $dataTable[$i]['id_alkes'],
          'alkes_nama'   => $dataTable[$i]['alkes_nama'],
          'alkes_kode'   => $dataTable[$i]['alkes_kode'],
          'alkes_merk'   => $dataTable[$i]['alkes_merk'],
          'pemakaian_alkes_awal'   => $dataTable[$i]['pemakaian_alkes_waktu_awal'],
          'pemakaian_alkes_akhir'   => $dataTable[$i]['pemakaian_alkes_waktu_akhir'],
          'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
        ]);
    }
  echo json_encode($data);
	
	
?>
	