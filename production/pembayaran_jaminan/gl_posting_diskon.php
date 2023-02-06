<?php
$sql = "select * from gl.gl_konf";
$datacoaUM = $dtaccess->Fetch($sql);
if(StripCurrency($_POST["txtDiskon"])<>0){
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaUM["dep_coa_diskon"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
  
            $dtmodel->Insert() or die("insert  error");	
              
            unset($dbField);
            unset($dbValue);
          }
?>