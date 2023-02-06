<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$usernik = $_GET["usernik"]; 
// json query  finalisasi klaim
$request = <<<EOT
{
  "metadata": {
     "method" : "claim_print"
  },
  "data": {
     "nomor_sep": "$sep"
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

$pdf = base64_decode($msg["data"]);
//echo $pdf;
// hasilnya adalah berupa binary string $pdf, untuk disimpan:
file_put_contents('klaim.pdf', $pdf);
// atau untuk ditampilkan dengan perintah:
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename=klaim.pdf');
echo $pdf;
?>