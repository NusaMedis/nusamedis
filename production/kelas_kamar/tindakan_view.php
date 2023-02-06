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
     $depId = $auth->GetDepId();
     
     $addPage  = "tindakan_edit.php";
     
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
	  
	 
    /* if(!$auth->IsAllowed("man_medis_kelas_kamar",PRIV_READ)){
          die("access_denied");
          exit(1);
          
     } elseif($auth->IsAllowed("man_medis_kelas_kamar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
 
     $editPage = "tindakan_edit.php";
     $thisPage = "tindakan_view.php";


	$isAllowedCreate=1;
	$isAllowedUpdate=1;
	$isAllowedDel=1;

     function StripArr($num){
          return StripCurrency($num);
     }
                                                                                                                  
                                                                                                                  
	$sql = "select * from klinik.klinik_kelas where id_dep = '$depId'" ;
  $sql .= " order by kelas_tingkat asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataKelas = $dtaccess->FetchAll($rs);
    // echo $sql;

	$sql = "select * from klinik.klinik_split where split_flag = ".QuoteValue(DPE_CHAR,SPLIT_INAP)."  order by split_id";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataSplit = $dtaccess->FetchAll($rs);
	
     $sql = "select * from klinik.klinik_biaya_split"; 
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     while($row = $dtaccess->Fetch($rs)) {
		$_POST["txtNom"][$row["id_biaya"]][$row["id_split"]] = $row["bea_split_nominal"];
	}
     
	$table = new InoTable("table1","100%","left",null,1,2,1,null);     
  $tableHeader = "&nbsp;Kelas Kamar Rawat Inap";

     // --- construct new table ---- //
	$counter=0;
	 $counterHeader = 0;
     
     
     
  

     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Kelas Kamar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "82%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tingkat Kelas";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;

     
	       if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
          $counterHeader++;
     }
     
	   //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
	 for($i=0,$counter=0,$n=count($dataKelas);$i<$n;$i++,$counter=0){
          
             $tbContent[$i][$counter][TABLE_ISI] = $dataKelas[$i]["kelas_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
             $tbContent[$i][$counter][TABLE_ISI] = $dataKelas[$i]["kelas_tingkat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; 
          
          if($isAllowedUpdate) {
          
          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataKelas[$i]["kelas_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          
               $tbContent[$i][$counter][TABLE_ISI] ='<a href="'.$editPage.'?id='.$enc->Encode($dataKelas[$i]["kelas_id"]).'&del=1"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';           
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
       
               }                                                                                                     
          
       
          
	
		/*for($j=0,$k=count($dataSplit);$j<$k;$j++){
			$tbContent[$i][$counter][TABLE_ISI] = currency_format($_POST["txtNom"][$dataBiaya[$i]["biaya_id"]][$dataSplit[$j]["split_id"]]);
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
		      $tbContent[$i][$counter][TABLE_ISI] = $namaVisite[$dataBiaya[$i]["biaya_tambahan"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
	
		
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataBiaya[$i]["biaya_total"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;    */
		
     }   


     //if($isAllowedUpdate) $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnUpdate" value="Simpan" class="button">&nbsp;';
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     $tbBottom[0][0][TABLE_ALIGN] = "center";
	$counter++;
	
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
                    <a href="import_kelas_kamar.php" class="btn btn-danger pull-right">Import</a>
                    <h2><?php echo $tableHeader;?></h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <button onClick="document.location.href='<?php echo $addPage;?>'" class="btn btn-primary" type="button">Tambah</button>
                      <li class="dropdown">
        
                    </ul>
                    <div class="clearfix"></div>
                  </div>

                  <div class="x_content">
                    <div class="table-responsive">
                      <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      
                        <thead>
                          <tr class="headings">         
                           <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                               <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                            <? } ?>
                              
                          </tr>
                        </thead>

                        <tbody>
                        <? for($i=0,$n=count($dataKelas);$i<$n;$i++) {   ?>
                          
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
