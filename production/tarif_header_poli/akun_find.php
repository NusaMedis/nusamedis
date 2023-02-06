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

	  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	  $dtaccess = new DataAccess();
	  $enc = new textEncrypt();   
	  $auth = new CAuth();
	  $table = new InoTable("table","100%","left");
	  $depId = $auth->GetDepId();
	  $depNama = $auth->GetDepNama();
	  $userName = $auth->GetUserName();
	  $depLowest = $auth->GetDepLowest();
     //echo $depId;
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
	    die("Maaf anda tidak berhak membuka halaman ini....");
	    exit(1);
	  } else 
	  if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
	    echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
	    exit(1);
	  } 
     
    $_POST["outlet"] = $dataOutlet["dep_id"];
    
    $outlet = $_POST["outlet"];
    
    $plx = new expAJAX("GetData");
    // $klinik = $depId;



function GetData($in_nama=null){
	global $dtaccess,$ROOT,$depId,$table;

	
	//$table = new ExpTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---

	   if($in_nama) $sql_where[] = " UPPER(biaya_nama) like '%".strtoupper($in_nama)."%'";  
     if($sql_where) $sql_where = implode(" and ",$sql_where);

  //perkiraan yang di select hanya kas saja
	$sql = "select * from klinik.klinik_biaya a 
		left join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id=a.biaya_kategori
		left join klinik.klinik_biaya_tarif d on d.id_biaya=a.biaya_id
    left join klinik.klinik_kelas e on e.kelas_id=d.id_kelas
          where d.biaya_total>0
           and a.id_dep = ".QuoteValue(DPE_CHAR,$depId);  
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by biaya_nama asc";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql;

	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Tindakan";
	$tbHeader[0][$counter][TABLE_WIDTH] = "25%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Kategori Biaya";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Kelas";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	$tbHeader[0][$counter][TABLE_ISI] = "Biaya";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
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

    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["kategori_tindakan_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["kelas_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".currency_format($dataTable[$i]["biaya_total"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/icon/cari.png" style="cursor:pointer" border="0" alt="Pilih" title="Pilih" width="18" height="18" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["biaya_id"])).'\',\''.addslashes(htmlentities($dataTable[$i]["biaya_nama"])).'\',\''.$dataTable[$i]["biaya_kode"].'\')"/>';    
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

function sendValue(no,nama,id) {
    self.parent.document.getElementById('prk_no').value = no;
	self.parent.document.getElementById('prk_nama').value = nama;
	self.parent.document.getElementById('prk_id').value = id;
	self.parent.tb_remove();
}

function Search(nama) {
	GetData(nama,'target=dv_hasil');
}

</script>
<?php require_once($LAY."header.php"); ?>
<!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<form name="frmSearch">
		<div class="col-md-12 col-sm-12 col-xs-12">
				<label><center>Pencarian&nbsp;Tindakan</center></label>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Tindakan</label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
					<input type="hidden" name="klinik" id="klinik" value="<?php echo $_GET["klinik"];?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<input type="button" name="btnSearch" value="Cari" class="btn btn-primary" onClick="Search(document.getElementById('_name').value,document.getElementById('klinik').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="btn btn-default" />
				</div>
			</div>
		</div>

</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
<br><br><br>
</div>
<?php require_once($LAY."js.php") ?>