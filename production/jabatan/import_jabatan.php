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
            if (strtoupper($data[1]) == 'DOKTER') $jabatan = 'D';
            else if (strtoupper($data[1]) == 'PPDS') $jabatan = 'PD';
            else if (strtoupper($data[1]) == 'PERAWAT') $jabatan = 'P';
            else if (strtoupper($data[1]) == 'STAFF') $jabatan = 'S';
            else if (strtoupper($data[1]) == 'ANALIS LAB') $jabatan = 'A';
            else if (strtoupper($data[1]) == 'RADIOGRAFER') $jabatan = 'R';
            else if (strtoupper($data[1]) == 'FISIOTERAPI') $jabatan = 'F';
            else if (strtoupper($data[1]) == 'ANASTESIS') $jabatan = 'AN';
            else if (strtoupper($data[1]) == 'PETUGAS IPJ') $jabatan = 'IP';
            else if (strtoupper($data[1]) == 'BIDAN') $jabatan = 'B';
            else $jabatan = NULL;

            $dbTable = "temp.global_auth_role";

            $dbField[] = "rol_id";   // PK
            $dbField[] = "rol_name";
            $dbField[] = "id_app";
            $dbField[] = "id_dep";
            $dbField[] = "rol_jabatan";

            $dbValue[] = QuoteValue(DPE_CHAR, date('ymdhis')+rand());
            $dbValue[] = QuoteValue(DPE_CHAR, $data[0]);
            $dbValue[] = QuoteValue(DPE_CHAR, 10);
            $dbValue[] = QuoteValue(DPE_CHAR, $depId);
            $dbValue[] = QuoteValue(DPE_CHAR, $jabatan);
            
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

        $sql = "SELECT * FROM temp.global_auth_role ORDER BY rol_name ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO global.global_auth_role SELECT * FROM temp.global_auth_role WHERE rol_name NOT IN (SELECT rol_name FROM global.global_auth_role)";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.global_auth_role";
      $dtaccess->Execute($sql);

      header("Location: role_view.php");
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
                      <h2>Import Jabatan</h2>
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
                                  <a href="role_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Jabatan <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Jabatan Fungsional (Dokter, PPDS, Perawat, Staff, Analis Lab, Radiografer, Fisioterapis, Anastesis, Petugas IPJ, Bidan) <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_jabatan.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Nama Jabatan</td>
                            <td>Jabatan Fungsional</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['rol_name'] ?></td>
                                <td><?= $value['rol_jabatan'] ?></td>
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