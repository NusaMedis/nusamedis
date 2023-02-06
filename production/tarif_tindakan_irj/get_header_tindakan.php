<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."currency.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."expAJAX.php");
  require_once($LIB."tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();     
  $auth = new CAuth();
  $depId = $auth->GetDepId();
  $depLowest = $auth->GetDepLowest();
  $table = new InoTable("table1","100%","left",null,1,2,1,null);
  $PageJenisBiaya = "page_jenis_biaya.php";    

  $tahunTarif = $auth->GetTahunTarif();
  $depNama = $auth->GetDepNama();
  $userName = $auth->GetUserName();

  $sql_where_tindakan = "select * from  klinik.klinik_kategori_tindakan_header where id_kategori_tindakan_header_instalasi=".QuoteValue(DPE_CHAR,$_GET['id']). " order by kategori_tindakan_header_urut asc";
  $dataTindakan = $dtaccess->FetchAll($sql_where_tindakan);

  echo json_encode($dataTindakan);
?>