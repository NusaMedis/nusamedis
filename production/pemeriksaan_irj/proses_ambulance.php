<?php
	// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
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
		
	$reg_id_baru = $dtaccess->GetTransID(); 
	$fol_id_baru = $dtaccess->GetTransID();
	$rt_id_baru = $dtaccess->GetTransID();
	$reg_id_lama = $_POST["id_reg"];
	$fol_id_lama = $_POST["fol_id"];
	$rt_id_lama = $_POST["rawat_tindakan_id"];
	if ($_POST['isNewRecord']){
		$fol_id = $fol_id_baru;
		$reg_id = $reg_id_lama;
		$rt_id = $rt_id_baru;
	} else {
		$fol_id = $fol_id_lama;
		$reg_id = $reg_id_lama;
		$rt_id = $rt_id_lama;
	}

	
	//cari data registrasi
	$sql = "select a.id_dokter, a.id_poli, a.id_cust_usr, a.id_pembayaran,b.rawat_id from klinik.klinik_registrasi a	
			left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
			where a.reg_id = '$reg_id_lama'";
	$dataReg = $dtaccess->Fetch($sql);
	
	//cari nama dan nominal biaya
	$sql = "select a.biaya_id,a.biaya_nama, b.biaya_total, b.biaya_tarif_id
			from klinik.klinik_biaya a
			left join klinik.klinik_biaya_tarif b on a.biaya_id = b.id_biaya	
				where b.biaya_tarif_id = '$_POST[biaya_tarif_id]'";
	$rs = $dtaccess->Execute($sql);
	$biaya = $dtaccess->FetchAll($rs);
	//die($sql);
	
	#cari data fol pel id lama
	$sql = "select fol_pelaksana_id from klinik.klinik_folio_pelaksana
			where id_fol = '$fol_id_lama'";
	$rs = $dtaccess->Execute($sql);
	$pel_lama = $dtaccess->FetchAll($rs);
	
	//cari split biaya
	$sql = "select id_split, bea_split_nominal from	klinik.klinik_biaya_split 	
			where id_biaya_tarif = '$_POST[biaya_tarif_id]'";
	$rs = $dtaccess->Execute($sql);
	$biayaSplit = $dtaccess->FetchAll($rs); 

	//data pelaksana dan dokter
	$dpjp = $dataReg['id_dokter']; //1
	$pel1 = $_POST['pelaksana']; //2
	$dokter_ins = $_POST['dokter_instruksi']; //7
	
	
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
	  $dbField[18] = "tindakan_tanggal";
	  $dbField[19] = "tindakan_waktu";
	  $dbField[20] = "fol_jenis_sem";
	  /*$dbField[17] = "fol_jenis_pasien";
	  $dbField[17] = "fol_total_harga";
	  $dbField[17] = "fol_dijamin"; */
	  
	  $dbValue[0] = QuoteValue(DPE_CHAR,$fol_id);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$dpjp);
	  $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_poli"]); 
	  $dbValue[4] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]); 
	  $dbValue[5] = QuoteValue(DPE_CHAR,$biaya[0]["biaya_id"]);
	  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
	  $dbValue[7] = QuoteValue(DPE_CHAR,'n');
	  $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
	  $dbValue[9] = QuoteValue(DPE_CHAR, '1');
	  $dbValue[10] = QuoteValue(DPE_CHAR,$userId);
	  $dbValue[11] = QuoteValue(DPE_CHAR,$biaya[0]['biaya_nama']);
	  $dbValue[12] = QuoteValue(DPE_NUMERIC,$biaya[0]['biaya_total']);
	  $dbValue[13] = QuoteValue(DPE_NUMERIC,$biaya[0]['biaya_total']);
	  $dbValue[14] = QuoteValue(DPE_NUMERIC,$biaya[0]['biaya_total']*$_POST["fol_jumlah"]);
	  $dbValue[15] = QuoteValue(DPE_CHAR,$dokter_ins);
	  $dbValue[16] = QuoteValue(DPE_CHAR,$pel1);
	  $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["biaya_tarif_id"]);
	  $dbValue[18] = QuoteValue(DPE_DATE,date_db(date("Y-m-d")));
	  $dbValue[19] = QuoteValue(DPE_CHAR,date("H:i:s"));
	  $dbValue[20] = QuoteValue(DPE_CHAR,"AM");
	  
		$dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		
		if($_POST['isNewRecord']=='true') {
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
	  $dbField[9] = "rawat_tindakan_keterangan"; 
	  $dbField[10] = "rawat_tindakan_flag"; 
	  //$dbField[10] = "rawat_tindakan_keterangan_2"; 
	  //$dbField[10] = "rawat_tindakan_diskon"; 
  
	  
	  $dbValue[0] = QuoteValue(DPE_CHAR,$rt_id);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$biaya[0]["biaya_id"]);
	  $dbValue[3] = QuoteValue(DPE_CHAR,$biaya[0]['biaya_total']*$_POST["fol_jumlah"]);
	  $dbValue[4] = QuoteValue(DPE_CHAR,$dpjp);
	  $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
	  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["rawat_id"]);
	  $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["fol_jumlah"]);
	  $dbValue[8] = QuoteValue(DPE_CHAR,"n");
	  $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["no_plat"]);
	  $dbValue[10] = QuoteValue(DPE_CHAR,"I");
	  //$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rhesus"]);
	  //$dbValue[10] = QuoteValue(DPE_CHAR,"");
