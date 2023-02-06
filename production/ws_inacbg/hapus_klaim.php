<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$usernik = $_GET["usernik"];
// json query  finalisasi klaim
$requestunfinal = <<<EOT
{
  "metadata": {
     "method" : "reedit_claim"
  },
  "data": {
     "nomor_sep": "$sep"
  }
}
EOT;
// json query  hapus klaim
$request = <<<EOT
{
  "metadata": {
     "method" : "delete_claim"
  },
  "data": {
     "nomor_sep": "$sep",
     "coder_nik": "$usernik"
  }
}
EOT;
//print_r($request);
//die();
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadunfinal = mc_encrypt($requestunfinal,$key);
$payloaddelete = mc_encrypt($request,$key);
//$payload = $request;
####### diunfinal dulu klo sudah final baru delete
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");

$ch1 = curl_init($url);
curl_setopt($ch1, CURLOPT_URL, $url);
curl_setopt($ch1, CURLOPT_HEADER, 0);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch1, CURLOPT_HTTPHEADER,$header);
curl_setopt($ch1, CURLOPT_POST, 1);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $payloadunfinal);
//request dengan curl
$responseunfinal = curl_exec($ch1);
####akhir unfinal
##awal delete
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payloaddelete);
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
//die();
  $metadata = $msg["metadata"];
  $code = $metadata["code"];
  $message = $metadata["message"];
  $dataresponse = $msg["response"];
  $kodeStatusSep = $dataresponse["kdStatusSep"];
  $namaStatusSep = $dataresponse["nmStatusSep"];
 // if($_GET["collect"]){
  
 // }
  if($code=='200'){
  //print_r($dataresponse);
  echo "<script>alert('Data Klaim SEP : ".$sep." BERHASIL DIHAPUS');</script>";
  }else{
  echo "<script>alert('$message');</script>";
  }
  
  echo "<script>window.close()</script>";  
//die();
?>