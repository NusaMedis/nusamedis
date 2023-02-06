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
	 
	#menghitung umur 
	$sql = "select cust_usr_tanggal_lahir from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
	$rs = $dtaccess->Execute($sql);
	$pasien = $dtaccess->Fetch($rs);
	
	# Tanggal Lahir
	$birthday = $pasien['cust_usr_tanggal_lahir'];	
	$biday = new DateTime( $birthday );  // Convert Ke Date Time
	$today = new DateTime();
	$diff = $today->diff($biday);	
	
	# cek poli pertama
	$sql = "select id_poli from klinik.klinik_registrasi where id_poli = id_poli_asal and reg_id = ".QuoteValue(DPE_CHAR,$_POST['regId']);
	$rs = $dtaccess->Execute($sql);
	$poliPertama = $dtaccess->Fetch($rs);
	
	#poli peratama
	if($poliPertama) {
		$sql2 = " update klinik.klinik_registrasi set id_poli = ".QuoteValue(DPE_CHAR,$_POST["klinik_asal"])." where id_poli_asal = ".QuoteValue(DPE_CHAR,$poliPertama['id_poli']);
		$sql2 .= "and reg_utama = ".QuoteValue(DPE_CHAR,$_POST['regId']);
	$rs = $dtaccess->Execute($sql);
	}

		$id_poli=$_POST["klinik_asal"];
	   //   $sql = "SELECT * FROM global.global_auth_poli WHERE poli_id ='$id_poli'";
	   // $row = pg_fetch_array(pg_query($con,$sql));
	   $id_dokter=$_POST['dokterr'];
	   $reg_buffer_tanggal=date("Y-m-d");

	 
	

	// SELECT max(reg_no_antrian) as maxkode FROM klinik.klinik_registrasi where reg_status!='' and reg_tanggal='2020-09-28'AND id_poli ='c96a0c5914b37954352542aae75e4709' and id_dokter ='95d2e62db7461708e6e44c0ee958485e'
	$query = "SELECT max(reg_no_antrian) as maxkode FROM klinik.klinik_registrasi WHERE reg_tanggal='$reg_buffer_tanggal'AND id_poli='$id_poli' AND id_dokter='$id_dokter'";
	$hasil = pg_query($query);
	$data  = pg_fetch_array($hasil);
	$kodeAntrian = $data['maxkode'];

	// mengambil angka atau bilangan dalam kode anggota terbesar,
	// dengan cara mengambil substring mulai dari karakter ke-1 diambil 6 karakter
	// misal 'BRG001', akan diambil '001'
	// setelah substring bilangan diambil lantas dicasting menjadi integer
	$noUrut = (int) substr($kodeAntrian, 3, 3);

	// bilangan yang diambil ini ditambah 1 untuk menentukan nomor urut berikutnya
	$noUrut++;



	// membentuk kode anggota baru
	// perintah sprintf("%03s", $noUrut); digunakan untuk memformat string sebanyak 3 karakter
	// misal sprintf("%03s", 12); maka akan dihasilkan '012'
	// atau misal sprintf("%03s", 1); maka akan dihasilkan string '001'

	if ($id_poli=="c96a0c5914b37954352542aae75e4709") {
	  # code...
	  $char="A";
	  

	}
	elseif ($id_poli=="c2b63ccfdc414dcd2c2d9a1c5f69db9a") {
	  # code...
	  $char="B";
	 
	}
	elseif ($id_poli="92704e222196ff9e9342db6755e1a6f4") {
	  # code...
	  $char="C";
	 
	}
	elseif ($id_poli="daf02f4a989a1a7d69439c8e5a23a661") {
	  # code...
	  $char="D";

	}

	$kd=substr($id_dokter,0,2);

	$noAntrian = $char .$kd. sprintf("%03s", $noUrut);

	$sql="select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST['regId'])." and id_poli = ".QuoteValue(DPE_CHAR,$id_poli)." and id_dokter = ".QuoteValue(DPE_CHAR,$_POST['dokterr']);
	
	$rs = $dtaccess->Execute($sql);
	$cekPoli = $dtaccess->Fetch($rs);
	echo $cekPoli['id_dokter']." ".$id_dokter;

	if ($cekPoli['id_dokter']=$id_dokter) {
		# code...

		// ---- insert ke registrasi ----
    $dbTable = "klinik.klinik_registrasi";
 
    $dbField[0] = "reg_id";   // PK
    $dbField[1] = "reg_tanggal";
    $dbField[2] = "reg_who_update";
    
    $dbField[3] = "reg_jenis_pasien";
    $dbField[4] = "reg_rujukan_id";         
    $dbField[5] = "reg_tipe_rawat";
    $dbField[6] = "id_poli";
    $dbField[7] = "id_dep";
	$dbField[8] = "id_dokter";
    $dbField[9] = "reg_sebab_sakit";
	$dbField[10] = "reg_rujukan_det";
    $dbField[11] = "reg_diagnosa_awal";
	$dbField[12] = "reg_prosedur_masuk";
    $dbField[13] = "id_perusahaan";
    $dbField[14] = "reg_tipe_jkn";
  
	$regId = $_POST['regId'];	
			
    $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
    $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
    $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
    
    $dbValue[3] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_id"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["instalasi"]);
    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik_asal"]);
    $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
	$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dokterr"]);
    $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["reg_sebab_sakit"]);
	$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_det"]);
    $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["reg_diagnosa_awal"]);
	$dbValue[12] = QuoteValue(DPE_CHAR,$_POST["reg_prosedur_masuk"]);
	($_POST["perusahaan"]) ? $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["perusahaan"]) : $dbValue[13] = QuoteValue(DPE_CHAR, null) ;
	($_POST["tipe_jkn"]) ? $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]) : $dbValue[14] = QuoteValue(DPE_CHAR, null) ;
				//print_r($dbValue);die();
	
	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    $dtmodel->Update() or die("insert  error");		
	
	 # sekalian update umur di global.global_customer_user
	 $umur =  $diff->y ."~".$diff->m ."~". $diff->d ;
	 $sql = "update global.global_customer_user";
	 $sql .= " set cust_usr_umur = ".QuoteValue(DPE_CHAR, $umur);
	 $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
	 $rs = $dtaccess->Execute($sql);
	 echo "sukses update umur di global_customer_user =>";
		 
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);

	}
	else{

		// ---- insert ke registrasi ----
    $dbTable = "klinik.klinik_registrasi";
 
    $dbField[0] = "reg_id";   // PK
    $dbField[1] = "reg_tanggal";
    $dbField[2] = "reg_who_update";
    
    $dbField[3] = "reg_jenis_pasien";
    $dbField[4] = "reg_rujukan_id";         
    $dbField[5] = "reg_tipe_rawat";
    $dbField[6] = "id_poli";
    $dbField[7] = "id_dep";
	$dbField[8] = "id_dokter";
    $dbField[9] = "reg_sebab_sakit";
	$dbField[10] = "reg_rujukan_det";
    $dbField[11] = "reg_diagnosa_awal";
	$dbField[12] = "reg_prosedur_masuk";
    $dbField[13] = "id_perusahaan";
    $dbField[14] = "reg_tipe_jkn";
    $dbField[15] = "reg_no_antrian";
		
	$regId = $_POST['regId'];	
			
    $dbValue[0] = QuoteValue(DPE_CHAR,$regId);
    $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
    $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
    
    $dbValue[3] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
    $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_id"]);
    $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["instalasi"]);
    $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik_asal"]);
    $dbValue[7] = QuoteValue(DPE_CHAR,$depId);
	$dbValue[8] = QuoteValue(DPE_CHAR,$_POST["dokterr"]);
    $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["reg_sebab_sakit"]);
	$dbValue[10] = QuoteValue(DPE_CHAR,$_POST["reg_rujukan_det"]);
    $dbValue[11] = QuoteValue(DPE_CHAR,$_POST["reg_diagnosa_awal"]);
	$dbValue[12] = QuoteValue(DPE_CHAR,$_POST["reg_prosedur_masuk"]);
	($_POST["perusahaan"]) ? $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["perusahaan"]) : $dbValue[13] = QuoteValue(DPE_CHAR, null) ;
	($_POST["tipe_jkn"]) ? $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["tipe_jkn"]) : $dbValue[14] = QuoteValue(DPE_CHAR, null) ;
				//print_r($dbValue);die();
	$dbValue[15] = QuoteValue(DPE_CHAR,$noAntrian); 
	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
    $dtmodel->Update() or die("insert  error");		
	
	 # sekalian update umur di global.global_customer_user
	 $umur =  $diff->y ."~".$diff->m ."~". $diff->d ;
	 $sql = "update global.global_customer_user";
	 $sql .= " set cust_usr_umur = ".QuoteValue(DPE_CHAR, $umur);
	 $sql .= " where cust_usr_id = ".QuoteValue(DPE_CHAR, $custUsrId);
	 $rs = $dtaccess->Execute($sql);
	 echo "sukses update umur di global_customer_user =>";
		 
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);


	}

	echo $_POST["klinik_asal"];


	
	
    
		 
	header("location: ".$_SERVER['HTTP_REFERER']);
?>
