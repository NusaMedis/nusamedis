<?php
    $sql = "select * from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST['pembayaran_id']);
    $dataFarmasi = $dtaccess->Fetch($sql);  
      
     //--INISIASI POSTING GL        
    require_once('gl_awal.php');  
     
      //--GL POSTING UANG MUKA        
    require_once('gl_uang_muka.php');

    require_once('gl_retur_uang_muka.php');
    //-- akhir posting UM    
 
       //--GL POSTING DISKON       
    require_once('gl_posting_diskon.php'); // diskon
    //-- akhir posting DISKON

       //--GL POSTING PEMBULATAN       
    require_once('gl_posting_pembulatan.php'); // pembulatan
    //-- akhir posting PEMBULATAN

       //--GL POSTING SERVICE CHARGE       
    require_once('gl_posting_service_cash.php'); // jasa rs
    //-- akhir posting SERVICE CHARGE    */

       //--GL POSTING PASIEN UMUM       
    require_once('gl_posting_umum.php'); // dari sisi pembayaran
    //-- akhir posting UMUM
   
   
           
       //--GL POSTING PASIEN JKN       
//     require_once('gl_posting_jkn.php');
//     //-- akhir posting JKN

//  // Piutang JAMKESDA
//     require_once('gl_posting_jamkesda.php'); 

//  // Piutang IKS
//     // require_once('gl_posting_iks.php'); 
 
//  // Piutang PKMS SILVER
//      require_once('gl_posting_pkms.php');

//        //--GL POSTING PASIEN JASARAHARAJA        
//     require_once('gl_posting_jasa_raharja.php');
//     //-- akhir posting JASARAHARAJA

//  // Piutang JKN+JASARAHARAJA
//     require_once('gl_posting_jkn_jasaraharja.php'); 
// //-- Akhir JKN+JASARAHARAJA

//     // Piutang Fasilitas
//     require_once('gl_posting_fasilitas.php'); 
//     //-- Akhir Fasilitas
    
//     // Piutang JKN+Fasilitas
//     require_once('gl_posting_jkn_fasilitas.php'); 
//-- Akhir JKN+Fasilitas      

     
       require('gl_pendapatan_irj.php'); // tindakan

       require('gl_pendapatan_farmasi.php');        // pendapatan apotik     
    
    
     // Posting GL Beban
    require_once('gl_posting_beban.php');   
    
   if ($dataFarmasi['penjualan_id'] != '') {
    //Posting Persediaan Gudang
    // require_once('gl_awal_persediaan.php');   
    
    require_once('gl_persediaan_farmasi.php');   
  }
     //die();

?>