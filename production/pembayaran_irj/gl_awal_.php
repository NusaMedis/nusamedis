<?php
 $sql = "select fol_diskon_persen_penjualan,fol_pembulatan_penjualan,fol_diskon_penjualan,fol_total_harga from  klinik.klinik_folio
			       where (fol_jenis like '%T%' or fol_jenis like '%WA%' or fol_jenis like '%R%') 
             and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and fol_lunas = 'n' and id_dep = ".QuoteValue(DPE_CHAR,$depId); 
		 $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataLaba = $dtaccess->Fetch($rs);
     
     $_POST["diskon"] = $dataLaba["fol_diskon_penjualan"];
     $_POST["diskonpersen"] = $dataLaba["fol_diskon_persen_penjualan"];
     $_POST["total"] = $dataLaba["fol_total_harga"];
     $_POST["pembulatan"] = $dataLaba["fol_pembulatan_penjualan"];  
     
      $sql = "select dep_konf_cetak_kasir from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
    	$rs_edit = $dtaccess->Execute($sql);
    	$row_edit = $dtaccess->Fetch($rs_edit);
    	$dtaccess->Clear($rs_edit);

      $_POST["dep_konf_cetak_kasir"] = $row_edit["dep_konf_cetak_kasir"]; 
      
      $sql="select * from klinik.klinik_folio a
            join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
            where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and fol_lunas='n'";
		 $rs = $dtaccess->Execute($sql);
     $dataFolioPas = $dtaccess->FetchAll($rs);
     //echo $sql; die();
     $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
     $total = $dtaccess->Fetch($sql);
     
     // cari isi pembayaran
     $sql="select * from klinik.klinik_pembayaran a
          where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPembayaranPas = $dtaccess->Fetch($rs);

     $sql="select * from klinik.klinik_registrasi a
          left join global.global_customer_user b on a.id_cust_usr= b.cust_usr_id
          where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $rs = $dtaccess->Execute($sql);
     $dataPas = $dtaccess->Fetch($rs);



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
      $dbField[8]  = "id_pembayaran_det";
      $dbField[9]  = "flag_jurnal";
      $dbField[10]  = "ref_tra_urutan";
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");

      $sql = "select pembayaran_det_kwitansi from klinik.klinik_pembayaran_det where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$pembDetUtama);
      $NoKwitansi = $dtaccess->Fetch($sql);
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'AC-%' 
              and tanggal_tra = ".QuoteValue(DPE_DATE,date('Y-m-d'))." 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
    if ($_POST['deposit_nominal_awal'] == '') {
      $totalBayar = StripCurrency($_POST['txtDibayar'][0]) + $_POST['txtDiskon'];
    }elseif ($_POST['deposit_nominal_awal'] <> '') {
      $totalBayar = StripCurrency($_POST['txtDibayar'][0]) + $_POST['deposit_nominal_awal'] + $_POST['txtDiskon'];
    }
      if($_POST["total_harga"]>$totalBayar){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Penerimaan Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".date_db($_POST['tanggal_posting'])." dengan nomor kwitansi ( ".$NoKwitansi['pembayaran_det_kwitansi']." )";
        }else{
          $keterangan ="Jurnal Penerimaan Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".date_db($_POST['tanggal_posting'])." dengan nomor kwitansi ( ".$NoKwitansi['pembayaran_det_kwitansi']." )";
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Penerimaan a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".date_db($_POST['tanggal_posting'])." dengan nomor kwitansi ( ".$NoKwitansi['pembayaran_det_kwitansi']." )";
        }else{
          $keterangan ="Jurnal Penerimaan a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".date_db($_POST['tanggal_posting'])." dengan nomor kwitansi ( ".$NoKwitansi['pembayaran_det_kwitansi']." )";
        }
      }  

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'AC'."-".date('ymd').$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$pembDetUtama); //ambil dari file insert_pembayaran_det_kassa.php saat insert klinik_pembayaran_det
    if ($_POST['reg_tipe_rawat']=='J') {
      $dbValue[9] = QuoteValue(DPE_CHAR,'PEJ'); //Flag Pendapatan Rawat Jalan
    }elseif ($_POST['reg_tipe_rawat']=='G') {
      $dbValue[9] = QuoteValue(DPE_CHAR,'PEG'); //Flag Pendapatan IGD
    }elseif ($_POST['reg_tipe_rawat']=='I') {
      $dbValue[9] = QuoteValue(DPE_CHAR,'PEI'); //Flag Pendapatan IRNA
    }
      $dbValue[10] = QuoteValue(DPE_CHAR,'AC'.$noRef);
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrHonorId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
?>