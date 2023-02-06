<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$usernik = $_GET["usernik"]; 
$startdt = $_GET["tgl_awal"];
$stopdt = $_GET["tgl_akhir"];
$jenisrawat = $_GET["tiperawat"];

// json query  finalisasi klaim
if($_GET["collect"]){
$request = <<<EOT
{
  "metadata": {
     "method" : "send_claim"
  },
  "data": {
     "start_dt": "$startdt",
     "start_dt": "$stopdt",
     "jenis_rawat": "$jenisrawat"     
  }
}
EOT;
}else{
$request = <<<EOT
{
  "metadata": {
     "method" : "send_claim_individual"
  },
  "data": {
     "nomor_sep": "$sep"
  }
}
EOT;
}
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

  $metadata = $msg["metadata"];
  $code = $metadata["code"];
  $message = $metadata["message"];
  $dataresponse = $msg["response"];
  $responsedata = $dataresponse["data"];
  
 // if($_GET["collect"]){
  
//  }else{
  $sepsend = $responsedata["SEP"];
  $tglpulangsend = $responsedata["tgl_pulang"];
  $kemkesdcstatussend = $responsedata["kemkes_dc_Status"];
  $bpjsdcstatussend = $responsedata["bpjs_dc_Status"];
 // }
  if($code=='200'){
  //print_r($dataresponse);
  echo "<script>alert('Data Klaim SEP : ".$sepsend." Tanggal Pulang : ".$tglpulangsend." Status Kemkes : ".$kemkesdcstatussend." Status BPJS : ".$bpjsdcstatussend."');</script>";
  }
  
  if($code=='400'){
  echo "<script>alert('$message');</script>";
  }
  
  echo "<script>window.close()</script>";  
//die();
?>