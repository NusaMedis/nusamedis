<?php
//echo "masuk ".$regId; die(); 
    require_once("encrypt.php");
    require_once("key.php");        

$regId = $_GET["id_reg"];
$sep = $_GET["sep"];
$kartu = $_GET["kartu"];
$nama = $_GET["nama"];
$koderm = $_GET["koderm"];
$tgllahir = $_GET["tgllahir"];
$gender = $_GET["gender"];    // 1 untuk laki-laki dan 2 untuk perempuan
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
$beratlahir = $_GET["beratlahir"];

//pecahan tarif
$bnonbedah = $_GET["bnonbedah"];
$brehab = $_GET["brehab"];
$bkamar = $_GET["bkamar"];
$bintensif = $_GET["bintensif"];
$bobat = $_GET["bobat"];
$balkes = $_GET["balkes"];
$bbmhp = $_GET["bbmhp"];
$baldis = $_GET["baldis"];
$bbedah = $_GET["bbedah"];
$bkonsul = $_GET["bkonsul"];
$bahli = $_GET["bahli"];
$bperawat = $_GET["bperawat"];
$bpenunjang = $_GET["bpenunjang"];
$brad = $_GET["brad"];
$blab = $_GET["blab"];
$bdarah = $_GET["bdarah"];

$link = "host=localhost dbname=muslimat_his user=its password=itsthok";
// echo $link;
// die();
//function GetTransID

    function GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

// json query      new claim
$newrequest = <<<EOT
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
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadnew = mc_encrypt($newrequest,$key);

// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");

// setup curl

// echo $url;
// die();
$chnew = curl_init($url);
curl_setopt($chnew, CURLOPT_URL, $url);
curl_setopt($chnew, CURLOPT_HEADER, 0);
curl_setopt($chnew, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chnew, CURLOPT_HTTPHEADER,$header);
curl_setopt($chnew, CURLOPT_POST, 1);
curl_setopt($chnew, CURLOPT_POSTFIELDS, $payloadnew);
//request dengan curl
$responsenew = curl_exec($chnew);
// echo $responsenew; die();
// echo $request; 
// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstnew  = strpos($responsenew, "\n")+1;
$lastnew   = strrpos($responsenew, "\n")-1;
$responsenew  = substr($responsenew, $firstnew, strlen($responsenew) - $firstnew - $lastnew);

