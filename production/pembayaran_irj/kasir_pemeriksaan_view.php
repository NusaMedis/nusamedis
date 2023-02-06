<?php
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");
require_once($LIB . "expAJAX.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userId = $auth->GetUserId();
$userName = $auth->GetUserName();
$depNama = $auth->GetDepNama();
$plx = new expAJAX("KurangBayar");

if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}

//if(!$auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE) && !$auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)){
//     die("access_denied");
//     exit(1);
//} else if($auth->IsAllowed("kassa_loket_kasir_irj",PRIV_CREATE)===1 || $auth->IsAllowed("sirs_flow_kassa_irj",PRIV_CREATE)===1){
//     echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Login First'</script>";
//     exit(1);
//}

//AJAX KURANG BAYAR
function KurangBayar($id)
{
  global $dtaccess, $depId, $view, $auth, $table;

  $sql = "select * from klinik.klinik_pembayaran where id_cust_usr=" . QuoteValue(DPE_CHAR, $id) . " 
                  and (pembayaran_flag='k' or pembayaran_flag='p' or pembayaran_total<>pembayaran_yg_dibayar)";
  $kurang = $dtaccess->Fetch($sql);

  if ($kurang["pembayaran_id"]) {
    return format_date($kurang["pembayaran_tanggal"]);
  } else {
    return 0;
  }
}


$_x_mode = "New";
$thisPage = "kasir_pemeriksaan_view.php";
$editPage = "kasir_pemeriksaan_proses.php";
$cicilanPage = "kasir_pemeriksaan_proses_cicilan.php";
$bayarCicilan = "kasir_pemeriksaan_proses_byr_cicilan.php";

// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
$_POST["dep_kasir_reg_bayar"] = $konfigurasi["dep_kasir_reg_bayar"];
$_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
$_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];

$table = new InoTable("table", "100%", "left");
$skr = date("d-m-Y");

