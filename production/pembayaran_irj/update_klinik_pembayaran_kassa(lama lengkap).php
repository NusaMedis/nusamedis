<?php

/* List Jenis Pasien
DEFINE("TIPE_PASIEN_ASKES","1");
DEFINE("TIPE_PASIEN_UMUM","2");
DEFINE("TIPE_PASIEN_JKN","5");
DEFINE("TIPE_PASIEN_IKS","7");
DEFINE("TIPE_PASIEN_PROGRAM","8");
DEFINE("TIPE_PASIEN_ASURANSI","10");
DEFINE("TIPE_PASIEN_TIDAK_MEMBAYAR","15");
DEFINE("TIPE_PASIEN_JAMKESMAS","16");
DEFINE("TIPE_PASIEN_JAMKESDA","18");
DEFINE("TIPE_PASIEN_SKTM","19");                           
DEFINE("TIPE_PASIEN_FASILITAS","20");
DEFINE("TIPE_PASIEN_ASKES_FASILITAS","21");
DEFINE("TIPE_PASIEN_PKMS_SILVER","22");
DEFINE("TIPE_PASIEN_PKMS_GOLD","23");

                           
*/               
//echo "Diskon ".$_POST["txtDiskon"];  die();
    //AMBIL DAHULU DATA-DATA YANG DIBUTUHKAN
    //ambil dulu yang lama 
    $sql = "select pembayaran_yg_dibayar, pembayaran_total, pembayaran_dijamin, pembayaran_hrs_bayar, pembayaran_diskon, pembayaran_diskon_persen, 
            pembayaran_service_cash, pembayaran_pembulatan, pembayaran_subsidi from klinik.klinik_pembayaran
            where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $DataPembayaranLama = $dtaccess->Fetch($rs); 
   // echo $sql."<br>"; 
    //echo $_POST["txtDibayar"][0]."<br>";
    //echo $_POST["reg_jenis_pasien"]." - ".TIPE_PASIEN_UMUM."<br>";  
   
    // UPDATE KLINIK PEMBAYARAN//    
    //Per Jenis Pasien
    if($_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) //1
    {
    
    
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_UMUM) //2
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      } else {
      $Total=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      $_POST["total_harga"] = StripCurrency($_POST["total_harga"]);
      }
      
      //pembayaran yg dibayar
      $Dibayar = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
           //   echo "<br> total biaya ".$_POST["total_biaya"]."<br> terima bayar ".$_POST["txtDibayar"][0];     
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
           //  echo "<br> harus bayar ".$DataPembayaranLama["pembayaran_hrs_bayar"]."<br> dibayar ".$Dibayar;
      if ($HrsBayar<0) $HrsBayar=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $Dibayar = $Dibayar + $_POST["uangmuka"];
      $Total = $Total + $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $Dibayar = $_POST["uangmuka"] - $Dibayar;
      $Total = $_POST["uangmuka"] - $Total;
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);

       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }
       $pembayaranTotal=StripCurrency($Total);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if(StripCurrency($_POST["txtDibayar"][0])>$_POST["total_harga"]){
       $pembayaranYgDibayar=StripCurrency($Total);
       } else {
       $pembayaranYgDibayar=StripCurrency($Dibayar);
       }
       $pembayaranSubsidi=0;
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=0;
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = 0;
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }                                                 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN)  //5
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      } else {
      $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      }
      
      //pembayaran dijamin
      $Dijamin=$DataPembayaranLama["pembayaran_dijamin"]+$_POST["inacbg_topup"];
      
      //pembayaran yg dibayar
      $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      //pembayaran selisih
      if($Dijamin > $_POST["total_biaya"]){
      $Selisih = $Dijamin - $_POST["total_biaya"];
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $_POST["total_biaya"] - $Dijamin;
      }
      
      //pembayaran subsidi
      $Subsidi = $_POST["total_biaya"] - $Dijamin;
      if($Subsidi<0) $Subsidi=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       $pembayaranFlag='n';
       if($Dijamin=0){
       $pembayaranTotal=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]){
       $pembayaranTotal=0;
       } else {
       $pembayaranTotal=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($Dijamin=0){
       $pembayaranYgDibayar=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]) {
       $pembayaranYgDibayar=0;
       } else {
       $pembayaranYgDibayar=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranSubsidi=StripCurrency($Subsidi);
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin > $_POST["total_biaya"]){
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=StripCurrency($Selisih);
       } else {
       $pembayaranSelisihPositif=0;
       $pembayaranSelisihNegatif=StripCurrency($Selisih);
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       if($DataPembayaranLama["pembayaran_dijamin"]>0){
       $pembayaranDijamin = StripCurrency($DataPembayaranLama["pembayaran_dijamin"]);
       } else {
       $pembayaranDijamin = 0;
       }
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    } 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_IKS)  //7
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      //echo "0"; die();
      } elseif($_POST["txtDiskonPersen"]==0 || $_POST["txtDiskon"]==0 || $_POST["txtServiceCash"]==0 || $_POST["txtBiayaPembulatan"]==0) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      //echo "a"; die();
      } else {
      $Total=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      $_POST["total_harga"] = StripCurrency($_POST["total_harga"]);
      //echo "1"; die();                                                                                                                                                                                                                                                                                                                                                                                   
      }
      
      //pembayaran dijamin
      if($_POST["perusahaan_plafon"]>0){
        if($_POST["total_biaya"]>=$_POST["perusahaan_plafon"]){
          $Dijamin = $DataPembayaranLama["pembayaran_dijamin"]+$_POST["perusahaan_plafon"];
        } else {
          $Dijamin = $DataPembayaranLama["pembayaran_dijamin"]+($_POST["total_biaya"] - StripCurrency($_POST["txtDiskon"]));
        }
      } else {
        if(StripCurrency($_POST["txtDiskon"])>0){
          $Dijamin = $dataPembayaranLama["pembayaran_dijamin"] + (StripCurrency($_POST["total_biaya"])-StripCurrency($_POST["txtDiskon"]));
        } else { 
          $Dijamin = $dataPembayaranLama["pembayaran_dijamin"] + StripCurrency($_POST["total_biaya"]);
        }
      }
      
      //pembayaran yg dibayar
      $Dibayar = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      //pembayaran selisih
      if($Dijamin > $HrsBayar){
      $Selisih = $Dijamin - $HrsBayar;
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $HrsBayar - $Dijamin;
      }
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $Dibayar = $Dibayar + $_POST["uangmuka"];
      $Total = $Total + $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $Dibayar = $_POST["uangmuka"] - $Dibayar;
      $Total = $_POST["uangmuka"] - $Total;
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["perusahaan_plafon"]>0){
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }} else {
       $pembayaranFlag='n';
       }
       $pembayaranTotal=StripCurrency($Total);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($_POST["total_harga"]<StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranYgDibayar=StripCurrency($Total);
       } else {
       $pembayaranYgDibayar=StripCurrency($Dibayar);
       }
       $pembayaranSubsidi=0;
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin > $HrsBayar){
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=StripCurrency($Selisih);
       } else {
       $pembayaranSelisihPositif=0;
       $pembayaranSelisihNegatif=StripCurrency($Selisih);
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = StripCurrency($Dijamin);
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    } 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PROGRAM) //8
    {                                                                  
    
    }                                                 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASURANSI)  //10
    {
    
    } 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_TIDAK_MEMBAYAR) //15
    {
    
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JAMKESMAS)  //16
    {                                                                          
    
    } 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JAMKESDA) //18
    {                                                                  
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      } else {
      $Total=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      $_POST["total_harga"] = StripCurrency($_POST["total_harga"]);
      }
      
      //pembayaran dijamin
      if($_POST["operasi"]) {
        if($_POST["jamkesda_kota_plafon_operasi_kota"]>0 || $_POST["jamkesda_kota_plafon_operasi_prop"]>0){
          $Dijamin = $_POST["jamkesda_kota_plafon_operasi_kota"] + $_POST["jamkesda_kota_plafon_operasi_prop"];
        } else {
          $Dijamin = $_POST["total_dijamin"];
        }
       } else{
        if($_POST["jamkesda_kota_plafon_kota"]>0 || $_POST["jamkesda_kota_plafon_prop"]>0){
          $Dijamin = $_POST["jamkesda_kota_plafon_kota"] + $_POST["jamkesda_kota_plafon_prop"];
        } else {
          $Dijamin = $_POST["total_dijamin"];
        }
      }
      
      //pembayaran yg dibayar
      $Dibayar = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      //pembayaran selisih
      if($Dijamin > $_POST["total_biaya"]){
      $Selisih = $Dijamin - $_POST["total_biaya"];
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $_POST["total_biaya"] - $Dijamin;
      }
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $Dibayar = $Dibayar + $_POST["uangmuka"];
      $Total = $Total + $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $Dibayar = $_POST["uangmuka"] - $Dibayar;
      $Total = $_POST["uangmuka"] - $Total;
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["total_harga"]>0){
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }} else {
       $pembayaranFlag='n';
       }
       $pembayaranTotal=StripCurrency($Total);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($_POST["total_harga"]<StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranYgDibayar=StripCurrency($Total);
       } else {
       $pembayaranYgDibayar=StripCurrency($Dibayar);
       }
       $pembayaranSubsidi=0;
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin > $_POST["total_biaya"]){
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=StripCurrency($Selisih);
       } else {
       $pembayaranSelisihPositif=0;
       $pembayaranSelisihNegatif=StripCurrency($Selisih);
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = StripCurrency($Dijamin);
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }                                                 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_SKTM)  //19
    {
    
    } 
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_FASILITAS) //20
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      //echo "0"; die();
      } elseif($_POST["txtDiskonPersen"]==0 || $_POST["txtDiskon"]==0 || $_POST["txtServiceCash"]==0 || $_POST["txtBiayaPembulatan"]==0) {
      $Total=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"]; 
      $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]); 
      //echo "a"; die();
      } else {
      $Total=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      $_POST["total_harga"] = StripCurrency($_POST["total_harga"]);
      //echo "1"; die();                                                                                                                                                                                                                                                                                                                                                                                   
      }
      
      //pembayaran yg dibayar
      $Dibayar = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $Dibayar = $Dibayar+ $_POST["uangmuka"];
      $Total= $Total+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $Dibayar = $_POST["uangmuka"] - $Dibayar;
      $Total= $_POST["uangmuka"] - $Total;
      }
      
      if($_POST["pagu_fasilitas"]>0){
        if($_POST["total_biaya"]>$_POST["pagu_fasilitas"]){
          $Subsidi = $dataPembayaranLama["pembayaran_subsidi"] + StripCurrency($_POST["pagu_fasilitas"]);
        } else {
          $Subsidi = $dataPembayaranLama["pembayaran_subsidi"] + (StripCurrency($_POST["total_biaya"])-StripCurrency($_POST["txtDiskon"]));
        }
      } else {
        if(StripCurrency($_POST["txtDiskon"])>0){
          $Subsidi = $dataPembayaranLama["pembayaran_subsidi"] + (StripCurrency($_POST["total_biaya"])-StripCurrency($_POST["txtDiskon"]));
        } else { 
          $Subsidi = $dataPembayaranLama["pembayaran_subsidi"] + StripCurrency($_POST["total_biaya"]);
        }
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }
       $pembayaranTotal=StripCurrency($Total);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranYgDibayar=StripCurrency($Dibayar);
       } else {
       $pembayaranYgDibayar=StripCurrency($Total);
       }
       $pembayaranSubsidi=StripCurrency($Subsidi);
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=0;
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = 0;
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_FASILITAS) //21
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      } else {
      $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      }
      
      //pembayaran dijamin
      $Dijamin=$DataPembayaranLama["pembayaran_dijamin"]+$_POST["inacbg_topup"];
      
      //pembayaran yg dibayar
      $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      //pembayaran selisih
      if($Dijamin > $_POST["total_biaya"]){
      $Selisih = $Dijamin - $_POST["total_biaya"];
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $_POST["total_biaya"] - $Dijamin;
      }
      
      //pembayaran subsidi
      $Subsidi = $_POST["total_biaya"] - $Dijamin;
      if($Subsidi<0) $Subsidi=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       $pembayaranFlag='n';
       if($Dijamin=0){
       $pembayaranTotal=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]){
       $pembayaranTotal=0;
       } else {
       $pembayaranTotal=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($Dijamin=0){
       $pembayaranYgDibayar=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]) {
       $pembayaranYgDibayar=0;
       } else {
       $pembayaranYgDibayar=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranSubsidi=StripCurrency($Subsidi);
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin > $_POST["total_biaya"]){
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=StripCurrency($Selisih);
       } else {
       $pembayaranSelisihPositif=0;
       $pembayaranSelisihNegatif=StripCurrency($Selisih);
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       if($DataPembayaranLama["pembayaran_dijamin"]>0){
       $pembayaranDijamin = StripCurrency($DataPembayaranLama["pembayaran_dijamin"]);
       } else {
       $pembayaranDijamin = 0;
       }
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PKMS_SILVER) //22
    {
      
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PKMS_GOLD) //23
    {
    
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JASA_RAHARJA) //24
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      } else {
      $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      }
      
      //pembayaran yg dibayar
      $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      }
      
      if($_POST["pagu_jasa_raharja"]>0){
        if($_POST["total_biaya"]>$_POST["pagu_jasa_raharja"]){
          $Dijamin = $DataPembayaranLama["pembayaran_dijamin"]+$_POST["pagu_jasa_raharja"];
        } else {
          $Dijamin = $DataPembayaranLama["pembayaran_dijamin"]+($_POST["total_biaya"] - StripCurrency($_POST["txtDiskon"]));
        }
      } else {
      $Dijamin = $dataPembayaranLama["pembayaran_dijamin"];
      }
      
      //pembayaran selisih
      if($Dijamin > $HrsBayar){
      $Selisih = $Dijamin - $HrsBayar;
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $HrsBayar - $Dijamin;
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }
       $pembayaranTotal=StripCurrency($_POST["total_harga"]);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranYgDibayar=StripCurrency($_POST["txtDibayar"][0]);
       } else {
       $pembayaranYgDibayar=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranSubsidi=0;
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin>$HrsBayar){
       $pembayaranSelisihNegatif=$DataPembayaranLama["pembayaran_selisih_negatif"];
       $pembayaranSelisihPositif=$DataPembayaranLama["pembayaran_selisih_positif"]+StripCurrency($Selisih);
       } else {
       $pembayaranSelisihNegatif=$DataPembayaranLama["pembayaran_selisih_negatif"]+StripCurrency($Selisih);
       $pembayaranSelisihPositif=$DataPembayaranLama["pembayaran_selisih_positif"];
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = StripCurrency($Dijamin);
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }
    else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PAKET) //25
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      } else {
      $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      }
      
      //pembayaran yg dibayar
      $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranFlag='p';
       } else {
       $pembayaranFlag='y';
       }
       $pembayaranTotal=StripCurrency($_POST["total_harga"]);
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($_POST["total_harga"]>StripCurrency($_POST["txtDibayar"][0])){
       $pembayaranYgDibayar=StripCurrency($_POST["txtDibayar"][0]);
       } else {
       $pembayaranYgDibayar=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranSubsidi=0;
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=0;
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       $pembayaranDijamin = 0;
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }
        else if ($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_JASA_RAHARJA)  //26
    {
      if($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
      $_POST["total_harga"]=StripCurrency($_POST["txtTotalDibayar"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      } else {
      $_POST["total_harga"]=StripCurrency($_POST["total_harga"])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      }
      
      //pembayaran dijamin
      $Dijamin=$DataPembayaranLama["pembayaran_dijamin"]+$_POST["inacbg_topup"];
      
      //pembayaran yg dibayar
      $_POST["txtDibayar"][0] = StripCurrency($_POST["txtDibayar"][0])+$DataPembayaranLama["pembayaran_yg_dibayar"];
      
      //pembayaran hrs bayar
      $HrsBayar=$DataPembayaranLama["pembayaran_hrs_bayar"]+$_POST["total_biaya"];
      if ($HrsBayar<0) $HrsBayar=0;
      
      //pembayaran selisih
      if($Dijamin > $_POST["total_biaya"]){
      $Selisih = $Dijamin - $_POST["total_biaya"];
      } else  {
      //$pembayaranHrsBayar=$_POST["total_harga"] ;
      $Selisih = $_POST["total_biaya"] - $Dijamin;
      }
      
      //pembayaran subsidi
      $Subsidi = $_POST["total_biaya"] - $Dijamin;
      if($Subsidi<0) $Subsidi=0;
      
      if($_POST["uangmuka"]>0 && $_POST["retur"]==0) {
      $_POST["txtDibayar"][0] = $_POST["txtDibayar"][0]+ $_POST["uangmuka"];
      $_POST["total_harga"]= $_POST["total_harga"]+ $_POST["uangmuka"];
      } elseif($_POST["uangmuka"]>0 && $_POST["retur"]>0) {
      $_POST["txtDibayar"][0] = $_POST["uangmuka"] - $_POST["txtDibayar"][0];
      $_POST["total_harga"]= $_POST["uangmuka"] - $_POST["total_harga"];
      }
      
      //pembayaran diskon, diskon persen, service cash
      $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
      $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
      $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
      $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);
      
       //Masukkan semua datanya
       $pembayaranWhoDokter=$Doktere["usr_name"];
       $pembayaranTanggal=date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
       $pembayaranCreate=date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
       $pembayaranFlag='n';
       if($Dijamin=0){
       $pembayaranTotal=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]){
       $pembayaranTotal=0;
       } else {
       $pembayaranTotal=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranServiceCash=StripCurrency($ServiceCash);
       if($Dijamin=0){
       $pembayaranYgDibayar=0;
       } elseif($Dijamin>0 && $Dijamin>$_POST["total_biaya"]) {
       $pembayaranYgDibayar=0;
       } else {
       $pembayaranYgDibayar=StripCurrency($_POST["total_harga"]);
       }
       $pembayaranSubsidi=StripCurrency($Subsidi);
       $pembayaranHrsBayar=StripCurrency($HrsBayar);
       if($Dijamin > $_POST["total_biaya"]){
       $pembayaranSelisihNegatif=0;
       $pembayaranSelisihPositif=StripCurrency($Selisih);
       } else {
       $pembayaranSelisihPositif=0;
       $pembayaranSelisihNegatif=StripCurrency($Selisih);
       }
       $pembayaranDiskon=StripCurrency($Diskon);
       $pembayaranDiskonPersen=StripCurrency($DiskonPersen);
       $pembayaranJBayar=$_POST["id_jbayar"];
       if($DataPembayaranLama["pembayaran_dijamin"]>0){
       $pembayaranDijamin = StripCurrency($DataPembayaranLama["pembayaran_dijamin"]);
       } else {
       $pembayaranDijamin = 0;
       }
       $pembayaranPembulatan = StripCurrency($Pembulatan);
    }

    //-- KESATUAN SEMUANYA UPDATE klinik_pembayaran
    
        $sql = "update klinik.klinik_pembayaran set 
                pembayaran_who_dokter =".QuoteValue(DPE_CHAR,$pembayaranWhoDokter).",                                                                                                                                                                                                       
                pembayaran_tanggal =".QuoteValue(DPE_DATE,$pembayaranTanggal).", 
                pembayaran_create =".QuoteValue(DPE_DATE,$pembayaranCreate).", 
                pembayaran_flag = ".QuoteValue(DPE_CHAR,$pembayaranFlag).",  
                pembayaran_total =".QuoteValue(DPE_NUMERIC,$pembayaranTotal).", 
                pembayaran_service_cash =".QuoteValue(DPE_NUMERIC,$pembayaranServiceCash).",
                pembayaran_yg_dibayar = ".QuoteValue(DPE_NUMERIC,$pembayaranYgDibayar).", 
                pembayaran_subsidi=".QuoteValue(DPE_NUMERIC,$pembayaranSubsidi).", 
                pembayaran_hrs_bayar =".QuoteValue(DPE_NUMERIC,$pembayaranHrsBayar).", 
                pembayaran_selisih_negatif = ".QuoteValue(DPE_NUMERIC,$pembayaranSelisihNegatif).", 
                pembayaran_selisih_positif = ".QuoteValue(DPE_NUMERIC,$pembayaranSelisihPositif).",
                pembayaran_diskon =".QuoteValue(DPE_NUMERIC,$pembayaranDiskon).", 
                pembayaran_diskon_persen =".QuoteValue(DPE_NUMERIC,$pembayaranDiskonPersen).",
                id_jbayar =".QuoteValue(DPE_CHAR,$pembayaranJBayar).",
                pembayaran_dijamin = ".QuoteValue(DPE_NUMERIC,$pembayaranDijamin).",
                pembayaran_pembulatan = ".QuoteValue(DPE_NUMERIC,$pembayaranPembulatan)."
                where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       //  echo $sql; die();
         $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
    
    // --- AKHIR UPDATE klinik_pembayaran
 //AKHIR UPDATE KLINIK PEMBAYARAN


?>