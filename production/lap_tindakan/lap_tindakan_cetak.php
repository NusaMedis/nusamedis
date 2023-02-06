  <?php
  require_once("../penghubung.inc.php");
  require_once($LIB . "login.php");
  require_once($LIB . "encrypt.php");
  require_once($LIB . "datamodel.php");
  require_once($LIB . "dateLib.php");
  require_once($LIB . "currency.php");
  require_once($LIB . "expAJAX.php");
  require_once($LIB . "tampilan.php");

  $view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();
  $table = new InoTable("table", "100%", "left");
  $userId = $auth->GetUserId();
  $userData = $auth->GetUserData();
  $depNama = $auth->GetDepNama();
  $depId = $auth->GetDepId();
  $tahunTarif = $auth->GetTahunTarif();
  /*$thisPage = "report_setoran_loket.php";
     $printPage = "report_setoran_loket_cetak.php?";*/

  //if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
  if ($_GET["klinik"]) {
    $_POST["klinik"] = $_GET["klinik"];
  } else if (!$_POST["klinik"]) {
    $_POST["klinik"] = $depId;
  }

  if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
    die("Maaf anda tidak berhak membuka halaman ini....");
    exit(1);
  } else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
    echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
    exit(1);
  }

  $sql = "select * from  klinik.klinik_split where id_tahun_tarif=" . QuoteValue(DPE_CHAR, $tahunTarif) . " order by split_urut asc ";
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_KLINIK);
  $dataSplit = $dtaccess->FetchAll($rs);
  // echo $sql;

  // KONFIGURASI
  $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_POST["klinik"]);
  $rs = $dtaccess->Execute($sql);
  $konfigurasi = $dtaccess->Fetch($rs);
  $_POST["dep_id"] = $konfigurasi["dep_id"];
  $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

  $skr = date("d-m-Y");
  $time = date("H:i:s");


  //tanggal

if ($_GET['status'] == 'y') {
 if($_GET["tgl_awal"]) $sql_where[] = "x.pembayaran_det_tgl >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
 if($_GET["tgl_akhir"]) $sql_where[] = "x.pembayaran_det_tgl <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
}else{
 if($_GET["tgl_awal"]) $sql_where[] = "d.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
 if($_GET["tgl_akhir"]) $sql_where[] = "d.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
}
if ($_GET["js_biaya"]) $sql_where[] = "i.pembayaran_jenis = " . QuoteValue(DPE_CHAR, $_GET["js_biaya"]);
//if($_GET["jbayar"]) $sql_where[] = "m.id_jbayar = ".QuoteValue(DPE_CHAR,$_GET["jbayar"]);
if ($_GET["cust_usr_kode"]) $sql_where[] = "c.cust_usr_kode like " . QuoteValue(DPE_CHAR, "%" . $_GET["cust_usr_kode"] . "%");
//$sql_where[] = " (pembayaran_flag='y' or pembayaran_flag='k') ";

if ($_GET["id_dokter"]) $sql_where[] = "a.id_dokter = " . QuoteValue(DPE_CHAR, $_GET["id_dokter"]);
if ($_POST["id_poli"]) { 
  if ($_POST["id_poli"] == 'ed6ab21fcc06cf3bda1186c44b59f453') {
    $sql_where[] = " a.is_operasi = 'y' ";
  }
  else{
    $sql_where[] = "d.id_poli = " . QuoteValue(DPE_CHAR, $_POST["id_poli"]);
  }
  
}

if ($_GET["reg_shift"]) {
  $sql_where[] = " d.reg_shift = " . QuoteValue(DPE_DATE, $_GET["reg_shift"]);
}

if ($_GET["reg_tipe_layanan"]) {
  $sql_where[] = "d.reg_tipe_layanan = " . QuoteValue(DPE_CHAR, $_GET["reg_tipe_layanan"]);
}

if ($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"] != "0") {
  $sql_where[] = "d.reg_jenis_pasien = " . QuoteValue(DPE_CHAR, $_GET["cust_usr_jenis"]);
}

if ($_GET["ush_id"]) {
  $sql_where[] = "d.id_perusahaan = " . QuoteValue(DPE_CHAR, $_GET["ush_id"]);
}

if ($_GET["cito"]) {
  $sql_where[] = "n.is_cito = " . QuoteValue(DPE_CHAR, $_GET["cito"]);
}

