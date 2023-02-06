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
     $poliId = $auth->IdPoli();
     $thisPage = "report_pasien.php";
     
    // $_POST["klinik"]=$depId;
    
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } 

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else  $_POST["klinik"] = $_POST["klinik"];
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
     $statusPasien["L"] = "Lama";
     $statusPasien["B"] = "Baru";

     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
     if($_POST["tanggal_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if($_POST["tanggal_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     if($_POST["reg_status_pasien"]) $sql_where[] = " reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);     

     if($_POST["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     if($_POST["reg_shift"]){
		$sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
	 }
	 
   if($_POST["reg_icd"]){
		$sql_where[] = " reg_icd = ".QuoteValue(DPE_CHAR,$_POST["reg_icd"]);
	 }
	 
	 if($_POST["reg_tipe_layanan"]){
		$sql_where[] = " reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 }
	 
	 if($_POST["cust_usr_jenis"] && $_POST["cust_usr_jenis"]<>0){
		$sql_where[] = " reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	 }
	 
	  if($_POST["cust_usr_jkn"]){
		$sql_where[] = " reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]);
	 }
   
   if($_POST["id_perusahaan"]){
		$sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
	 }
   
   if($_POST["id_jamkesda_kota"]){
		$sql_where[] = " a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
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
   if($_POST["petugas"]) $sql_where[] = " k.rawat_who_insert_icd = ".QuoteValue(DPE_CHAR,$_POST["petugas"]);
   
     if($_POST["id_poli"] && $_POST["id_poli"] <> '--') $sql_where[] = " a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

      $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
			   a.reg_batal,a.reg_diagnosa_awal,d.usr_name,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, 
         b.cust_usr_umur,
			   g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran,
         a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd from klinik.klinik_registrasi a 
			   left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
         left join global.global_auth_poli c on c.poli_id = a.id_poli
         left join global.global_auth_user d on a.id_dokter = d.usr_id
         left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
         left join global.global_departemen f on a.id_dep = f.dep_id
			   left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
			   left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
			   left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
			   left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
         left join klinik.klinik_perawatan k on k.id_reg=a.reg_id";
     $sql .= " where ".implode(" and ",$sql_where);
   //  $sql .= " and a.reg_tipe_rawat='J' "; 
     if(!$POST["id_poli"] || $_POST["id_poli"] == '--') {
     $sql .= " and (reg_utama is null or reg_utama ='') ";
     } 
     $sql.= " and cust_usr_kode<>'500' and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') ";
     $sql.= "order by a.id_pembayaran, a.reg_tanggal asc,a.reg_waktu asc";
     //$sql.= " and (a.reg_status='M0' or a.reg_status='E0' or a.reg_status='F0' or a.reg_status='E0') and (reg_utama is null or reg_utama='')
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
   //    echo $sql;
     
      for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_pembayaran"]==$dataTable[$i-1]["id_pembayaran"] ){
          $hitung[$dataTable[$i]["id_pembayaran"]] += 1;
          }      
      }                                                                                      

     $tableHeader = "&nbsp;Report Pengunjung";
  
     // --- construct new table ---- //
     $counterHeader = 0;
     
     if($_POST["btnExcelNew"]){
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Registrasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     } else {     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Identitas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
	 
  	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa Masuk";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     }
     $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
      if($_POST["btnExcelNew"]){
        if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
        $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]]+1;
        
		  $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"])." ".$dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
        }
      } else {
        if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
        $dataSpan["jml_span"] = $hitung[$dataTable[$i]["id_pembayaran"]]+1;
        
		  $tbContent[$i][$counter][TABLE_ISI] = $m + 1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          $m++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = nl2br($dataTable[$i]["cust_usr_alamat"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;    
          
          //if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
          $umur = explode("~",$dataTable[$i]["cust_usr_umur"]);
          $tbContent[$i][$counter][TABLE_ISI] = $umur[0]." tahun ".$umur[1]." bulan ".$umur[2]." hari";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_jenis_kelamin"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          if($dataTable[$i]["reg_jenis_pasien"]=='5'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jkn_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='18'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["jamkesda_kota_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
			  $counter++;
		  }elseif($dataTable[$i]["reg_jenis_pasien"]=='7'){
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"]." - ".$dataTable[$i]["perusahaan_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
			  $counter++;
		  }else{
			  $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
			  $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
			  $counter++;
		  }
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_kartu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"]."-".$dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];          
          $counter++;
		  
	      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
		      //if($dataTable[$i]["reg_icd"]=='y' && !$dataTable[$i]["rawat_diagnosa_utama"]){
          //$tbContent[$i][$counter][TABLE_ISI] = "Masuk Rawat Inap";
          //} else {
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_diagnosa_awal"];
          //}
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          if($dataTable[$i]["reg_icd"]=='y'){
          $tbContent[$i][$counter][TABLE_ISI] = "Sudah Dibridging";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "Belum Dibridging";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_who_insert_icd"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
          $counter++;
		 }
     }
          
     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
      $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." 
              and (poli_tipe ='J' or poli_tipe='L' or poli_tipe ='R') order by poli_nama"; 
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
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') 
     and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_name asc ";
     $rs = $dtaccess->Execute($sql);
     $dataDokter = $dtaccess->FetchAll($rs);
     
     //cari data kelurahan
		 $sql = "select * from global.global_kecamatan";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKec = $dtaccess->FetchAll($rs);

     //cari data shift
		 $sql = "select * from global.global_shift";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);

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
     
     $sql = "select distinct rawat_who_insert_icd from klinik.klinik_perawatan where rawat_who_insert_icd <> '' order by rawat_who_insert_icd asc";
     $rs = $dtaccess->Execute($sql);
     $petugas = $dtaccess->FetchAll($rs);

     $sql = "select * from global.global_lokasi where lokasi_kabupatenkota <>'00' and lokasi_kecamatan='00' and lokasi_kelurahan ='0000' 
             order by lokasi_propinsi, lokasi_kabupatenkota asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataKotaku = $dtaccess->FetchAll($rs);
          
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pengunjung_irj.xls');
      }
      
      if($_POST["btnExcelNew"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=pengunjung_irj.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

?>



<!DOCTYPE html>
<html lang="en">
<script language="JavaScript">
function CheckSimpan(frm) { 
     if(!frm.tanggal_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tanggal_awal.value)) {
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
  window.open('report_pengunjung_cetak.php?tipe=<?php echo $_POST["reg_tipe_rawat"];?>&klinik=<?php echo $_POST["klinik"];?>&tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>', '_blank');
<?php } ?>
</script>
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
                    <h2>Report Pengunjung</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
				
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input name="tanggal_awal" type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  name="tanggal_akhir"  type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
						<div id="div_header"><select class="select2_single form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
     						<option value="" >[ Semua Dokter ]</option>
    							<?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
  							<option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
	 						<?php } ?>
					</select></div> 
				    </div>
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Status</label>
						<div id="div_header"><select class="select2_single form-control" name="reg_icd" id="reg_icd" onKeyDown="return tabOnEnter(this, event);">
                  <option value="" >[ Semua Status]</option>
                  <option value="n" <?php if($_POST["reg_icd"]=='n') echo "selected"; ?>>Belum Dibridging</option>
                  <option value="y" <?php if($_POST["reg_icd"]=='y') echo "selected"; ?>>Sudah Dibridging</option>
        			</select></div> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Petugas</label>
						<div id="div_header"><select class="select2_single form-control" name="petugas" id="petugas" onKeyDown="return tabOnEnter(this, event);">
              			<option value="" >[ Semua Petugas ]</option>
              			<?php for($i=0,$n=count($petugas);$i<$n;$i++){ ?>
          				<option value="<?php echo $petugas[$i]["rawat_who_insert_icd"];?>" <?php if($petugas[$i]["rawat_who_insert_icd"]==$_POST["petugas"]) echo "selected"; ?>><?php echo $petugas[$i]["rawat_who_insert_icd"];?></option>
						<?php } ?>
        			</select></div> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Shift</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               				<select class="select2_single form-control" name="reg_shift" id="reg_shift" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
			   					<option value="">[- Semua Shift -]</option>
          							<?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
          						<option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($dataShift[$i]["shift_id"]==$_POST["reg_shift"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"]." (".$dataShift[$i]["shift_jam_awal"]."-".$dataShift[$i]["shift_jam_akhir"].")";?></option>
									<?php } ?>
							</select>
						</div> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jamkesda Kota</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               					<select class="select2_single form-control" name="id_jamkesda_kota" id="id_jamkesda_kota" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          							<option value="" >[ Pilih Nama Kota ]</option>
          								<?php for($i=0,$n=count($dataKota);$i<$n;$i++){ ?>
          							<option value="<?php echo $dataKota[$i]["jamkesda_kota_id"];?>" <?php if($dataKota[$i]["jamkesda_kota_id"]==$_POST["id_jamkesda_kota"]) echo "selected"; ?>><?php echo $dataKota[$i]["jamkesda_kota_nama"];?></option>
										<?php } ?>
								</select>
						</div> 
				    </div>

				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kota/Kabupaten</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      						<?php } else { ?>
              				<td width="20%" class="tablecontent">
      						<?php } ?>
               					<select class="select2_single form-control" name="id_lokasi_kota" id="id_lokasi_kota" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          							<option value="" >[ Pilih Kota / Kabupaten ]</option>
									<?php for($i=0,$n=count($dataKotaku);$i<$n;$i++){ ?>
									<option value="<?php echo $dataKotaku[$i]["lokasi_id"];?>" <?php if($dataKotaku[$i]["lokasi_id"]==$_POST["id_lokasi_kota"]) echo "selected"; ?>><?php echo $dataKotaku[$i]["lokasi_nama"];?></option>
									<?php } ?>
								</select>
						</div> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Pasien</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Layanan</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
              			<td width="20%" class="tablecontent">
      						<?php } else { ?>
              			<td width="20%" class="tablecontent">
      						<?php } ?>
               			<select class="select2_single form-control" name="reg_tipe_layanan" id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          					<option value="" >[ Pilih Tipe Layanan ]</option>
		  					<?php for($i=0,$n=count($tipeBiaya);$i<$n;$i++){ ?>
          					<option value="<?php echo $tipeBiaya[$i]["tipe_biaya_id"];?>" <?php if($tipeBiaya[$i]["tipe_biaya_id"]==$_POST["reg_tipe_layanan"]) echo "selected"; ?>><?php echo $tipeBiaya[$i]["tipe_biaya_nama"];?></option>
							<?php } ?>
						</select>
						</div> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Perusahaan</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori JKN</label>
						<div id="div_header"><?php if($userData["rol"]!='2') { ?>       	      
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
					<?php if($_POST["btnExcel"]) {?>

     		<table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="10">
               <strong>LAPORAN PENGUNJUNG IRJ<br />
               <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
               <br /><br />
               </strong>
               </td>          
          </tr>
         <tr class="tableheader">
          <td align="left" colspan="10">
          <?php if($_POST["tgl_awal"]==$_POST["tgl_akhir"]) { echo "Tanggal : ".$_POST["tgl_awal"]; } elseif($_POST["tgl_awal"]!=$_POST["tgl_akhir"]) { echo "Periode : ".$_POST["tgl_awal"]." - ".$_POST["tgl_akhir"]; }  ?>              
          <br /><br /> 
          </td>
          </tr>
          <br />
     </table>
     
<?php }?>
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
































