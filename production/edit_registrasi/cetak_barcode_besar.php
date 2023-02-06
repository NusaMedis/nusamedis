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

if ($_GET["id_reg"]) {
	//$_POST["cust_usr_id"]=$enc->Decode($_GET["id"]);	
	//	$_POST["cust_usr_id"] = $_GET["id"];

	$sql = "select a.cust_usr_jenis_kelamin, a.cust_usr_tanggal_lahir, a.cust_usr_kode,
	a.cust_usr_foto,a.cust_usr_nama,a.cust_usr_alamat as alamat1,a.cust_usr_suami,
	((current_date - a.cust_usr_tanggal_lahir)/365) as umur, c.id_poli, 
	c.reg_status_cetak_kartu,a.cust_usr_nama_kk, c.reg_kode_trans 
  		from   global.global_customer_user a  
  		left join  klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
  		
  		where c.reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);

	$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
	$dataPasien = $dtaccess->Fetch($rs);
	if($dataPasien["cust_usr_jenis_kelamin"]=="P"){
		$jk="perempuan";
	}
	else{

		$jk="laki-laki";
	}

	//var_dump($dataPasien);
	//echo $sql;
	//echo "data".$dataPasien["cust_usr_nama"];

	$pasien = str_split($dataPasien["cust_usr_kode"]);
	$kodepasien = $pasien[0] . "" . $pasien[1] . " " . $pasien[2] . "" . $pasien[3] . " " . $pasien[4] . "" . $pasien[5] . " " . $pasien[6] . "" . $pasien[7];


	if ($dataPasien["cust_usr_foto"]) {
		$fotoPasien = $ROOT . "/gambar/foto_pasien/" . $dataPasien["cust_usr_foto"];
	} else {
		$fotoPasien = $ROOT . "/gambar/foto_pasien/default.jpg";
	}
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

//update tracer barcode

//update klinik registrasi
$sql = "update klinik.klinik_registrasi set reg_tracer_barcode_besar='y' where
              reg_id = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);
$rs = $dtaccess->Execute($sql);

// --- bagian barcode --- //
define(__TRACE_ENABLED__, false);
define(__DEBUG_ENABLED__, false);



// Default value //
if (!isset($output))  $output   = "png";
if (isset($_GET["id_reg"])) $barcode  = $dataPasien["cust_usr_kode"];
if (!isset($type))    $type     = "C128C";
if (!isset($width))   $width    = "100";
if (!isset($height))  $height   = "80";
if (!isset($xres))    $xres     = "1";
if (!isset($font))    $font     = "0";
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

	//$obj = new C39Object($width, $height, $style, $barcode);
	$obj = new C128CObject($width, $height, $style, $barcode);
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

	<style type="text/css">
		body {
			font-family: Arial, Verdana, Helvetica, sans-serif;
			margin: 0px;
			font-size: 30px;
		}

		#dv_nama {
			position: absolute;
			top: 0px;
			left: 40px;
			z-index: 1;
			font-size: 13px;
			font-weight: bolder;
		}


		#dv_kode {
			position: absolute;
			top: 10px;
			left: 40px;
			z-index: 1;
			font-size: 12px;
			font-weight: bolder;
		}


		#dv_alamat {
			position: absolute;
			top: 20px;
			left: 40px;
			z-index: 1;
			font-size: 11px;
		}

		#dv_barcode {
			position: absolute;
			top: 30px;
			left: 10px;
			z-index: 1;
			font-size: 15px;
		}

		#dv_foto {
			position: absolute;
			top: 23px;
			left: 200px;
			z-index: 1;
		}

		table {
			font-size: 12px;
		}

		.rotate90 {
			-webkit-transform: rotate(90deg);
			-moz-transform: rotate(90deg);
			-o-transform: rotate(90deg);
			-ms-transform: rotate(90deg);
			transform: rotate(90deg);
		}
	</style>



</head>

