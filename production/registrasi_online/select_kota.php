<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."currency.php"); 

  // Inisialisasi Lib
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $depId = $auth->GetDepId(); 
     
  if (!empty($_GET['q'])){
  	if (ctype_digit($_GET['q'])) {
  		$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['q']."' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota!='00' order by lokasi_nama";
  		$d = $dtaccess->FetchAll($sql);
  	}
  }

  if (empty($_GET['kel'])){
  	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
  		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
  			$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop']."' and lokasi_kecamatan!='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota='".$_GET['kec']."' order by lokasi_nama";
  			$d = $dtaccess->FetchAll($sql);
  		}
  	}
  } else {
  	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
  		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
  			$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop']."' and lokasi_kecamatan='".$_GET['kel']."' and lokasi_kelurahan!='0000' and lokasi_kabupatenkota='".$_GET['kec']."' order by lokasi_nama";
  			$d = $dtaccess->FetchAll($sql);
  		}
  	}
  }
  echo json_encode($d);
?>
