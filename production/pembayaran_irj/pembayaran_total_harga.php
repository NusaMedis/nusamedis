<?php
   
   $sql = "select * from global.global_auth_poli where poli_tipe='P'";
   $rs = $dtaccess->Execute($sql);
   $op = $dtaccess->Fetch($rs);
   //echo $sql; 
   
   $sql = "select * from klinik.klinik_inacbg where id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
   $rs = $dtaccess->Execute($sql);
   $inacbg = $dtaccess->Fetch($rs);
   
   $sql = "select sum(uangmuka_jml) as total from klinik.klinik_pembayaran_uangmuka where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
   $uangmuka = $dtaccess->Fetch($sql);
   
   $sql = "select pembayaran_dijamin from  klinik.klinik_pembayaran
     where pembayaran_id = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
   $rs_dijamin = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
   $dataDijamin = $dtaccess->Fetch($rs_dijamin);
   
   $sql = "select * from global.global_konfigurasi_fasilitas where konf_fasilitas_id='1'";
   $rs = $dtaccess->Execute($sql);
   $konFasilitas = $dtaccess->Fetch($rs);
   
   $sql = "select * from global.global_detail_paket where detail_paket_id=".QuoteValue(DPE_CHAR,$_POST["reg_tipe_paket"]);
   $rs = $dtaccess->Execute($sql);
   $detPaket = $dtaccess->Fetch($rs);
   
   $sql = "select * from global.global_konf_jasa_raharja where konf_jasa_raharja_id='1'";
   $rs = $dtaccess->Execute($sql);
   $konfJR = $dtaccess->Fetch($rs);
   
   //total biaya
   $totalBiaya=$totalBiaya;   
   //harga dijamin
   $dijaminHarga = $dataDijamin["pembayaran_dijamin"]+$inacbg["inacbg_topup"];
   
   //echo "masuk = ".$op["poli_id"];
   //perhitungan rumus JKN
   if(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $_POST["id_poli"]==$op["poli_id"]){
   $totalHarga=$totalHarga;
   } elseif(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $totalBiaya > $dijaminHarga){
   $totalHarga=$totalBiaya-$dijaminHarga;
   } elseif(($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN || $_POST["reg_jenis_pasien"]==TIPE_PASIEN_ASKES) && $totalBiaya < $dijaminHarga){
   $totalHarga=$dijaminHarga-$totalBiaya;
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_FASILITAS){ //fasilitas
    if($konFasilitas["konf_fasilitas_pagu"]>0){
      if($totalBiaya>$konFasilitas["konf_fasilitas_pagu"]){
        if($konFasilitas["konf_fasilitas_diskon_irj"]>0){
          $diskon = ($konFasilitas["konf_fasilitas_diskon_irj"]/100)*($totalBiaya-$konFasilitas["konf_fasilitas_pagu"]);
          $diskonCur = currency_format(($konFasilitas["konf_fasilitas_diskon_irj"]/100)*($totalBiaya-$konFasilitas["konf_fasilitas_pagu"])); 
          $totalHarga = $totalBiaya-$konFasilitas["konf_fasilitas_pagu"]-StripCurrency($diskonCur);
          $_POST["diskon"] = currency_format($diskon);
          $_POST["diskon_persen"] = currency_format($konFasilitas["konf_fasilitas_diskon_irj"]); 
        } else {
          $totalHarga = $totalBiaya-$konFasilitas["konf_fasilitas_pagu"];
        }
      } else {
        $totalHarga = 0;
      }
    } else {
      if($konFasilitas["konf_fasilitas_diskon_irj"]>0){
        $diskon = ($konFasilitas["konf_fasilitas_diskon_irj"]/100)*$totalBiaya;
        $diskonCur = currency_format($diskon);
        $totalHarga = $totalBiaya-StripcCurrency($diskonCur);
        $_POST["diskon"] = currency_format($diskon);
        $_POST["diskon_persen"] = currency_format($konFasilitas["konf_fasilitas_diskon_irj"]);
      } else {
        $totalHarga = 0;
      }
    }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JASA_RAHARJA){ //jasa raharja
    if($konfJR["konf_jasa_raharja_pagu"]>0){
      if($totalBiaya>$konfJR["konf_jasa_raharja_pagu"]){
      $totalHarga = $totalBiaya - $konfJR["konf_jasa_raharja_pagu"];
      } else {
      $totalHarga = 0;
      }
    } else {
      $totalHarga = $totalBiaya;
    }
   }elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_PAKET){
     $totalHarga = $detPaket["detail_paket_nominal"]+$totalNonPaket;
   }elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_JASA_RAHARJA){
     if($_POST["id_poli"]==$op["poli_id"]){$totalHarga=$totalHarga;}
     elseif($totalBiaya > $dijaminHarga){
      $totalHarga=$totalBiaya-$dijaminHarga;
     }else{
      $totalHarga=$dijaminHarga-$totalBiaya;
     }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_JKN_FASILITAS){
     if($_POST["id_poli"]==$op["poli_id"]){$totalHarga=$totalHarga;}
     elseif($totalBiaya > $dijaminHarga){
      $totalHarga=$totalBiaya-$dijaminHarga;
     }else{
      $totalHarga=$dijaminHarga-$totalBiaya;
     }
   } elseif($_POST["reg_jenis_pasien"]==TIPE_PASIEN_IKS){
     if($_POST["perusahaan_plafon"]>0){
      if($totalBiaya>$_POST["perusahaan_plafon"]){
        if($_POST["perusahaan_diskon"]>0){
          $diskon = ($_POST["perusahaan_diskon"]/100)*($totalBiaya-$_POST["perusahaan_plafon"]);
          $diskonCur = currency_format($diskon);
          $totalHarga = $totalBiaya-$_POST["perusahaan_plafon"]-StripCurrency($diskonCur);
          $_POST["diskon"] = currency_format($diskon);
          $_POST["diskon_persen"] = currency_format($_POST["perusahaan_diskon"]); 
        } else {
          $totalHarga = $totalBiaya-$_POST["perusahaan_plafon"];
        }
      } else {
        $totalHarga = 0;
      }
    } else {
      if($_POST["perusahaan_diskon"]>0){
        $diskon = ($_POST["perusahaan_diskon"]/100)*$totalBiaya;
        $diskonCur = currency_format($diskon);
        $totalHarga = $totalBiaya-StripCurrency($diskonCur);
        $_POST["diskon"] = currency_format($diskon);
        $_POST["diskon_persen"] = currency_format($_POST["perusahaan_diskon"]);
      } else {
        $totalHarga = 0;
      }
    }
   } elseif($_POST["reg_jenis_pasien"]=='2'){   //Jika Pasien UMUM
     if($_POST["dep_konf_bulat_ribuan"]=="y"){
        $totalint = substr($totalBiaya,-3);   
        $selisih = 1000-$totalint; 
        if($selisih<>1000)    
        $_POST["bulat"] = $selisih;
        $totalHarga = $totalBiaya + $_POST["bulat"];
     } else{  
        if($_POST["dep_konf_bulat_ratusan"]=="y") { 
          $totalint = substr($totalHarga,-2);
          $selisih = 100-$totalint; 
          if($selisih>50){
            $_POST["bulat"] = $selisih;
            $totalHarga = $totalBiaya - $totalint;
          }elseif($selisih<=50){
            $totalHarga = $totalBiaya + $_POST["bulat"];
          }
        } else {
          $totalHarga = $totalBiaya;
        } 
     }
   } elseif($_POST["reg_jenis_pasien"]=='18'){
     $sql = "select * from klinik.klinik_registrasi a left join global.global_auth_poli b on b.poli_id=a.id_poli
            where b.poli_tipe='O' and a.id_pembayaran=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
     $rs = $dtaccess->Execute($sql);
     $operasi = $dtaccess->Fetch($rs);
     
     if($operasi) {
      if($_POST["jamkesda_kota_plafon_operasi_kota"]>0 || $_POST["jamkesda_kota_plafon_operasi_prop"]>0){
        if($totalBiaya>($_POST["jamkesda_kota_plafon_operasi_kota"]+$_POST["jamkesda_kota_plafon_operasi_prop"])){
          $totalHarga = $totalBiaya - $_POST["jamkesda_kota_plafon_operasi_kota"] - $_POST["jamkesda_kota_plafon_operasi_prop"];
        } else {
          $totalHarga = 0;
        }
      } else {
        $totalHarga = 0;
      }
     } else{
      if($_POST["jamkesda_kota_plafon_kota"]>0 || $_POST["jamkesda_kota_plafon_prop"]>0){
        if($totalBiaya>($_POST["jamkesda_kota_plafon_kota"]+$_POST["jamkesda_kota_plafon_prop"])){
          $totalHarga = $totalBiaya - $_POST["jamkesda_kota_plafon_kota"] - $_POST["jamkesda_kota_plafon_prop"];
        } else {
          $totalHarga = 0;
        }
      } else {
        $totalHarga = 0;
      }
     } 
   } else{
    $totalHarga=$totalHarga;
  }
  
   //  echo  $totalHarga; die();
   //if ($totalHarga<0) $totalHarga=0; 
   



?>