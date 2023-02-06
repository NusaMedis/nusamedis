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
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
         
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
  
  if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }  
 
 
 
    /* if(!$auth->IsAllowed("man_medis_diagnosa",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_diagnosa",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/      
     
   /*  if(!$_GET["jenis"]) {
          echo "denied";
          exit();
     }    */
     
     // if($_GET["kode"]) $_POST["_kode"] = $_GET["kode"];
     // if($_GET["nama"]) $_POST["_nama"] = $_GET["nama"];
     if($_GET["id_poli"]) $_POST["id_poli"] = $_GET["id_poli"];
     
     $editPage = "diagnosa_edit.php?nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $hapusPage = "diagnosa_edit.php?del=1&nama=".$_POST["_nama"]."&kode=".$_POST["_kode"];
     $thisPage = "diagnosa_view.php?";
     
     
     if($_POST["id_poli"]) $sql_where[] = " id_poli = ".QuoteValue(DPE_CHAR, $_POST["id_poli"]);

     //if($_POST["btnLanjut"] || $_GET["kode"] || $_GET["nama"]){}
     $sql = "select a.*, poli_nama from klinik.klinik_diagnosa a 
     left join global.global_auth_poli b on a.id_poli = b.poli_id";
     if($sql_where) $sql .= " where ".implode(" and ",$sql_where); 
     $sql .= " order by diagnosa_nomor_tanpa_titik";
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);

     
     
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;Master Diagnosa";
     
     $isAllowedDel = $auth->IsAllowed("man_medis_diagnosa",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("man_medis_diagnosa",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("man_medis_diagnosa",PRIV_CREATE);
     
     // --- construct new table ---- //
   
     	$counter=0;
     $counterHeader = 0;
   
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomor Urut";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Code";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Diagnosa";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;


     $tbHeader[0][$counterHeader][TABLE_ISI] = "Deskripsi";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Poli";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "70%";    
     $counterHeader++;

     
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Delete";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "8%";
    $counterHeader++;
   
     
        //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_nomor_tanpa_titik"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_nomor"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["diagnosa_short_desc"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;

           $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";          
          $counter++;
    
    
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["diagnosa_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;


           $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$hapusPage.'&id='.$enc->Encode($dataTable[$i]["diagnosa_id"]).'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Edit" title="Edit" border="0"></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;

         
     }
     
     $colspan = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;


     $sql = "SELECT poli_id, poli_nama from global.global_auth_poli";
     $dataPoli = $dtaccess->FetchAll($sql);

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
                <h3>Master Diagnosa</h3>
              </div>
            </div>

            <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Filter</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="GET">
                   
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Poli</label>
                      <select name="id_poli" class="form-control">
                        <option class="inputField" value="">- Pilih Poli-</option>
                        <?php for ($i = 0, $n = count($dataPoli); $i < $n; $i++) { ?>
                          <option value="<?=$dataPoli[$i]['poli_id']?>" <?=($_POST['id_poli'] == $dataPoli[$i]['poli_id']) ? "selected" : "" ?>><?=$dataPoli[$i]['poli_nama']?></option>
                        <?php } ?>
                      </select>
                    </div>

                  

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <input type="submit" name="btnLanjut" id="btnLanjut" value="   Lanjut   " class=" btn btn-primary">
                      <input type="submit" name="btnExcel" id="btnUrut" value="   Export Excel  " class=" btn btn-primary">
                      <input type="submit" name="btnCetak" value="   Cetak  " class=" btn btn-primary">
                      <?php echo $tombolAdd; ?>
                    </div>

                     
       
                    
                    <div class="clearfix"></div>
                  </form>
                </div>
              </div>
            </div>
          </div>

            <div class="clearfix"></div>

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
                    <script>
                      $("img#delete").click(function(){
                        var id_diag = $(this).data("href");

                        var r = confirm("Yakin akan menghapus data ini?");
                        if(r == true){
                          window.location.href = '<?=$hapusPage?>&id='+id_diag;
                        }
                        else{

                        }
                      });
                    </script>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->
        <?php
        if($_GET['btnCetak']){
        	?>
        	<script type="text/javascript">
        		window.open("CetakDiagnosa.php?id_poli=<?=$_POST['id_poli']?>");
        	</script>
        	<?php
     	}
        ?>
        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>

<?php require_once($LAY."js.php") ?>

  </body>
</html>