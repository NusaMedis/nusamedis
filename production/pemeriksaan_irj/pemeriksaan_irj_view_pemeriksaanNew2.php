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
	$(document).ready(function(){
		var a = "<?= $_GET['id_reg_pasien'] ?>";
				var b = "<?= $_GET['registrasi_status'] ?>";

 layani (a,b); 

});
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

					<!-- insert ke folio sebaai data awal -->
					<form method="POST" id="form_pemeriksaan" action="proses_registrasi.php">
						<input id="regId" type="hidden" name="regId">
						<input id="regIdRujukan" type="hidden">
						<input id="regIdLab" type="hidden">
						<input id="regIdRad" type="hidden">
						<input id="registId" type="hidden" name="registId">
						<input id="reg_tanggal" type="hidden" name="reg_tanggal">
						<input id="fol_id" type="hidden" name="fol_id">
						<input id="biaya_tarif_id" type="hidden" name="biaya_tarif_id">
						<input id="cust_usr_id" type="hidden" name="cust_usr_id">
						<input id="id_poli" type="hidden" name="id_poli">
						<input id="dokter_pengirim" type="hidden" name="dokter_pengirim">
						<input id="rawat_id" type="hidden" name="rawat_id">
						<input id="reg_status" type="hidden" name="reg_status">
						<input id="id_penjualan" type="hidden">
						<!-- BARIS 1 -->
						<div class="row">
							<!-- KOLOM KIRI -->
							<div class="col-md-4 col-sm-4 col-xs-12">
								<!-- == Hasil dari TABEL BAWA => set ke element berdasar id == -->
								<div class="x_panel">
									<div class="x_content">
										<table class="col-md-12 col-sm-12 col-md-12">
											<input type="hidden" name="foto" id="foto">
											<tr>
												<th width="150px">No. RM</th>
												<td width="15px">: </td>
												<td><input id="norm" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr>
												<th>Nama Pasien</th>
												<td>: </td>
												<td><input id="nmps" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr>
												<th>Alamat</th>
												<td>: </td>
												<td><input id="alps" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr>
												<th>Klinik</th>
												<td>: </td>
												<td><input id="klinik" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr>
												<th>Sebab Sakit</th>
												<td>: </td>
												<td><input id="reg_sebab_sakit" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr hidden>
												<th>Shift Kedatangan</th>
												<td>: </td>
												<td><input id="reg_shift" required class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
											</tr>
											<tr>
												<th>Cara Bayar</th>
												<td>: </td>
												<td><input id="reg_jenis_pasien" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
												<input type="hidden" name="jenis_pasien" id="jenis_pasien">
											</tr>
											<tr>
												<th style="color: red; visibility: hidden;" id="rawat_alergi" >Alergi</th>
												<td style="color: red; visibility: hidden;" id="rawat_alergi" >: </td>
												<td id="rawat_alergi" style="visibility: hidden;"><input id="rawat_alergi" class="no-border col-md-12 col-sm-12 col-md-12" style="color: red;" readonly></td>
												<input type="hidden" name="cust_usr_alergi" id="cust_usr_alergi">
											</tr>
											<tr>
												<th style="color: red; visibility: hidden;" id="diagKhusus" >Diagnosa Khusus</th>
												<td style="color: red; visibility: hidden;" id="diagKhusus" >: </td>
												<td id="diagKhusus" style="visibility: hidden;"><input id="diagKhusus" class="no-border col-md-12 col-sm-12 col-md-12" style="color: red;" readonly></td>
												
											</tr>
										</table>
									</div>
								</div>
							</div>


							<!-- KOLOM KANAN -->
							<div class="col-md-8 col-sm-8 col-xs-12">
								<div class="x_panel">
									<div class="x_content">
										<div class="col-md-6 col-sm-6 col-xs-12">
											<input id="dokter" name="dokter" class="easyui-combobox" style="width:100%;" data-options="
											url: 'get_dokterdpjp.php',
											valueField: 'usr_id',
											textField: 'usr_name',
											label: 'Dokter:',
											labelPosition: 'top',
											panelHeight: 'auto',
											onSelect: function(value){ 
											var usr_id = value.usr_id;
											var reg_id = $('#regId').val(); 
											if(reg_id != ''){
											$.post('update_dokter.php',{usr_id:usr_id, reg_id:reg_id },function(result){
											if (result.success){
											//alert(result.success);
										}
									},'json');
								}
							}
							">
						</div>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input id="kondisi" name="reg_status_kondisi" class="easyui-combobox" style="width:100%;" data-options="
							url: 'get_kondisi.php',
							valueField: 'kondisi_akhir_pasien_id',
							textField: 'kondisi_akhir_pasien_nama',
							label: 'Kondisi Akhir:',
							labelPosition: 'top',
							panelHeight: 'auto',
							onSelect: function(value){
							var v = value.kondisi_akhir_pasien_id
							if ( v == '3' || v == '2' ){
							var url = 'get_kondisi_deskripsi.php?id='+v;
							$('#kondisi_deskripsi').combobox('reload', url);
							$('#div_kondisi_deskripsi').css('display','block');
							//alert( v ); 
						} else {
						$('#div_kondisi_deskripsi').css('display','none');
					} 
				}
				" required>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">

				<input id="jenis_kb_id" name="jenis_kb_id" class="easyui-combobox" style="width:100%;" data-options="
				url: 'get_jenis_kb.php',
				valueField: 'jenis_kb_id',
				textField: 'jenis_kb_nama',
				label: 'Jenis KB:',
				labelPosition: 'top',
				panelHeight: 'auto'
				">
			</div>
			<div hidden class="col-md-6 col-sm-6 col-xs-12">
				<input id="tingkat_kegawatan" name="tingkat_kegawatan" class="easyui-combobox" style="width:100%;" data-options="
				url: 'get_kegawatan.php',
				valueField: 'tingkat_kegawatan_id',
				textField: 'tingkat_kegawatan_nama',
				label: 'Tingkat Kegawatan:',
				labelPosition: 'top',
				panelHeight: 'auto',
				">
			</div>
			<div id="div_kondisi_deskripsi" class="col-md-6 col-sm-6 col-xs-12" style="display: none;">
				<input id="kondisi_deskripsi" name="reg_status_kondisi_deskripsi" class="easyui-combobox" style="width:100%;" data-options="
				valueField: 'kondisi_akhir_deskripsi_id',
				textField: 'kondisi_akhir_deskripsi_nama',
				label: 'Kondisi Akhir Deskripsi:',
				labelPosition: 'top',
				panelHeight: 'auto',
				">
			</div>
		</div>

	</div>
