<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$usrId = $auth->GetUserId();
$userData = $auth->GetUserData();
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$depLowest = $auth->GetDepLowest();

$thisPage = "report_penerimaan.php";
$cetakPage = "report_penerimaan_cetak.php?";

// PRIVILLAGE
if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}

// PRIVILLAGE
/*  if(!$auth->IsAllowed("apo_lap_beli_langsung",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_lap_beli_langsung",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='../../login.php?msg=Session Expired'</script>";
          exit(1);
     }   */



if ($_GET["klinik"]) {
  $_POST["klinik"] = $_GET["klinik"];
} else if ($_POST["klinik"]) {
  $_POST["klinik"] = $_POST["klinik"];
} else {
  $_POST["klinik"] = $depId;
}

if ($auth->IsAllowed() === 1) {
  include("login.php");
  exit();
}

$plx = new expAJAX();

$skr = date("d-m-Y");
if (!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
if (!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
$sql_where[] = "c.faktur_tgl >= " . QuoteValue(DPE_DATE, date_db($_POST["tanggal_awal"]));
$sql_where[] = "c.faktur_tgl <= " . QuoteValue(DPE_DATE, DateAdd(date_db($_POST["tanggal_akhir"]), 0));
if ($_POST["id_sup"] && $_POST["id_sup"] != "--") $sql_where[] = "d.id_sup = " . QuoteValue(DPE_CHAR, $_POST["id_sup"]);
if ($_POST["id_apotik"] && $_POST["id_apotik"] != "--") $sql_where[] = "i.flag_supplier = " . QuoteValue(DPE_CHAR, $_POST["id_apotik"]);
if ($_POST["id_sumber"] && $_POST["id_sumber"] != "--") $sql_where[] = "d.id_sumber = " . QuoteValue(DPE_CHAR, $_POST["id_sumber"]);
if ($_POST["klinik"] && $_POST["klinik"] != "--") $sql_where[] = "d.id_dep = " . QuoteValue(DPE_CHAR, $_POST["klinik"]);
if ($_POST["tipe_obat"] && $_POST["tipe_obat"] != "--") {
  if($_POST["tipe_obat"] == 'g'){
    $sql_where[] = " f.obat_flag = " . QuoteValue(DPE_CHAR, $_POST["tipe_obat"]);
  }
  else{
    $sql_where[] = " (f.obat_flag != " . QuoteValue(DPE_CHAR, $_POST["tipe_obat"])." or f.obat_flag is null) ";
  }
  
}



$sql_where[] = "f.item_nama is not null";
if ($_POST["id_kategori"] && $_POST["id_kategori"] != "--") $sql_where[] = "f.id_kategori = " . QuoteValue(DPE_CHAR, $_POST["id_kategori"]);
$cetakPage = "report_penerimaan_cetak.php?tanggal_awal="
  . $_POST["tanggal_awal"] . "&tanggal_akhir=" . $_POST["tanggal_akhir"];

if ($_POST["Keterangan_po"] && $_POST["Keterangan_po"] != "--") $sql_where[] = "d.keterangan_po = " . QuoteValue(DPE_CHAR, $_POST["Keterangan_po"]);
if ($sql_where[0])
  $sql_where = implode(" and ", $sql_where);

//	if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
if ($_POST["btnLanjut"] || $_POST["btnExcel"] || $_POST["btnCetak"]) {
  $sql = "select a.*,c.*,g.dep_nama,h.satuan_nama,h.satuan_jumlah,c.faktur_nomor,j.sumber_nama,
             i.sup_nama,d.*,f.*            
             from logistik.logistik_faktur_item a  
             left join logistik.logistik_faktur c on a.id_faktur=c.faktur_id
             left join logistik.logistik_po d on c.id_po=d.po_id
             left join logistik.logistik_item f on f.item_id=a.id_item
             left join global.global_departemen g on g.dep_id = a.id_dep
             left join logistik.logistik_item_satuan h on h.satuan_id = f.id_satuan_beli
             left join logistik.logistik_grup_item k on k.grup_item_id = f.id_kategori
             left join global.global_supplier i on i.sup_id = d.id_sup
             left join global.global_sumber j on j.sumber_id = d.id_sumber";
  $sql .= " where po_flag='M' and " . $sql_where;
  $sql .= " order by c.faktur_tgl asc, faktur_nomor asc, item_nama asc";
  
  $rs = $dtaccess->Execute($sql, DB_SCHEMA_LOGISTIK);
  $dataTable = $dtaccess->FetchAll($rs);
}
//echo $sql;	   
//*-- config table ---*//
$table = new InoTable("table1", "100%", "left", null, 0, 2, 1, null);
$tableHeader = "&nbsp;LAPORAN PENERIMAAN";



$tglAwal = format_date($_POST["tanggal_awal"]);
$tglAkhir = $_POST["tanggal_akhir"];

if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=report_pembelian_langsung.xls');
}

if ($_POST["btnCetak"]) {

  $_x_mode = "cetak";
}

if ($_POST["klinik"]) {
  //Data Klinik
  if ($depLowest == 'n') {
    $sql = "select * from global.global_departemen order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
  } else {
    $sql = "select * from global.global_departemen where dep_id = '" . $_POST["klinik"] . "' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
  }
} else {
  $sql = "select * from global.global_departemen order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}

//AMBIL DATA SUPPLIER
$sql = "select * from global.global_supplier where sup_flag='M' and sup_aktif='y' order by sup_nama";
$rs = $dtaccess->Execute($sql);
$dataSupplier = $dtaccess->FetchAll($rs);

//AMBIL DATA SUPPLIER
$sql = "select * from global.global_sumber order by sumber_nama";
$rs = $dtaccess->Execute($sql);
$dataSumber = $dtaccess->FetchAll($rs);

$sql = "select * from logistik.logistik_grup_item order by grup_item_nama";
$rs = $dtaccess->Execute($sql);
$kategorinama = $dtaccess->FetchAll($rs);
$row = 0;
?>

<!DOCTYPE html>
<html lang="en">
  <?php if (!$_POST["btnExcel"]) { ?>
  <script language="Javascript">
  <? $plx->Run(); ?>
  var _wnd_new;
  function BukaWindow(url, judul) {
  if (!_wnd_new) {
  _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
  } else {
  if (_wnd_new.closed) {
  _wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
  } else {
  _wnd_new.focus();
  }
  }
  return false;
  }
  <?php if ($_x_mode == "cetak") { ?>
  //if(confirm('Cetak Laporan Penjualan Obat?'))
  BukaWindow('report_penerimaan_cetak.php?tanggal_awal=<?php echo $_POST["tanggal_awal"]; ?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"]; ?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"]; ?>&id_sup=<?php echo $_POST["id_sup"]; ?>&id_kategori=<?php echo $_POST["id_kategori"]; ?>&id_sumber=<?php echo $_POST["id_sumber"]; ?>&klinik=<?php echo $_POST["klinik"]; ?>', 'Pembelian Langsung');
  document.location.href = '<?php echo $thisPage; ?>';
  <?php } ?>
  </script>
  <script type="text/javascript" src="<?php echo $ROOT; ?>lib/script/jquery-1.11.3.min.js"></script>
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
            <div class="page-title">
              <div class="title_left">
                <h3>Logistik</h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Laporan Penerimaan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <?php if (!$_POST["btnExcel"]) { ?>
                    <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="tanggal_awal" type='text' class="form-control" value="<?php if ($_POST['tanggal_awal']) {
                          echo $_POST['tanggal_awal'];
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
                          <input name="tanggal_akhir" type='text' class="form-control" value="<?php if ($_POST['tanggal_akhir']) {
                          echo $_POST['tanggal_akhir'];
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Supplier</label>
                        <select class="form-control" name="id_sup" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                          <option class="inputField" value="--">- Semua Supplier -</option>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($dataSupplier); $i < $n; $i++) {
                          ?>
                          <option class="inputField" value="<?php echo $dataSupplier[$i]["sup_id"]; ?>" <?php if ($_POST["id_sup"] == $dataSupplier[$i]["sup_id"]) echo "selected" ?>><?php echo $dataSupplier[$i]["sup_nama"]; ?>&nbsp;</option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Apotik</label>
                        <select class="form-control" name="id_apotik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                          <option class="inputField" value="--" >- Semua Apotik -</option>
                          <option class="inputField" value="J" <?=($_POST['id_apotik'] == 'J') ? "selected" : ""?>>Apotik Graha</option>
                          <option class="inputField" value="I" <?=($_POST['id_apotik'] == 'I') ? "selected" : ""?>>Apotik RSIA</option>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Sumber Dana</label>
                        <select class="form-control" name="id_sumber" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
                          <option class="inputField" value="--">- Semua Sumber Dana -</option>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($dataSumber); $i < $n; $i++) {
                          ?>
                          <option class="inputField" value="<?php echo $dataSumber[$i]["sumber_id"]; ?>" <?php if ($_POST["id_sumber"] == $dataSumber[$i]["sumber_id"]) echo "selected" ?>><?php echo $dataSumber[$i]["sumber_nama"]; ?>&nbsp;</option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Obat</label>
                        <select class="form-control" name="tipe_obat" class="inputField">
                          <option class="inputField" value="--">- Tipe Obat -</option>
                          <option class="inputField" value="g" <?=($_POST['tipe_obat'] == 'g') ? "selected" : ""?>>Generik</option>
                          <option class="inputField" value="t" <?=($_POST['tipe_obat'] == 't') ? "selected" : ""?>>Non Generik</option>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Barang</label>
                        <select class="form-control" name="id_kategori" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
                          <option class="inputField" value="--">- Pilih Kategori -</option>
                          <?php $counter = -1;
                          for ($i = 0, $n = count($kategorinama); $i < $n; $i++) {
                          ?>
                          <option class="inputField" value="<?php echo $kategorinama[$i]["grup_item_id"]; ?>" <?php if ($_POST["id_kategori"] == $kategorinama[$i]["grup_item_id"]) echo "selected" ?>><?php echo $kategorinama[$i]["grup_item_nama"]; ?>&nbsp;</option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Keterangan</label>
                        <input type="text" class="form-control" name="Keterangan_po" id="Keterangan_po" size="30" maxlength="30" value="<?php echo $_POST["Keterangan_po"]; ?>" onKeyDown="return tabOnEnter(this, event);" />
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="submit">
                        <input type="submit" name="btnExcel" value="Export Excel" class="submit">
                        <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="submit">
                      </div>
                      <div class="clearfix"></div>
                      <?php }
                      } ?>
                      <?php if ($_POST["btnExcel"]) { ?>
                      <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr width="100%" class="tableheader">
                          <td width="100%" align="center" colspan="<?php echo (count($dataSplit) + 6) ?>">
                            <strong>LAPORAN PEMBELIAN OBAT LANGSUNG<br />
                            <?php echo $konfigurasi["dep_nama"] ?><?php echo $konfigurasi["dep_kop_surat_1"] ?><?php echo $konfigurasi["dep_kop_surat_2"] ?>
                            <!-- <br/>TAHUN <?php echo $dataTable[0]["tahun"] ?><br/> -->
                            </strong>
                          </td>
                        </tr>
                        <tr class="tableheader">
                          <td align="left" colspan="<?php echo (count($dataSplit) + 6) ?>">
                            <?php echo $poliNama; ?><br />
                            <?php if ($_POST["tanggal_awal"] == $_POST["tanggal_awal"]) {
                            echo "Tanggal : " . $_POST["tanggal_awal"];
                            } elseif ($_POST["tanggal_awal"] != $_POST["tanggal_akhir"]) {
                            echo "Periode : " . $_POST["tanggal_awal"] . " - " . $_POST["tanggal_akhir"];
                            }  ?>
                            <br />
                          </td>
                        </tr>
                      </table>
                      <?php } ?>
                      <input type="hidden" name="x_mode" value="<?php echo $_x_mode; ?>" />
                      <?php if (!$_POST["btnExcel"]) { ?>
                      <script type="text/javascript">
                      Calendar.setup({
                      inputField: "tanggal_awal", // id of the input field
                      ifFormat: "<?= $formatCal; ?>", // format of the input field
                      showsTime: false, // will display a time selector
                      button: "img_awal", // trigger for the calendar (button ID)
                      singleClick: true, // double-click mode
                      step: 1 // show all years in drop-down boxes (instead of every other year as default)
                      });
                      Calendar.setup({
                      inputField: "tanggal_akhir", // id of the input field
                      ifFormat: "<?= $formatCal; ?>", // format of the input field
                      showsTime: false, // will display a time selector
                      button: "img_akhir", // trigger for the calendar (button ID)
                      singleClick: true, // double-click mode
                      step: 1 // show all years in drop-down boxes (instead of every other year as default)
                      });
                      </script>
                      <?php } ?>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <!-- <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?> -->
                      <thead>
                        <tr>
                          <th rowspan="2">No</th>
                          <th rowspan="2">Tanggal</th>
                          <th rowspan="2">Tanggal Jatuh tempo</th>
                          <th rowspan="2">No Transaksi</th>
                          <th rowspan="2">Supplier</th>
                          <th rowspan="2">No Faktur</th>
                          <th rowspan="2">Nama Item Barang</th>
                          <th rowspan="2">Satuan</th>
                          <th rowspan="2">Harga Satuan</th>
                          <th rowspan="2">Jumlah</th>
                          <th rowspan="2">Harga Gross</th>
                          <th colspan="2">Diskon</th>
                          <th rowspan="2">Setelah Diskon</th>
                          <th colspan="2">PPN</th>
                          <th rowspan="2">Setelah PPN</th>
                          <th colspan="2">Harga Beli</th>
                        </tr>
                        <tr>
                          <th>%</th>
                          <th>Rp</th>
                          <th>%</th>
                          <th>Rp</th>
                          <th>Lama</th>
                          <th>Baru</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php for ($i = 0; $i < count($dataTable); $i++) { ?>
                          <?php 
                            if ($dataTable[$i]["po_id"] != $dataTable[$i - 1]["po_id"]) { 
                             $row++;
                             // $SubTotal += $dataTable[$i]['faktur_item_hna_ppn_minus_diskon'];
                             $sql = "select sum(faktur_item_hna_ppn_minus_diskon) as subtotal from logistik.logistik_faktur_item where id_faktur = ".QuoteValue(DPE_CHAR,$dataTable[$i]['faktur_id']);
                             $SubTotal = $dtaccess->Fetch($sql);
                          ?>
                            <tr style="font-weight: bold; color: #273340;">
                              <td><?php echo $row ?></td>
                              <td><?php echo $dataTable[$i]["faktur_tgl"] ?></td>
                              <td><?php echo $dataTable[$i]["po_jatuh_tempo"] ?></td>
                              <td><?php echo $dataTable[$i]["no_trans"] ?></td>
                              <td><?php echo $dataTable[$i]["sup_nama"] ?></td>
                              <td colspan="14">&nbsp;</td>
                            </tr>
                            <tr style="font-weight: bold; color: #273340;">
                              <td colspan="16">SUB TOTAL</td>
                              <td colspan="3"><?php echo "Rp." . currency_format($SubTotal['subtotal']); ?></td>
                            </tr>
                          <?php } ?>
                          <tr>
                            <td colspan="5">&nbsp;</td>
                            <td><?php echo $dataTable[$i]["faktur_nomor"] ?></td>
                            <td><?php echo $dataTable[$i]["item_nama"] ?></td>
                            <td><?php echo "&nbsp;" . $dataTable[$i]["satuan_nama"] . " (" . $dataTable[$i]["satuan_jumlah"] . ")" ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_hna"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_jumlah"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_hna_total"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_diskon_persen"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_diskon"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_hna_diskon"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_ppn_persen"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_ppn"]) ?></td>
                            <td><?php echo currency_format($bayar = $dataTable[$i]["faktur_item_hna_ppn_minus_diskon"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_harga_beli_lama"]) ?></td>
                            <td><?php echo currency_format($dataTable[$i]["faktur_item_harga_beli"]) ?></td>
                          </tr>
                        <?php 
                          $hppTot += $dataTable[$i]["faktur_item_hna_total"];
                          $totDiskon += $dataTable[$i]["faktur_item_diskon"];
                          $totPPN += $dataTable[$i]["faktur_item_ppn"];
                          $linetotal += $bayar;
                          unset($SubTotal);
                          } 
                        ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="9">GRAND TOTAL</td>
                          <td colspan="3"><?php echo "Rp." . currency_format($hppTot); ?></td>
                          <td colspan="3"><?php echo "Rp." . currency_format($totDiskon); ?></td>
                          <td ><?php echo "Rp." . currency_format($totPPN); ?></td>
                          <td colspan="1"><?php echo "Rp." . currency_format($linetotal); ?></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--page content -->
        <!-- footer content -->
        <?php require_once($LAY . "footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <?php require_once($LAY . "js.php") ?>
  </body>
</html>