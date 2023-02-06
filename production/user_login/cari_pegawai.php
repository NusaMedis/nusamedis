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
      $depId = $auth->GetDepId();
      $depNama = $auth->GetDepNama(); 
      $userName = $auth->GetUserName();
    
      
      $plx = new expAJAX("GetData");
        
    /*  if(!$auth->IsAllowed("man_user_user_login",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_user_login",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     

function GetData($in_nama){
	global $dtaccess,$depId,$enc,$table,$poliId,$ROOT, $idPrj, $idCust, $depId, $tgl, $skr, $klinik,$view,$ROOT,$splitsId,$addPage,$editPage,$regPage,$thisPage,$PageJenisBiaya,$PageKategoriTindakan,$totalHargaTax,$detPage;
	
	$table = new InoTable("table1","100%","center",null,0,5,1,null,"tblForm");
      
   	if($in_nama) $sql_where[] = "UPPER(pgw_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
    
         


	// --- cari data pasien ---
	$sql = "select * from hris.hris_pegawai a left join hris.hris_struktural b on a.id_struk = b.struk_id
  where UPPER(pgw_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));  
 // if($sql_where) $sql .= " ;
  //	$sql .= " order by template_nama";
  //return $sql;
  /**            
          where  (reg_utama is null or reg_utama ='') and a.id_dep = ".QuoteValue(DPE_CHAR,$depId);   
        //$sql .= " b.reg_status = 'E0' ";		 
			
  */
//			
	$rs = $dtaccess->Execute($sql);     
	$dataTable = $dtaccess->FetchAll($rs);
	       
	$counter = 0;          

	$tbHeader[0][$counter][TABLE_ISI] = "No";
	$tbHeader[0][$counter][TABLE_WIDTH] = "3%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
		
	$tbHeader[0][$counter][TABLE_ISI] = "NIP Pegawai";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
		
	$tbHeader[0][$counter][TABLE_ISI] = "Nama Pegawai";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

    $tbHeader[0][$counter][TABLE_ISI] = "Unit Kerja";
	$tbHeader[0][$counter][TABLE_WIDTH] = "15%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
  
    $tbHeader[0][$counter][TABLE_ISI] = "Alamat";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
    
    $tbHeader[0][$counter][TABLE_ISI] = "No. HP";
	$tbHeader[0][$counter][TABLE_WIDTH] = "10%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;

	/*$tbHeader[0][$counter][TABLE_ISI] = "Template Anemnesia";
	$tbHeader[0][$counter][TABLE_WIDTH] = "20%";
	$tbHeader[0][$counter][TABLE_ALIGN] = "center";
	$counter++;
                          */
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
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["pgw_nip"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
		
		$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".nl2br($dataTable[$i]["pgw_nama"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".nl2br($dataTable[$i]["struk_nama"]);
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    
        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["pgw_alamat"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;

        $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["pgw_telp_hp"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;
    
   /* $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["template_anemnesa"];
		$tbContent[$i][$counter][TABLE_ALIGN] = "left";
		$tbContent[$i][$counter][TABLE_CLASS] = $class;                    
		$counter++;   */                                                                                                                                                                                                                                                                                                                                             
		
		$tbContent[$i][$counter][TABLE_ISI] = '<img src="'.$ROOT.'gambar/r_arrowgrnsm.gif" style="cursor:pointer;" border="0" alt="Pilih" title="Pilih" width="22" height="22" class="img-button" OnClick="javascript: sendValue(\''.$dataTable[$i]["id_struk"].'\',\''.$dataTable[$i]["struk_nama"].'\',\''.$dataTable[$i]["pgw_id"].'\',\''.addslashes(htmlentities($dataTable[$i]["pgw_nama"])).'\',\''.addslashes(htmlentities($dataTable[$i]["pgw_nip"])).'\',\''.addslashes(htmlentities($dataTable[$i]["pgw_alamat"])).'\',\''.addslashes(htmlentities($dataTable[$i]["pgw_telp_hp"])).'\')"/>';
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
<br /><br /><br /><br />

<script language="JavaScript">
<?php $plx->Run(); ?>

function sendValue(id_struk,struk_nama,id,nama,nip,alamat,hp) {
 //alert(struk_nama);
  //self.parent.document.getElementById('id_struk').value = id_struk;
  self.parent.document.getElementById('pgw_id').value = id;
  self.parent.document.getElementById('pgw_nama').value = nama;
  self.parent.document.getElementById('pgw_nip').value = nip;
  self.parent.document.getElementById('usr_alamat').value = alamat;
  self.parent.document.getElementById('usr_no_hp').value = hp;
  self.parent.document.getElementById('struk_nama').value = struk_nama;
  //var str = e.options[e.selectedIndex].text;

  //self.parent.document.getElementById('pgw_nip').value = nip;

	self.parent.tb_remove();
}

function Search() {

	var nama = document.getElementById('_name').value;

	GetData(nama,'target=dv_hasil');

}

</script>

<form name="frmSearch">
<table border="1" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
			
      <tr class="tablesmallheader">
				<td colspan="2">&nbsp;</td>
			</tr>
      <tr class="tablesmallheader">
				<td colspan="2"><center>PENCARIAN&nbsp;</center></td>
			</tr>
     
          
          <!--<tr class="tablesmallheader" >
				<td colspan="2"><center>Pencarian&nbsp;</center></td>
			</tr>-->

			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Pegawai</td>
				<td class="tablecontent-odd">
					<?php echo $view->RenderTextBox("_name","_name",60,255,$_POST["_name"],false,false);?>
				</td>
			</tr>

			<tr>
				<td colspan="2"><center>
				  <input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search()" />      
					<input type="button" name="btnClose" value="Tutup" OnClick="self.parent.tb_remove()" class="submit" /></center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>



</form>

<div id="dv_hasil"></div>

<?php echo $view->SetFocus("_name",true);?>
<br /><br /><br /><br />
