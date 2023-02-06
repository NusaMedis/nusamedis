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
            /* Kelas Kamar */
              $sql = "SELECT * FROM klinik.klinik_kelas WHERE UPPER(kelas_nama) = ".QuoteValue(DPE_CHAR, strtoupper(str_replace('﻿', '', $data[0])));
              $dataKelas = $dtaccess->Fetch($sql);
            /* Kelas Kamar */

            /* Jenis Kamar */
              $sql = "SELECT * FROM klinik.klinik_jenis_kelas WHERE UPPER(jenis_kelas_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[1]));
              $dataJenisKelas = $dtaccess->Fetch($sql);
            /* Jenis Kamar */

            /* Poli */
              $sql = "SELECT * FROM global.global_auth_poli WHERE UPPER(poli_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[2]));
              $dataPoli = $dtaccess->Fetch($sql);
            /* Poli */

            /* Gedung Rawat */
              $sql = "SELECT * FROM global.global_gedung_rawat WHERE UPPER(gedung_rawat_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[3]));
              $dataGedung = $dtaccess->Fetch($sql);
            /* Gedung Rawat */

            $dbTable = "temp.klinik_kamar";

            $dbField[] = "kamar_id";   // PK
            $dbField[] = "kamar_kode";
            $dbField[] = "kamar_nama";
            $dbField[] = "id_kelas";
            $dbField[] = "id_poli";
            $dbField[] = "id_dep";
            $dbField[] = "id_gedung_rawat";
            $dbField[] = "id_jenis_kelas";

            $kamar_id = $dtaccess->GetTransID();
            $dbValue[] = QuoteValue(DPE_CHAR, $kamar_id);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[4]);
            $dbValue[] = QuoteValue(DPE_CHAR, $data[5]);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataKelas['kelas_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataPoli['poli_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $depId);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataGedung['gedung_rawat_id']);
            $dbValue[] = QuoteValue(DPE_CHAR, $dataJenisKelas['jenis_kelas_id']);
            
            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

            $dtmodel->Insert() or die("insert error");

            unset($dtmodel); unset($dbField); unset($dbValue); unset($dbKey);

            for ($i=0; $i < $data[6]; $i++) { 
              $dbTable = "temp.klinik_kamar_bed";

              $dbField[] = "bed_id";   // PK
              $dbField[] = "bed_kode";
              $dbField[] = "id_kamar";
              $dbField[] = "bed_nama";
  
              $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
              $dbValue[] = QuoteValue(DPE_CHAR, $i+1);
              $dbValue[] = QuoteValue(DPE_CHAR, $kamar_id);
              $dbValue[] = QuoteValue(DPE_CHAR, $i+1);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
  
              $dtmodel->Insert() or die("insert error");
  
              unset($dtmodel); unset($dbField); unset($dbValue); unset($dbKey);
            }
            $baris++;
          } else {
            $error[] = $startLine;
          }
          $startLine ++;
        }

        $sql = "SELECT * FROM temp.klinik_kamar a LEFT JOIN klinik.klinik_kelas b ON a.id_kelas = b.kelas_id LEFT JOIN klinik.klinik_jenis_kelas c ON a.id_jenis_kelas = c.jenis_kelas_id LEFT JOIN global.global_auth_poli d ON a.id_poli = d.poli_id LEFT JOIN global.global_gedung_rawat e ON a.id_gedung_rawat = e.gedung_rawat_id ORDER BY kamar_nama ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO klinik.klinik_kamar SELECT * FROM temp.klinik_kamar";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_kamar";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO klinik.klinik_kamar_bed SELECT * FROM temp.klinik_kamar_bed";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_kamar_bed";
      $dtaccess->Execute($sql);

      header("Location: kamar_view.php");
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
                      <h2>Import Kamar</h2>
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
                                  <a href="kamar_view.php" class="btn btn-danger">Kembali</a>
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Kelas Kamar (Manajemen - Medis - Kelas Kamar) <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Nama Jenis Kelas (Manajemen - Medis - Jenis Kelas) <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Nama Poli IRNA (Manajemen - Medis - Setup Poli - Khusus Instalasi Rawat Inap) <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Nama Gedung Rawat (Manajemen - Medis - Gedung/Ruang Rawat) <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Kode Kamar <br />
                            &nbsp;&nbsp;Kolom 6 &nbsp;&nbsp;: Nama Kamar <br />
                            &nbsp;&nbsp;Kolom 7 &nbsp;&nbsp;: Kapasitas <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_kamar.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Nama Kelas Kamar</td>
                            <td>Nama Jenis Kelas</td>
                            <td>Nama Poli IRNA</td>
                            <td>Nama Gedung Rawat</td>
                            <td>Kode Kamar</td>
                            <td>Nama Kamar</td>
                            <td>Kapasitas</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <?php
                                $sql = "SELECT COUNT(*) AS total FROM temp.klinik_kamar_bed WHERE id_kamar = ".QuoteValue(DPE_CHAR, $value['kamar_id']);
                                $dataBed = $dtaccess->Fetch($sql);
                              ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['kelas_nama'] ?></td>
                                <td><?= $value['jenis_kelas_nama'] ?></td>
                                <td><?= $value['poli_nama'] ?></td>
                                <td><?= $value['gedung_rawat_nama'] ?></td>
                                <td><?= $value['kamar_kode'] ?></td>
                                <td><?= $value['kamar_nama'] ?></td>
                                <td><?= $dataBed['total'] ?></td>
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