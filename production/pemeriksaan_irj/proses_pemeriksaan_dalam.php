<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();

$terapiPse = $_POST['terapi'];

$sql = "SELECT * from klinik.klinik_perawatan_diagnosa a 
left join klinik.klinik_perawatan b on a.id_rawat = b.rawat_id 
where id_reg = '".$_POST['dataReg']."'";
$dataDiagnosa = $dtaccess->FetchAll($sql);

if(count($dataDiagnosa) > 0){

	for($i = 0; $i < count($terapiPse); $i++){
		$terapis[] = str_replace("<br>", "", $terapiPse[$i]);
	}
	if($terapis){
		$terapi = implode("+", $terapis);
	}

	$forma = $_POST['forma'];

	for($i = 0; $i < count($forma); $i++){
		$index = $forma[$i]['name'];
		$value = $forma[$i]['value'];

		$form[$index] = $value;
	}


	$sql = "UPDATE klinik.klinik_perawatan SET rawat_poli = " . QuoteValue(DPE_CHAR, serialize($form));
	$sql .= ", rawat_anamnesa =" . QuoteValue(DPE_CHAR, $form['subjective']);
	$sql .= ", rawat_pemeriksaan_fisik =" . QuoteValue(DPE_CHAR, $form['objective']);
	$sql .= ", rawat_diagnosa_utama =" . QuoteValue(DPE_CHAR, $form['ket_diagnosa_empat']);
	$sql .= ", rawat_ket =" . QuoteValue(DPE_CHAR, $form['resume_medis']);
	$sql .= ", rawat_terapi =" . QuoteValue(DPE_CHAR, $terapi);
	$sql .= " where id_reg=" . QuoteValue(DPE_CHAR, $_POST['dataReg']);
	$dtaccess->Execute($sql);

	echo "Berhasil Disimpan";
}else{
	echo "Diagnosa Belum Diisi";
}

