<?php include_once 'layout/header.php'; ?>
<?php 
$custom_script[] = "create.js";

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
$sql = "select reg_id,cust_usr_id,a.reg_tanggal, cust_usr_kode,cust_usr_no_hp,reg_tipe_rawat, cust_usr_nama, poli_nama, cust_usr_no_jaminan,b.cust_usr_tanggal_lahir
from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
left join global.global_auth_poli c on a.id_poli = c.poli_id";
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
            <h3>Sep Baru</h3>
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
                 <!--  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">No Kartu Jaminan</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="noKartu1" id="noKartu1" readonly="" required="">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Peserta</label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                      <input type="text" class="form-control" name="namatxt1" id="namatxt1" readonly="" required="">
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
            <div class="x_panel" id="panelA">
              <div class="x_title">
                <h2>SEP</h2>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">
                <div class="" role="tabpanel" data-example-id="togglable-tabs">
                  <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#tab_rujukan" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Rujukan</a>
                    </li>
                    <li role="presentation" class=""><a href="#tab_manual" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">Rujukan Manual/IGD</a>
                    </li>
                  </ul>
                  <div id="myTabContent" class="tab-content">
                    <div class="form-group" style="padding-bottom: 35px">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal SEP</label>
                      <div class="col-md-4 col-sm-4 col-xs-12">
                        <input type="text" class="form-control tgl" name="tglSep_" id="tglSep_" value="<?php echo date_db( $row['reg_tanggal']) ?>">
                      </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade active in" id="tab_rujukan" aria-labelledby="home-tab">    
                      <form id="form_rujukan" class="form-horizontal form-label-left input_mask">
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Asal Rujukan</label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <select class="form-control" name="rujukan_asalRujukan_" id="rujukan_asalRujukan_" onselect="rujukan()" onchange="rujukan(this.value)">
                              <option value="1" selected>Faskes 1</option>
                              <option value="2">Faskes 2 (RS)</option>
                            </select>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">No Rujukan</label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" class="form-control" name="rujukan_noRujukan_" id="rujukan_noRujukan_">
                          </div>
                          <div class="col-md-3">
                            <button type="button" class="btn btn-info form-control" data-toggle="modal" data-target="#modal-rujukan">No Kartu</button>
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
                          <div class="col-md-9 col-sm-9 col-xs-12">
                            <button type="submit" class="btn btn-default">Cari</button>
                          </div>
                        </div>                            
                      </form>
                    </div>
                    <!-- /#tab_rujukan -->
                    <div role="tabpanel" class="tab-pane fade" id="tab_manual" aria-labelledby="profile-tab">
                      <form id="form_manual" class="form-horizontal form-label-left input_mask">
                        <div class="form-group">
                          <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Layanan BPJS</label>
                          <div class="col-md-6 col-sm-6 col-xs-12">
                            <select class="form-control" name="jnsPelayanan_" id="jnsPelayanan_">
                             <option <?php if ($row['reg_tipe_rawat'] == "I") {
                              echo "selected";
                            } ?> value="1">Rawat Inap</option>

                            <option <?php if ($row['reg_tipe_rawat'] == "J" || $row['reg_tipe_rawat'] == "G") {
                              echo "selected";
                            } ?> value="2">Rawat Jalan</option>




                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No Kartu / NIK</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="param" id="param" value="<?= $row['cust_usr_no_jaminan'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <button type="submit" class="btn btn-default">Cari</button>
                        </div>
                      </div>
                      <div class="alert alert-success alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                        </button><p>
                          <strong>Pembuatan SEP rawat jalan menggunakan no.kartu hanya bisa :</strong><br>

                          1. Untuk PPK yang tidak menggunakan jaringan komunikasi dapat manual.<br>
                        2. Untuk PPK yang mempunyai jaringan komunikasi data hanya bisa menerbitkan SEP Gawat Darurat.</p>
                      </div>
                    </form>
                  </div>
                  <!-- /#tab_manual -->
                </div>
                <!-- /.tab_content -->
              </div>
              <!-- /tab-panel -->
            </div>
            <!-- /.x_content -->
          </div>
          <!-- /.x_panel -->
          <form id="form_sep" class="form-horizontal form-label-left input_mask" method="POST">
            <input type="hidden" name="jnsPelayanan" id="jnsPelayanan">
            <input type="hidden" id="jumlahReg">
            <input type="hidden" name="id_reg" id="id_reg" value="<?= $row['reg_id'] ?>">
            <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?= $row['cust_usr_id'] ?>">
            <input type="hidden" name="tglLahir" id="tglLahir" readonly="">
            <input type="hidden" name="tglLahirInt" id="tglLahirInt" readonly="" value="<?= $row['cust_usr_tanggal_lahir'] ?>">
           <!--   <input type="hidden" class="form-control" name="jenisPeserta_txt" id="jenisPeserta_txt" readonly="">
             <input type="hidden" class="form-control" name="namatxt" id="namatxt" readonly="" required="">
              <input type="hidden" class="form-control" name="noKartu" id="noKartu" readonly="" required=""> -->
            <div class="x_panel" id="panelB" style="display: none;">
              <div class="x_title">
                <h2>SEP</h2>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">No Kartu Jaminan</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="noKartu" id="noKartu" readonly="" required="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Peserta</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="namatxt" id="namatxt" readonly="" required="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Peserta</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="jenisPeserta_txt" id="jenisPeserta_txt" readonly="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipe JKN</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="tipe_jkn" id="tipe_jkn">
                      <option value="1">PBI</option>
                      <option value="2">Non PBI</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Spesialis/Sub Spesialis*</label>
                  <div class="col-md-3 col-sm-3 col-xs-12">
                    <input type="checkbox" name="poli_eksekutif" id="poli_eksekutif" value="1"> Eksekutif<br>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" name="poli_tujuan_txt" id="poli_tujuan_txt" >
                    <input type="hidden" class="form-control" name="poli_tujuan" id="poli_tujuan" >
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Asal Rujukan</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="rujukan_asalRujukan" id="rujukan_asalRujukan" onchange="asal(this.value)">
                      <option value="1">Faskes 1</option>
                      <option value="2">Faskes 2 (RS)</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">PPK Asal Rujukan*</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="rujukan_ppkRujukan_txt" id="rujukan_ppkRujukan_txt">
                    <input type="hidden" class="form-control" name="rujukan_ppkRujukan" id="rujukan_ppkRujukan">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Tgl Rujukan</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control tgl" name="rujukan_tglRujukan" id="rujukan_tglRujukan" value="<?php echo date_db($row['reg_tanggal']) ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">No Rujukan*</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="rujukan_noRujukan" id="rujukan_noRujukan">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">No Surat Kontrol/SKDP</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="skdp_noSurat" id="skdp_noSurat">
                    *Hanya untuk pasien <i>kontrol ulang/rujuk internal.</i>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">DPJP Pemberi Surat SKDP/SPRI</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <span class="col-md-8 col-sm-8 col-xs-12" style="padding-left: 0px;">
                      <select class="form-control" name="skdp_noDPJP" id="skdp_noDPJP">
                        <option selected="" value="">Klik Nama Dokter dahulu</option>
                      </select>
                    </span>
                    <span class="col-md-4 col-sm-4 col-xs-12">
                      <button type="button" class="btn btn-sm btn-info form-control" onclick="init_dpjp()">Nama Dokter</button>
                    </span>
                    <span class="col-md-12 col-sm-12 col-xs-12">
                      *Hanya untuk pasien <i>kontrol ulang/rujuk internal.</i>
                    </span>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Tgl SEP</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control tgl" name="tglSep" id="tglSep" readonly="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Hak Kelas</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="klsRawat_txt" id="klsRawat_txt" readonly="">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Kelas Rawat</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="klsRawat" id="klsRawat">
                      <option value="1">Kelas 1</option>
                      <option value="2">Kelas 2</option>
                      <option value="3">Kelas 3</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">COB</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="cob" id="cob">
                      <option value="0">Tidak</option>
                      <option value="1">Ya</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Katarak</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="katarak" id="katarak">
                      <option value="0">Tidak</option>
                      <option value="1">Ya</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Diagnosa</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="text" class="form-control" name="diagAwal_txt" id="diagAwal_txt">
                    <input type="hidden" class="form-control" name="diagAwal" id="diagAwal">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Laka Lantas</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <select class="form-control" name="jaminan_lakaLantas" id="jaminan_lakaLantas" onchange="init_laka($(this).val())">
                      <option value="0">Tidak</option>
                      <option value="1">Ya</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Catatan</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <textarea id="message" class="form-control" name="catatan" id="catatan"></textarea>
                  </div>
                </div>
                <div id="laka-group" style="display: none;">
                  <hr>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Suplesi</label>
                    <div class="col-md-4 col-sm-4 col-xs-12">
                      <select class="form-control" name="laka_suplesi" id="laka_suplesi" onchange="init_suplesi($(this).val())">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                      </select>
                    </div>
                    <div class="col-md-3 col-sm-3 col-xs-12">
                      <button type="button" class="btn btn-default" id="btnCariSuplesi" onclick="cariSuplesi()">Cari SEP Suplesi</button>
                    </div>
                  </div>
                  <div id="suplesiY">
                    <input type="hidden" name="laka_noSepSuplesi" id="laka_noSepSuplesi">
                  </div>
                  <!-- /#suplesiY -->
                  <div id="suplesiN">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Penjamin</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <select class="form-control" name="laka_penjamin[]" id="laka_penjamin">
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Kejadian</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <input type="text" class="form-control tgl" name="laka_tglKejadian" id="laka_tglKejadian">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Lokasi Kejadian</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <select class="form-control" name="laka_kdPropinsi" id="laka_kdPropinsi" onchange="init_kabupaten()">
                          <option selected="" value="">Pilih Propinsi</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <select class="form-control" name="laka_kdKabupaten" id="laka_kdKabupaten" onchange="init_kecamatan()">
                          <option selected="" value="">Pilih Kabupaten</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <select class="form-control" name="laka_kdKecamatan" id="laka_kdKecamatan">
                          <option selected="" value="">Pilih Kecamatan</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Keterangan Laka</label>
                      <div class="col-md-9 col-sm-9 col-xs-12">
                        <textarea class="form-control" name="laka_keterangan" id="laka_keterangan"></textarea>
                      </div>
                    </div>
                  </div>
                  <!-- /#suplesiN -->
                </div>
                <!-- /#laka-group -->
                <hr>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">No SEP</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" name="noSep" id="noSep" required="" readonly>
                  </div>
                  <div class="col-md-3 col-sm-3 col-xs-12">
                    <button type="button" class="btn btn-default" id="btnSep" onclick="createSep()">Create SEP </button>
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

