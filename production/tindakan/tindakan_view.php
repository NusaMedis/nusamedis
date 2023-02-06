<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");
require_once($LIB . "expAJAX.php");
require_once($LIB . "tampilan.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$depId = $auth->GetDepId();
$depLowest = $auth->GetDepLowest();
$table = new InoTable("table1", "100%", "left", null, 1, 2, 1, null);
$PageJenisBiaya = "page_jenis_biaya.php";

$tahunTarif = $auth->GetTahunTarif();
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();

if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}
$backPage = "tindakan_view.php";
$detailPage = "tindakan_detail_view.php?id_kategori_tindakan_header_instalasi=" . $_POST["id_kategori_tindakan_header_instalasi"] . "&id_kategori_tindakan_header=" . $_POST["id_kategori_tindakan_header"] . "&biaya_kategori=" . $_POST["biaya_kategori"];


$isAllowedCreate = 1;
$isAllowedUpdate = 1;
$isAllowedDel = 1;


if ($_POST["id_kategori_tindakan_header_instalasi"]) {
  $idKategoriTindakanHeaderInstalasi = $_POST["id_kategori_tindakan_header_instalasi"];
  $_POST["id_kategori_tindakan_header_instalasi"] = $_POST["id_kategori_tindakan_header_instalasi"];
}

if ($_GET["id_kategori_tindakan_header_instalasi"]) {
  $idKategoriTindakanHeaderInstalasi = $_GET["id_kategori_tindakan_header_instalasi"];
  $_POST["id_kategori_tindakan_header_instalasi"] = $_GET["id_kategori_tindakan_header_instalasi"];
}

if ($_POST["id_kategori_tindakan_header"]) {
  $idKategoriTindakanHeader = $_POST["id_kategori_tindakan_header"];
  $_POST["id_kategori_tindakan_header"] = $_POST["id_kategori_tindakan_header"];
}

if ($_GET["id_kategori_tindakan_header"]) {
  $idKategoriTindakanHeader = $_GET["id_kategori_tindakan_header"];
  $_POST["id_kategori_tindakan_header"] = $_GET["id_kategori_tindakan_header"];
}

if ($_POST["biaya_kategori"]) {
  $idKategori = $_POST["biaya_kategori"];
  $_POST["biaya_kategori"] = $_POST["biaya_kategori"];
}

if ($_GET["biaya_kategori"]) {
  $idKategori = $_GET["biaya_kategori"];
  $_POST["biaya_kategori"] = $_GET["biaya_kategori"];
}

if ($_POST["biaya_jenis"]) {
  $biayaJenis = $_POST["biaya_jenis"];
  $_POST["biaya_jenis"] = $_POST["biaya_jenis"];
}

if ($_GET["biaya_jenis"]) {
  $biayaJenis = $_GET["biaya_jenis"];
  $_POST["biaya_jenis"] = $_GET["biaya_jenis"];
}

if ($_POST["biaya_jenis_sem"]) {
  $biayaJenisSem = $_POST["biaya_jenis_sem"];
  $_POST["biaya_jenis_sem"] = $_POST["biaya_jenis_sem"];
}

if ($_GET["biaya_jenis_sem"]) {
  $biayaJenisSem = $_GET["biaya_jenis_sem"];
  $_POST["biaya_jenis_sem"] = $_GET["biaya_jenis_sem"];
}

$excel = $_POST["btnExcel"];
$cetak = $_POST["btnCetak"];

$addPage = "tindakan_edit.php?id_kategori_tindakan_header_instalasi=" . $_POST["id_kategori_tindakan_header_instalasi"] . "&id_kategori_tindakan_header=" . $_POST["id_kategori_tindakan_header"] . "&biaya_kategori=" . $_POST["biaya_kategori"] . "&tambah=1";
$editPage = "tindakan_edit.php?id_kategori_tindakan_header_instalasi=" . $_POST["id_kategori_tindakan_header_instalasi"] . "&id_kategori_tindakan_header=" . $_POST["id_kategori_tindakan_header"] . "&biaya_kategori=" . $_POST["biaya_kategori"];
$thisPage = "tindakan_view.php";

$tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="pull-right btn btn-primary" onClick="document.location.href=\'' . $addPage . '\'"></button>';

