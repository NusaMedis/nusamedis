<?php
require_once("../penghubung.inc.php");
require_once($LIB . "bit.php");
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
$depNama = $auth->GetDepNama();
$userName = $auth->GetUserName();
$usrId = $auth->GetUserId();
$depId = $auth->GetDepId();
$auth = new CAuth();
$err_code = 0;


/*  if(!$auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_medis_setup_instalasi",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
     }*/


if ($_POST["x_mode"]) $_x_mode = &$_POST["x_mode"];
else $_x_mode = "New";

if ($_POST["margin_id"])  $marginId = &$_POST["margin_id"];

if ($_GET["id"]) {
  if ($_POST["btnDelete"]) {
    $_x_mode = "Delete";
  } else {
    $_x_mode = "Edit";
    $marginId = $enc->Decode($_GET["id"]);
  }

  $sql = "select * from apotik.apotik_margin a
             left join logistik.logistik_grup_item b on a.id_grup_item = b.grup_item_id 
				     where margin_id = " . QuoteValue(DPE_CHAR, $marginId);
  $rs_edit = $dtaccess->Execute($sql);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);

  $_POST["margin_nilai"] = $row_edit["margin_nilai"];
  $_POST["id_grup_item"] = $row_edit["id_grup_item"];
  $_POST["is_aktif"] = $row_edit["is_aktif"];
  $_POST["harga_min"] = $row_edit["harga_min"];
  $_POST["harga_max"] = $row_edit["harga_max"];
}

if ($_x_mode == "New") $privMode = PRIV_CREATE;
elseif ($_x_mode == "Edit") $privMode = PRIV_UPDATE;
else $privMode = PRIV_DELETE;

if ($_POST["btnNew"]) {
  header("location: " . $_SERVER["PHP_SELF"]);
  exit();
}

if ($_POST["btnSave"] || $_POST["btnUpdate"]) {

  if ($_POST["btnUpdate"]) {
    $marginId = &$_POST["margin_id"];
    $_x_mode = "Edit";
  }


  $dbTable = "apotik.apotik_margin";

  $dbField[0] = "margin_id";   // PK
  $dbField[1] = "margin_nilai";
  $dbField[2] = "id_grup_item";
  $dbField[3] = "is_aktif";
  $dbField[4] = "harga_min";
  $dbField[5] = "harga_max";
  if ($_POST["btnSave"]) {
    $dbField[6] = "margin_when_create";
    $dbField[7] = "margin_who_create";
  }

  if (!$marginId) $marginId = $dtaccess->GetTransId('apotik.apotik_margin', 'margin_id', DB_SCHEMA_GLOBAL);
  $dbValue[0] = QuoteValue(DPE_CHAR, $marginId);
  $dbValue[1] = QuoteValue(DPE_NUMERIC, $_POST["margin_nilai"]);
  $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["id_grup_item"]);
  $dbValue[3] = QuoteValue(DPE_CHAR, 'Y');
  $dbValue[4] = QuoteValue(DPE_NUMERIC, $_POST["harga_min"]);
  $dbValue[5] = QuoteValue(DPE_NUMERIC, $_POST["harga_max"]);
  if ($_POST["btnSave"]) {
    $dbValue[6] = QuoteValue(DPE_DATE, date('Y-m-d H:i:s'));
    $dbValue[7] = QuoteValue(DPE_CHAR, $usrId);
  }

  $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
  $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey);
  //print_r($dbValue);
  //die();
  if ($_POST["btnSave"]) {
    $dtmodel->Insert() or die("insert  error");
  } else if ($_POST["btnUpdate"]) {
    $dtmodel->Update() or die("update  error");
  }

  unset($dtmodel);
  unset($dbField);
  unset($dbValue);
  unset($dbKey);

  //non aktifkan margin dengan jenis pasien yang sama
  $sql = "update apotik.apotik_margin set is_aktif ='N'
                        where id_grup_item = " . QuoteValue(DPE_CHAR, $_POST["id_grup_item"]) . " 
                        and margin_id <> " . QuoteValue(DPE_CHAR, $marginId) . "
                        and harga_min =" . QuoteValue(DPE_NUMERIC, $_POST["harga_min"]) . " 
                        and harga_max =" . QuoteValue(DPE_NUMERIC, $_POST["harga_max"]);
  $rs = $dtaccess->Execute($sql);
  // echo $sql;die();
  header("location:margin_view.php");
  exit();
}


