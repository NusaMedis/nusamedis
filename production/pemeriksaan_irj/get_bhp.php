<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
               
           $sql = "select a.*,item_nama,item_id, satuan_nama from klinik.klinik_folio_pemakaian a 
           left join logistik.logistik_item b on b.item_id = a.id_item
           left join logistik.logistik_item_satuan c on b.id_satuan_jual = c.satuan_id
               where a.id_fol= '$_POST[fol_id]'"; 
     
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     $json = json_encode($dataTable);
     echo $json;
     //echo $sql;
     
?>
     