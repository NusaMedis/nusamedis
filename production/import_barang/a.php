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
  // echo $csvFile;
  // die();
  $plx = new expAJAX();


  if(isset($_POST["btnNext"])){

    if($_FILES["csv_file"]["tmp_name"]){
      $err = false;
    } else {
      $err=true;
    }

    if(!$err){
      if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
        $csvFile .= $_FILES["csv_file"]["name"];
        copy($_FILES["csv_file"]["tmp_name"], $csvFile);
      }
    }

    if ((!$myFile = @fopen(stripslashes($csvFile), "r")) || $err==true) {
      $err = true;
    }
    else
    {
      $baris=1;
      while ($data = fgetcsv($myFile, 500000, $delimiter))
      {
        if($data[0] || $data[1] || $data[2] || $data[3] || $data[4])
        {
          $sql = "SELECT agm_id FROM global.global_agama WHERE UPPER(agm_nama) LIKE ".QuoteValue(DPE_CHAR,strtoupper($data[5]));   
          $dataAgama = $dtaccess->Fetch($sql);
          // INSERT ke HRIS Pegawai
          $dbTable = "temp.global_customer_user";

          $dbField[0] = "cust_usr_id";   // PK
          $dbField[1] = "cust_usr_kode";
          $dbField[2] = "cust_usr_nama";
          $dbField[3] = "cust_usr_tempat_lahir";
          $dbField[4] = "cust_usr_tanggal_lahir";
          $dbField[5] = "cust_usr_jenis_kelamin";
          $dbField[6] = "cust_usr_agama";
          $dbField[7] = "cust_usr_alamat";
          $dbField[8] = "cust_usr_no_hp";
          $dbField[9] = "cust_usr_penanggung_jawab";
                
          $primaryKey = $dtaccess->GetTransID();
          $dbValue[0] = QuoteValue(DPE_CHAR,$primaryKey);
          $dbValue[1] = QuoteValue(DPE_CHAR,$data[0]);
          $dbValue[2] = QuoteValue(DPE_CHAR,$data[1]);
          $dbValue[3] = QuoteValue(DPE_CHAR,$data[2]);
          $dbValue[4] = QuoteValue(DPE_CHAR,$data[3]);
          $dbValue[5] = QuoteValue(DPE_CHAR,$data[4]);
          $dbValue[6] = QuoteValue(DPE_CHAR,$dataAgama['agm_id']);
          $dbValue[7] = QuoteValue(DPE_CHAR,$data[6]);
          $dbValue[8] = QuoteValue(DPE_CHAR,$data[7]);
          $dbValue[9] = QuoteValue(DPE_CHAR,$data[8]);

          $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
          $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

          $dtmodel->Insert() or die("insert error");

          unset($dtmodel); unset($dbValue); unset($dbKey);

          $baris++;
        }  // end check benar ngga is file excelnya
        else
        {
          $error[] = $startLine;
        }
        $startLine ++;
      }
    }
    if($error) $error = implode("<br>Data Excel Baris ke ",$error);
  }

  $sql = "SELECT * FROM temp.global_customer_user a
          LEFT JOIN global.global_agama b ON b.agm_id = a.cust_usr_agama";
  $dataTable = $dtaccess->FetchAll($sql);

  if ($_POST['btnNext2']) {
    $sql = "SELECT * FROM temp.global_customer_user";
    $dataTable = $dtaccess->FetchAll($sql);

    for ($i=0; $i < count($dataTable); $i++) { 
      // INSERT ke HRIS Pegawai
      $sql = "SELECT * FROM global.global_customer_user WHERE cust_usr_kode = ".QuoteValue(DPE_CHAR, $dataTable[$i]['cust_usr_kode']);
      $Check = $dtaccess->Fetch($sql);
      if (!$Check) {
        $dbTable = "global.global_customer_user";

        $dbField[0] = "cust_usr_id";   // PK
        $dbField[1] = "cust_usr_kode";
        $dbField[2] = "cust_usr_nama";
        $dbField[3] = "cust_usr_tempat_lahir";
        $dbField[4] = "cust_usr_tanggal_lahir";
        $dbField[5] = "cust_usr_jenis_kelamin";
        $dbField[6] = "cust_usr_agama";
        $dbField[7] = "cust_usr_alamat";
        $dbField[8] = "cust_usr_no_hp";
        $dbField[9] = "cust_usr_penanggung_jawab";
              
        $primaryKey = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$primaryKey);
        $dbValue[1] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_kode']);
        $dbValue[2] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_nama']);
        $dbValue[3] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_tempat_lahir']);
        $dbValue[4] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_tanggal_lahir']);
        $dbValue[5] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_jenis_kelamin']);
        $dbValue[6] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_agama']);
        $dbValue[7] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_alamat']);
        $dbValue[8] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_no_hp']);
        $dbValue[9] = QuoteValue(DPE_CHAR,$dataTable[$i]['cust_usr_penanggung_jawab']);

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert error");

        unset($dtmodel); unset($dbValue); unset($dbKey);
      }
    }
    $sql = "DELETE FROM temp.global_customer_user";
    $dtaccess->Execute($sql);
  }

  $tableHeader = "Import Pasien";
