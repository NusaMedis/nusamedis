<?php
require_once("../penghubung.inc.php");
require_once($ROOT . "lib/bit.php");
require_once($ROOT . "lib/login.php");
require_once($ROOT . "lib/encrypt.php");
require_once($ROOT . "lib/datamodel.php");
require_once($ROOT . "lib/dateLib.php");
require_once($ROOT . "lib/tree.php");
require_once($ROOT . "lib/currency.php");
require_once($ROOT . "lib/expAJAX.php");
require_once($ROOT . "lib/tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$err_code = 0;
$depId = $auth->GetDepId();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();
$table = new InoTable("table", "100%", "left");
$sql = "select margin_nilai from apotik.apotik_margin
      where id_grup_item = " . QuoteValue(DPE_CHAR, $_GET["id_kategori"]) . "
      and is_aktif ='Y' and " . $_GET["hargabeli"] . " >= harga_min and " . $_GET["hargabeli"] .
	" <= harga_max ";
$rs = $dtaccess->Execute($sql);
$margin = $dtaccess->Fetch($rs);
echo $margin['margin_nilai'];
