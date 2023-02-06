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
     $poli = $auth->GetPoli();
     //DIPATEN SEMENTARA
     $poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     //$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
         
    // PRIVILLAGE
   /*  if(!$auth->IsAllowed("apo_lap_penjualan",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_lap_penjualan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     // ambil gudang konfigurasi apotik
	   $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
          	   	   
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
     
     
	   $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR, $depId);

     $sql_where[] = "date(b.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_awal"]));
     $sql_where[] = "date(b.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_GET["tanggal_akhir"]));
     $sql_where[] ="b.id_gudang =".QuoteValue(DPE_CHAR,$_GET['klinik']);
//	   $sql_where[] = "c.item_nama is not null and a.penjualan_terbayar = 'y'";
    
    if ($sql_where[0]) 
	  $sql_where = implode(" and ",$sql_where);
     
  $sql = "select sum(penjualan_detail_jumlah) as total, c.item_nama,c.item_harga_jual,b.id_gudang,gudang_nama from apotik.apotik_penjualan_detail a 
             left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
             left join logistik.logistik_item c on a.id_item = c.item_id
             left join logistik.logistik_gudang d on d.gudang_id = b.id_gudang
             where b.penjualan_total > 0 and c.item_flag='M' and item_tipe_jenis ='2' ";
  $sql .= " and ".$sql_where;
  $sql .= " group by c.item_nama,c.item_harga_jual,b.id_gudang,gudang_nama";
  $sql .= " order by item_nama asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  
  //*-- config table ---*//
  $table = new InoTable("table1","100%","center",null,0,2,1,null);     
  $PageHeader = "Laporan Penjualan";
 
	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "3%";
  $counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Nama Obat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total Terjual";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

  $tbHeader[0][$counter][TABLE_ISI] = "Klinik";
  $tbHeader[0][$counter][TABLE_WIDTH] = "10%";
  $counter++;
/*
	$tbHeader[0][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Total Penjualan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

  $num = 1;  */
	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){

		$tbContent[$m][$counter][TABLE_ISI] = $i+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;	  
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["total"])."&nbsp;&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;

    $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["gudang_nama"];
    $tbContent[$m][$counter][TABLE_ALIGN] = "left";
    $counter++;

		$jml += $dataTable[$i]["total"]; 
 /*
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_harga_jual"])."&nbsp;&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
    $totaljual = $dataTable[$i]["total"]*$dataTable[$i]["item_harga_jual"];

		$tbContent[$m][$counter][TABLE_ISI] = currency_format($totaljual)."&nbsp;&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$num++;
   $jmltotaljual += $totaljual; */
		}
	

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<!--<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">-->&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	//$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($jml)."&nbsp;&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
/*
	$tbBottom[0][$counter][TABLE_ISI]   = "&nbsp;&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;

	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($jmltotaljual)."&nbsp;&nbsp;";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
*/	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];
	
	       if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
	
	 //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
    
    $sql = "select gudang_nama from logistik.logistik_gudang where gudang_id = '".$_GET['klinik']."'";
    $rs = $dtaccess->Execute($sql);
    $dataGudang = $dtaccess->Fetch($rs);


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

      <td style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent"><?php if($_GET["tanggal_awal"]==$_GET["tanggal_akhir"]) { echo "Tanggal : ".$_GET["tanggal_awal"]; } elseif($_GET["tanggal_awal"]!=$_GET["tanggal_akhir"]) { echo "Periode : ".$_GET["tanggal_awal"]." - ".$_GET["tanggal_akhir"]; }  ?></td>
  <td style="text-align:right;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">REKAP PENJUALAN</td>
    </tr>
   <tr>
      <td width="100%" colspan ="2" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Apotik : <?php echo $dataGudang["gudang_nama"]; ?> </td>
      <!--<td width="33%" style="text-align:right;font-size:12px;font-family:sans-serif;font-weight:bold;" class="tablecontent">Jenis Pasien : <?php //echo $bayarPasien[$_GET["cust_usr_jenis"]];  ?> </td> -->
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

 
