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

$func = $_GET['func'];



switch ($func) {
  case 'store':
    /* 
      $sql="update klinik.klinik_registrasi set reg_operasi='y' where reg_id=".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      $dtaccess->Execute($sql);

      $sql="update klinik.klinik_registrasi set reg_operasi='y' where reg_id=".QuoteValue(DPE_CHAR,$regId);
      $dtaccess->Execute($sql); */
    //echo $sql; die();

    $dbTable = "klinik.klinik_preop";
    $dbField[0] = "preop_id";
    $dbField[1] = "id_reg";
    $dbField[2] = "preop_waktu";
    $dbField[3] = "preop_tanggal_jadwal";
    $dbField[4] = "preop_selesai_jadwal";
    $dbField[5] = "id_dokter";

    $preopId = $dtaccess->GetTransID();
    $dbValue[0] = QuoteValue(DPE_CHAR, $preopId);
    $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['reg_id']);
    $dbValue[2] = QuoteValue(DPE_DATE, nice_date($_POST['preop_waktu'], 'Y-m-d H:i:s'));
    $dbValue[3] = QuoteValue(DPE_DATE, nice_date($_POST['preop_tanggal_jadwal'], 'Y-m-d H:i:s'));
    $dbValue[4] = QuoteValue(DPE_DATE, nice_date($_POST['preop_selesai_jadwal'], 'Y-m-d H:i:s'));
    $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['usr_name']);

    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

    $s = $dtmodel->Insert() or die("insert  error");

    if ($s) :
      $rs = [
        'reg_id'   => $_POST['reg_id'],
        'preop_id'   => $preopId,
        'preop_status'   => 'n',
        'preop_tanggal_jadwal'   => nice_date($_POST['preop_tanggal_jadwal'], 'd-m-Y H:i:s'),
        'preop_selesai_jadwal'   => nice_date($_POST['preop_selesai_jadwal'], 'd-m-Y H:i:s'),
        'preop_waktu'   => nice_date($_POST['preop_waktu'], 'd-m-Y H:i:s'),
      ];
      echo json_encode($rs);
    endif;

    unset($dbTable);
    unset($dbField);
    unset($dbValue);
    unset($dtmodel);
    break;

  case 'update':
    $dbTable = "klinik.klinik_preop";
    $dbField[0] = "preop_id";
    $dbField[1] = "preop_selesai_jadwal";
    $dbField[2] = "preop_waktu";
    $dbField[3] = "preop_tanggal_jadwal";
    $dbField[4] = "id_dokter";

    $preopId = $_POST['preop_id'];
    $dbValue[0] = QuoteValue(DPE_CHAR, $preopId);
    $dbValue[1] = QuoteValue(DPE_DATE, nice_date($_POST['preop_selesai_jadwal'], 'Y-m-d H:i:s'));
    $dbValue[2] = QuoteValue(DPE_DATE, nice_date($_POST['preop_waktu'], 'Y-m-d H:i:s'));
    $dbValue[3] = QuoteValue(DPE_DATE, nice_date($_POST['preop_tanggal_jadwal'], 'Y-m-d H:i:s'));
    $dbValue[4] = QuoteValue(DPE_CHAR, $_POST['usr_name']);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

    $s = $dtmodel->update();

    if ($s) :
      $rs = [
        'reg_id'   => $_POST['reg_id'],
        'preop_id'   => $preopId,
        'preop_status'   => 'n',
        'preop_tanggal_jadwal'   => nice_date($_POST['preop_tanggal_jadwal'], 'd-m-Y H:i:s'),
        'preop_selesai_jadwal'   => nice_date($_POST['preop_selesai_jadwal'], 'd-m-Y H:i:s'),
        'preop_waktu'   => nice_date($_POST['preop_waktu'], 'd-m-Y H:i:s'),
      ];
      echo json_encode($rs);
    endif;

    unset($dbTable);
    unset($dbField);
    unset($dbValue);
    unset($dtmodel);
    break;

  case 'destroy':
    $dbTable = "klinik.klinik_preop";
    $dbField[0] = "preop_id";
    $dbValue[0] = QuoteValue(DPE_CHAR, $_POST['id']);
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

    echo $dtmodel->delete();

    break;

  default:
    # code...
    break;
}