<div class="modal fade" id="modal-suplesi" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">Data Penjaminan</h4>
      </div>
      <div class="modal-body">
        <p>Daftar ini merupakan penjaminan kecelakaan lalu lintas dari PT. Jasa Raharja. Silahkan Pilih Data tersebut sesuai dengan berkas kasus sebelumnya dengan klik <button class="btn btn-xs btn-success"> <i>Pilih</i> </button></p>
        <div id="listSEPSuplesi"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="modal-rujukan" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
        <header class="modal-title" id="myHeader">
          <h4 class="modal-title" >Rujukan Faskes Tingkat 1</h4>
        </header>

      </div>
      <div class="modal-body">
        <form id="form_list_rujukan" class="form-horizontal form-label-left">

          <div class="form-group" style="margin: 0 auto">
            <label class="control-label col-md-3">No Kartu</label>
            <input type="hidden" name="faskes_rujukan" id="faskes_rujukan" class="form-control" value="1" required="">
            <div class="col-md-4">
              <input type="text" name="noka__" class="form-control" required="">
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary"> Cari </button>
            </div>
          </div>
        </form>
        <div id="listRujukan"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script type="text/javascript">
  function rujukan(isi){
    document.getElementById("faskes_rujukan").value = document.getElementById("rujukan_asalRujukan_").value;

    document.getElementById("myHeader").innerHTML = "Rujukan Faskes Tingkat "+ document.getElementById("rujukan_asalRujukan_").value;
  }
</script>

<script type="text/javascript">
  function asal(isi) {
    init_asalRujukan();
  }
</script>

<?php include_once 'layout/footer.php'; ?>