//	  print_r($dbValue); die();
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		if($_POST['isNewRecord']=='true') {
			$dtmodel->Insert() or die("insert  error");	
			echo "sukses insert perawatan tndakan => " ;
		} else {
			$dtmodel->Update() or die("insert  error");	
			echo "sukses update perawatan tndakan => " ;
			
			/*$sql = "delete from klinik.klinik_folio_split where id_fol=".QuoteValue(DPE_CHAR,$fol_id);
			$dtaccess->Execute($sql);
			echo "sukses hapus fol split lama => " ;*/
		}
			
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);	
	  
			
		/* # simpan di pelaksana
		for ( $n = 0 ; $n < 1 ; $n++ ) { #ulang 3 x dengan fol tipe berbeda
		  		
		  $dbTable = "klinik.klinik_folio_pelaksana";
		  $dbField[0] = "fol_pelaksana_id";   // PK
		  $dbField[1] = "id_fol";
		  $dbField[2] = "id_usr";
		  $dbField[3] = "fol_pelaksana_tipe";
		  
		  $folPelId = $dtaccess->GetTransID();   
		  #urutan ke-kanan harus sama karena array
		  $a = array(PELAKSANA_TIPE_PELAKSANA); #data tipe pelaksana
		  $b = array($pel1); #data pelaksana

		  $dbValue[0] = QuoteValue(DPE_CHAR,$folPelId);
		  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
		  $dbValue[2] = QuoteValue(DPE_CHAR,$b[$n]);
		  $dbValue[3] = QuoteValue(DPE_CHAR,$a[$n]);
		  
			$dbKey[1] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
			$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		
			if($_POST['isNewRecord']=='true') {
				$dtmodel->Insert() or die("insert  error");	
				echo "sukses insert pelaksana ".$a[$n]." => " ;
			} else {
				# do update 
				$sql = "update klinik.klinik_folio_pelaksana SET";
				$sql .=" id_usr =".QuoteValue(DPE_CHAR,$b[$n]);
				$sql .=" ,fol_pelaksana_tipe =".QuoteValue(DPE_CHAR,$a[$n]);
				$sql .=" where  fol_pelaksana_id =".QuoteValue(DPE_CHAR,$pel_lama[$n]["fol_pelaksana_id"]);
				$up = $dtaccess->Execute($sql);
				echo "sukses update pelaksana ".$a[$n]." => " ;
			}
			
		  unset($dtmodel);
		  unset($dbField);
		  unset($dbValue);
		  unset($dbKey);	

			#simpan split folio
			for ($x = 0; $x < count ($biayaSplit); $x++) {
			  $dbTable = "klinik.klinik_folio_split";
			  $dbField[0] = "folsplit_id";   // PK
			  $dbField[1] = "id_fol";
			  $dbField[2] = "id_split";
			  $dbField[3] = "folsplit_nominal";
			  $dbField[4] = "folsplit_nominal_satuan";
			  $dbField[5] = "folsplit_jumlah";
			  $dbField[6] = "id_dep";
			  $dbField[7] = "id_fol_pelaksana";
			  
			  $folSplitId = $dtaccess->GetTransID();  
			  
			  $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
			  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
			  $dbValue[2] = QuoteValue(DPE_CHAR,$biayaSplit[$x]['id_split']);
			  $dbValue[3] = QuoteValue(DPE_NUMERIC,$biayaSplit[$x]['bea_split_nominal']*$_POST["fol_jumlah"]);
			  $dbValue[4] = QuoteValue(DPE_NUMERIC,$biayaSplit[$x]['bea_split_nominal']);
			  $dbValue[5] = QuoteValue(DPE_NUMERIC,$_POST["fol_jumlah"]);
			  $dbValue[6] = QuoteValue(DPE_NUMERIC,$depId);
			  $dbValue[7] = QuoteValue(DPE_CHAR,$a[$n]);
			  
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
				$dtmodel->Insert() or die("insert  error");	
				echo "sukses insert folio split ".$a[$n]." - ".$x;
				
			  unset($dtmodel);
			  unset($dbField);
			  unset($dbValue);
			  unset($dbKey);	
			}	
		} */
	 exit();      
	
?>