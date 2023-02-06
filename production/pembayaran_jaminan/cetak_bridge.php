<?php
     // Library
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "currency.php");
require_once($LIB . "encrypt.php");
//  require_once($LIB."expAJAX.php"); 
require_once($LIB . "tampilan.php");

// Inisialisasi Lib
$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$enc = new textEncrypt();
$userData = $auth->GetUserData();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();
$tglSekarang = date("d-m-Y");
$depLowest = $auth->GetDepLowest();

//Data Pasien
$sql = "select kondisi_akhir_pasien_nama, a.*, b.*,c.usr_name, d.*, inacbg_surat_rujukan, h.no_sep ,j.id_pembayaran_ibu,j.id_pembayaran from klinik.klinik_registrasi a 
left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr 
left join global.global_auth_user c on c.usr_id = a.id_dokter
left join global.global_kondisi_akhir_pasien d on d.kondisi_akhir_pasien_id = a.reg_status_kondisi
left join klinik.klinik_inacbg f on a.reg_id = f.id_reg 
left join klinik.klinik_perawatan g on a.reg_id = g.id_reg
left join klinik.klinik_sep h on a.reg_id = h.sep_reg_id
 left join klinik.klinik_folio j on a.reg_id = j.id_reg


where rawat_id = " . QuoteValue(DPE_CHAR, $_GET['id']);
$dataPasien = $dtaccess->Fetch($sql);



$sql = "select * from klinik.klinik_jenis_inacbg order by jenis_inacbg_urut asc";
$dataVariabel = $dtaccess->FetchAll($sql);


     //data Folio
   /*  for ($i=0; $i < count($dataVariabel); $i++) { 
     $sql = "select sum(fol_nominal) as tagihan from klinik.klinik_folio a
     left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 
     where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran'])." and b.biaya_jenis = ".QuoteValue(DPE_CHAR,$dataVariabel[$i]['jenis_inacbg_id']);
     $dataFolio = $dtaccess->FetchAll($sql);
     // ////echo $sql;
 }*/

if (is_null($dataPasien['id_pembayaran_ibu']) || $dataPasien['id_pembayaran_ibu']==""  || $dataPasien['id_pembayaran_ibu']==" " ) {
        // code...
 	$sql = "select sum(fol_nominal) as total from klinik.klinik_folio where  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran'])."  and id_biaya <> '9999999'";
 	$TindakanJasa = $dtaccess->Fetch($sql);

 	////echo $sql;
 }
 else{
 	$sql = "select sum(fol_nominal) as total from klinik.klinik_folio where  id_pembayaran_ibu = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran_ibu'])."  and id_biaya <> '9999999'";
 	$TindakanJasa = $dtaccess->Fetch($sql);

 	////echo $sql;
 }
 //echo $sql;

 // $sql = "select sum(fol_nominal) as total from klinik.klinik_folio where  id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran'])."  and id_biaya <> '9999999'";
 // $TindakanJasa = $dtaccess->Fetch($sql);

 $JasaRS = $TindakanJasa['total'] * 0.1;
  // echo $JasaRS;


//Data Perawatan
$sql = "select rawat_id from klinik.klinik_perawatan where rawat_id = " . QuoteValue(DPE_CHAR, $_GET['id']);
$dataRawat = $dtaccess->Fetch($sql);
//Diagnosa Primer
$sql = "select diagnosa_nama, diagnosa_nomor, a.* from klinik.klinik_perawatan_diagnosa a 
 left join klinik.klinik_diagnosa b on b.diagnosa_id = a.id_diagnosa where id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat['rawat_id']) . " and rawat_diagnosa_status = 'Primer'";
$dataDiagnosaPrimer = $dtaccess->Fetch($sql);

//echo $sql;
//Diagnosa Sekunder
$sql = "select diagnosa_nama, diagnosa_nomor, a.* from klinik.klinik_perawatan_diagnosa a 
 left join klinik.klinik_diagnosa b on b.diagnosa_id = a.id_diagnosa where id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat['rawat_id']) . " and rawat_diagnosa_status <> 'Primer'";
$dataDiagnosaSekunder = $dtaccess->FetchAll($sql);
//echo $sql;
//Procedure
$sql = "select a.*,procedure_nama, procedure_nomor from klinik.klinik_perawatan_procedure a 
 left join klinik.klinik_procedure b on b.procedure_id = a.id_procedure where id_rawat = " . QuoteValue(DPE_CHAR, $dataRawat['rawat_id']) . " order by rawat_procedure_urut asc";
