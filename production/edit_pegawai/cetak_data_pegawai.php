<?php
    require_once("../penghubung.inc.php");
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
     $depId = $auth->GetDepId();

     if(!$auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ) && !$auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_tambah_pegawai",PRIV_READ)===1 || $auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }

	$isPrint = false;
	if($_GET["print"]) $isPrint = true;
	
	if(!$_POST["x_mode"]) 	{
		if(strtoupper($_GET["status"])=='N') $_x_mode = "New";
		else $_x_mode = "Edit";
	} else $_x_mode = & $_POST["x_mode"] ;
   	
	if($_POST["plamar_id"])  $plamarId = & $_POST["plamar_id"];
 	if($_POST["pgw_id"])  $pgwId = & $_POST["pgw_id"];
	if($_GET["nama"])$_POST["nama"]=$_GET["nama"];
	
	

	if ($_GET["id"]) {
		$pgwId = $enc->Decode($_GET["id"]);
	}
	if ($_GET["pejabat_id"]) {
		$pejabatId = $enc->Decode($_GET["pejabat_id"]);
	}
    if ($pgwId) {
		$sql = "select a.*,b.agm_nama,c.jab_kon_nama,d.pos_jenis_nama,e.gol_gol,e.gol_pangkat,f.jab_akad_nama,
                    g.jab_struk_nama, h.struk_nama, i.gol_pangkat as golongan 
			from hris.hris_pegawai a 
			left join hris.hris_agama b on b.agm_id = a.pgw_agama 
			left join hris.hris_jabatan_kontrak c on a.pgw_status=c.jab_kon_id
			left join hris.hris_master_jenis_posisi d on a.pgw_jenis_pegawai=d.pos_jenis_id
			left join global.global_golongan i on i.gol_id = a.pgw_pangkat_diterima 
			left join global.global_golongan e on a.id_gol=e.gol_id
			left join hris.hris_jabatan_akademik f on a.pgw_jab_akademik=f.jab_akad_id
			left join hris.hris_jabatan_struktural g on a.pgw_jabatan_struktural=g.jab_struk_id
			left join hris.hris_struktural h on h.struk_id = a.id_struk 
			where pgw_id ='".$pgwId."' order by a.pgw_nama"; 
		$rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$row_edit = $dtaccess->Fetch($rs_edit);
		$dtaccess->Clear($rs_edit);
//echo $sql;
		$_POST["pgw_id"] = $row_edit["pgw_id"]; 		
		$_POST["pgw_nip"] = $row_edit["pgw_nip"]; 
		$_POST["pgw_nama"] = $row_edit["pgw_nama"]; 
		$_POST["pgw_tempat_lahir"] = $row_edit["pgw_tempat_lahir"]; 
		$_POST["pgw_tanggal_lahir"] = format_date($row_edit["pgw_tanggal_lahir"]); 
		$_POST["pgw_agama"] = $row_edit["pgw_agama"]; 
		$_POST["pgw_alamat_surabaya"] = $row_edit["pgw_alamat_surabaya"]; 
		$_POST["pgw_telp_surabaya"] = $row_edit["pgw_telp_surabaya"]; 
		$_POST["pgw_telp_hp"] = $row_edit["pgw_telp_hp"]; 
		$_POST["pgw_sd_nama"] = $row_edit["pgw_sd_nama"]; 
		 
		$_POST["pgw_sd_tanggal_lulus"] = format_date($row_edit["pgw_sd_tanggal_lulus"]); 
		$_POST["pgw_sd_no_ijasah"] = $row_edit["pgw_sd_no_ijasah"]; 
		$_POST["pgw_sltp_nama"] = $row_edit["pgw_sltp_nama"]; 
		
		$_POST["pgw_sltp_tanggal_lulus"] = format_date($row_edit["pgw_sltp_tanggal_lulus"]); 
		$_POST["pgw_sltp_no_ijasah"] = $row_edit["pgw_sltp_no_ijasah"]; 
		$_POST["pgw_slta_nama"] = $row_edit["pgw_slta_nama"]; 
		 
		$_POST["pgw_slta_tanggal_lulus"] = format_date($row_edit["pgw_slta_tanggal_lulus"]); 
		$_POST["pgw_slta_no_ijasah"] = $row_edit["pgw_slta_no_ijasah"]; 
		$_POST["pgw_diploma_nama"] = $row_edit["pgw_diploma_nama"]; 
		$_POST["pgw_diploma_pt_asal"] = $row_edit["pgw_diploma_pt_asal"]; 
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
		$_POST["pgw_s3_gelar"] =  $row_edit["pgw_s3_gelar"]; 
		$_POST["pgw_s3_ipk"] = $row_edit["pgw_s3_ipk"]; 
		$_POST["pgw_gelar_tertinggi"] = $row_edit["pgw_gelar_tertinggi"]; 
		$_POST["pgw_pendidikan_tertinggi"] = $row_edit["pgw_pendidikan_tertinggi"]; 
		$_POST["pgw_status"] = $row_edit["pgw_status"]; 
		$_POST["pgw_pangkat_diterima"] = $row_edit["pgw_pangkat_diterima"]; 
		$_POST["id_gol"] = $row_edit["id_gol"]; 
		$_POST["pgw_jenis_pegawai"] = $row_edit["pgw_jenis_pegawai"]; 
		$_POST["pgw_no_sk_pangkat"] = $row_edit["pgw_no_sk_pangkat"]; 
		$_POST["pgw_tanggal_habis_sk"] = format_date($row_edit["pgw_tanggal_habis_sk"]); 
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
		$_POST["pgw_masa_kerja_golongan"] = $row_edit["pgw_masa_kerja_golongan"]; 
		$_POST["pgw_masa_kerja_diterima"] = $row_edit["pgw_masa_kerja_diterima"]; 
		$_POST["pgw_email"] = $row_edit["pgw_email"]; 
		$_POST["pgw_situs"] = $row_edit["pgw_situs"]; 
      
   
	if ($row_edit["pgw_s3_no_ijasah"]){
		$ijasahTertinggi="S3";
		$lulusan=$row_edit["pgw_s3_pt_asal"];
	}elseif
		($row_edit["pgw_s2_no_ijasah"]){
		$ijasahTertinggi="S2";
		$lulusan=$row_edit["pgw_s2_pt_asal"];
	}elseif
		($row_edit["pgw_s1_no_ijasah"]){
		$ijasahTertinggi="S1";
		$lulusan=$row_edit["pgw_s1_pt_asal"];
	}elseif
		($row_edit["pgw_diploma_no_ijasah"]){
		$ijasahTertinggi="DIPLOMA";
		$lulusan=$row_edit["pgw_diploma_pt_asal"];
	}elseif
		($row_edit["pgw_slta_no_ijasah"]){
		$ijasahTertinggi="SLTA";
		$lulusan=$row_edit["pgw_slta_nama"];
	}elseif
		($row_edit["pgw_sltp_no_ijasah"]){
		$ijasahTertinggi="SLTP";
		$lulusan=$row_edit["pgw_sltp_nama"];
	}elseif
		($row_edit["pgw_sd_no_ijasah"]){
		$ijasahTertinggi="SD";
		$lulusan=$row_edit["pgw_sd_nama"];
	}

				

		//------DATA studi lanjut s2-----
		$sql = "select * from hris.hris_studi_lanjut where id_pgw = '".$pgwId."' 
				and studi_lanjut_status = ".QuoteValue(DPE_CHAR,STUDI_LANJUT_S2);
		$rs_edit_s2 = $dtaccess->Execute($sql,DB_SCHEMA);
		$row_edit_s2 = $dtaccess->Fetch($rs_edit_s2);
		$dtaccess->Clear($rs_edit_s2);
		
		//------DATA studi lanjut s3-----
		$sql = "select * from hris.hris_studi_lanjut where id_pgw = '".$pgwId."' 
				and studi_lanjut_status = ".QuoteValue(DPE_CHAR,STUDI_LANJUT_S3);
		$rs_edit_s3 = $dtaccess->Execute($sql,DB_SCHEMA);
		$row_edit_s3 = $dtaccess->Fetch($rs_edit_s3);
		$dtaccess->Clear($rs_edit_s3);
			
		//------DATA sertifikasi lanjut-----		
		$sql = "select * from hris.hris_studi_lanjut_sertifikasi where id_pgw = '".$pgwId."'";
		$rs_edit_sertifikasi = $dtaccess->Execute($sql,DB_SCHEMA);
		$row_edit_sertifikasi = $dtaccess->FetchAll($rs_edit_sertifikasi);
		$dtaccess->Clear($rs_edit_sertifikasi);	
		
		//------DATA kursus-----
		$sql = "select * from hris.hris_pegawai_kursus where id_pgw = '".$pgwId."' order by pgw_kursus_tanggal_selesai desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataKursus = $dtaccess->FetchAll($rs);
          
          
		//------DATA Anak-----
		$sql = "select * from hris.hris_pegawai_data_anak where id_pgw = '".$pgwId."' order by pgw_anak_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataAnak = $dtaccess->FetchAll($rs);
          
		//------kemampuan bahasa asing-----
		$sql = "select * from hris.hris_pegawai_bahasa_asing where id_pgw = '".$pgwId."' order by pgw_bhs_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataBahasa = $dtaccess->FetchAll($rs);
		
		//------tanda jasa atau penghargaan-----
		$sql = "select * from hris.hris_pegawai_jasa where id_pgw = '".$pgwId."' order by pgw_jasa_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataJasa = $dtaccess->FetchAll($rs);
		
		//------kunjungan keluar negeri-----
		$sql = "select * from hris.hris_pegawai_kunjungan where id_pgw = '".$pgwId."' order by pgw_kunj_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataKunjung = $dtaccess->FetchAll($rs);
		
		
		//------pegawai pangkat-----
		$sql = "select a.*, b.struk_nama, c.gol_pangkat from hris.hris_pegawai_pangkat a
				left join hris.hris_struktural b on b.struk_id = a.id_struk
				left join global.global_golongan c on c.gol_id = a.id_gol
				where id_pgw = '".$pgwId."' order by pgw_pangkat_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataPangkat = $dtaccess->FetchAll($rs);
          
		//------pengalaman pekerjaan pegawai-----
		$sql = "select * from hris.hris_pegawai_pengalaman 
				where id_pgw = '".$pgwId."' order by pgw_pengalaman_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataPengalaman = $dtaccess->FetchAll($rs);
          
		//------organisasi pegawai-----
		$sql = "select * from hris.hris_pegawai_organisasi_profesi 
				where id_pgw = '".$pgwId."' order by pgw_org_prof_id desc";
		$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
		$dataOrga = $dtaccess->FetchAll($rs);
	}
	
	// -- bagian privilege ----
   if($_x_mode=="New") $privMode = PRIV_CREATE;
     else  $privMode = PRIV_UPDATE;
		
