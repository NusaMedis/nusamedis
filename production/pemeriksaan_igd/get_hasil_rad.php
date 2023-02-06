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
      $sql = "SELECT reg_id from klinik.klinik_registrasi a where a.reg_utama = '$_POST[id_reg]' and id_poli in (select poli_id from global.global_auth_poli where poli_tipe = 'R')";
      $rad =  $dataTable = $dtaccess->Fetch($sql);

    //  echo $rad['reg_id'];

      $sql = "SELECT COUNT(fol_id)  as jml from klinik.klinik_folio a where a.id_reg = '$rad[reg_id]' and a.fol_jenis_sem IS NULL" ;
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
      $sql = "select a.fol_id, a.tindakan_tanggal, a.tindakan_waktu, a.id_biaya, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah,resume_id,
         b.is_cito,
         d.rawat_tindakan_id,
         f.biaya_nama
      from klinik.klinik_folio a                                       
      left join klinik.klinik_biaya_tarif b on a.id_biaya_tarif = b.biaya_tarif_id
      left join radiologi.radiologi_resume c on a.fol_id = c.id_fol
      left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
      left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
      where a.id_reg = '$rad[reg_id]' order by tindakan_tanggal, tindakan_waktu desc OFFSET $start LIMIT $limit "; 
      $rs = $dtaccess->Execute($sql);
      $dataTable = $dtaccess->FetchAll($rs);

      $data = [];

      for($i=0; $i < count($dataTable) ; $i++){    

        if ($dataTable[$i]['is_cito'] == 'C') { $cito = " ( CITO )"; } else { $cito = ""; }

        array_push($data, [
          'fol_id'   => $dataTable[$i]['fol_id'],
          'id_reg'   => $dataTable[$i]['id_reg'],
          'resume_id'   => $dataTable[$i]['resume_id'],
          'id_biaya'   => $dataTable[$i]['id_biaya'],
          'tindakan_tanggal'   => format_date($dataTable[$i]['tindakan_tanggal']),
          'tindakan_waktu'   => $dataTable[$i]['tindakan_waktu'],
          'id_biaya_tarif'   => $dataTable[$i]['id_biaya_tarif'],
          'fol_lunas'   => $dataTable[$i]['fol_lunas'],
          'fol_jumlah'   => $dataTable[$i]['fol_jumlah'],
          'is_cito'   => $dataTable[$i]['is_cito'],
          'rawat_tindakan_id'   => $dataTable[$i]['rawat_tindakan_id'],
          'biaya_nama'   => $dataTable[$i]['biaya_nama'].$cito
        ]);
    }

    $hasil = array(
                'total' => $jmlData,
                'rows'  => $data
              );

    echo json_encode($hasil);
	
	} 
	
?>
	