</div>
<!-- <input type="text" id="poliFilter" name="poliFilter"> -->
<input name="btn" id="btn" class="btn btn-default col-md-3 pull-right" type="submit" value="Selesai">
<input id="btnReset" class="btn btn-default pull-right" style="display:none;" value="Batal" onclick="window.location.reload()">
</div>
</div>
</form>

<!-- row 2 == Data View Pasien -->
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">

		<!-- tab-->
		<div class="easyui-tabs" style="width:100%;">
			<!-- tab 1 -->
			<div title="Tindakan" style="padding:5px">
				<table id="dg1" style="width:100%;" toolbar="#toolbar1" pagination="false" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
					<thead>
						<tr>
							<th data-options="field:'tindakan_tanggal',width:50
							,editor:{type:'text'}
							">Tanggal</th>

							<th data-options="field:'tindakan_waktu',width:50
							,editor:{type:'text'}
							">Waktu</th>

							<th data-options="field:'id_biaya_tarif',width:300,
							formatter:function(value,row){
							return row.biaya_nama;
						},
						editor:{
						type:'combogrid',
						options:{
						panelWidth:500,
						idField:'biaya_tarif_id',
						textField:'biaya_nama',
						url:'get_biaya.php',
						mode: 'remote',
						delay: 200,
						onBeforeLoad:function(param){
						param.id_poli = document.getElementById('id_poli').value,
						param.jenis_pasien = document.getElementById('jenis_pasien').value;
					}, 
					onSelect:function(){
					simpan_folio();
				},
				columns:[[
				{field:'biaya_nama',title:'Nama',width:300},
				{field:'biaya_total',title:'Biaya',width:100,options:{precision:3}},
				]],
				required:true
			}
		}">Tindakan</th>

		<th data-options="field:'fol_jumlah',width:30
		,editor:{type:'numberspinner'}
		">Jumlah</th>

		<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
	</tr>
</thead>
</table>
<div id="toolbar1">
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_folio();">Baru</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg1').edatagrid('cancelRow')">Cancel</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_folio()">Hapus</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_folio()">Simpan</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg1').edatagrid('reload')">refresh</a>
</div>
</div>

