<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
	 
	//INISIALISAI AWAL LIBRARY
     $auth = new CAuth();
	 $userName = $auth->GetUserName();

     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	  //DATA AWAL
    $tglSekarang = date("d-m-Y");

	 //tabel header
     $tableHeader = "Rawat Jalan | Pemeriksaan Pasien";
	 
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  	<script type="text/javascript">
	//filter field data 
		$(function(){
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
	<script type="text/javascript">
	  $(function(){
    
    $("#form_pemeriksaan").submit(function(e){
      e.preventDefault();
      form = $('#form_pemeriksaan');
      $.ajax({
        type: 'POST',
        url:'cek_pemeriksaan.php',
        data: form.serialize(),
        dataType: 'json',
        success:function(result){
        console.log(result);
         if (result.success){
								$.messager.show({	 
									title: 'Berhasil',
									msg: "Proses Pemeriksaan Berhasil Disimpan"
								});
								window.location.reload();
							} else {
								$.messager.show({	// show error message
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

		dgp.edatagrid({
			saveUrl: 'proses_pelaksana.php',
			updateUrl: 'proses_pelaksana.php',
		});

		dgr.edatagrid({
			//saveUrl: 'proses_folio.php',
			//updateUrl: 'proses_folio.php',
			onSelect: function(index,row){
						if (row.fol_lunas == 'y' ){		
							alert('sudah dibayar, tidak bisa diedit'); 
							dg1.edatagrid('reload');
						}
					  }
		});

		dg1.edatagrid({
			saveUrl: 'proses_folio.php',
			updateUrl: 'proses_folio.php',
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
							fol_id:row.fol_id,
						},'reload');

					  }
		});
		
		dg2.edatagrid({
			saveUrl: 'proses_rujuk.php',
		});

		dg3.edatagrid({
			saveUrl: 'proses_gas_medis.php',
			updateUrl: 'proses_gas_medis.php',
			onSelect: function(index,row){
						if (row.fol_lunas == 'y' ){		
							alert('sudah dibayar, tidak bisa diedit'); 
							dg3.edatagrid('reload');
						}
					  },
			onClickRow: function(index,row){
						//tanam fol_id
						//alert(row.fol_id);
						$('#fol_id').val(row.fol_id);
						$('#biaya_tarif_id').val(row.id_biaya_tarif);

						dgp.edatagrid({ 
							url: 'get_fol_pelaksana.php',
						}); 
						// data parameter
						dgp.datagrid('load', {
							fol_id:row.fol_id,
						},'reload');

					  }
		});
		
		dg4.edatagrid({
			saveUrl: 'proses_ambulance.php',
			updateUrl: 'proses_ambulance.php',
			onSelect: function(index,row){
						if (row.fol_lunas == 'y' ){		
							alert('sudah dibayar, tidak bisa diedit'); 
							dg4.edatagrid('reload');
						}
					  }
		});
	 
		dg5.edatagrid({
			saveUrl: 'proses_darah.php',
			updateUrl: 'proses_darah.php',
			onSelect: function(index,row){
						if (row.fol_lunas == 'y' ){		
							alert('sudah dibayar, tidak bisa diedit'); 
							dg5.edatagrid('reload');
						}
					  },
			onClickRow: function(index,row){
						//tanam fol_id
						//alert(row.fol_id);
						$('#fol_id').val(row.fol_id);
						$('#biaya_tarif_id').val(row.id_biaya_tarif);

						dgp.edatagrid({ 
							url: 'get_fol_pelaksana.php',
						}); 
						// data parameter
						dgp.datagrid('load', {
							fol_id:row.fol_id,
						},'reload');

					  }
		});
		
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
            <div class="clearfix"></div>
					
			<!-- insert ke folio sebaai data awal -->
			<form method="POST" id="form_pemeriksaan" action="proses_registrasi.php">
				<input id="regId" type="hidden" name="regId">
        <input id="reg_tanggal" type="hidden" name="reg_tanggal">
				<input id="fol_id" type="hidden" name="fol_id">
				<input id="biaya_tarif_id" type="hidden" name="biaya_tarif_id">
				<input id="cust_usr_id" type="hidden" name="cust_usr_id">
				<input id="id_poli" type="hidden" name="id_poli">
			 <!-- BARIS 1 -->
            <div class="row">             
			<!-- KOLOM KIRI -->
              <div class="col-md-4 col-sm-4 col-xs-12"> 
				  <!-- == Hasil dari TABEL BAWA => set ke element berdasar id == -->
				<div class="x_panel">
                  <div class="x_content">
					<table class="col-md-12 col-sm-12 col-md-12">
					  <tr>
						<th width="150px">No. RM</th>
						<td width="15px">:  </td>
						<td><input id="norm" class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Nama Pasien</th>
						<td>:  </td>
						<td><input id="nmps"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Alamat</th>
						<td>:  </td>
						<td><input id="alps"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Klinik</th>
						<td>:  </td>
						<td><input id="klinik"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Sebab Sakit</th>
						<td>:  </td>
						<td><input id="reg_sebab_sakit"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr hidden>
						<th>Shift Kedatangan</th>
						<td>:  </td>
						<td><input id="reg_shift" required class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Cara Bayar</th>
						<td>:  </td>
						<td><input id="reg_jenis_pasien"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
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
						url: 'get_dokterPelaksana.php',
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
					<div  hidden class="col-md-6 col-sm-6 col-xs-12">
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
			  <input name="btn" id="btn" class="btn btn-default col-md-3 pull-right" type="submit" value="Selesai" style="display: none;">
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
							<table id="dg1" style="width:100%;"
									toolbar="#toolbar1" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>

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
											}">Tindakan</th>

										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>										
									</tr>
								</thead>
							</table> 
							<div id="toolbar1">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_folio();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg1').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_folio()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_folio()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg1').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 2 -->
						<div title="Rujukan" style="padding:5px">
							<table id="dg2" style="width:100%;"
									toolbar="#toolbar2" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
									<th data-options="field:'reg_tanggal',width:50">Tanggal</th>
									<th data-options="field:'reg_waktu',width:50">Waktu</th>
									<th data-options="field:'poli_id',width:200,
												formatter:function(value,row){
													return row.poli_nama;
												},
												editor:{
													type:'combobox',
													options:{
														valueField:'poli_id',
														textField:'poli_nama',
														url:'get_klinik_all.php',
														panelHeight: '100px',
														required:true
													}
												}">Klinik tujuan</th>
									</tr>
								</thead>
							</table> 
							<div id="toolbar2">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_rujuk();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg2').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_rujuk()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg2').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 3 -->
						<div title="Pemeriksaan" style="padding:5px">
						  <div class="form-horizontal form-label-left">
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
						<!--Tab 4-->
						<!--<div title="Asuhan Keperawatan" style="padding:5px">
						  <div class="form-horizontal form-label-left">
							<div class="item form-group">
								<label class="control-label col-md-2 col-sm-2 col-xs-12">Alasan Kunjungan</label>
								<div class="col-md-8 col-sm-8 col-xs-12">									
									<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Tujuan Kunjungan:</label>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Berobat
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Konsultasi
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Rujukan Internal
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Rujukan Eksternal
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Kontrol
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Paksa Rawat
								  </div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">									
									<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Keluhan:</label>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Tidak Ada
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Nyeri
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Muntah
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Tidak Bisa Tahan Kencing
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Anyang - Anyangan
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Kencing Tak Tuntas
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Demam
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Susah Kencing
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Kencing Darah
								  </div>
								  <div class="col-md-3 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Lain-lain
								  </div>
								</div>
							</div>
							<div class="item form-group">
								<label class="control-label col-md-2 col-sm-2 col-xs-12">Pemeriksaan Tanda-Tanda Vital</label>
								<div class="col-md-8 col-sm-8 col-xs-12">
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Keadaan Umum Pasien*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Pilih Isian</option>
								  			<option>Baik</option>
								  			<option>Sedang</option>
								  			<option>Kurang</option>
								  			<option>Tidak Bisa Tahan Kencing</option>
								  			<option>Anyang - Anyangan</option>
								  			<option>Kencing Tak Tuntas</option>
								  			<option>Demam</option>
								  			<option>Susah Kencing</option>
								  			<option>Kencing Darah</option>
								  			<option>Lain - Lain</option>
								  		</select>
								  	</div>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Tekanan Darah Sistole*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">mmhg</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Tekanan Darah Diastole*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">mmhg</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Suhu*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">Derajat C</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Tinggi Badan*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">cm</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Berat Badan*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">Kg</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Nadi*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">x/Menit</label>
								  </div>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								  	<label class="control-label col-md-4 col-sm-2 col-xs-12">Pernafasan*</label>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" name="" class="form-control">
								  	</div>
								  	<label class="control-label col-md-2 col-sm-2 col-xs-12">x/Menit</label>
								  </div>
								</div>
							</div>
							<div class="item form-group">
								<label class="control-label col-md-2 col-sm-2 col-xs-12">Skrining Gizi</label>
								<div class="col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Apakah pasien mengalami penurunan berat badan yang tidak diinginkan dalam enam bulan terakhir?</label>
									</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Tidak ada penurunan berat badan</option>
								  			<option>Tidak yakin / Tidak tahu / Terasa baju longgar</option>
								  		</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Jika YA, berapa penurunan badan tersebut?</label>
									</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Tidak</option>
								  			<option>1 - 5 kg</option>
								  			<option>6 - 10 kg</option>
								  			<option>11 - 15 kg</option>
								  			<option>> 15 kg</option>
								  		</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Jika YA, berapa penurunan badan tersebut?</label>
									</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Tidak</option>
								  			<option>Ya</option>
								  		</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Jika YA, berapa penurunan badan tersebut?</label>
									</div>
								  	<div class="col-md-5 col-sm-12 col-xs-12">
										<input type="text" class="form-control">
								  	</div>
								  	<div class="col-md-5 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								  	<div class="col-md-2 col-sm-12 col-xs-12">
								  		<input type="button" name="" value="Hitung" class="form-control">
								  	</div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Jika YA, berapa penurunan badan tersebut?</label>
									</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Tidak</option>
								  			<option>DM</option>
								  			<option>Kemoterapi</option>
								  			<option>Hemodialisa</option>
								  			<option>Geriatri</option>
								  			<option>Imunitas Menurun</option>
								  			<option>Lain - Lain</option>
								  		</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								</div>
							</div>
							<div class="item form-group">
								<label class="control-label col-md-2 col-sm-2 col-xs-12">Skrining Resiko Jatuh / Cidera</label>
								  <div class="col-md-8 col-sm-8 col-xs-12">
								    <div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Perhatikan cara berjalan pasien saat akan duduk dikursi, apakah tampak sempoyongan/limbung?</label>
								    </div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<select class="form-control">
								  			<option>Tidak ada penurunan berat badan</option>
								  			<option>Tidak yakin / Tidak tahu / Terasa baju longgar</option>
								  		</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								  </div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Apakah pasien memegang pinggiran kursi atau meja atau benda lain sebagai penopang saat akan duduk?</label>
									</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
										<select class="form-control">
											<option>Tidak ada penurunan berat badan</option>
								  			<option>Tidak yakin / Tidak tahu / Terasa baju longgar</option>
										</select>
								  	</div>
								  	<div class="col-md-6 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								</div>
								<div class="col-md-offset-2 col-md-8 col-sm-8 col-xs-12">
									<div class=" col-md-12 col-sm-8 col-xs-12">									
										<label class="col-md-3 col-sm-12 col-xs-12" style="text-align: left;">Hasil:</label>								
										<label class="col-md-3 col-sm-12 col-xs-12" >Keterangan:</label>						
										<label class="col-md-3 col-sm-12 col-xs-12" >Penanganan:</label>
									</div>
								  	<div class="col-md-3 col-sm-12 col-xs-12">
										<input type="text" class="form-control">
								  	</div>
								  	<div class="col-md-3 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								  	<div class="col-md-3 col-sm-12 col-xs-12">
								  		<input type="text" class="form-control">
								  	</div>
								  	<div class="col-md-2 col-sm-12 col-xs-12">
								  		<input type="button" value="Hitung" class="form-control">
								  	</div>
								</div>
							</div>
							<div class="item form-group">
								<label class="control-label col-md-2 col-sm-2 col-xs-12">Masalah Keperawatan</label>
								<div class="col-md-8 col-sm-8 col-xs-12">
								  <div class="col-md-8 col-sm-8 col-xs-12">									
									<label class="control-label col-md-12 col-sm-12 col-xs-12" style="text-align: left;">Masalah Keperawatan:</label>
								</div>
								<div class="col-md-8 col-sm-8 col-xs-12">
								  <div class="col-md-12 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Gangguan Integritas Kulit
								  </div>
								  <div class="col-md-12 col-sm-12 col-xs-12">
								  	<input type="checkbox" name=""> Gangguan rasa nyaman nyeri
								  </div>
								  <div class="col-md-12 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Gangguan Urinaria
								  </div>
								  <div class="col-md-12 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Kontrol post rawat
								  </div>
								  <div class="col-md-12 col-sm-12 col-xs-12">
								  	<input type="checkbox" name="">	Resiko Infeksi
								  </div>
								</div>
								</div>
							</div>
							<div class="item form-group">
								<label class="control-label col-md-8 col-sm-8 col-xs-12"></label>
								<div class="col-md-2 col-sm-2 col-xs-12">
								  <input id="btn_pemeriksaan" class="btn btn-default form-control" type="button" value="Simpan">
								</div>
							</div>
						  </div>
						</div>-->
						<!-- tab 4 
						<div title="Gas Medis" style="padding:5px">
							<table id="dg3" style="width:100%;"
									toolbar="#toolbar3" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:300,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_gas.php',
													mode: 'remote',
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													delay: 200,
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner',options:{precision:0}}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>										
									</tr>
								</thead>
							</table> 
							<div id="toolbar3">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_gas();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg3').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_gas()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_gas()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg3').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 5 
						<div title="Ambulance" style="padding:5px">
							<table id="dg4" style="width:100%;"
									toolbar="#toolbar4" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:300,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_ambulance.php',
													mode: 'remote',
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													delay: 200,
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
																				
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100,
											editor:{
												type:'textbox'
											}">Supir</th>
										
										<th data-options="field:'no_plat',width:100,editor:{type:'textbox'}">No Plat</th>
											
									</tr>
								</thead>
							</table> 
							<div id="toolbar4">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_ambulance();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg4').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_ambulance()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_ambulance()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg4').edatagrid('reload')">refresh</a>
							</div>
						</div>
						<!-- tab 6 
						<div title="Labu Darah" style="padding:5px">
							<table id="dg5" style="width:100%;"
									toolbar="#toolbar5" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										<th data-options="field:'biaya_tarif_id',width:200,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_tarif_id',
													textField:'biaya_nama',
													url:'get_biaya_darah.php',
													mode: 'remote',
													delay: 200,
													onBeforeLoad:function(param){
														param.id_poli = document.getElementById('id_poli').value;
													}, 
													columns:[[
														{field:'biaya_nama',title:'Nama',width:300},
														{field:'biaya_total',title:'Biaya',width:100,options:{min:0,decimalSeparator:3}},
													]],
													required:true
												}
											}">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:50
										,editor:{type:'numberspinner',options:{precision:0}}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:0">Lunas</th>
										
										<th data-options="field:'no_kantong',width:50,editor:{type:'textbox'}">No Kantung</th>
										
										<th data-options="field:'gol_darah',width:100,
											editor:{
												type:'combobox',
												options:{
													data: [{
														label: 'A',
														value: 'A'
													},{
														label: 'AB',
														value: 'AB'
													},{
														label: 'B',
														value: 'B'
													},{
														label: 'O',
														value: 'O'
													}],
													valueField:'value',
													textField:'label',
													panelHeight: 'auto',
													required:true
												}
											}">Gol. Darah</th>
											
										<th data-options="field:'rhesus',width:100,
											editor:{
												type:'combobox',
												options:{
													data: [{
														label: 'Positif',
														value: 'Positif'
													},{
														label: 'Negatif',
														value: 'Negatif'
													}],
													valueField:'value',
													textField:'label',
													panelHeight: 'auto',
													required:true
												}
											}">Rhesus</th>
												
									</tr>
								</thead>
							</table> 
							<div id="toolbar5">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_darah();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg5').edatagrid('cancelRow')">Cancel</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_darah()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_darah()">Simpan</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg5').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 7 -->
						<div title="Rencana operasi" style="padding:5px">
							<table id="dg6" style="width:100%;"
									toolbar="#toolbar6" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'preop_waktu',width:100">Waktu Order</th>
										<th data-options="field:'preop_tanggal_jadwal',width:100">Rencana Operasi</th>
										<th data-options="field:'preop_selesai_jadwal',width:100">Rencana Selesai</th>
										
									</tr>
								</thead>
							</table> 
							<div id="toolbar6">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_preop()">Order</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="delete_preop()">Batal Order</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg6').edatagrid('reload')">refresh</a>
							</div>
						</div>
						
						<!-- tab 8 -->
						<div title="Hasil Lab" style="padding:5px">
							<table id="dg7"  class="col-md-12 col-sm-12 col-xs-12" 
									toolbar="#toolbar7" pagination="false" height="200"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
									<th data-options="field:'test_nm',width:100">Nama Pemeriksaan</th>
									<th data-options="field:'result_value',width:100">Nilai Pemeriksaan</th>
									<th data-options="field:'ref_range',width:100">Nilai Rujukan / Normal</th>
									<th data-options="field:'unit',width:100">Unit / Satuan</th>
									</tr>
								</thead>
							</table> 
							<div id="toolbar7">
								<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg7').edatagrid('reload')">refresh</a>
							</div>
						</div>

						<!-- tab 1 -->
						<div title="Hasil Radiologi" style="padding:5px">
							<table id="dgr" style="width:100%;"
									toolbar="#toolbarr" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>

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
											}">Tindakan</th>

										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>										
									</tr>
								</thead>
							</table> 
							<div id="toolbarr">
								<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_resume()">Lihat Resume</a>
							</div>
						</div>								
					</div>
					<!--end tab-->
					<div id="div_pelaksana" style="display: block;">
					<div class="clearfix"><br></div>
					<table id="dg_pelaksana" title="Set Pelaksana" style="width:100%;"
							toolbar="#t_pelaksana" idField="fol_id"
							data-options="pagination:false, rownumbers:true, fitColumns:true, singleSelect:true">
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
											required:true
										}
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
											url:'get_dokterPelaksana.php',
											required:true
										}
									}">Pelaksana</th>
							</tr>
						</thead>
					</table>
					<div id="t_pelaksana">
						<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_pelaksana();">Baru</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="$('#dg_pelaksana').edatagrid('cancelRow')">Cancel</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_pelaksana()">Hapus</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_pelaksana()">Simpan</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="$('#dg_pelaksana').edatagrid('reload')">refresh</a>
					</div>	
					</div>

					<div class="clearfix"><br></div>
					<table id="dg" title="Pasien Terdaftar <?php echo $tglSekarang;?>" class="easyui-datagrid" class="col-md-12 col-sm-12 col-xs-12" style="width:100%;height:350px"
							toolbar="#toolbar"
							data-options=" url:'get_irj.php', pagination:false,
							rownumbers:true, fitColumns:true, singleSelect:true,
							onDblClickRow:function(){
								layani();
							}">
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
								<th data-options="field:'reg_status',
								formatter:function(value,row){
												var E0 = 'Belum Dilayani';
												var E1 = 'Sampai di Poli';
												var E2 = 'Sudah Dilayani';
												if (row.reg_status == 'E0') { return E0; }
												if (row.reg_status == 'E1') { return E1; }
												if (row.reg_status == 'E2') { return E2; }
											}
								">Status</th>
                                
							</tr>
						</thead>
					</table>
					<div id="toolbar">
					<div id = "tb" style = "padding: 5px; height: auto">
						
						<div>
							<a href="#" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="sampai()">Sampai di Poli</a>
  						<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="layani()">Layani</a>
  						<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#dg').edatagrid('reload')">refresh</a>
  						<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakspb()">Cetak SPB</a>
  						<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak_tagihan()">Cetak Tagihan</a>
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
    </script>

	<script type="text/javascript">
	var d = new Date();
	var tanggal = d.getDate()+"-"+(d.getMonth()+1)+"-"+d.getFullYear();
	var waktu = d.getHours()+":"+d.getMinutes()+":"+d.getSeconds();
	var notif1 = "Pilih pasien dahulu.";
	var notif2= "Pilih tindakan dahulu.";
  
  
  function cari(){			
			$('#dg').edatagrid('load',{
		        tgl_awal: $('#tgl_awal').val(),
		        tgl_akhir: $('#tgl_akhir').val()
		    });
			
		}
  
	//pre operasi ---------------------------
		function delete_preop(){			
			var row = $('#dg6').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin?',function(r){
					if (r){
						$.post('del_preop.php',{id:row.preop_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dibatalkan"
								});
								$('#dg6').datagrid('reload');	// reload the user data
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
	
		function add_preop(){
			var dataString = 'id_reg=' + document.getElementById('regId').value;
			  $.ajax({
				type: "POST",
				url: "proses_preop.php",
				data: dataString,
				success: function(){
					alert("Berhasil order");
					$('#dg6').edatagrid({ url: 'get_preop.php' }); //load data
					$('#dg6').datagrid('load', {
						id_reg: document.getElementById('regId').value
					});
				}
			  });
			return false;
		}
	// labu darah ------------------------------------
		function delete_darah(){			
			var row = $('#dg5').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_folio.php',{id:row.fol_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg5').datagrid('reload');	// reload the user data
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
	
		function simpan_darah(){
			$('#dg5').edatagrid('saveRow');
		    $('#dg5').edatagrid({ url: 'get_darah.php' }); //load data
			// data parameter
			$('#dg5').datagrid('load', {
				id_reg: document.getElementById('regId').value
			},'reload');
		}
	
		function add_darah(){
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != "") {
				$('#dg5').edatagrid('addRow',{
				index: 0,
				row:{
					tindakan_tanggal: tanggal,
					tindakan_waktu: waktu,
					id_reg : regId,
					fol_jumlah : '1' 
				}
				});
			} else {alert(notif1);}
		}
		// ambulance ------------------------------
		function delete_ambulance(){			
			var row = $('#dg4').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_folio.php',{id:row.fol_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg4').datagrid('reload');	// reload the user data
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
	
		function simpan_ambulance(){
			$('#dg4').edatagrid('saveRow');
		    $('#dg4').edatagrid({ url: 'get_ambulance.php' }); //load data
			// data parameter
			$('#dg4').datagrid('load', {
				id_reg: document.getElementById('regId').value
			},'reload');
		}
	
		function add_ambulance(){
		  // insert a row with default values
			var regId = document.getElementById('regId').value;
			if (regId != '') {
			$('#dg4').edatagrid('addRow',{
				index: 0,
				row:{
					tindakan_tanggal: tanggal,
					tindakan_waktu: waktu,
					id_reg : regId,
					fol_jumlah : '1'
				}
			});  
			} else {alert(notif1);}
		}
		// gas medis-------------------------------
		function delete_gas(){			
			var row = $('#dg3').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_folio.php',{id:row.fol_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg3').datagrid('reload');	// reload the user data
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
	
		function simpan_gas(){
			$('#dg3').edatagrid('saveRow');
		    $('#dg3').edatagrid({ url: 'get_gas_medis.php' }); //load data
			// data parameter
			$('#dg3').datagrid('load', {
				id_reg: document.getElementById('regId').value
			},'reload');
		}
	
		function add_gas(){
		  // insert a row with default values
			var regId = document.getElementById('regId').value;
			if(regId != '') {
			$('#dg3').edatagrid('addRow',{
				index: 0,
				row:{
					tindakan_tanggal: tanggal,
					tindakan_waktu: waktu,
					id_reg : regId,
					fol_jumlah : '1'
				}
			});
			} else {alert(notif1);}
		}
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
		// rujuk -----------------
		function add_rujuk(){
			// insert a row with default values
			var regId = document.getElementById('regId').value;
			if(regId != '') {
			$('#dg2').edatagrid('addRow',{
				index: 0,
				row:{
					regId : regId,
					fol_jumlah : '1'
				}
			});
			} else {alert(notif1);}
		}
		
		function simpan_rujuk(){
			var simpan = $('#dg2').edatagrid('saveRow');
			if (simpan){
				//load data
				$('#dg2').datagrid({
					url: 'get_data_rujukan.php'
				});
				$('#dg').datagrid('reload');
				
				// data parameter
				$('#dg2').datagrid('load', {
					id_reg: result[0].reg_id
				},'reload');
			}
			
		}
		//tindakan ----------------------------------
		function delete_folio(){			
			var row = $('#dg1').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_folio.php',{id:row.fol_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								$('#dg1').datagrid('reload');	// reload the user data
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
		
		function simpan_folio(){
			$('#dg1').edatagrid('saveRow');
		    $('#dg1').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg1').datagrid('load', {
				id_reg: document.getElementById('regId').value
			},'reload');
		}
		
		function add_folio(){
			var regId = document.getElementById('regId').value;
      var regTanggal = document.getElementById('reg_tanggal').value;
			//$('#dg_pelaksana').edatagrid('reload');
			if(regId != "") {
				$('#dg1').edatagrid('addRow',{
					index: 0,
					row:{
						tindakan_tanggal: regTanggal,
						tindakan_waktu: waktu,
						id_reg : regId,
						fol_jumlah : '1'
					}
				});
			} else {alert(notif1);}
		}

		//pelaksana ----------------------------------
		function delete_pelaksana(){	
			var dgp = $('#dg_pelaksana').edatagrid();		
			var row = dgp.edatagrid('getSelected');
			var fol_id = $('#fol_id').val();
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('del_pelaksana.php',{id:row.fol_pelaksana_id},function(result){
							if (result.success){
								$.messager.show({	// 
									title: 'Berhasil',
									msg: "Berhasil Dihapus"
								});
								dgp.edatagrid('load',{fol_id: fol_id});	// reload the user data
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
		
		function simpan_pelaksana(){
			var fol_id = $('#fol_id').val();
			$('#dg_pelaksana').edatagrid('saveRow');
		    $('#dg_pelaksana').edatagrid({ url: 'get_fol_pelaksana.php' }); //load data
			// data parameter
			$('#dg_pelaksana').datagrid('load', {
				fol_id: fol_id
			},'reload');
		}
		
		function add_pelaksana(){
			var dokter = $('#dokter').val();
			//alert(dokter);					   
			var fol_id = $('#fol_id').val();
			var biaya_tarif_id = $('#biaya_tarif_id').val();
		  // insert a row with default values
		  if(fol_id != ''){
			$('#dg_pelaksana').edatagrid('addRow',{
				index: 0,
				row:{
					id_fol : fol_id,
					id_biaya_tarif : biaya_tarif_id,
					 usr_id : dokter 
				}
			});
		  } else {alert(notif2);}
		}
		
		//layani -------------------
		function layani(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
			  if(row.reg_status == 'E0'){
				alert('Pasien Belum Sampai di Poli');
			} else if(row.reg_status != 'E0'){
				$.get('get_irj.php',{reg_id:row.reg_id},function(result){
					document.getElementById('norm').value = result[0].cust_usr_kode_tampilan;							
					document.getElementById("cust_usr_id").value = result[0].cust_usr_id;
					document.getElementById('regId').value = result[0].reg_id;	
					document.getElementById('nmps').value = result[0].cust_usr_nama;							
					document.getElementById('alps').value = result[0].cust_usr_alamat;							
					document.getElementById('reg_jenis_pasien').value = result[0].jenis_nama;
					document.getElementById('klinik').value = result[0].poli_nama;
					document.getElementById('id_poli').value = result[0].id_poli;
					document.getElementById("reg_sebab_sakit").value = result[0].sebab_sakit_nama;
					document.getElementById("reg_shift").value = result[0].shift_nama;
					document.getElementById("anamnesa").value = result[0].rawat_anamnesa;
					document.getElementById("observasi").value = result[0].rawat_keluhan;
					document.getElementById("konsultasi").value = result[0].rawat_catatan;
					document.getElementById("pemeriksaan_umum").value = result[0].rawat_pemeriksaan_fisik;
					document.getElementById("pencatatan_diagnosa").value = result[0].rawat_diagnosa_utama;
					document.getElementById("resume_medis").value = result[0].rawat_ket;
					document.getElementById("reg_tanggal").value = result[0].reg_tanggal;
					
					$('#dokter').combobox('setValue', result[0].id_dokter);
					$('#kondisi').combobox('setValue', result[0].reg_status_kondisi);
					$('#tingkat_kegawatan').combobox('setValue', result[0].reg_tingkat_kegawatan);
					//$('#tingkat_kegawatan').combobox().attr('required','required');
			
					document.getElementById('btn').style.display = 'block';	//jika edit tombol ganti value
					document.getElementById('btnReset').style.display = 'block';	//jika edit tombol reset muncul

					//load combobox dokter
					var url = 'get_dokterPelaksana.php?id_poli='+result[0].id_poli;
					$('#dokter').combobox('reload', url);

					if($('#dokter').val() == ''  ){ alert('Silahkan pilih Dokter dahulu!'); }					
					  			  
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
					$('#dg7').edatagrid({ 
						url: 'get_hasil_lab.php'
					}); 
					$('#dgr').edatagrid({ 
						url: 'get_hasil_rad.php'
					});
					$('#dg9').edatagrid({ 
						url: 'get_hasil_rad.php'
					}); 
					$('#dg8').edatagrid({ 
						url: 'get_hasil_rad.php'
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
						id_reg: result[0].reg_id
					});
						
					  
					  return false;
				},'json');  
			  }  
			}
		}
		
		//Sampai di Poli
		function sampai(){
			var row = $('#dg').datagrid('getSelected');
			if (row.reg_status == 'E0'){
				$.get('get_irj.php',{reg_id:row.reg_id},function(result){
					//insert awal (PK FK) ke folio						
					var dataString = 'isNewRecord=false'+ 
									 '&id_dep=' + result[0].id_dep + 
									 '&id_reg=' + result[0].reg_id + 
									 '&cust_usr_id='+ result[0].id_cust_usr + 
									 '&id_pembayaran=' + result[0].id_pembayaran + 
									 '&id_poli=' + result[0].id_poli +
									 '&id_dokter=' + result[0].usr_id + 
									 '&id_reg_jenis_pasien=' + result[0].reg_jenis_pasien;
					  //alert (dataString);return false;
					  $.ajax({
						type: "POST",
						url: "proses_sampai.php",
						data: dataString,
						success : function() { $('#dg').edatagrid('reload'); }
					  });
				},'json');  		
				//layani();
			} else if(row.reg_status != 'E0'){
				alert('Pasien Sudah Sampai di Poli');
			}  
		}
		
		//fungsi cetak spb
		function cetakspb(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_spb.php?id_reg='+row.reg_id+'&pembayaran_id='+row.id_pembayaran;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak tagihan
		function cetak_tagihan(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_tagihan.php?id_reg='+row.reg_id+'&pembayaran_id='+row.id_pembayaran;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}

		//fungsi cetak resume
		function cetak_resume(){
			 var row = $('#dgr').datagrid('getSelected');
			if (row){
				var url = 'cetak_resume.php?id_resume='+row.resume_id+'&id_reg='+row.id_reg;
				 var printWindow = window.open( url );
				 printWindow.addEventListener('load', function(){
					//printWindow.print();
					//printWindow.close();
				}, true);
			}
		}
	</script>

  </body>
</html>           