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
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$thisPage = "report_setoran_cicilan.php";
$printPage = "report_setoran_cicilan_cetak.php?";

//  if (!$_GET["klinik"]) $_GET["klinik"]=$depId;
//$_GET["klinik"] = $_GET["klinik"]; 

if ($_GET["klinik"]) {
  $_GET["klinik"] = $_GET["klinik"];
} else if (!$_GET["klinik"]) {
  $_GET["klinik"] = $depId;
}
  if($_GET["tgl_awal"]) $sql_where[] = "a.pembayaran_tanggal >= '".date_format(date_create($_GET["tgl_awal"]), 'Y-m-d')."' ";
  if($_GET["tgl_akhir"]) $sql_where[] = "a.pembayaran_tanggal <= '".date_format(date_create($_GET["tgl_akhir"]), 'Y-m-d')."' ";
  if($_GET["cust_usr_kode"]) $sql_where[] = "cust_usr_kode like ".QuoteValue(DPE_CHAR,'%'.$_GET["cust_usr_kode"].'%');
  if($_GET["id_poli"] != '--') $sql_where[] = "b.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);

  $sql_where = implode(" and ",$sql_where);

  $sql = "SELECT b.reg_jenis_pasien, a.pembayaran_id, a.pembayaran_create, a.pembayaran_diskon, a.pembayaran_total, b.reg_kode_trans, b.reg_keterangan, c.cust_usr_kode, c.cust_usr_nama 
          FROM klinik.klinik_pembayaran a 
          LEFT JOIN klinik.klinik_registrasi b ON a.id_reg = b.reg_id 
          LEFT JOIN global.global_customer_user c ON b.id_cust_usr = c.cust_usr_id 
          WHERE a.pembayaran_id IN (select id_pembayaran from klinik.klinik_pembayaran_det where 1=1";
  if ($_GET["usr_id"] <> "--") $sql .= " and who_when_update = " . QuoteValue(DPE_CHAR, $_GET["usr_id"]);
  $sql .= " ) AND b.reg_tipe_rawat = 'I' AND ".$sql_where;
  $sql .= "ORDER BY a.pembayaran_create ASC ";
  $dataTable = $dtaccess->FetchAll($sql);

$tableHeader = "Report Pembayaran";
// cari nama kasir --



$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_GET["klinik"]);
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
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">
<style>
  @media print {
    break {
      page-break-after: always;
    }
  }
</style>
<script language="javascript" type="text/javascript">
  window.print();
</script>


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
<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">
  <tr>
    <td width="100%" style="text-align:center;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PENERIMAAN INAP</td>
  </tr>
