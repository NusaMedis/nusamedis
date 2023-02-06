<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
error_reporting(0);
function HitungDetik($tanggalnya)
{
  if (($tanggalnya == NULL) && ($tanggalnya == 0)) {
    return 0;
  } else {
    $temp = explode(" ", trim($tanggalnya));
    if (count($temp) != 6) {
      return 0;
    } else {
      $jam = $temp[0];
      $menit = $temp[2];
      $detik = $temp[4];
      return ($jam * 3600) + ($menit * 60) + $detik;
    }
  }
}

// echo $sql_poli;

// cari jenis bayar ee //
$sql = "select * from global.global_jenis_bayar where jbayar_status='y' and id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by jbayar_id";
$jsBayar = $dtaccess->FetchAll($sql);

if (!$_POST["klinik"]) $_POST["klinik"] = $depId;

//pemanggilan tanggal hari ini 
if (!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
if (!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");

if (!empty($_POST["id_poli"])) $sql_where[] = "a.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
if (!empty($_POST["cust_usr_nama"])) $sql_where[] = "b.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_nama"]);
if ($_POST["cust_usr_kode"])  $sql_where[] = "b.cust_usr_kode like '%" . $_POST["cust_usr_kode"]."%'";



if (!empty($_POST["reg_tipe_rawat"])) {
  $sql_where[] = "a.reg_tipe_rawat = " . QuoteValue(DPE_CHAR, $_POST["reg_tipe_rawat"]);
  $sql_where[] = "c.poli_tipe = " . QuoteValue(DPE_CHAR, $_POST["reg_tipe_rawat"]);
  if ($_POST["reg_tipe_rawat"]=="I") {
    // code...
   $sql_where[] = "a.reg_status !='I9' ";
    $sql_where[] = "e.klinik_waktu_tunggu_status='I2' ";
   $sql_where2[] = "a.klinik_waktu_tunggu_status='I2' ";
 }elseif ($_POST["reg_tipe_rawat"]=="J") {
   // code...

  $sql_where2[] = "a.klinik_waktu_tunggu_status='E0' ";
   $sql_where[] = "e.klinik_waktu_tunggu_status='E0' ";
}elseif ($_POST["reg_tipe_rawat"]=="G") {
   // code...
   $sql_where[] = "e.klinik_waktu_tunggu_status='G0' ";
  $sql_where2[] = "a.klinik_waktu_tunggu_status='G0' ";

}

}




if ($_POST["reg_jenis_pasien"]!="--")  $sql_where[] = "a.reg_jenis_pasien =" . QuoteValue(DPE_CHAR, $_POST["reg_jenis_pasien"]);

// filter waktu tunggu


if (!empty($_POST["id_poli"])) $sql_where[] = "a.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);

if (!empty($_POST["cust_usr_nama"])) $sql_where2[] = "d.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_nama"]);
if ($_POST["cust_usr_kode"])  $sql_where2[] = "d.cust_usr_kode like '%" . $_POST["cust_usr_kode"]."%'";
$sql_where2[] = "b.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"])." 00:00:00");
$sql_where2[] = "b.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"])." 23:59:00");
$sql_where[] = "a.reg_tanggal >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"])." 00:00:00");
$sql_where[] = "a.reg_tanggal <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"])." 23:59:00");
if ($_POST["who_update"]!="--") $sql_where[] = "e.who_update = " . QuoteValue(DPE_CHAR, $_POST["who_update"]);


// if (!empty($_POST["id_poli"])) $sql_where3[] = "b.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);

