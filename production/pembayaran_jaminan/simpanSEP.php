<?php
     // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
   //  require_once($LIB."expAJAX.php"); 
     require_once($LIB."tampilan.php");

     error_reporting(0);
     
     // Inisialisasi Lib
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $enc = new textEncrypt();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depId = $auth->GetDepId();
     $poliId = $auth->IdPoli();
     $tglSekarang = date("d-m-Y");
     $depLowest = $auth->GetDepLowest();


$regID = $_POST['regID'];
$noSEP = $_POST['noSEP'];
$noAnggota = $_POST['noAnggota'];

$sql = "SELECT * from klinik.klinik_registrasi where reg_id = '$regID'";
$dataReg = $dtaccess->Fetch($sql);


      
$sql="update global.global_customer_user set cust_usr_no_jaminan='$noAnggota', cust_usr_no_sep = '$noSEP' where cust_usr_id=".QuoteValue(DPE_CHAR,$$dataReg["id_cust_usr"]);
$dtaccess->Execute($sql);
// echo $sql."<br>";


$sql="update klinik.klinik_sep set no_sep='$noSEP' where sep_reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
$dtaccess->Execute($sql);

//cari data pasien nya dulu
$sql = "select id_cust_usr from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$regID);
$rs = $dtaccess->Execute($sql);
$dataCust = $dtaccess->Fetch($rs);

//update data yang harus disimpan
//customer_user
$sql = "update global.global_customer_user set cust_usr_no_sep= '$noSEP',
        cust_usr_no_jaminan = '$noAnggota' 
        where cust_usr_id = ".QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
$rs = $dtaccess->Execute($sql);
//registrasi
$sql = "update klinik.klinik_registrasi set 
        reg_no_sep = '$noSEP' 
        where reg_id = ".QuoteValue(DPE_CHAR,$regID);
$rs = $dtaccess->Execute($sql);

echo "success";
?>