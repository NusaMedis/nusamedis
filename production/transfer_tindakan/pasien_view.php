<?php
     require_once("../penghubung.inc.php");
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
	   $depId = $auth->GetDepId();
	   $depLowest = $auth->GetDepLowest();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $editPage = "pasien_edit.php";
     $thisPage = "pasien_view.php";
     $regPage = "detail_pasien.php";
     $PageJenisBiaya = "page_jenis_biaya.php";    
     $plx = new expAJAX("GetData");
   
        if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }   
   
   /*if(!$auth->IsAllowed("man_medis_det_kat_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_det_kat_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
    	$isAllowedCreate=1;
    	$isAllowedUpdate=1;
    	$isAllowedDel=1;

    //AJAX untuk menampilkan tabel transaksi pemesanan
    function GetData($in_nama,$in_kode) 
    {
  		global $dtaccess,$depId,$enc,$table,$poliId,$klinik,$view,$ROOT,$splitsId,$addPage,$editPage,$regPage,$thisPage,$PageJenisBiaya,$PageKategoriTindakan,$totalHargaTax,$detPage;
  	
       // -- paging config ---//
       $recordPerPage = 250;
       if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
       else $currPage = 1;
       $startPage = ($currPage-1)*$recordPerPage;
       $endPage = $startPage + $recordPerPage;
       // -- end paging config ---//
     
     
     	if($in_nama) $sql_where[] = "UPPER(cust_usr_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
    	if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
         
      if($sql_where) $sql_where = implode(" and ",$sql_where);
  
   	  $sql = "select a.* 
             from global.global_customer_user a ";
      if($sql_where) $sql .= " where 1=1 and ".$sql_where;       
      $sql .= "order by cust_usr_nama asc";
      $rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataPasien = $dtaccess->FetchAll($rs);
      echo $sql;
     
       $sql = "select count(cust_usr_id) as total from global.global_customer_user a";
       if($sql_where) $sql .= " where 1=1 and ".$sql_where;
       //echo $sql;
       $rsNum = $dtaccess->Execute($sql);
       $numRows = $dtaccess->Fetch($rsNum);
       //return $sql;  SPLIT_TINDAKAN
     
	    
     $table = "<table border='1' width='100%'>";
     
     $table .= "<tr class='subheader'>";
  //   $table .= "<td width='35%' colspan='20' align='right'>".'<a href="'.$editPage.'?tambah='.$klinik.'&klinik='.$klinik.'&dep_lowest='.$lowlest.'&id_poli='.$poliId.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0" class="tombol"></img></a>'."</td>";
     $table .= "</tr>";
     
     $table .= "<tr class='subheader'>";
     $table .= "<td width='35%' colspan='1' align='left'>Total Pasien : ".$numRows["total"]."</td>";
     $table .= "<td width='35%' colspan='10' align='right'>".$view->RenderPaging($numRows["total"], $recordPerPage, $currPage)."</td>";
     $table .= "</tr>";
     
     $table .= "<tr>";
     $table .= "<td width='15%' class='subheader' align='center'>Kode Pasien</td>";
     $table .= "<td width='15%' class='subheader' align='center'>Nama Pasien</td>";
     $table .= "<td width='25%' class='subheader' align='center'>Alamat</td>";
     $table .= "<td width='5%' class='subheader' align='center'>Umur</td>";
     $table .= "<td width='5%' class='subheader' align='center'>Edit</td>";
 //    $table .= "<td width='5%' class='subheader' align='center'>Edit</td>";
 //    $table .= "<td width='5%' class='subheader' align='center'>Hapus</td>";
     $table .= "</tr>";

     
    for($i=0,$n=count($dataPasien);$i<$n;$i++)
    {
     $umur=split('~',$dataPasien[$i]["cust_usr_umur"]);
     
     $table .= "<tr>";
     $table .= "<td width='10%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_kode"]."</td>";
     $table .= "<td width='25%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_nama"]."</td>";		
     $table .= "<td width='10%' class='tablecontent'>&nbsp;".$dataPasien[$i]["cust_usr_alamat"]."</td>";
     $table .= "<td width='1%' class='tablecontent'>&nbsp;".$umur[0]." thn</td>";
     $table .= "<td width='5%' class='tablecontent' align='center'>".'<a href="'.$regPage.'?id_cust_usr='.$enc->Encode($dataPasien[$i]["cust_usr_id"]).'&id_dep='.$dataPasien[$i]["id_dep"].'&id_poli='.$dataPasien[$i]["id_poli"].'&dep_lowest='.$lowlest.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/finder.png" alt="Edit" title="Edit" border="0" class="tombol"></img></a>'."</td>";
     
 //    $table .= "<td width='5%' class='tablecontent' align='center'>".'<a href="'.$editPage.'?id='.$enc->Encode($dataPasien[$i]["cust_usr_id"]).'&id_dep='.$dataPasien[$i]["id_dep"].'&id_poli='.$dataPasien[$i]["id_poli"].'&dep_lowest='.$lowlest.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0" class="tombol"></img></a>'."</td>";
 //    $table .= "<td width='5%' class='tablecontent' align='center'>".'<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataPasien[$i]["cust_usr_id"]).'&id_poli='.$dataPasien[$i]["id_poli"].'&dep_lowest='.$lowlest.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" class="tombol" onclick="javascript: return hapus();"></img></a>'."</td>";
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

function Search() {
	var nama = document.getElementById('_name').value;
	var kode = document.getElementById('_kode').value;

	GetData(nama,kode,'target=dv_data');
}               

</script>


<script language="JavaScript">

  //function rejenis(kliniks) {
  //alert(kliniks);
  // document.location.href='pasien_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  //}
  
</script>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>

        <!-- top navigation -->
          <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Status Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">  
        <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kode Pasien</label>
					<?php echo $view->RenderTextBox("_kode","_kode",60,255,$_POST["_kode"],false,false);?>
				</div>
			<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
					<?php echo $view->RenderTextBox("_name","_name",60,255,$_POST["_name"],false,false);?>
			</div>
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
					<input type="button" name="btnSearch" value="Cari" class="btn btn-success" onClick="Search()"/>
        </div>
</form>
<!--<table class="table table-striped table-bordered dt-responsive nowrap" width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr class="tableheader">
          <td><?php echo $tableHeader;?></td>
     </tr>
     <tr> 
        <td colspan="<?php echo ($jumContent);?>"><div align="right">
            <input type="button" name="btnAdd" value="TAMBAH" id="btnAdd" class='submit' onClick="document.location.href='<?php echo $editPage;?>'">        
        </td>
    </tr>
</table>-->
<br />
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
  <table class="table table-striped table-bordered dt-responsive nowrap" width="100%" border="0" cellpadding="0" cellspacing="0">
     <div id="dv_data"></div>
   </table>
</form>
</div>
</div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>