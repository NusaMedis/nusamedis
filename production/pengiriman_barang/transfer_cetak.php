<?php
     require_once("../penghubung.inc.php");
     require_once($ROOT."lib/login.php");
     require_once($ROOT."lib/encrypt.php");
     require_once($ROOT."lib/datamodel.php");
     require_once($ROOT."lib/bit.php");
     require_once($ROOT."lib/dateLib.php");
     require_once($ROOT."lib/currency.php");
     require_once($ROOT."lib/tampilan.php");

         
     $view = new CView($_SERVER["PHP_SELF"],$_SERVER['QUERY_STRING']);
	   $dtaccess = new DataAccess();
     $enc = new textEncrypt();
     $auth = new CAuth();
     $err_code = 0;
     $skr = date("Y-m-d");
     $usrId = $auth->GetUserId();
     $userData = $auth->GetUserData();
     $depId = $auth->GetDepId();
	   $table = new InoTable("table","100%","left");
	
 	   /*
     if(!$auth->IsAllowed("transfer_stok",PRIV_CREATE)){
          die("access_denied");
          exit(1);
     } else if($auth->IsAllowed("transfer_stok",PRIV_CREATE)===1){
          echo"<script>window.parent.document.location.href='".$APLICATION_ROOT."login.php?msg=Login First'</script>";
          exit(1);
     }*/


	if($_GET["id"]) $_POST["id"] =$_GET["id"];
	
	$sql = "select a.*,b.gudang_nama, c.gudang_nama as dep_nama_asal,d.pgw_nip,d.pgw_nama,e.pgw_nama as kabag_minta, 
          e.pgw_nip as kabag_minta_nip, c.gudang_pj_nama, f.pgw_nip as pj_nip, b.gudang_pj_nama as pj_minta, g.pgw_nip as pj_nip_minta 
          from logistik.logistik_transfer_stok a
          left join logistik.logistik_gudang b on b.gudang_id = a.id_tujuan
          left join logistik.logistik_gudang c on c.gudang_id = a.id_asal
          left join hris.hris_pegawai d on c.gudang_ka = d.pgw_id
          left join hris.hris_pegawai e on b.gudang_ka = e.pgw_id
          left join hris.hris_pegawai f on c.gudang_pj = f.pgw_id
          left join hris.hris_pegawai g on b.gudang_pj = g.pgw_id
          where a.transfer_id = ".QuoteValue(DPE_CHAR,$_POST["id"]);
  $dataTransfer = $dtaccess->Fetch($sql,DB_SCHEMA);
	     //    echo $sql;
	$sql = "select *, b.hpp as hpp from logistik.logistik_transfer_stok a
          left join logistik.logistik_transfer_stok_detail b on a.transfer_id = b.id_transfer
          left join logistik.logistik_item c on c.item_id = b.id_item
          join logistik.logistik_grup_item d on d.grup_item_id = c.id_kategori
          left join logistik.logistik_item_satuan e on e.satuan_id = c.id_satuan_jual 
          where a.transfer_id = ".QuoteValue(DPE_CHAR, $_POST["id"])."
          order by b.no_urut";
  //echo $sql;
  $rs_edit = $dtaccess->Execute($sql,DB_SCHEMA);
  $dataTable = $dtaccess->FetchAll($rs_edit);
 	
//	$fotoName = $APLICATION_ROOT."gambar/foto_pasien/".$dataPasien["cust_usr_foto"];

       // KONFIGURASI
     $sql = "select * from global.global_departemen where dep_id =".QuoteValue(DPE_CHAR,$depId);
     $rs = $dtaccess->Execute($sql);
     $konfigurasi = $dtaccess->Fetch($rs);  
//echo $sql;     
     if ($konfigurasi["dep_height"]!=0) $panjang=$konfigurasi["dep_height"] ;
     if ($konfigurasi["dep_width"]!=0) $lebar=$konfigurasi["dep_width"] ;
     $fotoName = $ROOT."/gambar/img_cfg/".$konfigurasi["dep_logo"]; 
     $bg = $ROOT."/gambar/img_cfg/".$konfigurasi["dep_logo"];
     $lokasi = $ROOT."/gambar/img_cfg";


?>

<!--<?php //echo $view->RenderBody("inosoft_prn.css",true,null,false,"inosoft_prn.css"); ?>--> 

<script language="javascript" type="text/javascript">

window.print();

</script>

<style>
@media print {
     #tableprint { display:none; }
}
</style>


<form name="frmView" method="POST" action="<?php echo $thisPage; ?>">

<table width="100%" border="0" cellpadding="1" cellspacing="0" style="border-collapse:collapse">
  <tr>
        <td align="right"><img src="<?php echo $fotoName ;?>" height="75"> </td>
