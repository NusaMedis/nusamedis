<?php include_once 'layout/header.php'; ?>
<?php 
  $custom_script[] = 'pengajuan.js';

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
  $sql = "select reg_id,cust_usr_id, cust_usr_kode,cust_usr_no_hp, cust_usr_nama, poli_nama, d.*
      from klinik.klinik_registrasi a
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on a.id_poli = c.poli_id
      join klinik.klinik_sep d on a.reg_id = d.sep_reg_id";
  $sql .= " WHERE reg_id =".QuoteValue(DPE_CHAR, $_GET['reg_id']);
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
                <h3>Approval SEP</h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <!-- Row 1 Input Data Pasien -->
            <div class="row">
            <!-- Kolom 1 Input Data Pasien -->
              <div class="col-md-6 col-sm-6 col-xs-6">
                <div class="x_panel">
                  <div class="x_content">
                    <form id="form_pengajuan" class="form-horizontal">                     
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">No Kartu <span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input id="noKartu" name="noKartu" class="form-control col-md-7 col-xs-12" type="text" required="" onchange="cek_kepesertaan($(this).val())">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Nama Peserta 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input id="nama_peserta" name="nama_peserta" class="form-control col-md-7 col-xs-12" type="text" readonly="">
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal SEP<span class="required">*</span></label>
                        <div class="col-md-4 col-sm-4  col-xs-12">
                          <input type="text" class="form-control" id="tglSep" name="tglSep" data-inputmask="'mask': '99-99-9999'" required="required" />
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Jenis Layanan BPJS<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="jnsPelayanan" class="form-control" name="jnsPelayanan" >
                            <option value="2">Rawat Jalan</option>
                            <option value="1">Rawat Inap</option>
                          </select>
                        </div>
                      </div>
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Keterangan<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
                        </div>
                      </div>   

                      <!--  <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">User 
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input  id="nama_user" name="nama_user" value="Coba Ws" class="form-control col-md-7 col-xs-12" type="hidden" readonly="">
                        </div>
                      </div> -->
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="submit" name="submit" value="Kirim" class="btn btn-primary">
                        </div>
                      </div> 
                    </form>                       
                  </div>
                </div>                
              </div>
              <!-- END KOLOM 1 DATA PASIEN -->
            </div>
            <!-- END ROW INPUT DATA 1 -->
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