<!-- tab 2 -->
<div title="Transfer" style="padding:5px">
	<!-- <div title="Rujukan" style="padding:5px"> -->
	<table id="dg2" style="width:100%;" toolbar="#toolbar2" pagination="false" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
		<thead>
			<tr>
				<th data-options="field:'reg_tanggal',width:50">Tanggal</th>
				<th data-options="field:'reg_waktu',width:50">Waktu</th>
				<th data-options="field:'poli_id',width:75,
						formatter:function(value,row){
							return row.poli_nama;
						},">Klinik tujuan
				</th>
				<th data-options="field:'usr_id',width:200,
						formatter:function(value,row){
							return row.usr_name;
						}," >Dokter Poli
				</th>
				<th data-options="field:'reg_keterangan',width:175
						,editor:{type:'text'}
						">Catatan Dokter
				</th>
			</tr>
		</thead>
	</table>
	<div id="toolbar2">
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_rujuk();">Baru</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg2').edatagrid('cancelRow')">Cancel</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_rujuk()">Simpan</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg2').edatagrid('reload')">refresh</a>
	</div>
</div>

						
<!-- tab 3 -->
<div title=" " style="padding:5px">
	<div class="form-horizontal form-label-left">
		

		<!--awal <form id="form_obgynn" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>"> -->
		
	</div>
	</div>

	<div title=" " style="padding:5px">
		
	</div>
							
	<div title=" " style="padding:5px">
		<?php
		// require_once("obgyn.php");
		?>
	</div>
	<div title=" " style="padding:5px">
		<?php
		// require_once("anak.php");
		?>
	</div>
	<div title=" " style="padding:5px">
		<?php
		// require_once("dalam.php");
		?>
	</div>

	<div title="Rencana Operasi" style="padding:5px">
		<table id="dg6" style="width:100%;" autoSave="false" toolbar="#toolbar6" idField="preop_id" rownumbers="true" fitColumns="true" singleSelect="true">
			<thead>
				<tr>
					<th data-options="field:'preop_waktu',width:70
					,editor:{type:'datetimebox', options:{formatter:formatters, parser:parsers, required:true}}
					">Waktu Order</th>
					<th data-options="field:'preop_tanggal_jadwal',width:70
					,editor:{type:'datetimebox', options:{formatter:formatters, parser:parsers, required:true}}
					">Rencana Operasi</th>
					<th data-options="field:'preop_selesai_jadwal',width:70
					,editor:{type:'datetimebox', options:{formatter:formatters, parser:parsers, required:true}}
					">Rencana Selesai</th>
					<th data-options="field:'usr_name',width:70
					,editor:{ type:'combobox', options:{ valueField:'usr_id', textField:'usr_name', url:'get_dokterPelaksana.php?id_rol_jabatan=D', panelHeight: '100px', required:true } }
					">Dokter</th>
				</tr>
			</thead>
		</table>
		<div id="toolbar6">
			<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_preop();">Baru</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg6').edatagrid('cancelRow')">Batal</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="$('#dg6').edatagrid('destroyRow')">Hapus</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="$('#dg6').edatagrid('saveRow')">Simpan</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg6').edatagrid('reload')">refresh</a>
		</div>
	</div>
	<!-- /rencana operasi -->

	<!-- tab 1 -->
	<div title="Hasil Radiologi" style="padding:5px">
		<table id="dgr" style="width:100%;"
			toolbar="#toolbarr" idField="fol_id"
			rownumbers="true" fitColumns="true" singleSelect="true" 
			pagination="true" pageSize="10"
		>
			<thead>
				<tr>
					<th data-options="field:'tindakan_tanggal',width:50,editor:{type:'text'}">Tanggal</th>
					<th data-options="field:'tindakan_waktu',width:50,editor:{type:'text'}">Waktu</th>
					<th data-options="field:'id_biaya_tarif',width:300,
						formatter:function(value,row){
							return row.biaya_nama;
						},
						editor:{
							type:'combogrid',
							options:{
								panelWidth:500,
								idField:'biaya_tarif_id',
								textField:'biaya_nama',
								url:'get_biaya.php',
								mode: 'remote',
								delay: 200,
								onBeforeLoad:function(param){
									param.id_poli = document.getElementById('id_poli').value;
								}, 
								columns:[[
									{field:'biaya_nama',title:'Nama',width:300},
									{field:'biaya_total',title:'Biaya',width:100,options:{precision:3}},
								]],
								required:true
							}
						}"
					>Tindakan</th>
					<th data-options="field:'fol_jumlah',width:30,editor:{type:'numberspinner'}">Jumlah</th>
					<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>										
				</tr>
			</thead>
		</table> 
		<div id="toolbarr">
			<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_resume()">Lihat Resume</a>
		</div>
	</div>	

	<!-- tab 8 -->
	<div title="Hasil Lab" style="padding:5px">

	<table id="dg9" style="width:100%;" toolbar="#toolbar9" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
		<thead>
			<tr>
				<th data-options="field:'tanggal',width:500,fixed:true,editor:{type:'text'}">Tanggal</th>
				<th data-options="field:'waktu',width:500,fixed:true,editor:{type:'text'}">Waktu</th>
				<th hidden data-options="field:'id_reg',width:30"></th>
			</tr>
		</thead>
	</table>
	<div id="toolbar9">
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_resume_lab()">Lihat Resume</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg9').edatagrid('reload')">refresh</a>
	</div>
