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
	date_default_timezone_set('UTC+7');
  	$t=time();
  	$data = "$ID&$t";
    $secretKey = $konf["dep_secret_key_bpjs"];
   	$signature = base64_encode(hash_hmac('sha256', utf8_encode($data), utf8_encode($secretKey), true));

//data untuk create sep

if($_POST["reg_jenis_layanan"]=='rj'){
	$jnsPelayanan="2";
}else{
$jnsPelayanan="1";							//1. r.inap 2. r.jalan
}
if($_POST["reg_sebab_sakit"]=='4'){$_POST["reg_lakalantas"] ='1';}else{ $_POST["reg_lakalantas"] ='0';} // sebab sakit id 4 adalah kecelakaan lantas
if(!$_POST["cob"]){$_POST["cob"] ='0';}else{ $_POST["cob"] ='1';}
if(!$_POST["asal_faskes_rujuk"]){$_POST["asal_faskes_rujuk"] ='1';}else{ $_POST["asal_faskes_rujuk"] ='2';}
$noKartu = $_POST["cust_usr_no_jaminan"];
$tglSep = date('Y-m-d');
//$ppkPelayanan = $konf["dep_kode_rs"];
$ppkPelayanan = "0905R002";
$klsRawat = $_POST["hak_kelas_inap"];		//1. kelas 1, 2. kelas 2 3.kelas 3
$noMR = $_POST["cust_usr_kode"];
$asalRujukan = $_POST["asal_faskes_rujuk"]; //1.Faskes 1, 2. Faskes 2(RS)
$tglRujukan = date_db($_POST["reg_tgl_rujukan"]);
$noRujukan = str_pad($_POST["reg_no_rujukan"],19,"0",STR_PAD_LEFT);;              // no surat rujukan
$ppkRujukan = $_POST["reg_ppk_rujukan"];
$catatan = $_POST["catatan_bpjs"];
$diagAwal = str_replace(' ','',$_POST["reg_diagnosa_awal"]);
$tujuan = $poli["poli_bpjs"];			// ambil dari kode poli bpjs
$lakaLantas = $_POST["reg_lakalantas"];		// kecelakaan atau tidak  0=tidak 1= ya
$cob = $_POST["cob"];						// 0=tidak 1=ya
$penjamin = $_POST["penjamin_lantas"];		// 1=Jasa raharja PT, 2=BPJS Ketenagakerjaan, 3=TASPEN PT, 4=ASABRI PT} jika lebih dari 1 isi -> 1,2 (pakai delimiter koma)
$lokasiLaka = $_POST["reg_lokasi_lantas"]; //tempat lakalantas
$noTelp = $_POST["cust_usr_no_hp"];
$user = "";
$datastring ='{
           "request": {
              "t_sep": {
                 "noKartu": "'.$noKartu.'",
                 "tglSep": "'.$tglSep.'",
                 "ppkPelayanan": "'.$ppkPelayanan.'",
                 "jnsPelayanan": "'.$jnsPelayanan.'",
                 "klsRawat": "'.$klsRawat.'",
                 "noMR": "'.$noMR.'",
                 "rujukan": {
                    "asalRujukan": "'.$asalRujukan.'",
                    "tglRujukan": "'.$tglRujukan.'",
                    "noRujukan": "'.$noRujukan.'",
                    "ppkRujukan": "'.$ppkRujukan.'"
                 },
                 "catatan": "'.$catatan.'",
                 "diagAwal": "'.$diagAwal.'",
                 "poli": {
                    "tujuan": "'.$tujuan.'",
                    "eksekutif": "0"
                 },
                 "cob": {
                    "cob": "'.$cob.'"
                 },
                 "jaminan": {
                    "lakaLantas": "'.$lakaLantas.'",
                    "penjamin": "'.$penjamin.'",
                    "lokasiLaka": "'.$lokasiLaka.'"
                 },
                 "noTelp": "'.$noTelp.'",
                 "user": "'.$userName.'"
              }
           }
        }';
//echo $data_string; die();
   
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