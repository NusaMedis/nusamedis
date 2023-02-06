<?php
 // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."expAJAX.php"); 
     require_once($LIB."tampilan.php");
    require_once("encrypt.php");
    require_once("key.php");        


     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
   $depId = $auth->GetDepId();
   $userName = $auth->GetUserName();
   $userData = $auth->GetUserData();

$regId = $_GET["id_reg"];

// cari data pasien registrasi
$sql ="select * from klinik.klinik_registrasi a
                   left join global.global_customer_user b on a.id_cust_usr=b.cust_usr_id
                   left join klinik.klinik_inacbg c on a.reg_id =c.id_reg
                   where reg_id ='$regId'";
$rs = $dtaccess->Execute($sql);
$dataReg = $dtaccess->Fetch($rs);

$sep = $dataReg["reg_no_sep"];
$kartu = $dataReg["inacbg_no_peserta"];
$nama = $dataReg["cust_usr_nama"];
$koderm = $dataReg["cust_usr_kode"];
$tgllahir = $dataReg["cust_usr_tanggal_lahir"];
if($dataReg["cust_usr_jenis_kelamin"]=='L'){
$gender ='1';    
}else{
$gender = '2';    // 1 untuk laki-laki dan 2 untuk perempuan
}
if($dataReg["reg_tipe_rawat"]=='I'){
$tiperawat ='1';      //1 untuk IRNA 2 untuk IRJ	
}else{
$tiperawat ='2';      //1 untuk IRNA 2 untuk IRJ	
}
$tglmasuk =$dataReg["inacbg_tanggal_masuk"];       //tgl (yyyy-mm-dd H:i:s)
$tglpulang =$dataReg["inacbg_tanggal_keluar"];     //tgl (yyyy-mm-dd H:i:s)
$kelasrawat= $dataReg["inacbg_kelas_perawatan"];
$adlsubac = $dataReg["adlsubac"];
$adlchro = $dataReg["adlchro"];
$icuindc = $dataReg["icuindc"];
//echo $sql;
//die();
if($_GET["iculos"]){$iculos = $_GET["iculos"];}else{ $iculos='0';}
if($_GET["venthour"]){$venthour = $_GET["venthour"];}else {$venthour='0';}

if($dataReg["reg_tipe_rawat"]=='I'){
//urusan naik kelas atau tidak
$sql = "select * from klinik.klinik_kelas where kelas_id = ".QuoteValue(DPE_CHAR,$dataReg["reg_kelas"]);
$rs = $dtaccess->Execute($sql);
$datakelasnya = $dtaccess->Fetch($rs);
//jika kelas tingkat dikurangi hak kelas inap = 1 maka sesuai hak
// jika kurang dari 1 mka turun kelas 
//jika lebih dari 1 maka naik kelas

$kenaikankelas = $datakelasnya["kelas_tingkat"]-$dataReg["hak_kelas_inap"];
//cari mulai kapan naik kelasnya
$sql = "select * from klinik.klinik_rawat_inap_history a
		left join klinik.klinik_rawatinap b on a.id_rawatinap = b.rawatinap_id
		left join klinik.klinik_registrasi c on b.id_reg =c.reg_id
		where c.reg_id = '$regId' and a.rawat_inap_history_kelas_tujuan = c.reg_kelas";
$datainapnaik = $dtaccess->Fetch($sql);

$startnaik = strtotime($datainapnaik["rawat_inap_history_tanggal"]);
$selesainaik = strtotime($datainapnaik["reg_tanggal_pulang"]);
$losnaik = floor(($selesainaik-$startnaik)/(60*60*24))+1;

if($kenaikankelas > '1'){
$upclassind = '1';
$upclassclass = $datakelasnya["kelas_nama_bpjs"];	
$upclasslos = $losnaik;
$addpaymentpct = $_GET["addpaymentpct"];	
}else{
$upclassind = '0';
$upclassclass = '0';	
$upclasslos = '0';
$addpaymentpct='0';	
}
}else{
$upclassind = '0';
$upclassclass = '0';	
$upclasslos = '0';
$addpaymentpct='0';	
}
//cari data icd
$sql ="select rawat_icd_kode from klinik.klinik_perawatan_icd a
                   left join klinik.klinik_perawatan b on a.id_rawat=b.rawat_id
                   left join klinik.klinik_registrasi c on c.reg_id =b.id_reg
                   where reg_id ='$regId'
                   order by rawat_icd_urut asc";
