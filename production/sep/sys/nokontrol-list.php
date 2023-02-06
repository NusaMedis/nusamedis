<?php 
require_once "api.php";


$no_kontrol =$_GET['no_kontrol']; 

$bpjs = new Bpjs();

echo $bpjs->listNoKontrol($no_kontrol);
?>