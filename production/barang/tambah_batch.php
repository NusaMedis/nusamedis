<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
         
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $depId = $auth->GetDepId();
	   $table = new InoTable("table","100%","left");

     if ($_GET['hapus']) {
      $sql = "DELETE FROM logistik.logistik_item_batch WHERE batch_id = ".QuoteValue(DPE_CHAR, $_GET['hapus']);
      $dtaccess->Execute($sql);

      $url = "tambah_batch.php?id_item=".$_GET['id_item']."&klinik=".$_GET['klinik']."&id_jenis=".$_GET['id_jenis']."&id_gudang=".$_GET['id_gudang'];

      header('location: '.$url);
     }
     
	  
 	   /*
     if(!$auth->IsAllowed("transfer_stok",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("transfer_stok",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }*/

	if($_GET["klinik"]) $_POST["klinik"] = $_GET["klinik"];
	if($_GET["id_item"]) $_POST["id_item"] = $_GET["id_item"];
	if($_GET["id_gudang"]) $_POST["id_gudang"] = $_GET["id_gudang"];
	if($_GET["id_jenis"]) $_POST["id_jenis"] = $_GET["id_jenis"];
  
 /* $sql = "select * from logistik.logistik_konfigurasi where id_dep = ".QuoteValue(DPE_CHAR,$depId);
  $rs = $dtaccess->Execute($sql);
  $gudang = $dtaccess->Fetch($rs);
  $_POST["id_gudang"] = $gudang["konf_gudang"]; //amil gudang yang aktif
 */ 
$poli = "33"; //POLI APOTIK IRJ
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif
    
    $_POST["id_gudang"] = $theDep; 
	//echo $_POST["id_item"];
   //cek stok berdasarkan id_dep
  $sql = "select * from logistik.logistik_stok_dep
   where id_item = ".QuoteValue(DPE_CHAR,$_POST["id_item"])." and id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
  //echo $sql;
  //die();
  $dataItemStokdep = $dtaccess->Fetch($sql);
  
  if(!$dataItemStokdep){
        $dbTable = "logistik.logistik_stok_dep";
      
      $dbField[0] = "stok_dep_id";
      $dbField[1] = "id_item";
      $dbField[2] = "stok_dep_saldo";
      $dbField[3] = "stok_dep_create";
      $dbField[4] = "stok_dep_tgl";
      $dbField[5] = "id_dep";
      $dbField[6] = "id_gudang";
      
      $stokBatchDepId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchDepId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_item"]);
      $dbValue[2] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[3] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d'));
      $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      
//      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);


      $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue);

  }	
	// Tampilkan Data Obat dan Stok Terakhirnya //
	$sql = "select a.* , b.* from logistik.logistik_item a
          join logistik.logistik_stok_dep b on b.id_item = a.item_id
          where a.item_id = ".QuoteValue(DPE_CHAR,$_POST["id_item"])." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." and b.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
  $dataItemStok = $dtaccess->Fetch($sql);
 // echo $sql;
  // Tampilkan Data Gudang Item / Obat //
	$sql = "select a.gudang_nama from logistik.logistik_gudang a
          where a.gudang_id = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"]);
  $dataGudang = $dtaccess->Fetch($sql);
  
  // Tampilkan Jenis Item / Barang / Obatnya //
	$sql = "select a.jenis_nama from global.global_jenis_pasien a
          where a.jenis_id = ".QuoteValue(DPE_NUMERIC,$_POST["id_jenis"]);
  $dataJenisItem = $dtaccess->Fetch($sql);
  
  // Tampilkan semua data Batch Item Barang / Obat //	
	$sql = "select * from logistik.logistik_item_batch a
          where a.id_item = ".QuoteValue(DPE_CHAR,$_POST["id_item"])." and a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])."
           and a.batch_status = 'y' order by a.batch_tgl_jatuh_tempo asc";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
 	
	$fotoName = $ROOT."gambar/foto_pasien/".$dataPasien["cust_usr_foto"];
	
	if($_POST["btnSave"]) {
	
    // check, apkah tanggal yg di input tdk kadaluarsa //
/*    $dateNow = date_db($_POST["batch_tgl_jatuh_tempo"]);
    $now = date("Y-m-d");
    if($dateNow<$now) {
     echo "<script>alert('Maaf Tanggal yang diinput sudah Kadaluarsa');</script>";
     echo "<script>document.location.href = 'tambah_batch.php?klinik=".$_POST["klinik"]."&id_gudang=".$_POST["id_gudang"]."&id_item=".$_POST["id_item"]."';</script>";
     exit();        
    }   */    
  
              // insert data stok item batch dg flag A //
              $dbTable = "logistik.logistik_item_batch";
              $dbField[0]  = "batch_id";   // PK
              $dbField[1]  = "batch_no";
              $dbField[2]  = "batch_create";    
              $dbField[3]  = "batch_flag";
              $dbField[4]  = "id_item";
              $dbField[5]  = "id_dep";
              $dbField[6]  = "batch_keterangan";
              if ($_POST["batch_tgl_jatuh_tempo"])
              $dbField[7]  = "batch_tgl_jatuh_tempo";

              $_POST["batch_tgl_jatuh_tempo"] = str_replace("/", "-", $_POST["batch_tgl_jatuh_tempo"]);
              
              $batchId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["batch_no"]);
              $dbValue[2] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
              $dbValue[3] = QuoteValue(DPE_CHAR,'A');  // Saldo Untuk Opname
              $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_item"]); 
			        $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["batch_keterangan"]);
              if ($_POST["batch_tgl_jatuh_tempo"])
              $dbValue[7] = QuoteValue(DPE_CHAR,date_db($_POST["batch_tgl_jatuh_tempo"]));   //sesuai konfigurasi apotik 
