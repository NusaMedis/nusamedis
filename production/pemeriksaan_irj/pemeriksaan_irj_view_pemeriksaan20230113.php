<?php
##################	NOTE #####################
## insert cust usr kode sebagai primary	##
## update untuk tambah berdasar primary	##
#############################################

// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

//INISIALISASI LIBRARY
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();
$enc = new textEncrypt();
$depId = $auth->GetDepId();
$lokasi = $ROOT . "gambar/foto_pasien";
$depId = $auth->GetDepId();
$userId = $auth->GetUserId();
$tglSekarang = date("Y-m-d");

//AUTHENTIKASI
if (!$auth->IsAllowed("man_ganti_password", PRIV_READ)) {
	die("access_denied");
	exit(1);
} elseif ($auth->IsAllowed("man_ganti_password", PRIV_READ) === 1) {
	echo "<script>window.parent.document.location.href='" . $MASTER_APP . "login/login.php?msg=Session Expired'</script>";
	exit(1);
}


//INISIALISASI AWAL
$nextPage = "registrasi_pasien.php?usr_id=";


function count_digit($angka)
{
	return strlen((string) $angka);
}

$sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs from global.global_departemen where dep_id = '$depId'";
$konf = $dtaccess->Fetch($sql);
$norm_depan = $konf['dep_konf_reg_no_rm_depan'];

