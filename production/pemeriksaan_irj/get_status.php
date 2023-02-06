<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");

		//$json = json_encode($dataTable);

    $dataX = ['Primer','Sekunder','Komplikasi'];
    $dataY = ['Primer','Sekunder','Komplikasi'];
    $data = [];
 for($i=0; $i < count($dataX); $i++){    
    $row = array(
      'rawat_icd_status_id'   => $dataX[$i],
      'rawat_icd_status'   => $dataY[$i]
    );
    $data[]=$row;
  }
    echo json_encode($data);
?>