$rs = $dtaccess->Execute($sql);
$dataIcd = $dtaccess->FetchAll($rs);
//echo $sql;
//print_r($dataIcd);

$icd10= array( );

for($a=0,$b=count($dataIcd);$a<$b;$a++){
$icd10[] = $dataIcd[$a]["rawat_icd_kode"];
 }

$diagnosa = implode('#', $icd10);

//cari data icd9
$sql ="select rawat_icd9_kode from klinik.klinik_perawatan_icd9 a
                   left join klinik.klinik_perawatan b on a.id_rawat=b.rawat_id
                   left join klinik.klinik_registrasi c on c.reg_id =b.id_reg
                   where reg_id ='$regId'
                   order by rawat_icd9_urut asc";
$rs = $dtaccess->Execute($sql);
$dataIcd9 = $dtaccess->FetchAll($rs);

$icd9= array( );

for($a=0,$b=count($dataIcd9);$a<$b;$a++){
$icd9[] = $dataIcd9[$a]["rawat_icd9_kode"];
 }

$procedure = implode('#', $icd9);
//echo $diagnosa;


//$procedure = str_replace(",","#",$_GET["procedure"]);

//hitung total tagihan
$sql = "select sum(fol_nominal) as total from klinik.klinik_folio where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"]);
$rs = $dtaccess->Execute($sql);
$tagihantotal = $dtaccess->Fetch($rs);

$tarifrs = $tagihantotal["total"];

     //biaya non bedah
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='1' ";
     $rs = $dtaccess->Execute($sql);
     $dataNonBedah = $dtaccess->Fetch($rs);
     //rehab
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='10' ";
     $rs = $dtaccess->Execute($sql);
     $dataRehab = $dtaccess->Fetch($rs);
     //akomodasi
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='11' ";
     $rs = $dtaccess->Execute($sql);
     $dataAkomodasi = $dtaccess->Fetch($rs);
     //intensif
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='12' ";
     $rs = $dtaccess->Execute($sql);
     $dataIntensif = $dtaccess->Fetch($rs);
     //obat
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='13' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarObat = $dtaccess->Fetch($rs);
     //alkes
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='14' ";
     $rs = $dtaccess->Execute($sql);
     $dataAlkes = $dtaccess->Fetch($rs);
     //bmhp
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='15' ";
     $rs = $dtaccess->Execute($sql);
     $dataBmhp = $dtaccess->Fetch($rs);
     //alat medis
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='16' ";
     $rs = $dtaccess->Execute($sql);
     $dataAlMedis = $dtaccess->Fetch($rs);
     //bedah
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='2' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarBedah = $dtaccess->Fetch($rs);
     //konsultasi
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='3' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarKonsul = $dtaccess->Fetch($rs);
     //tenaga ahli
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='4' ";
     $rs = $dtaccess->Execute($sql);
     $dataAhli = $dtaccess->Fetch($rs);
     //keperawatan
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='5' ";
     $rs = $dtaccess->Execute($sql);
     $dataKeperawatan = $dtaccess->Fetch($rs);
     //penunjang
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='6' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarPenunjang = $dtaccess->Fetch($rs);
     //radiologi
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='7' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarRad = $dtaccess->Fetch($rs);
     //lab
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='8' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarLab = $dtaccess->Fetch($rs);
     //darah
     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio a
             left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataReg["id_pembayaran"])."
             and b.biaya_jenis ='9' ";
     $rs = $dtaccess->Execute($sql);
     $dataBayarDarah = $dtaccess->Fetch($rs);

