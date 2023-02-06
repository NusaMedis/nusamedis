<?php
           
    $sql = "select * from klinik.klinik_biaya
           where biaya_id = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_biaya"]);
   $dataPrkFolio = $dtaccess->Fetch($sql);

if($dataPrkFolio["biaya_jenis_sem"]<>'O'){

          $dbTable = "gl.gl_buffer_transaksidetil";
          
          $dbField[0]  = "id_trad";   // PK
          $dbField[1]  = "tra_id";
          $dbField[2]  = "prk_id";
          $dbField[3]  = "ket_trad";
          $dbField[4]  = "job_id";
          $dbField[5]  = "dept_id";
          $dbField[6]  = "jumlah_trad";
          $dbField[7]  = "id_poli";
          $dbField[8]  = "id_instalasi";
          $dbField[9]  = "id_fol";

               $transaksiDetailId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolio["id_prk_beban"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency(abs($dataFolioPas[$m]["fol_nominal"])));
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
      //    print_r($dbValue);    die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);
        
        
    } 

?>