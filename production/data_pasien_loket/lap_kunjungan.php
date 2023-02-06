<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/tampilan.php");

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
     
    // $_POST["klinik"]=$depId;

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
     else  $_POST["klinik"] = $_POST["klinik"];
     
     $sql = "select * from  klinik.klinik_split where split_id = 'edee873493dcc8f8849fa928428fe5a2' order by split_flag asc ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
     $dataSplit = $dtaccess->FetchAll($rs);
 
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
	 
	 $_POST["awal_shift1"] = $dataShift[0]["shift_jam_awal"];
	 $_POST["akhir_shift1"] = $dataShift[0]["shift_jam_akhir"];
	 $_POST["awal_shift2"] = $dataShift[1]["shift_jam_awal"];
	 $_POST["akhir_shift2"] = $dataShift[1]["shift_jam_akhir"];
	 
	 $awal1 = $_POST["awal_shift1"];
	 $akhir1 = $_POST["akhir_shift1"];
	 $awal2 = $_POST["awal_shift2"];
	 $akhir2 = $_POST["akhir_shift2"];

     if($userData["rol"]=='2') { 
            $sql_where1 = " a.id_dokter =".QuoteValue(DPE_CHAR,$userId);
     } else {
            if($_POST["id_dokter"]) $sql_where1 = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
     }
    
     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
     if($_POST["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     if($_POST["reg_shift"]){
		$sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_POST["reg_shift"]);
	 }

	/*if($userId<>'b9ead727d46bc226f23a7c1666c2d9fb' or $userId<>'92df81c2bebf2f93f75d9ad1014fe930'){
		$sql_where[] = " a.reg_who_update =".QuoteValue(DPE_CHAR,$userName);
	 }*/
	 
	 if($_POST["cust_usr_nama"]){
		$sql_where[] = " b.cust_usr_nama like '%".$_POST["cust_usr_nama"]."%'";
	 }
	 
	 if($_POST["cust_usr_kode"]){
		$sql_where[] = " b.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
	 }
	 
	 if($_POST["cust_usr_alamat"]){
		$sql_where[] = " b.cust_usr_alamat = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_alamat"]);
	 }
   
   if($_POST["cust_usr_jenis"]){
		$sql_where[] = " b.cust_usr_jenis = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jenis"]);
	 }
   
   if($_POST["reg_tipe_layanan"]){
		$sql_where[] = " a.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_POST["reg_tipe_layanan"]);
	 }
   
   if($_POST["id_perusahaan"]){
		$sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_POST["id_perusahaan"]);
	 }
   
   if($_POST["cust_usr_jkn"]){
		$sql_where[] = " b.cust_usr_jkn = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_jkn"]);
	 }
   
   if($_POST["id_jamkesda_kota"]){
		$sql_where[] = " a.id_jamkesda_kota = ".QuoteValue(DPE_CHAR,$_POST["id_jamkesda_kota"]);
	 }
	 
	 if($_POST["reg_status_pasien"]){
		$sql_where[] = " a.reg_status_pasien = ".QuoteValue(DPE_CHAR,$_POST["reg_status_pasien"]);
	 }
	 
    //Pilih Poli
     if($_POST["id_poli"]) $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

      $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
			   a.reg_batal,d.usr_name,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur,
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
     $sql.= " where ".implode(" and ",$sql_where);
     $sql.= " and (a.reg_status='E0' or a.reg_status='E1' or a.reg_status='F0' or a.reg_status='E2')  
              and cust_usr_kode<>'500' and cust_usr_kode<>'100' and a.reg_batal is null ";
     $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
      //echo $sql;
     
	   // --- ngitung jml data e ---
	  $sql = "select count(reg_id) as total
            from   klinik.klinik_registrasi a 
            join   global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
            where (a.reg_status='E0' or a.reg_status='E1' or a.reg_status='F0' or a.reg_status='E2') and cust_usr_kode<>'500' and cust_usr_kode<>'100'";
            $sql .= " and ".implode(" and ",$sql_where);
            $sql.= " and a.reg_batal is null ";
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;

     $tableHeader = "&nbsp;Report Kunjungan Pasien Harian";
  
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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Umur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     //$counterHeader++;    
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	  
		if($_POST["id_poli"] == '--') 
		{
		 if ($dataTable[$i]["id_poli"]!=$dataTable[$i-1]["id_poli"])
		 {
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
          
          if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
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
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
		  
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
        }  
	   } else { //jika milih poli
		
		if ($dataTable[$i]["id_pembayaran"]!=$dataTable[$i-1]["id_pembayaran"])
		 {
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
          
          if($dataTable[$i]["umur"]) $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["umur"];  else  $tbContent[$i][$counter][TABLE_ISI] = "-";
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
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
 
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
		  
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_waktu"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
        }  

       }	   
		
     }
     
     $colspan = count($tbHeader[0]);
     
       //ambil nama poli
  $sql = "select poli_nama, poli_id from global.global_auth_poli where (poli_tipe='J' or poli_tipe='M' or poli_tipe='R' or poli_tipe='L' or poli_tipe='P') and id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])   ; 
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

