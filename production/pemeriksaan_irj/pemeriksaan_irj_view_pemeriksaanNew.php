<?php

error_reporting();
ini_set('display_errors', 1);
// LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

//INISIALISAI AWAL LIBRARY
$auth = new CAuth();
$userName = $auth->GetUserName();
$userId=$auth->GetUserId();
$depId = $auth->GetDepId();
//AUTHENTIKASI
if (!$auth->IsAllowed("man_ganti_password", PRIV_READ)) {
	die("access_denied");
	exit(1);
} elseif ($auth->IsAllowed("man_ganti_password", PRIV_READ) === 1) {
	echo "<script>window.parent.document.location.href='" . $MASTER_APP . "login/login.php?msg=Session Expired'</script>";
	exit(1);
}
//DATA AWAL
$tglSekarang = date("d-m-Y");

//tabel header
$tableHeader = "Rawat Jalan | Pemeriksaan Pasien";
$sql = "select rawat_id, id_poli, id_cust_usr from klinik.klinik_perawatan where id_reg = " . QuoteValue(DPE_CHAR, $_POST["id_reg"]);
$rs = $dtaccess->Execute($sql);
$dataPasien = $dtaccess->Fetch($rs);

$sql = "select poli_nama ,id_dep from global.global_auth_poli where id_dep = '$depid'  and poli_id = " . QuoteValue(DPE_CHAR, $dataPasien["id_poli"]);
$rs = $dtaccess->Execute($sql);
$dataPoli = $dtaccess->Fetch($rs);
//echo $sql;
$lokasi = $ROOT . "gambar/foto_pasien";
$logoObstetri = $ROOT . "gambar/gambar_obstetri_new.png";
$logoGinekologi = $ROOT . "gambar/gambar_ginekologi.png";

// if ($_GET['id_reg_pasien'] && $_GET['reg_status']) { 
// 	$id_reg_pasien = $_GET['id_reg_pasien'];
// 		$reg_status = $_GET['reg_status'];

// 	echo "<script> layani(".$id_reg_pasien.",". $reg_status."); </script>";

// }
if ($_GET['usr_id']) {
	# data pasien 
	$sql = "select * from global.global_customer_user where cust_usr_id = '$_GET[usr_id]'";
	$rs = $dtaccess->Execute($sql);
	$row = $dtaccess->Fetch($rs);
	// echo $row;
}

$sql = "select dep_konf_tindakan_rujukan from global.global_departemen";
$Konfig = $dtaccess->Fetch($sql);

