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

$view       = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess   = new DataAccess();
$auth       = new CAuth();
$depId      = $auth->GetDepId();
$userName   = $auth->GetUserName();
$userId     = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$depNama    = $auth->GetDepNama();

// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
$_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];
$_POST["dep_posting_poli"] = $konfigurasi["dep_posting_poli"];
$_POST["dep_posting_split"] = $konfigurasi["dep_posting_split"];
$_POST["dep_konf_bulat_ribuan"] = $konfigurasi["dep_konf_bulat_ribuan"];
$_POST["dep_konf_bulat_ratusan"] = $konfigurasi["dep_konf_bulat_ratusan"];
$_POST["dep_posting_beban"] = $konfigurasi["dep_posting_beban"];
$_POST["dep_cetak_rincian"] = $konfigurasi["dep_cetak_rincian"];

$_x_mode = "New";
$thisPage = "kasir_pemeriksaan_proses.php?id_dokter=" . $_REQUEST['id_dokter'] . "&id_reg=" . $_REQUEST['id_reg'] . "&pembayaran_id=" . $_REQUEST['pembayaran_id'];
$delPage = "kasir_pemeriksaan_proses.php?";
$backPage = "kasir_pemeriksaan_view.php";
$table = new InoTable("table", "100%", "left");


if ($_GET["id_dokter"]) $_POST["id_dokter"] = $_GET["id_dokter"];
if ($_GET["id_poli"]) $_POST["id_poli"] = $_GET["id_poli"];
if ($_GET["reg_jenis_pasien"]) $_POST["reg_jenis_pasien"] = $_GET["reg_jenis_pasien"];

//cari data untuk diskonnya 
$sql = "select * from ar_ap.diskon where id_reg =" . QuoteValue(DPE_CHAR, $_GET['id_reg']) . "and pembayaran_id=" . QuoteValue(DPE_CHAR, $_GET['pembayaran_id']);
$rs = $dtaccess->Execute($sql);
$dataDiskon = $dtaccess->FetchAll($rs);

//  Tambah data diskon
if ($_POST['btnDiskon']) {
  $dbTable = "ar_ap.diskon";
  $dbField[0] = "id_diskon";   // PK
  $dbField[1] = "diskon_nama";
  $dbField[2] = "diskon_nominal";
  $dbField[3] = "id_reg";
  $dbField[4] = "pembayaran_id";

  $id = $dtaccess->GetTransId();

  $dbValue[0] = QuoteValue(DPE_CHAR, $id);
  $dbValue[1] = QuoteValue(DPE_CHAR, $_POST['diskon_nama']);
  $dbValue[2] = QuoteValue(DPE_NUMERIC, (int)str_replace(',', '', $_POST['txtdiskon1']));
  $dbValue[3] = QuoteValue(DPE_CHAR, $_POST['id_reg']);
  $dbValue[4] = QuoteValue(DPE_CHAR, $_POST['pembayaran_id']);
  // print_r($dbValue);die();
  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
  $dtmodel->Insert() or die("insert  error");

  unset($dbField);
  unset($dbValue);
  header("location:" . $thisPage);
  exit();
}
if ($_GET['kembali']) {
  # code...
  // $sql="delete from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg =".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
  //   // echo $sql;
  // $dtaccess->Execute($sql);
      //$kembali = "index.php";
  header("location:kasir_pemeriksaan_view.php");
  exit();
}


// hapus diskon
if ($_GET['del']) {
  $sql = "delete FROM ar_ap.diskon where id_diskon =" . QuoteValue(DPE_CHAR, $_GET['id_diskon']);
  $rs = $dtaccess->Execute($sql);
  header("location:" . $thisPage);
  exit();
}

//UNTUK DATA AWAL
if ($_GET["id_reg"] || $_GET["pembayaran_id"]) {
  $sql = "select a.reg_jenis_pasien, a.reg_tipe_rawat, a.reg_tipe_jkn, a.id_poli, a.id_dokter, a.id_cust_usr, a.id_perusahaan,
  a.id_jamkesda_kota, a.reg_tipe_layanan, a.id_poli, a.reg_tipe_paket, 
  a.reg_tipe_layanan, a.reg_shift, b.pembayaran_dijamin, c.cust_usr_id, 
  c.cust_usr_alamat, c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_jenis_kelamin, 
  c.cust_usr_foto,  ((current_date - c.cust_usr_tanggal_lahir)/365) as umur, c.cust_usr_jkn,   
  d.fol_keterangan, e.perusahaan_diskon, e.perusahaan_plafon, f.* from  
  klinik.klinik_registrasi a 
  left join klinik.klinik_pembayaran b on b.pembayaran_id = a.id_pembayaran 
  join  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id 
  left join klinik.klinik_folio d on d.id_reg=a.reg_id
  left join global.global_perusahaan e on e.perusahaan_id=a.id_perusahaan
  left join global.global_jamkesda_kota f on f.jamkesda_kota_id=a.id_jamkesda_kota
  where a.reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);
  $rs_pasien = $dtaccess->Execute($sql);
  $dataPasien = $dtaccess->Fetch($sql);

  $_POST['fol_id'] = $_GET["fol_id"];
  $_POST["id_reg"] = $_GET["id_reg"];
  $_POST["id_biaya"] = $_GET["biaya"];
  $_POST["pembayaran_id"] = $_GET["pembayaran_id"];

  $view->CreatePost($dataPasien);
  //Keterangan Pembayaran Default
  if ($dataPasien["reg_tipe_rawat"] == 'J') $_POST["pembayaran_det_ket"] = "Pembayaran Tagihan Rawat Jalan";
  if ($dataPasien["reg_tipe_rawat"] == 'I') $_POST["pembayaran_det_ket"] = "Pembayaran Tagihan Rawat Inap";
  if ($dataPasien["reg_tipe_rawat"] == 'G') $_POST["pembayaran_det_ket"] = "Pembayaran Tagihan Rawat Darurat";

  $lokasi = $ROOT . "gambar/foto_pasien";


  $sql = "select deposit_nominal from  
  klinik.klinik_deposit where id_cust_usr = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_id"]);
  $rs_pasien = $dtaccess->Execute($sql);
  $dataDeposit = $dtaccess->Fetch($rs_pasien);

  // untuk cek apakah dia ranap / rajal
  $sql = "select reg_id from klinik.klinik_registrasi where id_cust_usr = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_id"]) . " and reg_status like '%I%' and reg_id=" . QuoteValue(DPE_CHAR, $_POST["id_reg"]);
  $rest = $dtaccess->Execute($sql);
  $cek = $dtaccess->Fetch($rest);

  if ($cek['reg_id']) {
    $_POST["deposit_nominal"] = $dataDeposit["deposit_nominal"];
  } else {
    $_POST["deposit_nominal"] = "0";
  }
}


//AMBIL GRAND TOTAL
/* Yang Lama ambil dari pembayaran
     $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
             global.global_auth_user b on a.id_dokter = b.usr_id
             left join global.global_auth_poli c on a.id_poli = c.poli_id
             left join global.global_auth_user_poli d on d.id_poli = a.id_poli 
             
             where d.id_usr = ".QuoteValue(DPE_CHAR,$userId)." and a.fol_lunas='n' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$_POST["pembayaran_id"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by a.fol_waktu asc"; 
     */
//  echo $sql;    

/*if ($_POST["reg_tipe_rawat"]=='J' or $_POST["reg_tipe_rawat"]=='G')
     { // JIka Rawat Jalan atau Rawat Darurat
     $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
             global.global_auth_user b on a.id_dokter = b.usr_id
             left join global.global_auth_poli c on a.id_poli = c.poli_id
             where  a.fol_lunas='n' and a.id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." 
             and a.id_dep=".QuoteValue(DPE_CHAR,$depId)." order by a.fol_waktu asc"; 
    }
    else
    { // JIka Rawat Inap  */
      $sql = "select a.*,b.usr_name,c.poli_nama from  klinik.klinik_folio a left join 
      global.global_auth_user b on a.id_dokter = b.usr_id
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      where  a.fol_lunas='n' and a.id_pembayaran = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]) . " 
      order by a.fol_waktu asc";


// }
//echo $sql;
      $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
      $dataTable = $dtaccess->FetchAll($rs_edit);


      for ($i = 0, $n = count($dataTable); $i < $n; $i++) {

        $total = $dataTable[$i]["fol_nominal"];
        $totalBiaya = $totalBiaya + $dataTable[$i]["fol_nominal"];
        if ($dataTable[$i]["id_biaya"] <> "9999999") $totalBiayaNonFarmasi = $totalBiayaNonFarmasi + $dataTable[$i]["fol_nominal"];
        $dijamin = $dataTable[$i]["fol_dijamin"];

  //Jika Paket Sementara ditutup
  //if($dataTable[$i]["biaya_paket"]=="n")
  //{
  //$totalNonPaket += $dataTable[$i]["fol_nominal"];
  //}
  //}
        $totalHarga += $total;
        $minHarga = 0 - $totalHarga;
        $totalDijamin += $dijamin;
  //$grandTotalHarga = $totalHarga;
      }
