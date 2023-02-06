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

$sql = "SELECT * from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id_reg']);
$dataRawat = $dtaccess->Fetch($sql);

$tgl_rawat = $dataRawat['rawat_tanggal'];
$sql="SELECT * from klinik.klinik_perawatan a 
left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
left join global.global_auth_poli c on b.id_poli = c.poli_id
where poli_tipe = 'J' and rawat_tanggal < '$tgl_rawat' and a.id_cust_usr = ".QuoteValue(DPE_CHAR, $dataRawat['id_cust_usr']);

$dataRawatTerakhir = $dtaccess->Fetch($sql);

$rawat_id = $dataRawat['rawat_id'];
$sql = "SELECT id_reg, id_cust_usr from klinik.klinik_perawatan where rawat_id = '$rawat_id'";
$rawat = $dtaccess->Fetch($sql);

$sql = "SELECT *, e.waktu_asmed from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join global.global_auth_user d on a.id_dokter = d.usr_id
left join klinik.klinik_perawatan e on a.reg_id = e.id_reg
 where reg_id = ".QuoteValue(DPE_CHAR,$rawat['id_reg']);
$reg_data = $dtaccess->Fetch($sql);

$waktu_tglSekarang = $reg_data['reg_when_update'];

$sql = "select a.reg_id,b.rawat_id from klinik.klinik_registrasi a left join klinik.klinik_perawatan b on b.id_reg = a.reg_id where a.id_cust_usr = " . QuoteValue(DPE_CHAR, $reg_data["id_cust_usr"]) . " and a.reg_tipe_rawat = 'J' and a.id_poli <> '33' and reg_when_update <> '$waktu_tglSekarang' order by reg_tanggal desc limit 1 ";
$dtRegTerakhir = $dtaccess->Fetch($sql);

$sql_hpht = "SELECT anamnesa_isi_detail_nilai FROM klinik.klinik_anamnesa_isi_detail WHERE id_rawat = " . QuoteValue(DPE_CHAR, $dtRegTerakhir['rawat_id']) . " AND id_anamnesa = '5d2c31995c9ef8fbad77eaee2abc6ecd' AND id_anamnesa_detail = '50e70246c321cc4b0309bb9f5203fd42' and anamnesa_isi_detail_nilai not like '%Hari%' and anamnesa_isi_detail_nilai not like '% - %'";
$dataHpht = $dtaccess->Fetch($sql_hpht);

$_GET['hpht'] = $dataHpht['anamnesa_isi_detail_nilai'];

if ($dataHpht['anamnesa_isi_detail_nilai'] <> null) {
    $_GET['hpl'] = date('Y-m-d', strtotime('+279 days', strtotime(date_db($dataHpht['anamnesa_isi_detail_nilai']))));
  } else {
    $_GET['hpl'] = '';
  }

$sql = "SELECT b.biaya_nama from klinik.klinik_folio a
		left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
		where id_pembayaran = ".QuoteValue(DPE_CHAR,$reg_data['id_pembayaran']);
$dataFolio = $dtaccess->FetchAll($sql);

$sql = "SELECT terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
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

$sql = "SELECT * from klinik.klinik_anamnesa_tb where id_anamnesa = '9dafa78dca4a01f50d21fbc884a5eecb' AND id_reg=".QuoteValue(DPE_CHAR,$rawat['id_reg'])." order by when_create asc";
$dataAnamnesa = $dtaccess->FetchAll($sql);

$sql = "SELECT * from klinik.klinik_anamnesa_pilihan where id_anamnesa = '9dafa78dca4a01f50d21fbc884a5eecb' order by anamnesa_pilihan_urut asc, anamnesa_pilihan_id asc";
$field = $dtaccess->FetchAll($sql);

$sql_cek = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = '$rawat_id' AND id_anamnesa = 'TIPE LAYANAN'";
$dataTipeLayanan = $dtaccess->Fetch($sql_cek);

$sql = "SELECT anamnesa_isi_nilai from klinik.klinik_anamnesa_isi where id_rawat = '$rawat_id' AND id_anamnesa = '45e22934c1543643b4d49eb6c5cb09ee'";
$dataGPA = $dtaccess->Fetch($sql);

$GPA = explode(";", $dataGPA['anamnesa_isi_nilai']);

$sql = "SELECT * from klinik.klinik_anamnesa_isi_detail where id_rawat = '$rawat_id' and id_anamnesa_detail = '8c58848aae6b61fc5c7f70e4659ebbe5'";
$G = $dtaccess->Fetch($sql);

$sql = "SELECT * from klinik.klinik_anamnesa_isi_detail where id_rawat = '$rawat_id' and id_anamnesa_detail = '3275c22d4d0c8e008b5deb78d68ba116'";
$P = $dtaccess->Fetch($sql);

$sql = "SELECT * from klinik.klinik_anamnesa_isi_detail where id_rawat = '$rawat_id' and id_anamnesa_detail = '86b6648a00e26029a0949b88a6bebf2d'";
$A = $dtaccess->Fetch($sql);


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