?>

<!DOCTYPE html>
<html lang="en">
  <?php require_once($LAY."header.php") ?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php require_once($LAY."sidebar.php") ?>
        <!-- top navigation -->
        <?php require_once($LAY."topnav.php") ?>
        <!-- /top navigation -->
        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="clearfix"></div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Import Data Pasien</h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
                      <table class="table table-bordered">
                        <tr>
                          <td>Delimiter</td>
                          <td>
                            <select class="form-control" name="delimiter" required>
                              <option value=""></option>
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
                          <td colspan="2"><input type="submit" name="btnNext" class="btn btn-success" value="Import"></td>
                        </tr>
                      </table>
                    </form>

                    <font size="2" color="red">
                      &nbsp;&nbsp;Data Yang dibutuhkan:<br>
                      &nbsp;&nbsp;Kolom 1    : No Rekam Medis<br>
                      &nbsp;&nbsp;Kolom 2    : Nama Pasien<br>
                      &nbsp;&nbsp;Kolom 3    : Tempat Lahir<br>
                      &nbsp;&nbsp;Kolom 4    : Tanggal Lahir (yyyy-mm-dd) ex (2021-12-31)<br>
                      &nbsp;&nbsp;Kolom 5    : Jenis Kelamin (L/P)<br>
                      &nbsp;&nbsp;Kolom 6    : Agama (Islam, Kristen Protestan, Kristen Katholik, Hindu, Budha, Kristen, Kepercayaan, Lain-lain)<br>
                      &nbsp;&nbsp;Kolom 7    : Alamat<br>
                      &nbsp;&nbsp;Kolom 8    : No HP<br>
                      &nbsp;&nbsp;Kolom 9    : Penanggung Jawab<br>
                      &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                      <a href="pasien.csv">disini</a> <br>
                    </font>
                  </div>
                  <?php if ($_POST['btnNext']) { ?>
                    <div class="x_content">
                      <table class="table table-bordered">
                        <tr>
                          <td>No Rekam Medis</td>
                          <td>Nama Pasien</td>
                          <td>Tempat Lahir</td>
                          <td>Tanggal lahir</td>
                          <td>Jenis Kelamin</td>
                          <td>Agama</td>
                          <td>Alamat</td>
                          <td>No HP</td>
                          <td>Penanggung Jawab</td>
                        </tr>
                        <?php for ($i=0; $i < count($dataTable); $i++) { ?>
                          <tr>
                            <td><?php echo $dataTable[$i]['cust_usr_kode'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_nama'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_tempat_lahir'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_tanggal_lahir'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_jenis_kelamin'] ?></td>
                            <td><?php echo $dataTable[$i]['agm_nama'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_alamat'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_no_hp'] ?></td>
                            <td><?php echo $dataTable[$i]['cust_usr_penanggung_jawab'] ?></td>
                          </tr>
                        <?php } ?>
                      </table>
                      <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
                        <input type="submit" name="btnNext2" class="btn btn-success" value="Verifikasi">
                      </form>
                    </div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
        <?php require_once($LAY."footer.php") ?>
        <!-- /footer content -->
      </div>
    </div>
    <?php require_once($LAY."js.php") ?>
  </body>
</html>