if ($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
if ($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];
if (!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
if (!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;

if ($_POST["poli_tipe"]) {
  $id_poli_tipe = $_POST["poli_tipe"];
  $_POST["poli_tipe"] = $_POST["poli_tipe"];
}

if ($_GET["ResponseTime"]) {
  # code...

   #cek apakah sudah diinsert atau belum
   $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$_GET["id_reg"]);
   $rs = $dtaccess->Fetch($sql);

   if (!$rs["prev"]) {
      #cari waktu di buat terakhir
      $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " .QuoteValue(DPE_CHAR,$_GET["id_reg"]);
      $sql .= " order by klinik_waktu_tunggu_when_create desc ";
      $rs = $dtaccess->Fetch($sql);
      $durasi = durasi($rs["prev"], date("Y-m-d H:i:s"));
      $durasiDetik = durasiDetik($rs["prev"], date("Y-m-d H:i:s"));
      // echo $rs["prev"]; 
      //echo $durasi;
      //die();




      // ---- insert ke klinik waktu tunggu ----
      $dbTable = "klinik.klinik_waktu_tunggu";

      $dbField[0] = "klinik_waktu_tunggu_id";   // PK
      $dbField[1] = "id_reg";
      $dbField[2] = "id_cust_usr";
      $dbField[3] = "klinik_waktu_tunggu_when_create";
      $dbField[4] = "klinik_waktu_tunggu_who_create";
      $dbField[5] = "klinik_waktu_tunggu_status";
      $dbField[6] = "klinik_waktu_tunggu_status_keterangan";
      $dbField[7] = "id_poli";
      $dbField[8] = "klinik_waktu_tunggu_durasi";
      $dbField[9] = "klinik_waktu_tunggu_durasi_detik";
      $dbField[10] = "id_waktu_tunggu_status";

      $waktuTungguId = $dtaccess->GetTransID();


      $dbValue[0] = QuoteValue(DPE_CHAR, $waktuTungguId);
      $dbValue[1] = QuoteValue(DPE_CHAR, $_GET["id_reg"]);
      $dbValue[2] = QuoteValue(DPE_CHAR, $_GET["cust_usr_id"]);
      $dbValue[3] = QuoteValue(DPE_DATE, date("Y-m-d H:i:s"));
      $dbValue[4] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[5] = QuoteValue(DPE_CHAR, "K0");
      $dbValue[6] = QuoteValue(DPE_CHAR, "Pasien di kasir");
      $dbValue[7] = QuoteValue(DPE_CHAR, $_GET["id_poli"]);
      $dbValue[8] = QuoteValue(DPE_CHAR, $durasi);
      $dbValue[9] = QuoteValue(DPE_NUMERIC, $durasiDetik);
      $dbValue[10] = QuoteValue(DPE_CHAR, "K0");

      //print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);

      $dtmodel->Insert() or die("insert  error");

      //update status di klinik registrasi
      // $sql = "update klinik.klinik_registrasi set reg_status = 'E1' where reg_id = ".
      // QuoteValue(DPE_CHAR,$regId);
      // $rs = $dtaccess->Execute($sql);
      //echo $sql;
      unset($dtmodel);
      unset($dbField);
      unset($dbValue);
      unset($dbKey);
   }
      
}

if ($_GET["poli_tipe"]) {
  $id_poli_tipe = $_GET["poli_tipe"];
  $_POST["poli_tipe"] = $_GET["poli_tipe"];
}

if ($_POST["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like" . QuoteValue(DPE_CHAR, "%" . $_POST["cust_usr_kode"] . "%");
//     if($_POST["cust_usr_nama"])  {
//      $sql_where[] = "(UPPER(b.cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%")." 
//                      or UPPER(h.fol_keterangan) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%").")";
//     }
if ($_POST["cust_usr_nama"]) {
  $sql_where[] = "(UPPER(b.cust_usr_nama) like '%" . strtoupper($_POST["cust_usr_nama"]) . "%' or UPPER(c.reg_keterangan) like '%" . strtoupper($_POST["cust_usr_nama"]) . "%')";
}
if ($_POST["id_poli"])  $sql_where[] = "c.id_poli =" . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
if ($_POST["reg_jenis_pasien"])  $sql_where[] = "c.reg_jenis_pasien =" . QuoteValue(DPE_CHAR, $_POST["reg_jenis_pasien"]);
if ($_POST["reg_tipe_rawat"] == 'N') $sql_where[] = "(d.poli_tipe = 'A' or d.poli_tipe = 'R' or d.poli_tipe = 'L') ";
elseif ($_POST["reg_tipe_rawat"] <> '--')  $sql_where[] = "d.poli_tipe =" . QuoteValue(DPE_CHAR, $_POST["reg_tipe_rawat"]);

if($_POST["reg_tipe_rawat"] == 'J')  $sql_where[] = "c.reg_status != 'E1' and reg_status != 'E9'";
else if($_POST["reg_tipe_rawat"] == 'G') $sql_where[] = "c.reg_status != 'G1'";
else if($_POST["reg_tipe_rawat"] == 'I') $sql_where[] = "(c.reg_status = 'I3' or c.reg_status = 'I4')";

$sql_where[] = "reg_tanggal_pulang >= " . QuoteValue(DPE_DATE, date_db($_POST["tanggal_awal"]));
$sql_where[] = "reg_tanggal_pulang <= " . QuoteValue(DPE_DATE, date_db($_POST["tanggal_akhir"]));


if ($_POST["jbayar"]) $sql_where[] = "i.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
if ($_POST["fol_lunas"] == 'y') {
  $sql_where[] = "a.pembayaran_total = a.pembayaran_yg_dibayar and pembayaran_flag <> 'n'";
}
if ($_POST["fol_lunas"] == 'n') {
  $sql_where[] = "a.pembayaran_total <> a.pembayaran_yg_dibayar";
}
// if ($_POST["fol_lunas"]=='y') {
//   $sql_where[] = "h.fol_nominal is not null";
// }
// if ($_POST["fol_lunas"]=='n') {
//   $sql_where[] = "a.pembayaran_total <> a.pembayaran_yg_dibayar";
// }

if ($sql_where[0])
  $sql_where = implode(" and ", $sql_where);


if ($_POST["btnLanjut"] || $_POST["btnExcel"]) {

  $sql = "select * from global.global_auth_poli where poli_tipe='P'";
  $rs = $dtaccess->Execute($sql);
  $poliOP = $dtaccess->Fetch($rs);

  // $sqlIf = ", ( case when
  //                 (select count(*) from klinik.klinik_registrasi 
  //                 where reg_status != 'A0' and reg_status != 'I9' and reg_utama = c.reg_id) 
  //                 = 
  //                 (select count(*) from klinik.klinik_registrasi 
  //                 where (reg_status = 'E2' or reg_status = 'R2' or reg_status = 'R4' or reg_status = 'G2') and reg_status != 'A0' and reg_status != 'I9' and reg_utama = c.reg_id)
  //                 then 'y' 
  //                 else 'Penunjang Belum Diselesaikan' 
  //                 end ) as status";

  // if($_POST["reg_tipe_rawat"] == 'I') 
  //  {  //Jika Rawat Inap
  $sql = "select a.pembayaran_id,a.pembayaran_total,a.pembayaran_flag,a.pembayaran_yg_dibayar, 
                   b.cust_usr_nama, b.cust_usr_kode,c.id_cust_usr,
                   c.id_dokter,c.reg_id,c.id_poli,c.reg_tanggal,c.reg_waktu,c.reg_keterangan,
                   c.reg_status, c.reg_jenis_pasien, c.reg_tipe_jkn, c.reg_tipe_rawat,
                   d.poli_nama, f.jenis_nama,h.fol_nominal, reg_tanggal_pulang, reg_waktu_pulang ".$sqlIf." 
                   from 
                   klinik.klinik_pembayaran a 
                   join klinik.klinik_registrasi c  on c.reg_id = a.id_reg
                   join global.global_customer_user b on c.id_cust_usr = b.cust_usr_id
                   left join global.global_auth_poli d on d.poli_id = c.id_poli
                   left join global.global_jenis_pasien f on c.reg_jenis_pasien = f.jenis_id
                   left join klinik.klinik_folio h on h.id_pembayaran_det = a.pembayaran_id
                   where  c.reg_batal is null and reg_status <> 'I9' ";
  $sql .= "  and c.id_poli<>" . QuoteValue(DPE_CHAR, $_POST["poli_tipe"]);
  $sql .= " and " . $sql_where . " order by c.reg_tanggal, c.reg_waktu desc";

  //echo $sql;
  
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
}

//echo $sql;

if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=kasir.xls');
}


$tableHeader = "&nbsp;Pembayaran Pasien";
$counterHeader = 0;

if (!$_POST["btnExcel"]) {
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Bayar";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Rinci";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Rincian Rinci";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  // $tbHeader[0][$counterHeader][TABLE_ISI] = "Mulai Response Time";
  // $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  // $counterHeader++;
}

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Registrasi";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pulang";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tagihan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
$counterHeader++;

for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {

  $jumHeader = $counterHeader;
  /*
          if ($dataTable[$i]["reg_tipe_rawat"]=='J' || $dataTable[$i]["reg_tipe_rawat"]=='G')
          { //Rawat Darurat dan Jalan
          $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                  id_reg=".QuoteValue(DPE_CHAR,$dataTable[$i]["reg_id"])."
                  and fol_lunas='n'";
          }
          else
          {  //Rawat Inap  */
  $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                  id_pembayaran=" . QuoteValue(DPE_CHAR, $dataTable[$i]["pembayaran_id"]) . "
                  and fol_lunas='n'";

  // }
  $rs = $dtaccess->Execute($sql);
  $total = $dtaccess->Fetch($rs);

  $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where 
                  id_pembayaran=" . QuoteValue(DPE_CHAR, $dataTable[$i]["pembayaran_id"]) . "
                  and fol_lunas='n' and id_biaya <> '9999999'";

  // }
  $rs = $dtaccess->Execute($sql);
  $totalJasa = $dtaccess->Fetch($rs);

  if ($_POST["dep_konf_bulat_ribuan"] == "y") {
    $totalint = substr(currency_format($total["total"]), -3);
    $selisih = 1000 - $totalint;
    if ($selisih <> 1000) {
      $_POST["bulat"] = $selisih;
    } else $_POST["bulat"] = 0;
    $totalHarga = $total["total"] + $_POST["bulat"];
  } else {
    if ($_POST["dep_konf_bulat_ratusan"] == "y") {
      $totalint = substr(currency_format($total["total"]), -2);
      $selisih = 100 - $totalint;
      if ($selisih <> 100) {
        $_POST["bulat"] = $selisih;
      } else $_POST["bulat"] = 0;
      $totalHarga = $total["total"] + $_POST["bulat"];
    } else {
      $totalHarga = $total["total"];
    }
  }

  $editPage = "kasir_pemeriksaan_proses.php?id_dokter=" . $dataTable[$i]["id_dokter"] . "&id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"] . '&id_cust_usr=' . $dataTable[$i]["id_cust_usr"] . '&id_poli=' . $dataTable[$i]["id_poli"]. '&tipe_rawat=' . $_POST["reg_tipe_rawat"];
  $viewPage = "kasir_lihat_proses.php?id_dokter=" . $dataTable[$i]["id_dokter"] . "&id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"];
  $kurangBayarPage = "kasir_pemeriksaan_kurang_bayar.php?id_dokter=" . $dataTable[$i]["id_dokter"] . "&id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"];
  $piutangPage = "kasir_pemeriksaan_view.php?piutang=1&id_dokter=" . $dataTable[$i]["id_dokter"] . "&id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"];
  $timePage = "kasir_pemeriksaan_view.php?ResponseTime=1&id_dokter=" . $dataTable[$i]["id_dokter"] . "&id_reg=" . $dataTable[$i]["reg_id"] . '&cust_usr_id=' . $dataTable[$i]["id_cust_usr"] . '&id_poli=' . $dataTable[$i]["id_poli"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"];
  if ($dataTable[$i]["reg_tipe_rawat"] == 'I' || $dataTable[$i]["reg_tipe_rawat"] == 'G') {
    $cetakRinciPage = "cetak_rincian_irna.php?id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"];
  } else {
    $cetakRinciPage = "cetak_rincian_irj.php?id_reg=" . $dataTable[$i]["reg_id"] . "&pembayaran_id=" . $dataTable[$i]["pembayaran_id"] . $dataTable[$i]["pembayaran_det_id"];
  }

  
  // if($dataTable[$i]['status'] == 'y'){
  $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $editPage . '"><img hspace="2" width="30" height="30" src="' . $ROOT . 'gambar/icon/cari.png" alt="Bayar" title="Bayar" border="0" onclick="javascript: return CekData(' . QuoteValue(DPE_CHAR, $cust[$data[$i]]) . ');" /></a>';
  // }
  // else{
  //   $tbContent[$i][$counter][TABLE_ISI] = '';
  // }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="' . $ROOT . 'gambar/cetak.png" style="cursor:pointer" alt="Rincian" title="Rincian" border="0" onClick="CekRincian(\'' . $dataTable[$i]["reg_id"] . "-" . $dataTable[$i]["pembayaran_id"] . '-' . $dataTable[$i]["reg_tipe_rawat"] . '\');"/>';
  //    }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  if ($dataTable[$i]["reg_tipe_rawat"] == 'I') {
    $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="' . $ROOT . 'gambar/master_icd.png" style="cursor:pointer" alt="Cetak Rincian IRNA" title="Cetak Rincian IRNA" border="0" onClick="ProsesCetakIRNA(\'' . $dataTable[$i]["reg_id"] . "-" . $dataTable[$i]["pembayaran_id"] . "-" . $dataTable[$i]["pembayaran_det_id"] . '\');"/>';
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

 $sql = "select id_pembayaran from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
  $CheckLunas = $dtaccess->Fetch($sql);
  $sql = "select pembayaran_diskon from klinik.klinik_pembayaran where pembayaran_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
  $dataDiskon = $dtaccess->Fetch($sql);
 $sql = "select klinik_waktu_tunggu_when_create as prev from klinik.klinik_waktu_tunggu where id_reg = " .QuoteValue(DPE_CHAR,$dataTable[$i]['reg_id'])." and id_waktu_tunggu_status='K1'  ";
 // echo $sql;
   $rs = $dtaccess->Fetch($sql);
  // if (!$rs["prev"]  and $dataTable[$i]["reg_tipe_rawat"]=="I") {
  //   // $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$timePage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/time.png" alt="Mulai Response Time" title="Pulang" border="0"/></a>';
  //   $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="34" height="34" src="' . $ROOT . 'gambar/icon/time.png" style="cursor:pointer" alt="Mulai Response Time" title="Response Time" border="0" onClick="respontime(\'' . $dataTable[$i]["reg_id"] . "-" . $dataTable[$i]["id_cust_usr"] . "-" . $dataTable[$i]["id_poli"] . '\');"/>';
  // } 
  // elseif (($CheckLunas['id_pembayaran'] <> '' && $totalHarga == 0) || $dataDiskon['pembayaran_diskon'] > 0) { 
  //   $tbContent[$i][$counter][TABLE_ISI] = " ";
  // }
  // //  elseif (($CheckLunas['id_pembayaran'] == '' && $totalHarga == 0)) { 
  // //   $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
  // // }
  // else{
  //   if ($dataTable[$i]['cust_usr_kode'] == '100') {
  //     $tbContent[$i][$counter][TABLE_ISI] = " ";
  //   }else{
  //     $tbContent[$i][$counter][TABLE_ISI] = " ";
  //   }
  // }
  // $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  // $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  // $counter++;



  $tbContent[$i][$counter][TABLE_ISI] = ($i + 1);
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  if ($dataTable[$i]["cust_usr_kode"] == "100" || $dataTable[$i]["cust_usr_kode"] == "500") {
    $sql = "select reg_keterangan, fol_keterangan from klinik.klinik_folio a
                    left join klinik.klinik_registrasi b on a.id_reg = b.reg_id where a.id_reg =" . QuoteValue(DPE_CHAR, $dataTable[$i]["reg_id"]);
    $rs = $dtaccess->Execute($sql);
    $namalain = $dtaccess->Fetch($rs);
    $sql = "select * from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$dataTable[$i]['reg_id']);
    $reg = $dtaccess->Fetch($sql);
    if ($namalain["fol_keterangan"] == '' || $namalain["fol_keterangan"] == null) {
      $tbContent[$i][$counter][TABLE_ISI] = $reg["reg_keterangan"];
    } else {
      $tbContent[$i][$counter][TABLE_ISI] = $namalain["fol_keterangan"];
    }
  } else //jIKA BUKAN KODE 500
  {
    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;


  $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal_pulang"])." ".$dataTable[$i]['reg_waktu_pulang'];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  //Label Tipe Rawat
  $TipeRawat["J"] = "Rawat Jalan";
  $TipeRawat["I"] = "Rawat Inap";
  $TipeRawat["G"] = "Rawat Darurat";
  $TipeRawat["A"] = "Farmasi";
  $TipeRawat["R"] = "Radiologi";
  $TipeRawat["L"] = "Laboratorium";
  $tbContent[$i][$counter][TABLE_ISI] = $TipeRawat[$dataTable[$i]["reg_tipe_rawat"]];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $JasaRS = 0.1 * $totalJasa['total'];
  if ($dataTable[$i]['reg_tipe_rawat'] == 'I') {
    $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalHarga + $JasaRS);
  } else {
    $tbContent[$i][$counter][TABLE_ISI] = currency_format($totalHarga);
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;


  /*
          if($dataTable[$i]["reg_jenis_pasien"]==PASIEN_BAYAR_BPJS && $dataTable[$i]["reg_tipe_jkn"]==PASIEN_BAYAR_BPJS_NON_PBI){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN Non PBI";
          } elseif($dataTable[$i]["reg_jenis_pasien"]==PASIEN_BAYAR_BPJS && $dataTable[$i]["reg_tipe_jkn"]==PASIEN_BAYAR_BPJS_PBI){
          $tbContent[$i][$counter][TABLE_ISI] = "JKN PBI";
          } elseif($lunas[$data[$i]]=='n' || $lunas[$data[$i]]==''){
          $tbContent[$i][$counter][TABLE_ISI] = "Belum Lunas";                         
          } elseif($dataTable[$i]["pembayaran_flag"]=='k'){
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas (Kurang Bayar)";
          } elseif($dataTable[$i]["pembayaran_flag"]=='p'){
          $tbContent[$i][$counter][TABLE_ISI] = "Piutang";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
          }*/


  $sql = "select id_pembayaran from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
  // echo $sql;
  $CheckLunas = $dtaccess->Fetch($sql);
  $sql = "select pembayaran_diskon from klinik.klinik_pembayaran where pembayaran_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
  $dataDiskon = $dtaccess->Fetch($sql);
  if ($totalHarga > 0) {
    // if($dataTable[$i]['status'] == 'y'){
      $tbContent[$i][$counter][TABLE_ISI] = "Belum Lunas";
    // }
    // else{
    //   $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]['status'];
    // }
  } 
  elseif (($CheckLunas['id_pembayaran'] <> '' && $totalHarga == 0) || $dataDiskon['pembayaran_diskon'] > 0) { 
    $tbContent[$i][$counter][TABLE_ISI] = "Lunas";
  }
  else{
    if ($dataTable[$i]['cust_usr_kode'] == '100') {
      $tbContent[$i][$counter][TABLE_ISI] = "Belum ada tagihan";
    }else{
      $tbContent[$i][$counter][TABLE_ISI] = "Transer Rawat Inap";
    }
  }
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;
  unset($totalHarga);
}

