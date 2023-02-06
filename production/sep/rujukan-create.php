<?php include_once 'layout/header.php'; ?>
<?php 
  $custom_script[] = "rujukan-create.js";

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
  $sql = "select reg_id,cust_usr_id, cust_usr_kode,cust_usr_no_hp, cust_usr_nama, poli_nama, a.reg_tanggal_pulang as reg_pulang,  a.reg_waktu_pulang as waktu_pulang, d.*
      from klinik.klinik_registrasi a
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      join klinik.klinik_sep d on a.reg_id = d.sep_reg_id";
  $sql .= " WHERE reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
  $rs = $dtaccess->Execute($sql);
  $row = $dtaccess->Fetch($rs);

  $sql = "select sep_reg_id,ppk_dirujuk_txt FROM klinik.klinik_sep a";
  $sql .= " JOIN klinik.klinik_sep_rujukan b ON a.sep_reg_id = b.sep_rujukan_reg_id";
  $sql .= " WHERE sep_reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
  $rs = $dtaccess->Execute($sql);
  $exist = $dtaccess->Fetch($rs);

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
                <h3>Rujukan</h3>
              </div>
            </div>
            <div class="clearfix"></div>

            <div class="row">
              <? if($exist): ?>
              <div class="alert alert-error alert-dismissible fade in" role="alert">
                <strong>Peringatan!</strong> Pasien ini sudah dirujuk ke <?= $exist['ppk_dirujuk_txt'] ?>
              </div>
              <? endif; ?>
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
                  <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?= $row['cust_usr_id'] ?>">
                  <div class="x_panel" id="panelB">
                    <div class="x_title">
                      <h2>Rujukan Baru</h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Rujukan</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control tgl" name="tgl_rujukan" id="tgl_rujukan" readonly="" value="<?= date('d-m-Y') ?>">
                        </div>
                      </div>
                       <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal dirujuk</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control tgl" name="tgl_di_rujukan" id="tgl_di_rujukan" >
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Faskes Rujukan</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                         <select class="form-control" name="rujukan_asalRujukan" id="rujukan_asalRujukan" onchange="asal(this.value)">
                                  <option value="1" selected>Faskes 1</option>
                                  <option value="2">Faskes 2 (RS)</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">PPK Dirujuk</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="ppk_dirujuk_txt" id="ppk_dirujuk_txt">
                          <input type="hidden" class="form-control" name="ppk_dirujuk" id="ppk_dirujuk">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Layanan BPJS</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="jns_pelayanan" id="jns_pelayanan">
                            <option value="1">Rawat Inap</option>
                            <option value="2">Rawat Jalan</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Catatan Rujukan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <textarea id="message" class="form-control" name="catatan" id="catatan"></textarea>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Diagnosa Rujukan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="diag_rujukan_txt" id="diag_rujukan_txt">
                          <input type="hidden" class="form-control" name="diag_rujukan" id="diag_rujukan">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipe Rujukan</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="tipe_rujukan" id="tipe_rujukan">
                            <option value="0">Penuh</option>
                            <option value="1">Partial</option>
                            <option value="2">Rujuk Balik</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Poli Rujukan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="poli_rujukan_txt" id="poli_rujukan_txt" >
                          <input type="hidden" class="form-control" name="poli_rujukan" id="poli_rujukan" >
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

      <script type="text/javascript">
      function asal(isi) {
        init_asalRujukan();
      }
    </script>


<?php include_once 'layout/footer.php'; ?>