<?php
      require_once("penghubung.inc.php");
      require_once($ROOT."lib/login.php");
      require_once($ROOT."lib/encrypt.php");
  
    	$enc = new textEncrypt();
    	$auth = new CAuth();     
    	$dtaccess = new DataAccess();
    
      $login = $auth->IsLoginOk($_POST["txtUser"],$_POST["txtPass"]);
     
      $sql = "select b.id_app from global.global_auth_user_app b
             where id_usr=".QuoteValue(DPE_CHAR,$login["id"])."
             and id_app=".QuoteValue(DPE_NUMERIC,$_POST["cmbSystem"]);
      $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
      //echo $login;
      //die();
      $dataTable = $dtaccess->FetchAll($rs); 
     
     if($login && !$dataTable)
     {
      echo header("Location:../poli/menupelayanan.php?poli=4");
         exit();
     }
     
     if($login && $dataTable) {

        if($_POST["cmbSystem"]==1){
         //echo 'login true';//"<script>top.location.href='menupelayanan.php'</script>";    
         echo header("Location:../poli/menupelayanan.php?poli=4");   
			exit();         
        }
     } 
     elseif(!$login)
       {
         header("Location:../poli-mata.php?ref=11&msg=kode_eror01&user=".$_POST["txtUser"]);
       } 
     elseif(!$dataTable)
       {
         echo header("Location:../poli/menupelayanan.php?poli=4");
         exit();
       }
     
     unset($_POST["txtUser"]);
     unset($_POST["txtPass"]);
     unset($login);
     /*
     if($login==1){
         header("Location:login.php?msg=User Online");         
     } elseif($login==2) {
         header("Location:login.php?msg=Login Failed");
     } else {
          header("location:./klinik/index.php");
     }*/
?>