<body onLoad="window.print();">
	<table align="left"  border="0" style="width:10 cm; height:4cm; margin-left:0px; border: 0px solid black;">
	
		<?php 
					  $tgl_lahir= new DateTime(date_db($dataPasien["cust_usr_tanggal_lahir"]));

					  // tanggal hari ini
					  $today = new DateTime('today');
						// hari
					  $d = $today->diff($tgl_lahir)->d;
				
					  // bulan
					  $m = $today->diff($tgl_lahir)->m;
				
				
					  // tahun
					  $y = $today->diff($tgl_lahir)->y;
				
				
				?>
				
			<td valign="top">
			
				<!--<div style="border:0px solid black; width:5 cm; height:2.5 cm;">
					<table border="0" style="width:5 cm; height:2.5cm; border: 0px solid black;">-->

					<?php

$nm_akhir = explode(", ", str_replace("*", "'", $dataPasien["cust_usr_nama"]));
$nama = explode(",", $dataPasien["cust_usr_nama"]);?>

<table border="0">

<?php

if($nm_akhir[1] =="BY"){?>


				
				<tr>
					<td align="left" rowspan="3" width="50px">
						
					</td>
			
						<td  align="left"  style="text-align:left;font-size:10px;font-family:sans-serif;" rowspan="3">
						RSIA Muslimat Jombang
							<?php
							if ($obj) {
								if ($check_error) {
									echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
								} else { 

									$quality = 'H'; 
									//Ukuran besar QRCode
									$ukuran = 2; 
									$padding = 0; 
									QRCode::png(  $dataPasien["cust_usr_kode"]."-".$dataPasien["cust_usr_nama"]."-".date_db($dataPasien["cust_usr_tanggal_lahir"]."(".$dataPasien["cust_usr_jenis_kelamin"].")"),"../pemeriksaan_lab/image.png",$quality,$ukuran,$padding);
								
									echo "<img src='../pemeriksaan_lab/image.png' width='50px'height='50px'";
		
								}
							}
							?>
						</td>
						<td align="left" style="text-align:left;font-size:10px;font-family:sans-serif;font-stretch:extra-expanded;letter-spacing: 1px; ">
						<b>	No RM : <?php echo $dataPasien["cust_usr_kode"]; //$kodepasien;
							?></b>
						</td>
																													
					
					</tr>

					<tr>
										
										<td align="left" style="text-align:left;font-size:8px;font-family:sans-serif;font-weight:bold;"><?php echo substr($nm_akhir[0], 0, 20) . ", " . $nm_akhir[1] .", " . $nm_akhir[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></td>		
										</tr>
									<tr>
										<td align="left" style="text-align:left;font-size:8px;font-family:sans-serif;"><b>TTL <?php echo date_db($dataPasien["cust_usr_tanggal_lahir"]); //$dataPasien["cust_usr_nama"];
											?></b></td>		
					</tr>


<?php } 




	elseif($nm_akhir[1] =="NY"){?>



<tr>
			
			<td  align="left"  style="text-align:left;font-size:10px;font-family:sans-serif;" rowspan="3">
			RSIA Muslimat Jombang
				<?php
				if ($obj) {
					if ($check_error) {
						echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
					} else { 

						$quality = 'H'; 
						//Ukuran besar QRCode
						$ukuran = 2; 
						$padding = 0; 
						QRCode::png(  $dataPasien["cust_usr_kode"]."-".$dataPasien["cust_usr_nama"]."-".date_db($dataPasien["cust_usr_tanggal_lahir"]."(".$dataPasien["cust_usr_jenis_kelamin"].")"),"../pemeriksaan_lab/image.png",$quality,$ukuran,$padding);
					
						echo "<img src='../pemeriksaan_lab/image.png' width='50px'height='50px'";

					}
				}
				?>
			</td>
			<td align="left" style="text-align:left;font-size:12px;font-family:sans-serif;font-stretch:extra-expanded;letter-spacing: 1px; ">
			<b>	No RM : <?php echo $dataPasien["cust_usr_kode"]; //$kodepasien;
				?></b>
			</td>
																										
		
		</tr>

		<tr>
			
			<td align="left" style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;"><?php echo substr($nm_akhir[0], 0, 20) . ", " . $nm_akhir[1] .", " . $nm_akhir[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></td>		
			</tr>
		<tr>
			<td align="left" style="text-align:left;font-size:12px;font-family:sans-serif;"><b>TTL <?php echo date_db($dataPasien["cust_usr_tanggal_lahir"]); //$dataPasien["cust_usr_nama"];
				?></b></td>		
			</tr>

		

		<?php }
		elseif($nm_akhir[1]=="AN"){?>

<tr>
			
			<td  align="left"  style="text-align:left;font-size:10px;font-family:sans-serif;" rowspan="3">
			RSIA Muslimat Jombang
				<?php
				if ($obj) {
					if ($check_error) {
						echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
					} else { 

						$quality = 'H'; 
						//Ukuran besar QRCode
						$ukuran = 2; 
						$padding = 0; 
						QRCode::png(  $dataPasien["cust_usr_kode"]."-".$dataPasien["cust_usr_nama"]."-".date_db($dataPasien["cust_usr_tanggal_lahir"]."(".$dataPasien["cust_usr_jenis_kelamin"].")"),"../pemeriksaan_lab/image.png",$quality,$ukuran,$padding);
					
						echo "<img src='../pemeriksaan_lab/image.png' width='50px'height='50px'";

					}
				}
				?>
			</td>
			<td align="left" style="text-align:left;font-size:10px;font-family:sans-serif;font-stretch:extra-expanded;letter-spacing: 1px; ">
			<b>	No RM : <?php echo $dataPasien["cust_usr_kode"]; //$kodepasien;
				?></b>
			</td>
																										
		
		</tr>

		<tr>
			
			<td align="left" style="text-align:left;font-size:10px;font-family:sans-serif;font-weight:bold;"><?php echo substr($nm_akhir[0], 0, 20) . ", " . $nm_akhir[1] .", " . $nm_akhir[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></td>		
			</tr>
		<tr>
			<td align="left" style="text-align:left;font-size:10px;font-family:sans-serif;"><b>TTL <?php echo date_db($dataPasien["cust_usr_tanggal_lahir"]); //$dataPasien["cust_usr_nama"];
				?></b></td>		
			</tr>

		

		<?php }







		else{?>


				
				<tr>
			
						<td  align="left"  style="text-align:left;font-size:10px;font-family:sans-serif;" rowspan="3">
						RSIA Muslimat Jombang
							<?php
							if ($obj) {
								if ($check_error) {
									echo "<font color='#FF0000'>" . ($obj->GetError()) . "</font>";
								} else { 

									$quality = 'H'; 
									//Ukuran besar QRCode
									$ukuran = 2; 
									$padding = 0; 
									QRCode::png(  $dataPasien["cust_usr_kode"]."-".$dataPasien["cust_usr_nama"]."-".date_db($dataPasien["cust_usr_tanggal_lahir"]."(".$dataPasien["cust_usr_jenis_kelamin"].")"),"../pemeriksaan_lab/image.png",$quality,$ukuran,$padding);
								
									echo "<img src='../pemeriksaan_lab/image.png' width='50px'height='50px'";
		
								}
							}
							?>
						</td>
						<td align="left" style="text-align:left;font-size:8px;font-family:sans-serif;font-stretch:extra-expanded;letter-spacing: 1px; ">
						<b>	No RM : <?php echo $dataPasien["cust_usr_kode"]; //$kodepasien;
							?></b>
						</td>
																													
					
					</tr>

					<tr>
										
										<td align="left" style="text-align:left;font-size:8px;font-family:sans-serif;font-weight:bold;"><?php echo substr($nm_akhir[0], 0, 20) . ", " . $nm_akhir[1] .", " . $nm_akhir[2] . ' (' . $dataPasien["cust_usr_jenis_kelamin"] . ')'; ?></td>		
										</tr>
									<tr>
										<td align="left" style="text-align:left;font-size:8px;font-family:sans-serif;"><b>TTL <?php echo date_db($dataPasien["cust_usr_tanggal_lahir"]); //$dataPasien["cust_usr_nama"];
											?></b></td>		
					</tr>


<?php } ?>


				
				</table>


				<!--</div>-->
			</td>
		
		</tr>
	</table>
</body>

</html>