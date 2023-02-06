<?php

//konfigurasi COA uang muka dan KAS      
$sql = "select dep_coa_piutang_kurang_bayar 
         from gl.gl_konf 
         where id_dept = ".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$datacoaPiutangPerorangan = $dtaccess->Fetch($rs);

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
      $dtmodel->Insert() or die("insert  error"); 
        
      unset($dbField);
      unset($dbValue);
}

for($indexBayar=0,$jumBayar=3;$indexBayar<$jumBayar;$indexBayar++)   // Awal Looping Total Pembayaran dipaten 3 karen
{
    // Cari COA pembayaran sesuai dengan jenis bayar Pertama
    if ($_POST["txtdibayar".($indexBayar+1)]<>'') 
    {
      if ($_POST['id_jenis'.($indexBayar+1)] == '2') {
        $sql = "select * from global.global_jenis_bayar
               where jbayar_id = ".QuoteValue(DPE_CHAR,$_POST["jbayar".($indexBayar+1)]);
        $rs  = $dtaccess->Execute($sql);
        $coaJenisBayar = $dtaccess->Fetch($rs);  
      }elseif ($_POST['id_jenis'.($indexBayar+1)] == '7') {
         $sql = "select * from global.global_perusahaan where perusahaan_id = ".QuoteValue(DPE_CHAR,$_POST['jbayar'.($indexBayar+1)]);
         $coaJenisBayar = $dtaccess->Fetch($sql);
      }elseif ($_POST['id_jenis'.($indexBayar+1)] == '5') {
        $sql = "select * from global.global_jkn where jkn_id = ".QuoteValue(DPE_CHAR,$_POST['jbayar'.($indexBayar+1)]);
        // $coaJenisBayar = $dtaccess->Fetch($sql);
        $coaJenisBayar['id_prk'] = '01010101010103';
      } elseif ($_POST['id_jenis'.($indexBayar+1)] == '20') {
        $coaJenisBayar['id_prk'] = '"01010101020101';
      }
      
      //piutang Umum
      // if($dataPas["reg_jenis_pasien"]==TIPE_PASIEN_UMUM) {
        //if(!$dataFolioPas[$i]["fol_jenis_pasien"]) $dataFolioPas[$i]["fol_jenis_pasien"]=TIPE_PASIEN_UMUM;
        if(!$dataPas["reg_tipe_layanan"]) $dataPas["reg_tipe_layanan"]= "1";
        //Total pembayaran 1, 2 dan 3 ditambah diskon dan nominal apabila kurang dari Total Tagihan maka KURANG BAYAR
        if (StripCurrency($pembayaran_det_pembulatan) > 0) {
          $pembulatan = $pembayaran_det_pembulatan;
        }else{
          $pembulatan = '0';
        }
        if(StripCurrency($pembayaran_det_pembulatan)>0){
          $bulat = '-'.StripCurrency($pembayaran_det_pembulatan);
        }else{
          $bulat = str_replace("-", "", StripCurrency($pembayaran_det_pembulatan));
        }
        // $totalHarga = $_POST['total_harga'] + $bulat;
        $bayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtdibayar2"]) + StripCurrency($_POST["txtdibayar3"] + $_POST['deposit_nominal']) + StripCurrency($_POST["txtDiskon"]) + StripCurrency($_POST["deposit_nominal"])+$bulat;    
        
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
} // End Looping 3 Pembayaran

?>