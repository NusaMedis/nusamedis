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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $depId = $auth->GetDepId();
 
 
    if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
	/* if(!$auth->IsAllowed("man_medis_setup_sub_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_medis_setup_sub_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

	$isAllowedCreate=1;
	$isAllowedUpdate=1;
	$isAllowedDel=1;

   if($_GET["id_instalasi"]) $_POST["id_instalasi"] = $_GET["id_instalasi"];
   
   $editPage = "sub_instalasi_edit.php?konf=".$_GET["konf"]."&id_instalasi=".$_POST["id_instalasi"];
   $thisPage = "sub_instalasi_view.php?konf=".$_GET["konf"];
  
   $sql = "select a.*, b.instalasi_nama from global.global_auth_sub_instalasi a left join global.global_auth_instalasi b on b.instalasi_id=a.id_instalasi where a.id_dep = '$depId'";
   if($_POST["id_instalasi"]) $sql .= " and a.id_instalasi=".QuoteValue(DPE_CHAR,$_POST["id_instalasi"]); 
   $sql .= " order by sub_instalasi_urut ";
   $rs = $dtaccess->Execute($sql,DB_SCHEMA);
   $dataTable = $dtaccess->FetchAll($rs);

	$table = new InoTable("table1","100%","left",null,1,2,1,null);     
  $tableHeader = "Master Sub Instalasi";

     // --- construct new table ---- //
	$counter=0;
	 $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Sub Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Sub Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "48%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "48%";
     $counterHeader++;
     
  
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
     if($isAllowedDel){     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
      
	 for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["sub_instalasi_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["sub_instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
           if($isAllowedUpdate) {
      
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["sub_instalasi_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
         }      
          
          
         if($isAllowedDel) {
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["sub_instalasi_id"]).'&del=1"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
	
		
     }   
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_ALIGN] = "center";
	   $counter++;
     
     $sql = "select * from global.global_auth_instalasi order by instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataIns = $dtaccess->FetchAll($rs);
     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
	
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
                    <a href="import_sub_instalasi.php" class="btn btn-danger pull-right">Import</a>
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