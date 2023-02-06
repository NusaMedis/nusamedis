		<?php 
        require_once("../penghubung.inc.php"); 
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
                <h3>BIOS</h3>
                <ul class="nav side-menu">  
                   <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Master <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">                     
                      <li><a href="<?php echo $ROOT; ?>bios_master_akun/akun_view.php" target="_blank" style="font-size:15px">Master Akun</a></li>
                      <li><a href="<?php echo $ROOT; ?>bios_master_bank/bank_view.php" target="_blank" style="font-size:15px">Master Bank</a></li>
                      
                    </ul>
                  </li>
                   <li><a style="font-size:15px"><i class="glyphicon glyphicon-bed"></i>&nbsp;&nbsp; Proses <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">                     
                      <li><a href="<?php echo $ROOT; ?>bios_transaksi/transaksi_view.php" target="_blank" style="font-size:15px">Transaksi BIOS</a></li>
                    </ul>
                  </li> 
                  
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->


          </div>
        </div>