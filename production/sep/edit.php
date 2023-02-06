<?php include_once 'layout/header.php'; ?>
<?php 
  $custom_script[] = "edit.js";

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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Dokter DPJP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" readonly="" value="<?= $row['nama_dpjp'] ?>">
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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No. HP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" id="cust_usr_no_hp" value="<?= $row['cust_usr_no_hp'] ?>">
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
                  <input type="hidden" name="sep_id" id="sep_id" value="<?= $row['sep_id'] ?>">
                  <input type="hidden" name="id_reg" id="id_reg" value="<?= $row['reg_id'] ?>">
                  <input type="hidden" name="id_cust_usr" id="id_cust_usr" value="<?= $row['cust_usr_id'] ?>">
                  <input type="hidden" name="jnsPelayanan" id="jnsPelayanan" value="<?= $row['jns_pelayanan'] ?>">
                  <input type="hidden" name="tglSep" id="tglSep" value="<?= format_date($row['tgl_sep']) ?>">
                  <input type="hidden" name="poli_tujuan" id="poli_tujuan" value="<?= $row['poli_tujuan'] ?>">
                  <input type="hidden" name="dpjp" id="dpjp">
                  <div class="x_panel" id="panelB">
                    <div class="x_title">
                      <h2>SEP</h2>
                      <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No SEP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="noSep" id="noSep" value="<?= $row['no_sep'] ?>">
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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Asal Rujukan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <select class="form-control" name="rujukan_asalRujukan" id="rujukan_asalRujukan">
                            <option value="1">Faskes 1</option>
                            <option value="2">Faskes 2 (RS)</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tgl Rujukan</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control tgl" name="rujukan_tglRujukan" id="rujukan_tglRujukan" value="<?= format_date($row['rujukan_tgl_rujukan']) ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">PPK Asal Rujukan*</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="rujukan_ppkRujukan_txt" id="rujukan_ppkRujukan_txt" value="<?= $row['rujukan_ppk_rujukan_txt'] ?>">
                          <input type="hidden" class="form-control" name="rujukan_ppkRujukan" id="rujukan_ppkRujukan" value="<?= $row['rujukan_ppk_rujukan'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No Rujukan*</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="rujukan_noRujukan" id="rujukan_noRujukan" value="<?= $row['rujukan_no_rujukan'] ?>">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No Surat Kontrol/SKDP</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="skdp_noSurat" id="skdp_noSurat" value="<?= $row['skdp_no_surat'] ?>">
                        </div>
                      </div>
                       <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Spesialis/Sub Spesialis*</label>
                        <div class="col-md-3 col-sm-3 col-xs-12">
                          <input type="checkbox" name="poli_eksekutif" id="poli_eksekutif" value="1"> Eksekutif<br>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="poli_tujuan_txt" id="poli_tujuan_txt" value="<?php echo $row['poli_tujuan_txt']; ?>" >
                          <input type="hidden" class="form-control" name="poli_tujuan" id="poli_tujuan" value="poli_tujuan" >
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DPJP Pemberi Surat SKDP / SPRI</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <span class="col-md-8 col-sm-8 col-xs-12" style="padding-left: 0px;">
                          <select class="form-control" name="skdp_noDPJP" id="skdp_noDPJP">
                            <option selected="" value="<?php echo $row['skdp_noDPJP']; ?>"><?php echo $row['nama_dpjp']; ?></option>
                          </select>
                          </span>
                          <span class="col-md-4 col-sm-4 col-xs-12">
                          <button type="button" class="btn btn-sm btn-info" onclick="init_dpjp()">Nama Dokter</button>
                          </span>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Poli Eksekutif</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <select class="form-control" name="poli_eksekutif" id="poli_eksekutif">
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
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
                          <input type="text" class="form-control" name="diagAwal_txt" id="diagAwal_txt" value="<?= $row['diag_awal_txt'] ?>">
                          <input type="hidden" class="form-control" name="diagAwal" id="diagAwal" value="<?= $row['diag_awal'] ?>">
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
                          <textarea id="message" class="form-control" name="catatan" id="catatan"><?= $row['catatan'] ?></textarea>
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
                              <input type="text" class="form-control tgl" name="laka_tglKejadian" id="laka_tglKejadian" value="<?= format_date($row['laka_tgl_kejadian']) ?>">
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
                              <textarea class="form-control" name="laka_keterangan" id="laka_keterangan"><?= $row['laka_keterangan'] ?></textarea>
                            </div>
                          </div>
                        </div>
                        <!-- /#suplesiN -->
                      </div>
                      <!-- /#laka-group -->
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

<?php include_once 'layout/footer.php'; ?>