?>
<?php if(!$_POST["btnExcel"]) { ?>
<?php echo $view->RenderBody("ipad_depans.css",true,"LAPORAN KUNJUNGAN"); ?>
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
  window.open('report_pasien_cetak.php?klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_poli=<?php echo $_POST["id_poli"];?>', '_blank');
<?php } ?>

</script>

<?php echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '50%',
'height' : '100%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script>
<style type="text/css">
#top{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0068c9), to(#007bed));
background: -moz-linear-gradient(top, #0068c9, #007bed); 
}
</style>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
<?php if(!$_POST["btnExcel"]) { ?>
<div id="header"> 
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" align="right">
<a href=""><font size="6">LAPORAN KUNJUNGAN PASIEN</font></a>
</td>
</tr>
</table></div>
<div id="bodyku">
<br />
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">    
<table align="center" border="0" cellpadding="2" cellspacing="1" width="100%" class="tblForm" id="tblSearching">    
     <tr >
          <td width="10%" class="tablecontent">&nbsp;Periode Tanggal</td>
          <td width="30%" class="tablecontent" colspan="3">
               <input type="text" id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;(dd-mm-yyy)&nbsp;
               -
               <input type="text" id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;(dd-mm-yyy)&nbsp;    
       </td>
    </tr>
    	<tr>
				<td width="10%" class="tablecontent" width="30%">&nbsp;Nama Pasien</td>
				<td width="30%" class="tablecontent" colspan="3">
					<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
				</td>
			</tr>
			<tr>
				<td width="10%" class="tablecontent">&nbsp;No. Medrec</td>
				<td width="20%" class="tablecontent">
					<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
				</td>
				<td width="10%" class="tablecontent" >&nbsp;Nama Poli</td>
				<?php if($userData["rol"]!='2') { ?>       	      
				<td width="20%" class="tablecontent">
				<?php } else { ?>
				<td width="20%" class="tablecontent">
				<?php } ?>
					<select name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
						<option value="">[Pilih Poli]</option>
						<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
						<option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
						<?php } ?>
					</select>	
				</td>	
			</tr>
			<tr>
				<td width="10%" class="tablecontent">&nbsp;Alamat</td>
				<td width="20%" class="tablecontent">
					<?php echo $view->RenderTextBox("cust_usr_alamat","cust_usr_alamat",30,200,$_POST["cust_usr_alamat"],false,false);?>
				</td> 
				<td width="10%" class="tablecontent">&nbsp;Jenis Pasien&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               <select name="reg_status_pasien" id="reg_status_pasien" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          <option value="" >[ Pilih Jenis Pasien ]</option>
		  <option value="B" <?php if('B'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Baru</option>
          <option value="L" <?php if('L'==$_POST["reg_status_pasien"]) echo "selected"; ?> >Lama</option>
			</select>
               </td>
			</tr>
              <tr>
     <td width="10%" class="tablecontent">&nbsp;Shift&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               <select name="reg_shift" id="reg_shift" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
			   <option value="">[- Semua Shift -]</option>
          <?php for($i=0,$n=count($dataShift);$i<$n;$i++){ ?>
          <option value="<?php echo $dataShift[$i]["shift_id"];?>" <?php if($dataShift[$i]["shift_id"]==$_POST["reg_shift"]) echo "selected"; ?>><?php echo $dataShift[$i]["shift_nama"]." (".$dataShift[$i]["shift_jam_awal"]."-".$dataShift[$i]["shift_jam_akhir"].")";?></option>
				<?php } ?>
			</select>
               </td>
               <td width="10%" class="tablecontent">&nbsp;Tipe Layanan&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               <select name="reg_tipe_layanan" id="reg_tipe_layanan" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          <option value="" >[ Pilih Tipe Layanan ]</option>
		  <?php for($i=0,$n=count($tipeBiaya);$i<$n;$i++){ ?>
          <option value="<?php echo $tipeBiaya[$i]["tipe_biaya_id"];?>" <?php if($tipeBiaya[$i]["tipe_biaya_id"]==$_POST["reg_tipe_layanan"]) echo "selected"; ?>><?php echo $tipeBiaya[$i]["tipe_biaya_nama"];?></option>
				<?php } ?>
			</select>
               </td>
               </tr>
               <tr>
     <td width="10%" class="tablecontent">&nbsp;Cara Bayar&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               <select name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                <option value="0" >[ Pilih Cara Bayar ]</option>
                <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                <option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
      				<?php } ?>
      			</select>
               </td>
               <td width="10%" class="tablecontent">&nbsp;Nama Perusahaan&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
              <select name="id_perusahaan" id="id_perusahaan" onKeyDown="return tabOnEnter(this, event);">
                <option value="" >[ Pilih Nama Perusahaan ]</option>
                <?php for($i=0,$n=count($NamaPerusahaan);$i<$n;$i++){ ?>
                <option value="<?php echo $NamaPerusahaan[$i]["perusahaan_id"];?>" <?php if($NamaPerusahaan[$i]["perusahaan_id"]==$_POST["id_perusahaan"]) echo "selected"; ?>><?php echo ($i+1).". ".$NamaPerusahaan[$i]["perusahaan_nama"];?></option>
      				<?php } ?>    
      			</select>
               </td>
               </tr>
               <tr>
     <td width="10%" class="tablecontent">&nbsp;Nama Kota&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               <select name="id_jamkesda_kota" id="id_jamkesda_kota" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          <option value="" >[ Pilih Nama Kota ]</option>
          <?php for($i=0,$n=count($dataKota);$i<$n;$i++){ ?>
          <option value="<?php echo $dataKota[$i]["jamkesda_kota_id"];?>" <?php if($dataKota[$i]["jamkesda_kota_id"]==$_POST["id_jamkesda_kota"]) echo "selected"; ?>><?php echo $dataKota[$i]["jamkesda_kota_nama"];?></option>
				<?php } ?>
			</select>
               </td>
               <td width="10%" class="tablecontent">&nbsp;Kategori JKN&nbsp;&nbsp;</td>
			<?php if($userData["rol"]!='2') { ?>       	      
              <td width="20%" class="tablecontent">
      <?php } else { ?>
              <td width="20%" class="tablecontent">
      <?php } ?>
               	<select name="cust_usr_jkn" id="cust_usr_jkn" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
          <option value="" >[ Pilih Kategori JKN ]</option>
          <?php for($i=0,$n=count($dataJKN);$i<$n;$i++){ ?>
          <option value="<?php echo $dataJKN[$i]["jkn_id"];?>" <?php if($dataJKN[$i]["jkn_id"]==$_POST["cust_usr_jkn"]) echo "selected"; ?>><?php echo $dataJKN[$i]["jkn_nama"];?></option>
				<?php } ?>
			</select>
               </td>
               </tr>
          <!--<tr>
          <td width="10%" class="tablecontent" >&nbsp;Nama Poli</td>
          <td width="15%" class="tablecontent" >
									<select name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
										<option value="--">[Pilih Poli]</option>
										<?php //for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
										<option value="<?php //echo $dataPoli[$i]["poli_id"];?>" <?php //if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php //echo $dataPoli[$i]["poli_nama"];?></option>
										<?php //} ?>
									</select>	
          </td>	
          <td class="tablecontent">&nbsp;</td>
          <td width="15%" class="tablecontent" >
     </tr>-->
     <tr>
               <td class="tablecontent" colspan="7">                                    
               <input type="submit" name="btnLanjut" value="Lanjut" class="submit">
               <input type="submit" name="btnExcel" value="Export Excel" class="submit">
               <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="submit" />
          </td>
          </tr>   
</table>
<table width="100%" border="0" >
           <tr>
              <td align="right" >
              
                <?php if($_POST["tgl_awal"]==date("d-m-Y") && $_POST["tgl_akhir"]==date("d-m-Y")) echo "<b>Total Pasien Hari Ini : ".$numRows["total"]."</b>"; ?>            
               </td>
              
               </table>

</form>


<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>
<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="tableheader">
               <td align="center" colspan="10">
               <strong>LAPORAN KUNJUNGAN PASIEN<br />
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
     </table>
     
<?php }?>

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

<?php if(!$_POST["btnExcel"]) {?>
</div>

  		<!--<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php //echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah">&nbsp;</td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->
			
<?php }?>
<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/laporan_kedatangan.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
<?php echo $view->RenderBodyEnd(); ?>