</div>



<!-- tab 10 -->
<div title="Penjualan" style="padding:5px">
	<table id="dg10" style="width:100%;" toolbar="#toolbar10" pagination="false" idField="fol_id" rownumbers="true" fitColumns="true" singleSelect="true" pagination="true" pageSize="10">
		<thead>
			<tr>
				<th data-options="
					field:'item_id',width:300,
					formatter:function(value,row){
						return row.item_nama;
					},
					editor:{
						type:'combogrid',
						options:{
							panelWidth:500,
							idField:'item_id',
							textField:'item_nama',
							url:'get_obat.php',
							mode: 'remote',
							delay: 200,
							onBeforeLoad:function(param){
								param.id_poli = document.getElementById('id_poli').value
							}, 
							columns:[[
								{field:'item_nama',title:'Nama',width:300},
								{field:'item_harga_jual',title:'Biaya',width:100,options:{precision:3}},
							]],
							required:true,
							onSelect:get_harga,
						}
					}">Nama Obat</th>
				<th data-options="field:'fol_jumlah',width:30,editor:{type:'text'}">Jumlah</th>
				<th data-options="field:'satuan_nama',width:30,editor:{type:'text'}">Satuan</th>
				<th data-options="field:'biaya',width:30,editor:{type:'numberspinner'}">Harga</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<div id="toolbar10">
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_obat();">Baru</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg10').edatagrid('cancelRow')">Cancel</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_obat()">Hapus</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_obat()">Simpan</a>
		<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg10').edatagrid('reload')">refresh</a>
	</div>
</div>

<!-- tab 3 -->
<div title="Pemeriksaan" style="padding:5px">
	<div class="form-horizontal form-label-left">
		<input id="regId2"  name="regId" type="text" hidden>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Anamnesa</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="anamnesa" name="anamnesa" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Observasi</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="observasi" name="observasi" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Konsultasi</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="konsultasi" name="konsultasi" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Pemeriksaan</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="pemeriksaan_umum" name="pemeriksaan_umum" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Diagnosa</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="pencatatan_diagnosa" name="pencatatan_diagnosa" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-2 col-sm-2 col-xs-12">Resume Medis</label>
			<div class="col-md-8 col-sm-8 col-xs-12">
			  <textarea id="resume_medis" name="resume_medis" class="form-control"></textarea>
			</div>
		</div>
		<div class="item form-group">
			<label class="control-label col-md-8 col-sm-8 col-xs-12"></label>
			<div class="col-md-2 col-sm-2 col-xs-12">
			  <input id="btn_pemeriksaan" class="btn btn-default form-control" type="button" value="Simpan">
			</div>
		</div>
  	</div>
</div>


</div>

