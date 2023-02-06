<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
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
$table = new InoTable("table", "100%", "left");
$userData = $auth->GetUserData();
$depId = $auth->GetDepId();
$depNama = $auth->GetDepNama();

if ($_GET["loket"]) {
  $_GET["loket"] = $_GET["loket"];
} else if ($_GET["loket"]) {
  $_GET["loket"] = $_GET["loket"];
} else {
  $_GET["loket"] = $depId;
}

$findPage = "departemen_find2.php?";

if ($_x_mode == "New") $privMode = PRIV_CREATE;
elseif ($_x_mode == "Edit") $privMode = PRIV_UPDATE;
elseif ($_x_mode == "Delete") $privMode = PRIV_DELETE;
else $privMode = PRIV_READ;



$sql = "select id_prd,awal_prd,akhir_prd,nama_prd from  gl.gl_periode where nama_prd = " . QuoteValue(DPE_CHAR, $_GET["periode"]);
$rs_prk = $dtaccess->Execute($sql);
$dataPeriode = $dtaccess->Fetch($rs_prk);

if (!$_GET["tgl_awal"]) $_GET["tgl_awal"] .= format_date($dataPeriode["awal_prd"]);
if (!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] .= format_date($dataPeriode["akhir_prd"]);
if ($_GET["tgl_awal"]) $sql_where[] = "tanggal_tra >= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
if ($_GET["tgl_akhir"]) $sql_where[] = "tanggal_tra <= " . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));
if ($_GET["loket"] && $_GET["loket"] != "--") $sql_where[] .= " a.dept_id = " . QuoteValue(DPE_CHAR, $_GET["loket"]);
if ($_GET["flag_jurnal"] != "-") $sql_where[] .= " a.flag_jurnal = " . QuoteValue(DPE_CHAR, $_GET["flag_jurnal"]);

$sql = "select a.id_tra,a.ref_tra,a.tanggal_tra,a.ket_tra,a.namauser,a.real_time
              from  gl.gl_buffer_transaksi a where a.is_posting='n' and a.id_tra<>'1' and ref_tra not like 'RE%' and flag_jurnal != 'POBHP' ";
$sql .= " and " . implode(" and ", $sql_where);
$sql .= " order by a.tanggal_tra asc";
$rs_edit = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs_edit);
// echo $sql;
// exit();

$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
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
<script language="JavaScript">
  window.print();
