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
 
     $editPage = "masuk_edit.php?konf=".$_GET["konf"];
     $thisPage = "masuk_view.php?konf=".$_GET["konf"];
     $backPage = "prosedur_view.php";
     $detailPage = "detail_masuk_view.php";
     
     $prosedurId=$enc->Decode($_GET["id"]);
     
     /* if(!$auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

	$isAllowedCreate=1;
	$isAllowedUpdate=1;
	$isAllowedDel=1;

     function StripArr($num){
          return StripCurrency($num);
     }
	$rujukanFlag = $enc->Decode($_GET["id"]);
	
	 $sql = "select * from global.global_rujukan 
			 where rujukan_flag = ".QuoteValue(DPE_CHAR,$rujukanFlag);
	 
	 $sql .=" order by rujukan_id ";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataInstalasi = $dtaccess->FetchAll($rs);
    // echo $sql;

	$table = new InoTable("table1","100%","left",null,1,2,1,null);     

     // --- construct new table ---- //
	$counter=0;
	 $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nomor";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Masuk";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "50%";
     $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Detail";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
          
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
     }
     

    //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
	
	 for($i=0,$counter=0,$n=count($dataInstalasi);$i<$n;$i++,$counter=0){
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $i+1;
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataInstalasi[$i]["rujukan_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;


               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$detailPage.'?id='.$enc->Encode($dataInstalasi[$i]["rujukan_flag"]).'&id_cara='.$enc->Encode($dataInstalasi[$i]["rujukan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/cari.png" alt="Detail" title="Detail" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;          
          
           if($isAllowedUpdate) {
      
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_cara='.$enc->Encode($dataInstalasi[$i]["rujukan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_cara='.$enc->Encode($dataInstalasi[$i]["rujukan_id"]).'&del=1"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';               
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
	
	$tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
	$tombolKembali = '<input type="button" name="btnKembali" value="Kembali" class="btn btn-primary" onClick="document.location.href=\''.$backPage.'\'"></button>';
	
	//tampilin prosedur masuk-nya
	$sql = "select * from global.global_prosedur_masuk where prosedur_masuk_id = ".QuoteValue(DPE_CHAR,$prosedurId);
	$rs = $dtaccess->Execute($sql);
	$prosedurMasuk = $dtaccess->Fetch($rs);
	
	  $tableHeader = "&nbsp;Prosedur Masuk : ".$prosedurMasuk["prosedur_masuk_nama"];

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
                    <h2><?php echo $tableHeader;?></h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?><?php echo $tombolKembali; ?></span>
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
                          <? for($i=0,$n=count($dataInstalasi);$i<$n;$i++) {   ?>
                          
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