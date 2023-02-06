<?php
     // LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."tampilan.php");

$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();  
$auth = new CAuth();
$table = new InoTable("table","100%","left");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$userData = $auth->GetUserData();
$userId = $auth->GetUserId();
$thisPage = "report_pasien.php";
$poliId = $auth->IdPoli();

if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
  echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
  exit(1);
} 

 /*    
    if(!$auth->IsAllowed("rm_info_lap_kunjungan_irj",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("rm_info_lap_kunjungan_irj",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 

*/     
    // $_POST["klinik"]=$depId;

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else  $_POST["klinik"] = $_POST["klinik"];
     
 	   // KONFIGURASI
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];

     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
       $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
       $_POST['tgl_akhir']  = $skr;
     }
     
     //cari shift
     $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);

     if($_POST["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     //untuk mencari tanggal
     //if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     if($_POST["reg_shift"]){
      $sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
    }

	/*if($userId<>'b9ead727d46bc226f23a7c1666c2d9fb' or $userId<>'92df81c2bebf2f93f75d9ad1014fe930'){
		$sql_where[] = " a.reg_who_update =".QuoteValue(DPE_CHAR,$userName);
  }*/

  if($_POST["cust_usr_nama"]){
    $sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
  }

  if($_POST["cust_usr_kode"]){
    $sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
  }

  if($_POST["cust_usr_alamat"]){
    $sql_where[] = " b.cust_usr_alamat = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
  }

  if($_POST["reg_jenis_pasien"]){
    $sql_where[] = " a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_jenis_pasien"]);
  }

  if($_POST["reg_tipe_layanan"]){
    $sql_where[] = " a.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
  }

  if($_POST["id_perusahaan"]){
    $sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
  }

  if($_POST["cust_usr_jkn"]){
    $sql_where[] = " b.reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]);
  }

  if($_POST["id_jamkesda_kota"]){
    $sql_where[] = " a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
  }

  if($_POST["reg_status_pasien"]){
    $sql_where[] = " a.reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
  }

  if($_POST["kondisi_akhir"]){
    $sql_where[] = " a.reg_status_kondisi = ".QuoteValue(DPE_CHAR,$_POST["kondisi_akhir"]);
  }

  if($_POST["id_lokasi_kota"]){
   $sql = "select * from global.global_lokasi where lokasi_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_lokasi_kota"]);
   $rs = $dtaccess->Execute($sql);
   $datakotacari = $dtaccess->Fetch($rs);

   $sql_where[] = " ( b.id_prop = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_propinsi"])."
   and b.id_kota = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_kabupatenkota"]).") ";
 }
 if($_POST["reg_tipe_rawat"]){
  $sql_where[] = " a.reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);
}

    //Pilih Poli
if($_POST["id_poli"]) 
{
 $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
}

if($_POST["btnLanjut"] || $_POST["btnCetak"]){
  $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
  a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
  a.reg_batal,d.usr_name as dokter,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
  g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran,
  a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd,l.kondisi_akhir_pasien_nama, a.reg_id
  from klinik.klinik_registrasi a 
  left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
  left join global.global_auth_poli c on c.poli_id = a.id_poli
  left join global.global_auth_user d on a.id_dokter = d.usr_id
  left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
  left join global.global_departemen f on a.id_dep = f.dep_id
  left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
  left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
  left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
  left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
  left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
  left join global.global_kondisi_akhir_pasien l on l.kondisi_akhir_pasien_id=a.reg_status_kondisi";
  $sql.= " where a.reg_tipe_rawat='J' and ".implode(" and ",$sql_where);
  $sql.= " and  cust_usr_kode<>'500' and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') and id_pembayaran is not null ";
  $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
  $rs = $dtaccess->Execute($sql,DB_SCHEMA);
  $dataTable = $dtaccess->FetchAll($rs);
     // echo $sql;
}


$tableHeader = "&nbsp;Report Kunjungan Pasien Harian";

     // --- construct new table ---- //
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
$counterHeader++;

     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     //$counterHeader++;

     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     //$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Klinik";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
