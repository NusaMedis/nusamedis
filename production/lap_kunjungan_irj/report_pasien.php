<?php                                                                                      
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."expAJAX.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $poliId = $auth->GetPoli();
     $thisPage = "report_pasien.php";
     $depNama = $auth->GetDepNama();
     $plx = new expAJAX("GetDetail");
     
	 
    if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }
	
	
	/* if(!$auth->IsAllowed("irj_informasi_lap_kunjungan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("irj_informasi_lap_kunjungan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
    // $_POST["klinik"]=$depId;

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else  $_POST["klinik"] = $_POST["klinik"];
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
     
     //if($_POST["cust_usr_jenis"]) $_POST["cust_usr_jenis"] = $_POST["cust_usr_jenis"];
     
     function GetDetail($idJenis)
	{
  global $dtaccess,$view;
     
     if($idJenis=="5" || $idJenis=="26"){                                                      
       $sql = "select * from global.global_jkn order by jkn_id asc";
       $rs_edit = $dtaccess->Execute($sql);
       $dataDetail = $dtaccess->FetchAll($rs_edit);
  			unset($detail);
  			$detail[0] = $view->RenderOption("","[Pilih Tipe JKN]",$show);
  			$i = 1;
  			
       for($i=0,$n=count($dataDetail);$i<$n;$i++){   
           if($_POST["reg_tipe_jkn"]==$dataDetail[$i]["jkn_id"]) $show = "selected";
           $detail[$i+1] = $view->RenderOption($dataDetail[$i]["jkn_id"],$dataDetail[$i]["jkn_nama"],$show);
           unset($show);
       }
			 $str = $view->RenderComboBox("reg_tipe_jkn","reg_tipe_jkn",$detail,null,null,null);
	   } elseif($idJenis=="7"){                                                      
       $sql = "select * from global.global_perusahaan order by perusahaan_nama asc";
       $rs_edit = $dtaccess->Execute($sql);
       $dataDetail = $dtaccess->FetchAll($rs_edit);
  			unset($detail);
  			$detail[0] = $view->RenderOption("","[Pilih Perusahaan]",$show);
  			$i = 1;
  			
       for($i=0,$n=count($dataDetail);$i<$n;$i++){   
           if($_POST["id_perusahaan"]==$dataDetail[$i]["perusahaan_id"]) $show = "selected";
           $detail[$i+1] = $view->RenderOption($dataDetail[$i]["perusahaan_id"],$dataDetail[$i]["perusahaan_nama"],$show);
           unset($show);
       }
			 $str = $view->RenderComboBox("id_perusahaan","id_perusahaan",$detail,null,null,null);
	   } elseif($idJenis=="18"){                                                      
       $sql = "select * from global.global_jamkesda_kota order by jamkesda_kota_nama asc";
       $rs_edit = $dtaccess->Execute($sql);
       $dataDetail = $dtaccess->FetchAll($rs_edit);
  			unset($detail);
  			$detail[0] = $view->RenderOption("","[Pilih Jamkesda kota]",$show);
  			$i = 1;
  			
       for($i=0,$n=count($dataDetail);$i<$n;$i++){   
           if($_POST["id_jamkesda_kota"]==$dataDetail[$i]["jamkesda_kota_id"]) $show = "selected";
           $detail[$i+1] = $view->RenderOption($dataDetail[$i]["jamkesda_kota_id"],$dataDetail[$i]["jamkesda_kota_nama"],$show);
           unset($show);
       }
			 $str = $view->RenderComboBox("id_jamkesda_kota","id_jamkesda_kota",$detail,null,null,null);
	   } else {
       $str = "&nbsp;";
     }
   return $str;
  }
     
     //if($userId<>"b9ead727d46bc226f23a7c1666c2d9fb"){
     //if(!$_POST["id_poli"]) $_POST["id_poli"]=$poliId;
     //}
     
     //cari shift
	 $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);
	 

     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
     if($_POST["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     if($_POST["id_kecamatan"]) $sql_where[] = " b.id_kecamatan = ".QuoteValue(DPE_CHAR,$_POST["id_kecamatan"]);     
     if($_POST["reg_status_pasien"]) $sql_where[] = " reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);  
     if($_POST["reg_who_update"]) $sql_where[] = "a.reg_who_update = ".QuoteValue(DPE_CHAR,$_POST["reg_who_update"]);  
     if($_POST["rawat_diagnosa_utama"]) $sql_where[] = "upper(m.rawat_diagnosa_utama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["rawat_diagnosa_utama"])."%"); 

     if($_POST["id_dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     
     if($_POST["reg_shift"]){
		$sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
	 }
	 
	 if($_POST["reg_tipe_layanan"] && $_POST["reg_tipe_layanan"]<>"--"){
		$sql_where[] = " reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 }
   
    if($_POST["id_perusahaan"]){
		$sql_where[] = "a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
	 }
   
   if($_POST["id_jamkesda_kota"]){
		$sql_where[] = "a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
	 }
	 
	 if($_POST["cust_usr_jenis"]){
		$sql_where[] = " reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	 }
  
	 
	 //echo $userId;
	 /*if($userId!='b9ead727d46bc226f23a7c1666c2d9fb' || $userId!='92df81c2bebf2f93f75d9ad1014fe930'){

			$sql_where[] = " a.id_poli = '".$userData["id_poli"]."'";

	 }*/
	  if($_POST["reg_tipe_jkn"] && $_POST["reg_tipe_jkn"]<>"--"){
		$sql_where[] = " a.reg_tipe_jkn = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_jkn"]);
	 }
     //if($_POST["id_poli"] && $_POST["id_poli"] <> '--') $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

   //if($_POST["btnLanjut"] || $_POST["btnExcel"] || $_POST["btnCetak"]){
      $sql = "select tingkat_kegawatan_nama, b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
             a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
    			   a.reg_batal,d.usr_name,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
    			   g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran, b.cust_usr_no_hp,
             m.rawat_diagnosa_utama from klinik.klinik_pembayaran k left join klinik.klinik_registrasi a on a.id_pembayaran = k.pembayaran_id
			       left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
             left join global.global_auth_poli c on c.poli_id = a.id_poli
             left join global.global_auth_user d on a.id_dokter = d.usr_id
             left join global.global_jenis_pasien e on a.reg_jenis_pasien = e.jenis_id
             left join global.global_departemen f on a.id_dep = f.dep_id
    			   left join global.global_perusahaan g on g.perusahaan_id = a.id_perusahaan
    			   left join global.global_jamkesda_kota h on h.jamkesda_kota_id = a.id_jamkesda_kota
    			   left join global.global_jkn i on i.jkn_id = b.cust_usr_jkn
    			   left join global.global_tipe_biaya j on j.tipe_biaya_id = a.reg_tipe_layanan
             left join global.global_tingkat_kegawatan l on l.tingkat_kegawatan_id = a.reg_tingkat_kegawatan
             left join klinik.klinik_perawatan m on m.id_reg=a.reg_id";
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and (a.reg_status like 'M%' or a.reg_status like 'E%' or a.reg_status like 'F%') and a.reg_batal is null and a.id_cust_usr<>'500' 
              and a.id_poli=".QuoteValue(DPE_CHAR,$poliId);
     $sql.= " order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
 //     echo $sql;
     
	   // --- ngitung jml data e ---
	  $sql = "select count(id_pembayaran) as total
            from   klinik.klinik_registrasi a 
            join   global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join klinik.klinik_perawatan m on m.id_reg=a.reg_id
            where (a.reg_status like 'M%' or a.reg_status like 'E%' or a.reg_status like 'F%')";
            $sql .= " and ".implode(" and ",$sql_where);
            $sql.= " and a.reg_batal is null and a.id_poli=".QuoteValue(DPE_CHAR,$poliId);
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;
    //}
    
     $tableHeader = "&nbsp;Report Pengunjung IRJ";
  
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
	 
  	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
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
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Telp/Hp";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa Utama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
	   
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
        if($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"]){
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
          
          $tbContent[$i][$counter][TABLE_ISI] = $statusPasien[$dataTable[$i]["reg_status_pasien"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_no_hp"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"]."-".$dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["rawat_diagnosa_utama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $jumHeader=$tbHeader;
		 }
          
     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
      if($userId=="b9ead727d46bc226f23a7c1666c2d9fb"){
      $sql = "select b.poli_nama, b.poli_id from global.global_auth_poli b where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." and poli_tipe='J'";
      } else {
      $sql = "select b.poli_nama, b.poli_id from global.global_auth_poli b where poli_id=".QuoteValue(DPE_CHAR,$poliId)." and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
      } 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs_edit);
  
     // ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs); 
     
     $jenis[0] = $view->RenderOption("","[Pilih Cara Bayar]",$show);
      for($i=0,$n=count($jenisPasien);$i<$n;$i++){
             if($_POST["cust_usr_jenis"]==$jenisPasien[$i]["jenis_id"]) $show = "selected";
             $jenis[$i+1] = $view->RenderOption($jenisPasien[$i]["jenis_id"],$jenisPasien[$i]["jenis_nama"],$show); 
             unset($show);              
        }
          
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
     
     $sql = "select * from global.global_auth_user where id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_name asc ";
     $rs = $dtaccess->Execute($sql);
     $dataPetugas = $dtaccess->FetchAll($rs);
     
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
     
  // cari tingkat kegawatan
     $sql = "select * from global.global_tingkat_kegawatan order by tingkat_kegawatan_nama desc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tingkatkegawatan = $dtaccess->FetchAll($rs);
     
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_kunjungan_poli.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

?>

<script language="JavaScript">
<? $plx->Run(); ?>

function cariDetail(id){ 
	document.getElementById('div_detail').innerHTML = GetDetail(id,'type=r');
}

function CheckSimpan(frm) { 
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}


<?php if($_x_mode=="cetak"){ ?>	
  window.open('report_pasien_cetak.php?dokter=<?php echo $_POST["id_dokter"];?>&reg_status_pasien=<?php echo $_POST["reg_status_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["reg_shift"];?>&poli=<?php echo $poliId;?>&layanan=<?php echo $_POST["reg_tipe_layanan"];?>&jenis=<?php echo $_POST["cust_usr_jenis"];?>&jkn=<?php echo $_POST["reg_tipe_jkn"];?>&kota=<?php echo $_POST["id_jamkesda_kota"];?>&perusahaan=<?php echo $_POST["id_perusahaan"];?>&petugas=<?php echo $_POST["reg_who_update"];?>&diagnosa=<?php echo $_POST["rawat_diagnosa_utama"];?>', '_blank');
<?php } ?>

</script>


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
            <div class="page-title">
            </div>

            <div class="clearfix"></div>

            <div class="row">
			
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Laporan</h2>
                    <div class="clearfix"></div>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                		<div class="x_panel"> 
                  		<div class="x_content">
                  			<!--kolom kanan-->
					<form id="frmView" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input  id="tgl_awal" name="tgl_awal" type='text' class="form-control" value="<?php echo date('d-m-Y') ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  id="tgl_akhir" name="tgl_akhir"  type='text' class="form-control" value="<?php echo date('d-m-Y') ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Jenis Pasien</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);">
    			  			<option value="" >[ Semua Jenis ]</option>	
    						<option value="B" <?php if($_POST["reg_status_pasien"]=="B")echo "selected";?>>Baru</option>
    						<option value="L" <?php if($_POST["reg_status_pasien"]=="L")echo "selected";?>>Lama</option>
    					</select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Jam Kedatangan<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="reg_shift" id="reg_shift">
               				<option value="">[- Semua Shift -]</option>
              				<?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
              				<option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($dataShift[$i]["shift_id"]==$_POST["reg_shift"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"];?></option>
        					<?php } ?>
               			  </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Tipe Layanan<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="reg_tipe_layanan" id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);">
              				<option value="" >[ Semua Layanan ]</option>
              				<?php for($i=0,$n=count($tipeBiaya);$i<$n;$i++){ ?>
          					<option value="<?php echo $tipeBiaya[$i]["tipe_biaya_id"];?>" <?php if($tipeBiaya[$i]["tipe_biaya_id"]==$_POST["reg_tipe_layanan"]) echo "selected"; ?>><?php echo $tipeBiaya[$i]["tipe_biaya_nama"];?></option>
							<?php } ?>
        				  </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Nama Petugas<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="reg_who_update" id="reg_who_update" onKeyDown="return tabOnEnter(this, event);">
              				<option value="" >[ Semua Petugas ]</option>
              				<?php for($i=0,$n=count($dataPetugas);$i<$n;$i++){ ?>
          					<option value="<?php echo $dataPetugas[$i]["usr_name"];?>" <?php if($dataPetugas[$i]["usr_name"]==$_POST["reg_who_update"]) echo "selected"; ?>><?php echo $dataPetugas[$i]["usr_name"];?></option>
							<?php } ?>
        				 </select>
                        </div>
                      </div>               
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Nama Dokter</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
     						<option value="" >[ Semua Dokter ]</option>
    							<?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
  							<option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
	 						<?php } ?>
							</select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Cara Bayar</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <?php echo $view->RenderComboBox("cust_usr_jenis","cust_usr_jenis",$jenis,null,null,"onChange=\"javascript:return cariDetail(document.getElementById('cust_usr_jenis').value);\"");?>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Diagnosa Utama<span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input class="form-control" type="text" name="rawat_diagnosa_utama" id="rawat_diagnosa_utama" size="30" maxlength="50" value="<?php echo $_POST["rawat_diagnosa_utama"];?>" onKeyDown="return tabOnEnter(this, event);" />
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-12" for="last-name"><?php if($_POST["tgl_awal"]==date("d-m-Y") && $_POST["tgl_akhir"]==date("d-m-Y")) echo "<b>Total Pasien Hari Ini : ".$numRows["total"]."</b>"; ?>            
                        </label>
                      </div>             
                      </div>
                      
                      
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                          <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-Primary">
               			  <input type="submit" name="btnExcel" value="Export Excel" class="btn btn-Success">
               			  <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="btn btn-Primary" />
                          
                        </div>
                      </div>
                  </div>
                </div>
			  </div>
                    </form>
                  </div>
                </div>
			  </div>
			  
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                         <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>


					
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








