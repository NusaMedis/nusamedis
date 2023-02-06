<?php 
require_once "api.php";

$tglAwal = $_GET['tgl_awal']; 
$tglAkhir =$_GET['tgl_akhir']; 

$filter =$_GET['filter']; 

$bpjs = new Bpjs();

echo $bpjs->listKontrol($tglAwal,$tglAkhir,$filter);
?>