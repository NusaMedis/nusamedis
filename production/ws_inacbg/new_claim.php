<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$kartu = $_GET["kartu"];
$nama = $_GET["nama"];
$koderm = $_GET["koderm"];
$tgllahir = $_GET["tgllahir"];
$gender = $_GET["gender"];    // 1 untuk laki-laki dan 2 untuk perempuan
// json query      new claim
$request = <<<EOT
{
  "metadata": {
     "method" : "new_claim"
  },
  "data": {
     "nomor_kartu": "$kartu",
     "nomor_sep": "$sep",
     "nomor_rm": "$koderm",
     "nama_pasien": "$nama",
     "tgl_lahir": "$tgllahir",
     "gender": "$gender"
  }
}
EOT;
//print_r($request);
//die();
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payload = mc_encrypt($request,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//request dengan curl
$response = curl_exec($ch);



//echo $request; 
// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$first  = strpos($response, "\n")+1;
$last   = strrpos($response, "\n")-1;
$response  = substr($response, $first, strlen($response) - $first - $last);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$response = mc_decrypt($response,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msg = json_decode($response,true); 
//print_r($msg);
 /*
$link = "host=192.168.133.5 dbname=rso user=its password=itsthok";
$connect = pg_connect($link);

$query4 = "select inacbg_id from klinik.klinik_inacbg where inacbg_nos_sep = '".$sep,"'";
$result4 = pg_query($connect, $query4);
$inacbgcheck = pg_fetch_assoc($result4);

if(!$inacbgcheck) {
$inacbgID = GetTransID();
$query = " INSERT INTO klinik.klinik_inacbg(inacbg_id, id_reg, inacbg_kode, inacbg_no_sep, inacbg_dijamin)
    VALUES ('".$inacbgID."','".$regId."', '".$cbgcoderesponse."','".$sep."', '".$cbgtarifresponse."' )";
$result = pg_query($connect, $query);
 }else{
$query = " update klinik.klinik_inacbg set  where inacbg_nos_sep='".$sep."'";
$result = pg_query($connect, $query);
 
 } */
  $metadata = $msg["metadata"];
  $code = $metadata["code"];
  $message = $metadata["message"];
    
  if($code=='200'){
  echo "<script>alert('Data Klaim berhasil dimasukkan!!');</script>";
  }
  
  if($code=='400'){
  echo "<script>alert('$message');</script>";
  }
  echo "<script>window.close()</script>";  
//die();
?>