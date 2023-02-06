<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
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
     $depId = $auth->GetDepId();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     $table = new InoTable("table","100%","left");
     //echo $depId;
     
     /*if(!$auth->IsAllowed("man_ganti_password",PRIV_READ) && !$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1 || $auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
    $_POST["outlet"] = $dataOutlet["dep_id"];
    
    $outlet = $_POST["outlet"];
    
    $plx = new expAJAX("GetData");
    // $klinik = $depId;

    $urut = $_GET["urut"];
    //echo $urut;

function GetData($in_nama=null){
	global $dtaccess,$ROOT,$depId,$table;
	
	//$table = new ExpTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---

	   if($in_nama) $sql_where[] = " UPPER(nama_prk) like '%".strtoupper($in_nama)."%'";  
     if($sql_where) $sql_where = implode(" and ",$sql_where);

  //perkiraan yang di select hanya kas saja
	$sql = "select *  from  gl.gl_perkiraan where is_lowest='y' and id_dep = ".QuoteValue(DPE_CHAR,$depId);  
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by no_prk asc";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql." - ".$urut;

	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Kode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Akun";
	$tbHeader[0][$counter][TABLE_WIDTH] = "35%";
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

    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["no_prk"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["nama_prk"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/icon/cari.png" style="cursor:pointer" border="0" alt="Pilih" title="Pilih" width="30" height="30" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["nama_prk"])).'\',\''.$dataTable[$i]["no_prk"].'\',\''.$dataTable[$i]["id_prk"].'\')"/>';    
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

function sendValue(nama,no,id,urut) {
	self.parent.document.getElementById('prk_nama_beban').value = nama;
  self.parent.document.getElementById('prk_no_beban').value = no;
	self.parent.document.getElementById('id_prk_beban').value = id;
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
	<br><br>
		<div class="col-md-12 col-sm-12 col-xs-12">
				<label><center>Pencarian&nbsp;Akun Beban</center></label>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Akun</label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
					<input type="hidden" name="klinik" id="klinik" value="<?php echo $_GET["klinik"];?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<input type="button" name="btnSearch" value="Cari" class="btn btn-primary" onClick="Search(document.getElementById('_name').value)"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove();" class="btn btn-default" />
				</div>
			</div>
		</div>
</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
</div>
</body>

<?php require_once($LAY."js.php") ?>