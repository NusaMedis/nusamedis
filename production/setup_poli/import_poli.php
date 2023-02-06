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

  $tipe['DIAGNOSTIK'] = 'D';
  $tipe['FARMASI'] = 'A';
  $tipe['ICU'] = 'C';
  $tipe['IGD'] = 'G';
  $tipe['LABORATORIUM'] = 'L';
  $tipe['OPERASI'] = 'O';
  $tipe['ORTOTIK PROSTETIK'] = 'P';
  $tipe['PEMULASARAN JENAZAH'] = 'N';
  $tipe['RADIOLOGI'] = 'R';
  $tipe['RAWAT INAP'] = 'I';
  $tipe['RAWAT JALAN'] = 'J';
  $tipe['REHAB MEDIK'] = 'M';

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
            /* SUB INSTALASI */
              $sql = "SELECT * FROM global.global_auth_sub_instalasi WHERE UPPER(sub_instalasi_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[3]));
              $dataSubInstalasi = $dtaccess->Fetch($sql);
            /* SUB INSTALASI */

            $dbTable = "temp.global_auth_poli";

            $dbField[] = "poli_id";   // PK
            $dbField[] = "poli_nama";
            $dbField[] = "poli_kode";
            $dbField[] = "poli_bpjs";
            $dbField[] = "id_instalasi";
            $dbField[] = "id_sub_instalasi";
            $dbField[] = "poli_tipe";
            $dbField[] = "id_dep";

            $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
            $dbValue[] = QuoteValue(DPE_CHAR, str_replace('﻿', '', $data[0]));
            $dbValue[] = QuoteValue(DPE_CHAR, $data[1]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[2]);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataSubInstalasi['id_instalasi']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataSubInstalasi['sub_instalasi_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $tipe[strtoupper($data[4])]);
            $dbValue[] = QuoteValue(DPE_CHAR, $depId);
            
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

        $sql = "SELECT * FROM temp.global_auth_poli a LEFT JOIN global.global_auth_sub_instalasi b ON a.id_sub_instalasi = b.sub_instalasi_id LEFT JOIN global.global_auth_instalasi c ON a.id_instalasi = c.instalasi_id ORDER BY poli_nama ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO global.global_auth_poli SELECT * FROM temp.global_auth_poli WHERE UPPER(poli_nama) NOT IN (SELECT UPPER(poli_nama) FROM global.global_auth_poli)";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.global_auth_poli";
      $dtaccess->Execute($sql);

      header("Location: jenis_poli_view.php");
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
                      <h2>Import Poli</h2>
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
                                  <a href="jenis_poli_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Poli<br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Kode Poli <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Kode Poli BPJS <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Nama Sub Instalasi (Lihat di Manajemen - Medis - Setup Sub Instalasi) <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Tipe Poli (Diagnotik, Farmasi, ICU, IGD, Laboratorium, Operasi, Orototik Prostetik, Pemulasaran Jenazah, Radiologi, Rawat Inap, Rawat Jalan, Rehab Medik) <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_poli.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Nama Poli</td>
                            <td>Kode Poli</td>
                            <td>Kode Poli BPJS</td>
                            <td>Nama Instalasi</td>
                            <td>Nama Sub Instalasi</td>
                            <td>Poli Tipe</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['poli_nama'] ?></td>
                                <td><?= $value['poli_kode'] ?></td>
                                <td><?= $value['poli_bpjs'] ?></td>
                                <td><?= $value['instalasi_nama'] ?></td>
                                <td><?= $value['sub_instalasi_nama'] ?></td>
                                <td><?= $value['poli_tipe'] ?></td>
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