// if ($_POST["jbayar"]) $sql_where3[] = "i.id_jbayar = " . QuoteValue(DPE_CHAR, $_POST["jbayar"]);
// if ($_POST["who_update"]!="--") $sql_where3[] = "j.who_waktu_tunggu = " . QuoteValue(DPE_CHAR, $_POST["who_update"]);
// if (!empty($_POST["cust_usr_nama"])) $sql_where3[] = "c.cust_usr_nama = " . QuoteValue(DPE_CHAR, $_POST["cust_usr_nama"]);
// if ($_POST["cust_usr_kode"])  $sql_where3[] = "c.cust_usr_kode like '%" . $_POST["cust_usr_kode"]."%'";
// // $sql_where3[] = "a.klinik_waktu_tunggu_when_create >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"])." 00:00:00");
// // $sql_where3[] = "a.klinik_waktu_tunggu_when_create <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"])." 23:59:00");
// if ($_POST["reg_jenis_pasien"]!="--")  $sql_where3[] = "b.reg_jenis_pasien =" . QuoteValue(DPE_CHAR, $_POST["reg_jenis_pasien"]);

  // $sql_where[] = "c.poli_tipe!='A' and c.poli_tipe!='L' and c.poli_tipe!='R' and c.poli_tipe!='N' and c.poli_tipe!='O'";
//     $sql_where[] = "1=1";

$jmlHari = HitungHari(date_db($_POST["tgl_awal"]), date_db($_POST["tgl_akhir"]));

if ($_POST["btnLanjut"] || $_POST["btnExcel"]) {
  //untuk mencari tanggal
  $sql_where = implode(" and ", $sql_where);

  $sql = "select a.*,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_kode, poli_nama,d.rawatinap_tanggal_keluar,f.jenis_nama,e.klinik_waktu_tunggu_status from  klinik.klinik_registrasi a
  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
  left join global.global_auth_poli c on a.id_poli = c.poli_id
  left join klinik.klinik_rawatinap d on a.reg_id = d.id_reg
  left join klinik.klinik_waktu_tunggu e on e.id_reg = a.reg_id
  left join global.global_jenis_pasien f on a.reg_jenis_pasien = f.jenis_id";

  $sql .= " where (b.cust_usr_kode !='100' or b.cust_usr_kode !='500') and a.id_dep = '$depId' and (a.reg_utama=a.reg_id or reg_utama is null)  and ".$sql_where; 
  $sql .= "order by a.reg_when_update";
  
  $rs = $dtaccess->Execute($sql);
  $dataRegistrasi = $dtaccess->FetchAll($rs);

  $sql = "select * from  klinik.klinik_waktu_tunggu_status";
  $sql .= " where waktu_tunggu_tipe_rawat='$_GET[reg_tipe_rawat]' and waktu_tunggu_status_flag='y' order by waktu_tunggu_status_urut"; 
  $rs = $dtaccess->Execute($sql);
  $dataStatus = $dtaccess->FetchAll($rs);

  $sql_where2 = implode(" and ",$sql_where2);

  $sql = "select a.id_reg, a.klinik_waktu_tunggu_when_create,a.who_update, a.klinik_waktu_tunggu_when_update, klinik_waktu_tunggu_durasi, a.klinik_waktu_tunggu_durasi_detik,a.klinik_waktu_tunggu_status from 
  klinik.klinik_waktu_tunggu a
  left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
  left join klinik.klinik_waktu_tunggu_status c on c.waktu_tunggu_status_id = a.id_waktu_tunggu_status";
  $sql .= " where  reg_status not like 'A%' and ".$sql_where2;         
  $sql .= "order by b.reg_when_update,c.waktu_tunggu_status_urut";
    // echo $sql;
  $rs = $dtaccess->Execute($sql); 
  while($row = $dtaccess->Fetch($rs)) {
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"] = $row["klinik_waktu_tunggu_when_create"];  
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_update"] = $row["klinik_waktu_tunggu_when_update"];      
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi_detik"] = $row["klinik_waktu_tunggu_durasi_detik"];      
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"] = $row["klinik_waktu_tunggu_durasi"];     
    $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["who_update"] = $row["who_update"]; 

  }



  //var_dump($datawaktuTunggu);


  // --- construct new table ---- //
  $counterHeader = 0;
  $counterHeader2 = 0;
  $counterHeader3 = 0;
// echo '<i style="color:blue;font-size:30px;font-family:calibri ;">
//       hello php color </i> ';
  $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Reg";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Pulang";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;



  $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Medrec";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Lahir";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;




  $tbHeader[0][$counterHeader][TABLE_ISI] = "Informasi Waktu";

  $tbHeader[0][$counterHeader][TABLE_COLSPAN] =2;
  $counterHeader++;


  $tbHeader[1][$counterHeader2][TABLE_ISI] = "Mulai";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
  $counterHeader2++;

  $tbHeader[1][$counterHeader2][TABLE_ISI] = "Selesai";
  $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
  $counterHeader2++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Tunggu";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;


  $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
  $counterHeader++;






  $tgl = date_db($_POST["tgl_awal"]);
  $total_durasi = array(0, 0);
  $jumlah_berdurasi = array(0, 0);
  for ($i = 0, $counter = 0, $n = count($dataRegistrasi); $i < $n; $i++, $counter = 0) {



// <p style="color: red; text-align: center">
//       Request has been sent. Please wait for my reply!
//       </p>
//       if ($dataTable[$i]['is_posting'] == 'n') echo "style='color: red;'" if ($dataTable[$i]['is_posting'] == 'y') echo "style='color: black;'"

$tbContent[$i][$counter][TABLE_ISI] = ($i + 1) . ".";
$tbContent[$i][$counter][TABLE_ALIGN] = "right";

$counter++;



$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["reg_tanggal"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["reg_tanggal_pulang"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_kode"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = format_date($dataRegistrasi[$i]["cust_usr_tanggal_lahir"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

if ($dataRegistrasi[$i]["reg_tipe_rawat"]=="I") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Inap";
}
elseif ($dataRegistrasi[$i]["reg_tipe_rawat"]=="G") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Darurat";
}
elseif ($dataRegistrasi[$i]["reg_tipe_rawat"]=="J") {
      // code...
  $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";
}
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;



$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["poli_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["jenis_nama"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;





$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "center";
$counter++;

$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_update"]);
$tbContent[$i][$counter][TABLE_ALIGN] = "center";
$counter++;

$hours = floor($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] / 3600);
$minutes = floor(($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] / 60) % 60);
$seconds = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi_detik"] % 60;
$hasil=($diff->d *86400)+ ($diff->h * 60) +$diff->i;
$detik=$hasil*60;
// $tbContent[$i][$counter][TABLE_ISI] =   $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]]["K0"]["klinik_waktu_tunggu_durasi"];
$tbContent[$i][$counter][TABLE_ISI] =  $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"];
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;


$tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataRegistrasi[$i]["klinik_waktu_tunggu_status"]]["who_update"] ;
$tbContent[$i][$counter][TABLE_ALIGN] = "left";
$counter++;





