<?php 
	// LIBRARY
  require_once("../penghubung.inc.php");
  require_once($LIB."login.php");
  require_once($LIB."datamodel.php");
    
  //INISIALISASI LIBRARY
  $dtaccess = new DataAccess();
  $auth = new CAuth();
  $userId = $auth->GetUserId();


 	//AUTHENTIKASI
  if(!$auth->IsAllowed("man_ganti_password",PRIV_READ)){
      die("access_denied");
      exit(1);       
  } elseif($auth->IsAllowed("man_ganti_password",PRIV_READ)===1){
      echo"<script>window.parent.document.location.href='".$MASTER_APP."login/login.php?msg=Session Expired'</script>";
      exit(1);
  }

  $sql = "SELECT a.id_app, a.id_usr FROM global.global_auth_user_app a
		LEFT JOIN global.global_app b ON b.app_id = a.id_app 
		WHERE b.app_kode LIKE '%RAWAT JALAN%' AND a.id_usr = ".QuoteValue(DPE_CHAR,$userId);
  $rs = $dtaccess->FetchAll($sql);
  $count = count($rs);
//echo $count;

//die($sql);
 if ($count > 0) {
 	//header("location:index_rawat_jalan.php");  
 	header("location: ../pemeriksaan_irj/pemeriksaan_irj_view.php");  
 } else {
 	echo "
 		<script type='text/javascript' src='".$ROOT."assets/vendors/sweetalert/sweetalert.min.js'></script>
		<link rel='stylesheet' type='text/css' href='".$ROOT."assets/vendors/sweetalert/sweetalert.css'>
		<script type='text/javascript'>
		  setTimeout(function () {  
		   swal({
			title: 'Akses Ditolak',
			text:  'Anda tidak berhak membuka modul ini.',
			type: 'error',
			timer: 3000,
			showConfirmButton: true
		   });  
		  },100); 
		  window.setTimeout(function(){ 
		   window.close();
		  } ,3000); 
		</script>

	";
 }	

?> 
