<?php
  // Library
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."datamodel.php");
require_once($LIB."dateLib.php");
require_once($LIB."currency.php");
require_once($LIB."encrypt.php");
  //  require_once($LIB."expAJAX.php"); 
require_once($LIB."tampilan.php");

error_reporting(0);

  // Inisialisasi Lib
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$auth = new CAuth();
$enc = new textEncrypt();
$userData = $auth->GetUserData();
$userName = $auth->GetUserName();
$userId = $auth->GetUserId();
$depId = $auth->GetDepId();
$poliId = $auth->IdPoli();
$tglSekarang = date("d-m-Y");
$depLowest = $auth->GetDepLowest();

$findPage = "pasien_find.php?";

    // menampilkan data pemeriksaan baru waktu di klik tombol detail
if($_GET["id_reg"] || $_GET["id_inacbg"]) {
  $sql = "select b.*, d.jadwal_ket,a.id_poli as poli,a.id_dokter as dokter, a.reg_no_antrian, a.reg_id, a.reg_waktu_pulang,
  c.jam_nama,a.reg_keterangan,f.usr_name as dokter_nama, a.reg_no_sep, a.reg_tipe_rawat, a.reg_kelas, h.kelas_nama_bpjs, h.kelas_nama,  a.reg_tanggal_pulang, b.cust_usr_jenis_kelamin,a.reg_jenis_pasien,a.reg_tanggal, a.reg_waktu, b.cust_berat_lahir,b.cust_usr_no_jaminan,
  b.cust_usr_jenis, e.*, g.*, a.id_pembayaran, cust_usr_tanggal_lahir, a.id_cust_usr, a.reg_who_update, a.reg_periksa_gratis, a.reg_cara_keluar_inap,
  b.cust_usr_alamat, cust_usr_no_hp, g.inacbg_dokter, ((current_date - cust_usr_tanggal_lahir)/365) as umur,inacbg_kode, x.*,y.id_pembayaran_ibu
  from klinik.klinik_registrasi a
  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
  left join global.global_jam c on c.jam_id = a.id_jam 
  left join klinik.klinik_jadwal d on d.id_reg = a.reg_id  
  left join klinik.klinik_perawatan e on e.id_reg = a.reg_id  
  left join global.global_auth_user f on f.usr_id = a.id_dokter
  left join klinik.klinik_inacbg g on g.id_reg=a.reg_id
  left join klinik.klinik_kelas h on a.reg_kelas = h.kelas_id
  left join klinik.klinik_perawatan_imunisasi x on x.id_reg=a.reg_id 
  left join klinik.klinik_folio y on y.id_pembayaran = a.id_pembayaran        
  where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." order by reg_tanggal_pulang desc, reg_tanggal desc";
      // echo $sql.'<br/>';;
  $dataPasien= $dtaccess->Fetch($sql);  
  if ($dataPasien[id_pembayaran_ibu]!=' ') {
    // code...
      $sqlFolAnak="select * from klinik.klinik_folio where id_pembayaran_ibu='$dataPasien[id_pembayaran_ibu]' and id_pembayaran !='$dataPasien[id_pembayaran_ibu]' ";
    // echo $sqlFolAnak.'<br/>';
  // FolAnak;  
  $dataFolAnak= $dtaccess->Fetch($sqlFolAnak);   

  if ($dataFolAnak) {
       // code...
    $sqlAnak="select b.cust_usr_kode,b.cust_usr_nama,a.reg_tanggal,a.id_pembayaran from klinik.klinik_registrasi a
    left join global.global_customer_user b on b.cust_usr_id = a.id_cust_usr
    where a.id_pembayaran='$dataFolAnak[id_pembayaran]'";
      // echo $sqlAnak;
    // ;Anak;
    $dataAnak= $dtaccess->Fetch($sqlAnak);  

    $_POST['rm_anak']=$dataAnak["cust_usr_kode"];
    $_POST['nama_anak']=$dataAnak["cust_usr_nama"];
    $_POST['tanggal_registrasi']=$dataAnak["reg_tanggal"];
    $_POST['id_pembayaran_anak']=$dataAnak["id_pembayaran"];


    }
  }



}

