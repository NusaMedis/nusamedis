<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new TextEncrypt();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
$depLowest = $auth->GetDepLowest();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
//$poli = $auth->GetPoli();

$editPage = "item_edit.php";
$thisPage = "item_view.php?";

// PRIVILLAGE
if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}


/* if(!$auth->IsAllowed("apo_setup_barang",PRIV_READ)){
         echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
    } elseif($auth->IsAllowed("apo_setup_barang",PRIV_READ)===1){
         echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
         exit(1);
     }  */
$poli = "33"; //POLI APOTIK IRJ
if (!$_POST["id_jenis"]) $_POST["id_jenis"] = "2";

$sql = "select id_gudang from global.global_auth_poli where poli_id=" . QuoteValue(DPE_CHAR, $poli);
$rs = $dtaccess->Execute($sql);
$gudang = $dtaccess->Fetch($rs);
$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif

$sql = "select id_gudang from global.global_auth_poli where poli_id=" . QuoteValue(DPE_CHAR, $poli);
$rs = $dtaccess->Execute($sql);
$gudang = $dtaccess->Fetch($rs);
$theDep = $gudang["id_gudang"];  //Ambil Gudang yang aktif 

//Apotik Konfigurasi                                                          
$sql = "select conf_apotik_harga_otomatis_margin from apotik.apotik_conf";
$rs = $dtaccess->Execute($sql);
$confApotik = $dtaccess->Fetch($rs);

if ($_GET["klinik"]) {
  $_POST["klinik"] = $_GET["klinik"];
} else if ($_POST["klinik"]) {
  $_POST["klinik"] = $_POST["klinik"];
} else {
  $_POST["klinik"] = $depId;
}

if ($_GET["kembali"]) $_POST["klinik"] = $_GET["kembali"];

// -- paging config ---//
/*$recordPerPage = 100;
     if($_GET["currentPage"]) $currPage = $_GET["currentPage"];
     else $currPage = 1;
     $startPage = ($currPage-1)*$recordPerPage;
     $endPage = $startPage + $recordPerPage;
     // -- end paging config ---//
     
     if($_GET["klinik"]){
       $_SESSION["x_id_jenis_x"] = $_POST["klinik"];
     }else{
       $_GET["klinik"] = $_SESSION["x_id_jenis_x"];
     }
   
     $tipe["V"] = "Volume Based";
     $tipe["N"] = "Non Valume Based";
     */
//ambil data outlet dan data gudang
/* $sql = "select konf_outlet,konf_gudang from logistik.logistik_konfigurasi 
        where konf_id = 0";
     $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA_LOGISTIK);
     $konfigurasi = $dtaccess->Fetch($rs_edit);   */

/* if($_GET["id_jenis"]){
       $_SESSION["x_id_jenis_x"] = $_POST["id_jenis"];
     }else{
       $_GET["id_jenis"] = $_SESSION["x_id_jenis_x"];
     } */

$now = date('Y-m-d H:i:s');
$yesterday = date('Y-m-d H:i:s', strtotime($now. '-1 day'));

if (!$_POST["id_jenis"]) $_POST["id_jenis"] = "2";
/*if($_POST["id_jenis"]) $sql_where [] = " item_tipe_jenis = ".QuoteValue(DPE_CHAR,$_POST["id_jenis"]);*/
if ($_POST["grup_item_id"]) $sql_where[] = " id_kategori = " . QuoteValue(DPE_CHAR, $_POST["grup_item_id"]);
if ($_POST["_nama"]) $sql_where[] = "UPPER(a.item_nama) like " . QuoteValue(DPE_CHAR, strtoupper("%" . $_POST["_nama"] . "%"));
// if($_POST["klinik"] && $_POST["klinik"]!="--") $sql_where[] = "a.id_dep = ".QuoteValue(DPE_CHAR,$_POST["klinik"]);
if ($_POST["item_aktif"]) $sql_where[] = "a.item_aktif = " . QuoteValue(DPE_CHAR, $_POST["item_aktif"]);
if ($_POST["obat_flag"]) $sql_where[] = "a.obat_flag = " . QuoteValue(DPE_CHAR, $_POST["obat_flag"]);
if ($sql_where) $sql_where = implode(" and ", $sql_where);

$addPage = "item_edit.php?tambah=" . $_POST["klinik"] . "&id_kategori=" . $_POST["grup_item_id"];

