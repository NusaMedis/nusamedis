<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");

     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
//     $poli = $auth->GetPoli();
     
     //DIPATEN SEMENTARA
     // $poli = "33"; //POLI APOTIK IRJ
     // $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     // $rs = $dtaccess->Execute($sql);
     // $gudang = $dtaccess->Fetch($rs); 
     // $theDep = $gudang["id_gudang"];
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
         
    // PRIVILLAGE
     /*if(!$auth->IsAllowed("apo_penj_lappenjualan",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penj_lappenjualan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     // ambil gudang konfigurasi apotik
     	   	   
      if($depId=='01') {
             if($_POST["btnLanjut"])  {  
                    if(!$_POST["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; } 
             } else {
                    if (!$_GET["klinik"]) { $_POST["klinik"] = $depId; }  else if(!$_POST["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; } 
             }
     }else{
            if (!$_GET["klinik"]) { $_POST["klinik"] = $depId; }  else if(!$_POST["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; } 
     } 
     
	   
	   
	   $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_GET['tanggal_awal']){
     $_GET['tanggal_awal']  = $skr;
     }
     if(!$_GET['tanggal_akhir']){
     $_GET['tanggal_akhir']  = $skr;
     }
     
     
     if($_GET["op_mulai_jam"]){
     $_GET["op_mulai_jam"]= $_GET["op_mulai_jam"];
     }else{
     $_GET["op_mulai_jam"]= 0;
     }
     
     if($_GET["op_mulai_menit"]){
     $_GET["op_mulai_menit"]= $_GET["op_mulai_menit"];
     }else{
     $_GET["op_mulai_menit"]= 0;
     }
     
     if($_GET["op_selesai_jam"]){
     $_GET["op_selesai_jam"]= $_GET["op_selesai_jam"];
     }else{
     $_GET["op_selesai_jam"]= date('H');
     }
     
     if($_GET["op_selesai_menit"]){
     $_GET["op_selesai_menit"]= $_GET["op_selesai_menit"];
     }else{
     $_GET["op_selesai_menit"]= date('i');
     }
     
     
     $detik = date('s');
     
     $waktuMulai = $_GET["op_mulai_jam"].":".$_GET["op_mulai_menit"].":".$detik;
     $waktuselesai = $_GET["op_selesai_jam"].":".$_GET["op_selesai_menit"].":".$detik;
     
	   $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR, $depId);
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_gudang like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
     $sql_where[] = "date(a.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "date(a.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));
          if($_GET["nama"] && $_GET["nama"]!="") $sql_where[] = "UPPER(d.cust_usr_nama) like  '".strtoupper($_GET["nama"])."%' ";
          if($_GET["kode"] && $_GET["kode"]!="") $sql_where[] = "d.cust_usr_kode =  '".$_GET["kode"]."' ";
          if($_GET["jenis"]) $sql_where[] = "a.id_jenis_pasien =  '".$_GET["jenis"]."' ";
          if($_GET["reg_tipe_rawat"]) $sql_where[] = "h.reg_tipe_rawat=".QuoteValue(DPE_CHAR,$_GET["reg_tipe_rawat"]);     

     //$sql_where[] ="a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);

//     SEMENTARA DITUTUP DULU
//	   $sql_where[] = "c.item_nama is not null and a.penjualan_terbayar = 'y'";
    
    if ($sql_where[0]) 
	  $sql_where = implode(" and ",$sql_where);
     
     $sql = "select g.batch_no, e.usr_name,c.item_tipe_jenis,d.cust_usr_nama,a.cust_usr_nama as nama, d.cust_usr_id,
             c.item_nama,b.penjualan_detail_jumlah,a.penjualan_nomor,
             b.penjualan_detail_harga_jual,b.penjualan_detail_total,b.penjualan_detail_tuslag,
             a.penjualan_create, a.penjualan_grandtotal, a.no_resep,a.penjualan_total,a.dokter_nama,a.penjualan_id,b.id_penjualan,
             a.penjualan_biaya_resep, a.penjualan_biaya_racikan, a.penjualan_biaya_bhps, a.penjualan_diskon,
             a.penjualan_biaya_pembulatan, f.dep_nama,d.cust_usr_nama,d.cust_usr_kode, a.penjualan_tuslag, gudang_nama
             from apotik.apotik_penjualan a 
             left join apotik.apotik_penjualan_detail b on b.id_penjualan = a.penjualan_id
             left join logistik.logistik_item c on b.id_item = c.item_id
             left join global.global_customer_user d on d.cust_usr_id = a.id_cust_usr
             left join global.global_auth_user e on e.usr_id = a.who_update
             left join global.global_departemen f on f.dep_id = a.id_dep
             left join logistik.logistik_item_batch g on g.batch_id = b.id_batch
             left join klinik.klinik_registrasi h on a.id_reg = h.reg_id
             left join logistik.logistik_gudang i on i.gudang_id = a.id_gudang";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create desc,a.penjualan_nomor,item_nama asc";
     //echo $sql;
          
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  $table = new InoTable("table1","100%","left",null,0,2,1,null);     
  $PageHeader = "Laporan Penjualan";
 
	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_ROWSPAN] = "2";
	$tbHeader[0][$counter][TABLE_WIDTH] = "3%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	//$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Biaya Resep";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Kasir";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Diskon";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "GrandTotal";
	$tbHeader[0][$counter][TABLE_ROWSPAN] = "2";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Klinik";
	$tbHeader[0][$counter][TABLE_ROWSPAN] = "2";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
  $counter=0;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Jumlah";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No. Batch";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Sub Total";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "&nbsp";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	  
	  if($dataTable[$i]["penjualan_id"]!=$dataTable[$i-1]["penjualan_id"]){
	  //hitung total
	  //$total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung total Tax
	  // $totalTax+=$dataTable[$i]["penjualan_ppn"];
	  $num++;
		$tbContent[$m][$counter][TABLE_ISI] = $num;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tanggal= explode(" ", $dataTable[$i]["penjualan_create"]);
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($tanggal[0])."-".$tanggal[1];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		if($dataTable[$i]["cust_usr_kode"] == '100') $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["nama"];
		else $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

    //$dataTable[$i]["penjualan_total"] = ($dataTable[$i]["penjualan_total"] + $dataTable[$i]["penjualan_biaya_resep"] + $dataTable[$i]["penjualan_biaya_racikan"] + $dataTable[$i]["penjualan_biaya_bhps"] ) - $dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_pembulatan"];
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		///$tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;
		$totalJual += $dataTable[$i]["penjualan_total"];

		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_biaya_racikan"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$totalRacikan += $dataTable[$i]["penjualan_biaya_racikan"];
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_diskon"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$diskon += $dataTable[$i]["penjualan_diskon"];
		
		$grand = $dataTable[$i]["penjualan_total"]-$dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_resep"];
		
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($grand);
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$grandTotal += $grand;
		
		$j=0;$m++;$counter=0;
    }
		
    $j++;
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
    $tbContent[$m][$counter][TABLE_ISI] = $j."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;

  	$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["penjualan_detail_jumlah"])."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
  	$jml += $dataTable[$i]["penjualan_detail_jumlah"];
  
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["penjualan_detail_harga_jual"])."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;				
   
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["batch_no"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["penjualan_detail_total"])."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$total += $dataTable[$i]["penjualan_detail_total"];
    		
	  $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;


		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]['gudang_nama'];
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<!--<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">-->&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($jml);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalJual);      
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalRacikan);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($diskon);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($grandTotal);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
		    
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	       if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
	
	 //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
    
    

  $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
  $lokasi = $ROOT."/gambar/img_cfg";
  if($konfigurasi["dep_logo"]) $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
      else $fotoName = $lokasi."/default.jpg";
     
	
    if($_GET["reg_tipe_rawat"] =="J"){ $regTipeRawat = "RAWAT JALAN";}
    else if($_GET["reg_tipe_rawat"] =="I"){$regTipeRawat = "RAWAT INAP";}
    else if($_GET["reg_tipe_rawat"] =="G"){$regTipeRawat = "GAWAT DARURAT";}
    else{
    $regTipeRawat = "";
    }	
?>

<?php //echo $view->RenderBody("module.css",false,false,"CETAK LAP PENJUALAN"); ?>


<script language="javascript" type="text/javascript">

window.print();

</script>

</script>
<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table border="0" cellpadding="2" rowspan="3" cellspacing="0" align="center">
    <tr>
      <td rowspan="3" width="25%" class="tablecontent"><img src="<?php echo $fotoName ;?>" height="50"></td>
      <td style="text-align:left;font-size:16px;font-family:times new roman;font-weight:bold;" class="tablecontent">
      <?php echo $konfigurasi["dep_nama"]?><BR>
      <?php echo $konfigurasi["dep_kop_surat_1"]?><BR>
      <?php echo $konfigurasi["dep_kop_surat_2"]?></td>
    </tr>
  </table>
<br>
 <table border="0" cellpadding="3" cellspacing="0" style="align:left" width="100%">     
   <tr>
      <td width="100%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Periode : <?php echo $_GET["tanggal_awal"];?> - <?php echo $_GET["tanggal_akhir"];?></td>
    </tr>
   <tr>
      <td width="100%" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Waktu Shift : <?php echo $_GET["op_mulai_jam"].":".$_GET["op_mulai_menit"]." - ".$_GET["op_selesai_jam"].":".$_GET["op_selesai_menit"]; ?> </td>
      <!--<td width="33%" style="text-align:right;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Pasien : <?php //echo $bayarPasien[$_GET["cust_usr_jenis"]];  ?> </td> -->
    </tr>
  <tr>
  <td width="60%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN PENJUALAN <?php echo $regTipeRawat;?></td>
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


<?php //echo $view->RenderBodyEnd(); ?>

 
