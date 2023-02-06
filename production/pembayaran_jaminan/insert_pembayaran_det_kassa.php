<?php

/* List Jenis Pasien
DEFINE("TIPE_PASIEN_ASKES","1");
DEFINE("TIPE_PASIEN_UMUM","2");
DEFINE("TIPE_PASIEN_JKN","5");
DEFINE("TIPE_PASIEN_IKS","7");
DEFINE("TIPE_PASIEN_PROGRAM","8");
DEFINE("TIPE_PASIEN_GLOBAL_FUND","10");
DEFINE("TIPE_PASIEN_TIDAK_MEMBAYAR","15");
DEFINE("TIPE_PASIEN_JAMKESMAS","16");
DEFINE("TIPE_PASIEN_JAMKESDA","18");
DEFINE("TIPE_PASIEN_SKTM","19");                           
DEFINE("TIPE_PASIEN_FASILITAS","20");
DEFINE("TIPE_PASIEN_ASKES_FASILITAS","21");
DEFINE("TIPE_PASIEN_PKMS_SILVER","22");
DEFINE("TIPE_PASIEN_PKMS_GOLD","23");

                           
*/               
// echo "<pre>";
// print_r ($_POST);
// echo "</pre>";
// die();
$skr = date('Y-m-d');
$tgl1 = date("dmY"); 
$jam = date('H:i:s');
if ($_POST['reg_jenis_pasien'] == '2') {
  $pembDetFlag = "T"; //7    
  $pembDetPiutang = "T"; //14 
}elseif ($_POST['reg_jenis_pasien'] == '5' || $_POST['reg_jenis_pasien'] == '7' || $_POST['reg_jenis_pasien'] == '20') {
  $pembDetFlag = "P"; //7    
  $pembDetPiutang = "J"; //14 
}

// Delete Pembayaran Det

$sql = "SELECT pembayaran_det_id  from klinik.klinik_pembayaran_det where catatan = 'txtdibayar1' and id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
$dataPembayaranLalu = $dtaccess->Fetch($sql);

$pembayaranDetLalu = $dataPembayaranLalu['pembayaran_det_id'];

if($pembayaranDetLalu){
  $sql = "SELECT id_tra from gl.gl_buffer_transaksi where id_pembayaran_det = '$pembayaranDetLalu'";
  $jurnalLama = $dtaccess->Fetch($sql);

  $id_tra = $jurnalLama['id_tra'];

  $sql = "DELETE from gl.gl_buffer_transaksidetil where tra_id = '$id_tra'";
  $dtaccess->Execute($sql);

  $sql = "DELETE from gl.gl_buffer_transaksi where id_pembayaran_det = '$pembayaranDetLalu'";
  $dtaccess->Execute($sql);

  $sql = "DELETE from ar_ap.ar_trans where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
  $dtaccess->Execute($sql);

  $sql = "DELETE from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
  $dtaccess->Execute($sql);
}

$bayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtdibayar2"]) + StripCurrency($_POST["txtdibayar3"]) + StripCurrency($_POST["txtDiskon"]) + StripCurrency($_POST["deposit_nominal_awal"]);
$kurangBayar = $_POST["txtTotalBiayaService"] - $bayar;
$kurangnyaaaa = $_POST["txtTotalBiayaService"] - $bayar;

