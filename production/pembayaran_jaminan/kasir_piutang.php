<?php
     if($_GET["piutang"]){
        $_POST["pembayaran_id"] = $_GET["pembayaran_id"];
        $_POST["id_jbayar"] = "01";

        $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])."
                and fol_lunas='n'";
        $rs = $dtaccess->Execute($sql);
        $total = $dtaccess->Fetch($rs);
        
        $sql = "select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPembayaranLama = $dtaccess->Fetch($sql);
        
        $pembayaranHrsBayar = $dataPembayaranLama["pembayaran_hrs_bayar"] + $total["total"];
        
        $sql="update klinik.klinik_pembayaran set pembayaran_flag='p', pembayaran_hrs_bayar=".QuoteValue(DPE_NUMERIC,StripCurrency($pembayaranHrsBayar))." 
              where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $dtaccess->Execute($sql);
      
        $sql = "select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPembayaranPas = $dtaccess->Fetch($sql);
        
        $selisih = $dataPembayaranPas["pembayaran_total"] - $dataPembayaranPas["pembayaran_yg_dibayar"];
                
        $sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det 
                where id_pembayaran =".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $rs = $dtaccess->Execute($sql);
        $Maxs = $dtaccess->Fetch($rs);
        $MaksUrut = ($Maxs["total"]+1);
              
        $dbTable = "klinik.klinik_pembayaran_det";
        $dbField[0] = "pembayaran_det_id"; // PK
        $dbField[1] = "id_pembayaran";
        $dbField[2] = "pembayaran_det_create";
        $dbField[3] = "pembayaran_det_tgl";
        $dbField[4] = "pembayaran_det_ke";
        $dbField[5] = "pembayaran_det_total";
        $dbField[6] = "id_dep";
        $dbField[7] = "pembayaran_det_service_cash";
        $dbField[8] = "id_dokter";
        $dbField[9] = "who_when_update";
        $dbField[10] = "id_jbayar";
        $dbField[11] = "pembayaran_det_flag";
        $dbField[12] = "pembayaran_det_tipe_piutang";
        $dbField[13] = "pembayaran_det_hrs_bayar";
              
         $byrHonorId = $dtaccess->GetTransID();
         $dbValue[0] = QuoteValue(DPE_CHARKEY,$byrHonorId);
         $dbValue[1] = QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
         $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
         $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
         $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
         if($dataPembayaranPas["pembayaran_total"]<>$dataPembayaranPas["pembayaran_yg_dibayar"]){
         $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($selisih));
         } else {
         $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]));
         }
         $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
         $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtServiceCash"]));
         $dbValue[8] = QuoteValue(DPE_CHAR,$_GET["id_dokter"]);
         $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
         $dbValue[10] = QuoteValue(DPE_CHAR,'01');
         $dbValue[11] = QuoteValue(DPE_CHAR,'P');
         $dbValue[12] = QuoteValue(DPE_CHAR,'P');
         $dbValue[13] = QuoteValue(DPE_NUMERIC,$total["total"]);
     
     //print_r($dbValue); die();
         $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
         $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
         
         $dtmodel->Insert() or die("insert  error");
         
         unset($dbField);
         unset($dtmodel);
         unset($dbValue);
         unset($dbKey);
        
        $sql="select * from klinik.klinik_folio a
            join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
            where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId)."
            and fol_lunas='n'";
		 $rs = $dtaccess->Execute($sql);
     $dataFolioPas = $dtaccess->FetchAll($rs);
     
     $sql="select * from klinik.klinik_registrasi a
     left join global.global_customer_user b on a.id_cust_usr= b.cust_usr_id
     where reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $dataPas = $dtaccess->Fetch($rs);
     
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
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
      
      $sql = "select ref_tra_urut as kode from gl.gl_buffer_transaksi 
              where dept_id=".QuoteValue(DPE_CHAR,$depId)." and ref_tra like 'PENDPOST-%' 
              order by ref_tra_urut desc";
      $lastKode = $dtaccess->Fetch($sql);
      $noRef = $lastKode["kode"]+1;  
      
      if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
           $keterangan ="Jurnal Piutang Pendapatan a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
      }else{
           $keterangan ="Jurnal Piutang Pendapatan a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'PENDPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'PE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue);
      

      // update pembayaran detail
      $sqlPembdet = "update klinik.klinik_pembayaran_det set is_posting = 'y' where pembayaran_det_id = ".QuoteValue(DPE_CHAR,$byrHonorId);
      $updatePembdet = $dtaccess->Execute($sqlPembdet);
      
      //--GL POSTING UANG MUKA        
    require_once('gl_uang_muka.php');
    //-- akhir posting UM    


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
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_piutang_perorangan"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);              
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]));

