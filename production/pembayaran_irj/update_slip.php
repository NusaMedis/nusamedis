<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."tampilan.php");

//INISIALISASI LIBRARY
$enc = new textEncrypt();
$dtaccess = new DataAccess();
$auth = new CAuth();
   $depId = $auth->GetDepId();
   $userName = $auth->GetUserName();
$userLogin = $auth->GetUserData();

$id = $_POST['id_det'];

	$sqlUpdate = "update klinik.klinik_pembayaran_det set pembayaran_det_slip=".QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"])." where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$id);
    $result = $dtaccess->Execute($sqlUpdate);
    //echo $sqlUpdate;

if ($result){
     	echo json_encode(array('success'=>true));
     } else {
     	echo json_encode(array('errorMsg'=>'Some errors occured.'));
     } 

?>     