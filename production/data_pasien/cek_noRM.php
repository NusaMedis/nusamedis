<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
    $dtaccess = new DataAccess();
	
	$kd = explode('.',$_POST['cust_usr_kode']);
	$kode = $kd[0].$kd[1].$kd[2].$kd[3];
	//die($kode);
		  
	 $sql = "select cust_usr_kode from global.global_customer_user";
	$sql .=" where cust_usr_kode =".QuoteValue(DPE_CHAR, $kode);
	$rs = $dtaccess->Execute($sql);
	$data = $dtaccess->Fetch($rs); 
	//die($sql); 
	if ($data["cust_usr_kode"] == $kode ) {
		echo json_encode(array('success'=>$_POST["cust_usr_kode"]));
	}
	
?>