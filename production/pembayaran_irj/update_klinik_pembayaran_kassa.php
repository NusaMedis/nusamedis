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
            where pembayaran_id = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]);
$rs = $dtaccess->Execute($sql);
$DataPembayaranLama = $dtaccess->Fetch($rs);
// echo $sql."<br>"; 
//echo $_POST["txtDibayar"][0]."<br>";
//echo $_POST["reg_jenis_pasien"]." - ".TIPE_PASIEN_UMUM."<br>";  

// UPDATE KLINIK PEMBAYARAN//    
//Per Jenis Pasien
if ($_POST["reg_jenis_pasien"] == TIPE_PASIEN_ASKES) //1
{
} else if ($_POST["reg_jenis_pasien"] == TIPE_PASIEN_UMUM) //2
{
  if ($_POST["txtDiskonPersen"] || $_POST["txtDiskon"] || $_POST["txtServiceCash"] || $_POST["txtBiayaPembulatan"]) {
    $Total = StripCurrency($_POST["txtTotalDibayar"]) + $DataPembayaranLama["pembayaran_yg_dibayar"];
    $_POST["total_harga"] = StripCurrency($_POST["txtTotalDibayar"]);
  } else {
    $Total = StripCurrency($_POST["total_harga"]) + $DataPembayaranLama["pembayaran_yg_dibayar"];
    $_POST["total_harga"] = StripCurrency($_POST["total_harga"]);
  }

  //pembayaran yg dibayar
  $Dibayar = StripCurrency($_POST["txtDibayar"][0]) + $DataPembayaranLama["pembayaran_yg_dibayar"];
  //   echo "<br> total biaya ".$_POST["total_biaya"]."<br> terima bayar ".$_POST["txtDibayar"][0];     
  //pembayaran hrs bayar
  $HrsBayar = $DataPembayaranLama["pembayaran_hrs_bayar"] + $_POST["total_biaya"];
  //  echo "<br> harus bayar ".$DataPembayaranLama["pembayaran_hrs_bayar"]."<br> dibayar ".$Dibayar;
  if ($HrsBayar < 0) $HrsBayar = 0;

  if ($_POST["uangmuka"] > 0 && $_POST["retur"] == 0) {
    $Dibayar = $Dibayar + $_POST["uangmuka"];
    $Total = $Total + $_POST["uangmuka"];
  } elseif ($_POST["uangmuka"] > 0 && $_POST["retur"] > 0) {
    $Dibayar = $_POST["uangmuka"] - $Dibayar;
    $Total = $_POST["uangmuka"] - $Total;
  }

  //pembayaran diskon, diskon persen, service cash
  $Diskon = $DataPembayaranLama["pembayaran_diskon"] + StripCurrency($_POST["txtDiskon"]);
  $DiskonPersen = $DataPembayaranLama["pembayaran_diskon_persen"] + StripCurrency($_POST["txtDiskonPersen"]);
  $ServiceCash = $DataPembayaranLama["pembayaran_service_cash"] + StripCurrency($_POST["txtServiceCash"]);
  $Pembulatan = $DataPembayaranLama["pembayaran_pembulatan"] + StripCurrency($_POST["txtBiayaPembulatan"]);

  //Masukkan semua datanya
  $pembayaranWhoDokter = $Doktere["usr_name"];
  $pembayaranTanggal = date("Y-m-d"); //hati2 kadang ngga perlu diupdate  
  $pembayaranCreate = date("Y-m-d H:i:s"); //hati2 kadang ngga perlu diupdate 
  if ($_POST["total_harga"] > StripCurrency($_POST["txtDibayar"][0])) {
    $pembayaranFlag = 'p';
  } else {
    $pembayaranFlag = 'y';
  }
  $pembayaranTotal = StripCurrency($Total);
  $pembayaranServiceCash = StripCurrency($ServiceCash);
  if (StripCurrency($_POST["txtDibayar"][0]) > $_POST["total_harga"]) {
    $pembayaranYgDibayar = StripCurrency($Total);
  } else {
    $pembayaranYgDibayar = StripCurrency($Dibayar);
  }
  $pembayaranSubsidi = 0;
  $pembayaranHrsBayar = StripCurrency($HrsBayar);
  $pembayaranSelisihNegatif = 0;
  $pembayaranSelisihPositif = 0;
  $pembayaranDiskon = StripCurrency($Diskon);
  $pembayaranDiskonPersen = StripCurrency($DiskonPersen);
  $pembayaranJBayar = $_POST["id_jbayar"];
  $pembayaranDijamin = 0;
  $pembayaranPembulatan = StripCurrency($Pembulatan);
} 

//-- KESATUAN SEMUANYA UPDATE klinik_pembayaran
if ($_POST['reg_tipe_rawat'] == 'I') {
  // echo 'Pembayaran Total : '.$pembayaranTotal.' - '.$pembayaranServiceCash;
  $pembayaranTotal = $pembayaranTotal;
}
$sql = "update klinik.klinik_pembayaran set 
                pembayaran_who_dokter =" . QuoteValue(DPE_CHAR, $pembayaranWhoDokter) . ",                                                                                                                                                                                                       
                pembayaran_tanggal =" . QuoteValue(DPE_DATE, $pembayaranTanggal) . ", 
                pembayaran_create =" . QuoteValue(DPE_DATE, $pembayaranCreate) . ", 
                pembayaran_flag = " . QuoteValue(DPE_CHAR, $pembayaranFlag) . ",  
                pembayaran_total =" . QuoteValue(DPE_NUMERIC, $pembayaranTotal) . ", 
                pembayaran_service_cash =" . QuoteValue(DPE_NUMERIC, $pembayaranServiceCash) . ",
                pembayaran_yg_dibayar = " . QuoteValue(DPE_NUMERIC, $pembayaranYgDibayar) . ", 
                pembayaran_subsidi=" . QuoteValue(DPE_NUMERIC, $pembayaranSubsidi) . ", 
                pembayaran_hrs_bayar =" . QuoteValue(DPE_NUMERIC, $pembayaranHrsBayar) . ", 
                pembayaran_selisih_negatif = " . QuoteValue(DPE_NUMERIC, $pembayaranSelisihNegatif) . ", 
                pembayaran_selisih_positif = " . QuoteValue(DPE_NUMERIC, $pembayaranSelisihPositif) . ",
                pembayaran_diskon =" . QuoteValue(DPE_NUMERIC, $pembayaranDiskon) . ", 
                pembayaran_diskon_persen =" . QuoteValue(DPE_NUMERIC, $pembayaranDiskonPersen) . ",
                id_jbayar =" . QuoteValue(DPE_CHAR, $pembayaranJBayar) . ",
                pembayaran_dijamin = " . QuoteValue(DPE_NUMERIC, $pembayaranDijamin) . ",
                pembayaran_pembulatan = " . QuoteValue(DPE_NUMERIC, $pembayaranPembulatan) . "
                where pembayaran_id = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]);
 // echo $sql; die();
$rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);

//Jika ada penjualan maka dibikin sudah lunas
$sql = "update apotik.apotik_penjualan set 
                penjualan_terbayar ='y'
                where id_fol = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]);
//  echo $sql; die();
$rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
    
    // --- AKHIR UPDATE klinik_pembayaran
 //AKHIR UPDATE KLINIK PEMBAYARAN
