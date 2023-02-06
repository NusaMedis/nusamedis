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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
         
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
  
  if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
 
 
 
    /* if(!$auth->IsAllowed("man_medis_icd9",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_icd9",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/      
     
   /*  if(!$_GET["jenis"]) {
          echo "denied";
          exit();
     }    */
     
     if($_GET["kode"]) $_POST["_kode"] = $_GET["kode"];
     if($_GET["nama"]) $_POST["_nama"] = $_GET["nama"];
     
     $editPage = "icd9_edit.php?nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $hapusPage = "icd9_edit.php?del=1&nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $thisPage = "icd9_view.php?";
     
     if($_POST["_kode"]) $sql_where[] = "upper(icd9_nomor) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["_kode"])."%");
     if($_POST["_nama"]) $sql_where[] = "upper(icd9_nama) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["_nama"])."%");

     //if($_POST["btnLanjut"] || $_GET["kode"] || $_GET["nama"]){}
     $sql = "select * from klinik.klinik_icd9";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where); 
     $sql .= " order by icd9_nomor";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Master ICD 9";
     
     $isAllowedDel = $auth->IsAllowed("man_medis_icd9",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_medis_icd9",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_medis_icd9",PRIV_CREATE);
     
     // --- construct new table ---- //
   
     	$counter=0;
     $counterHeader = 0;
   
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Code";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Deskripsi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;

     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++;
   
     // if($isAllowedUpdate){
     //      $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     //      $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
     //      $counterHeader++;
     // } 
        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd9_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd9_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd9_short_desc"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
    
    
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["icd9_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
      
    
    // if($isAllowedDel) {
    //            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$hapusPage.'&id='.$enc->Encode($dataTable[$i]["icd9_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';               
    //            $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    //            $counter++;
    //       }
          
         
     }
     
     $colspan = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
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
                    <h2>Master ICD 9</h2>
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