</table>
<br>
<br>
<table width="100%" id="example" class="table table-striped table-bordered dt-responsive nowrap" border="1">
  <thead>
    <?php if($_GET['btnExcel']){ ?>
      <tr>
        <td colspan="83">Periode : <?php echo $_GET['tgl_awal'].' s/d '.$_GET['tgl_akhir'] ?></td>
      </tr>
    <?php } ?>
      <tr>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">TANGGAL</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">NO. REG</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">MEDREC</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">NAMA</td>
                          <td colspan="10" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 3000px;">PEMBAYARAN</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">RETUR</td>
                          <td colspan="11" style="text-align: center; vertical-align: middle; width: 3300px;">PENDAPATAN JASA LAYANAN MEDIK PASIEN UMUM</td>
                          <td colspan="11" style="text-align: center; vertical-align: middle; width: 3300px;">PENDAPATAN JASA LAYANAN MEDIK PASIEN BPJS</td>
                          <td colspan="11" style="text-align: center; vertical-align: middle; width: 3300px;">PENDAPATAN JASA LAYANAN MEDIK PASIEN KONTRAKTOR</td>
                          <td colspan="9" style="text-align: center; vertical-align: middle; width: 2700px;">PENUNJANG</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle; width: 2100px;">FARMASI RSIA</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle; width: 2100px;">FARMASI GRAHA</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">PENJUALAN ALKES</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN LAINNYA NON MEDIK</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">GAS MEDIK</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">SEWA ALAT MEDIK</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">PMI</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">AMBULAN</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">ADM</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">JASA RS</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">JUMLAH</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle; width: 300px;">SELISIH</td>
                        </tr>
                        <tr>
                          <!-- PEMBAYARAN -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">TUNAI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">DEPOSIT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">BCA</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">BRI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">BNI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">BPJS</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">PIUTANG UMUM</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">PIUTAN  ASS+KONT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">DISKON</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; background-color:#98AFC7; width: 300px;">BEBAN KARYAWAN</td>
                            
                          <!-- PEMBAYARAN -->
                          <!-- PASIEN UMUM -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG MEDIK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">GIZI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN INAP</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN OPERASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG NON MEDIK</td>
                          <!-- PASIEN UMUM -->
                          <!-- PASIEN BPJS -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG MEDIK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">GIZI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN INAP</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN OPERASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG NON MEDIK</td>
                          <!-- PASIEN BPJS -->
                          <!-- PASIEN ASURANSI -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG MEDIK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">GIZI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN INAP</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">PENDAPATAN RUANGAN OPERASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">RUANG NON MEDIK</td>
                          <!-- PASIEN ASURANSI -->
                          <!-- PENUNJANG -->
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">RADIOLOGI</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">LAB</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">FISIOTERAFI</td>
                          <!-- PENUNJANG -->
                          <!-- FARMASI RSIA -->
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">ALKES</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">OBAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TUSLAH</td>
                          <!-- FARMASI RSIA -->
                          <!-- FARMASI GRAHA -->
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">ALKES</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle; width: 900px;">OBAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle; width: 300px;">TUSLAH</td>
                          <!-- FARMASI GRAHA -->
                        </tr>
                        <tr>
                          <!-- PENUNJANG -->
                            <!-- Radiologi -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Radiologi -->
                            <!-- Lab -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Lab -->
                            <!-- Fisioterafi -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Fisioterafi -->
                          <!-- PENUNJANG -->
                          <!-- FARMASI RSIA -->
                            <!-- Alkes -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Alkes -->
                            <!-- Obat -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Obat -->
                          <!-- FARMASI RSIA -->
                          <!-- FARMASI GRAHA -->
                            <!-- Alkes -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Alkes -->
                            <!-- Obat -->
                              <td style="text-align: center; vertical-align: middle; width: 300px;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle; width: 300px;">KONTRAKTOR</td>
                            <!-- Obat -->
                          <!-- FARMASI GRAHA -->
                        </tr>
    </thead>
  <tbody>
                        <?php 
                          for ($i = 0; $i < count($dataTable); $i++) { 
                            $sql = "SELECT pembayaran_det_id, pembayaran_det_kwitansi from klinik.klinik_pembayaran_det 
                            where (pembayaran_det_id = id_pembayaran_det_multipayment or id_pembayaran_det_multipayment is null ) and pembayaran_det_tgl >= ".QuoteValue(DPE_DATE,$_GET["tgl_awal"])." and pembayaran_det_tgl <= ".QuoteValue(DPE_DATE,$_GET["tgl_akhir"])." 
                            AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                            $dataPembayaran_det = $dtaccess->Fetch($sql);

                            $id_pembayaran_det = $dataPembayaran_det['pembayaran_det_id'];
                            $no_kwitansi = $dataPembayaran_det['pembayaran_det_kwitansi'];

                            /* PEMBAYARAN */
                              /* Tunai */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = '01' 
                                and pembayaran_det_tgl >= ".QuoteValue(DPE_DATE,$_GET["tgl_awal"])." and pembayaran_det_tgl <= ".QuoteValue(DPE_DATE,$_GET["tgl_akhir"])."
                                AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranTunai = $dtaccess->Fetch($sql);
                              /* Tunai */
                              /* BCA */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = '0204' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranBCA = $dtaccess->Fetch($sql);
                              /* BCA */
                              /* BRI */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = '0203' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranBRI = $dtaccess->Fetch($sql);
                              /* BRI */
                              /* BNI */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = '0201' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranBNI = $dtaccess->Fetch($sql);
                              /* BNI */
                              /* BPJS */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = 'BPJS' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranBPJS = $dtaccess->Fetch($sql);
                              /* BPJS */
                              /* Piutang */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE pembayaran_det_flag = 'P' AND pembayaran_det_tipe_piutang = 'P' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranPiutang = $dtaccess->Fetch($sql);
                              /* Piutang */
                              /* Asuransi */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar IN (SELECT perusahaan_id FROM global.global_perusahaan) AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranAsuransi = $dtaccess->Fetch($sql);
                              /* Asuransi */
                              /* Karyawan */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = 'Karyawan' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranKaryawan = $dtaccess->Fetch($sql);
                              /* Karyawan */
                              /* Diskon */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = 'Diskon' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranDiskon = $dtaccess->Fetch($sql);
                              /* Diskon */
                              /* Retur */
                                $sql = "SELECT sum(deposit_history_nominal) as total FROM klinik.klinik_deposit_history where deposit_history_flag = 'M' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranDeposit = $dtaccess->Fetch($sql);

                                $sql = "SELECT deposit_history_nominal as total FROM klinik.klinik_deposit_history where deposit_history_flag = 'R' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranDepositRetur = $dtaccess->Fetch($sql);
                              /* Retur */
                            /* PEMBAYARAN */
                            /* PENDAPATAN JASA LAYANAN MEDIK */
                              /* JASA DOKTER */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where a.fol_nomor_kwitansi = '$no_kwitansi' and id_prk = '020106' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $JasaDokter = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where  a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020107' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $JasaPerawat = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where  a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020112' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $PendapatanIGD = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where  a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020111' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $PendapatanIRJ = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020116' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $Imunisasi = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where (id_prk = '020113' or id_prk = '020114') and   a.fol_nomor_kwitansi = '$no_kwitansi'  and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $TindakanVK = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where  a.fol_nomor_kwitansi = '$no_kwitansi'  and  id_prk = '02010102010103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RuangMedik = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020115' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $Gizi = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020108' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RuangInap = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010102010102' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RuangOperasi = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left 
                                join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010102010104' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RuangNonMedik = $dtaccess->Fetch($sql);
                              /* JASA DOKTER */
                            /* PENDAPATAN JASA LAYANAN MEDIK */
                            /* RADIOLOGI */
                              /* UMUM */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and reg_jenis_pasien = '2' and id_prk = '020101020103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadUmum = $dtaccess->Fetch($sql);
                              /* UMUM */
                              /* JKN */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and reg_jenis_pasien = '5' and id_prk = '020101020103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadJKN = $dtaccess->Fetch($sql);
                              /* JKN */
                              /* Asuransi */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '7' and id_prk = '020101020103' 
                                and   a.fol_nomor_kwitansi = '$no_kwitansi'  and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadAsuransi = $dtaccess->Fetch($sql);
                              /* Asuransi */
                            /* RADIOLOGI */
                            /* LABORATORIUM */
                              /* UMUM */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and reg_jenis_pasien = '2' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabUmum = $dtaccess->Fetch($sql);
                              /* UMUM */
                              /* JKN */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and reg_jenis_pasien = '5' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabJKN = $dtaccess->Fetch($sql);
                              /* JKN */
                              /* Asuransi */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                                left join klinik.klinik_registrasi b on b.reg_id = a.id_reg 
                                left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya 
                                where   a.fol_nomor_kwitansi = '$no_kwitansi'  and reg_jenis_pasien = '7' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabAsuransi = $dtaccess->Fetch($sql);
                              /* Asuransi */
                            /* LABORATORIUM */
                            /*FARMASI*/
                              /*GRAHA*/
                                $sql = "select sum(penjualan_grandtotal) as total from apotik.apotik_penjualan 
                                where id_fol in(select fol_id from klinik.klinik_folio where  fol_nomor_kwitansi = '$no_kwitansi' and  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']).") and id_gudang = '2'";
                                $ObatGraha = $dtaccess->Fetch($sql);
                                $sql = "select sum(penjualan_tuslag) as total from apotik.apotik_penjualan 
                                where id_fol in(select fol_id from klinik.klinik_folio where fol_nomor_kwitansi = '$no_kwitansi' and  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']).") and id_gudang = '2'";
                                $TuslagGraha = $dtaccess->Fetch($sql);
                                $HargaObatGraha = $ObatGraha['total'] - $TuslagGraha['total'];
                              /*GRAHA*/
                              /*RSIA*/
                                $sql = "select sum(penjualan_grandtotal) as total from apotik.apotik_penjualan 
                                where id_fol in(select fol_id from klinik.klinik_folio where  fol_nomor_kwitansi = '$no_kwitansi' and  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']).") and id_gudang = '3'";
                                $ObatRSIA = $dtaccess->Fetch($sql);
                                $sql = "select sum(penjualan_tuslag) as total from apotik.apotik_penjualan 
                                where id_fol in(select fol_id from klinik.klinik_folio where  fol_nomor_kwitansi = '$no_kwitansi' and  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']).") and id_gudang = '3'";
                                $TuslagRSIA = $dtaccess->Fetch($sql);
                                $HargaObatRSIA = $ObatRSIA['total'] - $TuslagRSIA['total'];
                              /*RSIA*/
                            /*FARMASI*/
                            /*ALKES RUANGAN*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010101010110' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $AlkesRuangan = $dtaccess->Fetch($sql);
                            /*ALKES RUANGAN*/
                            /*PEND LAINNYA NON MEDIK*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010102030101' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $PendLainnyaNonMedik = $dtaccess->Fetch($sql);
                            /*PEND LAINNYA NON MEDIK*/
                            /*GAS MEDIK*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020101020107' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $GasMedik = $dtaccess->Fetch($sql);
                            /*GAS MEDIK*/
                            /*SEWA ALAT MEDIK*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010102010101' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $SewaAlatMedik = $dtaccess->Fetch($sql);
                            /*SEWA ALAT MEDIK*/
                            /*PMI*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020101020109' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $PMI = $dtaccess->Fetch($sql);
                            /*PMI*/
                            /*AMBULANCE*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '02010102010105' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $Ambulance = $dtaccess->Fetch($sql);
                            /*AMBULANCE*/
                            /*ADM*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a 
                              left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya 
                              where   a.fol_nomor_kwitansi = '$no_kwitansi'  and id_prk = '020101020302' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $Adm = $dtaccess->Fetch($sql);
                            /*ADM*/
                            /*JASA RS*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio 
                              where  fol_nomor_kwitansi = '$no_kwitansi' and id_biaya <> '9999999' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $HitunganJasaRS = $dtaccess->Fetch($sql);

                              $JasaRS = 0.1 * $HitunganJasaRS['total'];
                            /*JASA RS*/

                            /*SUM PENERIMAAN*/
                              $Penerimaan = $pembayaranTunai['total'] + $pembayaranBCA['total'] + $pembayaranBRI['total'] + $pembayaranBNI['total'] + $pembayaranBPJS['total'] + $pembayaranPiutang['total'] + $pembayaranAsuransi['total'] + $pembayaranKaryawan['total'] + $pembayaranDiskon['total'] + str_replace('-', '', $pembayaranDeposit['total']);
                            /*SUM PENERIMAAN*/

                            /*SUM PENDAPATAN*/
                              $Pendapatan = $JasaDokter['total'] + $JasaPerawat['total'] + $PendapatanIGD['total'] + $PendapatanIRJ['total'] + $Imunisasi['total'] + $TindakanVK['total'] + $RuangMedik['total'] + $Gizi['total'] + $RuangInap['total'] + $RuangOperasi['total'] + $RuangNonMedik['total'] + $RadUmum['total'] + $RadJKN['total'] + $RadAsuransi['total'] + $LabUmum['total'] + $LabJKN['total'] + $LabAsuransi['total'] + $HargaObatRSIA + $TuslagRSIA['total'] + $HargaObatGraha + $TuslagGraha['total'] + $AlkesRuangan['total'] + $PendLainnyaNonMedik['total'] + $GasMedik['total'] + $SewaAlatMedik['total'] + $PMI['total'] + $Ambulance['total'] + $Adm['total'] + $JasaRS + str_replace('-', '', $pembayaranDepositRetur['total']);
                            /*SUM PENDAPATAN*/
                        ?>
                          <tr>
                            <td align="left"><?php echo $dataTable[$i]['pembayaran_create'] ?></td>
                            <td align="left"><?php echo $dataTable[$i]['reg_kode_trans'] ?></td>
                            <td align="left"><?php echo $dataTable[$i]['cust_usr_kode'] ?></td>
                            <td align="left"><?php if ($dataTable[$i]['cust_usr_kode'] == '100') {
                              echo $dataTable[$i]['reg_keterangan'];
                            } else{
                              echo $dataTable[$i]['cust_usr_nama'];
                            } ?></td> 
                          <!-- PEMBAYARAN -->
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranTunai['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= str_replace('-', '', number_format($pembayaranDeposit['total'],0,',','.')) ?></td> 
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranBCA['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranBRI['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranBNI['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranBPJS['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranPiutang['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranAsuransi['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranDiskon['total'],0,',','.') ?></td>
                              <td align="right" style="background-color:#98AFC7"><?= number_format($pembayaranKaryawan['total'],0,',','.') ?></td>

                              <td align="right"><?= str_replace('-', '', number_format($pembayaranDepositRetur['total'],0,',','.')) ?></td>
                              
                              <!-- <td align="right" style="background-color:#98AFC7"><?= $no_kwitansi ?></td>  -->
                            <!-- PEMBAYARAN -->
                            <!-- TINDAKAN POLI -->
                              <!-- UMUM -->
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($JasaDokter['total'],0,',','.'); 
                                      $JasaDokterUmum += $JasaDokter['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($JasaPerawat['total'],0,',','.'); 
                                      $JasaPerawatUmum += $JasaPerawat['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($PendapatanIGD['total'],0,',','.'); 
                                      $PendapatanIGDUmum += $PendapatanIGD['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($PendapatanIRJ['total'],0,',','.'); 
                                      $PendapatanIRJUmum += $PendapatanIRJ['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($Imunisasi['total'],0,',','.'); 
                                      $ImunisasiUmum += $Imunisasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($TindakanVK['total'],0,',','.'); 
                                      $TindakanVKUmum += $TindakanVK['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($RuangMedik['total'],0,',','.'); 
                                      $RuangMedikUmum += $RuangMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($Gizi['total'],0,',','.'); 
                                      $GiziUmum += $Gizi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($RuangInap['total'],0,',','.'); 
                                      $RuangInapUmum += $RuangInap['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($RuangOperasi['total'],0,',','.'); 
                                      $RuangOperasiUmum += $RuangOperasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($RuangNonMedik['total'],0,',','.'); 
                                      $RuangNonMedikUmum += $RuangNonMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                              <!-- UMUM -->
                              <!-- BPJS -->
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($JasaDokter['total'],0,',','.'); 
                                      $JasaDokterJKN += $JasaDokter['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($JasaPerawat['total'],0,',','.'); 
                                      $JasaPerawatJKN += $JasaPerawat['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($PendapatanIGD['total'],0,',','.'); 
                                      $PendapatanIGDJKN += $PendapatanIGD['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($PendapatanIRJ['total'],0,',','.'); 
                                      $PendapatanIRJJKN += $PendapatanIRJ['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($Imunisasi['total'],0,',','.'); 
                                      $ImunisasiJKN += $Imunisasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($TindakanVK['total'],0,',','.'); 
                                      $TindakanVKJKN += $TindakanVK['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($RuangMedik['total'],0,',','.'); 
                                      $RuangMedikJKN += $RuangMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($Gizi['total'],0,',','.'); 
                                      $GiziJKN += $Gizi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($RuangInap['total'],0,',','.'); 
                                      $RuangInapJKN += $RuangInap['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($RuangOperasi['total'],0,',','.'); 
                                      $RuangOperasiJKN += $RuangOperasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($RuangNonMedik['total'],0,',','.'); 
                                      $RuangNonMedikJKN += $RuangNonMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                              <!-- BPJS -->
                              <!-- ASSURANSI -->
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($JasaDokter['total'],0,',','.'); 
                                      $JasaDokterAsuransi += $JasaDokter['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($JasaPerawat['total'],0,',','.'); 
                                      $JasaPerawatAsuransi += $JasaPerawat['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($PendapatanIGD['total'],0,',','.'); 
                                      $PendapatanIGDAsuransi += $PendapatanIGD['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($PendapatanIRJ['total'],0,',','.'); 
                                      $PendapatanIRJAsuransi += $PendapatanIRJ['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($Imunisasi['total'],0,',','.'); 
                                      $ImunisasiAsuransi += $Imunisasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($TindakanVK['total'],0,',','.'); 
                                      $TindakanVKAsuransi += $TindakanVK['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($RuangMedik['total'],0,',','.'); 
                                      $RuangMedikAsuransi += $RuangMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($Gizi['total'],0,',','.'); 
                                      $GiziAsuransi += $Gizi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($RuangInap['total'],0,',','.'); 
                                      $RuangInapAsuransi += $RuangInap['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($RuangOperasi['total'],0,',','.'); 
                                      $RuangOperasiAsuransi += $RuangOperasi['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                                 <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($RuangNonMedik['total'],0,',','.'); 
                                      $RuangNonMedikAsuransi += $RuangNonMedik['total'];
                                    } else { echo '0'; } ?>
                                 </td> 
                              <!-- ASSURANSI -->
                            <!-- TINDAKAN POLI -->
                            <!-- Rad -->
                              <td align="right"><?= number_format($RadUmum['total'],0,',','.') ?></td>
                              <td align="right"><?= number_format($RadJKN['total'],0,',','.') ?></td>
                              <td align="right"><?= number_format($RadAsuransi['total'],0,',','.') ?></td> 
                            <!-- Rad -->
                            <!-- LAB -->
                              <td align="right"><?= number_format($LabUmum['total'],0,',','.') ?></td>
                              <td align="right"><?= number_format($LabJKN['total'],0,',','.') ?></td>
                              <td align="right"><?= number_format($LabAsuransi['total'],0,',','.') ?></td> 
                            <!-- LAB -->
                            <!-- Fisio -->
                              <td align="right">0</td>
                              <td align="right">0</td>
                              <td align="right">0</td> 
                            <!-- Fisio -->
                            <!-- FARMASI RSIA -->
                              <!-- Alkes RSIA -->
                                <td align="right">0</td>
                                <td align="right">0</td>
                                <td align="right">0</td> 
                              <!-- Alkes RSIA -->
                              <!-- Obat RSIA -->
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($HargaObatRSIA,0,',','.'); 
                                      $TotalObatRSIAUmum += $HargaObatRSIA;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($HargaObatRSIA,0,',','.'); 
                                      $TotalObatRSIAJKN += $HargaObatRSIA;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($HargaObatRSIA,0,',','.'); 
                                      $TotalObatRSIAAsuransi += $HargaObatRSIA;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                              <!-- Obat RSIA -->
                              <!-- Tuslag RSIA -->
                                <td align="right"><?= number_format($TuslagRSIA['total'],0,',','.') ?></td> 
                              <!-- Tuslag RSIA -->
                            <!-- FARMASI RSIA -->
                            <!-- FARMASI Graha -->
                              <!-- Alkes Graha -->
                                <td align="right">0</td>
                                <td align="right">0</td>
                                <td align="right">0</td> 
                              <!-- Alkes Graha -->
                              <!-- Obat Graha -->
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '2') { 
                                      echo number_format($HargaObatGraha,0,',','.'); 
                                      $TotalObatGrahaUmum += $HargaObatGraha;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '5') { 
                                      echo number_format($HargaObatGraha,0,',','.'); 
                                      $TotalObatGrahaJKN += $HargaObatGraha;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                                <td align="right">
                                  <?php 
                                    if($dataTable[$i]['reg_jenis_pasien'] == '7') { 
                                      echo number_format($HargaObatGraha,0,',','.'); 
                                      $TotalObatGrahaAsuransi += $HargaObatGraha;
                                    } else { 
                                      echo '0';
                                    } 
                                  ?>
                                </td>
                              <!-- Obat Graha -->
                              <!-- Tuslag Graha -->
                                <td align="right"><?= number_format($TuslagGraha['total'],0,',','.') ?></td> 
                              <!-- Tuslag Graha -->
                            <!-- FARMASI Graha -->
                            <!-- ALKES RUANGAN -->
                                <td align="right"><?= number_format($AlkesRuangan['total'],0,',','.') ?></td> 
                            <!-- ALKES RUANGAN -->
                            <!-- PEND NON MEDIK -->
                                <td align="right"><?= number_format($PendLainnyaNonMedik['total'],0,',','.') ?></td> 
                            <!-- PEND NON MEDIK -->
                            <!-- GAS MEDIK -->
                                <td align="right"><?= number_format($GasMedik['total'],0,',','.') ?></td> 
                            <!-- GAS MEDIK -->
                            <!-- SEWA ALAT MEDIK -->
                                <td align="right"><?= number_format($SewaAlatMedik['total'],0,',','.') ?></td> 
                            <!-- SEWA ALAT MEDIK -->
                            <!-- PMI -->
                                <td align="right"><?= number_format($PMI['total'],0,',','.') ?></td> 
                            <!-- PMI -->
                            <!-- AMBULANCE -->
                                <td align="right"><?= number_format($Ambulance['total'],0,',','.') ?></td> 
                            <!-- AMBULANCE -->
                            <!-- Administrasi -->
                                <td align="right"><?= number_format($Adm['total'],0,',','.') ?></td> 
                            <!-- Administrasi -->
                            <!-- JASA RS -->
                                <td align="right"><?= number_format($JasaRS,0,',','.') ?></td> 
                            <!-- JASA RS -->
                            <!-- JUMLAH -->
                                <td align="right"><?= number_format($Pendapatan,0,',','.') ?></td> 
                            <!-- JUMLAH -->
                            <!-- JUMLAH -->
                                <td align="right"><?= number_format($Penerimaan - $Pendapatan,0,',','.') ?></td> 
                            <!-- JUMLAH -->
                          </tr>
                        <?php 
                          /*PENERIMAAN*/
                            $TotalTunai += $pembayaranTunai['total'];
                            $TotalBCA += $pembayaranBCA['total'];
                            $TotalBRI += $pembayaranBRI['total'];
                            $TotalBNI += $pembayaranBNI['total'];
                            $TotalBPJS += $pembayaranBPJS['total'];
                            $TotalPiutang += $pembayaranPiutang['total'];
                            $TotalAsuransi += $pembayaranAsuransi['total'];
                            $TotalDiskon += $pembayaranDiskon['total'];
                            $TotalKaryawan += $pembayaranKaryawan['total'];
                            $TotalDeposit += str_replace('-', '', $pembayaranDeposit['total']);
                            $TotalDepositRetur += str_replace('-', '', $pembayaranDepositRetur['total']);
                          /*PENERIMAAN*/
                          /*PENDAPATAN*/
                            /*RAD*/
                              $TotalRadUmum += $RadUmum['total'];
                              $TotalRadJKN += $RadJKN['total'];
                              $TotalRadAsuransi += $RadAsuransi['total'];
                            /*RAD*/
                            /*LAB*/
                              $TotalLabUmum += $LabUmum['total'];
                              $TotalLabJKN += $LabJKN['total'];
                              $TotalLabAsuransi += $LabAsuransi['total'];
                            /*LAB*/
                            /*FARMASI*/
                              $TotalTuslagRSIA += $TuslagRSIA['total'];
                              $TotalTuslagGraha += $TuslagGraha['total'];
                            /*FARMASI*/
                            $TotalAlkesRuangan += $AlkesRuangan['total'];
                            $TotalPendLainnyaNonMedik += $PendLainnyaNonMedik['total'];
                            $TotalGasMedik += $GasMedik['total'];
                            $TotalSewaAlatMedik += $SewaAlatMedik['total'];
                            $TotalPMI += $PMI['total'];
                            $TotalAmbulance += $Ambulance['total'];
                            $TotalAdm += $Adm['total'];
                            $TotalJasaRS += $JasaRS;
                            $TotalPendapatan += $Pendapatan;
                          /*PENDAPATAN*/
                          $Selisih = $Penerimaan - $Pendapatan;
                          $TotalSelisih += $Selisih;
                          } 
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="4">TOTAL (<?php echo count($dataTable) ?>)</td>
                          <!-- PENERIMAAN -->
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalTunai,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalDeposit,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalBCA,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalBRI,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalBNI,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalBPJS,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalPiutang,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalAsuransi,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalDiskon,0,',','.') ?></td>
                            <td align="right" style="background-color:#98AFC7"><?php echo number_format($TotalKaryawan,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalDepositRetur,0,',','.') ?></td>
                            
                          <!-- PENERIMAAN -->
                          <!-- PENDAPATAN -->
                            <!-- JASA LAYANAN UMUM -->
                              <td align="right"><?php echo number_format($JasaDokterUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($JasaPerawatUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIGDUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIRJUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($ImunisasiUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanVKUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangMedikUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($GiziUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangInapUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangOperasiUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangNonMedikUmum,0,',','.') ?></td>
                            <!-- JASA LAYANAN UMUM -->
                            <!-- JASA LAYANAN JKN -->
                              <td align="right"><?php echo number_format($JasaDokterJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($JasaPerawatJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIGDJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIRJJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($ImunisasiJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanVKJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangMedikJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($GiziJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangInapJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangOperasiJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangNonMedikJKN,0,',','.') ?></td>
                            <!-- JASA LAYANAN JKN -->
                            <!-- JASA LAYANAN ASURANSI -->
                              <td align="right"><?php echo number_format($JasaDokterAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($JasaPerawatAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIGDAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanIRJAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($ImunisasiAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TindakanVKAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangMedikAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($GiziAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangInapAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangOperasiAsuransi,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($RuangNonMedikAsuransi,0,',','.') ?></td>
                            <!-- JASA LAYANAN ASURANSI -->
                            <!-- RAD -->
                              <td align="right"><?php echo number_format($TotalRadUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TotalRadJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TotalRadAsuransi,0,',','.') ?></td>
                            <!-- RAD -->
                            <!-- LAB -->
                              <td align="right"><?php echo number_format($TotalLabUmum,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TotalLabJKN,0,',','.') ?></td>
                              <td align="right"><?php echo number_format($TotalLabAsuransi,0,',','.') ?></td>
                            <!-- LAB -->
                            <!-- FISIO -->
                              <td align="right">0</td>
                              <td align="right">0</td>
                              <td align="right">0</td>
                            <!-- FISIO -->
                            <!-- RSIA -->
                              <!-- ALKES RSIA -->
                                <td align="right">0</td>
                                <td align="right">0</td>
                                <td align="right">0</td>
                              <!-- ALKES RSIA -->
                              <!-- OBAT RSIA -->
                                <td align="right"><?php echo number_format($TotalObatRSIAUmum,0,',','.') ?></td>
                                <td align="right"><?php echo number_format($TotalObatRSIAJKN,0,',','.') ?></td>
                                <td align="right"><?php echo number_format($TotalObatRSIAAsuransi,0,',','.') ?></td>
                              <!-- OBAT RSIA -->
                              <td align="right"><?php echo number_format($TotalTuslagRSIA,0,',','.') ?></td>
                            <!-- RSIA -->
                            <!-- Graha -->
                              <!-- ALKES Graha -->
                                <td align="right">0</td>
                                <td align="right">0</td>
                                <td align="right">0</td>
                              <!-- ALKES Graha -->
                              <!-- OBAT Graha -->
                                <td align="right"><?php echo number_format($TotalObatGrahaUmum,0,',','.') ?></td>
                                <td align="right"><?php echo number_format($TotalObatGrahaJKN,0,',','.') ?></td>
                                <td align="right"><?php echo number_format($TotalObatGrahaAsuransi,0,',','.') ?></td>
                              <!-- OBAT Graha -->
                              <td align="right"><?php echo number_format($TotalTuslagGraha,0,',','.') ?></td>
                            <!-- Graha -->
                            <td align="right"><?php echo number_format($TotalAlkesRuangan,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalPendLainnyaNonMedik,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalGasMedik,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalSewaAlatMedik,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalPMI,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalAmbulance,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalAdm,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalJasaRS,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalPendapatan,0,',','.') ?></td>
                            <td align="right"><?php echo number_format($TotalSelisih,0,',','.') ?></td>
                          <!-- PENDAPATAN -->
                        </tr>
                      </tfoot>
</table>