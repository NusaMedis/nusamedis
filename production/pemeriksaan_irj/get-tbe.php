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

    $sql = "select * from klinik.klinik_anamnesa_tb where id_anamnesa=".QuoteValue(DPE_CHAR,$_POST['anamnesa_id'])." AND id_reg=".QuoteValue(DPE_CHAR,$_POST['reg_id']);
    $sql .= "  order by when_create asc";
    $rs = $dtaccess->Execute($sql);
    $dataAnamnesa = $dtaccess->FetchAll($rs);
   

    $rspn = array();

    foreach ($dataAnamnesa as $rs) {
        $row['anamnesa_tb_id'] = $rs['anamnesa_tb_id'];
        $row['reg_id'] = $rs['id_reg'];
        $row['rawat_id'] = $rs['id_rawat'];
        $row['poli_id'] = $rs['id_poli'];
        $row['anamnesa_id'] = $rs['id_anamnesa'];
        
        $a = unserialize($rs['anamnesa_tb_isi']);
        foreach ($a as $aa) {
            $row[ $aa['field'] ] = $aa['value'];
        }
        $rspn[] = $row;
    }

    echo json_encode($rspn);

?>