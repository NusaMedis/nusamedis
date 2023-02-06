<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."currency.php"); 

  // Inisialisasi Lib
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $depId = $auth->GetDepId(); 
  
  /*SELECT KOTA*/
  if (!empty($_GET['q'])){
    if (ctype_digit($_GET['q'])) {
      $sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['q']."' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota!='00' order by lokasi_nama";
      $d = $dtaccess->FetchAll($sql);
    }
  }
  /*SELECT KOTA*/

  // if (empty($_GET['kecamatan'])){ // GET ADA TP VALUE KOSONG
    /*SELECT KELURAHAN*/
    if (!empty($_GET['kecamatan2']) and !empty($_GET['prop2']) and !empty($_GET['kota2'])){ // GET KEC & PROV ADA VALUE
      if (ctype_digit($_GET['kecamatan2']) and ctype_digit($_GET['prop2']) and ctype_digit($_GET['kota2'])) { // GET KEC & PROV ADA VALUE
        $sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop2']."' and lokasi_kecamatan='".$_GET['kecamatan2']."' and lokasi_kelurahan!='0000' and lokasi_kabupatenkota='".$_GET['kota2']."' order by lokasi_nama";
        $d = $dtaccess->FetchAll($sql);
      }
    }
    /*SELECT KELURAHAN*/
  // } else { // GET KEL ADA VALUE
  /*SELECT KECAMATAN*/
    if (!empty($_GET['kota']) and !empty($_GET['prop'])){ // GET KEC & PROV ADA VALUE
      if (ctype_digit($_GET['kota']) and ctype_digit($_GET['prop'])) { // GET KEC & PROV ADA VALUE TYPE NUMBER
        $sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop']."' and lokasi_kecamatan!='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota='".$_GET['kota']."' order by lokasi_nama";
        $d = $dtaccess->FetchAll($sql);
      }
    }
  /*SELECT KECAMATAN*/
  
  // }
  echo json_encode($d);
?>
