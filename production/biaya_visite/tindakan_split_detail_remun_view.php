<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $enc = new textEncrypt();     
     $auth = new CAuth();
   $depId = $auth->GetDepId();
   $depLowest = $auth->GetDepLowest();
     $table = new InoTable("table1","100%","left",null,1,2,1,null);
     $PageJenisBiaya = "page_jenis_biaya.php";    

     $tahunTarif = $auth->GetTahunTarif();
     $depNama = $auth->GetDepNama();
     $userName = $auth->GetUserName();
     
     /*if(!$auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_tarif_tarif_tindakan_semua_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/
     
  $backPage = "tindakan_split_detail_view.php"; 
    
    //Keterangan CITO
    $labelCito["C"] = "CITO";
    $labelCito["E"] = "Non CITO";
     
    $isAllowedCreate=1;
    $isAllowedUpdate=1;
    $isAllowedDel=1;
    $tipeRawat["TA"] = "IRJ";
    $tipeRawat["TG"] = "IGD";
    $tipeRawat["TI"] = "IRNA";
    
    $excel = $_POST["btnExcel"];
    $cetak = $_POST["btnCetak"];
      
     if($_POST["biaya_tarif_id"]) { 
       $biayaTarifId = $_POST["biaya_tarif_id"];
       $_POST["biaya_tarif_id"] = $_POST["biaya_tarif_id"];
       }
     
     if($_GET["biaya_tarif_id"]) { 
       $biayaTarifId = $_GET["biaya_tarif_id"];
       $_POST["biaya_tarif_id"] = $_GET["biaya_tarif_id"];
     }                                                            
     
     if($_POST["split_id"]) { 
       $splitId = $_POST["split_id"];
       $_POST["split_id"] = $_POST["split_id"];
       }
     
     if($_GET["split_id"]) { 
       $splitId = $_GET["split_id"];
       $_POST["split_id"] = $_GET["split_id"];
     }                                                            

    
    $sql = "select h.klinik_kategori_tindakan_header_instalasi_id,g.kategori_tindakan_header_id,
              c.kategori_tindakan_id
              from  klinik.klinik_biaya_tarif a           
              left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id     
              left join klinik.klinik_kategori_tindakan c on c.kategori_tindakan_id = b.biaya_kategori
              left join klinik.klinik_kategori_tindakan_header g on c.id_kategori_tindakan_header = g.kategori_tindakan_header_id 
              left join klinik.klinik_kategori_tindakan_header_instalasi h on g.id_kategori_tindakan_header_instalasi = h.klinik_kategori_tindakan_header_instalasi_id 
              where a.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId);        
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataAwal = $dtaccess->Fetch($rs);
      $_POST["id_kategori_tindakan_header_instalasi"] =  $dataAwal["klinik_kategori_tindakan_header_instalasi_id"];
      $_POST["id_kategori_tindakan_header"] =  $dataAwal["kategori_tindakan_header_id"];
      $_POST["biaya_kategori"] =  $dataAwal["kategori_tindakan_id"];
     
     $addPage = "tindakan_split_detail_remun_edit.php?split_id=".$splitId."&biaya_tarif_id=".$biayaTarifId."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"]."&tambah=1";
     $kembaliPage = "tindakan_split_detail_view.php?biaya_tarif_id=".$biayaTarifId."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"]."&biaya_id=".$_GET["biaya_id"];
     $editPage = "tindakan_split_detail_remun_edit.php?split_id=".$splitId."&biaya_tarif_id=".$biayaTarifId."&id_kategori_tindakan_header_instalasi=".$_POST["id_kategori_tindakan_header_instalasi"]."&id_kategori_tindakan_header=".$_POST["id_kategori_tindakan_header"]."&biaya_kategori=".$_POST["biaya_kategori"];
     $thisPage = "tindakan_split_detail_remun_view.php";

     $tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$addPage.'\'"></button>';
     $tombolKembali = '<input type="button" name="btnKembali" value="Kembali" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary" onClick="document.location.href=\''.$kembaliPage.'\'"></button>';
    
   $sql_where[] = "1=1"; 
     $sql_where[] = "k.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$biayaTarifId);    
     $sql_where[] = "k.id_split = ".QuoteValue(DPE_CHAR,$splitId);    
     $sql_where = implode(" and ",$sql_where);

         //, b.split_nama,j.bea_split_nominal,j.bea_split_id
          //    left join klinik.klinik_biaya_split j on k.id_split = j.id_split
          //    left join klinik.klinik_biaya_tarif h on j.id_biaya_tarif = h.biaya_tarif_id           
          //    left join klinik.klinik_split b on j.id_split = b.split_id
    
      $sql = "select k.*,l.fol_posisi_nama
              from  
              klinik.klinik_biaya_remunerasi k left join
              klinik.klinik_folio_posisi l on k.id_folio_posisi = l.fol_posisi_id
              where ".$sql_where;
      $sql .= " order by k.id_folio_posisi";
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataTable = $dtaccess->FetchAll($rs);
    
    
   $counterHeader = 0;
     $tableHeader = "Manajemen - Biaya Split Tarif Tindakan per Posisi";
      
     $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
     $counterHeader++;  
   
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
     //$counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
     //$counterHeader++;  
     
     //$tbHeader[0][$counterHeader][TABLE_ISI] = "Tipe Rawat";
     //$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     //$counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Posisi Nama";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Nominal";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++;

     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
     $counterHeader++; 
     
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
     $counterHeader++; 
          
     //TOTAL HEADER TABLE
     $jumHeader= $counterHeader;
    
     for($i=0,$counter=0,$n=count($dataTable);$i<$n;$i++,$counter=0){
  
      $tbContent[$i][$counter][TABLE_ISI] = $i+1;               
      $tbContent[$i][$counter][TABLE_ALIGN] = "center";
      $counter++;
        
      $tbContent[$i][$counter][TABLE_ISI] = $dataTable[$i]["fol_posisi_nama"];
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $counter++;
      
      $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataTable[$i]["biaya_remunerasi_nominal"]);
      $tbContent[$i][$counter][TABLE_ALIGN] = "left";
      $counter++;
            

    //    if($dataTable[$i]["biaya_jenis"]=='TA'){
  //      $tbContent[$i][$counter][TABLE_ISI] = "Rawat Jalan";  
//        }elseif($dataTable[$i]["biaya_jenis"]=='TG'){
//        $tbContent[$i][$counter][TABLE_ISI] = "I G D";  
//        }elseif($dataTable[$i]["biaya_jenis"]=='TI'){
//            $tbContent[$i][$counter][TABLE_ISI] = "Rawat Inap";
//        }
//            $tbContent[$i][$counter][TABLE_ALIGN] = "left";
//            $counter++;   
            
    //    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
    //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //        $counter++;
    //    $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;".$dataTable[$i]["biaya_nama"];
    //        $tbContent[$i][$counter][TABLE_ALIGN] = "left";
    //        $counter++;
        //if($isAllowedUpdate && $dataTable[$i]["biaya_tarif_id"]=='')
       // {
       //        $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$addPage.'&id='.$enc->Encode($dataTable[$i]["biaya_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/add.png" alt="Tambah" title="Tambah" border="0"></a>';
       // } else {
        //       $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';        
        //}

        //       $tbContent[$i][$counter][TABLE_ALIGN] = "center";
        //       $counter++;
               
          
        if($isAllowedUpdate)
        {
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id='.$enc->Encode($dataTable[$i]["biaya_remunerasi_id"]).'"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
        } else {
               $tbContent[$i][$counter][TABLE_ISI] = '&nbsp;';                
        }
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          
          if($isAllowedDel){
               $tbContent[$i][$counter][TABLE_ISI] = '<a href="'.$editPage.'&id_del='.$dataTable[$i]["biaya_remunerasi_id"].'&del=1"><img hspace="2" width="25" height="25" src="'.$ROOT.'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
               $tbContent[$i][$counter][TABLE_ALIGN] = "center";
               $counter++;
          }         
    }
    
    
     if($_POST["btnExcel"]){
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment; filename=tarif_all.xls');
     }
     
     if($_POST["btnCetak"]){
      $_x_mode = "cetak" ;      
     }


 

    $sql = "select d.bea_split_nominal,a.biaya_total,a.is_cito,b.biaya_nama,c.kelas_nama , e.split_nama
              from  klinik.klinik_biaya_split d 
              left join klinik.klinik_biaya_tarif a on d.id_biaya_tarif = a.biaya_tarif_id           
              left join klinik.klinik_biaya b on a.id_biaya = b.biaya_id   
              left join klinik.klinik_kelas c on c.kelas_id = a.id_kelas   
              left join klinik.klinik_split e on e.split_id = d.id_split
              where a.biaya_tarif_id = ".QuoteValue(DPE_CHAR,$biayaTarifId)." and id_split = ".QuoteValue(DPE_CHAR,$_GET['split_id']);
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataHeader = $dtaccess->Fetch($rs);
      // echo $sql;
      
    $sql = "select sum(biaya_remunerasi_nominal) as jumlah 
              from  klinik.klinik_biaya_remunerasi a            
              where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$biayaTarifId)." and 
              a.id_split = ".QuoteValue(DPE_CHAR,$splitId);
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataRemunNominal = $dtaccess->Fetch($rs);
      
      $sql = "select sum(bea_split_nominal) as jumlah 
              from  klinik.klinik_biaya_split a            
              where a.id_biaya_tarif = ".QuoteValue(DPE_CHAR,$biayaTarifId);
      //echo $sql;
      $rs = $dtaccess->Execute($sql);
      //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
      $dataSplitNominal = $dtaccess->Fetch($rs);

     
?>



<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>

  <body class="nav-sm">
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
                <h3><?php echo $tableHeader;?></h3>
              </div>
            </div>
      <div class="clearfix"></div>
      <!-- row filter -->
      <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h3>Nama Tindakan : <?php echo $dataHeader["biaya_nama"];?></h3>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_title">
                    <h2>Nama Split : <?php echo $dataHeader["split_nama"];?></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
          <form action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >          
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">Nominal Split : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo currency_format($dataHeader["bea_split_nominal"]);?></label>
                  </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">Kelas : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo $dataHeader["kelas_nama"];?></label>
                  </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">CITO : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo $labelCito[$dataHeader["is_cito"]];?></label>
              </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">Total Biaya Posisi : </label>
                        <label class="control-label col-md-4 col-sm-4 col-xs-4"><?php echo currency_format($dataRemunNominal["jumlah"]);?></label>
              </div>
                      
               <div class="col-md-4 col-sm-4 col-xs-4">
                          <label class="control-label col-md-4 col-sm-4 col-xs-4">&nbsp;</label>                     
              <?php echo "$tombolAdd"; ?>
                </div>
                      <div class="col-md-4 col-sm-4 col-xs-4">
                        <label class="control-label col-md-4 col-sm-4 col-xs-4">&nbsp;</label>
            <?php echo "$tombolKembali"; ?>
            </div>            
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
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
             <table class="table" cellspacing="0" width="100%">
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

