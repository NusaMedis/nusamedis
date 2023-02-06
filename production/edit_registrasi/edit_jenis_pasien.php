<?php
	// LIBRARY
	require_once("../penghubung.inc.php");
	require_once($LIB."bit.php");
	require_once($LIB."login.php");
	require_once($LIB."encrypt.php");
	require_once($LIB."datamodel.php");
	require_once($LIB."currency.php");
	require_once($LIB."dateLib.php");
	require_once($LIB."expAJAX.php");
	require_once($LIB."tampilan.php");	

	//INISIALISASI LIBRARY
	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
	$auth = new CAuth();
	$depNama = $auth->GetDepNama(); 
	$userName = $auth->GetUserName();
	$enc = new textEncrypt();     
	$depId = $auth->GetDepId();

	if ($_GET['cari']) {
		$sql_pasien = "SELECT a.reg_id, b.cust_usr_id, a.id_poli, a.id_poli_asal, b.cust_usr_kode, b.cust_usr_nama, d.instalasi_nama, c.poli_nama, a.reg_tanggal, a.reg_waktu FROM klinik.klinik_registrasi a LEFT JOIN global.global_customer_user b ON a.id_cust_usr = b.cust_usr_id LEFT JOIN global.global_auth_poli c ON a.id_poli = c.poli_id LEFT JOIN global.global_auth_instalasi d ON c.id_instalasi = d.instalasi_id WHERE a.reg_status != 'I9' and a.reg_status != 'I4'  ";
		if ($_GET['cust_usr_kode'] != '') $sql_pasien .= " AND b.cust_usr_kode LIKE ".QuoteValue(DPE_CHAR, '%'.$_GET['cust_usr_kode'].'%');
		if ($_GET['cust_usr_nama'] != '') $sql_pasien .= " AND UPPER(cust_usr_nama) LIKE ".QuoteValue(DPE_CHAR, strtoupper('%'.$_GET['cust_usr_nama'].'%'));
		$sql_pasien .= " ORDER BY b.cust_usr_kode ASC, a.reg_tanggal DESC, a.reg_waktu DESC LIMIT 100";
		$dataPasien = $dtaccess->FetchAll($sql_pasien);
	}
?>

<!DOCTYPE html>
<html lang="en">
	<?php require_once($LAY."header.php") ?>	
	<body class="nav-md" onload="$('#find_pk').focus()">
		<div class="container body">
			<div class="main_container">
				<?php require_once($LAY."sidebar.php") ?>
				<?php require_once($LAY."topnav.php") ?>
				<!-- page content -->
				<div class="right_col" role="main">
					<div class="">
						<div class="page-title">
							<div class="title_left">
								<h3>Menu Admin</h3>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_panel">
									<div class="x_content">
										<form method="GET" class="form-horizontal form-label-left" action="">
											<div class="item form-group">
												<label class="control-label col-md-2 col-sm-2 col-xs-12">Cari Pasien</label>
												<div class="col-md-3 col-sm-3 col-xs-3">
													<input type="text" id="cust_usr_kode" class="form-control" name="cust_usr_kode" value="<?php echo $_GET['cust_usr_kode'];?>" placeholder="No RM">
												</div>
												<div class="col-md-3 col-sm-3 col-xs-3">
													<input type="text" id="cust_usr_nama" class="form-control" name="cust_usr_nama" value="<?php echo $_GET['cust_usr_nama'];?>" placeholder="Nama Pasien">
												</div>
												<input type="submit" name="cari" value="Cari" class="btn btn-primary col-md-2 col-sm-2 col-xs-2">
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php if ($_GET['cari']): ?>
						<div class="">
							<div class="clearfix"></div>
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="x_panel">
										<div class="x_content">
											<table id="datatable-fixed-header" class="table table-striped table-bordered" cellspacing="0" width="100%">
												<thead>
													<tr>
														<th>No</th>
														<th>No RM</th>
														<th>Nama</th>
														<th>Instalasi</th>
														<th>Poli</th>
														<th>Tanggal Registrasi</th>
														<th>Edit</th>
														<!-- <th>Hapus</th> -->
													</tr>
												</thead>
												<tbody>
													<?php foreach ($dataPasien as $key => $value): ?>
														<tr <?php if ($value['id_poli'] == $value['id_poli_asal']) echo 'class="text-danger"' ?>>
															<td><?=$key+1?></td>
															<td><?=$value['cust_usr_kode']?></td>
															<td><?=$value['cust_usr_nama']?></td>
															<td><?=$value['instalasi_nama']?></td>
															<td><?=$value['poli_nama']?></td>
															<td><?=date_db($value['reg_tanggal']).' '.$value['reg_waktu']?></td>
															<td><a href="edit_jenis_pasien_proses.php?cust_usr_kode=<?=$_GET['cust_usr_kode']?>&cust_usr_nama=<?=$_GET['cust_usr_nama']?>&id_reg=<?=$value['reg_id']?>"><center><i class='fa fa-pencil' ></center></a></td>
														<!-- 	<td><a href="proses.php?cust_usr_kode=<?=$_GET['cust_usr_kode']?>&cust_usr_nama=<?=$_GET['cust_usr_nama']?>&id_reg=<?=$value['reg_id']?>&hapus=1"><center><i class='fa fa-trash'></center></a></td> -->
														</tr>
													<?php endforeach ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endif ?>
				</div>
				<!-- /page content -->
				<?php require_once($LAY."footer.php"); ?>
			</div>
		</div>
		<?php require_once($LAY."js.php"); ?>
	</body>
</html>