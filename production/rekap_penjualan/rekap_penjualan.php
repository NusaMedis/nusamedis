<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
	 
	$tableHeader = "Apotik - Rekap Penjualan";
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $poli = $auth->GetPoli();
     //DIPATEN SEMENTARA
     $poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
//    $plx = new expAJAX("GetData");
     $userData = $auth->GetUserData();     
	   $userName = $auth->GetUserName();
     $usrId = $auth->GetUserId();

     $thisPage = "rekap_penjualan.php";
     $cetakPage = "rekap_penjualan_cetak.php?";

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

if($_POST["btnLanjut"] || $_POST["btnExcel"] ){ 

     $sql_where[] = "date(b.penjualan_create) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(b.penjualan_create) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     if ($_POST['klinik'])$sql_where[] ="b.id_gudang =".QuoteValue(DPE_CHAR,$_POST['klinik']);
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
//echo $_POST['klinik'];  
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
	
  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=report_penjualan.xls');
     }
	
	if($_POST["btnCetak"]){

      $_x_mode = "cetak" ;
         
   }
    $sql = "select gudang_nama from logistik.logistik_gudang";
    $rs = $dtaccess->Execute($sql);
    $dataGudang = $dtaccess->FetchAll($rs);
	
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
  <?php require_once($LAY."header.php") ?>
<script language="JavaScript">
  function rejenis(kliniks) {
   document.location.href='grup_item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
</script> 
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
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
                    <h2>Rekap Penjualan</h2>
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
              <label class="control-label col-md-12 col-sm-12 col-xs-12">Apotik</label>
              <div class="col-md-12 col-sm-12 col-xs-12">
              <select class="form-control" name="klinik">
                <option value="">Semua Apotik</option>
               <?php for ($i=0; $i < count($dataApotik); $i++) { ?>
                <option value="<?php echo $dataApotik[$i]['id_gudang'] ?>" <?php if ($dataApotik[$i]['id_gudang'] == $_POST['klinik']) { echo 'selected'; } ?>><?php echo $dataApotik[$i]['poli_nama']; ?></option>
               <?php } ?>
              </select>
              </div>
            </div>
					
					<div class="col-md-4 pull-right col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						  <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
							<input type="submit" name="btnExcel" value="Export Excel" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
						<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
					</div>
					<table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
                    </table>
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


