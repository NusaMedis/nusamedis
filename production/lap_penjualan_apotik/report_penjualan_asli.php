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
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $poli = $auth->GetPoli();
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];
     $thisPage = "report_penjualan.php";
     $cetakPage = "report_penjualan_cetak.php?";
     
     // PRIVILLAGE
     if(!$auth->IsAllowed("apo_penj_lappenjualan",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penj_lappenjualan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
 
     if($depId && $depId!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$depId."%");
     $sql_where[] = "date(a.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(a.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     $sql_where[] ="a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
                                                                                                     
    // UPPER(template_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%")                    
     if($_POST["_nama"] && $_POST["_nama"]!="") $sql_where[] = "UPPER(d.cust_usr_nama) like  '".strtoupper($_POST["_nama"])."%' ";
     if($_POST["kode"] && $_POST["kode"]!="") $sql_where[] = "d.cust_usr_kode =  '".$_POST["kode"]."' ";
     if($_POST["id_jenis_pasien"]) $sql_where[] = "a.id_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["id_jenis_pasien"]);
     if($_POST["reg_tipe_rawat"]) $sql_where[] = "h.reg_tipe_rawat=".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);
     
	   $sql_where[] = "c.item_nama is not null and a.penjualan_terbayar = 'y'";
	   //if($waktuMulai) $sql_where[] = "CAST(a.penjualan_create as TIME) >= ".QuoteValue(DPE_DATE,$waktuMulai);
     //if($waktuselesai) $sql_where[] = "CAST(a.penjualan_create as TIME) <= ".QuoteValue(DPE_DATE,$waktuselesai);
    
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     $sql = "select g.batch_no, e.usr_name,c.item_tipe_jenis,d.cust_usr_nama,a.cust_usr_nama as nama, d.cust_usr_id,
             c.item_nama,b.penjualan_detail_jumlah,a.penjualan_nomor,
             b.penjualan_detail_harga_jual,b.penjualan_detail_total,b.penjualan_detail_tuslag,
             a.penjualan_create, a.penjualan_grandtotal, a.no_resep,a.penjualan_total,a.dokter_nama,a.penjualan_id,b.id_penjualan,
             a.penjualan_biaya_resep, a.penjualan_biaya_racikan, a.penjualan_biaya_bhps, a.penjualan_diskon,
             a.penjualan_biaya_pembulatan, f.dep_nama,d.cust_usr_nama,d.cust_usr_kode, a.penjualan_tuslag
             from apotik.apotik_penjualan a 
             left join apotik.apotik_penjualan_detail b on b.id_penjualan = a.penjualan_id
             left join logistik.logistik_item c on b.id_item = c.item_id
             left join global.global_customer_user d on d.cust_usr_id = a.id_cust_usr
             left join global.global_auth_user e on e.usr_id = a.who_update
             left join global.global_departemen f on f.dep_id = a.id_dep
             left join logistik.logistik_item_batch g on g.batch_id = b.id_batch
             left join klinik.klinik_registrasi h on a.id_reg = h.reg_id";
     $sql .= " where ".$sql_where;
     $sql .= " order by a.penjualan_create desc,item_nama asc";
//     echo $sql;
          
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
  
  $tbHeader[1][$counter][TABLE_ISI] = "Tuslag";
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
	  $tbContent[$m][$counter][TABLE_ISI] = format_date($tanggal[0])." ".$tanggal[1];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		if($dataTable[$i]["cust_usr_nama"]) $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		else $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

    //$dataTable[$i]["penjualan_total"] = ($dataTable[$i]["penjualan_total"] + $dataTable[$i]["penjualan_biaya_resep"] + $dataTable[$i]["penjualan_biaya_racikan"] + $dataTable[$i]["penjualan_biaya_bhps"] ) - $dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_pembulatan"];
		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		///$tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;
		$totalJual += $dataTable[$i]["penjualan_total"];

		$tbContent[$m][$counter][TABLE_ISI] = currency_format($dataTable[$i]["penjualan_biaya_resep"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$totalResep += $dataTable[$i]["penjualan_biaya_resep"];
		
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
    		
	  $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["penjualan_detail_tuslag"])."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
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
	
	$tbBottom[0][$counter][TABLE_ISI]   = currency_format($totalResep);
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
	
  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_penjualan.xls');
     }
	
	if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
   
   $sql = "select * from global.global_jenis_pasien where jenis_flag='y' order by jenis_id asc";
   $rs = $dtaccess->Execute($sql);
   $dataJenisPasien = $dtaccess->FetchAll($rs);
	
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
  BukaWindow('report_penjualan_cetak.php?nama=<?php echo $_POST["nama"];?>&kode=<?php echo $_POST["kode"];?>&tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&jenis=<?php echo $_POST["id_jenis_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>&reg_tipe_rawat=<?php echo $_POST["reg_tipe_rawat"];?>','Pemakaian Kasir');
	document.location.href='<?php echo $thisPage;?>';
<?php } ?>
  
