<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $thisPage = "report_pasien.php";
     $skr = date("d-m-Y");
     //$_POST["klinik"]=$depId;

     if (!$_GET["klinik"]) $_POST["klinik"]=$depId;
     else $_POST["klinik"]= $_GET["klinik"];

     //pemanggilan tanggal hari ini jika gk ada get tgl awal n akhir 
     if(!$_GET["tgl_awal"]) $_GET["tgl_awal"]  = $skr;
     if(!$_GET["tgl_akhir"]) $_GET["tgl_akhir"]  = $skr;

     //untuk mencari tanggal
     if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_GET["klinik"]."%");
     if($_GET["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     if($_GET["id_kecamatan"]) $sql_where[] = " b.id_kecamatan = ".QuoteValue(DPE_CHAR,$_GET["id_kecamatan"]);     
     if($_GET["reg_status_pasien"]) $sql_where[] = " reg_status_pasien = ".QuoteValue(DPE_CHAR,$_GET["reg_status_pasien"]);     
     if($_GET["shift"] && $_GET["shift"]!="--") $sql_where[] = " reg_shift = ".QuoteValue(DPE_CHAR,$_GET["shift"]);  
     if($_GET["dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["dokter"]);   

     if($_GET["jenis"] && $_GET["jenis"]!="--") $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["jenis"]);
     if($_GET["id_poli"] && $_GET["id_poli"] <> '--') $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
	 if($_GET["tipe"]) $sql_where[] = " reg_tipe_rawat = ".QuoteValue(DPE_CHAR,$_GET["tipe"]);     
          
     //if($_POST["id_poli"] && $_POST["id_poli"] <> '--') $sql_where[] = "a.id_poli = ".QuoteValue(DPE_CHAR,$_POST["id_poli"]);

      $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status_pasien, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
			   a.reg_batal,d.usr_name,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
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
     $sql.= " and cust_usr_kode<>'500' and cust_usr_kode<>'100' and (a.reg_batal is null or reg_batal='n') ";
     $sql.= "order by a.reg_tanggal asc,a.reg_waktu asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
      //echo $sql;
     
	   // --- ngitung jml data e ---
	  $sql = "select count(reg_id) as total
            from   klinik.klinik_registrasi a 
            join   global.global_customer_user b on a.id_cust_usr = b.cust_usr_id
            left join klinik.klinik_perawatan k on k.id_reg=a.reg_id
            where cust_usr_kode<>'500' and cust_usr_kode<>'100'";
            $sql .= " and ".implode(" and ",$sql_where);
            $sql.= " and (a.reg_batal is null or reg_batal='n') ";
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;

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
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Kelamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
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
     
	 
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;    
     
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
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_who_update"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
         
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
		
     }
     
     $colspan = count($tbHeader[0]);
     
      //ambil nama poli
      $sql = "select b.poli_nama, b.poli_id from global.global_auth_poli b where poli_id = ".QuoteValue(DPE_CHAR,$_GET["id_poli"])   ; 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->Fetch($rs_edit);
      
       // ambil jenis pasien
       $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
       $jenisPasien = $dtaccess->FetchAll($rs); 
         
       // ambil jenis pasien
       $sql = "select * from global.global_shift where shift_id = '".$_GET["shift"]."'";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
       $dataShift = $dtaccess->Fetch($rs); 
        
        //Data Klinik
        $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
        $rs = $dtaccess->Execute($sql);
        $dataKlinik = $dtaccess->FetchAll($rs);
        //echo $sql;        
       $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
       $rs = $dtaccess->Execute($sql);
       $konfigurasi = $dtaccess->Fetch($rs);
       
        if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
        if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
        //$fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];
        $lokasi = $ROOT."/gambar/img_cfg";   
        
        if($konfigurasi["dep_logo"]!="n") {
        $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
        } elseif($konfigurasi["dep_logo"]=="n") { 
        $fotoName = $lokasi."/default.jpg"; 
        } else { $fotoName = $lokasi."/default.jpg"; }    
  
?>
<script language="JavaScript">

window.print();

</script>
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3">
		<?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>  
  </tr>
</table>

  <br>
 <table border="0" colspan="2" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
    <tr>
      <td width="30%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tgl_awal"];?> - <?php echo $_GET["tgl_akhir"];?></td>
      <td width="70%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">
     LAPORAN KUNJUNGAN PASIEN <? if($_GET["tipe"]=='J'){ echo "IRJ";}elseif($_GET["tipe"]=='G'){echo "IGD";}elseif($_GET["tipe"]=='I'){echo "IRNA";}?> </td>
    </tr>
    <? if ($_GET["shift"]!="--") { ?>
    <!--<tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php echo $dataShift["shift_nama"];?></td>
    </tr>
    <? } ?> -->
    <? if ($_GET["id_poli"]!="--") { ?>
    <tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Rawat Jalan : <?php echo $dataPoli["poli_nama"];?></td>
    </tr>
    <? } ?>
  </table>
 <br>
<br>  
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table> 


