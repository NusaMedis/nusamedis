<?php
      $keterangan ="Jurnal Penjualan Obat ( ".$_POST['penjualan_no']." )";

      $sql = "select * from gl.gl_buffer_transaksi where id_pembayaran_det = ".QuoteValue(DPE_CHAR,$_POST['penjualan_id']);
      $dataGL = $dtaccess->Fetch($sql);

      if ($dataGL != '') {
        $sql = "delete from gl.gl_buffer_transaksi where id_tra = ".QuoteValue(DPE_CHAR,$dataGL['id_tra']);
        $rs = $dtaccess->Execute($sql);
      }
      
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
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
      $dbField[8]  = "flag_jurnal";
      $dbField[9]  = "id_pembayaran_det";
            
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AP-%' 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      if ($theDep == '2') {
        $flag = 'PG';
      }elseif ($theDep == '3') {
        $flag = 'PR';
      }


      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'AP'."-".date('ymd').$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$flag);
      $dbValue[9] = QuoteValue(DPE_CHAR,$_POST['penjualan_id']); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
                                                                        
      unset($dbField);
      unset($dbValue); 
?>