<?php
// Library
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "tampilan.php");

// Inisialisasi Lib
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$enc = new textEncrypt();
$userData = $auth->GetUserData();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();
$tglSekarang = date("d-m-Y");
$depLowest = $auth->GetDepLowest();

$type = $_POST['type'];

if ($type == 'saveFormKonsul') {


    $data = $_POST['dataForm'];
    $id_rawat = $_POST['id_rawat'];
    $ttd_keluarga = $_POST['ttd_keluarga'];

    $emptyPhoto = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAYAAABkW7XSAAAAxUlEQVR4nO3BMQEAAADCoPVPbQhfoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOA1v9QAATX68/0AAAAASUVORK5CYII=";

    $form = [];
    for ($i = 0; $i < count($data); $i++) {
        $sub = explode("[", $data[$i]['name']);
        $subArray = substr($sub[1],  0, strlen($sub[1]) - 1);
        if (count($sub) > 1) {
            $form[$sub[0]][$subArray] = $data[$i]['value'];
        } else {
            $form[$data[$i]['name']] = $data[$i]['value'];
        }
    }

    if ($ttd_keluarga != $emptyPhoto) {
        $fileName = $dtaccess->GetTransID();
        $encoded_image = explode(",", $ttd_keluarga)[1];
        $decoded_image = base64_decode($encoded_image);
        file_put_contents('../gambar/asset_ttd/' . $fileName . '.jpg', $decoded_image);

        $form["ttd_keluarga"] = $fileName;
    }

    $form = serialize($form);

    $dbTable = "klinik.klinik_perawatan";


    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "permintaan_konsultasi";


    $dbValue[0] = QuoteValue(DPE_CHAR, $id_rawat);
    $dbValue[1] = QuoteValue(DPE_CHAR, $form);



    //print_r($dbValue); die();
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);



    $dtmodel->Update() or die("Update  error");

    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);
}

if ($type == 'jawabForm') {

    $data = $_POST['dataForm'];
    $id_rawat = $_POST['id_rawat'];
    $ttd_keluarga = $_POST['ttd_keluarga'];

    $emptyPhoto = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAYAAABkW7XSAAAAxUlEQVR4nO3BMQEAAADCoPVPbQhfoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOA1v9QAATX68/0AAAAASUVORK5CYII=";

    $sql = "SELECT rawat_id from klinik.klinik_perawatan 
            where id_reg = ( 
                select reg_utama from klinik.klinik_registrasi a 
                left join klinik.klinik_perawatan b on a.reg_id = b.id_reg 
                where b.rawat_id = '$id_rawat'
                )";
    $rawat_utama = $dtaccess->Fetch($sql);

    $form = [];
    for ($i = 0; $i < count($data); $i++) {
        $sub = explode("[", $data[$i]['name']);
        $subArray = substr($sub[1],  0, strlen($sub[1]) - 1);
        if (count($sub) > 1) {
            $form[$sub[0]][$subArray] = $data[$i]['value'];
        } else {
            $form[$data[$i]['name']] = $data[$i]['value'];
        }
    }

    $form = serialize($form);

    $dbTable = "klinik.klinik_perawatan";

    $dbField[0] = "rawat_id";   // PK
    $dbField[1] = "jawaban_konsultasi";


    $dbValue[0] = QuoteValue(DPE_CHAR, $id_rawat);
    $dbValue[1] = QuoteValue(DPE_CHAR, $form);

    //print_r($dbValue); die();
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

    $res = $dtmodel->Update();

    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);

    if($rawat_utama['rawat_id']){
        $dbTable = "klinik.klinik_perawatan";

        $dbField[0] = "rawat_id";   // PK
        $dbField[1] = "jawaban_konsultasi";

        $dbValue[0] = QuoteValue(DPE_CHAR, $rawat_utama['rawat_id']);
        $dbValue[1] = QuoteValue(DPE_CHAR, $form);

        $dbKey[0] = 0; 
        $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

        $res = $dtmodel->Update();

        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
    }

    if ($res) {
        echo "ok";
    }
}

if ($type == 'hapus_ttd') {
    $fileName = "../gambar/asset_ttd/" . $_POST['id'] . ".jpg";


    if (file_exists($fileName)) {
        $res = unlink($fileName);

        if ($res) {
            echo "ok";
        }
    }
}

if ($type == 'get') {
    $id_rawat = $_POST['id_rawat'];

    $sql = "SELECT rawat_id, permintaan_konsultasi from klinik.klinik_perawatan 
            where id_reg = ( 
                select reg_utama from klinik.klinik_registrasi a 
                left join klinik.klinik_perawatan b on a.reg_id = b.id_reg 
                where b.rawat_id = '$id_rawat'
                )";
    $rawat_utama = $dtaccess->Fetch($sql);

    $send = [];

    $sql = "SELECT permintaan_konsultasi, jawaban_konsultasi from klinik.klinik_perawatan where rawat_id = '$id_rawat'";
    $dataKonsul = $dtaccess->Fetch($sql);

    $dataKonsul['permintaan_konsultasi'] = ($rawat_utama['rawat_id']) ? $rawat_utama['permintaan_konsultasi'] : $dataKonsul['permintaan_konsultasi'];

    $permintaan = ($dataKonsul['permintaan_konsultasi']) ? unserialize($dataKonsul['permintaan_konsultasi']) : array() ;
    $jawaban = ($dataKonsul['jawaban_konsultasi']) ? unserialize($dataKonsul['jawaban_konsultasi']) : array() ;
    $send['permintaan'] = $permintaan;
    $send['jawaban'] = $jawaban;

    echo json_encode($send);
}
