<?php    
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
  	 require_once($LIB."tampilan.php");	
     
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $enc = new textEncrypt();     
  	 $depId = $auth->GetDepId();
  	 $lokasi = $ROOT."gambar/foto_pasien";
     

	function count_digit($angka) {
		return strlen((string) $angka);
	}

	$sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs from global.global_departemen";
	$konf = $dtaccess->Fetch($sql);
	$norm_depan = $konf['dep_konf_reg_no_rm_depan'];
	
	# BPJS 
	$ID = $konf["dep_id_bpjs"];
	date_default_timezone_set('UTC');
  	$t=time();
  	$data = "$ID&$t";
    $secretKey = $konf["dep_secret_key_bpjs"];
   	$signature = base64_encode(hash_hmac('sha256', utf8_encode($data), utf8_encode($secretKey), true));
	$count = count_digit($_POST['param']);
	//echo $count;
   //	echo "cons_id <br>".$ID."<br> timestamp<br> ".$t."<br>signature<br>".$signature;

   	function xrequest($url, $signature, $ID, $t){
		$session = curl_init($url);
		$arrheader =  array(
			'X-cons-id: '.$ID,
			'X-timestamp: '.$t,
			'X-signature: '.$signature
			//'Content-Type: application/json'
			);
		curl_setopt($session, CURLOPT_HTTPHEADER, $arrheader);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);		
		$response = curl_exec($session);
		return $response;
	}
	
    # cari pasien
    if ($_POST["param"]) 
    {          
	//echo $_POST['param']; die();
		# cek kepesertaan bpjs 
		 //echo "cek bpjs sekarang";
		$uri = $konf["dep_alamat_ip_peserta"]; //"http://dvlp.bpjs-kesehatan.go.id:8081/Vclaim-rest";
		if ($count < 16 && $count <= 13 && $count > 8 ) { # NO JAMINAN
		  $completeurl = "$uri/Peserta/nokartu/".$_POST['param']."/tglSEP/".date("Y-m-d");
		}else{ //if($count <= 16 && $count > 13 && $count > 8 ) { # NIK
		  $completeurl = "$uri/Peserta/nik/".$_POST['param']."/tglSEP/".date("Y-m-d");
		}
		$response = xrequest($completeurl, $signature, $ID, $t);
		//echo $completeurl; 
		echo  $response; 	
    }
?>