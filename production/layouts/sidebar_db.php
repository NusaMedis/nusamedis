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

$sql = "select * from global.global_auth_menu where menu_level = '1' order by menu_urut, menu_level asc"; 
$menuAll = $dtaccess->FetchAll($sql);

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
          <?php 
            foreach ($menuAll as $key => $value) {     
              $sql = "select * from global.global_auth_menu where menu_parent = ".QuoteValue(DPE_CHAR, $value['menu_id']);
              $CheckLevel2 = $dtaccess->Fetch($sql);

              if ($CheckLevel2) { // Jika ada Sub Menu Level 2
                $sql = "select * from global.global_auth_menu where menu_parent = ".QuoteValue(DPE_CHAR, $value['menu_id'])." order by menu_urut asc";
                $ListLevel2 = $dtaccess->FetchAll($sql);
          ?>
            <li>
              <a><i class="<?php echo $value['menu_icon'] ?>"></i>&nbsp;&nbsp; <?php echo $value['menu_nama'] ?> <span class="fa fa-chevron-down"></span></a>
              <ul class="nav child_menu">
                <?php foreach ($ListLevel2 as $key => $value) { ?>
                  <li>
                    <?php 
                      $sql = "select * from global.global_auth_menu where menu_parent = ".QuoteValue(DPE_CHAR, $value['menu_id']);
                      $CheckLevel3 = $dtaccess->Fetch($sql);

                      if ($CheckLevel3) { // Jika ada Sub Menu Level 2
                        $sql = "select * from global.global_auth_menu where menu_parent = ".QuoteValue(DPE_CHAR, $value['menu_id'])." order by menu_urut asc";
                        $ListLevel3 = $dtaccess->FetchAll($sql);
                    ?>
                    <a target="_blank" style="font-size:15px"><i class="<?php echo $value['menu_icon'] ?>"></i>&nbsp;&nbsp; <?php echo $value['menu_nama'] ?> <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <?php foreach ($ListLevel3 as $key => $value) { ?>
                        <li>
                          <a href="<?php echo $ROOT.$value['menu_link'] ?>" style="font-size:15px"><i class="<?php echo $value['menu_icon'] ?>"></i>&nbsp;&nbsp; <?php echo $value['menu_nama'] ?></a>
                        </li>
                      <?php } ?>
                    </ul>
                    <?php } else { ?>
                      <li>
                      <a href="<?php echo $ROOT.$value['menu_link'] ?>" style="font-size:15px"><i class="<?php echo $value['menu_icon'] ?>"></i>&nbsp;&nbsp; <?php echo $value['menu_nama'] ?> </a>
                    <?php } ?>
                  </li>
                <?php } ?>
              </ul>
              <?php } else { ?>
                <li>
                <a><i class="<?php echo $value['menu_icon'] ?>"></i>&nbsp;&nbsp; <?php echo $value['menu_nama'] ?> </a>
              <?php }  ?>
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <!-- /sidebar menu -->
  </div>
</div>