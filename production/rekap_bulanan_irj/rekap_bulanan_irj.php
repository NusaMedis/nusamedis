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
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   $userData = $auth->GetUserData();
	   $userId = $auth->GetUserId();
     $thisPage = "rekap_bulanan_irj.php";

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //pemanggilan tanggal hari ini 
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     
     if($_POST["tgl_awal"]) $sql_where[] = "c.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "c.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     $jmlHari = HitungHari(date_db($_POST["tgl_awal"]),date_db($_POST["tgl_akhir"]));
     
  if($_POST["btnLanjut"] || $_POST["btnExcel"]){  
     //untuk mencari tanggal
     if($_POST["id_poli"]){
     $sql = "select * from global.global_auth_poli where poli_id=".QuoteValue(DPE_CHAR,$_POST["id_poli"]);
     } else {
     $sql = "select * from global.global_auth_poli where poli_tipe='J' order by poli_id asc";
     }
     $rs = $dtaccess->Execute($sql);
     $dataPoli = $dtaccess->FetchAll($rs);
     
     if($_POST["id_jenis"]){
     $sql = "select * from global.global_jenis_pasien where jenis_id='".$_POST["id_jenis"]."'";
     } else {
     $sql = "select * from global.global_jenis_pasien where jenis_flag='y' order by jenis_id asc";
     }
     $rs = $dtaccess->Execute($sql);
     $dataJenisPasien = $dtaccess->FetchAll($rs);
     
     $sql = "select count(reg_id) as total, a.reg_tanggal, a.reg_jenis_pasien, a.id_poli from klinik.klinik_registrasi a
            where reg_status_pasien = 'B' group by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli
            order by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli";
     $rs = $dtaccess->Execute($sql); 
  	 while($row = $dtaccess->Fetch($rs)) {
  		$dataBaru[$row["reg_tanggal"]][$row["reg_jenis_pasien"]][$row["id_poli"]] = $row["total"];		  
       }
       
     $sql = "select count(reg_id) as total, a.reg_tanggal, a.reg_jenis_pasien, a.id_poli from klinik.klinik_registrasi a
            where reg_status_pasien = 'L' group by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli
            order by a.reg_tanggal, a.reg_jenis_pasien, a.id_poli";
     $rs = $dtaccess->Execute($sql); 
  	 while($row2 = $dtaccess->Fetch($rs)) {
  		$dataLama[$row2["reg_tanggal"]][$row2["reg_jenis_pasien"]][$row2["id_poli"]] = $row2["total"];		  
       }
  
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
     $counterHeader3 = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tgl";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "3";
     $counterHeader++;
     
 
     for($a=0,$b=count($dataPoli);$a<$b;$a++){

        $tbHeader[0][$counterHeader][TABLE_ISI] = $dataPoli[$a]["poli_nama"];  
        $tbHeader[0][$counterHeader][TABLE_COLSPAN] = count($dataJenisPasien)*2;     
        $counterHeader++;  
        
      for($p=0,$q=count($dataJenisPasien);$p<$q;$p++){
    		$tbHeader[1][$counterHeader2][TABLE_ISI] = $dataJenisPasien[$p]["jenis_nama"];
        $tbHeader[1][$counterHeader2][TABLE_COLSPAN] = "2";          
        $counterHeader2++;		        
        
        $tbHeader[2][$counterHeader3][TABLE_ISI] = "Baru";
        $counterHeader3++;
        
        $tbHeader[2][$counterHeader3][TABLE_ISI] = "Lama";
        $counterHeader3++;
        
      }    
     }
     
     $tgl = date_db($_POST["tgl_awal"]);
 
     for($i=0,$counter=0,$n=$jmlHari;$i<$n;$i++,$counter=0){
       
          $tglTable = explode("-",$tgl);
          $tbContent[$i][$counter][TABLE_ISI] = $tglTable[2];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";            
          $counter++;                                         
       
       for($j=0,$m=count($dataPoli);$j<$m;$j++){   
        for($k=0,$o=count($dataJenisPasien);$k<$o;$k++){
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataBaru[$tgl][$dataJenisPasien[$k]["jenis_id"]][$dataPoli[$j]["poli_id"]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";          
          $counter++;
          //print_r($dataBaru);
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataLama[$tgl][$dataJenisPasien[$k]["jenis_id"]][$dataPoli[$j]["poli_id"]]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";          
          $counter++;
          
        }
       }
       $tgl = DateAdd($tgl,1);
       //print_r($tgl);   
     }
     
     $colspan = count($tbHeader[0]);
   }

     
       //ambil nama poli
  $sql = "select b.poli_nama, b.poli_id from   global.global_auth_poli b where id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"])." and poli_tipe='J'"   ; 
  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
  $poli = $dtaccess->FetchAll($rs_edit);
  
     // ambil jenis pasien
     $sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'";
     $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
     $jenisPasien = $dtaccess->FetchAll($rs); 
          
    //echo $sql;
          $sql = "select dep_nama from global.global_departemen where
              dep_id = '".$_GET["klinik"]."'";
          $rs = $dtaccess->Execute($sql);
          $namaKlinik = $dtaccess->Fetch($rs);
                                                      
      //Nama Sekolah
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     $sql = "select * from global.global_departemen where dep_id like '%".$depId."%' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
     //ambil jenis pasien
     $sql = "select * from global.global_auth_user where (id_rol = '2' or id_rol = '5') and id_dep like ".QuoteValue(DPE_CHAR,"%".$_POST["klinik"])." order by usr_id asc ";
     $rs = $dtaccess->Execute($sql);
     $dataDokter = $dtaccess->FetchAll($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=Lap_Statistik_Irj.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

     $tableHeader = "IRJ | Rekap Bulanan";
     
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
			<div class="clearfix"></div>
			<!-- row filter -->
			<div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Rekap Bulanan IRJ</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
<?php if(!$_POST["btnExcel"]) { ?>

<script language="JavaScript">
function CheckSimpan(frm) { 
     if(!frm.tgl_awal.value) {
          alert("Tanggal Harus Diisi");
          return false;
     }

     if(!CheckDate(frm.tgl_awal.value)) {
          return false;
     }
}

<?php if($_x_mode=="cetak"){ ?>	
  window.open('rekap_bulanan_irj_cetak.php?id_kat_rl_4=<?php echo $_POST["id_kat_rl_4"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_jenis=<?php echo $_POST["id_jenis"];?>&id_poli=<?php echo $_POST["id_poli"];?>', '_blank');
<?php } ?>

</script>

<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<?php } ?>				  
<?php if(!$_POST["btnExcel"]) { ?>
<!-- <script type="text/javascript">
$(document).ready(function() {
    $("a[rel=sepur]").fancybox({
'width' : '50%',
'height' : '100%',
'autoScale' : false,
'transitionIn' : 'none',
'transitionOut' : 'none',
'type' : 'iframe'      
});
}); 
</script> -->

<script type="text/javascript" src="<?php echo $APLICATION_ROOT;?>lib/script/scroll_ipad2.js"></script>
<style type="text/css">
#top{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0068c9), to(#007bed));
background: -moz-linear-gradient(top, #0068c9, #007bed); 
}
#footer{
background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#007bed), to(#0068c9));
background: -moz-linear-gradient(top, #007bed, #0068c9);
}
</style>			
			<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
							<input name="tgl_awal" type='text' class="form-control" 
							value="<?php if ($_POST['tgl_awal']) { echo $_POST['tgl_awal']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	           			 
			
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
						<div class='input-group date' id='datepicker2'>
							<input  name="tgl_akhir"  type='text' class="form-control" 
							value="<?php if ($_POST['tgl_akhir']) { echo $_POST['tgl_akhir']; } else { echo date('d-m-Y'); } ?>"  />
							<span class="input-group-addon">
								<span class="fa fa-calendar">
								</span>
							</span>
						</div>	     			 
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
						<?php if($userData["rol"]!='2') { ?>       	      
              				<td width="20%" class="tablecontent">
      							<?php } else { ?>
              				<td width="20%" class="tablecontent">
      							<?php } ?>
               				<select class="select2_single form-control" name="cust_usr_jenis" id="cust_usr_jenis" onKeyDown="return tabOnEnter(this, event);"> <!--onChange="this.form.submit();" -->
                				<option value="0" >[ Pilih Cara Bayar ]</option>
                					<?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                				<option value="<?php echo $jenisPasien[$i]["jenis_id"];?>" <?php if($jenisPasien[$i]["jenis_id"]==$_POST["cust_usr_jenis"]) echo "selected"; ?> onClick="javascript:return TampilCombo('<?php echo $jenisPasien[$i]["jenis_id"];?>');"><?php echo ($i+1).". ".$jenisPasien[$i]["jenis_nama"];?></option>
      								<?php } ?>
      						</select>
						
				    </div>
				    
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Klinik</label>
						<?php if($userData["rol"]!='2') { ?>       	      
							<td width="20%" class="tablecontent">
								<?php } else { ?>
							<td width="20%" class="tablecontent">
								<?php } ?>
							<select class="select2_single form-control" name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this, event);">
								<option value="">[Pilih Klinik]</option>
								<?php for($i=0,$n=count($dataPoli);$i<$n;$i++){ ?>
								<option value="<?php echo $dataPoli[$i]["poli_id"];?>" <?php if($dataPoli[$i]["poli_id"]==$_POST["id_poli"]) echo "selected"; ?>><?php echo $dataPoli[$i]["poli_nama"];?></option>
								<?php } ?>
							</select>
						
				    </div>
				  					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
               			<!--<input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success">-->
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary">
				    </div>
					<div class="clearfix"></div>
					<? if($_POST['btnLanjut'] || $_GET['edt'] || $_GET['tambah'] || $_GET['Kembali'] || $_GET["id_tahun_tarif"]){?>
					<?}?>
					<? if ($_x_mode == "Edit"){ ?>
					<?php echo $view->RenderHidden("kategori_tindakan_id","kategori_tindakan_id",$biayaId);?>
					<? } ?>
					
					<!-- <script type="text/javascript">
    				Calendar.setup({
       			 	inputField     :    "tanggal_awal",      // id of the input field
        			ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        			showsTime      :    false,            // will display a time selector
        			button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        			singleClick    :    true,           // double-click mode
        			step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    				});
    
    				Calendar.setup({
        			inputField     :    "tanggal_akhir",      // id of the input field
        			ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        			showsTime      :    false,            // will display a time selector
        			button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        			singleClick    :    true,           // double-click mode
        			step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    				});
					</script> -->
					</form>
					<?php } ?>
                  </div>
                </div>
              </div>
            </div>
			<!-- //row filter -->
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
					   <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                      <?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
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

</script>
<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="center" colspan="51">
               <strong>Lap. Statistik IRJ<br />
               <?php //echo $konfigurasi["dep_nama"]?>&nbsp;&nbsp;<?php //echo $konfigurasi["dep_kop_surat_1"]?>&nbsp;&nbsp;<?php //echo $konfigurasi["dep_kop_surat_2"]?>
                          <?php if($_POST["tgl_awal"]==$_POST["tgl_akhir"]) { echo "Tanggal : ".$_POST["tgl_awal"]; } elseif($_POST["tgl_awal"]!=$_POST["tgl_akhir"]) { echo "Periode : ".$_POST["tgl_awal"]." - ".$_POST["tgl_akhir"]; }  ?>
               <br /><br />
               </strong>
               </td>          
          </tr>
         <tr class="tableheader">
          <td align="left" colspan="10">
 <br><br> 
 <b>Nama Rumah Sakit : <?php echo $depNama;?></b>               
          <br /><br />
          </td>
          </tr>
     </table>
<?php }?>
<?php if(!$_POST["btnExcel"]) { ?>

<br />
<?php } ?>

