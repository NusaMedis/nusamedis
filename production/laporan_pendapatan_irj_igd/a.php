<?php
  require_once("../penghubung.inc.php");
  require_once($LIB . "login.php");
  require_once($LIB . "encrypt.php");
  require_once($LIB . "datamodel.php");
  require_once($LIB . "currency.php");
  require_once($LIB . "dateLib.php");
  require_once($LIB . "tampilan.php");
  require_once($LIB . "tree.php");
  require_once($LIB . "expAJAX.php");

  //INISIALISAI AWAL LIBRARY
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $err_code = 0;
  $auth = new CAuth();
  $view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
  $usrId = $auth->GetUserId();
  $table = new InoTable("table", "100%", "left");
  $userData = $auth->GetUserData();
  $depId = $auth->GetDepId();
  $depNama = $auth->GetDepNama();
  $userName = $auth->GetUserName();
  $depLowest = $auth->GetDepLowest();
  $userName = $auth->GetUserName();
  $skr = date("d-m-Y");

  if ($_POST['btnShow'] || $_POST["btnExcel"]) {
    if($_POST["tgl_awal"]) $sql_where[] = "a.pembayaran_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
    if($_POST["tgl_akhir"]) $sql_where[] = "a.pembayaran_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));

    $sql_where = implode(" and ",$sql_where);

    $sql = "SELECT b.reg_jenis_pasien, a.pembayaran_id, a.pembayaran_create, a.pembayaran_diskon, a.pembayaran_total, b.reg_kode_trans, b.reg_keterangan, c.cust_usr_kode, c.cust_usr_nama FROM klinik.klinik_pembayaran a LEFT JOIN klinik.klinik_registrasi b ON a.id_reg = b.reg_id LEFT JOIN global.global_customer_user c ON b.id_cust_usr = c.cust_usr_id WHERE a.pembayaran_id IN (select id_pembayaran from klinik.klinik_pembayaran_det) AND b.reg_tipe_rawat != 'I' AND ".$sql_where;
    $sql .= "ORDER BY a.pembayaran_create ASC ";
    $dataTable = $dtaccess->FetchAll($sql);
  }
  if ($_POST["btnExcel"]) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=Laporan-Penerimaan-IRJ&IGD.xls');
    echo "<h2>Laporan Penerimaan IRJ & IGD</h2><br>";
  }

  if ($_POST["btnCetak"]) {
    //echo $_POST["ush_id"];
    //die();
    $_x_mode = "cetak";
}

  $tableHeader = 'Laporan Pendapatan IRJ - IGD';
