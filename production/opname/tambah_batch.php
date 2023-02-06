<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");
         
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();
     $userData = $auth->GetUserData();
	   $table = new InoTable("table","100%","left");
	
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
  if($_GET["id_kategori"]) $_POST["id_kategori"] = $_GET["id_kategori"];
  if($_GET["id_sup"]) $_POST["id_sup"] = $_GET["id_sup"];
	//echo $_POST["id_item"];
	
	// Tampilkan Data Obat dan Stok Terakhirnya //
	$sql = "select a.* , b.* from logistik.logistik_item a
          join logistik.logistik_stok_dep b on b.id_item = a.item_id
          where a.item_id = ".QuoteValue(DPE_CHAR,$_POST["id_item"])." and a.id_dep =".QuoteValue(DPE_CHAR,$_POST["klinik"])." and b.id_gudang =".QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
  $dataItemStok = $dtaccess->Fetch($sql);
  
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
          and a.id_gudang = ".QuoteValue(DPE_CHAR,$_POST["id_gudang"])." and a.batch_status = 'y' order by a.batch_tgl_jatuh_tempo asc";
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
              $dbField[6]  = "id_gudang";
              $dbField[7]  = "batch_keterangan";
              if ($_POST["batch_tgl_jatuh_tempo"])
                  $dbField[8]  = "batch_tgl_jatuh_tempo";
              
              $batchId = $dtaccess->GetTransID();
              $dbValue[0] = QuoteValue(DPE_CHAR,$batchId);
              $dbValue[1] = QuoteValue(DPE_CHAR,$_POST["batch_no"]);
              $dbValue[2] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
              $dbValue[3] = QuoteValue(DPE_CHAR,'A');  // Saldo Untuk Opname
              $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["id_item"]); 
			        $dbValue[5] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
              $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
              $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["batch_keterangan"]);
              if ($_POST["batch_tgl_jatuh_tempo"])
                   $dbValue[8] = QuoteValue(DPE_DATE,date_db($_POST["batch_tgl_jatuh_tempo"]));   //sesuai konfigurasi apotik 
//  print_r($dbValue); die();           
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
          
              $dtmodel->Insert() or die("insert  error");
              	
              unset($dtmodel);
              unset($dbField);
              unset($dbValue);
              unset($dbKey);
      
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
      $dbValue[1] = QuoteValue(DPE_NUMERIC,StripCurrency($_POST["sendItem"]));
      $dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_item"]);
      $dbValue[3] = QuoteValue(DPE_CHAR,$batchId);
      $dbValue[4] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[5] = QuoteValue(DPE_CHAR,"A");
      $dbValue[6] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[7] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[8] = QuoteValue(DPE_CHAR,'');
      $dbValue[9] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
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
      $dbValue[3] = QuoteValue(DPE_NUMERIC,0);
      $dbValue[4] = QuoteValue(DPE_DATE,date('Y-m-d H:i:s'));
      $dbValue[5] = QuoteValue(DPE_DATE,date('Y-m-d'));
      $dbValue[6] = QuoteValue(DPE_CHAR,$_POST["klinik"]);
      $dbValue[7] = QuoteValue(DPE_CHAR,$_POST["id_gudang"]);
      
//      print_r($dbValue); die();
      $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
      $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);


      $dtmodel->Insert() or die("update  error");
      	
      unset($dbField);
      unset($dbValue);


          echo "<script>document.location.href='trans_opname_edit.php?klinik=".$_POST["klinik"]."&id_jenis=".$_POST["id_jenis"]."&id_gudang=".$_POST["id_gudang"]."&id_sup=".$_POST["id_sup"]."&id_kategori=".$_POST["id_kategori"]."'</script>;";
          exit();
          
  }

?> 
<?php echo $view->RenderBody("ipad_depans.css",true,"TAMBAH BATCH"); ?>
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

<body>
<div id="header">
<table border="0" width="100%" valign="top">
<tr>
<td width="10%" align="left" valign="top">
<a href="http://sikita.net" target="_blank"><img src="<?php echo $ROOT;?>gambar/sikitalogo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="#" target="_blank"><font size="6">TAMBAH BATCH</font></a>&nbsp;&nbsp;
</td>
</tr>
</table>
</div>
<div id="body">
<div id="scroller">