$tgl = DateAdd($tgl, 1);
    //print_r($tgl);   



$totsampai+=$detik+$diff->s;


}

//  $sql_where3 = implode(" and ", $sql_where3);

// $sql = "select count(a.klinik_waktu_tunggu_id) as jumlah from klinik.klinik_waktu_tunggu a
// left join klinik.klinik_registrasi b on b.reg_id = a.id_reg
// left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
// left join klinik.klinik_perawatan i on b.reg_id = i.id_reg
// left join klinik.klinik_pembayaran j on j.pembayaran_id = b.id_pembayaran ";
// $sql .= " where a.klinik_waktu_tunggu_status ='I2'  and b.reg_tipe_rawat ='I' and " . $sql_where3;

// $rs = $dtaccess->Execute($sql);
// $rowk1 = $dtaccess->Fetch($rs);

// // echo $rowk0["jumlah"]."-".$rowk1["jumlah"];

// $ratabayar=round($totbayar/$rowk1["jumlah"]);



// $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
// $tbBottom[0][$counter][TABLE_ISI] = "Rata-rata waktu Informasi";


// $tbBottom[0][$counter][TABLE_COLSPAN] = 13;


// $tbBottom[0][$counter][TABLE_ALIGN] = "center";
// $counter++;

// $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
// $tbBottom[0][$counter][TABLE_ISI] = waktuInfo($ratasampai);