//print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
                    
      for($m=0,$n=count($dataFolioPas);$m<$n;$m++){

// Pendapatan IRJ
     require('gl_pendapatan_irj.php');

      }

//POsting Biaya
     //POSTING ke GL
     
//cari yang split-nya ada angkanya
      $sql = "select a.folsplit_nominal from klinik.klinik_folio_split a
             left join klinik.klinik_folio b on a.id_fol = b.fol_id
             left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
             where c.id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and
             a.folsplit_nominal > '0'";
      $rs = $dtaccess->Execute($sql);
      $postbeban = $dtaccess->Fetch($rs);            
//       echo $sql; die();
     
     if ($postbeban["folsplit_nominal"]) {
           
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
            
      $dateEdit = date($dataPembayaranPas["pembayaran_tanggal"])." ".date("H:i:s");
      $dateReal = date("Y-m-d H:i:s");
            
      if($_POST["total_harga"]<>StripCurrency($_POST["txtDibayar"][0])){
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban Kurang Bayar a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } else {
        if($dataPas["id_cust_usr"]=="100" || $dataPas["id_cust_usr"]=="500"){
          $keterangan ="Jurnal Beban a.n ".$dataFolioPas[0]["fol_keterangan"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }else{
          $keterangan ="Jurnal Beban a.n ".$dataPas["cust_usr_nama"]." (".$dataPas["cust_usr_kode"].") 
                        Tgl ".$dataPembayaranPas["pembayaran_tanggal"];
        }
      } 

      $transaksiId = $dtaccess->GetTransId();
      $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiId);
      $dbValue[1] = QuoteValue(DPE_CHAR,'BEBANPOST'."-".$noRef);
      $dbValue[2] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$userName);
      $dbValue[5] = QuoteValue(DPE_DATE,$dateReal);
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,$noRef);
      $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
      $dbValue[9] = QuoteValue(DPE_CHAR,'BE');
 //      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");
      	                                                                
      unset($dbField);
      unset($dbValue); 




     //masukkan biaya bebannya
     for($m=0,$n=count($dataFolioPas);$m<$n;$m++){
// Pendapatan IRJ
       require('gl_posting_split.php');
        //--GL POSTING PASIEN UMUM       
       require('gl_posting_beban_umum.php');
             
     }      
      }
        
        $sql="update klinik.klinik_registrasi set reg_bayar='n' where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"]);
        $dtaccess->Execute($sql);
        
        
        $sql = "update klinik.klinik_folio set id_pembayaran_det=".QuoteValue(DPE_CHAR,$byrHonorId).", fol_lunas='y' 
                where id_pembayaran=".QuoteValue(DPE_CHAR,$_GET["pembayaran_id"])." and fol_lunas='n'";
        $dtaccess->Execute($sql);
        
        $kembali = "kasir_pemeriksaan_view.php?tgl_awal=".QuoteValue(DPE_DATE,$_POST["tgl_awal"])."&tgl_akhir=".QuoteValue(DPE_DATE,$_POST["tgl_akhir"]);
        header("location:".$kembali);
        exit();
      }
  
      //END PIUTANG
      
      
?>