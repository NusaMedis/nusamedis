<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $userData = $auth->GetUserData();
     $depNama = $auth->GetDepNama();
	   $depId = $auth->GetDepId();
	   $userName = $auth->GetUserName();
	   //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
     $depLowest = $auth->GetDepLowest();
     
    /* if(!$auth->IsAllowed("sirs_flow_status_pasien_irj",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("sirs_flow_status_pasien_irj",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }  */

    $_x_mode = "New";
     $thisPage = "pasien_view.php";
     $editPage = "detail_pasien.php?tindakan=1&";
     $findPage = "pasien_find.php?";

     if($_GET['tindakan']){
        // if($_GET["id_cust_usr"]) $_POST["cust_usr_id"] = $enc->Decode($_GET["cust_usr_id"]);
        $sql = "select * from global.global_customer_user a 
        left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id
        left join klinik.klinik_perawatan c on c.id_reg = b.reg_id
        left join klinik.klinik_jadwal d on d.id_reg = b.reg_id
                left join klinik.klinik_rawatinap g on b.reg_id = g.id_reg
        left join global.global_auth_poli e on b.id_poli = e.poli_id
        left join global.global_departemen f on b.id_dep = f.dep_id
        left join klinik.klinik_kamar h on h.kamar_id = g.id_kamar
                left join klinik.klinik_kamar_bed i on i.bed_id = g.id_bed
                left join klinik.klinik_kelas j on b.reg_kelas = j.kelas_id
        left join global.global_shift k on b.reg_shift = k.shift_id
          where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_GET["cust_usr_id"])." and b.reg_tipe_rawat !='I'  and reg_utama isnull
        order by b.reg_tanggal desc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
        $dataTable = $dtaccess->Fetch($rs);

        // echo $dataTable["id_pembayaran"];

        $sql = "update klinik.klinik_folio set id_pembayaran= ".QuoteValue(DPE_CHAR,$_GET["id_pembayaran"]).",is_transfer='y'
        WHERE id_pembayaran= ".QuoteValue(DPE_CHAR, $dataTable["id_pembayaran"])." and fol_lunas = 'n'";
        $dtaccess->Execute($sql);
        echo $sql;
      
        $kembali = "index.php";
        header("location:".$thisPage);
        exit();






     }
     
  if($_GET["id_cust_usr"]) $_POST["cust_usr_id"] = $enc->Decode($_GET["id_cust_usr"]);
  
     	
	if($_POST["cust_usr_kode"]) {
		$sql = "select cust_usr_id, cust_usr_nama from global.global_customer_user a where a.cust_usr_kode = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_kode"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
    $_POST["cust_usr_id"] = $dataPasien["cust_usr_id"];
    
    }

	if($_POST["cust_usr_id"]) {
		$sql = "select cust_usr_id, cust_usr_nama,cust_usr_kode from global.global_customer_user a where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"]);
		$dataPasien = $dtaccess->Fetch($sql,DB_SCHEMA_GLOBAL);
    $_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
    
    
     
    }

	
     if($dataPasien) {
          $table = new InoTable("table","100%","left");

          $sql = "select * from global.global_customer_user a 
                  left join klinik.klinik_registrasi b on b.id_cust_usr = a.cust_usr_id
                  left join klinik.klinik_perawatan c on c.id_reg = b.reg_id
                  left join klinik.klinik_jadwal d on d.id_reg = b.reg_id
				          left join klinik.klinik_rawatinap g on b.reg_id = g.id_reg
                  left join global.global_auth_poli e on b.id_poli = e.poli_id
                  left join global.global_departemen f on b.id_dep = f.dep_id
                  left join klinik.klinik_kamar h on h.kamar_id = g.id_kamar
				          left join klinik.klinik_kamar_bed i on i.bed_id = g.id_bed
				          left join klinik.klinik_kelas j on b.reg_kelas = j.kelas_id
                  left join global.global_shift k on b.reg_shift = k.shift_id
					where a.cust_usr_id = ".QuoteValue(DPE_CHAR,$_POST["cust_usr_id"])." and reg_utama isnull
                  order by b.reg_tanggal desc";
          $rs = $dtaccess->Execute($sql,DB_SCHEMA_KLINIK);
          $dataTable = $dtaccess->FetchAll($rs);
          
          $addPage = "perawatan_tambah.php?tambah=1&id=".$enc->Encode($dataTable[0]["cust_usr_id"]);
          
          //*-- config table ---*//
          $tableHeader = "&nbsp;Nama : ".$dataPasien["cust_usr_nama"];
          
         // $isAllowedUpdate = $auth->IsAllowed("dok_edit_pemeriksaan",PRIV_UPDATE);
          
          // --- construct new table ---- //
         // $colspan = ($isAllowedUpdate) ? 2:1;
          $colspan =2;
          $tbHeader[0][0][TABLE_ISI] = $tableHeader;
          $tbHeader[0][0][TABLE_WIDTH] = "80%";
          $tbHeader[0][0][TABLE_COLSPAN] = "12";
          
          //$tbHeader[0][1][TABLE_ISI] = '<a href="'.$addPage.'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
          //$tbHeader[0][1][TABLE_WIDTH] = "30%";
          //$tbHeader[0][1][TABLE_CLASS] = "tablecontent-odd";
          //$tbHeader[0][1][TABLE_COLSPAN] = "2";


          $counterHeader = 0;
         
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Tanggal";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;    

     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Bayar";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "3%";
          $counterHeader++;    
*/
          $tbHeader[1][$counterHeader][TABLE_ISI] = "Status";
          $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%";
          $counterHeader++;    
		  
     /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Anamnesa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Diagnosa";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "16%"; 
         $counterHeader++;

         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Pemeriksaan Fisik";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Penunjang";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;
        
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Tindakan";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;   
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Terapi(Resep)";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++; */
         
         $tbHeader[1][$counterHeader][TABLE_ISI] = "Poli";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;

		$tbHeader[1][$counterHeader][TABLE_ISI] = "Shift";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%";     
         $counterHeader++;
         
    /*     $tbHeader[1][$counterHeader][TABLE_ISI] = "Klinik";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "12%"; 
         $counterHeader++;
         */
		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Kelas";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "4%"; 
         $counterHeader++;
          
		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Bangsal";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "8%"; 
         $counterHeader++;

		 $tbHeader[1][$counterHeader][TABLE_ISI] = "Bed";
         $tbHeader[1][$counterHeader][TABLE_WIDTH] = "5%"; 
         $counterHeader++;		 
		 // if($isAllowedUpdate){
               $tbHeader[1][$counterHeader][TABLE_ISI] = "Edit";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++;
              //}
              
           /*    $tbHeader[1][$counterHeader][TABLE_ISI] = "Hapus";
               $tbHeader[1][$counterHeader][TABLE_WIDTH] = "7%";
               $counterHeader++; */
              
              
          for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
               
              
               if($dataTable[$i]["rawat_tanggal"]) {
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["reg_tanggal"]);
               } else {
               $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;&nbsp;".format_date($dataTable[$i]["reg_tanggal"])." (Belum Ada Data)";
               }$tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
                
      /*         $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["reg_bayar"];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
			*/   
			    $tbContent[$i][$counter][TABLE_ISI] = $regPasienStatus[$dataTable[$i]["reg_status"]];
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";          
               $counter++;
          
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["poli_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
		  
          //if ($dataTable[$i]["reg_shift"]==1) {}
		      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["shift_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;
     /*     
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["dep_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "left"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
               */
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kelas_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["kamar_nama"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["bed_kode"]; 
          $tbContent[$i][$counter][TABLE_ALIGN] = "center"; 
          $tbContent[$i][$counter][TABLE_VALIGN] = "top";        
          $tbContent[$i][$counter][ROWSPAN] = $dataSpan["jml_span"]; 
          $counter++;  
          
		  if($dataTable[$i]["reg_tipe_rawat"] == "I") {
                    $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["rawat_id"]).'&cust_usr_id='.$dataTable[$i]["cust_usr_id"].'&id_pembayaran='.$dataTable[$i]["id_pembayaran"].'&utama='.$dataTable[$i]["reg_utama"].'"><img hspace="2" width="32" height="32" src="'.$ROOT.'gambar/finder.png" alt="Edit" title="Edit" border="0"></a>';               
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++;
               }
               else{

            
                    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
                    $counter++; 


               }
                
                
          }
          
          $colspan = $colspan;
          
          $tbBottom[0][0][TABLE_ISI] .= '&nbsp;';
          $tbBottom[0][0][TABLE_WIDTH] = "100%";
          $tbBottom[0][0][TABLE_COLSPAN] = "12";
     }
     
       //-----konfigurasi-----//
    $sql = "select * from global.global_departemen";
    $sql .= " where dep_id=".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
    //echo $sql;
	     

