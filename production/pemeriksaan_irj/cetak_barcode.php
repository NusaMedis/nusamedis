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
if (isset($_GET["id"])) $barcode  = $dataPasien["cust_usr_kode"];
if (!isset($type))    $type     = "C128C";
if (!isset($width))   $width    = "200";
if (!isset($height))  $height   = "50.5";
if (!isset($xres))    $xres     = "2";
if (!isset($font))    $font     = "10";

//	if (isset($_GET["reg"])) $barcode1  = $_GET["reg"]; 
//	if (isset($_GET["id_reg"])) $barcode1  = $dataPasien["reg_kode_trans"];
//  echo "masuk".$_GET["reg"]; 
//     die();
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

?>
<html>

<head>

	<title>Cetak Barcode Pasien</title>
	<style>
		.img {
			width: 100%;
		}
	</style>
</head>

<body onload="window.print()">
	<table width="620px">

		<tr>
			<?php


			for ($i = 1; $i <= 3; $i++) { ?>

				<?php

				for ($j = 1; $j <= 10; $j++) {; ?>


					<td rowspan="3" style="width:181px; float:left; text-align:left; margin-right:0px;margin-left:15px;margin-bottom: 0px; padding-right: 4px;padding-left: 5px;  margin-bottom: 0px;padding-top: 5px;padding-bottom: 5px;">


						<?php
						if ($obj) {
							if ($check_error) {
								echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
							} else { ?>


								<span style="font-size:10px;font-family:sans-serif;font-weight:bold;text-align: center;">
									No RM : <?php echo $dataPasien["cust_usr_kode"]; ?>
									| Tl : <?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]); ?></span><br />

								<?php

								$nm_akhir = explode(", ", str_replace("*", "'", $dataPasien["cust_usr_nama"]));
								$nama = explode(",", $dataPasien["cust_usr_nama"]);





								?>

								<span style="font-size:10px;font-family:sans-serif;font-weight:bold;text-align: center;"><?php echo substr($nm_akhir[0], 0, 20) . ", " . $nm_akhir[1] .", " . $nm_akhir[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></span>



								<img class="img" src="<?php echo $ROOT; ?>lib/barcode/image.php?code=<?= $barcode ?>&style=<?= $style ?>&type=<?= $type ?>&width=<?= $width ?>&height=<?= $height ?>&xres=<?= $xres ?>&font=<?= $font ?>">


						<?php }
						}
						?>
					</td>



				<?php  } ?>



			<?php }

			?>
		</tr>
	</table>

	<!--<div style="border:0px solid black; width:11cm; margin: 10 auto">
			<div style="width:40%; float:left; text-align:left; margin-right:70;margin-left:10;">
				<span style="font-size:12px;font-family:sans-serif;font-weight:bold;">
					<?php echo substr(($dataPasien["cust_usr_nama"]), 0, 19) . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?>
				</span>
				<?php
				if ($obj) {
					if ($check_error) {
						echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
					} else { ?>
							<span style="font-size:12px;font-family:sans-serif;">No. RM : <?php echo $dataPasien["cust_usr_kode"]; ?></span>
					  <br><span style="font-size:11px;font-family:sans-serif;">TTL : <?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]); ?></span>
					  <img class="img" src="<?php echo $ROOT; ?>lib/barcode/image.php?code=<?= $barcode ?>&style=<?= $style ?>&type=<?= $type ?>&width=<?= $width ?>&height=<?= $height ?>&xres=<?= $xres ?>&font=<?= $font ?>">
					  <?php }
				}
						?>
			</div>
			<div style="width:40%; float:left; text-align:left;">
				<span style="font-size:12px;font-family:sans-serif;font-weight:bold;">
					<?php echo substr(($dataPasien["cust_usr_nama"]), 0, 19) . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?>
				</span>
				<?php
				if ($obj) {
					if ($check_error) {
						echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
					} else { ?>
							
					  <span style="font-size:12px;font-family:sans-serif;">No. RM : <?php echo $dataPasien["cust_usr_kode"]; ?></span>
					  <br><span style="font-size:11px;font-family:sans-serif;">TTL : <?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]); ?></span>
					  <img class="img" src="<?php echo $ROOT; ?>lib/barcode/image.php?code=<?= $barcode ?>&style=<?= $style ?>&type=<?= $type ?>&width=<?= $width ?>&height=<?= $height ?>&xres=<?= $xres ?>&font=<?= $font ?>">
					  <?php }
				}
						?>
			</div>
		</div> 
</body>

</html>
     