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
  if(!$_POST["bulan"]) $_POST["bulan"] = date("m");
  if(!$_POST["bulan"]) $_POST["bulan"] = date("Y");

  if( $_POST["bulan"]) $sql_where[] = "DATE_PART('month', tglsep) =".$_POST["bulan"];
  if( $_POST["tahun"]) $sql_where[] = "DATE_PART('year', tglsep) =".$_POST["tahun"];

  // if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);

  $sql = "select * from klinik.klinik_sep_pengajuan WHERE ";
 
  $sql .=implode(" and ",$sql_where);
  $sql .= " order by created desc limit 200";
  // echo $sql;

  $rs = $dtaccess->Execute($sql);
  $rows = $dtaccess->FetchAll($rs);

 
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
             <div class="col-md-12">
            <div class="box box-primary">
               <!--  <div class="box-header with-border">
                    <h3 class="box-title">Action</h3>
                </div> -->
                <div class="box-body">
                    <form method="POST" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 col-sm-3 col-xs-12 control-label">Bulan</label>
                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <select class="form-control" name="bulan">
                                    <option value=" ">-- Pilih Bulan --</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">Nopember</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-2 col-xs-12">
                                <select class="form-control" name="tahun">
                                  <?php
                                    for($i=date('Y'); $i>=date('Y')-32; $i-=1){
                                    echo"<option value='$i'> $i </option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3 col-sm-3 col-xs-12"></div>
                            <div class="col-md-3 col-sm-3 col-xs-12">
                               <button type="submit" class="btn form-control btn-success">Cari</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    
                    <div class="clearfix"></div>
                  </div>
                  <form class="form-horizontal">
                        <div>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-pengajuan"><i class="fa fa-plus"></i> Tambah Pengajuan</button>
                        </div>
                    </form>
                  <div class="x_content">          
                      <table id="datatable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>No.Kartu</th>
                                <th>Nama Peserta</th>
                                <th>Tgl.SEP</th>
                                <th>RI/RJ</th>
                                <th>Persetujuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                          <tbody>
                        <?php 
                        $no=1;

                        foreach ($rows as $key => $value) { ?>
                        <tr>
                          <td><?php echo $no; ?></td>
                          <td><?php echo $value['noka'];?></td>
                          <td><?php echo $value['namapeserta'];?></td>
                          <td><?php echo format_date($value['tglsep']);?></td>
                          <?php if ($value['jnspelayanan']=='1') {?>
                            <td>RI</td>
                          <?php }
                          else{ ?>
                             <td>RJ</td>
                          <?php } ?>
                          <td><?php echo $value['keterangan'];?></td>
                          <td>
                          <?php if ($value['ispengajuan']=='Y') {?>
                             <button type="button" class="btn btn-primary" id="btnAproval" onclick="aproval('<?php echo $value['sep_pengajuan_id']; ?>','<?php echo $value['namapeserta']; ?>'
                             ,'<?php echo $value['noka']; ?>','<?php echo $value['jnspelayanan']; ?>','<?php echo $value['keterangan']; ?>','<?php echo $value['tglsep']; ?>')">Pengajuan</button>

                          <?php } else if ($value['isaproval']=='Y') {?>
                             <button type="button" class="btn btn-success" id="btnSep" >Disetujui</button>

                          <?php } 
                           else if ($value['isaproval']=='Y' and $value['ispengajuan']=='Y' ) {?>
                             <button type="button" class="btn btn-success" id="btnSep">SEP Terbit</button>

                          <?php } ?>
                        </td>
                        
                        </tr>
                        <?php $no++; } ?>
                      </tbody>
                    </table>
          
          
                  </div>
                </div>
              </div>
         
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

     <div class="modal fade" id="modal-pengajuan" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                </button>
                 <header class="modal-title" id="myHeader">
              <h4 class="modal-title" >Pengajuan SEP</h4>
           </header>
                
              </div>
              <div class="modal-body">
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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pilih<span class="required">*</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <select id="pilih" class="form-control" name="pilih" >
                            <option value="Persetujuan Pembuatan SEP tanggal Backdate">Persetujuan Pembuatan SEP tanggal Backdate</option>
                            <option value="Persetujuan Fingerprint">Persetujuan Fingerprint</option>
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
                      <div class="item form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</span>
                        </label>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                          <input type="submit" name="submit" value="Kirim" class="btn btn-primary">
                        </div>
                      </div> 
                    </form>   
               
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>

            </div>
          </div>
        </div>

<?php include_once 'layout/footer.php'; ?>