// decrypt dengan fungsi mc_decrypt
$responsenew = mc_decrypt($responsenew,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msg = json_decode($responsenew,true); 

$metadata = $msg["metadata"];
$code = $metadata["code"];
$message = $metadata["message"];
// echo $responsenew; die();  
// echo $metadata; die();   
if($code=='200'){

 ######################################################UPDATE claim ###################
// json query      update claim         
if($tiperawat=='1'){
$requestupdate = <<<EOT
{
"metadata": {
"method": "set_claim_data",
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
"add_payment_pct": "$addpaymentpct",
"birth_weight": "$beratlahir",
"discharge_status": "$discharge",
"diagnosa": "$diagnosa",
"procedure": "$procedure",
"tarif_rs": {
"prosedur_non_bedah": "$bnonbedah",
"prosedur_bedah": "$bbedah",
"konsultasi": "$bkonsul",
"tenaga_ahli": "$bahli",
"keperawatan": "$bperawat",
"penunjang": "$bpenunjang",
"radiologi": "$brad",
"laboratorium": "$blab",
"pelayanan_darah": "$bdarah",
"rehabilitasi": "$brehab",
"kamar": "$bkamar",
"rawat_intensif": "$bintensif",
"obat": "$bobat",
"alkes": "$balkes",
"bmhp": "$bbmhp",
"sewa_alat": "$baldis"
},
"tarif_poli_eks": "$tarifekse",
"nama_dokter": "$dokter",
"kode_tarif": "$kodetarif",
"payor_id": "$payorid",
"payor_cd": "$payorcode",
"cob_cd": "$cobcd",
"coder_nik": "$usernik"
}
}
EOT;
}else{
$requestupdate = <<<EOT
{
"metadata": {
"method": "set_claim_data",
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
"add_payment_pct": "$addpaymentpct",
"birth_weight": "$beratlahir",
"discharge_status": "$discharge",
"diagnosa": "$diagnosa",
"procedure": "$procedure",
"tarif_rs": {
"prosedur_non_bedah": "$bnonbedah",
"prosedur_bedah": "$bbedah",
"konsultasi": "$bkonsul",
"tenaga_ahli": "$bahli",
"keperawatan": "$bperawat",
"penunjang": "$bpenunjang",
"radiologi": "$brad",
"laboratorium": "$blab",
"pelayanan_darah": "$bdarah",
"rehabilitasi": "$brehab",
"kamar": "$bkamar",
"rawat_intensif": "$bintensif",
"obat": "$bobat",
"alkes": "$balkes",
"bmhp": "$bbmhp",
"sewa_alat": "$baldis"
},
"tarif_poli_eks": "$tarifekse",
"nama_dokter": "$dokter",
"kode_tarif": "$kodetarif",
"payor_id": "$payorid",
"payor_cd": "$payorcode",
"cob_cd": "$cobcd",
"coder_nik": "$usernik"
}
}
EOT;
}
//print_r($requestupdate);
//die();
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadupdate = mc_encrypt($requestupdate,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chupdate = curl_init($url);
curl_setopt($chupdate, CURLOPT_URL, $url);
curl_setopt($chupdate, CURLOPT_HEADER, 0);
curl_setopt($chupdate, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chupdate, CURLOPT_HTTPHEADER,$header);
curl_setopt($chupdate, CURLOPT_POST, 1);
curl_setopt($chupdate, CURLOPT_POSTFIELDS, $payloadupdate);
//request dengan curl
$responseupdate = curl_exec($chupdate);

//echo $request; 
// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstupdate  = strpos($responseupdate, "\n")+1;
$lastupdate   = strrpos($responseupdate, "\n")-1;
$responseupdate  = substr($responseupdate, $firstupdate, strlen($responseupdate) - $firstupdate - $lastupdate);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$responseupdate = mc_decrypt($responseupdate,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
//echo $responseupdate;
//die();
$msgupdate = json_decode($responseupdate,true); 
//print_r($msg);

  $metadataupdate = $msgupdate["metadata"];
  $codeupdate = $metadataupdate["code"];
  $messageupdate = $metadataupdate["message"];
    
  if($codeupdate=='200'){

 ######################################################GROUPER ! ###################

$requestgrouper = <<<EOT
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
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadgrouper = mc_encrypt($requestgrouper,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chgruper = curl_init($url);
curl_setopt($chgruper, CURLOPT_URL, $url);
curl_setopt($chgruper, CURLOPT_HEADER, 0);
curl_setopt($chgruper, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chgruper, CURLOPT_HTTPHEADER,$header);
curl_setopt($chgruper, CURLOPT_POST, 1);
curl_setopt($chgruper, CURLOPT_POSTFIELDS, $payloadgrouper);
//request dengan curl
$responsegrouper = curl_exec($chgruper);

//echo $request; 
// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstgruper  = strpos($responsegrouper, "\n")+1;
$lastgrouper   = strrpos($responsegrouper, "\n")-1;
$responsegrouper  = substr($responsegrouper, $firstgruper, strlen($responsegrouper) - $firstgruper - $lastgrouper);

// decrypt dengan fungsi mc_decrypt
$responsegrouper = mc_decrypt($responsegrouper,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msggrouper = json_decode($responsegrouper,true); 
//print_r($msggrouper); die();

  $metadatagrouper = $msggrouper["metadata"];
  $codegrouper = $metadatagrouper["code"];
  $messagegrouper = $metadatagrouper["message"];
  $dataresponsegrouper = $msggrouper["response"];
  $cbgresponsegrouper = $dataresponsegrouper["cbg"];
  $cbgcoderesponsegrouper = $cbgresponsegrouper["code"];
  $cbgdescresponsegrouper = $cbgresponsegrouper["description"];
  $cbgtarifresponsegrouper = $cbgresponsegrouper["tariff"];
  $specmgopt = $msggrouper["special_cmg_option"];
  $specmgopt0 = $specmgopt["0"];
  $specmgopt0code = $specmgopt0["code"];
  $specmgopt1 = $specmgopt["1"];
  $specmgopt1code = $specmgopt1["code"];
  $specmgopt2 = $specmgopt["2"];
  $specmgopt2code = $specmgopt2["code"];
  $specmgopt3 = $specmgopt["3"];
  $specmgopt3code = $specmgopt3["code"];

if(!$cbgtarifresponsegrouper) $cbgtarifresponsegrouper=0;

$connect = pg_connect($link);
//cari dulu
$querydelete = "select inacbg_id from klinik.klinik_inacbg where inacbg_no_sep ='$sep'";
$resultdelete = pg_query($connect, $querydelete);
$datainacbg = pg_fetch_assoc($resultdelete);
//echo $querydatapasien;die();
//cari data pasiennya
$querydatapasien ="select * from klinik.klinik_registrasi a
                   left join global.global_customer_user b on a.id_cust_usr=b.cust_usr_id
                   where reg_id ='$regId'";
$resultpasien = pg_query($connect, $querydatapasien);
$dataPasien = pg_fetch_assoc($resultpasien);
//echo $querydatapasien;die();
//insert
if(!$datainacbg["inacbg_id"]){
$inacbgID = GetTransID();
$query = " INSERT INTO klinik.klinik_inacbg(inacbg_id, id_reg, inacbg_kode, inacbg_no_sep, inacbg_dijamin,inacbg_jenis_pasien)
    VALUES ('$inacbgID','$regId', '$cbgcoderesponsegrouper','$sep', '$cbgtarifresponsegrouper', '$tiperawat' )";
$result = pg_query($connect, $query);
//echo "<br>".$query;
//die();

}else{
$query1 = " update klinik.klinik_inacbg set id_reg='$regId', inacbg_kode='$cbgcoderesponsegrouper', inacbg_dijamin='$cbgtarifresponsegrouper',
            inacbg_pasien_nama ='".$dataPasien["cust_usr_nama"]."',inacbg_jenis_kelamin ='".$dataPasien["cust_usr_jenis_kelamin"]."',inacbg_tanggal_lahir ='".$dataPasien["cust_usr_tanggal_lahir"]."', inacbg_jenis_pasien='$tiperawat'
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1);
echo "<br>".$query1;
//die();
}
//echo $query;
//echo "masuk kedua";
//die(); 
  if(!$specmgopt0code){  
  if($codegrouper=='200'){
//  print_r($dataresponsegrouper);
//  die();
 ######################################################FINAL KLAIM ###################

 $requestfinal = <<<EOT
{
  "metadata": {
     "method" : "claim_final"
  },
  "data": {
     "nomor_sep": "$sep",
     "coder_nik": "$usernik"
  }
}
EOT;
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadfinal = mc_encrypt($requestfinal,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chfinal = curl_init($url);
curl_setopt($chfinal, CURLOPT_URL, $url);
curl_setopt($chfinal, CURLOPT_HEADER, 0);
curl_setopt($chfinal, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chfinal, CURLOPT_HTTPHEADER,$header);
curl_setopt($chfinal, CURLOPT_POST, 1);
curl_setopt($chfinal, CURLOPT_POSTFIELDS, $payloadfinal);
//request dengan curl
$responsefinal = curl_exec($chfinal);

// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstfinal  = strpos($responsefinal, "\n")+1;
$lastfinal   = strrpos($responsefinal, "\n")-1;
$responsefinal  = substr($responsefinal, $firstfinal, strlen($responsefinal) - $firstfinal - $lastfinal);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$responsefinal = mc_decrypt($responsefinal,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msgfinal = json_decode($responsefinal,true); 
//print_r($msg);

  $metadatafinal = $msgfinal["metadata"];
  $codefinal = $metadatafinal["code"];
  $messagefinal = $metadatafinal["message"];
  

$connect = pg_connect($link);
//cari dulu
$querydelete = "select inacbg_id from klinik.klinik_inacbg where inacbg_no_sep ='$sep'";
$resultdelete = pg_query($connect, $querydelete);
$datainacbg = pg_fetch_assoc($resultdelete);

$query1 = " update klinik.klinik_inacbg set inacbg_status_klaim='Final Klaim'
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1); 
 
  if($codefinal=='200'){
  //print_r($dataresponse);
       $query1 = " update klinik.klinik_inacbg set id_reg='$regId', inacbg_kode='$cbgcoderesponsegrouper', inacbg_dijamin='$cbgtarifresponsegrouper',
                inacbg_pasien_nama ='".$dataPasien["cust_usr_nama"]."',inacbg_jenis_kelamin ='".$dataPasien["cust_usr_jenis_kelamin"]."',inacbg_tanggal_lahir ='".$dataPasien["cust_usr_tanggal_lahir"]."', inacbg_jenis_pasien='$tiperawat'
                where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
           // echo $query1;
                $result = pg_query($connect, $query1);
  echo "<script>alert('Data Klaim Telah Berhasil di Bridging dan Final!!');</script>";
  }
  
  if($codefinal=='400'){
  echo "<script>alert('$messagefinal');</script>";
  }
  
  // echo "<script>window.close()</script>"; 
 ######################################################END FINAL KLAIM ###################
  }
  
  if($codegrouper=='400'){
  echo "<script>alert('$messagegrouper');</script>";
  }
// echo "<script>window.close()</script>";  
   }else{
   if($codegrouper=='200'){  
  echo "special_cmg_option 1 = $specmgopt0code<br>special_cmg_option 2 = $specmgopt1code";
  

 ######################################################GROUPER 2 ###################
  $spcmg1 = $specmgopt0code;
  $spcmg2 = $specmgopt1code;
  $spcmg3 = $specmgopt2code;
  $spcmg4 = $specmgopt3code;
  
if($spcmg4){
$special_cmg = "$spcmg1#$spcmg2#$spcmg3#$spcmg4";
}elseif($spcmg3 && !$spcmg4){
$special_cmg = "$spcmg1#$spcmg2#$spcmg3";
}elseif($spcmg2 && !$spcmg3){
$special_cmg = "$spcmg1#$spcmg2#$spcmg";
}elseif($spcmg1 && !$spcmg2){
$special_cmg = "$spcmg1";
}

$requestgrup2 = <<<EOT
{
  "metadata": {
     "method" : "grouper",
     "stage": "2"
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
$payloadgrup2 = mc_encrypt($requestgrup2,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chgrup2 = curl_init($url);
curl_setopt($chgrup2, CURLOPT_URL, $url);
curl_setopt($chgrup2, CURLOPT_HEADER, 0);
curl_setopt($chgrup2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chgrup2, CURLOPT_HTTPHEADER,$header);
curl_setopt($chgrup2, CURLOPT_POST, 1);
curl_setopt($chgrup2, CURLOPT_POSTFIELDS, $payloadgrup2);
//request dengan curl
$responsegrup2 = curl_exec($chgrup2);



//echo $request; 
// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstgrup2  = strpos($responsegrup2, "\n")+1;
$lastgrup2   = strrpos($responsegrup2, "\n")-1;
$responsegrup2  = substr($responsegrup2, $firstgrup2, strlen($responsegrup2) - $firstgrup2 - $lastgrup2);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$responsegrup2 = mc_decrypt($responsegrup2,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msggrup2 = json_decode($responsegrup2,true); 
//print_r($msg);

  $metadatagrup2 = $msggrup2["metadata"];
  $codegrup2 = $metadatagrup2["code"];
  $messagegrup2 = $metadatagrup2["message"];
  $dataresponsegrup2 = $msggrup2["response"];
  $cbgresponsegrup2 = $dataresponsegrup2["cbg"];
  $cbgcoderesponsegrup2 = $cbgresponsegrup2["code"];
  $cbgdescresponsegrup2 = $cbgresponsegrup2["description"];
  $cbgtarifresponsegrup2 = $cbgresponsegrup2["tariff"];
  $specmgoptgrup2 = $dataresponsegrup2["special_cmg"];
  $specmgopt0grup2 = $specmgoptgrup2["0"];
  $specmgopt0codegrup2 = $specmgopt0grup2["code"];
  $specmgopt0tariffgrup2 = $specmgopt0grup2["tariff"];
  $specmgopt1grup2 = $specmgoptgrup2["1"];
  $specmgopt1codegrup2 = $specmgopt1grup2["code"];
  $specmgopt1tariffgrup2 = $specmgopt1grup2["tariff"];
  $specmgopt2grup2 = $specmgoptgrup2["2"];
  $specmgopt2codegrup2 = $specmgopt2grup2["code"];
  $specmgopt2tariffgrup2 = $specmgopt2grup2["tariff"];  
  $specmgopt3grup2 = $specmgoptgrup2["3"];
  $specmgopt3codegrup2 = $specmgopt3grup2["code"];
  $specmgopt3tariffgrup2 = $specmgopt3grup2["tariff"];
 
  if($specmgopt3grup2){
  $topup = $specmgopt3tariffgrup2+$specmgopt2tariffgrup2+$specmgopt1tariffgrup2+$specmgopt0tariffgrup2;
  }elseif($specmgopt2grup2 && !$specmgopt3grup2){
  $topup = $specmgopt2tariffgrup2+$specmgopt1tariffgrup2+$specmgopt0tariffgrup2;  
  }elseif($specmgopt1grup2 && !$specmgopt2grup2){ 
  $topup = $specmgopt1tariffgrup2+$specmgopt0tariffgrup2;
  }elseif($specmgopt0grup2 && !$specmgopt1grup2) {
  $topup = $specmgopt0tariffgrup2;
  }
  //echo "special pro ".$specmgopt0code;

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
    VALUES ('$inacbgID','$regId', '$cbgcoderesponsegrup2','$sep', '$cbgtarifresponsegrup2' )";
$result = pg_query($connect, $query);
//echo "<br>".$query;
//die();

}else{
$query1 = " update klinik.klinik_inacbg set id_reg='$regId', inacbg_kode='$cbgcoderesponsegrup2', inacbg_dijamin='$cbgtarifresponsegrup2',
            inacbg_pasien_nama ='".$dataPasien["cust_usr_nama"]."',inacbg_jenis_kelamin ='".$dataPasien["cust_usr_jenis_kelamin"]."',
            inacbg_tanggal_lahir ='".$dataPasien["cust_usr_tanggal_lahir"]."',inacbg_sp ='".$spcmg1."',
            inacbg_drugs ='".$spcmg4."',inacbg_investigation ='".$spcmg3."',
            inacbg_prosthesis ='".$spcmg2."',inacbg_topup='".$topup."' 
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1);
//echo $query1;// die();
}
  
  if($codegrup2=='200'){
  echo "kode CBG = $cbgcoderesponse<br>special_cmg 1 = $specmgopt0code<br>special_cmg2 = $specmgopt1code";
 ######################################################FINAL KLAIM ###################

 $requestfinal = <<<EOT
{
  "metadata": {
     "method" : "claim_final"
  },
  "data": {
     "nomor_sep": "$sep",
     "coder_nik": "$usernik"
  }
}
EOT;
// data yang akan dikirimkan dengan method POST adalah encrypted:
$payloadfinal = mc_encrypt($requestfinal,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chfinal = curl_init($url);
curl_setopt($chfinal, CURLOPT_URL, $url);
curl_setopt($chfinal, CURLOPT_HEADER, 0);
curl_setopt($chfinal, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chfinal, CURLOPT_HTTPHEADER,$header);
curl_setopt($chfinal, CURLOPT_POST, 1);
curl_setopt($chfinal, CURLOPT_POSTFIELDS, $payloadfinal);
//request dengan curl
$responsefinal = curl_exec($chfinal);

// terlebih dahulu hilangkan "----BEGIN ENCRYPTED DATA----\r\n"
// dan hilangkan "----END ENCRYPTED DATA----\r\n"
$firstfinal  = strpos($responsefinal, "\n")+1;
$lastfinal   = strrpos($responsefinal, "\n")-1;
$responsefinal  = substr($responsefinal, $firstfinal, strlen($responsefinal) - $firstfinal - $lastfinal);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$responsefinal = mc_decrypt($responsefinal,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msgfinal = json_decode($responsefinal,true); 
//print_r($msg);

  $metadatafinal = $msgfinal["metadata"];
  $codefinal = $metadatafinal["code"];
  $messagefinal = $metadatafinal["message"];
  
$connect = pg_connect($link);
//cari dulu
$querydelete = "select inacbg_id from klinik.klinik_inacbg where inacbg_no_sep ='$sep'";
$resultdelete = pg_query($connect, $querydelete);
$datainacbg = pg_fetch_assoc($resultdelete);

$query1 = " update klinik.klinik_inacbg set inacbg_status_klaim='Final Klaim'
           where inacbg_id='".$datainacbg["inacbg_id"]."' and inacbg_no_sep ='$sep'";
$result = pg_query($connect, $query1); 
 
  if($codefinal=='200'){
  print_r($dataresponse);
  echo "<script>alert('Data Klaim Telah Berhasil di Bridging dan Final!!');</script>";
  }
  
  if($codefinal=='400'){
  echo "<script>alert('$messagefinal');</script>";
  }
  
 // echo "<script>window.close()</script>"; 
 ######################################################END FINAL KLAIM ###################
  }
  
  if($codegrup2=='400'){
  echo "<script>alert('$messagegrup2');</script>";
  } 
//echo "<script>window.close()</script>"; 
  
 ######################################################END GROUPER 2 ################### 
  } 
  } 

 ###################################################### END GROUPER 1###################
  }
  
  if($codeupdate=='400'){
  echo "<script>alert('$messageupdate');</script>";
  }  
  //echo "<script>window.close()</script>";
}
 ###################################################### END update claim ###################
if($code=='400'){
  echo "<script>alert('$message');</script>";
  }
  echo "<script>window.close()</script>";  
//die();
?>