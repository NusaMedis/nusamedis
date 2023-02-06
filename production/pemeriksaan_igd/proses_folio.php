<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

//INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();

$reg_id_baru = $dtaccess->GetTransID();
$fol_id_baru = $dtaccess->GetTransID();
$rt_id_baru = $dtaccess->GetTransID();
$reg_id_lama = $_POST["id_reg"];
$fol_id_lama = $_POST["fol_id"];
$rt_id_lama = $_POST["rawat_tindakan_id"];
if ($_POST['isNewRecord']) {
	$fol_id = $fol_id_baru;
	$reg_id = $reg_id_lama;
	$rt_id = $rt_id_baru;
} else {
	$fol_id = $fol_id_lama;
	$reg_id = $reg_id_lama;
	$rt_id = $rt_id_lama;
}


//cari data registrasi
$sql = "select reg_jenis_pasien,a.id_dokter, a.id_poli, a.id_cust_usr, a.id_pembayaran,b.rawat_id from klinik.klinik_registrasi a	
			left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
			where a.reg_id = '$reg_id_lama'";
$dataReg = $dtaccess->Fetch($sql);

$sql = "select id_gudang from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$dataReg['id_poli']);
	$dataGudang = $dtaccess->Fetch($sql);
	$theDep = $dataGudang['id_gudang'];

//cari nama dan nominal biaya
$sql = "select a.biaya_id,a.biaya_nama, a.biaya_jenis, a.biaya_jenis_sem, b.biaya_total, b.biaya_tarif_id
			from klinik.klinik_biaya a
			left join klinik.klinik_biaya_tarif b on a.biaya_id = b.id_biaya	
				where b.biaya_tarif_id = '$_POST[id_biaya_tarif]'";
$rs = $dtaccess->Execute($sql);
$biaya = $dtaccess->FetchAll($rs);
//die($sql);

#cari data fol pel id lama
$sql = "select fol_pelaksana_id from klinik.klinik_folio_pelaksana
			where id_fol = '$fol_id_lama'";
$rs = $dtaccess->Execute($sql);
$pel_lama = $dtaccess->FetchAll($rs);

//Data BHP
$sql = "select a.*,b.* from klinik.klinik_biaya_detil a
			left join logistik.logistik_item b on b.item_id = a.id_item
			where id_biaya = " . QuoteValue(DPE_CHAR, $biaya[0]["biaya_id"]);
$dataBHP = $dtaccess->FetchAll($sql);

// echo $sql;

//data pelaksana dan dokter
$dpjp = $dataReg['id_dokter']; //1
$pel1 = $userId; //2

# simpan klinik folio	
$dbTable = "klinik.klinik_folio";

$dbField[0] = "fol_id";   // PK
$dbField[1] = "id_reg";
$dbField[2] = "id_dokter";
$dbField[3] = "id_poli";
$dbField[4] = "id_cust_usr";
$dbField[5] = "id_biaya";
$dbField[6] = "id_pembayaran";
$dbField[7] = "fol_lunas";
$dbField[8] = "id_dep";
$dbField[9] = "fol_jumlah";
$dbField[10] = "who_when_update";
$dbField[11] = "fol_nama";
$dbField[12] = "fol_nominal_satuan";
$dbField[13] = "fol_nominal";
$dbField[14] = "fol_hrs_bayar";
$dbField[15] = "fol_dokter_instruksi";
$dbField[16] = "fol_pelaksana";
$dbField[17] = "id_biaya_tarif";
$dbField[18] = "fol_waktu";
$dbField[19] = "tindakan_waktu";
$dbField[20] = "tindakan_tanggal";
$dbField[21] = "fol_jenis";
$dbField[22] = "fol_jenis_sem";
/* 
	  $dbField[17] = "fol_waktu";
	  $dbField[17] = "fol_jenis_pasien";
	  $dbField[17] = "fol_total_harga";
	  $dbField[17] = "fol_dijamin"; */
//$dbField[17] = "fol_jenis_sem";
$tanggal = date_db($_POST["tindakan_tanggal"]);
$waktu = $_POST["tindakan_waktu"];

