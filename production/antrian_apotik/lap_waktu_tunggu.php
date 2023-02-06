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
     $thisPage = "lap_waktu_tunggu.php";

     if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //pemanggilan tanggal hari ini 
     if(!$_POST["tgl_awal"]) $_POST["tgl_awal"] = date("d-m-Y");
     if(!$_POST["tgl_akhir"]) $_POST["tgl_akhir"] = date("d-m-Y");
     
     if($_POST["tgl_awal"]) $sql_where[] = "a.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where[] = "a.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
     
     if($_POST["tgl_awal"]) $sql_where2[] = "b.reg_tanggal >= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_awal"]));
     if($_POST["tgl_akhir"]) $sql_where2[] = "b.reg_tanggal <= ".QuoteValue(DPE_DATE,date_db($_POST["tgl_akhir"]));
//     $sql_where[] = "1=1";
     
     $jmlHari = HitungHari(date_db($_POST["tgl_awal"]),date_db($_POST["tgl_akhir"]));
     
  if($_POST["btnLanjut"] || $_POST["btnExcel"]){  
     //untuk mencari tanggal
     $sql_where = implode(" and ",$sql_where);
     
     $sql = "select a.*,b.cust_usr_nama,b.cust_usr_kode from  klinik.klinik_registrasi a left join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id left join global.global_auth_poli c on a.id_poli =c.poli_id";
     $sql .= " where c.poli_tipe='A' and ".$sql_where; 
     $sql .= "order by a.reg_when_update";
     $rs = $dtaccess->Execute($sql);
     $dataRegistrasi = $dtaccess->FetchAll($rs);

     $sql = "select * from  klinik.klinik_waktu_tunggu_status";
     $sql .= " where waktu_tunggu_tipe_rawat='A' and waktu_tunggu_status_flag='y' order by waktu_tunggu_status_urut"; 
     $rs = $dtaccess->Execute($sql);
     $dataStatus = $dtaccess->FetchAll($rs);
//echo $sql;
     $sql_where2 = implode(" and ",$sql_where2);

     $sql = "select a.id_reg, a.klinik_waktu_tunggu_when_create, klinik_waktu_tunggu_durasi, a.klinik_waktu_tunggu_durasi_detik,a.klinik_waktu_tunggu_status from 
             klinik.klinik_waktu_tunggu a
             left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
             left join klinik.klinik_waktu_tunggu_status c on c.waktu_tunggu_status_id = a.id_waktu_tunggu_status
             left join global.global_auth_poli d on b.id_poli = d.poli_id";
     $sql .= " where  d.poli_tipe ='A' and  c.waktu_tunggu_tipe_rawat='A' and ".$sql_where2;         
     $sql .= " order by b.reg_when_update,c.waktu_tunggu_status_urut";
      
     $rs = $dtaccess->Execute($sql); 
     while($row = $dtaccess->Fetch($rs)) {
      $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_when_create"] = $row["klinik_waktu_tunggu_when_create"];      
      $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi_detik"] = $row["klinik_waktu_tunggu_durasi_detik"];      
      $datawaktuTunggu[$row["id_reg"]][$row["klinik_waktu_tunggu_status"]]["klinik_waktu_tunggu_durasi"] = $row["klinik_waktu_tunggu_durasi"];      

       }
      //var_dump($datawaktuTunggu);
     
     
     // --- construct new table ---- //
     $counterHeader = 0;
     $counterHeader2 = 0;
     $counterHeader3 = 0;
          
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No.";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "No. RM";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     $tbHeader[0][$counterHeader][TABLE_ROWSPAN] = "2";
     $counterHeader++;
     
 
     for($a=0,$b=count($dataStatus);$a<$b;$a++){

        $tbHeader[0][$counterHeader][TABLE_ISI] = $dataStatus[$a]["waktu_tunggu_status_nama"];
        if ($dataStatus[$a]["waktu_tunggu_status_urut"]==2) $jumSpan=1;  
        if ($dataStatus[$a]["waktu_tunggu_status_urut"]==3) $jumSpan=3;
        if ($dataStatus[$a]["waktu_tunggu_status_urut"]>3) $jumSpan=1;
        $tbHeader[0][$counterHeader][TABLE_COLSPAN] = $jumSpan;  
        $counterHeader++;  
 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Waktu";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
         $counterHeader2++;
     
         if ($dataStatus[$a]["waktu_tunggu_status_urut"]==3)
         { 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Durasi";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "10%";
         $counterHeader2++;
         }

         if ($dataStatus[$a]["waktu_tunggu_status_urut"]==3)
         { 
         $tbHeader[1][$counterHeader2][TABLE_ISI] = "Durasi Detik";
         $tbHeader[1][$counterHeader2][TABLE_WIDTH] = "5%";
         $counterHeader2++;
         }
     }
     
 
     $tgl = date_db($_POST["tgl_awal"]);
 
     for($i=0,$counter=0,$n=count($dataRegistrasi);$i<$n;$i++,$counter=0){

          if($dataRegistrasi[$i]["id_cust_usr"]=='100')$dataRegistrasi[$i]["cust_usr_nama"] =$dataRegistrasi[$i]["reg_keterangan"];
       
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1).".";
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";            
          $counter++;                                         

          $tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_kode"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++;                                         
       
          $tbContent[$i][$counter][TABLE_ISI] = $dataRegistrasi[$i]["cust_usr_nama"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";            
          $counter++;                                         

       for($j=0,$m=count($dataStatus);$j<$m;$j++){   
          $tbContent[$i][$counter][TABLE_ISI] = FormatTimestamp($datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_when_create"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;

          if ($dataStatus[$j]["waktu_tunggu_status_urut"]==3)
          { 
          $tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_durasi"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
          $counter++;
          }

          if ($dataStatus[$j]["waktu_tunggu_status_urut"]==3)
          { 
          $tbContent[$i][$counter][TABLE_ISI] = $datawaktuTunggu[$dataRegistrasi[$i]["reg_id"]][$dataStatus[$j]["waktu_tunggu_status_kode"]]["klinik_waktu_tunggu_durasi_detik"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
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
          header('Content-Disposition: attachment; filename=Lap_waktu_tunggu_lab.xls');
      }  
  
      if($_POST["btnCetak"]){
        $_x_mode = "cetak" ;      
     }

     $tableHeader = "Apotek | Laporan Waktu Tunggu";
     
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
                    <h2><? echo $tableheader;?></h2>
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
  window.open('lap_waktu_tunggu_rad_cetak.php?id_kat_rl_4=<?php echo $_POST["id_kat_rl_4"];?>&klinik=<?php echo $_POST["klinik"];?>&tgl_awal=<?php echo $_POST["tgl_awal"];?>&tgl_akhir=<?php echo $_POST["tgl_akhir"];?>&shift=<?php echo $_POST["shift"];?>&dokter=<?php echo $_POST["id_dokter"];?>&id_jenis=<?php echo $_POST["id_jenis"];?>&id_poli=<?php echo $_POST["id_poli"];?>', '_blank');
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

</script>
<?php if($_POST["btnExcel"]) {?>

     <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
               <td align="center" colspan="51">
               <strong>Lap. Waktu Tunggu Radiologi<br />
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

