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
$sql = "SELECT id_reg, id_cust_usr, rawat_pemeriksaan_fisik from klinik.klinik_perawatan where rawat_id = '$rawat_id'";
$rawat = $dtaccess->Fetch($sql);

$sql = "SELECT *, e.waktu_asmed from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join global.global_auth_user d on a.id_dokter = d.usr_id
left join klinik.klinik_perawatan e on a.reg_id = e.id_reg
 where reg_id = ".QuoteValue(DPE_CHAR,$rawat['id_reg']);
$reg_data = $dtaccess->Fetch($sql);

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

$sql = "SELECT * from klinik.klinik_anamnesa_tb where id_anamnesa = '9dafa78dca4a01f50d21fbc884a5eecb' AND id_reg=".QuoteValue(DPE_CHAR,$rawat['id_reg'])." order by when_create asc ";
$dataAnamnesa = $dtaccess->FetchAll($sql);

$sql_cek = "SELECT anamnesa_isi_nilai FROM klinik.klinik_anamnesa_isi WHERE id_rawat = '$rawat_id' AND id_anamnesa = 'TIPE LAYANAN'";
$dataTipeLayanan = $dtaccess->Fetch($sql_cek);

$sql = "SELECT * from klinik.klinik_anamnesa_pilihan where id_anamnesa = '9dafa78dca4a01f50d21fbc884a5eecb' order by anamnesa_pilihan_urut asc, anamnesa_pilihan_id asc";
$field = $dtaccess->FetchAll($sql);

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

