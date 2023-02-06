<?php
      // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
      
     //INISIALISASI LIBRARY
     $enc = new textEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");
     
     //$depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }

      $plx = new expAJAX("GetData");

 function GetData($in_nama,$in_kode,$in_alamat,$in_tgl)
 { 
	global $dtaccess, $ROOT, $ROOT, $idPrj, $idCust;
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	
	$sql_where[] = "1=1"; 
	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
	if($in_alamat) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_alamat."%"));
 	if($in_alamat) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_alamat."%"));
 	if($in_tgl) $sql_where[] = "cust_usr_tanggal_lahir = ".QuoteValue(DPE_CHAR,date_db($in_tgl));
	$sql_where = implode(" and ",$sql_where);

	// --- cari data krsnya ---
	$sql = "select a.cust_usr_id, a.cust_usr_nama, a.cust_usr_kode , a.cust_usr_alamat,a.cust_usr_tanggal_lahir from global.global_customer_user a";
	$sql .= " where 1=1 and ".$sql_where;			
	$sql .= " order by a.cust_usr_kode asc";
  //return $sql;
			
	$rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);     
	$dataTable = $dtaccess->FetchAll($rs);
	
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Kode";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pasien";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	/*$tbHeader[0][$counter][TABLE_ISI] = "Klinik";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;*/
		
	$tbHeader[0][$counter][TABLE_ISI] = "Alamat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
  
  $tbHeader[0][$counter][TABLE_ISI] = "Tgl lahir";
	$tbHeader[0][$counter][TABLE_WIDTH] = "30%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;


	$tbHeader[0][$counter][TABLE_ISI] = "Pilih";
	$tbHeader[0][$counter][TABLE_WIDTH] = "5%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
	
	
	for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0) {
		
		($i%2==0)? $class="tablecontent":$class="tablecontent-odd";

		$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
		$tbContent[$i][$counter][TABLE_ALIGN] = "right";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_kode"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		/*$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["dep_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;*/
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".nl2br($dataTable[$i]["cust_usr_alamat"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    

    
      $daytime = explode(".", $dataTable[$i]["cust_usr_tanggal_lahir"]);
              $time = explode(" ", $daytime[0]);

              $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
              $tbContent[$i][$counter][TABLE_ALIGN] = "left";
              $counter++;
    
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/icon/edit.png" style="cursor:pointer" border="0" alt="Pilih" title="Pilih" width="32" height="32" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["cust_usr_id"].'\',\''.$dataTable[$i]["cust_usr_kode"].'\',\''.$dataTable[$i]["cust_usr_nama"].'\',\''.$dataTable[$i]["cust_usr_alamat"].'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

$optionJK[0] = $view->RenderOption("","[All]",$show);
$optionJK[1] = $view->RenderOption("L","Laki-laki",$show);
$optionJK[2] = $view->RenderOption("P","Perempuan",$show);

?>

<script language="JavaScript">
<?php $plx->Run(); ?>

</script>

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id,kode,nama,alamat) {
	self.parent.document.getElementById('id_cust_usr').value = id;	
	self.parent.document.getElementById('cust_usr_kode').value = kode;
  self.parent.document.getElementById('cust_usr_nama').value = nama;
  self.parent.document.getElementById('cust_usr_alamat').value = alamat;
	self.parent.tb_remove();
}  

function Search() 
{
  if(document.getElementById('_kode').value == "" && document.getElementById('_name').value == "" && document.getElementById('_alamat').value == ""){
      alert('Salah satu Filter harus diisi');                            
			document.getElementById('_kode').focus();
			return false;    
  } 

	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('_kode').value;
	var alamat = document.getElementById('_alamat').value;
	var tgl = document.getElementById('_tgl').value;
  
	GetData(nama,kode,alamat,tgl,'target=dv_hasil');
}

</script>

<form name="frmSearch">
<table border="1" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
			<tr class="tablecontent-odd" >
				<td colspan="2"><center>PENCARIAN PASIEN&nbsp;</center></td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">No. RM</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_kode","_kode",30,200,$_POST["_kode"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Pasien</td>
				<td class="tablecontent">
					<?php echo $view->RenderTextBox("_name","_name",30,200,$_POST["_name"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Alamat</td>
				<td class="tablecontent-odd">
					<?php echo $view->RenderTextBox("_alamat","_alamat",100,255,$_POST["_alamat"],false,false);?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Tgl Lahir</td>
				<td class="tablecontent-odd">
					<?php echo $view->RenderTextBox("_tgl","_tgl",20,255,$_POST["_tgl"],false,false);?> (dd-mm-yyyy)
				</td>
			</tr>
			<tr>
				<td colspan="2"><center>
					<input type="button" name="btnSearch" value="Cari"  class="submit" onClick="return Search()"/>
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="submit" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>

<div id="dv_hasil"></div>
<br /><br /><br /><br />
<?php echo $view->SetFocus("_name",true);?>
 <?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
