<?php
require_once("../penghubung.inc.php");
require_once($ROOT . "lib/login.php");
require_once($ROOT . "lib/datamodel.php");
require_once($ROOT . "lib/dateLib.php");
require_once($ROOT . "lib/tampilan.php");
require_once($ROOT . "lib/currency.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$depNama = $auth->GetDepNama();

// if(!$auth->IsAllowed("kassa_informasi_lap_deposit_history",PRIV_CREATE)){
//       die("access_denied");
//       exit(1);
//  } else if($auth->IsAllowed("kassa_informasi_lap_deposit_history=",PRIV_CREATE)===1){
//       echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
//       exit(1);
//  } 


$_x_mode = "New";
$thisPage = "lap_deposit_view.php";


// KONFIGURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
$_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

$table = new InoTable("table", "100%", "left");
$skr = date("d-m-Y");

if (!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
if (!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
if ($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
if ($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];

if ($_POST["tanggal_awal"]) {
  $sql_where[] = "date(deposit_history_tgl)>=" . QuoteValue(DPE_DATE, date_db($_POST["tanggal_awal"]));
}
if ($_POST["tanggal_akhir"]) {
  $sql_where[] = "date(deposit_history_tgl)<=" . QuoteValue(DPE_DATE, date_db($_POST["tanggal_akhir"]));
}

if ($sql_where[0])
  $sql_where = implode(" and ", $sql_where);

$cetakPage = "lap_deposit_cetak.php?tanggal_awal=" . $_POST["tanggal_awal"] . "&tanggal_akhir=" . $_POST["tanggal_akhir"];
//if($_POST["btnLanjut"])   
//{   
$sql = "select a.*,b.*,c.*,jbayar_nama from klinik.klinik_deposit_history a
                left join klinik.klinik_deposit b on b.id_cust_usr=a.id_cust_usr
                left join global.global_customer_user c on c.cust_usr_id=a.id_cust_usr
                left join global.global_jenis_bayar d on d.jbayar_id = a.id_jbayar
                where  " . $sql_where;

$sql .= " order by cust_usr_id,deposit_history_when_create asc";
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);
//}      

$tableHeader = "&nbsp;Laporan History Deposit Global";
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Keterangan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Debet";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Kredit";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Saldo";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
$counterHeader++;

$m = 0;
for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
  if ($dataTable[$i]["cust_usr_kode"] == $dataTable[$i - 1]["cust_usr_kode"]) {
    $hitung[$dataTable[$i]["cust_usr_kode"]] += 1;
  }
}
for ($i = 0, $n = count($dataTable); $i < $n; $i++) {
  if ($dataTable[$i]["cust_usr_id"] == $dataTable[$i - 1]["cust_usr_id"]) {
    $hitung[$dataTable[$i]["cust_usr_id"]] += 1;
  }
}

for ($i = 0, $nomor = 1, $n = count($dataTable), $counter = 0; $i < $n; $i++, $counter = 0) {

  $tbContent[$i][$counter][TABLE_ISI] = ($i + 1);
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = formatTimestamp($dataTable[$i]["deposit_history_when_create"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  if ($dataTable[$i]["cust_usr_kode"] != $dataTable[$i - 1]["cust_usr_kode"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["cust_usr_kode"]] + 1;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $m++;
  }

  if ($dataTable[$i]["cust_usr_id"] != $dataTable[$i - 1]["cust_usr_id"]) {
    $dataSpan["jml_span"] = $hitung[$dataTable[$i]["cust_usr_id"]] + 1;

    $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
    $counter++;
    $m++;
  }

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["deposit_history_ket"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  if ($dataTable[$i]["deposit_history_nominal"] <= 0) {
    $tbContent[$i][$counter][TABLE_ISI] = '';
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format(abs($dataTable[$i]["deposit_history_nominal"]))) : currency_format(abs($dataTable[$i]["deposit_history_nominal"]));
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $counter++;
  } else {
    $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["deposit_history_nominal"])) : currency_format($dataTable[$i]["deposit_history_nominal"]);
    $tbContent[$i][$counter][TABLE_ALIGN] = "right";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $counter++;

    $tbContent[$i][$counter][TABLE_ISI] = '';
    $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    $counter++;
  }

  $tbContent[$i][$counter][TABLE_ISI] = ($_POST['btnExcel']) ? str_replace(',', '', currency_format($dataTable[$i]["deposit_history_nominal_sisa"])) : currency_format($dataTable[$i]["deposit_history_nominal_sisa"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;


  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["deposit_history_who_create"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
  $counter++;

  if ($dataTable[$i]["deposit_history_nominal"] < 0) {
    $kredit += abs($dataTable[$i]["deposit_history_nominal"]);
  } else {
    $debet += $dataTable[$i]["deposit_history_nominal"];
  }

  $grandTotal += abs($dataTable[$i]["deposit_history_nominal"]);
}

/*
          $tbContent[$i][$counter][TABLE_ISI] = '';
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][COLSPAN] = '5';
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($debet);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($kredit);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;        
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($grandTotal);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '';
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$counter++;*/



//-----konfigurasi-rue----//
$sql = "select * from global.global_departemen";
$sql .= " where dep_id=" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if ($_POST["btnCetak"]) {
  $_x_mode = "cetak";
}
//echo $sql;

?>
<script type="text/javascript">
  <?php if ($_x_mode == "cetak") { ?>
    window.open('<?php echo $cetakPage; ?>', '_blank');
  <?php } ?>
</script>

<?php
if ($_POST['btnExcel']) {
  header("Content-type: application/vnd-ms-excel");
  header("Content-Disposition: attachment; filename=Laporan History Deposit Global.xls");
}
?>
<?php if (!$_POST['btnExcel']) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
  <script src="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
  <script src="<?php echo $ROOT; ?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $("a[rel=sepur]").fancybox({
        'width': '60%',
        'height': '110%',
        'autoScale': false,
        'transitionIn': 'none',
        'transitionOut': 'none',
        'type': 'iframe'
      });
    });
  </script>

  <script type="text/javascript" src="<?php echo $ROOT; ?>lib/script/scroll_ipad2.js"></script>
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
                  <h2>Laporan History Deposit Global</h2>
                  <div class="clearfix"></div>
                </div>
                <?php if (!$_POST['btnExcel']) { ?>
                  <div class="x_content">
                    <form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>">
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary">
                        <input type="submit" name="btnExcel" value="Export Excel" class="btn btn-success">
                        <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="btn btn-Primary" />
                      </div>
                    </form>
                  <?php } ?>
                  <form name="frmView" method="POST" action="<?php echo $editPage; ?>">

                    <?php echo $table->RenderView($tbHeader, $tbContent, $tbBottom); ?>

                    <input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
                  </form>
                  <?php if (!$_POST['btnExcel']) { ?>
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
<?php } ?>