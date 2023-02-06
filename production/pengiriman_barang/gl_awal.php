<?php     
      $sql = "select dep_konf_cetak_kasir from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_cetak_kasir"] = $row_edit["dep_konf_cetak_kasir"]; 

      if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]=="1") $dataPas["reg_tipe_layanan"]="2";
     if(!$dataPas["reg_tipe_layanan"] && $dataPas["reg_kelas"]<>"1") $dataPas["reg_tipe_layanan"]="1";
          
      $dbTable = "gl.gl_buffer_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";   
      $dbField[2]  = "tanggal_tra"; 
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "ref_tra_urut";
      // $dbField[8]  = "id_pembayaran_det";
      $dbField[8]  = "flag_jurnal";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AF-%' 
              and tanggal_tra = ".QuoteValue(DPE_DATE,$dataTransfer['transfer_tanggal_keluar'])." 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      $Reff = date('ymd',strtotime($dataTransfer['transfer_tanggal_keluar']));
      $keterangan = "Jurnal Mutasi Obat (".$dataTransfer['transfer_nomor'].")";

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'AF'."-".$Reff.$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dataTransfer['transfer_tanggal_keluar']);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      // $dbValue[8] = QuoteValue(DPE_CHAR,$pembDetUtama); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
      $dbValue[8] = QuoteValue(DPE_CHAR,'MO');
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 
?>