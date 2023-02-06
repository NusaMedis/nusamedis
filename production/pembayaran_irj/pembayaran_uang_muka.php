<?php

    //---UPDATE KLINIK PEMBAYARAN UANG MUKA
    $sql = "update klinik.klinik_pembayaran_uangmuka set uangmuka_tgl_lunas=".QuoteValue(DPE_DATE,date("Y-m-d"))." 
            where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $dtaccess->Execute($sql);
    //- AKHIR PEMBAYARAN UANG MUKA
   
    $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $uangMuka = $dtaccess->Fetch($rs);
    
    $bayar = $dataPembayaran["pembayaran_yg_dibayar"] - $uangMuka["total"];
  
          //insert pelunasan uangmuka
          if($uangMuka){
            $dbTable = "klinik.klinik_pembayaran_uangmuka";
            $dbField[0] = "uangmuka_id";
            $dbField[1] = "id_reg";
            $dbField[2] = "id_pembayaran";
            $dbField[3] = "uangmuka_jml";
            $dbField[4] = "uangmuka_tgl";
            $dbField[5] = "id_jbayar";
            $dbField[6] = "who_update";
            $dbField[7] = "uangmuka_tgl_lunas";
            $dbField[8] = "id_pembayaran_det";
            
            $uangmukaId = $dtaccess->GetTransID();
            $dbValue[0] = QuoteValue(DPE_CHAR,$uangmukaId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
            $dbValue[3] = QuoteValue(DPE_NUMERIC,"-".StripCurrency($uangMuka["total"]));
            $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_jbayar"]);
            $dbValue[6] = QuoteValue(DPE_CHAR,$userName);
            $dbValue[7] = QuoteValue(DPE_DATE,date("Y-m-d"));
            $dbValue[8] = QuoteValue(DPE_CHAR,$byrHonorId);
            
            $dbKey[0] = 0;
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
                     
            $dtmodel->Insert() or die("insert  error");
             
            unset($dbField);
            unset($dtmodel);
            unset($dbValue);
            unset($dbKey);
          }

?>