$sql_where[] = "1=1";
//if($in_nama) $sql_where[] = "UPPER(biaya_nama) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_nama."%"));
//$sql_where[] = " a.id_dep = ".QuoteValue(DPE_CHAR,$depId);
//if($in_kode) $sql_where[] = "UPPER(cust_usr_kode) like ".QuoteValue(DPE_CHAR,strtoupper("%".$in_kode."%"));
if ($idKategoriTindakanHeader && $idKategoriTindakanHeader != "--") $sql_where[] = "b.id_kategori_tindakan_header = " . QuoteValue(DPE_CHAR, $idKategoriTindakanHeader);
if ($idKategori && $idKategori != "--") $sql_where[] = "a.biaya_kategori = " . QuoteValue(DPE_CHAR, $idKategori);
if ($biayaJenis && $biayaJenis != "--") $sql_where[] = "a.biaya_jenis = " . QuoteValue(DPE_CHAR, $biayaJenis);
if ($biayaJenisSem && $biayaJenisSem != "--") $sql_where[] = "a.biaya_jenis_sem = " . QuoteValue(DPE_CHAR, $biayaJenisSem);
$sql_where = implode(" and ", $sql_where);

// QUERY PERKIRAAN NANTI DULU
//              
//            
if ($_POST["btnLanjut"] || $_POST["btnExcel"]) {

  $sql = "select a.*, b.kategori_tindakan_id, b.id_kategori_tindakan_header,b.kategori_tindakan_nama, c.dep_nama, 
              d.kegiatan_kategori_nama, 
              g.kategori_tindakan_header_nama,
              h.jenis_tindakan_nama,
              i.jenis_inacbg_nama,
              f.no_prk as no_prk_beban, 
              e.nama_prk as prk_nama_pendapatan, e.no_prk as prk_no_pendapatan, f.nama_prk as nama_prk_beban, f.no_prk as no_prk_beban
              from klinik.klinik_biaya a
              join klinik.klinik_kategori_tindakan b on b.kategori_tindakan_id = a.biaya_kategori
              left join global.global_departemen c on c.dep_id = a.id_dep
              left join klinik.klinik_kegiatan_kategori_tindakan d on d.kegiatan_kategori_id = a.id_kegiatan_kategori 
              left join klinik.klinik_kategori_tindakan_header g on b.id_kategori_tindakan_header = g.kategori_tindakan_header_id
              left join klinik.klinik_jenis_tindakan h on a.biaya_jenis_sem = h.jenis_tindakan_kode
              left join klinik.klinik_jenis_inacbg i on a.biaya_jenis = i.jenis_inacbg_id
              left join gl.gl_perkiraan e on e.id_prk = a.id_prk 
              left join gl.gl_perkiraan f on f.id_prk = a.id_prk_beban
              where " . $sql_where;
  $sql .= " order by g.kategori_tindakan_header_urut,b.kategori_urut,a.biaya_urut";
  // echo $sql;
  $rs = $dtaccess->Execute($sql);
  //$rs = $dtaccess->Query($sql,$recordPerPage,$startPage);
  $dataTable = $dtaccess->FetchAll($rs);
}

$counterHeader = 0;
$tableHeader = "Manajemen - Master Tindakan";

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Kategori Tindakan Header";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Urut";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Kode Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Nama Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "25%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Variabel INACBG";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Jenis Tindakan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Pendapatan";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Perk Beban";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "10%";
$counterHeader++;

if(!$_POST["btnExcel"]){
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "4%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "BHP";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "5%";
  $counterHeader++;
}


//TOTAL HEADER TABLE
$jumHeader = $counterHeader;