$dbValue[0] = QuoteValue(DPE_CHAR, $fol_id);
$dbValue[1] = QuoteValue(DPE_CHAR, $reg_id);
$dbValue[2] = QuoteValue(DPE_CHAR, $dpjp);
$dbValue[3] = QuoteValue(DPE_CHAR, $dataReg["id_poli"]);
$dbValue[4] = QuoteValue(DPE_CHAR, $dataReg["id_cust_usr"]);
$dbValue[5] = QuoteValue(DPE_CHAR, $biaya[0]["biaya_id"]);
$dbValue[6] = QuoteValue(DPE_CHAR, $dataReg["id_pembayaran"]);
$dbValue[7] = QuoteValue(DPE_CHAR, 'n');
$dbValue[8] = QuoteValue(DPE_CHAR, $depId);
$dbValue[9] = QuoteValue(DPE_CHAR, $_POST["fol_jumlah"]);
$dbValue[10] = QuoteValue(DPE_CHAR, $userId);
$dbValue[11] = QuoteValue(DPE_CHAR, $biaya[0]['biaya_nama']);
$dbValue[12] = QuoteValue(DPE_NUMERIC, $biaya[0]['biaya_total']);
$dbValue[13] = QuoteValue(DPE_NUMERIC, $biaya[0]['biaya_total'] * $_POST["fol_jumlah"]);
$dbValue[14] = QuoteValue(DPE_NUMERIC, $biaya[0]['biaya_total'] * $_POST["fol_jumlah"]);
$dbValue[15] = QuoteValue(DPE_CHAR, $dokter_ins);
$dbValue[16] = QuoteValue(DPE_CHAR, $pel1);
$dbValue[17] = QuoteValue(DPE_CHAR, $_POST["id_biaya_tarif"]);
$dbValue[18] = QuoteValue(DPE_DATE, $tanggal . " " . $waktu);
$dbValue[19] = QuoteValue(DPE_DATE, $waktu);
$dbValue[20] = QuoteValue(DPE_DATE, $tanggal);
$dbValue[21] = QuoteValue(DPE_CHAR, $biaya[0]['biaya_jenis']);
$dbValue[22] = QuoteValue(DPE_CHAR, $biaya[0]['biaya_jenis_sem']);
// $dbValue[17] = QuoteValue(DPE_CHAR,"LD");

$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

if ($_POST['isNewRecord'] == 'true') {
	$dtmodel->Insert() or die("insert  error");
	echo "sukses insert folio => ";
} else {
	$dtmodel->Update() or die("update  error");
	echo "sukses update folio => ";
}

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);

if($_POST['isNewRecord'] != 'true'){
	$sql = "select fol_pemakaian_id, id_item from klinik.klinik_folio_pemakaian where id_fol = '$fol_id'";
	$bhp = $dtaccess->FetchAll($sql);

	for ($i = 0, $n = count($bhp); $i<$n ; $i++){
		$sql = "delete from logistik.logistik_stok_item where id_pemakaian = ".QuoteValue(DPE_CHAR, $bhp[$i]['fol_pemakaian_id']);
		$dtaccess->Execute($sql);

		$noww = date('Y-m-d H:i:s');
        $firsmonth = date('Y-m-01 00:00:00');

		/* Adjusment */
				$saldo = 0;
				$sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)." and id_item = ".QuoteValue(DPE_CHAR,$bhp[$i]['id_item'])." order by id_gudang asc, stok_item_create asc";
				$dataAdjustment = $dtaccess->FetchAll($sql);

		   $sql = "select * from logistik.logistik_stok_item where stok_item_create <= '$firsmonth' and id_gudang = '$theDep' and id_item = ".QuoteValue(DPE_CHAR,$bhp[$i]['id_item'])." order by stok_item_create desc limit 1";
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

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,StripCurrency($saldo)).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$bhp[$i]['id_item'])." and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	}

	$sql = "delete from klinik.klinik_folio_pemakaian where id_fol = '$fol_id'";
	$dtaccess->Execute($sql);

}

