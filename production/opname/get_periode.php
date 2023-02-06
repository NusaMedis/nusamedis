<?php
	require_once("../penghubung.inc.php");
  require_once($ROOT."lib/login.php");
  require_once($ROOT."lib/encrypt.php");
  require_once($ROOT."lib/datamodel.php");
  require_once($ROOT."lib/currency.php");
  require_once($ROOT."lib/dateLib.php");
  require_once($ROOT."lib/expAJAX.php");
  require_once($ROOT."lib/tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new TextEncrypt();     
  $auth = new CAuth();
  $table = new InoTable("table","100%","left");
  $usrId = $auth->GetUserId();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $depLowest = $auth->GetDepLowest();

  if ($_GET['tahun']) {
	  $sql = "SELECT * FROM logistik.logistik_penerimaan_periode WHERE extract(year from penerimaan_periode_tanggal_awal) = ".QuoteValue(DPE_CHAR, $_GET['tahun'])." ORDER BY penerimaan_periode_tanggal_awal ASC";
	  $data = $dtaccess->FetchAll($sql);

	  foreach ($data as $key => $value) {
	  	$isi[$key]['penerimaan_periode_id'] = $value['penerimaan_periode_id'];
	  	$isi[$key]['penerimaan_periode_nama'] = $value['penerimaan_periode_nama'];
	  }
  } elseif ($_GET['id']) {
  	$sql = "SELECT * FROM logistik.logistik_penerimaan_periode WHERE penerimaan_periode_id = ".QuoteValue(DPE_CHAR, $_GET['id']);
	  $data = $dtaccess->Fetch($sql);

	  $isi['tgl_awal'] = date_db($data['penerimaan_periode_tanggal_awal']);
	  $isi['tgl_akhir'] = date_db($data['penerimaan_periode_tanggal_akhir']);
  }

  echo json_encode($isi);
?>