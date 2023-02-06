<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();

		$sql = "UPDATE klinik.klinik_perawatan 
				SET rawat_anamnesa =".QuoteValue(DPE_CHAR,$_POST['anamnesa']);
		$sql .= ", rawat_keluhan =".QuoteValue(DPE_CHAR,$_POST['observasi']);
		$sql .= ", rawat_catatan =".QuoteValue(DPE_CHAR,$_POST['konsultasi']);
		$sql .= ", rawat_pemeriksaan_fisik =".QuoteValue(DPE_CHAR,$_POST['pemeriksaan_umum']);
		$sql .= ", rawat_diagnosa_utama =".QuoteValue(DPE_CHAR,$_POST['pencatatan_diagnosa']);
		$sql .= ", rawat_ket =".QuoteValue(DPE_CHAR,$_POST['resume_medis']);
		$sql .= " where id_reg=".QuoteValue(DPE_CHAR,$_POST['id_reg']);
		$dtaccess->Execute($sql);
		//echo $sql;
	
?>