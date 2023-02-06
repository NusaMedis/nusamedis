		<?php require_once("../penghubung.inc.php"); ?>
		<div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a class="site_title"> <span style="font-size:16px">RSPI Prof. Dr. Sulianti Saroso</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="<?php $ROOT ?>gambar/logo-rspiss.png" alt="..." class="img-circle profile_img">
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>Manajemen</h3>
                <ul class="nav side-menu">
                  <li><a style="font-size:15px"><i class="fa fa-home"></i>&nbsp;&nbsp; Pengaturan <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>konfigurasi/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi RS</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_biaya/konfigurasi_edit.php" target="_blank" style="font-size:15px">Konfigurasi Tarif</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_tampilan/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Tampilan</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_apotik/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Apotik</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_lab/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Lab</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_radiologi/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Radiologi</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_bor/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Bor</a></li>
                      <li><a href="<?php echo $ROOT; ?>konfigurasi_registrasi/konfigurasi_edit.php"target="_blank" style="font-size:15px">Konfigurasi Registrasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php"target="_blank" style="font-size:15px">Ganti Password</a></li>
                    
                    </ul>
                  </li>
                  <li><a style="font-size:15px"><i class="fa fa-users fa-lg"></i>&nbsp;&nbsp; User <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>edit_pegawai/data_pegawai_view.php"target="_blank" style="font-size:15px">Manajemen Pegawai</a></li>
                      <li><a href="<?php echo $ROOT; ?>master_satker/dep_view.php"target="_blank" style="font-size:15px">Master Satuan Kerja</a></li>
                      <li><a href="<?php echo $ROOT; ?>jabatan/role_view.php"target="_blank" style="font-size:15px">Jabatan</a></li>
                      <li><a href="<?php echo $ROOT; ?>user_login/hakakses_view.php"target="_blank" style="font-size:15px">User Login</a></li>
                    </ul>
                  </li> 
                   <li><a style="font-size:15px"><i class="fa fa-money"></i>&nbsp;&nbsp; Tarif <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>jenis_biaya/jenis_biaya_view.php"target="_blank" style="font-size:15px">Master Split Tindakan</a></li>
                      <li><a href="<?php echo $ROOT; ?>kat_tindakan_header_instalasi/kat_tindakan_header_instalasi_view.php"target="_blank" style="font-size:15px">Kategori Tindakan header Intalasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>kat_tindakan_header/kat_tindakan_header_view.php"target="_blank" style="font-size:15px">Kategori Tindakan header </a></li>
                      <li><a href="<?php echo $ROOT; ?>kat_tindakan/kat_tindakan_view.php"target="_blank" style="font-size:15px">Kategori Tindakan</a></li>
                      <li><a href="<?php echo $ROOT; ?>tarif_tindakan_irj/tindakan_view.php "target="_blank" style="font-size:15px">Tarif Tindakan Rawat Jalan</a></li>
                      <li><a href="<?php echo $ROOT; ?>tarif_tindakan_igd/tindakan_view.php "target="_blank" style="font-size:15px">Tarif Tindakan IGD</a></li>
                      <li><a href="<?php echo $ROOT; ?>tarif_tindakan_irna/tindakan_view.php " target="_blank" style="font-size:15px">Tarif Tindakan Rawat Inap</a></li>
                      <li><a href="<?php echo $ROOT; ?>tindakan_all/tindakan_view.php " target="_blank" style="font-size:15px">Tarif Tindakan Semua Tipe Rawat</a></li>
                      <li><a href="<?php echo $ROOT; ?>tarif_header_poli/biaya_poli_view.php " target="_blank" style="font-size:15px">Tarif Header klinik</a></li>
                      <li><a href="<?php echo $ROOT; ?>tarif_header_split/header_split_view.php" target="_blank" style="font-size:15px">Tarif Header Split</a></li>
                      <li><a href="<?php echo $ROOT; ?>biaya_registrasi/biaya_view.php " target="_blank" style="font-size:15px">Biaya Registrasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>biaya_pemeriksaan/biaya_view.php " target="_blank" style="font-size:15px">Biaya Pemerikasaan</a></li>
                      <li><a href="<?php echo $ROOT; ?>biaya_akomodasi/biaya_akomodasi_view.php" target="_blank" style="font-size:15px">Biaya Akomondasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>rincian_tindakan_irna/kategori_kassa_view.php" target="_blank" style="font-size:15px">Rincian Tindakan Inap</a></li>
                      <li><a href="<?php echo $ROOT; ?>tahun_tarif/tahun_tarif_view.php" target="_blank" style="font-size:15px">Master Tahun Tarif</a></li>
                      <li><a href="<?php echo $ROOT; ?>jenisbayar/jenis_bayar_view.php" target="_blank" style="font-size:15px">Setup Jenis Bayar</a></li>
                      <li><a href="<?php echo $ROOT; ?>fasilitas/fasilitas_view.php" target="_blank" style="font-size:15px">Plafon Fasilitas</a></li>
                      <li><a href="<?php echo $ROOT; ?>jasa_raharja/jasa_raharja_view.php" target="_blank" style="font-size:15px">Plafon Jasa Raharja</a></li>
                      <li><a href="<?php echo $ROOT; ?>perusahaan/perusahaan_view.php" target="_blank" style="font-size:15px">Master Perusahaan</a></li>
                    </ul>
                  </li>
                   <li><a style="font-size:15px"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Medis <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>instalasi/instalasi_view.php" target="_blank" style="font-size:15px">Setup Instalasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>sub_instalasi/sub_instalasi_view.php" target="_blank" style="font-size:15px">Setup Sub Instalasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>setup_poli/jenis_poli_view.php" target="_blank" style="font-size:15px">Setup Klinik</a></li>
                      <li><a href="<?php echo $ROOT; ?>tindakan/tindakan_view.php" target="_blank" style="font-size:15px">Kelas Kamar</a></li>
                      <li><a href="<?php echo $ROOT; ?>kamar/kamar_view.php" target="_blank" style="font-size:15px">Kamar</a></li>
                      <li><a href="<?php echo $ROOT; ?>bor_kamar/bor_kamar_view" target="_blank" style="font-size:15px">Master Bor Kamar</a></li>
                      <li><a href="<?php echo $ROOT; ?>kat_icd/kat_icd_view.php" target="_blank" style="font-size:15px">Kategori ICD</a></li>
                      <li><a href="<?php echo $ROOT; ?>det_kat_icd/det_icd_view.php" target="_blank" style="font-size:15px">Det kat ICD</a></li>
                      <li><a href="<?php echo $ROOT; ?>propinsi/propinsi_view.php" target="_blank" style="font-size:15px">Propinsi</a></li>
                      <li><a href="<?php echo $ROOT; ?>pekerjaan/pekerjaan_view.php" target="_blank" style="font-size:15px">Pekerjaan</a></li>
                      <li><a href="<?php echo $ROOT; ?>sekolah/sekolah_view.php" target="_blank" style="font-size:15px">pendidikan</a></li>
                      <li><a href="<?php echo $ROOT; ?>jenis_pegawai/jenis_pegawai_view.php" target="_blank" style="font-size:15px">Jenis Pegawai</a></li>
                      <li><a href="<?php echo $ROOT; ?>setup_tenaga_medis/medis_view.php" target="_blank" style="font-size:15px">Setup Tugas Medis</a></li>
                      <li><a href="<?php echo $ROOT; ?>rujukan/rujukan_view.php" target="_blank" style="font-size:15px">Rujukan</a></li>
                      <li><a href="<?php echo $ROOT; ?>icd9/icd9_view.php" target="_blank" style="font-size:15px">ICD9</a></li>
                      <li><a href="<?php echo $ROOT; ?>icd10/icd_view.php" target="_blank" style="font-size:15px">ICD10</a></li>
                      <li><a href="<?php echo $ROOT; ?>external_cause/external_cause_view.php" target="_blank" style="font-size:15px">External Cause</a></li>
                      <li><a href="<?php echo $ROOT; ?>morfologi/morfologi_view.php" target="_blank" style="font-size:15px">Morfologi</a></li>
                      <li><a href="<?php echo $ROOT; ?>status_perkawinan/status_perkawinan_view.php" target="_blank" style="font-size:15px">Status Perkawinan</a></li>
                      <li><a href="<?php echo $ROOT; ?>paket_detail/detail_paket_view.php" target="_blank" style="font-size:15px">Detail Paket</a></li>                        
                      <li><a href="<?php echo $ROOT; ?>master_kamar_operasi/master_kamar_operasi_view.php" target="_blank" style="font-size:15px">Master Kamar Operasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>sebab_sakit/sebab_sakit_view.php" target="_blank" style="font-size:15px">Sebab Sakit</a></li>
                    </ul>
                  </li> 
                  <li><a style="font-size:15px"><i class="fa fa-edit"></i>&nbsp&nbsp Import <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>import_pasien/import_pasien_excel.php" target="_blank" style="font-size:15px">Import Pasien</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_tarif_irj/import_tarif_irj_2014.php" target="_blank" style="font-size:15px">Import Tarif RJ</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_tarif_igd/import_tarif_igd2014.php" target="_blank" style="font-size:15px">Import Tarif IGD</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_tarif_irna/import_tarif_split_inap2014.php" target="_blank" style="font-size:15px">Import Tarif RI</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_kat_tindakan_header_instalasi/import_instalasi.php" target="_blank" style="font-size:15px">Imp.Kategori Tindakan Header Instalasi</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_kat_tindakan_header/import_header_kat2014.php" target="_blank" style="font-size:15px">Imp.Kategori Tindakan Header</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_kat_tindakan/import_kat_new2014.php" target="_blank" style="font-size:15px">Imp.Kategori Tindakan</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_perawatan/import_perawatan.php" target="_blank" style="font-size:15px">Import Perawatan</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_icd/import_icd.php" target="_blank" style="font-size:15px">Import ICD</a></li>
                      <li><a href="<?php echo $ROOT; ?>import/import_icd9/import_icd9.php" target="_blank" style="font-size:15px">Import ICD9</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_pegawai/import_pegawai.php" target="_blank" style="font-size:15px">Import Pegawai</a></li>
                      <li><a href="<?php echo $ROOT; ?>import_barang/import_barang.php" target="_blank" style="font-size:15px">Import Barang</a></li>
                    </ul>
                  </li>                   
                    <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Data <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>reset_pasien/data_pasien_edit.php " target="_blank" style="font-size:15px">Reset Pasien</a></li>
                      <li><a href="<?php echo $ROOT; ?>reset_user/data_user_edit.php" target="_blank" style="font-size:15px">Reset Data User</a></li>                      
                      <li><a href="<?php echo $ROOT; ?>reset_keuangan/data_keuangan_edit.php" target="_blank" style="font-size:15px">Reset Data Keuangan</a></li>
                      <li><a href="<?php echo $ROOT; ?>reset_master_keuangan/data_master_keuangan_edit.php" target="_blank" style="font-size:15px">Reset Master Keuangan</a></li>
                      <li><a href="<?php echo $ROOT; ?>reset_keperawatan/data_keperawatan_edit.php" target="_blank" style="font-size:15px">Reset Keperawatan</a></li>
                      <li><a href="<?php echo $ROOT; ?>reset_obat/data_obat_edit.php" target="_blank" style="font-size:15px">Reset Obat</a></li>
                    </ul>
                  </li>
                  <li><a style="font-size:15px"><i class="glyphicon glyphicon-folder-open"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Database <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>backup_restore/backup/backup.php" target="_blank" style="font-size:15px">Back-up Database</a></li>
                      <li><a href="<?php echo $ROOT; ?>backup_restore/restore/restore_view.php" target="_blank" style="font-size:15px">Restore Database</a></li>
                    </ul>
                  </li>
                   <li><a style="font-size:15px"><i class="glyphicon glyphicon-fullscreen"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Update <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>update_core/update_core.php" target="_blank" style="font-size:15px">Update</a></li>
                      <li><a href="<?php echo $ROOT; ?>update_plugin/update_plugin.php" target="_blank" style="font-size:15px">Plug In</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->

     
          </div>
        </div>