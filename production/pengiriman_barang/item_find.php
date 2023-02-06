<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth(); 
     
$_POST["outlet"] = $dataOutlet["dep_id"];
$outlet = $_POST["outlet"];

$transId = $enc->Decode($_GET["id"]);

$plx = new expAJAX("GetData");
//echo $outlet;

function GetData($in_nama=null,$tipe){
	global $dtaccess,$ROOT,$transId;
	
	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");

	  // --- cari data gudangnya di dtaabase transaksi ---
    $sql = "select a.id_asal from logistik.logistik_transfer_stok a where a.transfer_id=".QuoteValue(DPE_CHAR,$transId);
    $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
    $dep = $dtaccess->Fetch($rs_edit);
    $dtaccess->Clear($rs_edit);    
     
     $sql_where[] = " d.id_gudang =".QuoteValue(DPE_CHAR,$dep["id_asal"]);
     if($in_nama) $sql_where[] = " UPPER(item_nama) like '%".strtoupper($in_nama)."%'";
    if($tipe && $tipe!= "--") $sql_where[] = " a.id_kategori = ".QuoteValue(DPE_CHAR,$tipe);  
     if($sql_where) $sql_where = implode(" and ",$sql_where); 

	$sql = "select * from logistik.logistik_item a
          left join global.global_jenis_pasien b on b.jenis_id = a.item_tipe_jenis 
          left join logistik.logistik_item_batch c on c.id_item = a.item_id
          left join logistik.logistik_stok_batch_dep d on d.id_batch = c.batch_id
          left join logistik.logistik_grup_item e on e.grup_item_id = a.id_kategori";
	if($sql_where) $sql .= " where a.item_flag = 'M' and stok_batch_dep_saldo > '0.00' and ".$sql_where;				
	$sql .= " order by a.item_nama asc, c.batch_tgl_jatuh_tempo asc";
	//return $sql;
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item";
	$tbHeader[0][$counter][TABLE_WIDTH] = "25%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "No Batch";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Expire Date";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	//$tbHeader[0][$counter][TABLE_ISI] = "Stok Item";
	//$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	//$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	//$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Jenis Item";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Stok";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {

		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["batch_no"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jenis_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["stok_batch_dep_saldo"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="16" height="16" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\',\''.currency_format($dataTable[$i]["stok_batch_dep_saldo"]).'\',\''.$dataTable[$i]["jenis_nama"].'\',\''.$dataTable[$i]["stok_dep_tgl"].'\',\''.$dataTable[$i]["batch_id"].'\',\''.$dataTable[$i]["batch_no"].'\',\''.format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]).'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

     // --- master kelas sekolah  ---
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_id asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataJenis = $dtaccess->FetchAll($rs);

  $jenis[] = $view->RenderOption("","[- Pilih Semua Tipe -]",$show);
  for($i=0,$n=count($dataJenis);$i<$n;$i++){
		unset($show);
		if($_POST["jenis_id"]==$dataJenis[$i]["jenis_id"]) $show = "selected";
		$jenis[] = $view->RenderOption($dataJenis[$i]["jenis_id"],$dataJenis[$i]["jenis_nama"],$show);
	} 

     // --- master Tipe  ---
     $sql = "select * from logistik.logistik_grup_item where item_flag = 'M'  order by grup_item_id asc";
     $rs = $dtaccess->Execute($sql);
     $dataTipe = $dtaccess->FetchAll($rs);
    //echo $sql;    
    
		$tipe[] = $view->RenderOption("","[- Pilih Semua Tipe -]",$show);
    for($i=0,$n=count($dataTipe);$i<$n;$i++){
		unset($show);
		if($_POST["grup_item_id"]==$dataTipe[$i]["grup_item_id"]) $show = "selected";
    $tipe[] = $view->RenderOption($dataTipe[$i]["grup_item_id"],$dataTipe[$i]["grup_item_nama"],$show);
	}       

?>
<?php echo $view->RenderBody("ipad_depans.css",true,"ITEM FIND"); ?>


<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id,stok,jenis,tgl,batch,nobatch,tglexp) {

//if('<?php echo $_SESSION["tgl"] ;?>' >= tgl){
	self.parent.document.getElementById('item_nama').value = nama;
	self.parent.document.getElementById('item_id').value = id;
	self.parent.document.getElementById('stok').value = stok;
	self.parent.document.getElementById('jenis_nama').value = jenis;
	//self.parent.document.getElementById('txtHargaSatuan').value = formatCurrency(harga);
	self.parent.document.getElementById('transfer_detail_jumlah').value = '1';
	self.parent.document.getElementById('id_batch').value = batch;
	self.parent.document.getElementById('nomor_batch').value = nobatch;
	self.parent.document.getElementById('exp_date_batch').value = tglexp;
	self.parent.document.getElementById('transfer_detail_jumlah').focus();
	self.parent.tb_remove();
 //}else{
 //alert('Tanggal Keluar Melebihi Batas Input Item');
 //}
}

function Search(nama,tipe) {
	GetData(nama,tipe,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Item</center></td>
			</tr> 
      <tr>
        <td align="right" class="tablecontent" width="30%"><strong>Jenis Item</strong>&nbsp;</td>
          <td class="tablecontent-odd">
             <?php echo $view->RenderComboBox("jenis_id","jenis_id",$jenis,null,null);?> 
          </td>
      </tr>  
			<tr>
				<td align="right" class="tablecontent">Nama item</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
      <tr>
         <td align="right" class="tablecontent" width="30%"><strong>Kategori Barang</strong>&nbsp;</td>
          <td class="tablecontent-odd"> 
          <?php echo $view->RenderComboBox("grup_item_id","grup_item_id",$tipe,null,null);?>
        </td>
      </tr> 
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search(document.getElementById('_name').value,document.getElementById('grup_item_id').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="submit" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
<?php echo $view->RenderBodyEnd(); ?>

