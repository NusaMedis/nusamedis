<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."datamodel.php");
     
     //INISIALISASI LIBRARY
     $dtaccess = new DataAccess();
	 $tglSekarang = date("Y-m-d");
     
	if(isset($_GET['reg_id'])){
/*		$sql = "select 
		a.id_dep,a.reg_id,a.reg_utama,a.reg_status_kondisi,a.id_pembayaran,a.id_cust_usr,a.reg_rujukan_id,a.reg_tanggal,a.id_poli,a.id_dokter,a.reg_jenis_pasien,
		b.cust_usr_kode,b.cust_usr_nama,b.cust_usr_alamat,
		c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id from
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		left join global.global_auth_poli c on a.id_poli = c.poli_id 
		left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
		left join global.global_shift e on e.shift_id = a.reg_shift
		left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
		left join global.global_auth_user g on g.usr_id = a.id_dokter
		where c.poli_tipe='L' and reg_id = '$_GET[reg_id]'";   */
		$sql = "select 
		a.id_dep,a.reg_id,a.reg_status_kondisi,a.id_pembayaran,a.id_cust_usr,a.reg_rujukan_id,a.reg_tanggal,a.id_poli,a.id_dokter,a.reg_jenis_pasien,
		b.cust_usr_kode,b.cust_usr_id,b.cust_usr_nama,b.cust_usr_alamat,
		c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id from
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
		left join global.global_auth_poli c on a.id_poli = c.poli_id 
		left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
		left join global.global_shift e on e.shift_id = a.reg_shift
		left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
		left join global.global_auth_user g on g.usr_id = a.id_dokter
		where c.poli_tipe='L' and reg_id = '$_GET[reg_id]'";
    
		$sql .= " order by a.reg_when_update desc";
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		echo json_encode($dataTable); 
		//echo json_encode(array('errorMsg'=>'Some errors occured.'));
	}elseif(!isset($_GET['reg_id'])) {
/*		$sql = "select a.reg_id,a.reg_utama,a.reg_status,a.reg_tanggal,a.reg_waktu,b.cust_usr_kode,
		b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,
		d.kamar_nama,d.kamar_id,e.bed_kode,f.kelas_nama
		from 
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
		left join klinik.klinik_rawatinap c on c.id_reg = a.reg_id
		left join klinik.klinik_kamar d on d.kamar_id = c.id_kamar
        left join klinik.klinik_kamar_bed e on e.bed_id = c.id_bed
		left join klinik.klinik_kelas f on f.kelas_id = c.id_kategori_kamar
        left join global.global_auth_poli g on g.poli_id = a.id_poli
		"; */
$sql = "select a.reg_id,a.reg_waktu,a.reg_status,a.id_pembayaran,b.cust_usr_id,b.cust_usr_kode,b.cust_usr_nama,b.cust_usr_tanggal_lahir,b.cust_usr_alamat,c.poli_nama from 
		klinik.klinik_registrasi a left join 
		global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join
		global.global_auth_poli c on a.id_poli = c.poli_id";
//		$sql .= " where  a.reg_tipe_rawat='J' and reg_tanggal ='$tglSekarang'";
		    
		$sql .= " where  c.poli_tipe='L' and reg_tanggal ='$tglSekarang'";
		$sql .= " order by a.reg_when_update desc";
    //echo $sql;
		$rs = $dtaccess->Execute($sql);
		$dataTable = $dtaccess->FetchAll($rs); 
		echo json_encode($dataTable);
	} 
?>