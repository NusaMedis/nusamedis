<?php
      // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."expAJAX.php"); 
     require_once($LIB."tampilan.php");
	 
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();

     $sql = "select dep_alamat_ip_inacbg from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
 $key = "ad3b25ce4a3278ebf12eed7b23d366107637e9bbff5ac414a941c3669862ac12";
 //$key =  $konfigurasi["dep_alamat_ip_inacbg"]
 $url = $konfigurasi["dep_alamat_ip_inacbg"]."/e-klaim/ws.php";
 
echo $url;
?>