</script> 

<?php if(!$_POST["btnExcel"]) { ?>
<?php //echo $view->RenderBody("module.css",true,true,"LAPORAN PENJUALAN OBAT"); ?>

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
          <td width="30%" class="tablecontent" colspan="2">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
               - 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
          <td width="10%" class="tablecontent">&nbsp;Nama Pasien  </td>
                   <td width="30%" class="tablecontent" colspan="3">
                   <input  type="text" name="_nama" id="_nama" size="10" maxlength="50" value="<?php echo $_POST["_nama"]; ?>">
                   </td>
                  
          </tr>
                  <tr>
                 <td width="10%" class="tablecontent">&nbsp;Waktu</td>
          <td width="30%" class="tablecontent" colspan="2">
				<select name="op_mulai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_mulai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_mulai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				s/d
				<select name="op_selesai_jam" class="inputField" >
					<?php for($i=0,$n=24;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
					</select>:
					<select name="op_selesai_menit" class="inputField" >
					<?php for($i=0,$n=60;$i<$n;$i++){ ?>
						<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				
               </td>
                <td width="10%" class="tablecontent">&nbsp;No RM  </td>
                   <td width="30%" class="tablecontent" colspan="3">
                   <input  type="text" name="kode" id="kode" size="10" maxlength="10" value="<?php echo $_POST["kode"]; ?>">
                   </td>
       </tr>
       <tr>
          <td width="10%" class="tablecontent">&nbsp;Cara Bayar  </td>
          <td width="30%" class="tablecontent" colspan="2">
			       <select name="id_jenis_pasien" id="id_jenis_pasien" onKeyDown="return tabOnEnter(this,event)">
              <option value="">Pilih Cara Bayar</option>
              <?php for($i=0,$n=count($dataJenisPasien);$i<$n;$i++) { ?>
              <option value="<?php echo $dataJenisPasien[$i]["jenis_id"]; ?>" <?php if($_POST["id_jenis_pasien"]==$dataJenisPasien[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenisPasien[$i]["jenis_nama"]; ?></option>
              <?php } ?>
             </select>
                   </td>
                   <td width="10%" class="tablecontent">&nbsp;Instalasi  </td>
                   <td width="30%" class="tablecontent">
                   <select name="reg_tipe_rawat" id="reg_tipe_rawat">
                        <option value="" > [Semua Instalasi] </option>
                        <option value="J" <?php if($_POST["reg_tipe_rawat"] =="J"){echo "selected";}?>>Rawat Jalan</option>
                        <option value="I" <?php if($_POST["reg_tipe_rawat"] =="I"){echo "selected";}?>>Rawat Inap</option>
                        <option value="G" <?php if($_POST["reg_tipe_rawat"] =="G"){echo "selected";}?>>Gawat Darurat</option>
                   </select>
                   </td>     
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
               <td width="100%" align="center" colspan="<?php echo (count($dataSplit)+6)?>">
               <strong>LAPORAN PENJUALAN OBAT<br/>
               <?php echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php echo $konfigurasi["dep_kop_surat_2"]?>
               <br/>
               </strong>
               </td>          
          </tr>
          <tr class="tableheader">
          <td align="left" colspan="<?php echo (count($dataSplit)+6)?>">
          <?php echo $poliNama; ?><br/>
          <?php if($_POST["tanggal_awal"]==$_POST["tanggal_akhir"]) { echo "Tanggal : ".$_POST["tanggal_awal"]; } elseif($_POST["tanggal_awal"]!=$_POST["tanggal_akhir"]) { echo "Periode : ".$_POST["tanggal_awal"]." - ".$_POST["tanggal_akhir"]; }  ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php echo "Waktu Shift : ".$_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"]." - ".$_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"]; ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php if($_POST["cust_usr_jenis"]) { echo "Jenis Pasien : ".$bayarPasien[$_POST["cust_usr_jenis"]]; } ?>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php //echo "Nama Poli : ".$dataPoli[$i]["poli_nama"]; ?>
                        
               <br/>
          </td>
          </tr>
     </table>
<?php }?>	
<br />
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

<?php if($_POST["btnLanjut"] || $_POST["btnCetak"]) {?>
		 
     </div>
		 </div>

<?php }?>
		
<?php ////echo $view->RenderBottom("module.css",$userName,false,$depNama);?>  
 <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<?php //echo $view->RenderBodyEnd(); ?>     
 <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />                                               
  
</body>