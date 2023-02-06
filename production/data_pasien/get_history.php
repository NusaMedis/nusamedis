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
if ($_GET["id_cust_usr"]) {
	$sql = "select reg_id,b.id_poli,reg_tanggal,jenis_nama, reg_kode_trans, cust_usr_nama,cust_usr_kode, poli_nama, reg_tipe_rawat, usr_name, b.reg_waktu
			from global.global_customer_user a 
			left join klinik.klinik_registrasi b on a.cust_usr_id=b.id_cust_usr 
			left join global.global_auth_poli c on c.poli_id=b.id_poli
			left join global.global_jenis_pasien d on d.jenis_id=b.reg_jenis_pasien
			left join global.global_auth_user e on e.usr_id = b.id_dokter";
	$sql .= " WHERE b.id_poli!='33' and reg_status <>' ' and id_pembayaran notnull and (reg_utama is null or reg_utama = reg_id) and b.id_poli!='b1b99707e536adf5e57daede3576bb0f' and cust_usr_id=" . QuoteValue(DPE_CHAR, $_GET["id_cust_usr"]);
	$sql .= " order by reg_tanggal desc,reg_waktu desc limit 5";
	// echo $sql;
	$rs = $dtaccess->Execute($sql);
	$row = $dtaccess->FetchAll($rs);
?>
	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="x_panel">
				<div class="x_content">
					<h5><?= str_replace('*', "'", $row[0]['cust_usr_nama']) . ' [' . $row[0]['cust_usr_kode'] . ']' ?></h5>
					<table id="tata" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th width="100px">No</th>
								<th width="250px">Tanggal</th>
								<th width="250px">Waktu</th>
								<th width="350px">Poli Tujuan</th>
								<th width="350px">Dokter</th>
								<th width="250px">Asal Pasien</th>
								<th width="250px">Jenis Pasien</th>

							</tr>
						</thead>
						<tbody>
							<?php $no = 0;
							for ($i = 0; $i < count($row); $i++) {

								if ($row[$i]['reg_id'] != $row[$i - 1]['reg_id']) :

							?>
									<tr>
										<td><?php echo ++$no; ?></td>
										<td><?php echo format_date($row[$i]['reg_tanggal']); ?></td>
										<td><?php echo $row[$i]['reg_waktu']; ?></td>
										<td><?php echo $row[$i]['poli_nama']; ?></td>
										<td><?php echo $row[$i]['usr_name']; ?></td>
										<td><?php if ($row[$i]['reg_tipe_rawat'] == 'J') {
												echo "Rawat Jalan";
											} elseif ($row[$i]['reg_tipe_rawat'] == 'G') {
												echo "IGD";
											} elseif ($row[$i]['reg_tipe_rawat'] == 'I') {
												echo "Rawat Inap";
											} ?>
										</td>
										<td><?= $row[$i]['jenis_nama'] ?></td>
									</tr>
							<?php endif;
							} ?>
							<?php if (count($row) == 0) { ?>
								<tr>
									<td colspan="5">
										<center style="color:silver;">Belum ada history</center>
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