//if($userId == 'b9ead727d46bc226f23a7c1666c2d9fb' || $userId=='fed7a2bfc3479110ea037d1940b44c7c'){
if ($_GET["usr_id"] <> '--') {
  $sql_where[] = "i.pembayaran_who_create = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
}
//}

if ($_GET["layanan"] <> "--") {
  if ($_GET["layanan"] == "A") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='J'";
  } elseif ($_GET["layanan"] == "I") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='I'";
  } elseif ($_GET["layanan"] == "G") {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500') and d.reg_tipe_rawat='G'";
  } else {
    $sql_where[] = "(a.id_cust_usr <>'100' or a.id_cust_usr <>'500')";
  }
}

if ($_GET["fol_nama"]) $sql_where[] = "upper(a.fol_nama) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_GET["fol_nama"]) . "%");

  $sql_where = implode(" and ", $sql_where);
  $sql = "select a.*,  c.cust_usr_nama, c.cust_usr_kode, c.cust_usr_umur, c.cust_usr_tanggal_lahir, c.cust_usr_alamat, c.cust_usr_penanggung_jawab, b.biaya_nama,              
             f.jenis_nama, g.usr_name as dokter, i.*, d.reg_jenis_pasien, d.reg_tanggal, d.reg_waktu, e.dep_nama,
             j.poli_nama, k.tipe_biaya_nama, l.shift_nama, m.usr_name as ptg_entri, n.is_cito         
             from  klinik.klinik_folio a  
              left join klinik.klinik_registrasi d on a.id_reg = d.reg_id
              left join klinik.klinik_pembayaran i on i.pembayaran_id = a.id_pembayaran 
             left join global.global_customer_user c on a.id_cust_usr = c.cust_usr_id
             left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya
             left join global.global_departemen e on e.dep_id = a.id_dep 
             left join global.global_jenis_pasien f on f.jenis_id = d.reg_jenis_pasien
             left join global.global_auth_user g on d.id_dokter = g.usr_id
             left join global.global_auth_poli j on j.poli_id = d.id_poli
             left join global.global_tipe_biaya k on k.tipe_biaya_id = d.reg_tipe_layanan
             left join global.global_shift l on l.shift_id = d.reg_shift
             left join global.global_auth_user m on a.who_when_update = m.usr_id
             left join klinik.klinik_biaya_tarif n on a.id_biaya_tarif = n.biaya_tarif_id";
  //$sql .= " where d.reg_tipe_rawat='J' and 1=1 and ".$sql_where; 
  $sql .= " where 1=1 and " . $sql_where;
  $sql .= " order by i.pembayaran_create,a.id_pembayaran,a.fol_waktu";
  // echo $sql;
  $dataTable = $dtaccess->FetchAll($sql);
for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
  if ($dataTable[$i]["id_pembayaran"] == $dataTable[$i - 1]["id_pembayaran"]) {
    $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
  }
}
$counter = 0;
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Registrasi";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal lahir";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "&nbsp; Umur Pasien &nbsp;";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "13%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Penanggung Jawab";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Pelaksana";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

/*$tbHeader[0][$counterHeader][TABLE_ISI] = "Ptg. Entri";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%"; 
     $counterHeader++;*/

$tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Petugas";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$jumHeader = $counterHeader;
for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {

  $sql = "select usr_name from klinik.klinik_folio_pelaksana b 
          left join global.global_auth_user g on b.id_usr=g.usr_id 
          left join klinik.klinik_folio a on a.fol_id=b.id_fol 
          where b.id_fol=" . QuoteValue(DPE_CHAR, $dataTable[$i]["fol_id"]) . " order by fol_pelaksana_tipe asc";
  $pelaksana = $dtaccess->FetchAll($sql);
  //echo $sql;

  if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]] + 1;

    $umur = explode('~', $dataTable[$i]["cust_usr_umur"]);
    

    $tbContent[$i][$counter][TABLE_ISI] = $m + 1;
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $m++;

    $time = explode(" ", $daytime[0]);
    $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["pembayaran_tanggal"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    if ($dataTable[$i]["cust_usr_kode"] == '500' || $dataTable[$i]["cust_usr_kode"] == '100') {
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_keterangan"];
    } else {
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
    }
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_tanggal_lahir"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $umur[0]. " Tahun ".$umur[1]. " Bulan ".$umur[2]." Hari";
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_penanggung_jawab"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
  }
  //echo $sql;
  if ($dataTable[$i]['is_cito'] == 'C') {
    $cito = " ( CITO )";
  } else {
    $cito = "";
  }
  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_nama"] . $cito;
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_waktu"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;
  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["fol_jumlah"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $pelaksana[1]["usr_name"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  if ($dataTable[$i]["id_pembayaran"] != $dataTable[$i - 1]["id_pembayaran"]) {
    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_who_create"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
  }
}

$counter = 0;

$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 6;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;



$tbBottom[0][$counter][TABLE_ISI] = $dataTable[$i]["fol_jumlah"];
$tbBottom[0][$counter][TABLE_ALIGN] = "right";
$counter++;

$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
$tbBottom[0][$counter][TABLE_COLSPAN] = 7;
$tbBottom[0][$counter][TABLE_ALIGN] = "center";
$counter++;

  $tableHeader = "Report Tindakan IRJ";


  //ambil jenis pasien
  $sql = "select * from global.global_jenis_pasien where jenis_id=" . QuoteValue(DPE_NUMERIC, $_GET["cust_usr_jenis"]);
  $rs = $dtaccess->Execute($sql);
  $jenisPasien = $dtaccess->Fetch($rs);

  //ambil nama poli
  $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where poli_id = " . QuoteValue(DPE_CHAR, $_GET["id_poli"]);
  $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $dataPoli = $dtaccess->Fetch($rs_edit);

  //Data Klinik
  $sql = "select * from global.global_departemen where dep_id like '" . $_GET["klinik"] . "%' order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);

  //echo $sql;
  $sql = "select dep_nama from global.global_departemen where dep_id = '" . $_GET["klinik"] . "'";
  $rs = $dtaccess->Execute($sql);
  $namaKlinik = $dtaccess->Fetch($rs);
  $klinikHeader = "Klinik : " . $namaKlinik["dep_nama"];

  // cari tipe layanan
  $sql = "select * from global.global_tipe_biaya where tipe_biaya_id = '" . $_GET["layanan"] . "'";
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $tipeBiaya = $dtaccess->Fetch($rs);

  //cari shift by id
  $sql = "select * from global.global_shift where shift_id = '" . $_GET["shift"] . "'";
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $dataShiftId = $dtaccess->Fetch($rs);

  //cari nama petugas by id
  $sql = "select * from global.global_auth_user where usr_id = '" . $_GET["kasir"] . "'";
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $dataKasirId = $dtaccess->Fetch($rs);

  $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_POST["klinik"]);
  $rs = $dtaccess->Execute($sql);
  $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT . "/gambar/img_cfg";
  if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
  if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];

  if ($konfigurasi["dep_logo"] != "n") {
    $fotoName = $lokasi . "/" . $konfigurasi["dep_logo"];
  } elseif ($konfigurasi["dep_logo"] == "n") {
    $fotoName = $lokasi . "/default.jpg";
  } else {
    $fotoName = $lokasi . "/default.jpg";
  }


  ?>




  <script language="javascript" type="text/javascript">
    window.print();
  </script>

  <!-- Print KwitansiCustom Theme Style -->
  <link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

  <table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
    <tr>
      <td align="center"><img src="<?php echo $fotoName; ?>" height="75"> </td>
      <td align="center" bgcolor="#CCCCCC" id="judul">
        <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"] ?></strong><br></span>
        <span class="judul3">
          <?php echo $konfigurasi["dep_kop_surat_1"] ?></span><br>
        <span class="judul4">
          <?php echo $konfigurasi["dep_kop_surat_2"] ?></span></td>
    </tr>
  </table>
  <br>
  <table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">
    <tr>
      <?php if ($_GET["tgl_awal"] == $_GET["tgl_akhir"]) { ?>
        <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tanggal</td>
        <td width="1%">:</td>
        <td width="19%"><?php echo ($_GET["tgl_awal"]); ?></td>
      <?php } else { ?>
        <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode</td>
        <td width="1%">:</td>
        <td width="19%"><?php echo ($_GET["tgl_awal"]); ?> s/d <?php echo ($_GET["tgl_akhir"]); ?></td>
      <?php } ?>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN TINDAKAN IRJ</td>
    </tr>
    <tr>
      <? if ($_GET["id_poli"] != "") { ?>
        <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik</td>
        <td width="1%">:</td>
        <td colspan="2"><? if ($_GET["id_poli"] != "--") {
                          echo $dataPoli["poli_nama"];
                        } ?> </td>
      <?php } ?>
    </tr>

  </table>
  <br>
  <br>
  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>
      </td>
    </tr>
  </table>