$rspn = array();

    foreach ($dataAnamnesa as $rs) {
        $row['anamnesa_tb_id'] = $rs['anamnesa_tb_id'];
        $row['reg_id'] = $rs['id_reg'];
        $row['rawat_id'] = $rs['id_rawat'];
        $row['poli_id'] = $rs['id_poli'];
        $row['anamnesa_id'] = $rs['id_anamnesa'];
        
        $a = unserialize($rs['anamnesa_tb_isi']);
        foreach ($a as $aa) {
            $row[ $aa['field'] ] = $aa['value'];
        }
        $rspn[] = $row;
	}



	$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";  

  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;

  if($konfigurasi["dep_logo"]!="n") {
    $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
    $fotoName = $lokasi."/default.jpg"; 
  } else { 
    $fotoName = $lokasi."/default.jpg"; 
  }

  $fsk = explode(' ; ',$dataRawat['rawat_pemeriksaan_fisik']);
  $fisik = array();
  for($i = 0; $i < count($fsk); $i++){
	  $sprt = explode(' : ', $fsk[$i]);
	  $fisik[$sprt[0]] = $sprt[1];
  }

  $_GET['keluhanUtama'] = $dataRawat['rawat_anamnesa'];
  $pmrksaanObgyn = unserialize($dataRawat['rawat_obgyn']);
  $pmrksaanObgynl = unserialize($dataRawatTerakhir['rawat_obgyn']);
  $ttdPasien = $ROOT."/gambar/asset_ttd/".$rawat_id.".jpg";
	
