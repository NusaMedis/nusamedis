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

    $data = array();
    
    $sql = "select * from klinik.klinik_perawatan_terapi where id_rawat_terapi_racikan=".QuoteValue(DPE_CHAR,$_GET['id']);
    $rs = $dtaccess->Execute($sql);
    $dataFarmasi = $dtaccess->FetchAll($rs);
   //echo $sql;

   // $rspn = array();

    // foreach ($dataFarmasi as $rs) {
    //     $row['id_rawat_terapi_racikan'] = $rs['id_rawat_terapi_racikan'];
    //     $row['rawat_id'] = $rs['id_rawat'];
        
    //     $rspn[] = $row;
    // }

    for($i=0; $i < count($dataFarmasi); $i++){    
    $row = array(
      'id_rawat_terapi_racikan'   => $dataFarmasi[$i]['id_rawat_terapi_racikan'],
      'terapi_jumlah_item'   => $dataFarmasi[$i]['terapi_jumlah_item'],
      'item_nama'   => $dataFarmasi[$i]['item_nama'],
      'rawat_item_id'   => $dataFarmasi[$i]['rawat_item_id'],
    );
    $data[]=$row;
  }


    echo json_encode($data);

?>