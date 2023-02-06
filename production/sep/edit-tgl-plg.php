<?php include_once 'layout/header.php'; ?>
<?php 
  $custom_script[] = "edit-tgl-plg.js";

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

  #data pasien
  $sql = "select reg_id,cust_usr_id, cust_usr_kode,cust_usr_no_hp, cust_usr_nama, poli_nama, a.reg_tanggal_pulang as tanggal_pulang,a.reg_waktu_pulang as waktu_pulang,d.reg_tanggal_pulang as sep_pulang,d.reg_waktu_pulang as sep_waktu ,d.*
      from klinik.klinik_registrasi a
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      join klinik.klinik_sep d on a.reg_id = d.sep_reg_id";
  $sql .= " WHERE reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
  // echo $sql;
  $rs = $dtaccess->Execute($sql);
  $row = $dtaccess->Fetch($rs);
?>

    <div class="container body">
      <div class="main_container">
        <?php require_once("../layouts/sidebar.php") ?>
    		<?php// include_once 'layout/sidebar.php'; ?>
        <!-- top navigation -->
    		<?php include_once '../layouts/topnav.php'; ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Update Tanggal Pulang</h3>
              </div>
            </div>
            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-6 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Identitas & Info Registrasi Pasien </h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <br />
                    <form class="form-horizontal form-label-left input_mask">

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No RM</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" readonly="" name="cust_usr_kode" id="cust_usr_kode" value="<?= $row['cust_usr_kode'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" readonly="" value="<?= $row['cust_usr_nama'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No Jaminan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" readonly="" value="<?= $row['no_kartu'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal SEP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" readonly="" value="<?= format_date($row['tgl_sep']) ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No SEP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="noSep" id="noSep" value="<?= $row['no_sep'] ?>" readonly>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No. HP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" id="cust_usr_no_hp" value="<?= $row['cust_usr_no_hp'] ?>" readonly>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- /.x_content -->
                </div>
                <!-- /.x_panel -->
              </div>
              <!-- /.col -->

              <div class="col-md-6 col-sm-6 col-xs-12">
                <form id="form_sep" class="form-horizontal form-label-left input_mask" method="POST">
                  <input type="hidden" name="id_reg" id="id_reg" value="<?= $row['reg_id'] ?>">
                  <div class="x_panel" id="panelB">
                    <div class="x_title">
                      <h2>Update</h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Pulang</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control tgl" name="reg_tanggal_pulang" id="reg_tanggal_pulang" value="<?php echo format_date($row['tanggal_pulang']) ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Waktu Pulang</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control waktu" name="reg_waktu_pulang" id="reg_waktu_pulang" value="<?= $row['waktu_pulang'] ?>">
                        </div>
                      </div>
                    </div>
                    <!-- /.x_content -->
                  </div>
                  <!-- /.x_panel -->
                  <div class="col-md-4">
    		            <button type="button" class="btn btn-default form-control" onclick="window.history.back()">Kembali</button>
                  </div>
                  <div class="col-md-4">
    		            <button type="button" class="btn btn-warning form-control" onclick="window.location.reload(true)">Reset</button>
                  </div>
                  <div class="col-md-4">
                    <button type="submit" class="btn btn-primary form-control">Simpan</button>
                  </div>
                </form>
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            &nbsp;
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

<?php include_once 'layout/footer.php'; ?>