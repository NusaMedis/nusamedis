<?php 
    require_once("encrypt.php");
    require_once("key.php");        


$sep = $_GET["sep"];
$stage = $_GET["stage"]; 
$regId = $_GET["id_reg"];

    function GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

// json query      new claim
$request = <<<EOT
{
  "metadata": {
     "method" : "grouper",
     "stage": "1"
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

  $metadata = $msg["metadata"];
  $code = $metadata["code"];
  $message = $metadata["message"];
  $dataresponse = $msg["response"];
  $cbgresponse = $dataresponse["cbg"];
  $cbgcoderesponse = $cbgresponse["code"];
  $cbgdescresponse = $cbgresponse["description"];
  $cbgtarifresponse = $cbgresponse["tariff"];
  $specmgopt = $msg["special_cmg_option"];
  $specmgopt0 = $specmgopt["0"];
  $specmgopt0code = $specmgopt0["code"];
  $specmgopt1 = $specmgopt["1"];
  $specmgopt1code = $specmgopt1["code"];
  $specmgopt2 = $specmgopt["2"];
  $specmgopt2code = $specmgopt2["code"];
  $specmgopt3 = $specmgopt["3"];
  $specmgopt3code = $specmgopt3["code"];

//echo "masuk2";  
//  $grouper2link = "grouper2.php?sep=".$sep."&spcmg1=".$specmgopt0."&spcmg2=".$specmgopt1;
//  echo "masuk reg ".$regId;

//echo "sp1 ".$specmgopt0code; 
//echo "sp2 ".$specmgopt1code;
 
if(!$cbgtarifresponse) $cbgtarifresponse=0;
//$link = "host=192.168.133.1 dbname=rso user=its password=itsthok";
$link = "host=localhost dbname=rspi user=its password=itsthok";
$connect = pg_connect($link);
//cari dulu
$querydelete = "select inacbg_id from klinik.klinik_inacbg where inacbg_no_sep ='$sep'";
$resultdelete = pg_query($connect, $querydelete);
$datainacbg = pg_fetch_assoc($resultdelete);
 //print_r($datainacbg);
 //die();
//cari data pasiennya
$querydatapasien ="select * from klinik.klinik_registrasi a
                   left join global.global_customer_user b on a.id_cust_usr=b.cust_usr_id
                   where reg_id ='$regId'";
$resultpasien = pg_query($connect, $querydatapasien);
$dataPasien = pg_fetch_assoc($resultpasien);
//print_r($dataPasien);
//die();
//insert
if(!$datainacbg["inacbg_id"]){
$inacbgID = GetTransID();
$query = " INSERT INTO klinik.klinik_inacbg(inacbg_id, id_reg, inacbg_kode, inacbg_no_sep, inacbg_dijamin)
    VALUES ('$inacbgID','$regId', '$cbgcoderesponse','$sep', '$cbgtarifresponse' )";
$result = pg_query($connect, $query);
//echo "<br>".$query;
//die();

}else{
$query1 = " update klinik.klinik_inacbg set id_reg='$regId', inacbg_kode='$cbgcoderesponse', inacbg_dijamin='$cbgtarifresponse',
            inacbg_pasien_nama ='".$dataPasien["cust_usr_nama"]."',inacbg_jenis_kelamin ='".$dataPasien["cust_usr_jenis_kelamin"]."',inacbg_tanggal_lahir ='".$dataPasien["cust_usr_tanggal_lahir"]."'
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1);
//echo "<br>".$query1;
//die();
}
//echo $query;
//echo "masuk kedua";
//die(); 
  if(!$specmgopt0code){  
  if($code=='200'){
  //print_r($dataresponse);
  echo "<script>alert('Data Klaim berhasil diGROUPING!!');</script>";
  }
  
  if($code=='400'){
  echo "<script>alert('$message');</script>";
  }
echo "<script>window.close()</script>";  
   }else{
   if($code=='200'){  
  echo "special_cmg_option 1 = $specmgopt0code<br>special_cmg_option 2 = $specmgopt1code";
  
  $grouper2link = "grouper2.php?id_reg=".$regId."&sep=".$sep."&spcmg1=".$specmgopt0code."&spcmg2=".$specmgopt1code."&spcmg3=".$specmgopt2code."&spcmg4=".$specmgopt3code;
  
  header('location:'.$grouper2link);
  exit();  
  } 
  } 
  
//die();
?> 