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
	$sql = "select reg_jenis_pasien,a.id_dokter, a.id_poli, a.id_cust_usr, a.id_pembayaran,b.rawat_id,c.cust_usr_id,c.cust_usr_nama,cust_usr_umur,cust_usr_alamat, c.* 
			from klinik.klinik_registrasi a	
			left join klinik.klinik_perawatan b on a.reg_id = b.id_reg
			left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
			where a.reg_id = '$reg_id_lama'";
	$dataReg = $dtaccess->Fetch($sql);
	// echo $sql."<br>";
	
	//cari nama dan nominal biaya
	$sql = "select a.biaya_id,a.biaya_nama, b.biaya_total, b.biaya_tarif_id
			from klinik.klinik_biaya a
			left join klinik.klinik_biaya_tarif b on a.biaya_id = b.id_biaya	
				where b.biaya_tarif_id = '$_POST[id_biaya_tarif]'";
	$rs = $dtaccess->Execute($sql);
	$biaya = $dtaccess->Fetch($rs);
	// echo $sql."<br>";
	//die($sql);
	
	#cari data fol pel id lama
	$sql = "select fol_pelaksana_id from klinik.klinik_folio_pelaksana
			where id_fol = '$fol_id_lama'";
	$rs = $dtaccess->Execute($sql);
	$pel_lama = $dtaccess->FetchAll($rs);
	// echo $sql."<br>";
	
	//cari split biaya
	$sql = "select id_split, bea_split_nominal from	klinik.klinik_biaya_split 	
			where id_biaya_tarif = '$_POST[id_biaya_tarif]'";
	$rs = $dtaccess->Execute($sql);
	$biayaSplit = $dtaccess->FetchAll($rs);
	// echo $sql."<br>";


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
	  /* $dbField[17] = "fol_jenis";
	  $dbField[17] = "fol_waktu";
	  $dbField[17] = "fol_jenis_pasien";
	  $dbField[17] = "fol_total_harga";
	  $dbField[17] = "fol_dijamin"; */
	  //$dbField[17] = "fol_jenis_sem";
		$tanggal = date_db($_POST["tindakan_tanggal"]);
		$waktu = $_POST["tindakan_waktu"];
	  
	  $dbValue[0] = QuoteValue(DPE_CHAR,$fol_id);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$dpjp);
	  $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_poli"]); 
	  $dbValue[4] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]); 
	  $dbValue[5] = QuoteValue(DPE_CHAR,$biaya["biaya_id"]);
	  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
	  $dbValue[7] = QuoteValue(DPE_CHAR,'n');
	  $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
	  $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["fol_jumlah"]);
	  $dbValue[10] = QuoteValue(DPE_CHAR,$userName);
	  $dbValue[11] = QuoteValue(DPE_CHAR,$biaya['biaya_nama']);
	  $dbValue[12] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']);
	  $dbValue[13] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']*$_POST["fol_jumlah"]);
	  $dbValue[14] = QuoteValue(DPE_NUMERIC,$biaya['biaya_total']*$_POST["fol_jumlah"]);
	  $dbValue[15] = QuoteValue(DPE_CHAR,$dokter_ins);
	  $dbValue[16] = QuoteValue(DPE_CHAR,$pel1);
	  $dbValue[17] = QuoteValue(DPE_CHAR,$_POST["id_biaya_tarif"]);
	  $dbValue[18] = QuoteValue(DPE_DATE,$tanggal." ".$waktu);
	  $dbValue[19] = QuoteValue(DPE_DATE,$waktu);
	  $dbValue[20] = QuoteValue(DPE_DATE,$tanggal);
	 // $dbValue[17] = QuoteValue(DPE_CHAR,"LD");
	  
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
    
   #update klinik pembayaran
   $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where
   id_pembayaran = ".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]); 
   $rs = $dtaccess->Execute($sql);
   $dataFolio = $dtaccess->Fetch($rs);   
   
      $dbTable = "klinik.klinik_pembayaran";
  		$dbField[0] = "pembayaran_id";   // PK
  		$dbField[1] = "pembayaran_total";
  		
  		$dbValue[0] = QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
  		$dbValue[1] = QuoteValue(DPE_NUMERIC,$dataFolio["total"]);      			
      
  		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);
  
  			$dtmodel->Update() or die("update  error");	
  
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
	  $dbField[9] = "rawat_tindakan_flag"; 
	  //$dbField[9] = "rawat_tindakan_keterangan"; 
	  //$dbField[10] = "rawat_tindakan_keterangan_2"; 
	  //$dbField[10] = "rawat_tindakan_diskon"; 
  
	  
	  $dbValue[0] = QuoteValue(DPE_CHAR,$rt_id);
	  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
	  $dbValue[2] = QuoteValue(DPE_CHAR,$biaya["biaya_id"]);
	  $dbValue[3] = QuoteValue(DPE_CHAR,$biaya['biaya_total']*$_POST["fol_jumlah"]);
	  $dbValue[4] = QuoteValue(DPE_CHAR,$dpjp);
	  $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
	  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["rawat_id"]);
	  $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["fol_jumlah"]);
	  $dbValue[8] = QuoteValue(DPE_CHAR,"n");
	  $dbValue[9] = QuoteValue(DPE_CHAR,"J");
	  //$dbValue[9] = QuoteValue(DPE_CHAR,$_POST["no_kantong"]);
	  //$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rhesus"]);
	  //$dbValue[10] = QuoteValue(DPE_CHAR,"");
	  
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
		if($_POST['isNewRecord']=='true') {
			$dtmodel->Insert() or die("insert  error");	
			echo "sukses insert perawatan tndakan => " ;
		} else {
			$dtmodel->Update() or die("insert  error");	
			echo "sukses update perawatan tndakan => " ;
			#delete lab detail
			$sql = "delete from laboratorium.lab_pemeriksaan_detail where id_fol=".QuoteValue(DPE_CHAR,$fol_id);
			$dtaccess->Execute($sql);
			echo "sukses hapus lab_pemeriksaan_detail lama => " ;
		}
			
		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);	
    
    

   	# simpan di pelaksana
	//cari folio
	$sql = "select fol_nominal, fol_jumlah from klinik.klinik_folio
			where fol_id = ".QuoteValue(DPE_CHAR,$fol_id);
	$rs = $dtaccess->Execute($sql);
	$folio = $dtaccess->Fetch($rs);
	//print_r($folio);

	//cari split biaya
	$sql = "select id_split ,id_biaya, bea_split_persen,bea_split_nominal from klinik.klinik_biaya_split a	
			left join klinik.klinik_split b on a.id_split = b.split_id
			where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR, $_POST["id_biaya_tarif"]);
  //echo $sql;
	$rs = $dtaccess->Execute($sql);
	$biayaSplit = $dtaccess->FetchAll($rs);

    for ($i = 0; $i < count ($biayaSplit); $i++) 
    {
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
        $hasil = ($hasilSatuan)*$folio["fol_jumlah"];
        
			  $dbValue[0] = QuoteValue(DPE_CHAR,$folSplitId);
			  $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
			  $dbValue[2] = QuoteValue(DPE_CHAR,$biayaSplit[$i]["id_split"]);
			  $dbValue[3] = QuoteValue(DPE_NUMERIC,$hasil);
			  $dbValue[4] = QuoteValue(DPE_NUMERIC,$biayaSplit[$i]["bea"]*$hasilSatuan);
			  $dbValue[5] = QuoteValue(DPE_NUMERIC,$folio["fol_jumlah"]);
			  $dbValue[6] = QuoteValue(DPE_NUMERIC,$depId);
			  //$dbValue[7] = QuoteValue(DPE_CHAR,$folPelId);
			 	//print_r($dbValue); die();
			  $dbKey[1] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
			  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
				  $dtmodel->Insert() or die("insert  error");	
		 		  echo "sukses insert folio split =>".$x;
			  unset($dtmodel);
			  unset($dbField);
			  unset($dbValue);
			  unset($dbKey);

	  	}
			
	# sebelum insert cek lab pemeriksaan
	$sql = "select pemeriksaan_id from laboratorium.lab_pemeriksaan where id_reg = '$reg_id_lama'";
	$dataLab = $dtaccess->Fetch($sql);
	//print_r($dataLab);

	if (!$dataLab) { #jika tidak ada data maka insert lab pemeriksaan
	echo "tidak ada pemeriksaan id => "; 
	#simpan laboratorium pemeriksaan
	   $dbTable = "laboratorium.lab_pemeriksaan";
               
	   $dbField[0] = "pemeriksaan_id";   // PK
	   $dbField[1] = "id_reg";
	   $dbField[2] = "pemeriksaan_pasien_nama";
	   $dbField[3] = "id_dokter";
	   $dbField[4] = "pemeriksaan_create";
	   $dbField[5] = "pemeriksaan_umur";
	   $dbField[6] = "pemeriksaan_alamat";
	 //  $dbField[7] = "pemeriksaan_rawatinap";
	   $dbField[7] = "id_cust_usr";
	   $dbField[8] = "who_update";
	   $dbField[9] = "pemeriksaan_tgl";
	   
		$pemeriksaan_id = $dtaccess->GetTransID();
		
	   $dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaan_id);
	   $dbValue[1] = QuoteValue(DPE_CHAR,$reg_id);
	   $dbValue[2] = QuoteValue(DPE_CHAR,$dataReg["cust_usr_nama"]);
	   $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_dokter"]);
	   $dbValue[4] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
	   $dbValue[5] = QuoteValue(DPE_CHAR,$dataReg["cust_usr_umur"]);
	   $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg["cust_usr_alamat"]);
	 //  $dbValue[7] = QuoteValue(DPE_CHAR,$rawatinap);
	   $dbValue[7] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
	   $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
	   $dbValue[9] = QuoteValue(DPE_DATE,date("Y-m-d"));
		
	  
	   $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	   $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

	    if($_POST['isNewRecord']=='true') {
			$dtmodel->Insert() or die("insert  error");	
			echo "sukses insert lab_pemeriksaan => " ;
		}
	   
	   unset($dtmodel);
	   unset($dbField);
	   unset($dbValue);
	   unset($dbKey);
	
	} else {
		echo "pemeriksaan id ada => "; 
		$pemeriksaan_id = $dataLab["pemeriksaan_id"];
	}	

	#update dokter lab
	$dbTable = "laboratorium.lab_pemeriksaan";
  $dbField[0] = "pemeriksaan_id";   // PK
  $dbField[1] = "id_dokter_lab";

  $dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaan_id);
  $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["dokter_lab"]);

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  $dtmodel->Update() or die("update  error");    
  //print_r($dbValue); die();
  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

	
	$q = explode('-', $dataReg['cust_usr_tanggal_lahir']);
	$TahunLahir = $q[0];
	$TahunSekarang = date('Y');
	$Umur = $TahunSekarang - $TahunLahir;
	$sql = "select hasil_lab_id,hasil_lab_nama,hasil_lab_kode,hasil_lab_keterangan 
				from klinik.klinik_hasil_lab where id_biaya = ".QuoteValue(DPE_CHAR,$biaya["biaya_id"])." 
				and hasil_lab_jenis_kelamin = ".QuoteValue(DPE_CHAR, $dataReg['cust_usr_jenis_kelamin'])."
			  and ".$Umur." >= hasil_lab_batas_umur_awal and ".$Umur." <= hasil_lab_batas_umur_akhir";
	$dataAnak= $dtaccess->FetchAll($sql);
	//print_r($dataAnak);

	for($i=0,$n=count($dataAnak);$i<$n;$i++) { 
		$dbTable = "laboratorium.lab_pemeriksaan_detail";

		$dbField[0] = "periksa_det_id";   // PK
		$dbField[1] = "id_pemeriksaan";     
		$dbField[2] = "who_update"; 
		$dbField[3] = "id_cust_usr";
		$dbField[4] = "nama_pemeriksaan";
		$dbField[5] = "id_biaya";
		$dbField[6] = "when_create";
		$dbField[7] = "detail_kode"; 
		$dbField[8] = "id_fol"; 
		if($dataAnak[$i]["hasil_lab_keterangan"]){
			$dbField[9] = "pemeriksaan_nilai_normal";
		}   

		$pemeriksaandetAnakId = $dtaccess->GetTransID();   
		$dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaandetAnakId);
		$dbValue[1] = QuoteValue(DPE_CHAR,$pemeriksaan_id);
		$dbValue[2] = QuoteValue(DPE_CHAR,$userName);
		$dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
		$dbValue[4] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_nama"]);
		$dbValue[5] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_id"]);
		$dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
		$dbValue[7] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_kode"]);   
		$dbValue[8] = QuoteValue(DPE_CHAR,$fol_id);   
		if($dataAnak[$i]["hasil_lab_keterangan"]){
			$dbValue[9] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_keterangan"]); 
		} 
		$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
		$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

		$dtmodel->Insert() or die("insert  error");
		echo "sukses insert lab_pemeriksaan_detail ".$i." => ";

		unset($dtmodel);
		unset($dbField);
		unset($dbValue);
		unset($dbKey);

	}
		
	 exit();      
	
?>