//  print_r($dbValue); die();           
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("insert  error");
              	
              unset($dtmodel);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);
      //insert ke semua gudang
      $sql = "select gudang_id from logistik.logistik_gudang where gudang_flag = 'M'";
              $rs = $dtaccess->Execute($sql);
              $dataGudangAll = $dtaccess->FetchAll($rs);
      for ($i=0,$n=count($dataGudangAll); $i < $n ; $i++) { 
              
      $dbTable = "logistik.logistik_stok_item_batch";
      
      $dbField[0] = "stok_item_batch_id";
      $dbField[1] = "stok_item_batch_jumlah"; 
      $dbField[2] = "id_item";
      $dbField[3] = "id_batch";
      $dbField[4] = "id_dep";
      $dbField[5] = "stok_item_batch_flag";
      $dbField[6] = "stok_item_batch_create";
      $dbField[7] = "stok_item_batch_saldo";
      $dbField[8] = "stok_item_keterangan";
      $dbField[9] = "id_gudang"; 
      $dbField[10] = "stok_item_batch_hpp";
      $dbField[11] = "stok_item_hpp_ket";
      $dbField[12] = "id_pembelian";
      $dbField[13] = "stok_item_batch_hna";
      $dbField[14] = "stok_item_batch_hna_diskon";
      $dbField[15] = "stok_item_batch_hna_ppn";
      $dbField[16] = "stok_item_batch_hna_ppn_minus_diskon";
      $dbField[17] = "stok_item_batch_diskon_persen";
      $dbField[18] = "stok_item_batch_hna_total";
      
      $StokItemBatchId = $dtaccess->GetTransID();

      
      $dbValue[0] = QuoteValue(DPE_CHAR,$StokItemBatchId);
      $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_dep_saldo"]));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_item"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$batchId);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,"A");
      $dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[7] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_dep_saldo"]));
      $dbValue[8] = QuoteValue(DPE_CHAR,'');
      $dbValue[9] = QuoteValue(DPE_CHAR,$dataGudangAll[$i]["gudang_id"]);
      $dbValue[10] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[11] = QuoteValue(DPE_CHAR,$HppKet);
      $dbValue[12] = QuoteValue(DPE_CHAR,$fakturId);
      $dbValue[13] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[14] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[15] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[16] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[17] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[18] = QuoteValue(DPE_NUMERIC,0);
      
//    print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

      $dtmodel->Insert() or die("insert  error");
      	
      unset($dbField);
      unset($dbValue);
      
      $dbTable = "logistik.logistik_stok_batch_dep";
      
      $dbField[0] = "stok_batch_dep_id";
      $dbField[1] = "id_item";
      $dbField[2] = "id_batch";
      $dbField[3] = "stok_batch_dep_saldo";
      $dbField[4] = "stok_batch_dep_create";
      $dbField[5] = "stok_batch_dep_tgl";
      $dbField[6] = "id_dep";
      $dbField[7] = "id_gudang";
      
      $stokBatchDepId = $dtaccess->GetTransID();
      
      $dbValue[0] = QuoteValue(DPE_CHAR,$stokBatchDepId);
      $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["id_item"]);
      $dbValue[2] = QuoteValue(DPE_CHAR,$batchId);
      $dbValue[3] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["stok_dep_saldo"]));
      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d'));
      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[7] = QuoteValue(DPE_CHAR,$dataGudangAll[$i]["gudang_id"]);
      
