<?php
    require_once("../../penghubung.inc.php");
    require_once($LIB."bit.php");
    require_once($LIB."login.php");
    require_once($LIB."encrypt.php");
    require_once($LIB."datamodel.php");
    require_once($LIB."dateLib.php");
    require_once($LIB."currency.php");
    
     require_once($LIB."tampilan.php");   
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']); 
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();
    $err_code = 0;
    $auth = new CAuth();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();

    /* if(!$auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ) && !$auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ)===1 || $auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	
	if(!$_POST["x_mode"]) 	{
		if(strtoupper($_GET["status"])=='N') $_x_mode = "New";
		else $_x_mode = "Edit";
	} else $_x_mode = & $_POST["x_mode"] ;
   	
	if($_POST["plamar_id"])  $plamarId = & $_POST["plamar_id"];
 	if($_POST["pgw_id"])  $pgwId = & $_POST["pgw_id"];
	if($_GET["nama"])$_POST["nama"]=$_GET["nama"];
	if ($_GET["id"]) {
		$pgwId = $enc->Decode($_GET["id"]);
		$sql = "select a.*,struk_nama from hris.hris_pegawai a
				left join hris.hris_struktural b on b.struk_id = a.id_struk
				where pgw_id ='".$pgwId."'";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$row_edit = $dtaccess->Fetch($rs_edit);
		$dtaccess->Clear($rs_edit);
		$_POST["pgw_id"] = $row_edit["pgw_id"]; 
		$pgwId=$_POST["pgw_id"];
		$_POST["pgw_kode"] = $row_edit["pgw_kode"]; 
		$_POST["struk_nama"] = $row_edit["struk_nama"]; 
		$_POST["pgw_nip"] = $row_edit["pgw_nip"]; 
		$_POST["pgw_initial"] = $row_edit["pgw_initial"]; 
		$_POST["pgw_nama"] = $row_edit["pgw_nama"]; 
		$_POST["pgw_nama_panggilan"] = $row_edit["pgw_nama_panggilan"]; 
		$_POST["pgw_tempat_lahir"] = $row_edit["pgw_tempat_lahir"]; 
		$_POST["pgw_tanggal_lahir"] = format_date($row_edit["pgw_tanggal_lahir"]); 
		$_POST["pgw_jenis_kelamin"] = $row_edit["pgw_jenis_kelamin"]; 
		$_POST["pgw_status_nikah"] = $row_edit["pgw_status_nikah"]; 
		$_POST["pgw_agama"] = $row_edit["pgw_agama"]; 
		$_POST["pgw_warganegara"] = $row_edit["pgw_warganegara"]; 
		if($_POST["pgw_warganegara"]!="WNI" && $_POST["pgw_warganegara"]!="WNI Keturunan") $_POST["wna"] = $_POST["pgw_warganegara"];
		$_POST["pgw_suku_bangsa"] = $row_edit["pgw_suku_bangsa"]; 
		$_POST["pgw_ktp_no"] = $row_edit["pgw_ktp_no"]; 
		$_POST["pgw_passport_no"] = $row_edit["pgw_passport_no"]; 
		$_POST["pgw_golongan_darah"] = $row_edit["pgw_golongan_darah"]; 
		$_POST["pgw_nama_bank"] = $row_edit["pgw_nama_bank"]; 
		$_POST["pgw_no_rekening"] = $row_edit["pgw_no_rekening"]; 
		$_POST["pgw_alamat_asal"] = $row_edit["pgw_alamat_asal"]; 
		$_POST["pgw_kota_asal"] = $row_edit["pgw_kota_asal"]; 
		$_POST["pgw_telp_asal"] = $row_edit["pgw_telp_asal"]; 
		$_POST["pgw_hp_asal"] = $row_edit["pgw_hp_asal"]; 
		$_POST["pgw_alamat_surat"] = $row_edit["pgw_alamat_surat"]; 
		$_POST["pgw_telp_surat"] = $row_edit["pgw_telp_surat"]; 
		$_POST["pgw_alamat_surabaya"] = $row_edit["pgw_alamat_surabaya"]; 
		$_POST["pgw_telp_surabaya"] = $row_edit["pgw_telp_surabaya"]; 
		$_POST["pgw_telp_hp"] = $row_edit["pgw_telp_hp"]; 
		$_POST["pgw_kontak_darurat"] = $row_edit["pgw_kontak_darurat"]; 
		$_POST["pgw_kontak_darurat_telp"] = $row_edit["pgw_kontak_darurat_telp"]; 
		$_POST["pgw_kontak_darurat_hubungan"] = $row_edit["pgw_kontak_darurat_hubungan"]; 
		$_POST["pgw_kontak_darurat_hp"] = $row_edit["pgw_kontak_darurat_hp"]; 
		$_POST["pgw_nama_ayah"] = $row_edit["pgw_nama_ayah"]; 
		$_POST["pgw_ayah_masih_hidup"] = $row_edit["pgw_ayah_masih_hidup"]; 
		$_POST["pgw_nama_ibu"] = $row_edit["pgw_nama_ibu"]; 
		$_POST["pgw_ibu_masih_hidup"] = $row_edit["pgw_ibu_masih_hidup"]; 
		$_POST["pgw_alamat_ortu"] = $row_edit["pgw_alamat_ortu"]; 
		$_POST["pgw_kota_ortu"] = $row_edit["pgw_kota_ortu"]; 
		$_POST["pgw_telp_ortu"] = $row_edit["pgw_telp_ortu"]; 
		$_POST["pgw_nama_ayah_mertua"] = $row_edit["pgw_nama_ayah_mertua"]; 
		$_POST["pgw_ayah_mertua_masih_hidup"] = $row_edit["pgw_ayah_mertua_masih_hidup"]; 
		$_POST["pgw_nama_ibu_mertua"] = $row_edit["pgw_nama_ibu_mertua"]; 
		$_POST["pgw_ibu_mertua_masih_hidup"] = $row_edit["pgw_ibu_mertua_masih_hidup"]; 
		$_POST["pgw_alamat_mertua"] = $row_edit["pgw_alamat_mertua"]; 
		$_POST["pgw_kota_mertua"] = $row_edit["pgw_kota_mertua"]; 
		$_POST["pgw_telp_mertua"] = $row_edit["pgw_telp_mertua"]; 
		$_POST["pgw_nama_suami_istri"] = $row_edit["pgw_nama_suami_istri"]; 
		$_POST["pgw_panggilan_suami_istri"] = $row_edit["pgw_panggilan_suami_istri"]; 
		$_POST["pgw_kota_lahir_suami_istri"] = $row_edit["pgw_kota_lahir_suami_istri"]; 
		$_POST["pgw_tanggal_lahir_suami_istri"] = format_date($row_edit["pgw_tanggal_lahir_suami_istri"]); 
		$_POST["pgw_status_kerja_suami_istri"] = $row_edit["pgw_status_kerja_suami_istri"]; 
		$_POST["pgw_instansi_suami_istri"] = $row_edit["pgw_instansi_suami_istri"]; 
		$_POST["pgw_alamat_instansi_suami_istri"] = $row_edit["pgw_alamat_instansi_suami_istri"]; 
		$_POST["pgw_kota_instansi_suami_istri"] = $row_edit["pgw_kota_instansi_suami_istri"]; 
		$_POST["pgw_telp_instansi_suami_istri"] = $row_edit["pgw_telp_instansi_suami_istri"]; 
		$_POST["pgw_sd_nama"] = $row_edit["pgw_sd_nama"]; 
		$_POST["pgw_sd_kota"] = $row_edit["pgw_sd_kota"]; 
		$_POST["pgw_sd_tanggal_lulus"] = format_date($row_edit["pgw_sd_tanggal_lulus"]); 
		$_POST["pgw_sd_no_ijasah"] = $row_edit["pgw_sd_no_ijasah"]; 
		$_POST["pgw_sltp_nama"] = $row_edit["pgw_sltp_nama"]; 
		$_POST["pgw_sltp_kota"] = $row_edit["pgw_sltp_kota"]; 
		$_POST["pgw_sltp_tanggal_lulus"] = format_date($row_edit["pgw_sltp_tanggal_lulus"]); 
		$_POST["pgw_sltp_no_ijasah"] = $row_edit["pgw_sltp_no_ijasah"]; 
		$_POST["pgw_slta_nama"] = $row_edit["pgw_slta_nama"]; 
		$_POST["pgw_slta_kota"] = $row_edit["pgw_slta_kota"]; 
		$_POST["pgw_slta_tanggal_lulus"] = format_date($row_edit["pgw_slta_tanggal_lulus"]); 
		$_POST["pgw_slta_no_ijasah"] = $row_edit["pgw_slta_no_ijasah"]; 
		$_POST["pgw_diploma_nama"] = $row_edit["pgw_diploma_nama"]; 
		$_POST["pgw_diploma_pt_asal"] = $row_edit["pgw_diploma_pt_asal"]; 
		$_POST["pgw_diploma_kota"] = $row_edit["pgw_diploma_kota"]; 
		$_POST["pgw_diploma_bidang_ilmu"] = $row_edit["pgw_diploma_bidang_ilmu"]; 
		$_POST["pgw_diploma_tanggal_lulus"] = format_date($row_edit["pgw_diploma_tanggal_lulus"]); 
		$_POST["pgw_diploma_no_ijasah"] = $row_edit["pgw_diploma_no_ijasah"]; 
		$_POST["pgw_diploma_gelar"] = $row_edit["pgw_diploma_gelar"]; 
		$_POST["pgw_diploma_ipk"] = $row_edit["pgw_diploma_ipk"]; 
		$_POST["pgw_s1_nama"] = $row_edit["pgw_s1_nama"]; 
		$_POST["pgw_s1_pt_asal"] = $row_edit["pgw_s1_pt_asal"]; 
		$_POST["pgw_s1_kota"] = $row_edit["pgw_s1_kota"]; 
		$_POST["pgw_s1_bidang_ilmu"] = $row_edit["pgw_s1_bidang_ilmu"]; 
		$_POST["pgw_s1_tanggal_lulus"] = format_date($row_edit["pgw_s1_tanggal_lulus"]); 
		$_POST["pgw_s1_no_ijasah"] = $row_edit["pgw_s1_no_ijasah"]; 
		$_POST["pgw_s1_gelar"] = $row_edit["pgw_s1_gelar"]; 
		$_POST["pgw_s1_ipk"] = $row_edit["pgw_s1_ipk"]; 
		$_POST["pgw_s2_nama"] = $row_edit["pgw_s2_nama"]; 
		$_POST["pgw_s2_pt_asal"] = $row_edit["pgw_s2_pt_asal"]; 
		$_POST["pgw_s2_kota"] = $row_edit["pgw_s2_kota"]; 
		$_POST["pgw_s2_bidang_ilmu"] = $row_edit["pgw_s2_bidang_ilmu"]; 
		$_POST["pgw_s2_tanggal_lulus"] = format_date($row_edit["pgw_s2_tanggal_lulus"]); 
		$_POST["pgw_s2_no_ijasah"] = $row_edit["pgw_s2_no_ijasah"]; 
		$_POST["pgw_s2_gelar"] = $row_edit["pgw_s2_gelar"]; 
		$_POST["pgw_s2_ipk"] = $row_edit["pgw_s2_ipk"]; 
		$_POST["pgw_s3_nama"] = $row_edit["pgw_s3_nama"]; 
		$_POST["pgw_s3_pt_asal"] = $row_edit["pgw_s3_pt_asal"]; 
		$_POST["pgw_s3_kota"] = $row_edit["pgw_s3_kota"]; 
		$_POST["pgw_s3_bidang_ilmu"] = $row_edit["pgw_s3_bidang_ilmu"]; 
		$_POST["pgw_s3_tanggal_lulus"] = format_date($row_edit["pgw_s3_tanggal_lulus"]); 
		$_POST["pgw_s3_no_ijasah"] = $row_edit["pgw_s3_no_ijasah"]; 
		$_POST["pgw_s3_gelar"] = $row_edit["pgw_s3_gelar"]; 
		$_POST["pgw_s3_ipk"] = $row_edit["pgw_s3_ipk"]; 
		$_POST["pgw_gelar_muka"] = $row_edit["pgw_gelar_muka"]; 
		$_POST["pgw_gelar_belakang"] = $row_edit["pgw_gelar_belakang"]; 
		$_POST["pgw_bidang_keahlian"] = $row_edit["pgw_bidang_keahlian"]; 
		$_POST["pgw_gelar_tertinggi"] = $row_edit["pgw_gelar_tertinggi"]; 
		$_POST["pgw_pendidikan_tertinggi"] = $row_edit["pgw_pendidikan_tertinggi"]; 
		$_POST["pgw_akta_v"] = $row_edit["pgw_akta_v"]; 
		$_POST["pgw_status"] = $row_edit["pgw_status"]; 
		$_POST["pgw_pangkat_diterima"] = $row_edit["pgw_pangkat_diterima"]; 
		$_POST["id_gol"] = $row_edit["id_gol"]; 
		$_POST["pgw_jenis_pegawai"] = $row_edit["pgw_jenis_pegawai"]; 
		$_POST["pgw_tanggal_masuk"] = format_date($row_edit["pgw_tanggal_masuk"]); 
		$_POST["pgw_no_sk_pangkat"] = $row_edit["pgw_no_sk_pangkat"]; 
		$_POST["pgw_tmt_pangkat"] = format_date($row_edit["pgw_tmt_pangkat"]); 
		$_POST["pgw_tanggal_habis_sk"] = format_date($row_edit["pgw_tanggal_habis_sk"]); 
		$_POST["pgw_tanggal_keluar"] = format_date($row_edit["pgw_tanggal_keluar"]); 
		$_POST["pgw_alasan_keluar"] = $row_edit["pgw_alasan_keluar"]; 
		$_POST["pgw_jabatan_struktural"] = $row_edit["pgw_jabatan_struktural"]; 
		$_POST["pgw_no_sk_jab_struktural"] = $row_edit["pgw_no_sk_jab_struktural"]; 
		$_POST["pgw_jab_akademik"] = $row_edit["pgw_jab_akademik"]; 
		$_POST["pgw_no_sk_jab_akademik"] = $row_edit["pgw_no_sk_jab_akademik"];		
		$_POST["pgw_tanggal_sk_akademik"] = $row_edit["pgw_tanggal_sk_akademik"];
		$_POST["pgw_tmt_akademik"] = $row_edit["pgw_tmt_akademik"];
    $_POST["pgw_dp3_terakhir"] = $row_edit["pgw_dp3_terakhir"]; 
		$_POST["pgw_dp3_berikutnya"] = $row_edit["pgw_dp3_berikutnya"]; 
		$_POST["pgw_status_kerja"] = $row_edit["pgw_status_kerja"]; 
		$_POST["pgw_mulai_semester"] = $row_edit["pgw_mulai_semester"]; 
		$_POST["pgw_base_program_studi"] = $row_edit["pgw_base_program_studi"]; 
		$_POST["pgw_instansi_asal"] = $row_edit["pgw_instansi_asal"]; 
		$_POST["pgw_nip_pns"] = $row_edit["pgw_nip_pns"]; 
		$_POST["pgw_surat_ijin_mengajar"] = $row_edit["pgw_surat_ijin_mengajar"]; 
		$_POST["pgw_masa_kerja"] = $row_edit["pgw_masa_kerja"]; 
		$_POST["pgw_tipe"] = $row_edit["pgw_tipe"]; 
		$_POST["id_struk"] = $row_edit["id_struk"]; 
		$_POST["pgw_foto"] = $row_edit["pgw_foto"]; 
		$_POST["pgw_masa_kerja_golongan"] = $row_edit["pgw_masa_kerja_golongan"]; 
		$_POST["pgw_masa_kerja_diterima"] = $row_edit["pgw_masa_kerja_diterima"]; 
		$_POST["pgw_plafon_rawat_inap"] = $row_edit["pgw_plafon_rawat_inap"]; 
		$_POST["pgw_plafon_rawat_jalan"] = $row_edit["pgw_plafon_rawat_jalan"]; 
		$_POST["pgw_email"] = $row_edit["pgw_email"]; 
		$_POST["pgw_situs"] = $row_edit["pgw_situs"]; 
      $_POST["pgw_no_ac"] = $row_edit["pgw_no_ac"];
    $jamMasuk = explode(":",$row_edit["pgw_jam_masuk"]);
    $_POST["pgw_jam_masuk_jam"] = $jamMasuk[0];
    $_POST["pgw_jam_masuk_menit"] = $jamMasuk[1];
		$_POST["id_dep"] = $row_edit["id_dep"];
		//------DATA ANAK-----
		$sqlanak = "select * from hris.hris_pegawai_data_anak where id_pgw = '".$pgwId."' order by pgw_anak_id";
		$rsanak = $dtaccess->Execute($sqlanak,DB_SCHEMA_GLOBAL);
		$rowanak = $dtaccess->FetchAll($rsanak);
		$dtaccess->Clear($rsanak);
		for($i=0,$n=count($rowanak);$i<$n;$i++) {	
			$_POST["pgw_anak_nama"][$i+1] = $rowanak[$i]["pgw_anak_nama"];
			$_POST["pgw_anak_jenis_kelamin"][$i+1] = $rowanak[$i]["pgw_anak_jenis_kelamin"];
			$_POST["pgw_anak_kota_lahir"][$i+1] = $rowanak[$i]["pgw_anak_kota_lahir"];
			$_POST["pgw_anak_tanggal_lahir"][$i+1] = format_date($rowanak[$i]["pgw_anak_tanggal_lahir"]);
			$_POST["pgw_anak_pendidikan"][$i+1] = $rowanak[$i]["pgw_anak_pendidikan"];
			$_POST["pgw_anak_kerja"][$i+1] = $rowanak[$i]["pgw_anak_kerja"];
			$_POST["pgw_anak_nikah"][$i+1] = $rowanak[$i]["pgw_anak_nikah"];
		}
		//------DATA bahasa asing-----
		$sqlBhs = "select * from hris.hris_pegawai_bahasa_asing where id_pgw = '".$pgwId."' order by pgw_bhs_id";
		$rsBhs = $dtaccess->Execute($sqlBhs,DB_SCHEMA_GLOBAL);
		$rowBhs = $dtaccess->FetchAll($rsBhs);
		$dtaccess->Clear($rsBhs);

		for($i=0,$n=count($rowBhs);$i<$n;$i++) {	
			$_POST["pgw_bhs_id"][($i+1)] = $rowBhs[$i]["pgw_bhs_id"];
			$_POST["pgw_bhs_nama"][($i+1)] = $rowBhs[$i]["pgw_bhs_nama"];
			$_POST["pgw_bhs_tulis"][($i+1)] = $rowBhs[$i]["pgw_bhs_tulis"];
			$_POST["pgw_bhs_lisan"][($i+1)] = $rowBhs[$i]["pgw_bhs_lisan"];
			$_POST["pgw_bhs_tahun"][($i+1)] = $rowBhs[$i]["pgw_bhs_tahun"];
		}
		//------DATA kursus-----
		$sqlKur = "select * from hris.hris_pegawai_kursus where id_pgw = '".$pgwId."' order by pgw_kursus_id";
		$rsKur = $dtaccess->Execute($sqlKur,DB_SCHEMA_GLOBAL);
		$rowKur = $dtaccess->FetchAll($rsKur);
		$dtaccess->Clear($rsKur);

		for($i=0,$n=count($rowKur);$i<$n;$i++) {	
			$_POST["pgw_kursus_id"][($i+1)] = $rowKur[$i]["pgw_kursus_id"];
			$_POST["pgw_kursus_nama"][($i+1)] = $rowKur[$i]["pgw_kursus_nama"];
			$_POST["pgw_kursus_tanggal_selesai"][($i+1)] = format_date($rowKur[$i]["pgw_kursus_tanggal_selesai"]);
			$_POST["pgw_kursus_materi"][($i+1)] = $rowKur[$i]["pgw_kursus_materi"];
		}
		//------DATA pengalaman kerja-----
		$sqlPkerja = "select * from hris.hris_pegawai_pengalaman where id_pgw = '".$pgwId."' order by pgw_pengalaman_id";
		$rsPkerja = $dtaccess->Execute($sqlPkerja,DB_SCHEMA_GLOBAL);
		$rowPkerja = $dtaccess->FetchAll($rsPkerja);
		$dtaccess->Clear($rsPkerja);

		for($i=0,$n=count($rowPkerja);$i<$n;$i++) {	
			$_POST["pgw_pengalaman_nama"][($i+1)] = $rowPkerja[$i]["pgw_pengalaman_nama"];
			$_POST["pgw_pengalaman_lama_kerja"][($i+1)] = $rowPkerja[$i]["pgw_pengalaman_lama_kerja"];
			$_POST["pgw_pengalaman_tanggal_masuk"][($i+1)] = format_date($rowPkerja[$i]["pgw_pengalaman_tanggal_masuk"]);
			$_POST["pgw_pengalaman_tanggal_keluar"][($i+1)] = format_date($rowPkerja[$i]["pgw_pengalaman_tanggal_keluar"]);
			$_POST["pgw_pengalaman_golongan"][($i+1)] = $rowPkerja[$i]["pgw_pengalaman_golongan"];
			$_POST["pgw_pengalaman_alasan_keluar"][($i+1)] = $rowPkerja[$i]["pgw_pengalaman_alasan_keluar"];
		}

		//------DATA pengalaman kerja-----
		$sqlOrgProf = "select * from hris.hris_pegawai_organisasi_profesi where id_pgw = '".$pgwId."' order by pgw_org_prof_id";
		$rsOrgProf = $dtaccess->Execute($sqlOrgProf,DB_SCHEMA_GLOBAL);
		$rowOrgProf = $dtaccess->FetchAll($rsOrgProf);
		$dtaccess->Clear($rsOrgProf);

		for($i=0,$n=count($rowOrgProf);$i<$n;$i++) {	
			$_POST["pgw_org_prof_nama"][($i+1)]= $rowOrgProf[$i]["pgw_org_prof_nama"];
			$_POST["pgw_org_prof_sebagai"][($i+1)]= $rowOrgProf[$i]["pgw_org_prof_sebagai"];
			$_POST["pgw_org_prof_tanggal_masuk"][($i+1)]= format_date($rowOrgProf[$i]["pgw_org_prof_tanggal_masuk"]);
			$_POST["pgw_org_prof_status"][($i+1)]= $rowOrgProf[$i]["pgw_org_prof_status"];
		}
	}
	$lokasi = $ROOT."gambar/foto_pegawai";
	if($_POST["pgw_foto"]) $fotoName = $lokasi."/".$_POST["pgw_foto"];
    else $fotoName = $lokasi."/default.jpg";

	// -- bagian privilege ----
    if($_x_mode=="New") $privMode = PRIV_CREATE;
    else $privMode = PRIV_UPDATE;

//     if(!$auth->IsAllowed("report_data_pegawai",$privMode)){
//         die("access_denied");
//         exit(1);
//     } 
    // -- end priv ---
   


	// -- cari agama ---
		$sql = "select * from hris.hris_agama order by agm_id";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataAgama = $dtaccess->FetchAll($rs);
	// -- cari golongan / pangkat--
		$sql = "select * from global.global_golongan";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$row_golongan = $dtaccess->FetchAll($rs_edit);
		$dtaccess->Clear($rs_edit);
	// -- cari masa kerja golongan--
		$sql = "select max(gaji_masa_kerja) from hris.hris_gaji_pokok";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
		$rowMasa = $dtaccess->Fetch($rs_edit);
		$dtaccess->Clear($rs_edit);
	// -- cari jabatan kontrak (status pegawai)
	    $sql = "select * from hris.hris_jabatan_kontrak";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
		$row_jab_kontrak = $dtaccess->FetchAll($rs_edit);
		$dtaccess->Clear($rs_edit);
	// -- cari jenis pegawai (administratif dsb)
	    $sql = "select * from hris.hris_master_jenis_posisi";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
		$rowJenisPegawai= $dtaccess->FetchAll($rs_edit);
		$dtaccess->Clear($rs_edit);
	// -- cari jab struk
	    $sql = "select * from hris.hris_jabatan_struktural";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
		$rowJabStruk= $dtaccess->FetchAll($rs_edit);
		$dtaccess->Clear($rs_edit);
	// -- cari jab akad
	    $sql = "select * from hris.hris_jabatan_akademik";
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
		$rowJabAkad= $dtaccess->FetchAll($rs_edit);
		$dtaccess->Clear($rs_edit);

?>

<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!--- currency function--->
<script language="JavaScript" type="text/javascript" src="<?php echo $ROOT;?>lib/script/func_curr.js"></script>
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $ROOT;?>lib/script/jscalendar/css/calendar-system.css" title="calendar-system" />
<!-- calendar script -->
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jscalendar/calendar.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/jscalendar/calendar-setup.js"></script>
<!-- end -->

</head>

<?php echo $view->RenderBody("module.css",true,true,"INFORMASI DATA PEGAWAI"); ?>
<body>
<div id="body">
<br>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tblForm">
   <tr>
        <td align="left" colspan=2 class="tablesmallheader">ENTRY INFORMASI PEGAWAI</td>
    </tr>
	<tr>
		<td align="left" HEIGHT="15" colspan=2 class="tablecontent-odd">
			<input readonly type="hidden" name="pgw_tipe" class="inputField" value="D">
		</td>
	</tr>
	<tr>
		<td align="left" colspan=2 class="tablesmallheader">DATA PRIBADI</td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tblForm">
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Kode Pegawai</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><input readonly type="text" class="inputField" name="pgw_kode" size="15" maxlength="15" value="<?php echo $_POST["pgw_kode"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
		<td class="tablecontent" rowspan="6">
			<img hspace="2" width="100" height="150" name="img_foto" src="<?php echo $fotoName;?>"  border="1" onDblClick="BukaWindow('pgw_pic.php?orifoto='+ document.frmEdit.pgw_foto.value + '&nama='+document.frmEdit.pgw_nip.value,'UploadFoto')">
			<input type="hidden" name="pgw_foto" value="<?php echo $_POST["pgw_foto"];?>">
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nomor Induk</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><input readonly type="text" class="inputField" name="pgw_nip" size="15" maxlength="15" value="<?php echo $_POST["pgw_nip"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nomor AC</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><input readonly type="text" class="inputField" name="pgw_no_ac" size="15" maxlength="15" value="<?php echo $_POST["pgw_no_ac"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Initial</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_initial" size="15" maxlength="15" value="<?php echo $_POST["pgw_initial"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Lengkap</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama"];?>"/></td>
	</tr>
	<tr>
			<td align="left" class="tablecontent-odd" cellspacing="0">Nama pangilan</td>
		<td align="left" class="tablecontent" cellspacing="0"><input readonly type="text" class="inputField" name="pgw_nama_panggilan" size="25" maxlength="50" value="<?php echo $_POST["pgw_nama_panggilan"];?>"/></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tblForm">
	<tr>
		<td width= "30%" class="tablecontent-odd">Tempat Lahir / Tanggal Lahir</td>
		<td width= "70%" class="tablecontent" ><input readonly type="text" class="inputField" name="pgw_tempat_lahir" size="15" maxlength="20" value="<?php echo $_POST["pgw_tempat_lahir"];?>"/> / 
						<input readonly type="text" class="inputField" id="pgw_tanggal_lahir" name="pgw_tanggal_lahir" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_lahir"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					 (dd-mm-yyy)
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Jenis Kelamin</td>
		<td width= "70%" class="tablecontent" >
			<SELECT NAME="pgw_jenis_kelamin"class="inputField">
				<option class="inputField" value="L" <?php if($_POST["pgw_jenis_kelamin"]=="L")echo "selected";?>>Laki-laki</option>
				<option class="inputField" value="P" <?php if($_POST["pgw_jenis_kelamin"]=="P")echo "selected";?>>Perempuan</option>
			</SELECT></td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Status Perkawinan</td>
		<td width= "70%"class="tablecontent" colspan="2">
			<input readonly type="radio" name="pgw_status_nikah" id="sty" class="inputField" value="y" <?php if($_POST["pgw_status_nikah"]=="y") echo "checked";?>><label for="sty">Menikah</label>&nbsp;
			<input readonly type="radio" name="pgw_status_nikah" id="stn" class="inputField" value="n" <?php if($_POST["pgw_status_nikah"]=="n") echo "checked";?>><label for="stn">Belum Menikah</label>
			<input readonly type="radio" name="pgw_status_nikah" id="stj" class="inputField" value="j" <?php if($_POST["pgw_status_nikah"]=="j") echo "checked";?>><label for="stj">Janda/Duda</label>
			<input readonly type="radio" name="pgw_status_nikah" id="std" class="inputField" value="t" <?php if($_POST["pgw_status_nikah"]=="t") echo "checked";?>><label for="std">Tunangan</label>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Agama</td><td class="tablecontent" colspan="2">
			<select name="pgw_agama" class="inputField">
				<?php for($i=0,$n=count($dataAgama);$i<$n;$i++){ ?>								
					<option class="inputField" value="<?php echo $dataAgama[$i]["agm_id"];?>" <?php if($dataAgama[$i]["agm_id"]==$_POST["pgw_agama"]) echo "selected"; ?>><?php echo $dataAgama[$i]["agm_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Kewarganegaraan</td>
		<td width= "70%"class="tablecontent" colspan="2">
			<input readonly type="radio" name="pgw_warganegara" id="wn1" class="inputField" value="WNI" <?php if($_POST["pgw_warganegara"]=="WNI" || !$_POST["pgw_warganegara"]) echo "checked";?> onClick="WargaNegara(this.form,this);"><label for="wn1" >WNI</label>&nbsp;

			<input readonly type="radio" name="pgw_warganegara" id="wn2" class="inputField" value="WNI Keturunan" <?php if($_POST["pgw_warganegara"]=="WNI Keturunan") echo "checked";?> onClick="WargaNegara(this.form,this);"><label for="wn2" >WNI Keturunan</label>&nbsp;

			<input readonly type="radio" name="pgw_warganegara" id="wn3" class="inputField" value="WNA" <?php if($_POST["pgw_warganegara"] && $_POST["pgw_warganegara"]!="WNI" && $_POST["pgw_warganegara"]!="WNI Keturunan") echo "checked";?> onClick="WargaNegara(this.form,this);"><label for="wn3">WNA</label> &nbsp;
			
			<input readonly type="text" name="wna" value="<?php echo $_POST["wna"];?>" class="<?php if($_POST["pgw_warganegara"] == "WNI" || $_POST["pgw_warganegara"] == "WNI Keturunan") echo "bDisable";else echo "inputField";?>" size="20" maxlength="100" <?php if($_POST["pgw_warganegara"] == "WNI" || $_POST["pgw_warganegara"] == "WNI Keturunan") echo "disabled";?>>
		</td>
	</tr>
	<TD class="tablecontent-odd" align="left">Suku Bangsa  </td>
	<td width= "70%"class="tablecontent"><input readonly type="text" class="inputField" name="pgw_suku_bangsa" size="15" maxlength="20" value="<?php echo $_POST["pgw_suku_bangsa"];?>"/></td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">No. KTP </td>
		<td width= "70%"class="tablecontent"><input readonly type="text" class="inputField" name="pgw_ktp_no" size="35" maxlength="50" value="<?php echo $_POST["pgw_ktp_no"];?>"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">No. Passport </td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_passport_no" size="35" maxlength="50" value="<?php echo $_POST["pgw_passport_no"];?>"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">Golongan Darah</td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_golongan_darah" size="10" maxlength="10" value="<?php echo $_POST["pgw_golongan_darah"];?>"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">Nama Bank</td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_nama_bank" size="35" maxlength="50" value="<?php echo $_POST["pgw_nama_bank"];?>"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">Nomor Rekening</td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_no_rekening" size="35" maxlength="50" value="<?php echo $_POST["pgw_no_rekening"];?>"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">Alamat Email</td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_email" size="35" maxlength="50" value="<?php echo $_POST["pgw_email"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> </td>
	</TR>
	<tr>
		<TD class="tablecontent-odd" align="left">Alamat Situs Pribadi</td>
		<td class="tablecontent"><input readonly type="text" class="inputField" name="pgw_situs" size="35" maxlength="50" value="<?php echo $_POST["pgw_situs"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/> </td>
	</TR>
<!--dari alamat rumah lamaran-->
	<tr>
		<td width= "30%" class="tablecontent-odd">Alamat Asal</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td colspan="2"><input readonly type="text" class="inputField" name="pgw_alamat_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_alamat_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Kota</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_kota_asal" size="15" maxlength="15" value="<?php echo $_POST["pgw_kota_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_asal" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Hp</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_hp_asal" size="15" maxlength="15" value="<?php echo $_POST["pgw_hp_asal"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Alamat Surat</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td colspan="2"><input readonly type="text" class="inputField" name="pgw_alamat_surat" size="35" maxlength="50" value="<?php echo $_POST["pgw_alamat_surat"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_surat" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_surat"];?>"/></td>
				</tr>
			</table>
		</td>						     
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Alamat Sekarang</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td colspan="2"><input readonly type="text" class="inputField" name="pgw_alamat_surabaya" size="35" maxlength="50" value="<?php echo $_POST["pgw_alamat_surabaya"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_surabaya" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_surabaya"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Hp</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_hp" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_hp"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Kontak Darurat</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="15%">Nama Kontak</td>
					<td colspan="2"><input readonly type="text" class="inputField" name="pgw_kontak_darurat" size="35" maxlength="50" value="<?php echo $_POST["pgw_kontak_darurat"];?>"/></td>
				</tr>
				<tr>
					<td width="15%">Hubungan</td>
					<td colspan="2"><input readonly type="text" class="inputField" name="pgw_kontak_darurat_hubungan" size="35" maxlength="50" value="<?php echo $_POST["pgw_kontak_darurat_hubungan"];?>"/></td>
				</tr>
				<tr>
					<td width="15%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_kontak_darurat_telp" size="15" maxlength="15" value="<?php echo $_POST["pgw_kontak_darurat_telp"];?>"/></td>
				</tr>
				<tr>
					<td width="15%">Hp</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_kontak_darurat_hp" size="15" maxlength="15" value="<?php echo $_POST["pgw_kontak_darurat_hp"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" colspan=2 class="tablesmallheader">DATA KELUARGA</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Ayah</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama_ayah" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ayah"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Status</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0">
			<input readonly  type="radio" class="inputField" name="pgw_ayah_masih_hidup" id="ayahy" value="y"<?php if($_POST["pgw_ayah_masih_hidup"]=="y") echo "checked";?>><label for="ayahy">Masih Hidup</label>&nbsp;
			<input readonly  type="radio" class="inputField" name="pgw_ayah_masih_hidup" id="ayahn" value="n"<?php if($_POST["pgw_ayah_masih_hidup"]=="n") echo "checked";?>><label for="ayahn">Almarhum</label>&nbsp;
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Ibu</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama_ibu" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ibu"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Status</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0">
			<input readonly  type="radio" class="inputField" name="pgw_ibu_masih_hidup" id="ibuy" value="y"<?php if($_POST["pgw_ibu_masih_hidup"]=="y") echo "checked";?>><label for="ibuy">Masih Hidup</label>&nbsp;
			<input readonly  type="radio" class="inputField" name="pgw_ibu_masih_hidup" id="ibun" value="n"<?php if($_POST["pgw_ibu_masih_hidup"]=="n") echo "checked";?>><label for="ibun">Almarhum</label>&nbsp;
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Alamat Orang Tua</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td colspan="2">
						<textarea class="inputField" name="pgw_alamat_ortu" id="pgw_alamat_ortu" rows="3" cols="65"><?php echo $_POST["pgw_alamat_ortu"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_ortu" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_ortu"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Ayah Mertua</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama_ayah_mertua" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ayah_mertua"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Status</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0">
			<input readonly  type="radio" class="inputField" name="pgw_ayah_mertua_masih_hidup" id="ayah_mertuay" value="y"<?php if($_POST["pgw_ayah_mertua_masih_hidup"]=="y") echo "checked";?>><label for="ayah_mertuay">Masih Hidup</label>&nbsp;
			<input readonly  type="radio" class="inputField" name="pgw_ayah_mertua_masih_hidup" id="ayah_mertuan" value="n"<?php if($_POST["pgw_ayah_mertua_masih_hidup"]=="n") echo "checked";?>><label for="ayah_mertuan">Almarhum</label>&nbsp;
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Ibu Mertua</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama_ibu_mertua" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_ibu_mertua"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Status</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0">
			<input readonly  type="radio" class="inputField" name="pgw_ibu_mertua_masih_hidup" id="ibu_mertuay" value="y"<?php if($_POST["pgw_ibu_mertua_masih_hidup"]=="y") echo "checked";?>><label for="ibu_mertuay">Masih Hidup</label>&nbsp;
			<input readonly  type="radio" class="inputField" name="pgw_ibu_mertua_masih_hidup" id="ibu_mertuan" value="n"<?php if($_POST["pgw_ibu_mertua_masih_hidup"]=="n") echo "checked";?>><label for="ibu_mertuan">Almarhum</label>&nbsp;
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Alamat Mertua</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td colspan="2">
						<textarea class="inputField" name="pgw_alamat_mertua" id="pgw_alamat_mertua" rows="3" cols="65"><?php echo $_POST["pgw_alamat_mertua"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_mertua" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_mertua"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nama Istri/Suami</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_nama_suami_istri" size="30" maxlength="50" value="<?php echo $_POST["pgw_nama_suami_istri"];?>"/></td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Panggilan Istri/Suami</td>
		<td width= "70%" align="left" class="tablecontent" cellpadding="0"><input readonly  type="text" class="inputField" name="pgw_panggilan_suami_istri" size="15" maxlength="15" value="<?php echo $_POST["pgw_panggilan_suami_istri"];?>"/></td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Tempat/Tanggal Lahir Istri/Suami</td>
		<td class="tablecontent" ><input readonly type="text" class="inputField" name="pgw_kota_lahir_suami_istri" size="15" maxlength="20" value="<?php echo $_POST["pgw_kota_lahir_suami_istri"];?>"/> / 
						<input readonly type="text" class="inputField" id="pgw_tanggal_lahir_suami_istri" name="pgw_tanggal_lahir_suami_istri" size="15" maxlength="10" value="<?php echo $_POST["pgw_tanggal_lahir_suami_istri"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir_suami_istri" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					 (dd-mm-yyy)
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd">Status Kerja Istri/Suami</td>
		<td class="tablecontent" >
			<input readonly type="radio" name="pgw_status_kerja_suami_istri" id="tipe1" class="inputField" value="Y" <?php if($_POST["pgw_status_kerja_suami_istri"]=="Y" || !$_POST["pgw_status_kerja_suami_istri"]) echo "checked";?> <label for="tipe1" >Bekerja</label>&nbsp;
			<input readonly type="radio" name="pgw_status_kerja_suami_istri" id="tipe2" class="inputField" value="N" <?php if($_POST["pgw_status_kerja_suami_istri"]=="N") echo "checked";?>><label for="tipe2" >Tidak Bekerja</label>&nbsp;
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Instansi Istri/Suami <br>
		(diisi jika status kerja adalah "Bekerja"</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="10%">Nama</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_instansi_suami_istri" size="30" maxlength="50" value="<?php echo $_POST["pgw_instansi_suami_istri"];?>"/></td>
				</tr>
				<tr>
					<td align="top" width="10%">Alamat</td>
					<td colspan="2">
						<textarea class="inputField" name="pgw_alamat_instansi_suami_istri" id="pgw_alamat_instansi_suami_istri" rows="3" cols="65"><?php echo $_POST["pgw_alamat_instansi_suami_istri"];?></textarea>
					</td>
				</tr>
				<tr>
					<td width="10%">Kota</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_kota_instansi_suami_istri" size="15" maxlength="15" value="<?php echo $_POST["pgw_kota_instansi_suami_istri"];?>"/></td>
				</tr>
				<tr>
					<td width="10%">Telepon</td><td colspan="2"><input readonly type="text" class="inputField" name="pgw_telp_instansi_suami_istri" size="15" maxlength="15" value="<?php echo $_POST["pgw_telp_instansi_suami_istri"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
	  <td colspan="2" align="center" class="tablesmallheader">DATA ANAK</td>
	</tr>

	<tr><td height="2" colspan=2 class="tablecontent"></td></tr>
	<tr>
		<td colspan=2 class="tablecontent-odd">
			<TABLE align="center" Border=0 cellpadding="4" cellspacing="1" class="tblForm" width="100%">
				<TR>
					<TD align="center" width ="20%" class="tablecontent-odd">Nama</TD>
					<TD align="center"width="10%" class="tablecontent-odd">L/P</TD>
					<TD align="center" width="10%" class="tablecontent-odd">Tempat Lahir</TD>
					<TD align="center" width="15%" class="tablecontent-odd">Tanggal Lahir</TD>
					<TD align="center" width="10%" class="tablecontent-odd">Pendidikan</TD>
					<TD align="center" width="10%"class="tablecontent-odd">Pekerjaan</TD>
					<TD align="center" width="10%"class="tablecontent-odd">Nikah (Y/N)</TD>
				</TR>
				<?php	for($i=0;$i<7;$i++)  {?>						
				<TR>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" name="pgw_anak_nama[<?=$i+1?>]" size="30" maxlength="35" value="<?php echo $_POST["pgw_anak_nama"][$i+1];?>"/> 
					</TD>
		
					<TD class="tablecontent">
						<SELECT NAME="pgw_anak_jenis_kelamin[<?=$i+1?>]"class="inputField">
						<option class="inputField" value="L" <?php if($_POST["pgw_anak_jenis_kelamin"][$i+1]=="L")echo "selected";?>>Laki-laki</option>
						<option class="inputField" value="P" <?php if($_POST["pgw_anak_jenis_kelamin"][$i+1]=="P")echo "selected";?>>Perempuan</option>
						</SELECT>
					</TD>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" name="pgw_anak_kota_lahir[<?=$i+1?>]" size="15" maxlength="15" value="<?php echo $_POST["pgw_anak_kota_lahir"][$i+1];?>"/>
					</TD>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" id="pgw_anak_tanggal_lahir[<?=$i+1?>]" name="pgw_anak_tanggal_lahir[<?=$i+1?>]" size="10" maxlength="10" value="<?php echo $_POST["pgw_anak_tanggal_lahir"][$i+1];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lahir_anak[<?=$i+1?>]" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</TD>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" name="pgw_anak_pendidikan[<?=$i+1?>]" size="15" maxlength="25" value="<?php echo $_POST["pgw_anak_pendidikan"][$i+1];?>"/> </TD>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" name="pgw_anak_kerja[<?=$i+1?>]" size="15" maxlength="25" value="<?php echo $_POST["pgw_anak_kerja"][$i+1];?>"/> </TD>
					<TD class="tablecontent">
						<input readonly type="text" class="inputField" name="pgw_anak_nikah[<?=$i+1?>]" size="2" maxlength="1" value="<?php echo $_POST["pgw_anak_nikah"][$i+1];?>"/> </TD>
				</TR>
				<?php	}?>
			</TABLE>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>
	<tr>
	  <td colspan="3" align="center" class="tablesmallheader">PENDIDIKAN FORMAL</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Sekolah Dasar</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Nama</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sd_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_sd_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sd_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_sd_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_sd_tanggal_lulus" name="pgw_sd_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_sd_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_sd" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sd_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_sd_no_ijasah"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Sekolah Lanjut Tingkat Pertama</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Nama</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sltp_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_sltp_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sltp_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_sltp_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_sltp_tanggal_lulus" name="pgw_sltp_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_sltp_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_sltp" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_sltp_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_sltp_no_ijasah"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Sekolah Lanjut Tingkat Atas</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Nama</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_slta_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_slta_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_slta_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_slta_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_slta_tanggal_lulus" name="pgw_slta_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_slta_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_slta" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_slta_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_slta_no_ijasah"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Program Pendidikan Diploma</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Jurusan</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Perguruan Tinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_pt_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Bidang Ilmu</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_diploma_bidang_ilmu"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_diploma_tanggal_lulus" name="pgw_diploma_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_diploma" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_diploma_no_ijasah"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_gelar"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">IPK</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_diploma_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_diploma_ipk"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Program Pendidikan Strata-1</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Jurusan</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Perguruan Tinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_pt_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Bidang Ilmu</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s1_bidang_ilmu"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_s1_tanggal_lulus" name="pgw_s1_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s1" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s1_no_ijasah"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_gelar"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">IPK</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s1_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s1_ipk"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Program Pendidikan Strata-2</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Jurusan</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Perguruan Tinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_pt_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Bidang Ilmu</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s2_bidang_ilmu"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_s2_tanggal_lulus" name="pgw_s2_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s2" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s2_no_ijasah"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_gelar"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">IPK</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s2_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s2_ipk"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">Program Pendidikan Strata-3</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Jurusan</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_nama" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_nama"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Perguruan Tinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_pt_asal" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_pt_asal"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Kota</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_kota" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_kota"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Bidang Ilmu</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_bidang_ilmu" size="35" maxlength="50" value="<?php echo $_POST["pgw_s3_bidang_ilmu"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Tanggal Lulus</td>
					<td colspan="2">: 
						<input readonly type="text" class="inputField" id="pgw_s3_tanggal_lulus" name="pgw_s3_tanggal_lulus" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_tanggal_lulus"];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_lulus_s3" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
					</td>
				</tr>
				<tr>
					<td width="20%">Nomor Ijasah</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_no_ijasah" size="25" maxlength="50" value="<?php echo $_POST["pgw_s3_no_ijasah"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_gelar" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_gelar"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">IPK</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_s3_ipk" size="15" maxlength="15" value="<?php echo $_POST["pgw_s3_ipk"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width= "30%" class="tablecontent-odd">SEBUTAN DAN GELAR</td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Gelar Muka</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_gelar_muka" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_muka"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar Belakang</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_gelar_belakang" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_belakang"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Bidang Keahlian</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_bidang_keahlian" size="15" maxlength="50" value="<?php echo $_POST["pgw_bidang_keahlian"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Gelar Tertinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_gelar_tertinggi" size="35" maxlength="50" value="<?php echo $_POST["pgw_gelar_tertinggi"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Pend. Tertinggi</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_pendidikan_tertinggi" size="35" maxlength="50" value="<?php echo $_POST["pgw_pendidikan_tertinggi"];?>"/></td>
				</tr>
				<tr>
					<td width="20%">Akta V</td><td colspan="2">: <input readonly type="text" class="inputField" name="pgw_akta_v" size="15" maxlength="15" value="<?php echo $_POST["pgw_akta_v"];?>"/></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>
	<tr>
	  <td colspan="2" align="center" class="tablesmallheader">KEMAMPUAN BAHASA</td>
	</tr>
	<tr>
	<td class="tablecontent" colspan=3>
		<table align="center" border=0 cellpadding=4 cellspacing=1 width="100%" class="tblForm">
		<tr> 
		  <td class="tablecontent-odd" align="center" valign="absmiddle" >Jenis Bahasa</td>
		  <td class="tablecontent-odd" align="center" valign="absmiddle" colspan=2>Penguasaan Tertulis</td>
		  <td class="tablecontent-odd" align="center" valign="absmiddle" colspan=2>Penguasaan Lisan</td>
		  <td class="tablecontent-odd" align="center" valign="absmiddle" >Tahun</td>
		</tr>
		<?php for($i=1;$i<=3;$i++) {?>
			<tr>
				<td valign="top" align="center"class="tablecontent"><input readonly class="inputField" name="pgw_bhs_nama[<?=$i?>]" value="<?php echo $_POST["pgw_bhs_nama"][$i];?>" type=text size="40" maxlength="25"></td>
				<td valign="top" align="center"class="tablecontent"><input readonly TYPE="radio" class="inputField" NAME="pgw_bhs_tulis[<?=$i?>]" id="id<?=$i?>1"  value="y" <?php if($_POST["pgw_bhs_tulis"][$i]=="y")echo "checked"; ?> ><label for="id<?=$i?>1">Aktif</label></td>
				<td valign="top" align="center"class="tablecontent"><input readonly TYPE="radio" class="inputField" NAME="pgw_bhs_tulis[<?=$i?>]" id="id<?=$i?>2"  value="n" <?php if($_POST["pgw_bhs_tulis"][$i]=="n") echo "checked"; ?> ><label for="id<?=$i?>2" >Pasif</label></td>
				<td valign="top" align="center"class="tablecontent"><input readonly TYPE="radio" class="inputField" NAME="pgw_bhs_lisan[<?=$i?>]" id="id<?=$i?>3"  value="y" <?php if($_POST["pgw_bhs_lisan"][$i]=="y")echo "checked"; ?> ><label for="id<?=$i?>3">Aktif</label></td>
				<td valign="top" align="center"class="tablecontent"><input readonly TYPE="radio" class="inputField" NAME="pgw_bhs_lisan[<?=$i?>]" id="id<?=$i?>4"  value="n" <?php if($_POST["pgw_bhs_lisan"][$i]=="n") echo "checked"; ?> ><label for="id<?=$i?>4" >Pasif</label></td>
				<td valign="top" align="center"class="tablecontent"><input readonly class="inputField" name="pgw_bhs_tahun[<?=$i?>]" value="<?php echo $_POST["pgw_bhs_tahun"][$i];?>" type=text size="10" maxlength="4"></td>
			</tr>
		<?php }?>
		</table>
	</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>	
	<tr>
		<td colspan="3" align="center" class="tablesmallheader">PENDIDIKAN NON FORMAL DAN PROFESI</td>
	</tr>
		<td class="tablecontent" colspan=3>
			<table align="center" border=0 cellpadding=5 cellspacing=1 width="100%" class="tblForm">
				<TR>
					<TD align="center" width ="30%" class="tablecontent-odd">Kursus / Pelatihan</TD>
					<TD align="center" width ="70%" class="tablecontent-odd">Materi</TD>
					<TD align="center" width ="20%" class="tablecontent-odd">Tanggal</TD>
				</TR>
				<?php for($i=1;$i<=4;$i++) { ?>
				<TR>
					<TD valign="top" class="tablecontent">
						<input readonly class="inputField" type=text size="40" maxlength="100" name="pgw_kursus_nama[<?=$i?>]" value="<?php echo $_POST["pgw_kursus_nama"][$i];?>">
					</TD>
					<TD valign="top" align="center" class="tablecontent">
						<input readonly class="inputField" type=text size="60" maxlength="70" name="pgw_kursus_materi[<?=$i?>]" value="<?php echo $_POST["pgw_kursus_materi"][$i];?>">
					</TD>
					<TD valign="top" align="center" class="tablecontent">
						<input readonly class="inputField" type=text size="15" maxlength="10" id="pgw_kursus_tanggal_selesai[<?=$i?>]" name="pgw_kursus_tanggal_selesai[<?=$i?>]" value="<?php echo $_POST["pgw_kursus_tanggal_selesai"][$i];?>">
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_selesai_kursus[<?=$i?>]" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
					</TD>
				</TR>
				<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>	
	
	<tr>
		<td colspan="3" align="center" class="tablesmallheader">PEKERJAAN<BR>(diisi oleh personalia)</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Unit Kerja</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php echo $_POST["struk_nama"];?>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Status Pegawai</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php 
			for($i=0,$n=count($row_jab_kontrak);$i<$n;$i++){ 
				if ($_POST["pgw_status"]==$row_jab_kontrak[$i]["jab_kon_id"]) {
					echo $row_jab_kontrak[$i]["jab_kon_nama"];?>
					<input readonly type="hidden" name="pgw_status" value ="<?php echo $row_jab_kontrak[$i]["jab_kon_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Pangkat Diterima</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php for($i=0,$n=count($row_golongan);$i<$n;$i++){ 
				if ($_POST["pgw_pangkat_diterima"]==$row_golongan[$i]["gol_id"]) {
					echo $row_golongan[$i]["gol_pangkat"]." / ".$row_golongan[$i]["gol_gol"];?> 
					<input readonly type="hidden" name="pgw_pangkat_diterima" value ="<?php echo $row_golongan[$i]["gol_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Masa Kerja diterima</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><?php echo $_POST["pgw_masa_kerja_diterima"]." tahun";?>
			<input readonly readonly type="hidden" class="inputField" name="pgw_masa_kerja_diterima" value="<?php echo $_POST["pgw_masa_kerja_diterima"];?>"/>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Pangkat Sekarang</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php for($i=0,$n=count($row_golongan);$i<$n;$i++){ 
				if ($_POST["id_gol"]==$row_golongan[$i]["gol_id"]) {
					echo $row_golongan[$i]["gol_pangkat"]." / ".$row_golongan[$i]["gol_gol"];?> 
					<input readonly type="hidden" name="id_gol" value ="<?php echo $row_golongan[$i]["gol_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Masa Kerja Pangkat/Gol</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0"><?php echo $_POST["pgw_masa_kerja_golongan"]." tahun";?>
			<input readonly readonly type="hidden" class="inputField" name="pgw_masa_kerja_golongan" value="<?php echo $_POST["pgw_masa_kerja_golongan"];?>"/>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Jenis Pegawai</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php for($i=0,$n=count($rowJenisPegawai);$i<$n;$i++){ 
				if ($_POST["pgw_jenis_pegawai"]==$rowJenisPegawai[$i]["pos_jenis_id"]) {
					echo $rowJenisPegawai[$i]["pos_jenis_nama"];?> 
					<input readonly type="hidden" name="pgw_jenis_pegawai" value ="<?php echo $rowJenisPegawai[$i]["pos_jenis_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Tanggal Masuk</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" id="pgw_tanggal_masuk" name="pgw_tanggal_masuk" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_masuk"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nomor SK Angkat</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" name="pgw_no_sk_pangkat" size="20" maxlength="50" value="<?php echo $_POST["pgw_no_sk_pangkat"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Tanggal SK Angkat</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" id="pgw_tanggal_habis_sk" name="pgw_tanggal_habis_sk" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_habis_sk"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">TMT Angkat</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" id="pgw_tmt_pangkat" name="pgw_tmt_pangkat" size="15" maxlength="15" value="<?php echo $_POST["pgw_tmt_pangkat"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Tanggal Keluar</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" id="pgw_tanggal_keluar" name="pgw_tanggal_keluar" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_keluar"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Alasan Keluar</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" name="pgw_alasan_keluar" size="50" maxlength="100" value="<?php echo $_POST["pgw_alasan_keluar"];?>"/>
		</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Jabatan Struktural</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php for($i=0,$n=count($rowJabStruk);$i<$n;$i++){ 
				if ($_POST["pgw_jabatan_struktural"]==$rowJabStruk[$i]["jab_struk_id"]) {
					echo $rowJabStruk[$i]["jab_struk_nama"];?> 
					<input readonly type="hidden" name="pgw_jabatan_struktural" value ="<?php echo $rowJabStruk[$i]["jab_struk_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nomor SK Jabatan Struktural</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" name="pgw_no_sk_jab_struktural" size="20" maxlength="50" value="<?php echo $_POST["pgw_no_sk_jab_struktural"];?>"/>
		</td>
	</tr>
<!--	
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">DP3 Terakhir</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" name="pgw_dp3_terakhir" size="20" maxlength="50" value="<?php echo $_POST["pgw_dp3_terakhir"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">DP3 Berikutnya</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly readonly type="text" class="inputField" name="pgw_dp3_berikutnya" size="20" maxlength="50" value="<?php echo $_POST["pgw_dp3_berikutnya"];?>"/>
		</td>
	</tr>
-->	
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Masa Kerja</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<select disabled name="pgw_masa_kerja" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
				<option class="inputField" value="0"<?php if ($_POST["pgw_masa_kerja"]=="0") echo"selected"?>>0 Tahun</option>
				<?php for($i=1,$n=$rowMasa["max"];$i<$n;$i++){ ?>
					<option class="inputField" value="<?php echo $i;?>"<?php if ($_POST["pgw_masa_kerja"]==$i) echo"selected"?>><?php echo $i;?> Tahun</option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Jam Masuk</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<select disabledname="pgw_jam_masuk_jam" class="inputField" >
				<?php for($i=0,$n=24;$i<$n;$i++){ ?>
					<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["pgw_jam_masuk_jam"]) echo "selected"; ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
				<?php } ?>
				</select>:
				<select disabled name="pgw_jam_masuk_menit" class="inputField" >
				<?php for($i=0,$n=60;$i<$n;$i++){ ?>
					<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["pgw_jam_masuk_menit"]) echo "selected"; ?>><?php echo str_pad($i, 2, "0", STR_PAD_LEFT);?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<?php if($_POST["pgw_jenis_pegawai"]=='1') {?>
	<tr>
		<td colspan="3" align="center" class="tablesmallheader">KHUSUS PEGAWAI AKADEMIK</td>
	</tr>
	<tr >
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Jabatan Akademik</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<?php for($i=0,$n=count($rowJabAkad);$i<$n;$i++){ 
				if ($_POST["pgw_jab_akademik"]==$rowJabAkad[$i]["jab_akad_id"]) {
					echo $rowJabAkad[$i]["jab_akad_nama"];?> 
					<input type="hidden" name="pgw_jab_akademik" value ="<?php echo $rowJabAkad[$i]["jab_akad_id"];?>"/>
			<?php } }?>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Nomor SK Jabatan Akademik</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly type="text" class="inputField" name="pgw_no_sk_jab_akademik" size="20" maxlength="50" value="<?php echo $_POST["pgw_no_sk_jab_akademik"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Tanggal SK </td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly <?php if($_POST["pgw_jenis_pegawai"] != "1") echo "disabled";?> type="text" class="<?php if($_POST["pgw_jenis_pegawai"] !=1 || !$_POST["pgw_jenis_pegawai"]) echo "bDisable";else echo "inputField";?>" id="pgw_tanggal_sk_akademik" name="pgw_tanggal_sk_akademik" size="15" maxlength="15" value="<?php echo $_POST["pgw_tanggal_sk_akademik"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">TMT Jabatan Akademik</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly <?php if($_POST["pgw_jenis_pegawai"] != "1") echo "disabled";?> type="text" class="<?php if($_POST["pgw_jenis_pegawai"] !=1 || !$_POST["pgw_jenis_pegawai"]) echo "bDisable";else echo "inputField";?>" id="pgw_tmt_akademik" name="pgw_tmt_akademik" size="15" maxlength="15" value="<?php echo $_POST["pgw_tmt_akademik"];?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Program Studi</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly type="text" class="inputField" name="pgw_base_program_studi" size="20" maxlength="50" value="<?php echo $_POST["pgw_base_program_studi"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Instansi Asal</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly type="text" class="inputField" name="pgw_instansi_asal" size="20" maxlength="50" value="<?php echo $_POST["pgw_instansi_asal"];?>"/>
		</td>
	</tr>
	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">NIP PNS</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">
			<input readonly type="text" class="inputField" name="pgw_nip_pns" size="20" maxlength="50" value="<?php echo $_POST["pgw_nip_pns"];?>"/>
		</td>
	</tr>
<!--	<tr>
		<td width= "30%" align="left" class="tablecontent-odd" cellspacing="0">Surat Ijin Mengajar</td>
		<td width= "50%" align="left" class="tablecontent" cellpadding="0">-->
			<input readonly type="hidden" class="inputField" name="pgw_surat_ijin_mengajar" size="20" maxlength="50" value="<?php echo $_POST["pgw_surat_ijin_mengajar"];?>"/>
<!--		</td>
	</tr>-->
	<?php } ?>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>	
	<tr>
		<td colspan="3" align="center" class="tablesmallheader">PENGALAMAN KERJA</td>
	</tr>
	<?php for($i=1;$i<=3;$i++) { ?>
	<tr>
		<td width= "30%" class="tablecontent-odd">Instansi <?=$i?></td>
		<td class="tablecontent" >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td width="20%">Nama Instansi</td><td width="5%">: </td><td><input readonly type="text" class="inputField" name="pgw_pengalaman_nama[<?=$i?>]" size="35" maxlength="50" value="<?php echo $_POST["pgw_pengalaman_nama"][$i];?>"/></td>
				</tr>
				<tr>
					<td width= "20%" align="left" cellspacing="0">Tanggal Masuk</td>
					<td width="5%">: </td>
					<td class="tablecontent" cellpadding="0">
						<input readonly type="text" class="inputField" id="pgw_pengalaman_tanggal_masuk[<?=$i?>]" name="pgw_pengalaman_tanggal_masuk[<?=$i?>]" size="15" maxlength="15" value="<?php echo $_POST["pgw_pengalaman_tanggal_masuk"][$i];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_masuk_pgw_pengalaman[<?=$i?>]" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
					</td>
				</tr>
				<tr>
					<td width= "20%" align="left"cellspacing="0">Tanggal Keluar</td>
					<td width="5%">: </td>
					<td class="tablecontent" cellpadding="0">
						<input readonly type="text" class="inputField" id="pgw_pengalaman_tanggal_keluar[<?=$i?>]" name="pgw_pengalaman_tanggal_keluar[<?=$i?>]" size="15" maxlength="15" value="<?php echo $_POST["pgw_pengalaman_tanggal_keluar"][$i];?>"/>
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_keluar_pgw_pengalaman[<?=$i?>]" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
					</td>
				</tr>
				<tr>
					<td width="20%">Golongan</td><td width="5%">: </td><td><input readonly type="text" class="inputField" name="pgw_pengalaman_golongan[<?=$i?>]" size="15" maxlength="15" value="<?php echo $_POST["pgw_pengalaman_golongan"][$i];?>"/></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Alasan Keluar</td><td valign="top" width="5%">: </td><td valign="top" ><textarea class="inputField" name="pgw_pengalaman_alasan_keluar[<?=$i?>]" id="pgw_pengalaman_alasan_keluar[<?=$i?>]" rows="4" cols="65"><?php echo $_POST["pgw_pengalaman_alasan_keluar"][$i];?></textarea>
				</tr>
			</table>
		</td>
	</tr>
	<?php }?>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>	
	<tr>
		<td colspan="3" align="center" class="tablesmallheader">ORGANISASI PROFESI</td>
	</tr>
		<td class="tablecontent" colspan=3>
			<table align="center" border=0 cellpadding=5 cellspacing=1 width="100%" class="tblForm">
				<TR>
					<TD align="center" width ="30%" class="tablecontent-odd">Nama Organisasi</TD>
					<TD align="center" width ="30%" class="tablecontent-odd">Sebagai</TD>
					<TD align="center" width ="20%" class="tablecontent-odd">Tanggal Masuk</TD>
					<TD align="center" width ="20%" class="tablecontent-odd">Status</TD>
				</TR>
				<?php for($i=1;$i<=4;$i++) { ?>
				<TR>
					<TD valign="top" class="tablecontent">
						<input readonly class="inputField" type=text size="40" maxlength="100" name="pgw_org_prof_nama[<?=$i?>]" value="<?php echo $_POST["pgw_org_prof_nama"][$i];?>">
					</TD>
					<TD valign="top" align="center" class="tablecontent">
						<input readonly class="inputField" type=text size="30" maxlength="70" name="pgw_org_prof_sebagai[<?=$i?>]" value="<?php echo $_POST["pgw_org_prof_sebagai"][$i];?>">
					</TD>
					<TD valign="top" class="tablecontent">
						<input readonly class="inputField" type=text size="15" maxlength="10" id="pgw_org_prof_tanggal_masuk[<?=$i?>]" name="pgw_org_prof_tanggal_masuk[<?=$i?>]" value="<?php echo $_POST["pgw_org_prof_tanggal_masuk"][$i];?>">
						<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_masuk_pgw_org_prof[<?=$i?>]" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)
					</TD>
					<TD valign="top" align="center" class="tablecontent">
						<input readonly class="inputField" type=text size="15" maxlength="70" name="pgw_org_prof_status[<?=$i?>]" value="<?php echo $_POST["pgw_org_prof_status"][$i];?>">
					</TD>
				</TR>
				<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan=2 class="tablecontent-odd"></td>
	</tr>	
	<TR>
        <td colspan="3" align="center" class="tablecontent">
            <input readonly type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='data_pegawai_view.php?pgw_nama=<?php echo $_POST["pgw_nama"];?>'"/>
        </td>
    </tr>
</table>
<input readonly type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input readonly type="hidden" name="plamar_id" value="<?php echo $plamarId;?>">
<input readonly type="hidden" name="pgw_id" value="<?php echo $pgwId;?>">
<input readonly type="hidden" name="nama" value="<?php echo $_POST["nama"];?>">
</form>
<!--tanggal lahir pegawai-->
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tanggal_lahir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<!--tanggal lahir pegawai-->
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "pgw_tanggal_lahir_suami_istri",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_lahir_suami_istri",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<!--tanggal lahir anak-anak-->
<?php for($i=1;$i<8;$i++) { ?>
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_anak_tanggal_lahir[<?=$i?>]",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lahir_anak[<?=$i?>]",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php }?>
<!--tanggal lulus sd-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_sd_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_sd",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus sltp-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_sltp_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_sltp",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus slta-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_slta_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_slta",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus diploma-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_diploma_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_diploma",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus s1-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_s1_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s1",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus s2-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_s2_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s2",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal lulus s3-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_s3_tanggal_lulus",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_lulus_s3",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php for($i=1;$i<=4;$i++) {?>
<!--tanggal selesai kursus-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_kursus_tanggal_selesai[<?=$i?>]",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_selesai_kursus[<?=$i?>]",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php }?>
<!--tanggal masuk (PeKERJAAN)-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_tanggal_masuk",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_masuk",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal habis_sk(PeKERJAAN)-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_tanggal_habis_sk",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_habis_sk",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal keluar (PeKERJAAN)-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_tanggal_keluar",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_keluar",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php for($i=1;$i<=3;$i++) {?>
<!--tanggalmasuk(pengalaman)-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_pengalaman_tanggal_masuk[<?=$i?>]",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_masuk_pgw_pengalaman[<?=$i?>]",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<!--tanggal keluar (pengalaman)-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_pengalaman_tanggal_keluar[<?=$i?>]",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_keluar_pgw_pengalaman[<?=$i?>]",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php }?>
<?php for($i=1;$i<=4;$i++) {?>
<!--tanggal masuk organisasi profesi-->
<script type="text/javascript">
	Calendar.setup({
		inputField     :    "pgw_org_prof_tanggal_masuk[<?=$i?>]",      // id of the input field
		ifFormat       :    "<?=$formatCal;?>",       // format of the input field
		showsTime      :    false,            // will display a time selector
		button         :    "img_tgl_masuk_pgw_org_prof[<?=$i?>]",   // trigger for the calendar (button ID)
		singleClick    :    true,           // double-click mode
		step           :    1                // show all years in drop-down boxes (instead of every other year as default)
	});
</script>
<?php }?>

</div>
</body>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
</html>
<?
    $dtaccess->Close();
?>