<!-- Diagnosa -->
<div class="clearfix"><br></div>
<table id="tb_diagnose" title="Diagnosa" style="width:100%;" toolbar="#toolbar_diag" idField="rawat_diagnosa_id" rownumbers="true" fitColumns="true" singleSelect="true">
	<thead>
		<tr>
			<th data-options="field:'diagnosa_id',width:50,
			formatter:function(value,row){
			return row.diagnosa_nomor+' - '+row.diagnosa_deskripsi;
		}" editor="{type:'combobox',
		options:{
		required:true,
		url:'get_diagnosa.php', 
		valueField:'diagnosa_id', 
		textField:'diagnosa_nama', 
		textField:'diagnosa_deskripsi', 
		mode: 'remote', 
		method: 'GET', 
		onBeforeLoad:function(param){
		param.id_poli = document.getElementById('id_poli').value
	},
	formatter:function(row){
	return row.diagnosa_nomor+' - '+row.diagnosa_deskripsi;
},


}
}">Diagnosa</th>
<th data-options="field:'rawat_icd_status_id',width:50,
formatter:function(value,row){
return row.rawat_icd_status;
}"
editor="{type:'combobox',options:{required:true, url:'get_status.php',
valueField:'rawat_icd_status_id', textField:'rawat_icd_status', mode: 'remote', method: 'GET',formatter:function(row){
return row.rawat_icd_status;
}}}">Status Diagnosa</th>
                        <!-- <th data-options="field:'none',width:50">
                        	
                        </th> -->
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <div id="toolbar_diag">
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_diagnose()">Baru</a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#tb_diagnose').edatagrid('destroyRow')">Hapus</a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_diagnose()">Simpan</a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_diagnose').edatagrid('cancelRow')">Cancel</a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_diagnose').edatagrid('reload')">Refresh</a>
            </div>
            <!-- End Diagnosa -->

            <!-- Procedure -->
            <div class="clearfix"><br></div>
            <table id="tb_procedures" title="Procedure" style="width:100%;" toolbar="#toolbar_pro" idField="rawat_procedure_id" rownumbers="true" fitColumns="true" singleSelect="true">
            	<thead>
            		<tr>
            			<th data-options="field:'procedure_id',width:50,
            			formatter:function(value,row){
            			return row.procedure_nomor+' - '+row.procedure_short_desc;
            		}" editor="{
            		type:'combobox',
            		options:{
            		required:true, 
            		url:'get_procedure.php',
            		valueField:'procedure_id', 
            		textField:'procedure_nama', 
            		textField:'procedure_short_desc',
            		mode: 'remote',
            		method: 'GET',
            		formatter:function(row){
            		return row.procedure_nomor_tanpa_titik+' - '+row.procedure_short_desc;
            	},

            }
        }">Procedure</th>
    </tr>
</thead>
<tbody>

</tbody>
</table>
<div id="toolbar_pro">
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_procedures()">Baru</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#tb_procedures').edatagrid('destroyRow')">Hapus</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_procedures()">Simpan</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_procedures').edatagrid('cancelRow')">Cancel</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_procedures').edatagrid('reload')">Refresh</a>
</div>
<!-- End Procedure -->

<!-- Perawatan Terapi -->
<div class="clearfix"><br></div>
<input type="hidden" name="terapi_check" id="terapi_check">
<table id="tb_terapi" title="Perawatan Terapi" style="width:100%;" toolbar="#toolbar_terapi" idField="rawat_item_id" rownumbers="true" fitColumns="true" singleSelect="true" >
	<thead>
		<tr>
			<th data-options="field:'item_id',width:130,
				formatter:function(value,row){
				return row.item_nama;
				}" 
				editor="{
					type:'combobox',
					options:{
						required:true, 
						url:'get_item.php',
						valueField:'item_id', 
						textField:'item_nama', 
						mode: 'remote', 
						method: 'GET',
						onSelect: function (isi,value){
							$('#terapi_check').val(isi.stok_apotik);
						}
					},
				}"
			>Nama Obat</th>
			<th data-options="field:'terapi_jumlah_item',width:35,
				editor:{
					type:'numberspinner',
					options:{
						onChange: function(value){
							var stok = $('#terapi_check').val();
					    console.log('The value has been changed to ' + value);
					    if(value*1 > stok*1) {
					    	var row = $('#tb_terapi').datagrid('getSelected');
	              rowIndex = $('#tb_terapi').datagrid('getRowIndex',row);
	              rows = $('#tb_terapi').datagrid('getRows');
	              var ed = $('#tb_terapi').datagrid('getEditor', {index:rowIndex,field:'terapi_jumlah_item'});
	              var text = $(ed.target).numberspinner('setValue', 0);

	              $.messager.show({
									title: 'Error',
									msg: 'Quantity melebihi stok yang tersedia! Stok tersedia saat ini : ' + stok
								});
					    }
					  },
					},
				},
			">Jumlah</th>
			<th data-options="field:'petunjuk_id',width:30,
				formatter:function(value,row){
				return row.petunjuk_nama;
				}" editor="{type:'combobox',options:{url:'get_dosis.php',
			valueField:'petunjuk_id', textField:'petunjuk_nama', mode: 'remote', method: 'GET'}}">Aturan Minum</th>
			<th data-options="field:'aturan_pakai_id',width:30,
				formatter:function(value,row){
				return row.aturan_pakai_nama;
				}" editor="{type:'combobox',options:{url:'get_aturan_pakai.php',
			valueField:'aturan_pakai_id', textField:'aturan_pakai_nama', mode: 'remote', method: 'GET'}}">Aturan Pakai</th>
			<th data-options="field:'aturan_minum_id',width:30,
				formatter:function(value,row){
				return row.aturan_minum_nama;
				}" editor="{type:'combobox',options:{url:'get_aturan_minum.php',
			valueField:'aturan_minum_id', textField:'aturan_minum_nama', mode: 'remote', method: 'GET'}}">Dosis</th>
			<th data-options="field:'jam_aturan_pakai_id',width:40,
				formatter:function(value,row){
				return row.jam_aturan_pakai_nama;
				}" editor="{type:'combobox',options:{url:'get_jam_aturan_pakai.php',
			valueField:'jam_aturan_pakai_id', textField:'jam_aturan_pakai_nama', mode: 'remote', method: 'GET'}}">Jam Aturan Pakai</th>
			<th data-options="field:'keterangan',width:20
				,editor:{type:'text'}
			">Ket</th>
		</tr>
	</thead>
