<?php
     

     if ($_POST["obat_id"]) {
     
     $dateSekarang = date('Y-m-d H:i:s');
          
          $dbTable = "apotik.apotik_penjualan_detail";
          $dbField[0]  = "penjualan_detail_id";   // PK
          $dbField[1]  = "id_penjualan";
          $dbField[2]  = "id_item";
          $dbField[3]  = "penjualan_detail_harga_jual";
          $dbField[4]  = "penjualan_detail_jumlah";
          $dbField[5]  = "penjualan_detail_total";
          $dbField[6]  = "penjualan_detail_flag";
          $dbField[7]  = "penjualan_detail_create";
          $dbField[8]  = "id_petunjuk";
          $dbField[9]  = "id_dep";
          $dbField[10]  = "penjualan_detail_sisa";
          $dbField[11]  = "id_batch";
          $dbField[12]  = "penjualan_detail_tuslag";
          $dbField[13]  = "penjualan_detail_dosis_obat";
          $dbField[14]  = "id_aturan_minum";
          $dbField[15]  = "id_aturan_pakai";
          $dbField[16]  = "item_nama";
                   
          if (!$_POST["btn_edit"]) //jika tombol edit di klik
               $penjualanDetailId = $dtaccess->GetTransID();
          else
               $penjualanDetailId = $_POST["btn_edit"];
          $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanDetailId);
          $dbValue[1] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["obat_id"]);
          $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaSatuan"]));
          $dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtHargaTotal"]));  
          $dbValue[6] = QuoteValue(DPE_CHAR,'n');
          $dbValue[7] = QuoteValue(DPE_DATE,$dateSekarang);
          $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_petunjuk"]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["txtJumlah"]);
          $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["id_batch"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
          $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["penjualan_detail_dosis_obat"]);
          $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["id_aturan_minum"]);
          $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_aturan_pakai"]);
          $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["obat_nama"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          if ($_POST["btn_edit"])
            $dtmodel->Update() or die("insert  error");
          else
            $dtmodel->Insert() or die("insert  error");	
            
          unset($dbField);
          unset($dbValue); 
          unset($_POST["btnSave"]);
          unset($_POST["obat_id"]);
          unset($_POST["obat_kode"]);
          unset($_POST["obat_nama"]);
          unset($_POST["txtHargaSatuan"]);
          unset($_POST["txtJumlah"]);
          unset($_POST["txtHargaTotal"]);
          unset($_POST["txtTuslag"]);
          
     }
     
      $isprint = "n"; 
    // $_POST["penjualan_total_obat"]=$_POST["txtBalik"];

      $sql = "select sum(penjualan_detail_total) as penjualan_total_detail from apotik.apotik_penjualan_detail  where 
      id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId) ;
      $rs = $dtaccess->Execute($sql);
      $total = $dtaccess->Fetch($rs);
         
      $_POST["penjualan_total_detail"] = $total["penjualan_total_detail"];

      /*$grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"]);*/
      
      $grandTotals = StripCurrency($_POST["penjualan_total_detail"]) + StripCurrency($_POST["txtResep"]) + StripCurrency($_POST["txtBiayaRacikan"])+StripCurrency($_POST["txtBiayaPembulatan"])-StripCurrency($_POST["txtDiskon"]); 

      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJualCheck = $dtaccess->FetchAll($rs);
      
      for($x=0,$a=count($dataJualCheck);$x<$a;$x++){
      
       // jika si user memasukan 2 item batch yg sama maka di ambil salah satu dahulu utk di cek //
       if($dataJualCheck[$x]["id_batch"]!=$dataJualCheck[$x-1]["id_batch"]) {
       
          // Cek total stok yg akan di trasfer ke gudang tujuan //
          $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                  where id_batch = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"])." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataTransStokuff = $dtaccess->Fetch($rs);
         
          // Cek total Saldo di tabel item Batch //
          $sql = "select sum(batch_stok_saldo) as total from logistik.logistik_item_batch
                  where batch_id = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"]);
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
          $dataStokBatch = $dtaccess->Fetch($rs);
          
          // Checking apakah Stok yg di masukkan tidak kelebihan . more than, oh no !!! -,-
/*          if($dataTransStokuff["total"]>$dataStokBatch["total"]) {
          
             // Lihat Nama Item , No. Batch yg terkena penalty krn kebanyakan //
             $sql = "select item_nama, batch_no from logistik.logistik_item a
                     join logistik.logistik_item_batch b on b.id_item = a.item_id
                     where b.batch_id = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_batch"])." and b.id_dep = ".QuoteValue(DPE_CHAR,$dataJualCheck[$x]["id_dep"]);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
             $dataStokBatchAlert = $dtaccess->Fetch($rs);
             
             // kasih alert biar si user mengerti kalau si dy input kelebihan //       
             echo "<script>alert('Maaf data yang akan ditransfer terlalu banyak, ITEM : ".$dataStokBatchAlert["item_nama"].", BATCH NO : ".$dataStokBatchAlert["batch_no"].", MAX. STOK : ".currency_format($dataStokBatch["total"])."');</script>";
             echo "<script>document.location.href='penjualan_bebas.php?transaksi=".$penjualanId."'</script>;";
             exit();           
          }  */                          
        } 
      }  
        //die();
        //die();

      //Rubah Status Kuitansi Sudah Dibayar 
      $dbTable = "apotik.apotik_penjualan";
      $dbField[0]  = "penjualan_id";   // PK
      $dbField[1]  = "penjualan_create";
      $dbField[2]  = "penjualan_nomor";
      $dbField[3]  = "penjualan_total";     
      $dbField[4]  = "penjualan_terbayar";
      $dbField[5]  = "who_update";
      $dbField[6]  = "id_gudang";
      $dbField[7]  = "penjualan_flag";
      $dbField[8]  = "penjualan_catatan";
      $dbField[9]  = "penjualan_pajak";
      $dbField[10]  = "penjualan_diskon";
      $dbField[11]  = "penjualan_diskon_persen";
      $dbField[12]  = "penjualan_biaya_resep";
      $dbField[13]  = "penjualan_biaya_racikan";
      $dbField[14]  = "penjualan_biaya_bhps";
      $dbField[15]  = "penjualan_biaya_pembulatan";
      $dbField[16]  = "id_dep";
      $dbField[17]  = "penjualan_grandtotal";
      $dbField[18]  = "penjualan_bayar";
      $dbField[19]  = "penjualan_keterangan";
      $dbField[20]  = "penjualan_tuslag";
      $dbField[21]  = "id_fol";
      $dbField[22]  = "id_dokter";
      $dbField[23]  = "dokter_nama";
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$penjualanId);
      $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["penjualan_total_detail"]));  
      $dbValue[4] = QuoteValue(DPE_CHAR,'n');
      $dbValue[5] = QuoteValue(DPE_CHAR,$usrId);
      $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);
      $dbValue[7] = QuoteValue(DPE_CHAR,'L');
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["penjualan_catatan"]);
      $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtPPN"])); 
      $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
      $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskonPersen"])); 
      $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtResep"]));
      $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaRacikan"]));
      $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaBhps"])); 
      $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtBiayaPembulatan"]));
      $dbValue[16] = QuoteValue(DPE_CHAR,$depId); 
      $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
      $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDibayar"]));
      $dbValue[19] = QuoteValue(DPE_CHAR,$_POST["fol_keterangan"]); 
      $dbValue[20] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtTuslag"]));
      $dbValue[21] = QuoteValue(DPE_CHAR,$folId);
      $dbValue[22] = QuoteValue(DPE_CHAR,$_POST["usr"]);
      $dbValue[23] = QuoteValue(DPE_CHAR,$_POST["usr_name"]);
      
            
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      
      $dtmodel->Update() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
          
    /*          
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJual = $dtaccess->FetchAll($rs);

      for($i=0,$n=count($dataJual);$i<$n;$i++){
      
       // cek apakah ada dua batch atau lebih yg di input //
        if($dataJual[$i]["id_batch"]!=$dataJual[$i-1]["id_batch"]){        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
          
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
           
           //stok batch yg lama - stok baru (dikurangi)
           $stokBatchNow[$i] = $dataDepBatch["total"] - $dataPenjualanStok["total"];
          
          
          // Langsung Update Stok Batch di Gudangnya //
          $sql  ="update logistik.logistik_stok_batch_dep set 
                  stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokBatchNow[$i]);
          $sql .=" , stok_batch_dep_create = current_timestamp";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
          $rs = $dtaccess->Execute($sql);
         
         
         //END UPDATE POSISI STOK BATCH TERAKHIR 
         
         
         //UPDATE POSISI STOK TERAKHIR
         
         //cek di stok_dep untuk melihat stokterakhir
         $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
         $sql .="and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDep = $dtaccess->Fetch($rs);         
         
         //stok lama - stok baru (dikurangi)
          $stokNow[$i] = $dataDep["stok_dep_saldo"] - $dataJual[$i]["penjualan_detail_jumlah"];

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokNow[$i]);
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
          
          //---------------- END UPDATE POSISI STOK TERAKHIR
          //cari harga beli terakhir item
          $sql = " select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $rs = $dtaccess->Execute($sql);
           $dataHargabeli = $dtaccess->Fetch($rs);
          
          //insert kartu stok untuk histry batch untuk penjualan
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_batch_hpp";
          $dbField[11]  = "stok_item_batch_hna";
          $dbField[12]  = "stok_item_batch_hna_ppn_minus_diskon";
          $dbField[13]  = "id_batch";
          
          $date = date("Y-m-d H:i:s");
          $stobatkid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stobatkid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //insert kartu stok untuk penjualan
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";         
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_hpp";
          $dbField[11]  = "stok_item_hna";
          $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);      
         } // end detail
      } 

*/

        $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
                   where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
        $idPemb = $dtaccess->Fetch($sqlpemb);
		//echo $sql;			  
