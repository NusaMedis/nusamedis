<?php
require_once("../penghubung.inc.php");
require_once($LIB . "login.php");
require_once($LIB . "encrypt.php");
require_once($LIB . "datamodel.php");
require_once($LIB . "tampilan.php");
require_once($LIB . "currency.php");
require_once($LIB . "dateLib.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$table = new InoTable("table", "100%", "left");
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();

$editPage = "margin_edit.php";
$thisPage = "margin_view.php";

if (!$auth->IsAllowed("man_ganti_password", PRIV_CREATE)) {
  die("Maaf anda tidak berhak membuka halaman ini....");
  exit(1);
} else 
      if ($auth->IsAllowed("man_ganti_password", PRIV_CREATE) === 1) {
  echo "<script>window.parent.document.location.href='" . $ROOT . "login/login.php?msg=Login First'</script>";
  exit(1);
}



/* if(!$auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     } */

$isAllowedCreate = 1;
$isAllowedUpdate = 1;
$isAllowedDel = 1;

function StripArr($num)
{
  return StripCurrency($num);
}

if ($_POST["id_grup_item"]) {
  $sql_where[] = " a.id_grup_item = " . QuoteValue(DPE_CHAR, $_POST["id_grup_item"]);
}
if ($_POST["is_aktif"]) {
  $sql_where[] = " a.is_aktif = " . QuoteValue(DPE_CHAR, $_POST["is_aktif"]);
}

if ($sql_where) $sql_where = implode(" and ", $sql_where);

$sql = "select * from apotik.apotik_margin_bhp a
             left join logistik.logistik_grup_item b on a.id_grup_item = b.grup_item_id";
if ($sql_where)     $sql .= " where 1=1 and " . $sql_where;
$sql .= " order by grup_item_nama asc ";
$rs = $dtaccess->Execute($sql, DB_SCHEMA);
$dataMargin = $dtaccess->FetchAll($rs);
// echo $sql;

$table = new InoTable("table1", "100%", "left", null, 1, 2, 1, null);
$tableHeader = "&nbsp;Margin BHP";

// --- construct new table ---- //
$counter = 0;
$counterHeader = 0;

$tbHeader[0][$counterHeader][TABLE_ISI] = "No";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "2%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Kategori";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Min";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Harga Max";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "20%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Margin ( % )";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "15%";
$counterHeader++;

$tbHeader[0][$counterHeader][TABLE_ISI] = "Status";
$tbHeader[0][$counterHeader][TABLE_WIDTH] = "18%";
$counterHeader++;

if ($isAllowedUpdate) {
  $tbHeader[0][$counterHeader][TABLE_ISI] = "Edit";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  $counterHeader++;

  $tbHeader[0][$counterHeader][TABLE_ISI] = "Hapus";
  $tbHeader[0][$counterHeader][TABLE_WIDTH] = "1%";
  $counterHeader++;
}


//TOTAL HEADER TABLE
$jumHeader = $counterHeader;

for ($i = 0, $counter = 0, $n = count($dataMargin); $i < $n; $i++, $counter = 0) {

  if ($dataMargin[$i]["is_aktif"] == 'Y') {
    $status = "Aktif";
  } else {
    $status = " Non Aktif";
  }
  $tbContent[$i][$counter][TABLE_ISI] = $i + 1;
  $tbContent[$i][$counter][TABLE_ALIGN] = "center";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $dataMargin[$i]["grup_item_nama"];
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataMargin[$i]["harga_min"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataMargin[$i]["harga_max"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataMargin[$i]["margin_nilai"]);
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  $tbContent[$i][$counter][TABLE_ISI] = $status;
  $tbContent[$i][$counter][TABLE_ALIGN] = "left";
  $counter++;

  if ($isAllowedUpdate) {

    $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $editPage . '?id=' . $enc->Encode($dataMargin[$i]["margin_id"]) . '"><img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/icon/edit.png" alt="Edit" title="Edit" border="0"></a>';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;


    $tbContent[$i][$counter][TABLE_ISI] = '<a href="' . $editPage . '?id=' . $enc->Encode($dataMargin[$i]["margin_id"]) . '&del=1"><img hspace="2" width="32" height="32" src="' . $ROOT . 'gambar/icon/hapus.png" alt="Hapus" title="Hapus" border="0" onclick="javascript: return Hapus();"></a>';
    $tbContent[$i][$counter][TABLE_ALIGN] = "center";
    $counter++;
  }

  /*for($j=0,$k=count($dataSplit);$j<$k;$j++){
			$tbContent[$i][$counter][TABLE_ISI] = currency_format($_POST["txtNom"][$dataBiaya[$i]["biaya_id"]][$dataSplit[$j]["split_id"]]);
			$tbContent[$i][$counter][TABLE_ALIGN] = "center";
			$counter++;
		}
		
		      $tbContent[$i][$counter][TABLE_ISI] = $namaVisite[$dataBiaya[$i]["biaya_tambahan"]];
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;
	
		
          $tbContent[$i][$counter][TABLE_ISI] = currency_format($dataBiaya[$i]["biaya_total"]);
          $tbContent[$i][$counter][TABLE_ALIGN] = "center";
          $counter++;    */
}


//if($isAllowedUpdate) $tbBottom[0][0][TABLE_ISI] = '&nbsp;&nbsp;<input type="submit" name="btnUpdate" value="Simpan" class="button">&nbsp;';

$tbBottom[0][0][TABLE_WIDTH] = "100%";
$tbBottom[0][0][TABLE_COLSPAN] = count($tbHeader[0]);
$tbBottom[0][0][TABLE_ALIGN] = "center";
$counter++;
$tombolAdd = '<input type="button" name="btnAdd" value="Tambah" class="btn btn-primary" onClick="document.location.href=\'' . $editPage . '\'"></button>';

### combo kategori barang
$sql = "select * from logistik.logistik_grup_item order by grup_item_nama asc";
$rs = $dtaccess->Execute($sql);
$kategori = $dtaccess->FetchAll($rs);



?>
<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php") ?>

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

              <h3>Master Margin BHP</h3>
            </div>
          </div>

          <div class="clearfix"></div>

          <div class="row">

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2><?php echo $tableHeader; ?></h2>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form name="frmView" action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">
                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Kategori</label>

                      <select class="select2_single form-control" name="id_grup_item" id="id_grup_item" onKeyDown="return tabOnEnter(this, event);">
                        <option value="">[Pilih Kategori]</option>
                        <?php for ($i = 0, $n = count($kategori); $i < $n; $i++) { ?>
                          <option value="<?php echo $kategori[$i]["grup_item_id"]; ?>" <?php if ($kategori[$i]["grup_item_id"] == $_POST["id_grup_item"]) echo "selected"; ?>><?php echo $kategori[$i]["grup_item_nama"]; ?></option>
                        <?php } ?>
                      </select>

                    </div>


                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">Status</label>
                      <select class="select2_single form-control" name="is_aktif" id="is_aktif" onKeyDown="return tabOnEnter(this, event);">
                        <!--onChange="this.form.submit();" -->
                        <option value="">[ Pilih Status ]</option>
                        <option value="Y" <?php if ($_POST["is_aktif"] == 'Y') echo "selected"; ?>>Aktif</option>
                        <option value="N" <?php if ($_POST["is_aktif"] == 'N') echo "selected"; ?>>Non Aktif</option>
                      </select>

                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                      <label class="control-label col-md-12 col-sm-12 col-xs-12">&nbsp;</label>
                      <input type="submit" name="btnLanjut" value="Lanjut" class="pull-right btn btn-success">
                    </div>
                    <div class="clearfix"></div>
                  </form>
                </div>
                <div class="x_content">
                  <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                          <th class="column-title"><?php echo $tbHeader[0][$k][TABLE_ISI]; ?> </th>
                        <? } ?>
                      </tr>
                    </thead>
                    <tbody>
                      <? for ($i = 0, $n = count($dataMargin); $i < $n; $i++) {   ?>

                        <tr class="even pointer">
                          <? for ($k = 0, $l = $jumHeader; $k < $l; $k++) {  ?>
                            <td class=" "><?php echo $tbContent[$i][$k][TABLE_ISI] ?></td>
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
      <?php require_once($LAY . "footer.php") ?>
      <!-- /footer content -->
    </div>
  </div>

  <?php require_once($LAY . "js.php") ?>

</body>

</html>