<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php"); 
     require_once($LIB."tree.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
	   $auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $theDep = $auth->GetNamaLogistik();  //Ambil Gudang yang aktif
    $plx = new expAJAX("GetData");

function GetData($in_nama){
	global $dtaccess,$ROOT,$depId,$theDep;

	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---
	   if($in_nama) $sql_where[] = " UPPER(biaya_nama) like '".strtoupper($in_nama)."%'";
     if($sql_where) $sql_where = implode(" and ",$sql_where);

	$sql = "select c.*, a.*, kategori_tindakan_header_nama from klinik.klinik_biaya c
          left join klinik.klinik_kategori_tindakan a on a.kategori_tindakan_id=c.biaya_kategori
          left join klinik.klinik_kategori_tindakan_header b  on b.kategori_tindakan_header_id = a.id_kategori_tindakan_header 
          where a.id_dep = ".QuoteValue(DPE_CHAR,$depId);  
if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by kategori_tindakan_id";
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);


	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Kategori Tindakan Header";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Nama Kategori Tindakan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "Nama Tindakan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
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
    
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["kategori_tindakan_header_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["kategori_tindakan_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["biaya_nama"])).'\',\''.$dataTable[$i]["biaya_id"].'\')"/>';            
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    }
	
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

?>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id) { 
//if(stok > "0") {
	self.parent.document.getElementById('biaya_nama').value = nama;
	self.parent.document.getElementById('id_biaya').value = id;
	self.parent.tb_remove();
//}else{
//alert('Maaf, item stok kosong ('+stok+')');
//}
}

function Search() {
	var nama = document.getElementById('_name').value;
	GetData(nama,'target=dv_hasil');
	
}

</script>

<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;Tindakan</center></td>
			</tr> 
			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Tindakan</td>
				<td class="tablecontent-odd" width="70%">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search()"/>
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