$fsk = explode(' ; ',$rawat['rawat_pemeriksaan_fisik']);
  $fisik = array();
  for($i = 0; $i < count($fsk); $i++){
	  $sprt = explode(' : ', $fsk[$i]);
	  $fisik[$sprt[0]] = $sprt[1];
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

 $ttdPasien = $ROOT."/gambar/asset_ttd/".$rawat_id.".jpg";

 //print_r($_GET);

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
					<td><h2 style="margin: 0 0 0 10px;"><strong>ASUHAN MEDIS AWAL</strong></h2></td>
				</tr>
				<tr>
					<td>
						<table style="width: 60%; margin: auto;">
							<tr>
								<td>Nama Pasien</td>
								<td> : </td>
								<td><?= str_replace("*", "'", $reg_data['cust_usr_nama'])?></td>
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
			Jam mulai Assesmen Medis : <?=$reg_data['waktu_mulai_asmed']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>SUBJECTIVE </strong>
		</div>
		<?php if($dataTipeLayanan['anamnesa_isi_nilai'] == '0bstetri') { ?>
		<div>
			Keluhan Utama : <?=$_GET['keluhanUtama']?><br>
			<?php //if($_GET['select_keluhan_utama']) { ?>
			<!-- <?=$_GET['select_keluhan_utama']?> <?=($_GET['pendarahan_sedikit']) ? "Sedikit": ""?> <?=($_GET['pendarahan_banyak']) ? "Banyak": ""?> selama <?=$_GET['keluhan_utama_bulan']?>
			 dengan rasa <?=($_GET['mual']) ? "Mual,": ""?> <?=($_GET['perut_sakit']) ? "Perut Sakit,": ""?> <?=($_GET['pusing']) ? "Pusing,": ""?> <?=($_GET['muntah']) ? "Muntah,": ""?><br> -->
			<?php //} ?>
			 Hpht  : <?=$_GET['hpht']?><br>
			 Hpl : <?=$_GET['hpl']?><br>
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
		<?php if ($_REQUEST['gs0'] || $_REQUEST['gs1'] || $_REQUEST['gs2'] || $_REQUEST['fetal_pool'] || $_REQUEST['fetus'] || $_REQUEST['crl'] || $_REQUEST['djj'] || $_REQUEST['usia_kehamilan_minggu'] || $_REQUEST['usia_kehamilan_hari'] || $_REQUEST['hpl_muda']) : ?>

<?php  
	if ($_REQUEST['gs1'] == '1') {
		$_REQUEST['gs1'] = 'Tunggal';
	}elseif ($_REQUEST['gs1'] == '2') {
		$_REQUEST['gs1'] = 'Kembar';
	}
?>
	<h5>HAMIL MUDA (TRIMESTER I)</h5>
	<div style="margin-top: -15px;">
		<div class="block">
			GS : <?php if ($_REQUEST['gs0']) { ?>
				<?= $_REQUEST['gs0'] ?>
			<?php } ?>
			<?php if ($_REQUEST['gs1']) { ?>
				<?= $_REQUEST['gs1'] ?>
			<?php } ?>
			 
			<?php if ($_REQUEST['gs2']) { ?>
				<?= $_REQUEST['gs2'] ?> mm
			<?php } ?>
			&nbsp;
			<?php if ($_REQUEST['fetal_pool']) { ?>
				Fetal Pool
				:
				<?= $_REQUEST['fetal_pool'] ?>
			<?php } ?>
			<?php if ($_REQUEST['fetus']) { ?>
				Fetus
				:
				<?= $_REQUEST['fetus'] ?>
			<?php } ?>
		</div>
	</div>
	<div>
		<div class="block">
			
			<?php if ($_REQUEST['crl']) {
			echo "CRL"; ?>
				<?= $_REQUEST['crl'] ?>
				mm
			<?php } ?>
			<?php if ($_REQUEST['djj']) { ?>
				&nbsp; DJJ
				:
				<?= $_REQUEST['djj'] ?>
			<?php } ?>
			&nbsp;

			<?php if ($_REQUEST['usia_kehamilan_minggu']) {
			echo "Usia Hamil"; ?>
				<?= $_REQUEST['usia_kehamilan_minggu'] ?>
				Minggu
			<?php } ?>
			<?php if ($_REQUEST['usia_kehamilan_hari']) { ?>
				<?= $_REQUEST['usia_kehamilan_hari'] ?>
				Hari
			<?php } ?>
			<?php if ($_REQUEST['hpl_muda']) { ?>
				&nbsp;HPL
				<?= $_REQUEST['hpl_muda'] ?>
			<?php } ?>
		</div>
	</div>
<?php endif; ?>
<?php if (
	$_REQUEST['janin_tunggal'] ||
	$_REQUEST['janin_kembar'] ||
	$_REQUEST['janin_hidup'] ||
	$_REQUEST['janin_iufd'] ||
	$_REQUEST['letak_janin_kepala'] ||
	$_REQUEST['letak_janin_sungsang'] ||
	$_REQUEST['letak_janin_melintang'] ||
	$_REQUEST['letak_janin_oblique'] ||
	$_REQUEST['bpd'] ||
	$_REQUEST['fl'] ||
	$_REQUEST['ac'] ||
	$_REQUEST['efw'] ||
	$_REQUEST['insersi_fudus'] ||
	$_REQUEST['insersi_corpus'] ||
	$_REQUEST['insersi_sbr'] ||
	$_REQUEST['insersi_anterior'] ||
	$_REQUEST['insersi_posterior'] ||
	$_REQUEST['grade'] ||
	$_REQUEST['ketuban_cukup'] ||
	$_REQUEST['ketuban_kurang'] ||
	$_REQUEST['ketuban_banyak'] ||
	$_REQUEST['afi'] ||
	$_REQUEST['usia_kehamilan_minggu1'] ||
	$_REQUEST['usia_kehamilan_hari1'] ||
	$_REQUEST['hpltp'] ||
	$_REQUEST['jenis_kelamin']
) : ?>


	<h5>HAMIL TRIMESTER II-III</h5>
	<?php if($_REQUEST['janin_tunggal'] || $_REQUEST['janin_kembar'] || $_REQUEST['janin_hidup'] || $_REQUEST['janin_iufd']) { ?>
	<div style="margin-top: -15px;">
		<div class="block"> Janin &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : <?= ($_REQUEST['janin_tunggal'] == 'true') ? 'Tunggal;' : '' ?>
			<?= ($_REQUEST['janin_kembar'] == 'true') ? 'Kembar;' : '' ?>
			<?= ($_REQUEST['janin_hidup'] == 'true') ? 'Hidup;' : '' ?>
			<?= ($_REQUEST['janin_iufd'] == 'true') ? 'IUFD;' : '' ?>
		</div>
	</div>
<?php }?>
	<?php if($_REQUEST['letak_janin_kepala'] || $_REQUEST['letak_janin_sungsang'] || $_REQUEST['letak_janin_melintang'] || $_REQUEST['letak_janin_oblique']) { ?>
	<div>
		<div class="block">
			Letak Janin : <?= ($_REQUEST['letak_janin_kepala'] == 'true') ? 'Kepala;' : '' ?>
			<?= ($_REQUEST['letak_janin_sungsang'] == 'true') ? 'Sungsang;' : '' ?>
			<?= ($_REQUEST['letak_janin_melintang'] == 'true') ? 'Melintang;' : '' ?>
			<?= ($_REQUEST['letak_janin_oblique'] == 'true') ? 'Oblique;' : '' ?>

		</div>
	</div>
<?php }?>
	<?php if ($_REQUEST['bpd'] != '' || $_REQUEST['fi'] != '' || $_REQUEST['ac'] != '' || $_REQUEST['efw'] != '') { ?>
	<div>
		<div class="block">
			BPD &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;:
			<?php if ($_REQUEST['bpd']) { ?>
				&nbsp;<?= $_REQUEST['bpd'] ?> cm
			<?php } ?>
			<?php if ($_REQUEST['fl']) { ?>
				&nbsp; FL
				<?= $_REQUEST['fl'] ?> mm
			<?php } ?>
			<?php if ($_REQUEST['ac']) { ?>
				&nbsp;AC
				<?= $_REQUEST['ac'] ?> mm
			<?php } ?>
			<?php if ($_REQUEST['efw']) { ?>
				&nbsp;EFW
				<?= $_REQUEST['efw'] ?> gram
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<?php if($_REQUEST['insersi_fudus'] || $_REQUEST['insersi_corpus'] || $_REQUEST['insersi_sbr'] || $_REQUEST['insersi_anterior'] || $_REQUEST['insersi_posterior'] || $_REQUEST['grade']) { ?>
	<div>
		<div class="block">
			Plasenta &nbsp;&nbsp; :
			<?php if ($_REQUEST['insersi_fudus']) { ?>
				<?= ($_REQUEST['insersi_fudus'] == 'true') ? 'Fundus;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['insersi_corpus']) { ?>
				<?= ($_REQUEST['insersi_corpus'] == 'true') ? 'Corpus;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['insersi_sbr']) { ?>
				<?= ($_REQUEST['insersi_sbr'] == 'true') ? 'SBR;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['insersi_anterior']) { ?>
				<?= ($_REQUEST['insersi_anterior'] == 'true') ? 'Ant;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['insersi_posterior']) { ?>
				<?= ($_REQUEST['insersi_posterior'] == 'true') ? 'Post;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['grade']) { ?>
				&nbsp; Grade : <?= $_REQUEST['grade'] ?>
			<?php } ?>
		</div>
	</div>
	<?php }?>
	<?php if($_REQUEST['ketuban_cukup'] || $_REQUEST['ketubah_cukup'] || $_REQUEST['ketuban_kurang'] || $_REQUEST['ketuban_banyak'] || $_REQUEST['afi']) { ?>
	<div>
		<div class="block">
			Ketuban &nbsp;&nbsp; &nbsp; :
			<?php if ($_REQUEST['ketuban_cukup'] || $_REQUEST['ketubah_cukup']) { ?>
				<?= ($_REQUEST['ketuban_cukup'] == 'true' || $_REQUEST['ketubah_cukup'] == 'true') ? 'Cukup;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['ketuban_kurang']) { ?>
				<?= ($_REQUEST['ketuban_kurang'] == 'true') ? 'Kurang;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['ketuban_banyak']) { ?>
				<?= ($_REQUEST['ketuban_banyak'] == 'true') ? 'Banyak;' : '' ?>
			<?php } ?>
			<?php if ($_REQUEST['afi']) { ?>
				&nbsp;AFI
				<?= $_REQUEST['afi'] ?>
			<?php } ?>
		</div>
	</div>
	<?php }?>
	<?php if($_REQUEST['usia_kehamilan_minggu1'] || $_REQUEST['usia_kehamilan_hari1'] || $_REQUEST['hpltp']) { ?>
	<div>
		<div class="block">
			Usia Hamil &nbsp;:
			<?php if ($_REQUEST['usia_kehamilan_minggu1']) { ?>
				<?= $_REQUEST['usia_kehamilan_minggu1'] ?>
				mgg
			<?php } ?>
			<?php if ($_REQUEST['usia_kehamilan_hari1']) { ?>
				<?= $_REQUEST['usia_kehamilan_hari1'] ?>
				Hr
			<?php } ?>
			<?php if ($_REQUEST['hpltp']) { ?>
				&nbsp;
				HPL :
				<?= $_REQUEST['hpltp'] ?>
			<?php } ?>

		</div>
	</div>
	<?php }?>
	<?php if($_REQUEST['jenis_kelamin']) {?>
	<div>
		<div class="block">
			Kelamin &nbsp; &nbsp; &nbsp;:
			<?php
				if ($_REQUEST['jenis_kelamin'] == 'Laki') {
					echo "Laki - laki";
				}elseif ($_REQUEST['jenis_kelamin'] == 'Perempuan') {
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
		<?php if($_GET['pemeriksaanPenunjang']) { ?>
		<div>
			<div class="block">
				Pemeriksaan Penunjang (Laboratorium, Radiologi)&nbsp; &nbsp; &nbsp;:<br>
				<?php
					echo $_GET['pemeriksaanPenunjang'];
				?>
			</div>
		</div>
		<?php } ?>
		<?php if($_GET['status_lokalis']) { ?>
		<div>
			<div class="block">
				Status Lokalis &nbsp; &nbsp; &nbsp;:<br>
				<?php
					echo $_GET['status_lokalis'];
				?>
			</div>
		</div>
		<?php } ?>
		<div style="margin-top: 10px;">
			<strong>ANALISA : </strong>
		</div>
		<div style="margin-top: 7px">
			G : <?= $_GET['g_analisa']?> P : <?= $_GET['p_analisa']?> A : <?= $_GET['a_analisa']?> , <?=" / ".$_GET['ket_diagnosa_satu']?>  <?=($_GET['ket_diagnosa_dua'] == 'T') ? " / Tunggal" : ""?> <?=($_GET['ket_diagnosa_dua'] == 'G') ? " / Gemelli" : ""?> <?=" / ".$_GET['ket_diagnosa_tiga']?>  <?=" / ".$_GET['ket_diagnosa_lima']?>
			<br>- <?=$_GET['ket_diagnosa_empat']?>
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
			<li><?=$_GET['planning_penatalaksanaan']?></li>
			</ol>
		</div>
<?php
}
	else if($dataTipeLayanan['anamnesa_isi_nilai'] == 'Ginekology' ){
?>
		<div>
			Keluhan Utama : <?=$_GET['keluhan_utama']?><br>
			<?php if( $_GET['haid_teratur_ya'] || $_GET['haid_teratur_tidak']) { ?>
			<?=($_GET['haid_teratur_ya']) ? "Haid Teratur" : "" ?> <?=($_GET['haid_teratur_tidak']) ? "Haid Tidak Teratur" : "" ?> selama <?=$_GET['lamanya_haid_teratur']?> hari, dengan rasa <?=($_GET['haid_nyeri'] == 'y') ? "Nyeri" : "Tidak Nyeri"?>
			<?php } ?>
			<br>
			<?php if($_GET['amenore_ya'] || $_GET['menopause']) { ?>
			Gangguan Haid : <?php if($_GET['amenore_ya']) { ?> <?=($_GET['amenore_ya']) ? "Amenore" : "" ?> selama <?=($_GET['amenore_hari']) ? $_GET['amenore_hari'] : "" ?> hari<?php } ?> <?php if($_GET['menopause']) { ?> <?=$_GET['menopause']?> <?php } ?>
			<?php } ?>
			<br>
			Haid Selama : <?=$_GET['haid_lama_hari']?> Hari 
			<br>
			Haid lama dan banyak : <?=$_GET['haid_lama_banyak_hari']?> Hari
			<br>
			Haid selama 1 bulan : <?=$_GET['haid_lama_bulan']?> Kali
			<br>
			Lama haid terus menerus : <?=$_GET['terus_menerus_lama']?> Hari
			
			<?php if(($_GET['sedikit'] || $_GET['banyak']) && $_GET['terus_menerus_hari']) { ?>
			<br>
			Pendarahan : <?=($_GET['sedikit']) ? "Sedikit" : ""?> <?=($_GET['banyak']) ? "Banyak" : ""?> selama <?=$_GET['terus_menerus_hari']?>
			<br>
			<?php } ?>
			
			<?php if($_GET['gatal'] || $_GET['tidak_gatal'] || $_GET['bau'] || $_GET['tidak_bau']) { ?>
			Flour Albus : <?=($_GET['gatal']) ? "Gatal" : ""?> <?=($_GET['tidak_gatal']) ? "Tidak Gatal" : ""?> <?=($_GET['bau']) ? "Bau" : ""?> <?=($_GET['tidak_bau']) ? "Tidak Bau" : ""?>
			<br>
			Warna : <?=$_GET['warna']?>
			<br>
			Selama : <?=$_GET['sudah_berapa_lama']?>
			<br>
			<?php } ?>
			
			<?php if($_GET['perut_sakitt'] || $_GET['tumor'] || $_GET['myom_uteri'] || $_GET['kista_ovari'] || $_GET['ca_cx'] || $_GET['lainnyaa']) { ?>
			Lainnya : <?=($_GET['perut_sakitt']) ? "Perut Sakit" : ""?> <?=($_GET['tumor']) ? "Tumor" : ""?> <?=($_GET['myom_uteri']) ? "Myom Uteri" : ""?> <?=($_GET['kista_ovari']) ? "Kista Ovari" : ""?> <?=($_GET['ca_cx']) ? "CA CX" : ""?>
			<?php } ?>
		</div>

		<div style="margin-top: 0px;">
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
			Keadaan Umum Pasien : <?=$_GET['keadaan_umum_pasien_ginek']?><br>
			Kesadaran : <?=$_GET['kesadaran_ginek']?><br>
			Tekanan Darah Sistole : <?=$_GET['tekanan_darah_sistole_ginek']?> mm/Hg <br>
			Tekanan Darah Diastole : <?=$_GET['tekanan_darah_diastole_ginek']?> mm/Hg <br>
			Nadi  : <?=$_GET['nadi_ginek']?> x/Menit <br>
			Pernafasan : <?=$_GET['pernafasan_ginek']?> x/Menit <br>
			Suhu Badan : <?=$_GET['suhu_badan_ginek']?> °C <br>
			Tinggi Badan : <?=$_GET['tinggi_badan_ginek']?> Cm <br>
			Berat Badan : <?=$_GET['berat_badan_ginek']?> Kg <br>
			Leher (TVJ) : <?=$_GET['leher_tvj_ginek']?> Cm <br>
			<br>
			Mata : <?=$_GET['mata_ginek']?><br>
			Leher : <?=$_GET['leher_ginek']?><br>
			Payudara : <?=$_GET['payudara_ginek']?><br>
		</div>
		<div style="margin-top: 10px;">
			<strong>Pemeriksaan Penunjang (Laboratorium, Radiologi) : </strong>
		</div>
		<div>
			<?=$_GET['pemeriksaanPenunjang_g']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>USG Ginekologi : </strong>
		</div>
		<div>
			<?=$_GET['usg_ginekologi']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>Status Lokalis: </strong>
		</div>
		<div>
			<?=$_GET['status_lokalis_ginekologi']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>Pemeriksaan Inspekulo/VT : </strong>
		</div>
		<div>
			<?=$_GET['pemerisaan_dalam_vt']?>
		</div>
		<div style="margin-top: 10px;">
			<strong>ANALISA : </strong>
		</div>
		<div>
			
			<?=($_GET['analisa_diagnosaa']) ? "- ".$_GET['analisa_diagnosaa']."" : ""?>
			
			
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
			<?php if($_GET['analisa_diagnosaa_ginek']) {?><li><?=$_GET['analisa_diagnosaa_ginek']?></li><?php }?>
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
			<!-- <?php //if($_GET['planning_penatalaksanaan_ginek']) {?><li><?=$_GET['planning_penatalaksanaan_ginek']?></li><?php //}?> -->
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
						<li><?=$terapi[$i]['item_nama']?> <?=$terapi[$i]['terapi_jumlah_item']?>,  <?=$terapi[$i]['petunjuk_nama']?>, <?=$terapi[$i]['aturan_minum_nama']?>, <?=$terapi[$i]['aturan_pakai_nama']?>, <?=$terapi[$i]['jam_aturan_pakai_nama']?></li>
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
		<?php if($_GET['diagnosa'] || $_GET['penjelasan_penyakit'] || $_GET['pemeriksaan_penunjang'] || $_GET['terapi_edukasi'] || $_GET['terapi_alter'] || $_GET['tindakan_medis'] || $_GET['prognosa'] || $_GET['perkiraan_hari_rawat'] || $_GET['penjelasan_komplikasi'] || $_GET['informed_concent'] || $_GET['kondisi'] || $_GET['konsul'] || $_GET['edukasi_pulang'] || $_GET['edukasi_lain'] ){ ?>

		<div style="margin-top: 10px;display: flex">
			<div style="width: 50%; ">
				<div >
					<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Materi Edukasi : </strong>
				</div>
				<div>
					<ol>
						<?php if($_GET['diagnosa']){?><li>Diagnosa</li><?php }?>
						<?php if($_GET['penjelasan_penyakit']){?><li>Penjelasan penyakit (penyebab, tanda, gejala)</li><?php }?>
						<?php if($_GET['pemeriksaan_penunjang']){?><li>Pemeriksaan Penunjang</li><?php }?>
						<?php if($_GET['terapi_edukasi']){?><li>Terapi</li><?php }?>
						
						<?php if($_GET['prognosa']){?><li>Prognosa</li><?php }?>
						<?php if($_GET['tindakan_medis']){?><li>Tindakan Medis</li><?php }?>
						<?php if($_GET['terapi_alter']){?><li>Terapi alternative</li><?php }?>
						<?php if($_GET['perkiraan_hari_rawat']){?><li>Perkiraan Hari Rawat</li><?php }?>
						<?php if($_GET['penjelasan_komplikasi']){?><li>Penjelasan komplikasi / resiko yang mungkin terjadi</li><?php }?>
						<?php if($_GET['informed_concent']){?><li>Edukasi pengambilan informed concent</li><?php }?>
						<?php if($_GET['kondisi']){?><li>Kondisi kesehatan saat ini</li><?php }?>
						<?php if($_GET['konsul']){?><li>Konsul ke : <?= $_GET['konsul_det']?></li><?php }?>
						<?php if($_GET['edukasi_pulang']){?><li>Edukasi sebelum pulang</li><?php }?>
						<?php if($_GET['edukasi_lain']){?><li>Lain lain : <?=$_GET['lain_det']?></li><?php }?>
					</ol>
				</div>
			</div>
			<div style="width: 50%;">
				<div >
					<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edukasi : </strong>
				</div>
				<div>
					<ol>
						<?php if ($_GET['memahamiMateri']) {  ?><li>&nbsp;Memahami Materi</li><?php }?>
                    	<?php if ($_GET['bisaMengulang']) {  ?><li>&nbsp;Bisa Mengulang Materi</li><?php }?>
                    	<?php if ($_GET['membatasiMateri']) {  ?><li>&nbsp;Membatasi Materi</li><?php }?>
                    	<?php if ($_GET['pengulanganMateri']) {  ?><li>&nbsp;Butuh Pengulangan Materi</li><?php }?>
                    	
                    	<?php if ($_GET['butuhLeaflet']) {  ?><li>&nbsp;Butuh Leaflet</li><?php }?>
                    	<?php if ($_GET['lain_lainEdukasi']) {  ?><li>&nbsp;Lainnya : </li>
                      
                      <?php if ($_GET["lainEd_det"]) { ?> - <?php echo $_GET['lainEd_det']; ?><br><?php }?>
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
				<?= str_replace("*", "'", $reg_data['cust_usr_nama']);?>
				
					
				</center>
			</td>
			<td>
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