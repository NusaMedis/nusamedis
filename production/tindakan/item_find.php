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
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ) && !$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1 || $auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }
     
    $_POST["outlet"] = $dataOutlet["dep_id"];
    
    $outlet = $_POST["outlet"];
    
    $plx = new expAJAX("GetData");
    // $klinik = $depId;

    $tipe = $_GET["tipe"];
    //echo $urut;

function GetData($in_nama=null, $tipe=null){
	global $dtaccess,$ROOT,$depId,$table;
	
	//$table = new ExpTable("table1","100%","center",null,0,1,1,null,"tblForm");
	
	// --- cari data menunya ---

	   if($in_nama) $sql_where[] = " UPPER(item_nama) like '%".strtoupper($in_nama)."%'";  

	   if($tipe == 'LA'){
	   	 $sql_where[] = " item_flag = 'L' ";
	   }
	   else{
	   	$sql_where[] = " item_flag = 'B' ";
	   }

       if($sql_where) $sql_where = implode(" and ",$sql_where);

  //perkiraan yang di select hanya kas saja
	$sql = "select *  from  logistik.logistik_item where 1=1";  
  if($sql_where) $sql .= " and ".$sql_where;
	$sql .= " order by item_nama asc";
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);     
	$dataTable = $dtaccess->FetchAll($rs);
	//return $sql." - ".$urut;

	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "1%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Item";
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

		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["item_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/icon/cari.png" style="cursor:pointer" border="0" alt="Pilih" title="Pilih" width="30" height="30" class="img-button" OnClick="javascript: sendValue(\''.addslashes(htmlentities($dataTable[$i]["item_nama"])).'\',\''.$dataTable[$i]["item_id"].'\')"/>';    
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
	self.parent.document.getElementById('item_nama').value = nama;
	self.parent.document.getElementById('item_id').value = id;
	self.parent.tb_remove();
}

function Search(nama, tipe) {
	GetData(nama, tipe, 'target=dv_hasil');
}

</script>

<body>
<div id="body">
<form name="frmSearch">
<table border="0" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="0" align="center" width="100%">
			<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian Item</center></td>
			</tr> 
			<tr>
				<td align="right" class="tablecontent">Nama Item</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",50,200,$_POST["_name"],false,false);?>
					<input type="hidden" name="tipe" id="tipe" value="<?php echo $_GET["tipe"];?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search(document.getElementById('_name').value, document.getElementById('tipe').value)"/>
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
</div>
</body>


