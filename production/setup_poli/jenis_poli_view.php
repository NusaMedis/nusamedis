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
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $depId = $auth->GetDepId();
     
     
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	 
	 /*if(!$auth->IsAllowed("man_medis_setup_poli",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_setup_poli",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
 
  
     $editPage = "jenis_poli_edit.php?konf=".$_GET["konf"];
     $thisPage = "jenis_poli_view.php?konf=".$_GET["konf"];
     
     if(!$_POST["poli_tipe"]) $_POST["poli_tipe"]=$_GET["tipe"];
      
     
     
      $sql = "select a.*, c.poli_tipe_nama, d.instalasi_nama, e.sub_instalasi_nama,f.gudang_nama,g.gudang_nama as gudang_nonmedis_nama from global.global_auth_poli a
              LEFT JOIN global.global_auth_poli_tipe c ON a.poli_tipe = c.poli_tipe_id  
              LEFT JOIN klinik.klinik_biaya b on a.id_biaya = b.biaya_id
              left join global.global_auth_instalasi d on d.instalasi_id=a.id_instalasi
              left join global.global_auth_sub_instalasi e on e.sub_instalasi_id=a.id_sub_instalasi
              left join logistik.logistik_gudang f on f.gudang_id=a.id_gudang
              left join umum.umum_gudang g on a.id_gudang_nonmedis = g.gudang_id where a.id_dep = '$depId'
              ";
      if($_POST["poli_tipe"]) $sql .= " and a.poli_tipe='".$_POST["poli_tipe"]."'";
      $sql .= " ORDER by a.poli_tipe,a.poli_kode ASC";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataTable = $dtaccess->FetchAll($rs);
      //echo $sqli;

      
      
      $sql= "select * from global.global_auth_poli_tipe order by poli_tipe_id ASC";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      $dataPoli = $dtaccess->FetchAll($rs);

     
     
     //*-- config table ---*//

     
     $isAllowedDel = $auth->IsAllowed("setup_jenis_poli",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("setup_jenis_poli",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("setup_jenis_poli",PRIV_CREATE);
     
     $tableHeader = "Master Setup Poli";
     // --- construct new table ---- //
     $counterHeader = 0;
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++; 
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Poli BPJS";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Sub Instalasi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Gudang Medis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Gudang";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++;

//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Gudang Non-Medis";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++;
     
//     $tbHeader[0][$counterHeader][TABLE_ISI] = "Antrian Poli";
//     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
//     $counterHeader++;
    
     
     /* Ditutup Sementara biar fokus
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Subspesialis";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tambah Sub";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; */
     
     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
     //}
     
    //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
     //}
          
       //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
        unset($spacer); 
		
      	$length = (strlen($dataTable[$i]["poli_tree"])/TREE_LENGTH_CHILD)-1; 
      	for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;";
        
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;
         
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["poli_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$spacer.$dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["poli_bpjs"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["poli_tipe_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["sub_instalasi_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["gudang_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
//          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["gudang_nama"];
//          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//          $counter++;

//          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["gudang_nonmedis_nama"];
//          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//          $counter++;
          
//          if($dataTable[$i]["poli_antrian"]=="y"){
//          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp; Aktif - Urutan : ".$dataTable[$i]["poli_antrian_urut"];
//          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//          $counter++;
//          }else{
//          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp; Tidak Aktif";
//          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//          $counter++;
//          }

          /* ditutup dulu biar fokus
          if($dataTable[$i]["poli_sub"]=="y"){               
          $tbContent[$i][$counter][TABLE_ISI] = "<img hspace='2' width='18' height='18' alt='aktif' title='aktif' border='0' src='".$ROOT."gambar/aktif.png'>";
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "<img hspace='2' width='18' height='18' alt='non aktif' title='non aktif' border='0' src='".$ROOT."gambar/non_aktif.png'>";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&parent='.$dataTable[$i]["poli_tree"].'&tipe='.$dataTable[$i]["poli_tipe"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/anak.png" alt="Tambah" title="Tambah" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;  */
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["poli_id"]).'&tipe='.$dataTable[$i]["poli_tipe"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] ='<a href="'.$editPage.'&del=1&id='.$enc->Encode($dataTable[$i]["poli_id"]).'&parent='.$dataTable[$i]["poli_tree"].'&tipe='.$_POST["poli_tipe"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';           
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
       
          
         
     }
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_ALIGN] = "center";
     
     $colspan = count($tbHeader[0]);
   
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
                    <a href="import_poli.php" class="btn btn-danger pull-right">Import</a>
                    <h2><?php echo $tableHeader;?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
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