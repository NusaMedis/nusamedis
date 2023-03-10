<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "expAJAX.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();

$rawat_id = $_GET['asd'];
$sql = "SELECT * from klinik.klinik_perawatan where rawat_id = '$rawat_id'";
$rawat = $dtaccess->Fetch($sql);

$tgl_rawat = $rawat['rawat_tanggal'];
$sql="SELECT * from klinik.klinik_perawatan where rawat_tanggal < '$tgl_rawat' and id_cust_usr = ".QuoteValue(DPE_CHAR, $rawat['id_cust_usr']);

$dataRawatTerakhir = $dtaccess->Fetch($sql);

$sql = "SELECT *,a.id_dokter as dokter from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join global.global_auth_user d on a.id_dokter = d.usr_id
where reg_id = ".QuoteValue(DPE_CHAR,$rawat['id_reg']);

$reg_data = $dtaccess->Fetch($sql);

$sql = "SELECT b.diagnosa_nama, diagnosa_short_desc from klinik.klinik_perawatan_diagnosa a
left join klinik.klinik_diagnosa b on a.id_diagnosa = b.diagnosa_id 
where id_rawat = '$rawat_id'";
$diagnosa = $dtaccess->FetchAll($sql);

$sql = "SELECT * from klinik.klinik_perawatan_edukasi where id_rawat = '$rawat_id'";
$edukasiAskep = $dtaccess->Fetch($sql);


$sql = "SELECT b.procedure_nama, procedure_short_desc from klinik.klinik_perawatan_procedure a
left join klinik.klinik_procedure b on a.id_procedure = b.procedure_id
where id_rawat = '$rawat_id'";
$procedure = $dtaccess->FetchAll($sql);

$sql = "SELECT a.keterangan, terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
$sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
$sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
$sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
$sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
$sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
$sql .= " WHERE id_rawat = '$rawat_id'";
$sql .= " AND id_rawat_terapi_racikan is null ";
$terapi = $dtaccess->fetchAll($sql);

$sql = "SELECT * from klinik.klinik_perawatan_terapi_racikan where id_rawat = '$rawat_id'";
$dataRacikan = $dtaccess->FetchAll($sql);

$dataPemeriksaan = unserialize($rawat['rawat_poli']);

if($rawat['rawat_pemeriksaan_fisik']){
	$pemeriksaan = $rawat['rawat_pemeriksaan_fisik'];

	$d = explode(' ; ', $pemeriksaan);
	$keadaan = array();
	for($i = 0; $i < count($d); $i++){
		$temp = explode(' : ', $d[$i]);
		$keadaan[$temp[0]] = $temp[1];
	}
}

$dataObat = explode("+", $rawat['rawat_terapi']);


$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
$rs = $dtaccess->Execute($sql);
$konfigurasi = $dtaccess->Fetch($rs);
$lokasi = $ROOT."/gambar/img_cfg";  

if ($_GET['logo']) {
  	// code...
	$konfigurasi["dep_logo"]="4451551341542358769";
}


if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;

if($konfigurasi["dep_logo"]!="n") {
	$fotoName = $lokasi."/4451551341542358769.png";
} elseif($konfigurasi["dep_logo"]=="n") { 
	$fotoName = $lokasi."/default.jpg"; 
} else { 
	$fotoName = $lokasi."/default.jpg"; 
}

$ttdPasien = $ROOT."/gambar/asset_ttd/".$rawat_id.".jpg";


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
		/* width: 800px; 
		white-space: nowrap;*/
		font-size: 12px;
	}

	@page {
		size: auto;
		/* auto is the initial value */
		margin: 0cm;

	}

	.block {
		display: inline-block;
		padding: 1px;
	}

	img{
		width: 2cm;
	}

	table{
		font-size: 12px;
		width: 100%;
	}
</style>
<script>
	window.print();
</script>
<!-- onload="setTimeout('self.close()',5000)" -->

