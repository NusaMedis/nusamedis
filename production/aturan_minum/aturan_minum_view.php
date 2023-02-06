<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depLowest = $auth->GetDepLowest();
     
     $editPage = "aturan_minum_edit.php";
     $thisPage = "aturan_minum_view.php";
     
	 // PRIVILLAGE
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
    /* if(!$auth->IsAllowed("apo_setup_sat_barang",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_setup_sat_barang",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }  */
     
    // if(!$_POST["klinik"]) $_POST["klinik"] = $depId; 
     //if (!$_GET["klinik"]) { $_POST["klinik"] = $depId; }     
     //else if(!$_POST["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; }
 
 if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
      }else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }
      else { $_POST["klinik"] = $depId; }
 
     if ($_GET["kembali"]) $_POST["klinik"]=$_GET["kembali"]; 
     $addPage = "aturan_minum_edit.php?tambah=".$_POST["klinik"];
     // -- paging config ---//
     $recordPerPage = 25;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//

     //$sql_where[] = "1=1";
     
     if($_GET["klinik"]){
       $_SESSION["x_id_jenis_x"] = $_POST["klinik"];
     }else{
       $_GET["klinik"] = $_SESSION["x_id_jenis_x"];
     }

    // if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]); 
     if($_POST["_nama"]) $sql_where[] = "UPPER(aturan_minum_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
     if($sql_where) $sql_where = implode(" and ",$sql_where);
   
     $sql = "select a.* from apotik.apotik_aturan_minum a ";
     if($sql_where) $sql .= " where ".$sql_where;
     $sql .= " order by aturan_minum_nama asc ";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
      //echo $sql;
      
     // --- ngitung jml data e ---              
     $sql = "select count(aturan_minum_id) as total from  apotik.apotik_aturan_minum";
     if($sql_where) $sql .= " where ".$sql_where;
     //echo $sql;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum);
     
     //*-- config table ---*//           
     $tableHeader = "&nbsp;Aturan Minum Obat";
     
   /*  $isAllowedDel = $auth->IsAllowed("apo_setup_sat_barang",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("apo_setup_sat_barang",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("apo_setup_sat_barang",PRIV_CREATE); */
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
                
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++;      
     
     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //} 
      
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
    // }
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;   
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
     
       $tbContent[$i][$counter][TABLE_ISI] = ($startPage+$i+1);               
       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
       $counter++;
                
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["aturan_minum_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;  
          
          //if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["aturan_minum_id"]).'&klinik='.$dataTable[$i]["id_dep"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //}
         // if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["aturan_minum_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          //}
          
     }
     
     $colspan = count($tbHeader[0]);

          $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     
    if($_POST["klinik"]){
       //Data Klinik
       if($depLowest=='n'){
            $sql = "select * from global.global_departemen order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
       }else{
            $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
            $rs = $dtaccess->Execute($sql);
            $dataKlinik = $dtaccess->FetchAll($rs);
        }
     }else{
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
     }
     
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php"); ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        
		<?php require_once($LAY."sidebar.php"); ?>

        <!-- top navigation -->
		<?php require_once($LAY."topnav.php"); ?>
		<!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Apotik</h3>
              </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2> Aturan Minum</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					<form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                      	<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Aturan Minum</label>
						<?php echo $view->RenderTextBox("_nama","_nama",50,200,$_POST["_nama"],false,false);?></div>
                      </div>
					  
                      <div class="ln_solid"></div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnSearch" id="btnSearch" value="cari" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">&nbsp;
				    </div>
                      <?php echo $view->SetFocus("btnAdd"); ?>
                    </form>
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









