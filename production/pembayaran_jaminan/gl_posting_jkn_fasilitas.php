<?php

      //cari coa piutang sesuai dengan tipejkn
        
      if ($dataPas["reg_jenis_pasien"]==TIPE_PASIEN_JKN_FASILITAS){ 
         $jaminanJKN = $dataPembayaranPas["pembayaran_dijamin"] + StripCurrency($_POST["inacbg_topup"]);
         
         $sql = "select * from global.global_jkn where jkn_id = ".QuoteValue(DPE_CHAR,$dataPas["reg_tipe_jkn"]);
         $rs = $dtaccess->Execute($sql);
         $prkJKN = $dtaccess->Fetch($rs);
         
        if($jaminanJKN==0){
      
          if(StripCurrency($_POST["txtDiskon"])>0){
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_subsidi"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"]-StripCurrency($_POST["txtDiskon"])));
//          print_r($dbValue);   die();
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_subsidi"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["total_biaya"]));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          } 

        }else{
          if($dataPembayaranPas["pembayaran_yg_dibayar"]>0){ //iur
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$prkJKN["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminanJKN));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
              $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue); 

            if($dataPembayaranPas["pembayaran_subsidi"]>0){
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
              $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_subsidi"]);
              $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
              $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
              $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
              $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaranPas["pembayaran_subsidi"]));
    //          print_r($dbValue);   die();
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
    
                $dtmodel->Insert() or die("insert  error");	
                
              unset($dbField);
              unset($dbValue);
            }
            
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
            
          }else{
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$prkJKN["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($jaminanJKN));
  //          print_r($dbValue);   die();
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_subsidi"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($dataPembayaranPas["pembayaran_subsidi"]));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
              $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue); 

            }
          }
      }
?>