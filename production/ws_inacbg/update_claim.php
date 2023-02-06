<?php 
    require_once("encrypt.php");
    require_once("key.php");        

$sep = $_GET["sep"];
$kartu = $_GET["kartu"];
$koderm = $_GET["koderm"];
$tiperawat =$_GET["tiperawat"];     //1 untuk IRNA 2 untuk IRJ
$tglmasuk =$_GET["masuk"];       //tgl (yyyy-mm-dd H:i:s)
$tglpulang =$_GET["pulang"];     //tgl (yyyy-mm-dd H:i:s)
$kelasrawat= $_GET["kelasrawat"];
$adlsubac = $_GET["adlsubac"];
$adlchro = $_GET["adlchro"];
$icuindc = $_GET["icuindc"];
if($_GET["iculos"]){$iculos = $_GET["iculos"];}else{ $iculos='0';}
if($_GET["venthour"]){$venthour = $_GET["venthour"];}else {$venthour='0';}
$upclassind = $_GET["upclassind"];
$upclassclass = $_GET["upclassclass"];
if($_GET["upclasslos"]){$upclasslos = $_GET["upclasslos"];}else{ $upclasslos='0';}
$diagnosa = str_replace(",","#",$_GET["diagnosa"]);
$procedure = str_replace(",","#",$_GET["procedure"]);
$tarifrs = $_GET["tarifrs"];
$tarifekse = $_GET["tarifekse"];
$dokter = $_GET["dokter"];
$kodetarif = $_GET["kodetarif"];
$payorid = $_GET["payorid"];
$payorcode = $_GET["payorcode"];
$usernik = $_GET["usernik"];
$discharge = $_GET["discharge"];
//echo "masuk".$usernik;
//die();
// json query      update claim
if($tiperawat=='1'){
$request = <<<EOT
{
  "metadata": {
     "method" : "set_claim_data",
     "nomor_sep": "$sep"
  },
  "data": {
     "nomor_sep": "$sep",
     "nomor_kartu": "$kartu",
     "tgl_masuk": "$tglmasuk",
     "tgl_pulang": "$tglpulang",
     "jenis_rawat": "$tiperawat",    
     "kelas_rawat": "$kelasrawat",    
     "adl_sub_acute": "$adlsubac",    
     "adl_chronic": "$adlchro",    
     "icu_indikator": "$icuindc",    
     "icu_los": "$iculos",    
     "ventilator_hour": "$venthour",    
     "upgrade_class_ind": "$upclassind",    
     "upgrade_class_class": "$upclassclass",    
     "upgrade_class_los": "$upclasslos",    
     "birth_weight": "0",
     "discharge_status": "$discharge",
     "diagnosa": "$diagnosa",
     "procedure": "$procedure",
     "tarif_rs": "$tarifrs",
     "tarif_poli_eks": "$tarifekse",
     "nama_dokter": "$dokter",
     "kode_tarif": "$kodetarif",
     "payor_id": "$payorid",
     "payor_cd": "$payorcode",
     "coder_nik": "$usernik"
     
  }
}
EOT;
}else{
$request = <<<EOT
{
  "metadata": {
     "method" : "set_claim_data",
     "nomor_sep": "$sep"
  },
  "data": {
     "nomor_sep": "$sep",
     "nomor_kartu": "$kartu",
     "tgl_masuk": "$tglmasuk",
     "tgl_pulang": "$tglpulang",
     "jenis_rawat": "$tiperawat",    
     "kelas_rawat": "$kelasrawat",        
     "adl_chronic": "$adlchro",      
     "birth_weight": "0",
     "discharge_status": "$discharge",
     "diagnosa": "$diagnosa",
     "procedure": "$procedure",
     "tarif_rs": "$tarifrs",
     "tarif_poli_eks": "$tarifekse",
     "nama_dokter": "$dokter",
     "kode_tarif": "$kodetarif",
     "payor_id": "$payorid",
     "payor_cd": "$payorcode",
     "coder_nik": "$usernik"
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
    
  if($code=='200'){
  echo "<script>alert('Data Klaim berhasil dimasukkan!!');</script>";
  }
  
  if($code=='400'){
  echo "<script>alert('$message');</script>";
  }  
  echo "<script>window.close()</script>";
//die();
?>