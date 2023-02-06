<?php
     //lib
require_once("../penghubung.inc.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."currency.php");
require_once($LIB."dateLib.php");
require_once($LIB."tampilan.php");

     // INISIALISASI lib
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new TextEncrypt();     
$auth = new CAuth();
$table = new InoTable("table","100%","left");
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();
$depId = $auth->GetDepId();
$depLowest = $auth->GetDepLowest();
$userId = $auth->GetUserId();
     //echo "admin=".$enc->Encode($userId);
$tableHeader = "&nbsp;User Account Master";
     //PRIVILLAGE
     // if(!$auth->IsAllowed("man_user_user_login",PRIV_READ)){
     //      die("access_denied");
     //      exit(1);

     // } elseif($auth->IsAllowed("man_user_user_login",PRIV_READ)===1){
     //      echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
     //      exit(1);
     // } 


if ($_GET["kembali"]) {
  $_POST["klinik"] = $_GET["kembali"]; 
       // echo $_POST["klinik"];
}

     //VARIABLE AWAL
$statusLogin["y"] = "Aktif";
$statusLogin["n"] = "Non Aktif";      

if (!$_GET["klinik"]) { $_POST["klinik"] = $depId; }     
else if($_GET["klinik"]) { $_POST["klinik"] = $_GET["klinik"]; 
}else if($_POST["klinik"]) { $_POST["klinik"] = $_POST["klinik"]; }

if($_GET["satker"]) $_POST["satker"] = $_GET["satker"];

if ($_GET["kembali"]) {
  $_POST["klinik"] = $_GET["kembali"]; 
       // echo $_POST["klinik"];
}     

$addPage = "hakakses_add.php";
$editPage = "hakakses_edit.php?klinik=".$_POST["klinik"];
$thisPage = "hakakses_view.php";
$ttd = "hakakses_ttd.php?";

     // -- paging config ---//
/*     $recordPerPage = 20;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;      */
     // -- end paging config ---//
     
     if($_GET["klinik"]){
       $_SESSION["x_id_jenis_x"] = $_POST["klinik"];
     }else{
       $_GET["klinik"] = $_SESSION["x_id_jenis_x"];

     }

     if($_POST["_nama"]) $sql_where[] = "UPPER(usr_loginname) like ".QuoteValue(DPE_CHAR,strtoupper("%".$_POST["_nama"]."%"));
     if($_POST["pgw_nama"]){
      $sql_where[] = " UPPER(usr_name) like '%".strtoupper($_POST["pgw_nama"])."%'";
    }
    if($_POST["id_rol"]) $sql_where[] = "a.id_rol = ".QuoteValue(DPE_CHAR,$_POST["id_rol"]);
    if($_POST["rol_jabatan"]){
      $sql_where[] = " a.rol_jabatan = ".QuoteValue(DPE_CHAR,$_POST["rol_jabatan"]);
    }
    if($_POST["satker"]){ 
      $sql="select * from hris.hris_struktural where struk_id='".$_POST["satker"]."'";
      $rs = $dtaccess->Execute($sql,DB_SCHEMA);
      $dataDepPilih = $dtaccess->Fetch($rs); 
    }


    if($sql_where) $sql_where = implode(" and ",$sql_where);

    if($_POST["btnSearch"] || $_GET['id']){
    }
     // QUERY TAMPILKAN DATA
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
          
/*		  $lokasi = $ROOT."gambar/foto_pegawai";
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


       if($_POST["klinik"]){
       //Data Klinik
         if($depLowest=='n'){
          $sql = "select * from global.global_departemen order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
        }else{
          $sql = "select * from global.global_departemen where dep_id = '".$_POST["klinik"]."' order by dep_id";
          $rs = $dtaccess->Execute($sql);
          $dataKlinik = $dtaccess->FetchAll($rs);
        }
      }else{
        $sql = "select * from global.global_departemen order by dep_id";
        $rs = $dtaccess->Execute($sql);
        $dataKlinik = $dtaccess->FetchAll($rs);
      }


       //Data Satuan Kerja

      $sql = "select * from hris.hris_struktural order by struk_tree";
      $rs = $dtaccess->Execute($sql);
      $dataSatker = $dtaccess->FetchAll($rs);

            //data Jabatan
      $sql = "select * from global.global_auth_role order by rol_name";
      $rs = $dtaccess->Execute($sql);
      $dataJabatan = $dtaccess->FetchAll($rs);


// Nama klinik
      $sql = "select dep_nama from global.global_departemen where
      dep_id = '".$_GET["klinik"]."'";
      $rs = $dtaccess->Execute($sql);
      $namaKlinik = $dtaccess->Fetch($rs);

      
      //Nama Klinik
      $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"];


      ?>
      <?php //echo $view->RenderBody("expressa.css",true); ?>

      <script language="JavaScript"> 
// Javascript buat warning jika di klik tombol hapus -,- 
function Reset() {
  if(confirm('apakah anda yakin akan me-RESET user ini ? Password user ini akan sama dengan User Login'));
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
          <div class="page-title">
            <div class="title_left">
              <h3>Manajemen</h3>
            </div>
          </div>

          <div class="clearfix"></div>
          <!-- row filter -->
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Filter</h2>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >



                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <label >Nama Pegawai</label>
                      <div id="div_header"><?php echo $view->RenderTextBox("pgw_nama","pgw_nama",30,200,$_POST["pgw_nama"],false,false);?>
                    </div> 
                  </div>

                   <input type="submit" name="btnSearch" value="Cari" class="pull-right btn btn-primary">
                 

               
                  <a target="_blank" href="ttd_dokter_cetak.php?pdf=1" class="pull-right btn btn-danger">Export PDF</a>
                 
                   
                  
                    <a target="_blank" href="ttd_dokter_cetak.php?cetak=1" class="pull-right btn btn-success">Cetak</a>
               

                  
                  

                  <div class="clearfix"></div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- //row filter -->

        <div class="row">

          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              <div class="x_title">
                <h2>Pegawai</h2>
                <span class="pull-right"><?php echo $tombolAdd; ?></span>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">
                <table class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                  <thead>
                    <tr>
                      <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?>                               
                       <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI];?> </th>
                     <? } ?>
                   </tr>
                 </thead>
                 <tbody>
                  <? for($i=0,$n=count($dataTable);$i<$n;$i++) {   ?>

                    <tr class="even pointer">
                      <? for($k=0,$l=$jumHeader;$k<$l;$k++) {  ?> 
                        <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI]?></td>
                      <? } ?>

                    </tr>

                  <? } ?>
                </tbody>
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