//     if(!$auth->IsAllowed("report_data_pegawai",$privMode)){
//         die("access_denied");
//         exit(1);
//     }
	
    // -- end priv ---

   		if(isset($_POST["btnPrint"])) {
			echo "<script>window.open('cetak_data_pegawai.php?print=yes".
			"&id=".$enc->Encode($_POST["pgw_id"])."&pejabat_id=".$enc->Encode($_POST["pejabat_id"]).
			"&tanggal=".$_POST["report_pgw"]."', 'wndprint', 'menubar=yes,scrollbars=yes')</script>";		
		}



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

	// -- ambil data pejabat penandatangan
	$sql = "select a.*,b.pgw_nama  from hris.hris_pejabat a 
	left join hris.hris_pegawai b on b.pgw_id=a.id_pgw";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA);
	$dataPjPenandatangan = $dtaccess->FetchAll($rs);
	
	if($pejabatId) { 
		// -- ambil data pejabat penandatangan
		$sql = "select a.*,b.pgw_nama  from hris.hris_pejabat a 
                         left join hris.hris_pegawai b on b.pgw_id=a.id_pgw
                         where b.pgw_id = ".QuoteValue(DPE_CHAR,$pejabatId);
		$rs = $dtaccess->Execute($sql,DB_SCHEMA);
		$tandaTangan = $dtaccess->Fetch($rs);
	}

	if($_GET["tanggal"])  $_POST["report_pgw"] = $_GET["tanggal"];
	if($_GET["tanggal"])  $tanggal = format_date($_GET["tanggal"]);
  
  $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $konfig = $dtaccess->Fetch($rs);
  
