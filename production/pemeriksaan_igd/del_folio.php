<?php
	// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
   	 $dtaccess = new DataAccess();
    
    $sql = "select id_pembayaran, id_poli from klinik.klinik_folio where
   fol_lunas = 'n' and fol_id = ".QuoteValue(DPE_CHAR,$_POST["id"]); 
   
   $rs = $dtaccess->Execute($sql);
   $dataFolio = $dtaccess->Fetch($rs);
   $idPembayaran = $dataFolio["id_pembayaran"];
   $idPoli = $dataFolio["id_poli"];
   
	

	 if($idPembayaran){

	 	$sql = "SELECT id_gudang from global.global_auth_poli where poli_id = '$idPoli'";
	 	$gudang = $dtaccess->Fetch($sql);

	 	$theDep = $gudang['id_gudang'];

	 	$sql = "SELECT fol_pemakaian_id, id_item from klinik.klinik_folio_pemakaian where id_fol = ".QuoteValue(DPE_CHAR, $_POST['id']);
	 	$dataBHP = $dtaccess->FetchAll($sql);

	 	for ($i = 0, $n = count($dataBHP); $i<$n ; $i++){

	 		$sql = "DELETE from logistik.logistik_stok_item where id_pemakaian = ".QuoteValue(DPE_CHAR, $dataBHP[$i]['fol_pemakaian_id']);
	 		$dtaccess->Execute($sql);

	 		$noww = date('Y-m-d H:i:s');
        $firsmonth = date('Y-m-01 00:00:00');

		/* Adjusment */
				$saldo = 0;
				$sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["id_item"])." order by id_gudang asc, stok_item_create asc";
				$dataAdjustment = $dtaccess->FetchAll($sql);

		   $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["id_item"])." order by stok_item_create desc limit 1";
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
        if(count($dataAdjustment) > 0){
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
	        if ($dataAdjustment[$ls]["stok_item_flag"]=='M') $saldo=$saldo+$dataAdjustment[$ls]["stok_item_jumlah"]; //Retur Penjualan
	        
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
	  	}

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo ='$saldo', stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["id_item"])." and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);


	 	}

	 	 //delete di klinik folio
	 $sql = "delete from klinik.klinik_folio
				where fol_lunas = 'n' and fol_id = '$_POST[id]'";
	 $result = $dtaccess->Execute($sql);

	 	#update klinik pembayaran
			$sql = "select sum(fol_nominal) as total from klinik.klinik_folio where
		   id_pembayaran = ".QuoteValue(DPE_CHAR,$idPembayaran); 
		   $rs = $dtaccess->Execute($sql);
		   $dataFolio = $dtaccess->Fetch($rs);  
		   
		   
		   
			$sql = "update klinik.klinik_pembayaran set pembayaran_total = ".QuoteValue(DPE_NUMERIC,$dataFolio["total"])."
						where pembayaran_id = '".$idPembayaran."'";
			$result = $dtaccess->Execute($sql); 
		   
			 
			  if ($result){
					echo json_encode(array('success'=>true));
				} else {
					echo json_encode(array('errorMsg'=>'Some errors occured.'));
				} 
	 }
	 else{
	 	echo json_encode(array('errorMsg'=>'Sudah dibayar, tidak bisa diedit'));
	 }
   
   
   
	 
	 exit();      

?>