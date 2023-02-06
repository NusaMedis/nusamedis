<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."tree.php");
     require_once($LIB."expAJAX.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();  
     $depNama = $auth->GetDepNama();    
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depLowest = $auth->GetDepLowest();
     $depId = $auth->GetDepId();
     $tree = new CTree("hris.hris_struktural","struk_tree", TREE_LENGTH_CHILD);
     $userName = $auth->GetUserName();
     	   
 
 
     $editPage = "dep_edit.php";
     $thisPage = "dep_view.php";
     $detPage = "account_det_view.php";
     $backPage = "dep_view.php";
     
        if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
   
   /*  if(!$auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
	 
	 
	 $sql = "SELECT * FROM hris.hris_struktural"; 
      $sql .= " ORDER BY struk_tree asc";
      //WHERE LENGTH(id_prk) = 2 ORDER BY order_prk";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GL);
     $dataTable = $dtaccess->FetchAll($rs);
	 
     //*-- config table ---*//
     $tableHeader = "Master&nbsp;Satuan&nbsp;Kerja";
     
    // $isAllowedDel = $auth->IsAllowed("setup_departemen",PRIV_DELETE);
    // $isAllowedUpdate = $auth->IsAllowed("setup_departemen",PRIV_UPDATE);
    // $isAllowedCreate = $auth->IsAllowed("setup_departemen",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode SatKer";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  
	 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama SatKer";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  
     
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
    // }
     
   //  if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Anak";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
           
 
    // }
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	unset($spacer); 
		
	$length = (strlen($dataTable[$i]["struk_tree"])/TREE_LENGTH_CHILD)-1; 
	for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;"; 
	
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		  
		  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["struk_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
		  
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$spacer.$dataTable[$i]["struk_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;   
                                                                                     
         if($_POST["btnCetak"]){
         $_x_mode = "cetak" ;      
     	 } 

        $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["struk_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
                
  		  $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?parent='.$enc->Encode($dataTable[$i]["struk_tree"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/anak.png" alt="Tambah Anak" title="Tambah Anak" border="0"></a>';               
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
				
			  $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["struk_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="return Hapus();"></a>'; //(strlen($dataTable[$i]["jbayar_id"])!=TREE_LENGTH)?'<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>':"";                   
        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        $counter++;
				                            
     }
	 
	 if($_POST["struk_id"])  $prkId = & $_POST["struk_id"];
     if(isset($_GET["parent"])) $parentId = $enc->Decode($_GET["parent"]);
	   if($_POST["parent_id"])  $parentId = & $_POST["parent_id"]; 
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
                    <a href="import_satker.php" class="btn btn-danger pull-right">Import</a>
                    <h2>Satuan Kerja</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  	<form name="frmView" method="POST" action="<?php echo $thisPage; ?>">
					             <table class="table" cellspacing="0" width="100%">
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
					         </form>
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