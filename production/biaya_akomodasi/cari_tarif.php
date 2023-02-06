<?php
      require_once("../penghubung.inc.php");
      require_once($LIB."login.php");
      require_once($LIB."datamodel.php");
      require_once($LIB."dateLib.php");
      require_once($LIB."expAJAX.php");
      require_once($LIB."currency.php");
      require_once($LIB."tampilan.php");
      
      $dtaccess = new DataAccess();
      $auth = new CAuth();
      $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
      $userId = $auth->GetUserId();
      $depNama = $auth->GetDepNama();
      $userName = $auth->GetUserName();
      $depId = $auth->GetDepId();
      $tahunTarif = $auth->GetTahunTarif();
          
      $plx = new expAJAX("GetData");
      
      if(!$auth->IsAllowed("man_tarif_biaya_akomodasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_biaya_akomodasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
     

function GetData($in_nama=null){
	global $dtaccess,$depId,$enc,$table,$ROOT, $idPrj, $idCust,
   $depId, $tgl, $skr, $klinik,$view,$ROOT,$addPage,
   $editPage,$tahunTarif;
	
	 $table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
      
  if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));

	// --- cari data pasien ---
	$sql = "select * from klinik.klinik_biaya a 
          where UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"))." 
          order by biaya_nama";  	
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
  //return $sql;
	       
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "3%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Tindakan";
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["biaya_nama"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/finder.png" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["biaya_id"].'\',\''.addslashes(htmlentities($dataTable[$i]["biaya_nama"])).'\')"/>';
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
 <!-- Bootstrap -->
 <script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(biaya_id,biaya_nama) {
//alert(id);
	self.parent.document.getElementById('id_biaya').value = biaya_id;
	self.parent.document.getElementById('biaya_nama').value = biaya_nama;
  //self.parent.document.getElementById('pgw_nip').value = nip;

	self.parent.tb_remove();
}

function Search() {

	var nama = document.getElementById('_name').value;

	GetData(nama,'target=dv_hasil');

}

</script>
<link href="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="<?php echo $ROOT; ?>assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script><div>
<br><br>

			 <div class="col-md-12 col-sm-12 col-xs-12">
				<form name="frmSearch"  class="form-horizontal form-label-left">
					<div class="form-group">
           <label class="control-label col-md-4 col-sm-4 col-xs-12">Nama Tindakan</label>
            <div class="col-md-5 col-sm-5 col-xs-12">
							<?php echo $view->RenderTextBox("_name","_name",60,255,$_POST["_name"],false,false);?></div>
            </div>  
					 
					<div class="form-group">
          <label class="control-label col-md-4 col-sm-4 col-xs-12"></label>
          <div class="col-md-5 col-sm-5 col-xs-12">
						<input type="button" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()" />
							<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="btn btn-default" />
            </div>
        </div>  
				</form>
			<div>
<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
</div>