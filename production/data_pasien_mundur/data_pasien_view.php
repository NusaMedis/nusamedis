<?php
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
     $depId = $auth->GetDepId();
     $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $editPage = "pasien_edit.php";
     $thisPage = "pasien_view.php";
     $regPage = "kedatangan_pasien.php";
     $PageJenisBiaya = "page_jenis_biaya.php";    
   //  $plx = new expAJAX("GetData");
     
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
     $editPage         = "data_pasien_edit.php?";
     $thisPage         = "data_pasien_view.php";   


		if($_GET["cust_usr_kode"])  $sql_where[] = "cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_GET["cust_usr_kode"]."%");
		if($_GET["cust_usr_nama"])  $sql_where[] = "upper(cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_GET["cust_usr_nama"])."%");
    if($_POST["find_alamat"])  $sql_where[] = "UPPER(cust_usr_alamat) like ".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["find_alamat"])."%");
    if($_POST["find_tgl_lahir"])  $sql_where[] = "cust_usr_tanggal_lahir =".QuoteValue(DPE_CHAR,$tgl);
    $sql_where[] = "cust_usr_nama is not null";
    $sql_where[] = "cust_usr_kode <> '500'";
		 if ($sql_where[0])  $sql_where = implode(" and ",$sql_where);


     // -- paging config ---//
       $recordPerPage = 10;
       if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
       else $currPage = 1;
       $startPage = ($currPage-1)*$recordPerPage;
       $endPage = $startPage + $recordPerPage;
       // -- end paging config ---//

    // if($_POST["btnLanjut"]){ 
      $sql = "select a.cust_usr_id,a.cust_usr_kode,a.cust_usr_nama,a.cust_usr_alamat,a.cust_usr_tanggal_lahir from global.global_customer_user a";
      $sql .= " where 1=1";
      $sql .= " and ".$sql_where;
      $sql .= " order by a.cust_usr_kode desc";
      $rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
   //echo $sql;
      $dataTable = $dtaccess->FetchAll($rs); 
   
      # total pasien
     $sql = "select count(cust_usr_id) as total from global.global_customer_user a";
     if($sql_where) $sql .= " where 1=1 and ".$sql_where;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum);
       //return $sql; 
     
    
    // }  
    
     //*-- config table ---*//
     $tableHeader = "Rekam Medik - Data Pasien";
     
     // --- construct new table ---- //
     $counterHeader=0;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "40%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal Lahir";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
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
          
     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $tbContent[$i][$counter][TABLE_NOWRAP] = true;
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          
          if($isAllowedUpdate){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'id='.$enc->Encode($dataTable[$i]["cust_usr_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
          }
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["cust_usr_id"]).'&del=1"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0"></a>';
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
                <h3>&nbsp;</h3>
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
                  <form name="frmFind" method="GET" action="<?php echo $_SERVER["PHP_SELF"]?>">
			              <div class="col-md-3 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
					            <?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_GET["cust_usr_kode"],false,false);?>   
			              </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
                      <?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_GET["cust_usr_nama"],false,false);?>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat</label>
                      <input type="text" id="find_alamat" class="form-control" name="find_alamat" value="<?php echo $_GET['find_alamat'];?>"> 
                    </div>

                    <div class="col-md-2 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Tanggal Lahir</label>
                       <input type="text" class="form-control" id="find_tgl_lahir" name="find_tgl_lahir" data-inputmask="'mask': '99-99-9999'" value="<?php echo $_GET['find_tgl_lahir'];?>" /> 
                    </div>

				            <div class="col-md-1 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
					            <input type="submit" name="btnLanjut" value="Cari" class="btn btn-primary form-control">
   				          </div>	


					      <div class="clearfix"></div>
					      </form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->

             <!-- Data View Pasien Row 1-->
            <div class="row">             
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Data Pasien</h2>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
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
                          <tr>
                            <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                            <? } ?>
                          </tr>
                         <? } ?>
                      </tbody>
                      <tfoot>
                        <tr>
                            <td colspan="2"><strong>Total Pasien : <?php echo $numRows["total"]; ?></strong></td>
                            <td colspan="4"><strong><?php echo $view->RenderPaging($numRows["total"], $recordPerPage, $currPage) ?></strong></td>
                          </tr>
                      </tfoot>
                    </table>
					
                  </div>
                </div>
              </div>
            </div>
            <!-- END Data Pasien Row 1-->
      
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
          <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
<!-- jQuery -->
<?php require_once($LAY."js.php") ?>

  </body>
</html>           