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
                <img src="<?php echo $ROOT ?>gambar/logo-rspiss.png" alt="..." class="img-circle profile_img">
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

<!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>Penunjang</h3>
                <ul class="nav side-menu">           
                                    
                   <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">                      
                      <li><a href="<?php echo $ROOT; ?>pemeriksaan_rehab_medik/pemeriksaan_rehab_medik_view.php" target="_blank" style="font-size:15px">Pemeriksaan</a></li>
                       <li><a href="<?php echo $ROOT; ?>tracer_status/terima_berkas.php" target="_blank" style="font-size:15px">Terima Status</a></li>
                        <li><a href="<?php echo $ROOT; ?>tracer_status/kirim_rm.php" target="_blank" style="font-size:15px">Kembalikan Status</a></li>
                    </ul>
                  </li> 
                  <li><a style="font-size:15px"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp; Informasi <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="<?php echo $ROOT; ?>lap_kunjungan_penunjang/lap_kunjungan.php" target="_blank" style="font-size:15px">Laporan Kunjungan</a></li>
                      <li><a href="<?php echo $ROOT; ?>lap_tindakan_penunjang/lap_tindakan.php" target="_blank" style="font-size:15px">Laporan Tindakan</a></li>
                      <li><a href="<?php echo $ROOT; ?>lap_waktu_tunggu_penunjang/lap_waktu_tunggu.php" target="_blank" style="font-size:15px">Laporan Waktu Tunggu</a></li>
                      <li><a href="<?php echo $ROOT; ?>lap_status_pasien_penunjang/lap_status_pasien_penunjang.php" target="_blank" style="font-size:15px">Laporan Status Pasien</a></li>
                      <li><a href="<?php echo $ROOT; ?>rekap_bulanan_penunjang/rekap_bulanan_penunjang.php" target="_blank" style="font-size:15px">Rekap Bulanan Penunjang </a></li>
                    </ul>
                  </li>                   
                 
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->


          </div>
        </div>