<?php
	require_once("../penghubung.inc.php");
	require_once($LIB."login.php");
	require_once($LIB."encrypt.php");
	require_once($LIB."datamodel.php");
	require_once($LIB."dateLib.php");
	require_once($LIB."currency.php");
	require_once($LIB."expAJAX.php");
	require_once($LIB."tampilan.php");

	$view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
	$enc = new textEncrypt();     
	$auth = new CAuth();
	$table = new InoTable("table","100%","left");
	$userId = $auth->GetUserId();
	$userName = $auth->GetUserName();
	$userData = $auth->GetUserData();
	$depNama = $auth->GetDepNama();
	$depId = $auth->GetDepId();

	$sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
  $lokasi = $ROOT."/gambar/img_cfg";   
  if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
  if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
 
  if($konfigurasi["dep_logo"]!="n") {
  $fotoName = $lokasi."/".$konfigurasi["dep_logo"];
  } elseif($konfigurasi["dep_logo"]=="n") { 
  $fotoName = $lokasi."/default.jpg"; 
  } else { $fotoName = $lokasi."/default.jpg"; }

	$sql = "SELECT cust_usr_id, cust_usr_nama, cust_usr_kode_tampilan, cust_usr_tanggal_lahir, cust_usr_no_hp, cust_usr_jenis_kelamin FROM global.global_customer_user WHERE cust_usr_kode != '100' ORDER BY cust_usr_kode";
	$data = $dtaccess->FetchAll($sql);

	$sql = "SELECT COUNT(cust_usr_kode) AS total FROM global.global_customer_user WHERE cust_usr_kode != '100'";
	$dataTotal = $dtaccess->Fetch($sql);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Pasien</title>
	</head>
	<link href="<?php echo $ROOT; ?>assets/css/print_kwitansi.css" rel="stylesheet">
	<body onload="window.print()">
		<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
		  <tr>
		    <td align="center"><img src="<?php echo $fotoName ;?>" height="75"> </td>
		    <td align="center" bgcolor="#CCCCCC" id="judul"> 
		     	<span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
					<span class="judul3">
					<?php echo $konfigurasi["dep_kop_surat_1"]?></span><br>
		    	<span class="judul4">       
			  	<?php echo $konfigurasi["dep_kop_surat_2"]?></span>
			  </td>  
		  </tr>
		</table>
		<br>
		<table border="0" cellpadding="2" cellspacing="0" style="align:left" width="100%">     
	    <tr>
	      <td width="100%" style="text-align:center;font-size:24px;font-family:sans-serif;font-weight:bold;" class="tablecontent">DATA PASIEN</td>
	    </tr>
	    <tr>
	      <td>Total Data Pasien : <?= $dataTotal['total'] ?></td>
	    </tr>
	  </table>
	  <br>
	  <br>
	  <table width="100%" border="1" cellpadding="0" cellspacing="0">
	  	<thead>
	  		<tr>
	  			<th>No</th>
	  			<th>No RM</th>
	  			<th>Nama Pasien</th>
	  			<th>Alamat</th>
	  			<th>Jenis Kelamin</th>
	  			<th>No HP</th>
	  		</tr>
	  	</thead>
	  	<tbody>
	  		<?php foreach ($data as $key => $value): ?>
		  		<tr>
		  			<td><?= $key+1 ?></td>
		  			<td><?= $value['cust_usr_kode_tampilan'] ?></td>
		  			<td><?= $value['cust_usr_nama'] ?></td>
		  			<td><?= $value['cust_usr_alamat'] ?></td>
		  			<td><?= ($value['cust_usr_jenis_kelamin'] == 'P') ? 'Perempuan' : 'Laki - Laki'; ?></td>
		  			<td><?= $value['cust_usr_no_hp'] ?></td>
		  		</tr>
	  		<?php endforeach ?>
	  	</tbody>
	  </table>
	</body>
</html>