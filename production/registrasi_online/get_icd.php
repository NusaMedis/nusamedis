<?php
	// LIBRARY
	require_once("../penghubung.inc.php");
	require_once($LIB."datamodel.php");

	//INISIALISASI LIBRARY
	$dtaccess = new DataAccess();

	$sql = "select icd_id, icd_nama, icd_nomor from klinik.klinik_icd ";
	$sql .= " where UPPER(icd_nomor) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['q'])."%");
	$sql .= " OR UPPER(icd_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET['q'])."%");
	$sql .= " order by icd_nomor asc limit 100";

	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	
	$json = json_encode($dataTable);
	echo $json;
?>