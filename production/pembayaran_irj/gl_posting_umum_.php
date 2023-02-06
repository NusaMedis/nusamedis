<?php

//konfigurasi COA uang muka dan KAS      
$sql = "select dep_coa_piutang_kurang_bayar 
         from gl.gl_konf 
         where id_dept = ".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$datacoaPiutangPerorangan = $dtaccess->Fetch($rs);
$bayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtdibayar2"]) + StripCurrency($_POST["txtdibayar3"]) + 
        StripCurrency($_POST["txtDiskon"]) + StripCurrency($_POST["deposit_nominal"]); 
    if ($kurangBayar > 0) {
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
      $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaPiutangPerorangan["dep_coa_piutang_kurang_bayar"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
      $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($kurangBayar));
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
// print_r($dbValue);die();
      $dtmodel->Insert() or die("insert  error"); 
        
      unset($dbField);
      unset($dbValue);
    }
for($indexBayar=0,$jumBayar=3;$indexBayar<$jumBayar;$indexBayar++)   // Awal Looping Total Pembayaran dipaten 3 karen
{
    // Cari COA pembayaran sesuai dengan jenis bayar Pertama
    if ($_POST["txtdibayar".($indexBayar+1)]<>'') 
    {
      $sql = "select * from global.global_jenis_bayar
             where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar".($indexBayar+1)]);
      $rs  = $dtaccess->Execute($sql);
      $coaJenisBayar = $dtaccess->Fetch($rs);   
      
      //piutang Umum
      if($dataPas["reg_jenis_pasien"]==TIPE_PASIEN_UMUM) {
        //if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=TIPE_PASIEN_UMUM;
        if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
        //Total pembayaran 1, 2 dan 3 ditambah diskon dan nominal apabila kurang dari Total Tagihan maka KURANG BAYAR
        $bayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtdibayar2"]) + StripCurrency($_POST["txtdibayar3"]) + 
        StripCurrency($_POST["txtDiskon"]) + StripCurrency($_POST["deposit_nominal"]) + $pembayaran_det_pembulatan;      
        if(($_POST["total_harga"]>$bayar && $_POST["total_harga"]>$_POST['deposit_nominal_awal']) && $indexBayar==0){    //KURANG BAYAR  Untuk Awak Transaksi
          $beda = $_POST["total_harga"]-StripCurrency($_POST["txtdibayar1"])-StripCurrency($_POST["txtdibayar2"])-StripCurrency($_POST["txtdibayar3"]);
          
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
                $dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
                $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
                $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
                $dbValue[5] = QuoteValue(DPE_CHAR,$depId);              
                $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtdibayar".($indexBayar+1)]));
      
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      
                  $dtmodel->Insert() or die("insert  error");	
                  
                unset($dbField);
                unset($dbValue);
                
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
                $dbValue[2] = QuoteValue(DPE_CHAR,$datacoaPiutangPerorangan["dep_coa_piutang_kurang_bayar"]);
                $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
                $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
                $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
                $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($beda));
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      // print_r($dbValue);die();
                $dtmodel->Insert() or die("insert  error");	
                  
                unset($dbField);
                unset($dbValue);
                
        } else {    
        $totalPembayaranBersih = StripCurrency($_POST["txtdibayar".($indexBayar+1)]);
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
                $dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
                $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
                $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
                $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
                $dbValue[6] = QuoteValue(DPE_NUMERIC,$totalPembayaranBersih);
      
       
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);
      
                  $dtmodel->Insert() or die("insert  error");	
                  
                unset($dbField);
                unset($dbValue);    

         }
      }
    }
} // End Looping 3 Pembayaran
for($indexBayar=0,$jumBayar=1;$indexBayar<$jumBayar;$indexBayar++)   // Awal Looping Total Pembayaran dipaten 3 karen
{
  $sql = "select * from global.global_jenis_bayar
       where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["id_jbayar".($indexBayar+1)]);
  $rs  = $dtaccess->Execute($sql);
  $coaJenisBayar = $dtaccess->Fetch($rs);  
  if ($pembayaran_det_pembulatan > 0) {
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
      $dbValue[2] = QuoteValue(DPE_CHAR,$coaJenisBayar["id_prk"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$keterangan);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["job_id"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,$depId);  
      $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($pembayaran_det_pembulatan));
    //          print_r($dbValue);   die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GL);

      $dtmodel->Insert() or die("insert  error");  
        
      unset($dbField);
      unset($dbValue);
  }   
}
?>