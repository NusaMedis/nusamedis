<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/currency.php"); 
     
     // Inisialisasi Lib
	   $dtaccess = new DataAccess();
     $auth = new CAuth();
	   $depId = $auth->GetDepId(); 
  
if (!empty($_GET['q'])){
	if (ctype_digit($_GET['q'])) {
		$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['q']."' and lokasi_kecamatan='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota!='00' order by lokasi_nama";
		$d = $dtaccess->FetchAll($sql);
    echo "<option value=''>Pilih Kota/Kab</option>";
    for($i=0,$n=count($d);$i<$n;$i++) 
    {
//      echo '<option value="'.$d[$i]["lokasi_kabupatenkota"].'&prop=.'$_GET['q'].'">'.$d[$i]["lokasi_nama"].'</option>';

      echo '<option value="'.$d[$i]["lokasi_kabupatenkota"].'&prop='.$_GET["q"].'">'.$d[$i]["lokasi_nama"].'</option>';

    }
	}
 // echo $sql;
}

if (empty($_GET['kel'])){

	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
			$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop']."' and lokasi_kecamatan!='00' and lokasi_kelurahan='0000' and lokasi_kabupatenkota='".$_GET['kec']."' order by lokasi_nama";
			//echo $sql;
      $d = $dtaccess->FetchAll($sql);
      echo"<option selected value=''>Pilih Kecamatan</option>";
      for($i=0,$n=count($d);$i<$n;$i++) 
      {
        echo '<option value="'.$d[$i]["lokasi_kecamatan"].'&kec='.$d[$i]["lokasi_kabupatenkota"].'&prop='.$d[$i]["lokasi_propinsi"].'">'.$d[$i]["lokasi_nama"].'</option>';
      }

		}
	}
} else {
	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
			$sql = "SELECT * FROM global.global_lokasi where lokasi_propinsi='".$_GET['prop']."' and lokasi_kecamatan='".$_GET['kel']."' and lokasi_kelurahan!='0000' and lokasi_kabupatenkota='".$_GET['kec']."' order by lokasi_nama";
			$d = $dtaccess->FetchAll($sql);
      //echo $sql;
      echo"<option selected value=''>Pilih Kelurahan/Desa</option>";
      for($i=0,$n=count($d);$i<$n;$i++) 
      {
        echo '<option value="'.$d[$i]["lokasi_kode"].'">'.$d[$i]["lokasi_nama"].'</option>';
      }

		}
	}
}


/*
if (empty($_GET['kel'])){

	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
		include '../koneksi.php';
			$query = mysql_query("SELECT * FROM inf_lokasi where lokasi_propinsi=$_GET[prop] and lokasi_kecamatan!=0 and lokasi_kelurahan=0 and lokasi_kabupatenkota=$_GET[kec] order by lokasi_nama");
			echo"<option selected value=''>Pilih Kecamatan</option>";
			while($d = mysql_fetch_array($query)){
				echo "<option value='$d[lokasi_kecamatan]&kec=$d[lokasi_kabupatenkota]&prop=$d[lokasi_propinsi]''>$d[lokasi_nama]</option>";
			}
		}
	}
} else {
	if (!empty($_GET['kec']) and !empty($_GET['prop'])){
		if (ctype_digit($_GET['kec']) and ctype_digit($_GET['prop'])) {
		include '../koneksi.php';
			$query = mysql_query("SELECT * FROM inf_lokasi where lokasi_propinsi=$_GET[prop] and lokasi_kecamatan=$_GET[kel] and lokasi_kelurahan!=0 and lokasi_kabupatenkota=$_GET[kec] order by lokasi_nama");
			echo"<option selected value=''>Pilih Kelurahan/Desa</option>";
			while($d = mysql_fetch_array($query)){
				echo "<option value='$d[lokasi_kode]'>$d[lokasi_nama]</option>";
			}
		}
	}
}
*/




?>
