<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		 
	  $sql = "select * from global.global_kondisi_akhir_deskripsi";
    $sql .=" where id_kondisi_akhir_pasien =".QuoteValue(DPE_CHAR,$_POST['id']);
    $sql .=" order by kondisi_akhir_deskripsi_nama asc";
	  //die($sql);
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);

	echo "<option value=''>[ Pilih Kondisi Akhir Deskripsi ]</option>";
	for ($i = 0; $i < count($dataTable); $i++){
		echo "<option value='".$dataTable[$i]['kondisi_akhir_deskripsi_id']."'>".$dataTable[$i]['kondisi_akhir_deskripsi_nama']."</option>";
	}
	
?>