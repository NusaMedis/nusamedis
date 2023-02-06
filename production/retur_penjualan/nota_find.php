<?php
      require_once("../penghubung.inc.php");
      require_once($LIB."login.php");
      require_once($LIB."datamodel.php");
      require_once($LIB."dateLib.php");
      require_once($LIB."expAJAX.php");
      require_once($LIB."tampilan.php");
      
      $dtaccess = new DataAccess();
      $auth = new CAuth();
      $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
      $userId = $auth->GetUserId();
      $depNama = $auth->GetDepNama();
      $depId = $auth->GetDepId();
      $tgl = date("Y-m-d");
     $userName = $auth->GetUserName();
      
      $plx = new expAJAX("GetData");
       
     

function GetData($in_nama,$in_kode,$in_nota){
	global $dtaccess, $ROOT, $depId, $tgl;

	$tgl = date("Y-m-d");
	$tgl_awal = date("Y-m-d", strtotime("-5 days"));
	
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
	
	$sql_where[] = "1=1"; 
	if($in_nama) $sql_where[] = "UPPER(b.cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
	if($in_kode) $sql_where[] = "UPPER(b.cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
	if($in_nota) $sql_where[] = "UPPER(a.penjualan_nomor) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nota."%"));
	$sql_where[] = " date(penjualan_create) <= '$tgl' and date(penjualan_create) >= '$tgl_awal'" ;
  $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
	$sql_where = implode(" and ",$sql_where);


	// --- Sementara ditutup dulu
//	$sql = "select *, a.cust_usr_nama as pasien from apotik.apotik_penjualan a 
//          left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
//          where a.penjualan_terbayar = 'y'";   	 


	// --- cari nota pasien ---
	$sql = "select a.*, a.cust_usr_nama as pasien, b.cust_usr_kode from apotik.apotik_penjualan a 
          left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
          where 1=1";   	 
	$sql .= " and ".$sql_where;			
	$sql .= " order by a.penjualan_create desc";
	//return $sql;		
	$rs = $dtaccess->Execute($sql);     
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
	
	$tbHeader[0][$counter][TABLE_ISI] = "No Nota";
	$tbHeader[0][$counter][TABLE_WIDTH] = "40%";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["pasien"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["penjualan_nomor"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["cust_usr_id"].'\',\''.htmlspecialchars($dataTable[$i]["cust_usr_nama"]).'\',\''.$dataTable[$i]["cust_usr_alamat"].'\',\''.$dataTable[$i]["penjualan_id"].'\',\''.$dataTable[$i]["penjualan_nomor"].'\')"/>';
		$tbContent[$i][$counter][TABLE_ALIGN] = "center";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
	}
		
	$str = $table->RenderView($tbHeader,$tbContent,$tbBottom);
	
	return $str;
}

//$optionJK[0] = $view->RenderOption("","[All]",$show);
//$optionJK[1] = $view->RenderOption("L","Laki-laki",$show);
//$optionJK[2] = $view->RenderOption("P","Perempuan",$show);

?>
<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id,nama,alamat,penjualan,penjualan_no) {
	self.parent.document.getElementById('id_cust_usr').value = id;
	self.parent.document.getElementById('cust_usr_nama').value = nama;
	self.parent.document.getElementById('cust_usr_alamat').value = alamat;
	self.parent.document.getElementById('id_penjualan').value = penjualan;
	self.parent.document.getElementById('no_nota').value = penjualan_no;
	self.parent.tb_remove();
}

function Search() {
	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('_kode').value;
	var alamat= document.getElementById('_nota').value;

	GetData(nama,kode,alamat,'target=dv_hasil');
}

</script>
<!-- Bootstrap -->
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<br><br>
			 <div class="col-md-12 col-sm-12 col-xs-12">
				<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>"  class="form-horizontal form-label-left">
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Pasien</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<?php echo $view->RenderTextBox("_name","_name",30,200,$_POST["_name"],false,false);?>
							</div>
                    </div>  
					
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">No Reg</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
								<?php echo $view->RenderTextBox("_kode","_kode",30,200,$_POST["_kode"],false,false);?>
							</div>
                    </div> 
					
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12">No Nota</label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
								<?php echo $view->RenderTextBox("_nota","_nota",30,200,$_POST["_nota"],false,false);?>
						</div>
                    </div> 
					  
					<div class="form-group">
                        <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
                        <div class="col-md-5 col-sm-5 col-xs-12">
							<input type="button" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()" />
							<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="submit" /></center>
				
						</div>
                    </div> 	 					
				</form>
			<div>
<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_kode",true);?>
</div>





