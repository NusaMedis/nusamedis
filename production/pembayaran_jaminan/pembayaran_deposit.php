<?php
       //Ambil Data Awal
       $sql = "select * from klinik.klinik_deposit_history a 
                left join klinik.klinik_deposit b on b.id_cust_usr=a.id_cust_usr 
                where a.id_cust_usr=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataDeposit = $dtaccess->Fetch($rs);
        
        $sql = "select cust_usr_kode,cust_usr_nama,cust_usr_kode from global.global_customer_user 
                where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataPasien = $dtaccess->Fetch($rs);
        
        //$_POST["deposit_history_nominal"] = $dataEdit["deposit_history_nominal"];
        $_POST["deposit_history_nominal_lama"] = $dataDeposit["deposit_history_nominal"];
        $_POST["deposit_history_nominal_sisa"] = $dataDeposit["deposit_history_nominal_sisa"];
        $_POST["deposit_history_nominal_sisa_lama"] = $dataDeposit["deposit_history_nominal_sisa"];
        $_POST["deposit_nominal"] = $dataDeposit["deposit_nominal"];
        $_POST["deposit_tgl"] = $dataDeposit["deposit_tgl"];
        $_POST["deposit_history_tgl"] = $dataDeposit["deposit_history_tgl"];
  
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
        
        $sql = "select deposit_history_no_bukti as kode from klinik.klinik_deposit_history 
                where id_dep=".QuoteValue(DPE_CHAR,$depId)." and deposit_history_no_bukti like 'TK-%' 
                order by deposit_history_when_create desc";
        $lastKode = $dtaccess->Fetch($sql);
        $kode = explode("-",$lastKode["kode"]);  
        $noBukti = "TK-".str_pad($kode[1]+1,6,"0",STR_PAD_LEFT);
        
        $ket = "Pembayaran Tagihan Nomer ... a.n ".$dataPasien["cust_usr_nama"]." (".$dataPasien["cust_usr_kode"].")";
        
        $nominal = $_POST["deposit_history_nominal_sisa_lama"]+abs($_POST["deposit_history_nominal_lama"])-StripCurrency($_POST["deposit_history_nominal"]);
        
        $_POST["id_deposit_history"] = $dtaccess->GetTransId();

        $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["id_deposit_history"]);
        $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[3] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($_POST["deposit_history_nominal"]));
        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($nominal));
        $dbValue[5] = QuoteValue(DPE_CHAR,$userName);
        $dbValue[6] = QuoteValue(DPE_CHAR,$ket);
        
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        //print_r($dbValue); die();
        
        $dtmodel->Insert() or die("insert  error");
                     
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
        
    //- AKHIR PEMBAYARAN DEPOSIT


?>