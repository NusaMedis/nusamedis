<?php

   // LIBRARY
     require_once("../penghubung.inc.php");
     require_once("../lib/dataaccess.php");

     $dtaccess = new DataAccess();
   
	if ($_GET['rm']){ 
	 $sql = "select cust_usr_kode, cust_usr_nama from global.global_customer_user  a ";
	 $sql .= " where UPPER(cust_usr_kode) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['rm'])."%");
 	 //$sql .= " OR UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['nama'])."%");
	 $sql .=" order by cust_usr_kode desc limit 10";    
     $rs = $dtaccess->Execute($sql);
     $row = $dtaccess->FetchAll($rs);
	 $json = [];
	 
		for($i=0; $i < count( $row ); $i++ ){
		 $json[] = ['id'=>$row[$i]['cust_usr_kode'], 'text'=>$row[$i]['cust_usr_kode']];
		}
	}
	
	if ($_GET['nama']){ 
	 $sql = "select cust_usr_kode, cust_usr_nama from global.global_customer_user  a ";
	 //$sql .= " where UPPER(cust_usr_kode) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['rm'])."%");
 	 $sql .= " WHERE UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['nama'])."%");
	 $sql .=" order by cust_usr_kode desc limit 10";    
     $rs = $dtaccess->Execute($sql);
     $row = $dtaccess->FetchAll($rs);
	 $json = [];
	 
		for($i=0; $i < count( $row ); $i++ ){
		 $json[] = ['id'=>$row[$i]['cust_usr_nama'], 'text'=>$row[$i]['cust_usr_nama']];
		}
	}
	
	if ($_GET['nik']){ 
	 $sql = "select cust_usr_no_identitas, cust_usr_nik from global.global_customer_user  a ";
 	 $sql .= " WHERE cust_usr_no_identitas like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['nik'])."%");
	 //$sql .= " where UPPER(cust_usr_kode) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['rm'])."%");
	 $sql .=" order by cust_usr_kode desc limit 10";    
     $rs = $dtaccess->Execute($sql);
     $row = $dtaccess->FetchAll($rs);
	 $json = [];
	 
		for($i=0; $i < count( $row ); $i++ ){
		 $json[] = ['id'=>$row[$i]['cust_usr_no_identitas'], 'text'=>$row[$i]['cust_usr_no_identitas']];
		}
	}


echo json_encode($json);
?>