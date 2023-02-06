<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

//INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();
// print_r($userId);
// exit();
$idSplit = "1"; //DIPATEN 1 untuk JASA MEDIK    

//cari folio
$sql = "select folsplit_id from klinik.klinik_folio_split 
      where id_fol = " . QuoteValue(DPE_CHAR, $_POST["id_fol"]) . " and id_split='$idSplit'";
//echo $sql;
$rs = $dtaccess->Execute($sql);
$folioSplit = $dtaccess->Fetch($rs);

$sql = "select id_biaya_tarif from klinik.klinik_folio
      where fol_id = " . QuoteValue(DPE_CHAR, $_POST["id_fol"]);
$rs = $dtaccess->Execute($sql);
$folio = $dtaccess->Fetch($rs);


//cari split biaya
$sql = "select bea_split_nominal from klinik.klinik_biaya_split 
          where id_biaya_tarif = '$folio[id_biaya_tarif]' and id_split='$idSplit'";
$rs = $dtaccess->Execute($sql);
$biayaSplit = $dtaccess->Fetch($rs);

//


//INSERT REMUNERASI PASIEN
$sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya_tarif =" . QuoteValue(DPE_CHAR, $folio["id_biaya_tarif"]);
$sql .= " and id_split = " . QuoteValue(DPE_CHAR, $idSplit);
$sql .= " and id_folio_posisi = " . QuoteValue(DPE_CHAR, $_POST["fol_pelaksana_tipe"]);
$rs = $dtaccess->Execute($sql);
$remun = $dtaccess->Fetch($rs);
// echo $sql; 


if ($_POST['isNewRecord'] == 'true') {
  $folPelId = $dtaccess->GetTransID();
} else {
  $folPelId = $_POST['fol_pelaksana_id'];
}

# simpan di pelaksana
$dbTable = "klinik.klinik_folio_pelaksana";
$dbField[0] = "fol_pelaksana_id";   // PK
$dbField[1] = "id_fol";
$dbField[2] = "id_usr";
$dbField[3] = "id_fol_posisi";
$dbField[4] = "fol_pelaksana_nominal";
$dbField[5] = "id_fol_split";
$dbField[6] = "fol_pelaksana_tipe";

$dbValue[0] = QuoteValue(DPE_CHAR, $folPelId);
$dbValue[1] = QuoteValue(DPE_CHAR, $_POST["id_fol"]);
$dbValue[2] = QuoteValue(DPE_CHAR, $_POST["usr_id"]);
$dbValue[3] = QuoteValue(DPE_CHAR, $_POST["fol_pelaksana_tipe"]);
$dbValue[4] = QuoteValue(DPE_NUMERIC, $remun['biaya_remunerasi_nominal']);
$dbValue[5] = QuoteValue(DPE_CHAR, $folioSplit["folsplit_id"]);
$dbValue[6] = QuoteValue(DPE_CHAR, $_POST["fol_pelaksana_tipe"]);

$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
// print_r($dbField);
// print_r($dbValue);
//print_r($dbKey);
//die();
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

if ($_POST['isNewRecord'] == 'true') {
  $dtmodel->Insert() or die("insert  error");
  echo "sukses insert pelaksana => ";
} else {
  $dtmodel->Update() or die("insert  error");
  echo "sukses update pelaksana => ";
  //delete split dulu
  //$sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$_POST["id_fol"]);
  //$dtaccess->Execute($sql);
  //echo "sukses hapus fol split lama => " ;
}

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);




exit();
