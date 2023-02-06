<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
        
     if( isset($_POST['id_reg']) ){
     $sql = "select id_pembayaran from klinik.klinik_registrasi where reg_id = '$_POST[id_reg]'";
     $dataPembayaran = $dtaccess->Fetch($sql);
     $sql = "select a.fol_id,a.is_transfer, a.tindakan_tanggal, a.tindakan_waktu, a.id_biaya, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah,
         b.is_cito,
         d.rawat_tindakan_id,
         f.biaya_nama
      from klinik.klinik_folio a                                       
      left join klinik.klinik_biaya_tarif b on a.id_biaya_tarif = b.biaya_tarif_id
      left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
      left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
      where a.id_pembayaran = '$dataPembayaran[id_pembayaran]' and a.id_biaya != ''
      "; 
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  $data = [];

    for($i=0; $i < count($dataTable); $i++){    

      if ($dataTable[$i]['is_cito'] == 'C') { $cito = " ( CITO )"; } else { $cito = ""; }

      array_push($data, [
          'fol_id'   => $dataTable[$i]['fol_id'],
          'id_reg'   => $dataTable[$i]['id_reg'],
          'id_biaya'   => $dataTable[$i]['id_biaya'],
          'tindakan_tanggal'   => format_date($dataTable[$i]['tindakan_tanggal']),
          'tindakan_waktu'   => $dataTable[$i]['tindakan_waktu'],
          'id_biaya_tarif'   => $dataTable[$i]['id_biaya_tarif'],
          'fol_lunas'   => $dataTable[$i]['fol_lunas'],
          'fol_jumlah'   => $dataTable[$i]['fol_jumlah'],
          'is_cito'   => $dataTable[$i]['is_cito'],
          'rawat_tindakan_id'   => $dataTable[$i]['rawat_tindakan_id'],
          'is_transfer'   => $dataTable[$i]['is_transfer'],
          'biaya_nama'   => $dataTable[$i]['biaya_nama'].$cito
        ]);
    }

    echo json_encode($data);
  //echo $sql;
  } 
  
?>
  