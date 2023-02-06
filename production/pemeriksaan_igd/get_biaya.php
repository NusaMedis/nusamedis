<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."login.php");
     require_once($LIB."currency.php");
   
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
    $auth = new CAuth();
     
     $depId = $auth->GetDepId();
     
     // Data Kategori Tindakan Header Instalasi//
     $sql = "select dep_konf_reg_kelas_igd, dep_konf_tarif_jenis_pasien, dep_konf_header_klinik from  global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataKelasTarif = $dtaccess->Fetch($rs);
     
      $data = [];
		 
	$sql = "select a.biaya_tarif_id, a.biaya_total,a.id_kelas, b.biaya_nama, a.is_cito
			from klinik.klinik_biaya_tarif a 
			left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id
      left join klinik.klinik_kategori_tindakan c on b.biaya_kategori = c.kategori_tindakan_id
      left join klinik.klinik_kategori_tindakan_header d on d.kategori_tindakan_header_id = c. id_kategori_tindakan_header";
      if ($dataKelasTarif["dep_konf_header_klinik"]=='y') {
        $sql .=" left join klinik.klinik_biaya_poli e on d.kategori_tindakan_header_id = e.id_kategori_tindakan_header";
      }
    $sql .=" where ".QuoteValue(DPE_CHAR,date("Y-m-d"))." >= a.biaya_tarif_tgl_awal";
    $sql .=" and ".QuoteValue(DPE_CHAR,date("Y-m-d"))."<= a.biaya_tarif_tgl_akhir";
    
    if ($dataKelasTarif["dep_konf_header_klinik"]=='y') {
      $sql .=" and e.id_poli = ".QuoteValue(DPE_CHAR,$_POST['id_poli']);
    }
    if ($dataKelasTarif["dep_konf_tarif_jenis_pasien"]=='y') {
       $sql .=" and a.id_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST['id_jenis_pasien']);
    }
   
    //$sql .=" and a.id_kelas = ".QuoteValue(DPE_CHAR,'4023d2b9644b5c0ec1090d1dc0c60aa3'); //kelas 2 / pasien umum
    $sql .=" and a.id_kelas = ".QuoteValue(DPE_CHAR,$dataKelasTarif["dep_konf_reg_kelas_igd"]); //NON CLASS
    //$sql .=" and a.id_kelas = ".QuoteValue(DPE_CHAR,'cab7daf49124e163135e8966dbf5f32c'); //kelas 3
    $sql .=" and UPPER(b.biaya_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST['q'])."%");
//	  $sql .=" and b.biaya_jenis_sem is NULL";
    $sql .=" order by b.biaya_nama asc";

    
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
	//$data = [];

    for($i=0; $i < count($dataTable); $i++){    

      if ($dataTable[$i]['is_cito'] == 'C') { $cito = " ( CITO )"; } else { $cito = ""; }

      $data[] = array(
          'biaya_tarif_id'   => $dataTable[$i]['biaya_tarif_id'],
          'biaya_total'   => currency_format($dataTable[$i]['biaya_total']),
          'biaya_nama'   => $dataTable[$i]['biaya_nama'].$cito
        );
    }

    echo json_encode($data);
?>