for ($i = 0; $i < count($dataBHP); $i++) {
	$dbTable = "klinik.klinik_folio_pemakaian";

	$dbField[0] = "fol_pemakaian_id";   // PK
	$dbField[1] = "id_fol";
	$dbField[2] = "id_item";
	//$dbField[3] = "id_batch"; 
	$dbField[3] = "fol_pemakaian_item_nama";
	$dbField[4] = "fol_pemakaian_jumlah";
	$dbField[5] = "id_dep";
	$dbField[6] = "id_biaya";
	$dbField[7] = "who_create";
	$dbField[8] = "when_create";
	$dbField[9] = "id_poli";

	$folPemakaianId = $dtaccess->GetTransID();
	$dbValue[0] = QuoteValue(DPE_CHAR, $folPemakaianId);
	$dbValue[1] = QuoteValue(DPE_CHAR, $fol_id);
	$dbValue[2] = QuoteValue(DPE_CHAR, $dataBHP[$i]['item_id']);
	//$dbValue[3] = QuoteValue(DPE_CHAR,$dataBHP[$i]["id_poli"]); 
	$dbValue[3] = QuoteValue(DPE_CHAR, $dataBHP[$i]["item_nama"]);
	$dbValue[4] = QuoteValue(DPE_CHAR, $dataBHP[$i]["biaya_detil_jumlah"]);
	$dbValue[5] = QuoteValue(DPE_CHAR, $depId);
	$dbValue[6] = QuoteValue(DPE_CHAR, $biaya[0]["biaya_id"]);
	$dbValue[7] = QuoteValue(DPE_CHAR, $userName);
	$dbValue[8] = QuoteValue(DPE_DATE, $tanggal . " " . $waktu);
	$dbValue[9] = QuoteValue(DPE_CHAR, $dataReg["id_poli"]);

	$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

	
		$dtmodel->Insert() or die("insert  error");
		echo "sukses insert folio detil => ";
	

	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);

	/*SQl Logisitik stok item */
				$sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang =".QuoteValue(DPE_CHAR,$theDep);
							$sql .="and id_item =".QuoteValue(DPE_CHAR,$dataBHP[$i]['item_id']);
							$sql .="order by stok_item_create desc"; 
							$dataDep = $dtaccess->Fetch($sql);
							$newStok = $dataDep['stok_item_saldo'] - $dataBHP[$i]["biaya_detil_jumlah"];
								
							$dbTable = "logistik.logistik_stok_item";
							$dbField[0]  = "stok_item_id";   // PK
							$dbField[1]  = "stok_item_jumlah"; //Pemakaian
							$dbField[2]  = "id_item";    
							$dbField[3]  = "id_gudang";
							$dbField[4]  = "stok_item_flag";
							$dbField[5]  = "stok_item_create";
							$dbField[6]  = "stok_item_saldo"; //Saldo saat ini
							$dbField[7]  = "id_dep";
							$dbField[8]  = "id_pemakaian";
							$dbField[9]  = "stok_item_hpp";

									$Stokitemid = $dtaccess->GetTransID();  
							
							$dbValue[0] = QuoteValue(DPE_CHAR,$Stokitemid);
							$dbValue[1] = QuoteValue(DPE_NUMERIC,$dataBHP[$i]["biaya_detil_jumlah"]);
							$dbValue[2] = QuoteValue(DPE_CHAR,$dataBHP[$i]["item_id"]);
							$dbValue[3] = QuoteValue(DPE_CHAR,$theDep);
							$dbValue[4] = QuoteValue(DPE_CHAR,'PP');
							$dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
							$dbValue[6] = QuoteValue(DPE_NUMERIC,$newStok);       
							$dbValue[7] = QuoteValue(DPE_CHAR,$depId);
							$dbValue[8] = QuoteValue(DPE_CHAR,$folPemakaianId);
							$dbValue[9] = QuoteValue(DPE_NUMERIC,$item_hpp);
							
							$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
							$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
				
							
								$dtmodel->Insert() or die("insert  error"); 
								echo "sukses insert fol pemakaian => " ;
							
								
							unset($dbField);
							unset($dbValue);
     
    /* Mabil stok gudang */
				$sql = "select * from logistik.logistik_stok_dep where id_gudang = ".QuoteValue(DPE_CHAR,$theDep)."
								and id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["item_id"]);
				$dataStokDep = $dtaccess->Fetch($sql);
				
					 
		/* Update stok dep di gudang  */
           if ($dataStokDep) {
             $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$newStok);
             $sql .=" , stok_dep_create = current_timestamp";
             $sql .=" , stok_dep_tgl = current_date";
             $sql .=" where id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["item_id"]);
             $sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$theDep);
             $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           }else{
             $dbTable = "logistik.logistik_stok_dep";
             $dbField[0]  = "stok_dep_id";   // PK
             $dbField[1]  = "id_item";
             $dbField[2]  = "stok_dep_saldo";    
             $dbField[3]  = "stok_dep_create";
             $dbField[4]  = "stok_dep_tgl";
             $dbField[5]  = "id_dep";
             $dbField[6]  = "id_gudang";
             
             $StokDepId = $dtaccess->GetTransID();
             $dbValue[0] = QuoteValue(DPE_CHAR,$StokDepId);
             $dbValue[1] = QuoteValue(DPE_CHAR,$dataBHP[$i]["item_id"]);
             $dbValue[2] = QuoteValue(DPE_NUMERIC,$newStok);
             $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
             $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
             $dbValue[5] = QuoteValue(DPE_CHAR,'9999999');
             $dbValue[6] = QuoteValue(DPE_CHAR,$theDep);       
             
             $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
             $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     
             $dtmodel->Insert() or die("update  error");
               
             unset($dbField);
             unset($dbValue);
					 }


				$noww = date('Y-m-d H:i:s');
        		$firsmonth = date('Y-m-01 00:00:00');
		/* Adjusment */
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

          $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC, $saldo).", stok_dep_tgl= ".QuoteValue(DPE_DATE,date('Y-m-d'))." where id_item = ".QuoteValue(DPE_CHAR,$dataBHP[$i]["id_item"])." and id_gudang = '$theDep'";
          $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
}