?>
<!DOCTYPE HTML "//-W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php if  ($isPrint != true) {?>
<?php echo $view->RenderBody("module.css",true,true,"CETAK DATA PEGAWAI"); ?>
<?php }?>
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

<body>
<div id="body">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">

<table width="100%" border="0" cellpadding="5" cellspacing="1" class="tblForm">
   <tr>
        <td align="Center" colspan=2 class="tablesmallheader"><U> DATA PEGAWAI </U></td>
    </tr>
	<tr>
		<td align="left" HEIGHT="15" colspan=2 class="tablecontent-odd">
			<input readonly type="hidden" name="pgw_tipe" class="inputField" value="D">
		</td>
	</tr>
	<tr>
		<td align="Center" colspan=2 class="tablesmallheader"><U><font size="4"><?php echo $depNama; ?> PER
		<?if (!$isPrint) {?>
			<input class="inputField" type="text" onKeyDown="return tabOnEnter(this, event);" size="15" maxlength="10" name="report_pgw" id="report_pgw" value="<?php echo $_POST["report_pgw"];?>" <?php if ($_POST["btnDelete"]) echo "disabled"; ?>>
			<img <?php if ($_POST["btnDelete"]) echo "disabled"; ?> src="<?php echo $ROOT;?>gambar/b_calendar.png" align="middle" hspace=0 vspace="0" width="16" height="16" align="middle" id="img_report_pgw" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />(dd-mm-yyyy)	
		<? } else { ?>
			<?php echo format_date_long($tanggal);?></U>
		<? } ?></font>
		</td>
	</tr>
</table>


