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
          $sql = "SELECT struk_id FROM hris.hris_struktural WHERE struk_kode LIKE ".QuoteValue(DPE_CHAR,$data[4]);   
          $rs = $dtaccess->Execute($sql);
          $dataStruk = $dtaccess->Fetch($rs);
          
          $sql = "SELECT * FROM global.global_auth_role WHERE UPPER(rol_name) = ".QuoteValue(DPE_CHAR, strtoupper($data[5]));
          $dataJabatan = $dtaccess->Fetch($sql);
             
          if ($dataStruk) //Jika ada kodenya atau kodenya benar
          {
            // INSERT ke HRIS Pegawai
            $dbTable = "temp.hris_pegawai";

            $dbField[0] = "pgw_id";   // PK
            $dbField[1] = "pgw_nama";
            $dbField[2] = "pgw_nip";
            $dbField[3] = "pgw_alamat";
            $dbField[4] = "pgw_telp_hp";              
            $dbField[5] = "id_struk";
                  
            $pgwId = $dtaccess->GetTransID();
            $dbValue[0] = QuoteValue(DPE_CHAR,$pgwId);
            $dbValue[1] = QuoteValue(DPE_CHAR,$data[0]);
            $dbValue[2] = QuoteValue(DPE_CHAR,$data[1]);
            $dbValue[3] = QuoteValue(DPE_CHAR,$data[2]);
            $dbValue[4] = QuoteValue(DPE_CHAR,$data[3]);
            $dbValue[5] = QuoteValue(DPE_CHAR,$dataStruk["struk_id"]);

            $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
            $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

            $dtmodel->Insert() or die("insert error");

            unset($dtmodel); unset($dbValue); unset($dbKey); unset($dbTable); unset($dbField);

            /* Userlogin */
              $dbTable = "temp.global_auth_user";

              $dbField[] = "usr_id";   // PK
              $dbField[] = "usr_name";   // PK
              $dbField[] = "usr_loginname";
              $dbField[] = "usr_password";
              $dbField[] = "id_rol";
              $dbField[] = "id_dep";              
              $dbField[] = "id_pgw";

              $dbValue[] = QuoteValue(DPE_CHAR, $dtaccess->GetTransID());
              $dbValue[] = QuoteValue(DPE_CHAR, $data[0]);
              $dbValue[] = QuoteValue(DPE_CHAR, $data[6]);
              $dbValue[] = QuoteValue(DPE_CHAR, md5($data[7]));
              $dbValue[] = QuoteValue(DPE_CHAR, $dataJabatan["rol_id"]);
              $dbValue[] = QuoteValue(DPE_CHAR, $depId);
              $dbValue[] = QuoteValue(DPE_CHAR, $pgwId);

              $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
              $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

              $dtmodel->Insert() or die("insert error");

              unset($dtmodel); unset($dbValue); unset($dbKey); unset($dbTable); unset($dbField);
            /* Userlogin */
            
          }
          else   
          {  //Jika Kodenya salah
            echo "<font color='red'>Kode Satker salah baris ke".$baris." </font><br>";
          }   
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

  $sql = "SELECT * FROM temp.hris_pegawai a LEFT JOIN hris.hris_struktural b ON b.struk_id = a.id_struk LEFT JOIN temp.global_auth_user c ON a.pgw_id = c.id_pgw";
  $dataTable = $dtaccess->FetchAll($sql);


  if ($_POST['btnNext2']) {
    $sql = "SELECT * FROM temp.hris_pegawai";
    $dataTable = $dtaccess->FetchAll($sql);

    for ($i=0; $i < count($dataTable); $i++) { 
      // INSERT ke HRIS Pegawai
      $sql = "SELECT * FROM hris.hris_pegawai WHERE pgw_nip = ".QuoteValue(DPE_CHAR, $dataTable[$i]['pgw_nip']);
      $Check = $dtaccess->Fetch($sql);
      if (!$Check) {
        $dbTable = "hris.hris_pegawai";

        $dbField[0] = "pgw_id";   // PK
        $dbField[1] = "pgw_nama";
        $dbField[2] = "pgw_nip";
        $dbField[3] = "pgw_alamat";
        $dbField[4] = "pgw_telp_hp";              
        $dbField[5] = "id_struk";
              
        $pgwId = $dtaccess->GetTransID();
        $dbValue[0] = QuoteValue(DPE_CHAR,$pgwId);
        $dbValue[1] = QuoteValue(DPE_CHAR,$dataTable[$i]['pgw_nama']);
        $dbValue[2] = QuoteValue(DPE_CHAR,$dataTable[$i]['pgw_nip']);
        $dbValue[3] = QuoteValue(DPE_CHAR,$dataTable[$i]['pgw_alamat']);
        $dbValue[4] = QuoteValue(DPE_CHAR,$dataTable[$i]['pgw_telp_hp']);
        $dbValue[5] = QuoteValue(DPE_CHAR,$dataTable[$i]['id_struk']);

        $dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
        $dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);

        $dtmodel->Insert() or die("insert error");

        unset($dtmodel); unset($dbValue); unset($dbKey);
      }
    }
    $sql = "INSERT INTO global.global_auth_user SELECT * FROM temp.global_auth_user WHERE id_pgw NOT IN (SELECT id_pgw FROM global.global_auth_user)";
    $dtaccess->Execute($sql);

    $sql = "DELETE FROM temp.hris_pegawai";
    $dtaccess->Execute($sql);
  }

  $tableHeader = "Import Pegawai";
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
                    <h2>Import Data Pegawai</h2>
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
                      &nbsp;&nbsp;Data Yang dibutuhkan:<br />
                      &nbsp;&nbsp;Kolom 1    : Nama Pegawai<br />
                      &nbsp;&nbsp;Kolom 2    : NIP  <br />
                      &nbsp;&nbsp;Kolom 3    : Alamat  <br />
                      &nbsp;&nbsp;Kolom 4    : No. HP  <br />
                      &nbsp;&nbsp;Kolom 5    : Kode Satker (Lihat Manajemen - User - Satuan Kerja)  <br />
                      &nbsp;&nbsp;Kolom 6    : Nama Jabatan (Lihat Manajemen - User - Jabatan)  <br />
                      &nbsp;&nbsp;Kolom 7    : Userlogin  <br />
                      &nbsp;&nbsp;Kolom 8    : Password  <br />
                      &nbsp;&nbsp;Untuk Contoh data silahkan klik 
                      <a href="import_pegawai.csv">disini</a> <br>
                    </font>
                  </div>
                  <?php if ($_POST['btnNext']) { ?>
                    <div class="x_content">
                      <table class="table table-bordered">
                        <tr>
                          <td>Nama Pegawai</td>
                          <td>NIP</td>
                          <td>Alamat</td>
                          <td>No Hp</td>
                          <td>Satker</td>
                          <td>Userlogin</td>
                          <td>Password</td>
                        </tr>
                        <?php for ($i=0; $i < count($dataTable); $i++) { ?>
                          <tr>
                            <td><?php echo $dataTable[$i]['pgw_nama'] ?></td>
                            <td><?php echo $dataTable[$i]['pgw_nip'] ?></td>
                            <td><?php echo $dataTable[$i]['pgw_alamat'] ?></td>
                            <td><?php echo $dataTable[$i]['pgw_telp_hp'] ?></td>
                            <td><?php echo $dataTable[$i]['struk_nama'] ?></td>
                            <td><?php echo $dataTable[$i]['usr_loginname'] ?></td>
                            <td><?php echo $dataTable[$i]['usr_password'] ?></td>
                          </tr>
                        <?php } ?>
                      </table>
                      <form name="frmEdit" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>" enctype="multipart/form-data">
                        <input type="submit" name="btnNext2" class="btn btn-success" value="Approve">
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