<form name="frmEdit" method="POST" action="<?php echo $thisPage; ?>">
<br />
<table border="0" cellpadding="2" cellspacing="2"  align="center" width="100%">    
    <tr>
       <td width="30%" class="tablecontent">&nbsp;&nbsp;Nama Item </td>
       <td width="70%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataItemStok["item_nama"];?></td>
    </tr> 
    <tr>
       <td width="30%" class="tablecontent">&nbsp;&nbsp;Stok Total </td>
       <td width="70%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo number_format($dataItemStok["stok_dep_saldo"],4);?></td>
    </tr>
    <tr>
       <td width="30%" class="tablecontent">&nbsp;&nbsp;Gudang </td>
       <td width="70%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataGudang["gudang_nama"];?></td>
    </tr>
    <tr>
       <td width="30%" class="tablecontent">&nbsp;&nbsp;Jenis Item </td>
       <td width="70%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataJenisItem["jenis_nama"];?></td>
    </tr>        
  </table>
<br>

<table border="1" cellpadding="2" cellspacing="2"  align="center" width="100%">    
    <tr>
       <td width="20%" class="subheader">&nbsp;&nbsp;No. Batch</td>
       <td width="30%" class="subheader">&nbsp;&nbsp;Expire Date&nbsp;<i>(dd-mm-yyyy)</i></td>
       <td width="30%" class="subheader">&nbsp;&nbsp;Keterangan</td>
    </tr>
    
    <tr>
       <td width="20%" class="tableheader">&nbsp;&nbsp;<input type="text" name="batch_no" id="batch_no" value="<?php echo $_POST["batch_no"];?>" /> </td>
       <td width="30%" class="tableheader">&nbsp;&nbsp;<input type="text" name="batch_tgl_jatuh_tempo" id="batch_tgl_jatuh_tempo" value="<?php echo $_POST["batch_tgl_jatuh_tempo"];?>" />
        <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tanggal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
       </td>
       <td width="35%" class="tableheader">&nbsp;&nbsp;<input type="text" size="40" name="batch_keterangan" id="batch_keterangan" value="<?php echo $_POST["batch_keterangan"];?>" /></td>
    </tr>

    <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
    <tr>
       <td width="20%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataTable[$i]["batch_no"];?></td>
       <td width="30%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
       <td width="30%" class="tablecontent-odd">&nbsp;&nbsp;<?php echo $dataTable[$i]["batch_keterangan"];?></td>
    </tr>
    <?php } ?>    
    <tr>
        <td colspan="3" align="center">
            <?php echo $view->RenderButton(BTN_SUBMIT,"btnSave","btnSave","Simpan","submit",false,"onClick=\"javascript:return CheckDataSave(document.frmEdit);\"");?>
            <?php echo $view->RenderButton(BTN_BUTTON,"btnBack","btnBack","Kembali","submit",false,"onClick=\"document.location.href='trans_opname_edit.php?klinik=".$_POST["klinik"]."&id_jenis=".$_POST["id_jenis"]."&id_sup=".$_POST["id_sup"]."&id_kategori=".$_POST["id_kategori"]."';\"");?>                    
        </td>
    </tr>
         
</table>    
<script>document.frmEdit.batch_no.focus();</script>
<?php echo $view->RenderHidden("id_item","id_item",$_POST["id_item"]);?>
<?php echo $view->RenderHidden("klinik","klinik",$_POST["klinik"]);?>
<?php echo $view->RenderHidden("id_gudang","id_gudang",$_POST["id_gudang"]);?>
<?php echo $view->RenderHidden("id_sup","id_sup",$_POST["id_sup"]);?>
<?php echo $view->RenderHidden("id_kategori","id_kategori",$_POST["id_kategori"]);?>
<?php echo $view->RenderHidden("stok_dep_saldo","stok_dep_saldo",number_format($dataItemStok["stok_dep_saldo"],4));?>
</form>

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "batch_tgl_jatuh_tempo",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tanggal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
</script>

<?php echo $view->RenderBodyEnd(); ?>
