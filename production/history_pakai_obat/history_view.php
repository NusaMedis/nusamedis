<?php
// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."tampilan.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");

     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "report_pasien.php";
     $poliId = $auth->IdPoli();
$skr      = date("d-m-Y");
	 
	if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 

$tgl_awal = ($_POST['tgl_awal']) ? $_POST['tgl_awal'] : $skr;
$tgl_akhir = ($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : $skr;
	

if ($_POST["tgl_awal"]) $sql_where[] = "DATE(b.penjualan_create) >= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_awal"]));
if ($_POST["tgl_akhir"]) $sql_where[] = "DATE(b.penjualan_create) <= " . QuoteValue(DPE_DATE, date_db($_POST["tgl_akhir"]));
	if($_POST["cust_usr_nama"]){
		$sql_where[] = " upper(b.cust_usr_nama) like '%".strtoupper($_POST["cust_usr_nama"])."%' ";
	 }
	 
	 if($_POST["cust_usr_kode"]){
		$sql_where[] = " d.cust_usr_kode like '%".$_POST["cust_usr_kode"]."%'";
	 }

   if($_POST["item_nama"]){
    $sql_where[] = " e.item_nama like '%".strtoupper($_POST["item_nama"])."%'";
   }

   if($_POST["id_dokter"]){
    $sql_where[] = " c.id_dokter = ".QuoteValue(DPE_CHAR,$_POST["id_dokter"]);
   }

   if($_POST["id_petugas"]){
    $sql_where[] = " b.who_update = ".QuoteValue(DPE_CHAR,$_POST["id_petugas"]);
   }

	if($_POST["btnLanjut"] || $_POST["btnCetak"] || $_POST["btnExcel"]){
    $sql = " select a.id_penjualan,a.id_item,a.penjualan_detail_jumlah,b.*,d.cust_usr_kode,e.item_nama,e.item_racikan, d.cust_usr_alamat, f.usr_name from apotik.apotik_penjualan_detail a 
            left join apotik.apotik_penjualan b on a.id_penjualan =b.penjualan_id
            left join klinik.klinik_registrasi c on b.id_reg = c.reg_id
            left join global.global_customer_user d on c.id_cust_usr = d.cust_usr_id
            left join logistik.logistik_item e on a.id_item =e.item_id
            left join global.global_auth_user f on b.who_update = f.usr_id";
    $sql.= " where ".implode(" and ",$sql_where);
    //$sql.= " and b.id_dokter =".QuoteValue(DPE_CHAR,$_POST["nama_dokter"]);
    $sql.= " order by c.reg_tanggal, c.reg_waktu,b.penjualan_create,b.penjualan_id asc";
    $rs = $dtaccess->Execute($sql,DB_SCHEMA);
    $dataTable = $dtaccess->FetchAll($rs);
     	//echo $sql;
	}

	 for($i=0,$n=count($dataTable);$i<$n;$i++) {
          if($dataTable[$i]["id_penjualan"]==$dataTable[$i-1]["id_penjualan"] ){
          $hitung[$dataTable[$i]["id_penjualan"]] += 1;
          }      
      }   

	$tableHeader = "History Pemakaian Obat";
// --- construct new table ---- //
    
     $m = 1 ;
     
     
     $colspan = count($tbHeader[0]);

     if($_POST["btnCetak"]){
    //echo $_POST["ush_id"];
    //die();
    $_x_mode = "cetak" ;      
  }

  if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=history_obat.xls');
      }

  $sql = "select * from global.global_auth_user where id_rol = 2 order by usr_name asc";
  $rs = $dtaccess->Execute($sql);
  $dataDokter = $dtaccess->FetchAll($rs);

  $sql = "select * from global.global_auth_user where id_rol = 10 order by usr_name asc";
  $rs = $dtaccess->Execute($sql);
  $dataPetugas = $dtaccess->FetchAll($rs);
?>
<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
<script type="text/javascript">
	// function CheckFilter(frm) {
	// 	if(!frm.cust_usr_nama.value && !frm.cust_usr_kode.value && !frm.item_nama.value && !frm.id_dokter.value){
	// 	alert('Salah Satu Filter Wajib Diisi !!');
 //          return false;
	// }

 //     	return true;  
	// }
  <?php if($_x_mode=="cetak"){ ?> 

  window.open('history_cetak.php?tgl_awal=<?php echo $_POST['tgl_awal'] ?>&tgl_akhir=<?php echo $_POST['tgl_akhir'] ?>&kode=<?php echo $_POST["cust_usr_kode"];?>&nama=<?php echo $_POST["cust_usr_nama"];?>&item_nama=<?php echo $_POST["item_nama"];?>&id_dokter=<?php echo $_POST["id_dokter"];?>', '_blank');
 <?php } ?>