if ($_POST["btnLanjut"] || $_POST["btnSearch"] || $_POST["btnExcel"]) {
  $sql = "select a.*, b.jenis_nama, b.jenis_id, c.dep_nama, d.grup_item_nama, e.satuan_nama as satuan_beli, f.satuan_nama as satuan_jual,
     kategori_tindakan_nama, sup_nama, tipe_sediaan_nama
     from logistik.logistik_item a
     left join global.global_jenis_pasien b on b.jenis_id = a.item_tipe_jenis
     left join global.global_departemen c on c.dep_id = a.id_dep
     left join global.global_supplier h on a.id_sup = h.sup_id
     left join logistik.logistik_grup_item d on d.grup_item_id=a.id_kategori
     left join logistik.logistik_item_satuan e on a.id_satuan_beli = e.satuan_id
     left join logistik.logistik_item_satuan f on a.id_satuan_jual = f.satuan_id
     left join klinik.klinik_kategori_tindakan g on a.id_kategori_tindakan = g.kategori_tindakan_id 
     left join logistik.logistik_tipe_sediaan i on a.id_kat_item = i.tipe_sediaan_id
     where a.item_flag='M' and item_racikan = 'n' and a.item_id in(select id_item from logistik.logistik_stok_item where (stok_item_flag != 'A' or (stok_item_flag = 'A' and stok_item_create <= '$now' and stok_item_create >= '$yesterday' )) and id_item = a.item_id) ";
  if ($sql_where) $sql .= "  and " . $sql_where;
  //$sql .= " order by id_kategori asc, item_berlaku asc ";
  $sql .= " order by grup_item_nama,item_nama ";
  $rs = $dtaccess->Execute($sql);
  $dataTable = $dtaccess->FetchAll($rs);
  //echo $sql;
  //echo $sql.'<br>';
  // --- ngitung jml data e ---              
  /*$sql = "select count(item_id) as total
               from logistik.logistik_item";
      if($sql_where) $sql .= " where ".$sql_where;
     $rsNum = $dtaccess->Execute($sql);
     $numRows = $dtaccess->Fetch($rsNum); */
  //echo $sql;
  //*-- config table ---*//


  $tableHeader = "&nbsp;Setup Barang";

  $isAllowedDel = $auth->IsAllowed("inv_setup_setup_barang", PRIV_DELETE);
  $isAllowedUpdate = $auth->IsAllowed("inv_setup_setup_barang", PRIV_UPDATE);
  $isAllowedCreate = $auth->IsAllowed("inv_setup_setup_barang", PRIV_CREATE);

  // --- construct new table ---- //
  $counterHeader = 0;
  if (!$_POST["btnExcel"]) {
    //if($isAllowedDel){
    $tbHeader[0][$counterHeader][TABLE_ISI] = "";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
    $counterHeader++;
    //}

    //if($isAllowedUpdate){
    $tbHeader[0][$counterHeader][TABLE_ISI] = "";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
    $counterHeader++;
    //}
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Batch";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
    $counterHeader++;

    $tbHeader[0][$counterHeader][TABLE_ISI] = "Cek";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
    $counterHeader++;
  }

  $tbHeader[0][$counterHeader][TABLE_ISI] = "No";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Item";
  // $tbHeader[0][$counterHeader][TABLE_ISI] = "Item ID";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Barang";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "H. Pokok Supplier";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Diskon";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "PPN Masukan";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Margin";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "H. Jual";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;
  /*
     $tbHeader[0][$counterHeader][TABLE_ISI] = "Stok";
     $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";     
     $counterHeader++;  
*/
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan Beli";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Satuan Jual";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Sediaan";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Supplier";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "7%";
  $counterHeader++;


  if (!$_POST["item_aktif"]) {
    $tbHeader[0][$counterHeader][TABLE_ISI] = "Aktif";
    $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
    $counterHeader++;
  }
  //TOTAL HEADER TABLE
  $jumHeader = $counterHeader;
  for ($i = 0, $j = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0, $j++) {

    $dataTable[$i]["item_hpp"] = ($dataTable[$i]["item_harga_diskon"] && $dataTable[$i]["item_harga_diskon"] != null && $dataTable[$i]["obat_flag"] == 'g') ? $dataTable[$i]["item_harga_diskon"] : $dataTable[$i]["item_hpp"];

    $hargabeli = $dataTable[$i]["item_hpp"];
    //cari margin obat
    if ($hargabeli > 0) {
      $sql = "select margin_nilai from apotik.apotik_margin
      where id_grup_item = " . QuoteValue(DPE_CHAR, $dataTable[$i]["id_kategori"]) . "
      and is_aktif ='Y' and " . $hargabeli . " >= harga_min and " . $hargabeli .
        " <= harga_max ";
      // yang lama 
      // $sql = "select margin_nilai from apotik.apotik_margin
      // where id_jenis_pasien = " . QuoteValue(DPE_NUMERIC, $_POST["id_jenis"]) . "
      // and is_aktif ='Y' and " . $hargabeli . " >= harga_min and " . $hargabeli .
      //   " <= harga_max ";
      $rs = $dtaccess->Execute($sql);
      $margin = $dtaccess->Fetch($rs);
    }
    $ppn = 0.1 * $dataTable[$i]["item_hpp"];
    $Hmargin = ($margin["margin_nilai"] / 100) * ($dataTable[$i]["item_hpp"]);

    if ($confApotik["conf_apotik_harga_otomatis_margin"] == 'y'){ //jika harga dari margin maka dari perhitungan jika tidak ambil dari db
      $hargajual = $dataTable[$i]["item_hpp"] + $Hmargin;
    }
    else{
      $hargajual = $dataTable[$i]["item_harga_jual"];
    }

    if($dataTable[$i]["obat_flag"] == 'g'){
      $item_id = $dataTable[$i]["item_id"];
      $sql = "SELECT faktur_item_diskon_persen, faktur_item_hpp from logistik.logistik_faktur_item a where id_item = '$item_id' order by faktur_item_when_create desc limit 1";
      $diskon = $dtaccess->Fetch($sql);

      $harga_diskon = intval($diskon['faktur_item_hpp'] * $diskon['faktur_item_diskon_persen'] / 100);
    }

    //cari batch item ada nggak
    $sql = " select batch_id from logistik.logistik_item_batch where id_item = " . QuoteValue(DPE_CHAR, $dataTable[$i]["item_id"]);
    $rs  = $dtaccess->Execute($sql);
    $batchdata = $dtaccess->Fetch($rs);
    /* if($dataTable[$i]["id_kategori_tindakan"]!=$dataTable[$i-1]["id_kategori_tindakan"]){
      $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["kategori_tindakan_nama"];
      $tbContent[$j][$counter][TABLE_ALIGN] = "left";
      $tbContent[$j][$counter][TABLE_CLASS] = "tablesmallheader";
      $tbContent[$j][$counter][TABLE_COLSPAN] = $counterHeader;
      $counter=0;
      $j++;
  }    
   */
    if (!$_POST['btnExcel']) {
      //          if($isAllowedDel) {
      $tbContent[$j][$counter][TABLE_ISI] = '<a href="' . $editPage . '?del=1&id=' . $enc->Encode($dataTable[$i]["item_id"]) . '&klinik=' . $dataTable[$i]["id_dep"] . '"><img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onClick="javascript:return Hapus();"></a>';
      $tbContent[$j][$counter][TABLE_ALIGN] = "center";
      $counter++;
      //          }



      //          if($isAllowedUpdate) {
      $tbContent[$j][$counter][TABLE_ISI] = '<a href="' . $editPage . '?id=' . $enc->Encode($dataTable[$i]["item_id"]) . '&klinik=' . $dataTable[$i]["id_dep"] . '&id_jenis=' . $_POST['id_jenis'] . '"><img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
      $tbContent[$j][$counter][TABLE_ALIGN] = "center";
      $counter++;
      //          }

      $tbContent[$j][$counter][TABLE_ISI] = '<a href="tambah_batch.php?id_item=' . $dataTable[$i]["item_id"] . '&klinik=' . $dataTable[$i]["id_dep"] . '&id_jenis=' . $dataTable[$i]["item_tipe_jenis"] . '&id_gudang=' . $theDep . '"><img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/add.png" border="0" alt="Tambah Batch" title="Tambah Batch" width="18" height="18" class="img-button")"/></a>';
      $tbContent[$j][$counter][TABLE_ALIGN] = "center";
      $counter++;

      if ($batchdata) {
        $tbContent[$j][$counter][TABLE_ISI] = '<img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/ok.png" border="0" alt="Cek Batch" title="Cek Batch" width="18" height="18" class="img-button")"/>';
      } else {
        $tbContent[$j][$counter][TABLE_ISI] = '&nbsp';
      }
      $tbContent[$j][$counter][TABLE_ALIGN] = "left";
      $counter++;
    }
    /*  $lokasi = $ROOT."images/item";
         $fotoName=$lokasi."/".$dataTable[$i]["item_pic"];
          
          $tbContent[$i][$counter][TABLE_ISI] ='<img hspace="2" width="100" height="75" src="'.$fotoName.'" border="0">';
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++; */
    $tbContent[$j][$counter][TABLE_ISI] = $j + 1;
    $tbContent[$j][$counter][TABLE_ALIGN] = "center";
    $counter++;

    // $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["item_id"];
    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["item_kode"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["item_nama"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_hpp"]);
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = currency_format($harga_diskon);
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = currency_format($ppn);
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = currency_format($Hmargin);
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = currency_format($hargajual);
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;
    /*      
          $tbContent[$j][$counter][TABLE_ISI] = currency_format($dataTable[$i]["item_stok"]);
          $tbContent[$j][$counter][TABLE_ALIGN] = "center";
          $counter++;  
*/
    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["satuan_beli"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "center";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["satuan_jual"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["grup_item_nama"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["tipe_sediaan_nama"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;

    $tbContent[$j][$counter][TABLE_ISI] = $dataTable[$i]["sup_nama"];
    $tbContent[$j][$counter][TABLE_ALIGN] = "left";
    $counter++;



    if (!$_POST["item_aktif"]) {
      if ($dataTable[$i]["item_aktif"] == 'y') {
        $tbContent[$j][$counter][TABLE_ISI] = 'Aktif';
      } else {
        $tbContent[$j][$counter][TABLE_ISI] = 'Tidak';
      }
      $tbContent[$j][$counter][TABLE_ALIGN] = "left";
      $counter++;
    }
  }

  $colspan = count($tbHeader[0]);
}
//data jenis
$sql = "select jenis_id , jenis_nama from global.global_jenis_pasien where jenis_flag = 'y' order by jenis_nama desc";
$dataJenis = $dtaccess->FetchAll($sql);
//echo $dataJenis;

// --- master Tipe  ---
$sql = "select * from logistik.logistik_grup_item  where item_flag='M' and id_dep = " . QuoteValue(DPE_CHAR, $_POST["klinik"]) . " order by grup_item_nama asc";
$rs = $dtaccess->Execute($sql);
$dataTipe = $dtaccess->FetchAll($rs);
//echo $sql;    

$tipe[] = $view->RenderOption("", "[- Pilih Semua Tipe -]", $show);
for ($i = 0, $n = count($dataTipe); $i < $n; $i++) {
  unset($show);
  if ($_POST["grup_item_id"] == $dataTipe[$i]["grup_item_id"]) $show = "selected";
  $tipe[] = $view->RenderOption($dataTipe[$i]["grup_item_id"], $dataTipe[$i]["grup_item_nama"], $show);
}

if ($_POST["klinik"]) {
  //Data Klinik
  if ($depLowest == 'n') {
    $sql = "select * from global.global_departemen order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
  } else {
    $sql = "select * from global.global_departemen where dep_id = '" . $_POST["klinik"] . "' order by dep_id";
    $rs = $dtaccess->Execute($sql);
    $dataKlinik = $dtaccess->FetchAll($rs);
  }
} else {
  $sql = "select * from global.global_departemen order by dep_id";
  $rs = $dtaccess->Execute($sql);
  $dataKlinik = $dtaccess->FetchAll($rs);
}

//-- bikin combo box untuk jenis item --//
$sql = "select * from global.global_jenis_pasien where jenis_flag = 'y'  order by jenis_id asc";
$rs = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);

unset($opt_jenis);
$i = 1;
$opt_jenis[0] = $view->RenderOption("--", "[Pilih Jenis]", $show);
while ($data_jenis = $dtaccess->Fetch($rs)) {
  unset($show);
  if ($data_jenis["jenis_id"] == $_POST["id_jenis"]) $show = "selected";
  $opt_jenis[$i] = $view->RenderOption($data_jenis["jenis_id"], $data_jenis["jenis_nama"], $show);
  $i++;
}
/* 
    $sql = "select item_id, item_berlaku from logistik.logistik_item where id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $dataItem = $dtaccess->FetchAll($rs);
    for($i=0,$n=count($dataItem);$i<$n;$i++) {
    
    $hasil = explode("-", $dataItem[$i]["item_berlaku"]);
    $update = "0".$hasil[0]."-".$hasil[1];

        if(strlen($hasil)=="5") {
             $coba = ($hasil[0]."-".$hasil[1]);
             //echo $coba;
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,$coba)." where item_id=".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"]);
          $dtaccess->Execute($sql);
        } else if(strlen($hasil)=="4") {
             $cobadE = ("05"."-".$hasil[1]);
             //echo $coba;
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,$cobadE)." where item_id=".QuoteValue(DPE_CHAR,$dataItem[$i]["item_id"]);
          $dtaccess->Execute($sql);
        }
    
    }    
    
   
          $sql = "update logistik.logistik_item set item_berlaku =".QuoteValue(DPE_CHAR,"01-2010");
          $dtaccess->Execute($sql);
    */
if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=report_setup_barang.xls');
}

?>
<!DOCTYPE html>
<html lang="en">
<?php if (!$_POST["btnExcel"]) { ?>
  <?php require_once($LAY . "header.php") ?>
  <script language="JavaScript">
    function CheckDel(frm) {
      if (confirm("Semua transaksi yang terdapat barang tersebut akan dihapus, Apakah anda yakin ingin menghapus barang?") == 1) {
        document.frmView.submit();
      } else {
        return false;
      }
    }
    /*  function reklinik(kliniks) {
       document.location.href='item_view.php?klinik='+kliniks+'&currentPage=<?php echo $_GET["currentPage"]; ?>&recPerPage=<?php echo $_GET["recPerPage"]; ?>';
      }  */

    function rejenis(jenis) {
      document.location.href = 'item_view.php?klinik=' + jenis + '&currentPage=<?php echo $_GET["currentPage"]; ?>&recPerPage=<?php echo $_GET["recPerPage"]; ?>';
    }
  </script>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY . "sidebar.php") ?>
        <!-- top navigation -->
        <?php require_once($LAY . "topnav.php") ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">
                <h3>Apotik</h3>
              </div>
            </div>
            <div class="clearfix"></div>
            <!-- row filter -->
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Setup Barang</h2>
                    <div class="clearfix"></div>
                    <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  </div>
                  <div class="x_content">

                    <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori Barang</label>
                        <select class="form-control" name="grup_item_id" onchange="this.form.submit();">
                          <option value="">[- Tipe Item -]</option>
                          <?php for ($i = 0, $n = count($dataTipe); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataTipe[$i]["grup_item_id"]; ?>" <?php if ($_POST["grup_item_id"] == $dataTipe[$i]["grup_item_id"]) echo "selected"; ?>><?php echo $dataTipe[$i]["grup_item_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Barang</label>
                        <?php echo $view->RenderTextBox("_nama", "_nama", 40, 200, $_POST["_nama"], false, false); ?>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Cara Bayar</label>
                        <select class="form-control" name="id_jenis">
                          <option value="">[- Pilih Cara Bayar -]</option>
                          <?php for ($i = 0, $n = count($dataJenis); $i < $n; $i++) { ?>
                            <option value="<?php echo $dataJenis[$i]["jenis_id"]; ?>" <?php if ($_POST["id_jenis"] == $dataJenis[$i]["jenis_id"]) echo "selected"; ?>><?php echo $dataJenis[$i]["jenis_nama"]; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Barang</label>
                        <select class="form-control" name="obat_flag" id="obat_flag">
                          <option value="">[- Semua -]</option>
                          <option value="g" <?php if ($_POST["obat_flag"] == 'g') echo "selected"; ?>>Generik</option>
                          <option value="t" <?php if ($_POST["obat_flag"] == 't') echo "selected"; ?>>Non Generik</option>
                          <option value="a" <?php if ($_POST["obat_flag"] == 'a') echo "selected"; ?>>Alat Kesehatan</option>
                        </select>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Status Barang</label>
                        <select class="form-control" name="item_aktif" id="item_aktif">
                          <option value="">[- Status Item -]</option>
                          <option value="y" <?php if ($_POST["item_aktif"] == 'y') echo "selected"; ?>>Aktif</option>
                          <option value="n" <?php if ($_POST["item_aktif"] == 'n') echo "selected"; ?>>Tidak</option>
                        </select>
                      </div>

                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <input type="submit" name="btnLanjut" value="Lanjut" id="button" class="btn btn-success">
                        <input type="button" name="btnAdd" value="Tambah" id="button" class="btn btn-primary" onClick="document.location.href='<?php echo $addPage; ?>'">
                        <input type="submit" name="btnExcel" value="Export Excel" class="btn btn-default">
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
                  <?php } ?>
                  <table id="datatable-responsive" border="1" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                          <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI]; ?> </th>
                        <? } ?>
                      </tr>
                    </thead>
                    <tbody>
                      <? for ($i = 0, $n = count($dataTable); $i < $n; $i++) {   ?>

                        <tr class="even pointer">
                          <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI] ?></td>
                          <? } ?>

                        </tr>
                      <? } ?>
                    </tbody>
                    <?php if (!$_POST["btnExcel"]) { ?>
                  </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <?php require_once($LAY . "footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <?php require_once($LAY . "js.php") ?>
  </body>
<?php } ?>

</html>