if ($_GET["del"]) {
  $marginId = $enc->Decode($_GET["id"]);
  //data margin edit
  $sql = "select * from apotik.apotik_margin where margin_id = " . QuoteValue(DPE_CHAR, $marginId);
  $rs = $dtaccess->Execute($sql);
  $dataMargin = $dtaccess->Fetch($rs);

  //cari margin yang mau diupdate
  $sql = "select * from apotik.apotik_margin where id_grup_item = " . QuoteValue(DPE_CHAR, $dataMargin["id_grup_item"]) . " and margin_id <> " . QuoteValue(DPE_CHAR, $marginId) . " order by margin_when_create desc";
  $rs = $dtaccess->Execute($sql);
  $dataMarginUpdate = $dtaccess->Fetch($rs);
  // aktifkan margin terakhir dengan jenis pasien yang sama
  $sql = "update apotik.apotik_margin set is_aktif ='y' where margin_id = " . QuoteValue(DPE_CHAR, $dataMarginUpdate["margin_id"]);
  $rs = $dtaccess->Execute($sql);

  $sql = "delete from apotik.apotik_margin where margin_id = " . QuoteValue(DPE_CHAR, $marginId);
  $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);


  header("location:margin_view.php");
  exit();
}

### combo kategori barang
$sql = "select * from logistik.logistik_grup_item order by grup_item_nama asc";
$rs = $dtaccess->Execute($sql);
$kategori = $dtaccess->FetchAll($rs);


?>

<!DOCTYPE html>
<html lang="en">
<?php require_once($LAY . "header.php"); ?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">

      <?php require_once($LAY . "sidebar.php"); ?>

      <!-- top navigation -->
      <?php require_once($LAY . "topnav.php"); ?>
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

          <div class="row">

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Margin Obat</h2>
                  <span class="pull-right"><?php echo $tombolAdd; ?></span>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form id="demo-form2" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Kategori<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="select2_single form-control" name="id_grup_item" id="id_grup_item" onKeyDown="return tabOnEnter(this, event);">
                          <option value="">[Pilih Kategori]</option>
                          <?php for ($i = 0, $n = count($kategori); $i < $n; $i++) { ?>
                            <option value="<?php echo $kategori[$i]["grup_item_id"]; ?>" <?php if ($kategori[$i]["grup_item_id"] == $_POST["id_grup_item"]) echo "selected"; ?>><?php echo $kategori[$i]["grup_item_nama"]; ?></option>
                          <?php } ?>
                        </select>

                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Harga Min<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="harga_min" name="harga_min" value="<?php echo $_POST["harga_min"] ?>" required="required" class="form-control col-md-7 col-xs-12">

                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Harga Max<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="harga_max" name="harga_max" value="<?php echo $_POST["harga_max"] ?>" required="required" class="form-control col-md-7 col-xs-12">

                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Nilai Margin<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" id="margin_nilai" name="margin_nilai" value="<?php echo $_POST["margin_nilai"] ?>" required="required" class="form-control col-md-7 col-xs-12">

                      </div>
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>
                        <button id="<? if ($_x_mode == "Edit") echo "btnUpdate";
                                    else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate";
                                                                    else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update";
                                                                                                                  else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update";
                                                                                                                                                                  else echo "Simpan"; ?></button>
                      </div>
                    </div>
                    <?php echo $view->RenderHidden("margin_id", "margin_id", $marginId); ?>
                    <?php echo $view->RenderHidden("x_mode", "x_mode", $_x_mode); ?>
                  </form>
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