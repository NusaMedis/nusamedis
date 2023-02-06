<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."tampilan.php");
  //INISIALISASI LIBRARY
  $enc = new textEncrypt();
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
  $table = new InoTable("table1","100%","center");
  $userName = $auth->GetUserName();

  $sql = "DELETE FROM klinik.klinik_rujukan_tindakan WHERE rujukan_tindakan_id =".QuoteValue(DPE_CHAR,$_GET['id']);
  $dtaccess->Execute($sql);
  $sql = "delete from laboratorium.lab_pemeriksaan_detail where id_fol = ".QuoteValue(DPE_CHAR,$_GET['fol_id']);
  $dtaccess->Execute($sql);
  $sql = "delete from klinik.klinik_folio where
  fol_id =".QuoteValue(DPE_CHAR,$_GET['fol_id'])." and fol_lunas = 'n'";
	 $result = $dtaccess->Execute($sql);
   

  $sql_fol = "DELETE FROM klinik.klinik_perawatan_tindakan WHERE id_fol =".QuoteValue(DPE_CHAR,$_GET['fol_id']);
  $dtaccess->Execute($sql_fol);
?>