<?php

   // LIBRARY
     require_once("../penghubung.inc.php");
     require_once("../lib/dataaccess.php");

     $dtaccess = new DataAccess();
   
if($_POST['poli']) {
	$id = $_POST['poli'];
	
	$sql = "select a.usr_name,a.usr_id,b.id_poli,c.rol_jabatan
			from global.global_auth_user a 
			left join global.global_auth_user_poli b on a.usr_id = b.id_usr
			left join global.global_auth_role c on a.id_rol = c.rol_id";
    $sql .= " where c.rol_jabatan = 'D' and usr_status='y'";	
    $sql .= " and b.id_poli =".QuoteValue(DPE_CHAR,$_POST['poli']);	
    $sql .= " order by usr_name asc";	
	//die($sql);
    $rs = $dtaccess->Execute($sql);
    $dataDokter = $dtaccess->FetchAll($rs);
	$total 		= count($dataDokter);
	
	if ($total > 0) {
		//echo '<option value="" selected="selected">- Pilih Dokter -</option>';
		for ( $i=0; $i < $total; $i++ ) {
			echo '<option value="'.$dataDokter[$i]['usr_id'].'">'.$dataDokter[$i]["usr_name"].'</option>';
		}
	} else {
		echo '<option value="" selected="selected">Dokter Tidak Ditemukan</option>';
	} 
}


?>