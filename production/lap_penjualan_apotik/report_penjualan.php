<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");

	   $tableHeader = "Apotik - Laporan Penjualan";
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $userData = $auth->GetUserData();     
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $usrId = $auth->GetUserId();
 //    $poli = $auth->GetPoli();
     
     //DIPATEN SEMENTARA
     //$poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     //$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  
     
     $thisPage = "report_penjualan.php";
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
     
     $skr = date("d-m-Y");
     $time = date("H:i:s");
     
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
 
     //if($depId && $depId!="--") $sql_where[] = "a.id_dep like ".QuoteValue(DPE_CHAR,"%".$depId."%");
     if ($_POST['status'] == 'y') {
	     $sql_where[] = "pembayaran_det_tgl >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
	     $sql_where[] = "pembayaran_det_tgl <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     }else{
     	$sql_where[] = "date(a.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
	    $sql_where[] = "date(a.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     }
     if ($_POST['klinik']) $sql_where[] ="a.id_gudang =".QuoteValue(DPE_CHAR,$_POST['klinik']);
                                                                                                     
    // UPPER(template_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%")                    
     if($_POST["_nama"] && $_POST["_nama"]!="") $sql_where[] = "UPPER(d.cust_usr_nama) like  '".strtoupper($_POST["_nama"])."%' or UPPER(a.cust_usr_nama) like  '".strtoupper($_POST["_nama"])."%'";
     if($_POST["kode"] && $_POST["kode"]!="") $sql_where[] = "d.cust_usr_kode =  '".$_POST["kode"]."' ";
	 if($_POST["id_jenis_pasien"]) $sql_where[] = "a.id_jenis_pasien=".QuoteValue(DPE_NUMERIC,$_POST["id_jenis_pasien"]);
	 if($_POST["nomor_penjualan"] && $_POST["nomor_penjualan"]!="") $sql_where[] = "a.penjualan_nomor ilike  '%".$_POST["nomor_penjualan"]."%' ";
     if($_POST["reg_tipe_rawat"]) $sql_where[] = "h.reg_tipe_rawat=".QuoteValue(DPE_CHAR,$_POST["reg_tipe_rawat"]);

     if($_POST["status"] == 'y') $sql_where[] = "j.id_pembayaran_det IS NOT NULL";
     if($_POST["status"] == 'n') $sql_where[] = "(j.id_pembayaran_det IS NULL or pembayaran_det_tgl > ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"])).")";


