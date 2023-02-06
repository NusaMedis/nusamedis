<?php
  $sql = "select * from apotik.apotik_penjualan a
          left join klinik.klinik_folio b on b.fol_id = a.id_fol
          where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'])." and fol_lunas = 'n'";
  $dataFarmasi = $dtaccess->FetchAll($sql);

  for ($i = 0; $i < count($dataFarmasi); $i++) {
    $sql = "select sum(penjualan_detail_total) as penjualan_total_detail, sum(penjualan_detail_harga_beli) as hargabeli from apotik.apotik_penjualan_detail  where 
            id_penjualan = ".QuoteValue(DPE_CHAR,$dataFarmasi[$i]['penjualan_id']);
    $HargaObat = $dtaccess->Fetch($sql);

    $_POST['hargabeli'] = $HargaObat['hargabeli'];

    $keterangan ="Jurnal Penjualan Obat ( ".$dataFarmasi[$i]['penjualan_nomor']." )";

    $sql = "select * from gl.gl_buffer_transaksi where ket_tra = ".QuoteValue(DPE_CHAR,$keterangan);
    $dataGL = $dtaccess->Fetch($sql);

    if ($dataGL != '') {
      $sql = "delete from gl.gl_buffer_transaksi where id_tra = ".QuoteValue(DPE_CHAR,$dataGL['id_tra']);
      $rs = $dtaccess->Execute($sql);
    }
    
    $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$dataFarmasi[$i]['penjualan_id']);
    $dataDetail = $dtaccess->Fetch($sql);

    $dbTable = "gl.gl_buffer_transaksi";
    $dbField[0]  = "id_tra";   // PK
    $dbField[1]  = "ref_tra";   
    $dbField[2]  = "tanggal_tra"; 
    $dbField[3]  = "ket_tra";
    $dbField[4]  = "namauser";
    $dbField[5]  = "real_time";
    $dbField[6]  = "dept_id";
    $dbField[7]  = "ref_tra_urut";
    //$dbField[8]  = "id_pembayaran_det";
    $dbField[8]  = "flag_jurnal";
          
    $dateReal = date("Y-m-d H:i:s");
    
    $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
            where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AP-%' 
            order by ref_tra_urut desc";
    $lastKode = $dtaccess->Fetch($sql);
    $noRef = $lastKode["kode"]+1;  


    $transaksiId2 = $dtaccess->GetTransId();
    $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId2);
    $dbValue[1] = QuoteValue(DPE_CHAR,'AP'."-".date('ymd').$noRef);
    $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
    $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
    $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
    $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
    $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
    //$dbValue[8] = QuoteValue(DPE_CHAR,$pembDetUtama); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
    $dbValue[8] = QuoteValue(DPE_CHAR,'P');
  //      print_r($dbValue); die();
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    $dtmodel->Insert() or die("insert  error");
                                                                      
    unset($dbField);
    unset($dbValue); 

          $sql = "select * from gl.gl_konf
             where id_dept = ".QuoteValue(DPE_CHAR,$depId);
      $dataCOA = $dtaccess->Fetch($sql);

      if ($_POST['reg_tipe_rawat'] == 'J' || $_POST['reg_tipe_rawat'] == 'G') {
        $prkId = $dataCOA['dep_coa_persediaan_apotik_irj'];
        $prkIdx = $dataCOA['dep_coa_beban_apotik_irj'];
      }elseif ($_POST['reg_tipe_rawat'] == 'I') {
        $prkId = $dataCOA['dep_coa_persediaan_apotik_irna'];
        $prkIdx = $dataCOA['dep_coa_beban_apotik_inap'];
      }

          
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId2);
          $dbValue[2] = QuoteValue(DPE_CHAR,$prkId);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.$_POST['hargabeli']); // COA PERSEDIAAN GUDANG
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dbField);
          unset($dbValue);

      $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar1"]);
      $dataCOA = $dtaccess->Fetch($sql);
          
      $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";

          $transaksiDetailId = $dtaccess->GetTransId();
          
          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId2);
          $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdx); // COA HPP
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hargabeli']));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");   
            
          unset($dbField);
          unset($dbValue);
  }
?>