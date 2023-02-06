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

   

    $rspn = array();

    $row['komposisi'] = 'Komposisi';
    $row['dosis'] = 'Dosis';
    $row['satuan_komposisi'] = 'Satuan Komposisi';

    $rspn[] = $row;

    echo json_encode($rspn);

?>