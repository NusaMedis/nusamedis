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
       
     $tableHeader = "&nbsp;Statistik IRJ";
  
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
     
?>
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
<script type="text/javascript">
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
</script>

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


<div id="top">
<table border="0" width="100%" valign="top" >
<tr>
<td width="10%" align="left" valign="top">
<a href="#" target="_blank"><img height="44px" src="<?php echo $APLICATION_ROOT;?>tampilan/images/style/logo.png"/></a>
</td>
<td width="90%" valign="top" align="right">
<a href="" target="_blank"><span style="font-size: 27pt; color: #fff;">Rekap Bulanan IRJ</span></a>&nbsp;&nbsp;
</td>
</tr>
</table>   
</div>
<!--<div id="body">
<div id="scroller">-->
<form name="frmView" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" onSubmit="return CheckSimpan(this);">    
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="tblForm" id="tblSearching">    
     <tr><td>&nbsp;</td></tr>
     <tr >
      <td align="left" class="tablecontent" width="20%">&nbsp;Nama RS</td>
			 <td class="tablecontent" width="80%"><select name="klinik" class="inputField" onKeyDown="return tabOnEnter_select_with_button(this, event);" ><!-- onChange="this.form.submit();"> -->
				<?php $counter = -1;
					for($i=0,$n=count($dataKlinik);$i<$n;$i++){
					unset($spacer); 
					$length = (strlen($dataKlinik[$i]["dep_id"])/TREE_LENGTH_CHILD)-1; 
					for($j=0;$j<$length;$j++) $spacer .= "..";
				?>
					<option class="inputField" value="<?php echo $dataKlinik[$i]["dep_id"];?>"<?php if ($_POST["klinik"]==$dataKlinik[$i]["dep_id"]) echo"selected"?>><?php echo $spacer." ".$dataKlinik[$i]["dep_nama"];?>&nbsp;</option>
				<?php } ?>
				</select>
		  </td>
		 </tr> 
     <tr>
          <td width="15%" class="tablecontent">&nbsp;Tanggal</td>
          <td width="50%" class="tablecontent">
               <input type="text"  id="tgl_awal" name="tgl_awal" size="15" maxlength="10" value="<?php echo $_POST["tgl_awal"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_awal" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />
               &nbsp;(dd-mm-yyyy)&nbsp;-&nbsp;
               <input type="text"  id="tgl_akhir" name="tgl_akhir" size="15" maxlength="10" value="<?php echo $_POST["tgl_akhir"];?>"/>
               <img src="<?php echo $APLICATION_ROOT;?>gambar/b_calendar.png" width="16" height="16" align="middle" id="img_tgl_akhir" style="cursor: pointer; border: 0px solid white;" title="Date selector" onMouseOver="this.style.background='red';" onMouseOut="this.style.background=''" />            
               &nbsp;(dd-mm-yyyy)
          </td>

          </tr>
      <tr>
          <td width="15%" class="tablecontent">&nbsp;Cara Bayar</td>
          <td width="50%" class="tablecontent">
               <select name="id_jenis" id="id_jenis" onKeyDown="return tabOnEnter(this,event);">
                <option value="">Pilih Cara Bayar</option>
                <?php for($i=0,$n=count($jenisPasien);$i<$n;$i++){ ?>
                <option value="<?php echo $jenisPasien[$i]["jenis_id"]?>" <?php if($_POST["id_jenis"]==$jenisPasien[$i]["jenis_id"]) echo "selected";?>><?php echo $jenisPasien[$i]["jenis_nama"];?></option>
                <?php } ?>
               </select>
          </td>

          </tr>
      <tr>
          <td width="15%" class="tablecontent">&nbsp;Klinik</td>
          <td width="50%" class="tablecontent">
               <select name="id_poli" id="id_poli" onKeyDown="return tabOnEnter(this,event);">
                <option value="">Pilih Klinik</option>
                <?php for($i=0,$n=count($poli);$i<$n;$i++){ ?>
                <option value="<?php echo $poli[$i]["poli_id"]?>" <?php if($_POST["id_poli"]==$poli[$i]["poli_id"]) echo "selected";?>><?php echo $poli[$i]["poli_nama"];?></option>
                <?php } ?>
               </select>
          </td>

          </tr>
     <tr>
               <td class="tablecontent" colspan="7">
               <input type="submit" name="btnLanjut" value="Lanjut" class="submit">
               <input type="submit" name="btnExcel" value="Export Excel" class="submit">
               <input type="submit" name="btnCetak" id="btnCetak" value="Cetak" class="submit" />
          </td>
          </tr>   
</table>
</form>


<script type="text/javascript">
    Calendar.setup({
        inputField     :    "tgl_awal",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_awal",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
    
    Calendar.setup({
        inputField     :    "tgl_akhir",      // id of the input field
        ifFormat       :    "<?=$formatCal;?>",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "img_tgl_akhir",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
</script>
<?php } ?>
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

<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>

<?php if(!$_POST["btnExcel"]) {?>
</div>
</div>
			
<?php }?>
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
<a href="javascript:showHideGB()" style="text-decoration:none; color:#000; font-weight:bold; line-height:0;"><img src="<?php echo $APLICATION_ROOT;?>gambar/tutupclose.png"/></a>
</div>
<center>
<a rel="sepur" href="<?php echo $ROOT;?>demo/laporan_kedatangan.php"><img src="<?php echo $APLICATION_ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>
