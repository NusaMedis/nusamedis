<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");    
     require_once($LIB."currency.php");
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $depNama = $auth->GetDepNama(); 
     
 	  // if(!$auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_CREATE)){
    //       die("access_denied");
    //       exit(1);
    //  } else if($auth->IsAllowed("kassa_transaksi_deposit_masuk",PRIV_CREATE)===1){
    //       echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
    //       exit(1);
    //  } 


     $_x_mode = "New";
     $thisPage = "deposit_masuk_view.php";
     
     // KONFIGURASI
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     $_POST["dep_bayar_reg"] = $konfigurasi["dep_bayar_reg"];
     $_POST["dep_kasir_tindakan"] = $konfigurasi["dep_kasir_tindakan"];

      $table = new InoTable("table","100%","left");
       $skr = date("d-m-Y");
       
        if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
        if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
        if($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
        if($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];

     if($_POST["cust_usr_kode"])  {$sql_where[] = "cust_usr_kode like".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_kode"]."%");}
     if($_POST["cust_usr_nama"])  {$sql_where[] = "upper(cust_usr_nama) like".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_nama"])."%"); }
     if($_POST["cust_usr_alamat"])  {$sql_where[] = "upper(cust_usr_alamat) like".QuoteValue(DPE_CHAR,"%".strtoupper($_POST["cust_usr_alamat"])."%"); }
     if($_POST["tanggal_awal"]) {$sql_where[] = "date(deposit_history_tgl)>=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));}
     if($_POST["tanggal_akhir"]) {$sql_where[] = "date(deposit_history_tgl)<=".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));}
     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     
     //if($_POST["btnLanjut"])   
     //{   
        $sql = "select * from klinik.klinik_deposit_history a
                left join klinik.klinik_deposit b on b.id_cust_usr=a.id_cust_usr
                left join global.global_customer_user c on c.cust_usr_id=a.id_cust_usr
                left join gl.gl_buffer_transaksi d on d.id_pembayaran_det = a.id_multipayment
                where a.deposit_history_nominal>0 and ".$sql_where;
        $rs = $dtaccess->Execute($sql);
        $dataTable = $dtaccess->FetchAll($rs);    
        // echo $sql;
     //}      
		     
          $tableHeader = "&nbsp;Deposit Masuk";
          $counterHeader = 0;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++; 

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++; 
          
		      $tbHeader[0][$counterHeader][TABLE_ISI] = "Cetak Ulang";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++; 

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Medrec";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Alamat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nominal";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
      for($i=0,$nomor=1,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
          
			    $editPage = "deposit_masuk_edit.php?id=".$dataTable[$i]["deposit_history_id"]."&id_deposit=".$dataTable[$i]["deposit_id"]."&id_cust_usr=".$dataTable[$i]["cust_usr_id"]."&id_multipayment=".$dataTable[$i]["id_multipayment"];
          $delPage = "deposit_masuk_edit.php?del=1&id=".$dataTable[$i]["deposit_history_id"]."&id_deposit=".$dataTable[$i]["deposit_id"]."&id_cust_usr=".$dataTable[$i]["cust_usr_id"]."&id_multipayment=".$dataTable[$i]["id_multipayment"];
			    $cetakPage = "deposit_masuk_cetak.php?id=".$dataTable[$i]["deposit_history_id"]."&id_deposit=".$dataTable[$i]["deposit_id"]."&id_cust_usr=".$dataTable[$i]["cust_usr_id"]."&id_multipayment=".$dataTable[$i]["id_multipayment"];

          if ($dataTable[$i]['is_posting'] == 'n') {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$delPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Edit" title="Edit" border="0"/></a>';
          }else{
            $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;

          if(date("Y-m-d")==$dataTable[$i]["deposit_history_tgl"]){
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"/></a>';
          } else {
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;";
          }               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$cetakPage.'" target="_blank"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cetak.png" alt="Cetak" title="Cetak" border="0"/></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_kode"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["cust_usr_alamat"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = format_date($dataTable[$i]["deposit_history_tgl"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["deposit_history_nominal"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
      } 
      
      if($_POST["btnTambah"]){
        header("location: deposit_masuk_edit.php");
        exit();
      }
      
          //-----konfigurasi-rue----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
	
?>


<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '60%',
'height' : '110%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 

</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>
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
                    <h2>Deposit Masuk</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
              <input name="tanggal_awal" type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
            <div class='input-group date' id='datepicker2'>
              <input  name="tanggal_akhir"  type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>             
            </div>
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">No RM</label>            
                <input  name="cust_usr_kode"  id="cust_usr_kode" type='text' class="form-control" value="<? echo $_POST['cust_usr_kode']; ?>"  />          
            </div>
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>            
                <input  name="cust_usr_nama"  id="cust_usr_nama" type='text' class="form-control" value="<? echo $_POST['cust_usr_nama']; ?>"  />          
            </div>
            
            <div class="col-md-4 col-sm-6 col-xs-12">
                <label class="control-label col-md-12 col-sm-12 col-xs-12">Alamat Pasien</label>            
                <input  name="cust_usr_alamat"  id="cust_usr_alamat" type='text' class="form-control" value="<? echo $_POST['cust_usr_alamat']; ?>"  />          
            </div>
	    	<script>document.frmFind.cust_usr_kode.focus();</script>

          <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>           
            <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
                    <!-- <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success"> -->
                    <input type="submit" name="btnTambah" id="btnTambah" value="Tambah" class="pull-right btn btn-primary">
            </div>
</form>
<form name="frmView" method="POST" action="<?php echo $editPage; ?>">

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    

<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
</form>

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

