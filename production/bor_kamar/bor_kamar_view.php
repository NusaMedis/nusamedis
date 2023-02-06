<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     //$depNama = $auth->GetDepNama();
     //$userName = $auth->GetUserName();
 
     $editPage = "bor_kamar_edit.php";
     $thisPage = "bor_kamar_view.php";

    
       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
	
	
	/* if(!$auth->IsAllowed("man_medis_master_bor_kamar",PRIV_READ) && !$auth->IsAllowed("sirs_tampilan_bor_bor",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_master_bor_kamar",PRIV_READ)===1 || $auth->IsAllowed("sirs_tampilan_bor_bor",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/


    $sql = "select count(reg_id) as jumlah from klinik.klinik_registrasi a 
            left join klinik.klinik_rawatinap b on b.id_reg=a.reg_id 
            left join klinik.klinik_rawat_inap_history e on e.id_reg=a.reg_id 
            where reg_status='I2' and rawat_inap_history_status='A' and a.reg_tanggal = '".date("Y-m-d")."'";
    $masukHariIni = $dtaccess->Fetch($sql);

    $sql = "select count(bed_id) as jumlah from klinik.klinik_kamar_bed where bed_reserved = 'n' and bed_keterangan = 'n'";
    $bedTersedia = $dtaccess->Fetch($sql);

    $sql = "select count(bed_id) as jumlah from klinik.klinik_kamar_bed where bed_reserved = 'y' and bed_keterangan = 'n'";
    $bedReserved = $dtaccess->Fetch($sql);

    $sql = "SELECT count(reg_id) as jumlah from klinik.klinik_registrasi a 
            left join klinik.klinik_rawatinap b on b.id_reg = a.reg_id
            left join klinik.klinik_rawat_inap_history e on e.id_reg = a.reg_id
            where rawat_inap_history_status='P' and rawat_inap_history_rawat_jalan ='I4' and rawatinap_tanggal_keluar = '".date("Y-m-d")."'";
    $pasienKeluar = $dtaccess->Fetch($sql);

    $BOR = intval( ($bedReserved['jumlah'] / ($bedTersedia['jumlah'] + $masukHariIni['jumlah']) )*100 );

    $LOS = $bedReserved['jumlah'] / $pasienKeluar['jumlah'];


	

	
	$tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
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
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Master Bor Kamar</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					           <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <tr>
                        <td>BOR</td>
                        <td><?=$BOR?>%</td>
                      </tr>
                      <tr>
                        <td>LOS</td>
                        <td><?=$LOS?>%</td>
                      </tr>
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