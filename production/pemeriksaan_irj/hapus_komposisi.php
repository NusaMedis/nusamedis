<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
//INISIALISASI LIBRARY
$enc = new textEncrypt();
$dtaccess = new DataAccess();
$auth = new CAuth();
$view = new CView($_SERVER["PHP_SELF"], $_SERVER['QUERY_STRING']);
$table = new InoTable("table1", "100%", "center");
$userName = $auth->GetUserName();

$sql = "DELETE FROM klinik.klinik_perawatan_terapi WHERE rawat_item_id =" . QuoteValue(DPE_CHAR, $_GET['id']);
$dtaccess->Execute($sql);

// ---------------------------hapus apotik detail racikan----------------------------
$sql_detail = "DELETE FROM apotik.apotik_detail_racikan WHERE detail_racikan_id =" . QuoteValue(DPE_CHAR, $_GET['id']);
$dtaccess->Execute($sql_detail);


// ---------------------------hapus komposisi racikan history----------------------------
$sql_hist = "DELETE FROM klinik.klinik_history_terapi WHERE history_terapi_id =" . QuoteValue(DPE_CHAR, $_GET['id']);
$dtaccess->Execute($sql_hist);