#tambah pasien baru
if ($_GET['reg_status_pasien'] == "B") {
	require_once("data_pasien_kode.php");
	$usr_kode = $_POST["kode_pasien"];


	$rm =substr( $_POST["kode_pasien"],0,2);
	// $arr = str_split($usr_kode, "2");
	$usr_kode_tampilan = $rm.".".substr( $_POST["kode_pasien"],2);

	$dbTable = "global.global_customer_user";
	$dbField[0] = "cust_usr_id";   // PK  
	if ($norm_depan == 'y') {
		$dbField[1] = "cust_usr_kode";
		$dbField[2] = "cust_usr_kode_tampilan";
	}

	$custUsrId = $dtaccess->GetTransID();
	$dbValue[0] = QuoteValue(DPE_CHAR, $custUsrId);
	if ($norm_depan == 'y') {
		$dbValue[1] = QuoteValue(DPE_CHAR, $usr_kode);
		$dbValue[2] = QuoteValue(DPE_CHAR, $usr_kode_tampilan);
	}
	$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
	$dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
	$dtmodel->Insert() or die("insert  error");

	// die();
	unset($dtmodel);
	unset($dbField);
	unset($dbValue);
	unset($dbKey);

	if ($_GET['bpjs'] == "0") {
		$statusJKN = "bpjs=true&noKartu=" . $_GET['noKartu'];
	} else {
		$statusJKN = "bpjs=false";
	};
	header("location:" . $nextPage . $custUsrId . "&status_pasien=B&" . $statusJKN);
	exit();
}
# cari pasien
if ($_POST["btn"]) {
	if ($_POST['find_tgl_lahir']) {
		$tga = Explode('-', $_POST['find_tgl_lahir']);
		$tgl = $tga[2] . "-" . $tga[1] . "-" . $tga[0];
	}

	# parameter query
	if ($_POST["find_pk"]) {
		$pk = strval($_POST['find_pk']);
		$count = count_digit($pk);

		if ($count < 16 && $count < 13 && $count <= 8 && $count > 0) {
			//echo 'NO RM';
			$sql_where[] = " cust_usr_kode =" . QuoteValue(DPE_CHAR, $pk);
		} elseif ($count < 16 && $count <= 13 && $count > 8) {
			//echo 'NO BPJS';
			$sql_where[] = " cust_usr_no_jaminan =" . QuoteValue(DPE_CHAR, $pk);
		} elseif ($count <= 16 && $count > 13 && $count > 8) {
			//echo "NIK";
			$sql_where[] = " (cust_usr_no_identitas =" . QuoteValue(DPE_CHAR, $pk) . " or cust_usr_nik =" . QuoteValue(DPE_CHAR, $pk) . ")";
		}
		elseif ($count <= 18 && $count > 16) {
			//echo "NIK";
			$sql_where[] = " cust_usr_id in( select id_cust_usr from klinik.klinik_registrasi_kontrol where reg_nomor_kontrol =" . QuoteValue(DPE_CHAR, $pk) . ")";
		}
	}
	$findPasien = str_replace("'", "*", $_POST["find_nama"]);
	if ($_POST["find_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($findPasien) . "%");
	if ($_POST["find_penanggung_jawab"])  $sql_where[] = "UPPER(cust_usr_penanggung_jawab) like " . QuoteValue(DPE_CHAR, strtoupper($_POST["find_penanggung_jawab"]) . "%");
	if ($_POST["find_alamat"])  $sql_where[] = "UPPER(cust_usr_alamat) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_POST["find_alamat"]) . "%");
	if ($_POST["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =" . QuoteValue(DPE_CHAR, $tgl);
	// $sql_where[] = "cust_usr_nama is not null";
	$sql_where[] = "cust_usr_kode <> '500'";
	if ($sql_where[0])  $sql_where = implode(" and ", $sql_where);
	#end parameter

	$sql = "select
    a.id_dep, a.reg_id, a.reg_tanggal, a.reg_when_update, a.reg_status_kondisi,a.id_pembayaran,a.id_cust_usr,a.reg_rujukan_id,
        a.reg_tingkat_kegawatan,a.id_poli,a.id_dokter,a.reg_jenis_pasien, rawat_id,
    b.cust_usr_kode,b.cust_usr_kode_tampilan,b.cust_usr_id,b.cust_usr_nama,b.cust_usr_foto,b.cust_usr_alamat,
    c.poli_nama, d.sebab_sakit_nama, e.shift_nama, f.jenis_nama,g.usr_name,g.usr_id,
    h.rawat_anamnesa,h.rawat_pemeriksaan_fisik,h.rawat_penunjang,h.rawat_kasus_keterangan,h.rawat_terapi
    ,h.rawat_keluhan,h.rawat_catatan,h.rawat_diagnosa_utama,h.rawat_ket,b.cust_usr_id,h.rawat_usg_ginekologi,h.rawat_pemeriksaan_dalam, c.form_asmed, h.rawat_anak, a.id_jenis_kb, h.rawat_terapi
    from
    klinik.klinik_registrasi a left join
    global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
    left join global.global_auth_poli c on a.id_poli = c.poli_id
    left join global.global_sebab_sakit d on d.sebab_sakit_id = a.reg_sebab_sakit
    left join global.global_shift e on e.shift_id = a.reg_shift
    left join global.global_jenis_pasien f on f.jenis_id = a.reg_jenis_pasien
    left join global.global_auth_user g on g.usr_id = a.id_dokter
    left join klinik.klinik_perawatan h on h.id_reg = a.reg_id
    where reg_id = '$_GET[reg_id]' and a.id_dep = '$depId'";
	$sql .= " and " . $sql_where;
	$sql .= " order by cust_usr_kode desc limit 50";
	echo $sql;
	$rs = $dtaccess->Execute($sql);
	$row = $dtaccess->FetchAll($rs);
	//echo count($row);
}
$tableHeader = "Registrasi Pasien";
?>

<!DOCTYPE html>
<html lang="en">

<?php require_once($LAY . "header.php") ?>
<!-- sweet alert -->
<script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.css">
<script>
	function pad(str, max) {
		str = str.toString();
		return str.length < max ? pad("0" + str, max) : str;
	}

	function cek_kepesertaan(param) {
		$.post("cek_kepesertaan.php", {
				param: param
			},
			function(data) {
				var status = data.metaData.code;
				//alert(status);
				if (status != '200' || param == '0000000000000') { //jika aktif
					window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B');
				} else {
					alert('Status pasien BPJS / ASKES ' + data.response.peserta.statusPeserta.keterangan);
					var nk = data.response.peserta.noKartu;
					window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B&bpjs=0&noKartu=' + nk);
				}
			}, "json");
	}

	$(document).ready(function() {
		$('#tata').dataTable({
			"iDisplayLength": 25,
			"order": [
				[0, 'desc']
			]
		});
	});
</script>
<?php #jika pencarian norm/nik/bpjs tidak ditemukan beri alert
if ($_POST["btn"] == 'btnAtas' && count($row) < 1) {
	//echo "<script>alert('Pasien Tidak Ditemukan. Registrasi sebagai pasien baru?');</script>";
	echo "
				<script type='text/javascript'>
				var pk = " . $pk . ";
				var param = pad(pk, 13);
				//alert(param);
				  setTimeout(function () {  
				   swal({
				   	background: '#FF0000E6',
					title: '<span style=\"color: #FFF\"> Pasien tidak ditemukan.</span>',
					html: '<span style=\"color: #FFF\">Registrasi sebagai pasien baru?</span>',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#337AB7',
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak',
					closeOnConfirm: false
					//closeOnCancel: false
				  }).then((result) => {
					  if (result.value) {
					    window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B');
						cek_kepesertaan(param);
					  }
					})
				  },10); 
				</script>
			";
} elseif ($_POST["btn"] == 'btnAtas' && count($row) >= 1) {
	//cek kepesertaanbpjs
	$aktif = 0;
	if ($aktif = 0) {
		echo "
				<script type='text/javascript'>
				  setTimeout(function () {  
				   swal({
					title: 'SEP Tidak dapat diterbitkan.',
					text: 'Registrasi sebagai pasien umum?',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#337AB7',
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak',
					closeOnConfirm: false,
					//closeOnCancel: false
				  },
				  function(){
					window.location.replace('registrasi_pasien.php?usr_id=" . $row[0]['cust_usr_id'] . "&status_pasien=L&bpjs=false');
				  });  
				  },10); 
				</script>
				";
		//header("location: registrasi_pasien.php?usr_id=".$row[0]['cust_usr_kode']."&status_pasien=L&bpjs=false");
	} //elseif ( $aktif = 1 ) { header("location: registrasi_pasien.php?usr_id=".$row[0]['cust_usr_kode']."&status_pasien=L&bpjs=trues"); }
};

if ($_GET["reg_sukses"]) {

	echo "
				<script type='text/javascript'>
				  setTimeout(function () {  
				   swal({
				   	background: 'rgb(0, 255, 25)',
					title: 'Registrasi Berhasil',
					text:  '',
					type: 'success',
					timer: 1200,
					showConfirmButton: true,
					confirmButtonColor: 'rgb(17, 206, 36)',
				   });  
				  },10); 
				</script>
			";
};
?>

<body class="nav-md" onload="$('#find_pk').focus()">
	<div class="container body">
		<div class="main_container">
			<?php require_once($LAY . "sidebar.php") ?>

			<!-- top navigation -->
			<?php require_once($LAY . "topnav.php") ?>
			<!-- /top navigation -->
			<!-- page content -->
			<div class="right_col" role="main">
				<div class="">
					<div class="page-title">
						<div class="title_left">
							<h3>Registrasi Pasien</h3>
						</div>

						<div class="title_right">

						</div>
					</div>
					<div class="clearfix"></div>

					<!-- Row BEGIN -->
<!-- 					<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<div class="x_panel">
								<div class="x_content">
									<form method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
										<div class="item form-group">
											<label class="control-label col-md-2 col-sm-2 col-xs-12">No RM / NIK / No BPJS / No Kontrol
											</label>
											<div class="col-md-8 col-sm-6 col-xs-6">
												<input id="find_pk" class="form-control" name="find_pk" value="<?php echo $_POST["find_pk"] ?>" placeholder=" Silahkan isi No RM / NIK / No BPJS" required="required">
											</div>
											<div class="col-md-1 col-sm-1 col-xs-1">
												<input type="hidden" name="btn" value="btnAtas">
												<button type="submit" class="form-control btn btn-primary"> Cari </button>

											</div>
											<div class="col-md-1 col-sm-1 col-xs-1">
												<a class="form-control btn btn-success" href="scan.php">Scan</a>
											</div>
										</div>
									</form>

									<form method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
										<div id="rm" class="item form-group">
											<label class="control-label col-md-2 col-sm-2 col-xs-12">
												Filter Cari Pasien
											</label>
											<div class="col-md-2 col-sm-2 col-xs-2">
												<input type="text" id="find_nama" class="form-control" name="find_nama" value="<?php echo $_POST['find_nama']; ?>" placeholder="Nama Pasien">
											</div>
											<div class="col-md-2 col-sm-2 col-xs-2">
												<input type="text" id="find_alamat" class="form-control" name="find_alamat" value="<?php echo $_POST['find_alamat']; ?>" placeholder="Alamat Pasien">
											</div>
                        </div-->
										<!-- 	<div class="col-md-2 col-sm-2 col-xs-2">
												<input type="text" class="form-control" id="find_tgl_lahir" name="find_tgl_lahir" data-inputmask="'mask': '99-99-9999'" value="<?php echo $_POST['find_tgl_lahir']; ?>" placeholder="Tanggal Lahir" />
											</div>
											<div class="col-md-2 col-sm-2 col-xs-2">
												<input type="text" id="find_penanggung_jawab" class="form-control" name="find_penanggung_jawab" value="<?php echo $_POST['find_penanggung_jawab']; ?>" placeholder="Nama Penanggung Jawab Pasien">
											</div>
											<div class="col-md-1 col-sm-2 col-xs-2">
												<input type="hidden" name="btn" value="btnBawah">
												<button type="submit" class="form-control btn btn-primary"> Cari </button>
											</div>
											<div class="col-md-1 col-sm-2 col-xs-2">
												<a href="registrasi_pasien_awal.php?reg_status_pasien=B" class="form-control btn btn-success">Baru</a>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div> --> 
				<!-- END ROW -->
				<div id="hasil-filter-ajax">
					<!-- Row BEGIN -->
				
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_panel">
								<!-- 	<div class="x_title">
										<h2>Hasil Pencarian Pasien Lama</h2>
										<div class="clearfix"></div>
									</div> -->
									<div class="x_content">
										<!-- <table id="tata" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%"> -->
						<table id="dg" title="Pasien Terdaftar <?php echo $tglSekarang; ?>" class="easyui-datagrid" class="col-md-12 col-sm-12 col-xs-12" style="width:100%;height:350px" toolbar="#toolbar" data-options=" url:'get_irj.php', pagination:false,
							rownumbers:true, fitColumns:true, singleSelect:true,
							onDblClickRow:function(){
							layani();
						},
						rowStyler: function(index,row){
						if (row.reg_status=='E2'){
						return 'background-color:#4CAF50; color:#fff;'; 
					}
				}

				">
				<thead>
					<tr>
						<!-- TABEL DATA => field samakan field tabel database -->
						<th field="id_cust_usr" hidden width="50">user ID</th>
						<th field="reg_id" hidden width="50">Reg ID</th>
						<th field="reg_tanggal">Tanggal</th>
						<th field="reg_waktu">Waktu</th>
						<th field="cust_usr_kode">No. RM</th>
						<th field="reg_kode_trans" width="50">No. Registrasi</th>
						<th field="cust_usr_nama" width="50">Nama Pasien</th>
						<th field="cust_usr_alamat" width="50">Alamat</th>
						<th field="cust_usr_tanggal_lahir" width="30">Tanggal Lahir</th>
						<th data-options="field:'jenis_nama',width:50,
						formatter:function(value,row){
						if(row.jkn_nama != null){ a = row.jenis_nama+' '+row.jkn_nama }
						else if(row.perusahaan_nama != null){ a = row.jenis_nama+' '+row.perusahaan_nama }
						else { a = row.jenis_nama };
						return a;
					}
					">Cara Bayar</th>
					<th data-options="field:'reg_status_pasien',width:50,
					formatter:function(value,row){
					if(row.reg_status_pasien == 'B'){ a = 'BARU' }
					else { a = 'LAMA' };
					return a;
				}
				">Baru/Lama</th>
				<th field="poli_nama" width="50">Poli</th>
				<th field="dokter" width="50">Dokter</th>
				<th data-options="field:'reg_status',
				formatter:function(value,row){
				var E0 = 'Belum Dilayani';
				var E1 = 'Sampai di Poli';
				var E2 = 'Sudah Dilayani';
				if (row.reg_status == 'E0') { return E0; }
				if (row.reg_status == 'E1') { return E1; }
				if (row.reg_status == 'E2') { return E2; }
			},
			">Status</th>
			<!-- styler:cellStyler -->
		</tr>
	</thead>
</table>
<div id="toolbar">
	<div id="tb" style="padding: 5px; height: auto">
								<!-- <div style = "margin-bottom: 5px">
							Rentang tanggal: <input id="tgl_awal" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
							Ke: <input id="tgl_akhir" class = "easyui-datebox" data-options="formatter:myformatter,parser:myparser" style = "width: 120px">
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-search" onclick="cari()"> Cari </a>
						</div> -->
						<div>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="sampai()">Sampai di Poli</a>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="layani()">Layani</a>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#dg').edatagrid('reload')">refresh</a>

							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak()">Cetak Reg</a>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakspb()">Cetak SPB</a>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_tagihan()">Cetak Tagihan</a>
							<a id="doNothing" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakb()">Cetak Barcode</a>
						</div>
					</div>

				</div>
									</div>
								</div>
							</div>
						</div>
				
					<!-- END ROW -->
				</div>

			</div>
		</div>
		<!-- /page content -->
		</form>




		<!-- footer content -->
		<?php require_once($LAY . "footer.php"); ?>


		<script type="text/javascript">
			$('#find_pk').change(function() {
				var a = $(this).val();
				var dap = pad(a, 8); // => "0000000a"
				//alert(dap);
				$('#find_pk').val(dap);
			});

			function regist(location, id) {
				$('#btn-regist').removeAttr('href');
				$('#btn-regist').attr('href', 'registrasi_pasien.php?usr_id='+id+'&status_pasien=L&waktu=<?php echo date('Y-m-d H:i:s') ?>');

				$.ajax({
					url: 'get_history.php?id_cust_usr=' + id,
					success: function(result) {
						$('#history').html(result);
					}
				})
			}
		</script>

		<!-- /footer content -->
	</div>
	</div>
	<!-- validator -->
	<script src="<?php echo $ROOT; ?>assets/vendors/validator/validator.js"></script>
	<?php require_once($LAY . "js.php"); ?>
	<script>
		let element = ['#find_nama', '#find_alamat', '#find_tgl_lahir', '#find_penanggung_jawab'];
		for (let index = 0; index < element.length; index++) {

			$(element[index]).on('keyup', function() {

				$('#hasil-filter-ajax').html('<div class="row">\
              <div class="col-md-12 col-sm-12 col-xs-12">\
				<div class="x_panel">\
				  <div class="x_title">\
                    <h2>Mohon Tunggu...</h2>\
                    <div class="clearfix"></div>\
                  </div>\
				</div>\
				</div>\
				</div>');
				$.ajax({
					url: 'get_filter_ajax.php',
					method: 'post',
					dataType: 'html',
					data: {
						find_nama: $('#find_nama').val(),
						find_alamat: $('#find_alamat').val(),
						find_tgl_lahir: $('#find_tgl_lahir').val(),
						find_penanggung_jawab: $('#find_penanggung_jawab').val(),
					},
					success: function(res) {
						{
							$('#hasil-filter-ajax').html(res);;
						}
					}
				})
			})

		}


	function layani() {
		var row = $('#dg').datagrid('getSelected');
					if (row) {

		window.location.href ='pemeriksaan_irj_view_pemeriksaan2022.php?id_reg_pasien='+row.reg_id+'&reg_status='+row.reg_status;
	}
			
			// if (row) {
			// 	if (row.reg_status == 'E0') {
			// 		// alert('Pasien Belum Sampai di Poli');
			// 	} else if (row.reg_status != 'E0') {
			// 		$.get('get_irj.php', {
			// 			reg_id: row.reg_id
			// 		}, function(result) {

			// 			document.getElementById('norm').value = result[0].cust_usr_kode_tampilan;
			// 			document.getElementById("cust_usr_id").value = result[0].cust_usr_id;
			// 			document.getElementById('regId').value = result[0].reg_id;
			// 			document.getElementById('regId2').value = result[0].reg_id;
			// 			document.getElementById('reg_status').value = result[0].reg_status;
			// 			document.getElementById('nmps').value = result[0].cust_usr_nama;
			// 			document.getElementById('alps').value = result[0].cust_usr_alamat;
			// 			document.getElementById('reg_jenis_pasien').value = result[0].jenis_nama;
			// 			document.getElementById('klinik').value = result[0].poli_nama;
			// 			document.getElementById('id_poli').value = result[0].id_poli;
			// 			document.getElementById("reg_sebab_sakit").value = result[0].sebab_sakit_nama;
			// 			document.getElementById("reg_shift").value = result[0].shift_nama;
			// 			document.getElementById("foto").value = result[0].cust_usr_foto;
			// 			document.getElementById("reg_tanggal").value = result[0].reg_tanggal;
			// 			document.getElementById('jenis_pasien').value = result[0].reg_jenis_pasien;
			// 			$("#rawat_id").val(result[0].rawat_id);
						

			// 			$('#dokter').combobox('setValue', result[0].id_dokter);
			// 			$('#kondisi').combobox('setValue', result[0].reg_status_kondisi);
			// 			$('#tingkat_kegawatan').combobox('setValue', result[0].reg_tingkat_kegawatan);
			// 			//$('#tingkat_kegawatan').combobox().attr('required','required');

			// 			document.getElementById('btn').style.display = 'block'; //jika edit tombol ganti value
			// 			document.getElementById('btnReset').style.display = 'block'; //jika edit tombol reset muncul


			// 			//load combobox dokter
			// 			var url = 'get_dokterdpjp.php?id_poli=' + result[0].id_poli;
			// 			$('#dokter').combobox('reload', url);

			// 			if ($('#dokter').val() == '') {
			// 				alert('Silahkan pilih Dokter dahulu!');
			// 			}

			// 			//load data
			// 			$('#dg1').edatagrid({
			// 				url: 'get_folio.php'
			// 			});
			// 			$('#dg2').datagrid({
			// 				url: 'get_data_rujukan.php'
			// 			});
			// 			$('#dg3').datagrid({
			// 				url: 'get_gas_medis.php'
			// 			});
			// 			$('#dg4').datagrid({
			// 				url: 'get_ambulance.php'
			// 			});
			// 			$('#dg5').datagrid({
			// 				url: 'get_darah.php'
			// 			});
			// 			$('#dg6').edatagrid({
			// 				url: 'get_preop.php'
			// 			});
			// 			// $('#dg7').edatagrid({
			// 			// 	url: 'get_hasil_lab.php'
			// 			// });
			// 			$('#dg9').edatagrid({
			// 				url: 'get_hasil_lab2.php'
			// 			});
			// 			$('#dg9').edatagrid('load', {
			// 				id_cust_usr: result[0].id_cust_usr
			// 			});
			// 			 GAS BHP TAB 
			// 			$('#dg10').edatagrid({
			// 				url: 'get_data_bhp_tab.php'
			// 			});
			// 			$('#dg10').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			/* GAS BHP TAB */
			// 			$('#dgr').edatagrid({
			// 				url: 'get_hasil_rad.php'
			// 			});
			// 			$('#dghp').edatagrid({
			// 				url: 'get_hasil_pemeriksaan.php'
			// 			});

			// 			$('#tb_diagnosa').edatagrid({
			// 				url: 'ctrl_diagnosa.php'
			// 			});

			// 			$('#tb_procedures').edatagrid({
			// 				url: 'ctrl_procedures.php'
			// 			});

			// 			$('#tb_diagnose').edatagrid({
			// 				url: 'ctrl_diagnose.php'
			// 			});

			// 			$('#tb_procedure').edatagrid({
			// 				url: 'ctrl_procedure.php'
			// 			});

			// 			$('#tb_terapi').edatagrid({
			// 				url: 'ctrl_terapi.php'
			// 			});

			// 			$('#tb_terapi_racikan').edatagrid({
			// 				url: 'ctrl_terapi_racikan.php'
			// 			});

						

						
			// 			// data parameter
			// 			$('#dg1').datagrid('load', {
			// 				id_reg: result[0].reg_id,
			// 			});
			// 			$('#dg2').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dg3').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dg4').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dg5').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dg6').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dg7').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});
			// 			$('#dgr').datagrid('load', {
			// 				id_cust_usr: result[0].id_cust_usr
			// 			});
			// 			$('#dghp').datagrid('load', {
			// 				id_reg: result[0].reg_id
			// 			});

			// 			$('#tb_diagnosa').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 			});
			// 			$('#tb_procedure').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 			});

			// 			$('#tb_diagnose').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 			});
			// 			$('#tb_procedures').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 			});
			// 			if (result[0].layanan == '0bstetri') {



			// 				$('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('load', {
			// 					reg_id: result[0].reg_id,
			// 					anamnesa_id:'9dafa78dca4a01f50d21fbc884a5eecb',
			// 				});
			// 			}
			// 			else {


			// 				$('#riwayat_kehamilan_ginek').datagrid('load', {
			// 					reg_id: result[0].reg_id,
			// 					anamnesa_id:'9dafa78dca4a01f50d21fbc884a5eecb',
			// 				});
			// 			}



			// 			$('#tb_terapi').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 				reg_id: result[0].reg_id,
			// 				cust_usr_id: result[0].cust_usr_id,
			// 				reg_jenis_pasien: result[0].reg_jenis_pasien,
			// 			});
			// 			$('#tb_terapi_racikan').datagrid('load', {
			// 				rawat_id: result[0].rawat_id,
			// 				reg_id: result[0].reg_id,
			// 				cust_usr_id: result[0].cust_usr_id,
			// 				reg_jenis_pasien: result[0].reg_jenis_pasien,
			// 			});
			// 			return false;
			// 		}, 'json');
}
// }
// }
	</script>

	<div class="modal fade" id="modal-id">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">HISTORY KUNJUNGAN <span id="nama-rm"></span></h4>
				</div>
				<div class="modal-body" id="history">

				</div>
				<div class="modal-footer">
					<a id="btn-regist" class="btn btn-primary">OK</a>
				</div>
			</div>
		</div>
	</div>

</body>

</html>