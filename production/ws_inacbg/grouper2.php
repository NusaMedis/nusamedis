<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$stage = "2";
$regId = $_GET["id_reg"];
$spcmg1= $_GET["spcmg1"];
$spcmg2= $_GET["spcmg2"]; 
$spcmg3= $_GET["spcmg3"];
$spcmg4= $_GET["spcmg4"];
if($spcmg4){
$special_cmg = "$spcmg1#$spcmg2#$spcmg3#$spcmg4";
}elseif($spcmg3 && !$spcmg4){
$special_cmg = "$spcmg1#$spcmg2#$spcmg3";
}elseif($spcmg2 && !$spcmg3){
$special_cmg = "$spcmg1#$spcmg2#$spcmg";
}elseif($spcmg1 && !$spcmg2){
$special_cmg = "$spcmg1";
} 

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
     "stage": "$stage"
  },
  "data": {
     "nomor_sep": "$sep",
     "special_cmg": "$special_cmg"
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
  $specmgopt = $dataresponse["special_cmg"];
  $specmgopt0 = $specmgopt["0"];
  $specmgopt0code = $specmgopt0["code"];
  $specmgopt0tariff = $specmgopt0["tariff"];
  $specmgopt1 = $specmgopt["1"];
  $specmgopt1code = $specmgopt1["code"];
  $specmgopt1tariff = $specmgopt1["tariff"];
  $specmgopt2 = $specmgopt["2"];
  $specmgopt2code = $specmgopt2["code"];
  $specmgopt2tariff = $specmgopt2["tariff"];  
  $specmgopt3 = $specmgopt["3"];
  $specmgopt3code = $specmgopt3["code"];
  $specmgopt3tariff = $specmgopt3["tariff"];
 
  if($specmgopt3){
  $topup = $specmgopt3tariff+$specmgopt2tariff+$specmgopt1tariff+$specmgopt0tariff;
  }elseif($specmgopt2 && !$specmgopt3){
  $topup = $specmgopt2tariff+$specmgopt1tariff+$specmgopt0tariff;  
  }elseif($specmgopt1 && !$specmgopt2){ 
  $topup = $specmgopt1tariff+$specmgopt0tariff;
  }elseif($specmgopt0 && !$specmgopt1) {
  $topup = $specmgopt0tariff;
  }
  //echo "special pro ".$specmgopt0code;
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
            inacbg_pasien_nama ='".$dataPasien["cust_usr_nama"]."',inacbg_jenis_kelamin ='".$dataPasien["cust_usr_jenis_kelamin"]."',
            inacbg_tanggal_lahir ='".$dataPasien["cust_usr_tanggal_lahir"]."',inacbg_sp ='".$spcmg1."',
            inacbg_drugs ='".$spcmg4."',inacbg_investigation ='".$spcmg3."',
            inacbg_prosthesis ='".$spcmg2."',inacbg_topup='".$topup."' 
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1);
//echo $query1;// die();
}
  
  if($code=='200'){
  echo "kode CBG = $cbgcoderesponse<br>special_cmg 1 = $specmgopt0code<br>special_cmg2 = $specmgopt1code";
  echo "<script>alert('Data Klaim berhasil di GROUPER Stage 2 !!');</script>";
  }
  
  if($code=='400'){
  echo "<script>alert('$message');</script>";
  } 
echo "<script>window.close()</script>";
//die();
?>