$dataProcedure = $dtaccess->FetchAll($sql);
//echo $sql;
// Konfig
$sql = "select * from global.global_departemen";
$Konfig = $dtaccess->Fetch($sql);

    // $sql = "select * from klinik.klinik_perawatan_edukasi where id_reg = ".QuoteValue(DPE_CHAR,$_GET['id']);
    // $sql .= " order by rawat_edukasi_when_create asc";
    // $dataEdukasii = $dtaccess->Fetch($sql);

 $RM = $dataPasien['cust_usr_kode'];
 $lokasi = $ROOT."/gambar/asset_ttd/".$RM.".jpg";   
    //echo $lokasi;
    //for ($i=0; $i < count($dataEdukasii); $i++) {
 $dokter = $_POST['id_dokter'];
 $lokasiDokter = "asset_ttd"."/".$dokter.".jpg";
    //echo $_POST['id_dokter']."<br>";
    //}

 $tableHeader = 'Cetak';
 ?>
 <!-- Bootstrap -->
 <link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
 <!-- Font Awesome -->
 <link href="<?php echo $ROOT; ?>assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
 <!DOCTYPE html>
 <html>
 <head>
 	<style type="text/css">
 	body{
 		padding: 50px;
 	}

 	#qq{
 		border: solid;
 		border-color: black;
 		border-width: 2px;
 		font-size: 13px;
 		height: 30px;
 		margin-bottom: 0px !important;
 	}

 	td{
 		font-size: 14px;
 		vertical-align: middle;

 	}

 	table.diagnosa td{
 		padding: 5px 0;
 	}

 	table.dtRS td{
 		border: 1px solid black;
 		border-collapse: collapse;
 	}

 	table.addPad td{
 		padding: 2px;
 	}

 </style>
 <?php //require_once($LAY."header.php") ?>
 <title>Cetak Bridge</title>
 <script type="text/javascript">
 	window.print();
 </script>