<body>
	<div class="wrapper" style="width: 18cm; margin : auto;">
		<div style="margin-top: 20px;">
			<table style=" margin-bottom: 10px;">
				<tr>
					<td rowspan='2' width="30%">
						<center>
							<img src="<?php echo $fotoName;?>">
							<H4><?php echo $konfigurasi['dep_nama']; ?></H4>
							<?php echo $konfigurasi['dep_kop_surat_1']." Telp. ".$konfigurasi['dep_kop_surat_2']; ?>
						</center>
					</td>
					<td><h2 style="margin: 0 0 0 10px;"><strong>ASUHAN MEDIS AWAL</strong></h2></td>
				</tr>
				<tr>
					<td>
						<table style="width: 60%; margin: auto;">
							<tr>
								<td>Nama Pasien</td>
								<td> : </td>
								<td><?=$reg_data['cust_usr_nama']?></td>
							</tr>
							<tr>
								<td>Nomor RM</td>
								<td> : </td>
								<td><?=$reg_data['cust_usr_kode']?></td>
							</tr>
							<tr>
								<td>Poli Asal</td>
								<td> : </td>
								<td><?=$reg_data['poli_nama']?></td>
							</tr>
							<tr>
								<td>Tanggal Lahir</td>
								<td> : </td>
								<td><?=date_format(date_create($reg_data['cust_usr_tanggal_lahir']), 'd-m-Y')?></td>
							</tr>
							<tr>
								<td>Tanggal Registrasi</td>
								<td> : </td>
								<td><?=date_format(date_create($reg_data['reg_tanggal']), 'd-m-Y')?></td>
							</tr>
							<tr>
								<td>Waktu Registrasi</td>
								<td> : </td>
								<td><?=$reg_data['reg_waktu']?></td>
							</tr>
							<tr>
								<td>Waktu Cetak</td>
								<td> : </td>
								<td><?=$rawat['waktu_asmed']?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div style="margin-top: 10px; margin-bottom: 5px;">
			Jam mulai Assesmen Medis : <?=$rawat['waktu_mulai_asmed']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>SUBJECTIVE </strong>
		</div>
		
		<div>
			Keluhan Utama : <?=$dataPemeriksaan['keluhanUtama']?><br>
			
			
		</div>
		<div style="margin-top: 10px;">
			<strong>OBJECTIVE </strong>
		</div>
		<div>
			<?php if($keadaan['Keadaan Umum pasien']) { ?>Keadaan Umum Pasien : <?=$keadaan['Keadaan Umum pasien']?><br><?php } ?>
			
			<?php if($keadaan['Tekanan Darah Sistole']) { ?>Tekanan Darah Sistole : <?=$keadaan['Tekanan Darah Sistole']?> mm/Hg <br><?php } ?>
			<?php if($keadaan['Tekanan Darah Diastole']) { ?>Tekanan Darah Diastole : <?=$keadaan['Tekanan Darah Diastole']?> mm/Hg <br><?php } ?>
			<?php if($keadaan['Nadi']) { ?>Nadi  : <?=$keadaan['Nadi']?> x/Menit <br><?php } ?>
			<?php if($keadaan['Pernafasan']) { ?>Pernafasan : <?=$keadaan['Pernafasan']?> x/Menit <br><?php } ?>
			<?php if($keadaan['Suhu']) { ?>Suhu Badan : <?=$keadaan['Suhu']?> ??C <br><?php } ?>
			<?php if($keadaan['Tinggi Badan']) { ?>Tinggi Badan : <?=$keadaan['Tinggi Badan']?> Cm <br><?php } ?>
			<?php if($keadaan['Berat Badan']) { ?>Berat Badan : <?=$keadaan['Berat Badan']?> Kg <br><?php } ?>
			<?php if($keadaan['Saturasi']) { ?>Saturasi Oksigen : <?=$keadaan['Saturasi']?> % <br><?php } ?>
			
			
			

		</div>
		
		<?php if($dataPemeriksaan['pemeriksaanPenunjang']) { ?>
			<div style="margin-top: 10px;">
				<strong>Lain - lain : </strong>
			</div>
			<div>
				<div class="block">

					<?php
					echo $dataPemeriksaan['pemeriksaanPenunjang'];
					?>
				</div>
			</div>
		<?php } ?>
		<?php if($dataPemeriksaan['status_lokalis']) { ?>
			<div style="margin-top: 10px;">
				<strong>Status Lokalis : </strong>
			</div>
			<div>
				<div class="block">

					<?php
					echo $dataPemeriksaan['status_lokalis'];
					?>
				</div>
			</div>
		<?php } ?>
		<!-- <div style="margin-top: 10px;">
			<strong>ANALISA : </strong>
		</div>
		<div style="margin-top: 7px">
			<br><?=$dataPemeriksaan['ket_diagnosa_empat']?>
		</div> -->
		<div style="margin-top: 10px;">
			<strong>DIAGNOSA : </strong>
		</div>
		<div>
			<ul style="list-style-type: none;margin: 0 0 0 -20px">
				<?php
				for($i = 0; $i < count($diagnosa); $i++){
					?>
					<li><?=$diagnosa[$i]['diagnosa_short_desc']?></li>
					<?php
				}
				?>
				<?php if($dataPemeriksaan['diagnose_skr']) {?>
					<li><?=$dataPemeriksaan['diagnose_skr']?></li>
				<?php }?>
			</ul>
		</div>
		<div style="margin-top: 10px;">
			<strong>PLANNING : </strong>
		</div>
		<div>
			<ul style="list-style-type: none;margin: 0 0 0 -20px">
				<?php
				for($i = 0; $i < count($procedure); $i++){
					?>
					<li><?=$procedure[$i]['procedure_short_desc']?></li>
					<?php
				}
				?>
				<li><?=$dataPemeriksaan['planning_penatalaksanaan']?></li>
			</ul>
		</div>

		

		<div style="margin-top: 10px;">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Terapi : 
		</div>
		<div>
			<ol >
				<?php for($i = 0; $i < count($dataObat); $i++){ 
					preg_match('#\((.*?)\)#', $dataObat[$i], $match);

					$nomor = $match[1];
					$ingg = str_replace($match[0], "", $dataObat[$i]);

					$ingridients = explode(",", $ingg);
					$nomora = explode(",", $nomor);
					?>
					<?php if($nomor) { ?>
						<li style="margin-bottom: 10px">
							<ul style="list-style-type: none;">
								<?php for($a = 0; $a < count($ingridients); $a++) { ?>
									<li style="margin-left: -20px;"><?=$ingridients[$a]?></li>
								<?php }?>
							</ul>

							<?=$nomora[0]?> <br>
							<?=$nomora[1]?>
						</li>
						
					<?php } 
					else { ?>
						<?php if($ingridients[0] != '') { ?>
							<li><?=$ingridients[0]?></li>
						<?php }?>
					<?php }?>
				<?php }?>

				<?php
				for($i = 0; $i < count($dataRacikan); $i++){
					?>
					<li>
						<?php
						$rawat_racikan = $dataRacikan[$i]['rawat_terapi_racikan_id'];
						$sql = "SELECT a.keterangan, terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
						$sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
						$sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
						$sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
						$sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
						$sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
						$sql .= " WHERE id_rawat_terapi_racikan = '$rawat_racikan'";
						$terapiRacikitem = $dtaccess->fetchAll($sql);
						?>
						<ul style="list-style-type: none;">
							<?php
							for($s=0; $s < count($terapiRacikitem); $s++){
								?>
								<li style="margin-left: -20px;"><?=$terapiRacikitem[$s]['item_nama']?> <?=$terapiRacikitem[$s]['terapi_jumlah_item']?></li>
								<?php
							}
							?>
						</ul>
						<?=$dataRacikan[$i]['jenis_racikan_nama']?> <?=$dataRacikan[$i]['rawat_terapi_racikan_jumlah']?> <?=$dataRacikan[$i]['satuan_nama']?> <?=$dataRacikan[$i]['petunjuk_nama']?> <?=$dataRacikan[$i]['aturan_pakai_nama']?> </li>
						<?php
					}
					?>

					<?php
					for($i = 0; $i < count($terapi); $i++){
						?>
						<li><?=$terapi[$i]['item_nama']?> <?=$terapi[$i]['terapi_jumlah_item']?>,  <?=$terapi[$i]['petunjuk_nama']?>, <?=$terapi[$i]['aturan_minum_nama']?>, <?=$terapi[$i]['aturan_pakai_nama']?>, <?=$terapi[$i]['jam_aturan_pakai_nama']?>, <?=$terapi[$i]['keterangan']?></li>
						<?php
					}
					?>
				</ol>

			</div>

			<?php if($dataPemeriksaan['diagnosa'] || $dataPemeriksaan['penjelasan_penyakit'] || $dataPemeriksaan['pemeriksaan_penunjang'] || $dataPemeriksaan['terapi_edukasi'] || $dataPemeriksaan['tindakan_medis'] || $dataPemeriksaan['prognosa'] || $dataPemeriksaan['perkiraan_hari_rawat'] || $dataPemeriksaan['penjelasan_komplikasi'] || $dataPemeriksaan['informed_concent'] || $dataPemeriksaan['kondisi'] || $dataPemeriksaan['konsul'] || $_GET['edukasi_pulang'] || $dataPemeriksaan['edukasi_lain'] ){ ?>

				<div style="margin-top: 10px;display: flex">
					<div style="width: 50%; ">
						<div >
							<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi : </strong>
						</div>
						<div>
							<ol>
								<?php if($dataPemeriksaan['diagnosa']){?><li>Diagnosa</li><?php }?>
								<?php if($dataPemeriksaan['penjelasan_penyakit']){?><li>Penjelasan penyakit (penyebab, tanda, gejala)</li><?php }?>
								<?php if($dataPemeriksaan['pemeriksaan_penunjang']){?><li>Pemeriksaan Penunjang</li><?php }?>
								<?php if($dataPemeriksaan['terapi_edukasi']){?><li>Terapi / terapi alternative</li><?php }?>
								<?php if($dataPemeriksaan['tindakan_medis']){?><li>Tindakan Medis</li><?php }?>
								<?php if($dataPemeriksaan['prognosa']){?><li>Prognosa</li><?php }?>
								<?php if($dataPemeriksaan['perkiraan_hari_rawat']){?><li>Perkiraan Hari Rawat</li><?php }?>
								<?php if($dataPemeriksaan['penjelasan_komplikasi']){?><li>Penjelasan komplikasi / resiko yang mungkin terjadi</li><?php }?>
								<?php if($dataPemeriksaan['informed_concent']){?><li>Edukasi pengambilan informed concent</li><?php }?>
								<?php if($dataPemeriksaan['kondisi']){?><li>Kondisi kesehatan saat ini</li><?php }?>
								<?php if($dataPemeriksaan['konsul']){?><li>Konsul ke : <?= $dataPemeriksaan['konsul_det']?></li><?php }?>
								<?php if($dataPemeriksaan['edukasi_pulang']){?><li>Edukasi sebelum pulang</li><?php }?>
								<?php if($dataPemeriksaan['edukasi_lain']){?><li>Lain lain : <?=$dataPemeriksaan['lain_det']?></li><?php }?>
							</ol>
						</div>
					</div>
					<div style="width: 50%;">
						<div >
							<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Evaluasi Edukasi : </strong>
						</div>
						<div>
							<ol>
								<?php if ($dataPemeriksaan['memahamiMateri']) {  ?><li>&nbsp;Memahami Materi</li><?php }?>
								<?php if ($dataPemeriksaan['bisaMengulang']) {  ?><li>&nbsp;Bisa Mengulang Materi</li><?php }?>
								<?php if ($dataPemeriksaan['membatasiMateri']) {  ?><li>&nbsp;Membatasi Materi</li><?php }?>
								<?php if ($dataPemeriksaan['pengulanganMateri']) {  ?><li>&nbsp;Butuh Pengulangan Materi</li><?php }?>
								<?php if ($dataPemeriksaan['butuhLeaflet']) {  ?><li>&nbsp;Butuh Leaflet</li><?php }?>

								<?php if ($dataPemeriksaan['lain_lainEdukasi']) {  ?><li>&nbsp;Lainnya : </li>

								<?php if ($dataPemeriksaan["lainEd_det"]) { ?> - <?php echo $dataPemeriksaan['lainEd_det']; ?><br><?php }?>
							<?php }?>
						</ol>
					</div>
				</div>
			</div>
		<?php }?>
		
		<div>
			<table width="100%">
				<tr>
					<td width="50%">
						<center>
							<br>
							<br>
							Pasien
							<br>
							<?php if(file_exists($ttdPasien)) {?>
								<img style="width: 120px; height: 60px;" src="<?=$ttdPasien?>">
							<?php } 
							else { ?>
								<br>
								<br>
								<br>
							<?php }?>
							<br>
							<?= str_replace("*", "'", $rawat['rawat_nama_ttd'])?>
							<br>
							( <?= str_replace("*", "'", $reg_data['cust_usr_nama'])?> )


						</center>
					</td>
					<td>
						<center>

							Jombang, <?=date_format(date_create($reg_data['reg_tanggal_pulang']), "d-m-Y")?><br>
							Dokter Penanggung Jawab (DPJP)
							<br>
							<img height="60" src="../gambar/asset_ttd/<?=$reg_data['dokter']?>.jpg">
							<br>


							 <?=$reg_data['usr_name']?> 
						</center>
					</td>
				</tr>
			</table>

			<div style="width: 100%; position: relative;">
				<!-- <p style="float: right;">Waktu Cetak : <?=date("d-m-Y H:i:s")?></p> -->
			</div>
		</div>
	</div>
	

</body>

</html>