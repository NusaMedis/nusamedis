 <?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     //INISIALISASI LIBRARY
    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName();
	   $depLowest = $auth->GetDepLowest();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
 
     $editPage = "kat_icd_edit.php";
     $thisPage = "kat_icd_view.php";
     
       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 	
	
	/*   if(!$auth->IsAllowed("man_medis_kategori_icd",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kategori_icd",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
	   if($_GET["klinik"] || $_GET["dep_lowest"]) { 
        $_POST["klinik"] = $_GET["klinik"]; 
        $_POST["dep_lowest"] = $_GET["dep_lowest"];
        
      } else if($_POST["klinik"]) {       
         $klinik = explode("-", $_POST["klinik"]);
         $_POST["klinik"] = $klinik[0]; 
         $_POST["dep_lowest"] = $klinik[1];
         
      } else {        
         $_POST["klinik"] = $depId; 
         $_POST["dep_lowest"] = $depLowest;
      }
      
	   $addPage = "kat_icd_edit.php?klinik=".$_POST["klinik"];
      
     $sql = "select * from  klinik.klinik_kat_icd";
     //if($_POST["klinik"] && $_POST["klinik"]!="--") $sql .=" where id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"]."%");
     $sql .= " order by kat_icd_nama asc";        
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
     

     //*-- config table ---*//
     $tableHeader = "&nbsp;Master Kategori ICD";
     
     // --- construct new table ---- //
     $counterHeader = 0;

		$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;     
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
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
     	
			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kat_icd_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["kat_icd_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;

               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["kat_icd_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
     }
     
      //-----konfigurasi-----//
      $sql = "select * from global.global_departemen";
      $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
      $rs = $dtaccess->Execute($sql);
      $konfigurasi = $dtaccess->Fetch($rs);
      
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
     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     //KEBUTUHAN SEARCHING
     $sql = "select * from klinik.klinik_kelas order by kelas_id";  
     $rs = $dtaccess->Execute($sql);
     $dataKelas = $dtaccess->FetchAll($rs);

     $kelas[0] = $view->RenderOption("","Pilih Semua Kelas",$show);
     for($i=0,$n=count($dataKelas);$i<$n;$i++) {
          unset($show);
          if($_POST["id_kelas"]==$dataKelas[$i]["kelas_id"]) $show = "selected";
          $kelas[$i+1] = $view->RenderOption($dataKelas[$i]["kelas_id"],$dataKelas[$i]["kelas_nama"],$show);
     }

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
                    <h2>Kategori ICD</h2>
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