<?php include_once 'layout/header.php'; ?>
<?php 
$custom_script[] = "create_kontrol.js";

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
$sql = "select reg_id,cust_usr_id,a.reg_tanggal, cust_usr_kode,cust_usr_no_hp,reg_tipe_rawat, cust_usr_nama, poli_nama, cust_usr_no_jaminan,b.cust_usr_tanggal_lahir,d.no_sep,d.no_kartu,d.cust_usr_nama_txt
from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join klinik.klinik_sep d on a.reg_id = d.sep_reg_id";
$sql .= " WHERE reg_id =".QuoteValue(DPE_CHAR, $_GET['id_reg']);
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
            <h3>Rencana Kontrol / Rencana Inap</h3>
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
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Poli Klinik</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" readonly="" value="<?= $row['poli_nama'] ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">No. HP</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="cust_usr_no_hp" id="cust_usr_no_hp" value="<?= $row['cust_usr_no_hp'] ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">No. Kartu Jaminan</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="no_kartu" id="no_kartu" value="<?= $row['no_kartu'] ?>" readonly>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">No. SEP</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="no_sep_txt" id="no_sep_txt" value="<?= $row['no_sep'] ?>" readonly>
                    </div>
                  </div>
                   <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Peserta</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="namatxt1" id="namatxt1" readonly="" value="<?= $row['cust_usr_nama_txt'] ?>" required="">
                    </div>
                  </div>

                 <!--  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">No Kartu Jaminan</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="noKartu1" id="noKartu1" readonly="" required="">
                    </div>
                  </div>
                 
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Peserta</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="jenisPeserta_txt1" id="jenisPeserta_txt1" readonly="">
                    </div>
                  </div> -->
                </form>
              </div>
              <!-- /.x_content -->
            </div>
            <!-- /.x_panel -->
          </div>
          <!-- /.col -->


          <div class="col-md-6 col-sm-6 col-xs-12">

            <form id="form_kontrol" class="form-horizontal form-label-left input_mask" method="POST">
              <input type="hidden" name="jnsPelayanan" id="jnsPelayanan">
              <input type="hidden" id="jumlahReg">
              <input type="hidden" name="id_reg" id="id_reg" value="<?= $row['reg_id'] ?>">
              <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?= $row['cust_usr_id'] ?>">
              <input type="hidden" name="tglLahir" id="tglLahir" readonly="">
              <input type="hidden" name="tglLahirInt" id="tglLahirInt" readonly="" value="<?= $row['cust_usr_tanggal_lahir'] ?>">
              <input type="hidden" class="form-control" name="no_sep" id="no_sep" value="<?= $row['no_sep'] ?>" readonly>
           <!--   <input type="hidden" class="form-control" name="jenisPeserta_txt" id="jenisPeserta_txt" readonly="">
             <input type="hidden" class="form-control" name="namatxt" id="namatxt" readonly="" required="">
             <input type="hidden" class="form-control" name="noKartu" id="noKartu" readonly="" required=""> -->
             <div class="x_panel" id="panelB" >
              <div class="x_title">
                <!-- <h2>SEP</h2> -->
                <div class="clearfix"></div>
              </div>
              <div class="x_content">

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Tgl Rencana Kontrol/Inap</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="date" class="form-control tgl" name="tglRencanaKontrol" id="tglRencanaKontrol" value="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Pelayanan</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="tipe_pelayanan" id="tipe_pelayanan">
                      <option value="1">Rawat Jalan</option>
                      <option value="2">Rawat Inap</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Spesialis/Sub Spesialis*</label>

                  <div class="col-md-9 col-sm-9 col-xs-12">

                    <input type="hidden" class="form-control" name="poli_tujuan_kontrol" id="poli_tujuan_kontrol" >
                    <span class="col-md-8 col-sm-8 col-xs-12" style="padding-left: 0px;">
                      <input type="text" class="form-control" readonly="" name="poli_tujuan_txt_kontrol" id="poli_tujuan_txt_kontrol" >
                    </span>
                    <span class="col-md-4 col-sm-4 col-xs-12">
                      <button type="button" class="btn btn-sm btn-info form-control" onclick="init_dpjp()">Pilih Poli</button>
                    </span>

                  </div>

                </div>


                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">DPJP Pemberi Surat SKDP/SPRI</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="hidden" class="form-control" name="dpjp_kontrol" id="dpjp_kontrol" >
                    <span class="col-md-8 col-sm-8 col-xs-12" style="padding-left: 0px;">
                     <input type="text" class="form-control" readonly="" name="dpjp_text_kontrol" id="dpjp_text_kontrol" >
                   </span>
                   <span class="col-md-4 col-sm-4 col-xs-12">
                    <button type="button" class="btn btn-sm btn-info form-control" onclick="myFunction()">Nama Dokter</button>
                  </span>

                </div>
              </div>

              <hr>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">No. Surat Kontrol</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="noSep" id="noSep" required="" readonly>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-12">
                  <button type="button" class="btn btn-success" id="btnSep" onclick="createNoKontrol()">Buat No. Surat Konrol </button>
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
<div class="modal fade" id="modal-poli" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
        <header class="modal-title" id="myHeader">
          <h4 class="modal-title" >Pilih Poli</h4>
        </header>

      </div>
      <div class="modal-body">
        <form id="form_list_poli" class="form-horizontal form-label-left">

          <div class="form-group" style="margin: 0 auto">
            <label class="control-label col-md-3">No Kartu</label>
          
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary"> Cari </button>
            </div>
          </div>
        </form>
        <div id="listPoli"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<script type="text/javascript">

function myFunction() {
   alert("Hello");
}





</script>

<?php include_once 'layout/footer.php'; ?>