// cari jenis pasien e
$sql = "select * from global.global_jenis_pasien where jenis_id='2' and jenis_flag = 'y' order by jenis_nama desc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$jenisPasien = $dtaccess->FetchAll($rs);

// Data Kategori Rawat//
$sql = "select * from  global.global_auth_poli";
$sql .= " order by poli_tipe asc";
$rs = $dtaccess->Execute($sql);
$dataPoli = $dtaccess->FetchAll($rs);

//cari instalasi
$sql = "select * from global.global_auth_poli_tipe where poli_tipe_id = 'J' or poli_tipe_id = 'G' or poli_tipe_id = 'I' or poli_tipe_id = 'A'";
$sql .= " order by poli_tipe_nama";
$rs = $dtaccess->Execute($sql);
$dataInstalasi = $dtaccess->FetchAll($rs);

// cari data poliklinik
if (!$_POST['reg_tipe_rawat']) $sql_where_poli[] = "poli_tipe = 'J' ";
elseif ($_POST['reg_tipe_rawat'] == 'N') $sql_where_poli[] = "(poli_tipe = 'A' or poli_tipe = 'L' or poli_tipe = 'R' )";
elseif ($_POST['reg_tipe_rawat']) $sql_where_poli[] = "poli_tipe = " . QuoteValue(DPE_CHAR, $_POST['reg_tipe_rawat']);
$sql_poli = "select poli_nama, poli_id from  global.global_auth_poli";
if ($sql_where_poli) $sql_poli .= " where " . implode(" and ", $sql_where_poli);
$sql_poli .= " order by poli_tipe asc";
$rs_poli = $dtaccess->Execute($sql_poli);
$dataPolitipe = $dtaccess->FetchAll($rs_poli);
//echo $sql_poli;