//-- RUMUS PEMBULATAN dan Penambahan Uang Muka
      require_once('pembayaran_total_harga.php');

//tampilan atas yang merah

//Konfigurasi Service Charge
//ditutup dulu
      $sql = "select sum(fol_hrs_bayar) as total from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$_GET['pembayaran_id']);
      $dataFolio = $dtaccess->Fetch($sql);
if ($_POST["reg_tipe_rawat"] == 'I') //Jika Rawat Inap
{
  $serviceCharge = (int)((10 / 100) * $totalBiayaNonFarmasi);
}

$grandTotalHarga = $totalHarga - $uangmuka["total"] + $serviceCharge - $_POST["deposit_nominal"];
// $pembulatan_awal = (substr(ceil($grandTotalHargax), -2) != '00') ? round($grandTotalHargax,-2)+100 : round($grandTotalHargax,-2);
// $satuan = substr(ceil($grandTotalHargax), -2);
// $awal = round($grandTotalHargax, -2);
// if ($satuan >= 50) {
//   $selisih = 100 - $satuan;
//   $pembulatan_awal = $awal;
// } 
// else {
//   $selisih = 100 - $satuan;
//   $pembulatan_awal = $awal+$satuan;
// }
// $grandTotalHarga = $pembulatan_awal;

// $pembayaran_det_pembulatan = $pembulatan_awal - $grandTotalHargax;



//echo "total ".$totalHarga."-".$inacbg["inacbg_topup"];

if ($uangmuka["total"] > 0) {
  $retur = $uangmuka["total"] - $totalHarga;
  if ($retur < 0) $retur = 0;
}



if ($_POST["btnOk"])  //Jika klik tombol ganti data diatas
{
  $sql = "update  klinik.klinik_folio set fol_keterangan = " . QuoteValue(DPE_CHAR, $_POST["fol_keterangan"]) . " 
  where id_pembayaran = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]) . " and id_dep=" . QuoteValue(DPE_CHAR, $depId);
  $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);

  $kembali = "kasir_pemeriksaan_proses.php?id_dokter=" . $_POST["id_dokter"] . "&reg_jenis_pasien=" . $_POST["reg_jenis_pasien"] . "&id_poli=" . $_POST["id_poli"] . "&id_reg=" . $_POST["id_reg"] . "&pembayaran_id=" . $_POST["pembayaran_id"];


  header("location:" . $kembali);
  exit();
}
// untuk menghitung awal dari waaktu tunggu 
if (!$_POST["btnBayar"]) {
  // require_once "proses_waktu_tunggu.php";
}
// Jika Klik tombol Bayar //
if ($_POST["btnBayar"]) {
  // require_once "proses_waktu_tunggu.php";
  $totalTagihan = $_POST["txtTotalBiayaService"];
  $bayar = StripCurrency($_POST["txtdibayar1"]) + StripCurrency($_POST["txtdibayar2"]) + StripCurrency($_POST["txtdibayar3"]) + StripCurrency($_POST["txtDiskon"]) + StripCurrency($_POST["deposit_nominal_awal"]);
  $diskonNominal = $_POST["dis"];
  
  $kurangBayar = $_POST["total_harga"] + StripCurrency($_POST['deposit_nominal_awal']) - $bayar;
  

  // ---  AMBIL DATA AWAL YANG DIBUTUHKAN UNTUK SIMPAN PEMBAYARAN
  $sql = "select * from klinik.klinik_pembayaran where 
  id_reg =" . QuoteValue(DPE_CHAR, $_POST["id_reg"]);
  $dataReg = $dtaccess->Fetch($sql);

  //--- AKHIR AMBIL DATA AWAL 

  //JIKA NAMA PENJAMIN BEDA MAKA SET FOLIO SEMUA DENGAN NAMA PENJAMIN
  if ($_POST["fol_keterangan"]) {
    $sql = "update klinik.klinik_folio set fol_keterangan = " . QuoteValue(DPE_CHAR, $_POST["fol_keterangan"]) . " 
    where id_pembayaran = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]);
    $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
  }

  //update status pasien
  if($_POST['reg_tipe_rawat']=='G'){

    $sql = "update klinik.klinik_registrasi set 
    reg_status ='G3'
    where reg_id = " . QuoteValue(DPE_CHAR, $_POST["reg_id"]);
    $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
  }
  elseif($_POST['reg_tipe_rawat']=='J'){

    $sql = "update klinik.klinik_registrasi set 
    reg_status ='E3'
    where reg_id = " . QuoteValue(DPE_CHAR, $_POST["reg_id"]);
    $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
  }



//tutup update status pasien

  //----     UPDATE STATUS REGISTRASI
  /* //SEMENTARA DITUTUP DULU NGGA USAH DIUPDATE STATUS REGISTRASINYA
    $sql = "select reg_id from klinik.klinik_registrasi where reg_utama = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
    $rs = $dtaccess->Execute($sql);
    $allReg = $dtaccess->FetchAll($rs);

    for($i=0,$n=count($allReg);$i<$n;$i++)
    {
      $sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status='E1',
              reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$allReg[$i]["reg_id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
    }
     
    //Update yg reg_utama
    $sql = "update klinik.klinik_registrasi set reg_waktu_pulang = CURRENT_TIME, reg_msk_apotik = 'y', reg_bayar = 'n', reg_status_kondisi='U', reg_status='E0',
            reg_tanggal_pulang=reg_tanggal where reg_id = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);  
            $dtaccess->Execute($sql); */
  //--- AKHIR UPDATE STATUS REGISTRASI



  //Update klinik pembayaran kassa
            require_once('update_klinik_pembayaran_kassa.php');

  //-- INSERT PEMBAYARAN DET
            require_once('insert_pembayaran_det_kassa.php');
  //-- AKHIR INSERT PEMBAYARAN DET



  //-- AWAL PROSES UANG MUKA
  /* Pembayaran Uang Muka sementara ditutup
    require_once('pembayaran_uang_muka.php');
    */
  //-- AWAL PROSES DEPOSIT   
    require_once('pembayaran_deposit.php');
  //-- AKHIR PROSES UANG MUKA         

  //---------   UPDATE KLINIK FOLIO UNTUK FOL DIBAYAR           
    $sql  = " update  klinik.klinik_folio set fol_dibayar = fol_nominal ";
  $sql .= " , id_pembayaran_det = " . QuoteValue(DPE_CHAR, $pembDetUtama); //$pembDetId itu ID PEMBAYARAN DETAIL iinsertnya di insert_pembayaran_det_kassa.php
  $sql .= " , fol_dibayar_when = CURRENT_TIMESTAMP where id_pembayaran = " . QuoteValue(DPE_CHAR, $_POST["pembayaran_id"]) . " and fol_lunas='n'";
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);

  // --- AKHIR UPDATE KLINIK FOLIO fol_dibayar=fol_nominal 

  //Jika ada penjualan maka dibikin sudah lunas
  $sql = "update apotik.apotik_penjualan set 
  penjualan_terbayar ='y'
  where id_fol in (select fol_id from klinik.klinik_folio where id_pembayaran_det = " . QuoteValue(DPE_CHAR, $pembDetId) . ")";
  //  echo $sql; die();
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);

  //AWAL POSTING ke GL   
  // Sementara ditutup untuk Posting GL nya
  require_once('posting_gl.php');

  // AKHIR POSTING GL

  //PERINTAH CETAK KWITANSI  


  $cetak = "y";

  //  header("location:kasir_pemeriksaan_view.php");
} // AKHIR PROSES PEMBAYARAN





//DATA YANG DIGUNAKAN UNTUK VIEW DAN COMBO

// buat ambil jenis bayar --
$sql = "select * from global.global_jenis_bayar where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and jbayar_lowest<>'n' and jbayar_id = '01' and jbayar_tipe != 'O' order by jbayar_id asc";
$dataJenisBayar = $dtaccess->FetchAll($sql);

$sql = "select * from global.global_jenis_bayar where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and jbayar_status='y' and jbayar_tipe != 'O' order by jbayar_id asc";
$dataJenisBayar2 = $dtaccess->FetchAll($sql);

$sql = "select * from global.global_jenis_bayar where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and jbayar_status='y' and jbayar_tipe != 'O' and jbayar_id <> '01' order by jbayar_id asc";
$dataJenisBayar3 = $dtaccess->FetchAll($sql);

$sql = "select * from global.global_jenis_bayar where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and jbayar_status='y' and jbayar_tipe != 'O' and jbayar_id <> '01' and jbayar_lowest = 'n' order by jbayar_id asc";
$dataJenisBayar4 = $dtaccess->FetchAll($sql);


