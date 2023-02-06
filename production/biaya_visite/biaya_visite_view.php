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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();    
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
 
     //function untuk halaman //-----------------------------------------------------------------------------------------//
     $addPage  = "biaya_visite_edit.php";
     $editPage = "biaya_visite_edit.php";
     $detailPage = "biaya_visite_detail_view.php";
     $thisPage = "biaya_visite_view.php";     
     
      $sql_where[] = "1=1";
      
      //$sql= "select a.*, e.biaya_id,e.biaya_nama FROM global.global_biaya_visite a 
          //  left join klinik.klinik_biaya e on e.biaya_id=a.id_biaya";    
            
      $sql= "select * from klinik.klinik_biaya where biaya_jenis_sem = 'PK'";         
      
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataTable = $dtaccess->FetchAll($rs); 


     // --- table header ---------------------------------------------------------------------------------------//
     $tableHeader = "Manajemen - Biaya Visite";
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;        
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
     $counterHeader++;
     
     /*                   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
     $counterHeader++;
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "22%";
     $counterHeader++;  */
     /*                             
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }  */
     
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
    //table body nya-------------------------------------------------------------------------------------------// 
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
       
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          

               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detailPage.'?biaya_id='.$dataTable[$i]["biaya_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detail" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               /*          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["biaya_id"]).'&id_kelas='.$dataTable[$i]["id_kelas"].'&id_kamar='.$dataTable[$i]["id_kamar"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["biaya_visite_id"]).'&id_kelas='.$dataTable[$i]["id_kelas"].'&id_kamar='.$dataTable[$i]["id_kamar"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++; */
          /* 
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["amb_id"]).'"><img hspace="2" width="16" height="16" src="'.$ROOT.'gambar/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
                    
          if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["amb_id"]).'"><img hspace="2" width="16" height="16" src="'.$ROOT.'gambar/b_drop.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          } */
         
     }
     
     $colspan = count($tbHeader[0]);

   
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
     
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
                <h3><?php echo $tableHeader;?></h3>
              </div>
            </div>
			<div class="clearfix"></div>
            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
						 	      <? if  (!$dataTable) { ?><input type="button" name="Tambah" value="Tambah"  class="col-md-6 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href='<?php echo $addPage;?>'"> <? } ?>
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