</script>
<style>
  @media print {
    #tableprint {
      display: none;
    }
  }

  #splitBorder tr td table {
    border-collapse: collapse;
  }

  #splitBorder tr td table tr td {
    border: 1px solid black;
  }

  body {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    margin: 5px;
    margin-top: 0px;
    margin-left: 0px;
  }

  .menubody {
    background-image: url(gambar/background_01.gif);
    background-position: left;
  }

  .menutop {
    font-family: Arial;
    font-size: 11px;
    color: #FFFFFF;
    background-color: #000e98;
    background-image: url(gambar/bg_topmenu.png);
    background-repeat: repeat-x;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    height: 25px;
    background-position: left top;
    cursor: pointer;
  }

  .menubottom {
    background-image: url(gambar/submenu_bg.png);
    background-repeat: no-repeat;
  }

  .menuleft {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #333333;
    background-image: url(gambar/submenu_btn.png);
    background-repeat: repeat-y;
    font-weight: bolder;
  }

  .menuleft_bawah {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8px;
    color: #333333;
    background-image: url(gambar/submenu_btn_bawah.png);
    font-weight: bold;
  }

  .img-button {
    cursor: pointer;
    border: 0px;
  }

  .menuleft a:link,
  a:visited,
  a:active {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    text-decoration: none;
    color: #333333;
  }

  .menuleft a:hover {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12px;
    text-decoration: none;
    color: #6600CC;
  }

  table {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    padding: 0px;
    border-color: #000000;
    border-collapse: collapse;
    border-style: solid;
  }

  #tablesearch {
    display: none;
  }

  .passDisable {
    color: #0F2F13;
    border: 1px solid #f1b706;
    background-color: #ffff99;
  }

  .tabaktif {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #E60000;
    background-color: #ffe232;
    background-image: url(gambar/tbl_subheadertab.png);
    background-repeat: repeat-x;
    font-weight: bolder;
    height: 18;
    text-transform: capitalize;
  }

  .tabpasif {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000000;
    background-color: #ffe232;
    background-image: url(gambar/tbl_subheader2.png);
    background-repeat: repeat-x;
    font-weight: bolder;
    height: 18;
    text-transform: capitalize;
  }

  .caption {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-style: normal;
  }

  a:link,
  a:visited,
  a:active {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    text-decoration: none;
    color: #1F457E;

  }

  a:hover {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    text-decoration: underline;
    color: #8897AE;
  }

  .titlecaption {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    font-style: oblique;
    font-weight: bolder;

  }

  .tableheader {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #333333;
    font-weight: bold;
    text-transform: uppercase;
  }

  .tablesmallheader {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: bold;
    height: 18px;
    background-position: left top;
  }

  .tablecontent {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-odd {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-kosong {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: bold;
    color: #FC0508;
    height: 18px;
  }

  .tablecontent-medium {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 16px;
    font-weight: lighter;
    background-color: #fff5b3;
    height: 18px;
  }

  .tablecontent-gede {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 23px;
    font-weight: lighter;
    background-color: #fff5b3;
    height: 18px;
  }

  .tablecontent-odd {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-odd-kosong {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #FC0508;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-odd-medium {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 16px;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-odd-gede {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 23px;
    font-weight: lighter;
    height: 18px;
  }

  .tablecontent-telat {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #FC0508;
    font-weight: lighter;
    background-color: #fff5b3;
    height: 18px;
  }

  .tablecontent-odd-telat {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #FC0508;
    font-weight: lighter;
    height: 18px;
  }

  .inputField {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #0F2F13;
    border: 1px solid #1A5321;
    background-color: #EBF4A8;
  }


  .content {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    background-color: #E7E6FF;
    height: 18px;
  }

  .content-odd {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    height: 18px;
  }

  .subheader {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000000;
    background-color: #FFFFFF;
    font-weight: bolder;
    height: 18;
    text-transform: capitalize;
  }

  .subheader-print {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #000000;
    font-weight: bolder;
    height: 18;
  }

  .staycontent {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
  }

  .button,
  submit,
  reset {
    display: none;
    visibility: hidden;
  }

  select,
  option {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    text-indent: 2px;
    margin: 2px;
    left: 0px;
    clip: rect(auto auto auto auto);
    border-top: 0px;
    border-right: 0px;
    border-bottom: 0px;
    border-left: 0px;
  }

  input,
  textarea {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    border: 1px solid #f1b706;
    text-indent: 2px;
    margin: 2px;
    left: 0px;
    width: auto;
    vertical-align: middle;
  }

  .subtitlecaption {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-style: normal;
    font-weight: 500;
  }

  .inputcontent {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
    background: #E6EDFB url(../none);
    border: none;
    text-align: right;
  }

  .hlink {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
  }

  .navActive {
    color: #cc0000;
  }

  fieldset {
    border: thin solid #2F2F2F;
  }

  .whiteborder {
    border: none;
    margin: 0px 0px;
    padding: 0px 0px;
    border-collapse: collapse;
  }

  .adaborder {
    border-left: none;
    border-top: none;
    border-bottom: none;
    border-right: solid #999999 1px;
    margin: 0px 0px;
    padding: 0px 0px;
    border-collapse: separate;
  }

  .divcontent {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: lighter;
    background-color: #E7E6FF;
    border-bottom: solid #999999 1px;
    border-right: solid #999999 1px;
  }

  .curedit {
    text-align: right;
  }

  #div_cetak {
    display: block;
  }

  #tblSearching {
    display: none;
  }

  #printMessage {
    display: none;
  }

  #noborder.tablecontent {
    border-style: none;
  }

  #noborder.tablecontent-odd {
    border-style: none;
  }

  .noborder {
    border-style: none;
  }

  body {
    font-family: Arial, Verdana, Helvetica, sans-serif;
    margin: 0px;
    font-size: 10px;
  }

  .tableisi {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    border: none #000000 0px;
    padding: 4px;
    border-collapse: collapse;
  }


  .tableisi td {
    border: solid #000000 1px;
    padding: 4px;
  }

  .tablenota {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 10px;
    border: solid #000000 1px;
    padding: 4px;
    border-collapse: collapse;
  }

  .tablenota .judul {
    border: solid #000000 1px;
    padding: 4px;
  }

  .tablenota .isi {
    border-right: solid black 1px;
    padding: 4px;
  }

  .ttd {
    height: 50px;
  }

  .judul {
    font-size: 14px;
    font-weight: bolder;
    border-collapse: collapse;
  }


  .judul1 {
    font-size: 12px;
    font-weight: bolder;
  }

  .judul2 {
    font-size: 14px;
    font-weight: bolder;
  }

  .judul3 {
    font-size: 12px;
    font-weight: normal;
  }

  .judul4 {
    font-size: 11px;
    font-weight: bold;
    background-color: #CCCCCC;
    text-align: center;
  }

  .judul5 {
    font-size: 16px;
    font-weight: bold;
    background-color: #d6d6d6;
    text-align: center;
    color: #000000;
  }

  .judul5b {
    font-size: 12px;
    font-weight: bold;
    background-color: #d6d6d6;
    text-align: center;
    color: #000000;
  }

  .judul6 {
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    color: #000000;
  }

  @media print {
    thead {
      display: table-header-group;
    }

    tfoot {
      display: table-footer-group;
    }

    body {
      margin: 0;
    }
  }
