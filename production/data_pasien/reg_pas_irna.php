<?php
require_once("../penghubung.inc.php");
require_once($LIB."bit.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."tree.php");
require_once($LIB."expAJAX.php");
require_once($LIB."tampilan.php");
require_once($LIB."currency.php");
require_once($LIB."upload.php");

$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$err_code = 0;
$userData = $auth->GetUserData();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();

/* INSERT RAWAT INAP */
$dbTable = "klinik_rawatinap";

  $dbField[0] = "rawatinap_id";   // PK
  $dbField[1] = "id_reg";
  $dbField[2] = "id_kategori_kamar";
  $dbField[3] = "id_kamar";
  $dbField[4] = "id_bed";
  $dbField[5] = "rawatinap_tanggal_masuk";
  $dbField[6] = "rawatinap_waktu_masuk";
  $dbField[7] = "rawatinap_jenis_pasien";
  $dbField[8] = "rawatinap_asal_instalasi";
  $dbField[9] = "id_gedung_rawat";

  $rawatinap_id = $dtaccess->GetTransID("klinik_rawatinap","rawatinap_id",DB_SCHEMA_KLINIK);

  $dbValue[0] = QuoteValue(DPE_CHAR,$rawatinap_id);   // PK
  $dbValue[1] = QuoteValue(DPE_CHAR,$regId);
  $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
  $dbValue[3] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
  $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
  $dbValue[5] = QuoteValue(DPE_DATE,date("Y-m-d"));
  $dbValue[6] = QuoteValue(DPE_DATE,date("H:i:s"));
  $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
  $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["asal_pasien"]);
  $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_gedung_rawat"]);

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);
  $dtmodel->Insert() or die("insert  error");

  unset($dtmodel);
  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  /* INSERT RAWAT INAP */

  /* INSERT RAWAT INAP HISTORY */
  $dbTable = "klinik_rawat_inap_history";

  $dbField[0] = "rawat_inap_history_id";   // PK
  $dbField[1] = "rawat_inap_history_who_update";
  $dbField[2] = "rawat_inap_history_when_update";
  $dbField[3] = "rawat_inap_history_tanggal";
  $dbField[4] = "id_reg";
  $dbField[5] = "rawat_inap_history_kelas_tujuan";
  $dbField[6] = "rawat_inap_history_status";
  $dbField[7] = "rawat_inap_history_jenis_pasien";
  $dbField[8] = "rawat_inap_history_kamar_tujuan";
  $dbField[9] = "rawat_inap_history_bed_tujuan";
  $dbField[10] = "rawat_inap_history_gedung_tujuan";
  $dbField[11] = "id_rawatinap";

  $rawat_inap_history_id = $dtaccess->GetTransID("klinik_rawat_inap_history","rawat_inap_history_id",DB_SCHEMA_KLINIK);

  $dbValue[0] = QuoteValue(DPE_CHAR,$rawat_inap_history_id);   // PK
  $dbValue[1] = QuoteValue(DPE_CHAR,$userName);
  $dbValue[2] = QuoteValue(DPE_CHAR,date("Y-m-d H:i:s"));
  $dbValue[3] = QuoteValue(DPE_DATE,date("Y-m-d"));
  $dbValue[4] = QuoteValue(DPE_CHAR,$regId);
  $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["id_kelas"]);
  $dbValue[6] = QuoteValue(DPE_CHAR,A);    // a = awal, p = pulang, t = transfer
  $dbValue[7] = QuoteValue(DPE_NUMERICKEY,$_POST["reg_jenis_pasien"]);
  $dbValue[8] = QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
  $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_bed"]);
  $dbValue[10] = QuoteValue(DPE_CHAR,$_POST["id_gedung_rawat"]);
  $dbValue[11] = QuoteValue(DPE_CHAR,$rawatinap_id);

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey,DB_SCHEMA_KLINIK);

  $dtmodel->Insert() or die("insert  error");

  unset($dtmodel);
  unset($dbTable);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);
  /* INSERT RAWAT INAP HISTORY */

  /* UPDATE BED TERPAKAI */  
  $sqlUpdate = "update klinik_kamar_bed set bed_reserved='y' where bed_id = ".QuoteValue(DPE_CHAR,$_POST["id_bed"]);
  $dtaccess->Execute($sqlUpdate,DB_SCHEMA_KLINIK);
  /* UPDATE BED TERPAKAI */  

  /* Pengecekan Kamar */
  $sql = "select * from klinik.klinik_kamar where kamar_id=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
  $poli = $dtaccess->Fetch($sql);
  /* Pengecekan Kamar */

  /* UPDATE REGISTRASI */
  $sqlKelas = "update klinik_registrasi set reg_kelas=".QuoteValue(DPE_CHAR,$_POST["id_kelas"]).",reg_no_sep=".QuoteValue(DPE_CHAR,$_POST["reg_no_sep"]).",hak_kelas_inap=".QuoteValue(DPE_CHAR,$_POST["id_kelas"]).", id_poli=".QuoteValue(DPE_CHAR,$poli["id_poli"])." where reg_id = ".QuoteValue(DPE_CHAR,$regId);
  $dtaccess->Execute($sqlKelas,DB_SCHEMA_KLINIK); 
  /* UPDATE REGISTRASI */
  
  /* Pengecekan BED */
  $sql = "select count(bed_kode) as bed from klinik.klinik_kamar_bed where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"])." and bed_reserved='y' and bed_keterangan='n'";
  $bed = $dtaccess->Fetch($sql); 
  /* Pengecekan BED */

  /* Pengecekan Jumlah BED */
  $sql = "select count(bed_kode) as total from klinik.klinik_kamar_bed where bed_reserved='y' and bed_keterangan='n'";
  $total = $dtaccess->Fetch($sql);

  $persen = ($total["total"]/150)*100;
  /* Pengecekan Jumlah BED */

  /* Pengecekan Bor Kamar */
  $sql = "select * from klinik.klinik_rawat_inap_bor_kamar where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
  $kamar = $dtaccess->Fetch($sql);

  $sisaBed = $kamar["jml_bed"]-$bed["bed"];
  /* Pengecekan Bor Kamar */

  /* UPDATE BOR KAMAR */
  $sql = "update klinik.klinik_rawat_inap_bor_kamar set bed_terpakai=".QuoteValue(DPE_NUMERIC,StripCurrency($bed["bed"])).", bed_tersisa=".QuoteValue(DPE_NUMERIC,Stripcurrency($sisaBed))." where id_kamar=".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
  $dtaccess->Execute($sql);
  /* UPDATE BOR KAMAR */
  
  /* Pengecekan Registrasi Asal */ 
 // $sql = "select count(*) as total from klinik.klinik_registrasi where id_cust_usr = '$custUsrId' and reg_tipe_rawat = '$_POST[asal_pasien]' and reg_status_kondisi='2' and reg_utama is null ";
 // // echo $sql;
 // //    $rsNum = $dtaccess->Execute($sql);
 //    $numRows = $dtaccess->Fetch($sql);

  $sql = "select reg_id,id_pembayaran,reg_jenis_pasien from klinik.klinik_registrasi where id_cust_usr = '$custUsrId' and reg_tipe_rawat = '$_POST[asal_pasien]' and  reg_utama is null and id_pembayaran is not null order by reg_tanggal desc,reg_waktu desc ";
  $reg_id_lama = $dtaccess->Fetch($sql);

  $sql = "select reg_id,id_pembayaran,reg_jenis_pasien from klinik.klinik_registrasi where id_cust_usr = '$custUsrId' and reg_utama ='$reg_id_lama[reg_id]' order by reg_tanggal desc";
  $reg_id_lab = $dtaccess->Fetch($sql);
  
  
  /* UPDATE TINDAKAN SEBELUMNYA */
    // if ($reg_id_lama['reg_jenis_pasien'] != ''  ) {
    // $sql = "update klinik.klinik_folio set id_reg = '$regId', id_pembayaran ='$byrId' where fol_lunas = 'n' and id_pembayaran = '$reg_id_lama[id_pembayaran]' ";
  $sql = "update klinik.klinik_folio set id_pembayaran ='$byrId', is_transfer = 'y' where fol_lunas = 'n' and id_pembayaran = '$reg_id_lama[id_pembayaran]' ";
    // echo $sql;
  $dtaccess->Execute($sql);

    // $sql = "update klinik.klinik_registrasi set id_pembayaran ='$byrId' where reg_utama = '$reg_id_lama[reg_id]' ";
    // $dtaccess->Execute($sql);
  $sql = "update klinik.klinik_registrasi set  id_pembayaran ='$byrId' where reg_id='$reg_id_lab[reg_id]' and id_pembayaran = '$reg_id_lab[id_pembayaran]' ";
      // echo $sql;
  $dtaccess->Execute($sql);


  $sql = "update apotik.apotik_penjualan set id_pembayaran = '$byrId' where id_pembayaran = '$reg_id_lama[id_pembayaran]'";
  $dtaccess->Execute($sql);
  /* UPDATE TINDAKAN SEBELUMNYA */
  // }

  //update general concen ranap
  
  $sql="update klinik.klinik_igd_serialize set id_reg='$regId' where id_reg='$reg_id_lama[reg_id]' and tab='general_consern_ranap'";
  $dtaccess->Execute($sql);
?>