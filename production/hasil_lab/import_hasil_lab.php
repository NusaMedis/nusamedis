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
            $sql_biaya = "SELECT * FROM klinik.klinik_biaya WHERE UPPER(biaya_nama) = ".QuoteValue(DPE_CHAR, str_replace('﻿', '', strtoupper($data[0])));
            $dataBiaya = $dtaccess->Fetch($sql_biaya);
            
            $level = $data[6];
            
            if ($level == '1'){
              $sql = "SELECT MAX(hasil_lab_kode) AS max FROM temp.klinik_hasil_lab WHERE LENGTH(hasil_lab_kode) = 4 AND id_biaya != ".QuoteValue(DPE_CHAR, $dataBiaya['biaya_id']);
              $dataHasilLab = $dtaccess->Fetch($sql);
              
              $kode = sprintf("%04d", ($dataHasilLab['max']+1));
            } else {
              $level_anak = ($level*2)+2;

              $sql = "SELECT MAX(hasil_lab_kode) AS max FROM temp.klinik_hasil_lab WHERE LENGTH(hasil_lab_kode) = ".QuoteValue(DPE_CHAR, ($level*2))." AND id_biaya = ".QuoteValue(DPE_CHAR, $dataBiaya['biaya_id'])." AND hasil_lab_jenis_kelamin = ".QuoteValue(DPE_CHAR, $data[1])." AND hasil_lab_batas_umur_awal = ".QuoteValue(DPE_CHAR, $data[2])." AND hasil_lab_batas_umur_akhir = ".QuoteValue(DPE_CHAR, $data[3]);
              $dataHasilLab = $dtaccess->Fetch($sql);
              
              $sql = "SELECT MAX(hasil_lab_kode) AS max FROM temp.klinik_hasil_lab WHERE LENGTH(hasil_lab_kode) = ".QuoteValue(DPE_CHAR, $level_anak)." AND  hasil_lab_kode != ".QuoteValue(DPE_CHAR, $dataHasilLab['max'])." AND hasil_lab_kode LIKE ".QuoteValue(DPE_CHAR, $dataHasilLab['max'].'%')." AND id_biaya = ".QuoteValue(DPE_CHAR, $dataBiaya['biaya_id'])." AND hasil_lab_jenis_kelamin = ".QuoteValue(DPE_CHAR, $data[1])." AND hasil_lab_batas_umur_awal = ".QuoteValue(DPE_CHAR, $data[2])." AND hasil_lab_batas_umur_akhir = ".QuoteValue(DPE_CHAR, $data[3]);
              $dataHasilLabAnak = $dtaccess->Fetch($sql);
              
              $kode = ($dataHasilLabAnak['max']) ? sprintf("%0".$level_anak."d", ($dataHasilLabAnak['max']+1)) : $dataHasilLab['max'].''.sprintf("%02d", ($dataHasilLabAnak['max']+1));
            }

            $is_anak = '';

            $dbTable = "temp.klinik_hasil_lab";

            $dbField[] = "hasil_lab_id";   // PK
            $dbField[] = "hasil_lab_nama";
            $dbField[] = "hasil_lab_kode";
            $dbField[] = "hasil_lab_keterangan";
            $dbField[] = "id_dep";
            $dbField[] = "hasil_lab_is_lowest";
            $dbField[] = "id_biaya";
            $dbField[] = "hasil_lab_batas_bawah";
            $dbField[] = "hasil_lab_batas_atas";
            $dbField[] = "is_number";
            $dbField[] = "hasil_lab_batas_umur_awal";
            $dbField[] = "hasil_lab_batas_umur_akhir";
            $dbField[] = "hasil_lab_jenis_kelamin";

            $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
            $dbValue[] = QuoteValue(DPE_CHAR, $data[4]);
            $dbValue[] = QuoteValue(DPE_CHAR, $kode);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[5]);
            $dbValue[] = QuoteValue(DPE_CHAR, $depId);
            $dbValue[] = QuoteValue(DPE_CHAR, $is_anak);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataBiaya['biaya_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[8]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[9]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[7]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[2]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[3]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[1]);

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

        $sql_table = "SELECT * FROM temp.klinik_hasil_lab ORDER BY id_biaya ASC, hasil_lab_jenis_kelamin ASC, hasil_lab_batas_umur_awal ASC, hasil_lab_batas_umur_akhir ASC, hasil_lab_kode ASC";
        $dataTable = $dtaccess->FetchAll($sql_table);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO klinik.klinik_hasil_lab SELECT * FROM temp.klinik_hasil_lab";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_hasil_lab";
      $dtaccess->Execute($sql);

      header("Location: hasil_lab_view.php");
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
                      <h2>Import Hasil Lab</h2>
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
                                  <a href="hasil_lab_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Tindakan (Lihat Manajemen => Tarif => Master Tindakan) <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Jenis Kelamin (L / P) <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Batas Umur Awal (THN) <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Batas Umur Akhir (THN) <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Nama Hasil Lab <br />
                            &nbsp;&nbsp;Kolom 6 &nbsp;&nbsp;: Nilai Normal <br />
                            &nbsp;&nbsp;Kolom 7 &nbsp;&nbsp;: Level (1, 2, 3, 4, dst) <br />
                            &nbsp;&nbsp;Kolom 8 &nbsp;&nbsp;: Is Number (y / n) <br />
                            &nbsp;&nbsp;Kolom 9 &nbsp;&nbsp;: Hasil Lab Batas Bawah <br />
                            &nbsp;&nbsp;Kolom 10 : Hasil Lab Batas Atas <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_hasil_lab.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Nama Hasil Lab</td>
                            <td>Nilai Normal</td>
                            <td>Jenis Kelamin</td>
                            <td>Batas Umur Awal</td>
                            <td>Batas Umur Akhir</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <?php
                                $sql = "SELECT * FROM temp.klinik_hasil_lab WHERE hasil_lab_kode != ".QuoteValue(DPE_CHAR, $value['hasil_lab_kode'])." AND hasil_lab_kode LIKE ".QuoteValue(DPE_CHAR, $value['hasil_lab_kode'].'%')." AND id_biaya = ".QuoteValue(DPE_CHAR, $value['id_biaya'])." AND hasil_lab_jenis_kelamin = ".QuoteValue(DPE_CHAR, $value['hasil_lab_jenis_kelamin'])." AND hasil_lab_batas_umur_awal = ".QuoteValue(DPE_CHAR, $value['hasil_lab_batas_umur_awal'])." AND hasil_lab_batas_umur_akhir = ".QuoteValue(DPE_CHAR, $value['hasil_lab_batas_umur_akhir']);
                                $dataLowest = $dtaccess->Fetch($sql);

                                if ($dataLowest != NULL) $sql = "UPDATE temp.klinik_hasil_lab SET hasil_lab_is_lowest = 'n' WHERE hasil_lab_id = ".QuoteValue(DPE_CHAR, $dataLowest['hasil_lab_id']);
                                else $sql = "UPDATE temp.klinik_hasil_lab SET hasil_lab_is_lowest = 'y' WHERE hasil_lab_id = ".QuoteValue(DPE_CHAR, $dataLowest['hasil_lab_id']);
                                $dtaccess->Execute($sql);
                              ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['hasil_lab_nama'] ?></td>
                                <td><?= $value['hasil_lab_keterangan'] ?></td>
                                <td><?= $value['hasil_lab_jenis_kelamin'] ?></td>
                                <td><?= $value['hasil_lab_batas_umur_awal'] ?></td>
                                <td><?= $value['hasil_lab_batas_umur_akhir'] ?></td>
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