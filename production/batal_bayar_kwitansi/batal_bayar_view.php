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
 	   /*if(!$auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("kas_pembayaran_pemeriksaan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  */

     $_x_mode = "New";
     $thisPage = "penata_jasa_view.php";
     $editPage = "penata_jasa_proses.php";
     
     //kembali pada Menu View Penata Jasa
   	 if($_POST["btnView"]) 
     {           
     	header("location:penata_jasa_view.php");
     	exit();                                  
     }

     
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

     $sql_where[] = "DATE(pembayaran_det_tgl) >= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_awal"]));
     $sql_where[] = "DATE(pembayaran_det_tgl) <= ".QuoteValue(DPE_DATE,date_db($_POST["tanggal_akhir"]));
     
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
     
     if($_POST["btnLanjut"]){ 
          $sql = "select a.reg_id, a.reg_tipe_rawat,b.pembayaran_tanggal, b.pembayaran_create, b.pembayaran_jenis, b.pembayaran_id,
                  c.cust_usr_nama, c.cust_usr_kode,e.usr_name, i.*,
                  d.poli_nama,f.jenis_nama,g.tipe_biaya_nama 
                  from klinik.klinik_pembayaran_det i
                  join klinik.klinik_pembayaran b on i.id_pembayaran = b.pembayaran_id left join
                  klinik.klinik_registrasi a on a.reg_id=i.id_reg left join 
                  global.global_customer_user c on a.id_cust_usr = c.cust_usr_id left join 
                  global.global_auth_poli d on d.poli_id = a.id_poli
                  left join global.global_auth_user e on e.usr_id = a.id_dokter
                  left join global.global_jenis_pasien f on a.reg_jenis_pasien = f.jenis_id
                  left join global.global_tipe_biaya g on a.reg_tipe_layanan = g.tipe_biaya_id 
                  where a.id_dep =".QuoteValue(DPE_CHAR,$depId)."
                  and i.is_tutup<>'y' and (i.pembayaran_det_id = i.id_pembayaran_det_multipayment or i.id_pembayaran_det_multipayment is null) and ".$sql_where." order by pembayaran_det_create asc";
          //echo $sql; 
          $dataTable = $dtaccess->FetchAll($sql);
      }

	      $tableHeader = "&nbsp;BATAL BAYAR PROSES";
          
          $counterHeader = 0;
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Lihat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;

          /*$tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;*/
           
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
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Cara Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Bayar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Petugas Kasir";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;

          $tbHeader[0][$counterHeader][TABLE_ISI] = "Total Bayar";           
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
          $counterHeader++;
          
      //print_r($data);    
      for($i=0,$nomor=1,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
          
          $sql = "select sum(fol_nominal) as nominal from klinik.klinik_folio where id_pembayaran_det=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_det_id"]);
          $rs = $dtaccess->Execute($sql);
          $folTotal = $dtaccess->Fetch($rs); 

          $sql = "select sum(pembayaran_det_dibayar) as total from klinik.klinik_pembayaran_det where id_pembayaran = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_id']);
          $TotalBayar = $dtaccess->Fetch($sql);

          $sql = "select * from gl.gl_buffer_transaksi where id_pembayaran_det = ".QuoteValue(DPE_CHAR,$dataTable[$i]['pembayaran_det_id']);
          $Jurnal = $dtaccess->Fetch($sql);

          $sql = "SELECT jbayar_nama as nama from global.global_jenis_bayar where jbayar_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]["id_jbayar"]);
          $jenis = $dtaccess->Fetch($sql);
          
          if(!$jenis['nama']){
            $sql = "SELECT perusahaan_nama as nama from global.global_perusahaan where perusahaan_id = ".QuoteValue(DPE_CHAR, $dataTable[$i]["id_jbayar"]);
            $jenis = $dtaccess->Fetch($sql);

            if(!$jenis['nama']){
              $jenis['nama'] = $dataTable[$i]["id_jbayar"];
            }
          }
          
          $sql = "select fol_keterangan, reg_keterangan from klinik.klinik_folio a 
          left join klinik.klinik_registrasi b on a.id_reg = b.reg_id
          where id_pembayaran_det=".QuoteValue(DPE_CHAR,$dataTable[$i]["pembayaran_det_id"]);
          $rs = $dtaccess->Execute($sql);
          $keterangan = $dtaccess->Fetch($rs); 
//      {	
			    $lihatPage = "batal_bayar_lihat_proses.php?id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"]."&id_pembayaran_det=".$dataTable[$i]["pembayaran_det_id"];
  		    $editPage = "batal_bayar_proses.php?id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"]."&id_pembayaran_det=".$dataTable[$i]["pembayaran_det_id"];
			    $hapusPage = "batal_bayar_proses.php?delReg=1&id_reg=".$dataTable[$i]["reg_id"]."&pembayaran_id=".$dataTable[$i]["pembayaran_id"]."&id_pembayaran_det=".$dataTable[$i]["pembayaran_det_id"];
        
        //if($total[$data[$i]]==$folTotal["nominal"]){   
