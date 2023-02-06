<?php
     // Library
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
   //  require_once($LIB."expAJAX.php"); 
     require_once($LIB."tampilan.php");

     error_reporting(0);
     
     // Inisialisasi Lib
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $enc = new textEncrypt();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depId = $auth->GetDepId();
     $poliId = $auth->IdPoli();
     $tglSekarang = date("d-m-Y");
     $depLowest = $auth->GetDepLowest();

     //$wsinacbgserver = "172.16.202.20";
    // $wsinacbgserver = $_SERVER['SERVER_NAME'];
     $sql = 'select dep_id from global.global_departemen';
     $a = $dtaccess->Fetch($sql);
     $depId = $a['dep_id'];
     
     $wsinacbgserver = $_SERVER['HTTP_HOST']."/muslimat_his/production";
    // echo $wsinacbgserver; 
    // echo $wsinacbgserver; 
     // Link rel
     $_x_mode = "New";
     

    // Keadaan Keluar
     $sql = "select * from klinik.klinik_keadaan_keluar_inap order by keadaan_keluar_inap_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $keadaanKeluarInap = $dtaccess->FetchAll($rs);

      // Cara Keluar
     $sql = "select * from klinik.klinik_cara_keluar_inap where cara_keluar_inap_id !='3' and cara_keluar_inap_id !='4' order by cara_keluar_inap_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $caraKeluarInap = $dtaccess->FetchAll($rs);


   
     
    

     //cari data icd
     $sql = "select * from klinik.klinik_icd order by icd_nomor asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataicd = $dtaccess->FetchAll($rs); 
     
     //cari data obat
     $sql = "select * from logistik.logistik_item";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $dataObat = $dtaccess->FetchAll($rs);   
     
     //AJAX
     $reg = $_GET["id_reg"]; 

             

    $lokasi = $ROOT."/gambar/foto_gigi";
    $lokasiXray = $ROOT."/gambar/foto_xray_gigi";
    
      $sqlPerawatan = " select a.rawat_id, a.rawat_tanggal, a.rawat_keluhan, a.id_cust_usr, a.rawat_foto, a.rawat_foto_xray, a.id_reg, a.rawat_anamnesa, a.rawat_penting, a.rawat_terapi, a.rawat_catatan, a.rawat_pemeriksaan_fisik, a.rawat_penunjang 
                        from klinik.klinik_perawatan a
                        left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
                        where id_cust_usr =".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"])." and a.id_dep =".QuoteValue(DPE_CHAR,$depId)." order by rawat_tanggal desc";
      $rs = $dtaccess->Execute($sqlPerawatan);
      $dataPerawatan= $dtaccess->FetchAll($rs); 

      $sql = "select * from global.global_departemen a
             left join global.global_cust_ket_label b on b.id_dep = a.dep_id
             left join global.global_cust_ket_isi c on c.id_cust_ket = b.cust_ket_id
             where dep_id = ".QuoteValue(DPE_CHAR,$depId)." and c.id_cust_usr =".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
      $rs = $dtaccess->Execute($sql); 
      $dataLabel = $dtaccess->FetchAll($rs);
      
      $lokasiFoto = $APLICATION_ROOT."gambar/foto_pasien";
      $lokTakeFoto = $ROOT."/gambar/foto_gigi";
      
      // DATA Kode ICD
     $sql = "select * from klinik.klinik_icd order by icd_nomor asc";
     $rs = $dtaccess->Execute($sql);
     $dataicd = $dtaccess->FetchAll($rs);
   
  if ($_POST["hidTambah"]) $_POST["btnTambah"]=1;

    
   // ----- update dan simpan data ----- //
   if ($_POST["btnSave"] || $_POST["btnVerif"] || $_POST["btnTambah"]) {
   // echo "Masuk sini"; 
   $cust_usr_kode_tampilan=substr($_POST["cust_usr_kode"],3) ; 



    $isiImunisasi = implode(",", $_POST['imunisasi']);
    // echo "data ".$isiImunisasi;
 	$imunisasi_id = $dtaccess->GetTransID();  
 	// echo $isiImunisasi;

   	

    $date=date('Y-m-d');


    $sql = "UPDATE klinik.klinik_registrasi set reg_tanggal = ".QuoteValue(DPE_DATE, date_db($_POST['tgl_masuk'])).", reg_tanggal_pulang = ".QuoteValue(DPE_DATE, date_db($_POST['tgl_pulang'])).", 
    reg_keluar_inap = ".QuoteValue(DPE_DATE, date_db($_POST['tgl_pulang'])).", reg_kelas = ".QuoteValue(DPE_CHAR, $_POST['kls_perawatan']).", reg_cara_keluar_inap = ".QuoteValue(DPE_CHAR, $_POST['id_cara_keluar_inap'])." WHERE reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
    $dtaccess->Execute($sql);

    $sql = "UPDATE klinik.klinik_inacbg set inacbg_tanggal_masuk = ".QuoteValue(DPE_DATE, date_db($_POST['tgl_masuk'])).", inacbg_tanggal_keluar = ".QuoteValue(DPE_DATE, date_db($_POST['tgl_pulang']))." WHERE id_reg = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
    $dtaccess->Execute($sql);

    // $sql = "UPDATE klinik.klinik_perawatan set rawat_anamnesa2 = ".QuoteValue(DPE_CHAR, $_POST['anamnesa']). ", rawat_terapi=".QuoteValue(DPE_CHAR, $_POST['rawat_terapi_manual']) .
    // " WHERE rawat_id = ".QuoteValue(DPE_CHAR, $_POST['rawat_id']);
    // $dtaccess->Execute($sql);
    // echo $sql;

   //data Diagnosa
   $sql = "select a.* from klinik.klinik_perawatan_icd a left join klinik.klinik_perawatan b on b.rawat_id = a.id_rawat where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
   $diagnosa = $dtaccess->FetchAll($sql); 

   $sql = "select a.* from klinik.klinik_perawatan_icd9 a left join klinik.klinik_perawatan b on b.rawat_id = a.id_rawat where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
   $procedure = $dtaccess->FetchAll($sql);

   if ($_POST['id_dokter'] == '') {
     $sql = "select usr_name from klinik.klinik_registrasi a left join global.global_auth_user b on b.usr_id = a.id_dokter where reg_id = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
   }else{
    $sql = "select * from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$_POST['id_dokter']);
   }
   $datadpjp = $dtaccess->Fetch($sql);
   $_POST['dpjp'] = $datadpjp['usr_name'];

//untuk simpan database

//untuk bridging
$user_nm=$_POST['user_nm'];
$user_pw=$_POST['user_pw'];
$norm=$_POST['norm'];
$nm_pasien=$_POST['nm_pasien'];
$jns_kelamin=$_POST['jns_kelamin'];
$tgl_lahir=date_db($_POST['cust_usr_tanggal_lahir']);
$jns_pbyrn=$_POST['jns_pbyrn'];
$cust_usr_no_jaminan=$_POST['cust_usr_no_jaminan'];
$no_sep=$_POST['reg_no_sep'];
$jns_perawatan=$_POST['jns_perawatan'];
$kls_perawatan=$_POST['kls_perawatan'];
$tgl_masuk=date_db($_POST['tgl_masuk']);
$tgl_keluar=date_db($_POST['tgl_keluar']);
$cara_keluar=$_POST['cara_keluar'];
$dpjp=$_POST['dpjp'];
$berat_lahir=$_POST['berat_lahir'];
$tarif_rs=$_POST['tarif_rs'];
$srt_rujukan=$_POST['srt_rujukan'];
$bhp=$_POST['bhp'];
$severity3=$_POST['severity3'];
$adl=$_POST['adl'];
$spec_proc=$_POST['spec_proc'];
$spec_dr=$_POST['spec_dr'];
$spec_inv=$_POST['spec_inv'];
$spec_prosth=$_POST['spec_prosth'];
$diag1=$diagnosa[0]['rawat_icd_kode'];
$diag2=$diagnosa[1]['rawat_icd_kode'];
$diag3=$diagnosa[2]['rawat_icd_kode'];
$diag4=$diagnosa[3]['rawat_icd_kode'];
$diag5=$diagnosa[4]['rawat_icd_kode'];
$diag6=$diagnosa[5]['rawat_icd_kode'];
$diag7=$diagnosa[6]['rawat_icd_kode'];
$diag8=$diagnosa[7]['rawat_icd_kode'];
$diag9=$diagnosa[8]['rawat_icd_kode'];
$diag10=$diagnosa[9]['rawat_icd_kode'];
$proc1=$procedure[0]['rawat_icd9_kode'];
$proc2=$procedure[1]['rawat_icd9_kode'];
$proc3=$procedure[2]['rawat_icd9_kode'];
$proc4=$procedure[3]['rawat_icd9_kode'];
$proc5=$procedure[4]['rawat_icd9_kode'];
$proc6=$procedure[5]['rawat_icd9_kode'];
$proc7=$procedure[6]['rawat_icd9_kode'];
$proc8=$procedure[7]['rawat_icd9_kode'];
$proc9=$procedure[8]['rawat_icd9_kode'];
$proc10=$procedure[9]['rawat_icd9_kode'];
$usernik = $_POST["usernik"];            
       //cari id_inacbg
        $sql = "select * from klinik.klinik_inacbg where id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $rs = $dtaccess->Execute($sql);
        $dataInacbgku = $dtaccess->Fetch($rs);
        
        if($dataInacbgku) $_POST["id_inacbg"] = $dataInacbgku["inacbg_id"];

        // nominal data tindakannya //
           $totalBiaya = $_POST["txtTotalDibayar"]; 
               
           
           // data icd penyakit pasiennya //
           $sql = "select * from klinik.klinik_icd where icd_id = '".$_POST["id_icd"]."'";
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           $dataicdRawat = $dtaccess->Fetch($rs);
           
           //$_POST["icd_nama"] = $dataicdRawat["icd_nama"];
           $_POST["pasien_diagnosa_utama"] = $dataicdRawat["icd_nama"];
       
           if($_POST["rawat_penting_anamnesa"]) $anamnesa = "1";  else $anamnesa = "0";
           if($_POST["icd_nama"]) $keluhan = "1"; else $keluhan = "0";
           if($_POST["rawat_penting_terapi"]) $terapi = "1"; else $terapi = "0";
           if($_POST["rawat_penting_catatan"]) $catatan = "1"; else $catatan = "0";
           $hasil = $anamnesa."-".$keluhan."-".$terapi."-".$catatan;
           
           // data icd penyakit pasiennya //
           $sql = "select * from klinik.klinik_perawatan where id_reg = '".$_POST["id_reg"]."'";
           $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
           $dataIdRawat = $dtaccess->Fetch($rs);
          
          if($_POST["btnSave"]) $checkFlag = 'n'; 
         if($_POST["btnSaveVerif"] || $_POST["btnVerif"]) $checkFlag = 'y'; 
          
             



 //SEMENTARA DIHILANGKAN
 
          // //$lunas = ($_POST["cust_usr_jenis"]==PASIEN_BAYAR_SWADAYA)?'n':'y';
          // // Insert data Perawatan Pasien //
          // $dbTable = " klinik.klinik_perawatan";
          // $dbField[0] = "rawat_id";   // PK
          // $dbField[1] = "id_reg";
          // $dbField[2] = "rawat_keluhan";
          // $dbField[3] = "id_cust_usr";
          // //$dbField[4] = "rawat_waktu";
          // $dbField[4] = "rawat_catatan";
          // //$dbField[6] = "rawat_tanggal";
          // $dbField[5] = "rawat_flag"; 
          // $dbField[6] = "rawat_flag_komen"; 
          // $dbField[7] = "id_poli"; 
          // $dbField[8] = "id_dep";
          // $dbField[9] = "rawat_who_insert_icd";
          // $dbField[10] = "rawat_terapi"; 
          // $dbField[11] = "rawat_penting";            
          // $dbField[12] = "rawat_diagnosa_utama";
          // $dbField[13] = "rawat_diagnosa_kedua";
          // $dbField[14] = "rawat_pemeriksaan_lain";
          // $dbField[15] = "rawat_dokter";
          // $dbField[16] = "rawat_tindakan";
          // $dbField[17] = "rawat_waktu_kontrol";
          // $dbField[18] = "rawat_anamnesa2";
          // $dbField[19] = "perawatan_tanggal_partus";
          // $dbField[20] = "perawatan_tanggal_kuret";
          

          // //if($_POST["btnSave"]){ 
          // //$dbField[23] = "rawat_waktu";         
          // if($dataIdRawat) $_POST["rawat_id"]= $_POST["rawat_id"];
          // else $_POST["rawat_id"] = $dtaccess->GetTransID();   
          
          // $time = date("H:i:s");
          // $waktu = date_db($_POST["rawat_tanggal"])." ".$time;
          
          // $hour = substr($_POST["jam"], -2);
          // $minutes = substr($_POST["menit"], -2);
          // $second = substr($_POST["detik"], -2);
          // $timeNow = $hour.":".$minutes.":".$second;       
          
          // $dbValue[0] = QuoteValue(DPE_CHAR,$_POST["rawat_id"]);   // PK
          // $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
          // $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["icd_nama"]);
          // $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
          // //$dbValue[4] = QuoteValue(DPE_CHAR,date_db($_POST["rawat_tanggal"])." ".$timeNow);
          // $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["rawat_catatan"]);
          // //$dbValue[6] = QuoteValue(DPE_DATE,date_db($_POST["rawat_tanggal"]));
          // $dbValue[5] = QuoteValue(DPE_CHAR,'M'); 
          // $dbValue[6] = QuoteValue(DPE_CHAR,'RAWAT JALAN'); 
          // $dbValue[7] = QuoteValue(DPE_CHAR,$poliId); 
          // $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
          // $dbValue[9] = QuoteValue(DPE_CHAR,$userData["name"]);
          // $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["rawat_terapi"]);
          // $dbValue[11] = QuoteValue(DPE_CHAR,$hasil);                                     
          // $dbValue[12] = QuoteValue(DPE_CHAR,$_POST["pasien_diagnosa_utama"]);
          // $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["pasien_diagnosa_kedua"]);
          // $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["pasien_pemeriksaan_lain"]);
          // $dbValue[15] = QuoteValue(DPE_CHAR,$_POST["id_dokter_1"]);
          // $dbValue[16] = QuoteValue(DPE_CHAR,$_POST["pasien_tindakan"]);          
          // $dbValue[17] = QuoteValue(DPE_DATE,$time);
          // $dbValue[18] = QuoteValue(DPE_CHAR,$_POST["anamnesa"]);  
          // $dbValue[19] = QuoteValue(DPE_DATE,date_db($_POST["perawatan_tanggal_partus"])); 
          // $dbValue[20] = QuoteValue(DPE_DATE,date_db($_POST["perawatan_tanggal_kuret"]));



          // $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          // $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
           
          //  if($dataIdRawat) $dtmodel->Update() or die("update  error"); 
          // else $dtmodel->Insert() or die("insert  error");  

          // unset($dtmodel);
          // unset($dbTable);
          // unset($dbField);
          // unset($dbValue);
          // unset($dbKey);

      
               $sql = "update klinik.klinik_registrasi set reg_keterangan='".$_POST["reg_keterangan"]."', reg_tujuan_rujukan='".$_POST["reg_tujuan_rujukan"]."', reg_keadaan_keluar_inap='".$_POST["id_keadaan_keluar_inap"]."' , reg_icd='y'
                        where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
               
               $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      
        //END INSERT ke Rekam Medik
      
$sql="update global.global_customer_user set cust_usr_tanggal_lahir=".QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"])).", cust_usr_no_jaminan=".QuoteValue(DPE_CHAR,$_POST["cust_usr_no_jaminan"]).", cust_usr_no_sep=".QuoteValue(DPE_CHAR,$_POST['reg_no_sep']).
", cust_usr_gol_darah = ".QuoteValue(DPE_CHAR,$_POST['cust_usr_gol_darah']).", cust_usr_alergi=".QuoteValue(DPE_CHAR,$_POST['cust_usr_alergi'])." where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
$dtaccess->Execute($sql);
// echo $sql."<br>";
$sql="select * from global.global_auth_user where usr_name = ".QuoteValue(DPE_CHAR,$_POST["dpjp"]);
$rs = $dtaccess->Execute($sql);
$datausr = $dtaccess->Fetch($rs); 
//$_POST["usr_name"]   =  $datausr["usr_name"];           
//echo $sql."<br>";
$sql="update klinik.klinik_registrasi set id_dokter =".QuoteValue(DPE_CHAR,$datausr["usr_id"])." , reg_no_sep=".QuoteValue(DPE_CHAR,$_POST['reg_no_sep'])." where reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
//echo $sql; die() ;
$dtaccess->Execute($sql);
//echo $sql."<br>";
$sql="update klinik.klinik_inacbg set inacbg_dokter =".QuoteValue(DPE_CHAR,$_POST["dpjp"])." where id_reg=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
//echo $sql; //die() ;
$dtaccess->Execute($sql);

$sql="update klinik.klinik_sep set no_sep=".QuoteValue(DPE_CHAR,$_POST['reg_no_sep'])." where sep_reg_id=".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
$dtaccess->Execute($sql);


$regId = $_POST["reg_id"];
//cari data pasien nya dulu
$sql = "select id_cust_usr from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$regId);
$rs = $dtaccess->Execute($sql);
$dataCust = $dtaccess->Fetch($rs);

//update data yang harus disimpan
//customer_user
$sql = "update global.global_customer_user set cust_usr_no_sep= ".QuoteValue(DPE_CHAR,$_POST['reg_no_sep']).",
        cust_usr_no_jaminan = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_no_jaminan"]).",
        cust_berat_lahir = ".QuoteValue(DPE_NUMERIC,$_POST["berat_lahir"])."
        where cust_usr_id = ".QuoteValue(DPE_CHAR,$dataCust["id_cust_usr"]);
$rs = $dtaccess->Execute($sql);
//echo $sql."<br>";
//registrasi
$sql = "update klinik.klinik_registrasi set 
        hak_kelas_inap = ".QuoteValue(DPE_CHAR,$_POST["kls_perawatan"]).",
        reg_no_sep = ".QuoteValue(DPE_CHAR,strtoupper($_POST["reg_no_sep"])).",
        reg_tanggal_pulang = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_pulang"])).",
        reg_tanggal = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_masuk"])).",
        reg_keluar_inap = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_keluar"])).",
        reg_waktu_pulang = ".QuoteValue(DPE_CHAR,$_POST["reg_waktu_pulang"])." 
        where reg_id = ".QuoteValue(DPE_CHAR,$regId);
$rs = $dtaccess->Execute($sql);
//echo $sql;
//die();
//rawatinap
$sql = "update klinik.klinik_rawatinap set rawatinap_tanggal_masuk = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_masuk"])).",
        rawatinap_tanggal_keluar = ".QuoteValue(DPE_DATE,date_db($_POST["tgl_keluar"]))."
        where id_reg = ".QuoteValue(DPE_DATE,$regId);