?>
<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>
<script>

	var _wnd_new;

	function BukaWindow(url, judul) {
		if (!_wnd_new) {
			_wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
		} else {
			if (_wnd_new.closed) {
				_wnd_new = window.open(url, judul, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
			} else {
				_wnd_new.focus();
			}
		}
		return false;
	}
</script>
<script type="text/javascript">
	//filter field data 
// 	$(document).ready(function(){
// 		var a = "<?= $_GET['id_reg_pasien'] ?>";
// 				var b = "<?= $_GET['registrasi_status'] ?>";

//  layani (a,b); 

// });
	$(function() {
		var dg = $('#dg').datagrid();

		var obgL = $(".easyui-tabs").tabs('getTab', 2);
		var ankL = $(".easyui-tabs").tabs('getTab', 3);
		var dalamL = $(".easyui-tabs").tabs('getTab', 4);
		var obg = $(".easyui-tabs").tabs('getTab', 5);
		var ank = $(".easyui-tabs").tabs('getTab', 6);
		var dlm = $(".easyui-tabs").tabs('getTab', 7);
		obgL.panel('options').tab.hide();
		ankL.panel('options').tab.hide();
		dalamL.panel('options').tab.hide();
		obg.panel('options').tab.hide();
		ank.panel('options').tab.hide();
		dlm.panel('options').tab.hide();

		$("a#doNothing").click(function(e){
			e.preventDefault();
		});

		$("form#form_obgynn").find("input#konsull").click(function(){
			var check = $(this).is(":checked");
			if(check == true){
				$("form#form_obgynn").find("input#konsul_detl").css("display", "block");
				$("form#form_obgynn").find('input#konsul_det').attr('disabled', false);
			}
			else{
				$("form#form_obgynn").find("input#konsul_detl").css("display", "none");
				$("form#form_obgynn").find('input#konsul_detl').attr('disabled', false);
			}

		});

		$("form#form_obgynn").find("input#edukasi_lainl").click(function(){
			var check = $(this).is(":checked");
			if(check == true){
				$("form#form_obgynn").find("input#lain_detl").css("display", "block");
				$("form#form_obgynn").find('input#lain_detl').attr('disabled', false);
			}
			else{
				$("form#form_obgynn").find("input#lain_detl").css("display", "none");
				$("form#form_obgynn").find('input#lain_detl').attr('disabled', false);
			}

		});

	  $("input#diagnosal").click(function(){
	  	var check = $(this).is(":checked");
	  	if(check == true){

	  		document.getElementById('bisaMengulangl').checked = true;

	  		document.getElementById('memahamiMateril').checked = true;
	  		document.getElementById('penjelasan_penyakitl').checked = true;
	  		document.getElementById('pemeriksaan_penunjangl').checked = true;
	  		document.getElementById('terapi_edukasil').checked = true;
	  		document.getElementById('prognosal').checked = true;

	  	}


	  });

	  $("form#form_annak").find("input#konsula").click(function(){
	  	var check = $(this).is(":checked");
	  	if(check == true){
	  		$("form#form_annak").find("input#konsul_deta").css("display", "block");
	  		$("form#form_annak").find('input#konsul_deta').attr('disabled', false);
	  	}
	  	else{
	  		$("form#form_annak").find("input#konsul_deta").css("display", "none");
	  		$("form#form_annak").find('input#konsul_deta').attr('disabled', false);
	  	}

	  });

	  $("form#form_annak").find("input#edukasi_laina").click(function(){
	  	var check = $(this).is(":checked");
	  	if(check == true){
	  		$("form#form_annak").find("input#lain_deta").css("display", "block");
	  		$("form#form_annak").find('input#lain_deta').attr('disabled', false);
	  	}
	  	else{
	  		$("form#form_annak").find("input#lain_deta").css("display", "none");
	  		$("form#form_annak").find('input#lain_deta').attr('disabled', false);
	  	}

	  });


	  dg.datagrid('enableFilter', [
			//disable filter
			{
				field: 'reg_waktu',
				type: 'label'
			},
			{
				field: 'cust_usr_kode_tampilan',
				type: 'text'
			},
			{
				field: 'cust_usr_nama',
				type: 'text'
			},
			{
				field: 'cust_usr_alamat',
				type: 'label'
			},
			{
				field: 'cust_usr_tanggal_lahir',
				type: 'label'
			},
			//enable filter
			{
				field: 'reg_status', //filter status
				type: 'combobox',
				options: {
					data: [{
						label: 'Semua',
						value: ''
					}, {
						label: 'Belum Dilayani',
						value: 'Belum Dilayani'
					}, {
						label: 'Sampai di Poli',
						value: 'Sampai di Poli'
					}, {
						label: 'Sudah Dilayani',
						value: 'Sudah Dilayani'
					}],
					valueField: 'value',
					textField: 'label',
					panelHeight: 'auto',
					onChange: function(value) {
						if (value == '') {
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
			}, {
				field: 'reg_tipe_jkn', //filter status
				type: 'combobox',
				options: {
					data: [{
						label: 'Semua',
						value: ''
					}, {
						label: 'PBI',
						value: 'PBI'
					}, {
						label: 'NON PBI',
						value: 'NON PBI'
					}],
					valueField: 'value',
					textField: 'label',
					panelHeight: 'auto',
					onChange: function(value) {
						if (value == '') {
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
			}, {
				field: 'poli_nama', //filter poli
				type: 'combobox',
				options: {
					url: 'get_klinik.php',
					valueField: 'poli_nama',
					textField: 'poli_nama',
					panelHeight: 'auto',
					onChange: function(value) {
						if (value == '') {
							dg.datagrid('removeFilterRule', 'poli_nama');
						} else {
							dg.datagrid('addFilterRule', {
								field: 'poli_nama',
								op: 'equal',
								value: value
							});
							$("#poliFilter").val(value);
						}
						dg.datagrid('doFilter');
					}
				}
			}
			]);
	});
</script>
<script type="text/javascript">
	$(function() {

		$("#form_pemeriksaan").submit(function(e) {
			e.preventDefault();
			form = $('#form_pemeriksaan');
			$.ajax({
				type: 'POST',
				url: 'cek_pemeriksaan.php',
				data: form.serialize(),
				dataType: 'json',
				success: function(result) {
					console.log(result);
					if (result.success) {
						$.messager.show({
							title: 'Berhasil',
							msg: "Proses Pemeriksaan Berhasil Disimpan"
						});
						//window.location.reload();
						resetAll();
					} else {
						$.messager.show({ // show error message
							title: 'Error',
							msg: result.errorMsg
						});
					}

				},
			});
		});

		var dgp = $('#dg_pelaksana').edatagrid();
		var dg1 = $('#dg1').edatagrid();
		var dg2 = $('#dg2').edatagrid();
		var dg3 = $('#dg3').edatagrid();
		var dg4 = $('#dg4').edatagrid();
		var dg5 = $('#dg5').edatagrid();
		var dg6 = $('#dg6').edatagrid();
		var dg7 = $('#dg7').edatagrid();
		var dg8 = $('#dg8').edatagrid();
		var dg9 = $('#dg9').edatagrid();
		var dgr = $('#dgr').edatagrid();
		var dghp = $('#dghp').edatagrid();
		var dgicd = $('#tb_diagnosa').edatagrid();
		var dgicd9 = $('#tb_procedure').edatagrid();
		var dgdiagnose = $('#tb_diagnose').edatagrid();
		var dgprocedures = $('#tb_procedures').edatagrid();
		var dgTerapi = $('#tb_terapi').edatagrid();
		var dgTerapiRacikan = $('#tb_terapi_racikan').edatagrid();
		var racikan = $('#komposisi');
		var rujukan_isi = $('#rujukan_isi');
		var dgbhp = $('#tb_bhp').edatagrid();

		dghp.edatagrid({
			//saveUrl: 'proses_folio.php',
			//updateUrl: 'proses_folio.php',
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dghp.edatagrid('reload');
				}
			}
		});

		dgp.edatagrid({
			saveUrl: 'proses_pelaksana.php',
			updateUrl: 'proses_pelaksana.php',
		});

		dgbhp.edatagrid({
			saveUrl: 'proses_bhp.php',
			updateUrl: 'proses_bhp.php',
		});

		dgr.edatagrid({
			//saveUrl: 'proses_folio.php',
			//updateUrl: 'proses_folio.php',
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dg1.edatagrid('reload');
				}
			}
		});

		dg1.edatagrid({
			saveUrl: 'proses_folio.php',
			updateUrl: 'proses_folio.php',
			rowStyler: function(index, row) {
				if (row.fol_lunas == 'y') {
					return 'background-color:#fbe1e1;font-weight:bold;';
				}
			},
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dg1.edatagrid('reload');
				}
			},
			onClickRow: function(index, row) {
				//tanam fol_id
				//alert(row.fol_id);
				//$('#div_pelaksana').show();
				$('#fol_id').val(row.fol_id);
				$('#biaya_tarif_id').val(row.id_biaya_tarif);

				dgp.edatagrid({
					url: 'get_fol_pelaksana.php',
				});
				// data parameter
				dgp.datagrid('load', {
					fol_id: row.fol_id,
				}, 'reload');

				dgbhp.edatagrid({
					url: 'get_bhp.php',
				});
				// data parameter
				dgbhp.datagrid('load', {
					fol_id: row.fol_id,
				}, 'reload');

			}
		});

		<?php if($Konfig['dep_konf_tindakan_rujukan'] == 'y') { ?>
			dg2.edatagrid({
				saveUrl: 'proses_rujuk.php',
				onClickRow: function(index, row) {

				if (row.poli_nama == 'Radiologi') {
					$('.modal_rujukan2').modal('show');

					var rujukan_isi = $('#rujukan_isi');
					var jenis_pasien = $('#jenis_pasien').val();
					var id_reg = $('#regId').val();
					poliID = row.poli_id;
					REGtanggal = row.reg_tanggal;
					REGwaktu = row.reg_waktu;
					$('#regIdRad').val(row.reg_id);
					rujukan_isi.edatagrid({
						url: 'get_rujukan_isi.php?id_poli=' + row.poli_id + '&jenis_pasien=' + jenis_pasien + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu,
					})
					$('#registId').val(row.reg_id);

					$('#tindakan-rad').removeAttr('style');
					$('#tindakan-lab').attr('style', 'display: none;');
					$('#div_tindakan').attr('style', 'display:none;');
					var tb_tindakan_rad = $('#tb_tindakan_rad').edatagrid();
					tb_tindakan_rad.edatagrid({
						url: 'get_kategori_tindakan_rad.php',
						view: detailview,
						detailFormatter: function(index, row) {
							return "<div style=\"padding:2px\"><table id=\"detail-" + index + "\"></table></div>";
						},
						onExpandRow: function(index, row) {
							$("#detail-" + index).datagrid({
								url: "get_tindakan_rad.php?id=" + row.kategori_tindakan_id+'&jenis_pasien='+jenis_pasien,
								fitColumns: true,
								singleSelect: true,
								rownumbers: true,
								loadMsg: "Tunggu Sebentar",
								height: "auto",
								checkOnSelect: true,
								selectOnCheck: true,
								singleSelect: false,
								columns: [
								[{
									field: "ck",
									checkbox: "true",
									width: 15,
									align: "center"
								},
								{
									field: "biaya_tarif_id",
									title: "Nama",
									width: 50,
									align: "center",
									formatter: function(value, row) {
										return row.biaya_nama;
									}
								},
								{
									field: "biaya_total",
									title: "Biaya",
									width: 10,
									align: "right"
								}
								]
								],


								onResize: function() {
									$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
								},
								onLoadSuccess: function() {
									setTimeout(function() {
										$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
									}, 0);
								}
							});
							$("#tb_tindakan").datagrid("fixDetailRowHeight", index);
						}
					});


					setTimeout(() => {
						$('#tb_tindakan_rad').edatagrid({
							url: 'get_kategori_tindakan_rad.php?id_poli=' + row.poli_id+'&jenis_pasien='+jenis_pasien
						});
					}, 1000);

				}else if (row.poli_nama == 'Laboratorium') {
					$('.modal_rujukan2').modal('show');

					var rujukan_isi = $('#rujukan_isi');
					var jenis_pasien = $('#jenis_pasien').val();
					var id_reg = $('#regId').val();
					poliID = row.poli_id;
					REGtanggal = row.reg_tanggal;
					REGwaktu = row.reg_waktu;
					$('#regIdLab').val(row.reg_id);
					rujukan_isi.edatagrid({
						url: 'get_rujukan_isi.php?id_poli=' + row.poli_id + '&jenis_pasien=' + jenis_pasien + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu,
					})
					$('#registId').val(row.reg_id);

					$('#tindakan-lab').removeAttr('style');
					$('#tindakan-rad').attr('style', 'display: none;');
					$('#div_tindakan').attr('style', 'display:none;');
					var tb_tindakan_lab = $('#tb_tindakan_lab').edatagrid();
					tb_tindakan_lab.edatagrid({
						url: 'get_kategori_tindakan_lab.php',
						view: detailview,
						detailFormatter: function(index, row) {
							return "<div style=\"padding:2px\"><table id=\"detail-" + index + "\"></table></div>";
						},
						onExpandRow: function(index, row) {
							$("#detail-" + index).datagrid({
								url: "get_tindakan_lab.php?id=" + row.kategori_tindakan_id+'&jenis_pasien='+jenis_pasien,
								fitColumns: true,
								singleSelect: true,
								rownumbers: true,
								loadMsg: "Tunggu Sebentar",
								height: "auto",
								checkOnSelect: true,
								selectOnCheck: true,
								singleSelect: false,
								columns: [
								[{
									field: "ck",
									checkbox: "true",
									width: 15,
									align: "center"
								},

								{
									field: "biaya_tarif_id",
									title: "Nama",
									width: 50,
									align: "center",
									formatter: function(value, row) {
										return row.biaya_nama;
									}
								},
								{
									field: "biaya_total",
									title: "Biaya",
									width: 10,
									align: "right"
								}
								]
								],


								onResize: function() {
									$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
								},
								onLoadSuccess: function() {
									setTimeout(function() {
										$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
									}, 0);
								}
							});
							$("#tb_tindakan").datagrid("fixDetailRowHeight", index);
						}
					});


					setTimeout(() => {
						$('#tb_tindakan_lab').edatagrid({
							url: 'get_kategori_tindakan_lab.php?id_poli=' + row.poli_id+'&jenis_pasien='+jenis_pasien
						});
					}, 1000);

				}

				// ++++++++++++++++++++++++++++++++++get tindakan laboratorium +++++++++++++++++++++++++++++++
				else {
					$('#div_tindakan').removeAttr('style');
					$('#tindakan-rad').attr('style', 'display:none;');
					$('#tindakan-lab').attr('style', 'display: none;');
					$('.modal_rujukan2').modal('show');
					var rujukan_isi = $('#rujukan_isi');
					var jenis_pasien = $('#jenis_pasien').val();
					var id_reg = $('#regId').val();
					$('#regIdRujukan').val(row.reg_id);
					poliID = row.poli_id;
					REGtanggal = row.reg_tanggal;
					REGwaktu = row.reg_waktu;
					rujukan_isi.datagrid({
						url: 'get_rujukan_isi.php?id_poli=' + row.poli_id + '&jenis_pasien=' + jenis_pasien + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu,
					});
					setTimeout(() => {

						$('#rujukan_isi').datagrid();
						rujukan_isi.datagrid({
							view: detailview,
							singleSelect: true,
							fitColumns: true,
							fit: false,
							rownumbers: true,
							striped: true,
							detailFormatter: function(index, row) {
								return '<div class="ddv"></div>';
							},
							onExpandRow: function(index, row) {
								var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
								var jenis_pasien = $('#jenis_pasien').val();
								ddv.panel({
									border: false,
									cache: true,
									href: 'frm-rujukan.php?index=' + index + '&id_poli=' + poliID + '&jenis_pasien=' + jenis_pasien,
									onLoad: function() {
										rujukan_isi.datagrid('fixRowHeight', index);
										rujukan_isi.datagrid('selectRow', index);
										rujukan_isi.datagrid('getRowDetail', index).find('form').form('load', row);
									},
								});
								rujukan_isi.datagrid('fixRowHeight', index)
							}
						});
					}, 1000);
				}
				},
				onBeforeEdit: function(index, row){
					var col_poli = $(this).datagrid('getColumnOption', 'poli_id');
					var col_dokterPoli = $(this).datagrid('getColumnOption', 'usr_id');

					col_dokterPoli.editor = {
						type:'combobox',
						options:{
							valueField:'usr_id',
							textField:'usr_name',
							url:'get_dokterPelaksanaRujukan.php',
							panelHeight: '100px',
							required:true
						}
					};

					col_poli.editor = {
						type:'combobox',
						options:{
							valueField:'poli_id',
							textField:'poli_nama',
							url:'get_klinik_all.php',
							panelHeight: '100px',
							required:true,
						}
					};	
				},
				onBeginEdit:function(index,row){
					var editors = $(this).datagrid('getEditors', index);
					var editor_poli = $(editors[0].target);
					var editor_dokter = $(editors[1].target);

					//console.log(editor_poli);
					editor_poli.combobox('options').onSelect = function(record){
						  var poli_id = record.poli_id;
				          editor_dokter.combobox('reload', 'get_dokterPelaksanaRujukan.php?poli_id='+poli_id);
				    }
				},
			});
		<?php } else { ?>
			dg2.edatagrid({
				saveUrl: 'proses_rujuk.php',
				onBeforeEdit: function(index, row){
					var col_poli = $(this).datagrid('getColumnOption', 'poli_id');
					var col_dokterPoli = $(this).datagrid('getColumnOption', 'usr_id');

					col_dokterPoli.editor = {
						type:'combobox',
						options:{
							valueField:'usr_id',
							textField:'usr_name',
							url:'get_dokterPelaksanaRujukan.php',
							panelHeight: '100px',
							required:true
						}
					};

					col_poli.editor = {
						type:'combobox',
						options:{
							valueField:'poli_id',
							textField:'poli_nama',
							url:'get_klinik_all.php',
							panelHeight: '100px',
							required:true,
						}
					};
				},
				onBeginEdit:function(index,row){
					var editors = $(this).datagrid('getEditors', index);
					var editor_poli = $(editors[0].target);
					var editor_dokter = $(editors[1].target);

					//console.log(editor_poli);
					editor_poli.combobox('options').onSelect = function(record){
						  var poli_id = record.poli_id;
				          editor_dokter.combobox('reload', 'get_dokterPelaksanaRujukan.php?poli_id='+poli_id);
				    }
				},
			});
		<?php } ?>




		// dg_rujukan.edatagrid({
		// 	saveUrl: 'proses_rujuk.php',
		// });

		dg3.edatagrid({
			saveUrl: 'proses_gas_medis.php',
			updateUrl: 'proses_gas_medis.php',
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dg3.edatagrid('reload');
				}
			},
			onClickRow: function(index, row) {
				//tanam fol_id
				//alert(row.fol_id);
				$('#fol_id').val(row.fol_id);
				$('#biaya_tarif_id').val(row.id_biaya_tarif);

				dgp.edatagrid({
					url: 'get_fol_pelaksana.php',
				});
				// data parameter
				dgp.datagrid('load', {
					fol_id: row.fol_id,
				}, 'reload');

			}
		});

		dg4.edatagrid({
			saveUrl: 'proses_ambulance.php',
			updateUrl: 'proses_ambulance.php',
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dg4.edatagrid('reload');
				}
			}
		});

		dg5.edatagrid({
			saveUrl: 'proses_darah.php',
			updateUrl: 'proses_darah.php',
			onSelect: function(index, row) {
				if (row.fol_lunas == 'y') {
					alert('sudah dibayar, tidak bisa diedit');
					dg5.edatagrid('reload');
				}
			},
			onClickRow: function(index, row) {
				//tanam fol_id
				//alert(row.fol_id);
				$('#fol_id').val(row.fol_id);
				$('#biaya_tarif_id').val(row.id_biaya_tarif);

				dgp.edatagrid({
					url: 'get_fol_pelaksana.php',
				});
				// data parameter
				dgp.datagrid('load', {
					fol_id: row.fol_id,
				}, 'reload');

			}
		});

		/* dg6 - preop -------------------------------------------------------------------------- */
		dg6.edatagrid({
			saveUrl: 'proses_preop.php?func=store',
			updateUrl: 'proses_preop.php?func=update',
			destroyUrl: 'proses_preop.php?func=destroy',
			onSuccess: function() {
				dg6.edatagrid('reload');
			},
			onBeforeEdit: function(index, row) {
				// alert(row.poli_id);
				if (row.poli_id == '' || row.poli_id == null || row.poli_id == undefined) {
					return true;
				} else {
					alert('Tidak boleh diperbaharui');
					return false;
				}
			},
		});

		dgdiagnose.edatagrid({
			saveUrl: 'ctrl_diagnose.php?func=store',
			updateUrl: 'ctrl_diagnose.php?func=update',
			destroyUrl: 'ctrl_diagnose.php?func=destroy',
		});

		dgprocedures.edatagrid({
			saveUrl: 'ctrl_procedures.php?func=store',
			updateUrl: 'ctrl_procedures.php?func=update',
			destroyUrl: 'ctrl_procedures.php?func=destroy',
		});

		dgicd.edatagrid({
			saveUrl: 'ctrl_diagnosa.php?func=store',
			updateUrl: 'ctrl_diagnosa.php?func=update',
			destroyUrl: 'ctrl_diagnosa.php?func=destroy',
		});

		dgicd9.edatagrid({
			saveUrl: 'ctrl_procedure.php?func=store',
			updateUrl: 'ctrl_procedure.php?func=update',
			destroyUrl: 'ctrl_procedure.php?func=destroy',
		});

		dgTerapi.edatagrid({
			saveUrl: 'ctrl_terapi.php?func=store',
			updateUrl: 'ctrl_terapi.php?func=update',
			destroyUrl: 'ctrl_terapi.php?func=destroy',
			onClickRow: function(index, row) {
				$('#tb_terapi').edatagrid('reload');
			}
		});

		dgTerapiRacikan.edatagrid({
			saveUrl: 'ctrl_terapi_racikan.php?func=store',
			updateUrl: 'ctrl_terapi_racikan.php?func=update',
			destroyUrl: 'ctrl_terapi_racikan.php?func=destroy&id_cust_usr=<?php echo $_GET[usr_id] ?>',
			onClickRow: function(index, row) {

				$('#toolbar_rujukan').removeAttr('style');

				$('.modal_rujukan').modal('show');
				var rujukan_isi = $('#komposisi');
				var id_rawat = $('#rawat_id').val();
				var id_reg = $('#regId').val();

				poliID = row.poli_id;
				REGtanggal = row.reg_tanggal;
				REGwaktu = row.reg_waktu;
				rawat_terapi_racikan_id = row.rawat_terapi_racikan_id;
				$('#id_penjualan').val(row.rawat_terapi_racikan_id);
					racikan.datagrid(); //----
					racikan.datagrid({
						url: 'get_komposisi.php?id=' + row.rawat_terapi_racikan_id,

					})
					setTimeout(() => {

						$('#rujukan_isi').datagrid();
						rujukan_isi.datagrid({
							view: detailview,
							singleSelect: true,
							fitColumns: true,
							fit: false,
							rownumbers: true,
							striped: true,
							detailFormatter: function(index, row) {
								return '<div class="ddv"></div>';
							},
							onExpandRow: function(index, row) {
								var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
								var id_rawat = $('#rawat_id').val();
								ddv.panel({
									border: false,
									cache: true,
									href: 'frm_komposisi.php?index=' + index + '&id_rawat=' + id_rawat + '&rawat_terapi_racikan_id=' + rawat_terapi_racikan_id,
									onLoad: function() {
										racikan.datagrid('fixRowHeight', index);
										racikan.datagrid('selectRow', index);
										racikan.datagrid('getRowDetail', index).find('form').form('load', row);
									},
								});
								racikan.datagrid('fixRowHeight', index)
							}
						});
					}, 1000);
				},
			});

		$('#dg10').edatagrid({
			saveUrl: 'proses_bhp_tab.php',
			updateUrl: 'proses_bhp_tab.php',
			onSelect: function(index,row){
				if (row.fol_lunas == 'y' ){		
					alert('sudah dibayar, tidak bisa diedit'); 
					$('#dg10').edatagrid('reload');
				}
			},
			rowStyler:function(index,row){
				if (row.fol_lunas == 'y' ){
					return 'background-color:#fbe1e1;font-weight:bold;';
				}
			},
			onSelect: function(index,row){
				if (row.fol_lunas == 'y' ){		
					alert('sudah dibayar, tidak bisa diedit'); 
					dg1.edatagrid('reload');
				}
			},
			onClickRow: function(index,row){
				$('#fol_id').val(row.fol_id);
				$('#biaya_tarif_id').val(row.id_biaya_tarif);

			}
		});

		racikan.datagrid();
		racikan.datagrid({
			view: detailview,
			singleSelect: true,
			fitColumns: true,
			fit: false,
			rownumbers: true,
			striped: true,

			detailFormatter: function(index, row) {
				return '<div class="ddv"></div>';
			},
			onExpandRow: function(index, row) {
				var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
				var id_rawat = $('#rawat_id').val();
				ddv.panel({
					border: false,
					cache: true,
					href: 'frm_komposisi.php?index=' + index + '&id_rawat=' + id_rawat + '&rawat_terapi_racikan_id=' + rawat_terapi_racikan_id,
					onLoad: function() {
						racikan.datagrid('fixRowHeight', index);
						racikan.datagrid('selectRow', index);
						racikan.datagrid('getRowDetail', index).find('form').form('load', row);
					},
				});
				racikan.datagrid('fixRowHeight', index)
			}
		});

	});