//      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);


      $dtmodel->Insert() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);
    }  

          echo "<script>document.location.href='item_view.php?klinik=".$_POST["klinik"]."&id_jenis=".$_POST["id_jenis"]."&id_gudang=".$_POST["id_gudang"]."'</script>;";
          exit();
          
  }

?> 
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="javascript" type="text/javascript">
function CheckDataSave(frm)
{  
/*  if(!frm.batch_no.value){
		alert('No Batch Harus Diisi');
		frm.batch_no.focus();
          return false;
	}
	
  if(!frm.batch_tgl_jatuh_tempo.value){
		alert('Tanggal Expire Harus Diisi');
		frm.batch_tgl_jatuh_tempo.focus();
          return false;
	}  */
	
	/*if(!frm.batch_stok_saldo.value){
		alert('Saldo Stok Harus Diisi');
		frm.batch_stok_saldo.focus();
          return false;
	}*/
	
     document.frmEdit.submit();     
}
</script>
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
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Tambah Batch</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmEdit" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-9 col-sm-9 col-xs-12">Nama Item</label>
						<?php echo $dataItemStok["item_nama"];?> 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-9 col-sm-9 col-xs-12">Stok Total</label>						
						<?php echo number_format($dataItemStok["stok_dep_saldo"],4);?>
				    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-9 col-sm-9 col-xs-12">Gudang</label>						
						<?php echo $dataGudang["gudang_nama"];?>
				    </div>

					<div class="clearfix"></div>
					
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
    <tr>
       <td width="5%" class="subheader">&nbsp;&nbsp;No.</td>
       <td>Hapus</td>
       <td width="20%" class="subheader">&nbsp;&nbsp;No. Batch</td>
       <td width="30%" class="subheader">&nbsp;&nbsp;Expire Date&nbsp;<i>(dd-mm-yyyy)</i></td>
       <td width="30%" class="subheader">&nbsp;&nbsp;Keterangan</td>
    </tr>
    
    <tr>
       <td width="5%" class="tableheader">&nbsp;&nbsp;</td>
       <td width="5%" class="tableheader">&nbsp;&nbsp;</td>
       <td width="20%" class="tableheader">&nbsp;&nbsp;<input type="text" name="batch_no" id="batch_no" value="<?php echo $_POST["batch_no"];?>" /> </td>
       <td width="30%" class="tableheader">&nbsp;&nbsp;<input type="text" name="batch_tgl_jatuh_tempo" id="batch_tgl_jatuh_tempo" value="<?php echo $_POST["batch_tgl_jatuh_tempo"];?>" /></td>
       <td width="35%" class="tableheader">&nbsp;&nbsp;<input type="text" size="40" name="batch_keterangan" id="batch_keterangan" value="<?php echo $_POST["batch_keterangan"];?>" /></td>
    </tr>

    <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
    <tr>
       <td width="5%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $i+1;?></td>
       <td align="center"><a href="tambah_batch.php?hapus=<?= $dataTable[$i]['batch_id'] ?>&id_item=<?= $_GET['id_item'] ?>&klinik=<?= $_GET['klinik'] ?>&id_jenis=<?= $_GET['id_jenis'] ?>&id_gudang=<?= $_GET['id_gudang'] ?>" title=""><i class="fa fa-trash" style="font-size: 18px;"></i></a></td>  
       <td width="20%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataTable[$i]["batch_no"];?></td>
       <td width="30%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
       <td width="30%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataTable[$i]["batch_keterangan"];?></td>
    </tr>
    <?php } ?>    
    <tr>
        <td colspan="4" align="center">
            <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
            <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='item_view.php?klinik=".$_POST["klinik"]."&id_jenis=".$_POST["id_jenis"]."&id_gudang=".$_POST["id_gudang"]."';\"");?>                    
        </td>
    </tr>
         
</table> 
					
 <?php echo $view->RenderHidden("id_item","id_item",$_POST["id_item"]);?>
<?php echo $view->RenderHidden("klinik","klinik",$_POST["klinik"]);?>
<?php echo $view->RenderHidden("id_gudang","id_gudang",$_POST["id_gudang"]);?>
<?php echo $view->RenderHidden("stok_dep_saldo","stok_dep_saldo",number_format($dataItemStok["stok_dep_saldo"],4));?>
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
 </form>
<?php require_once($LAY."js.php") ?>

  </body>
</html>