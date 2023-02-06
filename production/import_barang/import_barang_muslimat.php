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

  $jenis['GENERIK'] = 'g';
  $jenis['NON GENERIK'] = 't';
  $jenis['ALKES'] = 'a';

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
            /* Satuan Obat */
              $sql = "SELECT * FROM logistik.logistik_item_satuan WHERE satuan_tipe = 'J' AND UPPER(satuan_nama) = ".QuoteValue(DPE_CHAR, strtoupper(str_replace('﻿', '', $data[0])));
              $dataSatuan = $dtaccess->Fetch($sql);
            /* Satuan Obat */

            /* Kategori Obat */
              $sql = "SELECT * FROM logistik.logistik_grup_item WHERE UPPER(grup_item_nama) = ".QuoteValue(DPE_CHAR, strtoupper($data[1]));
              $dataKategori = $dtaccess->Fetch($sql);
            /* Kategori Obat */

            /* Gudang */
              $sql = "SELECT * FROM logistik.logistik_gudang ORDER BY gudang_nama ASC";
              $dataGudang = $dtaccess->FetchAll($sql);
            /* Gudang */

            /* Insert Logistik Item */
              $dbTable = "temp.logistik_item";

              $dbField[] = "item_id";   // PK
              $dbField[] = "id_satuan_jual";
              $dbField[] = "id_kategori";
              $dbField[] = "obat_flag";
              $dbField[] = "item_kode";
              $dbField[] = "item_nama";
              $dbField[] = "item_narkotika";
              $dbField[] = "item_psikotropika";
              $dbField[] = "item_harga_beli";
              $dbField[] = "id_dep";

              $item_id = $dtaccess->GetTransID();
              $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
              $dbValue[] = QuoteValue(DPE_CHAR, $dataSatuan['satuan_id']);
              $dbValue[] = QuoteValue(DPE_CHAR, $dataKategori['grup_item_id']);
              $dbValue[] = QuoteValue(DPE_CHAR, $jenis[strtoupper($data[2])]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[3]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[4]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[5]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[6]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[7]);
              $dbValue[] = QuoteValue(DPE_CHAR, $depId);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

              $dtmodel->Insert() or die("insert error");

              unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
            /* Insert Logistik Item */

            /* Insert Logistik Item Batch */
              $dbTable = "temp.logistik_item_batch";

              $dbField[] = "batch_id";   // PK
              $dbField[] = "batch_create";
              $dbField[] = "id_item";
              $dbField[] = "batch_stok_saldo";
              $dbField[] = "batch_flag";
              $dbField[] = "id_dep";

              $batch_id = $dtaccess->GetTransID();
              $dbValue[] = QuoteValue(DPE_CHAR, $batch_id);
              $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
              $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
              $dbValue[] = QuoteValue(DPE_CHAR, '1000');
              $dbValue[] = QuoteValue(DPE_CHAR, 'A');
              $dbValue[] = QuoteValue(DPE_CHAR, $depId);
              
              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

              $dtmodel->Insert() or die("insert error");

              unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
            /* Insert Logistik Item Batch */

            foreach ($dataGudang as $value) {
              /* Insert Logistik Stok Dep */
                $dbTable = "temp.logistik_stok_dep";

                $dbField[] = "stok_dep_id";   // PK
                $dbField[] = "id_item";
                $dbField[] = "stok_dep_saldo";
                $dbField[] = "stok_dep_create";
                $dbField[] = "stok_dep_tgl";
                $dbField[] = "id_dep";
                $dbField[] = "id_gudang";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d'));
                $dbValue[] = QuoteValue(DPE_CHAR, $depId);
                $dbValue[] = QuoteValue(DPE_CHAR, $value['gudang_id']);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              /* Insert Logistik Stok Dep */

              /* Insert Logistik Stok Batch Dep */
                $dbTable = "temp.logistik_stok_batch_dep";

                $dbField[] = "stok_batch_dep_id";   // PK
                $dbField[] = "id_item";
                $dbField[] = "id_batch";
                $dbField[] = "stok_batch_dep_saldo";
                $dbField[] = "stok_batch_dep_create";
                $dbField[] = "stok_batch_dep_tgl";
                $dbField[] = "id_dep";
                $dbField[] = "id_gudang";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $batch_id);
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d'));
                $dbValue[] = QuoteValue(DPE_CHAR, $depId);
                $dbValue[] = QuoteValue(DPE_CHAR, $value['gudang_id']);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              /* Insert Logistik Stok Batch Dep */

              /* Insert Logistik Stok Item */
                $dbTable = "temp.logistik_stok_item";

                $dbField[] = "stok_item_id";   // PK
                $dbField[] = "stok_item_jumlah";
                $dbField[] = "id_item";
                $dbField[] = "id_dep";
                $dbField[] = "stok_item_flag";
                $dbField[] = "stok_item_create";
                $dbField[] = "stok_item_saldo";
                $dbField[] = "id_gudang";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $depId);
                $dbValue[] = QuoteValue(DPE_CHAR, 'A');
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, $value['gudang_id']);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              /* Insert Logistik Stok Item */

              /* Insert Logistik Stok Item Batch */
                $dbTable = "temp.logistik_stok_item_batch";

                $dbField[] = "stok_item_batch_id";   // PK
                $dbField[] = "stok_item_batch_jumlah";
                $dbField[] = "id_item";
                $dbField[] = "id_batch";
                $dbField[] = "id_dep";
                $dbField[] = "stok_item_batch_flag";
                $dbField[] = "stok_item_batch_create";
                $dbField[] = "stok_item_batch_saldo";
                $dbField[] = "id_gudang";

                $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, $item_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $batch_id);
                $dbValue[] = QuoteValue(DPE_CHAR, $depId);
                $dbValue[] = QuoteValue(DPE_CHAR, 'A');
                $dbValue[] = QuoteValue(DPE_CHAR, date('Y-m-d H:i:s'));
                $dbValue[] = QuoteValue(DPE_CHAR, '1000');
                $dbValue[] = QuoteValue(DPE_CHAR, $value['gudang_id']);
                
                $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
                $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

                $dtmodel->Insert() or die("insert error");

                unset($dtmodel); unset($dbTable); unset($dbField); unset($dbValue); unset($dbKey);
              /* Insert Logistik Stok Item Batch */
            }
            $baris++;
          } else {
            $error[] = $startLine;
          }
          $startLine ++;
        }

        $sql = "SELECT * FROM temp.logistik_item a LEFT JOIN logistik.logistik_grup_item b ON a.id_kategori = b.grup_item_id LEFT JOIN logistik.logistik_item_satuan c ON a.id_satuan_jual = c.satuan_id ORDER BY item_nama ASC";
        $dataTable = $dtaccess->FetchAll($sql);
      }
      if($error) $error = implode("<br>Data Excel Baris ke ",$error);
    }
  /* KLIK IMPORT */

  /* KLIK APPROVE */
    if(isset($_POST["btnApprove"])){
      $sql = "INSERT INTO logistik.logistik_item SELECT * FROM temp.logistik_item";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_item";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO logistik.logistik_item_batch SELECT * FROM temp.logistik_item_batch";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_item_batch";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO logistik.logistik_stok_dep SELECT * FROM temp.logistik_stok_dep";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_stok_dep";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO logistik.logistik_stok_batch_dep SELECT * FROM temp.logistik_stok_batch_dep";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_stok_batch_dep";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO logistik.logistik_stok_item SELECT * FROM temp.logistik_stok_item";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_stok_item";
      $dtaccess->Execute($sql);

      $sql = "INSERT INTO logistik.logistik_stok_item_batch SELECT * FROM temp.logistik_stok_item_batch";
      $dtaccess->Execute($sql);

      $sql = "DELETE FROM temp.logistik_stok_item_batch";
      $dtaccess->Execute($sql);

      header("Location: import_barang_muslimat.php");
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
                      <h2>Import Obat</h2>
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
                            &nbsp;&nbsp;Kolom 1 &nbsp;&nbsp;: Satuan Obat (Lihat di Logistik - Master - Satuan Barang) <br />
                            &nbsp;&nbsp;Kolom 2 &nbsp;&nbsp;: Kategori Obat (Lihat di Logistik - Master - Kategori Barang) <br />
                            &nbsp;&nbsp;Kolom 3 &nbsp;&nbsp;: Jenis Obat (Generik / Non Generik / Alkes) <br />
                            &nbsp;&nbsp;Kolom 4 &nbsp;&nbsp;: Kode Obat <br />
                            &nbsp;&nbsp;Kolom 5 &nbsp;&nbsp;: Nama Obat <br />
                            &nbsp;&nbsp;Kolom 6 &nbsp;&nbsp;: Obat Narkotika (y/n) <br />
                            &nbsp;&nbsp;Kolom 7 &nbsp;&nbsp;: Obat Psikotropika (y/n) <br />
                            &nbsp;&nbsp;Kolom 8 &nbsp;&nbsp;: Harga Beli <br />
                            &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                            <a href="import_obat.csv">disini</a> <br>
                          </font>
                        <!-- Syarat Import -->
                      </div>
                    <!-- Sebelum Import -->
                    <?php if ($_POST['btnImport']) { ?>
                      <div class="x_content">
                        <table class="table table-bordered">
                          <tr>
                            <td>No</td>
                            <td>Kode Obat</td>
                            <td>Nama Obat</td>
                            <td>Satuan Obat</td>
                            <td>Kategori Obat</td>
                            <td>Jenis Obat</td>
                            <td>Obat Narkotika</td>
                            <td>Obat Psikotropika</td>
                            <td>Harga Tindakan</td>
                          </tr>
                          <?php if($dataTable) : ?>
                            <?php foreach($dataTable as $key => $value) : ?>
                              <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $value['item_kode'] ?></td>
                                <td><?= $value['item_nama'] ?></td>
                                <td><?= $value['satuan_nama'] ?></td>
                                <td><?= $value['grup_item_nama'] ?></td>
                                <td><?= $value['obat_flag'] ?></td>
                                <td><?= $value['item_narkotika'] ?></td>
                                <td><?= $value['item_psikotropika'] ?></td>
                                <td><?= $value['item_harga_beli'] ?></td>
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