//Data Registrasi
  $sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_POST['id_reg']);
  $dataRegistrasi = $dtaccess->Fetch($sql);

    //AMBIL DAHULU DATA-DATA YANG DIBUTUHKAN
    //cari no urut pembayaran det terakhir 
    $sql = "select max(pembayaran_det_ke) as total from klinik.klinik_pembayaran_det 
            where id_pembayaran =".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $rs = $dtaccess->Execute($sql);
    $Maxs = $dtaccess->Fetch($rs);
    $MaksUrut = ($Maxs["total"]+1);
    //$MaksUrut = "1";
                    
    //cari data pembayaran
    $sql="select * from klinik.klinik_pembayaran where pembayaran_id=".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
    $dataPembayaran = $dtaccess->Fetch($sql);
    
    $kurang = $dataPembayaran["pembayaran_total"] - $dataPembayaran["pembayaran_yg_dibayar"];
    //echo $kurang; die();
    $selisih = $dataPembayaran["pembayaran_total"] - $dataPembayaran["pembayaran_dijamin"];
    if($kurang<0) $kurang=0;
    
    $dijamin = $dataPembayaran["pembayaran_dijamin"] + StripCurrency($_POST["inacbg_topup"]);
    if ($_POST['reg_tipe_rawat'] == 'I') { 
      $pembDetTotalService = $_POST["txtTotalBiayaService"] + (0.1 * $_POST["txtTotalBiayaService"]); 
    } else {
      $pembDetTotalService = $_POST["txtTotalBiayaService"];
    }

    if((StripCurrency($_POST["txtdibayar1"])+StripCurrency($_POST["txtDiskon"])+$_POST["deposit_nominal_awal"])>StripCurrency($_POST["total_harga"]) + StripCurrency($_POST["txtServiceCash"]))   //13
    { 
      if ($_POST['deposit_nominal'] > $_POST['total_harga']+ StripCurrency($_POST["txtServiceCash"])) {
        $_POST['txtdibayar1'] = '0';
        $pembDetDibayar = $_POST['total_harga'];
      }else{ 
        if ($_POST['pembayaran_det_pembulatan'] > 0) {
         $_POST["txtdibayar1"] = StripCurrency($_POST["total_harga"]) + StripCurrency($_POST["txtServiceCash"]) - $_POST["deposit_nominal"];
         $pembDetDibayar = StripCurrency($_POST["txtdibayar1"]) - $_POST["deposit_nominal"]+$_POST['pembayaran_det_pembulatan'];
        }else{ 
          if ($_POST['reg_tipe_rawat'] == 'I') { 
           $totalBayar = StripCurrency($_POST['txtdibayar1']) + StripCurrency($_POST['txtdibayar2']) + StripCurrency($_POST['txtdibayar3']) - StripCurrency($_POST['txtDiskon']);
           if ($totalBayar>=$_POST['total_harga']+$_POST['txtServiceCash']) { echo 'asd';
            $_POST["txtdibayar1"] = StripCurrency($_POST["total_harga"]) + StripCurrency($_POST["txtServiceCash"])-str_replace('-','',$_POST['pembayaran_det_pembulatan']);  
           }elseif($totalBayar+$_POST['deposit_nominal_awal'] >= $_POST['total_harga'] + $_POST['txtServiceCash']){
            $_POST['txtdibayar1'] = $_POST['txtdibayar1'];
           }else{ echo 'def';
            $_POST["txtdibayar1"] = StripCurrency($_POST["total_harga"]) + StripCurrency($_POST["txtServiceCash"]) - $_POST["deposit_nominal"]-str_replace('-','',$_POST['pembayaran_det_pembulatan']);
           }
           $pembDetDibayar = StripCurrency($_POST["txtdibayar1"]); 
          }else{
           $_POST["txtdibayar1"] = StripCurrency($_POST["total_harga"]) + StripCurrency($_POST["txtServiceCash"]) - $_POST["deposit_nominal"] -str_replace('-','',$_POST['pembayaran_det_pembulatan']);
           $pembDetDibayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtServiceCash"]) - $_POST["deposit_nominal"] - StripCurrency($_POST["txtDiskon"]);
          }

          // echo $_POST['txtdibayar1'];die();
        }
      }
    }
    else{ 
     $pembDetDibayar = StripCurrency($_POST["txtdibayar1"]);
    }
    $pembDetTotal = $_POST["total_harga"];  //5
    $pembDetServiceCharge = $_POST["txtServiceCash"]; //7 
    $pembDetDiskonPersen = $_POST["txtDiskonPersen"]; //16 
    $pembDetHrsBayar = StripCurrency($_POST["total_biaya"]) + StripCurrency($_POST["txtServiceCash"]) - StripCurrency($_POST["txtDiskon"]); //17
    $pembDetBiayaPembulatan = $_POST["pembayaran_det_pembulatan"]; //18 
    if ($_POST["deposit_nominal"]>=$pembDetTotalService) {
      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "pembayaran_det_service_cash";
      $dbField[8] = "id_dokter";
      $dbField[9] = "who_when_update";
      $dbField[10] = "id_jbayar";
      $dbField[11] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[13] = "pembayaran_det_dibayar";
      $dbField[14] = "pembayaran_det_tipe_piutang";
      $dbField[15] = "pembayaran_det_diskon";
      $dbField[16] = "pembayaran_det_diskon_persen";
      $dbField[17] = "pembayaran_det_hrs_bayar";
      $dbField[18] = "pembayaran_det_pembulatan";
      $dbField[19] = "id_reg";
      $dbField[20] = "pembayaran_det_slip";
      $dbField[21] = "pembayaran_det_ket";
      $dbField[22] = "pembayaran_det_mp_ke";
      if ($_POST["txtdibayar2"]<>'') $dbField[23] = "id_pembayaran_det_multipayment";
       
       $pembDetId = $dtaccess->GetTransID();
       if ($_POST["txtdibayar2"]<>'') $multiPaymentId = $pembDetId;
       $pembDetUtama = $pembDetId;
       $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
       $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                
       $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
       $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotalService));
       $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetServiceCharge));
       $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
       $dbValue[10] = QuoteValue(DPE_CHAR,'DP');
       $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
       $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtdibayar1"]));
       $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
       $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskon));
       $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskonPersen));
       $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
       $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetBiayaPembulatan));
       $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
       $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
       $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["pembayaran_det_ket"]);
       $dbValue[22] = QuoteValue(DPE_NUMERIC, 1);
       if ($_POST["txtdibayar2"]<>'') $dbValue[23] = QuoteValue(DPE_CHAR, $multiPaymentId);
      
       
      // print_r($dbValue); die();
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       
       $dtmodel->Insert() or die("insert  error");
       
       unset($dbField);
       unset($dtmodel);
       unset($dbValue);
       unset($dbKey);
       }else{ 
    
    //SEMUA PEMBAYARAN DET DIINSERT DIBAWAH
        $sql = "select pembayaran_det_kwitansi as kode from klinik.klinik_pembayaran_det a
              where pembayaran_det_tgl=".QuoteValue(DPE_DATE,$skr)."
              and  pembayaran_det_kwitansi is not null 
              order by pembayaran_det_create desc";
      $lastKode = $dtaccess->Fetch($sql);
      //echo $sql;
      //echo $lastKode["kode"]; die();
      
      if($pembDetFlag=="T"){ $flag="01"; }
      elseif($pembDetFlag=="P"){ $flag="02"; }
      elseif($pembDetFlag=="S"){ $flag="03"; }
      
      $kode=explode(".",$lastKode["kode"]);
      $flg=$kode[0];
      $ins=$kode[1];
      $tgl=$kode[2];
      $no=$kode[3];
      
     
      if($_POST["reg_tipe_rawat"]=="J"){
        $kw1 = "01";
      } elseif($_POST["reg_tipe_rawat"]=="G"){
        $kw1 = "03";
      } elseif($_POST["reg_tipe_rawat"]=="I"){
        $kw1 = "02";
      } 
        $_POST["kwitansi_nomor"] = $flag.".".$kw1.".".$tgl1.".".str_pad(($no+1),5,"0",STR_PAD_LEFT);
    if ($_POST["txtdibayar1"]<>'') {

      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "pembayaran_det_service_cash";
      $dbField[8] = "id_dokter";
      $dbField[9] = "who_when_update";
      $dbField[10] = "id_jbayar";
      $dbField[11] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[13] = "pembayaran_det_dibayar";
      $dbField[14] = "pembayaran_det_tipe_piutang";
      $dbField[15] = "pembayaran_det_diskon";
      $dbField[16] = "pembayaran_det_diskon_persen";
      $dbField[17] = "pembayaran_det_hrs_bayar";
      $dbField[18] = "pembayaran_det_pembulatan";
      $dbField[19] = "id_reg";
      $dbField[20] = "pembayaran_det_slip";
      $dbField[21] = "pembayaran_det_kwitansi";
      $dbField[22] = "pembayaran_det_mp_ke";
      $dbField[23] = "catatan";

      $pembDetId = $dtaccess->GetTransID();
      $pembDetUtama = $pembDetId;
      $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
      $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
      $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                                                
      $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
      $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotal));
      $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
      $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetServiceCharge));
      $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
      $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
      if ($_POST["id_jbayar11"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar11"]);
      }
      if ($_POST["id_jbayar12"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar12"]);
      }
      if ($_POST["id_jbayar13"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar13"]);
      }
      if ($_POST["id_jbayar14"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar14"]);
      }
      $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
      $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
      $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDibayar));
      $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
      $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskon));
      $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskonPersen));
      $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
      $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($pembayaran_det_pembulatan));
      $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
      $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
      $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
      $dbValue[22] = QuoteValue(DPE_CHAR, '1');
      $dbValue[23] = QuoteValue(DPE_CHAR, 'txtdibayar1');

      // print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dtmodel);
      unset($dbValue);
      unset($dbKey);

      if ($_POST['jpasien1'] == '20') {
        $flagPiutang = 'K';
        $PK = $dataRegistrasi['id_pgw'];
      }elseif ($_POST['jpasien1'] == '5') {
        $flagPiutang = 'B';
        $PK = 'BPJS';
      }elseif ($_POST['jpasien1'] == '7') {
        $flagPiutang = 'A';
        $PK = $dataRegistrasi['id_perusahaan'];
      }elseif ($_POST['jpasien1'] == '2' && $_POST['id_jbayar13'] == 'x') {
        $flagPiutang = 'P';
        $PK = '';
      }
      $dbTable = "ar_ap.ar_trans";
      $dbField[0] = "ar_trans_id";   // PK
      $dbField[1] = "id_dept";
      $dbField[2] = "ar_trans_tgl";
      $dbField[3] = "ar_trans_who_update";
      $dbField[4] = "ar_trans_when_update";
      $dbField[5] = "id_cust_usr";
      $dbField[6] = "ar_trans_jumlah";
      $dbField[7] = "ar_trans_sisa";
      $dbField[8] = "ar_trans_tgl_jatuhtempo";
      $dbField[9] = "id_reg";
      $dbField[10] = "id_pembayaran";
      $dbField[11] = "flag_piutang";
      if ($_POST["id_jbayar11"]!='--') { //Asuransi
        $dbField[12] = "id_perusahaan";
      }
      if ($_POST["id_jbayar12"]!='--') { //BPJS
        $dbField[12] = "id_jkn";
      }
      if ($_POST["id_jbayar14"]!='--') { //Karyawan
        $dbField[12] = "id_pgw";
      }
      if ($_POST['id_jbayar13']) {
        $dbField[12] = "id_pgw";
      }
      $dbField[13] = "ar_trans_kode";
      $dbField[14] = "id_pembayaran_det";

      $id = $dtaccess->GetTransId();
      $tgl1 = date('Y-m-d'); // pendefinisian tanggal awal
      $tgl2 = date('Y-m-d', strtotime('+30 days', strtotime($tgl1)));
      $kurangnya = $_POST["txtdibayar1"];
      //Kode 
      $sql = "select count(ar_trans_id) as urut from ar_ap.ar_trans where ar_trans_tgl = ".QuoteValue(DPE_DATE,date('Y-m-d'));
      $dataKode = $dtaccess->Fetch($sql);
      $Urutan = $dataKode['urut'] + 1;
      $Kodenyaa = "ARP-".date('Ymd')."-".$Urutan;
      $dbValue[0] = QuoteValue(DPE_CHAR, $id);
      $dbValue[1] = QuoteValue(DPE_CHAR, $depId);
      $dbValue[2] = QuoteValue(DPE_DATE, date_db($_POST['tanggal_posting']));
      $dbValue[3] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[4] = QuoteValue(DPE_CHAR, date_db($_POST['tanggal_posting']).' '.$jam);
      $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['id_cust_usr']);
      $dbValue[6] = QuoteValue(DPE_CHAR, StripCurrency($kurangnya));
      $dbValue[7] = QuoteValue(DPE_CHAR, StripCurrency($kurangnya));
      $dbValue[8] = QuoteValue(DPE_DATE, $tgl2);
      $dbValue[9] = QuoteValue(DPE_CHAR, $_POST['id_reg']);
      $dbValue[10] = QuoteValue(DPE_CHAR, $_POST['pembayaran_id']);
      $dbValue[11] = QuoteValue(DPE_CHAR, $flagPiutang);
      $dbValue[12] = QuoteValue(DPE_CHAR, $PK);
      $dbValue[13] = QuoteValue(DPE_CHAR, $Kodenyaa);
      $dbValue[14] = QuoteValue(DPE_CHAR, $pembDetId);
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dbValue);
    }
    if ($_POST["txtdibayar2"]<>'') {
      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "pembayaran_det_service_cash";
      $dbField[8] = "id_dokter";
      $dbField[9] = "who_when_update";
      $dbField[10] = "id_jbayar";
      $dbField[11] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[13] = "pembayaran_det_dibayar";
      $dbField[14] = "pembayaran_det_tipe_piutang";
      $dbField[15] = "pembayaran_det_diskon";
      $dbField[16] = "pembayaran_det_diskon_persen";
      $dbField[17] = "pembayaran_det_hrs_bayar";
      $dbField[18] = "pembayaran_det_pembulatan";
      $dbField[19] = "id_reg";
      $dbField[20] = "pembayaran_det_slip";
      $dbField[21] = "pembayaran_det_kwitansi";
      $dbField[22] = "pembayaran_det_mp_ke";
      $dbField[23] = "catatan";

      
       $pembDetId = $dtaccess->GetTransID();
       // $pembDetUtama = $pembDetId;
       $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
       $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                
       $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
       $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotal));
       $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetServiceCharge));
       $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
      if ($_POST["id_jbayar21"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar21"]);
      }
      if ($_POST["id_jbayar22"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar22"]);
      }
      if ($_POST["id_jbayar23"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar23"]);
      }
      if ($_POST["id_jbayar24"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar24"]);
      }
       $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
       $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtdibayar2"]));
       $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
       $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskon));
       $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskonPersen));
       $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
       $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetBiayaPembulatan));
       $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
       $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
       $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
       $dbValue[22] = QuoteValue(DPE_CHAR, '2');
       $dbValue[23] = QuoteValue(DPE_CHAR, 'txtdibayar2');
      
       
     //  print_r($dbValue); die();
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       
       $dtmodel->Insert() or die("insert  error");
       
       unset($dbField);
       unset($dtmodel);
       unset($dbValue);
       unset($dbKey);

      if ($_POST['jpasien2'] == '20') {
        $flagPiutang = 'K';
        $PK = $dataRegistrasi['id_pgw'];
      }elseif ($_POST['jpasien2'] == '5') {
        $flagPiutang = 'B';
        $PK = 'BPJS';
      }elseif ($_POST['jpasien2'] == '7') {
        $flagPiutang = 'A';
        $PK = $dataRegistrasi['id_perusahaan'];
      }elseif ($_POST['jpasien2'] == '2' && $_POST['id_jbayar23'] == 'x') {
        $flagPiutang = 'P';
        $PK = '';
      }
      $dbTable = "ar_ap.ar_trans";
      $dbField[0] = "ar_trans_id";   // PK
      $dbField[1] = "id_dept";
      $dbField[2] = "ar_trans_tgl";
      $dbField[3] = "ar_trans_who_update";
      $dbField[4] = "ar_trans_when_update";
      $dbField[5] = "id_cust_usr";
      $dbField[6] = "ar_trans_jumlah";
      $dbField[7] = "ar_trans_sisa";
      $dbField[8] = "ar_trans_tgl_jatuhtempo";
      $dbField[9] = "id_reg";
      $dbField[10] = "id_pembayaran";
      $dbField[11] = "flag_piutang";
      if ($_POST["id_jbayar21"]!='--') { //Asuransi
        $dbField[12] = "id_perusahaan";
      }
      if ($_POST["id_jbayar22"]!='--') { //BPJS
        $dbField[12] = "id_jkn";
      }
      if ($_POST["id_jbayar24"]!='--') { //Karyawan
        $dbField[12] = "id_pgw";
      }else{
        $dbField[12] = "id_pgw";
      }
      $dbField[13] = "ar_trans_kode";
      $dbField[14] = "id_pembayaran_det";

      $id1 = $dtaccess->GetTransId();
      $tgl1 = date('Y-m-d'); // pendefinisian tanggal awal
      $tgl2 = date('Y-m-d', strtotime('+30 days', strtotime($tgl1)));
      $kurangnya = $_POST["txtdibayar2"];
       //Kode 
      $sql = "select count(ar_trans_id) as urut from ar_ap.ar_trans where ar_trans_tgl = ".QuoteValue(DPE_DATE,date('Y-m-d'));
      $dataKode = $dtaccess->Fetch($sql);
      $Urutan = $dataKode['urut'] + 1;
      $Kodenyaa = "ARP-".date('Ymd')."-".$Urutan;
      $dbValue[0] = QuoteValue(DPE_CHAR, $id1);
      $dbValue[1] = QuoteValue(DPE_CHAR, $depId);
      $dbValue[2] = QuoteValue(DPE_DATE, date_db($_POST['tanggal_posting']));
      $dbValue[3] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[4] = QuoteValue(DPE_CHAR, date_db($_POST['tanggal_posting']).' '.$jam);
      $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['id_cust_usr']);
      $dbValue[6] = QuoteValue(DPE_NUMERIC, StripCurrency($kurangnya));
      $dbValue[7] = QuoteValue(DPE_NUMERIC, StripCurrency($kurangnya));
      $dbValue[8] = QuoteValue(DPE_DATE, $tgl2);
      $dbValue[9] = QuoteValue(DPE_CHAR, $_POST['id_reg']);
      $dbValue[10] = QuoteValue(DPE_CHAR, $_POST['pembayaran_id']);
      $dbValue[11] = QuoteValue(DPE_CHAR, $flagPiutang);
      $dbValue[12] = QuoteValue(DPE_CHAR, $PK);
      $dbValue[13] = QuoteValue(DPE_CHAR, $Kodenyaa);
      $dbValue[14] = QuoteValue(DPE_CHAR, $pembDetId);
      // print_r($dbValue);die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dbValue);
    }
    if ($_POST["txtdibayar3"]<>'') {
      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "pembayaran_det_service_cash";
      $dbField[8] = "id_dokter";
      $dbField[9] = "who_when_update";
      $dbField[10] = "id_jbayar";
      $dbField[11] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[13] = "pembayaran_det_dibayar";
      $dbField[14] = "pembayaran_det_tipe_piutang";
      $dbField[15] = "pembayaran_det_diskon";
      $dbField[16] = "pembayaran_det_diskon_persen";
      $dbField[17] = "pembayaran_det_hrs_bayar";
      $dbField[18] = "pembayaran_det_pembulatan";
      $dbField[19] = "id_reg";
      $dbField[20] = "pembayaran_det_slip";
      $dbField[21] = "pembayaran_det_kwitansi";
      $dbField[22] = "pembayaran_det_mp_ke";
      $dbField[23] = "catatan";

      
       $pembDetId = $dtaccess->GetTransID();
       // $pembDetUtama = $pembDetId;
       $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
       $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                
       $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
       $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotal));
       $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetServiceCharge));
       $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
      if ($_POST["id_jbayar31"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar31"]);
      }
      if ($_POST["id_jbayar32"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar32"]);
      }
      if ($_POST["id_jbayar33"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar33"]);
      }
      if ($_POST["id_jbayar34"]!='--') { 
        $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_jbayar34"]);
      }
       $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
       $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtdibayar3"]));
       $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
       $dbValue[15] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskon));
       $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetDiskonPersen));
       $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
       $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetBiayaPembulatan));
       $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
       $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
       $dbValue[21] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
       $dbValue[22] = QuoteValue(DPE_CHAR, '3');
       $dbValue[23] = QuoteValue(DPE_CHAR, 'txtdibayar3');
      
       
     //  print_r($dbValue); die();
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       
       $dtmodel->Insert() or die("insert  error");
       
       unset($dbField);
       unset($dtmodel);
       unset($dbValue);
       unset($dbKey);

      if ($_POST['jpasien3'] == '20') {
        $flagPiutang = 'K';
        $PK = $dataRegistrasi['id_pgw'];
      }elseif ($_POST['jpasien3'] == '5') {
        $flagPiutang = 'B';
        $PK = 'BPJS';
      }elseif ($_POST['jpasien3'] == '7') {
        $flagPiutang = 'A';
        $PK = $dataRegistrasi['id_perusahaan'];
      }elseif ($_POST['jpasien3'] == '2' && $_POST['id_jbayar33'] == 'x') {
        $flagPiutang = 'P';
        $PK = '';
      }
      $dbTable = "ar_ap.ar_trans";
      $dbField[0] = "ar_trans_id";   // PK
      $dbField[1] = "id_dept";
      $dbField[2] = "ar_trans_tgl";
      $dbField[3] = "ar_trans_who_update";
      $dbField[4] = "ar_trans_when_update";
      $dbField[5] = "id_cust_usr";
      $dbField[6] = "ar_trans_jumlah";
      $dbField[7] = "ar_trans_sisa";
      $dbField[8] = "ar_trans_tgl_jatuhtempo";
      $dbField[9] = "id_reg";
      $dbField[10] = "id_pembayaran";
      $dbField[11] = "flag_piutang";
      if ($_POST["id_jbayar31"]!='--') { //Asuransi
        $dbField[12] = "id_perusahaan";
      }
      if ($_POST["id_jbayar32"]!='--') { //BPJS
        $dbField[12] = "id_jkn";
      }
      if ($_POST["id_jbayar34"]!='--') { //Karyawan
        $dbField[12] = "id_pgw";
      }else{
        $dbField[12] = "id_pgw";
      }
      $dbField[13] = "ar_trans_kode";
      $dbField[14] = "id_pembayaran_det";

      $id1 = $dtaccess->GetTransId();
      $tgl1 = date('Y-m-d'); // pendefinisian tanggal awal
      $tgl2 = date('Y-m-d', strtotime('+30 days', strtotime($tgl1)));
      $kurangnya = $_POST["txtdibayar3"];
       //Kode 
      $sql = "select count(ar_trans_id) as urut from ar_ap.ar_trans where ar_trans_tgl = ".QuoteValue(DPE_DATE,date('Y-m-d'));
      $dataKode = $dtaccess->Fetch($sql);
      $Urutan = $dataKode['urut'] + 1;
      $Kodenyaa = "ARP-".date('Ymd')."-".$Urutan;
      $dbValue[0] = QuoteValue(DPE_CHAR, $id1);
      $dbValue[1] = QuoteValue(DPE_CHAR, $depId);
      $dbValue[2] = QuoteValue(DPE_DATE, date_db($_POST['tanggal_posting']));
      $dbValue[3] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[4] = QuoteValue(DPE_CHAR, date_db($_POST['tanggal_posting']).' '.$jam);
      $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['id_cust_usr']);
      $dbValue[6] = QuoteValue(DPE_NUMERIC, StripCurrency($kurangnya));
      $dbValue[7] = QuoteValue(DPE_NUMERIC, StripCurrency($kurangnya));
      $dbValue[8] = QuoteValue(DPE_DATE, $tgl2);
      $dbValue[9] = QuoteValue(DPE_CHAR, $_POST['id_reg']);
      $dbValue[10] = QuoteValue(DPE_CHAR, $_POST['pembayaran_id']);
      $dbValue[11] = QuoteValue(DPE_CHAR, $flagPiutang);
      $dbValue[12] = QuoteValue(DPE_CHAR, $PK);
      $dbValue[13] = QuoteValue(DPE_CHAR, $Kodenyaa);
      $dbValue[14] = QuoteValue(DPE_CHAR, $pembDetId);
      // print_r($dbValue);die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dbValue);
    }
       
      if ($kurangBayar > 0) //JIKA KURANG BAYAR
       {
        // echo 'bane';die();
      // $_POST["kwitansi_nomor"] = $flag.".".$kw1.".".$tgl1.".".str_pad(($no+1),5,"0",STR_PAD_LEFT);
       
       $dbTable = "klinik.klinik_pembayaran_det";
            $dbField[0] = "pembayaran_det_id"; // PK
            $dbField[1] = "id_pembayaran";
            $dbField[2] = "pembayaran_det_create";
            $dbField[3] = "pembayaran_det_tgl";
            $dbField[4] = "pembayaran_det_ke";
            $dbField[5] = "pembayaran_det_total";
            $dbField[6] = "id_dep";
            $dbField[7] = "id_dokter";
            $dbField[8] = "who_when_update";
            $dbField[9] = "id_jbayar";
            $dbField[10] = "id_jenis_pasien";
            $dbField[11] = "pembayaran_det_flag";
            $dbField[12] = "pembayaran_det_tipe_piutang";
            $dbField[13] = "id_reg";
            $dbField[14] = "pembayaran_det_ket";
            $dbField[15] = "pembayaran_det_kwitansi";
            $dbField[16] = "pembayaran_det_dibayar";
            $dbField[17] = "catatan";
          
             $pembDetIdNew2 = $dtaccess->GetTransID();
             $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetIdNew2);
             $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
             $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
             $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                
             $dbValue[4] = QuoteValue(DPE_NUMERIC,($MaksUrut+2));
             $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($kurangBayar));
             $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
             $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
             $dbValue[8] = QuoteValue(DPE_CHAR,$userName);
             $dbValue[9] = QuoteValue(DPE_CHAR,'');
             $dbValue[10] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
             $dbValue[11] = QuoteValue(DPE_CHAR,"P");
             $dbValue[12] = QuoteValue(DPE_CHAR,'P');
             $dbValue[13] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
             $dbValue[14] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_ket"]);
             $dbValue[15] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
             $dbValue[16] = QuoteValue(DPE_NUMERIC,StripCurrency($kurangBayar));
             $dbValue[17] = QuoteValue(DPE_CHAR,"Kurang bayar");
             
             // print_r($dbValue); die();
             $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
             $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
             if ($kurangBayar > 0) {
               $dtmodel->Insert() or die("insert  error");
             }
             
             unset($dbField);
             unset($dtmodel);
             unset($dbValue);
             unset($dbKey);

             $dbTable = "ar_ap.ar_trans";
              $dbField[0] = "ar_trans_id";   // PK
              $dbField[1] = "id_dept";
              $dbField[2] = "ar_trans_tgl";
              $dbField[3] = "ar_trans_who_update";
              $dbField[4] = "ar_trans_when_update";
              $dbField[5] = "id_cust_usr";
              $dbField[6] = "ar_trans_jumlah";
              $dbField[7] = "ar_trans_sisa";
              $dbField[8] = "ar_trans_tgl_jatuhtempo";
              $dbField[9] = "id_reg";
              $dbField[10] = "id_pembayaran";
              $dbField[11] = "flag_piutang";
              $dbField[12] = "ar_trans_kode";
              $dbField[13] = "id_pembayaran_det";

              $id = $dtaccess->GetTransId();
              $tgl1 = date('Y-m-d'); // pendefinisian tanggal awal
              $tgl2 = date('Y-m-d', strtotime('+30 days', strtotime($tgl1)));
              // $kurangnya = $_POST["total_harga"] - $bayar;

              //Kode 
              $sql = "select count(ar_trans_id) as urut from ar_ap.ar_trans where ar_trans_tgl = ".QuoteValue(DPE_DATE,date('Y-m-d'));
              $dataKode = $dtaccess->Fetch($sql);
              $Urutan = $dataKode['urut'] + 1;
              $Kodenyaa = "ARP-".date('Ymd')."-".$Urutan;
              $dbValue[0] = QuoteValue(DPE_CHAR, $id);
              $dbValue[1] = QuoteValue(DPE_CHAR, $depId);
              $dbValue[2] = QuoteValue(DPE_DATE, date('Y-m-d'));
              $dbValue[3] = QuoteValue(DPE_CHAR, $userName);
              $dbValue[4] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
              $dbValue[5] = QuoteValue(DPE_CHAR, $_POST['id_cust_usr']);
              $dbValue[6] = QuoteValue(DPE_NUMERIC, $kurangBayar);
              $dbValue[7] = QuoteValue(DPE_NUMERIC, $kurangBayar);
              $dbValue[8] = QuoteValue(DPE_DATE, $tgl2);
              $dbValue[9] = QuoteValue(DPE_CHAR, $_POST['id_reg']);
              $dbValue[10] = QuoteValue(DPE_CHAR, $_POST['pembayaran_id']);
              $dbValue[11] = QuoteValue(DPE_CHAR, 'KB');
              $dbValue[12] = QuoteValue(DPE_CHAR, $Kodenyaa);
              $dbValue[13] = QuoteValue(DPE_CHAR, $pembDetIdNew2);
              // print_r($dbValue);die();
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
              $dtmodel->Insert() or die("insert  error");

              unset($dbField);
              unset($dbValue);
       } //AKHIR KURANG BAYAR   

       //DISKON
      if ($_POST["txtDiskon"]<>'') {
    
      $dbTable = "klinik.klinik_pembayaran_det";
      $dbField[0] = "pembayaran_det_id"; // PK
      $dbField[1] = "id_pembayaran";
      $dbField[2] = "pembayaran_det_create";
      $dbField[3] = "pembayaran_det_tgl";
      $dbField[4] = "pembayaran_det_ke";
      $dbField[5] = "pembayaran_det_total";
      $dbField[6] = "id_dep";
      $dbField[7] = "pembayaran_det_service_cash";
      $dbField[8] = "id_dokter";
      $dbField[9] = "who_when_update";
      $dbField[10] = "id_jbayar";
      $dbField[11] = "id_jenis_pasien";
      $dbField[12] = "pembayaran_det_flag";
      $dbField[13] = "pembayaran_det_dibayar";
      $dbField[14] = "pembayaran_det_tipe_piutang";
      $dbField[15] = "pembayaran_det_diskon";
      $dbField[16] = "pembayaran_det_diskon_persen";
      $dbField[17] = "pembayaran_det_hrs_bayar";
      $dbField[18] = "pembayaran_det_pembulatan";
      $dbField[19] = "id_reg";
      $dbField[20] = "pembayaran_det_slip";
      $dbField[21] = "pembayaran_det_ket";
      $dbField[22] = "pembayaran_det_mp_ke";
      $dbField[23] = "pembayaran_det_kwitansi";
      if ($_POST["txtdibayar2"]<>'') $dbField[24] = "id_pembayaran_det_multipayment";
      
       $pembDetId = $dtaccess->GetTransID();
       $dbValue[0] = QuoteValue(DPE_CHARKEY,$pembDetId);
       $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["pembayaran_id"]);
       $dbValue[2] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']).' '.$jam);
       $dbValue[3] = QuoteValue(DPE_DATE,date_db($_POST['tanggal_posting']));                                
       $dbValue[4] = QuoteValue(DPE_NUMERIC,$MaksUrut);
       $dbValue[5] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetTotal));
       $dbValue[6] = QuoteValue(DPE_CHAR,$depId);
       $dbValue[7] = QuoteValue(DPE_NUMERIC,0);
       $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
       $dbValue[9] = QuoteValue(DPE_CHAR,$userName);
       $dbValue[10] = QuoteValue(DPE_CHAR,'Diskon');
       $dbValue[11] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
       $dbValue[12] = QuoteValue(DPE_CHAR,$pembDetFlag);
       $dbValue[13] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["txtDiskon"]));
       $dbValue[14] = QuoteValue(DPE_CHAR,$pembDetPiutang);
       $dbValue[15] = QuoteValue(DPE_NUMERIC,0);
       $dbValue[16] = QuoteValue(DPE_NUMERIC,0);
       $dbValue[17] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetHrsBayar));
       $dbValue[18] = QuoteValue(DPE_NUMERIC,StripCurrency($pembDetBiayaPembulatan));
       $dbValue[19] = QuoteValue(DPE_CHAR, $_POST["id_reg"]);
       $dbValue[20] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_slip"]);
       $dbValue[21] = QuoteValue(DPE_CHAR,$_POST["pembayaran_det_ket"]);
       $dbValue[22] = QuoteValue(DPE_NUMERIC, 3);
       $dbValue[23] = QuoteValue(DPE_CHAR, $_POST["kwitansi_nomor"]);
       if ($_POST["txtdibayar2"]<>'') $dbValue[24] = QuoteValue(DPE_CHAR, $multiPaymentId); 
      
       
     //  print_r($dbValue); die();
       $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
       $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
       
       $dtmodel->Insert() or die("insert  error");
       
       unset($dbField);
       unset($dtmodel);
       unset($dbValue);
       unset($dbKey);
       }
}
?>