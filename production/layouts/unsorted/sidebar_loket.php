		<?php require_once("../penghubung.inc.php"); 
		    require_once($LIB."login.php");    
            require_once($LIB."datamodel.php");
            require_once($LIB."conf/database.php");
            require_once($LIB."conf/db_depan.php");
			$auth = new CAuth();    
            $enc = new textEncrypt();     
            
            $host="localhost";
            $user=$enc->Decode(DB_USER);
            $password=$enc->Decode(DB_PASSWORD);
            $port="5432";
            $dbname = DB_DEPAN;
            
            //ambil title Software dari Konfigurasi
            $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
               
            $sql = pg_query($link, "select dep_title,dep_logo from global.global_departemen");
            $dataTitle = pg_fetch_assoc($sql);
            
            $lokasi = $ROOT."gambar/img_cfg";
            $fotoName = $lokasi."/".$dataTitle["dep_logo"];
            
		?>
		<div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a class="site_title"> <span style="font-size:16px"><?php echo $dataTitle["dep_title"];?></span></a>
            </div>

            <div class="clearfix"></div>
            
            <!-- menu profile quick info -->
            
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="<?php echo $fotoName;?>" alt="..." class="img-circle profile_img">
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
                  <li><a><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; LOKET <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                        <li><a><i class="glyphicon glyphicon-user"></i>&nbsp;Loket<span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
      					    <? if($auth->IsAllowed("fo_loket_registrasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien/registrasi_pasien_awal.php"  style="font-size:15px">Registrasi Pasien</a></li> <? } ?>
                             <? if($auth->IsAllowed("fo_loket_edit_registrasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_registrasi/registrasi_irj_view.php"  style="font-size:15px">Edit Registrasi</a></li> <? } ?>
                             <? if($auth->IsAllowed("fo_loket_penata_jasa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penata_jasa_irj/ar_main.php"  style="font-size:15px">Penata Jasa IRJ</a></li> <? } ?>
                            <li><a href="<?php echo $ROOT; ?>registrasi_online/registrasi_online.php"  style="font-size:15px">Registrasi Online</a></li>                                          
                          </ul>
                        </li>
                        <li><a><i class="glyphicon glyphicon-list-alt"></i>&nbsp;Informasi<span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("fo_daftar_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien_loket/data_pasien_view.php"  style="font-size:15px">Data Pasien</a></li> <? } ?>
                            <? if($auth->IsAllowed("fo_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_all/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                            <? if($auth->IsAllowed("fol_lap_pengunjung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung_irj/report_pasien.php"  style="font-size:15px">Laporan Pengunjung</a></li><? } ?>
                            <? if($auth->IsAllowed("fol_lap_batal_kunjung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_batal_registrasi/lap_batal.php"  style="font-size:15px">Laporan Batal Kunjungan</a></li><? } ?>
                          </ul>
                        </li>
                        <li><a><i class="glyphicon glyphicon-file"></i>&nbsp;Cetak Tracer<span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>tracer_irj_lama/tracer_irj_lama_view.php"  style="font-size:15px">Cetak Tracer</a></li>
                            <li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode/tracer_irj_lama_barcode_view.php"  style="font-size:15px">Cetak Barcode Kecil</a></li>
                            <li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode_besar/tracer_irj_lama_barcode_view.php"  style="font-size:15px">Cetak Barcode Besar</a></li>
                            <li><a href="<?php echo $ROOT; ?>tracer_registrasi/tracer_registrasi_view.php"  style="font-size:15px">Cetak Registrasi</a></li>
                            <li><a href="<?php echo $ROOT; ?>tracer_riwayat/tracer_riwayat_view.php"  style="font-size:15px">Cetak Riwayat</a></li>
                            <!--<li><a href="<?php echo $ROOT; ?>tracer_status/tracer_status_view.php"  style="font-size:15px">Cetak Barcode Kecil</a></li>-->
                          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;Dokumentasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>user_guide/User_Manual_Loket.pdf"  style="font-size:15px">User Guide</a></li>
                          </ul>
                        </li>	
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-refresh"></i>&nbsp;Koneksi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>../../antrian_pasien/index.php"  style="font-size:15px">Antrian Pasien</a></li>
                          </ul>
                        </li>

                    </ul>
                  </li> 
                  <!-- End Menu Loket -->
                  
                  <!-- Menu Rawat Jalan -->
                  <li><a><i class="glyphicon glyphicon-cog"></i>&nbsp;&nbsp; RAWAT JALAN <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                     <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irj/pemeriksaan_irj_view.php"  style="font-size:15px">Pemeriksaan</a></li><? } ?>
                         <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irj_emr/pemeriksaan_irj_view.php"  style="font-size:15px">EMR DEV</a></li><? } ?>
    					  <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/terima_berkas.php"  style="font-size:15px">Terima Status</a></li><? } ?>
    					  <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_status/kirim_rm.php"  style="font-size:15px">Kembalikan Status</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                					<? if($auth->IsAllowed("irj_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_irj/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                    			<? if($auth->IsAllowed("irj_informasi_lap_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                  			  <? if($auth->IsAllowed("irj_informasi_lap_waktu_tunggu",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_waktu_tunggu/rekap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                  			  <? if($auth->IsAllowed("irj_informasi_lap_status_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_status_pasien_irj/lap_status_pasien_irj.php"  style="font-size:15px">Laporan Status Pasien</a></li><? } ?>
                  			  <? if($auth->IsAllowed("irj_informasi_rekap_bulanan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_bulanan_irj/rekap_bulanan_irj.php"  style="font-size:15px">Rekap Bulanan IRJ</a></li><? } ?>
    
                        </ul>
                      </li> 
                    </ul>
                  </li> 
                  <!-- END Menu Rawat Jalan -->

                  <!-- Menu IGD -->
                  <li><a><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp; IGD <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("igd_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd/pemeriksaan_igd_view.php"  style="font-size:15px">Pemeriksaan</a></li><? } ?>
                          <? if($auth->IsAllowed("igd_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd_int/pemeriksaan_irna_view.php"  style="font-size:15px">Pemeriksaan IGD INT</a></li><? } ?>
                             <? if($auth->IsAllowed("igd_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_igd_irna/pemeriksaan_irna_view.php"  style="font-size:15px">Pemeriksaan IGD IRNA</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                         <? if($auth->IsAllowed("igd_informasi_laporan_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_igd/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
             			 <? if($auth->IsAllowed("igd_informasi_laporan_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_igd/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
             			 <? if($auth->IsAllowed("igd_informasi_laporan_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_tindakan_igd/rekap_tindakan_igd.php"  style="font-size:15px">Rekap Tindakan</a></li><? } ?>
    
              			 <? if($auth->IsAllowed("igd_informasi_laporan_waktu_tunggu",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_igd/lap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
    					 <? if($auth->IsAllowed("igd_informasi_laporan_tingkat_kegawatan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_triase/lap_triase.php"  style="font-size:15px">Laporan Tingkat Kegawatan</a></li><? } ?>
    					 <? if($auth->IsAllowed("igd_informasi_laporan_status_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_status_pasien_igd/lap_status_pasien_igd.php"  style="font-size:15px">Laporan Status Pasien</a></li><? } ?>
    					 <? if($auth->IsAllowed("igd_informasi_rekap_bulanan_igd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_bulanan_igd/rekap_bulanan_igd.php"  style="font-size:15px">Rekap Bulanan IGD</a></li><? } ?>
     
                        </ul>
                      </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu IGD -->

                  <!-- Menu Rawat Inap -->
                  <li><a><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; RAWAT INAP <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <!-- <? if($auth->IsAllowed("rawat_inap_proses_antrian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_irna/antrian.php"  style="font-size:15px">Antrian</a></li><? } ?> -->
                            <? if($auth->IsAllowed("rawat_inap_pemeriksaan_irna",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna/pemeriksaan_irna_view.php"  style="font-size:15px">Pemeriksaan IRNA</a></li><? } ?>
                              <? if($auth->IsAllowed("rawat_inap_pemeriksaan_irna",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_irna/transfer_irna_view.php"  style="font-size:15px">Transfer IRNA</a></li><? } ?>
      					  <? if($auth->IsAllowed("rawat_inap_rencana_pulang",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien_rencana_pulang/penata_jasa_edit_view.php"  style="font-size:15px">Rencana Pulang</a></li><? } ?>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("rawat_inap_lap_pengunjung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung_irna/report_pasien.php"  style="font-size:15px">Laporan Pengunjung</a></li><? } ?>
                            <? if($auth->IsAllowed("rawat_inap_lap_penggunaan_bed",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penggunaan_bed/penggunaan_bed.php"  style="font-size:15px">Laporan Penggunaan Bed</a></li><? } ?>
                			  <? if($auth->IsAllowed("rawat_inap_lap_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_irna/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                			  <? if($auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sensus_harian/sensus_view.php"  style="font-size:15px">Sensus Harian</a></li><? } ?>
                			  <? if($auth->IsAllowed("rawat_inap_informasi_lap_sensus_harian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_pulang_irna/pasien_pulang_view.php"  style="font-size:15px">Laporan Pasien Pulang</a></li><? } ?>
                              <li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif/report_pasien.php"  style="font-size:15px">Laporan Perbandingan Tarif Pasien</a></li>
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Rawat Inap -->
                  
                  <!-- Menu Rehab Medik -->
                  <li><a><i class="glyphicon glyphicon-share"></i>&nbsp;&nbsp; REHAB MEDIK <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <li><a href="<?php echo $ROOT; ?>pemeriksaan_rehab_medik/pemeriksaan_rehab_medik_view.php"  style="font-size:15px">Pemeriksaan</a></li>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>lap_kunjungan_rehab_medik/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li>
                					  <li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li>
                					  <li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_rehab_medik/rekap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li>
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Rehab Medik -->
                  
                  <!-- Menu Rekam Medik -->
                  <li><a><i class="glyphicon glyphicon-th-list"></i>&nbsp;&nbsp; REKAM MEDIK <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">   
                            <? if($auth->IsAllowed("rm_proses_data_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_pasien/data_pasien_view.php"  style="font-size:15px">Data Pasien</a></li><? } ?>                   
                            <? if($auth->IsAllowed("rm_proses_input_diagnosa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_diagnosa_inacbg51_new/input_rm.php"  style="font-size:15px">Input Diagnosa New</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_proses_input_diagnosa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_diagnosa_inacbg51/input_rm.php"  style="font-size:15px">Input Diagnosa </a></li><? } ?>
                            <? if($auth->IsAllowed("rm_proses_input_diagnosa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_diagnosa_inacbg51_new/input_rm_coba.php"  style="font-size:15px">Cek Tanggal Pulang</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_proses_edit_diagnosa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_diagnosa_inacbg51/edit_input_rm.php"  style="font-size:15px">Edit Diagnosa</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_proses_verifikasi_diagnosa",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>verifikasi_diagnosa/edit_input_rm.php"  style="font-size:15px">Verifikasi Diagnosa</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_proses_bridging_jkn",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_inacbg51/edit_input_rm.php"  style="font-size:15px">Bridging JKN</a></li><? } ?>
                              <? if($auth->IsAllowed("rm_proses_bridging_jkn",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>inacbg_masal/inacbg_masal_view.php"  style="font-size:15px">Bridging Massal</a></li><? } ?>
      					  <? if($auth->IsAllowed("rm_proses_data_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_keluar/tracer_keluar.php"  style="font-size:15px">Kirim Status Ke Poli</a></li><? } ?>
      					  <? if($auth->IsAllowed("rm_proses_data_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_terima_rm/terima_rm.php"  style="font-size:15px">Terima Status Dari Poli</a></li><? } ?>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("rm_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_pengunjung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pengunjung/report_pasien.php"  style="font-size:15px">Laporan Pengunjung</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekam_medik_pasien/rekam_medik_pasien.php"  style="font-size:15px">Rekam Medik Pasien</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_rekam_medik",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rekam_medik/lap_rm.php"  style="font-size:15px">Lap Rekam Medik</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_inacbg",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_inacbg/lap_inacbg.php"  style="font-size:15px">Lap INACBG</a></li><? } ?>         
                            <? if($auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_10_bsr_diagnosa/rekap_10_bsr_diagnosa.php"  style="font-size:15px">Lap 10 Besar Diagnosa</a></li><? } ?>
                            <!--
                            <? if($auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_igd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>10bsr_diagnosa_igd/10bsr_diagnosa_igd.php"  style="font-size:15px">Lap 10 Besar Diagnosa IGD</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_10_bsr_diagnosa_irna",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>10bsr_diagnosa_irna/10bsr_diagnosa_irna.php"  style="font-size:15px">Lap 10 Besar Diagnosa IRNA</a></li><? } ?>
                            -->
                            <? if($auth->IsAllowed("rm_informasi_rekap_pasien_per_jenis_kelamin",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_jns_kelamin/rekap_jenis_kelamin.php"  style="font-size:15px">Rekap Pasien Per Jenis Kelamin</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_demografi_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_demografi/rekap_propinsi.php"  style="font-size:15px">Demografi Pasien</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_rekap_pasien_per_agama",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_agama/rekap_agama.php"  style="font-size:15px">Rekap Pasien Per Agama</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_pasien_masuk_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_masuk_inap/report_pasien_masuk.php"  style="font-size:15px">Lap Pasien Masuk Inap</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_pasien_sedang_dirawat",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_masih_dirawat/report_pasien_dirawat.php"  style="font-size:15px">Lap Pasien Sedang Dirawat</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_pasien_pulang",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pasien_pulang/report_pasien_pulang.php"  style="font-size:15px">Lap Pasien Pulang</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_index_dokter",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_dokter/lap_bridging.php"  style="font-size:15px">Index Dokter</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_rekap_pasien_per_cara_bayar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_pasien_instalasi/rekap_pasien.php"  style="font-size:15px">Rekap Pasien Per Cara Bayar</a></li><? } ?>
                					  <? if($auth->IsAllowed("rm_informasi_index_kematian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>index_kematian/report_pasien_pulang.php"  style="font-size:15px">Index Kematian</a></li><? } ?>
                					  <? if($auth->IsAllowed("rm_informasi_index_kematian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>laporan_tracer/laporan_tracer.php"  style="font-size:15px">Laporan Berkas Pasien</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>trace_history_pasien/history_pasien.php"  style="font-size:15px">History Kunjungan Pasien</a></li><? } ?>
                          </ul>
                        </li>                   
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-refresh"></i>&nbsp;Tracer <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <!--li><a href="<?php echo $ROOT; ?>tracer_irj_baru/tracer_irj_baru_view.php"  style="font-size:15px">Tracer IRJ Pasien Baru</a></li-->
                            <? if($auth->IsAllowed("rm_tracer_tracer_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_registrasi/tracer_registrasi_view.php"  style="font-size:15px">Tracer Registrasi</a></li><? } ?>
      
                            <? if($auth->IsAllowed("rm_tracer_tracer_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama/tracer_irj_lama_view.php"  style="font-size:15px">Tracer Pasien</a></li><? } ?>
      
                            <? if($auth->IsAllowed("rm_tracer_tracer_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_riwayat/tracer_riwayat_view.php"  style="font-size:15px">Tracer Riwayat Pasien</a></li><? } ?>
      
                            <? if($auth->IsAllowed("rm_tracer_tracer_barcode_kecil",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode/tracer_irj_lama_barcode_view.php"  style="font-size:15px">Tracer Barcode Kecil </a></li><? } ?>
      					            <? if($auth->IsAllowed("rm_tracer_tracer_barcode_besar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tracer_irj_lama_barcode_besar/tracer_irj_lama_barcode_view.php"  style="font-size:15px">Tracer Barcode Besar</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_tracer_cetak_ulang_tracer",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_tracer/cetak_ulang_tracer_view.php"  style="font-size:15px">Cetak Ulang Tracer</a></li><? } ?>      
                          </ul>
                        </li>                  
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-th-large"></i>&nbsp;SP2RS <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("rm_sp2rs_rl_1_2",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_1_2/lap_rl_12.php"  style="font-size:15px">RL 1.2</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_1",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_1/lap_rl_3_1.php"  style="font-size:15px">RL 3.1</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_2",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_2/lap_rl_3_2.php"  style="font-size:15px">RL 3.2</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_3",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_3/lap_rl_3_3.php"  style="font-size:15px">RL 3.3</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_4",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_4/lap_rl_3_4.php"  style="font-size:15px">RL 3.4</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_5",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_5/lap_rl_3_5.php"  style="font-size:15px">RL 3.5</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_6",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_6/lap_rl_3_6.php"  style="font-size:15px">RL 3.6</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_14",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_14/lap_rl_3_14.php"  style="font-size:15px">RL 3.14</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_3_15",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_3_15/lap_rl_3_15.php"  style="font-size:15px">RL 3.15</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_4_a",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_4_a/lap_rl_4_a.php"  style="font-size:15px">RL 4.a</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_4_b",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_4_b/lap_rl_4_b.php"  style="font-size:15px">RL 4.b</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_5_1",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_5_1/lap_rl_5_1.php"  style="font-size:15px">RL 5.1</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_5_2",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_5_2/lap_rl_5_2.php"  style="font-size:15px">RL 5.2</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_5_3",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_5_3/lap_rl_5_3.php"  style="font-size:15px">RL 5.3</a></li><? } ?>
                            <? if($auth->IsAllowed("rm_sp2rs_rl_5_4",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rl_5_4/lap_rl_5_4.php"  style="font-size:15px">RL 5.4</a></li><? } ?>                           
                        </ul>
                      </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Rekam Medik -->

                  <!-- Menu Operasi -->
                  <li><a><i class="glyphicon glyphicon-scissors"></i>&nbsp;&nbsp; OPERASI <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("operasi_proses_rencana_operasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rencana_operasi/penata_jasa_edit_view.php"  style="font-size:15px">Rencana Operasi</a></li><? } ?>
              			  <? if($auth->IsAllowed("operasi_proses_tindakan_operasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_operasi/pemeriksaan_view.php"  style="font-size:15px">Tindakan Operasi</a></li><? } ?>
                          <? if($auth->IsAllowed("operasi_proses_operasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>operasi/penata_jasa_edit_view.php"  style="font-size:15px">Operasi</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? if($auth->IsAllowed("operasi_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_operasi/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
              			  <? if($auth->IsAllowed("operasi_informasi_lap_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
              			  <? if($auth->IsAllowed("operasi_informasi_lap_operasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Operasi</a></li> <? } ?>                     
              			  <li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Pemakaian BHP IBS</a></li>                  
    
                        </ul>
                      </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Operasi -->
                  
                  <!-- Menu Laboratorium -->
                  <li><a><i class="glyphicon glyphicon-tint"></i>&nbsp;&nbsp; LABORATORIUM <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <!--<li><a href="<?php echo $ROOT; ?>master_dokter_lab/dokter_view.php" >Master Dokter</a></li> -->
                          <!-- <? //if($auth->IsAllowed("lab_master_template_hasil_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_template_lab/template_lab_view.php"  style="font-size:15px">Template Hasil Lab</a></li><? //} ?>   -->
                          <?// if($auth->IsAllowed("lab_master_master_hasil_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>hasil_lab/hasil_lab_view.php"  style="font-size:15px">Master Hasil Lab</a></li><? //} ?>
                           <!--<? if($auth->IsAllowed("lab_master_master_hasil_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_hasil_lab/lab_view.php"  style="font-size:15px">Master Hasil Lab</a></li><? } ?>-->
                        </ul>
                      </li>                  
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <!--<li><a href="<?php echo $ROOT; ?>lab/penata_jasa_edit_view.php" >Lab Luar</a></li> -->
                          <? if($auth->IsAllowed("lab_proses_registrasi_manual",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_lab/"  style="font-size:15px">Registrasi Laboratorium</a></li> <? } ?>     
                          <? if($auth->IsAllowed("lab_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_lab/pemeriksaan_lab_view.php"  style="font-size:15px">Pemeriksaan</a></li><? } ?>
                           <!-- <? if($auth->IsAllowed("lab_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_lab/pemeriksaan_lab_luar_view.php"  style="font-size:15px">Pemeriksaan Lab Luar</a></li><? } ?>-->
                           <li><a href="<?php echo $ROOT; ?>pemeriksaan_lab/pemeriksaan_lab_luar_view.php"  style="font-size:15px">Pemeriksaan Lab Luar</a></li>
                          <? if($auth->IsAllowed("lab_proses_input_hasil_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>input_hasil_lab_irj/input_hasil_lab_view.php"  style="font-size:15px">Input hasil Lab</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                    
                          <? if($auth->IsAllowed("lab_informasi_lap_kunjungan_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_lab/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
                          <? if($auth->IsAllowed("lab_informasi_lap_tindakan_lab",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_lab/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
                          <? if($auth->IsAllowed("lab_informasi_lap_waktu_tunggu",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_lab/lap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                        </ul>
                      </li> 
                    </ul>
                  </li> 
                  <!-- END Menu Laboratorium -->
                  
                  <!-- Menu Radiologi -->
                  <li><a><i class="glyphicon glyphicon-erase"></i>&nbsp;&nbsp; RADIOLOGI <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("rad_master_master_template",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_template/template_view.php"  style="font-size:15px">Master Template</a></li><? } ?>
                          <? if($auth->IsAllowed("rad_master_master_kelompok",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_kelompok/kelompok_view.php"  style="font-size:15px">Master Kelompok</a></li><? } ?>
                          <? if($auth->IsAllowed("rad_master_master_sub_kelompok",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_sub_kelompok/sub_kelompok_view.php"  style="font-size:15px">Master Sub Kelompok</a></li><? } ?>
                          <? if($auth->IsAllowed("rad_master_dokter_radiologi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_dokter/dokter_view.php"  style="font-size:15px">Dokter Radiologi</a></li><? } ?>
                        </ul>
                      </li>                  
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu"> 
    					 <!--  <? if($auth->IsAllowed("rad_proses_registrasi_manual",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_radiologi/registrasi_radiologi_view.php"  style="font-size:15px">Registrasi Manual</a></li><? } ?>	-->				
                          <? if($auth->IsAllowed("rad_proses_radiologi_luar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_radiologi/"  style="font-size:15px">Registrasi Radiologi</a></li> <? } ?>     
                          <? if($auth->IsAllowed("rad_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_radiologi/pemeriksaan_radiologi_view.php"  style="font-size:15px">Pemeriksaan</a></li><? } ?>
                          <!-- <? if($auth->IsAllowed("rad_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_radiologi/pemeriksaan_radiologi_luar_view.php"  style="font-size:15px">Pemeriksaan Rad. Luar</a></li><? } ?> -->
                          <li><a href="<?php echo $ROOT; ?>pemeriksaan_radiologi/pemeriksaan_radiologi_luar_view.php"  style="font-size:15px">Pemeriksaan Rad. Luar</a></li>
                          <? if($auth->IsAllowed("rad_proses_input_resume",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>resume_radiologi/penata_jasa_edit_view.php"  style="font-size:15px">Input Resume</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                   
                          <? if($auth->IsAllowed("rad_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_radiologi/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
              			      <? if($auth->IsAllowed("rad_informasi_lap_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_radiologi/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
    					            <? if($auth->IsAllowed("rad_informasi_lap_waktu_tunggu",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_radiologi/lap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                           <li><a href="<?php echo $ROOT; ?>lap_penerimaan_resume/report_pasien.php"  style="font-size:15px">Laporan Penerimaan Resume</a></li>
                        </ul>
                      </li>  
                    </ul>
                  </li> 
                  <!-- END Menu Radiologi --> 
                  
                  <!-- Menu IPJ -->
                  <li><a><i class="glyphicon glyphicon-object-align-right"></i>&nbsp;&nbsp; IPJ <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("ipj_registrasi_tarik",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_ipj_tarik/registrasi_ipj_view.php"  style="font-size:15px">Registrasi</a></li><? } ?>
                          <? if($auth->IsAllowed("ipj_registrasi_ipj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>registrasi_ipj/registrasi_pasien.php"  style="font-size:15px">Registrasi Luar</a></li><? } ?>
                          <? if($auth->IsAllowed("ipj_pemeriksaan_ipj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pemeriksaan_ipj/pemeriksaan_ipj_view.php"  style="font-size:15px">Pemeriksaan</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? if($auth->IsAllowed("ipj_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_ipj/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li><? } ?>
              					  <? if($auth->IsAllowed("ipj_lap_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_tindakan_ipj/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li><? } ?>
              					  <? if($auth->IsAllowed("ipj_waktu_tunggu",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_ipj/lap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li><? } ?>
                        	<? if($auth->IsAllowed("ipj_lap_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_ipj/lap_tindakan.php"  style="font-size:15px">Laporan Pasien IPJ</a></li><? } ?>
    					</ul>
                      </li>                    
                    </ul>
                  </li> 
                  <!-- END Menu IPJ -->
                  
                  <!-- Menu Apotik -->
                  <li><a><i class="glyphicon glyphicon-baby-formula"></i>&nbsp;&nbsp; APOTIK <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <li><a href="<?php echo $ROOT; ?>kat_barang/grup_item_view.php"  style="font-size:15px">Kat. Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>sat_barang/satuan_view.php"  style="font-size:15px">Satuan Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>petunjuk/petunjuk_view.php"  style="font-size:15px">Petunjuk Dosis</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang/item_view.php"  style="font-size:15px">Setup Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>paket_farmasi/role_view.php"  style="font-size:15px">Paket</a></li>
                            <li><a href="<?php echo $ROOT; ?>narkotika/narkotika_view.php"  style="font-size:15px">Obat Narkotika</a></li>
                            <li><a href="<?php echo $ROOT; ?>psikotropika/psikotropika_view.php"  style="font-size:15px">Obat Psikotropika</a></li>
                            <li><a href="<?php echo $ROOT; ?>aturan_pakai/aturan_pakai_view.php"  style="font-size:15px">Aturan Pakai</a></li>
                            <? //if($auth->IsAllowed("apo_master_jam_aturan_pakai",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jam_aturan_pakai/jam_jadwal_view.php"  style="font-size:15px">Jam Aturan Pakai</a></li><? //} ?>
                            <li><a href="<?php echo $ROOT; ?>aturan_minum/aturan_minum_view.php"  style="font-size:15px">Aturan Minum</a></li>
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_apotik/konfigurasi_edit.php"  style="font-size:15px">Konfigurasi Apotik</a></li>
                          </ul>
                        </li>                
                         <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Permintaan <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <? if($auth->IsAllowed("apo_minta_minta_barang",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>permintaan_barang/transfer_stok_view.php"  style="font-size:15px">Permintaan Barang</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_minta_kirim_barang",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pengiriman_barang/transfer_stok_view.php"  style="font-size:15px">Pengiriman Barang</a></li><? } ?>
                            
                          </ul>
                        </li>
                         <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan/penjualan_view.php"  style="font-size:15px">Penjualan Umum</a></li><? } ?>
                            <!--<? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_bpjs/penjualan_view.php"  style="font-size:15px">Penjualan Klarifikasi</a></li><? } ?> -->
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_bpjs/penjualan_view.php"  style="font-size:15px">Penjualan Jaminan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan/penjualan_bebas_view.php"  style="font-size:15px">Penjualan Bebas</a></li><? } ?>
                            <!--<? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_mundur/penjualan_view.php"  style="font-size:15px">Penjualan Mundur</a></li><? } ?> -->
                            
      
      <!--                      <? if($auth->IsAllowed("apo_proses_penjualan_igd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_igd/penjualan_view.php"  style="font-size:15px">Penjualan IGD</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irna",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_inap/penjualan_view.php"  style="font-size:15px">Penjualan Inap</a></li><? } ?>   -->
                            <? if($auth->IsAllowed("apo_proses_retur_penjualan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>retur_penjualan/retur_penjualan_view.php"  style="font-size:15px">Retur Penjualan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_all_ulang_penjualan/cetak_ulang_penjualan.php"  style="font-size:15px">Cetak Ulang Penjualan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_etiket/cetak_ulang_penjualan.php"  style="font-size:15px">Cetak Ulang Etiket</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_retur_jual/cetak_ulang_retur_view.php"  style="font-size:15px">Cetak Ulang Retur Penjualan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_apotik/antrian_apotik_view.php"  style="font-size:15px">Proses Penyiapan Obat</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_proses_penjualan_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_langsung/penjualan_view.php"  style="font-size:15px">Penjualan Langsung</a></li><? } ?>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                          
                            <? if($auth->IsAllowed("apo_informasi_lap_penjualan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penjualan/report_penjualan.php"  style="font-size:15px">Laporan Penjualan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_informasi_rekap_penjualan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_penjualan/rekap_penjualan.php"  style="font-size:15px">Rekap Penjualan</a></li><? } ?>
                            <? if($auth->IsAllowed("apo_informasi_retur_penjualan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_retur_penjualan/report_retur_penjualan.php"  style="font-size:15px">Laporan Retur Penjualan</a></li><? } ?>
                            <li><a href="<?php echo $ROOT; ?>rekap_bulanan_apotik/rekap_bulanan.php"  style="font-size:15px">Laporan Kinerja Apotik</a></li>
                            <li><a href="<?php echo $ROOT; ?>history_pakai_obat/history_view.php"  style="font-size:15px">History Pemakaian Obat/Alkes Pasien</a></li>
                            <li><a href="<?php echo $ROOT; ?>antrian_apotik/lcd_apotik.php"  style="font-size:15px">LCD Apotik</a></li>
                            <li><a href="<?php echo $ROOT; ?>antrian_apotik/lap_waktu_tunggu.php"  style="font-size:15px">Lap. Waktu Tunggu</a></li>
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Apotik -->
                  
                  <!-- Menu BPJS -->
                  <li><a><i class="glyphicon glyphicon-ice-lolly"></i>&nbsp;&nbsp; BPJS <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <!-- <? if($auth->IsAllowed("bpjs_antrian_irna_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>antrian_irna_bpjs/antrian.php"  style="font-size:15px">Antrian Pasien IRNA</a></li><? } ?> -->
                            <? if($auth->IsAllowed("tutup_transaksi_irj_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tutup_transaksi_irj_bpjs/kasir_pemeriksaan_view.php"  style="font-size:15px">Tutup Transaksi</a></li><? } ?>
                            <? if($auth->IsAllowed("bpjs_antrian_irna_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reg_mundur/registrasi_pasien_awal.php"  style="font-size:15px">Registrasi Mundur</a></li><? } ?>
                            <!--<? if($auth->IsAllowed("tutup_transaksi_igd_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tutup_transaksi_igd_bpjs/kasir_pemeriksaan_view.php"  style="font-size:15px">Tutup Transaksi IGD</a></li><? } ?>  
                            <? if($auth->IsAllowed("tutup_transaksi_irna_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tutup_transaksi_irna_bpjs/kasir_pemeriksaan_view.php"  style="font-size:15px">Tutup Transaksi IRNA</a></li><? } ?>  -->
                            <? if($auth->IsAllowed("cetak_ulang_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>loket_jpk/registrasi_pasien_awal.php"  style="font-size:15px">Create SEP</a></li><? } ?>
      					           <? if($auth->IsAllowed("cetak_ulang_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_sep/cetak_ulang_sep_view.php"  style="font-size:15px">Cetak Ulang SEP</a></li><? } ?>
                            <? if($auth->IsAllowed("update_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>update_sep/update_sep_view.php"  style="font-size:15px">Update SEP</a></li><? } ?>
                            <? if($auth->IsAllowed("hapus_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>hapus_sep/hapus_sep_view.php"  style="font-size:15px">Hapus SEP</a></li><? } ?> 
                            <? if($auth->IsAllowed("cari_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cari_sep/cari_sep_view.php"  style="font-size:15px">Cari SEP</a></li><? } ?>
                            <? if($auth->IsAllowed("cari_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>loket_jpk/pengajuan_sep.php"  style="font-size:15px">Pengajuan SEP</a></li><? } ?>
                             <? if($auth->IsAllowed("cari_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>loket_jpk/approval_sep.php"  style="font-size:15px">Approval SEP</a></li><? } ?>
                             <? if($auth->IsAllowed("cari_sep_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>update_tgl_pulang/update_tgl_pulang.php"  style="font-size:15px">Update Tanggal Pulang</a></li><? } ?>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Cetak <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <? if($auth->IsAllowed("tutup_transaksi_irj_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_rincian_jaminan/cetak_view_pemeriksaan.php"  style="font-size:15px">Cetak Ulang Rincian</a></li><? } ?>
                          </ul>
                        </li> 
      
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp&nbsp&nbsp Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("lap_kunjungan_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_kunjungan_irj/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li>  <? } ?>
                            <? if($auth->IsAllowed("lap_kunjungan_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pemb_jaminan/report_setoran_loket.php"  style="font-size:15px">Laporan Pendapatan</a></li>  <? } ?>
                            <? if($auth->IsAllowed("lap_kunjungan_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_jaminan/report_setoran_cicilan.php"  style="font-size:15px">Laporan Jaminan</a></li>  <? } ?>
                					  <? if($auth->IsAllowed("ref_poli_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_poli_bpjs/ref_poli_bpjs.php"  style="font-size:15px">Referensi Poli BPJS</a></li>  <? } ?>
                					  <? if($auth->IsAllowed("ref_faskes_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_faskes_bpjs/ref_faskes_bpjs.php"  style="font-size:15px">Referensi Faskes BPJS</a></li>  <? } ?>
                					  <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_ruangrawat_bpjs.php"  style="font-size:15px">Referensi Ruang Rawat BPJS</a></li>  <? } ?>
                              <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_kelasrawat_bpjs.php"  style="font-size:15px">Referensi Kelas Rawat BPJS</a></li>  <? } ?>
                					  <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_global_fund/rekap_global_fund.php"  style="font-size:15px">Rekap Global Fund</a></li>  <? } ?>
                          </ul>
                        </li>                   
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp&nbsp&nbsp Dokumentasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>user_guide/user_guide_bpjs.pdf"  style="font-size:15px">User Guide</a></li>
                          </ul>
                        </li>                   
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp&nbsp&nbsp VCLAIM <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ref_kamar_bpjs.php"  style="font-size:15px">Referensi Kamar BPJS</a></li>  <? } ?>
                            <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ruangan_baru.php"  style="font-size:15px">Ruangan Baru</a></li>  <? } ?> 
                            <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/ketersediaan_rs.php"  style="font-size:15px">Ketersediaan Kamar RS</a></li>  <? } ?>  
                              <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/hapus_ruangan.php"  style="font-size:15px">Hapus Ruangan</a></li>  <? } ?> 
                              <? if($auth->IsAllowed("ref_ruang_rawat_bpjs",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ref_ruangrawat_bpjs/update_ketersediaan.php"  style="font-size:15px">Update Ketersediaan</a></li>  <? } ?>  
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu BPJS -->

                  <!-- Menu MCU -->
                  <li><a><i class="glyphicon glyphicon-scissors"></i>&nbsp;&nbsp; MCU <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <li><a href="<?php echo $ROOT; ?>pemeriksaan_mcu/pemeriksaan_mcu_view.php"  style="font-size:15px">Pemeriksaan</a></li>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="<?php echo $ROOT; ?>lap_kunjungan_irj/lap_kunjungan.php"  style="font-size:15px">Laporan Kunjungan</a></li>
              					  <li><a href="<?php echo $ROOT; ?>lap_tindakan/lap_tindakan.php"  style="font-size:15px">Laporan Tindakan</a></li>
              					  <li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_mcu/rekap_waktu_tunggu.php"  style="font-size:15px">Laporan Waktu Tunggu</a></li>
                        </ul>
                      </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu MCU -->
                  
                  <!-- Menu Kasir -->
                  <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; KASIR <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("kasir_proses_pembayaran_irj",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_irj/kasir_pemeriksaan_view.php"  style="font-size:15px">Pembayaran Pasien</a></li><? } ?>
                          <!-- SEKARANG DIJADIKAN SATU
                          <? if($auth->IsAllowed("kasir_proses_pembayaran_igd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_igd/kasir_pemeriksaan_view.php"  style="font-size:15px">Pembayaran Pasien IGD</a></li><? } ?>
                          <? if($auth->IsAllowed("kasir_proses_pembayaran_irna",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pembayaran_irna/kasir_pemeriksaan_view.php"  style="font-size:15px">Pembayaran Pasien IRNA</a></li><? } ?>
                          -->
                        </ul>
                      </li> 
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Cetak <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">                      
                          <? if($auth->IsAllowed("rawat_inap_cetak_ulang_kwitansi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>cetak_ulang_rincian_kwitansi/cetak_view_pemeriksaan.php"  style="font-size:15px">Cetak Ulang Kwitansi</a></li><? } ?>
                        </ul>
                      </li> 
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? if($auth->IsAllowed("kasir_lap_pendapatan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pemb/report_setoran_loket.php"  style="font-size:15px">Laporan Pendapatan</a></li><? } ?>
                          <? if($auth->IsAllowed("kasir_lap_detail_pendapatan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_pemb/report_setoran_loket.php"  style="font-size:15px">Laporan Detail Pendapatan</a></li><? } ?>
                          <? if($auth->IsAllowed("kasir_lap_penerimaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_penerimaan/report_setoran_cicilan.php"  style="font-size:15px">Laporan Penerimaan</a></li><? } ?>
    <!--                      <? if($auth->IsAllowed("kasir_lap_detail_penerimaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_penerimaan/report_detail_penerimaan.php"  style="font-size:15px">Laporan Detail Penerimaan</a></li><? } ?> -->
                          <? if($auth->IsAllowed("kasir_lap_pendapatan_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_pendapatan_instalasi/lap_pendapatan_instalasi.php"  style="font-size:15px">Laporan Pendapatan Instalasi</a></li><? } ?>
                          <? if($auth->IsAllowed("kasir_lap_pendapatan_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_rincian_pendapatan/lap_rincian_pendapatan.php"  style="font-size:15px">Rincian Pendapatan</a></li><? } ?>
                          <? if($auth->IsAllowed("kasir_lap_pendapatan_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rekap_rincian_pendapatan/rekap_rincian_pendapatan.php"  style="font-size:15px">Rekap Pendapatan dan Penerimaan</a></li><? } ?>
                          <li><a href="<?php echo $ROOT; ?>lap_perbandingan_tarif/report_pasien.php"  style="font-size:15px">Laporan Perbandingan Tarif Pasien</a></li>
    
                        </ul>
                      </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Kasir -->
                  
                  <!-- Menu Manajemen -->
                  <li><a><i class="glyphicon glyphicon-dashboard"></i>&nbsp;&nbsp; MANAJEMEN <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="fa fa-home"></i>&nbsp;&nbsp; Pengaturan <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("man_pengaturan_konfigurasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi/konfigurasi_edit.php"  style="font-size:15px">Konfigurasi RS</a></li><? } ?>
                            <? if($auth->IsAllowed("man_pengaturan_konf_tarif",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_biaya/konfigurasi_edit.php"  style="font-size:15px">Konfigurasi Tarif</a></li><? } ?>
                            <!--<li><a href="<?php echo $ROOT; ?>konfigurasi_tampilan/konfigurasi_edit.php">Konfigurasi Tampilan</a></li>
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_apotik/konfigurasi_edit.php">Konfigurasi Apotik</a></li>
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_lab/konfigurasi_edit.php">Konfigurasi Lab</a></li>
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_radiologi/konfigurasi_edit.php">Konfigurasi Radiologi</a></li>
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_bor/konfigurasi_edit.php">Konfigurasi Bor</a></li>  -->
                            <? if($auth->IsAllowed("man_pengaturan_konf_antrian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konfigurasi_registrasi/konfigurasi_edit.php"  style="font-size:15px">Konfigurasi Pelayanan</a></li><? } ?>
                            <!--<li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php">Ganti Password</a></li>-->                    
                          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="fa fa-users fa-lg"></i>&nbsp;&nbsp; User <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("man_user_edit_pegawai",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_pegawai/data_pegawai_view.php" style="font-size:15px">Manajemen Pegawai</a></li><? } ?>
                            <? if($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satker/dep_view.php" style="font-size:15px">Master Satuan Kerja</a></li><? } ?>
                            <? if($auth->IsAllowed("man_user_jabatan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jabatan/role_view.php" style="font-size:15px">Jabatan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_user_user_login",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>user_login/hakakses_view.php" style="font-size:15px">User Login</a></li><? } ?>
      					            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php"  style="font-size:15px">Ganti Password</a></li><? } ?>
                            
      					          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="fa fa-money"></i>&nbsp;&nbsp; Tarif <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("man_tarif_jenis_biaya",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenis_biaya/jenis_biaya_view.php" style="font-size:15px">Master Split Tindakan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan_header_instalasi/kat_tindakan_header_instalasi_view.php" style="font-size:15px">Kategori Tindakan header Intalasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_kat_tindakan_header",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan_header/kat_tindakan_header_view.php" style="font-size:15px">Kategori Tindakan header </a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_kat_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_tindakan/kat_tindakan_view.php" style="font-size:15px">Kategori Tindakan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_master_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_jenis_tindakan/jenis_tindakan_view.php"  style="font-size:15px">Master Jenis Tindakan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_master_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_variable_inacbg/variable_inacbg_view.php"  style="font-size:15px">Master Variable INACBG</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_master_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tindakan/tindakan_view.php " style="font-size:15px">Master Tindakan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_biaya_reg",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_registrasi/biaya_view.php "  style="font-size:15px">Biaya Registrasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_biaya_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_pemeriksaan/biaya_view.php "  style="font-size:15px">Biaya Pemeriksaan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_biaya_akomodasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_akomodasi/biaya_akomodasi_view.php"  style="font-size:15px">Biaya Akomodasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_biaya_visite",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_visite/biaya_visite_view.php"  style="font-size:15px">Biaya Visite</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_biaya_visite",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>biaya_askep/biaya_askep_view.php"  style="font-size:15px">Biaya Askep</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_tarif_header_poli",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tarif_header_poli/biaya_poli_view.php "  style="font-size:15px">Tarif Header klinik</a></li><? } ?>
                            
                            <? if($auth->IsAllowed("man_tarif_tarif_tindakan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>tarif_tindakan_irj/tindakan_view.php " style="font-size:15px">Tarif Tindakan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_rincian_tindakan_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rincian_tindakan_irna/kategori_kassa_view.php"  style="font-size:15px">Rincian Tindakan Inap</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenisbayar/jenis_bayar_view.php"  style="font-size:15px">Setup Jenis Bayar</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_plafon_fasilitas",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>fasilitas/fasilitas_view.php"  style="font-size:15px">Plafon Karyawan</a></li><? } ?>
                            <!--<? if($auth->IsAllowed("man_tarif_plafon_jasa_raharja",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jasa_raharja/jasa_raharja_view.php"  style="font-size:15px">Plafon Jasa Raharja</a></li><? } ?> -->
                            <? if($auth->IsAllowed("man_tarif_master_perusahaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>perusahaan/perusahaan_view.php"  style="font-size:15px">Master Perusahaan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_tarif_master_margin",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>margin_obat/margin_view.php"  style="font-size:15px">Master Margin Obat</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_detil_paket",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>paket_poli/paket_poli_view.php"  style="font-size:15px">Detail Paket Klinik</a></li> <? } ?>  
                          </ul>
                        </li>   
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Medis <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>instalasi/instalasi_view.php"  style="font-size:15px">Setup Instalasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_setup_sub_instalasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sub_instalasi/sub_instalasi_view.php"  style="font-size:15px">Setup Sub Instalasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_setup_poli",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>setup_poli/jenis_poli_view.php"  style="font-size:15px">Setup Poli</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_jadwal_dokter",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jam_jadwal/jam_jadwal_view.php"  style="font-size:15px">Jam Jadwal</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_jadwal_dokter",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jadwal_dokter/jadwal_dokter_view.php"  style="font-size:15px">Jadwal Dokter</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_kelas_kamar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kelas_kamar/tindakan_view.php"  style="font-size:15px">Kelas Kamar</a></li><? } ?>
                            <? if($auth->IsAllowed("igd_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenis_kelas/jenis_kelas_view.php"  style="font-size:15px">Jenis Kelas</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_gedung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ruang_rawat/ruang_rawat_view.php"  style="font-size:15px">Gedung / Ruang Rawat</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_kamar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kamar/kamar_view.php"  style="font-size:15px">Kamar</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_kondisi_akhir",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kondisi_akhir/kondisi_view.php"  style="font-size:15px">Kondisi Akhir</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_cara_keluar_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_cara_masuk/masuk_view.php"  style="font-size:15px">Master Cara Masuk</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_cara_keluar_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_cara_keluar_inap/cara_keluar_view.php"  style="font-size:15px">Master Cara Keluar Inap</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_master_prosedur_masuk",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_prosedur_masuk/prosedur_view.php"  style="font-size:15px">Master Prosedur Masuk</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_master_bor_kamar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>bor_kamar/bor_kamar_view.php"  style="font-size:15px">Master Bor Kamar</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_kategori_icd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kat_icd/kat_icd_view.php"  style="font-size:15px">Kategori ICD</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_det_kat_icd",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>det_kat_icd/det_icd_view.php"  style="font-size:15px">Det kat ICD</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>propinsi/propinsi_view.php"  style="font-size:15px">Propinsi</a></li><? } ?>
                            <!--
                            <? if($auth->IsAllowed("man_medis_pekerjaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pekerjaan/pekerjaan_view.php"  style="font-size:15px">Pekerjaan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_sekolah",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sekolah/sekolah_view.php"  style="font-size:15px">Pendidikan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_jenis_pegawai",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>jenis_pegawai/jenis_pegawai_view.php"  style="font-size:15px">Jenis Pegawai</a></li><? } ?> 
                            <? if($auth->IsAllowed("man_medis_pejabat_penandatangan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pejabat_penandatangan/pejabat_penandatangan_view.php"  style="font-size:15px">Pejabat Penandatangan</a></li><? } ?>   
                            <? if($auth->IsAllowed("man_medis_setup_tenaga_medis",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>setup_tenaga_medis/medis_view.php"  style="font-size:15px">Setup Tugas Medis</a></li><? } ?>     -->
                            <? if($auth->IsAllowed("man_medis_rujukan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>rujukan/rujukan_view.php"  style="font-size:15px">Rujukan</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_icd9",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>icd9/icd9_view.php"  style="font-size:15px">ICD9</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_icd10",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>icd10/icd_view.php"  style="font-size:15px">ICD10</a></li><? } ?>
                            <!--
                            <? if($auth->IsAllowed("man_medis_external_cause",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>external_cause/external_cause_view.php"  style="font-size:15px">External Cause</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_morfologi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>morfologi/morfologi_view.php"  style="font-size:15px">Morfologi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_pengaturan_status_perkawinan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>status_perkawinan/status_perkawinan_view.php"  style="font-size:15px">Status Perkawinan</a></li><? } ?>   -->                  
                            <? if($auth->IsAllowed("man_medis_kamar_operasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_kamar_operasi/master_kamar_operasi_view.php"  style="font-size:15px">Master Kamar Operasi</a></li><? } ?>
                            <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>sebab_sakit/sebab_sakit_view.php"  style="font-size:15px">Sebab Sakit</a></li><? } ?>
                          </ul>
                        </li>
                        <li><a><i class="fa fa-edit"></i>&nbsp;&nbsp; Import <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>import_pegawai/import_pegawai.php" >Import Pegawai</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_pasien/import_pasien.php" >Import Pasien</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_tarif_irj/import_tarif_irj.php" >Import Tarif</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_barang/import_barang_muslimat.php" >Import Obat</a></li>
      
                            <!--
                            <li><a href="<?php echo $ROOT; ?>import_pasien/import_pasien_excel.php" >Import Pasien</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_tarif_irj/import_tarif_irj_2014.php" >Import Tarif RJ</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_tarif_igd/import_tarif_igd2014.php" >Import Tarif IGD</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_tarif_irna/import_tarif_split_inap2014.php" >Import Tarif RI</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_kat_tindakan_header_instalasi/import_instalasi.php" >Imp.Kategori Tindakan Header Instalasi</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_kat_tindakan_header/import_header_kat2014.php" >Imp.Kategori Tindakan Header</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_kat_tindakan/import_kat_new2014.php" >Imp.Kategori Tindakan</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_perawatan/import_perawatan.php" >Import Perawatan</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_icd/import_icd.php" >Import ICD</a></li>
                            <li><a href="<?php echo $ROOT; ?>import/import_icd9/import_icd9.php" >Import ICD9</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_pegawai/import_pegawai.php" >Import Pegawai</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_barang/import_barang.php" >Import Barang</a></li>  -->
                          </ul>
                        </li>                   
                          <li><a><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Data <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>reset_pegawai/reset_pegawai.php" >Reset Pegawai</a></li> 
                            <li><a href="<?php echo $ROOT; ?>reset_pasien/reset_pasien.php" >Reset Pasien</a></li>
                            <li><a href="<?php echo $ROOT; ?>reset_tarif/reset_tarif.php" >Reset Tarif</a></li>  
                            <!--
                            <li><a href="<?php echo $ROOT; ?>reset_user/data_user_edit.php" >Reset Data User</a></li>                        
                            <li><a href="<?php echo $ROOT; ?>reset_pasien/reset_pasien.php " >Reset Pasien</a></li>                                         
                            <li><a href="<?php echo $ROOT; ?>reset_keuangan/data_keuangan_edit.php" >Reset Data Keuangan</a></li>
                            <li><a href="<?php echo $ROOT; ?>reset_master_keuangan/data_master_keuangan_edit.php" >Reset Master Keuangan</a></li>
                            <li><a href="<?php echo $ROOT; ?>reset_keperawatan/data_keperawatan_edit.php" >Reset Keperawatan</a></li>
                            <li><a href="<?php echo $ROOT; ?>reset_obat/data_obat_edit.php" >Reset Obat</a></li>  -->
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Manajemen -->
                  
                  <!-- Menu Administrasi -->
                  <li><a><i class="glyphicon glyphicon-phone"></i>&nbsp;&nbsp; ADMINISTRASI <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Admin <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <? if($auth->IsAllowed("administrator_admin_edit_transaksi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ar_history_all/ar_main.php"  style="font-size:15px">Edit Transaksi</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_admin_edit_antrian",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>data_log/data_log.php"  style="font-size:15px">Data Log</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_admin_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_password/ganti_password.php"  style="font-size:15px">Ganti Password</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_admin_user_login",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>user_login/hakakses_view.php"  style="font-size:15px">User Login</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_admin_user_login",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>import_pasien/dataconvert_pasien.php"  style="font-size:15px">Convert DB Flat</a></li><? } ?>
                          </ul>
                        </li>                  
                         <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                      
                            <? if($auth->IsAllowed("administrator_proses_status_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_status_irj/pasien_view.php"  style="font-size:15px">Status Pasien</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_batal_bayar_kwitansi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_bayar_kwitansi/batal_bayar_view.php"  style="font-size:15px">Batal Bayar Kwitansi</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_ganti_jenis_bayar",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>ganti_cara_bayar/ganti_cara_bayar.php"  style="font-size:15px">Ganti Jenis Bayar</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_batal_piutang",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_piutang/batal_piutang.php"  style="font-size:15px">Batal Piutang</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_batal_bayar_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>batal_bayar_irna/batal_bayar_inap_view.php"  style="font-size:15px">Batal Bayar Inap</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_edit_tanggal_inap",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>edit_tgl_inap/pasien_view.php"  style="font-size:15px">Edit Tanggal Inap</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_proses_kosongkan_bed",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>kosongkan_bed/penata_jasa_edit_view.php"  style="font-size:15px">Kosongkan Bed</a></li><? } ?>
                            <!--<? if($auth->IsAllowed("administrator_proses_penjualan_apotik_mundur",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>penjualan_mundur/penjualan_view.php"  style="font-size:15px">Penjualan Apotik Mundur</a></li><? } ?> -->
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("administrator_informasi_laporan_honor_dokter",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_detail_pemb/report_setoran_loket.php"  style="font-size:15px">Laporan Honor Dokter</a></li><? } ?>
                            <? if($auth->IsAllowed("administrator_informasi_laporan_remunerasi",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lap_remunerasi/remunerasi_view.php"  style="font-size:15px">Laporan Remunerasi</a></li><? } ?>
                          </ul>
                        </li>                   
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Antrian <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>konf_antrian/konfigurasi_antrian.php"  style="font-size:15px">Konfigurasi Antrian</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/antrian.php"  style="font-size:15px">LCD Antrian</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>pasien/antri_tambah.php"  style="font-size:15px">Tampilan Pasien</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>reset_antrian/reset_antrian.php"  style="font-size:15px">Reset Antrian</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_1.php"  style="font-size:15px">LCD 1</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_2.php"  style="font-size:15px">LCD 2</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_3.php"  style="font-size:15px">LCD 3</a></li><? } ?>                      
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_4.php"  style="font-size:15px">LCD 4</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_5.php"  style="font-size:15px">LCD 5</a></li><? } ?>
                            <? if($auth->IsAllowed("man_ganti_password",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>lcd/lcd_6.php"  style="font-size:15px">LCD 6</a></li><? } ?>
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Administrasi -->
                  
                  <!-- Menu Logistik -->
                  <li><a><i class="glyphicon glyphicon-briefcase"></i>&nbsp;&nbsp; LOGISTIK <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">  
                            <li><a href="<?php echo $ROOT; ?>konfigurasi_logistik/konfigurasi_edit.php"  style="font-size:15px">Konfigurasi</a></li> 
                            <li><a href="<?php echo $ROOT; ?>gudang_logistik/dep_view.php"  style="font-size:15px">Gudang</a></li> 
                            <li><a href="<?php echo $ROOT; ?>pengirim/pengirim_view.php"  style="font-size:15px">Pengirim</a></li> 
                            <li><a href="<?php echo $ROOT; ?>supplier/supplier_view.php"  style="font-size:15px">Supplier</a></li>                    
                            <li><a href="<?php echo $ROOT; ?>kat_barang/grup_item_view.php"  style="font-size:15px">Kat. Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>sat_barang/satuan_view.php"  style="font-size:15px">Satuan Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>petunjuk/petunjuk_view.php"  style="font-size:15px">Petunjuk Dosis</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang/item_view.php"  style="font-size:15px">Setup Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_gudang/item_gudang_view.php"  style="font-size:15px">Setup Barang per Gudang</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_generik/item_generik_view.php"  style="font-size:15px">Barang Generik</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_non_generik/item_generik_view.php"  style="font-size:15px">Barang Non Generik</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_fornas/item_fornas_view.php"  style="font-size:15px">Barang Fornas</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_non_fornas/item_non_fornas_view.php"  style="font-size:15px">Barang Non Fornas</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_katalog/item_katalog_view.php"  style="font-size:15px">Barang Katalog</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang_non_katalog/item_non_katalog_view.php"  style="font-size:15px">Barang Non Katalog</a></li>
                            <li><a href="<?php echo $ROOT; ?>barang/item_view.php"  style="font-size:15px">Setup Barang</a></li>
                            <li><a href="<?php echo $ROOT; ?>racikan/racikan_view.php"  style="font-size:15px">Maser Racikan</a></li>
                            <li><a href="<?php echo $ROOT; ?>paket_farmasi/role_view.php"  style="font-size:15px">Paket Farmasi</a></li>
                            <li><a href="<?php echo $ROOT; ?>narkotika/narkotika_view.php"  style="font-size:15px">Obat Narkotika</a></li>
                            <li><a href="<?php echo $ROOT; ?>psikotropika/psikotropika_view.php"  style="font-size:15px">Obat Psikotropika</a></li>
                            <!-- <li><a href="<?php echo $ROOT; ?>racikan/racikan_view.php"  style="font-size:15px">Obat Racikan</a></li> -->
                            <li><a href="<?php echo $ROOT; ?>periode/periode_view.php"  style="font-size:15px">Periode Opname</a></li>
                          </ul>
                        </li>                  
                         <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Penerimaan <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <li><a href="<?php echo $ROOT; ?>order_pembelian/trans_beli_po_view.php"  style="font-size:15px">Faktur Barang Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_penerimaan/report_penerimaan.php"  style="font-size:15px">Laporan Penerimaan</a></li>
                            <li><a href="<?php echo $ROOT; ?>edit_penerimaan_barang/trans_beli_po_terima_edit.php"  style="font-size:15px">Edit Penerimaan Barang</a></li>
                          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Distribusi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <li><a href="<?php echo $ROOT; ?>pengiriman/transfer_stok_view.php"  style="font-size:15px">Pengiriman Medis</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_permintaan/report_sbbk.php"  style="font-size:15px">Lap. Permintaan Medis</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_pengiriman/report_sbbk.php"  style="font-size:15px">Lap. Pengiriman Medis</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_kinerja_distribusi/report_sbbk.php"  style="font-size:15px">Lap. Kinerja Distribusi</a></li>
                          </ul>
                        </li> 
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Opname <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <li><a href="<?php echo $ROOT; ?>opname/trans_opname.php"  style="font-size:15px">Stok Opname</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_opname/opname.php"  style="font-size:15px">Lap. Stok Opname</a></li>
                            <li><a href="<?php echo $ROOT; ?>import_opname/import_opname.php"  style="font-size:15px">Import Stok Opname</a></li>
                          </ul>
                        </li>
                        
                        <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                          
                            <li><a href="<?php echo $ROOT; ?>lap_kartu_stok/histori_stok_gflk.php"  style="font-size:15px">Kartu Stok</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_semua_stok/laporan_stok_semua_gudang_gflk.php"  style="font-size:15px">Lap. Semua Stok</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_psikotropika/lap_psikotropika.php"  style="font-size:15px">Lap. Obat Psikotropika</a></li>
                            <li><a href="<?php echo $ROOT; ?>lap_narkotika/lap_narkotika.php"  style="font-size:15px">Lap. Obat Narkotika</a></li>
                          </ul>
                        </li>                   
                       <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Import Data <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">                     
                            <li><a href="<?php echo $ROOT; ?>import_barang/import_barang.php"  style="font-size:15px">Import Barang</a></li>
                          </ul>
                        </li>                   
                    </ul>
                  </li> 
                  <!-- END Menu Logistik -->
                  
                  <!--E Medical Record -->
                  <li><a><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp; E MEDICAL RECORD <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-th"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IRJ</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IRJ</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_igd/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa IGD</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_igd/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa IGD</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_anamnesa_irna/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Anamnesa Rawat Inap</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_anamnesa_irna/anamnesa_view.php" target="_blank" style="font-size:15px">Anamnesa Rawat Inap</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_header_asuhan_medis/header_anamnesa_view.php" target="_blank" style="font-size:15px">Header Asuhan Medis</a></li><? } ?>
                          <? if($auth->IsAllowed("man_medis_master_sebab_sakit",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_asuhan_medis/anamnesa_view.php" target="_blank" style="font-size:15px">Asuhan Medis Isi</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_lap_kunjungan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_pemeriksaan_view.php" target="_blank" style="font-size:15px">Master Triage Pemeriksaan</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_lap_pengunjung",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_kategori_view.php" target="_blank" style="font-size:15px">Master Triage Kategori</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_triage/triage_pilihan_view.php" target="_blank" style="font-size:15px">Master Triage Pilihan</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_gizi_view.php" target="_blank" style="font-size:15px">Master Skrining Gizi</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_skrining/skrining_jatuh_view.php" target="_blank" style="font-size:15px">Master Scrining Jatuh</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satuan_pakai/satuan_pakai_view.php" target="_blank" style="font-size:15px">Master Satuan Pakai</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_aturan_pakai/aturan_pakai_view.php" target="_blank" style="font-size:15px">Master Aturan Pakai</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_obat_medik/obat_medik_view.php" target="_blank" style="font-size:15px">Master Satuan Jadi</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_satuan_komposisi/satuan_komposisi_view.php" target="_blank" style="font-size:15px">Master Satuan Komposisi</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_jenis_racikan/jenis_racikan_view.php" target="_blank" style="font-size:15px">Master Jenis Racikan</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_lokasi_radiologi/lokasi_radiologi_view.php" target="_blank" style="font-size:15px">Master Lokasi Radiologi</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_konsul_yang_diinginkan/konsul_yang_diinginkan_view.php" target="_blank" style="font-size:15px">Master Konsul Yang Diinginkan</a></li><? } ?>
                          <? if($auth->IsAllowed("rm_informasi_rekam_medik_pasien",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>master_rumah_sakit/rumah_sakit_view.php" target="_blank" style="font-size:15px">Master Rumah Sakit</a></li><? } ?>
                        </ul>
                      </li>                  
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rawat Darurat <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>e_medical_record_igd/input_rm.php" target="_blank" style="font-size:15px">E-Medical Record IGD</a></li><? } ?>                      
                        </ul>
                      </li>                
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rawat Jalan <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <? //if($auth->IsAllowed("irj_proses_pemeriksaan_askep",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>keperawatan/input_rm.php" target="_blank" style="font-size:15px">Asuhan Keperawatan</a></li><? //} ?>
                          <? //if($auth->IsAllowed("irj_proses_pemeriksaan_dokter",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>e_medical_record/input_rm.php" target="_blank" style="font-size:15px">E-Medical Record</a></li><? //} ?>
                          <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>history/input_rm.php" target="_blank" style="font-size:15px">History</a></li><? } ?>
                        </ul>
                      </li>                
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rawat Inap <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <!-- <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>e_medical_record_igd/input_rm.php" target="_blank" style="font-size:15px">E-Medical Record IGD</a></li><? } ?>          -->             
                        </ul>
                      </li>               
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp; Rehab Medik <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <!-- <? if($auth->IsAllowed("irj_proses_pemeriksaan",PRIV_READ)) { ?><li><a href="<?php echo $ROOT; ?>e_medical_record_igd/input_rm.php" target="_blank" style="font-size:15px">E-Medical Record IGD</a></li><? } ?>          -->             
                        </ul>
                      </li>             
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Farmasi <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="<?php echo $ROOT;?>history_farmasi/planning_farmasi.php" target="_blank" style="font-size:15px">History Farmasi</a></li>
                        </ul>
                      </li>             
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-file"></i>&nbsp;&nbsp; Case Mix <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="<?php echo $ROOT;?>bpjs/tampilan_bpjs.php" target="_blank" style="font-size:15px">RJTL</a></li>
                        </ul>
                      </li>             
                      <li><a style="font-size:15px"><i class="glyphicon glyphicon-folder-open"></i>&nbsp;&nbsp; Rawat Intensif <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <!-- <li><a href="<?php echo $ROOT;?>history_farmasi/planning_farmasi.php" target="_blank" style="font-size:15px">History Farmasi</a></li> -->
                        </ul>
                      </li>             
                    </ul>
                  </li> 
                  <!-- END E Medical Record -->
                  
                  <!-- Menu Akuntansi -->
                  <li><a><i class="glyphicon glyphicon-usd"></i>&nbsp;&nbsp; AKUNTANSI <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                       <li><a style="font-size:15px"><i class="fa fa-wrench"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <li><a href="<?php echo $ROOT; ?>../production/konfigurasi_akuntansi/konfigurasi.php" target="_blank" style="font-size:15px">Konfigurasi</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/account_level_satu/account_level_satu_view_gl.php" target="_blank" style="font-size:15px">Perkiraan Lv 1</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/account/account_view_gl.php" target="_blank" style="font-size:15px">Perkiraan</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/periode/period.php" target="_blank" style="font-size:15px">Periode</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/biaya_prk/biaya_prk_view.php" target="_blank" style="font-size:15px">Setup Biaya Prk</a></li>
                          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="fa fa-gear"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <!--<li><a href="<?php echo $ROOT; ?>master_dokter_lab/dokter_view.php" target="_blank">Master Dokter</a></li> -->
                            <li><a href="<?php echo $ROOT; ?>../production/saldo_awal/saldo_awal_edit.php" target="_blank" style="font-size:15px">Saldo Awal</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/kas_masuk/kas_masuk.php" target="_blank" style="font-size:15px">Kas Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/kas_keluar/kas_keluar.php" target="_blank" style="font-size:15px">Kas Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/bank_masuk/bank_masuk.php" target="_blank" style="font-size:15px">Bank Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/bank_keluar/bank_keluar.php" target="_blank" style="font-size:15px">Bank Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/transaksi/cashier_transaction.php" target="_blank" style="font-size:15px">Transaksi Memorial</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/daftar_kas_masuk/daftar_kas_masuk.php" target="_blank" style="font-size:15px">Daftar Kas Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/daftar_kas_keluar/daftar_kas_keluar.php" target="_blank" style="font-size:15px">Daftar Kas Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/daftar_bank_masuk/daftar_bank_masuk.php" target="_blank" style="font-size:15px">Daftar Bank Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/daftar_bank_keluar/daftar_bank_keluar.php" target="_blank" style="font-size:15px">Daftar Bank Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/daftar_transaksi/cashier_transaction_list.php" target="_blank" style="font-size:15px">Daft. Trans. Memorial</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara/cashier_report_journal.php" target="_blank" style="font-size:15px">Jurnal Sementara</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_unbalance/cashier_report_journal_unbalance.php" target="_blank" style="font-size:15px">Jurnal Sementara Unbalance</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/buku_besar_sementara/report_buku_besar_sementara.php" target="_blank" style="font-size:15px">Buku Besar Sementara</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/neraca_sementara/report_neraca_sementara.php" target="_blank" style="font-size:15px">Neraca Percobaan</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/laba_rugi_sementara/report_laba_rugi_sementara.php" target="_blank" style="font-size:15px">Laba Rugi Sementara</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/posting_gl/posting_gl.php" target="_blank" style="font-size:15px">Posting GL</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/unposting_jurnal_sementara/lap_posting.php" target="_blank" style="font-size:15px">Unposting GL</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_rekening/journal_sementara_rekening.php" target="_blank" style="font-size:15px">Jurnal Sem. Rekening</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_penerimaan_kas/journal_sementara_penerimaan_kas.php" target="_blank" style="font-size:15px">Jurnal Sem. Pen. Kas</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_bank_keluar/journal_sementara_bank_keluar.php" target="_blank" style="font-size:15px">Jurnal Sem. Bank Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_hutang/journal_sementara_hutang.php" target="_blank" style="font-size:15px">Jurnal Sem. Hutang</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_persediaan/journal_sementara_persediaan.php" target="_blank" style="font-size:15px">Jurnal Sem. Persediaan</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_umum/journal_sementara_umum.php" target="_blank" style="font-size:15px">Jurnal Sem. Umum</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_sementara_pendapatan/jurnal_sementara_pendapatan.php" target="_blank" style="font-size:15px">Jurnal Sem. Pendapatan</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/neraca_sementara/report_neraca_sementara.php" target="_blank" style="font-size:15px">C a L K</a></li>
                          </ul>
                        </li>
                        <li><a style="font-size:15px"><i class="fa fa-table"></i>&nbsp;&nbsp; Laporan <span class="fa fa-chevron-down"></span></a>
                          <ul class="nav child_menu">
                            <!--<li><a href="<?php echo $ROOT; ?>master_dokter_lab/dokter_view.php" target="_blank">Master Dokter</a></li> -->
                            <li><a href="<?php echo $ROOT; ?>../production/lap_kas_masuk/lap_kas_masuk.php" target="_blank" style="font-size:15px">Lap. Kas Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/lap_kas_keluar/lap_kas_keluar.php" target="_blank" style="font-size:15px">Lap. Kas Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/lap_bank_masuk/lap_bank_masuk.php" target="_blank" style="font-size:15px">Lap. Bank Masuk</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/lap_bank_keluar/lap_bank_keluar.php" target="_blank" style="font-size:15px">Lap. Bank Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/lap_memorial/lap_memorial.php" target="_blank" style="font-size:15px">Lap. Memorial</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal/report_journal.php" target="_blank" style="font-size:15px">Jurnal</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/buku_besar/report_buku_besar.php" target="_blank" style="font-size:15px">Buku Besar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/neraca/report_neraca.php" target="_blank" style="font-size:15px">Neraca</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/laba_rugi/report_laba_rugi.php" target="_blank" style="font-size:15px">Laporan Laba Rugi</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/neraca/report_neraca.php" target="_blank" style="font-size:15px">Neraca Komperatif</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/laba_rugi/report_laba_rugi.php" target="_blank" style="font-size:15px">Laba Rugi Komperatif</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/ekuitas/report_ekuitas.php" target="_blank" style="font-size:15px">Perubahan Ekuitas</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/arus_kas/report_arus_kas.php" target="_blank" style="font-size:15px">Laporan Arus Kas</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_rekening/journal_rekening.php" target="_blank" style="font-size:15px">Jurnal Rekening</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_penerimaan_kas/journal_penerimaan_kas.php" target="_blank" style="font-size:15px">Jurnal Pen. Kas</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_bank_keluar/journal_bank_keluar.php" target="_blank" style="font-size:15px">Jurnal Bank Keluar</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_hutang/journal_hutang.php" target="_blank" style="font-size:15px">Jurnal Hutang</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_persediaan/journal_persediaan.php" target="_blank" style="font-size:15px">Jurnal Persediaan</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/jurnal_umum/journal_umum.php" target="_blank" style="font-size:15px">Jurnal Umum</a></li>
                            <li><a href="<?php echo $ROOT; ?>../production/lap_pendapatan/lap_akuntansi.php" target="_blank" style="font-size:15px">Lap. Pendapatan Harian</a></li>
                          </ul>
                        </li>                  
                    </ul>
                  </li> 
                  <!-- END Menu Akuntansi -->

                  <!--
                  <li><a><i class="glyphicon glyphicon-folder-open"></i>&nbsp;&nbsp; Database <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>backup_restore/backup/backup.php" >Back-up Database</a></li>
                      <li><a href="<?php echo $ROOT; ?>backup_restore/restore/restore_view.php" >Restore Database</a></li>
                    </ul>
                  </li> -->
                  <!--s
                   <li><a><i class="glyphicon glyphicon-fullscreen"></i>&nbsp&nbsp&nbsp&nbsp &nbsp Update <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>update_core/update_core.php" >Update</a></li>
                      <li><a href="<?php echo $ROOT; ?>update_plugin/update_plugin.php" >Plug In</a></li>
                    </ul>
                  </li> -->
                  <!--
                  <li><a style="font-size:15px"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Privilage <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>global_app/app_view.php"  style="font-size:15px">Global APP</a></li>
                      <li><a href="<?php echo $ROOT; ?>global_privilage/privilage_view.php"  style="font-size:15px">Global Privilage</a></li>
                    </ul>
                  </li>
                  -->
                  
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->

     
          </div>
        </div>