?>
<?php if (!$_POST["btnExcel"]) { ?>
<!DOCTYPE html>
<html>
  <?php require_once($LAY . "header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY . "sidebar.php") ?>
        <?php require_once($LAY . "topnav.php") ?>
        <div class="right_col" role="main">
          <div class="">
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?= $tableHeader ?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" method="POST" action="">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="tgl_awal" type='text' class="form-control" value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
                          <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                          <div class='input-group date' id='datepicker2'>
                            <input  name="tgl_akhir"  type='text' class="form-control" 
                            value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
                            <span class="input-group-addon">
                              <span class="fa fa-calendar">
                              </span>
                            </span>
                          </div>
                      </div>
                      <div class="form-group">
                        <div class="col-sm-12 col-md-12 col-xs-12">
                          <input type="submit" id="btnShow" name="btnShow" class="pull-right btn btn-primary" value="Lihat">
                          <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                          <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
                        </div>
                      </div>
                      <? if ($_x_mode == "Edit") { ?>
                        <?php echo $view->RenderHidden("kategori_tindakan_id", "kategori_tindakan_id", $biayaId); ?>
                      <? } ?>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <? } ?>
            <?php if ($_POST["btnShow"] || $_POST['btnExcel']) { ?>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_content">
                    <table width="100%" id="example" class="table table-striped table-bordered dt-responsive nowrap" border="1">
                      <thead>
                        <tr>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">TANGGAL</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">NO. REG</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">MEDREC</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">NAMA</td>
                          <td colspan="10" style="text-align: center; vertical-align: middle;">PEMBAYARAN</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle;">PENDAPATAN JASA LAYANAN MEDIK PASIEN UMUM</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle;">PENDAPATAN JASA LAYANAN MEDIK PASIEN BPJS</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle;">PENDAPATAN JASA LAYANAN MEDIK PASIEN KONTRAKTOR</td>
                          <td colspan="9" style="text-align: center; vertical-align: middle;">PENUNJANG</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle;">FARMASI RSIA</td>
                          <td colspan="7" style="text-align: center; vertical-align: middle;">FARMASI GRAHA</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">PENJUALAN ALKES</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">GAS MEDIK</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">AMBULAN</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">ADM</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">JUMLAH</td>
                          <td rowspan="3" style="text-align: center; vertical-align: middle;">PEMBULATAN</td>
                        </tr>
                        <tr>
                          <!-- PEMBAYARAN -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TUNAI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">BCA</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">BRI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">BNI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">BPJS</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">PIUTANG UMUM</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">PIUTAN  ASS+KONT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">DISKON</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">BEBAN KARYAWAN</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">PEMB. DEPOSIT</td>
                          <!-- PEMBAYARAN -->
                          <!-- PASIEN UMUM -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">RUANG MEDIK</td>
                          <!-- PASIEN UMUM -->
                          <!-- PASIEN BPJS -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">RUANG MEDIK</td>
                          <!-- PASIEN BPJS -->
                          <!-- PASIEN ASURANSI -->
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA DOKTER</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">JASA PERAWAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN IGD</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN RAJAL</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">IMUNISASI</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TINDAKAN VK</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">RUANG MEDIK</td>
                          <!-- PASIEN ASURANSI -->
                          <!-- PENUNJANG -->
                            <td colspan="3" style="text-align: center; vertical-align: middle;">RADIOLOGI</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle;">LAB</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle;">FISIOTERAFI</td>
                          <!-- PENUNJANG -->
                          <!-- FARMASI RSIA -->
                            <td colspan="3" style="text-align: center; vertical-align: middle;">ALKES</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle;">OBAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TUSLAH</td>
                          <!-- FARMASI RSIA -->
                          <!-- FARMASI GRAHA -->
                            <td colspan="3" style="text-align: center; vertical-align: middle;">ALKES</td>
                            <td colspan="3" style="text-align: center; vertical-align: middle;">OBAT</td>
                            <td rowspan="2" style="text-align: center; vertical-align: middle;">TUSLAH</td>
                          <!-- FARMASI GRAHA -->
                        </tr>
                        <tr>
                          <!-- PENUNJANG -->
                            <!-- Radiologi -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Radiologi -->
                            <!-- Lab -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Lab -->
                            <!-- Fisioterafi -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Fisioterafi -->
                          <!-- PENUNJANG -->
                          <!-- FARMASI RSIA -->
                            <!-- Alkes -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Alkes -->
                            <!-- Obat -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Obat -->
                          <!-- FARMASI RSIA -->
                          <!-- FARMASI GRAHA -->
                            <!-- Alkes -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Alkes -->
                            <!-- Obat -->
                              <td style="text-align: center; vertical-align: middle;">UMUM</td>
                              <td style="text-align: center; vertical-align: middle;">BPJS</td>
                              <td style="text-align: center; vertical-align: middle;">KONTRAKTOR</td>
                            <!-- Obat -->
                          <!-- FARMASI GRAHA -->
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                          for ($i = 0; $i < count($dataTable); $i++) { 
                            /* PEMBAYARAN */
                              /* Tunai */
                                $sql = "SELECT SUM(pembayaran_det_dibayar) AS total FROM klinik.klinik_pembayaran_det WHERE id_jbayar = '01' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
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
                                $sql = "SELECT deposit_history_nominal as total FROM klinik.klinik_deposit_history where deposit_history_flag = 'P' AND id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pembayaran_id']);
                                $pembayaranDeposit = $dtaccess->Fetch($sql);
                              /* Retur */
                            /* PEMBAYARAN */
                            /* PENDAPATAN JASA LAYANAN MEDIK */
                              /* JASA DOKTER */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020106' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $JasaDokter = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020107' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $JasaPerawat = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020112' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $PendapatanIGD = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020111' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $PendapatanIRJ = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020116' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $Imunisasi = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '020113' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $TindakanVK = $dtaccess->Fetch($sql);

                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where id_prk = '02010102010103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RuangMedik = $dtaccess->Fetch($sql);
                              /* JASA DOKTER */
                            /* PENDAPATAN JASA LAYANAN MEDIK */
                            /* RADIOLOGI */
                              /* UMUM */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '2' and id_prk = '020101020103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadUmum = $dtaccess->Fetch($sql);
                              /* UMUM */
                              /* JKN */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '5' and id_prk = '020101020103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadJKN = $dtaccess->Fetch($sql);
                              /* JKN */
                              /* Asuransi */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '7' and id_prk = '020101020103' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $RadAsuransi = $dtaccess->Fetch($sql);
                              /* Asuransi */
                            /* RADIOLOGI */
                            /* LABORATORIUM */
                              /* UMUM */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '2' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabUmum = $dtaccess->Fetch($sql);
                              /* UMUM */
                              /* JKN */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '5' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabJKN = $dtaccess->Fetch($sql);
                              /* JKN */
                              /* Asuransi */
                                $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_registrasi b on b.reg_id = a.id_reg left join klinik.klinik_biaya c on c.biaya_id = a.id_biaya where reg_jenis_pasien = '7' and id_prk = '0201010210' and a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                                $LabAsuransi = $dtaccess->Fetch($sql);
                              /* Asuransi */
                            /* LABORATORIUM */
                            /*FARMASI*/
                              /*GRAHA*/
                                $sql = "select sum(penjualan_grandtotal) as total from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id'])." and id_gudang = '2'";
                                $ObatGraha = $dtaccess->Fetch($sql);
                                $sql = "select sum(penjualan_tuslag) as total from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id'])." and id_gudang = '2'";
                                $TuslagGraha = $dtaccess->Fetch($sql);
                                $HargaObatGraha = $ObatGraha['total'] - $TuslagGraha['total'];
                              /*GRAHA*/
                              /*RSIA*/
                                $sql = "select sum(penjualan_grandtotal) as total from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id'])." and id_gudang = '3'";
                                $ObatRSIA = $dtaccess->Fetch($sql);
                                $sql = "select sum(penjualan_tuslag) as total from apotik.apotik_penjualan where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id'])." and id_gudang = '3'";
                                $TuslagRSIA = $dtaccess->Fetch($sql);
                                $HargaObatRSIA = $ObatRSIA['total'] - $TuslagRSIA['total'];
                              /*RSIA*/
                            /*FARMASI*/
                            /*ALKES RUANGAN*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya where id_prk = '02010101010110' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $AlkesRuangan = $dtaccess->Fetch($sql);
                            /*ALKES RUANGAN*/
                            /*GAS MEDIK*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya where id_prk = '020101020107' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $GasMedik = $dtaccess->Fetch($sql);
                            /*GAS MEDIK*/
                            /*AMBULANCE*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya where id_prk = '02010102010105' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $Ambulance = $dtaccess->Fetch($sql);
                            /*AMBULANCE*/
                            /*ADM*/
                              $sql = "select sum(fol_nominal) as total from klinik.klinik_folio a left join klinik.klinik_biaya b on b.biaya_id = a.id_biaya where id_prk = '020101020302' and id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
                              $Adm = $dtaccess->Fetch($sql);
                            /*ADM*/

                            /*SUM PENERIMAAN*/
                              $Penerimaan = $pembayaranTunai['total'] + $pembayaranBCA['total'] + $pembayaranBRI['total'] + $pembayaranBNI['total'] + $pembayaranBPJS['total'] + $pembayaranPiutang['total'] + $pembayaranAsuransi['total'] + $pembayaranKaryawan['total'] + $pembayaranDiskon['total'] + $pembayaranDeposit['total'];
                            /*SUM PENERIMAAN*/
                            /*SUM PENDAPATAN*/
                              $Pendapatan = $JasaDokter['total'] + $JasaPerawat['total'] + $PendapatanIGD['total'] + $PendapatanIRJ['total'] + $Imunisasi['total'] + $TindakanVK['total'] + $RuangMedik['total'] + $RadUmum['total'] + $RadJKN['total'] + $RadAsuransi['total'] + $LabUmum['total'] + $LabJKN['total'] + $LabAsuransi['total'] + $HargaObatRSIA + $TuslagRSIA['total'] + $HargaObatGraha + $TuslagGraha['total'] + $AlkesRuangan['total'] + $GasMedik['total'] + $Ambulance['total'] + $Adm['total'];
                            /*SUM PENDAPATAN*/
                        ?>
                          <tr>
                            <td><?php echo $dataTable[$i]['pembayaran_create'] ?></td>
                            <td><?php echo $dataTable[$i]['reg_kode_trans'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_kode'] ?></td>
                            <td><?php if ($dataTable[$i]['cust_usr_kode'] == '100') {
                              echo $dataTable[$i]['reg_keterangan'];
                            } else{
                              echo $dataTable[$i]['cust_usr_nama'];
                            } ?></td> 
                          <!-- PEMBAYARAN -->
                              <td align="right"><?= number_format($pembayaranTunai['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranBCA['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranBRI['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranBNI['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranBPJS['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranPiutang['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranAsuransi['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranDiskon['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranKaryawan['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($pembayaranDeposit['total'],2,',','.') ?></td> 
                            <!-- PEMBAYARAN -->
                            <!-- TINDAKAN POLI -->
                              <!-- UMUM -->
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($JasaDokter['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($JasaPerawat['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($PendapatanIGD['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($PendapatanIRJ['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($Imunisasi['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($TindakanVK['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') { echo number_format($RuangMedik['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                              <!-- UMUM -->
                              <!-- BPJS -->
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($JasaDokter['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($JasaPerawat['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($PendapatanIGD['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($PendapatanIRJ['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($Imunisasi['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($TindakanVK['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') { echo number_format($RuangMedik['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                              <!-- BPJS -->
                              <!-- ASSURANSI -->
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($JasaDokter['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($JasaPerawat['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($PendapatanIGD['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($PendapatanIRJ['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($Imunisasi['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($TindakanVK['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                                 <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') { echo number_format($RuangMedik['total'],2,',','.'); } else { echo '0.00'; } ?></td> 
                              <!-- ASSURANSI -->
                            <!-- TINDAKAN POLI -->
                            <!-- Rad -->
                              <td align="right"><?= number_format($RadUmum['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($RadJKN['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($RadAsuransi['total'],2,',','.') ?></td> 
                            <!-- Rad -->
                            <!-- LAB -->
                              <td align="right"><?= number_format($LabUmum['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($LabJKN['total'],2,',','.') ?></td>
                              <td align="right"><?= number_format($LabAsuransi['total'],2,',','.') ?></td> 
                            <!-- LAB -->
                            <!-- Fisio -->
                              <td align="right">0.00</td>
                              <td align="right">0.00</td>
                              <td align="right">0.00</td> 
                            <!-- Fisio -->
                            <!-- FARMASI RSIA -->
                              <!-- Alkes RSIA -->
                                <td align="right">0.00</td>
                                <td align="right">0.00</td>
                                <td align="right">0.00</td> 
                              <!-- Alkes RSIA -->
                              <!-- Obat RSIA -->
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') echo number_format($HargaObatRSIA,2,',','.');  else echo '0.00'; ?></td>
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') echo number_format($HargaObatRSIA,2,',','.');  else echo '0.00'; ?></td>
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') echo number_format($HargaObatRSIA,2,',','.');  else echo '0.00'; ?></td>
                              <!-- Obat RSIA -->
                              <!-- Tuslag RSIA -->
                                <td align="right"><?= number_format($TuslagRSIA['total'],2,',','.') ?></td> 
                              <!-- Tuslag RSIA -->
                            <!-- FARMASI RSIA -->
                            <!-- FARMASI Graha -->
                              <!-- Alkes Graha -->
                                <td align="right">0.00</td>
                                <td align="right">0.00</td>
                                <td align="right">0.00</td> 
                              <!-- Alkes Graha -->
                              <!-- Obat Graha -->
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '2') echo number_format($HargaObatGraha,2,',','.');  else echo '0.00'; ?></td>
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '5') echo number_format($HargaObatGraha,2,',','.');  else echo '0.00'; ?></td>
                                <td align="right"><?php if($dataTable[$i]['reg_jenis_pasien'] == '7') echo number_format($HargaObatGraha,2,',','.');  else echo '0.00'; ?></td> 
                              <!-- Obat Graha -->
                              <!-- Tuslag Graha -->
                                <td align="right"><?= number_format($TuslagGraha['total'],2,',','.') ?></td> 
                              <!-- Tuslag Graha -->
                            <!-- FARMASI Graha -->
                            <!-- ALKES RUANGAN -->
                                <td align="right"><?= number_format($AlkesRuangan['total'],2,',','.') ?></td> 
                            <!-- ALKES RUANGAN -->
                            <!-- GAS MEDIK -->
                                <td align="right"><?= number_format($GasMedik['total'],2,',','.') ?></td> 
                            <!-- GAS MEDIK -->
                            <!-- AMBULANCE -->
                                <td align="right"><?= number_format($Ambulance['total'],2,',','.') ?></td> 
                            <!-- AMBULANCE -->
                            <!-- Administrasi -->
                                <td align="right"><?= number_format($Adm['total'],2,',','.') ?></td> 
                            <!-- Administrasi -->
                            <!-- JUMLAH -->
                                <td align="right"><?= number_format($Pendapatan,2,',','.') ?></td> 
                            <!-- JUMLAH -->
                            <!-- JUMLAH -->
                                <td align="right"><?= number_format($Penerimaan - $Pendapatan,2,',','.') ?></td> 
                            <!-- JUMLAH -->
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <?php } ?>
            <?php if (!$_POST["btnExcel"]) { ?>
          </div>
        </div>
        <?php require_once($LAY . "footer.php"); ?>
      </div>
    </div>
  </body>
  <?php require_once($LAY . "js.php"); ?>
</html>
<? } ?>

<script type="text/javascript" charset="utf-8" async defer>
  $(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
  } );
  <?php if ($_x_mode == "cetak") { ?>
    window.open('report_setoran_cicilan_cetak.php?&tgl_awal=<?php echo $_POST["tgl_awal"]; ?>&tgl_akhir=<?php echo $_POST["tgl_akhir"]; ?>', '_blank');
  <?php } ?>
</script>