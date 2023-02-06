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

  $hari['SENIN'] = '1';
  $hari['SELASA'] = '2';
  $hari['RABU'] = '3';
  $hari['KAMIS'] = '4';
  $hari['JUMAT'] = '5';
  $hari['SABTU'] = '6';
  $hari['MINGGU'] = '7';

  $hari_nama['1'] = 'Senin';
  $hari_nama['2'] = 'Selasa';
  $hari_nama['3'] = 'Rabu';
  $hari_nama['4'] = 'Kamis';
  $hari_nama['5'] = 'Jumat';
  $hari_nama['6'] = 'Sabtu';
  $hari_nama['7'] = 'Minggu';

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
            /* POLI */
              $sql = "SELECT * FROM global.global_auth_poli WHERE UPPER(poli_nama) = ".QuoteValue(DPE_CHAR, strtoupper(str_replace('﻿', '', $data[0])));
              $dataPoli = $dtaccess->Fetch($sql);
            /* POLI */

            /* DOKTER */
              $sql = "SELECT * FROM global.global_auth_user WHERE UPPER(usr_name) = ".QuoteValue(DPE_CHAR, strtoupper($data[1]));
              $dataDokter = $dtaccess->Fetch($sql);
            /* DOKTER */

            $dbTable = "temp.klinik_jadwal_dokter";

            $dbField[] = "jadwal_dokter_id";   // PK
            $dbField[] = "id_instalasi";
            $dbField[] = "id_sub_instalasi";
            $dbField[] = "id_poli";
            $dbField[] = "id_dokter";
            $dbField[] = "jadwal_dokter_hari";
            $dbField[] = "jadwal_dokter_jam_mulai";
            $dbField[] = "jadwal_dokter_jam_selesai";

            $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
            $dbValue[] = QuoteValue(DPE_CHAR, $dataPoli['id_instalasi']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataPoli['id_sub_instalasi']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataPoli['poli_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataDokter['usr_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $hari[strtoupper($data[2])]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[3]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[4]);
            
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

        $sql = "SELECT * FROM temp.klinik_jadwal_dokter a LEFT JOIN global.global_auth_poli b ON a.id_poli = b.poli_id LEFT JOIN global.global_auth_user c ON a.id_dokter = c.usr_id ORDER BY poli_nama ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO klinik.klinik_jadwal_dokter SELECT * FROM temp.klinik_jadwal_dokter";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_jadwal_dokter";
      $dtaccess->Execute($sql);

      header("Location: jadwal_dokter_view.php");
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
                      <h2>Import Jadwal Dokter</h2>
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
                                  <a href="jadwal_dokter_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Poli (Lihat di Manajemen - Medis - Setup Poli) <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Nama Dokter (Lihat di Manajemen - User - User Login) <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Nama Hari (Senin, Selesa, Rabu, Kamis, Jumat, Sabtu, Minggu) <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Jam Mulai (00:00:00) <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Jam Selesai (00:00:00) <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_jadwal.csv">disini</a> <br>
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
                            <td>Nama Dokter</td>
                            <td>Hari</td>
                            <td>Jam Awal</td>
                            <td>Jam Akhir</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['poli_nama'] ?></td>
                                <td><?= $value['usr_name'] ?></td>
                                <td><?= $hari_nama[$value['jadwal_dokter_hari']] ?></td>
                                <td><?= $value['jadwal_dokter_jam_mulai'] ?></td>
                                <td><?= $value['jadwal_dokter_jam_selesai'] ?></td>
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