</table>
<div id="toolbar_terapi">
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_terapi()">Baru</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#tb_terapi').edatagrid('destroyRow')">Hapus</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#tb_terapi').edatagrid('saveRow')">Simpan</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_terapi').edatagrid('cancelRow')">Cancel</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_terapi').edatagrid('reload')">Refresh</a>
</div>
<!-- End Perawatan Terapi -->

<!-- Perawatan Terapi Racikan -->
<div class="clearfix"><br></div>

<table id="tb_terapi_racikan" title="Perawatan Terapi Racikan" style="width:100%;" toolbar="#toolbar_terapi_racikan" idField="rawat_terapi_racikan_id" rownumbers="true" fitColumns="true" singleSelect="true">
	<thead>
		<tr>
			<th data-options="field:'jenis_racikan_id',width:30,
				formatter:function(value,row){
				return row.jenis_racikan_nama;
				}" editor="{type:'combobox',options:{url:'get_jenis_racikan.php',
			valueField:'jenis_racikan_id', textField:'jenis_racikan_nama', mode: 'remote', method: 'GET'}}">Jenis Racikan</th>
			<th data-options="field:'rawat_terapi_racikan_jumlah',width:20
				,editor:{type:'text'}
			">Jumlah</th>
			<th data-options="field:'satuan_id',width:30,
				formatter:function(value,row){
				return row.satuan_nama;
				}" editor="{type:'combobox',options:{url:'get_satuan_jadi.php',
			valueField:'satuan_id', textField:'satuan_nama', mode: 'remote', method: 'GET'}}">Satuan Jadi</th>
			<th data-options="field:'petunjuk_id',width:30,
				formatter:function(value,row){
				return row.petunjuk_nama;
				}" editor="{type:'combobox',options:{url:'get_dosis.php',
			valueField:'petunjuk_id', textField:'petunjuk_nama', mode: 'remote', method: 'GET'}}">Aturan Minum</th>
			<th data-options="field:'aturan_pakai_id',width:30,
				formatter:function(value,row){
				return row.aturan_pakai_nama;
				}" editor="{type:'combobox',options:{url:'get_aturan_pakai.php',
			valueField:'aturan_pakai_id', textField:'aturan_pakai_nama', mode: 'remote', method: 'GET'}}">Aturan Pakai</th>
			<th data-options="field:'aturan_minum_id',width:30,
				formatter:function(value,row){
				return row.aturan_minum_nama;
				}" editor="{type:'combobox',options:{url:'get_aturan_minum.php',
			valueField:'aturan_minum_id', textField:'aturan_minum_nama', mode: 'remote', method: 'GET'}}">Dosis</th>
			<th data-options="field:'jam_aturan_pakai_id',width:40,
				formatter:function(value,row){
				return row.jam_aturan_pakai_nama;
				}" editor="{type:'combobox',options:{url:'get_jam_aturan_pakai.php',
			valueField:'jam_aturan_pakai_id', textField:'jam_aturan_pakai_nama', mode: 'remote', method: 'GET'}}">Jam Aturan Pakai</th>
			<th hidden="" field="rawat_terapi_racikan_id">ID</th>
		</tr>
	</thead>
