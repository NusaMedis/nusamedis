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
     $thisPage = "report_retur_penjualan.php";
     $cetakPage = "report_penjualan_cetak.php?";
         
     // PRIVILLAGE
    if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
     
     // ambil gudang konfigurasi apotik
	   $sql = "select * from apotik.apotik_conf where id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs);
     
     if($gudang["conf_gudang_obat"]=='L'){
          $theDep = "1";
     }else{
          $theDep = $auth->GetNamaLogistik();
     }
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
     if($_POST["op_mulai_jam"]){
     $_POST["op_mulai_jam"]= $_POST["op_mulai_jam"];
     }else{
     $_POST["op_mulai_jam"]= 0;
     }
     
     if($_POST["op_mulai_menit"]){
     $_POST["op_mulai_menit"]= $_POST["op_mulai_menit"];
     }else{
     $_POST["op_mulai_menit"]= 0;
     }
     
     if($_POST["op_selesai_jam"]){
     $_POST["op_selesai_jam"]= $_POST["op_selesai_jam"];
     }else{
     $_POST["op_selesai_jam"]= date('H');
     }
     
     if($_POST["op_selesai_menit"]){
     $_POST["op_selesai_menit"]= $_POST["op_selesai_menit"];
     }else{
     $_POST["op_selesai_menit"]= date('i');
     }

     $detik = date('s');
     $waktuMulai = $_POST["op_mulai_jam"].":".$_POST["op_mulai_menit"].":".$detik;
     $waktuselesai = $_POST["op_selesai_jam"].":".$_POST["op_selesai_menit"].":".$detik;
	   $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
     $cetakPage = "report_penjualan_cetak.php?tanggal_awal="
     .$_POST["tanggal_awal"]."&tanggal_akhir=".$_POST["tanggal_akhir"]."&penjualan_tipe=".$_POST["penjualan_tipe"];
 
     if($depId && $depId!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$depId."%");
     $sql_where[] = "date(a.retur_penjualan_when_update) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(a.retur_penjualan_when_update) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     $sql_where[] ="a.id_gudang =".QuoteValue(DPE_CHAR,$theDep);
	   $sql_where[] = "c.item_nama is not null and a.retur_penjualan_lunas = 'y'";
	   if($waktuMulai) $sql_where[] = "CAST(a.retur_penjualan_when_update as TIME) >= ".QuoteValue(DPE_DATE,$waktuMulai);
     if($waktuselesai) $sql_where[] = "CAST(a.retur_penjualan_when_update as TIME) <= ".QuoteValue(DPE_DATE,$waktuselesai);
    
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.retur_penjualan_id,a.retur_penjualan_when_update,a.retur_penjualan_total,a.retur_penjualan_nomor,d.penjualan_nomor,d.cust_usr_nama,
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
  
  $tbHeader[0][$counter][TABLE_ISI] = "No. Faktur/Resep ";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	//$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Petugas";
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
            //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
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
          header('Content-Disposition: attachment; filename=report_retur_penjualan.xls');
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
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
	
	
?>

<!DOCTYPE html>
<html lang="en">
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
  BukaWindow('report_retur_penjualan_cetak.php?tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&cust_usr_jenis=<?php echo $_POST["cust_usr_jenis"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>','Retur Barang');
	document.location.href='<?php echo $thisPage;?>';
<?php } ?>
  
</script> 
  <?php require_once($LAY."header.php") ?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Apotik</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2> Laporan Retrun Penjualan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <?php if(!$_POST["btnExcel"]) { ?>
				  <form  name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"];?>"> 
				 	<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input name="tanggal_awal" type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  name="tanggal_akhir"  type='text' class="form-control" 
							value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Waktu</label>
                        
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
								Sampai			
							<select  name="op_selesai_jam" class="inputField" >
							<?php for($i=0,$n=24;$i<$n;$i++){ ?>
							<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_jam"]) echo "selected"; ?>><?php echo $i;?></option>
							<?php } ?>
							</select>:
							<select  name="op_selesai_menit" class="inputField" >
							<?php for($i=0,$n=60;$i<$n;$i++){ ?>
							<option class="inputField" value="<?php echo $i;?>" <?php if($i==$_POST["op_selesai_menit"]) echo "selected"; ?>><?php echo $i;?></option>
							<?php } ?>
							</select>							     			 
				    </div>
					
					<div class="col-md-4 pull-right col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
					         <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary"">
			<input type="submit" name="btnExcel" value="Export Excel" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
					</div>
					            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
            </div>
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
               <strong>LAPORAN RETUR PENJUALAN OBAT<br/>
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
<!-- <?php if(!$_POST["btnExcel"]) {?>
		 </div>
		 </div>

  		<table width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>
<?php }?> -->
					</form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>
