<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "datamodel.php");

//INISIALISASI LIBRARY
$dtaccess = new DataAccess();
$sql = "UPDATE klinik.klinik_perawatan SET rawat_obgyn = " . QuoteValue(DPE_CHAR, serialize($_POST));
$sql .= ", rawat_anamnesa =" . QuoteValue(DPE_CHAR, $_POST['anamnesa']);
// $sql .= ", anamnesa =".QuoteValue(DPE_CHAR,$_POST['observasi']);
//$sql .= ", rawat_catatan =".QuoteValue(DPE_CHAR,$_POST['konsultasi']);
$diagnosa = 'G=' . $_POST['g_analisa'] . '; P=' . $_POST['p_analisa'] . '; A=' . $_POST['a_analisa'];
$diagnosa .= '<br> ' . $_POST['ket_diagnosa_satu'] . ' Mg / ';
$diagnosa .= ($_POST['ket_diagnosa_dua'] == 'G') ? 'Gemelli' : 'Tunggal ';
$diagnosa .= ' / ' . $_POST['ket_diagnosa_tiga'] . ' / ' . $_POST['ket_diagnosa_lima'] . '<br> ' . $_POST['ket_diagnosa_empat'];
$sql .= ", rawat_penunjang=" . QuoteValue(DPE_CHAR, $diagnosa);
$sql .= ", rawat_pemeriksaan_fisik =" . QuoteValue(DPE_CHAR, $_POST['pemeriksaan_umum']);
$sql .= ", rawat_diagnosa_utama =" . QuoteValue(DPE_CHAR, $_POST['ket_diagnosa_empat']);
$sql .= ", rawat_ket =" . QuoteValue(DPE_CHAR, $_POST['resume_medis']);
$sql .= ", rawat_terapi =" . QuoteValue(DPE_CHAR, $_POST['terapi']);
$sql .= ", rawat_usg_ginekologi =" . QuoteValue(DPE_CHAR, $_POST['usg_ginekologi']);
$sql .= ", rawat_pemeriksaan_dalam =" . QuoteValue(DPE_CHAR, $_POST['pemeriksaan_dalam']);
$sql .= " where id_reg=" . QuoteValue(DPE_CHAR, $_POST['id_reg']);
$dtaccess->Execute($sql);
		// echo $sql;
