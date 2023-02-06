<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $poli = $auth->GetPoli();
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
//    $plx = new expAJAX("GetData");
     $userData = $auth->GetUserData();     
	   $userName = $auth->GetUserName();

     $thisPage = "rekap_penjualan.php";
     $cetakPage = "rekap_penjualan_cetak.php?";

     // PRIVILLAGE
 if(!$auth->IsAllowed("apo_pen_rekappenjualan",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_pen_rekappenjualan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  
     // ambil gudang konfigurasi apotik
	   $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
          
     $skr = date("d-m-Y");
     $time = date("H:i:s");

     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }

	   $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR, $depId);
     $cetakPage = "report_penjualan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];

if($_POST["btnLanjut"] || $_POST["btnExcel"]){ 

     $sql_where[] = "date(b.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(b.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     $sql_where[] ="b.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
//	   $sql_where[] = "c.item_nama is not null and a.penjualan_terbayar = 'y'";

     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
       
  $sql = "select sum(penjualan_detail_jumlah) as total, c.item_nama,c.item_harga_jual from apotik.apotik_penjualan_detail a 
             left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
             left join logistik.logistik_item c on a.id_item = c.item_id
             where c.item_flag='M' and item_tipe_jenis ='2' ";
  $sql .= " and ".$sql_where;
  $sql .= " group by c.item_nama,c.item_harga_jual";
  $sql .= " order by item_nama asc";
	$rs = $dtaccess->Execute($sql);
	$dataTable = $dtaccess->FetchAll($rs);
//echo $sql;  
}
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
/*
	$tbHeader[0][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Total Penjualan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
 */
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
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
	
  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_penjualan.xls');
     }
	
	if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
    $sql = "select gudang_nama from logistik.logistik_gudang where gudang_id = '".$theDep."'";
    $rs = $dtaccess->Execute($sql);
    $dataGudang = $dtaccess->Fetch($rs);
	
	 //Data Klinik
    $sql = "select * from global.global_departemen where dep_id like '".$depId."%' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
    
         $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
	
	
?>




<?php if(!$_POST["btnExcel"]) { ?>



<?php //echo $view->RenderBody("module.css",true,true,"REKAP PENJUALAN OBAT"); ?>
<br /><br /><br /><br />

<?php } ?>
<script language="JavaScript">

  function rejenis(kliniks) {
   document.location.href='report_penjualan.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
  var _wnd_new;

function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
  
<?php if($_x_mode=="cetak"){ ?>
	//if(confirm('Cetak Laporan Penjualan Obat?')) 
  BukaWindow('rekap_penjualan_cetak.php?tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>','Pemakaian Kasir');
	document.location.href='<?php echo $thisPage;?>';
<?php } ?>
  
</script> 

<?php if(!$_POST["btnExcel"]) { ?>
<body>

<div id="body">
<div id="scroller">
<br />
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
<!--<tr class="tablecontent">
          <td width="20%" align="left">&nbsp;Nama Klinik</td>
          <td width="80%" align="left">
			 <select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onchange="rejenis(this.value);">
				<option class="inputField" value="" >- Pilih Klinik -</option>
				<?php /*$counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
					*/
				?>
					<!--<option class="inputField" value="<?php //echo $dataKlinik[$i]["dep_id"];?>"<?php //if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php //echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php // } ?>
				</select>
		  </td>
		 </tr> -->
     <tr>
          <td width="10%" class="tablecontent">&nbsp;Periode  </td>
          <td width="30%" class="tablecontent" colspan="3">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
               - 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
          </tr>
	<tr>
          <td class="tablecontent" >&nbsp;</td>
          <td class="tablecontent" colspan="5">
               <input type="submit" name="btnLanjut" value="Lanjut" class="submit">
			<input type="submit" name="btnExcel" value="Export Excel" class="submit">
			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="submit">
          </td>
     </tr>
</table>
</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tanggal_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tanggal_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>
<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr width="100%" class="tableheader">
               <td width="100%" align="center" colspan="<?php echo (count($dataSplit)+5)?>">
               <strong>LAPORAN PENJUALAN OBAT<br/>
               <?php echo $konfigurasi["dep_nama"]?><br/>
               <?php echo $konfigurasi["dep_kop_surat_1"]?><br/>
               <?php echo $konfigurasi["dep_kop_surat_2"]?>
               <br/>
               </strong>
               </td>          
          </tr>
          <tr class="tableheader">
          <td align="left" colspan="<?php echo (count($dataSplit)+5)?>">
          <?php echo $poliNama; ?><br/>
          <?php if($_POST["tanggal_awal"]==$_POST["tanggal_akhir"]) { echo "Tanggal : ".$_POST["tanggal_awal"]; } elseif($_POST["tanggal_awal"]!=$_POST["tanggal_akhir"]) { echo "Periode : ".$_POST["tanggal_awal"]." - ".$_POST["tanggal_akhir"]; }  ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php echo "Apotik : ".$dataGudang["gudang_nama"]; ?> 
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //if($_POST["cust_usr_jenis"]) { echo "Jenis Pasien : ".$bayarPasien[$_POST["cust_usr_jenis"]]; } ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //echo "Nama Poli : ".$dataPoli[$i]["poli_nama"]; ?>
                        
               <br/>
          </td>
          </tr>
     </table>
<?php }?>	
<br />
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<?php if(!$_POST["btnExcel"]) {?>
		 </div>
		 </div>
<!--
  		<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->
<?php }?>			
</body>
</html>
<?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
 


