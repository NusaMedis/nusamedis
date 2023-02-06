<?php
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
	$url = "edit_jenis_pasien.php?cust_usr_kode=$_GET[cust_usr_kode]&cust_usr_nama=$_GET[cust_usr_nama]&cari=Cari";

	if ($_GET['hapus']) {
		$sql_pasien = "SELECT id_pembayaran FROM klinik.klinik_registrasi WHERE id_poli = id_poli_asal AND reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
		$dataPasien = $dtaccess->Fetch($sql_pasien);

		if ($dataPasien) {
			$sql_folio = "SELECT count(*)  AS total FROM klinik.klinik_folio WHERE id_pembayaran = ".QuoteValue(DPE_CHAR, $dataPasien['id_pembayaran']);
			$dataFolio = $dtaccess->Fetch($sql_folio);

			$pesan = "Tindakan Untuk Poli Ini Masih ".$dataFolio['total'].", Beserta Tindakan Penunjangnya";
		} else {
			$sql_folio = "SELECT count(*)  AS total FROM klinik.klinik_folio WHERE id_reg = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
			$dataFolio = $dtaccess->Fetch($sql_folio);

			$pesan = "Tindakan Untuk Poli Ini Masih ".$dataFolio['total'];
		}

		if ($dataFolio['total'] > 0) {
			$pesan = $pesan;
		} else {
			$sql_inacbg = "delete from klinik.klinik_inacbg where id_reg = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
      $dtaccess->Execute($sql_inacbg);

			$sql_perawatan = "delete from klinik.klinik_perawatan where id_reg = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
      $dtaccess->Execute($sql_perawatan);

			$sql_registrasi = "delete from klinik.klinik_registrasi where reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
      $dtaccess->Execute($sql_registrasi);

			$pesan = "Berhasil Hapus Registrasi ".$_GET['id_reg'];
		}

		echo "
			<script>
				window.alert('".$pesan."')
	    	window.location.href='".$url."'
			</script>
		";
	} else {
    if ($_POST['simpan']) {
      /* UPDATE GLOBAK CUSTOMER USER */
      $sql = "UPDATE global.global_customer_user SET cust_usr_kode = ".QuoteValue(DPE_CHAR, $_POST['cust_usr_kode']).", cust_usr_nama = ".QuoteValue(DPE_CHAR, $_POST['cust_usr_nama']).", cust_usr_alamat = ".QuoteValue(DPE_CHAR, $_POST['cust_usr_alamat'])." WHERE cust_usr_id = ".QuoteValue(DPE_CHAR, $_POST['cust_usr_id']);
      $dtaccess->Execute($sql);
      /* UPDATE GLOBAK CUSTOMER USER */

      /* UPDATE KLINIK REGISTRASI */
      $sql = "UPDATE klinik.klinik_registrasi SET reg_tanggal = ".QuoteValue(DPE_CHAR, date_db($_POST['reg_tanggal'])).", reg_waktu = ".QuoteValue(DPE_CHAR, $_POST['reg_waktu']).", reg_jenis_pasien = ".QuoteValue(DPE_CHAR, $_POST['reg_jenis_pasien']).", reg_tipe_rawat = ".QuoteValue(DPE_CHAR, $_POST['reg_tipe_rawat']).", id_poli = ".QuoteValue(DPE_CHAR, $_POST['id_poli']).", id_poli_asal = ".QuoteValue(DPE_CHAR, $_POST['id_poli_asal'])." WHERE reg_id = ".QuoteValue(DPE_CHAR, $_POST['reg_id']);
      $dtaccess->Execute($sql);
      /* UPDATE KLINIK REGISTRASI */

      header('Location: '.$url);
    }

		$sql_pasien = "SELECT b.cust_usr_id, a.reg_id, b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, a.reg_tanggal, a.reg_waktu, a.reg_tipe_rawat, c.id_instalasi, a.id_poli, a.reg_jenis_pasien, a.id_poli_asal FROM klinik.klinik_registrasi a LEFT JOIN global.global_customer_user b ON a.id_cust_usr = b.cust_usr_id LEFT JOIN global.global_auth_poli c ON a.id_poli = c.poli_id WHERE a.reg_id = ".QuoteValue(DPE_CHAR, $_GET['id_reg']);
		$dataPasien = $dtaccess->Fetch($sql_pasien);

	  /* JENIS PASIEN */
	  $sql_jenis_pasien = "SELECT jenis_id, jenis_nama FROM global.global_jenis_pasien ORDER BY jenis_id ASC";
	  $dataJenisPasien = $dtaccess->FetchAll($sql_jenis_pasien);
	  /* JENIS PASIEN */

	  /* POLI */
	  $sql_poli = "SELECT poli_id, poli_nama FROM global.global_auth_poli WHERE poli_tipe = ".QuoteValue(DPE_CHAR, $dataPasien['reg_tipe_rawat'])." ORDER BY poli_nama";
	  $dataPoli = $dtaccess->FetchAll($sql_poli);
	  /* POLI */

    /* POLI ASAL */
    $sql_poli_asal = "SELECT poli_id, poli_nama FROM global.global_auth_poli ORDER BY poli_nama";
    $dataPoliAsal = $dtaccess->FetchAll($sql_poli_asal);
    /* POLI ASAL */
	}