// $tbBottom[0][$counter][TABLE_COLSPAN] = 1;


// $tbBottom[0][$counter][TABLE_ALIGN] = "left";
// $counter++;


// $colspan = count($tbHeader[0]);
// }


// function waktuInfo($secs) {  

//   if($secs>=60){$minutes=floor($secs/60);$secs=$secs%60;$r.=$minutes.' Mnt ';}  
//   $r.=$secs.' Dtk';  
//   return $r;  
// }  

// function waktuBayar($secs) {  

//   if($secs>=60){$minutes=floor($secs/60);$secs=$secs%60;$r.=$minutes.' Mnt ';}  
//   $r.=$secs.' Dtk';  
//   return $r;  
}  


//ambil nama poli

// ambil jenis pasien
$sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
$jenisPasien = $dtaccess->FetchAll($rs);

//echo $sql;
$sql = "select dep_nama from global.global_departemen where
dep_id = '" . $_GET["klinik"] . "'";
$rs = $dtaccess->Execute($sql);
$namaKlinik = $dtaccess->Fetch($rs);

//Nama Sekolah
$klinikHeader = "Klinik : " . $namaKlinik["dep_nama"];

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

$sql = "select * from global.global_departemen where dep_id like '%" . $depId . "%' order by dep_id";
$rs = $dtaccess->Execute($sql);
$dataKlinik = $dtaccess->FetchAll($rs);

//ambil jenis pasien
$sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like " . QuoteValue(DPE_CHAR, "%" . $_POST["klinik"]) . " order by usr_id asc ";
$rs = $dtaccess->Execute($sql);
$dataDokter = $dtaccess->FetchAll($rs);

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];
$fotoName = $ROOT . "adm/gambar/img_cfg/" . $konfigurasi["dep_logo"];

if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=rekap_waktu_tunggu_loket.xls');
}

if ($_POST["btnCetak"]) {
  $_x_mode = "cetak";
}
// cari data poliklinik
if (!$_POST['reg_tipe_rawat']) $sql_where_poli[] = "poli_tipe = 'J' ";
elseif ($_POST['reg_tipe_rawat'] == 'N') $sql_where_poli[] = "(poli_tipe = 'A' or poli_tipe = 'L' or poli_tipe = 'R' )";
elseif ($_POST['reg_tipe_rawat']) $sql_where_poli[] = "poli_tipe = " . QuoteValue(DPE_CHAR, $_POST['reg_tipe_rawat']);
$sql_poli = "select poli_nama, poli_id from  global.global_auth_poli";
if ($sql_where_poli) $sql_poli .= " where " . implode(" and ", $sql_where_poli);
$sql_poli .= " order by poli_tipe asc";
$rs_poli = $dtaccess->Execute($sql_poli);
$dataPolitipe = $dtaccess->FetchAll($rs_poli);
$tableHeader = "Loket | LAPORAN WAKTU TUNGGU Loket";

