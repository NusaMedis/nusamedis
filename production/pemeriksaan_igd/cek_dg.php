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
	$tableHeader = "Rawat Inap | Pemeriksaan Pasien";
	 
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

	<script type="text/javascript">
		$(function(){
			$('#dg1').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg1').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg2').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg2').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg3').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg3').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg4').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg4').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg5').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg5').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg6').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg6').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg7').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg7').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg8').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg8').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg9').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg9').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg10').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg10').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg11').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg11').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');

			$('#dg12').edatagrid({ url: 'get_folio.php' }); //load data
			// data parameter
			$('#dg12').datagrid('load', {
				id_reg: 'a8ee08e1882c8bfd9837d8139cd5b632'
			},'reload');
	
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

             <!-- row 2 == Data View Pasien -->
            <div class="row">             
              <div class="col-md-12 col-sm-12 col-xs-12">
                <!--begin all tab-->
					<div class="easyui-tabs" style="width: 100%;">
						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg1" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg2" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg3" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg4" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg5" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg6" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg7" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg8" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg9" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg10" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg11" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						<!-- tab 1 -->
						<div title="Tindakan" style="padding:5px">
							<table id="dg12" style="width:100%;"
									pagination="false" idField="fol_id"
									rownumbers="true" fitColumns="true" singleSelect="true" 
									striped="true">
								<thead>
									<tr>
										<th data-options="field:'tindakan_tanggal',width:50
										,editor:{type:'text'}
										" >Tanggal</th>
										
										<th data-options="field:'tindakan_waktu',width:50
										,editor:{type:'text'}
										" >Waktu</th>
										
										<th data-options="field:'biaya_tarif_id',width:300">Tindakan</th>
											
										<th data-options="field:'fol_jumlah',width:30
										,editor:{type:'numberspinner'}
										" >Jumlah</th>
										
										<th hidden data-options="field:'fol_lunas',width:30">Lunas</th>
										
										<th data-options="field:'pelaksana',width:100">Pelaksana</th>
											
										<th data-options="field:'dokter_instruksi',width:200">Pelaksana</th>
										
									</tr>
								</thead>
							</table> 
						</div>

						
					</div>
					<!--end all tab-->
					<div class="clearfix"><br></div>
				
					
              
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

  </body>
</html>           