$sql = "select * from global.global_auth_poli where id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by poli_nama asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataPoli = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_tipe_biaya order by tipe_biaya_id asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataTipeLayanan = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_jenis_pasien order by jenis_nama asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataJenis = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataKota = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_perusahaan order by perusahaan_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataPerusahaan = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_jkn order by jkn_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$dataJkn = $dtaccess->FetchAll($rs);

$sql = "select * from global.global_detail_paket";
$rs = $dtaccess->Execute($sql);
$dataPaket = $dtaccess->Fetch($rs);


// data Order Poli
$sql = "select reg_id,poli_nama,c.usr_name,d.usr_name as dokter_sender,reg_who_update
from klinik.klinik_registrasi a
left join global.global_auth_poli b on a.id_poli = b.poli_id
left join global.global_auth_user c on a.id_dokter = c.usr_id
left join global.global_auth_user d on a.reg_dokter_sender = d.usr_id
where a.id_dep =" . QuoteValue(DPE_CHAR, $depId) . " and a.id_pembayaran =" . QuoteValue(DPE_CHAR, $_GET["pembayaran_id"]);
$sql .= " order by reg_tanggal, reg_waktu asc";
$dataorderPoli = $dtaccess->FetchAll($sql);


$tableHeader = "&nbsp;Proses Pembayaran Pasien";

?>

<script type="text/javascript">
  var grandTotal = '<?php echo $grandTotalHarga; ?>';

  function GantiDiskon(diskon) {
    var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g, "");
    var bayar1 = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
    var bayar2 = document.getElementById('txtdibayar2').value.toString().replace(/\,/g, "");
    var bayar3 = document.getElementById('txtdibayar3').value.toString().replace(/\,/g, "");
    var tagihan = document.getElementById('total_harga').value.toString().replace(/\,/g, "");
    var deposit = document.getElementById('deposit_nominal').value.toString().replace(/\,/g, "");
    //var totalnya = document.getElementById('txtDibayar0').value.toString().replace(/\,/g,"");
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g, "");
    var pembulatan = document.getElementById('pembulatan').value.toString().replace(/\,/g, "");
    //var service = document.getElementById('txtServiceCash').value.toString().replace(/\,/g,"");
    var kembalian;
    var tunai = $('#id_jbayar1').val();

    tagihan_int = tagihan * 1;
    if (tunai != '01') //Jika Bukan TUNAI
    {
      if (bayar1 > tagihan_int) {
        bayar1 = 0;
      }
    }
    if (bayar2 > tagihan_int - bayar1) {
      bayar2 = 0;
    }
    if (bayar3 > tagihan_int - bayar1 - bayar2) {
      bayar3 = 0;
    }
    //console.log(bayar2);
    dibayar_int = bayaren * 1; //total tagihan
    bayaren1 = bayar1 * 1;
    bayaren2 = bayar2 * 1;
    bayaren3 = bayar3 * 1;
    diskon_int = diskon * 1;
    deposit_int = deposit * 1;
    totaldibayar = bayaren1 + bayaren2 + bayaren3;
    bulat = pembulatan * 1;
    //service_int = service*1;
    kembalian = (tagihan_int - totaldibayar) - diskon_int;
    document.getElementById('txtIsi').innerHTML = formatCurrency(kembalian);
    document.getElementById('txtDibayar0').value = formatCurrency(totaldibayar);


  }

  function GantiPengurangan(totalbayar) {
    var bayaren = document.getElementById('txtTotalDibayar').value.toString().replace(/\,/g, "");
    var bayar1 = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
    var bayar2 = document.getElementById('txtdibayar2').value.toString().replace(/\,/g, "");
    var bayar3 = document.getElementById('txtdibayar3').value.toString().replace(/\,/g, "");

    var tagihan = document.getElementById('total_harga').value.toString().replace(/\,/g, "");
    var deposit = document.getElementById('deposit_nominal').value.toString().replace(/\,/g, "");
    //var totalnya = document.getElementById('txtDibayar0').value.toString().replace(/\,/g,"");
    var diskon = document.getElementById('txtDiskon').value.toString().replace(/\,/g, "");
    var service = document.getElementById('txtServiceCash').value.toString().replace(/\,/g, "");
    var pembulatan = document.getElementById('pembulatan').value.toString().replace(/\,/g, "");
    var kembalian;
    var tunai = $('#id_jbayar1').val();

    tagihan_int = tagihan * 1;
    if (tunai != '01') //Jika Bukan TUNAI
    {
      if (bayar1 > tagihan_int) {
        bayar1 = 0;
      }
    }
    if (bayar2 > tagihan_int - bayar1) {
      bayar2 = 0;
    }
    if (bayar3 > tagihan_int - bayar1 - bayar2) {
      bayar3 = 0;
    }
    dibayar_int = bayaren * 1; //total tagihan
    bayaren1 = bayar1 * 1;
    bayaren2 = bayar2 * 1;
    bayaren3 = bayar3 * 1;
    diskon_int = diskon * 1;
    deposit_int = deposit * 1;
    bulat = pembulatan * 1;

    totaldibayar = bayaren1 + bayaren2 + bayaren3;
    //service_int = service*1;
    kembalian = (tagihan_int - totaldibayar) - diskon_int;
    document.getElementById('txtIsi').innerHTML = formatCurrency(kembalian);
    document.getElementById('txtDibayar0').value = formatCurrency(totaldibayar);

  }



  var _wnd_new;

  function BukaWindow(url, judul) {
    if (!_wnd_new) {
      _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
    } else {
      if (_wnd_new.closed) {
        _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=700,height=800,left=150,top=20');
      } else {
        _wnd_new.focus();
      }
    }
    return false;
  }
  //     $next = "kasir_pemeriksaan_dot_cetak.php?dep_bayar_reg=".$_POST["dep_bayar_reg"]."&id_reg=".$_POST["id_reg"]."&ket=".$_POST["fol_keterangan"]."&dis=".$_POST["txtDiskon"]."&disper=".$_POST["txtDiskonPersen"]."&pembul=".$_POST["pembulatan"]."&total=".$_POST["total"];
</script>

