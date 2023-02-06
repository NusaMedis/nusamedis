<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();


     $kode = ($_GET['transaksi']) ? $enc->Decode($_GET['transaksi']) : "";

     if($_POST['btnSimpan']){
     	$dbTable = "apotik.apotik_nama_racikan";

        $dbField[0]  = "nama_racikan_id";   // PK
        $dbField[1]  = "nama_racikan_nama";
        $dbField[2]  = "nama_racikan_jenis";
       
      	$racikanId = $dtaccess->GetTransID();
        $namaRacikan = $_POST['nama_racikan'];
        $theDep = $_POST['id_gudang'];
        $dbValue[0] = QuoteValue(DPE_CHAR, $racikanId);
        $dbValue[1] = QuoteValue(DPE_CHAR, $namaRacikan);
        $dbValue[2] = QuoteValue(DPE_CHAR, $_POST['jenis_racikan']);

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert  error");     
          
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
    
    	$dbTable = "logistik.logistik_item";

        $dbField[0]  = "item_id";   // PK
        $dbField[1]  = "item_nama";
        $dbField[2]  = "item_racikan";
        $dbField[3]  = "item_harga_beli";
        $dbField[4]  = "item_harga_jual";
        $dbField[5]  = "id_satuan_jual";
       
        $dbValue[0] = QuoteValue(DPE_CHAR, $racikanId);
        $dbValue[1] = QuoteValue(DPE_CHAR, $namaRacikan);
        $dbValue[2] = QuoteValue(DPE_CHAR,'y');
        $dbValue[3] = QuoteValue(DPE_NUMERIC, $_POST['hrg_satuan']);
        $dbValue[4] = QuoteValue(DPE_NUMERIC, $_POST['hrg_satuan']);
        $dbValue[5] = QuoteValue(DPE_NUMERIC, $_POST['satuan_nama']);

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert  error");     
          
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);

        $dbTable = "logistik.logistik_item_batch";
               
        $dbField[0] = "batch_id";   // PK
        $dbField[1] = "id_item";
        $dbField[2] = "batch_no";
        $dbField[3] = "batch_create";
        $dbField[4] = "id_dep";
        $dbField[5] = "batch_flag";
               
        $batchId = $dtaccess->GetTransId();   
        $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$racikanId); 
        $dbValue[2] = QuoteValue(DPE_CHAR,$depId); 
        $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));    
        $dbValue[4] = QuoteValue(DPE_CHAR,$depId);
        $dbValue[5] = QuoteValue(DPE_CHAR,'A');
              
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_LOGISTIK);

		$dtmodel->Insert() or die("insert  error"); 

		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);

		$dbTable = "logistik.logistik_stok_item";
		$dbField[0]  = "stok_item_id";   // PK
		$dbField[1]  = "stok_item_jumlah";
		$dbField[2]  = "id_item";    
		$dbField[3]  = "id_gudang";
		$dbField[4]  = "stok_item_flag";
		$dbField[5]  = "stok_item_create";         
		$dbField[6]  = "stok_item_saldo";
		$dbField[7]  = "id_dep";

		$date = date("Y-m-d H:i:s");
		$stokid = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
		$dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
		$dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
		$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
		$dbValue[4] = QuoteValue(DPE_CHAR,'A');
		$dbValue[5] = QuoteValue(DPE_DATE,$date);
		$dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
		$dbValue[7] = QuoteValue(DPE_CHAR,$depId);

		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error"); 

		unset($dbTable);
		unset($dbField);
		unset($dbValue);
		unset($dbKey); 

		$dbTable = "logistik.logistik_stok_dep";
		$dbField[0]  = "stok_dep_id";   // PK
		$dbField[1]  = "stok_dep_saldo";
		$dbField[2]  = "id_item";    
		$dbField[3]  = "id_gudang";
		$dbField[4]  = "stok_dep_tgl";
		$dbField[5]  = "stok_dep_create";         
		$dbField[6]  = "id_dep";

		$date = date("Y-m-d H:i:s");
		$stokdepid = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$stokdepid);
		$dbValue[1] = QuoteValue(DPE_NUMERIC,'0');  
		$dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
		$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
		$dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
		$dbValue[5] = QuoteValue(DPE_DATE,$date); 
		$dbValue[6] = QuoteValue(DPE_CHAR,$depId);

		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error"); 

		unset($dbTable);
		unset($dbField);
		unset($dbValue);
		unset($dbKey); 

		$dbTable = "logistik.logistik_stok_item_batch";
		$dbField[0]  = "stok_item_batch_id";   // PK
		$dbField[1]  = "stok_item_batch_jumlah";
		$dbField[2]  = "id_item";    
		$dbField[3]  = "id_gudang";
		$dbField[4]  = "stok_item_batch_flag";
		$dbField[5]  = "stok_item_batch_create";         
		$dbField[6]  = "stok_item_batch_saldo";
		$dbField[7]  = "id_dep";
		$dbField[8]  = "id_batch";

		$date = date("Y-m-d H:i:s");
		$stokbatchid = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
		$dbValue[1] = QuoteValue(DPE_NUMERIC,0);  
		$dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
		$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
		$dbValue[4] = QuoteValue(DPE_CHAR,'A');
		$dbValue[5] = QuoteValue(DPE_DATE,$date);
		$dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
		$dbValue[7] = QuoteValue(DPE_CHAR,$depId);
		$dbValue[8] = QuoteValue(DPE_CHAR,$batchId);

		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error"); 

		unset($dbTable);
		unset($dbField);
		unset($dbValue);
		unset($dbKey); 

		$dbTable = "logistik.logistik_stok_batch_dep";
		$dbField[0]  = "stok_batch_dep_id";   // PK
		$dbField[1]  = "stok_batch_dep_saldo";
		$dbField[2]  = "id_item";    
		$dbField[3]  = "id_gudang";
		$dbField[4]  = "stok_batch_dep_tgl";
		$dbField[5]  = "stok_batch_dep_create";         
		$dbField[6]  = "id_dep";
		$dbField[7]  = "id_batch";

		$date = date("Y-m-d H:i:s");
		$stokbatchdepid = $dtaccess->GetTransID();
		$dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchdepid);
		$dbValue[1] = QuoteValue(DPE_NUMERIC,'0');  
		$dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
		$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
		$dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
		$dbValue[5] = QuoteValue(DPE_DATE,$date); 
		$dbValue[6] = QuoteValue(DPE_CHAR,$depId);
		$dbValue[7] = QuoteValue(DPE_CHAR,$batchId);

		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error"); 

		unset($dbTable);
		unset($dbField);
		unset($dbValue);
		unset($dbKey); 

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
		$dbField[13]  = "id_aturan_pakai";
		$dbField[14]  = "id_aturan_minum";
		$dbField[15] = "id_jam_aturan_pakai";
		$dbField[16]  = "item_nama";
		$dbField[17]  = "penjualan_detail_ppn";
		$dbField[18]  = "penjualan_detail_harga_pokok";
		$dbField[19]  = "penjualan_detail_harga_beli";

		$penjualandetId = ($_POST["id_penjualan_detail"]) ? $_POST["id_penjualan_detail"] : $dtaccess->GetTransID();

		$dbValue[0] = QuoteValue(DPE_CHAR,$penjualandetId);
		$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["penjualan_id"]);
		$dbValue[2] = QuoteValue(DPE_CHAR,$racikanId);
		$dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency(intval($_POST['hrg_satuan'])));
		$dbValue[4] = QuoteValue(DPE_NUMERIC,$_POST["quantity"]);
		$dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST['total']);  
		$dbValue[6] = QuoteValue(DPE_CHAR,'n');
		$dbValue[7] = QuoteValue(DPE_DATE,$date);
		$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dosis"]);
		$dbValue[9] = QuoteValue(DPE_CHAR,$depId);
		$dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST["quantity"]);
		$dbValue[11] = QuoteValue(DPE_CHAR,$batchId);
		$dbValue[12] = QuoteValue(DPE_NUMERIC, $_POST['hrgTuslagTot']);
		$dbValue[13] = QuoteValue(DPE_CHAR,$_POST["aturan_pakai"]);
		$dbValue[14] = QuoteValue(DPE_CHAR,$_POST["aturan_minum"]);
		$dbValue[15] = QuoteValue(DPE_CHAR,$_POST["jam_aturan_minum"]);
		$dbValue[16] = QuoteValue(DPE_CHAR,$namaRacikan);
		$dbValue[17] = QuoteValue(DPE_NUMERIC,$_POST['hrgPpnTot']);
		$dbValue[18] = QuoteValue(DPE_NUMERIC,$_POST['hrgPokokTot']);
		$dbValue[19] = QuoteValue(DPE_NUMERIC,$_POST['hrgHppTot']);

		//print_r($dbValue); die();
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error");  

		unset($dbField);
		unset($dbValue); 

		for($i=0; $i < count($_POST['item_id']); $i++){
			$racikanitemId = $_POST['item_id'][$i];

			$dbTable = "apotik.apotik_detail_racikan";
	        $dbField[0] = "detail_racikan_id";
	        $dbField[1] = "id_nama_racikan";
	        $dbField[2] = "id_item";
	        $dbField[3] = "item_nama";
	        $dbField[4] = "detail_racikan_jumlah";
	        $dbField[5] = "detail_racikan_total";
	        $dbField[6] = "item_harga_jual";
	        $dbField[7] = "when_create";
	        $dbField[8] = "who_create";
	        $dbField[9] = "detail_racikan_ppn";
	        $dbField[10] = "detail_racikan_hpp";
	        $dbField[11] = "detail_racikan_harga_pokok";
	        $dbField[12] = "detail_racikan_tuslag";

	        $date = date("Y-m-d H:i:s");
	        $detracikId = $dtaccess->GetTransID();

	        $dbValue[0] = QuoteValue(DPE_CHAR,$detracikId);
	        $dbValue[1] = QuoteValue(DPE_CHAR,$racikanId);  
	        $dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
	        $dbValue[3] = QuoteValue(DPE_CHAR,$_POST['item_nama'][$i]); 
	        $dbValue[4] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['jumlah'][$i]));  
	        $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['totAll'][$i]));
	        $dbValue[6] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hargamargin'][$i])); 
	        $dbValue[7] = QuoteValue(DPE_DATE,$date);
	        $dbValue[8] = QuoteValue(DPE_CHAR,$usrId);
	        $dbValue[9] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['ppnTot'][$i]));
	        $dbValue[10] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hppTot'][$i]));
	        $dbValue[11] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['hrgMarginTot'][$i]));
	        $dbValue[12] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST['tuslagTot'][$i]));
	                      
	        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
	              
	        $dtmodel->Insert() or die("insert  error"); 
	                        
	        unset($dbTable);
	        unset($dbField);
	        unset($dbValue);
	        unset($dbKey); 

			$dbTable = "logistik.logistik_stok_item";
			$dbField[0]  = "stok_item_id";   // PK
			$dbField[1]  = "stok_item_jumlah";
			$dbField[2]  = "id_item";    
			$dbField[3]  = "id_gudang";
			$dbField[4]  = "stok_item_flag";
			$dbField[5]  = "stok_item_create";         
			$dbField[6]  = "stok_item_saldo";
			$dbField[7]  = "id_dep";
			$dbField[8]  = "id_racikan";
			$dbField[9]  = "stok_item_hpp";
			$dbField[10]  = "stok_item_hpp_penjualan";
			$dbField[11]  = "id_penjualan";

			$date = date("Y-m-d H:i:s");

			$stokid = $dtaccess->GetTransID();
			$dbValue[0] = QuoteValue(DPE_CHAR,$stokid);
			$dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST['jumlah'][$i]);  
			$dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
			$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
			$dbValue[4] = QuoteValue(DPE_CHAR,'P');
			$dbValue[5] = QuoteValue(DPE_DATE,$date);
			$dbValue[6] = QuoteValue(DPE_NUMERIC,'0'); 
			$dbValue[7] = QuoteValue(DPE_CHAR,$depId);
			$dbValue[8] = QuoteValue(DPE_CHAR,$racikanId);
			$dbValue[9] = QuoteValue(DPE_NUMERIC,$_POST['hpp'][$i]);
			$dbValue[10] = QuoteValue(DPE_NUMERIC,$_POST['hppTot'][$i]);
			$dbValue[11] = QuoteValue(DPE_CHAR,$_POST["penjualan_id"]);

			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

			$dtmodel->Insert() or die("insert  error"); 

			unset($dbTable);
			unset($dbField);
			unset($dbValue);
			unset($dbKey); 

			$noww = date('Y-m-d H:i:s');
			$firsmonth = date('Y-m-01 00:00:00');

			$sql = "select * from logistik.logistik_stok_item where stok_item_create >= '$firsmonth' and stok_item_create <= '$noww' and id_gudang = '$theDep' and id_item = '$racikanitemId' order by id_gudang asc, stok_item_create asc";
			$dataAdjustment = $dtaccess->FetchAll($sql);

			$sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = '$racikanitemId' order by stok_item_create desc limit 1";
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

			$sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = '$racikanitemId' and id_gudang = '$theDep'";
			$dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

			$sql = "select batch_id from logistik.logistik_item_batch where id_item = ".QuoteValue(DPE_CHAR,$racikanitemId);
			$rs = $dtaccess->Execute($sql);
			$dataBatch = $dtaccess->Fetch($rs);                    
			//simpan stok_batch_item
			$dbTable = "logistik.logistik_stok_item_batch";
			$dbField[0]  = "stok_item_batch_id";   // PK
			$dbField[1]  = "stok_item_batch_jumlah";
			$dbField[2]  = "id_item";    
			$dbField[3]  = "id_gudang";
			$dbField[4]  = "stok_item_batch_flag";
			$dbField[5]  = "stok_item_batch_create";         
			$dbField[6]  = "stok_item_batch_saldo";
			$dbField[7]  = "id_dep";
			$dbField[8]  = "id_batch";
			$dbField[9]  = "id_racikan";

			$date = date("Y-m-d H:i:s");
			$stokbatchid = $dtaccess->GetTransID();
			$dbValue[0] = QuoteValue(DPE_CHAR,$stokbatchid);
			$dbValue[1] = QuoteValue(DPE_NUMERIC,$_POST['jumlah'][$i]);  
			$dbValue[2] = QuoteValue(DPE_CHAR,$racikanitemId);
			$dbValue[3] = QuoteValue(DPE_CHAR,$theDep); //departemen tujuan         
			$dbValue[4] = QuoteValue(DPE_CHAR,'P');
			$dbValue[5] = QuoteValue(DPE_DATE,$date);
			$dbValue[6] = QuoteValue(DPE_NUMERIC,$saldo); 
			$dbValue[7] = QuoteValue(DPE_CHAR,$depId);
			$dbValue[8] = QuoteValue(DPE_CHAR,$dataBatch["batch_id"]);
			$dbValue[9] = QuoteValue(DPE_CHAR,$racikanId);

			$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

			$dtmodel->Insert() or die("insert  error"); 

			unset($dbTable);
			unset($dbField);
			unset($dbValue);
			unset($dbKey); 

			//simpan stok_batch_dep
			$sql = "update logistik.logistik_stok_batch_dep set stok_batch_dep_saldo ='$saldo', stok_batch_dep_tgl=".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_batch =".QuoteValue(DPE_CHAR,$dataBatch['batch_id'])." and id_gudang = ".QuoteValue(DPE_CHAR,$theDep);
			$rs = $dtaccess->Execute($sql);


		}

		header("location: ".$_POST['url_back']);
		exit();


     }

     $sql = "SELECT a.*, b.cust_usr_nama from apotik.apotik_penjualan a 
     		left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
      where penjualan_id = '$kode'";
     $dataPenj = $dtaccess->Fetch($sql);

     $sql = "select * from apotik.apotik_nama_racikan ";  
     $dataRacikan = $dtaccess->FetchAll($sql);

     //combo satuan jual
    $sql = "select * from logistik.logistik_item_satuan where satuan_tipe ='J'
            order by satuan_nama asc";
    $dataSatuan = $dtaccess->FetchAll($sql);

    $sql = "select * from apotik.apotik_jenis_racikan 
            order by jenis_racikan_nama asc";
    $jenisRacikan = $dtaccess->FetchAll($sql);

    //combo dosis
    $sql = "select * from apotik.apotik_obat_petunjuk
            order by petunjuk_nama asc";
    $dataDosis = $dtaccess->FetchAll($sql);

    //combo aturan minum
    $sql = "select * from apotik.apotik_aturan_minum
            order by aturan_minum_nama asc";
    $dataAtMinum = $dtaccess->FetchAll($sql);

    //combo aturan pakai
    $sql = "select * from apotik.apotik_aturan_pakai
            order by aturan_pakai_nama asc";
    $dataAtPakai = $dtaccess->FetchAll($sql);

    // jam aturan pakai
    $sql = "select * from apotik.apotik_jam_aturan_pakai
            order by jam_aturan_pakai_nama asc";
    $dataJam = $dtaccess->FetchAll($sql);

    $namaRacikan = $dataPenj['cust_usr_nama']."_".date("YmdHis");
     
 ?>
 <!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <script type="text/javascript">
    function number_format (number, decimals, dec_point, thousands_sep) {
      // Strip all characters but numerical ones.
      number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
      var n = !isFinite(+number) ? 0 : +number,
          prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
          sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
          dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
          s = '',
          toFixedFix = function (n, prec) {
              var k = Math.pow(10, prec);
              return '' + Math.round(n * k) / k;
          };
      // Fix for IE parseFloat(0.55).toFixed(0) = 0;
      s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
      if (s[0].length > 3) {
          s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
      }
      if ((s[1] || '').length < prec) {
          s[1] = s[1] || '';
          s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    }
  </script>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Racikan</h3>
              </div>
            </div>
               <div class="clearfix"></div>
               <!-- row filter -->
               <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Input Racikan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <form style="display: none">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12">Racikan</label>
                            <div class="col-md-5 col-sm-5 col-xs-12">
                            <select class="form-control" name="id_racikan" id="id_racikan">
                                  <option value="">[- Pilih Racikan -]</option>
                                  <?php for($i=0,$n=count($dataRacikan);$i<$n;$i++) { ?>
                                    <option value="<?=$dataRacikan[$i]["nama_racikan_id"]?>"><?=$dataRacikan[$i]["nama_racikan_nama"]?></option>
                                  <?php } ?>               
                             </select>
                            </div>
                        </div>  
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12">&nbsp;</label>
                            <div class="col-md-5 col-sm-5 col-xs-12">
                              <button class="btn btn-primary">Buat Master Baru</button>
                            </div>
                        </div>  
                      </form>
                  </div>
                </div>
              </div>
            </div>
               <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  </div>
                </div>
              </div>
            </div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST">
                      <div class="form-group">
                        <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%" id="ingTab">
                          <thead>
                            <tr>
                              <th width="5%"></th>
                              <th width="20%">Nama Barang</th>
                              <th width="20%">Harga Satuan</th>
                              <th width="20%">Jumlah</th>
                              <th width="20%">Harga</th>
                            </tr>
                          </thead>
                          <tbody>
                            
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="2">
                                <input id="item_nama" type="text" class="form-control">
                                <input id="item_id" type="hidden" class="form-control">
                                <input id="hargamargin" type="hidden" class="form-control">
                                <input id="tuslag" type="hidden" class="form-control">
                                <input id="hpp" type="hidden" class="form-control">

                                <input id="stok_gudang" type="hidden" class="form-control">

                                <input id="idRow" type="hidden" class="form-control">

                                <label id="stok_gudang"></label>
                              </td>
                              <td style="padding-top: 20px">
                                <label id="hargamargin"></label>
                              </td>
                              <td>
                                <input id="jumlah" type="text" class="form-control">
                              </td>
                              <td style="padding-top: 20px">
                                <label id="harga"></label>
                                <input id="hrgMarginTot" type="hidden" class="form-control">
                                <input id="ppnTot" type="hidden" class="form-control">
                                <input id="tuslagTot" type="hidden" class="form-control">
                                <input id="hppTot" type="hidden" class="form-control">
                                <input id="totAll" type="hidden" class="form-control">
                              </td>
                            </tr>
                            <tr>
                              <td colspan="4">
                                <button id="simpanDet" type="button" class="btn btn-primary">Simpan Bahan</button>
                              </td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <div class="form-group">
                        <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                          <tr>
                            <td>Nama Racikan</td>
                            <td>
                              <input name="nama_racikan" class="form-control" value="<?=$namaRacikan?>" required>
                            </td>
                          </tr>

                          <tr>
                            <td>Satuan</td>
                            <td>
                              <select name="satuan_nama" class="form-control">
                                <option>--Pilih Satuan--</option>
                                <?php for($i=0; $i < count($dataSatuan); $i++) { ?>
                                  <option value="<?=$dataSatuan[$i]['satuan_id']?>"><?=$dataSatuan[$i]['satuan_nama']?></option>
                                <?php } ?>
                              </select>
                            </td>
                          </tr>

                          <tr>
                            <td>Jenis Racikan</td>
                            <td>
                              <select name="jenis_racikan" class="form-control">
                                <option>--Pilih Jenis Racikan--</option>
                                <?php for($i=0; $i < count($jenisRacikan); $i++) { ?>
                                  <option value="<?=$jenisRacikan[$i]['jenis_racikan_id']?>"><?=$jenisRacikan[$i]['jenis_racikan_nama']?></option>
                                <?php } ?>
                              </select>
                            </td>
                          </tr>

                          <tr>
                            <td>Quantity</td>
                            <td>
                              <input name="quantity" class="form-control" required="">
                            </td>
                          </tr>

                          <tr>
                            <td>Harga Satuan</td>
                            <td>
                              <input name="hrg_satuan" class="form-control" readonly="">
                            </td>
                          </tr>

                          <tr>
                            <td>Total</td>
                            <td>
                              <input name="total" type="text" class="form-control" readonly="">

                              <input name="hrgPokokTot" type="hidden" class="form-control">
                              <input name="hrgHppTot" type="hidden" class="form-control">
                              <input name="hrgPpnTot" type="hidden" class="form-control">
                              <input name="hrgTuslagTot" type="hidden" class="form-control">
                            </td>
                          </tr>

                          <tr>
                            <td>Dosis</td>
                            <td>
                              <select name="dosis" class="form-control">
                                <option>--Pilih Dosis--</option>
								<?php for($i=0,$n=count($dataDosis);$i<$n;$i++) { ?>
									<option value="<?=$dataDosis[$i]["petunjuk_id"];?>"><?=$dataDosis[$i]["petunjuk_nama"]?></option>
								<?php } ?>    
                              </select>
                            </td>
                          </tr>

                          <tr>
                            <td>Aturan Pakai</td>
                            <td>
                              <select name="aturan_pakai" class="form-control">
                                <option>--Pilih Aturan Pakai--</option>
								<?php for($i=0,$n=count($dataAtPakai);$i<$n;$i++) { ?>
									<option value="<?=$dataAtPakai[$i]["aturan_pakai_id"]?>"><?=$dataAtPakai[$i]["aturan_pakai_nama"]?></option>
								<?php } ?>   
                              </select>
                            </td>
                          </tr>

                          <tr>
                            <td>Aturan Minum</td>
                            <td>
                              <select name="aturan_minum" class="form-control">
                                <option>--Pilih Aturan Minum--</option>
								<?php for($i=0,$n=count($dataAtMinum);$i<$n;$i++) { ?>
									<option value="<?=$dataAtMinum[$i]["aturan_minum_id"]?>"><?=$dataAtMinum[$i]["aturan_minum_nama"]?></option>
								<?php } ?> 
                              </select>
                            </td>
                          </tr>

                          <tr>
                            <td>Jam Aturan Pakai</td>
                            <td>
                              <select name="jam_aturan_minum" class="form-control">
                                <option>--Pilih Jam Aturan Pakai--</option>
								<?php for($i=0,$n=count($dataJam);$i<$n;$i++) { ?>
									<option value="<?=$dataJam[$i]["jam_aturan_pakai_id"];?>"><?=$dataJam[$i]["jam_aturan_pakai_nama"]?></option>
								<?php } ?>  
                              </select>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="2" align="center">
                            	<input type="hidden" name="btnSimpan" value="1">
                            	<input type="hidden" name="id_gudang" value="<?=$dataPenj['id_gudang']?>">
                            	<input type="hidden" name="penjualan_id" value="<?=$dataPenj['penjualan_id']?>">

                            	<input type="hidden" name="url_back" value="penjualan.php?kode=<?=$_GET['kode']?>&transaksi=<?=$_GET['transaksi']?>&idreg=<?=$_GET['id_reg']?>&id_pembayaran=<?=$_GET['id_pembayaran']?>">

                            	<button type="submit" class="btn btn-success">Simpan Racikan</button>
                            	<a href="penjualan.php?kode=<?=$_GET['kode']?>&transaksi=<?=$_GET['transaksi']?>&idreg=<?=$_GET['id_reg']?>&id_pembayaran=<?=$_GET['id_pembayaran']?>"><button type="button" class="btn btn-danger">Kembali</button></a>
                            </td>
                          </tr>

                        </table>
                      </div>  
                    </form> 

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--page content -->
<script type="text/javascript">

  function sortTr(){
  	var hrgTot = 0, hrgPokokTot = 0, hrgHppTot = 0, hrgPpnTot = 0, hrgTuslagTot = 0;

  	$("table#ingTab tbody tr").each(function(ind, val){
    	var temphrgTot = $(this).find("input[name='totAll["+ind+"]']").val();
    	var temphrgPokokTot = $(this).find("input[name='hrgMarginTot["+ind+"]']").val();
    	var temphrgHppTot = $(this).find("input[name='hppTot["+ind+"]']").val();
    	var temphrgPpnTot = $(this).find("input[name='ppnTot["+ind+"]']").val();
    	var temphrgTuslagTot = $(this).find("input[name='tuslagTot["+ind+"]']").val();

    	hrgTot += Number(temphrgTot); 
    	hrgPokokTot += Number(temphrgPokokTot);
    	hrgHppTot += Number(temphrgHppTot);
    	hrgPpnTot += Number(temphrgPpnTot);
    	hrgTuslagTot += Number(temphrgTuslagTot);

    });

    $("input[name='total']").val(hrgTot);
    $("input[name='hrgPokokTot']").val(hrgPokokTot);
    $("input[name='hrgHppTot']").val(hrgHppTot);
    $("input[name='hrgPpnTot']").val(hrgPpnTot);
    $("input[name='hrgTuslagTot']").val(hrgTuslagTot);
  }

  function getObat(){
    $("input#item_nama").autocomplete({
      serviceUrl: 'get_obat_racikan.php?id_gudang=<?=$dataPenj['id_gudang']?>',
      paramName: 'item_nama',
      transformResult: function(response) {
        var data = jQuery.parseJSON(response);

        return {
          suggestions: $.map(data, function(item) {
            return {
              value: item.item_nama,
              data: {
                item_kode: item.item_kode,
                item_nama: item.item_nama,
                item_id: item.item_id,
                item_harga_beli: item.item_harga_beli,
                hpp: item.hpp,
                item_harga_jual: item.item_harga_jual,
                item_tuslag: item.item_tuslag,
                batch_id: item.batch_id,
                batch_no: item.batch_no,
                ppn: item.ppn,
                batch_tgl_jatuh_tempo: item.batch_tgl_jatuh_tempo,
                tuslag: item.tuslag,
                hmargin: item.item_harga_margin,

                item_stok_alert: item.item_stok_alert,
                stok_batch_dep_saldo: item.stok_batch_dep_saldo,
              }
            };
          })
        };
      },
      onSelect: function(suggestion) {
          $(this).val(suggestion.data.item_nama);
          $("input#item_id").val(suggestion.data.item_id);

          $("input#hargamargin").val(suggestion.data.hmargin);
          $("label#hargamargin").text(number_format(suggestion.data.hmargin, 0, ',', '.'));
          $("input#tuslag").val(suggestion.data.tuslag);
          $("input#hpp").val(suggestion.data.hpp);

          $("input#stok_gudang").val(suggestion.data.stok_batch_dep_saldo);
          $("label#stok_gudang").text("Sisa stok : "+number_format(suggestion.data.stok_batch_dep_saldo, 2, ',', '.'));
      }
    });
  }

  $(document).ready(function(){
    getObat();
  });

  $("input#jumlah").keyup(function(){
    var hargamargin = $("input#hargamargin").val();
    var tuslag = $("input#tuslag").val();
    var hpp = $("input#hpp").val();

    var jumlah = $("input#jumlah").val();

    duit = 1.1 * hargamargin;
    hppTot = hpp * jumlah;
    tuslagTot = (tuslag / 100 * duit) * jumlah;
    hrgMarginTot = hargamargin * jumlah;
    ppnTot = (hrgMarginTot * 0.1);

    totAll = parseInt(parseInt(hrgMarginTot) + parseInt(ppnTot) + parseInt(tuslagTot));

    $("input#hrgMarginTot").val(parseInt(hrgMarginTot));
    $("input#ppnTot").val(parseInt(ppnTot));
    $("input#tuslagTot").val(parseInt(tuslagTot));
    $("input#hppTot").val(hppTot);
    $("input#totAll").val(totAll);
    $("label#harga").text(number_format(totAll, 0, ',', '.'));
  });

  $("button#simpanDet").click(function(){
    var item_nama = $("input#item_nama").val();
    var item_id = $("input#item_id").val();
    var hargamargin = $("input#hargamargin").val();
    var tuslag = $("input#tuslag").val();
    var hpp = $("input#hpp").val();

    var stok_gudang = $("input#stok_gudang").val();

    var jumlah = $("input#jumlah").val();

    var hrgMarginTot = $("input#hrgMarginTot").val();
    var ppnTot = $("input#ppnTot").val();
    var tuslagTot = $("input#tuslagTot").val();
    var hppTot = $("input#hppTot").val();
    var totAll = $("input#totAll").val();

    var cRow = $("table#ingTab tbody tr").length;

    var html = "";

    if(Number(jumlah) > Number(stok_gudang) || jumlah.length == 0){
    	if(Number(jumlah) > Number(stok_gudang)) alert("Stok Tidak Cukup");
    	if(jumlah.length == 0) alert("Jumlah Belum Terisi");
    	
    }
    else{


	    html += "<tr>";
	    html += "<td align='center'>";
	    html += "<button id='deleteRow' class='btn btn-danger'><span class='glyphicon glyphicon-remove'></span</button>";
	    //html += "<button id='deleteRow' class='btn btn-warning'><span class='glyphicon glyphicon-pencil'></span</button>";
	    html += "</td>";

	    html += "<td>"+item_nama;
	    html += "<input type='hidden' id='item_namaTr' name='item_nama["+cRow+"]' value='"+item_nama+"'>";
	    html += "<input type='hidden' id='item_idTr' name='item_id["+cRow+"]' value='"+item_id+"'>";
	    html += "<input type='hidden' id='hargamarginTr' name='hargamargin["+cRow+"]' value='"+hargamargin+"'>";
	    html += "<input type='hidden' id='hppTr' name='hpp["+cRow+"]' value='"+hpp+"'>";
	    html += "<input type='hidden' id='tuslagTr' name='tuslag["+cRow+"]' value='"+tuslag+"'>";
	    html += "</td>";

	    html += "<td>"+number_format(hargamargin, 0, ',', '.')+"</td>";

	    html += "<td>"+jumlah;
	    html += "<input type='hidden' id='jumlahTr' name='jumlah["+cRow+"]' value='"+jumlah+"'>";
	    html += "</td>";

	    html += "<td>"+number_format(totAll, 0, ',', '.');
	    html += "<input type='hidden' id='hrgMarginTotTr' name='hrgMarginTot["+cRow+"]' value='"+hrgMarginTot+"'>";
	    html += "<input type='hidden' id='ppnTotTr' name='ppnTot["+cRow+"]' value='"+ppnTot+"'>";
	    html += "<input type='hidden' id='tuslagTotTr' name='tuslagTot["+cRow+"]' value='"+tuslagTot+"'>";
	    html += "<input type='hidden' id='hppTotTr' name='hppTot["+cRow+"]' value='"+hppTot+"'>";
	    html += "<input type='hidden' id='totAllTr' name='totAll["+cRow+"]' value='"+totAll+"'>";
	    html += "</td>";

	    html += "</tr>";

	    $("table#ingTab tbody").append(html);

	    sortTr();

	    $("input#item_nama").val("");
	    $("input#item_id").val("");
	    $("input#hargamargin").val("");
	    $("label#hargamargin").text("");
	    $("input#tuslag").val("");
	    $("input#hpp").val("");
	    $("input#stok_gudang").val("");
	    $("label#stok_gudang").text("");
	    $("input#jumlah").val("");
	    $("input#hrgMarginTot").val("");
	    $("input#ppnTot").val("");
	    $("input#tuslagTot").val("");
	    $("input#hppTot").val("");
	    $("input#totAll").val("");
	    $("label#harga").text("");
	}

  });

  $("input[name='quantity']").keyup(function(){
  	var quantity = $(this).val();
  	var hrgTot = $("input[name='total']").val();

  	var satuan = hrgTot/quantity;

  	$("input[name='hrg_satuan']").val(satuan);
  });

  $("table#ingTab").on("click", "button#deleteRow", function(){
  	$(this).parents("tr").remove();

  	$("table#ingTab tbody tr").each(function(ind, val){
    	$(this).find("input#item_namaTr").attr("name", "item_nama["+ind+"]");
    	$(this).find("input#item_idTr").attr("name", "item_id["+ind+"]");
    	$(this).find("input#hargamarginTr").attr("name", "hargamargin["+ind+"]");
    	$(this).find("input#hppTr").attr("name", "hpp["+ind+"]");
    	$(this).find("input#tuslagTr").attr("name", "tuslag["+ind+"]");

    	$(this).find("input#jumlahTr").attr("name", "jumlah["+ind+"]");

    	$(this).find("input#hrgMarginTotTr").attr("name", "hrgMarginTot["+ind+"]");
    	$(this).find("input#ppnTotTr").attr("name", "ppnTot["+ind+"]");
    	$(this).find("input#tuslagTotTr").attr("name", "tuslagTot["+ind+"]");
    	$(this).find("input#hppTotTr").attr("name", "hppTot["+ind+"]");
    	$(this).find("input#totAllTr").attr("name", "totAll["+ind+"]");

    });

  	sortTr();
  });
  	
  
</script>
        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
