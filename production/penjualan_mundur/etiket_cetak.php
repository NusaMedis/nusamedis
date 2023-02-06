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
          echo"<script>window.document.location.href='".$APLICATION_ROOT."expire.php'</script>";
          exit(1);
          
     } elseif($auth->IsAllowed("apo_penjualan_dalam",PRIV_READ)===1){
          echo"<script>window.parent.document.location.href='".$ROOT."login.php?msg=Session Expired'</script>";
          exit(1);
     } */

                                                                  
     $_x_mode = "New";
     $thisPage = "kasir_view.php";
	
	if($_GET["id"]) {
		$sql = "select a.id_item,a.penjualan_detail_create, a.penjualan_detail_jumlah, b.* ,c.item_nama , c.item_tipe_jenis,d.cust_usr_kode_tampilan,d.cust_usr_tanggal_lahir,
            e.jenis_nama, f.poli_nama,g.petunjuk_nama,d.cust_usr_ibu,h.batch_tgl_jatuh_tempo,
            i.dep_nama,i.dep_kop_surat_1,j.aturan_minum_nama,k.aturan_pakai_nama, c.item_racikan, z.jam_aturan_pakai_nama
            from apotik.apotik_penjualan_detail a 
            left join apotik.apotik_penjualan b on a.id_penjualan = b.penjualan_id
            left join klinik.klinik_registrasi l on b.id_reg =l.reg_id
            left join logistik.logistik_item c on a.id_item = c.item_id 
            left join global.global_customer_user d on l.id_cust_usr = d.cust_usr_id
            left join global.global_jenis_pasien e on b.id_jenis_pasien = e.jenis_id
            left join global.global_auth_poli f on f.poli_id = b.id_poli
            left join apotik.apotik_obat_petunjuk g on a.id_petunjuk = g.petunjuk_id
            left join logistik.logistik_item_batch h on a.id_batch = h.batch_id
            left join global.global_departemen i on b.id_dep = i.dep_id
            left join apotik.apotik_aturan_minum j on a.id_aturan_minum=j.aturan_minum_id
            left join apotik.apotik_aturan_pakai k on k.aturan_pakai_id=a.id_aturan_pakai
            left join apotik.apotik_jam_aturan_pakai z on z.jam_aturan_pakai_id = a.id_jam_aturan_pakai
            where b.penjualan_id = ".QuoteValue(DPE_CHAR,$_GET["id"]);
		$dataTable = $dtaccess->FetchAll($sql);
			
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

<?php// echo $view->RenderBody("inventori_prn.css",true); ?>
<html>
<head>
<title>Cetak Pengeluaran Obat Apotik</title>

<script language="javascript" type="text/javascript">

window.print();
window.close();

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
<?php for($i=0,$n=count($dataTable);$i<$n;$i++) {  
$waktu=explode(' ',$dataTable[$i]["penjualan_detail_create"]);
$umur = explode('~',$dataTable[$i]["cust_usr_umur"]);
$aturan = explode('x',$dataTable[$i]["petunjuk_nama"]);
 ?>
<fieldset style="border: 0;">
<table align="center" width="100%" border="0" style="font-size:11px;">
<tr>
<td align="center"><strong><?php echo $dataTable[$i]["dep_nama"];?></strong></td>
</tr>
</table>

<table align="center" width="100%" border="0" style="font-size:8; letter-spacing: 0.2;">
<tr >
<td align="left" width="30%" style="font-size: 8">No. Trans / Tgl</td>
<td align="center" style="font-size: 9">:</td>
<td align="left" style="font-size: 9"><?php echo $dataTable[$i]["penjualan_nomor"];?></td>
</tr>
<tr style="font-size: 10" valign="top">
<td align="left" style="font-size: 9">Nama</td>
<td align="center">:</td>
<td align="left" ><?php echo $dataTable[$i]["cust_usr_nama"];?></td>
</tr>
<tr style="font-size: 9">
<td align="left">RM / Tgl Lahir</td>
<td align="center">:</td>
<td align="left"><?php echo $dataTable[$i]["cust_usr_kode_tampilan"].' / '.format_date($dataTable[$i]["cust_usr_tanggal_lahir"]);?></td>
</tr>
<?php 
$item_racikan = $dataTable[$i]["item_racikan"];
if(trim($item_racikan) == 'y') {
	$sql = "select c.item_nama,detail_racikan_jumlah from apotik.apotik_detail_racikan a
	left join logistik.logistik_item c on a.id_item = c.item_id 
	where id_nama_racikan = ".QuoteValue(DPE_CHAR,$dataTable[$i]["id_item"]);
	$racikan_detail = $dtaccess->FetchAll($sql);
	$data = array();
	foreach($racikan_detail as $a){
		array_push($data,$a['detail_racikan_jumlah'].' '.$a['item_nama']);
	}
?>
<tr style="font-size: 9" valign="top">
<td align="left">Nama Obat</td>
<td align="center" >:</td>
<td align="left" height="50"><?php echo implode(' ~ ',$data);?></td>
</tr>
<?php } else { ?>
<tr style="font-size: 9" valign="top">
<td align="left">Nama Obat</td>
<td align="center" >:</td>
<td align="left" height="50"><?php echo $dataTable[$i]["item_nama"];?></td>
</tr>
<?php } ?>

<tr style="font-size: 9">
<td align="left" >Aturan Pakai</td>
<td align="center">:</td>
<td align="left"><?php echo $dataTable[$i]["petunjuk_nama"]." ".$dataTable[$i]["aturan_minum_nama"];?></td>
</tr>
<?php if ($konfigurasi["dep_konf_jam_aturan_pakai"] =='y') { ?>
<tr style="font-size: 9">
<td align="left" >Jam Aturan Pakai</td>
<td align="center">:</td>
<td align="left"><?php echo $dataTable[$i]["jam_aturan_pakai_nama"];?></td>
</tr>
<?php } ?>
<tr>
<tr style="font-size: 9">
<td align="left">Cara Pakai</td>
<td align="center">:</td>
<td align="left" style="font-size: 9"><?php echo $dataTable[$i]["aturan_pakai_nama"];?>&nbsp;</td>
</tr>
<tr style="font-size: 9">
<td align="left">Jumlah / ED</td>
<td align="center"  >:</td>
<td align="left" style="font-size: 10"><?php echo currency_format($dataTable[$i]["penjualan_detail_jumlah"]);?> / <?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>
</tr>

</table>

</fieldset>
<?php } ?>
</body>
</html>
