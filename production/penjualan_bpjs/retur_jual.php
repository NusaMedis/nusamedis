<?php
     require_once("../penghubung.inc.php");
     require_once($LIB."bit.php");
     require_once($LIB."login.php");
     require_once($LIB."encrypt.php");
     require_once($LIB."datamodel.php");
     require_once($LIB."dateLib.php");
     require_once($LIB."currency.php");
     require_once($LIB."tampilan.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     $depNama = $auth->GetDepNama();
     $depId = $auth->GetDepId();

 	//AUTHENTIFIKASI
 

                                                                  
     $_x_mode = "New";
     $thisPage = "penjualan_cetak.php";
	
	if($_GET["id"]) {
		$sql = "select a.* , b.* ,c.item_nama , c.item_tipe_jenis,d.*,e.jenis_nama, f.penjualan_nomor, f.dokter_nama 
            from logistik.logistik_retur_penjualan a 
            left join logistik.logistik_retur_penjualan_detail b on b.id_penjualan_retur = a.retur_penjualan_id 
            left join logistik.logistik_item c on b.id_item = c.item_id 
            left join apotik.apotik_penjualan f on f.penjualan_id = a.id_penjualan 
            left join global.global_customer_user d on f.id_cust_usr = d.cust_usr_id 
            left join global.global_jenis_pasien e on f.id_jenis_pasien = e.jenis_id

            where a.retur_penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
    //echo $sql;
    $dataTable = $dtaccess->FetchAll($sql);
          
		//$_POST["id_reg"] = $_GET["id_reg"]; 
		//$_POST["fol_jenis"] = $_GET["jenis"]; 
		//$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		//$_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
			
		$sql = "select * from klinik.klinik_folio where fol_lunas = 'n' and fol_jenis = ".QuoteValue(DPE_CHAR,$_POST["fol_jenis"])." and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"]);
		$dataFolio = $dtaccess->FetchAll($sql);

     }

	$lokasi = $ROOT."/gambar/img_cfg";
	                                                       
	   $sql = "select * from global.global_departemen where dep_id = ".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     if ($konfigurasi["dep_logo"])
       $fotoName = $ROOT."gambar/img_cfg/".$konfigurasi["dep_logo"];	
     else
       $fotoName = $ROOT."gambar/img_cfg/default.jpg";
  
?>                                        


