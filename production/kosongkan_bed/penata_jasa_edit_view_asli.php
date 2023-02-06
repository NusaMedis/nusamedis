<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
	    
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId();
     $userId = $auth->GetUserId();
     $userName = $auth->GetUserName();
     $usePoli = $auth->GetPoli();
     $depNama = $auth->GetDepNama();
 	 
    
       if(!$auth->IsAllowed("man_ganti_password",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("man_ganti_password",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     } 

     $_x_mode = "New";
     $thisPage = "penata_jasa_view.php";
     $editPage = "penata_jasa_proses.php";
     $transferPage = "penata_jasa_transfer.php";
     
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
     $time = date("H:i:s");
     
     if(!$_POST['tgl_awal']){
     $_POST['tgl_awal']  = $skr;
     }
     if(!$_POST['tgl_akhir']){
     $_POST['tgl_akhir']  = $skr;
     }
   
        if(!$_POST["tanggal_awal"]) $_POST["tanggal_awal"] = $skr;
        if(!$_POST["tanggal_akhir"]) $_POST["tanggal_akhir"] = $skr;
        if($_GET["tanggal_awal"]) $_POST["tanggal_awal"] =  $_GET["tanggal_awal"];
        if($_GET["tanggal_akhir"]) $_POST["tanggal_akhir"] =  $_GET["tanggal_akhir"];
        

     if($_POST["cust_usr_kode"])  $sql_where[] = "e.cust_usr_kode like".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
     if($_POST["cust_usr_nama"])  $sql_where[] = "e.cust_usr_nama like".QuoteValue(DPE_CHAR,"%".$_POST["cust_usr_nama"]."%");


     if($_POST["id_kamar"]<>'--')  $sql_where[] = "a.id_kamar like".QuoteValue(DPE_CHAR,$_POST["id_kamar"]);
    
     if ($sql_where[0]) 
	   $sql_where = implode(" and ",$sql_where);
      
          $sql ="select a.*,a.bed_kode, b.kamar_nama, b.kamar_kode
				          from klinik.klinik_kamar_bed a 
                  join klinik.klinik_kamar b on b.kamar_id = a.id_kamar                
                  where b.id_dep =".QuoteValue(DPE_CHAR,$depId)."
	                and a.bed_reserved ='y'";            
                  if ($sql_where) $sql .= " and ".$sql_where;
                  $sql .= " order by b.kamar_nama,a.bed_urut";
                // echo $sql;
                  $dataTable = $dtaccess->FetchAll($sql);
	     
		$row = -1;
		for($i=0,$n=count($dataTable);$i<$n;$i++) {

				$row++;
				$kamar[$dataTable[$i]["reg_id"]] = $dataTable[$i]["kamar_nama"];
				$bed[$dataTable[$i]["reg_id"]] = $dataTable[$i]["bed_kode"];  

	}
	   
   
    
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
          $counterHeader++;          


          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kamar";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "45%";
          $counterHeader++;
          
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Bed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
         
          $tbHeader[0][$counterHeader][TABLE_ISI] = "Kosongkan bed";
          $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
          $counterHeader++;
          
          
          

          
          
      for($i=0,$n=count($dataTable),$counter=0;$i<$n;$i++,$counter=0) {
     
			    $update = "penata_jasa_edit_view.php?updateBed=1&id_reg=".$reg[$data[$i]]."&bed_id=".$dataTable[$i]["bed_id"];
        
    
     
                          
    			$tbContent[$i][$counter][TABLE_ISI] = ($i+1);
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_nama"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;

    			$tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"];
    			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
              
          $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$update.'"><img hspace="2" width="30" height="30" src="'.$ROOT.'gambar/icon/hapus.png" alt="Proses" title="Proses" border="0" onclick="javascript: return hapusReg();"/></a>';               
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    			$tbContent[$i][$counter][TABLE_CLASS] = "tablecontent";
    			$counter++;
        

      } 
      
      
      if ($_GET["updateBed"]) 
    {     
        $bed= $_GET["bed_id"];
        $sql = "update klinik.klinik_kamar_bed set bed_reserved='n'
                         where bed_id = ".QuoteValue(DPE_CHAR,$bed);//[$i]);
               $dtaccess->Execute($sql, DB_SCHEMA);

     $kembali = "penata_jasa_edit_view.php";
     header("location:".$kembali);
     exit();    
   }
      

    $sql = "select * from klinik.klinik_kamar";
    $rs = $dtaccess->Execute($sql);
    $dataKamar = $dtaccess->FetchAll($rs);
    //echo $sql;
    
                                                                            
	
?>
<?php //echo $view->RenderBody("module.css",true,true,"KOSONGKAN BED"); ?>
<br /><br /><br /><br />

<?php //echo $view->InitUpload(); ?>
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
  if(confirm('apakah anda yakin akan mengosongkan bed???'));
  else return false;
}

function pulangReg() {
  if(confirm('apakah anda yakin akan memulangkan Pasien ini???'));
  else return false;
}



</script>

<script type="text/javascript" src="<?php echo $ROOT;?>lib/script/scroll_ipad2.js"></script>

<?
    //query judul menu
    $sql = "select * from global.global_auth_poli where poli_id='$usePoli'";
    $rs = $dtaccess->Execute($sql);
    $poliPoli = $dtaccess->Fetch($rs);
?>


<div id="bodyku">
<br />
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<table align="center" border=0 cellpadding=2 cellspacing=1 width="100%" class="smallheader" id="tblSearching">


	    	<script>document.frmFind.cust_usr_kode.focus();</script>

		<tr>
				<td width="10%" class="tablecontent" width="30%">Ruang :</td>
				<td class="tablecontent">
       		<select name="id_kamar">
						 <option value="--">[ Semua Kamar ]</option>
						 <?php for($i=0,$n=count($dataKamar);$i<$n;$i++) { ?>
							 <option value="<?php echo $dataKamar[$i]["kamar_id"];?>" <?php if($_POST["id_kamar"]==$dataKamar[$i]["kamar_id"]) echo "selected";?>><?php echo $dataKamar[$i]["kamar_nama"];?></option>
						 <?php } ?>

					 </select>
         </td>
    </tr>     

    <tr>
          <td colspan="2" class="tablecontent-odd"><input type="submit" name="btnLanjut" value="Lanjut" class="submit"></td>
	   </tr>
</table>
</form>

<script type="text/javascript">

</script>

<form name="frmView" method="POST" action="<?php echo $editPage; ?>">
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>    
</form>
<input type="hidden" name="x_mode" value="<?php echo $_x_mode ?>" />
</div>


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
 <?php //echo $view->RenderBottom("module.css",$userName,false,$depNama); ?>
<?php //echo $view->RenderBodyEnd(); ?>