// SEMENTARA DITUTUP KARENA PEMBAYARAN BELUM BERES     
//	   $sql_where[] = "c.item_nama is not null and a.penjualan_terbayar = 'y'";
	   //if($waktuMulai) $sql_where[] = "CAST(a.penjualan_create as TIME) >= ".QuoteValue(DPE_DATE,$waktuMulai);
     //if($waktuselesai) $sql_where[] = "CAST(a.penjualan_create as TIME) <= ".QuoteValue(DPE_DATE,$waktuselesai);
    
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     $sql = "select g.batch_no, e.usr_name,c.item_tipe_jenis,d.cust_usr_nama,a.cust_usr_nama as nama, d.cust_usr_id,
             c.item_nama,b.penjualan_detail_jumlah,a.penjualan_nomor,
             b.penjualan_detail_harga_jual,b.penjualan_detail_total,b.penjualan_detail_tuslag,b.penjualan_detail_ppn, b.penjualan_detail_harga_pokok,
             a.penjualan_create, a.penjualan_grandtotal, a.no_resep,a.penjualan_total,a.dokter_nama,a.penjualan_id,b.id_penjualan,
             a.penjualan_biaya_resep, a.penjualan_biaya_racikan, a.penjualan_biaya_bhps, a.penjualan_diskon,
             a.penjualan_biaya_pembulatan, f.dep_nama,d.cust_usr_nama,d.cust_usr_kode, a.penjualan_tuslag, gudang_nama, j.id_pembayaran_det, a.penjualan_catatan, reg_keterangan
             from apotik.apotik_penjualan a 
             left join apotik.apotik_penjualan_detail b on b.id_penjualan = a.penjualan_id
             left join logistik.logistik_item c on b.id_item = c.item_id
             left join global.global_customer_user d on d.cust_usr_id = a.id_cust_usr
             left join global.global_auth_user e on e.usr_id = a.who_update
             left join global.global_departemen f on f.dep_id = a.id_dep
             left join logistik.logistik_item_batch g on g.batch_id = b.id_batch
             left join klinik.klinik_registrasi h on a.id_reg = h.reg_id
             left join logistik.logistik_gudang i on i.gudang_id = a.id_gudang
             left join klinik.klinik_folio j ON a.penjualan_nomor = j.fol_catatan";
     $sql .= " left join klinik.klinik_pembayaran_det x on x.pembayaran_det_id = j.id_pembayaran_det";
     $sql .= " where ".$sql_where." and (a.penjualan_total is not null or a.penjualan_total <> 0)";
     $sql .= " order by a.penjualan_create desc, a.penjualan_nomor, item_nama asc";
     
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

	$tbHeader[0][$counter][TABLE_ISI] = "No. E Resep";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
  $counter++;
  
	$tbHeader[0][$counter][TABLE_ISI] = "Total";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	//$tbHeader[0][$counter][TABLE_COLSPAN] = "2";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Biaya Racikan";
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

	$tbHeader[0][$counter][TABLE_ISI] = "Status";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;

    $tbHeader[0][$counter][TABLE_ISI] = "Catatan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$counter++;
	
  $counter=0;
  
  $tbHeader[1][$counter][TABLE_ISI] = "No.";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;
  
  $tbHeader[1][$counter][TABLE_ISI] = "Detail";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[1][$counter][TABLE_COLSPAN] = "2";
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
  
  $tbHeader[1][$counter][TABLE_ISI] = "&nbsp;";
	$tbHeader[1][$counter][TABLE_WIDTH] = "5%";
  $counter++;

  

	$tbHeader[1][$counter][TABLE_ISI] = "Klinik";
	$tbHeader[1][$counter][TABLE_WIDTH] = "10%";
    $tbHeader[1][$counter][TABLE_COLSPAN] = "2";
	$counter++;
            //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
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
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

		if($dataTable[$i]["cust_usr_kode"] == '100') $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["nama"];
		else $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;

    //$dataTable[$i]["penjualan_total"] = ($dataTable[$i]["penjualan_total"] + $dataTable[$i]["penjualan_biaya_resep"] + $dataTable[$i]["penjualan_biaya_racikan"] + $dataTable[$i]["penjualan_biaya_bhps"] ) - $dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_pembulatan"];
		$tbContent[$m][$counter][TABLE_ISI] = number_format($dataTable[$i]["penjualan_total"], 0, ',', '.');
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		///$tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;
		$totalJual += $dataTable[$i]["penjualan_total"];

		$tbContent[$m][$counter][TABLE_ISI] = number_format($dataTable[$i]["penjualan_biaya_racikan"], 0, ',', '.');
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$totalRacikan += $dataTable[$i]["penjualan_biaya_racikan"];
		
		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		
		$tbContent[$m][$counter][TABLE_ISI] = number_format($dataTable[$i]["penjualan_diskon"], 0, ',', '.');
		$tbContent[$m][$counter][TABLE_ALIGN] = "center";
		$counter++;
		$diskon += $dataTable[$i]["penjualan_diskon"];
		
		$grand = $dataTable[$i]["penjualan_total"]-$dataTable[$i]["penjualan_diskon"] + $dataTable[$i]["penjualan_biaya_racikan"];
		
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp"; //currency_format($grand);
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
		$status = ($dataTable[$i]['id_pembayaran_det'] == '') ? 'Belum Diposting' : 'Sudah Diposting';
		$tbContent[$m][$counter][TABLE_ISI] = $status;
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		
		$counter++;
		$grandTotal += $grand;
          
          $tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_catatan"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
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
		$tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;

  	$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".number_format($dataTable[$i]["penjualan_detail_jumlah"], 2, ',', '.')."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
  	$jml += $dataTable[$i]["penjualan_detail_jumlah"];
  
    $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".number_format($dataTable[$i]["penjualan_detail_harga_jual"], 0, ',', '.')."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;		
		$hJual += $dataTable[$i]["penjualan_detail_harga_jual"];		
   
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["batch_no"];
		$tbContent[$m][$counter][TABLE_ALIGN] = "left";
		$counter++;
		
		$hasil = intval($dataTable[$i]["penjualan_detail_jumlah"] * $dataTable[$i]["penjualan_detail_harga_jual"]);
		$tbContent[$m][$counter][TABLE_ISI] = "&nbsp;".number_format($dataTable[$i]['penjualan_detail_harga_pokok'], 0, ',', '.')."&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$jmlh += $dataTable[$i]['penjualan_detail_harga_pokok'];
    		
	  $tbContent[$m][$counter][TABLE_ISI] = "&nbsp;";
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		
    		
	  
		
		$tbContent[$m][$counter][TABLE_ISI] = number_format($dataTable[$i]["penjualan_detail_total"], 0, ',', '.');
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
		$counter++;
		$total += $dataTable[$i]["penjualan_detail_total"];

		$tbContent[$m][$counter][TABLE_ISI] = $dataTable[$i]['gudang_nama'];
		$tbContent[$m][$counter][TABLE_ALIGN] = "right";
        $tbContent[$m][$counter][TABLE_COLSPAN] = "2";
		$counter++;
	}

	$counter=0;	
	$tbBottom[0][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<!--<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">-->&nbsp;';
	$tbBottom[0][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";

	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]     = "Total";
	$tbBottom[0][$counter][TABLE_ALIGN]   = "center";
	$tbBottom[0][$counter][TABLE_COLSPAN]   = "2";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($jml, 2, ',', '.');
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($hJual, 0, ',', '.');      
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($totalRacikan, 0, ',', '.');
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($jmlh, 0, ',', '.'); //currency_format($total);
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($diskon, 0, ',', '.');
	$tbBottom[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbBottom[0][$counter][TABLE_ISI]   = number_format($grandTotal, 0, ',', '.'); //currency_format($grandTotal);
	$tbBottom[0][$counter][TABLE_ROWSPAN]   = "2";
	$tbBottom[0][$counter][TABLE_ALIGN] = "right";
	$counter++;	


	$counter=0;	
	$tbBottom[1][$counter][TABLE_ISI]     = '&nbsp;&nbsp;<!--<input type="button" name="btnCetak" value="Cetak" class="button" onClick="document.location.href=\''.$cetakPage.'\'">-->&nbsp;';
	$tbBottom[1][$counter][TABLE_ALIGN]   = "left";
	$tbBottom[1][$counter][TABLE_COLSPAN]   = "8";
	$counter++;
	
	
	
	$tbBottom[1][$counter][TABLE_ISI]   = "&nbsp;"; //currency_format($grandTotal);
	$tbBottom[1][$counter][TABLE_ALIGN] = "right";
	$counter++;	


	$tbBottom[1][$counter][TABLE_ISI]   = '&nbsp;';
	$tbBottom[1][$counter][TABLE_ALIGN] = "center";
	$tbBottom[1][$counter][TABLE_COLSPAN]   = "3";
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

$sql = "select a.* from global.global_auth_poli a left join global.global_auth_user_poli b on b.id_poli = a.poli_id where poli_tipe = 'A' and id_usr = ".QuoteValue(DPE_CHAR,$usrId);
$dataApotik = $dtaccess->FetchAll($sql);
    
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
  BukaWindow('report_penjualan_cetak.php?nama=<?php echo $_POST["nama"];?>&kode=<?php echo $_POST["kode"];?>&tanggal_awal=<?php echo $_POST["tanggal_awal"];?>&tanggal_akhir=<?php echo $_POST["tanggal_akhir"];?>&jenis=<?php echo $_POST["id_jenis_pasien"];?>&klinik=<?php echo $_POST["klinik"];?>&op_mulai_jam=<?php echo $_POST["op_mulai_jam"];?>&op_mulai_menit=<?php echo $_POST["op_mulai_menit"];?>&op_selesai_jam=<?php echo $_POST["op_selesai_jam"];?>&op_selesai_menit=<?php echo $_POST["op_selesai_menit"];?>&reg_tipe_rawat=<?php echo $_POST["reg_tipe_rawat"];?>','Pemakaian Kasir');
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
                    <h2>Laporan Penjualan</h2>
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
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>							
							<input  type="text" name="_nama" id="_nama" size="10" class="form-control" maxlength="50" value="<?php echo $_POST["_nama"]; ?>">     			 
				    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No RM</label>							
							<input  type="text" name="kode" id="kode" size="10" class="form-control"  maxlength="10" value="<?php echo $_POST["kode"]; ?>">	     			 
				    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Instalasi</label>							
							<select class="form-control" name="reg_tipe_rawat" id="reg_tipe_rawat">
                        <option value="" > [Semua Instalasi] </option>
                        <option value="J" <?php if($_POST["reg_tipe_rawat"] =="J"){echo "selected";}?>>Rawat Jalan</option>
                        <option value="I" <?php if($_POST["reg_tipe_rawat"] =="I"){echo "selected";}?>>Rawat Inap</option>
                        <option value="G" <?php if($_POST["reg_tipe_rawat"] =="G"){echo "selected";}?>>Gawat Darurat</option>
						</select>    			 
				    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Status</label>
							<select class="form-control" name="status" id="status">
                        <option value="" > [Pilih Status] </option>
                        <option value="y" <?php if($_POST["status"] =="y"){echo "selected";}?>>Sudah Diposting</option>
                        <option value="n" <?php if($_POST["status"] =="n"){echo "selected";}?>>Belum Diposting</option>
						</select>    			 
				    </div>

					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nomor Penjualan</label>							
							<input  type="text" name="nomor_penjualan" id="nomor_penjualan" size="10" class="form-control" maxlength="50" value="<?php echo $_POST["nomor_penjualan"]; ?>">     			 
				    </div>

					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Apotik</label>							
			              <select class="form-control" name="klinik">
			                <option value="">Semua Apotik</option>
			               <?php for ($i=0; $i < count($dataApotik); $i++) { ?>
			                <option value="<?php echo $dataApotik[$i]['id_gudang'] ?>" <?php if ($dataApotik[$i]['id_gudang'] == $_POST['klinik']) { echo 'selected'; } ?>><?php echo $dataApotik[$i]['poli_nama']; ?></option>
			               <?php } ?>
			              </select>   			 
				    </div>
					
					<div class="col-md-4 pull-right col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>	
                        <div>
						  <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary">					
						  <input type="submit" name="btnExcel" value="Export Excel" class="btn btn-primary">					
						<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="btn btn-primary">
						</div>					
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
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
<?php if($_POST["btnLanjut"] || $_POST["btnCetak"]) {?>

<?php }?>	
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