$rs = $dtaccess->Execute($sql);
//echo $sql."<br>";die();

$sql="update global.global_customer_user set 
cust_usr_tanggal_lahir=".QuoteValue(DPE_DATE,date_db($_POST["cust_usr_tanggal_lahir"])).", 
cust_usr_no_jaminan=".QuoteValue(DPE_CHAR,$_POST["cust_usr_no_jaminan"]).", 
cust_usr_no_sep=".QuoteValue(DPE_CHAR,$_POST['reg_no_sep'])." where cust_usr_id=".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"]);
$dtaccess->Execute($sql);
// echo $sql."<br>";
$sql="select * from global.global_auth_user where usr_name = ".QuoteValue(DPE_CHAR,$_POST["dpjp"]);
$rs = $dtaccess->Execute($sql);
$datausr = $dtaccess->Fetch($rs); 
//$_POST["usr_name"]   =  $datausr["usr_name"];           
//echo $sql."<br>";
$sql="update klinik.klinik_registrasi set id_dokter =".QuoteValue(DPE_CHAR,$datausr["usr_id"])." , 
reg_no_sep=".QuoteValue(DPE_CHAR,$_POST['reg_no_sep'])." 
where reg_id=".QuoteValue(DPE_CHAR,$regId);
//echo $sql;
$dtaccess->Execute($sql);

          $kembali = "kasir_pemeriksaan_view.php";

           //print_r($_POST);
       //   $cetakklaimlink = "<script>window.open('http://".$wsinacbgserver."/ws_inacbg/cetak_klaim.php?sep=".$no_sep."&usernik=".$usernik."&id_reg=".$_POST["id_reg"]."')</script>";           
       //   $cetakklaim="yes";
          
      header("location:".$kembali);
      exit();
      // if(!$_POST["btnTambah"]){
      // $simpan="yes";    
      // }
         }
         
         if($_POST["btnLihat"]){
            $cetak="y";
         }
         
  
    // menampilkan data pemeriksaan baru waktu di klik tombol detail
  if($_GET["id_reg"] || $_GET["id_inacbg"]) {
     
     $sql = "select b.*, d.jadwal_ket,a.id_poli as poli,a.id_dokter as dokter, a.reg_no_antrian, a.reg_id, a.reg_waktu_pulang,
              c.jam_nama,a.reg_keterangan,f.usr_name as dokter_nama, a.reg_no_sep, a.reg_tipe_rawat, a.reg_kelas, h.kelas_nama_bpjs, h.kelas_nama,  a.reg_tanggal_pulang, b.cust_usr_jenis_kelamin,a.reg_jenis_pasien,a.reg_tanggal, a.reg_waktu, b.cust_berat_lahir,b.cust_usr_no_jaminan,
                b.cust_usr_jenis, e.*, g.*, a.id_pembayaran, cust_usr_tanggal_lahir, a.id_cust_usr, a.reg_who_update, a.reg_periksa_gratis, a.reg_cara_keluar_inap,
                b.cust_usr_alamat, cust_usr_no_hp, g.inacbg_dokter, ((current_date - cust_usr_tanggal_lahir)/365) as umur,inacbg_kode, x.*
                from klinik.klinik_registrasi a
                left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
                left join global.global_jam c on c.jam_id = a.id_jam 
                left join klinik.klinik_jadwal d on d.id_reg = a.reg_id  
                left join klinik.klinik_perawatan e on e.id_reg = a.reg_id  
                left join global.global_auth_user f on f.usr_id = a.id_dokter
                left join klinik.klinik_inacbg g on g.id_reg=a.reg_id
                left join klinik.klinik_kelas h on a.reg_kelas = h.kelas_id
                left join klinik.klinik_perawatan_imunisasi x on x.id_reg=a.reg_id         
                where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." order by reg_tanggal_pulang desc, reg_tanggal desc";
    //echo $sql;
    $dataPasien= $dtaccess->Fetch($sql);  
    $_POST["cust_usr_alergi"] = $dataPasien["cust_usr_alergi"]; 
    $_POST["cust_usr_jenis"] = $dataPasien["cust_usr_jenis"]; 
    $_POST["id_poli"] = $dataPasien["poli"]; 
    $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"]; 
    $_POST["cust_usr_nama"] = $dataPasien["cust_usr_nama"]; 
    $_POST["cust_usr_jenis_kelamin"] = $dataPasien["cust_usr_jenis_kelamin"]; 
    $_POST["cust_usr_alamat"] = $dataPasien["cust_usr_alamat"]; 
    $_POST["cust_usr_no_hp"] = $dataPasien["cust_usr_no_hp"]; 
    $_POST["id_reg"] = $dataPasien["reg_id"]; 
    $_POST["id_pembayaran"] = $dataPasien["id_pembayaran"];
    $_POST["reg_berat_lahir"] = $dataPasien["cust_berat_lahir"];
    
    if($_GET["id_inacbg"] ) 
    {
    $_POST["cara_keluar"] = $dataPasien["inacbg_cara_keluar"];
    //$_POST["dpjp"] = $_POST["dpjp"];
   // if ($dataPasien["inacbg_dokter"] == '')
    //{
    //$_POST["datadokter"] = $dataPasien["dokter_nama"];
    //}else {
    //if ($dataPasien["inacbg_dokter"] == $dataPasien["dokter_nama"]){
    $_POST["datadokter"] = $dataPasien["inacbg_dokter"]; 
    $_POST["dpjp"] = $dataPasien["inacbg_dokter"]; 
     $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];      
    //} else {    
    //$_POST["datadokter"] = $dataPasien["inacbg_dokter"]; }
    //}
    }
    else {
    $_POST["dpjp"] = $_POST["dpjp"]; 
    $_POST["datadokter"] = $dataPasien["dokter_nama"];
    }
    $_POST["id_dokter"] = $dataPasien["dokter"];
    $_POST["id_dokter_1"] = $dataPasien["dokter"];
    $_POST["id_cust_usr"] = $dataPasien["cust_usr_id"];    
    $_POST["reg_who_update"] = $dataPasien["reg_who_update"];
    $_POST["reg_jenis_pasien"] = $dataPasien["reg_jenis_pasien"];
    // if (!$_POST["btnBridge"]) {
      $_POST["reg_tanggal"] = $dataPasien["reg_tanggal"];
      $_POST["reg_waktu"] = $dataPasien["reg_waktu"];
    // }
    $_POST["cust_usr_jenis"] = $dataPasien["cust_usr_jenis"];
    $_POST["cust_usr_nama_pasien"] = $dataPasien["cust_usr_nama"];
    $namaPasien = substr($dataPasien["cust_usr_nama"], 0, 16);
    $_POST["jam_nama"] = substr($dataPasien["jam_nama"], 0, 5);
    $_POST["cust_usr_alamat_pasien"] = $dataPasien["cust_usr_alamat"];
    $_POST["cust_usr_tanggal_lahir"] = $dataPasien["cust_usr_tanggal_lahir"];
    $_POST["reg_tipe_rawat"] = $dataPasien["reg_tipe_rawat"];
    $_POST["umur"] = $dataPasien["umur"];
    $_POST["reg_no_antrian"] = $dataPasien["reg_no_antrian"];
    $_POST["reg_keterangan"] = $dataPasien["reg_keterangan"];
    $_POST["reg_periksa_gratis"] = $dataPasien["reg_periksa_gratis"];
    $_POST["cust_usr_foto"] = $dataPasien["cust_usr_foto"];
    $_POST["jadwal_ket"] = $dataPasien["jadwal_ket"];
    $_POST["rawat_anamnesa"] = $dataPasien["rawat_anamnesa"];
    $_POST["rawat_pemeriksaan_fisik"] = $dataPasien["rawat_pemeriksaan_fisik"];
    $_POST["rawat_penunjang"] = $dataPasien["rawat_penunjang"];
    $_POST["rawat_diagnosa_utama"] = $dataPasien["rawat_diagnosa_utama"];
    $_POST["rawat_diagnosa_kedua"] = $dataPasien["rawat_diagnosa_kedua"];
    $_POST["rawat_rujuk_ke_ruang"] = $dataPasien["rawat_rujuk_ke_ruang"];
    $_POST["rawat_pemeriksaan_lain"] = $dataPasien["rawat_pemeriksaan_lain"];
    $_POST["rawat_dokter"] = $dataPasien["rawat_dokter"];
    $_POST["rawat_tindakan"] = $dataPasien["rawat_tindakan"];
    $_POST["rawat_id"] = $dataPasien["rawat_id"];  
    $_POST["pasien_anamnesa"] = $dataPasien["rawat_anamnesa"];
    $_POST["pasien_pemeriksaan_fisik"] = $dataPasien["rawat_pemeriksaan_fisik"];
    $_POST["pasien_penunjang"] = $dataPasien["rawat_penunjang"];
    $_POST["inacbg_morfologi1"] = $dataPasien["inacbg_morfologi1"];
    $_POST["inacbg_morfologi2"] = $dataPasien["inacbg_morfologi2"];
    $_POST["inacbg_ext_cause1"] = $dataPasien["inacbg_ext_cause1"];
    $_POST["inacbg_ext_cause2"] = $dataPasien["inacbg_ext_cause2"];
    if($_GET["id_inacbg"]) $_POST["srt_rujukan"] = $dataPasien["inacbg_surat_rujukan"];
    $_POST["inacbg_kode"] = $dataPasien["inacbg_kode"];
    $_POST["inacbg_dijamin"] = $dataPasien["inacbg_dijamin"];
    $_POST["cust_usr_gol_darah"] = $dataPasien["cust_usr_gol_darah"];
    $_POST["isi_imun"] = $dataPasien["isi_imun"];
    $_POST["edit"] = 1;
    // if (!$_POST["btnBridge"]) {
      $_POST["reg_tanggal_pulang"] = $dataPasien["reg_tanggal_pulang"];
      $_POST["reg_waktu_pulang"] = $dataPasien["reg_waktu_pulang"];
    // }
    //untuk data bridging
    $_POST["user_nm"]='NCC';
    $_POST["user_pw"]='NCC';
    $_POST["cust_usr_no_jaminan"] = $dataPasien["cust_usr_no_jaminan"];
    $_POST["dokter_nama"] = $dataPasien["dokter_nama"];
    
    $sql = "select no_sep from klinik.klinik_sep a where sep_reg_id =".QuoteValue(DPE_CHAR,$dataPasien["reg_id"]);
    $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
    $dataSepPasien = $dtaccess->Fetch($rs);
    $_POST['reg_no_sep'] = $dataSepPasien["no_sep"];
    
    //kalau disimpan di reg_no_sep maka ambil dari registrasi
    if (!$_POST['reg_no_sep']) $_POST['reg_no_sep'] = $dataPasien["reg_no_sep"];
    

