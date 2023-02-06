<?php
   // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."currency.php");
     require_once($LIB."expAJAX.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $tables = new inoTable("table","800","left");
	   $depId = $auth->GetDepId();
	   $depNama = $auth->GetDepNama();
	   $userName = $auth->GetUserName(); 
     $depLowest = $auth->GetDepLowest();
     $tahunTarif = $auth->GetTahunTarif();
     
    
	     if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	
	/* if(!$auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_tarif_kategori_tindakan_header_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
     $thisPage = "kat_tindakan_header_instalasi_view.php";
     $editPage = "kat_tindakan_header_instalasi_edit.php";
  
      if($_GET["id_instalasi"]) $_POST["id_instalasi"] = $_GET["id_instalasi"];
  //     echo "tahun tarif ".$_POST["id_tahun_tarif"];
      
     if($_POST["id_instalasi"] && $_POST["id_instalasi"]!="--") $sql_where[] = "a.id_instalasi = ".QuoteValue(DPE_CHAR,$_POST["id_instalasi"]);

if($_POST["btnLanjut"]){ 
   
     $sql = "select a.*, b.dep_nama, c.instalasi_nama from klinik.klinik_kategori_tindakan_header_instalasi a
             left join global.global_departemen b on b.dep_id = a.id_dep
             left join global.global_auth_instalasi c on c.instalasi_id=a.id_instalasi
             where 1=1";
     if ($sql_where) $sql .= " and ".implode(" and ",$sql_where);
     $sql .= " order by a.klinik_kategori_tindakan_header_instalasi_urut asc"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $dataTable = $dtaccess->FetchAll($rs);	   	  

    $tableHeader = "Master&nbsp;Header&nbsp;Instalasi";
     
    // $isAllowedDel = $auth->IsAllowed("setup_departemen",PRIV_DELETE);
    // $isAllowedUpdate = $auth->IsAllowed("setup_departemen",PRIV_UPDATE);
    // $isAllowedCreate = $auth->IsAllowed("setup_departemen",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Header Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  
	 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  
     
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
           
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;
           
 
    // }
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0)
     {
	
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		  
		  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["klinik_kategori_tindakan_header_instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
		  
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;   
                                                                                     
         if($_POST["btnCetak"]){
         $_x_mode = "cetak" ;      
     	 } 
      
         		         	
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["klinik_kategori_tindakan_header_instalasi_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               			   				
			   $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["klinik_kategori_tindakan_header_instalasi_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="return Hapus();"></a>'; //(strlen($dataTable[$i]["jbayar_id"])!=TREE_LENGTH)?'<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>':"";                   
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
				                            
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?pub='.$linkPub[$dataTable[$i]["jbayar_aktif"]].'&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'">'.$imgPub[$dataTable[$i]["jbayar_aktif"]].'</a>';
	       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	       $counter++;
                    
     }
	  }
	 
     if($_POST["klinik_kategori_tindakan_header_instalasi_id"])  $kategoriTindakanId = & $_POST["klinik_kategori_tindakan_header_instalasi_id"];
     // jika di edit //


     $sql = "select * from global.global_auth_instalasi order by instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);
     
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
					
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-9 col-sm-9 col-xs-12">Instalasi</label>						
    						<select name="id_instalasi" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
    							<option class="form_control" value="--">- Semua Instalasi</option>
    						<?php for($i=0,$n=count($dataInstalasi);$i<$n;$i++){ ?>
        				<option class="inputField" value="<?php echo $dataInstalasi[$i]["instalasi_id"];?>" <?php if($_POST["id_instalasi"]==$dataInstalasi[$i]["instalasi_id"]) echo"selected"?>><?php echo $dataInstalasi[$i]["instalasi_nama"];?>&nbsp;</option>
        				<?php } ?>
    						</select>
				    </div>
				    <div class="col-md-8 col-sm-6 col-xs-12">
              <label class="control-label col-md-12">&nbsp;</label>	
              <input type="button" name="tambah" value="Tambah" class="col-md-3 btn btn-primary" onClick="document.location.href='<?php echo $editPage; ?>'">
              <input type="submit" name="btnLanjut" value="Cari" class="col-md-3 btn btn-success">
              <a href="import_header_instalasi.php" class="btn btn-danger col-md-3">Import</a>
            </div>
          <br>
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