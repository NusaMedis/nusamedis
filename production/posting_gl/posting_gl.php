<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "tree.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

//INISIALISAI AWAL LIBRARY
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$err_code = 0;
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$skr = date("Y-m-d");
$usrId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$table = new InoTable("table", "100%", "left");
$depId = $auth->GetDepId();
$userData = $auth->GetUserData();
$depLowest = $auth->GetDepLowest();
$userName = $auth->GetUserName();


if ($_GET["loket"]) {
  $_POST["loket"] = $_GET["loket"];
} else if ($_POST["loket"]) {
  $_POST["loket"] = $_POST["loket"];
} else {
  $_POST["loket"] = $depId;
}

$findPage = "departemen_find2.php?";
$viewPage = "posting_gl.php";
$checkPage = "posting_gl.php?tgl_awal=" . $_GET['tgl_awal'] . '&tgl_akhir=' . $_GET['tgl_akhir'] . '&flag_jurnal=' . $_GET['flag_jurnal'] . '&checkall=y';
$uncheckPage = "posting_gl.php?tgl_awal=" . $_GET['tgl_awal'] . '&tgl_akhir=' . $_GET['tgl_akhir'] . '&flag_jurnal=' . $_GET['flag_jurnal'] . '&checkall=n';

if ($_x_mode == "New") $privMode = PRIV_CREATE;
elseif ($_x_mode == "Edit") $privMode = PRIV_UPDATE;
elseif ($_x_mode == "Delete") $privMode = PRIV_DELETE;
else $privMode = PRIV_READ;

if (!$_GET["tgl_awal"]) $_GET["tgl_awal"] = date("d-m-Y");
if (!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = date("d-m-Y");
if ($_GET["tgl_awal"]) $sql_where[] = "tanggal_tra >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
if ($_GET["tgl_akhir"]) $sql_where[] = "tanggal_tra <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));
if ($_GET["loket"] && $_GET["loket"] != "--") $sql_where[] .= " a.dept_id = " . QuoteValue(DPE_CHAR, $_GET["loket"]);
if ($_GET["flag_jurnal"] != "-") $sql_where[] .= " a.flag_jurnal = " . QuoteValue(DPE_CHAR, $_GET["flag_jurnal"]);

$sql = "select a.id_tra,a.ref_tra,a.tanggal_tra,a.ket_tra,a.namauser,a.real_time
              from  gl.gl_buffer_transaksi a where a.is_posting='n' and a.id_tra<>'1' 
              and ref_tra not like 'RE%' and id_tra in(select tra_id from gl.gl_buffer_transaksidetil)";
$sql .= " and " . implode(" and ", $sql_where);
$sql .= " order by a.tanggal_tra asc, a.ref_tra_urut asc";
$rs_edit = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs_edit);