function get_harga(index,record){
	console.log(record);
	var row = $('#dg10').datagrid('getSelected');
	var rowIndex = $('#dg10').datagrid('getRowIndex',row);
	var ed2 = $('#dg10').edatagrid('getEditor', {index:rowIndex,field:'biaya'});
	var ed3 = $('#dg10').edatagrid('getEditor', {index:rowIndex,field:'satuan_nama'});
	(ed3.target).val(record.satuan_nama);
	(ed2.target).numberspinner('setValue',record.item_harga_jual);
}

function get_harga_bhp(index,record){
	console.log(record);
	var row = $('#tb_bhp').datagrid('getSelected');
	var rowIndex = $('#tb_bhp').datagrid('getRowIndex',row);
	var ed2 = $('#tb_bhp').edatagrid('getEditor', {index:rowIndex,field:'satuan_nama'});
	(ed2.target).val(record.satuan_nama);
}
</script>



<!-- /////////////////// -->

<body class="nav-md">
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
							<h3><?php echo $tableHeader; ?></h3>
						</div>
					</div>
					<div class="clearfix"></div>



<!-- row 2 == Data View Pasien -->
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">

		<!-- tab-->
		<div class="easyui-tabs" style="width:0%; height:0">
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>
			<div title=" "></div>	
		</div>


          

