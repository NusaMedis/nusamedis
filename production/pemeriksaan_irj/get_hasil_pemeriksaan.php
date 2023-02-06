<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();

    #paging
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $limit = isset($_POST['rows']) ? intval($_POST['rows']) : 10;

    //echo $page.$limit;
		  	
    if( isset($_POST['id_reg']) ){
      #get reg radiologi
      $sql = "SELECT a.id_cust_usr from klinik.klinik_registrasi a where 
      a.reg_id = '$_POST[id_reg]'";
      $pemeriksaan =  $dataTable = $dtaccess->Fetch($sql);

    //  echo $pemeriksaan['reg_id'];

      $sql = "SELECT COUNT(rawat_id)  as jml from klinik.klinik_perawatan a where 
      a.id_cust_usr = '$pemeriksaan[id_cust_usr]'" ;
      $rs =  $dataTable = $dtaccess->Fetch($sql);

      if($rs) {
        $jmlData = $rs['jml'];
      } else {
        $jmlData = 0;
      }       
   
      # buat paging atau halaman
      if( $jmlData > 0 && $limit > 0) { 
        $total_pages = ceil($jmlData/$limit); 
      } else { 
        $total_pages = 0; 
      }

      if ($page > $total_pages) $page = $total_pages;
        $start = $limit*$page - $limit;
      if($start < 0) $start = 0;

    #ambil data
      $sql = "select a.rawat_id,a.rawat_tanggal, a.rawat_waktu_kontrol, a.rawat_anamnesa, a.rawat_pemeriksaan_fisik, a.rawat_penunjang,
              a.rawat_kasus_keterangan,a.id_reg
      from klinik.klinik_perawatan a
      where a.id_cust_usr = '$pemeriksaan[id_cust_usr]' order by rawat_tanggal desc, rawat_waktu_kontrol desc OFFSET $start LIMIT $limit "; 
      $rs = $dtaccess->Execute($sql);
      $dataTable = $dtaccess->FetchAll($rs);
	  
	  //echo json_encode($dataTable); die;

      $data = [];

      for($i=0; $i < count($dataTable) ; $i++){    

        if ($dataTable[$i]['is_cito'] == 'C') { $cito = " ( CITO )"; } else { $cito = ""; }

        array_push($data, [
          'rawat_id'   => $dataTable[$i]['rawat_id'],
          'rawat_tanggal'   => format_date($dataTable[$i]['rawat_tanggal']),
          'rawat_waktu_kontrol'   => $dataTable[$i]['rawat_waktu_kontrol'],
          'rawat_anamnesa'   => $dataTable[$i]['rawat_anamnesa'],
          'rawat_pemeriksaan_fisik'   => $dataTable[$i]['rawat_pemeriksaan_fisik'],
          'rawat_penunjang'   => $dataTable[$i]['rawat_penunjang'],
          'rawat_kasus_keterangan'   => $dataTable[$i]['rawat_kasus_keterangan'],
          'id_reg'   => $dataTable[$i]['id_reg']
        ]);
    }

    $hasil = array(
                'total' => $jmlData,
                'rows'  => $data
              );

    echo json_encode($hasil);
	
	} 
	
?>
	