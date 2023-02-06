<?php
     // LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."tampilan.php");

     //INISIALISAI AWAL LIBRARY
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$tahunTarif = $auth->GetTahunTarif();
$userLogin = $auth->GetUserData();

     //AUTHENTIKASI
if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
	die("Maaf anda tidak berhak membuka halaman ini....");
	exit(1);
} else 
if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
	echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
	exit(1);
}  

    //DATA AWAL
$tglSekarang = date("d-m-Y");

	// Data tipe poli
$sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J' or poli_tipe_id='I') order by poli_tipe_nama asc";    
$rs = $dtaccess->Execute($sql);
$dataTipe = $dtaccess->FetchAll($rs);

	 //data gedung
$sql = "select * from global.global_gedung_rawat 
order by gedung_rawat_nama, gedung_lantai_ke asc ";     
$rs = $dtaccess->Execute($sql);
$dataGedungRawat = $dtaccess->FetchAll($rs);

     //data kelas
$sql = "select * from klinik.klinik_kelas order by kelas_id";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataKelas = $dtaccess->FetchAll($rs);


/* SQL AGAMA */
$sql = "select * from global.global_agama order by agm_id";
$dataAgama = $dtaccess->FetchAll($sql);
/* SQL AGAMA */

/* SQL PENDIDIKAN */
$sql = "select * from global.global_pendidikan order by pendidikan_urut";
$dataPendidikan = $dtaccess->FetchAll($sql);
/* SQL PENDIDIKAN */

/* SQL PEKERJAAN */
$sql = "select * from global.global_pekerjaan order by pekerjaan_nama";
$dataPekerjaan = $dtaccess->FetchAll($sql);
/* SQL PEKERJAAN */

/* SQL NEGARA */
$sql = "select * from global.global_negara order by negara_nama asc";
$dataNegara = $dtaccess->FetchAll($sql);
/* SQL NEGARA */

/* SQL STATUS PERKAWINAN */
$sql = "select * from global.global_status_perkawinan order by status_perkawinan_nama";
$dataStatus = $dtaccess->FetchAll($sql);
/* SQL STATUS PERKAWINAN */

/* SQL STATUS PENANGGUNG JAWAB */
$sql = "select * from global.global_status_pj order by status_pj_nama";
$dataStatusPJ = $dtaccess->FetchAll($sql);
/* SQL STATUS PENANGGUNG JAWAB */

/* SQL INSTALASI */
$sql = "select instalasi_id, instalasi_nama from global.global_auth_instalasi";
$dataInstalasi = $dtaccess->FetchAll($sql);
/* SQL INSTALASI */

/* SQL SEBAB SAKIT */
$sql = "select * from global.global_sebab_sakit";
$dataSebabSakit = $dtaccess->FetchAll($sql);
/* SQL SEBAB SAKIT */

/* SQL TIPE LAYANAN */
$sql = "select * from  global.global_tipe_biaya where tipe_biaya_aktif ='y' ";
$dataLayanan = $dtaccess->FetchAll($sql);
/* SQL TIPE LAYANAN */

/* SQL SHIFT */
$sql = "select * from  global.global_shift a where a.shift_aktif='y' order by shift_id limit 1";
$dataShift = $dtaccess->FetchAll($sql);
/* SQL SHIFT */

/* SQL PROVINSI */
$sql = "select * from  global.global_lokasi where lokasi_kabupatenkota='00' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' order by lokasi_id";
$dataProvinsi = $dtaccess->FetchAll($sql);

/* SQL JENIS PASIEN */
$sql = "select * from  global.global_jenis_pasien a";
$dataJPasien = $dtaccess->FetchAll($sql);
/* SQL JENIS PASIEN */

/* SQL JKN */
$sql = "select * from  global.global_jkn order by jkn_id desc";
$dataJKN = $dtaccess->FetchAll($sql);
/* SQL JKN */

/* SQL PERUSAHAAN */
$sql = "select * from  global.global_perusahaan order by perusahaan_nama asc";
$dataIKS = $dtaccess->FetchAll($sql);
/* SQL PERUSAHAAN */

/* SQL GEDUNG */
$sql = "select * from global.global_gedung_rawat order by gedung_rawat_nama, gedung_lantai_ke asc ";
$dataGedungRawat = $dtaccess->FetchAll($sql);
/* SQL GEDUNG */

/* SQL KELAS */
$sql = "select * from klinik.klinik_kelas order by kelas_id";
$dataKelas = $dtaccess->FetchAll($sql);
/* SQL KELAS */

/* OPTION KELAS */
for ($i = 0, $n = count($dataKelas); $i < $n; $i++) {
	unset($show);
	if ($_POST["id_kelas"] == $dataKelas[$i]["kelas_id"]) $show = "selected";
	$opt_kategori[$i] = $view->RenderOption($dataKelas[$i]["kelas_id"], $dataKelas[$i]["kelas_nama"], $show);
	$opt_kamar[0] = $view->RenderOption("--", "[pilih kamar]", $show);

	$opt_bed[0] = $view->RenderOption("--", "[pilih bed]", $show);
	if ($_POST["id_kamar"] && $_POST["id_kamar"] != "--") {
		$opt_bed[0] = $view->RenderOption("--", "[pilih bed]", $show);
	}
}
/* OPTION KELAS */

/* SQL DOKTER DAN PELAKSANA */
$sql = "select * from global.global_auth_user a left join global.global_auth_role b on a.id_rol = b.rol_id where (rol_jabatan = 'D' or rol_jabatan='R' or rol_jabatan='A') and a.id_dep =" . QuoteValue(DPE_CHAR, $depId) . " order by usr_name asc";
// $dataDokter = $dtaccess->FetchAll($sql);
$dataPelaksana = $dtaccess->FetchAll($sql);
/* SQL DOKTER DAN PELAKSANA */

/* SQL PROCEDUR MASUK */
$sql = "select * from global.global_prosedur_masuk";
$dataProsedurMasuk = $dtaccess->FetchAll($sql);
/* SQL PROCEDUR MASUK */

/* SQL POLI */
$sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='M' or poli_tipe_id='R' or poli_tipe_id='L' or poli_tipe_id='G' or poli_tipe_id='J' or poli_tipe_id='I') order by poli_tipe_nama asc";
$dataTipe = $dtaccess->FetchAll($sql);
/* SQL POLI */

/* SQL POLI TIPE */
$sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J') order by poli_tipe_nama asc";
$dataAsal = $dtaccess->FetchAll($sql);
/* SQL POLI TIPE */

$lokasi = $ROOT . "gambar/foto_pasien";
$lokTakeFoto = $ROOT . "gambar/foto_pasien";


