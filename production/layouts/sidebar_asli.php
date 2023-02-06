<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "conf/database.php");
require_once($LIB . "conf/db_depan.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

$auth = new CAuth();
$enc = new textEncrypt();

$host = "localhost";
$user = $enc->Decode(DB_USER);
$password = $enc->Decode(DB_PASSWORD);
$port = "5432";
$dbname = DB_DEPAN;

//ambil title Software dari Konfigurasi
$link = pg_connect("host=" . $host . " port=" . $port . " dbname=" . $dbname . " user=" . $user . " password=" . $password);

$sql = pg_query($link, "select dep_title,dep_logo from global.global_departemen");
$dataTitle = pg_fetch_assoc($sql);

$lokasi = $ROOT . "gambar/img_cfg";
$fotoName = $lokasi . "/" . $dataTitle["dep_logo"];
$userId = $auth->GetUserId();
$userName = $auth->GetUserName();
?>
<div class="col-md-3 left_col">
  <div class="left_col scroll-view">
    <div class="navbar nav_title" style="border: 0;">
      <a class="site_title"> <span style="font-size:16px"><?php echo $dataTitle["dep_title"]; ?></span></a>
    </div>
    <div class="clearfix"></div>
    <!-- menu profile quick info -->
    <div class="profile clearfix">
      <div class="profile_pic">
        <img src="<?php echo $fotoName; ?>" alt="..." class="img-circle profile_img">
      </div>
    </div>
    <!-- /menu profile quick info -->
    <br />
    <!-- sidebar menu -->
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
      <div class="menu_section">
        <h3>MENU UTAMA</h3>
        <ul class="nav side-menu">

          <!-- Menu LOKET -->
          <!-- APP ID = 1   -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '33' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppScrening = $dtaccess->Fetch($rs);
          if ($authAppScrening) {
            ?>
            <li><a><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; SCREEN (develop) <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">


                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                     <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>screencovid/form.php" target="_blank" style="font-size:15px">Form Pasien Belum Registrasi </a></li><? //} 
                      ?>

                        <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>screencovid/form.php?reg=1" target="_blank" style="font-size:15px">Form Pasien Registrasi </a></li><? //} 
                      ?>


                      
                    </ul>
                  </li>

                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi Screen Covid <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                     <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>screencovid/pasien_view.php?tiperawat=G" target="_blank" style="font-size:15px">IGD</a></li><? //} 
                      ?>

                      <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>screencovid/pasien_view.php?tiperawat=J" target="_blank" style="font-size:15px">Rajal</a></li><? //} 
                      ?>
                      
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>screencovid/pasien_view.php?tiperawat=I"  target="_blank" style="font-size:15px">Ranap</a></li><? // } 
                      ?>
                      
                    </ul>
                  </li>


                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>

                </ul>
              </li>
          <? } // Akhir if App Screening
          ?>
          <!-- End Menu Loket -->


          <!-- Menu LOKET -->
          <!-- APP ID = 1   -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '1' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppLoket = $dtaccess->Fetch($rs);
          if ($authAppLoket) {
            ?>
            <li><a><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; LOKET <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("fo_loket_registrasi", PRIV_READ) || $auth->IsAllowed("fo_loket_edit_registrasi", PRIV_READ) || $auth->IsAllowed("fo_loket_penata_jasa", PRIV_READ) || $auth->IsAllowed("fo_loket_registrasi_online", PRIV_READ)) { ?>
                  <li><a><i class="glyphicon glyphicon-user"></i>&nbsp;Loket<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("fo_loket_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien/registrasi_pasien_awal.php" target="_blank" style="font-size:15px">Registrasi Pasien</a></li> <? } ?>
                      <? if ($auth->IsAllowed("fo_loket_edit_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_registrasi/registrasi_irj_view.php" target="_blank" style="font-size:15px">Edit Registrasi</a></li> <? } ?>
                      <? if ($auth->IsAllowed("fo_loket_registrasi_online", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_online/registrasi_online.php" target="_blank" style="font-size:15px">Registrasi Online</a></li> <?php } ?>

                      <? if ($auth->IsAllowed("fo_loket_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_online/pasien_kontrol.php" target="_blank" style="font-size:15px">Laporan Pasien Kontrol</a></li> <?php } ?>


                      <? //if($auth->IsAllowed("fo_loket_registrasi_online",PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>data_pasien_non_regis/registrasi_pasien_awal.php" target="_blank" style="font-size:15px">Sinkronisasi Pendaftaran</a></li> <?php //}  
                      ?>
                      <? if ($auth->IsAllowed("fo_loket_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>/bridge_appsheet/kirim_data_loket.php" target="_blank" style="font-size:15px">Bridge Mutu</a></li> <?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;E-General Consent <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                  	 <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>EMR-Loket/input_rm_igd.php" target="_blank" style="font-size:15px">IGD</a></li><? //} 
                      ?>

                      <? // if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>EMR-Loket/input_rm.php" target="_blank" style="font-size:15px">Rajal</a></li><? //} 
                      ?>
                      
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>EMR-Loket/input_rm_ranap.php" target="_blank" style="font-size:15px">Ranap</a></li><? // } 
                      ?>
                      
                    </ul>
                  </li>

                  <?php if ($auth->IsAllowed("fo_daftar_pasien", PRIV_READ) || $auth->IsAllowed("fo_lap_registrasi", PRIV_READ) || $auth->IsAllowed("fo_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("fol_lap_pengunjung", PRIV_READ) || $auth->IsAllowed("fol_lap_batal_kunjung", PRIV_READ)) { ?>
                    <li><a><i class="glyphicon glyphicon-list-alt"></i>&nbsp;Informasi<span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("fo_daftar_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien_loket/data_pasien_view.php" target="_blank" style="font-size:15px">Data Pasien</a></li> <? } ?>
                        <? if ($auth->IsAllowed("fo_daftar_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu_loket/rekap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                        <? if ($auth->IsAllowed("fo_daftar_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_kontrol/lap_pasien_view.php" target="_blank" style="font-size:15px">Laporan Pasien Kontrol</a></li><? } ?>

                        <? if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Registrasi Pasien</a></li><? } ?>
                        <? if ($auth->IsAllowed("fo_lap_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjunan_irj.php" target="_blank" style="font-size:15px">Laporan Registrasi Rawat Jalan</a></li><? } ?>
                        <? if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjungan_igd.php" target="_blank" style="font-size:15px">Laporan Registrasi IGD</a></li><? } ?>
                        <? if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjungan_irna.php" target="_blank" style="font-size:15px">Laporan Registrasi Rawat Inap</a></li><? } ?>
                        <!--  <? if ($auth->IsAllowed("fol_lap_pengunjung", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung_irj/report_pasien.php" target="_blank" style="font-size:15px">Laporan Pengunjung</a></li><? } ?> -->
                        <? if ($auth->IsAllowed("fol_lap_batal_kunjung", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_batal_registrasi/lap_batal.php" target="_blank" style="font-size:15px">Laporan Batal Kunjungan</a></li><? } ?>
                      <? //if ($auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_bed_kosong/bed_kosong.php" target="_blank" style="font-size:15px">Laporan Bed Kosong</a></li><? //} 
                      ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("fol_cetak_tracer", PRIV_READ) || $auth->IsAllowed("fol_cetak_barcode_kecil", PRIV_READ) || $auth->IsAllowed("fol_cetak_barcode_besar", PRIV_READ) || $auth->IsAllowed("fol_cetak_registrasi", PRIV_READ) || $auth->IsAllowed("fol_cetak_riwayat", PRIV_READ)) { ?>
                  <li><a><i class="glyphicon glyphicon-file"></i>&nbsp;Cetak Tracer<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("fol_cetak_tracer", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama/tracer_irj_lama_view.php" target="_blank" style="font-size:15px">Cetak Tracer</a></li><?php } ?>
                      <? if ($auth->IsAllowed("fol_cetak_barcode_kecil", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode/tracer_irj_lama_barcode_view.php" target="_blank" style="font-size:15px">Cetak Barcode Kecil</a></li><?php } ?>
                      <? if ($auth->IsAllowed("fol_cetak_barcode_besar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode_besar/tracer_irj_lama_barcode_view.php" target="_blank" style="font-size:15px">Cetak Barcode Besar</a></li><?php } ?>
                      <? if ($auth->IsAllowed("fol_cetak_registrasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_registrasi/tracer_registrasi_view.php" target="_blank" style="font-size:15px">Cetak Registrasi</a></li><?php } ?>
                      <? if ($auth->IsAllowed("fol_cetak_riwayat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_riwayat/tracer_riwayat_view.php" target="_blank" style="font-size:15px">Cetak Riwayat</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;Dokumentasi <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    <li><a href="<?php echo $ROOT; ?>user_guide/User_Manual_Loket.pdf" target="_blank" style="font-size:15px">User Guide</a></li>
                  </ul>
                </li>

                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>

                </ul>
              </li>
          <? } // Akhir if App Loket 
          ?>
          <!-- End Menu Loket -->

          <!-- Menu Rawat Jalan -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '2' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppIRJ = $dtaccess->Fetch($rs);
          if ($authAppIRJ) {
            ?>
            <li><a><i class="glyphicon glyphicon-cog"></i>&nbsp;&nbsp; RAWAT JALAN <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("irj_proses_asuhan_keperawatan", PRIV_READ) || $auth->IsAllowed("irj_proses_terima_status", PRIV_READ) || $auth->IsAllowed("irj_proses_kembalikan_status", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_asuhan_keperawatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irj/pemeriksaan_irj_view_pemeriksaan.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_asuhan_keperawatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irj/pemeriksaan_irj_view_antrian.php" target="_blank" style="font-size:15px">Antrian Poli</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_terima_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/terima_berkas.php" target="_blank" style="font-size:15px">Terima Status</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_kembalikan_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/kirim_rm.php" target="_blank" style="font-size:15px">Kembalikan Status</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_asuhan_keperawatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>indikator_mutu_urj/indikator_mutu_urj_view.php" target="_blank" style="font-size:15px">Indikator Mutu</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_irj/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>

                      <? if ($auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>bridge_appsheet/kirim_data_irj.php" target="_blank" style="font-size:15px">Bridge Mutu</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_10_bsr_diagnosa/rekap_10_bsr_diagnosa.php" target="_blank" style="font-size:15px">Laporan 10 Besar Diagnosa ICD 10</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu/rekap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_status_pasien_irj/lap_status_pasien_irj.php" target="_blank" style="font-size:15px">Laporan Status Pasien</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_bulanan_irj/rekap_bulanan_irj.php" target="_blank" style="font-size:15px">Rekap Bulanan IRJ</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_preop_irj/report_preop_irj.php" target="_blank" style="font-size:15px">Laporan Rencana Operasi IRJ</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_online/jadwal_dokter.php" target="_blank" style="font-size:15px">Kuota Dokter</a></li> <?php } ?>

                      <? if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_online/registrasi_online.php" target="_blank" style="font-size:15px">Daftar Registrasi Online</a></li> <?php } ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>


                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>

                 <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu Rawat Jalan -->

          <!-- Menu IGD -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '4' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppIGD = $dtaccess->Fetch($rs);
          if ($authAppIGD) {
            ?>
            <li><a><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp; IGD <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("igd_proses_pemeriksaan", PRIV_READ) || $auth->IsAllowed("igd_proses_pemeriksaan_int", PRIV_READ) || $auth->IsAllowed("igd_proses_pemeriksaan_irna", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("igd_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd/pemeriksaan_igd_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd/pemeriksaan_igd_view_mundur_rawat.php" target="_blank" style="font-size:15px">Tindakan IGD Mundur</a></li><? } ?>
                      <? if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>surat_kontrol_ponek/surat_kontrol_ponek_view.php" target="_blank" style="font-size:15px">Surat Kontrol IGD Ponek</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_proses_pemeriksaan_int", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd_int/pemeriksaan_irna_view.php" target="_blank" style="font-size:15px">Pemeriksaan IGD INT</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_proses_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd_irna/pemeriksaan_irna_view.php" target="_blank" style="font-size:15px">Pemeriksaan IGD IRNA</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_terima_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/terima_berkas.php" target="_blank" style="font-size:15px">Terima Status</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_kembalikan_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/kirim_rm.php" target="_blank" style="font-size:15px">Kembalikan Status</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("igd_informasi_laporan_kunjungan", PRIV_READ) || $auth->IsAllowed("igd_informasi_laporan_tindakan", PRIV_READ) || $auth->IsAllowed("igd_informasi_rekap_tindakan", PRIV_READ) || $auth->IsAllowed("igd_informasi_laporan_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("igd_informasi_laporan_tingkat_kegawatan", PRIV_READ) || $auth->IsAllowed("igd_informasi_laporan_status_pasien", PRIV_READ) || $auth->IsAllowed("igd_informasi_rekap_bulanan_igd", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("igd_informasi_laporan_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_igd/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_laporan_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_igd/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_rekap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_tindakan_igd/rekap_tindakan_igd.php" target="_blank" style="font-size:15px">Rekap Tindakan</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_laporan_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_igd/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_laporan_tingkat_kegawatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_triase/lap_triase.php" target="_blank" style="font-size:15px">Laporan Tingkat Kegawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_laporan_status_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_status_pasien_igd/lap_status_pasien_igd.php" target="_blank" style="font-size:15px">Laporan Status Pasien IGD</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_laporan_status_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_status_pasien_irj/lap_status_pasien_irj.php" target="_blank" style="font-size:15px">Laporan Status Pasien IRJ</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_rekap_bulanan_igd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_bulanan_igd/rekap_bulanan_igd.php" target="_blank" style="font-size:15px">Rekap Bulanan IGD</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_rekap_bulanan_igd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_preop_igd/report_preop_igd.php" target="_blank" style="font-size:15px">Laporan Rencana Operasi IGD</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_rekap_bulanan_igd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_hasil_lab_irj/lap_hasil_lab_igd.php" target="_blank" style="font-size:15px">Laporan Hasil Lab</a></li><? } ?>
                      <? if ($auth->IsAllowed("igd_informasi_rekap_bulanan_igd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>info_igd/info_igd_view.php" target="_blank" style="font-size:15px">Informasi Statistik IGD</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>
                      //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 

                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>

                   <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu IGD -->

          <!-- Menu Rawat Inap -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '3' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppInap = $dtaccess->Fetch($rs);
          if ($authAppInap) {
            ?>
            <li><a><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; RAWAT INAP <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ) || $auth->IsAllowed("rawat_inap_transfer_irna", PRIV_READ) || $auth->IsAllowed("rawat_inap_rencana_pulang", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <!-- <? if ($auth->IsAllowed("rawat_inap_proses_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_irna/antrian.php"  target="_blank" style="font-size:15px">Antrian</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna/pemeriksaan_irna_view.php" target="_blank" style="font-size:15px">Pemeriksaan IRNA</a></li><? } ?>
                      <!--   <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna_ok/pemeriksaan_irna_ok_view.php" target="_blank" style="font-size:15px">Tindakan Operasi</a></li><? } ?> -->
                      <!-- <? if ($auth->IsAllowed("rawat_inap_transfer_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna/transfer_irna_view.php" target="_blank" style="font-size:15px">Transfer IRNA</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("rawat_inap_rencana_pulang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_rencana_pulang/penata_jasa_edit_view.php" target="_blank" style="font-size:15px">Rencana Pulang</a></li><? } ?>
                      <? //if ($auth->IsAllowed("rawat_inap_rencana_pulang", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>master_alkes/alkes_view.php" target="_blank" style="font-size:15px">Master Alkes</a></li><? //} 
                      ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rawat_inap_lap_pengunjung", PRIV_READ) || $auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ) || $auth->IsAllowed("rawat_inap_lap_tindakan", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_pasien_pulang", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_kontrol/pasien_kontrol_view.php" target="_blank" style="font-size:15px">Informasi Pasien Kontrol</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_lap_pengunjung", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung_irna/report_pasien.php" target="_blank" style="font-size:15px">Laporan Pengunjung</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penggunaan_bed/penggunaan_bed.php" target="_blank" style="font-size:15px">Laporan Penggunaan Bed</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_bed_kosong/bed_kosong.php" target="_blank" style="font-size:15px">Laporan Bed Kosong</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pemakaian_alkes/report_pemakaian_alkes.php" target="_blank" style="font-size:15px">Laporan Pemakaian Alkes</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_irna/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sensus_harian/sensus_view.php" target="_blank" style="font-size:15px">Sensus Harian</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_sensus_harian/sensus_view.php" target="_blank" style="font-size:15px">Rekap Sensus Harian</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_informasi_lap_pasien_pulang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_pulang_irna/pasien_pulang_view.php" target="_blank" style="font-size:15px">Laporan Pasien Pulang</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif/report_pasien.php" target="_blank" style="font-size:15px">Laporan Perbandingan Tarif Pasien</a></li><?php } ?>
                      <? if ($auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif_asuransi/report_pasien.php" target="_blank" style="font-size:15px">Laporan Perbandingan Tarif Pasien Asuransi</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rawat_inap_lap_pengunjung", PRIV_READ) || $auth->IsAllowed("rawat_inap_lap_penggunaan_bed", PRIV_READ) || $auth->IsAllowed("rawat_inap_lap_tindakan", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_pasien_pulang", PRIV_READ) || $auth->IsAllowed("rawat_inap_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Surat Kontrol <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_kontrol/pasien_kontrol_create.php?id_poli=6528c31371368823f1e11772c26335fe" target="_blank" style="font-size:15px">Rawat Nifas</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_kontrol/pasien_kontrol_create.php?id_poli=b32007585f886be3a97da614258297f3" target="_blank" style="font-size:15px">Rawat Anak</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_kontrol/pasien_kontrol_create.php?id_poli=d6dd48f320d243b42528bfc52e96ce84" target="_blank" style="font-size:15px">Rawat Neo</a></li><? } ?>
                      <? if ($auth->IsAllowed("rawat_inap_pemeriksaan_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_kontrol/pasien_kontrol_create.php?id_poli=eae3aed310ef687fe1bea2c6e995622e" target="_blank" style="font-size:15px">Rawat RGT</a></li><? } ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>

                	<li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu Rawat Inap -->

          <!-- Menu Rehab Medik -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '19' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppRehabMedik = $dtaccess->Fetch($rs);
          if ($authAppRehabMedik) {
            ?>
            <!-- Disembunyikan Sementara pada 09-01-2021 -->
            <li style="display:none"><a><i class="glyphicon glyphicon-share"></i>&nbsp;&nbsp; REHAB MEDIK <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("rehab_medik_proses_pemeriksaan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rehab_medik_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_rehab_medik/pemeriksaan_rehab_medik_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rehab_medik_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("rehab_medik_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("rehab_medik_informasi_lap_waktu_tunggu", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rehab_medik_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_rehab_medik/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><?php } ?>
                      <? if ($auth->IsAllowed("rehab_medik_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><?php } ?>
                      <? if ($auth->IsAllowed("rehab_medik_informasi_lap_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_rehab_medik/rekap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu Rehab Medik -->

          <!-- Menu Rekam Medik -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '11' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppRekamMedik = $dtaccess->Fetch($rs);
          if ($authAppRekamMedik) {
            ?>
            <li><a><i class="glyphicon glyphicon-th-list"></i>&nbsp;&nbsp; REKAM MEDIK <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ) || $auth->IsAllowed("rm_proses_input_diagnosa_new", PRIV_READ) || $auth->IsAllowed("rm_proses_cek_tanggal_pulang", PRIV_READ) || $auth->IsAllowed("rm_proses_kirim_status", PRIV_READ) || $auth->IsAllowed("rm_proses_terima_status", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien_loket/data_pasien_view.php" target="_blank" style="font-size:15px">Data Pasien</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_coder/rekap_koder.php" target="_blank" style="font-size:15px">Rekap Petugas Coder</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>/icd10/icd_view.php" target="_blank" style="font-size:15px">Master ICD 10</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>/icd9/icd9_view.php" target="_blank" style="font-size:15px">Master ICD 9</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>/imunisasi_view/imunisasi_view.php" target="_blank" style="font-size:15px">Master Jenis Imunisasi</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_data_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_diagnosa_inacbg51_new/input_rm.php" target="_blank" style="font-size:15px">Input Diagnosa</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_kirim_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_keluar/tracer_keluar.php" target="_blank" style="font-size:15px">Kirim Status Ke Poli</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_proses_terima_status", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_terima_rm/terima_rm.php" target="_blank" style="font-size:15px">Terima Status Dari Poli</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rm_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_pengunjung", PRIV_READ) || $auth->IsAllowed("rm_informasi_rekam_medik_pasien", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_rekam_medik", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_inacbg", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_irj", PRIV_READ) || $auth->IsAllowed("rm_informasi_rekap_pasien_per_jenis_kelamin", PRIV_READ) || $auth->IsAllowed("rm_informasi_demografi_pasien", PRIV_READ) || $auth->IsAllowed("rm_informasi_rekap_pasien_per_agama", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_pasien_masuk_inap", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_pasien_sedang_dirawat", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_pasien_pulang", PRIV_READ) || $auth->IsAllowed("rm_informasi_index_dokter", PRIV_READ) || $auth->IsAllowed("rm_informasi_rekap_pasien_per_cara_bayar", PRIV_READ) || $auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ) || $auth->IsAllowed("rm_informasi_lap_berkas_pasien", PRIV_READ) || $auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rm_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_emr_full/cetak_emr_view.php" target="_blank" style="font-size:15px">Cetak EMR</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_pengunjung", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung/report_pasien.php" target="_blank" style="font-size:15px">Laporan Pengunjung</a></li><? } ?>
                      <!--         <? if ($auth->IsAllowed("rm_informasi_rekam_medik_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekam_medik_pasien/cetak_emr_view.php" target="_blank" style="font-size:15px">Rekam Medik Pasien</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("rm_informasi_lap_rekam_medik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rekam_medik/lap_rm.php" target="_blank" style="font-size:15px">Lap Rekam Medik</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_rekam_medik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_imunisasi/lap_imunisasi.php" target="_blank" style="font-size:15px">Lap Imunisasi</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_inacbg", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_inacbg/lap_inacbg.php" target="_blank" style="font-size:15px">Lap INACBG</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_irj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_10_bsr_diagnosa/rekap_10_bsr_diagnosa.php" target="_blank" style="font-size:15px">Lap 10 Besar Diagnosa</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_irj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_10_bsr_tindakan/rekap_10_bsr_tindakan.php" target="_blank" style="font-size:15px">Lap 10 Besar Tindakan</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_rekap_pasien_per_jenis_kelamin", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_jns_kelamin/rekap_jenis_kelamin.php" target="_blank" style="font-size:15px">Rekap Pasien Per Jenis Kelamin</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_demografi_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_demografi/rekap_propinsi.php" target="_blank" style="font-size:15px">Demografi Pasien</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_rekap_pasien_per_agama", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_agama/rekap_agama.php" target="_blank" style="font-size:15px">Rekap Pasien Per Agama</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_pasien_masuk_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_masuk_inap/report_pasien_masuk.php" target="_blank" style="font-size:15px">Lap Pasien Masuk Inap</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_pasien_sedang_dirawat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_masih_dirawat/report_pasien_dirawat.php" target="_blank" style="font-size:15px">Lap Pasien Sedang Dirawat</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_lap_pasien_pulang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_pulang/report_pasien_pulang.php" target="_blank" style="font-size:15px">Lap Pasien Pulang</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_index_dokter", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_dokter/lap_bridging_.php" target="_blank" style="font-size:15px">Index Dokter</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_rekap_pasien_per_cara_bayar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_pasien_instalasi/rekap_pasien.php" target="_blank" style="font-size:15px">Rekap Pasien Per Cara Bayar</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_kematian/report_pasien_pulang.php" target="_blank" style="font-size:15px">Index Kematian</a></li><? } ?>

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_obat_diare/lap_obat_diare_view.php" target="_blank" style="font-size:15px">Rekap Pasien Diare</a></li><? } ?>

                      <!--  -->

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_penyakit/index_penyakit_irj_view.php" target="_blank" style="font-size:15px">Index Penyakit IRJ</a></li><? } ?>

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_penyakit_igd/index_penyakit_igd_view.php" target="_blank" style="font-size:15px">Index Penyakit IGD</a></li><? } ?>

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_penyakit_irna/index_penyakit_irna.php" target="_blank" style="font-size:15px">Index Penyakit IRNA</a></li><? } ?>

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>indeks_operasi/indeks_operasi_view.php" target="_blank" style="font-size:15px">Index Operasi</a></li><? } ?>

                      <? if ($auth->IsAllowed("rm_informasi_index_kematian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kb/lap_kb_view.php" target="_blank" style="font-size:15px">Laporan KB</a></li><? } ?>


                      <? if ($auth->IsAllowed("rm_informasi_lap_berkas_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>laporan_tracer/laporan_tracer.php" target="_blank" style="font-size:15px">Laporan Berkas Pasien</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>trace_history_pasien/history_pasien.php" target="_blank" style="font-size:15px">History Kunjungan Pasien</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kehamilan/lap_kehamilan.php" target="_blank" style="font-size:15px">Laporan Hamil Baru</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ttd_dokter/ttd_dokter_view.php" target="_blank" style="font-size:15px">Daftar ttd Dokter</a></li><? } ?>
                      <? if ($auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_dirujuk/lap_pasien_dirujuk_view.php" target="_blank" style="font-size:15px">Laporan Pasien Dirujuk Keluar</a></li>
                    <? } ?>

                    <? if ($auth->IsAllowed("rm_informasi_history_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_igd/lap_pasien_bersalin.php" target="_blank" style="font-size:15px">Laporan Pasien Bersalin</a></li>
                  <? } ?>
                </ul>
              </li>
            <?php } ?>
            <?php if ($auth->IsAllowed("rm_sp2rs_rl_1_2", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_1", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_2", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_3", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_4", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_5", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_6", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_14", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_3_15", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_4_a", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_5_1", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_5_2", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_5_3", PRIV_READ) || $auth->IsAllowed("rm_sp2rs_rl_5_4", PRIV_READ)) { ?>
              <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th-large"></i>&nbsp;SP2RS <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">

                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_a", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_4_a/lap_rl_4_a.php" target="_blank" style="font-size:15px">RL 4.a</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_4_b/lap_rl_4_b.php" target="_blank" style="font-size:15px">RL 4.b</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rujukan/lap_rujukan_view.php" target="_blank" style="font-size:15px">RL 3.2 RAWAT DARURAT</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rujukan/lap_rujukan_view_peinatologi.php" target="_blank" style="font-size:15px">RL 3.5 PEINATOLOGI</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rujukan_operasi/lap_rujukan_view_operasi.php" target="_blank" style="font-size:15px">RL 3.6 PEMBEDAHAN</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rujukan_314/lap_rujukan_314_view.php" target="_blank" style="font-size:15px">RL 3.14 RUJUKAN</a></li><? } ?>
                  <? if ($auth->IsAllowed("rm_sp2rs_rl_4_b", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_34/lap_rl_34_view.php" target="_blank" style="font-size:15px">RL 3.4 KEBIDANAN</a></li><? } ?>


                </ul>
              </li>
            <?php } ?>
            <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Rekam Medik -->

            <!-- Menu Operasi -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '9' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppOperasi = $dtaccess->Fetch($rs);
            if ($authAppOperasi) {
              ?>
              <li><a><i class="glyphicon glyphicon-scissors"></i>&nbsp;&nbsp; OPERASI <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("operasi_proses_rencana_operasi", PRIV_READ) || $auth->IsAllowed("operasi_proses_tindakan_operasi", PRIV_READ) || $auth->IsAllowed("operasi_proses_operasi", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("operasi_proses_rencana_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rencana_operasi/penata_jasa_edit_view.php" target="_blank" style="font-size:15px">Rencana Operasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_proses_tindakan_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_operasi/pemeriksaan_view.php" target="_blank" style="font-size:15px">Tindakan Operasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_proses_rencana_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_operasi/pemeriksaan_view_mundur.php" target="_blank" style="font-size:15px">Tindakan Operasi Mundur</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_proses_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>operasi/penata_jasa_edit_view.php" target="_blank" style="font-size:15px">Operasi</a></li><? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("operasi_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("operasi_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("operasi_informasi_lap_operasi", PRIV_READ) || $auth->IsAllowed("operasi_informasi_lap_pemakaian_bhp_ibs", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("operasi_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_operasi/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan_operasi.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_proses_rencana_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_indikasisc/lap_indikasisc_view.php" target="_blank" style="font-size:15px">Laporan Indikasi Operasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_10_besar_tindakan.php" target="_blank" style="font-size:15px">Laporan 10 Besar Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("operasi_informasi_lap_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Operasi</a></li> <? } ?>
                        <? if ($auth->IsAllowed("operasi_informasi_lap_pemakaian_bhp_ibs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Pemakaian BHP IBS</a></li><?php } ?>
                      </ul>
                    </li>
                  <?php } ?>

                  <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>

                    </ul>
                  </li>
                <?php } ?>

                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                 <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>

          <?php } ?>
          <!-- END Menu Operasi -->

          <!-- Menu Laboratorium -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '6' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppLab = $dtaccess->Fetch($rs);
          if ($authAppLab) {
            ?>
            <li><a><i class="glyphicon glyphicon-tint"></i>&nbsp;&nbsp; LABORATORIUM <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("lab_master_master_hasil_lab", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("lab_master_master_hasil_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>hasil_lab/hasil_lab_view.php" target="_blank" style="font-size:15px">Master Hasil Lab</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("lab_proses_pemeriksaan", PRIV_READ) || $auth->IsAllowed("lab_proses_pemeriksaan_luar", PRIV_READ) || $auth->IsAllowed("lab_proses_input_hasil_lab", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("lab_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_lab/pemeriksaan_lab_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><? } ?>
                      <? if ($auth->IsAllowed("lab_proses_pemeriksaan_luar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_lab/pemeriksaan_lab_luar_view.php" target="_blank" style="font-size:15px">Pemeriksaan Lab Luar</a></li><?php } ?>
                      <? if ($auth->IsAllowed("lab_proses_input_hasil_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_hasil_lab_irj/input_hasil_lab_view.php" target="_blank" style="font-size:15px">Input hasil Lab</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ) || $auth->IsAllowed("lab_informasi_lap_tindakan_lab", PRIV_READ) || $auth->IsAllowed("lab_informasi_lap_waktu_tunggu", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_lab/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_lab/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                      <? if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_lab/lap_tindakan_bulan.php" target="_blank" style="font-size:15px">Rekap Tindakan Bulanan</a></li><? } ?>
                      <? if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_lab/lap_penerimaan_lab.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><? } ?>

                      <? if ($auth->IsAllowed("lab_informasi_lap_kunjungan_lab", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_lab/lap_jasdok.php" target="_blank" style="font-size:15px">Laporan Tindakan Dokter</a></li><? } ?>
                      <? if ($auth->IsAllowed("lab_informasi_lap_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_lab/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu Laboratorium -->

          <!-- Menu Radiologi -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '7' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppRad = $dtaccess->Fetch($rs);
          if ($authAppRad) {
            ?>
            <li><a><i class="glyphicon glyphicon-erase"></i>&nbsp;&nbsp; RADIOLOGI <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("rad_master_master_template", PRIV_READ) || $auth->IsAllowed("rad_master_master_kelompok", PRIV_READ) || $auth->IsAllowed("rad_master_master_sub_kelompok", PRIV_READ) || $auth->IsAllowed("rad_master_master_film", PRIV_READ) || $auth->IsAllowed("rad_master_dokter_radiologi", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rad_master_master_template", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_template/template_view.php" target="_blank" style="font-size:15px">Master Template</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_master_master_kelompok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_kelompok/kelompok_view.php" target="_blank" style="font-size:15px">Master Kelompok</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_master_master_sub_kelompok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_sub_kelompok/sub_kelompok_view.php" target="_blank" style="font-size:15px">Master Sub Kelompok</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_master_master_film", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_film/film_view.php" target="_blank" style="font-size:15px">Master Film</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_master_dokter_radiologi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_dokter/dokter_view.php" target="_blank" style="font-size:15px">Dokter Radiologi</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rad_proses_pemeriksaan", PRIV_READ) || $auth->IsAllowed("rad_proses_radiologi_luar", PRIV_READ) || $auth->IsAllowed("rad_proses_input_resume", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rad_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_radiologi/pemeriksaan_radiologi_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_proses_radiologi_luar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_radiologi/pemeriksaan_radiologi_luar_view.php" target="_blank" style="font-size:15px">Pemeriksaan Rad. Luar</a></li><?php } ?>
                      <? if ($auth->IsAllowed("rad_proses_input_resume", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>resume_radiologi/penata_jasa_edit_view.php" target="_blank" style="font-size:15px">Input Resume</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("rad_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("rad_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("rad_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("rad_informasi_lap_penerimaan_resume", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("rad_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_radiologi/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_informasi_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_radiologi/lap_jasdok.php" target="_blank" style="font-size:15px">Laporan Tindakan Dokter</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_informasi_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_radiologi/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>

                      <? if ($auth->IsAllowed("rad_informasi_lap_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_radiologi/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                      <? if ($auth->IsAllowed("rad_informasi_lap_penerimaan_resume", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_resume/report_pasien.php" target="_blank" style="font-size:15px">Laporan Penerimaan Resume</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><? // } 
                      ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("irj_informasi_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_tindakan", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("irj_informasi_lap_status_pasien", PRIV_READ) || $auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu Radiologi -->

          <!-- Menu IPJ -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '18' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppIPJ = $dtaccess->Fetch($rs);
          if ($authAppIPJ) {
            ?>
            <li><a><i class="glyphicon glyphicon-object-align-right"></i>&nbsp;&nbsp; IPJ <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("ipj_registrasi_tarik", PRIV_READ) || $auth->IsAllowed("ipj_registrasi_ipj", PRIV_READ) || $auth->IsAllowed("ipj_pemeriksaan_ipj", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <!-- <? if ($auth->IsAllowed("ipj_registrasi_tarik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_ipj_tarik/registrasi_ipj_view.php" target="_blank" style="font-size:15px">Registrasi</a></li><? } ?>
                      <? if ($auth->IsAllowed("ipj_registrasi_ipj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_ipj/registrasi_pasien.php" target="_blank" style="font-size:15px">Registrasi Luar</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("ipj_pemeriksaan_ipj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_ipj/pemeriksaan_ipj_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("ipj_lap_kunjungan", PRIV_READ) || $auth->IsAllowed("ipj_lap_tindakan", PRIV_READ) || $auth->IsAllowed("ipj_waktu_tunggu", PRIV_READ) || $auth->IsAllowed("ipj_lap_pasien", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("ipj_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_ipj/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                      <? if ($auth->IsAllowed("ipj_lap_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_ipj/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                      <!-- <? if ($auth->IsAllowed("ipj_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_ipj/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("ipj_lap_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_ipj/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Pasien IPJ</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
          <!-- END Menu IPJ -->

          <!-- Menu Apotik -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '8' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppApotik = $dtaccess->Fetch($rs);
          if ($authAppApotik) {
            ?>
            <li><a><i class="glyphicon glyphicon-baby-formula"></i>&nbsp;&nbsp; APOTIK <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php if ($auth->IsAllowed("apo_master_kategori_barang", PRIV_READ) || $auth->IsAllowed("apo_master_satuan_barang", PRIV_READ) || $auth->IsAllowed("apo_master_petunjuk_dosis", PRIV_READ) || $auth->IsAllowed("apo_master_setup_barang", PRIV_READ) || $auth->IsAllowed("apo_master_paket", PRIV_READ) || $auth->IsAllowed("apo_master_obat_narkotika", PRIV_READ) || $auth->IsAllowed("apo_master_obat_psikotropika", PRIV_READ) || $auth->IsAllowed("apo_master_aturan_pakai", PRIV_READ) || $auth->IsAllowed("apo_master_jam_aturan_pakai", PRIV_READ) || $auth->IsAllowed("apo_master_aturan_minum", PRIV_READ) || $auth->IsAllowed("apo_master_konfig_apotik", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apo_master_kategori_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_barang/grup_item_view.php" target="_blank" style="font-size:15px">Kat. Barang</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_satuan_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sat_barang/satuan_view.php" target="_blank" style="font-size:15px">Satuan Barang</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_petunjuk_dosis", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>petunjuk/petunjuk_view.php" target="_blank" style="font-size:15px">Petunjuk Dosis</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_setup_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang/item_view.php" target="_blank" style="font-size:15px">Setup Barang</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_paket", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>paket_farmasi/role_view.php" target="_blank" style="font-size:15px">Paket</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_obat_narkotika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>narkotika/narkotika_view.php" target="_blank" style="font-size:15px">Obat Narkotika</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_obat_psikotropika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>psikotropika/psikotropika_view.php" target="_blank" style="font-size:15px">Obat Psikotropika</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_aturan_pakai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>aturan_pakai/aturan_pakai_view.php" target="_blank" style="font-size:15px">Aturan Pakai</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_jam_aturan_pakai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jam_aturan_pakai/jam_jadwal_view.php" target="_blank" style="font-size:15px">Jam Aturan Pakai</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_master_aturan_minum", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>aturan_minum/aturan_minum_view.php" target="_blank" style="font-size:15px">Aturan Minum</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_master_konfig_apotik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_apotik/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi Apotik</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apo_minta_minta_barang", PRIV_READ) || $auth->IsAllowed("apo_minta_kirim_barang", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Permintaan <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apo_minta_minta_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>permintaan_barang/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_minta_kirim_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pengiriman_barang/transfer_stok_view.php" target="_blank" style="font-size:15px">Penerimaan Barang</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apo_minta_minta_barang", PRIV_READ) || $auth->IsAllowed("apo_minta_kirim_barang", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-transfer"></i>&nbsp;&nbsp; Transfer Manual <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apo_minta_minta_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>transfer_manual_pengiriman/transfer_stok_view.php" target="_blank" style="font-size:15px">Transfer Barang</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_minta_kirim_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>transfer_manual_penerimaan/transfer_stok_view.php" target="_blank" style="font-size:15px">Penerimaan Barang</a></li><? } ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apo_proses_penjualan", PRIV_READ) || $auth->IsAllowed("apo_proses_penjualan_jaminan", PRIV_READ) || $auth->IsAllowed("apo_proses_penjualan_bebas", PRIV_READ) || $auth->IsAllowed("apo_proses_retur_penjualan", PRIV_READ) || $auth->IsAllowed("apo_proses_cetak_ulang_penjualan", PRIV_READ) || $auth->IsAllowed("apo_proses_cetak_ulang_etiket", PRIV_READ) || $auth->IsAllowed("apo_proses_cetak_ulang_retur_penjualan", PRIV_READ) || $auth->IsAllowed("apo_proses_penyiapan_obat", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apo_proses_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan/penjualan_view.php" target="_blank" style="font-size:15px">Penjualan Umum</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_penjualan_jaminan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_bpjs/penjualan_view.php" target="_blank" style="font-size:15px">Penjualan Jaminan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_penjualan_bebas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan/penjualan_bebas_view.php" target="_blank" style="font-size:15px">Penjualan Bebas</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_penjualan_bebas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>retur_penjualan/retur_penjualan_view.php" target="_blank" style="font-size:15px">Retur Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_cetak_ulang_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_all_ulang_penjualan/cetak_ulang_penjualan.php" target="_blank" style="font-size:15px">Cetak Ulang Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_cetak_ulang_etiket", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_etiket/cetak_ulang_penjualan.php" target="_blank" style="font-size:15px">Cetak Ulang Etiket</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_cetak_ulang_retur_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_retur_jual/cetak_ulang_retur_view.php" target="_blank" style="font-size:15px">Cetak Ulang Retur Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_proses_penyiapan_obat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_apotik/antrian_apotik_view.php" target="_blank" style="font-size:15px">Proses Penyiapan Obat</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apo_informasi_lap_penjualan", PRIV_READ) || $auth->IsAllowed("apo_informasi_rekap_penjualan", PRIV_READ) || $auth->IsAllowed("apo_informasi_retur_penjualan", PRIV_READ) || $auth->IsAllowed("apo_informasi_lap_kinerja_apotik", PRIV_READ) || $auth->IsAllowed("apo_informasi_history_pemakaian_obat", PRIV_READ) || $auth->IsAllowed("apo_informasi_lcd", PRIV_READ) || $auth->IsAllowed("apo_informasi_lap_waktu_tunggu", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apo_informasi_lap_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penjualan_apotik/report_penjualan.php" target="_blank" style="font-size:15px">Laporan Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_informasi_lap_penjualan", PRIV_READ) && ($userId == '4fae6da463adb28b2a6a5246f103dd10' || $userId == '6b430ff96572b84f055e325226694dec' || $userId == '9a7bd31c43b680df8f8b991c26ce2742' || $userId == 'f640ba7141d81ebcf9b85b4b600f27f1' || $userId == '267822763220e15d5d880e57956b5055')) { ?><li><a href="<?php echo $ROOT; ?>rekap_tuslah/lap_tuslag_far.php" target="_blank" style="font-size:15px">Rincian Tuslah</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_informasi_rekap_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_penjualan/rekap_penjualan.php" target="_blank" style="font-size:15px">Rekap Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("apo_informasi_retur_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_retur_penjualan/report_retur_penjualan.php" target="_blank" style="font-size:15px">Laporan Retur Penjualan</a></li><? } ?>
                      <? //if ($auth->IsAllowed("administrator_proses_transaksi_ri_mona", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_koreksi_stok/koreksi.php" target="_blank" style="font-size:15px">Laporan Koreksi Stok</a></li><? //} 
                      ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_obat_kadaluarsa/lap_obat_kadaluarsa.php" target="_blank" style="font-size:15px">Lap. Tanggal Kadaluarsa Obat</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_informasi_lap_kinerja_apotik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_bulanan_apotik/rekap_bulanan.php" target="_blank" style="font-size:15px">Laporan Kinerja Apotik</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_informasi_history_pemakaian_obat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>history_pakai_obat/history_view.php" target="_blank" style="font-size:15px">History Pemakaian Obat/Alkes Pasien</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_informasi_history_pemakaian_obat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>history_pemakaian_obat_ranap/history_view_inap.php" target="_blank" style="font-size:15px">History Pemakaian Obat/Alkes Pasien Rawat Inap</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_informasi_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_apotik/lcd_apotik.php" target="_blank" style="font-size:15px">LCD Apotik</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apo_informasi_lap_waktu_tunggu", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_apotik/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Lap. Waktu Tunggu</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_informasi_opname_lap_psikotropika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_psikotropika/lap_psikotropika.php" target="_blank" style="font-size:15px">Lap. Obat Psikotropika</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_informasi_opname_lap_narkotika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_narkotika/lap_narkotika.php" target="_blank" style="font-size:15px">Lap. Obat Narkotika</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_informasi_opname_lap_narkotika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_stok_alert/lap_stok_alert.php" target="_blank" style="font-size:15px">Lap. Stok Alert</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apotik_opname_stok_opname", PRIV_READ) || $auth->IsAllowed("apotik_opname_lap_stok_opname", PRIV_READ) || $auth->IsAllowed("apotik_opname_import_stok", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Opname <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apotik_opname_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>opname/trans_opname.php" target="_blank" style="font-size:15px">Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_opname_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>opname/opname_verif.php" target="_blank" style="font-size:15px">Verifikasi Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_opname_lap_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_opname/opname.php" target="_blank" style="font-size:15px">Lap. Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_opname_import_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_opname/import_opname.php" target="_blank" style="font-size:15px">Import Stok Opname</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("apotik_informasi_opname_kartu_stok", PRIV_READ) || $auth->IsAllowed("apotik_informasi_opname_lap_semua_stok", PRIV_READ) || $auth->IsAllowed("apotik_informasi_opname_lap_psikotropika", PRIV_READ) || $auth->IsAllowed("apotik_informasi_opname_lap_narkotika", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi Opname<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("apotik_informasi_opname_kartu_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kartu_stok/histori_stok_gflk.php" target="_blank" style="font-size:15px">Apotik Kartu Stok</a></li><?php } ?>
                      <? if ($auth->IsAllowed("apotik_informasi_opname_lap_semua_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok/laporan_stok_semua_gudang_gflkxx.php" target="_blank" style="font-size:15px">Lap. Semua Stok</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Apotik -->

            <!-- Menu BPJS -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '17' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppBPJS = $dtaccess->Fetch($rs);
            if ($authAppBPJS) {
              ?>
              <li><a><i class="glyphicon glyphicon-ice-lolly"></i>&nbsp;&nbsp; BPJS / JAMINAN <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("tutup_transaksi", PRIV_READ) || $auth->IsAllowed("cetak_ulang_sep_bpjs", PRIV_READ) || $auth->IsAllowed("update_sep_bpjs", PRIV_READ) || $auth->IsAllowed("cari_sep_bpjs", PRIV_READ) || $auth->IsAllowed("approval_sep_bpjs", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("cetak_ulang_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sep/index.php" target="_blank" style="font-size:15px">Create SEP</a></li><? } ?>
                        <? if ($auth->IsAllowed("cetak_ulang_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sep/rujukan-index.php" target="_blank" style="font-size:15px">Rujukan</a></li><? } ?>
                        <? if ($auth->IsAllowed("update_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sep/sep-list.php" target="_blank" style="font-size:15px">Data SEP</a></li><? } ?>
                        <? if ($auth->IsAllowed("cari_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sep/pengajuan_new.php" target="_blank" style="font-size:15px">Pengajuan SEP</a></li><? } ?>
                        <? if ($auth->IsAllowed("cari_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sep/kontrol_inap_view.php" target="_blank" style="font-size:15px">Kunjungan Kontrol/Inap</a></li><? } ?>
                        <!-- <? if ($auth->IsAllowed("approval_sep_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>loket_jpk/approval_sep.php" target="_blank" style="font-size:15px">Approval SEP</a></li><? } ?> -->
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("bpjs_cetak_ulang_rincian", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Cetak <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("bpjs_cetak_ulang_rincian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_rincian_jaminan/cetak_view_pemeriksaan.php" target="_blank" style="font-size:15px">Cetak Ulang Rincian</a></li><? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("lap_kunjungan_bpjs", PRIV_READ) || $auth->IsAllowed("lap_pendapatan_bpjs", PRIV_READ) || $auth->IsAllowed("lap_jaminan_bpjs", PRIV_READ) || $auth->IsAllowed("lap_piutang_perorangan", PRIV_READ) || $auth->IsAllowed("ref_poli_bpjs", PRIV_READ) || $auth->IsAllowed("ref_faskes_bpjs", PRIV_READ) || $auth->IsAllowed("ref_ruang_rawat_bpjs", PRIV_READ) || $auth->IsAllowed("ref_kelas_rawat_bpjs", PRIV_READ) || $auth->IsAllowed("bpjs_informasi_rekap_global_fund", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp&nbsp&nbsp Informasi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("lap_kunjungan_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_irj/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li> <? } ?>
                        <? if ($auth->IsAllowed("lap_pendapatan_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pemb_jaminan/report_setoran_loket.php" target="_blank" style="font-size:15px">Laporan Pendapatan</a></li> <? } ?>
                        <? if ($auth->IsAllowed("lap_jaminan_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jaminan/report_setoran_cicilan.php" target="_blank" style="font-size:15px">Laporan Jaminan</a></li> <? } ?>
                        <? if ($auth->IsAllowed("lap_piutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jaminan_perorangan/report_setoran_cicilan.php" target="_blank" style="font-size:15px">Laporan Piutang Umum</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ref_poli_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_poli_bpjs/ref_poli_bpjs.php" target="_blank" style="font-size:15px">Referensi Poli BPJS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ref_faskes_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_faskes_bpjs/ref_faskes_bpjs.php" target="_blank" style="font-size:15px">Referensi Faskes BPJS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ref_ruang_rawat_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_ruangrawat_bpjs.php" target="_blank" style="font-size:15px">Referensi Ruang Rawat BPJS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ref_kelas_rawat_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_kelasrawat_bpjs.php" target="_blank" style="font-size:15px">Referensi Kelas Rawat BPJS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("bpjs_informasi_rekap_global_fund", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_global_fund/rekap_global_fund.php" target="_blank" style="font-size:15px">Rekap Global Fund</a></li> <? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp&nbsp&nbsp Dokumentasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>user_guide/user_guide_bpjs.pdf" target="_blank" style="font-size:15px">User Guide</a></li>
                    </ul>
                  </li>
                  <?php if ($auth->IsAllowed("ref_kamar_bpjs", PRIV_READ) || $auth->IsAllowed("ref_ruang_baru_bpjs", PRIV_READ) || $auth->IsAllowed("ketersediaan_kamar_bpjs", PRIV_READ) || $auth->IsAllowed("hapus_ruangan_bpjs", PRIV_READ) || $auth->IsAllowed("update_ketersediaan_bpjs", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp&nbsp&nbsp VCLAIM <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("ref_kamar_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_kamar_bpjs.php" target="_blank" style="font-size:15px">Referensi Kamar BPJS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ref_ruang_baru_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ruangan_baru.php" target="_blank" style="font-size:15px">Ruangan Baru</a></li> <? } ?>
                        <? if ($auth->IsAllowed("ketersediaan_kamar_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ketersediaan_rs.php" target="_blank" style="font-size:15px">Ketersediaan Kamar RS</a></li> <? } ?>
                        <? if ($auth->IsAllowed("hapus_ruangan_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/hapus_ruangan.php" target="_blank" style="font-size:15px">Hapus Ruangan</a></li> <? } ?>
                        <? if ($auth->IsAllowed("update_ketersediaan_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/update_ketersediaan.php" target="_blank" style="font-size:15px">Update Ketersediaan</a></li> <? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu BPJS -->

            <!-- Menu Kasir -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '5' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppKasir = $dtaccess->Fetch($rs);
            if ($authAppKasir) {
              ?>
              <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; KASIR <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("kasir_proses_pembayaran", PRIV_READ) || $auth->IsAllowed("kasir_proses_pembayaran_jaminan", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("kasir_proses_pembayaran", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_irj/kasir_pemeriksaan_view.php" target="_blank" style="font-size:15px">Pembayaran Pasien</a></li><? } ?>
                        <? if ($auth->IsAllowed("kasir_proses_pembayaran_jaminan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_jaminan/kasir_pemeriksaan_view.php" target="_blank" style="font-size:15px">Pembayaran Jaminan</a></li><? } ?>
                        <!-- <? if ($auth->IsAllowed("kasir_proses_pembayaran_jaminan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu_kasir/rekap_list.php" target="_blank" style="font-size:15px">Mutu Kasir</a></li><? } ?> -->
                      <? //if ($auth->IsAllowed("rawat_inap_rencana_pulang", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>pasien_rencana_pulang/penata_jasa_edit_view_kasir.php" target="_blank" style="font-size:15px">Rencana Pulang</a></li><? //} 
                      ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("kasir_proses_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_proses_batal_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_transaksi_deposit_masuk", PRIV_READ) || $auth->IsAllowed("kasir_transaksi_deposit_keluar", PRIV_READ) || $auth->IsAllowed("kasir_transaksi_laporan_deposit_masuk", PRIV_READ) || $auth->IsAllowed("kasir_transaksi_history_deposit", PRIV_READ) || $auth->IsAllowed("kasir_transaksi_history_deposit_rm", PRIV_READ) || $auth->IsAllowed("kasir_informasi_laporan_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_informasi_rekap_tutup_kasir", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Transaksi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("kasir_proses_tutup_kasir", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>posting_piutang_umum/posting_piutang_umum.php" target="_blank" style="font-size:15px">Belum Posting</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_transaksi_deposit_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>deposit_masuk/deposit_masuk_view.php" target="_blank" style="font-size:15px">Deposit</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_transaksi_laporan_deposit_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_deposit/lap_deposit_view.php" target="_blank" style="font-size:15px">Laporan Deposit</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_transaksi_history_deposit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_deposit_history/lap_deposit_view.php" target="_blank" style="font-size:15px">History Deposit</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_transaksi_history_deposit_rm", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_deposit_history_by_rm/lap_deposit_view.php" target="_blank" style="font-size:15px">History Deposit by No. RM</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Cetak <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_rincian_kwitansi/cetak_view_pemeriksaan.php" target="_blank" style="font-size:15px">Cetak Ulang Kwitansi</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp;<span>Pend. Lain-lain</span> <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pendapatan_lain/penjualan_bebas_view.php" target="_blank" style="font-size:15px">Penjualan</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_proses_pembayaran", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_kasir_bebas/kasir_penjualan_view.php" target="_blank" style="font-size:15px">Pembayaran</a></li><? } ?>
                      <? if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_kasir/item_view.php" target="_blank" style="font-size:15px">Master Item</a></li><? } ?>
                      <? if ($auth->IsAllowed("cetak_ulang_kwitansi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_bayar_penjualan_kasir/batal_bayar_penjualan_kasir.php" target="_blank" style="font-size:15px">Batal Bayar</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("kasir_lap_pendapatan", PRIV_READ) || $auth->IsAllowed("kasir_lap_pendapatan_per_unit", PRIV_READ) || $auth->IsAllowed("kasir_lap_detail_pendapatan", PRIV_READ) || $auth->IsAllowed("kasir_lap_penerimaan_all", PRIV_READ) || $auth->IsAllowed("kasir_lap_penerimaan", PRIV_READ) || $auth->IsAllowed("kasir_lap_detail_penerimaan", PRIV_READ) || $auth->IsAllowed("kasir_lap_pendapatan_instalasi", PRIV_READ) || $auth->IsAllowed("kasir_rincian_pendapatan", PRIV_READ) || $auth->IsAllowed("kasir_lap_pendapatan_dan_penerimaan", PRIV_READ) || $auth->IsAllowed("kasir_lap_perbandingan_tafif", PRIV_READ) || $auth->IsAllowed("kasir_informasi_lap_deposit_masuk", PRIV_READ) || $auth->IsAllowed("kasir_informasi_lap_deposit_keluar", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi Kasir<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? // if ($auth->IsAllowed("kasir_lap_pendapatan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_pemb/report_setoran_loket.php" target="_blank" style="font-size:15px">Laporan Pendapatan</a></li><? //} 
                      ?>
                  <!--     <?
                       if ($auth->IsAllowed("kasir_lap_detail_pendapatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_pemb/report_setoran_loket.php" target="_blank" style="font-size:15px">Laporan Detail Pendapatan</a></li><? } 
                     ?> -->
                     <!--       <? if ($auth->IsAllowed("kasir_lap_detail_pendapatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pendapatan_lain/report_penjualan.php" target="_blank" style="font-size:12px">Laporan Pendapatan Lain-lain</a></li><? } ?> -->
                     <? if ($auth->IsAllowed("kasir_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan/report_setoran_cicilan.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><? } ?>
                     <? if ($auth->IsAllowed("kasir_lap_detail_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_penerimaan/report_detail_penerimaan.php" target="_blank" style="font-size:15px">Laporan Detail Penerimaan</a></li><? } ?>
                     <? if ($auth->IsAllowed("kasir_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_bank/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan Bank</a></li><? } ?>
                     <? if ($auth->IsAllowed("administrator_informasi_laporan_remunerasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jasa_dokter/lap_jasa_dokter.php" target="_blank" style="font-size:15px">Laporan Jasa Dokter</a></li><? } ?>
                     <!-- <? if ($auth->IsAllowed("kasir_lap_pendapatan_dan_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_rincian_pendapatan/rekap_pendapatan_penerimaan.php" target="_blank" style="font-size:15px">Rekap Pendapatan dan Penerimaan</a></li><? } ?> -->
                     <? if ($auth->IsAllowed("kasir_lap_perbandingan_tafif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif/report_pasien.php" target="_blank" style="font-size:15px">Laporan Perbandingan Tarif Pasien</a></li><?php } ?>

                     <? if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Registrasi Pasien</a></li><? } ?>
                      <? //if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>lap_piutang/lap_piutang_view.php" target="_blank" style="font-size:15px">Laporan Piutang</a></li><? //} 
                      ?>
                      <? //if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>laporan_pendapatan_irj_igd/index.php" target="_blank" style="font-size:15px">Laporan Pendapatan IRJ IGD</a></li><? //} 
                      ?>
                      <? //if ($auth->IsAllowed("fo_lap_kunjungan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>laporan_pendapatan_irna/index.php" target="_blank" style="font-size:15px">Laporan Pendapatan INAP</a></li><? //} 
                      ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("kasir_proses_pembayaran", PRIV_READ) || $auth->IsAllowed("kasir_proses_pembayaran_jaminan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Mutu Kasir <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? if ($auth->IsAllowed("kasir_proses_pembayaran_jaminan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu_kasir/rekap_list.php" target="_blank" style="font-size:15px">Mutu Kasir</a></li><? } ?>
                      <? if ($auth->IsAllowed("kasir_informasi_lap_deposit_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu_kasir/rekap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li><?php } ?>

                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("kasir_bp_lap_blm_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_bp_rekap_blm_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_bp_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_bp_lap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_bp_rekap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("kasir_bp_rekap_bendahara", PRIV_READ)) { ?>
                <?php } ?>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                </ul>
              </li>
            <?php } ?>

            <!-- END Menu Kasir -->

            <!-- Menu Manajemen -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '16' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppManajemen = $dtaccess->Fetch($rs);
            if ($authAppManajemen) {
              ?>
              <li><a><i class="glyphicon glyphicon-dashboard"></i>&nbsp;&nbsp; MANAJEMEN <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if (
                    $auth->IsAllowed("man_pengaturan_konfigurasi", PRIV_READ) || $auth->IsAllowed("man_pengaturan_konf_tarif", PRIV_READ) ||
                    $auth->IsAllowed("man_pengaturan_konf_antrian", PRIV_READ)
                  ) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="fa fa-home"></i>&nbsp;&nbsp; Pengaturan <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_pengaturan_konfigurasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi RS</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_pengaturan_konf_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_biaya/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi Tarif</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_pengaturan_konf_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_registrasi/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi Pelayanan</a></li><? } ?>
                      </ul>
                    </li>
                  <? } ?>
                  <?php if (
                    $auth->IsAllowed("man_user_edit_pegawai", PRIV_READ) || $auth->IsAllowed("man_user_master_satuan_kerja", PRIV_READ) ||
                    $auth->IsAllowed("man_user_jabatan", PRIV_READ) || $auth->IsAllowed("man_user_user_login", PRIV_READ) || $auth->IsAllowed("man_ganti_password", PRIV_READ)
                  ) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="fa fa-users fa-lg"></i>&nbsp;&nbsp; User <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_user_edit_pegawai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_pegawai/data_pegawai_view.php" target="_blank" style="font-size:15px">Manajemen Pegawai</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_user_master_satuan_kerja", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satker/dep_view.php" target="_blank" style="font-size:15px">Master Satuan Kerja</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_user_jabatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jabatan/role_view.php" target="_blank" style="font-size:15px">Jabatan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_user_user_login", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>user_login/hakakses_view.php" target="_blank" style="font-size:15px">User Login</a></li><? } ?>

                        <? if ($auth->IsAllowed("man_ganti_password", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php" target="_blank" style="font-size:15px">Ganti Password</a></li><? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("man_tarif_master_split_tindakan", PRIV_READ) || $auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi", PRIV_READ) || $auth->IsAllowed("man_tarif_kat_tindakan_header", PRIV_READ) || $auth->IsAllowed("man_tarif_kat_tindakan", PRIV_READ) || $auth->IsAllowed("man_tarif_master_jenis_tindakan", PRIV_READ) || $auth->IsAllowed("man_tarif_master_variabel_inacbg", PRIV_READ) || $auth->IsAllowed("man_tarif_master_tindakan", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_reg", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_pemeriksaan", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_akomodasi", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_visite", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_askep", PRIV_READ) || $auth->IsAllowed("man_tarif_tarif_header_poli", PRIV_READ) || $auth->IsAllowed("man_tarif_tarif_tindakan", PRIV_READ) || $auth->IsAllowed("man_tarif_rincian_tindakan_inap", PRIV_READ) || $auth->IsAllowed("man_tarif_setup_jenis_bayar", PRIV_READ) || $auth->IsAllowed("man_tarif_plafon_karyawan", PRIV_READ) || $auth->IsAllowed("man_tarif_master_perusahaan", PRIV_READ) || $auth->IsAllowed("man_tarif_master_margin", PRIV_READ) || $auth->IsAllowed("man_medis_detil_paket", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="fa fa-money"></i>&nbsp;&nbsp; Tarif <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_tarif_master_split_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenis_biaya/jenis_biaya_view.php" target="_blank" style="font-size:15px">Master Split Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan_header_instalasi/kat_tindakan_header_instalasi_view.php" target="_blank" style="font-size:15px">Kategori Tindakan header Intalasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_kat_tindakan_header", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan_header/kat_tindakan_header_view.php" target="_blank" style="font-size:15px">Kategori Tindakan header </a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_kat_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan/kat_tindakan_view.php" target="_blank" style="font-size:15px">Kategori Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_jenis_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_jenis_tindakan/jenis_tindakan_view.php" target="_blank" style="font-size:15px">Master Jenis Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_variabel_inacbg", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_variable_inacbg/variable_inacbg_view.php" target="_blank" style="font-size:15px">Master Variable INACBG</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tindakan/tindakan_view.php " target="_blank" style="font-size:15px">Master Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_biaya_reg", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_registrasi/biaya_view.php " target="_blank" style="font-size:15px">Biaya Registrasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_biaya_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_pemeriksaan/biaya_view.php " target="_blank" style="font-size:15px">Biaya Pemeriksaan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_biaya_akomodasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_akomodasi/biaya_akomodasi_view.php" target="_blank" style="font-size:15px">Biaya Akomodasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_biaya_visite", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_visite/biaya_visite_view.php" target="_blank" style="font-size:15px">Biaya Visite</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_biaya_askep", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_askep/biaya_askep_view.php" target="_blank" style="font-size:15px">Biaya Askep</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_tarif_header_poli", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tarif_header_poli/biaya_poli_view.php " target="_blank" style="font-size:15px">Tarif Header klinik</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_tarif_tindakan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tarif_tindakan_irj/tindakan_view.php " target="_blank" style="font-size:15px">Tarif Tindakan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_rincian_tindakan_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rincian_tindakan_irna/kategori_kassa_view.php" target="_blank" style="font-size:15px">Rincian Tindakan Inap</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_setup_jenis_bayar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenisbayar/jenis_bayar_view.php" target="_blank" style="font-size:15px">Setup Jenis Bayar</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_plafon_karyawan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>fasilitas/fasilitas_view.php" target="_blank" style="font-size:15px">Plafon Karyawan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_perusahaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>perusahaan/perusahaan_view.php" target="_blank" style="font-size:15px">Master Perusahaan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_margin", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>margin_obat/margin_view.php" target="_blank" style="font-size:15px">Master Margin Obat</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_tarif_master_margin", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>margin_bhp/margin_view.php" target="_blank" style="font-size:15px">Master Margin BHP</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_detil_paket", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>paket_poli/paket_poli_view.php" target="_blank" style="font-size:15px">Detail Paket Klinik</a></li> <? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("man_medis_setup_instalasi", PRIV_READ) || $auth->IsAllowed("man_medis_setup_sub_instalasi", PRIV_READ) || $auth->IsAllowed("man_medis_setup_poli", PRIV_READ) || $auth->IsAllowed("man_medis_jam_jadwal", PRIV_READ) || $auth->IsAllowed("man_medis_jadwal_dokter", PRIV_READ) || $auth->IsAllowed("man_tarif_master_variabel_inacbg", PRIV_READ) || $auth->IsAllowed("man_tarif_master_tindakan", PRIV_READ) || $auth->IsAllowed("man_medis_kelas_kamar", PRIV_READ) || $auth->IsAllowed("man_medis_jenis_kelas", PRIV_READ) || $auth->IsAllowed("man_medis_gedung", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_visite", PRIV_READ) || $auth->IsAllowed("man_tarif_biaya_askep", PRIV_READ) || $auth->IsAllowed("man_medis_kamar", PRIV_READ) || $auth->IsAllowed("man_medis_kondisi_akhir", PRIV_READ) || $auth->IsAllowed("man_medis_cara_masuk_inap", PRIV_READ) || $auth->IsAllowed("man_medis_cara_keluar_inap", PRIV_READ) || $auth->IsAllowed("man_medis_master_prosedur_masuk", PRIV_READ) || $auth->IsAllowed("man_medis_master_bor_kamar", PRIV_READ) || $auth->IsAllowed("man_medis_kategori_icd", PRIV_READ) || $auth->IsAllowed("man_medis_det_kat_icd", PRIV_READ) || $auth->IsAllowed("man_medis_propinsi", PRIV_READ) || $auth->IsAllowed("man_medis_rujukan", PRIV_READ) || $auth->IsAllowed("man_medis_icd9", PRIV_READ) || $auth->IsAllowed("man_medis_icd10", PRIV_READ) || $auth->IsAllowed("man_medis_kamar_operasi", PRIV_READ) || $auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Medis <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_medis_setup_instalasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>instalasi/instalasi_view.php" target="_blank" style="font-size:15px">Setup Instalasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_setup_sub_instalasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sub_instalasi/sub_instalasi_view.php" target="_blank" style="font-size:15px">Setup Sub Instalasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_setup_poli", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>setup_poli/jenis_poli_view.php" target="_blank" style="font-size:15px">Setup Poli</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_jam_jadwal", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jam_jadwal/jam_jadwal_view.php" target="_blank" style="font-size:15px">Jam Jadwal</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_jadwal_dokter", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jadwal_dokter/jadwal_dokter_view.php" target="_blank" style="font-size:15px">Jadwal Dokter</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_kelas_kamar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kelas_kamar/tindakan_view.php" target="_blank" style="font-size:15px">Kelas Kamar</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_jenis_kelas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenis_kelas/jenis_kelas_view.php" target="_blank" style="font-size:15px">Jenis Kelas</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_gedung", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ruang_rawat/ruang_rawat_view.php" target="_blank" style="font-size:15px">Gedung / Ruang Rawat</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_kamar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kamar/kamar_view.php" target="_blank" style="font-size:15px">Kamar</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_kondisi_akhir", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kondisi_akhir/kondisi_view.php" target="_blank" style="font-size:15px">Kondisi Akhir</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_cara_masuk_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_cara_masuk/masuk_view.php" target="_blank" style="font-size:15px">Master Cara Masuk</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_cara_keluar_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_cara_keluar_inap/cara_keluar_view.php" target="_blank" style="font-size:15px">Master Cara Keluar Inap</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_prosedur_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_prosedur_masuk/prosedur_view.php" target="_blank" style="font-size:15px">Master Prosedur Masuk</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_bor_kamar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>bor_kamar/bor_kamar_view.php" target="_blank" style="font-size:15px">Master Bor Kamar</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_ganti_password", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_pendidikan/pendidikan_view.php" target="_blank" style="font-size:15px">Master Pendidikan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_ganti_password", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_pekerjaan/pekerjaan_view.php" target="_blank" style="font-size:15px">Master Pekerjaan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_ganti_password", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_status_perkawinan/perkawinan_view.php" target="_blank" style="font-size:15px">Master Status Perkawinan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_kategori_icd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_icd/kat_icd_view.php" target="_blank" style="font-size:15px">Kategori ICD</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_det_kat_icd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>det_kat_icd/det_icd_view.php" target="_blank" style="font-size:15px">Det kat ICD</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_propinsi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>propinsi/propinsi_view.php" target="_blank" style="font-size:15px">Propinsi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_rujukan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rujukan/rujukan_view.php" target="_blank" style="font-size:15px">Rujukan</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_icd9", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_diagnosa/diagnosa_view.php" target="_blank" style="font-size:15px">Master Diagnosa</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_icd9", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_procedure/procedure_view.php" target="_blank" style="font-size:15px">Master Procedure</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_icd9", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>icd9/icd9_view.php" target="_blank" style="font-size:15px">ICD9</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_icd10", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>icd10/icd_view.php" target="_blank" style="font-size:15px">ICD10</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_kamar_operasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_kamar_operasi/master_kamar_operasi_view.php" target="_blank" style="font-size:15px">Master Kamar Operasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sebab_sakit/sebab_sakit_view.php" target="_blank" style="font-size:15px">Sebab Sakit</a></li><? } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("man_import_pegawai", PRIV_READ) || $auth->IsAllowed("man_import_pasien", PRIV_READ) || $auth->IsAllowed("man_import_tarif", PRIV_READ) || $auth->IsAllowed("man_import_obat", PRIV_READ)) { ?>
                    <li><a><i class="fa fa-edit"></i>&nbsp;&nbsp; Import <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_import_pegawai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_pegawai/import_pegawai.php">Import Pegawai</a></li><?php } ?>
                        <? if ($auth->IsAllowed("man_import_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_pasien/import_pasien.php">Import Pasien</a></li><?php } ?>
                        <? if ($auth->IsAllowed("man_import_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_tarif_irj/import_tarif_irj.php">Import Tarif</a></li><?php } ?>
                        <? if ($auth->IsAllowed("man_import_obat", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_barang/import_barang_muslimat.php">Import Obat</a></li><?php } ?>
                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("man_data_reset_pegawai", PRIV_READ) || $auth->IsAllowed("man_data_reset_pasien", PRIV_READ) || $auth->IsAllowed("man_data_reset_tarif", PRIV_READ)) { ?>
                    <li><a><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Data <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("man_data_reset_pegawai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reset_pegawai/reset_pegawai.php">Reset Pegawai</a></li> <?php } ?>
                        <? if ($auth->IsAllowed("man_data_reset_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reset_pasien/reset_pasien.php">Reset Pasien</a></li><?php } ?>
                        <? if ($auth->IsAllowed("man_data_reset_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reset_tarif/reset_tarif.php">Reset Tarif</a></li><?php } ?>
                      </ul>
                    </li>
                  <?php } ?>
<!--                   <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li> -->
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Manajemen -->

            <!-- Menu Administrasi -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '14' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppAdministrasi = $dtaccess->Fetch($rs);
            if ($authAppAdministrasi) {
              ?>
              <li><a><i class="glyphicon glyphicon-phone"></i>&nbsp;&nbsp; ADMINISTRASI <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("administrator_admin_edit_transaksi", PRIV_READ) || $auth->IsAllowed("administrator_admin_edit_antrian", PRIV_READ) || $auth->IsAllowed("administrator_admin_ganti_password", PRIV_READ) || $auth->IsAllowed("administrator_admin_user_login", PRIV_READ) || $auth->IsAllowed("administrator_admin_convert_db_flat", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Admin <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                      <? //if($auth->IsAllowed("fo_loket_penata_jasa",PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>penata_jasa_irj/ar_main.php" <? //if($auth->IsNewTab("fo_loket_penata_jasa")) { echo"target='_blank'"; } 
                                                                                      ?> target="_blank" style="font-size:15px">Penata Jasa IRJ</a></li> <? //} 
                                                                                      ?>
                      <? //if($auth->IsAllowed("fo_loket_penata_jasa",PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>penata_jasa_irna/ar_main.php" <? //if($auth->IsNewTab("fo_loket_penata_jasa")) { echo"target='_blank'"; } 
                                                                                        ?> target="_blank" style="font-size:15px">Penata Jasa IRNA</a></li> <? //} 
                                                                                        ?>
                                                                                        <? if ($auth->IsAllowed("administrator_admin_edit_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_log/data_log.php" target="_blank" style="font-size:15px">Data Log</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_admin_ganti_password", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php" target="_blank" style="font-size:15px">Ganti Password</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_admin_user_login", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>user_login/hakakses_view.php" target="_blank" style="font-size:15px">User Login</a></li><? } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                  <?php if ($auth->IsAllowed("administrator_proses_registrasi_mundur", PRIV_READ) || $auth->IsAllowed("administrator_proses_pemeriksaan_irj_mundur", PRIV_READ) || $auth->IsAllowed("administrator_proses_pemeriksaan_igd_mundur", PRIV_READ) || $auth->IsAllowed("administrator_proses_status_pasien", PRIV_READ) || $auth->IsAllowed("administrator_proses_batal_bayar_kwitansi", PRIV_READ) || $auth->IsAllowed("administrator_proses_ganti_jenis_bayar", PRIV_READ) || $auth->IsAllowed("administrator_proses_batal_piutang", PRIV_READ) || $auth->IsAllowed("administrator_proses_batal_bayar_inap", PRIV_READ) || $auth->IsAllowed("administrator_proses_edit_tanggal_inap", PRIV_READ) || $auth->IsAllowed("administrator_proses_kosongkan_bed", PRIV_READ) || $auth->IsAllowed("administrator_proses_transaksi_ri_mona", PRIV_READ)  || $auth->IsAllowed("apo_proses_penjualan", PRIV_READ)) { ?>
                                                                                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                                                                                      <ul class="nav child_menu">
                                                                                        <? if ($auth->IsAllowed("administrator_proses_registrasi_mundur", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien_mundur/registrasi_pasien_awal.php" target="_blank" style="font-size:15px">Registrasi Pasien Mundur</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("apo_proses_penjualan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_mundur/penjualan_view.php" target="_blank" style="font-size:15px">Penjualan Mundur</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_pemeriksaan_irj_mundur", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irj/pemeriksaan_irj_view_mundur.php" target="_blank" style="font-size:15px">Pemeriksaan IRJ Mundur</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_pemeriksaan_igd_mundur", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd/pemeriksaan_igd_view_mundur.php" target="_blank" style="font-size:15px">Pemeriksaan IGD Mundur</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_status_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_status_irj/pasien_view.php" target="_blank" style="font-size:15px">Status Pasien</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("man_pengaturan_konfigurasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>transfer_tindakan/pasien_view.php" target="_blank" style="font-size:15px">Transfer Tindakan</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_batal_bayar_kwitansi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_bayar_kwitansi/batal_bayar_view.php" target="_blank" style="font-size:15px">Batal Bayar Kwitansi</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_ganti_jenis_bayar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_cara_bayar/ganti_cara_bayar.php" target="_blank" style="font-size:15px">Ganti Jenis Bayar</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_batal_piutang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_piutang/batal_piutang.php" target="_blank" style="font-size:15px">Batal Bayar Jaminan</a></li><? } ?>
                                                                                        <!-- <? if ($auth->IsAllowed("administrator_proses_batal_bayar_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_bayar_irna/batal_bayar_inap_view.php" target="_blank" style="font-size:15px">Batal Bayar Inap</a></li><? } ?> -->
                                                                                        <? if ($auth->IsAllowed("administrator_proses_edit_tanggal_inap", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_tgl_inap/pasien_view.php" target="_blank" style="font-size:15px">Edit Tanggal Inap</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_kosongkan_bed", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kosongkan_bed/penata_jasa_edit_view.php" target="_blank" style="font-size:15px">Kosongkan Bed</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_transaksi_ri_mona", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna/pemeriksaan_irna_view_mundur.php" target="_blank" style="font-size:15px">Transakasi RI Mona</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_transaksi_ri_mona", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jurnal_sementara/cashier_report_journal.php" target="_blank" style="font-size:15px">Editor Jurnal</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_proses_transaksi_ri_mona", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penyesuaian_stok/trans_koreksi.php" target="_blank" style="font-size:15px">Koreksi Stok Apotek</a></li><? } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                  <?php if ($auth->IsAllowed("administrator_informasi_laporan_honor_dokter", PRIV_READ) || $auth->IsAllowed("administrator_informasi_laporan_remunerasi", PRIV_READ)) { ?>
                                                                                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                                                                                      <ul class="nav child_menu">
                                                                                        <? if ($auth->IsAllowed("administrator_informasi_laporan_honor_dokter", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_pemb/report_setoran_loket.php" target="_blank" style="font-size:15px">Laporan Honor Dokter</a></li><? } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                  <?php if ($auth->IsAllowed("administrator_antrian_konfig_antrian", PRIV_READ) || $auth->IsAllowed("administrator_antrian_lcd_antrian", PRIV_READ) || $auth->IsAllowed("administrator_antrian_tampilan_pasien", PRIV_READ) || $auth->IsAllowed("administrator_antrian_reset_antrian", PRIV_READ) || $auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?>
                                                                                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Antrian <span class="fa fa-chevron-down"></span></a>
                                                                                      <ul class="nav child_menu">
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_konfig_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konf_antrian/konfigurasi_antrian.php" target="_blank" style="font-size:15px">Konfigurasi Antrian</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/antrian.php" target="_blank" style="font-size:15px">LCD Antrian</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_tampilan_pasien", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien/antri_tambah.php" target="_blank" style="font-size:15px">Tampilan Pasien</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_reset_antrian", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reset_antrian/reset_antrian.php" target="_blank" style="font-size:15px">Reset Antrian</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_1.php" target="_blank" style="font-size:15px">LCD 1</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_2.php" target="_blank" style="font-size:15px">LCD 2</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_3.php" target="_blank" style="font-size:15px">LCD 3</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_4.php" target="_blank" style="font-size:15px">LCD 4</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_5.php" target="_blank" style="font-size:15px">LCD 5</a></li><? } ?>
                                                                                        <? if ($auth->IsAllowed("administrator_antrian_lcd", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_6.php" target="_blank" style="font-size:15px">LCD 6</a></li><? } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                </ul>
                                                                              </li>
                                                                            <?php } ?>
                                                                            <!-- END Menu Administrasi -->

                                                                            <!-- Menu Logistik -->
                                                                            <?
                                                                            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '22' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
                                                                            $rs = $dtaccess->Execute($sql);
                                                                            $authAppLogistik = $dtaccess->Fetch($rs);
                                                                            if ($authAppLogistik) {
                                                                              ?>
                                                                              <li><a><i class="glyphicon glyphicon-briefcase"></i>&nbsp;&nbsp; LOGISTIK <span class="fa fa-chevron-down"></span></a>
                                                                                <ul class="nav child_menu">
                                                                                  <?php if ($auth->IsAllowed("logistik_master_konfig", PRIV_READ) || $auth->IsAllowed("logistik_master_gudang", PRIV_READ) || $auth->IsAllowed("logistik_master_pengirim", PRIV_READ) || $auth->IsAllowed("logistik_master_supplier", PRIV_READ) || $auth->IsAllowed("logistik_master_kat_barang", PRIV_READ) || $auth->IsAllowed("logistik_master_satuan_barang", PRIV_READ) || $auth->IsAllowed("logistik_master_petunjuk_dosis", PRIV_READ) || $auth->IsAllowed("logistik_master_setup_barang", PRIV_READ) || $auth->IsAllowed("logistik_master_setup_barang_per_gudang", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_generik", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_non_generik", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_fornas", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_non_fornas", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_katalog", PRIV_READ) || $auth->IsAllowed("logistik_master_barang_non_katalog", PRIV_READ) || $auth->IsAllowed("logistik_master_racikan", PRIV_READ) || $auth->IsAllowed("logistik_master_paket_farmasi", PRIV_READ) || $auth->IsAllowed("logistik_master_obat_narkotika", PRIV_READ) || $auth->IsAllowed("logistik_master_obat_psikotropika", PRIV_READ) || $auth->IsAllowed("logistik_master_periode", PRIV_READ)) { ?>
                                                                                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                                                                                      <ul class="nav child_menu">
                                                                                        <? if ($auth->IsAllowed("logistik_master_konfig", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_logistik/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi</a></li> <?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_gudang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>gudang_logistik/dep_view.php" target="_blank" style="font-size:15px">Gudang</a></li> <?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_pengirim", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pengirim/pengirim_view.php" target="_blank" style="font-size:15px">Pengirim</a></li> <?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_supplier", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>supplier/supplier_view.php" target="_blank" style="font-size:15px">Supplier</a></li> <?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_kat_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_barang/grup_item_view.php" target="_blank" style="font-size:15px">Kat. Barang</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_satuan_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sat_barang/satuan_view.php" target="_blank" style="font-size:15px">Satuan Barang</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_petunjuk_dosis", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>petunjuk/petunjuk_view.php" target="_blank" style="font-size:15px">Petunjuk Dosis</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_setup_barang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang/item_view.php" target="_blank" style="font-size:15px">Setup Barang</a></li><?php } ?>

                                                                                        <? if ($auth->IsAllowed("logistik_master_setup_barang_per_gudang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_gudang/item_gudang_view.php" target="_blank" style="font-size:15px">Setup Barang per Gudang</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_generik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_generik/item_generik_view.php" target="_blank" style="font-size:15px">Barang Generik</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_non_generik", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_non_generik/item_generik_view.php" target="_blank" style="font-size:15px">Barang Non Generik</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_fornas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_fornas/item_fornas_view.php" target="_blank" style="font-size:15px">Barang Fornas</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_non_fornas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_non_fornas/item_non_fornas_view.php" target="_blank" style="font-size:15px">Barang Non Fornas</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_katalog", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_katalog/item_katalog_view.php" target="_blank" style="font-size:15px">Barang Katalog</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_barang_non_katalog", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>barang_non_katalog/item_non_katalog_view.php" target="_blank" style="font-size:15px">Barang Non Katalog</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_racikan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>racikan/racikan_view.php" target="_blank" style="font-size:15px">Maser Racikan</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_paket_farmasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>paket_farmasi/role_view.php" target="_blank" style="font-size:15px">Paket Farmasi</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_obat_narkotika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>narkotika/narkotika_view.php" target="_blank" style="font-size:15px">Obat Narkotika</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_obat_psikotropika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>psikotropika/psikotropika_view.php" target="_blank" style="font-size:15px">Obat Psikotropika</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>periode/periode_view.php" target="_blank" style="font-size:15px">Periode Stok</a></li><?php } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                  <?php if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ) || $auth->IsAllowed("logistik_penerimaan_lap_penerimaan", PRIV_READ) || $auth->IsAllowed("logistik_penerimaan_edit_penerimaan", PRIV_READ)) { ?>
                                                                                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Penerimaan <span class="fa fa-chevron-down"></span></a>
                                                                                      <ul class="nav child_menu">
                                                                                        <? if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>order_pembelian/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_penerimaan_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><?php } ?>
                                                                                        <? if ($auth->IsAllowed("logistik_penerimaan_edit_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_penerimaan_barang/trans_beli_po_terima_edit.php" target="_blank" style="font-size:15px">Edit Faktur Masuk</a></li><?php } ?>
                                                                                      </ul>
                                                                                    </li>
                                                                                  <?php } ?>
                                                                                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; BHP <span class="fa fa-chevron-down"></span></a>
                                                                                    <ul class="nav child_menu">
                    <? //if ($auth->IsAllowed("logistik_master_setup_barang", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>barang_bhp/item_view.php" target="_blank" style="font-size:15px">Setup Barang BHP</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>order_pembelian_bhp/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk BHP</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_bhp/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_distribusi_pengiriman_medis", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>pengiriman_bhp/transfer_stok_view.php" target="_blank" style="font-size:15px">Pengiriman BHP</a></li><?php // } 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_distribusi_pengiriman_medis", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_pengiriman_bhp/report_sbbk.php" target="_blank" style="font-size:15px">Laporan Pengiriman BHP</a></li><?php // } 
                    ?>
                    <? //if($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk",PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>laporan_bhps/laporan_bhps_view.php" target="_blank" style="font-size:15px">Laporan BHP</a></li><?php //} 
                    ?>
                    <? //if($auth->IsAllowed("logistik_penerimaan_lap_penerimaan",PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>rekap_bhps/rekap_bhps.php" target="_blank" style="font-size:15px">Rekap BHP</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_master_supplier", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>supplier_bhp/supplier_view.php" target="_blank" style="font-size:15px">Supplier BHP</a></li> <?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_informasi_kartu_stok", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_kartu_stok_bhp/histori_stok_gflk.php" target="_blank" style="font-size:15px">Logistik Kartu Stok</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok_bhp/laporan_stok_semua_gudang_gflkxx.php" target="_blank" style="font-size:15px">Lap. Semua Stok BHP</a></li><?php //} 
                    ?>
                    <li><a href="<?php echo $ROOT; ?>opname_bhp/trans_opname.php" target="_blank" style="font-size:15px">Stok Opname</a></li>
                    <li><a href="<?php echo $ROOT; ?>opname_bhp/opname_verif.php" target="_blank" style="font-size:15px">Verifikasi Stok Opname</a></li>
                    <li><a href="<?php echo $ROOT; ?>lap_opname_bhp/opname.php" target="_blank" style="font-size:15px">Lap. Stok Opname</a></li>
                  </ul>
                </li>

                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Reagen Lab <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    <? //if ($auth->IsAllowed("logistik_master_setup_barang", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>barang_lab/item_view.php" target="_blank" style="font-size:15px">Setup Barang Reagen Lab</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>order_pembelian_lab/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk Reagen Lab</a></li><?php //} 
                    ?>

                    <? //if ($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_lab/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><?php //} 
                    ?>

                    <? //if ($auth->IsAllowed("logistik_distribusi_pengiriman_medis", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>pengiriman_lab/transfer_stok_view.php" target="_blank" style="font-size:15px">Pengiriman Reagen Lab</a></li><?php // } 
                    ?>



                    <? //if ($auth->IsAllowed("logistik_informasi_kartu_stok", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_kartu_stok_lab/histori_stok_gflk.php" target="_blank" style="font-size:15px">Logistik Kartu Stok</a></li><?php //} 
                    ?>
                    <? //if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok_lab/laporan_stok_semua_gudang_gflkxx.php" target="_blank" style="font-size:15px">Lap. Semua Stok BHP</a></li><?php //} 
                    ?>
                    <li><a href="<?php echo $ROOT; ?>opname_lab/trans_opname.php" target="_blank" style="font-size:15px">Stok Opname</a></li>
                    <li><a href="<?php echo $ROOT; ?>opname_lab/opname_verif.php" target="_blank" style="font-size:15px">Verifikasi Stok Opname</a></li>
                    <li><a href="<?php echo $ROOT; ?>lap_opname_lab/opname.php" target="_blank" style="font-size:15px">Lap. Stok Opname</a></li>
                  </ul>
                </li>

                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Pemakaian <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    <? //if($auth->IsAllowed("logistik_penerimaan_faktur_barang_masuk",PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>pemakaian/pemakaian_view.php" target="_blank" style="font-size:15px">Pemakaian</a></li><?php //} 
                    ?>
                    <? //if($auth->IsAllowed("logistik_penerimaan_lap_penerimaan",PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>pemakaian/report_pemakaian.php" target="_blank" style="font-size:15px">Laporan Pemakaian</a></li><?php //} 
                    ?>
                    <? //if($auth->IsAllowed("logistik_penerimaan_lap_penerimaan",PRIV_READ)) { 
                    ?><li><a href="<?php echo $ROOT; ?>pemakaian/rekap_pemakaian.php" target="_blank" style="font-size:15px">Rekap Pemakaian</a></li><?php //} 
                    ?>
                  </ul>
                </li>
                <?php if ($auth->IsAllowed("logistik_distribusi_pengiriman_medis", PRIV_READ) || $auth->IsAllowed("logistik_distribusi_lap_permintaan_medis", PRIV_READ) || $auth->IsAllowed("logistik_distribusi_lap_pengiriman_medis", PRIV_READ) || $auth->IsAllowed("logistik_distribusi_lap_kinerja_distribusi", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Distribusi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("logistik_distribusi_pengiriman_medis", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pengiriman/transfer_stok_view.php" target="_blank" style="font-size:15px">Pengiriman Obat</a></li><?php } ?>
                      <? //if($auth->IsAllowed("logistik_distribusi_pengiriman_medis",PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>transfer_stok/transfer_stok_view.php" target="_blank" style="font-size:15px">Transfer Stok</a></li><?php //} 
                      ?>
                      <? if ($auth->IsAllowed("logistik_distribusi_lap_permintaan_medis", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Medis</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_distribusi_lap_pengiriman_medis", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengiriman/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Pengiriman Medis</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_distribusi_lap_kinerja_distribusi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kinerja_distribusi/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Kinerja Distribusi</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("logistik_opname_stok_opname", PRIV_READ) || $auth->IsAllowed("logistik_opname_lap_stok_opname", PRIV_READ) || $auth->IsAllowed("logistik_opname_import_stok_opname", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Opname <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("logistik_opname_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>opname/trans_opname.php" target="_blank" style="font-size:15px">Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_opname_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>opname/opname_verif.php" target="_blank" style="font-size:15px">Verifikasi Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_opname_lap_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_opname/opname.php" target="_blank" style="font-size:15px">Lap. Stok Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_opname_lap_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_selisih_opname/opname.php" target="_blank" style="font-size:15px">Lap. Selisih Opname</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_opname_lap_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_selisih_opname/koreksi_stok_penj.php" target="_blank" style="font-size:15px">Lap. Koreksi Stok Penjualan</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_opname_import_stok_opname", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_opname/import_opname.php" target="_blank" style="font-size:15px">Import Stok Opname</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("logistik_informasi_kartu_stok", PRIV_READ) || $auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ) || $auth->IsAllowed("logistik_informasi_lap_obat_psikotropika", PRIV_READ) || $auth->IsAllowed("logistik_informasi_lap_obat_narkotika", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">

                      <? if ($auth->IsAllowed("logistik_informasi_kartu_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kartu_stok/histori_stok_gflk.php" target="_blank" style="font-size:15px">Logistik Kartu Stok</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok/laporan_stok_semua_gudang_gflkxx.php" target="_blank" style="font-size:15px">Lap. Semua Stok</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok/laporan_stok_saldo_akhir.php" target="_blank" style="font-size:15px">Lap. Selisih Semua Stok</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_semua_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_obat_kadaluarsa/lap_obat_kadaluarsa.php" target="_blank" style="font-size:15px">Lap. Tanggal Kadaluarsa Obat</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_obat_psikotropika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_psikotropika/lap_psikotropika.php" target="_blank" style="font-size:15px">Lap. Obat Psikotropika</a></li><?php } ?>
                      <? if ($auth->IsAllowed("logistik_informasi_lap_obat_narkotika", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_narkotika/lap_narkotika.php" target="_blank" style="font-size:15px">Lap. Obat Narkotika</a></li><?php } ?>
                    </ul>
                  </li>
                <?php } ?>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Logistik -->

            <!-- Menu Logistik Non Medik -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '28' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppLogistikNonMed = $dtaccess->Fetch($rs);
            if ($authAppLogistikNonMed) {
              ?>
              <li><a><i class="glyphicon glyphicon-briefcase"></i>&nbsp;&nbsp; LOGISTIK NON MEDIK<span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("log_non_med_unit", PRIV_READ) || $auth->IsAllowed("log_non_med_pengirim", PRIV_READ) || $auth->IsAllowed("log_non_med_supplier", PRIV_READ) || $auth->IsAllowed("log_non_med_kat_barang", PRIV_READ) || $auth->IsAllowed("log_non_med_sat_barang", PRIV_READ) || $auth->IsAllowed("log_non_med_setup_barang", PRIV_READ) || $auth->IsAllowed("log_non_med_periode_stok", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("logistik_master_konfig", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_non_logistik/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi</a></li> <?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_unit", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>gudang_logistik_non_medis/dep_view.php" target="_blank" style="font-size:15px">Unit</a></li> <?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_pengirim", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>pengirim_non_medis/pengirim_view.php" target="_blank" style="font-size:15px">Pengirim</a></li> <?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_supplier", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>supplier_non_medis/supplier_view.php" target="_blank" style="font-size:15px">Supplier</a></li> <?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_kat_barang", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>kat_barang_non_medis/grup_item_view.php" target="_blank" style="font-size:15px">Kat. Barang</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_sat_barang", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>sat_barang_non_medis/satuan_view.php" target="_blank" style="font-size:15px">Satuan Barang</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_setup_barang", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>barang_non_medis/item_view.php" target="_blank" style="font-size:15px">Setup Barang</a></li><?php } ?>

                        <!-- <? if ($auth->IsAllowed("logistik_master_setup_barang_per_gudang", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>barang_gudang/item_gudang_view.php" target="_blank" style="font-size:15px">Setup Barang per Gudang</a></li><?php } ?> -->

                        <? if ($auth->IsAllowed("log_non_med_periode_stok", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>periode/periode_view.php" target="_blank" style="font-size:15px">Periode Stok</a></li><?php } ?>

                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)  && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Penerimaan <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ)&& $userId!="82ff0c061bc44510fc212c49b5b83a01" ) { ?><li><a href="<?php echo $ROOT; ?>order_pembelian_non_medis/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>order_pembelian_non_medis_pembiayaan/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk Pembiayaan</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>order_pembelian_non_medis_aset/trans_beli_po_view.php" target="_blank" style="font-size:15px">Faktur Barang Masuk Aset</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>order_pembelian_non_medis_lain_lain/biaya_lain_view.php" target="_blank" style="font-size:15px">Biaya Lain - lain</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_faktur_masuk", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>permintaan_pengadaan_barang/pengadaan_view.php" target="_blank" style="font-size:15px">Permintaan / Realisasi Barang Jasa</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_non_medis/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_lain_non_med/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan Biaya Lain - lain</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan_non_medis_non_stok/report_penerimaan.php" target="_blank" style="font-size:15px">Laporan Penerimaan Aset & Pembiayaan</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jatuh_tempo_non_medis/jatuh_tempo_view.php" target="_blank" style="font-size:15px">Laporan Periode Pembelian / Jatuh Tempo</a></li><?php } ?>

                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("log_non_med_permintaan_barang", PRIV_READ)) { ?>
                    <li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("log_non_med_pemakaian_non_medis", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-refresh"></i>&nbsp;&nbsp; Pemakaian <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("log_non_med_pemakaian_non_medis", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>pemakaian_non_medis/pemakaian_view.php" target="_blank" style="font-size:15px">Pemakaian Non Medis</a></li><?php } ?>
                      </ul>
                    </li>
                  <?php } ?>

                  <?php if ($auth->IsAllowed("log_non_med_pengiriman", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_pengiriman", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_kinerja_dist", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Distribusi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("log_non_med_pengiriman", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pengiriman_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Pengiriman Barang</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Permintaan Non Medis</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_pengiriman", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_pengiriman_non_medis/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Pengiriman Non Medis</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_pengiriman", PRIV_READ)  && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>rekap_pengiriman_non_medis/report_sbbk.php" target="_blank" style="font-size:15px">Rekap Pengiriman Non Medis</a></li><?php } ?>   
                        <? if ($auth->IsAllowed("log_non_med_lap_pengiriman", PRIV_READ)  && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>rekap_pengiriman_non_medis_new/report_sbbk_new.php" target="_blank" style="font-size:15px">Rekap Distribusi Per Item Non Medis</a></li><?php } ?>

                        <? if ($auth->IsAllowed("log_non_med_lap_kinerja_dist", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_kinerja_distribusi_non_medis/report_sbbk.php" target="_blank" style="font-size:15px">Lap. Kinerja Distribusi</a></li><?php } ?>
                      </ul>
                    </li>
                  <?php } ?>

                  <?php if ($auth->IsAllowed("log_non_med_opname", PRIV_READ) || $auth->IsAllowed("log_non_med_verif_opname", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_opname", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Opname <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("log_non_med_opname", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>opname_non_medis/trans_opname.php" target="_blank" style="font-size:15px">Stok Opname</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_verif_opname", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>opname_non_medis/opname_verif.php" target="_blank" style="font-size:15px">Verifikasi Stok Opname</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_opname", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_opname_non_medis/opname.php" target="_blank" style="font-size:15px">Lap. Stok Opname</a></li><?php } ?>

                      </ul>
                    </li>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("log_non_med_kartu_stok", PRIV_READ) || $auth->IsAllowed("log_non_med_lap_stok", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">

                        <? if ($auth->IsAllowed("log_non_med_kartu_stok", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kartu_stok_non_medis/histori_stok_gflk.php" target="_blank" style="font-size:15px">Logistik Kartu Stok</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_stok", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok_non_medis/laporan_stok_semua_gudang_gflkxx.php" target="_blank" style="font-size:15px">Lap. Semua Stok</a></li><?php } ?>
                        <? if ($auth->IsAllowed("log_non_med_lap_stok", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_semua_stok_non_medis/laporan_stok_saldo_akhir.php" target="_blank" style="font-size:15px">Lap. Selisih Semua Stok</a></li><?php } ?>

                      </ul>
                    </li>
                  <?php } ?>

                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Logistik Non Medik -->

            <!--E Medical Record -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '23' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppEMR = $dtaccess->Fetch($rs);
            if ($authAppEMR) {
              ?>
              <li id="EMR"><a><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp; E MEDICAL RECORD <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("emr_master_header_anamnesa_irj", PRIV_READ) || $auth->IsAllowed("emr_master_anamnesa_irj", PRIV_READ) || $auth->IsAllowed("emr_master_header_anamnesa_igd", PRIV_READ) || $auth->IsAllowed("emr_master_anamnesa_igd", PRIV_READ) || $auth->IsAllowed("emr_master_header_anamnesa_irna", PRIV_READ) || $auth->IsAllowed("emr_master_anamnesa_irna", PRIV_READ) || $auth->IsAllowed("emr_master_header_anamnesa_asmed", PRIV_READ) || $auth->IsAllowed("emr_master_asmed_isi", PRIV_READ) || $auth->IsAllowed("emr_master_triage_pemeriksaan", PRIV_READ) || $auth->IsAllowed("emr_master_triage_kategori", PRIV_READ) || $auth->IsAllowed("emr_master_triage_pilihan", PRIV_READ) || $auth->IsAllowed("emr_master_skrining_gizi", PRIV_READ) || $auth->IsAllowed("emr_master_skrining_gizi", PRIV_READ) || $auth->IsAllowed("emr_master_skrining_jatuh", PRIV_READ) || $auth->IsAllowed("emr_master_satuan_pakai", PRIV_READ) || $auth->IsAllowed("emr_master_aturan_pakai", PRIV_READ) || $auth->IsAllowed("emr_master_satuan_jadi", PRIV_READ) || $auth->IsAllowed("emr_master_satuan_komposisi", PRIV_READ) || $auth->IsAllowed("emr_master_jenis_racikan", PRIV_READ) || $auth->IsAllowed("emr_master_lokasi_radiologi", PRIV_READ) || $auth->IsAllowed("emr_master_konsul", PRIV_READ) || $auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("emr_master_header_anamnesa_irj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IRJ</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_anamnesa_irj", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IRJ</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_igd/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IGD</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_igd/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IGD</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_igd_medis/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IGD Medis</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_igd_medis/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IGD Medis</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_igd_implementasi/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IGD Implementasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_igd_implementasi/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IGD Implementasi</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_igd_modal/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IGD Implementasi Modal</a></li><? } ?>
                        <? if ($auth->IsAllowed("man_medis_master_sebab_sakit", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_igd_modal/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IGD Implementasi Modal</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_header_anamnesa_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_irna/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa Rawat Inap</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_anamnesa_irna", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_irna/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa Rawat Inap</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_header_anamnesa_asmed", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_asuhan_medis/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Asuhan Medis</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_asmed_isi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_asuhan_medis/anamnesa_view.php" target="_blank" style="font-size:15px">Asuhan Medis Isi</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_triage_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_pemeriksaan_view.php" target="_blank" style="font-size:15px">Master Triage Pemeriksaan</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_triage_kategori", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_kategori_view.php" target="_blank" style="font-size:15px">Master Triage Kategori</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_triage_pilihan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_pilihan_view.php" target="_blank" style="font-size:15px">Master Triage Pilihan</a></li><? } ?>
                        <? if ($auth->IsAllowed("emr_master_skrining_gizi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_gizi_view.php" target="_blank" style="font-size:15px">Master Skrining Gizi</a></li><? } ?>
                      <? //if($auth->IsAllowed("emr_master_skrining_gizi",PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_nyeri_view.php" target="_blank" style="font-size:15px">Master Skrining Nyeri</a></li><? //} 
                      ?>
                      <? if ($auth->IsAllowed("emr_master_skrining_gizi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_malnutrisi_view.php" target="_blank" style="font-size:15px">Master Skrining Malnutrisi</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_skrining_jatuh", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_puji_view.php" target="_blank" style="font-size:15px">Master Skrining Puji Rohyati</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_skrining_jatuh", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_gcs_view.php" target="_blank" style="font-size:15px">Master GCS</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_skrining_jatuh", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_jatuh_view.php" target="_blank" style="font-size:15px">Master Scrining Jatuh</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_satuan_pakai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satuan_pakai/satuan_pakai_view.php" target="_blank" style="font-size:15px">Master Satuan Pakai</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_aturan_pakai", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_aturan_pakai/aturan_pakai_view.php" target="_blank" style="font-size:15px">Master Aturan Pakai</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_satuan_jadi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_obat_medik/obat_medik_view.php" target="_blank" style="font-size:15px">Master Satuan Jadi</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_satuan_komposisi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satuan_komposisi/satuan_komposisi_view.php" target="_blank" style="font-size:15px">Master Satuan Komposisi</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_jenis_racikan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_jenis_racikan/jenis_racikan_view.php" target="_blank" style="font-size:15px">Master Jenis Racikan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_lokasi_radiologi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_lokasi_radiologi/lokasi_radiologi_view.php" target="_blank" style="font-size:15px">Master Lokasi Radiologi</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_konsul", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_konsul_yang_diinginkan/konsul_yang_diinginkan_view.php" target="_blank" style="font-size:15px">Master Konsul Yang Diinginkan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_rumah_sakit/rumah_sakit_view.php" target="_blank" style="font-size:15px">Master Rumah Sakit</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_diagnosa_keperawatan/master_diagnosa_keperawatan_view.php" target="_blank" style="font-size:15px">Master Diagnosa Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_diagnosa_keperawatan/master_rencana_tindakan_keperawatan_view.php" target="_blank" style="font-size:15px">Master Rencana Tindakan Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_diagnosa_keperawatan/master_rencana_tindakan_dan_diagnosa_keperawatan_view.php" target="_blank" style="font-size:15px">Master Rencana Tindakan Dan Diagnosa Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_diagnosis_perencanaan/diag_plan_view.php" target="_blank" style="font-size:15px">Master Diagnosis dan Perencanaan Perawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_master_rs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_proses_kebidanan_keperawatan/diag_plan_bidan_view.php" target="_blank" style="font-size:15px">Master Proses Keperawatan / Kebidanan</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>
                <?php if ($auth->IsAllowed("emr_igd_asmed", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; IGD <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>triage/input_rm.php?id_poli=1" target="_blank" style="font-size:15px">Triage</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?id_poli=1&tipe_rawat=G" target="_blank" style="font-size:15px">Verifikasi EMR (Proses Develop)</a></li><? } ?>
                    </ul>
                  </li>

                <?php } ?>
                <?php if ($auth->IsAllowed("emr_igd_asmed", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; IGD Ponek/Nifas <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>triage/input_rm.php" target="_blank" style="font-size:15px">Triage</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan_igd/input_rm.php" target=" _blank" style="font-size:15px">Ass. Keperawatan</a></li><? } ?>
                      <!-- <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>e_medical_record_igd/input_rm.php" target="_blank" style="font-size:15px">Ass. Medis</a></li><? } ?> -->
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd_asmed_mundur/pemeriksaan_igd_view.php" target="_blank" style="font-size:15px">Ass. Medis</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>durance_operasi/durance_operasi_view.php" target="_blank" style="font-size:15px">Durante Operasi</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>odc/odc_view.php" target="_blank" style="font-size:15px">ODC (Kuret)</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php" target="_blank" style="font-size:15px">Verifikasi EMR IGD Ponek (Proses Develop)</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?id_poli=2&tiperawat=I" target="_blank" style="font-size:15px">Verifikasi EMR Ranap Nifas (Proses Develop)</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_ok.php" target="_blank" style="font-size:15px">Verifikasi EMR Durante Operasi (Proses Develop)</a></li><? } ?>

                    </ul>
                  </li>

                <?php } ?>
                <?php if ($auth->IsAllowed("emr_igd_asmed", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; RANAP ANAK <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>triage/input_rm.php?id_poli=1" target="_blank" style="font-size:15px">Triage</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan_igd/input_rm.php?poli_id=b32007585f886be3a97da614258297f3" target=" _blank" style="font-size:15px">Ass. Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna_asmed_anak/pemeriksaan_igd_view.php" target="_blank" style="font-size:15px">Ass. Medis</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?id_poli=3&tiperawat=I" target="_blank" style="font-size:15px">Verifikasi EMR (Proses Develop)</a></li><? } ?>
                    </ul>
                  </li>

                <?php } ?>
                <?php if ($auth->IsAllowed("emr_igd_asmed", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; RANAP NEO <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>triage/input_rm.php?id_poli=1" target="_blank" style="font-size:15px">Triage</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan_igd/input_rm.php?poli_id=d6dd48f320d243b42528bfc52e96ce84" target=" _blank" style="font-size:15px">Ass. Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna_asmed_neo/pemeriksaan_igd_view.php" target="_blank" style="font-size:15px">Ass. Medis</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?id_poli=3&reg_tiperawat=I" target="_blank" style="font-size:15px">Verifikasi EMR (Proses Develop)</a></li><? } ?>
                    </ul>
                  </li>

                <?php } ?>
                <?php if ($auth->IsAllowed("emr_igd_asmed", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; RANAP RGT <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan_igd/input_rm.php?poli_id=eae3aed310ef687fe1bea2c6e995622e" target=" _blank" style="font-size:15px">Ass. Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna_asmed_rgt/pemeriksaan_igd_view.php" target="_blank" style="font-size:15px">Ass. Medis</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?id_poli=3&reg_tiperawat=I" target="_blank" style="font-size:15px">Verifikasi EMR (Proses Develop)</a></li><? } ?>
                    </ul>
                  </li>

                <?php } ?>
                <?php if ($auth->IsAllowed("emr_irj_askep", PRIV_READ) || $auth->IsAllowed("emr_irj_asmed", PRIV_READ) || $auth->IsAllowed("emr_irj_history", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rawat Jalan <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("emr_irj_askep", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan/input_rm.php" target="_blank" style="font-size:15px">Asuhan Keperawatan</a></li><? } ?>
                      <? if ($auth->IsAllowed("emr_irj_askep", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_emr/input_rm_.php?klinik=1&id_poli=4" target="_blank" style="font-size:15px">Verifikasi EMR Rawat Jalan </a></li><? } ?>
                      <!-- <? //if ($auth->IsAllowed("emr_irj_asmed", PRIV_READ)) { 
                            ?><li><a href="<?php //echo $ROOT; 
                                            ?>e_medical_record/input_rm.php" target="_blank" style="font-size:15px">E-Medical Record</a></li><? //} 
                                          ?> -->
                                          <? if ($auth->IsAllowed("emr_irj_history", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>history/input_rm.php" target="_blank" style="font-size:15px">History</a></li><? } ?>
                                        </ul>
                                      </li>


                                    <?php } ?>
                <!--                 <li><a style="font-size:15px"><i class="fa fa-user-md fa-lg"></i>&nbsp;&nbsp; Kebidanan <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                  </ul>
                </li>
                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rawat Inap <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    
                    <li><a href="<?php echo $ROOT; ?>keperawatan_ranap/input_rm.php" target="_blank" style="font-size:15px">Assesmen Keperawatan</a>
                    </li>
                    <li><a href="<?php echo $ROOT; ?>keperawatan_ranap/isian_berkas_rm.php" target="_blank" style="font-size:15px">Isian Berkas Rekam Medik</a>
                    </li>
                    
                    <li><a href="<?php echo $ROOT; ?>e_medical_record_ranap/input_rm.php" target="_blank" style="font-size:15px">Assesmen Medis</a></li>
                  </ul>
                </li>

                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rehab Medik <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                  </ul>
                </li> -->
                <!--                 <?php //if ($auth->IsAllowed("emr_farmasi_history", PRIV_READ)) { 
                                      ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Farmasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("emr_farmasi_history", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>history_farmasi/planning_farmasi.php" target="_blank" style="font-size:15px">History Farmasi</a></li><?php } ?>
                    </ul>
                  </li> -->
                <?php //} 
                ?>
                <?php if ($auth->IsAllowed("emr_casemix_rjtl", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Case Mix <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? //if ($auth->IsAllowed("emr_casemix_rjtl", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>bpjs/tampilan_bpjs.php" target="_blank" style="font-size:15px">RJTL</a></li><?php //} 
                      ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>casemix_inap/tampilan_bpjs_irna.php" target="_blank" style="font-size:15px">Resume Inap</a></li><? } ?>
                      <? if ($auth->IsAllowed("irj_proses_pemeriksaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>casemix_inap/tampilan_bpjs_igd.php" target="_blank" style="font-size:15px">Resume IGD</a></li><? } ?>
                    </ul>
                  </li>
                <?php } ?>

                <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Laporan EMR <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    <li><a href="<?php echo $ROOT; ?>cetak_emr_full/cetak_emr_view.php" target="_blank" style="font-size:15px">Cetak EMR</a></li>
                    <!-- <li><a href="<?php echo $ROOT; ?>lap_kunjungan_nifas/lap_kunjungan_nifas.php" target="_blank" style="font-size:15px">Laporan Register Gynekologi</a></li> -->
                    <li><a href="<?php echo $ROOT; ?>lap_kunjungan_nifas/lap_kunjungan_ponek.php" target="_blank" style="font-size:15px">Laporan Register Ponek</a></li>
                    <li><a href="<?php echo $ROOT; ?>lap_kunjungan_emr/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li>
                    <!-- <li><a href="<?php echo $ROOT; ?>lap_kunjungan_nifas/lap_kunjungan_preop.php" target="_blank" style="font-size:15px">Laporan Register Obsetri</a></li> -->
                    <!-- <li><a href="<?php echo $ROOT; ?>lap_kunjungan_nifas/lap_kunjungan_operasi.php" target="_blank" style="font-size:15px">Laporan Register Operasi</a></li> -->
                  </ul>
                </li>



                <!--                 <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-folder-open"></i>&nbsp;&nbsp; Rawat Intensif <span class="fa fa-chevron-down"></span></a>
                -->
                <!--         <ul class="nav child_menu">
                    <li><a href="<?php echo $ROOT; ?>history_farmasi/planning_farmasi.php" target="_blank" style="font-size:15px">History Farmasi</a></li>
                  </ul> -->
                </li>
              </ul>
            </li>
          <?php } ?>
          <!-- END E Medical Record -->

          <!--AR/AP -->
          <?
          $sql = "select usr_app_id from global.global_auth_user_app where id_app = '25' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
          $rs = $dtaccess->Execute($sql);
          $authAppARAP = $dtaccess->Fetch($rs);
          if ($authAppARAP) {
            ?>
            <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; AR/AP <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">

                <?php if ($auth->IsAllowed("ar_pelunasan_piutang_perorangan", PRIV_READ) || $auth->IsAllowed("ar_pelunasan_piutang_asuransi", PRIV_READ) || $auth->IsAllowed("ar_pelunasan_piutang_bpjs", PRIV_READ) || $auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { ?>
                  <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; AR <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <? if ($auth->IsAllowed("ar_pelunasan_piutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_perorangan/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Piutang Umum</a></li><? } ?>
                      <? if ($auth->IsAllowed("ar_pelunasan_piutang_asuransi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_asuransi/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Piutang Asuransi</a></li><? } ?>
                      <? if ($auth->IsAllowed("ar_pelunasan_piutang_bpjs", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_bpjs/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Piutang BPJS</a></li><? } ?>
                      <? if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_karyawan/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Piutang Karyawan</a></li><? } ?>
                      <? if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_kurang_bayar/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Piutang Kurang Bayar</a></li><? } ?>
                  <? //if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_pelunasan_piutang/cetak_ulang_pelunasan_piutang.php" target="_blank" style="font-size:15px">Cetak Ulang</a></li><? //} 
                  ?>
                  <? //if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>batal_bayar_pelunasan_piutang/batal_bayar_pelunasan_piutang.php" target="_blank" style="font-size:15px">Batal Bayar</a></li><? //} 
                  ?>
                </ul>
              </li>
            <?php } ?>

            <?php if ($auth->IsAllowed("ap_pelunasan_piutang_supplier", PRIV_READ)) { ?>
              <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; AP <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <? if ($auth->IsAllowed("ap_pelunasan_piutang_supplier", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_supplier/pelunasan_piutang.php" target="_blank" style="font-size:15px">Pelunasan Hutang Supplier</a></li><? } ?>
                  <? if ($auth->IsAllowed("ap_pelunasan_piutang_supplier", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>selisih_hutang_pelunasan/trans_beli_po_view.php" target="_blank" style="font-size:15px">Input Selisih Pelunasan</a></li><? } ?>
                  <? if ($auth->IsAllowed("ap_pelunasan_piutang_supplier", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pelunasan_piutang_supplier/pelunasan_piutang_custom.php" target="_blank" style="font-size:15px">Pelunasan Hutang Supplier (Selisih)</a></li><? } ?>
                  <? //if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>batal_bayar_pelunasan_supplier/batal_bayar_pelunasan_supplier.php" target="_blank" style="font-size:15px">Batal Bayar</a></li><? //} 
                  ?>
                </ul>
              </li>
            <?php } ?>
            <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list"></i>&nbsp;&nbsp; Informasi Hutang <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <li><a href="<?php echo $ROOT; ?>lap_pelunasan_hutang_supplier/lap_pelunasan_hutang_supplier.php" target="_blank" style="font-size:15px">Laporan Pelunasan Hutang Supplier</a></li>
                <li><a href="<?php echo $ROOT; ?>../production/lap_jatuh_tempo/jatuh_tempo_view.php" target="_blank" style="font-size:15px">Lap. Jatuh Tempo</a></li>
                <li><a href="<?php echo $ROOT; ?>../production/lap_jatuh_tempo_bhp/jatuh_tempo_view.php" target="_blank" style="font-size:15px">Lap. Jatuh Tempo BHP Instalasi Farmasi</a></li>
                <li><a href="<?php echo $ROOT; ?>../production/lap_jatuh_tempo_bhp_lab/jatuh_tempo_view.php" target="_blank" style="font-size:15px">Lap. Jatuh Tempo BHP Laboratorium</a></li>
                <li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_supplier/kartu_hutang_supplier_view.php" target="_blank" style="font-size:15px">Lap. Kartu Hutang PerSupplier</a></li>
                <li><a href="<?php echo $ROOT; ?>../production/rekap_hutang_supplier/rekap_hutang_supplier_view.php" target="_blank" style="font-size:15px">Rekap Hutang Supplier</a></li>
              </ul>
            </li>
            <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list"></i>&nbsp;&nbsp; Informasi Piutang <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_perorangan", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang_perorangan/pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Piutang Umum</a></li>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_perorangan", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang_kurang_bayar/pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Piutang Kurang Bayar</a></li>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_asuransi", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang_asuransi/pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Piutang Asuransi</a></li><? //} 
                ?>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_bpjs", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang_bpjs/pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Piutang BPJS</a></li><? //} 
                ?>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang_karyawan/pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Piutang Karyawan</a></li><? //} 
                ?>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_karyawan", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>lap_pelunasan_piutang/laporan_pelunasan_piutang.php" target="_blank" style="font-size:15px">Laporan Pelunasan Piutang</a></li><? //} 
                ?>
                <?php if ($auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_asuransi/kartu_hutang_asuransi_view.php" target="_blank" style="font-size:15px">Lap. Kartu Piutang Asuransi</a></li><?php } ?>
                <?php if ($auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_bpjs/kartu_hutang_bpjs_view.php" target="_blank" style="font-size:15px">Lap. Kartu Piutang BPJS</a></li><?php } ?>

                <?php if ($auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_perorangan/kartu_hutang_perorangan_view.php" target="_blank" style="font-size:15px">Lap. Kartu Piutang Umum</a></li><?php } ?>
                <?php if ($auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_kurang_bayar/kartu_hutang_kurang_bayar_view.php" target="_blank" style="font-size:15px">Lap. Kartu Piutang Kurang Bayar</a></li><?php } ?>
                <?php if ($auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kartu_hutang_karyawan/kartu_hutang_karyawan_view.php" target="_blank" style="font-size:15px">Lap. Kartu Piutang Karyawan</a></li><?php } ?>
                <? //if ($auth->IsAllowed("ar_pelunasan_piutang_asuransi", PRIV_READ)) { 
                ?><li><a href="<?php echo $ROOT; ?>rekap_piutang/rekap_piutang_view.php" target="_blank" style="font-size:15px">Rekap Piutang Asuransi</a></li><? //} 
                ?>
              </ul>
            </li>

            <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Informasi Non Medik <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <? if ($auth->IsAllowed("log_non_med_lap_penerimaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jatuh_tempo_non_medis/jatuh_tempo_view.php" target="_blank" style="font-size:15px">Laporan Periode Pembelian / Jatuh Tempo</a></li><?php } ?>

              </ul>
            </li>
          </ul>
        </li>
      <?php } ?>
      <!-- END AR / AP -->


      <!-- Menu Akuntansi -->
      <?
      $sql = "select usr_app_id from global.global_auth_user_app where id_app = '24' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
      $rs = $dtaccess->Execute($sql);
      $authAppAcc = $dtaccess->Fetch($rs);
      if ($authAppAcc) {
        ?>
        <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; AKUNTANSI <span class="fa fa-chevron-down"></span></a>
          <ul class="nav child_menu">
            <?php if ($auth->IsAllowed("akuntansi_master_konfig", PRIV_READ) || $auth->IsAllowed("akuntansi_master_perkiraan_level1", PRIV_READ) || $auth->IsAllowed("akuntansi_master_perkiraan", PRIV_READ) || $auth->IsAllowed("akuntansi_master_periode", PRIV_READ) || $auth->IsAllowed("akuntansi_master_setup_biaya_prk", PRIV_READ)) { ?>
              <li><a target="_blank" style="font-size:15px"><i class="fa fa-wrench"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <? if ($auth->IsAllowed("akuntansi_master_konfig", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/konfigurasi_akuntansi/konfigurasi.php" target="_blank" style="font-size:15px">Konfigurasi</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_master_perkiraan_level1", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/account_level_satu/account_level_satu_view_gl.php" target="_blank" style="font-size:15px">Perkiraan Lv 1</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_master_perkiraan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/account/account_view_gl.php" target="_blank" style="font-size:15px">Perkiraan</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/periode/period.php" target="_blank" style="font-size:15px">Periode</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/periode_saldo_awal/index.php" target="_blank" style="font-size:15px">Periode Saldo Awal</a></li><?php } ?>
                </ul>
              </li>
            <?php } ?>
            <?php if ($auth->IsAllowed("akuntansi_proses_saldo_awal", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_kas_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_kas_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_bank_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_transaksi_memorial", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_daftar_kas_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_daftar_kas_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_daftar_bank_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_daftar_bank_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_daftar_trans_memorial", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sementara", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sementara_unbalance", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_buku_besar_sementara", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_neraca_percobaan", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_laba_rugi_sementara", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_posting_gl", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_unposting_gl", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_rekening", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_pen_kas", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_bank_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_persediaan", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_umum", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_jurnal_sem_pendapatan", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_calk", PRIV_READ) || $auth->IsAllowed("akuntansi_proses_saldo_awal_piutang_perorangan", PRIV_READ)) { ?>
              <li><a target="_blank" style="font-size:15px"><i class="fa fa-gear"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <? //if($auth->IsAllowed("akuntansi_proses_saldo_awal",PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>../production/saldo_awal/saldo_awal_edit.php" target="_blank" style="font-size:15px">Saldo Awal Neraca</a></li><?php //} 
                  ?>
                  <? //if($auth->IsAllowed("akuntansi_proses_saldo_awal",PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>../production/hutang_supplier/hutang_supplier_view.php" target="_blank" style="font-size:15px">Saldo Awal Hutang Supplier</a></li><?php //} 
                  ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_saldo_awal_piutang_perorangan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/hutang_perorangan/hutang_perorangan_view.php" target="_blank" style="font-size:15px">Saldo Awal Piutang</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_kas_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kas_masuk_custom/kas_masuk.php" target="_blank" style="font-size:15px">Jurnal Kas Masuk</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_kas_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/kas_keluar_custom/kas_keluar.php" target="_blank" style="font-size:15px">Jurnal Kas Keluar</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_bank_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/bank_masuk_custom/bank_masuk.php" target="_blank" style="font-size:15px">Jurnal Bank Masuk</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/bank_keluar_custom/bank_keluar.php" target="_blank" style="font-size:15px">Jurnal Bank Keluar</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_deposit/journal_sementara_deposit.php" target="_blank" style="font-size:15px">Jurnal Uang Muka Pasien</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_transaksi_memorial", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_memorial/jurnal_memorial.php" target="_blank" style="font-size:15px">Jurnal Memorial</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sementara_unbalance", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_unbalance/cashier_report_journal_unbalance.php" target="_blank" style="font-size:15px">Jurnal Sementara Unbalance</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sementara_unbalance", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_belum_posting/jurnal_sementara_belum_posting.php" target="_blank" style="font-size:15px">Jurnal Belum Posting</a></li><?php } ?>

<? //if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ)) { 
                  ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_hutang/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Jurnal Pengakuan Hutang Supplier</a></li><?php //} 
                  ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pelunasan_asuransi/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Jurnal Pelunasan Piutang Asuransi</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pelunasan_umum/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Jurnal Pelunasan Piutang Umum</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pelunasan_kurang_bayar/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Jurnal Pelunasan Piutang Kurang Bayar</a></li><?php } ?>
                  <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_hutang", PRIV_READ)) {
                    ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pelunasan_hutang/journal_sementara_pelunasan_hutang.php" target="_blank" style="font-size:15px">Jurnal Pelunasan Hutang</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_mutasi/journal_sementara_mutasi.php" target="_blank" style="font-size:15px">Jurnal Mutasi Obat</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_persediaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_persediaan/journal_sementara_persediaan.php" target="_blank" style="font-size:15px">Jurnal Persediaan Penjualan</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_penerimaan_non_medis/journal_sementara_penerimaan.php" target="_blank" style="font-size:15px">Jurnal Penerimaan Barang Non Medis</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_mutasi_non_medis/journal_sementara_mutasi.php" target="_blank" style="font-size:15px">Jurnal Mutasi Non Medis</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_jurnal_sem_pendapatan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pendapatan/jurnal_sementara_pendapatan.php" target="_blank" style="font-size:15px">Jurnal Penerimaan IRJ IGD IRNA</a></li><?php } ?>
                    <!-- <? if ($auth->IsAllowed("akuntansi_proses_calk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/neraca_sementara/report_neraca_sementara.php" target="_blank" style="font-size:15px">C a L K</a></li><?php } ?> -->
                    <? if ($auth->IsAllowed("akuntansi_proses_daftar_kas_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/daftar_kas_masuk/daftar_kas_masuk.php" target="_blank" style="font-size:15px">Daftar Kas Masuk</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_daftar_kas_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/daftar_kas_keluar/daftar_kas_keluar.php" target="_blank" style="font-size:15px">Daftar Kas Keluar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_daftar_bank_masuk", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/daftar_bank_masuk/daftar_bank_masuk.php" target="_blank" style="font-size:15px">Daftar Bank Masuk</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_daftar_bank_keluar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/daftar_bank_keluar/daftar_bank_keluar.php" target="_blank" style="font-size:15px">Daftar Bank Keluar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_daftar_trans_memorial", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/daftar_transaksi/cashier_transaction_list.php" target="_blank" style="font-size:15px">Daftar Jurnal Memorial</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_proses_posting_gl", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/posting_gl/posting_gl.php" target="_blank" style="font-size:15px">Posting GL</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_proses_unposting_gl", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/unposting_jurnal_sementara/lap_posting_new.php" target="_blank" style="font-size:15px">Unposting GL</a></li><?php } ?>

                  </ul>
                </li>
              <?php } ?>
              <?php if ($auth->IsAllowed("akuntansi_informasi_lap_kas_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_kas_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_bank_masuk", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_bank_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_memorial", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_buku_besar", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_neraca", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_laba_rugi", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_neraca_komperatif", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_laba_rugi_komperatif", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_perubahan_ekuitas", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_arus_kas", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_rekening", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_bank_keluar", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_hutang", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_persediaan", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_jurnal_umum", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_lap_pendapatan_harian", PRIV_READ) || $auth->IsAllowed("akuntansi_informasi_kartu_hutang_perorangan", PRIV_READ)) { ?>
                <li><a target="_blank" style="font-size:15px"><i class="fa fa-table"></i>&nbsp;&nbsp; Laporan <span class="fa fa-chevron-down"></span></a>
                  <ul class="nav child_menu">
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/buku_harian_kas/buku_harian_kas_view.php" target="_blank" style="font-size:15px">Buku Harian Kas</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/buku_harian_bank/buku_harian_bank_view.php" target="_blank" style="font-size:15px">Buku Harian Bank</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_kas_masuk/journal_kas_masuk.php" target="_blank" style="font-size:15px">Laporan Jurnal Kas Masuk</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_kas_keluar/journal_kas_keluar.php" target="_blank" style="font-size:15px">Laporan Jurnal Kas Keluar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_penerimaan_bank/journal_penerimaan_bank.php" target="_blank" style="font-size:15px">Laporan Jurnal Bank Masuk</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_pen_kas", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_penerimaan_bank_keluar/journal_penerimaan_bank.php" target="_blank" style="font-size:15px">Laporan Jurnal Bank Keluar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_deposit/journal_deposit.php" target="_blank" style="font-size:15px">Laporan Jurnal Uang Muka</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_memorial/journal_memorial.php" target="_blank" style="font-size:15px">Laporan Jurnal Memorial</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_deposit/lap_deposit_view.php" target="_blank" style="font-size:15px">Laporan Deposit</a></li><? } ?>

                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_unbalance/cashier_report_journal_unbalance.php" target="_blank" style="font-size:15px">Laporan Jurnal Unbalance</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_hutang/journal_hutang.php" target="_blank" style="font-size:15px">Laporan Jurnal Pengakuan Hutang</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_pelunasan_hutang/journal_pelunasan_hutang.php" target="_blank" style="font-size:15px">Laporan Jurnal Pelunasan Hutang</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_pelunasan_asuransi/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Laporan Jurnal Pelunasan Piutang Asuransi</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_pelunasan_umum/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Laporan Jurnal Pelunasan Piutang Umum</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_pelunasan_kurang_bayar/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Laporan Jurnal Pelunasan Piutang Kurang Bayar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_persediaan", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_persediaan/journal_persediaan.php" target="_blank" style="font-size:15px">Laporan Jurnal Persediaan Penjualan</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_mutasi/journal_mutasi.php" target="_blank" style="font-size:15px">Laporan Jurnal Mutasi</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_penerimaan_non_medis/journal_penerimaan.php" target="_blank" style="font-size:15px">Laporan Jurnal Penerimaan Barang Non Medis</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_mutasi_non_medis/journal_mutasi.php" target="_blank" style="font-size:15px">Laporan Jurnal Mutasi Non Medis</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_jurnal_umum", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/jurnal_pendapatan/journal_pendapatan.php" target="_blank" style="font-size:15px">Laporan Jurnal Penerimaan</a></li><?php } ?>


                    <? if ($auth->IsAllowed("akuntansi_master_periode", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/rekap_jurnal_penerimaan/rekap_jurnal_penerimaan_view.php" target="_blank" style="font-size:15px">Rekap Jurnal Penerimaan</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_buku_besar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/buku_besar_custom/report_buku_besar.php" target="_blank" style="font-size:15px">Buku Besar</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_buku_besar", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/resume_buku_besar/report_buku_besar_sementara.php" target="_blank" style="font-size:15px">Rekap Perkiraan Buku Besar</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_lap_laba_rugi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/laba_rugi_custom/report_laba_rugi.php" target="_blank" style="font-size:15px">Laporan Laba Rugi</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_lap_laba_rugi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/penjelasan_biaya/report_laba_rugi.php" target="_blank" style="font-size:15px">Penjelasan Biaya Pada Laba Rugi</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_lap_laba_rugi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/penjelasan_pendapatan/report_laba_rugi.php" target="_blank" style="font-size:15px">Penjelasan Pendapatan Pada Laba Rugi</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_neraca", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/neraca_custom/report_neraca.php" target="_blank" style="font-size:15px">Neraca</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_neraca", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/penjelasan_aktiva/report_neraca_sementara.php" target="_blank" style="font-size:15px">Penjelasan Aktiva</a></li><?php } ?>
                    <? if ($auth->IsAllowed("akuntansi_informasi_neraca", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/penjelasan_passiva/report_neraca_sementara.php" target="_blank" style="font-size:15px">Penjelasan Passiva</a></li><?php } ?>

                    <? if ($auth->IsAllowed("akuntansi_informasi_neraca", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/neraca_lajur/report_neraca.php" target="_blank" style="font-size:15px">Neraca Lajur</a></li><?php } ?>

                  </ul>
                </li>
              <?php } ?>
              <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Stok Non Medik <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">

                      <? //if ($auth->IsAllowed("irj_informasi_rekap_bulanan", PRIV_READ)) { 
                      ?><li><a href="<?php echo $ROOT; ?>permintaan_barang_non_medis/transfer_stok_view.php" target="_blank" style="font-size:15px">Permintaan Barang</a></li><? // } 
                      ?>
                      <? //if ($auth->IsAllowed("log_non_med_lap_permintaan", PRIV_READ) && $userId!="82ff0c061bc44510fc212c49b5b83a01") { ?><li><a href="<?php echo $ROOT; ?>lap_permintaan_non_medis/report_permintaan_pengiriman.php" target="_blank" style="font-size:15px">Lap. Permintaan dan Pengiriman Non Medis</a></li><?php //} ?>


                      
                    </ul>
                  </li>
                </ul>
              </li>
            <?php } ?>
            <!-- END Menu Akuntansi -->

            <!-- Menu Keuangan -->
            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '26' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppKeuangan = $dtaccess->Fetch($rs);
            if ($authAppKeuangan) {
              ?>
              <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; KEUANGAN <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("keuangan_proses_lap_belum_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_belum_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_lap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_bendahara", PRIV_READ)) { ?>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("keuangan_informasi_lap_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_detail_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_penerimaan_all", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_detail_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_pendapatan_instalasi", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_rincian_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_rekap_pendapatan_dan_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_deposit_masuk", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_deposit_keluar", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_remunerasi", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif/report_pasien.php" target="_blank" style="font-size:15px">Laporan Perbandingan Tarif Pasien</a></li><?php } ?>
                        <? if ($auth->IsAllowed("keuangan_informasi_lap_remunerasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_remunerasi/remunerasi_view.php" target="_blank" style="font-size:15px">Laporan Remunerasi Dokter</a></li><?php } ?>
                        <? if ($auth->IsAllowed("keuangan_informasi_lap_remunerasi", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_remunerasi/remunerasi_view_perawat.php" target="_blank" style="font-size:15px">Laporan Remunerasi Perawat</a></li><?php } ?>
                        <li><a href="<?php echo $ROOT; ?>../production/rekap_tuslah/report_penjualan.php" target="_blank" style="font-size:15px">Rekap Tuslah</a></li>
                        <li><a href="<?php echo $ROOT; ?>../production/rekap_tuslah/lap_tuslag.php" target="_blank" style="font-size:15px">Rincian Tuslah</a></li>
                        <? //if($auth->IsAllowed("akuntansi_informasi_lap_memorial",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>../production/lap_penjualan/report_penjualan.php" target="_blank" style="font-size:15px">Laporan Penjualan</a></li><?php ?>
                        <li><a href="<?php echo $ROOT; ?>dashboard_grafik/all_graph.php" target="_blank" style="font-size:15px">Dashboard Grafik</a></li>
                      </ul>
                    </li>
                  <?php } ?>
                </ul>
              </li>

            <?php } ?>



            <?
            $sql = "select usr_app_id from global.global_auth_user_app where id_app = '26' and id_usr=" . QuoteValue(DPE_CHAR, $userId);
            $rs = $dtaccess->Execute($sql);
            $authAppKeuangan = $dtaccess->Fetch($rs);
            if ($authAppKeuangan) {
              ?>
              <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; Asuransi <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                  <?php if ($auth->IsAllowed("keuangan_proses_lap_belum_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_belum_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_lap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_tutup_kasir", PRIV_READ) || $auth->IsAllowed("keuangan_proses_rekap_bendahara", PRIV_READ)) { ?>
                  <?php } ?>
                  <?php if ($auth->IsAllowed("keuangan_informasi_lap_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_detail_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_penerimaan_all", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_detail_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_pendapatan_instalasi", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_rincian_pendapatan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_rekap_pendapatan_dan_penerimaan", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_deposit_masuk", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_deposit_keluar", PRIV_READ) || $auth->IsAllowed("keuangan_informasi_lap_remunerasi", PRIV_READ)) { ?>
                    <li><a target="_blank" style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <? if ($auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif_asuransi/report_pasien.php" target="_blank" style="font-size:15px">Laporan Perbandingan Tarif Pasien Asuransi</a></li><?php } ?>
                        <? if ($auth->IsAllowed("keuangan_informasi_lap_perbandingan_tarif", PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_piutang/lap_piutang_view_asuransi.php" target="_blank" style="font-size:15px">Laporan Piutang Asuransi</a></li><?php } ?>



                      </ul>
                    </li>
                  <?php } ?>
                </ul>
              </li>

            <?php } ?>

          </ul>
        </div>
      </div>
      <!-- /sidebar menu -->

    </div>
  </div>