<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
	 
	//INISIALISAI AWAL LIBRARY
     $auth = new CAuth();

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

	  //tabel headerf
    $tableHeader = "Laboratorium | Pemeriksaan Pasien";
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
	<script type="text/javascript">
		$(function(){
			$('#dg1').edatagrid({
				saveUrl: 'proses_folio.php?tambah=true',
				updateUrl: 'proses_folio.php?update=true',
				onSelect: function(index,row){
							if (row.fol_lunas == 'y' ){		
								alert('sudah dibayar, tidak bisa diedit'); 
								$('#dg1').edatagrid('reload');
							}
						  }
				//rowStyler:function(index,row){
					//if (row.fol_lunas == 'y' ){
						//return 'background-color:yellow;font-weight:bold;';
					//}
				//}
			});
		});
		
	</script>
	<script type="text/javascript">
		$(function(){
			$('#dg2').edatagrid({
				//url: 'get_data_rujukan.php',
				saveUrl: 'proses_rujuk.php',
				//updateUrl: 'proses_folio.php?update=true'
			});
		});
	</script>
	 <script type="text/javascript">
	//filter field data 
		$(function(){
			var dg = $('#dg').datagrid();
			
			dg.datagrid('enableFilter', [
			//disable filter
			{field:'reg_waktu',type:'label'},
			{field:'cust_usr_kode',type:'text'},
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
                <h3>&nbsp;</h3>
              </div>
            </div>
            <div class="clearfix"></div>
					
			<!-- insert ke folio sebaai data awal -->
			<form method="POST" action="proses_registrasi.php">
				<input id="regId" type="hidden" name="regId">
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
						<th>Penunjang</th>
						<td>:  </td>
						<td><input id="klinik"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Sebab Sakit</th>
						<td>:  </td>
						<td><input id="reg_sebab_sakit"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
					  </tr>
					  <tr>
						<th>Shift Kedatangan</th>
						<td>:  </td>
						<td><input id="reg_shift"  class="no-border col-md-12 col-sm-12 col-md-12" readonly></td>
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
						/* onSelect: function(value){
							var v = value.kondisi_akhir_pasien_nama
							if ( v == 'Dirujuk' ){
								var url = 'get_klinik.php?rujuk=true';
								$('#rujuk').combobox('reload', url);
								$('#div_rujuk').css('display','block');
								//alert( v ); 
							} else {
								$('#div_rujuk').css('display','none');
							} 
						}*/
						">
						
						<!--div id="div_rujuk" style="display:none;" class="col-md-10 col-sm-10 col-xs-10">
						<input id="rujuk" name="rujuk" class="easyui-combobox" style="width:100%;" data-options="
							valueField: 'poli_id',
							textField: 'poli_nama',
							label: '&nbsp;',
							labelPosition: 'top',
							panelHeight: 'auto',
							">
						</div-->
					</div>
					
                  </div>
                </div>
			  <input name="btn" id="btn" class="btn btn-default col-md-3 pull-right" type="submit" value="Simpan">
			  <input id="btnReset" class="btn btn-default pull-right" style="display:none;" value="Batal" onclick="window.location.reload()">
              </div>
            </div>
			</form>

             <!-- row 2 == Data View Pasien -->
            <div class="row">             
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_content">
				  <!-- tab-->
					<div class="easyui-tabs" class="col-md-12 col-sm-12 col-xs-12">
						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg1"  class="col-md-12 col-sm-12 col-xs-12" 
									toolbar="#toolbar1" pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									pagination="true" pageSize="10">
								<thead>
									<tr>
										<th data-options="field:'id_biaya',width:300,
											formatter:function(value,row){
												return row.biaya_nama;
											},
											editor:{
												type:'combogrid',
												options:{
													panelWidth:500,
													idField:'biaya_id',
													textField:'biaya_nama',
													url:'get_biaya.php',
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
										
										<th data-options="field:'pelaksana',width:100,
											formatter:function(value,row){
												return row.pelaksana_nama;
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
											
										<th data-options="field:'dokter_instruksi',width:200,
											formatter:function(value,row){
												return row.dokter_instruksi_nama;
											},
											editor:{
												type:'combobox',
												options:{
													valueField:'usr_id',
													textField:'usr_name',
													url:'get_dokterPelaksana.php',
													panelHeight: 'auto',
													required:true
												}
											}">Dokter Instruksi</th>
										
									</tr>
								</thead>
							</table> 
							<div id="toolbar1">
								<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="add_folio();">Baru</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="delete_folio()">Hapus</a>
								<a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="simpan_folio()">Simpan</a>
							</div>
						</div>
						<!-- tab 2
						<div title="Labu Darah" style="padding:10px">
							<p></p>
						</div>  -->
					</div>
					<!--end tab-->
					<div class="clearfix"><br></div>
					<table id="dg" title="Pasien Terdaftar <?php echo $tglSekarang;?>" class="easyui-datagrid" class="col-md-12 col-sm-12 col-xs-12"
							toolbar="#toolbar"
							data-options=" url:'get_irj.php', pagination:false,
							rownumbers:true, fit:true, autoRowHeight:true, fitColumns:true, singleSelect:true">
						<thead>
							<tr>
								<!-- TABEL DATA => field samakan field tabel database -->
								<th field="id_cust_usr" hidden width="50">user ID</th>
								<th field="reg_id" hidden width="50">Reg ID</th>
								<th field="reg_utama" hidden width="50">Reg utama</th>
								<th field="reg_waktu" width="50">Waktu</th>
								<th field="cust_usr_kode" width="50">No. RM</th>
								<th field="cust_usr_nama" width="50">Nama Pasien</th>
								<th field="cust_usr_alamat" width="50">Alamat</th>
								<th field="cust_usr_tanggal_lahir" width="50">Tanggal Lahir</th>
								<th field="poli_nama" width="50">Poli Asal</th>
								<th data-options="field:'reg_status',width:100,
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
						<a href="#" class="easyui-linkbutton" iconCls="icon-ok" plain="true" onclick="sampai()">Sampai di Poli</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="layani()">Layani</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="javascript:$('#dg').edatagrid('reload')">refresh</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetak()">Cetak Reg</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-print" plain="true" onclick="cetakspb()">Cetak SPB</a>
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
		function delete_folio(){			
			var row = $('#dg1').datagrid('getSelected');
			if (row){
				$.messager.confirm('Konfirmasi','Anda yakin menghapus data ini?',function(r){
					if (r){
						$.post('proses_folio.php?delete=true',{id:row.fol_id},function(result){
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
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$.get('get_irj.php',{reg_id:row.reg_id},function(result){
					  //load data
					  $('#dg1').edatagrid({
							url: 'get_folio.php'
						}); 
						// data parameter
						$('#dg1').datagrid('load', {
							id_reg: result[0].reg_id,
							reg_utama: result[0].reg_utama,
							id_cust_usr: result[0].id_cust_usr,
							id_dokter: result[0].usr_id,
							id_poli: result[0].id_poli
							
						},'reload');
					  
					  return false;
				},'json');  
					
			}
		}
		
		function add_folio(){
		  $('#dg1').edatagrid('addRow');
		  var reg_id = $('#regId').val();
		  //alert(reg_id);
			 
		}
		
		function layani(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$.get('get_irj.php',{reg_id:row.reg_id},function(result){
					document.getElementById('norm').value = result[0].cust_usr_kode;							
					document.getElementById('regId').value = result[0].reg_id;	
					document.getElementById('nmps').value = result[0].cust_usr_nama;							
					document.getElementById('alps').value = result[0].cust_usr_alamat;							
					document.getElementById('reg_jenis_pasien').value = result[0].jenis_nama;
					document.getElementById('klinik').value = result[0].poli_nama;
					document.getElementById("reg_sebab_sakit").value = result[0].sebab_sakit_nama;
					document.getElementById("reg_shift").value = result[0].shift_nama;
					
					$('#dokter').combobox('setValue', result[0].id_dokter);
					$('#kondisi').combobox('setValue', result[0].reg_status_kondisi);
			
					document.getElementById('btn').value = "Simpan";	//jika edit tombol ganti value
					document.getElementById('btnReset').style.display = 'block';	//jika edit tombol reset muncul
					
					  //load data
					  $('#dg1').edatagrid({
							url: 'get_folio.php'
						}); 
					  						
						// data parameter
						$('#dg1').datagrid('load', {
							id_reg: result[0].reg_id,
							id_cust_usr: result[0].id_cust_usr,
							id_dokter: result[0].usr_id,
							id_poli: result[0].id_poli
							
						},'reload');
						
					  
					  return false;
				},'json');  
					
			}  
		}

		//sampai di poli
		function sampai(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
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
					  
					  return false;
				},'json');  
					
			}  
		}
		
		//fungsi cetak registrasi
		function cetak(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_registrasi.php?reg_id='+row.reg_id;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					printWindow.print();
					printWindow.close();
				}, true);
			}
		}
		
		//fungsi cetak spb
		function cetakspb(){
			 var row = $('#dg').datagrid('getSelected');
			if (row){
				var url = 'cetak_spb.php?reg_id='+row.reg_id+'&pembayaran_id='+row.id_pembayaran;
				 var printWindow = window.open( url, 'load', 'left=200, top=100, toolbar=0, resizable=0' );
				 printWindow.addEventListener('load', function(){
					printWindow.print();
					printWindow.close();
				}, true);
			}
		}
	</script>

  </body>
</html>           