if ($_GET['usr_id']) {
	/* SQL DATA PASIEN */
	$sql = "select * from global.global_customer_user where cust_usr_id = '$_GET[usr_id]'";
	$row = $dtaccess->Fetch($sql);
	/* SQL DATA PASIEN */

	$usr_kode = $row['cust_usr_kode'];
	$arr = str_split($usr_kode, "2");
	$usr_kode_tampilan = implode(".", $arr);

	/* SQL POLI KE 2 */
	$sql = "select reg_id, reg_tipe_rawat, id_poli, b.poli_nama from klinik.klinik_registrasi a left join global.global_auth_poli b on a.id_poli = b.poli_id";
	$sql .= " WHERE id_cust_usr = " . QuoteValue(DPE_CHAR, $row[cust_usr_id]) . " and reg_tanggal = " . QuoteValue(DPE_DATE, date('Y-m-d'));
	$sql .= " and (poli_tipe <> 'A' and poli_tipe <> 'R' and poli_tipe <> 'L')";
	$sql .= " order by reg_waktu desc";
	$reg = $dtaccess->Fetch($sql);
	$polike2 = $dtaccess->FetchAll($sql);
	/* SQL POLI KE 2 */

	/* Menentukan Tanggal Lahir */
	$birthday = $row['cust_usr_tanggal_lahir'];
	$today = new DateTime();
	$biday = new DateTime($birthday);
	$diff = $today->diff($biday);

	$tahun = $diff->y;
	$bulan = $diff->m;
	$hari = $diff->d;
	/* Menentukan Tanggal Lahir */

	if ($_GET['status_pasien'] == 'L') {
		/* Pengecekan JKN IRNA Terakhir */
		$sql = "select reg_id, reg_tipe_rawat, id_poli, reg_tanggal, b.rawatinap_tanggal_keluar, reg_cara_keluar_inap from klinik.klinik_registrasi a ";
		$sql .= " left join klinik.klinik_rawatinap b on a.reg_id = b.id_reg ";
		$sql .= " WHERE id_cust_usr = " . QuoteValue(DPE_CHAR, $row[cust_usr_id]) . " and reg_jenis_pasien = '5' and reg_status ='I5' and reg_tipe_rawat = 'I'";
		$sql .= " order by reg_waktu desc";
		$jkn_last = $dtaccess->Fetch($sql);
		/* Pengecekan JKN IRNA Terakhir */

		if ($jkn_last) {
			$last = new DateTime($jkn_last['rawatinap_tanggal_keluar']);
			$last_jkn = $today->diff($last);
			$last_jkn_hari = $last_jkn->d;
		}
	}

	$sql = "select poli_tipe from klinik.klinik_registrasi a left join global.global_auth_poli b on b.poli_id = a.id_poli where reg_utama is null and id_cust_usr = ".QuoteValue(DPE_CHAR, $_GET['usr_id'])." order by reg_tanggal desc, reg_waktu desc";
	$dataInstalasiTerakhir = $dtaccess->Fetch($sql);

  #fix kebangsaan 
	if ($row["cust_usr_asal_negara"] == "") $row["cust_usr_asal_negara"] = '1';
}

for($i=0,$n=count($dataKelas);$i<$n;$i++){
	unset($show);
	if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
	$opt_kategori[$i] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
	$opt_kamar[0] = $view->RenderOption("--","[pilih kamar]",$show);
	
	$opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
	if($_POST["id_kamar"] && $_POST["id_kamar"]!="--"){
		$opt_bed[0] = $view->RenderOption("--","[pilih bed]",$show);
	}
}

	 // Data asal poli
$sql = "select * from global.global_auth_poli_tipe where (poli_tipe_id='G' or poli_tipe_id='J') order by poli_tipe_nama asc"; 
$rs = $dtaccess->Execute($sql);
$dataAsal = $dtaccess->FetchAll($rs);

     // Data poli
$sql = "select * from global.global_auth_poli order by poli_nama asc"; 
$rs = $dtaccess->Execute($sql);
$polike2 = $dtaccess->FetchAll($rs);

	//cari data Sebab Sakit
$sql = "select * from global.global_sebab_sakit";
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataSebabSakit = $dtaccess->FetchAll($rs);

	 // Data prosedur masuk
$sql = "select * from global.global_prosedur_masuk";    
$rs = $dtaccess->Execute($sql);
$dataProsedurMasuk = $dtaccess->FetchAll($rs);

	 // Data jenis pasien yang ditampilkan umum saja//
$sql = "select * from  global.global_jenis_pasien a";
    // $sql .= " where jenis_id<>".PASIEN_BAYAR_BPJS." and jenis_flag='y'";
    //echo $sql;
$rs = $dtaccess->Execute($sql);
$dataJPasien = $dtaccess->FetchAll($rs);

	 // Data jenis jkn
$sql = "select * from  global.global_jkn order by jkn_id desc";
$rs = $dtaccess->Execute($sql);
$dataJKN = $dtaccess->FetchAll($rs);

      // Data jenis iks
$sql = "select * from  global.global_perusahaan order by perusahaan_nama asc";
$rs = $dtaccess->Execute($sql);
$dataIKS = $dtaccess->FetchAll($rs);

$sql = "select a.usr_name,a.usr_id,c.rol_jabatan
from global.global_auth_user a 
left join global.global_auth_role c on a.id_rol = c.rol_id";
$sql .= " where c.rol_jabatan = 'D'";   
$sql .= " and usr_status = 'y'";    
    //$sql .= " and d.jadwal_dokter_jam_mulai <".QuoteValue(DPE_DATE,date("H:i:s"));
    //$sql .= " and d.jadwal_dokter_jam_selesai >".QuoteValue(DPE_DATE,date("H:i:s"));
    //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_CHAR,date('N'));
    //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_NUMERIC,GetDayNameNew(date_db($tglSekarang))); 
$sql .= " order by usr_name asc";   
$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
$dataDokter = $dtaccess->FetchAll($rs);
 
// $sql = "select a.usr_name,a.usr_id,b.id_poli,c.rol_jabatan
// 		from global.global_auth_user a 
// 		left join global.global_auth_user_poli b on a.usr_id = b.id_usr
// 		left join global.global_auth_role c on a.id_rol = c.rol_id
// 		left join klinik.klinik_jadwal_dokter d on a.usr_id = d.id_dokter";
// $sql .= " where c.rol_jabatan = 'D'  and b.id_poli=d.id_poli";   
// $sql .= " and usr_status = 'y'";    
// $sql .= " and b.id_poli =".QuoteValue(DPE_CHAR,$_POST['poli_id']);
// $sql .= " and d.jadwal_dokter_jam_mulai <".QuoteValue(DPE_DATE,date("H:i:s"));
// $sql .= " and d.jadwal_dokter_jam_selesai >".QuoteValue(DPE_DATE,date("H:i:s"));
// $sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_CHAR,date('N'));
// //$sql .= " and d.jadwal_dokter_hari =".QuoteValue(DPE_NUMERIC,GetDayNameNew(date_db($tglSekarang))); 
// $sql .= " order by usr_name asc";   
// $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
// $dataDokter = $dtaccess->FetchAll($rs);


$sql = "select id_dokter from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR,$_GET['reg_id']);
$dataReg = $dtaccess->Fetch($sql);

	 //tabel header
$tableHeader = "Edit Registrasi";
$addpasien = '<input type="button" name="btnadd" value="Registrasi Pasien Baru" class="btn btn-primary col-md-3 pull-right" onClick="document.location.href=\''.$pasienadd.'\'"></button>';
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY."header.php") ?>

