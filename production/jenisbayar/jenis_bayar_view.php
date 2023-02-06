<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
	 require_once($LIB."tree.php");
     require_once($LIB."currency.php");
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","center");
     $tree = new CTree("global.global_jenis_bayar","jbayar_id", TREE_LENGTH_CHILD);
     $depNama = $auth->GetDepNama(); 
      $userName = $auth->GetUserName();
     $depId = $auth->GetDepId();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();
     
     
   /* if(!$auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_setup_jenis_bayar",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

     
     //$plx = new expAJAX("CheckKode,GetReg");
     
     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;  
     
     $editPage = "jenis_bayar_edit.php";
     $thisPage = "jenis_bayar_view.php";
     $cetakPage = "jenis_bayar_cetak.php";

	    /*$imgPub["y"] = '<img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/oke.png" alt="Tidak Aktif" title="Tidak Aktif" border="0">';
	    $imgPub["n"] = '<img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/no_oke.png" alt="Aktif" title="Aktif" border="0">';
	
	    $linkPub["y"] = 'n';
	    $linkPub["n"] = 'y';*/


          // -- paging config ---//
     $recordPerPage = 10;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//

     if($_POST["_nama"]) $sql_where[] = "UPPER(jbayar_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
	   
	   if($sql_where) $sql_where = implode(" and ",$sql_where);
	   
     $sql = "select a.* from global.global_jenis_bayar a";
     if($sql_where) $sql .= " where a.id_dep like '".$_POST["klinik"]."%' and ".$sql_where;
     $sql .= " order by a.jbayar_id asc";
     $rs = $dtaccess->Execute($sql);
     //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
      // --- ngitung jml data e ---              
     $sql = "select count(jbayar_id) as total from  global.global_jenis_bayar";
     if($sql_where) $sql .= " where ".$sql_where;
     //echo $sql;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum);
     
     //*-- config table ---*//
     $tableHeader = "Daftar&nbsp;Jenis&nbsp;Bayar";
     
    // $isAllowedDel = $auth->IsAllowed("setup_departemen",PRIV_DELETE);
    // $isAllowedUpdate = $auth->IsAllowed("setup_departemen",PRIV_UPDATE);
    // $isAllowedCreate = $auth->IsAllowed("setup_departemen",PRIV_CREATE);
     
     // --- construct new table ---- //
     $counterHeader = 0;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;  
     
     //if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
    // }
     
   //  if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tambah";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
          
    	  $tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
	  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
	  $counterHeader++;  
 
    // }
      //TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){ 
	unset($spacer); 
		
	$length = (strlen($dataTable[$i]["jbayar_id"])/TREE_LENGTH_CHILD)-1; 
	for($j=0;$j<$length;$j++) $spacer .= ".&nbsp;.&nbsp;"; 
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$spacer.$dataTable[$i]["jbayar_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;   
                                                                                     
         if($_POST["btnCetak"]){
         $_x_mode = "cetak" ;      
     	 } 
        //  if($isAllowedDel) {
              // if($dataTable[$i]["jbayar_lowest"]=="n") $tbContent[$i][$counter][TABLE_ISI] = '&nbsp';
              // else 
              $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["jbayar_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="return Hapus();"></a>'; //(strlen($dataTable[$i]["jbayar_id"])!=TREE_LENGTH)?'<a href="'.$editPage.'?del=1&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>':"";                   
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
        //  }
          
         // if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?id='.$enc->Encode($dataTable[$i]["jbayar_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?parent='.$enc->Encode($dataTable[$i]["jbayar_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/anak.png" alt="Tambah Anak" title="Tambah Anak" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'?pub='.$linkPub[$dataTable[$i]["jbayar_aktif"]].'&id='.$enc->Encode($dataTable[$i]["jbayar_aktif"]).'">'.$imgPub[$dataTable[$i]["jbayar_aktif"]].'</a>';
	       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
	       $counter++;
        //  }
            
     }
     
     $sql = "select * from global.global_departemen where dep_id=".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
     
    
          $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\''.$editPage.'\'"></button>';
     
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
                    <h2>Jenis Bayar</h2>
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