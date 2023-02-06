<?php
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."bit.php");
  require_once($LIB."encrypt.php");
  require_once($LIB."datamodel.php");
  require_once($LIB."dateLib.php");
  require_once($LIB."tampilan.php");   

  $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
  $dtaccess = new DataAccess();
  $enc = new textEncrypt();
  $auth = new CAuth();  

  $depId = $auth->GetDepId();
  $userName = $auth->GetUserName();
  $depNama = $auth->GetDepNama();

  $delimiter = $_POST['delimiter'];
  $startLine = 0;
  
  if($_POST["csvFile"]) $csvFile = $_POST["csvFile"];
  else $csvFile = $ROOT."temp/";
  $plx = new expAJAX();

  /* KLIK IMPORT */
    if(isset($_POST["btnImport"])){

      $err = ($_FILES["csv_file"]["tmp_name"]) ? false : true;

      if(!$err){
        if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
          $csvFile .= $_FILES["csv_file"]["name"];
          copy($_FILES["csv_file"]["tmp_name"], $csvFile);
        }
      }

      if ((!$myFile = @fopen(stripslashes($csvFile), "r")) || $err==true) {
        $err = true;
      } else {
        $baris=1;
        while ($data = fgetcsv($myFile, 500000, $delimiter))  {
          if($data[0]) {
            $dbTable = "temp.hris_struktural";

            $dbField[] = "struk_id";   // PK
            $dbField[] = "struk_nama";
            $dbField[] = "struk_kode";
            $dbField[] = "struk_induk";

            $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
            $dbValue[] = QuoteValue(DPE_CHAR, $data[0]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[1]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[2]);

            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

            $dtmodel->Insert() or die("insert error");

            unset($dtmodel); unset($dbValue); unset($dbKey);
            $baris++;
          } else {
            $error[] = $startLine;
          }
          $startLine ++;
        }

        /* STRUK INDUK */
          $sql = "SELECT * FROM temp.hris_struktural WHERE struk_induk = '' ORDER BY struk_kode ASC";
          $dataStrukInduk = $dtaccess->FetchAll($sql);

          $sql = "SELECT MAX(struk_tree) AS max FROM hris.hris_struktural WHERE LENGTH(struk_tree) = 2";
          $dataMax = $dtaccess->Fetch($sql);

          foreach ($dataStrukInduk as $key => $value) {
            $sql = "UPDATE temp.hris_struktural SET struk_tree = ".QuoteValue(DPE_CHAR, sprintf("%02d", ($dataMax['max']+1)))." WHERE struk_id = ".QuoteValue(DPE_CHAR, $value['struk_id']);
            $dtaccess->Execute($sql);
            
            $sql = "UPDATE temp.hris_struktural SET struk_is_lowest = 'y' WHERE struk_id = ".QuoteValue(DPE_CHAR, $value['struk_id'])." AND ".QuoteValue(DPE_CHAR, $value['struk_kode'])." NOT IN (SELECT struk_induk FROM temp.hris_struktural)";
            $dtaccess->Execute($sql);
          }
        /* STRUK INDUK */

        /* STRUK */
          $sql_gl = "SELECT * FROM temp.hris_struktural WHERE struk_induk != '' ORDER BY struk_kode ASC";
          $dataGL = $dtaccess->FetchAll($sql_gl);

          foreach ($dataGL as $k => $val) {
            $sql_struk = "SELECT * FROM temp.hris_struktural WHERE struk_kode = ".QuoteValue(DPE_CHAR, $val['struk_induk']);
            $dataStruk = $dtaccess->Fetch($sql_struk);
            
            $sql_anak = "SELECT COUNT(*) AS total FROM temp.hris_struktural WHERE struk_induk = ".QuoteValue(DPE_CHAR, $val['struk_kode']);
            $dataAnak = $dtaccess->Fetch($sql_anak);

            $total[$val['struk_induk']][] = $val['struk_kode'];
            $anak = ($dataAnak['total'] == 0) ? 'y' : 'n';

            $sql = "UPDATE temp.hris_struktural SET struk_is_lowest = ".QuoteValue(DPE_CHAR, $anak).", struk_tree = ".QuoteValue(DPE_CHAR, $dataStruk['struk_tree'].sprintf("%02d", COUNT($total[$val['struk_induk']])))." WHERE struk_id = ".QuoteValue(DPE_CHAR, $val['struk_id']);
            $dtaccess->Execute($sql);
          }
        /* STRUK */

        $sql = "SELECT * FROM temp.hris_struktural ORDER BY struk_kode ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO hris.hris_struktural SELECT struk_id, struk_nama, struk_tree, struk_is_lowest, struk_kode, struk_tipe, struk_jenjang, struk_kode_ps, struk_tgl_sk_dikti, struk_status, id_jenjang, struk_akreditasi, struk_matakuliah_nama, struk_kode_dikti, struk_status_akreditasi, struk_kode_sp FROM temp.hris_struktural WHERE struk_kode NOT IN (SELECT struk_kode FROM hris.hris_struktural)";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.hris_struktural";
      $dtaccess->Execute($sql);

      header("Location: dep_view.php");
      exit();
    }
  /* KLIK APPROVE */
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <?php require_once($LAY."topnav.php") ?>
        <!-- Konten -->
          <div class="right_col" role="main">
            <div class="">
              <div class="clearfix"></div>
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="x_panel">
                    <div class="x_title">
                      <h2>Import Satker</h2>
                      <div class="clearfix"></div>
                    </div>
                    <!-- Sebelum Import -->
                      <div class="x_content">
                        <!-- Form -->
                          <form name="form_import" method="POST" action="" enctype="multipart/form-data">
                            <table class="table table-bordered">
                              <tr>
                                <td>Delimiter</td>
                                <td>
                                  <select class="form-control" name="delimiter" required>
                                    <option value="">Pilih Delimeter</option>
                                    <option value=",">Koma (,)</option>
                                    <option value=";">Titik Koma (;)</option>
                                  </select>
                                </td>
                              </tr>
                              <tr>
                                <td>File</td>
                                <td><input type="file" name="csv_file"></td>
                              </tr>
                              <tr>
                                <td colspan="2" align="right">
                                  <input type="submit" name="btnImport" class="btn btn-success" value="Import">
                                  <a href="dep_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Kode Satker <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Nama Satker <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Induk Satker <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_satker.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Kode Satker</td>
                            <td>Nama Satker</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['struk_kode'] ?></td>
                                <td><?= $value['struk_nama'] ?></td>
                              </tr>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </table>
                        <form name="frmEdit" method="POST" action="" enctype="multipart/form-data">
                          <input type="submit" name="btnApprove" class="btn btn-success" value="Approve">
                        </form>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <!-- Konten -->
        <!-- footer content -->
        <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <?php require_once($LAY."js.php") ?>
  </body>
</html>