<?php
     //LIBRARY
require_once("../penghubung.inc.php");
require_once($LIB."bit.php");
require_once($LIB."login.php");
require_once($LIB."encrypt.php");
require_once($LIB."datamodel.php");
require_once($LIB."tampilan.php");

     //INISIALISASI LIBRARY
$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
$dtaccess = new DataAccess();
$enc = new textEncrypt();
$auth = new CAuth();
$userData = $auth->GetUserData();
$err_code = 0;
$depNama = $auth->GetDepNama();
$depId = $auth->GetDepId();
$userName = $auth->GetUserName();
     //Ambil Data Status Departemen Klinik kalau terendah(y) maka tidak keluar combo pilihan Klinik
$depLowest = $auth->GetDepLowest();
     //PRIVILLAGE  
    //  $RM = $dataPasien['rawat_id'];
    //  $nama_ttd=$dataPasien['rawat_nama_ttd'];
    // $lokasi = "../gambar/asset_ttd/".$RM.".jpg";  

  /*  if(!$auth->IsAllowed("man_user_user_login",PRIV_READ)){
          die("access_denied");
          exit(1);

     } elseif($auth->IsAllowed("man_user_user_login",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
          exit(1);
        } */

        $thisPage = "hakakses_ttd.php";
        $findPage = "cari_pegawai.php?";
        $lokasi = $ROOT."/gambar/foto_pegawai";

     //GET DATA   
        if($_POST["usr_id"])  $usrId = & $_POST["usr_id"];   
        if (!$_POST["klinik"]) $_POST["klinik"]=$depId;

     //EDIT DATA
        if ($_GET["id"]) {
         $usrId = $enc->Decode($_GET["id"]);
         $ttd = "../gambar/asset_ttd/".$usrId.".jpg"; 


         $sql = "select * 
         from hris.hris_pegawai e 
         left join global.global_auth_user a on a.id_pgw = e.pgw_id 
         left join global.global_auth_role b on a.id_rol = b.rol_id  
         left join global.global_departemen c on a.id_dep = c.dep_id
         left join global.global_auth_poli d on d.poli_id = a.usr_poli
         left join hris.hris_struktural f on f.struk_id = e.id_struk where 1=1 and usr_id = ".QuoteValue(DPE_CHAR,$usrId);
         $rs_edit = $dtaccess->Execute($sql);
         $row_edit = $dtaccess->Fetch($rs_edit);
         $dtaccess->Clear($rs_edit);
          //echo $sql;
         $_POST["usr_loginname"] = $row_edit["usr_loginname"];
         $_POST["usr_name"] = $row_edit["usr_name"];
         $_POST["id_rol"] = $row_edit["id_rol"];
         $_POST["usr_status"] = $row_edit["usr_status"];
         $_POST["usr_when_create"] = $row_edit["usr_when_create"];
         $_POST["usr_app_def"] = $row_edit["usr_app_def"];
         $_POST["usr_poli"] = $row_edit["usr_poli"];
         $_POST["klinik"] = $row_edit["id_dep"];
         $_POST["usr_foto"] = $row_edit["pgw_foto"]; 
         $_POST["usr_alamat"] = $row_edit["pgw_alamat"]; 
         $_POST["usr_tempat_lahir"] = $row_edit["pgw_tempat_lahir"]; 
         $_POST["usr_tgl_lahir"] = format_date($row_edit["pgw_tanggal_lahir"]); 
         $_POST["usr_no_hp"] = $row_edit["pgw_telp_hp"];
         $_POST["usr_pendidikan"] = $row_edit["pgw_pendidikan"];
          //$_POST["id_struk"] = $row_edit["id_struk"];
         $_POST["pgw_nip"] = $row_edit["pgw_nip"];
         $_POST["pgw_id"] = $row_edit["pgw_id"];
         $_POST["struk"] = $row_edit["id_struk"];
         $_POST["struk_nama"] = $row_edit["struk_nama"];
         $_POST["usr_no_urut"] = $row_edit["usr_no_urut"];
         
         if($_POST["usr_foto"]) $fotoName = $lokasi."/".$row_edit["usr_foto"];
         else $fotoName = $lokasi."/default.jpg";
         $back = "hakakses_view.php?satker=".$_POST["satker"]."&kembali=".$_POST["klinik"];


         $sql = "select * from global.global_auth_user_app
         where id_usr = ".QuoteValue(DPE_CHAR,$usrId)."
         order by id_app";
         $rs = $dtaccess->Execute($sql);
         $dataUsrApp = $dtaccess->FetchAll($rs);

         for($i=0,$n=count($dataUsrApp);$i<$n;$i++){
           $_POST["id_app"][$dataUsrApp[$i]["id_app"]] = $dataUsrApp[$i]["id_app"];               
         }

           // -- CARI DATA APLIKASI -- //
         $sql = "select * from global.global_app order by app_nama asc";
         $rs = $dtaccess->Execute($sql,DB_SCHEMA);
         $dataApp = $dtaccess->FetchAll($rs); 
         for($i=0,$n=count($dataApp);$i<$n;$i++)
         {
           $sql = "select * from global.global_auth_user_app
           where id_usr = ".QuoteValue(DPE_CHAR,$usrId)." and id_app = ".$dataApp[$i]["app_id"]."
           order by id_app";
           $rs = $dtaccess->Execute($sql);
           $dataUsrAppSatuan = $dtaccess->Fetch($rs);

           if ($dataUsrAppSatuan)
            $_POST["id_app"][$i] = $dataUsrAppSatuan["id_app"];
          else 
            $_POST["id_app"][$i] = "N";  
          unset($dataUsrAppSatuan);            
        } 

          //----end---//

        $sql = "select * from global.global_auth_user_poli
        where id_usr = ".QuoteValue(DPE_CHAR,$usrId)."
        order by id_poli";
        $rs = $dtaccess->Execute($sql);
          //echo $sql;
        $dataUsrPoli = $dtaccess->FetchAll($rs);

        for($i=0,$n=count($dataUsrPoli);$i<$n;$i++){
         $_POST["id_poli"][$dataUsrPoli[$i]["id_poli"]] = $dataUsrPoli[$i]["id_poli"];               
       }

           // -- CARI DATA APLIKASI -- //
       $sql = "select * from global.global_auth_poli order by poli_nama asc";
       $rs = $dtaccess->Execute($sql,DB_SCHEMA);
       $dataPoli = $dtaccess->FetchAll($rs); 
       for($i=0,$n=count($dataPoli);$i<$n;$i++)
       {
         $sql = "select * from global.global_auth_user_poli
         where id_usr = ".QuoteValue(DPE_CHAR,$usrId)." and id_poli = '".$dataPoli[$i]["poli_id"]."'
         order by id_poli";
         $rs = $dtaccess->Execute($sql);
         $dataUsrPoliSatuan = $dtaccess->Fetch($rs);

         if ($dataUsrPoliSatuan)
          $_POST["id_poli"][$i] = $dataUsrPoliSatuan["id_poli"];
        else 
          $_POST["id_poli"][$i] = "N";  
        unset($dataUsrPoliSatuan);            
      } 



             // -- CARI DATA Validator -- //
      $sql = "select * from global.global_validator order by validator_id asc";

      $rs = $dtaccess->Execute($sql,DB_SCHEMA);
      $dataValidator = $dtaccess->FetchAll($rs); 
      for($i=0,$n=count($dataValidator);$i<$n;$i++)
      {
       $sql = "select * from global.global_user_validator
       where id_usr = ".QuoteValue(DPE_CHAR,$usrId)." and id_validator = '".$dataValidator[$i]["validator_id"]."'
       order by id_validator";
                    // echo $sql;
       $rs = $dtaccess->Execute($sql);
       $dataUsrValidatorSatuan = $dtaccess->Fetch($rs);

       if ($dataUsrValidatorSatuan)
        $_POST["id_validator"][$i] = $dataUsrValidatorSatuan["id_validator"];
      else 
        $_POST["id_validator"][$i] = "N";  
      unset($dataUsrValidatorSatuan);            
    } 
          //----end---//

  }




  if (!$_POST["usr_foto"])
  {
    $_POST["usr_foto"] = "default.jpg";
    $fotoName = $lokasi."/".$_POST["usr_foto"];
  }

     //PRIVILLAGE 
     /*if(!$auth->IsAllowed("setup_hak_akses",$privMode)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
     } else if($auth->IsAllowed("setup_hak_akses",$privMode)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Login First'</script>";
          exit(1);
        }  */


     // menyimpan data 
        if ($_POST["btnUpdate"]) {

          $usr_Id =  $_POST["usr_id"];
          $nm_file = $_FILES["gambar"]["name"];
          $tp_file = $_FILES["gambar"]["tmp_name"];
          $sz_file = $_FILES["gambar"]["size"];
          $ty_file = $_FILES["gambar"]["type"];

          $dir =  "../gambar/asset_ttd/".$usr_Id.".jpg"; 

          move_uploaded_file($tp_file, $dir);
          $ttd = "../gambar/asset_ttd/".$usr_Id.".jpg"; 

  // cek status file
          if($sz_file > 100000){
    // tampilkan status gagal karena melebihi batas
            echo $nm_file . " <b>Gagal! file melebihi ukuran. Ukuran maksimal 100kb</b><br>"."<input type='button' value='Kembali' onclick='history.back(-1)' />";
          } else if (file_exists($ttd)) {
    // tampilkan status gagal karena file sudah ada
            $imgFullpath = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/'. "gambar/asset_ttd/" . $nm_file;
            echo $nm_file . " <b>Gagal! File sudah ada</b><br>" ."<input type='button' value='Kembali' onclick='history.back(-1)' />";
          } else {
    //lokasi file
            $imgFullpath = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/'. "gambar/asset_ttd/" .$usr_Id.".jpg"; 

    //deskripsi file
            echo "<b>Stored in:</b><a href = '$imgFullpath' target='_blank'> " .$imgFullpath.'<a>';

            echo "<br/><b>File Name:</b> " . $nm_file . "<br>";
            echo "<b>Type:</b> " . $ty_file . "<br>";
            echo "<b>Size:</b> " . $sz_file . " kB<br>";
            echo "<b>Temp file:</b> " . $tp_file . "<br>";
          }






          $back = "hakakses_view.php?satker=".$_POST["satker"]."&kembali=".$_POST["klinik"]."";
          header("location:".$back);
          exit();           

        }

     //BUAT HAPUS USER DATA
        if ($_GET["del"]) {
          $usrId = $enc->Decode($_GET["id"]);
          //print_r($_POST["btnDelete"]);
          //die();

          $sql = "delete from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
          $dtaccess->Execute($sql);

               //$sql = "delete from hris.hris_pegawai where pgw_id = ".QuoteValue(DPE_CHAR,$usrId);
               //$dtaccess->Execute($sql);

          $back = "hakakses_view.php?satker=".$_POST["satker"]."&kembali=".$_POST["klinik"]."";
          header("location:".$back);
          exit(); 
        }

     //BUAT HAPUS USER DATA
        if ($_GET["reset"]) {
          $usrId = $enc->Decode($_GET["id"]);

          $sql = "select usr_loginname from global.global_auth_user where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
          $rs = $dtaccess->Execute($sql);
          $dataUser = $dtaccess->Fetch($rs);

          $passwordReset = md5($dataUser["usr_loginname"]); 

          $sql = "update global.global_auth_user set usr_password = ".QuoteValue(DPE_CHAR,$passwordReset)." where usr_id = ".QuoteValue(DPE_CHAR,$usrId);
          $dtaccess->Execute($sql);

               //$sql = "delete from hris.hris_pegawai where pgw_id = ".QuoteValue(DPE_CHAR,$usrId);
               //$dtaccess->Execute($sql);

          $back = "hakakses_view.php?satker=".$_POST["satker"]."&kembali=".$_POST["klinik"]."";
          header("location:".$back);
          exit(); 
        }

        $sql = "select * from global.global_auth_role order by rol_name asc";
        $rs = $dtaccess->Execute($sql);
        $dataJabatan = $dtaccess->FetchAll($rs);     

     // -- CARI DATA APLIKASI -- //
        $sql = "select * from global.global_app order by app_nama asc";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA);
        $dataApp = $dtaccess->FetchAll($rs); 


     // --- cari Klinik ---
        $sql = "select dep_id,dep_nama from global.global_departemen order by dep_id";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataKlinik = $dtaccess->FetchAll($rs);   

        $sql = "select dep_nama from global.global_departemen where
        dep_id = '".$_POST["klinik"]."'";
        $rs = $dtaccess->Execute($sql);
        $namaKlinik = $dtaccess->Fetch($rs);

      //Nama Klinik
        $klinikHeader = "Klinik : ".$namaKlinik["dep_nama"]; 

     // --- cari Poli per departemen ---
        $sql = "select poli_id,poli_nama from global.global_auth_poli order by poli_nama";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataPoli= $dtaccess->FetchAll($rs);

        $sql="select * from global.global_validator";
        $rs=$dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataValidator=$dtaccess->FetchAll($rs);



     // --- cari auth gedung ---
        $sql = "select * from global.global_gedung_rawat order by gedung_rawat_nama";
        $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
        $dataGedung= $dtaccess->FetchAll($rs); 

       //Data Satuan Kerja

        $sql = "select * from hris.hris_struktural where struk_is_lowest = 'y' order by struk_tree";
        $rs = $dtaccess->Execute($sql);
        $dataSatker = $dtaccess->FetchAll($rs);

        $kembali = "hakakses_view.php?satker=".$_GET["satker"]."&kembali=".$_POST["klinik"]."";

        ?>


        <!DOCTYPE html>
        <html lang="en">
        <?php require_once($LAY."header.php"); ?>
        <script language="Javascript">
          function GantiPassword(frm, elm)
          {
           if(elm.checked){
            frm.usr_password.disabled = false;
            frm.usr_password2.disabled = false;
            frm.usr_password2.style.backgroundColor = '#FFFFFF';
            frm.usr_password.style.backgroundColor = '#FFFFFF';   
            frm.usr_password.style.borderColor = '#c2c6d3';   
            frm.usr_password2.style.borderColor = '#c2c6d3';
            frm.usr_password.focus();
          } else {
            frm.usr_password.disabled = true;
            frm.usr_password2.disabled = true;
            frm.usr_password2.style.backgroundColor = '#e2dede';
            frm.usr_password.style.backgroundColor = '#e2dede';
            frm.usr_password.style.borderColor = '#c2c6d3';   
            frm.usr_password2.style.borderColor = '#c2c6d3';
          }
        }

        function CheckDataSave(frm) 
        {  
          if(!frmEdit.pgw_nama.value){
            alert('Pegawai Harus Dipilih');
            frmEdit.pgw_nama.focus();
            return false;
          }

          if(!frmEdit.usr_loginname.value){
            alert('Nama Login Harus Diisi');
            frmEdit.usr_loginname.focus();
            return false;
          }

          if (frmEdit.is_password.checked)
          {
            if(!frmEdit.usr_password.value){
              alert('Password Harus Diisi');
              frmEdit.usr_password.focus();
              return false;
            }

            if(!frmEdit.usr_password2.value){
              alert('Ulangi Password Harus Diisi');
              frmEdit.usr_password2.focus();
              return false;
            }

          }  
          document.frmEdit.submit();  
        }
      </script>

      <body class="nav-md">
        <div class="container body">
          <div class="main_container">

            <?php require_once($LAY."sidebar.php"); ?>

            <!-- top navigation -->
            <?php require_once($LAY."topnav.php"); ?>
            <!-- /top navigation -->

            <!-- page content -->
            <div class="right_col" role="main">
              <div class="">
                <div class="page-title">
                  <div class="title_left">
                    <h3>Manajemen</h3>
                  </div>
                </div>

                <div class="clearfix"></div>

                <div class="row">

   
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <div class="x_panel">
                        <div class="x_title">
                          <h2>Tanda Tangan</h2>
                          <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                          <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-12" for="first-name"><span class="required"></span>
                            </label>
                            <center>

                              <img hspace="2" height="100" name="original" id="original" style="cursor:pointer; margin-bottom:15px;<?=(file_exists($ttd)) ? '' : 'display: none;'?> " src="<?php echo $ttd; ?>" valign="middle" border="1"><br>
                              <!-- <label id="nama_ttd" style="<?=(file_exists($ttd)) ? '' : 'display: none;'?>"><?= $nama_ttd?></label> -->
                              <br>

                              <canvas class="canvas" id="pasien" style="<?=(file_exists($ttd)) ? 'display: none;' : ''?>"></canvas>
                              <br>

                              <!--    <input type="text" name="myText" id="myText" style="<?=(file_exists($ttd)) ? 'display: none;' : ''?>" /> -->
                              <button id="savettd" type="button" style="<?=(file_exists($ttd)) ? 'display: none;' : ''?>">Simpan</button> 
                              <button id="resetCanvas" type="button">Reset</button></br>


                            </center>

                          </div>
                        </div>
                        <div class="clearfix"></div> 
                        <div class="ln_solid"></div>
                      </div>
                    </div>
                    <form id="frmEdit" method="POST" enctype="multipart/form-data" class="form-horizontal form-label-left" action="<?php echo $_SERVER["PHP_SELF"]?>">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <div class="x_panel">
                        <div class="x_title">
                          <h2>Tanda Tangan</h2>
                          <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                          <div class="form-group">
                            <label class="control-label col-md-2 col-sm-2 col-xs-12" for="first-name"><span class="required"></span>
                            </label>
                            <center>
                              <img id="output" src="<?php echo $ttd; ?>"  height="150" width="150">


                              <input type="file" accept="image/*" onchange="loadFile(event)" name="gambar" required="required">
                              <!-- <input type="submit" name="submit" value="UPLOAD"> -->


                              <ol style='font-style: italic; font-size: 12px; color: red;'>
                                <li>Pilih File (browse)</li>
                                <li>File yang diperbolehkan - <b>images(png).</b></li>
                                <li>File kurang dari 100kb</li>
                              </ol>

                            </center>

                          </div>
                        </div>
                        <div class="clearfix"></div> 
                        <div class="ln_solid"></div>
                      </div>
                    </div>



                    <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-3">
                      <button id="btnUpdate" name="btnUpdate"; type="submit" value="Update"  class="btn btn-success">Update</button>
                      <button class="btn btn-Primary" type="button" onClick="window.history.back()">Kembali</button>                              
                    </div>

                    <?php 
                    echo $view->RenderHidden("usr_id","usr_id",$usrId);

                    echo $view->RenderHidden("klinik","klinik",$_POST["klinik"]);
                    echo $view->RenderHidden("usr_when_create","usr_when_create",$_POST["usr_when_create"]);
                    echo $view->RenderHidden("satker","satker",$_GET["satker"]);
                    ?>

                  </form>

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
      <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>


      <script type="text/javascript">

        if($("canvas#pasien").length > 0){
          var canvas = document.querySelector("canvas#pasien");
          var signaturePad = new SignaturePad(canvas);
        }

        var id = "<?php echo $usrId; ?>" ;

        $("#resetCanvas").click(function(e){
          const context = canvas.getContext('2d');
          context.clearRect(0, 0, canvas.width, canvas.height);

          $.post("simpan_ttd.php", {
            id : id,
            act : "delete",
          });

          $("canvas#pasien").css("display", "inline-block");
          $("button#savettd").css("display", "inline-block");
      // $("input#myText").css("display", "inline-block");

      $("img#original").css("display", "none");
      // $("label#nama_ttd").css("display", "none");
    });

        $("#savettd").click(function(e){
       // var x = document.getElementById("myText").value;

       var signature = signaturePad.toDataURL(); 

       $.ajax({
        url: "simpan_ttd.php",
        data :{
          foto: signature,
          id:id,

          act : "save",
        },
        method: "POST",
        success:function(){
          alert("Tanda Tangan Berhasil Disimpan");
              location.reload();
            }
          })    

     });

        function Clear_image() {
          c5_context.clearRect(1, 1, 600, 300);
        } 
        var loadFile = function(event) {
          var output = document.getElementById('output');
          output.src = URL.createObjectURL(event.target.files[0]);
        };

      </script>

      <?php require_once($LAY."js.php") ?>

    </body>
    </html>

