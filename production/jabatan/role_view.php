<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     
     // INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
	   $depId = $auth->GetDepId();
	   // Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table","100%","left");
     
     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
   /* if(!$auth->IsAllowed("man_user_jabatan",PRIV_READ) && !$auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)){
          die("access_denied");
          exit(1);
     } elseif($auth->IsAllowed("man_user_jabatan",PRIV_READ)===1 || $auth->IsAllowed("sirs_user_setup_jabatan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
   
  
     $addPage = "role_edit.php";
 
      if($_GET["klinik"]){
       $_SESSION["x_id_jenis_x"] = $_POST["klinik"];
     }else{
       $_GET["klinik"] = $_SESSION["x_id_jenis_x"];
     }
     
     if($_POST["rol_jabatan"] && $_POST["rol_jabatan"]!="--") $sql_where[] = "a.rol_jabatan = ".QuoteValue(DPE_CHAR,$_POST["rol_jabatan"]);                                                                
	   if($_POST["_nama"]) $sql_where[] = "UPPER(rol_name) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
  
     if($sql_where) $sql_where = implode(" and ",$sql_where);
     
     $editPage = "role_edit.php";
     $detPage = "role_act_view.php";
     $thisPage = "role_view.php";
   
if($_POST["btnSearch"]){ }

     $sql = "select a.* from global.global_auth_role a";
     if($sql_where) $sql .= " where ".$sql_where;
     $sql .= " order by rol_name asc"; 
     //echo $sql ,b.dep_id,b.dep_nama  b.dep_id asc ,;
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
     
     // --- ngitung jml data e ---              
 //    $sql = "select count(rol_id) as total from  global.global_auth_role a";
//     if($sql_where) $sql .= " where ".$sql_where;
     
     //echo $sql;
//     $rsNum = $dtaccess->Execute($sql);
//     $numRows = $dtaccess->Fetch($rsNum) ;
     
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;User Role Master";
     
     // --- construct new table ---- //
     $counterHeader = 0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Jabatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%"; 
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Jabatan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%"; 
     $counterHeader++;

     if ($depLowest=='n') {
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%"; 
     $counterHeader++;    
     }
     
     //if($auth->IsAllowed("setup_role",PRIV_READ)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
    // }     
     
    // if($auth->IsAllowed("setup_role",PRIV_UPDATE)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
   //  }
     
     //if($auth->IsAllowed("setup_role",PRIV_DELETE)){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
   //  }
     
	 //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          $tbContent[$i][$counter][TABLE_ISI] =   ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;' .$dataTable[$i]["rol_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;&nbsp;' .$roleJabatan[$dataTable[$i]["rol_jabatan"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $counter++;

          if ($depLowest=='n') {
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          } 
          
          // if($auth->IsAllowed("setup_role",PRIV_READ)) {
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detPage.'?id='.$dataTable[$i]["rol_id"].'&app_id='.$dataTable[$i]["id_app"].'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detail" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
        //  } 
          
         // if($auth->IsAllowed("setup_role",PRIV_UPDATE)) {
         //      if($dataTable[$i]["rol_id"]!='1' && $dataTable[$i]["rol_id"]!='2' && $dataTable[$i]["rol_id"]!='3' && $dataTable[$i]["rol_id"]!='4' && $dataTable[$i]["rol_id"]!='5') 
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
        /*       else
                    $tbContent[$i][$counter][TABLE_ISI] = '';  */
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
        // }
          
         // if($auth->IsAllowed("setup_role",PRIV_DELETE)) {
               if($dataTable[$i]["rol_id"]!='1' && $dataTable[$i]["rol_id"]!='2' && $dataTable[$i]["rol_id"]!='3' && $dataTable[$i]["rol_id"]!='4' && $dataTable[$i]["rol_id"]!='5')
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["rol_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
               else
                    $tbContent[$i][$counter][TABLE_ISI] = '';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
         // }
          
                 
     }
     
     $colspan = 6;
   
     $tbBottom[0][0][TABLE_ISI] = "&nbsp;";
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
                <h3>Manajemen</h3>
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
				  <input type="hidden" name="depId" value="<?php echo $depId ?>" />
				  
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Jabatan</label>
						<?php echo $view->RenderTextBox("_nama","_nama",50,200,$_POST["_nama"],false,false);?>
                    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Jabatan</label>
                    
						<select name="rol_jabatan" class="select2_single form-control" id="rol_jabatan" onKeyDown="return tabOnEnter(this, event);">
							<option class="form_control" value="">[ Pilih Tipe Jabatan ]</option>								
							<option class="form_control" value="<?php echo STS_JAB_DOKTER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_DOKTER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_DOKTER];?></option>
							<option class="form_control" value="<?php echo STS_JAB_PPDS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PPDS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PPDS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_PERAWAT;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_PERAWAT) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_PERAWAT];?></option>
							<option class="form_control" value="<?php echo STS_JAB_STAFF;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_STAFF) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_STAFF];?></option>
							<option class="form_control" value="<?php echo STS_JAB_ANALIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANALIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANALIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_RADIOGRAFER;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_RADIOGRAFER) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_RADIOGRAFER];?></option>
							<option class="form_control" value="<?php echo STS_JAB_FISIOTERAPIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_FISIOTERAPIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_FISIOTERAPIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_ANESTESIS;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_ANESTESIS) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_ANESTESIS];?></option>
							<option class="form_control" value="<?php echo STS_JAB_IPJ;?>" <?php if($_POST["rol_jabatan"]==STS_JAB_IPJ) echo "selected"; ?>><?php echo $roleJabatan[STS_JAB_IPJ];?></option>

						</select>
                    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<?php if ($_GET['btnFilter']) { echo $tombolAdd; } ?>
						<input type="submit" name="btnSearch" value="Cari"  class="col-md-5 col-sm-5 col-xs-5 btn btn-primary"/>
						<input type="button" name="btnAdd" value="Tambah"  class="col-md-6 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href='<?php echo $addPage;?>'">
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
                    <a href="import_jabatan.php" class="btn btn-danger pull-right">Import</a>
                    <h2>User Role Master</h2>
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