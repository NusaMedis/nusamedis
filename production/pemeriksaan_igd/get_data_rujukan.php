<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $tglSekarang = date("Y-m-d");

	 //if( isset($_POST['id_reg']) ){
		$sql = "select a.reg_id, a.id_poli as poli_id, b.poli_nama, a.reg_tanggal,a.reg_waktu, reg_keterangan from klinik.klinik_registrasi a
				left join global.global_auth_poli b on a.id_poli = b.poli_id
				where id_poli_asal IS NOT NULL
				and a.reg_utama = '$_POST[id_reg]' 
				";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
    
		$data=array();
    
		for($i=0; $i < count($dataTable); $i++)
    {    
      	$row = array(
          'reg_id'   => $dataTable[$i]['reg_id'],
          'poli_id'   => $dataTable[$i]['poli_id'],
          'poli_nama'   => $dataTable[$i]['poli_nama'],
          'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
          'reg_waktu'   => $dataTable[$i]['reg_waktu'],
          'reg_keterangan'   => $dataTable[$i]['reg_keterangan'],
        );
        $data[]=$row;
    }

    echo json_encode($data);

?>