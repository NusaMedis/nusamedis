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
require_once($LIB . "tree.php");

$view = new CView($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$err_code = 0;
$tree = new CTree("klinik.klinik_hasil_lab", "hasil_lab_kode", TREE_LENGTH_CHILD);
//$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();

if (isset($_GET["id_biaya"])) $id_biaya = ($_GET["id_biaya"]);


$thisPage = "hasil_lab_detail_edit.php";


/*if(!$auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_master_satuan_kerja",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */
//echo $_GET["parent"]."<br>".($_GET["parent"]);
if ($_POST["x_mode"]) $_x_mode = &$_POST["x_mode"];
else $_x_mode = "New";

if (isset($_GET["parent"])) $parentId = ($_GET["parent"]);
if ($_POST["parent_id"])  $parentId = &$_POST["parent_id"];
if ($_POST["hasil_lab_id"])  $biayaId = &$_POST["hasil_lab_id"];
if ($_POST["id_biaya"])  $id_biaya = &$_POST["id_biaya"];
if ($_GET["id"]) {
  if ($_POST["btnDelete"]) {
    $_x_mode = "Delete";
  } else {
    $_x_mode = "Edit";
    $biayaId = ($_GET["id"]);
  }

  $sql = "select * from klinik.klinik_hasil_lab where hasil_lab_id = " . QuoteValue(DPE_CHAR, $biayaId);
  $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);
  $view->CreatePost($row_edit);
  $kembali = "hasil_lab_detail_view.php?id=" . ($id_biaya);
}

if ($_GET["parent"]) {
  $sql = "select hasil_lab_nama as hasil_lab_nama_parent,hasil_lab_kode as hasil_lab_kode_parent from 
        klinik.klinik_hasil_lab where id_biaya='" . $_GET['id_biaya'] . "' and hasil_lab_kode = " . QuoteValue(DPE_CHAR, $parentId);
  $rs_edit = $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
  $row_edit = $dtaccess->Fetch($rs_edit);
  $dtaccess->Clear($rs_edit);
  $view->CreatePost($row_edit);
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
    $biayaId = &$_POST["hasil_lab_id"];
    $id_biaya = &$_POST["id_biaya"];
    $_x_mode = "Edit";
  }

  if ($err_code == 0) {
    $dbTable = "klinik.klinik_hasil_lab";

    $dbField[0] = "hasil_lab_id";   // PK
    $dbField[1] = "hasil_lab_nama";
    $dbField[2] = "hasil_lab_keterangan";
    $dbField[3] = "id_biaya";
    if ($_POST["btnSave"] || $_POST["btnUpdateLagi"]) {
      $dbField[4] = "hasil_lab_kode";
    }

    if (!$biayaId) $biayaId = $dtaccess->GetTransID();
    $_POST["hasil_lab_kode"] = $tree->AddChild($parentId);

    $dbValue[0] = QuoteValue(DPE_CHAR, $biayaId);
    $dbValue[1] = QuoteValue(DPE_CHAR, $_POST["lab_nama"]);
    $dbValue[2] = QuoteValue(DPE_CHAR, $_POST["lab_keterangan"]);
    $dbValue[3] = QuoteValue(DPE_CHAR, $id_biaya);
    if ($_POST["btnSave"] || $_POST["btnUpdateLagi"]) {
      $dbValue[4] = QuoteValue(DPE_CHAR, $_POST["hasil_lab_kode"]);
    }
    //print_r($dbValue); die();
    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
    $dtmodel = new DataModel($dbTable, $dbField, $dbValue, $dbKey, DB_SCHEMA_GL);

    if ($_POST["btnSave"] || $_POST["btnUpdateLagi"]) {
      $dtmodel->Insert() or die("insert  error");
    } else if ($_POST["btnUpdate"]) {
      $dtmodel->Update() or die("update  error");
    }

    unset($dtmodel);
    unset($dbField);
    unset($dbValue);
    unset($dbKey);

    if ($parentId) {
      $sql = "update klinik.klinik_hasil_lab set hasil_lab_is_lowest = 'n' 
                        where hasil_lab_kode = " . QuoteValue(DPE_CHAR, $parentId);
      $dtaccess->Execute($sql, DB_SCHEMA_GLOBAL);
    }

    $backPage = "hasil_lab_detail_view.php?id=" . ($id_biaya);

    header("location:" . $backPage);
    exit();
  }
}


if ($_GET["del"]) {

  $Id = ($_GET["id"]);
  $IdBiaya = ($_GET["id_biaya"]);

  $sql = "select hasil_lab_id,hasil_lab_kode from klinik.klinik_hasil_lab where hasil_lab_id = " . QuoteValue(DPE_CHAR, $Id);
  $rs = $dtaccess->Execute($sql);
  $parent = $dtaccess->Fetch($rs);


  $sql = "delete from klinik.klinik_hasil_lab where id_biaya='" . $IdBiaya . "' and hasil_lab_kode like " . QuoteValue(DPE_CHAR, $parent["hasil_lab_kode"] . "%");
  $dtaccess->Execute($sql);


  $sql = "delete from klinik.klinik_hasil_lab 
           where hasil_lab_id = " . QuoteValue(DPE_CHAR, $Id);
  $dtaccess->Execute($sql);
  $backPage = "hasil_lab_detail_view.php?id=" . ($id_biaya);

  header("location:" . $backPage);
  exit();
}
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
              <h3>Laboratorium</h3>
            </div>
          </div>

          <div class="clearfix"></div>

          <div class="row">

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="x_panel">
                <div class="x_title">
                  <h2>Master Hasil Lab</h2>
                  <?php if ($parentId) { ?>
                    <h2>&nbsp;Anak dari : <?php echo $_POST["hasil_lab_nama_parent"]; ?> Kode : <?php echo $_POST["hasil_lab_kode_parent"]; ?></h2>
                  <? } ?>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                  <form id="frmEdit" method="POST" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"] ?>">

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Hasil lab</label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <input type="text" name="lab_nama" class="form-control col-md-7 col-xs-12" id="lab_nama" maxlength="255" value="<?php echo $_POST["hasil_lab_nama"]; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Nilai Normal</label>
                      <div class="col-md-5 col-sm-5 col-xs-12">
                        <input type="text" name="lab_keterangan" class="form-control col-md-7 col-xs-12" id="lab_keterangan" value="<?php echo $_POST["hasil_lab_keterangan"]; ?>">
                      </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button class="btn btn-danger" type="button" onClick="window.history.back()">Kembali</button>
                        <button id="<? if ($_x_mode == "Edit") echo "btnUpdate";
                                    else echo "btnSave"; ?>" name="<? if ($_x_mode == "Edit") echo "btnUpdate";
                                                                    else echo "btnSave"; ?>" type="submit" value="<? if ($_x_mode == "Edit") echo "Update";
                                                                                                                  else echo "Simpan"; ?>" class="btn btn-success"><? if ($_x_mode == "Edit") echo "Update";
                                                                                                                                                                  else echo "Simpan"; ?></button>
                      </div>
                    </div>
                    <?php echo $view->RenderHidden("parent_id", "parent_id", $parentId); ?>
                    <?php echo $view->RenderHidden("hasil_lab_id", "hasil_lab_id", $biayaId); ?>
                    <?php echo $view->RenderHidden("id_biaya", "id_biaya", $id_biaya); ?>
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