for ($i = 0, $counter = 0, $n = count($dataTable); $i < $n; $i++, $counter = 0) {

  $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["kategori_tindakan_header_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["kategori_tindakan_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["biaya_urut"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "right";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["biaya_kode_kategori"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["biaya_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["jenis_inacbg_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["jenis_tindakan_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;


  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["prk_nama_pendapatan"] . "(" . $dataTable[$i]["prk_no_pendapatan"] . ")";
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = "&nbsp;&nbsp;" . $dataTable[$i]["prk_nama_beban"] . "(" . $dataTable[$i]["prk_no_beban"] . ")";
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;


  if ($isAllowedUpdate && !$_POST["btnExcel"]) {
    $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $editPage . '&id=' . $enc->Encode($dataTable[$i]["biaya_id"]) . '"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;
  }
  

  //Ditutup dulu sementara

  if ($isAllowedDel && !$_POST["btnExcel"]) {
    $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $editPage . '&id_del=' . $dataTable[$i]["biaya_id"] . '&del=1"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;
  }
  


  if(!$_POST["btnExcel"]){
    $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $detailPage . '&biaya_id=' . $dataTable[$i]["biaya_id"] . '&id=' . $enc->Encode($dataTable[$i]["biaya_tarif_id"]) . '"><img hspace="2" width="25" height="25" src="' . $ROOT . 'gambar/icon/cari.png" alt="BHP" title="BHP" border="0"></a>';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;
  }
  
}




if ($_POST["btnExcel"]) {
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=tarif_all.xls');
}

if ($_POST["btnCetak"]) {
  $_x_mode = "cetak";
}



// Data Kategori Tindakan Header Instalasi//
$sql = "select * from  klinik.klinik_jenis_tindakan a where jenis_tindakan_flag='y'";
$sql .= " order by jenis_tindakan_nama asc";
$rs = $dtaccess->Execute($sql);
$dataJenisTindakan = $dtaccess->FetchAll($rs);

//-- bikin combo box untuk jenis INACBG --//
$sql = "select * from klinik.klinik_jenis_inacbg order by jenis_inacbg_nama";
$rs = $dtaccess->Execute($sql, DB_SCHEMA);
$dataJenisInacbg = $dtaccess->FetchAll($rs);

// Data Kategori Tindakan Header Instalasi//
$sql = "select * from  klinik.klinik_kategori_tindakan_header_instalasi a";
$sql .= " order by klinik_kategori_tindakan_header_instalasi_urut asc";
$rs = $dtaccess->Execute($sql);
$dataKategoriTindakanHeaderInstalasi = $dtaccess->FetchAll($rs);

// Data Kategori Tindakan Header //
if ($_POST['id_kategori_tindakan_header_instalasi']) $sql_where_header[] = "a.id_kategori_tindakan_header_instalasi = " . QuoteValue(DPE_CHAR, $_POST['id_kategori_tindakan_header_instalasi']);
$sql_header = "select * from  klinik.klinik_kategori_tindakan_header a where 1=1";
if ($sql_where_header) $sql_header .= " and " . implode(" and ", $sql_where_header);
$sql_header .= " order by kategori_tindakan_header_urut asc";
$rs_header = $dtaccess->Execute($sql_header);
$dataKategoriTindakanHeader = $dtaccess->FetchAll($rs_header);

// Data Kategori Tindakan Header //

if ($_POST['id_kategori_tindakan_header']) $sql_where_tindakan[] = "a.id_kategori_tindakan_header = " . QuoteValue(DPE_CHAR, $_POST['id_kategori_tindakan_header']);
$sql_where_tindakan = "select * from  klinik.klinik_kategori_tindakan";
if ($sql_where_tindakan)  $sql_where_tindakan .= " where id_kategori_tindakan_header = " . QuoteValue(DPE_CHAR, $idKategoriTindakanHeader);
$sql_where_tindakan .= " order by kategori_urut asc";
$rs_tindakan = $dtaccess->Execute($sql_where_tindakan);
$dataKategoriTindakan = $dtaccess->FetchAll($rs_tindakan);


?>



<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>

<body class="nav-md">
  <?php if(!$_POST['btnExcel']) { ?>
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
              <h3><?php echo $tableHeader; ?></h3>
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
                  <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                    <!--
				    <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">Tipe Rawat</label>
						<select name="biaya_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);" >
						    		<option class="inputField" value="" >- Pilih Tipe Rawat -</option>
				    				<option class="inputField" value="TA" <?php if ($_POST["biaya_jenis"] == "TA") echo "selected" ?>>Rawat Jalan&nbsp;</option> 
				   					 <option class="inputField" value="TI" <?php if ($_POST["biaya_jenis"] == "TI") echo "selected" ?>>Rawat Inap&nbsp;</option>
           							 <option class="inputField" value="TG" <?php if ($_POST["biaya_jenis"] == "TG") echo "selected" ?>>IGD&nbsp;</option>
				  		</select> 				  		
				    </div> -->
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header Instalasi</label>
                      <select name="id_kategori_tindakan_header_instalasi" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                        <option class="inputField" value="">- Pilih Kategori Tindakan Header Instalasi-</option>
                        <?php for ($i = 0, $n = count($dataKategoriTindakanHeaderInstalasi); $i < $n; $i++) { ?>
                          <option class="inputField" value="<?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]; ?>" <?php if ($_POST["id_kategori_tindakan_header_instalasi"] == $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_id"]) echo "selected" ?>><?php echo $dataKategoriTindakanHeaderInstalasi[$i]["klinik_kategori_tindakan_header_instalasi_nama"]; ?>&nbsp;</option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan Header</label>
                      <select name="id_kategori_tindakan_header" class="select2_single form-control" onchange="this.form.submit()" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                        <option class="inputField" value="">- Pilih Kategori Tindakan Header -</option>
                        <?php for ($i = 0, $n = count($dataKategoriTindakanHeader); $i < $n; $i++) { ?>
                          <option class="inputField" value="<?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"]; ?>" <?php if ($_POST["id_kategori_tindakan_header"] == $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_id"]) echo "selected" ?>><?php echo $dataKategoriTindakanHeader[$i]["kategori_tindakan_header_nama"]; ?>&nbsp;</option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Nama Kategori Tindakan</label>
                      <select name="biaya_kategori" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                        <option class="inputField" value="">- Pilih Kategori Tindakan -</option>
                        <?php for ($i = 0, $n = count($dataKategoriTindakan); $i < $n; $i++) { ?>
                          <option class="inputField" value="<?php echo $dataKategoriTindakan[$i]["kategori_tindakan_id"]; ?>" <?php if ($_POST["biaya_kategori"] == $dataKategoriTindakan[$i]["kategori_tindakan_id"]) echo "selected" ?>><?php echo $dataKategoriTindakan[$i]["kategori_tindakan_nama"]; ?>&nbsp;</option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Jenis Tindakan</label>
                      <select name="biaya_jenis_sem" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                        <option class="inputField" value="">- Pilih Jenis Tindakan -</option>
                        <?php for ($i = 0, $n = count($dataJenisTindakan); $i < $n; $i++) { ?>
                          <option class="inputField" value="<?php echo $dataJenisTindakan[$i]["jenis_tindakan_id"]; ?>" <?php if ($_POST["biaya_jenis_sem"] == $dataJenisTindakan[$i]["jenis_tindakan_id"]) echo "selected" ?>><?php echo $dataJenisTindakan[$i]["jenis_tindakan_nama"]; ?>&nbsp;</option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Variable INACBG</label>
                      <select name="biaya_jenis" class="select2_single form-control" onKeyDown="return tabOnEnter_select_with_button(this, event);">
                        <option class="inputField" value="">- Pilih Variable INACBG -</option>
                        <?php for ($i = 0, $n = count($dataJenisInacbg); $i < $n; $i++) { ?>
                          <option class="inputField" value="<?php echo $dataJenisInacbg[$i]["jenis_inacbg_id"]; ?>" <?php if ($_POST["biaya_jenis"] == $dataJenisInacbg[$i]["jenis_inacbg_id"]) echo "selected" ?>><?php echo $dataJenisInacbg[$i]["jenis_inacbg_nama"]; ?>&nbsp;</option>
                        <?php } ?>
                      </select>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <?=$tombolAdd?>
                      <input type="submit" name="btnLanjut" id="btnLanjut" value="   Lanjut   " class="pull-right  btn btn-primary">
                      <input type="submit" name="btnExcel" id="btnUrut" value="   Export Excel  " class="pull-right  btn btn-primary">
                    </div>

                     
				<!-- 	<div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
						
				  </div>   -->
                    <!-- DITUTUP DULU SEMENTARA UNtuk input kode bpjs
					<div class="col-md-4 col-sm-6 col-xs-12">
                  <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
            			<input type="submit" name="btnUrut" id="btnUrut" value="Urutkan Tindakan" class="pull-right col-md-12 col-sm-12 col-xs-12 btn btn-primary">
				  </div>
          -->
                    <?php if ($idKategori && $idKategoriTindakanHeader && $idKategoriTindakanHeaderInstalasi) { ?>
                      <div class="col-md-4 col-sm-6 col-xs-12">
                        <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                        <?php //echo $tombolAdd; ?>
                      </div>
                    <? } ?>
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
                <?php }?>
                  <table class="table" cellspacing="0" width="100%" <?=($_POST["btnExcel"]) ? "border='1'" : ""?>>
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
                  </table>
                  <?php if(!$_POST['btnExcel']) { ?>
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
<?php } ?>
  <?php require_once($LAY . "js.php") ?>

</body>

</html>