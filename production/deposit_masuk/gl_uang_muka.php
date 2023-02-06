<?php    
      //konfigurasi COA uang muka dan KAS   
      $sql = "select * from gl.gl_konf
             where id_dept = ".QuoteValue(DPE_CHAR,$depId);
      $dataCOA = $dtaccess->Fetch($sql);

      //$total = $_POST['jbayar_jumlah1'] + $_POST['jbayar_jumlah2'] + $_POST['jbayar_jumlah3'];
      $sql = "select sum(deposit_history_nominal) as total from klinik.klinik_deposit_history where id_multipayment = ".QuoteValue(DPE_CHAR,$idMultipayment);
      $dataDeposit = $dtaccess->Fetch($sql);
      $total = $dataDeposit['total'];
          
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataCOA["dep_coa_uangmuka"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.$total);
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
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataCOA["id_prk"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
          $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
          $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah1"]));
//          print_r($dbValue);   die();
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

          $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue);

      if ($_POST['jbayar_jumlah2'] != '0') {
        $sql = "select * from global.global_jenis_bayar
               where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar2"]);
        $dataCOA2 = $dtaccess->Fetch($sql);
            
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$dataCOA2["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah2"]));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error"); 
              
            unset($dbField);
            unset($dbValue);
      }

      if ($_POST['jbayar_jumlah3'] != '0') {
        $sql = "select * from global.global_jenis_bayar
               where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar3"]);
        $dataCOA3 = $dtaccess->Fetch($sql);
            
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
            $dbValue[2] = QuoteValue(DPE_CHAR,$dataCOA3["id_prk"]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
            $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
            $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["jbayar_jumlah3"]));
  //          print_r($dbValue);   die();
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

            $dtmodel->Insert() or die("insert  error"); 
              
            unset($dbField);
            unset($dbValue);
      }

?>