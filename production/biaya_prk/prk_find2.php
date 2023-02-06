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
     $userName = $auth->GetUserName();    
	   $auth = new CAuth();
     $err_code = 0;
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();

    $plx = new expAJAX("GetData");

function GetData($in_nama){
	global $dtaccess,$ROOT,$depId,$ROOT;

	$table = new InoTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---
	   if($in_nama) $sql_where[] = " UPPER(nama_prk) like '%".strtoupper($in_nama)."%'";  
     if($sql_where) $sql_where = implode(" and ",$sql_where);

  
	$sql = "select * from gl.gl_perkiraan"; 
  $sql .= " where id_dep = ".QuoteValue(DPE_CHAR,$depId);
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by no_prk asc";
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "No. Perkiraan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Perkiraan";
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
     //untuk .. pemisah antar perkiraan
     $length=0;
     $k=0;
     $length = (strlen($dataTable[$i]["order_prk"])/TREE_LENGTH_CHILD)-1;
     for($k=0;$k<$length;$k++) $spacer .= ".&nbsp;.&nbsp;";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
    
    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$spacer.$dataTable[$i]["no_prk"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$spacer.$dataTable[$i]["nama_prk"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		if ($dataTable[$i]["is_lowest"]=='y')
    {
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["nama_prk"])).'\',\''.$dataTable[$i]["id_prk"].'\')"/>';            
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    }
    else
    {
  		$tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';    
  		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
  		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
  		$counter++;
    }

	  unset($spacer);
    }
	
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

?>

<br /><br /><br /><br />
<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(nama,id) {
	self.parent.document.getElementById('prk_debet').value = nama;
	self.parent.document.getElementById('id_prk_debet').value = id;
	self.parent.tb_remove();
}

function Search() {
  var nama = document.getElementById('_name').value;
	GetData(nama,'target=dv_hasil');
}
/*
function sendValue(nama,id) { 
//if(stok > "0") {
	self.parent.document.getElementById('nama_prk_piutang_debet').value = nama;
	self.parent.document.getElementById('id_prk_piutang_debet').value = id;
	self.parent.tb_remove();
//}else{
//alert('Maaf, item stok kosong ('+stok+')');
//}
}

function Search(nama) {
	var nama = document.getElementById('_name').value;
	GetData(nama,'target=dv_hasil');
	
}
 */
</script>
<?php require_once($LAY."header.php"); ?>
<!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<form name="frmSearch">
		<div class="col-md-12 col-sm-12 col-xs-12">
				<label><center>Pencarian&nbsp;Perkiraan&nbsp;Akuntansi</center></label>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Akun</label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<input type="button" name="btnSearch" value="Cari" class="btn btn-primary" onClick="Search()"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="btn btn-default" />
				</div>
			</div>
		</div>
</form>
<?php echo $view->SetFocus("_name",true);?>

<div id="dv_hasil"></div>
<br /><br /><br /><br />

<?php require_once($LAY."js.php") ?>
