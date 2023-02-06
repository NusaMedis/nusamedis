<?php
	// LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."tampilan.php");
	 
	//INISIALISAI AWAL LIBRARY
   	 $dtaccess = new DataAccess();
     $auth = new CAuth();
	 $depId = $auth->GetDepId();
	 $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $tahunTarif = $auth->GetTahunTarif();
     $userLogin = $auth->GetUserData();
		
	$auth_id = $dtaccess->GetTransID();
	$id_gedung = $_POST['id_gedung_rawat'];
	$usr_id = $_POST['usr_id'];

	//echo $usr_id;
    
	//cari global usr gedung
	$sql = "select * from global.global_auth_user_gedung
			where id_gedung = ".QuoteValue(DPE_CHAR,$id_gedung);
	$sql .=" and id_usr = ".QuoteValue(DPE_CHAR,$usr_id);
	//echo $sql;
	$auth = $dtaccess->Fetch($sql);
	$tot =  count($auth) ;

	echo $tot;

	if ($tot > 1) {
		echo "delete";
		$sql = "DELETE from global.global_auth_user_gedung";
		$sql .=" WHERE usr_gedung_id = ".QuoteValue(DPE_CHAR,$auth['usr_gedung_id']);
		$dtaccess->Execute($sql);
		
	} else if ($tot <= 1) {
		echo "insert";
		$sql = "insert into global.global_auth_user_gedung values('$auth_id','$id_gedung','$usr_id','$depId')";
		$dtaccess->Execute($sql);
	}
  

	 exit();      
	
?>