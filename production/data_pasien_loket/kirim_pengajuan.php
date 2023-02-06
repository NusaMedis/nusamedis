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
  	 
	$sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs,dep_kode_rs from global.global_departemen";
	$konf = $dtaccess->Fetch($sql);

	# BPJS 
	$ID = $konf["dep_id_bpjs"];
	date_default_timezone_set('UTC');
  	$t=time();
  	$data = "$ID&$t";
    $secretKey = $konf["dep_secret_key_bpjs"];
   	$signature = base64_encode(hash_hmac('sha256', utf8_encode($data), utf8_encode($secretKey), true));

    $noKartu = $_POST['noKartu'];
    $tglSep = date_db($_POST['noKartu']);
    $jnsPelayanan = $_POST['jnsPelayanan'];
    $keterangan = $_POST['keterangan'];

    $datastring ='{
           "request": {
              "t_sep": {
                 "noKartu": "'.$noKartu.'",
                 "tglSep": "'.$tglSep.'",
                 "jnsPelayanan": "'.$jnsPelayanan.'",
                 "keterangan": "'.$keterangan.'",
                 "user": "'.$userName.'"
              }
           }
        }';
echo $data_string; die();
   
		# cek kepesertaan bpjs 
		 //echo "cek bpjs sekarang";
		$uri = $konf["dep_alamat_ip_peserta"]; //"http://dvlp.bpjs-kesehatan.go.id:8081/Vclaim-rest"
		$completeurl = "$uri/SEP/insert";

		
		$session = curl_init($completeurl);
		$arrheader =  array(
			'X-cons-id: '.$ID,
			'X-timestamp: '.$t,
			'X-signature: '.$signature,
			'Content-Type: Application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($datastring)
			);
		curl_setopt($session, CURLOPT_HTTPHEADER, $arrheader);
		curl_setopt($session, CURLOPT_POSTFIELDS, $datastring);
		curl_setopt($session, CURLOPT_POST, TRUE);
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, "POST"); 
		curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);		
		
		$response = curl_exec($session);
		echo $response;		

?>  	 