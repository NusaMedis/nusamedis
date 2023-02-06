<?php
	// LIBRARY
		 require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
			$dtaccess = new DataAccess();
			
			//INISIALISAI AWAL LIBRARY
			$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
			$dtaccess = new DataAccess();
			$enc = new textEncrypt();
			$auth = new CAuth();
	  	$depId = $auth->GetDepId();
		 $userName = $auth->GetUserName();
			$userId = $auth->GetUserId();
			$tahunTarif = $auth->GetTahunTarif();
			$userLogin = $auth->GetUserData();
		
		/* ambil data dari fol id */
		$sql = "select * from klinik.klinik_folio_pemakaian
		where fol_pemakaian_id = '$_POST[id]'";
		$data = $dtaccess->Fetch($sql);


		$sql = "select id_gudang from global.global_auth_poli
		 where poli_id = '$data[id_poli]'";
		$dataGudang = $dtaccess->Fetch($sql);
		$gudang = $dataGudang['id_gudang'];
	
		
		$sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang 
		=".QuoteValue(DPE_CHAR,$gudang);
		$sql .="and id_item =".QuoteValue(DPE_CHAR,$data["id_item"]);
		$sql .="order by stok_item_create desc"; 
		$dataDep= $dtaccess->Fetch($sql);
		

		$redoStok  =  intval($dataDep['stok_item_saldo']) + ($data['fol_pemakaian_jumlah']);

		/* update stok dep */
		$sql  ="update logistik.logistik_stok_dep set stok_dep_saldo ='$redoStok'";
		$sql .=" , stok_dep_create = current_timestamp";
		$sql .=" , stok_dep_tgl = current_date";
		$sql .=" where id_item = '$data[id_item]'";
		$sql .=" and id_gudang ='$gudang'";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
		
		/* delete di stok dep item */
		$sql = "delete from logistik.logistik_stok_item
		where id_pemakaian = '$_POST[id]'";
    $result1 = $dtaccess->Execute($sql);

	 //delete di klinik folio
	  $sql = "delete from klinik.klinik_folio_pemakaian
				where fol_pemakaian_id = '$_POST[id]'";
		$result = $dtaccess->Execute($sql);
		
				/* Adjusment */
				$saldo = 0;
				$sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$gudang)." and id_item = ".QuoteValue(DPE_CHAR,$data["id_item"])." order by id_gudang asc, stok_item_create asc";
				$dataAdjustment = $dtaccess->FetchAll($sql);

		   $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$gudang' and id_item = ".QuoteValue(DPE_CHAR,$data["id_item"])." order by stok_item_create desc limit 1";
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

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo = '$saldo', stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$data["id_item"])." and id_gudang = '$gudang'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
					

	  if ($result){
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('errorMsg'=>'Some errors occured.'));
		} 
	 
	 exit();      

?>