?>
<?php if (!$_POST["btnExcel"]) { ?>


  <!DOCTYPE html>
  <html lang="en">
  <?php require_once($LAY . "header.php") ?>

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
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                <?php } ?>
                <div class="x_title">
                  <h2>LAPORAN WAKTU TUNGGU Loket</h2>
                  <div class="clearfix"></div>
                </div>
                <?php if (!$_POST["btnExcel"]) { ?>
                  <div class="x_content">
                    <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                      <?php if (!$_POST["btnExcel"]) { ?>

                        <script language="JavaScript">
                          function CheckSimpan(frm) {
                            if (!frm.tgl_awal.value) {
                              alert("Tanggal Harus Diisi");
                              return false;
                            }

                            if (!CheckDate(frm.tgl_awal.value)) {
                              return false;
                            }
                          }

                          <?php if ($_x_mode == "cetak") { ?>
                            window.open('rekap_waktu_tunggu_cetak.php?klinik=<?php echo $_POST["klinik"]; ?>&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>&shift=<?php echo $_POST["shift"]; ?>&dokter=<?php echo $_POST["id_dokter"]; ?>&id_jenis=<?php echo $_POST["id_jenis"]; ?>&id_poli=<?php echo $_POST["id_poli"]; ?>&reg_tipe_rawat=<?= $_POST["reg_tipe_rawat"] ?>&reg_jenis_pasien=<?= $_POST["reg_jenis_pasien"] ?>&jbayar=<?= $_POST["jbayar"] ?>&cust_usr_kode=<?= $_POST["cust_usr_kode"] ?>&cust_usr_nama=<?= $_POST["cust_usr_nama"] ?>&who_update=<?= $_POST["who_update"] ?>', '_blank');
                          <?php } ?>
                        </script>

                        <link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
                        <script src="<?php echo $APLICATION_ROOT; ?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
                        <script src="<?php echo $APLICATION_ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
                      <?php } ?>
                      <?php if (!$_POST["btnExcel"]) { ?>
                      <!-- <script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '50%',
'height' : '100%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script> -->

<script type="text/javascript" src="<?php echo $APLICATION_ROOT; ?>lib/script/scroll_ipad2.js"></script>
<style type="text/css">
#top {
  background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0068c9), to(#007bed));
  background: -moz-linear-gradient(top, #0068c9, #007bed);
}

#footer {
  background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#007bed), to(#0068c9));
  background: -moz-linear-gradient(top, #007bed, #0068c9);
}
</style>
<div class="col-md-4 col-sm-6 col-xs-12">
  <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
  <div class='input-group date' id='datepicker'>
    <input name="tgl_awal" type='text' class="form-control" value="<?php if ($_POST['tgl_awal']) {
      echo $_POST['tgl_awal'];
      } else {
        echo date('d-m-Y');
      } ?>" />
      <span class="input-group-addon">
        <span class="fa fa-calendar">
        </span>
      </span>
    </div>

    <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
    <div class='input-group date' id='datepicker2'>
      <input name="tgl_akhir" type='text' class="form-control" value="<?php if ($_POST['tgl_akhir']) {
        echo $_POST['tgl_akhir'];
        } else {
          echo date('d-m-Y');
        } ?>" />
        <span class="input-group-addon">
          <span class="fa fa-calendar">
          </span>
        </span>
      </div>


<!--       <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Status</label>
      <select name="reg_status" class="select2_single form-control" id="reg_status" required="";>
        <option value="I2" <?php if ($_POST["reg_status"] == "I2") echo "selected" ?>>Menginap</option>;
        <option value="I3" <?php if ($_POST["reg_status"] == "I3") echo "selected" ?>>Rencana Pulang</option>



      </select> -->





    </div>
                      <!--
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
            <div id="div_header"><?php if ($userData["rol"] != '2') { ?>              
                      <td width="20%" class="tablecontent">
                    <?php } else { ?>
                      <td width="20%" class="tablecontent">
                    <?php } ?>
                      <select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> 
                        <option value="0" >[ Pilih Cara Bayar ]</option>
                          <?php for ($i = 0, $n = count($jenisPasien); $i < $n; $i++) { ?>
                        <option value="<?php echo $jenisPasien[$i]["jenis_id"]; ?>" <?php if ($jenisPasien[$i]["jenis_id"] == $_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"]; ?>');"><?php echo ($i + 1) . ". " . $jenisPasien[$i]["jenis_nama"]; ?></option>
                      <?php } ?>
                  </select>
            </div> 
          </div>   -->


          <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat *</label>
            <select name="reg_tipe_rawat" class="select2_single form-control" id="reg_tipe_rawat" required="" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event)" ;>
              <option value="J" <?php if ($_POST["reg_tipe_rawat"] == "J") echo "selected" ?>>Rawat Jalan</option>;
              <option value="I" <?php if ($_POST["reg_tipe_rawat"] == "I") echo "selected" ?>>Rawat Inap</option>
              <option value="G" <?php if ($_POST["reg_tipe_rawat"] == "G") echo "selected" ?>>IGD</option>


            </select>
          </div>

          <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik </label>
            <select name="id_poli" class="select2_single form-control" id="id_poli">
              <option value="">Pilih Klinik</option>
              <?php for ($i = 0, $n = count($dataPolitipe); $i < $n; $i++) { ?>
                <option class="inputField" value="<?php echo $dataPolitipe[$i]["poli_id"]; ?>" <?php if ($_POST["id_poli"] == $dataPolitipe[$i]["poli_id"]) echo "selected" ?>><?php echo $dataPolitipe[$i]["poli_nama"]; ?>&nbsp;</option>
              <?php } ?>
            </select>
          </div>


          <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien </label>
            <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
          </div>
          <div class="col-md-4 col-sm-6 col-xs-12">
            <label class="control-label col-md-12 col-sm-12 col-xs-12">No Rekam Medik</label>
            <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
          </div>


