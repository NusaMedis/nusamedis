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
     
     $host="localhost";
     $user=$enc->Decode(DB_USER);
     $password=$enc->Decode(DB_PASSWORD);
     $port="5432";
     $dbname = "rspi";

     $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
      
      // nyari data ee dulu -- trus di while --
     $sql = pg_query($link, "delete from klinik.klinik_reg_antrian_reguler");   
     $sql =pg_query($link, "delete from klinik.klinik_reg_antrian_jkn_reguler ");
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian ");
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian_jkn ");
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian_jkn_rehab_medik ");
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian_jkn_rehab_medik_ekse ");          
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian_rehab ");
     $sql = pg_query($link,"delete from klinik.klinik_reg_antrian_rehab_ekse ");
/*
     
     //Reset Antrian Umum Reguler
     $sql = "delete from klinik.klinik_reg_antrian_reguler";
     $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     
     //Reset Antrian JKN Reguler
     $sql = "delete from klinik.klinik_reg_antrian_jkn_reguler ";
     $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     
     //Reset Antrian Umum Eksekutif
     $sql = "delete from klinik.klinik_reg_antrian ";
     $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     
     //Reset Antrian JKN Eksekutif
     $sql = "delete from klinik.klinik_reg_antrian_jkn ";
     $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
*/
?>