<!--end tab-->



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

				<div class="modal fade bs-example-modal-lg modal_rujukan" id="modal_rujukan" role="dialog" aria-hidden="true" >
					<div class="modal-dialog modal-lg">
						<div class="modal-content">

							<div class="modal-header">
										<!-- <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
										</button> -->
										<h4 class="modal-title" id="myModalLabel">Komposisi Racikan</h4>
									</div>
									<div class="modal-body">
										<div id="toolbar_rujukan">
											<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newRow()">Baru</a>
											<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#komposisi').datagrid('reload')">Refresh</a>
											<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroykomposisi()">Hapus</a>
										</div>
										<table class="" id="komposisi" style="width:100%; height: auto;" data-options="toolbar:'#toolbar_rujukan',onLoadSuccess:function(){newRow()}">
											<thead>
												<tr>
													<th width="50" field="item_nama">Nama Item</th>
													<th width="50" field="terapi_jumlah_item">Jumlah</th>
													<th width="50" field="id_rawat_terapi_racikan" hidden="">id racikan</th>
													<th width="50" field="rawat_item_id" hidden="">id</th>
												</tr>
											</thead>
										</table>
									</div>
									<div class="modal-footer">
										<button type="button" onclick="prosesHitungKomposisi()" class="btn btn-default" data-dismiss="modal">Close</button>
									</div>

								</div>
							</div>
						</div>

						<div class="modal fade bs-example-modal-lg modal_rujukan2" id="modal_rujukan2" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-lg">
								<div class="modal-content">

									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
										</button>
										<h4 class="modal-title" id="myModalLabel">Pemeriksaan</h4>
									</div>
									<div class="modal-body">
										<div id="div_tindakan">
											<div id="toolbar_rujukann">
												<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newRujukan()">Baru</a>
												<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#rujukan_isi').datagrid('reload')">Refresh</a>
												<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroykomposisii()">Hapus</a>
												<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_tindakan_lab()">Cetak Tindakan</a>
											</div>
											<table class="" id="rujukan_isi" style="width:100%; height: auto;" data-options="toolbar:'#toolbar_rujukann'">
												<thead>
													<tr>
														<th width="50" field="tindakan_rujukan">Pemeriksaan</th>
														<th width="50" field="id_tindakan" hidden="">id</th>
														<th width="50" field="folio_id" hidden="">id</th>
													</tr>
												</thead>
											</table>
										</div>
										<div id="tindakan-rad">
											<table id="tb_tindakan_rad" style="width:100%;" toolbar="#toolbar2_rad" pagination="false" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
												<thead>
													<tr>
														<th data-options="field:'kategori_tindakan_id',width:50, 
														formatter:function(value,row){
														return row.kategori_tindakan_nama;
													}">Kategori</th>
												</tr>
											</thead>
										</table>
										<div id="toolbar2_rad">
											<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_tindakan_rad()">Simpan</a>
											<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#tb_tindakan_rad').edatagrid('reload')">refresh</a>
										</div>
									</div>
									<div id="tindakan-lab">
										<table id="tb_tindakan_lab" style="width:100%;" toolbar="#toolbar2_lab" pagination="false" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
											<thead>
												<tr>
													<th data-options="field:'kategori_tindakan_id',width:50, 
														formatter:function(value,row){
														return row.kategori_tindakan_nama;
													}">Kategori</th>
												</tr>
											</thead>
										</table>
										<div id="toolbar2_lab">
											<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_tindakan_lab()">Simpan</a>
											<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#tb_tindakan_lab').edatagrid('reload')">refresh</a>
											<!-- <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_tindakan_lab()">Cetak Tindakan</a> -->
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
								</div>

							</div>
						</div>
					</div>


				</div>

			</div>

		</div>
	</div>
	<!-- /page content -->

	<!-- footer content -->
	<?php require_once($LAY . "footer.php") ?>
	<!-- /footer content -->
</div>
</div>
<!-- jQuery -->
<?php require_once($LAY . "js.php") ?>
<script type="text/javascript">
	function cellStyler(value, row, index) {

		if (value == 'E2') {
			return 'background-color:green;color:white;';
		}

	}
</script>

<script type="text/javascript">
	function newRujukan() {
		var dt = {
			isNewRecord: true,
		}
		$('#rujukan_isi').datagrid('appendRow', dt);
		var index = $('#rujukan_isi').datagrid('getRows').length - 1;
		$('#rujukan_isi').datagrid('expandRow', index);
		$('#rujukan_isi').datagrid('selectRow', index);
		$('#rujukan_isi').datagrid('fixDetailRowHeight', index);
	}

	function cancelRujukan(index) {
		var row = $('#rujukan_isi').datagrid('getRows')[index];
		if (row.isNewRecord) {
			$('#rujukan_isi').datagrid('deleteRow', index);
		} else {
			$('#rujukan_isi').datagrid('collapseRow', index);
		}
	}

	function cetak() {
		var row = $('#dg').datagrid('getSelected');
		if (row) {
			var url = '../edit_registrasi/cetak_registrasi.php?reg_id=' + row.reg_id;
			var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
		}
	}

	function saveRujukan(index) {
		var row = $('#rujukan_isi').datagrid('getRows')[index];
		var id_reg = $('#regId').val();
		var url = row.isNewRecord ? 'simpan-rujukan.php?id_poli=' + poliID + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu : 'update-komposisi.php?func=update&id=1';
		$('#rujukan_isi').datagrid('getRowDetail', index).find('form').form('submit', {
			url: url,
			onSubmit: function(param) {
				param.tindakan_rujukan = row.tindakan_rujukan;
				return $(this).form('validate');
			},
			success: function(data) {
				data = eval('(' + data + ')');
				data.isNewRecord = false;
				$('#rujukan_isi').datagrid('collapseRow', index);
				$('#rujukan_isi').datagrid('updateRow', {
					index: index,
					row: data
				});
			}
		});
	}

	function destroykomposisii() {
		var row = $('#rujukan_isi').datagrid('getSelected');
		if (row) {
			$.messager.confirm('Konfirmasi', 'Anda Yakin?', function(r) {
				if (r) {
					var index = $('#rujukan_isi').datagrid('getRowIndex', row);
					$.get('hapus-rujukan.php?id=' + row.id_tindakan + '&fol_id=' + row.folio_id, {}, function() {
						$('#rujukan_isi').datagrid('deleteRow', index);
					});
				}
			});
		}
	}

	function myformatter(date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		return (d < 10 ? ('0' + d) : d) + '-' + (m < 10 ? ('0' + m) : m) + '-' + y;
	}

	function myparser(s) {
		if (!s) return new Date();
		var ss = (s.split('-'));
		var y = parseInt(ss[0], 10);
		var m = parseInt(ss[1], 10);
		var d = parseInt(ss[2], 10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
			return new Date(d, m - 1, y);
		} else {
			return new Date();
		}
	}

	function formatters(date) {
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();
		var s2 = [date.getHours(), date.getMinutes(), date.getSeconds()].join(':');
		return (d < 10 ? ('0' + d) : d) + '-' + (m < 10 ? ('0' + m) : m) + '-' + y + ' ' + s2;
	}

	function parsers(s) {
		if (!s) return new Date();
		var ss = (s.split(' '));
		var dt = (ss[0].split('-'));
		var tm = (ss[1].split(':'));
		var y = parseInt(dt[0]);
		var m = parseInt(dt[1]);
		var d = parseInt(dt[2]);
		var h = parseInt(tm[0])
		var i = parseInt(tm[1])
		var s = parseInt(tm[2])

		if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
			return new Date(d, m - 1, y, h, i, s);
		} else {
			return new Date();
		}
	}