<!-- 
                        <div class="col-md-4 col-sm-6 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik </label>
                          <select name="id_poli" class="select2_single form-control" id="id_poli">
                            <option value="">Pilih Klinik</option>
                            <?php for ($i = 0, $n = count($dataPolitipe); $i < $n; $i++) { ?>
                              <option class="inputField" value="<?php echo $dataPolitipe[$i]["poli_id"]; ?>" <?php if ($_POST["id_poli"] == $dataPolitipe[$i]["poli_id"]) echo "selected" ?>><?php echo $dataPolitipe[$i]["poli_nama"]; ?>&nbsp;</option>
                            <?php } ?>
                          </select>
                        </div>

                      -->




                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien </label>

                        <select name="reg_jenis_pasien" class="select2_single form-control" id="reg_jenis_pasien">
                          <option value="--" <?php if ($_POST['reg_jenis_pasien'] == '--') echo "selected"; ?>>--Pilih Cara Bayar--</option>
                          <option value="2" <?php if ($_POST['reg_jenis_pasien'] == '2') echo "selected"; ?>>1. Umum</option>
                          <option value="5" <?php if ($_POST['reg_jenis_pasien'] == '5') echo "selected"; ?>>2. JKN / KIS</option>
                          <option value="7" <?php if ($_POST['reg_jenis_pasien'] == '7') echo "selected"; ?>>3. Asuransi</option>
                          <option value="20" <?php if ($_POST['reg_jenis_pasien'] == '20') echo "selected"; ?>>4. Karyawan</option>
                        </select>
                      </div>

                      <?php 
                      $sql = "select * from global.global_auth_user where id_rol='32' order by usr_name";
                      $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
                      $dataKasir2 = $dtaccess->FetchAll($rs);
                      ?>


                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Petugas</label>
                        <select class="select2_single form-control" name="who_update" onKeyDown="return tabOnEnter(this, event);">
                          <option value="--">[ Pilih Nama Petugas ]</option>
                          <?php for ($i = 0, $n = count($dataKasir2); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataKasir2[$i]["usr_name"]; ?>" <?php if ($_POST["who_update"] == $dataKasir2[$i]["usr_name"]) echo "selected"; ?>><?php echo $dataKasir2[$i]["usr_name"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>


          <!--               <div class="col-md-4 col-sm-6 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Pilih Status</label>
                          <select name="reg_status" class="select2_single form-control" id="reg_status" required="";>
                            <option value="I2" <?php if ($_POST["reg_status"] == "I2") echo "selected" ?>>Menginap</option>;
                            <option value="I3" <?php if ($_POST["reg_status"] == "I3") echo "selected" ?>>Rencana Pulang</option>
                            <option value="I4" <?php if ($_POST["reg_status"] == "I4") echo "selected" ?>>Pulang</option>




                          </select>
                        </div> -->

                <!--         <div class="col-md-4 col-sm-6 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Status Posting</label>
                          <select name="posting" class="select2_single form-control" id="posting" required="";>

                            <option value="N" <?php if ($_POST["posting"] == "N") echo "selected" ?>>Belum Posting</option>
                            <option value="Y" <?php if ($_POST["posting"] == "Y") echo "selected" ?>>Posting</option>



                          </select>
                        </div> -->
                        

                        <div class="col-md-4 col-sm-6 col-xs-12">
                         <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                         <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
                         <!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
                         <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-danger">
                         <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                       </div>


                       <div class="clearfix"></div>
                       <? if ($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]) { ?>
                       <? } ?>
                       <? if ($_x_mode == "Edit") { ?>
                        <?php echo $view->RenderHidden("kategori_tindakan_id", "kategori_tindakan_id", $biayaId); ?>
                      <? } ?>

                      <!-- <script type="text/javascript">
            Calendar.setup({
              inputField     :    "tanggal_awal",      // id of the input field
              ifFormat       :    "<?= $formatCal; ?>",       // format of the input field
              showsTime      :    false,            // will display a time selector
              button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
              singleClick    :    true,           // double-click mode
              step           :    1                // show all years in drop-down boxes (instead of every other year as default)
            });
    
            Calendar.setup({
              inputField     :    "tgl_akhir",      // id of the input field
              ifFormat       :    "<?= $formatCal; ?>",       // format of the input field
              showsTime      :    false,            // will display a time selector
              button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
              singleClick    :    true,           // double-click mode
              step           :    1                // show all years in drop-down boxes (instead of every other year as default)
            });
          </script> -->
        </form>
      <?php }
    } ?>
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
      <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>
    </div>
    <?php
    if (($total_durasi[0] != 0) && ($jumlah_berdurasi[0] != 0)) {
      ?>
      <h4 align="right">Rata-rata durasi Pasien selesai dilayani: <?= FormatTime(round($total_durasi[0] / $jumlah_berdurasi[0])); ?></h4>
      <?
    }
    if (($total_durasi[1] != 0) && ($jumlah_berdurasi[1] != 0)) {
      ?>
      <!-- <h4 align="right">Rata-rata durasi Pasien Selesai di Layani: <?= FormatTime(round($total_durasi[1] / $jumlah_berdurasi[1])); ?></h4> -->
      <?
    }
    ?>

  </div>
