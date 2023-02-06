<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     //INISIALISASI LIBRARY
     $enc = new textEncrypt();
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
     $table = new InoTable("table1","100%","center");
     
     //$depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     //AUTHENTIKASI
     if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }
	 
      
     // AUTH UNTUK CRUD 
     $isAllowedDel = $auth->IsAllowed("man_ganti_password",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_ganti_password",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_ganti_password",PRIV_CREATE);
     
     //DEKLARASI LINK
     $fasilitasPage    = "fasilitas_view.php";
     $bedPage          = "bed_view.php";
     $editPage         = "kamar_edit.php?id_kelas=".$_POST["id_kelas"];
     $thisPage         = "kamar_view.php";
	 
     
     if($_GET["id_kelas"]) $_POST["id_kelas"]=$_GET["id_kelas"];
     
      
      //--mencari jumlah bed--//
     $sql_bed = "select id_kamar,count(bed_id) as total_bed from klinik.klinik_kamar_bed group by id_kamar";
     $rs_bed = $dtaccess->Execute($sql_bed);
     $data_bed = $dtaccess->FetchAll($rs_bed);
     for($i=0,$j=count($data_bed);$i<$j;$i++){
        $jml_bed[$data_bed[$i]["id_kamar"]] = $data_bed[$i]["total_bed"];
      }
      
      $sql = "select a.kamar_id,a.kamar_nama,a.kamar_kode,b.kelas_nama,c.poli_nama, a.id_poli from klinik.klinik_kamar a
           left join klinik.klinik_kelas b on a.id_kelas = b.kelas_id
           left join global.global_auth_poli c on a.id_poli = c.poli_id";
      if($_POST["id_kelas"]) $sql .= " where a.id_kelas='".$_POST["id_kelas"]."'";
      $sql .= " order by kamar_nama asc";
      $rs = $dtaccess->Execute($sql);
      $dataTable = $dtaccess->FetchAll($rs); 
      
    
     //*-- config table ---*//
     $tableHeader = "&nbsp;Kamar Rawat Inap";
     
     // --- construct new table ---- //
     $counterHeader=0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kelas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Kamar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Kamar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Kapasitas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Bed";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;

     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
     if($isAllowedDel){     
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
      
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kelas_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $jml_bed[$dataTable[$i]["kamar_id"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
     
          $tbContent[$i][$counter][TABLE_ISI] = "<a href=\"".$bedPage."?RoomId=".$enc->Encode($dataTable[$i]["kamar_id"])."&RoomName=".$dataTable[$i]["kamar_nama"]."\"><img hspace=\"2\" width=\"25\" height=\"25\" src=\"".$ROOT."gambar/icon/cari.png\" alt=\"Bed\" title=\"Bed\" border=\"0\"></a>";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kamar_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["kamar_id"]).'&del=1&id_poli='.$dataTable[$i]["id_poli"].'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
     }

          

     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     
     if($isAllowedCreate)
     {
          $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     }
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
                    <h2>Master Kamar</h2>
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