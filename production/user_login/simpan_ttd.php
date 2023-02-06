
<?php 

  	require_once("../penghubung.inc.php");
     require_once($LIB."login.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."encrypt.php");
   //  require_once($LIB."expAJAX.php");
     require_once($LIB."tampilan.php");

     // Inisialisasi Lib
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $enc = new textEncrypt();
     $userData = $auth->GetUserData();
     $userName = $auth->GetUserName();
     $userId = $auth->GetUserId();
     $depId = $auth->GetDepId();
     $poliId = $auth->IdPoli();
     $tglSekarang = date("d-m-Y");
     $now = date("Y-m-d H:i:s");
     $depLowest = $auth->GetDepLowest();

	// $result = array();
	// $imagedata = base64_decode($_POST['img_data']);
	
	// $filename = md5(date("dmYhisA"));
	// //Location to where you want to created sign image
	// $file_name = 'asset_ttd/'.$filename.'.png';
	// file_put_contents($file_name,$imagedata);
	// $result['status'] = 1;
	// $result['file_name'] = $file_name;
	// echo json_encode($result);
	$data_uri = $_POST['foto'];
	$id = $_POST['id'];
	// $nama_ttd = $_POST['nama_ttd'];
	$filename = md5(date("dmYhisA"));
	$encoded_image = explode(",", $data_uri)[1];
	$decoded_image = base64_decode($encoded_image);
	 file_put_contents( '../gambar/asset_ttd/'.$id.'.jpg', $decoded_image);
	    // $sql = "UPDATE klinik.klinik_perawatan set rawat_nama_ttd = '$nama_ttd' where rawat_id ='$id' ";
     //  $s = $dtaccess->Execute($sql);

	
?>

