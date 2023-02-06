<?php
        // echo "masuk";
          if($_POST["dep_posting_split"]=='y'){
          // echo "gak masuk";
          $sql = "select * from klinik.klinik_folio_split a
                 left join klinik.klinik_split b on a.id_split= b.split_id
                 where id_fol = ".QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"])."
                 and a.folsplit_nominal <>'0' and ( b.id_prk_kredit <> '' or id_prk_kredit is not null)";         
          
             //  echo $sql; die();
               $dataPrkFolioSplit = $dtaccess->FetchAll($sql);

     for($p=0,$q=count($dataPrkFolioSplit);$p<$q;$p++){
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

               $transaksiDetaildebetId = $dtaccess->GetTransId();

          $dbValue[0] = QuoteValue(DPE_CHAR,$transaksiDetaildebetId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$transaksiId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataPrkFolioSplit[$p]["id_prk_kredit"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($dataPrkFolioSplit[$p]["folsplit_nominal"]));
          $dbValue[7] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_poli"]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["id_instalasi"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$dataFolioPas[$m]["fol_id"]);
      //    print_r($dbValue);
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);
          unset($dataPrkFolio);

          }

        }
?>