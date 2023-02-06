<?php include_once 'layout/header.php'; ?>
<?php 
$custom_script[] = "../assets/vendors/datatables.net/js/jquery.dataTables.min.js";
$custom_script[] = "../assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js";
$custom_script[] = "../assets/vendors/datatables.net-buttons/js/dataTables.buttons.min.js";
$custom_script[] = "../assets/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js";
$custom_script[] = "../assets/vendors/datatables.net-buttons/js/buttons.flash.min.js";
$custom_script[] = "../assets/vendors/datatables.net-buttons/js/buttons.html5.min.js";
$custom_script[] = "../assets/vendors/datatables.net-buttons/js/buttons.print.min.js";
$custom_script[] = "../assets/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js";
$custom_script[] = "../assets/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js";
$custom_script[] = "../assets/vendors/datatables.net-responsive/js/dataTables.responsive.min.js";
$custom_script[] = "../assets/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js";
$custom_script[] = "../assets/vendors/datatables.net-scroller/js/dataTables.scroller.min.js";
$custom_script[] = "../assets/vendors/jszip/dist/jszip.min.js";
$custom_script[] = "../assets/vendors/pdfmake/build/pdfmake.min.js";
$custom_script[] = "../assets/vendors/pdfmake/build/vfs_fonts.js";
$custom_css[] = "../assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css";
$custom_css[] = "../assets/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css";
$custom_css[] = "../assets/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css";
$custom_css[] = "../assets/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css";
$custom_css[] = "../assets/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css";
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

  # data pasien bpjs
if($_GET["cust_usr_kode"])  $sql_where[] = "cust_usr_kode like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_kode"])."%");
if($_GET["no_sep"])  $sql_where[] = "no_sep like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["no_sep"])."%");
if($_GET["cust_usr_nama"])  $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
if(!empty($_GET["tgl_awal"]))  $sql_where[] = "tgl_sep >=".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
if(!empty($_GET["tgl_akhir"]))  $sql_where[] = "tgl_sep <=".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
if( empty($_GET["tgl_awal"]) && empty($_GET["tgl_akhir"]) ) $sql_where[] = "tgl_sep =".QuoteValue(DPE_DATE,date('Y-m-d'));
$sql_where[] = "cust_usr_nama is not null";
$sql_where[] = "cust_usr_kode <> '500'";
if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);

$sql = "select d.cust_usr_nama_txt, reg_id,cust_usr_id,cust_usr_kode,cust_usr_kode_tampilan, cust_usr_nama, cust_usr_tanggal_lahir , poli_nama, reg_tanggal, reg_waktu, tgl_sep, no_sep, jkn_nama, jenis_peserta_txt
from klinik.klinik_registrasi a
left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
left join global.global_auth_poli c on a.id_poli = c.poli_id
left join klinik.klinik_sep d on a.reg_id = d.sep_reg_id
left join global.global_jkn e on d.tipe_jkn = e.jkn_id";
$sql .= " WHERE reg_jenis_pasien = 5 and (d.no_sep != '' or d.no_sep IS NOT NULL) ";  
$sql .= " and ".$sql_where;
$sql .= " order by reg_tanggal desc, reg_waktu desc, tgl_sep desc limit 200";
  // echo $sql;

$rs = $dtaccess->Execute($sql);
$rows = $dtaccess->FetchAll($rs);
?>

<div class="container body">
  <div class="main_container">
    <?php require_once("../layouts/sidebar.php") ?>
    <?php // include_once 'layout/sidebar.php'; ?>
    <!-- top navigation -->
    <?php include_once '../layouts/topnav.php'; ?>
    <!-- /top navigation -->

    <!-- page content -->
    <div class="right_col" role="main">
      <div class="">
        <div class="page-title">
          <div class="title_left">
            <h3>Data Pasien Kontrol</h3>
          </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_content">          
                <form id="form_list_kontrol" class="form-horizontal form-label-left input_mask" method="GET">
                  <div class="form-group col-md-4">
                    <label class="control-label">Tgl SEP</label>
                    <input type="text" class="form-control tgl" name="tgl_awal" id="tgl_awal" value="<?= $_GET['tgl_awal'] ?>">
                  </div>
                  <div class="form-group col-md-4">
                    <label class="control-label">sampai <small>(Tgl SEP)</small></label>
                    <input type="text" class="form-control tgl" name="tgl_akhir" id="tgl_akhir" value="<?= $_GET['tgl_akhir'] ?>">
                  </div>
                  <div class="form-group col-md-4">
                    <label class="control-label">Filter</label>
                    <select class="form-control" name="filter" id="filter">
                      <option value="1">Tanggal Rencana Kontrol</option>
                      <option value="2">Tanggal Entri</option>
                    </select>
                  </div>
                  <div class="form-group col-md-4">
                     <button type="submit" class="btn btn-primary"> Cari </button>
                  </div>
                 
                 
                  <div class="form-group col-md-4">
                    <label class="control-label">&nbsp;</label>
                    <!-- <a target="_blank" href="sep-list_export.php?tgl_awal=<?php echo $_GET[tgl_awal] ?>&tgl_akhir=<?php echo $_GET[tgl_akhir] ?>" class="pull-left btn btn-success">EXPORT KE EXCEL</a> -->
                    <!-- <input type="submit" name="btnExcel" value="Export Excel" class="pull-left btn btn-success">  -->
                  </div>                           
                </form>

              <!--     <form id="form_list_no_kontrol" class="form-horizontal form-label-left input_mask" method="GET">
                <div class="form-group col-md-4">
                  <label class="control-label">Tgl SEP</label>
                  <input type="text" class="form-control" name="no_kontrol" id="no_kontrol" value="<?= $_GET['no_kontrol'] ?>">
                </div>

                <div class="form-group col-md-4">
                  <label class="control-label"><br/></label>
                 <button type="submit" class="btn btn-primary"> Cari </button>
               </div>


                         
            </form> -->
              </div>
            </div>
          </div>
          <!-- /.col -->

          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <h2>Pasien Kontrol</h2>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">   
                     

                <div id="listKontrol"></div>


              </div>
            </div>
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
  function destroy(sep) {
    $.getJSON('destroy.php', {no_sep: sep}, function(rspns) {
      if (rspns.metaData.code != 200) {
        new PNotify({
          title: 'Error',
          text: rspns.metaData.message,
          type: 'error',
          styling: 'bootstrap3'
        });
      } else {
        new PNotify({
          title: 'Sukses',
          text: 'Data SEP '+rspns.response+' berhasil dihapus',
          type: 'success',
          styling: 'bootstrap3',
          addclass: 'dark'
        });
        $("table").find("tr#" + rspns.response).remove();
      }
    })
  }
</script>

<?php include_once 'layout/footer.php'; ?>