<html>
<head>
<title>Cetak Retur Obat Apotik</title>

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>
 <style type="text/css">
 
    body {
	   font-family:      Arial, Verdana, Helvetica, sans-serif;
	   margin: 0px;
	    font-size: 10px;
    }
    
    .tableisi {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: none #000000 0px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    
    .tableisi td {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota {
	   font-family:      Verdana, Arial, Helvetica, sans-serif;
	   font-size:        10px;
	    border: solid #000000 1px; 
	    padding:4px;
	    border-collapse:collapse;
    }
    
    .tablenota .judul  {
	    border: solid #000000 1px; 
	    padding:4px;
    }
    
    .tablenota .isi {
	    border-right: solid black 1px;
	    padding:4px;
    }
    
    .ttd {
	    height:50px;
    }
    
    .judul {
	    font-size:      14px;
	    font-weight: bolder;
	    border-collapse:collapse;
    }
    
    
    .judul1 {
	    font-size: 12px;
	    font-weight: bolder;
    }
    .judul2 {
	    font-size: 14px;
	    font-weight: bolder;
    }
    .judul3 {
	    font-size: 12px;
	    font-weight: normal;
    }
    
    .judul4 {
	    font-size: 11px;
	    font-weight: bold;
	    background-color : #CCCCCC;
	    text-align : center;
    }
    .judul5 {
	    font-size: 11px;
	    font-weight: bold;
	    background-color : #040404;
	    text-align : center;
	    color : #FFFFFF;
    } 
    
   
</style>




</style>
</head>
<body>
<!-- <table width="25%" border="0" cellpadding="1" cellspacing="0" style="font-size:11px;">
  <tr>
    <td align="center"> 
     <strong><?php echo $konfigurasi["dep_nama"]?></strong><br>
		<?php echo $konfigurasi["dep_kop_surat_1"]?><br>       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></td>
  </tr>
  <tr>
  <td colspan="3">&nbsp;</td>
  </tr>
  
</table> -->
<table width="40%" border="0" style="font-size:10px;">
<tr valign="top">
    <td colspan="3" align="center" style="font-size:14px;">RETUR BARANG/OBAT MEDIK</td>
</tr> 
<tr valign="top">
    <td><br><br></td>
</tr> 
<tr>
    <td width="35%">No. Faktur Retur</td>
    <td width="1%">:</td>
    <td><?php echo $dataTable[0]["retur_penjualan_nomor"] ;?></td>
</tr>
<tr>
    <td >Tanggal</td>
    <td >:</td>
    <td><?php echo FormatTimeStamp($dataTable[0]["retur_penjualan_detail_create"]);?></td>
</tr>      
<tr>
    <td >No. Faktur Penjualan</td>
    <td>:</td>
    <td><?php echo $dataTable[0]["penjualan_nomor"] ;?></td>
</tr>
<tr>
    <td >No RM</td>
    <td >:</td>
    <td><?php echo $dataTable[0]["cust_usr_kode"];?></td>
</tr>                     
<tr>
    <td >Nama Pasien</td>
    <td align="center">:</td>
    <td><?php echo $dataTable[0]["cust_usr_nama"];?></td>
</tr>
<tr>
    <td >Alamat</td>
    <td align="center">:</td>
    <td><?php echo $dataTable[0]["cust_usr_alamat"];?></td>
</tr>
<tr>
    <td >Cara Bayar</td>
    <td align="center">:</td>
    <td><?php echo $dataTable[0]["jenis_nama"];?></td>
</tr>
<tr>
    <td >Nama Dokter</td>
    <td align="center">:</td>
    <td><?php echo $dataTable[0]["dokter_nama"];?></td>
</tr>
<tr>
    <td colspan="3">&nbsp;</td>
</tr>        
<tr><td colspan="3">                

        <table border="0" width="100%" style="font-size:10px;border-collapse:collapse;"> 
        <tr height="10">
        <td width="2%" style="border-bottom:1px solid black;" align="center">No </td>
        <td width="87%" style="border-bottom:1px solid black;"align="center">Nama Obat</td>
<!--        <td width="17%" style="border-bottom:1px solid black;" align="center">Harga</td> -->
                <td></td>
        <td width="13%" style="border-bottom:1px solid black;" align="center">QTY</td>
        <td width="30%" style="border-bottom:1px solid black;" align="center">Total</td>
        </tr>
        	<?php for($i=0,$n=count($dataTable);$i<$n;$i++) { 
                   ?>
        <tr>
        <td align="center"><?php echo ($i+1); ?></td>
        <td align="left"><?php echo $dataTable[$i]["item_nama"];?></td>
        <td></td>
<!--        <td align="center"><?php echo currency_format($dataTable[$i]["retur_penjualan_detail_total"]);?></td>-->
        <td align="center"><?php echo currency_format($dataTable[$i]["retur_penjualan_detail_jumlah"]);?></td>
        <td align="right">Rp. <?php echo currency_format($dataTable[$i]["retur_penjualan_detail_grandtotal"]);?></td>
        <?php $totalHarga+=$dataTable[$i]["retur_penjualan_detail_grandtotal"]; ?>
        </tr>
        <?php } ?>
        
        <?php if($n<5) { for($i=0;$i<(5-$n);$i++) { ?>
         <tr>
          <td colspan="3">&nbsp;</td>
          </tr>
        
        <?php } }?>
                       
        <tr>
        <td colspan="3" align="right" style="font-size:10px;">Total Akhir</td>
        <td>&nbsp;&nbsp;</td>
        <td align="right"><span style="font-size:10px;"><?php echo "Rp.&nbsp;".currency_format($totalHarga); ?></span><span style="margin:-7px 0 0 2px;position:absolute;"></span></td>
        </tr> 
       <tr>
        <td colspan="2">
        </td>
        </tr>
 <!--       <tr height="20">
        <td colspan="5" align="left" style="font-size:10px;">Terbilang : <?php echo terbilang($totalHarga); ?> Rupiah</td>
        </tr>-->
        </table>
</td>

</tr>
<tr>
<td colspan="3">
 <hr>
</td>
</tr>
<tr>
<!--- table kiri --->
<td colspan="3">
        <table border="0" width="100%" style="font-size:10px;border-collapse:collapse;">
  <tr>
  <td>&nbsp;</td>
  </tr>
  <tr>
  <td align="right">Yang Menerima,<br><br><br><br><br>(<?php echo $userData["name"];?>)</td>
  </tr>
    <tr>
  <td >&nbsp;</td>
  </tr>
  
    <tr>
  <td align="center"><i>Terimakasih atas kunjungan anda</i></td>
  </tr>
  <tr>
  <td align="center"><i>Produk yang sudah dibeli tidak dapat ditukar atau di kembalikan.</i></td>
  </tr>
  </table>
</td>
</tr>

</table>




</body>
</html>
