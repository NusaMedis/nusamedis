<?php
     require_once("lib/tampilan.php");
	 if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
	 
?>
<?php
	
  require_once('lib/includes/tile.class.php');
  require_once('lib/includes/ui.class.php');
  require_once("penghubung.inc.php");
  require_once($ROOT."lib/login.php");  
  require_once($ROOT."lib/datamodel.php");
  require_once($ROOT."lib/conf/database.php");
  require_once($ROOT."lib/conf/db_depan.php");
  $auth = new CAuth();   
  $enc = new textEncrypt();   
  $userName = $auth->GetUserName();
  $depId = $auth->GetDepId();
  $depNama = $auth->GetDepNama();                                                 
  $poliId = $auth->IdPoli();
  $logoKlinik = $auth->GetLogoKlinik();
  $logoAplKiri = $auth->GetLogoAplikasiKiri();  
  
  $host="localhost";
  $user=$enc->Decode(DB_USER);
  $password=$enc->Decode(DB_PASSWORD);
  $port="5432";
  $dbname = DB_DEPAN;
  
  //ambil title Software dari Konfigurasi
  $link = pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
     
  $sql = pg_query($link, "select dep_title from global.global_departemen");
  $dataTitle = pg_fetch_assoc($sql);
    
  $ui = new UI();

  $ui->setTitle($dataTitle["dep_title"]);
  $ui->setUser($userName);

  $tiles = array(
        array(
            'name'        => 'tile000',
            'thumbnail'   => 'gambar/icon_menu_depan/layanan.png',  
            'content'     => 'LOKET',           
            'url'         => './production/loket/index.php', 
            'size'        => '2x2',
            'theme'       => 'orange',
            'link'        => './production/loket/index.php'
        ),
        array(
            'name'        => 'tile001',
            'thumbnail'   => 'gambar/icon_menu_depan/rawat_jalan.png',
            'content'     => 'RAWAT JALAN',  
            'url'         => './production/rawat_jalan/index.php', 
            'size'        => '2x2',
            'theme'       => 'green',
            'link'        => './production/rawat_jalan/index.php'
        ),
        
        array(
            'name'        => 'typography',
            'thumbnail'   => 'gambar/icon_menu_depan/ugd.png',  
            'content'     => 'IGD',       
            'url'         => './production/igd/index.php',
            'size'        => '2x2',                                 
            'theme'       => 'darkred',
            'link'        => './production/igd/index.php'
        ),
        array(
            'name'        => 'tile002',
            'thumbnail'   => 'gambar/icon_menu_depan/rawat_inap.png',
            'content'     => 'RAWAT INAP',
            'url'         => './production/rawat_inap/index.php',
            'size'        => '2x2',
            'theme'       => 'darkblue',
            'link'        => './production/rawat_inap/index.php'
        ),
        array(
            'name'        => 'tile003',
            'thumbnail'   => 'gambar/icon_menu_depan/rawat_jalan.png',  
            'content'     => 'REHAB MEDIK',
            'url'         => './production/rehab_medik/index.php',
            'size'        => '2x2',
            'theme'       => 'blue',
            'link'        => './production/rehab_medik/index.php'
        ),                                          
        array(
            'name'        => '',
            'thumbnail'   => 'gambar/icon_menu_depan/widget_chart.png',
            'content'     => 'REKAM MEDIK',
            'url'         => './production/rekam_medik/index.php',
            'size'        => '2x2',
            'theme'       => 'orange',
            'link'        => './production/rekam_medik/index.php'
        ),
        array(
            'name'        => 'tile007',
            'thumbnail'   => 'gambar/icon_menu_depan/operasi.png',
            'content'     => 'OPERASI',
            'url'         => './production/operasi/index.php',
            'size'        => '2x2',
            'theme'       => 'blue',
            'link'        => './production/operasi/index.php'
        ),
        array(
            'name'        => 'tile008',
            'thumbnail'   => 'gambar/icon_menu_depan/widget_file.png',
            'content'     => 'PENUNJANG',
            'url'         => './production/penunjang/index.php',
            'size'        => '2x2',
            'theme'       => 'purple',
            'link'        => './production/penunjang/index.php'
        ),
        array(
            'name'        => 'tile009',
            'thumbnail'   => 'gambar/icon_menu_depan/laboratorium.png',
            'content'     => 'LABORATORIUM',
            'url'         => './production/lab/index.php',
            'size'        => '2x2',
            'theme'       => 'darkblue',
            'link'        => './production/lab/index.php'
        ),
        array(
            'name'        => 'tile010',
            'thumbnail'   => 'gambar/icon_menu_depan/radiologi.png',
            'content'     => 'RADIOLOGI',
            'url'         => './production/radiologi/index.php',
            'size'        => '2x2',
            'theme'       => 'green',
            'link'        => './production/radiologi/index.php',
        ),
        array(
            'name'        => 'typography',
            'thumbnail'   => 'gambar/icon_menu_depan/icu.png',
            'content'     => 'Instalasi Pemulasaran Jenasah',
            'url'         => './production/ipj/index.php',
            'size'        => '4x2',
            'theme'       => 'red',
            'link'        => './production/ipj/index.php'
        ),
        array(
            'name'        => 'tile006',
            'thumbnail'   => 'gambar/icon_menu_depan/apotik.png',
            'content'     => 'APOTIK',
            'url'         => './production/apotik/index.php',
            'size'        => '2x2',
            'theme'       => 'green',
            'link'        => './production/apotik/index.php'
        ),
        array(
            'name'        => 'typography',
            'thumbnail'   => 'gambar/icon_menu_depan/rawat_inap.png',
            'content'     => 'RAWAT INTENSIF',
            'url'         => './production/rawat_intensif/index.php',
            'size'        => '2x2',
            'theme'       => 'yellow',
            'link'        => './production/rawat_intensif/index.php'
        ),
        array(
            'name'        => 'typography',
            'thumbnail'   => 'gambar/icon_menu_depan/loket.png',
            'content'     => 'EPIDEMOLOGI',
            'url'         => './production/epidemologi/index.php',
            'size'        => '2x2',
            'theme'       => 'darkred',
            'link'        => './production/epidemologi/index.php'
        ),
        array(
            'name'        => 'tile003',
            'thumbnail'   => 'gambar/icon_menu_depan/widget_chart.png',
            'content'     => 'BPJS',
            'url'         => './production/bpjs/index.php',
            'size'        => '2x2',
            'theme'       => 'blue',
            'link'        => './production/bpjs/index.php'
        ),
		array(
            'name'        => '',
            'thumbnail'   => 'gambar/icon_menu_depan/layanan.png',
            'content'     => 'Medical Check Up',
            'url'         => './production/mcu/index.php',
            'size'        => '2x2',
            'theme'       => 'green',
            'link'        => './production/mcu/index.php'
        ),
        array(
            'name'        => 'tile003',
            'thumbnail'   => 'gambar/icon_menu_depan/kasir.png',
            'content'     => 'KASIR',
            'url'         => './production/kasir/index.php',
            'size'        => '2x2',
            'theme'       => 'orange',
            'link'        => './production/kasir/index.php'
        ),
		); 
        

    function makeRandomTile($id, $size = '2x2') {
        $tile = array(
            'name'        => 'widget_000' . $id,
            'thumbnail'   => '',
            'content'     => '',
            'url'         => 'tiles/blank.php',
            'size'        => $size,
            'theme'       => 'grey',
            'link'        => ''
        );

        return $tile;
    }

    
    $manajemen = array(
        'name'        => 'Manajemen',
        'thumbnail'   => 'gambar/icon_menu_depan/widget_chart.png',
        'content'     => 'Manajemen',
        'url'         => './production/index.php',
        'size'        => '2x2',
        'theme'       => 'red',
        'link'        => './production/index.php'
    );
    
    $administrasi = array(
        'name'        => 'Administrasi',
        'thumbnail'   => 'gambar/icon_menu_depan/layanan.png',
        'content'     => 'Administrasi',
        'url'         => './production/sirs/index.php',
        'size'        => '2x2',
        'theme'       => 'darkblue',
        'link'        => './production/sirs/index.php'
    );
    
    $informasi = array(
            'name'        => 'typography',
            'thumbnail'   => 'gambar/icon_menu_depan/loket.png',
            'content'     => 'INFORMASI',
            'url'         => './production/informasi/index.php',
            'size'        => '2x2',
            'theme'       => 'green',
            'link'        => './production/informasi/index.php'
        );
        
    $remunerasi = array(
            'name'        => 'tile011',
            'thumbnail'   => 'gambar/icon_menu_depan/widget_chart.png',
            'content'     => 'REMUNERASI',
            'url'         => './production/remunerasi/index.php',
            'size'        => '2x2',
            'theme'       => 'purple',
            'link'        => './production/remunerasi/index.php'
        );
    $mobilisasi_dana = array(
            'name'        => 'tile011',
            'thumbnail'   => 'gambar/icon_menu_depan/widget_chart.png',
            'content'     => 'MOBILISASI DANA',
            'url'         => './production/mobilisasi_dana/index.php',
            'size'        => '2x2',
            'theme'       => 'yellow',
            'link'        => './production/mobilisasi_dana/index.php'
        );
    $logistik_medik = array(
            'name'        => 'tile011',
            'thumbnail'   => 'gambar/icon_menu_depan/apotik.png',
            'content'     => 'LOGISTIK MEDIK',
            'url'         => './production/logistik/index.php',
            'size'        => '2x2',
            'theme'       => 'blue',
            'link'        => './production/logistik/index.php'
        );


    $tile_container1 = array(
        'size'  => 'full',
        'theme' => '',
        'tiles' => $tiles
    );
    $tile_container2 = array(
        'size'  => 'half',
        'theme' => '',                                                                              
        'tiles' => array($manajemen,$administrasi,$informasi,$remunerasi,$mobilisasi_dana,$logistik_medik)
    );
    $ui->addTileContainer($tile_container1);
    $ui->addTileContainer($tile_container2);

    $ui->printHeader();
    $ui->printNav();
    $ui->printTiles();
    $ui->printFooter();
?>