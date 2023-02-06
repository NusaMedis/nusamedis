<?php    
      //konfigurasi COA uang muka dan KAS   
      $sql = "select * from gl.gl_konf
             where id_dept = ".QuoteValue(DPE_CHAR,$depId);
      $dataCOA = $dtaccess->Fetch($sql);

      if ($theDep == '2') {
        $prkId = $dataCOA['dep_coa_persediaan_apotik_irj'];
        $prkIdx = $dataCOA['dep_coa_beban_apotik_irj'];
      }elseif ($theDep == '3') {
        $prkId = $dataCOA['dep_coa_persediaan_apotik_irna'];
        $prkIdx = $dataCOA['dep_coa_beban_apotik_inap'];
      }

          
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$prkId);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.$_POST['hargabeli']); // COA PERSEDIAAN GUDANG
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error"); 
            
          unset($dbField);
          unset($dbValue);

      $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar1"]);
      $dataCOA = $dtaccess->Fetch($sql);
          
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$prkIdx); // COA HPP
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hargabeli']));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);

?>