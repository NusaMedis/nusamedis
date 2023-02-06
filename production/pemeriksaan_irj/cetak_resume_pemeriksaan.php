<?php        
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."expAJAX.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     
	if($_GET["id_rawat"]) {
		$sql = "select a.reg_kode_trans,a.reg_jenis_pasien, a.reg_tipe_layanan,
        cust_usr_alamat, cust_usr_nama, cust_usr_kode,cust_usr_kode_tampilan, b.cust_usr_jenis_kelamin, 
        a.id_dokter, reg_umur,reg_umur_bulan, reg_umur_hari, ((current_date - b.cust_usr_tanggal_lahir)/365) as umur,
        a.reg_waktu, a.reg_tanggal, d.usr_name as dokter_nama, e.poli_nama, c.rawat_anamnesa, 
        c.rawat_pemeriksaan_fisik, c.rawat_penunjang,c.rawat_kasus_keterangan
		from klinik.klinik_perawatan c 
        left join klinik.klinik_registrasi a on c.id_reg = a.reg_id
        left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
        left join global.global_auth_user d on a.id_dokter = d.usr_id
        left join global.global_auth_poli e on a.id_poli = e.poli_id
        where c.rawat_id = ".QuoteValue(DPE_CHAR,$_GET["id_rawat"]);
       // echo $sql;
		$dataPasien= $dtaccess->Fetch($sql);
		//print_r($dataPasien);
	}
?>                          
<style>
	.regards{
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
			$a = array('L' => 'Laki-Laki', 'P' => 'Perempuan'); echo $a[$dataPasien["cust_usr_jenis_kelamin"]]; ?> / 
			<?php echo $dataPasien["reg_umur"]; ?> th. - <?php echo $dataPasien["reg_umur_bulan"]; ?> bl. - <?php echo $dataPasien["reg_umur_hari"]; ?> hr.
		</td>
	  </tr>
	  <tr>
		<td>Alamat</td>
		<td>:</td>
		<td><?php echo $dataPasien["cust_usr_alamat"]; ?></td>
	  </tr>
	</table>
	<table width="50%" border="0px">
	  <tr>
		<td>Tgl. Perawatan</td>
		<td>:</td>
		<td><?php echo format_date($dataPasien["reg_tanggal"])." ".$dataPasien["reg_waktu"]; ?></td>
	  </tr>
      <tr>
		<td>Klinik</td>
		<td>:</td>
		<td><?php echo $dataPasien["poli_nama"]; ?></td>
	  </tr>
	  <tr>
		<td>Dokter</td>
		<td>:</td>
		<td><?php echo $dataPasien["dokter_nama"]; ?></td>
	  </tr>
	</table>
    <br><br>
	<div class="clearfix"><br><br></div>
	<hr style="border: 1px dashed;">
	<span>URAIAN HASIL PERAWATAN</span>
	<hr style="border: 1px dashed;">
	<div class="clearfix"><br><br></div>          
	<table width="100%" border="0px">
	  <tr>
		<td width="15%">Subjective</td>
		<td>:</td>
		<td><?php echo $dataPasien["rawat_anamnesa"]; ?></td>
	  </tr>
      <tr>
		<td width="15%">Objective</td>
		<td>:</td>
		<td><?php echo $dataPasien["rawat_pemeriksaan_fisik"]; ?></td>
	  </tr>
      <tr>
		<td width="15%">Assesmen</td>
		<td>:</td>
		<td><?php echo $dataPasien["rawat_penunjang"]; ?></td>
	  </tr>
	  <tr>
		<td width="15%">Planning</td>
		<td>:</td>
		<td><?php echo $dataPasien["rawat_kasus_keterangan"]; ?></td>
	  </tr>
	</table>
</body>
</html>