//JENIS PERAWATAN
        if ($dataPasien["reg_tipe_rawat"]=='J' or $dataPasien["reg_tipe_rawat"]=='G')
        {
          $_POST["jns_perawatan"]='2'; //RAWAT JALAN
          $_POST["jns_perawatan_nama"]='RAWAT JALAN';
          $_POST["kls_perawatan_nama"]='-';  
          $_POST["kls_perawatan"]='3';
          /*$_POST["tgl_keluar"]=$dataPasien["reg_tanggal"]; 
          $_POST["tgl_pulang"]=$dataPasien["reg_tanggal"]; 
          $_POST["waktu_pulang"]=$dataPasien["reg_waktu"];   */   
        
        }
        else
        {
          $_POST["jns_perawatan"]='1'; //RAWAT INAP
          $_POST["jns_perawatan_nama"]='RAWAT INAP';              
          $_POST["kls_perawatan_nama"]=$dataPasien["kelas_nama"];  
          $_POST["kls_perawatan"]=$dataPasien["kelas_nama_bpjs"];                 
        
        }
     // cari jenis pasien ee --
     $sql = "select a.* from global.global_jenis_pasien a where jenis_id =".$_POST["reg_jenis_pasien"]." order by a.jenis_id asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataJnsPasien = $dtaccess->Fetch($rs);
     
     // cari biaya pembayarannnya //
     $sql = "select a.* from klinik.klinik_registrasi a where reg_id =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPembayaran = $dtaccess->Fetch($rs);
     
     // siapa yg update //
     $sql = "select a.* from global.global_auth_user a where usr_name =".QuoteValue(DPE_CHAR,$_POST["reg_who_update"]);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $whoIsUpdate = $dtaccess->Fetch($rs); 


     $sql = "select sum(fol_nominal) as jumlah_nominal from klinik.klinik_folio 
             where id_pembayaran =".QuoteValue(DPE_CHAR,$dataPembayaran["id_pembayaran"]);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataBayar = $dtaccess->Fetch($rs);
  



      
    }
         
       

       
        //-----konfigurasi-----//
        $sql = "select * from global.global_departemen";
        $sql .= " where dep_id = ".QuoteValue(DPE_CHAR,$depId);
        $rs = $dtaccess->Execute($sql);
        $konfigurasi = $dtaccess->Fetch($rs);                
     
        // cari tgl registrasi
        $sql = "SELECT * FROM klinik.klinik_registrasi WHERE id_cust_usr = ".QuoteValue(DPE_CHAR,$_POST["id_cust_usr"])." order by reg_when_update desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataTglRegistrasi = $dtaccess->FetchAll($rs);
          
     // -- cari dokter -- //
     $sql = "select usr_id, usr_name from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep =".QuoteValue(DPE_CHAR,$depId)." order by usr_name";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataDokter = $dtaccess->FetchAll($rs);
          
     // -- cari bidan -- //
     $sql = "select usr_id, usr_name from global.global_auth_user where id_rol = 9 and id_dep =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataBidan = $dtaccess->FetchAll($rs);
     
     // -- cari suster -- //
     $sql = "select usr_id, usr_name from global.global_auth_user where id_rol = 6 and id_dep =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_HRIS);
     $dataPerawat = $dtaccess->FetchAll($rs);
     
      // cari jenis pasien e
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs);
     
          // --- cari poli ---
     $sql = "select poli_nama,poli_id, id_biaya from global.global_auth_poli where poli_id > '0' order by poli_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataPoli = $dtaccess->FetchAll($rs);

    
    
     
          if(!$_POST["rawat_tanggal"]) $_POST["rawat_tanggal"] = date("d-m-Y");
          if(!$_POST["jam"]) $_POST["jam"] = date("H");
          if(!$_POST["menit"]) $_POST["menit"] = date("i");
          if(!$_POST["detik"]) $_POST["detik"] = date("s");
  
          if($_POST["cust_usr_jenis_kelamin"]=='P')$gender="2";          
          if($_POST["cust_usr_jenis_kelamin"]=='L')$gender="1";
          if(!$_POST["usernik"])$_POST["usernik"]="123123123123";
          if(!$_POST["payorcode"])$_POST["payorcode"]="JKN";
          if(!$_POST["payorid"])$_POST["payorid"]="3";

          
   $sql = "select a.* from klinik.klinik_perawatan_icd a left join klinik.klinik_perawatan b on b.rawat_id = a.id_rawat where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id_reg'])." order by rawat_icd_status asc";
   $diagnosa = $dtaccess->FetchAll($sql); 
   for ($x=0; $x < count($diagnosa); $x++) { 
     $dataDiagnosa[$x] = $diagnosa[$x]['rawat_icd_kode']." ".$diagnosa[$x]['rawat_icd9_tindakan_nama']." (".$diagnosa[$x]['rawat_icd_status'].");";
   }

   $sql = "select a.* from klinik.klinik_perawatan_icd9 a left join klinik.klinik_perawatan b on b.rawat_id = a.id_rawat where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
   $procedure = $dtaccess->FetchAll($sql);
   for ($y=0; $y < count($procedure); $y++) { 
     $dataProcedure[$y] = $procedure[$y]['rawat_icd9_kode']." ".$procedure[$y]['rawat_icd9_keterangan'].";";
   }
   //echo $sql;
          
             

          
         
          if($_POST["jns_perawatan"]=='2'){ $tgl_masuk = $_POST["reg_tanggal"]; $tgl_keluar=$tgl_masuk;}
          
          //keperluan bridging 5.2
          $tgl_masuk .= " ".$_POST["reg_waktu"];
          $tgl_keluar .= " ".$_POST["reg_waktu"];
           
     //echo "Masuk ".$tgl_masuk."<br>";
       //echo "Keluar ".$tgl_keluar;  
