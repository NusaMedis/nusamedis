<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();  
     $auth = new CAuth();
     $table = new InoTable("table","100%","left");
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $thisPage = "report_pasien.php";
     $skr = date("d-m-Y");
     //$_POST["klinik"]=$depId;

     $sql = "select  b.rol_name,a.*, c.dep_nama, d.poli_nama,f.struk_nama,e.pgw_bagian,e.pgw_nama,e.pgw_nip,b.rol_jabatan 
    from global.global_auth_user a
    left join  hris.hris_pegawai e on a.id_pgw = e.pgw_id 
    left join global.global_auth_role b on a.id_rol = b.rol_id
    left join global.global_departemen c on a.id_dep = c.dep_id
    left join global.global_auth_poli d on d.poli_id = a.usr_poli
    left join hris.hris_struktural f on f.struk_id = e.id_struk where 1=1 and pgw_tipe='D' and b.rol_name='DOKTER'";
    if($_GET['id'])$sql .= " and usr_id = '".$_GET['id']."'";
    if($dataDepPilih["struk_tree"]) $sql .= "  and struk_tree like'".$dataDepPilih["struk_tree"]."%' ";

    if($sql_where) $sql .= " and ".$sql_where  ;

             //where a.id_dep like '".$_POST["klinik"]."%' and a.id_rol <> 0 
    $sql .= " order by struk_tree,pgw_nama asc";

    $rs = $dtaccess->Execute($sql);
    // echo $sql;
    $dataTable = $dtaccess->FetchAll($rs);

     //echo $sql; 
     // --- ngitung jml data e ---              
    $sql = "select count(usr_id) as total from  hris.hris_pegawai e
    left join global.global_auth_user a on a.id_pgw = e.pgw_id
    left join global.global_auth_role b on a.id_rol = b.rol_id
    left join hris.hris_struktural f on f.struk_id = e.id_struk
    where 1=1";
    if($dataDepPilih["struk_tree"]) $sql .= "  and struk_tree like'".$dataDepPilih["struk_tree"]."%' ";
    if($sql_where) $sql .= " and ".$sql_where  ;
//     echo $sql;
    $rsNum = $dtaccess->Execute($sql);

    $numRows = $dtaccess->Fetch($rsNum);

     //*-- config table ---*//                                    


     //--- construct new table ----//
    $counterHeader = 0;

     /*$tbHeader[0][$counterHeader][TABLE_ISI] = "<input type=\"checkbox\" onClick=\"EW_selectKey(this,'cbDelete[]');\">";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "3%";
     $counterHeader++;*/
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
     $counterHeader++;
     
     /*
   $tbHeader[0][$counterHeader][TABLE_ISI] = "Foto";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
     $counterHeader++; */

     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Dokter";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     $counterHeader++;    

     $tbHeader[0][$counterHeader][TABLE_ISI] = "Tanda Tangan";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "50%";
     $counterHeader++;    



     

     $jumHeader = $counterHeader;
     
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){

          /*$tbContent[$i][$counter][TABLE_ISI] = '<input type="checkbox" name="cbDelete[]" value="'.$dataTable[$i]["usr_id"].'">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;*/
          $tbContent[$i][$counter][TABLE_ISI] = ($i+1);
          $tbContent[$i][$counter][TABLE_ALIGN] = "right";
          $counter++;
          
/*      $lokasi = $ROOT."gambar/foto_pegawai";
      if($dataTable[$i]["usr_foto"]) $fotoName=$lokasi."/".$dataTable[$i]["usr_foto"];
          else $fotoName = $lokasi."/default.jpg";
          
          $tbContent[$i][$counter][TABLE_ISI] ='<img hspace="2" width="75" height="75" src="'.$fotoName.'" border="0">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;   */


          $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["usr_name"];
          $tbContent[$i][$counter][TABLE_ALIGN] = "left";
          $counter++;         


          $lokasi = "../gambar/asset_ttd/".$dataTable[$i]["usr_id"].".jpg";   
          if (file_exists($lokasi)) {
           $tbContent[$i][$counter][TABLE_ISI] = "<img src=".$lokasi." alt='' style='width:200px' >";
           
         }
         else{
           $tbContent[$i][$counter][TABLE_ISI] = $status;
         }

         
         $tbContent[$i][$counter][TABLE_ALIGN] = "center";
         $counter++;



     }
     
   
     $tableHeader="Laporan Tanda Tangan Dokter";

if ($_GET['cetak']=="y") { ?>

<script language="JavaScript">

window.print();

</script>

<!-- Print KwitansiCustom Theme Style -->
<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">

<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
    <span class="judul3">
    <?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
    <span class="judul4">       
    <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>  
  </tr>
</table>

   <table border="0" colspan="3" cellpadding="3" cellspacing="0" style="align:left" width="100%">  
 <tr>
    <td colspan="13"width="100%"  style="text-align:center;font-size:16px;font-family:sans-serif;font-weight:bold;" class="tablecontent">
             <?php echo $tableHeader; ?> </td>
   
 </tr>   
      
       
   

  </table>


  
 <?php   # code...
       } 
        else  if ($_GET['excel']=="y"){
            header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=laporan_kunjungan_pasien_irj.xls');?>

          <br>

   <table border="0" colspan="3" cellpadding="3" cellspacing="0" style="align:left" width="100%">  
 <tr>
    <td colspan="13"width="100%"  style="text-align:center;font-size:16px;font-family:sans-serif;font-weight:bold;" class="tablecontent">
             <?php echo $tableHeader; ?> </td>
   
 </tr>   
      
       
 
  </table>


 
 <br>
<br>  

<?
        }

?>


  <table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<?php echo $table->RenderView($tbHeader,$tbContent,$tbBottom); ?>
</td>
</tr>
</table> 
