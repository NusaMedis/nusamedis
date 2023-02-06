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

    $sql = "select * from klinik.klinik_perawatan_terapi where id_reg=".QuoteValue(DPE_CHAR,$_GET['reg_id']);
    $rs = $dtaccess->Execute($sql);
    $dataPerawatanTerapi = $dtaccess->FetchAll($rs);
   

    $rspn = array();

    foreach ($dataPerawatanTerapi as $rs) {
        $row['rawat_item_id'] = $rs['rawat_item_id'];
        $row['reg_id'] = $rs['id_reg'];
        $row['rawat_id'] = $rs['id_rawat'];
        $row['id_item'] = $rs['id_item'];
        $row['terapi_jumlah_item'] = $rs['terapi_jumlah_item'];
        
        $a = unserialize($rs['perawatan_terapi']);
        foreach ($a as $aa) {
            $row[ $aa['field'] ] = $aa['value'];
        }
        $rspn[] = $row;
    }

    echo json_encode($rspn);

?>