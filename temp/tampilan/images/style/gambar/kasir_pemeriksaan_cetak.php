<?php
     require_once("penghubung.inc.php");
     require_once($ROOT."lib/bit.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($APLICATION_ROOT."lib/tampilan.php");
     
     
     $view = new CView($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING']);
	$dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $userData = $auth->GetUserData();
     $userId = $auth->GetUserId();
     
  //ambil nama poli dan id
  /*$sql = "select b.poli_nama, b.poli_id from global.global.global_auth_user a left join global.global.global_auth_poli b 
          on a.usr_poli=b.poli_id where a.usr_id = ".QuoteValue(DPE_NUMERIC,$userId);    
  $rs_edit = $dtaccess->Execute($sql);
  $row_edit = $dtaccess->Fetch($rs_edit);  
  
  
  $poliNama = $row_edit["poli_nama"];
	$poliID = $row_edit["poli_id"];
	*/
	
 	if(!$auth->IsAllowed("fo_pembayaran_pemeriksaan",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("fo_pembayaran_pemeriksaan",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }

     $_x_mode = "New";
     $thisPage = "kasir_view.php";
	
	if($_GET["id_reg"] || $_GET["jenis"]) {
		$sql = "select cust_usr_nama,cust_usr_kode,b.cust_usr_jenis_kelamin,cust_usr_alamat,
             ((current_date - cust_usr_tanggal_lahir)/365) as umur,  a.id_cust_usr
            from klinik.klinik_registrasi a join global.global_customer_user b on a.id_cust_usr = b.cust_usr_id 
            where a.reg_id = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and a.id_dep=".QuoteValue(DPE_CHAR,$depId);
           
    $dataPasien= $dtaccess->Fetch($sql);
     echo $sql;      
		$_POST["id_reg"] = $_GET["id_reg"]; 
		$_POST["fol_jenis"] = $_GET["jenis"]; 
		$_POST["id_cust_usr"] = $dataPasien["id_cust_usr"];
		$_POST["cust_usr_kode"] = $dataPasien["cust_usr_kode"];
		
		$sql = "select * from klinik.klinik_folio where
           ( fol_jenis like '%R%' or fol_jenis like '%T%' ) and  fol_lunas = 'n' and id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
		$dataFolio = $dtaccess->FetchAll($sql);

		$sql = "select * from global.global_kwitansi where id_reg = ".QuoteValue(DPE_CHAR,$_GET["id_reg"])." and id_dep=".QuoteValue(DPE_CHAR,$depId);
		$datakwitansi= $dtaccess->Fetch($sql);
		
    $_POST["kwitansi_id"] = $datakwitansi["kwitansi_id"];
    $_POST["kwitansi_nomor"] = $datakwitansi["kwitansi_nomor"];
		

          
          if(!$datakwitansi){
          $sql = "select max(kwitansi_nomor) as kode from global.global_kwitansi where id_dep =".QuoteValue(DPE_CHAR,$depId);
          $lastKode = $dtaccess->Fetch($sql);
          $_POST["kwitansi_nomor"] = str_pad($lastKode["kode"]+1,6,"0",STR_PAD_LEFT);
     
        $dbTable = "global.global_kwitansi";
			
				$dbField[0] = "kwitansi_id";   // PK
				$dbField[1] = "kwitansi_nomor";
				$dbField[2] = "id_reg";
				$dbField[3] = "id_dep";
				
        if(!$_POST["kwitansi_id"]) $_POST["kwitansi_id"] = $dtaccess->GetNewID("global.global_kwitansi","kwitansi_id");	  
				$dbValue[0] = QuoteValue(DPE_NUMERIC,$_POST["kwitansi_id"]);
				$dbValue[1] = QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"]);
				$dbValue[2] = QuoteValue(DPE_CHAR,$_POST["id_reg"]);
        $dbValue[3] = QuoteValue(DPE_CHAR,$depId);
        	
				$dbKey[0] = 0; // -- set key buat clause wherenya , valuenya = index array buat field / value
				$dtmodel = new DataModel($dbTable,$dbField,$dbValue,$dbKey);
				
				$dtmodel->Insert() or die("insert error"); 
				
				unset($dtmodel);
				unset($dbField);
				unset($dbValue);
				unset($dbKey);
        }
    
        //update folio 
        $sql = "update klinik.klinik_folio set fol_lunas = 'y' , fol_nomor_kwitansi = ".QuoteValue(DPE_CHAR,$_POST["kwitansi_nomor"])." where id_reg = ".QuoteValue(DPE_CHAR,$_POST["id_reg"])." and id_dep =".QuoteValue(DPE_CHAR,$depId)." and (fol_jenis like '%T%' or fol_jenis like '%R%')";
        $dtaccess->Execute($sql);
        
     }

	$fotoName = $APLICATION_ROOT."gambar/logo_kasir.png";
	
	   $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);
     
     if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
     if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     $fotoName = $APLICATION_ROOT."gambar/img_cfg/".$konfigurasi["dep_logo"];	

?>

<html>
<head>

<title>Cetak Kwitansi Pemeriksaan</title>

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

<?php echo $view->InitUpload(); ?>

<script>
$(document).ready( function() {
	window.print();
});
      
</script>
</head>

<body>
<!--
<table width="1120" border="0" cellpadding="2" cellspacing="1" align="center">
	<tr>
  <td align="left" valign="top" width="5%">--><!--<img src="<?php echo $ROOT;?>com/madiun.jpg" width="130px" height="65px">-->
  <!--</td>
	<td align="left" valign="top" style="font-size:9px; letter-spacing: 2px" width="85%">PEMERINTAH KOTA MADIUN<BR>RUMAH SAKIT UMUM DAERAH<BR>Jl. Campursari No. 12 Telp. / Fax. (0351)481314<br>MADIUN</td>
	</tr>
<tr>
		<td align="center" colspan="2"><hr color="black" width="100%"></td></tr>
</table>
<table width="1120" border="0" cellpadding="2" cellspacing="1" align="center">
	<tr>
	<td align="center" valign="top" width="100%" style="font-size:10px"><u>KWITANSI PEMBAYARAN</u></td>
	</tr>
</table>  -->


<table width="100%" border="1" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td align="center"><img src="<?php echo $fotoName ;?>" width="<?php echo $lebar;?>" height="<?php echo $panjang;?>"> </td>
    <td align="center" bgcolor="#CCCCCC" id="judul"> 
     <span class="judul2"> <strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3">
		<?php echo $konfigurasi["dep_kop_surat_1"]?><br>       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td>
  </tr>
  <tr>
    <td colspan="2" class="judul4">KWITANSI PEMBAYARAN PEMERIKSAAN</td> 
  </tr>
</table>

<table width="100%" border="0" style="font-size:10px;">
<tr>
<!--- table kiri --->
<td width="50%">
<table border="0" style="font-size:10px;" width="100%" valign="top"> 
<tr>
<td>No. Kwitansi</td>
<td>:</td>
<td><?php echo $_POST["kwitansi_nomor"];?></td>
</tr>
<tr>
<td>Tgl / Waktu</td>
<td align="center">:</td>
<td><?php echo date("d-m-Y H:i:s");?></td>
</tr>
<tr>
<tr>
<td width="20%">Nama Pasien</td>
<td width="1%"align="center">:</td>
<td><?php echo $dataPasien["cust_usr_nama"];?></td>
</tr>
<tr>
<td >No RM</td>
<td align="center">:</td>
<td><?php echo $dataPasien["cust_usr_kode"];?></td>
</tr>
<tr>
<td>Alamat</td>
<td align="center">:</td>
<td><?php echo $dataPasien["cust_usr_alamat"];?></td>
</tr>
<td colspan="3">&nbsp;</td>
</tr> 
<tr>
<td colspan="3">Yang Menerima,<br><br>(<?php echo $userData["name"];?>)</td>
</tr>
</table> 
</td>


<!--- table kanan --->

<td valign="top" width="50%" align="center">
<table border="0" width="100%" style="font-size:10px;border-collapse:collapse;"> 
<tr height="20">
<td width="2%" style="border-bottom:1px solid black;" align="center">No </td>
<td width="60%" style="border-bottom:1px solid black;"align="center">Rincian Biaya</td>
<td width="8%" style="border-bottom:1px solid black;" align="center">QTY</td>
<td width="30%" style="border-bottom:1px solid black;" align="center">Biaya Total</td>
</tr>
	<?php for($i=0,$n=count($dataFolio);$i<$n;$i++) { 
  if($dataFolio[$i]["fol_jumlah"]!='0.00'){
            $total = $dataFolio[$i]["fol_jumlah"]*$dataFolio[$i]["fol_nominal"];
          }else{
            $total = $dataFolio[$i]["fol_nominal"];
          } ?>
<tr>
<td align="center"><?php echo ($i+1); ?></td>
<td align="center"><?php echo $dataFolio[$i]["fol_nama"];?></td>
<td align="center"><?php if($dataFolio[$i]["fol_jumlah"]!='0.00'){
          echo currency_format($dataFolio[$i]["fol_jumlah"]);
          }else{
          echo "1";
          } ?></td>
<td align="center">Rp. <?php echo currency_format($total);?></td>
<?php $totalHarga+=$total; ?>
</tr>
<?php } ?>

<?php if($n<6) { for($i=0;$i<(6-$n);$i++) { ?>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<?php } }?>
<tr>
<td colspan="3" align="center" style="font-size:10px;">Jumlah</td>
<td align="center"><span style="border-top:1px solid black;font-size:10px;"><?php echo "Rp.&nbsp;".currency_format($totalHarga); ?></span><span style="margin:-7px 0 0 2px;position:absolute;">+</span></td>
</tr>          
<tr height="20">
<td colspan="4" style="font-size:10px;">Terbilang : <?php echo terbilang($totalHarga); ?> Rupiah</td>
</tr>
</table>
</td>

</tr>
</table>




</body>
</html>
