<?php
 
      //HITUNG TOTAL UANG MUKA by id_pembayaran
       $sql = "select deposit_nominal as total from  
            klinik.klinik_deposit where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
       $rs_pasien = $dtaccess->Execute($sql);
       $dataUM = $dtaccess->Fetch($sql);
       
        $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar1"]);
      //echo $sql;
      //die();
      $rs  = $dtaccess->Execute($sql);
      $coaJenisBayar = $dtaccess->Fetch($rs);   
          
      //konfigurasi COA uang muka dan KAS      
      $sql = "select * 
               from gl.gl_konf 
               where id_dept = ".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $datacoaUM = $dtaccess->Fetch($rs);
          
    if($_POST["deposit_nominal"]>0){
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_uangmuka"]);
          //$dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["deposit_nominal"]));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          // $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
       /*   
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_uangmuka"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.StripCurrency($dataUM["total"]));
          //print_r($dbValue); 
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);    */
          }

?>