?>

<!DOCTYPE html>
<html lang="en">
	<?php require_once($LAY."header.php") ?>	
	<body class="nav-md">
		<div class="container body">
			<div class="main_container">
				<?php require_once($LAY."sidebar.php") ?>
				<?php require_once($LAY."topnav.php") ?>
				<!-- page content -->
				<div class="right_col" role="main">
					<div class="">
						<div class="page-title">
							<div class="title_left">
								<h3>Menu Admin Proses</h3>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_panel">
									<div class="x_content">
										<form method="POST" class="form-horizontal form-label-left" action="">
                      <input type="hidden" name="cust_usr_id" value="<?= $dataPasien['cust_usr_id'] ?>">
                      <input type="hidden" name="reg_id" value="<?= $dataPasien['reg_id'] ?>">
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3"><i>~ Data Pasien ~</i></label>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">No RM</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<input type="text" id="cust_usr_kode" class="form-control" name="cust_usr_kode" value="<?php echo $dataPasien['cust_usr_kode'];?>" placeholder="No RM" readonly="">
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Nama Pasien</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<input type="text" id="cust_usr_nama" class="form-control" name="cust_usr_nama" value="<?php echo $dataPasien['cust_usr_nama'];?>" placeholder="Nama Pasien" readonly="">
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Alamat Pasien</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<input type="text" id="cust_usr_alamat" class="form-control" name="cust_usr_alamat" value="<?=$dataPasien['cust_usr_alamat']?>" placeholder="Alamat Pasien" readonly="">
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3"><i>~ Data Registrasi ~</i></label>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Tanggal Registrasi</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<div class='input-group date' id='datepicker'>
														<input type="text" id="reg_tanggal" class="form-control" name="reg_tanggal" data-inputmask="'alias': 'dd-mm-yyyy'" value="<?php echo date_db($dataPasien['reg_tanggal']);?>" placeholder="Tanggal Registrasi">
														<span class="input-group-addon"><span class="fa fa-calendar" readonly=""></span></span>
													</div>
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Waktu Registrasi</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<input type="text" id="reg_waktu" class="form-control" name="reg_waktu" data-inputmask="'mask': '99:99:99'" value="<?php echo $dataPasien['reg_waktu'];?>" placeholder="Waktu Registrasi" readonly="">
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Jenis Pasien</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<select name="reg_jenis_pasien" id="reg_jenis_pasien" class="form-control">
                            <option value="">--- Pilih Jenis Pasien ---</option>
                            <?php for($i=0;$i<count($dataJenisPasien);$i++){ ?>
                              <option value="<?php echo $dataJenisPasien[$i]["jenis_id"];?>" <?php if($dataJenisPasien[$i]["jenis_id"]==$dataPasien["reg_jenis_pasien"]) echo "selected"; ?>><?php echo $dataJenisPasien[$i]["jenis_nama"];?></option>
                            <?php } ?>
                          </select>  
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Tipe Rawat</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<select name="reg_tipe_rawat" id="reg_tipe_rawat" class="form-control" readonly="">
                            <option value="">--- Pilih Tipe Rawat ---</option>
                            <option value="J"<?php if ($dataPasien['reg_tipe_rawat'] == 'J') echo 'selected'; ?>>Rawat Jalan</option>
                            <option value="G"<?php if ($dataPasien['reg_tipe_rawat'] == 'G') echo 'selected'; ?>>Rawat Darurat</option>
                            <option value="I"<?php if ($dataPasien['reg_tipe_rawat'] == 'I') echo 'selected'; ?>>Rawat Inap</option>
                          </select>  
												</div>
											</div>
											<div class="item form-group">
												<label class="control-label col-md-3 col-sm-3 col-xs-3">Poli Klinik</label>
												<div class="col-md-9 col-sm-9 col-xs-9">
													<select name="id_poli" id="id_poli" class="form-control">	
                            <option value="">--- Pilih Poli ---</option>
    				                <?php for($i=0;$i<count($dataPoli);$i++){ ?>
            	                <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$dataPasien["id_poli"]) echo "selected"; ?>><?=$dataPoli[$i]["poli_nama"];?></option>
  				                  <?php } ?>
  			                  </select>
												</div>
											</div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-3">Poli Klinik Asal</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                          <select name="id_poli_asal" id="id_poli_asal" class="form-control"> 
                            <option value="">--- Pilih Poli Asal ---</option>
                            <?php for($i=0;$i<count($dataPoliAsal);$i++){ ?>
                              <option value="<?php echo $dataPoliAsal[$i]["poli_id"];?>" <?php if($dataPoliAsal[$i]["poli_id"]==$dataPasien["id_poli_asal"]) echo "selected"; ?>><?=$dataPoliAsal[$i]["poli_nama"];?></option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
											<div class="item form-group">
												<input type="submit" name="simpan" value="Simpan" class="btn btn-success col-md-2 col-sm-2 col-xs-2 pull-right">
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /page content -->
				<?php require_once($LAY."footer.php"); ?>
			</div>
		</div>
		<?php require_once($LAY."js.php"); ?>
	</body>
</html>