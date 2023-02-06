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
     
     if(!$_GET['tgl_awal']){
     $_GET['tgl_awal']  = $skr;
     }
     if(!$_GET['tgl_akhir']){
     $_GET['tgl_akhir']  = $skr;
     }
     
     //untuk mencari tanggal
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]);
          
     //untuk mencari tanggal
     if($_GET["tgl_awal"]) $sql_where[] = "reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
 
           
   if($_GET["poli"]){
    $sql_where[] = " a.id_poli = ".QuoteValue(DPE_CHAR,$_GET["poli"]);
   }
   if($_GET["tipe"]){
    $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["tipe"]);
   }
   
   if($_GET["id_perusahaan"]){
    $sql_where[] = " a.id_perusahaan = ".QuoteValue(DPE_CHAR,$_GET["id_perusahaan"]);
   }
   
   if($_GET["kondisi"]){
    $sql_where[] = " a.reg_status_kondisi = ".QuoteValue(DPE_CHAR,$_GET["kondisi"]);
   }

   if($_GET["deskripsi"]){
    $sql_where[] = " a.reg_status_kondisi_deskripsi = ".QuoteValue(DPE_CHAR,$_GET["deskripsi"]);
   }

   if($_GET["dokter"]) $sql_where[] = "a.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["dokter"]);
     
    //Pilih Poli
    $sql_where[] = "reg_tipe_rawat = 'J'";

      $sql = "select b.cust_usr_kode, b.cust_usr_nama, b.cust_usr_alamat, b.cust_usr_tanggal_lahir, b.cust_usr_jenis_kelamin,f.dep_nama, 
               a.reg_jenis_pasien, a.reg_shift,a.reg_asal,a.reg_status,a.reg_bayar,a.reg_kode_trans, a.reg_kartu,a.reg_keterangan, a.reg_waktu, a.reg_tanggal, 
			   a.reg_batal,d.usr_name as dokter,jenis_nama, a.id_poli, c.poli_nama, ((current_date - cust_usr_tanggal_lahir)/365) as umur, b.cust_usr_umur,
			   g.perusahaan_nama, h.jamkesda_kota_nama, i.jkn_nama, a.reg_who_update, a.reg_tipe_layanan, j.tipe_biaya_nama, a.id_pembayaran,
               a.reg_icd, k.rawat_diagnosa_utama, k.rawat_who_insert_icd,l.kondisi_akhir_pasien_nama from klinik.klinik_registrasi a 
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
			   left join global.global_kondisi_akhir_pasien l on l.kondisi_akhir_pasien_id=a.reg_status_kondisi";
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
            where a.reg_tipe_rawat='J' and cust_usr_kode<>'500' and cust_usr_kode<>'100'";
            $sql .= " and ".implode(" and ",$sql_where);
            $sql.= " and (a.reg_batal is null or reg_batal='n') ";
    $rsNum = $dtaccess->Execute($sql);
    $numRows = $dtaccess->Fetch($rsNum);
    //echo $sql;

     $tableHeader = "Laporan Status Pasien";
  
     // --- construct new table ---- //
     $counterHeader = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Registrasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.Rm";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "22%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;   
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;	   
	 
	 /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Status Pembayaran";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++; */
	 	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Kondisi Akhir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
	 
	 $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";     
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
		  
          $tbContent[$i][$counter][TABLE_ISI] = strtoupper($dataTable[$i]["reg_who_update"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dokter"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;  
 	   
		
     }
     
     $colspan = count($tbHeader[0]);
    
     

           //ambil nama poli
      $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where poli_id = ".QuoteValue(DPE_CHAR,$_GET["poli"]); 
      $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->Fetch($rs_edit);
//echo $sql;
      
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
     LAPORAN STATUS PASIEN IRJ <? if($_GET["tipe"]=='J'){ echo "IRJ";}elseif($_GET["tipe"]=='G'){echo "IGD";}elseif($_GET["tipe"]=='I'){echo "IRNA";}?> </td>
    </tr>
    <? if ($_GET["shift"]!="--") { ?>
    <!--<tr>
      <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Shift : <?php echo $dataShift["shift_nama"];?></td>
    </tr>
    <? } ?> -->
    <tr>
      <? if ($_GET["id_poli"]!="--") { ?>
       <td width="10%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik : <? if ($_GET["poli"]!="--") { echo $dataPoli["poli_nama"];} ?></td>
       <td colspan="2"> </td>
       <?php } ?>
    </tr>
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


