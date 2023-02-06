<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$depLowest = $auth->GetDepLowest();
$table = new InoTable("table1", "100%", "left", null, 1, 2, 1, null);
$PageJenisBiaya = "page_jenis_biaya.php";


$sql = "delete from global.global_customer_user
where cust_usr_id =" . QuoteValue(DPE_CHAR, $_GET['id']);
$rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$row_edit = $dtaccess->Fetch($rs_edit);
$dtaccess->Clear($rs_edit);
$data = "oke";

echo json_encode($data);