#update klinik pembayaran
$sql = "select sum(fol_nominal) as total from klinik.klinik_folio where
   id_pembayaran = " . QuoteValue(DPE_CHAR, $dataReg["id_pembayaran"]);
$rs = $dtaccess->Execute($sql);
$dataFolio = $dtaccess->Fetch($rs);

$dbTable = "klinik.klinik_pembayaran";
$dbField[0] = "pembayaran_id";   // PK
$dbField[1] = "pembayaran_total";

$dbValue[0] = QuoteValue(DPE_CHAR, $dataReg["id_pembayaran"]);
$dbValue[1] = QuoteValue(DPE_NUMERIC, $dataFolio["total"]);

$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GLOBAL);

$dtmodel->Update() or die("update  error");

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);

#cek perawatan
$sql_rawat = "select * from klinik.klinik_perawatan 
                  where id_reg = " . QuoteValue(DPE_CHAR, $reg_id);
$dataPerawat = $dtaccess->Fetch($sql_rawat);
#jika tidak ada maka isi
if (!$dataPerawat) {
	$rawat_id = $dtaccess->GetTransID();

	$dbTable = " klinik.klinik_perawatan";
	$dbField[0] = "rawat_id";   // PK
	$dbField[1] = "id_reg";
	$dbField[2] = "id_cust_usr";
	$dbField[3] = "rawat_waktu_kontrol";
	$dbField[4] = "rawat_tanggal";
	$dbField[5] = "rawat_flag";
	$dbField[6] = "rawat_flag_komen";
	$dbField[7] = "id_poli";
	$dbField[8] = "id_dep";
	$dbField[9] = "rawat_who_update";
	$dbField[10] = "rawat_waktu";

	$dbValue[0] = QuoteValue(DPE_CHAR, $rawat_id);   // PK
	$dbValue[1] = QuoteValue(DPE_CHAR, $reg_id);
	$dbValue[2] = QuoteValue(DPE_CHAR, $dataReg["id_cust_usr"]);
	$dbValue[3] = QuoteValue(DPE_CHAR, date("H:i:s"));
	$dbValue[4] = QuoteValue(DPE_DATE, date("Y-m-d"));
	$dbValue[5] = QuoteValue(DPE_CHAR, 'I');
	$dbValue[6] = QuoteValue(DPE_CHAR, 'RAWAT INAP');
	$dbValue[7] = QuoteValue(DPE_CHAR, $dataReg["id_poli"]);
	$dbValue[8] = QuoteValue(DPE_CHAR, $depId);
	$dbValue[9] = QuoteValue(DPE_CHAR, $userName);
	$dbValue[10] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));

	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_KLINIK);
	$dtmodel->Insert() or die("insert  error");
	echo " sukses input perawatan =>";

	unset($dtmodel);
	unset($dbValue);
	unset($dbField);
	unset($dbKey);
} else {
	$rawat_id = $dataReg["rawat_id"];
}


