<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "barcode.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "phpqrcode/qrlib.php");

$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$depId = $auth->GetDepId();
$depNama = $auth->GetDepNama();
$plx = new expAJAX("");

if ($_GET['dokter']) {
	$sql = "select * from global.global_auth_user where usr_id='" . $_GET['dokter'] . "'";
	$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
	$dataDokter = $dtaccess->Fetch($rs);
}

if ($_GET["id"] || $_GET["id_reg"]) {

	//$_POST["cust_usr_id"]=$enc->Decode($_GET["id"]);	
	//	$_POST["cust_usr_id"] = $_GET["id"];

	$sql = "select a.cust_usr_jenis_kelamin, a.cust_usr_tanggal_lahir, a.cust_usr_kode,
	a.cust_usr_foto,a.cust_usr_nama,a.cust_usr_alamat as alamat1,a.cust_usr_suami,
	((current_date - a.cust_usr_tanggal_lahir)/365) as umur, c.id_poli, d.poli_nama, 
	c.reg_status_cetak_kartu,a.cust_usr_nama_kk, c.reg_kode_trans 
  		from   global.global_customer_user a  
  		left join  klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
  		left join   global.global_auth_poli d on d.poli_id = c.id_poli
  		where c.reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);

	$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
	$dataPasien = $dtaccess->Fetch($rs);

	//var_dump($dataPasien);
	//echo $sql;
	//echo "data".$dataPasien["cust_usr_nama"];
	if ($dataPasien["cust_usr_foto"]) {
		$fotoPasien = $ROOT . "/gambar/foto_pasien/" . $dataPasien["cust_usr_foto"];
	} else {
		$fotoPasien = $ROOT . "/gambar/foto_pasien/default.jpg";
	}

	//update status /
	$sql = "update klinik.klinik_registrasi set reg_tracer_barcode = 'y' where reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);
	$dtaccess->Execute($sql);
}

// KONFIHURASI
$sql = "select * from global.global_departemen where dep_id =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);

if ($konfigurasi["dep_height"] != 0) $panjang = $konfigurasi["dep_height"];
if ($konfigurasi["dep_width"] != 0) $lebar = $konfigurasi["dep_width"];
$fotoName = $ROOT . "/gambar/img_cfg/" . $konfigurasi["dep_logo"];
$bg = $ROOT . "/gambar/img_cfg/" . $konfigurasi["dep_logo"];

$sql = "select * from global.global_konfigurasi_kartu where id_dep =" . QuoteValue(DPE_CHAR, $depId);
$rs = $dtaccess->Execute($sql);
$konfKartu = $dtaccess->Fetch($rs);
$fotoKiri = $ROOT . "kasir/images/konfigurasi_kartu/" . $konfKartu["konf_kartu_pic_kiri"];
$fotoKanan = $ROOT . "kasir/images/konfigurasi_kartu/" . $konfKartu["konf_kartu_pic_kanan"];
$fotoBelakangKiri = $ROOT . "kasir/images/konfigurasi_kartu/" . $konfKartu["konf_kartu_pic_belakang_kiri"];
$fotoBelakangKanan = $ROOT . "kasir/images/konfigurasi_kartu/" . $konfKartu["konf_kartu_pic_belakang_kanan"];
$alamatPasien = substr(($dataPasien["alamat1"]), 0, 30);
// --- bagian barcode --- //
define(__TRACE_ENABLED__, false);
define(__DEBUG_ENABLED__, false);



// Default value //
if (!isset($output))  $output   = "png";
if (!$_GET["id"]) $barcode  = $dataPasien["cust_usr_kode"];
if (!isset($type))    $type     = "C128C";
if (!isset($width))   $width    = "200";
if (!isset($height))  $height   = "50.5";
if (!isset($xres))    $xres     = "2";
if (!isset($font))    $font     = "10";

//	if (isset($_GET["reg"])) $barcode1  = $_GET["reg"]; 
//	if (isset($_GET["id_reg"])) $barcode1  = $dataPasien["reg_kode_trans"];
//  echo "masuk".$_GET["reg"]; 
// echo $barcode;
// die();
$border = "off";
$drawtext = "off";
$stretchtext = "on";
//------------------------------------// 

if (isset($barcode) && strlen($barcode) > 0) {
	$style  = BCS_ALIGN_LEFT;
	$style |= ($output  == "png") ? BCS_IMAGE_PNG  : 0;
	$style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0;
	$style |= ($border  == "on") ? BCS_BORDER 	  : 0;
	$style |= ($drawtext == "on") ? BCS_DRAW_TEXT  : 0;
	$style |= ($stretchtext == "on") ? BCS_STRETCH_TEXT  : 0;
	$style |= ($negative == "on") ? BCS_REVERSE_COLOR  : 0;

	$obj = new  C128CObject($width, $height, $style, $barcode);

	if ($obj) {
		if ($obj->DrawObject($xres)) {
			$check_error = 0;
		} else {
			$check_error = 1;
		}
	}
}
// --- End bagian barcode --- // */


