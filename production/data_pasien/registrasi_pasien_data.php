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
	 $tanggal = date('Y-m-d');
	 
     

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
	 	
	    if ($_POST['find_tgl_lahir']){
			$tga = Explode('-',$_POST['find_tgl_lahir']);
			$tgl = $tga[2]."-".$tga[1]."-".$tga[0]; 
		}
		
		//if($_POST["find_rm"])  $sql_where[] = "cust_usr_kode =".QuoteValue(DPE_CHAR,$_POST["find_rm"]);
		//if($_POST["find_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["find_nama"])."%");
		//if($_POST["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =".QuoteValue(DPE_CHAR,$tgl);
		$sql_where[] = "reg_tanggal =".QuoteValue(DPE_CHAR,$tanggal);
		if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);
		
		# data pasien register
		$sql = "select a.reg_id,b.cust_usr_kode,b.cust_usr_kode_tampilan, b.cust_usr_nama, b.cust_usr_tanggal_lahir, 
				b.cust_usr_alamat 
				from klinik.klinik_registrasi a
				left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr";
		$sql .= " WHERE 1=1";  
		//$sql .= " and ".$sql_where;
		//echo $sql;
		$rs = $dtaccess->Execute($sql);
		$row = $dtaccess->FetchAll($rs);
		//echo count($row);
	  
	  $tableHeader = "Data Registrasi Pasien";
		
?>

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
            <div class="page-title">
              <div class="title_left">
                <h3><?php echo $tableHeader; ?></h3>
              </div>
              <div class="title_right">
              </div>
            </div>
            <div class="clearfix"></div>
			
			<!-- Row BEGIN 
            <div class="row">
			<form method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
              <div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
				  <div class="x_title">
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <div class="form-group">
					  <div id ="rm" class="item form-group">
                        <div class="col-md-4 col-sm-4 col-xs-4">
                          <select id="find_rm" class="form-control" name="find_rm" value="<?php echo $_POST['find_rm'];?>">
            				<option value=""></option>
						  </select>
                        </div>
						<div class="col-md-4 col-sm-4 col-xs-4">
                          <input type="text" id="find_nama" class="form-control" name="find_nama" value="<?php echo $_POST['find_nama'];?>" placeholder="Nama Pasien">
                        </div>
						<div class="col-md-4 col-sm-4 col-xs-4">
						  <input type="text" class="form-control" id="find_tgl_lahir" name="find_tgl_lahir" data-inputmask="'mask': '99-99-9999'" onKeyDown="return tabOnEnter(this, event);"  placeholder="Tanggal Lahir" />
                         
                        </div>
                      </div>
					  <div class="item form-group">
						<div class="col-md-4 col-sm-4 col-xs-4">
                          <button type="button" class="form-control btn btn-warning" onclick="window.location.replace('registrasi_pasien_data.php');"> Reset Filter </button>
                        </div>
						<div class="col-md-8 col-sm-8 col-xs-8">
						  <input type="hidden" name="btn" value="btnLanjut"> 
                          <button type="submit" class="form-control btn btn-primary"> Filter </button>
                        </div>
                      </div>
               		</div>					  
                  </div>
                </div>
              </div> 
			</form>
            </div>
             END ROW -->
			<!-- Row BEGIN -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
				  <div class="x_title">
                    <h2>Data Pasien <?php echo format_date($tanggal); ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th width="100px">NO RM</td>
						  <th width="250px">Nama</td>
						  <th width="100px">Tgl Lahir</td>
						  <th>Alamat</td>
						  <th width="200px"></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php for ($i=0; $i < count ($row); $i++){ ?>
						<tr>
						  <td><?php echo $row[$i]['cust_usr_kode_tampilan'];?></td>
						  <td><?php echo $row[$i]['cust_usr_nama'];?></td>
						  <td><?php echo $row[$i]['cust_usr_tanggal_lahir'];?></td>
						  <td><?php echo $row[$i]['cust_usr_alamat'];?></td>
						  <td>
							<a class="col-xs-5 btn btn-xs btn-default" href="registrasi_pasien.php?reg_id=<?php echo $row[$i]['reg_id']; ?>&usr_id=<?php echo $row[$i]['cust_usr_kode']; ?>"> Edit <i class="fa fa-pencil"></i><a>
							<a class="col-xs-5 btn btn-xs btn-danger" href="registrasi_pasien.php?reg_id=<?php echo $row[$i]['reg_id']; ?>&usr_id=<?php echo $row[$i]['cust_usr_kode']; ?>"> Hapus <i class="fa fa-remove"></i><a>
						  </td>
						</tr>
						<?php } ?>
						<?php if (count($row) == 0) { ?>
						<tr>
						  <td colspan="5"><center>Belum ada pasien.</center></td>
						</tr>
						<?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div> 
            </div>
            <!-- END ROW -->
          </div>
        </div>
        <!-- /page content -->
        </form>
        <!-- footer content -->
		<?php require_once($LAY."footer.php"); ?>
		<script type="text/javascript">
		  $('#find_rm').select2({
			placeholder: 'No RM',
			ajax: {
			  url: 'get_rm.php',
			  dataType: 'json',
			  data: function (params) {
				  return {
					rm: params.term, // search term
				  };
			  },	  
			  processResults: function (data) {
				return {
				  results: data
				};
			  },
			  cache: true
			},
			 allowClear: true
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
