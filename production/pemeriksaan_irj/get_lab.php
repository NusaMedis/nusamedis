<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
	 require_once($LIB."login.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $tglSekarang = date("Y-m-d");
	 $auth = new CAuth();
	 $userId = $auth->GetUserId();
     
	if(isset($_GET['reg_id'])){
		$sql = "select 
		a.id_dep,a.reg_id,a.reg_status_kondisi,a.id_pembayaran,a.id_cust_usr,a.reg_rujukan_id,a.reg_tingkat_kegawatan,a.reg_tanggal,a.id_poli,a.reg_jenis_pasien,
		b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_id,b.cust_usr_nama,b.cust_usr_alamat,
		c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id,
		h.rawat_anamnesa,h.rawat_keluhan,h.rawat_keluhan,h.rawat_catatan,h.rawat_pemeriksaan_fisik,h.rawat_diagnosa_utama,h.rawat_ket,
		a.id_dokter, i.id_dokter_lab
		from
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		left join global.global_auth_poli c on a.id_poli = c.poli_id 
		left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
		left join global.global_shift e on e.shift_id = a.reg_shift
		left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
		left join global.global_auth_user g on g.usr_id = a.id_dokter
		left join klinik.klinik_perawatan h on h.id_reg = a.reg_id
		left join laboratorium.lab_pemeriksaan i on i.id_reg = a.reg_id
		where reg_id = '$_GET[reg_id]'";
		$sql .= " order by a.reg_when_update asc";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		// echo $sql;
		echo json_encode($dataTable); 
		//echo json_encode(array('errorMsg'=>'Some errors occured.'));
	}elseif(!isset($_GET['reg_id'])) {
if (isset($_POST['tgl_awal']) || isset($_POST['tgl_akhir'])) 
    {
    	 	$kondisi = " and a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST['tgl_awal']));
    	 	$kondisi .= " and a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST['tgl_akhir']));
	 }else {
    	 	$kondisi = "and a.reg_tanggal ='$tglSekarang'";
	 }  
  

		$sql = "select a.reg_id,a.reg_kode_trans,a.reg_tanggal,a.reg_waktu,a.reg_status,a.reg_tipe_jkn,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_nama,b.cust_usr_alamat,
    b.cust_usr_kode_tampilan,j.pemeriksaan_pasien_nama,b.cust_usr_tanggal_lahir,j.pemeriksaan_alamat,c.poli_nama, f.jenis_nama, e.jkn_nama,g.poli_nama as poli_asal, gedung_rawat_nama from 
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
		global.global_auth_poli c on a.id_poli = c.poli_id left join
		global.global_auth_user_poli d on a.id_poli = d.id_poli
		left join global.global_jkn e on a.reg_tipe_jkn = e.jkn_id
		left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
		left join global.global_auth_poli g on a.id_poli_asal = g.poli_id
		left join klinik.klinik_rawat_inap_history h on a.reg_utama = h.id_reg
		left join global.global_gedung_rawat i on h.rawat_inap_history_gedung_tujuan = i.gedung_rawat_id
		left join laboratorium.lab_pemeriksaan j on j.id_reg = a.reg_id";
		$sql .= " where c.poli_tipe = 'L' and b.cust_usr_id <> '100' and d.id_usr = '$userId'";
		$sql .= $kondisi;
		$sql .= " order by a.reg_when_update asc";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		$data = [];
		//echo $sql;
    for($i=0; $i < count($dataTable); $i++){    

      array_push($data, [
          'reg_kode_trans'   => $dataTable[$i]['reg_kode_trans'],
          'reg_id'   => $dataTable[$i]['reg_id'],
          'reg_tanggal'   => format_date($dataTable[$i]['reg_tanggal']),
          'reg_waktu'   => $dataTable[$i]['reg_waktu'],
          'reg_status'   => $dataTable[$i]['reg_status'],
          'reg_tipe_jkn'   => $dataTable[$i]['reg_tipe_jkn'],
          'id_pembayaran'   => $dataTable[$i]['id_pembayaran'],
          'cust_usr_id'   => $dataTable[$i]['cust_usr_id'],
          'cust_usr_kode'   => $dataTable[$i]['cust_usr_kode'],
          'cust_usr_kode_tampilan'   => $dataTable[$i]['cust_usr_kode_tampilan'],
          'cust_usr_nama'   => $dataTable[$i]['cust_usr_nama'],
          'cust_usr_tanggal_lahir'   => format_date($dataTable[$i]['cust_usr_tanggal_lahir']),
          'cust_usr_alamat'   => $dataTable[$i]['cust_usr_alamat'],
          'poli_nama'   => $dataTable[$i]['poli_nama'],
          'jenis_nama'   => $dataTable[$i]['jenis_nama'],
          'jkn_nama'   => $dataTable[$i]['jkn_nama'],
          'perusahaan_nama'   => $dataTable[$i]['perusahaan_nama'],
          'reg_status_pasien'   => $dataTable[$i]['reg_status_pasien'],
        ]);
    }
		echo json_encode($data);
	} 
?>