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
     $userName = $auth->GetUserName();
     
     $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif  
    $plx = new expAJAX("GetData");

function GetData($in_nama=null,$kategori){
	global $dtaccess,$ROOT,$depId,$theDep; 

	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");

//	    $skr=date["Y-m-d"];
      	
	// --- cari data menunya ---
     $jenis = "2";
	   if($in_nama) $sql_where[] = " UPPER(c.item_nama) like '".strtoupper($in_nama)."%'";
     if($jenis && $jenis!="--") $sql_where[] = "d.jenis_id = ".QuoteValue(DPE_NUMERIC,$jenis)."";  
     if($kategori && $kategori!="--") $sql_where[] = "c.id_kategori = ".QuoteValue(DPE_CHAR,$kategori)."";
     if($sql_where) $sql_where = implode(" and ",$sql_where);       
$dateskrg=date("Y-m-d");
	$sql = "select b.batch_id, b.batch_no, b.batch_tgl_jatuh_tempo, a.stok_batch_dep_saldo ,
          c.item_kode, c.item_id , c.item_nama , c.item_harga_jual, d.jenis_nama 
          from logistik.logistik_stok_batch_dep a 
          left join logistik.logistik_item_batch b on a.id_batch = b.batch_id
          left join logistik.logistik_item c  on a.id_item=c.item_id
          left join global.global_jenis_pasien d on d.jenis_id = c.item_tipe_jenis
          left join logistik.logistik_grup_item e on e.grup_item_id = c.id_kategori";  
  $sql .= " where c.item_aktif='y' and c.item_flag = 'M' and a.id_gudang=".QuoteValue(DPE_CHAR,$theDep).
  " and b.id_dep = ".QuoteValue(DPE_CHAR,$depId);
//  " and batch_tgl_jatuh_tempo >= ".QuoteValue(DPE_date,$skr);
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by c.item_nama asc, b.batch_tgl_jatuh_tempo asc";
    // return $sql;
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Obat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
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
	
	$tbHeader[0][$counter][TABLE_ISI] = "Stok";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Harga Jual";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Jenis Obat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {   

    /*$sql = "select a.stok_dep_saldo from logistik.logistik_stok_dep a 
    where a.id_item =".QuoteValue(DPE_CHAR,$dataTable[$i]["item_id"])." and id_gudang = '2'
    and id_dep = ".QuoteValue(DPE_CHAR,$depId)." order by a.stok_dep_create desc";
  	$rs = $dtaccess->Execute($sql);     
  	$log = $dtaccess->Fetch($rs);*/
    

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
		
		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["stok_batch_dep_saldo"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_harga_jual"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;                        
		
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jenis_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++; 
		
//		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\',\''.currency_format($dataTable[$i]["item_harga_jual"]).'\',\''.$dataTable[$i]["item_kode"].'\',\''.currency_format($dataTable[$i]["batch_stok_saldo"]).'\',\''.$dataTable[$i]["batch_id"].'\',\''.$dataTable[$i]["batch_no"].'\',\''.format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]).'\')"/>';
    		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\',\''.currency_format($dataTable[$i]["item_harga_jual"]).'\',\''.$dataTable[$i]["item_kode"].'\',\''.$dataTable[$i]["stok_batch_dep_saldo"].'\',\''.$dataTable[$i]["batch_id"].'\',\''.$dataTable[$i]["batch_no"].'\',\''.format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]).'\')"/>';            
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    }
	
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}
 /*    // --- master jenis pasien ---
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
     $rs = $dtaccess->Execute($sql);
     $dataJenis = $dtaccess->FetchAll($rs);
   // print_r ($dataJenis);
    echo $sql;

		$jenis = $view->RenderOption('--','[ Pilih Semua ]',$show);
  for($i=0,$n=count($dataJenis);$i<$n;$i++){
		unset($show); 
		if($_POST["jenis_id"]==$dataJenis[$i]["jenis_id"]) $show = "selected";
		$jenis = $view->RenderOption($dataJenis[$i]["jenis_id"],$dataJenis[$i]["jenis_nama"],$show);
	}  */  
   //-- bikin combo box untuk Jenis --//
   	$sql = "select jenis_id,jenis_nama from global.global_jenis_pasien where jenis_flag='y' order by jenis_id asc"; 
		$dataJenis = $dtaccess->FetchAll($sql);

   //-- bikin combo box untuk Kategori --//
   	$sql = "select * from logistik.logistik_grup_item where item_flag='M' order by grup_item_nama asc"; 
		$dataGrup = $dtaccess->FetchAll($sql);

?>

<?php ////echo $view->RenderBody("module.css",true,false,"CARI ITEM"); ?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function Search(nama,kategori) {
  GetData(nama,kategori,'target=dv_hasil');
	
}

function sendValue(nama,id,harga,kode,stok,batch,batch_no,batch_exp) {
//if(stok > "0") {
  self.parent.document.getElementById('obat_kode').value = kode;
	self.parent.document.getElementById('obat_nama').value = nama;
	self.parent.document.getElementById('obat_id').value = id;
	self.parent.document.getElementById('txtHargaSatuan').value = harga;
	self.parent.document.getElementById('txtHargaTotal').value = harga;
	self.parent.document.getElementById('txtJumlah').value = '1';
	self.parent.document.getElementById('id_batch').value = batch;
	self.parent.document.getElementById('batch_no').value = batch_no;
	self.parent.document.getElementById('batch_tgl_jatuh_tempo').value = batch_exp;
	self.parent.document.getElementById('txtSatuanNom').innerHTML = harga;
	self.parent.document.getElementById('txtIsiTotale').innerHTML = harga;
	self.parent.document.getElementById('txtJumlah').focus();
	self.parent.tb_remove();
//}else{
//alert('Maaf, item stok kosong ('+stok+')');
//}
}



</script>
<?php //echo $view->RenderBody("module.css",true,true,"CARI ITEM"); ?>
<br /><br /><br /><br />
<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Obat</center></td>
			</tr> 
     <!--<tr>
     <td width='10%' align="right" class="tablecontent">&nbsp;Jenis&nbsp;</td>
     <td align="left" class="tablecontent-odd">
               <select name="id_jenis" id="jenis_id" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
               <option value="">[- Semua Jenis -]</option>
            <?php for($i=0,$n=count($dataJenis);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataJenis[$i]["jenis_id"];?>" <?php if($_POST["id_jenis"]==$dataJenis[$i]["jenis_id"]) echo "selected";?>><?php echo $dataJenis[$i]["jenis_nama"];?></option>
						 <?php } ?>               
               </select>
    </td>
    </tr>-->
     <tr>
     <td width='10%' align="right" class="tablecontent">&nbsp;Kategori Barang&nbsp;</td>
     <td align="left" class="tablecontent-odd">
               <select name="id_kategori" id="id_kategori" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
               <option value="">[- Pilih Kategori -]</option>
            <?php for($i=0,$n=count($dataGrup);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataGrup[$i]["grup_item_id"];?>" <?php if($_POST["id_kategori"]==$dataGrup[$i]["grup_item_id"]) echo "selected";?>><?php echo $dataGrup[$i]["grup_item_nama"];?></option>
						 <?php } ?>               
               </select>
    </td>
    </tr>
    <tr>
				<td align="right" class="tablecontent" width="30%">Nama Obat</td>
				<td class="tablecontent-odd" width="70%">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search(document.getElementById('_name').value,document.getElementById('id_kategori').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="submit" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<?php echo $view->SetFocus("_name",true);?>
</form>

<div id="dv_hasil"></div>

 <?php ////echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>

