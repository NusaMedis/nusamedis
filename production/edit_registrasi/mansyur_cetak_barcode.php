<?php 
    	require_once("../penghubung.inc.php");
    	require_once($LIB."bit.php");
    	require_once($LIB."login.php");
    	require_once($LIB."encrypt.php");
    	require_once($LIB."datamodel.php");
    	//require_once($LIB."barcode.php");
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
	
	$sql = "select a.cust_usr_jenis_kelamin, a.cust_usr_tanggal_lahir, a.cust_usr_kode,
	a.cust_usr_foto,a.cust_usr_nama,a.cust_usr_alamat as alamat1,a.cust_usr_suami,
	((current_date - a.cust_usr_tanggal_lahir)/365) as umur, c.id_poli, d.poli_nama, 
	c.reg_status_cetak_kartu,a.cust_usr_nama_kk, c.reg_kode_trans 
  		from   global.global_customer_user a  
  		left join  klinik.klinik_registrasi c on c.id_cust_usr = a.cust_usr_id
  		left join   global.global_auth_poli d on d.poli_id = c.id_poli
  		where c.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
  	
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

//update tracer barcode

 //update klinik registrasi
     $sql = "update klinik.klinik_registrasi set reg_tracer_barcode_besar='y' where
              reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"]);
     $rs = $dtaccess->Execute($sql);

//urusan barcode
$text = $dataPasien["cust_usr_kode"];
$size = '100'; // (Default)
$type='Code128'; // (Default)
$orientation= 'vertical';// (Default)
$print= 'false'; //tulisannya muncul



?>
<html>
<head>

<title>Cetak Barcode Pasien</title>

<style type="text/css">
body {
    font-family:Arial, Verdana, Helvetica, sans-serif;
    margin: 0px;
    font-size:50px;
}

#dv_nama {
	position:absolute;
	top:0px;
	left:50px;
	z-index:1;
	font-size: 13px;
	font-weight:bolder;
}


#dv_kode {
	position:absolute;
	top:10px;
	left:50px;
	z-index:1;
	font-size: 12px;
	font-weight:bolder;
}


#dv_alamat {
	position:absolute;
	top:20px;
	left:50px;
	z-index:1;
	font-size: 11px;
}

#dv_barcode {
	position:absolute;
	top:30px;
	left:20px;
	z-index:1;
  font-size: 15px;
}

#dv_foto {
	position:absolute;
	top:23px;
	left:230px;
	z-index:1;
}

table{
font-size:12px;
}

.rotate90 {
    -webkit-transform: rotate(90deg);
    -moz-transform: rotate(90deg);
    -o-transform: rotate(90deg);
    -ms-transform: rotate(90deg);
    transform: rotate(90deg);
}
</style>



</head>
<body onLoad="window.print();">
<table align="center" border="0" style="width:10 cm; height:4cm; border: 0px solid black;">
		<tr>
			<td valign="top">
				<!--<div style="border:0px solid black; width:5 cm; height:2.5 cm;">
					<table border="0" style="width:5 cm; height:2.5cm; border: 0px solid black;">-->
					<table border="0" >   
					 <tr>
							<td rowspan="2" > 
              		<img alt="testing" src="libbarcode.php?text=<?=$text?>&orientation=<?=$orientation?>&size=<?=$size?>&codetype=<?=$type?>&print=<?=$print?>" />
							</td>
							<td align="center" style="text-align:center;font-size:19px;font-family:sans-serif;font-weight:bold;"><?php echo substr(strtoupper($dataPasien["cust_usr_nama"]),0,20); //$dataPasien["cust_usr_nama"];?></td>
             </tr>
          	<!-- <tr>

 							<td align="center" >
              <?php 
              		if ($obj) {
              			if ($check_error) {
              				echo "<font color='#FF0000'>".($obj->GetError())."</font>";
              			} else { ?>
              				<img src="<?php echo $ROOT;?>lib/barcode/image.php?code=<?=$barcode?>&style=<?=$style?>&type=<?=$type?>&width=<?=$width?>&height=<?=$height?>&xres=<?=$xres?>&font=<?=$font?>">
                      
                      <?php }
              		}
              	?>
                </td>
            </tr>
            -->
            <tr>

 							<td align="center" style="text-align:center;font-size:65px;font-family:sans-serif;font-weight:bold;font-stretch:extra-expanded;letter-spacing: 1px; ">
              <?php echo $dataPasien["cust_usr_kode"];?>
                </td>
            </tr>
				            
          </table>


<!--</div>-->
</td>
</tr>
</table>
</body>
</html>
     