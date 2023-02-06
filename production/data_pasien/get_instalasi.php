<?php

   // LIBRARY
     require_once("../penghubung.inc.php");
     require_once("../lib/dataaccess.php");

     $dtaccess = new DataAccess();
   
if($_POST['instalasi']) {
	$tipe 			= $_POST['instalasi'];
	// Data poli / klinik
	 $sql = "select * from global.global_auth_poli where (poli_tipe='$tipe' or poli_tipe='R' or poli_tipe='L') order by poli_tipe, poli_nama ASC";    
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
	$total 		= count($dataPoli);
	json = json_encode()

	/* if ($total > 0) {
		while ($dataKatTindakan) {
			echo '<option value="'.$dataKatTindakan['kategori_tindakan_id'].'">'.$rows['kategori_tindakan_nama'].'</option>';
		}
	} else {
		echo '<option value="" selected="selected">Kategori kosong</option>';
	}  */
}


?>