?>

<?php// echo $view->InitUpload(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.easing-1.3.pack.js"></script>
<script src="<?php echo $ROOT;?>lib/script/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
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

function Kembali() {

    document.location.href='pasien_view.php';
}

</script>


<?php// echo $view->InitThickBox(); ?>
<script language="JavaScript">

// Javascript buat warning jika di klik tombol hapus -,- 
function hapus() {
  if(confirm('apakah anda yakin akan menghapus data ini???'));
  else return false;
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
                    <h2>Kedatangan Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
<form name="frmFind" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<?php if(!$dataPasien["cust_usr_id"] && $_POST["btnLanjut"]) { ?>
<font color="red"><strong>No. RM Tidak Ditemukan</strong></font>
<?php } ?>

<script>document.frmFind.cust_usr_kode.focus();</script>

</form>

<?php if($dataPasien["cust_usr_id"] || $_POST["btnAdd"]) { ?>
<form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data"  onSubmit="return CheckSimpan(this)">
<table width="100%" align="center">
<!--<tr><td align="right">
     <a href="<?php echo $addPage; ?>" style="text-decoration:none"><input type="button" value="Tambah" class="submit" alt="Tambah" title="Tambah" border="0"></a>
</td></tr>-->
<tr><td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td></tr>
<tr>
    <td>
    <input type="button" name="btnBack" id="btnBack" value="Kembali" class="submit" onClick="javascript: Kembali();" />
    </td>
</tr>
</table>
</form>
<?php } ?>

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
<a rel="sepur" href="<?php echo $ROOT;?>demo/edit_pemeriksaan.php"><img src="<?php echo $ROOT;?>gambar/helpicon.gif"/></a>
</center>
<script type="text/javascript">
var gb = document.getElementById("gb");
gb.style.center = (30-gb.offsetWidth).toString() + "px";
</script></center></div></div>
<?php } ?>


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