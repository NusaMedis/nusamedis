<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");     
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $usrId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $depLowest = $auth->GetDepLowest();
	   
     $thisPage = "report_penerimaan.php";
     $cetakPage = "report_penerimaan_cetak.php?"; 
     
     // PRIVILLAGE
   /*  if(!$auth->IsAllowed("apo_lap_beli_langsung",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_lap_beli_langsung",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='../../login.php?msg=Session Expired'</script>";
          exit(1);
     }   */


   
      if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
         
    	if($auth->IsAllowed()===1){
    	    include("login.php");
    	    exit();
    	}
	
	   $plx = new expAJAX();
	
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
     $sql_where[] = "c.faktur_tgl >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "c.faktur_tgl <= ".QuoteValue(DPE_DATE,DateAdd(date_db($_POST["tanggal_akhir"]),0));    
     if($_POST["id_sup"] && $_POST["id_sup"]!="--") $sql_where[] = "d.id_sup = ".QuoteValue(DPE_CHAR,$_POST["id_sup"]);
          if($_POST["id_sumber"] && $_POST["id_sumber"]!="--") $sql_where[] = "d.id_sumber = ".QuoteValue(DPE_CHAR,$_POST["id_sumber"]);
     if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "d.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
     $sql_where[] = "f.item_nama is not null";
          if($_POST["id_kategori"] && $_POST["id_kategori"]!="--") $sql_where[] = "f.id_kategori = ".QuoteValue(DPE_CHAR,$_POST["id_kategori"]);
     $cetakPage = "report_penerimaan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"];

          if($_POST["Keterangan_po"] && $_POST["Keterangan_po"]!="--") $sql_where[] = "d.keterangan_po = ".QuoteValue(DPE_CHAR,$_POST["Keterangan_po"]);
    if ($sql_where[0]) 
  	$sql_where = implode(" and ",$sql_where);
	
//	if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
          if ( $_POST["btnLanjut"] || $_POST["btnExcel"] || $_POST["btnCetak"] ) {
     $sql = "select a.*,c.*,g.dep_nama,h.satuan_nama,h.satuan_jumlah,c.faktur_nomor,j.sumber_nama,
             i.sup_nama,d.*,f.*            
             from logistik.logistik_faktur_item a  
             left join logistik.logistik_faktur c on a.id_faktur=c.faktur_id
             left join logistik.logistik_po d on c.id_po=d.po_id
             left join logistik.logistik_item f on f.item_id=a.id_item
             left join global.global_departemen g on g.dep_id = a.id_dep
             left join logistik.logistik_item_satuan h on h.satuan_id = f.id_satuan_jual
             left join logistik.logistik_grup_item k on k.grup_item_id = f.id_kategori
              left join global.global_supplier i on i.sup_id = d.id_sup
               left join global.global_sumber j on j.sumber_id = d.id_sumber";
      $sql .= " where po_flag='M' and ".$sql_where;
     $sql .= " order by c.faktur_tgl asc, faktur_nomor asc, item_nama asc";     
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
	   $dataTable = $dtaccess->FetchAll($rs);
     
     }
