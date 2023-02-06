<?php
  // Library
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."currency.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."tampilan.php");

  // Inisialisasi Lib
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $enc = new textEncrypt();
  $userName = $auth->GetUserName();
  $userId = $auth->GetUserId();
  $depId = $auth->GetDepId();
  $poliId = $auth->IdPoli();
  $tglSekarang = date("d-m-Y");
  $depLowest = $auth->GetDepLowest();

  $sqlReg = "SELECT id_cust_usr, reg_id,reg_utama,id_pembayaran FROM klinik.klinik_registrasi WHERE id_poli = ".QuoteValue(DPE_CHAR, $_GET['id_poli'])." AND reg_tanggal = ".QuoteValue(DPE_DATE, date_db($_GET['reg_tanggal']))." AND reg_waktu = ".QuoteValue(DPE_CHAR, $_GET['reg_waktu']);
  $dataReg = $dtaccess->Fetch($sqlReg);

  $sqlBiaya = "SELECT biaya_id,biaya_nama,biaya_tarif_id,biaya_total FROM klinik.klinik_biaya a left join klinik.klinik_biaya_tarif b on b.id_biaya = a.biaya_id WHERE biaya_id = ".QuoteValue(DPE_CHAR, $_POST['tindakan']);
  $dataBIaya = $dtaccess->Fetch($sqlBiaya);

  $sql = "SELECT cust_usr_nama,cust_usr_alamat,cust_usr_tanggal_lahir from global.global_customer_user where cust_usr_id = ".QuoteValue(DPE_CHAR,$dataReg['id_cust_usr']);
  $dataPasien = $dtaccess->Fetch($sql);

  $sql = "SELECT pemeriksaan_id from laboratorium.lab_pemeriksaan where id_reg = ".QuoteValue(DPE_CHAR,$dataReg['reg_id']);
  $dataLab = $dtaccess->Fetch($sql);

  $sql = "SELECT pemeriksaan_id from radiologi.radiologi_pemeriksaan where id_reg = ".QuoteValue(DPE_CHAR,$dataReg['reg_id']);
  $dataRad = $dtaccess->Fetch($sql);

  $sql = "select rawat_id from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$dataReg['reg_utama']);
  $dataRawat = $dtaccess->Fetch($sql);

  /* --- Klinik_folio --- */
  $dbTable = "klinik.klinik_folio";

  $dbField[0] = "fol_id";   // PK
  $dbField[1] = "id_reg"; 
  $dbField[2] = "id_dokter"; 
  $dbField[3] = "id_poli"; 
  $dbField[4] = "id_cust_usr"; 
  $dbField[5] = "id_biaya"; 
  $dbField[6] = "id_pembayaran"; 
  $dbField[7] = "fol_lunas"; 
  $dbField[8] = "id_dep"; 
  $dbField[9] = "fol_jumlah"; 
  $dbField[10] = "who_when_update"; 
  $dbField[11] = "fol_nama";  
  $dbField[12] = "fol_nominal_satuan";  
  $dbField[13] = "fol_nominal"; 
  $dbField[14] = "fol_hrs_bayar";
  $dbField[15] = "fol_dokter_instruksi";
  $dbField[16] = "fol_pelaksana";
  $dbField[17] = "id_biaya_tarif";
  $dbField[18] = "fol_waktu";
  $dbField[19] = "tindakan_waktu";
  $dbField[20] = "tindakan_tanggal";  
  $dbField[21] = "fol_jenis";

  $fol_id = $dtaccess->GetTransID();
  $tanggal = date("Y-m-d");
  $waktu = date("H:i:s");

  $dbValue[0] = QuoteValue(DPE_CHAR,$fol_id);
  $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["reg_id"]); // id_reg ambil dari rujukan
  $dbValue[2] = QuoteValue(DPE_CHAR,''); // tidak ada karena belum meilih dokter di rujukan
  $dbValue[3] = QuoteValue(DPE_CHAR,$_GET['id_poli']); 
  $dbValue[4] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]); 
  $dbValue[5] = QuoteValue(DPE_CHAR,$_POST['tindakan']);
  $dbValue[6] = QuoteValue(DPE_CHAR,$dataReg['id_pembayaran']);
  $dbValue[7] = QuoteValue(DPE_CHAR,'n');
  $dbValue[8] = QuoteValue(DPE_CHAR,$depId);
  $dbValue[9] = QuoteValue(DPE_CHAR,1);
  $dbValue[10] = QuoteValue(DPE_CHAR,$userId);
  $dbValue[11] = QuoteValue(DPE_CHAR,$dataBIaya['biaya_nama']);
  $dbValue[12] = QuoteValue(DPE_NUMERIC,$dataBIaya['biaya_total']);
  $dbValue[13] = QuoteValue(DPE_NUMERIC,$dataBIaya['biaya_total']);
  $dbValue[14] = QuoteValue(DPE_NUMERIC,$dataBIaya['biaya_total']);
  $dbValue[15] = QuoteValue(DPE_CHAR,''); // tidak ada karena belum meilih dokter intruksi di rujukan
  $dbValue[16] = QuoteValue(DPE_CHAR,''); // tidak ada karena belum meilih pelaksana di rujukan
  $dbValue[17] = QuoteValue(DPE_CHAR,$dataBIaya['biaya_tarif_id']);
  $dbValue[18] = QuoteValue(DPE_DATE,$tanggal." ".$waktu);
  $dbValue[19] = QuoteValue(DPE_DATE,$waktu);
  $dbValue[20] = QuoteValue(DPE_DATE,$tanggal);  
  $dbValue[21] = QuoteValue(DPE_CHAR,'');

  $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  //print_r($dbValue); die();
  $dtmodel->Insert() or die("insert  error"); 
  
  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  unset($dtmodel);
  /* --- Klinik_folio --- */

    $dbTable = "klinik.klinik_perawatan_tindakan";
    $dbField[0] = "rawat_tindakan_id";   // PK
    $dbField[1] = "id_fol"; 
    $dbField[2] = "id_tindakan"; 
    $dbField[3] = "rawat_tindakan_total"; 
    $dbField[4] = "id_dokter"; 
    $dbField[5] = "id_dep"; 
    $dbField[6] = "id_rawat"; 
    $dbField[7] = "rawat_tindakan_jumlah"; 
    $dbField[8] = "is_sync"; 
    $dbField[9] = "rawat_tindakan_flag"; 
    $dbField[10] = "rawat_tindakan_nama"; 
  if ($_GET['id_poli'] == 'bd731912df14620374835f5e595d78bb' || $_GET['id_poli'] == '20') { //Radiologi
    $dbField[11] = "rawat_tindakan_jenis_sem"; 
  }
    //$dbField[9] = "rawat_tindakan_keterangan"; 
    //$dbField[10] = "rawat_tindakan_keterangan_2"; 
    //$dbField[10] = "rawat_tindakan_diskon"; 
  
    $rt_id = $dtaccess->GetTransID();
    $dbValue[0] = QuoteValue(DPE_CHAR,$rt_id);
    $dbValue[1] = QuoteValue(DPE_CHAR,$fol_id);
    $dbValue[2] = QuoteValue(DPE_CHAR,$dataBIaya["biaya_id"]);
    $dbValue[3] = QuoteValue(DPE_CHAR,$dataBIaya['biaya_total']*$_POST["fol_jumlah"]);
    $dbValue[4] = QuoteValue(DPE_CHAR,$dpjp);
    $dbValue[5] = QuoteValue(DPE_CHAR,$depId);
    $dbValue[6] = QuoteValue(DPE_CHAR,$dataRawat["rawat_id"]);
    $dbValue[7] = QuoteValue(DPE_NUMERIC,$_POST["fol_jumlah"]);
    $dbValue[8] = QuoteValue(DPE_CHAR,"n");
    $dbValue[9] = QuoteValue(DPE_CHAR,"J");
    $dbValue[10] = QuoteValue(DPE_CHAR,$dataBIaya['biaya_nama']);
  if ($_GET['id_poli'] == '20') { //Radiologi
    $dbValue[11] = QuoteValue(DPE_CHAR,'LA');
  }elseif ($_GET['id_poli'] == 'bd731912df14620374835f5e595d78bb') { //Radiologi
    $dbValue[11] = QuoteValue(DPE_CHAR,'RA');
  }

  $dbKey[0] = 0; # -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  //print_r($dbValue); die();
  $dtmodel->Insert() or die("insert  error"); 
  
  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  unset($dtmodel);

  $dataAnamnesaPilihan = ['tindakan_rujukan', 'id_tindakan', 'folio_id'];

  foreach ($dataAnamnesaPilihan as $f) {
    if (array_key_exists($f, $_POST)) {
      $data[] = [ 'field' => $f, 'value' => $_POST[$f] ];
    }
  }

  $dbTable = "klinik.klinik_rujukan_tindakan";

  $dbField[0] = 'rujukan_tindakan_id';
  $dbField[1] = 'rujukan_tindakan_nama';
  $dbField[2] = 'id_poli';
  $dbField[3] = 'id_reg';
  $dbField[4] = 'id_fol';

  $id= $dtaccess->GetTransID();
  $dbValue[0] = QuoteValue(DPE_CHAR, $id);   // PK
  $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['tindakan']);
  $dbValue[2] = QuoteValue(DPE_CHAR, $_GET['id_poli']);
  $dbValue[3] = QuoteValue(DPE_CHAR, $dataReg["reg_id"]);
  $dbValue[4] = QuoteValue(DPE_CHAR, $fol_id);

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
  $a = $dtmodel->Insert() or die("update error");

  if ($a) {
    foreach ($dataAnamnesaPilihan as $f) {
      $rs['tindakan_rujukan'] = $dataBIaya['biaya_nama'];
      $rs['id_tindakan'] = $id;
      $rs['folio_id'] = $fol_id;
    }
    echo json_encode($rs);
  }

  unset($dtmodel);
  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);  

  if ($_GET['id_poli'] == '20') { // LAB
     $dbTable = "laboratorium.lab_pemeriksaan";
     
     $dbField[0] = "pemeriksaan_id";   // PK
     $dbField[1] = "id_reg"; 
     $dbField[2] = "pemeriksaan_pasien_nama"; 
     $dbField[3] = "pemeriksaan_create"; 
     $dbField[4] = "pemeriksaan_alamat"; 
     $dbField[5] = "id_cust_usr"; 
     $dbField[6] = "who_update"; 
     $dbField[7] = "pemeriksaan_tgl"; 
     $dbField[8] = "pemeriksaan_hasil"; 
     $dbField[9] = "id_dokter_lab"; 
     //$dbField[10] = "pemeriksaan_tanggal_lahir"; 

     
     $pemeriksaanId = $dtaccess->GetTransId();
     $dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaanId);
     $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["reg_id"]); 
     $dbValue[2] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_nama"]); 
     $dbValue[3] = QuoteValue(DPE_DATE,$tanggal." ".$waktu); 
     $dbValue[4] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_alamat"]); 
     $dbValue[5] = QuoteValue(DPE_CHAR,$dataReg['id_cust_usr']); 
     $dbValue[6] = QuoteValue(DPE_CHAR,$userName); 
     $dbValue[7] = QuoteValue(DPE_CHAR,$tanggal); 
     $dbValue[8] = QuoteValue(DPE_CHAR,n); 
     $dbValue[9] = QuoteValue(DPE_CHAR,''); 
     //$dbValue[10] = QuoteValue(DPE_DATE,$dataPasien['cust_usr_tanggal_lahir']); 
     
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);

        if ($dataLab['pemeriksaan_id'] == '') {
          $dtmodel->Insert() or die("insert  error"); 
        }

        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);

    $sql = "select * from klinik.klinik_hasil_lab where id_biaya = ".QuoteValue(DPE_CHAR,$_POST["tindakan"]);
    //die($sql);
    $dataAnak= $dtaccess->FetchAll($sql);
    //print_r($dataAnak);

    for($i=0,$n=count($dataAnak);$i<$n;$i++) { 
      $dbTable = "laboratorium.lab_pemeriksaan_detail";
       
       $dbField[0] = "periksa_det_id";   // PK
       $dbField[1] = "id_pemeriksaan";     
       $dbField[2] = "who_update"; 
       $dbField[3] = "id_cust_usr";
       $dbField[4] = "nama_pemeriksaan";
       $dbField[5] = "id_biaya";
       $dbField[6] = "when_create";
       $dbField[7] = "detail_kode"; 
       $dbField[8] = "id_fol"; 
      //  $dbField[9] = "periksa_is_lowest"; 
       if($dataAnak[$i]["hasil_lab_keterangan"]){
       $dbField[9] = "pemeriksaan_nilai_normal";
       }   

       $pemeriksaandetAnakId = $dtaccess->GetTransID();   
    if ($dataLab['pemeriksaan_id'] == '') {
    $pemeriksaanId = $pemeriksaanId;
    }else{
      $pemeriksaanId = $dataLab['pemeriksaan_id'];
    }
       $dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaandetAnakId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$pemeriksaanId);
       $dbValue[2] = QuoteValue(DPE_CHAR,$userName);
       $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
       $dbValue[4] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_nama"]);
       $dbValue[5] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_id"]);
       $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
       $dbValue[7] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_kode"]);   
       $dbValue[8] = QuoteValue(DPE_CHAR,$fol_id);   
      //  $dbValue[9] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_is_lowest"]);   
       if($dataAnak[$i]["hasil_lab_keterangan"]){
       $dbValue[9] = QuoteValue(DPE_CHAR,$dataAnak[$i]["hasil_lab_keterangan"]); 
       } 
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

       $dtmodel->Insert() or die("insert  error");
      //echo "sukses insert lab_pemeriksaan_detail ".$i." => ";
       
       unset($dtmodel);
       unset($dbField);
       unset($dbValue);
       unset($dbKey);
     
    }
   } 

   if ($_GET['id_poli'] == 'bd731912df14620374835f5e595d78bb') { //Radiologi
     $dbTable = "radiologi.radiologi_pemeriksaan";
     
     $dbField[0] = "pemeriksaan_id";   // PK
     $dbField[1] = "id_reg"; 
     $dbField[2] = "pemeriksaan_pasien_nama"; 
     $dbField[3] = "pemeriksaan_create"; 
     $dbField[4] = "pemeriksaan_alamat"; 
     $dbField[5] = "id_cust_usr"; 
     $dbField[6] = "who_update"; 
     $dbField[7] = "pemeriksaan_tgl"; 
     $dbField[8] = "id_dokter"; 

     $pemeriksaanId = $dtaccess->GetTransId(); 
     $dokterRad = 'f9758f9e70f75744d793eae3e6864c64';
     $dbValue[0] = QuoteValue(DPE_CHAR,$pemeriksaanId);
     $dbValue[1] = QuoteValue(DPE_CHAR,$dataReg["reg_id"]); 
     $dbValue[2] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_nama"]); 
     $dbValue[3] = QuoteValue(DPE_DATE,$tanggal." ".$waktu); 
     $dbValue[4] = QuoteValue(DPE_CHAR,$dataPasien["cust_usr_alamat"]); 
     $dbValue[5] = QuoteValue(DPE_CHAR,$dataReg['id_cust_usr']); 
     $dbValue[6] = QuoteValue(DPE_CHAR,$userName); 
     $dbValue[7] = QuoteValue(DPE_CHAR,$tanggal); 
     $dbValue[8] = QuoteValue(DPE_CHAR,''); 
     
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_GLOBAL);

     if ($dataRad['pemeriksaan_id'] == '') {
          $dtmodel->Insert() or die("insert  error"); 
     }
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);  

     $dbTable = "radiologi.radiologi_resume";
               
     $dbField[0] = "resume_id";   // PK
     $dbField[1] = "resume_ket";
     $dbField[2] = "id_template";
     $dbField[3] = "id_reg";
     $dbField[4] = "id_dokter_rad";
     $dbField[5] = "resume_tanggal";
     $dbField[6] = "who_update";
     $dbField[7] = "id_cust_usr";
     $dbField[8] = "when_create";
     $dbField[9] = "id_pemeriksaan";
     $dbField[10] = "periksa_res_total";
     $dbField[11] = "nama_pemeriksaan";
     $dbField[12] = "id_dokter_pengirim";
     $dbField[13] = "id_kelompok";
     $dbField[14] = "id_sub_kelompok";
     $dbField[15] = "resume_foto";
     $dbField[16] = "id_fol";
     $dbField[17] = "id_biaya";

    if ($dataRad['pemeriksaan_id'] == '') {
    $pemeriksaanId = $pemeriksaanId;
    }else{
      $pemeriksaanId = $dataRad['pemeriksaan_id'];
    }
    
    $res_id = $dtaccess->GetTransID();
     $dbValue[0] = QuoteValue(DPE_CHAR,$res_id);
     $dbValue[1] = QuoteValue(DPE_CHAR,"");
     $dbValue[2] = QuoteValue(DPE_CHAR,"");
     $dbValue[3] = QuoteValue(DPE_CHAR,$dataReg["reg_id"]);
     $dbValue[4] = QuoteValue(DPE_CHAR,'');
     $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d"));
     $dbValue[6] = QuoteValue(DPE_CHAR,$userName);
     $dbValue[7] = QuoteValue(DPE_CHAR,$dataReg["id_cust_usr"]);
     $dbValue[8] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
     $dbValue[9] = QuoteValue(DPE_CHAR,$pemeriksaanId);
     $dbValue[10] = QuoteValue(DPE_CHAR,'1');
     $dbValue[11] = QuoteValue(DPE_CHAR,$dataBIaya['biaya_nama']);
     $dbValue[12] = QuoteValue(DPE_CHAR,'');
     $dbValue[13] = QuoteValue(DPE_CHAR,"");
     $dbValue[14] = QuoteValue(DPE_CHAR,"");
     $dbValue[15] = QuoteValue(DPE_CHAR,"");
     $dbValue[16] = QuoteValue(DPE_CHAR,$fol_id);
     $dbValue[17] = QuoteValue(DPE_CHAR,$_POST['tindakan']);
     
     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      //if($_POST['isNewRecord']=='true') {
      $dtmodel->Insert() or die("insert  error"); 
      //echo "sukses insert radiologi resume => " ;
    //}
     
     unset($dtmodel);
     unset($dbField);
     unset($dbValue);
     unset($dbKey);
   }