$bnonbedah = $dataNonBedah["jumlah_nominal"];
$brehab = $dataRehab["jumlah_nominal"];
$bkamar = $dataAkomodasi["jumlah_nominal"];
$bintensif = $dataIntensif["jumlah_nominal"];
$bobat = $dataBayarObat["jumlah_nominal"];
$balkes = $dataAlkes["jumlah_nominal"];
$bbmhp = $dataBmhp["jumlah_nominal"];
$baldis = $dataAlMedis["jumlah_nominal"];
$bbedah = $dataBayarBedah["jumlah_nominal"];
$bkonsul = $dataBayarKonsul["jumlah_nominal"];
$bahli = $dataAhli["jumlah_nominal"];
$bperawat = $dataKeperawatan["jumlah_nominal"];
$bpenunjang = $dataBayarPenunjang["jumlah_nominal"];
$brad = $dataBayarRad["jumlah_nominal"];
$blab = $dataBayarLab["jumlah_nominal"];
$bdarah = $dataBayarDarah["jumlah_nominal"];


$tarifekse = $_GET["tarifekse"];
$dokter = $dataReg["inacbg_dokter"];
$kodetarif = "AP"; //tipe A pemerintah
$payorid = "3"; //id jkn
$payorcode = "JKN";
$usernik = "123123123123";
$discharge = $_GET["discharge"];
$beratlahir = $dataReg["cust_usr_berat_lahir"];
//echo $kodetarif;
//die();
$link = "host=localhost dbname=rspi user=its password=itsthok";
//echo $link;
//die();
//function GetTransID

    function GetTransID()
    {
        $r = rand();
        $u = uniqid(getmypid() . $r . (double)microtime()*1000000,true);
        $m = md5(session_id().$u);
        return($m);  
    }

#####update data pasien#######
 $requestupdatepasien = '
{
"metadata" :{
    "method" : "update_patient",
    "nomor_rm" : "'.$koderm.'"
},
"data" : {
    "nomor_kartu" : "'.$kartu.'",
    "nomor_rm" : "'.$koderm.'",
    "nama_pasien" : "'.$nama.'",
    "tgl_lahir" : "'.$tgllahir.'",
    "gender" : "'.$gender.'"
}
}
';   

echo $requestupdatepasien;
$payloadupdatepasien = mc_encrypt($requestupdatepasien,$key);
//$payload = $request;
// tentukan Content-Type pada http header
$header = array("Content-Type: application/x-www-form-urlencoded");
// url server aplikasi E-Klaim,
// silakan disesuaikan instalasi masing-masing
// setup curl
$chupdatepas = curl_init($url);
curl_setopt($chupdatepas, CURLOPT_URL, $url);
curl_setopt($chupdatepas, CURLOPT_HEADER, 0);
curl_setopt($chupdatepas, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chupdatepas, CURLOPT_HTTPHEADER,$header);
curl_setopt($chupdatepas, CURLOPT_POST, 1);
curl_setopt($chupdatepas, CURLOPT_POSTFIELDS, $payloadupdatepasien);
//request dengan curl
$responseupdatepas = curl_exec($chupdatepas);
####end update pasien
$firstfinal  = strpos($responseupdatepas, "\n")+1;
$lastfinal   = strrpos($responseupdatepas, "\n")-1;
$responseupdatepas  = substr($responseupdatepas, $firstfinal, strlen($responseupdatepas) - $firstfinal - $lastfinal);

//echo "masuk";
//die();
// decrypt dengan fungsi mc_decrypt
$responseupdatepas = mc_decrypt($responseupdatepas,$key);
// hasil decrypt adalah format json, ditranslate kedalam array
$msgfinal = json_decode($responseupdatepas,true); 
print_r($msgfinal);

/*  $metadatafinal = $msgfinal["metadata"];
  $codefinal = $metadatafinal["code"];
  $messagefinal = $metadatafinal["message"];

if($codefinal=='200'){
  echo "<script>alert('$messagefinal');</script>";
  }
  
  if($codefinal=='400'){
  echo "<script>alert('$messagefinal');</script>";
  }  
*/  //echo "<script>window.close()</script>";
?>