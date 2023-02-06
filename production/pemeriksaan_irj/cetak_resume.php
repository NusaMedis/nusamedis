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

$lokasirad = $ROOT . "gambar/foto_radiologi";

if ($_GET["id_reg"]) {
	$sql = "select h.foto_radiologi_nama, a.reg_kode_trans,a.reg_jenis_pasien, a.reg_tipe_layanan,cust_usr_alamat, cust_usr_nama, cust_usr_kode,cust_usr_kode_tampilan, b.cust_usr_jenis_kelamin, a.id_dokter, reg_umur,reg_umur_bulan, reg_umur_hari, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur, d.usr_name as dokter_pengirim,
		e.poli_nama as poli_asal, reg_waktu, reg_tanggal, f.poli_nama , c.when_create, c.resume_ket,c.nama_pemeriksaan, g.usr_name as dokter_radiologi, c.resume_tanggal
		from klinik.klinik_registrasi a 
            join  global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
			join  radiologi.radiologi_resume c on c.id_reg = a.reg_id 
			left join radiologi.radiologi_foto h on h.id_resume=c.resume_id
            left join global.global_auth_user d on c.id_dokter_pengirim = d.usr_id
			left join global.global_auth_poli e on a.id_poli_asal = e.poli_id
			left join global.global_auth_poli f on a.id_poli = f.poli_id
            left join global.global_auth_user g on c.id_dokter_rad = g.usr_id
            where c.id_reg = " . QuoteValue(DPE_CHAR, $_GET["id_reg"]);

	// echo $sql;

	$dataPasien = $dtaccess->Fetch($sql);
	//print_r($dataPasien);
}
?>
<style>
	.regards {
		position: relative;
		float: right;
		padding-right: 20px;
		text-align: center;
	}
</style>

<body>
	<center><?php echo strtoupper($dataPasien["nama_pemeriksaan"]); ?></center>
	<br><br>
	<table width="50%" border="0px" style="float: left">
		<tr>
			<td width="30%">No. Reg.</td>
			<td width="2%">:</td>
			<td><?php echo $dataPasien["reg_kode_trans"]; ?></td>
		</tr>
		<tr>
			<td>No. RM</td>
			<td>:</td>
			<td><?php echo $dataPasien["cust_usr_kode_tampilan"]; ?></td>
		</tr>
		<tr>
			<td>Nama</td>
			<td>:</td>
			<td><?php echo $dataPasien["cust_usr_nama"]; ?></td>
		</tr>
		<tr>
			<td>Jenis Kelamin / Umur</td>
			<td>:</td>
			<td><?php
				$a = array('L' => 'Laki-Laki', 'P' => 'Perempuan');
				echo $a[$dataPasien["cust_usr_jenis_kelamin"]]; ?> /
				<?php echo $dataPasien["reg_umur"]; ?> th. - <?php echo $dataPasien["reg_umur_bulan"]; ?> bl. - <?php echo $dataPasien["reg_umur_hari"]; ?> hr.
			</td>
		</tr>
		<tr>
			<td>Dr. Pengirim</td>
			<td>:</td>
			<td><?php echo $dataPasien["dokter_pengirim"]; ?></td>
		</tr>
		<tr>
			<td>Alamat</td>
			<td>:</td>
			<td><?php echo $dataPasien["cust_usr_alamat"]; ?></td>
		</tr>
	</table>
	<table width="50%" border="0px">
		<tr>
			<td width="30%">Rujukan</td>
			<td width="2%">:</td>
			<td><?php echo $dataPasien["poli_asal"]; ?></td>
		</tr>
		<tr>
			<td>Ruang / Poli</td>
			<td>:</td>
			<td><?php echo $dataPasien["poli_nama"]; ?></td>
		</tr>
		<tr>
			<td>Kelas / Ruang Rawat</td>
			<td>:</td>
			<td>&nbsp;/ </td>
		</tr>
		<tr>
			<td>Tgl. Pendaftaran</td>
			<td>:</td>
			<td><?php echo format_date($dataPasien["reg_tanggal"]) . " " . $dataPasien["reg_waktu"]; ?></td>
		</tr>
		<tr>
			<td>Tgl. Hasil</td>
			<td>:</td>
			<td><?php echo format_date($dataPasien["resume_tanggal"]) ?></td>
		</tr>
	</table>
	<div class="clearfix"><br><br></div>
	<hr style="border: 1px dashed;">
	<span>URAIAN HASIL PEMERIKSAAN<span>
			<hr style="border: 1px dashed;">
			<img src="<?= $lokasirad . '/' . $dataPasien['foto_radiologi_nama'] ?>" width="30%" class="img-responsive" alt="Image">
			<hr>
			<?php echo nl2br($dataPasien["resume_ket"]); ?>
			<div class="clearfix"><br><br></div>
			<div class="regards">
				Terima Kasih, Sejawat<br>
				Dokter Spesialis Radiologi<br><br><br>
				<?php echo $dataPasien["dokter_radiologi"]; ?>
			</div>
</body>

</html>