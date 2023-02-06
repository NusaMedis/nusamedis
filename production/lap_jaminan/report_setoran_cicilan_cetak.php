<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $thisPage = "report_setoran_cicilan.php";
     $printPage = "report_setoran_cicilan_cetak.php?";
    
   //  if (!$_POST["klinik"]) $_POST["klinik"]=$depId;
       //$_POST["klinik"] = $_GET["klinik"]; 
       
     if($_GET["klinik"]) { $_POST["klinik"]=$_GET["klinik"]; }
      else if(!$_POST["klinik"]) { $_POST["klinik"]=$depId; }
      
     
 	   // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_id"] = $konfigurasi["dep_id"];
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
          
       $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_GET['tgl_awal']){
     $_GET['tgl_awal']  = $skr;
     }
     if(!$_GET['tgl_akhir']){
     $_GET['tgl_akhir']  = $skr;
     }
     
     if(!$_GET["pembayaran_det_flag"]) $_GET["pembayaran_det_flag"]='T';
    
	//cari shift
	   $sql = "select * from global.global_shift order by shift_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShift = $dtaccess->FetchAll($rs);
     

	 
	 if($_GET["klinik"] && $_GET["klinik"]!="--") $sql_where[] = "j.id_dep = ".QuoteValue(DPE_CHAR,$_GET["klinik"]);
     if($_GET["tgl_awal"]) $sql_where[] = "date(j.pembayaran_det_tgl) >= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"]));
     if($_GET["tgl_akhir"]) $sql_where[] = "date(j.pembayaran_det_tgl) <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"]));
     if($_GET["js_biaya"]) $sql_where[] = "a.pembayaran_jenis = ".QuoteValue(DPE_CHAR,$_GET["js_biaya"]);
     if($_GET["jbayar"]) $sql_where[] = "j.id_jbayar = ".QuoteValue(DPE_CHAR,$_GET["jbayar"]);
     if($_GET["dokter"]) $sql_where[] = "d.id_dokter = ".QuoteValue(DPE_CHAR,$_GET["dokter"]);
     if($_GET["id_poli"]<>'--') $sql_where[] = "d.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
     // if($_GET["pembayaran_det_flag"]<>'--') $sql_where[] = "j.pembayaran_det_flag = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_flag"]);
     
     
   if($_GET["shift"]){
		
    
    $sql = "select * from global.global_shift where shift_id=".QuoteValue(DPE_CHAR,$_GET["shift"]);
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataShiftPost = $dtaccess->Fetch($rs);
     
		$sql_where[] = " j.pembayaran_det_create>= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_awal"])." ".$dataShiftPost["shift_jam_awal"])." and j.pembayaran_det_create <= ".QuoteValue(DPE_DATE,date_db($_GET["tgl_akhir"])." ".$dataShiftPost["shift_jam_akhir"]);
	 }
	 
         
	 if($_GET["reg_tipe_layanan"]){
		$sql_where[] = "d.reg_tipe_layanan = ".QuoteValue(DPE_CHAR,$_GET["layanan"]);
	 }
	 
     if($_GET["cust_usr_jenis"] || $_GET["cust_usr_jenis"]!="0"){
		 $sql_where[] = "d.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_jenis"]);
	   }
	   
	  //    if($_GET["perusahaan"]){
		 // $sql_where[] = "d.id_perusahaan = ".QuoteValue(DPE_CHAR,$_GET["perusahaan"]);
	  //  }

	   if($_GET["id_poli"]<>'--'){
		  $sql_where[] = "d.id_poli = ".QuoteValue(DPE_CHAR,$_GET["id_poli"]);
	   }

	   if($_GET["kasir"]<>"--"){
		 $sql_where[] = "j.who_when_update = ".QuoteValue(DPE_CHAR,$_GET["kasir"]);
	   }
 
 // if($_GET["pembayaran_det_flag"]<>"--"){
	// 	 $sql_where[] = "j.pembayaran_det_flag = ".QuoteValue(DPE_CHAR,$_GET["pembayaran_det_flag"]);
	//    }      
	   
 if($_GET["layanan"]<>"--"){
		if($_GET["layanan"]=="A") {$sql_where[] = "(d.reg_status like 'E%' or d.reg_status like 'M%' or d.reg_status='F0' or d.reg_status='A0')";}
   elseif($_GET["layanan"]=="I")
   {$sql_where[] = "d.reg_status like 'I%'";}
   else
   {$sql_where[] = "d.reg_status like 'G%'";}
	   }      

     /*if ($userId<>'b9ead727d46bc226f23a7c1666c2d9fb') {
		   $sql_where[] = "a.pembayaran_who_create = '".$userName."'";
	   }*/
     
	   $sql_where = implode(" and ",$sql_where);

     $sql = "select a.*, j.*, cust_usr_kode, cust_usr_nama, tipe_biaya_nama, 
            usr_name, poli_nama, shift_nama, jenis_nama, jbayar_nama,d.reg_tanggal,d.reg_waktu from klinik.klinik_pembayaran_det j 
            left join klinik.klinik_pembayaran a on j.id_pembayaran = a.pembayaran_id
            left join klinik.klinik_registrasi d on d.reg_id = j.id_reg
            left join global.global_customer_user c on c.cust_usr_id = a.id_cust_usr
            left join global.global_jenis_pasien e on e.jenis_id = d.reg_jenis_pasien
            left join global.global_auth_poli f on f.poli_id = d.id_poli
            left join global.global_shift g on g.shift_id = d.reg_shift
            left join global.global_tipe_biaya h on h.tipe_biaya_id = d.reg_tipe_layanan
            left join global.global_auth_user i on i.usr_id = d.id_dokter
            left join global.global_jenis_bayar k on k.jbayar_id=j.id_jbayar";
     $sql .= " where 1=1 and j.pembayaran_det_flag = 'P' and j.pembayaran_det_tipe_piutang = 'J' and ".$sql_where; 
     //$sql .= " order by pembayaran_create desc, pembayaran_multipayment_create desc, id_reg desc";
     $sql .= " order by j.pembayaran_det_kwitansi, j.pembayaran_det_create, a.pembayaran_id asc";
     //echo $sql;
   $dataTable = $dtaccess->FetchAll($sql);

     for($i=0,$n=count($dataTable);$i<$n;$i++) 
     
   	 $counter=0;
   	 $counterHeader=0;
		
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Transaksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu Masuk Perawatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
	   $counterHeader++;
	   
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
	   //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Transaksi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "6%";
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "12%"; 
	   $counterHeader++;
	   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
	   $counterHeader++;
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
	   //$counterHeader++;
	             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Jaminan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;

    /* $tbHeader[0][$counterHeader][TABLE_ISI] = "Dijamin";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Subsidi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Iur Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hrs Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%"; 
	   $counterHeader++;*/
     	   
	   if ($_GET["id_poli"]=="--")
     {
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
	   $counterHeader++;
     }
	   
	   $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "18%";
	   $counterHeader++;
	   
     
    for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
		//$totcicilan += $dataTable[$i]["cicilan_multipayment_total"];