</script>

<script type="text/javascript">
	var d = new Date();
	var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
	var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
	var notif1 = "Pilih pasien dahulu.";
	var notif2 = "Pilih tindakan dahulu.";


	function cari() {
		$('#dg').edatagrid('load', {
			tgl_awal: $('#tgl_awal').val(),
			tgl_akhir: $('#tgl_akhir').val()
		});

	}

		//pre operasi ---------------------------
		function delete_preop() {
			var row = $('#dg6').datagrid('getSelected');
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin?', function(r) {
					if (r) {
						$.post('del_preop.php', {
							id: row.preop_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dibatalkan"
								});
								$('#dg6').datagrid('reload'); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}

		}

		/* dg6 - preop ------------------------------------------------------------------------------------------ */

		function add_preop() {
			var reg_id = $('#regId').val();
			var d = new Date();
			var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
			var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();

			if (reg_id != "") {
				$('#dg6').edatagrid('addRow', {
					// index: 0,
					row: {
						preop_waktu: tanggal + ' ' + waktu,
						preop_tanggal_jadwal: tanggal + ' ' + waktu,
						// preop_selesai_jadwal: tanggal+' '+waktu,
						reg_id: reg_id,
					}
				});
			}
		}

		// labu darah ------------------------------------
		function delete_darah() {
			var row = $('#dg5').datagrid('getSelected');
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_folio.php', {
							id: row.fol_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg5').datagrid('reload'); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}

		}

		function simpan_darah() {
			$('#dg5').edatagrid('saveRow');
			$('#dg5').edatagrid({
				url: 'get_darah.php'
			}); //load data
			// data parameter
			$('#dg5').datagrid('load', {
				id_reg: document.getElementById('regId').value
			}, 'reload');
		}

		function add_darah() {
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != "") {
				$('#dg5').edatagrid('addRow', {
					index: 0,
					row: {
						tindakan_tanggal: tanggal,
						tindakan_waktu: waktu,
						id_reg: regId,
						fol_jumlah: '1'
					}
				});
			} else {
				alert(notif1);
			}
		}
		// ambulance ------------------------------
		function delete_ambulance() {
			var row = $('#dg4').datagrid('getSelected');
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_folio.php', {
							id: row.fol_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg4').datagrid('reload'); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}

		}

		function simpan_ambulance() {
			$('#dg4').edatagrid('saveRow');
			$('#dg4').edatagrid({
				url: 'get_ambulance.php'
			}); //load data
			// data parameter
			$('#dg4').datagrid('load', {
				id_reg: document.getElementById('regId').value
			}, 'reload');
		}

		function add_ambulance() {
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != '') {
				$('#dg4').edatagrid('addRow', {
					index: 0,
					row: {
						tindakan_tanggal: tanggal,
						tindakan_waktu: waktu,
						id_reg: regId,
						fol_jumlah: '1'
					}
				});
			} else {
				alert(notif1);
			}
		}
		// gas medis-------------------------------
		function delete_gas() {
			var row = $('#dg3').datagrid('getSelected');
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_folio.php', {
							id: row.fol_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg3').datagrid('reload'); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}

		}

		function simpan_gas() {
			$('#dg3').edatagrid('saveRow');
			$('#dg3').edatagrid({
				url: 'get_gas_medis.php'
			}); //load data
			// data parameter
			$('#dg3').datagrid('load', {
				id_reg: document.getElementById('regId').value
			}, 'reload');
		}

		function add_gas() {
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != '') {
				$('#dg3').edatagrid('addRow', {
					index: 0,
					row: {
						tindakan_tanggal: tanggal,
						tindakan_waktu: waktu,
						id_reg: regId,
						fol_jumlah: '1'
					}
				});
			} else {
				alert(notif1);
			}
		}

		/* GAS BHP TAB */
		function delete_obat(){		
			var row = $('#dg10').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_bhp_tab.php',{item_id:row.item_id,id_reg:row.id_reg},function(result){
							if (result.success){
											$.messager.show({	// 
												title: 'Berhasil',
												msg: "Berhasil Dihapus"
											});
											$('#dg10').datagrid('reload');	// reload the user data
										} else {
											$.messager.show({	// show error message
												title: 'Error',
												msg: result.errorMsg
											});
										}
									},'json');
					}
				});
			}

		}

		function simpan_obat(){
			$('#dg10').edatagrid('saveRow');
						$('#dg10').edatagrid({ url: 'get_data_bhp_tab.php' }); //load data
						// data parameter
						$('#dg10').datagrid('load', {
							id_reg: document.getElementById('regId').value
						},'reload');
					}

					function add_obat(){
						// insert a row with default values
						var regId = document.getElementById('regId').value;
						$('#dg10').edatagrid('addRow',{
							index: 0,
							row:{
								id_reg : regId,
								fol_jumlah : '1'
							}
						});
					}
					/* END GAS BHP TAB */

		//  pemeriksaan ----------------
		$("#btn_pemeriksaan").click(function(){
			var dataString = 'id_reg=' + document.getElementById('regId').value + 
							 '&anamnesa=' + document.getElementById('anamnesa').value +
							 '&observasi=' + document.getElementById('observasi').value +
							 '&konsultasi=' + document.getElementById('konsultasi').value +
							 '&pemeriksaan_umum=' + document.getElementById('pemeriksaan_umum').value +
							 '&pencatatan_diagnosa=' + document.getElementById('pencatatan_diagnosa').value +
							 '&resume_medis=' + document.getElementById('resume_medis').value;
			  $.ajax({
				type: "POST",
				url: "proses_pemeriksaan.php",
				data: dataString,
				success: function(){
					alert("Berhasil disimpan");
				}
			  });
			return false;
		});

		$("#btn_pemeriksaan_anak").click(function() {
			var form = $('#form_annak');
			var subjective = form.find("#subjectivel").val();
			var objective = form.find("#objectivel").val();
			var terapi = form.find("#values_l").find("div").toArray();
			var planning = form.find("#planningl").val();

			var a = [];
			for ( var i = 0; i < terapi.length; i++ ) {
				a.push( terapi[ i ].innerHTML );
			}

			var valuess = form.serializeArray();

			var dataReg = document.getElementById('regId').value;

			$.post("proses_pemeriksaan_anak.php", {
				dataReg : dataReg, forma : valuess, terapi : a
			}).done(function(data){
				alert(data);
			});

			return false;
		});

		$("#btn_pemeriksaan_dalam").click(function() {
			var form = $('#form_dalamm');
			var subjective = form.find("#subjectivel").val();
			var objective = form.find("#objectivel").val();
			var terapi = form.find("#values_l").find("div").toArray();
			var planning = form.find("#planningl").val();

			var a = [];
			for ( var i = 0; i < terapi.length; i++ ) {
				a.push( terapi[ i ].innerHTML );
			}

			var valuess = form.serializeArray();

			var dataReg = document.getElementById('regId').value;

			$.post("proses_pemeriksaan_dalam.php", {
				dataReg : dataReg, forma : valuess, terapi : a
			}).done(function(data){
				alert(data);
			});

			
			return false;
		});

		$("#cetak_resume_lanjutan").click(function() {
			var form = $('#form_obgynn');
			var dataString = 'id_reg=' + document.getElementById('regId').value;
			window.open('cetak_resume_poli_lanjutan.php?' + dataString, '_blank');
		});

		$("#cetak_resume_medis").click(function() {
			
			var dataString = 'id=' + document.getElementById('rawat_id').value;
			BukaWindow('cetak_bpjs.php?' + dataString, "Resume");
		});

		$("#cetak_bridge").click(function() {
			
			var dataString = 'id=' + document.getElementById('rawat_id').value;
			BukaWindow('cetak_bridge.php?' + dataString, "Resume");
		});

		$("#cetak_resume_medis_anak").click(function() {
			
			var dataString = 'id=' + document.getElementById('rawat_id').value;
			BukaWindow('cetak_bpjs.php?' + dataString, "Resume");
		});

		$("#cetak_resume_lanjutan_anak").click(function() {
			
			var dataString = 'asd=' + document.getElementById('rawat_id').value;
			window.open('cetak_resume_poli_anak_lanjutan.php?' + dataString, '_blank');
		});

		$("#cetak_resume_lanjutan_dalam").click(function() {
			
			var dataString = 'asd=' + document.getElementById('rawat_id').value;
			window.open('cetak_resume_poli_dalam_lanjutan.php?' + dataString, '_blank');
		});

		// rujuk -----------------
		function add_rujuk() {
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != '') {
				$('#dg2').edatagrid('addRow', {
					index: 0,
					row: {
						regId: regId,
						fol_jumlah: '1'
					}
				});
			} else {
				alert(notif1);
			}
		}

		function simpan_rujuk() {
			var simpan = $('#dg2').edatagrid('saveRow');
			var reg_id = $("input#regId").val();
			if (simpan) {
				//load data
				$('#dg2').datagrid({
					url: 'get_data_rujukan.php'
				});
				//$('#dg').datagrid('reload');

				// data parameter
				$('#dg2').datagrid('load', {
					id_reg: reg_id
				}, 'reload');
			}

		}
		//tindakan ----------------------------------
		function delete_folio() {
			var row = $('#dg1').datagrid('getSelected');
			if (row.fol_lunas == 'n') {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_folio.php', {
							id: row.fol_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg1').datagrid('reload'); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}
			else{
				alert('sudah dibayar, tidak bisa diedit');
			}
		}

		function simpan_diagnose() {
			$('#tb_diagnose').edatagrid('saveRow');
			$('#tb_diagnose').edatagrid({
				url: 'ctrl_diagnose.php'
			}); //load data
			// data parameter
			$('#tb_diagnose').datagrid('load', {
				rawat_id: document.getElementById('rawat_id').value
			}, 'reload');

		}

		function simpan_procedures() {
			$('#tb_procedures').edatagrid('saveRow');
			$('#tb_procedures').edatagrid({
				url: 'ctrl_procedures.php'
			}); //load data
			// data parameter
			$('#tb_procedures').datagrid('load', {
				rawat_id: document.getElementById('rawat_id').value
			}, 'reload');

		}

		function simpan_folio() {
			$('#dg1').edatagrid('saveRow');
			$('#dg1').edatagrid({
				url: 'get_folio.php'
			}); //load data
			// data parameter
			$('#dg1').datagrid('load', {
				id_reg: document.getElementById('regId').value
			}, 'reload');

		}

		function add_folio() {
			var regId = document.getElementById('regId').value;
			var regTanggal = document.getElementById('reg_tanggal').value;
			var regStatus = document.getElementById('reg_status').value;
			//$('#dg_pelaksana').edatagrid('reload');
			// $('#dg1').edatagrid('reload');
			var lunas ='n';
			var is_transfer='n';
			
			$.ajax({
				url: 'get_folio.php',
				type: 'POST',
				data: {
					id_reg: regId
				},
				dataType: 'JSON',
				success: function(e) {
					console.log(e[0].fol_lunas);
					lunas = e[0].fol_lunas;
					is_transfer = e[0].is_transfer;
				}
			});
			// ==========================================================


			if (regId != "" && regStatus != 'E3' && lunas != 'y' ) {
				var a = $('#dg1').edatagrid('addRow', {
					index: 0,
					row: {
						tindakan_tanggal: regTanggal,
						tindakan_waktu: waktu,
						id_reg: regId,
						fol_jumlah: '1'
					}
				});


			} else {
				alert('Sudah dibayar, Tidak Bisa Input Tindakan');
			}
			// if (regId != "" || regStatus == 'G3' ) {
			// 	alert('Sudah dibayar, Tidak Bisa Input Tindakan');
			// }



			if (regId == "") {
				alert(notif1);
			}
		}

		//pelaksana ----------------------------------
		function delete_pelaksana() {
			var dgp = $('#dg_pelaksana').edatagrid();
			var row = dgp.edatagrid('getSelected');
			var fol_id = $('#fol_id').val();
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_pelaksana.php', {
							id: row.fol_pelaksana_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								dgp.edatagrid('load', {
									fol_id: fol_id
								}); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}
		}

		function simpan_pelaksana() {
			var fol_id = $('#fol_id').val();
			$('#dg_pelaksana').edatagrid('saveRow');
			$('#dg_pelaksana').edatagrid({
				url: 'get_fol_pelaksana.php'
			}); //load data
			// data parameter
			$('#dg_pelaksana').datagrid('load', {
				fol_id: fol_id
			}, 'reload');
		}

		function add_pelaksana() {
			var dokter = $('#dokter').val();
			//alert(dokter);					   
			var fol_id = $('#fol_id').val();
			var id_posisi = 2;
			var biaya_tarif_id = $('#biaya_tarif_id').val();
			// insert a row with default values
			if (fol_id != '') {
				$('#dg_pelaksana').edatagrid('addRow', {
					index: 0,
					row: {
						id_fol: fol_id,
						id_biaya_tarif: biaya_tarif_id,
						//usr_id : dokter,
						fol_pelaksana_tipe: id_posisi
					}
				});
			} else {
				alert(notif2);
			}
		}

		//BHP
		function add_bhp() {
			var fol_id = $('#fol_id').val();
			if (regId != "") {
				$('#tb_bhp').edatagrid('addRow', {
					index: 0,
					row: {
						fol_id: fol_id
					}
				});
			} else {
				alert(notif2);
			}
		}

		function simpan_bhp() {
			var fol_id = $('#fol_id').val();
			$('#tb_bhp').edatagrid('saveRow');
			$('#tb_bhp').edatagrid({
				url: 'get_bhp.php'
			}); //load data
			// data parameter
			$('#tb_bhp').datagrid('load', {
				fol_id: fol_id
			}, 'reload');
		}

		function delete_bhp() {
			var dgbhp = $('#tb_bhp').edatagrid();
			var row = dgbhp.edatagrid('getSelected');
			var fol_id = $('#fol_id').val();
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda yakin menghapus data ini?', function(r) {
					if (r) {
						$.post('del_bhp.php', {
							id: row.fol_pemakaian_id
						}, function(result) {
							if (result.success) {
								$.messager.show({ // 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								dgbhp.edatagrid('load', {
									fol_id: fol_id
								}); // reload the user data
							} else {
								$.messager.show({ // show error message
									title: 'Error',
									msg: result.errorMsg
								});
							}
						}, 'json');
					}
				});
			}
		}
		//layani -------------------

		$("input#janin_kembarl").click(function(){
		    var check = $(this).is(":checked");
		    if(check == true){
		        $("input#janin_kembarl_det").css("display", "block");
		        $("input#janin_kembarl_det").attr("disabled", false);
		    }
		    else{
		    	$("input#janin_kembarl_det").css("display", "none");
		      	$("input#janin_kembarl_det").attr("disabled", true);
		    }

		});

		$("input#janin_kembarl_det").focusout(function(){
		  	var jumlah = parseInt($(this).val());
		  	jumlah = (jumlah == 0 || jumlah == null || Number.isInteger(jumlah) === false) ? 1 : jumlah;
		  	var html = "";
		  	var count = $("form#form_obgynn div#divJenkel div#detJenkel").length;
		  	var tambahan = jumlah - count;

		  	if(tambahan > 0){
		  		for(var i=0; i < tambahan; i++){
			  		html += "<div class='col-md-12' id='detJenkel' style='margin: 5px 0;'>";
			  		html += "<select class='form-control' name='jenis_kelamin_"+(count+i)+"'>";
			  		html += "<option value=''> - </option>";
			  		html += "<option value='Laki'>Laki-laki</option>";
			  		html += "<option value='Perempuan'>Perempuan</option>";
			  		html += "</select>";
			  		html += "</div>";
			  	}

			  	$("form#form_obgynn div#divJenkel").append(html);
		  	}
		  	else if(tambahan < 0){
		  		$("form#form_obgynn div#divJenkel div#detJenkel").slice(tambahan).remove();
		  	}
		  	

		  });
		

		function layani() {
			var row = $('#dg').datagrid('getSelected');
			// console.log(row);
			if (row.reg_id) {
				if (row.reg_status == 'E0') {
					alert('Pasien Belum Sampai di Poli');
				} else if (row.reg_status != 'E0') {
					location.href = "pemeriksaan_irj_view_pemeriksaanNew2.php?id_reg_pasien="+row.reg_id+"&registrasi_status="+row.reg_status;
				}
			}
		};

