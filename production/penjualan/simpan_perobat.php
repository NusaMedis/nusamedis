<?php

/* cari gudang */
      
      $sql = "select id_gudang from apotik.apotik_penjualan where penjualan_id=".QuoteValue(DPE_CHAR,$penjualanId);
      $rs = $dtaccess->Execute($sql);
      $gudang = $dtaccess->Fetch($rs); 
      $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  

      $sql = "DELETE from logistik.logistik_stok_item where id_penjualan = ".QuoteValue(DPE_CHAR, $penjualanId);
      $dtaccess->Execute($sql);

      $sql = "DELETE from logistik.logistik_stok_item_batch where id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
          $dtaccess->Execute($sql);

      $sql = "SELECT * from apotik.apotik_penjualan_detail where id_penjualan = ".QuoteValue(DPE_CHAR, $penjualanId);
      $dataObat = $dtaccess->FetchAll($sql);

      for ($i = 0, $n = count($dataObat); $i < $n ; $i++){

    /* Cari jml Stok di gudang */
        $sql = "SELECT stok_dep_saldo from logistik.logistik_stok_dep 
        where id_item = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
        $stok= $dtaccess->Fetch($sql); 

        $sisa_stok = $stok['stok_dep_saldo'] - $dataObat[$i]["penjualan_detail_jumlah"] ;

        $sql = "select item_harga_beli, item_hpp, item_harga_diskon, item_generik, item_racikan, obat_flag from logistik.logistik_item where item_id = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"]);
        $rs = $dtaccess->Execute($sql);
        $dataHargabeli = $dtaccess->Fetch($rs);

        if($dataHargabeli['item_racikan'] == 'y' || $dataHargabeli['item_racikan'] == 'y  '){
          $id_racikan = $dataObat[$i]["id_item"];

           $sql = "SELECT * from apotik.apotik_nama_racikan where nama_racikan_id = '$id_racikan'";
           $namaRacikan = $dtaccess->Fetch($sql);

          $sql = "SELECT a.*, item_hpp, item_harga_diskon, item_generik, obat_flag from apotik.apotik_detail_racikan a 
          left join logistik.logistik_item b on a.id_item = b.item_id 
          where id_nama_racikan = '$id_racikan'";
          $racikan = $dtaccess->FetchAll($sql);

          for($a = 0; $a < count($racikan); $a++){
            $id_item = $racikan[$a]['id_item'];
            $komposisi = $racikan[$a]['detail_racikan_jumlah'];
            $hpp = ($racikan[$a]['obat_flag'] == 'g') ? $racikan[$a]['item_harga_diskon'] : $racikan[$a]['item_hpp'];

            $penggunaan = $komposisi;

            $hppPenj = $penggunaan * intval($hpp);

            $sql = "SELECT stok_dep_saldo from logistik.logistik_stok_dep 
            where id_item = '$id_item' and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
            $stokRacikan = $dtaccess->Fetch($sql); 

            $sisa_stokRacikan = $stokRacikan['stok_dep_saldo'] - $penggunaan ;

            $sql = "select item_harga_beli from logistik.logistik_item where item_id = '$id_item'";
            $dataHargabeliRacikan = $dtaccess->Fetch($sql);

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
           $dbField[13]  = "stok_item_hpp_penjualan";
           $dbField[14]  = "id_racikan";
           
           $date = $dataObat[$i]["penjualan_detail_create"];
           $stokid = $dtaccess->GetTransID();
           $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
           $dbValue[1] = QuoteValue(DPE_NUMERIC,$penggunaan);  
           $dbValue[2] = QuoteValue(DPE_CHAR,$id_item);
           $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
           $dbValue[4] = QuoteValue(DPE_CHAR,'P');
           $dbValue[5] = QuoteValue(DPE_DATE,$date);
           $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stokRacikan)); 
           $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
           $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
           $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
           $dbValue[10] = QuoteValue(DPE_NUMERIC, $hpp);
           $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeliRacikan["item_harga_beli"]);
           $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeliRacikan["item_harga_beli"]);
           $dbValue[13] = QuoteValue(DPE_NUMERIC, $racikan[$a]['detail_racikan_hpp']);
           $dbValue[14] = QuoteValue(DPE_CHAR, $id_racikan);
           
              
           $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
           $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
           $dtmodel->Insert() or die("insert  error"); 
           unset($dbTable);
           unset($dbField);
           unset($dbValue);
           unset($dbKey); 
           

           unset($_POST["txtJumlah"]);

           $noww = date('Y-m-d H:i:s');
           $firsmonth = date('Y-m-01 00:00:00');

           $sql = "select * from logistik.logistik_stok_item where stok_item_create >= '$firsmonth' and stok_item_create <= '$noww' and id_gudang = '$theDep' and id_item = '$id_item' order by id_gudang asc, stok_item_create asc";
           $dataAdjustment = $dtaccess->FetchAll($sql);

           $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = '$id_item' order by stok_item_create desc limit 1";
           $lastData = $dtaccess->Fetch($sql);

           if(count($lastData['stok_item_saldo']) == 0){
             $saldo = 0;
           }
           else{
             $saldo = $lastData['stok_item_saldo'];
           }
           
           $arrayAdjusment = [];
	       $arrayStokItemId = [];
	       $arrayAdjusmentJumlah = [];

           
            /* SQL PENGURUTAN */
		for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
	        $StokItemId = $dataAdjustment[$ls]["stok_item_id"];
	        $stokItemJumlah = ($dataAdjustment[$ls]["stok_item_flag"]=='O') ? $dataAdjustment[$ls]["stok_item_saldo"] - $saldo : $dataAdjustment[$ls]["stok_item_jumlah"];

	        if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$dataAdjustment[$ls]["stok_item_saldo"]; //Opname
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
	        if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
	        
	        $arrayAdjusment[] = " when stok_item_id = '$StokItemId' then $saldo ";
	        $arrayAdjusmentJumlah[] = " when stok_item_id = '$StokItemId' then $stokItemJumlah ";
	        $arrayStokItemId[] = "'$StokItemId'";

      	}

	      $strAdjusment = implode(" ", $arrayAdjusment);
	      $strAdjusmentJumlah = implode(" ", $arrayAdjusmentJumlah);
	      $strStokItemId = implode(", ", $arrayStokItemId);

	      $sql = "UPDATE logistik.logistik_stok_item set stok_item_saldo = ( case 
	              $strAdjusment
	              end ),
	              stok_item_jumlah = ( case 
	              $strAdjusmentJumlah 
	              end)
	              where stok_item_id in ($strStokItemId) ";
	      $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = '$id_item' and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            

          }

        }
        else{

    /* insert data logistik */
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
       $dbField[13]  = "stok_item_hpp_penjualan";
       
       $date = $dataObat[$i]["penjualan_detail_create"];
       $stokid = $dtaccess->GetTransID();
       $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
       $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataObat[$i]["penjualan_detail_jumlah"]);  
       $dbValue[2] = QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"]);
       $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
       $dbValue[4] = QuoteValue(DPE_CHAR,'P');
       $dbValue[5] = QuoteValue(DPE_DATE,$date);
       $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($sisa_stok)); 
       $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
       $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
       $dbValue[10] = ($dataHargabeli["obat_flag"] == 'g') ? QuoteValue(DPE_NUMERIC,StripCurrency($dataHargabeli["item_harga_diskon"])) : QuoteValue(DPE_NUMERIC,StripCurrency($dataHargabeli["item_hpp"]));
       $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
       $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,$dataObat[$i]["penjualan_detail_harga_beli"]);
       
          
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       $dtmodel->Insert() or die("insert  error"); 
       unset($dbTable);
       unset($dbField);
       unset($dbValue);
       unset($dbKey); 
       

  unset($_POST["txtJumlah"]);
  /* SQL PENGURUTAN */

            if($dataObat[$i]["id_batch"]!=$dataObat[$i-1]["id_batch"]) {        
          
           // Data transaksi penjualan buffer //      
           $sql = "select sum(penjualan_detail_jumlah) as total from apotik.apotik_penjualan_detail
                   where id_batch = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_batch"])." and id_dep = ".QuoteValue(DPE_CHAR,$depId)." and id_penjualan =".QuoteValue(DPE_CHAR,$penjualanId);
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           $dataPenjualanStok = $dtaccess->Fetch($rs);
 //echo $sql; //die();         
           //UPDATE POSISI STOK BATCH TERAKHIR  
          
           // Cek Saldo Batch terakhir //
           $sql = "select sum(stok_batch_dep_saldo) as total from logistik.logistik_stok_batch_dep 
           where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
           $sql .=" and id_item =".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"]);
           $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
           $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataObat[$i]["id_batch"]); 
           $rs = $dtaccess->Execute($sql);
           $dataDepBatch = $dtaccess->Fetch($rs);
