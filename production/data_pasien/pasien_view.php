<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/expAJAX.php");
     require_once($ROOT."lib/tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $editPage = "pasien_edit.php";
     $thisPage = "pasien_view.php";
     $regPage = "kedatangan_pasien.php";
     $PageJenisBiaya = "page_jenis_biaya.php";    
     $plx = new expAJAX("GetData");
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
    	$isAllowedCreate=1;
    	$isAllowedUpdate=1;
    	$isAllowedDel=1;
     

       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 	 
       /*  if(!$auth->IsAllowed("rm_info_data_pasien",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("rm_info_data_pasien",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
      
    if ($_POST["btnRegistrasi"])     
      {          
                         
  		header("location:".$ROOT."module/loket_irj/kunjungan_pasien_irj/registrasi.php");
  		exit();       		
     }


     
      
     
    //AJAX untuk menampilkan tabel transaksi pemesanan
    function GetData($in_nama,$in_namapasangan,$in_kode,$in_alamat,$in_tgl,$in_tglpasangan) 
    {
  		global $dtaccess,$depId,$enc,$table,$poliId,$klinik,$view,$ROOT,$ROOT,$splitsId,$addPage,$editPage,$regPage,$thisPage,$PageJenisBiaya,$PageKategoriTindakan,$totalHargaTax,$detPage;
  	
       // -- paging config ---//
       $recordPerPage = 50;
       if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
       else $currPage = 1;
       $startPage = ($currPage-1)*$recordPerPage;
       $endPage = $startPage + $recordPerPage;
       // -- end paging config ---//
     
     
     	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
     	if($in_namapasangan) $sql_where[] = "UPPER(cust_usr_istri) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_namapasangan."%"));
    	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
    	if($in_alamat) $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_alamat."%"));
      if($in_tgl) $sql_where[] = "cust_usr_tanggal_lahir = ".QuoteValue(DPE_CHAR,date_db($in_tgl));
      if($in_tglpasangan) $sql_where[] = "cust_tgllahir_pasangan = ".QuoteValue(DPE_CHAR,date_db($in_tglpasangan));
                     
      if($sql_where) $sql_where = implode(" and ",$sql_where);
  
   	  $sql = "select a.* 
             from global.global_customer_user a ";
      if($sql_where) $sql .= " where 1=1 and ".$sql_where;       
      $sql .= "order by cust_usr_nama asc";
      $rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataPasien = $dtaccess->FetchAll($rs);
      //return $sql;
     
       $sql = "select count(cust_usr_id) as total from global.global_customer_user a";
       if($sql_where) $sql .= " where 1=1 and ".$sql_where;
       //echo $sql;
       $rsNum = $dtaccess->Execute($sql);
       $numRows = $dtaccess->Fetch($rsNum);
       //return $sql;  SPLIT_TINDAKAN
     
	    
     $table = "<table border='1' width='100%'>";
     
     $table .= "<tr class='subheader'>";
     $table .= "<td colspan='7' align='right'>".'<a href="'.$editPage.'?tambah='.$klinik.'&klinik='.$klinik.'&dep_lowest='.$lowlest.'&id_poli='.$poliId.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0" class="tombol"></img></a>'."</td>";
     $table .= "</tr>";
     
     $table .= "<tr class='subheader'>";
     $table .= "<td colspan='3' align='left'>Total Pasien : ".$numRows["total"]."</td>";
     $table .= "<td colspan='4' align='right'>".$view->RenderPaging($numRows["total"], $recordPerPage, $currPage)."</td>";
     $table .= "</tr>";
     
     $table .= "<tr>";
     $table .= "<td width='5%' class='subheader' align='center'>No. RM</td>";
     $table .= "<td width='15%' class='subheader' align='center'>Nama Pasien</td>";
     $table .= "<td width='15%' class='subheader' align='center'>Nama Pasangan</td>";
     $table .= "<td width='25%' class='subheader' align='center'>Alamat</td>";
     $table .= "<td width='5%' class='subheader' align='center'>Umur Pasien</td>";
     $table .= "<td width='5%' class='subheader' align='center'>Tgl Lahir Pasangan</td>";
     //$table .= "<td width='5%' class='subheader' align='center'>Kedatangan</td>";
     $table .= "<td width='5%' class='subheader' align='center'>Edit</td>";
     $table .= "</tr>";

     
    for($i=0,$n=count($dataPasien);$i<$n;$i++)
    {
     $umur=split('~',$dataPasien[$i]["cust_usr_umur"]);
     
     $table .= "<tr>";
     $table .= "<td width='5%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_kode"]."</td>";
     $table .= "<td width='15%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_nama"]."</td>";		
     $table .= "<td width='15%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_istri"]."</td>";
     $table .= "<td width='25%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_alamat"]."</td>";
     $table .= "<td width='5%' class='tablecontent'>&nbsp;".$umur[0]." thn</td>";
     $table .= "<td width='5%' class='tablecontent'>&nbsp;".format_date($dataPasien[$i]["cust_tgllahir_pasangan"])."</td>";
     //$table .= "<td width='5%' class='tablecontent' align='center'>".'<a href="'.$regPage.'?id_cust_usr='.$enc->Encode($dataPasien[$i]["cust_usr_id"]).'&id_dep='.$dataPasien[$i]["id_dep"].'&id_poli='.$dataPasien[$i]["id_poli"].'&dep_lowest='.$lowlest.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/finder.png" alt="Edit" title="Edit" border="0" class="tombol"></img></a>'."</td>";
     
     $table .= "<td width='5%' class='tablecontent' align='center'>".'<a href="'.$editPage.'?id='.$enc->Encode($dataPasien[$i]["cust_usr_id"]).'&id_dep='.$dataPasien[$i]["id_dep"].'&id_poli='.$dataPasien[$i]["id_poli"].'&dep_lowest='.$lowlest.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" class="tombol"></img></a>'."</td>";
     $table .= "</tr>";

     }
     $table .= "</table>";

      return $table;
	      //return $table->RenderView($tbHeader,$tbContent,$tbBottom);
     }
     
     //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
     
    if($konfigurasi["dep_lowest"]=='n'){
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
           
     }else if($_POST["klinik"]){
          $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
          
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
          
     }

     
?>
 
<script language="javascript" type="text/javascript">
<? $plx->Run(); ?>

function submitenter(myfield,e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   Search();
   return false;
   }
else
   return true;
}


// buat nampilkan data semua (Ajax)
function getData() {     
     GetData('target=dv_dataku');
}

// Javascript buat warning jika di klik tombol hapus -,- 
function hapusSplit() {
  if(confirm('apakah anda yakin akan menghapus Split Tindakan ini??? Karena akan otomatis berhubungan dengan laporan dan proses dengan split tindakan ini!!!'));
  else return false;
}

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan menghapus Pasien ini??? Pastikan tidak ada Rekam Medik dan Histroy Kedatangan!!!'));
  else return false;
}

// Javascript buat warning jika di klik tombol hapus -,- 
function hapusmedrec() {
  if(confirm('apakah anda yakin akan menghapus No Medrec ini??? Karena semua data kunjungannya akan hilang!!!'));
  else return false;
}

function Search() {
//  if(document.getElementById('_kode').value == "" && document.getElementById('_name').value == "" && document.getElementById('_alamat').value == ""){
//      alert('Salah satu Filter harus diisi');                            
//			document.getElementById('_kode').focus();
//			return false;    
//  }
  
	var nama = document.getElementById('_name').value;
	var namapasangan = document.getElementById('_namepasangan').value;
	var kode = document.getElementById('_kode').value;
  var alamat = document.getElementById('_alamat').value;
  var tgl = document.getElementById('_tgl').value;
  var tglpasangan = document.getElementById('_tglpasangan').value;
  
	GetData(nama,namapasangan,kode,alamat,tgl,tglpasangan,'target=dv_data');
}   

<?php if($_GET["currentPage"]){ ?>
   GetData("target=dv_data");
<?php } ?>            

</script>

<script language="JavaScript">

  //function rejenis(kliniks) {
  //alert(kliniks);
  // document.location.href='pasien_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  //}
  
</script>
<?php echo $view->RenderBody("module.css",true,true,"DATA PASIEN"); ?>
<br>
<div id="body">
<div id="scroller">

<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">  
<table border="1" width="100%" cellpadding="1" cellspacing="1">
<tr>
	<td>
		<table cellpadding="1" cellspacing="1" border="1" align="center" width="100%">
  
			<tr>
				<td align="right" class="tablecontent" width="30%">No. RM</td>
				<td class="tablecontent-odd">
					<input type="text" name="_kode" id="_kode" size="20" value="<?php echo $_POST["_kode"];?>" onKeyPress="return submitenter(this,event)"/><?php echo $_POST["_kode"];?>
				</td>
			</tr>

			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Pasien</td>
				<td class="tablecontent-odd">
					<input type="text" name="_name" id="_name" size="40" value="<?php echo $_POST["_name"];?>" onKeyPress="return submitenter(this,event)"/><?php echo $_POST["_name"];?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Nama Pasangan</td>
				<td class="tablecontent-odd">
					<input type="text" name="_namepasangan" id="_namepasangan" size="40" value="<?php echo $_POST["_namepasangan"];?>" onKeyPress="return submitenter(this,event)"/><?php echo $_POST["_namepasangan"];?>
				</td>
			</tr>
			<tr>
				<td align="right" class="tablecontent" width="30%">Alamat Pasien</td>
				<td class="tablecontent-odd">
					<input type="text" name="_alamat" id="_alamat" size="60" value="<?php echo $_POST["_alamat"];?>" onKeyPress="return submitenter(this,event)"/><?php echo $_POST["_alamat"];?>

				</td>
			</tr>
      <tr>
				<td align="right" class="tablecontent" width="30%">Tgl Lahir</td>
				<td class="tablecontent-odd">
					<?php// echo $view->RenderTextBox("_tgl","_tgl",20,255,$_POST["_tgl"],false,false);?> 
				  <input type="text"  id="_tgl" name="_tgl" size="15" maxlength="10" value="<?php echo $_POST["_tgl"];?>"/>
          <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          (dd-mm-yyyy)
        </td>
			</tr>
      <tr>
				<td align="right" class="tablecontent" width="30%">Tgl Lahir Pasangan</td>
				<td class="tablecontent-odd">
					<?php //echo $view->RenderTextBox("_tglpasangan","_tglpasangan",20,255,$_POST["_tglpasangan"],false,false);?>
				  <input type="text"  id="_tglpasangan" name="_tglpasangan" size="15" maxlength="10" value="<?php echo $_POST["_tglpasangan"];?>"/>
          <img src="<?php echo $ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
          (dd-mm-yyyy)			
      	</td>
			</tr>
			<tr>
				<td colspan="2">
         <center>
					<input type="button" name="btnSearch" value="Cari" class="submit" onClick="Search()"/>
          <!--<input type="submit" name="btnRegistrasi" value="Registrasi" class="submit"/>-->
         </center>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "_tgl",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    Calendar.setup({
        inputField     :    "_tglpasangan",      // id of the input field
        ifFormat       :    "<?php echo $formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
     <!--<tr> 
        <td colspan="<?php echo ($jumContent);?>"><div align="right">
            <input type="button" name="btnAdd" value="TAMBAH" id="btnAdd" class='submit' onClick="document.location.href='<?php echo $editPage;?>'">        
        </td>
    </tr>-->
</table>
<br />
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
     <div id="dv_data"></div>
</form>

</div>
</div>
<?php echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php echo $view->RenderBodyEnd(); ?>