if ($_POST["btpost"]) {
  $cb = &$_POST["cbPost"];
  for ($i = 0, $n = count($cb); $i < $n; $i++) {


    $sql = "select * from  gl.gl_buffer_transaksi
              where id_tra = " . QuoteValue(DPE_CHAR, $cb[$i]);
    $rs_edit = $dtaccess->Execute($sql);
    $dataTrans = $dtaccess->FetchAll($rs_edit);

    for ($k = 0, $batas = count($dataTrans); $k < $batas; $k++) {
      $Q = explode('-', $dataTrans[$i]['ref_tra']);
      $Reff = $Q[0];
      $Lenght = strlen($dataTrans[$i]['ref_tra_urut']);
      // echo $Lenght;die();

      $Explode = explode('-', $dataTrans[$k]['tanggal_tra']);
      $Tahun = $Explode[0];

      $sql = "select * from gl.gl_periode_saldo_awal where nama_prd = ".QuoteValue(DPE_CHAR, $Tahun);
      $PeriodeSaldoAwal = $dtaccess->Fetch($sql);

      if ($Lenght == 1) $NoReff = '0000'.$dataTrans[$i]['ref_tra_urut'];
      if ($Lenght == 2) $NoReff = '000'.$dataTrans[$i]['ref_tra_urut'];
      if ($Lenght == 3) $NoReff = '00'.$dataTrans[$i]['ref_tra_urut'];
      if ($Lenght == 4) $NoReff = '0'.$dataTrans[$i]['ref_tra_urut'];
      if ($Lenght == 5) $NoReff = $dataTrans[$i]['ref_tra_urut'];

      $Edited = $Reff.$NoReff;

      $dbTable = " gl.gl_transaksi";
      $dbField[0]  = "id_tra";   // PK
      $dbField[1]  = "ref_tra";
      $dbField[2]  = "tanggal_tra";
      $dbField[3]  = "ket_tra";
      $dbField[4]  = "namauser";
      $dbField[5]  = "real_time";
      $dbField[6]  = "dept_id";
      $dbField[7]  = "terima_dari";
      $dbField[8]  = "prk_id";
      $dbField[9]  = "ref_tra_urut";
      $dbField[10]  = "id_dep";
      $dbField[11]  = "flag_jurnal";
      $dbField[12]  = "who_posting";
      $dbField[13]  = "ref_tra_urutan";

      $dbValue[0] = QuoteValue(DPE_CHAR, $dataTrans[0]["id_tra"]);
      $dbValue[1] = QuoteValue(DPE_CHAR, $dataTrans[0]["ref_tra"]);
      $dbValue[2] = QuoteValue(DPE_DATE, $dataTrans[0]["tanggal_tra"]); //$_POST["tanggal_tra"]);
      $dbValue[3] = QuoteValue(DPE_CHAR, $dataTrans[0]["ket_tra"]);
      $dbValue[4] = QuoteValue(DPE_CHAR, $dataTrans[0]["namauser"]);
      $dbValue[5] = QuoteValue(DPE_DATE, $dataTrans[0]["real_time"]);
      $dbValue[6] = QuoteValue(DPE_CHAR, $dataTrans[0]["dept_id"]);
      $dbValue[7] = QuoteValue(DPE_CHAR, $dataTrans[0]["terima_dari"]);
      $dbValue[8] = QuoteValue(DPE_CHAR, $dataTrans[0]["prk_id"]);
      $dbValue[9] = QuoteValue(DPE_NUMERIC, $dataTrans[0]["ref_tra_urut"]);
      $dbValue[10] = QuoteValue(DPE_CHAR, $dataTrans[0]["dept_id"]);
      $dbValue[11] = QuoteValue(DPE_CHAR, $dataTrans[0]["flag_jurnal"]);
      $dbValue[12] = QuoteValue(DPE_CHAR, $userName);
      $dbValue[13] = QuoteValue(DPE_CHAR, $Edited);

      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA);
      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dbValue);
    }

    $sql = "select * from  gl.gl_buffer_transaksidetil where tra_id = " . QuoteValue(DPE_CHAR, $cb[$i]);
    $rs = $dtaccess->Execute($sql);
    $dataTransaksiDetil = $dtaccess->FetchAll($rs);

    for ($j = 0, $m = count($dataTransaksiDetil); $j < $m; $j++) {

      $dbTable = " gl.gl_transaksidetil";

      $dbField[0]  = "id_trad";   // PK
      $dbField[1]  = "tra_id";
      $dbField[2]  = "prk_id";
      $dbField[3]  = "ket_trad";
      $dbField[4]  = "jumlah_trad";
      $dbField[5]  = "dept_id";
      $dbField[6]  = "job_id";
      $dbField[7]  = "trad_keterangan";
      $dbField[8]  = "id_periode";


      $dbValue[0] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["id_trad"]);
      $dbValue[1] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["tra_id"]);
      $dbValue[2] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["prk_id"]);
      $dbValue[3] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["ket_trad"]);
      $dbValue[4] = QuoteValue(DPE_NUMERIC, StripCurrency($dataTransaksiDetil[$j]["jumlah_trad"]));
      $dbValue[5] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["dept_id"]);
      $dbValue[6] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["job_id"]);
      $dbValue[7] = QuoteValue(DPE_CHAR, $dataTransaksiDetil[$j]["trad_keterangan"]);
      $dbValue[8] = QuoteValue(DPE_CHAR, $PeriodeSaldoAwal['id_prd']);

      // print_r($dbValue);die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);
      $dtmodel->Insert() or die("insert  error");

      unset($dbField);
      unset($dbValue);
      unset($_POST["btnSave"]);
      unset($_POST["job_nama"]);
      unset($_POST["prk_nama"]);
      unset($_POST["jumlah_trad1"]);
      unset($_POST["jumlah_trad2"]);
    }

    //Telah Terposting    
    $sql = "update  gl.gl_buffer_transaksi set is_posting = 'y' where id_tra = " . QuoteValue(DPE_CHAR, $cb[$i]);
    $dtaccess->Execute($sql);
  }
  // kembali ke tampilan view ---
  header("location:" . $viewPage);
  exit();
}

