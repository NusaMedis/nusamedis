<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();


	$sql = "select count(rujukan_no_rujukan) as rows from klinik.klinik_sep where rujukan_no_rujukan = ".QuoteValue(DPE_CHAR,$_POST['param']);
	$dataRujukan = $dtaccess->Fetch($sql);

	$Result = $dataRujukan['rows'];

	echo $Result;

?>