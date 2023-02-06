<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php"); 
     require_once($ROOT."lib/tampilan.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
	   $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     /*$host="localhost";
     $user="its";
     $password="itsthok";
     $port="5432";
     $dbname="rso_coding";   */
     
     $host=DB_SERVER;
     //$user=$enc->Encode(DB_USER);
     //$password=$enc->Encode(DB_PASSWORD);
     $user="its";
     $password="itsthok";
     $port="5432";
     $dbname=DB_NAME;

     $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
      
     $q = strtoupper($_GET["q"]);
     //echo $q;
     
      // nyari data ee dulu -- trus di while --
      $result = pg_query($link, "select biaya_id, biaya_nama, biaya_kategori 
      from klinik.klinik_biaya where UPPER(biaya_nama) like '%".$q."%' ");   
       while($hasil = pg_fetch_assoc($result)) {
       
       // tk masukkan array lagi --         biaya_kategori     biaya_nama
//       $items = array($hasil['biaya_kategori']=>$hasil['biaya_nama']."~".$hasil['biaya_id']);
  
      $items = array($hasil['biaya_nama']=>$hasil['biaya_kategori']."~".$hasil['biaya_id']);
       
       foreach ($items as $key=>$value) {
    	     if (strpos(strtoupper($key), $q) !== false) {
    		      //echo $items;
              echo "$key|$value\n";
    	     }
       }
       
       }

?>