<?php
     //LIBRARY 
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php"); 
     require_once($LIB."tree.php");
     
     
     //INISIALISAI AWAL LIBRARY
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $err_code = 0;                   
     $auth = new CAuth();
     $depNama = $auth->GetDepNama(); 
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();	
     $table = new InoTable("table","100%","left");   
     $userData = $auth->GetUserData(); 
     $depId = $auth->GetDepId();
     $depLowest = $auth->GetDepLowest();
     $userName = $auth->GetUserName();
     

     $sql = "select * from gl.gl_buffer_transaksi where (flag_jurnal = 'PEJ' or flag_jurnal = 'PEI' or flag_jurnal = 'PEG') and id_tra in(select tra_id from gl.gl_buffer_transaksidetil where prk_id = '01010101010106') and tanggal_tra >= '2020-07-01' and tanggal_tra <= '2020-07-31' and id_pembayaran_det not in(select id_pembayaran_det from ar_ap.ar_trans where flag_jurnal = 'A') order by tanggal_tra asc";
     $dataPiutang = $dtaccess->FetchAll($sql);
   $tableHeader = "FK Tipe Rawat in Folio";
?>

<script language="Javascript">

var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=800,height=600,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=800,height=600,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}

function ProsesCetak(id) {
  BukaWindow('bank_keluar_cetak.php?id='+id+'','Cetak');
	document.location.href='<?php echo $thisPage;?>';
}

</script>
<!DOCTYPE html>
<html lang="en">
	<?php require_once($LAY."header.php") ?>
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
						<div class="clearfix"></div>
						<!-- row filter -->
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="x_panel">
									<div class="x_content">
										<form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
										<div class="col-md-4 col-sm-6 col-xs-12">
											<label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal Registrasi (DD-MM-YYYY)</label>
											<div class='input-group date' id='datepicker'>
												<input name="tgl_awal" type='text' class="form-control"
												value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
												<span class="input-group-addon">
													<span class="fa fa-calendar">
													</span>
												</span>
											</div>
											
											<label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal Registrasi (DD-MM-YYYY)</label>
											<div class='input-group date' id='datepicker2'>
												<input  name="tgl_akhir"  type='text' class="form-control"
												value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
												<span class="input-group-addon">
													<span class="fa fa-calendar">
													</span>
												</span>
											</div>
										</div>
										
										<div class="col-md-4 col-sm-6 col-xs-12">
											<label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
											<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
										</div>
										
									</form>
								</div>
							</div>
						</div>
					</div>
					<!-- //row filter -->
					<div class="col-md-12 col-sm-12 col-xs-12">
						<div class="x_panel">
							<div class="x_content">
								<table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<td>No</td>
											<td>Tanggal</td>
											<td>Kode Jurnal</td>
											<td>Keterangan</td>
											<td>Piutang Asuransi</td>
											<td>Edit</td>
										</tr>
									</thead>
									<tbody>
									<?php 
										for ($i = 0; $i < count($dataPiutang); $i++) { 
											$sql = "select * from gl.gl_buffer_transaksidetil where prk_id = '01010101010106' and tra_id = ".QuoteValue(DPE_CHAR,$dataPiutang[$i]['id_tra']);
											$Nominal = $dtaccess->Fetch($sql);
									?>
										<tr>
											<td><?php echo $i+1 ?></td>
											<td><?php echo $dataPiutang[$i]['tanggal_tra'] ?></td>
											<td><?php echo $dataPiutang[$i]['ref_tra'] ?></td>
											<td><?php echo $dataPiutang[$i]['ket_tra'] ?></td>
											<td><?php echo $Nominal['jumlah_trad'] ?></td>
											<td><a href="piutang_bermasalah_edit.php?id=<?php echo $dataPiutang[$i]['id_tra'] ?>">Edit</a></td> 
										</tr>
									<?php } ?>
									</tbody>
								</table>
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
<?php require_once($LAY."js.php") ?>
</body>
</html>