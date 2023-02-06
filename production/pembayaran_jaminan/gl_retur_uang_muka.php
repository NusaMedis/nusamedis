<?php 
    //konfigurasi COA uang muka dan KAS      
  $sql = "select * from gl.gl_konf 
           where id_dept = ".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $datacoaUM = $dtaccess->Fetch($rs);
  $Datetime = date('Y-m-d H:i:s');
  $id_pembayaran = $_POST['pembayaran_id'];

  $sql = "SELECT sum(cast(diskon_nominal as decimal(16, 2))) as diskon 
          from ar_ap.diskon 
          where pembayaran_id = '$id_pembayaran'";
  $dataDiskon = $dtaccess->Fetch($sql);

  if ($_POST['deposit_nominal_awal'] > 0) {
    $totalPembayaran = StripCurrency($_POST['txtdibayar1']) + StripCurrency($_POST['txtdibayar2']) + StripCurrency($_POST['txtdibayar3']) + StripCurrency($_POST['deposit_nominal_awal']);
      // echo "<pre>";
      // print_r ($_POST);
      // echo "</pre>";
      // echo $totalPembayaran." > ".$_POST['txtTotalBiayaService'];
      // die();
    if ($totalPembayaran > $_POST['txtTotalBiayaService']) { // Total DIbayar lebih bsr dari Tagihan 
      //Jurnal
      // echo 'asdsads';
      // die();
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
        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["deposit_nominal_awal"])); // UM Disisi Debet seluruhnya
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

        $dtmodel->Insert() or die("insert  error"); 
          
        unset($dbField);
        unset($dbValue);

      $Tagihan = ($_POST['txtTotalBiayaService']);
      $Dibayar = StripCurrency($_POST['txtdibayar1']) + StripCurrency($_POST['txtdibayar2']) + StripCurrency($_POST['txtdibayar3']);
      $PembayaranDeposit = StripCurrency($_POST['deposit_nominal_awal']);
      $diskonSum = $dataDiskon['diskon'];
      //Excess dan Retur
      $Excess = $Tagihan - $diskonSum - $Dibayar;
      $Retur = $PembayaranDeposit - $Excess;

      // if ($pembayaran_det_pembulatan < 0) {
      //   $Pembulatan = str_replace("-", "", $pembayaran_det_pembulatan);
      // }else{
      //   $Pembulatan = $pembayaran_det_pembulatan;
      // }

      // if ($Dibayar >= $Tagihan) {
      //   $Returnyaa = $_POST['deposit_nominal_awal'];
      // }else{
      //   $Returnyaa = StripCurrency($Dibayar-$Tagihan);
      // }



      $sql = "select deposit_history_no_bukti as kode from klinik.klinik_deposit_history 
              where deposit_history_no_bukti like 'TK-%' 
              order by deposit_history_when_create desc";
      $lastKode = $dtaccess->Fetch($sql);
      $kode = explode("-",$lastKode["kode"]);  
      $noBukti = "TK-".str_pad($kode[1]+1,6,"0",STR_PAD_LEFT);

      

     
      // echo $Dibayar.'-'.$Tagihan.'+'.$Pembulatan."<br>";
      // echo $Returnyaa;
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
        $dbValue[2] = QuoteValue(DPE_CHAR,'0101010103'); // Dipaten KAS RS Untuk Retur Sisa Uang Muka
        $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
        $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
        $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
        $dbValue[6] = QuoteValue(DPE_NUMERIC,'-'.StripCurrency($Retur));
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      
        $dtmodel->Insert() or die("insert  error"); 
          
        unset($dbField);
        unset($dbValue);
      //END Jurnal

      // DEPOSIT HISTORY
      // echo $Returnyaa;die();
      
      if ($Excess > 0) {          
        $dbTable = "klinik.klinik_deposit_history";
            
          $dbField[0]  = "deposit_history_id";   // PK
          $dbField[1]  = "id_cust_usr";
          $dbField[2]  = "id_dep";
          $dbField[3]  = "deposit_history_nominal";
          $dbField[4]  = "deposit_history_nominal_sisa";
          $dbField[5]  = "deposit_history_tgl";
          $dbField[6]  = "deposit_history_when_create";
          $dbField[7]  = "deposit_history_who_create";
          $dbField[8]  = "deposit_history_ket";
          $dbField[9]  = "deposit_history_no_bukti";
          $dbField[10] = "deposit_history_flag";
          $dbField[11] = "id_jbayar";
          $dbField[12] = "id_pembayaran";

          $depositHistoryID = $dtaccess->GetTransId();
          $dbValue[0] = QuoteValue(DPE_CHAR,$depositHistoryID);
          $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['cust_usr_id']);
          $dbValue[2] = QuoteValue(DPE_CHAR,'9999999');
          $dbValue[3] = QuoteValue(DPE_NUMERIC,'-'.$Excess);
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$Retur);
          $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d'));
          $dbValue[6] = QuoteValue(DPE_DATE,$Datetime);
          $dbValue[7] = QuoteValue(DPE_CHAR,$userName);
          $dbValue[8] = QuoteValue(DPE_CHAR,"Pembayaran Tagihan1");
          $dbValue[9] = QuoteValue(DPE_CHAR,$noBukti);
          $dbValue[10] = QuoteValue(DPE_CHAR,'E');
          $dbValue[11] = QuoteValue(DPE_CHAR,'01');
          $dbValue[12] = QuoteValue(DPE_CHAR,$_POST['pembayaran_id']);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");
                       
          unset($dbField);
          unset($dtmodel);
          unset($dbValue);
          unset($dbKey);
      }
      // echo $Returnyaa;

      $dbTable = "klinik.klinik_deposit_history";
          
        $dbField[0]  = "deposit_history_id";   // PK
        $dbField[1]  = "id_cust_usr";
        $dbField[2]  = "id_dep";
        $dbField[3]  = "deposit_history_nominal";
        $dbField[4]  = "deposit_history_nominal_sisa";
        $dbField[5]  = "deposit_history_tgl";
        $dbField[6]  = "deposit_history_when_create";
        $dbField[7]  = "deposit_history_who_create";
        $dbField[8]  = "deposit_history_ket";
        $dbField[9]  = "deposit_history_no_bukti";
        $dbField[10] = "deposit_history_flag";
        $dbField[11] = "id_jbayar";
        $dbField[12] = "id_pembayaran";

        $depositHistoryID = $dtaccess->GetTransId();
        $dbValue[0] = QuoteValue(DPE_CHAR,$depositHistoryID);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['cust_usr_id']);
        $dbValue[2] = QuoteValue(DPE_CHAR,'9999999');
        $dbValue[3] = QuoteValue(DPE_NUMERIC,'-'.$Retur);
        $dbValue[4] = QuoteValue(DPE_NUMERIC,'0');
        $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d'));
        $dbValue[6] = QuoteValue(DPE_DATE,$Datetime);
        $dbValue[7] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[8] = QuoteValue(DPE_CHAR,"Retur Uang Muka");
        $dbValue[9] = QuoteValue(DPE_CHAR,$noBukti);
        $dbValue[10] = QuoteValue(DPE_CHAR,'R');
        $dbValue[11] = QuoteValue(DPE_CHAR,'01');
        $dbValue[12] = QuoteValue(DPE_CHAR,$_POST['pembayaran_id']);

        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        if ($PembayaranDeposit <> 0) {
          $dtmodel->Insert() or die("insert  error");
        }
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
      // END DEPOSIT HISTORY
    }else{
      // JURNAL
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
        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["deposit_nominal_awal"])); // UM Disisi Debet seluruhnya
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

        $dtmodel->Insert() or die("insert  error"); 
          
        unset($dbField);
        unset($dbValue);
      // END JURNAL
      // DEPOSIT HISTORY
      $sql = "select deposit_history_no_bukti as kode from klinik.klinik_deposit_history 
              where deposit_history_no_bukti like 'TK-%' 
              order by deposit_history_when_create desc";
      $lastKode = $dtaccess->Fetch($sql);
      $kode = explode("-",$lastKode["kode"]);  
      $noBukti = "TK-".str_pad($kode[1]+1,6,"0",STR_PAD_LEFT);
      
      $dbTable = "klinik.klinik_deposit_history";
          
        $dbField[0]  = "deposit_history_id";   // PK
        $dbField[1]  = "id_cust_usr";
        $dbField[2]  = "id_dep";
        $dbField[3]  = "deposit_history_nominal";
        $dbField[4]  = "deposit_history_nominal_sisa";
        $dbField[5]  = "deposit_history_tgl";
        $dbField[6]  = "deposit_history_when_create";
        $dbField[7]  = "deposit_history_who_create";
        $dbField[8]  = "deposit_history_ket";
        $dbField[9]  = "deposit_history_no_bukti";
        $dbField[10]  = "id_pembayaran";
        $dbField[11] = "deposit_history_flag";

        $PembayaranDeposit = StripCurrency($_POST['deposit_nominal_awal'] - $Returnyaa);
        $depositHistoryID = $dtaccess->GetTransId();
        $dbValue[0] = QuoteValue(DPE_CHAR,$depositHistoryID);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST['cust_usr_id']);
        $dbValue[2] = QuoteValue(DPE_CHAR,'9999999');
        $dbValue[3] = QuoteValue(DPE_NUMERIC,'-'.$_POST['deposit_nominal_awal']);
        $dbValue[4] = QuoteValue(DPE_NUMERIC,'0');
        $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d'));
        $dbValue[6] = QuoteValue(DPE_DATE,$Datetime);
        $dbValue[7] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[8] = QuoteValue(DPE_CHAR,"Pembayaran Tagihan");
        $dbValue[9] = QuoteValue(DPE_CHAR,$noBukti);
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST['pembayaran_id']);
        $dbValue[11] = QuoteValue(DPE_CHAR,'P');
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert  error");
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
      // END DEPOSIT HISTORY
    }

    $sql = "select * from klinik.klinik_deposit_history where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id'])." and deposit_history_flag = 'M' order by deposit_history_when_create desc";
    $depositMasukTerakhir = $dtaccess->Fetch($sql);

    $sql = "update klinik.klinik_deposit_history set id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id'])." where id_multipayment = ".QuoteValue(DPE_CHAR,$depositMasukTerakhir['id_multipayment']);
    $result = $dtaccess->Execute($sql);
    // die();
    $sql = "update klinik.klinik_deposit set deposit_nominal = '0' where id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_id']);
    $result = $dtaccess->Execute($sql);
  }
?>