<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
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
 
     $editPage = "kabupaten_edit.php";
     $thisPage = "propinsi_detail.php";
     
     $backPage = "propinsi_view.php";
     $detailPage= "kabupaten_detail.php";
     
    /* if(!$auth->IsAllowed("man_medis_kecamatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
     // cari data dprtemen //
	   if($_GET["klinik"]) { 
        $_POST["klinik"] = $_GET["klinik"]; 
     }else if($_POST["klinik"]) { 
        $_POST["klinik"] = $_POST["klinik"]; 
     } else { 
        $_POST["klinik"] = $depId; 
     }

if($_GET["id"]){
  $propId = $enc->Decode($_GET["id"]);

}      
	   $addPage = "kabupaten_add.php?klinik=".$_POST["klinik"]."&prop=".$_GET["prop"];
      
     $sql = "select * from  global.global_lokasi where lokasi_propinsi = ".QuoteValue(DPE_CHAR,$_GET["prop"])." and
             lokasi_kabupatenkota <>'00' and lokasi_kecamatan ='00' and lokasi_kelurahan='0000'";
     $sql .= " order by lokasi_kode asc";        
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);     

     //*-- config table ---*//
     $tableHeader = "&nbsp;MASTER KABUPATEN";
     
     // --- construct new table ---- //
     $counterHeader = 0;
             
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Kabupaten / Kota";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;
     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
    //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     	
			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["lokasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["lokasi_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
 
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detailPage.'?prop='.$_GET["prop"].'&kab='.$dataTable[$i]["lokasi_kabupatenkota"].'&id='.$enc->Encode($dataTable[$i]["lokasi_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detail" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
                         
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?prop='.$_GET["prop"].'&kab='.$dataTable[$i]["lokasi_kabupatenkota"].'&id='.$enc->Encode($dataTable[$i]["lokasi_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?del=1&prop='.$_GET["prop"].'&kab='.$dataTable[$i]["lokasi_kabupatenkota"].'&del=1&id='.$enc->Encode($dataTable[$i]["lokasi_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a>';                
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
     }

if($_GET["del"]){
  $idLokasi = $enc->Decode($_GET["id"]);
  $idProp = $_GET["prop"];
  $idkab = $_GET["kab"];
    
  $sql = "select * from global.global_lokasi where lokasi_propinsi = '".$idProp."' and lokasi_kabupatenkota = '".$idkab."'
          and lokasi_kecamatan <>'00'";
  $rs  = $dtaccess->Execute($sql);
  $dataKabHapus = $dtaccess->Fetch($rs);
//   echo $sql;// die();
  if($dataKabHapus){
      echo "<script type='text/javascript'>alert('Kabupaten ini memiliki Kecamatan !!!!');</script>";
  }else{
  $sql = "delete from global.global_lokasi where lokasi_id = '".$idLokasi."'";
  $rs = $dtaccess->Execute($sql);
  
    $sql = "select * from global.global_lokasi where lokasi_propinsi = '".$idProp."' and lokasi_kabupatenkota='00'";
    $rs = $dtaccess->Execute($sql);
    $dataPropinsi = $dtaccess->Fetch($rs);
    
   $lokpropid = $enc->Encode($dataPropinsi["lokasi_id"]);
    
  $backpage= "propinsi_detail.php?prop=".$idProp."&id=".$lokpropid;
  header("Location:".$backpage);
}
}              

		// Nama Puskesmas
    $sql = "select dep_nama from global.global_departemen where dep_id like '".$_POST["klinik"]."' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataPuskesmas = $dtaccess->Fetch($rs);

    $sql = "select lokasi_nama from global.global_lokasi where lokasi_id = '".$propId."'";
    $rs = $dtaccess->Execute($sql);
    $dataKecamatan = $dtaccess->Fetch($rs);
    
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="javascript" type="text/javascript">
function hapus() {
  if(confirm('apakah anda yakin untuk menghapus Kabupaten ini ?'));
  else return false;
}

function Kembali() {

    document.location.href='<?php echo $backPage;?>';
} 

</script>
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
            <div class="page-title">
              <div class="title_left">
              
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?php echo $tableHeader;?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >									    
					<div class="col-md-4 col-sm-6 col-xs-12">						
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="button" name="btnBack" id="btnBack" value="Kembali" class="submit" onClick="javascript: Kembali();" />
						<td colspan="<?php echo ($jumContent);?>">						
						<a onClick="document.location.href='<?php echo $addPage;?>'"><img hspace="2" width="32" height="32" src="<? echo $ROOT ?>gambar/icon/add.png" alt="Tambah" title="Tambah" border="0" class="tombol"></img></a>
						</td>
               		 </div>
					<div class="clearfix"></div>			
					
					</form>
                  
                </div>
              </div>
            </div>
            
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?php echo $dataKecamatan["lokasi_nama"];?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                        </tr>
                      </thead>
                      <tbody>
                          <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>
                          
                          <tr class="even pointer">
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                            
                          </tr>
                           
                         <? } ?>
                      </tbody>
                    </table>
					
                  
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