//echo $sql; die();           
           //stok batch yg lama - stok baru (dikurangi)
           $stokBatchNow[$i] = $dataDepBatch["total"] - $dataPenjualanStok["total"];
          
          
          // Langsung Update Stok Batch di Gudangnya //
          $sql  ="update logistik.logistik_stok_batch_dep set 
                  stok_batch_dep_saldo =".QuoteValue(DPE_NUMERIC,$stokBatchNow[$i]);
          $sql .=" , stok_batch_dep_create = current_timestamp";
          $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"]);
          $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
          $sql .=" and id_dep =".QuoteValue(DPE_CHAR,$depId);
          $sql .=" and id_batch =".QuoteValue(DPE_CHAR,$dataObat[$i]["id_batch"]); 
          $rs = $dtaccess->Execute($sql);

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
          
          
          $stokid = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
          $dbValue[1] = QuoteValue(DPE_NUMERIC,$dataObat[$i]["penjualan_detail_jumlah"]);  
          $dbValue[2] = QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
          $dbValue[4] = QuoteValue(DPE_CHAR,'P');
          $dbValue[5] = QuoteValue(DPE_DATE,$date);
          $dbValue[6] = QuoteValue(DPE_NUMERIC,$sisa_stok); 
          $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
          $dbValue[8] = QuoteValue(DPE_CHAR,"(".$_POST["penjualan_no"].")");
          $dbValue[9] = QuoteValue(DPE_CHAR,$penjualanId);
          $dbValue[10] = QuoteValue(DPE_NUMERIC,$dataObat[$i]["penjualan_detail_harga_jual"]);
          $dbValue[11] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataHargabeli["item_harga_beli"]);
          $dbValue[13] = QuoteValue(DPE_CHAR,$dataObat[$i]["id_batch"]);
          
          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert  error"); 
          
          unset($dbTable);
          unset($dbField);
          unset($dbValue);
          unset($dbKey);
        }
            $noww = date('Y-m-d H:i:s');
            $firsmonth = date('Y-m-01 00:00:00');

             $sql = "select * from logistik.logistik_stok_item where stok_item_create >= '$firsmonth' and stok_item_create <= '$noww' and id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"])." order by id_gudang asc, stok_item_create asc";
             $dataAdjustment = $dtaccess->FetchAll($sql);

             $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"])." order by stok_item_create desc limit 1";
              $lastData = $dtaccess->Fetch($sql);

              if(count($lastData['stok_item_saldo']) == 0){
                $saldo = 0;
              }
              else{
                $saldo = $lastData['stok_item_saldo'];
              }
              

           
            /* SQL PENGURUTAN */

            for ($ls=0,$qs=count($dataAdjustment);$ls<$qs;$ls++) {
              if ($dataAdjustment[$ls]["stok_item_flag"]=='A') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Saldo Awal
              if ($dataAdjustment[$ls]["stok_item_flag"]=='PP') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Pemakaian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Penerimaan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='T' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Transfer Keluar
              if ($dataAdjustment[$ls]["stok_item_flag"]=='B') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Pembelian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='P') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Penjualan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='O') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Opname
              if ($dataAdjustment[$ls]["stok_item_flag"]=='K') $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Pembelian
              if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]==null) $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Penerimaan
              if ($dataAdjustment[$ls]["stok_item_flag"]=='L' && $dataAdjustment[$ls]["id_dep_tujuan"]!=null) $saldo=$saldo-$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur ke Gudang Keluar
              if ($dataAdjustment[$l]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
              
              $sql  ="update logistik.logistik_stok_item set stok_item_saldo=".$saldo." where stok_item_id =".QuoteValue(DPE_CHAR,$dataAdjustment[$ls]["stok_item_id"]);
              $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
            }

            $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$dataObat[$i]["id_item"])." and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
            $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

            }

}
?>