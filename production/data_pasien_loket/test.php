<?php    
     // LIBRARY
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."currency.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."expAJAX.php");
  	 require_once($LIB."tampilan.php");	
     
     //INISIALISASI LIBRARY
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
     $dtaccess = new DataAccess();
     $auth = new CAuth();
     $depNama = $auth->GetDepNama(); 
     $userName = $auth->GetUserName();
     $enc = new textEncrypt();     
  	 $depId = $auth->GetDepId();
  	 
	$sql = "select dep_konf_reg_no_rm_depan,dep_alamat_ip_peserta,dep_id_bpjs,dep_secret_key_bpjs,dep_kode_rs from global.global_departemen";
	$konf = $dtaccess->Fetch($sql);
	$norm_depan = $konf['dep_konf_reg_no_rm_depan'];

	# BPJS 
	$ID = $konf["dep_id_bpjs"];
	date_default_timezone_set('UTC');
  	$t=time();
  	$data = "$ID&$t";
    $secretKey = $konf["dep_secret_key_bpjs"];
   	$signature = base64_encode(hash_hmac('sha256', utf8_encode($data), utf8_encode($secretKey), true));
   	 if($_POST["btnLanjut"]){   
		# cek kepesertaan bpjs 
		 //echo "cek bpjs sekarang";
		$uri = $konf["dep_alamat_ip_peserta"]; //"http://dvlp.bpjs-kesehatan.go.id:8081/Vclaim-rest"
		$completeurl = "$uri/referensi/poli/".$_POST["poli"];

		
		$session = curl_init($completeurl);
		$arrheader =  array(
			'X-cons-id: '.$ID,
			'X-timestamp: '.$t,
			'X-signature: '.$signature,
			'Content-Type: Application/x-www-form-urlencoded',
				);
		curl_setopt($session, CURLOPT_HTTPHEADER, $arrheader);
		curl_setopt($session, CURLOPT_POST, TRUE);
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, "GET"); 
		curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);		
		
		$response = curl_exec($session);
	//	echo $response;		
	 }
?>
<form name="frmView" action="<?php echo $_SERVER["PHP_SELF"]?>" method="POST" >
<table cellspacing="0" width="100%">
                        <tr>                                                   
                            <td class="column-title">Nama Poli</td>              
                        </tr>
                          <tr class="even pointer">
                              <td ><?php echo $view->RenderTextBox("poli","poli",30,200,$_POST["poli"],false,false);?></td>
							  <td ><input type="submit" name="btnLanjut" id="btnLanjut"value="Cari" class="pull-right btn btn-primary"></td>
                          </tr>
                    </table>
</form>					
<?php if($_POST["btnLanjut"]){ ?> 					
<table cellspacing="0" width="100%">
                        <tr>                                                   
                            <td class="column-title">Deskripsi</td>              
                        </tr>
                          <tr class="even pointer">
                              <td ><?php echo $response;?></td>
                          </tr>
                    </table>		
<? } ?>				