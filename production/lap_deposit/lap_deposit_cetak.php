  <?php
	require_once("../penghubung.inc.php");
	require_once($ROOT . "lib/login.php");
	require_once($ROOT . "lib/encrypt.php");
	require_once($ROOT . "lib/datamodel.php");
	require_once($ROOT . "lib/dateLib.php");
	require_once($ROOT . "lib/currency.php");
	require_once($ROOT . "lib/expAJAX.php");
	require_once($ROOT . "lib/tampilan.php");

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
	$thisPage = "report_setoran_loket.php";
	$printPage = "report_setoran_loket_cetak.php?";

	//if (!$_GET["klinik"]) $_GET["klinik"]=$depId;
	if ($_GET["klinik"]) {
		$_GET["klinik"] = $_GET["klinik"];
	} else if (!$_GET["klinik"]) {
		$_GET["klinik"] = $depId;
	}

	// if(!$auth->IsAllowed("kassa_informasi_lap_deposit",PRIV_CREATE)){
	//      die("access_denied");
	//      exit(1);
	// } else if($auth->IsAllowed("kassa_informasi_lap_deposit",PRIV_CREATE)===1){
	//      echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
	//      exit(1);
	// }

	// KONFIGURASI
	// $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $_GET["klinik"]);
	// $rs = $dtaccess->Execute($sql);
	// $konfigurasi = $dtaccess->Fetch($rs);


	$skr = date("d-m-Y");
	$time = date("H:i:s");

	if (!$_GET['tgl_awal']) {
		$_GET['tgl_awal']  = $skr;
	}
	if (!$_GET['tgl_akhir']) {
		$_GET['tgl_akhir']  = $skr;
	}


	// KONFIGURASI
	// $sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
	// $rs = $dtaccess->Execute($sql);
	// $konfigurasi = $dtaccess->Fetch($rs);
	$_GET["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
	$_GET["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

	$table = new InoTable("table", "100%", "left");

if (!$_GET["tgl_awal"]) $_GET["tgl_awal"] = $skr;
if (!$_GET["tgl_akhir"]) $_GET["tgl_akhir"] = $skr;
if ($_GET["tgl_awal"]) $_GET["tgl_awal"] =  $_GET["tgl_awal"];
if ($_GET["tgl_akhir"]) $_GET["tgl_akhir"] =  $_GET["tgl_akhir"];

if ($_GET["tgl_awal"]) {
  $sql_where[] = "date(deposit_history_tgl)>=" . QuoteValue(DPE_DATE, date_db($_GET["tgl_awal"]));
}
if ($_GET["tgl_akhir"]) {
  $sql_where[] = "date(deposit_history_tgl)<=" . QuoteValue(DPE_DATE, date_db($_GET["tgl_akhir"]));
}
if ($_GET['posting'] == 'y') $sql_where[] .= "id_pembayaran is not null";
if ($_GET['posting'] == 'n') $sql_where[] .= "id_pembayaran is null";
$sql_where[] = "1=1";

if ($sql_where[0])
  $sql_where = implode(" and ", $sql_where);

//if($_GET["btnLanjut"])   
//{   
$sql = "SELECT c.cust_usr_kode, a.deposit_history_no_bukti, c.cust_usr_nama, a.deposit_history_tgl, 
jbayar_nama, jenis_nama, a.id_pembayaran
from klinik.klinik_deposit_history a 
left join klinik.klinik_deposit b on b.id_cust_usr=a.id_cust_usr 
left join global.global_customer_user c on c.cust_usr_id=a.id_cust_usr 
left join global.global_jenis_bayar d on d.jbayar_id = a.id_jbayar 
left join klinik.klinik_registrasi e on e.id_pembayaran = a.id_pembayaran 
left join global.global_jenis_pasien f on f.jenis_id = e.reg_jenis_pasien 
where deposit_history_flag <> 'M' and reg_utama is null and " . $sql_where;
$sql .= " group by c.cust_usr_id, a.id_pembayaran, a.deposit_history_no_bukti, a.deposit_history_tgl, 
jbayar_nama, jenis_nama, a.deposit_history_when_create order by deposit_history_when_create asc";
$rs = $dtaccess->Execute($sql);
$dataTable = $dtaccess->FetchAll($rs);

//Saldo Awal
$sql = "select sum(deposit_history_nominal) as total from klinik.klinik_deposit_history where deposit_history_tgl < ".QuoteValue(DPE_DATE, date_db($_GET['tgl_awal']));
$SaldoAwal = $dtaccess->Fetch($sql);

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



  <title>LAPORAN DEPOSIT</title>
  <script language="javascript" type="text/javascript">
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


  	.judul {
  		font-size: 14px;
  		font-weight: bolder;
  		border-collapse: collapse;
  	}


  	.judul1 {
  		font-size: 14px;
  		font-weight: bolder;
  	}

  	.judul2 {
  		font-size: 14px;
  		font-weight: bolder;
  	}

  	.judul3 {
  		font-size: 18px;
  		font-weight: normal;
  	}

  	.judul4 {
  		font-size: 12px;
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

  	.judul6 {
  		font-size: 12px;
  		font-weight: bold;
  		text-align: center;
  		color: #000000;
  	}
  </style>

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
  		<td width="100%" colspan="3" style="text-align:center;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN DEPOSIT YANG BELUM DIPOSTING</td>
  	</tr>
  	<tr>
  		<?php if ($_GET["tgl_awal"] == $_GET["tgl_akhir"]) { ?>
  			<td width="9%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tanggal</td>
  			<td width="1%">:</td>
  			<td width="90%"><?php echo ($_GET["tgl_awal"]); ?></td>
  		<?php } else { ?>
  			<td width="9%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode</td>
  			<td width="1%">:</td>
  			<td width="90%"><?php echo ($_GET["tgl_awal"]); ?> s/d <?php echo ($_GET["tgl_akhir"]); ?></td>
  		<?php } ?>
  	</tr>
  </table>
  <br>
  <br>
  <table class="table table-bordered" border="1" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">No Medrec</th>
        <th style="text-align: center;">No Bukti Transaksi</th>
        <th style="text-align: center;">Nama Pasien</th>
        <th style="text-align: center;">Tanggal Posting</th>
        <th style="text-align: center;">Tanggal Penerimaan UM</th>
        <th style="text-align: center;">Debet</th>
        <th style="text-align: center;">Kredit</th>
        <th style="text-align: center;">Saldo</th>
      </tr>
    </thead>
      <tbody>
        <tr>
          <td colspan="7">SALDO AWAL</td>
          <td align="right"><?php echo currency_format($SaldoAwal['total']); ?></td>
        </tr>
        <?php 
          for ($i=0; $i < count($dataTable); $i++) { 
            $sql = "select * from klinik.klinik_deposit_history where id_pembayaran = ".QuoteValue(DPE_CHAR, $dataTable[$i]['id_pembayaran'])."
                    and deposit_history_flag != 'M' limit 1";
            $TglKredit = $dtaccess->Fetch($sql);

            $DepositDebet = currency_format($dataTable[$i]['deposit_history_nominal']);
            // if ($TglKredit) $DepositKredit = currency_format($dataTable[$i]['deposit_history_nominal']); else $DepositKredit = 0;

            if (date_db($TglKredit['deposit_history_tgl']) <= $_POST['tgl_akhir']) {
              $TglPosting = date_db($TglKredit['deposit_history_tgl']);
              $DepositKredit = currency_format($dataTable[$i]['deposit_history_nominal']);
            }else{
              $TglPosting = '-';
              $DepositKredit = 0;
            }
            $Saldo = StripCurrency($DepositDebet) - StripCurrency($DepositKredit);
        ?>
          <tr>
            <td align="center" width="10%"><?php echo $dataTable[$i]['cust_usr_kode'] ?></td>
            <td align="center" width="10%"><?php echo $dataTable[$i]['deposit_history_no_bukti'].' / '.$dataTable[$i]['jenis_nama'] ?></td>
            <td align="left" width="30%"><?php echo $dataTable[$i]['cust_usr_nama'] ?></td>
            <td align="center" width="10%"><?php echo date_db($TglPosting) ?></td>
            <td align="center" width="10%"><?php echo date_db($dataTable[$i]['deposit_history_tgl']) ?></td>
            <td align="right" width="10%"><?php echo $DepositDebet ?></td>
            <td align="right" width="10%"><?php if($TglKredit['deposit_history_tgl'] != '') echo $DepositKredit; else echo '-'; ?></td>
            <td align="right" width="10%"><?php echo currency_format($Saldo) ?></td>
          </tr>
        <?php 
            $SubDebet += StripCurrency($DepositDebet);
            $SubKredit += StripCurrency($DepositKredit);
            $SubSaldo += $Saldo;
          } 
        ?>
      </tbody>
    <tfoot>
      <tr>
        <td align="center" colspan="5"><b>TOTAL</b></td>
        
        <td align="right"><b><?php echo currency_format($SubKredit) ?></b></td>
        <td align="right"><b><?php echo currency_format($SubDebet) ?></b></td>
        <td align="right"><b><?php echo currency_format($SubSaldo) ?></b></td>
      </tr>
    </tfoot>
  </table>