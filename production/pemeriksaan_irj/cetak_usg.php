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
<?php if (!$_REQUEST['usg_ginekologi']) : ?>
	<?php if ($_REQUEST['gs0'] || $_REQUEST['gs1'] || $_REQUEST['gs2'] || $_REQUEST['fetal_pool'] || $_REQUEST['fetus'] || $_REQUEST['crl'] || $_REQUEST['djj'] || $_REQUEST['usia_kehamilan_minggu'] || $_REQUEST['usia_kehamilan_hari'] || $_REQUEST['hpl_muda']) : ?>
		<style>
			.wrapper {
				transform: rotate(90deg);
				position: fixed;
				margin-left: 140px;
				margin-top: -10px;
				width: 30px;
				padding-bottom: 10px;
			}
		</style>
	<?php else : ?>
		<style>
			.wrapper {
				transform: rotate(90deg);
				position: fixed;
				margin-left: -40px;
				margin-top: 175px;
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
	<?php endif; ?>
<?php else : ?>
	<style>
		.wrapper {
			transform: rotate(90deg);
			position: fixed;
			margin-left: -15px;
			margin-top: 175px;
			width: 30px;
			padding-bottom: 10px;
			border-style: dotted;
			border-right: 2px dashed silver;
			border-left: 0px dashed silver;
			border-top: 0px dashed silver;
			border-bottom: 0px dashed silver;
			width: 370px;
		}
	</style>
<?php endif; ?>

<!-- onload="setTimeout('self.close()',5000)" -->

<body>
	<div class="wrapper">
		<div>
			<p style="font-size:13px; margin-bottom: -10px;"> <?= $_REQUEST['nama_pasien'] ?> (<?= $_REQUEST['nomor_rm'] ?>)</p>
			<h5 style="margin-bottom:-20px; ">HASIL PEMERIKSAAN USG <?php echo date('d-m-Y'); ?><h5>

		</div>
		<hr style="width:400px;margin-bottom:-15px; border-style:dashed; color:white;">
		<?php if ($_REQUEST['usg_ginekologi']) { ?>
			<h5>&nbsp;</h5>
			<div style="margin-top: -15px;">
				<div class="block">
					USG Ginekologi : <?= $_REQUEST['usg_ginekologi'] ?>
				</div>
			</div>
		<?php } ?>
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
			<div style="margin-top: -15px;">
				<div class="block"> Janin &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : <?= ($_REQUEST['janin_tunggal'] == 'true') ? 'Tunggal;' : '' ?>
					<?= ($_REQUEST['janin_kembar'] == 'true') ? 'Kembar;' : '' ?>
					<?= ($_REQUEST['janin_hidup'] == 'true') ? 'Hidup;' : '' ?>
					<?= ($_REQUEST['janin_iufd'] == 'true') ? 'IUFD;' : '' ?>
				</div>
			</div>
			<div>
				<div class="block">
					Letak Janin : <?= ($_REQUEST['letak_janin_kepala'] == 'true') ? 'Kepala;' : '' ?>
					<?= ($_REQUEST['letak_janin_sungsang'] == 'true') ? 'Sungsang;' : '' ?>
					<?= ($_REQUEST['letak_janin_melintang'] == 'true') ? 'Melintang;' : '' ?>
					<?= ($_REQUEST['letak_janin_oblique'] == 'true') ? 'Oblique;' : '' ?>

				</div>
			</div>
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
<?php endif; ?>
			<div>
				<div class="block">
					Lain-lain &nbsp; &nbsp; &nbsp;:
					<?php
						echo $_REQUEST['lain-lain'];
					?>
				</div>
			</div>
			<div>
				<div class="block">
					USG Tambahan &nbsp; &nbsp; &nbsp;:
					<?=$_REQUEST['USGTambahan']?>
				</div>
			</div>
	</div>

<!-- <div class="regards">
				Terima Kasih, Sejawat<br>
				Dokter Spesialis Radiologi<br><br><br>
				<?php // echo $dataPasien["dokter_radiologi"]; 
				?>
			</div> -->
<script>
	var url = "cetak_usg.php?<?= 'gs0' . $_REQUEST['gs0'] . '&gs1=' . $_REQUEST['gs1'] . '&gs2=' . $_REQUEST['gs2'] . '&=fetal_pool' . $_REQUEST['fetal_pool'] . '&fetus=' . $_REQUEST['fetus'] . '&crl=' . $_REQUEST['crl'] . '&djj=' . $_REQUEST['djj'] . '&usia_kehamilan_minggu=' . $_REQUEST['usia_kehamilan_minggu'] . '&usia_kehamilan_hari=' . $_REQUEST['usia_kehamilan_hari'] . '&hpl_muda=' . $_REQUEST['hpl_muda'] ?><?= '&janin_tunggal=' . $_REQUEST['janin_tunggal'] . '&janin_kembar=' . $_REQUEST['janin_kembar'] . '&janin_hidup=' . $_REQUEST['janin_hidup'] . '&janin_iufd=' . $_REQUEST['janin_iufd'] . '&letak_janin_kepala=' . $_REQUEST['letak_janin_kepala'] . '&letak_janin_sungsang=' . $_REQUEST['letak_janin_sungsang'] . '&letak_janin_melintang=' . $_REQUEST['letak_janin_melintang'] . '&letak_janin_oblique=' . $_REQUEST['letak_janin_oblique'] . '&bpd=' . $_REQUEST['bpd'] . '&fl=' . $_REQUEST['fl'] . '&sc=' . $_REQUEST['ac'] . '&efw=' . $_REQUEST['efw'] . '&insersi_fudus=' . $_REQUEST['insersi_fudus'] . '&insersi_corpus=' . $_REQUEST['insersi_corpus'] . '&insersi_sbr=' . $_REQUEST['insersi_sbr'] . '&insersi_anterior=' . $_REQUEST['insersi_anterior'] . '&insersi_posterior=' . $_REQUEST['insersi_posterior'] . '&grade=' . $_REQUEST['grade'] . '&ketuban_cukup=' . $_REQUEST['ketuban_cukup'] . '&ketuban_kurang=' . $_REQUEST['ketuban_kurang'] . '&ketuban_banyak=' . $_REQUEST['ketuban_banyak'] . '&afi=' . $_REQUEST['afi'] . '&usia_kehamilan_minggu1=' . $_REQUEST['usia_kehamilan_minggu1'] . '&usia_kehamilan_hari1=' . $_REQUEST['usia_kehamilan_hari1'] . '&hpltp=' . $_REQUEST['hpltp'] . '&jenis_kelamin=' . $_REQUEST['jenis_kelamin'] ?>&c=1";

	function myFunction(a) {

		var myWindow = window.open(a, "cetak", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=550,height=400,left=100,top=100");
		myWindow.print();

	}
	// window.open(url, '', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=550,height=400,left=100,top=100');
	<?php if ($_REQUEST['c'] != '1') { ?>
		// myFunction(url);
	<?php } ?>
	window.print();
</script>

</body>

</html>