if ($_POST['btnTransfer']) {
  $sql = "update klinik.klinik_folio set id_pembayaran_ibu = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran'])." where id_pembayaran = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran_anak']);
  $dtaccess->Execute($sql);

  $sql = "update klinik.klinik_folio set id_pembayaran_ibu = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran'])." where id_pembayaran = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran']);
  $dtaccess->Execute($sql);

  echo "<script type='text/javascript'>alert('Proses Selesai!');</script>";

  // echo "<script>document.location.href='input_rm.php';</script>";      
}

elseif ($_POST['btnHapus']) {
  $sql = "update klinik.klinik_folio set id_pembayaran_ibu = ' ' where id_pembayaran_ibu = ".QuoteValue(DPE_CHAR, $_POST['id_pembayaran']);
  // echo $sql;
  $dtaccess->Execute($sql);
  // echo $sql;


  echo "<script type='text/javascript'>alert('Hapus Selesai!');</script>";
  // echo "<script>document.location.href='input_rm.php';</script>";      
}
$tableHeader = "&nbsp;Transfer Tindakan";
?>

<script type="text/javascript">

 var _wnd_new;
 function BukaWindow(url,judul)
 {
  if(!_wnd_new) {
    _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=10');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=400,height=300,left=100,top=10');
    } else {
      _wnd_new.focus();
    }
  }
  return false;
} 

function BukaWindow(url,judul)
{
  if(!_wnd_new) {
    _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
  } else {
    if (_wnd_new.closed) {
      _wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=1000,left=100,top=10');
    } else {
      _wnd_new.focus();
    }
  }
  return false;
}

<?php if($cetak=="y"){?>
  BukaWindow('<?php echo $ROOT;?>kassa/module/kasir_irj/kasir_irj/kasir_cetak_sementara.php?dep_bayar_reg=<?php echo $_POST["dep_bayar_reg"];?>&id_reg=<?php echo $_POST["id_reg"];?>&ket=<?php echo $_POST["fol_keterangan"];?>&dis=<?php echo $_POST["diskon"];?>&disper=<?php echo $_POST["diskonpersen"];?>&pembul=<?php echo $_POST["pembulatan"];?>&total=<?php echo $_POST["total"];?>','Kwitansi');
  document.location.href='<?php echo $thisPage;?>';
<?php } ?>


</script>  

