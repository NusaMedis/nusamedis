<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");     
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depLowest = $auth->GetDepLowest();
     
     $editPage = "propinsi_edit.php";
     $thisPage = "propinsi_view.php";
     $detailPage = "propinsi_detail.php";
	 $tmbh = "tambah_prop.php";
    
       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 


	
    /* if(!$auth->IsAllowed("man_medis_kecamatan",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kecamatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/

     // cek departemennya //
	   if($_GET["klinik"]) { 
       $_POST["klinik"] = $_GET["klinik"]; 
     } else if($_POST["klinik"]) { 
       $_POST["klinik"] = $_POST["klinik"]; 
     } else { $_POST["klinik"] = $depId; }
      
	   $addPage = "kecamatan_edit.php?klinik=".$_POST["klinik"];
       
 //        echo $sql;
        $sql = "select * from global.global_lokasi where
              lokasi_kabupatenkota = '00' and lokasi_kecamatan ='00' and lokasi_kelurahan ='0000'";
        $sql .= " order by lokasi_kode asc" ;
        $rs = $dtaccess->Execute($sql);
        $dataProp = $dtaccess->FetchAll($rs);
        
     //*-- config table ---*//
     $tableHeader = "&nbsp;Master Data Propinsi";
     $table = new InoTable("table1","50%","left",null,0,2,1,null);

     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";     
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Propinsi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "35%";     
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";      
     $counterHeader++;
     
       //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
      
      for($i=0,$counter=0,$n=count($dataProp);$i<$n;$i++,$counter=0){
          
     
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataProp[$i]["lokasi_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataProp[$i]["lokasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detailPage.'?id='.$enc->Encode($dataProp[$i]["lokasi_id"]).'&prop='.$dataProp[$i]["lokasi_propinsi"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detail" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?edit=1&id='.$enc->Encode($dataProp[$i]["lokasi_id"]).'&prop='.$dataProp[$i]["lokasi_propinsi"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
     
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'?del=1&id='.$enc->Encode($dataProp[$i]["lokasi_id"]).'&prop='.$dataProp[$i]["lokasi_propinsi"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a>';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
     }

          

     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);

if($_GET["del"]){
  $idLokasi = $enc->Decode($_GET["id"]);
  $idProp = $_GET["prop"];
  
  $sql = "select * from global.global_lokasi where lokasi_propinsi = '".$idProp."' and lokasi_kabupatenkota <>'00'";
  $rs  = $dtaccess->Execute($sql);
  $datapropHapus = $dtaccess->Fetch($rs);
//   echo $sql;// die();
  if($datapropHapus){
      echo "<script type='text/javascript'>alert('Propinsi ini memiliki Kabupaten/Kota !!!!');</script>";
  }else{
  $sql = "delete from global.global_lokasi where lokasi_id = '".$idLokasi."'";
  $rs = $dtaccess->Execute($sql);
  
}
}    
$tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$tmbh.'\'"></button>';          
?>
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
            <div class="page-title">
              <div class="title_left">
              
                <h3>Manajemen</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2><?php echo $tableHeader;?></h2>
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
                          <? for($i=0,$n=count($dataProp);$i<$n;$i++) {   ?>
                          
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