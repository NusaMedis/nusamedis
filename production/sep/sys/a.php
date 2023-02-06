<?php 
  require_once("../penghubung.inc.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZString.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZReverseDictionary.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZData.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZString.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZUtil.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZUtil16.php");
  require_once($LIB."lz_string_php_master/src/LZCompressor/LZContext.php");
  /* WAJIB */
  $consID = "24785";// V2
  $secretKey = "9gO4BA9817"; //V2
  $userKey = "a44bbf0564f775ef8e5f070d65e292c7"; //V2
  // $consID = "19840";
  // $secretKey = "6eJBCC6014";
  // $nik = "3329090407020002"; // Erik
  // $nik = "3502090205000002"; // Bismo
  $nik = "3519022805000001"; // Erda
  // $nokartu = '0000111486069';
  $tgl = date('Y-m-d');
  /* WAJIB */

  /* Pembuatan Waktu BPJS */
  date_default_timezone_set('UTC');
  $tStamp = strval(time()-strtotime('1970-01-01 00:00:00'));
  /* Pembuatan Waktu BPJS */
  
  $signature = hash_hmac('sha256', $consID."&".$tStamp, $secretKey, true); // Pembuatan Tanda Tangan Untuk BPJS
 
  $encodedSignature = base64_encode($signature); // Hasil Tanda Tangan Untuk BPJS Setelah di Encode

  // $url = "https://new-api.bpjs-kesehatan.go.id:8080/new-vclaim-rest"; // Alamat API
  $url = "https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev"; // Alamat API V2 dev
  $url_detail = "$url/Peserta/nik/$nik/tglSEP/$tgl";

  /* FUN PENGIRIMAN BPJS */
  $arrheader =  array(
    'X-cons-id: '.$consID,
    'X-timestamp: '.$tStamp,
    'X-signature: '.$encodedSignature,
    'user_key: '.$userKey,
    'Content-Type: application/json; charset=utf-8',
  );
  
  $ch = curl_init($url_detail); // inisialisasi kirim url
  curl_setopt($ch, CURLOPT_HTTPHEADER, $arrheader);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);    

  $response = curl_exec($ch);

  $isi = json_decode($response, true);
  $key = $consID.$secretKey.$tStamp;
  $string = $isi['response'];

  $encrypt_method = 'AES-256-CBC';
  // hash
  $key_hash = hex2bin(hash('sha256', $key));
  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning        
  $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
  $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

  $result = \LZCompressor\LZString::decompressFromEncodedURIComponent($output);
  
  echo "<pre>";
  print_r ($tStamp);
  echo "</pre>";

  echo "<pre>";
  print_r ($encodedSignature);
  echo "</pre>";

  echo "<pre>";
  print_r (json_decode($result));
  echo "</pre>";
?>