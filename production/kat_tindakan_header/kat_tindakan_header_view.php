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
     
     $thisPage = "kat_tindakan_header_view.php";
     $editPage = "kat_tindakan_header_edit.php";
     $findPage = "akun_prk.php?"; 
     $findPage2 = "akun_prk2.php?";  
     $PageJenisBiaya = "page_jenis_biaya.php";   
     $back = "kat_tindakan_view.php";
     
    /*  if(!$auth->IsAllowed("man_tarif_kat_tindakan_header",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_kat_tindakan_header",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
  
  //SUPAYA COMBO FILTERNYA TIDAK HILANG   
	if($_GET["id_kategori_tindakan_header_instalasi"]) { 
       $_POST["id_kategori_tindakan_header_instalasi"] = $_GET["id_kategori_tindakan_header_instalasi"];
     }
   
  if($_POST["id_kategori_tindakan_header_instalasi"] && $_POST["id_kategori_tindakan_header_instalasi"]!="--") $sql_where[] = "a.id_kategori_tindakan_header_instalasi = ".QuoteValue(DPE_CHAR,$_POST["id_kategori_tindakan_header_instalasi"]);
  
  $tableHeader = "Manajemen-Kat&nbsp;tindakan&nbsp;Header";        

  if($_POST["btnLanjut"])
  { 
	  $sql = "select a.*, b.no_prk as no_prk_pendapatan, b.nama_prk as nama_prk_pendapatan, c.no_prk as no_prk_biaya, c.nama_prk as nama_prk_biaya,
            d.klinik_kategori_tindakan_header_instalasi_nama 
            from klinik.klinik_kategori_tindakan_header a left join gl.gl_perkiraan b on b.id_prk=a.id_prk_pendapatan
            left join gl.gl_perkiraan c on c.id_prk=a.id_prk_biaya
            left join klinik.klinik_kategori_tindakan_header_instalasi d on d.klinik_kategori_tindakan_header_instalasi_id=a.id_kategori_tindakan_header_instalasi";
     if ($sql_where) $sql .= " where 1=1";
     if ($sql_where) $sql .= " and ".implode(" and ",$sql_where);
     $sql .= " order by a.kategori_tindakan_header_urut asc"; 


       $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
       $dataTable = $dtaccess->FetchAll($rs);


	 
     
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

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Urut";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;  

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan Header";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  

     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Akun Perkiraan Pendapatan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     //$counterHeader++;  
	 
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Akun Perkiraan Biaya";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     //$counterHeader++; 

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
	  
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
	
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
		  
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["klinik_kategori_tindakan_header_instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

		      $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_header_urut"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

		      $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kategori_tindakan_header_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
                                                                     
           if($_POST["btnCetak"])
           {
           $_x_mode = "cetak" ;      
    	     } 
           		         	
           $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["kategori_tindakan_header_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;
                 			   				
  			   $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id_kategori_tindakan_header_instalasi='.$dataTable[$i]["id_kategori_tindakan_header_instalasi"].'&id='.$enc->Encode($dataTable[$i]["kategori_tindakan_header_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="return Hapus();"></a>'; //(strlen($dataTable[$i]["jbayar_id"])!=TREE_LENGTH)?'<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>':"";                   
           $tbContent[$i][$counter][TABLE_ALIGN] = "center";
           $counter++;
  				                            
                    
       }
      }

      if($_GET["id_kategori_tindakan_header_instalasi"]) $_POST["id_kategori_tindakan_header_instalasi"] = $_GET["id_kategori_tindakan_header_instalasi"];
      if($_POST["id_kategori_tindakan_header_instalasi"]) $katHeaderIns = $_POST["id_kategori_tindakan_header_instalasi"];
 
     
     $sql = "select * from klinik.klinik_kategori_tindakan_header_instalasi order by klinik_kategori_tindakan_header_instalasi_urut";
     $rs = $dtaccess->Execute($sql);
     $dataInstalasi = $dtaccess->FetchAll($rs);
     
     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-6 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';

   	// Data Kategori Tindakan Header //
     $sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
     $sql .= " order by klinik_kategori_tindakan_header_instalasi_nama asc";
     $rs = $dtaccess->Execute($sql);
     $dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);
	 
	  //combo box Kategori Tindakan Header
     $sql = "select * from klinik.klinik_kategori_tindakan_header_instalasi where id_dep =".QuoteValue(DPE_CHAR,$depId)." 
     order by klinik_kategori_tindakan_header_instalasi_urut";
     //echo $sql;
     $rs = $dtaccess->Execute($sql);
     $dataTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

     
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
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Instalasi</label>
        						  <select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
        						    <option class="inputField" value="" >- Pilih Kategori Tindakan Instalasi -</option>
        				    		 <?php for($i=0,$n=count($dataTindakanHeaderInstalasi);$i<$n;$i++){ ?>
        				   			 <option class="inputField" value="<?php echo $dataTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"];?>"<?php if ($_POST["id_kategori_tindakan_header_instalasi"]==$dataTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo"selected"?>><?php echo $dataTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"];?>&nbsp;</option>
        				  			 <?php } ?>
        				  		</select> 
      				    </div>
      				    <div class="col-md-8 col-sm-6 col-xs-12">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
      						  <input type="button" name="tambah" value="Tambah" class="col-md-3 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href='<?php echo $editPage; ?>'">
      						  <input type="submit" name="btnLanjut" value="Cari" class="col-md-3 col-sm-5 col-xs-5 btn btn-success">
                    <a href="import_header.php" class="btn btn-danger col-md-3">Import</a>
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
            					<?php 
            					if ($_x_mode == "Edit"){
            					echo $view->RenderHidden("kategori_tindakan_header_id","kategori_tindakan_header_id",$kategoriTindakanId);
            					}
            					?>
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