<script type="text/javascript">
	function getKlinik(param){
		if (param == 'I') {
			$("#div_klinik").css('display','none');
			$("#div_klinik_asal").css('display','none');
			$("#div_gedung").css('display','block');
			$("#div_kelas").css('display','block');
			$("#div_kamar").css('display','block');
			$("#div_bed").css('display','block');
			$("#div_asal").css('display','block');
			$("#div_reg_tanggal").css('display','block');
		} else { 
			$("#div_klinik").css('display','block');
			$("#div_klinik_asal").css('display','block');
			$("#div_gedung").css('display','none');
			$("#div_kelas").css('display','none');
			$("#div_kamar").css('display','none');
			$("#div_bed").css('display','none');
			$("#div_asal").css('display','none');
			$("#div_reg_tanggal").css('display','block');
		}
		
		if(param){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'instalasi_id='+param,
				success:function(html){
					$('#klinik').html(html);
					$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
				}
			}); 
		}else{
			$('#klinik').html('<option value="">Pilih Instalasi Dahulu</option>');
			$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
		}
	};
	
	function getDokter(param){
		if(param){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'poli_id='+param,
				success:function(html){
					$('#dokter').html(html);
				}
			});
			getPaket(param);            
		}else{
			$('#dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
			$('#paket').html('<option value="">Pilih Klinik Dahulu</option>');
		}
	};

	function getPoliAsal(param){
		$.ajax({
			type:'GET',
			url:'poli_asal.php',
			data:'id_cust_usr='+param,
			success:function(html){
				$('#klinik_asal').html(html);
			}
		});           
	};

	function dokter(param){
		$.ajax({
			type:'GET',
			url:'dokter.php',
			data:'id_cust_usr='+param,
			success:function(html){
				$('#ubah_dokter').html(html);
			}
		});           
	};
	
	function getPaket(param){
		if(param){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'id_poli='+param,
				success:function(html){
					$('#paket').html(html);
				}
			});             
		}
	};
	
	function getKamarbyKelas(param){
		var kelas_id = param;
		var gedung_id = $('#id_gedung_rawat').val();
		if(kelas_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'kelas_id='+kelas_id+'&gedung_id='+gedung_id,
				success:function(html){
					$('#id_kamar').html(html);
					$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>'); 
				}
			}); 
		}else{
			$('#id_kamar').html('<option value="">Pilih Gedung dan Kelas Dahulu</option>'); 
			$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
		}
	};
	
	function getBed(param){
		var kamar_id = param;
		if(kamar_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'kamar_id='+kamar_id,
				success:function(html){
					$('#id_bed').html(html);
				}
			}); 
		}else{
			$('#id_bed').html('<option value="">Pilih Kamar Dahulu</option>');
		}
		
	};
	
	function getCaraKunjungan(param){
		var prosedur_id = param;
		if(prosedur_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'prosedur_id='+prosedur_id,
				success:function(html){
					$('#reg_rujukan_id').html(html); 
					$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
				}
			}); 
		}else{
			$('#reg_rujukan_id').html('<option value="">Pilih Prosedur Dahulu</option>');
			$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function getPoli(param){
		var poli_id = param;
		if(poli_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'poli_id='+poli_id,
				success:function(html){
					$('#poli_id').html(html); 
					// $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
				}
			}); 
		}else{
			// $('#reg_rujukan_id').html('<option value="">Pilih Prosedur Dahulu</option>');
			// $('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function getCaraKunjunganDet(param){
		var rujukan_id = param;
		if(rujukan_id){
			$.ajax({
				type:'POST',
				url:'RS_Data.php',
				data:'rujukan_id='+rujukan_id,
				success:function(html){
					$('#reg_rujukan_det').html(html); 
				}
			}); 
		}else{
			$('#reg_rujukan_det').html('<option value="">Pilih Kunjungan Dahulu</option>');
		}
	};
	
	function cekCaraBayar(param){
		var jenis_pasien = param;
		if(jenis_pasien=='5'){ //pasien jkn
			$('#bpjs').css('display','block');
			$("#div_jkn").css('display','block');
			$("#div_iks").css('display','none');
			$("#tipe_jkn").removeAttr("disabled") ; 
			$("#tipe_iks").attr("disabled","disabled");
        } else if(jenis_pasien=='7'){ //cara bayar iks
        	$('#bpjs').css('display','none');
        	$("#div_jkn").css('display','none');
        	$("#div_iks").css('display','block');
        	$("#tipe_jkn").attr("disabled","disabled");
        	$("#tipe_iks").removeAttr("disabled") ; 
        }else{
        	$('#bpjs').css('display','none');   
        	$("#div_jkn").css('display','none');
        	$("#div_iks").css('display','none');
        	$("#tipe_jkn").attr("disabled","disabled");
        	$("#tipe_iks").attr("disabled","disabled");
        }
    };
    
    function getDokterIrna(param){
    	$.ajax({
    		type:'POST',
    		url:'RS_Data.php',
    		data:'irna=irna',
    		success:function(html){
    			$('#dokter').html(html);
    		}
    	}); 
    };
</script>


