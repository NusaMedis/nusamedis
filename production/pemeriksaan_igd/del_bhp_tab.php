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
    $sql="select id_cust_usr from klinik.klinik_registrasi where reg_id='$_POST[id_reg]'";
    $dataCust = $dtaccess->Fetch($sql);
    
    $sql="select * from klinik.klinik_registrasi where reg_status ='A7' and id_cust_usr=".QuoteValue(DPE_CHAR,$dataCust['id_cust_usr']);
    $dataBhpTab = $dtaccess->Fetch($sql);

    $sql= "select  a.* ,b.id_fol
      from apotik.apotik_penjualan_detail a
      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
      where  a.id_item='$_POST[item_id]' and b.id_reg =".QuoteValue(DPE_CHAR,$dataBhpTab['reg_id']);
		$data = $dtaccess->Fetch($sql);
		$id_penjualan = $data['id_penjualan'];
    
		$sql = "select id_gudang from global.global_auth_poli
		 where poli_id = '$dataBhpTab[id_poli_asal]'";
		$dataGudang = $dtaccess->Fetch($sql);
		$gudang = $dataGudang['id_gudang'];
	
		
		$sql = "select stok_item_saldo from logistik.logistik_stok_item where id_gudang 
		=".QuoteValue(DPE_CHAR,$gudang);
		$sql .="and id_item =".QuoteValue(DPE_CHAR,$_POST['item_id']);
		$sql .="order by stok_item_create desc"; 
		$dataDep= $dtaccess->Fetch($sql);
		

		$redoStok  =  intval($dataDep['stok_item_saldo']) + ($data['penjualan_detail_jumlah']);

		/* update stok dep */
		$sql  ="update logistik.logistik_stok_dep set stok_dep_saldo ='$redoStok'";
		$sql .=" , stok_dep_create = current_timestamp";
		$sql .=" , stok_dep_tgl = current_date";
		$sql .=" where id_item = '$_POST[item_id]'";
		$sql .=" and id_gudang =".QuoteValue(DPE_CHAR,$gudang);
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
		
		/* delete di stok dep item */
		$sql = "delete from logistik.logistik_stok_item
		where id_item  = '$_POST[item_id]' and id_penjualan =".QuoteValue(DPE_CHAR, $data['id_penjualan']);
    $result = $dtaccess->Execute($sql);

    /* delete apotik penj detail */

    $sql = "delete from apotik.apotik_penjualan_detail
		where id_item  = '$_POST[item_id]' and id_penjualan =".QuoteValue(DPE_CHAR, $data['id_penjualan']);
		$result1 = $dtaccess->Execute($sql);
		
   /* update fol nominal  */
   $sql= "select  sum(penjualan_detail_total) as total
      from apotik.apotik_penjualan_detail a
      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
      where  b.id_reg =".QuoteValue(DPE_CHAR,$dataBhpTab['reg_id']);
    $total = $dtaccess->Fetch($sql);
    
   $sql="update klinik.klinik_folio set fol_nominal =".QuoteValue(DPE_NUMERIC,$total['total']).",
   fol_dibayar =".QuoteValue(DPE_NUMERIC,$total['total']).",
   fol_total_harga=".QuoteValue(DPE_NUMERIC,$total['total']).",
   fol_hrs_bayar=".QuoteValue(DPE_NUMERIC,$total['total'])."
   
    where fol_id=".QuoteValue(DPE_CHAR, $data['id_fol']);
    $dtaccess->Execute($sql);



    $sqlpemb = "select id_pembayaran from klinik.klinik_registrasi
              where reg_id=".QuoteValue(DPE_CHARKEY,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $idPemb = $dtaccess->Fetch($sqlpemb);

    $sql  ="update klinik.klinik_registrasi set reg_obat='y'
            where id_pembayaran=".QuoteValue(DPE_CHARKEY,$regLama["id_pembayaran"])." and id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql); 

    /* UPDATE PEMBAYARAN TOTAL */
    $sql = "SELECT sum(fol_nominal) AS total FROM klinik.klinik_folio WHERE id_pembayaran = ".QuoteValue(DPE_CHAR, $idPemb["id_pembayaran"]);
    $dataTotal = $dtaccess->Fetch($sql);

    $sql = "UPDATE klinik.klinik_pembayaran SET pembayaran_total = ".QuoteValue(DPE_NUMERIC, $dataTotal['total'])." WHERE pembayaran_id = ".QuoteValue(DPE_CHAR, $idPemb["id_pembayaran"]);
    $dtaccess->Execute($sql);
	 

		       /* SQL PENGURUTAN */
					 $sql = "select * from logistik.logistik_stok_item where id_gudang = ".QuoteValue(DPE_CHAR,$gudang)." and id_item = ".QuoteValue(DPE_CHAR,$_POST['item_id'])." order by id_gudang asc, stok_item_create asc";
					 $dataAdjustment = $dtaccess->FetchAll($sql);
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
			 
					 $sql  ="update logistik.logistik_stok_dep set stok_dep_saldo =".QuoteValue(DPE_NUMERIC,$saldo)." where id_item = ".QuoteValue(DPE_CHAR,$data["item_id"])." and id_gudang =".QuoteValue(DPE_CHAR,$gudang);
					 $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
					 
					 
		/* CEK APAKAH PENJUALAN KOSONG */
			$sql= "select  a.* from apotik.apotik_penjualan_detail a
			left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
			where  b.id_reg =".QuoteValue(DPE_CHAR,$dataBhpTab['reg_id']);
			$cekPenjualan = $dtaccess->FetchAll($sql);
			
			if(!$cekPenjualan){
				/* Hapus penjual det*/
					$sql="delete  from apotik.apotik_penjualan_detail where id_penjualan =".QuoteValue(DPE_CHAR,$id_penjualan);
					$dtaccess->Execute($sql);
				/* Hapus penjual */
					$sql="delete  from apotik.apotik_penjualan where penjualan_id =".QuoteValue(DPE_CHAR,$id_penjualan);
					$dtaccess->Execute($sql);
				/* Hapus folio penjualan obat*/
					$sql="delete  from klinik.klinik_folio  where id_reg =".QuoteValue(DPE_CHAR,$dataBhpTab['reg_id']);
					$dtaccess->Execute($sql);
				/* Hapus reg apotik*/
					$sql="delete  from klinik.klinik_registrasi where reg_id =".QuoteValue(DPE_CHAR,$dataBhpTab['reg_id']);
					$dtaccess->Execute($sql);
			}
		

	  if ($result){
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('errorMsg'=>'Some errors occured.'));
		} 
	 
	 exit();      

?>