$counterHeader++;


    /* $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;*/

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kondisi Akhir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++; 

     
     $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

		//if($_POST["id_poli"] == '--') 
		//{
		 //if ($dataTable[$i]["id_poli"]!=$dataTable[$i-1]["id_poli"])
		 //{
       $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
       $tbContent[$i][$counter][TABLE_ALIGN] = "right";
       $counter++;

       $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
       $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
       $counter++;

       $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
       $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
       $counter++;

       $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";
       $counter++;

       $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
       $counter++;

       $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
       $counter++;    

          //if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          //$umur = explode("~",$dataTable[$i]["cust_usr_umur"]);
          //$tbContent[$i][$counter][TABLE_ISI] = $umur[0]." tahun ".$umur[1]." bulan ".$umur[2]." hari";
          //$tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          //$counter++;

          //$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          //$tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          //$counter++;

       $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
       $counter++;         

       if($dataTable[$i]["reg_jenis_pasien"]=='5'){
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jkn_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
         $counter++;
       }elseif($dataTable[$i]["reg_jenis_pasien"]=='18'){
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jamkesda_kota_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
         $counter++;
       }elseif($dataTable[$i]["reg_jenis_pasien"]=='7'){
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["perusahaan_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
         $counter++;
       }else{
         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
         $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
         $counter++;
       }

       $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
       $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
       $counter++;



          /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;*/

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kondisi_akhir_pasien_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  

          

        }

        $colspan = count($tbHeader[0]);

       //ambil nama poli