?>
<style>
	body{
		white-space: unset !important;
	}
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
					<td><h2 style="margin: 0 0 0 10px;"><strong><?=($dataRawatTerakhir['rawat_id']) ? "ASUHAN MEDIS LANJUTAN" : "ASUHAN MEDIS AWAL" ?></strong></h2></td>
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
								<td><?=$reg_data['waktu_asmed']?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div style="margin-top: 10px; margin-bottom: 5px;">
			Jam mulai Assesmen Medis : <?=$dataRawat['waktu_mulai_asmed']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>SUBJECTIVE </strong>
		</div>
		<?php if($dataTipeLayanan['anamnesa_isi_nilai'] == '0bstetri') { ?>
		<div >
			Keluhan Utama : <?=$_GET['keluhanUtama']?><br>
			<?php //if($pmrksaanObgyn['select_keluhan_utama'] || $pmrksaanObgyn['mual'] || $pmrksaanObgyn['perut_sakit'] || $pmrksaanObgyn['pusing'] || $pmrksaanObgyn['muntah']) { ?>
			<?php //if($pmrksaanObgyn['select_keluhan_utama']) {?>
			<!-- <?=$pmrksaanObgyn['select_keluhan_utama']?> <?=($pmrksaanObgyn['pendarahan_sedikit']) ? "Sedikit": ""?> <?=($pmrksaanObgyn['pendarahan_banyak']) ? "Banyak": ""?> selama <?=$pmrksaanObgyn['keluhan_utama_bulan']?> -->
			<?php //}?>
			 <!-- dengan rasa <?=($pmrksaanObgyn['mual']) ? "Mual,": ""?> <?=($pmrksaanObgyn['perut_sakit']) ? "Perut Sakit,": ""?> <?=($pmrksaanObgyn['pusing']) ? "Pusing,": ""?> <?=($pmrksaanObgyn['muntah']) ? "Muntah,": ""?><br> -->

			<?php //} ?>
			<?php if($pmrksaanObgyn['hpht']) { ?>Hpht  : <?=$pmrksaanObgyn['hpht']?><br><?php } ?>
			<?php if($pmrksaanObgyn['hpl']) { ?>Hpl : <?=$pmrksaanObgyn['hpl']?><br><?php } ?>
			 Riwayat kehamilan & persalinan yang lalu : 
			 <table border="1" style="border-collapse: collapse;">
				<thead>
					<tr>
						<th>Nomor</th>
					<?php for($i = 0; $i < count($field); $i++){ ?>
						<th><?=$field[$i]['anamnesa_pilihan_nama']?></th>
					<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php for($i = 0; $i < count($rspn); $i++){ 
						
						?>
						<tr>
							<td style="text-align: center"><?=$i+1?></td>
						<?php for($s = 0; $s < count($field); $s++){ ?>
							<td><?=$rspn[$i][$field[$s]['anamnesa_pilihan_id']]?></td>
						<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<div style="margin-top: 10px;">
			<strong>OBJECTIVE </strong>
		</div>
		<div>
			<?php if($fisik['Keadaan Umum Pasien']) { ?>Keadaan Umum Pasien : <?=$fisik['Keadaan Umum Pasien']?><br><?php } ?>
			<?php if($fisik['Kesadaran']) { ?>Kesadaran : <?=$fisik['Kesadaran']?><br><?php } ?>
			<?php if($fisik['Tekanan Darah Sistole']) { ?>Tekanan Darah Sistole : <?=$fisik['Tekanan Darah Sistole']?> mm/Hg <br><?php } ?>
			<?php if($fisik['Tekanan Darah Diastole']) { ?>Tekanan Darah Diastole : <?=$fisik['Tekanan Darah Diastole']?> mm/Hg <br><?php } ?>
			<?php if($fisik['Nadi']) { ?>Nadi  : <?=$fisik['Nadi']?> x/Menit <br><?php } ?>
			<?php if($fisik['Pernafasan']) { ?>Pernafasan : <?=$fisik['Pernafasan']?> x/Menit <br><?php } ?>
			<?php if($fisik['Suhu']) { ?>Suhu Badan : <?=$fisik['Suhu']?> °C <br><?php } ?>
			<?php if($fisik['Tinggi Badan']) { ?>Tinggi Badan : <?=$fisik['Tinggi Badan']?> Cm <br><?php } ?>
			<?php if($fisik['Berat Badan']) { ?>Berat Badan : <?=$fisik['Berat Badan']?> Kg <br><?php } ?>
			<?php if($fisik['leher_tvj']) { ?>Leher (TVJ) : <?=$fisik['leher_tvj']?> Cm <br><?php } ?>
			<br>
			<?php if($fisik['Mata']) { ?>Mata : <?=$fisik['Mata']?><br><?php } ?>
			<?php if($fisik['Leher']) { ?>Leher : <?=$fisik['Leher']?><br><?php } ?>
			<?php if($fisik['Payudara']) { ?>Payudara : <?=$fisik['Payudara']?><br><?php } ?>
			<br>
			<?php if($fisik['Letak Anak'] ) { ?>Letak Anak : <?=$fisik['Letak Anak']?><?php } ?>
			

		</div>
		<div style="margin-top: 10px;">
			<strong>USG : </strong>
		</div>
		<?php if ($pmrksaanObgyn['gs0'] || $pmrksaanObgyn['gs1'] || $pmrksaanObgyn['gs2'] || $pmrksaanObgyn['fetal_pool'] || $pmrksaanObgyn['fetus'] || $pmrksaanObgyn['crl'] || $pmrksaanObgyn['djj'] || $pmrksaanObgyn['usia_kehamilan_minggu'] || $pmrksaanObgyn['usia_kehamilan_hari'] || $pmrksaanObgyn['hpl_muda']) : ?>

<?php  
	if ($pmrksaanObgyn['gs1'] == '1') {
		$pmrksaanObgyn['gs1'] = 'Tunggal';
	}elseif ($pmrksaanObgyn['gs1'] == '2') {
		$pmrksaanObgyn['gs1'] = 'Kembar';
	}
?>
	<h5>HAMIL MUDA (TRIMESTER I)</h5>
	<div style="margin-top: -15px;">
		<div class="block">
			GS : <?php if ($pmrksaanObgyn['gs0']) { ?>
				<?= $pmrksaanObgyn['gs0'] ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['gs1']) { ?>
				<?= $pmrksaanObgyn['gs1'] ?>
			<?php } ?>
			
			<?php if ($pmrksaanObgyn['gs2']) { ?>
				<?= $pmrksaanObgyn['gs2'] ?> mm
			<?php } ?>
			&nbsp;
			<?php if ($pmrksaanObgyn['fetal_pool']) { ?>
				Fetal Pool
				:
				<?= $pmrksaanObgyn['fetal_pool'] ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['fetus']) { ?>
				Fetus
				:
				<?= $pmrksaanObgyn['fetus'] ?>
			<?php } ?>
		</div>
	</div>
	<div>
		<div class="block">
			
			<?php if ($pmrksaanObgyn['crl']) {
			echo "CRL"; ?>
				<?= $pmrksaanObgyn['crl'] ?>
				mm
			<?php } ?>
			<?php if ($pmrksaanObgyn['djj']) { ?>
				&nbsp; DJJ
				:
				<?= $pmrksaanObgyn['djj'] ?>
			<?php } ?>
			&nbsp;

			<?php if ($pmrksaanObgyn['usia_kehamilan_minggu']) {
			echo "Usia Hamil"; ?>
				<?= $pmrksaanObgyn['usia_kehamilan_minggu'] ?>
				Minggu
			<?php } ?>
			<?php if ($pmrksaanObgyn['usia_kehamilan_hari']) { ?>
				<?= $pmrksaanObgyn['usia_kehamilan_hari'] ?>
				Hari
			<?php } ?>
			<?php if ($pmrksaanObgyn['hpl_muda']) { ?>
				&nbsp;HPL
				<?= $pmrksaanObgyn['hpl_muda'] ?>
			<?php } ?>
		</div>
	</div>
<?php endif; ?>
<?php if (
	$pmrksaanObgyn['janin_tunggal'] ||
	$pmrksaanObgyn['janin_kembar'] ||
	$pmrksaanObgyn['janin_hidup'] ||
	$pmrksaanObgyn['janin_iufd'] ||
	$pmrksaanObgyn['letak_janin_kepala'] ||
	$pmrksaanObgyn['letak_janin_sungsang'] ||
	$pmrksaanObgyn['letak_janin_melintang'] ||
	$pmrksaanObgyn['letak_janin_oblique'] ||
	$pmrksaanObgyn['bpd'] ||
	$pmrksaanObgyn['fl'] ||
	$pmrksaanObgyn['ac'] ||
	$pmrksaanObgyn['efw'] ||
	$pmrksaanObgyn['insersi_fudus'] ||
	$pmrksaanObgyn['insersi_corpus'] ||
	$pmrksaanObgyn['insersi_sbr'] ||
	$pmrksaanObgyn['insersi_anterior'] ||
	$pmrksaanObgyn['insersi_posterior'] ||
	$pmrksaanObgyn['grade'] ||
	$pmrksaanObgyn['ketuban_cukup'] ||
	$pmrksaanObgyn['ketuban_kurang'] ||
	$pmrksaanObgyn['ketuban_banyak'] ||
	$pmrksaanObgyn['afi'] ||
	$pmrksaanObgyn['usia_kehamilan_minggu1'] ||
	$pmrksaanObgyn['usia_kehamilan_hari1'] ||
	$pmrksaanObgyn['hpltp'] ||
	$pmrksaanObgyn['jenis_kelamin']
	
) : ?>


	<h5>HAMIL TRIMESTER II-III</h5>
	<?php if($pmrksaanObgyn['janin_tunggal'] || $pmrksaanObgyn['janin_kembar'] || $pmrksaanObgyn['janin_hidup'] || $pmrksaanObgyn['janin_iufd']) { ?>
	<div style="margin-top: -15px;">
		<div class="block"> Janin &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : <?= ($pmrksaanObgyn['janin_tunggal'] == 'true') ? 'Tunggal;' : '' ?>
			<?= ($pmrksaanObgyn['janin_kembar'] == 'true') ? 'Kembar;' : '' ?>
			<?= ($pmrksaanObgyn['janin_hidup'] == 'true') ? 'Hidup;' : '' ?>
			<?= ($pmrksaanObgyn['janin_iufd'] == 'true') ? 'IUFD;' : '' ?>
		</div>
	</div>
<?php }?>
	<?php if($pmrksaanObgyn['letak_janin_kepala'] || $pmrksaanObgyn['letak_janin_sungsang'] || $pmrksaanObgyn['letak_janin_melintang'] || $pmrksaanObgyn['letak_janin_oblique']) { ?>
	<div>
		<div class="block">
			Letak Janin : <?= ($pmrksaanObgyn['letak_janin_kepala'] == 'true') ? 'Kepala;' : '' ?>
			<?= ($pmrksaanObgyn['letak_janin_sungsang'] == 'true') ? 'Sungsang;' : '' ?>
			<?= ($pmrksaanObgyn['letak_janin_melintang'] == 'true') ? 'Melintang;' : '' ?>
			<?= ($pmrksaanObgyn['letak_janin_oblique'] == 'true') ? 'Oblique;' : '' ?>

		</div>
	</div>
<?php }?>
	<?php if ($pmrksaanObgyn['bpd'] != '' || $pmrksaanObgyn['fi'] != '' || $pmrksaanObgyn['ac'] != '' || $pmrksaanObgyn['efw'] != '') { ?>
	<div>
		<div class="block">
			BPD &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;:
			<?php if ($pmrksaanObgyn['bpd']) { ?>
				&nbsp;<?= $pmrksaanObgyn['bpd'] ?> cm
			<?php } ?>
			<?php if ($pmrksaanObgyn['fl']) { ?>
				&nbsp; FL
				<?= $pmrksaanObgyn['fl'] ?> mm
			<?php } ?>
			<?php if ($pmrksaanObgyn['ac']) { ?>
				&nbsp;AC
				<?= $pmrksaanObgyn['ac'] ?> mm
			<?php } ?>
			<?php if ($pmrksaanObgyn['efw']) { ?>
				&nbsp;EFW
				<?= $pmrksaanObgyn['efw'] ?> gram
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<?php if($pmrksaanObgyn['insersi_fudus'] || $pmrksaanObgyn['insersi_corpus'] || $pmrksaanObgyn['insersi_sbr'] || $pmrksaanObgyn['insersi_anterior'] || $pmrksaanObgyn['insersi_posterior'] || $pmrksaanObgyn['grade']) { ?>
	<div>
		<div class="block">
			Plasenta &nbsp;&nbsp; :
			<?php if ($pmrksaanObgyn['insersi_fudus']) { ?>
				<?= ($pmrksaanObgyn['insersi_fudus'] == 'true') ? 'Fundus;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['insersi_corpus']) { ?>
				<?= ($pmrksaanObgyn['insersi_corpus'] == 'true') ? 'Corpus;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['insersi_sbr']) { ?>
				<?= ($pmrksaanObgyn['insersi_sbr'] == 'true') ? 'SBR;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['insersi_anterior']) { ?>
				<?= ($pmrksaanObgyn['insersi_anterior'] == 'true') ? 'Ant;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['insersi_posterior']) { ?>
				<?= ($pmrksaanObgyn['insersi_posterior'] == 'true') ? 'Post;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['grade']) { ?>
				&nbsp; Grade : <?= $pmrksaanObgyn['grade'] ?>
			<?php } ?>
		</div>
	</div>
	<?php }?>
	<?php if($pmrksaanObgyn['ketuban_cukup'] || $pmrksaanObgyn['ketubah_cukup'] || $pmrksaanObgyn['ketuban_kurang'] || $pmrksaanObgyn['ketuban_banyak'] || $pmrksaanObgyn['afi']) { ?>
	<div>
		<div class="block">
			Ketuban &nbsp;&nbsp; &nbsp; :
			<?php if ($pmrksaanObgyn['ketuban_cukup'] || $pmrksaanObgyn['ketubah_cukup']) { ?>
				<?= ($pmrksaanObgyn['ketuban_cukup'] == 'true' || $pmrksaanObgyn['ketubah_cukup'] == 'true') ? 'Cukup;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['ketuban_kurang']) { ?>
				<?= ($pmrksaanObgyn['ketuban_kurang'] == 'true') ? 'Kurang;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['ketuban_banyak']) { ?>
				<?= ($pmrksaanObgyn['ketuban_banyak'] == 'true') ? 'Banyak;' : '' ?>
			<?php } ?>
			<?php if ($pmrksaanObgyn['afi']) { ?>
				&nbsp;AFI
				<?= $pmrksaanObgyn['afi'] ?>
			<?php } ?>
		</div>
	</div>
	<?php }?>
	<?php if($pmrksaanObgyn['usia_kehamilan_minggu1'] || $pmrksaanObgyn['usia_kehamilan_hari1'] || $pmrksaanObgyn['hpltp']) { ?>
	<div>
		<div class="block">
			Usia Hamil &nbsp;:
			<?php if ($pmrksaanObgyn['usia_kehamilan_minggu1']) { ?>
				<?= $pmrksaanObgyn['usia_kehamilan_minggu1'] ?>
				mgg
			<?php } ?>
			<?php if ($pmrksaanObgyn['usia_kehamilan_hari1']) { ?>
				<?= $pmrksaanObgyn['usia_kehamilan_hari1'] ?>
				Hr
			<?php } ?>
			<?php if ($pmrksaanObgyn['hpltp']) { ?>
				&nbsp;
				HPL :
				<?= $pmrksaanObgyn['hpltp'] ?>
			<?php } ?>

		</div>
	</div>
	<?php }?>
	<?php if($pmrksaanObgyn['jenis_kelamin']) {?>
	<div>
		<div class="block">
			Kelamin &nbsp; &nbsp; &nbsp;:
			<?php
				if ($pmrksaanObgyn['jenis_kelamin'] == 'Laki') {
					echo "Laki - laki";
				}elseif ($pmrksaanObgyn['jenis_kelamin'] == 'Perempuan') {
					echo "Perempuan";
				}else{
					echo "&nbsp;";
				}
			?>
		</div>
	</div>
<?php }?>
	
<?php endif;
		?>
		<?php if($pmrksaanObgyn['pemeriksaanPenunjang']) { ?>
		<div>
			<div class="block">
				Pemeriksaan Penunjang (Laboratorium, Radiologi)&nbsp; &nbsp; &nbsp;:<br>
				<?php
					echo $pmrksaanObgyn['pemeriksaanPenunjang'];
				?>

			</div>
		</div>
		<?php } ?>
		<?php if($pmrksaanObgyn['status_lokalis']) { ?>
		<div>
			<div class="block">
				Status Lokalis &nbsp; &nbsp; &nbsp;:<br>
				<?php
					echo $pmrksaanObgyn['status_lokalis'];
				?>
			</div>
		</div>
		<?php } ?>
		<div style="margin-top: 10px;">
			<strong>ANALISA: </strong>
		</div>
		<div style="margin-top: 7px">
			<?php if(array_search("G",$GPA) != '') { ?>
				G : <?=($G['anamnesa_isi_detail_nilai']) ? $G['anamnesa_isi_detail_nilai'] : '0'//$pmrksaanObgyn['g_analisa']?> 
			<?php }?>
			<?php if(array_search("P",$GPA) != '') { ?>
			P : <?=($P['anamnesa_isi_detail_nilai']) ? $P['anamnesa_isi_detail_nilai'] : '0'//$pmrksaanObgyn['p_analisa']?> 
			<?php }?>
			<?php if(array_search("A",$GPA) != '') { ?>
			A : <?=($A['anamnesa_isi_detail_nilai']) ? $A['anamnesa_isi_detail_nilai'] : '0'//$pmrksaanObgyn['a_analisa']?> , 
			<?php }?>

			<?=" / ".$pmrksaanObgyn['ket_diagnosa_satu']?>  <?=($pmrksaanObgyn['ket_diagnosa_dua'] == 'T' || $pmrksaanObgyn['janin_tunggal'] == 'true') ? " / Tunggal" : ""?> <?=($pmrksaanObgyn['ket_diagnosa_dua'] == 'G' || $pmrksaanObgyn['janin_kembar'] == 'true') ? " / Gemelli" : ""?> <?=" / ".$pmrksaanObgyn['ket_diagnosa_tiga']?>  <?=($pmrksaanObgyn['ket_diagnosa_lima']) ? " / ".$pmrksaanObgyn['ket_diagnosa_lima'] : ""?> <?=($pmrksaanObgyn['letak_janin_kepala'] == 'true') ? " / Kepala " : ""?> <?=($pmrksaanObgyn['letak_janin_sungsang'] == 'true') ? " / Sungsang " : ""?> <?=($pmrksaanObgyn['letak_janin_melintang'] == 'true') ? " / Melintang " : ""?> <?=($pmrksaanObgyn['letak_janin_oblique'] == 'true') ? " / Oblique " : ""?> 
			<br><?php if($pmrksaanObgyn['ket_diagnosa_empat']) { ?> - <?=$pmrksaanObgyn['ket_diagnosa_empat']?><?php }?>
		</div>
		<div style="margin-top: 10px;">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DIAGNOSA : 
		</div>
		<div>
		<ol style="margin: 0px">
			<?php
				for($i = 0; $i < count($diagnosa); $i++){
					?>
					<li><?=$diagnosa[$i]['diagnosa_short_desc']?></li>
					<?php
				}
			?>
			</ol>
		</div>
		<div style="margin-top: 10px;">
			<strong>PLANNING : </strong>
		</div>
		<div>
			<ol>
			<?php
				for($i = 0; $i < count($procedure); $i++){
					?>
					<li><?=$procedure[$i]['procedure_short_desc']?></li>
					<?php
				}
			?>
			<?php if($pmrksaanObgyn['planning_penatalaksanaan']) { ?><li><?=$pmrksaanObgyn['planning_penatalaksanaan']?></li><?php }?>
			</ol>
		</div>
<?php
}
	else if($dataTipeLayanan['anamnesa_isi_nilai'] == 'Ginekology' ){
?>
		<div>
			Keluhan Utama : <?=$pmrksaanObgyn['keluhan_utama']?><br>
			<?php if( $pmrksaanObgyn['haid_teratur_ya'] || $pmrksaanObgyn['haid_teratur_tidak']) { ?>
			<?=($pmrksaanObgyn['haid_teratur_ya']) ? "Haid Teratur" : "" ?> <?=($pmrksaanObgyn['haid_teratur_tidak']) ? "Haid Tidak Teratur" : "" ?> selama <?=$pmrksaanObgyn['lamanya_haid_teratur']?> hari, dengan rasa <?=($pmrksaanObgyn['haid_nyeri'] == 'y') ? "Nyeri" : "Tidak Nyeri"?>
			<?php } ?>
			<br>
			<?php if($pmrksaanObgyn['amenore_ya'] || $pmrksaanObgyn['menopause']) { ?>
			Gangguan Haid : <?php if($pmrksaanObgyn['amenore_ya']) { ?> <?=($pmrksaanObgyn['amenore_ya']) ? "Amenore" : "" ?> selama <?=($pmrksaanObgyn['amenore_hari']) ? $pmrksaanObgyn['amenore_hari'] : "" ?> hari<?php } ?> <?php if($pmrksaanObgyn['menopause']) { ?> <?=$pmrksaanObgyn['menopause']?> <?php } ?>
			<?php } ?>
			<br>
			Haid Selama : <?=$pmrksaanObgyn['haid_lama_hari']?> Hari 
			<br>
			Haid lama dan banyak : <?=$pmrksaanObgyn['haid_lama_banyak_hari']?> Hari
			<br>
			Haid selama 1 bulan : <?=$pmrksaanObgyn['haid_lama_bulan']?> Kali
			<br>
			Lama haid terus menerus : <?=$pmrksaanObgyn['terus_menerus_lama']?> Hari
			<br>
			<?php if(($pmrksaanObgyn['sedikit'] || $pmrksaanObgyn['banyak']) && $pmrksaanObgyn['terus_menerus_hari']) { ?>
			Pendarahan : <?=($pmrksaanObgyn['sedikit']) ? "Sedikit" : ""?> <?=($pmrksaanObgyn['banyak']) ? "Banyak" : ""?> selama <?=$pmrksaanObgyn['terus_menerus_hari']?>
			<?php } ?>
			<br>
			<?php if($pmrksaanObgyn['gatal'] || $pmrksaanObgyn['tidak_gatal'] || $pmrksaanObgyn['bau'] || $pmrksaanObgyn['tidak_bau']) { ?>
			Flour Albus : <?=($pmrksaanObgyn['gatal']) ? "Gatal" : ""?> <?=($pmrksaanObgyn['tidak_gatal']) ? "Tidak Gatal" : ""?> <?=($pmrksaanObgyn['bau']) ? "Bau" : ""?> <?=($pmrksaanObgyn['tidak_bau']) ? "Tidak Bau" : ""?>
			<br>
			Warna : <?=$pmrksaanObgyn['warna']?>
			<br>
			Selama : <?=$pmrksaanObgyn['sudah_berapa_lama']?>
			<?php } ?>
			<br>
			<?php if($pmrksaanObgyn['perut_sakitt'] || $pmrksaanObgyn['tumor'] || $pmrksaanObgyn['myom_uteri'] || $pmrksaanObgyn['kista_ovari'] || $pmrksaanObgyn['ca_cx'] || $pmrksaanObgyn['lainnyaa']) { ?>
			Lainnya : <?=($pmrksaanObgyn['perut_sakitt']) ? "Perut Sakit" : ""?> <?=($pmrksaanObgyn['tumor']) ? "Tumor" : ""?> <?=($pmrksaanObgyn['myom_uteri']) ? "Myom Uteri" : ""?> <?=($pmrksaanObgyn['kista_ovari']) ? "Kista Ovari" : ""?> <?=($pmrksaanObgyn['ca_cx']) ? "CA CX" : ""?>
			<?php } ?>
		</div>

		<div style="margin-top: 10px;">
			<strong>Riwayat kehamilan & persalinan yang lalu : </strong>
			<table border="1" style="border-collapse: collapse;">
				<thead>
					<tr>
						<th>Nomor</th>
					<?php for($i = 0; $i < count($field); $i++){ ?>
						<th><?=$field[$i]['anamnesa_pilihan_nama']?></th>
					<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php for($i = 0; $i < count($rspn); $i++){ 
						
						?>
						<tr>
							<td style="text-align: center"><?=$i+1?></td>
						<?php for($s = 0; $s < count($field); $s++){ ?>
							<td><?=$rspn[$i][$field[$s]['anamnesa_pilihan_id']]?></td>
						<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		
		<div style="margin-top: 10px;">
			<strong>OBJECTIVE</strong>
		</div>
		<div>
			Keadaan Umum Pasien : <?=$pmrksaanObgyn['keadaan_umum_pasien_ginek']?><br>
			Kesadaran : <?=$pmrksaanObgyn['kesadaran_ginek']?><br>
			Tekanan Darah Sistole : <?=$pmrksaanObgyn['tekanan_darah_sistole_ginek']?> mm/Hg <br>
			Tekanan Darah Diastole : <?=$pmrksaanObgyn['tekanan_darah_diastole_ginek']?> mm/Hg <br>
			Nadi  : <?=$pmrksaanObgyn['nadi_ginek']?> x/Menit <br>
			Pernafasan : <?=$pmrksaanObgyn['pernafasan_ginek']?> x/Menit <br>
			Suhu Badan : <?=$pmrksaanObgyn['suhu_badan_ginek']?> °C <br>
			Tinggi Badan : <?=$pmrksaanObgyn['tinggi_badan_ginek']?> Cm <br>
			Berat Badan : <?=$pmrksaanObgyn['berat_badan_ginek']?> Kg <br>
			Leher (TVJ) : <?=$pmrksaanObgyn['leher_tvj_ginek']?> Cm <br>
			<br>
			Mata : <?=$pmrksaanObgyn['mata_ginek']?><br>
			Leher : <?=$pmrksaanObgyn['leher_ginek']?><br>
			Payudara : <?=$pmrksaanObgyn['payudara_ginek']?><br>
		</div>
		
		<div style="margin-top: 10px;">
			<strong>Pemeriksaan Penunjang (Laboratorium, Radiologi) : </strong>
		</div>
		<div>
			<?=$pmrksaanObgyn['pemeriksaanPenunjang']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>USG Ginekologi : </strong>
		</div>
		<div>
			<?=$pmrksaanObgyn['usg_ginekologi']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>Status Lokalis: </strong>
		</div>
		<div>
			<?=$pmrksaanObgyn['status_lokalis_ginekologi']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>Pemeriksaan Inspekulo/VT : </strong>
		</div>
		<div>
			<?=$pmrksaanObgyn['pemerisaan_dalam_vt']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>ANALISA/DIAGNOSA : </strong>
		</div>
		<div>
			<ol style="margin: 0px">
			<?php
				for($i = 0; $i < count($diagnosa); $i++){
					?>
					<li><?=$diagnosa[$i]['diagnosa_short_desc']?></li>
					<?php
				}
			?>
			</ol>
			<!-- <?=$pmrksaanObgyn['analisa_diagnosaa']?> -->
		</div>
		<div style="margin-top: 10px;">
			<strong>PLANNING : </strong>
		</div>
		<div>
		<ol>
			<?php
				for($i = 0; $i < count($procedure); $i++){
					?>
					<li><?=$procedure[$i]['procedure_short_desc']?></li>
					<?php
				}
			?>
			</ol>
			
		</div>
<?php } ?>
		
		<div style="margin-top: 10px;">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Terapi : 
		</div>
		<div>
			<ol>
			<?php
				for($i = 0; $i < count($terapi); $i++){
					?>
						<li><?=$terapi[$i]['item_nama']?> <?=$terapi[$i]['petunjuk_nama']?>, <?=$terapi[$i]['aturan_minum_nama']?>, <?=$terapi[$i]['aturan_pakai_nama']?>, <?=$terapi[$i]['jam_aturan_pakai_nama']?></li>
					<?php
				}
			?>
			</ol>
		</div>
		<?php if(count($dataRacikan) > 0) { ?>
		<div style="margin-top: 10px;">
			<strong>Terapi Racikan : </strong>
		</div>
		<div>
			<ol>
			<?php
				for($i = 0; $i < count($dataRacikan); $i++){
					?>
						<li><?=$dataRacikan[$i]['jenis_racikan_nama']?> <?=$dataRacikan[$i]['rawat_terapi_racikan_jumlah']?> <?=$dataRacikan[$i]['satuan_nama']?> <?=$dataRacikan[$i]['petunjuk_nama']?> <?=$dataRacikan[$i]['aturan_pakai_nama']?> </li>
						<?php
							$rawat_racikan = $dataRacikan[$i]['rawat_terapi_racikan_id'];
							$sql = "SELECT terapi_jumlah_item, terapi_dosis, a.item_nama, a.petunjuk_nama, a.aturan_minum_nama, a.aturan_pakai_nama, a.jam_aturan_pakai_nama from klinik.klinik_perawatan_terapi a";
							$sql .= " LEFT JOIN apotik.apotik_jam_aturan_pakai b on b.jam_aturan_pakai_id = a.id_jam_aturan_pakai ";
							$sql .= " LEFT JOIN apotik.apotik_aturan_pakai c on c.aturan_pakai_id = a.id_aturan_pakai ";
							$sql .= " LEFT JOIN apotik.apotik_aturan_minum d on d.aturan_minum_id = a.id_aturan_minum ";
							$sql .= " LEFT JOIN apotik.apotik_obat_petunjuk e on e.petunjuk_id = a.terapi_dosis ";
							$sql .= " LEFT JOIN logistik.logistik_item g on g.item_id = a.id_item ";
							$sql .= " WHERE id_rawat_terapi_racikan = '$rawat_racikan'";
							$terapiRacikitem = $dtaccess->fetchAll($sql);
						?>
						<ol>
							<?php
							for($s=0; $s < count($terapiRacikitem); $s++){
								?>
								<li><?=$terapiRacikitem[$s]['item_nama']?> <?=$terapiRacikitem[$s]['terapi_jumlah_item']?></li>
								<?php
							}
							?>
						</ol>
					<?php
				}
			?>
			</ol>
		</div>
	<?php }?>
	<?php if($pmrksaanObgyn['diagnosa'] || $pmrksaanObgyn['penjelasan_penyakit'] || $pmrksaanObgyn['pemeriksaan_penunjang'] || $pmrksaanObgyn['terapi_edukasi'] || $pmrksaanObgyn['tindakan_medis'] || $pmrksaanObgyn['prognosa'] || $pmrksaanObgyn['perkiraan_hari_rawat'] || $pmrksaanObgyn['penjelasan_komplikasi'] || $pmrksaanObgyn['informed_concent'] || $pmrksaanObgyn['kondisi'] || $pmrksaanObgyn['konsul'] || $pmrksaanObgyn['edukasi_pulang'] || $pmrksaanObgyn['edukasi_lain'] ){ ?>

		<div style="margin-top: 10px;display: flex">
			<div style="width: 50%; ">
				<div >
					<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi : </strong>
				</div>
				<div>
					<ol>
						<?php if($pmrksaanObgyn['diagnosa']){?><li>Diagnosa</li><?php }?>
						<?php if($pmrksaanObgyn['penjelasan_penyakit']){?><li>Penjelasan penyakit (penyebab, tanda, gejala)</li><?php }?>
						<?php if($pmrksaanObgyn['pemeriksaan_penunjang']){?><li>Pemeriksaan Penunjang</li><?php }?>
						<?php if($pmrksaanObgyn['terapi_edukasi']){?><li>Terapi / terapi alternative</li><?php }?>
						<?php if($pmrksaanObgyn['tindakan_medis']){?><li>Tindakan Medis</li><?php }?>
						<?php if($pmrksaanObgyn['prognosa']){?><li>Prognosa</li><?php }?>
						<?php if($pmrksaanObgyn['perkiraan_hari_rawat']){?><li>Perkiraan Hari Rawat</li><?php }?>
						<?php if($pmrksaanObgyn['penjelasan_komplikasi']){?><li>Penjelasan komplikasi / resiko yang mungkin terjadi</li><?php }?>
						<?php if($pmrksaanObgyn['informed_concent']){?><li>Edukasi pengambilan informed concent</li><?php }?>
						<?php if($pmrksaanObgyn['kondisi']){?><li>Kondisi kesehatan saat ini</li><?php }?>
						<?php if($pmrksaanObgyn['konsul']){?><li>Konsul ke : <?= $pmrksaanObgyn['konsul_det']?></li><?php }?>
						<?php if($pmrksaanObgyn['edukasi_pulang']){?><li>Edukasi sebelum pulang</li><?php }?>
						<?php if($pmrksaanObgyn['edukasi_lain']){?><li>Lain lain : <?=$pmrksaanObgyn['lain_det']?></li><?php }?>
					</ol>
				</div>
			</div>
			<div style="width: 50%;">
				<div >
					<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edukasi : </strong>
				</div>
				<div>
					<ol>
						<?php if ($pmrksaanObgyn['memahamiMateri']) {  ?><li>&nbsp;Memahami Materi</li><?php }?>
                    	<?php if ($pmrksaanObgyn['bisaMengulang']) {  ?><li>&nbsp;Bisa Mengulang Materi</li><?php }?>
                    	<?php if ($pmrksaanObgyn['membatasiMateri']) {  ?><li>&nbsp;Membatasi Materi</li><?php }?>
                    	<?php if ($pmrksaanObgyn['pengulanganMateri']) {  ?><li>&nbsp;Butuh Pengulangan Materi</li><?php }?>
                    	
                    	<?php if ($pmrksaanObgyn['butuhLeaflet']) {  ?><li>&nbsp;Butuh Leaflet</li><?php }?>
                    	<?php if ($pmrksaanObgyn['lain_lainEdukasi']) {  ?><li>&nbsp;Lainnya : </li>
                      
                      <?php if ($pmrksaanObgyn["lainEd_det"]) { ?> - <?php echo $pmrksaanObgyn['lainEd_det']; ?><br><?php }?>
                      <?php }?>
					</ol>
				</div>
			</div>
		</div>
		<?php }?>
		<div>
		<table width="100%">
		<tr>
			<td width="50%" style="vertical-align: top;">
				<center>

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
				<?=$reg_data['cust_usr_nama']?>
				
					
				</center>
			</td>
			<td style="vertical-align: top;">
				<center>

				Jombang, <?=date_format(date_create($reg_data['reg_tanggal_pulang']), "d-m-Y")?><br>
				Dokter Penanggung Jawab (DPJP)
				<br>
				<br>
				<br>
				<br>
				
				
					<!-- <?=$reg_data['usr_name']?> -->
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