# simpan klinik perawatan tindakan  
$dbTable = "klinik.klinik_perawatan_tindakan";
$dbField[0] = "rawat_tindakan_id";   // PK
$dbField[1] = "id_fol";
$dbField[2] = "id_tindakan";
$dbField[3] = "rawat_tindakan_total";
$dbField[4] = "id_dokter";
$dbField[5] = "id_dep";
$dbField[6] = "id_rawat";
$dbField[7] = "rawat_tindakan_jumlah";
$dbField[8] = "is_sync";
$dbField[9] = "rawat_tindakan_flag";
//$dbField[9] = "rawat_tindakan_keterangan"; 
//$dbField[10] = "rawat_tindakan_keterangan_2"; 
//$dbField[10] = "rawat_tindakan_diskon"; 


$dbValue[0] = QuoteValue(DPE_CHAR, $rt_id);
$dbValue[1] = QuoteValue(DPE_CHAR, $fol_id);
$dbValue[2] = QuoteValue(DPE_CHAR, $biaya[0]["biaya_id"]);
$dbValue[3] = QuoteValue(DPE_CHAR, $biaya[0]['biaya_total'] * $_POST["fol_jumlah"]);
$dbValue[4] = QuoteValue(DPE_CHAR, $dpjp);
$dbValue[5] = QuoteValue(DPE_CHAR, $depId);
$dbValue[6] = QuoteValue(DPE_CHAR, $rawat_id);
$dbValue[7] = QuoteValue(DPE_NUMERIC, $_POST["fol_jumlah"]);
$dbValue[8] = QuoteValue(DPE_CHAR, "n");
$dbValue[9] = QuoteValue(DPE_CHAR, "J");
//$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["no_kantong"]);
//$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rhesus"]);
//$dbValue[10] = QuoteValue(DPE_CHAR,"");

$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
if ($_POST['isNewRecord'] == 'true') {
	$dtmodel->Insert() or die("insert  error");
	echo "sukses insert perawatan tndakan => ";
} else {
	$dtmodel->Update() or die("insert  error");
	echo "sukses update perawatan tndakan => ";
	#delete folio split
	$sql = "delete from klinik.klinik_folio_split where id_fol=" . QuoteValue(DPE_CHAR, $fol_id);
	$dtaccess->Execute($sql);
	echo "sukses hapus fol split lama => ";
}

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);




# simpan di pelaksana
//cari folio
$sql = "select fol_nominal, fol_jumlah from klinik.klinik_folio
			where fol_id = " . QuoteValue(DPE_CHAR, $fol_id);
$rs = $dtaccess->Execute($sql);
$folio = $dtaccess->Fetch($rs);
//print_r($folio);

//cari split biaya
$sql = "select id_split ,id_biaya, bea_split_persen,bea_split_nominal from klinik.klinik_biaya_split a	
			left join klinik.klinik_split b on a.id_split = b.split_id
			where id_biaya_tarif = " . QuoteValue(DPE_CHAR, $_POST["id_biaya_tarif"]);
$rs = $dtaccess->Execute($sql);
$biayaSplit = $dtaccess->FetchAll($rs);

//print_r($biayaSplit);


// 	$sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya =".QuoteValue(DPE_CHAR, $biaya[0]['biaya_id']);
//	$remunerasi = $dtaccess->FetchAll($sql);


#simpan split folio
#INSERT FOLIO SPLIT dan Biaya Remunerasi


