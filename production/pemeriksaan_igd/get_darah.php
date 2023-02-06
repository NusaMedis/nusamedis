<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
		 $sql = "select a.fol_id, a.id_biaya,a.tindakan_tanggal,a.tindakan_waktu, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah, a.fol_dokter_instruksi as dokter_instruksi, a.fol_pelaksana as pelaksana, 
				 b.usr_name as pelaksana_nama,
				 c.usr_name as dokter_instruksi_nama,
				 d.rawat_tindakan_keterangan, d.rawat_tindakan_keterangan_2,d.rawat_tindakan_id, rawat_tindakan_gol_darah,
				 f.biaya_nama
			from klinik.klinik_folio a
			left join global.global_auth_user b on a.fol_pelaksana = b.usr_id
			left join global.global_auth_user c on a.fol_dokter_instruksi = c.usr_id
			left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
			left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
			
			where a.id_reg = '$_POST[id_reg]' and a.fol_jenis_sem = 'LD'"; 
      //where a.id_pembayaran = '$_POST[id_pembayaran]'"; 
       //echo $sql;
		  $sql .=" order by a.tindakan_tanggal, tindakan_waktu desc";     
	//echo $sql;
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	$data = [];
	
	for($i=0; $i < count($dataTable); $i++){    
        array_push($data, [
          'fol_id'   => $dataTable[$i]['fol_id'],
          'id_biaya'   => $dataTable[$i]['id_biaya'],
          'tindakan_tanggal'   => format_date($dataTable[$i]['tindakan_tanggal']),
          'tindakan_waktu'   => $dataTable[$i]['tindakan_waktu'],
          'biaya_tarif_id'   => $dataTable[$i]['id_biaya_tarif'],
          'id_reg'   => $dataTable[$i]['id_reg'],
          'fol_lunas'   => $dataTable[$i]['fol_lunas'],
          'no_kantong'   => $dataTable[$i]['rawat_tindakan_keterangan'],
          'rhesus'   => $dataTable[$i]['rawat_tindakan_keterangan_2'],
          'dokter'   => $dataTable[$i]['dokter'],
          'fol_jumlah'   => $dataTable[$i]['fol_jumlah'],
          'dokter_instruksi'   => $dataTable[$i]['dokter_instruksi'],
          'pelaksana'   => $dataTable[$i]['pelaksana'],
          'pelaksana_nama'   => $dataTable[$i]['pelaksana_nama'],
          'dokter_instruksi_nama'   => $dataTable[$i]['dokter_instruksi_nama'],
          'rawat_tindakan_id'   => $dataTable[$i]['rawat_tindakan_id'],
          'biaya_nama'   => $dataTable[$i]['biaya_nama'],
          'gol_darah'   => $dataTable[$i]['rawat_tindakan_gol_darah'],
        ]);
    }
  echo json_encode($data);
?>
	

<?php
     // LIBRARY
    // require_once("../penghubung.inc.php");
     //require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
    // $dtaccess = new DataAccess();
		  	
//     if( isset($_POST['id_reg']) ){
		 //$sql = "select a.fol_id, a.id_biaya, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah, a.fol_dokter_instruksi as dokter_instruksi, a.fol_pelaksana as pelaksana,
				/* b.usr_name as pelaksana_nama,
				 c.usr_name as dokter_instruksi_nama,
				 d.rawat_tindakan_keterangan as no_kantong, d.rawat_tindakan_keterangan_2 as rhesus,d.rawat_tindakan_id,
				 f.biaya_nama
			from klinik.klinik_folio a
			left join global.global_auth_user b on a.fol_pelaksana = b.usr_id
			left join global.global_auth_user c on a.fol_dokter_instruksi = c.usr_id
			left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
			left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
			where a.id_reg = '$_POST[id_reg]' and a.fol_jenis_sem = 'LD'
			"; */
			
	//$rs = $dtaccess->Execute($sql);
	//$dataTable = $dtaccess->FetchAll($rs);
	//$json = json_encode($dataTable);
	//echo $json;
	//echo $sql;
	//} 
	
?>