// cari jenis bayar ee //
$sql = "select * from global.global_jenis_bayar where jbayar_status='y' and id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by jbayar_id";
$jsBayar = $dtaccess->FetchAll($sql);
?>
<?php if (!$_POST["btnExcel"]) { ?>
  <?php if ($cetak == "y") { ?>
    // if(confirm('Cetak Invoice?'))
    BukaWindow('tutup_kasir_cetak.php?tgl_awal=<?php echo $_POST["tanggal_awal"]; ?>&tgl_akhir=<?php echo $_POST["tanggal_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>&shift=<?php echo $_POST["shift"]; ?>&dokter=<?php echo $_POST["id_dokter"]; ?>&js_biaya=<?php echo $_POST["js_biaya"]; ?>&jbayar=<?php echo $_POST["jbayar"] ?>', '_blank');
    document.location.href='<?php echo $thisPage; ?>';
  <?php } ?>







  <!DOCTYPE html>
  <html lang="en">
  <?php require_once($LAY . "header.php") ?>
  <script type="text/javascript">
    function CekRincian(id) {
      var all_id = id.split('-');
      if (all_id[2] == 'G') {
        window.open('cetak_rincian_igd.php?id_reg=' + all_id[0] + '&pembayaran_id=' + all_id[1], 'Rincian Tagihan');
      } else if (all_id[2] == "J") {
        window.open('cetak_rincian_irj.php?id_reg=' + all_id[0] + '&pembayaran_id=' + all_id[1], 'Rincian Tagihan');
      } else if (all_id[2] == "I") {
        window.open('cetak_rincian_irna.php?id_reg=' + all_id[0] + '&pembayaran_id=' + all_id[1], 'Rincian Tagihan');
      }
    }

    function ProsesCetakIRNA(id) {
      //alert(id);

      var all_id = id.split('-');
      var link = 'edit_data_pasiene.php?usr_id=' + all_id[0] + '&id_reg=' + all_id[1] + '&kode=' + all_id[2];

      window.open('cetak_tagihan_rinci.php?id_reg=' + all_id[0] + '&pembayaran_id=' + all_id[1] + '&pembayaran_det_id=' + all_id[2], 'Cetak Kwitansi Rincian Rinci');
      //document.location.href='<?php echo $thisPage; ?>';
    }
         function respontime(id) {
      // body...
       // alert(id);
       var all_id = id.split('-');
       var id_reg=all_id[0];

         // var link = 'responsetime.php?id_reg=' + all_id[0] + '&id_cust_usr=' + all_id[1] + '&id_poli=' + all_id[2];
     
        var id_cust_usr=all_id[1];
        var id_poli=all_id[2];
       
        $.post("responsetime.php", {
            id_reg: id_reg,
            id_cust_usr: id_cust_usr,
            id_poli: id_poli,
            
        }).done(function(data) {
            if (data == 'y') {

                alert("Berhasil Menyimpan Data");
            }
             else if (data == 'n') {

                alert("Gagal Menyimpan Datan");
            } else {
                alert("Gagal Menyimpan Data");

            }
            // get_dataFormKonsultasi(id_reg);
            // location.reload();
        });
        // window.open(link);
      


    }
  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY . "sidebar.php") ?>

        <!-- top navigation -->
        <?php require_once($LAY . "topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Manajemen</h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Pembayaran </h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST" class="">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Masuk(DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo $_POST["tanggal_awal"] ?>" />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>

                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal Masuk(DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker2'>
                          <input id="tanggal_akhir" name="tanggal_akhir" type='text' class="form-control" value="<?php echo $_POST["tanggal_akhir"] ?>" />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
                        <input type="text" name="cust_usr_kode" id="cust_usr_kode" class="form-control" value="<?php echo $_POST["cust_usr_kode"]; ?>">


                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                        <input type="text" name="cust_usr_nama" id="cust_usr_nama" class="form-control" value="<?php echo $_POST["cust_usr_nama"]; ?>">
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat *</label>
                        <select name="reg_tipe_rawat" class="select2_single form-control" id="reg_tipe_rawat" required="" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event)" ;>
                          <option value="J" <?php if ($_POST["reg_tipe_rawat"] == "J") echo "selected" ?>>Rawat Jalan</option>;
                          <option value="I" <?php if ($_POST["reg_tipe_rawat"] == "I") echo "selected" ?>>Rawat Inap</option>
                          <option value="G" <?php if ($_POST["reg_tipe_rawat"] == "G") echo "selected" ?>>IGD</option>
                          <option value="N" <?php if ($_POST["reg_tipe_rawat"] == "N") echo "selected" ?>>Lain-lain</option>
                          <!-- <option value="R" <?php if ($_POST["reg_tipe_rawat"] == "R") echo "selected" ?>>Radiologi</option>
                              <option value="L" <?php if ($_POST["reg_tipe_rawat"] == "L") echo "selected" ?>>Laboratorium</option> -->

                        </select>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Poli Klinik </label>
                        <select name="id_poli" class="select2_single form-control" id="id_poli">
                          <option value="">Pilih Klinik</option>
                          <?php for ($i = 0, $n = count($dataPolitipe); $i < $n; $i++) { ?>
                            <option class="inputField" value="<?php echo $dataPolitipe[$i]["poli_id"]; ?>" <?php if ($_POST["id_poli"] == $dataPolitipe[$i]["poli_id"]) echo "selected" ?>><?php echo $dataPolitipe[$i]["poli_nama"]; ?>&nbsp;</option>
                          <?php } ?>
                        </select>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar </label>

                        <select name="reg_jenis_pasien" class="select2_single form-control" id="reg_jenis_pasien">
                          <option value="2" <?php if ($_POST['reg_jenis_pasien'] == '2') echo "selected"; ?>>1. Umum</option>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Bayar</label>
                        <select name="jbayar" class="select2_single form-control" id="jbayar" onKeyDown="return tabOnEnter(this, event);">
                          <option class="inputField" value="">[ Pilih Jenis Bayar ]</option>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($jsBayar); $i < $n; $i++) {
                            unset($spacer);
                            $length = (strlen($jsBayar[$i]["jbayar_id"]) / TREE_LENGTH_CHILD) - 1;
                            for ($j = 0; $j < $length; $j++) $spacer .= "..";
                          ?>
                            <option value="<?php echo $jsBayar[$i]["jbayar_id"]; ?>" <?php if ($_POST["jbayar"] == $jsBayar[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer . " " . $jsBayar[$i]["jbayar_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success" style="display:none;">
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-5 col-sm-5 col-xs-5 btn btn-success">
                      </div>
                      <div class="clearfix"></div>
                      <? if ($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]) { ?>
                      <? } ?>
                      <? if ($_x_mode == "Edit") { ?>
                        <?php echo $view->RenderHidden("kategori_tindakan_id", "kategori_tindakan_id", $biayaId); ?>
                      <? } ?>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- //row filter -->


            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                          <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI]; ?> </th>
                        <? } ?>
                      </tr>
                    </thead>
                    <tbody>
                      <? for ($i = 0, $n = count($dataTable); $i < $n; $i++) {   ?>

                        <tr class="even pointer">
                          <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI] ?></td>
                          <? } ?>

                        </tr>

                      <? } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /page content -->

      <!-- footer content -->
      <?php require_once($LAY . "footer.php") ?>
      <!-- /footer content -->
    </div>
    </div>

    <?php require_once($LAY . "js.php") ?>

  </body>

  </html>

  </script>
  <script type="text/javascript" language="JavaScript">
    <? $plx->Run(); ?>

    var mTimer;

    function CekData(id) {
      var hasil;
      //alert(id);
      hasil = KurangBayar(id, 'type=r');
      if (hasil != 0) {
        if (id < > '500' || id < > '100') {
          alert('Pasien ada tunggakan tanggal ' + hasil);
          return false;
        }
      } else {
        return true;
      }
      //SetPerawatan(id,loket,'type=r');
    }
  </script>

<? } ?>