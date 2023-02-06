<?php

   // LIBRARY
     require_once("../penghubung.inc.php");
     require_once("../lib/dataaccess.php");

     $dtaccess = new DataAccess();
   
	 $sql = "select * from global.global_customer_user where cust_usr_id = '$_POST[usr_id]'";    
     $rs = $dtaccess->Execute($sql);
     $row = $dtaccess->Fetch($rs);
	 
		# Tanggal Lahir
		$birthday = $row['cust_usr_tanggal_lahir'];	
		# Convert Ke Date Time
		$biday = new DateTime( $birthday );
		$today = new DateTime();
		$diff = $today->diff($biday);	
		$tahun = $diff->y;
		$bulan = $diff->m;
		$hari = $diff->d;
		
	 echo json_encode(array(
				"cust_usr_kode" => $row['cust_usr_kode'],
				"cust_usr_nama" => $row['cust_usr_nama'],
				"cust_usr_tempat_lahir"=> $row['cust_usr_tempat_lahir'],
				"cust_usr_tanggal_lahir"=> $row['cust_usr_tanggal_lahir'],
				"cust_usr_umur_tahun"=>$tahun,
				"cust_usr_umur_bulan"=>$bulan,
				"cust_usr_umur_hari"=>$hari,
				"cust_usr_jenis_kelamin"=> $row['cust_usr_jenis_kelamin'],
				"cust_usr_agama"=> $row['cust_usr_agama'],
				"cust_usr_alamat"=> $row['cust_usr_alamat'],
				"cust_usr_dusun"=> $row['cust_usr_dusun'],
				"cust_usr_no_identitas"=> $row['cust_usr_no_identitas'],
				"id_prop"=> $row['id_prop'],
				"id_kota"=> $row['id_kota'],
				"id_kecamatan"=> $row['id_kecamatan'],
				"id_kelurahan"=> $row['id_kelurahan'],
				"id_pendidikan"=> $row['id_pendidikan'],
				"id_pekerjaan"=> $row['id_pekerjaan'],
				"cust_usr_asal_negara"=> $row['cust_usr_asal_negara'],
				"id_status_perkawinan"=> $row['id_status_perkawinan'],
				"cust_usr_penanggung_jawab"=> $row['cust_usr_penanggung_jawab'],
				"cust_usr_penanggung_jawab_status"=> $row['cust_usr_penanggung_jawab_status'],
				"cust_usr_no_hp"=> $row['cust_usr_no_hp'],
				"cust_usr_id"=> $row['cust_usr_id'],
				"cust_usr_gol_darah"=> $row['cust_usr_gol_darah'],
				"cust_usr_gol_darah_resus"=> $row['cust_usr_gol_darah_resus'],
			));

//echo json_encode($row);
?>