//cari sudah pernah dibridge atau belum
          $sql =" select inacbg_kode from klinik.klinik_inacbg where inacbg_id = ".QuoteValue(DPE_CHAR,$_GET["id_inacbg"]);
          $rs = $dtaccess->Execute($sql);
          $kodecbg = $dtaccess->Fetch($rs);
         // echo $sql;
         //  die();
                    //Data Kelas
          $sql = "select * from klinik.klinik_kelas";
          $dataKelas = $dtaccess->FetchAll($sql);
           








    
if($_POST["btnCetakClaim"]){
echo $cetakklaimlink;
}

 $tableHeader = "&nbsp;Edit Diagnosa";
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <script type="text/javascript">
  //$(function(){
    var dgicd = $('#tb_diagnosa').edatagrid();
    var dgicd9 = $('#tb_procedure').edatagrid();
    var dgdiagnose = $('#tb_diagnose').edatagrid();
    var dgprocedures = $('#tb_procedures').edatagrid();

    dgicd.edatagrid({
      saveUrl: 'simpan_diagnosa.php',
    });

    dgicd9.edatagrid({
      saveUrl: 'simpan_procedure.php',
    });

    dgdiagnose.edatagrid({
      saveUrl: 'simpan_diagnose.php',
    });

    dgprocedures.edatagrid({
      saveUrl: 'simpan_procedures.php',
    });

  //});
  </script>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        
    <?php require_once($LAY."sidebar.php"); ?>

        <!-- top navigation -->
    <?php require_once($LAY."topnav.php"); ?>
    <!-- /top navigation -->
<head>


