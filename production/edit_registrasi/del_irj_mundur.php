<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."tampilan.php");

//INISIALISASI LIBRARY
$enc = new textEncrypt();
$dtaccess = new DataAccess();
$auth = new CAuth();
   $depId = $auth->GetDepId();
   $userName = $auth->GetUserName();
$userLogin = $auth->GetUserData();
$id = $_POST['reg_id'];

#cek sudah bayar belum
$sql = "select id_reg from klinik.klinik_pembayaran_det where id_reg = ".QuoteValue(DPE_CHAR,$id);
$pemb = $dtaccess->FetchAll($sql);

$sql = "SELECT reg_status from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$id);
$status = $dtaccess->Fetch($sql);

$stat = substr($status['reg_status'], 1);

$sql = "select fol_id from klinik.klinik_folio where id_reg = ".QuoteValue(DPE_CHAR, $id)." and fol_lunas='n'";
$dataTindakan = $dtaccess->Fetch($sql);

//echo $sql;

//echo count($pemb);

if (count($pemb) > 0) {
     echo json_encode(array('errorMsg'=>'Tagihan sudah di bayar. Batal Bayar dahulu.'));
} else if(($status['reg_status'] == 'E1' || $status['reg_status'] == 'G1') && $dataTindakan['fol_id'] == ''){
     $sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$id);
     $reg = $dtaccess->Fetch($sql);

     #insert ke klinik registrasi batal
     $dbTable = "klinik.klinik_registrasi_batal";

     $dbField[0] = "reg_batal_id";   // PK
     $dbField[1] = "reg_batal_tanggal";
     $dbField[2] = "reg_batal_waktu";
     $dbField[3] = "id_cust_usr";
     $dbField[4] = "reg_batal_status";
     $dbField[5] = "reg_batal_who_update";
     $dbField[6] = "reg_batal_when_update";
     $dbField[7] = "reg_batal_jenis_pasien";
     $dbField[8] = "reg_batal_status_pasien";
     $dbField[9] = "reg_batal_rujukan_id";         
     $dbField[10] = "reg_batal_tipe_rawat";
     $dbField[11] = "id_poli";
     $dbField[12] = "id_dep";
     $dbField[13] = "reg_batal_shift";
     $dbField[14] = "reg_batal_tipe_layanan";
     $dbField[15] = "reg_batal_sebab_sakit";
     $dbField[16] = "id_dokter";
     $dbField[17] = "reg_batal_diagnosa_awal";
     $dbField[18] = "id_pembayaran";
     $dbField[19] = "reg_batal_tingkat_kegawatan";
     $dbField[20] = "id_poli_asal";
     $dbField[21] = "reg_batal_umur";
     $dbField[22] = "reg_batal_umur_bulan";
     $dbField[23] = "reg_batal_umur_hari";
     $dbField[24] = "reg_batal_kelas";
     $dbField[25] = "reg_batal_prosedur_masuk";
     $dbField[26] = "reg_batal_tracer_registrasi";
     $dbField[27] = "reg_batal_tracer_barcode";
     $dbField[28] = "reg_batal_tracer_barcode_besar";
     $dbField[29] = "reg_batal_tracer_riwayat";
     $dbField[30] = "reg_batal_tracer";
     $dbField[31] = "reg_batal_kode_trans";
     $dbField[32] = "reg_batal_alasan";

     $dbValue[0] = QuoteValue(DPE_CHAR,$regId = $dtaccess->GetTransID());
     $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
     $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
     $dbValue[3] = QuoteValue(DPE_CHAR,$reg["id_cust_usr"]);
     $dbValue[4] = QuoteValue(DPE_CHAR,$reg["reg_status"]);
     $dbValue[5] = QuoteValue(DPE_CHAR,$userLogin["name"]);
     $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
     $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$reg["reg_jenis_pasien"]);
     $dbValue[8] = QuoteValue(DPE_CHAR,$reg["reg_status_pasien"]);
     $dbValue[9] = QuoteValue(DPE_CHAR,$reg["reg_rujukan_id"]);
     $dbValue[10] = QuoteValue(DPE_CHAR,$reg["reg_tipe_rawat"]);
     $dbValue[11] = QuoteValue(DPE_CHAR,$reg["id_poli"]);
     $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
     $dbValue[13] = QuoteValue(DPE_CHAR,$reg["reg_shift"]);
     $dbValue[14] = QuoteValue(DPE_CHAR,$reg["reg_tipe_layanan"]);
     $dbValue[15] = QuoteValue(DPE_CHAR,$reg["reg_sebab_sakit"]);
     $dbValue[16] = QuoteValue(DPE_CHAR,$reg["id_dokter"]);
     $dbValue[17] = QuoteValue(DPE_CHAR,$reg["reg_diagnosa_awal"]);
     $dbValue[18] = QuoteValue(DPE_CHAR,$reg["id_pembayaran"]);
     $dbValue[19] = QuoteValue(DPE_CHAR,$reg["reg_tingkat_kegawatan"]);
     $dbValue[20] = QuoteValue(DPE_CHAR,$reg["id_poli_asal"]);
     $dbValue[21] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur"]);
     $dbValue[22] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur_bulan"]);
     $dbValue[23] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur_hari"]);
     $dbValue[24] = QuoteValue(DPE_CHAR,$reg["reg_kelas"]); # ikut conf rs
     $dbValue[25] = QuoteValue(DPE_CHAR,$reg["reg_prosedur_masuk"]);
     $dbValue[26] = QuoteValue(DPE_CHAR,$reg["reg_tracer_registrasi"]);
     $dbValue[27] = QuoteValue(DPE_CHAR,$reg["reg_tracer_barcode"]);
     $dbValue[28] = QuoteValue(DPE_CHAR,$reg["reg_tracer_barcode_besar"]);
     $dbValue[29] = QuoteValue(DPE_CHAR,$reg["reg_tracer_riwayat"]);
     $dbValue[30] = QuoteValue(DPE_CHAR,$reg["reg_tracer"]);
     $dbValue[31] = QuoteValue(DPE_CHAR,$reg["reg_kode_trans"]);
     $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["alasan"]);
    // print_r($dbValue);

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     $dtmodel->Insert() or die("insert error");        

     $sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$id);
     $result = $dtaccess->Execute($sql);

     if ($result){
          echo json_encode(array('success'=>true));
     } else {
          echo json_encode(array('errorMsg'=>'Some errors occured.'));
     }


}else if($stat != '0' && $stat != '1'){
     echo json_encode(array('errorMsg'=>'Pasien Telah Dilayani di poli.'));
}
else {

     $sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$id);
     $reg = $dtaccess->Fetch($sql);

     #insert ke klinik registrasi batal
     $dbTable = "klinik.klinik_registrasi_batal";

     $dbField[0] = "reg_batal_id";   // PK
     $dbField[1] = "reg_batal_tanggal";
     $dbField[2] = "reg_batal_waktu";
     $dbField[3] = "id_cust_usr";
     $dbField[4] = "reg_batal_status";
     $dbField[5] = "reg_batal_who_update";
     $dbField[6] = "reg_batal_when_update";
     $dbField[7] = "reg_batal_jenis_pasien";
     $dbField[8] = "reg_batal_status_pasien";
     $dbField[9] = "reg_batal_rujukan_id";         
     $dbField[10] = "reg_batal_tipe_rawat";
     $dbField[11] = "id_poli";
     $dbField[12] = "id_dep";
     $dbField[13] = "reg_batal_shift";
     $dbField[14] = "reg_batal_tipe_layanan";
     $dbField[15] = "reg_batal_sebab_sakit";
     $dbField[16] = "id_dokter";
     $dbField[17] = "reg_batal_diagnosa_awal";
     $dbField[18] = "id_pembayaran";
     $dbField[19] = "reg_batal_tingkat_kegawatan";
     $dbField[20] = "id_poli_asal";
     $dbField[21] = "reg_batal_umur";
     $dbField[22] = "reg_batal_umur_bulan";
     $dbField[23] = "reg_batal_umur_hari";
     $dbField[24] = "reg_batal_kelas";
     $dbField[25] = "reg_batal_prosedur_masuk";
     $dbField[26] = "reg_batal_tracer_registrasi";
     $dbField[27] = "reg_batal_tracer_barcode";
     $dbField[28] = "reg_batal_tracer_barcode_besar";
     $dbField[29] = "reg_batal_tracer_riwayat";
     $dbField[30] = "reg_batal_tracer";
     $dbField[31] = "reg_batal_kode_trans";
     $dbField[32] = "reg_batal_alasan";

     $dbValue[0] = QuoteValue(DPE_CHAR,$regId = $dtaccess->GetTransID());
     $dbValue[1] = QuoteValue(DPE_DATE,date("Y-m-d"));
     $dbValue[2] = QuoteValue(DPE_DATE,date("H:i:s"));
     $dbValue[3] = QuoteValue(DPE_CHAR,$reg["id_cust_usr"]);
     $dbValue[4] = QuoteValue(DPE_CHAR,$reg["reg_status"]);
     $dbValue[5] = QuoteValue(DPE_CHAR,$userLogin["name"]);
     $dbValue[6] = QuoteValue(DPE_DATE,date("Y-m-d H:i:s"));
     $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$reg["reg_jenis_pasien"]);
     $dbValue[8] = QuoteValue(DPE_CHAR,$reg["reg_status_pasien"]);
     $dbValue[9] = QuoteValue(DPE_CHAR,$reg["reg_rujukan_id"]);
     $dbValue[10] = QuoteValue(DPE_CHAR,$reg["reg_tipe_rawat"]);
     $dbValue[11] = QuoteValue(DPE_CHAR,$reg["id_poli"]);
     $dbValue[12] = QuoteValue(DPE_CHAR,$depId);
     $dbValue[13] = QuoteValue(DPE_CHAR,$reg["reg_shift"]);
     $dbValue[14] = QuoteValue(DPE_CHAR,$reg["reg_tipe_layanan"]);
     $dbValue[15] = QuoteValue(DPE_CHAR,$reg["reg_sebab_sakit"]);
     $dbValue[16] = QuoteValue(DPE_CHAR,$reg["id_dokter"]);
     $dbValue[17] = QuoteValue(DPE_CHAR,$reg["reg_diagnosa_awal"]);
     $dbValue[18] = QuoteValue(DPE_CHAR,$reg["id_pembayaran"]);
     $dbValue[19] = QuoteValue(DPE_CHAR,$reg["reg_tingkat_kegawatan"]);
     $dbValue[20] = QuoteValue(DPE_CHAR,$reg["id_poli_asal"]);
     $dbValue[21] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur"]);
     $dbValue[22] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur_bulan"]);
     $dbValue[23] = QuoteValue(DPE_NUMERICKEY,$reg["reg_umur_hari"]);
     $dbValue[24] = QuoteValue(DPE_CHAR,$reg["reg_kelas"]); # ikut conf rs
     $dbValue[25] = QuoteValue(DPE_CHAR,$reg["reg_prosedur_masuk"]);
     $dbValue[26] = QuoteValue(DPE_CHAR,$reg["reg_tracer_registrasi"]);
     $dbValue[27] = QuoteValue(DPE_CHAR,$reg["reg_tracer_barcode"]);
     $dbValue[28] = QuoteValue(DPE_CHAR,$reg["reg_tracer_barcode_besar"]);
     $dbValue[29] = QuoteValue(DPE_CHAR,$reg["reg_tracer_riwayat"]);
     $dbValue[30] = QuoteValue(DPE_CHAR,$reg["reg_tracer"]);
     $dbValue[31] = QuoteValue(DPE_CHAR,$reg["reg_kode_trans"]);
     $dbValue[32] = QuoteValue(DPE_CHAR,$_POST["alasan"]);
    // print_r($dbValue);

     $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
     $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
     $dtmodel->Insert() or die("insert error");        

     $sql = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$id);
     $result = $dtaccess->Execute($sql);

     # update rawat inap
     if ($reg["reg_tipe_rawat"]=="I") {
          $sql = "select * from klinik.klinik_rawat_inap_history where id_reg= ".QuoteValue(DPE_CHAR, $id);
          $irna = $dtaccess->Fetch($sql);

          $sqlUpdate = "update klinik_kamar_bed set bed_reserved='n' where bed_id = ".QuoteValue(DPE_CHAR,$irna["rawat_inap_history_bed_tujuan"]);
          $dtaccess->Execute($sqlUpdate,DB_SCHEMA_KLINIK);
     }

     if ($result){
          echo json_encode(array('success'=>true));
     } else {
          echo json_encode(array('errorMsg'=>'Some errors occured.'));
     } 
}

?>