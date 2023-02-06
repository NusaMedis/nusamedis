<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $usrId = $auth->GetUserId();
     //$editPage = "trans_beli_po_edit.php?";
     $thisPage = "retur_penjualan_view.php?";
     $itemPage = "retur_penjualan_item.php?";
     //$orderPage = "trans_beli_po_order.php?";
     $terimaPage = "retur_penjualan_item_terima.php?";
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
     $theDep = $auth->GetNamaLogistik();
     
     $sql =" select * from apotik.apotik_conf where conf_id = 'asa' ";
    $rs_edit = $dtaccess->Execute($sql);
    $dataOnOff = $dtaccess->Fetch($rs_edit);
     
     // PRIVILLAGE
 if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 
     
     $skr = date("d-m-Y");
     
     if(!$_POST['tanggal_awal']){
     $_POST['tanggal_awal']  = $skr;
     }
     if(!$_POST['tanggal_akhir']){
     $_POST['tanggal_akhir']  = $skr;
     }
     
     if($_POST["tanggal_awal"]) $sql_where[] = "a.retur_penjualan_tgl >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     if($_POST["tanggal_akhir"]) $sql_where[] = "a.retur_penjualan_tgl <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select * from logistik.logistik_retur_penjualan a 
             left join apotik.apotik_penjualan b on b.penjualan_id = a.id_penjualan 
             where a.id_dep like '".$depId."%' and retur_penjualan_lunas = 'n' and b.id_gudang='2' ";//Gudang dipaten ke apotik
     $sql .= " and ".$sql_where;
     $sql .= " order by retur_penjualan_tgl desc, retur_penjualan_lunas asc, retur_penjualan_urut asc";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA);
     $dataTable = $dtaccess->FetchAll($rs);
     //echo $sql;
     
     //*-- config table ---*//
     $tableHeader = "&nbsp;RETUR PENJUALAN";
     
   /*  $isAllowedDel = $auth->IsAllowed("inv_proses_pembelian_po",PRIV_DELETE);
     $isAllowedUpdate = $auth->IsAllowed("inv_proses_pembelian_po",PRIV_UPDATE);
     $isAllowedCreate = $auth->IsAllowed("inv_proses_pembelian_po",PRIV_CREATE);    */
     
     // --- construct new table ---- //
     $counterHeader = 0;
    // if($isAllowedDel){
//          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
 //         $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
 //         $counterHeader++;
    // }
     
     //if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     //}
    // if($isAllowedUpdate){
    //      $tbHeader[0][$counterHeader][TABLE_ISI] = "Receive";
    //      $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
    //      $counterHeader++;
    // }
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Retur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No Nota";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
    
	//TOTAL HEADER TABLE
      $jumHeader= $counterHeader;
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
        
      //if($dataTable[$i]["retur_pembelian_lunas"]=="n"){ 
        
        //if($isAllowedDel) {
//               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$thisPage.'del=1&id='.$enc->Encode($dataTable[$i]["retur_penjualan_id"]).'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" class="tombol" title="Hapus" border="0"></a>';               
//               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
//               $counter++;
          //}
         
         // if($isAllowedUpdate) {
                             //if ( $dataOnOff["conf_apotik_central"] == 'y') {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$itemPage.'transaksi='.$enc->Encode($dataTable[$i]["retur_penjualan_id"]).'&id_penjualan_trans='.$enc->Encode($dataTable[$i]["id_penjualan"]).'"><img hspace="2" src="'.$ROOT.'gambar/icon/edit.png" width="32" height="32" class="tombol" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++; /*} else {
               $tbContent[$i][$counter][TABLE_ISI] = '';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
               
               }*/
          //}
         /*  }else{
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;         
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;    
           } */
      
         // jika data sudah di retur maka recevid supplier bisa aktif
         /*if($dataTable[$i]["retur_pembelian_lunas"]=="y"){ 
        
                if($isAllowedUpdate) {
                     $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$terimaPage.'terima=1&transaksi='.$enc->Encode($dataTable[$i]["retur_pembelian_id"]).'&klinik='.$_POST["klinik"].'"><img hspace="2" src="'.$ROOT.'gambar/finder.png" class="tombol" alt="Edit" title="Edit" border="0"></a>';               
                     $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                     $counter++;
                }
        
         }else{
               
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;           
         } 
        */
        
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["retur_penjualan_tgl"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["retur_penjualan_nomor"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["penjualan_nomor"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
  
     }
     
     $colspan = count($tbHeader[0]);
     
     /*if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'tambah=1\'">&nbsp;';
     } */
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
     
          
       if ($_GET["del"]) {
           $returId = $enc->Decode($_GET["id"]);
    
           $sql = "delete from logistik.logistik_retur_penjualan where retur_penjualan_id = ".QuoteValue(DPE_CHAR,$returId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
           
           $sql = "delete from logistik.logistik_retur_penjualan_detail where retur_penjualan_detail_id = ".QuoteValue(DPE_CHAR,$returId);
           $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);

           $kembali = "retur_penjualan_view.php?kembali=".$_POST["klinik"];
          
                  header("location:".$kembali);
                  exit();   
     }
     
?>


<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script language="JavaScript">

function CheckDel(frm)
                    {
                           if (confirm("Semua transaksi yang terdapat barang tersebut akan dihapus, Apakah anda yakin ingin menghapus barang?")==1)
                           {
                                document.frmView.submit();
                            } else { 
                         return false;
                        }
                  }
/*  function reklinik(kliniks) {
   document.location.href='item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }  */

  function rejenis(jenis) {
   document.location.href='narkotika_view.php?klinik='+jenis+'&currentPage=<?php echo $_GET["currentPage"];?>&recPerPage=<?php echo $_GET["recPerPage"];?>';
  }
  
function CheckData() {
	if(confirm('Anda Yakin Ingin Memberikan Flag Narkotika Pada Item Ini?')){
		document.frmView.btnSave.value = 'Simpan';
		document.location.href='narkotika_view.php';
	}
}
  
function editData() {
	if(confirm('Anda Yakin Ingin Mengubah Flag Narkotika Pada Item Ini?')){
		document.frmView.btnSave.value = 'Simpan';
		document.location.href='narkotika_view.php?edit=1';
	}
}

</script>
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
                <h3>Retur Penjualan</h3>
              </div>
            </div>
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Periode</h2>
                    <div class="clearfix"></div>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
				  </div>
                  <div class="x_content">
				  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input  id="tanggal_awal" name="tanggal_awal" type='text' class="form-control" value="<?php echo date('d-m-Y') ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  id="tanggal_akhir" name="tanggal_akhir"  type='text' class="form-control" value="<?php echo date('d-m-Y') ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						<input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary">
						<input type="button" name="btnTambah" value="Tambah" class="btn btn-success" onClick="document.location.href='retur_penjualan_item.php'" />
          
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
<?php echo $view->SetFocus("btnAdd"); ?>
<?php require_once($LAY."js.php") ?>
  </body>
</html>




















