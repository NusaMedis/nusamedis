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

  $jenis['UMUM'] = '2';
  $jenis['BPJS'] = '5';
  $jenis['ASURANSI'] = '7';
  $jenis['KARYAWAN'] = '20';

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
            /* Kategori Tindakan */
              $sql = "SELECT * FROM klinik.klinik_kategori_tindakan a LEFT JOIN klinik.klinik_kategori_tindakan_header b ON a.id_kategori_tindakan_header = b.kategori_tindakan_header_id WHERE UPPER(kategori_tindakan_header_nama) = ".QuoteValue(DPE_CHAR, strtoupper(str_replace('﻿', '', $data[0])))." AND UPPER(kategori_tindakan_nama) = ".QuoteValue(DPE_CHAR, strtoupper(str_replace('﻿', '', $data[1])));
              $dataKategori = $dtaccess->Fetch($sql);
            /* Kategori Tindakan */

            /* Kelas */
              $sql = "SELECT * FROM klinik.klinik_kelas WHERE UPPER(kelas_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[6]));
              $dataKelas = $dtaccess->Fetch($sql);
            /* Kelas */

            /* Biaya */
              $sql = "SELECT * FROM temp.klinik_biaya WHERE UPPER(biaya_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[5]));
              $dataBiaya = $dtaccess->Fetch($sql);
            /* Biaya */

            /* Insert Biaya */
              if (!$dataBiaya) {
                $dbTable = "temp.klinik_biaya";

                $dbField[] = "biaya_id";   // PK
                $dbField[] = "biaya_kategori";
                $dbField[] = "biaya_jenis_sem";
                $dbField[] = "biaya_jenis";
                $dbField[] = "biaya_kode";
                $dbField[] = "biaya_nama";
                $dbField[] = "biaya_urut";
                $dbField[] = "id_dep";

                $biaya_id = $dtaccess->GetTransID();
                $urut += 1;

                $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $dataKategori['kategori_tindakan_id']);
                $dbValue[] = QuoteValue(DPE_CHAR, $data[2]);
                $dbValue[] = QuoteValue(DPE_CHAR, $data[3]);
                $dbValue[] = QuoteValue(DPE_CHAR, $data[4]);
                $dbValue[] = QuoteValue(DPE_CHAR, $data[5]);
                $dbValue[] = QuoteValue(DPE_CHAR, $urut+$dtaccess->GetNewID("klinik.klinik_biaya","biaya_urut ",DB_SCHEMA_GLOBAL));
                $dbValue[] = QuoteValue(DPE_CHAR, $depId);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              }
            /* Insert Biaya */

            /* Insert Biaya Tarif */
              $dbTable = "temp.klinik_biaya_tarif";

              $dbField[] = "biaya_tarif_id";   // PK
              $dbField[] = "id_biaya";
              $dbField[] = "id_kelas";
              $dbField[] = "id_jenis_pasien";
              $dbField[] = "biaya_total";
              $dbField[] = "biaya_tarif_tgl_awal";
              $dbField[] = "biaya_tarif_tgl_akhir";

              $biaya_tarif_id = $dtaccess->GetTransID();
              $total = $data[10]+$data[11]+$data[12];

              $dbValue[] = QuoteValue(DPE_CHAR, $biaya_tarif_id);
              $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
              $dbValue[] = QuoteValue(DPE_CHAR, $dataKelas['kelas_id']);
              $dbValue[] = QuoteValue(DPE_CHAR, $jenis[strtoupper($data[7])]);
              $dbValue[] = QuoteValue(DPE_CHAR, $total);
              $dbValue[] = QuoteValue(DPE_CHAR, date_db($data[8]));
              $dbValue[] = QuoteValue(DPE_CHAR, date_db($data[9]));
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

              $dtmodel->Insert() or die("insert error");

              unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
            /* Insert Biaya Tarif */

            /* Insert Biaya Split */
              /* Pelayanan */
                $dbTable = "temp.klinik_biaya_split";

                $dbField[] = "bea_split_id";   // PK
                $dbField[] = "id_biaya";
                $dbField[] = "id_biaya_tarif";
                $dbField[] = "id_split";
                $dbField[] = "bea_split_nominal";
                $dbField[] = "bea_split_persen";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $biaya_tarif_id);
                $dbValue[] = QuoteValue(DPE_CHAR, '1');
                $dbValue[] = QuoteValue(DPE_CHAR, $data[11]+$data[12]);
                $dbValue[] = QuoteValue(DPE_CHAR, ($data[11]+$data[12])/$total*100);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);

                /* Remunerasi Dokter */
                  if ($data[11] > 0) {
                    $dbTable = "temp.klinik_biaya_remunerasi";

                    $dbField[] = "biaya_remunerasi_id";   // PK
                    $dbField[] = "id_biaya";
                    $dbField[] = "id_biaya_tarif";
                    $dbField[] = "id_split";
                    $dbField[] = "id_folio_posisi";
                    $dbField[] = "biaya_remunerasi_nominal";

                    $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                    $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
                    $dbValue[] = QuoteValue(DPE_CHAR, $biaya_tarif_id);
                    $dbValue[] = QuoteValue(DPE_CHAR, '1');
                    $dbValue[] = QuoteValue(DPE_CHAR, '10');
                    $dbValue[] = QuoteValue(DPE_CHAR, $data[11]);

                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                    $dtmodel->Insert() or die("insert error");

                    unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
                  }
                /* Remunerasi Dokter */

                /* Remunerasi Perawat */
                  if ($data[12] > 0) {
                    $dbTable = "temp.klinik_biaya_remunerasi";

                    $dbField[] = "biaya_remunerasi_id";   // PK
                    $dbField[] = "id_biaya";
                    $dbField[] = "id_biaya_tarif";
                    $dbField[] = "id_split";
                    $dbField[] = "id_folio_posisi";
                    $dbField[] = "biaya_remunerasi_nominal";

                    $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                    $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
                    $dbValue[] = QuoteValue(DPE_CHAR, $biaya_tarif_id);
                    $dbValue[] = QuoteValue(DPE_CHAR, '1');
                    $dbValue[] = QuoteValue(DPE_CHAR, '2');
                    $dbValue[] = QuoteValue(DPE_CHAR, $data[12]);

                    $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                    $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                    $dtmodel->Insert() or die("insert error");

                    unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
                  }
                /* Remunerasi Perawat */
              /* Pelayanan */

              /* Sarana */
                $dbTable = "temp.klinik_biaya_split";

                $dbField[] = "bea_split_id";   // PK
                $dbField[] = "id_biaya";
                $dbField[] = "id_biaya_tarif";
                $dbField[] = "id_split";
                $dbField[] = "bea_split_nominal";
                $dbField[] = "bea_split_persen";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, $biaya_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $biaya_tarif_id);
                $dbValue[] = QuoteValue(DPE_CHAR, '2');
                $dbValue[] = QuoteValue(DPE_CHAR, $data[10]);
                $dbValue[] = QuoteValue(DPE_CHAR, $data[10]/$total*100);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              /* Sarana */
            /* Insert Biaya Split */
            $baris++;
          } else {
            $error[] = $startLine;
          }
          $startLine ++;
        }

        $sql = "SELECT * FROM temp.klinik_biaya a LEFT JOIN temp.klinik_biaya_tarif b ON a.biaya_id = b.id_biaya LEFT JOIN klinik.klinik_kelas c ON b.id_kelas = c.kelas_id LEFT JOIN global.global_jenis_pasien d ON b.id_jenis_pasien = d.jenis_id ORDER BY biaya_nama ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO klinik.klinik_biaya SELECT * FROM temp.klinik_biaya";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_biaya";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO klinik.klinik_biaya_tarif SELECT * FROM temp.klinik_biaya_tarif";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_biaya_tarif";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO klinik.klinik_biaya_split SELECT * FROM temp.klinik_biaya_split";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_biaya_split";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO klinik.klinik_biaya_remunerasi SELECT * FROM temp.klinik_biaya_remunerasi";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.klinik_biaya_remunerasi";
      $dtaccess->Execute($sql);

      header("Location: import_tarif_irj.php");
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
                      <h2>Import Tarif Tindakan</h2>
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
                                </td>
                              </tr>
                            </table>
                          </form>
                        <!-- Form -->

                        <!-- Syarat Import -->
                          <font size="2" color="red">
                            &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Nama Header Kategori Tindakan (Lihat di Manajemen - Tarif - Kategori Tindakan Header) <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Nama Kategori Tindakan (Lihat di Manajemen - Tarif - Kategori Tindakan) <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Kode Jenis Tindakan (LIhat di Manajemen - Tarif - Master Jenis Tindakan) <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Kode Jenis INACBG (Lihat di Manajemen - Tarif - Master Variabel INACBG) <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Kode Tindakan <br />
                            &nbsp;&nbsp;Kolom 6 &nbsp;&nbsp;: Nama Tindakan <br />
                            &nbsp;&nbsp;Kolom 7 &nbsp;&nbsp;: Nama Kelas (Lihat di Manajemen - Medis - Kelas Kamar) <br />
                            &nbsp;&nbsp;Kolom 8 &nbsp;&nbsp;: Jenis Pasien (Umum, BPJS, Asuransi, Karyawan) <br />
                            &nbsp;&nbsp;Kolom 9 &nbsp;&nbsp;: Tanggal Awal Berlaku (dd-mm-yyyy) <br />
                            &nbsp;&nbsp;Kolom 10 : Tanggal Akhir Berlaku (dd-mm-yyyy)<br />
                            &nbsp;&nbsp;Kolom 11 : Biaya RS <br />
                            &nbsp;&nbsp;Kolom 12 : Biaya Dokter <br />
                            &nbsp;&nbsp;Kolom 13 : Biaya Perawat <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_tarif.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Kode Tindakan</td>
                            <td>Nama Tindakan</td>
                            <td>Nama Kelas</td>
                            <td>Nama Jenis Pasien</td>
                            <td>Harga Tindakan</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['biaya_kode'] ?></td>
                                <td><?= $value['biaya_nama'] ?></td>
                                <td><?= $value['kelas_nama'] ?></td>
                                <td><?= $value['jenis_nama'] ?></td>
                                <td><?= $value['biaya_total'] ?></td>
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