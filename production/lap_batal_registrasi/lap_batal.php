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
	 
     if($_POST["id_dokter"]) $sql_where1 = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "reg_batal_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_batal_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     if($_POST["reg_shift"]){
		$sql_where[] = " reg_batal_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
	 }

	 if($_POST["cust_usr_nama"]){
		$sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%'";
	 }
	 
	 if($_POST["cust_usr_kode"]){
		$sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
	 }
	 
	 if($_POST["cust_usr_alamat"]){
		$sql_where[] = " b.cust_usr_alamat = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
	 }
   
   if($_POST["cust_usr_jenis"]){
		$sql_where[] = " a.reg_batal_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	 }
   
   if($_POST["reg_tipe_layanan"]){
		$sql_where[] = " a.reg_batal_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 }
   
  
   if($_POST["cust_usr_jkn"]){
		$sql_where[] = " b.reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]);
	 }
   
	 if($_POST["reg_status_pasien"]){
		$sql_where[] = " a.reg_batal_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
	 }
	 
	 if($_POST["kondisi_akhir"]){
		$sql_where[] = " a.reg_batal_status_kondisi = ".QuoteValue(DPE_CHAR,$_POST["kondisi_akhir"]);
	 }

	 if($_POST["id_lokasi_kota"]){
		 $sql = "select * from global.global_lokasi where lokasi_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_lokasi_kota"]);
     $rs = $dtaccess->Execute($sql);
     $datakotacari = $dtaccess->Fetch($rs);
    
    $sql_where[] = " ( b.id_prop = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_propinsi"])."
                      and b.id_kota = ".QuoteValue(DPE_CHAR,$datakotacari["lokasi_kabupatenkota"]).") ";
	 }
if($_POST["reg_tipe_rawat"]){
		$sql_where[] = " a.reg_batal_tipe_rawat = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);
	 }
   	 
    //Pilih Poli
     if($_POST["id_poli"]) $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

      $sql = "select a.*, b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               d.usr_name,e.jenis_nama, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
			   i.jkn_nama,j.tipe_biaya_nama
			   from klinik.klinik_registrasi_batal a 
			   left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
               left join global.global_auth_poli c on c.poli_id = a.id_poli
               left join global.global_auth_user d on a.id_dokter = d.usr_id
               left join global.global_jenis_pasien e on a.reg_batal_jenis_pasien = e.jenis_id
               left join global.global_departemen f on a.id_dep = f.dep_id
			   left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
			   left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_batal_tipe_layanan ";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and cust_usr_kode<>'500' and cust_usr_kode<>'100' ";
     $sql.= "order by a.reg_batal_tanggal asc,a.reg_batal_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
      //echo $sql;
     
	   // --- ngitung jml data e ---
	  $sql = "select count(reg_batal_id) as total
            from klinik.klinik_registrasi_batal a 
            join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            where cust_usr_kode<>'500' and cust_usr_kode<>'100'";
            $sql .= " and ".implode(" and ",$sql_where);
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;

     $tableHeader = "&nbsp;Report Batal Kunjungan Pasien";
  
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
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

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Alasan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     //$counterHeader++;    
       $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  
		//if($_POST["id_poli"] == '--') 
		//{
		 //if ($dataTable[$i]["id_poli"]!=$dataTable[$i-1]["id_poli"])
		 //{
         $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
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


          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";   
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];       
          $counter++; 

          //if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          $umur = explode("~",$dataTable[$i]["cust_usr_umur"]);
          $tbContent[$i][$counter][TABLE_ISI] = $umur[0]." tahun ".$umur[1]." bulan ".$umur[2]." hari";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
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
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_batal_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
		  
		  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_batal_alasan"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_batal_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
		  
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_batal_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_batal_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  

     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
  $sql = "select poli_nama, poli_id from global.global_auth_poli where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])   ; 
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
          header('Content-Disposition: attachment; filename=report_kunjungan_irj.xls');
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
                    <h2>Report Batal Kunjungan</h2>
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
						 
				    </div>
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
						
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat</label>
						<?php echo $view->RenderTextBox("cust_usr_alamat","cust_usr_alamat",30,200,$_POST["cust_usr_alamat"],false,false);?>
						
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      						<?php } else { ?>
              				<td width="20%" class="tablecontent">
      						<?php } ?>
               					<select class="select2_single form-control" name="reg_tipe_rawat" id="reg_tipe_rawat" onKeyDown="return tabOnEnter(this, event);">
				                  <option value="" >[ Semua Tipe Rawat]</option>
				                  <option value="J" <?php if($_POST["reg_tipe_rawat"]=='J') echo "selected"; ?>>Rawat Jalan</option>
				                  <option value="G" <?php if($_POST["reg_tipe_rawat"]=='G') echo "selected"; ?>>Rawat Darurat</option>
				                  <option value="I" <?php if($_POST["reg_tipe_rawat"]=='I') echo "selected"; ?>>Rawat Inap</option>
				        		 </select>
				        		 </td>
						
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik</label>
						<?php if($userData["rol"]!='2') { ?>       	      
							<td width="20%" class="tablecontent">
								<?php } else { ?>
							<td width="20%" class="tablecontent">
								<?php } ?>
							<select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
								<option value="">[Pilih Klinik]</option>
								<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
								<option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
								<?php } ?>
							</select>
						
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
						<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               					<select class="select2_single form-control" name="reg_status_pasien" id="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          							<option value="" >[ Pilih Jenis Pasien ]</option>
		  							<option value="B" <?php if('B'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Baru</option>
          							<option value="L" <?php if('L'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Lama</option>
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
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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
  window.open('lap_batal_cetak.php?tipe=<?php echo $_POST["reg_tipe_rawat"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>', '_blank');
<?php } ?>

</script>