//echo $sql;	   
     //*-- config table ---*//
     $table = new InoTable("table1","100%","left",null,0,2,1,null);     
     $PageHeader = "Laporan Penerimaan";

	// --- construct new table ---- //
	$counter=0;
	$tbHeader[0][$counter][TABLE_ISI] = "No.";
	$tbHeader[0][$counter][TABLE_WIDTH] = "2%";
  $counter++;
 
   $tbHeader[0][$counter][TABLE_ISI] = "Tanggal";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  
  $tbHeader[0][$counter][TABLE_ISI] = "No Transaksi";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++;    
  
  
  $tbHeader[0][$counter][TABLE_ISI] = "Supplier";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++;   
  
    	$tbHeader[0][$counter][TABLE_ISI] = "Sumber Dana";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++;  
  


	$tbHeader[0][$counter][TABLE_ISI] = "No. Faktur";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  

  
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item Barang";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  
  $tbHeader[0][$counter][TABLE_ISI] = "Satuan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  
  $tbHeader[0][$counter][TABLE_ISI] = "Harga Satuan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  
   $tbHeader[0][$counter][TABLE_ISI] = "Jumlah";
	$tbHeader[0][$counter][TABLE_WIDTH] = "7%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  
     $tbHeader[0][$counter][TABLE_ISI] = "Line Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++; 
  

  
  	$tbHeader[0][$counter][TABLE_ISI] = "Keterangan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][ROWSPAN] = "2";
  $counter++;



  $counter=0;
  
  $tbHeader[1][$counter][TABLE_ISI] = "";
	$tbHeader[1][$counter][TABLE_WIDTH] = "2%";
  $counter++;
    



  $row = -1;
	for($i=0,$m=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$m++,$counter=0){
	   
	  
	  if($dataTable[$i]["po_id"]!=$dataTable[$i-1]["po_id"]){
   $row++;
	  //hitung total
	  //$total+=$dataTable[$i]["penjualan_total"];
	  
	  //hitung total Tax
	 // $totalTax+=$dataTable[$i]["penjualan_ppn"];
	  
		$tbContent[$m][$counter][TABLE_ISI] = $row+1;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++; 
    
    
     $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["faktur_tgl"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++; 
    
    $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["no_trans"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++; 
    
 		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["sup_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    
    		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["sumber_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
	          
    
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;      
    
    	$tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;    

    
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++; 
    
                                                
		$tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++; 
                 
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;  
    
     $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++; 
    
     $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;    
        


		$j=0;$m++;$counter=0;
    }
		
    $j++;
    
    
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++; 
    
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;    
    
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;        
		
    $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;        
		 
     
         $tbContent[$m][$counter][TABLE_ISI] = "";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;      
    

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["faktur_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
    
    
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
    
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["satuan_nama"]." (".$dataTable[$i]["satuan_jumlah"].")";
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["faktur_item_hna"]);  
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
                                                                               
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["faktur_item_jumlah"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";                                        
		$counter++;
    
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".currency_format($bayar = $dataTable[$i]["faktur_item_hna_total"]);
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";                                        
		$counter++;
    
			
			      $linetotal += $bayar; 
    
    	$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["keterangan_po"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";                                        
		$counter++;


    		
	}

	
	
	$tbBottom[0][$counter][TABLE_ISI] = "TOTAL PENERIMAAN";
  $tbBottom[0][$counter][TABLE_COLSPAN] = 9;
  $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI] = "Rp.".currency_format($linetotal);
  $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
	$tbBottom[0][$counter][TABLE_ALIGN] = "left";
	$counter++;

	$tbBottom[0][$counter][TABLE_WIDTH] = "";
  $tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
      
      
	
	
	$tglAwal=format_date($_POST["tanggal_awal"]);
	$tglAkhir=$_POST["tanggal_akhir"];

  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_pembelian_langsung.xls');
     }
	
	if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   } 
   
   if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
    
    //AMBIL DATA SUPPLIER
    $sql = "select * from global.global_supplier where sup_flag='M' and sup_aktif='y' order by sup_nama";
    $rs = $dtaccess->Execute($sql);
    $dataSupplier = $dtaccess->FetchAll($rs);
    
        //AMBIL DATA SUPPLIER
    $sql = "select * from global.global_sumber order by sumber_nama";
    $rs = $dtaccess->Execute($sql);
    $dataSumber = $dtaccess->FetchAll($rs);
    
        $sql = "select * from logistik.logistik_grup_item order by grup_item_nama";
    $rs = $dtaccess->Execute($sql);
    $kategorinama = $dtaccess->FetchAll($rs);
	
?>

<?php if(!$_POST["btnExcel"]) { ?>
<?php } ?>
<script language="Javascript">

<? $plx->Run(); ?>

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
  BukaWindow('report_penerimaan_cetak.php?tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&id_sup=<?php echo $_POST["id_sup"];?>&id_kategori=<?php echo $_POST["id_kategori"];?>&id_sumber=<?php echo $_POST["id_sumber"];?>&klinik=<?php echo $_POST["klinik"];?>','Pembelian Langsung');
	document.location.href='<?php echo $thisPage;?>';
<?php } ?>
  


</script>
<?php if(!$_POST["btnExcel"]) { ?>
<body>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6"><?php echo $PageHeader;?></font></a>
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">
<br>
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border="0" cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">
      <tr >
      <!--<td align="left" class="tablecontent" width="15%">&nbsp;Nama Klinik : </td>
			 <td class="tablecontent" colspan="4"><select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--" >- Semua Klinik -</option>
				<?php /*$counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= ".."; */
				?>
				<!--<option class="inputField" value="<?php //echo $dataKlinik[$i]["dep_id"];?>"<?php //if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php //echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php //} ?>
				</select>
		  </td>-->
      <td align="left" class="tablecontent" width="15%">&nbsp;Nama Supplier : </td>
			 <td class="tablecontent" colspan="4"><select name="id_sup" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--" >- Semua Supplier -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataSupplier);$i<$n;$i++){
				?>
				<option class="inputField" value="<?php echo $dataSupplier[$i]["sup_id"];?>"<?php if ($_POST["id_sup"]==$dataSupplier[$i]["sup_id"]) echo"selected"?>><?php echo $dataSupplier[$i]["sup_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
            <td align="left" class="tablecontent" width="15%">&nbsp;Nama Sumber Dana : </td>
			 <td class="tablecontent" colspan="4"><select name="id_sumber" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--" >- Semua Sumber Dana -</option>
				<?php $counter = -1;
					for($i=0,$n=count($dataSumber);$i<$n;$i++){
				?>
				<option class="inputField" value="<?php echo $dataSumber[$i]["sumber_id"];?>"<?php if ($_POST["id_sumber"]==$dataSumber[$i]["sumber_id"]) echo"selected"?>><?php echo $dataSumber[$i]["sumber_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
      </tr>


		           
     
   <tr>
          <td align="left" width="15%" class="tablecontent">&nbsp;Periode : </td>
          <td class="tablecontent" colspan="4">
			<?php echo $view->RenderTextBox("tanggal_awal","tanggal_awal","12","12",$_POST["tanggal_awal"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/>
               - 
			<?php echo $view->RenderTextBox("tanggal_akhir","tanggal_akhir","12","12",$_POST["tanggal_akhir"],"inputField", "readonly",false);?>
			<img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''"/></td>
           
        <td align="left" class="tablecontent" width="15%">&nbsp;Kategori Barang : </td>
			 <td class="tablecontent" colspan="4"><select name="id_kategori" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" onChange="this.form.submit();">
				<option class="inputField" value="--" >- Pilih Kategori -</option>
				<?php $counter = -1;
					for($i=0,$n=count($kategorinama);$i<$n;$i++){
				?>
				<option class="inputField" value="<?php echo $kategorinama[$i]["grup_item_id"];?>"<?php if ($_POST["id_kategori"]==$kategorinama[$i]["grup_item_id"]) echo"selected"?>><?php echo $kategorinama[$i]["grup_item_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
      </tr> 
         <tr>
          <td align="left" width="15%" class="tablecontent"> </td>
          <td class="tablecontent" colspan="4">
       
        <td align="left" class="tablecontent" width="15%">&nbsp;Keterangan : </td>
          <td class="tablecontent-odd" colspan="4">
      	<input type="text" name="Keterangan_po" id="Keterangan_po" size="30" maxlength="30" value="<?php echo $_POST["Keterangan_po"];?>" onKeyDown="return tabOnEnter(this, event);"/>
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
<?php } ?>
<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr width="100%" class="tableheader">
               <td width="100%" align="center" colspan="<?php echo (count($dataSplit)+6)?>">
               <strong>LAPORAN PEMBELIAN OBAT LANGSUNG<br/>
               <?php echo $konfigurasi["dep_nama"]?><?php echo $konfigurasi["dep_kop_surat_1"]?><?php echo $konfigurasi["dep_kop_surat_2"]?>
              <!-- <br/>TAHUN <?php echo $dataTable[0]["tahun"]?><br/> -->
               
               </strong>
               </td>          
          </tr>
          <tr class="tableheader">
          <td align="left" colspan="<?php echo (count($dataSplit)+6)?>">
          <?php echo $poliNama; ?><br/>
          <?php if($_POST["tanggal_awal"]==$_POST["tanggal_awal"]) { echo "Tanggal : ".$_POST["tanggal_awal"]; } elseif($_POST["tanggal_awal"]!=$_POST["tanggal_akhir"]) { echo "Periode : ".$_POST["tanggal_awal"]." - ".$_POST["tanggal_akhir"]; }  ?>

               <br/>
          </td>
          </tr>
     </table>
<?php }?>	
<br />
<table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr> 
        <td>
            <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
				</td>
		 </tr>

	   <tr>
          <td colspan="3" align="left" class="tablecontent-odd">

          </td>
    </tr>
</table> 
<input type="hidden" name="x_mode" value="<?php echo $_x_mode;?>" />    
</form>
<?php if(!$_POST["btnExcel"]) {?>
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
</div>
</div>
<?php }?>				
</body>
</html>
 
