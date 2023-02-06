<?php
	 // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     //INISIALISASI LIBRARY
     $enc = new textEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");
     
     //$depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     //AUTHENTIKASI
     // if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
     //      die("access_denied");
     //      exit(1);
          
     // } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
     //      echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
     //      exit(1);
     // }
     $tgl = date('Y-m-d');
     
     $sql = "select count(cust_usr_id) as jumlah from global.global_customer_user 
            where cust_usr_id in (select id_cust_usr from klinik.klinik_registrasi where reg_tanggal = '$tgl' and reg_tipe_rawat != 'I')";
     $pengunjung = $dtaccess->Fetch($sql);

     $sql = "select count(cust_usr_id) as jumlah from global.global_customer_user 
            where cust_usr_id in (select id_cust_usr from klinik.klinik_registrasi where reg_tanggal = '$tgl' and reg_tipe_rawat != 'I' and reg_jenis_pasien = '2')";
     $pengunjung_umum = $dtaccess->Fetch($sql);

     $sql = "select count(cust_usr_id) as jumlah from global.global_customer_user 
            where cust_usr_id in (select id_cust_usr from klinik.klinik_registrasi where reg_tanggal = '$tgl' and reg_tipe_rawat != 'I' and reg_jenis_pasien != '2')";
     $pengunjung_jaminan = $dtaccess->Fetch($sql);

     $sql = "select count(cust_usr_id) as jumlah from global.global_customer_user 
            where cust_usr_id in (select id_cust_usr from klinik.klinik_registrasi where reg_tanggal = '$tgl' and reg_tipe_rawat != 'I' and reg_status != 'A0' and reg_status_pasien = 'L')";
     $pengunjung_lama = $dtaccess->Fetch($sql);

     $sql = "select count(cust_usr_id) as jumlah from global.global_customer_user 
            where cust_usr_id in (select id_cust_usr from klinik.klinik_registrasi where reg_tanggal = '$tgl' and reg_tipe_rawat != 'I' and reg_status != 'A0' and reg_status_pasien = 'B')";
     $pengunjung_baru = $dtaccess->Fetch($sql);

     $sql = "select avg(klinik_waktu_tunggu_durasi_detik) as waktu_tunggu from klinik.klinik_waktu_tunggu 
     where klinik_waktu_tunggu_when_create  >= '$tgl 00:00:00' and klinik_waktu_tunggu_when_create  <= '$tgl 23:59:59'";
     $waktu_tunggu = $dtaccess->Fetch($sql);

     $rata_menit = intval($waktu_tunggu['waktu_tunggu'])/60;

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' and poli_nama <> 'Laboratorium' 
     and a.reg_tanggal = '$tgl' and a.reg_jenis_pasien = '2' and a.reg_tipe_rawat != 'I' ";
     $dataJenisPasienUmum = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli  
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '5' and a.reg_tipe_rawat != 'I' ";
     $dataJenisPasienJKN = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
     poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '7' and a.reg_tipe_rawat != 'I' ";
     $dataJenisPasienIKS = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '20' and a.reg_tipe_rawat != 'I' ";
     $dataJenisPasienKaryawan = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '2' and a.reg_tipe_rawat = 'I' ";
     $dataJenisPasienUmumI = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '5' and a.reg_tipe_rawat = 'I' ";
     $dataJenisPasienJKNI = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '7' and a.reg_tipe_rawat = 'I' ";
     $dataJenisPasienIKSI = $dtaccess->Fetch($sql);

     $sql = "select count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and a.reg_jenis_pasien = '20' and a.reg_tipe_rawat = 'I' ";
     $dataJenisPasienKaryawanI = $dtaccess->Fetch($sql);

     $sql = "select poli_nama, count(a.reg_id) as total from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' and a.reg_tanggal = '$tgl' 
     group by poli_nama order by total desc";
     $dataKunjunganPoli = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and reg_tipe_rawat != 'I' ";
     $dataPasienKunjunganPoli = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_status_pasien = 'L' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliLama = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_status_pasien = 'B' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliBaru = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_jenis_pasien = '2' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliUmum = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_jenis_pasien = '7' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliJaminan = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_jenis_pasien = '20' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliKaryawan = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli
      left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
      where a.reg_batal is null and poli_nama not ilike '%Apotek%' and 
      poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl'
      and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
      and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_jenis_pasien = '5' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPoliBPJS = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal = '$tgl' 
     and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
     and cust_usr_kode<>'500' and cust_usr_kode<>'100' and reg_tipe_rawat != 'I'";
     $dataPasienKunjunganPolii = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl' 
     and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
     and cust_usr_kode<>'500' and cust_usr_kode<>'100'";
     $dataPasienKunjunganPoliii = $dtaccess->FetchAll($sql);

     $sql = "select count(a.reg_id) as total 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$tgl' and a.reg_tanggal <= '$tgl' 
     and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
     and cust_usr_kode<>'500' and cust_usr_kode<>'100'";
     $dataPasienKunjunganPoliiii = $dtaccess->FetchAll($sql);

     $day = date('w');
     $week_start = date('Y-m-d', strtotime('-'.($day-1).' days'));
     $week_end = date('Y-m-d', strtotime('+'.(7-$day).' days'));

     $sql = "select count(a.reg_id) as total, a.reg_tanggal 
     from klinik.klinik_registrasi a 
     left join global.global_auth_poli b on b.poli_id = a.id_poli 
     left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
     where a.reg_batal is null and poli_nama not ilike '%Apotek%' 
     and poli_nama <> 'Laboratorium' and a.reg_tanggal >= '$week_start' and a.reg_tanggal <= '$week_end'
     and (a.reg_utama is null or a.reg_utama ='' or a.id_poli = a.id_poli_asal) 
     and cust_usr_kode<>'500' and cust_usr_kode<>'100' and reg_tipe_rawat != 'I'
     group by a.reg_tanggal 
     order by a.reg_tanggal ";
     $dataRekapMinggu = $dtaccess->FetchAll($sql);

     for ($i=0; $i < count($dataPasienKunjunganPolii); $i++) { 
      $PasienSekarang += $dataPasienKunjunganPolii[$i]['total'];
    }
    for ($i=0; $i < count($dataPasienKunjunganPoliii); $i++) { 
      $PasienLalu += $dataPasienKunjunganPoliii[$i]['total'];
    }
    for ($i=0; $i < count($dataPasienKunjunganPoliiii); $i++) { 
      $PasienLaluLalu += $dataPasienKunjunganPoliiii[$i]['total'];
    }

     $dataPoints = array( 
      array("labell"=>$dataJenisPasienUmum['jenis_nama'], "y"=>$dataJenisPasienUmum['total']),
      array("labell"=>$dataJenisPasienJKN['jenis_nama'], "y"=>$dataJenisPasienJKN['total']),
      array("labell"=>$dataJenisPasienIKS['jenis_nama'], "y"=>$dataJenisPasienIKS['total']),
      array("labell"=>$dataJenisPasienKaryawan['jenis_nama'], "y"=>$dataJenisPasienKaryawan['total'])
    );

    $dataPoints2 = array( 
      array("labell"=>$dataJenisPasienUmumI['jenis_nama'], "y"=>$dataJenisPasienUmumI['total']),
      array("labell"=>$dataJenisPasienJKNI['jenis_nama'], "y"=>$dataJenisPasienJKNI['total']),
      array("labell"=>$dataJenisPasienIKSI['jenis_nama'], "y"=>$dataJenisPasienIKSI['total']),
      array("labell"=>$dataJenisPasienKaryawanI['jenis_nama'], "y"=>$dataJenisPasienKaryawanI['total'])
    );

    $RekapPasien = array();
    for($i = 0; $i < count($dataRekapMinggu); $i++){
      $RekapPasien[] = array("label"=>date_format(date_create($dataRekapMinggu[$i]['reg_tanggal']), 'd-m-Y'), "y"=>intval($dataRekapMinggu[$i]['total']));
    }

    
    

     $tableHeader = "Menu Utama"; 
