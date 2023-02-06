<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."/login.php");
     require_once($LIB."/encrypt.php");
     require_once($LIB."/datamodel.php");
     require_once($LIB."/dateLib.php");
     require_once($LIB."/tampilan.php");
     require_once($LIB."/currency.php");
        
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new TextEncrypt();     
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   //$poli = $auth->GetPoli();
     
     $poli ='33';
	   $sql = "select id_gudang from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$poli);
     $rs = $dtaccess->Execute($sql);
     $gudang = $dtaccess->Fetch($rs); 
     $theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif 

	 if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }

     
     // PRIVILLAGE
   /*  if(!$auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)){
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     }   */
     
     $skr = date("d-m-Y");
     if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
     if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
    
     $sql_where[] = "date(a.retur_penjualan_tgl) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "date(a.retur_penjualan_tgl) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
       if($_POST["nama"] && $_POST["nama"]!="") $sql_where[] = "UPPER(c.cust_usr_nama) like  '".strtoupper($_POST["nama"])."%' ";
     if($_POST["kode"] && $_POST["kode"]!="") $sql_where[] = "c.cust_usr_kode =  '".$_POST["kode"]."' ";
     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
 
     $sql = "select a.*, d.cust_usr_nama, d.cust_usr_kode,e.jenis_nama from logistik.logistik_retur_penjualan a
     left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
     left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
     left join global.global_customer_user d on d.cust_usr_id = b.id_cust_usr
     left join global.global_jenis_pasien e on c.reg_jenis_pasien = e.jenis_id 
     where a.id_dep =".QuoteValue(DPE_CHAR,$depId);
   //  $sql .=" a.penjualan_terbayar = 'y'";
     $sql .= " and ".$sql_where;
     $rs = $dtaccess->Execute($sql);
     $dataTable = $dtaccess->FetchAll($rs);
    // echo $sql;
     //$isAllowedDel = $auth->IsAllowed("pros_penjualan_dlm",PRIV_DELETE);
     //$isAllowedUpdate = $auth->IsAllowed("pros_penjualan_dlm",PRIV_UPDATE);
     //$isAllowedCreate = $auth->IsAllowed("pros_penjualan_dlm",PRIV_CREATE);
     
     // --- construct new table ---- //
     $tableHeader = "&nbsp;Cetak Ulang Retur Penjualan";
     $counterHeader = 0;
     /*if($isAllowedDel){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }
     
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }*/

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
	   $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
	   $counterHeader++;

     /*
     if($isAllowedUpdate){
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Order";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;
     }  */
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Nota";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Retur";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";     
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";     
     $counterHeader++;

    $jumHeader= $counterHeader;
    
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
        
        /*  if($isAllowedDel) {
               $tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["po_id"].'">';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }
 
          if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'id='.$enc->Encode($dataTable[$i]["penjualan_id"]).'"><img hspace="2" width="25" height="25" src="'.$APLICATION_ROOT.'gambar/b_edit.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }*/
               
              $tbContent[$i][$counter][TABLE_ISI] = $m+1;
              $tbContent[$i][$counter][TABLE_ALIGN] = "right";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
              $m++;
    	
              $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="17" height="17" src="'.$ROOT.'gambar/cetak.png" style="cursor:pointer" alt="Cetak Kwitansi" title="Cetak Kwitansi" border="0" onClick="ProsesCetak(\''.$dataTable[$i]["retur_penjualan_id"].'\');"/>';
			        $tbContent[$i][$counter][TABLE_ALIGN] = "center";
              $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"];
              $counter++;
          /*if($isAllowedUpdate) {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'transaksi='.$enc->Encode($dataTable[$i]["po_id"]).'"><img hspace="2" width="16" height="16" src="'.$APLICATION_ROOT.'gambar/b_prop.png" alt="Edit" title="Edit" border="0"></a>';               
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }*/
                    
          $tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["retur_penjualan_tgl"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["retur_penjualan_nomor"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
          
          
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["cust_usr_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["retur_penjualan_total"]); 
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;".$dataTable[$i]["jenis_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;         
     }
     $colspan = count($tbHeader[0]);
 
     /*if($isAllowedDel) {
          $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnDelete" value="Hapus" class="button">&nbsp;';
     }
     if($isAllowedCreate) {
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;&nbsp;<input type="button" name="btnAdd" value="Tambah Baru" class="button" onClick="document.location.href=\''.$editPage.'tambah=1\'">&nbsp;';
     }*/
     
     $tbBottom[0][0][TABLE_WIDTH] = "100%";
     $tbBottom[0][0][TABLE_COLSPAN] = $colspan;
?>
<?php //echo $view->RenderBody("ipad_depans.css",true,"CETAK ULANG PENJUALAN"); ?>
<script language="JavaScript">

var _wnd_new;
function BukaWindow(url,judul)
{
    if(!_wnd_new) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
	} else {
		if (_wnd_new.closed) {
			_wnd_new = window.open(url,judul,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=850,height=500,left=100,top=100');
		} else {
			_wnd_new.focus();
		}
	}
     return false;
}
function ProsesCetak(id) {
 
  BukaWindow('cetak_ulang_retur.php?id='+id+'','Nota');
	//document.location.href='<?php echo $thisPage;?>';
}
</script>
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
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Cetak Ulang Retur Penjualan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Retur</label>
               <div class='input-group date' id='datepicker'>
              <input name="tanggal_awal" type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>
               &nbsp;sampai dengan&nbsp;
               <div class='input-group date' id='datepicker2'>
              <input  name="tanggal_akhir"  type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>
               &nbsp;(dd-mm-yyy)&nbsp;
          </div>
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
            <?php echo $view->RenderTextBox("kode","kode",30,200,$_POST["kode"],false,false);?>
             
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
            <?php echo $view->RenderTextBox("nama","nama",30,200,$_POST["nama"],false,false);?>
            
            </div>
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
              </div>
          <div class="clearfix"></div>
          
</form>
                  </div>
                </div>
              </div>
            </div>
      <!-- //row filter -->


              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">

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
                            <td class=" " align="<?php echo $tbContent[$i][$k][TABLE_ALIGN]?>"><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
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