function resetAll() {
	var tdkn = $(".easyui-tabs").tabs('getTab', 0);
	var obgL = $(".easyui-tabs").tabs('getTab', 2);
	var ankL = $(".easyui-tabs").tabs('getTab', 3);
	var obg = $(".easyui-tabs").tabs('getTab', 4);
	var ank = $(".easyui-tabs").tabs('getTab', 5);


	obgL.panel('options').tab.hide();
	obgL.panel('close');
	obg.panel('options').tab.hide();
	obg.panel('close');
	ankL.panel('options').tab.hide();
	ankL.panel('close');
	ank.panel('options').tab.hide();
	ank.panel('close');
	tdkn.panel('open');


	document.getElementById('norm').value = "";
	document.getElementById("cust_usr_id").value = "";
	document.getElementById('regId').value = "";
	document.getElementById('nmps').value = "";
	document.getElementById('alps').value = "";
	document.getElementById('reg_jenis_pasien').value = "";
	document.getElementById('klinik').value = "";
	document.getElementById('id_poli').value = "";
	document.getElementById("reg_sebab_sakit").value = "";
	document.getElementById("reg_shift").value = "";
	document.getElementById("foto").value = "";
	document.getElementById("reg_tanggal").value = "";
	document.getElementById('jenis_pasien').value = "";
	$('#jenis_kb_id').combobox('setValue', "");
	$("#rawat_id").val("");


	$("th#diagKhusus").css("visibility", "hidden");
	$("td#diagKhusus").css("visibility", "hidden");

	$("th#rawat_alergi").css("visibility", "hidden");
	$("td#rawat_alergi").css("visibility", "hidden");

	$('#dokter').combobox('setValue', "");
	$('#kondisi').combobox('setValue', "");
	$('#tingkat_kegawatan').combobox('setValue', "");
			//$('#tingkat_kegawatan').combobox().attr('required','required');

			document.getElementById('btn').style.display = 'block'; //jika edit tombol ganti value
			document.getElementById('btnReset').style.display = 'block'; //jika edit tombol reset muncul


			//load combobox dokter
			var url = 'get_dokterdpjp.php?id_poli=' + "";
			$('#dokter').combobox('reload', url);

			//load data
			$('#dg1').edatagrid({
				url: 'get_folio.php'
			});
			$('#dg2').datagrid({
				url: 'get_data_rujukan.php'
			});
			$('#dg3').datagrid({
				url: 'get_gas_medis.php'
			});
			$('#dg4').datagrid({
				url: 'get_ambulance.php'
			});
			$('#dg5').datagrid({
				url: 'get_darah.php'
			});
			$('#dg6').edatagrid({
				url: 'get_preop.php'
			});
			// $('#dg7').edatagrid({
			// 	url: 'get_hasil_lab.php'
			// });
			$('#dg9').edatagrid({
				url: 'get_hasil_lab2.php'
			});

			$('#dg9').edatagrid('load', {
				id_cust_usr: ""
			});
			/* GAS BHP TAB */
			$('#dg10').edatagrid({
				url: 'get_data_bhp_tab.php'
			});
			$('#dg10').datagrid('load', {
				id_reg: ""
			});
			/* GAS BHP TAB */
			$('#dgr').edatagrid({
				url: 'get_hasil_rad.php'
			});
			$('#dghp').edatagrid({
				url: 'get_hasil_pemeriksaan.php'
			});

			$('#tb_diagnosa').edatagrid({
				url: 'ctrl_diagnosa.php'
			});

			$('#tb_procedures').edatagrid({
				url: 'ctrl_procedures.php'
			});

			$('#tb_diagnose').edatagrid({
				url: 'ctrl_diagnose.php'
			});

			$('#tb_procedure').edatagrid({
				url: 'ctrl_procedure.php'
			});

			$('#tb_terapi').edatagrid({
				url: 'ctrl_terapi.php'
			});

			$('#tb_terapi_racikan').edatagrid({
				url: 'ctrl_terapi_racikan.php'
			});

			
			// data parameter
			$('#dg1').datagrid('load', {
				id_reg: "",
			});

			$('#dg2').datagrid('load', {
				id_reg: ""
			});

			$('#dg3').datagrid('load', {
				id_reg: ""
			});

			$('#dg4').datagrid('load', {
				id_reg: ""
			});

			$('#dg5').datagrid('load', {
				id_reg: ""
			});

			$('#dg6').datagrid('load', {
				id_reg: ""
			});

			$('#dg7').datagrid('load', {
				id_reg: ""
			});

			$('#dgr').datagrid('load', {
				id_cust_usr: ""
			});

			$('#dghp').datagrid('load', {
				id_reg: ""
			});

			$('#tb_diagnosa').datagrid('load', {
				rawat_id: "",
			});

			$('#tb_procedure').datagrid('load', {
				rawat_id: "",
			});

			$('#tb_diagnose').datagrid('load', {
				rawat_id: "",
			});

			$('#tb_procedures').datagrid('load', {
				rawat_id: "",
			});

			$('#tb_terapi').datagrid('load', {
				rawat_id: "",
				reg_id: "",
				cust_usr_id: "",
				reg_jenis_pasien: "",
			});

			$('#tb_terapi_racikan').datagrid('load', {
				rawat_id: "",
				reg_id: "",
				cust_usr_id: "",
				reg_jenis_pasien: "",
			});

			$('#dg').edatagrid('reload');
		}

		//Sampai di Poli
		function sampai() {
			var row = $('#dg').datagrid('getSelected');
			var rowIndex = $("#dg").datagrid("getRowIndex", row);
			if (row.reg_status == 'E0') {
				$.get('get_irj.php', {
					reg_id: row.reg_id
				}, function(result) {
					//insert awal (PK FK) ke folio						
					var dataString = 'isNewRecord=false' +
					'&id_dep=' + result[0].id_dep +
					'&id_reg=' + result[0].reg_id +
					'&cust_usr_id=' + result[0].id_cust_usr +
					'&id_pembayaran=' + result[0].id_pembayaran +
					'&id_poli=' + result[0].id_poli +
					'&id_dokter=' + result[0].usr_id +
					'&id_reg_jenis_pasien=' + result[0].reg_jenis_pasien;
					//alert (dataString);return false;
					$.ajax({
						type: "POST",
						url: "proses_sampai.php",
						data: dataString,
						success: function() {
							$('#dg').edatagrid('reload');
						}
					});
				}, 'json');

				setTimeout(function() {
					$('#dg').datagrid('selectRow', rowIndex);
					setTimeout(function() {
						layani();
					}, 500);
				}, 500);
			} else if (row.reg_status != 'E0') {
				alert('Pasien Sudah Sampai di Poli');
			}
		}
		//cetak resume lab
		function cetak_resume_lab() {
			var row = $('#dg9').datagrid('getSelected');
			if (row) {
				var url = '../input_hasil_lab_irj/input_hasil_lab_cetak2.php?read=1&id_reg=' + row.id_reg;
				var printWindow = window.open(url);
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}


		//fungsi cetak spb
		function cetakspb() {
			var row = $('#dg').datagrid('getSelected');
			if (row) {
				var url = 'cetak_spb.php?id_reg=' + row.reg_id + '&pembayaran_id=' + row.id_pembayaran;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak tagihan
		function cetak_tagihan() {
			var row = $('#dg').datagrid('getSelected');
			if (row) {
				var url = 'cetak_tagihan.php?id_reg=' + row.reg_id + '&pembayaran_id=' + row.id_pembayaran;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak resume
		function cetak_resume() {
			var row = $('#dgr').datagrid('getSelected');
			if (row) {
				var url = 'cetak_resume.php?id_resume=' + row.resume_id + '&id_reg=' + row.id_reg;
				var printWindow = window.open(url);
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		function cetak_resume_pemeriksaan() {
			var row = $('#dghp').datagrid('getSelected');
			if (row) {
				var url = 'cetak_resume_pemeriksaan.php?id_rawat=' + row.rawat_id + '&id_reg=' + row.id_reg;
				var printWindow = window.open(url);
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		function add_diagnosa() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_diagnosa').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}

		function add_diagnose() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_diagnose').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}

		function add_procedure() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_procedure').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}

		function add_procedures() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_procedures').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}

		function add_terapi() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_terapi').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}

		function add_terapi_racikan() {
			var regId = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			if (regId != "") {
				$('#tb_terapi_racikan').edatagrid('addRow', {
					index: 0,
					row: {
						id_reg: regId,
						id_rawat: id_rawat
					}
				});
			} else {
				alert(notif1);
			}
		}
	</script>

	<script type="text/javascript">
		function newRow() {

			var dt = {
				isNewRecord: true,
			}

			var index = $('#komposisi').datagrid('getRows');

			$('#komposisi').datagrid('appendRow', dt).datagrid('collapseRow', ((index.length) - 1)).datagrid('expandRow', ((index.length) - 1));
			$('#komposisi').datagrid('expandRow', index);
			$('#komposisi').datagrid('selectRow', index);
			$('#komposisi').datagrid('fixDetailRowHeight', index);




		}

		function cancelKomposisi(index) {
			var row = $('#komposisi').datagrid('getRows')[index];
			if (row.isNewRecord) {
				$('#komposisi').datagrid('deleteRow', index);
			} else {
				$('#komposisi').datagrid('collapseRow', index);
			}
		}

		function saveKomposisi(index) {
			var row = $('#komposisi').datagrid('getRows')[index];
			var id_reg = $('#regId').val();
			var id_rawat = $('#rawat_id').val();
			//var rawat_terapi_racikan_id =  row.rawat_terapi_racikan_id;
			var url = row.isNewRecord ? 'simpan_komposisi.php?id_rawat=' + id_rawat + '&rawat_terapi_racikan_id=' + rawat_terapi_racikan_id : 'update-komposisi.php?func=update&id=1';
			$('#komposisi').datagrid('getRowDetail', index).find('form').form('submit', {
				url: url,
				onSubmit: function(param) {
					param.rawat_terapi_racikan_id = rawat_terapi_racikan_id;
					param.rawat_id = id_rawat;
					return $(this).form('validate');
				},
				success: function(data) {
					data = eval('(' + data + ')');
					data.isNewRecord = false;

					$('#komposisi').datagrid('collapseRow', index);
					$('#komposisi').datagrid('updateRow', {
						index: index,
						row: data
					});

					$('#komposisi').datagrid('reload');
				}
			});
		}

		function destroykomposisi() {
			var row = $('#komposisi').datagrid('getSelected');
			if (row) {
				$.messager.confirm('Konfirmasi', 'Anda Yakin?', function(r) {
					if (r) {
						var index = $('#komposisi').datagrid('getRowIndex', row);
						$.get('hapus_komposisi.php?id=' + row.rawat_item_id, {}, function() {
							$('#komposisi').datagrid('deleteRow', index);
						});
					}
				});
			}
		}

		function cetakb() {
			var row = $('#dg').datagrid('getSelected');
			if (row) {
				var url = '../pemeriksaan_irna/cetak_barcode.php?id_reg=' + row.reg_id + '&id=' + row.id_cust_usr;
				var printWindow = window.open(url, 'load', 'left=200, top=100, toolbar=0, resizable=0');
				printWindow.addEventListener('load', function() {
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//   ++++++++++++++++++++++++++++input tindakan laboratorium+++++++++++++++++++++++++++++++++++


		var tb_tindakan = $('#tb_tindakan').edatagrid();

		tb_tindakan.edatagrid({
			url: 'get_kategori_tindakan.php',
			view: detailview,
			detailFormatter: function(index, row) {
				return "<div style=\"padding:2px\"><table id=\"detail-" + index + "\"></table></div>";
			},
			onExpandRow: function(index, row) {
				$("#detail-" + index).datagrid({
					url: "get_tindakan.php?id=" + row.kategori_tindakan_id,
					fitColumns: true,
					singleSelect: true,
					rownumbers: true,
					loadMsg: "Tunggu Sebentar",
					height: "auto",
					checkOnSelect: true,
					selectOnCheck: true,
					singleSelect: false,
					columns: [
					[{
						field: "ck",
						checkbox: "true",
						width: 15,
						align: "center"
					},

					{
						field: "biaya_tarif_id",
						title: "Nama",
						width: 50,
						align: "center",
						formatter: function(value, row) {
							return row.biaya_nama;
						}
					},
					{
						field: "biaya_total",
						title: "Biaya",
						width: 10,
						align: "right"
					}
					]
					],


					onResize: function() {
						$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
					},
					onLoadSuccess: function() {
						setTimeout(function() {
							$("#tb-tindakan").datagrid("fixDetailRowHeight", index);
						}, 0);
					}
				});
				$("#tb_tindakan").datagrid("fixDetailRowHeight", index);
			}
		});

		// +++++++++++++++++++++++++++++Simpan tindakan+++++++++++++++++++++++++
		var d = new Date();
		var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
		var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
		var notif1 = "Pilih pasien dahulu.";
		var notif2 = "Pilih tindakan dahulu.";

		function simpan_tindakan() {
			var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
			var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
			var id_reg = $('#registId').val();
			var id_dokter_pengirim = $('#dokter').val();
			var totalRow = $("#tb_tindakan").edatagrid("getRows").length;
			var data = [];
			var idx = 0;
			if (id_reg != "") {
				for (var i = 0; i < totalRow; i++) {
					//data[i] = $("#detail-" + i).datagrid("getSelections");
					if ($("#detail-" + i).edatagrid().length > 0) {
						var tes = $("#detail-" + i).datagrid("getChecked");
						if (tes.length > 0) {
							data[idx] = $("#detail-" + i).datagrid("getChecked");
							idx += 1;
						}
					}
				}
				for (var i = 0; i < data.length; i++) {
					for (var j = 0; j < data[i].length; j++) {
						var biaya_tarif_id = data[i][j].biaya_tarif_id;
						$.post('proses_folio_lab.php', {
							id_biaya_tarif: biaya_tarif_id,
							fol_jumlah: '1',
							tindakan_tanggal: tanggal,
							tindakan_waktu: waktu,
							id_reg: id_reg,
							id_dokter_pengirim: id_dokter_pengirim,
							isNewRecord: 'true'
						}, function(result) {
							$('#tb_tindakan').edatagrid('reload');
							$('#dg1').edatagrid('reload');
						});

					}
				}
			} else {
				alert(notif1);
			}
		}

		// +++++++++++++++++++++++++++proses hitung komposisi+++++++++++++++++++++++++++++++++++++++
		function prosesHitungKomposisi() {
			$.post('proses_hitung_komposisi.php', {
				rawat_terapi_racikan_id: rawat_terapi_racikan_id,
				id_item: $('#id_penjualan').val()
			}, function(result) {});
		}
		// ++++++++++++++++++++++++++++++Simpan tindakakn radiologi ++++++++++++++++++++++++++++++++++++
		function simpan_tindakan_rad() {
			var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
			var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
			var id_reg = $('#registId').val();
			var id_dokter_pengirim = $('#dokter_pengirim').val();
			var totalRow = $("#tb_tindakan_rad").edatagrid("getRows").length;
			var data = [];
			var idx = 0;
			if (id_reg != "") {
				for (var i = 0; i < totalRow; i++) {
					//data[i] = $("#detail-" + i).datagrid("getSelections");
					if ($("#detail-" + i).edatagrid().length > 0) {
						var tes = $("#detail-" + i).datagrid("getChecked");
						if (tes.length > 0) {
							data[idx] = $("#detail-" + i).datagrid("getChecked");
							idx += 1;
						}
					}
				}
				for (var i = 0; i < data.length; i++) {
					for (var j = 0; j < data[i].length; j++) {
						var biaya_tarif_id = data[i][j].biaya_tarif_id;
						$.post('proses_folio_rad.php', {
							id_biaya_tarif: biaya_tarif_id,
							fol_jumlah: '1',
							tindakan_tanggal: tanggal,
							tindakan_waktu: waktu,
							id_reg: id_reg,
							id_dokter_pengirim: id_dokter_pengirim,
							isNewRecord: 'true'
						}, function(result) {
							$('#tb_tindakan_rad').edatagrid('reload');
							$('#dg1').edatagrid('reload');
						});

					}
				}
			} else {
				alert(notif1);
			}
		}
		function simpan_tindakan_lab() {
			var tanggal = d.getDate() + "-" + (d.getMonth() + 1) + "-" + d.getFullYear();
			var waktu = d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
			var id_reg = $('#registId').val();
			var id_dokter_pengirim = $('#dokter_pengirim').val();
			var totalRow = $("#tb_tindakan_lab").edatagrid("getRows").length;
			var data = [];
			var idx = 0;
			if (id_reg != "") {
				for (var i = 0; i < totalRow; i++) {
					//data[i] = $("#detail-" + i).datagrid("getSelections");
					if ($("#detail-" + i).edatagrid().length > 0) {
						var tes = $("#detail-" + i).datagrid("getChecked");
						if (tes.length > 0) {
							data[idx] = $("#detail-" + i).datagrid("getChecked");
							idx += 1;
						}
					}
				}
				for (var i = 0; i < data.length; i++) {
					for (var j = 0; j < data[i].length; j++) {
						var biaya_tarif_id = data[i][j].biaya_tarif_id;
						$.post('proses_folio_lab.php', {
							id_biaya_tarif: biaya_tarif_id,
							fol_jumlah: '1',
							tindakan_tanggal: tanggal,
							tindakan_waktu: waktu,
							id_reg: id_reg,
							id_dokter_pengirim: id_dokter_pengirim,
							isNewRecord: 'true'
						}, function(result) {
							$('#tb_tindakan_lab').edatagrid('reload');
							$('#dg1').edatagrid('reload');
						});

					}
				}
			} else {
				alert(notif1);
			}
		}
		//cetak tindakan lab
		function cetak_tindakan_lab() {
			var regId = $('#regIdRujukan').val();
			var regIdUtama = $('#regId').val();
			var dokter = $('#dokter').val();
			if (regId) {
				var url = 'cetak_lab_tindakan.php?id_reg=' + regId + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu + '&dokter=' + dokter + '&id_reg_utama=' + regIdUtama;
				var printWindow = window.open(url);
				printWindow.addEventListener('load', function() {}, true);
			}
		}

		//cetak tindakan rad
		function cetak_tindakan_rad() {
			var regId = $('#regIdRad').val();
			var dokter = $('#dokter').val();
			if (regId) {
				var url = 'cetak_rad_tindakan.php?id_reg=' + regId + '&reg_tanggal=' + REGtanggal + '&reg_waktu=' + REGwaktu + '&dokter=' + dokter;
				var printWindow = window.open(url);
				printWindow.addEventListener('load', function() {}, true);
			}
		}

		$("button#showHistoryObgyn").click(function(){
			var id_poli = $(this).data("idpoli");
			var id_cust_usr = $(this).data("idcust");

			if(id_poli.length > 0 && id_cust_usr.length > 0){
				window.open("../bpjs/tampilan_bpjs_cus.php?id_poli="+id_poli+"&cust_usr_id="+id_cust_usr);
			}
		});
	</script>
	
	<!-- <script type="text/javascript">
window.open('', 'cetak_usg');
document.getElementById('form_obgynn').submit();
</script> -->

</body>

</html>