<script type="text/javascript">
function ChangeDisplay(id) {
     var disp = Array();
     
     disp['none'] = 'block';
     disp['block'] = 'none';
     
     document.getElementById(id).style.display = disp[document.getElementById(id).style.display];
}

function CekBridging(frm) {

    if(!document.getElementById('norm').value)
    {
      alert('Maaf No RM Harus Diisi');
      document.getElementById('norm').focus();
      return false;
    }  
     if(!document.getElementById('nm_pasien').value)
    {
      alert('Maaf Nama Pasien Harus Diisi');
      document.getElementById('nm_pasien').focus();
      return false;
    }
    if(!document.getElementById('jns_kelamin').value)
    {
      alert('Maaf Jenis Kelamin Harus Diisi');
      document.getElementById('jns_kelamin').focus();
      return false;
    }  
     if(!document.getElementById('cust_usr_tanggal_lahir').value)
    {
      alert('Maaf Tanggal Lahir Harus Diisi');
      document.getElementById('cust_usr_tanggal_lahir').focus();
      return false;
    }
    if(!document.getElementById('jns_pbyrn').value)
    {
      alert('Maaf Jenis Pembayaran Harus Diisi');
      document.getElementById('jns_pbyrn').focus();
      return false;
    }  

     if(!document.getElementById('jns_perawatan').value)
    {
      alert('Maaf Jenis Perawatan Harus Diisi');
      document.getElementById('jns_perawatan').focus();
      return false;
    }
    if(!document.getElementById('kls_perawatan').value)
    {
      alert('Maaf Kelas Perawatan Harus Diisi');
      document.getElementById('kls_perawatan').focus();
      return false;
    }  
     if(!document.getElementById('tgl_masuk').value)
    {
      alert('Maaf Tanggal Masuk Harus Diisi');
      document.getElementById('tgl_masuk').focus();
      return false;
    }
    if(!document.getElementById('tgl_keluar').value)
    {
      alert('Maaf Tanggal Keluar Harus Diisi');
      document.getElementById('tgl_keluar').focus();
      return false;
    }  
     if(!document.getElementById('cara_keluar').value)
    {
      alert('Maaf Cara Keluar Harus Diisi');
      document.getElementById('cara_keluar').focus();
      return false;
    }
    if(!document.getElementById('dpjp').value)
    {
      alert('Maaf Dokter Penanggungjawab Harus Diisi');
      document.getElementById('dpjp').focus();
      return false;
    }  
     if(!document.getElementById('tarif_rs').value)
    {
      alert('Maaf Tarif Rumah Sakit Harus Diisi');
      document.getElementById('tarif_rs').focus();
      return false;
    }
    if(document.getElementById('srt_rujukan').value == 0)
    {
      alert('Maaf Surat Rujukan Harus Ada');
      document.getElementById('srt_rujukan').focus();
      return false;
    }
    if(!document.getElementById('CityAjax').value)
    {
      alert('Maaf Diagnosa Utama Harus Diisi');
      document.getElementById('CityAjax').focus();
      return false;
    }

      return true;      
}

function CheckDataSave(frm)
{   
  <?php if(!$_GET["id"]) { ?>
   
    if(!frm.id_reg.value){
    alert('Maaf, Anda belum memasukkan pasien');
    return false;
    }
      
  <?php } ?>    
}

  
 var _wnd_new;
  function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=10');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=10');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
} 

function validate(){
  alert("Maaf, Anda belum memasukkan pasien");
  return false;
}  


  function BukaWindow(url,judul)
{
    if(!_wnd_new) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
    } else {
      _wnd_new.focus();
    }
  }
     return false;
}

<?php if($bridgedata=="yes"){?>
      document.location.href='<?php echo $backPage;?>';
<?php } ?>
 
<?php if($cetak=="y"){?>
      BukaWindow('<?php echo $ROOT;?>kassa/module/kasir_irj/kasir_irj/kasir_cetak_sementara.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>','Kwitansi');
      document.location.href='<?php echo $thisPage;?>';
<?php } ?>

function CaptureEvent(evt){
    var keyCode = document.layers ? evt.which : document.all ? evt.keyCode : evt.keyCode;       
    
    if(keyCode==113) {  // -- f2 buat fokus ke tipe transaksi ---
      if (confirm('Apakah Anda yakin akan menyimpan data perawatan tersebut?')==1)
      {
           document.getElementById('hidSave').value = '1';
           document.frmEdit.submit();
       }
       
      // document.location.href='edit_input_rm.php';
    }

    
    return false;
}  

<?php if($cetakklaim=="yes"){?>
      BukaWindow('http://<? echo $wsinacbgserver;?>/muslimat_his/production/ws_inacbg/cetak_klaim.php?sep=<? echo $no_sep;?>&usernik=<? echo $usernik;?>&id_reg=<? echo $_POST["id_reg"];?>','Kwitansi');
      document.location.href='input_rm.php';
<?php } ?>

 
</script>  
</head>

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Rekam Medik - <? echo $tableHeader;?></h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="form-horizontal form-label-left col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>                  
                  <div class="x_content">                  
                  
                  <form id="frmEdit" class="form-horizontal form-label-left" name="frmEdit" method="POST" autocomplete="off" action="<?php echo $thisPage;?>" enctype="multipart/form-data" onSubmit="return CheckDataSave(this)">                   
<table border="0" width="100%">
<tr>
    <?php if($simpan=="yes"){
     ?>
  		<script type="text/javascript">
  			window.location=window.location.href;
  		</script>
    



  <tr><td align="center"><font color="red"><h2> Data Telah Tersimpan</h2></font></td></tr>
<? } ?>
    <td align="right">   

           
             
    </td>
</tr> 
</table>
<div class="col-md-6 col-sm-6 col-xs-6">
          <div class="form-group">
            <input type="hidden" name="rawat_id" value="<?php echo $dataPasien['rawat_id'] ?>">
            <label class="control-label col-md-3 col-sm-3 col-xs-6">No RM</label>
            <div class="col-md-9 col-sm-9 col-xs-6">
            <input type="text" id="cust_usr_kode" name="cust_usr_kode" readonly value="<?php echo substr($_POST["cust_usr_kode"], 2) ; ?>" required="required" class="form-control col-md-7 col-xs-12">
           <!--  <input type="hidden" name="rawat_id" id="rawat_id" value="<?php echo $_POST['rawat_id'] ?>"> -->
            <input type="hidden" name="reg_id" id="reg_id" value="<?php echo $_GET['id_reg'] ?>">
            <input type="hidden" name="reg_tipe_rawat" id="reg_tipe_rawat" value="<?php echo $_POST['reg_tipe_rawat'] ?>">
            </div>
          </div>
                      
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-6">Nama Lengkap</label>
            <div class="col-md-9 col-sm-9 col-xs-6">
            <input type="text" id="cust_usr_nama_txt" name="cust_usr_nama_txt" readonly value="<?php echo $_POST["cust_usr_nama"]." / ".$_POST["tahun"]." Tahun"; ?>" required="required" class="form-control col-md-7 col-xs-12">
            <input type="hidden" id="cust_usr_nama" name="cust_usr_nama"  value="<?php echo $_POST["cust_usr_nama"]; ?>" >
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="cust_usr_jenis_kelamin_nama" name="cust_usr_jenis_kelamin_nama" readonly value="<?php echo $jenisKelamin[$_POST["cust_usr_jenis_kelamin"]]; ?>" required="required" class="form-control col-md-7 col-xs-12">
            <input type="hidden" id="cust_usr_jenis_kelamin" name="cust_usr_jenis_kelamin"  value="<?php echo $_POST["cust_usr_jenis_kelamin"]; ?>" >
            </div>
          </div>
          </div> <!-- div kiri -->                     
                  <div class="col-md-6 col-sm-6 col-xs-6">
                      <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tanggal Lahir  <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12"> 
              <input type="text" id="cust_usr_tanggal_lahir"  name="cust_usr_tanggal_lahir" value="<?php echo format_date($_POST["cust_usr_tanggal_lahir"]);?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
              </div>
            </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Alamat <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_alamat" name="cust_usr_alamat" value="<?php echo $_POST["cust_usr_alamat"]; ?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">No HP <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_no_hp_txt" name="cust_usr_no_hp_txt" readonly value="<?php echo $_POST["cust_usr_no_hp"]; ?>" required="required" class="form-control col-md-7 col-xs-12">
                          <input type="hidden" id="cust_usr_no_hp" name="cust_usr_no_hp"  value="<?php echo $_POST["cust_usr_no_hp"]; ?>" >
                        </div>
                      </div> 
                     </div> <!-- DIV END AKHIR KANAN -->
        </div>
      </div>
    </div> <!-- div akhir untuk data pasien --> 

