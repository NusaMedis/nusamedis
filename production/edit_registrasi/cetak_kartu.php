<?php 
    	require_once("../penghubung.inc.php");
    	require_once($LIB."bit.php");
    	require_once($LIB."login.php");
    	require_once($LIB."encrypt.php");
    	require_once($LIB."datamodel.php");
    	require_once($LIB."barcode.php");
	    require_once($LIB."expAJAX.php");
    	require_once($LIB."tampilan.php");
    	
    	$dtaccess = new DataAccess();
    	$enc = new textEncrypt();                                 
    	$auth = new CAuth();
    	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
    	$depId = $auth->GetDepId();
  		$depNama = $auth->GetDepNama();
	    $plx = new expAJAX("");      

	if($_GET["id"] || $_GET["id_reg"]) { 
  //$_POST["cust_usr_id"]=$enc->Decode($_GET["id"]);	
//	$_POST["cust_usr_id"] = $_GET["id"];
	
	$sql = "select cust_usr_jenis_kelamin, cust_usr_tanggal_lahir, cust_usr_kode,
	cust_usr_foto,cust_usr_nama,cust_usr_alamat as alamat1,cust_usr_suami,
	((current_date - cust_usr_tanggal_lahir)/365) as umurns 
  		from   global.global_customer_user
  		where cust_usr_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
  	
   $rs = $dtaccess->Execute($sql,DB_SCHEMA_GLOBAL);
   $dataPasien = $dtaccess->Fetch($rs);

	//var_dump($dataPasien);
	//echo $sql;
	//echo "data".$dataPasien["cust_usr_nama"];
	if($dataPasien["cust_usr_foto"]){
  	$fotoPasien = $ROOT."/gambar/foto_pasien/".$dataPasien["cust_usr_foto"];
  	} else {
  		$fotoPasien = $ROOT."/gambar/foto_pasien/default.jpg"; 
		}
		
	//update status /
    $sql = "update klinik.klinik_registrasi set reg_status_cetak_kartu = 'y' where id_cust_usr = ".QuoteValue(DPE_CHAR,$_GET["id"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
    $dtaccess->Execute($sql);
     
	}
	
	// KONFIHURASI
	$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfigurasi = $dtaccess->Fetch($rs);
     
    if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
    if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
    $fotoName = $ROOT."/gambar/img_cfg/".$konfigurasi["dep_logo"];	
    $bg = $ROOT."/gambar/img_cfg/".$konfigurasi["dep_logo"];
     
    $sql = "select * from global.global_konfigurasi_kartu where id_dep =".QuoteValue(DPE_CHAR,$depId);
    $rs = $dtaccess->Execute($sql);
    $konfKartu = $dtaccess->Fetch($rs);
    $fotoKiri = $ROOT."kasir/images/konfigurasi_kartu/".$konfKartu["konf_kartu_pic_kiri"];
    $fotoKanan = $ROOT."kasir/images/konfigurasi_kartu/".$konfKartu["konf_kartu_pic_kanan"];
    $fotoBelakangKiri = $ROOT."kasir/images/konfigurasi_kartu/".$konfKartu["konf_kartu_pic_belakang_kiri"];
    $fotoBelakangKanan = $ROOT."kasir/images/konfigurasi_kartu/".$konfKartu["konf_kartu_pic_belakang_kanan"];
    $alamatPasien=substr(($dataPasien["alamat1"]),0,30);
	// --- bagian barcode --- //
	define (__TRACE_ENABLED__,false);
	define (__DEBUG_ENABLED__,false);  
									   

							  
	// Default value //
	if (!isset($output))  $output   = "png";
	if (isset($_GET["id"])) $barcode  = $dataPasien["cust_usr_kode"];
	if (!isset($type))    $type     = "C39";
	if (!isset($width))   $width    = "250";
	if (!isset($height))  $height   = "55";
	if (!isset($xres))    $xres     = "2";
	if (!isset($font))    $font     = "3";
//	if (isset($_GET["reg"])) $barcode1  = $_GET["reg"]; 
//	if (isset($_GET["id_reg"])) $barcode1  = $dataPasien["reg_kode_trans"];
//  echo "masuk".$_GET["reg"]; 
//     die();
  
  $border = "off";
	$drawtext = "off";
	$stretchtext = "on";
	//------------------------------------// 
					
	if (isset($barcode) && strlen($barcode)>0) {    
		$style  = BCS_ALIGN_RIGHT;					       
		$style |= ($output  == "png" ) ? BCS_IMAGE_PNG  : 0; 
		$style |= ($output  == "jpeg") ? BCS_IMAGE_JPEG : 0; 
		$style |= ($border  == "on"  ) ? BCS_BORDER 	  : 0; 
		$style |= ($drawtext== "on"  ) ? BCS_DRAW_TEXT  : 0; 
		$style |= ($stretchtext== "on" ) ? BCS_STRETCH_TEXT  : 0; 
		$style |= ($negative== "on"  ) ? BCS_REVERSE_COLOR  : 0; 

		$obj = new C39Object(230, 55, $style, $barcode);
		
		if ($obj) {
			if ($obj->DrawObject($xres)) {
				$check_error = 0;
			} else {
				$check_error = 1;
			}
		}
	}
// --- End bagian barcode --- // */

?>
<html>
<head>

<title>Cetak Barcode Pasien</title>

</head>
<body> 

<table align="left" border="0">
		<tr>
			<td valign="top">

					<table border="0" >  
          <br><br><br>
        
					 <tr>
							<td align="left"  style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;"><br><br><?php echo strtoupper($dataPasien["cust_usr_nama"]);?><br></td>
             </tr>
              <tr>
                 <td align="left" style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;">
              <?php echo $dataPasien["cust_usr_kode"];?> 
                </td>
              </tr> 
              <tr>
                <td align="left" style="text-align:left;font-size:14px;font-family:sans-serif;font-weight:bold;">
                <?php echo date('d/m/Y',strtotime($dataPasien["cust_usr_tanggal_lahir"]));?> 
              </tr>
          	<tr>

             
 							<td align="left" style="text-align:left;font-size:12px;font-family:sans-serif;font-weight:bold;">
              <?php 
              		if ($obj) {
              			if ($check_error) {
              				echo "<font color='#FF0000'>".($obj->GetError())."</font>";
              			} else { ?>
              				<img src="<?php echo $ROOT; ?>lib/barcode2/barcode.php?text=<?php echo $barcode ?>&print=true&size=30>">
                      <?php }
              		}
              	?>
                </td>
             	
            </tr>
           
          </table>

</td>
</tr>
</table>
</body>
</html>
     