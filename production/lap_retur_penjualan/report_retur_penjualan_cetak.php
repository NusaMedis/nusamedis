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
     //$theDep = "2";   //apotik
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $thisPage = "report_penjualan.php";
     $cetakPage = "report_penjualan_cetak.php?";
     
     // ambil gudang konfigurasi apotik
	   $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     
     if($gudang["conf_gudang_obat"]=='L'){
          $theDep = "1";
     }else{
          $theDep = $auth->GetNamalogistik();
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
	   $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $cetakPage = "report_penjualan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
 
     if($depId && $depId!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$depId."%");
     $sql_where[] = "date(a.retur_penjualan_when_update) >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "date(a.retur_penjualan_when_update) <= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));
     $sql_where[] ="a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
	   $sql_where[] = "c.item_nama is not null and a.retur_penjualan_lunas = 'y'";
	   if($waktuMulai) $sql_where[] = "CAST(a.retur_penjualan_when_update as TIME) >= ".QuoteValue(DPE_DATE,$waktuMulai);
     if($waktuselesai) $sql_where[] = "CAST(a.retur_penjualan_when_update as TIME) <= ".QuoteValue(DPE_DATE,$waktuselesai);
    
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.retur_penjualan_id,a.retur_penjualan_when_update,a.retur_penjualan_total,a.retur_penjualan_nomor,d.penjualan_nomor,e.cust_usr_nama,
             a.retur_penjualan_who_create,c.item_nama,b.retur_penjualan_detail_jumlah,b.retur_penjualan_detail_total
              from logistik.logistik_retur_penjualan a 
             left join logistik.logistik_retur_penjualan_detail b on b.id_penjualan_retur = a.retur_penjualan_id
             left join logistik.logistik_item c on b.id_item = c.item_id
             left join apotik.apotik_penjualan d on d.penjualan_id = a.id_penjualan
             left join global.global_customer_user e on e.cust_usr_id = d.id_cust_usr";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.retur_penjualan_urut asc";
     //echo $sql;
          
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  $table = new InoTable("table1","100%","left",null,0,2,1,null);     
  $PageHeader = "Laporan Retur Penjualan";
 
	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "No. Retur";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "No. Faktur/Resep";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	//$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Petugsa";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	/*$tbHeader[0][$counter][TABLE_ISI] = "Diskon";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "GrandTotal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;*/
	
  $counter=0;
	$tbHeader[1][$counter][TABLE_ISI] = "";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  /*$tbHeader[1][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  */
  
  $tbHeader[1][$counter][TABLE_ISI] = "Jumlah Retur";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Sub Total";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  /*$tbHeader[1][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;*/

	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	  
	  if($dataTable[$i]["retur_penjualan_id"]!=$dataTable[$i-1]["retur_penjualan_id"]){
	  //hitung total
	  //$total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung total Tax
	  // $totalTax+=$dataTable[$i]["penjualan_ppn"];
	  $num++;
		$tbContent[$m][$counter][TABLE_ISI] = $num;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
	  
	  $tanggal= explode(" ", $dataTable[$i]["retur_penjualan_when_update"]);
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($tanggal[0])."-".$tanggal[1];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["retur_penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

    //$dataTable[$i]["penjualan_total"] = ($dataTable[$i]["penjualan_total"] + $dataTable[$i]["penjualan_biaya_resep"] + $dataTable[$i]["penjualan_biaya_racikan"] + $dataTable[$i]["penjualan_biaya_bhps"] ) - $dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_pembulatan"];
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["retur_penjualan_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		///$tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;
		$totalJual += $dataTable[$i]["retur_penjualan_total"];
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["retur_penjualan_who_create"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		/*$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_diskon"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$diskon += $dataTable[$i]["penjualan_diskon"];
		
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_grandtotal"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$grandTotal += $dataTable[$i]["penjualan_grandtotal"];*/
		
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

  	$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["retur_penjualan_detail_jumlah"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
  	$jml += $dataTable[$i]["retur_penjualan_detail_jumlah"];
  
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["retur_penjualan_detail_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;		
		
		$total_transaksi_harga_jual = $dataTable[$i]["retur_penjualan_detail_total"] * $dataTable[$i]["retur_penjualan_detail_jumlah"];
		$total += $total_transaksi_harga_jual;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
    
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($total_transaksi_harga_jual)."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		/*$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;*/
		
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
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalJual)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($total)."&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";
	$counter++;	
	
	/*$tbBottom[0][$counter][TABLE_ISI]   = currency_format($diskon);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($grandTotal);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
	*/
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_penjualan.xls');
     }
	
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
     
	
	
?>



<script language="javascript" type="text/javascript">

window.print();

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
  <td width="60%" rowspan="2" style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">LAPORAN RETUR PENJUALAN</td>
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
