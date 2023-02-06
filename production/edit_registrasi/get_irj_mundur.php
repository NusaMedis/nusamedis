<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $tglSekarang = date("Y-m-d");
	  
	if(isset($_GET['reg_id'])){
		$sql = "select a.*,b.id_kelurahan as kelurahan,b.id_kecamatan as kecamatan,b.id_kota as kota,b.*,c.poli_nama,c.poli_id,d.id_kategori_kamar,d.id_kamar,d.id_bed,e.id_gedung_rawat, a.id_perusahaan from 
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
		global.global_auth_poli c on a.id_poli = c.poli_id 
		left join klinik.klinik_rawatinap d on a.reg_id = d.id_reg
		left join klinik.klinik_kamar e on d.id_kamar = e.kamar_id and d.id_kategori_kamar = e.id_kelas 
		where (a.reg_status = 'E0' or a.reg_status = 'I0' or a.reg_status = 'R0' or a.reg_status = 'G0'or a.reg_status = 'G1'
		or a.reg_status = 'E1' or a.reg_status = 'R1') and a.reg_id = '$_GET[reg_id]'";
		$sql .= " order by a.reg_when_update desc";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		echo json_encode($dataTable); 

	}elseif(!isset($_GET['reg_id'])) {
		if (isset($_POST['tgl_awal']) || isset($_POST['tgl_akhir'])) 
		{
				$kondisi = "  a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST['tgl_awal']));
				$kondisi .= " and a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST['tgl_akhir']));
		}else {
				$kondisi = "a.reg_tanggal =".QuoteValue(DPE_DATE,date_db($tglSekarang));
		}  
		
		$sql = "select a.reg_id,a.reg_tanggal,a.reg_kode_trans,a.reg_status,a.id_cust_usr,a.reg_waktu,b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama,c.poli_id,d.jkn_nama,f.jenis_nama, perusahaan_nama,h.poli_nama as poli_asal, usr_name,a.id_dokter from 
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
		global.global_auth_poli c on a.id_poli = c.poli_id
		left join global.global_jkn d on a.reg_tipe_jkn = d.jkn_id
		left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    	left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
		left join global.global_auth_poli h on a.id_poli_asal = h.poli_id
		left join global.global_auth_user i on i.usr_id = a.id_dokter ";
		$sql .= "where ".$kondisi;
		$sql .= " and (a.reg_status = 'E0' or a.reg_status = 'I0' or a.reg_status = 'R0' or a.reg_status = 'G0'or a.reg_status = 'G1'
		or a.reg_status = 'E1' or a.reg_status = 'R1') and cust_usr_kode != '100'";
		//$sql .= " where '$tglSekarang' > a.reg_tanggal";
		$sql .= " order by a.id_poli,a.reg_no_antrian asc";
		
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		//echo json_encode($dataTable);
		$data = [];

	    for($i=0; $i < count($dataTable); $i++){    
	    	$namaPasien = str_replace("*","'",$dataTable[$i]['cust_usr_nama']);

	      array_push($data, [
	          'reg_kode_trans'   => $dataTable[$i]['reg_kode_trans'],
	          'reg_id'   => $dataTable[$i]['reg_id'],
	          'reg_status'   => $dataTable[$i]['reg_status'],
	          'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
	          'reg_waktu'   => $dataTable[$i]['reg_waktu'],
			  'reg_status'   => $dataTable[$i]['reg_status'],
			  'reg_umur'   => $dataTable[$i]['reg_umur'],
			  'reg_umur_hari'   => $dataTable[$i]['reg_umur_hari'],
			  'reg_umur_bulan'   => $dataTable[$i]['reg_umur_bulan'],
	          'reg_tipe_jkn'   => $dataTable[$i]['reg_tipe_jkn'],
	          'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
			  'id_cust_usr'   => $dataTable[$i]['id_cust_usr'],
			  'cust_usr_tempat_lahir'   => $dataTable[$i]['cust_usr_tempat_lahir'],
	          'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
	          'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode'],
	          'cust_usr_nama'   => $namaPasien,
	          'cust_usr_tanggal_lahir'   => format_date($dataTable[$i]['cust_usr_tanggal_lahir']),
			  'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
			  'cust_usr_jenis_kelamin'   => $dataTable[$i]['cust_usr_jenis_kelamin'],
			  'cust_usr_agama'=>$dataTable[$i]['cust_usr_agama'],
			  'cust_usr_alergi'=>$dataTable[$i]['cust_usr_alergi'],
			  'cust_usr_gol_darah_resus' => $dataTable[$i]['cust_usr_gol_darah_resus'],
			  'id_kelurahan' => $dataTable[$i]['kelurahan'],
			  'id_kecamatan' => $dataTable[$i]['kecamatan'],
			  'id_kota' => $dataTable[$i]['kota'],
			  'cust_usr_no_hp'=>$dataTable[$i]['cust_usr_no_hp'],
			  'cust_usr_no_hp'=>$dataTable[$i]['cust_usr_no_hp'],
			  'cust_usr_no_identitas' =>$dataTable[$i]['cust_usr_no_identitas'],
			  'id_card' =>$dataTable[$i]['id_card'],
			  'cust_usr_pekerjaan' =>$dataTable[$i]['cust_usr_pekerjaan'],
	          'poli_nama'   => $dataTable[$i]['poli_nama'],
	          'poli_id'   => $dataTable[$i]['poli_id'],
	          'jenis_nama'   => $dataTable[$i]['jenis_nama'],
	          'jkn_nama'   => $dataTable[$i]['jkn_nama'],
	          'perusahaan_nama'   => $dataTable[$i]['perusahaan_nama'],
	          'poli_asal'   => $dataTable[$i]['poli_asal'],
	          'dokter'   => $dataTable[$i]['usr_name'],
	          'id_dokter'   => $dataTable[$i]['id_dokter']
	        ]);
	    }

	    echo json_encode($data);
	} 
	  

?>