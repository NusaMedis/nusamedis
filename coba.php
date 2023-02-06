<?php
require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php"); 
    // require_once($ROOT."lib/tampilan.php");
     require_once($ROOT."lib/conf/database.php");
     require_once($ROOT."lib/currency.php");
          
    // $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
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

$link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
 
$sql= "select * from radiologi.radiologi_template where template_id='6ce0aa716822544a1c473734d05aedb0'";
      //cari konfigurasi departemen
     $sqlcaridep = pg_query($link, $sql);
     $arrdep = pg_fetch_assoc($sqlcaridep);

$string = $arrdep["template_ket"];
 echo $string."<br>";
 echo "htmlentities ".nl2br(htmlentities($string))."<br>";
 echo "htmlspecialchars ".nl2br(htmlspecialchars($string))."<br>";
?>