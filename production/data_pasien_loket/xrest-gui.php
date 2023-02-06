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
	$norm_depan = $konf['dep_konf_reg_no_rm_depan'];

//cari poli bpjsnya
	$sql = "select poli_bpjs from global.global_auth_poli where poli_id = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
	$poli = $dtaccess->Fetch($sql);

	# BPJS 
	$ID = $konf["dep_id_bpjs"];
	date_default_timezone_set('UTC');
  	$t=time();
  	$data = "$ID&$t";
    $secretKey = $konf["dep_secret_key_bpjs"];
   	$signature = base64_encode(hash_hmac('sha256', utf8_encode($data), utf8_encode($secretKey), true));

echo "X-cons-id: ".$ID."<br>
			X-timestamp: ".$t."<br>
			X-signature: ".$signature."<br>
			Content-Type: Application/x-www-form-urlencoded";
?>  	 