<!-- div awal untuk transaksi perawatan pasien --> 
      <div class="form-horizontal form-label-left col-md-12 col-sm-12 col-xs-12">    
          <div class="x_panel">
              <div class="x_title">
                  <h2>Data Transaksi Perawatan</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                  </ul>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>                  
            <div class="x_content"> 

            <div class="col-md-6 col-sm-6 col-xs-6"> 
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Jenis Perawatan  <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12"> 
                  <input type="text" readonly id="jns_perawatan_nama"  name="jns_perawatan_nama" value="<?php echo $_POST["jns_perawatan_nama"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                  <input type="hidden" id="jns_perawatan"  name="jns_perawatan" value="<?php echo $_POST["jns_perawatan"];?>" />
                </div>
                </div>

                <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Kelas Perawatan  <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12"> 
              <?php if ($_POST["jns_perawatan"]=='2') { //JIKA RAWAT JALAN KELAS DIPATEN?>
                <input type="text" readonly id="kls_perawatan_nama"  name="kls_perawatan_nama" value="<?php echo $_POST["kls_perawatan_nama"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                <input type="hidden" id="kls_perawatan"  name="kls_perawatan" value="<?php echo $_POST["kls_perawatan"];?>" />
              <? } else { ?>
                      <select name="kls_perawatan" class="form-control" id="kls_perawatan" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                        <?php for($i=0,$n=count($dataKelas);$i<$n;$i++){ ?>
                          <option value="<?php echo $dataKelas[$i]["kelas_id"];?>" <?php if($dataKelas[$i]["kelas_nama_bpjs"]==$_POST["kls_perawatan"]) echo "selected"; ?> ><?php echo $dataKelas[$i]["kelas_nama"];?></option>
                        <?php } ?>
                        </select>            
              <? } ?>
              </div>
            </div>

          <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Klinik <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                <select name="id_poli" class="form-control" id="id_poli" disabled onKeyDown="return tabOnEnter(this, event);">      
                <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataPoli[$i]["poli_nama"];?></option>
                <?php } ?>
                </select>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Dokter <span class="required">*</span>
            </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
              <select name="id_dokter" id="id_dokter" class="form-control" onKeyDown="return tabOnEnter(this, event);">      
                  <option value="">-Pilih Dokter-</option>
                  <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>  
                  
                    <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php                      
                     if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected"; ?>><?php echo ($i+1).". ".$dataDokter[$i]["usr_name"];?></option>
                  <?php } ?>
             </select>
            </div>
          </div>  
            <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tarif RS<span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" id="tarif_rs_txt" name="tarif_rs_txt" readonly class="form-control col-md-7 col-xs-12" value="<?php echo currency_format(round($dataBayar["jumlah_nominal"]));?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                    <input type="hidden" id="tarif_rs"  name="tarif_rs" value="<?php echo round($dataBayar["jumlah_nominal"]+$JasaRS);?>" />
                    <!--<input type="submit" name="btnLihat" id="btnLihat" class="submit" value="Rincian">  -->
                  </div>
            </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Jenis Tarif<span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
            <select disabled="disabled" name="kodetarif" id="kodetarif">
              <option <?php if ($_POST["kodetarif"]=='AP') echo "selected";?> value="AP">TARIF RS KELAS A PEMERINTAH</option>
              <option <?php if ($_POST["kodetarif"]=='AS') echo "selected";?> value="AS">TARIF RS KELAS A SWASTA</option>
              <option <?php if ($_POST["kodetarif"]=='BP') echo "selected";?> value="BP">TARIF RS KELAS B PEMERINTAH</option>
              <option <?php if ($_POST["kodetarif"]=='BS') echo "selected";?> value="BS">TARIF RS KELAS B SWASTA</option>
              <option <?php if ($_POST["kodetarif"]=='CP') echo "selected";?> value="CP">TARIF RS KELAS C PEMERINTAH</option>
              <option <?php if ($_POST["kodetarif"]=='CS') echo "selected";?> value="CS">TARIF RS KELAS C SWASTA</option>
              <option <?php if ($_POST["kodetarif"]=='DP') echo "selected";?> value="DP">TARIF RS KELAS D PEMERINTAH</option>
              <option <?php if ($_POST["kodetarif"]=='DS') echo "selected";?> value="DS">TARIF RS KELAS D SWASTA</option>
              <option <?php if ($_POST["kodetarif"]=='RSCM') echo "selected";?> value="RSCM">TARIF RSUPN CIPTO MANGUNKUSUMO</option>
              <option <?php if ($_POST["kodetarif"]=='RSJP') echo "selected";?> value="RSJP">TARIF RSJPD HARAPAN KITA</option>
              <option <?php if ($_POST["kodetarif"]=='RSD') echo "selected";?> value="RSD">TARIF RS KANKER DHARMAIS</option>
              <option <?php if ($_POST["kodetarif"]=='RSAB') echo "selected";?> value="RSAB">TARIF RSAB HARAPAN KITA</option>
           </select>        
            </div>
           </div> 
            
            <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Cara bayar <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="reg_jenis_pasien" class="form-control" id="reg_jenis_pasien" disabled onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
              <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
              <?php } ?>
              </select>
              </div>
          </div>         
          <div class="form-group">
               <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">No. Peserta  <? if($_POST["reg_jenis_pasien"]=='5'){ ?><span class="required">*</span><? }?>
               </label>
               <div class="col-md-6 col-sm-6 col-xs-12"> 
                <input type="text" id="cust_usr_no_jaminan"  name="cust_usr_no_jaminan" value="<?php echo $_POST["cust_usr_no_jaminan"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
              </div>   
            </div>
              <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">No. SEP  <? if($_POST["reg_jenis_pasien"]=='5'){ ?><span class="required">*</span><? }?>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12"> 
              <input type="text" id="reg_no_sep"  name="reg_no_sep" value="<?php echo strtoupper($_POST["reg_no_sep"]);?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
              </div>
            </div>
          </div>  <!-- DIV Akhir Kiri>   --> 

          <div class="col-md-6 col-sm-6 col-xs-6">         
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tanggal Masuk  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                           <input type="text" id="tgl_masuk"  name="tgl_masuk" value="<?php echo format_date($_POST["reg_tanggal"]);?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                        </div>
                  </div>
                  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Waktu Masuk  <span class="required" >*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                          <input type="text" id="waktu_masuk"  name="waktu_masuk" value="<?php echo $_POST["reg_waktu"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                        </div>
                    </div>                                                                                                                                                                                                        
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tanggal Pulang <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="tgl_pulang"  name="tgl_pulang" value="<?php echo format_date($_POST["reg_tanggal_pulang"]);?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                      <input type="hidden" id="tgl_keluar"  name="tgl_keluar" value="<?php $_POST["reg_tanggal_pulang"];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Waktu Pulang  <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                          <input type="text" id="reg_waktu_pulang"  name="reg_waktu_pulang" value="<?php echo $_POST["reg_waktu_pulang"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                        </div>
                    </div> 
                    
                    <?php

                      $tgl1 = new DateTime($_POST["reg_tanggal"]);
                      $tgl2 = new DateTime($_POST["reg_tanggal_pulang"]);
                      $tanggal = $tgl2->diff($tgl1)->days + 1;
                    
                    ?>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Lama Hari Rawat <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                          <input type="text" id=""  name="" value="<?php echo $tanggal;?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                        </div>
                    </div> 
                    
                      
                       <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Jenis Pembayaran  <span class="required">*</span>
                        </label>
                        <?php

                          if($_POST["reg_jenis_pasien"]=='2'){
                            $jenis_px="Umum";
                          }
                          elseif($_POST["reg_jenis_pasien"]=='5'){
                            $jenis_px="JKN/KIS";
                          }

                          elseif($_POST["reg_jenis_pasien"]=='7'){
                            $jenis_px="Asuransi";
                          }



                        ?>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                        <input type="text" id="jns_pbyrn"  name="jns_pbyrn" value="<?php echo $jenis_px;?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                  </div>
                      </div>
                      
                       
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Cara Keluar <span class="required"></span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="form-control" name="cara_keluar" id="cara_keluar" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                  <?php for($i=0,$n=count($keadaanKeluarInap);$i<$n;$i++){ ?>
                  <option value="<?php echo $keadaanKeluarInap[$i]["keadaan_keluar_inap_id"];?>" <?php if($keadaanKeluarInap[$i]["keadaan_keluar_inap_id"]==$_POST["id_keadaan_keluar_inap"]) echo "selected"; ?>><?php echo $keadaanKeluarInap[$i]["keadaan_keluar_inap_nama"];?></option>
                    <?php } ?>
                  </select>  </div>
                      </div>
                        
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Berat Lahir <? if($_POST["reg_jenis_pasien"]=='5'){ ?><span class="required">*</span><? }?>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12"> 
                        <input type="text" id="berat_lahir"  name="berat_lahir" value="<?php echo $_POST["reg_berat_lahir"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                  </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Surat Rujukan<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select name="srt_rujukan" id="srt_rujukan">
                  <option <?php if ($_POST["srt_rujukan"]=='1') echo "selected";?> value="1">Ada</option>
                  <option <?php if ($_POST["srt_rujukan"]=='0') echo "selected";?> value="0">Tidak Ada</option>
                </select>
                </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >BHP<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="bhp"  name="bhp" value="<?php echo $_POST["bhp"];?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                  </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Severity 3<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                        <select name="severity3" id="severity3">
                          <option <?php if ($_POST["severity3"]=='0') echo "selected";?> value="0">Tidak Ada</option>
                          <option <?php if ($_POST["severity3"]=='1') echo "selected";?> value="1">Ada</option>
                        </select>
                      </div>
                      </div>
                  </div>    
                      <div class="ln_solid"></div>                      
                    <input id="btnSave" name="btnSave" type="submit" class="btn btn-success" value="Simpan" onClick="javascript:return CekBridging(document.frmEdit);"/>
                  </div>
                  </div>
                </div>
              </form>
              </div>              
              
       </div>
            
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

