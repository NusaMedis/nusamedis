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
    /* if(!$auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)){
          echo"<script>window.document.location.href='".$ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */

                                                                  
     $_x_mode = "New";
     $thisPage = "kasir_view.php";
	
	if($_GET["id"]) {
		$sql = "select a.* , b.* ,c.item_nama , c.item_tipe_jenis,d.*,e.jenis_nama, f.poli_nama, a.cust_usr_nama as nama
            from apotik.apotik_penjualan a 
            left join apotik.apotik_penjualan_detail b on b.id_penjualan = a.penjualan_id
            left join logistik.logistik_item c on b.id_item = c.item_id 
            left join global.global_customer_user d on a.id_cust_usr = d.cust_usr_id
            left join global.global_jenis_pasien e on a.id_jenis_pasien = e.jenis_id
            left join global.global_auth_poli f on f.poli_id = a.id_poli
            where a.penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
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
<title>Cetak Pengeluaran Obat Apotik</title>

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
	    font-size: 10px;
	    font-weight: bolder;
    }
    .judul2 {
	    font-size: 8px;
	    font-weight: bolder;
    }
    .judul3 {
	    font-size: 6px;
	    font-weight: normal;
    }
    
    .judul4 {
	    font-size: 5px;
	    font-weight: bold;
	    background-color : #CCCCCC;
	    text-align : center;
    }
    .judul5 {
	    font-size: 5px;
	    font-weight: bold;
	    background-color : #040404;
	    text-align : center;
	    color : #FFFFFF;
    } 
    
   
</style>

</head>
<body>


<table width="40%" border="0" style="font-size:10px;">
<tr>
<!--- table kiri --->
<td width="100%">
        <table border="0" style="font-size:10px;" width="100%" valign="top"> 
        <tr valign="top">
          <td width="40%">COPY FAKTUR OBAT</td>
          <td align="center"></td>
          <td></td>
        </tr> 
        <tr valign="top">
          <td width="40%"></td>
          <td align="center"></td>
          <td></td>
        </tr> 

        <tr valign="top">
          <td width="40%">Tanggal</td>
          <td align="center">:</td>
          <td><?php echo date($dataTable[0]["penjualan_create"]);?></td>
        </tr> 
        <tr>
        <td width="40%">No. Faktur</td>
        <td>:</td>
        <td><?php echo $dataTable[0]["penjualan_nomor"] ;?></td>
        </tr>
        <tr>
          <td width="40%">No RM</td>
          <td align="center">:</td>
          <td><?php echo $dataTable[0]["cust_usr_kode"];?></td>
        </tr>
        
                             
        <tr>
        <td width="40%">Nama Pasien</td>
        <td width="1%"align="center">:</td>
        <td><?php echo $dataTable[0]["nama"];?></td>
        </tr>
        <tr>
        <td width="30%">Tanggal Lahir</td>
        <td width="1%"align="center">:</td>
        <td><?php echo format_date($dataTable[0]["cust_usr_tanggal_lahir"]);?></td>
        </tr>
        <tr>
        <td width="30%">Jenis Kelamin</td>
        <td width="1%"align="center">:</td>
        <td><?php 
        if ($dataTable[0]["cust_usr_jenis_kelamin"]=='P'){
        $jk="Perempuan";
        } else { $jk="Laki - Laki"; }
        echo $jk;?></td>
        </tr>

        <tr valign="top">
          <td width="40%">Alamat</td>
          <td align="center">:</td>
          <td><?php echo $dataTable[0]["cust_usr_alamat"];?></td>
        </tr>
    
        <tr>
          <td width="40%">Cara Bayar</td>
          <td align="center">:</td>
          <td><?php echo $dataTable[0]["jenis_nama"];?></td>
        </tr>
 
        <?php if($POST["penjualan_keterangan"]){ ?>
        <tr>
        <td width="50%">Ditanggung Oleh</td>
        <td align="center">:</td>
        <td><?php echo $dataTable[0]["penjualan_keterangan"];?></td>
        </tr>
        <?php } ?>
        
        <tr>
          <td width="40%">Nama Dokter</td>
          <td align="center">:</td>
          <td><?php echo $dataTable[0]["dokter_nama"];?></td>
        </tr> 
        
        <?
        $sql = "select * from klinik.klinik_kamar where kamar_id = '".$dataTable[0]["id_kamar"]."'";
        $dataKamar = $dtaccess->Fetch($sql);
        //echo $sql;
        //die() ;
        ?>
        <?php if($dataTable[0]["poli_nama"]){ ?>
        <tr>
          <td width="40%">Poli</td>
          <td align="center">:</td>
          <td><?php echo $dataTable[0]["poli_nama"] ;?></td>
        </tr>
        <?php } ?>
        <?php if($dataKamar){ ?>
        <tr>
          <td width="40%">Kamar</td>
          <td align="center">:</td>
          <td><?php echo $dataKamar[0]["kamar_nama"] ;?></td>
        </tr> 
        <?php } ?>
        <tr>
        <td colspan="3">&nbsp;</td>
        </tr>        
        <?php 
        $cols = -2; 
        if($dataTable[0]["penjualan_biaya_resep"]) $cols = $cols + 1;
        if($dataTable[0]["penjualan_biaya_racikan"]) $cols = $cols + 1;
        if($dataTable[0]["penjualan_biaya_bhps"]) $cols = $cols + 1;
        if($dataTable[0]["penjualan_diskon"]) $cols = $cols + 1;
        if($dataTable[0]["penjualan_biaya_pembulatan"]) $cols = $cols + 1;
        ?>
        
        </table> 
