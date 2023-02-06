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
     $thisPage = "rekap_10_bsr_diagnosa.php";

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //pemanggilan tanggal hari ini 
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     
     if($_POST["tgl_awal"]) $sql_where[] = "tindakan_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "tindakan_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     // if($_POST["_tipe"]) $sql_where[] = "h.poli_tipe = ".QuoteValue(DPE_CHAR,$_POST["_tipe"]);
     // if($_POST["jenis_pasien"] && $_POST["jenis_pasien"] != '--') $sql_where[] = "a.reg_jenis_pasien = ".QuoteValue(DPE_CHAR,$_POST["jenis_pasien"]);
     
     
     $jmlHari = HitungHari(date_db($_POST["tgl_awal"]),date_db($_POST["tgl_akhir"]));
     if(!$_POST["baris"]) $_POST["baris"]='10';
     $sql_where = implode(" and ", $sql_where);

     if($_POST["btnLanjut"] || $_POST["btnExcel"]){

 
     $sql = "select fol_nama,count(fol_nama) as jumlah from klinik.klinik_folio 
    "; 
     
      $sql .= " where is_operasi ='y' and (fol_nama<>'Biaya Administrasi Operasi' and fol_nama <>'Sewa Kamar Operasi'
    and fol_nama <> 'Kru OK') and " . $sql_where;

    $sql.="group by fol_nama order by jumlah desc";
                         
      
       $dataPasienJml = $dtaccess->FetchAll($sql);
    //    echo $sqljml;
       //var_dump($datawaktuTunggu);
     
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
    
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tindakan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbContent[$i][$counter][TABLE_ALIGN] = "center";   
    
     $counterHeader++;

          
 
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Jumlah";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "35%";
   
     $counterHeader++;


 
     //for($i=0,$counter=0,$n=count($dataPasienJml);$i<$n;$i++,$counter=0){
     //$_POST["baris"] = 100;
     for($i=0,$counter=0,$n=$_POST["baris"] ;$i<$n;$i++,$counter=0){
         
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1).".";
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";            
          $counter++; 

                 
          $tbContent[$i][$counter][TABLE_ISI] =$dataPasienJml[$i]["fol_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++; 
          
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataPasienJml[$i]["jumlah"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++; 

       
       
     }
     
  //    $colspan = count($tbHeader[0]);
   
   
  //  $counter = 0;
                     
  // $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  // $tbBottom[0][$counter][TABLE_ISI] ="Jumlah";
  // $tbBottom[0][$counter][TABLE_COLSPAN] = 2;
  // $tbBottom[0][$counter][TABLE_ALIGN] = "center";
	// $counter++;

	// //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
  // $tbBottom[0][$counter][TABLE_ALIGN] = "right";


                   
  //    $tbBottom[0][$counter][TABLE_ISI] =$totalLk;
  //    $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
   
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
  //       $counter++;
   
  //       //$tbBottom[0][$counter][TABLE_ISI] = currency_format($totalIRJ);
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "right";

                      
	// $tbBottom[0][$counter][TABLE_WIDTH] = "30%";
  //    $tbBottom[0][$counter][TABLE_ISI] =$totalPr;
  //    $tbBottom[0][$counter][TABLE_ALIGN] = "center";
  //       $counter++;
   
      
   }
   
   

  
          
    //echo $sql;
    $sql = "select dep_nama from global.global_departemen where
        dep_id = '".$_GET["klinik"]."'";
    $rs = $dtaccess->Execute($sql);
    $namaKlinik = $dtaccess->Fetch($rs);

    $sql = "SELECT * from global.global_jenis_pasien";
    $jenisPaien = $dtaccess->FetchAll($sql);
                                                      
      //Nama Sekolah
     $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];
      
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     $sql = "select * from global.global_departemen where dep_id like '%".$depId."%' order by dep_id";
     $rs = $dtaccess->Execute($sql);
     $dataKlinik = $dtaccess->FetchAll($rs);
     
      if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
      if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
      $fotoName = $ROOT."adm/gambar/img_cfg/".$konfigurasi["dep_logo"];   
    
    	if($_POST["btnExcel"]){
        
        $_x_mode = "excel";
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

     $tableHeader = "Rekap 10 Besar Tindakan";
     
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
                    <h2>Rekap 10 Besar Tindakan</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
				  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >

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

</script>

<link rel="stylesheet" type="text/css" href="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $APLICATION_ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

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
				  
       <!--    <div class="form-group">
            <label class="control-label col-md-4 col-sm-4 col-xs-12">Tipe Rawat</label>
            <div class="col-md-5 col-sm-5 col-xs-12">
             <select class="select2_single form-control" name="_tipe" id="_tipe"  onKeyDown="return tabOnEnter(this, event);" >                                    
                 <option value="J" <?php if($_POST["_tipe"]=='J')echo "selected" ;?> >Rawat Jalan</option>
                 <option value="G" <?php if($_POST["_tipe"]=='G')echo "selected" ;?> >Rawat Darurat</option>
                 <option value="I" <?php if($_POST["_tipe"]=='I')echo "selected" ;?> >Rawat Inap</option>                                  
            </select>
            </div>
    			</div>  

          <div class="form-group">
            <label class="control-label col-md-4 col-sm-4 col-xs-12">Jenis Pasien</label>
            <div class="col-md-5 col-sm-5 col-xs-12">
             <select class="select2_single form-control" name="jenis_pasien" id="_tipe"  onKeyDown="return tabOnEnter(this, event);" >                                    
                 <option value="--" <?php if($_POST["jenis_pasien"]=='--')echo "selected" ;?> >-- Jenis Pasien --</option>
                 <?php
                  for($i = 0; $i < count($jenisPaien); $i++){
                    ?>
                    <option value="<?=$jenisPaien[$i]['jenis_id']?>" <?php if($_POST["jenis_pasien"]==$jenisPaien[$i]['jenis_id'])echo "selected" ;?> ><?=$jenisPaien[$i]['jenis_nama']?></option>
                    <?php
                  }
                 ?>
            </select>
            </div>
          </div>  
 -->
          <div class="form-group">
            <label class="control-label col-md-4 col-sm-4 col-xs-12">Jumlah Data</label>
            <div class="col-md-5 col-sm-5 col-xs-12">
             <input type="text" name="baris" id="baris" class="form-control"  value="<? echo $_POST["baris"];?>">
            </div>
          </div>  
				  					
					<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>						
						<input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-primary">
               			<!-- <input type="submit" name="btnExcel" value="Export Excel" class="pull-right btn btn-success"> 
               			<input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="pull-right btn btn-primary"> -->
                     <!-- <input type="submit" name="btnExcel" id="btnExcel" value="Excel" class="pull-right btn btn-success"> -->
				    </div>
					<div class="clearfix"></div>
										
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
					           <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
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

<script>


<?php if($_x_mode=="cetak"){ ?>	
  window.open('rekap_10_bsr_tindakan_cetak.php?id_kat_rl_4=<?php echo $_POST["id_kat_rl_4"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_jenis=<?php echo $_POST["id_jenis"];?>&id_poli=<?php echo $_POST["id_poli"];?>&tipe=<?php echo $_POST["_tipe"];?>&baris=<?php echo $_POST["baris"];?>&cetak=y', '_blank');
<?php } ?>

<?php if($_x_mode=="excel"){ ?>	
  window.open('rekap_10_bsr_tindakan_cetak.php?id_kat_rl_4=<?php echo $_POST["id_kat_rl_4"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_jenis=<?php echo $_POST["id_jenis"];?>&id_poli=<?php echo $_POST["id_poli"];?>&tipe=<?php echo $_POST["_tipe"];?>&baris=<?php echo $_POST["baris"];?>&excel=y', '_blank');
<?php } ?>


</script>

