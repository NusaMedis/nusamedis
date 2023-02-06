<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		  
	 if($_POST['get_pasien']){
		$sql = "select cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan,cust_usr_id,cust_usr_nama,cust_usr_alamat
				from global.global_customer_user";
		//$sql .= " where UPPER(cust_usr_id) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['get_pasien'])."%");
		//$sql .= " or UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['get_pasien'])."%");
		$sql .= " WHERE cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST['get_pasien']);
	 }elseif($_POST['q']){
		$sql = "select cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan,cust_usr_id,cust_usr_nama,cust_usr_alamat, cust_usr_tanggal_lahir
				from global.global_customer_user";
		$sql .= " where UPPER(cust_usr_kode) like".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['q'])."%");
		$sql .= " OR UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['q'])."%");
	 } else {
		$sql = "select cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan,cust_usr_nama,cust_usr_alamat, cust_usr_tanggal_lahir
				from global.global_customer_user 
				order by cust_usr_kode desc limit 5";
	 }
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs);
		$json = json_encode($dataTable);
		echo $json;
?>