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
 
 
    /* if(!$auth->IsAllowed("man_medis_icd10",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_icd10",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }  */       
     
   /*  if(!$_GET["jenis"]) {
          echo "denied";
          exit();
     }    */
      
      $_GET["jenis"]='1';
     if($_GET["kode"]) $_POST["_kode"] = $_GET["kode"];
     if($_GET["nama"]) $_POST["_nama"] = $_GET["nama"];
     if($_GET["jenis"]) $_POST["jenis"] = $_GET["jenis"];
     
     $editPage = "icd_edit.php?jenis=".$_POST["jenis"]."&nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $hapusPage = "icd_edit.php?del=1&jenis=".$_POST["jenis"]."&nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $thisPage = "icd_view.php?jenis=".$_POST["jenis"];

     //if($_POST["btnLanjut"] || $_GET["kode"] || $_GET["nama"]){}
      $page = (isset($_GET['page']))? (int) $_GET['page'] : 1;
    
    // Jumlah data per halaman
    $limit = 10;
    // if($_POST["_kode"]) $sql_where[] = "upper(icd_nomor) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["_kode"])."%");
    if($_GET['icdcode']) $sql_where[] = " upper(icd_nomor) LIKE ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["icdcode"])."%");
    if($_GET['icd_deskripsi']) $sql_where[] = "upper(icd_deskripsi) LIKE ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["icd_deskripsi"])."%");
    if($_GET['icd_nama']) $sql_where[] = "upper(icd_nama) LIKE ".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["icd_nama"])."%");

    $limitStart = ($page - 1) * $limit;
     $sql = "select  * from klinik.klinik_icd ";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where); 
     $sql .= " order by icd_nomor LIMIT ".$limit." OFFSET ".$limitStart;
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
    //  echo $sql;
     
     //*-- config table ---*//
     //$tableHeader = "&nbsp;ICD ";
     $tableHeader.= ($_GET["jenis"]==1)?" 10":" 9";
     
     $isAllowedDel = $auth->IsAllowed("man_medis_icd10",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_medis_icd10",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_medis_icd10",PRIV_CREATE);
     
     // --- construct new table ---- //
   	$tableHeader = "Master ICD 10";
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
   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
          $counterHeader++;
   
	    //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["icd_deskripsi"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
    
     
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["icd_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
        
    

               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$hapusPage.'&id='.$enc->Encode($dataTable[$i]["icd_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
          
         
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
                    <h2><?php echo $tableHeader;?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                  <form name="frmFind" method="GET" action="<?php echo $_SERVER["PHP_SELF"]?>">
         
            
     

          <div class="col-md-4 col-sm-4 col-xs-4">
            <label class="control-label col-md-12">Masukkan Code</label>
          
            <?php echo $view->RenderTextBox("icdcode","icdcode",30,200,$_GET["icdcode"],false,false);?>
						
          </div>


          <div class="col-md-4 col-sm-4 col-xs-4">
            <label class="control-label col-md-12">Nama ICD</label>
          
            <?php echo $view->RenderTextBox("icd_nama","icd_nama",30,200,$_GET["icd_nama"],false,false);?>
          </div>

          <div class="col-md-4 col-sm-4 col-xs-4">
            <label class="control-label col-md-12">Deskripsi ICD</label>
            
            <?php echo $view->RenderTextBox("icd_deskripsi","icd_deskripsi",30,200,$_GET["icd_deskripsi"],false,false);?>
          </div>
                    
          <div class="col-md-12 col-sm-12 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <td class="tablecontent-odd" colspan="5"><input type="submit" name="btnLanjut" value="Lanjut" class="submit"></td>
            </div>                  
          <div class="clearfix"></div>
          </form>
					<table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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

<div align="right">
  <ul class="pagination">
    <?php
    // Jika page = 1, maka LinkPrev disable
    if($page == 1){ 
    ?>        
      <!-- link Previous Page disable --> 
      <li class="disabled"><a href="#">Previous</a></li>
    <?php
    }
    else{ 
      $LinkPrev = ($page > 1)? $page - 1 : 1;
    ?>
      <!-- link Previous Page --> 
      <li><a href="icd_view.php?page=<?php echo $LinkPrev; ?>&icdcode=<?php echo $_GET["icdcode"]; ?>&icd_nama=<?php echo $_GET["icd_nama"]; ?>&icd_deskripsi=<?php echo $_GET["icd_deskripsi"]; ?>">Previous</a></li>
    <?php
      }
    ?>

    <?php
    
     $sql = "select  count(*) as jumlah from klinik.klinik_icd  where icd_jenis = ".QuoteValue(DPE_CHAR,$_POST["jenis"]);
     if($sql_where) $sql .= " and ".implode(" and ",$sql_where); 
     $rs = $dtaccess->Execute($sql);
     $JumlahData = $dtaccess->Fetch($rs);
    // Hitung jumlah halaman yang tersedia
    $jumlahPage = ceil($JumlahData["jumlah"] / $limit); 
    
    // Jumlah link number 
    $jumlahNumber = 1; 
   
    // Untuk awal link number
    $startNumber = ($page > $jumlahNumber)? $page - $jumlahNumber : 1; 
    
    // Untuk akhir link number
    $endNumber = ($page < ($jumlahPage - $jumlahNumber))? $page + $jumlahNumber : $jumlahPage; 
    
    for($i = $startNumber; $i <= $endNumber; $i++){
      $linkActive = ($page == $i)? ' class="active"' : '';
    ?>
      <li<?php echo $linkActive; ?>><a href="icd_view.php?page=<?php echo $i; ?>&icdcode=<?php echo $_GET["icdcode"]; ?>&icd_nama=<?php echo $_GET["icd_nama"]; ?>&icd_deskripsi=<?php echo $_GET["icd_deskripsi"]; ?>"><?php echo $i; ?></a></li>
    <?php
      }
    ?>
    
    <!-- link Next Page -->
    <?php       
    if($page == $jumlahPage){ 
    ?>
      <li class="disabled"><a href="#">Next</a></li>
    <?php
    }
  
  
    else{
      $linkNext = ($page < $jumlahPage)? $page + 1 : $jumlahPage;
    ?>
    <li><a href="icd_view.php?page=<?php echo $linkNext; ?>&icdcode=<?php echo $_GET["icdcode"]; ?>&icd_nama=<?php echo $_GET["icd_nama"]; ?>&icd_deskripsi=<?php echo $_GET["icd_deskripsi"]; ?>">Next</a></li>
    <?php
    }
    ?>
  </ul>
</div>
					
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