//  $sql = "select poli_nama, poli_id from global.global_auth_poli where 
//  (poli_tipe='J' or poli_tipe='M' or poli_tipe='R' or poli_tipe='L' or poli_tipe='P') and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])   ; 
//  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
//  $dataPoli = $dtaccess->FetchAll($rs_edit);

        $sql = "select * from global.global_auth_poli where poli_tipe='J'"; 
        $sql .= " order by poli_nama asc";
        $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataPoli = $dtaccess->FetchAll($rs_edit);



     // ambil jenis pasien
        $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $jenisPasien = $dtaccess->FetchAll($rs); 

    //echo $sql;
        $sql = "select dep_nama from global.global_departemen where
        dep_id = '".$_GET["klinik"]."'";
        $rs = $dtaccess->Execute($sql);
        $namaKlinik = $dtaccess->Fetch($rs);

      //Nama Sekolah
        $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];

        $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
        $rs = $dtaccess->Execute($sql);
        $konfigurasi = $dtaccess->Fetch($rs);

        if($konfigurasi["dep_lowest"]=='n'){
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
        }else if($_POST["klinik"]){
     //Data Klinik
          $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
        }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
        }     

     //ambil jenis pasien
        $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') order by usr_id asc ";
        $rs = $dtaccess->Execute($sql);
        $dataDokter = $dtaccess->FetchAll($rs);

     // cari perusahaan
        $sql = "select * from global.global_perusahaan order by perusahaan_id desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataPerusahaan = $dtaccess->FetchAll($rs);

	 // cari kota jamkesda
        $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_id desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataKota = $dtaccess->FetchAll($rs);

	 // cari Kategori jkn
        $sql = "select * from global.global_jkn order by jkn_id desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataJKN = $dtaccess->FetchAll($rs);

       // cari tipe biaya
        $sql = "select * from global.global_tipe_biaya order by tipe_biaya_nama desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $tipeBiaya = $dtaccess->FetchAll($rs);

	 // cari kondisi
        $sql = "select kondisi_akhir_pasien_id,kondisi_akhir_pasien_nama
        from global.global_kondisi_akhir_pasien 
        order by kondisi_akhir_pasien_id asc";
        $rs = $dtaccess->Execute($sql);
        $dataKondisi = $dtaccess->FetchAll($rs);

        if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
        if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
        $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   

        if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_kunjungan_irj.xls');
        }  

        if($_POST["btnCetak"]){
          $_x_mode = "cetak" ;      
        }
        if($_POST["btnSingkron"]){
          $_x_mode = "kirim" ;      
        }
        $sql = "select * from global.global_lokasi where lokasi_kabupatenkota <>'00' and lokasi_kecamatan='00' and lokasi_kelurahan ='0000' 
        order by lokasi_propinsi, lokasi_kabupatenkota asc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataKotaku = $dtaccess->FetchAll($rs);

        ?>

        <!DOCTYPE html>
        <html lang="en">
        <?php require_once($LAY."header.php") ?>

        <body class="nav-md">
          <div class="container body">
            <div class="main_container">
              <?php require_once($LAY."sidebar.php") ?>

              <!-- top navigation -->
              <?php require_once($LAY."topnav.php") ?>
              <!-- /top navigation -->

              <!-- page content -->
              <div class="right_col" role="main">
                <div class="">
                 <div class="clearfix"></div>
                 <!-- row filter -->
                 <div class="row">
                  <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                      <div class="x_title">
                        <h2>Report Kunjungan</h2>
                        <div class="clearfix"></div>
                      </div>
                      <div class="x_content">
                        <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >



                         <div class="col-md-4 col-sm-6 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                          <div class='input-group date' id='datepicker'>
                           <input name="tgl_awal" type='text' class="form-control" 
                           value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
                           <span class="input-group-addon">
                            <span class="fa fa-calendar">
                            </span>
                          </span>
                        </div>	           			 

                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker2'>
                         <input  name="tgl_akhir"  type='text' class="form-control" 
                         value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
                         <span class="input-group-addon">
                          <span class="fa fa-calendar">
                          </span>
                        </span>
                      </div>	     			 
                    </div>


                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
                      <?php if($userData["rol"]!='2') { ?>       	      
                        <td width="20%" class="tablecontent">
                        <?php } else { ?>
                          <td width="20%" class="tablecontent">
                          <?php } ?>
                          <select class="select2_single form-control" name="reg_jenis_pasien" id="reg_jenis_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                            <option value="0" >[ Pilih Cara Bayar ]</option>
                            <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                              <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["reg_jenis_pasien"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
                            <?php } ?>
                          </select>

                        </div>




                        <div class="col-md-4 col-sm-6 col-xs-12">
                          <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Rawat Jalan</label>
                          <?php if($userData["rol"]!='2') { ?>       	      
                           <td width="20%" class="tablecontent">
                           <?php } else { ?>
                             <td width="20%" class="tablecontent">
                             <?php } ?>
                             <select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                              <!--<option value="">[Pilih Klinik]</option> -->
                              <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                                <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
                              <?php } ?>
                            </select>

                          </div>

                          <div class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Status Pasien</label>
                            <?php if($userData["rol"]!='2') { ?>       	      
                              <td width="20%" class="tablecontent">
                              <?php } else { ?>
                                <td width="20%" class="tablecontent">
                                <?php } ?>
                                <select class="select2_single form-control" name="reg_status_pasien" id="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                                 <option value="" >[ Pilih Status Pasien ]</option>
                                 <option value="B" <?php if('B'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Baru</option>
                                 <option value="L" <?php if('L'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Lama</option>
                               </select>

                             </div>

                             <div class="col-md-4 col-sm-6 col-xs-12">
                              <label class="control-label col-md-12 col-sm-12 col-xs-12">Dokter</label>
                              
                              <select class="select2_single form-control" name="id_dokter" >
                                <option value="" >[ Pilih Dokter ]</option>
                                <?php for($i=0; $i < count($dataDokter); $i++) { ?>
                                 <option value="<?=$dataDokter[$i]['usr_id']?>" <?=($_POST['id_dokter'] == $dataDokter[$i]['usr_id']) ? "selected" : "" ?> ><?=$dataDokter[$i]['usr_name']?></option>
                               <?php } ?>
                             </select>

                           </div>



                           <div hidden class="col-md-4 col-sm-6 col-xs-12">
                            <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori JKN</label>
                            <?php if($userData["rol"]!='2') { ?>       	      
                              <td width="20%" class="tablecontent">
                              <?php } else { ?>
                                <td width="20%" class="tablecontent">
                                <?php } ?>
                                <select class="select2_single form-control" name="cust_usr_jkn" id="cust_usr_jkn" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                                 <option value="" >[ Pilih Kategori JKN ]</option>
                                 <?php for($i=0,$n=count($dataJKN);$i<$n;$i++){ ?>
                                   <option value="<?php echo $dataJKN[$i]["jkn_id"];?>" <?php if($dataJKN[$i]["jkn_id"]==$_POST["cust_usr_jkn"]) echo "selected"; ?>><?php echo $dataJKN[$i]["jkn_nama"];?></option>
                                 <?php } ?>
                               </select>

                             </div>

                             <div class="col-md-4 col-sm-6 col-xs-12">
                              <label class="control-label col-md-12 col-sm-12 col-xs-12">Kondisi Akhir</label>
                              <?php if($userData["rol"]!='2') { ?>       	      
                                <td width="20%" class="tablecontent">
                                <?php } else { ?>
                                  <td width="20%" class="tablecontent">
                                  <?php } ?>
                                  <select class="select2_single form-control" name="kondisi_akhir" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                                   <option value="" >[ Pilih Kondisi Akhir ]</option>
                                   <?php for($i=0,$n=count($dataKondisi);$i<$n;$i++){ ?>
                                     <option value="<?php echo $dataKondisi[$i]["kondisi_akhir_pasien_id"];?>" <?php if($dataKondisi[$i]["kondisi_akhir_pasien_id"]==$_POST["kondisi_akhir"]) echo "selected"; ?>><?php echo $dataKondisi[$i]["kondisi_akhir_pasien_nama"];?></option>
                                   <?php } ?>
                                 </select>

                               </div>

                               <div class="col-md-4 col-sm-6 col-xs-12">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
                                <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
                                <!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
                                <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
                                <input type="submit" name="btnSingkron" id="btnSingkron" value=" Kirim Data ke Mutu" class="pull-right btn btn-success">

                              </div>
                              <div class="clearfix"></div>
                              <? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
                               <?}?>
                               <? if ($_x_mode == "Edit"){ ?>
                                 <?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
                               <? } ?>

                             </form>
                           </div>
                         </div>
                       </div>
                     </div>
                     <!-- //row filter -->


                     <div class="col-md-12 col-sm-12 col-xs-12">
                      <div class="x_panel">
                        <div class="x_content">
                          <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                            <thead>
                              <tr>
                                <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                                 <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                               <? } ?>
                             </tr>
                           </thead>
                           <tbody>
                            <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>

                              <tr class="even pointer">
                                <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                                  <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                                <? } ?>

                              </tr>

                            <? } ?>
                          </tbody>
                          <?php //echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
                        </table>					
                      </div>
                    </div>
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

      </body>
      </html>

    </script>
    <?php if(!$_POST["btnExcel"]) { ?>

      <br />
    <?php } ?>
    <script language="JavaScript">
      function CheckSimpan(frm) { 
       if(!frm.tgl_awal.value) {
        alert("Tanggal Harus Diisi");
        return false;
      }

      if(!CheckDate(frm.tgl_awal.value)) {
        return false;
      }
    }

//window.onload = function() { TampilCombo(); }
  /*function TampilCombo(id)
    {        
         
         //alert(id);
         if(id=="7"){
              id_perusahaan.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              id_perusahaan.disabled = true;
         }
         if(id=="18"){
              id_jamkesda_kota.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              id_jamkesda_kota.disabled = true;
         }
		 if(id=="5"){
              cust_usr_jkn.disabled = false;
              //elm_combo.checked = true; 
                       
         } else {
              cust_usr_jkn.disabled = true;
         }
       }*/

       <?php if($_x_mode=="cetak"){ ?>	
        window.open('report_pasien_cetak.php?tipe=<?php echo $_POST["reg_tipe_rawat"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>&jenis=<?php echo $_POST["reg_jenis_pasien"]?>', '_blank');
      <?php } ?>
      <?php if($_x_mode=="kirim"){ ?>  
        window.open('kirim_data_mutu.php?reg_tipe_rawat=<?php echo $_POST["reg_tipe_rawat"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&id_dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>&reg_jenis_pasien=<?php echo $_POST["reg_jenis_pasien"]?>', '_blank');
      <?php } ?>
    </script>
