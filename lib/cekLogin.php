<?php
class cekLogin{
  function __construct(){
	require_once("login.php");

	$auth = new CAuth();
  }
  function cekLogin(){	
	//AUTHENTIFIKASI
 	 if(!$auth->IsAllowed("fo_registrasi",PRIV_CREATE)){
          die("Maaf anda tidak berhak membuka halaman ini....");
          exit(1);
     } else 
      if($auth->IsAllowed("fo_registrasi",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login/login.php?msg=Login First'</script>";
          exit(1);
     }
  }
}
?>