<!--    <td align="center" bgcolor="#CCCCCC" id="judul">     -->
    <td align="center" id="judul">  
     <span class="judul2"><strong><?php echo $konfigurasi["dep_nama"]?></strong><br></span>
		<span class="judul3"><strong>
		<?php echo $konfigurasi["dep_kop_surat_1"]?></strong></span><br>
    <span class="judul4">       
	  <?php echo $konfigurasi["dep_kop_surat_2"]?></span></td> 
    <td width="30%" >&nbsp;</td> 
  </tr>
</table>
    <td align="center" colspan="8"><strong>=======================================================================================================================================</strong></td>
 <table border="0" cellpadding="2" cellspacing="0"  align="center" width="100%">     
    <tr>
      <td style="text-align:center;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent"><u>SURAT BUKTI BARANG KELUAR</u></td>
    </tr>
    <tr>
      <td style="text-align:center;font-size:18px;font-family:sans-serif;font-weight:bold;" class="tablecontent">( SBBK )</td>
    </tr>
  </table>
<br>
<table border="0" cellpadding="0" cellspacing="0"  align="center" width="100%">    
    <tr>
       <td width="30%"><font style="font-size:14px;font-weight:normal">Nomor Permintaan</td>
       <td width="2%"><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"> <?php echo $dataTransfer["transfer_nomor"]?></td>
       <td>&nbsp;</td>
       <td><font style="font-size:14px;font-weight:normal">Keterangan</td>
       <td><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"><?php echo $dataTransfer["transfer_keterangan"]?></td>
    </tr>    
    <tr>
     <td width="30%"><font style="font-size:14px;font-weight:normal">Dikirim Dari</td>
       <td width="2%"><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"> <?php echo $dataTransfer["dep_nama_asal"]?></td>
       <td>&nbsp;</td>
       <td width="30%"><font style="font-size:14px;font-weight:normal">Dikirim kepada</td>
       <td width="2%"><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"> <?php echo $dataTransfer["gudang_nama"]?></td>
    </tr>   
    <tr>
       <td width="30%"><font style="font-size:14px;font-weight:normal">Tanggal Permintaan</td>
       <td width="2%"><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"> <?php echo format_date($dataTransfer["transfer_tanggal_permintaan"]);?></td>
       <td>&nbsp;</td>
       <td><font style="font-size:14px;font-weight:normal">Tanggal Pengiriman</td>
       <td><font style="font-size:14px;font-weight:normal">:</td>
       <td><font style="font-size:14px;font-weight:normal"><?php echo format_date($dataTransfer["transfer_tanggal_keluar"]);?></td>
    </tr>    

  </table>
