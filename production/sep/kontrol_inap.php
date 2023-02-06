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
            <h3>Rencana Kontrol / Rencana Inap </h3>
          </div>
        </div>
        <div class="clearfix"></div>


        <div class="col-md-12 col-sm-6 col-xs-12">
          <div class="x_panel" id="panelA">
            <div class="x_title">
              <h2>Action</h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <div class="" role="tabpanel" data-example-id="togglable-tabs">
                <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                  <li role="presentation" class="active"><a href="#tab_rujukan" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Nomor SEP</a>
                  </li>
                  <li role="presentation" class=""><a href="#tab_manual" role="tab" id="profile-tab" data-toggle="tab" aria-expanded="false">List Rencana Kunjungan Kontrol/Inap</a>
                  </li>
                </ul>
                <div id="myTabContent" class="tab-content">

                  <div role="tabpanel" class="tab-pane fade active in" id="tab_rujukan" aria-labelledby="home-tab">    
                    <form id="form_rujukan" class="form-horizontal form-label-left input_mask">
                      <div class="form-group" >
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Pilih</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">

                          <input type="radio" id="jenis" name="jenis" value="A" onchange="showStuff('A', this); return false;" checked=""> Rencana Kontrol
                          <input type="radio" id="jenis" name="jenis" value="Rencana Kontrol" onchange="showStuff('B', this); return false;"> Rencana Rawat Inap
                        </div>
                      </div>
                      <div class="form-group" id="form1">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No SEP</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="no_sep" id="no_sep_">
                        </div>
                      </div>
                      <div class="form-group" id="form2" style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Tanggal Rencana Inap</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control tgl" name="tgl_inap" id="tgl_inap" value="">
                        </div>
                      </div>
                      <div class="form-group" id="form3" style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">No.Kartu</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="noKartu" id="noKartu" value="">
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
                      <div class="form-group col-md-2">
                        <label class="control-label">Tgl SEP</label>
                        <input type="text" class="form-control tgl" name="tgl_awal" id="tgl_awal" value="<?= $_GET['tgl_awal'] ?>">
                      </div>
                      <div class="form-group col-md-2">
                        <label class="control-label">sampai <small>(Tgl SEP)</small></label>
                        <input type="text" class="form-control tgl" name="tgl_akhir" id="tgl_akhir" value="<?= $_GET['tgl_akhir'] ?>">
                      </div>
                      <div class="form-group col-md-2">
                        <label class="control-label">No MR</label>
                        <input type="text" class="form-control" name="cust_usr_kode" id="cust_usr_kode" value="<?= $_GET['cust_usr_kode'] ?>">
                      </div>
                      <div class="form-group col-md-2">
                        <label class="control-label">Nama</label>
                        <input type="text" class="form-control" name="cust_usr_nama" id="cust_usr_nama" value="<?= $_GET['cust_usr_nama'] ?>">
                      </div>
                      <div class="form-group col-md-2">
                        <label class="control-label">No SEP</label>
                        <input type="text" class="form-control" name="no_sep" id="no_sep" value="<?= $_GET['no_sep'] ?>">
                      </div>
                      <div class="form-group col-md-2">
                        <label class="control-label">&nbsp;</label>
                        <button type="submit" class="btn form-control btn-default">Cari</button>
                      </div> 
                      <div class="form-group col-md-4">
                        <label class="control-label">&nbsp;</label>
                        <a target="_blank" href="sep-list_export.php?tgl_awal=<?php echo $_GET[tgl_awal] ?>&tgl_akhir=<?php echo $_GET[tgl_akhir] ?>" class="pull-left btn btn-success">EXPORT KE EXCEL</a>
                        <!-- <input type="submit" name="btnExcel" value="Export Excel" class="pull-left btn btn-success">  -->
                      </div>                           


                    </form>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                      <div class="x_panel">
                        <div class="x_title">
                          <h2>Pasien Jaminan</h2>
                          <div class="clearfix"></div>
                        </div>
                        <div class="x_content">          
                          <table id="datatable" class="table table-striped table-bordered">
                            <thead>
                              <tr>
                                <th>No</th>
                                <th width="150px">Tanggal Reg.</td>
                                  <th>Tanggal SEP.</td>
                                    <th width="100px">No MR</td>
                                      <th width="200px">Nama</td>
                                        <th>Poli Klinik</td>
                                          <th>Jenis Peserta</td>
                                            <th width="100px">No SEP</td>
                                              <th></th>
                                            </tr>
                                          </thead>
                                          <tbody>
                                            <?php 
                                            $no=1;
                                            foreach ($rows as $key => $value) { ?>
                                              <tr id="<?= $value['no_sep'];?>">
                                                <td><?php echo $no; ?></td>
                                                <td><?php echo format_date($value['reg_tanggal']).' '.$value['reg_waktu'];?></td>
                                                <td><?php echo format_date($value['tgl_sep']);?></td>
                                                <td><?php echo $value['cust_usr_kode'];?></td>
                                                <td><?php 
                                                if (is_null($value['cust_usr_nama_txt'])) {
                             # code...
                                                  echo  str_replace("*", "'", $value['cust_usr_nama']) ;
                                                }
                                                else if ($value['cust_usr_nama_txt']=" ") {
                             # code...
                                                 echo  str_replace("*", "'", $value['cust_usr_nama']);
                                               }
                                               else{ echo  str_replace("*", "'", $value['cust_usr_nama_txt']);
                                             }?></td>
                                             <td><?php echo $value['poli_nama'];?></td>
                                             <td><?php echo $value['jenis_peserta_txt'];?></td>
                                             <td><?php echo $value['no_sep'];?></td>
                                             <td>
                                              <a class="btn btn-xs btn-success" href="edit.php?reg_id=<?= $value['reg_id'];?>">Update SEP</a>
                                              <a class="btn btn-xs btn-success" href="edit-tgl-plg.php?reg_id=<?= $value['reg_id'];?>">Update Tgl Pulang</a>
                                              <a class="btn btn-xs btn-warning" href="#" onclick="destroy('<?= $value['no_sep'];?>')">Hapus SEP</a>
                                              <a class="btn btn-xs btn-default" href="print-sep.php?reg_id=<?= $value['reg_id'];?>&no=<?php echo $no ?>" target="_blank">Print SEP</a>
                                              <a class="btn btn-xs btn-success" href="rujukan-create.php?reg_id=<?= $value['reg_id'];?>">Buat Rujukan</a>
                                              <a class="btn btn-xs btn-danger"href="pdf-sep.php?reg_id=<?= $value['reg_id'];?>&no=<?php echo $no ?>">Export PDF SEP</a>
                                              <a  class="btn btn-xs btn-danger" href="pdf-kwitansi.php?id_reg=<?= $value['reg_id'];?>&no=<?php echo $no ?>&total=">Export PDF Bukti Bayar</a>
                                            </td>
                                          </tr>
                                          <?php
                                          $no++; } ?>
                                        </tbody>
                                      </table>


                                    </div>
                                  </div>
                                </div>

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
          <script type="text/javascript">


            function showStuff(value,btn) {
              if(value=='A'){
                document.getElementById("form1").style.display = 'block';
                document.getElementById("form2").style.display = 'none';
                document.getElementById("form3").style.display = 'none';
              }
              else if(value=='B'){
                   document.getElementById("form1").style.display = 'none';
                document.getElementById("form2").style.display = 'block';
                document.getElementById("form3").style.display = 'block';
             }

           }
         </script>

         <?php include_once 'layout/footer.php'; ?>