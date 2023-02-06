<?php
      $sql = "select * from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id']);
      $dataFarmasi = $dtaccess->Fetch($sql);
      
     //--INISIASI POSTING GL        
    require_once('gl_awal.php');  
    if ($_POST['deposit_nominal_awal'] > 0) {
      //--GL POSTING UANG MUKA        
    require_once('gl_uang_muka.php');
    //-- akhir posting UM    
    require_once('gl_retur_uang_muka.php');
    }
 
       //--GL POSTING DISKON       
    require_once('gl_posting_diskon.php');
    //-- akhir posting DISKON

       //--GL POSTING PEMBULATAN       
    require_once('gl_posting_pembulatan.php');
    //-- akhir posting PEMBULATAN

       //--GL POSTING SERVICE CHARGE       
    require_once('gl_posting_service_cash.php');
    //-- akhir posting SERVICE CHARGE    */

       //--GL POSTING PASIEN UMUM       
    require_once('gl_posting_umum.php');
    //-- akhir posting UMUM
   
   
           
       //--GL POSTING PASIEN JKN       
    require_once('gl_posting_jkn.php');
    //-- akhir posting JKN

 // Piutang JAMKESDA
    require_once('gl_posting_jamkesda.php'); 

 // Piutang IKS
    require_once('gl_posting_iks.php'); 
 
 // Piutang PKMS SILVER
     require_once('gl_posting_pkms.php');

       //--GL POSTING PASIEN JASARAHARAJA        
    require_once('gl_posting_jasa_raharja.php');
    //-- akhir posting JASARAHARAJA

 // Piutang JKN+JASARAHARAJA
    require_once('gl_posting_jkn_jasaraharja.php'); 
//-- Akhir JKN+JASARAHARAJA

    // Piutang Fasilitas
    require_once('gl_posting_fasilitas.php'); 
    //-- Akhir Fasilitas
    
    // Piutang JKN+Fasilitas
    require_once('gl_posting_jkn_fasilitas.php'); 
//-- Akhir JKN+Fasilitas      

     
       require('gl_pendapatan_irj.php');

       require('gl_pendapatan_farmasi.php');             
    
    
     // Posting GL Beban
    require_once('gl_posting_beban.php');   

  if ($dataFarmasi['penjualan_id'] != '') {
    //Posting Persediaan Gudang
    // require_once('gl_awal_persediaan.php');   
    
    require_once('gl_persediaan_farmasi.php');   
  }
 
     //die();

?>