<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  	
     if( isset($_POST['id_reg']) ){
		 $sql = "select a.tindakan_tanggal,a.tindakan_waktu,a.fol_id,a.id_biaya_tarif, a.id_biaya, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah, a.fol_dokter_instruksi as dokter_instruksi, a.fol_pelaksana as pelaksana,
				 b.usr_name as pelaksana_nama,
				 c.usr_name as dokter_instruksi_nama,
				 d.rawat_tindakan_keterangan as no_plat,d.rawat_tindakan_id,
				 f.biaya_nama
			from klinik.klinik_folio a
			left join global.global_auth_user b on a.fol_pelaksana = b.usr_id
			left join global.global_auth_user c on a.fol_dokter_instruksi = c.usr_id
			left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
			left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
			where a.id_reg = '$_POST[id_reg]' and a.fol_jenis_sem = 'AM'
			"; 
	
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	$json = json_encode($dataTable);
	echo $json;
	//echo $sql;
	} 
	
?>
	