</div>
</div>
</div>
</div>
<!-- /page content -->

<!-- footer content -->
<?php if (!$_POST["btnExcel"]) { ?>
  <!-- /page content -->

  <!-- footer content -->
  <?php require_once($LAY . "footer.php") ?>
  <!-- /footer content -->
</div>
</div>

<?php require_once($LAY . "js.php") ?>

</body>

</html>

<?php } ?>

</script>
<?php if ($_POST["btnExcel"]) { ?>

  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" colspan="51">
        <strong>Lap. Mutu Kasir<br />
          <?php //echo $konfigurasi["dep_nama"]
          ?>&nbsp;&nbsp;<?php //echo $konfigurasi["dep_kop_surat_1"]
                        ?>&nbsp;&nbsp;<?php //echo $konfigurasi["dep_kop_surat_2"]
                        ?>
                        <?php if ($_POST["tgl_awal"] == $_POST["tgl_akhir"]) {
                          echo "Tanggal : " . $_POST["tgl_awal"];
                        } elseif ($_POST["tgl_awal"] != $_POST["tgl_akhir"]) {
                          echo "Periode : " . $_POST["tgl_awal"] . " - " . $_POST["tgl_akhir"];
                        }  ?>
                        <br /><br />
                      </strong>
                    </td>
                  </tr>
                  <tr class="tableheader">
                    <td align="left" colspan="10">
                      <br><br>
                      <b>Nama Rumah Sakit : <?php echo $depNama; ?></b>
                      <br /><br />
                    </td>
                  </tr>
                </table>
              <?php } ?>
              <?php if (!$_POST["btnExcel"]) { ?>

                <br />
                <?php } ?>