//          $sql  ="update klinik.klinik_registrasi set reg_obat='y' , reg_status = 'E0'
 //                 where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql  ="update klinik.klinik_registrasi set reg_obat='y'
                  where id_pembayaran=".QuoteValue(DPE_CHARKEY,$_POST["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);

          $rs = $dtaccess->Execute($sql); 
      //cari folio penjualan tersebut untuk dicari id_reg dan pembayaran_idnya
         $sql = "select * from  klinik.klinik_folio where fol_catatan = ".QuoteValue(DPE_CHAR,$_POST["penjualan_no"]);
         $rs = $dtaccess->Execute($sql);
         $foliojualan = $dtaccess->Fetch($rs);

         if(!$foliojualan) {

        $sql = "select count(id_item) as total_item from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR,$penjualanId);
        $rs = $dtaccess->Execute($sql);
        $jumlahTotalObat = $dtaccess->Fetch($rs);

          //INSERT FOLIO
          $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_nominal";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";                   
          $dbField[9] = "id_poli";
          $dbField[10] = "fol_jenis_pasien";
          $dbField[11] = "id_dep";
          $dbField[12] = "fol_dibayar";
          $dbField[13] = "fol_dibayar_when";
          $dbField[14] = "fol_total_harga";
          $dbField[15] = "id_pembayaran";
          $dbField[16] = "fol_keterangan";
          $dbField[17] = "fol_hrs_bayar";
          $dbField[18] = "fol_catatan";
          $dbField[19] = "fol_nominal_satuan";
          $dbField[20] = "fol_jumlah";
          $dbField[21] = "id_dokter";
          $dbField[22] = "who_when_update";
                                                  
         $sqltdk = "select biaya_jenis, biaya_nama, biaya_id from klinik.klinik_biaya where biaya_jenis = 'O' and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $dataObat = $dtaccess->Fetch($sqltdk);
         //checking ulang penjulannya
         $sql = "select penjualan_id from apotik.apotik_penjualan where penjualan_nomor =".QuoteValue(DPE_CHAR,$_POST["penjualan_nomor"])." and penjualan_id <> ".QuoteValue(DPE_CHAR,$penjualanId);
         $rs = $dtaccess->Execute($sql);
         $nomorjual = $dtaccess->Fetch($rs);
         if(!$nomorjual){
          $_POST["penjualan_nomor"]= $_POST["penjualan_nomor"];
         }else{
          $sql = "select max(penjualan_urut) as urut from apotik.apotik_penjualan where id_dep =".QuoteValue(DPE_CHAR,$depId)." and penjualan_flag = 'D'";
            $lastKode = $dtaccess->Fetch($sql);
            $tgl = explode("-",date('Y-m-d'));
            $_POST["penjualan_nomor"] = "LPRJ".str_pad($lastKode["urut"]+1,5,"0",STR_PAD_LEFT)."/".$tgl[2]."/".$tgl[1]."/".$tgl[0];
            $_POST["hidUrut"] = $lastKode["urut"]+1;
            $sql = "update apotik.apotik_penjualan set penjualan_nomor =".QuoteValue(DPE_CHAR,$_POST["penjualan_nomor"]).",penjualan_urut = '".$_POST["hidUrut"]."' where penjualan_id = ".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
         }
         $date = date('Y-m-d H:i:s');                
               
               $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[4] = QuoteValue(DPE_CHAR,'OA');
               $dbValue[5] = QuoteValue(DPE_CHAR,'100');//DIPATEN 100 untuk Penjualan Obat dari Luar
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
               $dbValue[9] = QuoteValue(DPE_CHARKEY,$poli);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,'2');
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[13] = QuoteValue(DPE_DATE,$date);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[15] = QuoteValue(DPE_CHAR,$idPemb["id_pembayaran"]); 
               $dbValue[16] = "'".$_POST["cust_usr_nama"]." (".$_POST["penjualan_alamat"].")'";
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["penjualan_nomor"]);     
               $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[20] = QuoteValue(DPE_NUMERIC,$jumlahTotalObat["total_item"]);
               $dbValue[21] = QuoteValue(DPE_CHAR,$usrId);
               $dbValue[22] = QuoteValue(DPE_CHAR,$usrId);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               
               $dtmodel->Insert() or die("insert  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);

               $sql = "update apotik.apotik_penjualan set id_fol=".QuoteValue(DPE_CHAR,$folId)."  , id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])."
                    where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
            $rs = $dtaccess->Execute($sql);
      }else{

      	$folId = $foliojualan["fol_id"];
      	$regId = $foliojualan["id_reg"];
      	$byrId = $foliojualan["id_pembayaran"];

       $dbTable = "klinik.klinik_folio";
          $dbField[0] = "fol_id";   // PK
          $dbField[1] = "id_reg";
          $dbField[2] = "fol_nama";
          $dbField[3] = "fol_nominal";
          $dbField[4] = "fol_jenis";
          $dbField[5] = "id_cust_usr";
          $dbField[6] = "fol_waktu";
          $dbField[7] = "fol_lunas";
          $dbField[8] = "id_biaya";                   
          $dbField[9] = "id_poli";
          $dbField[10] = "fol_jenis_pasien";
          $dbField[11] = "id_dep";
          $dbField[12] = "fol_dibayar";
          $dbField[13] = "fol_dibayar_when";
          $dbField[14] = "fol_total_harga";
          $dbField[15] = "id_pembayaran";
          $dbField[16] = "fol_keterangan";
          $dbField[17] = "fol_hrs_bayar";
          $dbField[18] = "fol_catatan";
          $dbField[19] = "fol_nominal_satuan";
          $dbField[20] = "fol_jumlah";
          $dbField[21] = "id_dokter";
          $dbField[22] = "who_when_update";
                                                  
         $date = date('Y-m-d H:i:s');                
               
            //   $folId = $dtaccess->GetTransID();
               $dbValue[0] = QuoteValue(DPE_CHAR,$folId);
               $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
               $dbValue[2] = QuoteValue(DPE_CHAR,'Penjualan Obat');
               $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[4] = QuoteValue(DPE_CHAR,'OA');
               $dbValue[5] = QuoteValue(DPE_CHAR,'100');//DIPATEN 100 untuk Penjualan Obat dari Luar
               $dbValue[6] = QuoteValue(DPE_DATE,$date);
               $dbValue[7] = QuoteValue(DPE_CHAR,'n');
               $dbValue[8] = QuoteValue(DPE_CHAR,'9999999');
               $dbValue[9] = QuoteValue(DPE_CHARKEY,$poli);
               $dbValue[10] = QuoteValue(DPE_NUMERICKEY,'2');
               $dbValue[11] = QuoteValue(DPE_CHAR,$depId);
               $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[13] = QuoteValue(DPE_DATE,$date);
               $dbValue[14] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[15] = QuoteValue(DPE_CHAR,$byrId); 
               $dbValue[16] = "'".$_POST["cust_usr_nama"]." (".$_POST["penjualan_alamat"].")'";
               $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["penjualan_nomor"]);     
               $dbValue[19] = QuoteValue(DPE_NUMERIC,StripCurrency($grandTotals));
               $dbValue[20] = QuoteValue(DPE_NUMERIC,$jumlahTotalObat["total_item"]);
               $dbValue[21] = QuoteValue(DPE_CHAR,$usrId);
               $dbValue[22] = QuoteValue(DPE_CHAR,$usrId);
               
               $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
               $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
               //print_r($dbValue);
               //die();
               
               $dtmodel->Update() or die("update  error");
               
               unset($dbField);
               unset($dtmodel);
               unset($dbValue);
               unset($dbKey);	
      }

              
      $sql = "select * from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId)." and id_dep =".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $dataJual = $dtaccess->FetchAll($rs); 
      // echo $sql; die();
      for($i=0,$n=count($dataJual);$i<$n;$i++){
        //hapus penjualan yang sebelumnya
          $sql = "delete from logistik.logistik_stok_item where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);

          $sql = "delete from logistik.logistik_stok_item_batch where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);
     /*     
           $sql = "select a.*, c.gudang_nama as nama_asal, d.gudang_nama as nama_tujuan
                         from logistik.logistik_stok_item a
                         left join logistik.logistik_gudang b on a.id_gudang = b.gudang_id
                         left join logistik.logistik_gudang c on a.id_dep_asal = c.gudang_id
                         left join logistik.logistik_gudang d on a.id_dep_tujuan = d.gudang_id";
                 $sql .= " where a.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
                 $sql .= " a.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
                 $sql .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
                 $sql .= " order by a.id_gudang asc, a.stok_item_create asc";
                 $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            	   $dataTable1 = $dtaccess->FetchAll($rs);
      // echo $sql; 
                 for($ia=0,$na=count($dataTable1);$ia<$na;$ia++)
                 {
                   if ($dataTable1[$ia]["stok_item_flag"]=='A') //Saldo Awal
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='PP') //Pemakaian
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='T' && $dataTable1[$ia]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='B') //Pembelian
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='P') //Penjualan
                     $saldo=$saldo-$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='O') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
                   if ($dataTable1[$ia]["stok_item_flag"]=='M') //Opname
                     $saldo=$saldo+$dataTable1[$ia]["stok_item_jumlah"];
            
                      //update saldo stok
//                      if ($saldo>0)
//                      {
                       $sql  ="update logistik.logistik_stok_item 
                               set stok_item_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                              where stok_item_id =".QuoteValue(DPE_CHAR,$dataTable1[$ia]["stok_item_id"]);
                        $df = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
                 //       echo $sql;              
//                       }
                } //akhir looping for stok item
//                      if ($saldo>0)
 //                     {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_dep 
                                set stok_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldo)." 
                                where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and 
                               id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                         $fg = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
//                     }               
                   
           //      echo $sql;

               //Adjustment Item Batch
               $sqlBatch = "select a.item_nama,b.batch_id,b.batch_no,c.* from 
                            logistik.logistik_item a left join 
                            logistik.logistik_item_batch b on b.id_item = a.item_id left join
                            logistik.logistik_stok_item_batch c on b.batch_id = c.id_batch";
               $sqlBatch .= " where c.id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and ";
               $sqlBatch .= " c.id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"])." and ";
               $sqlBatch .= " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
               $sqlBatch .= " order by a.item_nama,b.batch_id,a.id_gudang asc, c.stok_item_batch_create asc";
            //  echo $sqlBatch;

               $rsBatch = $dtaccess->Execute($sqlBatch,DB_SCHEMA_LOGISTIK);
          	   $dataBatch = $dtaccess->FetchAll($rsBatch);
                 for($k=0,$l=count($dataBatch);$k<$l;$k++)
                 {
                //   echo "ke".$k;
           
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='A') //Saldo Awal
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='PP') //Pemakaian
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]==null) //Transfer Penerimaan
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='T' && $dataBatch[$k]["id_dep_tujuan"]!=null) //Transfer Keluar
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='B') //Pembelian
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='P') //Penjualan
                     $saldoBatch=$saldoBatch-$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='O') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
                   if ($dataBatch[$k]["stok_item_batch_flag"]=='M') //Opname
                     $saldoBatch=$saldoBatch+$dataBatch[$k]["stok_item_batch_jumlah"];
            
                      //update saldo stok
                    //  if ($saldoBatch>0)
                    //  {
                       $sql  ="update logistik.logistik_stok_item_batch 
                               set stok_item_batch_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                              where stok_item_batch_id =".QuoteValue(DPE_CHAR,$dataBatch[$k]["stok_item_batch_id"]);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
                     //  }
            
                   //   if ($saldoBatch>0)
                   //   {
                        //update saldo stok
                        $sql  ="update logistik.logistik_stok_batch_dep 
                                set stok_batch_dep_saldo=".QuoteValue(DPE_NUMERIC,$saldoBatch)." 
                                where id_batch =".QuoteValue(DPE_CHAR,$dataBatch[$k]["batch_id"])." and 
                                id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                        $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);              
            
                    // }              
                    //echo "Adjust Batch : ".$dataBatch[$k]["batch_no"]." Berhasil <br>";

                    if($dataBatch[$k]["batch_id"]!=$dataBatch[$k+1]["batch_id"]) unset($saldoBatch);

                   } //end for batch
         
       */   
      // cek apakah ada dua batch atau lebih yg di input //
        if($dataJual[$i]["id_batch"]!=$dataJual[$i-1]["id_batch"]) {        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
 //echo $sql; //die();         
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
//echo $sql; die();           
           //stok batch yg lama - stok baru (dikurangi)
           $stokBatchNow[$i] = $dataDepBatch["total"] - $dataPenjualanStok["total"];
          
          
          // Langsung Update Stok Batch di Gudangnya //
          $sql  ="update logistik.logistik_stok_batch_dep set 
                  stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokBatchNow[$i]);
          $sql .=" , stok_batch_dep_create = current_timestamp";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]); 
          $rs = $dtaccess->Execute($sql);
         
         
         //END UPDATE POSISI STOK BATCH TERAKHIR 
         
         
         //UPDATE POSISI STOK TERAKHIR
         
         //cek di stok_dep untuk melihat stokterakhir
         $sql = "select stok_dep_saldo from logistik.logistik_stok_dep where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
         $sql .="and id_item =".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
         $sql .="and id_dep =".QuoteValue(DPE_CHAR,$depId);
         $sql .="order by stok_dep_create desc"; 
         $rs = $dtaccess->Execute($sql);
         $dataDep = $dtaccess->Fetch($rs);         
         
         //stok lama - stok baru (dikurangi)
          $stokNow[$i] = $dataDep["stok_dep_saldo"] - $dataJual[$i]["penjualan_detail_jumlah"];

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokNow[$i]);
          $sql .=" , stok_dep_create = current_timestamp";
          $sql .=" , stok_dep_tgl = current_date";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $rs = $dtaccess->Execute($sql);
          
          //---------------- END UPDATE POSISI STOK TERAKHIR
          //cari harga beli terakhir item
          $sql = " select item_harga_beli from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
           $rs = $dtaccess->Execute($sql);
           $dataHargabeli = $dtaccess->Fetch($rs);
          
          //insert kartu stok untuk histry batch untuk penjualan
          $dbTable = "logistik.logistik_stok_item_batch";
          $dbField[0]  = "stok_item_batch_id";   // PK
          $dbField[1]  = "stok_item_batch_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_batch_flag";
          $dbField[5]  = "stok_item_batch_create";
          $dbField[6]  = "stok_item_batch_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_batch_hpp";
          $dbField[11]  = "stok_item_batch_hna";
          $dbField[12]  = "stok_item_batch_hna_ppn_minus_diskon";
          $dbField[13]  = "id_batch";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_batch"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
          
          //insert kartu stok untuk penjualan
          $dbTable = "logistik.logistik_stok_item";
          $dbField[0]  = "stok_item_id";   // PK
          $dbField[1]  = "stok_item_jumlah";
          $dbField[2]  = "id_item";    
          $dbField[3]  = "id_gudang";
          $dbField[4]  = "stok_item_flag";
          $dbField[5]  = "stok_item_create";         
          $dbField[6]  = "stok_item_saldo";
          $dbField[7]  = "id_dep";
          $dbField[8]  = "stok_item_keterangan";
          $dbField[9]  = "id_penjualan";
          $dbField[10]  = "stok_item_hpp";
          $dbField[11]  = "stok_item_hna";
          $dbField[12]  = "stok_item_hna_ppn_minus_diskon";
          
          $date = date("Y-m-d H:i:s");
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataJual[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$stokNow[$i]); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataJual[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error");	
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);      
    } 
    
}   
?>