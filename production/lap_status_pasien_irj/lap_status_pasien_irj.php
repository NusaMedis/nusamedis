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
        
    // $_POST["klinik"]=$depId;
   $statusPasien["E0"]="Blm Dilayani";
   $statusPasien["E1"]="Pelayanan Awal";
   $statusPasien["E2"]="Pelayanan Medis";
   $statusPasien["E3"]="Pulang";
   
   $statusPasien["G0"]="Blm Dilayani";
   $statusPasien["G1"]="Pelayanan Awal";
   $statusPasien["G2"]="Pelayanan Medis";
   $statusPasien["G3"]="Pulang";
   
   $statusPasien["y"]="Lunas";
   $statusPasien["n"]="Belum Lunas";
   
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
     
        
     //untuk mencari tanggal
     if($_POST["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
 
           
   if(!empty($_POST["id_poli"])){
    $sql_where[] = " a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
   }
   if(!empty($_POST["cust_usr_jenis"])){
    $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
   }
   
   if(!empty($_POST["id_perusahaan"])){
    $sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
   }
   
   if(!empty($_POST["kondisi_akhir"])){
    $sql_where[] = " a.reg_status_kondisi = ".QuoteValue(DPE_CHAR,$_POST["kondisi_akhir"]);
   }

   if(!empty($_POST["kondisi_akhir_deskripsi"])){
    $sql_where[] = " a.reg_status_kondisi_deskripsi = ".QuoteValue(DPE_CHAR,$_POST["kondisi_akhir_deskripsi"]);
   }

    if($_POST["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
   
     
    //Pilih Poli
    $sql_where[] = "reg_tipe_rawat = 'J'";
      $sql = "select o.rawat_icd_kode, o.rawat_icd9_tindakan_nama , b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, 
      b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
      a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status,a.reg_bayar,
      a.reg_kode_trans, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
      a.reg_batal,d.usr_name as dokter,jenis_nama, a.id_poli, c.poli_nama, 
      ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur, g.perusahaan_nama, 
      h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, 
      a.id_pembayaran, a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd,l.kondisi_akhir_pasien_nama, 
      m.kondisi_akhir_deskripsi_nama 
      from klinik.klinik_registrasi a 
      left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
      left join global.global_auth_poli c on c.poli_id = a.id_poli 
      left join global.global_auth_user d on a.id_dokter = d.usr_id 
      left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id 
      left join global.global_departemen f on a.id_dep = f.dep_id 
      left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan 
      left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota 
      left join global.global_jkn i on i.jkn_id = a.reg_tipe_jkn 
      left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan 
      left join klinik.klinik_perawatan k on k.id_reg=a.reg_id 
      left join global.global_kondisi_akhir_pasien l on l.kondisi_akhir_pasien_id = a.reg_status_kondisi 
      left join global.global_kondisi_akhir_deskripsi m on m.kondisi_akhir_deskripsi_id = a.reg_status_kondisi_deskripsi 
      left join klinik.klinik_perawatan_icd o on k.rawat_id = o.id_rawat ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and cust_usr_kode<>'500' and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') and (rawat_icd_urut = '1' or rawat_icd_urut is null) and poli_nama not ilike '%Apotek%'";
     $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
      //echo $sql;
    

     $tableHeader = "Laporan Status Pasien";
  
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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Registrasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;   
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
   
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;     
   
   /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pembayaran";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++; */
     
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Kondisi Akhir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

      $tbHeader[0][$counterHeader][TABLE_ISI] = "Kondisi Deskripsi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
   
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
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
      
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_kode_trans"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
      
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

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
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;   
                
      /*$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_bayar"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; */
      
        $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kondisi_akhir_pasien_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; 

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_icd_kode"]."- ".$dataTable[$i]["rawat_icd9_tindakan_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kondisi_akhir_deskripsi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++; 
      
          $tbContent[$i][$counter][TABLE_ISI] = strtoupper($dataTable[$i]["reg_who_update"]);
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

  $sql = "select a.poli_nama, a.poli_id from global.global_auth_poli a join global.global_auth_user_poli b on a.poli_id = b.id_poli 
          where (a.poli_tipe='J' or a.poli_tipe='M') and b.id_usr =".QuoteValue(DPE_CHAR,$userId); 
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
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_id asc ";
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
          header('Content-Disposition: attachment; filename=laporan_status_pasien_igd.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }
     $sql = "select * from global.global_lokasi where lokasi_kabupatenkota <>'00' and lokasi_kecamatan='00' and lokasi_kelurahan ='0000' 
             order by lokasi_propinsi, lokasi_kabupatenkota asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKotaku = $dtaccess->FetchAll($rs);

?>

<!DOCTYPE html>
<html lang="en">
<?php if(!$_POST["btnExcel"]) {  ?>
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
      <?php }?>
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Laporan Status Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
          <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
<?php  if(!$_POST["btnExcel"]) {  ?>
          <!--fieldset>
                          <div class="control-group">
                            <div class="controls">
                              <div class="col-md-11 xdisplay_inputx form-group has-feedback">
                                <input type="text" name="tgl_coba" class="form-control has-feedback-left" id="single_cal2" aria-describedby="inputSuccess2Status2">
                                <span class="fa fa-calendar-o form-control-feedback left" aria-hidden="true"></span>
                                <span id="inputSuccess2Status2" class="sr-only">(success)</span>
                              </div>
                            </div>
                          </div>
          </fieldset-->
          
      
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
                      <select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                        <option value="0" >[ Pilih Cara Bayar ]</option>
                          <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                        <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
                      <?php } ?>
                  </select>
            
            </div>
                                
              <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Poli</label>
            <?php if($userData["rol"]!='2') { ?>               
              <td width="20%" class="tablecontent">
                <?php } else { ?>
              <td width="20%" class="tablecontent">
                <?php } ?>
              <select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
                <option value="">[ Pilih klinik ]</option>
                <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
                <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
                <?php } ?>
              </select>
      
            </div>
                                    
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Perusahaan</label>
            <?php if($userData["rol"]!='2') { ?>               
                      <td width="20%" class="tablecontent">
                  <?php } else { ?>
                      <td width="20%" class="tablecontent">
                  <?php } ?>
                        <select class="select2_single form-control" name="id_perusahaan" id="id_perusahaan" onKeyDown="return tabOnEnter(this, event);">
                          <option value="" >[ Pilih Nama Perusahaan ]</option>
                          <?php for($i=0,$n=count($NamaPerusahaan);$i<$n;$i++){ ?>
                          <option value="<?php echo $NamaPerusahaan[$i]["perusahaan_id"];?>" <?php if($NamaPerusahaan[$i]["perusahaan_id"]==$_POST["id_perusahaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$NamaPerusahaan[$i]["perusahaan_nama"];?></option>
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Dokter</label>
                              
                         <select class="select2_single form-control" name="id_dokter" >
                              <option value="" >[ Pilih Dokter ]</option>
                              <?php for($i=0; $i < count($dataDokter); $i++) { ?>
                                   <option value="<?=$dataDokter[$i]['usr_id']?>" <?=($_POST['id_dokter'] == $dataDokter[$i]['usr_id']) ? "selected" : "" ?> ><?=$dataDokter[$i]['usr_name']?></option>
                              <?php } ?>
                         </select>
                               
                        </div>
            
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kondisi Akhir</label>
          
                        <select class="select2_single form-control" name="kondisi_akhir" onchange="get_kondisi_akhir_deskripsi(this.value)" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                        <option value="">[ Pilih Kondisi Akhir ]</option>
                        <?php for($i=0,$n=count($dataKondisi);$i<$n;$i++){ ?>
                        <option value="<?php echo $dataKondisi[$i]["kondisi_akhir_pasien_id"];?>" <?php if($dataKondisi[$i]["kondisi_akhir_pasien_id"]==$_POST["kondisi_akhir"]) echo "selected"; ?>><?php echo $dataKondisi[$i]["kondisi_akhir_pasien_nama"];?></option>
                  <?php } ?>
                </select>
  
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Kondisi Akhir Deskripsi</label>
       
                    <select class="select2_single form-control" id="kondisi_akhir_deskripsi" name="kondisi_akhir_deskripsi" disabled=""> <!--onChange="this.form.submit();" -->
                      <option value="">[ Pilih Kondisi Akhir Deskripsi ]</option>
                    </select>
              
            </div>
          
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
                    <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">
                    <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
            </div>
          <div class="clearfix"></div>
          <? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
          <?}?>
          <? if ($_x_mode == "Edit"){ ?>
          <?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
          <? } ?>
          
          <script type="text/javascript">
            Calendar.setup({
              inputField     :    "tanggal_awal",      // id of the input field
              ifFormat       :    "<?=$formatCal;?>",       // format of the input field
              showsTime      :    false,            // will display a time selector
              button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
              singleClick    :    true,           // double-click mode
              step           :    1                // show all years in drop-down boxes (instead of every other year as default)
            });
    
            Calendar.setup({
              inputField     :    "tanggal_akhir",      // id of the input field
              ifFormat       :    "<?=$formatCal;?>",       // format of the input field
              showsTime      :    false,            // will display a time selector
              button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
              singleClick    :    true,           // double-click mode
              step           :    1                // show all years in drop-down boxes (instead of every other year as default)
            });
          </script>
          </form>
                  </div>
                </div>
              </div>
            </div>
      <!-- //row filter -->


              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  <?php } ?>
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

function get_kondisi_akhir_deskripsi(v) { 
  //alert(v);
  if ( v == '3' || v == '2' ){
    $.ajax({
        type: 'POST',
        url: 'get_kondisi_deskripsi.php',
        data: 'id='+v,
        success:function(html){
          $('#kondisi_akhir_deskripsi').removeAttr('disabled');
            $('#kondisi_akhir_deskripsi').html(html);
        }
    }); 
  } else {
    $('#kondisi_akhir_deskripsi').attr('disabled','disabled');
  //  $('#kondisi_akhir_deskripsi').html('<option value="">Pilih Klinik Dahulu</option>'); 
  }
}

window.onload = function() { TampilCombo(); }
  function TampilCombo(id)
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
    }

<?php if($_x_mode=="cetak"){ ?> 
  window.open('lap_status_pasien_irj_cetak.php?tipe=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&dokter=<?php echo $_POST["id_dokter"];?>&poli=<?php echo $_POST["id_poli"];?>&id_perusahaan=<?php echo $_POST["id_perusahaan"];?>&kondisi=<?php echo $_POST["kondisi_akhir"];?>&deskripsi=<?php echo $_POST["kondisi_akhir-deskripsi"];?>', '_blank');
<?php } ?>

</script>
