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
     $tahunTarif = $auth->GetTahunTarif();
     
         if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	
	
	/* if(!$auth->IsAllowed("man_tarif_biaya_pemeriksaan",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_biaya_pemeriksaan",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */


     if (!$_POST["biaya_pemeriksaan_id"])
        $_POST["biaya_pemeriksaan_id"]=$_GET["biaya_pemeriksaan_id"];
     
     
        
     if($_GET["id_poli"]) $_POST["id_poli"]=$_GET["id_poli"];
 
 //function untuk halaman //-----------------------------------------------------------------------------------------//
     $addPage  = "biaya_edit.php?id_tahun_tarif=".$_POST["id_tahun_tarif"]."&id_poli=".$_POST["id_poli"];
     $editPage = "biaya_edit.php?id_tahun_tarif=".$_POST["id_tahun_tarif"];
     $thisPage = "biaya_view.php";
          
      
      
      if($_POST["id_poli"]) $sql_where[] = "a.id_poli=".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
      
      $sql_where[] = "1=1";
      
      if ($_POST["btnLanjut"] || $_GET["id_poli"])
      {            
      $sql= "select a.*, c.biaya_total, d.biaya_nama, e.poli_nama,f.kelas_nama FROM klinik.klinik_biaya_pemeriksaan a 
            left join klinik.klinik_biaya_tarif c on c.biaya_tarif_id=a.id_biaya_tarif
            left join klinik.klinik_biaya d on d.biaya_id=a.id_biaya
            left join global.global_auth_poli e on e.poli_id=a.id_poli
            left join klinik.klinik_kelas f on f.kelas_id=c.id_kelas";      
      if ($sql_where) $sql = $sql." where ".implode(" and ",$sql_where);
      $sql = $sql." order by d.biaya_nama ASC";
      
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
      $dataTable = $dtaccess->FetchAll($rs); 
      //echo $sql;
      }
      
      //die();     
     
    // $plx = new expAJAX("GetData");
    /* if(!$auth->IsAllowed("setup_tindakan_admin",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("setup_tindakan_admin",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }          */            
            
     //*-- config table ---*//
     //$tableHeader = "&nbsp;Setup Biaya Poli";
     
    //$isAllowedDel = $auth->IsAllowed("setup_tindakan_admin",PRIV_DELETE);
     //$isAllowedUpdate = $auth->IsAllowed("setup_tindakan_admin",PRIV_UPDATE);
     //$isAllowedCreate = $auth->IsAllowed("setup_tindakan_admin",PRIV_CREATE);
     
     // --- table header ---------------------------------------------------------------------------------------//
     
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;    
        
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     $counterHeader++; 
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nominal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Shift";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Biaya";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;  */      
                        
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
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
    //table body nya-------------------------------------------------------------------------------------------// 
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
        
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["kelas_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".currency_format($dataTable[$i]["biaya_total"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          /*
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["shift_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
       
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["tipe_biaya_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; */
                    
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["biaya_pemeriksaan_id"]).'&id_poli='.$dataTable[$i]["id_poli"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&del=1&id='.$enc->Encode($dataTable[$i]["biaya_pemeriksaan_id"]).'&id_poli='.$dataTable[$i]["id_poli"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';               
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
     
     $sql = "select * from klinik.klinik_tahun_tarif";
     $rs = $dtaccess->Execute($sql);
     $dataSearch = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_auth_poli where poli_tipe='J' or poli_tipe='M' or poli_tipe='G' order by poli_tipe, poli_id";
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konf = $dtaccess->Fetch($rs);
     
     $tableHeader = "Master Biaya Pemeriksaan";
     
?>



<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-sm">
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
                    <h2>Master Biaya Pemeriksaan</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					        
                  
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Klinik</label>						
						<select name="id_poli" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						<option value="">- Pilih Klinik -</option>
            <?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
            <option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($_POST["id_poli"]==$dataPoli[$i]["poli_id"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"]; ?></option>
            <?php } ?>
          </select>
				    </div>
				    
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnLanjut" value="   Lanjut   " class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
				    </div>
					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						 	<input type="button" name="Tambah" value="Tambah"  class="col-md-6 col-sm-6 col-xs-6 btn btn-primary" onClick="document.location.href='<?php echo $addPage;?>'">
						 		</td>
      					</div>
                  
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