</table>
<div id="toolbar_terapi_racikan">
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_terapi_racikan()">Baru</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('destroyRow')">Hapus</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('saveRow')">Simpan</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('cancelRow')">Cancel</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_terapi_racikan').edatagrid('reload')">Refresh</a>
</div>
<!-- END Perawatan Terapi Racikan -->

<!--end tab-->
<div id="div_pelaksana" style="display: block;">
	<div class="clearfix"><br></div>
	<table id="dg_pelaksana" title="Set Pelaksana" style="width:100%;" toolbar="#t_pelaksana" idField="fol_id" data-options="pagination:false, rownumbers:true, fitColumns:true, singleSelect:true">
		<thead>
			<tr>
				<th data-options="field:'fol_pelaksana_tipe',width:100,
				formatter:function(value,row){
				return row.fol_posisi_nama;
			},
			editor:{
			type:'combobox',
			options:{
			valueField:'fol_posisi_id',
			textField:'fol_posisi_nama',
			panelHeight: 'auto',
			url:'get_fol_posisi.php',
			required:true,
			onSelect: function (value){
			//alert(value.fol_posisi_id);
			var row = $('#dg_pelaksana').datagrid('getSelected');

			var rowIndex = $('#dg_pelaksana').datagrid('getRowIndex',row);
			//var pelaksana_tipe = $('dg_pelaksana').edatagrid('getEditor', {index:rowIndex,field:'fol_posisi_id'});
			//var rol_jabatan_id = (pelaksana_tipe.target).combobox('getValue');
			var url = 'get_dokterPelaksana.php?id_rol_jabatan='+value.id_rol_jabatan;
			var ed = $('#dg_pelaksana').edatagrid('getEditor', {index:rowIndex,field:'usr_id'});
			(ed.target).combobox('reload',url).combobox('clear');
		}
	},
} ">Posisi</th>

<th data-options="field:'usr_id',width:100,
formatter:function(value,row){
return row.usr_name;
},
editor:{
type:'combobox',
options:{
valueField:'usr_id',
textField:'usr_name',
panelHeight: 'auto',
url : 'get_dokterPelaksana.php?id_rol_jabatan=D',
onSelect:function(row){
setTimeout(function(){
simpan_pelaksana();
}, 1);

},
required:true,
}
}">Pelaksana</th>
</tr>
</thead>
</table>
<div id="t_pelaksana">
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_pelaksana();">Baru</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg_pelaksana').edatagrid('cancelRow')">Cancel</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_pelaksana()">Hapus</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_pelaksana()">Simpan</a>
	<a id="doNothing" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg_pelaksana').edatagrid('reload')">refresh</a>
</div>
</div>

<!--BHP-->
<div class="clearfix"><br></div>
<table id="tb_bhp" title="Bahan Habis Pakai" style="width:100%;" toolbar="#toolbar_bhp" idField="fol_pemakaian_id" rownumbers="true" fitColumns="true" singleSelect="true">
	<thead>
		<tr>
			<th data-options="field:'item_id',width:50,
			formatter:function(value,row){
			return row.item_nama;
		}" editor="{type:'combogrid',options:{
		panelWidth:500,
		idField:'item_id',
		textField:'item_nama',
		url:'get_item_bhp.php',
		mode: 'remote',
		columns:[[
		{field:'item_nama',title:'Nama',width:300},
		]],
		required:true,
		onSelect:get_harga_bhp,
	}}">Item</th>
	<th data-options="field:'fol_pemakaian_jumlah',width:20
	,editor:{type:'text'}
	">Jumlah</th>
	<th data-options="field:'satuan_nama',width:10
	,editor:{type:'text'}
	">Satuan</th>
</tr>
</thead>
<tbody>

</tbody>
</table>
<div id="toolbar_bhp">
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_bhp()">Baru</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_bhp()">Hapus</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="javascript:$('#tb_bhp').edatagrid('saveRow')">Simpan</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="javascript:$('#tb_bhp').edatagrid('cancelRow')">Cancel</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#tb_bhp').edatagrid('reload')">Refresh</a>
</div>
<!--END BHP-->

<div class="clearfix"><br></div>

						<table id="dg" title="Pasien Terdaftar <?php echo $tglSekarang; ?>" class="easyui-datagrid" class="col-md-12 col-sm-12 col-xs-12" style="width:0%;height:0" toolbar="#toolbar
				">
				<!-- <table id="dg"> -->
