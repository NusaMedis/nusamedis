<?php


	# code...
	if($_SERVER['REQUEST_METHOD']=='GET') {

	require_once('koneksi.php');
	class usr{}
	
	
	// $pasien_tgllahir = date('Y-m-d',strtotime($_POST["pasien_tgllahir"]));


	$date=date('Y-m-d', strtotime($tgl_lahir));

	
	
	
	$query = pg_query($con, "select reg_id,id_poli,reg_tanggal,jenis_nama, reg_kode_trans,cust_usr_id, cust_usr_nama,cust_usr_kode, cust_usr_tanggal_lahir,poli_nama, reg_tipe_rawat
	 from global.global_customer_user a left join klinik.klinik_registrasi b 
	on a.cust_usr_id=b.id_cust_usr left join global.global_auth_poli c on c.poli_id=b.id_poli
	left join global.global_jenis_pasien d on d.jenis_id=b.reg_jenis_pasien
	 WHERE id_poli!='33' and reg_utama is null and id_poli!='b1b99707e536adf5e57daede3576bb0f' and cust_usr_kode='20143184' and cust_usr_tanggal_lahir='2019-11-12' order by reg_tanggal desc");
	
	$row = pg_fetch_array($query);
	
	if (!empty($row)){
		$response = new usr();
		$response->value =202;
		$response->message = "RM Anda Terdaftar Sebagai ".$row['cust_usr_kode'];
		$response->pasien_rm = $row['cust_usr_kode'];
		$response->cust_usr_id = $row['cust_usr_id'];
		$response->pasien_nama = $row['cust_usr_nama'];

		$response->jenis_nama = $row['jenis_nama'];
		$response->poli_nama = $row['poli_nama'];
		$response->pasien_tgllahir =date('d F Y',strtotime( $row['cust_usr_tanggal_lahir']));
		$response->reg_tanggal=date('d F Y',strtotime( $row['reg_tanggal']));
	


		
		die(json_encode($response));
		
	} else { 
		$response = new usr();
		$response->value = 404;
		$response->message = "No RM Tidak Valid Atau Belum Terdaftar Silahkan Cek Kembali";
		die(json_encode($response));
	}
	
	
	pg_close($con);

  

}

	





 ?>