if ($_GET["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=Laporan-Posting-GL.xls');
  echo "<h2>Laporan Posting GL</h2><br>";
}

if ($_POST["loket"]) {
  //Data loket
  if ($depLowest == 'n') {
    $sql = "select * from global.global_departemen order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataloket = $dtaccess->FetchAll($rs);
  } else {
    $sql = "select * from global.global_departemen where dep_id = '" . $_POST["loket"] . "' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataloket = $dtaccess->FetchAll($rs);
  }
} else {
  $sql = "select * from global.global_departemen order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataloket = $dtaccess->FetchAll($rs);
}
if ($_GET["btnCetak"]) {
  $_x_mode = "cetak";
}
$tableHeader = 'Posting GL';

?>
<script language="JavaScript">
  <?php if ($_x_mode == "cetak") { ?>
    window.open('posting_gl_cetak.php?loket=<?php echo $_GET["loket"]; ?>&level=<?php echo $level; ?>&tgl_awal=<?php echo $_GET["tgl_awal"]; ?>&tgl_akhir=<?php echo $_GET["tgl_akhir"]; ?>&flag_jurnal=<?= $_GET['flag_jurnal'] ?>', '_blank');
  <?php } ?>
</script>
<?php require_once($LAY . "header.php") ?>

<body class="nav-md">
  <?php if(!$_GET['btnExcel']) { ?>
  <div class="container body">
    <div class="main_container">
      <?php require_once($LAY . "sidebar.php") ?>

      <!-- top navigation -->
      <?php require_once($LAY . "topnav.php") ?>
      <!-- /top navigation -->

      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          <form name="frmView" method="GET" action="<?php echo $_SERVER["PHP_SELF"] ?>">
            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
              <div class='input-group date' id='datepicker'>
                <input name="tgl_awal" type='text' class="form-control" value="<?php if ($_GET['tgl_awal']) {
                                                                                  echo $_GET['tgl_awal'];
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
                <input name="tgl_akhir" type='text' class="form-control" value="<?php if ($_GET['tgl_akhir']) {
                                                                                  echo $_GET['tgl_akhir'];
                                                                                } else {
                                                                                  echo date('d-m-Y');
                                                                                } ?>" />
                <span class="input-group-addon">
                  <span class="fa fa-calendar">
                  </span>
                </span>
              </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Jurnal</label>
              <select class="form-control" name="flag_jurnal">
                <option value="-" <?php if ($_GET['flag_jurnal'] == '-') echo 'selected'; ?>>- Semua Tipe Jurnal -</option>
                <option value="PEJ" <?php if ($_GET['flag_jurnal'] == 'PEJ') echo 'selected'; ?>>Penerimaan Rawat Jalan</option>
                <option value="PEG" <?php if ($_GET['flag_jurnal'] == 'PEG') echo 'selected'; ?>>Penerimaan IGD</option>
                <option value="PEI" <?php if ($_GET['flag_jurnal'] == 'PEI') echo 'selected'; ?>>Penerimaan Rawat Inap</option>
                <option value="RUM" <?php if ($_GET['flag_jurnal'] == 'RUM') echo 'selected'; ?>>Pembalik Uang Muka</option>
                <option value="PO" <?php if ($_GET['flag_jurnal'] == 'PO') echo 'selected'; ?>>Pengakuan Hutang Supplier</option>
                <option value="PPA" <?php if ($_GET['flag_jurnal'] == 'PPA') echo 'selected'; ?>>Pelunasan Piutang Asuransi</option>
                <option value="PPP" <?php if ($_GET['flag_jurnal'] == 'PPP') echo 'selected'; ?>>Pelunasan Piutang Umum</option>
                <option value="PPKB" <?php if ($_GET['flag_jurnal'] == 'PPKB') echo 'selected'; ?>>Pelunasan Piutang Kurang Bayar</option>
                <option value="PPB" <?php if ($_GET['flag_jurnal'] == 'PPB') echo 'selected'; ?>>Pelunasan Piutang BPJS</option>
                <option value="PR" <?php if ($_GET['flag_jurnal'] == 'PR') echo 'selected'; ?>>Persediaan Penjualan RSIA</option>
                <option value="PG" <?php if ($_GET['flag_jurnal'] == 'PG') echo 'selected'; ?>>Persediaan Penjualan Graha</option>
                <option value="UM" <?php if ($_GET['flag_jurnal'] == 'UM') echo 'selected'; ?>>Uang Muka</option>
                <option value="PS" <?php if ($_GET['flag_jurnal'] == 'PS') echo 'selected'; ?>>Pelunasan Hutang Supplier</option>
                <option value="MO" <?php if ($_GET['flag_jurnal'] == 'MO') echo 'selected'; ?>>Mutasi Obat</option>
                <option value="LL" <?php if ($_GET['flag_jurnal'] == 'LL') echo 'selected'; ?>>Pendapatan Lain-lain</option>
                <option value="PKR" <?php if ($_GET['flag_jurnal'] == 'PKR') echo 'selected'; ?>>Koreksi Stok Penjualan RSIA</option>
                <option value="PKG" <?php if ($_GET['flag_jurnal'] == 'PKG') echo 'selected'; ?>>Koreksi Stok Penjualan Graha</option>
                <option value="KSG" <?php if ($_GET['flag_jurnal'] == 'KSG') echo "selected"; ?>>Koreksi Stok Opname Graha</option>
                <option value="KSR" <?php if ($_GET['flag_jurnal'] == 'KSR') echo "selected"; ?>>Koreksi Stok Opname RSIA</option>
                <option value="KSP" <?php if ($_GET['flag_jurnal'] == 'KSP') echo "selected"; ?>>Koreksi Stok Opname Gudang</option>
                <option value="POBHP" <?php if ($_GET['flag_jurnal'] == 'POBHP') echo "selected"; ?>>Penerimaan Barang Non Medis</option>
                <option value="MB" <?php if ($_GET['flag_jurnal'] == 'MB') echo "selected"; ?>>Mutasi Non Medis</option>
                <option value="MEM" <?php if ($_GET['flag_jurnal'] == 'MEM') echo 'selected'; ?>>Memorial</option>
                <option value="RT" <?php if ($_GET['flag_jurnal'] == 'RT') echo 'selected'; ?>>Retur Faktur</option>
              </select>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div>
                <input type="submit" id="btnShow" name="btnShow" class="btn btn-primary" value="Lihat Jurnal" />
                <input type="submit" id="btnCetak" name="btnCetak" class="btn btn-success" value="Cetak" />
                <input type="submit" id="btnExcel" name="btnExcel" class="btn btn-success" value="Export Excel" />
              </div>
            </div>
          </form>

          <?php } ?>
          
          <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>">
            <table class="table table-striped table-bordered" width="100%" border="1" cellpadding="1" cellspacing="1">

              <tr>
                <td width="1%" class="tablecontent">
                  <center>&nbsp;&nbsp;No&nbsp;&nbsp;</center>
                </td>
                <td width="1%" class="tablecontent">
                  <center>&nbsp;&nbsp;Date&nbsp;&nbsp;</center>
                </td>
                <td width="1%" class="tablecontent">
                  <center>&nbsp;&nbsp;Ref.&nbsp;&nbsp;</center>
                </td>
                <td class="tablecontent">&nbsp;&nbsp;Keterangan&nbsp;-&nbsp;[&nbsp;Akun&nbsp;]&nbsp;&nbsp;</td>
                <td width="20%" class="tablecontent">
                  <center>&nbsp;&nbsp;Debet&nbsp;&nbsp;</center>
                </td>
                <td width="20%" class="tablecontent">
                  <center>&nbsp;&nbsp;Kredit&nbsp;&nbsp;</center>
                </td>
              </tr>

              <?php for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) { ?>
                <tr>
                  <td class="tablecontent-odd">
                    <center>&nbsp;&nbsp;<?php echo ($i + 1); ?>&nbsp;&nbsp;</center>
                  </td>
                  <td class="tablecontent-odd">
                    <center>&nbsp;&nbsp;<?php echo (format_date($dataTable[$i]["tanggal_tra"])); ?>&nbsp;&nbsp;</center>
                  </td>
                  <td class="tablecontent-odd">
                    <center>&nbsp;&nbsp;<?php echo ($dataTable[$i]["ref_tra"]); ?>&nbsp;&nbsp;</center>
                  </td>
                  <td colspan="3" class="tablecontent-odd"><strong>&nbsp;&nbsp;<?php echo ($dataTable[$i]["ket_tra"]); ?>&nbsp;&nbsp;</strong></td>
                </tr>
                <?php
                $sql =  "select c.no_prk,c.nama_prk,prk_id, sum(jumlah_trad) as total
                        from gl.gl_buffer_transaksidetil b
                        left join  gl.gl_perkiraan c on b.prk_id = c.id_prk
                        where b.tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]) . "
                        and jumlah_trad <> 0 and jumlah_trad > 0";
                $sql .= " group by tra_id,no_prk,nama_prk,prk_id order by no_prk asc";
                $rs_edit = $dtaccess->Execute($sql);
                $dataDetilDebet = $dtaccess->FetchAll($rs_edit);

                $sql =  "select c.no_prk,c.nama_prk,prk_id, sum(jumlah_trad) as total
                        from gl.gl_buffer_transaksidetil b
                        left join  gl.gl_perkiraan c on b.prk_id = c.id_prk
                        where b.tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]) . "
                        and jumlah_trad <> 0 and jumlah_trad < 0";
                $sql .= " group by tra_id,no_prk,nama_prk,prk_id order by no_prk asc";
                $rs_edit = $dtaccess->Execute($sql);
                $dataDetilKredit = $dtaccess->FetchAll($rs_edit);

                $sql = "select sum(jumlah_trad) as total from gl.gl_buffer_transaksidetil where tra_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]['id_tra']);
                $Sum = $dtaccess->Fetch($sql);
                  for ($j = 0, $count = 0, $m = count($dataDetilDebet); $j < $m; $j++, $count = 0) {
                  $nm_prk[$j] = "&nbsp;" . $dataDetilDebet[$j]["nama_prk"] . "(" . $dataDetilDebet[$j]["no_prk"] . ")";
                  // echo $sql;
                ?>
                  <tr>
                    <td colspan="3" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;</td>
                    <td class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;&nbsp;<?php echo ($nm_prk[$j]); ?></td>
                    <?php if ($dataDetilDebet[$j]["total"] > 0) { ?>
                      <td align="right" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;<?php echo currency_format(abs($dataDetilDebet[$j]["total"])); ?></td>
                      <td class="tablecontent-odd">&nbsp;</td>
                    <? } ?>
                    <?php if ($dataDetilDebet[$j]["total"] < 0) { ?>
                      <td class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;</td>
                      <td align="right" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;<?php echo currency_format(abs($dataDetilDebet[$j]["total"])); ?></td>
                    <? } ?>
                  </tr>
                <?php } ?>
                <?php 
                for ($j = 0, $count = 0, $m = count($dataDetilKredit); $j < $m; $j++, $count = 0) {
                  $nm_prk[$j] = "&nbsp;" . $dataDetilKredit[$j]["nama_prk"] . "(" . $dataDetilKredit[$j]["no_prk"] . ")";
                  // echo $sql;
                ?>
                  <tr>
                    <td colspan="3" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;</td>
                    <td class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;&nbsp;<?php echo ($nm_prk[$j]); ?></td>
                    <?php if ($dataDetilKredit[$j]["total"] > 0) { ?>
                      <td align="right" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;<?php echo currency_format(abs($dataDetilKredit[$j]["total"])); ?></td>
                      <td class="tablecontent-odd">&nbsp;</td>
                    <? } ?>
                    <?php if ($dataDetilKredit[$j]["total"] < 0) { ?>
                      <td class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;</td>
                      <td align="right" class="tablecontent-odd" <?php if ($Sum['total'] != 0) echo "style='color: red;'" ?>>&nbsp;<?php echo currency_format(abs($dataDetilKredit[$j]["total"])); ?></td>
                    <? } ?>
                  </tr>
                <?php } ?>
                <?php 
                $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_buffer_transaksidetil
                where jumlah_trad > '0' and tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]);
                $rs_edit = $dtaccess->Execute($sql);
                $dataTot = $dtaccess->Fetch($rs_edit);
                $grandTotal += $dataTot['jumlah'];

                $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_buffer_transaksidetil
                where jumlah_trad < '0' and tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]);
                $rs_edit = $dtaccess->Execute($sql);
                $dataTotKredit = $dtaccess->Fetch($rs_edit);
                $grandTotalKredit += $dataTotKredit['jumlah'];
                ?>

                <tr>
                  <td colspan="3" class="tablecontent">&nbsp;</td>
                  <td align="right" class="tablecontent"><strong>&nbsp;&nbsp;Total&nbsp;Transaksi&nbsp;:&nbsp;<?php echo (currency_format($dataTot["jumlah"])); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
                  <td colspan="2" class="tablecontent">&nbsp;</td>
                </tr>

                <?php if(!$_GET['btnExcel']) { ?>
                <tr class="content" class="tablecontent">
                  <td colspan="3" class="tablecontent" nowrap>&nbsp;</td>
                  <td colspan="3" class="tablecontent" nowrap>&nbsp;&nbsp;
                    <input type="checkbox" name="cbPost[]" id="cbPost[<?php echo $dataTable[$i]["id_tra"]; ?>]" value="<?php echo $dataTable[$i]["id_tra"]; ?>" <?php if ($_GET['checkall'] == 'y') echo "checked"; ?> />
                    <label for="cbPost[<?php echo $dataTable[$i]["id_tra"]; ?>]">Posting&nbsp;ke&nbsp;GL</label>
                  </td>
                </tr>
                <?php } ?>
              <?php } ?>
              <tr class="tablesmallheader">
                <td colspan="5" nowrap>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4" class="tablecontent">&nbsp;</td>
                <td class="tablecontent"><?php echo currency_format($grandTotal) ?></td>
                <td class="tablecontent"><?php echo currency_format(abs($grandTotalKredit)) ?></td>
              </tr>
              <tr>
                <td colspan="4" class="tablecontent">&nbsp;</td>
                <td align="center" class="tablecontent" style="color: red;"><strong>&nbsp;&nbsp;GRAND TOTAL&nbsp;:&nbsp;<?php echo (currency_format($grandTotal)); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
                <td class="tablecontent">&nbsp;</td>
              </tr>
              <?php if(!$_GET['btnExcel']) { ?>
              <tr>
                <td colspan="3" owrap>&nbsp;</td>
                <td colspan="3" nowrap>&nbsp;&nbsp;<img src="<?php echo ($ROOT); ?>gambar/arrow_kiriatas.gif" border="0" align="middle" />&nbsp;&nbsp;
                  <a style="cursor: pointer;color: #1F457E;" href="<?php echo $checkPage; ?>">Check&nbsp;All</a>&nbsp;/&nbsp;
                  <a style="cursor: pointer;color: #1F457E;" href="<?php echo $uncheckPage; ?>">UnCheck&nbsp;All</a>&nbsp;</td>
              </tr>
              <?php } ?>
            </table>
            <?php if(!$_GET['btnExcel']) { ?>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td width="50%" nowrap>&nbsp;&nbsp;<input type="submit" name="btpost" id="btpost" value="Post to GL" class="btn btn-primary" />&nbsp;&nbsp;</td>
                <td align="right" width="50%" nowrap>&nbsp;&nbsp;
                  <!--<img src="<?php echo ($ROOT); ?>images/printer.png" border="0" width="16" height="16" align="middle" class="img-button" alt="Print" title="Print" OnClick="javascript: print_doc();" />-->&nbsp;&nbsp;&nbsp;&nbsp;</td>
              </tr>
            </table>
            <?php } ?>
          </form>
          
          <?php if(!$_GET['btnExcel']) { ?>
          <script type="text/javascript" src="<?php echo $ROOT; ?>lib/script/elements.js"></script>
          <script type="text/javascript" src="<?php echo $ROOT; ?>lib/script/func_curr.js"></script>
          <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>lib/css/expressa.css">
          <script type="text/javascript" src="<?php echo $ROOT; ?>lib/script/ew.js"></script>
          <?php //} 
          ?>
          <?php if ($_POST["btnShow"] && !$dataTable) { ?>
            <br>
            <font color="red"><b>Maaf Data Tidak Tersedia</b></font>
          <?php } ?>

        </div>
      </div>

      <?php require_once($LAY . "footer.php") ?>
      <?php require_once($LAY . "js.php") ?>
    <?php } ?>
</body>