for ($i = 0; $i < count($biayaSplit); $i++) {
	//INSERT KLINIK BIAYA SPLIT
	$dbTable = "klinik.klinik_folio_split";
	$dbField[0] = "folsplit_id";   // PK
	$dbField[1] = "id_fol";
	$dbField[2] = "id_split";
	$dbField[3] = "folsplit_nominal";
	$dbField[4] = "folsplit_nominal_satuan";
	$dbField[5] = "folsplit_jumlah";
	$dbField[6] = "id_dep";
	//$dbField[7] = "id_fol_pelaksana";

	$folSplitId = $dtaccess->GetTransID();

	$hasilSatuan = $biayaSplit[$i]["bea_split_nominal"];
	$hasil = ($hasilSatuan) * $folio["fol_jumlah"];

	$dbValue[0] = QuoteValue(DPE_CHAR, $folSplitId);
	$dbValue[1] = QuoteValue(DPE_CHAR, $fol_id);
	$dbValue[2] = QuoteValue(DPE_CHAR, $biayaSplit[$i]["id_split"]);
	$dbValue[3] = QuoteValue(DPE_NUMERIC, $hasil);
	$dbValue[4] = QuoteValue(DPE_NUMERIC, $biayaSplit[$i]["bea"] * $hasilSatuan);
	$dbValue[5] = QuoteValue(DPE_NUMERIC, $folio["fol_jumlah"]);
	$dbValue[6] = QuoteValue(DPE_NUMERIC, $depId);
	//$dbValue[7] = QuoteValue(DPE_CHAR,$folPelId);
	// print_r($dbValue); die();
	$dbKey[1] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
	$dtmodel->Insert() or die("insert  error");
	echo "sukses insert folio split =>" . $folSplitId;
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);

	/*
      //INSERT REMUNERASI PASIEN
			$sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya =".QuoteValue(DPE_CHAR, $biayaSplit[$i]['id_biaya']);
			$sql .= " and id_split = ".QuoteValue(DPE_CHAR, $biayaSplit[$i]['id_split']);
      //echo $sql;
			$rs = $dtaccess->Execute($sql);
			$remun = $dtaccess->FetchAll($rs);

			for ($x = 0; $x < count ($remun); $x++) 
      {

				$split_persen = (int) $biayaSplit[$i]['bea_split_persen'];
				$remun_persen = (int) $remun[$x]['biaya_remunerasi_prosentase'];
				$biaya_total = (int) $folio["fol_nominal"];
        
				$hasilRemunerasi = ($split_persen/100)*($remun_persen/100)*$biaya_total;

      	if($_POST['isNewRecord']=='true') {
      		$folPelId = $dtaccess->GetTransID();   
      	} else {
      	 	$folPelId = $pel_lama['fol_pelaksana_id'];
      	}
      	
      		# simpan di pelaksana
      		$dbTable = "klinik.klinik_folio_pelaksana";
      		$dbField[0] = "fol_pelaksana_id";   // PK
      		$dbField[1] = "id_fol";
      		$dbField[2] = "id_usr";
      		$dbField[3] = "id_fol_posisi";
      		$dbField[4] = "fol_pelaksana_nominal";
          $dbField[5] = "id_fol_split";
      		$dbField[6] = "fol_pelaksana_tipe";

      		$dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
      		$dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
      		$dbValue[2] = QuoteValue(DPE_CHAR,$dataReg['id_dokter']);
      		$dbValue[3] = QuoteValue(DPE_NUMERIC, $remun[$x]['id_folio_posisi']);
      		$dbValue[4] = QuoteValue(DPE_CHAR,$hasilRemunerasi);
          $dbValue[5] = QuoteValue(DPE_CHAR,$folSplitId);
          $dbValue[6] = QuoteValue(DPE_NUMERIC, $remun[$x]['id_folio_posisi']);
      		 
      		$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
      		//print_r($dbField);
      		//print_r($dbValue);
      		//print_r($dbKey);
      		//die();
      		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
      		
      		if($_POST['isNewRecord']=='true') {
      			$dtmodel->Insert() or die("insert  error");	
      			echo "sukses insert pelaksana => " ;
      		} else {
      			$dtmodel->Update() or die("insert  error");	
      			echo "sukses update pelaksana => " ;
      			//delete split dulu
      			/* $sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$_POST["id_fol"]);
      			$dtaccess->Execute($sql);
      			echo "sukses hapus fol split lama => "  
      		}
      			
      		unset($dtmodel);
      		unset($dbField);
      		unset($dbValue);
      		unset($dbKey);		
        

			}  //Akhir Biaya Remunerasi*/
}	//AKHIR BIAYA SPLIT        