if ($_REQUEST['id_reg'] and !$_GET['reg_tanggal']) {
	$sql = "select a.tindakan_tanggal, tindakan_waktu,a.fol_id, a.id_biaya, a.id_biaya_tarif, a.id_reg, a.fol_lunas, a.id_dokter as dokter, a.fol_jumlah, 
				 e.biaya_tarif_id,
				 f.biaya_nama,
				 g.pemeriksaan_id
			from klinik.klinik_folio a
			left join klinik.klinik_perawatan_tindakan d on a.fol_id  = d.id_fol
			left join klinik.klinik_biaya_tarif e on a.id_biaya_tarif  = e.biaya_tarif_id
			left join klinik.klinik_biaya f on a.id_biaya  = f.biaya_id
			left join laboratorium.lab_pemeriksaan g on a.id_reg = g.id_reg
			where a.id_reg = '$_REQUEST[id_reg]' and a.fol_jenis_sem IS NULL
			";
	//$sql .= " and g.pemeriksaan_id in (select id_pemeriksaan from laboratorium.lab_pemeriksaan_detail) ";

	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
}
if ($_GET['reg_tanggal'] and $_GET['reg_waktu']) {
	$sqlReg = "SELECT id_cust_usr, reg_id FROM klinik.klinik_registrasi WHERE id_poli = '20' AND reg_tanggal = " . QuoteValue(DPE_DATE, date_db($_GET['reg_tanggal'])) . " AND reg_waktu = " . QuoteValue(DPE_CHAR, $_GET['reg_waktu']);
	$dataReg = $dtaccess->Fetch($sqlReg);

	$sql = "SELECT a.rujukan_tindakan_id, a.id_fol, b.biaya_nama FROM klinik.klinik_rujukan_tindakan a LEFT JOIN klinik.klinik_biaya b ON a.rujukan_tindakan_nama = b.biaya_id WHERE a.id_poli = '20' AND id_reg = " . QuoteValue(DPE_CHAR, $dataReg['reg_id']);
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
}
?>
<style>
	.regards {
		position: relative;
		float: right;
		padding-right: 20px;
		text-align: center;
	}

	body {
		margin: 0;
		/* overflow: ; */
		font-family: Arial, Helvetica, sans-serif;
		height: 800px;
		/* width: 800px; */
		white-space: nowrap;
	}

	@page {
		size: auto;
		/* auto is the initial value */
		margin: 0cm;

	}

	.block {
		display: inline-block;
		padding: 1px;
		font-size: 13px;
	}
</style>

<style>
	.wrapper {
		transform: rotate(90deg);
		position: fixed;
		margin-left: -50px;
		margin-top: 70px;
		padding: 5px 5px 50px 5px;
		border-style: dotted;
		border-right: 2px dashed silver;
		border-left: 0px dashed silver;
		border-top: 0px dashed silver;
		border-bottom: 0px dashed silver;
		width: 370px;
		/* border-color: white; */
	}
</style>



<!-- onload="setTimeout('self.close()',5000)" -->
<title>TINDAKAN RADIOLOGI CETAK</title>

<body>
	<div class="wrapper">
		<div>


			<span style="font-size:10px;font-family:sans-serif;font-weight:bold;text-align: center;">
				No RM : <?php echo $dataPasien["cust_usr_kode"]; ?>
				| Tl : <?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]); ?></span><br />

			<?php

			// $nm_akhir = explode(", ", str_replace('*', "'", $dataPasien["cust_usr_nama"]));
			$nama = explode(",", $dataPasien["cust_usr_nama"]);




			?>

			<span style="font-size:10px;font-family:sans-serif;font-weight:bold;text-align: center;"><?php echo substr($nama[0], 0, 20) . ", " . $nama[1] . ", " . $nama[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></span>


			<br>
			<img class="img" src="<?php echo $ROOT; ?>lib/barcode/image.php?code=<?= $barcode ?>&style=<?= $style ?>&type=<?= $type ?>&width=<?= $width ?>&height=<?= $height ?>&xres=<?= $xres ?>&font=<?= $font ?>">


			<!-- <p style="font-size:13px; margin-bottom: -10px;"> <?= $_REQUEST['nmps'] ?> (<?= $_REQUEST['norm'] ?>)</p> -->
			<h5 style="margin-bottom:-20px; ">TINDAKAN RADIOLOGI <?php echo date('d-m-Y'); ?><h5>

		</div>
		<hr style="width:400px;margin-bottom:-15px; border-style:dashed; color:white;">
		<br>
		<div class="block">
			<?php
			for ($i = 0; $i < count($dataTable); $i++) {

				echo '<li>' . $dataTable[$i]['biaya_nama'] . '</li>';
				// 'tindakan_tanggal'   => format_date($dataTable[$i]['tindakan_tanggal']),
				// 'tindakan_waktu'   => $dataTable[$i]['tindakan_waktu'],
				// 'folio_id'       => $dataTable[$i]['fol_id'],
				// 'id_biaya_tarif'   => $dataTable[$i]['id_biaya_tarif'],
				// 'id_reg'       => $dataTable[$i]['id_reg'],
				// 'fol_lunas'     => $dataTable[$i]['fol_lunas'],
				// 'dokter'       => $dataTable[$i]['dokter'],
				// 'fol_jumlah'     => $dataTable[$i]['fol_jumlah'],
				// 'biaya_tarif_id'   => $dataTable[$i]['biaya_tarif_id'],
				// 'tindakan_rujukan'     => $dataTable[$i]['biaya_nama'],
				// 'pemeriksaan_id'   => $dataTable[$i]['pemeriksaan_id'],

			}
			?>

		</div>
		<br>
		<br>
		<div>
			Dokter yang meminta
			<br>
			<br>
			<?= $dataDokter["usr_name"] ?>
		</div>

	</div>

	<script>
		window.print();
	</script>

</body>

</html>