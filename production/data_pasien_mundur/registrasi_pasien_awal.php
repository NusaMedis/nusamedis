<?php    
     ##################	NOTE #####################
	 ## insert cust usr kode sebagai primary	##
	 ## update untuk tambah berdasar primary	##
	 #############################################
	
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
  	 $lokasi = $ROOT."gambar/foto_pasien";
     

     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	
	  
    //INISIALISASI AWAL
	$nextPage = "registrasi_pasien.php?usr_id="; 	
	 

	function count_digit($angka) {
		return strlen((string) $angka);
	}

	$sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs from global.global_departemen";
	$konf = $dtaccess->Fetch($sql);
	$norm_depan = $konf['dep_konf_reg_no_rm_depan'];
	
	#tambah pasien baru
	if($_GET['reg_status_pasien'] == "B") {
	  require_once("data_pasien_kode.php");
	  $usr_kode = $_POST["kode_pasien"];
	  $arr = str_split($usr_kode,"2");
	  $usr_kode_tampilan = implode(".",$arr);
		  
        $dbTable = "global.global_customer_user";         
        $dbField[0] = "cust_usr_id";   // PK  
        if ($norm_depan == 'y') {
		  $dbField[1] = "cust_usr_kode";
		  $dbField[2] = "cust_usr_kode_tampilan";
        }
		 
        $custUsrId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$custUsrId);   
        if ($norm_depan == 'y') {
          $dbValue[1] = QuoteValue(DPE_CHAR,$usr_kode);
	      $dbValue[2] = QuoteValue(DPE_CHAR,$usr_kode_tampilan);
		} 
        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
        $dtmodel->Insert() or die("insert  error");	
         
		// die();
        unset($dtmodel);
        unset($dbField);
        unset($dbValue);
        unset($dbKey);
		
		if($_GET['bpjs'] == "0"){ $statusJKN = "bpjs=true&noKartu=".$_GET['noKartu']; }else{ $statusJKN = "bpjs=false";};
        header("location:".$nextPage.$custUsrId."&status_pasien=B&".$statusJKN);
        exit();        
	}	
    # cari pasien
    if ($_POST["btn"]) 
    {                               
	    if ($_POST['find_tgl_lahir']){
			$tga = Explode('-',$_POST['find_tgl_lahir']);
			$tgl = $tga[2]."-".$tga[1]."-".$tga[0]; 
		}
		
		# parameter query
		if($_POST["find_pk"]) {
			$pk = strval($_POST['find_pk']);
			$count = count_digit($pk);

			if ($count < 16 && $count < 13 && $count <= 8 && $count > 0 ) {
				//echo 'NO RM';
				$sql_where[] = " cust_usr_kode =".QuoteValue(DPE_CHAR,$pk);
			} elseif ($count < 16 && $count <= 13 && $count > 8 ) {
				//echo 'NO BPJS';
				$sql_where[] = " cust_usr_no_jaminan =".QuoteValue(DPE_CHAR,$pk);
			} elseif ($count <= 16 && $count > 13 && $count > 8 ) {
				//echo "NIK";
				$sql_where[] = " (cust_usr_no_identitas =".QuoteValue(DPE_CHAR,$pk)." or cust_usr_nik =".QuoteValue(DPE_CHAR,$pk).")";
			}
		}
		$findPasien = str_replace("'", "*", $_POST["find_nama"]);
		if($_POST["find_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($findPasien)."%");
		if($_POST["find_penanggung_jawab"])  $sql_where[] = "UPPER(cust_usr_penanggung_jawab) like ".QuoteValue(DPE_CHAR,strtoupper($_POST["find_penanggung_jawab"])."%");
		if($_POST["find_alamat"])  $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["find_alamat"])."%");
		if($_POST["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =".QuoteValue(DPE_CHAR,$tgl);
		$sql_where[] = "cust_usr_nama is not null";
		$sql_where[] = "cust_usr_kode <> '500'";
		if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);
		#end parameter

		$sql = "select cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan, cust_usr_nama, cust_usr_tanggal_lahir, cust_usr_alamat,cust_usr_penanggung_jawab 
				from global.global_customer_user";
		$sql .= " WHERE 1=1";  
		$sql .= " and ".$sql_where;
		$sql .= " order by cust_usr_kode desc limit 50";
		//echo $sql;
		$rs = $dtaccess->Execute($sql);
		$row = $dtaccess->FetchAll($rs);
		//echo count($row);
    }
	$tableHeader = "Registrasi Pasien";
?>

<!DOCTYPE html>
<html lang="en">
  
	<?php require_once($LAY."header.php") ?>
	<!-- sweet alert -->
	<script type="text/javascript" src="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $ROOT; ?>assets/vendors/sweetalert/sweetalert.css">
	<script>
		function pad (str, max) {
			str = str.toString();
			return str.length < max ? pad("0" + str, max) : str;
		}
		function cek_kepesertaan(param){
			$.post( "cek_kepesertaan.php", { param: param },
			function( data ) {
				var status = data.metaData.code;
				//alert(status);
				if(status != '200' || param =='0000000000000'){ //jika aktif
					window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B');
				}else{
					alert('Status pasien BPJS / ASKES '+data.response.peserta.statusPeserta.keterangan);
					var nk = data.response.peserta.noKartu;
					window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B&bpjs=0&noKartu='+nk);
				}
			  },"json");          
		}

		$(document).ready(function() {
		    $('#tata').dataTable({
		        "iDisplayLength": 25,
		        "order":[[0, 'desc']]
		    });
		} );

	</script>
	<?php #jika pencarian norm/nik/bpjs tidak ditemukan beri alert
		if($_POST["btn"]=='btnAtas' && count($row) < 1 ) {
			//echo "<script>alert('Pasien Tidak Ditemukan. Registrasi sebagai pasien baru?');</script>";
			echo "
				<script type='text/javascript'>
				var pk = ".$pk.";
				var param = pad(pk, 13);
				//alert(param);
				  setTimeout(function () {  
				   swal({
				   	background: '#FF0000E6',
					title: '<span style=\"color: #FFF\"> Pasien tidak ditemukan.</span>',
					html: '<span style=\"color: #FFF\">Registrasi sebagai pasien baru?</span>',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#337AB7',
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak',
					closeOnConfirm: false
					//closeOnCancel: false
				  }).then((result) => {
					  if (result.value) {
					    window.location.replace('registrasi_pasien_awal.php?reg_status_pasien=B');
						cek_kepesertaan(param);
					  }
					})
				  },10); 
				</script>
			";
		}elseif($_POST["btn"]=='btnAtas' && count($row) >= 1 ) {
			//cek kepesertaanbpjs
			$aktif = 0 ; 
			if ( $aktif = 0 ) {
				echo "
				<script type='text/javascript'>
				  setTimeout(function () {  
				   swal({
					title: 'SEP Tidak dapat diterbitkan.',
					text: 'Registrasi sebagai pasien umum?',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#337AB7',
					confirmButtonText: 'Ya',
					cancelButtonText: 'Tidak',
					closeOnConfirm: false,
					//closeOnCancel: false
				  },
				  function(){
					window.location.replace('registrasi_pasien.php?usr_id=".$row[0]['cust_usr_id']."&status_pasien=L&bpjs=false');
				  });  
				  },10); 
				</script>
				";
				//header("location: registrasi_pasien.php?usr_id=".$row[0]['cust_usr_kode']."&status_pasien=L&bpjs=false");
			} //elseif ( $aktif = 1 ) { header("location: registrasi_pasien.php?usr_id=".$row[0]['cust_usr_kode']."&status_pasien=L&bpjs=trues"); }
		};

		if($_GET["reg_sukses"]) {
			
			echo "
				<script type='text/javascript'>
				  setTimeout(function () {  
				   swal({
				   	background: 'rgb(0, 255, 25)',
					title: 'Registrasi Berhasil',
					text:  '',
					type: 'success',
					timer: 1200,
					showConfirmButton: true,
					confirmButtonColor: 'rgb(17, 206, 36)',
				   });  
				  },10); 
				</script>
			";
			
		};
	?>

  <body class="nav-md" onload="$('#find_pk').focus()">
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
                <h3>Registrasi Pasien</h3>
              </div>

              <div class="title_right">
                
              </div>
            </div>
            <div class="clearfix"></div>
			
			<!-- Row BEGIN -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
                  <div class="x_content">
					<form method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
					  <div class="item form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-12" >No RM / NIK / No BPJS
                        </label>
                        <div class="col-md-8 col-sm-6 col-xs-6">
						  <input id="find_pk" class="form-control" name="find_pk" value="<?php echo $_POST["find_pk"] ?>" placeholder=" Silahkan isi No RM / NIK / No BPJS" required="required">
                        </div>
						<div class="col-md-2 col-sm-2 col-xs-2">
						  <input type="hidden" name="btn" value="btnAtas"> 
                          <button type="submit" class="form-control btn btn-primary"> Cari </button>
                        </div>
                      </div>
					</form>

					<form method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
					  <!--div class="item form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-12" >Tipe Registrasi
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-8">
                          <select id="reg_status_pasien" class="form-control" name="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);">
            				<option value="L">Pasien Lama</option>
            				<option value="B">Pasien Baru</option>
            			  </select>
                        </div>
                      </div-->
					  <div id ="rm" class="item form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-12">
						Filter Cari Pasien
                        </label>
						<div class="col-md-2 col-sm-2 col-xs-2">
                          <input type="text" id="find_nama" class="form-control" name="find_nama" value="<?php echo $_POST['find_nama'];?>" placeholder="Nama Pasien">
                        </div>
						<div class="col-md-2 col-sm-2 col-xs-2">
                          <input type="text" id="find_alamat" class="form-control" name="find_alamat" value="<?php echo $_POST['find_alamat'];?>" placeholder="Alamat Pasien">
                        </div>
                        <!--div class="col-md-2 col-sm-2 col-xs-2">
                          <select id="find_rm" class="form-control" name="find_rm" value="<?php echo $_POST['find_rm'];?>">
            				<option value=""></option>
						  </select>
                        </div-->
						<div class="col-md-2 col-sm-2 col-xs-2">
						  <input type="text" class="form-control" id="find_tgl_lahir" name="find_tgl_lahir" data-inputmask="'mask': '99-99-9999'" value="<?php echo $_POST['find_tgl_lahir'];?>"  placeholder="Tanggal Lahir" />
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-2">
                          <input type="text" id="find_penanggung_jawab" class="form-control" name="find_penanggung_jawab" value="<?php echo $_POST['find_penanggung_jawab'];?>" placeholder="Nama Penanggung Jawab Pasien">
                        </div>
						<div class="col-md-2 col-sm-2 col-xs-2">
						  <input type="hidden" name="btn" value="btnBawah"> 
                          <button type="submit" class="form-control btn btn-primary"> Cari </button>
                        </div>
                      </div>
					</form>
					  <!--div class="item form-group">
                        <label class="control-label col-md-2 col-sm-2 col-xs-12" >
                        </label>
						<div class="col-md-2 col-sm-2 col-xs-2">
						  <input type="hidden" name="btn" value="btnLanjut"> 
                          <button type="submit" class="form-control btn btn-primary"> Lanjut </button>
                        </div>
						<div class="col-md-6 col-sm-6 col-xs-6">
                          <button type="button" class="form-control btn btn-warning" onclick="window.location.replace('registrasi_pasien_awal.php');"> Reset Filter </button>
                        </div>
                      </div-->
               		</div>					  
                  </div>
                </div>
              </div> 
            </div>
            <!-- END ROW -->
			<!-- Row BEGIN -->
			<?php if($_POST['btn']) { ?>
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
						  <th width="100px"></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php for ($i=0; $i < count ($row); $i++){ ?>
						<tr>
						  <td><?php echo $row[$i]['cust_usr_kode'];?></td>
						  <?php $Cust_Usr_Nama = str_replace("*", "'", $row[$i]["cust_usr_nama"]); ?>
						  <td><?php echo $Cust_Usr_Nama;?></td>
						  <td><?php echo format_date($row[$i]['cust_usr_tanggal_lahir']);?></td>
						  <td><?php echo $row[$i]['cust_usr_alamat'];?></td>
						  <td><?php echo $row[$i]['cust_usr_penanggung_jawab'];?></td>
						  <td>
							<a class="col-xs-12 btn btn-xs btn-primary" href="registrasi_pasien.php?usr_id=<?php echo $row[$i]['cust_usr_id']; ?>&status_pasien=L"> Registrasi <i class="fa fa-arrow-right"></i><a>
						  </td>
						</tr>
						<?php } ?>
						<?php if (count($row) == 0) { ?>
						<tr>
						  <td colspan="5"><center>Pasien tidak ditemukan.</center></td>
						</tr>
						<?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div> 
            </div>
			<?php } ?>
            <!-- END ROW -->
          </div>
        </div>
        <!-- /page content -->
        </form>
        <!-- footer content -->
		<?php require_once($LAY."footer.php"); ?>


		<script type="text/javascript">
			$('#find_pk').change(function() {	
				var a = $(this).val();
				var dap = pad(a, 7);    // => "0000000a"
				//alert(dap);
				$('#find_pk').val(dap);
			});

		</script>
        
        <!-- /footer content -->
      </div>
    </div>
<!-- validator -->
<script src="<?php echo $ROOT; ?>assets/vendors/validator/validator.js"></script>
<?php require_once($LAY."js.php"); ?>
  </body>
</html>