<br>
     <table align="center" width="100%" border="1" cellpadding="5" cellspacing="0">
      <tr align="center">
        <td style="font-size:14px;"><b>No</b></td>
        <td style="font-size:14px;"><b>Nama Barang</b></td>
        <td style="font-size:14px;"><b>Satuan</b></td>
        <td style="font-size:14px;"><b>Kode Barang</b></td>
        <td style="font-size:14px;"><b>Kategori</b></td>
        <td style="font-size:14px;"><b>Jumlah Permintaan</b></td>    
        <td style="font-size:14px;"><b>Jumlah Disetujui</b></td>
        <td style="font-size:14px;"><b>HPP</b></td>
      </tr>
      <?php for($i=0,$n=count($dataTable);$i<$n;$i++){ ?>
      <tr>
        <td align="center"  style="font-size:14px;">&nbsp;<?php echo $i+1 ;?></td>
        <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_nama"];?></td>
        <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["satuan_nama"];?></td>
        <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_tree"];?></td>
        <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["grup_item_nama"];?></td>
        <td align="right" style="font-size:14px;">&nbsp;<?php echo number_format($dataTable[$i]["transfer_detail_jumlah_permintaan"], 2, '.', ',');?></td>
        <td align="right" style="font-size:14px;">&nbsp;<?php echo number_format($dataTable[$i]["transfer_detail_jumlah"], 2, '.', ',');?></td>
        <td align="right" style="font-size:14px;">&nbsp;<?php echo number_format(intval($dataTable[$i]["hpp"]), 0, '.', ',');?></td>
      </tr>
      <?php 
        $permintaan += $dataTable[$i]["transfer_detail_jumlah_permintaan"];
        $detail += $dataTable[$i]["transfer_detail_jumlah"];
        $jumhpp += $dataTable[$i]["hpp"];
      } ?>
      <tr>
        <td align="right" colspan="5" style="font-size:14px;">Jumlah</td>
        <td align="right" style="font-size:14px;"><?=number_format($permintaan, 2, '.', ',')?></td>
        <td align="right" style="font-size:14px;"><?=number_format($detail, 2, '.', ',')?></td>
        <td align="right" style="font-size:14px;"><?= number_format(intval($jumhpp), 0, '.', ',') ?></td>
      </tr>
     	<!--<tr align="center">
     		<td width="50%">
     			<table width="100%" border="1" >
            <tr align="center">
              <td style="font-size:14px;"><b>No</b></td>
              <td style="font-size:14px;"><b>Nama Barang</b</td>
              <td style="font-size:14px;"><b>Kode Barang</b</td>
              <!--<td style="font-size:14px;"><b>ED</b</td>-->
              <!--<td style="font-size:14px;"><b>Jumlah</b</td>    
            </tr>
            <?php 
            $n=count($dataTable); 
            if($n<=20){
             $c=$n;
            }else{
            $c=20;
            }
            for($i=0;$i<$c;$i++){ ?>
            <!--<tr>
              <td align="center"  style="font-size:14px;">&nbsp;<?php echo $i+1 ;?></td>
              <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_nama"];?></td>
              <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_tree_kode"];?></td>
              <!--<td style="font-size:14px;">&nbsp;<?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>-->
              <!--<td align="right" style="font-size:14px;">&nbsp;<?php echo currency_format($dataTable[$i]["transfer_detail_jumlah"]);?></td>
            </tr>
            <?php } ?>
          </table> 
     		</td>
        <td width="50%" valign="top"> 
      <?php 
          $n=count($dataTable);
          if($n>20){
           $i=20;
           $n=40;
             ?> 
     		
         	<!--<table width="100%" border="1">
            <tr align="center">
              <td style="font-size:14px;"><b>No</b></td>
              <td style="font-size:14px;"><b>Nama Barang</b</td>
              <td style="font-size:14px;"><b>Kode Barang</b</td>
              <!--<td style="font-size:14px;"><b>Expire Date</b</td>-->
              <!--<td style="font-size:14px;"><b>QTY</b</td>    
            </tr>
            <?php for($i;$i<$n;$i++){ ?>
            <tr>
              <td align="center" style="font-size:14px;">&nbsp;<?php echo $i+1; ?></td>
              <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_nama"];?></td>
              <td style="font-size:14px;">&nbsp;<?php echo $dataTable[$i]["item_tree_kode"];?></td>
              <!--<td style="font-size:14px;">&nbsp;<?php echo format_date($dataTable[$i]["batch_tgl_jatuh_tempo"]);?></td>-->              
              <!--<td align="right"  style="font-size:14px;">&nbsp;<?php echo currency_format($dataTable[$i]["transfer_detail_jumlah"]);?></td>
            </tr>
            <?php } ?>    
          </table> 
          
          <?php } ?>
     		</td>
     	</tr>-->   
      </table>
    	<table width="100%" border="0">
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	  <td style="font-size:14px;" width="50%" align="center"><b><?php echo $konfigurasi["dep_kota"];?>, <?php echo format_date_long(date("Y-m-d"));?></b></td>
     	</tr>
     	<tr>
     	  <td style="font-size:14px;" width="50%" align="center"><b>Setuju Dikeluarkan</b><br><b>Ka. <?php echo $dataTransfer["dep_nama_asal"];?></b></td>
     	  <td style="font-size:14px;" width="50%" align="center"><b>Permintaan</b><br><b>Ka. Bag/Sub. Bag/Seksi <?php echo $dataTransfer["gudang_nama"];?></b></td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center"><b><u><?php echo $dataTransfer["pgw_nama"];?></u></b></td>
     	  <td style="font-size:14px;" width="50%" align="center"><b><u><?php echo $dataTransfer["kabag_minta"];?></u></b></td>
     	</tr>
       <tr><td><br></td></tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center"><b>Dikeluarkan</b><br><b>Oleh</b></td>
     	  <td style="font-size:14px;" width="50%" align="center"><b>Penerima</b></td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	  <td style="font-size:14px;" width="50%" align="center">&nbsp;</td>
     	</tr>
      <tr>
     	  <td style="font-size:14px;" width="50%" align="center"><b><u><?php echo $dataTransfer["gudang_pj_nama"]; ?></u></b></td>
     	  <td style="font-size:14px;" width="50%" align="center"><b><u><?php echo $dataTransfer["pj_minta"]; ?></u></b></td>
     	</tr>
     	<!--<tr>
     	 <td style="font-size:14px;" align="center"><u>.......................</u></td>
     	 <td >&nbsp;</td>
     	 <td style="font-size:14px;" align="center"><u><?php echo $dataTransfer["transfer_pengirim"]; ?></u></td>
     	 <td >&nbsp;</td>
     	 <td style="font-size:14px;" align="center"><u><?php echo $dataTransfer["transfer_penerima"]; ?></u></td>
     	</tr>
     	<tr>
     	 <td style="font-size:14px;" align="center">NIP. ................</td>
     	 <td >&nbsp;</td>
     	 <td style="font-size:14px;" align="center">NIP. <?php echo $dataTransfer["transfer_pengirim_nip"]; ?></td>
     	 <td >&nbsp;</td>
     	 <td style="font-size:14px;" align="center">NIP. <?php echo $dataTransfer["transfer_penerima_nip"]; ?></td>
     	</tr>-->
 
     </table>

</form>
</body>
<!--<?php echo $view->RenderBodyEnd(); ?>-->

</html>