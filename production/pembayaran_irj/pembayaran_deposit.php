<?php
       //Ambil Data Awal
       $sql = "select * from  klinik.klinik_deposit
                where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataDeposit = $dtaccess->Fetch($rs);
        
        $sql = "select cust_usr_kode,cust_usr_nama,cust_usr_kode from global.global_customer_user 
                where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPasien = $dtaccess->Fetch($rs);   
        
        $_POST["deposit_history_nominal_sisa"] = $dataDeposit["deposit_nominal"];
        //$_POST["deposit_nominal"] = $dataDeposit["deposit_nominal"];
       
       //Jika Biaya Pembayaran < dari Deposit maka jumlah deposit yang dikurangi adalah jumlah pembayarann 
       if ($_POST["txtTotalBiayaService"]<$_POST["deposit_nominal"])
        {
          $_POST["deposit_nominal"] = (StripCurrency($_POST["txtTotalBiayaService"])-StripCurrency($_POST["txtDiskon"]));
        } 
  
  
      //insert deposit
      if($_POST["deposit_nominal"]){
        $dbTable = "klinik.klinik_deposit_history";
        $dbField[0] = "deposit_history_id";
        $dbField[1] = "id_cust_usr";
        $dbField[2] = "id_dep";
        $dbField[3] = "deposit_history_nominal";
        $dbField[4] = "deposit_history_nominal_sisa";
        $dbField[5] = "deposit_history_who_create";
        $dbField[6] = "deposit_history_ket";
        $dbField[7] = "deposit_history_tgl";
        $dbField[8] = "deposit_history_when_create";
        $dbField[9] = "deposit_history_no_bukti";
        
        $sql = "select deposit_history_no_bukti as kode from klinik.klinik_deposit_history 
                where id_dep=".QuoteValue(DPE_CHAR,$depId)." and deposit_history_no_bukti like 'TK-%' 
                order by deposit_history_when_create desc";
        $lastKode = $dtaccess->Fetch($sql);
        $kode = explode("-",$lastKode["kode"]);  
        $noBukti = "TK-".str_pad($kode[1]+1,6,"0",STR_PAD_LEFT);
        
        $ket = $_POST["pembayaran_det_ket"]."( ".date("d-m-Y").") a.n ".$dataPasien["cust_usr_nama"]." (".$dataPasien["cust_usr_kode"].")";
        $nominalSisa = $_POST["deposit_history_nominal_sisa"]-StripCurrency($_POST["deposit_nominal"]);
        
        $_POST["id_deposit_history"] = $dtaccess->GetTransId();

        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_deposit_history"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["deposit_nominal"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($nominalSisa));
        $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[6] = QuoteValue(DPE_CHAR,$ket);
        $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d"));
        $dbValue[8] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
        $dbValue[9] = QuoteValue(DPE_CHAR,$noBukti);
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        //print_r($dbValue); die();
        
        // $dtmodel->Insert() or die("insert  error");
                     
        unset($dbField);
        unset($dtmodel);
        unset($dbValue);
        unset($dbKey);
     } //AKHIR INSERT DEPOSIT 


    //---UPDATE SALDO KLINIK PEMBAYARAN DEPOSIT
    $sql = "select sum(deposit_history_nominal) as total from klinik.klinik_deposit_history where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
    $rs = $dtaccess->Execute($sql);
    $total = $dtaccess->Fetch($rs);
        
    $sql = "update klinik.klinik_deposit set deposit_nominal=".QuoteValue(DPE_NUMERIC,StripCurrency($total["total"]))."
            where id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
    $dtaccess->Execute($sql);
    
    $sql = "update klinik.klinik_pembayaran_det set pembayaran_det_deposit =".QuoteValue(DPE_NUMERIC,StripCurrency($_POST["deposit_nominal"])).", 
            id_deposit_history=".QuoteValue(DPE_CHAR,$_POST["id_deposit_history"])."
            where pembayaran_det_id=".QuoteValue(DPE_CHAR,$pembDetUtama);
    $dtaccess->Execute($sql);
        
    //- AKHIR PEMBAYARAN DEPOSIT
    

    
    // INSERT LAPORAN PEMBAYARAN DET
    
 /*
      $pembDetTotal = $_POST["total_harga"]+$_POST["deposit_nominal"];  //5
      $pembDetServiceCharge = $_POST["txtServiceCash"]; //7  
      $pembDetFlag = "U"; //7   
      $pembDetDibayar = $_POST["deposit_nominal"];  //13
      $pembDetPiutang = "U"; //14 
      $pembDetHrsBayar = StripCurrency($_POST["total_biaya"]) + StripCurrency($_POST["txtServiceCash"]) - StripCurrency($_POST["txtDiskon"]); //17
      $pembDetBiayaPembulatan = $_POST["txtBiayaPembulatan"]; //18  


      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "id_dokter";
      $dbField[8] = "who_when_update";
      $dbField[9] = "id_jbayar";
      $dbField[10] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[12] = "pembayaran_det_dibayar";
      $dbField[13] = "pembayaran_det_tipe_piutang";
      $dbField[14] = "pembayaran_det_hrs_bayar";
      $dbField[15] = "id_reg";
      $dbField[16] = "pembayaran_det_slip";
      $dbField[17] = "pembayaran_det_ket";

      
       $pembDetId = $dtaccess->GetTransID();
       $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       $dbValue[2] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
       $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));                                
       $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
       $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotal));
       $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
       $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
       $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar1"]);
       $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
       $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtdibayar1"]));
       $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
       $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
       $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
       $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
       $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["pembayaran_det_ket"]);
      
       
     //  print_r($dbValue); die();
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       
       $dtmodel->Insert() or die("insert  error");
       
       unset($dbField);
       unset($dtmodel);
       unset($dbValue);
       unset($dbKey);     
 
 
 
 */   
    
    
    // AKHIR INSERT PEMBAYARAN DET


?>