$sql = "select fol_keterangan from klinik.klinik_folio k
        left join klinik.klinik_pembayaran a on a.pembayaran_id = k.id_pembayaran and a.id_reg = k.id_reg
        where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_id"]);
        $rs = $dtaccess->Execute($sql);
        $dataFolket = $dtaccess->Fetch($rs);
                       
            $tbContent[$i][$counter][TABLE_ISI] = $m+1;
            $tbContent[$i][$counter][TABLE_ALIGN] = "right";
            $counter++;
            $m++;
 
            $daytime = explode(".", $dataTable[$i]["pembayaran_det_create"]);
            $time = explode(" ", $daytime[0]);
            $tbContent[$i][$counter][TABLE_ISI] = format_date($time[0])."&nbsp;".$time[1];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
            
            $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["reg_tanggal"])."&nbsp;".$dataTable[$i]["reg_waktu"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
  
            /*
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "center";
            $counter++;        */
            
            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["pembayaran_det_kwitansi"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "center";
            $counter++;  
            
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
    	
            $nama = explode("-",$dataFolket["fol_keterangan"]);
            if($dataTable[$i]["cust_usr_kode"]=='100' || $dataTable[$i]["cust_usr_kode"]=='500'){
            $tbContent[$i][$counter][TABLE_ISI] = $nama[0];
            } else {
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
            }
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
              
            $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
            $counter++;
            
           // $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
           // $tbContent[$i][$counter][TABLE_ALIGN] = "left";
           // $counter++;
			  
      			$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_det_total"]);
      			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
      			$counter++;

			      $totalPembayaran += $dataTable[$i]["pembayaran_det_total"];
			
      			/*if($dataTable[$i]["pembayaran_det_flag"]=='J'){
            $tbContent[$i][$counter][TABLE_ISI] = currency_format($dijamin = $dataTable[$i]["pembayaran_det_total"]);
            } else {
            $tbContent[$i][$counter][TABLE_ISI] = currency_format($dijamin = 0);
            }
      			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
      			$counter++;
            
            $totalDijamin += $dijamin;
      			  
      			if($dataTable[$i]["pembayaran_det_flag"]=='S'){
            $tbContent[$i][$counter][TABLE_ISI] = currency_format($subsidi = $dataTable[$i]["pembayaran_det_total"]);
            } else {
            $tbContent[$i][$counter][TABLE_ISI] = currency_format($subsisi = 0);
            }
      			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
      			$counter++;
            
            $totalSubsidi += $subsidi;
      			  
      			$tbContent[$i][$counter][TABLE_ISI] = currency_format($iur);
      			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
      			$counter++;
			  
      			$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_det_total"]);
      			$tbContent[$i][$counter][TABLE_ALIGN] = "right";
      			$counter++;*/
      		 
      			if ($_GET["id_poli"]=="--")
            {	
      			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
      			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
      			$counter++;
            }
      			  
      			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
      			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
      			$counter++;
      			  
        }  

        //$total += $dataTable[$i]["fol_dibayar"];
      
     //echo $dijamin."-".$subsidi."-".$iur."-".$hrsBayar; die();
     $counter = 0;
          
	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 8;  
  $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($totalPembayaran);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
  
  if ($_GET["id_poli"]=="--")
  {  
	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 1;
  $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
  }
  else
  {
	$tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
  $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;  
  }
     
     $tableHeader = "Report Pembayaran";

	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembayaran.xls');
     }
     
       if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
     
     //ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_id=".QuoteValue(DPE_NUMERIC,$_GET["reg_jenis_pasien"]);
     $rs = $dtaccess->Execute($sql);
     $jenisPasien = $dtaccess->Fetch($rs);
     
          //Data Klinik
          $sql = "select * from global.global_departemen where dep_id like '".$_POST["klinik"]."%' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
          
          //echo $sql;
          $sql = "select dep_nama from global.global_departemen where dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
          $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
          
        // cari tipe layanan
     $sql = "select * from global.global_tipe_biaya where tipe_biaya_id = '".$_GET["layanan"]."'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $tipeBiayaId = $dtaccess->Fetch($rs);
	 
	 //cari shift by id
			$sql = "select * from global.global_shift where shift_id = '".$_GET["shift"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataShiftId = $dtaccess->Fetch($rs);
			
	 //cari shift by id
			$sql = "select poli_nama from global.global_auth_poli where poli_id = '".$_GET["id_poli"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataPoli = $dtaccess->Fetch($rs);

			//cari nama petugas by id
			$sql = "select * from global.global_auth_user where usr_id = '".$_GET["kasir"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataKasirId = $dtaccess->Fetch($rs);
      
      //cari nama perusahaan
			$sql = "select * from global.global_perusahaan where perusahaan_id = '".$_GET["perusahaan"]."'";
			$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
			$dataPerusahaan = $dtaccess->Fetch($rs);


     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";   
  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
 
  if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }
  
  
?>
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<script language="javascript" type="text/javascript">

window.print();

</script>


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
 <table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
    <tr>
      <td width="100%" style="text-align:center;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN JAMINAN</td>   
    </tr>
    <?php if($_GET["id_poli"]<>'--') { ?> 
    <tr>
       <td width="100%" style="text-align:center;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Klinik/Penunjang : <?php echo $dataPoli["poli_nama"]; ?> </td>
    </tr>
    <? } ?>
    <?php if($_GET["perusahaan"]<>'--') { ?> 
    <tr>
       <td width="100%" style="text-align:center;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Perusahaan : <?php echo $dataPerusahaan["perusahaan_nama"]; ?> </td>
    </tr>
    <? } ?>
    <tr>
      <td width="100%" style="text-align:center;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode Transaksi : <?php echo $_GET["tgl_awal"];?> s/d <?php echo $_GET["tgl_akhir"];?></td>
    </tr>
    <!--
	<tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Nama Petugas : <?php if($_GET["kasir"]=="--"){ echo "--";} else { echo $_GET["kasir"];} ?> </td>
    </tr>-->
  <!--  
	<tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Tipe Layanan : <?php if($_GET["layanan"]=="--"){ echo "--";} else { echo $tipeBiayaId["tipe_biaya_nama"];} ?> </td>
    </tr>
   <?php if($_GET["reg_jenis_pasien"]) { ?> 
    <tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Pasien : <?php echo $jenisPasien["jenis_nama"];?> </td>
    </tr>
    <?php } ?>
    <?php if($_GET["js_biaya"]) { ?> 
    <tr>
       <td width="100%" colspan="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Biaya : <?php if($_GET["js_biaya"]=='T') echo "TUNAI"; else echo "CICILAN"; ?> </td>
    </tr>
    <?php } ?>
    -->
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