<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY."header.php"); ?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require_once($LAY."sidebar.php"); ?>
      <!-- top navigation -->
      <?php require_once($LAY."topnav.php"); ?>
      <!-- /top navigation -->
      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          <div class="page-title">
            <div class="title_left">
              <h3>Rekam Medik - <? echo $tableHeader;?></h3>
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="row">
            <div class="form-horizontal form-label-left col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Data Pasien</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                  </ul>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="frmEdit" class="form-horizontal form-label-left" name="frmEdit" method="POST" autocomplete="off" action="<?php echo $thisPage;?>" enctype="multipart/form-data" onSubmit="return CheckDataSave(this)">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                      <div class="form-group">
                        <input type="hidden" name="rawat_id" value="<?php echo $dataPasien['rawat_id'] ?>">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6">No RM</label>
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <input type="text" id="cust_usr_kode" name="cust_usr_kode" readonly value="<?php echo substr($dataPasien["cust_usr_kode"], 2) ; ?>" required="required" class="form-control col-md-7 col-xs-12">
                          <input type="hidden" name="reg_id" id="reg_id" value="<?php echo $_GET['id_reg'] ?>">
                          <input type="hidden" name="id_pembayaran" id="id_pembayaran" value="<?php echo $_GET['id_pembayaran'] ?>">
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6">Nama Lengkap</label>
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <input type="text" id="cust_usr_nama_txt" name="cust_usr_nama_txt" readonly value="<?php echo $dataPasien["cust_usr_nama"]." / ".$dataPasien["tahun"]." Tahun"; ?>" required="required" class="form-control col-md-7 col-xs-12">
                          <input type="hidden" id="cust_usr_nama" name="cust_usr_nama"  value="<?php echo $dataPasien["cust_usr_nama"]; ?>" >
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Kelamin <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_jenis_kelamin_nama" name="cust_usr_jenis_kelamin_nama" readonly value="<?php echo $jenisKelamin[$dataPasien["cust_usr_jenis_kelamin"]]; ?>" required="required" class="form-control col-md-7 col-xs-12">
                          <input type="hidden" id="cust_usr_jenis_kelamin" name="cust_usr_jenis_kelamin"  value="<?php echo $dataPasien["cust_usr_jenis_kelamin"]; ?>" >
                        </div>
                      </div>
                    </div> <!-- div kiri -->
                    <div class="col-md-6 col-sm-6 col-xs-6">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tanggal Lahir  <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_tanggal_lahir" class="form-control" name="cust_usr_tanggal_lahir" value="<?php echo format_date($dataPasien["cust_usr_tanggal_lahir"]);?>" size="35" maxlength="255" onKeyDown="return tabOnEnter_select_with_button(this, event);"/>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Alamat <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_alamat" name="cust_usr_alamat" value="<?php echo $dataPasien["cust_usr_alamat"]; ?>" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">No HP <span class="required">*</span></label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="cust_usr_no_hp_txt" name="cust_usr_no_hp_txt" readonly value="<?php echo $dataPasien["cust_usr_no_hp"]; ?>" required="required" class="form-control col-md-7 col-xs-12">
                          <input type="hidden" id="cust_usr_no_hp" name="cust_usr_no_hp"  value="<?php echo $dataPasien["cust_usr_no_hp"]; ?>" >
                        </div>
                      </div>
                    </div> <!-- DIV END AKHIR KANAN -->
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="x_panel">
                  <div class="x_content">
                    <div class="col-md-6 col-sm-6 col-xs-6">
                      <div class="form-group">
                        <input type="hidden" name="rawat_id" value="<?php echo $dataPasien['rawat_id'] ?>">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6">No RM</label>
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <a href="<?php echo $findPage; ?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien">
                            <input type="text" name="rm_anak" id="rm_anak" class="form-control" value="<?php echo $_POST["rm_anak"]; ?>" readonly="readonly" />
                          </a>
                          <a href="<?php echo $findPage; ?>&TB_iframe=true&height=550&width=800&modal=true" class="thickbox" title="Cari Pasien"><img src="<?php echo ($ROOT); ?>gambar/finder.png" border="0" align="top" style="cursor:pointer" title="Cari Pasien" alt="Cari Pasien" /></a>
                          <input type="hidden" name="id_pembayaran_anak" id="id_pembayaran_anak">
                          <input type="hidden" name="id_cust_usr" id="id_cust_usr">
                          <input type="hidden" name="id_reg_anak" id="id_reg_anak">
                        </div>
                      </div>

                      <div class="col-md-12 col-sm-6 col-xs-6">&nbsp;</div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6">Nama Lengkap</label>
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <input type="text" id="nama_anak" name="nama_anak" value="<?php echo $_POST["nama_anak"]; ?>" readonly required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>

                      <div class="col-md-12 col-sm-6 col-xs-6">&nbsp;</div>

                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-6">Tanggal Registrasi</label>
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <input type="text" id="tanggal_registrasi" name="tanggal_registrasi" value="<?php echo $_POST["tanggal_registrasi"]; ?>" readonly required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>

                      <div class="col-md-12 col-sm-6 col-xs-6">&nbsp;</div>

                      <div class="form-group">
                        <div class="col-md-9 col-sm-9 col-xs-6">
                          <input type="submit" name="btnTransfer" class="btn btn-success" value="Proses">
                        </div>
                        <?php 
                        if($dataFolAnak) { ?>
                          t
                          <div class="col-md-9 col-sm-9 col-xs-6">
                            <input type="submit" name="btnHapus" class="btn btn-danger" value="Hapus Transfer">
                          </div>
                        <?php }

                      ?>
                      a

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
        </form>
      </body>
      </html>