?>
<!DOCTYPE html>
<html>
  <?php require_once($LAY."header.php") ?>
  <script>
window.onload = function() {
 
 
var chart = new CanvasJS.Chart("chartContainer", {
  theme: "dark",
  animationEnabled: true,
  data: [{
    type: "doughnut",
    yValueFormatString: "#,##0\"\"",
    showInLegend: true,
    //legendText: "{label} : {y}",
    dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
  }]
});
chart.render();

var chart2 = new CanvasJS.Chart("chartContainer2", {
  theme: "dark",
  animationEnabled: true,
  data: [{
    type: "doughnut",
    yValueFormatString: "#,##0\"\"",
    showInLegend: true,
    //legendText: "{label} : {y}",
    dataPoints: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
  }]
});
chart2.render();

var chart3 = new CanvasJS.Chart("LineChart", {
  animationEnabled: true,
  theme: "light2",
  axisX: {
    valueFormatString: "dd-mm-yyyy"
  },
  axisY: {
    title: "Jumlah Pasien",
    maximum: 250
  },
  data: [{
    type: "splineArea",
    color: "#6599FF",
    xValueType: "dateTime",
    xValueFormatString: "dd-mm-yyyy",
    yValueFormatString: "#,##0 Pasien",
    dataPoints: <?php echo json_encode($RekapPasien); ?>
  }]
});
 
chart3.render();
 
}

