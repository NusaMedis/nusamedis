<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
   
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
  if (isset($_POST["id_poli"])) {
  $sql = "select c.kategori_tindakan_nama, c.kategori_tindakan_id
      from klinik.klinik_kategori_tindakan c 
      left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header
      left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header";
  $sql .=" Where e.id_poli = ".QuoteValue(DPE_CHAR,$_POST['id_poli']);
  $sql .=" order by c.kategori_tindakan_nama asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  }
  else
  {
   echo "ngga masuk id polinya";
  }
  
  

    echo json_encode($dataTable);
?>