<script type="text/javascript">

    function add_diagnosa(){
      var regId = $('#reg_id').val();
      var id_rawat = $('#rawat_id').val();
      if(regId != "") {
        $('#tb_diagnosa').edatagrid('addRow',{
          index: 0,
          row:{
            id_reg : regId,
            id_rawat : id_rawat,
            isNewRecord : true
          }
        });
      } else {alert(notif1);}
    }

    function add_diagnose(){
      var regId = $('#reg_id').val();
      var id_rawat = $('#rawat_id').val();
      if(regId != "") {
        $('#tb_diagnose').edatagrid('addRow',{
          index: 0,
          row:{
            id_reg : regId,
            id_rawat : id_rawat,
            isNewRecord : true
          }
        });
      } else {alert(notif1);}
    }



    function add_cod(){
      var regId = $('#reg_id').val();
      var id_rawat = $('#rawat_id').val();
      if(regId != "") {
        $('#tb_cod').edatagrid('addRow',{
          index: 0,
          row:{
            id_reg : regId,
            id_rawat : id_rawat,
            isNewRecord : true
          }
        });
      } else {alert(notif1);}
    }


    function simpan_diagnosa(){
      var simpan = $('#tb_diagnosa').edatagrid('saveRow');
      var id_rawat = $('#rawat_id').val();
      var row = $('#tb_diagnosa').datagrid('getRows')[0];
      var rows = $('#tb_diagnosa').datagrid('getSelected');
      var inac = $('#id_inacbg').val();
        $.post('simpan_diagnosa.php',{id_rawat:id_rawat,icd_id:rows.icd_id,rawat_icd_kasus_id:rows.rawat_icd_kasus_id,rawat_icd_status_id:rows.rawat_icd_status_id,rawat_icd_id:rows.rawat_icd_id,id_inacbg:inac});
      if (simpan){
        //load data
        $('#tb_diagnosa').datagrid({
          url: 'get_diagnosa.php'
        });
        // data parameter
        $('#tb_diagnosa').datagrid('load', {
          //id_reg: $('#reg_id').val(),
          id_rawat : $('#rawat_id').val()
        },'reload');
      }
      
    }

    function simpan_diagnose(){
      var simpan = $('#tb_diagnose').edatagrid('saveRow');
      var id_rawat = $('#rawat_id').val();
      var row = $('#tb_diagnose').datagrid('getRows')[0];
      var rows = $('#tb_diagnose').datagrid('getSelected');
      var inac = $('#id_inacbg').val();
      $.post('ctrl_diagnose.php?func=store',{id_rawat:id_rawat,diagnosa_id:rows.diagnosa_id,rawat_diagnosa_id:rows.rawat_diagnosa_id,id_inacbg:inac});
      if (simpan){
        //load data
        $('#tb_diagnose').datagrid({
          url: 'ctrl_diagnose.php'
        });
        // data parameter
        $('#tb_diagnose').datagrid('load', {
          //id_reg: $('#reg_id').val(),
          id_rawat : $('#rawat_id').val()
        },'reload');
      }
      
    }


    function simpan_cod(){
      var simpan = $('#tb_cod').edatagrid('saveRow');
      var id_rawat = $('#rawat_id').val();
      var row = $('#tb_cod').datagrid('getRows')[0];
      var rows = $('#tb_cod').datagrid('getSelected');
      var inac = $('#id_inacbg').val();
        $.post('simpan_cod.php',{id_rawat:id_rawat,icd_id:rows.icd_id,rawat_icd_kasus_id:rows.rawat_icd_kasus_id,rawat_icd_status_id:rows.rawat_icd_status_id,rawat_icd_id:rows.rawat_icd_id,id_inacbg:inac});
      if (simpan){
        //load data
        $('#tb_cod').datagrid({
          url: 'get_cod.php'
        });
        // data parameter
        $('#tb_cod').datagrid('load', {
          //id_reg: $('#reg_id').val(),
          id_rawat : $('#rawat_id').val()
        },'reload');
      }
      
    }




    function add_procedure(){
      var regId = $('#reg_id').val();
      var id_rawat = $('#rawat_id').val();
      var x = {
        isNewRecord : true,
      }
      if(regId != "") {
        //$('#tb_procedure').edatagrid('appendRow',x);
        $('#tb_procedure').edatagrid('addRow',{
          index: 0,
          row:{
            id_reg : regId,
            id_rawat : id_rawat
          }
        });
        //alert(x);
      } else {alert(notif1);}
    }

    function add_procedures(){
      var regId = $('#reg_id').val();
      var id_rawat = $('#rawat_id').val();
      var x = {
        isNewRecord : true,
      }
      if(regId != "") {
        //$('#tb_procedure').edatagrid('appendRow',x);
        $('#tb_procedures').edatagrid('addRow',{
          index: 0,
          row:{
            id_reg : regId,
            id_rawat : id_rawat
          }
        });
        //alert(x);
      } else {alert(notif1);}
    }

    function simpan_procedure(index){
      var simpan = $('#tb_procedure').edatagrid('saveRow');
      var id_rawat = $('#rawat_id').val();
      var row = $('#tb_procedure').datagrid('getRows')[0];
      var rows = $('#tb_procedure').datagrid('getSelected');
      var inac = $('#id_inacbg').val();
        $.post('simpan_procedure.php',{id_rawat:id_rawat,icd9_id:rows.icd9_id,rawat_icd9_id:rows.rawat_icd9_id,id_inacbg:inac});
      if (simpan){
        //load data
        $('#tb_procedure').datagrid({
          url: 'get_procedure.php'
        });
        
        // data parameter
        $('#tb_procedure').datagrid('load', {
          //id_reg: $('#reg_id').val(),
          id_rawat : $('#rawat_id').val()
        },'reload');
      }
      
    }

    function simpan_procedures(index){
      var simpan = $('#tb_procedures').edatagrid('saveRow');
      var id_rawat = $('#rawat_id').val();
      var row = $('#tb_procedures').datagrid('getRows')[0];
      var rows = $('#tb_procedures').datagrid('getSelected');
      var inac = $('#id_inacbg').val();
        $.post('ctrl_procedures.php?func=store',{id_rawat:id_rawat,procedure_id:rows.procedure_id,rawat_procedure_id:rows.rawat_procedure_id,id_inacbg:inac});
      if (simpan){
        //load data
        $('#tb_procedures').datagrid({
          url: 'ctrl_procedures.php'
        });
        
        // data parameter
        $('#tb_procedures').datagrid('load', {
          //id_reg: $('#reg_id').val(),
          id_rawat : $('#rawat_id').val()
        },'reload');
      }
      
    }

    function delete_diagnosa(){      
      var row = $('#tb_diagnosa').datagrid('getSelected');
      if (row){
        $.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
          if (r){
            $.post('ctrl_diagnosa.php?func=destroy',{id:row.rawat_icd_id},function(result){
              if (result.success){
                $.messager.show({ // 
                  title: 'Berhasil',
                  msg: "Berhasil Dihapus"
                });
                $('#tb_diagnosa').datagrid('reload'); // reload the user data
              } else {
                $.messager.show({ // show error message
                  title: 'Error',
                  msg: result.errorMsg
                });
              }
            },'json');
          }
        });
      }
    }    

    function delete_diagnose(){      
      var row = $('#tb_diagnose').datagrid('getSelected');
      if (row){
        $.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
          if (r){
            $.post('ctrl_diagnose.php?func=destroy',{id:row.rawat_diagnosa_id},function(result){
              if (result.success){
                $.messager.show({ // 
                  title: 'Berhasil',
                  msg: "Berhasil Dihapus"
                });
                $('#tb_diagnose').datagrid('reload'); // reload the user data
              } else {
                $.messager.show({ // show error message
                  title: 'Error',
                  msg: result.errorMsg
                });
              }
            },'json');
          }
        });
      }
    }    


    function delete_cod(){      
      var row = $('#tb_cod').datagrid('getSelected');
      if (row){
        $.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
          if (r){
            $.post('ctrl_cod.php?func=destroy',{id:row.rawat_icd_id},function(result){
              if (result.success){
                $.messager.show({ // 
                  title: 'Berhasil',
                  msg: "Berhasil Dihapus"
                });
                $('#tb_cod').datagrid('reload'); // reload the user data
              } else {
                $.messager.show({ // show error message
                  title: 'Error',
                  msg: result.errorMsg
                });
              }
            },'json');
          }
        });
      }
    }    

    function delete_procedure(){      
      var row = $('#tb_procedure').datagrid('getSelected');
      if (row){
        $.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
          if (r){
            $.post('ctrl_procedure.php?func=destroy',{id:row.rawat_icd9_id},function(result){
              if (result.success){
                $.messager.show({ // 
                  title: 'Berhasil',
                  msg: "Berhasil Dihapus"
                });
                $('#tb_procedure').datagrid('reload'); // reload the user data
              } else {
                $.messager.show({ // show error message
                  title: 'Error',
                  msg: result.errorMsg
                });
              }
            },'json');
          }
        });
      }
    }

    function delete_procedures(){      
      var row = $('#tb_procedures').datagrid('getSelected');
      if (row){
        $.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
          if (r){
            $.post('ctrl_procedures.php?func=destroy',{id:row.rawat_procedure_id},function(result){
              if (result.success){
                $.messager.show({ // 
                  title: 'Berhasil',
                  msg: "Berhasil Dihapus"
                });
                $('#tb_procedures').datagrid('reload'); // reload the user data
              } else {
                $.messager.show({ // show error message
                  title: 'Error',
                  msg: result.errorMsg
                });
              }
            },'json');
          }
        });
      }
    }

          $('#tb_diagnosa').edatagrid({
            url: 'get_diagnosa.php'
          });  

          $('#tb_diagnose').edatagrid({
            url: 'ctrl_diagnose.php'
          });

          $('#tb_cod').edatagrid({
            url: 'get_cod.php'
          }); 

          $('#tb_procedure').edatagrid({
            url: 'get_procedure.php'
          });

          $('#tb_procedures').edatagrid({
            url: 'ctrl_procedures.php'
          });

          $('#tb_diagnosa').datagrid('load', {
            id_rawat: $('#rawat_id').val(),
          });

          $('#tb_diagnose').datagrid('load', {
            id_rawat: $('#rawat_id').val(),
          });

          $('#tb_cod').datagrid('load', {
            id_rawat: $('#rawat_id').val(),
          });
          
          $('#tb_procedure').datagrid('load', {
            id_rawat : $('#rawat_id').val()
          });

          $('#tb_procedures').datagrid('load', {
            id_rawat : $('#rawat_id').val()
          });

