 <?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
     if( isset($_POST['id_reg']) ){
     // $sql = "select id_pembayaran from klinik.klinik_registrasi where reg_id = '$_POST[id_reg]'";
     $dataPembayaran = $dtaccess->Fetch($sql);
		 $sql = "select a.fol_id, a.tindakan_tanggal, a.tindakan_waktu, a.id_biaya, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah,
		 		 b.is_cito,
				 d.rawat_tindakan_id,
				 f.biaya_nama
			from klinik.klinik_folio a																		   
			left join klinik.klinik_biaya_tarif b on a.id_biaya_tarif = b.biaya_tarif_id
			left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
			left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
			where a.id_reg = '$_POST[id_reg]' order by fol_dibayar_when asc limit 1 
			"; 
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->Fetch($rs);
	$data = [];


      array_push($data, [
          'fol_id'   => $dataTable['fol_id'],
          'id_reg'   => $dataTable['id_reg'],
          'id_biaya'   => $dataTable['id_biaya'],
          'tindakan_tanggal'   => format_date($dataTable['tindakan_tanggal']),
          'tindakan_waktu'   => $dataTable['tindakan_waktu'],
          'id_biaya_tarif'   => $dataTable['id_biaya_tarif'],
          'fol_lunas'   => $dataTable['fol_lunas'],
          'fol_jumlah'   => $dataTable['fol_jumlah'],
          'is_cito'   => $dataTable['is_cito'],
          'rawat_tindakan_id'   => $dataTable['rawat_tindakan_id'],
          'biaya_nama'   => $dataTable['biaya_nama'].$cito
        ]);
    

    echo json_encode($data);
	// echo $sql;
	} 
	
?>
	