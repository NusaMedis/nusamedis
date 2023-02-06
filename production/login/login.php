<?php
      require_once("../penghubung.inc.php");
      require_once("../lib/dataaccess.php");
      require_once("../lib/login.php");
      
      
      $dtaccess = new DataAccess();
      $auth = new CAuth();  
      $errorLogin=0;
      $_POST["cmbSystem"]=1; //LOKET Di master global_app

      if ($_POST["btnLogin"]) 
      { 
	    $login = $auth->IsLoginOk($_POST["txtUser"],$_POST["txtPass"],$_POST["txtPoli"]);
		  if($login) 
		  {
			 header("Location:".$ROOT."index_menu.php");       
		  } 
		  else
		  {     
			$errorLogin=1;
		   }   
      unset($_POST["txtUser"]);
      unset($_POST["txtPass"]);
      unset($login);
     }
     
    
?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

        <title></title>
	<!-- jQuery -->
    <script src="../production/assets/vendors/jquery/dist/jquery.min.js"></script>
	<!-- Bootstrap -->
    <link href="login_asset/css/bootstrap.min.css" rel="stylesheet">
	<!-- sweet alert -->
	<script type="text/javascript" src="../production/assets/vendors/sweetalert/sweetalert.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../production/assets/vendors/sweetalert/sweetalert.css">

    <!-- Custom Theme Style -->
    <link href="login_asset/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form name="frmLogin" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">   
              <h1>Login User</h1>
              <div>
				<input type="text" id="username" name="txtUser" class="form-control" placeholder="Username" required="" />
              </div>
              <div>
                <input type="password" id="password" name="txtPass" class="form-control" placeholder="Password" required="" />
              </div>
              <div>
				<input type="submit" name="btnLogin" value="Login" class="btn btn-info">
				<input type="hidden" name="cmbSystem" value="1">
			 </div>
            </form>
          </section>
        </div>
      </div>
    </div>
	
	<?php 
		if ($errorLogin==1){
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
		}elseif (isset($_GET['msg'])=="Login First"){
		echo " 
		<script type='text/javascript'>
		  setTimeout(function () {  
		   swal({
			title: 'Silahkan Login',
			text:  '',
			type: 'success',
			timer: 1000,
			showConfirmButton: true
		   });  
		  },10); 
		  window.setTimeout(function(){ 
		   window.location.refresh;
		  } ,3000); 
		</script>
		";
		}

	?>
	
  </body>
</html>
