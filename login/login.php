<?php
require_once("../penghubung.inc.php");
require_once("../" . $LIB . "lib/datamodel.php");
require_once("../" . $LIB . "lib/conf/database.php");
require_once("../" . $LIB . "lib/conf/db_depan.php");
require_once("../" . $LIB . "lib/dataaccess.php");
require_once("../" . $LIB . "lib/login.php");


$dtaccess = new DataAccess();
$auth = new CAuth();

$errorLogin = 0;
$_POST["cmbSystem"] = 1; //LOKET Di master global_app

if ($_POST["btnLogin"]) {
      $login = $auth->IsLoginOk($_POST["txtUser"], $_POST["txtPass"], $_POST["txtPoli"]);
      if ($login) {
            header("Location:" . $ROOT . "production/index.php");
      } else {
            $errorLogin = 1;
      }
      unset($_POST["txtUser"]);
      unset($_POST["txtPass"]);
      unset($login);
}

$enc = new textEncrypt();
$lokasi = $ROOT . "/gambar/img_cfg";
$fotoName = $lokasi . "/logonm.png";

?>
<!DOCTYPE html>
<html lang="en">

<head>
      <title>Nusa Medis</title>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/bootstrap/css/bootstrap.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/animate/animate.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/css-hamburgers/hamburgers.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/animsition/css/animsition.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/select2/select2.min.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/vendor/daterangepicker/daterangepicker.css">
      <!--===============================================================================================-->
      <link rel="stylesheet" type="text/css" href="login_asset/css/util.css">
      <link rel="stylesheet" type="text/css" href="login_asset/css/main.css">
      <!--===============================================================================================-->
      <script type="text/javascript" src="../production/assets/vendors/sweetalert/sweetalert.min.js"></script>
      <link rel="stylesheet" type="text/css" href="../production/assets/vendors/sweetalert/sweetalert.css">
</head>

<body style="background-color: #666666;" onload="myFunction()">

      <div class="limiter">
            <div class="container-login100">
                  <div class="wrap-login100">
                        <form class="login100-form validate-form" name="frmLogin" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>">
                              <span class="login100-form-title p-b-43">
                                    <img src="<?php echo $fotoName; ?>" style="width: 100%;"><br>

                              </span>


                              <div class="wrap-input100 validate-input" data-validate="Masukkan Username Terlebih Dahulu">
                                    <input class="input100" type="text" id="username" name="txtUser">
                                    <span class="focus-input100"></span>
                                    <span class="label-input100">Username</span>
                              </div>


                              <div class="wrap-input100 validate-input" data-validate="Masukkan Password Terlebih Dahulu">
                                    <input class="input100" type="password" id="password" name="txtPass">
                                    <span class="focus-input100"></span>
                                    <span class="label-input100">Password</span>
                              </div>

                              <div class="container-login100-form-btn">
                                    <button class="btn form-control" name="btnLogin" value="Login" style="background-color: rgb(42, 63, 84);">
                                          <label style="color: white;">Login</label>
                                    </button>
                              </div>
                              <br>
                              <!--
                              <center>©PT. SWA DIGITAL SOLUSINDO - <a href="http://www.swadigitalsolusindo.com" target="_blank">www.swadigitalsolusindo.com</a></center>-->
                              <input type="hidden" name="cmbSystem" value="1">
                        </form>
                        <div class="login100-more" style="background-image: url('login_asset/images/nusamedisbc.jpg');">
                              <!-- <img src="login_asset/images/dextra-logo.png" style="margin-left:35%; margin-top:22%;"> -->
                              <!-- <br> -->
                        </div>
                  </div>
            </div>
      </div>

      <?php
      if ($errorLogin == 1) {
            echo " 
                <script type='text/javascript'>
                setTimeout(function () {  
                     swal({
                          title: 'Gagal',
                          text:  'User name atau Password anda Salah',
                          type: 'error',
                          timer: 3000,
                          showConfirmButton: true
                          });  
                          },10); 
                          window.setTimeout(function(){ 
                               window.location.refresh;
                               } ,3000); 
                               </script>
                               ";
      } elseif (isset($_GET['msg']) == "Login First") {
            echo " 
                               <script type='text/javascript'>
                               setTimeout(function () {  
                                    swal({
                                         title: 'Silahkan Login',
                                         text:  '',
                                         type: 'success',
                                         timer: 1000,
                                         showConfirmButton: false
                                         });  
                                         },10); 
                                         window.setTimeout(function(){ 
                                              window.location.refresh;
                                              } ,3000); 
                                              </script>
                                              ";
      } elseif (isset($_GET['msg']) == "Session Expired") {
            echo " 
                                              <script type='text/javascript'>
                                              setTimeout(function () {  
                                                   swal({
                                                        title: 'Maaf, Session Ini Telah Berakhir Silahkan Login',
                                                        text:  '',
                                                        type: 'success',
                                                        timer: 1000,
                                                        showConfirmButton: false
                                                        });  
                                                        },10); 
                                                        window.setTimeout(function(){ 
                                                             window.location.refresh;
                                                             } ,3000); 
                                                             </script>
                                                             ";
      }

      ?>



      <!--===============================================================================================-->
      <script src="login_asset/vendor/jquery/jquery-3.2.1.min.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/vendor/animsition/js/animsition.min.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/vendor/bootstrap/js/popper.js"></script>
      <script src="login_asset/vendor/bootstrap/js/bootstrap.min.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/vendor/select2/select2.min.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/vendor/daterangepicker/moment.min.js"></script>
      <script src="login_asset/vendor/daterangepicker/daterangepicker.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/vendor/countdowntime/countdowntime.js"></script>
      <!--===============================================================================================-->
      <script src="login_asset/js/main.js"></script>

      <script>
            function myFunction() {
                  $('#ifrm').css('display', 'none');
            }
      </script>
</body>

</html>