<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php"); ?>
<!-- <body  onLoad="GantiPembulatan('<?php echo $_POST["txtBiayaPembulatan"]; ?>','<?php echo $grandTotalHarga; ?>')"; >-->

  <body class="nav-md" onload="GantiDiskon()">
    <div class="container body">
      <div class="main_container">

        <?php require_once($LAY . "sidebar.php"); ?>

        <!-- top navigation -->
        <?php require_once($LAY . "topnav.php"); ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Pembayaran Pasien</h3>
              </div>
            </div>

            <div class="clearfix"></div>
            <div class="row">
              <!-- ==== BARIS ===== -->
              <!-- ==== kolom kiri ===== -->
              <!-- ==== mulai form ===== -->
              <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
                <div class="col-md-6 col-sm-6 col-xs-12">

                  <!-- ==== panel putih ===== -->
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Data Pasien</h2>
                      <span class="pull-right"></span>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">No. RM
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_kode"]; ?>">
                        </div>
                      </div>
                      <input type="hidden" name="reg_id" class="form-control" value="<?php echo $_GET["id_reg"]; ?>">
                      <input type="hidden" name="tipe_rawat" class="form-control" value="<?php echo $_GET["tipe_rawat"]; ?>">

                      <?php if ($dataPasien["id_cust_usr"] == '100' || $dataPasien["id_cust_usr"] == '500') { ?>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap</label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input readonly type="text" class="form-control" value="<?php echo $dataPasien["fol_keterangan"]; ?>">
                          </div>
                        </div>
                      <?php } else { ?>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nama Lengkap
                          </label>
                          <div class="col-md-8 col-sm-8 col-xs-12">
                            <input readonly type="text" class="form-control" value="<?php echo $dataPasien["cust_usr_nama"]; ?>">
                          </div>
                        </div>
                      <?php } ?>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Alamat
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input readonly type="text" class="form-control" value="<?php echo nl2br($dataPasien["cust_usr_alamat"]); ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Sudah Terima Dari
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" class="form-control" name="fol_keterangan" id="fol_keterangan" size="45" maxlength="45" value="<?php echo $_POST["fol_keterangan"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" />
                          <input type="submit" name="btnOk" value="Ganti Data" class="submit" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Cara Bayar
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select readonly name="reg_jenis_pasien" class="select2_single form-control" disabled id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);">
                            <option value="--">[ Pilih Cara Bayar ]</option>
                            <?php for ($i = 0, $n = count($dataJenis); $i < $n; $i++) { ?>
                              <option value="<?php echo $dataJenis[$i]["jenis_id"]; ?>" <?php if ($_POST["reg_jenis_pasien"] == $dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"]; ?></option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Klinik
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select readonly class="select2_single form-control" name="id_poli" disabled id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                            <option value="--">[ Pilih Klinik ]</option>
                            <?php for ($i = 0, $n = count($dataPoli); $i < $n; $i++) { ?>
                              <option value="<?php echo $dataPoli[$i]["poli_id"]; ?>" <?php if ($_POST["id_poli"] == $dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"]; ?></option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Untuk Pembayaran
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="text" name="pembayaran_det_ket" id="pembayaran_det_ket" class="form-control" value="<?php echo $_POST["pembayaran_det_ket"]; ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Tanggal Posting</label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <div class='input-group date' id='datepicker'>
                            <input id="tanggal_posting" name="tanggal_posting" type='text' class="form-control" value="<?php echo date('dd-mm-YY') ?>" />
                            <span class="input-group-addon">
                              <span class="fa fa-calendar">
                              </span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- ==== //panel putih ===== -->
                  <!-- ==== panel putih ===== -->
                <!-- gk butuh foto pasien 
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Foto Pasien</h2>
                    <span class="pull-right"></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                      <div class="form-group">
                        <td width= "5%" align="center" class="tablecontent" rowspan="10"><img src="<?php if ($_POST["cust_usr_foto"]) echo $lokasi . "/" . $_POST["cust_usr_foto"];
                                                                                                    else echo $lokasi . "/default.jpg"; ?>" height="100px" width="100px" align="center"/></td>
                  </div>            
                  </div>
                </div>  -->

              </div>
              <!-- ==== // kolom kiri ===== -->


              <?
              $ttotal = $grandTotalHarga; //Ini Default Pengisian dari Text Box total pembayaran
              $totalPembayaran = $grandTotalHarga;     //Ini Tulisan Merah Total
              ?>

              <!-- ==== kolom kanan ===== -->
              <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Total Tagihan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <table border="0">
                          <tr>
                            <td width="40%" align="center" class="tablecontent" rowspan="5">
                              <font color='red' size='10'><span id=txtIsi><?php echo number_format(ceil($grandTotalHarga)); ?></span></font>
                            </td>
                          </tr>
                        </table>
                      </div>
                    </div>
                    <?
                    // Jika Total Biaya + Jasa Layanan RS Kurang dari Deposit Nominal maka Pembayaran di non aktifkan
                    if (($totalBiaya + $serviceCharge) < $_POST["deposit_nominal"]) {
                      $pembayaranDisabled = TRUE;
                      $tipeText = "readonly";
                    } else //jika sebaliknya
                    {
                      $pembayaranDisabled = FALSE;
                      $tipeText = "";
                    }

                    ?>
                    
                    <?php if ($dataTable) : ?>
                      <?php if (count($dataDiskon) > 0) : ?>
                        <?php $totalDiskon = 0;
                        foreach ($dataDiskon as $dd) :  $totalDiskon = $totalDiskon + $dd['diskon_nominal']; ?>
                          <div class="form-group">
                            <div class="col-md-6 col-sm-8 col-xs-12">
                              <label for="">Diskon</label>
                              <input type="text" readonly value="<?= $dd['diskon_nama'] ?>" class="form-control">
                            </div>

                            <div class="col-md-5 col-sm-6 col-xs-12">
                              <label for=""> Nominal</label>
                              <input type="text" readonly value="<?= number_format($dd['diskon_nominal']) ?>" class="form-control">
                            </div>
                            <div class="col-md-1">
                              <label for="">Hapus</label>
                              <a href="<?= $thisPage ?>&id_diskon=<?= $dd['id_diskon'] ?>&del=1" class="btn btn-danger"> X </a>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    <?php endif; ?>

                    <div class="form-group">
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        Diskon
                        <select name="diskon_nama" class="select2_single form-control" <? if ($pembayaranDisabled) echo "disabled" ?> onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">- Pilih Discount -</option>
                          <option value="ASKES/RUANGAN">ASKES/RUANGAN</option>
                          <option value="ASKES/PMDR">ASKES/PMDR</option>
                          <option value="ASKES/OBAT-ALKES">ASKES/OBAT-ALKES</option>
                          <option value="ASKES/POLI UMUM / IGD">ASKES/POLI UMUM / IGD</option>
                          <option value="ASKES/SEWA ALAT DI RUANGAN">ASKES/SEWA ALAT DI RUANGAN</option>
                          <option value="ASKES/GAS MEDIS">ASKES/GAS MEDIS</option>
                          <option value="ASKES/BIAYA OPERASI">ASKES/BIAYA OPERASI</option>
                          <option value="ASKES/TINDAKAN PERAWATAN">ASKES/TINDAKAN PERAWATAN</option>
                          <option value="ASKES/LABORATORIUM">ASKES/LABORATORIUM</option>
                          <option value="ASKES/RADIOLOGI">ASKES/RADIOLOGI</option>
                          <option value="ASKES/CTSCAN">ASKES/CTSCAN</option>
                          <option value="ASKES/EKG">ASKES/EKG</option>
                          <option value="ASKES/USG">ASKES/USG</option>
                          <option value="ASKES/FISIOTERAPI">ASKES/FISIOTERAPI</option>
                          <option value="ASKES/PMI">ASKES/PMI</option>
                          <option value="ASKES/ADMINISTRASI">ASKES/ADMINISTRASI</option>
                          <option value="DISCOUNT/RUANGAN">DISCOUNT/RUANGAN</option>
                          <option value="DISCOUNT/POLI UMUM / IGD">DISCOUNT/POLI UMUM / IGD</option>
                          <option value="DISCOUNT/SEWA ALAT DI RUANGAN">DISCOUNT/SEWA ALAT DI RUANGAN</option>
                          <option value="DISCOUNT/TINDAKAN PERAWATAN">DISCOUNT/TINDAKAN PERAWATAN</option>
                          <option value="DISCOUNT/OBAT-ALKES">DISCOUNT/OBAT-ALKES</option>
                          <option value="DISCOUNT/GAS MEDIS">DISCOUNT/GAS MEDIS</option>
                          <option value="DISCOUNT/BIAYA OPERASI">DISCOUNT/BIAYA OPERASI</option>
                          <option value="DISCOUNT/BIAYA PERSALINAN">DISCOUNT/BIAYA PERSALINAN</option>
                          <option value="DISCOUNT/LABORATORIUM">DISCOUNT/LABORATORIUM</option>
                          <option value="DISCOUNT/RADIOLOGI">DISCOUNT/RADIOLOGI</option>
                          <option value="DISCOUNT/CTSCAN">DISCOUNT/CTSCAN</option>
                          <option value="DISCOUNT/EKG">DISCOUNT/EKG</option>
                          <option value="DISCOUNT/USG">DISCOUNT/USG</option>
                          <option value="DISCOUNT/FISIOTERAPI">DISCOUNT/FISIOTERAPI</option>
                          <option value="DISCOUNT/AMBULAN">DISCOUNT/AMBULAN</option>
                          <option value="DISCOUNT/ADMINISTRASI">DISCOUNT/ADMINISTRASI</option>
                          <option value="DISCOUNT/VISITE">DISCOUNT/VISITE</option>
                          <option value="DISCOUNT/PAKET AKOMODASI">DISCOUNT/PAKET AKOMODASI</option>
                          <option value="DISCOUNT DOKTER IMUNISASI">DISCOUNT DOKTER IMUNISASI</option>
                        </select>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        Nominal
                        <?php echo $view->RenderTextBox("txtdiskon1", "txtdiskon1", "30", "30", $_POST["txtdiskon1"], "curedit", "", true, 'onChange=GantiDiskon(this);'); ?>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        <?php if ($dataTable) { ?>
                          <input type="submit" name="btnDiskon" id="btnDiskon" value="Tambah Diskon" class="submit">
                        <?php } ?>
                      </div>
                    </div>

                    <br>
                    <br>
                    <br>

                    <div class="form-group">
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        Jenis Bayar 1
                        <select name="id_jbayar1" class="select2_single form-control" <? if ($pembayaranDisabled) echo "disabled" ?> id="id_jbayar1" onKeyDown="return tabOnEnter(this, event);">
                          <?php //if($depLowest=='n'){ 
                          ?><option class="inputField" value="--">- Pilih Cara Bayar -</option><?php //} 
                          ?>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($dataJenisBayar2); $i < $n; $i++) {
                            unset($spacer);
                            $length = (strlen($dataJenisBayar2[$i]["jbayar_id"]) / TREE_LENGTH_CHILD) - 1;
                            for ($j = 0; $j < $length; $j++) $spacer .= "..";
                            ?>
                            <option value="<?php echo $dataJenisBayar2[$i]["jbayar_id"]; ?>" <?php if ($_POST["id_jbayar1"] == $dataJenisBayar2[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer . " " . $dataJenisBayar2[$i]["jbayar_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        Pembayaran 1
                        <?php echo $view->RenderTextBox("txtdibayar1", "txtdibayar1", "30", "30", $_POST["txtdibayar1"], "curedit", $tipeText, true, 'onChange=GantiPengurangan();'); ?>
                      </div>
                    </div>

                    <div class="form-group">
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        Jenis Bayar 2
                        <select name="id_jbayar2" class="select2_single form-control" <? if ($pembayaranDisabled) echo "disabled" ?> id="id_jbayar2" onKeyDown="return tabOnEnter(this, event);">
                          <?php //if($depLowest=='n'){ 
                          ?><option class="inputField" value="--">- Pilih Cara Bayar -</option><?php //} 
                          ?>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($dataJenisBayar3); $i < $n; $i++) {
                            unset($spacer);
                            $length = (strlen($dataJenisBayar3[$i]["jbayar_id"]) / TREE_LENGTH_CHILD) - 1;
                            for ($j = 0; $j < $length; $j++) $spacer .= "..";
                            ?>
                            <option value="<?php echo $dataJenisBayar3[$i]["jbayar_id"]; ?>" <?php if ($_POST["id_jbayar2"] == $dataJenisBayar3[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer . " " . $dataJenisBayar3[$i]["jbayar_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        Pembayaran 2
                        <?php echo $view->RenderTextBox("txtdibayar2", "txtdibayar2", "30", "30", $_POST["txtdibayar2"], "curedit", $tipeText, true, 'onChange=GantiPengurangan();'); ?>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-8 col-xs-12">
                        Jenis Bayar 3
                        <select name="id_jbayar3" class="select2_single form-control" <? if ($pembayaranDisabled) echo "disabled" ?> id="id_jbayar3" onKeyDown="return tabOnEnter(this, event);">
                          <?php //if($depLowest=='n'){ 
                          ?><option class="inputField" value="--">- Pilih Cara Bayar -</option><?php //} 
                          ?>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($dataJenisBayar3); $i < $n; $i++) {
                            unset($spacer);
                            $length = (strlen($dataJenisBayar3[$i]["jbayar_id"]) / TREE_LENGTH_CHILD) - 1;
                            for ($j = 0; $j < $length; $j++) $spacer .= "..";
                            ?>
                            <option value="<?php echo $dataJenisBayar3[$i]["jbayar_id"]; ?>" <?php if ($_POST["id_jbayar3"] == $dataJenisBayar3[$i]["jbayar_id"]) echo "selected"; ?>><?php echo $spacer . " " . $dataJenisBayar3[$i]["jbayar_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        Pembayaran 3
                        <?php echo $view->RenderTextBox("txtdibayar3", "txtdibayar3", "30", "30", $_POST["txtdibayar3"], "curedit", $tipeText, true, 'onChange=GantiPengurangan();'); ?>
                      </div>
                    </div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
                      <table border="0">
                        <b>
                          <hr></b>
                          <tr>
                            <td class="tablecontent" align="center">&nbsp;</td>
                            <td width="60%" align="right" class="tablecontent-odd"><b>Deposit</b> </td>
                            <td class="tablecontent" align="center">&nbsp;:&nbsp;</td>
                            <td class="tablecontent" colspan="4">
                              <?
                              if ($_POST["deposit_nominal"] > 0) $isiDeposit = $_POST["deposit_nominal"];
                              else $isiDeposit = 0;

                              ?>
                              <?php echo $view->RenderLabel("lblDeposit", "lblDeposit", $isiDeposit); ?>
                              <?php echo $view->RenderHidden("deposit_nominal", "deposit_nominal", $_POST["deposit_nominal"]); ?>
                              <?php echo $view->RenderHidden("deposit_nominal_awal", "deposit_nominal_awal", $_POST["deposit_nominal"]); ?>
                              <?php echo $view->RenderHidden("cust_usr_id", "cust_usr_id", $_POST["cust_usr_id"]); ?>
                            </td>
                          </tr>
                          <tr>
                            <td class="tablecontent" align="center">&nbsp;</td>
                            <td width="60%" align="right" class="tablecontent-odd"><b>Diskon</b> </td>
                            <td class="tablecontent" align="center">&nbsp;:&nbsp;</td>
                            <td class="tablecontent" colspan="4">
                              <input type="text" class="form-control" value="<?= number_format($totalDiskon) ?>" readonly>
                              <?php echo $view->RenderHidden("txtDiskon", "txtDiskon", $totalDiskon, "", $totalDiskon, "curedit", "", true, 'onChange=GantiDiskon(this);'); ?>
                            <?php //echo $view->RenderHidden("txtDiskon","txtDiskon","30","30",$_POST["txtDiskon"],"curedit", "",true,'onChange=GantiDiskon(this);');
                            ?>
                          </td>
                        </tr>
                        <tr>
                          <td class="tablecontent" align="center">&nbsp;</td>
                          <td width="60%" align="right" class="tablecontent-odd"><b>Total Pembayaran</b> </td>
                          <td class="tablecontent" align="center">&nbsp;:&nbsp;</td>
                          <td class="tablecontent" colspan="4">
                            <?php echo $view->RenderTextBox("txtDibayar[0]", "txtDibayar0", "30", "30", $_POST["txtDibayar0"], "curedit", "readonly", true, 'onChange=GantiPengurangan(this);'); ?></td>
                          </tr>
                        </table>
                        <br><br>
                        <td width="50%" align="center">
                          <?php
                           if ($dataTable) {
                          //  $sql = "select klinik_waktu_tunggu_when_create as prev,klinik_waktu_tunggu_when_update from klinik.klinik_waktu_tunggu where id_waktu_tunggu_status='K0' AND id_reg = " . QuoteValue(DPE_CHAR, $_GET['id_reg']);
                          //   // echo $sql;
                          //  $rs = $dtaccess->Fetch($sql);
                          //  if (is_null($rs["klinik_waktu_tunggu_when_update"])) {

                          //   $sql = "update klinik.klinik_waktu_tunggu set klinik_waktu_tunggu_when_update = " . QuoteValue(DPE_DATE, date("Y-m-d H:i:s")) . " 
                          //   where id_reg = " . QuoteValue(DPE_CHAR, $_GET['id_reg'])." and klinik_waktu_tunggu_status='K0'";
                          //   $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);


                          //   $time=date('Y-m-d H:i:s');

                          // }
                          // else{
                          //   $akhir  = date_create(date('Y-m-d H:i:s'));
                          //     $awal = date_create($rs["klinik_waktu_tunggu_when_update"]); // waktu sekarang
                          //     $diff  = date_diff( $awal, $akhir );

                          //     $time=$rs["klinik_waktu_tunggu_when_update"];
                              

                          //   }

                            ?>
                            <input type="button" name="btnBayar" id="btnBayar" value="Bayar" class="submit">
                            <!-- <input type="submit" name="btnBayar" id="btnBayar" value="Bayar" class="submit" onclick="Kembaliannya()">      -->
                          <?php } ?>
                          <input type="button" name="simpan" id="simpan" value="Kembali" class="submit" onClick="document.location.href='kasir_pemeriksaan_proses.php?kembali=1&id_reg=<?php echo $_GET['id_reg'] ?>'" ; />
                        </td>
                      </div>
                    </div>

                  </div>
                </div>
     

                          <!-- ==== // KHUSUS BUTTON ===== -->
                        </div>

                        <div class="x_content">
                          <div class="form-group">
                            <fieldset>
                              <legend><strong>Data Tagihan Yang Belum Dibayar</strong></legend>
                              <div id="kasir">
                                <table style="width: 100%;" role="grid" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                  <tr class="tablesmallheader">
                                    <td width="3%" align='center'>No</td>
                                    <td width="20%" align='center'>Layanan</td>
                                    <td width="12%" align='center'>Klinik/Penunjang</td>
                                    <td width="17%" align='center'>Nama Dokter</td>
                                    <td width="10%" align='center'>Biaya</td>
                                    <td width="5%" align='center'>Quantity</td>
                                    <td width="10%" align='center'>Tagihan</td>
                                  </tr>

                                  <?php for ($i = 0, $n = count($dataTable); $i < $n; $i++) { ?>

                                    <?php if (
                                      $dataTable[$i]["fol_jenis"] == 'O' || $dataTable[$i]["fol_jenis"] == 'OI'
                                      || $dataTable[$i]["fol_jenis"] == '13' || $dataTable[$i]["fol_jenis"] == 'OG'
                                      || $dataTable[$i]["fol_jenis"] == 'I'
                                    ) {
                                      $sql = "select c.item_nama, a.* ,satuan_nama
                                      from apotik.apotik_penjualan_detail a
                                      left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.id_fol = " . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_id"]);
                                      $rs = $dtaccess->Execute($sql);
                                      $dataFarmasidetail  = $dtaccess->FetchAll($rs);
                                    }

                                    if (
                                      $dataTable[$i]["fol_jenis"] == 'R' || $dataTable[$i]["fol_jenis"] == 'RA'
                                      || $dataTable[$i]["fol_jenis"] == 'RG' || $dataTable[$i]["fol_jenis"] == 'RI'
                                    ) {
                                      $sql = "select c.item_nama, a.* ,satuan_nama
                                      from logistik.logistik_retur_penjualan_detail a
                                      left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                      left join logistik.logistik_item c on a.id_item = c.item_id
                                      left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                      where b.retur_penjualan_nomor = " . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_catatan"]);
                                      $rs = $dtaccess->Execute($sql);
                                      $dataReturdetail  = $dtaccess->FetchAll($rs);
                                    }      ?>

                                    <tr class="tablecontent-odd">
                                      <td width="3%"><?php echo ($i + 1) . "."; ?></td>
                                      <td width="20%">
                                        <?php if (
                                          $dataTable[$i]["fol_jenis"] == "O" || $dataTable[$i]["fol_jenis"] == "OA" || $dataTable[$i]["fol_jenis"] == "OG" ||
                                          $dataTable[$i]["fol_jenis"] == "OI" || $dataTable[$i]["fol_jenis"] == "R" || $dataTable[$i]["fol_jenis"] == "RA" ||
                                          $dataTable[$i]["fol_jenis"] == "RA" || $dataTable[$i]["fol_jenis"] == "RG" || $dataTable[$i]["fol_jenis"] == "RI"
                                        ) {
                                          echo $dataTable[$i]["fol_nama"] . " (" . $dataTable[$i]["fol_catatan"] . ")";
                                        } else echo $dataTable[$i]["fol_nama"]; ?>
                                      </td>
                                      <td width="12%"><?php echo $dataTable[$i]["poli_nama"]; ?></td>
                                      <td width="17%"><?php echo $dataTable[$i]["usr_name"]; ?></td>
                                      <td width="10%" align='right'><?php echo number_format($dataTable[$i]["fol_nominal_satuan"], 2, '.', ','); ?></td>
                                      <td width="5%" align='right'><?php echo round($dataTable[$i]["fol_jumlah"]); ?></td>
                                      <td width="10%" align='right'><?php echo number_format($dataTable[$i]["fol_nominal"], 2, '.', ',') ?></td>
                                    </tr>
                                    <?php if (
                                      $dataTable[$i]["fol_jenis"] == 'O' || $dataTable[$i]["fol_jenis"] == 'OI'
                                      || $dataTable[$i]["fol_jenis"] == '13' || $dataTable[$i]["fol_jenis"] == 'OG'
                                      || $dataTable[$i]["fol_jenis"] == 'I' || $dataTable[$i]["fol_jenis"] == 'R' ||
                                      $dataTable[$i]["fol_jenis"] == 'RI'
                                      || $dataTable[$i]["fol_jenis"] == 'RA' || $dataTable[$i]["fol_jenis"] == 'RG'
                                    ) {  ?>

                                      <tr class="garis_atas garis_bawah">
                                        <?php if (
                                          $dataTable[$i]["fol_jenis"] == 'O' || $dataTable[$i]["fol_jenis"] == 'OI'
                                          || $dataTable[$i]["fol_jenis"] == '13' || $dataTable[$i]["fol_jenis"] == 'OG'
                                          || $dataTable[$i]["fol_jenis"] == 'I'
                                        ) {
                                          $sql = "select count(penjualan_detail_id) as total
                                          from apotik.apotik_penjualan_detail a
                                          left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
                                          left join logistik.logistik_item c on a.id_item = c.item_id
                                          left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                          where b.id_fol = " . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_id"]);
                                          $rs = $dtaccess->Execute($sql);
                                          $totalitem = $dtaccess->Fetch($rs);
                                        }
                                        if (
                                          $dataTable[$i]["fol_jenis"] == 'R' ||
                                          $dataTable[$i]["fol_jenis"] == 'RI'
                                          || $dataTable[$i]["fol_jenis"] == 'RA' || $dataTable[$i]["fol_jenis"] == 'RG'
                                        ) {
                                          $sql = "select count(retur_penjualan_detail_id) as total
                                          from logistik.logistik_retur_penjualan_detail a
                                          left join logistik.logistik_retur_penjualan b on a.id_penjualan_retur = b.retur_penjualan_id
                                          left join logistik.logistik_item c on a.id_item = c.item_id
                                          left join logistik.logistik_item_satuan d on c.id_satuan_jual = d.satuan_id
                                          where b.retur_penjualan_nomor = " . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_catatan"]);
                                          $rs = $dtaccess->Execute($sql);
                                        } ?>
                                        <td align="left" rowspan="<?php echo $totalitem["total"] + 1; ?>"></td>
                                        <td align="left">Nama Item/Obat</td>
                                        <td align="right">Harga Satuan</td>
                                        <td align="right">Quantity</td>
                                        <td align="right">Total</td>
                                        <td align="right"></td>
                                      </tr>

                                    <?php } ?>
                                    <?php if (
                                      $dataTable[$i]["fol_jenis"] == 'O' || $dataTable[$i]["fol_jenis"] == 'OI'
                                      || $dataTable[$i]["fol_jenis"] == '13' || $dataTable[$i]["fol_jenis"] == 'OG'
                                      || $dataTable[$i]["fol_jenis"] == 'I'
                                    ) {  ?>

                                      <?php for ($x = 0, $y = count($dataFarmasidetail); $x < $y; $x++) { ?>
                                        <tr>

                                          <td align="left"> - <?php echo $dataFarmasidetail[$x]["item_nama"]; ?></td>
                                          <td align="right"><?php echo number_format($dataFarmasidetail[$x]["penjualan_detail_harga_jual"], 2, '.', ','); ?></td>
                                          <td align="right"><?php echo number_format($dataFarmasidetail[$x]["penjualan_detail_jumlah"], 2, '.', ','); ?> <?php echo $dataFarmasidetail[$x]["satuan_nama"]; ?></td>
                                          <td align="right"><?php echo number_format($dataFarmasidetail[$x]["penjualan_detail_total"], 2, '.', ','); ?></td>
                                          <td align="right"></td>
                                        </tr>
                                      <?php } ?>

                                    <?php } ?>
                                    <?php if (
                                      $dataTable[$i]["fol_jenis"] == 'R' || $dataTable[$i]["fol_jenis"] == 'RA'
                                      || $dataTable[$i]["fol_jenis"] == 'RI' || $dataTable[$i]["fol_jenis"] == 'RG'
                                    ) { ?>
                                      <?php for ($x = 0, $y = count($dataReturdetail); $x < $y; $x++) { ?>
                                        <tr class="garis_atas garis_bawah">
                                          <td align="left"> - <?php echo $dataReturdetail[$x]["item_nama"]; ?></td>
                                          <td align="right"><?php echo number_format($dataReturdetail[$x]["retur_penjualan_detail_total"], 2, '.', ','); ?></td>
                                          <td align="right"><?php echo number_format($dataReturdetail[$x]["retur_penjualan_detail_jumlah"], 2, '.', ','); ?> <?php echo $dataReturdetail[$x]["satuan_nama"]; ?></td>
                                          <td align="right"><?php echo number_format($dataReturdetail[$x]["retur_penjualan_detail_grandtotal"], 2, '.', ','); ?></td>

                                        </tr>
                                      <?php } ?>

                                    <? } ?>
                                  </tr>
                                  <?php

                                  $totalPembayaran += $dataTable[$i]["fol_nominal"];
                                  $totalHarga = $totalBiaya - $dijaminHarga;
                                  if ($totalHarga < 0) $totalHarga = 0;

                                  ?>
                                <?php } ?>

                    <!-- <?php
                    $total_awal = $totalBiaya + $serviceCharge;
                    $satuan = substr(ceil($total_awal), -2);
                    $awal = round($total_awal, -2);
                    if ($satuan >= 50) {
                      $selisih = 100 - $satuan;
                      $pembulatan_awal = $awal;
                    } else {
                      $selisih = 100 - $satuan;
                      $pembulatan_awal = $awal+$satuan;
                    }
                    //? round($total_awal,-2)+100 : round($total_awal,-2);
                    $pembulatan = $pembulatan_awal - $total_awal;
                    $total_akhir = ceil($pembulatan_awal);
                  ?> -->

                  <tr>
                    <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Sub Total Tagihan</b></td>
                    <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. " . number_format($total_akhir, 2, '.', ',') . "</b>"; ?></td>
                    <input type="hidden" name="txtTotalBiaya" id="txtTotalBiaya" value="<?php echo $totalBiaya; ?>" />
                  </tr>
                  <? if ($serviceCharge > 0) { ?>
                    <tr>
                      <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Jasa RS</b></td>
                      <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. " . number_format($serviceCharge, 2, '.', ',') . "</b>"; ?></td>
                    </tr>
                  <? } ?>
                    <!-- <tr>
                      <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Pembulatan</b></td>
                      <input type="hidden" name="pembayaran_det_pembulatan" id="pembayaran_det_pembulatan" value="<?php echo $pembulatan; ?>">
                      <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. " . number_format($pembulatan, 2, '.', ',') . "</b>"; ?></td>
                    </tr> -->
                    <tr>
                      <td class="tablesmallheader" width="45%" align="right" colspan="5"><b>Total Tagihan</b></td>
                      <td class="tablesmallheader" width="15%" colspan='2' align='right'><?php echo "<b>Rp. " . number_format($total_akhir - $isiDeposit, 2, '.', ',') . "</b>"; ?></td>
                      <input type="hidden" name="txtServiceCash" id="txtServiceCash" value="<?php echo $serviceCharge; ?>" />
                      <input type="hidden" name="txtTotalBiayaService" id="txtTotalBiayaService" value="<?php echo ($totalBiaya + $serviceCharge); ?>" />
                    </tr>
                  </table>
                </div>
              </fieldset>
            </div>
          </div>
        </div>

        <?php echo $view->RenderHidden("konf_reg_id", "konf_reg_id", $_POST["konf_reg_id"]); ?>
        <input type="hidden" name="total_harga" id="total_harga" value="<?php echo round($grandTotalHarga); ?>" />
        <input type="hidden" name="total_dijamin" id="total_dijamin" value="<?php echo $totalDijamin; ?>" />
        <input type="hidden" name="total_biaya" id="total_biaya" value="<?php echo $totalBiaya; ?>" />
        <input type="hidden" name="tagihan" id="tagihan" value="<?php echo $totalBiaya; ?>" />
        <input type="hidden" name="txtBack" id="txtBack" value="<?php echo $_POST["txtBack"]; ?>" />
        <input type="hidden" name="txtcek" id="txtcek" value="<?php echo $_POST["txtcek"]; ?>">
        <input type="hidden" name="txtTotalDibayar" id="txtTotalDibayar" value="<?php echo $totalHarga ?>">
        <input type="hidden" name="txtKembalian" id="txtKembalian" value="<?php echo $_POST["txtHargaTotal"]; ?>">
        <input type="hidden" name="pembayaran_id" id="pembayaran_id" value="<?php echo $_POST["pembayaran_id"]; ?>">
        <input type="hidden" name="bayar" id="bayar" value="<?php echo $grandTotalHarga; ?>" />
        <script>
          document.frmEdit.txtDibayar0.focus();
        </script>
        <input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
        <input type="hidden" name="id_cust_usr" value="<?php echo $_POST["id_cust_usr"]; ?>" />
        <input type="hidden" name="id_reg" value="<?php echo $_GET["id_reg"]; ?>" />
        <input type="hidden" name="fol_jenis" value="<?php echo $_POST["fol_jenis"]; ?>" />
        <input type="hidden" name="fol_id" value="<?php echo $_GET["fol_id"]; ?>" />
        <input type="hidden" name="biaya_id" value="<?php echo $_GET["jenis"]; ?>" />
        <input type="hidden" name="waktu" value="<?php echo $_GET["waktu"]; ?>" />
        <input type="hidden" name="dep_bayar_reg" value="<?php echo $_POST["dep_bayar_reg"]; ?>" />
        <input type="hidden" name="reg_jenis_pasien" value="<?php echo $_POST["reg_jenis_pasien"]; ?>" />
        <input type="hidden" name="reg_tipe_layanan" value="<?php echo $_POST["reg_tipe_layanan"]; ?>" />
        <input type="hidden" name="reg_tipe_rawat" value="<?php echo $_POST["reg_tipe_rawat"]; ?>" />
        <input type="hidden" name="id_poli" value="<?php echo $_POST["id_poli"]; ?>" />
        <input type="hidden" name="id_dokter" value="<?php echo $_POST["id_dokter"]; ?>" />
        <input type="hidden" name="retur" value="<?php echo $retur; ?>" />
        <input type="hidden" name="op" value="<?php echo $op["poli_id"]; ?>" />
        <input type="hidden" name="dep_posting_poli" value="<?php echo $_POST["dep_posting_poli"]; ?>" />
        <input type="hidden" name="reg_tipe_paket" value="<?php echo $_POST["reg_tipe_paket"]; ?>" />
        <input type="hidden" name="dep_posting_beban" value="<?php echo $_POST["dep_posting_beban"]; ?>" />
        <input type="hidden" name="operasi" value="<?php echo $operasi["preop_id"]; ?>" />
        <input type="hidden" name="dep_cetak_rincian" value="<?php echo $_POST["dep_cetak_rincians"]; ?>" />
        <input type="hidden" name="pembulatan" id="pembulatan" value="<?php echo $pembulatan; ?>" />


      </form> <!-- ==== Akhir form ===== -->
      <!-- ==== // kolom kanan ===== -->
    </div> <!-- ==== // BARIS ===== -->
  </div>
</div>
<!-- /page content -->

<!-- footer content -->
<?php require_once($LAY . "footer.php") ?>
<!-- /footer content -->
</div>
</div>
<script>

  var countDownDate = <?php echo strtotime($time) ?> * 1000;
  var now = <?php echo time() ?> * 1000;

    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get todays date and time
        // 1. JavaScript
        // var now = new Date().getTime();
        // 2. PHP
        now = now + 1000;

        // Find the distance between now an the count down date
        var distance =   now-countDownDate;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id="demo"
        document.getElementById("sisa_waktu").innerHTML ="Lama Waktu "+ 
        minutes + " menit ";
        document.getElementById("et_alasan").disabled = true;


        // If the count down is over, write some text 
        if (minutes > 10) {
          clearInterval(x);
          document.getElementById("sisa_waktu").innerHTML ="Anda melewati batas waktu lebih dari 10 menit silahkan mengisi Alasan Keterlambatan Pembayaran"
          document.getElementById("et_alasan").disabled = false;
          // document.getElementById("et_alasan").required;
        }
      }, 1000);


    $(function(){

      // Find Elements
      var id_jbayar1  = $('#id_jbayar1');
      var txtdibayar1 = $('#txtdibayar1');

      var id_jbayar2  = $('#id_jbayar2');
      var txtdibayar2 = $('#txtdibayar2');

      var id_jbayar3  = $('#id_jbayar3');
      var txtdibayar3 = $('#txtdibayar3');

      // Kondisikan element id : txtdibayar1 disabled
      txtdibayar1.attr('disabled','disabled');
      txtdibayar2.attr('disabled','disabled');
      txtdibayar3.attr('disabled','disabled');

      // Saat elemen id : id_jbayar1 diganti maka
      id_jbayar1.on('change', function(){

        if( id_jbayar1.val() == '--' ){
          txtdibayar1.prop('disabled', true);
        }else{
          txtdibayar1.prop('disabled', false);
        }

        
      })

      id_jbayar2.on('change', function(){

       if( id_jbayar2.val() == '--' ){
        txtdibayar2.prop('disabled', true);
      }else{
        txtdibayar2.prop('disabled', false);
      }


    })

      id_jbayar3.on('change', function(){

       if( id_jbayar3.val() == '--' ){
        txtdibayar3.prop('disabled', true);
      }else{
        txtdibayar3.prop('disabled', false);
      }


    })
      
    })
  </script>

  <?php require_once($LAY . "js.php") ?>
  <script type="text/javascript">
    /*
         function inputKwitansi($bayardet){
              //var id_det = $bayardet;
                    $.messager.prompt('Silahkan input', 'Nomor Kwitansi:', function(r){
                         if (r){
                              //alert(r);                             
                              $.post('update_slip.php',{id_det:$bayardet,pembayaran_det_slip:r},function(result){
                              if (result.success){
                                window.location.href='kasir_pemeriksaan_view.php';
                              } else {
                                $.messager.show({ // show error message
                                  title: 'Error',
                                  msg: result.errorMsg
                                });
                              }
                            },'json');
                         }
                    });
               
                  }    */

                  <?php if ($cetak == "y") { ?>
      //    if(confirm('Cetak Invoice?'))


      <?php if ($_POST["reg_tipe_rawat"] == 'J' || $_POST["reg_tipe_rawat"] == 'R') { ?> //JIKA RAWAT JALAN
        <?php if ($_POST["dep_cetak_rincian"] == 'y') { ?>
          BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtdibayar1"]); ?>&pembayaran_det_id=<?php echo $pembDetId; ?>&uangmuka_id=<?php echo $uangmukaId; ?>', 'Kwitansi');
        <?php } else if ($_POST["dep_cetak_rincian"] == 'a') { ?>
          BukaWindow('cetak_kwitansi_rincian_a5.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]); ?>&pembayaran_det_id=<?php echo $pembDetId; ?>&uangmuka_id=<?php echo $uangmukaId; ?>', 'Kwitansi');
        <?php } else { ?>
          BukaWindow('cetak_kwitansi_label.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&totalbiayaservice=<?php echo $_POST["txtTotalBiayaService"]; ?>&deposit_awal=<?php echo $_POST["deposit_nominal_awal"]; ?>&deposit=<?php echo $_POST["deposit_nominal"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtdibayar1"]); ?>&dibayar2=<?php echo StripCurrency($_POST["txtdibayar2"]); ?>&dibayar3=<?php echo StripCurrency($_POST["txtdibayar3"]); ?>&pembayaran_det_id=<?php echo $pembDetUtama; ?>&uangmuka_id=<?php echo $uangmukaId; ?>&catatan=<?php echo $_POST["catatan"]; ?>&pembayaran_id=<?php echo $_POST["pembayaran_id"]; ?>', 'Kwitansi');
        <?php } ?>
        //inputKwitansi('<?php //echo $pembDetId;
                          ?>'); 
                          document.location.href = '<?php echo $backPage; ?>';
                        <?php } else ?>
      <?php if ($_POST["reg_tipe_rawat"] == 'G') { ?> //JIKA RAWAT DARURAT
        <?php if ($_POST["dep_cetak_rincian"] == 'y') { ?>
          BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]); ?>&pembayaran_det_id=<?php echo $pembDetId; ?>&uangmuka_id=<?php echo $uangmukaId; ?>', 'Kwitansi');
        <?php } else { ?>
          BukaWindow('cetak_kwitansi_igd.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&totalbiayaservice=<?php echo $_POST["txtTotalBiayaService"]; ?>&deposit_awal=<?php echo $_POST["deposit_nominal_awal"]; ?>&deposit=<?php echo $_POST["deposit_nominal"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtdibayar1"]); ?>&dibayar2=<?php echo StripCurrency($_POST["txtdibayar2"]); ?>&dibayar3=<?php echo StripCurrency($_POST["txtdibayar3"]); ?>&pembayaran_det_id=<?php echo $pembDetUtama; ?>&uangmuka_id=<?php echo $uangmukaId; ?>&catatan=<?php echo $_POST["catatan"]; ?>&pembayaran_id=<?php echo $_POST["pembayaran_id"]; ?>', 'Kwitansi');

        <?php } ?>
        //inputKwitansi('<?php echo $pembDetId; ?>'); 
        //  document.location.href='<?php // echo $thisPage;
                                    ?>';
                                    document.location.href = '<?php echo $backPage; ?>';
                                  <?php } else ?>
      <?php if ($_POST["reg_tipe_rawat"] == 'I') { ?> //JIKA RAWAT INAP
        <?php if ($_POST["dep_cetak_rincian"] == 'y') { ?>
          BukaWindow('cetak_kwitansi_rincian.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtDibayar"][0]); ?>&pembayaran_det_id=<?php echo $pembDetId; ?>&uangmuka_id=<?php echo $uangmukaId; ?>', 'Kwitansi');
        <?php } else { ?>
          BukaWindow('cetak_kwitansi_irna.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"]; ?>&id_reg=<?php echo $_POST["id_reg"]; ?>&ket=<?php echo $_POST["fol_keterangan"]; ?>&dis=<?php echo StripCurrency($_POST["txtDiskon"]); ?>&disper=<?php echo $_POST["diskonpersen"]; ?>&pembul=<?php echo $_POST["pembayaran_det_pembulatan"]; ?>&total=<?php echo $_POST["total_harga"]; ?>&totalbiayaservice=<?php echo $_POST["txtTotalBiayaService"]; ?>&deposit_awal=<?php echo $_POST["deposit_nominal_awal"]; ?>&deposit=<?php echo $_POST["deposit_nominal"]; ?>&dibayar=<?php echo StripCurrency($_POST["txtdibayar1"]); ?>&dibayar2=<?php echo StripCurrency($_POST["txtdibayar2"]); ?>&dibayar3=<?php echo StripCurrency($_POST["txtdibayar3"]); ?>&pembayaran_det_id=<?php echo $pembDetUtama; ?>&uangmuka_id=<?php echo $uangmukaId; ?>&catatan=<?php echo $_POST["catatan"]; ?>&pembayaran_id=<?php echo $_POST["pembayaran_id"]; ?>', 'Kwitansi');

        <?php } ?>
        //inputKwitansi('<?php echo $pembDetId; ?>'); 


        //  document.location.href='<?php //echo $thisPage;
                                    ?>';

                                    document.location.href = '<?php echo $backPage; ?>';
                                  <?php } ?>

                                <?php } ?>
                              </script>
                              <script type="text/javascript">
                                $('#id_jbayar1').on('change', function() {
                                  console.log(this.value);
      // var jenis_pasien = this.value;
      // for (var i = 0; i < '<?php count(dataJenisBayar4) ?>'; i++) {
      //   if ('<?php echo $dataJenisBayar4 ?>' == this.value) {
      //   console.log('ok');
      // }
      // }
      //Sementara Ditutup Muslimat
      // if (this.value == '02' || this.value == '0202' || this.value == '0204') {
      //   alert("Pilih Jenis Bayar Yang Paling Detail");
      // }
    });
                                $('#id_jbayar2').on('change', function() {
                                  console.log(this.value);
      //Sementara Ditutup Muslimat
      // if (this.value == '02' || this.value == '0202' || this.value == '0204') {
      //   alert("Pilih Jenis Bayar Yang Paling Detail");
      // }
    });
                                $('#id_jbayar3').on('change', function() {
                                  console.log(this.value);
      //Sementara Ditutup Muslimat
      // if (this.value == '02' || this.value == '0202' || this.value == '0204') {
      //   alert("Pilih Jenis Bayar Yang Paling Detail");
      // }
    });
                                $('#txtdibayar1').on('change', function() {
                                  var a = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
                                  aa = a * 1;
                                  var tunai = $('#id_jbayar1').val();
                                  var q = aa;
                                  console.log(q);
      //alert( this.value );
      if (q > '<?= $grandTotalHarga ?>' && tunai != '01') {
        alert('Pembayaran Non Tunai Tidak Boleh Melebihi Total Tagihan');
        document.getElementById('txtdibayar1').value = '0';
      }
    });
                                $('#txtdibayar2').on('change', function() {
                                  var a = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
                                  var b = document.getElementById('txtdibayar2').value.toString().replace(/\,/g, "");
                                  aa = a * 1;
                                  bb = b * 1;
                                  var q = aa + bb;
                                  console.log(q);
      //alert( this.value );
      if (q > '<?= $grandTotalHarga ?>') {
        alert('Pembayaran Non Tunai Tidak Boleh Melebihi Total Tagihan');
        document.getElementById('txtdibayar2').value = '0';
      }
    });
                                $('#txtdibayar3').on('change', function() {
                                  var a = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
                                  var b = document.getElementById('txtdibayar2').value.toString().replace(/\,/g, "");
                                  var c = document.getElementById('txtdibayar3').value.toString().replace(/\,/g, "");
                                  aa = a * 1;
                                  bb = b * 1;
                                  cc = c * 1;
                                  var q = aa + bb + cc;
                                  console.log(q);
      //alert( this.value );
      if (q > '<?= $grandTotalHarga ?>') {
        alert('Pembayaran Non Tunai Tidak Boleh Melebihi Total Tagihan');
        document.getElementById('txtdibayar3').value = '0';
      }
    });
  </script>
  <script>
    var input = $("<input>")
    .attr("type", "hidden")
    .attr("name", "btnBayar").val("Bayar");
    $('#btnBayar').on('click', function() {
      var a = document.getElementById('txtdibayar1').value.toString().replace(/\,/g, "");
      var b = document.getElementById('txtdibayar2').value.toString().replace(/\,/g, "");
      var c = document.getElementById('txtdibayar3').value.toString().replace(/\,/g, "");
      var d = document.getElementById('deposit_nominal').value.toString().replace(/\,/g, "");
      var e = document.getElementById('txtDiskon').value.toString().replace(/\,/g, "");
      // var d = document.getElementById('txtdiskon1').value.toString().replace(/\,/g, "");
      aa = a * 1;
      bb = b * 1;
      cc = c * 1;
      dd = d * 1;
      ee = e * 1;
      // dd = d * 1;
      var jumlah = aa + bb + cc;
      if (jumlah > 0 || dd > 0 || ee > 0) {
        $('#demo-form2').append(input);
        $('#demo-form2').submit();
      } 
      else {
        var resl = confirm("apakah anda yakin ingin memasukkan pasien ini ke piutang perorangan?");
        if (resl == true) {
          $('#demo-form2').append(input);
          $('#demo-form2').submit();
        } else {

        }
      }
    });
  </script>

</body>

</html>