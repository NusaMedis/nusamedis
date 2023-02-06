<?php
      
      $sql = "select a.*, jbayar_nama from klinik.klinik_deposit_history a 
              left join global.global_jenis_bayar b on b.jbayar_id = a.id_jbayar
              where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['id_cust_usr'])."
              and deposit_history_id = ".QuoteValue(DPE_CHAR,$_POST['id_deposit_history']);
      $dataKeterangan = $dtaccess->Fetch($sql);

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
              where ref_tra like 'AH-%' 
              and tanggal_tra = ".QuoteValue(DPE_DATE,date('Y-m-d'))." 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  

      $keterangan ="Jurnal ".$dataKeterangan["deposit_history_ket"]." 
                    Tgl ".date_db($dataKeterangan["deposit_history_tgl"]);

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'AH'."-".date('ymd').$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,'UM');
      $dbValue[9] = QuoteValue(DPE_CHAR,$idMultipayment); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
                                                                        
      unset($dbField);
      unset($dbValue); 
?>