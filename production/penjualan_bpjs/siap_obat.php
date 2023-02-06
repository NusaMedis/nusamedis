<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");    
     require_once($LIB."encrypt.php");
     require_once($LIB."tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table","70%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();  
     $depLowest = $auth->GetDepLowest();
     $enc = new TextEncrypt();
     	                
     $_POST["id_penjualan"]=$enc->Decode($_GET["id"]);
     
     $theDep = $dataTransaksi[0]["id_gudang"];
     $sql = "update apotik.apotik_penjualan set is_terima ='s'
            where penjualan_id = ".QuoteValue(DPE_CHAR,$_POST["id_penjualan"]);
     $rs = $dtaccess->Execute($sql);
     
     $kembali = "penjualan_view.php?klinik=".$depId;
     header('location:'.$kembali);
     exit();
?>