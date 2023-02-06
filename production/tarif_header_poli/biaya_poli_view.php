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
	
	/* if(!$auth->IsAllowed("man_tarif_tarif_header_poli",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_header_poli",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */
     
     //Konfigurasi
     $sql = "select dep_konf_header_klinik from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $Konfigurasi = $dtaccess->Fetch($rs);

     if($_GET["poli"]) $_POST["poli"] = $_GET["poli"];
 
     $editPage = "biaya_poli_edit.php?poli=".$_POST["poli"];
     $thisPage = "biaya_poli_view.php";
    // $plx = new expAJAX("GetData");
 
     if($_POST["btnLanjut"] || $_GET["poli"]){
       $sql = "select a.*, b.poli_nama, c.kategori_tindakan_header_nama, d.tahun_tarif_nama,e.biaya_nama from klinik.klinik_biaya_poli a 
              left join global.global_auth_poli b on b.poli_id=a.id_poli
              left join klinik.klinik_kategori_tindakan_header c on c.kategori_tindakan_header_id=a.id_kategori_tindakan_header
              left join klinik.klinik_tahun_tarif d on d.tahun_tarif_id=a.id_tahun_tarif
              left join klinik.klinik_biaya e on e.biaya_id = a.id_biaya";
       if ($_POST["poli"])
        if ($Konfigurasi["dep_konf_header_klinik"]=='y') {
        $sql .=" where a.id_poli=".QuoteValue(DPE_CHAR,$_POST["poli"]); 
        $sql .=" and a.id_poli <> '' ";
      }
        if ($Konfigurasi["dep_konf_header_klinik"]=='n') {
        $sql .=" where a.id_biaya <> '' ";
      }
       $sql .= " order by a.id_tahun_tarif, a.id_poli, a.id_kategori_tindakan_header";     

       // echo $sql;     
       $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
       $dataTable = $dtaccess->FetchAll($rs);
     }
         
      $sq= "select * from global.global_auth_poli order by poli_nama ASC";
      $rso = $dtaccess->Execute($sq,DB_SCHEMA_KLINIK);
      $dataTab = $dtaccess->FetchAll($rso);
     
     //*-- config table ---*//
     //$tableHeader = "&nbsp;Setup Biaya Poli";
     
    //$isAllowedDel = $auth->IsAllowed("setup_tindakan_admin",PRIV_DELETE);
     //$isAllowedUpdate = $auth->IsAllowed("setup_tindakan_admin",PRIV_UPDATE);
     //$isAllowedCreate = $auth->IsAllowed("setup_tindakan_admin",PRIV_CREATE);
     
     // --- construct new table ---- //
     
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
    if ($Konfigurasi["dep_konf_header_klinik"]=='y') {
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
     $counterHeader++; 
    }

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Kategori Tindakan Header";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;
     if ($Konfigurasi["dep_konf_header_klinik"]=='n') {
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;
     }

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
          $counterHeader++;
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
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
          if ($Konfigurasi["dep_konf_header_klinik"]=='y') {
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          }
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_header_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          if ($Konfigurasi["dep_konf_header_klinik"]=='n') {
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          }         
           $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["biaya_poli_id"]).'&tahun='.$dataTable[$i]["tahun_tarif_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;

           $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&del=1&id='.$enc->Encode($dataTable[$i]["biaya_poli_id"]).'&tahun='.$dataTable[$i]["tahun_tarif_id"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return hapus();"></a>';               
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;
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
                <h3>Manajemen - Tarif Header Klinik</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Filter</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
							
					 <div class="col-md-4 col-sm-6 col-xs-12">
                         <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama poli</label>
						 <select name="poli" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
									<option class="inputField" value="" >- Pilih Poli -</option>
									<?php 
       								 $counter = -1;
       									for($i=0,$n=count($dataTab);$i<$n;$i++){
									?>
									<option class="inputField" value="<?php echo $dataTab[$i]["poli_id"];?>"
       								 <?php if($_POST["poli"]==$dataTab[$i]["poli_id"]) {echo"selected";}?>>
       								 <?php echo $dataTab[$i]["poli_nama"];?>&nbsp;
        							 </option>
        							 <?php } ?>
						</select> 
				    </div>

					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnLanjut" value="   Lanjut   " class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
						<?php echo $tombolAdd; ?>
				    </div>

					<div class="clearfix"></div>
					</form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

            <div class="row">

              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
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