<script type="text/javascript">
	//filter field data 
	$(function(){

			//auto complete
			$('#diagnosa').autocomplete({
				serviceUrl: 'get_icd.php',
				paramName: 'q',
				transformResult: function(response) {
					var data = jQuery.parseJSON(response);
					return {
						suggestions: $.map(data, function(item) {
							return { value: item.icd_nomor+" - "+item.icd_nama, data: item.icd_nomor };
						})
					};
				},
				onSelect: function (suggestion) {
					$('#reg_diagnosa_awal').val(suggestion.data);
				}
			});

			var dg = $('#dg').datagrid();
			
			dg.datagrid('enableFilter', [
			//disable filter
			{field:'reg_waktu',type:'label'},
			{field:'cust_usr_kode_tampilan',type:'text'},
			{field:'cust_usr_nama',type:'text'},
			{field:'cust_usr_alamat',type:'label'},
			{field:'cust_usr_tanggal_lahir',type:'label'},
			//enable filter
			{
				field:'reg_status', //filter status
				type:'combobox',
				options:{
					data: [{
						label: 'Semua',
						value: ''
					},{
						label: 'Belum Dilayani',
						value: 'Belum Dilayani'
					},{
						label: 'Sampai di Poli',
						value: 'Sampai di Poli'
					},{
						label: 'Sudah Dilayani',
						value: 'Sudah Dilayani'
					}],
					valueField:'value',
					textField:'label',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'reg_status');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'reg_status',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			},{
				field:'reg_tipe_jkn', //filter status
				type:'combobox',
				options:{
					data: [{
						label: 'Semua',
						value: ''
					},{
						label: 'PBI',
						value: 'PBI'
					},{
						label: 'NON PBI',
						value: 'NON PBI'
					}],
					valueField:'value',
					textField:'label',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'reg_tipe_jkn');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'reg_tipe_jkn',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			},{
				field:'poli_nama', //filter poli
				type:'combobox',
				options:{
					url: 'get_klinik.php',
					valueField:'poli_nama',
					textField:'poli_nama',
					panelHeight: 'auto',
					onChange:function(value){
						if (value == ''){
							dg.datagrid('removeFilterRule', 'poli_nama');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'poli_nama',
								op: 'equal',
								value: value
							});
						}
						dg.datagrid('doFilter');
					}
				}
			}]);
		});
	</script>
	
	<!-- /////////////////// -->
	<body class="nav-md">
		<div class="container body">
			<div class="main_container">
				<?php require_once($LAY."sidebar.php") ?>

				<!-- top navigation -->
				<?php require_once($LAY."topnav.php") ?>
				<!-- /top navigation -->

				<!-- page content -->
				<div class="right_col" role="main">
					<div class="">
						<div class="page-title">
							<div class="title_left">
								<h3><?php echo $tableHeader; ?></h3>
							</div>
						</div>
						<?php //echo "$addpasien"; ?>
						<div class="clearfix"></div>
						
						<!-- insert irj Data Pasien -->
						<form id="form_irj" action="proses_update.php" method="post">
							<input id="cust_usr_id" type="hidden" name="cust_usr_id">
							<input id="regId" type="hidden" name="regId">
							<input id="reg_status_pasien" value="L" type="hidden" name="reg_status_pasien">
							<div class="row">             
								<!-- KOLOM KIRI -->
								<div class="col-md-6 col-sm-6 col-xs-12">
									<!-- == Hasil dari combobox => set ke element berdasar id == -->
									<div class="x_panel">
										<div class="x_title">
											<h2>Data Registrasi</h2>
											<div class="clearfix"></div>
										</div>
										<div class="x_content">
											<table class="col-md-12 col-sm-12 col-md-12">
												<tr>
													<th width="150px">No. RM</th>
													<td width="15px">:  </td>
													<td><input id="norm" class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_kode" readonly></td>
												</tr>
												<tr>
													<th>Nama Pasien</th>
													<td>:  </td>
													<td><input id="nmps"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_nama" readonly></td>
												</tr>
												<tr>
													<th>Alamat</th>
													<td>:  </td>
													<td><input id="alps"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_alamat" readonly></td>	
												</tr>
												<tr>
													<th>Poli Klinik</th>
													<td>:  </td>
													<td><input id="poli"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_alamat" readonly></td>	
												</tr>
												<tr>
													<th>Tempat Lahir</th>
													<td>:  </td>
													<td><input id="cust_usr_tempat_lahir"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_tempat_lahir" readonly></td>	
												</tr>
												<tr>
													<th>Tanggal Lahir</th>
													<td>:  </td>
													<td><input id="cust_usr_tanggal_lahir"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_tanggal_lahir" readonly></td>	
												</tr>
												
												<tr>
													<th>Umur</th>
													<td>:  </td>
													<td>
														<!-- <div class="col-md-12 col-sm-12 col-xs-12"> -->
															<input id="tahun" size="3" name="tahun" readonly><?php echo $tahun ?> tahun
															<input name="bulan" id="bulan" size="3" maxlength="3" readonly /> bulan
															<input name="hari" id="hari" size="3" maxlength="3" readonly /> hari
														<!-- </div> -->
													</td>	
												</tr>
												<tr>
													<th>Jenis Kelamin</th>
													<td>:  </td>
													<td>
														<input id="cust_usr_jenis_kelamin"  class="no-border col-md-12 col-sm-12 col-md-12" name="cust_usr_jenis_kelamin" readonly>
													</td>	
												</tr>
												<tr>
													<th>Agama</th>
													<td>:  </td>
													<td>
														<select class="form-control" name="cust_usr_agama" id="cust_usr_agama" onKeyDown="return tabOnEnter(this, event);">
															<option value="">[ Pilih Agama ]</option>
															<?php for ($i = 0, $n = count($dataAgama); $i < $n; $i++) { ?>
																<option value="<?php echo $dataAgama[$i]["agm_id"]; ?>" <?php if ($row["cust_usr_agama"] == $dataAgama[$i]["agm_id"]) echo "selected" ?>><?php echo $dataAgama[$i]["agm_nama"]; ?>
															</option>
														<?php } ?>
													</select>
												</td>	
											</tr>


											<tr>
												<th>Pendidikan</th>
												<td>:  </td>
												<td>
													<select class="form-control" name="id_pendidikan" id="id_pendidikan" onKeyDown="return tabOnEnter(this, event);">
														<option value="">[ Pilih Pendidikan ]</option>
														<?php for ($i = 0, $n = count($dataPendidikan); $i < $n; $i++) { ?>
															<option value="<?php echo $dataPendidikan[$i]["pendidikan_id"]; ?>" <?php if ($row["id_pendidikan"] == $dataPendidikan[$i]["pendidikan_id"]) echo "selected" ?>><?php echo $dataPendidikan[$i]["pendidikan_nama"]; ?>
														</option>
													<?php } ?>
												</select>
											</td>	
										</tr>

										<tr>
											<th>Alergi Obat</th>
											<td>:  </td>
											<td>
												<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
													<input readonly type="text" name="alergi" id="alergi" class="form-control" >
												<!-- </div> -->
											</div>
											</td>
										<!-- <td>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-12">Gol. Darah</label>
												<div class="col-md-8 col-sm-8 col-xs-12">
													<select class="form-control" name="cust_usr_gol_darah" id="cust_usr_gol_darah">
														<option <?php if ($row['cust_usr_gol_darah'] = '-') {
															echo "selected";
														} ?> value="-">Tidak Tahu</option>
														<option <?php if ($row['cust_usr_gol_darah'] = 'A') {
															echo "selected";
														} ?> value="A">A</option>
														<option <?php if ($row['cust_usr_gol_darah'] = 'AB') {
															echo "selected";
														} ?> value="AB">AB</option>
														<option <?php if ($row['cust_usr_gol_darah'] = 'B') {
															echo "selected";
														} ?> value="B">B</option>
														<option <?php if ($row['cust_usr_gol_darah'] = 'O') {
															echo "selected";
														} ?> value="O">O</option>
													</select>
													
												</div>
											</td>	 -->
										</tr>
					  <!-- <tr>
						<th>Alamat</th>
						<td>:  </td>
						<td><textarea id="cust_usr_alamat" name="cust_usr_alamat" readonly> <?php echo htmlspecialchars($row["cust_usr_alamat"]); ?></textarea ></td>	
					</tr> -->
					  <!-- <tr>
						<th>Nama Dusun/RT/RW </th>
						<td>:  </td>
						<td><input  id="id_kelurahan" readonly name="id_kelurahan" data-validate-length-range="5,20" class="optional form-control col-md-7 col-xs-12"></td>	
					  </tr>

					  <tr>
						<th>Kecamatan</th>
						<td>:  </td>
						<td><input id="id_kecamatan" readonly name="id_kecamatan" value="<?php echo $row["id_kecamatan"]; ?> " data-validate-length-range="5,20" class="optional form-control col-md-7 col-xs-12"></td>	
					  </tr>

					  <tr>
						<th>Kota</th>
						<td>:  </td>
						<td>
						<div class="col-md-8 col-sm-8 col-xs-12">
                        <select  name="id_kota" readonly id="id_kota" onchange="pilih_kecamatan(this.value)"></select>
                      </div>
					  </td>	
					  </tr>

					  <tr>
						<th>Provinsi</th>
						<td>:  </td>
						<td> 
						<div class="col-md-8 col-sm-8 col-xs-12">
							<select class="form-control" name="id_prop" id="id_prop" readonly onchange="pilih_kota(this.value)">
							<?php for ($i = 0, $n = count($dataProvinsi); $i < $n; $i++) { ?>
								<option value="<?php echo $dataProvinsi[$i]['lokasi_propinsi']; ?>" <?php if ($dataProvinsi[$i]["lokasi_propinsi"] == $row["id_prop"]) {
																									echo "selected";
																									} elseif ($dataProvinsi[$i]["lokasi_propinsi"] == $konf['dep_kode_prop']) echo "selected"; ?>><?php echo $dataProvinsi[$i]['lokasi_nama']; ?>
								</option>';
							<? } ?>
							<option value="0">Tidak Tahu</option>
							</select>
                      </div>
					  </td>	
					</tr> -->
					<tr>
						<th>No HP</th>
						<td>:  </td>
						<td><input id="cust_usr_no_hp" name="cust_usr_no_hp"  maxlength="13" required="required" data-validate-length-range="10,13" class="form-control col-md-7 col-xs-12" readonly></td>	
						&nbsp;																			
					</tr>

					<tr>
						<th>No KTP / Identitas</th>
						<td>:  </td>
						<td>
							<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
								<input  readonly name="cust_usr_no_identitas" id="cust_usr_no_identitas" maxlength="65" value="<?php echo $row["cust_usr_no_identitas"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);"  class="form-control" /></font>
							<!-- </div> -->
						</td>	
						<!-- <td>
							<div class="col-md-8 col-sm-8 col-xs-12">
								Jenis :
								<select name="id_card" class="form-control" readonly onKeyDown="return tabOnEnter(this, event);">
									<option value="KTP" <?php if ($row["id_card"] == "KTP") echo "selected"; ?>>KTP</option>
									<option value="SIM" <?php if ($row["id_card"] == "SIM") echo "selected"; ?>>SIM</option>
									<option value="PASPOR" <?php if ($row["id_card"] == "PASPOR") echo "selected"; ?>>PASPOR</option>
								</select>
							</div>

						</td> -->
						
						
					</tr>

					

					
					<tr>
						<th>Pekerjaan</th>
						<td>:  </td>
						<td>
							<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
								<select class="form-control" name="id_pekerjaan" readonly id="id_pekerjaan" onKeyDown="return tabOnEnter(this, event);">
									<option value="">Pilih Pekerjaan</option>
									<?php for ($i = 0, $n = count($dataPekerjaan); $i < $n; $i++) { ?>
										<option value="<?php echo $dataPekerjaan[$i]["pekerjaan_id"]; ?>" <?php if ($dataPekerjaan[$i]["pekerjaan_id"] == $row["id_pekerjaan"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataPekerjaan[$i]["pekerjaan_nama"]; ?></option>
									<?php } ?>
								</select>
							<!-- </div> -->
						</td>	
					</tr>

					<tr>
						<th>Kebangsaan</th>
						<td>:  </td>
						<td>
							<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
								<select class="form-control" readonly name="cust_usr_asal_negara" id="cust_usr_asal_negara" onKeyDown="return tabOnEnter(this, event);">
									<option value="">Pilih Kebangsaan</option>
									<?php for ($i = 0, $n = count($dataNegara); $i < $n; $i++) { ?>
										<option value="<?php echo $dataNegara[$i]["negara_id"]; ?>" <?php if ($dataNegara[$i]["negara_id"] == $row["cust_usr_asal_negara"]) echo "selected"; ?>><?php echo $dataNegara[$i]["negara_nama"] . " ( " . $dataNegara[$i]["negara_kode"] . " ) "; ?></option>
									<?php } ?>
								</select>
							<!-- </div> -->
						</td>	
					</tr>

					<tr>
						<th>Status Pernikahan</th>
						<td>:  </td>
						<td>
							<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
								<select class="form-control" readonly name="id_status_perkawinan" id="id_status_perkawinan" onKeyDown="return tabOnEnter(this, event);">
									<option value="">Pilih Status Perkawinan</option>
									<?php for ($i = 0, $n = count($dataStatus); $i < $n; $i++) { ?>
										<option value="<?php echo $dataStatus[$i]["status_perkawinan_id"]; ?>" <?php if ($dataStatus[$i]["status_perkawinan_id"] == $row["id_status_perkawinan"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataStatus[$i]["status_perkawinan_nama"]; ?></option>
									<?php } ?>
								</select>
							<!-- </div> -->
						</td>	
					</tr>

					<tr>
						<th>Nama Penanggung Jawab</th>
						<td>:  </td>
						<td>
							<!-- <div class="col-md-8 col-sm-8 col-xs-12"> -->
								<input  class="form-control" readonly name="cust_usr_penanggung_jawab" id="cust_usr_penanggung_jawab" size="30" maxlength="65" value="<?php echo $row["cust_usr_penanggung_jawab"]; ?>" onKeyDown="return tabOnEnter_select_with_button(this, event);" /></font>
							<!-- </div></td> -->
							<!-- <td>	<div class="col-md-8 col-sm-8 col-xs-12">
								Status :
								<select disabled class="form-control" name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">
									<option value="">- Pilih Hubungan -</option>
									<?php for ($i = 0, $n = count($dataStatusPJ); $i < $n; $i++) { ?>
										<option value="<?php echo $dataStatusPJ[$i]["status_pj_id"]; ?>" <?php if ($dataStatusPJ[$i]["status_pj_id"] == $row["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataStatusPJ[$i]["status_pj_nama"]; ?></option>
									<?php } ?>
								</select>
							</div>
						</td>	 -->
					</tr>



					<tr>
						<th>Berat Lahir</th>
						<td>:  </td>
						<td>
							<input id="cust_berat_lahir" readonly name="cust_berat_lahir" value="<?php echo $_POST["cust_berat_lahir"]; ?>" maxlength="13" data-validate-length-range="1,13" class="form-control col-md-7 col-xs-12" readonly>
						</td>	
					</tr>

					<tr>
						<th>Gol. Darah</th>
						<td>:  </td>
						<td>
							<!-- <div class="item form-group">
							<label class="control-label col-md-3 col-sm-3 col-xs-12">Gol. Darah</label>
							<div class="col-md-8 col-sm-8 col-xs-12"> -->
								<select class="form-control" name="cust_usr_gol_darah" id="cust_usr_gol_darah">
									<option <?php if ($row['cust_usr_gol_darah'] = '-') {
										echo "selected";
									} ?> value="-">Tidak Tahu</option>
									<option <?php if ($row['cust_usr_gol_darah'] = 'A') {
										echo "selected";
									} ?> value="A">A</option>
									<option <?php if ($row['cust_usr_gol_darah'] = 'AB') {
										echo "selected";
									} ?> value="AB">AB</option>
									<option <?php if ($row['cust_usr_gol_darah'] = 'B') {
										echo "selected";
									} ?> value="B">B</option>
									<option <?php if ($row['cust_usr_gol_darah'] = 'O') {
										echo "selected";
									} ?> value="O">O</option>
								</select>								
							<!-- </div> -->
						</td>
					</tr>

					<tr>
						<th>Jenis</th>
						<td>:  </td>
						<td>
							<select name="id_card" class="form-control" readonly onKeyDown="return tabOnEnter(this, event);">
								<option value="KTP" <?php if ($row["id_card"] == "KTP") echo "selected"; ?>>KTP</option>
								<option value="SIM" <?php if ($row["id_card"] == "SIM") echo "selected"; ?>>SIM</option>
								<option value="PASPOR" <?php if ($row["id_card"] == "PASPOR") echo "selected"; ?>>PASPOR</option>
							</select>
						</td>
					</tr>

					<tr>
						<th>Status</th>
						<td>:  </td>
						<td>
							<select disabled class="form-control" name="cust_usr_penanggung_jawab_status" id="cust_usr_penanggung_jawab_status" onKeyDown="return tabOnEnter(this, event);">
								<option value="">- Pilih Hubungan -</option>
									<?php for ($i = 0, $n = count($dataStatusPJ); $i < $n; $i++) { ?>
										<option value="<?php echo $dataStatusPJ[$i]["status_pj_id"]; ?>" <?php if ($dataStatusPJ[$i]["status_pj_id"] == $row["cust_usr_penanggung_jawab_status"]) echo "selected"; ?>><?php echo ($i + 1) . ". " . $dataStatusPJ[$i]["status_pj_nama"]; ?></option>
									<?php } ?>
							</select>
						</td>
					</tr>
					
				</table>
			</div>
		</div>
	</div>
	<!-- KOLOM KANAN -->
	<div class="col-md-6 col-sm-6 col-xs-12">
		<div class="x_panel">
			<div class="x_content" >
				
				<div class="col-md-6 col-sm-12 col-xs-12" >
					<label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tipe Rawat</label>
					<select id="instalasi" class="select2_single form-control" name="instalasi" onChange="getKlinik($(this).val());">
						<!--  <option value="">- Pilih instalasi -</option> -->
						<?php for ($i=0; $i < count($dataTipe); $i++) { ?>
							<option value="<?php echo $dataTipe[$i]['poli_tipe_id'] ?>" 
								<?php if ($reg['reg_tipe_rawat'] == $dataTipe[$i]['poli_tipe_id']) { echo "selected"; } elseif ($dataTipe[$i]['poli_tipe_id'] == 'J') { echo "selected"; } ?>
								><?php echo $dataTipe[$i]['poli_tipe_nama'] ?></option>
							<?php } ?>
						</select>
						<input type="hidden" name="instalasi" id="instalasi">
					</div>
					
					<div id="div_klinik_asal" class="col-md-6 col-sm-12 col-xs-12">
						<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Poli Klinik </label>
						
						<!-- <select id="klinik_asal" class="select2_single form-control" name="klinik_asal" onchange="funChangeDokter(this.value)">
							<option value="">- Pilih -</option>
							<?php //for($i=0,$n=count($polike2);$i<$n;$i++) {?>
								<option value="<?php //echo $polike2[$i]["poli_id"];?>"><?php echo $polike2[$i]["poli_nama"];?></option>
							<?php //} ?>
						</select> -->

						<select id="klinik_asal" class="select2_single form-control" name="klinik_asal" onchange="changeDokter($(this).val());">
							<option value="">- Pilih -</option>
							<?php for($i=0,$n=count($polike2);$i<$n;$i++) {?>
								<option value="<?php echo $polike2[$i]["poli_id"];?>"<?php if ($_POST['poli_id'] == $polike2[$i]['poli_id']) { echo "selected"; } ?>><?php echo $polike2[$i]["poli_nama"];?></option>
							<?php }?>
						</select>
						<input type="hidden" name="klinik_asal" id="klinik_asal">
					</div>  

					<div class="col-md-6 col-sm-6 col-xs-12"> 
						<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Bayar</label>
						<select id="reg_jenis_pasien" class="select2_single form-control" name="reg_jenis_pasien"  onChange="cekCaraBayar($(this).val())">
							<!--<option value="">- Pilih Cara Bayar -</option>-->
							<?php 
							for($i=0,$n=count($dataJPasien);$i<$n;$i++){
								?>
								<option value="<?php echo $dataJPasien[$i]["jenis_id"];?>"
									<?php if ($dataJPasien[$i]["jenis_id"]=='2') echo "selected" ?>>
									<?php echo $dataJPasien[$i]["jenis_nama"];?>
								</option>
							<?php } ?>
						</select>
					</div>
					
					<div id="div_jkn" class="col-md-6 col-sm-6 col-xs-12" style="display:none;">
						<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Tipe JKN</label>
						<select id="tipe_jkn" class="select2_single form-control" name="tipe_jkn" disabled>
							<!--<option value="">- Pilih Cara Bayar -</option>-->
							<?php 
							for($i=0,$n=count($dataJKN);$i<$n;$i++){
								?>
								<option value="<?php echo $dataJKN[$i]["jkn_id"];?>">
									<?php echo $dataJKN[$i]["jkn_nama"];?>
								</option>
							<?php } ?>
						</select>
					</div>

					<div id="div_iks" class="col-md-6 col-sm-6 col-xs-12" style="display: none;">
						<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Perusahaan</label>
						<select id="tipe_iks" class="select2_single form-control" name="perusahaan" disabled="">
							<?php 
							for($i=0,$n=count($dataIKS);$i<$n;$i++){
								?>
								<option value="<?php echo $dataIKS[$i]["perusahaan_id"];?>">
									<?php echo $dataIKS[$i]["perusahaan_nama"];?>
								</option>
							<?php } ?>
						</select>
					</div>
					
					<div class="col-md-6 col-sm-12 col-xs-12" >
						<label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Sebab Sakit</label>
						<select id="reg_sebab_sakit" class="select2_single form-control" name="reg_sebab_sakit" >
							<!--  <option value="">- Pilih Sebab Sakit -</option> -->
							<?php for($i=0,$n=count($dataSebabSakit);$i<$n;$i++){ ?>
								<option value="<?php echo $dataSebabSakit[$i]["sebab_sakit_id"];?>">
									<?php echo $dataSebabSakit[$i]["sebab_sakit_nama"];?> 
								</option>
							<?php } ?>
						</select>
						<input type="hidden" name="reg_sebab_sakit" id="reg_sebab_sakit">
					</div>
                    <!--
                    <div id="div_reg_tanggal" class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label pull-left col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Tanggal Registrasi</label>
                        <input type="text" id="reg_tanggal" name="reg_tanggal" class="form-control" value="<?php echo date('d-m-Y'); ?>" data-inputmask="'mask': '99-99-9999'">
                    </div> -->
                    
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                    	<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Prosedur Masuk</label>
                    	<select id="reg_prosedur_masuk" class="select2_single form-control" name="reg_prosedur_masuk" required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')" onChange="getCaraKunjungan($(this).val())">
                    		<option value="">- Pilih Prosedur Masuk -</option>
                    		<?php 
                    		for($i=0,$n=count($dataProsedurMasuk);$i<$n;$i++){
                    			?>
                    			<option value="<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_id"];?>">
                    				<?php echo $dataProsedurMasuk[$i]["prosedur_masuk_nama"];?>   
                    			</option>
                    		<?php } ?>
                    	</select>
                    	<input type="hidden" name="reg_prosedur_masuk" id="reg_prosedur_masuk">
                    </div>
                    
                    <div class="col-md-6 col-sm-12 col-xs-12">
                    	<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Cara Kunjungan</label>
                    	<select id="reg_rujukan_id" class="select2_single form-control" name="reg_rujukan_id"  onChange="getCaraKunjunganDet($(this).val())">
                    		<option value="">- Pilih Cara Kunjungan -</option>
                    		<?php 
                    		for($i=0,$n=count($dataCaraKunjungan);$i<$n;$i++){
                    			?>
                    			<option value="<?php echo $dataCaraKunjungan[$i]["rujukan_id"];?>">
                    				<?php echo $dataCaraKunjungan[$i]["rujukan_nama"];?>   
                    			</option>
                    		<?php } ?>
                    	</select>
                    	<input type="hidden" name="reg_rujukan_id" id="reg_rujukan_id">
                    </div>

                    <div id="rujukan_det" class="col-md-6 col-sm-12 col-xs-12">
                    	<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Detail Kunjungan</label>
                    	<select id="reg_rujukan_det" class="select2_single form-control" name="reg_rujukan_det" >
                    		<option value="">- Pilih Detail Kunjungan -</option>
                    	</select>
                    </div>
                    
                    <div class="col-md-6 col-sm-6 col-xs-12">
                    	<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Diagnosa Awal</label>
                    	<input name ="diagnosa" type="text" class="form-control" id="diagnosa" placeholder="" value="">
                    	<input name ="reg_diagnosa_awal" type="hidden" class="form-control" id="reg_diagnosa_awal" value="">
                    </div>

                    <div class="col-md-6 col-sm-6 col-xs-12">
                    	<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;" >Dokter</label>
                    	<select id="ubah_dokter" name="dokterr" class="form-control select2_single">
                    		<option value="">Pilih Dokter</option>
                    		<?php 
							for ($i=0; $i<count($dataDokter); $i++) { ?>
                    			<option value="<?php echo $dataDokter[$i]['usr_id'];?>"<?php if ($_POST['dokter'] == $dataDokter[$i]['usr_id']) { echo "selected"; } ?>><?php echo $dataDokter[$i]['usr_name']; ?></option>
                    		<?php } ?>
                    	</select>
                    	<input type="hidden" name="dokterr" id="ubah_dokter">
                    </div>

                   


                    <!-- <div class="col-md-6 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align:left;">Klinik</label>
                        <select id="klink" class="select2_single form-control" name="klinik" required oninvalid="this.setCustomValidity('Silahkan Pilih Salah Satu')" oninput="setCustomValidity('')" onChange="getPoli($(this).val())">
                        <option value="">- Pilih Klinik -</option>
                            <?php 
                            for($i=0,$n=count($dataPoli);$i<$n;$i++){
                                ?>
                            <option value="<?php echo $dataPoli[$i]["poli_id"];?>">
                                <?php echo $dataPoli[$i]["poli_nama"];?>   
                            </option>
                        <?php } ?>
                        </select>
                    </div> -->
                </div>
            </div>
            <input class="btn btn-success" name="btn_value" id="btn_value" value="Tambah" type="hidden">
            <input name="btn" id="btn" class="btn btn-primary col-md-3 pull-right" type="submit" value="Simpan">
            <input id="btnReset" class="btn btn-danger pull-right" style="display:none;" value="Batal" onclick="window.location.reload()">
        </div>
    </div>
</form>

<!-- Data View Pasien -->
<div class="row">             
	<div class="col-md-12 col-sm-12 col-xs-12">
		
		<table id="dg" title="Data Kunjungan Pasien <?php echo $tglSekarang;?>" style="width:100%;height:400px"
			toolbar="#toolbar"
			data-options=" url:'get_irj.php', pagination:false,
			rownumbers:true, fitColumns:true, singleSelect:true,
			onDblClickRow:function(){
			editUser();
		}">
		<thead>
			<tr>
				<!-- TABEL DATA => field samakan field tabel database -->
				<th field="reg_id" hidden >Reg ID</th>
				<th field="reg_tanggal" width="50">Tanggal</th>
				<th field="reg_waktu" width="50">Waktu</th>
				<th field="reg_kode_trans" width="50">No. Registrasi</th>
				<th field="cust_usr_kode_tampilan" width="50">No. RM</th>
				<th field="cust_usr_nama" width="50">Nama Pasien</th>
				<th field="cust_usr_alamat" width="100">Alamat</th>
				<th field="cust_usr_tanggal_lahir" width="50">Tanggal Lahir</th>
				<th field="pendidikan_nama" width="50">Pendidikan</th>
				<th data-options="field:'jenis_nama',width:50,
				formatter:function(value,row){
				if(row.jkn_nama != null){ a = row.jenis_nama+' '+row.jkn_nama }
				else if(row.perusahaan_nama != null){ a = row.jenis_nama+' '+row.perusahaan_nama }
				else { a = row.jenis_nama };
				return a;
			}
			">Cara Bayar</th>
			<th field="poli_nama" width="100">Poli</th>
			<th hidden data-options="field:'reg_status',width:50,
			formatter:function(value,row){
			var E0 = 'Belum Dilayani';
			var E1 = 'Sampai di Poli';
			var E2 = 'Sudah Dilayani';
			if (row.reg_status == 'E0') { return E0; }
			if (row.reg_status == 'E1') { return E1; }
			if (row.reg_status == 'E2') { return E2; }
		}
		">Status</th>
		<th data-options="field:'poli_asal',width:50,
		formatter:function(value,row){
		if(row.poli_asal != row.poli_nama){ a = row.poli_asal }
		else { a = 'Poli Pertama' };
		return a;
	}
	">Poli Asal</th>
	<th field="dokter" width="50">Dokter</th>
</tr>
</thead>
</table>
<div id="toolbar">
	<div id = "tb" style = "padding: 5px; height: auto">
                        <!--
							<div style = "margin-bottom: 5px">
								Rentang tanggal: <input id="tgl_awal" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
								Ke: <input id="tgl_akhir" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
								<a href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="cari()"> Cari </a>
							</div>    -->
							<div>
								<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Batal Registrasi</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg').datagrid('reload');">Refresh</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak()">Cetak Reg</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakb()">Cetak Barcode</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakbb()">Cetak Gelang Tangan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakkartu()">Cetak Kartu</a>            
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakringkasan()">Cetak Ringkasan</a>
								<!-- <a href="edit_jenis_pasien.php" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="">Edit Jenis Pasien</a> -->
							</div>
						</div>
					</div>	
					
					
				</div>
			</div>
			
		</div>
	</div>
	<!-- /page content -->

	<!-- footer content -->
	<?php require_once($LAY."footer.php") ?>
	<!-- /footer content -->
</div>
</div>
<!-- jQuery -->
<?php require_once($LAY."js.php") ?>

<script type="text/javascript">
	function myformatter(date){
		var y = date.getFullYear();
		var m = date.getMonth()+1;
		var d = date.getDate();
		return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
	}
	function myparser(s){
		if (!s) return new Date();
		var ss = (s.split('-'));
		var y = parseInt(ss[0],10);
		var m = parseInt(ss[1],10);
		var d = parseInt(ss[2],10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
			return new Date(d,m-1,y);
		} else {
			return new Date();
		}
	}
	
	function cari(){			
		$('#dg').edatagrid('load',{
			tgl_awal: $('#tgl_awal').val(),
			tgl_akhir: $('#tgl_akhir').val()
		});
	}
</script>
<script type="text/javascript">
	//fungsi edit => Ambil data dari tabel berdasar PK => lempar data berdasar elemen id

	$("select#instalasi").change(function(){
		var val_id = $(this).val();
		$("input#instalasi").val(val_id);
	});
	$("select#reg_prosedur_masuk").change(function(){
		var val_id = $(this).val();
		$("input#reg_prosedur_masuk").val(val_id);
	});
	$("select#reg_rujukan_det").change(function(){
		var val_id = $(this).val();
		$("input#reg_rujukan_det").val(val_id);
	});
	$("select#ubah_dokter").change(function(){
		var val_id = $(this).val();
		$("input#ubah_dokter").val(val_id);
	});
	$("select#klinik_asal").change(function(){
		var val_id = $(this).val();
		$("input#klinik_asal").val(val_id);
	});
	$("select#reg_sebab_sakit").change(function(){
		var val_id = $(this).val();
		$("input#reg_sebab_sakit").val(val_id);
	});
	$("select#reg_rujukan_id").change(function(){
		var val_id = $(this).val();
		$("input#reg_rujukan_id").val(val_id);
	});


	function editUser(){
		var row = $('#dg').datagrid('getSelected');
		if (row){
			$.get('get_irj.php',{reg_id:row.reg_id},function(r){
				$('#norm').val(r[0].cust_usr_kode);
				$('#nmps').val(r[0].cust_usr_nama);
				$('#cust_usr_tempat_lahir').val(r[0].cust_usr_tempat_lahir);
				var reg_tanggal = formatDate(r[0].cust_usr_tanggal_lahir);
				$('#cust_usr_tanggal_lahir').val(reg_tanggal);
				
				if(row.poli_id){
					$.ajax({
						type:'POST',
						url:'RS_Data.php',
						data:'poli_id='+row.poli_id+'&id_dokter='+row.id_dokter,
						success:function(html){
							$('#ubah_dokter').html(html);
						}
					});
				}else{
					$('#ubah_dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
				}
				console.log(a);
				$('#alps').val(r[0].cust_usr_alamat);
				$('#poli').val(r[0].poli_nama);
				$('#instalasi').val(r[0].reg_tipe_rawat);
				$('input#instalasi').val(r[0].reg_tipe_rawat);
				$('#regId').val(r[0].reg_id);
				$('#cust_usr_id').val(r[0].id_cust_usr);
				$('#klinik_asal').val(r[0].id_poli);
				$('input#klinik_asal').val(r[0].id_poli);
				$('#dokter').val(r[0].id_dokter);
				$('#ubah_dokter').val(r[0].id_dokter);
				$('input#ubah_dokter').val(r[0].id_dokter);
				$('#reg_diagnosa_awal').val(r[0].reg_diagnosa_awal);
				$('#diagnosa').val(r[0].reg_diagnosa_awal);
				$('#tahun').val(r[0].reg_umur);
				$('#hari').val(r[0].reg_umur_hari);
				$('#bulan').val(r[0].reg_umur_bulan);
				$('#cust_usr_jenis_kelamin').val(r[0].cust_usr_jenis_kelamin);
				$('#cust_usr_agama').val(r[0].cust_usr_agama);
				$('#cust_usr_alergi').val(r[0].cust_usr_alergi);
				$('#cust_usr_gol_darah_resus').val(r[0].cust_usr_gol_darah_resus);
				$('#id_kelurahan').val(r[0].id_kelurahan);
				$('#id_kecamatan').val(r[0].id_kecamatan);
				$('#id_kota').val(r[0].id_kota);
				$('#id_prop').val(r[0].id_prop);
				$('#cust_usr_no_hp').val(r[0].cust_usr_no_hp);
				$('#cust_usr_no_identitas').val(r[0].cust_usr_no_identitas);
				$('#id_card').val(r[0].id_card);
				$('#cust_usr_pekerjaan').val(r[0].cust_usr_pekerjaan);
				$('#cust_usr_asal_negara').val(r[0].cust_usr_asal_negara);
				$('#id_status_perkawinan').val(r[0].id_status_perkawinan);
				$('#cust_usr_penanggung_jawab').val(r[0].cust_usr_penanggung_jawab);
				$('#cust_usr_penanggung_jawab_status').val(r[0].cust_usr_penanggung_jawab_status);
				$('#cust_usr_berat_lahir').val(r[0].cust_usr_berat_lahir);
				$('#cust_usr_alamat').val(r[0].cust_usr_alamat);
				$('#pendidikan_nama').val(r[0].pendidikan_nama);
				$('#id_pendidikan').val(r[0].id_pendidikan);

				var reg_tanggal = formatDate(r[0].reg_tanggal);
				$('#reg_tanggal').val(reg_tanggal);
					//getPoliAsal(r[0].id_cust_usr);
					getKlinik(r[0].reg_tipe_rawat);
					getPoli(r[0].poli_id);
					getCaraKunjungan(r[0].reg_prosedur_masuk);
					getCaraKunjunganDet(r[0].reg_rujukan_id);
					cekCaraBayar(r[0].reg_jenis_pasien);
					setTimeout(function() {  
						$('#reg_sebab_sakit').val(r[0].reg_sebab_sakit);
						$('input#reg_sebab_sakit').val(r[0].reg_sebab_sakit);
						$('#reg_prosedur_masuk').val(r[0].reg_prosedur_masuk);
						$('input#reg_prosedur_masuk').val(r[0].reg_prosedur_masuk);
						$('#reg_rujukan_id').val(r[0].reg_rujukan_id);
						$('input#reg_rujukan_id').val(r[0].reg_rujukan_id);
						$('#poli_id').val(r[0].poli_id);
						$('#reg_rujukan_det').val(r[0].reg_rujukan_det);
						$('input#reg_rujukan_det').val(r[0].reg_rujukan_det);
						$('#reg_jenis_pasien').val(r[0].reg_jenis_pasien);
						$('#tipe_jkn').val(r[0].reg_tipe_jkn);
						$('#tipe_iks').val(r[0].id_perusahaan);

						var stats = r[0].reg_status;
						stats = stats.substring(1);
						if(stats != '0'){
							$("select#instalasi").attr("disabled", "true");
							$("select#reg_prosedur_masuk").attr("disabled", "true");
							$("select#reg_rujukan_det").attr("disabled", "true");
							$("select#ubah_dokter").attr("disabled", "true");
							$("select#klinik_asal").attr("disabled", "true");
							$("select#reg_sebab_sakit").attr("disabled", "true");
							$("select#reg_rujukan_id").attr("disabled", "true");
						}
						else{
						// $("select#instalasi").removeAttr("disabled");
						// $("select#reg_prosedur_masuk").removeAttr("disabled");
						// $("select#reg_rujukan_det").removeAttr("disabled");	
						$("select#ubah_dokter").removeAttr("disabled");
						$("select#klinik_asal").removeAttr("disabled");
						// $("select#reg_sebab_sakit").removeAttr("disabled");
						// $("select#reg_rujukan_id").removeAttr("disabled");

						$("select#instalasi").attr("disabled", "true");
						$("select#reg_prosedur_masuk").attr("disabled", "true");
						$("select#reg_rujukan_det").attr("disabled", "true");
						// $("select#ubah_dokter").attr("disabled", "true");
						// $("select#klinik_asal").attr("disabled", "true");
						$("select#reg_sebab_sakit").attr("disabled", "true");
						$("select#reg_rujukan_id").attr("disabled", "true");
					}

				}, 2000);
					document.getElementById('btn').value = "Simpan";	//jika edit tombol ganti value
					document.getElementById('btn_value').value = "Simpan";	//jika edit tombol ganti value
					document.getElementById('btnReset').style.display = 'block';	//jika edit tombol reset muncul
				},'json');
}  

}

function formatDate(date) {
	var d = new Date(date),
	month = '' + (d.getMonth() + 1),
	day = '' + d.getDate(),
	year = d.getFullYear();

	if (month.length < 2) month = '0' + month;
	if (day.length < 2) day = '0' + day;
	return [day, month, year].join('-');
}

		//fungsi hapus => Ambil data dari tabel berdasar PK => query hapus data di file del_irj.php
		function destroyUser(){
			var row = $('#dg').datagrid('getSelected');
			if (row){

				$.messager.prompt('Anda yakin?', 'Alasan pembatalan registrasi:', function(r){
					if (r){
						//alert(r);
						$.post('del_irj.php',{reg_id:row.reg_id,alasan:r},function(result){
							if (result.success){
								// $.messager.show({	// 
								// 	title: 'Berhasil',
								// 	msg: "Pembatalan registrasi sukses"
								// });
								alert("Pembatalan Berhasil")
								$('#dg').datagrid('reload');	// reload the user data
							} else {
								$.messager.show({	// show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						},'json');
					}
					else{
						alert("Harap isi alasan pembatalan");

					}
				});
			}
		}
		//fungsi cetak registrasi
		function cetak(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_registrasi.php?reg_id='+row.reg_id;
				var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}
		//fungsi cetak barcode
		function cetakb(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_barcode.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}
		
		//fungsi cetak barcode besar
		function cetakbb(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_barcode_besar.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				printWindow.addEventListener('load', function(){
				//	printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak kartu
		function cetakkartu(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_kartu.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
				var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				printWindow.addEventListener('load', function(){
					printWindow.print();
					
				}, true);
			}
		}

    	//fungsi cetak ringkasan
    	function cetakringkasan(){
    		var row = $('#dg').datagrid('getSelected');
    		if (row){
    			var url = 'cetak_ringkasan.php?id_reg='+row.reg_id+'&id='+row.id_cust_usr;
    			var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
    			printWindow.addEventListener('load', function(){
				//printWindow.print();
				//	printWindow.close();
				}, true);
    		}
    	}

		function changeDokter(id){
			if(id){
				$.ajax({
					type:'POST',
					url:'RS_Data.php',
					data:'poli_id='+id,
					success:function(html){
						$('#ubah_dokter').html(html);
					}
				});
			}else{
				$('#ubah_dokter').html('<option value="">Pilih Klinik Dahulu</option>'); 
			}

		}

    	</script>

		
    	
    </body>
    </html>           