//          if ($dataTable[$i]["reg_status"]=='F0')
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$lihatPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/cari.png" alt="Proses" title="Proses" border="0"/></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
          
                                                        
          //if ($dataTable[$i]["reg_status"]=='F0')
          if ($Jurnal['is_posting'] != 'y') {
            $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/edit.png" alt="Proses" title="Proses" border="0"/></a>';               
          }else{
            $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';               
          }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
                
              /*if ($dataTable[$i]["reg_utama"]=='')
              { 
                $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/spacer.gif" alt="Proses" title="Proses" border="0"/>';
              }
              elseif ($dataTable[$i]["reg_status"]=='F0')
              {
                $tbContent[$i][$counter][TABLE_ISI] = '<img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/spacer.gif" alt="Proses" title="Proses" border="0"/>';
              }
              else
              {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$hapusPage.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Proses" title="Proses" border="0"  onclick="javascript: return hapusReg();"/></a>';               
              }
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;*/          
    			 
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
          $tbContent[$i][$counter][TABLE_ISI] = ($keterangan["fol_keterangan"]) ? "&nbsp;&nbsp;&nbsp;&nbsp;".$keterangan["fol_keterangan"] : "&nbsp;&nbsp;&nbsp;&nbsp;".$keterangan["reg_keterangan"];
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

          $tipeRawat["J"]="Rawat Jalan";
          $tipeRawat["I"]="Rawat Inap";
          $tipeRawat["G"]="Rawat Darurat";
          
    			$tbContent[$i][$counter][TABLE_ISI] = $tipeRawat[$dataTable[$i]["reg_tipe_rawat"]];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["jenis_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $jenis['nama'];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["who_when_update"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
          $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($TotalBayar['total']);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

        //}  
//  		 		} //END JIKA SUDAH BAYAR   SEMENTARA DIHILANGKAN

          unset($sqlBayar);
          unset($dataBayar);
      } 
      
          //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);

    $sql = "select * from hris.hris_struktural where struK_nama like '%AKUNTANSI%'";
    $dataStruktural = $dtaccess->Fetch($sql);

    $sql = "select * from global.global_auth_user a
            left join hris.hris_pegawai b on b.pgw_id = a.id_pgw
            left join hris.hris_struktural c on c.struk_id = b.id_struk
            where usr_id = ".QuoteValue(DPE_CHAR,$userId)." and struk_nama = 'AKUNTANSI'";
    $dataJabatan = $dtaccess->Fetch($sql);
    // echo $sql;
	
?>
<?php  //echo $view->RenderBody("ipad_depans.css",true,"BATAL BAYAR KWITANSI"); ?>
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
                    <h2>Batal Bayar Kwitansi</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Periode Tanggal (DD-MM-YYYY)</label>
                        <div class='input-group date' id='datepicker'>
              <input name="tanggal_awal" type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_awal']) { echo $_POST['tanggal_awal']; } else { echo date('d-m-Y'); } ?>"  <?php if ($dataJabatan) echo "disabled"; ?>>
              <span class="input-group-addon">
                <span class="fa fa-calendar">
                </span>
              </span>
            </div>                   
      
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Sampai Tanggal (DD-MM-YYYY)</label>
            <div class='input-group date' id='datepicker2'>
              <input  name="tanggal_akhir"  type='text' class="form-control" 
              value="<?php if ($_POST['tanggal_akhir']) { echo $_POST['tanggal_akhir']; } else { echo date('d-m-Y'); } ?>"  <?php if ($dataJabatan) echo "disabled"; ?>>
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
	    	<script>document.frmFind.cust_usr_kode.focus();</script>
    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" class="btn btn-primary"></td>
			  <!--  <td align="right" class="tablecontent-odd"><input type="submit" id="btnView" name="btnView" value="Entry Tindakan" class="submit"></td> -->

     </div>
</form>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
</form>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
</div>

  		<!--<table  width="100%" cellspacing="1" border="0" cellpadding="1" align="left">
			<tr>
      <td align="left" width="15%" valign="middle" class="bawah"><?php echo '&nbsp;&nbsp;<strong><font face="sans-serif">'.$userName.'</font></strong>';?></font></td>
			<td align="left" width="10%" valign="middle" class="bawah"><input type="button" name="bantuan" class="submit" value="Bantuan" ></td>
      <td align="right" width="75%" valign="middle" class="bawah"><?php //echo '<strong><font face="calibri" size="3px">'.strtoupper($depNama).'</font></strong>';?>&nbsp;&nbsp;&nbsp;</td>
      </tr>
			</table>-->
<?php if($konfigurasi["dep_konf_dento"]=='y') { ;?>
<!--------Buat Helpicon----------->
<script type="text/javascript">
function showHideGB(){
var gb = document.getElementById("gb");
var w = gb.offsetWidth;
gb.opened ? moveGB(0, 30-w) : moveGB(20-w, 10);
gb.opened = !gb.opened;
}
function moveGB(x0, xf){
var gb = document.getElementById("gb");
var dx = Math.abs(x0-xf) > 10 ? 5 : 1;
//var dir = xf>x0 ? 1 : -1;
var dir = 10;
var x = x0 + dx * dir;
gb.style.right = x.toString() + "px";
if(x0!=xf){setTimeout("moveGB("+x+", "+xf+")", 10);}
}
</script>
<div id="gb"><div class="gbcontent"><div style="text-align:center;">
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/pembayaran_kasir.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
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