</script>
<? if(!$_POST["btnExcel"]) { ?>
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
                    <h2><?= $tableHeader ?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" ><!-- FILTER KIRI -->
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <!-- Filter Tanggal Awal -->
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
                          <input name="tgl_awal" type='text' class="form-control" value="<?= $tgl_awal ?>">
                          <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                        <!-- Filter Tanggal Awal -->
                        <!-- Filter Tanggal Akhir -->
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker2'>
                          <input name="tgl_akhir" type='text' class="form-control" value="<?= $tgl_akhir ?>">
                          <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                        <!-- Filter Tanggal Akhir -->
                      </div>
				 <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Pasien</label>
						<?php echo $view->RenderTextBox("cust_usr_nama","cust_usr_nama",30,200,$_POST["cust_usr_nama"],false,false);?>
					 
				    </div>
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">No. RM</label>
						<?php echo $view->RenderTextBox("cust_usr_kode","cust_usr_kode",30,200,$_POST["cust_usr_kode"],false,false);?>
						
				    </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Obat</label>
            <?php echo $view->RenderTextBox("item_nama","item_nama",30,200,$_POST["item_nama"],false,false);?>
            
            </div>
          </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Dokter</label>
              <div>
              <select class="select2_single form-control" name="id_dokter" id="id_dokter" onKeyDown="return tabOnEnter(this, event);">
                <option value="">[Pilih Dokter]</option> 
                <?php for($i=0,$n=count($dataDokter);$i<$n;$i++){ ?>
                <option value="<?php echo $dataDokter[$i]["usr_id"];?>" <?php if($dataDokter[$i]["usr_id"]==$_POST["id_dokter"]) echo "selected"; ?>><?php echo $dataDokter[$i]["usr_name"];?></option>
                <?php } ?>
              </select>
            </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Petugas</label>
              <div>
              <select class="select2_single form-control" name="id_petugas" id="id_petugas">
                <option value="">[Pilih Petugas]</option> 
                <?php for($i=0,$n=count($dataPetugas);$i<$n;$i++){ ?>
                <option value="<?php echo $dataPetugas[$i]["usr_id"];?>" <?php if($dataPetugas[$i]["usr_id"]==$_POST["id_petugas"]) echo "selected"; ?>><?php echo $dataPetugas[$i]["usr_name"];?></option>
                <?php } ?>
              </select>
            </div>
            </div>
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary" onClick="javascript:return CheckFilter(document.frmView);">
               			<!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
				    </div>
					<div class="clearfix"></div>
				  </form>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->
			 <!-- //row content -->
			<div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">

                  <div class="x_content">
                    <? } ?>
                    <? if($_POST["btnExcel"] || $_POST["btnLanjut"]){ ?>
                  <table id="datatable-responsive" border="1" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                    <!-- <thead> -->
                    	<tr>
                        <th>No</th>
                        <th>No RM</th>
                        <th>Nama Pasien</th>
                        <th>Alamat</th>
                        <th>No Faktur</th>
                        <th>Catatan</th>
                        <th>Tanggal</th>
                        <th>Dokter</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Pelaksana</th>
                      </tr>
                    <!-- </thead> -->
                  <?php for($i=0,$n=count($dataTable);$i<$n;$i++) { ?>
                    <!-- <tbody> -->
            					<tr>
            						<? if($dataTable[$i]["id_penjualan"]!=$dataTable[$i-1]["id_penjualan"] ){
                       			$dataSpan = $hitung[$dataTable[$i]["id_penjualan"]]+1; ?>		
              						<td rowspan="<? echo $dataSpan?>"><? echo $m++;?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_kode"];?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_nama"];?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["cust_usr_alamat"];?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["penjualan_nomor"];?></td>
                          <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["penjualan_catatan"];?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo FormatTimeStamp($dataTable[$i]["penjualan_create"]);?></td>
              						<td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["dokter_nama"];?></td>
              					<? } ?>
            					
            					<? if($dataTable[$i]["item_racikan"]='y') {
              					$sql = "select item_nama from apotik.apotik_detail_racikan where id_nama_racikan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_item"]);
                      	$rs = $dtaccess->Execute($sql);
                      	$dataRacikan = $dtaccess->FetchAll($rs);
            					?>
            						<td><strong><? echo $dataTable[$i]["item_nama"];?></strong><br>
            						<table align="center" border='0'>
            							<? for($a=0,$b=count($dataRacikan);$a<$b;$a++) { ?>
            							<tr><td><? echo $dataRacikan[$a]["item_nama"]?></td></tr>
            						<? } ?>
            						</table></td>
            					 <? } ?>				
            						<td align="right"><? echo $dataTable[$i]["penjualan_detail_jumlah"];?></td>
                        <? if($dataTable[$i]["id_penjualan"]!=$dataTable[$i-1]["id_penjualan"] ){
                            $dataSpan = $hitung[$dataTable[$i]["id_penjualan"]]+1; ?>   
                          <td rowspan="<? echo $dataSpan?>"><? echo $dataTable[$i]["usr_name"];?></td>
                        <? } ?>
            					</tr>
                  <? 
                    $SumQuantity += $dataTable[$i]['penjualan_detail_jumlah'];
                    } 
                  ?>
                      <tr>
                        <td colspan="9" align="right"><b>TOTAL QUANTITY</b></td>
                        <td style="color:red;" align="right"><b><?php echo str_replace(',', '.', currency_format($SumQuantity)) ?></b></td>
                      </tr>
                    <!-- </tbody> -->
                  </table>
                  <? } ?>				
                  </div>
                </div>
              </div>	  	
              <!-- //row content -->
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