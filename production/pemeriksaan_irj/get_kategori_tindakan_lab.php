<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
   
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();


  # get poli pasien - paten radiologi
  $sql = "select poli_id from global.global_auth_poli";
  $sql .=" Where poli_tipe = 'L'";
  $poli = $dtaccess->Fetch($sql);

//echo $_GET['id_poli'];die;
  $sql = "select c.kategori_tindakan_nama, c.kategori_tindakan_id
      from klinik.klinik_kategori_tindakan c 
      left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header
      left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header";
  $sql .=" where e.id_poli =".QuoteValue(DPE_CHAR,$_GET['id_poli']);
  $sql .=" order by c.kategori_tindakan_nama asc";
//echo $sql;
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);

    echo json_encode($dataTable);
?>