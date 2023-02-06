<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "expAJAX.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();

$rawat_id = $_GET['asd'];
$sql = "SELECT id_reg, id_cust_usr, rawat_pemeriksaan_fisik from klinik.klinik_perawatan where rawat_id = '$rawat_id'";
$rawat = $dtaccess->Fetch($sql);

$sql = "SELECT *, e.waktu_asmed from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join global.global_auth_user d on a.id_dokter = d.usr_id
left join klinik.klinik_perawatan e on a.reg_id = e.id_reg
 where reg_id = ".QuoteValue(DPE_CHAR,$rawat['id_reg']);
$reg_data = $dtaccess->Fetch($sql);

$sql = "SELECT * from klinik.klinik_registrasi_kontrol where reg_utama = ".QuoteValue(DPE_CHAR,$rawat['id_reg']);
$dataKontrol = $dtaccess->Fetch($sql);

$sql = "SELECT b.diagnosa_nama, diagnosa_short_desc from klinik.klinik_perawatan_diagnosa a
left join klinik.klinik_diagnosa b on a.id_diagnosa = b.diagnosa_id 
where id_rawat = '$rawat_id'";
$diagnosa = $dtaccess->FetchAll($sql);

$sql = "SELECT b.procedure_nama, procedure_short_desc from klinik.klinik_perawatan_procedure a
left join klinik.klinik_procedure b on a.id_procedure = b.procedure_id
where id_rawat = '$rawat_id'";
$procedure = $dtaccess->FetchAll($sql);

$obgyn = unserialize($reg_data['rawat_obgyn']);

$umur = explode('~', $reg_data['cust_usr_umur']);

	$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";  

  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;

  if($konfigurasi["dep_logo"]!="n") {
    $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
    $fotoName = $lokasi."/default.jpg"; 
  } else { 
    $fotoName = $lokasi."/default.jpg"; 
  }

	
?>
<style>
	.regards {
		position: relative;
		float: right;
		padding-right: 20px;
		text-align: center;
	}

	body {
		margin: 0;
		/* overflow: ; */
		font-family: Arial, Helvetica, sans-serif;
		height: 800px;
		/* width: 800px; */
		white-space: nowrap;
		font-size: 12px;
	}

	@page {
		size: auto;
		/* auto is the initial value */
		margin: 0cm;

	}

	.block {
		display: inline-block;
		padding: 1px;
	}

	td.judul{
		width: 20%;
	}

	td{
		vertical-align: top;
	}

	td.gap{
		min-width: 40%;
	}

	img{
        width: 2cm;
      }

	  table{
		font-size: 12px;
		width: 100%;
	  }

	  li{
	  	margin-left: -25px;
	  }
</style>
<script>
	//window.print();
</script>
<!-- onload="setTimeout('self.close()',5000)" -->

<body>
	<div class="wrapper" style="width: 18cm; margin : auto;">

		<div style="margin-top: 20px;">
			<table>
				<tr>
					<td width="20%">
						<center>
							<img src="<?php echo $fotoName;?>">
						</center>
					</td>
					<td style="padding-left: 20px;">
						<H1><?php echo $konfigurasi['dep_nama']; ?></H1>
						<h3><?php echo $konfigurasi['dep_kop_surat_1']." Telp. ".$konfigurasi['dep_kop_surat_2']; ?></h3>
					</td>
				</tr>
			</table>
		</div>
		<div style="margin-top: 20px;">
			<center><h2>SURAT KONTROL IBU</h2></center>
		</div>
		<div style="margin-top: 20px;">
			<table style="width: 100%;">
				<tr>
					<td>&nbsp;</td>
					<td style="text-align: right;">Jombang, <?=date_format(date_create($reg_data['reg_tanggal']), 'd-m-Y')?></td>
				</tr>
				<tr>
					<td>Kepada</td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>
						Yth. <?=$reg_data['cust_usr_nama']?><br>
						Di. Jombang
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						Assalamu'alaikum Wr. Wb.<br>
						Mohon Kontrol
					</td>
					<td></td>
				</tr>
			</table>

			<table style="width: 100%;">
				<tr>
					<td class="judul">No. Register</td>
					<td> : </td>
					<td><?=$dataKontrol['reg_nomor_kontrol']?></td>
				</tr>
				<tr>
					<td>Nama Pasien</td>
					<td> : </td>
					<td><?=$reg_data['cust_usr_nama']?></td>
				</tr>
				<tr>
					<td>Nomor RM</td>
					<td> : </td>
					<td><?=$reg_data['cust_usr_kode']?></td>
				</tr>
				<tr>
					<td>Umur</td>
					<td> : </td>
					<td><?=$umur[0]?> Tahun</td>
				</tr>
				<tr>
					<td>Nama Suami</td>
					<td> : </td>
					<td><?=$reg_data['cust_usr_penanggung_jawab']?></td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td> : </td>
					<td><?=$reg_data['cust_usr_alamat']?></td>
				</tr>
				<tr>
					<td>Diagnosa</td>
					<td> : </td>
					<td>
						<ol style="margin: 0px">
						<?php
							for($i = 0; $i < count($diagnosa); $i++){
								?>
								<li><?=$diagnosa[$i]['diagnosa_short_desc']?></li>
								<?php
							}
						?>
						</ol>
					</td>
				</tr>
				<tr>
					<td>Tindakan</td>
					<td> : </td>
					<td>
						<ol style="margin: 0px">
						<?php
							for($i = 0; $i < count($procedure); $i++){
								?>
								<li><?=$procedure[$i]['procedure_short_desc']?></li>
								<?php
							}
						?>
						<?php if($obgyn['planning_penatalaksanaan']){ ?><li><?=$obgyn['planning_penatalaksanaan']?></li><?php }?>
						</ol>
					</td>
				</tr>
				<tr>
					<td>Kontrol Tgl.</td>
					<td> : </td>
					<td><?=date_format(date_create($reg_data['rawat_rujuk_tanggal_kembali']), 'd-m-Y')?></td>
				</tr>
			</table>
			<table style="width: 100%;">
				<tr>
					<td>Sekian Terima kasih.</td>
					<td class="gap"></td>
					<td></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td class="gap"></td>
					<td></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td class="gap"></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td>
						<center>
							Wassalam,
							<br>
							<br>
							<br>
							<br>
							(___________________________)
						</center>
					</td>
				</tr>
				
			</table>

		</div>
		

</body>

</html>