</style>

<body onLoad="$('#autostart').trigger('click');">
  <div id="body">
    <table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
      <tr>
        <td width="10%" align="center"><img src="<?php echo $fotoName; ?>" height="50"> </td>
        <td align="center" bgcolor="#CCCCCC" id="judul">
          <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"] ?></strong><br></span>
          <span class="judul3">
            <?php echo $konfigurasi["dep_kop_surat_1"] ?><br>
            <?php echo $konfigurasi["dep_kop_surat_2"] ?></span></td>
      </tr>
      <tr>
        <td colspan="2" class="judul5">LAPORAN POSTING GL</td>
      </tr>
      <tr>
        <td colspan="2" class="judul5b"><? echo $_GET["tgl_awal"] . " s/d " . $_GET["tgl_akhir"]; ?></td>
      </tr>
    </table>

    <table width="100%" border="0" cellpadding="1" cellspacing="1">
      <tr class="tablecontent">

        <td width="100%" align="right">Periode <? echo $dataPeriode["nama_prd"]; ?></td>
      </tr>
    </table>
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
                $sql = "select a.id_tra,a.ref_tra,a.tanggal_tra,a.ket_tra,a.namauser,a.real_time,
            b.ket_trad,b.jumlah_trad,c.nama_prk,c.no_prk from  gl.gl_buffer_transaksidetil b
             left join  gl.gl_buffer_transaksi a on a.id_tra = b.tra_id
             left join  gl.gl_perkiraan c on b.prk_id = c.id_prk
             where a.is_posting='n' and b.tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]) . "
             order by jumlah_trad desc";
                $rs_edit = $dtaccess->Execute($sql);
                $dataDetil = $dtaccess->FetchAll($rs_edit);

                for ($j = 0, $count = 0, $m = count($dataDetil); $j < $m; $j++, $count = 0) {
                  $nm_prk[$j] = "[" . $dataDetil[$j]["no_prk"] . "]&nbsp;" . $dataDetil[$j]["nama_prk"]; ?>
                <?php $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_buffer_transaksidetil
                              where jumlah_trad > 0 and tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]);
                $rs_edit = $dtaccess->Execute($sql);
                $dataTotDebet = $dtaccess->Fetch($rs_edit);

                $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_buffer_transaksidetil
                        where jumlah_trad < 0 and tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]);
                $rs_edit = $dtaccess->Execute($sql);
                $dataTotKredit = $dtaccess->Fetch($rs_edit); ?>
                <?php if ($dataTotDebet["jumlah"] != abs($dataTotKredit["jumlah"])) { ?>
                  <tr>
                    <td colspan="3" class="tablecontent-odd">&nbsp;</td>
                    <td class="tablecontent-odd" style="color:red;">&nbsp;&nbsp;<?php echo ($nm_prk[$j]); ?>&nbsp;&nbsp;</td>
                    <?php if ($dataDetil[$j]["jumlah_trad"] > 0) { ?>
                      <td class="tablecontent-odd" style="color:red;">&nbsp;<?php echo currency_format(abs($dataDetil[$j]["jumlah_trad"])); ?></td>
                      <td class="tablecontent-odd">&nbsp;</td>
                    <? } ?>

                    <?php if ($dataDetil[$j]["jumlah_trad"] < 0) { ?>
                      <td class="tablecontent-odd">&nbsp;</td>
                      <td class="tablecontent-odd" style="color:red;">&nbsp;<?php echo currency_format(abs($dataDetil[$j]["jumlah_trad"])); ?></td>
                    <? } ?>
                  </tr>
                <?php }else{ ?>
                  <tr>
                    <td colspan="3" class="tablecontent-odd">&nbsp;</td>
                    <td class="tablecontent-odd">&nbsp;&nbsp;<?php echo ($nm_prk[$j]); ?>&nbsp;&nbsp;</td>
                    <?php if ($dataDetil[$j]["jumlah_trad"] > 0) { ?>
                      <td class="tablecontent-odd">&nbsp;<?php echo currency_format(abs($dataDetil[$j]["jumlah_trad"])); ?></td>
                      <td class="tablecontent-odd">&nbsp;</td>
                    <? } ?>

                    <?php if ($dataDetil[$j]["jumlah_trad"] < 0) { ?>
                      <td class="tablecontent-odd">&nbsp;</td>
                      <td class="tablecontent-odd">&nbsp;<?php echo currency_format(abs($dataDetil[$j]["jumlah_trad"])); ?></td>
                    <? } ?>
                  </tr>
                <?php } ?>
                  <?php //$total[$j] += $dataDetil[$j]["jumlah_trad"]; 
                  ?>
                <?php } ?>
                <?php $sql = "select sum(jumlah_trad) as jumlah from  gl.gl_buffer_transaksidetil
              where jumlah_trad > '0' and tra_id = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_tra"]);
                $rs_edit = $dtaccess->Execute($sql);
                $dataTot = $dtaccess->Fetch($rs_edit);
                $grandTotal += $dataTot['jumlah'];
                $grandTotalDebet = $grandTotalDebet + $dataTotDebet["jumlah"];
                $grandTotalKredit = $grandTotalKredit + $dataTotKredit["jumlah"];
                ?>

                <tr>
                  <td colspan="3" class="tablecontent">&nbsp;</td>
                  <td align="right" class="tablecontent"><strong>&nbsp;&nbsp;Total&nbsp;Transaksi&nbsp;:&nbsp;<?php echo (currency_format($dataTot["jumlah"])); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
                  <td colspan="2" class="tablecontent">&nbsp;</td>
                </tr>

              <?php } ?>
              <tr class="tablesmallheader">
                <td colspan="6" nowrap>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="4" class="tablecontent">&nbsp;</td>
                <td class="tablecontent"><?php echo currency_format($grandTotalDebet) ?></td>
                <td class="tablecontent"><?php echo currency_format(abs($grandTotalKredit)) ?></td>
              </tr>
              <tr>
                <td colspan="4" class="tablecontent">&nbsp;</td>
                <td align="center" colspan="2" class="tablecontent" style="color: red;"><strong>&nbsp;&nbsp;GRAND TOTAL&nbsp;:&nbsp;<?php echo (currency_format($grandTotal)); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
                <td class="tablecontent">&nbsp;</td>
              </tr>
            </table>
    <? php // } 
    ?>
    <?php if ($_GET["btnShow"] && !$dataTable) { ?>
      <br>
      <font color="red"><b>Maaf Data Tidak Tersedia</b></font>
    <?php } ?>