</table>


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
		

		function layani(id, status) {
			var row = $('#dg').datagrid('getSelected');
			// console.log(row);
			if (id) {
				if (status == 'E0') {
					alert('Pasien Belum Sampai di Poli');
				} else if (status != 'E0') {
					$.get('get_irj.php', {
						reg_id: id
					}, function(result) {

						document.getElementById('norm').value = result[0].cust_usr_kode_tampilan;
						document.getElementById("cust_usr_id").value = result[0].cust_usr_id;
						document.getElementById('regId').value = result[0].reg_id;
						document.getElementById('regId2').value = result[0].reg_id;
						document.getElementById('reg_status').value = result[0].reg_status;
						document.getElementById('nmps').value = result[0].cust_usr_nama;
						document.getElementById('alps').value = result[0].cust_usr_alamat;
						document.getElementById('reg_jenis_pasien').value = result[0].jenis_nama;
						document.getElementById('klinik').value = result[0].poli_nama;
						document.getElementById('id_poli').value = result[0].id_poli;
						document.getElementById("reg_sebab_sakit").value = result[0].sebab_sakit_nama;
						document.getElementById("reg_shift").value = result[0].shift_nama;
						document.getElementById("foto").value = result[0].cust_usr_foto;
						document.getElementById("reg_tanggal").value = result[0].reg_tanggal;
						document.getElementById('jenis_pasien').value = result[0].reg_jenis_pasien;
						$("#rawat_id").val(result[0].rawat_id);
						

						$('#dokter').combobox('setValue', result[0].id_dokter);
						$('#kondisi').combobox('setValue', result[0].reg_status_kondisi);
						$('#tingkat_kegawatan').combobox('setValue', result[0].reg_tingkat_kegawatan);
						//$('#tingkat_kegawatan').combobox().attr('required','required');

						document.getElementById('btn').style.display = 'block'; //jika edit tombol ganti value
						document.getElementById('btnReset').style.display = 'block'; //jika edit tombol reset muncul


						//load combobox dokter
						var url = 'get_dokterdpjp.php?id_poli=' + result[0].id_poli;
						$('#dokter').combobox('reload', url);

						if ($('#dokter').val() == '') {
							alert('Silahkan pilih Dokter dahulu!');
						}

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
							id_cust_usr: result[0].id_cust_usr
						});
						/* GAS BHP TAB */
						$('#dg10').edatagrid({
							url: 'get_data_bhp_tab.php'
						});
						$('#dg10').datagrid('load', {
							id_reg: result[0].reg_id
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
							id_reg: result[0].reg_id,
						});
						$('#dg2').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dg3').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dg4').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dg5').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dg6').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dg7').datagrid('load', {
							id_reg: result[0].reg_id
						});
						$('#dgr').datagrid('load', {
							id_cust_usr: result[0].id_cust_usr
						});
						$('#dghp').datagrid('load', {
							id_reg: result[0].reg_id
						});

						$('#tb_diagnosa').datagrid('load', {
							rawat_id: result[0].rawat_id,
						});
						$('#tb_procedure').datagrid('load', {
							rawat_id: result[0].rawat_id,
						});

						$('#tb_diagnose').datagrid('load', {
							rawat_id: result[0].rawat_id,
						});
						$('#tb_procedures').datagrid('load', {
							rawat_id: result[0].rawat_id,
						});
						if (result[0].layanan == '0bstetri') {



							$('#9dafa78dca4a01f50d21fbc884a5eecb').datagrid('load', {
								reg_id: result[0].reg_id,
								anamnesa_id:'9dafa78dca4a01f50d21fbc884a5eecb',
							});
						}
						else {


							$('#riwayat_kehamilan_ginek').datagrid('load', {
								reg_id: result[0].reg_id,
								anamnesa_id:'9dafa78dca4a01f50d21fbc884a5eecb',
							});
						}



						$('#tb_terapi').datagrid('load', {
							rawat_id: result[0].rawat_id,
							reg_id: result[0].reg_id,
							cust_usr_id: result[0].cust_usr_id,
							reg_jenis_pasien: result[0].reg_jenis_pasien,
						});
						$('#tb_terapi_racikan').datagrid('load', {
							rawat_id: result[0].rawat_id,
							reg_id: result[0].reg_id,
							cust_usr_id: result[0].cust_usr_id,
							reg_jenis_pasien: result[0].reg_jenis_pasien,
						});
						return false;
					}, 'json');
}
}
}

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