</script>

<script type="text/javascript">
  function hapusicd(id,id_inacbg) {
    $.post('hapus_rawat_icd.php', {rawat_icd_id : id, id_inacbg: id_inacbg}, function(data) {
      if (data.success) {
        console.log(data);
        window.location.reload();
        //$('#diagn').load(document.URL +  ' #diagn');
      }
    },'json')
  } 

  function addDiagnosa(id_rawat, id_inacbg) {
    document.getElementById('hidTambah').value = '1';
    document.frmEdit.submit();

    $.post('add_rawat_diagnosa.php', { add : true, id_rawat: id_rawat, id_inacbg:id_inacbg }, function(data) {
      if (data.success) {
        console.log(data);
        //window.location.reload();
       // $('#diagn').load(document.URL +  ' #diagn');
      }
    },'json')
  }



  function addCod(id_rawat, id_inacbg) {
    document.getElementById('hidTambah').value = '1';
    document.frmEdit.submit();

    $.post('add_rawat_cod.php', { add : true, id_rawat: id_rawat, id_inacbg:id_inacbg }, function(data) {
      if (data.success) {
        console.log(data);
        //window.location.reload();
       // $('#diagn').load(document.URL +  ' #diagn');
      }
    },'json')
  }

  function hapusicd9(id,id_inacbg) {
    $.post('hapus_rawat_icd9.php', {rawat_icd9_id : id, id_inacbg: id_inacbg}, function(data) {
      if (data.success) {
        console.log(data);
        window.location.reload();
        //$('#proced').load(document.URL +  ' #proced');
      }
    },'json')
  } 

  function addProcedure(id_rawat, id_inacbg) {
   document.getElementById('hidTambah').value = '1';
    document.frmEdit.submit();
    $.post('add_rawat_procedure.php', { add : true, id_rawat: id_rawat, id_inacbg:id_inacbg }, function(data) {
      if (data.success) {
        console.log(data);
        //window.location.reload();
       // $('#proced').load(document.URL +  ' #proced');
      }
    },'json')
  }
</script>

 
<script type="text/javascript">
  
  
  //-------------------Morfologi yang ke 1
  
    function findValue201(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');              

    //alert("The value you selected was: " + sValue);
    document.getElementById('morfologi_nama').value=values[0];
    document.getElementById('id_morfologi_1').value=values[1];
    document.getElementById('morfologi_nomor').focus();
  }
  
    function selectItem201(li) {
      findValue201(li);
  }

  function formatItem201(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('morfologi_nama').value=alamat[0];
  document.getElementById('id_morfologi_1').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";
     
  }  
  
  
   //-------------------Morfologi yang ke 2
  
    function findValue202(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');              

    //alert("The value you selected was: " + sValue);
    document.getElementById('morfologi_nama2').value=values[0];
    document.getElementById('id_morfologi_2').value=values[1];
    document.getElementById('morfologi_nomor2').focus();
  }
  
    function selectItem202(li) {
      findValue202(li);
  }

  function formatItem202(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('morfologi_nama2').value=alamat[0];Diagnosa
  
  //-------------------External Cause yang ke 1
  
    function findValue301(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');              

    //alert("The value you selected was: " + sValue);
    document.getElementById('external_cause_nama').value=values[0];
    document.getElementById('id_external_cause_1').value=values[1];
    document.getElementById('external_cause_nomor').focus();
  }
  
    function selectItem301(li) {
      findValue301(li);
  }

  function formatItem301(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('external_cause_nama').value=alamat[0];
  document.getElementById('id_external_cause_1').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";
     
  }  
  
  
   //-------------------External Cause yang ke 2
  
    function findValue302(li) {
    if( li == null ) return alert("No match!");

    // if coming from an AJAX call, let's use the CityId as the value
    if( !!li.extra ) var sValue = li.extra[0];

    // otherwise, let's just display the value in the text box
    else var sValue = li.selectValue;
    var values =  sValue.split('~');              

    //alert("The value you selected was: " + sValue);
    document.getElementById('external_cause_nama2').value=values[0];
    document.getElementById('id_external_cause_2').value=values[1];
    document.getElementById('external_cause_nomor2').focus();
  }
  
    function selectItem302(li) {
      findValue302(li);
  }

  function formatItem302(row) {
  
  var alamat = row[1].split('~');
  
  if(row[0]) {
  document.getElementById('external_cause_nama2').value=alamat[0];
  document.getElementById('id_external_cause_2').value=alamat[1];
  } 
  return "<b>"+ row[0] +"</b>" + " (<b>"+ alamat[0] + "</b>)";
     
  }    

//--------------------END---------------------------------///
   
    
    $("#MorfAjax").autocomplete(
      "autocomplete_morfologi.php",
      {
        delay:10,
        minChars:2,
        matchSubset:1,
        matchContains:1,
        cacheLength:10,
        onItemSelect:selectItem201,
        onFindValue:findValue201,
        formatItem:formatItem201,
        autoFill:true
      }
    );
    
    $("#MorfAjax2").autocomplete(
      "autocomplete_morfologi.php",
      {
        delay:10,
        minChars:2,
        matchSubset:1,
        matchContains:1,
        cacheLength:10,
        onItemSelect:selectItem202,
        onFindValue:findValue202,
        formatItem:formatItem202,
        autoFill:true
      }
    );
    
    $("#ExAjax").autocomplete(
      "autocomplete_external_cause.php",
      {
        delay:10,
        minChars:2,
        matchSubset:1,
        matchContains:1,
        cacheLength:10,
        onItemSelect:selectItem301,
        onFindValue:findValue301,
        formatItem:formatItem301,
        autoFill:true
      }
    );
    
    $("#ExAjax2").autocomplete(
      "autocomplete_external_cause.php",
      {
        delay:10,
        minChars:2,
        matchSubset:1,
        matchContains:1,
        cacheLength:10,
        onItemSelect:selectItem302,
        onFindValue:findValue302,
        formatItem:formatItem302,
        autoFill:true
      }
    );
  
</script>

<input type="hidden" name="_x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"];?>"/>
<input type="hidden" name="cust_usr_nama_pasien" value="<?php echo $_POST["cust_usr_nama_pasien"];?>"/>
<input type="hidden" name="rawat_tanggal" value="<?php echo $_POST["rawat_tanggal"];?>"/>
<input type="hidden" name="cust_usr_alamat_pasien" value="<?php echo $_POST["cust_usr_alamat_pasien"];?>"/>
<input type="hidden" name="cust_usr_alamat" value="<?php echo $_POST["cust_usr_alamat"];?>"/>
<input type="hidden" name="cust_usr_no_hp" value="<?php echo $_POST["cust_usr_no_hp"];?>"/>
<input type="hidden" name="cust_usr_jenis" value="<?php echo $_POST["cust_usr_jenis"];?>"/>
<input type="hidden" name="rawat_id" id="rawat_id" value="<?php echo $dataPasien["rawat_id"];?>"/>
<input type="hidden" name="reg_waktu" value="<?php echo $_POST["reg_waktu"];?>"/>
<?php if($_POST["id_reg"]) { ?>
<input type="hidden" name="id_reg" value="<?php echo $_POST["id_reg"];?>"/>
<input type="hidden" name="id_pembayaran" value="<?php echo $_POST["id_pembayaran"];?>"/>
<?php } else { ?>
<input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"];?>"/>        
<?php } ?>
<input type="hidden" name="edit" value="<?php echo $_GET["edit"];?>"/>
<input type="hidden" name="id_inacbg" id="id_inacbg" value="<?php echo $_GET["id_inacbg"];?>"/>
<input type="hidden" name="tgl_awal" value="<?php echo $_GET["tgl_awal"];?>"/>
<input type="hidden" name="tgl_akhir" value="<?php echo $_GET["tgl_akhir"];?>"/>
<input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"];?>"/>
<input type="hidden" name="pembayaran_id" value="<?php echo $dataPembayaran["id_pembayaran"];?>"/>
<input type="hidden" name="usr_who_update" value="<?php echo $whoIsUpdate["usr_id"];?>"/>
<input type="hidden" name="id_inacbg" value="<?php echo $_GET['id_inacbg'];?>"/>

</form>
</body>
</html>