</td>
<td width="100%" valign="top">
         <table border="0" style="font-size:10px;" valign="top">

        
         </table>
</td>
</tr>

<tr>
<td colspan="2">
 <hr>
</td>
</tr>

<tr>
<!--- table kanan --->

<td valign="top" width="100%" align="center" colspan="2">
        <table border="0" width="100%" style="font-size:10px;border-collapse:collapse;"> 
        <tr height="10">
        <td width="2%" style="border-bottom:1px solid black;" align="center">No </td>
        <td width="87%" style="border-bottom:1px solid black;"align="center">Nama Item</td>
<!--        <td width="17%" style="border-bottom:1px solid black;" align="center">Harga</td> -->
                <td></td>
        <td width="13%" style="border-bottom:1px solid black;" align="center">QTY</td>
        <td width="30%" style="border-bottom:1px solid black;" align="center">Total</td>
        </tr>
        	<?php for($i=0,$n=count($dataTable);$i<$n;$i++) { 
                   ?>
        <tr>
        <td align="center"><?php echo ($i+1); ?></td>
        <td align="center"><?php echo $dataTable[$i]["item_nama"];?></td>
        <td></td>
<!--        <td align="center"><?php echo currency_format($dataTable[$i]["penjualan_detail_harga_jual"]);?></td>-->
        <td align="center"><?php echo currency_format($dataTable[$i]["penjualan_detail_jumlah"],2);?></td>
        <td align="right">Rp. <?php echo currency_format($dataTable[$i]["penjualan_detail_total"]);?></td>
        <?php $totalHarga+=$dataTable[$i]["penjualan_detail_total"]; ?>
        </tr>
        <?php } ?>
        
        <?php if($n<5) { for($i=0;$i<(5-$n);$i++) { ?>
         <tr>
          <td colspan="3">&nbsp;</td>
          </tr>
        
        <?php } }?>
        
        <?php if($dataTable[0]["penjualan_biaya_resep"]!="0.00"){ ?>
        <tr>
        <td colspan="4" align="center">Biaya Resep</td>
        <td align="right">Rp. <?php echo currency_format($dataTable[0]["penjualan_biaya_resep"]) ;?></td>
        </tr>
        <?php }?>
        <?php if($dataTable[0]["penjualan_biaya_racikan"]!="0.00"){ ?>
        <tr>
        <td colspan="4" align="center">Biaya Racikan</td>
        <td align="right">Rp. <?php echo currency_format($dataTable[0]["penjualan_biaya_racikan"]) ;?></td>
        </tr>
        <?php }?>
        
        <?php if($dataTable[0]["penjualan_biaya_bhps"]!="0.00"){ ?>
        <tr>
        <td colspan="4" align="center">Biaya BHPS</td>
        <td align="right">Rp. <?php echo currency_format($dataTable[0]["penjualan_biaya_bhps"]) ;?></td>
        </tr>
        <?php }?>
        
        <?php if($dataTable[0]["penjualan_diskon"]!="0.00"){ ?>
        <tr>
        <td colspan="4" align="center">Diskon</td>
        <td align="right">-Rp. <?php echo currency_format($dataTable[0]["penjualan_diskon"]) ;?></td>
        </tr>
        <?php }?>
        
        
        
        <?php $totalAfterDiskon = ($dataTable[0]["penjualan_total"] +  $dataTable[0]["penjualan_biaya_resep"] + $dataTable[0]["penjualan_biaya_racikan"] + $dataTable[0]["penjualan_biaya_bhps"] ) - $dataTable[0]["penjualan_diskon"]; ?> 
        <!--<tr>
        <td colspan="4" align="center" style="font-size:10px;">Total</td>
        <td align="center"><span style="font-size:10px;"><?php echo "Rp.&nbsp;".currency_format($totalAfterDiskon); ?></span><span style="margin:-7px 0 0 2px;position:absolute;"></span></td>
        </tr>--> 
        
        <?php if($dataTable[0]["penjualan_biaya_pembulatan"]!="0.00"){ ?>
        <tr>
        <td colspan="4" align="right">Pembulatan</td>
        <td>&nbsp;&nbsp;</td>
        <td align="right"> Rp. <?php echo currency_format($dataTable[0]["penjualan_biaya_pembulatan"]) ;?></td>
        </tr>
        <?php }?>
           
        <?php $totalGrand = ($dataTable[0]["penjualan_total"] +  $dataTable[0]["penjualan_biaya_resep"] + $dataTable[0]["penjualan_biaya_racikan"] + $dataTable[0]["penjualan_biaya_bhps"] ) - $dataTable[0]["penjualan_diskon"]; ?> 
        <tr>
        <td colspan="3" align="right" style="font-size:14px;">Total Akhir</td>
        <td>&nbsp;&nbsp;</td>
        <td align="right"><span style="font-size:14px;"><?php echo "Rp.&nbsp;".currency_format($totalGrand); ?></span><span style="margin:-7px 0 0 2px;position:absolute;"></span></td>
        </tr> 
       <tr>
        <td colspan="2">
        </td>
        </tr>
 <!--       <tr height="20">
        <td colspan="5" align="left" style="font-size:10px;">Terbilang : <?php echo terbilang($totalGrand); ?> Rupiah</td>
        </tr>-->
        </table>
</td>

</tr>
<tr>
<td colspan="2">
 <hr>
</td>
</tr>
<tr>
<!--- table kiri --->
<td colspan="2" width="100%">
        <table border="0" width="100%" style="font-size:10px;border-collapse:collapse;">
  <tr>
  <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
  <td colspan="5" align="right">Yang Membeli,<br><br><br><br><br>(<?php echo $dataTable[0]["cust_usr_nama"];?>)</td>
  <td colspan="5" align="right">Yang Menerima,<br><br><br><br><br>(<?php echo $userData["name"];?>)</td>
  </tr>
    <tr>
  <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <tr>
  <td colspan="5" align="center"><i>Terimakasih atas kunjungan anda</i></td>
  </tr>
  <tr>
  <td colspan="5" align="center"><i>Produk yang sudah dibeli tidak dapat ditukar atau di kembalikan.</i></td>
  </tr>
  <tr>

    <tr>
  <td colspan="2" align="right">Reprinted on <?php echo date('d-m-Y H:i:s');?> </td>
  </tr>

</table>
</td>
</tr>

</table>




</body>
</html>
