<?php

     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
	 
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     
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

     if($_POST["cust_usr_kode"])  $sql_where[] = "c.cust_usr_kode like".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     if($_POST["cust_usr_nama"])  $sql_where[] = "c.cust_usr_nama like".strtoupper(QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_nama"]."%"));
     if($_POST["kwitansi_nomor"])  $sql_where[] = "i.pembayaran_det_kwitansi like".QuoteValue(DPE_CHAR,"%".$_POST["kwitansi_nomor"]."%");
     
     $sql_where[] = "DATE(pembayaran_det_tgl) = ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));

     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);

     if($_POST["btnLanjut"]){ 
          $sql = "select a.reg_id, b.pembayaran_tanggal, b.pembayaran_create, b.pembayaran_jenis, b.pembayaran_id,
                  c.cust_usr_nama, c.cust_usr_kode,e.usr_name, i.*,j.*,
                  d.poli_nama,f.jenis_nama,g.tipe_biaya_nama from klinik.klinik_pembayaran_det i
                  join klinik.klinik_pembayaran b on i.id_pembayaran = b.pembayaran_id left join
                  klinik.klinik_registrasi a on a.reg_id=b.id_reg left join 
                  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id left join 
                  global.global_auth_poli d on d.poli_id = a.id_poli
                  left join global.global_auth_user e on e.usr_id = a.id_dokter
                  left join global.global_jenis_pasien f on a.reg_jenis_pasien = f.jenis_id
                  left join global.global_tipe_biaya g on a.reg_tipe_layanan = g.tipe_biaya_id
                  left join global.global_jenis_bayar j on i.id_jbayar = j.jbayar_id 
                  where a.id_dep =".QuoteValue(DPE_CHAR,$depId)."
                  and pembayaran_det_flag='T' and c.cust_usr_kode <> '100' and i.is_tutup<>'y' and ".$sql_where." order by pembayaran_det_kwitansi asc";
          //echo $sql;
          $dataTable = $dtaccess->FetchAll($sql);
      }

	        $tableHeader = "&nbsp;GANTI CARA BAYAR";
          $counterHeader = 0;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Proses";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;
           
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No. Kwitansi";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Pasien";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "30%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Waktu";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
                    
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Klinik";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Layanan";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Bayar";           
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

      for($i=0,$nomor=1,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {

          $sql = "select reg_keterangan,fol_keterangan from klinik.klinik_registrasi a
                  left join klinik.klinik_pembayaran_det b on a.id_pembayaran = b.id_pembayaran
                  left join klinik.klinik_folio c on a.reg_id = c.id_reg and c.id_pembayaran_det = b.pembayaran_det_id              
                  where b.pembayaran_det_id =".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_det_id"]);
          $rs = $dtaccess->Execute($sql);
          $keterangan = $dtaccess->Fetch($rs); 
          
  		    $editPage = "ganti_cara_bayar_proses.php?id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"]."&id_pembayaran_det=".$dataTable[$i]["pembayaran_det_id"];
                    
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Proses" title="Proses" border="0"/></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_kode"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["pembayaran_det_kwitansi"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			if($dataTable[$i]["cust_usr_kode"]=='100' || $dataTable[$i]["cust_usr_kode"]=='500'){
          if($keterangan["fol_keterangan"]=='' || $keterangan["fol_keterangan"]==null){
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$keterangan["reg_keterangan"];
          }else{
          $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$keterangan["fol_keterangan"];
          }
          }else{
    			$tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;&nbsp;".$dataTable[$i]["cust_usr_nama"];
          }
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			
    			$tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($dataTable[$i]["pembayaran_det_create"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
    			  			
    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["tipe_biaya_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jbayar_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "left";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["pembayaran_det_total"]);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

      }
      
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);           
?>
<?php //echo $view->RenderBody("module.css",true,true,"GANTI CARA BAYAR"); ?>
<?php // echo $view->InitUpload(); ?>
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


function hapusReg() {
  if(confirm('apakah anda yakin akan menghapus data Transaksi ini???'));
  else return false;
}
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
                    <h2>Ganti Cara Bayar</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
              <input name="tanggal_awal" type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  />
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>
          </div>
    	<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
					<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
				</div>
			<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">NO RM</label>
					<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
				</div>
			<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No Kwitansi</label>
					<?php echo $view->RenderTextBox("kwitansi_nomor","kwitansi_nomor",30,200,$_POST["kwitansi_nomor"],false,false);?>
				</div>   
	    	<script>document.frmFind.cust_usr_kode.focus();</script>

    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary">
			  <!--  <td align="right" class="tablecontent-odd"><input type="submit" id="btnView" name="btnView" value="Entry Tindakan" class="submit"></td> -->

     </div>
</form>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
</form>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
</div>
<?php //echo $view->RenderBodyEnd(); ?>
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