</script>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <!-- $LAY" -->        
		  <?php require_once($LAY."sidebar.php") ?>
		<!-- //sidebar -->
        <!-- top navigation -->
		 <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

		
    <!-- == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI -->
    <!-- == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI == KONTEN DISINI -->
        <!-- page content -->
        <div class="right_col" role="main">
          <!-- top tiles -->
          <div class="row tile_count">
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Pengunjung Hari Ini</span>
              <div class="count green"><?=$PasienSekarang?></div>
              <!-- <span class="count_bottom"><i class="green">4% </i> Dari Minggu Lalu</span> -->
            </div>
            <!-- <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-clock-o"></i> Waktu Tunggu</span>
              <div class="count"><?=number_format($rata_menit, 2, '.', ',')?></div>
               <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>3% </i> Dari Minggu Lalu</span>
            </div> -->
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pengunjung Lama</span>
              <div class="count"><?=$dataPasienKunjunganPoliLama[0]['total']?></div>
              <!-- <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> Dari Minggu Lalu</span> -->
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pengunjung Baru</span>
              <div class="count"><?=$dataPasienKunjunganPoliBaru[0]['total']?></div>
              <!-- <span class="count_bottom"><i class="red"><i class="fa fa-sort-desc"></i>12% </i> Dari Minggu Lalu</span> -->
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pasien Umum</span>
              <div class="count"><?=$dataPasienKunjunganPoliUmum[0]['total']?></div>
              <!-- <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> Dari Minggu Lalu</span> -->
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pasien BPJS</span>
              <div class="count"><?=$dataPasienKunjunganPoliBPJS[0]['total']?></div>
              <!-- <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> Dari Minggu Lalu</span> -->
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pasien Asuransi</span>
              <div class="count"><?=$dataPasienKunjunganPoliJaminan[0]['total']?></div>
              <!-- <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> Dari Minggu Lalu</span> -->
            </div>
            <!-- <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Pasien Karyawan</span>
              <div class="count"><?=$dataPasienKunjunganPoliKaryawan[0]['total']?></div>
              <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> Dari Minggu Lalu</span>
            </div> -->
          </div>
          <!-- /top tiles -->

          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="dashboard_graph">

                <div class="row x_title">
                  <div class="col-md-6">
                    <h3>Rekap Kunjungan Pasien</h3>
                  </div>
                  <div class="col-md-6">

                    <!-- <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                      <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                      <span>October 10, 2017 - October 19, 2017</span> <b class="caret"></b>
                    </div> -->

                  </div>

                </div>

                <div class="col-md-9 col-sm-9 col-xs-12">
                <div id="LineChart" style="height: 370px; width: 100%;"></div>
                <script src="../assets/script/canvasjs.min.js"></script>
                </div>
                <div class="col-md-3 col-sm-3 col-xs-12 bg-white">
                  <div class="x_title">
                    <h2> Kunjungan Poli</h2>
                    <div class="clearfix"></div>
                  </div>

                  <?php
                    
                  for ($i=0; $i < count($dataKunjunganPoli); $i++) { 

                    $totalPasien += $dataPasienKunjunganPoli[$i]['total'];
                    $totalPasienPerPoli = ($dataKunjunganPoli[$i]['total'] != 0 || $dataKunjunganPoli[$i]['total'] != null) ? ($dataKunjunganPoli[$i]['total']/$totalPasien)*100 : 0;
                    $PasienPoliFix = substr($totalPasienPerPoli, 0,2);
                    //echo $PasienPoliFix;
                  ?>
                  
                  <div class="col-md-12 col-sm-12 col-xs-6">
                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span><?php echo $dataKunjunganPoli[$i]['poli_nama'] ?></span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="<?= $PasienPoliFix; ?>" aria-valuenow="<?php echo $PasienPoliFix; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 66%;">
                          <span class="sr-only"><?php echo $PasienPoliFix; ?></span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span><?php echo $dataKunjunganPoli[$i]['total']; ?></span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  </div>
                  
                  <?php } ?>

                </div>

                <div class="clearfix"></div>
              </div>
            </div>

          </div>
          <br />

          <div class="row">


            <!--<div class="col-md-4 col-sm-4 col-xs-12">
              <div class="x_panel tile fixed_height_320">
                <div class="x_title">
                  <h2>Pendapatan per poli</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                      <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Settings 1</a>
                        </li>
                        <li><a href="#">Settings 2</a>
                        </li>
                      </ul>
                    </li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                  </ul>
                  <div class="clearfix"></div>
                </div>

                 <div class="x_content">
                  <h4>5 Besar Total Pendapatan Poli</h4>
                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span>Anak</span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 66%;">
                          <span class="sr-only">60% Complete</span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span>65M</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>

                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span>Penyakit Dalam</span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 45%;">
                          <span class="sr-only">60% Complete</span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span>50M</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span>Syaraf</span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 25%;">
                          <span class="sr-only">60% Complete</span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span>40M</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span>Mata</span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 5%;">
                          <span class="sr-only">60% Complete</span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span>29M</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  <div class="widget_summary">
                    <div class="w_left w_25">
                      <span>Ortopedi</span>
                    </div>
                    <div class="w_center w_55">
                      <div class="progress">
                        <div class="progress-bar bg-green" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 2%;">
                          <span class="sr-only">60% Complete</span>
                        </div>
                      </div>
                    </div>
                    <div class="w_right w_20">
                      <span>11M</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>

                </div>
              </div>
            </div> -->

            <div class="col-md-4 col-sm-4 col-xs-12">
              <div class="x_panel tile fixed_height_320 overflow_hidden">
                <div class="x_title">
                  <h2>Kunjungan per Jenis Pasien | Poli</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                      <!-- <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Settings 1</a>
                        </li>
                        <li><a href="#">Settings 2</a>
                        </li>
                      </ul> -->
                    </li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                  </ul>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <table class="" style="width:100%">
                    <tr>
                      <th style="width:37%;">
                        <p>Jenis Pasien</p>
                      </th>
                      <th>
                        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7">
                          <p class="">Jenis Pasien</p>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                          <p class="">Prosentase</p>
                        </div>
                      </th>
                    </tr>
                    <tr>
                      <td>
                      <div id="chartContainer" style="height: 260px; width: 100%;"></div>
                        <script src="../assets/script/canvasjs.min.js"></script>
                      </td>
                      <td>
                        <table class="tile_info">
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #4F81BC"></i>Umum</p>
                            </td>
                            <td><?php echo $dataJenisPasienUmum['total']; ?></td>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #C0504E"></i>JKN / KIS</p>
                            </td>
                            <td><?php echo $dataJenisPasienJKN['total']; ?></td>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #9BBB58"></i>Asuransi</p>
                            </td>
                            <?php if ($dataJenisPasienIKS['total'] != ''){ ?>
                              <td><?php echo $dataJenisPasienIKS['total']; ?></td>
                            <?php } else { ?>
                              <td>0</td>
                            <?php } ?>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square aero"></i>Karyawan</p>
                            </td>
                            <?php if ($dataJenisPasienKaryawan['total'] != ''){ ?>
                              <td><?php echo $dataJenisPasienKaryawan['total']; ?></td>
                            <?php } else { ?>
                              <td>0</td>
                            <?php } ?>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-md-4 col-sm-4 col-xs-12">
              <div class="x_panel tile fixed_height_320 overflow_hidden">
                <div class="x_title">
                  <h2>Kunjungan per Jenis Pasien | Rawat Inap</h2>
                  <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </li>
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                      <!-- <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Settings 1</a>
                        </li>
                        <li><a href="#">Settings 2</a>
                        </li>
                      </ul> -->
                    </li>
                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                    </li>
                  </ul>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <table class="" style="width:100%">
                    <tr>
                      <th style="width:37%;">
                        <p>Jenis Pasien</p>
                      </th>
                      <th>
                        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7">
                          <p class="">Jenis Pasien</p>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">
                          <p class="">Prosentase</p>
                        </div>
                      </th>
                    </tr>
                    <tr>
                      <td>
                      <div id="chartContainer2" style="height: 260px; width: 100%;"></div>
                        <script src="../assets/script/canvasjs.min.js"></script>
                      </td>
                      <td>
                        <table class="tile_info">
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #4F81BC"></i>Umum</p>
                            </td>
                            <td><?php echo $dataJenisPasienUmumI['total']; ?></td>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #C0504E"></i>JKN / KIS</p>
                            </td>
                            <td><?php echo $dataJenisPasienJKNI['total']; ?></td>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square" style="color: #9BBB58"></i>Asuransi</p>
                            </td>
                            <?php if ($dataJenisPasienIKSI['total'] != ''){ ?>
                              <td><?php echo $dataJenisPasienIKSI['total']; ?></td>
                            <?php } else { ?>
                              <td>0</td>
                            <?php } ?>
                          </tr>
                          <tr>
                            <td>
                              <p><i class="fa fa-square aero"></i>Karyawan</p>
                            </td>
                            <?php if ($dataJenisPasienKaryawanI['total'] != ''){ ?>
                              <td><?php echo $dataJenisPasienKaryawanI['total']; ?></td>
                            <?php } else { ?>
                              <td>0</td>
                            <?php } ?>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

          </div>

          <div class="row">
          </div>
        </div>
        <!-- /page content -->
		
    <!-- // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN -->
    <!-- // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN // == BATAS KONTEN -->
        <!-- footer content -->
       <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
<?php require_once($LAY."js.php") ?>
  </body>
</html>