// set default untuk petugas yang login 

$idSplit = "1"; //DIPATEN 1 untuk JASA MEDIK    

//cari folio
$sql = "select folsplit_id from klinik.klinik_folio_split 
			where id_fol = " . QuoteValue(DPE_CHAR, $fol_id_baru) . " and id_split='$idSplit'";
// echo $sql;
$rs = $dtaccess->Execute($sql);
$folioSplit = $dtaccess->Fetch($rs);

$sql = "select id_biaya_tarif from klinik.klinik_folio
			where fol_id = " . QuoteValue(DPE_CHAR, $fol_id_baru);
$rs = $dtaccess->Execute($sql);
$folio = $dtaccess->Fetch($rs);


//cari split biaya
$sql = "select bea_split_nominal from	klinik.klinik_biaya_split 
     			where id_biaya_tarif = '$folio[id_biaya_tarif]' and id_split='$idSplit'";
$rs = $dtaccess->Execute($sql);
// echo $sql;
$biayaSplit = $dtaccess->Fetch($rs);

//


//INSERT REMUNERASI PASIEN
$sql = "select * from klinik.klinik_biaya_remunerasi where id_biaya_tarif =" . QuoteValue(DPE_CHAR, $folio["id_biaya_tarif"]);
$sql .= " and id_split = " . QuoteValue(DPE_CHAR, $idSplit);
$sql .= " and id_folio_posisi = '2'" ;
$rs = $dtaccess->Execute($sql);
$remun = $dtaccess->Fetch($rs);

// if ($remun) {
// 	$remun_persen = (int) $remun['biaya_remunerasi_prosentase'];
// }else{
// 	$remun_persen = 100;
// }
// $biaya_split = (int) $biayaSplit["bea_split_nominal"];
// //echo "remun persen".$remun_persen;
// //echo "biaya split".$biaya_split;die();
// $hasilRemunerasi = ($remun_persen / 100) * $biaya_split;

if ($_POST['isNewRecord'] == 'true') {
	$folPelId = $dtaccess->GetTransID();
} else {
	$folPelId = $_POST['fol_pelaksana_id'];
}

# simpan di pelaksana
$dbTable = "klinik.klinik_folio_pelaksana";
$dbField[0] = "fol_pelaksana_id";   // PK
$dbField[1] = "id_fol";
$dbField[2] = "id_usr";
$dbField[3] = "id_fol_posisi";
$dbField[4] = "fol_pelaksana_nominal";
$dbField[5] = "id_fol_split";
$dbField[6] = "fol_pelaksana_tipe";

$dbValue[0] = QuoteValue(DPE_CHAR, $folPelId);
$dbValue[1] = QuoteValue(DPE_CHAR, $fol_id_baru);
$dbValue[2] = QuoteValue(DPE_CHAR, $userId);
$dbValue[3] = QuoteValue(DPE_CHAR, '2');
$dbValue[4] = QuoteValue(DPE_NUMERIC, $remun['biaya_remunerasi_nominal']);
$dbValue[5] = QuoteValue(DPE_CHAR, $folioSplit["folsplit_id"]);
$dbValue[6] = QuoteValue(DPE_CHAR, '2');

$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
// print_r($dbField);
// print_r($dbValue);
// print_r($dbKey);
//die();
$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

if ($_POST['isNewRecord'] == 'true') {
	$dtmodel->Insert() or die("insert  error");
	echo "sukses insert pelaksana => ";
} else {
	$dtmodel->Update() or die("insert  error");
	echo "sukses update pelaksana => ";
	//delete split dulu
	//$sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$_POST["id_fol"]);
	//$dtaccess->Execute($sql);
	//echo "sukses hapus fol split lama => " ;
}

unset($dtmodel);
unset($dbField);
unset($dbValue);
unset($dbKey);


exit();