<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tblForm">
	<tr>
		<td align="left" class="tablesmallheader" colspan="2"><strong>A. &nbsp;REGRISTRASI KEPEGAWAIAN</strong></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kode Pegawai</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_kode"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Induk Dosen Nasional</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_noinduk_nasional"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Pokok Pegawai</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nip"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Peserta Jamsostek</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_jamsostek"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor AC</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_ac"];?></label></td>
	</tr>
	<tr>
		<td align="left" class="tablesmallheader" colspan="2"><strong>B. &nbsp;KETERANGAN DIRI</strong></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Lengkap</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Panggilan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_panggilan"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Initial</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_initial"];?></label></td>
	</tr>
	<tr>
		<td align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat,Tgl Lahir</td>
		<td align="left" class="tablecontent" >:&nbsp;&nbsp;
			<label><?php echo $row_edit["pgw_tempat_lahir"];?></label> / 
			<label><?php echo format_date($row_edit["pgw_tanggal_lahir"]);?></label>		
		</td>
	</tr>
     <?php if($row_edit["pgw_status_nikah"]=="n")
               $_POST["status_nikah"] = "Belum Nikah";
           elseif($row_edit["pgw_status_nikah"]=="y")
               $_POST["status_nikah"] = "Nikah";
           elseif($row_edit["pgw_status_nikah"]=="j")
               $_POST["status_nikah"] = "Janda";
           elseif($row_edit["pgw_status_nikah"]=="t")
               $_POST["status_nikah"] = "Tunangan";
     ?>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Perkawinan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $_POST["status_nikah"];?></label></td>
	</tr>
     <?php if($row_edit["pgw_jenis_kelamin"]=="L")
               $_POST["jenis_kelamin"] = "Laki - Laki";
           elseif($row_edit["pgw_jenis_kelamin"]=="P")
               $_POST["jenis_kelamin"] = "Wanita";
     ?>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jenis Kelamin</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $_POST["jenis_kelamin"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kewarganegaraan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_warganegara"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Suka Bangsa</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_suku_bangsa"];?></label></td>
	</tr>
	<tr>
		<td class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Agama / Kepercayaan</td>
		<td class="tablecontent" >
			<label>:&nbsp;&nbsp;<?php echo $row_edit["agm_nama"];?></label>
		</td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor KTP</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_ktp_no"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Passport</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_passport_no"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Golongan Darah</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_golongan_darah"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Bank</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_bank"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Rekening</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_rekening"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Email</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_email"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Situs Pribadi</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_situs"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Asal</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_alamat_asal"];?><br><?php echo $row_edit["pgw_kota_asal"];?><br><?php echo $row_edit["pgw_telp_asal"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Surat</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_alamat_surat"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Sekarang</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_alamat_surabaya"];?><br><?php echo $row_edit["pgw_telp_surabaya"];?><br><?php echo $row_edit["pgw_telp_hp"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kotak Darurat</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_kotak_darurat"];?><br><?php echo $row_edit["pgw_kota_darurat_hubungan"];?><br><?php echo $row_edit["pgw_kota_darurat_telp"];?><br><?php echo $row_edit["pgw_kota_darurat_hp"];?></label></td>
	</tr>
	<tr>
		<td align="left" class="tablesmallheader" colspan="2"><strong>C. &nbsp;DATA KELUARGA</strong></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Ayah</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ayah"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Ayah</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_ayah_masih_hidup"]=="y"?"Masih Hidup":"Sudah Meninggal";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Ibu</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ibu"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Ibu</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_ibu_masih_hidup"]=="y"?"Masih Hidup":"Sudah Meninggal";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat Orang Tua</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_alamat_ortu"];?><br><?php echo $row_edit["pgw_telp_ortu"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Ayah Mertua</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ayah_mertua"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Ayah Mertua</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ayah_masih_hidup"]=="y"?"Masih Hidup":"Sudah Meninggal";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Ibu Mertua</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ibu_mertua"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Ibu Mertua</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_ibu_masih_hidup"]=="y"?"Masih Hidup":"Sudah Meninggal";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nama_suami_istri"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Panggilan Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_panggilan_suami_istri"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat Lahir Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_kota_lahir_suami_istri"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Lahir Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_tanggal_lahir_suami_istri"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Kerja Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_status_kerja_suami_istri"]=="y"?"Kerja":"Tidak Kerja";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Instansi Istri / Suami</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_instasi_suami_istri"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tunjangan Keluarga</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo "Tunjangan Suami / Istri &nbsp;&nbsp;&nbsp;&nbsp;".$row_edit["pgw_is_tunj_suami_istri"]=="y"?"Dapat Tunjangan":"Tidak Dapat Tunjangan";?><br>:&nbsp;&nbsp;<?php echo "Tunjangan Anak &nbsp;&nbsp;&nbsp;&nbsp;".$row_edit["pgw_is_tunj_anak"]=="y"?"Dapat Tunjangan":"Tidak Dapat Tunjangan";?></label></td>
	</tr>
	<!--data anak-->
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>D. &nbsp;DATA ANAK</strong></td>
     </tr>
	<tr>
		<td colspan="2">
			<table Border=1 cellpadding=1 cellspacing=1 width="100%">
				<tr>
					<td width="2%" class="tablecontent-odd" align="center">No</td>
					<td width="15%" class="tablecontent-odd" align="center">Nama</td>
					<td width="5%" class="tablecontent-odd" align="center">Jenis Kelamin</td>
					<td width="10%" class="tablecontent-odd" align="center">Tempat Lahir</td>
					<td width="10%" class="tablecontent-odd" align="center">Tanggal Lahir</td>
					<td width="10%" class="tablecontent-odd" align="center">Pendidikan</td>
					<td width="5%" class="tablecontent-odd" align="center">Pekerjaan</td>
					<td width="5%" class="tablecontent-odd" align="center">Nikah</td>
				</tr>
                    <?php for($i=0,$n=$dataAnak?count($dataAnak):5;$i<$n;$i++) { ?>
                         <tr><?php if($dataAnak[$i]["pgw_anak_nama"]=="L")
                                   $_POST["jenis_anak"] = "Laki-laki";
                              elseif($dataAnak[$i]["pgw_anak_nama"]=="P")
                                   $_POST["jenis_anak"] = "Wanita";?>
                              <?php if($dataAnak[$i]["pgw_anak_nikah"]=="Y")
                                   $_POST["nikah_anak"] = "Nikah";
                              elseif($dataAnak[$i]["pgw_anak_nikah"]=="N")
                                   $_POST["nikah_anak"] = "Belum";?>
                                   
                              <td width="2%" class="tablecontent"><?php echo $i+1;?></td>
                              <td width="15%" class="tablecontent"><?php echo $dataAnak[$i]["pgw_anak_nama"]?$dataAnak[$i]["pgw_anak_nama"]:"&nbsp;";?></td>
                              <td width="5%" class="tablecontent"><?php echo $_POST["jenis_anak"]?$_POST["jenis_anak"]:"&nbsp;";?></td>
                              <td width="10%" class="tablecontent"><?php echo $dataAnak[$i]["pgw_anak_kota_lahir"]?$dataAnak[$i]["pgw_anak_kota_lahir"]:"&nbsp;";?></td>
                              <td width="10%" class="tablecontent"><?php echo $dataAnak[$i]["pgw_anak_tanggal_lahir"]?$dataAnak[$i]["pgw_anak_tanggal_lahir"]:"&nbsp;";?></td>
                              <td width="10%" class="tablecontent"><?php echo $dataAnak[$i]["pgw_anak_pendidikan"]?$dataAnak[$i]["pgw_anak_pendidikan"]:"&nbsp;";?></td>
                              <td width="5%" class="tablecontent"><?php echo $dataAnak[$i]["pgw_anak_kerja"]?$dataAnak[$i]["pgw_anak_kerja"]:"&nbsp;";?></td>
                              <td width="5%" class="tablecontent"><?php echo $_POST["nikah_anak"]?$_POST["nikah_anak"]:"&nbsp;";?></td>
                         </tr>
                    <?php } ?>
			</table>
		</td>
	</tr>
	<!--Pendidikan Pegawai-->
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>E. &nbsp;PENDIDIKAN FORMAL</strong></td>
     </tr>
	<tr>
		<td class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Sekolah Dasar</strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Nama</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_sd_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Kota</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_sd_kota"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_sd_tanggal_lulus"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_sd_no_ijasah"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Sekolah Lanjutan Tingkat Pertama</strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Nama</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_sltp_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Kota</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_sltp_kota"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_sltp_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_sltp_no_ijasah"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Sekolah Lanjutan Tingkat Atas</strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Nama</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_slta_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Kota</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_slta_kota"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_slta_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_slta_no_ijasah"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Program Pendidikan Diploma</strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Jurusan</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_diploma_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Perguruan Tinggi</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_diploma_pt_asal"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Kota</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_diploma_kota"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Bidang Ilmu</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_diploma_bidang_ilmu"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_diploma_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_diploma_nomor_ijasah"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Gelar</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_diploma_gelar"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">IPK</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_diploma_ipk"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Program Pendidikan Strata-1 </strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Jurusan</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_s1_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Perguruan Tinggi</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_s1_pt_asal"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Kota</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s1_kota"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Bidang Ilmu</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s1_bidang_ilmu"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_s1_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s1_nomor_ijasah"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Gelar</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s1_gelar"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">IPK</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s1_ipk"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Program Pendidikan Strata-2</strong> </td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Jurusan</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_s2_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Perguruan Tinggi</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_s2_pt_asal"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Kota</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s2_kota"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Bidang Ilmu</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s2_bidang_ilmu"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_s2_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s2_nomor_ijasah"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Gelar</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s2_gelar"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">IPK</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s2_ipk"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Program Pendidikan Strata-3 </strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Jurusan</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_s3_nama"];?></label></td>
				</tr>
				<tr>
					<td width="20">Perguruan Tinggi</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_s3_pt_asal"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Kota</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s3_kota"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Bidang Ilmu</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s3_bidang_ilmu"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Tanggal Lulus</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo format_date($row_edit["pgw_s3_tanggal_lulus"]);?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Nomor Ijasah</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s3_nomor_ijasah"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Gelar</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s3_gelar"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">IPK</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_s3_ipk"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="tablecontent-odd"  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Sebutan dan Gelar</strong></td>
		<td class="tablecontent"  >
			<table Border=0 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="20%">Gelar Muka</td><td width="1%">:</td>
					<td  width="80%"><label><?php echo $row_edit["pgw_gelar_muka"];?></label></td>
				</tr>
				<tr>
					<td width="20">Gelar Belakang</td><td width="1%">:</td>
					<td   width="80%"><label><?php echo $row_edit["pgw_gelar_belakang"];?></label></td>
				</tr>
				<tr>
					<td valign="top" width="20%">Bidang Keahlian</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_bidang_keahlian"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Gelar Tertinggi</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_gelar_tertinggi"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Pendidikan Tertinggi</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_pendidikan_tertinggi"];?>
						</label>
					</td>
				</tr>
				<tr>
					<td valign="top" width="20%">Akta V</td><td valign="top" width="1%">:</td>
					<td valign="top"><label><?php echo $row_edit["pgw_akta_v"];?>
						</label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>F. &nbsp;KEMAMPUAN BAHASA</strong></td>
     </tr>
	<tr>
		<td colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="2%" class="tablecontent-odd" align="center">No</td>
					<td  width="20%" class="tablecontent-odd" align="center">Jenis Bahasa</td>
					<td  width="10%" class="tablecontent-odd" align="center">Penguasaan Tertulis</td>
					<td  width="10%" class="tablecontent-odd" align="center">Penguasaan Lisan</td>
					<td  width="10%" class="tablecontent-odd" align="center">Tahun</td>
				</tr>
				<?php for($i=0,$n=$dataBahasa?count($dataBahasa):5;$i<$n;$i++) { ?>
					<tr>
						<?php if($dataBahasa[$i]["pgw_bhs_lisan"]=="y")
								$_POST["bahasa_lisan"] = "Aktif";
							elseif($dataBahasa[$i]["pgw_bhs_lisan"]=="n")
								$_POST["bahasa_lisan"] = "Pasif";
							
							if($dataBahasa[$i]["pgw_bhs_tulis"]=="y")
								$_POST["bahasa_tulis"] = "Aktif";
							elseif($dataBahasa[$i]["pgw_bhs_tulis"]=="n")
								$_POST["bahasa_tulis"] = "Pasif";?>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></label></td>
						<td  width="20%" class="tablecontent"><label><?php echo $dataBahasa[$i]["pgw_bhs_nama"]?$dataBahasa[$i]["pgw_bhs_nama"]:"&nbsp;";?></label></td>
						<td  width="10%" class="tablecontent"><label><?php echo $_POST["bahasa_tulis"]?$_POST["bahasa_tulis"]:"&nbsp;";?></label></td>
						<td  width="10%" class="tablecontent"><label><?php echo $_POST["bahasa_tulis"]?$_POST["bahasa_tulis"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataBahasa[$i]["pgw_bhs_tahun"]?$dataBahasa[$i]["pgw_bhs_tahun"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>G. &nbsp;PENDIDIKAN NON FORMAL DAN PROFESI</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="2%" class="tablecontent-odd" align="center">No</td>
					<td  width="20%" class="tablecontent-odd" align="center">Jenis</td>
					<td  width="10%" class="tablecontent-odd" align="center">Jurusan / Spesialisasi</td>
					<td  width="10%" class="tablecontent-odd" align="center">Gelar</td>
					<td  width="10%" class="tablecontent-odd" align="center">Tahun Ijasah</td>
					<td  width="10%" class="tablecontent-odd" align="center">Instansi</td>
				</tr>
				<?php for($i=0,$n=$dataKursus?count($dataKursus):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></label></td>
						<td  width="20%" class="tablecontent"><label><?php echo $dataKursus[$i]["pgw_kursus_nama"]?$dataKursus[$i]["pgw_kursus_nama"]:"&nbsp;";?></label></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKursus[$i]["pgw_kursus_materi"]?$dataKursus[$i]["pgw_kursus_materi"]:"&nbsp;";?></label></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKursus[$i]["pgw_kursus_gelar"]?$dataKursus[$i]["pgw_kursus_gelar"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKursus[$i]["pgw_kursus_tahun"]?$dataKursus[$i]["pgw_kursus_tahun"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKursus[$i]["pgw_kursus_instansi"]?$dataKursus[$i]["pgw_kursus_instansi"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>H. &nbsp;TANDA JASA / PENGHARGAAN</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="2%" class="tablecontent-odd" align="center">No</td>
					<td  width="20%" class="tablecontent-odd" align="center">Penghargaan</td>
					<td  width="10%" class="tablecontent-odd" align="center">Tahun Ijasah</td>
					<td  width="10%" class="tablecontent-odd" align="center">Instansi</td>
				</tr>
				<?php for($i=0,$n=$dataJasa?count($dataJasa):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataJasa[$i]["pgw_jasa_nama"]?$dataJasa[$i]["pgw_jasa_nama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataJasa[$i]["pgw_jasa_tahun"]?$dataJasa[$i]["pgw_jasa_tahun"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataJasa[$i]["pgw_jasa_instansi"]?$dataJasa[$i]["pgw_jasa_instansi"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>I. &nbsp;KUNJUNGAN KELUAR NEGERI</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="2%" class="tablecontent-odd" align="center">No</td>
					<td  width="10%" class="tablecontent-odd" align="center">Negara</td>
					<td  width="20%" class="tablecontent-odd" align="center">Tujuan Kunjungan </td>
					<td  width="10%" class="tablecontent-odd" align="center">Lama</td>
					<td  width="10%" class="tablecontent-odd" align="center">Sumber Biaya</td>
				</tr>
				<?php for($i=0,$n=$dataKunjung?count($dataKunjung):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKunjung[$i]["pgw_kunjung_negara"]?$dataKunjung[$i]["pgw_kunjung_negara"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKunjung[$i]["pgw_kunjung_tujuan"]?$dataKunjung[$i]["pgw_kunjung_tujuan"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKunjung[$i]["pgw_kunjung_lama"]?$dataKunjung[$i]["pgw_kunjung_lama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataKunjung[$i]["pgw_kunjung_biaya"]?$dataKunjung[$i]["pgw_kunjung_biaya"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>J. &nbsp;P E K E R J A A N </strong></td>
     </tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Unit Kerja</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["struk_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Status Pegawai</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["jab_kon_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pangkat Saat Diterima</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["golongan"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Masa Kerja diterima</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_masa_kerja_diterima"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pangkat Sekarang</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["gol_pangkat"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Masa Kerja Golongan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_masa_kerja_golongan"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jenis Pegawai</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pos_jenis_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Masuk</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tanggal_masuk"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor SK Pengangkatan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_sk_pangkat"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal SK Pengangkatan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tanggal_habis_sk"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TMT</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tmt_pangkat"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Keluar</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tanggal_keluar"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alasan Keluar</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_alasan_keluar"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan Struktural</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["jab_struk_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor SK Jabatan Struktural</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_sk_jab_struktural"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Masa Kerja Jabatan Struktural</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_masa_kerja"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jam Masuk</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_jam_masuk"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dapat Plafon Rawat Jalan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_is_plafon_jalan"]=="y"?"Dapat":"Tidak";?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dapat Plafon Rawat Inap</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_is_plafon_inap"]=="y"?"Dapat":"Tidak";?></label></td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>K. &nbsp;PEGAWAI EDUKATIF</strong></td>
     </tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan Akademik</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["jab_akad_nama"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor SK Jabatan Akademik</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_no_sk_jab_akademik"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal SK</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tanggal_sk_akademik"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TMT Jabatan Akademik</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo format_date($row_edit["pgw_tmt_akademik"]);?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fakultas</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_base_fakultas"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jurusan</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_base_jurusan"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Program Studi</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_base_program_studi"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Instansi Asal</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_instansi_asal"];?></label></td>
	</tr>
	<tr>
		<td width="25%" align="left" class="tablecontent-odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NIP PNS</td>
		<td align="left" class="tablecontent" ><label>:&nbsp;&nbsp;<?php echo $row_edit["pgw_nip_pns"];?></label></td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>L. &nbsp;KEPANGKATAN DAN JABATAN</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="2" class="tablecontent-odd" align="center">No</td>
					<td  width="15%" class="tablecontent-odd" align="center">Pangkat</td>
					<td  width="15%" class="tablecontent-odd" align="center">Jabatan - Golongan / Ruang</td>
					<td  width="10%" class="tablecontent-odd" align="center">Sejak Tanggal</td>
					<td  width="10%" class="tablecontent-odd" align="center">Unit Kerja</td>
				</tr>
				<?php for($i=0,$n=count($dataPangkat)?count($dataPangkat):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataPangkat[$i]["pgw_pangkat_nama"]?$dataPangkat[$i]["pgw_pangkat_nama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataPangkat[$i]["gol_pangkat"]?$dataPangkat[$i]["gol_pangkat"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataPangkat[$i]["pgw_pangkat_tanggal"]?format_date($dataPangkat[$i]["pgw_pangkat_tanggal"]):"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataPangkat[$i]["struk_nama"]?$dataPangkat[$i]["struk_nama"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>M. &nbsp;RIWAYAT PEKERJAAN</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=1 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="1%" rowspan="2" class="tablecontent-odd" align="center">No</td>
					<td  width="15%" rowspan="2" class="tablecontent-odd" align="center">Riwayat Pekerjaan</td>
					<td  width="15%" colspan="3" class="tablecontent-odd" align="center">Surat Keputusan</td>
					<td  width="10%" rowspan="2" class="tablecontent-odd" align="center">Golongan / Ruang Gaji</td>
					<td  width="10%" rowspan="2" class="tablecontent-odd" align="center">Gaji Pokok</td>
					<td  width="10%" rowspan="2" class="tablecontent-odd" align="center">TMT</td>
					<td  width="10%" rowspan="2" class="tablecontent-odd" align="center">SDT</td>
					<td  width="10%" rowspan="2" class="tablecontent-odd" align="center">Keterangan</td>
				</tr>
					<tr>
						<td  width="10%" class="tablecontent-odd" align="center">Pejabat</td>
						<td  width="10%" class="tablecontent-odd" align="center">Nomor</td>
						<td  width="10%" class="tablecontent-odd" align="center">Tanggal</td>
					</tr>
				<?php for($i=0,$n=$dataRiwayat?count($dataRiwayat):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="1%" class="tablecontent"><label><?php echo $i+1;?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_nama"]?$dataRiwayat[$i]["pgw_riwayat_nama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_pejabat"]?$dataRiwayat[$i]["pgw_riwayat_pejabat"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_nomor"]?$dataRiwayat[$i]["pgw_riwayat_nomor"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_tanggal"]?$dataRiwayat[$i]["pgw_riwayat_tanggal"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["gol_nama"]?$dataRiwayat[$i]["gol_nama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_gaji_pokok"]?$dataRiwayat[$i]["pgw_riwayat_gaji_pokok"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_tmt"]?$dataRiwayat[$i]["pgw_riwayat_tmt"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_sdt"]?$dataRiwayat[$i]["pgw_riwayat_sdt"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataRiwayat[$i]["pgw_riwayat_ket"]?$dataRiwayat[$i]["pgw_riwayat_ket"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>N. &nbsp;PENGALAMAN PEKERJAAN</strong></td>
     </tr>
	<tr>
		<td colspan="2" >
               <table Border=0 cellpadding=2 cellspacing=1 width="100%">
				<?php for($i=0,$n=count($dataPengalaman)?count($dataPengalaman):5;$i<$n;$i++) { ?>
                         <tr>
                              <td  width="15%" class="tablecontent-odd" rowspan="5">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Instansi <?php echo ($i+1);?></strong></td>
                              <td  width="15%" class="tablecontent">Nama Instansi</td>
                              <td  width="85%" class="tablecontent"><label>:&nbsp;&nbsp;<?php echo $dataPengalaman[$i]["pgw_pengalaman_nama"]?$dataPengalaman[$i]["pgw_pengalaman_nama"]:"&nbsp;";?></td>
                         </tr>
                         <tr>
                              <td  width="10%" class="tablecontent">Tanggal Masuk</td>
                              <td  width="85%" class="tablecontent"><label>:&nbsp;&nbsp;<?php echo $dataPengalaman[$i]["pgw_pengalaman_tanggal_masuk"]?format_date($dataPengalaman[$i]["pgw_pengalaman_tanggal_masuk"]):"&nbsp;";?></td>
                         </tr>
                         <tr>
                              <td  width="10%" class="tablecontent">Tanggal Keluar</td>
                              <td  width="85%" class="tablecontent"><label>:&nbsp;&nbsp;<?php echo $dataPengalaman[$i]["pgw_pengalaman_tanggal_keluar"]?format_date($dataPengalaman[$i]["pgw_pengalaman_tanggal_keluar"]):"&nbsp;";?></td>
                         </tr>
                         <tr>
                              <td  width="10%" class="tablecontent">Golongan</td>
                              <td  width="85%" class="tablecontent"><label>:&nbsp;&nbsp;<?php echo $dataPengalaman[$i]["pgw_pengalaman_golongan"]?$dataPengalaman[$i]["pgw_pengalaman_golongan"]:"&nbsp;";?></td>
                         </tr>
                         <tr>
                              <td  width="10%" class="tablecontent">Alasan Keluar</td>
                              <td  width="85%" class="tablecontent"><label>:&nbsp;&nbsp;<?php echo $dataPengalaman[$i]["pgw_pengalaman_alasan_keluar"]?$dataPengalaman[$i]["pgw_pengalaman_alasan_keluar"]:"&nbsp;";?></td>
                         </tr>
				<?php } ?>
               </table>
		</td>
	</tr>
     <tr>
          <td colspan="2" class="tablesmallheader"><strong>O. &nbsp;ORGANISASI PROFESI</strong></td>
     </tr>
	<tr>
		<td class="tablecontent" colspan="2" >
			<table Border=1 cellpadding=3 cellspacing=0 width="100%"  class="tablecontent">
				<tr>
					<td  width="3%" class="tablecontent-odd" align="center">No</td>
					<td  width="15%" class="tablecontent-odd" align="center">Nama Organisasi</td>
					<td  width="15%" class="tablecontent-odd" align="center">Sebagai</td>
					<td  width="10%" class="tablecontent-odd" align="center">Tanggal Masuk</td>
					<td  width="10%" class="tablecontent-odd" align="center">Status</td>
				</tr>
				<?php for($i=0,$n=count($dataOrga)?count($dataOrga):5;$i<$n;$i++) { ?>
					<tr>
						<td  width="2%" class="tablecontent"><label><?php echo $i+1;?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataOrga[$i]["pgw_org_prof_nama"]?$dataOrga[$i]["pgw_org_prof_nama"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataOrga[$i]["pgw_org_prof_sebagai"]?$dataOrga[$i]["pgw_org_prof_sebagai"]:"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataOrga[$i]["pgw_org_prof_tanggal_masuk"]?format_date($dataOrga[$i]["pgw_org_prof_tanggal_masuk"]):"&nbsp;";?></td>
						<td  width="10%" class="tablecontent"><label><?php echo $dataOrga[$i]["pgw_org_prof_status"]?$dataOrga[$i]["pgw_org_prof_status"]:"&nbsp;";?></td>
					</tr>
				<?php } ?>
			</table>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" class="tablecontent-odd" colspan="5"></td>
	</tr>
	<?if (!$isPrint) {?>
	<tr>
		<td class="tablecontent-odd" align="right">Tanda Tangan Surat&nbsp;&nbsp;</td>
		<td class="tablecontent" >
			<select name="pejabat_id" class="inputField">            
				<?php for($i=0,$n=count($dataPjPenandatangan);$i<$n;$i++){ ?>
					<option class="inputField" value="<?php echo $dataPjPenandatangan[$i]["id_pgw"];?>" <?php if($dataPjPenandatangan[$i]["id_pgw"]==$_POST["pejabat_id"]) echo "selected"; ?> ><?php echo $dataPjPenandatangan[$i]["pgw_nama"];?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<? } else { ?>
	<BR>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">
		 <?php echo $depNama;?>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">
    <?php echo $konfig["dep_kota"];?>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">
		 <u><?php if($tandaTangan["pgw_gelar_muka"]) echo $tandaTangan["pgw_gelar_muka"]." ";?><?php echo $tandaTangan["pgw_nama"];?><?php if($tandaTangan["pgw_gelar_belakang"]) echo " ".$tandaTangan["pgw_gelar_belakang"];?></u>
		</td>
	</tr>
	<tr>
		<td align="left" HEIGHT="20" colspan="5">
		<?php echo $tandaTangan["pejabat_an"];?>
		</td>
	</tr>
	<? } ?>
	<TR>
        <td align="center" class="tablecontent" colspan="5">
           <?php if  ($isPrint != true) {?> <input type="button" name="btnNew" value="Kembali" class="inputField" onClick="document.location.href='data_pegawai_view.php?pgw_nama=<?php echo $_POST["pgw_nama"];?>'"/> <?php }?>
			&nbsp;&nbsp;
			<?php if  ($isPrint != true) {?>  <input type="submit" name="btnPrint" value="Cetak" class="inputField" onClick="document.location.href='data_pegawai_view.php?pgw_nama=<?php echo $_POST["pgw_nama"];?>'"/> <?php }?>
        </td>
	</tr>
</table>
<?php if (!$isPrint) {?>
<script type="text/javascript">
	Calendar.setup({
        inputField     :    "report_pgw",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_report_pgw",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php }?>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode?>" />
<input type="hidden" name="plamar_id" value="<?php echo $plamarId;?>">
<input type="hidden" name="pgw_id" value="<?php echo $pgwId;?>">
<input type="hidden" name="nama" value="<?php echo $_POST["nama"];?>">
</form>

</div>
</body>
<?php if(!$isPrint) { ?>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
<?php } ?>
</html>
<?
    $dtaccess->Close();
?>
