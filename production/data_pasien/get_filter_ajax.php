<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$dtaccess = new DataAccess();
if ($_POST["find_nama"] || $_POST["find_penanggung_jawab"] || $_POST["find_alamat"] || $_POST["find_tgl_lahir"]) {
	$findPasien = str_replace("'", "*", $_POST["find_nama"]);
	$tgl = date('Y-m-d');

	if (strlen(str_replace('_', '', $_POST['find_tgl_lahir'])) == 10) {
		$tgl = date_format(date_create($_POST['find_tgl_lahir']), 'Y-m-d');
	}

	if ($_POST["find_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($findPasien) . "%");
	if ($_POST["find_penanggung_jawab"])  $sql_where[] = "UPPER(cust_usr_penanggung_jawab) like " . QuoteValue(DPE_CHAR, strtoupper($_POST["find_penanggung_jawab"]) . "%");
	if ($_POST["find_alamat"])  $sql_where[] = "UPPER(cust_usr_alamat) like " . QuoteValue(DPE_CHAR, "%" . strtoupper($_POST["find_alamat"]) . "%");
	if ($_POST["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =" . QuoteValue(DPE_CHAR, $tgl);
	$sql_where[] = "cust_usr_nama is not null";
	$sql_where[] = "cust_usr_kode <> '500'";
	if ($sql_where[0])  $sql_where = implode(" and ", $sql_where);
	#end parameter

	$sql = "select cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan, cust_usr_nama, cust_usr_tanggal_lahir, cust_usr_alamat,cust_usr_penanggung_jawab 
				from global.global_customer_user";
	$sql .= " WHERE 1=1";
	$sql .= " and " . $sql_where;
	$sql .= " order by cust_usr_kode desc limit 50";
	//echo $sql;
	$rs = $dtaccess->Execute($sql);
	$row = $dtaccess->FetchAll($rs);
?>
	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="x_panel">
				<div class="x_title">
					<h2>Hasil Pencarian Pasien Lama</h2>
					<div class="clearfix"></div>
				</div>
				<div class="x_content">
					<table id="tata" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th width="100px">No RM</td>
								<th width="250px">Nama</td>
								<th width="100px">Tgl Lahir</td>
								<th width="350px">Alamat</td>
								<th width="250px">Nama Penggung Jawab</td>
								<th width="100px">
									</td>
							</tr>
						</thead>
						<tbody>
							<?php for ($i = 0; $i < count($row); $i++) { ?>
								<tr>
									<td><?php echo $row[$i]['cust_usr_kode']; ?></td>
									<?php $Cust_Usr_Nama = str_replace("*", "'", $row[$i]["cust_usr_nama"]); ?>
									<td><?php echo $Cust_Usr_Nama; ?></td>
									<td><?php echo format_date($row[$i]['cust_usr_tanggal_lahir']); ?></td>
									<td><?php echo $row[$i]['cust_usr_alamat']; ?></td>
									<td><?php echo $row[$i]['cust_usr_penanggung_jawab']; ?></td>
									<td>

										<a onclick="regist('registrasi_pasien.php?usr_id=<?php echo $row[$i]['cust_usr_id']; ?>&status_pasien=L','<?= $row[$i]['cust_usr_id'] ?>')" data-toggle="modal" href='#modal-id' class="col-xs-12 btn btn-xs btn-primary"> Registrasi <i class="fa fa-arrow-right"></i><a>

									</td>
								</tr>
							<?php } ?>
							<?php if (count($row) == 0) { ?>
								<tr>
									<td colspan="5">
										<center>Pasien tidak ditemukan.</center>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php } ?>