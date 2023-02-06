<?php 

     require_once("../lib/login.php");
     require_once("../lib/datamodel.php"); 
     require_once("../lib/tampilan.php");
     require_once("../lib/conf/database.php");
     require_once("../lib/currency.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
	   $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     
     $host="localhost";
     $user=$enc->Decode(DB_USER);
     $password=$enc->Decode(DB_PASSWORD);
     $port="5432";
     $dbname = DB_NAME;
     
     $skr = date("d-m-Y");
      
     $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
?>