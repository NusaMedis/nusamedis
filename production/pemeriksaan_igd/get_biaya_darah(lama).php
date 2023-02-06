<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
		 
	$sql = "select a.biaya_tarif_id, a.biaya_total, b.biaya_nama
            from klinik.klinik_biaya_tarif a 
            left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id";
   
    //$sql .=" where a.biaya_tarif_tgl_akhir >= ".QuoteValue(DPE_CHAR,date("Y-m-d"));
    //$sql .=" and e.id_poli = ".QuoteValue(DPE_CHAR,$_POST['id_poli']);
    $sql .=" where ".QuoteValue(DPE_CHAR,date("Y-m-d"))." >= a.biaya_tarif_tgl_awal";
    $sql .=" and ".QuoteValue(DPE_CHAR,date("Y-m-d"))."<= a.biaya_tarif_tgl_akhir";
    $sql .=" and UPPER(b.biaya_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['q'])."%");
    $sql .=" and b.biaya_jenis_sem = 'LD'";
    $sql .=" and a.id_kelas = ".QuoteValue(DPE_CHAR,'4023d2b9644b5c0ec1090d1dc0c60aa3'); //NON CLASS
    $sql .=" order by b.biaya_nama asc";
      //die($sql);

	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	
	$json = json_encode($dataTable);
	echo $json;
	
?>