</head>
<body>

	<table width="100%">
		<tr>
			<td><b>FORMULIR DATA VARIABEL INA-CBG's</b></td>
			<td>
				<table width="100%" class="dtRS">
					<tr>

						<td width="40%">Kode RS</td>
						<td>&nbsp;:&nbsp;</td>
						<td><?php echo $Konfig['dep_kode_prop']; ?></td>
					</tr>
					<tr>
						<td width="40%">Nama RS</td>
						<td>&nbsp;:&nbsp;</td>
						<td><?php echo $Konfig['dep_nama']; ?></td>
					</tr>
					<tr>
						<td width="40%">Alamat</td>
						<td>&nbsp;:&nbsp;</td>
						<td><?php echo $Konfig['dep_kop_surat_1']; ?></td>
					</tr>
					<tr>
						<td width="40%">Kab.Prop</td>
						<td>&nbsp;:&nbsp;</td>
						<td>Jombang / JAWA TIMUR</td>
					</tr>
					<tr>
						<td width="40%">Tipe RS</td>
						<td>&nbsp;:&nbsp;</td>
						<td>C</td>
					</tr>
				</table>
			</td>
		</tr>

		<table width="100%" class="addPad">
			<tr>
				<td width="3%" align="left">1.</td>
				<td width="25%">No. Rekam Medis</td>
				<td><?php echo substr($dataPasien['cust_usr_kode'], 2) ; ?></td>
			</tr>
			<?php
			if (is_null($dataPasien["cust_usr_nama_txt"])) {
                  	 	# code...
				$nama=$dataPasien["cust_usr_nama"];
			}
			else{
				$nama=$dataPasien["cust_usr_nama_txt"];
			} ?>
			<tr>
				<td width="3%" align="left">2.</td>
				<td width="25%">Nama Pasien</td>
				<td><?=$nama?></td>
			</tr>
			<tr>
				<td width="3%" align="left">3.</td>
				<td width="25%">Jenis Kelamin *</td>
				<?php if ($dataPasien['cust_usr_jenis_kelamin'] == 'L'){ ?>
					<td>Laki-laki</td>
				<?php } else { ?>
					<td>Perempuan</td>
				<?php } ?>
			</tr>
			<tr>
				<td width="3%" align="left">4.</td>
				<td width="25%">Tanggal Lahir</td>
				<td><?php echo date_db($dataPasien['cust_usr_tanggal_lahir']); ?></td>
			</tr>
			<!-- <tr>
				<td width="3%" align="left">5.</td>
				<td width="25%">Model Pembayaran</td>
				<td><?php //if($dataPasien['reg_tipe_jkn'] == '1') echo 'Jamkesmas'; else if($dataPasien['reg_tipe_jkn'] == '2') echo 'Non Jamkesmas'; else echo '-'; ?></td>
			</tr> -->
			<tr>
				<td width="3%" align="left">5.</td>
				<td width="25%">Nomor Kepesertaan</td>
				<td><?php echo $dataPasien['cust_usr_no_jaminan']; ?></td>
			</tr>
			<!-- <tr>
				<td width="3%" align="left">7.</td>
				<td width="25%">Nomor SEP</td>
			  	<td><?=($dataPasien['reg_no_sep'] != '' || $dataPasien['reg_no_sep'] != null ) ? $dataPasien['reg_no_sep'] : $dataPasien['no_sep']?></td>
			  </tr> -->
			  <tr>
			  	<td width="3%" align="left">6.</td>
			  	<td width="25%">Jenis Perawatan *</td>
			  	<td><?php echo 'Rawat Inap' ?></td>
			  </tr>
			<!-- <tr>
				<td width="3%" align="left">9.</td>
				<td width="25%">Kelas Perawatan</td>
			  	<td><?php //echo '-' ?></td>
			  </tr> -->
			  <tr>
			  	<td width="3%" align="left">7.</td>
			  	<td width="25%">Tanggal Masuk</td>
			  	<td><?php echo date_db($dataPasien['reg_tanggal']); ?></td>
			  </tr>
			  <tr>
			  	<td width="3%" align="left">8.</td>
			  	<td>Tanggal Keluar</td>
			  	<td><?php echo date_db($dataPasien['reg_tanggal_pulang']); ?></td>
			  </tr>
			<!-- <tr>
				<td width="3%" align="left">12.</td>
				<td>Cara Keluar</td>
				<td><?php //echo $dataPasien['kondisi_akhir_pasien_nama']; ?></td>
			</tr> -->
			<tr>
				<td width="3%" align="left">9.</td>
				<td width="25%">Berat Lahir</td>
				<td><?php echo $dataPasien['cust_berat_lahir']; ?> g</td>
			</tr>
			<!-- <tr>
				<td width="3%" align="left">14.</td>
				<td width="25%">Total Biaya Rumah Sakit</td>
				<td>Rp <?php //echo currency_format(($TindakanJasa['total']), 0, ',', '.');?></td>
			</tr> -->
			<!-- <tr>
				<td width="3%" align="left">15.</td>
				<td width="25%">Surat Rujukan</td>
				<td><?=($dataPasien['inacbg_surat_rujukan'] == '1') ? "Ada" : "Tidak Ada"?></td>
			</tr> -->
			<tr>
				<td width="3%" align="left">10.</td>
				<td width="25%">Total Rincian Per Item</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4" style="padding-left: 30px">


					<table border="1" width="80%">
						<tr>
							<td align="center" width="3%">No</td>
							<td align="center" width="72%">Jenis Pelayanan</td>
							<td align="center" width="25%">Biaya (Rp.)</td>
						</tr>
						<?php for ($i=0; $i < count($dataVariabel); $i++) { 
							/* $sql = "select sum(fol_nominal) as tagihan from klinik.klinik_folio where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran'])." and fol_jenis = ".QuoteValue(DPE_CHAR,$dataVariabel[$i]['jenis_inacbg_id']);*/

							if (is_null($dataPasien['id_pembayaran_ibu']) || $dataPasien['id_pembayaran_ibu']==""  || $dataPasien['id_pembayaran_ibu']==" " ) {
        // code...
								$sql = "select sum(fol_nominal) as tagihan from klinik.klinik_folio a
								left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 

								where a.id_pembayaran = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran'])." and b.biaya_jenis = ".QuoteValue(DPE_CHAR,$dataVariabel[$i]['jenis_inacbg_id']);
								$dataFolio = $dtaccess->Fetch($sql);

								//echo $sql;

							}
							else{
								$sql = "select sum(fol_nominal) as tagihan from klinik.klinik_folio a
								left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id 

								where a.id_pembayaran_ibu = ".QuoteValue(DPE_CHAR,$dataPasien['id_pembayaran_ibu'])." and b.biaya_jenis = ".QuoteValue(DPE_CHAR,$dataVariabel[$i]['jenis_inacbg_id']);
								$dataFolio = $dtaccess->Fetch($sql);
								////echo $sql;

							}





							?>
							<tr>
								<td><?php echo $i+1 ?></td>
								<td><?php echo $dataVariabel[$i]['jenis_inacbg_nama']; ?></td>
								<?php if($dataVariabel[$i]['jenis_inacbg_nama']=='Penunjang'){?>

									<?php if($dataPasien['reg_tipe_rawat']=='J'){?>


										<td>Rp.<?php echo currency_format($dataFolio['tagihan']); ?></td>

									<?php  }
									elseif($dataPasien['reg_tipe_rawat']=='I'){
										$JasaRS = $JasaRS + $dataFolio['tagihan'];
										$dataFolio['tagihan'] = $JasaRS;
										?>
										<td>Rp.<?php echo currency_format($JasaRS); ?></td>

									<?php  }
									?>

								<?php }
								else{ ?>
									<td>Rp.<?php echo currency_format($dataFolio['tagihan']); ?></td>
								<?php } ?>
							</tr>
							<?php 
							$grandTotal += $dataFolio['tagihan'];
						} ?>

						<tr>
							<td colspan="2">Total Rincian RS</td>
							<td>Rp.<?php echo currency_format($grandTotal); ?></td>
						</tr>
					</table>
				</td>
			</tr>
					<tr>
				<td colspan="4">
					<table width="100%" class="diagnosa">
						<tr>
							<td colspan="2">&nbsp;</td>
							<td align="center">Diagnosa Dokter</td>
							<td>Kode ICD-10</td>
						</tr>
						<tr>
							<td width="3%">11.</td>
							<td width="15%">Diagnosa Primer</td>
							<td width="72%" align="center"><input type="text" name="diagnosa_primer_nama" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaPrimer['diagnosa_nama']; ?>"></td>
							<td width="10%" align="center"><input type="text" name="diagnosa_primer_kode" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaPrimer['diagnosa_nomor']; ?>"></td>
						</tr>
						<tr>
							<td>12.</td>
							<td>Diagnosa Sekunder</td>
							<td align="center"> <input type="text" name="diagnosa_sekunder0_nama" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaSekunder[0]['diagnosa_nama']; ?>"></td>
							<td align="center"> <input type="text" name="diagnosa_sekunder0_kode" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaSekunder[0]['diagnosa_nomor']; ?>"></td>
						</tr>
						<?php if (count($dataDiagnosaSekunder) > 1) {
							for ($i = 1; $i < count($dataDiagnosaSekunder); $i++) {
						?>
								<tr>
									<td colspan="2">&nbsp;</td>
									<td align="center"> <input type="text" name="diagnosa_sekunder1_nama" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaSekunder[$i]['diagnosa_nama']; ?>"></td>
									<td align="center"> <input type="text" name="diagnosa_sekunder1_kode" class="form-control form-group" id="qq" value="<?php echo $dataDiagnosaSekunder[$i]['diagnosa_nomor']; ?>"></td>
								</tr>
						<?php
							}
						}
						?>





						<tr>
							<td colspan="2">&nbsp;</td>
							<td align="center"> Uraian Prosedur / Tindakan</td>
							<td>Kode ICD-9CM</td>
						</tr>
						<tr>
							<td>13.</td>
							<td>Prosedur / Tindakan</td>
							<td align="center"><input type="text" name="procedure0_nama" class="form-control form-group" id="qq" value="<?php echo $dataProcedure[0]['procedure_nama']; ?>"></td>
							<td align="center"><input type="text" name="procedure0_kode" class="form-control form-group" id="qq" value="<?php echo $dataProcedure[0]['procedure_nomor']; ?>"></td>
						</tr>

						<?php if (count($dataProcedure) > 1) {
							for ($i = 1; $i < count($dataProcedure); $i++) {
						?>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td align="center"><input type="text" name="procedure<?= $i ?>_nama" class="form-control form-group" id="qq" value="<?php echo $dataProcedure[$i]['procedure_nama']; ?>"></td>
									<td align="center"><input type="text" name="procedure<?= $i ?>_kode" class="form-control form-group" id="qq" value="<?php echo $dataProcedure[$i]['rawat_procedure_kode']; ?>"></td>
								</tr>
						<?php
							}
						}
						?>


					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">
					<table style="width: 100%">
						<tr>
							<td colspan="3">&nbsp;</td>
							<td align="center">Jombang, <?php echo date_db($dataPasien['reg_tanggal_pulang']); ?></td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td align="center">Pasien</td>
							<td align="center">Dokter Penanggung Jawab</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
							<td align="center">
								<br>
								<br>
								<br>
								<br>
								<br>
								<br>
								( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
							</td>
							<td align="center">
								<br>
								<br>
								<br>
								<br>
								<br>
								<br>
								( <?php echo $dataPasien['usr_name']; ?> )
							</td>
						</tr>
					</table>
				</td>
			</tr>

		</table>

	</body>
	</html>