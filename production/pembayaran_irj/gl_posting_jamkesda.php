<?php
if($dataPas["reg_jenis_pasien"]==TIPE_PASIEN_JAMKESDA) {

        $sql = "select * from global.global_jamkesda_kota where jamkesda_kota_id = ".QuoteValue(DPE_CHAR,$dataPas["id_jamkesda_kota"]);
        $dataJamkesdaProp = $dtaccess->Fetch($sql);
 
        // Hitung Total piutang Jamkesda Kota & Propinsi
        $sql = "select sum(fol_dijamin2) as total_kota, sum(fol_dijamin1) as total_prop from klinik.klinik_folio
               where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
        $dataPiutangJamkesda = $dtaccess->Fetch($sql);         
        
        // Piutang Jamkesda Kota           
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJamkesdaProp["id_prk_kota"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($kota));
//        echo"rrrrr";  print_r($dbValue); die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
  
  //Piutang Jamkesda Propinsi

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
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJamkesdaProp["id_prk_propinsi"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($prop));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
       
       

       $dataPiutangJamkesdaTotal =  $dataPiutangJamkesda["total_kota"] + $dataPiutangJamkesda["total_prop"];
       $selisihBayarJamkesda =  $dataPembayaranPas["pembayaran_total"] - $dataPiutangJamkesdaTotal;
// Pembayaran IUR PASIEN JAMKESDA
if($dataPembayaranPas["pembayaran_total"]>0){       

          if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
            $beda = $_POST["total_harga"]-StripCurrency($_POST["txtDibayar"][0]);
            
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);              
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"][0]));
  
  //print_r($dbValue); die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
              $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
            
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
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
  //print_r($dbValue); 
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
              $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
                  
          } else {
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_harga"]));
  
   
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
              $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
      
}
        
}
?>